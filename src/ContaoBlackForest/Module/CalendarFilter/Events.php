<?php
/**
 * FRAMEWORK
 *
 * Copyright (C) FRAMEWORK
 *
 * @package   brugg-regio-ch
 * @file      Events.php
 * @author    Sven Baumann <baumann.sv@gmail.com>
 * @author    Dominik Tomasi <dominik.tomasi@gmail.com>
 * @license   GNU/LGPL
 * @copyright Copyright 2015 owner
 */


namespace ContaoBlackForest\Module\CalendarFilter;


/**
 * Class Events
 *
 * @package ContaoBlackForest\Module\CalendarFilter
 */
class Events
{
    /**
     * @param $arrCalendars
     * @param $intStart
     * @param $intEnd
     */
    public function getAllEvents($arrEvents, $arrCalendars, $intStart, $intEnd, $eventList)
    {
        if ($filter = \Session::getInstance()->get('eventlistfilter')) {
            foreach ($filter as $post => $value) {
                if ($postValue = \Input::post($post)) {
                    $this->filterCalendar($arrEvents, array('field' => $post, 'value' => $postValue), $eventList);
                }
            }
        }

        return $arrEvents;
    }

    protected function filterCalendar(&$calendars, array $argument, $eventList)
    {
        foreach ($calendars as $index => &$value) {
            if (array_key_exists($argument['field'], $value)) {
                if (array_key_exists($index, $calendars)
                    && ($value[$argument['field']] != $argument['value'])
                ) {
                    $timeRange = explode('-', $argument['value']);
                    if ((count($timeRange) === 2)
                        && \Validator::isNumeric($timeRange[0])
                        && \Validator::isNumeric($timeRange[1])
                        && (($value[$argument['field']] < $timeRange[0])
                            || ($value[$argument['field']] > $timeRange[1]))
                    ) {
                        unset($calendars[$index]);

                        continue;
                    }

                    if (count($timeRange) < 2) {
                        if ($eventList->calendarFilterMergeMonth) {
                            $filter = \Session::getInstance()->get('eventlistfilter');

                            if (!array_key_exists($argument['value'], $filter['mergeMonth'])) {
                                unset($calendars[$index]);
                                continue;
                            }

                            $removeFromIndex = true;
                            foreach ($filter['mergeMonth'][$argument['value']] as $timeRange) {
                                $timeRange = explode('-', $timeRange);
                                if (($value[$argument['field']] > $timeRange[0])
                                    && ($value[$argument['field']] < $timeRange[1])
                                ) {
                                    $removeFromIndex = false;
                                }
                            }

                            if (!$removeFromIndex) {
                                continue;
                            }
                        }

                        unset($calendars[$index]);
                    }
                }
            }

            if (!array_key_exists($argument['field'], $value)) {
                $this->filterCalendar($value, $argument, $eventList);
            }

            if (empty($value)) {
                unset($calendars[$index]);
            }
        }
    }
}
