<?php
namespace SPHERE\System\Cache\Handler;

use SPHERE\System\Cache\CacheFactory;
use SPHERE\System\Cache\CacheStatus;
use SPHERE\System\Config\ConfigContainer;
use SPHERE\System\Config\Reader\ReaderInterface;
use SPHERE\System\Debugger\DebuggerFactory;
use SPHERE\System\Debugger\Logger\ErrorLogger;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class CouchbaseHandler
 *
 * @package SPHERE\System\Cache\Handler
 */
class CouchbaseHandler extends AbstractHandler implements HandlerInterface
{

    /** @var null|\CouchbaseCluster $Connection */
    private $Connection = null;
    /** @var null|ConfigContainer $Config */
    private $Config = null;

    /**
     * @param                 $Name
     * @param ReaderInterface $Config
     *
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
                $this->Config = $Value;
                $Host = $Value->getContainer('Host');
                if ($Host) {
                    $this->Connection = new \CouchbaseCluster('couchbase://'.(string)$Host);
                    $this->setValue('CheckRunningStatus', true);
                    if (true === $this->getValue('CheckRunningStatus')) {
                        $this->Connection->openBucket('default')->remove('CheckRunningStatus');
                        return $this;
                    } else {
                        (new DebuggerFactory())->createLogger(new ErrorLogger())
                            ->addLog(__METHOD__.' Error: Server not available -> Fallback');
                    }
                } else {
                    (new DebuggerFactory())->createLogger(new ErrorLogger())
                        ->addLog(__METHOD__.' Error: Configuration not available -> Fallback to Memcached');
                }
            } else {
                (new DebuggerFactory())->createLogger(new ErrorLogger())
                    ->addLog(__METHOD__.' Error: Configuration not available -> Fallback to Memcached');
            }
        } else {
            if (null === $Config) {
                (new DebuggerFactory())->createLogger(new ErrorLogger())
                    ->addLog(__METHOD__.' Error: Configuration not available -> Fallback to Memcached');
            } else {
                (new DebuggerFactory())->createLogger(new ErrorLogger())
                    ->addLog(__METHOD__.' Error: PHP-Module not available -> Fallback to Memcached');
            }
        }
        return (new CacheFactory())->createHandler(new MemcachedHandler());
    }

    /**
     * @param string $Key
     * @param mixed  $Value
     * @param int    $Timeout
     * @param string $Region
     *
     * @return CouchbaseHandler
     */
    public function setValue($Key, $Value, $Timeout = 0, $Region = 'default')
    {

        try {
            $this->Connection->openBucket($Region)->replace($Key, $Value);
        } catch (\Exception $Exception) {
            $this->Connection->openBucket($Region)->insert($Key, $Value);
        }
        return $this;
    }

    /**
     * @param string $Key
     * @param string $Region
     *
     * @return mixed
     */
    public function getValue($Key, $Region = 'default')
    {

        try {
            return $this->Connection->openBucket($Region)->get($Key)->value;
        } catch (\Exception $Exception) {
            return null;
        }
    }

    /**
     * @param string $Region
     *
     * @return CouchbaseHandler
     */
    public function clearCache($Region = 'default')
    {

        $this->Connection->openBucket($Region)->manager()->flush();
        return $this;
    }

    /**
     * @return CacheStatus
     */
    public function getStatus()
    {

        $Username = $this->Config->getContainer('Username');
        $Password = $this->Config->getContainer('Password');
        if ($Username && $Password) {
            $Status = $this->Connection->manager((string)$Username, (string)$Password)->info();
            Debugger::screenDump($Status);
            return new CacheStatus(-1, -1,
                $Status['storageTotals']['ram']['quotaTotal'],
                $Status['storageTotals']['ram']['usedByData'],
                $Status['storageTotals']['ram']['quotaUsed'] - $Status['storageTotals']['ram']['usedByData'],
                $Status['storageTotals']['ram']['quotaTotal'] - $Status['storageTotals']['ram']['quotaUsed']
            );
        }
        return new CacheStatus();
    }
}
