<?php
namespace SPHERE\System\Extension;

use Markdownify\Converter;
use MOC\V\Component\Template\Template;
use MOC\V\Core\HttpKernel\HttpKernel;
use SPHERE\System\Cache\CacheFactory;
use SPHERE\System\Cache\Handler\HandlerInterface;
use SPHERE\System\Config\ConfigFactory;
use SPHERE\System\Config\Reader\IniReader;
use SPHERE\System\Database\Fitting\Repository;
use SPHERE\System\Extension\Repository\DataTables;
use SPHERE\System\Extension\Repository\Debugger;
use SPHERE\System\Extension\Repository\ModHex;
use SPHERE\System\Extension\Repository\Roadmap;
use SPHERE\System\Extension\Repository\Sorter;
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
     * @return \MOC\V\Core\HttpKernel\Component\IBridgeInterface
     */
    public static function getRequest()
    {

        return HttpKernel::getRequest();
    }

    /**
     * @param HandlerInterface $Handler
     * @param string           $Name
     *
     * @return HandlerInterface
     */
    public function getCache(HandlerInterface $Handler, $Name = 'Memcached')
    {

        $Config = (new ConfigFactory())->createReader(__DIR__.'/../Cache/Configuration.ini', new IniReader());
        return (new CacheFactory())->createHandler($Handler, $Config, $Name);
    }

    /**
     * @return Debugger
     */
    public function getDebugger()
    {

        return new Debugger();
    }

    /**
     * @param Repository $EntityRepository
     * @param array      $Filter array( 'ColumnName' => 'Value', ... )
     *
     * @return DataTables
     */
    public function getDataTable(Repository $EntityRepository, $Filter = array())
    {

        return new DataTables($EntityRepository, $Filter);
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
     * @param string $FileKey   Key-Name in $_FILES
     * @param string $Location  Storage-Directory
     * @param bool   $Overwrite File in Storage-Directory
     *
     * @return Upload
     */
    public function getUpload($FileKey, $Location, $Overwrite = false)
    {

        return new Upload($FileKey, $Location, $Overwrite);
    }

    /**
     * @return Converter
     */
    public function getMarkdownify()
    {

        return new Converter();
    }

    /**
     * @return Roadmap
     */
    public function getRoadmap()
    {

        return new Roadmap();
    }

    /**
     * @param array $List
     *
     * @return Sorter
     */
    public function getSorter($List)
    {

        return new Sorter($List);
    }
}
