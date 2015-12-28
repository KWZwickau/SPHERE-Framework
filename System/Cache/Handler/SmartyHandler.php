<?php
namespace SPHERE\System\Cache\Handler;

use MOC\V\Component\Template\Component\Bridge\Repository\SmartyTemplate;
use SPHERE\System\Cache\CacheStatus;
use SPHERE\System\Config\Reader\ReaderInterface;
use SPHERE\System\Debugger\DebuggerFactory;
use SPHERE\System\Debugger\Logger\BenchmarkLogger;
use SPHERE\System\Debugger\Logger\ErrorLogger;

/**
 * Class SmartyHandler
 *
 * @package SPHERE\System\Cache\Handler
 */
class SmartyHandler extends AbstractHandler implements HandlerInterface
{

    private static $Cache = '/../../../Library/MOC-V/Component/Template/Component/Bridge/Repository/SmartyTemplate';

    /**
     * @param                 $Name
     * @param ReaderInterface $Config
     *
     * @return HandlerInterface
     */
    public function setConfig($Name, ReaderInterface $Config = null)
    {

        return $this;
    }

    /**
     * @param string $Key
     * @param mixed  $Value
     * @param int    $Timeout
     * @param string $Region
     *
     * @return SmartyHandler
     */
    public function setValue($Key, $Value, $Timeout = 0, $Region = 'Default')
    {

        // MUST NOT USE
        (new DebuggerFactory())->createLogger(new ErrorLogger())
            ->addLog(__METHOD__.' Error: SET - MUST NOT BE USED!');
        return $this;
    }

    /**
     * @param string $Key
     * @param string $Region
     *
     * @return mixed
     */
    public function getValue($Key, $Region = 'Default')
    {

        // MUST NOT USE
        (new DebuggerFactory())->createLogger(new ErrorLogger())
            ->addLog(__METHOD__.' Error: GET - MUST NOT BE USED!');
        return null;
    }

    /**
     * @return SmartyHandler
     */
    public function clearCache()
    {

        (new DebuggerFactory())->createLogger(new BenchmarkLogger())->addLog('Clear Smarty');
        (new SmartyTemplate())->createInstance()->clearAllCache();
        return $this;
    }

    /**
     * @return CacheStatus
     */
    public function getStatus()
    {

        (new DebuggerFactory())->createLogger(new BenchmarkLogger())->addLog('Status Smarty');
        return new CacheStatus(-1, -1, $this->calcStatusAvailable(), $this->calcStatusUsed(), $this->calcStatusFree(),
            $this->calcStatusAvailable() - $this->calcStatusFree() - $this->calcStatusUsed()
        );
    }

    /**
     * @return float
     */
    private function calcStatusAvailable()
    {

        return ( disk_total_space(__DIR__) );
    }

    /**
     * @return int
     */
    private function calcStatusUsed()
    {

        $Total = 0;
        $Path = realpath(__DIR__.self::$Cache);
        if ($Path !== false) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($Path,
                \FilesystemIterator::SKIP_DOTS)) as $Object) {
                $Total += $Object->getSize() * 1024;
            }
        }
        return $Total;
    }

    /**
     * @return float
     */
    private function calcStatusFree()
    {

        return ( disk_free_space(__DIR__) );
    }
}
