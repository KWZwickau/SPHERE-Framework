<?php

namespace SPHERE\Application\Education\Competence\SkillRate;

use DateTime;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Api\Document\Storage\ApiPersonPicture;
use SPHERE\Application\Api\Education\Competence\ApiOnlineSkillRate;
use SPHERE\Application\Api\Education\Competence\ApiSkillRate;
use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Competence\ScoreType\ScoreType;
use SPHERE\Application\Education\Competence\SkillGrid\Service\Entity\TblSkill;
use SPHERE\Application\Education\Competence\SkillGrid\SkillGrid;
use SPHERE\Application\Education\Competence\SkillRate\Service\Entity\TblStudentSkill;
use SPHERE\Application\Education\Competence\SkillRate\Service\Entity\TblStudentSkillRate;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
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
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Icon\Repository\Tag;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Stage;

class FrontendStudent extends FrontendDivisionCourse
{
    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $PersonId
     * @param null $SelectedYearId
     * @param bool $IsInterdisciplinary
     *
     * @return Stage
     *
     * @noinspection PhpUnused
     */
    public function frontendStudent($DivisionCourseId, $SubjectId, $PersonId, $SelectedYearId = null, bool $IsInterdisciplinary = false): Stage
    {
        $stage = new Stage();

        $stage->setContent(
            ApiPersonPicture::receiverModal()
            . ApiSupportReadOnly::receiverOverViewModal()
            . ApiSkillRate::receiverModal()
            . ApiSkillRate::receiverBlock(
                $this->loadViewStudentContent($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId, false, $IsInterdisciplinary),
                'Content'
            )
        );

        return $stage;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject|null $tblSubject
     *
     * @return string
     */
    public function getStudentHead(TblPerson $tblPerson, TblDivisionCourse $tblDivisionCourse, ?TblSubject $tblSubject): string
    {
        $pictureHeight = '138px';
        $panelStudent = new Panel(
            'Schüler',
            $tblPerson->getLastFirstNameWithCallNameUnderline(true),
            Panel::PANEL_TYPE_INFO
        );
        $list[] = $tblDivisionCourse->getTypeName() . ': ' . $tblDivisionCourse->getDisplayName();
        if ($tblSubject) {
            $pictureHeight = '170px';
            $list[] = "Fach: {$tblSubject->getDisplayName()}";
        }
        $panelCourse = new Panel(
            'Kurs',
            $list,
            Panel::PANEL_TYPE_INFO
        );

        if (($tblPersonPicture = Storage::useService()->getPersonPictureByPerson($tblPerson))) {
            $PersonPicture = (new Link($tblPersonPicture->getPicture($pictureHeight, '10px'), $tblPerson->getId()))
                ->ajaxPipelineOnClick(ApiPersonPicture::pipelineShowPersonPicture($tblPerson->getId()));
        } else {
            $File = FileSystem::getFileLoader('/Common/Style/Resource/SSWIcon.png');
            $PersonPicture = '<img src="' . $File->getLocation() . '" style="height: ' . $pictureHeight . '; border-radius: 10px; opacity: 0.2">';
        }

        $rows[] = new LayoutRow(array(
            new LayoutColumn(new Layout(new LayoutGroup(array(
                new LayoutRow(new LayoutColumn($panelStudent)),
                new LayoutRow(new LayoutColumn($panelCourse)),
            ))), 10),
            new LayoutColumn(new Center($PersonPicture), 2),
        ));

        return new Layout(new LayoutGroup($rows));
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     * @param $SelectedYearId
     * @param bool $IsOldYears
     * @param bool $IsInterdisciplinary
     *
     * @return string
     */
    public function loadViewStudentContent(
        $DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId, bool $IsOldYears = false, bool $IsInterdisciplinary = false): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Kurs wurde nicht gefunden!', new Exclamation());
        }
        if (!($tblYear = $tblDivisionCourse->getServiceTblYear())) {
            return new Danger('Schuljahr wurde nicht gefunden!', new Exclamation());
        }
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Schüler wurde nicht gefunden!', new Exclamation());
        }

        $tblSubject = null;
        if (!$IsInterdisciplinary) {
            $tblSubject = Subject::useService()->getSubjectById($SubjectId) ?: null;
        }

        $role = SkillRate::useService()->getRole();
        $isEdit = Grade::useService()->getIsEdit($DivisionCourseId, $SubjectId, $role);

        $content = '';
        foreach (SkillRate::useService()->getStudentEducationList($tblPerson, $tblYear, $IsOldYears) as $tblStudentEducationTemp) {
            if (($tblYearTemp = $tblStudentEducationTemp->getServiceTblYear())
                && ($tblSchoolType = $tblStudentEducationTemp->getServiceTblSchoolType())
                && ($level = $tblStudentEducationTemp->getLevel()) !== null
            ) {
                $skills = [];
                $dataList = [];
                $tblStudentSkillList = SkillRate::useService()->getStudentSkillListByPersonAndYear($tblPerson, $tblYearTemp, $tblSubject);
                $tblSkillList = SkillGrid::useService()->getSkillListBy($tblSchoolType, $level, $tblSubject,
                    $tblStudentEducationTemp->getServiceTblCourse() ?: null, Student::useService()->getPrimarySupportFocusTypeByPerson($tblPerson));
                foreach ($tblSkillList as $tblSkill) {
                    $tblStudentSkill = $tblStudentSkillList['SkillId_' . $tblSkill->getId()] ?? null;
                    $this->setViewSkillData($dataList, $tblSkill, $tblStudentSkill, $DivisionCourseId, $SelectedYearId, $tblSubject, $SubjectId);
                    $skills[$tblSkill->getId()] = 1;
                }
                // individuelle Kompetenzen ohne Kompetenzraster oder von einer anderen Klassenstufe
                foreach ($tblStudentSkillList as $tblStudentSkill) {
                    if (!$tblStudentSkill->getServiceTblSkill() || !isset($skills[$tblStudentSkill->getServiceTblSkill()->getId()])) {
                        $this->setViewSkillData($dataList, null, $tblStudentSkill, $DivisionCourseId, $SelectedYearId, $tblSubject, $SubjectId);
                    }
                }

                if ($IsOldYears) {
                    $content .= new Title('Schuljahr: ' . $tblYearTemp->getName(), 'Klassenstufe: ' . $level);
                }
                foreach ($dataList as $item) {
                    // alten Schuljahren grau statt blau
                    $content .= new Panel($item['isBold'] ? new Bold($item['name']) : $item['name'], $item['skills'],
                        $tblYear->getId() == $tblYearTemp->getId() ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_DEFAULT);
                }
            }
        }

        // Inklusion
        $support = '';
        if (Student::useService()->getIsSupportByPerson($tblPerson)) {
            $support = (new Standard('Inklusion', ApiSupportReadOnly::getEndpoint(), new Tag(), [], 'Inklusion des Schülers anzeigen'))
                ->ajaxPipelineOnClick(ApiSupportReadOnly::pipelineOpenOverViewModal($tblPerson->getId()));
        }

        $buttons = '';
        if ($isEdit) {
            $buttons = (new Primary('Kompetenzen bewerten', ApiSkillRate::getEndpoint(), new ClipBoard()))
                ->ajaxPipelineOnClick(ApiSkillRate::pipelineLoadEditStudentContent(
                    $DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId, $IsInterdisciplinary ? 'true' : 'false'));

            if (!$IsInterdisciplinary) {
                $buttons .= (new Primary('Kompetenz umbenennen', ApiSkillRate::getEndpoint(), new Edit()))
                        ->ajaxPipelineOnClick(ApiSkillRate::pipelineOpenRenameStudentSkillModal($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId))
                    . (new Primary('Kompetenz auswählen', ApiSkillRate::getEndpoint(), new Select(),
                        [], 'Kompetenz vom Kompetenzraster einer anderen Klassenstufe wählen'))
                        ->ajaxPipelineOnClick(ApiSkillRate::pipelineOpenAddStudentSkillModal($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId))
                    . (new Primary('Kompetenz hinzufügen', ApiSkillRate::getEndpoint(), new Plus()))
                        ->ajaxPipelineOnClick(ApiSkillRate::pipelineOpenCreateStudentSkillModal($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId));
            }
        }

        return
            new Title(
                new Standard("Zurück", "/Education/Competence/SkillRate/DivisionCourse", new ChevronLeft(),
                    ['DivisionCourseId' => $DivisionCourseId, 'SubjectId' => $SubjectId, 'SelectedYearId' => $SelectedYearId], 'Zurück zur Kursansicht')
                . "&nbsp;&nbsp;&nbsp;Kompetenzbewertung"
                . new Muted(new Small(" Schüleransicht "))
            )
            . new PullClear($buttons
            . new PullRight(
                $support
                    . (new Standard($IsInterdisciplinary ? 'Fach' : 'Fächerübergreifend', ApiOnlineSkillRate::getEndpoint()))
                        ->ajaxPipelineOnClick(ApiOnlineSkillRate::pipelineLoadViewStudentContent(
                            $DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId, $IsOldYears, $IsInterdisciplinary ? 'false' : 'true'))
                    . (new Standard('Alte Schuljahre ' . ($IsOldYears ? 'ausblenden' : 'anzeigen'), ApiOnlineSkillRate::getEndpoint()))
                        ->ajaxPipelineOnClick(ApiOnlineSkillRate::pipelineLoadViewStudentContent(
                            $DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId, $IsOldYears ? 'false' : 'true', $IsInterdisciplinary))
            ))
            . new Container('&nbsp;')
            . $this->getStudentHead($tblPerson, $tblDivisionCourse, $tblSubject)
            . $content;
    }

    /**
     * @param array $dataList
     * @param TblSkill|null $tblSkill
     * @param TblStudentSkill|null $tblStudentSkill
     * @param $DivisionCourseId
     * @param $SelectedYearId
     * @param TblSubject|null $tblSubject
     * @param $SubjectId
     *
     * @return void
     */
    private function setViewSkillData(
        array &$dataList, ?TblSkill $tblSkill, ?TblStudentSkill $tblStudentSkill, $DivisionCourseId, $SelectedYearId, ?TblSubject $tblSubject, $SubjectId
    ): void {
        $skillAreaName = '';
        if ($tblSkill
            && ($tblSkillArea = $tblSkill->getTblSkillArea())
        ) {
            $skillAreaName = $tblSkillArea->getName() ?: 'Ohne Kompetenzbereich';

        } elseif ($tblStudentSkill) {
            $skillAreaName = $tblStudentSkill->getSkillArea() ?: 'Ohne Kompetenzbereich';
        }

        if ($skillAreaName) {
            $skillAreaIdentifier = preg_replace('/[^a-zA-Z0-9]/', '', $skillAreaName);
            if (!isset($dataList[$skillAreaIdentifier])) {
                $dataList[$skillAreaIdentifier] = [
                    'name' => $skillAreaName,
                    'isBold' => false,
                    'skills' => []
                ];
            }

            $isBold = false;
            $displayLast = '';
            if ($tblStudentSkill) {
                $tblSubjectForSkillRate = $tblSubject ? null : (Subject::useService()->getSubjectById($SubjectId) ?: null);
                if (($displayLast = SkillRate::useService()->getDisplayStudentSkillRateLastOrAverage(
                    $tblStudentSkill, $tblSubjectForSkillRate, "Verlauf anzeigen"
                ))) {
                    $dataList[$skillAreaIdentifier]['isBold'] = true;
                    $isBold = true;
                    $displayLast = (new Link(new Bold($displayLast), ApiOnlineSkillRate::getEndpoint(), null, []))
                        ->ajaxPipelineOnClick(ApiOnlineSkillRate::pipelineOpenStudentSkillRateHistoryModal(
                            $DivisionCourseId, $tblStudentSkill->getId(), $SelectedYearId, $tblSubjectForSkillRate?->getId()));
                }
                $skillLevel = $tblStudentSkill->getSkillLevel();
                $skill = $tblStudentSkill->getSkill();
            } else {
                $skillLevel = $tblSkill->getLevel();
                $skill = $tblSkill->getSkill();
            }

            $displaySkill = ($skillLevel ? new Muted($skillLevel . ' ') : '')
                . ($isBold ? new Bold($skill) : $skill)
                . ($displayLast ? new PullRight($displayLast) : '');

            $dataList[$skillAreaIdentifier]['skills'][] = $displaySkill;
        }
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     * @param $SelectedYearId
     * @param bool $IsInterdisciplinary
     *
     * @return string
     */
    public function loadEditStudentContent($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId, bool $IsInterdisciplinary = false): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Kurs nicht gefunden.', new Exclamation());
        }
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Schüler wurde nicht gefunden!', new Exclamation());
        }

        $tblSubject = null;
        if (!$IsInterdisciplinary) {
            $tblSubject = Subject::useService()->getSubjectById($SubjectId) ?: null;
        }

        return
            new Title(
                new Standard("Zurück", "/Education/Competence/SkillRate/Student", new ChevronLeft(),
                    ['DivisionCourseId' => $DivisionCourseId, 'SubjectId' => $SubjectId, 'PersonId' => $PersonId, 'SelectedYearId' => $SelectedYearId,
                        'IsInterdisciplinary' => $IsInterdisciplinary])
                . "&nbsp;&nbsp;&nbsp;Kompetenzbewertung"
                . new Muted(new Small(" Schüleransicht "))
            )
            . $this->getStudentHead($tblPerson, $tblDivisionCourse, $tblSubject)
            . new Well($this->formStudentSkillRateList($tblDivisionCourse, $tblPerson, $tblSubject, $SelectedYearId, $SubjectId));
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblPerson $tblPerson
     * @param TblSubject|null $tblSubject
     * @param $SelectedYearId
     * @param $SubjectId
     * @param null $ErrorList
     *
     * @return Form
     */
    public function formStudentSkillRateList(TblDivisionCourse $tblDivisionCourse, TblPerson $tblPerson, ?TblSubject $tblSubject, $SelectedYearId, $SubjectId,
        $ErrorList = null): Form
    {
        // aktuelles Datum vorsetzen
        if (!$ErrorList) {
            $global = $this->getGlobal();
            $global->POST['Data']['Date'] = (new DateTime('today'))->format('d.m.Y');
            $global->savePost();
        }

        $dataList = [];
        if (($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
            && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
            && ($level = $tblStudentEducation->getLevel()) !== null
        ) {
            $skills = [];
            $tblStudentSkillList = SkillRate::useService()->getStudentSkillListByPersonAndYear($tblPerson, $tblYear, $tblSubject);
            $tblSkillList = SkillGrid::useService()->getSkillListBy($tblSchoolType, $level, $tblSubject,
                $tblStudentEducation->getServiceTblCourse() ?: null, Student::useService()->getPrimarySupportFocusTypeByPerson($tblPerson));
            foreach ($tblSkillList as $tblSkill) {
                $tblStudentSkill = $tblStudentSkillList['SkillId_' . $tblSkill->getId()] ?? null;
                $this->setEditSkillData($dataList, $SubjectId, $ErrorList, $tblSkill, $tblStudentSkill);
                $skills[$tblSkill->getId()] = 1;
            }
            // individuelle Kompetenzen ohne Kompetenzraster oder von einer anderen Klassenstufe
            foreach ($tblStudentSkillList as $tblStudentSkill) {
                if (!$tblStudentSkill->getServiceTblSkill() || !isset($skills[$tblStudentSkill->getServiceTblSkill()->getId()])) {
                    $this->setEditSkillData($dataList, $SubjectId, $ErrorList, null, $tblStudentSkill);
                }
            }
        }

        $rows = [];
        $rows[] = new FormRow(array(
            new FormColumn((new DatePicker('Data[Date]', '', 'Datum', new Calendar()))->setRequired(), 3),
            new FormColumn((new TextField('Data[Comment]',
                'Wie erfolgte die Feststellung der Kompetenz (z.B.: HA, Stundenaufgabe, Arbeitsblatt usw.)',
                'Öffentlicher Kommentar zur Kompetenzfeststellung')), 9)
        ));
        foreach ($dataList as $item) {
            $rows[] = new FormRow(new FormColumn(new Panel(
                $item['isBold'] ? new Bold($item['name']) : $item['name'],
                $item['skills'],
                Panel::PANEL_TYPE_INFO
            )));
        }

        if ($ErrorList) {
            $rows[] = new FormRow(new FormColumn(new Danger("Die Daten wurden nicht gespeichert. Bitte beachten Sie die Fehlermeldungen weiter oben.")));
        }

        $rows[] = new FormRow(array(
            new FormColumn(array(
                new Container('&nbsp;'),
                (new Primary('Speichern', ApiSkillRate::getEndpoint(), new Save()))
                    ->ajaxPipelineOnClick(ApiSkillRate::pipelineSaveEditStudentSkillRate(
                        $tblDivisionCourse->getId(), $tblPerson->getId(), $SubjectId, $SelectedYearId, $tblSubject ? 'false' : 'true')),
                (new Standard('Abbrechen', ApiOnlineSkillRate::getEndpoint(), new Disable()))
                    ->ajaxPipelineOnClick(ApiOnlineSkillRate::pipelineLoadViewStudentContent(
                        $tblDivisionCourse->getId(), $tblPerson->getId(), $SubjectId, $SelectedYearId, 'false', $tblSubject ? 'false' : 'true')),
            ))
        ));

        $form = (new Form(new FormGroup($rows)))->disableSubmitAction();

        if ($ErrorList) {
            foreach ($ErrorList as $error) {
                $form->setError($error['Name'], $error['Message']);
            }
        }

        return $form;
    }

    /**
     * @param array $dataList
     * @param $ErrorList
     * @param TblSkill|null $tblSkill
     * @param TblStudentSkill|null $tblStudentSkill
     * @param TblStudentSkillRate|null $tblStudentSkillRate
     * @param $SubjectId
     *
     * @return void
     */
    private function setEditSkillData(array &$dataList, $SubjectId, $ErrorList, ?TblSkill $tblSkill, ?TblStudentSkill $tblStudentSkill,
        ?TblStudentSkillRate $tblStudentSkillRate = null): void
    {
        $skillAreaName = '';
        $inputKey = '';
        if ($tblSkill
            && ($tblSkillArea = $tblSkill->getTblSkillArea())
        ) {
            $skillAreaName = $tblSkillArea->getName() ?: 'Ohne Kompetenzbereich';
            $inputKey = 'SkillId_' . $tblSkill->getId();
        } elseif ($tblStudentSkill) {
            $skillAreaName = $tblStudentSkill->getSkillArea() ?: 'Ohne Kompetenzbereich';
            $inputKey = 'StudentSkillId_' . $tblStudentSkill->getId();
        }

        if ($skillAreaName) {
            $skillAreaIdentifier = preg_replace('/[^a-zA-Z0-9]/', '', $skillAreaName);
            if (!isset($dataList[$skillAreaIdentifier])) {
                $dataList[$skillAreaIdentifier] = [
                    'name' => $skillAreaName,
                    'isBold' => false,
                    'skills' => []
                ];
            }

            // Eingabe entsprechend dem Bewertungssystem
            $tblScoreType = null;
            if ($tblSkill
                && ($tblSkillGrid = $tblSkill->getTblSkillGrid())
            ) {
                $tblScoreType = $tblSkillGrid->getServiceTblScoreType() ?: null;
            } elseif ($tblStudentSkill) {
                $tblScoreType = $tblStudentSkill->getServiceTblScoreType() ?: null;
            }

            if ($tblScoreType) {
                // individuelles Bewertungssystem
                $identifier = "Data[ScoreTypeSkills][{$tblScoreType->getId()}][$inputKey]";
                if ($tblStudentSkillRate && ($tblScoreTypeItem = $tblStudentSkillRate->getServiceTblScoreTypeItem())) {
                    $global = $this->getGlobal();
                    $global->POST['Data']['ScoreTypeSkills'][$tblScoreType->getId()][$inputKey] = $tblScoreTypeItem->getId();
                    $global->savePost();
                }
                $input = new SelectBox($identifier, '', ['{{ Name }}' => $tblScoreType->getScoreTypeItems()], null, true, null);
            } else {
                // Prozent
                $identifier = "Data[PercentSkills][$inputKey]";
                if ($tblStudentSkillRate) {
                    $global = $this->getGlobal();
                    $global->POST['Data']['PercentSkills'][$inputKey] = $tblStudentSkillRate->getRate();
                    $global->savePost();
                }
                $input = new TextField($identifier);
            }

            // Anzeige Fehlermeldung
            if (isset($ErrorList[$identifier])) {
                $input->setError($ErrorList[$identifier]['Message']);
            }

            $isBold = false;
            $displayLast = '';
            if ($tblStudentSkill) {
                $tblSubjectForSkillRate = null;
                if (!$tblStudentSkill->getServiceTblSubject()) {
                    // ist fächerübergreifend → Fach wird mit an der Bewertung gespeichert
                    $tblSubjectForSkillRate = Subject::useService()->getSubjectById($SubjectId) ?: null;
                }
                if (($displayLast = SkillRate::useService()->getDisplayStudentSkillRateLastOrAverage($tblStudentSkill, $tblSubjectForSkillRate))) {
                    $dataList[$skillAreaIdentifier]['isBold'] = true;
                    $isBold = true;
                }
                $skillLevel = $tblStudentSkill->getSkillLevel();
                $skill = $tblStudentSkill->getSkill();
            } else {
                $skillLevel = $tblSkill->getLevel();
                $skill = $tblSkill->getSkill();
            }

            $displaySkill = ($skillLevel ? new Muted($skillLevel . ' ') : '')
                . ($isBold ? new Bold($skill) : $skill)
                . ($displayLast ? new PullRight($displayLast) : '');

            $dataList[$skillAreaIdentifier]['skills'][] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn((new Container($displaySkill))->setStyle(['padding-top: 5px;']), $tblStudentSkillRate ? 9 : 10),
//                        new LayoutColumn(((new Container($displayLast)))->setStyle(['padding-top: 5px;']), 1),
                new LayoutColumn($input, $tblStudentSkillRate ? 3 : 2)
            ))));
        }
    }

    /**
     * @param $DivisionCourseId
     * @param $StudentSkillId
     * @param $SelectedYearId
     * @param $SubjectId
     *
     * @return string
     */
    public function openStudentSkillRateHistoryModal($DivisionCourseId, $StudentSkillId, $SelectedYearId, $SubjectId): string
    {
        if (!($tblStudentSkill = SkillRate::useService()->getStudentSkillById($StudentSkillId))) {
            return new Danger('Kompetenz wurde nicht gefunden.', new Exclamation());
        }

        $panelList = [];
        if ($tblStudentSkill->getSkillArea()) {
            $panelList[] = "Kompetenzbereich: {$tblStudentSkill->getSkillArea()}";
        }
        if ($tblStudentSkill->getSkillLevel()) {
            $panelList[] = "Niveau: {$tblStudentSkill->getSkillLevel()}";
        }
        $panelList[] = new Bold("Kompetenz: {$tblStudentSkill->getSkill()}"
            . new PullRight(SkillRate::useService()->getDisplayStudentSkillRateLastOrAverage(
                $tblStudentSkill, Subject::useService()->getSubjectById($SubjectId) ?: null)));

        return new Panel('Kompetenz', $panelList, Panel::PANEL_TYPE_INFO)
            . ApiSkillRate::receiverBlock($this->loadViewStudentSkillRateHistoryContent($DivisionCourseId, $StudentSkillId, $SelectedYearId, $SubjectId), 'SkillRateHistoryContent');
    }

    /**
     * @param $DivisionCourseId
     * @param $StudentSkillId
     * @param $SelectedYearId
     * @param $SubjectId
     *
     * @return string
     */
    public function loadViewStudentSkillRateHistoryContent($DivisionCourseId, $StudentSkillId, $SelectedYearId, $SubjectId): string
    {
        if (!($tblStudentSkill = SkillRate::useService()->getStudentSkillById($StudentSkillId))) {
            return new Danger('Kompetenz wurde nicht gefunden.', new Exclamation());
        }
        $tblSubject = $tblStudentSkill->getServiceTblSubject() ?: null;
        $tblSubjectForSkillRate = Subject::useService()->getSubjectById($SubjectId) ?: null;

        $role = SkillRate::useService()->getRole();
        $isEdit = Grade::useService()->getIsEdit($DivisionCourseId, $tblSubject ? $tblSubject->getId() : $SubjectId, $role);
        // Fachlehrer dürfen Kompetenzbewertungen aus alten Schuljahren nicht bearbeiten
        if ($isEdit
            && $role == 'Teacher'
        ) {
            $isEdit = false;
            if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
                && ($tblDivisionCourseYear = $tblDivisionCourse->getServiceTblYear())
                && ($tblYear = $tblStudentSkill->getServiceTblYear())
                && $tblYear->getId() == $tblDivisionCourseYear->getId()
            ) {
                $isEdit = true;
            }
        }

        $gradeFrontend = Grade::useFrontend();
        $headerList['Date'] = $gradeFrontend->getTableColumnHead('Datum');
        $headerList['Rate'] = $gradeFrontend->getTableColumnHead('Bewertung');
        $headerList['Teacher'] = $gradeFrontend->getTableColumnHead('Lehrer');
        $headerList['Comment'] = $gradeFrontend->getTableColumnHead('Öffentlicher Kommentar zur Kompetenzfeststellung');
        if ($isEdit) {
            $headerList['Option'] = $gradeFrontend->getTableColumnHead('&nbsp;');
        }

        $tblStudentSkillRateList = SkillRate::useService()->getStudentSkillRateListBy($tblStudentSkill, $tblSubjectForSkillRate);
        $bodyList = [];
        foreach ($tblStudentSkillRateList as $tblStudentSkillRate) {
            $bodyList[$tblStudentSkillRate->getId()]['Date'] = $gradeFrontend->getTableColumnBody($tblStudentSkillRate->getDateString());
            $bodyList[$tblStudentSkillRate->getId()]['Rate'] = $gradeFrontend->getTableColumnBody($tblStudentSkillRate->getDisplayRate());
            $bodyList[$tblStudentSkillRate->getId()]['Teacher'] = $gradeFrontend->getTableColumnBody($tblStudentSkillRate->getDisplayTeacher());
            $bodyList[$tblStudentSkillRate->getId()]['Comment'] = $gradeFrontend->getTableColumnBody($tblStudentSkillRate->getComment() ?: '&nbsp;');
            if ($isEdit) {
                $bodyList[$tblStudentSkillRate->getId()]['Option'] = $gradeFrontend->getTableColumnBody(
                    (new Standard('', ApiSkillRate::getEndpoint(), new Edit(), [], 'Kompetenzbewertung bearbeiten'))
                        ->ajaxPipelineOnClick(ApiSkillRate::pipelineLoadEditStudentSkillRateHistoryContent(
                            $DivisionCourseId, $tblStudentSkillRate->getId(), $SelectedYearId, $SubjectId))
                    . (new Standard('', ApiSkillRate::getEndpoint(), new Remove(), [], 'Kompetenzbewertung löschen'))
                        ->ajaxPipelineOnClick(ApiSkillRate::pipelineLoadDeleteStudentSkillRateHistoryContent(
                            $DivisionCourseId, $tblStudentSkillRate->getId(), $SelectedYearId, $SubjectId))
                );
            }
        }

        return new Title(new ClipBoard() . ' Verlauf der Kompetenzbewertung')
            . $gradeFrontend->getTableCustom($headerList, $bodyList);
    }

    /**
     * @param $DivisionCourseId
     * @param $StudentSkillRateId
     * @param $SelectedYearId
     * @param $SubjectId
     * @param null $ErrorList
     *
     * @return string
     */
    public function loadEditStudentSkillRateHistoryContent($DivisionCourseId, $StudentSkillRateId, $SelectedYearId, $SubjectId, $ErrorList = null): string
    {
        if (!($tblStudentSkillRate = SkillRate::useService()->getStudentSkillRateById($StudentSkillRateId))) {
            return new Danger('Kompetenzbewertung wurde nicht gefunden.', new Exclamation());
        }

        $global = $this->getGlobal();
        $global->POST['Data']['Date'] = $tblStudentSkillRate->getDateString();
        $global->POST['Data']['Comment'] = $tblStudentSkillRate->getComment();
        // Rate wird in setEditSkillData gesetzt
        $global->savePost();

        $rows = [];
        $rows[] = new FormRow(array(
            new FormColumn((new DatePicker('Data[Date]', '', 'Datum', new Calendar()))->setRequired(), 3),
            new FormColumn((new TextField('Data[Comment]',
                'Wie erfolgte die Feststellung der Kompetenz (z.B.: HA, Stundenaufgabe, Arbeitsblatt usw.)',
                'Öffentlicher Kommentar zur Kompetenzfeststellung')), 9)
        ));
        $dataList = [];
        $tblStudentSkill = $tblStudentSkillRate->getTblStudentSkill();
        $this->setEditSkillData($dataList, $SubjectId, $ErrorList, $tblStudentSkill->getServiceTblSkill() ?: null, $tblStudentSkill, $tblStudentSkillRate);
        foreach ($dataList as $item) {
            $rows[] = new FormRow(new FormColumn(new Panel(
                $item['isBold'] ? new Bold($item['name']) : $item['name'],
                $item['skills'],
                Panel::PANEL_TYPE_INFO
            )));
        }

        $rows[] = new FormRow(array(
            new FormColumn(array(
//                new Container('&nbsp;'),
                (new Primary('Speichern', ApiSkillRate::getEndpoint(), new Save()))
                    ->ajaxPipelineOnClick(ApiSkillRate::pipelineSaveEditStudentSkillRateHistoryContent(
                        $DivisionCourseId, $tblStudentSkillRate->getId(), $SelectedYearId, $SubjectId)),
                (new Standard('Abbrechen', ApiOnlineSkillRate::getEndpoint(), new Disable()))
                    ->ajaxPipelineOnClick(ApiOnlineSkillRate::pipelineLoadViewStudentSkillRateHistoryContent(
                        $DivisionCourseId, $tblStudentSkill->getId(), $SelectedYearId, $SubjectId)),
            ))
        ));

        $form = (new Form(new FormGroup($rows)))->disableSubmitAction();

        if ($ErrorList) {
            foreach ($ErrorList as $error) {
                $form->setError($error['Name'], $error['Message']);
            }
        }

        return new Title(new Edit() . ' Kompetenzbewertung bearbeiten')
            . new Well($form);
    }

    /**
     * @param $DivisionCourseId
     * @param $StudentSkillRateId
     * @param $SelectedYearId
     * @param $SubjectId
     *
     * @return string
     */
    public function loadDeleteStudentSkillRateHistoryContent($DivisionCourseId, $StudentSkillRateId, $SelectedYearId, $SubjectId): string
    {
        if (!($tblStudentSkillRate = SkillRate::useService()->getStudentSkillRateById($StudentSkillRateId))) {
            return new Danger('Kompetenzbewertung wurde nicht gefunden.', new Exclamation());
        }

        $tblStudentSkill = $tblStudentSkillRate->getTblStudentSkill();

        return new Title(new Remove() . ' Kompetenzbewertung löschen')
            . new Layout(new LayoutGroup(new LayoutRow(
                    new LayoutColumn(
                        new Panel(
                            new Question() . ' Diese Kompetenzbewertung wirklich löschen?',
                            array(
                                'Datum: ' . new Bold($tblStudentSkillRate->getDateString()),
                                'Bewertung: ' . new Bold($tblStudentSkillRate->getDisplayRate()),
                            ),
                            Panel::PANEL_TYPE_DANGER
                        )
                        . (new DangerLink('Ja', ApiSkillRate::getEndpoint(), new Ok()))
                            ->ajaxPipelineOnClick(ApiSkillRate::pipelineSaveDeleteStudentSkillRateHistoryContent(
                                $DivisionCourseId, $tblStudentSkillRate->getId(), $SelectedYearId, $SubjectId))
                        . (new Standard('Nein', ApiOnlineSkillRate::getEndpoint(), new Remove()))
                            ->ajaxPipelineOnClick(ApiOnlineSkillRate::pipelineLoadViewStudentSkillRateHistoryContent(
                                $DivisionCourseId, $tblStudentSkill->getId(), $SelectedYearId, $SubjectId))
                    )
            )));
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     * @param $SelectedYearId
     *
     * @return string
     */
    public function openRenameStudentSkillModal($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Kurs nicht gefunden.', new Exclamation());
        }
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Schüler wurde nicht gefunden!', new Exclamation());
        }

        $tblSubject = Subject::useService()->getSubjectById($SubjectId) ?: null;
        $list = [];
        if (($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
            && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
            && ($level = $tblStudentEducation->getLevel()) !== null
        ) {
            $tblStudentSkillList = SkillRate::useService()->getStudentSkillListByPersonAndYear($tblPerson, $tblYear, $tblSubject);
            $tblSkillList = SkillGrid::useService()->getSkillListBy($tblSchoolType, $level, $tblSubject,
                $tblStudentEducation->getServiceTblCourse() ?: null, Student::useService()->getPrimarySupportFocusTypeByPerson($tblPerson));
            foreach ($tblSkillList as $tblSkill) {
                $tblStudentSkill = $tblStudentSkillList['SkillId_' . $tblSkill->getId()] ?? null;
                if ($tblStudentSkill) {
                    $skillArea = $tblStudentSkill->getSkillArea() ?: 'Ohne Kompetenzbereich';
                    $skillLevel = $tblStudentSkill->getSkillLevel();
                    $skill = $tblStudentSkill->getSkill();
                } else {
                    $skillArea = $tblSkill->getTblSkillArea() ? $tblSkill->getTblSkillArea()->getName() : '';
                    $skillArea = $skillArea ?: 'Ohne Kompetenzbereich';
                    $skillLevel = $tblSkill->getLevel();
                    $skill = $tblSkill->getSkill();
                }
                $list[] = new SelectBoxItem('SkillId_' . $tblSkill->getId(),
                    $skillArea . ' - ' . ($skillLevel ? $skillLevel . ' - ' : '') . $skill);
            }
            // individuelle Kompetenzen ohne Kompetenzraster
            foreach ($tblStudentSkillList as $tblStudentSkill) {
                if (!$tblStudentSkill->getServiceTblSkill()) {
                    $skillArea = $tblStudentSkill->getSkillArea() ?: 'Ohne Kompetenzbereich';
                    $skillLevel = $tblStudentSkill->getSkillLevel();
                    $skill = $tblStudentSkill->getSkill();
                    $list[] = new SelectBoxItem('StudentSkillId_' . $tblStudentSkill->getId(),
                        $skillArea . ' - ' . ($skillLevel ? $skillLevel . ' - ' : '') . $skill);
                }
            }
        }

        return new Title(new Edit() . ' Kompetenz umbenennen')
//            . implode('<br />', $list)
            . new Well((new Form(new FormGroup([
                new FormRow(
                    new FormColumn(
                        (new SelectBox("Data[Id]", "Kompetenz wählen", ['{{ Name }}' => $list], null, false, null))
                            ->ajaxPipelineOnChange(ApiSkillRate::pipelineLoadRenameSkillContent($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId))
                    )
                ),
                new FormRow(
                    new FormColumn(
                        ApiSkillRate::receiverBlock('', 'RenameSkillContent')
                    )
                )
            ])))->disableSubmitAction());
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     * @param $SelectedYearId
     * @param null $Data
     *
     * @return string
     */
    public function loadRenameSkillContent($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId, $Data = null): string
    {
        if ($Data === null || empty($Data['Id'])) {
            return new Warning("Bitte wählen Sie zunächst eine Kompetenz aus.", new Exclamation());
        }

        $split = explode('_', $Data['Id']);
        $tblSkill = null;
        if ($split[0] == 'SkillId'
            && ($tblSkill = SkillGrid::useService()->getSkillById($split[1]))
            && ($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $tblStudentSkill = SkillRate::useService()->getStudentSkillBy($tblPerson, $tblYear, $tblSkill);
        } else {
            $tblStudentSkill = SkillRate::useService()->getStudentSkillById($split[1]);
        }

        $global = $this->getGlobal();
        if ($tblStudentSkill) {
            if (!$tblSkill) {
                $global->POST['Data']['SkillArea'] = $tblStudentSkill->getSkillArea();
                $global->POST['Data']['ScoreTypeId'] = ($tblScoreType = $tblStudentSkill->getServiceTblScoreType()) ? $tblScoreType->getId() : -1;
                $global->POST['Data']['IsAverage'] = $tblStudentSkill->getIsAverage();
            }
            $global->POST['Data']['SkillLevel'] = $tblStudentSkill->getSkillLevel();
            $global->POST['Data']['Skill'] = $tblStudentSkill->getSkill();
        } elseif ($tblSkill) {
            $global->POST['Data']['SkillLevel'] = $tblSkill->getLevel();
            $global->POST['Data']['Skill'] = $tblSkill->getSkill();
        }
        $global->savePost();

        $rows = [];
        if (!$tblSkill) {
            $rows[] = new LayoutRow(new LayoutColumn(
                new TextField('Data[SkillArea]', '', 'Kompetenzbereich')
            ));
        }
        $rows[] = new LayoutRow([
            new LayoutColumn(
                new TextField('Data[SkillLevel]', '', 'Niveau')
                , 3),
            new LayoutColumn(
                (new TextField('Data[Skill]', '', 'Kompetenz'))->setRequired()
                , 9),
        ]);
        if (!$tblSkill) {
            $rows[] = new LayoutRow(new LayoutColumn(
                (new SelectBox('Data[ScoreTypeId]', 'Bewertungssystem', array('{{ Name }} {{ Description }}' => ScoreType::useService()->getScoreTypeAll())))
            ));
            $rows[] = new LayoutRow(new LayoutColumn(
                new CheckBox('Data[IsAverage]', 'Für eine mehrmalig bewertete Kompetenz wird ein Durchschnitt gebildet (Ansonsten zählt nur die letzte Eingabe "Auf-Leveln")', 1)
            ));
        }
        $rows[] = new LayoutRow([
            new LayoutColumn(
                (new Primary('Speichern', ApiSkillRate::getEndpoint(), new Save()))
                    ->ajaxPipelineOnClick(ApiSkillRate::pipelineSaveRenameSkill($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId))
            )
        ]);

        return new Layout(new LayoutGroup($rows));
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     * @param $SelectedYearId
     * @param null $ErrorList
     *
     * @return string
     */
    public function openAddStudentSkillModal($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId, $ErrorList = null): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Kurs nicht gefunden.', new Exclamation());
        }
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Schüler wurde nicht gefunden!', new Exclamation());
        }

        $tblSubject = Subject::useService()->getSubjectById($SubjectId) ?: null;
        $list = [];
        if (($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
            && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
            && ($level = $tblStudentEducation->getLevel()) !== null
        ) {
            $tblStudentSkillList = SkillRate::useService()->getStudentSkillListByPersonAndYear($tblPerson, $tblYear, $tblSubject);
            for ($i = $tblSchoolType->getMinLevel(); $i <= $tblSchoolType->getMaxLevel(); $i++) {
                if ($i == $level) {
                    continue;
                }
                $tblSkillList = SkillGrid::useService()->getSkillListBy($tblSchoolType, $i, $tblSubject,
                    $tblStudentEducation->getServiceTblCourse() ?: null, Student::useService()->getPrimarySupportFocusTypeByPerson($tblPerson));
                foreach ($tblSkillList as $tblSkill) {
                    // bereits vorhandene ausblenden
                    if (isset($tblStudentSkillList['SkillId_' . $tblSkill->getId()])) {
                        continue;
                    }

                    $skillArea = $tblSkill->getTblSkillArea() ? $tblSkill->getTblSkillArea()->getName() : '';
                    $skillArea = $skillArea ?: 'Ohne Kompetenzbereich';
                    $skillLevel = $tblSkill->getLevel();
                    $skill = $tblSkill->getSkill();
                    $list[] = new SelectBoxItem($tblSkill->getId(),
                         'Klassenstufe:' . $i . ' - '. $skillArea . ' - ' . ($skillLevel ? $skillLevel . ' - ' : '') . $skill);
                }
            }
        }

        $form = (new Form(new FormGroup([
            new FormRow(
                new FormColumn(
                    new SelectBox("Data[Id]", "Kompetenz auswählen", ['{{ Name }}' => $list], null, false, null)
                )
            ),
            new FormRow(
                new FormColumn(
                    (new Primary('Speichern', ApiSkillRate::getEndpoint(), new Save()))
                        ->ajaxPipelineOnClick(ApiSkillRate::pipelineSaveAddStudentSkill($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId))
                )
            )
        ])))->disableSubmitAction();

        if ($ErrorList) {
            foreach ($ErrorList as $error) {
                $form->setError($error['Name'], $error['Message']);
            }
        }

        return new Title(new Select() . ' Kompetenz vom Kompetenzraster einer anderen Klassenstufe wählen')
            . new Well($form);
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     * @param $SelectedYearId
     * @param null $ErrorList
     *
     * @return string
     */
    public function openCreateStudentSkillModal($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId, $ErrorList = null): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Kurs nicht gefunden.', new Exclamation());
        }
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Schüler wurde nicht gefunden!', new Exclamation());
        }

        if ($ErrorList == null) {
            $tblSubject = Subject::useService()->getSubjectById($SubjectId) ?: null;

            // IsAverage und Bewertungssystem vorauswählen aus bestehendem Kompetenzraster
            $isAverage = false;
            $tblScoreType = null;
            if (($tblYear = $tblDivisionCourse->getServiceTblYear())
                && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                && ($level = $tblStudentEducation->getLevel()) !== null
                && ($tblSkillGridList = SkillGrid::useService()->getSkillGridListBy($tblSchoolType, $level, $tblSubject))
            ) {
                $tblSkillGrid = current($tblSkillGridList);
                $isAverage = $tblSkillGrid->getIsAverage();
                $tblScoreType = $tblSkillGrid->getServiceTblScoreType();
            }
            $global = $this->getGlobal();
            if ($isAverage) {
                $global->POST['Data']['IsAverage'] = 1;
            }
            $global->POST['Data']['ScoreTypeId'] = $tblScoreType ? $tblScoreType->getId() : - 1;
            $global->savePost();
        }

        $form = (new Form(new FormGroup([
            new FormRow(
                new FormColumn(
                    new TextField('Data[SkillArea]', '', 'Kompetenzbereich')
                )
            ),
            new FormRow([
                new FormColumn(
                    new TextField('Data[SkillLevel]', '', 'Niveau')
                , 3),
                new FormColumn(
                    (new TextField('Data[Skill]', '', 'Kompetenz'))->setRequired()
                , 9),
            ]),
            new FormRow(
                new FormColumn(
                    (new SelectBox('Data[ScoreTypeId]', 'Bewertungssystem', array('{{ Name }} {{ Description }}' => ScoreType::useService()->getScoreTypeAll())))
                )
            ),
            new FormRow(
                new FormColumn(
                    new CheckBox('Data[IsAverage]', 'Für eine mehrmalig bewertete Kompetenz wird ein Durchschnitt gebildet (Ansonsten zählt nur die letzte Eingabe "Auf-Leveln")', 1)
                )
            ),
            new FormRow(
                new FormColumn(
                    (new Primary('Speichern', ApiSkillRate::getEndpoint(), new Save()))
                        ->ajaxPipelineOnClick(ApiSkillRate::pipelineSaveCreateStudentSkill($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId))
                )
            )
        ])))->disableSubmitAction();

        if ($ErrorList) {
            foreach ($ErrorList as $error) {
                $form->setError($error['Name'], $error['Message']);
            }
        }

        return new Title(new Plus() . ' Kompetenz hinzufügen')
            . new Well($form);
    }
}