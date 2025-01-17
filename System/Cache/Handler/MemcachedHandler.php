<?php

namespace SPHERE\System\Cache\Handler;

use SPHERE\System\Cache\CacheFactory;
use SPHERE\System\Cache\CacheStatus;
use SPHERE\System\Config\Reader\ReaderInterface;
use SPHERE\System\Debugger\DebuggerFactory;
use SPHERE\System\Debugger\Logger\BenchmarkLogger;
use SPHERE\System\Debugger\Logger\CacheLogger;
use SPHERE\System\Debugger\Logger\ErrorLogger;

/**
 * Class MemcachedHandler
 *
 * @package SPHERE\System\Cache\Handler
 */
class MemcachedHandler extends AbstractHandler implements HandlerInterface
{

    /** @var null|\Memcached $Connection */
    private static $Connection = null;

    private $Host = '';
    private $Port = '';
    /** @var bool $HashKey */
    private $HashKey = true;

    /**
     * @param bool $HashKey
     * @return MemcachedHandler
     */
    public function setHashKey($HashKey)
    {
        $this->HashKey = $HashKey;
        return $this;
    }

    /**
     * @param                 $Name
     * @param ReaderInterface $Config
     *
     * @return HandlerInterface
     */
    public function setConfig($Name, ReaderInterface $Config = null)
    {

        if (null === self::$Connection
            && null !== $Config
            && class_exists('\Memcached', false)
        ) {
            $Value = $Config->getValue($Name);
            if ($Value) {
                if ($Value->getContainer('Enabled') && $Value->getContainer('Enabled')->getValue()) {
                    $this->Host = $Value->getContainer('Host');
                    $this->Port = $Value->getContainer('Port');
                    if ($this->Host && $this->Port) {
                        self::$Connection = new \Memcached('pMC');
                        self::$Connection->setOption(\Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
                        self::$Connection->setOption(\Memcached::OPT_COMPRESSION, false);
                        self::$Connection->setOption(\Memcached::OPT_TCP_NODELAY, true);
                        self::$Connection->setOption(\Memcached::OPT_NO_BLOCK, true);
                        self::$Connection->setOption(\Memcached::OPT_CONNECT_TIMEOUT, 1);
                        if (!count(self::$Connection->getServerList())) {
                            if (self::$Connection->addServer((string)$this->Host, (string)$this->Port)) {
                                $this->setValue('CheckRunningStatus', true);
                                if (true === $this->getValue('CheckRunningStatus')) {
                                    self::$Connection->delete('CheckRunningStatus');
                                    return $this;
                                } else {
                                    (new DebuggerFactory())->createLogger(new ErrorLogger())
                                        ->addLog(__METHOD__ . ' Error: Server not available -> Fallback');
                                }
                            }
                        } else {
                            $this->setValue('CheckRunningStatus', true);
                            if (true === $this->getValue('CheckRunningStatus')) {
                                self::$Connection->delete('CheckRunningStatus');
                                return $this;
                            } else {
                                (new DebuggerFactory())->createLogger(new ErrorLogger())
                                    ->addLog(__METHOD__ . ' Error: Server not available -> Fallback');
                            }
                        }
                    } else {
                        (new DebuggerFactory())->createLogger(new ErrorLogger())
                            ->addLog(__METHOD__ . ' Error: Configuration not available -> Fallback');
                    }
                } else {
                    (new DebuggerFactory())->createLogger(new ErrorLogger())
                        ->addLog(__METHOD__ . ' Error: MemcachedHandler is Disabled -> Fallback');
                }
            } else {
                (new DebuggerFactory())->createLogger(new ErrorLogger())
                    ->addLog(__METHOD__ . ' Error: Configuration not available -> Fallback');
            }
        } else {
            if (null === $Config) {
                (new DebuggerFactory())->createLogger(new ErrorLogger())
                    ->addLog(__METHOD__ . ' Error: Initialisation not possible -> Fallback');
            }
        }
        (new DebuggerFactory())->createLogger(new ErrorLogger())
            ->addLog(__METHOD__ . ' Error: Memcached not available -> Fallback');
        return (new CacheFactory())->createHandler(new DefaultHandler());
    }

    /**
     * @param string $Key
     * @param mixed $Value
     * @param int $Timeout
     * @param string $Region
     *
     * @return MemcachedHandler
     */
    public function setValue($Key, $Value, $Timeout = 0, $Region = 'Default')
    {

        if ($this->isValid()) {
            self::$Connection->set(
                preg_replace(
                    '!\s+!is',
                    '',
                    $this->getSlotRegion($Region) . '#' . $this->getKey($Key)
                ), $Value,
                (!$Timeout ? 0 : time() + $Timeout));
            // 0 = MEMCACHED_SUCCESS
            if (0 == ($Code = self::$Connection->getResultCode())) {
                return $this;
            } else {
                (new DebuggerFactory())->createLogger(new ErrorLogger())
                    ->addLog(__METHOD__ . ' Error: '
                        . $Region . '->' . $Key . ' - '
                        . $Code . ' - '
                        . self::$Connection->getResultMessage()
                    );
            }
        }
        return $this;
    }

    /**
     * @return bool
     */
    private function isValid()
    {

        return (null === self::$Connection ? false : true);
    }

    /**
     * @param $Region
     *
     * @return string
     */
    public function getSlotRegion($Region)
    {

        return $this->getSlot() . ':' . $Region;
    }

    /**
     * @return string
     */
    public function getSlot()
    {

        if (isset($_SESSION['Memcached-Slot'])) {
            return $_SESSION['Memcached-Slot'];
        }
        return 'PUBLIC';
    }

    /**
     * @internal
     *
     * @param mixed $Key
     * @return mixed
     */
    private function getKey($Key)
    {
        if( $this->HashKey ) {
            // Fix array-to-string notice
            if( is_array( $Key ) ){
                $Key = serialize($Key);
            }
            // Fix long keys resulting in wrong serialisation data
            return crc32($Key);
        }
        return $Key;
    }

    /**
     * @param string $Key
     * @param string $Region
     *
     * @return mixed
     */
    public function getValue($Key, $Region = 'Default')
    {

        if ($this->isValid()) {
            $Value = self::$Connection->get(
                preg_replace(
                    '!\s+!is',
                    '',
                    $this->getSlotRegion($Region) . '#' . $this->getKey($Key)
                )
            );
            // 0 = MEMCACHED_SUCCESS
            if (0 == ($Code = self::$Connection->getResultCode())) {
                return $Value;
            } else {
                (new DebuggerFactory())->createLogger(new ErrorLogger())
                    ->addLog(__METHOD__ . ' Error: '
                        . $Region . '->' . $Key . ' - '
                        . $Code . ' - '
                        . self::$Connection->getResultMessage()
                    );
            }
        }
        return null;
    }

    /**
     * @return \Memcached|null
     */
    public function getCache()
    {

        return self::$Connection;
    }

    /**
     * @return MemcachedHandler
     */
    public function clearCache()
    {

        (new DebuggerFactory())->createLogger(new BenchmarkLogger())->addLog('Clear MemCached');
        if ($this->isValid()) {
            self::$Connection->flush();
            // 0 = MEMCACHED_SUCCESS
            if (0 == ($Code = self::$Connection->getResultCode())) {
                return $this;
            } else {
                (new DebuggerFactory())->createLogger(new ErrorLogger())
                    ->addLog(__METHOD__ . ' Error: '
                        . $Code . ' - '
                        . self::$Connection->getResultMessage()
                    );
            }
        }
        return $this;
    }

    /**
     * @param string $Slot
     */
    public function clearSlot($Slot)
    {

        (new DebuggerFactory())->createLogger(new CacheLogger())->addLog('Requested Memcached-Slot-Clear: ' . $Slot);
        $Pattern = '!^' . preg_quote($Slot, '!') . ':!is';
        if (!($CacheList = $this->fetchKeys())) {
            $CacheList = array();
        }
        $KeyList = preg_grep($Pattern, $CacheList);
        if (!empty($KeyList)) {
            $this->removeKeys($KeyList);
            (new DebuggerFactory())->createLogger(new CacheLogger())->addLog('Cleared Memcached-Slot: ' . implode(',',
                    $KeyList));
        }
    }

    /**
     * Internal
     *
     * Get all cache Keys
     *
     * @return array
     */
    public function fetchKeys()
    {

        $List = self::$Connection->getAllKeys();
        (new DebuggerFactory())->createLogger(new ErrorLogger())
            ->addLog(__METHOD__ . ' Content: ' . json_encode($List));
        // 0 = MEMCACHED_SUCCESS
        if (0 == ($Code = self::$Connection->getResultCode())) {
            return $List;
        } else {
            (new DebuggerFactory())->createLogger(new ErrorLogger())
                ->addLog(__METHOD__ . ' Error: '
                    . $Code . ' - '
                    . self::$Connection->getResultMessage()
                );
        }
        return array();
    }

    /**
     * Internal
     *
     * Remove cache by Key
     *
     * @param $List
     *
     * @return MemcachedHandler
     */
    public function removeKeys($List)
    {

        self::$Connection->deleteMulti($List);
        // 0 = MEMCACHED_SUCCESS
        if (0 == ($Code = self::$Connection->getResultCode())) {
            return $this;
        } else {
            (new DebuggerFactory())->createLogger(new ErrorLogger())
                ->addLog(__METHOD__ . ' Error: '
                    . $Code . ' - '
                    . self::$Connection->getResultMessage()
                );
        }
        return $this;
    }

    /**
     * @return CacheStatus
     */
    public function getStatus()
    {

        (new DebuggerFactory())->createLogger(new BenchmarkLogger())->addLog('Status MemCached');
        if ($this->isValid()) {
            $Status = self::$Connection->getStats();
            $Status = $Status[$this->Host . ':' . $this->Port];
            return new CacheStatus(
                $Status['get_hits'], $Status['get_misses'], $Status['limit_maxbytes'],
                $Status['bytes'], $Status['limit_maxbytes'] - $Status['bytes'], 0
            );
        } else {
            return new CacheStatus();
        }
    }

    /**
     * Internal
     *
     * Memcached exists
     *
     * @return bool
     */
    public function isEnabled()
    {

        return $this->isValid();
    }
}
