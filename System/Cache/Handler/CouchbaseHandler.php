<?php
namespace SPHERE\System\Cache\Handler;

use SPHERE\System\Cache\CacheFactory;
use SPHERE\System\Cache\CacheStatus;
use SPHERE\System\Config\Reader\ReaderInterface;
use SPHERE\System\Debugger\DebuggerFactory;
use SPHERE\System\Debugger\Logger\ErrorLogger;

/**
 * Class CouchbaseHandler
 * @package SPHERE\System\Cache\Handler
 */
class CouchbaseHandler extends AbstractHandler implements HandlerInterface
{

    /** @var null|\CouchbaseCluster $Connection */
    private $Connection = null;

    /**
     * @param $Name
     * @param ReaderInterface $Config
     * @return HandlerInterface
     */
    public function setConfig($Name, ReaderInterface $Config = null)
    {

        if (null === $this->Connection
            && null !== $Config
            && class_exists('\CouchbaseCluster', false)
        ) {
            $Value = $Config->getValue($Name);
            if ($Value) {
                $Host = $Value->getContainer('Host');
                if ($Host) {
                    $this->Connection = new \CouchbaseCluster('couchbase://' . (string)$Host);

                    (new DebuggerFactory())->createLogger(new ErrorLogger())
                        ->addLog(__METHOD__.' Error: Server not available -> Fallback');


                    return $this;
                } else {
                    (new DebuggerFactory())->createLogger(new ErrorLogger())
                        ->addLog(__METHOD__ . ' Error: Configuration not available -> Fallback');
                }
            } else {
                (new DebuggerFactory())->createLogger(new ErrorLogger())
                    ->addLog(__METHOD__ . ' Error: Configuration not available -> Fallback');
            }
        } else {
            if (null === $Config) {
                (new DebuggerFactory())->createLogger(new ErrorLogger())
                    ->addLog(__METHOD__ . ' Error: Configuration not available -> Fallback');
            } else {
                (new DebuggerFactory())->createLogger(new ErrorLogger())
                    ->addLog(__METHOD__.' Error: Server not available -> Fallback');
            }
        }
        return (new CacheFactory())->createHandler(new MemcachedHandler());
    }

    /**
     * @param string $Key
     * @param mixed $Value
     * @param int $Timeout
     * @param string $Region
     * @return CouchbaseHandler
     */
    public function setValue($Key, $Value, $Timeout = 0, $Region = 'Default')
    {

        return $this;
    }

    /**
     * @param string $Key
     * @param string $Region
     * @return mixed
     */
    public function getValue($Key, $Region = 'Default')
    {

        return null;
    }

    /**
     * @return CouchbaseHandler
     */
    public function clearCache()
    {

        return $this;
    }

    /**
     * @return CacheStatus
     */
    public function getStatus()
    {

        if (false) {
            $Status = $this->Connection->getStats();
            $Status = $Status[$this->Host.':'.$this->Port];
            return new CacheStatus(
                $Status['get_hits'], $Status['get_misses'], $Status['limit_maxbytes'],
                $Status['bytes'], $Status['limit_maxbytes'] - $Status['bytes'], 0
            );
        } else {
            return new CacheStatus();
        }
    }
}
