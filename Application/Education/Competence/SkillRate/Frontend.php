<?php

namespace SPHERE\Application\Education\Competence\SkillRate;

use SPHERE\Application\Education\Competence\SkillGrid\SkillGrid;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
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
        $stage->setContent($this->loadStudentEdit(972, 9));

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

    // PersonId, TeacherId, YearId, StudentSkillId, Date, ?Comment, Value, ?ScoreTypeItemId
    // PersonId, TeacherId, YearId, ?SkillId, ?Level, Skill, SkillArea (für neue Skills)

    public function loadStudentEdit($PersonId, $SubjectId): string
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Schüler wurde nicht gefunden!', new Exclamation());
        }
        if (!($tblSubject = Subject::useService()->getSubjectById($SubjectId))) {
            return new Danger('Schüler wurde nicht gefunden!', new Exclamation());
        }

        // Todo individuelle Kompetenzen hinzufügen
        // Todo anzeige geänderter Skillname
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

                    // Todo anzeige Durchschnitt oder letzte Bewertung
                    $dataList[$tblSkillArea->getId()]['skills'][] = new Layout(new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn(($tblSkill->getLevel() ? new Muted($tblSkill->getLevel() . ' ') : '')
                            . $tblSkill->getSkill(), 9),
                        // Todo entsprechendes Bewertungssystem
                        new LayoutColumn(new TextField('Data[Skills][' . $tblSkill->getId() . ']'), 3)
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

        return new Well((new Form(new FormGroup($rows)))->disableSubmitAction());
    }
}