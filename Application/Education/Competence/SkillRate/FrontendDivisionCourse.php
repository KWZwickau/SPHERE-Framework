<?php

namespace SPHERE\Application\Education\Competence\SkillRate;

use DateTime;
use SPHERE\Application\Api\Document\Storage\ApiPersonPicture;
use SPHERE\Application\Api\Education\Competence\ApiSkillRate;
use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\Education\Competence\SkillGrid\SkillGrid;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMember;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
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
            . ApiSkillRate::receiverBlock($this->loadDivisionCourseContent($DivisionCourseId, $SubjectId, $SelectedYearId), 'Content')
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
//        $role = SkillRate::useService()->getRole();
//        $isEdit = Grade::useService()->getIsEdit($DivisionCourseId, $SubjectId, $role);

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblSubject = Subject::useService()->getSubjectById($SubjectId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $optionInActive = '';
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
                    $optionInActive = (new CheckBox('Data[OptionInActive]', $countInActive . $tempContent . ' Schüler mit anzeigen', 1))
                        ->ajaxPipelineOnChange(ApiSkillRate::pipelineCheckInActive($DivisionCourseId, $SubjectId, $SelectedYearId));
                    $optionInActive = new Form(new FormGroup(new FormRow(new FormColumn($optionInActive))));
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
                    }
                }
            }

            $hasPicture = !empty($pictureList);
            $hasIntegration = !empty($integrationList);
            $hasCourse = !empty($courseList);
            $headerList = $gradeFrontend->getGradeBookPreHeaderList($hasPicture, $hasIntegration, $hasCourse);
            $headerList['SkillRates'] = $gradeFrontend->getTableColumnHead('Kompetenzbewertung');
            $headerList['Option'] = $gradeFrontend->getTableColumnHead('&nbsp;');

            $count = 0;
            $bodyList = [];
            $skillList = [];
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

                        $bodyList[$tblPerson->getId()]['SkillRates'] = $gradeFrontend->getTableColumnBody(
                            $this->getDisplayStudentSkills($tblPerson, $tblYear, $tblSubject, $skillList)
                        );

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

            return $optionInActive
                . $gradeFrontend->getTableCustom($headerList, $bodyList);
        }

        return "";
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSubject|null $tblSubject
     * @param $skillList
     *
     * @return string
     */
    private function getDisplayStudentSkills(TblPerson $tblPerson, TblYear $tblYear, ?TblSubject $tblSubject, &$skillList): string
    {
        $countTotal = 0;
        $countRates = 0;
        if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
            && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
            && ($level = $tblStudentEducation->getLevel()) !== null
        ) {
            $tblStudentSkillList = SkillRate::useService()->getStudentSkillListByPersonAndYear($tblPerson, $tblYear, $tblSubject);

            $tblCourse = $tblStudentEducation->getServiceTblCourse() ?: null;
            $tblSupportFocusType = Student::useService()->getPrimarySupportFocusTypeByPerson($tblPerson);
            if (!$tblCourse && !$tblSupportFocusType) {
                if (isset($skillList[$tblSchoolType->getId()][$level])) {
                    $tblSkillList = $skillList[$tblSchoolType->getId()][$level];
                } else {
                    $tblSkillList = SkillGrid::useService()->getSkillListBy($tblSchoolType, $level, $tblSubject);
                    $skillList[$tblSchoolType->getId()][$level] = $tblSkillList;
                }
            } else {
                $tblSkillList = SkillGrid::useService()->getSkillListBy($tblSchoolType, $level, $tblSubject, $tblCourse, $tblSupportFocusType);
            }

            $skills = [];
            foreach ($tblSkillList as $tblSkill) {
                $countTotal++;
                $tblStudentSkill = $tblStudentSkillList['SkillId_' . $tblSkill->getId()] ?? null;
                if ($tblStudentSkill && SkillRate::useService()->getStudentSkillRateListBy($tblStudentSkill)) {
                    $countRates++;
                }

                $skills[$tblSkill->getId()] = 1;
            }
            // individuelle Kompetenzen ohne Kompetenzraster oder von einer anderen Klassenstufe
            foreach ($tblStudentSkillList as $tblStudentSkill) {
                if (!$tblStudentSkill->getServiceTblSkill() || !isset($skills[$tblStudentSkill->getServiceTblSkill()->getId()])) {
                    $countTotal++;
                    if (SkillRate::useService()->getStudentSkillRateListBy($tblStudentSkill)) {
                        $countRates++;
                    }
                }
            }
        }

        return "$countRates von $countTotal Kompetenzen bewertet.";
    }
}