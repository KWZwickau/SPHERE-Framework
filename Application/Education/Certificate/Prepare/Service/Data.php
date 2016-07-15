<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.07.2016
 * Time: 11:18
 */

namespace SPHERE\Application\Education\Certificate\Prepare\Service;

use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblCertificatePrepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareGrade;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

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
     *
     * @return false|TblCertificatePrepare[]
     */
    public function getPrepareAllByDivision(TblDivision $tblDivision)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificatePrepare',
            array(
                TblCertificatePrepare::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId()
            )
        );
    }

    /**
     * @param TblCertificatePrepare $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblTestType $tblTestType
     *
     * @return false|TblPrepareGrade
     */
    public function getPrepareGradeBySubject(
        TblCertificatePrepare $tblPrepare,
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblTestType $tblTestType
    ) {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPrepareGrade',
            array(
                TblPrepareGrade::ATTR_TBL_CERTIFICATE_PREPARE => $tblPrepare->getId(),
                TblPrepareGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblPrepareGrade::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                TblPrepareGrade::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                TblPrepareGrade::ATTR_SERVICE_TBL_TEST_TYPE => $tblTestType->getId(),
            )
        );
    }

    /**
     * @param TblCertificatePrepare $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param TblTestType $tblTestType
     * @param TblGradeType $tblGradeType
     *
     * @return false|TblPrepareGrade
     */
    public function getPrepareGradeByGradeType(
        TblCertificatePrepare $tblPrepare,
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        TblTestType $tblTestType,
        TblGradeType $tblGradeType
    ) {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPrepareGrade',
            array(
                TblPrepareGrade::ATTR_TBL_CERTIFICATE_PREPARE => $tblPrepare->getId(),
                TblPrepareGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblPrepareGrade::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                TblPrepareGrade::ATTR_SERVICE_TBL_TEST_TYPE => $tblTestType->getId(),
                TblPrepareGrade::ATTR_SERVICE_TBL_GRADE_TYPE => $tblGradeType->getId(),
            )
        );
    }

    /**
     * @param TblCertificatePrepare $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblTestType $tblTestType
     *
     * @return false|TblPrepareGrade[]
     */
    public function getPrepareGradeAllByPerson(
        TblCertificatePrepare $tblPrepare,
        TblPerson $tblPerson,
        TblTestType $tblTestType
    ) {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPrepareGrade',
            array(
                TblPrepareGrade::ATTR_TBL_CERTIFICATE_PREPARE => $tblPrepare->getId(),
                TblPrepareGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblPrepareGrade::ATTR_SERVICE_TBL_TEST_TYPE => $tblTestType->getId(),
            )
        );
    }

    /**
     * @param TblCertificatePrepare $tblPrepare
     * @param TblTestType $tblTestType
     *
     * @return false|TblPrepareGrade[]
     */
    public function getPrepareGradeAllByPrepare(
        TblCertificatePrepare $tblPrepare,
        TblTestType $tblTestType
    ) {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPrepareGrade',
            array(
                TblPrepareGrade::ATTR_TBL_CERTIFICATE_PREPARE => $tblPrepare->getId(),
                TblPrepareGrade::ATTR_SERVICE_TBL_TEST_TYPE => $tblTestType->getId()
            )
        );
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

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblCertificatePrepare $tblPrepare
     * @param $Date
     * @param $Name
     * @param TblTask|null $tblAppointedDateTask
     * @param TblTask|null $tblBehaviorTask
     * @param TblPerson|null $tblPersonSigner
     * @param bool|false $IsAppointedDateTaskUpdated
     *
     * @return bool
     */
    public function updatePrepare(
        TblCertificatePrepare $tblPrepare,
        $Date,
        $Name,
        TblTask $tblAppointedDateTask = null,
        TblTask $tblBehaviorTask = null,
        TblPerson $tblPersonSigner = null,
        $IsAppointedDateTaskUpdated = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblCertificatePrepare $Entity */
        $Entity = $Manager->getEntityById('TblCertificatePrepare', $tblPrepare->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setDate($Date ? new \DateTime($Date) : null);
            $Entity->setName($Name);
            $Entity->setServiceTblAppointedDateTask($tblAppointedDateTask);
            $Entity->setServiceTblBehaviorTask($tblBehaviorTask);
            $Entity->setServiceTblPersonSigner($tblPersonSigner);
            $Entity->setAppointedDateTaskUpdated($IsAppointedDateTaskUpdated);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblCertificatePrepare $tblPrepare
     * @param TblTestType $tblTestType
     * @param $gradeList
     */
    public function createPrepareGrades(
        TblCertificatePrepare $tblPrepare,
        TblTestType $tblTestType,
        $gradeList
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        if (is_array($gradeList)) {
            foreach ($gradeList as $personId => $gradeArray) {
                $tblPerson = Person::useService()->getPersonById($personId);
                if ($tblPerson && is_array($gradeArray)) {
                    /** @var TblGrade $tblGrade */
                    foreach ($gradeArray as $tblGrade) {
                        $Entity = new TblPrepareGrade();
                        $Entity->setTblCertificatePrepare($tblPrepare);
                        $Entity->setServiceTblDivision($tblGrade->getServiceTblDivision() ? $tblGrade->getServiceTblDivision() : null);
                        $Entity->setServiceTblSubject($tblGrade->getServiceTblSubject() ? $tblGrade->getServiceTblSubject() : null);
                        $Entity->setServiceTblPerson($tblGrade->getServiceTblPerson() ? $tblGrade->getServiceTblPerson() : null);
                        $Entity->setServiceTblTestType($tblTestType);
                        $Entity->setGrade($tblGrade->getDisplayGrade());

                        $Manager->bulkSaveEntity($Entity);
                        // ToDo GCK Protokoll bulkSave sonst witzlos
                        // Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
                    }
                }
            }

            $Manager->flushCache();
        }
    }

    /**
     * @param TblCertificatePrepare $tblPrepare
     * @param TblTestType $tblTestType
     */
    public function destroyPrepareGrades(
        TblCertificatePrepare $tblPrepare,
        TblTestType $tblTestType
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $tblPrepareGradeList = $this->getPrepareGradeAllByPrepare(
            $tblPrepare, $tblTestType
        );
        if ($tblPrepareGradeList) {
            foreach ($tblPrepareGradeList as $tblPrepareGrade) {
                $Entity = $Manager->getEntity('TblPrepareGrade')->findOneBy(array('Id' => $tblPrepareGrade->getId()));
                if (null !== $Entity) {
                    /** @var Element $Entity */
                    // ToDo GCK Protokoll bulkSave sonst witzlos
                    // Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
                    $Manager->bulkKillEntity($Entity);
                }
            }

            $Manager->flushCache();
        }
    }

    /**
     * @param TblCertificatePrepare $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param TblTestType $tblTestType
     * @param TblGradeType $tblGradeType
     * @param $Grade
     *
     * @return TblPrepareGrade
     */
    public function updatePrepareGradeForBehavior(
        TblCertificatePrepare $tblPrepare,
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        TblTestType $tblTestType,
        TblGradeType $tblGradeType,
        $Grade
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblPrepareGrade $Entity */
        $Entity = $Manager->getEntity('TblPrepareGrade')->findOneBy(array(
            TblPrepareGrade::ATTR_TBL_CERTIFICATE_PREPARE => $tblPrepare->getId(),
            TblPrepareGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            TblPrepareGrade::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
            TblPrepareGrade::ATTR_SERVICE_TBL_TEST_TYPE => $tblTestType->getId(),
            TblPrepareGrade::ATTR_SERVICE_TBL_GRADE_TYPE => $tblGradeType->getId(),
        ));
        if ($Entity === null) {
            $Entity = new TblPrepareGrade();
            $Entity->setTblCertificatePrepare($tblPrepare);
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setServiceTblDivision($tblDivision);
            $Entity->setServiceTblTestType($tblTestType);
            $Entity->setServiceTblGradeType($tblGradeType);
            $Entity->setGrade($Grade);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        } else {
            $Protocol = clone $Entity;
            $Entity->setGrade($Grade);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
        }

        return $Entity;
    }
}