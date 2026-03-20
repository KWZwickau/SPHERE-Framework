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
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
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
    public function frontendSkills($SchoolTypeId = null, $Filter = null): Stage
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

        $stage->setContent(
            $buttonList
            . new Container('&nbsp;')
            . ($SchoolTypeId && Type::useService()->getTypeById($SchoolTypeId)
                ? new Panel(new Filter() . ' Filter', $this->formFilter($SchoolTypeId, $Filter), Panel::PANEL_TYPE_INFO)
                : '')
            . ApiSkill::receiverBlock($this->loadSkillGridTable($SchoolTypeId, $Filter), 'SkillGridTable')
        );

        return $stage;
    }

    /**
     * @param $SchoolTypeId
     * @param $Filter
     *
     * @return string
     */
    public function loadSkillGridTable($SchoolTypeId = null, $Filter = null): string
    {
        if ($SchoolTypeId && ($tblSchoolType = Type::useService()->getTypeById($SchoolTypeId))) {
            // Filter für Klassenstufe und Fach
            $level = null;
            $tblSubjectFilter = null;
            if ($Filter) {
                if ($Filter['Level'] !== '') {
                    $level = $Filter['Level'];
                }
                if ($Filter['SubjectId']) {
                    $tblSubjectFilter = Subject::useService()->getSubjectById($Filter['SubjectId']);
                }
            }
            $dataList = [];
            if (($tblSkillGridList = Skill::useService()->getSkillGridListBy($tblSchoolType, $level, $tblSubjectFilter))) {
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

            return new Primary('Kompetenzraster hinzufügen', '/Education/Competence/Skill/Edit', new Plus(),
                    ['SchoolTypeId' => $SchoolTypeId, 'Filter' => $Filter])
                . new Container('&nbsp;')
                . $table;

        } else {
            return new Warning('Bitte wählen Sie zunächst eine Schulart aus.');
        }
    }

    public function formFilter($SchoolTypeId, $Filter): Form
    {
        if ($Filter != null) {
            $global = $this->getGlobal();
            if (!empty($Filter['Level'])) {
                $global->POST['Filter']['Level'] = $Filter['Level'];
            }
            if ($Filter['SubjectId'] && ($tblSubjectFilter = Subject::useService()->getSubjectById($Filter['SubjectId']))) {
                $global->POST['Filter']['SubjectId'] = $tblSubjectFilter->getId();
            }

            $global->savePost();
        }

        $tblSubjectList = Subject::useService()->getSubjectAll();

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    (new TextField('Filter[Level]', '', 'Klassenstufe'))
                        ->ajaxPipelineOnKeyUp(ApiSkill::pipelineLoadSkillGridTable($SchoolTypeId))
                , 6),
                new FormColumn(
                    (new SelectBox('Filter[SubjectId]', 'Fach', array('{{ Acronym }} {{ Name }}' => $tblSubjectList)))
                        ->ajaxPipelineOnChange(ApiSkill::pipelineLoadSkillGridTable($SchoolTypeId))
                , 6)
            )),
        )));
    }

    /**
     * @param null $SchoolTypeId
     * @param null $Filter
     * @param null $SkillGridId
     * @param null $Data
     *
     * @return Stage
     */
    public function frontendEditSkills($SchoolTypeId = null, $Filter = null, $SkillGridId = null, $Data = null): Stage
    {
        $stage = new Stage('Kompetenzraster', $SkillGridId ? 'Bearbeiten' : 'Hinzufügen');
        $stage->addButton($this->getBackButton($SchoolTypeId, $Filter));

        if (($tblSchoolType = Type::useService()->getTypeById($SchoolTypeId))) {
//            $stage->setContent(ApiSkill::receiverBlock(
            $stage->setContent(new Well(
                Skill::useService()->updateSkillGrid(
                    $this->formSkillGrid($SchoolTypeId, $Filter, $SkillGridId, true),
                    $tblSchoolType,
                    $Filter,
                    $Data
                )
            ));
        }

        return $stage;
    }

    /**
     * @param $SchoolTypeId
     * @param $Filter
     *
     * @return Standard
     */
    protected function getBackButton($SchoolTypeId, $Filter): Standard
    {
        return (new Standard('Zurück', '/Education/Competence/Skill', new ChevronLeft(), ['SchoolTypeId' => $SchoolTypeId, 'Filter' => $Filter]));
    }

    /**
     * @param $SchoolTypeId
     * @param $Filter
     * @param $SkillGridId
     * @param bool $setPost
     * @param $Data
     * @param $ErrorList
     *
     * @return Form
     */
    public function formSkillGrid($SchoolTypeId = null, $Filter = null, $SkillGridId = null, bool $setPost = false, $Data = null, $ErrorList = null): Form
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

        // Todo Bewertungssysteme
        $tblScoreTypeList = [];
        $tblScoreTypeList[] = new SelectBoxItem(-1, 'Prozent');

        $tblSubjectList = Subject::useService()->getSubjectAll();
        $tblCourseAll = Course::useService()->getCourseAll();
        $tblSupportFocusTypeAll = Student::useService()->getSupportFocusTypeAll();

        if ($Data === null) {
            $areaRanking = 1;
            $skillAreaRows[] = new FormRow(new FormColumn(
                ApiSkill::receiverBlock($this->getSkillAreaContent($areaRanking), "SkillAreaContent_$areaRanking")
            ));
        } else {
            $countSkillAreas = count($Data['SkillAreas']);
            $count = 0;
            foreach ($Data['SkillAreas'] as $areaRanking => $areaArray) {
                $skillAreaRows[] = new FormRow(new FormColumn(
                    ApiSkill::receiverBlock(
                        $this->getSkillAreaContent($areaRanking, ++$count == $countSkillAreas, $Data, $ErrorList),
                        "SkillAreaContent_$areaRanking"
                    )
                ));
            }
        }

        $form = new Form(array(
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
                                    (new NumberField('Data[Level]', '', 'Klassenstufe ' . new Danger('*')))//->setRequired()
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
            new FormGroup(
                $skillAreaRows
//                new FormRow(array(
//                    new FormColumn(
//                        ApiSkill::receiverBlock($this->getSkillAreaContent(1), "SkillAreaContent_$AreaRanking")
//                    )
//                )),
            ),
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Speichern', new Save())
                    )
                )),
            ))
        ));

        if ($ErrorList) {
            foreach ($ErrorList as $error) {
                $form->setError($error['Name'], $error['Message']);
            }
        }

        return $form;
    }


    /**
     * @param $AreaRanking
     * @param bool $hasAddButton
     * @param null $Data
     * @param null $ErrorList
     *
     * @return string
     */
    public function getSkillAreaContent($AreaRanking, bool $hasAddButton = true, $Data = null, $ErrorList = null): string
    {
        $content = [];
        if ($Data === null) {
            $skillRanking = 1;
            $content[] = ApiSkill::receiverBlock($this->getSkillContent($AreaRanking, $skillRanking), "SkillContent_$AreaRanking" . "_$skillRanking");
        } else {
            $countSkills = count(array_filter(
                array_keys($Data['Skills']),
                fn($key) => str_starts_with((string) $key, "$AreaRanking-")
            ));
            $count = 0;
            foreach ($Data['Skills'] as $key => $skillArray) {
                $split = explode('-', $key);
                if ($split[0] == $AreaRanking) {
                    $skillRanking = $split[1];
                    $content[] = ApiSkill::receiverBlock(
                        $this->getSkillContent($AreaRanking, $skillRanking, ++$count == $countSkills, $ErrorList),
                        "SkillContent_$AreaRanking" . "_$skillRanking"
                    );
                }
            }
        }

        $layout = new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    new TextField("Data[SkillAreas][$AreaRanking][Area]", 'Neuer Kompetenzbereich', 'Kompetenzbereich')
                , 3),
                new LayoutColumn(
                    $content
                , 9)
            )),
        )));

        $button = '';
        if ($hasAddButton) {
            $button = ApiSkill::receiverBlock(
                (new Link(new Bold('Kompetenzbereich hinzufügen'), ApiSkill::getEndpoint(), new Plus()))
                    ->ajaxPipelineOnClick(ApiSkill::pipelineLoadSkillAreaContent($AreaRanking + 1)),
                'SkillAreaContent_' . ($AreaRanking + 1)
            );
        }

        return new Panel(
            "$AreaRanking. Kompetenzbereich",
            $layout,
            Panel::PANEL_TYPE_INFO
        ) . $button;
    }

    /**
     * @param $AreaRanking
     * @param $SkillRanking
     * @param bool $hasAddButton
     * @param null $ErrorList
     *
     * @return string
     */
    public function getSkillContent($AreaRanking, $SkillRanking, bool $hasAddButton = true, $ErrorList = null): string
    {
        // POST kann maximal auf der 3. Ebene sein, es gehen keine tieferen Arrays
        $levelInput = new TextField("Data[Skills][$AreaRanking-$SkillRanking][Level]", 'Niveau', 'Niveau');
        $skillInput = new TextField("Data[Skills][$AreaRanking-$SkillRanking][Skill]", 'Neue Kompetenz', 'Kompetenz ' . new Danger('*'));
        if (isset($ErrorList["Data[Skills][$AreaRanking-$SkillRanking][Skill]"])) {
            $skillInput->setError($ErrorList["Data[Skills][$AreaRanking-$SkillRanking][Skill]"]['Message']);
        }

        $rows[] = new LayoutRow(array(
            new LayoutColumn(
                    $levelInput
                , 4),
            new LayoutColumn(
                    $skillInput
                , 8),
        ));
        if ($hasAddButton) {
            $rows[] = new LayoutRow(array(
                new LayoutColumn(
                    ApiSkill::receiverBlock(
                        (new Link(new Bold('Kompetenz hinzufügen'), ApiSkill::getEndpoint(), new Plus()))
                            ->ajaxPipelineOnClick(ApiSkill::pipelineLoadSkillContent($AreaRanking, $SkillRanking + 1)),
                        'SkillContent_' . $AreaRanking . '_' . ($SkillRanking + 1)
                    )
                )
            ));
        }

        return new Layout(new LayoutGroup($rows));
    }
}