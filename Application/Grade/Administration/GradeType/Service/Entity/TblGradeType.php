<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 02.11.2015
 * Time: 10:32
 */

namespace SPHERE\Application\Grade\Administration\GradeType\Service\Entity;


use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblScoreType")
 * @Cache(usage="NONSTRICT_READ_WRITE")
 */
class TblGradeType extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_CODE = 'Code';

    /**
     * @Column(type="string")
     */
    protected $Code;

    /**
     * @Column(type="string")
     */
    protected $Name;

    /**
     * @Column(type="string")
     */
    protected $Description;

    /**
     * @Column(type="boolean")
     */
    protected $IsActive;

    /**
     * @Column(type="boolean")
     */
    protected $IsHighlighted;

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->Code;
    }

    /**
     * @param string $Code
     */
    public function setCode($Code)
    {
        $this->Code = $Code;
    }

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
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->IsActive;
    }

    /**
     * @param boolean $IsActive
     */
    public function setIsActive($IsActive)
    {
        $this->IsActive = $IsActive;
    }

    /**
     * @return boolean
     */
    public function getIsHighlighted()
    {
        return $this->IsHighlighted;
    }

    /**
     * @param boolean $IsHighlighted
     */
    public function setIsHighlighted($IsHighlighted)
    {
        $this->IsHighlighted = $IsHighlighted;
    }
}