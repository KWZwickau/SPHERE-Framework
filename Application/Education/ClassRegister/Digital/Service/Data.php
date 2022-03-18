<?php

namespace SPHERE\Application\Education\ClassRegister\Digital\Service;

use DateTime;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblCourseContent;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblLessonContent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

class Data  extends AbstractData
{
    public function setupDatabaseContent()
    {

    }

    /**
     * @param $Date
     * @param $Lesson
     * @param $Content
     * @param $Homework
     * @param $Room
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     * @param TblYear|null $tblYear
     * @param TblPerson|null $tblPerson
     * @param TblSubject|null $tblSubject
     *
     * @return TblLessonContent
     */
    public function createLessonContent(
        $Date,
        $Lesson,
        $Content,
        $Homework,
        $Room,
        TblDivision $tblDivision = null,
        TblGroup $tblGroup = null,
        TblYear $tblYear = null,
        TblPerson $tblPerson = null,
        TblSubject $tblSubject = null
    ): TblLessonContent {

        $Manager = $this->getEntityManager();

        $Entity = new TblLessonContent();
        $Entity->setDate($Date ? new DateTime($Date) : null);
        $Entity->setLesson($Lesson);
        $Entity->setContent($Content);
        $Entity->setHomework($Homework);
        $Entity->setRoom($Room);
        $Entity->setServiceTblDivision($tblDivision);
        $Entity->setServiceTblGroup($tblGroup);
        $Entity->setServiceTblYear($tblYear);
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setServiceTblSubject($tblSubject);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblLessonContent $tblLessonContent
     * @param $Date
     * @param $Lesson
     * @param $Content
     * @param $Homework
     * @param $Room
     * @param TblPerson|null $tblPerson
     * @param TblSubject|null $tblSubject
     *
     * @return bool
     */
    public function updateLessonContent(
        TblLessonContent $tblLessonContent,
        $Date,
        $Lesson,
        $Content,
        $Homework,
        $Room,
        TblPerson $tblPerson = null,
        TblSubject $tblSubject = null
    ): bool {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblLessonContent $Entity */
        $Entity = $Manager->getEntityById('TblLessonContent', $tblLessonContent->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setDate($Date ? new DateTime($Date) : null);
            $Entity->setLesson($Lesson);
            $Entity->setContent($Content);
            $Entity->setHomework($Homework);
            $Entity->setRoom($Room);
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setServiceTblSubject($tblSubject);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblLessonContent $tblLessonContent
     *
     * @return bool
     */
    public function destroyLessonContent(TblLessonContent $tblLessonContent): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblLessonContent $Entity */
        $Entity = $Manager->getEntityById('TblLessonContent', $tblLessonContent->getId());
        if (null !== $Entity) {
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);

            return true;
        }

        return false;
    }
    
    /**
     * @param $Id
     *
     * @return false|TblLessonContent
     */
    public function getLessonContentById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblLessonContent', $Id);
    }

    /**
     * @param DateTime $date
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     *
     * @return false|TblLessonContent[]
     */
    public function getLessonContentAllByDate(DateTime $date, TblDivision $tblDivision = null, TblGroup $tblGroup = null)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblLessonContent', array(
            TblLessonContent::ATTR_DATE => $date,
            TblLessonContent::ATTR_SERVICE_TBL_DIVISION => $tblDivision ? $tblDivision->getId() : null,
            TblLessonContent::ATTR_SERVICE_TBL_GROUP => $tblGroup ? $tblGroup->getId() : null
        ), array(TblLessonContent::ATTR_LESSON => self::ORDER_ASC) );
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup $tblSubjectGroup
     * @param $Date
     * @param $Lesson
     * @param $Content
     * @param $Homework
     * @param $Room
     * @param $IsDoubleLesson
     * @param TblPerson|null $tblPerson
     *
     * @return TblCourseContent
     */
    public function createCourseContent(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup,
        $Date,
        $Lesson,
        $Content,
        $Homework,
        $Room,
        $IsDoubleLesson,
        TblPerson $tblPerson = null
    ): TblCourseContent {

        $Manager = $this->getEntityManager();

        $Entity = new TblCourseContent();
        $Entity->setServiceTblDivision($tblDivision);
        $Entity->setServiceTblSubject($tblSubject);
        $Entity->setServiceTblSubjectGroup($tblSubjectGroup);
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setDate($Date ? new DateTime($Date) : null);
        $Entity->setLesson($Lesson);
        $Entity->setContent($Content);
        $Entity->setHomework($Homework);
        $Entity->setRoom($Room);
        $Entity->setIsDoubleLesson($IsDoubleLesson);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblCourseContent $tblCourseContent
     * @param $Date
     * @param $Lesson
     * @param $Content
     * @param $Homework
     * @param $Room
     * @param $IsDoubleLesson
     * @param TblPerson|null $tblPerson
     *
     * @return bool
     */
    public function updateCourseContent(
        TblCourseContent $tblCourseContent,
        $Date,
        $Lesson,
        $Content,
        $Homework,
        $Room,
        $IsDoubleLesson,
        TblPerson $tblPerson = null
    ): bool {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCourseContent $Entity */
        $Entity = $Manager->getEntityById('TblCourseContent', $tblCourseContent->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setDate($Date ? new DateTime($Date) : null);
            $Entity->setLesson($Lesson);
            $Entity->setContent($Content);
            $Entity->setHomework($Homework);
            $Entity->setRoom($Room);
            $Entity->setIsDoubleLesson($IsDoubleLesson);
            $Entity->setServiceTblPerson($tblPerson);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblCourseContent $tblCourseContent
     *
     * @return bool
     */
    public function destroyCourseContent(TblCourseContent $tblCourseContent): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCourseContent $Entity */
        $Entity = $Manager->getEntityById('TblCourseContent', $tblCourseContent->getId());
        if (null !== $Entity) {
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param $Id
     *
     * @return false|TblCourseContent
     */
    public function getCourseContentById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblCourseContent', $Id);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup $tblSubjectGroup
     *
     * @return false|TblCourseContent[]
     */
    public function getCourseContentListBy(TblDivision $tblDivision, TblSubject $tblSubject,TblSubjectGroup $tblSubjectGroup)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblCourseContent', array(
            TblCourseContent::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
            TblCourseContent::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
            TblCourseContent::ATTR_SERVICE_TBL_SUBJECT_GROUP => $tblSubjectGroup->getId()
        ));
    }
}