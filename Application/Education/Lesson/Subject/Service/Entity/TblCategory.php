<?php
namespace SPHERE\Application\Education\Lesson\Subject\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblCategory")
 * @Cache(usage="READ_ONLY")
 */
class TblCategory extends Element
{

    const ATTR_IDENTIFIER = 'Identifier';
    const ATTR_IS_LOCKED = 'IsLocked';
    const ATTR_NAME = 'Name';
    const ATTR_DESCRIPTION = 'Description';

    /**
     * @Column(type="string")
     */
    protected $Identifier;
    /**
     * @Column(type="boolean")
     */
    protected $IsLocked;
    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="string")
     */
    protected $Description;

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

        $this->Identifier = strtoupper($Identifier);
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
     * @return bool
     */
    public function isLocked()
    {

        return (bool)$this->IsLocked;
    }

    /**
     * @param bool $IsLocked
     */
    public function setLocked($IsLocked)
    {

        $this->IsLocked = (bool)$IsLocked;
    }

    /**
     * @return bool|TblSubject[]
     */
    public function getTblSubjectAll()
    {

        return Subject::useService()->getSubjectAllByCategory($this);
    }
}
