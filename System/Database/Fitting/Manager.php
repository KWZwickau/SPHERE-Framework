<?php
namespace SPHERE\System\Database\Fitting;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use SPHERE\System\Cache\Cache;
use SPHERE\System\Cache\Type\Memcached;

/**
 * Class Manager
 *
 * @package SPHERE\System\Database\Fitting
 */
class Manager
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
        $this->flushCache();
        return $this;
    }

    /**
     * @return EntityManager
     */
    final public function flushCache()
    {

        $this->EntityManager->flush();
        // Clear distributed Cache-System (if possible)
        (new Cache(new Memcached()))->getCache()->clearCache();
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
        $this->flushCache();
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
}
