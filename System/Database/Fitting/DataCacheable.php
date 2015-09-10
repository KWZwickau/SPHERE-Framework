<?php
namespace SPHERE\System\Database\Fitting;

use SPHERE\System\Cache\Cache;
use SPHERE\System\Cache\Type\Memcached;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class DataCacheable
 *
 * @package SPHERE\System\Database\Fitting
 */
abstract class DataCacheable
{

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

        $Cache = (new Cache(new Memcached()))->getCache();
        $Key = sha1($__METHOD__.':'.$EntityName.':'.$Id);
        $Entity = null;
        if (false === ( $Entity = $Cache->getValue($Key) )) {
            $Entity = $EntityManager->getEntityById($EntityName, $Id);
            $Cache->setValue($Key, $Entity);
            Debugger::protocolDump(
                'Get '.$__METHOD__.' (Factory) ['.implode('], [', array($EntityName, $Id)).'] Result: '.(
                $Entity ? 'Ok' : ( null === $Entity ? 'None' : 'Error' ) )
            );
        } else {
//            Debugger::protocolDump(
//                'Get '.$__METHOD__.' (Cache) ['.implode('], [', array($EntityName, $Id)).'] Result: '.(
//                $Entity ? 'Ok' : ( null === $Entity ? 'None' : 'Error' ) )
//            );
        }
        return ( null === $Entity ? false : $Entity );
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

        $Cache = (new Cache(new Memcached()))->getCache();
        $Key = sha1($__METHOD__.':'.$EntityName.':'.implode(':', (array)$Parameter));
        $Entity = null;
        if (false === ( $Entity = $Cache->getValue($Key) )) {
            $Entity = $EntityManager->getEntity($EntityName)->findOneBy($Parameter);
            $Cache->setValue($Key, $Entity);
            Debugger::protocolDump(
                'Get '.$__METHOD__.' (Factory) ['.implode('], [', (array)$Parameter).'] Result: '.(
                $Entity ? 'Ok' : ( null === $Entity ? 'None' : 'Error' ) )
            );
        } else {
//            Debugger::protocolDump(
//                'Get '.$__METHOD__.' (Cache) ['.implode('], [', (array)$Parameter).'] Result: '.(
//                $Entity ? 'Ok' : ( null === $Entity ? 'None' : 'Error' ) )
//            );
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

        $Cache = (new Cache(new Memcached()))->getCache();
        $Key = sha1($__METHOD__.':'.$EntityName.':'.implode(':', (array)$Parameter));
        $EntityList = null;
        if (false === ( $EntityList = $Cache->getValue($Key) )) {
            $EntityList = $EntityManager->getEntity($EntityName)->findBy($Parameter);
            $Cache->setValue($Key, $EntityList);
            Debugger::protocolDump(
                'Get '.$__METHOD__.' (Factory) ['.implode('], [', (array)$Parameter).'] Result: '.(
                $EntityList ? 'Ok' : ( null === $EntityList ? 'None' : 'Error' ) )
            );

        } else {
//            Debugger::protocolDump(
//                'Get '.$__METHOD__.' (Cache) ['.implode('], [', (array)$Parameter).'] Result: '.(
//                $EntityList ? 'Ok' : ( null === $EntityList ? 'None' : 'Error' ) )
//            );
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

        $Cache = (new Cache(new Memcached()))->getCache();
        $Key = sha1($__METHOD__.':'.$EntityName.':All');
        $EntityList = null;
        if (false === ( $EntityList = $Cache->getValue($Key) )) {
            $EntityList = $EntityManager->getEntity($EntityName)->findAll();
            $Cache->setValue($Key, $EntityList);
            Debugger::protocolDump(
                'Get '.$__METHOD__.' (Factory) ['.implode('], [', array($EntityName, 'All')).'] Result: '.(
                $EntityList ? 'Ok' : ( null === $EntityList ? 'None' : 'Error' ) )
            );
        } else {
//            Debugger::protocolDump(
//                'Get '.$__METHOD__.' (Cache) ['.implode('], [', array($EntityName, 'All')).'] Result: '.(
//                $EntityList ? 'Ok' : ( null === $EntityList ? 'None' : 'Error' ) )
//            );
        }
        return ( empty( $EntityList ) ? false : $EntityList );
    }
}
