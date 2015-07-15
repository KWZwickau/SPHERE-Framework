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
 * This is an abstract definition of common
 * folder manipulation listing and information
 * per folder.
 *
 * @vendor  Eden
 * @package System
 * @author  Christian Blanquera cblanquera@openovate.com
 */
class Folder extends Path
{

    const ERROR_CHMOD_IS_INVALID = 'Invalid permissions set when creating the folder %s.';

    /**
     * Creates a folder given the path
     *
     * @param int chmod
     *
     * @return this
     */
    public function create( $chmod = 0755 )
    {

        //argument 1 must be an integer
        Argument::i()->test( 1, 'int' );

        //if chmod is not and integer or not between 0 and 777
        if (!is_int( $chmod ) || $chmod < 0 || $chmod > 777) {
            //throw an error
            Exception::i( self::ERROR_CHMOD_IS_INVALID )
                ->addVariable( $this->data )
                ->trigger();
        }

        //if it's not a directory
        if (!is_dir( $this->data )) {
            //then make it
            mkdir( $this->data, $chmod, true );
        }

        return $this;
    }

    /**
     * Returns the name of the directory.. just the name
     *
     * @return string the name
     */
    public function getName()
    {

        $pathArray = $this->getArray();
        return array_pop( $pathArray );
    }

    /**
     * Checks to see if this
     * path is a real file
     *
     * @param string|null
     *
     * @return bool
     */
    public function isFolder( $path = null )
    {

        //argument 1 must be a string
        Argument::i()->test( 1, 'string', 'null' );

        //if path is string
        if (is_string( $path )) {
            //return path appended
            return is_dir( $this->data.'/'.$path );
        }

        return is_dir( $this->data );
    }

    /**
     * Removes a folder given the path
     *
     * @return Eden\System\Folder
     */
    public function remove()
    {

        //get absolute path
        $path = $this->absolute();

        //if it's a directory
        if (is_dir( $path )) {
            //remove it
            rmdir( $path );
        }

        return $this;
    }

    /**
     * Removes files and folder given a path
     *
     * @return Eden\System\Folder
     */
    public function truncate()
    {

        $this->removeFolders();
        $this->removeFiles();

        return $this;
    }

    /**
     * Removes a folder given the path and optionally the regular expression
     *
     * @param string regular expression
     *
     * @return Eden\System\Folder
     */
    public function removeFolders( $regex = null )
    {

        //argument 1 must be a string or null
        Argument::i()->test( 1, 'string', 'null' );

        $this->absolute();

        $folders = $this->getFolders( $regex );

        if (empty( $folders )) {
            return $this;
        }

        //walk directory
        foreach ($folders as $folder) {
            //remove directory
            $folder->remove();
        }

        return $this;
    }

    /**
     * Returns a list of folders given the path and optionally the regular expression
     *
     * @param string regular expression
     * @param bool
     *
     * @return array
     */
    public function getFolders( $regex = null, $recursive = false )
    {

        //argument test
        Argument::i()
            //argument 1 must be a string
            ->test( 1, 'string', 'null' )
            //argument 2 must be a boolean
            ->test( 2, 'bool' );

        $this->absolute();

        $folders = array();

        if ($handle = opendir( $this->data )) {
            //walk the directory
            while (false !== ( $folder = readdir( $handle ) )) {
                // If this is infact a directory
                //and if it matches the regex
                if ($folder != '.' && $folder != '..'
                    && filetype( $this->data.'/'.$folder ) == 'dir'
                    && ( !$regex || preg_match( $regex, $folder ) )
                ) {

                    //add it
                    $folders[] = self::i( $this->data.'/'.$folder );
                    if ($recursive) {
                        $subfolders = self::i( $this->data.'/'.$folder );
                        $folders = array_merge( $folders, $subfolders->getFolders( $regex, $recursive ) );
                    }

                }
            }
            closedir( $handle );
        }

        return $folders;
    }

    /**
     * Removes files given the path and optionally a regular expression
     *
     * @param string|null regular expression
     *
     * @return Eden\System\Folder
     */
    public function removeFiles( $regex = null )
    {

        //argument 1 must be a string
        Argument::i()->test( 1, 'string', 'null' );

        //get the files
        $files = $this->getFiles( $regex );

        if (empty( $files )) {
            return $this;
        }

        //walk the array
        foreach ($files as $file) {
            //remove everything
            $file->remove();
        }

        return $this;
    }

    /**
     * Returns a list of files given the path and optionally the pattern
     *
     * @param string|null regular expression
     * @param             bool
     *
     * @return array
     */
    public function getFiles( $regex = null, $recursive = false )
    {

        //argument test
        Argument::i()
            //argument 1 must be a string
            ->test( 1, 'string', 'null' )
            //argument 2 must be a boolean
            ->test( 2, 'bool' );

        $this->absolute();

        $files = array();

        if ($handle = opendir( $this->data )) {
            //for each file
            while (false !== ( $file = readdir( $handle ) )) {
                // If this is infact a file
                if (filetype( $this->data.'/'.$file ) == 'file'
                    && ( !$regex || preg_match( $regex, $file ) )
                ) {
                    //add it
                    $files[] = File::i( $this->data.'/'.$file );
                    // recursive and this is infact a directory
                } else {
                    if ($recursive && $file != '.' && $file != '..'
                        && filetype( $this->data.'/'.$file ) == 'dir'
                    ) {
                        $subfiles = self::i( $this->data.'/'.$file );
                        $files = array_merge( $files, $subfiles->getFiles( $regex, $recursive ) );
                    }
                }
            }

            closedir( $handle );
        }

        return $files;
    }
}
