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

#$GLOBALS['FE_MOD']['events']['eventfilter'] = 'ContaoBlackForest\Module\CalendarFilter\ModuleEventFilter';

$GLOBALS['TL_HOOKS']['getAllEvents']['eventfilter'] = array('ContaoBlackForest\Module\CalendarFilter\Events', 'filterAllEvents');

/*$GLOBALS['TL_EXTEND']['ModuleEventlist'][] = array(
    'namespace' => 'ContaoBlackForest',
    'path'      => 'system/modules/calendar-filter/modules/ModuleEventlist.php'
);*/
