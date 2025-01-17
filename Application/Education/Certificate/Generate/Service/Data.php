<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 25.11.2016
 * Time: 11:27
 */

namespace SPHERE\Application\Education\Certificate\Generate\Service;

use DateTime;
use SPHERE\Application\Education\Certificate\Generate\Service\Entity\TblGenerateCertificate;
use SPHERE\Application\Education\Certificate\Generate\Service\Entity\TblGenerateCertificateSetting;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTask;
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
        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGenerateCertificate', $Id);
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
     * @return false|TblGenerateCertificate[]
     */
    public function getGenerateCertificateAll()
    {
        return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblGenerateCertificate');
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
     * @param TblCommonGender|null $tblCommonGender
     * @param null $AppointedDateForAbsence
     *
     * @return TblGenerateCertificate
     */
    public function createGenerateCertificate(
        TblYear $tblYear,
        $Date,
        $Name,
        TblCertificateType $tblCertificateType,
        TblTask $tblAppointedDateTask = null,
        TblTask $tblBehaviorTask = null,
        string $HeadmasterName = '',
        bool $IsDivisionTeacherAvailable = false,
        TblCommonGender $tblCommonGender = null,
        $AppointedDateForAbsence = null
    ): TblGenerateCertificate {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblGenerateCertificate();
        $Entity->setServiceTblYear($tblYear);
        $Entity->setDate($Date ? new DateTime($Date) : null);
        $Entity->setName($Name);
        $Entity->setServiceTblCertificateType($tblCertificateType);
        $Entity->setServiceTblAppointedDateTask($tblAppointedDateTask);
        $Entity->setServiceTblBehaviorTask($tblBehaviorTask);
        $Entity->setHeadmasterName($HeadmasterName);
        $Entity->setIsDivisionTeacherAvailable($IsDivisionTeacherAvailable);
        $Entity->setServiceTblCommonGenderHeadmaster($tblCommonGender);
        $Entity->setAppointedDateForAbsence($AppointedDateForAbsence ? new DateTime($AppointedDateForAbsence) : null);

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
     * @param null $AppointedDateForAbsence
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
        string $Name = '',
        $AppointedDateForAbsence = null
    ): bool {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblGenerateCertificate $Entity */
        $Entity = $Manager->getEntityById('TblGenerateCertificate', $tblGenerateCertificate->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setDate($Date ? new DateTime($Date) : null);
            $Entity->setIsDivisionTeacherAvailable($IsDivisionTeacherAvailable);
            $Entity->setHeadmasterName($HeadmasterName);
            $Entity->setServiceTblCommonGenderHeadmaster($tblCommonGender);
            $Entity->setServiceTblAppointedDateTask($tblAppointedDateTask);
            $Entity->setServiceTblBehaviorTask($tblBehaviorTask);
            $Entity->setName($Name);
            $Entity->setAppointedDateForAbsence($AppointedDateForAbsence ? new DateTime($AppointedDateForAbsence) : null);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblGenerateCertificate $tblGenerateCertificate
     *
     * @return bool
     */
    public function destroyGenerateCertificate(TblGenerateCertificate $tblGenerateCertificate): bool
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

    /**
     * @param TblGenerateCertificate $tblGenerateCertificate
     * @param $Field
     *
     * @return false|TblGenerateCertificateSetting
     */
    public function getGenerateCertificateSettingBy(TblGenerateCertificate $tblGenerateCertificate, $Field)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGenerateCertificateSetting',
            array(
                TblGenerateCertificateSetting::ATTR_TBL_GENERATE_CERTIFICATE => $tblGenerateCertificate->getId(),
                TblGenerateCertificateSetting::ATTR_FIELD => $Field,
            )
        );
    }

    /**
     * @param TblGenerateCertificate $tblGenerateCertificate
     *
     * @return false|TblGenerateCertificateSetting[]
     */
    public function getGenerateCertificateSettingAllByGenerateCertificate(TblGenerateCertificate $tblGenerateCertificate)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGenerateCertificateSetting',
            array(
                TblGenerateCertificateSetting::ATTR_TBL_GENERATE_CERTIFICATE => $tblGenerateCertificate->getId()
            )
        );
    }

    /**
     * @param TblGenerateCertificate $tblGenerateCertificate
     * @param $Field
     * @param $Value
     *
     * @return TblGenerateCertificateSetting
     */
    public function createGenerateCertificateSetting(
        TblGenerateCertificate $tblGenerateCertificate,
        $Field,
        $Value
    ): TblGenerateCertificateSetting {
        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblGenerateCertificateSetting')->findOneBy(array(
            TblGenerateCertificateSetting::ATTR_TBL_GENERATE_CERTIFICATE => $tblGenerateCertificate->getId(),
            TblGenerateCertificateSetting::ATTR_FIELD => $Field,
        ));
        if ($Entity === null) {
            $Entity = new TblGenerateCertificateSetting();
            $Entity->setTblGenerateCertificate($tblGenerateCertificate);
            $Entity->setField($Field);
            $Entity->setValue($Value);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblGenerateCertificateSetting $tblGenerateCertificateSetting
     * @param $Value
     *
     * @return bool
     */
    public function updateGenerateCertificateSetting(
        TblGenerateCertificateSetting $tblGenerateCertificateSetting,
        $Value
    ): bool {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblGenerateCertificateSetting $Entity */
        $Entity = $Manager->getEntityById('TblGenerateCertificateSetting', $tblGenerateCertificateSetting->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setValue($Value);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }
}