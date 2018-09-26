<?php
namespace SPHERE\Application\Setting\Consumer;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Setting\Consumer\Service\Data;
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
     * @param bool $Simulate
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($Simulate, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($Simulate);
        if (!$Simulate && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
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
}