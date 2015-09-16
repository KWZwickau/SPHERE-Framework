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
 * @Table(name="tblCategorySubject")
 * @Cache(usage="READ_ONLY")
 */
class TblCategorySubject extends Element
{

    const ATTR_TBL_CATEGORY = 'tblCategory';
    const ATTR_TBL_SUBJECT = 'tblSubject';

    /**
     * @Column(type="bigint")
     */
    protected $tblCategory;
    /**
     * @Column(type="bigint")
     */
    protected $tblSubject;

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

    /**
     * @return bool|TblSubject
     */
    public function getTblSubject()
    {

        if (null === $this->tblSubject) {
            return false;
        } else {
            return Subject::useService()->getSubjectById($this->tblSubject);
        }
    }

    /**
     * @param null|TblSubject $tblSubject
     */
    public function setTblSubject(TblSubject $tblSubject = null)
    {

        $this->tblSubject = ( null === $tblSubject ? null : $tblSubject->getId() );
    }
}
