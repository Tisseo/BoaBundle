<?php

namespace Tisseo\BoaBundle\Services;

use Doctrine\ORM\EntityManager;
use Tisseo\EndivBundle\Entity\Trip;
use Tisseo\EndivBundle\Entity\LineVersion;
use Tisseo\EndivBundle\Entity\Route;
use Tisseo\EndivBundle\Entity\RouteStop;
use Tisseo\EndivBundle\Services\RouteManager;
use Tisseo\EndivBundle\Services\StopTimeManager;


class Monitoring
{
    /** @var StopTimeManager */
    private $stopTimeManager;

    private $routeManager;

    /** @var  \Doctrine\ORM\EntityManager */
    private $em;

    /**
     * Monitoring constructor.
     *
     * @param StopTimeManager    $stopTimeManager
     * @param RouteManager       $routeManager
     * @param EntityManager      $em
     */
    public function __construct(
        StopTimeManager $stopTimeManager,
        RouteManager $routeManager,
        EntityManager $em)
    {
        $this->stopTimeManager = $stopTimeManager;
        $this->routeManager = $routeManager;
        $this->em = $em;
    }

  /**
   * Search offers by line version id
   * @param $lineVersionId
   *
   * @return array
   */
    public function search($lineVersionId) {
      $rawSql = "
      WITH
        max_rank AS (SELECT MAX(rank) as m, route_id FROM route_stop GROUP BY route_id),
        stop_name AS(
          SELECT short_name as name, stop.id as id FROM stop JOIN stop_history ON (stop_id = stop.id AND start_date < now() AND(end_date IS NULL OR end_date > now()))
          UNION
          SELECT name, id FROM odt_area
        ),
        bm_idx AS (
          SELECT 
            UNNEST(STRING_TO_ARRAY(calendars.bm, NULL))::BOOLEAN as is_valid, 
            GENERATE_SUBSCRIPTS(STRING_TO_ARRAY(calendars.bm, NULL), 1) as idx, 
            pc_id, 
            dc_id
          FROM
          (
            SELECT 
              CASE WHEN trip_datasource.datasource_id = 1 
                THEN getcalendarbitmask(pc.id, line_version.start_date, COALESCE (line_version.end_date, line_version.planned_end_date))::text
                ELSE getbitmaskbeetweencalendars(dc.id, pc.id, line_version.start_date, COALESCE (line_version.end_date, line_version.planned_end_date))::text
              END as bm,
              CASE WHEN trip_datasource.datasource_id = 1 
                THEN 27
                ELSE dc.id
              END as dc_id,
              pc.id as pc_id
            FROM
              trip LEFT OUTER JOIN
              calendar as dc ON day_calendar_id = dc.id JOIN
              calendar as pc ON period_calendar_id = pc.id JOIN
              trip_datasource ON trip_datasource.trip_id = trip.id JOIN
              route On route_id = route.id JOIN
              line_version ON route.line_version_id = line_version.id
            WHERE trip.is_pattern IS FALSE AND line_version.id = :lineVersionId 
            GROUP BY dc.id, pc.id,datasource_id,start_date,end_date, planned_end_date
          ) as calendars
        )
      SELECT 
        count(trip.id) as \"number\", -- nombre de service
        array_agg(trip.id) as \"trips\", -- liste des id des trips pour chaque jour
        line_version.start_date + idx -1 as \"traffic_date\", -- date de circulation
        route.id as route_id,
        route.name, 
        name_start.name as \"start\", -- arrêt de début 
        name_end.name as \"end\" -- arrêt de fin
      
      FROM
        trip JOIN
        route ON route.id = trip.route_id JOIN
        line_version ON line_version.id = route.line_version_id join
        bm_idx ON ((bm_idx.dc_id = day_calendar_id OR dc_id = 27) AND pc_id = period_calendar_id) JOIN
        max_rank ON max_rank.route_id = route.id JOIN
        route_stop as rs_start ON (rs_start.route_id = route.id AND rs_start.rank = 1) JOIN
        stop_name as name_start ON name_start.id = rs_start.waypoint_id JOIN
        route_stop as rs_end ON (rs_end.route_id = route.id AND rs_end.rank = max_rank.m) JOIN
        stop_name as name_end ON name_end.id = rs_end.waypoint_id
      WHERE 
        is_valid AND 
        line_version.id = :lineVersionId AND 
        trip.is_pattern IS FALSE
      GROUP BY bm_idx.idx, traffic_date, route.id, route.name, name_start.name, name_end.name
      ORDER BY   traffic_date;
            ";
      $stmt = $this->em->getConnection()->prepare($rawSql);
      $stmt->bindParam(":lineVersionId", $lineVersionId);
      $stmt->execute();

      return  $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    public function getGraphData($routes)
    {
      $data = [];
      foreach ($routes as $key => $route) {
        $objRoute = $this->routeManager->find($route['route_id']);
        $routeStopDeparture = NULL;
        foreach ($objRoute->getRouteStops() as $routeStop) {
          if ($routeStop->getRank() == 1) {
            $routeStopDeparture = $routeStop;
            break;
          }
        }

        // Pour chaque trip récupérer stop time le plus tôt et la route id
        // Compte le nombre de départ par route / heure
        $trips = explode(
          ',',
          substr($route['trips'], 1, strlen($route['trips']) - 2)
        );

        // search stopTime for each trip
        $stopTimes = [];
        foreach ($trips as $tripId) {
          $stopTime = $this->stopTimeManager->getStopTimesByTripId($tripId, $routeStopDeparture->getId());
          if (is_array($stopTime) && !empty($stopTime)) {
            array_push($stopTimes, $stopTime[0]);
          }
        }


        // Compute each hour of day
        if (!empty($stopTimes)) {
          $result = $this->generateDataDayGraph($stopTimes);
          // Format
          $data['hour']['labels'] = array_keys($result);
          $data['hour']['datasets'][] = [
            'label' => $route['name'],
            'data' => array_values($result),
            'backgroundColor' => $route['color_value'],
            'borderColor' => $route['color_value'],
            'borderWidth' => 1,
          ];
        }
      }

      return $data;
    }

    /**
     * Return a formatted array for Charjs library
     *
     * @param $stopTimes array of StopTime
     *
     * @return array|false
     */
    private function generateDataDayGraph($stopTimes)
    {
        if (empty($stopTimes)) {
            return false;
        }

        // from 0 to 27h
        $arrayBitmask = str_split(str_repeat('0', 27));

        $sumFunc = function ($t1, $t2) {
            return $t1 + $t2;
        };

        // For each stopTime, do the sum of bitmask
        foreach ($stopTimes as $stopTime) {
            $hourStart = intval($stopTime->getDepartureTime() / 3600);
            $bitmask = str_repeat('0', $hourStart) . '1';
            $bitmask = str_pad($bitmask, strlen($bitmask) + (27 - strlen($bitmask)), '0', STR_PAD_RIGHT);
            $arrayBitmask = array_map($sumFunc, $arrayBitmask, str_split($bitmask));
        }

        // Rename array keys
        foreach ($arrayBitmask as $key => $value) {
            $newKey = str_pad($key, 2, 0, STR_PAD_LEFT);
            $arrayBitmask[$newKey.'h'] = $value;
            unset($arrayBitmask[$key]);
        }

        return $arrayBitmask;
    }
}
