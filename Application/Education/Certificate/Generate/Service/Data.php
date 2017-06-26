<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 25.11.2016
 * Time: 11:27
 */

namespace SPHERE\Application\Education\Certificate\Generate\Service;

use SPHERE\Application\Education\Certificate\Generate\Service\Entity\TblGenerateCertificate;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateType;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTask;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonGender;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Education\Certificate\Generate\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param $Id
     *
     * @return false|TblGenerateCertificate
     */
    public function getGenerateCertificateById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblGenerateCertificate', $Id);
    }

    /**
     * @param TblYear $tblYear
     *
     * @return false|TblGenerateCertificate[]
     */
    public function getGenerateCertificateAllByYear(TblYear $tblYear)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblGenerateCertificate',
            array(
                TblGenerateCertificate::ATTR_SERVICE_TBL_YEAR => $tblYear->getId()
            )
        );
    }

    /**
     * @param TblYear $tblYear
     * @param $Date
     * @param $Name
     * @param TblCertificateType $tblCertificateType
     * @param TblTask|null $tblAppointedDateTask
     * @param TblTask|null $tblBehaviorTask
     * @param string $HeadmasterName
     * @param bool $IsDivisionTeacherAvailable
     *
     * @param TblCommonGender $tblCommonGender
     * @return TblGenerateCertificate
     */
    public function createGenerateCertificate(
        TblYear $tblYear,
        $Date,
        $Name,
        TblCertificateType $tblCertificateType,
        TblTask $tblAppointedDateTask = null,
        TblTask $tblBehaviorTask = null,
        $HeadmasterName = '',
        $IsDivisionTeacherAvailable = false,
        TblCommonGender $tblCommonGender = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblGenerateCertificate();
        $Entity->setServiceTblYear($tblYear);
        $Entity->setDate($Date ? new \DateTime($Date) : null);
        $Entity->setName($Name);
        $Entity->setServiceTblCertificateType($tblCertificateType);
        $Entity->setServiceTblAppointedDateTask($tblAppointedDateTask);
        $Entity->setServiceTblBehaviorTask($tblBehaviorTask);
        $Entity->setHeadmasterName($HeadmasterName);
        $Entity->setIsDivisionTeacherAvailable($IsDivisionTeacherAvailable);
        $Entity->setServiceTblCommonGenderHeadmaster($tblCommonGender);
        $Entity->setIsLocked(false);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblGenerateCertificate $tblGenerateCertificate
     * @param $Date
     * @param $IsDivisionTeacherAvailable
     * @param $HeadmasterName
     * @param TblCommonGender|null $tblCommonGender
     * @param TblTask|null $tblAppointedDateTask
     * @param TblTask|null $tblBehaviorTask
     * @param string $Name
     *
     * @return bool
     */
    public function updateGenerateCertificate(
        TblGenerateCertificate $tblGenerateCertificate,
        $Date,
        $IsDivisionTeacherAvailable,
        $HeadmasterName,
        TblCommonGender $tblCommonGender = null,
        TblTask $tblAppointedDateTask = null,
        TblTask $tblBehaviorTask = null,
        $Name = ''
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblGenerateCertificate $Entity */
        $Entity = $Manager->getEntityById('TblGenerateCertificate', $tblGenerateCertificate->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setDate($Date ? new \DateTime($Date) : null);
            $Entity->setIsDivisionTeacherAvailable($IsDivisionTeacherAvailable);
            $Entity->setHeadmasterName($HeadmasterName);
            $Entity->setServiceTblCommonGenderHeadmaster($tblCommonGender);
            $Entity->setServiceTblAppointedDateTask($tblAppointedDateTask);
            $Entity->setServiceTblBehaviorTask($tblBehaviorTask);
            $Entity->setName($Name);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblGenerateCertificate $tblGenerateCertificate
     * @param bool $IsLocked
     *
     * @return bool
     */
    public function lockGenerateCertificate(
        TblGenerateCertificate $tblGenerateCertificate,
        $IsLocked = true
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblGenerateCertificate $Entity */
        $Entity = $Manager->getEntityById('TblGenerateCertificate', $tblGenerateCertificate->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setIsLocked($IsLocked);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    public function destroyGenerateCertificate(TblGenerateCertificate $tblGenerateCertificate)
    {

        $Manager = $this->getEntityManager();

        /** @var TblGenerateCertificate $Entity */
        $Entity = $Manager->getEntityById('TblGenerateCertificate', $tblGenerateCertificate->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->removeEntity($Entity);

            return true;
        }
        return false;
    }
}