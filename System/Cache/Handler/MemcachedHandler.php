<?php
namespace SPHERE\System\Cache\Handler;

use SPHERE\System\Cache\CacheFactory;
use SPHERE\System\Config\Reader\ReaderInterface;
use SPHERE\System\Debugger\DebuggerFactory;
use SPHERE\System\Debugger\Logger\ErrorLogger;

/**
 * Class MemcachedHandler
 * @package SPHERE\System\Cache\Handler
 */
class MemcachedHandler extends AbstractHandler implements HandlerInterface
{
    /** @var null|\Memcached $Connection */
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
            && class_exists('\Memcached', false)
        ) {
            $Value = $Config->getValue($Name);
            if ($Value) {
                $Host = $Value->getContainer('Host');
                $Port = $Value->getContainer('Port');
                if ($Host && $Port) {
                    $this->Connection = new \Memcached();
                    if ($this->Connection->addServer((string)$Host, (string)$Port)) {
                        $this->Connection->setOption(\Memcached::OPT_TCP_NODELAY, true);
                        $this->Connection->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
                        $this->setValue('CheckRunningStatus', true);
                        if (true === $this->getValue('CheckRunningStatus')) {
                            $this->Connection->delete('CheckRunningStatus');
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
     * @return MemcachedHandler
     */
    public function setValue($Key, $Value, $Timeout = 0, $Region = 'Default')
    {
        if ($this->isValid()) {
            $this->Connection->set($Key, $Value, (!$Timeout ? null : time() + $Timeout));
        }
        return $this;
    }

    /**
     * @return bool
     */
    private function isValid()
    {
        return (null === $this->Connection ? false : true);
    }

    /**
     * @param string $Key
     * @param string $Region
     * @return mixed
     */
    public function getValue($Key, $Region = 'Default')
    {
        if ($this->isValid()) {
            $Value = $this->Connection->get($Key);
            // 0 = MEMCACHED_SUCCESS
            if (0 == ($Code = $this->Connection->getResultCode())) {
                return $Value;
            } else {
                (new DebuggerFactory())->createLogger(new ErrorLogger())
                    ->addLog(__METHOD__ . ' Error: '
                        . $Region . '->' . $Key . ' - '
                        . $Code . ' - '
                        . $this->Connection->getResultMessage()
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
        return $this->Connection;
    }

    /**
     * @return MemcachedHandler
     */
    public function clearCache()
    {
        if ($this->isValid()) {
            $this->Connection->flush();
        }
        return $this;
    }
}
