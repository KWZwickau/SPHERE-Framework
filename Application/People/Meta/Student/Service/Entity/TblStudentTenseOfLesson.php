<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblStudentTenseOfLesson")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentTenseOfLesson extends Element
{
    const ATTR_IDENTIFIER = 'Identifier';
    const ATTR_NAME = 'Name';
    const FULL_TIME = 'FULL_TIME';
    const PART_TIME = 'PART_TIME';

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

    /**
     * @return string
     */
    public function getCertificateName()
    {
        if ($this->getIdentifier() == self::FULL_TIME) {
            return 'Vollzeitform';
        } elseif ($this->getIdentifier() == self::PART_TIME) {
            return  'Teilzeitform';
        } else {
            return $this->getName();
        }
    }
}