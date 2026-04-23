<?php

namespace SPHERE\Application\Education\Competence\SkillRate;

use DateTime;
use SPHERE\Application\Api\Document\Storage\ApiPersonPicture;
use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMember;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class FrontendDivisionCourse extends Extension implements IFrontendInterface
{
    /**
     * @param $SelectedYearId
     * @param $DivisionCourseId
     * @param $SubjectId
     *
     * @return Stage
     *
     * @noinspection PhpUnused
     */
    public function frontendDivisionCourse($SelectedYearId = null, $DivisionCourseId = null, $SubjectId = null): Stage
    {
        $textCourse = "";
        $textSubject = "";
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblSubject = Subject::useService()->getSubjectById($SubjectId))
        ) {
            $textCourse = new Bold($tblDivisionCourse->getDisplayName());
            $textSubject = new Bold($tblSubject->getDisplayName());
        }

        $stage = new Stage();
        $stage->setContent(
            new Title(
                new Standard("Zurück", "/Education/Competence/SkillRate", new ChevronLeft(), ['SelectedYearId' => $SelectedYearId])
                . "&nbsp;&nbsp;&nbsp;Kompetenzbewertung"
                . new Muted(new Small(" für Kurs: ")) . $textCourse
                . new Muted(new Small(" im Fach: ")) . $textSubject
            )
            . ApiSupportReadOnly::receiverOverViewModal()
            . ApiPersonPicture::receiverModal()
            . $this->loadDivisionCourseContent($DivisionCourseId, $SubjectId, $SelectedYearId)
        );

        return $stage;
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $SelectedYearId
     * @param bool $ShowInActive
     *
     * @return string
     */
    public function loadDivisionCourseContent($DivisionCourseId, $SubjectId, $SelectedYearId, bool $ShowInActive = false): string
    {
        $role = SkillRate::useService()->getRole();
        $isEdit = Grade::useService()->getIsEdit($DivisionCourseId, $SubjectId, $role);
        $isCheckTeacherLectureship = $isEdit && ($role == 'Teacher');

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblSubject = Subject::useService()->getSubjectById($SubjectId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $inactiveStudentList = array();
            if ($ShowInActive) {
                $tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses(true, true, new DateTime('today'));
                if (($tblDivisionCourseMemberList = $tblDivisionCourse->getStudentsWithSubCourses(true, false))) {
                    /** @var TblDivisionCourseMember $tblDivisionCourseMember */
                    foreach ($tblDivisionCourseMemberList as $tblDivisionCourseMember) {
                        if ($tblDivisionCourseMember->isInActive() && ($tblPersonTemp = $tblDivisionCourseMember->getServiceTblPerson())) {
                            $inactiveStudentList[$tblPersonTemp->getId()] = $tblPersonTemp;
                        }
                    }
                }
            } else {
                $tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses(false, true, new DateTime('today'));
                if (($countInActive = $tblDivisionCourse->getCountInActiveStudents())) {
                    $tempContent = $countInActive == 1 ? ' inaktiven' : ' inaktive';
                    $optionInActive = (new CheckBox('Data[OptionInActive]', $countInActive . $tempContent . ' Schüler mit anzeigen', 1));
                    // Todo inactive anzeigen
//                        ->ajaxPipelineOnChange(ApiGradeBook::pipelineCheckInActive($DivisionCourseId, $SubjectId, $Filter));
                }
            }

            $gradeFrontend = Grade::useFrontend();

            $integrationList = array();
            $pictureList = array();
            $courseList = array();
            if ($tblPersonList) {
                foreach ($tblPersonList as $tblPerson) {
                    if (($tblVirtualSubject = DivisionCourse::useService()->getVirtualSubjectFromRealAndVirtualByPersonAndYearAndSubject(
                            $tblPerson, $tblYear, $tblSubject, isset($inactiveStudentList[$tblPerson->getId()])
                        ))
                        && $tblVirtualSubject->getHasGrading()
                    ) {
                        // Schüler-Informationen
                        Grade::useService()->setStudentInfo($tblPerson, $tblYear, $integrationList, $pictureList, $courseList);

                        // Todo Kompetenzbereiche
                    }
                }
            }

            $hasPicture = !empty($pictureList);
            $hasIntegration = !empty($integrationList);
            $hasCourse = !empty($courseList);
            $headerList = $gradeFrontend->getGradeBookPreHeaderList($hasPicture, $hasIntegration, $hasCourse);
            $headerList['Option'] = $gradeFrontend->getTableColumnHead('&nbsp;');

            $count = 0;
            $bodyList = [];
            if ($tblPersonList) {
                foreach ($tblPersonList as $tblPerson) {
                    if (($tblVirtualSubject = DivisionCourse::useService()->getVirtualSubjectFromRealAndVirtualByPersonAndYearAndSubject(
                            $tblPerson, $tblYear, $tblSubject, isset($inactiveStudentList[$tblPerson->getId()])
                        ))
                        && $tblVirtualSubject->getHasGrading()
                    ) {
                        $bodyList[$tblPerson->getId()] = $gradeFrontend->getGradeBookPreBodyList($tblPerson, ++$count,
                            $hasPicture, $hasIntegration, $hasCourse,
                            $pictureList, $integrationList, $courseList, isset($inactiveStudentList[$tblPerson->getId()]));

                        $bodyList[$tblPerson->getId()]['Option'] = $gradeFrontend->getTableColumnBody(
                            new Standard('', '/Education/Competence/SkillRate/Student', new EyeOpen(), [
                                'DivisionCourseId' => $tblDivisionCourse->getId(),
                                'SubjectId' => $tblSubject->getId(),
                                'PersonId' => $tblPerson->getId(),
                                'SelectedYearId' => $SelectedYearId
                            ])
                        );
                    }
                }
            }

            return $gradeFrontend->getTableCustom($headerList, $bodyList);
        }

        return "";
    }
}