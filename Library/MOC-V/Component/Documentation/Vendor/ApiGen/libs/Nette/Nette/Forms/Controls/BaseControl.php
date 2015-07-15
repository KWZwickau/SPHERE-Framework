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
use Nette\Forms\Form;
use Nette\Forms\IControl;
use Nette\Forms\Rule;
use Nette\Utils\Html;

/**
 * Base class that implements the basic functionality common to form controls.
 *
 * @author     David Grudl
 *
 * @property-read Nette\Forms\Form                 $form
 * @property-read string                           $htmlName
 * @property   string                              $htmlId
 * @property-read array                            $options
 * @property   Nette\Localization\ITranslator|NULL $translator
 * @property   mixed                               $value
 * @property-read bool                             $filled
 * @property-write                                 $defaultValue
 * @property   bool                                $disabled
 * @property-read Nette\Utils\Html                 $control
 * @property-read Nette\Utils\Html                 $label
 * @property-read Nette\Utils\Html                 $controlPrototype
 * @property-read Nette\Utils\Html                 $labelPrototype
 * @property-read Nette\Forms\Rules                $rules
 * @property   bool                                $required
 * @property-read array                            $errors
 */
abstract class BaseControl extends Nette\ComponentModel\Component implements IControl
{

    /** @var string */
    public static $idMask = 'frm%s-%s';

    /** @var string textual caption or label */
    public $caption;

    /** @var mixed unfiltered control value */
    protected $value;

    /** @var Nette\Utils\Html  control element template */
    protected $control;

    /** @var Nette\Utils\Html  label element template */
    protected $label;

    /** @var array */
    private $errors = array();

    /** @var bool */
    private $disabled = false;

    /** @var string */
    private $htmlId;

    /** @var string */
    private $htmlName;

    /** @var Nette\Forms\Rules */
    private $rules;

    /** @var Nette\Localization\ITranslator */
    private $translator = true; // means autodetect

    /** @var array user options */
    private $options = array();


    /**
     * @param  string  caption
     */
    public function __construct( $caption = null )
    {

        $this->monitor( 'Nette\Forms\Form' );
        parent::__construct();
        $this->control = Html::el( 'input' );
        $this->label = Html::el( 'label' );
        $this->caption = $caption;
        $this->rules = new Nette\Forms\Rules( $this );
    }

    /**
     * Equal validator: are control's value and second parameter equal?
     *
     * @param  Nette\Forms\IControl
     * @param  mixed
     *
     * @return bool
     */
    public static function validateEqual( IControl $control, $arg )
    {

        $value = $control->getValue();
        foreach (( is_array( $value ) ? $value : array( $value ) ) as $val) {
            foreach (( is_array( $arg ) ? $arg : array( $arg ) ) as $item) {
                if ((string)$val === (string)( $item instanceof IControl ? $item->value : $item )) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Filled validator: is control filled?
     *
     * @param  Nette\Forms\IControl
     *
     * @return bool
     */
    public static function validateFilled( IControl $control )
    {

        return $control->isFilled();
    }

    /**
     * Valid validator: is control valid?
     *
     * @param  Nette\Forms\IControl
     *
     * @return bool
     */
    public static function validateValid( IControl $control )
    {

        return $control->rules->validate( true );
    }

    /**
     * Changes control's HTML attribute.
     *
     * @param  string name
     * @param  mixed  value
     *
     * @return BaseControl  provides a fluent interface
     */
    public function setAttribute( $name, $value = true )
    {

        $this->control->$name = $value;
        return $this;
    }

    /**
     * Returns user-specific option.
     *
     * @param  string key
     * @param  mixed  default value
     *
     * @return mixed
     */
    final public function getOption( $key, $default = null )
    {

        return isset( $this->options[$key] ) ? $this->options[$key] : $default;
    }

    /**
     * Returns user-specific options.
     *
     * @return array
     */
    final public function getOptions()
    {

        return $this->options;
    }

    /**
     * Is control filled?
     *
     * @return bool
     */
    public function isFilled()
    {

        return (string)$this->getValue() !== ''; // NULL, FALSE, '' ==> FALSE
    }

    /**
     * Returns control's value.
     *
     * @return mixed
     */
    public function getValue()
    {

        return $this->value;
    }

    /**
     * Sets control's value.
     *
     * @param  mixed
     *
     * @return BaseControl  provides a fluent interface
     */
    public function setValue( $value )
    {

        $this->value = $value;
        return $this;
    }



    /********************* translator ****************d*g**/

    /**
     * Sets control's default value.
     *
     * @param  mixed
     *
     * @return BaseControl  provides a fluent interface
     */
    public function setDefaultValue( $value )
    {

        $form = $this->getForm( false );
        if (!$form || !$form->isAnchored() || !$form->isSubmitted()) {
            $this->setValue( $value );
        }
        return $this;
    }

    /**
     * Returns form.
     *
     * @param  bool   throw exception if form doesn't exist?
     *
     * @return Nette\Forms\Form
     */
    public function getForm( $need = true )
    {

        return $this->lookup( 'Nette\Forms\Form', $need );
    }

    /**
     * Is control disabled?
     *
     * @return bool
     */
    public function isDisabled()
    {

        return $this->disabled;
    }



    /********************* interface IFormControl ****************d*g**/

    /**
     * Disables or enables control.
     *
     * @param  bool
     *
     * @return BaseControl  provides a fluent interface
     */
    public function setDisabled( $value = true )
    {

        $this->disabled = (bool)$value;
        return $this;
    }

    /**
     * Generates control's HTML element.
     *
     * @return Nette\Utils\Html
     */
    public function getControl()
    {

        $this->setOption( 'rendered', true );

        $control = clone $this->control;
        $control->name = $this->getHtmlName();
        $control->disabled = $this->disabled;
        $control->id = $this->getHtmlId();
        $control->required = $this->isRequired();

        $rules = self::exportRules( $this->rules );
        $rules = substr( PHP_VERSION_ID >= 50400 ? json_encode( $rules,
                JSON_UNESCAPED_UNICODE ) : json_encode( $rules ), 1, -1 );
        $rules = preg_replace( '#"([a-z0-9_]+)":#i', '$1:', $rules );
        $rules = preg_replace( '#(?<!\\\\)"(?!:[^a-z])([^\\\\\',]*)"#i', "'$1'", $rules );
        $control->data( 'nette-rules', $rules ? $rules : null );

        return $control;
    }

    /**
     * Sets user-specific option.
     * Options recognized by DefaultFormRenderer
     * - 'description' - textual or Html object description
     *
     * @param  string key
     * @param  mixed  value
     *
     * @return BaseControl  provides a fluent interface
     */
    public function setOption( $key, $value )
    {

        if ($value === null) {
            unset( $this->options[$key] );

        } else {
            $this->options[$key] = $value;
        }
        return $this;
    }

    /**
     * Returns HTML name of control.
     *
     * @return string
     */
    public function getHtmlName()
    {

        if ($this->htmlName === null) {
            $name = str_replace( self::NAME_SEPARATOR, '][', $this->lookupPath( 'Nette\Forms\Form' ), $count );
            if ($count) {
                $name = substr_replace( $name, '', strpos( $name, ']' ), 1 ).']';
            }
            if (is_numeric( $name ) || in_array( $name, array(
                        'attributes',
                        'children',
                        'elements',
                        'focus',
                        'length',
                        'reset',
                        'style',
                        'submit',
                        'onsubmit'
                    ) )
            ) {
                $name .= '_';
            }
            $this->htmlName = $name;
        }
        return $this->htmlName;
    }

    /**
     * Returns control's HTML id.
     *
     * @return string
     */
    public function getHtmlId()
    {

        if ($this->htmlId === false) {
            return null;

        } elseif ($this->htmlId === null) {
            $this->htmlId = sprintf( self::$idMask, $this->getForm()->getName(),
                $this->lookupPath( 'Nette\Forms\Form' ) );
        }
        return $this->htmlId;
    }

    /**
     * Changes control's HTML id.
     *
     * @param  string new ID, or FALSE or NULL
     *
     * @return BaseControl  provides a fluent interface
     */
    public function setHtmlId( $id )
    {

        $this->htmlId = $id;
        return $this;
    }

    /**
     * Is control mandatory?
     *
     * @return bool
     */
    final public function isRequired()
    {

        foreach ($this->rules as $rule) {
            if ($rule->type === Rule::VALIDATOR && !$rule->isNegative && $rule->operation === Form::FILLED) {
                return true;
            }
        }
        return false;
    }



    /********************* rendering ****************d*g**/

    /**
     * @return array
     */
    protected static function exportRules( $rules )
    {

        $payload = array();
        foreach ($rules as $rule) {
            if (!is_string( $op = $rule->operation )) {
                $op = new Nette\Callback( $op );
                if (!$op->isStatic()) {
                    continue;
                }
            }
            if ($rule->type === Rule::VALIDATOR) {
                $item = array(
                    'op'  => ( $rule->isNegative ? '~' : '' ).$op,
                    'msg' => $rules->formatMessage( $rule, false )
                );

            } elseif ($rule->type === Rule::CONDITION) {
                $item = array(
                    'op'      => ( $rule->isNegative ? '~' : '' ).$op,
                    'rules'   => self::exportRules( $rule->subRules ),
                    'control' => $rule->control->getHtmlName()
                );
                if ($rule->subRules->getToggles()) {
                    $item['toggle'] = $rule->subRules->getToggles();
                }
            }

            if (is_array( $rule->arg )) {
                foreach ($rule->arg as $key => $value) {
                    $item['arg'][$key] = $value instanceof IControl ? (object)array( 'control' => $value->getHtmlName() ) : $value;
                }
            } elseif ($rule->arg !== null) {
                $item['arg'] = $rule->arg instanceof IControl ? (object)array( 'control' => $rule->arg->getHtmlName() ) : $rule->arg;
            }

            $payload[] = $item;
        }
        return $payload;
    }

    /**
     * Generates label's HTML element.
     *
     * @param  string
     *
     * @return Nette\Utils\Html
     */
    public function getLabel( $caption = null )
    {

        $label = clone $this->label;
        $label->for = $this->getHtmlId();
        if ($caption !== null) {
            $label->setText( $this->translate( $caption ) );

        } elseif ($this->caption instanceof Html) {
            $label->add( $this->caption );

        } else {
            $label->setText( $this->translate( $this->caption ) );
        }
        return $label;
    }

    /**
     * Returns translated string.
     *
     * @param  string
     * @param  int      plural count
     *
     * @return string
     */
    public function translate( $s, $count = null )
    {

        $translator = $this->getTranslator();
        return $translator === null || $s == null ? $s : $translator->translate( $s, $count ); // intentionally ==
    }

    /**
     * Returns translate adapter.
     *
     * @return Nette\Localization\ITranslator|NULL
     */
    final public function getTranslator()
    {

        if ($this->translator === true) {
            return $this->getForm( false ) ? $this->getForm()->getTranslator() : null;
        }
        return $this->translator;
    }



    /********************* rules ****************d*g**/

    /**
     * Sets translate adapter.
     *
     * @param  Nette\Localization\ITranslator
     *
     * @return BaseControl  provides a fluent interface
     */
    public function setTranslator( Nette\Localization\ITranslator $translator = null )
    {

        $this->translator = $translator;
        return $this;
    }

    /**
     * Returns control's HTML element template.
     *
     * @return Nette\Utils\Html
     */
    final public function getControlPrototype()
    {

        return $this->control;
    }

    /**
     * Returns label's HTML element template.
     *
     * @return Nette\Utils\Html
     */
    final public function getLabelPrototype()
    {

        return $this->label;
    }

    /**
     * Adds a validation condition a returns new branch.
     *
     * @param  mixed      condition type
     * @param  mixed      optional condition arguments
     *
     * @return Nette\Forms\Rules      new branch
     */
    public function addCondition( $operation, $value = null )
    {

        return $this->rules->addCondition( $operation, $value );
    }

    /**
     * Adds a validation condition based on another control a returns new branch.
     *
     * @param  Nette\Forms\IControl form       control
     * @param                       mixed      condition type
     * @param                       mixed      optional condition arguments
     *
     * @return Nette\Forms\Rules      new branch
     */
    public function addConditionOn( IControl $control, $operation, $value = null )
    {

        return $this->rules->addConditionOn( $control, $operation, $value );
    }

    /**
     * @return Nette\Forms\Rules
     */
    final public function getRules()
    {

        return $this->rules;
    }

    /**
     * Makes control mandatory.
     *
     * @param  string  error message
     *
     * @return BaseControl  provides a fluent interface
     */
    final public function setRequired( $message = null )
    {

        return $this->addRule( Form::FILLED, $message );
    }



    /********************* validation ****************d*g**/

    /**
     * Adds a validation rule.
     *
     * @param  mixed      rule type
     * @param  string     message to display for invalid data
     * @param  mixed      optional rule arguments
     *
     * @return BaseControl  provides a fluent interface
     */
    public function addRule( $operation, $message = null, $arg = null )
    {

        $this->rules->addRule( $operation, $message, $arg );
        return $this;
    }

    /**
     * Adds error message to the list.
     *
     * @param  string  error message
     *
     * @return void
     */
    public function addError( $message )
    {

        if (!in_array( $message, $this->errors, true )) {
            $this->errors[] = $message;
        }
        $this->getForm()->addError( $message );
    }

    /**
     * Returns errors corresponding to control.
     *
     * @return array
     */
    public function getErrors()
    {

        return $this->errors;
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {

        return (bool)$this->errors;
    }

    /**
     * @return void
     */
    public function cleanErrors()
    {

        $this->errors = array();
    }

    /**
     * This method will be called when the component becomes attached to Form.
     *
     * @param  Nette\Forms\IComponent
     *
     * @return void
     */
    protected function attached( $form )
    {

        if (!$this->disabled && $form instanceof Form && $form->isAnchored() && $form->isSubmitted()) {
            $this->htmlName = null;
            $this->loadHttpData();
        }
    }

    /**
     * Loads HTTP data.
     *
     * @return void
     */
    public function loadHttpData()
    {

        $path = explode( '[', strtr( str_replace( array( '[]', ']' ), '', $this->getHtmlName() ), '.', '_' ) );
        $this->setValue( Nette\Utils\Arrays::get( $this->getForm()->getHttpData(), $path, null ) );
    }

}
