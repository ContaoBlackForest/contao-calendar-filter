<?php

/**
 * contao-calendar-filter
 *
 * Copyright (C) ContaoBlackForest
 *
 * @package   contao-calendar-filter
 * @file      EventList.php
 * @author    Sven Baumann <baumann.sv@gmail.com>
 * @author    Dominik Tomasi <dominik.tomasi@gmail.com>
 * @license   LGPL-3.0+
 * @copyright Copyright 2015 ContaoBlackForest
 */


namespace ContaoBlackForestCalendarFilter;

use ContaoBlackForest\Module\CalendarFilter\EventList;

class ModuleEventlist extends \ContaoBlackForestCalendarFilterBridge\ModuleEventlist
{
    use EventList;
}
