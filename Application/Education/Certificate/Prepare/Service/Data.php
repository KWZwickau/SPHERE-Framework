<?php
namespace SPHERE\Application\Education\Certificate\Prepare\Service;

use DateTime;
use SPHERE\Application\Education\Absence\Absence;
use SPHERE\Application\Education\Certificate\Generate\Service\Entity\TblGenerateCertificate;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateType;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareAdditionalGrade;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareAdditionalGradeType;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareComplexExam;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareGrade;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareInformation;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareStudent;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTask;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
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
        $this->createPrepareAdditionalGradeType('13/1', '13-1'); // Berufliches Abitur
        $this->createPrepareAdditionalGradeType('13/2', '13-2'); // Berufliches Abitur
        $this->createPrepareAdditionalGradeType('schriftliche Prüfung', 'WRITTEN_EXAM');
        $this->createPrepareAdditionalGradeType('mündliche Prüfung', 'VERBAL_EXAM');
        $this->createPrepareAdditionalGradeType('zusätzliche mündliche Prüfung', 'EXTRA_VERBAL_EXAM');
        $this->createPrepareAdditionalGradeType('Klasse 10', 'LEVEL-10');
        $this->createPrepareAdditionalGradeType('Klasse 11', 'LEVEL-11'); // Berufliches Abitur
    }

    /**
     * @param $Id
     *
     * @return false|TblPrepareCertificate
     */
    public function getPrepareById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPrepareCertificate', $Id);
    }

    /**
     * @return false|TblPrepareCertificate[]
     */
    public function getPrepareAll()
    {
        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPrepareCertificate');
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblGradeType $tblGradeType
     *
     * @return false|TblPrepareGrade
     */
    public function getPrepareGradeByGradeType(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblGradeType $tblGradeType
    ) {
        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPrepareGrade',
            array(
                TblPrepareGrade::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepare->getId(),
                TblPrepareGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblPrepareGrade::ATTR_SERVICE_TBL_GRADE_TYPE => $tblGradeType->getId(),
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
    public function getPrepareStudentBy(TblPrepareCertificate $tblPrepare, TblPerson $tblPerson, bool $isForced = false)
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
     * @param bool $IsApproved
     * @param bool $IsPrinted
     *
     * @return false|TblPrepareStudent[]
     */
    public function getPrepareStudentAllWhere(bool $IsApproved = false, bool $IsPrinted = false)
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
        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPrepareInformation', array(
            TblPrepareInformation::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepare->getId(),
            TblPrepareInformation::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
        ));
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     *
     * @return false|TblPrepareInformation[]
     */
    public function getPrepareInformationAllByPrepare(TblPrepareCertificate $tblPrepare)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPrepareInformation', array(
            TblPrepareInformation::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepare->getId()
        ));
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
     * @param bool $IsPrepared
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
     *
     * @return bool
     */
    public function updatePrepareResetRemove(
        TblPrepareCertificate $tblPrepare
    ): bool {
        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblPrepareCertificate $Entity */
        $Entity = $Manager->getEntityById('TblPrepareCertificate', $tblPrepare->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setEntityRemove(false);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblGradeType $tblGradeType
     * @param $Grade
     *
     * @return TblPrepareGrade
     */
    public function updatePrepareGradeForBehavior(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblGradeType $tblGradeType,
        $Grade
    ): TblPrepareGrade {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblPrepareGrade $Entity */
        $Entity = $Manager->getEntity('TblPrepareGrade')->findOneBy(array(
            TblPrepareGrade::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepare->getId(),
            TblPrepareGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            TblPrepareGrade::ATTR_SERVICE_TBL_GRADE_TYPE => $tblGradeType->getId(),
        ));
        if ($Entity === null) {
            $Entity = new TblPrepareGrade();
            $Entity->setTblPrepareCertificate($tblPrepare);
            $Entity->setServiceTblPerson($tblPerson);
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
        bool $IsApproved = false,
        bool $IsPrinted = false,
        $ExcusedDays = null,
        $UnexcusedDays = null
    ): TblPrepareStudent {
        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblPrepareStudent')->findOneBy(array(
            TblPrepareStudent::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepare->getId(),
            TblPrepareStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
        ));
        if ($Entity === null) {
            $Entity = new TblPrepareStudent();
            $Entity->setTblPrepareCertificate($tblPrepare);
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setServiceTblCertificate($tblCertificate ?: null);
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
    ): TblPrepareInformation {
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
    ): bool {
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

        // Update
        /** @var TblPrepareStudent $Entity */
        $Entity = $Manager->getEntityById('TblPrepareStudent', $tblPrepareStudent->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setApproved(true);
            $Entity->setPrinted(false);

            // Fehlzeiten aus dem Klassenbuch übernehmen
            if ($useClassRegisterForAbsence) {
                if (($tblPerson = $tblPrepareStudent->getServiceTblPerson())
                    && ($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
                    && ($tblYear = $tblPrepare->getYear())
                ) {
                    if (($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())
                        && $tblGenerateCertificate->getAppointedDateForAbsence()
                    ) {
                        $tillDateAbsence = new DateTime($tblGenerateCertificate->getAppointedDateForAbsence());
                    } else {
                        $tillDateAbsence = new DateTime($tblPrepare->getDate());
                    }
                    list($startDateAbsence) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);

                    $tblCompany = false;
                    $tblSchoolType = false;
                    if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                        $tblCompany = $tblStudentEducation->getServiceTblCompany();
                        $tblSchoolType = $tblStudentEducation->getServiceTblSchoolType();
                    }

                    $Entity->setExcusedDays(Absence::useService()->getExcusedDaysByPerson($tblPerson, $tblYear, $tblCompany ?: null, $tblSchoolType ?: null,
                        $startDateAbsence, $tillDateAbsence));
                    $Entity->setUnexcusedDays(Absence::useService()->getUnexcusedDaysByPerson($tblPerson, $tblYear, $tblCompany ?: null, $tblSchoolType ?: null,
                        $startDateAbsence, $tillDateAbsence));
                }
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

        if (($tblPrepareStudentList = $this->getPrepareStudentAllByPrepare($tblPrepare))
            && ($tblYear = $tblPrepare->getYear())
        ) {
            if (($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())
                && $tblGenerateCertificate->getAppointedDateForAbsence()
            ) {
                $tillDateAbsence = new DateTime($tblGenerateCertificate->getAppointedDateForAbsence());
            } else {
                $tillDateAbsence = new DateTime($tblPrepare->getDate());
            }
            list($startDateAbsence) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);

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
                        if ($useClassRegisterForAbsence) {
                            $tblCompany = false;
                            $tblSchoolType = false;
                            if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                                $tblCompany = $tblStudentEducation->getServiceTblCompany();
                                $tblSchoolType = $tblStudentEducation->getServiceTblSchoolType();
                            }

                            $Entity->setExcusedDays(Absence::useService()->getExcusedDaysByPerson($tblPerson, $tblYear, $tblCompany ?: null, $tblSchoolType ?: null,
                                $startDateAbsence, $tillDateAbsence));
                            $Entity->setUnexcusedDays(Absence::useService()->getUnexcusedDaysByPerson($tblPerson, $tblYear, $tblCompany ?: null, $tblSchoolType ?: null,
                                $startDateAbsence, $tillDateAbsence));
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
    public function isPreparePrinted(TblPrepareCertificate $tblPrepareCertificate): bool
    {
        return (bool)$this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPrepareStudent',
            array(
                TblPrepareStudent::ATTR_TBL_PREPARE_CERTIFICATE => $tblPrepareCertificate->getId(),
                TblPrepareStudent::ATTR_IS_PRINTED => true
            ));
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
    public function isGradeTypeUsed(TblGradeType $tblGradeType): bool
    {
        return (bool)$this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPrepareGrade',
            array(
                TblPrepareGrade::ATTR_SERVICE_TBL_GRADE_TYPE => $tblGradeType->getId()
            )
        );
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
        bool $isSelected = false,
        bool $isLocked = false
    ): TblPrepareAdditionalGrade {
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
        bool $isSelected = false
    ): bool {
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
        bool $isSelected = false
    ): bool {

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

        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblPrepareAdditionalGrade', $parameters, array('Ranking' => self::ORDER_ASC));
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
     */
    public function getPrepareAdditionalGradeByRanking(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson,
        TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType,
        $ranking
    ) {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblPrepareAdditionalGrade',
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
     *
     * @return false|TblPrepareAdditionalGrade
     */
    public function getPrepareAdditionalGradeById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblPrepareAdditionalGrade', $Id);
    }

    /**
     * @param TblPrepareAdditionalGrade $tblPrepareAdditionalGrade
     *
     * @return bool
     */
    public function destroyPrepareAdditionalGrade(TblPrepareAdditionalGrade $tblPrepareAdditionalGrade): bool
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
    public function updatePrepareAdditionalGradeRanking(TblPrepareAdditionalGrade $tblPrepareAdditionalGrade, $Ranking): bool
    {
        $Manager = $this->getEntityManager();
        /** @var TblPrepareAdditionalGrade $Entity */
        $Entity = $Manager->getEntityById('TblPrepareAdditionalGrade', $tblPrepareAdditionalGrade->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setRanking($Ranking);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

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
        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPrepareAdditionalGradeType', $Id);
    }

    /**
     * @param $Name
     * @param $Identifier
     *
     * @return false|TblPrepareAdditionalGradeType
     */
    public function createPrepareAdditionalGradeType($Name, $Identifier)
    {
        $Manager = $this->getEntityManager();

        $Entity = $Manager->getEntity('TblPrepareAdditionalGradeType')->findOneBy(array(TblPrepareAdditionalGradeType::ATTR_IDENTIFIER => $Identifier));

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
    public function destroyPrepareCertificate(TblPrepareCertificate $tblPrepareCertificate): bool
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
    ): bool {
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
    ): TblPrepareComplexExam {
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
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblGenerateCertificate $tblGenerateCertificate
     *
     * @return false|TblPrepareCertificate
     */
    public function getForcedPrepareByDivisionCourseAndGenerateCertificate(TblDivisionCourse $tblDivisionCourse, TblGenerateCertificate $tblGenerateCertificate)
    {
        return $this->getForceEntityBy(__METHOD__, $this->getEntityManager(), 'TblPrepareCertificate', array(
            TblPrepareCertificate::ATTR_SERVICE_TBL_DIVISION => $tblDivisionCourse->getId(),
            TblPrepareCertificate::ATTR_SERVICE_TBL_GENERATE_CERTIFICATE => $tblGenerateCertificate->getId()
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

    /**
     * @param TblPerson $tblPerson
     * @param TblCertificateType $tblCertificateType
     *
     * @return TblPrepareStudent[]|false
     */
    public function getPrepareStudentListByPersonAndCertificateType(TblPerson $tblPerson, TblCertificateType $tblCertificateType)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('ps')
            ->from(TblPrepareStudent::class, 'ps')
            ->leftJoin(TblPrepareCertificate::class, 'pc', 'WITH', 'ps.tblPrepareCertificate = pc.Id')
            ->leftJoin(TblGenerateCertificate::class, 'gc', 'WITH', 'pc.serviceTblGenerateCertificate = gc.Id')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('ps.serviceTblPerson', '?1'),
                    $queryBuilder->expr()->eq('gc.serviceTblCertificateType', '?2')
                ),
            )
            ->setParameter(1, $tblPerson->getId())
            ->setParameter(2, $tblCertificateType->getId())
            ->orderBy('gc.Date', 'DESC')
            ->getQuery();

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblCertificateType $tblCertificateType
     * @param TblYear $tblYear
     *
     * @return TblPrepareStudent[]|false
     */
    public function getPrepareStudentListByPersonAndCertificateTypeAndYear(
        TblPerson $tblPerson, TblCertificateType $tblCertificateType, TblYear $tblYear, $sort
    ) {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('ps')
            ->from(TblPrepareStudent::class, 'ps')
            ->leftJoin(TblPrepareCertificate::class, 'pc', 'WITH', 'ps.tblPrepareCertificate = pc.Id')
            ->leftJoin(TblGenerateCertificate::class, 'gc', 'WITH', 'pc.serviceTblGenerateCertificate = gc.Id')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('ps.serviceTblPerson', '?1'),
                    $queryBuilder->expr()->eq('gc.serviceTblCertificateType', '?2'),
                    $queryBuilder->expr()->eq('gc.serviceTblYear', '?3')
                ),
            )
            ->setParameter(1, $tblPerson->getId())
            ->setParameter(2, $tblCertificateType->getId())
            ->setParameter(3, $tblYear->getId())
            ->orderBy('gc.Date', $sort)
            ->getQuery();

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param array $tblEntityList
     *
     * @return bool
     */
    public function createEntityListBulk(array $tblEntityList): bool
    {
        $Manager = $this->getConnection()->getEntityManager();

        foreach ($tblEntityList as $tblEntity) {
            $Manager->bulkSaveEntity($tblEntity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $tblEntity, true);
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblTask $tblTask
     *
     * @return bool
     */
    public function getIsAppointedDateTaskGradeApproved(TblPerson $tblPerson, TblTask $tblTask): bool
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('ps')
            ->from(TblPrepareStudent::class, 'ps')
            ->leftJoin(TblPrepareCertificate::class, 'pc', 'WITH', 'ps.tblPrepareCertificate = pc.Id')
            ->leftJoin(TblGenerateCertificate::class, 'gc', 'WITH', 'pc.serviceTblGenerateCertificate = gc.Id')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('ps.serviceTblPerson', '?1'),
                    $queryBuilder->expr()->eq('ps.IsApproved', 1),
                    $queryBuilder->expr()->eq('gc.serviceTblAppointedDateTask', '?2'),
                    // gelöschter Zeugnisauftrag
                    $queryBuilder->expr()->isNull('gc.EntityRemove')
                ),
            )
            ->setParameter(1, $tblPerson->getId())
            ->setParameter(2, $tblTask->getId())
            ->orderBy('gc.Date', 'DESC')
            ->getQuery();

        $resultList = $query->getResult();

        return !empty($resultList);
    }
}