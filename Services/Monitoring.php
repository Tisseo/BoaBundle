<?php

namespace Tisseo\BoaBundle\Services;

use Tisseo\EndivBundle\Entity\Datasource;
use Tisseo\EndivBundle\Entity\Trip;
use Tisseo\EndivBundle\Services\CalendarManager;
use Tisseo\EndivBundle\Services\LineVersionManager;
use Tisseo\EndivBundle\Entity\LineVersion;
use Tisseo\EndivBundle\Entity\Route;
use Tisseo\EndivBundle\Entity\RouteStop;
use Tisseo\EndivBundle\Services\StopTimeManager;
use Tisseo\EndivBundle\Services\TripManager;
use Symfony\Component\HttpFoundation\Session\Session;

class Monitoring
{
    /**
     * @var LineVersionManager
     */
    private $lineVersionManager;

    /**
     * @var CalendarManager
     */
    private $calendarManager;

    /** @var StopTimeManager */
    private $stopTimeManager;

    /** @var TripManager */
    private $tripManager;

    /** @var  array */
    private $configuration;

    /**
     * Monitoring constructor.
     *
     * @param LineVersionManager $lineVersionManager
     * @param CalendarManager    $calendarManager
     * @param StopTimeManager    $stopTimeManager
     * @param TripManager        $tripManager
     * @param array              $configuration
     */
    public function __construct(
        LineVersionManager $lineVersionManager,
        CalendarManager $calendarManager,
        StopTimeManager $stopTimeManager,
        TripManager $tripManager,
        $configuration)
    {
        $this->lineVersionManager = $lineVersionManager;
        $this->calendarManager = $calendarManager;
        $this->stopTimeManager = $stopTimeManager;
        $this->tripManager = $tripManager;
        $this->configuration = $configuration;
    }

    /**
     * @param $lineVersionId
     * @param \DateTimeInterface $date
     *
     * @return array
     */
    public function compute($lineVersionId, \DateTimeInterface $date)
    {
        $session = new Session();
        $result = [];
        $date = \DateTimeImmutable::createFromMutable($date);
        $stringDate = $date->format('Y-m-d H:i:s');

        /** @var LineVersion $lineVersion */
        $lineVersion = $this->lineVersionManager->find($lineVersionId);
        $defaultListColor = $this->configuration['defaultColors'];

        $session->set('lineVersionId', $lineVersionId);

        // Get Bitmasks
        $cachedBitmask = $session->get('cachedBitmask', null);
        if (is_null($cachedBitmask)) {
            $session->set('cachedBitmask', []);
        } else if (!isset($cachedBitmask[$lineVersionId])) {
            $cachedBitmask[$lineVersionId] = null;
            $session->set('cachedBitmask', $cachedBitmask);
        }


        /** @var Route $route */
        foreach ($lineVersion->getRoutes() as $key => $route) {
            $trips = $this->getTripsForRoute($route);
            $routeStopDeparture = $trips[0]->getRoute()->getRouteStops()->first();
            $routeStopArrival = $trips[0]->getRoute()->getRouteStops()->last();

            $stopDeparture = $routeStopDeparture->getWaypoint()->getStop();
            if ($stopDeparture->getStopArea() == null) {
                $departureName = $stopDeparture->getMasterStop()->getStopArea()->getLongName();
            } else {
                $departureName = $stopDeparture->getStopArea()->getLongName();
            }

            $stopArrival = $routeStopArrival->getWaypoint()->getStop();
            if ($stopArrival->getStopArea() == null) {
                $arrivalName = $stopArrival->getMasterStop()->getStopArea()->getLongName();
            } else {
                $arrivalName = $stopArrival->getStopArea()->getLongName();
            }

            // Caches the month results of the each route of the line version
            $monthYear = $date->format('m-Y');
            if (isset($cachedBitmask[$lineVersionId]['month'][$monthYear][$route->getId()])) {
                $month = $cachedBitmask[$lineVersionId]['month'][$monthYear][$route->getId()];
                $monthCached = true;
            } else {
                $month = $this->tripsByMonth($trips, $date);
                $cachedBitmask = $session->get('cachedBitmask');
                $cachedBitmask[$lineVersionId]['month'][$monthYear][$route->getId()] = $month;
                $session->set('cachedBitmask', $cachedBitmask);
                $monthCached = false;
            }


            $day = $this->tripsByDay($trips, $date);
            $hour = $this->tripsByHour($routeStopDeparture, $date);

            array_push(
                $result,
                [
                    'name' => $route->getName(),
                    'route_id' => $route->getId(),
                    'date' => $stringDate,
                    'departure' => $departureName,
                    'arrival' => $arrivalName,
                    'month' => $month, // Call method computeForMonth;
                    'monthCached' => $monthCached, // flag for template
                    'day' => $day, //Call method computeForDay
                    'hour' => $hour, // Call method computeForHour
                    'lineVersion' => $lineVersionId,
                    'properties' => ['color' => isset($defaultListColor[$key]) ? $defaultListColor[$key] : '#00ff00'],
                ]
            );
        }

        return $result;
    }

    /**
     * @param \Tisseo\EndivBundle\Entity\Route $route
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTripsForRoute(Route $route)
    {
        $session = new Session();
        $cachedTrips = $session->get('cachedTrips');
        if (is_null($cachedTrips) || empty($cachedTrips)) {
            $session->set('cachedTrips', []);
        }

        if(isset($cachedTrips[$route->getId()])) {
            $trips = $cachedTrips[$route->getId()];
        } else {
            $trips = $route->getTrips();
            $cachedTrips[$route->getId()] = $trips;
            $session->set('cachedTrips', $cachedTrips);
        }

        return $trips;
    }

    /**
     * @param $trips
     * @param \DateTimeInterface $startDate
     * @param bool               $graph
     *
     * @return array|int
     */
    public function tripsByMonth($trips, \DateTimeInterface $startDate, $graph = false)
    {
        $startDate = $startDate->modify('first day of this month');
        $startDate = $startDate->setTime(0, 0, 0);
        $endDate = $startDate->modify('last day of this month');
        $endDate = $endDate->setTime(23, 59, 59);

        if (!$graph) {
            return $this->countServices($trips, $startDate, $endDate);
        } else {
            return $this->generateDataMonthGraph($trips, $startDate, $endDate);
        }
    }

    /**
     * @param $trips
     * @param \DateTimeInterface $startDate
     *
     * @return int
     */
    public function tripsByDay($trips, \DateTimeInterface $startDate)
    {
        //$startDate = $startDate->setTime(0, 0, 0);
        //$endDate = $startDate->setTime(23, 59, 59);
        $day = $startDate->format('j');
        $startDate = $startDate->modify('first day of this month');
        $startDate = $startDate->setTime(0, 0, 0);
        $endDate = $startDate->modify('last day of this month');
        $endDate = $endDate->setTime(23, 59, 59);

        return $this->countServices($trips, $startDate, $endDate, 2, $day);
    }

    /**
     * @param \Tisseo\EndivBundle\Entity\RouteStop $routeStopDeparture
     * @param \DateTimeInterface                   $date
     * @param bool                                 $graph
     *
     * @return array|int
     */
    public function tripsByHour(RouteStop $routeStopDeparture, \DateTimeInterface $date, $graph = false)
    {
        $startDate = ($graph) ? $date->setTime(0, 0, 0) : $date;
        $startTime = intval($startDate->format('H')) * 3600;

        $endDate = $date->setTime(23, 59, 59);
        $endTime = ($graph) ? intval($endDate->format('H')) * 3600 + 3599 : $startTime + 3599;

        $stopTimes = $this->stopTimeManager->getStopTimeWhoStartBetween($startTime, $endTime, $routeStopDeparture);

        if (!$graph) {
            $filteredTrips = [];
            /* @var \Tisseo\EndivBundle\Entity\StopTime $stopTime */
            foreach ($stopTimes as $k => $stopTime) {
                $filteredTrips[] = $stopTime->getTrip();
            }

            return $this->countServices($filteredTrips, $startDate, $endDate);
        } else {
            return $this->generateDataDayGraph($stopTimes, $startDate, $endDate);
        }
    }

    /**
     * Count the number of services for the each trip between startDate and endDate
     *
     * @param $trips
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @param int $mode
     * @param int $day current day number
     *
     * @return int
     */
    private function countServices($trips, \DateTimeInterface $startDate, \DateTimeInterface $endDate, $mode = 1, $day = null)
    {
        if (empty($trips)) {
            return 0;
        }

        $nbService = 0;
        $session = new Session();
        $lineVersionId = $session->get('lineVersionId');
        list($key, $cachedBitmask) = $this->getCachedBitmask(
            $lineVersionId,
            $startDate,
            $endDate
        );

        switch($mode) {
            case 1:
                /** @var \Tisseo\EndivBundle\Entity\Trip $trip */
                foreach ($trips as $trip) {
                    $bitmask = $this->getBitmask($trip, $startDate, $endDate, $cachedBitmask[$lineVersionId][$key]);
                    $nbService += substr_count($bitmask, 1);
                }
                $session->set('cachedBitmask', $cachedBitmask);
                break;
            case 2:
                /** @var \Tisseo\EndivBundle\Entity\Trip $trip */
                foreach ($trips as $trip) {
                    if (isset($cachedBitmask[$lineVersionId][$key][$trip->getId()])) {
                        $bitmask = $cachedBitmask[$lineVersionId][$key][$trip->getId()];
                        $bit = substr($bitmask,$day -1, $day);
                        $nbService += $bit;
                    };
                }
                break;
        }


        return $nbService;
    }

    /**
     * @param $trips
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     *
     * @return array|int
     */
    private function generateDataMonthGraph($trips, \DateTimeInterface $startDate, \DateTimeInterface $endDate)
    {
        if (empty($trips)) {
            return 0;
        }

        $dayInMonth = cal_days_in_month(CAL_GREGORIAN, $startDate->format('m'), $startDate->format('Y'));
        $arrayBitmask = str_split(str_repeat('0', $dayInMonth));

        $session = new Session();
        $lineVersionId = $session->get('lineVersionId', null);
        list($key, $cachedBitmask) = $this->getCachedBitmask(
            $lineVersionId,
            $startDate,
            $endDate
        );

        $sumFunc = function ($t1, $t2) {
            return $t1 + $t2;
        };

        foreach ($trips as $trip) {
            $bitmask = $this->getBitmask($trip, $startDate, $endDate, $cachedBitmask[$lineVersionId][$key]);
            $arrayBitmask = array_map($sumFunc, $arrayBitmask, str_split($bitmask));
        }

        // Rename array keys
        $monthYear = $startDate->format('m/Y');
        foreach ($arrayBitmask as $key => $value) {
            $newKey = str_pad(($key + 1), 2, 0, STR_PAD_LEFT);
            $arrayBitmask[$newKey.'/'.$monthYear] = $value;
            unset($arrayBitmask[$key]);
        }

        return $arrayBitmask;
    }

    /**
     * @param $stopTimes
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     *
     * @return array|int
     */
    private function generateDataDayGraph($stopTimes, \DateTimeInterface $startDate, \DateTimeInterface $endDate)
    {
        if (empty($stopTimes)) {
            return 0;
        }

        $arrayBitmask = str_split(str_repeat('0', 24));
        $cachedBitmask = [];

        $sumFunc = function ($t1, $t2) {
            return $t1 + $t2;
        };

        foreach ($stopTimes as $stopTime) {
            $bitmask = $this->getBitmask($stopTime->getTrip(), $startDate, $endDate, $cachedBitmask);

            if ($bitmask == 1) {
                $hourStart = intval($stopTime->getDepartureTime() / 3600);
                $bitmask = str_repeat('0', $hourStart).'1';
                $bitmask = str_pad($bitmask, strlen($bitmask) + (24 - strlen($bitmask)), '0', STR_PAD_RIGHT);
            }

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

    /**
     * Get bitmask for a trip $trip between $startDate and $endDate
     *
     * @param \Tisseo\EndivBundle\Entity\Trip $trip
     * @param \DateTimeInterface              $startDate
     * @param \DateTimeInterface              $endDate
     * @param $cachedBitmask array of cached bitmask (by reference)
     *
     * @return string
     */
    private function getBitmask(Trip $trip, \DateTimeInterface $startDate, \DateTimeInterface $endDate, &$cachedBitmask)
    {
        if (!$trip->getPeriodCalendar()) {
            return '0';  // ignore trip without period calendar
        }
        /*$tests = $trip->getRoute()->getLineVersion()->getLine()->getLineDatasources();
        foreach ($tests as $test) {
            dump($test);
        }*/
        if (isset($cachedBitmask[$trip->getId()])) {
            return $cachedBitmask[$trip->getId()];
        }
        if (!$trip->getDayCalendar() /* || linedatasoure = 1 (hastus) */) {
            //if (!isset($cachedBitmask[$trip->getPeriodCalendar()->getId()])) {
            //if (!isset($cachedBitmask[$trip->getId()])) {
                $bitmask = $this->calendarManager->getCalendarBitmask(
                    $trip->getPeriodCalendar()->getId(),
                    $startDate,
                    $endDate
                );
                //$cachedBitmask[$trip->getPeriodCalendar()->getId()] = $bitmask;
                $cachedBitmask[$trip->getId()] = $bitmask;
            //} else {
                //$bitmask = $cachedBitmask[$trip->getPeriodCalendar()->getId()];

            //}
        } else {
            //if (!isset($cachedBitmask[$trip->getDayCalendar()->getId().'-'.$trip->getPeriodCalendar()->getId()])) {
            //if (!isset($cachedBitmask[$trip->getId()])) {
                $bitmask = $this->calendarManager->getCalendarsIntersectionBitmask(
                    $trip->getPeriodCalendar()->getId(),
                    $trip->getDayCalendar()->getId(),
                    $startDate,
                    $endDate
                );
              //  $cachedBitmask[$trip->getDayCalendar()->getId().'-'.$trip->getPeriodCalendar()->getId()] = $bitmask;
                $cachedBitmask[$trip->getId()] = $bitmask;
            //} else {
               // $bitmask = $cachedBitmask[$trip->getId()];

            //}
        }

        return $bitmask;
    }


    /**
     * Generate key for cache array
     *
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @return string
     */
    private function generateCacheKey(\DateTimeInterface $startDate, \DateTimeInterface $endDate)
    {
        return $startDate->getTimestamp() .':'. $endDate->getTimeStamp();
    }

    /**
     * Get catchedBitmask array
     *
     * @param $lineVersionId
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @return mixed array
     */
    private function getCachedBitmask($lineVersionId, \DateTimeInterface $startDate, \DateTimeInterface $endDate)
    {
        $session = new Session();
        $cachedBitmask = $session->get('cachedBitmask', null);
        $key = $this->generateCacheKey($startDate, $endDate);
        if (!isset($cachedBitmask[$lineVersionId][$key])) {
            $cachedBitmask[$lineVersionId][$key] = null;
        }

        return [$key, $cachedBitmask];
    }
}
