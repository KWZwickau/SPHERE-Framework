<?php
namespace SPHERE\Application\People\Meta\Student\Service;

use SPHERE\Application\People\Meta\Student\Service\Data\Support;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentBaptism;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentBilling;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentLocker;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentMedicalRecord;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransport;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentAgreement;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentBaptism;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentDisorder;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentFocus;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentIntegration;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentLiberation;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentLocker;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentMedicalRecord;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentTransfer;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentTransport;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Service\Entity\TblSiblingRank;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Data
 *
 * @package SPHERE\Application\People\Meta\Student\Service
 */
class Data extends Support
{

    /**
     * @return false|ViewStudent[]
     */
    public function viewStudent()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewStudent'
        );
    }

    /**
     * @return false|ViewStudentAgreement[]
     */
    public function viewStudentAgreement()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewStudentAgreement'
        );
    }

    /**
     * @return false|ViewStudentBaptism[]
     */
    public function viewStudentBaptism()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewStudentBaptism'
        );
    }

//    /**
//     * @return false|ViewStudentBilling[]
//     */
//    public function viewStudentBilling()
//    {
//
//        return $this->getCachedEntityList(
//            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewStudentBilling'
//        );
//    }

    /**
     * @return false|ViewStudentDisorder[]
     */
    public function viewStudentDisorder()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewStudentDisorder'
        );
    }

    /**
     * @return false|ViewStudentFocus[]
     */
    public function viewStudentFocus()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewStudentFocus'
        );
    }

    /**
     * @return false|ViewStudentIntegration[]
     */
    public function viewStudentIntegration()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewStudentIntegration'
        );
    }

    /**
     * @return false|ViewStudentLiberation[]
     */
    public function viewStudentLiberation()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewStudentLiberation'
        );
    }

    /**
     * @return false|ViewStudentLocker[]
     */
    public function viewStudentLocker()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewStudentLocker'
        );
    }

    /**
     * @return false|ViewStudentMedicalRecord[]
     */
    public function viewStudentMedicalRecord()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewStudentMedicalRecord'
        );
    }

//    /**
//     * @return false|ViewStudentSubject[]
//     */
//    public function viewStudentSubject()
//    {
//
//        return $this->getCachedEntityList(
//            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewStudentSubject'
//        );
//    }

    /**
     * @return false|ViewStudentTransfer[]
     */
    public function viewStudentTransfer()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewStudentTransfer'
        );
    }

    /**
     * @return false|ViewStudentTransport[]
     */
    public function viewStudentTransport()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewStudentTransport'
        );
    }

    public function setupDatabaseContent()
    {

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

        $Entity = $this->getStudentSubjectTypeByIdentifier('ORIENTATION');
        if ($Entity) {
            if ($Entity->getName() !== 'Neigungskurs') {
                $this->updateStudentSubjectType($Entity, 'Neigungskurs');
            }
        } else {
            $this->createStudentSubjectType('ORIENTATION', 'Neigungskurs');
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

        // old Table
        $this->createStudentFocusType('Sprache');
        $this->createStudentFocusType('Körperlich-motorische Entwicklung');
        $this->createStudentFocusType('Sozial-emotionale Entwicklung');
        $this->createStudentFocusType('Hören');
        $this->createStudentFocusType('Sehen');
        $this->createStudentFocusType('Geistige Entwicklung');
        $this->createStudentFocusType('Lernen');

        // new Table
        $this->createSupportFocusType('Sprache');
        $this->createSupportFocusType('Körperlich-motorische Entwicklung');
        $this->createSupportFocusType('Sozial-emotionale Entwicklung');
        $this->createSupportFocusType('Hören');
        $this->createSupportFocusType('Sehen');
        $this->createSupportFocusType('Geistige Entwicklung');
        $this->createSupportFocusType('Lernen');
        $this->createSupportFocusType('Unterricht kranker Schüler');

        // old Table
        $this->createStudentDisorderType('LRS');
        $this->createStudentDisorderType('Gehörschwierigkeiten');
        $this->createStudentDisorderType('Augenleiden');
        $this->createStudentDisorderType('Sprachfehler');
        $this->createStudentDisorderType('Dyskalkulie');
        $this->createStudentDisorderType('Autismus');
        $this->createStudentDisorderType('ADS / ADHS');
        $this->createStudentDisorderType('Rechenschwäche');
        $this->createStudentDisorderType('Hochbegabung');
        $this->createStudentDisorderType('Konzentrationsstörung');
        $this->createStudentDisorderType('Körperliche Beeinträchtigung');

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
        $this->createSupportType('Änderung', '');
        $this->createSupportType('Ablehnung', '');
        $this->createSupportType('Wiederspruch', '');
    }

    /**
     * @param string         $Disease
     * @param string         $Medication
     * @param null|TblPerson $tblPersonAttendingDoctor
     * @param int            $InsuranceState
     * @param string         $Insurance
     *
     * @return TblStudentMedicalRecord
     */
    public function createStudentMedicalRecord(
        $Disease,
        $Medication,
        TblPerson $tblPersonAttendingDoctor = null,
        $InsuranceState,
        $Insurance
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblStudentMedicalRecord();
        $Entity->setDisease($Disease);
        $Entity->setMedication($Medication);
        $Entity->setServiceTblPersonAttendingDoctor($tblPersonAttendingDoctor);
        $Entity->setInsuranceState($InsuranceState);
        $Entity->setInsurance($Insurance);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblStudentMedicalRecord $tblStudentMedicalRecord
     * @param string                  $Disease
     * @param string                  $Medication
     * @param null|TblPerson          $tblPersonAttendingDoctor
     * @param int                     $InsuranceState
     * @param string                  $Insurance
     *
     * @return bool
     */
    public function updateStudentMedicalRecord(
        TblStudentMedicalRecord $tblStudentMedicalRecord,
        $Disease,
        $Medication,
        TblPerson $tblPersonAttendingDoctor = null,
        $InsuranceState,
        $Insurance
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblStudentMedicalRecord $Entity */
        $Entity = $Manager->getEntityById('TblStudentMedicalRecord', $tblStudentMedicalRecord->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setDisease($Disease);
            $Entity->setMedication($Medication);
            $Entity->setServiceTblPersonAttendingDoctor($tblPersonAttendingDoctor);
            $Entity->setInsuranceState($InsuranceState);
            $Entity->setInsurance($Insurance);
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
        $Entity->setBaptismDate(( $BaptismDate ? new \DateTime($BaptismDate) : null ));
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
            $Entity->setBaptismDate(( $BaptismDate ? new \DateTime($BaptismDate) : null ));
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

    /**
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

    /**
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
     *
     * @return TblStudentLocker
     */
    public function createStudentLocker(
        $LockerNumber,
        $LockerLocation,
        $KeyNumber
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblStudentLocker();
        $Entity->setLockerNumber($LockerNumber);
        $Entity->setLockerLocation($LockerLocation);
        $Entity->setKeyNumber($KeyNumber);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblStudentLocker $tblStudentLocker
     * @param string           $LockerNumber
     * @param string           $LockerLocation
     * @param string           $KeyNumber
     *
     * @return bool
     */
    public function updateStudentLocker(
        TblStudentLocker $tblStudentLocker,
        $LockerNumber,
        $LockerLocation,
        $KeyNumber
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblStudentLocker $Entity */
        $Entity = $Manager->getEntityById('TblStudentLocker', $tblStudentLocker->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setLockerNumber($LockerNumber);
            $Entity->setLockerLocation($LockerLocation);
            $Entity->setKeyNumber($KeyNumber);
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
     *
     * @return TblStudentTransport
     */
    public function createStudentTransport(
        $Route,
        $StationEntrance,
        $StationExit,
        $Remark
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblStudentTransport();
        $Entity->setRoute($Route);
        $Entity->setStationEntrance($StationEntrance);
        $Entity->setStationExit($StationExit);
        $Entity->setRemark($Remark);
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
     *
     * @return bool
     */
    public function updateStudentTransport(
        TblStudentTransport $tblStudentTransport,
        $Route,
        $StationEntrance,
        $StationExit,
        $Remark
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
}
