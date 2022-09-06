<?php
namespace MOC\V\Component\Mail\Component\Bridge\Repository;

use Eden\Mail\Smtp;
use MOC\V\Component\Mail\Component\Bridge\Bridge;
use MOC\V\Component\Mail\Component\IBridgeInterface;
use MOC\V\Component\Mail\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Mail\Exception\MailException;
use MOC\V\Core\AutoLoader\AutoLoader;

/**
 * Class EdenPhpSmtp
 *
 * @package MOC\V\Component\Mail\Component\Bridge\Repository
 */
class EdenPhpSmtp extends Bridge implements IBridgeInterface
{

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

    /** @var null|Smtp $Instance */
    private $Instance = null;
    /** @var array $Header */
    private $Header = array();

    /**
     *
     */
    public function __construct()
    {

        AutoLoader::getNamespaceAutoLoader('Eden\Mail',
            __DIR__.'/../../../Vendor/EdenPhpMail/1.0.3-Master',
            'Eden\Mail'
        );
        AutoLoader::getNamespaceAutoLoader('Eden\Core',
            __DIR__.'/../../../Vendor/EdenPhpMail/1.0.3-Master/vendor/eden/core/Eden/Core',
            'Eden\Core'
        );
        AutoLoader::getNamespaceAutoLoader('Eden\System',
            __DIR__.'/../../../Vendor/EdenPhpMail/1.0.3-Master/vendor/eden/system/Eden/System',
            'Eden\System'
        );
        AutoLoader::getNamespaceAutoLoader('Eden\Type',
            __DIR__.'/../../../Vendor/EdenPhpMail/1.0.3-Master/vendor/eden/type/Eden/Type',
            'Eden\Type'
        );
    }

    /**
     * @param string   $Host
     * @param string   $Username
     * @param string   $Password
     * @param null|int $Port
     * @param bool     $useSSL
     * @param bool     $useTLS
     *
     * @return EdenPhpSmtp
     * @throws MailException
     */
    public function connectServer($Host, $Username, $Password, $Port = null, $useSSL = false, $useTLS = false)
    {

        try {
            $this->Instance = new Smtp($Host, $Username, $Password, $Port, $useSSL, $useTLS);
            $this->Instance->connect();
        } catch (\Exception $Exception) {
            throw new MailException($Exception->getMessage(), $Exception->getCode(), $Exception);
        }
        return $this;
    }

    /**
     * @return EdenPhpSmtp
     * @throws MailException
     */
    public function disconnectServer()
    {

        try {
            $this->Instance->disconnect();
            $this->Instance->reset();
        } catch (\Exception $Exception) {
            throw new MailException($Exception->getMessage(), $Exception->getCode(), $Exception);
        }
        return $this;
    }

    /**
     * @param $Address
     *
     * @return EdenPhpSmtp
     */
    public function setFromHeader($Address)
    {

        $this->Header['From'] = $Address;
        return $this;
    }

    /**
     * @param $Address
     *
     * @return EdenPhpSmtp
     */
    public function setReplyHeader($Address)
    {

        $this->Header['Reply-To'] = $Address;
        return $this;
    }

    /**
     * @return EdenPhpSmtp
     * @throws MailException
     */
    public function sendMail()
    {

        try {
            $this->Instance->send($this->Header);
        } catch (\Exception $Exception) {
            throw new MailException($Exception->getMessage(), $Exception->getCode(), $Exception);
        }
        return $this;
    }

    /**
     * @param string $Content
     *
     * @return EdenPhpSmtp
     * @throws MailException
     */
    public function setMailSubject($Content)
    {

        try {
            $this->Instance->setSubject($Content);
        } catch (\Exception $Exception) {
            throw new MailException($Exception->getMessage(), $Exception->getCode(), $Exception);
        }
        return $this;
    }

    /**
     * @param string $Content
     * @param bool   $useHtml
     *
     * @return EdenPhpSmtp
     * @throws MailException
     */
    public function setMailBody($Content, $useHtml = true)
    {

        try {
            $this->Instance->setBody($Content, $useHtml);
        } catch (\Exception $Exception) {
            throw new MailException($Exception->getMessage(), $Exception->getCode(), $Exception);
        }
        return $this;
    }

    /**
     * @param string      $Address
     * @param null|string $Name
     *
     * @return EdenPhpSmtp
     * @throws MailException
     */
    public function addRecipientTO($Address, $Name = null)
    {

        try {
            $this->Instance->addTo($Address, $Name);
        } catch (\Exception $Exception) {
            throw new MailException($Exception->getMessage(), $Exception->getCode(), $Exception);
        }
        return $this;
    }

    /**
     * @param string      $Address
     * @param null|string $Name
     *
     * @return EdenPhpSmtp
     * @throws MailException
     */
    public function addRecipientCC($Address, $Name = null)
    {

        try {
            $this->Instance->addCC($Address, $Name);
        } catch (\Exception $Exception) {
            throw new MailException($Exception->getMessage(), $Exception->getCode(), $Exception);
        }
        return $this;
    }

    /**
     * @param string      $Address
     * @param null|string $Name
     *
     * @return EdenPhpSmtp
     * @throws MailException
     */
    public function addRecipientBCC($Address, $Name = null)
    {

        try {
            $this->Instance->addBCC($Address, $Name);
        } catch (\Exception $Exception) {
            throw new MailException($Exception->getMessage(), $Exception->getCode(), $Exception);
        }
        return $this;
    }

//    /**
//     * @param FileParameter $File
//     *
//     * @return EdenPhpSmtp
//     * @throws MailException
//     */
//    public function addAttachment(FileParameter $File)
//    {
//
//        try {
//            $this->Instance->addAttachment($File->getFileInfo()->getRealPath(),
//                file_get_contents($File->getFileInfo()->getRealPath()));
//        } catch (\Exception $Exception) {
//            throw new MailException($Exception->getMessage(), $Exception->getCode(), $Exception);
//        }
//        return $this;
//    }

    /**
     * @param FileParameter $File
     *
     * @return EdenPhpSmtp
     * @throws MailException
     */
    public function addAttachment(FileParameter $File)
    {

        try {

            $mime = null;
            //mime_content_type seems to be deprecated in some versions of PHP
            //if it does exist then lets use it
            if (function_exists('mime_content_type') && !$mime) {
                $mime = mime_content_type($File->getFileInfo()->getRealPath());
            }

            //if not then use the replacement funciton fileinfo
            //see: http://www.php.net/manual/en/function.finfo-file.php
            if (function_exists('finfo_open' && !$mime)) {
                $resource = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($resource, $File->getFileInfo()->getRealPath());
                finfo_close($resource);
            }

            if(!$mime) {
                //ok we have to do this manually
                //get this file extension
                $extension = strtolower($File->getFileInfo()->getExtension());

                //get the list of mimetypes stored locally
                $types = self::$mimeTypes;
                //if this extension exissts in the types
                if (isset($types[$extension])) {
                    //return the mimetype
                    $mime = $types[$extension];
                }
            }

            if(!$mime) {
                $mime = 'text/plain';
            }

            $this->Instance->addAttachment(basename($File->getFileInfo()->getRealPath()),
                file_get_contents($File->getFileInfo()->getRealPath()),$mime);
        } catch (\Exception $Exception) {
            throw new MailException($Exception->getMessage(), $Exception->getCode(), $Exception);
        }
        return $this;
    }
}
