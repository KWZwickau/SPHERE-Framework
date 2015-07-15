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
 * General available methods for common file
 * manipulations and information per file
 *
 * @vendor  Eden
 * @package System
 * @author  Christian Blanquera cblanquera@openovate.com
 */
class File extends Path
{

    const ERROR_PATH_IS_NOT_FILE = 'Path %s is not a file in the system.';
    protected static $mimeTypes = array(
        'ai'      => 'application/postscript',
        'aif'     => 'audio/x-aiff',
        'aifc'    => 'audio/x-aiff',
        'aiff'    => 'audio/x-aiff',
        'asc'     => 'text/plain',
        'atom'    => 'application/atom+xml',
        'au'      => 'audio/basic',
        'avi'     => 'video/x-msvideo',
        'bcpio'   => 'application/x-bcpio',
        'bin'     => 'application/octet-stream',
        'bmp'     => 'image/bmp',
        'cdf'     => 'application/x-netcdf',
        'cgm'     => 'image/cgm',
        'class'   => 'application/octet-stream',
        'cpio'    => 'application/x-cpio',
        'cpt'     => 'application/mac-compactpro',
        'csh'     => 'application/x-csh',
        'css'     => 'text/css',
        'dcr'     => 'application/x-director',
        'dif'     => 'video/x-dv',
        'dir'     => 'application/x-director',
        'djv'     => 'image/vnd.djvu',
        'djvu'    => 'image/vnd.djvu',
        'dll'     => 'application/octet-stream',
        'dmg'     => 'application/octet-stream',
        'dms'     => 'application/octet-stream',
        'doc'     => 'application/msword',
        'dtd'     => 'application/xml-dtd',
        'dv'      => 'video/x-dv',
        'dvi'     => 'application/x-dvi',
        'dxr'     => 'application/x-director',
        'eps'     => 'application/postscript',
        'etx'     => 'text/x-setext',
        'exe'     => 'application/octet-stream',
        'ez'      => 'application/andrew-inset',
        'gif'     => 'image/gif',
        'gram'    => 'application/srgs',
        'grxml'   => 'application/srgs+xml',
        'gtar'    => 'application/x-gtar',
        'hdf'     => 'application/x-hdf',
        'hqx'     => 'application/mac-binhex40',
        'htm'     => 'text/html',
        'html'    => 'text/html',
        'ice'     => 'x-conference/x-cooltalk',
        'ico'     => 'image/x-icon',
        'ics'     => 'text/calendar',
        'ief'     => 'image/ief',
        'ifb'     => 'text/calendar',
        'iges'    => 'model/iges',
        'igs'     => 'model/iges',
        'jnlp'    => 'application/x-java-jnlp-file',
        'jp2'     => 'image/jp2',
        'jpe'     => 'image/jpeg',
        'jpeg'    => 'image/jpeg',
        'jpg'     => 'image/jpeg',
        'js'      => 'application/x-javascript',
        'kar'     => 'audio/midi',
        'latex'   => 'application/x-latex',
        'lha'     => 'application/octet-stream',
        'lzh'     => 'application/octet-stream',
        'm3u'     => 'audio/x-mpegurl',
        'm4a'     => 'audio/mp4a-latm',
        'm4b'     => 'audio/mp4a-latm',
        'm4p'     => 'audio/mp4a-latm',
        'm4u'     => 'video/vnd.mpegurl',
        'm4v'     => 'video/x-m4v',
        'mac'     => 'image/x-macpaint',
        'man'     => 'application/x-troff-man',
        'mathml'  => 'application/mathml+xml',
        'me'      => 'application/x-troff-me',
        'mesh'    => 'model/mesh',
        'mid'     => 'audio/midi',
        'midi'    => 'audio/midi',
        'mif'     => 'application/vnd.mif',
        'mov'     => 'video/quicktime',
        'movie'   => 'video/x-sgi-movie',
        'mp2'     => 'audio/mpeg',
        'mp3'     => 'audio/mpeg',
        'mp4'     => 'video/mp4',
        'mpe'     => 'video/mpeg',
        'mpeg'    => 'video/mpeg',
        'mpg'     => 'video/mpeg',
        'mpga'    => 'audio/mpeg',
        'ms'      => 'application/x-troff-ms',
        'msh'     => 'model/mesh',
        'mxu'     => 'video/vnd.mpegurl',
        'nc'      => 'application/x-netcdf',
        'oda'     => 'application/oda',
        'ogg'     => 'application/ogg',
        'pbm'     => 'image/x-portable-bitmap',
        'pct'     => 'image/pict',
        'pdb'     => 'chemical/x-pdb',
        'pdf'     => 'application/pdf',
        'pgm'     => 'image/x-portable-graymap',
        'pgn'     => 'application/x-chess-pgn',
        'pic'     => 'image/pict',
        'pict'    => 'image/pict',
        'png'     => 'image/png',
        'pnm'     => 'image/x-portable-anymap',
        'pnt'     => 'image/x-macpaint',
        'pntg'    => 'image/x-macpaint',
        'ppm'     => 'image/x-portable-pixmap',
        'ppt'     => 'application/vnd.ms-powerpoint',
        'ps'      => 'application/postscript',
        'qt'      => 'video/quicktime',
        'qti'     => 'image/x-quicktime',
        'qtif'    => 'image/x-quicktime',
        'ra'      => 'audio/x-pn-realaudio',
        'ram'     => 'audio/x-pn-realaudio',
        'ras'     => 'image/x-cmu-raster',
        'rdf'     => 'application/rdf+xml',
        'rgb'     => 'image/x-rgb',
        'rm'      => 'application/vnd.rn-realmedia',
        'roff'    => 'application/x-troff',
        'rtf'     => 'text/rtf',
        'rtx'     => 'text/richtext',
        'sgm'     => 'text/sgml',
        'sgml'    => 'text/sgml',
        'sh'      => 'application/x-sh',
        'shar'    => 'application/x-shar',
        'silo'    => 'model/mesh',
        'sit'     => 'application/x-stuffit',
        'skd'     => 'application/x-koan',
        'skm'     => 'application/x-koan',
        'skp'     => 'application/x-koan',
        'skt'     => 'application/x-koan',
        'smi'     => 'application/smil',
        'smil'    => 'application/smil',
        'snd'     => 'audio/basic',
        'so'      => 'application/octet-stream',
        'spl'     => 'application/x-futuresplash',
        'src'     => 'application/x-wais-source',
        'sv4cpio' => 'application/x-sv4cpio',
        'sv4crc'  => 'application/x-sv4crc',
        'svg'     => 'image/svg+xml',
        'swf'     => 'application/x-shockwave-flash',
        't'       => 'application/x-troff',
        'tar'     => 'application/x-tar',
        'tcl'     => 'application/x-tcl',
        'tex'     => 'application/x-tex',
        'texi'    => 'application/x-texinfo',
        'texinfo' => 'application/x-texinfo',
        'tif'     => 'image/tiff',
        'tiff'    => 'image/tiff',
        'tr'      => 'application/x-troff',
        'tsv'     => 'text/tab-separated-values',
        'txt'     => 'text/plain',
        'ustar'   => 'application/x-ustar',
        'vcd'     => 'application/x-cdlink',
        'vrml'    => 'model/vrml',
        'vxml'    => 'application/voicexml+xml',
        'wav'     => 'audio/x-wav',
        'wbmp'    => 'image/vnd.wap.wbmp',
        'wbmxl'   => 'application/vnd.wap.wbxml',
        'wml'     => 'text/vnd.wap.wml',
        'wmlc'    => 'application/vnd.wap.wmlc',
        'wmls'    => 'text/vnd.wap.wmlscript',
        'wmlsc'   => 'application/vnd.wap.wmlscriptc',
        'wrl'     => 'model/vrml',
        'xbm'     => 'image/x-xbitmap',
        'xht'     => 'application/xhtml+xml',
        'xhtml'   => 'application/xhtml+xml',
        'xls'     => 'application/vnd.ms-excel',
        'xml'     => 'application/xml',
        'xpm'     => 'image/x-xpixmap',
        'xsl'     => 'application/xml',
        'xslt'    => 'application/xslt+xml',
        'xul'     => 'application/vnd.mozilla.xul+xml',
        'xwd'     => 'image/x-xwindowdump',
        'xyz'     => 'chemical/x-xyz',
        'zip'     => 'application/zip',
        'woff'    => 'application/x-font-woff',
        'eot'     => 'application/vnd.ms-fontobject',
        'ttf'     => 'font/truetype',
        'otf'     => 'font/opentype'
    );
    protected $path = null;

    /**
     * Checks to see if this
     * path is a real file
     *
     * @return bool
     */
    public function isFile()
    {

        return file_exists( $this->data );
    }

    /**
     * Returns the base file name with out the extension
     *
     * @return string
     */
    public function getBase()
    {

        $pathInfo = pathinfo( $this->data );
        return $pathInfo['filename'];
    }

    /**
     * Returns the contents of a file given the path
     *
     * @return string
     */
    public function getContent()
    {

        $this->absolute();

        //if the pat is not a real file
        if (!is_file( $this->data )) {
            //throw an exception
            Exception::i()
                ->setMessage( self::ERROR_PATH_IS_NOT_FILE )
                ->addVariable( $this->data )
                ->trigger();
        }

        return file_get_contents( $this->data );
    }

    /**
     * Returns the executes the specified file and returns the final value
     *
     * @return bool
     */
    public function getData()
    {

        $this->absolute();

        return include( $this->data );
    }

    /**
     * Returns the file path
     *
     * @return string
     */
    public function getFolder()
    {

        return dirname( $this->data );
    }

    /**
     * Returns the mime type of a file
     *
     * @return string
     */
    public function getMime()
    {

        $this->absolute();

        //mime_content_type seems to be deprecated in some versions of PHP
        //if it does exist then lets use it
        if (function_exists( 'mime_content_type' )) {
            return mime_content_type( $this->data );
        }

        //if not then use the replacement funciton fileinfo
        //see: http://www.php.net/manual/en/function.finfo-file.php
        if (function_exists( 'finfo_open' )) {
            $resource = finfo_open( FILEINFO_MIME_TYPE );
            $mime = finfo_file( $resource, $this->data );
            finfo_close( $finfo );

            return $mime;
        }

        //ok we have to do this manually
        //get this file extension
        $extension = strtolower( $this->getExtension() );

        //get the list of mimetypes stored locally
        $types = self::$mimeTypes;
        //if this extension exissts in the types
        if (isset( $types[$extension] )) {
            //return the mimetype
            return $types[$extension];
        }

        //return text/plain by default
        return $types['class'];
    }

    /**
     * Returns the base file name extension
     *
     * @return string
     */
    public function getExtension()
    {

        $pathInfo = pathinfo( $this->data );

        if (!isset( $pathInfo['extension'] )) {
            return null;
        }

        return $pathInfo['extension'];
    }

    /**
     * Returns the file name
     *
     * @return string
     */
    public function getName()
    {

        return basename( $this->data );
    }

    /**
     * Returns the size of a file in bytes
     *
     * @return string
     */
    public function getSize()
    {

        $this->absolute();

        return filesize( $this->data );
    }

    /**
     * Returns the last time file was modified in UNIX time
     *
     * @return int
     */
    public function getTime()
    {

        $this->absolute();

        return filemtime( $this->data );
    }

    /**
     * Creates a php file and puts specified variable into that file
     *
     * @param *mixed
     *
     * @return Eden\System\File
     */
    public function setData( $variable )
    {

        return $this->setContent( "<?php //-->\nreturn ".var_export( $variable, true ).";" );
    }

    /**
     * Creates a file and puts specified content into that file
     *
     * @param *string content
     *
     * @return Eden\System\File
     */
    public function setContent( $content )
    {

        //argument 1 must be string
        Argument::i()->test( 1, 'string' );

        try {
            $this->absolute();
        } catch( Exception $e ) {
            $this->touch();
        }

        file_put_contents( $this->data, $content );

        return $this;
    }

    /**
     * Touches a file (effectively creates the file if
     * it doesn't exist and updates the date if it does)
     *
     * @return Eden\Utility\File
     */
    public function touch()
    {

        touch( $this->data );

        return $this;
    }

    /**
     * Removes a file
     *
     * @return Eden\Utility\File
     */
    public function remove()
    {

        $this->absolute();

        //if it's a file
        if (is_file( $this->data )) {
            //remove it
            unlink( $this->data );

            return $this;
        }

        return $this;
    }
}
