<?php

namespace Tisseo\BoaBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use Tisseo\EndivBundle\Services\CalendarManager;
use Tisseo\EndivBundle\Services\LineVersionManager;
use Tisseo\EndivBundle\Entity\LineVersion;
use Tisseo\EndivBundle\Entity\Route;
use Tisseo\EndivBundle\Entity\RouteStop;
use Tisseo\EndivBundle\Services\RouteStopManager;
use Tisseo\EndivBundle\Services\StopTimeManager;

class Monitoring
{
    const GRAPH_TYPE_MONTH = 1;
    const GRAPH_TYPE_DAY = 2;

    /** @var \Doctrine\Common\Persistence\ObjectManager $om */
    private $om;

    /**
     * @var LineVersionManager
     */
    private $lineVersionManager;

    /**
     * @var CalendarManager
     */
    private $calendarManager;

    /** @var RouteStopManager */
    private $routeStopManager;

    /** @var StopTimeManager */
    private $stopTimeManager;

    public function __construct(
        ObjectManager $om,
        LineVersionManager $lineVersionManager,
        CalendarManager $calendarManager,
        RouteStopManager $routeStopManager,
        StopTimeManager $stopTimeManager)
    {
        $this->om = $om;
        $this->lineVersionManager = $lineVersionManager;
        $this->calendarManager = $calendarManager;
        $this->routeStopManager = $routeStopManager;
        $this->stopTimeManager = $stopTimeManager;
    }

    public function compute($lineVersionId, \DateTimeInterface $date)
    {
        $result = [];
        $date = \DateTimeImmutable::createFromMutable($date);
        $stringDate = $date->format('Y-m-d H:i:s');

        /** @var LineVersion $lineVersion */
        $lineVersion = $this->lineVersionManager->find($lineVersionId);

        /** @var Route $route */
        foreach ($lineVersion->getRoutes() as $route) {
            /** @var RouteStop $routeStop */
            $rank = 0;

            foreach ($route->getRouteStops() as $routeStop) {
                if ($routeStop->getRank() == 1) {
                    $routeStopDeparture = $routeStop;
                }

                if ($routeStop->getRank() >= $rank) {
                    $rank = $routeStop->getRank();
                    $routeStopArrival = $routeStop;
                }
            }

            if (!isset($routeStopDeparture, $routeStopArrival)) {
                throw new \Exception();
            }

            $trips = $route->getTrips();

            array_push(
                $result,
                [
                    'name' => $route->getName(),
                    'route_id' => $route->getId(),
                    'date' => $stringDate,
                    'departure' => $routeStopDeparture->getWaypoint()->getStop()->getStopArea()->getLongName(),
                    'arrival' => $routeStopArrival->getWaypoint()->getStop()->getStopArea()->getLongName(),
                    'month' => $this->tripsByMonth($trips, $date), // Call method computeForMonth
                    'day' => $this->tripsByDay($trips, $date), // Call method computeForDay
                    'hour' => $this->tripsByHour($routeStopDeparture, $date), // Call method computeForHour
                ]
            );
        }

        return $result;
    }

    public function tripsByMonth($trips, \DateTimeInterface $startDate, $graph=false)
    {
        $startDate = $startDate->modify('first day of this month');
        $startDate->setTime(00, 00, 00);
        $endDate = $startDate->modify('last day of this month');
        $endDate->setTime(23, 59, 59);

        if (!$graph) {
            return $this->countServices($trips, $startDate, $endDate);
        } else {
            return $this->generateDataGraph($trips, $startDate, $endDate);
        }
    }

    public function tripsByDay($trips, \DateTimeInterface $startDate)
    {
        $startDate = $startDate->setTime(00, 00, 00);
        $endDate = $startDate->setTime(23, 59, 59);

        return $this->countServices($trips, $startDate, $endDate);
    }

    public function tripsByHour(RouteStop $routeStopDeparture, \DateTimeInterface $startDate, $graph=false)
    {
        //$startDate = $startDate->setTime(00,00,00);
        $endDate = $startDate->setTime(24, 59, 59);

        // Convert StartDate time to second
        $startTime = intval($startDate->format('h')) * 3600;
        // Adds 59 min and 59 sec
        $endTime = $startTime + 3599;

        $stopTimes = $this->stopTimeManager->getStopTimeWhoStartBetween($startTime, $endTime, $routeStopDeparture->getId());
        $trips = $routeStopDeparture->getRoute()->getTrips();

        /** @var \Tisseo\EndivBundle\Entity\StopTime $stopTime */
        /** @var \Tisseo\EndivBundle\Entity\Trip $trip */
        $filteredTrips = [];
        foreach ($trips as $trip) {
            foreach ($stopTimes as $k => $stopTime) {
                if ($stopTime->getTrip()->getId() == $trip->getId()) {
                    $filteredTrips[] = $trip;
                    unset($stopTimes[$k]);
                }
            }
        }

        if (!$graph) {
            return $this->countServices($filteredTrips, $startDate, $endDate);
        } else {
            return $this->generateDataGraph($filteredTrips, $startDate, $endDate, self::GRAPH_TYPE_DAY);
        }
    }

    private function countServices($trips, \DateTimeInterface $startDate, \DateTimeInterface $endDate)
    {
        if (empty($trips)) {
            return 0;
        }

        $nbService = 0;
        /** @var \Tisseo\EndivBundle\Entity\Trip $trip */
        foreach ($trips as $trip) {
            if (!$trip->getPeriodCalendar()) {
                continue;  // ignore trip without period calendar
            }

            if (!$trip->getDayCalendar()) {
                $bitmask = $this->calendarManager->getCalendarBitmask(
                    $trip->getPeriodCalendar()->getId(),
                    $startDate,
                    $endDate
                );
                $nbService += substr_count($bitmask, 1);
            } else {
                $bitmask = $this->calendarManager->getCalendarsIntersectionBitmask(
                    $trip->getPeriodCalendar()->getId(),
                    $trip->getDayCalendar()->getId(),
                    $startDate,
                    $endDate
                );
                $nbService += substr_count($bitmask, 1);
            }
        }

        return $nbService;
    }

    private function generateDataGraph($trips, \DateTimeInterface $startDate, \DateTimeInterface $endDate, $type = self::GRAPH_TYPE_MONTH)
    {
        if (empty($trips)) {
            return 0;
        }

        switch($type) {
            case self::GRAPH_TYPE_MONTH:
                $monthYear = $startDate->format('m/Y');
                $dayInMonth = cal_days_in_month(CAL_GREGORIAN, $startDate->format('m'), $startDate->format('Y'));
                $arrayBitmask = str_split(str_repeat('0', $dayInMonth));

                $sumFunc = function ($t1, $t2) {
                    return $t1 + $t2;
                };

                foreach ($trips as $trip) {
                    if (!$trip->getPeriodCalendar()) {
                        continue;  // ignore trip without period calendar
                    }
                    if (!$trip->getDayCalendar()) {
                        $bitmask = $this->calendarManager->getCalendarBitmask(
                            $trip->getPeriodCalendar()->getId(),
                            $startDate,
                            $endDate
                        );
                    }
                    else {
                        $bitmask = $this->calendarManager->getCalendarsIntersectionBitmask(
                            $trip->getPeriodCalendar()->getId(),
                            $trip->getDayCalendar()->getId(),
                            $startDate,
                            $endDate
                        );
                    }

                    $arrayBitmask = array_map($sumFunc, $arrayBitmask, str_split($bitmask));

                }

                // Rename array keys
                foreach ($arrayBitmask as $key => $value) {
                    $newKey = str_pad(($key + 1), 2, 0, STR_PAD_LEFT);
                    $arrayBitmask[$newKey . '/' . $monthYear] = $value;
                    unset($arrayBitmask[$key]);
                }

                break;

            case self::GRAPH_TYPE_DAY:
                $arrayBitmask = [];

                //$monthYear = $startDate->format('m/Y');
                //$dayInMonth = cal_days_in_month(CAL_GREGORIAN, $startDate->format('m'), $startDate->format('Y'));
                $arrayBitmask = str_split(str_repeat('0', 23));

                $sumFunc = function ($t1, $t2) {
                    return $t1 + $t2;
                };


                foreach ($trips as $trip) {
                    if (!$trip->getPeriodCalendar()) {
                        continue;  // ignore trip without period calendar
                    }
                    if (!$trip->getDayCalendar()) {
                        $bitmask = $this->calendarManager->getCalendarBitmask(
                            $trip->getPeriodCalendar()->getId(),
                            $startDate,
                            $endDate
                        );
                    }
                    else {
                        $bitmask = $this->calendarManager->getCalendarsIntersectionBitmask(
                            $trip->getPeriodCalendar()->getId(),
                            $trip->getDayCalendar()->getId(),
                            $startDate,
                            $endDate
                        );
                    }

                    $arrayBitmask = array_map($sumFunc, $arrayBitmask, str_split($bitmask));

                }

                // Rename array keys
                foreach ($arrayBitmask as $key => $value) {
                    $newKey = str_pad(($key + 1), 2, 0, STR_PAD_LEFT);
                    $arrayBitmask[$newKey . 'h'] = $value;
                    unset($arrayBitmask[$key]);
                }


                break;
            default:
                throw new \Exception('graph type unknown');

        }

        return $arrayBitmask;
    }
}
