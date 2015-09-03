<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Forms\Controls;

use Nette;
use Nette\Utils\Html;

/**
 * Set of radio button controls.
 *
 * @author     David Grudl
 *
 * @property   array               $items
 * @property-read Nette\Utils\Html $separatorPrototype
 * @property-read Nette\Utils\Html $containerPrototype
 */
class RadioList extends BaseControl
{

    /** @var Nette\Utils\Html  separator element template */
    protected $separator;

    /** @var Nette\Utils\Html  container element template */
    protected $container;

    /** @var array */
    protected $items = array();


    /**
     * @param  string  label
     * @param  array   options from which to choose
     */
    public function __construct($label = null, array $items = null)
    {

        parent::__construct($label);
        $this->control->type = 'radio';
        $this->container = Html::el();
        $this->separator = Html::el('br');
        if ($items !== null) {
            $this->setItems($items);
        }
    }

    /**
     * Has been any radio button selected?
     *
     * @return bool
     */
    public function isFilled()
    {

        return $this->getValue() !== null;
    }

    /**
     * Returns selected radio value.
     *
     * @param  bool
     *
     * @return mixed
     */
    public function getValue($raw = false)
    {

        return is_scalar($this->value) && ( $raw || isset( $this->items[$this->value] ) ) ? $this->value : null;
    }

    /**
     * Returns options from which to choose.
     *
     * @return array
     */
    final public function getItems()
    {

        return $this->items;
    }

    /**
     * Sets options from which to choose.
     *
     * @param  array
     *
     * @return RadioList  provides a fluent interface
     */
    public function setItems(array $items)
    {

        $this->items = $items;
        return $this;
    }

    /**
     * Returns separator HTML element template.
     *
     * @return Nette\Utils\Html
     */
    final public function getSeparatorPrototype()
    {

        return $this->separator;
    }


    /**
     * Returns container HTML element template.
     *
     * @return Nette\Utils\Html
     */
    final public function getContainerPrototype()
    {

        return $this->container;
    }


    /**
     * Generates control's HTML element.
     *
     * @param  mixed
     *
     * @return Nette\Utils\Html
     */
    public function getControl($key = null)
    {

        if ($key === null) {
            $container = clone $this->container;
            $separator = (string)$this->separator;

        } elseif (!isset( $this->items[$key] )) {
            return null;
        }

        $control = parent::getControl();
        $id = $control->id;
        $counter = -1;
        $value = $this->value === null ? null : (string)$this->getValue();
        $label = Html::el('label');

        foreach ($this->items as $k => $val) {
            $counter++;
            if ($key !== null && (string)$key !== (string)$k) {
                continue;
            }

            $control->id = $label->for = $id.'-'.$counter;
            $control->checked = (string)$k === $value;
            $control->value = $k;

            if ($val instanceof Html) {
                $label->setHtml($val);
            } else {
                $label->setText($this->translate((string)$val));
            }

            if ($key !== null) {
                return Html::el()->add($control)->add($label);
            }

            $container->add((string)$control.(string)$label.$separator);
            $control->data('nette-rules', null);
            // TODO: separator after last item?
        }

        return $container;
    }


    /**
     * Generates label's HTML element.
     *
     * @param  string
     *
     * @return void
     */
    public function getLabel($caption = null)
    {

        $label = parent::getLabel($caption);
        $label->for = null;
        return $label;
    }

}
