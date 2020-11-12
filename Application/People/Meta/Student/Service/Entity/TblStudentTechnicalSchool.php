<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Course\Service\Entity\TblSchoolDiploma;
use SPHERE\Application\Education\School\Course\Service\Entity\TblTechnicalCourse;
use SPHERE\Application\Education\School\Course\Service\Entity\TblTechnicalDiploma;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblStudentTechnicalSchool")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentTechnicalSchool extends Element
{
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblTechnicalCourse;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSchoolDiploma;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblTechnicalDiploma;
    /**
     * @Column(type="string")
     */
    protected $PraxisLessons;
    /**
     * @Column(type="string")
     */
    protected $DurationOfTraining;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentTenseOfLesson;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentTrainingStatus;

    /**
     * @return bool|TblTechnicalCourse
     */
    public function getServiceTblTechnicalCourse()
    {
        if (null === $this->serviceTblTechnicalCourse) {
            return false;
        } else {
            return Course::useService()->getTechnicalCourseById($this->serviceTblTechnicalCourse);
        }
    }

    /**
     * @param TblTechnicalCourse|null $tblTechnicalCourse
     */
    public function setServiceTblTechnicalCourse(TblTechnicalCourse $tblTechnicalCourse = null)
    {
        $this->serviceTblTechnicalCourse = ( null === $tblTechnicalCourse ? null : $tblTechnicalCourse->getId() );
    }

    /**
     * @return bool|TblSchoolDiploma
     */
    public function getServiceTblSchoolDiploma()
    {
        if (null === $this->serviceTblSchoolDiploma) {
            return false;
        } else {
            return Course::useService()->getSchoolDiplomaById($this->serviceTblSchoolDiploma);
        }
    }

    /**
     * @param TblSchoolDiploma|null $tblSchoolDiploma
     */
    public function setServiceTblSchoolDiploma(TblSchoolDiploma $tblSchoolDiploma = null)
    {
        $this->serviceTblSchoolDiploma = ( null === $tblSchoolDiploma ? null : $tblSchoolDiploma->getId() );
    }

    /**
     * @return bool|TblTechnicalDiploma
     */
    public function getServiceTblTechnicalDiploma()
    {
        if (null === $this->serviceTblTechnicalDiploma) {
            return false;
        } else {
            return Course::useService()->getTechnicalDiplomaById($this->serviceTblTechnicalDiploma);
        }
    }

    /**
     * @param TblTechnicalDiploma|null $tblTechnicalDiploma
     */
    public function setServiceTblTechnicalDiploma(TblTechnicalDiploma $tblTechnicalDiploma = null)
    {
        $this->serviceTblTechnicalDiploma = ( null === $tblTechnicalDiploma ? null : $tblTechnicalDiploma->getId() );
    }

    /**
     * @return string
     */
    public function getPraxisLessons()
    {
        return $this->PraxisLessons;
    }

    /**
     * @param string $PraxisLessons
     */
    public function setPraxisLessons($PraxisLessons)
    {
        $this->PraxisLessons = $PraxisLessons;
    }

    /**
     * @return string
     */
    public function getDurationOfTraining()
    {
        return $this->DurationOfTraining;
    }

    /**
     * @param string $DurationOfTraining
     */
    public function setDurationOfTraining($DurationOfTraining)
    {
        $this->DurationOfTraining = $DurationOfTraining;
    }

    /**
     * @return bool|TblStudentTenseOfLesson
     */
    public function getTblStudentTenseOfLesson()
    {
        if (null === $this->tblStudentTenseOfLesson) {
            return false;
        } else {
            return Student::useService()->getStudentTenseOfLessonById($this->tblStudentTenseOfLesson);
        }
    }

    /**
     * @param null|TblStudentTenseOfLesson $tblStudentTenseOfLesson
     */
    public function setTblStudentTenseOfLesson(TblStudentTenseOfLesson $tblStudentTenseOfLesson = null)
    {
        $this->tblStudentTenseOfLesson = ( null === $tblStudentTenseOfLesson ? null : $tblStudentTenseOfLesson->getId() );
    }

    /**
     * @return bool|TblStudentTrainingStatus
     */
    public function getTblStudentTrainingStatus()
    {
        if (null === $this->tblStudentTrainingStatus) {
            return false;
        } else {
            return Student::useService()->getStudentTrainingStatusById($this->tblStudentTrainingStatus);
        }
    }

    /**
     * @param null|TblStudentTrainingStatus $tblStudentTrainingStatus
     */
    public function setTblStudentTrainingStatus(TblStudentTrainingStatus $tblStudentTrainingStatus = null)
    {
        $this->tblStudentTrainingStatus = ( null === $tblStudentTrainingStatus ? null : $tblStudentTrainingStatus->getId() );
    }
}