<?php

/**
 * contao-calendar-filter
 *
 * Copyright (C) ContaoBlackForest
 *
 * @package   contao-calendar-filter
 * @file      config.php
 * @author    Sven Baumann <baumann.sv@gmail.com>
 * @author    Dominik Tomasi <dominik.tomasi@gmail.com>
 * @license   LGPL-3.0+
 * @copyright Copyright 2015 ContaoBlackForest
 */

#$GLOBALS['FE_MOD']['events']['eventfilter'] = 'ContaoBlackForest\Module\CalendarFilter\ModuleEventFilter';

$GLOBALS['TL_HOOKS']['getAllEvents'][] = array('ContaoBlackForest\Module\CalendarFilter\Events', 'getAllEvents');

$GLOBALS['TL_EXTEND']['ModuleEventlist'][] = array(
    'namespace' => 'ContaoBlackForest',
    'path'      => 'system/modules/calendar-filter/modules/ModuleEventlist.php'
);

