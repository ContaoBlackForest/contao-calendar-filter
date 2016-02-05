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


namespace ContaoBlackForest\Module\CalendarFilter\DataContainer;

use ContaoBlackForest\Module\CalendarFilter\Event\GetFilterOptionsEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

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

        global $container,
               $TL_LANG;

        $options = array(
            'pid'       => $TL_LANG['tl_module']['pidCalendar'],
            'author'    => $TL_LANG['tl_calendar_events']['author'][0],
            'startDate' => $TL_LANG['tl_module']['startDateFilter'],
        );

        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher = $container['event-dispatcher'];
        $event = new GetFilterOptionsEvent();
        $event->setOptions($options);
        $eventDispatcher->dispatch(GetFilterOptionsEvent::NAME, $event);

        return $event->getOptions();
    }
}
