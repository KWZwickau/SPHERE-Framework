<?php
namespace SPHERE\Application\People\Meta\Student\Service;

use DateTime;
use SPHERE\Application\Education\School\Course\Service\Entity\TblSchoolDiploma;
use SPHERE\Application\Education\School\Course\Service\Entity\TblTechnicalCourse;
use SPHERE\Application\Education\School\Course\Service\Entity\TblTechnicalDiploma;
use SPHERE\Application\Education\School\Course\Service\Entity\TblTechnicalSubjectArea;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Meta\Student\Service\Data\Support;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentBaptism;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentBilling;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentInsuranceState;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentLocker;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentMasernInfo;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentMedicalRecord;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSpecialNeeds;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSpecialNeedsLevel;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubjectType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTechnicalSchool;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTenseOfLesson;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTrainingStatus;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransport;
use SPHERE\Application\People\Relationship\Service\Entity\TblSiblingRank;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Data
 *
 * @package SPHERE\Application\People\Meta\Student\Service
 */
class Data extends Support
{

    public function setupDatabaseContent()
    {

        $this->createStudentInsuranceState('Pflicht');
        $this->createStudentInsuranceState('Freiwillig');
        $this->createStudentInsuranceState('Privat');
        $this->createStudentInsuranceState('Familie Vater');
        $this->createStudentInsuranceState('Familie Mutter');

        // Werte nur bei der Initialisierung verwenden
        if(!$this->getStudentAgreementCategoryAll()){
            $tblStudentAgreementCategory = $this->createStudentAgreementCategory(
                'Foto des Schülers',
                'Sowohl Einzelaufnahmen als auch in Gruppen (z.B. zufällig)'
            );
            $this->createStudentAgreementType($tblStudentAgreementCategory, 'in Schulschriften');
            $this->createStudentAgreementType($tblStudentAgreementCategory, 'in Veröffentlichungen');
            $this->createStudentAgreementType($tblStudentAgreementCategory, 'auf Internetpräsenz');
            $this->createStudentAgreementType($tblStudentAgreementCategory, 'auf Facebookseite');
            $this->createStudentAgreementType($tblStudentAgreementCategory, 'für Druckpresse');
            $this->createStudentAgreementType($tblStudentAgreementCategory, 'durch Ton/Video/Film');
            $this->createStudentAgreementType($tblStudentAgreementCategory, 'für Werbung in eigener Sache');

            $tblStudentAgreementCategory = $this->createStudentAgreementCategory(
                'Namentliche Erwähnung des Schülers',
                ''
            );
            $this->createStudentAgreementType($tblStudentAgreementCategory, 'in Schulschriften');
            $this->createStudentAgreementType($tblStudentAgreementCategory, 'in Veröffentlichungen');
            $this->createStudentAgreementType($tblStudentAgreementCategory, 'auf Internetpräsenz');
            $this->createStudentAgreementType($tblStudentAgreementCategory, 'auf Facebookseite');
            $this->createStudentAgreementType($tblStudentAgreementCategory, 'für Druckpresse');
            $this->createStudentAgreementType($tblStudentAgreementCategory, 'durch Ton/Video/Film');
            $this->createStudentAgreementType($tblStudentAgreementCategory, 'für Werbung in eigener Sache');
        }

        $Entity = $this->getStudentSubjectTypeByIdentifier('ORIENTATION');
        if ($Entity) {
            // ist mit Absicht das der Neigungskurs nur einmal in Wahlbereich umbenannt wird und danach per DB pro Mandant
            // ein individueller Name vergeben werden kann
            if ($Entity->getName() == 'Neigungskurs') {
                $this->updateStudentSubjectType($Entity, 'Wahlbereich');
            }
        } else {
            $this->createStudentSubjectType('ORIENTATION', 'Wahlbereich');
        }
        $Entity = $this->getStudentSubjectTypeByIdentifier('ADVANCED');
        if ($Entity) {
            if ($Entity->getName() !== 'Vertiefungskurs') {
                $this->updateStudentSubjectType($Entity, 'Vertiefungskurs');
            }
        } else {
            $this->createStudentSubjectType('ADVANCED', 'Vertiefungskurs');
        }
        $Entity = $this->getStudentSubjectTypeByIdentifier('PROFILE');
        if ($Entity) {
            if ($Entity->getName() !== 'Profil') {
                $this->updateStudentSubjectType($Entity, 'Profil');
            }
        } else {
            $this->createStudentSubjectType('PROFILE', 'Profil');
        }
        $this->createStudentSubjectType('RELIGION', 'Religion');

        $Entity = $this->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE');
        if ($Entity) {
            if ($Entity->getName() !== 'Fremdsprache') {
                $this->updateStudentSubjectType($Entity, 'Fremdsprache');
            }
        } else {
            $this->createStudentSubjectType('FOREIGN_LANGUAGE', 'Fremdsprache');
        }
        $Entity = $this->getStudentSubjectTypeByIdentifier('ELECTIVE');
        if ($Entity) {
            if ($Entity->getName() !== 'Wahlfach') {
                $this->updateStudentSubjectType($Entity, 'Wahlfach');
            }
        } else {
            $this->createStudentSubjectType('ELECTIVE', 'Wahlfach');
        }
        $Entity = $this->getStudentSubjectTypeByIdentifier('TEAM');
        if ($Entity) {
            if ($Entity->getName() !== 'Arbeitsgemeinschaft') {
                $this->updateStudentSubjectType($Entity, 'Arbeitsgemeinschaft');
            }
        } else {
            $this->createStudentSubjectType('TEAM', 'Arbeitsgemeinschaft');
        }
        $Entity = $this->getStudentSubjectTypeByIdentifier('TRACK_INTENSIVE');
        if ($Entity) {
            if ($Entity->getName() !== 'Leistungskurs') {
                $this->updateStudentSubjectType($Entity, 'Leistungskurs');
            }
        } else {
            $this->createStudentSubjectType('TRACK_INTENSIVE', 'Leistungskurs');
        }
        $Entity = $this->getStudentSubjectTypeByIdentifier('TRACK_BASIC');
        if ($Entity) {
            if ($Entity->getName() !== 'Grundkurs') {
                $this->updateStudentSubjectType($Entity, 'Grundkurs');
            }
        } else {
            $this->createStudentSubjectType('TRACK_BASIC', 'Grundkurs');
        }

        $this->createStudentSubjectType('TRACK_INTENSIVE', 'Track Intensive');
        $this->createStudentSubjectType('TRACK_BASIC', 'Track Basic');

        $this->createStudentSubjectRanking('1', '1.');
        $this->createStudentSubjectRanking('2', '2.');
        $this->createStudentSubjectRanking('3', '3.');
        $this->createStudentSubjectRanking('4', '4.');
        $this->createStudentSubjectRanking('5', '5.');
        $this->createStudentSubjectRanking('6', '6.');
        $this->createStudentSubjectRanking('7', '7.');
        $this->createStudentSubjectRanking('8', '8.');
        $this->createStudentSubjectRanking('9', '9.');

//        // old Table (deprecated)
//        $this->createStudentFocusType('Sprache');
//        $this->createStudentFocusType('Körperlich-motorische Entwicklung');
//        $this->createStudentFocusType('Sozial-emotionale Entwicklung');
//        $this->createStudentFocusType('Hören');
//        $this->createStudentFocusType('Sehen');
//        $this->createStudentFocusType('Geistige Entwicklung');
//        $this->createStudentFocusType('Lernen');

        // new Table
        $this->createSupportFocusType('Sprache');
        $this->createSupportFocusType('Körperlich-motorische Entwicklung');
        if(($tblSupportFocusType = $this->getSupportFocusTypeByName('Sozial-emotionale Entwicklung'))){
            $this->updateSupportFocusType($tblSupportFocusType, 'Emotionale-soziale Entwicklung', '');
        } else {
            $this->createSupportFocusType('Emotionale-soziale Entwicklung');
        }
        $this->createSupportFocusType('Hören');
        $this->createSupportFocusType('Sehen');
        $this->createSupportFocusType('Geistige Entwicklung');
        $this->createSupportFocusType('Lernen');
        if(Consumer::useService()->getConsumerBySessionIsConsumerType(TblConsumer::TYPE_SACHSEN)){
            $this->createSupportFocusType('Unterricht kranker Schüler');
        }
        if(Consumer::useService()->getConsumerBySessionIsConsumerType(TblConsumer::TYPE_BERLIN)){
            $this->createSupportFocusType('Autismus');
        }

//        // old Table (deprecated)
//        $this->createStudentDisorderType('LRS');
//        $this->createStudentDisorderType('Gehörschwierigkeiten');
//        $this->createStudentDisorderType('Augenleiden');
//        $this->createStudentDisorderType('Sprachfehler');
//        $this->createStudentDisorderType('Dyskalkulie');
//        $this->createStudentDisorderType('Autismus');
//        $this->createStudentDisorderType('ADS / ADHS');
//        $this->createStudentDisorderType('Rechenschwäche');
//        $this->createStudentDisorderType('Hochbegabung');
//        $this->createStudentDisorderType('Konzentrationsstörung');
//        $this->createStudentDisorderType('Körperliche Beeinträchtigung');

        // new Table Disorder with new Translation
        $this->createSpecialDisorderType('ADS / ADHS');
        $this->createSpecialDisorderType('Auditive Wahrnehmungsstörungen');
        $this->createSpecialDisorderType('Konzentrationsstörungen');
        $this->createSpecialDisorderType('Störung motorischer Funktionen');
        $this->createSpecialDisorderType('Lese-/ Rechtschreibstörung');
        $this->createSpecialDisorderType('Rechenschwäche');
        $this->createSpecialDisorderType('Sonstige Entwicklungsbesonderheiten');
        $this->createSpecialDisorderType('Sprach-/ Sprechstörungen');
        $this->createSpecialDisorderType('Störungen aus dem Autismusspektrum');
        $this->createSpecialDisorderType('Visuelle Wahrnehmungsstörungen');

        $this->createStudentTransferType('ENROLLMENT', 'Einschulung');
        $this->createStudentTransferType('ARRIVE', 'Aufnahme');
        $this->createStudentTransferType('LEAVE', 'Abgabe');
        $this->createStudentTransferType('PROCESS', 'Process');

        if (($tblStudentLiberationCategory = $this->getStudentLiberationCategoryByName('Sportbefreihung'))){
            $this->updateStudentLiberationCategory($tblStudentLiberationCategory, 'Sportbefreiung');
        } else {
            $tblStudentLiberationCategory = $this->createStudentLiberationCategory('Sportbefreiung');
        }

        if ($tblStudentLiberationCategory) {
            $this->createStudentLiberationType($tblStudentLiberationCategory, 'Nicht befreit');
            $this->createStudentLiberationType($tblStudentLiberationCategory, 'Teilbefreit');
            $this->createStudentLiberationType($tblStudentLiberationCategory, 'Vollbefreit');
        }

        $this->createStudentSchoolEnrollmentType('PREMATURE', 'vorzeitige Einschulung');
        $this->createStudentSchoolEnrollmentType('REGULAR', 'fristgemäße Einschulung');
        $this->createStudentSchoolEnrollmentType('POSTPONED', 'Einschulung nach Zurückstellung');

        // TblSupportType
        $this->createSupportType('Beratung', '');
        $this->createSupportType('Förderantrag', '');
        $this->createSupportType('Förderbescheid', '');
        if(($tblSupportType = $this->getSupportTypeByName('Änderung'))){
            $this->updateSupportType($tblSupportType, 'Aufhebung', '');
        } else {
            $this->createSupportType('Aufhebung', '');
        }

        $this->createSupportType('Ablehnung', '');
        if(($tblSupportType = $this->getSupportTypeByName('Wiederspruch'))){
            $this->updateSupportType($tblSupportType, 'Widerspruch', '');
        } else {
            $this->createSupportType('Widerspruch', '');
        }
        $this->createSupportType('Zustimmung', '');

        // Masern
        $this->createStudentMasernInfo(TblStudentMasernInfo::DOCUMENT_IDENTIFICATION, TblStudentMasernInfo::TYPE_DOCUMENT, 'Impfausweis'
            , 'Impfdokumentation (Impfausweis)');
        $this->createStudentMasernInfo(TblStudentMasernInfo::DOCUMENT_VACCINATION_PROTECTION, TblStudentMasernInfo::TYPE_DOCUMENT, 'Ausreichender Impfschutz'
            , 'ärztliches Zeugnis über das Bestehen eines ausreichenden Impfschutzes');
        $this->createStudentMasernInfo(TblStudentMasernInfo::DOCUMENT_IMMUNITY, TblStudentMasernInfo::TYPE_DOCUMENT, 'Immunität gegen Masern'
            , 'ärztliches Zeugnis über die Immunität gegen Masern');
        $this->createStudentMasernInfo(TblStudentMasernInfo::DOCUMENT_CANT_VACCINATION, TblStudentMasernInfo::TYPE_DOCUMENT, 'keine Schutzimpfung möglich'
            , 'ärztliches Zeugnis, dass das Kind nicht an einer Schutzimpfung (Kontraindikation) oder anderen Maßnahmen zur spezifischen Prophylaxe teilnehmen kann');
        $this->createStudentMasernInfo(TblStudentMasernInfo::DOCUMENT_NOT_NECESSARY, TblStudentMasernInfo::TYPE_DOCUMENT, 'Kein Nachweis notwendig'
            , 'Kein Nachweis notwendig (vor 1971 geboren)');

        $this->createStudentMasernInfo(TblStudentMasernInfo::CREATOR_STATE, TblStudentMasernInfo::TYPE_CREATOR, 'Staatlich'
            , 'staatliche Stelle');
        $this->createStudentMasernInfo(TblStudentMasernInfo::CREATOR_COMMUNITY, TblStudentMasernInfo::TYPE_CREATOR, 'Gemeinschaftseinrichtung'
            , 'Leitung der bisher besuchten Gemeinschaftseinrichtung');

        // Förderschule
        $this->createStudentSpecialNeedsLevel('Unterstufe', 'LOWER');
        $this->createStudentSpecialNeedsLevel('Mittelstufe', 'MIDDLE');
        $this->createStudentSpecialNeedsLevel('Oberstufe', 'UPPER');
        $this->createStudentSpecialNeedsLevel('Werkstufe', 'WORK');

        // Berufsbildende Schulen
        $this->createStudentTenseOfLesson(TblStudentTenseOfLesson::FULL_TIME, 'Vollzeitunterricht');
        $this->createStudentTenseOfLesson(TblStudentTenseOfLesson::PART_TIME, 'Teilzeitunterricht');
        $this->createStudentTrainingStatus(TblStudentTrainingStatus::STUDENT, 'Auszubildende/Schüler');
        $this->createStudentTrainingStatus(TblStudentTrainingStatus::CHANGE_STUDENT, 'Umschüler');
    }

    /**
     * @return array
     */
    public function migrateStudentSubjectLevels(): array
    {
        $count = 0;
        $start = hrtime(true);

        $Manager = $this->getEntityManager();

        $tblStudentSubjectType = $this->getStudentSubjectTypeByIdentifier(TblStudentSubjectType::TYPE_FOREIGN_LANGUAGE);
//        if (($tblStudentSubjectList = $this->getCachedEntityListBy(__METHOD__, $Manager, 'TblStudentSubject', array(
//            TblStudentSubject::ATTR_TBL_STUDENT_SUBJECT_TYPE => $tblStudentSubjectType->getId()
//        )))) {
//            /** @var TblStudentSubject $tblStudentSubject */
//            foreach ($tblStudentSubjectList as $tblStudentSubject) {
//                if (($tblLevelFrom = $tblStudentSubject->getServiceTblLevelFrom())) {
//                    $count++;
//                    $tblStudentSubject->setLevelFrom(intval($tblLevelFrom->getName()));
//                }
//                if (($tblLevelTill = $tblStudentSubject->getServiceTblLevelTill())) {
//                    $count++;
//                    $tblStudentSubject->setLevelTill(intval($tblLevelTill->getName()));
//                }
//
//                $Manager->bulkSaveEntity($tblStudentSubject);
//            }
//        }

        $Manager->flushCache();

        $end = hrtime(true);

        return array($count, round(($end - $start) / 1000000000, 2));
    }

    /**
     * @param string $Name
     * @param string $Description
     *
     * @return TblStudentInsuranceState
     */
    public function createStudentInsuranceState($Name = '', $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblStudentInsuranceState')->findOneBy(array(
            TblStudentInsuranceState::ATTR_NAME => $Name
        ));
        if (null === $Entity) {
            $Entity = new TblStudentInsuranceState();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param string $Meta
     * @param string $Type
     * @param string $TextShort
     * @param string $TextLont
     *
     * @return TblStudentMasernInfo
     */
    public function createStudentMasernInfo($Meta = '', $Type = '', $TextShort = '', $TextLont = '')
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblStudentMasernInfo')->findOneBy(array(
            TblStudentMasernInfo::ATTR_META => $Meta
        ));

        if($Entity === null){
            $Entity = new TblStudentMasernInfo();
            $Entity->setMeta($Meta);
            $Entity->setType($Type);
            $Entity->setTextShort($TextShort);
            $Entity->setTextLong($TextLont);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param string $Disease
     * @param string $Medication
     * @param string $AttendingDoctor
     * @param int $InsuranceState
     * @param string $Insurance
     * @param string $InsuranceNumber
     * @param DateTime|null $MasernDate
     * @param TblStudentMasernInfo|null $MasernDocumentType
     * @param TblStudentMasernInfo|null $MasernCreatorType
     *
     * @return TblStudentMedicalRecord
     */
    public function createStudentMedicalRecord(
        $Disease,
        $Medication,
        $AttendingDoctor,
        $InsuranceState,
        $Insurance,
        $InsuranceNumber,
        $MasernDate = null,
        TblStudentMasernInfo $MasernDocumentType = null,
        TblStudentMasernInfo  $MasernCreatorType = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        if ($InsuranceState === null || $InsuranceState === '') {
            $InsuranceState = 0;
        }

        $Entity = new TblStudentMedicalRecord();
        $Entity->setDisease($Disease);
        $Entity->setMedication($Medication);
        $Entity->setAttendingDoctor($AttendingDoctor);
        $Entity->setInsuranceState($InsuranceState);
        $Entity->setInsurance($Insurance);
        $Entity->setInsuranceNumber($InsuranceNumber);
        $Entity->setMasernDate($MasernDate);
        $Entity->setMasernDocumentType($MasernDocumentType);
        $Entity->setMasernCreatorType($MasernCreatorType);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblStudentMedicalRecord $tblStudentMedicalRecord
     * @param string $Disease
     * @param string $Medication
     * @param string $AttendingDoctor
     * @param int $InsuranceState
     * @param string $Insurance
     * @param $InsuranceNumber
     * @param DateTime|null $MasernDate
     * @param TblStudentMasernInfo|null $MasernDocumentType
     * @param TblStudentMasernInfo|null $MasernCreatorType
     *
     * @return bool
     */
    public function updateStudentMedicalRecord(
        TblStudentMedicalRecord $tblStudentMedicalRecord,
        $Disease,
        $Medication,
        $AttendingDoctor,
        $InsuranceState,
        $Insurance,
        $InsuranceNumber,
        $MasernDate = null,
        TblStudentMasernInfo $MasernDocumentType = null,
        TblStudentMasernInfo $MasernCreatorType = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblStudentMedicalRecord $Entity */
        $Entity = $Manager->getEntityById('TblStudentMedicalRecord', $tblStudentMedicalRecord->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setDisease($Disease);
            $Entity->setMedication($Medication);
            $Entity->setAttendingDoctor($AttendingDoctor);
            $Entity->setInsuranceState($InsuranceState);
            $Entity->setInsurance($Insurance);
            $Entity->setInsuranceNumber($InsuranceNumber);
            $Entity->setMasernDate($MasernDate);
            $Entity->setMasernDocumentType($MasernDocumentType);
            $Entity->setMasernCreatorType($MasernCreatorType);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

//    /**
//     * @return bool|int
//     * @deprecated MAX don't display Highest Number
//     */
//    public function getStudentMaxIdentifier()
//    {
//
//        $Manager = $this->getConnection()->getEntityManager();
//
//        // cast to int didn't work with QueryBuilder
//        $Query = $Manager->getQueryBuilder()
//            ->select('MAX(CAST(S.Identifier AS UNSIGNED))')
//            ->from(__NAMESPACE__ . '\Entity\TblStudent', 'S')
//            ->getQuery();
//
//        $result = $Query->getResult();
//
//        if(!empty($result)){
//            if(isset($result[0][1])) {
//                $result = $result[0][1];
//            }
//        }
//        return ( $result ? $result : false );
//    }

    /**
     * @return false|TblStudent[]
     */
    public function getStudentAll()
    {
        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
//        return $this->getForceEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudent'
        );
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentMedicalRecord
     */
    public function getStudentMedicalRecordById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentMedicalRecord', $Id
        );
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentMedicalRecord
     */
    public function getStudentInsuranceStateById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentInsuranceState', $Id
        );
    }

    /**
     * @param $Name
     *
     * @return false|TblStudentInsuranceState
     */
    public function getStudentInsuranceStateByName($Name)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblStudentInsuranceState', array(
                TblStudentInsuranceState::ATTR_NAME => $Name
            )
        );
    }

    /**
     * @param $Id
     *
     * @return false|TblStudentMasernInfo
     */
    public function getStudentMasernInfoById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblStudentMasernInfo', $Id);
    }

    /**
     * @param string $Type
     *
     * @return false|TblStudentMasernInfo[]
     */
    public function getStudentMasernInfoByType($Type = TblStudentMasernInfo::TYPE_DOCUMENT)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblStudentMasernInfo', array(
                TblStudentMasernInfo::ATTR_TYPE => $Type
            )
        );
    }

    /**
     * @return bool|TblStudentMedicalRecord[]
     */
    public function getStudentInsuranceStateAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentInsuranceState'
        );
    }

    /**
     * @param string $BaptismDate
     * @param string $Location
     *
     * @return TblStudentBaptism
     */
    public function createStudentBaptism(
        $BaptismDate,
        $Location
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblStudentBaptism();
        $Entity->setBaptismDate(( $BaptismDate ? new DateTime($BaptismDate) : null ));
        $Entity->setLocation($Location);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblStudentBaptism $tblStudentBaptism
     * @param string            $BaptismDate
     * @param string            $Location
     *
     * @return bool
     */
    public function updateStudentBaptism(
        TblStudentBaptism $tblStudentBaptism,
        $BaptismDate,
        $Location
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblStudentBaptism $Entity */
        $Entity = $Manager->getEntityById('TblStudentBaptism', $tblStudentBaptism->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setBaptismDate(( $BaptismDate ? new DateTime($BaptismDate) : null ));
            $Entity->setLocation($Location);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentBaptism
     */
    public function getStudentBaptismById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentBaptism', $Id
        );
    }

    /** @deprecated
     * @param null|TblSiblingRank $tblSiblingRank
     *
     * @return TblStudentBilling
     */
    public function createStudentBilling(
        TblSiblingRank $tblSiblingRank = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblStudentBilling();
        $Entity->setServiceTblSiblingRank($tblSiblingRank);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /** @deprecated
     * @param TblStudentBilling   $tblStudentBilling
     * @param null|TblSiblingRank $tblSiblingRank
     *
     * @return bool
     */
    public function updateStudentBilling(
        TblStudentBilling $tblStudentBilling,
        TblSiblingRank $tblSiblingRank = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblStudentBilling $Entity */
        $Entity = $Manager->getEntityById('TblStudentBilling', $tblStudentBilling->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setServiceTblSiblingRank($tblSiblingRank);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentBilling
     */
    public function getStudentBillingById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentBilling', $Id
        );
    }

    /**
     * @param string $LockerNumber
     * @param string $LockerLocation
     * @param string $KeyNumber
     * @param string $CombinationLockNumber
     *
     * @return TblStudentLocker
     */
    public function createStudentLocker(
        $LockerNumber,
        $LockerLocation,
        $KeyNumber,
        $CombinationLockNumber
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblStudentLocker();
        $Entity->setLockerNumber($LockerNumber);
        $Entity->setLockerLocation($LockerLocation);
        $Entity->setKeyNumber($KeyNumber);
        $Entity->setCombinationLockNumber($CombinationLockNumber);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblStudentLocker $tblStudentLocker
     * @param string           $LockerNumber
     * @param string           $LockerLocation
     * @param string           $KeyNumber
     * @param string           $CombinationLockNumber
     *
     * @return bool
     */
    public function updateStudentLocker(
        TblStudentLocker $tblStudentLocker,
        $LockerNumber,
        $LockerLocation,
        $KeyNumber,
        $CombinationLockNumber
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblStudentLocker $Entity */
        $Entity = $Manager->getEntityById('TblStudentLocker', $tblStudentLocker->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setLockerNumber($LockerNumber);
            $Entity->setLockerLocation($LockerLocation);
            $Entity->setKeyNumber($KeyNumber);
            $Entity->setCombinationLockNumber($CombinationLockNumber);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentLocker
     */
    public function getStudentLockerById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentLocker', $Id
        );
    }

    /**
     * @param string $Route
     * @param string $StationEntrance
     * @param string $StationExit
     * @param string $Remark
     * @param boolean $IsDriverStudent
     *
     * @return TblStudentTransport
     */
    public function createStudentTransport(
        $Route,
        $StationEntrance,
        $StationExit,
        $Remark,
        $IsDriverStudent
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblStudentTransport();
        $Entity->setRoute($Route);
        $Entity->setStationEntrance($StationEntrance);
        $Entity->setStationExit($StationExit);
        $Entity->setRemark($Remark);
        $Entity->setIsDriverStudent($IsDriverStudent);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblStudentTransport $tblStudentTransport
     * @param string              $Route
     * @param string              $StationEntrance
     * @param string              $StationExit
     * @param string              $Remark
     * @param boolean             $IsDriverStudent
     *
     * @return bool
     */
    public function updateStudentTransport(
        TblStudentTransport $tblStudentTransport,
        $Route,
        $StationEntrance,
        $StationExit,
        $Remark,
        $IsDriverStudent
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblStudentTransport $Entity */
        $Entity = $Manager->getEntityById('TblStudentTransport', $tblStudentTransport->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setRoute($Route);
            $Entity->setStationEntrance($StationEntrance);
            $Entity->setStationExit($StationExit);
            $Entity->setRemark($Remark);
            $Entity->setIsDriverStudent($IsDriverStudent);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentTransport
     */
    public function getStudentTransportById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentTransport', $Id
        );
    }

    /**
     * @param Element[] $EntityList
     * @param Element[] $ProtocolList
     *
     * @return bool
     *
     */
    public function bulkSaveEntityList($EntityList = array(), $ProtocolList = array())
    {

        $Manager = $this->getConnection()->getEntityManager();
        if (!empty($EntityList)) {
            foreach ($EntityList as $key => $Entity) {
                $Manager->bulkSaveEntity($Entity);
                if ($ProtocolList[$key] === false) {
                    Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity, true);
                } else {
                    Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                        $ProtocolList[$key],
                        $Entity, true);
                }
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
            return true;
        }
        return false;
    }

    /**
     * @param $IsHeavyMultipleHandicapped
     * @param $IncreaseFactorHeavyMultipleHandicappedSchool
     * @param $IncreaseFactorHeavyMultipleHandicappedRegionalAuthorities
     * @param $RemarkHeavyMultipleHandicapped
     * @param $DegreeOfHandicap
     * @param $Sign
     * @param $ValidTo
     * @param TblStudentSpecialNeedsLevel|null $tblStudentSpecialNeedsLevel
     *
     * @return TblStudentSpecialNeeds
     */
    public function createStudentSpecialNeeds(
        $IsHeavyMultipleHandicapped,
        $IncreaseFactorHeavyMultipleHandicappedSchool,
        $IncreaseFactorHeavyMultipleHandicappedRegionalAuthorities,
        $RemarkHeavyMultipleHandicapped,
        $DegreeOfHandicap,
        $Sign,
        $ValidTo,
        TblStudentSpecialNeedsLevel $tblStudentSpecialNeedsLevel = null
    ) {

        $Manager = $this->getEntityManager();

        $Entity = new TblStudentSpecialNeeds();
        $Entity->setIsHeavyMultipleHandicapped($IsHeavyMultipleHandicapped);
        $Entity->setIncreaseFactorHeavyMultipleHandicappedSchool($IncreaseFactorHeavyMultipleHandicappedSchool);
        $Entity->setIncreaseFactorHeavyMultipleHandicappedRegionalAuthorities($IncreaseFactorHeavyMultipleHandicappedRegionalAuthorities);
        $Entity->setRemarkHeavyMultipleHandicapped($RemarkHeavyMultipleHandicapped);
        $Entity->setDegreeOfHandicap($DegreeOfHandicap);
        $Entity->setSign($Sign);
        $Entity->setValidTo($ValidTo);
        $Entity->setTblStudentSpecialNeedsLevel($tblStudentSpecialNeedsLevel);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblStudentSpecialNeedsLevel|null $tblStudentSpecialNeedsLevel
     *
     * @return TblStudentSpecialNeeds
     */
    public function insertStudentSpecialNeedsLevel(TblStudentSpecialNeedsLevel $tblStudentSpecialNeedsLevel = null)
    {

        $Manager = $this->getEntityManager();

        $Entity = new TblStudentSpecialNeeds();
        $Entity->setIsHeavyMultipleHandicapped(false);
        $Entity->setIncreaseFactorHeavyMultipleHandicappedSchool('');
        $Entity->setIncreaseFactorHeavyMultipleHandicappedRegionalAuthorities('');
        $Entity->setRemarkHeavyMultipleHandicapped('');
        $Entity->setDegreeOfHandicap('');
        $Entity->setSign('');
        $Entity->setValidTo('');
        $Entity->setTblStudentSpecialNeedsLevel($tblStudentSpecialNeedsLevel);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblStudentSpecialNeeds $tblStudentSpecialNeeds
     * @param $IsHeavyMultipleHandicapped
     * @param $IncreaseFactorHeavyMultipleHandicappedSchool
     * @param $IncreaseFactorHeavyMultipleHandicappedRegionalAuthorities
     * @param $RemarkHeavyMultipleHandicapped
     * @param $DegreeOfHandicap
     * @param $Sign
     * @param $ValidTo
     * @param TblStudentSpecialNeedsLevel|null $tblStudentSpecialNeedsLevel
     *
     * @return bool
     */
    public function updateStudentSpecialNeeds(
        TblStudentSpecialNeeds $tblStudentSpecialNeeds,
        $IsHeavyMultipleHandicapped,
        $IncreaseFactorHeavyMultipleHandicappedSchool,
        $IncreaseFactorHeavyMultipleHandicappedRegionalAuthorities,
        $RemarkHeavyMultipleHandicapped,
        $DegreeOfHandicap,
        $Sign,
        $ValidTo,
        TblStudentSpecialNeedsLevel $tblStudentSpecialNeedsLevel = null
    ) {

        $Manager = $this->getEntityManager();
        /** @var null|TblStudentSpecialNeeds $Entity */
        $Entity = $Manager->getEntityById('TblStudentSpecialNeeds', $tblStudentSpecialNeeds->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setIsHeavyMultipleHandicapped($IsHeavyMultipleHandicapped);
            $Entity->setIncreaseFactorHeavyMultipleHandicappedSchool($IncreaseFactorHeavyMultipleHandicappedSchool);
            $Entity->setIncreaseFactorHeavyMultipleHandicappedRegionalAuthorities($IncreaseFactorHeavyMultipleHandicappedRegionalAuthorities);
            $Entity->setRemarkHeavyMultipleHandicapped($RemarkHeavyMultipleHandicapped);
            $Entity->setDegreeOfHandicap($DegreeOfHandicap);
            $Entity->setSign($Sign);
            $Entity->setValidTo($ValidTo);
            $Entity->setTblStudentSpecialNeedsLevel($tblStudentSpecialNeedsLevel);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param string $Identifier
     * @param string $Name
     *
     * @return TblStudentTenseOfLesson
     */
    public function createStudentTenseOfLesson($Identifier, $Name)
    {
        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblStudentTenseOfLesson')->findOneBy(array(
            TblStudentTenseOfLesson::ATTR_IDENTIFIER => $Identifier
        ));

        if (null === $Entity) {
            $Entity = new TblStudentTenseOfLesson();
            $Entity->setIdentifier($Identifier);
            $Entity->setName($Name);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param string $Identifier
     * @param string $Name
     *
     * @return TblStudentTrainingStatus
     */
    public function createStudentTrainingStatus($Identifier, $Name)
    {
        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblStudentTrainingStatus')->findOneBy(array(
            TblStudentTrainingStatus::ATTR_IDENTIFIER => $Identifier
        ));

        if (null === $Entity) {
            $Entity = new TblStudentTrainingStatus();
            $Entity->setIdentifier($Identifier);
            $Entity->setName($Name);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param $praxisLessons
     * @param $durationOfTraining
     * @param $remark
     * @param TblTechnicalCourse|null $tblTechnicalCourse
     * @param TblSchoolDiploma|null $tblSchoolDiploma
     * @param TblType|null $tblSchoolType
     * @param TblTechnicalDiploma|null $tblTechnicalDiploma
     * @param TblType|null $tblTechnicalType
     * @param TblStudentTenseOfLesson|null $tblStudentTenseOfLesson
     * @param TblStudentTrainingStatus|null $tblStudentTrainingStatus
     * @param string $yearOfSchoolDiploma
     * @param string $yearOfTechnicalDiploma
     * @param TblTechnicalSubjectArea|null $tblTechnicalSubjectArea
     * @param bool $hasFinancialAid
     * @param string $financialAidApplicationYear
     * @param string $financialAidBureau
     *
     * @return TblStudentTechnicalSchool
     */
    public function createStudentTechnicalSchool(
        $praxisLessons,
        $durationOfTraining,
        $remark,
        TblTechnicalCourse $tblTechnicalCourse = null,
        TblSchoolDiploma $tblSchoolDiploma = null,
        TblType $tblSchoolType = null,
        TblTechnicalDiploma $tblTechnicalDiploma = null,
        TblType $tblTechnicalType = null,
        TblStudentTenseOfLesson $tblStudentTenseOfLesson = null,
        TblStudentTrainingStatus $tblStudentTrainingStatus = null,
        $yearOfSchoolDiploma = '',
        $yearOfTechnicalDiploma = '',
        TblTechnicalSubjectArea $tblTechnicalSubjectArea = null,
        $hasFinancialAid = false,
        $financialAidApplicationYear = '',
        $financialAidBureau = ''
    ) {
        $Manager = $this->getEntityManager();

        $Entity = new TblStudentTechnicalSchool();
        $Entity->setPraxisLessons($praxisLessons);
        $Entity->setDurationOfTraining($durationOfTraining);
        $Entity->setRemark($remark);
        $Entity->setServiceTblTechnicalCourse($tblTechnicalCourse);
        $Entity->setServiceTblSchoolDiploma($tblSchoolDiploma);
        $Entity->setServiceTblSchoolType($tblSchoolType);
        $Entity->setServiceTblTechnicalDiploma($tblTechnicalDiploma);
        $Entity->setServiceTblTechnicalType($tblTechnicalType);
        $Entity->setTblStudentTenseOfLesson($tblStudentTenseOfLesson);
        $Entity->setTblStudentTrainingStatus($tblStudentTrainingStatus);
        $Entity->setYearOfSchoolDiploma($yearOfSchoolDiploma);
        $Entity->setYearOfTechnicalDiploma($yearOfTechnicalDiploma);
        $Entity->setServiceTblTechnicalSubjectArea($tblTechnicalSubjectArea);
        $Entity->setHasFinancialAid($hasFinancialAid);
        $Entity->setFinancialAidApplicationYear($financialAidApplicationYear);
        $Entity->setFinancialAidBureau($financialAidBureau);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblStudentTechnicalSchool $tblStudentTechnicalSchool
     * @param $praxisLessons
     * @param $durationOfTraining
     * @param $remark
     * @param TblTechnicalCourse|null $tblTechnicalCourse
     * @param TblSchoolDiploma|null $tblSchoolDiploma
     * @param TblType|null $tblSchoolType
     * @param TblTechnicalDiploma|null $tblTechnicalDiploma
     * @param TblType|null $tblTechnicalType
     * @param TblStudentTenseOfLesson|null $tblStudentTenseOfLesson
     * @param TblStudentTrainingStatus|null $tblStudentTrainingStatus
     * @param string $yearOfSchoolDiploma
     * @param string $yearOfTechnicalDiploma
     * @param TblTechnicalSubjectArea|null $tblTechnicalSubjectArea
     * @param bool $hasFinancialAid
     * @param string $financialAidApplicationYear
     * @param string $financialAidBureau
     *
     * @return bool
     */
    public function updateStudentTechnicalSchool(
        TblStudentTechnicalSchool $tblStudentTechnicalSchool,
        $praxisLessons,
        $durationOfTraining,
        $remark,
        TblTechnicalCourse $tblTechnicalCourse = null,
        TblSchoolDiploma $tblSchoolDiploma = null,
        TblType $tblSchoolType = null,
        TblTechnicalDiploma $tblTechnicalDiploma = null,
        TblType $tblTechnicalType = null,
        TblStudentTenseOfLesson $tblStudentTenseOfLesson = null,
        TblStudentTrainingStatus $tblStudentTrainingStatus = null,
        $yearOfSchoolDiploma = '',
        $yearOfTechnicalDiploma = '',
        TblTechnicalSubjectArea $tblTechnicalSubjectArea = null,
        $hasFinancialAid = false,
        $financialAidApplicationYear = '',
        $financialAidBureau = ''
    ) {

        $Manager = $this->getEntityManager();
        /** @var null|TblStudentTechnicalSchool $Entity */
        $Entity = $Manager->getEntityById('TblStudentTechnicalSchool', $tblStudentTechnicalSchool->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;

            $Entity->setPraxisLessons($praxisLessons);
            $Entity->setDurationOfTraining($durationOfTraining);
            $Entity->setRemark($remark);
            $Entity->setServiceTblTechnicalCourse($tblTechnicalCourse);
            $Entity->setServiceTblSchoolDiploma($tblSchoolDiploma);
            $Entity->setServiceTblSchoolType($tblSchoolType);
            $Entity->setServiceTblTechnicalDiploma($tblTechnicalDiploma);
            $Entity->setServiceTblTechnicalType($tblTechnicalType);
            $Entity->setTblStudentTenseOfLesson($tblStudentTenseOfLesson);
            $Entity->setTblStudentTrainingStatus($tblStudentTrainingStatus);
            $Entity->setYearOfSchoolDiploma($yearOfSchoolDiploma);
            $Entity->setYearOfTechnicalDiploma($yearOfTechnicalDiploma);
            $Entity->setServiceTblTechnicalSubjectArea($tblTechnicalSubjectArea);
            $Entity->setHasFinancialAid($hasFinancialAid);
            $Entity->setFinancialAidApplicationYear($financialAidApplicationYear);
            $Entity->setFinancialAidBureau($financialAidBureau);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }
}
