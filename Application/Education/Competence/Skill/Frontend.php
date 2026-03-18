<?php

namespace SPHERE\Application\Education\Competence\Skill;

use SPHERE\Application\Api\Education\Competence\ApiSkill;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\Course\Course;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{
    /** @noinspection PhpUnused */
    public function frontendSkills($SchoolTypeId = null): Stage
    {
        $stage = new Stage('Kompetenzraster', 'Übersicht');

        $buttonList = '';
        $route = '/Education/Competence/Skill';
        if (($tblSchoolTypeList = School::useService()->getConsumerSchoolTypeAll())) {
            foreach ($tblSchoolTypeList as $tblSchoolType) {
                if ($tblSchoolType->getId() == $SchoolTypeId) {
                    $buttonList .= new Standard(new Info(new Bold($tblSchoolType->getName())), $route, new Edit(), array('SchoolTypeId' => $tblSchoolType->getId()));
                } else {
                    $buttonList .= new Standard(
                        $tblSchoolType->getName() . ($tblSchoolType->getShortName() == 'Gy' ||  $tblSchoolType->getShortName() == 'BGy' ? ' (SekI)' : '')
                        , $route, null, array('SchoolTypeId' => $tblSchoolType->getId()));
                }
            }
        }

        if ($SchoolTypeId && ($tblSchoolType = Type::useService()->getTypeById($SchoolTypeId))) {
            // Todo Filter für Klassenstufe und Fach
            $dataList = [];
            if (($tblSkillGridList = Skill::useService()->getSkillGridListBy($tblSchoolType))) {
                foreach ($tblSkillGridList as $tblSkillGrid) {
                    $dataList[] = [
                        'Level' => $tblSkillGrid->getLevel(),
                        'Subject' => ($tblSubject = $tblSkillGrid->getServiceTblSubject()) ? $tblSubject->getDisplayName() : '',
                        'Name' => $tblSkillGrid->getName()
                    ];
                }
            }

            $table = new TableData(
                $dataList,
                null,
                [
                    'Level' => 'Klassenstufe',
                    'Subject' => 'Fach',
                    'Name' => 'Name'
                ],
                [
                    'order' => array(
                        array('0', 'asc'),
                        array('1', 'asc'),
                    ),
                ]
            );

            $stage->setContent(
                $buttonList
                . new Container('&nbsp;')
                . new Primary('Kompetenzraster hinzufügen', '/Education/Competence/Skill/Edit', new Plus(), ['SchoolTypeId' => $SchoolTypeId])
                . new Container('&nbsp;')
                . $table
            );

        } else {
            $stage->setContent(
                $buttonList
                . new Container('&nbsp;') . new Warning('Bitte wählen Sie zunächst eine Schulart aus.')
            );
        }

        return $stage;
    }

    /**
     * @param null $SchoolTypeId
     * @param null $SkillGridId
     * @param null $Data
     *
     * @return Stage
     */
    public function frontendEditSkills($SchoolTypeId = null, $SkillGridId = null, $Data = null): Stage
    {
        $stage = new Stage('Kompetenzraster', $SkillGridId ? 'Bearbeiten' : 'Hinzufügen');

        if (($tblSchoolType = Type::useService()->getTypeById($SchoolTypeId))) {
            $stage->setContent(new Well(Skill::useService()->updateSkillGrid($this->formSkillGrid($SkillGridId, $SchoolTypeId), $tblSchoolType, $Data)));
        }

        return $stage;
    }

    public function formSkillGrid($SkillGridId = null, $SchoolTypeId = null, bool $setPost = false): Form
    {
        // beim Checken der Input-Felder darf der Post nicht gesetzt werden
//        $tblSkillGrid = DivisionCourse::useService()->getSubjectTableById($SkillGridId);
//        if ($setPost && $tblSkillGrid) {
//            $Global = $this->getGlobal();
//            $Global->POST['Data']['Level'] = $tblSkillGrid->getLevel();
//            $Global->POST['Data']['TypeName'] = $tblSkillGrid->getTypeName();
//            $Global->POST['Data']['Subject'] = $tblSkillGrid->getSubjectId();
//            $Global->POST['Data']['StudentMetaIdentifier'] = $tblSkillGrid->getStudentMetaIdentifier();
//            $Global->POST['Data']['HoursPerWeek'] = $tblSkillGrid->getHoursPerWeek();
//            $Global->POST['Data']['HasGrading'] = $tblSkillGrid->getHasGrading();
//            $Global->POST['Data']['GradeText'] = ($tblGradeText = $tblSkillGrid->getServiceTblGradeText()) ? $tblGradeText->getId() : 0;
//            $Global->savePost();
//        } elseif (!$tblSkillGrid) {
//            $Global = $this->getGlobal();
//            $Global->POST['Data']['TypeName'] = 'Pflichtbereich';
//            $Global->POST['Data']['HasGrading'] = 1;
//            $Global->savePost();
//        }

//        if ($SkillGridId) {
//            $buttonList[] = (new Primary('Speichern', ApiSkill::getEndpoint(), new Save()))
//                ;// ->ajaxPipelineOnClick(ApiSkill::pipelineEditSubjectTableSave($SkillGridId, $SchoolTypeId));
//            $buttonList[] = (new \SPHERE\Common\Frontend\Link\Repository\Danger('Löschen', ApiSkill::getEndpoint(), new Remove()))
//                ;// ->ajaxPipelineOnClick(ApiSkill::pipelineOpenDeleteSubjectTableModal($SkillGridId, $SchoolTypeId));
//        } else {
//            $buttonList[] = (new Primary('Speichern', ApiSkill::getEndpoint(), new Save()))
//                ;// ->ajaxPipelineOnClick(ApiSkill::pipelineCreateSubjectTableSave($SchoolTypeId));
//        }

        $Data = null;
        $Errors = null;
        $AreaRanking = 1;

        // Todo Bewertungssysteme
        $tblScoreTypeList = [];
        $tblScoreTypeList[] = new SelectBoxItem(-1, 'Prozent');

        $tblSubjectList = Subject::useService()->getSubjectAll();
        $tblCourseAll = Course::useService()->getCourseAll();
        $tblSupportFocusTypeAll = Student::useService()->getSupportFocusTypeAll();

        return (new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel(
                            'Kompetenzraster',
                            new Layout(new LayoutGroup(array(
                                new LayoutRow(array(
                                    new LayoutColumn(
                                        (new TextField('Data[Name]', '', 'Name ' . new Danger('*')))->setAutoFocus()
                                    , 6),
                                    new LayoutColumn(
                                        (new SelectBox('Data[ScoreTypeId]', 'Bewertungssystem', array('{{ Name }}' => $tblScoreTypeList)))
                                    , 6),
                                )),
                                new LayoutRow(array(
                                    new LayoutColumn(
                                        new CheckBox('Data[IsAverage]', 'Für eine mehrmalig bewertete Kompetenz wird ein Durchschnitt gebildet (Ansonsten zählt nur die letzte Eingabe "Auf-Leveln")', 1)
                                    )
                                ))
                            ))),
                            Panel::PANEL_TYPE_INFO
                        )
                    ),
                )),
                new FormRow(
                    new FormColumn(
                        new Panel(
                            'Gültigkeitsbereich des Kompetenzrasters',
                            new Layout(new LayoutGroup(new LayoutRow(array(
                                new LayoutColumn(
                                    (new NumberField('Data[Level]', '', 'Klassenstufe'))//->setRequired()
                                    , 3 ),
                                new LayoutColumn(
                                    (new SelectBox('Data[SubjectId]', 'Fach (ansonsten Fächerübergreifend)', array('{{ Acronym }} - {{ Name }}' => $tblSubjectList)))
                                    , 3 ),
                                new LayoutColumn(
                                    (new SelectBox('Data[CourseId]', 'Bildungsgang', array('{{ Name }}' => $tblCourseAll)))
                                    , 3 ),
                                new LayoutColumn(
                                    (new SelectBox('Data[SupportFocusTypeId]', 'Primärer Förderschwerpunkt', array('{{ Name }}' => $tblSupportFocusTypeAll)))
                                    , 3 )
                            )))),
                            Panel::PANEL_TYPE_INFO
                        )
                    )
                )
            )),
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        ApiSkill::receiverBlock($this->getSkillAreaContent(1), "SkillAreaContent_$AreaRanking")
                    )
                )),
            )),
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Speichern', new Save())
                    )
                )),
            ))
        )));
    }

    public function getSkillAreaContent($AreaRanking): string
    {
        $layout = new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    new TextField("Data[SkillAreas][$AreaRanking][Area]", 'Neuer Kompetenzbereich', 'Kompetenzbereich')
                , 3),
                new LayoutColumn(
                    ApiSkill::receiverBlock($this->getSkillContent($AreaRanking, 1), "SkillContent_$AreaRanking" . "_1")
                , 9)
            )),
        )));

        return new Panel(
            "$AreaRanking. Kompetenzbereich",
            $layout,
            Panel::PANEL_TYPE_INFO
        ) . ApiSkill::receiverBlock(
                (new Link(new Bold('Kompetenzbereich hinzufügen'), ApiSkill::getEndpoint(), new Plus()))
                    ->ajaxPipelineOnClick(ApiSkill::pipelineLoadSkillAreaContent($AreaRanking + 1)),
                'SkillAreaContent_' . ($AreaRanking + 1)
            );
    }

    public function getSkillContent($AreaRanking, $SkillRanking): string
    {
        return new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    new TextField("Data[SkillAreas][$AreaRanking][Skills][$SkillRanking][Niveau]", 'Niveau', 'Niveau')
                , 4),
                new LayoutColumn(
                    (new TextField("Data[SkillAreas][$AreaRanking][Skills][$SkillRanking][Skill]", 'Neue Kompetenz', 'Kompetenz ' . new Danger('*')))
                , 8),
            )),
            new LayoutRow(array(
                new LayoutColumn(
                    ApiSkill::receiverBlock(
                        (new Link(new Bold('Kompetenz hinzufügen'), ApiSkill::getEndpoint(), new Plus()))
                            ->ajaxPipelineOnClick(ApiSkill::pipelineLoadSkillContent($AreaRanking, $SkillRanking + 1)),
                        'SkillContent_' . $AreaRanking . '_' . ($SkillRanking + 1)
                    )
                )
            ))
        )));
    }
}