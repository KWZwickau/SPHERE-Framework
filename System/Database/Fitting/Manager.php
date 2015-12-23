<?php
namespace SPHERE\System\Database\Fitting;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use SPHERE\System\Cache\Handler\MemcachedHandler;
use SPHERE\System\Cache\Handler\MemoryHandler;
use SPHERE\System\Debugger\DebuggerFactory;
use SPHERE\System\Debugger\Logger\BenchmarkLogger;
use SPHERE\System\Extension\Extension;

/**
 * Class Manager
 *
 * @package SPHERE\System\Database\Fitting
 */
class Manager extends Extension
{

    /** @var EntityManager $EntityManager */
    private $EntityManager = null;
    /** @var string $Namespace */
    private $Namespace = '';

    /**
     * @param EntityManager $EntityManager
     * @param string        $Namespace
     */
    final function __construct(EntityManager $EntityManager, $Namespace)
    {

        $this->EntityManager = $EntityManager;
        $this->Namespace = $Namespace;
    }

    /**
     * @param string $ClassName
     *
     * @return Repository
     */
    final public function getEntity($ClassName)
    {

        // MUST NOT USE Cache-System
        return $this->getRepository($ClassName);
    }

    /**
     * @param string $ClassName
     *
     * @return Repository
     */
    final public function getRepository($ClassName)
    {

        // MUST NOT USE Cache-System
        return $this->EntityManager->getRepository($this->Namespace.$ClassName);
    }

    /**
     * @param string $ClassName
     * @param int    $Id
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     * @return Entity
     */
    final public function getEntityById($ClassName, $Id)
    {

        // MUST NOT USE Cache-System
        return $this->EntityManager->find($this->Namespace.$ClassName, $Id);
    }

    /**
     * @param $Entity
     *
     * @return EntityManager
     */
    final public function killEntity($Entity)
    {

        $this->EntityManager->remove($Entity);
        $this->flushCache(get_class($Entity));
        return $this;
    }

    /**
     * @param null|string $Region
     *
     * @return EntityManager
     */
    final public function flushCache($Region = null)
    {

        /** @var MemcachedHandler $Cache */
        $Cache = $this->getCache(new MemcachedHandler());
        $this->EntityManager->flush();
        if (preg_match('!Gatekeeper!', $this->Namespace)) {
            (new DebuggerFactory())->createLogger(new BenchmarkLogger())->addLog(
                'Manager Full-Slot-Flush '.$Cache->getSlot().' Trigger: '.$this->Namespace
            );
            $KeyList = $Cache->getCache()->getAllKeys();
            $ClearList = preg_grep("/^".preg_quote($Cache->getSlot()).".*/is", $KeyList);
            $Cache->getCache()->deleteMulti($ClearList);
            $ClearList = preg_grep("/^".preg_quote('PUBLIC').".*/is", $KeyList);
            $Cache->getCache()->deleteMulti($ClearList);
        } else {
            if (!preg_match('!'.preg_quote('Platform\System\\').'(Archive|Protocol)!', $this->Namespace)) {
                // Clear distributed Cache-System (if possible)
                if (null === $Region) {
                    /** @var MemcachedHandler $Cache */
                    $KeyList = $Cache->getCache()->getAllKeys();
                    $ClearList = preg_grep("/^".preg_quote($Cache->getSlot()).".*/is", $KeyList);
                    (new DebuggerFactory())->createLogger(new BenchmarkLogger())->addLog(
                        'Manager Slot-Flush '.$Cache->getSlot().' Trigger: '.$this->Namespace
                    );
                    $Cache->getCache()->deleteMulti($ClearList);
                    $ClearList = preg_grep("/^".preg_quote('PUBLIC').".*/is", $KeyList);
                    $Cache->getCache()->deleteMulti($ClearList);
                } else {
                    /** @var MemcachedHandler $Cache */
                    $KeyList = $Cache->getCache()->getAllKeys();
                    $RegionList = explode('\\', $this->Namespace);
                    $RegionList[0] = $Cache->getSlotRegion($RegionList[0]);
                    foreach ($RegionList as $Index => $Region) {
                        if ($Index > 0 && !empty( $Region )) {
                            $RegionList[$Index] = $RegionList[$Index - 1].'\\'.$Region;
                        }
                    }
                    $RegionList = array_filter($RegionList);
                    krsort($RegionList);
                    foreach ($RegionList as $Region) {
                        $ClearList = preg_grep("/^".preg_quote($Region).".*/is", $KeyList);
                        if (!empty( $ClearList ) && substr_count($Region, '\\') > 1) {
                            (new DebuggerFactory())->createLogger(new BenchmarkLogger())->addLog(
                                'Manager Region-Flush '.$Cache->getSlot().' '.$Region.' Trigger: '.$this->Namespace
                            );
                            $Cache->getCache()->deleteMulti($ClearList);
                            break;
                        }
                    }
                }
            }
        }
        $this->getCache(new MemoryHandler())->clearCache();
        return $this;
    }

    /**
     * @param $Entity
     *
     * @return EntityManager
     */
    final public function saveEntity($Entity)
    {

        $this->EntityManager->persist($Entity);
        $this->flushCache(get_class($Entity));
        return $this;
    }

    /**
     * MUST use flushCache to commit bulk
     *
     * @param $Entity
     *
     * @return EntityManager
     */
    final public function bulkSaveEntity($Entity)
    {

        $this->EntityManager->persist($Entity);
        return $this;
    }

    /**
     * MUST use flushCache to commit bulk
     *
     * @param $Entity
     *
     * @return EntityManager
     */
    final public function bulkKillEntity($Entity)
    {

        $this->EntityManager->remove($Entity);
        return $this;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    final public function getQueryBuilder()
    {

        return $this->EntityManager->createQueryBuilder();
    }
}
