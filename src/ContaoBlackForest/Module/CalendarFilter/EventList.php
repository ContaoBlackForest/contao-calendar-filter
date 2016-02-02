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

namespace ContaoBlackForest\Module\CalendarFilter;

use Contao\Symfony\Component\Form\ContaoFormBuilder;
use ModuleEventlist;

/**
 * Class ModuleEventFilter
 *
 * @package ContaoBlackForest\Module\CalendarFilter
 */

/** @var \ModuleEventlist $this */
trait EventList
{
    /**
     * {@inheritDoc}
     */
    public function generate()
    {
        /** @var \ModuleEventlist $this */
        $this->calendarFilterField = deserialize($this->calendarFilterField);

        if (!empty($this->calendarFilterField)) {
            $this->strTemplate = 'mod_eventlist_filter';
            $this->filterForm  = '';

            $GLOBALS['TL_LANG']['FMD']['eventlist'][0] = $GLOBALS['TL_LANG']['FMD']['eventfilter'][0];

            if ($this->calendarFilterMergeMonth) {
                $this->calendarFilterField = implode(',', $this->calendarFilterField);
                $this->calendarFilterField = str_replace('startDate', 'startDate,mergeMonth', $this->calendarFilterField);
                $this->calendarFilterField = explode(',', $this->calendarFilterField);
            }
        }

        return parent::generate();
    }

    /**
     * {@inheritDoc}
     */
    public function compile()
    {
        parent::compile();

        $this->getFilter();

        if ($this->Template->eventcount === 0) {
            \Session::getInstance()->remove('eventlistfilter');
        }
    }

    protected function getFilter()
    {
        /** @var \ModuleEventlist $this */
        if (!$this->calendarFilterField
        ) {
            return null;
        }

        $countEvents = \Session::getInstance()->get('eventlistfilterCount');

        $filter = \Session::getInstance()->get('eventlistfilter');
        if ($filter
            && \Session::getInstance()->get('eventlistfilterCount') === count($this->arrEvents)
        ) {
            if (count($filter) === count($this->calendarFilterField)) {
                $this->Template->filterForm = $this->compileFilterForm($filter);

                return true;
            }
        }


        $events = array();
        foreach ($this->arrEvents as $firstRow) {
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

        $filter = array();
        foreach ($this->calendarFilterField as $field) {
            $filter[$field] = $this->getFilterFieldInformation($field, $events);
        }

        $this->mergeFilterMonth($filter);

        $this->Template->filterForm = $this->compileFilterForm($filter);

        if (!empty($filter)) {
            \Session::getInstance()->setData(array('eventlistfilter' => $filter));
            \Session::getInstance()->set('eventlistfilterCount', count($this->arrEvents));
        }

        return true;
    }

    protected function compileFilterForm($data)
    {
        $template = '';

        if (empty($data)) {
            return $template;
        }

        $form    = new ContaoFormBuilder();
        $builder = $form->getBuilder();

        $sortedData = array();
        foreach ($this->calendarFilterField as $sortKey) {
            $sortedData[$sortKey] = $data[$sortKey];
        }

        foreach ($sortedData as $name => $value) {
            if ($this->calendarFilterMergeMonth
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
                'form/bootstrap3.horizontal.html.twig',
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

        global $TL_DCA;
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
        return $information;
    }

    protected function mergeFilterMonth(&$filter)
    {
        if (!$this->calendarFilterMergeMonth
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
