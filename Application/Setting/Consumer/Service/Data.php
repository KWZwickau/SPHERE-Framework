<?php
namespace SPHERE\Application\Setting\Consumer\Service;

use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
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

        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount && ($tblConsumer = $tblAccount->getServiceTblConsumer())) {

            $this->createSetting('People', 'Meta', 'Student', 'Automatic_StudentNumber',
                TblSetting::TYPE_BOOLEAN, '0');

            $this->createSetting('Transfer', 'Indiware', 'Import', 'Lectureship_ConvertDivisionLatinToGreek',
                TblSetting::TYPE_BOOLEAN, '0');

            $this->createSetting('Contact', 'Address', 'Address', 'Format_GuiString',
                TblSetting::TYPE_STRING, TblAddress::VALUE_PLZ_ORT_OT_STR_NR);

            $this->createSetting('Api', 'Document', 'Standard', 'PasswordChange_PictureAddress',
                TblSetting::TYPE_STRING, '');
            $this->createSetting('Api', 'Document', 'Standard', 'PasswordChange_PictureHeight',
                TblSetting::TYPE_STRING, '');

            $this->createSetting('Api', 'Document', 'Standard', 'SignOutCertificate_PictureAddress',
                TblSetting::TYPE_STRING, '');
            $this->createSetting('Api', 'Document', 'Standard', 'SignOutCertificate_PictureHeight',
                TblSetting::TYPE_STRING, '');

            $this->createSetting('Api', 'Document', 'Standard', 'EnrollmentDocument_PictureAddress',
                TblSetting::TYPE_STRING, '');
            // Höhe sollte kleiner als 120px sein
            $this->createSetting('Api', 'Document', 'Standard', 'EnrollmentDocument_PictureHeight',
                TblSetting::TYPE_STRING, '');

            // Logo für das Zeugnis darf skalliert nicht breiter sein als 182px (bei einer höhe von 50px [Bsp.: 546 * 150 ist noch ok])
            if ($tblConsumer->getAcronym() == 'ESS') {
                $this->createSetting('Education', 'Certificate', 'Generate', 'PictureAddress', TblSetting::TYPE_STRING,
                    '/Common/Style/Resource/Logo/ESS-Zeugnis-Logo.png');
            } else {
                $this->createSetting('Education', 'Certificate', 'Generate', 'PictureAddress', TblSetting::TYPE_STRING,
                    '');
            }

            if ($tblConsumer->getAcronym() == 'ESZC'
                || $tblConsumer->getAcronym() == 'EVSC'
            ) {
                $this->createSetting('Api', 'Education', 'Certificate', 'OrientationAcronym', TblSetting::TYPE_STRING,
                    'NK');
                $this->createSetting('Api', 'Education', 'Certificate', 'ProfileAcronym', TblSetting::TYPE_STRING,
                    'PRO');
            } else {
                $this->createSetting('Api', 'Education', 'Certificate', 'OrientationAcronym', TblSetting::TYPE_STRING,
                    '');
                $this->createSetting('Api', 'Education', 'Certificate', 'ProfileAcronym', TblSetting::TYPE_STRING,
                    '');
            }

            // Anzeige des Notendurchschnitts in der Eltern/Schüler-Übersicht
            $this->createSetting('Education', 'Graduation', 'Gradebook', 'IsShownAverageInStudentOverview', TblSetting::TYPE_BOOLEAN, false);
            // Anzeige des Notenspiegels und des Fach-Klassendurchschnitts in der Eltern/Schüler-Übersicht
            $this->createSetting('Education', 'Graduation', 'Gradebook', 'IsShownScoreInStudentOverview', TblSetting::TYPE_BOOLEAN, false);

            if ($tblConsumer->getAcronym() == 'ESZC') {
                $this->createSetting(
                    'Education',
                    'Certificate',
                    'Prepare',
                    'IsGradeVerbalOnDiploma',
                    TblSetting::TYPE_BOOLEAN,
                    '1'
                );
            } else {
                $this->createSetting(
                    'Education',
                    'Certificate',
                    'Prepare',
                    'IsGradeVerbalOnDiploma',
                    TblSetting::TYPE_BOOLEAN,
                    '0'
                );
            }

            if ($tblConsumer->getAcronym() == 'EVAMTL'
                || $tblConsumer->getAcronym() == 'CMS'
            ) {
                $this->createSetting(
                    'Education',
                    'Certificate',
                    'Prepare',
                    'IsSchoolExtendedNameDisplayed',
                    TblSetting::TYPE_BOOLEAN,
                    '1'
                );
            } else {
                $this->createSetting(
                    'Education',
                    'Certificate',
                    'Prepare',
                    'IsSchoolExtendedNameDisplayed',
                    TblSetting::TYPE_BOOLEAN,
                    '0'
                );
            }

            // Verwendung aller Kopfnotenaufträgen für eine Zeugnisvorbereitung
            if ($tblConsumer->getAcronym() == 'EGE'
                || $tblConsumer->getAcronym() == 'FESH'
            ) {
                $this->createSetting(
                    'Education',
                    'Certificate',
                    'Prepare',
                    'UseMultipleBehaviorTasks',
                    TblSetting::TYPE_BOOLEAN,
                    '1'
                );
            } else {
                $this->createSetting(
                    'Education',
                    'Certificate',
                    'Prepare',
                    'UseMultipleBehaviorTasks',
                    TblSetting::TYPE_BOOLEAN,
                    '0'
                );
            }

            if ($tblConsumer->getAcronym() == 'EVSC') {
                // Notenbuch: Sortierung nach Großen (fettmarkiert) und Klein Noten
                $this->createSetting(
                    'Education',
                    'Graduation',
                    'Gradebook',
                    'SortHighlighted',
                    TblSetting::TYPE_BOOLEAN,
                    '1'
                );
                // Notenbuch: Große Noten (fettmarkiert) rechts anordnen
                $this->createSetting(
                    'Education',
                    'Graduation',
                    'Gradebook',
                    'IsHighlightedSortedRight',
                    TblSetting::TYPE_BOOLEAN,
                    '1'
                );
            } else {
                $this->createSetting(
                    'Education',
                    'Graduation',
                    'Gradebook',
                    'SortHighlighted',
                    TblSetting::TYPE_BOOLEAN,
                    '0'
                );
                $this->createSetting(
                    'Education',
                    'Graduation',
                    'Gradebook',
                    'IsHighlightedSortedRight',
                    TblSetting::TYPE_BOOLEAN,
                    '1'
                );
            }

            // Anzeige des Schulnamens auf der Schülerkartei für die Grundschule
            if ($tblConsumer->getAcronym() == 'ESRL') {
                $this->createSetting('Api', 'Document', 'StudentCard_PrimarySchool', 'ShowSchoolName', TblSetting::TYPE_BOOLEAN, '0');
            } else {
                $this->createSetting('Api', 'Document', 'StudentCard_PrimarySchool', 'ShowSchoolName', TblSetting::TYPE_BOOLEAN, '1');
            }
        }
        $this->createSetting(
            'Education',
            'Certificate',
            'Generate',
            'UseCourseForCertificateChoosing',
            TblSetting::TYPE_BOOLEAN,
            '1'
        );
        $this->createSetting(
            'Education',
            'ClassRegister',
            'Sort',
            'SortMaleFirst',
            TblSetting::TYPE_BOOLEAN,
            '1'
        );
        // Kopfnoten können auch für Fächer vergebenen werden, welche nicht benotet werden
        $this->createSetting(
            'Education',
            'Graduation',
            'Evaluation',
            'HasBehaviorGradesForSubjectsWithNoGrading',
            TblSetting::TYPE_BOOLEAN,
            '0'
        );
        // automatische Bekanntgabe von Leistungsüberprüfungen nach x Tagen für die Notenübersicht für Schüler
        $this->createSetting(
            'Education',
            'Graduation',
            'Evaluation',
            'AutoPublicationOfTestsAfterXDays',
            TblSetting::TYPE_INTEGER,
            '28'
        );
        // Zensuren im Wortlaut auf Abgangszeugnissen
        $this->createSetting(
            'Education',
            'Certificate',
            'Prepare',
            'IsGradeVerbalOnLeave',
            TblSetting::TYPE_BOOLEAN,
            '0'
        );
        $this->createSetting('Setting', 'Consumer', 'Service', 'Sort_UmlautWithE',
            TblSetting::TYPE_BOOLEAN, '1');

        $this->createSetting('Setting', 'Consumer', 'Service', 'Sort_WithShortWords',
            TblSetting::TYPE_BOOLEAN, '1');
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