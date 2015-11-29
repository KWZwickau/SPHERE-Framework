<?php
namespace SPHERE\System\Cache\Handler;

use SPHERE\System\Cache\CacheFactory;
use SPHERE\System\Config\Reader\ReaderInterface;
use SPHERE\System\Debugger\DebuggerFactory;
use SPHERE\System\Debugger\Logger\ErrorLogger;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class CouchbaseHandler
 * @package SPHERE\System\Cache\Handler
 */
class CouchbaseHandler extends AbstractHandler implements HandlerInterface
{
    /** @var null|\Couchbase $Connection */
    private $Connection = null;

    /**
     * @param $Name
     * @param ReaderInterface $Config
     * @return HandlerInterface
     */
    public function setConfig($Name, ReaderInterface $Config = null)
    {

        Debugger::screenDump(func_get_args(), class_exists('\CouchbaseCluster'));

        if (null === $this->Connection
            && null !== $Config
            && class_exists('\CouchbaseCluster', false)
        ) {
            $Value = $Config->getValue($Name);
            if ($Value) {
                $Host = $Value->getContainer('Host');
                if ($Host) {
                    $this->Connection = new \CouchbaseCluster('couchbase://' . (string)$Host);

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
            }
        }
        return (new CacheFactory())->createHandler(new DefaultHandler());
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
}
