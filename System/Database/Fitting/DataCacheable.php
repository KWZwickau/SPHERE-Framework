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
     * @param string $MethodName Called get{Entity}By{Parameter}-Method
     * @param array  $Parameter  Method Parameter-Array
     * @param array  $Callback   array( $this, 'get{Entity}By{Parameter}-Method' ) containing EntityManager-Logic
     *
     * @return false|Element
     * @throws \Exception
     */
    final protected function getCachedEntityBy($MethodName, $Parameter, $Callback)
    {

        $Cache = (new Cache(new Memcached()))->getCache();
        $Key = sha1(implode(':', (array)$Parameter));
        $Entity = null;
        if (false === ( $Entity = $Cache->getValue($Key) )) {
            if (is_callable($Callback)) {
                $Entity = call_user_func_array($Callback, $Parameter);
                if (is_array($Entity)) {
                    throw new \Exception('getCachedEntityBy: Only single Entities allowed');
                }
                $Cache->setValue($Key, $Entity);
                Debugger::protocolDump(
                    'Get '.$MethodName.' (Factory) ['.implode('], [', (array)$Parameter).'] Result: '.(
                    $Entity ? 'Ok' : ( null === $Entity ? 'None' : 'Error' ) )
                );
            }
        } else {
            Debugger::protocolDump(
                'Get '.$MethodName.' (Cache) ['.implode('], [', (array)$Parameter).'] Result: '.(
                $Entity ? 'Ok' : ( null === $Entity ? 'None' : 'Error' ) )
            );
        }
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param string $MethodName Called get{Entity}By{Parameter}-Method
     * @param array  $Parameter  Method Parameter-Array
     * @param array  $Callback   array( $this, 'get{Entity}By{Parameter}-Method' ) containing EntityManager-Logic
     *
     * @return false|Element[]
     * @throws \Exception
     */
    final protected function getCachedEntityListBy($MethodName, $Parameter, $Callback)
    {

        $Cache = (new Cache(new Memcached()))->getCache();
        $Key = sha1(implode(':', (array)$Parameter));
        $EntityList = null;
        if (false === ( $EntityList = $Cache->getValue($Key) )) {
            if (is_callable($Callback)) {
                $EntityList = call_user_func_array($Callback, $Parameter);
                if (!is_array($EntityList)) {
                    throw new \Exception('getCachedEntityBy: Only multiple Entities allowed');
                }
                $Cache->setValue($Key, $EntityList);
                Debugger::protocolDump(
                    'Get '.$MethodName.' (Factory) ['.implode('], [', (array)$Parameter).'] Result: '.(
                    $EntityList ? 'Ok' : ( null === $EntityList ? 'None' : 'Error' ) )
                );
            }
        } else {
            Debugger::protocolDump(
                'Get '.$MethodName.' (Cache) ['.implode('], [', (array)$Parameter).'] Result: '.(
                $EntityList ? 'Ok' : ( null === $EntityList ? 'None' : 'Error' ) )
            );
        }
        return ( empty( $EntityList ) ? false : $EntityList );
    }
}
