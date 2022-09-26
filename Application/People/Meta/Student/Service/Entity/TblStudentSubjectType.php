<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblStudentSubjectType")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentSubjectType extends Element
{

    const ATTR_IDENTIFIER = 'Identifier';
    const ATTR_NAME = 'Name';

    const TYPE_ORIENTATION = 'ORIENTATION';
    const TYPE_ADVANCED = 'ADVANCED';
    const TYPE_PROFILE = 'PROFILE';
    const TYPE_RELIGION = 'RELIGION';
    const TYPE_FOREIGN_LANGUAGE = 'FOREIGN_LANGUAGE';
    const TYPE_ELECTIVE = 'ELECTIVE';
    const TYPE_TEAM = 'TEAM';
    const TYPE_TRACK_INTENSIVE = 'TRACK_INTENSIVE';
    const TYPE_TRACK_BASIC = 'TRACK_BASIC';

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
