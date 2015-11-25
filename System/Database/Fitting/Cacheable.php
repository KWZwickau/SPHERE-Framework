<?php
namespace SPHERE\System\Database\Fitting;

use SPHERE\System\Cache\Handler\HandlerInterface;
use SPHERE\System\Cache\Handler\MemcachedHandler;
use SPHERE\System\Debugger\DebuggerFactory;
use SPHERE\System\Debugger\Logger\BenchmarkLogger;
use SPHERE\System\Extension\Extension;

/**
 * Class Cacheable
 *
 * @package SPHERE\System\Database\Fitting
 */
abstract class Cacheable extends Extension
{

    /** @var null|HandlerInterface $CacheSystem */
    private static $CacheSystem = null;
    /** @var bool $Enabled */
    private $Enabled = false;
    /** @var bool $Debug */
    private $Debug = false;

    /**
     * @param string $__METHOD__ Initiator
     * @param Manager $EntityManager
     * @param string $EntityName
     * @param int $Id
     *
     * @return false|Element
     * @throws \Exception
     */
    final protected function getCachedEntityById($__METHOD__, Manager $EntityManager, $EntityName, $Id)
    {

        $Cache = self::getCacheSystem();
        $Key = $this->getKeyHash($__METHOD__, $EntityName, $Id);
        $Entity = null;
        if (!$this->Enabled || null === ($Entity = $Cache->getValue($Key))) {
            $Entity = $EntityManager->getEntityById($EntityName, $Id);
            $Cache->setValue($Key, $Entity);
            $this->debugFactory($__METHOD__, $Entity, $Id);
        } else {
            $this->debugCache($__METHOD__, $Entity, $Id);
        }
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @return HandlerInterface
     */
    private function getCacheSystem()
    {

        if (null === self::$CacheSystem) {
            self::$CacheSystem = $this->getCache(new MemcachedHandler());
        }
        return self::$CacheSystem;
    }

    /**
     * @param string $__METHOD__
     * @param string $EntityName
     * @param string|array $Parameter
     *
     * @return string
     */
    private function getKeyHash($__METHOD__, $EntityName, $Parameter)
    {

        if (is_object($Parameter)) {
            $Parameter = json_decode(json_encode($Parameter), true);
        }
        return sha1(session_id() . ':' . $__METHOD__ . ':' . $EntityName . ':' . implode('#', (array)$Parameter));
    }

    /**
     * @param string $__METHOD__
     * @param array|object $EntityList
     * @param array|string|int $Parameter
     */
    private function debugFactory($__METHOD__, $EntityList, $Parameter)
    {

        if ($this->Debug) {
            (new DebuggerFactory())->createLogger(new BenchmarkLogger())->addLog(
                'Factory: ' . $__METHOD__ . ' [' . implode('], [', (array)$Parameter) . '] Result: ' . (
                $EntityList ? 'Ok' : (null === $EntityList ? 'None' : 'Error'))
            );
        }
    }

    /**
     * @param string $__METHOD__
     * @param array|object $EntityList
     * @param array |string|int $Parameter
     */
    private function debugCache($__METHOD__, $EntityList, $Parameter)
    {

        if ($this->Debug) {
            (new DebuggerFactory())->createLogger(new BenchmarkLogger())->addLog(
                'Cache: ' . $__METHOD__ . ' [' . implode('], [', (array)$Parameter) . '] Result: ' . (
                $EntityList ? 'Ok' : (null === $EntityList ? 'None' : 'Error'))
            );
        }
    }

    /**
     * @param string $__METHOD__ Initiator
     * @param Manager $EntityManager
     * @param string $EntityName
     * @param array $Parameter Initiator Parameter-Array
     *
     * @return false|Element
     * @throws \Exception
     */
    final protected function getCachedEntityBy($__METHOD__, Manager $EntityManager, $EntityName, $Parameter)
    {

        $Cache = self::getCacheSystem();
        $Key = $this->getKeyHash($__METHOD__, $EntityName, $Parameter);
        $Entity = null;
        if (!$this->Enabled || null === ($Entity = $Cache->getValue($Key))) {
            $Entity = $EntityManager->getEntity($EntityName)->findOneBy($Parameter);
            $Cache->setValue($Key, $Entity);
            $this->debugFactory($__METHOD__, $Entity, $Parameter);
        } else {
            $this->debugCache($__METHOD__, $Entity, $Parameter);
        }
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param string $__METHOD__ Initiator
     * @param Manager $EntityManager
     * @param string $EntityName
     * @param array $Parameter Initiator Parameter-Array
     *
     * @return false|Element[]
     * @throws \Exception
     */
    final protected function getCachedEntityListBy($__METHOD__, Manager $EntityManager, $EntityName, $Parameter)
    {

        $Cache = self::getCacheSystem();
        $Key = $this->getKeyHash($__METHOD__, $EntityName, $Parameter);
        $EntityList = null;
        if (!$this->Enabled || null === ($EntityList = $Cache->getValue($Key))) {
            $EntityList = $EntityManager->getEntity($EntityName)->findBy($Parameter);
            $Cache->setValue($Key, $EntityList);
            $this->debugFactory($__METHOD__, $EntityList, $Parameter);
        } else {
            $this->debugCache($__METHOD__, $EntityList, $Parameter);
        }
        return (empty($EntityList) ? false : $EntityList);
    }

    /**
     * @param string $__METHOD__ Initiator
     * @param Manager $EntityManager
     * @param string $EntityName
     *
     * @return false|Element[]
     * @throws \Exception
     */
    final protected function getCachedEntityList($__METHOD__, Manager $EntityManager, $EntityName)
    {

        $Cache = self::getCacheSystem();
        $Key = $this->getKeyHash($__METHOD__, $EntityName, 'All');
        $EntityList = null;
        if (!$this->Enabled || null === ($EntityList = $Cache->getValue($Key))) {
            $EntityList = $EntityManager->getEntity($EntityName)->findAll();
            $Cache->setValue($Key, $EntityList);
            $this->debugFactory($__METHOD__, $EntityList, 'All');
        } else {
            $this->debugCache($__METHOD__, $EntityList, 'All');
        }
        return (empty($EntityList) ? false : $EntityList);
    }
}
