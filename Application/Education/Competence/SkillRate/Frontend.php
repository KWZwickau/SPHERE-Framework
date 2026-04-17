<?php

namespace SPHERE\Application\Education\Competence\SkillRate;

use SPHERE\Application\Api\Education\Competence\ApiSkillRate;
use SPHERE\Application\Education\Competence\SkillGrid\SkillGrid;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
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
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{
    /** @noinspection PhpUnused */
    public function frontendSkills($SchoolTypeId = null): Stage
    {
        $stage = new Stage('Kompetenzbewertung', 'Übersicht');

//        $stage->setContent($this->loadStudentContent(972, 9));

        $tblYearList = Term::useService()->getYearByNow();
        $stage->setContent(ApiSkillRate::receiverBlock(
            $this->loadStudentEdit(972, (current($tblYearList))->getId(), 9),
            'EditStudentSkillRateContent'
        ));

        return $stage;
    }

    public function loadStudentContent($PersonId, $SubjectId): string
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Schüler wurde nicht gefunden!', new Exclamation());
        }
        if (!($tblSubject = Subject::useService()->getSubjectById($SubjectId))) {
            return new Danger('Schüler wurde nicht gefunden!', new Exclamation());
        }

        // Todo individuelle Kompetenzen hinzufügen
        $dataList = [];
        if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson))
            && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
            && ($level = $tblStudentEducation->getLevel()) !== null
        ) {
            $tblSkillList = SkillGrid::useService()->getSkillListBy($tblSchoolType, $level, $tblSubject);
            foreach ($tblSkillList as $tblSkill) {
                if (($tblSkillArea = $tblSkill->getTblSkillArea())) {
                    // todo fett falls schon bewertet
                    if (!isset($dataList[$tblSkillArea->getId()])) {
                        $dataList[$tblSkillArea->getId()] = [
                            'name' => $tblSkillArea->getName() ?: 'Ohne Kompetenzbereich',
                            'skills' => []
                        ];
                    }

                    $dataList[$tblSkillArea->getId()]['skills'][] =
                        ($tblSkill->getLevel() ? new Muted($tblSkill->getLevel() . ' ') : '')
                            . $tblSkill->getSkill();
                }
            }
        }

        $content = '';
        foreach ($dataList as $item) {
            // bei alten Schuljahren grau statt blau
            $content .= new Panel($item['name'], $item['skills'], Panel::PANEL_TYPE_INFO);
        }

        return $content;
    }

    public function loadStudentEdit($PersonId, $YearId, $SubjectId): string
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Schüler wurde nicht gefunden!', new Exclamation());
        }
        if (!($tblYear = Term::useService()->getYearById($YearId))) {
            return new Danger('Schuljahr nicht gefunden.', new Exclamation());
        }

        $tblSubject = $SubjectId ? Subject::useService()->getSubjectById($SubjectId) : null;

        return new Well($this->formStudentSkillRateList($tblPerson, $tblYear, $tblSubject ?: null));
    }

    public function formStudentSkillRateList(TblPerson $tblPerson, TblYear $tblYear, ?TblSubject $tblSubject, $ErrorList = null): Form
    {
        // Todo individuelle Kompetenzen hinzufügen
        // Todo anzeige geänderter Skillname
        $dataList = [];
        if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
            && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
            && ($level = $tblStudentEducation->getLevel()) !== null
        ) {
            $scoreTypeListBySkillGrid = [];
            $tblSkillList = SkillGrid::useService()->getSkillListBy($tblSchoolType, $level, $tblSubject);
            foreach ($tblSkillList as $tblSkill) {
                if (($tblSkillArea = $tblSkill->getTblSkillArea())) {
                    // todo fett falls schon bewertet
                    if (!isset($dataList[$tblSkillArea->getId()])) {
                        $dataList[$tblSkillArea->getId()] = [
                            'name' => $tblSkillArea->getName() ?: 'Ohne Kompetenzbereich',
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

                    // Todo anzeige Durchschnitt oder letzte Bewertung
                    $dataList[$tblSkillArea->getId()]['skills'][] = new Layout(new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn(($tblSkill->getLevel() ? new Muted($tblSkill->getLevel() . ' ') : '')
                            . $tblSkill->getSkill(), 9),
                        new LayoutColumn($input, 3)
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
            // bei alten Schuljahren grau statt blau
            $rows[] = new FormRow(new FormColumn(new Panel($item['name'], $item['skills'], Panel::PANEL_TYPE_INFO)));
        }

        $rows[] = new FormRow(array(
            new FormColumn(array(
                new Container('&nbsp;'),
                (new Primary('Speichern', ApiSkillRate::getEndpoint(), new Save()))
                    ->ajaxPipelineOnClick(ApiSkillRate::pipelineSaveEditStudentSkillRate($tblPerson->getId(), $tblYear->getId(), $tblSubject?->getId())),
                new Standard('Abbrechen', '/Education/Competence/SkillRate', new Disable())
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
}