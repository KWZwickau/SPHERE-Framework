<?php
namespace SPHERE\Application\Setting\Consumer;

use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer as GatekeeperConsumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Setting\Consumer\Service\Data;
use SPHERE\Application\Setting\Consumer\Service\Entity\TblAccountDownloadLock;
use SPHERE\Application\Setting\Consumer\Service\Entity\TblSetting;
use SPHERE\Application\Setting\Consumer\Service\Entity\TblStudentCustody;
use SPHERE\Application\Setting\Consumer\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Setting\Consumer
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {

        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param string $Cluster
     * @param string $Application
     * @param string|null $Module
     * @param string $Identifier
     * @return false|TblSetting
     */
    public function getSetting(
        string $Cluster,
        string $Application,
        string $Module = null,
        string $Identifier = ''
    ):false|TblSetting {

        return (new Data($this->getBinding()))->getSetting(
            $Cluster, $Application, $Module, $Identifier
        );
    }

    /**
     * @param $Id
     *
     * @return false|TblSetting
     */
    public function getSettingById($Id)
    {

        return (new Data($this->getBinding()))->getSettingById($Id);
    }

    /**
     * @param bool $IsSystem
     *
     * @return false|TblSetting[]
     */
    public function getSettingAll($IsSystem = false)
    {
        return (new Data($this->getBinding()))->getSettingAll($IsSystem);
    }

    /**
     * @param $Value
     *
     * @return bool|TblType[]
     */
    public function getSchoolTypeBySettingString($Value)
    {

        $Value = str_replace(' ', '', $Value);
        $ValueList = explode(',', $Value);
        $tblSchoolTypeList = array();
        if($Value != '' && $ValueList){
            foreach ($ValueList as $ShortName){
                if(($tblType = Type::useService()->getTypeByShortName($ShortName))){
                    $tblSchoolTypeList[$tblType->getId()] = $tblType;
                }
            }
        }
        return (!empty($tblSchoolTypeList) ? $tblSchoolTypeList : false);
    }

    /**
     * @param string $Cluster
     * @param string $Application
     * @param string|null $Module
     * @param string $Identifier
     * @param string $Type
     * @param string $Value
     *
     * @return TblSetting
     */
    public function createSetting(
        string $Cluster,
        string $Application,
        string $Module = null,
        string $Identifier = '',
        string $Type = TblSetting::TYPE_BOOLEAN,
        string $Value = ''
    ):TblSetting {

        return (new Data($this->getBinding()))->createSetting(
            $Cluster, $Application, $Module, $Identifier, $Type, $Value
        );
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
        return (new Data($this->getBinding()))->updateSetting(
            $tblSetting, $value
        );
    }

    /**
     * @param TblAccount $tblAccountStudent
     *
     * @return false|TblStudentCustody[]
     */
    public function getStudentCustodyByStudent(TblAccount $tblAccountStudent)
    {

        return (new Data($this->getBinding()))->getStudentCustodyByStudent($tblAccountStudent);
    }

    /**
     * @param TblAccount $tblAccountStudent
     * @param TblAccount $tblAccountCustody
     *
     * @return false|TblStudentCustody
     */
    public function getStudentCustodyByStudentAndCustody(TblAccount $tblAccountStudent, TblAccount $tblAccountCustody)
    {

        return (new Data($this->getBinding()))->getStudentCustodyByStudentAndCustody($tblAccountStudent,
            $tblAccountCustody);
    }

    /**
     * @param TblAccount $tblAccountStudent
     * @param TblAccount $tblAccountCustody
     * @param TblAccount $tblAccountBlocker
     *
     * @return false|TblStudentCustody
     */
    public function createStudentCustody(
        TblAccount $tblAccountStudent,
        TblAccount $tblAccountCustody,
        TblAccount $tblAccountBlocker
    ) {

        return (new Data($this->getBinding()))->createStudentCustody($tblAccountStudent, $tblAccountCustody,
            $tblAccountBlocker);
    }

    /**
     * @param TblStudentCustody $tblStudentCustody
     *
     * @return bool
     */
    public function removeStudentCustody(TblStudentCustody $tblStudentCustody)
    {

        return (new Data($this->getBinding()))->removeStudentCustody($tblStudentCustody);
    }

    /**
     * @return string
     */
    public function getGermanSortBySetting()
    {
        // Setting controlled DataTable
        $IsUmlautWithE = true;
        if(($tblSetting = Consumer::useService()->getSetting('Setting', 'Consumer', 'Service', 'Sort_UmlautWithE'))){
            $IsUmlautWithE = $tblSetting->getValue();
        }
        $IsSortWithShortWords = true;
        if(($tblSetting = Consumer::useService()->getSetting('Setting', 'Consumer', 'Service', 'Sort_WithShortWords'))){
            $IsSortWithShortWords = $tblSetting->getValue();
        }
            // default
        $return = TblSetting::SORT_GERMAN_AE_WITHOUT;
        if($IsUmlautWithE && !$IsSortWithShortWords){
            // ä = ae / Sortierung ignoriert Bindewörter
            $return = TblSetting::SORT_GERMAN_AE_WITHOUT;
        } elseif($IsUmlautWithE && $IsSortWithShortWords){
            // ä = ae / Sortierung mit Bindewörter
            $return = TblSetting::SORT_GERMAN_AE_WITH;
        } elseif(!$IsUmlautWithE && !$IsSortWithShortWords) {
            // ä = a / Sortierung ignoriert Bindewörter
            $return = TblSetting::SORT_GERMAN_A_WITHOUT;
        } elseif(!$IsUmlautWithE && $IsSortWithShortWords) {
            // ä = a / Sortierung mit Bindewörter
            $return = TblSetting::SORT_GERMAN_A_WITH;
        }
        return $return;
    }

    /**
     * @param IFormInterface $form
     * @param $Data
     * @param $isSystem
     *
     * @return IFormInterface|string
     */
    public function updateSettingList(IFormInterface $form, $Data, $isSystem)
    {
        if ($Data == null) {
            return $form;
        }

        // integer validieren
        $Error = false;
        foreach ($Data as $settingId => $value) {
            if (($tblSetting = $this->getSettingById($settingId))
                && $tblSetting->getType() == TblSetting::TYPE_INTEGER
                && !preg_match('/^[0-9]*$/', $value)
            ) {
               $form->setError('Data[' . $tblSetting->getId() . ']', 'Bitte geben Sie eine Ganze Zahl (Integer) ein.');
               $Error = true;
            }
        }

        if (!$Error) {
            foreach ($Data as $settingId => $value) {
                if (($tblSetting = $this->getSettingById($settingId))) {
                    switch ($tblSetting->getType()) {
                        case TblSetting::TYPE_BOOLEAN:
                            $this->updateSetting($tblSetting, '1');
                            break;
                        case TblSetting::TYPE_INTEGER:
                        default:
                            $this->updateSetting($tblSetting, $value);
                    }
                }
            }

            // alle nicht gecheckten Checkboxen auf false setzen
            if (($tblSettingList = Consumer::useService()->getSettingAll($isSystem))) {
                foreach ($tblSettingList as $tblSetting) {
                    if (!isset($Data[$tblSetting->getId()]) && $tblSetting->getType() == TblSetting::TYPE_BOOLEAN) {
                        $this->updateSetting($tblSetting, '0');
                    }
                }
            }

            return new Success('Die Daten wurden gespeichert', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . new Redirect('/Setting/Consumer/Setting', Redirect::TIMEOUT_SUCCESS);
        }

        return $form;
    }

    /**
     * @param TblSetting $tblSetting
     * @param TblConsumer $tblConsumer
     *
     * @return string
     */
    public function getSettingByConsumer(TblSetting $tblSetting, TblConsumer $tblConsumer)
    {

        return (new Data($this->getBinding()))->getSettingByConsumer($tblSetting, $tblConsumer);
    }

    /**
     * @param TblAccount $tblAccount
     * @param \DateTime $dateTime
     * @param $identifier
     * @param $isLocked
     * @param $isLockedLastLoad
     *
     * @return TblAccountDownloadLock
     */
    public function createAccountDownloadLock(
        TblAccount $tblAccount,
        \DateTime $dateTime,
        $identifier,
        $isLocked,
        $isLockedLastLoad
    ) {
        return (new Data($this->getBinding()))->createAccountDownloadLock(
            $tblAccount,
            $dateTime,
            $identifier,
            $isLocked,
            $isLockedLastLoad
        );
    }

    /**
     * @param TblAccount $tblAccount
     * @param $identifier
     *
     * @return false|TblAccountDownloadLock
     */
    public function getAccountDownloadLock(
        TblAccount $tblAccount,
        $identifier
    ) {
        return (new Data($this->getBinding()))->getAccountDownloadLock($tblAccount, $identifier);
    }

    /**
     * @param string $Identifier
     * @param string $Value
     */
    public function createAccountSetting(
        string $Identifier,
        string $Value
    ) {
        if (($tblAccount = Account::useService()->getAccountBySession())) {
            (new Data($this->getBinding()))->createAccountSetting(
                $tblAccount,
                $Identifier,
                $Value
            );
        }
    }

    /**
     * @param string $Identifier
     *
     * @return string|false
     */
    public function getAccountSettingValue(
        string $Identifier
    ) {
        if (($tblAccount = Account::useService()->getAccountBySession())
            && ($tblAccountSetting = (new Data($this->getBinding()))->getAccountSetting($tblAccount, $Identifier))) {
            return $tblAccountSetting->getValue();
        } else {
            return false;
        }
    }

    /**
     * aktuell nicht genutzte Mandanten
     *
     * @return array
     */
    public function getConsumerBlackList(): array
    {
        $blackList = array();
        // aktuell nicht genutzte Mandanten in Sachsen
        if (GatekeeperConsumer::useService()->getConsumerTypeFromServerHost() == TblConsumer::TYPE_SACHSEN) {
            $blackList['DWO'] = 1;
            $blackList['EMSP'] = 1;
            $blackList['ESA'] = 1;
            $blackList['ESL'] = 1;
            $blackList['ESVL'] = 1;
            $blackList['EVAP'] = 1;
            $blackList['EVMS'] = 1;
            $blackList['EVMSH'] = 1;
            $blackList['EVSB'] = 1;
            $blackList['EVSL'] = 1;
            $blackList['EWM'] = 1;
            $blackList['EWS'] = 1;
            $blackList['FV'] = 1;
        }

        return $blackList;
    }

}