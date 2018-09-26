<?php
namespace SPHERE\Application\Setting\Consumer\Service;

use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Setting\Consumer\Service\Entity\TblSetting;
use SPHERE\Application\Setting\Consumer\Service\Entity\TblStudentCustody;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Data
 * @package SPHERE\Application\Setting\Consumer\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        if (($tblSetting = $this->createSetting('People', 'Meta', 'Student', 'Automatic_StudentNumber', TblSetting::TYPE_BOOLEAN, '0'))) {
            $this->updateSettingDescription($tblSetting, 'Die Schülernummern werden automatisch vom System erstellt.
                In diesem Fall können die Schülernummern nicht per Hand vergeben werden.');
        }
        if (($tblSetting = $this->createSetting('Transfer', 'Indiware', 'Import', 'Lectureship_ConvertDivisionLatinToGreek', TblSetting::TYPE_BOOLEAN, '0'))) {
            $this->updateSettingDescription($tblSetting, 'Ersetzung der Klassengruppennamen beim Import in ausgeschriebene Griechische Buchstaben. (z.B. a => alpha)');
        }
        if (($tblSetting = $this->createSetting('Contact', 'Address', 'Address', 'Format_GuiString', TblSetting::TYPE_STRING, TblAddress::VALUE_PLZ_ORT_OT_STR_NR))) {
            $this->updateSettingDescription($tblSetting, 'Reihenfolge für Adressanzeige: 1 = PLZ_ORT_OT_STR_NR, 2 = OT_STR_NR_PLZ_ORT');
        }
        if (($tblSetting = $this->createSetting('Api', 'Document', 'Standard', 'PasswordChange_PictureAddress', TblSetting::TYPE_STRING, ''))) {
            $this->updateSettingDescription($tblSetting, 'Für die Eltern und Schülerzugänge kann für das Passwortänderungsanschreiben ein Bild (Logo) hinterlegt werden. Adresse des Bildes:');
        }
        if (($tblSetting = $this->createSetting('Api', 'Document', 'Standard', 'PasswordChange_PictureHeight', TblSetting::TYPE_STRING, ''))) {
            $this->updateSettingDescription($tblSetting, 'Für die Eltern und Schülerzugänge kann für das Passwortänderungsanschreiben ein Bild (Logo) hinterlegt werden. Höhe des Bildes (z.B. 70px):');
        }
        if (($tblSetting = $this->createSetting('Api', 'Document', 'Standard', 'SignOutCertificate_PictureAddress', TblSetting::TYPE_STRING, ''))) {
            $this->updateSettingDescription($tblSetting, 'Für die Abmeldebescheinigung kann ein Bild (Logo) hinterlegt werden. Adresse des Bildes:');
        }
        if (($tblSetting = $this->createSetting('Api', 'Document', 'Standard', 'SignOutCertificate_PictureHeight', TblSetting::TYPE_STRING, ''))) {
            $this->updateSettingDescription($tblSetting, 'Für die Abmeldebescheinigung kann ein Bild (Logo) hinterlegt werden. Höhe des Bildes (z.B. 70px):');
        }
        if (($tblSetting = $this->createSetting('Api', 'Document', 'Standard', 'EnrollmentDocument_PictureAddress', TblSetting::TYPE_STRING, ''))) {
            $this->updateSettingDescription($tblSetting, 'Für die Schulbescheinigung kann ein Bild (Logo) hinterlegt werden. Adresse des Bildes:');
        }
        if (($tblSetting = $this->createSetting('Api', 'Document', 'Standard', 'EnrollmentDocument_PictureHeight', TblSetting::TYPE_STRING, ''))) {
            // Höhe sollte kleiner als 120px sein
            $this->updateSettingDescription($tblSetting, 'Für die Schulbescheinigung kann ein Bild (Logo) hinterlegt werden. Höhe des Bildes (Maximal 120px):');
        }
        if (($tblSetting = $this->createSetting('Education', 'Certificate', 'Generate', 'PictureAddress', TblSetting::TYPE_STRING, ''))) {
            // Logo für das Zeugnis darf skalliert nicht breiter sein als 182px (bei einer höhe von 50px [Bsp.: 546 * 150 ist noch ok])
            $this->updateSettingDescription($tblSetting, 'Für die Standard-Zeugnisse kann ein Bild (Logo) hinterlegt werden. Logo für das Zeugnis darf skalliert nicht breiter sein als 182px (bei einer höhe von 50px [Bsp.: 546 * 150 ist noch ok]). Adresse des Bildes:');
        }
        if (($tblSetting = $this->createSetting('Api', 'Education', 'Certificate', 'OrientationAcronym', TblSetting::TYPE_STRING, ''))) {
            $this->updateSettingDescription($tblSetting, 'Werden die Neigungskurse in der Bildung nicht einzeln gepflegt, sondern nur ein einzelner Standard-Neigungskurs, kann hier das Kürzel des Standard-Neigungskurses (z.B. NK) hinterlegt werden. Für die Zeugnisse wir dann der eigentliche Neigungskurs aus der Schülerakte des Schülers gezogen.');
        }
        if (($tblSetting = $this->createSetting('Api', 'Education', 'Certificate', 'ProfileAcronym', TblSetting::TYPE_STRING, ''))) {
            $this->updateSettingDescription($tblSetting, 'Werden die Profile in der Bildung nicht einzeln gepflegt, sondern nur ein einzelnes Standard-Profil, kann hier das Kürzel des Standard-Profils (z.B. PRO) hinterlegt werden. Für die Zeugnisse wir dann das eigentliche Profil aus der Schülerakte des Schülers gezogen.');
        }
        if (($tblSetting = $this->createSetting('Education', 'Graduation', 'Gradebook', 'IsShownAverageInStudentOverview', TblSetting::TYPE_BOOLEAN, false))) {
            // Anzeige des Notendurchschnitts in der Eltern/Schüler-Übersicht
            $this->updateSettingDescription($tblSetting, 'Anzeige des Notendurchschnitts in der Eltern/Schüler-Übersicht', true);
        }
        if (($tblSetting = $this->createSetting('Education', 'Graduation', 'Gradebook', 'IsShownScoreInStudentOverview', TblSetting::TYPE_BOOLEAN, false))) {
            // Anzeige des Notenspiegels und des Fach-Klassendurchschnitts in der Eltern/Schüler-Übersicht
            $this->updateSettingDescription($tblSetting, 'Anzeige des Notenspiegels und des Fach-Klassendurchschnitts in der Eltern/Schüler-Übersicht', true);
        }
        if (($tblSetting = $this->createSetting('Education', 'Certificate', 'Prepare', 'IsGradeVerbalOnDiploma', TblSetting::TYPE_BOOLEAN, '0'))) {
            $this->updateSettingDescription($tblSetting, 'Anzeige der Zensuren im Wortlaut auf Abschlusszeugnissen', true);
        }
        if (($tblSetting =  $this->createSetting('Education', 'Certificate', 'Prepare', 'IsSchoolExtendedNameDisplayed', TblSetting::TYPE_BOOLEAN, '0'))) {
            $this->updateSettingDescription($tblSetting, 'Anzeige des Schul-Zusatzes (Institutionszusatz) auf Zeugnissen', true);
        }
        if (($tblSetting = $this->createSetting('Education', 'Certificate', 'Prepare', 'UseMultipleBehaviorTasks', TblSetting::TYPE_BOOLEAN, '0'))) {
            // Verwendung aller Kopfnotenaufträgen für eine Zeugnisvorbereitung
            $this->updateSettingDescription($tblSetting, 'Verwendung aller Kopfnotenaufträge des Schuljahres für die Zeugnisvorbereitung', true);
        }
        if (($tblSetting = $this->createSetting('Education', 'Graduation', 'Gradebook', 'SortHighlighted', TblSetting::TYPE_BOOLEAN, '0'))) {
            $this->updateSettingDescription($tblSetting, 'Sortierung der Zensuren im Notenbuch nach Großen (fettmarkiert) und Kleinen Noten', true);
        }
        if (($tblSetting = $this->createSetting('Education', 'Graduation', 'Gradebook', 'IsHighlightedSortedRight', TblSetting::TYPE_BOOLEAN, '1'))) {
            $this->updateSettingDescription($tblSetting, 'Bei der Sortierung der Zensuren im Notenbuch nach Großen (fettmarkiert) und Kleinen Noten, werden die Großen Noten nach rechts sortiert.', true);
        }
        if (($tblSetting = $this->createSetting('Education', 'Graduation', 'Gradebook', 'ShowAverageInPdf', TblSetting::TYPE_BOOLEAN, '1'))) {
            // Notenbuch Pdf download -> Durchschnittsnote anzeigen
            $this->updateSettingDescription($tblSetting, 'Anzeige des Notendurchschnitts im heruntergeladenen Notenbuch (PDF)', true);
        }
        if (($tblSetting = $this->createSetting('Education', 'Graduation', 'Gradebook', 'ShowCertificateGradeInPdf', TblSetting::TYPE_BOOLEAN, '1'))) {
            // Notenbuch Pdf download -> Zeugnisnoten anzeigen
            $this->updateSettingDescription($tblSetting, 'Anzeige der Zeugnisnote im heruntergeladenen Notenbuch (PDF)', true);
        }
        if (($tblSetting = $this->createSetting('Api', 'Document', 'StudentCard_PrimarySchool', 'ShowSchoolName', TblSetting::TYPE_BOOLEAN, '1'))) {
            // Anzeige des Schulnamens auf der Schülerkartei für die Grundschule
            $this->updateSettingDescription($tblSetting, 'Anzeige des Schulnamens oben rechts (Stempel-Feld) auf der Schülerkartei für die Grundschule', true);
        }
        if (($tblSetting = $this->createSetting('Reporting', 'KamenzReport', 'Validation', 'FirstForeignLanguageLevel', TblSetting::TYPE_INTEGER, 1))) {
            // Validierung (Kamenz + Schnittstelle) der 1. Fremdsprache ab Klassenstufe x
            $this->updateSettingDescription($tblSetting, 'Validierung (Kamenz + Schnittstelle), ob die 1. Fremdsprache in der Schülerakte gepflegt ist, ab Klassenstufe:', true);
        }
        if (($tblSetting = $this->createSetting('Education', 'Certificate', 'Generate', 'UseCourseForCertificateChoosing', TblSetting::TYPE_BOOLEAN, '1'))) {
            $this->updateSettingDescription($tblSetting, 'Es wird der Bildungsgang des Schülers verwendet, um die entsprechende Zeugnisvorlage (Mittelschule) dem Schüler automatisch zuzuordnen.');
        }
        if (($tblSetting = $this->createSetting('Education', 'ClassRegister', 'Sort', 'SortMaleFirst', TblSetting::TYPE_BOOLEAN, '1'))) {
            $this->updateSettingDescription($tblSetting, 'Bei der Sortierung der Schüler im Klassenbuch nach Geschlecht, stehen die männlichen Schüler zuerst. ', true);
        }
        if (($tblSetting = $this->createSetting('Education', 'Graduation', 'Evaluation', 'HasBehaviorGradesForSubjectsWithNoGrading', TblSetting::TYPE_BOOLEAN, '0'))) {
            // Kopfnoten können auch für Fächer vergebenen werden, welche nicht benotet werden
            $this->updateSettingDescription($tblSetting, 'Bei Kopfnotenaufträgen können auch Kopfnoten für Fächer vergebenen werden, welche nicht benotet werden.', true);
        }
        if (($tblSetting = $this->createSetting('Education', 'Graduation', 'Evaluation', 'AutoPublicationOfTestsAfterXDays', TblSetting::TYPE_INTEGER, '28'))) {
            // automatische Bekanntgabe von Leistungsüberprüfungen nach x Tagen für die Notenübersicht für Schüler
            $this->updateSettingDescription($tblSetting, 'Automatische Bekanntgabe von Leistungsüberprüfungen für die Notenübersicht der Schüler/Eltern nach x Tagen:', true);
        }
        if (($tblSetting = $this->createSetting('Education', 'Certificate', 'Prepare', 'IsGradeVerbalOnLeave', TblSetting::TYPE_BOOLEAN, '0'))) {
            // Zensuren im Wortlaut auf Abgangszeugnissen
            $this->updateSettingDescription($tblSetting, 'Anzeige der Zensuren im Wortlaut auf Abgangszeugnissen', true);
        }
        if (($tblSetting = $this->createSetting('Setting', 'Consumer', 'Service', 'Sort_UmlautWithE', TblSetting::TYPE_BOOLEAN, '1'))) {
            $this->updateSettingDescription($tblSetting, 'Bei der alphabetischen Sortierung von Namen werden Umlaute ersetzt durch Vokal + e (z.B. ä => ae)', true);
        }
        if (($tblSetting = $this->createSetting('Setting', 'Consumer', 'Service', 'Sort_WithShortWords', TblSetting::TYPE_BOOLEAN, '1'))) {
            $this->updateSettingDescription($tblSetting, 'Bei der alphabetischen Sortierung von Namen werden kurze Zwischenworte (z.B. von, der, die, ein) für die Sortierung berücksichtigt', true);
        }
        if (($tblSetting = $this->createSetting('Education', 'ClassRegister', 'Frontend', 'ShowDownloadButton', TblSetting::TYPE_BOOLEAN, '1'))) {
            $this->updateSettingDescription($tblSetting, 'Fachlehrer können sich im Klassenbuch die Standard-Klassenlisten-Auswertung (Excel) herunterladen.', true);
        }

//        $tblAccount = Account::useService()->getAccountBySession();
//        if ($tblAccount && ($tblConsumer = $tblAccount->getServiceTblConsumer())) {
//
//        }
    }

    /**
     * @param      $Cluster
     * @param      $Application
     * @param null $Module
     * @param      $Identifier
     *
     * @return false|TblSetting
     */
    public function getSetting(
        $Cluster,
        $Application,
        $Module = null,
        $Identifier
    ) {

        return $this->getCachedEntityBy(
            __METHOD__,
            $this->getConnection()->getEntityManager(),
            'TblSetting',
            array(
                TblSetting::ATTR_CLUSTER     => $Cluster,
                TblSetting::ATTR_APPLICATION => $Application,
                TblSetting::ATTR_MODULE      => $Module ? $Module : null,
                TblSetting::ATTR_IDENTIFIER  => $Identifier,
            )
        );
    }

    /**
     * @param bool $IsSystem
     *
     * @return false|TblSetting[]
     */
    public function getSettingAll($IsSystem = false)
    {

        if ($IsSystem) {
            return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblSetting', array(
                TblSetting::ATTR_APPLICATION => self::ORDER_ASC
            ));
        } else {
            return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblSetting', array(
                TblSetting::ATTR_IS_PUBLIC => true
            ), array(
                TblSetting::ATTR_APPLICATION => self::ORDER_ASC
            ));
        }
    }

    /**
     * @param $Id
     *
     * @return false|TblStudentCustody
     */
    public function getStudentCustodyById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblStudentCustody',
            $Id);
    }

    /**
     * @param TblAccount $tblAccountStudent
     *
     * @return false|TblStudentCustody[]
     */
    public function getStudentCustodyByStudent(TblAccount $tblAccountStudent)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblStudentCustody',
            array(
                TblStudentCustody::ATTR_SERVICE_TBL_ACCOUNT_STUDENT => $tblAccountStudent->getId()
            ));
    }

    /**
     * @param TblAccount $tblAccountStudent
     * @param TblAccount $tblAccountCustody
     *
     * @return false|TblStudentCustody
     */
    public function getStudentCustodyByStudentAndCustody(TblAccount $tblAccountStudent, TblAccount $tblAccountCustody)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblStudentCustody',
            array(
                TblStudentCustody::ATTR_SERVICE_TBL_ACCOUNT_STUDENT => $tblAccountStudent->getId(),
                TblStudentCustody::ATTR_SERVICE_TBL_ACCOUNT_CUSTODY => $tblAccountCustody->getId()
            ));
    }

    /**
     * @param $Cluster
     * @param $Application
     * @param null $Module
     * @param $Identifier
     * @param string $Type
     * @param $Value
     *
     * @return TblSetting
     */
    public function createSetting(
        $Cluster,
        $Application,
        $Module = null,
        $Identifier,
        $Type = TblSetting::TYPE_BOOLEAN,
        $Value
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblSetting')->findOneBy(array(
            TblSetting::ATTR_CLUSTER     => $Cluster,
            TblSetting::ATTR_APPLICATION => $Application,
            TblSetting::ATTR_MODULE      => $Module ? $Module : null,
            TblSetting::ATTR_IDENTIFIER  => $Identifier,
        ));
        if ($Entity === null) {
            $Entity = new TblSetting();
            $Entity->setCluster($Cluster);
            $Entity->setApplication($Application);
            $Entity->setModule($Module);
            $Entity->setIdentifier($Identifier);
            $Entity->setType($Type);
            $Entity->setValue($Value);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblSetting $tblSetting
     * @param $value
     *
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function updateSetting(TblSetting $tblSetting, $value)
    {
        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblSetting $Entity */
        $Entity = $Manager->getEntityById('TblSetting', $tblSetting->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setValue($value);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblSetting $tblSetting
     * @param string $description
     * @param bool $isPublic
     *
     * @return bool
     */
    public function updateSettingDescription(TblSetting $tblSetting, $description, $isPublic = false)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblSetting $Entity */
        $Entity = $Manager->getEntityById('TblSetting', $tblSetting->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setDescription($description);
            $Entity->setIsPublic($isPublic);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblAccount $tblAccountStudent
     * @param TblAccount $tblAccountCustody
     * @param TblAccount $tblAccountBlocker
     *
     * @return TblStudentCustody
     */
    public function createStudentCustody(
        TblAccount $tblAccountStudent,
        TblAccount $tblAccountCustody,
        TblAccount $tblAccountBlocker
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblStudentCustody();
        $Entity->setServiceTblAccountStudent($tblAccountStudent);
        $Entity->setServiceTblAccountCustody($tblAccountCustody);
        $Entity->setServiceTblAccountBlocker($tblAccountBlocker);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblStudentCustody $tblStudentCustody
     *
     * @return bool
     */
    public function removeStudentCustody(TblStudentCustody $tblStudentCustody)
    {
        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblStudentCustody')->findOneBy(array('Id' => $tblStudentCustody->getId()));
        if (null !== $Entity) {
            /** @var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }
}