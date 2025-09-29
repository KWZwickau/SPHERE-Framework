<?php

namespace SPHERE\Application\App\Authentication\Process\Service;

use Exception;
use SPHERE\Application\App\Authentication\Process\Service\Entity\TblFactor;
use SPHERE\Application\App\Authentication\Process\Service\Entity\TblProcess;
use SPHERE\Application\App\Authentication\Process\Service\Entity\TblStep;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 *
 */
class Data extends AbstractData
{
    public function setupDatabaseContent(): void
    {
        // TODO: Implement setupDatabaseContent() method.
        $tblFactor = $this->createFactor('Credentials', 'Username & Password');
    }

    public function createFactor(string $name, ?string $description = null): ?TblFactor
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $manager = $connection->getEntityManager();
        $entity = $manager->getEntity('TblFactor')->findOneBy([TblFactor::ATTR_NAME => $name]);
        if (null === $entity) {
            $entity = new TblFactor();
            $entity->setName($name);
            $entity->setDescription($description);

            $manager->saveEntity($entity);
            Protocol::useService()->createInsertEntry($connection->getDatabase(), $entity);
        }
        return $entity;
    }

    public function createProcess(TblAccount $tblAccount, TblFactor $tblFactor, bool $isSolved): ?TblProcess
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $manager = $connection->getEntityManager();
        $entity = $manager->getEntity('TblProcess')->findOneBy([
            TblProcess::SERVICE_TBL_ACCOUNT => $tblAccount->getId(),
            TblProcess::ATTR_TBL_FACTOR => $tblFactor->getId()
        ]);
        if (null === $entity) {
            $entity = new TblProcess();
            $entity->setServiceTblAccount($tblAccount);
            $entity->setTblFactor($tblFactor);
            $entity->setIsSolved($isSolved);

            $manager->saveEntity($entity);
            Protocol::useService()->createInsertEntry($connection->getDatabase(), $entity);
        }
        return $entity;
    }

    public function createStep(TblIdentification $tblIdentification, TblFactor $tblFactor): ?TblStep
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $manager = $connection->getEntityManager();
        $entity = $manager->getEntity('TblStep')->findOneBy([
            TblStep::SERVICE_TBL_IDENTIFICATION => $tblIdentification->getId(),
            TblStep::ATTR_TBL_FACTOR => $tblFactor->getId()
        ]);
        if (null === $entity) {
            $entity = new TblStep();
            $entity->setServiceTblIdentification($tblIdentification);
            $entity->setTblFactor($tblFactor);

            $manager->saveEntity($entity);
            Protocol::useService()->createInsertEntry($connection->getDatabase(), $entity);
        }
        return $entity;
    }

    /**
     * @throws Exception
     */
    public function getFactorById(int $id): ?TblFactor
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        /** @var TblFactor $entity */
        $entity = $this->getCachedEntityById(__METHOD__, $connection->getEntityManager(), 'TblFactor', $id);
        if (!$entity) {
            return null;
        }
        return $entity;
    }

    /**
     * @throws Exception
     */
    public function getAllStepByIdentification(TblIdentification $tblIdentification): ?array
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        /** @var TblStep[] $entities */
        $entities = $this->getCachedEntityListBy(__METHOD__, $connection->getEntityManager(), 'TblStep', [
            TblStep::SERVICE_TBL_IDENTIFICATION => $tblIdentification->getId()
        ]);
        if (!$entities) {
            return null;
        }
        return $entities;
    }

    /**
     * @throws Exception
     */
    public function getAllProcessByAccount(TblAccount $tblAccount, ?bool $isSolved = null): ?array
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $criteria = [
            TblProcess::SERVICE_TBL_ACCOUNT => $tblAccount->getId()
        ];
        if (null !== $isSolved) {
            $criteria[TblProcess::ATTR_IS_SOLVED] = $isSolved;
        }
        /** @var TblProcess[] $entities */
        $entities = $this->getCachedEntityListBy(__METHOD__, $connection->getEntityManager(), 'TblProcess', $criteria);
        if (!$entities) {
            return null;
        }
        return $entities;
    }
}
