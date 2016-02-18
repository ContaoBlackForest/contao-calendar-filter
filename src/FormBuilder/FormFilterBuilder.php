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

namespace ContaoBlackForest\Module\CalendarFilter\FormBuilder;

use Contao\Symfony\Component\Form\ContaoFormBuilder;

/**
 * Class FormFilterBuilder
 *
 * @package ContaoBlackForest\Module\CalendarFilter\FormBuilder
 */
class FormFilterBuilder extends ContaoFormBuilder
{
    public function __construct()
    {
        parent::__construct();

        $this->setBuilder();
        $this->setRequestToken();
    }

    private function setOptions()
    {
        return array(
            'attr' => array(
                'novalidate' => 'novalidate'
            ),
        );
    }

    private function setDefaults()
    {
        return array(
            'csrf_protection' => false,
        );
    }

    private function setBuilder()
    {
        $this->builder = $this->getFactory()->createNamedBuilder('', 'form', $this->setDefaults(), $this->setOptions());
    }
}
