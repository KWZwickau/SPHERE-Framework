<?php //-->
/*
 * This file is part of the System package of the Eden PHP Library.
 * (c) 2013-2014 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE
 * distributed with this package.
 */

namespace Eden\System;

/**
 * Core Factory Class
 *
 * @vendor  Eden
 * @package System
 * @author  Christian Blanquera cblanquera@openovate.com
 */
class Factory extends Base
{

    const INSTANCE = 1;

    /**
     * Returns the file class
     *
     * @param string
     *
     * @return Eden\System\File
     */
    public function file( $path )
    {

        //argument 1 must be a string
        Argument::i()->test( 1, 'string' );

        return File::i( $path );
    }

    /**
     * Returns the folder class
     *
     * @param string
     *
     * @return Eden\System\Folder
     */
    public function folder( $path )
    {

        //argument 1 must be a string
        Argument::i()->test( 1, 'string' );

        return Folder::i( $path );
    }

    /**
     * Returns the path class
     *
     * @param string
     *
     * @return Eden\System\Path
     */
    public function path( $path )
    {

        //argument 1 must be a string
        Argument::i()->test( 1, 'string' );

        return Path::i( $path );
    }
}
