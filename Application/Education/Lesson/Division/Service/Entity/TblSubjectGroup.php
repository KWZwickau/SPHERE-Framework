<?php
namespace SPHERE\Application\Education\Lesson\Division\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * e.g. Info I, Info II
 *
 * @Entity
 * @Table(name="tblSubjectGroup")
 * @Cache(usage="READ_ONLY")
 */
class TblSubjectGroup extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_DESCRIPTION = 'Description';
    const ATTR_IS_ADVANCED_COURSE = 'IsAdvancedCourse';

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
    protected $IsAdvancedCourse;

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
    public function isAdvancedCourse()
    {
        return (boolean) $this->IsAdvancedCourse;
    }

    /**
     * @param boolean $IsAdvancedCourse
     */
    public function setIsAdvancedCourse($IsAdvancedCourse)
    {
        $this->IsAdvancedCourse = $IsAdvancedCourse;
    }
}
