<?php
namespace SPHERE\Application\Billing\Inventory\Item\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Setting\Service\Entity\TblSetting;
use SPHERE\Application\Billing\Inventory\Setting\Setting;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblItem")
 * @Cache(usage="READ_ONLY")
 */
class TblItem extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_IS_ACTIVE = 'IsActive';

    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="text")
     */
    protected $Description;
    /**
     * @Column(type="bigint")
     */
    protected $tblItemType;
    /**
     * @Column(type="string")
     */
    protected $SepaRemark;
    /**
     * @Column(type="string")
     */
    protected $DatevRemark;
    /**
     * @Column(type="string")
     */
    protected $FibuAccount;
    /**
     * @Column(type="string")
     */
    protected $FibuToAccount;
    /**
     * @Column(type="string")
     */
    protected $Kost1;
    /**
     * @Column(type="string")
     */
    protected $Kost2;
    /**
     * @Column(type="string")
     */
    protected $BuKey;
    /**
     * @Column(type="boolean")
     */
    protected $IsActive;

    /**
     * @return string
     */
    public function getName()
    {

        return $this->Name;
    }

    /**
     * @param string $Name
     */
    public function setName($Name)
    {

        $this->Name = $Name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {

        return $this->Description;
    }

    /**
     * @param string $Description
     */
    public function setDescription($Description)
    {

        $this->Description = $Description;
    }

    /**
     * @return bool|TblItem
     */
    public function getTblItemType()
    {

        if(null === $this->tblItemType){
            return false;
        } else {
            return Item::useService()->getItemTypeById($this->tblItemType);
        }
    }

    /**
     * @param TblItemType $tblItemType
     */
    public function setTblItemType(TblItemType $tblItemType)
    {

        $this->tblItemType = (null === $tblItemType ? null : $tblItemType->getId());
    }

    /**
     * @return string
     */
    public function getDisplayDescription()
    {
        return nl2br($this->getDescription());
    }

    /**
     * @param bool $ignoreDefault used for POST in Frontend
     *
     * @return string
     */
    public function getSepaRemark($ignoreDefault = false)
    {

        if($ignoreDefault){
            return $this->SepaRemark;
        }

        $BookingText = $this->getName();
        if($this->SepaRemark){
            return $this->SepaRemark;
        } else {
            if(($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_SEPA_REMARK))){
                if($tblSetting->getValue() !== ''){
                    $BookingText = $tblSetting->getValue();
                }
            }
        }
        return $BookingText;
    }

    /**
     * @param string $SepaRemark
     */
    public function setSepaRemark($SepaRemark = '')
    {
        $this->SepaRemark = $SepaRemark;
    }

    /**
     * @param bool $ignoreDefault used for POST in Frontend
     *
     * @return string
     */
    public function getDatevRemark($ignoreDefault = false)
    {

        if($ignoreDefault){
            return $this->DatevRemark;
        }

        $BookingText = $this->getName();
        if($this->DatevRemark){
            return $this->DatevRemark;
        } else {
            if(($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_DATEV_REMARK))){
                if($tblSetting->getValue() !== ''){
                    $BookingText = $tblSetting->getValue();
                }
            }
        }

        return $BookingText;
    }

    /**
     * @param string $DatevRemark
     */
    public function setDatevRemark($DatevRemark = '')
    {
        $this->DatevRemark = $DatevRemark;
    }

    /**
     * @param bool $ignoreDefault
     *
     * @return string
     */
    public function getFibuAccount($ignoreDefault = false)
    {

        // Ohne individuelle Einstellung, wird versucht, die Grundeinstellung zu ziehen
        if('' === $this->FibuAccount && !$ignoreDefault){
            if(($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_FIBU_ACCOUNT))){
                return $tblSetting->getValue();
            }
        }
        return $this->FibuAccount;
    }

    /**
     * @param string $FibuAccount
     */
    public function setFibuAccount($FibuAccount = '')
    {
        $this->FibuAccount = $FibuAccount;
    }

    /**
     * @param bool $ignoreDefault
     *
     * @return string
     */
    public function getFibuToAccount($ignoreDefault = false)
    {

        // Ohne individuelle Einstellung, wird versucht, die Grundeinstellung zu ziehen
        if('' === $this->FibuToAccount && !$ignoreDefault){
            if(($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_FIBU_TO_ACCOUNT))){
                return $tblSetting->getValue();
            }
        }
        return $this->FibuToAccount;
    }

    /**
     * @param string $FibuToAccount
     */
    public function setFibuToAccount($FibuToAccount = '')
    {
        $this->FibuToAccount = $FibuToAccount;
    }

    /**
     * @param bool $ignoreDefault
     *
     * @return string
     */
    public function getKost1($ignoreDefault = false)
    {

        // Ohne individuelle Einstellung, wird versucht, die Grundeinstellung zu ziehen
        if('' === $this->Kost1 && !$ignoreDefault){
            if(($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_KOST_1))){
                return $tblSetting->getValue();
            }
        }
        return $this->Kost1;
    }

    /**
     * @param string $Kost1
     */
    public function setKost1($Kost1 = '')
    {
        $this->Kost1 = $Kost1;
    }

    /**
     * @param bool $ignoreDefault
     *
     * @return string
     */
    public function getKost2($ignoreDefault = false)
    {

        // Ohne individuelle Einstellung, wird versucht, die Grundeinstellung zu ziehen
        if('' === $this->Kost2 && !$ignoreDefault){
            if(($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_KOST_2))){
                return $tblSetting->getValue();
            }
        }
        return $this->Kost2;
    }

    /**
     * @param string $Kost2
     */
    public function setKost2($Kost2 = '')
    {
        $this->Kost2 = $Kost2;
    }

    /**
     * @param bool $ignoreDefault
     *
     * @return string
     */
    public function getBuKey($ignoreDefault = false)
    {

        // Ohne individuelle Einstellung, wird versucht, die Grundeinstellung zu ziehen
        if('' === $this->BuKey && !$ignoreDefault){
            if(($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_KOST_2))){
                return $tblSetting->getValue();
            }
        }
        return $this->BuKey;
    }

    /**
     * @param string $BuKey
     */
    public function setBuKey($BuKey = '')
    {
        $this->BuKey = $BuKey;
    }

    /**
     * @param bool $ignoreDefault
     *
     * @return string
     */
    public function getIsActive()
    {

        return $this->IsActive;
    }

    /**
     * @param string $IsActive
     */
    public function setIsActive($IsActive = false)
    {
        $this->IsActive = $IsActive;
    }
}
