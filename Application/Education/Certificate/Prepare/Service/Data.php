<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.07.2016
 * Time: 11:18
 */

namespace SPHERE\Application\Education\Certificate\Prepare\Service;

use DateTime;
use SPHERE\Application\Education\Certificate\Generate\Service\Entity\TblGenerateCertificate;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveStudent;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareAdditionalGrade;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareAdditionalGradeType;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareComplexExam;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareGrade;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareInformation;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareStudent;
use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Data
 * @package SPHERE\Application\Education\Certificate\Prepare\Service
 */
class Data extends DataLeave
{

    public function setupDatabaseContent()
    {

        $this->createPrepareAdditionalGradeType('Vorjahres Note', 'PRIOR_YEAR_GRADE');

        // Realschulabschluss
        $this->createPrepareAdditionalGradeType('Jn (Jahresnote)', 'JN');
        $this->createPrepareAdditionalGradeType('Ps (schriftliche Prüfung)', 'PS');
        $this->createPrepareAdditionalGradeType('Pm (mündliche Prüfung)', 'PM');
        $this->createPrepareAdditionalGradeType('Pz (Zusatz-Prüfung)', 'PZ');

        // Hauptschulabschluss
        $this->createPrepareAdditionalGradeType('J (vorläufige Jahresleistung [Notendurchschnitt])', 'J');
        $this->createPrepareAdditionalGradeType('Ls (Leistungsnachweisnote [schriftlich])', 'LS');
        $this->createPrepareAdditionalGradeType('Lm (Leistungsnachweisnote [mündlich])', 'LM');

        // Real + Hauptschulabschluss
        $this->createPrepareAdditionalGradeType('En (Endnote)', 'EN');

        // Abitur
        $this->createPrepareAdditionalGradeType('11/1', '11-1');
        $this->createPrepareAdditionalGradeType('11/2', '11-2');
        $this->createPrepareAdditionalGradeType('12/1', '12-1');
        $this->createPrepareAdditionalGradeType('12/2', '12-2');
        $this->createPrepareAdditionalGradeType('schriftliche Prüfung', 'WRITTEN_EXAM');
        $this->createPrepareAdditionalGradeType('mündliche Prüfung', 'VERBAL_EXAM');
        $this->createPrepareAdditionalGradeType('zusätzliche mündliche Prüfung', 'EXTRA_VERBAL_EXAM');
        $this->createPrepareAdditionalGradeType('Klasse 10', 'LEVEL-10');

        // migration TblLeaveStudent serviceTblDivision -> serviceTblYear
        // kann später wieder entfernt werden
        if (($tblLeaveStudentList = $this->getLeaveStudentAllByYearIsNull())) {
            $updateList = array();
            foreach ($tblLeaveStudentList as $tblLeaveStudent) {
                if (($tblDivision = $tblLeaveStudent->getServiceTblDivision())
                    && ($tblYear = $tblDivision->getServiceTblYear())
                ) {
                    $tblLeaveStudent->setServiceTblYear($tblYear);
                    $updateList[] = $tblLeaveStudent;
                } else {
                    $this->destroyLeaveStudent($tblLeaveStudent);
                }
            }
            if (!empty($updateList)) {
                $this->updateEntityListBulk($updateList);
            }
        }
    }

    /**
     * @param $Id
     *
     * @return false|TblPrepareCertificate
     */
    public function getPrepareById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblPrepareCertificate', $Id);
    }

    /**
     * @deprecated
     *
     * @param TblDivision $tblDivision
     * @param bool $IsGradeInformation
     *
     * @return false|Entity\TblPrepareCertificate[]
     */
    public function getPrepareAllByDivision(TblDivision $tblDivision, $IsGradeInformation = false)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblPrepareCertificate',
            array(
                TblPrepareCertificate::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
//                TblPrepareCertificate::ATTR_IS_GRADE_INFORMATION => $IsGradeInformation
            )
        );
    }

    /**
     *
     * @return false|TblPrepareCertificate[]
     */
    public function getPrepareAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblPrepareCertificate'
        );
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblTestType $tblTestType
     *
     * @return false|TblPrepareGrade
     */
    public function getPrepareGradeBySubject(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblTestType $tblTestType
    ) {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPrepareGrade',
            array(
                TblPrepareGrade::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepare->getId(),
                TblPrepareGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblPrepareGrade::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                TblPrepareGrade::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                TblPrepareGrade::ATTR_SERVICE_TBL_TEST_TYPE => $tblTestType->getId(),
            )
        );
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblTestType $tblTestType
     * @param TblGradeType $tblGradeType
     *
     * @return false|TblPrepareGrade
     */
    public function getPrepareGradeByGradeType(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblTestType $tblTestType,
        TblGradeType $tblGradeType
    ) {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPrepareGrade',
            array(
                TblPrepareGrade::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepare->getId(),
                TblPrepareGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblPrepareGrade::ATTR_SERVICE_TBL_TEST_TYPE => $tblTestType->getId(),
                TblPrepareGrade::ATTR_SERVICE_TBL_GRADE_TYPE => $tblGradeType->getId(),
            )
        );
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblTestType $tblTestType
     *
     * @return false|TblPrepareGrade[]
     */
    public function getPrepareGradesByPrepare(
        TblPrepareCertificate $tblPrepare,
        TblTestType $tblTestType
    ) {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPrepareGrade',
            array(
                TblPrepareGrade::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepare->getId(),
                TblPrepareGrade::ATTR_SERVICE_TBL_TEST_TYPE => $tblTestType->getId(),
            )
        );
    }

    /**
     * @deprecated use getBehaviorGradeAllByPrepareCertificateAndPerson
     *
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblTestType $tblTestType
     * @param bool $IsForced
     *
     * @return false|TblPrepareGrade[]
     * @throws \Exception
     */
    public function getPrepareGradeAllByPerson(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblTestType $tblTestType,
        $IsForced = false
    ) {

        if ($IsForced) {
            return $this->getForceEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblPrepareGrade',
                array(
                    TblPrepareGrade::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepare->getId(),
                    TblPrepareGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                    TblPrepareGrade::ATTR_SERVICE_TBL_TEST_TYPE => $tblTestType->getId(),
                )
            );
        } else {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblPrepareGrade',
                array(
                    TblPrepareGrade::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepare->getId(),
                    TblPrepareGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                    TblPrepareGrade::ATTR_SERVICE_TBL_TEST_TYPE => $tblTestType->getId(),
                )
            );
        }
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblTestType $tblTestType
     *
     * @return false|TblPrepareGrade[]
     */
    public function getPrepareGradeAllByPrepare(
        TblPrepareCertificate $tblPrepare,
        TblTestType $tblTestType
    ) {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPrepareGrade',
            array(
                TblPrepareGrade::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepare->getId(),
                TblPrepareGrade::ATTR_SERVICE_TBL_TEST_TYPE => $tblTestType->getId()
            )
        );
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param bool $isForced
     *
     * @return false|TblPrepareStudent
     */
    public function getPrepareStudentBy(TblPrepareCertificate $tblPrepare, TblPerson $tblPerson, $isForced = false)
    {

        if ($isForced) {
            return $this->getForceEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPrepareStudent',
                array(
                    TblPrepareStudent::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepare->getId(),
                    TblPrepareStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                )
            );
        } else {
            return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPrepareStudent',
                array(
                    TblPrepareStudent::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepare->getId(),
                    TblPrepareStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                )
            );
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblCertificate|null $tblCertificate
     *
     * @return false|TblPrepareStudent[]
     */
    public function getPrepareStudentAllByPerson(TblPerson $tblPerson, TblCertificate $tblCertificate = null)
    {
        if ($tblCertificate) {
            return $this->getForceEntityListBy(__METHOD__, $this->getEntityManager(), 'TblPrepareStudent', array(
                TblPrepareStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblPrepareStudent::ATTR_SERVICE_TBL_CERTIFICATE => $tblCertificate->getId()
            ));
        } else {
            return $this->getForceEntityListBy(__METHOD__, $this->getEntityManager(), 'TblPrepareStudent', array(
                TblPrepareStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
        }
    }

    /**
     * @param $Id
     *
     * @return false|TblPrepareStudent
     */
    public function getPrepareStudentById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblPrepareStudent', $Id);
    }

    /**
     * @param bool|false $IsApproved
     * @param bool|false $IsPrinted
     *
     * @return false|TblPrepareStudent[]
     */
    public function getPrepareStudentAllWhere($IsApproved = false, $IsPrinted = false)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPrepareStudent',
            array(
                TblPrepareStudent::ATTR_IS_APPROVED => $IsApproved,
                TblPrepareStudent::ATTR_IS_PRINTED => $IsPrinted
            )
        );
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param $Field
     *
     * @return false|TblPrepareInformation
     */
    public function getPrepareInformationBy(TblPrepareCertificate $tblPrepare, TblPerson $tblPerson, $Field)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPrepareInformation',
            array(
                TblPrepareInformation::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepare->getId(),
                TblPrepareInformation::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblPrepareInformation::ATTR_FIELD => $Field,
            )
        );
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     *
     * @return false|TblPrepareInformation[]
     */
    public function getPrepareInformationAllByPerson(TblPrepareCertificate $tblPrepare, TblPerson $tblPerson)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblPrepareInformation',
            array(
                TblPrepareInformation::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepare->getId(),
                TblPrepareInformation::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            )
        );
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     *
     * @return false|TblPrepareInformation[]
     */
    public function getPrepareInformationAllByPrepare(TblPrepareCertificate $tblPrepare)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblPrepareInformation',
            array(
                TblPrepareInformation::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepare->getId()
            )
        );
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblGenerateCertificate|null $tblGenerateCertificate
     * @param TblPerson|null $tblPersonSigner
     *
     * @return TblPrepareCertificate
     */
    public function createPrepare(
        TblDivisionCourse $tblDivisionCourse,
        ?TblGenerateCertificate $tblGenerateCertificate,
        ?TblPerson $tblPersonSigner
    ): TblPrepareCertificate {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblPrepareCertificate $Entity */
        $Entity = $Manager->getEntity('TblPrepareCertificate')->findOneBy(array(
            TblPrepareCertificate::ATTR_SERVICE_TBL_GENERATE_CERTIFICATE => $tblGenerateCertificate->getId(),
            TblPrepareCertificate::ATTR_SERVICE_TBL_DIVISION => $tblDivisionCourse->getId()
        ));

        if ($Entity === null) {
            $Entity = new TblPrepareCertificate();
            $Entity->setServiceTblGenerateCertificate($tblGenerateCertificate);
            $Entity->setServiceTblDivision($tblDivisionCourse);
            $Entity->setServiceTblPersonSigner($tblPersonSigner);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson|null $tblPersonSigner
     * @param bool IsPrepared
     *
     * @return bool
     */
    public function updatePrepare(
        TblPrepareCertificate $tblPrepare,
        ?TblPerson $tblPersonSigner,
        bool $IsPrepared
    ): bool {
        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblPrepareCertificate $Entity */
        $Entity = $Manager->getEntityById('TblPrepareCertificate', $tblPrepare->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setServiceTblPersonSigner($tblPersonSigner);
            $Entity->setIsPrepared($IsPrepared);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblTestType $tblTestType
     * @param TblGradeType $tblGradeType
     * @param $Grade
     *
     * @return TblPrepareGrade
     */
    public function updatePrepareGradeForBehavior(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblTestType $tblTestType,
        TblGradeType $tblGradeType,
        $Grade
    ): TblPrepareGrade {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblPrepareGrade $Entity */
        $Entity = $Manager->getEntity('TblPrepareGrade')->findOneBy(array(
            TblPrepareGrade::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepare->getId(),
            TblPrepareGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            TblPrepareGrade::ATTR_SERVICE_TBL_TEST_TYPE => $tblTestType->getId(),
            TblPrepareGrade::ATTR_SERVICE_TBL_GRADE_TYPE => $tblGradeType->getId(),
        ));
        if ($Entity === null) {
            $Entity = new TblPrepareGrade();
            $Entity->setTblPrepareCertificate($tblPrepare);
            $Entity->setServiceTblPerson($tblPerson);
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

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblCertificate|null $tblCertificate
     * @param bool|false $IsApproved
     * @param bool|false $IsPrinted
     * @param null $ExcusedDays
     * @param null $UnexcusedDays
     *
     * @return TblPrepareStudent
     */
    public function createPrepareStudent(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblCertificate $tblCertificate = null,
        $IsApproved = false,
        $IsPrinted = false,
        $ExcusedDays = null,
        $UnexcusedDays = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblPrepareStudent')->findOneBy(array(
            TblPrepareStudent::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepare->getId(),
            TblPrepareStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
        ));
        if ($Entity === null) {
            $Entity = new TblPrepareStudent();
            $Entity->setTblPrepareCertificate($tblPrepare);
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setServiceTblCertificate($tblCertificate ? $tblCertificate : null);
            $Entity->setApproved($IsApproved);
            $Entity->setPrinted($IsPrinted);
            $Entity->setExcusedDays($ExcusedDays);
            $Entity->setUnexcusedDays($UnexcusedDays);
            $Entity->setIsPrepared(false);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblPrepareStudent $tblPrepareStudent
     * @param TblCertificate|null $tblCertificate
     * @param bool|false $IsApproved
     * @param bool|false $IsPrinted
     * @param null $ExcusedDays
     * @param null $ExcusedDaysFromLessons
     * @param null $UnexcusedDays
     * @param null $UnexcusedDaysFromLessons
     * @param TblPerson|null $tblPersonSigner
     *
     * @return bool
     */
    public function updatePrepareStudent(
        TblPrepareStudent $tblPrepareStudent,
        TblCertificate $tblCertificate = null,
        bool $IsApproved = false,
        bool $IsPrinted = false,
        $ExcusedDays = null,
        $ExcusedDaysFromLessons = null,
        $UnexcusedDays = null,
        $UnexcusedDaysFromLessons = null,
        TblPerson $tblPersonSigner = null
    ): bool {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblPrepareStudent $Entity */
        $Entity = $Manager->getEntityById('TblPrepareStudent', $tblPrepareStudent->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setServiceTblCertificate($tblCertificate);
            $Entity->setApproved($IsApproved);
            $Entity->setPrinted($IsPrinted);
            $Entity->setExcusedDays($ExcusedDays);
            $Entity->setExcusedDaysFromLessons($ExcusedDaysFromLessons);
            $Entity->setUnexcusedDays($UnexcusedDays);
            $Entity->setUnexcusedDaysFromLessons($UnexcusedDaysFromLessons);
            $Entity->setServiceTblPersonSigner($tblPersonSigner);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }


    /**
     * @param array $Data
     */
    public function createPrepareStudentSetBulkTemplates(array $Data)
    {
        $Manager = $this->getConnection()->getEntityManager();

        foreach ($Data as $prepareId => $personList) {
            if (($tblPrepare = $this->getPrepareById($prepareId) )) {
                foreach ($personList as $personId => $tblCertificate) {
                    if (($tblPerson = Person::useService()->getPersonById($personId))) {
                        $Entity = $Manager->getEntity('TblPrepareStudent')->findOneBy(array(
                            TblPrepareStudent::ATTR_TBL_PREPARE_CERTIFICATE => $prepareId,
                            TblPrepareStudent::ATTR_SERVICE_TBL_PERSON => $personId,
                        ));

                        if ($Entity === null) {
                            $Entity = new TblPrepareStudent();
                            $Entity->setTblPrepareCertificate($tblPrepare);
                            $Entity->setServiceTblPerson($tblPerson);
                            $Entity->setServiceTblCertificate($tblCertificate);
                            $Entity->setApproved(false);
                            $Entity->setPrinted(false);
                            $Entity->setIsPrepared(false);

                            $Manager->bulkSaveEntity($Entity);
                            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity, true);
                        }
                    }
                }
            }
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param $Field
     * @param $Value
     *
     * @return TblPrepareInformation
     */
    public function createPrepareInformation(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        $Field,
        $Value
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblPrepareInformation')->findOneBy(array(
            TblPrepareInformation::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepare->getId(),
            TblPrepareInformation::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            TblPrepareInformation::ATTR_FIELD => $Field,
        ));
        if ($Entity === null) {
            $Entity = new TblPrepareInformation();
            $Entity->setTblPrepareCertificate($tblPrepare);
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setField($Field);
            $Entity->setValue($Value);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblPrepareInformation $tblPrepareInformation
     * @param $Field
     * @param $Value
     *
     * @return bool
     */
    public function updatePrepareInformation(
        TblPrepareInformation $tblPrepareInformation,
        $Field,
        $Value
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblPrepareInformation $Entity */
        $Entity = $Manager->getEntityById('TblPrepareInformation', $tblPrepareInformation->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setField($Field);
            $Entity->setValue($Value);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblGenerateCertificate $tblGenerateCertificate
     *
     * @return false|TblPrepareCertificate[]
     */
    public function getPrepareAllByGenerateCertificate(TblGenerateCertificate $tblGenerateCertificate)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblPrepareCertificate', array(
                TblPrepareCertificate::ATTR_SERVICE_TBL_GENERATE_CERTIFICATE => $tblGenerateCertificate->getId()
            )
        );
    }

    /**
     * @param TblPrepareStudent $tblPrepareStudent
     *
     * @return bool
     */
    public function updatePrepareStudentSetApproved(TblPrepareStudent $tblPrepareStudent): bool
    {
        $Manager = $this->getConnection()->getEntityManager();

        $useClassRegisterForAbsence = ($tblSettingAbsence = Consumer::useService()->getSetting('Education', 'ClassRegister', 'Absence', 'UseClassRegisterForAbsence'))
            && $tblSettingAbsence->getValue();

        $date = null;
        if (($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())) {
            if (($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())
                && $tblGenerateCertificate->getAppointedDateForAbsence()
            ) {
                $date = new DateTime($tblGenerateCertificate->getAppointedDateForAbsence());
            } else {
                $date = new DateTime($tblPrepare->getDate());
            }
        }

        // Update
        /** @var TblPrepareStudent $Entity */
        $Entity = $Manager->getEntityById('TblPrepareStudent', $tblPrepareStudent->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setApproved(true);
            $Entity->setPrinted(false);

            // Fehlzeiten aus dem Klassenbuch übernehmen
            // todo Fehlzeiten aus dem Klassenbuch übernehmen
            if ($useClassRegisterForAbsence && false) {
                $Entity->setExcusedDays(Absence::useService()->getExcusedDaysByPerson(
                    $tblPerson,
                    $tblDivision,
                    $date
                ));
                $Entity->setUnexcusedDays(Absence::useService()->getUnexcusedDaysByPerson(
                    $tblPerson,
                    $tblDivision,
                    $date
                ));
            }

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
        }

        return true;
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     *
     * @return bool
     */
    public function updatePrepareStudentListSetApproved(TblPrepareCertificate $tblPrepare): bool
    {
        $Manager = $this->getConnection()->getEntityManager();

        $useClassRegisterForAbsence = ($tblSettingAbsence = Consumer::useService()->getSetting('Education', 'ClassRegister', 'Absence', 'UseClassRegisterForAbsence'))
            && $tblSettingAbsence->getValue();

        if (($tblPrepareStudentList = $this->getPrepareStudentAllByPrepare($tblPrepare))) {
            if (($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())
                && $tblGenerateCertificate->getAppointedDateForAbsence()
            ) {
                $date = new DateTime($tblGenerateCertificate->getAppointedDateForAbsence());
            } else {
                $date = new DateTime($tblPrepare->getDate());
            }
            foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                if (($tblPerson = $tblPrepareStudent->getServiceTblPerson())
                    && !$tblPrepareStudent->isApproved()
                ) {
                    // Update
                    /** @var TblPrepareStudent $Entity */
                    $Entity = $Manager->getEntityById('TblPrepareStudent', $tblPrepareStudent->getId());
                    $Protocol = clone $Entity;
                    if (null !== $Entity) {
                        $Entity->setApproved(true);
                        $Entity->setPrinted(false);

                        // Fehlzeiten aus dem Klassenbuch übernehmen
                        // todo Fehlzeiten aus dem Klassenbuch übernehmen
                        if ($useClassRegisterForAbsence && false) {
                            $Entity->setExcusedDays(Absence::useService()->getExcusedDaysByPerson(
                                $tblPerson,
                                $tblDivision,
                                $date
                            ));
                            $Entity->setUnexcusedDays(Absence::useService()->getUnexcusedDaysByPerson(
                                $tblPerson,
                                $tblDivision,
                                $date
                            ));
                        }

                        $Manager->bulkSaveEntity($Entity);
                        Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity, true);
                    }
                }
            }

            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
        }

        return true;
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     *
     * @return bool
     */
    public function updatePrepareStudentListResetApproved(TblPrepareCertificate $tblPrepare): bool
    {
        $Manager = $this->getConnection()->getEntityManager();

        $useClassRegisterForAbsence = ($tblSettingAbsence = Consumer::useService()->getSetting('Education', 'ClassRegister', 'Absence', 'UseClassRegisterForAbsence'))
            && $tblSettingAbsence->getValue();

        if (($tblPrepareStudentList = $this->getPrepareStudentAllByPrepare($tblPrepare))) {
            foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                if ($tblPrepareStudent->isApproved()) {
                    // Update
                    /** @var TblPrepareStudent $Entity */
                    $Entity = $Manager->getEntityById('TblPrepareStudent', $tblPrepareStudent->getId());
                    $Protocol = clone $Entity;
                    if (null !== $Entity) {
                        $Entity->setApproved(false);
                        $Entity->setPrinted(false);

                        // Fehlzeiten zurücksetzen, bei automatischer Übernahme der Fehlzeiten
                        if ($useClassRegisterForAbsence) {
                            $Entity->setExcusedDays(null);
                            $Entity->setUnexcusedDays(null);
                        }

                        $Manager->bulkSaveEntity($Entity);
                        Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity, true);
                    }
                }
            }

            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
        }

        return true;
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     *
     * @return bool
     */
    public function isPreparePrinted(TblPrepareCertificate $tblPrepareCertificate)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPrepareStudent',
            array(
                TblPrepareStudent::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepareCertificate->getId(),
                TblPrepareStudent::ATTR_IS_PRINTED => true
            ))
            ? true : false;
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     *
     * @return false|TblPrepareStudent[]
     */
    public function getPrepareStudentAllByPrepare(TblPrepareCertificate $tblPrepareCertificate)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPrepareStudent',
            array(
                TblPrepareStudent::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepareCertificate->getId(),
            )
        );
    }

    /**
     * @param TblGradeType $tblGradeType
     *
     * @return bool
     */
    public function isGradeTypeUsed(TblGradeType $tblGradeType)
    {

        return $this->getCachedEntityBy(
            __METHOD__,
            $this->getConnection()->getEntityManager(),
            'TblPrepareGrade',
            array(
                TblPrepareGrade::ATTR_SERVICE_TBL_GRADE_TYPE => $tblGradeType->getId()
            )
        ) ? true : false;
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     * @param TblSubject $tblSubject
     * @param TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType
     * @param $ranking
     * @param $grade
     * @param bool $isSelected
     * @param bool $isLocked
     *
     * @return TblPrepareAdditionalGrade
     */
    public function createPrepareAdditionalGrade(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson,
        TblSubject $tblSubject,
        TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType,
        $ranking,
        $grade,
        $isSelected = false,
        $isLocked = false
    ) {

        $Manager = $this->getEntityManager();

        /** @var TblPrepareAdditionalGrade $Entity */
        $Entity = $Manager->getEntity('TblPrepareAdditionalGrade')->findOneBy(array(
            TblPrepareAdditionalGrade::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepareCertificate->getId(),
            TblPrepareAdditionalGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            TblPrepareAdditionalGrade::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
            TblPrepareAdditionalGrade::ATTR_TBL_PREPARE_ADDITIONAL_GRADE_TYPE => $tblPrepareAdditionalGradeType->getId()
        ));

        if ($Entity === null) {
            $Entity = new TblPrepareAdditionalGrade();
            $Entity->setTblPrepareCertificate($tblPrepareCertificate);
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setServiceTblSubject($tblSubject);
            $Entity->setTblPrepareAdditionalGradeType($tblPrepareAdditionalGradeType);
            $Entity->setRanking($ranking);
            $Entity->setGrade($grade);
            $Entity->setSelected($isSelected);
            $Entity->setLocked($isLocked);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblPrepareAdditionalGrade $tblPrepareAdditionalGrade
     * @param $grade
     * @param bool $isSelected
     *
     * @return bool
     */
    public function updatePrepareAdditionalGrade(
        TblPrepareAdditionalGrade $tblPrepareAdditionalGrade,
        $grade,
        $isSelected = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblPrepareAdditionalGrade $Entity */
        $Entity = $Manager->getEntityById('TblPrepareAdditionalGrade', $tblPrepareAdditionalGrade->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setGrade($grade);
            $Entity->setSelected($isSelected);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblPrepareAdditionalGrade $tblPrepareAdditionalGrade
     * @param TblSubject $tblSubject
     * @param $grade
     * @param bool $isSelected
     *
     * @return bool
     */
    public function updatePrepareAdditionalGradeAndSubject(
        TblPrepareAdditionalGrade $tblPrepareAdditionalGrade,
        TblSubject $tblSubject,
        $grade,
        $isSelected = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblPrepareAdditionalGrade $Entity */
        $Entity = $Manager->getEntityById('TblPrepareAdditionalGrade', $tblPrepareAdditionalGrade->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setServiceTblSubject($tblSubject);
            $Entity->setGrade($grade);
            $Entity->setSelected($isSelected);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     * @param TblPrepareAdditionalGradeType|null $tblPrepareAdditionalGradeType
     *
     * @return false|TblPrepareAdditionalGrade[]
     */
    public function getPrepareAdditionalGradeListBy(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson,
        TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType = null
    ) {

        if ($tblPrepareAdditionalGradeType) {
            $parameters =  array(
                TblPrepareAdditionalGrade::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepareCertificate->getId(),
                TblPrepareAdditionalGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblPrepareAdditionalGrade::ATTR_TBL_PREPARE_ADDITIONAL_GRADE_TYPE => $tblPrepareAdditionalGradeType->getId()
            );
        } else {
            $parameters =  array(
                TblPrepareAdditionalGrade::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepareCertificate->getId(),
                TblPrepareAdditionalGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            );
        }

        return $this->getCachedEntityListBy(
            __METHOD__,
            $this->getEntityManager(),
            'TblPrepareAdditionalGrade',
           $parameters,
            array('Ranking' => self::ORDER_ASC)
        );
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     * @param TblSubject $tblSubject
     * @param TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType
     * @param bool $isForced
     *
     * @return false|TblPrepareAdditionalGrade
     */
    public function getPrepareAdditionalGradeBy(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson,
        TblSubject $tblSubject,
        TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType,
        bool $isForced = false
    ) {

        if ($isForced) {
            return $this->getForceEntityBy(
                __METHOD__,
                $this->getEntityManager(),
                'TblPrepareAdditionalGrade',
                array(
                    TblPrepareAdditionalGrade::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepareCertificate->getId(),
                    TblPrepareAdditionalGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                    TblPrepareAdditionalGrade::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                    TblPrepareAdditionalGrade::ATTR_TBL_PREPARE_ADDITIONAL_GRADE_TYPE => $tblPrepareAdditionalGradeType->getId()
                )
            );
        } else {
            return $this->getCachedEntityBy(
                __METHOD__,
                $this->getEntityManager(),
                'TblPrepareAdditionalGrade',
                array(
                    TblPrepareAdditionalGrade::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepareCertificate->getId(),
                    TblPrepareAdditionalGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                    TblPrepareAdditionalGrade::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                    TblPrepareAdditionalGrade::ATTR_TBL_PREPARE_ADDITIONAL_GRADE_TYPE => $tblPrepareAdditionalGradeType->getId()
                )
            );
        }
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     * @param TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType
     * @param $ranking
     *
     * @return false|TblPrepareAdditionalGrade
     * @throws \Exception
     */
    public function getPrepareAdditionalGradeByRanking(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson,
        TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType,
        $ranking
    ) {

        return $this->getCachedEntityBy(
            __METHOD__,
            $this->getEntityManager(),
            'TblPrepareAdditionalGrade',
            array(
                TblPrepareAdditionalGrade::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepareCertificate->getId(),
                TblPrepareAdditionalGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblPrepareAdditionalGrade::ATTR_TBL_PREPARE_ADDITIONAL_GRADE_TYPE => $tblPrepareAdditionalGradeType->getId(),
                TblPrepareAdditionalGrade::ATTR_RANKING => $ranking,
            )
        );
    }

    /**
     * @param $Id
     * @return false|TblPrepareAdditionalGrade
     */
    public function getPrepareAdditionalGradeById($Id)
    {

        return $this->getCachedEntityById(
            __METHOD__,
            $this->getEntityManager(),
            'TblPrepareAdditionalGrade',
            $Id
        );
    }

    /**
     * @param TblPrepareAdditionalGrade $tblPrepareAdditionalGrade
     *
     * @return bool
     */
    public function destroyPrepareAdditionalGrade(TblPrepareAdditionalGrade $tblPrepareAdditionalGrade)
    {

        $Manager = $this->getEntityManager();

        /** @var TblPrepareAdditionalGrade $Entity */
        $Entity = $Manager->getEntityById('TblPrepareAdditionalGrade', $tblPrepareAdditionalGrade->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblPrepareAdditionalGrade $tblPrepareAdditionalGrade
     * @param $Ranking
     *
     * @return bool
     */
    public function updatePrepareAdditionalGradeRanking(TblPrepareAdditionalGrade $tblPrepareAdditionalGrade, $Ranking)
    {

        $Manager = $this->getEntityManager();

        /** @var TblPrepareAdditionalGrade $Entity */
        $Entity = $Manager->getEntityById('TblPrepareAdditionalGrade', $tblPrepareAdditionalGrade->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setRanking($Ranking);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param $Identifier
     *
     * @return bool|TblPrepareAdditionalGradeType
     */
    public function getPrepareAdditionalGradeTypeByIdentifier($Identifier)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPrepareAdditionalGradeType',
            array(
                TblPrepareAdditionalGradeType::ATTR_IDENTIFIER => strtoupper($Identifier)
            )
        );
    }

    /**
     * @param $Id
     *
     * @return bool|TblPrepareAdditionalGradeType
     */
    public function getPrepareAdditionalGradeTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPrepareAdditionalGradeType',
            $Id
        );
    }

    /**
     * @param $Name
     * @param $Identifier
     *
     * @return null|TblPrepareAdditionalGradeType
     */
    public function createPrepareAdditionalGradeType($Name, $Identifier)
    {

        $Manager = $this->getEntityManager();

        $Entity = $Manager->getEntity('TblPrepareAdditionalGradeType')
            ->findOneBy(array(TblPrepareAdditionalGradeType::ATTR_IDENTIFIER => $Identifier));

        if (null === $Entity) {
            $Entity = new TblPrepareAdditionalGradeType();
            $Entity->setName($Name);
            $Entity->setIdentifier(strtoupper($Identifier));

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * soft remove
     * @param TblPrepareCertificate $tblPrepareCertificate
     *
     * @return bool
     */
    public function destroyPrepareCertificate(TblPrepareCertificate $tblPrepareCertificate)
    {

        $Manager = $this->getEntityManager();

        /** @var TblPrepareCertificate $Entity */
        $Entity = $Manager->getEntityById('TblPrepareCertificate', $tblPrepareCertificate->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->removeEntity($Entity);

            return true;
        }
        return false;
    }

    /**
     * @deprecated
     *
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return false|TblLeaveStudent[]
     */
    public function  getLeaveStudentAllByDivision(TblDivisionCourse $tblDivisionCourse)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblLeaveStudent',
            array(TblLeaveStudent::ATTR_SERVICE_TBL_DIVISION => $tblDivisionCourse->getId()));
    }

    /**
     * @param TblPrepareComplexExam $tblPrepareComplexExam
     * @param $grade
     * @param TblSubject|null $tblFirstSubject
     * @param TblSubject|null $tblSecondSubject
     *
     * @return bool
     */
    public function updatePrepareComplexExam(
        TblPrepareComplexExam $tblPrepareComplexExam,
        $grade,
        TblSubject $tblFirstSubject = null,
        TblSubject $tblSecondSubject = null
    ) {
        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblPrepareComplexExam $Entity */
        $Entity = $Manager->getEntityById('TblPrepareComplexExam', $tblPrepareComplexExam->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setGrade($grade);
            $Entity->setServiceTblFirstSubject($tblFirstSubject);
            $Entity->setServiceTblSecondSubject($tblSecondSubject);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblPrepareStudent $tblPrepareStudent
     * @param $identifier
     * @param $ranking
     * @param $grade
     * @param TblSubject|null $tblFirstSubject
     * @param TblSubject|null $tblSecondSubject
     *
     * @return TblPrepareComplexExam
     */
    public function createPrepareComplexExam(
        TblPrepareStudent $tblPrepareStudent,
        $identifier,
        $ranking,
        $grade,
        TblSubject $tblFirstSubject = null,
        TblSubject $tblSecondSubject = null
    ) {

        $Manager = $this->getEntityManager();

        /** @var TblPrepareComplexExam $Entity */
        $Entity = $Manager->getEntity('TblPrepareComplexExam')->findOneBy(array(
            TblPrepareComplexExam::ATTR_TBL_PREPARE_STUDENT => $tblPrepareStudent->getId(),
            TblPrepareComplexExam::ATTR_IDENTIFIER => $identifier,
            TblPrepareComplexExam::ATTR_RANKING => $ranking
        ));

        if ($Entity === null) {
            $Entity = new TblPrepareComplexExam();
            $Entity->setTblPrepareStudent($tblPrepareStudent);
            $Entity->setIdentifier($identifier);
            $Entity->setRanking($ranking);
            $Entity->setGrade($grade);
            $Entity->setServiceTblFirstSubject($tblFirstSubject);
            $Entity->setServiceTblSecondSubject($tblSecondSubject);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblPrepareStudent $tblPrepareStudent
     * @param $identifier
     * @param $ranking
     *
     * @return false|TblPrepareComplexExam
     */
    public function getPrepareComplexExamBy(
        TblPrepareStudent $tblPrepareStudent,
        $identifier,
        $ranking
    ) {

        return $this->getCachedEntityBy(
            __METHOD__,
            $this->getEntityManager(),
            'TblPrepareComplexExam',
            array(
                TblPrepareComplexExam::ATTR_TBL_PREPARE_STUDENT => $tblPrepareStudent->getId(),
                TblPrepareComplexExam::ATTR_IDENTIFIER => $identifier,
                TblPrepareComplexExam::ATTR_RANKING => $ranking
            )
        );
    }

    /**
     * @param TblPrepareStudent $tblPrepareStudent
     *
     * @return false|TblPrepareComplexExam[]
     */
    public function getPrepareComplexExamAllByPrepareStudent(TblPrepareStudent $tblPrepareStudent)
    {
        return $this->getCachedEntityListBy(
            __METHOD__,
            $this->getEntityManager(),
            'TblPrepareComplexExam',
            array(TblPrepareComplexExam::ATTR_TBL_PREPARE_STUDENT => $tblPrepareStudent->getId()),
            array(
                TblPrepareComplexExam::ATTR_IDENTIFIER => self::ORDER_DESC,
                TblPrepareComplexExam::ATTR_RANKING => self::ORDER_ASC
            )
        );
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool|false $IsPrinted
     *
     * @return false|TblPrepareStudent[]
     */
    public function getPrepareStudentAllWherePrintedByPerson(TblPerson $tblPerson, $IsPrinted = false)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPrepareStudent',
            array(
                TblPrepareStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblPrepareStudent::ATTR_IS_PRINTED => $IsPrinted
            )
        );
    }

    /**
     * @param TblPrepareStudent $tblPrepareStudent
     * @param $IsPrepared
     *
     * @return bool
     */
    public function updatePrepareStudentSetIsPrepared(
        TblPrepareStudent $tblPrepareStudent,
        $IsPrepared
    ) : bool
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblPrepareStudent $Entity */
        $Entity = $Manager->getEntityById('TblPrepareStudent', $tblPrepareStudent->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setIsPrepared($IsPrepared);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return false|TblPrepareCertificate[]
     */
    public function getPrepareAllByDivisionCourse(TblDivisionCourse $tblDivisionCourse)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblPrepareCertificate', array(
            TblPrepareCertificate::ATTR_SERVICE_TBL_DIVISION => $tblDivisionCourse->getId()
        ));
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     *
     * @return false|TblPrepareGrade[]
     */
    public function getBehaviorGradeAllByPrepareCertificateAndPerson(TblPrepareCertificate $tblPrepareCertificate, TblPerson $tblPerson)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblPrepareGrade', array(
            TblPrepareGrade::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepareCertificate->getId(),
            TblPrepareGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            // nur Kopfnoten
            TblPrepareGrade::ATTR_SERVICE_TBL_SUBJECT => null
        ));
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     *
     * @return false|TblPrepareGrade[]
     */
    public function getBehaviorGradeAllByPrepareCertificate(TblPrepareCertificate $tblPrepareCertificate)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblPrepareGrade', array(
            TblPrepareGrade::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepareCertificate->getId(),
            // nur Kopfnoten
            TblPrepareGrade::ATTR_SERVICE_TBL_SUBJECT => null
        ));
    }

    /**
     * @param array $tblEntityList
     *
     * @return bool
     */
    public function updateEntityListBulk(array $tblEntityList): bool
    {
        $Manager = $this->getEntityManager();

        /** @var Element $tblElement */
        foreach ($tblEntityList as $tblElement) {
            $Manager->bulkSaveEntity($tblElement);
            /** @var Element $Entity */
            $Entity = $Manager->getEntityById($tblElement->getEntityShortName(), $tblElement->getId());
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Entity, $tblElement, true);
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }
}