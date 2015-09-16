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
 * @Table(name="tblGroupCategory")
 * @Cache(usage="READ_ONLY")
 */
class TblGroupCategory extends Element
{

    const ATTR_TBL_GROUP = 'tblGroup';
    const ATTR_TBL_CATEGORY = 'tblCategory';

    /**
     * @Column(type="bigint")
     */
    protected $tblGroup;
    /**
     * @Column(type="bigint")
     */
    protected $tblCategory;

    /**
     * @return bool|TblGroup
     */
    public function getTblGroup()
    {

        if (null === $this->tblGroup) {
            return false;
        } else {
            return Subject::useService()->getGroupById($this->tblGroup);
        }
    }

    /**
     * @param null|TblGroup $tblGroup
     */
    public function setTblGroup(TblGroup $tblGroup = null)
    {

        $this->tblGroup = ( null === $tblGroup ? null : $tblGroup->getId() );
    }

    /**
     * @return bool|TblCategory
     */
    public function getTblCategory()
    {

        if (null === $this->tblCategory) {
            return false;
        } else {
            return Subject::useService()->getCategoryById($this->tblCategory);
        }
    }

    /**
     * @param null|TblCategory $tblCategory
     */
    public function setTblCategory(TblCategory $tblCategory = null)
    {

        $this->tblCategory = ( null === $tblCategory ? null : $tblCategory->getId() );
    }
}
