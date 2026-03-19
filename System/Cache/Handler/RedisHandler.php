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
 * Class RedisHandler
 *
 * @package SPHERE\System\Cache\Handler
 */
class RedisHandler extends AbstractHandler
{

    /** @var null|\Redis $Connection */
    private $Connection = null;

    private $Host = '';
    private $Port = '';

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
            && class_exists('\Redis', false)
        ) {
            $Value = $Config->getValue($Name);
            if ($Value) {
                if ($Value->getContainer('Enabled') && $Value->getContainer('Enabled')->getValue()) {
                    $this->Host = $Value->getContainer('Host');
                    $this->Port = $Value->getContainer('Port');
                    if ($this->Host && $this->Port) {

                        $this->Connection = new \Redis();

                        if (!$this->Connection->isConnected()) {
                            if ($this->Connection->connect((string)$this->Host, (string)$this->Port)) {
                                $this->setValue('CheckRunningStatus', 1);
                                $Check = (string)$this->getValue('CheckRunningStatus');
                                if ('1' === $Check) {
                                    $this->removeKeys(['CheckRunningStatus']);
                                    return $this;
                                }

                                (new DebuggerFactory())->createLogger(new ErrorLogger())
                                    ->addLog(__METHOD__ . ' Error: Server not available -> Fallback');
                            }
                        } else {
                            $this->setValue('CheckRunningStatus', 1);
                            $Check = (string)$this->getValue('CheckRunningStatus');
                            if ('1' === $Check) {
                                $this->removeKeys(['CheckRunningStatus']);
                                return $this;
                            }

                            (new DebuggerFactory())->createLogger(new ErrorLogger())
                                ->addLog(__METHOD__ . ' Error: Server not available -> Fallback');
                        }
                    } else {
                        (new DebuggerFactory())->createLogger(new ErrorLogger())
                            ->addLog(__METHOD__ . ' Error: Configuration not available -> Fallback');
                    }
                } else {
                    (new DebuggerFactory())->createLogger(new ErrorLogger())
                        ->addLog(__METHOD__ . ' Error: RedisHandler is Disabled -> Fallback');
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
            ->addLog(__METHOD__ . ' Error: Redis not available -> Fallback');
        return (new CacheFactory())->createHandler(new DefaultHandler());
    }

    /**
     * @param string $Key
     * @param mixed $Value
     * @param int $Timeout
     * @param string $Region
     *
     * @return RedisHandler
     */
    public function setValue($Key, $Value, $Timeout = 0, $Region = 'Default'): RedisHandler
    {

        if ($this->isValid()) {

            $Value = serialize($Value);

            $regionKey = preg_replace('!\s+!', '', $this->getSlotRegion($Region) . '#' . $Key);
            if(empty($Timeout)) {
                $Code = $this->Connection->set($regionKey, $Value);
            } else {
                $Code = $this->Connection->set($regionKey, $Value, ['EX' => $Timeout]);
            }
            // true = REDIS_SUCCESS
            if (true === $Code) {
                return $this;
            }

            (new DebuggerFactory())->createLogger(new ErrorLogger())
                ->addLog(__METHOD__ . ' Error: '
                    . $Region . '->' . $Key
                );
        }
        return $this;
    }

    /**
     * @return bool
     */
    private function isValid()
    {

        return !(null === $this->Connection);
    }

    /**
     * @param $Region
     *
     * @return string
     */
    public function getSlotRegion($Region): string
    {

        return $this->getSlot() . ':' . $Region;
    }

    /**
     * @return string
     */
    public function getSlot(): string
    {

        if (isset($_SESSION['Memcached-Slot'])) {
            return $_SESSION['Memcached-Slot'];
        }
        return 'PUBLIC';
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
            try {
                $regionKey = preg_replace('!\s+!', '', $this->getSlotRegion($Region) . '#' . $Key);
                $Value = $this->Connection->get($regionKey);
            } catch (\Exception $exception) {
                (new DebuggerFactory())->createLogger(new ErrorLogger())
                    ->addLog(__METHOD__ . ' Error: '
                        . $Region . '->' . $Key . ' - '
                        . $exception->getCode() . ' - '
                        . $exception->getMessage()
                    );
                return null;
            }

            if (false !== $Value) {
                $Value = unserialize($Value,['allowed_classes' => true]);
                return $Value;
            }

            (new DebuggerFactory())->createLogger(new ErrorLogger())
                ->addLog(__METHOD__ . ' Error: '
                    . $Region . '->' . $Key
                );
        }
        return null;
    }

    /**
     * @return \Redis|null
     */
    public function getCache()
    {

        return $this->Connection;
    }

    /**
     * @return RedisHandler
     */
    public function clearCache()
    {

        (new DebuggerFactory())->createLogger(new BenchmarkLogger())->addLog('Clear Redis');
        if ($this->isValid()) {
            $Code = $this->Connection->flushDB();
            // 0 = REDIS_SUCCESS
            if (false !== $Code) {
                return $this;
            }

            (new DebuggerFactory())->createLogger(new ErrorLogger())
                ->addLog(__METHOD__ . ' Error: '
                );
        }
        return $this;
    }

    /**
     * @param string $Slot
     */
    public function clearSlot($Slot)
    {

        (new DebuggerFactory())->createLogger(new CacheLogger())->addLog('Requested Redis-Slot-Clear: ' . $Slot);
        $Pattern = '!^' . preg_quote($Slot, '!') . ':!is';
        $CacheList = $this->fetchKeys();
        if (empty($CacheList)) {
            $CacheList = [];
        }
        $KeyList = preg_grep($Pattern, $CacheList);
        if (!empty($KeyList)) {
            $this->removeKeys($KeyList);
            (new DebuggerFactory())->createLogger(new CacheLogger())->addLog('Cleared Redis-Slot: ' . implode(',',
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

        $List = $this->Connection->keys('*');
        (new DebuggerFactory())->createLogger(new CacheLogger())
            ->addLog(__METHOD__ . ' Content: ' . json_encode($List));

        if (false !== $List) {
            return $List;
        }

        (new DebuggerFactory())->createLogger(new ErrorLogger())
            ->addLog(__METHOD__ . ' Error: '
            );
        return [];
    }

    /**
     * Internal
     *
     * Remove cache by Key
     *
     * @param $List
     *
     * @return RedisHandler
     */
    public function removeKeys($List)
    {

        $Code = $this->Connection->del($List);

        if (false !== $Code) {
            (new DebuggerFactory())->createLogger(new CacheLogger())->addLog('Remove Keys: ' . implode(', ',$List));
            return $this;
        }

        (new DebuggerFactory())->createLogger(new ErrorLogger())
            ->addLog(__METHOD__ . ' Error: '
            );
        return $this;
    }

    /**
     * @return CacheStatus
     */
    public function getStatus()
    {

        (new DebuggerFactory())->createLogger(new BenchmarkLogger())->addLog('Status Redis');

        return new CacheStatus();
    }

    /**
     * Internal
     *
     * Redis exists
     *
     * @return bool
     */
    public function isEnabled()
    {

        return $this->isValid();
    }
}
