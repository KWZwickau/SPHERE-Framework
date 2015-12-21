<?php
namespace SPHERE\System\Cache\Handler;

use SPHERE\System\Cache\CacheFactory;
use SPHERE\System\Cache\CacheStatus;
use SPHERE\System\Config\Reader\ReaderInterface;
use SPHERE\System\Debugger\DebuggerFactory;
use SPHERE\System\Debugger\Logger\BenchmarkLogger;
use SPHERE\System\Debugger\Logger\ErrorLogger;

/**
 * Class OpCacheHandler
 *
 * @package SPHERE\System\Cache\Handler
 */
class OpCacheHandler extends AbstractHandler implements HandlerInterface
{

    /**
     * @param ReaderInterface $Name
     * @param ReaderInterface $Config
     *
     * @return HandlerInterface
     */
    public function setConfig($Name, ReaderInterface $Config = null)
    {

        if (function_exists('opcache_reset')) {
            return $this;
        }
        (new DebuggerFactory())->createLogger(new ErrorLogger())
            ->addLog(__METHOD__.' Error: OpCache not available -> Fallback');
        return (new CacheFactory())->createHandler(new DefaultHandler());
    }

    /**
     * @param string $Key
     * @param mixed  $Value
     * @param int    $Timeout
     * @param string $Region
     *
     * @return OpCacheHandler
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
     * @return OpCacheHandler
     */
    public function clearCache()
    {

        (new DebuggerFactory())->createLogger(new BenchmarkLogger())->addLog('Clear OpCache');
        opcache_reset();
        return $this;
    }

    /**
     * @return CacheStatus
     */
    public function getStatus()
    {

        (new DebuggerFactory())->createLogger(new BenchmarkLogger())->addLog('Status OpCache');
        $Status = opcache_get_status();
        $Config = opcache_get_configuration();
        return new CacheStatus(
            $Status['opcache_statistics']['hits'], $Status['opcache_statistics']['misses'],
            $Config['directives']['opcache.memory_consumption'],
            $Status['memory_usage']['used_memory'], $Status['memory_usage']['free_memory'],
            $Status['memory_usage']['wasted_memory']
        );
    }
}
