<?php
namespace SPHERE\System\Extension;

use Markdownify\Converter;
use MOC\V\Component\Template\Template;
use MOC\V\Core\HttpKernel\HttpKernel;
use SPHERE\System\Cache\CacheFactory;
use SPHERE\System\Cache\Handler\HandlerInterface;
use SPHERE\System\Cache\Handler\MemoryHandler;
use SPHERE\System\Config\ConfigFactory;
use SPHERE\System\Config\Reader\IniReader;
use SPHERE\System\Config\Reader\ReaderInterface;
use SPHERE\System\Database\Fitting\Repository;
use SPHERE\System\Debugger\DebuggerFactory;
use SPHERE\System\Debugger\Logger\LoggerInterface;
use SPHERE\System\Extension\Repository\DataTables;
use SPHERE\System\Extension\Repository\Debugger;
use SPHERE\System\Extension\Repository\ModHex;
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

    /** @var null|ReaderInterface $CacheConfig */
    private static $CacheConfig = null;
    /** @var HandlerInterface[] $CacheRegister */
    private static $CacheRegister = array();
    /** @var null|ReaderInterface $LoggerConfig */
    private static $LoggerConfig = null;
    /** @var LoggerInterface[] $LoggerRegister */
    private static $LoggerRegister = array();

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

        if (null === self::$CacheConfig) {
            self::$CacheConfig = (new ConfigFactory())->createReader(
                __DIR__.'/../Cache/Configuration.ini',
                new IniReader()
            );
        }

        $Key = get_class($Handler).$Name;
        if (isset( self::$CacheRegister[$Key] )) {
            $Handler = self::$CacheRegister[$Key];
        } else {
            $Handler = (new CacheFactory())->createHandler($Handler, self::$CacheConfig, $Name);
            self::$CacheRegister[$Key] = $Handler;
        }
        return $Handler;
    }

    /**
     * @param LoggerInterface $Logger
     * @param string $Name
     *
     * @return LoggerInterface
     */
    public function getLogger(LoggerInterface $Logger, $Name = 'Debugger')
    {

        if (null === self::$LoggerConfig) {
            self::$LoggerConfig = (new ConfigFactory())->createReader(
                __DIR__ . '/../Debugger/Configuration.ini',
                new IniReader()
            );
        }

        $Key = get_class($Logger) . $Name;
        if (isset(self::$LoggerRegister[$Key])) {
            $Logger = self::$LoggerRegister[$Key];
        } else {
            $Logger = (new DebuggerFactory())->createLogger($Logger, self::$LoggerConfig, $Name);
            self::$LoggerRegister[$Key] = $Logger;
        }
        return $Logger;
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
        $Key = md5($Location);
        $Cache = $this->getCache( new MemoryHandler() );
        if( !($Template = $Cache->getValue( $Key, __METHOD__ )) ) {
            $Template = Template::getTemplate($Location);
            $Cache->setValue( $Key, $Template, 0, __METHOD__ );
            return clone $Template;
        } else {
            return clone $Template;
        }
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
     * @param array $List
     *
     * @return Sorter
     */
    public function getSorter($List)
    {

        return new Sorter($List);
    }
}
