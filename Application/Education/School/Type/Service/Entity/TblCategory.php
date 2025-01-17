<?php
namespace SPHERE\Application\Education\School\Type\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblCategory")
 * @Cache(usage="READ_ONLY")
 */
class TblCategory extends Element
{
    const ATTR_IDENTIFIER = 'Identifier';
    const ATTR_NAME = 'Name';
    const PRE_SCHOOL = 'PRE_SCHOOL';
    const COMMON = 'COMMON';
    const TECHNICAL = 'TECHNICAL';
    const SECOND_COURSE = 'SECOND_COURSE';

    /**
     * @Column(type="string")
     */
    protected $Identifier;
    /**
     * @Column(type="string")
     */
    protected $Name;

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