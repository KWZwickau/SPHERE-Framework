<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Api\Education\Graduation\Grade\ApiScoreRule;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreCondition;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreRule;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreRuleConditionList;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Equalizer;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Group;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\MinusSign;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Link\Repository\ToggleSelective;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Repository\Sorter\StringNaturalOrderSorter;

abstract class FrontendScoreRule extends FrontendScoreGroup
{
    /**
     * @param null $ScoreRule
     *
     * @return Stage
     */
    public function frontendScoreRule($ScoreRule = null): Stage
    {
        $Stage = new Stage('Berechnungsvorschrift', 'Übersicht');
        $Stage->setMessage(
            'Hier werden die Berechnungsvorschriften, für die automatische Durchschnittsberechnung der Zensuren, verwaltet.' . '<br>' .
            'Die Berechnungsvorschrift bildet die 1. Ebene und setzt sich aus einer oder mehrerer Berechnungsvarianten
            zusammen.'
        );

        $this->setScoreStageMenuButtons($Stage, self::SCORE_RULE);

        $contentTable = array();
        $tblScoreRuleAll = Grade::useService()->getScoreRuleAll(true);
        if ($tblScoreRuleAll) {
            foreach ($tblScoreRuleAll as $tblScoreRule) {

                $structure = array();
                if ($tblScoreRule->getDescription() != '') {
                    $structure[] = 'Beschreibung: ' . $tblScoreRule->getDescription() . '<br/>';
                }
                if ($tblScoreRule->getDescriptionForExtern() != '') {
                    $structure[] = new Bold('Beschreibung für Eltern/Schülerzugänge: ') . '<br/>'
                        . str_replace("\n", '<br/>', $tblScoreRule->getDescriptionForExtern()) . '<br/>';
                }
                $structure = Grade::useService()->getScoreRuleStructure($tblScoreRule, $structure);

                $contentTable[] = array(
                    'Status' => $tblScoreRule->getIsActive()
                        ? new \SPHERE\Common\Frontend\Text\Repository\Success(new PlusSign() . ' aktiv')
                        : new \SPHERE\Common\Frontend\Text\Repository\Warning(new MinusSign() . ' inaktiv'),
                    'Name' => $tblScoreRule->getName(),
                    'Structure' => empty($structure) ? '' : implode('<br/>', $structure),
                    'Option' =>
                        (new Standard('', '/Education/Graduation/Grade/ScoreRule/Edit', new Edit(),
                            array('Id' => $tblScoreRule->getId()), 'Bearbeiten')) .
                        ($tblScoreRule->getIsActive()
                            ? (new Standard('', '/Education/Graduation/Grade/ScoreRule/Activate', new MinusSign(),
                                array('Id' => $tblScoreRule->getId()), 'Deaktivieren'))
                            : (new Standard('', '/Education/Graduation/Grade/ScoreRule/Activate', new PlusSign(),
                                array('Id' => $tblScoreRule->getId()), 'Aktivieren'))) .
                        ($tblScoreRule->getIsUsed()
                            ? ''
                            : (new Standard('', '/Education/Graduation/Grade/ScoreRule/Destroy', new Remove(),
                                array('Id' => $tblScoreRule->getId()), 'Löschen'))) .
                        ($tblScoreRule->getIsActive() ?
                            (new Standard('', '/Education/Graduation/Grade/ScoreRule/Condition/Select', new Listing(),
                                array('Id' => $tblScoreRule->getId()), 'Berechnungsvarianten auswählen')) .
                            (new Standard('', '/Education/Graduation/Grade/ScoreRule/Subject', new Equalizer(),
                                array('Id' => $tblScoreRule->getId()), 'Fach-Klassenstufen(Schulart) zuordnen')) .
                            (new Standard('', '/Education/Graduation/Grade/ScoreRule/SubjectDivisionCourse', new Group(),
                                array('Id' => $tblScoreRule->getId()), 'Fach-Kurse zuordnen'))
                            : '')
                );
            }
        }

        $Form = $this->formScoreRule()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($contentTable, null, array(
                                'Status' => 'Status',
                                'Name' => 'Name',
                                'Structure' => '',
                                'Option' => '',
                            ), array(
                                'order' => array(
                                    array('0', 'asc'),
                                    array('1', 'asc'),
                                ),
                                'columnDefs' => array(
                                    array('orderable' => false, 'width' => '215px', 'targets' => -1),
                                ),
                            ))
                        ))
                    ))
                ), new Title(new ListingTable() . ' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Well(Grade::useService()->createScoreRule($Form, $ScoreRule))
                        ))
                    ))
                ), new Title(new PlusSign() . ' Hinzufügen'))
            ))
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formScoreRule(): Form
    {
        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new TextField('ScoreRule[Name]', '', 'Name'), 4
                ),
                new FormColumn(
                    new TextField('ScoreRule[Description]', '', 'Beschreibung'), 8
                ),
            )),
            new FormRow(array(
                new FormColumn(
                    new TextArea('ScoreRule[DescriptionForExtern]', '', 'Beschreibung für Eltern/Schüler-Zugänge')
                )
            ))
        )));
    }

    /**
     * @param null $Id
     * @param null $ScoreRule
     * @return Stage|string
     */
    public function frontendEditScoreRule($Id = null, $ScoreRule = null)
    {
        $Stage = new Stage('Berechnungsvorschrift', 'Bearbeiten');
        $Stage->addButton(
            new Standard('Zur&uuml;ck', '/Education/Graduation/Grade/ScoreRule', new ChevronLeft())
        );

        $tblScoreRule = Grade::useService()->getScoreRuleById($Id);
        if ($tblScoreRule) {
            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['ScoreRule']['Name'] = $tblScoreRule->getName();
                $Global->POST['ScoreRule']['Description'] = $tblScoreRule->getDescription();
                $Global->POST['ScoreRule']['DescriptionForExtern'] = $tblScoreRule->getDescriptionForExtern();
                $Global->savePost();
            }

            $Form = $this->formScoreRule()
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    'Berechnungsvorschrift',
                                    new Bold($tblScoreRule->getName()) . '&nbsp;&nbsp;'
                                    . new Muted(new Small(new Small($tblScoreRule->getDescription()))),
                                    Panel::PANEL_TYPE_INFO
                                )
                            ),
                        ))
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Well(Grade::useService()->updateScoreRule($Form, $Id, $ScoreRule))
                            ),
                        ))
                    ), new Title(new Edit() . ' Bearbeiten'))
                ))
            );

            return $Stage;
        } else {
            return $Stage . new Danger(new Ban() . ' Berechnungsvorschrift nicht gefunden')
                . new Redirect('/Education/Graduation/Grade/ScoreRule', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param null $Id
     * @param bool|false $Confirm
     *
     * @return Stage|string
     */
    public function frontendDestroyScoreRule($Id = null, bool $Confirm = false)
    {
        $Stage = new Stage('Berechnungsvorschrift', 'Löschen');
        $Stage->addButton(
            new Standard('Zur&uuml;ck', '/Education/Graduation/Grade/ScoreRule', new ChevronLeft())
        );

        if (($tblScoreRule = Grade::useService()->getScoreRuleById($Id))) {
            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel('Berechnungsvorschrift',
                            $tblScoreRule->getName() . ($tblScoreRule->getDescription() !== '' ? '&nbsp;&nbsp;'
                                . new Muted(new Small($tblScoreRule->getDescription())) : ''),
                            Panel::PANEL_TYPE_INFO),
                        new Panel(new Question() . ' Diese Berechnungsvorschrift wirklich löschen?', array(
                            $tblScoreRule->getName() . ($tblScoreRule->getDescription() !== '' ? '&nbsp;&nbsp;'
                                . new Muted(new Small($tblScoreRule->getDescription())) : '')
                        ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Education/Graduation/Grade/ScoreRule/Destroy', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            . new Standard(
                                'Nein', '/Education/Graduation/Grade/ScoreRule', new Disable())
                        )
                    )))))
                );
            } else {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            (Grade::useService()->destroyScoreRule($tblScoreRule)
                                ? new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success()
                                    . ' Die Berechnungsvorschrift wurde gelöscht')
                                : new Danger(new Ban() . ' Die Berechnungsvorschrift konnte nicht gelöscht werden')
                            ),
                            new Redirect('/Education/Graduation/Grade/ScoreRule', Redirect::TIMEOUT_SUCCESS)
                        )))
                    )))
                );
            }

            return $Stage;

        } else {
            return $Stage . new Danger('Berechnungsvorschrift nicht gefunden.', new Ban())
                . new Redirect('/Education/Graduation/Grade/ScoreRule', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param $Id
     *
     * @return string
     */
    public function frontendActivateScoreRule($Id = null): string
    {
        $Stage = new Stage('Berechnungsvorschrift', 'Aktivieren/Deaktivieren');
        $Stage->addButton(
            new Standard('Zur&uuml;ck', '/Education/Graduation/Grade/ScoreRule', new ChevronLeft())
        );

        if (($tblScoreRule = Grade::useService()->getScoreRuleById($Id))) {
            $IsActive = !$tblScoreRule->getIsActive();
            if ((Grade::useService()->setScoreRuleActive($tblScoreRule, $IsActive))) {
                return $Stage . new Success('Die Berechnungsvorschrift wurde '
                        . ($IsActive ? 'aktiviert.' : 'deaktiviert.')
                        , new \SPHERE\Common\Frontend\Icon\Repository\Success())
                    . new Redirect('/Education/Graduation/Grade/ScoreRule', Redirect::TIMEOUT_SUCCESS);
            } else {
                return $Stage . new Danger('Die Berechnungsvorschrift konnte nicht '
                        . ($IsActive ? 'aktiviert' : 'deaktiviert') . ' werden.'
                        , new Ban())
                    . new Redirect('/Education/Graduation/Grade/ScoreRule', Redirect::TIMEOUT_ERROR);
            }
        } else {
            return $Stage . new Danger('Berechnungsvorschrift nicht gefunden.', new Ban())
                . new Redirect('/Education/Graduation/Grade/ScoreRule', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendScoreRuleConditionSelect($Id = null)
    {
        $Stage = new Stage('Berechnungsvorschrift', 'Berechnungsvarianten auswählen');
        $Stage->addButton(new Standard('Zurück', '/Education/Graduation/Grade/ScoreRule', new ChevronLeft()));

        if (empty($Id)) {
            $Stage->setContent(new Warning('Die Daten konnten nicht abgerufen werden'));
        } else {
            $tblScoreRule = Grade::useService()->getScoreRuleById($Id);
            if (empty($tblScoreRule)) {
                $Stage->setContent(new Warning('Die Berechnungsvorschrift konnte nicht abgerufen werden'));
            } else {
                $tblScoreRuleConditionListByRule = Grade::useService()->getScoreRuleConditionListByScoreRule($tblScoreRule);
                $tblScoreConditionAll = Grade::useService()->getScoreConditionAll();
                $tblScoreConditionAllByRule = array();
                if ($tblScoreRuleConditionListByRule) {
                    /** @var TblScoreRuleConditionList $tblScoreRuleConditionList */
                    foreach ($tblScoreRuleConditionListByRule as $tblScoreRuleConditionList) {
                        $tblScoreConditionAllByRule[] = $tblScoreRuleConditionList->getTblScoreCondition();
                    }
                }

                if (!empty($tblScoreConditionAllByRule) && $tblScoreConditionAll) {
                    $tblScoreConditionAll = array_udiff($tblScoreConditionAll, $tblScoreConditionAllByRule,
                        function (TblScoreCondition $ObjectA, TblScoreCondition $ObjectB) {

                            return $ObjectA->getId() - $ObjectB->getId();
                        }
                    );
                }

                if ($tblScoreRuleConditionListByRule) {
                    foreach ($tblScoreRuleConditionListByRule as &$tblScoreRuleCondition) {
                        $tblScoreRuleCondition->Name = $tblScoreRuleCondition->getTblScoreCondition()->getName();
                        $tblScoreRuleCondition->Priority = $tblScoreRuleCondition->getTblScoreCondition()->getPriority();
                        $tblScoreRuleCondition->Option =
                            (new \SPHERE\Common\Frontend\Link\Repository\Primary(
                                'Entfernen', '/Education/Graduation/Grade/ScoreRule/Condition/Remove',
                                new Minus(), array(
                                'Id' => $tblScoreRuleCondition->getId()
                            )))->__toString();
                    }
                }

                if ($tblScoreConditionAll) {
                    foreach ($tblScoreConditionAll as $tblScoreCondition) {
                        $tblScoreCondition->Option =
                            (new \SPHERE\Common\Frontend\Link\Repository\Primary(
                                'Hinzufügen',
                                '/Education/Graduation/Grade/ScoreRule/Condition/Add',
                                new Plus(),
                                array(
                                    'tblScoreRuleId' => $tblScoreRule->getId(),
                                    'tblScoreConditionId' => $tblScoreCondition->getId()
                                )
                            ))->__toString();
                    }
                }

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel('Berechnungsvorschrift', $tblScoreRule->getName(),
                                        Panel::PANEL_TYPE_INFO),
                                    12
                                ),
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Title('Ausgewählte', 'Berechnungsvarianten'),
                                    new TableData($tblScoreRuleConditionListByRule, null,
                                        array(
                                            'Name' => 'Name',
                                            'Priority' => 'Priorität',
                                            'Option' => ''
                                        ),
                                        array(
                                            'columnDefs' => array(
                                                array('type' => 'natural', 'targets' => 1),
                                                array('orderable' => false, 'targets' => -1),
                                            )
                                        )
                                    )
                                ), 6
                                ),
                                new LayoutColumn(array(
                                    new Title('Verfügbare', 'Berechnungsvarianten'),
                                    new TableData($tblScoreConditionAll, null,
                                        array(
                                            'Name' => 'Name ',
                                            'Priority' => 'Priorität ',
                                            'Option' => ' '
                                        ),
                                        array(
                                            'columnDefs' => array(
                                                array('type' => 'natural', 'targets' => 1),
                                                array('orderable' => false, 'targets' => -1),
                                            )
                                        )
                                    )
                                ), 6
                                )
                            )),
                        )),
                    ))
                );
            }
        }

        return $Stage;
    }

    /**
     * @param null $tblScoreRuleId
     * @param null $tblScoreConditionId
     *
     * @return Stage
     */
    public function frontendScoreRuleConditionAdd($tblScoreRuleId = null, $tblScoreConditionId = null): Stage
    {
        $Stage = new Stage('Berechnungsvorschrift', 'Berechnungsvariante einer Berechnungsvorschrift hinzufügen');

        $tblScoreRule = Grade::useService()->getScoreRuleById($tblScoreRuleId);
        $tblScoreCondition = Grade::useService()->getScoreConditionById($tblScoreConditionId);

        if ($tblScoreRule && $tblScoreCondition) {
            $Stage->setContent(Grade::useService()->addScoreRuleConditionList($tblScoreRule, $tblScoreCondition));
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendScoreRuleConditionRemove($Id = null): Stage
    {
        $Stage = new Stage('Berechnungsvorschrift', 'Berechnungsvariante von einer Berechnungsvorschrift entfernen');

        $tblScoreRuleCondition = Grade::useService()->getScoreRuleConditionListById($Id);
        if ($tblScoreRuleCondition) {
            $Stage->setContent(Grade::useService()->removeScoreRuleConditionList($tblScoreRuleCondition));
        }

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Data
     *
     * @return Stage
     */
    public function frontendScoreRuleSubject($Id = null, $Data = null): Stage
    {
        $Stage = new Stage('Berechnungsvorschrift', 'Fach-Klassenstufen(Schulart) einer Berechnungsvorschrift zuordnen');
        $Stage->setMessage('Hier können der ausgewählten Berechnungsvorschrift Fach-Klassenstufen(Schulart) zugeordnet werden.');
        $Stage->addButton(new Standard('Zurück', '/Education/Graduation/Grade/ScoreRule', new ChevronLeft()));

        if (($tblYearList = Term::useService()->getYearByNow())) {
            $tblYear = current($tblYearList);
            $Data['Year'] = $tblYear->getId();
            $global = $this->getGlobal();
            $global->POST['Data']['Year'] = $tblYear->getId();
            $global->savePost();
        }

        if ($tblScoreRule = Grade::useService()->getScoreRuleById($Id)) {
            $Stage->setContent(
                new Panel(
                    'Berechnungsvorschrift',
                    new Bold($tblScoreRule->getName()) . '&nbsp;&nbsp;'
                    . new Muted(new Small(new Small($tblScoreRule->getDescription()))),
                    Panel::PANEL_TYPE_INFO
                )
                . new Well($this->formScoreRuleSubject($tblScoreRule, $Data))
            );
        } else {
            $Stage->setContent(new Danger('Berechnungsvorschrift nicht gefunden.', new Exclamation()));
        }

        return $Stage;
    }

    /**
     * @param TblScoreRule $tblScoreRule
     * @param null $Data
     *
     * @return Form
     */
    public function formScoreRuleSubject(TblScoreRule $tblScoreRule, $Data = null): Form
    {
        $tblSchoolTypeList = School::useService()->getConsumerSchoolTypeCommonAll();

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    (new SelectBox('Data[Year]', 'Schuljahr', array("{{ DisplayName }}" => Term::useService()->getYearAll())))
                        ->ajaxPipelineOnChange(ApiScoreRule::pipelineLoadScoreRuleSubjects($tblScoreRule->getId()))
                , 6),
                new FormColumn(
                    (new SelectBox('Data[SchoolType]', 'Schulart', array('{{ Name }}' => $tblSchoolTypeList)))
                        ->ajaxPipelineOnChange(ApiScoreRule::pipelineLoadScoreRuleSubjects($tblScoreRule->getId()))
                , 6),
            )),
            new FormRow(new FormColumn(
                ApiScoreRule::receiverBlock($this->loadScoreRuleSubjects($tblScoreRule, $Data), 'ScoreRuleSubjectsContent')
            )),
            new FormRow(new FormColumn(array(
                (new \SPHERE\Common\Frontend\Link\Repository\Primary('Speichern', ApiScoreRule::getEndpoint(), new Save()))
                    ->ajaxPipelineOnClick(ApiScoreRule::pipelineSaveScoreRuleSubjects($tblScoreRule->getId())),
                (new Standard('Abbrechen', '/Education/Graduation/Grade/ScoreRule', new Disable()))
            )))
        )));
    }

    /**
     * @param TblScoreRule $tblScoreRule
     * @param null $Data
     *
     * @return string
     */
    public function loadScoreRuleSubjects(TblScoreRule $tblScoreRule, $Data = null): string
    {
        if (isset($Data['Year']) && ($tblYear = Term::useService()->getYearById($Data['Year']))
            && isset($Data['SchoolType']) && ($tblSchoolType = Type::useService()->getTypeById($Data['SchoolType']))
        ) {
            $global = $this->getGlobal();
            $global->POST['Data']['Subjects'] = array();
            $Data['Subjects'] = array();
            $list = array();
            if (($tblScoreRuleSubjectList = Grade::useService()->getScoreRuleSubjectListByYearAndSchoolType($tblYear, $tblSchoolType))) {
                foreach ($tblScoreRuleSubjectList as $tblScoreRuleSubject) {
                    if (($tblSubject = $tblScoreRuleSubject->getServiceTblSubject())
                        && ($tblScoreRuleTemp = $tblScoreRuleSubject->getTblScoreRule())
                    ) {
                        if ($tblScoreRule->getId() == $tblScoreRuleTemp->getId()) {
                            $global->POST['Data']['Subjects'][$tblScoreRuleSubject->getLevel()][$tblSubject->getId()] = 1;
                        } else {
                            $list[$tblScoreRuleSubject->getLevel()][$tblSubject->getId()] = ' ' . new Label($tblScoreRuleTemp->getName(), Label::LABEL_TYPE_PRIMARY);
                        }
                    }
                }
            }
            $global->savePost();

            $size = 3;
            $columnList = array();
            $toggleList = array();

            $minLevel = $tblSchoolType->getMinLevel();
            $maxLevel = $tblSchoolType->getMaxLevel();
            for ($level = $minLevel; $level <= $maxLevel; $level++) {
                $contentPanelList = array();
                if (($tblSubjectList = DivisionCourse::useService()->getSubjectListBySchoolTypeAndLevelAndYear($tblSchoolType, $level, $tblYear))) {
                    $tblSubjectList = $this->getSorter($tblSubjectList)->sortObjectBy('DisplayName');
                    foreach ($tblSubjectList as $tblSubject) {
                        $name = 'Data[Subjects][' . $level . '][' . $tblSubject->getId() .']';
                        $toggleList[$level][$tblSubject->getId()] = $name;
                        $contentPanelList[$level][$tblSubject->getId()] =
                            new CheckBox($name, $tblSubject->getDisplayName() . ($list[$level][$tblSubject->getId()] ?? ''), 1);
                    }
                }

                if (!empty($contentPanelList[$level])) {
                    if (isset($toggleList[$level])) {
                        array_unshift($contentPanelList[$level], new ToggleSelective('Alle wählen/abwählen', $toggleList[$level]));
                    }
                    $columnList[] = new LayoutColumn(new Panel('Klassenstufe ' . $level, $contentPanelList[$level], Panel::PANEL_TYPE_INFO), $size);
                }
            }

            if (empty($columnList)) {
                return new Warning('Keine entsprechenden Klassenstufen gefunden.', new Exclamation());
            } else {
                return new Layout(new LayoutGroup(
                    Grade::useService()->getLayoutRowsByLayoutColumnList($columnList, $size),
                    new Title($tblSchoolType->getName())
                ));
            }
        }

        return new Warning('Bitte wählen Sie zunächst ein Schuljahr und eine Schulart aus.');
    }

    /**
     * @param null $Id
     * @param null $Data
     *
     * @return Stage
     */
    public function frontendScoreRuleSubjectDivisionCourse($Id = null, $Data = null): Stage
    {
        $Stage = new Stage('Berechnungsvorschrift', 'Fach-Kurse einer Berechnungsvorschrift zuordnen');
        $Stage->setMessage('Hier können der ausgewählten Berechnungsvorschrift Fach-Kursen zugeordnet werden.');
        $Stage->addButton(new Standard('Zurück', '/Education/Graduation/Grade/ScoreRule', new ChevronLeft()));

        if (($tblYearList = Term::useService()->getYearByNow())) {
            $tblYear = current($tblYearList);
            $Data['Year'] = $tblYear->getId();
            $global = $this->getGlobal();
            $global->POST['Data']['Year'] = $tblYear->getId();
            $global->savePost();
        }

        if ($tblScoreRule = Grade::useService()->getScoreRuleById($Id)) {
            $Stage->setContent(
                new Panel(
                    'Berechnungsvorschrift',
                    new Bold($tblScoreRule->getName()) . '&nbsp;&nbsp;'
                    . new Muted(new Small(new Small($tblScoreRule->getDescription()))),
                    Panel::PANEL_TYPE_INFO
                )
                . new Well($this->formScoreRuleSubjectDivisionCourse($tblScoreRule, $Data))
            );
        } else {
            $Stage->setContent(new Danger('Berechnungsvorschrift nicht gefunden.', new Exclamation()));
        }

        return $Stage;
    }

    /**
     * @param TblScoreRule $tblScoreRule
     * @param null $Data
     *
     * @return Form
     */
    public function formScoreRuleSubjectDivisionCourse(TblScoreRule $tblScoreRule, $Data = null): Form
    {
        $tblSchoolTypeList = School::useService()->getConsumerSchoolTypeCommonAll();

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    (new SelectBox('Data[Year]', 'Schuljahr', array("{{ DisplayName }}" => Term::useService()->getYearAll())))
                        ->ajaxPipelineOnChange(ApiScoreRule::pipelineLoadScoreRuleSubjectDivisionCourses($tblScoreRule->getId()))
                    , 6),
                new FormColumn(
                    (new SelectBox('Data[SchoolType]', 'Schulart', array('{{ Name }}' => $tblSchoolTypeList)))
                        ->ajaxPipelineOnChange(ApiScoreRule::pipelineLoadScoreRuleSubjectDivisionCourses($tblScoreRule->getId()))
                    , 6),
            )),
            new FormRow(new FormColumn(
                ApiScoreRule::receiverBlock($this->loadScoreRuleSubjectDivisionCourses($tblScoreRule, $Data), 'ScoreRuleSubjectDivisionCoursesContent')
            )),
            new FormRow(new FormColumn(array(
                (new \SPHERE\Common\Frontend\Link\Repository\Primary('Speichern', ApiScoreRule::getEndpoint(), new Save()))
                    ->ajaxPipelineOnClick(ApiScoreRule::pipelineSaveScoreRuleSubjectDivisionCourses($tblScoreRule->getId())),
                (new Standard('Abbrechen', '/Education/Graduation/Grade/ScoreRule', new Disable()))
            )))
        )));
    }

    /**
     * @param TblScoreRule $tblScoreRule
     * @param null $Data
     *
     * @return string
     */
    public function loadScoreRuleSubjectDivisionCourses(TblScoreRule $tblScoreRule, $Data = null): string
    {
        if ((isset($Data['Year']) && ($tblYear = Term::useService()->getYearById($Data['Year'])))
            && (isset($Data['SchoolType']) && ($tblSchoolType = Type::useService()->getTypeById($Data['SchoolType'])))
        ) {
            $global = $this->getGlobal();
            $global->POST['Data']['SubjectDivisionCourses'] = array();
            $Data['SubjectDivisionCourses'] = array();
            $list = array();
            if (($tblScoreRuleSubjectDivisionCourseList = Grade::useService()->getScoreRuleSubjectDivisionCourseListByYear($tblYear))) {
                foreach ($tblScoreRuleSubjectDivisionCourseList as $tblScoreRuleSubjectDivisionCourse) {
                    if (($tblSubject = $tblScoreRuleSubjectDivisionCourse->getServiceTblSubject())
                        && ($tblScoreRuleTemp = $tblScoreRuleSubjectDivisionCourse->getTblScoreRule())
                        && ($tblDivisionCourse = $tblScoreRuleSubjectDivisionCourse->getServiceTblDivisionCourse())
                        && ($tblSchoolTypeList = $tblDivisionCourse->getSchoolTypeListFromStudents())
                        && isset($tblSchoolTypeList[$tblSchoolType->getId()])
                    ) {
                        if ($tblScoreRule->getId() == $tblScoreRuleTemp->getId()) {
                            $global->POST['Data']['SubjectDivisionCourses'][$tblDivisionCourse->getId()][$tblSubject->getId()] = 1;
                        } else {
                            $list[$tblDivisionCourse->getId()][$tblSubject->getId()] = ' ' . new Label($tblScoreRuleTemp->getName(), Label::LABEL_TYPE_PRIMARY);
                        }
                    }
                }
            }
            $global->savePost();

            $layoutGroups = array();
            if (($temp = $this->getLayoutGroupForDivisionCoursesSelectByTypeIdentifier($tblYear, TblDivisionCourseType::TYPE_DIVISION, $tblSchoolType, $list))) {
                $layoutGroups[] = $temp;
            }
            if (($temp = $this->getLayoutGroupForDivisionCoursesSelectByTypeIdentifier($tblYear, TblDivisionCourseType::TYPE_CORE_GROUP, $tblSchoolType, $list))) {
                $layoutGroups[] = $temp;
            }
            if (($temp = $this->getLayoutGroupForDivisionCoursesSelectByTypeIdentifier($tblYear, TblDivisionCourseType::TYPE_TEACHING_GROUP, $tblSchoolType, $list))) {
                $layoutGroups[] = $temp;
            }
            if (($temp = $this->getLayoutGroupForDivisionCoursesSelectByTypeIdentifier($tblYear, TblDivisionCourseType::TYPE_ADVANCED_COURSE, $tblSchoolType, $list))) {
                $layoutGroups[] = $temp;
            }
            if (($temp = $this->getLayoutGroupForDivisionCoursesSelectByTypeIdentifier($tblYear, TblDivisionCourseType::TYPE_BASIC_COURSE, $tblSchoolType, $list))) {
                $layoutGroups[] = $temp;
            }

            if (empty($layoutGroups)) {
                return new Warning('Keine entsprechenden Kurse gefunden.', new Exclamation());
            } else {
                return new Layout($layoutGroups);
            }
        }

        return new Warning('Bitte wählen sie zunächst ein Schuljahr und eine Schulart aus.');
    }

    /**
     * @param TblYear $tblYear
     * @param string $TypeIdentifier
     * @param TblType $tblSchoolType
     * @param array $list
     *
     * @return false|LayoutGroup
     */
    private function getLayoutGroupForDivisionCoursesSelectByTypeIdentifier(TblYear $tblYear, string $TypeIdentifier, TblType $tblSchoolType, array $list)
    {
        $size = 3;
        $columnList = array();
        $contentPanelList = array();
        $toggleList = array();

        $tblDivisionCourseType = DivisionCourse::useService()->getDivisionCourseTypeByIdentifier($TypeIdentifier);
        $this->setContentPanelListForDivisionCourseType($contentPanelList, $toggleList, $tblYear, $TypeIdentifier, $tblSchoolType, $list);
        if (!empty($contentPanelList)) {
            foreach ($contentPanelList as $divisionCourseId => $content) {
                if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($divisionCourseId))) {
                    if (isset($toggleList[$tblDivisionCourse->getId()])) {
                        array_unshift($content, new ToggleSelective('Alle wählen/abwählen', $toggleList[$tblDivisionCourse->getId()]));
                    }
                    $columnList[] = new LayoutColumn(new Panel($tblDivisionCourse->getName(), $content, Panel::PANEL_TYPE_INFO), $size);
                }
            }

            return new LayoutGroup(
                Grade::useService()->getLayoutRowsByLayoutColumnList($columnList, $size),
                new Title($tblDivisionCourseType->getName() . 'n')
            );
        }

        return false;
    }

    /**
     * @param array $contentPanelList
     * @param array $toggleList
     * @param TblYear $tblYear
     * @param string $TypeIdentifier
     * @param TblType $tblSchoolType
     * @param array $list
     */
    private function setContentPanelListForDivisionCourseType(array &$contentPanelList, array &$toggleList, TblYear $tblYear,
        string $TypeIdentifier, TblType $tblSchoolType, array $list)
    {
        if (($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, $TypeIdentifier))) {
            $tblDivisionCourseList = $this->getSorter($tblDivisionCourseList)->sortObjectBy('Name', new StringNaturalOrderSorter());
            /** @var TblDivisionCourse $tblDivisionCourse */
            foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                if (($tblSchoolTypeList = $tblDivisionCourse->getSchoolTypeListFromStudents())
                    && isset($tblSchoolTypeList[$tblSchoolType->getId()])
                ) {
                    if (($tblSubjectList = DivisionCourse::useService()->getSubjectListByDivisionCourse($tblDivisionCourse))) {
                        $tblSubjectList = $this->getSorter($tblSubjectList)->sortObjectBy('DisplayName');
                        foreach ($tblSubjectList as $tblSubject) {
                            $name = "Data[SubjectDivisionCourses][{$tblDivisionCourse->getId()}][{$tblSubject->getId()}]";
                            $toggleList[$tblDivisionCourse->getId()][$tblSubject->getId()] = $name;
                            $contentPanelList[$tblDivisionCourse->getId()][$tblSubject->getId()]
                                = new CheckBox($name, $tblSubject->getDisplayName() . ($list[$tblDivisionCourse->getId()][$tblSubject->getId()] ?? ''), 1);
                        }
                    }
                }
            }
        }
    }
}