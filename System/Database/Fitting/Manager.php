<?php
namespace SPHERE\System\Database\Fitting;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblSession;
use SPHERE\System\Cache\Handler\DataCacheHandler;
use SPHERE\System\Cache\Handler\DefaultHandler;
use SPHERE\System\Cache\Handler\MemcachedHandler;
use SPHERE\System\Cache\Handler\MemoryHandler;
use SPHERE\System\Cache\Handler\RedisHandler;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Database\Link\Register;
use SPHERE\System\Debugger\Logger\CacheLogger;
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
    /** @var null|Identifier $Identifier */
    private $Identifier = null;

    /**
     * @param EntityManager $EntityManager
     * @param string $Namespace
     * @param Identifier $Identifier
     */
    final function __construct(EntityManager $EntityManager, $Namespace, Identifier $Identifier)
    {

        $this->EntityManager = $EntityManager;
        $this->Namespace = $Namespace;
        $this->Identifier = $Identifier;
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
     * @return Repository|EntityRepository
     */
    final public function getRepository($ClassName)
    {

        // Replace "Table"-Name with "Database"."Table"-Name
        /** @var \MOC\V\Component\Database\Component\Bridge\Repository\Doctrine2ORM $Connection */
        $Connection = (new Register())->getConnection( $this->Identifier )->getConnection();
        $ClassMetadata = $this->EntityManager->getClassMetadata($this->Namespace.$ClassName);
        if( 'mssql' == $Connection->getConnection()->getDatabasePlatform()->getName() ) {
            $ClassMetadata->setPrimaryTable(array(
                'name' => $ClassMetadata->getTableName(),
                'schema' => $Connection->getConnection()->getDatabase().'.dbo'
            ));
        } else {
            $ClassMetadata->setPrimaryTable(array(
                'name' => $ClassMetadata->getTableName(),
                'schema' => $Connection->getConnection()->getDatabase()
            ));
        }

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
     * @return Entity|object
     */
    final public function getEntityById($ClassName, $Id)
    {

        // MUST NOT USE Cache-System
        return $this->EntityManager->find($this->Namespace.$ClassName, $Id);
    }

    /**
     * @param Element $Entity
     *
     * @return Manager|EntityManager
     */
    final public function killEntity($Entity)
    {

        $Entity = $this->prepareEntity( $Entity );
        $this->EntityManager->remove($Entity);
        $this->flushCache(get_class($Entity));
        (new DataCacheHandler(__METHOD__))->addDependency($Entity)->clearData();
        return $this;
    }

    /**
     * @param null|string $Region
     *
     * @return Manager|EntityManager
     */
    final public function flushCache($Region = null)
    {

        /** @var DefaultHandler|MemcachedHandler|RedisHandler $Cache */
        $Cache = $this->getCache(new RedisHandler());

        if( is_a($Cache, RedisHandler::class) || is_a($Cache,MemcachedHandler::class) ){
            $cacheIsActive = true;
        } else {
            $cacheIsActive = false;
        }

        // TblSession MUST USE Forced Entity, or todo: special case TblSession
        if ($cacheIsActive && $Region !== TblSession::class) {
            if (!preg_match('!'.preg_quote('Platform\\System\\', '!').'(Archive|Protocol)!', $this->Namespace)) {
                // Clear distributed Cache-System (if possible)
                $KeyList = $Cache->fetchKeys($Cache->getSlot().'*');
                if (null === $Region) {
                    $ClearList = preg_grep("/^".preg_quote($Cache->getSlot(), '/').".*/is", $KeyList);
                    $this->getLogger(new CacheLogger())->addLog(
                        'Manager Slot-Flush '.$Cache->getSlot().' Trigger: '.$this->Namespace
                    );
                    $Cache->removeKeys($ClearList);
                } else {
                    $RegionList = explode('\\', $this->Namespace);
                    $RegionList = array_filter($RegionList);
                    $RegionList[0] = $Cache->getSlotRegion($RegionList[0]);
                    $RegionList = array_slice($RegionList, 0, count($RegionList) - 2);
                    foreach ($RegionList as $Index => $Region) {
                        if ($Index > 0 && !empty( $Region ) && $Index < 4) {
                            $RegionList[$Index] = $RegionList[$Index - 1].'\\'.$Region;
                        }
                    }
                    $RegionList = array_filter($RegionList);
                    krsort($RegionList);
                    foreach ($RegionList as $Region) {
                        $ClearList = preg_grep("/^".preg_quote($Region, '/').".*/is", $KeyList);
                        if (!empty( $ClearList ) && substr_count($Region, '\\') > 1) {
                            $this->getLogger(new CacheLogger())->addLog(
                                'Manager Region-Flush '.$Cache->getSlot().' '.$Region.' Trigger: '.$this->Namespace
                            );
                            $Cache->removeKeys($ClearList);
                            break;
                        }
                    }
                }
                $KeyList = $Cache->fetchKeys('PUBLIC*');
                $ClearList = preg_grep("/^".preg_quote('PUBLIC', '/').".*/is", $KeyList);
                $Cache->removeKeys($ClearList);
            }
        }
        $this->getCache(new MemoryHandler())->clearCache();
        $this->EntityManager->flush();
        return $this;
    }

    /**
     * @param Element $Entity
     *
     * @return Manager|EntityManager
     */
    final public function removeEntity($Entity)
    {

        $Entity->setEntityRemove(true);
        $this->saveEntity($Entity);
        return $this;
    }

    /**
     * @param Element $Entity
     *
     * @return Manager|EntityManager
     */
    final public function saveEntity($Entity)
    {

        $this->EntityManager->persist($Entity);
        $this->flushCache(get_class($Entity));
        (new DataCacheHandler(__METHOD__))->addDependency($Entity)->clearData();
        return $this;
    }

    /**
     * @param Element|object $Entity
     *
     * @return Manager|EntityManager
     */
    final public function updateEntity($Entity)
    {
        if(!$this->EntityManager->contains($Entity)) {
            throw new \RuntimeException('Entity not recognized');
        }
        $Entity->lifecycleUpdate();
        $this->flushCache(get_class($Entity));
        (new DataCacheHandler(__METHOD__))->addDependency($Entity)->clearData();
        return $this;
    }

    /**
     * @param Element $Entity
     *
     * @return Manager|EntityManager
     */
    final public function saveEntityWithSetId($Entity)
    {
        $this->EntityManager->persist($Entity);

        $metadata = $this->EntityManager->getClassMetaData(get_class($Entity));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);

        $this->EntityManager->flush();
    }

    /**
     * MUST use flushCache to commit bulk
     *
     * @param $Entity
     *
     * @return Manager|EntityManager
     */
    final public function bulkSaveEntity($Entity)
    {

        $Entity = $this->prepareEntity( $Entity, false );
        $this->EntityManager->persist($Entity);
        return $this;
    }

    /**
     * MUST use flushCache to commit bulk
     *
     * @param $Entity
     *
     * @return Manager|EntityManager
     */
    final public function bulkKillEntity($Entity)
    {

        $Entity = $this->prepareEntity( $Entity );
        $this->EntityManager->remove($Entity);
        return $this;
    }

    /**
     * @param Element $Entity
     * @param bool $updateLifeCycle
     * @return Element
     */
    private function prepareEntity( $Entity, $updateLifeCycle = false ) {

        if( !$this->EntityManager->contains( $Entity ) ) {
            /** @var Element $Entity */
            $Entity = $this->EntityManager->merge($Entity);
            if( $updateLifeCycle ) {
                if (empty($Entity->getEntityCreate())) {
                    $Entity->lifecycleCreate();
                } else {
                    $Entity->lifecycleUpdate();
                }
            }
        }
        return $Entity;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    final public function getQueryBuilder()
    {

        return $this->EntityManager->createQueryBuilder();
    }
}
