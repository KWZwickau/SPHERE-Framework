<?php
namespace SPHERE\Application\Education\Graduation\Gradebook\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblGradeType")
 * @Cache(usage="READ_ONLY")
 */
class TblGradeType extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_CODE = 'Code';
    const ATTR_SERVICE_TBL_TEST_TYPE = 'serviceTblTestType';

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
    protected $IsHighlighted;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblTestType;

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
    public function isHighlighted()
    {

        return (bool)$this->IsHighlighted;
    }

    /**
     * @param boolean $IsHighlighted
     */
    public function setHighlighted($IsHighlighted)
    {

        $this->IsHighlighted = (bool)$IsHighlighted;
    }

    /**
     * @return bool|TblTestType
     */
    public function getServiceTblTestType()
    {

        if (null === $this->serviceTblTestType) {
            return false;
        } else {
            return Evaluation::useService()->getTestTypeById($this->serviceTblTestType);
        }
    }

    /**
     * @param TblTestType|null $serviceTblTestType
     */
    public function setServiceTblTestType($serviceTblTestType)
    {

        $this->serviceTblTestType = ( null === $serviceTblTestType ? null : $serviceTblTestType->getId() );
    }
}
