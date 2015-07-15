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
 * The base class for all classes wishing to integrate with Eden.
 * Extending this class will allow your methods to seemlessly be
 * overloaded and overrided as well as provide some basic class
 * loading patterns.
 *
 * @vendor  Eden
 * @package Type
 * @author  Christian Blanquera cblanquera@openovate.com
 */
class Factory extends CoreBase
{

    /**
     * One of the hard thing about instantiating classes is
     * that design patterns can impose different ways of
     * instantiating a class. The word "new" is not flexible.
     * Authors of classes should be able to control how a class
     * is instantiated, while leaving others using the class
     * oblivious to it. All in all its one less thing to remember
     * for each class call. By default we instantiate classes with
     * this method.
     *
     * @param string|array|null
     *
     * @return object
     */
    public static function i( $type = null )
    {

        if (func_num_args() > 1) {
            $type = func_get_args();
        }

        if (is_array( $type )) {
            return ArrayType::i( $type );
        }

        if (is_string( $type )) {
            return StringType::i( $type );
        }

        return self::getSingleton( __CLASS__ );
    }

    /**
     * Returns the array class
     *
     * @param array|mixed[,mixed..]
     *
     * @return Eden\Type\Type\ArrayType
     */
    public function getArray( $array )
    {

        $args = func_get_args();
        if (count( $args ) > 1 || !is_array( $array )) {
            $array = $args;
        }

        return ArrayType::i( $array );
    }

    /**
     * Returns the string class
     *
     * @param string
     *
     * @return Eden\Type\Type\StringType
     */
    public function getString( $string )
    {

        //argument 1 must be a string
        Argument::i()->test( 1, 'string' );

        return StringType::i( $string );
    }

}
