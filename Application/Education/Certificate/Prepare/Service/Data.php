<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.07.2016
 * Time: 11:18
 */

namespace SPHERE\Application\Education\Certificate\Prepare\Service;

use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblCertificatePrepare;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTask;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 * @package SPHERE\Application\Education\Certificate\Prepare\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param $Id
     *
     * @return false|TblCertificatePrepare
     */
    public function getPrepareById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificatePrepare', $Id);
    }

    /**
     * @param TblDivision $tblDivision
     * @param null $IsApproved
     * @param null $IsPrinted
     *
     * @return false|TblCertificatePrepare[]
     */
    public function getPrepareAllByDivision(TblDivision $tblDivision, $IsApproved = null, $IsPrinted = null)
    {

        if ($IsApproved !== null && $IsPrinted !== null) {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblCertificatePrepare',
                array(
                    TblCertificatePrepare::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                    TblCertificatePrepare::ATTR_IS_APPROVED => $IsApproved,
                    TblCertificatePrepare::ATTR_IS_PRINTED => $IsPrinted
                )
            );
        } elseif ($IsApproved !== null) {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblCertificatePrepare',
                array(
                    TblCertificatePrepare::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                    TblCertificatePrepare::ATTR_IS_APPROVED => $IsApproved,
                )
            );
        } elseif ($IsPrinted !== null) {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblCertificatePrepare',
                array(
                    TblCertificatePrepare::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                    TblCertificatePrepare::ATTR_IS_PRINTED => $IsPrinted
                )
            );
        } else {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblCertificatePrepare',
                array(
                    TblCertificatePrepare::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId()
                )
            );
        }
    }

    /**
     * @param TblDivision $tblDivision
     * @param $Date
     * @param $Name
     *
     * @return TblCertificatePrepare
     */
    public function createPrepare(
        TblDivision $tblDivision,
        $Date,
        $Name
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblCertificatePrepare();
        $Entity->setServiceTblDivision($tblDivision);
        $Entity->setDate($Date ? new \DateTime($Date) : null);
        $Entity->setName($Name);
        $Entity->setApproved(false);
        $Entity->setPrinted(false);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblCertificatePrepare $tblPrepare
     * @param $Date
     * @param $Name
     * @param $IsApproved
     * @param $IsPrinted
     * @param TblTask|null $tblAppointedDateTask
     * @param TblTask|null $tblBehaviorTask
     *
     * @return bool
     */
    public function updatePrepare(
        TblCertificatePrepare $tblPrepare,
        $Date,
        $Name,
        $IsApproved,
        $IsPrinted,
        TblTask $tblAppointedDateTask = null,
        TblTask $tblBehaviorTask = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblCertificatePrepare $Entity */
        $Entity = $Manager->getEntityById('TblCertificatePrepare', $tblPrepare->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setDate($Date ? new \DateTime($Date) : null);
            $Entity->setName($Name);
            $Entity->setApproved($IsApproved);
            $Entity->setPrinted($IsPrinted);
            $Entity->setServiceTblAppointedDateTask($tblAppointedDateTask);
            $Entity->setServiceTblBehaviorTask($tblBehaviorTask);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }
}