<?php
namespace SPHERE\System\Extension;

use Markdownify\Converter;
use MOC\V\Component\Template\Template;
use MOC\V\Core\HttpKernel\HttpKernel;
use SPHERE\System\Extension\Repository\Debugger;
use SPHERE\System\Extension\Repository\ModHex;
use SPHERE\System\Extension\Repository\SuperGlobal;
use SPHERE\System\Extension\Repository\Upload;

/**
 * Class Extension
 *
 * @package SPHERE\System\Extension
 */
class Extension
{

    /**
     * @return Debugger
     */
    public function getDebugger()
    {

        return new Debugger();
    }

    /**
     * @param string $String
     *
     * @return ModHex
     */
    public function getModHex($String)
    {

        return ModHex::withString($String);
    }

    /**
     * @return \MOC\V\Core\HttpKernel\Component\IBridgeInterface
     */
    public function getRequest()
    {

        return HttpKernel::getRequest();
    }

    /**
     * @param $Location
     *
     * @return \MOC\V\Component\Template\Component\IBridgeInterface
     */
    public function getTemplate($Location)
    {

        return Template::getTemplate($Location);
    }

    /**
     * @return SuperGlobal
     */
    public function getGlobal()
    {

        return new SuperGlobal($_GET, $_POST, $_SESSION);
    }

    /**
     * @param string $FileKey  Key-Name in $_FILES
     * @param string $Location Storage-Directory
     *
     * @return Upload
     */
    public function getUpload($FileKey, $Location)
    {

        return new Upload($FileKey, $Location);
    }

    /**
     * @return Converter
     */
    public function getMarkdownify()
    {

        return new Converter();
    }
}
