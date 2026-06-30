<?php

namespace SPHERE\Application\Education\Competence\SkillRate;

use DateTime;
use SPHERE\Application\Api\Document\Storage\ApiPersonPicture;
use SPHERE\Application\Api\Education\Competence\ApiSkillRate;
use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\Education\Competence\ScoreType\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Competence\SkillGrid\SkillGrid;
use SPHERE\Application\Education\Competence\SkillRate\Service\Entity\TblStudentSkill;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMember;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\ClipBoard;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class FrontendDivisionCourse extends Extension implements IFrontendInterface
{
    /**
     * @param null $SelectedYearId
     * @param null $DivisionCourseId
     * @param null $SubjectId
     * @param bool $IsInterdisciplinary
     *
     * @return Stage
     *
     * @noinspection PhpUnused
     */
    public function frontendDivisionCourse($SelectedYearId = null, $DivisionCourseId = null, $SubjectId = null, bool $IsInterdisciplinary = false): Stage
    {
        $stage = new Stage();
        $stage->setContent(ApiSupportReadOnly::receiverOverViewModal()
            . ApiPersonPicture::receiverModal()
            . ApiSkillRate::receiverBlock(
                $this->loadViewDivisionCourseContent($DivisionCourseId, $SubjectId, $SelectedYearId, false, $IsInterdisciplinary),
                'Content'
            )
        );

        return $stage;
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $SelectedYearId
     * @param bool $ShowInActive
     * @param bool $IsInterdisciplinary
     *
     * @return string
     */
    public function loadViewDivisionCourseContent(
        $DivisionCourseId, $SubjectId, $SelectedYearId, bool $ShowInActive = false, bool $IsInterdisciplinary = false)
    : string {
        $role = SkillRate::useService()->getRole();
        $isEdit = Grade::useService()->getIsEdit($DivisionCourseId, $SubjectId, $role);

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblSubject = Subject::useService()->getSubjectById($SubjectId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            // nur für SL, KL oder Alle-Readonly
            $hasStudentOverview = SkillRate::useService()->hasStudentOverview($role, $tblDivisionCourse);

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
                    if (!$tblSubject
                        || (($tblVirtualSubject = DivisionCourse::useService()->getVirtualSubjectFromRealAndVirtualByPersonAndYearAndSubject(
                                $tblPerson, $tblYear, $tblSubject, isset($inactiveStudentList[$tblPerson->getId()])
                            ))
                            && $tblVirtualSubject->getHasGrading())
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
                    if (!$tblSubject
                        || (($tblVirtualSubject = DivisionCourse::useService()->getVirtualSubjectFromRealAndVirtualByPersonAndYearAndSubject(
                                $tblPerson, $tblYear, $tblSubject, isset($inactiveStudentList[$tblPerson->getId()])
                            ))
                            && $tblVirtualSubject->getHasGrading())
                    ) {
                        $bodyList[$tblPerson->getId()] = $gradeFrontend->getGradeBookPreBodyList($tblPerson, ++$count,
                            $hasPicture, $hasIntegration, $hasCourse,
                            $pictureList, $integrationList, $courseList, isset($inactiveStudentList[$tblPerson->getId()]));

                        $bodyList[$tblPerson->getId()]['SkillRates'] = $gradeFrontend->getTableColumnBody(
                            $this->getDisplayStudentSkills(
                                $tblPerson, $tblYear, $IsInterdisciplinary ? null : $tblSubject, $IsInterdisciplinary ? $tblSubject : null, $skillList)
                        );

                        $bodyList[$tblPerson->getId()]['Option'] = $gradeFrontend->getTableColumnBody(
                            new Standard('', '/Education/Competence/SkillRate/Student', new ClipBoard(), [
                                'DivisionCourseId' => $tblDivisionCourse->getId(),
                                'SubjectId' => $tblSubject->getId(),
                                'PersonId' => $tblPerson->getId(),
                                'SelectedYearId' => $SelectedYearId,
                                'IsInterdisciplinary' => $IsInterdisciplinary
                            ], 'Kompetenzbewertung')
                            . ($hasStudentOverview
                                ? new Standard('', '/Education/Competence/SkillRate/Student/Overview', new EyeOpen(),
                                    ['DivisionCourseId' => $DivisionCourseId, 'SubjectId' => $SubjectId, 'PersonId' => $tblPerson->getId(),
                                        'BackRoute' => '/Education/Competence/SkillRate/DivisionCourse', 'SelectedYearId' => $SelectedYearId], 'Schülerübersicht')
                                : ''),
                            null,
                            '8%'
                        );
                    }
                }
            }

            $actions = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    ($isEdit
                        ? (new Primary('Kompetenz kursweise bewerten', ApiSkillRate::getEndpoint(), new ClipBoard()))
                            ->ajaxPipelineOnClick(ApiSkillRate::pipelineLoadEditDivisionCourseContent(
                                $DivisionCourseId, $SubjectId, $SelectedYearId, $IsInterdisciplinary ? 'true' : 'false'
                            ))
                        : ''
                    )
                    , 6),
                new LayoutColumn(
                    $optionInActive
                    , 3),
                new LayoutColumn(
                    new PullRight(
                        (new Standard($IsInterdisciplinary ? 'Fach' : 'Fächerübergreifend', ApiSkillRate::getEndpoint()))
                            ->ajaxPipelineOnClick(ApiSkillRate::pipelineLoadViewDivisionCourseContent(
                                $DivisionCourseId, $SubjectId, $SelectedYearId, $IsInterdisciplinary ? 'false' : 'true'))
                    )
                    , 3)
            ))));

            return
                new Title(
                    new Standard("Zurück", "/Education/Competence/SkillRate", new ChevronLeft(), ['SelectedYearId' => $SelectedYearId])
                    . "&nbsp;&nbsp;&nbsp;Kompetenzbewertung"
                    . new Muted(new Small(" für Kurs: ")) . new Bold($tblDivisionCourse->getDisplayName())
                    . new Muted(new Small(" im Fach: ")) . new Bold($tblSubject->getDisplayName())
                )
                . $actions
                . ($optionInActive ? '' : new Container('&nbsp;'))
                . $gradeFrontend->getTableCustom($headerList, $bodyList);
        }

        return "";
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSubject|null $tblSubject
     * @param TblSubject|null $tblSubjectForSkillRate
     * @param $skillList
     *
     * @return string
     */
    private function getDisplayStudentSkills(
        TblPerson $tblPerson, TblYear $tblYear, ?TblSubject $tblSubject, ?TblSubject $tblSubjectForSkillRate, &$skillList
    ): string {
        $countTotal = 0;
        $countRates = 0;
        $tblSupportFocusType = null;
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
                if ($tblStudentSkill && SkillRate::useService()->getStudentSkillRateListBy($tblStudentSkill, $tblSubjectForSkillRate)) {
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

        if ($tblSupportFocusType && $countTotal == 0) {
            return new \SPHERE\Common\Frontend\Text\Repository\Warning("Es existieren Kompetenzraster mit und ohne den 
                Förderschwerpunkt: {$tblSupportFocusType->getName()}.<br> 
                Bitte wählen Sie für den Schüler die entsprechenden Kompetenzen bei der Kompetenzbewertung über: \"Kompetenzen auswählen\" aus.");
        } else {
            return "$countRates von $countTotal Kompetenzen bewertet.";
        }
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $SelectedYearId
     * @param $IsInterdisciplinary
     *
     * @return string
     */
    public function loadEditDivisionCourseContent($DivisionCourseId, $SubjectId, $SelectedYearId, $IsInterdisciplinary): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Kurs nicht gefunden.', new Exclamation());
        }

        $tblSubject = Subject::useService()->getSubjectById($SubjectId) ?: null;

        // erstmal nur von Kompetenzraster möglich, eigene sind ja individuell
        $skillList = [];
        if (($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())
        ) {
            $schoolTypeList = [];
            foreach ($tblPersonList as $tblPerson) {
                if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                    && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                    && ($level = $tblStudentEducation->getLevel()) !== null
                ) {
                    if (!isset($schoolTypeList[$tblSchoolType->getId()][$level])) {
                        $schoolTypeList[$tblSchoolType->getId()][$level] = 1;
                        // Bildungsgang? Primärer Förderschwerpunkt?
                        $skillList = array_merge($skillList, SkillGrid::useService()->getSkillListBy(
                            $tblSchoolType, $level, $IsInterdisciplinary ? null : $tblSubject));
                    }
                }
            }
        }
        $list = [];
        foreach ($skillList as $tblSkill) {
            $list[] = new SelectBoxItem($tblSkill->getId(), $tblSkill->getDisplayName());
        }

        return
            new Title(
                new Standard("Zurück", "/Education/Competence/SkillRate/DivisionCourse", new ChevronLeft(),
                    ['DivisionCourseId' => $DivisionCourseId, 'SubjectId' => $SubjectId, 'SelectedYearId' => $SelectedYearId,
                        'IsInterdisciplinary' => $IsInterdisciplinary])
                . "&nbsp;&nbsp;&nbsp;Kompetenzbewertung"
                . new Muted(new Small(" für Kurs: ")) . new Bold($tblDivisionCourse->getDisplayName())
                . new Muted(new Small(" im Fach: ")) . new Bold($tblSubject?->getDisplayName())
            )
            . new Well(
                (new Form(new FormGroup([
                    new FormRow(
                        new FormColumn(
                            (new SelectBox("Data[Id]", "Kompetenz wählen", ['{{ Name }}' => $list], null, false, null))
                                ->ajaxPipelineOnChange(ApiSkillRate::pipelineLoadEditDivisionCourseSkillRateContent(
                                    $DivisionCourseId, $SubjectId, $SelectedYearId, $IsInterdisciplinary ? 'true' : 'false'))
                        )
                    ),
                    new FormRow(
                        new FormColumn(
                            ApiSkillRate::receiverBlock('', 'SkillRateContent')
                        )
                    )
                ])))->disableSubmitAction()
            );
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $SelectedYearId
     * @param $IsInterdisciplinary
     * @param null $Data
     * @param null $ErrorList
     *
     * @return string
     */
    public function loadEditDivisionCourseSkillRateContent(
        $DivisionCourseId, $SubjectId, $SelectedYearId, $IsInterdisciplinary, $Data = null, $ErrorList = null
    ): string {
        $cancelButton = (new Standard('Abbrechen', '/Education/Competence/SkillRate', new Disable()))
            ->ajaxPipelineOnClick(ApiSkillRate::pipelineLoadViewDivisionCourseContent(
                $DivisionCourseId, $SubjectId, $SelectedYearId, $IsInterdisciplinary ? 'true' : 'false'));

        if ($Data === null || empty($Data['Id'])) {
            return new Warning("Bitte wählen Sie zunächst eine Kompetenz aus.", new Exclamation())
                . $cancelButton;
        }

        if ($ErrorList === null) {
            $global = $this->getGlobal();
            $global->POST['Data']['Date'] = (new DateTime('today'))->format('d.m.Y');
            $global->savePost();
        }

        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Kurs nicht gefunden.', new Exclamation());
        }

        $tblSubject = Subject::useService()->getSubjectById($SubjectId) ?: null;

        // Herausforderung dann allerdings verschiedene Bewertungssysteme, eventuell bei allen gleich variante mit radio,
        // ansonsten SelectBox statt Radio oder erstmal geht nur bei gleich

        if (($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblSkill = SkillGrid::useService()->getSkillById($Data['Id']))
            && ($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())
        ) {
            $gradeFrontend = Grade::useFrontend();

            $integrationList = array();
            $pictureList = array();
            $courseList = array();
            $studentSkillList = [];
            $scoreTypeList = [];
            foreach ($tblPersonList as $tblPerson) {
                if (!$tblSubject
                    || (($tblVirtualSubject = DivisionCourse::useService()->getVirtualSubjectFromRealAndVirtualByPersonAndYearAndSubject(
                            $tblPerson, $tblYear, $tblSubject
                        ))
                        && $tblVirtualSubject->getHasGrading())
                ) {
                    if (($virtualStudentSkill = SkillRate::useService()->getVirtualStudentSkillBySkillName($tblPerson, $tblYear, $tblSkill))) {
                        $studentSkillList[$tblPerson->getId()] = $virtualStudentSkill;

                        $tblScoreTypeStudent = $virtualStudentSkill->getServiceTblScoreType();
                        $scoreTypeList[$tblScoreTypeStudent ? $tblScoreTypeStudent->getId() : -1] = $tblScoreTypeStudent;

                        // Schüler-Informationen
                        Grade::useService()->setStudentInfo($tblPerson, $tblYear, $integrationList, $pictureList, $courseList);
                    }
                }
            }

            $hasPicture = !empty($pictureList);
            $hasIntegration = !empty($integrationList);
            $hasCourse = !empty($courseList);
            $headerList = $gradeFrontend->getGradeBookPreHeaderList($hasPicture, $hasIntegration, $hasCourse);
            $headerList['SkillRates'] = $gradeFrontend->getTableColumnHead('Vorherige Bewertung');

            $tblScoreType = null;
            $isDiverseScoreType = false;
            if (count($scoreTypeList) == 0) {
                $headerList['Percent'] = $gradeFrontend->getTableColumnHead('Prozent');
            } elseif (count($scoreTypeList) == 1) {
                $tblScoreType = current($scoreTypeList);
                /** @var TblScoreType $tblScoreType */
                if ($tblScoreType) {
                    foreach ($tblScoreType->getScoreTypeItems() as $tblScoreTypeItem) {
                        $headerList['ScoreTypeId_' . $tblScoreTypeItem->getId()] = $gradeFrontend->getTableColumnHead($tblScoreTypeItem->getName());
                    }
                    // erforderlich fürs Entfernen der Radiooption, wenn einmal gesetzt
                    $headerList['ScoreTypeId_0'] = $gradeFrontend->getTableColumnHead('Keine Bewertung');
                } else {
                    $headerList['Percent'] = $gradeFrontend->getTableColumnHead('Prozent');
                }
            } else {
                $isDiverseScoreType = true;
                $headerList['Diverse'] = $gradeFrontend->getTableColumnHead('Bewertung');
            }

            $count = 0;
            $bodyList = [];
            foreach ($tblPersonList as $tblPerson) {
                if (!$tblSubject
                    || (($tblVirtualSubject = DivisionCourse::useService()->getVirtualSubjectFromRealAndVirtualByPersonAndYearAndSubject(
                            $tblPerson, $tblYear, $tblSubject
                        ))
                        && $tblVirtualSubject->getHasGrading())
                ) {
                    if (isset($studentSkillList[$tblPerson->getId()])) {
                        // Schüler-Informationen
                        Grade::useService()->setStudentInfo($tblPerson, $tblYear, $integrationList, $pictureList, $courseList);

                        $bodyList[$tblPerson->getId()] = $gradeFrontend->getGradeBookPreBodyList($tblPerson, ++$count,
                            $hasPicture, $hasIntegration, $hasCourse,
                            $pictureList, $integrationList, $courseList);

                        $virtualStudentSkill = $studentSkillList[$tblPerson->getId()];
                        if ($virtualStudentSkill instanceof TblStudentSkill) {
                            $bodyList[$tblPerson->getId()]['SkillRates'] = $gradeFrontend->getTableColumnBody(
                                SkillRate::useService()->getToolTipStudentSkillRateLastOrAverage($virtualStudentSkill, $IsInterdisciplinary ? $tblSubject : null)
                            );

                            $inputKey = 'StudentSkillId_' . $virtualStudentSkill->getId();
                        } else {
                            $bodyList[$tblPerson->getId()]['SkillRates'] = $gradeFrontend->getTableColumnBody('&nbsp;');

                            $inputKey = 'SkillId_' . $tblSkill->getId();
                        }

                        if ($tblScoreType) {
                            $identifier = "Data[ScoreTypeSkills][{$tblScoreType->getId()}][{$tblPerson->getId()}][$inputKey]";
                            foreach ($tblScoreType->getScoreTypeItems() as $tblScoreTypeItem) {
                                $input = new RadioBox($identifier, '&nbsp;', $tblScoreTypeItem->getId());
                                $bodyList[$tblPerson->getId()]['ScoreTypeId_' . $tblScoreTypeItem->getId()] = $gradeFrontend->getTableColumnBody($input);
                            }
                            // erforderlich fürs Entfernen der Radiooption, wenn einmal gesetzt
                            $input = new RadioBox($identifier, '&nbsp;', 0);
                            $bodyList[$tblPerson->getId()]['ScoreTypeId_0'] = $gradeFrontend->getTableColumnBody($input);
                        } elseif ($isDiverseScoreType
                            && isset($studentSkillList[$tblPerson->getId()])
                            && ($tblScoreTypeStudent = $studentSkillList[$tblPerson->getId()]->getServiceTblScoreType())
                        ) {
                            // Divers (Schülerabhängig)
                            $identifier = "Data[ScoreTypeSkills][{$tblScoreTypeStudent->getId()}][{$tblPerson->getId()}][$inputKey]";
                            $input = new SelectBox($identifier, '', ['{{ Name }}' => $tblScoreTypeStudent->getScoreTypeItems()], null, true, null);
                            $bodyList[$tblPerson->getId()]['Diverse'] = $gradeFrontend->getTableColumnBody($input);
                        } else {
                            // Prozent
                            $identifier = "Data[PercentSkills][{$tblPerson->getId()}][$inputKey]";
                            $input = new TextField($identifier);

                            // Anzeige Fehlermeldung
                            if (isset($ErrorList[$identifier])) {
                                $input->setError($ErrorList[$identifier]['Message']);
                            }

                            $bodyList[$tblPerson->getId()][$isDiverseScoreType ? 'Diverse' : 'Percent'] = $gradeFrontend->getTableColumnBody($input);
                        }
                    }
                }
            }


            if (!empty($bodyList)) {
                $inputDate = (new DatePicker('Data[Date]', '', 'Datum', new Calendar()))->setRequired();
                if (isset($ErrorList['Data[Date]'])) {
                    $inputDate->setError($ErrorList['Data[Date]']['Message']);
                }

                return
                    new Layout(new LayoutGroup(new LayoutRow([
                        new LayoutColumn($inputDate, 3),
                        new LayoutColumn((new TextField('Data[Comment]',
                            'Wie erfolgte die Feststellung der Kompetenz (z.B.: HA, Stundenaufgabe, Arbeitsblatt usw.)',
                            'Öffentlicher Kommentar zur Kompetenzfeststellung')), 9)
                    ])))
                    . $gradeFrontend->getTableCustom($headerList, $bodyList)
                    . ($ErrorList ? new Danger("Die Daten wurden nicht gespeichert. Bitte beachten Sie die Fehlermeldungen weiter oben.") : '')
                    . (new Primary('Speichern', ApiSkillRate::getEndpoint(), new Save()))
                        ->ajaxPipelineOnClick(ApiSkillRate::pipelineSaveEditDivisionCourseSkillRate(
                            $DivisionCourseId, $SubjectId, $SelectedYearId, $IsInterdisciplinary ? 'true' : 'false'))
                    . (new Standard('Abbrechen', '/Education/Competence/SkillRate', new Disable()))
                        ->ajaxPipelineOnClick(ApiSkillRate::pipelineLoadViewDivisionCourseContent(
                            $DivisionCourseId, $SubjectId, $SelectedYearId, $IsInterdisciplinary ? 'true' : 'false'));
            }
        }

        return new Warning('Keine Schüler für die ausgewählten Kompetenz gefunden.', new Exclamation());
    }
}