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
 * String Object
 *
 * @vendor  Eden
 * @package Type
 * @author  Christian Blanquera cblanquera@openovate.com
 */
class StringType extends Base
{

    protected static $methods = array(
        'addslashes'              => self::PRE,
        'bin2hex'                 => self::PRE,
        'chunk_split'             => self::PRE,
        'convert_uudecode'        => self::PRE,
        'convert_uuencode'        => self::PRE,
        'crypt'                   => self::PRE,
        'html_entity_decode'      => self::PRE,
        'htmlentities'            => self::PRE,
        'htmlspecialchars_decode' => self::PRE,
        'htmlspecialchars'        => self::PRE,
        'lcfirst'                 => self::PRE,
        'ltrim'                   => self::PRE,
        'md5'                     => self::PRE,
        'nl2br'                   => self::PRE,
        'quoted_printable_decode' => self::PRE,
        'quoted_printable_encode' => self::PRE,
        'quotemeta'               => self::PRE,
        'rtrim'                   => self::PRE,
        'sha1'                    => self::PRE,
        'sprintf'                 => self::PRE,
        'str_pad'                 => self::PRE,
        'str_repeat'              => self::PRE,
        'str_rot13'               => self::PRE,
        'str_shuffle'             => self::PRE,
        'strip_tags'              => self::PRE,
        'stripcslashes'           => self::PRE,
        'stripslashes'            => self::PRE,
        'strpbrk'                 => self::PRE,
        'stristr'                 => self::PRE,
        'strrev'                  => self::PRE,
        'strstr'                  => self::PRE,
        'strtok'                  => self::PRE,
        'strtolower'              => self::PRE,
        'strtoupper'              => self::PRE,
        'strtr'                   => self::PRE,
        'substr_replace'          => self::PRE,
        'substr'                  => self::PRE,
        'trim'                    => self::PRE,
        'ucfirst'                 => self::PRE,
        'ucwords'                 => self::PRE,
        'vsprintf'                => self::PRE,
        'wordwrap'                => self::PRE,
        'count_chars'             => self::PRE,
        'hex2bin'                 => self::PRE,
        'strlen'                  => self::PRE,
        'strpos'                  => self::PRE,
        'substr_compare'          => self::PRE,
        'substr_count'            => self::PRE,
        'str_ireplace'            => self::POST,
        'str_replace'             => self::POST,
        'preg_replace'            => self::POST,
        'explode'                 => self::POST
    );

    /**
     * Preloads the string
     *
     * @param scalar
     *
     * @return mixed
     */
    public function __construct( $data )
    {

        //argument 1 must be scalar
        Argument::i()->test( 1, 'scalar' );

        $data = (string)$data;

        parent::__construct( $data );
    }

    /**
     * If we output this to string we should see the raw string
     *
     * @return string
     */
    public function __toString()
    {

        return $this->data;
    }

    /**
     * Camelizes a string
     *
     * @param string prefix
     *
     * @return Eden\Type\Type\StringType
     */
    public function camelize( $prefix = '-' )
    {

        //argument 1 must be a string
        Argument::i()->test( 1, 'string' );
        $this->data = str_replace( $prefix, ' ', $this->data );
        $this->data = str_replace( ' ', '', ucwords( $this->data ) );
        $this->data = strtolower( substr( $this->data, 0, 1 ) ).substr( $this->data, 1 );

        return $this;
    }

    /**
     * Transforms a string with caps and
     * space to a lower case dash string
     *
     * @return Eden\Type\Type\StringType
     */
    public function dasherize()
    {

        $this->data = preg_replace( "/[^a-zA-Z0-9_-\s]/i", '', $this->data );
        $this->data = str_replace( ' ', '-', trim( $this->data ) );
        $this->data = preg_replace( "/-+/i", '-', $this->data );
        $this->data = strtolower( $this->data );

        return $this;
    }

    /**
     * Titlizes a string
     *
     * @param string prefix
     *
     * @return Eden\Type\Type\StringType
     */
    public function titlize( $prefix = '-' )
    {

        //argument 1 must be a string
        Argument::i()->test( 1, 'string' );

        $this->data = ucwords( str_replace( $prefix, ' ', $this->data ) );

        return $this;
    }

    /**
     * Uncamelizes a string
     *
     * @param string prefix
     *
     * @return Eden\Type\Type\StringType
     */
    public function uncamelize( $prefix = '-' )
    {

        //argument 1 must be a string
        Argument::i()->test( 1, 'string' );

        $this->data = strtolower( preg_replace( "/([A-Z])/", $prefix."$1", $this->data ) );

        return $this;
    }

    /**
     * Summarizes a text
     *
     * @param int number of words
     *
     * @return Eden\Type\Type\StringType
     */
    public function summarize( $words )
    {

        //argument 1 must be an integer
        Argument::i()->test( 1, 'int' );

        $this->data = explode( ' ', strip_tags( $this->data ), $words );
        array_pop( $this->data );
        $this->data = implode( ' ', $this->data );

        return $this;
    }

    /**
     * A PHP method excepts arrays in 3 ways, first argument,
     * last argument and as a reference
     *
     * @param string
     *
     * @return string|false
     */
    protected function getMethodType( $name )
    {

        if (isset( self::$methods[$name] )) {
            return self::$methods[$name];
        }

        if (isset( self::$methods['str_'.$name] )) {
            $name = 'str_'.$name;
            return self::$methods[$name];
        }

        $uncamel = strtolower( preg_replace( "/([A-Z])/", "_$1", $name ) );

        if (isset( self::$methods[$uncamel] )) {
            $name = $uncamel;
            return self::$methods[$name];
        }

        if (isset( self::$methods['str_'.$uncamel] )) {
            $name = 'str_'.$uncamel;
            return self::$methods[$name];
        }

        return false;
    }

}
