<?php

/**
 * contao-calendar-filter
 *
 * Copyright (C) ContaoBlackForest
 *
 * @package   contao-calendar-filter
 * @file      tl_module.php
 * @author    Sven Baumann <baumann.sv@gmail.com>
 * @author    Dominik Tomasi <dominik.tomasi@gmail.com>
 * @license   LGPL-3.0+
 * @copyright Copyright 2015 ContaoBlackForest
 */

echo "";

$GLOBALS['TL_DCA']['tl_module']['palettes']['eventlist'] = str_replace('cal_calendar', 'cal_calendar,filterField', $GLOBALS['TL_DCA']['tl_module']['palettes']['eventlist']);

$fields = array(
    'filterField' => array(
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['cal_calendar'],
        'exclude'          => true,
        'inputType'        => 'checkboxWizard',
        'options_callback' => array('ContaoBlackForest\Module\CalendarFilter\DataContainer\Module', 'getFilterFields'),
        'eval'             => array('multiple' => true),
        'sql'              => "blob NULL"
    )
);

$GLOBALS['TL_DCA']['tl_module']['fields'] = array_merge($fields, $GLOBALS['TL_DCA']['tl_module']['fields']);
