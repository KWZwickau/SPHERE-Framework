<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 25.11.2016
 * Time: 11:27
 */

namespace SPHERE\Application\Education\Certificate\Generate\Service;

use SPHERE\Application\Education\Certificate\Generate\Service\Entity\TblCertificateSetting;
use SPHERE\Application\Education\Certificate\Generate\Service\Entity\TblGenerateCertificate;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateType;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTask;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
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

        if (!$this->getCertificateSetting()) {
            $this->createCertificateSetting();
        }
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
     * @return false|TblCertificateSetting
     */
    public function getCertificateSetting()
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCertificateSetting',
            array());
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
        $IsDivisionTeacherAvailable = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblGenerateCertificate();
        $Entity->setServiceTblYear($tblYear);
        $Entity->setDate($Date ? new \DateTime($Date) : null);
        $Entity->setName($Name);
        $Entity->setServiceTblCertificateType($tblCertificateType);
        $Entity->setServiceTblAppointedDateTask($tblAppointedDateTask ? $tblAppointedDateTask : null);
        $Entity->setServiceTblBehaviorTask($tblBehaviorTask ? $tblBehaviorTask : null);
        $Entity->setHeadmasterName($HeadmasterName);
        $Entity->setIsDivisionTeacherAvailable($IsDivisionTeacherAvailable);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblGenerateCertificate $tblGenerateCertificate
     * @param $Date
     * @param $IsDivisionTeacherAvailable
     * @param $HeadmasterName
     *
     * @return bool
     */
    public function updateGenerateCertificate(
        TblGenerateCertificate $tblGenerateCertificate,
        $Date,
        $IsDivisionTeacherAvailable,
        $HeadmasterName
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblGenerateCertificate $Entity */
        $Entity = $Manager->getEntityById('TblGenerateCertificate', $tblGenerateCertificate->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setDate($Date ? new \DateTime($Date) : null);
            $Entity->setIsDivisionTeacherAvailable($IsDivisionTeacherAvailable);
            $Entity->setHeadmasterName($HeadmasterName);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param bool $UseCourseForCertificateChoosing
     *
     * @return TblCertificateSetting
     */
    public function createCertificateSetting(
        $UseCourseForCertificateChoosing = true
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblCertificateSetting();
        $Entity->setUseCourseForCertificateChoosing($UseCourseForCertificateChoosing);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }
}