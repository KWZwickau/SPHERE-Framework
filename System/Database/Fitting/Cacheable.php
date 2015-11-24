<?php
namespace SPHERE\System\Database\Fitting;

use SPHERE\System\Cache\Cache;
use SPHERE\System\Cache\IApiInterface;
use SPHERE\System\Cache\Type\Memcached;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class Cacheable
 *
 * @package SPHERE\System\Database\Fitting
 */
abstract class Cacheable extends Extension
{

    /** @var null|IApiInterface $CacheSystem */
    private static $CacheSystem = null;
    /** @var bool $Enabled */
    private $Enabled = true;
    /** @var bool $Debug */
    private $Debug = false;

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

        $Cache = self::getCacheSystem();
        $Key = $this->getKeyHash($__METHOD__, $EntityName, $Id);
        $Entity = null;
        if (!$this->Enabled || false === ( $Entity = $Cache->getValue($Key) )) {
            $Entity = $EntityManager->getEntityById($EntityName, $Id);
            $Cache->setValue($Key, $Entity, 300);
            $this->debugFactory($__METHOD__, $Entity, $Id, $Cache->getLastTiming());
        } else {
            $this->debugCache($__METHOD__, $Entity, $Id, $Cache->getLastTiming());
        }
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return IApiInterface
     */
    private function getCacheSystem()
    {

        if (null === self::$CacheSystem) {
            self::$CacheSystem = (new Cache(new Memcached()))->getCache();
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
        return sha1(session_id().':'.$__METHOD__.':'.$EntityName.':'.implode('#', (array)$Parameter));
    }

    /**
     * @param string           $__METHOD__
     * @param array|object     $EntityList
     * @param array|string|int $Parameter
     * @param string           $Timing
     */
    private function debugFactory($__METHOD__, $EntityList, $Parameter, $Timing)
    {

        if ($this->Debug) {
            Debugger::protocolDump(
                'Factory: '.$__METHOD__.' ['.implode('], [', (array)$Parameter).'] Result: '.(
                $EntityList ? 'Ok' : ( null === $EntityList ? 'None' : 'Error' ) ).' ~'.$Timing.'ms'
            );
        }
    }

    /**
     * @param string            $__METHOD__
     * @param array|object      $EntityList
     * @param array |string|int $Parameter
     * @param string            $Timing
     */
    private function debugCache($__METHOD__, $EntityList, $Parameter, $Timing)
    {

        if ($this->Debug) {
            Debugger::protocolDump(
                'Cache: '.$__METHOD__.' ['.implode('], [', (array)$Parameter).'] Result: '.(
                $EntityList ? 'Ok' : ( null === $EntityList ? 'None' : 'Error' ) ).' ~'.$Timing.'ms'
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

        $Cache = self::getCacheSystem();
        $Key = $this->getKeyHash($__METHOD__, $EntityName, $Parameter);
        $Entity = null;
        if (!$this->Enabled || false === ( $Entity = $Cache->getValue($Key) )) {
            $Entity = $EntityManager->getEntity($EntityName)->findOneBy($Parameter);
            $Cache->setValue($Key, $Entity, 300);
            $this->debugFactory($__METHOD__, $Entity, $Parameter, $Cache->getLastTiming());
        } else {
            $this->debugCache($__METHOD__, $Entity, $Parameter, $Cache->getLastTiming());
        }
        return ( null === $Entity ? false : $Entity );
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

        $Cache = self::getCacheSystem();
        $Key = $this->getKeyHash($__METHOD__, $EntityName, $Parameter);
        $EntityList = null;
        if (!$this->Enabled || false === ( $EntityList = $Cache->getValue($Key) )) {
            $EntityList = $EntityManager->getEntity($EntityName)->findBy($Parameter);
            $Cache->setValue($Key, $EntityList, 300);
            $this->debugFactory($__METHOD__, $EntityList, $Parameter, $Cache->getLastTiming());
        } else {
            $this->debugCache($__METHOD__, $EntityList, $Parameter, $Cache->getLastTiming());
        }
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

        $Cache = self::getCacheSystem();
        $Key = $this->getKeyHash($__METHOD__, $EntityName, 'All');
        $EntityList = null;
        if (!$this->Enabled || false === ( $EntityList = $Cache->getValue($Key) )) {
            $EntityList = $EntityManager->getEntity($EntityName)->findAll();
            $Cache->setValue($Key, $EntityList, 300);
            $this->debugFactory($__METHOD__, $EntityList, 'All', $Cache->getLastTiming());
        } else {
            $this->debugCache($__METHOD__, $EntityList, 'All', $Cache->getLastTiming());
        }
        return ( empty( $EntityList ) ? false : $EntityList );
    }
}
