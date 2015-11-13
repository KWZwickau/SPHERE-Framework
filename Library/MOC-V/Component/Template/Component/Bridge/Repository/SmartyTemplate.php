<?php
namespace MOC\V\Component\Template\Component\Bridge\Repository;

use MOC\V\Component\Template\Component\Bridge\Bridge;
use MOC\V\Component\Template\Component\IBridgeInterface;
use MOC\V\Component\Template\Component\Parameter\Repository\FileParameter;

/**
 * Class SmartyTemplate
 *
 * @package MOC\V\Component\Template\Component\Bridge
 */
class SmartyTemplate extends Bridge implements IBridgeInterface
{

    /** @var \Smarty $Instance */
    private $Instance = null;
    /** @var string $Template */
    private $Template = null;

    /**
     *
     */
    public function __construct()
    {

        if (!defined('SMARTY_DIR')) {
            define('SMARTY_DIR', realpath(__DIR__.'/../../../Vendor/Smarty/').DIRECTORY_SEPARATOR);
        }
        /** @noinspection PhpIncludeInspection */
        require_once( SMARTY_DIR.'Smarty.class.php' );
    }

    /**
     * @param FileParameter $Location
     * @param bool          $Reload
     *
     * @return IBridgeInterface
     */
    public function loadFile(FileParameter $Location, $Reload = false)
    {

        $this->createInstance($Reload);
        $this->Template = $Location->getFile();
        return $this;
    }

    /**
     * @param bool|false $Reload
     *
     * @return \Smarty
     */
    public function createInstance($Reload = false)
    {

        $this->Instance = new \Smarty();
        $this->Instance->caching = !$Reload;
        $this->Instance->compile_dir = __DIR__.'/SmartyTemplate';
        $this->Instance->cache_dir = __DIR__.'/SmartyTemplate';
        return $this->Instance;
    }

    /**
     * @return string
     */
    public function getContent()
    {

        $this->Instance->assign($this->VariableList, null, true);
        return $this->Instance->fetch($this->Template);
    }

}
