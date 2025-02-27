<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse;

use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Binding\AbstractService;

abstract class ServiceCourseSystem extends AbstractService
{
    /**
     * @param TblPerson $tblPerson
     * @param int|null $studentLevel
     *
     * @return array[]
     */
    public function getCoursesForStudent(TblPerson $tblPerson, ?int $studentLevel = null): array
    {
        $advancedCourses = array();
        $basicCourses = array();

        $levelList = array();
        if (($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListByPerson($tblPerson))) {
            foreach ($tblStudentEducationList as $tblStudentEducation) {
                if (DivisionCourse::useService()->getIsCourseSystemByStudentEducation($tblStudentEducation)
                    && ($tblYear = $tblStudentEducation->getServiceTblYear())
                    && ($tblStudentSubjectList = DivisionCourse::useService()->getStudentSubjectListByPersonAndYear($tblPerson, $tblYear, true))
                    && ($level = $tblStudentEducation->getLevel())
                    && (!$studentLevel || $level == $studentLevel)
                ) {
                    // Schuljahreswiederholungen ignorieren
                    if (!isset($levelList[$level])) {
                        $levelList[$level] = 1;
                        foreach ($tblStudentSubjectList as $tblStudentSubject) {
                            if (($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
                                // SSW-2351 Fehlerbehebung Kurshalbjahreszeugnisse GY
//                            if ($tblSubject->getAcronym() == 'EN2') {
//                                $tblSubject = Subject::useService()->getSubjectByAcronym('EN');
//                            }
                                if ($tblSubject) {
                                    if ($tblStudentSubject->getIsAdvancedCourse()) {
                                        $advancedCourses[$tblSubject->getId()] = $tblSubject;
                                    } else {
                                        $basicCourses[$tblSubject->getId()] = $tblSubject;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return array($advancedCourses, $basicCourses);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     *
     * @return array
     */
    public function getAdvancedCoursesForStudent(TblPerson $tblPerson, TblYear $tblYear): array
    {
        $advancedCourses = array();
        if (($tblStudentSubjectList = DivisionCourse::useService()->getStudentSubjectListByPersonAndYear($tblPerson, $tblYear, true))) {
            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                if (($tblSubject = $tblStudentSubject->getServiceTblSubject())
                    && $tblStudentSubject->getIsAdvancedCourse()
                ) {
                    $advancedCourses[$tblSubject->getId()] = $tblSubject;
                }
            }
        }

        return $advancedCourses;
    }
}