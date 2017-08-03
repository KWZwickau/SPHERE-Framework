<?php
namespace SPHERE\Application\Setting\Consumer\Service;

use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Setting\Consumer\Service\Entity\TblSetting;
use SPHERE\System\Database\Binding\AbstractData;

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

            $this->createSetting('Transfer', 'Indiware', 'Import', 'Lectureship_ConvertDivisionLatinToGreek',
                TblSetting::TYPE_BOOLEAN, '0');

            $this->createSetting('Contact', 'Address', 'Address', 'Format_GuiString',
                TblSetting::TYPE_STRING, TblAddress::VALUE_PLZ_ORT_OT_STR_NR);

            $this->createSetting('Api', 'Document', 'Standard', 'EnrollmentDocument_PictureAddress',
                TblSetting::TYPE_STRING, '');
            // Höhe sollte kleiner als 120px sein
            $this->createSetting('Api', 'Document', 'Standard', 'EnrollmentDocument_PictureHeight',
                TblSetting::TYPE_STRING,
                '');

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
     * @param $Cluster
     * @param $Application
     * @param null $Module
     * @param $Identifier
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
}