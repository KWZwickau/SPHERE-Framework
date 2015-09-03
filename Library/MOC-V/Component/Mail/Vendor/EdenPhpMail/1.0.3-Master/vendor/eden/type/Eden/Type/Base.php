<?php //-->
/*
 * This file is part of the Type package of the Eden PHP Library.
 * (c) 2013-2014 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE
 * distributed with this package.
 */

namespace Eden\Type;

use Eden\Core\Base as CoreBase;

/**
 * Base class for data type classes
 *
 * @vendor  Eden
 * @package Type
 * @author  Christian Blanquera cblanquera@openovate.com
 */
abstract class Base extends CoreBase
{

    const PRE = 'pre';
    const POST = 'post';
    const REFERENCE = 'reference';

    protected $data = null;
    protected $original = null;

    /**
     * Preset the data and the original
     *
     * @param *mixed
     *
     * @return void
     */
    public function __construct($data)
    {

        $this->original = $this->data = $data;
    }

    /**
     * Dermines if the missing method is actually a PHP call.
     * If so, call it.
     *
     * @param *string
     * @param *array
     *
     * @return mixed
     */
    public function __call($name, $args)
    {

        Argument::i()
            //argument 1 must be a string
            ->test(1, 'string')
            //argument 2 must be an array
            ->test(2, 'array');

        $type = $this->getMethodType($name);

        //if no type
        if (!$type) {
            //we don't process anything else
            //call the parent
            return parent::__call($name, $args);
        }

        //case different types
        switch ($type) {
            case self::PRE:
                //if pre, we add it first into the args
                array_unshift($args, $this->data);
                break;
            case self::POST:
                //if post, we add it last into the args
                array_push($args, $this->data);
                break;
            case self::REFERENCE:
                //if reference, we add it first
                //into the args and call it
                call_user_func_array($name, array_merge(array(&$this->data), $args));
                return $this;
        }

        //call the method
        $result = call_user_func_array($name, $args);

        //if the result is a string
        if (is_string($result)) {
            //if this class is a string type
            if ($this instanceof StringType) {
                //set value
                $this->data = $result;
                return $this;
            }

            //return string class
            return StringType::i($result);
        }

        //if the result is an array
        if (is_array($result)) {
            //if this class is a array type
            if ($this instanceof ArrayType) {
                //set value
                $this->data = $result;
                return $this;
            }

            //return array class
            return ArrayType::i($result);
        }

        return $result;
    }

    /**
     * A PHP method excepts string and arrays in 3 ways, first
     * argument, last argument and as a reference
     *
     * @param *string
     *
     * @return string
     */
    abstract protected function getMethodType($name);

    /**
     * Returns the value
     *
     * @param bool whether to get the modified or original version
     *
     * @return string
     */
    public function get($modified = true)
    {

        //argument 1 must be a bool
        Argument::i()->test(1, 'bool');

        return $modified ? $this->data : $this->original;
    }

    /**
     * Reverts changes back to the original
     *
     * @return Eden\Type\Type\Base
     */
    public function revert()
    {

        $this->data = $this->original;
        return $this;
    }

    /**
     * Sets data
     *
     * @return Eden\Type\Type\Base
     */
    public function set($value)
    {

        $this->data = $value;
        return $this;
    }
}
