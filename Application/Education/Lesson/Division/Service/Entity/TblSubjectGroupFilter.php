<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 11.06.2018
 * Time: 13:53
 */

namespace SPHERE\Application\Education\Lesson\Division\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblSubjectGroupFilter")
 * @Cache(usage="READ_ONLY")
 */
class TblSubjectGroupFilter extends Element
{

    const ATTR_TBL_SUBJECT_GROUP = 'tblSubjectGroup';
    const ATTR_FIELD = 'Field';

    /**
     * @Column(type="bigint")
     */
    protected $tblSubjectGroup;

    /**
     * @Column(type="string")
     */
    protected $Field;

    /**
     * @Column(type="string")
     */
    protected $Value;

    /**
     * @return bool|TblSubjectGroup
     */
    public function getTblSubjectGroup()
    {

        if (null === $this->tblSubjectGroup) {
            return false;
        } else {
            return Division::useService()->getSubjectGroupById($this->tblSubjectGroup);
        }
    }

    /**
     * @param null|TblSubjectGroup $tblSubjectGroup
     */
    public function setTblSubjectGroup(TblSubjectGroup $tblSubjectGroup = null)
    {

        $this->tblSubjectGroup = ( null === $tblSubjectGroup ? null : $tblSubjectGroup->getId() );
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->Value;
    }

    /**
     * @param string $Value
     */
    public function setValue($Value)
    {
        $this->Value = $Value;
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
}