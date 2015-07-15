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

/**
 * Push button control with no default behavior.
 *
 * @author     David Grudl
 */
class Button extends BaseControl
{

    /**
     * @param  string  caption
     */
    public function __construct( $caption = null )
    {

        parent::__construct( $caption );
        $this->control->type = 'button';
    }


    /**
     * Bypasses label generation.
     *
     * @return void
     */
    public function getLabel( $caption = null )
    {

        return null;
    }


    /**
     * Generates control's HTML element.
     *
     * @param  string
     *
     * @return Nette\Utils\Html
     */
    public function getControl( $caption = null )
    {

        $control = parent::getControl();
        $control->value = $this->translate( $caption === null ? $this->caption : $caption );
        return $control;
    }

}
