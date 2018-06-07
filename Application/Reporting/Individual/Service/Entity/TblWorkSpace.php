<?php

namespace SPHERE\Application\Reporting\Individual\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Reporting\Individual\Individual;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblWorkSpace")
 * @Cache(usage="READ_ONLY")
 */
class TblWorkSpace extends Element
{

    const VIEW_TYPE_ALL = 'All';
    const VIEW_TYPE_STUDENT = 'Student';
    const VIEW_TYPE_PROSPECT = 'Prospect';
    const VIEW_TYPE_CUSTODY = 'Custody';
    const VIEW_TYPE_TEACHER = 'Teacher';

    const ATTR_TBL_PRESET = 'tblPreset';
    const ATTR_SERVICE_TBL_ACCOUNT = 'serviceTblAccount';
    const ATTR_FIELD = 'Field';
    const ATTR_VIEW = 'View';
    const ATTR_VIEW_TYPE = 'ViewType';
    const ATTR_POSITION = 'Position';
    const ATTR_FIELD_COUNT = 'FieldCount';

    /**
     * @Column(type="bigint")
     */
    protected $tblPreset;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblAccount;
    /**
     * @Column(type="string")
     */
    protected $Field;
    /**
     * @Column(type="string")
     */
    protected $View;
    /**
     * @Column(type="string")
     */
    protected $ViewType;
    /**
     * @Column(type="integer")
     */
    protected $Position;
    /**
     * @Column(type="integer")
     */
    protected $FieldCount;
    /**
     * @Column(type="boolean")
     */
    protected $IsExpanded;

    /**
     * @return bool|TblPreset
     */
    public function getTblPreset()
    {
        if (null === $this->tblPreset) {
            return false;
        } else {
            return Individual::useService()->getPresetById($this->tblPreset);
        }
    }

    /**
     * @param TblPreset $tblPreset
     */
    public function setTblPreset(TblPreset $tblPreset = null)
    {
        $this->tblPreset = (null === $tblPreset ? null : $tblPreset->getId());
    }

    /**
     * @return bool|TblAccount
     */
    public function getServiceTblAccount()
    {

        if (null === $this->serviceTblAccount) {
            return false;
        } else {
            return Account::useService()->getAccountById($this->serviceTblAccount);
        }
    }

    /**
     * @param null|TblAccount $serviceTblAccount
     */
    public function setServiceTblAccount(TblAccount $serviceTblAccount = null)
    {

        $this->serviceTblAccount = (null === $serviceTblAccount ? null : $serviceTblAccount->getId());
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->Field;
    }

    /**
     * @param string $Field
     */
    public function setField($Field)
    {
        $this->Field = $Field;
    }

    /**
     * @return string
     */
    public function getView()
    {
        return $this->View;
    }

    /**
     * @param string $View
     */
    public function setView($View)
    {
        $this->View = $View;
    }

    /**
     * @return string
     */
    public function getViewType()
    {
        return $this->ViewType;
    }

    /**
     * @param string $ViewType
     */
    public function setViewType($ViewType)
    {
        $this->ViewType = $ViewType;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->Position;
    }

    /**
     * @param int $Position
     */
    public function setPosition($Position)
    {
        $this->Position = $Position;
    }

    /**
     * @return int
     */
    public function getFieldCount()
    {
        return $this->FieldCount;
    }

    /**
     * @param int $FieldCount
     */
    public function setFieldCount($FieldCount)
    {
        $this->FieldCount = $FieldCount;
    }

    /**
     * @return bool
     */
    public function isExpanded()
    {
        return (bool)$this->IsExpanded;
    }

    /**
     * @param bool $isExpanded
     */
    public function setExpanded($isExpanded)
    {
        $this->IsExpanded = (bool)$isExpanded;
    }
}
