<?php
namespace SPHERE\Application\Setting\Consumer\Service;

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
        // Logo für das Zeugnis darf skalliert nicht breiter sein als 182px (bei einer höhe von 50px [Bsp.: 546 * 150 ist noch ok])
        if ($tblAccount->getServiceTblConsumer()->getAcronym() == 'ESS') {
            $this->createSetting('Education', 'Certificate', 'Generate', 'PictureAddress', TblSetting::TYPE_STRING,
                '/Common/Style/Resource/Logo/ESS-Oberschule.png');
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
        $this->createSetting(
            'Education',
            'Certificate',
            'Prepare',
            'IsGradeVerbalOnDiploma',
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