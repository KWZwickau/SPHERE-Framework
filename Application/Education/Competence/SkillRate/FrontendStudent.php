<?php

namespace SPHERE\Application\Education\Competence\SkillRate;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Api\Document\Storage\ApiPersonPicture;
use SPHERE\Application\Api\Education\Competence\ApiSkillRate;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Competence\SkillGrid\SkillGrid;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
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
     * @param $SelectedYearId
     *
     * @return Stage
     *
     * @noinspection PhpUnused
     */
    public function frontendStudent($DivisionCourseId, $SubjectId, $PersonId, $SelectedYearId = null): Stage
    {
        $stage = new Stage();
        $title = new Title(
            new Standard("Zurück", "/Education/Competence/SkillRate/DivisionCourse", new ChevronLeft(),
                ['DivisionCourseId' => $DivisionCourseId, 'SubjectId' => $SubjectId, 'SelectedYearId' => $SelectedYearId], 'Zurück zur Kursansicht')
            . "&nbsp;&nbsp;&nbsp;Kompetenzbewertung"
            . new Muted(new Small(" Schüleransicht "))
        );

        $error = '';
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $error = new Danger('Kurs wurde nicht gefunden!', new Exclamation());
        }
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $error = new Danger('Schüler wurde nicht gefunden!', new Exclamation());
        }
        if (!($tblSubject = Subject::useService()->getSubjectById($SubjectId))) {
            $error = new Danger('Fach wurde nicht gefunden!', new Exclamation());
        }

        if ($error) {
            $stage->setContent($title . $error);
        } else {
            $stage->setContent(
                $title
//                . new Container('&nbsp;')
                . $this->getStudentHead($tblPerson, $tblDivisionCourse, $tblSubject ?: null)
                . ApiSkillRate::receiverBlock($this->loadViewStudentContent($DivisionCourseId, $PersonId, $SubjectId), 'Content')
            );
        }

        return $stage;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject|null $tblSubject
     *
     * @return string
     */
    private function getStudentHead(TblPerson $tblPerson, TblDivisionCourse $tblDivisionCourse, ?TblSubject $tblSubject): string
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

        return ApiPersonPicture::receiverModal()
            . ApiSkillRate::receiverModal()
            . new Layout(new LayoutGroup($rows));
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     *
     * @return string
     */
    public function loadViewStudentContent($DivisionCourseId, $PersonId, $SubjectId): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Kurs wurde nicht gefunden!', new Exclamation());
        }
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Schüler wurde nicht gefunden!', new Exclamation());
        }
        if (!($tblSubject = Subject::useService()->getSubjectById($SubjectId))) {
            return new Danger('Fach wurde nicht gefunden!', new Exclamation());
        }

        // Todo individuelle Kompetenzen hinzufügen
        // Todo anzeige geänderter Skillname
        $dataList = [];
        if (($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
            && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
            && ($level = $tblStudentEducation->getLevel()) !== null
        ) {
            $tblStudentSkillList = SkillRate::useService()->getStudentSkillListByPersonAndYear($tblPerson, $tblYear);
            $tblSkillList = SkillGrid::useService()->getSkillListBy($tblSchoolType, $level, $tblSubject);
            foreach ($tblSkillList as $tblSkill) {
                if (($tblSkillArea = $tblSkill->getTblSkillArea())) {
                    if (!isset($dataList[$tblSkillArea->getId()])) {
                        $dataList[$tblSkillArea->getId()] = [
                            'name' => $tblSkillArea->getName() ?: 'Ohne Kompetenzbereich',
                            'isBold' => false,
                            'skills' => []
                        ];
                    }

                    $isBold = false;
                    $displayLast = '';
                    if (($tblStudentSkill = $tblStudentSkillList[$tblSkill->getId()] ?? null)
                        && ($displayLast = SkillRate::useService()->getDisplayStudentSkillRateLastOrAverage($tblStudentSkill, "Verlauf anzeigen"))
                    ) {
                        $dataList[$tblSkillArea->getId()]['isBold'] = true;
                        $isBold = true;
                        $displayLast = (new Link(new Bold($displayLast), ApiSkillRate::getEndpoint(), null, []))
                            ->ajaxPipelineOnClick(ApiSkillRate::pipelineOpenStudentSkillRateHistoryModal($tblStudentSkill->getId()));
                    }
                    $displaySkill = ($tblSkill->getLevel() ? new Muted($tblSkill->getLevel() . ' ') : '')
                        . ($isBold ? new Bold($tblSkill->getSkill()) : $tblSkill->getSkill())
                        . ($displayLast ? new PullRight($displayLast) : '');

                    $dataList[$tblSkillArea->getId()]['skills'][] = $displaySkill;
                }
            }
        }

        $content = '';
        foreach ($dataList as $item) {
            // bei alten Schuljahren grau statt blau
            $content .= new Panel($item['isBold'] ? new Bold($item['name']) : $item['name'], $item['skills'], Panel::PANEL_TYPE_INFO);
        }

        return (new Primary('Kompetenzen bewerten', ApiSkillRate::getEndpoint(), new Edit()))
                ->ajaxPipelineOnClick(ApiSkillRate::pipelineLoadEditStudentContent($DivisionCourseId, $PersonId, $SubjectId))
            . new Container('&nbsp;')
            . $content;
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     *
     * @return string
     */
    public function loadEditStudentContent($DivisionCourseId, $PersonId, $SubjectId): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Kurs nicht gefunden.', new Exclamation());
        }
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Schüler wurde nicht gefunden!', new Exclamation());
        }

        $tblSubject = $SubjectId ? Subject::useService()->getSubjectById($SubjectId) : null;

        return new Well($this->formStudentSkillRateList($tblDivisionCourse, $tblPerson, $tblSubject ?: null));
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblPerson $tblPerson
     * @param TblSubject|null $tblSubject
     * @param $ErrorList
     *
     * @return Form
     */
    public function formStudentSkillRateList(TblDivisionCourse $tblDivisionCourse, TblPerson $tblPerson, ?TblSubject $tblSubject, $ErrorList = null): Form
    {
        // Todo individuelle Kompetenzen hinzufügen
        // Todo anzeige geänderter Skillname
        $dataList = [];
        if (($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
            && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
            && ($level = $tblStudentEducation->getLevel()) !== null
        ) {
            $scoreTypeListBySkillGrid = [];
            $tblStudentSkillList = SkillRate::useService()->getStudentSkillListByPersonAndYear($tblPerson, $tblYear);
            $tblSkillList = SkillGrid::useService()->getSkillListBy($tblSchoolType, $level, $tblSubject);
            foreach ($tblSkillList as $tblSkill) {
                if (($tblSkillArea = $tblSkill->getTblSkillArea())) {
                    if (!isset($dataList[$tblSkillArea->getId()])) {
                        $dataList[$tblSkillArea->getId()] = [
                            'name' => $tblSkillArea->getName() ?: 'Ohne Kompetenzbereich',
                            'isBold' => false,
                            'skills' => []
                        ];
                    }

                    // Eingabe entsprechend dem Bewertungssystem
                    $tblSkillGrid = $tblSkill->getTblSkillGrid();
                    if ($tblSkillGrid && !isset($scoreTypeListBySkillGrid[$tblSkillGrid->getId()])) {
                        if (($tblScoreType = $tblSkillGrid->getServiceTblScoreType())) {
                            $scoreTypeListBySkillGrid[$tblSkillGrid->getId()] = [
                                'tblScoreTypeId' => $tblScoreType->getId(),
                                'Items' => $tblScoreType->getScoreTypeItems()
                            ];
                        } else {
                            $scoreTypeListBySkillGrid[$tblSkillGrid->getId()] = null;
                        }
                    }
                    if ($tblSkillGrid && isset($scoreTypeListBySkillGrid[$tblSkillGrid->getId()])
                        && ($scoreType = $scoreTypeListBySkillGrid[$tblSkillGrid->getId()])
                    ) {
                        // individuelles Bewertungssystem
                        $identifier = "Data[ScoreTypeSkills][{$scoreType['tblScoreTypeId']}][{$tblSkill->getId()}]";
                        $input = new SelectBox($identifier, '', ['{{ Name }}' => $scoreType['Items']], null, true, null);
                    } else {
                        // Prozent
                        $identifier = "Data[PercentSkills][{$tblSkill->getId()}]";
                        $input = new TextField($identifier);
                    }

                    // Anzeige Fehlermeldung
                    if (isset($ErrorList[$identifier])) {
                        $input->setError($ErrorList[$identifier]['Message']);
                    }

                    $isBold = false;
                    $displayLast = '';
                    if (isset($tblStudentSkillList[$tblSkill->getId()])
                        && ($displayLast = SkillRate::useService()->getDisplayStudentSkillRateLastOrAverage($tblStudentSkillList[$tblSkill->getId()]))
                    ) {
                        $dataList[$tblSkillArea->getId()]['isBold'] = true;
                        $isBold = true;
                    }

                    $displaySkill = ($tblSkill->getLevel() ? new Muted($tblSkill->getLevel() . ' ') : '')
                        . ($isBold ? new Bold($tblSkill->getSkill()) : $tblSkill->getSkill())
                        . ($displayLast ? new PullRight($displayLast) : '');

                    $dataList[$tblSkillArea->getId()]['skills'][] = new Layout(new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn((new Container($displaySkill))->setStyle(['padding-top: 5px;']), 10),
//                        new LayoutColumn(((new Container($displayLast)))->setStyle(['padding-top: 5px;']), 1),
                        new LayoutColumn($input, 2)
                    ))));
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
                    ->ajaxPipelineOnClick(ApiSkillRate::pipelineSaveEditStudentSkillRate($tblDivisionCourse->getId(), $tblPerson->getId(), $tblSubject?->getId())),
                (new Standard('Abbrechen', '/Education/Competence/SkillRate', new Disable()))
                    ->ajaxPipelineOnClick(ApiSkillRate::pipelineLoadViewStudentContent($tblDivisionCourse->getId(), $tblPerson->getId(), $tblSubject?->getId())),
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
     * @param $StudentSkillId
     *
     * @return string
     */
    public function openStudentSkillRateHistoryModal($StudentSkillId): string
    {
        if (!($tblStudentSkill = SkillRate::useService()->getStudentSkillById($StudentSkillId))) {
            return new Danger('Kompetenz wurde nicht gefunden.', new Exclamation());
        }

        $dataList = [];
        if ($tblStudentSkill->getSkillArea()) {
            $dataList[] = "Kompetenzbereich: {$tblStudentSkill->getSkillArea()}";
        }
        if ($tblStudentSkill->getSkillLevel()) {
            $dataList[] = "Niveau: {$tblStudentSkill->getSkillLevel()}";
        }
        $dataList[] = new Bold("Kompetenz: {$tblStudentSkill->getSkill()}"
            . new PullRight(SkillRate::useService()->getDisplayStudentSkillRateLastOrAverage($tblStudentSkill)));

        $tblStudentSkillRateList = SkillRate::useService()->getStudentSkillRateListBy($tblStudentSkill);
        $rows = [];
        foreach ($tblStudentSkillRateList as $tblStudentSkillRate) {
            $rows[] = new LayoutRow([
                new LayoutColumn($tblStudentSkillRate->getDateString(), 1),
                new LayoutColumn(new Center($tblStudentSkillRate->getDisplayRate()), 2),
                new LayoutColumn($tblStudentSkillRate->getDisplayTeacher(), 2),
                new LayoutColumn($tblStudentSkillRate->getComment(), 7),
            ]);
        }

        return new Title(new Bold('Verlauf der Kompetenzbewertung'))
            . new Panel('Kompetenz', $dataList, Panel::PANEL_TYPE_INFO)
            . new Title(new Layout(new LayoutGroup(new LayoutRow([
                new LayoutColumn("Datum", 1),
                new LayoutColumn(new Center("Bewertung"), 2),
                new LayoutColumn("Lehrer", 2),
                new LayoutColumn("Öffentlicher Kommentar zur Kompetenzfeststellung", 6),
            ]))))
            . new Layout(new LayoutGroup($rows));
    }
}