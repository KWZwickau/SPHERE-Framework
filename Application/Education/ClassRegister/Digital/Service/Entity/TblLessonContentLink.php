<?php

namespace SPHERE\Application\Education\ClassRegister\Digital\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblClassRegisterLessonContentLink")
 * @Cache(usage="READ_ONLY")
 */
class TblLessonContentLink extends Element
{
    const ATTR_TBL_LESSON_CONTENT = 'tblClassRegisterLessonContent';
    const ATTR_TBL_COURSE_CONTENT = 'tblClassRegisterCourseContent';
    const ATTR_TBL_LINK_ID = 'LinkId';

    /**
     * @Column(type="bigint")
     */
    protected $LinkId;
    /**
     * @Column(type="bigint")
     */
    protected $tblClassRegisterLessonContent;
    /**
     * @Column(type="bigint")
     */
    protected $tblClassRegisterCourseContent;

    /**
     * @return bool|TblLessonContent
     */
    public function getTblLessonContent()
    {
        if (null === $this->tblClassRegisterLessonContent) {
            return false;
        } else {
            return Digital::useService()->getLessonContentById($this->tblClassRegisterLessonContent);
        }
    }

    /**
     * @param TblLessonContent|null $tblLessonContent
     */
    public function setTblLessonContent(?TblLessonContent $tblLessonContent)
    {
        $this->tblClassRegisterLessonContent = ( null === $tblLessonContent ? null : $tblLessonContent->getId() );
    }

    /**
     * @return bool|TblCourseContent
     */
    public function getTblCourseContent(): bool|TblCourseContent
    {
        if (null === $this->tblClassRegisterCourseContent) {
            return false;
        } else {
            return Digital::useService()->getCourseContentById($this->tblClassRegisterCourseContent);
        }
    }

    /**
     * @param TblCourseContent|null $tblCourseContent
     */
    public function setTblCourseContent(?TblCourseContent $tblCourseContent): void
    {
        $this->tblClassRegisterCourseContent = ( null === $tblCourseContent ? null : $tblCourseContent->getId() );
    }

    /**
     * @return int
     */
    public function getLinkId(): int
    {
        return $this->LinkId;
    }

    /**
     * @param int $LinkId
     */
    public function setLinkId(int $LinkId)
    {
        $this->LinkId = $LinkId;
    }
}