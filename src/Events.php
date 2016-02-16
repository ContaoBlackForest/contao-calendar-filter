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

namespace ContaoBlackForest\Module\CalendarFilter;

use Contao\Symfony\Component\Form\ContaoFormBuilder;
use ContaoBlackForest\Module\CalendarFilter\Event\GetFilterOptionsEvent;
use ContaoBlackForest\Module\CalendarFilter\Event\PostFilterEventsEvent;
use ContaoBlackForest\Module\CalendarFilter\Event\PostFilterInformationEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class Events
 *
 * @package ContaoBlackForest\Module\CalendarFilter
 */
class Events
{
    protected $events = array();

    protected $eventList = null;

    /**
     * @param array   $events
     * @param array   $calendars
     * @param         $startTime
     * @param         $endTime
     * @param \Module $eventList
     *
     * @return array
     */
    public function filterAllEvents(array $events, array $calendars, $startTime, $endTime, \Module &$eventList)
    {
        global $container;

        if (!$eventList->calendarFilterField
        ) {
            return $events;
        }

        $eventList->calendarFilterField = deserialize($eventList->calendarFilterField);

        if (!empty($eventList->calendarFilterField)) {
            if (empty($eventList->customTpl)) {
                $eventList->Template->setName('mod_eventlist_filter');
            }
            $eventList->Template->filterForm = '';

            if ($eventList->calendarFilterMergeMonth) {
                $eventList->calendarFilterField = implode(',', $eventList->calendarFilterField);
                $eventList->calendarFilterField = str_replace('startDate', 'startDate,mergeMonth', $eventList->calendarFilterField);
                $eventList->calendarFilterField = explode(',', $eventList->calendarFilterField);
            }
        }

        $this->eventList = &$eventList;
        $this->events    = $events;

        if ($eventList->getModel()->perPage) {
            $restorePost = \Session::getInstance()->get('eventlistfilterpost_' . $this->eventList->id);
            if ($restorePost) {
                foreach ($restorePost as $postField => $postValue) {
                    if (\Input::post($postField) === null) {
                        \Input::setPost($postField, $postValue);
                    }
                }
            }
        }

        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher = $container['event-dispatcher'];
        if (!$filter = \Session::getInstance()->get('eventlistfilter_' . $this->eventList->id)) {
            $filter = array();
            foreach ($eventList->calendarFilterField as $filterField) {
                $filter[$filterField] = '';
            }
        }

        foreach ($filter as $post => $value) {
            if ($postValue = \Input::post($post)) {
                $filterEventsEvent = new PostFilterEventsEvent($post, $postValue);
                $filterEventsEvent->setEvents($this->events);
                $eventDispatcher->dispatch(PostFilterEventsEvent::NAME, $filterEventsEvent);
                $this->events = $filterEventsEvent->getEvents();

                $filterOptionsEvent = new GetFilterOptionsEvent();
                $eventDispatcher->dispatch(GetFilterOptionsEvent::NAME, $filterOptionsEvent);

                if ($filterOptionsEvent->hasOption($post)) {
                    continue;
                }
                $this->filterCalendar($this->events, array('field' => $post, 'value' => $postValue));
            }
        }
        $this->getFilter($filter);

        if ($eventList->getModel()->perPage
            && $filter
        ) {
            $postSession = array();

            foreach (array_keys($filter) as $postField) {
                if (!$postValue = \Input::post($postField)) {
                    continue;
                }

                $postSession[$postField] = $postValue;
            }

            if (count($postSession) > 0) {
                \Session::getInstance()->set('eventlistfilterpost_' . $this->eventList->id, $postSession);
            }
        }

        $this->eventList->Template->filterForm = $this->compileFilterForm($filter);

        return $this->events;
    }

    protected function filterCalendar(array &$events, array $argument)
    {
        foreach ($events as $index => &$value) {
            if (array_key_exists($argument['field'], $value)) {
                if (array_key_exists($index, $events)
                    && ($value[$argument['field']] != $argument['value'])
                ) {
                    $timeRange = explode('-', $argument['value']);
                    if ((count($timeRange) === 2)
                        && \Validator::isNumeric($timeRange[0])
                        && \Validator::isNumeric($timeRange[1])
                        && (($value[$argument['field']] < $timeRange[0])
                            || ($value[$argument['field']] > $timeRange[1]))
                    ) {
                        unset($events[$index]);

                        continue;
                    }

                    if (count($timeRange) < 2) {
                        if ($this->eventList->calendarFilterMergeMonth) {
                            $filter = \Session::getInstance()->get('eventlistfilter_' . $this->eventList->id);

                            if (!array_key_exists($argument['value'], $filter['mergeMonth'])) {
                                unset($events[$index]);
                                continue;
                            }

                            $removeFromIndex = true;
                            foreach ($filter['mergeMonth'][$argument['value']] as $timeRange) {
                                $timeRange = explode('-', $timeRange);
                                if (($value[$argument['field']] > $timeRange[0])
                                    && ($value[$argument['field']] < $timeRange[1])
                                ) {
                                    $removeFromIndex = false;
                                }
                            }

                            if (!$removeFromIndex) {
                                continue;
                            }
                        }

                        unset($events[$index]);
                    }
                }
            }

            if (!array_key_exists($argument['field'], $value)) {
                $this->filterCalendar($value, $argument);
            }

            if (empty($value)) {
                unset($events[$index]);
            }
        }
    }

    protected function getFilter($filter)
    {
        if ($sessionFilter = \Session::getInstance()->get('eventlistfilter_' . $this->eventList->id)) {
            $filter = $sessionFilter;
        }

        $events = array();
        foreach ($this->events as $firstRow) {
            if (empty($firstRow)) {
                continue;
            }

            foreach ($firstRow as $row) {
                $events = array_merge(array_values($row), $events);
            }
        }

        if (empty($events)) {
            return null;
        }

        $filterAll = \Session::getInstance()->get('eventlistfilterall_' . $this->eventList->id);


        $resetFilterAll = true;
        if ($filterAll) {
            foreach (array_keys($filterAll) as $postField) {
                if (!\Input::post($postField)) {
                    continue;
                }

                $resetFilterAll = false;
            }
        }
        if (!$filterAll
            || $resetFilterAll
        ) {
            \Session::getInstance()->set('eventlistfilterall_' . $this->eventList->id, $filter);
            $filterAll = \Session::getInstance()->get('eventlistfilterall_' . $this->eventList->id);
        }

        $filter = array();
        foreach ($this->eventList->calendarFilterField as $field) {
            $filter[$field] = $this->getFilterFieldInformation($field, $events);
        }

        if ($this->eventList->calendarFilterMergeMonth
            && \Input::post('startDate')
        ) {
            $filter['mergeMonth'][\Input::post('startDate')] =
                $filterAll['mergeMonth'][\Input::post('startDate')];

            foreach ($filterAll['startDate'] as $date => $dateName) {
                $chunks = explode(' - ', $dateName);
                if ($chunks[0] != \Input::post('startDate')) {
                    continue;
                }

                $filter['startDate'][$date] = $dateName;
            }
        }

        $this->mergeFilterMonth($filter);

        if (!empty($filter)) {
            \Session::getInstance()->set('eventlistfilter_' . $this->eventList->id, $filter);
            \Session::getInstance()->set('eventlistfilterCount_' . $this->eventList->id, count($this->events));
        }

        return true;
    }

    protected function compileFilterForm()
    {
        $data = \Session::getInstance()->get('eventlistfilter_' . $this->eventList->id);

        $template = '';

        if (empty($data)) {
            return $template;
        }

        $form    = new ContaoFormBuilder();
        $builder = $form->getBuilder();

        $sortedData = array();
        foreach ($this->eventList->calendarFilterField as $sortKey) {
            $sortedData[$sortKey] = $data[$sortKey];
        }

        foreach ($sortedData as $name => $value) {
            if ($this->eventList->calendarFilterMergeMonth
                && $name === 'startDate'
            ) {
                continue;
            }

            $sort        = '';
            $choicesData = array();

            foreach ($value as $choicesName => $choicesValue) {
                if (!$choicesValue instanceof \Model) {
                    $sort = 'ksort';

                    $choicesData[$choicesName] = $choicesValue;

                    if ($name === 'mergeMonth') {
                        $choicesData[$choicesName] = $choicesName;
                    }
                }

                if ($choicesValue instanceof \Model) {
                    global $TL_DCA;
                    $sort = 'natcasesort';
                    /** @var \Model $choicesValue */
                    $foreignKey                = explode('.', $TL_DCA['tl_calendar_events']['fields'][$name]['foreignKey']);
                    $choicesData[$choicesName] = $choicesValue->$foreignKey[1];
                }

            }

            if (!empty($choicesData)) {
                $sort($choicesData);

                if ($name === 'mergeMonth') {
                    $name = 'startDate';

                    $sortChoicesData = array();
                    foreach (array_values($GLOBALS['TL_LANG']['MONTHS']) as $month) {
                        if (!array_key_exists($month, $choicesData)) {
                            continue;
                        }
                        $sortChoicesData[$month] = $choicesData[$month];
                    }

                    $choicesData = $sortChoicesData;
                }

                $builder->add(
                    $name,
                    'choice',
                    array(
                        'label'       => $GLOBALS['TL_LANG']['FMD']['eventfilter'][$name],
                        'empty_value' => $GLOBALS['TL_LANG']['FMD']['eventfilter']['pleaseSelect'],
                        'choices'     => $choicesData,
                        'data'        => \Input::post($name),
                        'attr'        => array(
                            'onchange' => 'this.form.submit()',
                            'class'    => 'styled_select tl_select',
                        )
                    )
                );
            }
        }

        $objPage = null;
        foreach (array_keys($data) as $comparison) {
            if (!$comparison) {
                continue;
            }

            global $objPage;
        }

        if ($objPage) {
            $action = \Controller::generateFrontendUrl(array('alias' => $objPage->alias));

            if ($action) {
                $builder->setAction($action);
            }

            $template = $form->getFormHandler()->getTwig()->getEnvironment()->render(
                'form/' . $this->eventList->calendarFilterTemplate,
                array(
                    'form' => $form->getBuilder()->getForm()->createView(),
                )
            );
        }

        return $template;
    }

    protected function getFilterFieldInformation($field, $data)
    {
        if (empty($field)
            && !is_array($data)
        ) {
            return null;
        }

        global $container,
               $TL_DCA;

        $information = array();

        if (empty($information)
            && array_key_exists($field, $TL_DCA['tl_calendar_events']['fields'])
            && array_key_exists('foreignKey', $TL_DCA['tl_calendar_events']['fields'][$field])
        ) {
            $cache      = array();
            $foreignKey = explode('.', $TL_DCA['tl_calendar_events']['fields'][$field]['foreignKey']);
            /** @var \Model $model */
            $model = \Model::getClassFromTable($foreignKey[0]);

            foreach ($data as $value) {
                if (!$value[$field]) {
                    continue;
                }

                if (!array_key_exists($value[$field], $cache)) {
                    $cache[$value[$field]] = $model::findByPk($value[$field]);
                }

                if (array_key_exists($value[$field], $cache)) {
                    $cache[$value[$field]] = $model::findByPk($value[$field]);

                    $information[$value[$field]] = $cache[$value[$field]];
                }
            }
        }

        if (empty($information)
            && array_key_exists('eval', $TL_DCA['tl_calendar_events']['fields']['startDate'])
            && array_key_exists('rgxp', $TL_DCA['tl_calendar_events']['fields']['startDate']['eval'])
            && ($TL_DCA['tl_calendar_events']['fields']['startDate']['eval']['rgxp'] === 'date')
        ) {
            foreach ($data as $value) {
                if ($value[$field]
                    && \Validator::isNumeric($value[$field])
                ) {
                    if (array_key_exists('start', $value)
                        && $value['start']
                        && $value['start'] < time()
                    ) {
                        continue;
                    }
                    if (array_key_exists('stop', $value)
                        && $value['stop']
                        && $value['stop'] > time()
                    ) {
                        continue;
                    }

                    $month      = \Date::parse('m::3', $value[$field]);
                    $year       = \Date::parse('Y', $value[$field]);
                    $stringDate = $month . ' - ' . $year;

                    if (!in_array($stringDate, $information)) {
                        $dateTime = new \DateTime();
                        $dateTime->setTimestamp($value[$field]);
                        $dateTime->modify('first day of this month');
                        $beginMonth = $dateTime->getTimestamp();
                        $dateTime->modify('last day of this month');
                        $endMonth = $dateTime->getTimestamp();

                        $information[$beginMonth . '-' . $endMonth] = $stringDate;
                    }
                }
            }
        }

        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher  = $container['event-dispatcher'];
        $informationEvent = new PostFilterInformationEvent($field, $data);
        $informationEvent->setInformation($information);
        $eventDispatcher->dispatch(PostFilterInformationEvent::NAME, $informationEvent);

        return $informationEvent->getInformation();
    }

    protected function mergeFilterMonth(&$filter)
    {
        if (!$this->eventList->calendarFilterMergeMonth
            || !array_key_exists('startDate', $filter)
        ) {
            return;
        }

        $mergeMonth = array();
        foreach ($filter['startDate'] as $monthRange => $month) {
            $chunks = explode(' - ', $month);

            $mergeMonth[$chunks[0]][$chunks[1]] = $monthRange;
        }

        $filter['mergeMonth'] = $mergeMonth;
    }
}
