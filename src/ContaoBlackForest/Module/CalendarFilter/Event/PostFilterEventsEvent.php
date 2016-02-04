<?php

/**
 * contao-calendar-filter
 *
 * Copyright © ContaoBlackForest
 *
 * @package   contao-calendar-filter
 * @file      tl_module.php
 * @author    Sven Baumann <baumann.sv@gmail.com>
 * @author    Dominik Tomasi <dominik.tomasi@gmail.com>
 * @license   LGPL-3.0+
 * @copyright Copyright 2016 ContaoBlackForest
 */

namespace ContaoBlackForest\Module\CalendarFilter\Event;

use Symfony\Component\EventDispatcher\Event;

class PostFilterEventsEvent extends Event
{
    const NAME = 'calendar.filter.post-events';

    protected $filter;

    protected $field;

    protected $events;

    public function __construct($field, $filter)
    {
        $this->filter = $filter;
        $this->field = $field;
    }

    /**
     * @return mixed
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return mixed
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @param mixed $events
     */
    public function setEvents(array $events)
    {
        $this->events = $events;
    }
}
