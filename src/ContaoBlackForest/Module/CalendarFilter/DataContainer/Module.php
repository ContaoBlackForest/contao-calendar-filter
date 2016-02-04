<?php
/**
 * FRAMEWORK
 *
 * Copyright (C) FRAMEWORK
 *
 * @package   brugg-regio-ch
 * @file      Module.php
 * @author    Sven Baumann <baumann.sv@gmail.com>
 * @author    Dominik Tomasi <dominik.tomasi@gmail.com>
 * @license   GNU/LGPL
 * @copyright Copyright 2015 owner
 */


namespace ContaoBlackForest\Module\CalendarFilter\DataContainer;

/**
 * Class Module
 *
 * @package ContaoBlackForest\Module\CalendarFilter\DataContainer
 */
class Module
{
    public function getFilterFields(\DataContainer $dataContainer)
    {
        \Controller::loadDataContainer('tl_calendar_events');
        \Controller::loadLanguageFile('tl_calendar_events');

        global $TL_LANG;

        $options = array(
            'pid'       => $TL_LANG['tl_module']['pidCalendar'],
            'author'    => $TL_LANG['tl_calendar_events']['author'][0],
            'startDate' => $TL_LANG['tl_module']['startDateFilter'],
        );

        return $options;
    }
}
