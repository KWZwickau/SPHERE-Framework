<?php //-->
/*
 * This file is part of the Type package of the Eden PHP Library.
 * (c) 2013-2014 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE
 * distributed with this package.
 */

namespace Eden\Type;

/**
 * Array object
 *
 * @vendor  Eden
 * @package Type
 * @author  Christian Blanquera cblanquera@openovate.com
 */
class ArrayType extends Base implements \ArrayAccess, \Iterator, \Serializable, \Countable
{

    protected static $methods = array(
        'array_change_key_case'   => self::PRE,
        'array_chunk'             => self::PRE,
        'array_combine'           => self::PRE,
        'array_count_datas'       => self::PRE,
        'array_diff_assoc'        => self::PRE,
        'array_diff_key'          => self::PRE,
        'array_diff_uassoc'       => self::PRE,
        'array_diff_ukey'         => self::PRE,
        'array_diff'              => self::PRE,
        'array_fill_keys'         => self::PRE,
        'array_filter'            => self::PRE,
        'array_flip'              => self::PRE,
        'array_intersect_assoc'   => self::PRE,
        'array_intersect_key'     => self::PRE,
        'array_intersect_uassoc'  => self::PRE,
        'array_intersect_ukey'    => self::PRE,
        'array_intersect'         => self::PRE,
        'array_keys'              => self::PRE,
        'array_merge_recursive'   => self::PRE,
        'array_merge'             => self::PRE,
        'array_pad'               => self::PRE,
        'array_reverse'           => self::PRE,
        'array_shift'             => self::PRE,
        'array_slice'             => self::PRE,
        'array_splice'            => self::PRE,
        'array_sum'               => self::PRE,
        'array_udiff_assoc'       => self::PRE,
        'array_udiff_uassoc'      => self::PRE,
        'array_udiff'             => self::PRE,
        'array_uintersect_assoc'  => self::PRE,
        'array_uintersect_uassoc' => self::PRE,
        'array_uintersect'        => self::PRE,
        'array_unique'            => self::PRE,
        'array_datas'             => self::PRE,
        'count'                   => self::PRE,
        'current'                 => self::PRE,
        'each'                    => self::PRE,
        'end'                     => self::PRE,
        'extract'                 => self::PRE,
        'key'                     => self::PRE,
        'next'                    => self::PRE,
        'prev'                    => self::PRE,
        'sizeof'                  => self::PRE,
        'array_fill'              => self::POST,
        'array_map'               => self::POST,
        'array_search'            => self::POST,
        'compact'                 => self::POST,
        'implode'                 => self::POST,
        'in_array'                => self::POST,
        'array_unshift'           => self::REFERENCE,
        'array_walk_recursive'    => self::REFERENCE,
        'array_walk'              => self::REFERENCE,
        'arsort'                  => self::REFERENCE,
        'asort'                   => self::REFERENCE,
        'krsort'                  => self::REFERENCE,
        'ksort'                   => self::REFERENCE,
        'natcasesort'             => self::REFERENCE,
        'natsort'                 => self::REFERENCE,
        'reset'                   => self::REFERENCE,
        'rsort'                   => self::REFERENCE,
        'shuffle'                 => self::REFERENCE,
        'sort'                    => self::REFERENCE,
        'uasort'                  => self::REFERENCE,
        'uksort'                  => self::REFERENCE,
        'usort'                   => self::REFERENCE,
        'array_push'              => self::REFERENCE
    );
    protected $data = array();
    protected $original = array();

    /**
     * Preloads the array
     *
     * @param array
     *
     * @return mixed
     */
    public function __construct( $data = array() )
    {

        //if there is more arguments or data is not an array
        if (func_num_args() > 1 || !is_array( $data )) {
            //just get the args
            $data = func_get_args();
        }

        parent::__construct( $data );
    }

    /**
     * Try to see if this method is a PHP defined array function
     *
     * @param *string
     * @param *array
     *
     * @return mixed
     */
    public function __call( $name, $args )
    {

        Argument::i()
            //argument 1 must be a string
            ->test( 1, 'string' )
            //argument 2 must be an array
            ->test( 2, 'array' );

        //if the method starts with get
        if (strpos( $name, 'get' ) === 0) {
            //getUserName('-')
            $separator = '_';
            if (isset( $args[0] ) && is_scalar( $args[0] )) {
                $separator = (string)$args[0];
            }

            $key = preg_replace( "/([A-Z0-9])/", $separator."$1", $name );
            //get rid of get
            $key = strtolower( substr( $key, 3 + strlen( $separator ) ) );

            if (isset( $this->data[$key] )) {
                return $this->data[$key];
            }

            return null;
        } else {
            if (strpos( $name, 'set' ) === 0) {
                //setUserName('Chris', '-')
                $separator = '_';
                if (isset( $args[1] ) && is_scalar( $args[1] )) {
                    $separator = (string)$args[1];
                }

                $key = preg_replace( "/([A-Z0-9])/", $separator."$1", $name );

                //get rid of set
                $key = strtolower( substr( $key, 3 + strlen( $separator ) ) );

                $this->__set( $key, isset( $args[0] ) ? $args[0] : null );

                return $this;
            }
        }

        return parent::__call( $name, $args );
    }

    /**
     * Allow object property magic to redirect to the data variable
     *
     * @param *string
     * @param *mixed
     *
     * @return void
     */
    public function __set( $name, $value )
    {

        //argument 1 must be a string
        Argument::i()->test( 1, 'string' );

        $this->data[$name] = $value;
    }

    /**
     * If we output this to string we should see it as json
     *
     * @return string
     */
    public function __toString()
    {

        return json_encode( $this->get() );
    }

    /**
     * Copies the value of source key into destination key
     *
     * @param string
     * @param string
     *
     * @return Eden\Type\Type\ArrayType
     */
    public function copy( $source, $destination )
    {

        Argument::i()
            //argument 1 must be a string
            ->test( 1, 'string' )
            //argument 2 must be a string
            ->test( 2, 'string' );

        $this->data[$destination] = $this->data[$source];
        return $this;
    }

    /**
     * returns size using the Countable interface
     *
     * @return string
     */
    public function count()
    {

        return count( $this->data );
    }

    /**
     * Removes a row in an array and adjusts all the indexes
     *
     * @param *scalar the key to leave out
     *
     * @return Eden\Type\Type\ArrayType
     */
    public function cut( $key )
    {

        //argument 1 must be scalar
        Argument::i()->test( 1, 'scalar' );

        //if nothing to cut
        if (!isset( $this->data[$key] )) {
            //do nothing
            return $this;
        }

        //unset the value
        unset( $this->data[$key] );
        //reindex the list
        $this->data = array_values( $this->data );
        return $this;
    }

    /**
     * Returns the current item
     * For Iterator interface
     *
     * @return void
     */
    public function current()
    {

        return current( $this->data );
    }

    /**
     * Loops through returned result sets
     *
     * @param *callable
     *
     * @return Eden\Type\Type\ArrayType
     */
    public function each( $callback )
    {

        Argument::i()->test( 1, 'callable' );

        foreach ($this->data as $key => $value) {
            call_user_func( $callback, $key, $value );
        }

        return $this;
    }

    /**
     * Returns if the data is empty
     *
     * @return bool
     */
    public function isEmpty()
    {

        return empty( $this->data );
    }

    /**
     * Increases the position
     * For Iterator interface
     *
     * @return void
     */
    public function next()
    {

        next( $this->data );
    }

    /**
     * isset using the ArrayAccess interface
     *
     * @param *scalar|null|bool
     *
     * @return bool
     */
    public function offsetExists( $offset )
    {

        //argument 1 must be scalar, null or bool
        Argument::i()->test( 1, 'scalar', 'null', 'bool' );

        return isset( $this->data[$offset] );
    }

    /**
     * returns data using the ArrayAccess interface
     *
     * @param *scalar|null|bool
     *
     * @return bool
     */
    public function offsetGet( $offset )
    {

        //argument 1 must be scalar, null or bool
        Argument::i()->test( 1, 'scalar', 'null', 'bool' );

        return isset( $this->data[$offset] ) ? $this->data[$offset] : null;
    }

    /**
     * Sets data using the ArrayAccess interface
     *
     * @param *scalar|null|bool
     * @param mixed
     *
     * @return void
     */
    public function offsetSet( $offset, $value )
    {

        //argument 1 must be scalar, null or bool
        Argument::i()->test( 1, 'scalar', 'null', 'bool' );

        if (is_null( $offset )) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }

        return $this;
    }

    /**
     * unsets using the ArrayAccess interface
     *
     * @param *scalar|null|bool
     *
     * @return bool
     */
    public function offsetUnset( $offset )
    {

        //argument 1 must be scalar, null or bool
        Argument::i()->test( 1, 'scalar', 'null', 'bool' );

        unset( $this->data[$offset] );

        return $this;
    }

    /**
     * Inserts a row in an array after the given index and adjusts all the indexes
     *
     * @param *scalar the key we are looking for to past after
     * @param *mixed the value to paste
     * @param scalar the key to paste along with the value
     *
     * @return Eden\Type\Type\ArrayType
     */
    public function paste( $after, $value, $key = null )
    {

        //Argument test
        Argument::i()
            //Argument 1 must be a scalar
            ->test( 1, 'scalar' )
            //Argument 3 must be a scalar or null
            ->test( 3, 'scalar', 'null' );

        $list = array();
        //for each row
        foreach ($this->data as $i => $val) {
            //add this row back to the list
            $list[$i] = $val;

            //if this is not the key we are
            //suppose to paste after
            if ($after != $i) {
                //do nothing more
                continue;
            }

            //if there was a key involved
            if (!is_null( $key )) {
                //lets add the new value
                $list[$key] = $value;
                continue;
            }

            //lets add the new value
            $list[] = $arrayValue;
        }

        //if there was no key involved
        if (is_null( $key )) {
            //reindex the array
            $list = array_values( $list );
        }

        //give it back
        $this->data = $list;

        return $this;
    }

    /**
     * Rewinds the position
     * For Iterator interface
     *
     * @return void
     */
    public function rewind()
    {

        reset( $this->data );
    }

    /**
     * returns serialized data using the Serializable interface
     *
     * @return string
     */
    public function serialize()
    {

        return json_encode( $this->data );
    }

    /**
     * Sets data
     *
     * @param array
     *
     * @return Eden\Type\Type\ArrayType
     */
    public function set( $value )
    {

        //argument 1 must be an array
        //we test for array this way because the parent
        //does not specify data type
        Argument::i()->test( 1, 'array' );

        $this->data = $value;
        return $this;
    }

    /**
     * sets data using the Serializable interface
     *
     * @param string
     *
     * @return Eden\Type\Type\ArrayType
     */
    public function unserialize( $data )
    {

        //argument 1 must be a string
        Argument::i()->test( 1, 'string' );

        $this->data = json_decode( $data, true );

        return $this;
    }

    /**
     * Validates whether if the index is set
     * For Iterator interface
     *
     * @return bool
     */
    public function valid()
    {

        return isset( $this->data[$this->key()] );
    }

    /**
     * Returns th current position
     * For Iterator interface
     *
     * @return void
     */
    public function key()
    {

        return key( $this->data );
    }

    /**
     * A PHP method excepts arrays in 3 ways, first argument,
     * last argument and as a reference
     *
     * @param *string
     *
     * @return string
     */
    protected function getMethodType( $name )
    {

        if (isset( self::$methods[$name] )) {
            return self::$methods[$name];
        }

        if (isset( self::$methods['array_'.$name] )) {
            $name = 'array_'.$name;
            return self::$methods[$name];
        }

        $uncamel = strtolower( preg_replace( "/([A-Z])/", "_$1", $name ) );

        if (isset( self::$methods[$uncamel] )) {
            $name = $uncamel;
            return self::$methods[$name];
        }

        if (isset( self::$methods['array_'.$uncamel] )) {
            $name = 'array_'.$uncamel;
            return self::$methods[$name];
        }

        return false;
    }
}
