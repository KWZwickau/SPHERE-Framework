<?php

namespace SPHERE\Application\Reporting\Individual\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Reporting\Individual\Individual;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblPresetSetting")
 * @Cache(usage="READ_ONLY")
 */
class TblPresetSetting extends Element
{

    const ATTR_TBL_PRESET = 'tblPreset';
    const ATTR_FIELD = 'Field';
    const ATTR_VIEW = 'View';
    const ATTR_VIEW_TYPE = 'ViewType';
    const ATTR_POSITION = 'Position';

    /**
     * @Column(type="bigint")
     */
    protected $tblPreset;
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
    public function setTblPreset(TblPreset $tblPreset)
    {
        $this->tblPreset = (null === $tblPreset ? null : $tblPreset->getId());
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
}