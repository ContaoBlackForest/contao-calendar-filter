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

\Bit3\Contao\MetaPalettes\MetaPalettes::appendAfter(
    'tl_module',
    'eventlist',
    'config',
    array(
        'config_filter' => array('calendarFilterField', 'calendarFilterMergeMonth'),
    )
);

$fields = array(
    'calendarFilterField' => array(
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['calendarFilterField'],
        'exclude'          => true,
        'inputType'        => 'checkboxWizard',
        'options_callback' => array('ContaoBlackForest\Module\CalendarFilter\DataContainer\Module', 'getFilterFields'),
        'eval'             => array('multiple' => true),
        'sql'              => "blob NULL"
    ),

    'calendarFilterMergeMonth'   => array
    (
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['calendarFilterMergeMonth'],
        'exclude'   => true,
        'filter'    => true,
        'inputType' => 'checkbox',
        'sql'       => "char(1) NOT NULL default ''"
    ),
);

$GLOBALS['TL_DCA']['tl_module']['fields'] = array_merge($fields, $GLOBALS['TL_DCA']['tl_module']['fields']);
