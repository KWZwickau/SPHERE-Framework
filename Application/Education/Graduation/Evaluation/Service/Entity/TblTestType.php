<?php
namespace SPHERE\Application\Education\Graduation\Evaluation\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblTestType")
 * @Cache(usage="READ_ONLY")
 */
class TblTestType extends Element
{

    const TEST = 'TEST';
    const BEHAVIOR = 'BEHAVIOR';
    const APPOINTED_DATE_TASK = 'APPOINTED_DATE_TASK';
    const BEHAVIOR_TASK = 'BEHAVIOR_TASK';

    const ATTR_NAME = 'Name';
    const ATTR_IDENTIFIER = 'Identifier';

    /**
     * @Column(type="string")
     */
    protected $Name;

    /**
     * @Column(type="string")
     */
    protected $Identifier;

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
    public function getIdentifier()
    {

        return $this->Identifier;
    }

    /**
     * @param string $Identifier
     */
    public function setIdentifier($Identifier)
    {

        $this->Identifier = $Identifier;
    }
}
