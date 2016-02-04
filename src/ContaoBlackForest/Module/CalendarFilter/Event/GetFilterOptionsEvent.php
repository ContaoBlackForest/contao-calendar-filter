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

class GetFilterOptionsEvent extends Event
{
    const NAME = 'calendar.filter.get-options';

    protected $options;

    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param mixed $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    public function setOption($index, $language)
    {
        if (empty($language)) {
            $language = $index;
        }

        $this->options[$index] = $language;
    }

    public function hasOption($optionName)
    {
        if (!empty($this->options)
            && array_key_exists($optionName, $this->options)
        ) {
            return true;
        }

        return false;
    }
}
