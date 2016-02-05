<?php

/**
 * contao-calendar-filter
 *
 * Copyright © ContaoBlackForest
 *
 * @package   contao-calendar-filter
 * @author    Sven Baumann <baumann.sv@gmail.com>
 * @author    Dominik Tomasi <dominik.tomasi@gmail.com>
 * @license   LGPL-3.0+
 * @copyright Copyright 2016 ContaoBlackForest
 */

namespace ContaoBlackForest\Module\CalendarFilter\Event;

use Symfony\Component\EventDispatcher\Event;

class PostFilterInformationEvent extends Event
{
    const NAME = 'calendar.filter.post-information';

    protected $filter;

    protected $information;

    protected $events;

    public function __construct($filter, array $events)
    {
        $this->filter = $filter;
        $this->events = $events;
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
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @return mixed
     */
    public function getInformation()
    {
        return $this->information;
    }

    /**
     * @param mixed $information
     */
    public function setInformation(array $information)
    {
        $this->information = $information;
    }
}
