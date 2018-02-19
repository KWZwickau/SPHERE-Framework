<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.07.2016
 * Time: 11:18
 */

namespace SPHERE\Application\Education\Certificate\Prepare\Service;

use SPHERE\Application\Education\Certificate\Generate\Generate;
use SPHERE\Application\Education\Certificate\Generate\Service\Entity\TblGenerateCertificate;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveStudent;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareAdditionalGrade;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareAdditionalGradeType;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareGrade;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareInformation;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareStudent;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
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
                TblPrepareCertificate::ATTR_IS_GRADE_INFORMATION => $IsGradeInformation
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
     * @param TblDivision $tblDivision
     * @param TblTestType $tblTestType
     * @param TblGradeType $tblGradeType
     *
     * @return false|TblPrepareGrade
     */
    public function getPrepareGradeByGradeType(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        TblTestType $tblTestType,
        TblGradeType $tblGradeType
    ) {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPrepareGrade',
            array(
                TblPrepareGrade::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepare->getId(),
                TblPrepareGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblPrepareGrade::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                TblPrepareGrade::ATTR_SERVICE_TBL_TEST_TYPE => $tblTestType->getId(),
                TblPrepareGrade::ATTR_SERVICE_TBL_GRADE_TYPE => $tblGradeType->getId(),
            )
        );
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblTestType $tblTestType
     *
     * @return false|TblPrepareGrade[]
     */
    public function getPrepareGradeAllByPerson(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblTestType $tblTestType
    ) {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPrepareGrade',
            array(
                TblPrepareGrade::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepare->getId(),
                TblPrepareGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblPrepareGrade::ATTR_SERVICE_TBL_TEST_TYPE => $tblTestType->getId(),
            )
        );
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
     * @param TblDivision $tblDivision
     * @param $Date
     * @param $Name
     * @param bool $IsGradeInformation
     * @param TblGenerateCertificate $tblGenerateCertificate
     * @param TblTask $tblAppointedDateTask
     * @param TblTask $tblBehaviorTask
     *
     * @return TblPrepareCertificate
     */
    public function createPrepare(
        TblDivision $tblDivision,
        $Date,
        $Name,
        $IsGradeInformation = false,
        TblGenerateCertificate $tblGenerateCertificate = null,
        TblTask $tblAppointedDateTask = null,
        TblTask $tblBehaviorTask = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblPrepareCertificate $Entity */
        $Entity = $Manager->getEntity('TblPrepareCertificate')->findOneBy(array(
            TblPrepareCertificate::ATTR_SERVICE_TBL_GENERATE_CERTIFICATE => $tblGenerateCertificate->getId(),
            TblPrepareCertificate::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId()
        ));

        if ($Entity === null) {
            $Entity = new TblPrepareCertificate();
            $Entity->setServiceTblGenerateCertificate($tblGenerateCertificate ? $tblGenerateCertificate : null);
            $Entity->setServiceTblDivision($tblDivision);
            $Entity->setDate($Date ? new \DateTime($Date) : null);
            $Entity->setName($Name);
            $Entity->setIsGradeInformation($IsGradeInformation);
            $Entity->setServiceTblAppointedDateTask($tblAppointedDateTask ? $tblAppointedDateTask : null);
            $Entity->setServiceTblBehaviorTask($tblBehaviorTask ? $tblBehaviorTask : null);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param $Date
     * @param $Name
     * @param TblTask|null $tblAppointedDateTask
     * @param TblTask|null $tblBehaviorTask
     * @param TblPerson|null $tblPersonSigner
     *
     * @return bool
     */
    public function updatePrepare(
        TblPrepareCertificate $tblPrepare,
        $Date,
        $Name,
        TblTask $tblAppointedDateTask = null,
        TblTask $tblBehaviorTask = null,
        TblPerson $tblPersonSigner = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblPrepareCertificate $Entity */
        $Entity = $Manager->getEntityById('TblPrepareCertificate', $tblPrepare->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setDate($Date ? new \DateTime($Date) : null);
            $Entity->setName($Name);
            $Entity->setServiceTblAppointedDateTask($tblAppointedDateTask);
            $Entity->setServiceTblBehaviorTask($tblBehaviorTask);
            $Entity->setServiceTblPersonSigner($tblPersonSigner);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param TblTestType $tblTestType
     * @param TblGradeType $tblGradeType
     * @param $Grade
     *
     * @return TblPrepareGrade
     */
    public function updatePrepareGradeForBehavior(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        TblTestType $tblTestType,
        TblGradeType $tblGradeType,
        $Grade
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblPrepareGrade $Entity */
        $Entity = $Manager->getEntity('TblPrepareGrade')->findOneBy(array(
            TblPrepareGrade::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepare->getId(),
            TblPrepareGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            TblPrepareGrade::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
            TblPrepareGrade::ATTR_SERVICE_TBL_TEST_TYPE => $tblTestType->getId(),
            TblPrepareGrade::ATTR_SERVICE_TBL_GRADE_TYPE => $tblGradeType->getId(),
        ));
        if ($Entity === null) {
            $Entity = new TblPrepareGrade();
            $Entity->settblPrepareCertificate($tblPrepare);
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
     * @param null $UnexcusedDays
     * @param TblPerson|null $tblPersonSigner
     *
     * @return bool
     */
    public function updatePrepareStudent(
        TblPrepareStudent $tblPrepareStudent,
        TblCertificate $tblCertificate = null,
        $IsApproved = false,
        $IsPrinted = false,
        $ExcusedDays = null,
        $UnexcusedDays = null,
        TblPerson $tblPersonSigner = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblPrepareStudent $Entity */
        $Entity = $Manager->getEntityById('TblPrepareStudent', $tblPrepareStudent->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setServiceTblCertificate($tblCertificate);
            $Entity->setApproved($IsApproved);
            $Entity->setPrinted($IsPrinted);
            $Entity->setExcusedDays($ExcusedDays);
            $Entity->setUnexcusedDays($UnexcusedDays);
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
    public function createPrepareStudentSetBulkTemplates(
        $Data
    ) {

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
     * @param TblPrepareCertificate $tblPrepare
     * @param bool $isDiploma
     *
     * @return bool
     */
    public function copySubjectGradesByPrepare(TblPrepareCertificate $tblPrepare, $isDiploma = false)
    {

        $Manager = $this->getConnection()->getEntityManager();

        // nicht freigebene Fachnoten löschen
        if (($tblTestType = Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK'))) {
            $tblPrepareGradeList = $this->getPrepareGradeAllByPrepare(
                $tblPrepare, $tblTestType
            );
            if ($tblPrepareGradeList) {
                $isApprovedArray = array();
                foreach ($tblPrepareGradeList as $tblPrepareGrade) {
                    if (($tblPerson = $tblPrepareGrade->getServiceTblPerson())) {
                        if (!isset($isApprovedArray[$tblPerson->getId()])) {
                            if (($tblPersonStudent = $this->getPrepareStudentBy($tblPrepare, $tblPerson))) {
                                $isApprovedArray[$tblPerson->getId()] = $tblPersonStudent->isApproved();
                            } else {
                                $isApprovedArray[$tblPerson->getId()] = false;
                            }
                        }
                    }

                    // Freigegebene nicht löschen
                    if (!$isApprovedArray[$tblPerson->getId()]) {
                        // nur Freigabe protokollieren
                        $Manager->bulkKillEntity($tblPrepareGrade);
                    }
                }
            }
        }

        $tblConsumer = Consumer::useService()->getConsumerBySession();

        if (($tblTask = ($tblPrepare->getServiceTblAppointedDateTask()))
            && ($tblDivision = $tblPrepare->getServiceTblDivision())
            && ($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))
        ) {

            // Klassen ermitteln in denen die Schüler Unterricht haben
            $divisionList = array();
            $divisionPersonList = array();
            foreach ($tblPersonList as $tblPerson) {
                // bereits Freigebene überspringen
                if (($tblPrepareStudent = $this->getPrepareStudentBy($tblPrepare, $tblPerson))
                    && $tblPrepareStudent->isApproved()
                ) {
                    continue;
                }

                if (($tblPersonDivisionList = Student::useService()->getCurrentDivisionListByPerson($tblPerson))) {
                    foreach ($tblPersonDivisionList as $tblDivisionItem) {
                        if (!isset($divisionList[$tblDivisionItem->getId()])) {
                            $divisionList[$tblDivisionItem->getId()] = $tblDivisionItem;
                        }
                    }
                }

                $divisionPersonList[$tblPerson->getId()] = 1;

                // Freigabe setzen
                if ($tblPrepareStudent) {
                    // Update
                    /** @var TblPrepareStudent $Entity */
                    $Entity = $Manager->getEntityById('TblPrepareStudent', $tblPrepareStudent->getId());
                    $Protocol = clone $Entity;
                    if (null !== $Entity) {
                        $Entity->setApproved(true);
                        $Entity->setPrinted(false);

                        $Manager->bulkSaveEntity($Entity);
                        Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol,
                            $Entity, true);
                    }
                } else {
                    // Create
                    $Entity = $Manager->getEntity('TblPrepareStudent')->findOneBy(array(
                        TblPrepareStudent::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepare->getId(),
                        TblPrepareStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                    ));
                    if ($Entity === null) {
                        $Entity = new TblPrepareStudent();
                        $Entity->setTblPrepareCertificate($tblPrepare);
                        $Entity->setServiceTblPerson($tblPerson);
                        $Entity->setApproved(true);
                        $Entity->setPrinted(false);

                        // noch nicht gesetze Zeugnisvorlagen setzen
                        if ($tblConsumer && !$Entity->getServiceTblCertificate()) {
                            // Eigene Vorlage
                            if (($certificateList = Generate::useService()->getPossibleCertificates($tblPrepare,
                                $tblPerson, $tblConsumer))
                            ) {
                                if (count($certificateList) == 1) {
                                    $Entity->setServiceTblCertificate(current($certificateList));
                                } elseif (count($certificateList) > 1) {
                                    /** @var TblCertificate $certificate */
                                    $ChosenCertificate = false;
                                    foreach ($certificateList as $certificate) {
                                        if ($certificate->isChosenDefault()) {
                                            $ChosenCertificate = $certificate;
                                            break;
                                        }
                                    }
                                    if ($ChosenCertificate) {
                                        $Entity->setServiceTblCertificate($ChosenCertificate);
                                    }
                                }
                                // Standard Vorlagen
                            } elseif (($certificateList = Generate::useService()->getPossibleCertificates($tblPrepare,
                                $tblPerson))
                            ) {
                                if (count($certificateList) == 1) {
                                    if (count($certificateList) == 1) {
                                        $Entity->setServiceTblCertificate(current($certificateList));
                                    }
                                }
                            }
                        }

                        $Manager->bulkSaveEntity($Entity);
                        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity, true);
                    }
                }
            }

            // kopieren der nicht freigegeben Fachnoten
            foreach ($divisionList as $tblDivisionItem) {
                // Abschlusszeugnisse
                if ($isDiploma) {
                    if ((($tblDivisionPersonList = Division::useService()->getStudentAllByDivision($tblDivisionItem)))
                        && ($tblPrepareAdditionalGradeType = $this->getPrepareAdditionalGradeTypeByIdentifier('EN'))
                    ) {
                        foreach ($tblDivisionPersonList as $tblPersonItem) {
                            if (($tblPrepareAdditionalGradeList = $this->getPrepareAdditionalGradeListBy(
                                $tblPrepare, $tblPersonItem, $tblPrepareAdditionalGradeType
                            ))
                            ) {
                                foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                                    if (($tblSubject = $tblPrepareAdditionalGrade->getServiceTblSubject())) {
                                        $Entity = new TblPrepareGrade();
                                        $Entity->settblPrepareCertificate($tblPrepare);
                                        $Entity->setServiceTblDivision($tblDivision);
                                        $Entity->setServiceTblSubject($tblSubject);
                                        $Entity->setServiceTblPerson($tblPersonItem);
                                        $Entity->setServiceTblTestType($tblTestType);
                                        $Entity->setGrade($tblPrepareAdditionalGrade->getGrade());

                                        $Manager->bulkSaveEntity($Entity);
                                        // nur Freigabe protokollieren
                                    }
                                }
                            }
                        }
                    }
                } else {
                    if (($tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblTask, $tblDivisionItem))) {
                        foreach ($tblTestAllByTask as $tblTest) {
                            if (($tblSubject = $tblTest->getServiceTblSubject())
                                && ($tblDivisionPersonList = Division::useService()->getStudentAllByDivision($tblDivisionItem))
                            ) {
                                foreach ($tblDivisionPersonList as $tblPersonItem) {
                                    if (isset($divisionPersonList[$tblPersonItem->getId()])
                                        && ($tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
                                            $tblPersonItem))
                                    ) {
                                        $Entity = new TblPrepareGrade();
                                        $Entity->settblPrepareCertificate($tblPrepare);
                                        $Entity->setServiceTblDivision($tblGrade->getServiceTblDivision() ? $tblGrade->getServiceTblDivision() : null);
                                        $Entity->setServiceTblSubject($tblGrade->getServiceTblSubject() ? $tblGrade->getServiceTblSubject() : null);
                                        $Entity->setServiceTblPerson($tblGrade->getServiceTblPerson() ? $tblGrade->getServiceTblPerson() : null);
                                        $Entity->setServiceTblTestType($tblTestType);
                                        // keine Tendenzen auf Zeugnissen
                                        $withTrend = true;
                                        if ($tblGrade->getServiceTblPerson()
                                            && ($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare,
                                                $tblGrade->getServiceTblPerson()))
                                            && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                                            && !$tblCertificate->isInformation()
                                        ) {
                                            $withTrend = false;
                                        }
                                        $Entity->setGrade($tblGrade->getDisplayGrade($withTrend));

                                        $Manager->bulkSaveEntity($Entity);
                                        // nur Freigabe protokollieren
                                    }
                                }
                            }
                        }
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
    public function updatePrepareStudentDivisionResetApproved(TblPrepareCertificate $tblPrepare)
    {

        if (($tblDivision = $tblPrepare->getServiceTblDivision())
            && ($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))
        ) {

            $Manager = $this->getConnection()->getEntityManager();

            foreach ($tblPersonList as $tblPerson) {
                if (($tblPrepareStudent = $this->getPrepareStudentBy($tblPrepare, $tblPerson))) {
                    // Update
                    /** @var TblPrepareStudent $Entity */
                    $Entity = $Manager->getEntityById('TblPrepareStudent', $tblPrepareStudent->getId());
                    $Protocol = clone $Entity;
                    if (null !== $Entity) {
                        $Entity->setApproved(false);
                        $Entity->setPrinted(false);

                        $Manager->bulkSaveEntity($Entity);
                        Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol,
                            $Entity, true);
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
     * @param TblPerson $tblPerson
     * @param bool $isDiploma
     *
     * @return bool
     */
    public function copySubjectGradesByPerson(TblPrepareCertificate $tblPrepare, TblPerson $tblPerson, $isDiploma = false)
    {

        $Manager = $this->getConnection()->getEntityManager();

        // Fachnoten löschen
        if (($tblTestType = Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK'))) {
            $tblPrepareGradeList = $this->getPrepareGradeAllByPerson(
                $tblPrepare, $tblPerson, $tblTestType
            );
            if ($tblPrepareGradeList) {
                foreach ($tblPrepareGradeList as $tblPrepareGrade) {
                    // nur Freigabe protokollieren
                    $Manager->bulkKillEntity($tblPrepareGrade);
                }
            }
        }

        $tblConsumer = Consumer::useService()->getConsumerBySession();

        if ($tblTestType
            && ($tblTask = ($tblPrepare->getServiceTblAppointedDateTask()))
            && ($tblDivision = $tblPrepare->getServiceTblDivision())
            && ($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))
            && ($tblYear = $tblDivision->getServiceTblYear())
        ) {

            // Klassen ermitteln in denen der Schüler Unterricht hat
            $divisionList = array();
            if (($tblPersonDivisionList = Student::useService()->getDivisionListByPersonAndYear($tblPerson, $tblYear))) {
                foreach ($tblPersonDivisionList as $tblDivisionItem) {
                    if (!isset($divisionList[$tblDivisionItem->getId()])) {
                        $divisionList[$tblDivisionItem->getId()] = $tblDivisionItem;
                    }
                }
            }

            // Freigabe setzen
            if (($tblPrepareStudent = $this->getPrepareStudentBy($tblPrepare, $tblPerson))) {
                // Update
                /** @var TblPrepareStudent $Entity */
                $Entity = $Manager->getEntityById('TblPrepareStudent', $tblPrepareStudent->getId());
                $Protocol = clone $Entity;
                if (null !== $Entity) {
                    $Entity->setApproved(true);
                    $Entity->setPrinted(false);

                    $Manager->bulkSaveEntity($Entity);
                    Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol,
                        $Entity, true);
                }
            } else {
                // Create
                $Entity = $Manager->getEntity('TblPrepareStudent')->findOneBy(array(
                    TblPrepareStudent::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepare->getId(),
                    TblPrepareStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                ));
                if ($Entity === null) {
                    $Entity = new TblPrepareStudent();
                    $Entity->setTblPrepareCertificate($tblPrepare);
                    $Entity->setServiceTblPerson($tblPerson);
                    $Entity->setApproved(true);
                    $Entity->setPrinted(false);

                    // noch nicht gesetze Zeugnisvorlagen setzen
                    if ($tblConsumer && !$Entity->getServiceTblCertificate()) {
                        // Eigene Vorlage
                        if (($certificateList = Generate::useService()->getPossibleCertificates($tblPrepare,
                            $tblPerson, $tblConsumer))
                        ) {
                            if (count($certificateList) == 1) {
                                $Entity->setServiceTblCertificate(current($certificateList));
                            } elseif (count($certificateList) > 1) {
                                /** @var TblCertificate $certificate */
                                $ChosenCertificate = false;
                                foreach ($certificateList as $certificate) {
                                    if ($certificate->isChosenDefault()) {
                                        $ChosenCertificate = $certificate;
                                        break;
                                    }
                                }
                                if ($ChosenCertificate) {
                                    $Entity->setServiceTblCertificate($ChosenCertificate);
                                }
                            }
                            // Standard Vorlagen
                        } elseif (($certificateList = Generate::useService()->getPossibleCertificates($tblPrepare,
                            $tblPerson))
                        ) {
                            if (count($certificateList) == 1) {
                                if (count($certificateList) == 1) {
                                    $Entity->setServiceTblCertificate(current($certificateList));
                                }
                            }
                        }
                    }

                    $Manager->bulkSaveEntity($Entity);
                    Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity, true);
                }
            }

            // kopieren der  Fachnoten
            foreach ($divisionList as $tblDivisionItem) {
                // Abschlusszeugnisse
                if ($isDiploma) {
                    if (($tblPrepareAdditionalGradeType = $this->getPrepareAdditionalGradeTypeByIdentifier('EN'))
                        && ($tblPrepareAdditionalGradeList = $this->getPrepareAdditionalGradeListBy(
                            $tblPrepare, $tblPerson, $tblPrepareAdditionalGradeType
                        ))) {
                        foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                            if (($tblSubject = $tblPrepareAdditionalGrade->getServiceTblSubject())) {
                                $Entity = new TblPrepareGrade();
                                $Entity->settblPrepareCertificate($tblPrepare);
                                $Entity->setServiceTblDivision($tblDivision);
                                $Entity->setServiceTblSubject($tblSubject);
                                $Entity->setServiceTblPerson($tblPerson);
                                $Entity->setServiceTblTestType($tblTestType);
                                $Entity->setGrade($tblPrepareAdditionalGrade->getGrade());

                                $Manager->bulkSaveEntity($Entity);
                                // nur Freigabe protokollieren
                            }
                        }
                    }
                } else {
                    if (($tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblTask, $tblDivisionItem))) {
                        foreach ($tblTestAllByTask as $tblTest) {
                            if (($tblSubject = $tblTest->getServiceTblSubject())) {
                                if (($tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
                                    $tblPerson))
                                ) {
                                    $Entity = new TblPrepareGrade();
                                    $Entity->settblPrepareCertificate($tblPrepare);
                                    $Entity->setServiceTblDivision($tblGrade->getServiceTblDivision() ? $tblGrade->getServiceTblDivision() : null);
                                    $Entity->setServiceTblSubject($tblGrade->getServiceTblSubject() ? $tblGrade->getServiceTblSubject() : null);
                                    $Entity->setServiceTblPerson($tblGrade->getServiceTblPerson() ? $tblGrade->getServiceTblPerson() : null);
                                    $Entity->setServiceTblTestType($tblTestType);

                                    // keine Tendenzen auf Zeugnissen
                                    $withTrend = true;
                                    if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                                        && !$tblCertificate->isInformation()
                                    ) {
                                        $withTrend = false;
                                    }
                                    $Entity->setGrade($tblGrade->getDisplayGrade($withTrend));

                                    $Manager->bulkSaveEntity($Entity);
                                    // nur Freigabe protokollieren
                                }
                            }
                        }
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
     *
     * @return TblPrepareAdditionalGrade
     */
    public function createPrepareAdditionalGrade(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson,
        TblSubject $tblSubject,
        TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType,
        $ranking,
        $grade
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

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblPrepareAdditionalGrade $tblPrepareAdditionalGrade
     * @param $grade
     *
     * @return bool
     */
    public function updatePrepareAdditionalGrade(
        TblPrepareAdditionalGrade $tblPrepareAdditionalGrade,
        $grade
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblPrepareAdditionalGrade $Entity */
        $Entity = $Manager->getEntityById('TblPrepareAdditionalGrade', $tblPrepareAdditionalGrade->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setGrade($grade);

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
     *
     * @return false|TblPrepareAdditionalGrade
     */
    public function getPrepareAdditionalGradeBy(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson,
        TblSubject $tblSubject,
        TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType
    ) {

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
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     *
     * @return false|TblLeaveStudent
     */
    public  function getLeaveStudentBy(TblPerson $tblPerson, TblDivision $tblDivision)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblLeaveStudent',
            array(
                TblLeaveStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblLeaveStudent::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId()
            )
        );
    }
}