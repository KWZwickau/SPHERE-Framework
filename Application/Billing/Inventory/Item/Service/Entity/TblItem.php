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
     * @return string
     */
    public function getSepaRemark()
    {

        if($this->SepaRemark){
            return $this->SepaRemark;
        }
        return $this->getName();
    }

    /**
     * @param string $SepaRemark
     */
    public function setSepaRemark($SepaRemark = '')
    {
        $this->SepaRemark = $SepaRemark;
    }

    /**
     * @return string
     */
    public function getDatevRemark()
    {

        if($this->DatevRemark){
            return $this->DatevRemark;
        }
        return $this->getName();
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
}
