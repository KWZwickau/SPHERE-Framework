<?php
namespace SPHERE\System\Database\Fitting;

use SPHERE\System\Cache\CacheFactory;
use SPHERE\System\Cache\Handler\HandlerInterface;
use SPHERE\System\Cache\Handler\MemcachedHandler;
use SPHERE\System\Cache\Handler\MemoryHandler;
use SPHERE\System\Config\ConfigFactory;
use SPHERE\System\Config\Reader\IniReader;
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
    private $Enabled = true;
    /** @var null|bool $Debug */
    private $Debug = null;

    /**
     * @param string  $__METHOD__ Initiator
     * @param Manager $EntityManager
     * @param string  $EntityName
     * @param int     $Id
     *
     * @return false|Element
     * @throws \Exception
     */
    final protected function getCachedEntityById($__METHOD__, Manager $EntityManager, $EntityName, $Id)
    {

        $Key = $this->getKeyHash($EntityName, $Id);

        $Memory = (new CacheFactory())->createHandler(new MemoryHandler());
        if (!$this->Enabled || null === ( $Entity = $Memory->getValue($Key, $__METHOD__) )) {

            $Cache = self::getCacheSystem($__METHOD__);
            $Entity = null;
            if (!$this->Enabled || null === ( $Entity = $Cache->getValue($Key, $__METHOD__) )) {
                $Entity = $EntityManager->getEntityById($EntityName, $Id);
                if (null === $Entity) {
                    $Entity = false;
                }
                $Cache->setValue($Key, $Entity, 0, $__METHOD__);
                $this->debugFactory($__METHOD__, $Entity, $Id);
            } else {
                $this->debugCache($__METHOD__, $Entity, $Id);
            }
            $Memory->setValue($Key, $Entity, 0, $__METHOD__);
            return ( null === $Entity || false === $Entity ? false : $Entity );
        }
        $this->getLogger(new BenchmarkLogger())->addLog('Memory (Id) ' . $EntityName . ' ' . $__METHOD__ . ' > ' . $Id);
        return ( null === $Entity || false === $Entity ? false : $Entity );
    }

    /**
     * @param string       $EntityName
     * @param string|array $Parameter
     *
     * @return string
     */
    private function getKeyHash($EntityName, $Parameter)
    {

        if (is_object($Parameter)) {
            $Parameter = json_decode(json_encode($Parameter), true);
        }
        return md5($EntityName . ':' . implode('#', (array)$Parameter));
    }

    /**
     * @param string $__METHOD__
     *
     * @return HandlerInterface
     */
    private function getCacheSystem($__METHOD__)
    {

        if (null === self::$CacheSystem || !self::$CacheSystem instanceof MemcachedHandler) {
            $this->debugSystem($__METHOD__, 'Memcached');
            self::$CacheSystem = $this->getCache(new MemcachedHandler(), 'Memcached');
        }
        return self::$CacheSystem;
    }

    /**
     * @param string $__METHOD__
     * @param string $Type
     */
    private function debugSystem($__METHOD__, $Type)
    {

        if ($this->useDebugger()) {
            $this->getLogger(new BenchmarkLogger())->addLog(
                'System: '.$__METHOD__.' ['.$Type.']'
            );
        }
    }

    /**
     * @return bool|null
     */
    private function useDebugger()
    {
        if ($this->Debug === null) {
            $DebuggerConfig = (new ConfigFactory())
                ->createReader(__DIR__ . '/../../../System/Debugger/Configuration.ini', new IniReader());
            if ($DebuggerConfig->getConfig()->getContainer('Debugger')->getContainer('Enabled')->getValue()) {
                if ($DebuggerConfig->getConfig()->getContainer('Debugger')->getContainer('DatabaseCache')->getValue()) {
                    $this->Debug = true;
                } else {
                    $this->Debug = false;
                }
            } else {
                $this->Debug = false;
            }
        }
        return $this->Debug;
    }

    /**
     * @param string                $__METHOD__
     * @param array|object|bool|int $EntityList
     * @param array|string|int      $Parameter
     */
    private function debugFactory($__METHOD__, $EntityList, $Parameter)
    {

        if ($this->useDebugger()) {
            $this->getLogger(new BenchmarkLogger())->addLog(
                'Factory: '.$__METHOD__.' ['.implode('], [', (array)$Parameter).'] Result: '.(
                $EntityList ? 'Ok' : ( null === $EntityList ? 'None' : 'Error' ) )
            );
        }
    }

    /**
     * @param string                $__METHOD__
     * @param array|object|bool|int $EntityList
     * @param array |string|int     $Parameter
     */
    private function debugCache($__METHOD__, $EntityList, $Parameter)
    {

        if ($this->useDebugger()) {
            $this->getLogger(new BenchmarkLogger())->addLog(
                'Cache: '.$__METHOD__.' ['.implode('], [', (array)$Parameter).'] Result: '.(
                $EntityList ? 'Ok' : ( null === $EntityList ? 'None' : 'Error' ) )
            );
        }
    }

    /**
     * @param string  $__METHOD__ Initiator
     * @param Manager $EntityManager
     * @param string  $EntityName
     * @param array   $Parameter  Initiator Parameter-Array
     *
     * @return false|Element
     * @throws \Exception
     */
    final protected function getCachedEntityBy($__METHOD__, Manager $EntityManager, $EntityName, $Parameter)
    {

        $Key = $this->getKeyHash($EntityName, $Parameter);

        $Memory = (new CacheFactory())->createHandler(new MemoryHandler());
        if (!$this->Enabled || null === ( $Entity = $Memory->getValue($Key, $__METHOD__) )) {

            $Cache = self::getCacheSystem($__METHOD__);
            $Entity = null;
            if (!$this->Enabled || null === ( $Entity = $Cache->getValue($Key, $__METHOD__) )) {
                $Entity = $EntityManager->getEntity($EntityName)->findOneBy($Parameter);
                if (null === $Entity) {
                    $Entity = false;
                }
                $Cache->setValue($Key, $Entity, 0, $__METHOD__);
                $this->debugFactory($__METHOD__, $Entity, $Parameter);
            } else {
                $this->debugCache($__METHOD__, $Entity, $Parameter);
            }
            $Memory->setValue($Key, $Entity, 0, $__METHOD__);
            return ( null === $Entity || false === $Entity ? false : $Entity );
        }
        $this->getLogger(new BenchmarkLogger())->addLog('Memory (Criteria) ' . $EntityName . ' ' . $__METHOD__);
        return ( null === $Entity || false === $Entity ? false : $Entity );
    }

    /**
     * @param string  $__METHOD__ Initiator
     * @param Manager $EntityManager
     * @param string  $EntityName
     * @param array   $Parameter  Initiator Parameter-Array
     *
     * @return false|Element[]
     * @throws \Exception
     */
    final protected function getCachedEntityListBy($__METHOD__, Manager $EntityManager, $EntityName, $Parameter)
    {

        $Key = $this->getKeyHash($EntityName, $Parameter);

        $Memory = (new CacheFactory())->createHandler(new MemoryHandler());
        if (!$this->Enabled || null === ( $EntityList = $Memory->getValue($Key, $__METHOD__) )) {

            $Cache = self::getCacheSystem($__METHOD__);
            $EntityList = null;
            if (!$this->Enabled || null === ( $EntityList = $Cache->getValue($Key, $__METHOD__) )) {
                $EntityList = $EntityManager->getEntity($EntityName)->findBy($Parameter);
                $Cache->setValue($Key, $EntityList, 0, $__METHOD__);
                $this->debugFactory($__METHOD__, $EntityList, $Parameter);
            } else {
                $this->debugCache($__METHOD__, $EntityList, $Parameter);
            }
            $Memory->setValue($Key, $EntityList, 0, $__METHOD__);
            return ( empty( $EntityList ) ? false : $EntityList );
        }
        $this->getLogger(new BenchmarkLogger())->addLog('Memory-List (Criteria) ' . $EntityName . ' ' . $__METHOD__);
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param string  $__METHOD__ Initiator
     * @param Manager $EntityManager
     * @param string  $EntityName
     *
     * @return false|Element[]
     * @throws \Exception
     */
    final protected function getCachedEntityList($__METHOD__, Manager $EntityManager, $EntityName)
    {

        $Key = $this->getKeyHash($EntityName, 'All');

        $Memory = (new CacheFactory())->createHandler(new MemoryHandler());
        if (!$this->Enabled || null === ( $EntityList = $Memory->getValue($Key, $__METHOD__) )) {

            $Cache = self::getCacheSystem($__METHOD__);
            $EntityList = null;
            if (!$this->Enabled || null === ( $EntityList = $Cache->getValue($Key, $__METHOD__) )) {
                $EntityList = $EntityManager->getEntity($EntityName)->findAll();
                $Cache->setValue($Key, $EntityList, 0, $__METHOD__);
                $this->debugFactory($__METHOD__, $EntityList, 'All');
            } else {
                $this->debugCache($__METHOD__, $EntityList, 'All');
            }
            $Memory->setValue($Key, $EntityList, 0, $__METHOD__);
            return ( empty( $EntityList ) ? false : $EntityList );
        }
        $this->getLogger(new BenchmarkLogger())->addLog('Memory-List ' . $EntityName . ' ' . $__METHOD__);
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param string  $__METHOD__ Initiator
     * @param Manager $EntityManager
     * @param string  $EntityName
     * @param array   $Parameter  Initiator Parameter-Array
     *
     * @return false|Element
     * @throws \Exception
     */
    final protected function getCachedCountBy($__METHOD__, Manager $EntityManager, $EntityName, $Parameter)
    {

        $Key = $this->getKeyHash($EntityName, $Parameter);

        $Memory = (new CacheFactory())->createHandler(new MemoryHandler());
        if (!$this->Enabled || null === ( $Entity = $Memory->getValue($Key, $__METHOD__) )) {

            $Cache = self::getCacheSystem($__METHOD__);
            $Entity = null;
            if (!$this->Enabled || null === ( $Entity = $Cache->getValue($Key, $__METHOD__) )) {
                $Entity = $EntityManager->getEntity($EntityName)->countBy($Parameter);
                if (null === $Entity) {
                    $Entity = false;
                }
                $Cache->setValue($Key, $Entity, 0, $__METHOD__);
                $this->debugFactory($__METHOD__, $Entity, $Parameter);
            } else {
                $this->debugCache($__METHOD__, $Entity, $Parameter);
            }
            $Memory->setValue($Key, $Entity, 0, $__METHOD__);
            return ( null === $Entity || false === $Entity ? false : $Entity );
        }
        $this->getLogger(new BenchmarkLogger())->addLog('Memory (Criteria) ' . $EntityName . ' ' . $__METHOD__);
        return ( null === $Entity || false === $Entity ? false : $Entity );
    }
}
