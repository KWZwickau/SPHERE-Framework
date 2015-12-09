<?php
namespace SPHERE\System\Cache\Handler;

use SPHERE\System\Cache\CacheFactory;
use SPHERE\System\Cache\CacheStatus;
use SPHERE\System\Config\ConfigContainer;
use SPHERE\System\Config\Reader\ReaderInterface;
use SPHERE\System\Debugger\DebuggerFactory;
use SPHERE\System\Debugger\Logger\BenchmarkLogger;
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
    public $Connection = null;
    /** @var null|ConfigContainer $Config */
    private $Config = null;
    /** @var bool $isAvailable */
    private $isAvailable = true;

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
                    $this->isAvailable = false;
                    $this->setValue('CheckRunningStatus', true);
                    if ($this->getValue('CheckRunningStatus')) {
                        $this->isAvailable = true;
                        return $this;
                    } else {
                        (new DebuggerFactory())->createLogger(new ErrorLogger())
                            ->addLog(__METHOD__.' Error: Server not available -> Fallback to Memcached');
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

        $Data = serialize($Value);
        try {
            $this->getBucket()->upsert((string)$Region, json_encode(array('Key' => $Key, 'Value' => $Data)));
        } catch (\Exception $Exception) {
            (new DebuggerFactory())->createLogger(new ErrorLogger())
                ->addLog('Couchbase: Set '.$Region.' '.json_encode(
                        array(
                            'Key'   => $Key,
                            'Value' => $Data
                        )
                    ).' Failed');
        }
        Debugger::screenDump('S', $Key, $Value);
        return $this;
    }

    /**
     * @return \CouchbaseBucket
     * @throws \Exception
     */
    private function getBucket()
    {

        if ($this->isAvailable) {
            if (isset( $_SESSION['Memcached-Slot'] )) {
                $Bucket = $_SESSION['Memcached-Slot'];
            } else {
                $Bucket = 'PUBLIC';
            }
        } else {
            $Bucket = 'default';
        }

        (new DebuggerFactory())->createLogger(new BenchmarkLogger())->addLog('Bucket: '.$Bucket);

        try {
            return $this->Connection->openBucket($Bucket);
        } catch (\Exception $Exception) {
            throw new \Exception($Exception->getMessage().' ('.$Bucket.')', $Exception->getCode(), $Exception);
        }
    }

    /**
     * @param string $Key
     * @param string $Region
     *
     * @return null|mixed
     */
    public function getValue($Key, $Region = 'default')
    {

        try {
            $Value = json_decode($this->getBucket()->get($Region)->value);
            (new DebuggerFactory())->createLogger(new BenchmarkLogger())->addLog('Couchbase: Get '.$Region.' '.$Key.' OK');
        } catch (\Exception $Exception) {
            $Value = null;
            (new DebuggerFactory())->createLogger(new BenchmarkLogger())->addLog('Couchbase: Get '.$Region.' '.$Key.' FAIL');
        }
        Debugger::screenDump('U', $Key, $Value);
        return $Value;
    }

    /**
     * @param string $Region
     *
     * @return CouchbaseHandler
     */
    public function clearCache($Region = 'default')
    {

        (new DebuggerFactory())->createLogger(new BenchmarkLogger())->addLog('Couchbase: Flush');

        $this->getBucket()->manager()->flush();
        return $this;
    }

    public function removeValue($Key, $Region = 'default')
    {

        try {
            $this->getBucket()->remove($Region);
        } catch (\Exception $Exception) {
            throw new \Exception($Exception->getMessage().' ('.$Region.' -> '.$Key.' )', $Exception->getCode(),
                $Exception);
        }
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
