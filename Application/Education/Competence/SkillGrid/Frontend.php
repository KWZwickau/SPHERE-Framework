<?php

namespace SPHERE\Application\Education\Competence\SkillGrid;

use SPHERE\Application\Api\Education\Competence\ApiSkillGrid;
use SPHERE\Application\Education\Competence\ScoreType\ScoreType;
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
use SPHERE\Common\Frontend\Icon\Repository\ChevronUp;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
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
        $route = '/Education/Competence/SkillGrid';
        if (($tblSchoolTypeList = School::useService()->getConsumerSchoolTypeAll())) {
            foreach ($tblSchoolTypeList as $tblSchoolType) {
                $name = $tblSchoolType->getName() . ($tblSchoolType->getShortName() == 'Gy' ||  $tblSchoolType->getShortName() == 'BGy' ? ' (SekI)' : '');
                if ($tblSchoolType->getId() == $SchoolTypeId) {
                    $buttonList .= new Standard(new Info(new Bold($name)), $route, new Edit(), array('SchoolTypeId' => $tblSchoolType->getId()));
                } else {
                    $buttonList .= new Standard($name, $route, null, array('SchoolTypeId' => $tblSchoolType->getId()));
                }
            }
        }

        $stage->setContent(
            $buttonList
            . new Container('&nbsp;')
            . ($SchoolTypeId && Type::useService()->getTypeById($SchoolTypeId)
                ? new Panel(new Filter() . ' Filter', $this->formFilter($SchoolTypeId, $Filter), Panel::PANEL_TYPE_INFO)
                : '')
            . ApiSkillGrid::receiverModal()
            . ApiSkillGrid::receiverBlock($this->loadSkillGridTable($SchoolTypeId, $Filter), 'SkillGridTable')
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
            foreach (SkillGrid::useService()->getAvailableSkillGridList($tblSchoolType, $level, $tblSubjectFilter ?: null) as $tblSkillGrid) {
                $dataList[] = [
                    'Level' => $tblSkillGrid->getLevel(),
                    'Subject' => ($tblSubject = $tblSkillGrid->getServiceTblSubject()) ? $tblSubject->getDisplayName() : '',
                    'Name' => $tblSkillGrid->getName(),
                    'SkillAreas' => $tblSkillGrid->getDisplaySkillAreas(),
                    'Option' => new Standard('', '/Education/Competence/SkillGrid/Edit', new Edit(),
                        ['SchoolTypeId' => $SchoolTypeId, 'Filter' => $Filter, 'SkillGridId' => $tblSkillGrid->getId()],
                        'Kompetenzraster bearbeiten')
                        . (new Standard('', ApiSkillGrid::getEndpoint(), new Remove(), array(), 'Kompetenzraster löschen'))
                            ->ajaxPipelineOnClick(ApiSkillGrid::pipelineOpenDeleteSkillGridModal($tblSkillGrid->getId(), $SchoolTypeId, $Filter))
                ];
            }

            $table = new TableData(
                $dataList,
                null,
                [
                    'Level' => 'Klassenstufe',
                    'Subject' => 'Fach',
                    'Name' => 'Name',
                    'SkillAreas' => 'Kompetenzbereiche',
                    'Option' => ' '
                ],
                [
                    'columnDefs' => array(
                        array('orderable' => false, 'width' => '60px', 'targets' => -1),
                        // array('searchable' => false, 'targets' => array(-1, -2)),
                    ),
                    'order' => array(
                        array('0', 'asc'),
                        array('1', 'asc'),
                    ),
                    'responsive' => false,
                    'destroy' => true
                ]
            );

            return new Primary('Kompetenzraster hinzufügen', '/Education/Competence/SkillGrid/Edit', new Plus(),
                    ['SchoolTypeId' => $SchoolTypeId, 'Filter' => $Filter])
                . new Container('&nbsp;')
                . $table;

        } else {
            return new Warning('Bitte wählen Sie zunächst eine Schulart aus.');
        }
    }

    /**
     * @param $SchoolTypeId
     * @param $Filter
     *
     * @return Form
     */
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

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    (new TextField('Filter[Level]', '', 'Klassenstufe'))
                        ->ajaxPipelineOnKeyUp(ApiSkillGrid::pipelineLoadSkillGridTable($SchoolTypeId))
                , 6),
                new FormColumn(
                    (new SelectBox('Filter[SubjectId]', 'Fach', array('{{ Acronym }} - {{ Name }}' => SkillGrid::useService()->getAvailableSubjectList())))
                        ->ajaxPipelineOnChange(ApiSkillGrid::pipelineLoadSkillGridTable($SchoolTypeId))
                , 6)
            )),
        )));
    }

    /**
     * @param null $SchoolTypeId
     * @param null $Filter
     * @param null $SkillGridId
     *
     * @return Stage
     * @noinspection PhpUnused
     */
    public function frontendEditSkills($SchoolTypeId = null, $Filter = null, $SkillGridId = null): Stage
    {
        $stage = new Stage('Kompetenzraster', $SkillGridId ? 'Bearbeiten' : 'Hinzufügen');

        $stage->setContent(
            ApiSkillGrid::receiverBlock($this->loadEditSkillGridContent(true, $SchoolTypeId, $Filter, $SkillGridId), 'EditSkillGridContent')
        );

        return $stage;
    }

    /**
     * @param bool $setPost
     * @param $SchoolTypeId
     * @param $Filter
     * @param $SkillGridId
     * @param $Action
     * @param $ActionId
     * @param $Data
     *
     * @return string
     */
    public function loadEditSkillGridContent(bool $setPost = false, $SchoolTypeId = null, $Filter = null, $SkillGridId = null,
        $Action = null, $ActionId = null, $Data = null): string
    {
        if ($Action == 'RemoveSkill' && isset($Data['Skills'][$ActionId])) {
            unset($Data['Skills'][$ActionId]);
        } elseif ($Action == 'MoveSkillUp' && isset($Data['Skills'][$ActionId])) {
            $split = explode('-', $ActionId);
            $areaRanking = $split[0];
            $skillRanking = $split[1];
            // wenn der SkillGrid davor gelöscht wurde → nicht nur minus 1
            $up = $skillRanking - 1;
            while ($up > 0) {
                if (isset($Data['Skills'][$areaRanking . '-' . $up])) {
                    $temp = $Data['Skills'][$ActionId];
                    $Data['Skills'][$ActionId] = $Data['Skills'][$areaRanking . '-' . $up];
                    $Data['Skills'][$areaRanking . '-' . $up] = $temp;

                    ksort($Data['Skills']);

                    // muss zusätzlich gepostet werden, damit die werte korrekt im Frontend angezeigt werden
                    $global = $this->getGlobal();
                    $global->POST['Data']['Skills'][$ActionId] = $Data['Skills'][$ActionId];
                    $global->POST['Data']['Skills'][$areaRanking . '-' . $up] = $Data['Skills'][$areaRanking . '-' . $up];
                    $global->savePost();

                    break;
                }
                $up--;
            }
        } elseif ($Action == 'RemoveSkillArea' && isset($Data['SkillAreas'][$ActionId])) {
            unset($Data['SkillAreas'][$ActionId]);
        } elseif ($Action == 'MoveSkillAreaUp' && isset($Data['SkillAreas'][$ActionId])) {
            // wenn der SkillGrid davor gelöscht wurde → nicht nur minus 1
            $up = $ActionId - 1;
            while ($up > 0) {
                if (isset($Data['SkillAreas'][$up])) {
                    $temp = $Data['SkillAreas'][$ActionId];
                    $Data['SkillAreas'][$ActionId] = $Data['SkillAreas'][$up];
                    $Data['SkillAreas'][$up] = $temp;
                    ksort($Data['SkillAreas']);

                    //  auch zugehörige Skills tauschen
                    $skillsCopy = $Data['Skills'];
                    $Data['Skills'] = [];
                    foreach ($skillsCopy as $key => $value) {
                        $split = explode('-', $key);
                        $areaRanking = $split[0];
                        $skillRanking = $split[1];
                        if ($areaRanking == $up) {
                            $Data['Skills']["$ActionId-$skillRanking"] = $value;
                        } elseif ($areaRanking == $ActionId) {
                            $Data['Skills']["$up-$skillRanking"] = $value;
                        } else {
                            $Data['Skills'][$key] = $value;
                        }
                    }
                    ksort($Data['Skills']);

                    // muss zusätzlich gepostet werden, damit die werte korrekt im Frontend angezeigt werden
                    $global = $this->getGlobal();

                    $global->POST['Data']['SkillAreas'][$ActionId] = $Data['SkillAreas'][$ActionId];
                    $global->POST['Data']['SkillAreas'][$up] = $Data['SkillAreas'][$up];
                    // auch die Skills posten
                    foreach ($Data['Skills'] as $key => $array) {
                        $global->POST['Data']['Skills'][$key] = $array;
                    }
                    $global->savePost();

                    break;
                }
                $up--;
            }
        }

        return new Well($this->formSkillGrid($setPost, $SchoolTypeId, $Filter, $SkillGridId, $Data));
    }

    /**
     * @param bool $setPost
     * @param null $SchoolTypeId
     * @param null $Filter
     * @param null $SkillGridId
     * @param null $Data
     * @param null $ErrorList
     *
     * @return Form
     */
    public function formSkillGrid(bool $setPost, $SchoolTypeId = null, $Filter = null, $SkillGridId = null, $Data = null, $ErrorList = null): Form
    {
        // beim Checken der Input-Felder darf der Post nicht gesetzt werden
        $tblSkillGrid = SkillGrid::useService()->getSkillGridById($SkillGridId);
        if ($setPost && $tblSkillGrid) {
            $Global = $this->getGlobal();
            $Global->POST['Data']['Name'] = $tblSkillGrid->getName();
            $Global->POST['Data']['ScoreTypeId'] = ($tblScoreType = $tblSkillGrid->getServiceTblScoreType()) ? $tblScoreType->getId() : -1;
            $Global->POST['Data']['IsAverage'] = $tblSkillGrid->getIsAverage();

            $Global->POST['Data']['Level'] = $tblSkillGrid->getLevel();
            $Global->POST['Data']['SubjectId'] = ($tblSubject = $tblSkillGrid->getServiceTblSubject()) ? $tblSubject->getId() : 0;
            $Global->POST['Data']['CourseId'] = ($tblCourse = $tblSkillGrid->getServiceTblCourse()) ? $tblCourse->getId() : 0;
            $Global->POST['Data']['SupportFocusTypeId'] = ($tblSupportFocusType = $tblSkillGrid->getServiceTblSupportFocusType())
                ? $tblSupportFocusType->getId() : 0;

            $tblSkillAreaList = [];
            foreach ($tblSkillGrid->getSkills() as $tblSkill) {
                $skillRanking = $tblSkill->getSortOrder();
                $tblSkillArea = $tblSkill->getTblSkillArea();
                $areaRanking = $tblSkillArea->getSortOrder();

                if (!isset($tblSkillAreaList[$tblSkillArea->getId()])) {
                    $Global->POST['Data']['SkillAreas'][$areaRanking]['Area'] = $tblSkillArea->getName() ?: '';
                    $Data['SkillAreas'][$areaRanking]['Area'] = $tblSkillArea->getName() ?: '';

                    $tblSkillAreaList[$tblSkillArea->getId()] = $tblSkillArea;
                }

                $Global->POST['Data']['Skills'][$areaRanking . '-' . $skillRanking]['Skill'] = $tblSkill->getSkill();
                $Data['Skills'][$areaRanking . '-' . $skillRanking]['Skill'] = $tblSkill->getSkill();
                $Global->POST['Data']['Skills'][$areaRanking . '-' . $skillRanking]['Level'] = $tblSkill->getLevel() ?: '';
                $Data['Skills'][$areaRanking . '-' . $skillRanking]['Level'] = $tblSkill->getLevel() ?: '';
            }

            $Global->savePost();
        } elseif ($setPost && !$tblSkillGrid) {
            $Global = $this->getGlobal();
            $Global->POST['Data']['ScoreTypeId'] = -1;
            $Global->savePost();
        }

        // Bewertungssysteme
        $tblScoreTypeList = ScoreType::useService()->getScoreTypeAll();

        $tblSubjectList = SkillGrid::useService()->getAvailableSubjectList();
        $tblCourseAll = Course::useService()->getCourseAll();
        $tblSupportFocusTypeAll = Student::useService()->getSupportFocusTypeAll();

        $skillAreaRows = [];
        if ($Data === null) {
            $areaRanking = 1;
            $skillAreaRows[] = new FormRow(new FormColumn(
                ApiSkillGrid::receiverBlock($this->getSkillAreaContent($SchoolTypeId, $Filter, $SkillGridId, $areaRanking), "SkillAreaContent_$areaRanking")
            ));
        } else {
            $countSkillAreas = count($Data['SkillAreas']);
            $count = 0;
            foreach ($Data['SkillAreas'] as $areaRanking => $areaArray) {
                $skillAreaRows[] = new FormRow(new FormColumn(
                    ApiSkillGrid::receiverBlock(
                        $this->getSkillAreaContent($SchoolTypeId, $Filter, $SkillGridId, $areaRanking, ++$count == $countSkillAreas, $Data, $ErrorList),
                        "SkillAreaContent_$areaRanking"
                    )
                ));
            }
        }

        if (SkillGrid::useService()->getIsHeadmaster()) {
            $labelSubject = 'Fach (ansonsten Fächerübergreifend)';
        } else {
            $labelSubject = 'Fach ' . new Danger('*');
        }

        $form = (new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel(
                            'Kompetenzraster',
                            new Layout(new LayoutGroup(array(
                                new LayoutRow(array(
                                    new LayoutColumn(
                                        (new TextField('Data[Name]', '', 'Name ' . new Danger('*')))
                                    , 6),
                                    new LayoutColumn(
                                        (new SelectBox('Data[ScoreTypeId]', 'Bewertungssystem', array('{{ Name }} {{ Description }}' => $tblScoreTypeList)))
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
                                    (new SelectBox('Data[SubjectId]', $labelSubject, array('{{ Acronym }} - {{ Name }}' => $tblSubjectList)))
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
            ),
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new Container('&nbsp;'),
                        (new Primary('Speichern', ApiSkillGrid::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiSkillGrid::pipelineSaveEditSkillGrid($SchoolTypeId, $Filter, $SkillGridId)),
                        new Standard('Abbrechen', '/Education/Competence/SkillGrid', new Disable(), ['SchoolTypeId' => $SchoolTypeId, 'Filter' => $Filter])
                    ))
                )),
            ))
        )))->disableSubmitAction();

        if ($ErrorList) {
            foreach ($ErrorList as $error) {
                $form->setError($error['Name'], $error['Message']);
            }
        }

        return $form;
    }


    /**
     * @param $SchoolTypeId
     * @param $Filter
     * @param $SkillGridId
     * @param $AreaRanking
     * @param bool $hasAddButton
     * @param null $Data
     * @param null $ErrorList
     *
     * @return string
     */
    public function getSkillAreaContent($SchoolTypeId, $Filter, $SkillGridId, $AreaRanking, bool $hasAddButton = true, $Data = null, $ErrorList = null): string
    {
        $content = [];
        if ($Data === null) {
            $skillRanking = 1;
            $content[] = ApiSkillGrid::receiverBlock($this->getSkillContent($SchoolTypeId, $Filter, $SkillGridId, $AreaRanking, $skillRanking), "SkillContent_$AreaRanking" . "_$skillRanking");
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
                    $content[] = ApiSkillGrid::receiverBlock(
                        $this->getSkillContent($SchoolTypeId, $Filter, $SkillGridId, $AreaRanking, $skillRanking, ++$count == $countSkills, $ErrorList),
                        "SkillContent_$AreaRanking" . "_$skillRanking"
                    );
                }
            }
        }

        $layout = new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    new TextField("Data[SkillAreas][$AreaRanking][Area]", $AreaRanking == 1 ? 'Lesen / Mit Medien umgehen' : 'Neuer Kompetenzbereich', 'Kompetenzbereich')
                , 3),
                new LayoutColumn(
                    $content
                , 9)
            )),
        )));

        $button = '';
        if ($hasAddButton) {
            $button = ApiSkillGrid::receiverBlock(
                (new Link(new Bold('Kompetenzbereich hinzufügen'), ApiSkillGrid::getEndpoint(), new Plus()))
                    ->ajaxPipelineOnClick(ApiSkillGrid::pipelineLoadSkillAreaContent($SchoolTypeId, $Filter, $SkillGridId,$AreaRanking + 1)),
                'SkillAreaContent_' . ($AreaRanking + 1)
            );
        }

        $headerButtons =
            '&nbsp;&nbsp;'
            . (new Link('', ApiSkillGrid::getEndpoint(), new Minus(), [], 'Kompetenzbereich löschen', null))
                ->ajaxPipelineOnClick(ApiSkillGrid::pipelineLoadEditSkillGridContent(
                    $SchoolTypeId, $Filter, $SkillGridId, 'RemoveSkillArea', $AreaRanking))
            . '&nbsp;&nbsp;'
            . (new Link('', ApiSkillGrid::getEndpoint(), new ChevronUp(), [], 'Kompetenzbereich nach oben verschieben'))
                ->ajaxPipelineOnClick(ApiSkillGrid::pipelineLoadEditSkillGridContent(
                    $SchoolTypeId, $Filter, $SkillGridId, 'MoveSkillAreaUp', $AreaRanking));

        return new Panel(
            "$AreaRanking. Kompetenzbereich " . $headerButtons,
            $layout,
            Panel::PANEL_TYPE_INFO
        ) . $button;
    }

    /**
     * @param $SchoolTypeId
     * @param $Filter
     * @param $SkillGridId
     * @param $AreaRanking
     * @param $SkillRanking
     * @param bool $hasAddButton
     * @param null $ErrorList
     *
     * @return string
     */
    public function getSkillContent($SchoolTypeId, $Filter, $SkillGridId, $AreaRanking, $SkillRanking, bool $hasAddButton = true, $ErrorList = null): string
    {
        // POST kann maximal auf der 3. Ebene sein, es gehen keine tieferen Arrays
        $levelInput = new TextField("Data[Skills][$AreaRanking-$SkillRanking][Level]",
            $AreaRanking == 1 && $SkillRanking == 1 ? 'Grundwissen' : '', 'Niveau');
        $skillInput = new TextField("Data[Skills][$AreaRanking-$SkillRanking][Skill]",
            $AreaRanking == 1 && $SkillRanking == 1 ? 'Ich entnehme Texten gezielt Informationen.' : 'Neue Kompetenz', 'Kompetenz ' . new Danger('*'));
        if (isset($ErrorList["Data[Skills][$AreaRanking-$SkillRanking][Skill]"])) {
            $skillInput->setError($ErrorList["Data[Skills][$AreaRanking-$SkillRanking][Skill]"]['Message']);
        }

        $rows[] = new LayoutRow(array(
            new LayoutColumn(
                    $levelInput
                , 3),
            new LayoutColumn(
                    $skillInput
                , 7),
            new LayoutColumn(array(
                // TODO with flex ?
                (new Container('&nbsp;'))->setStyle(['height: 22px;']),
                (new Standard('', ApiSkillGrid::getEndpoint(), new Minus(), [], 'Kompetenz löschen'))
                    ->ajaxPipelineOnClick(ApiSkillGrid::pipelineLoadEditSkillGridContent(
                        $SchoolTypeId, $Filter, $SkillGridId, 'RemoveSkill', "$AreaRanking-$SkillRanking")),
                (new Standard('', ApiSkillGrid::getEndpoint(), new ChevronUp(), [], 'Kompetenz nach oben verschieben'))
                    ->ajaxPipelineOnClick(ApiSkillGrid::pipelineLoadEditSkillGridContent(
                        $SchoolTypeId, $Filter, $SkillGridId, 'MoveSkillUp', "$AreaRanking-$SkillRanking"))
            ), 2),
        ));
        if ($hasAddButton) {
            $rows[] = new LayoutRow(array(
                new LayoutColumn(
                    ApiSkillGrid::receiverBlock(
                        (new Link(new Bold('Kompetenz hinzufügen'), ApiSkillGrid::getEndpoint(), new Plus()))
                            ->ajaxPipelineOnClick(ApiSkillGrid::pipelineLoadSkillContent($SchoolTypeId, $Filter, $SkillGridId, $AreaRanking, $SkillRanking + 1)),
                        'SkillContent_' . $AreaRanking . '_' . ($SkillRanking + 1)
                    )
                )
            ));
        }

        return new Layout(new LayoutGroup($rows));
    }
}