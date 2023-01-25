<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreCondition;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreConditionGroupList;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreGroup;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Equalizer;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\MinusSign;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Quantity;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;

abstract class FrontendScoreCondition extends FrontendMinimumGradeCount
{
    /**
     * @param null $ScoreCondition
     *
     * @return Stage
     */
    public function frontendScoreCondition($ScoreCondition = null): Stage
    {
        $Stage = new Stage('Berechnungsvariante', 'Übersicht');
        $Stage->setMessage(
            'Hier werden die Berechnungsvarianten verwaltet.' . '<br>' .
            'Die Berechnungsvariante bildet die 2. Ebene der Berechnungsvorschriften und setzt sich aus einer Priorität
            , Zensuren-Gruppen und Bedingungen zusammen. <br>
            Die Priorität gibt an, in welcher Reihenfolge die Berechnungsvarianten
            (falls eine Berechnungsvorschrift mehrere Berechnungsvarianten enthält) berücksichtigt werden.
            Dabei hat die Berechnungsvariante mit der niedrigsten Zahl die höchste Priorität. <br>
            Die Bedingungen geben an, ob für die Durchschnittsberechnung die Berechnungsvariante gewählt wird.
            Ist keine Bedingung hinterlegt, wird diese Berechnungsvariante immer gewählt. Hingegen wenn eine oder mehrere
            Bedingung(en) hinterlegt sind, wird diese Berechnungsvariante nur gewählt, wenn alle Bedingungen bei den Zensuren
            des Schülers erfüllt sind.'
        );
        $this->setScoreStageMenuButtons($Stage, self::SCORE_CONDITION);

        $contentTable = array();
        $tblScoreConditionAll = Grade::useService()->getScoreConditionAll(true);
        if ($tblScoreConditionAll) {
            foreach ($tblScoreConditionAll as $tblScoreCondition) {
                $scoreGroups = '';
                $tblScoreGroups = Grade::useService()->getScoreConditionGroupListByCondition($tblScoreCondition);
                if ($tblScoreGroups) {
                    foreach ($tblScoreGroups as $tblScoreGroup) {
                        $scoreGroups .= $tblScoreGroup->getTblScoreGroup()->getName()
                            . new Small(new Muted(' (' . 'Faktor: ' . $tblScoreGroup->getTblScoreGroup()->getDisplayMultiplier() . ')')) . ', ';
                    }
                }
                if (($length = strlen($scoreGroups)) > 2) {
                    $scoreGroups = substr($scoreGroups, 0, $length - 2);
                }

                $requirements = Grade::useService()->getRequirementsForScoreCondition($tblScoreCondition, true);

                $contentTable[] = array(
                    'Status' => $tblScoreCondition->getIsActive()
                        ? new \SPHERE\Common\Frontend\Text\Repository\Success(new PlusSign() . ' aktiv')
                        : new \SPHERE\Common\Frontend\Text\Repository\Warning(new MinusSign() . ' inaktiv'),
                    'Name' => $tblScoreCondition->getName(),
                    'ScoreGroups' => $scoreGroups,
                    'Requirement' => $requirements,
                    'Priority' => $tblScoreCondition->getPriority(),
                    'Option' =>
                        (new Standard('', '/Education/Graduation/Grade/ScoreRule/Condition/Edit', new Edit(),
                            array('Id' => $tblScoreCondition->getId()), 'Bearbeiten')) .
                        ($tblScoreCondition->getIsActive()
                            ? (new Standard('', '/Education/Graduation/Grade/ScoreRule/Condition/Activate',
                                new MinusSign(),
                                array('Id' => $tblScoreCondition->getId()), 'Deaktivieren'))
                            : (new Standard('', '/Education/Graduation/Grade/ScoreRule/Condition/Activate',
                                new PlusSign(),
                                array('Id' => $tblScoreCondition->getId()), 'Aktivieren'))) .
                        ($tblScoreCondition->getIsUsed()
                            ? ''
                            : (new Standard('', '/Education/Graduation/Grade/ScoreRule/Condition/Destroy', new Remove(),
                                array('Id' => $tblScoreCondition->getId()), 'Löschen'))) .
                        ($tblScoreCondition->getIsActive() ?
                            (new Standard('', '/Education/Graduation/Grade/ScoreRule/Condition/Group/Select',
                                new Listing(),
                                array('Id' => $tblScoreCondition->getId()), 'Zensuren-Gruppen auswählen')) .
                            (new Standard('', '/Education/Graduation/Grade/ScoreRule/Condition/GradeType/Select',
                                new Equalizer(),
                                array('Id' => $tblScoreCondition->getId()),
                                'Bedingungen auswählen')) : '')
                );
            }
        }

        $Form = $this->formScoreCondition()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($contentTable, null,
                                array(
                                    'Status' => 'Status',
                                    'Name' => 'Name',
                                    'ScoreGroups' => 'Zensuren-Gruppen',
                                    'Requirement' => 'Bedingungen',
                                    'Priority' => 'Priorität',
//                                'Round' => 'Runden',
                                    'Option' => '',
                                ), array(
                                    'order' => array(
                                        array('0', 'asc'),
                                        array('1', 'asc'),
                                    ),
                                    'columnDefs' => array(
                                        array('type' => 'natural', 'targets' => 4),
                                        array('orderable' => false, 'targets' => -1),
                                    ),

                                )
                            )
                        ))
                    ))
                ), new Title(new ListingTable() . ' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Well(Grade::useService()->createScoreCondition($Form, $ScoreCondition))
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
    private function formScoreCondition(): Form
    {
        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new TextField('ScoreCondition[Name]', 'Klassenarbeit 60% : Rest 40%', 'Name'), 10
                ),
                new FormColumn(
                    new NumberField('ScoreCondition[Priority]', '1', 'Priorität'), 2
                )
            ))
        )));
    }

    /**
     * @param null $Id
     * @param null $ScoreCondition
     *
     * @return Stage|string
     */
    public function frontendEditScoreCondition($Id = null, $ScoreCondition = null)
    {
        $Stage = new Stage('Berechnungsvariante', 'Bearbeiten');
        $Stage->addButton(
            new Standard('Zur&uuml;ck', '/Education/Graduation/Grade/ScoreRule/Condition', new ChevronLeft())
        );

        $tblScoreCondition = Grade::useService()->getScoreConditionById($Id);
        if ($tblScoreCondition) {
            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['ScoreCondition']['Name'] = $tblScoreCondition->getName();
                $Global->POST['ScoreCondition']['Priority'] = $tblScoreCondition->getPriority();

                $Global->savePost();
            }

            $Form = $this->formScoreCondition()
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    'Berechnungsvariante',
                                    $tblScoreCondition->getName(),
                                    Panel::PANEL_TYPE_INFO
                                )
                            ),
                        ))
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Well(Grade::useService()->updateScoreCondition($Form, $Id, $ScoreCondition))
                            ),
                        ))
                    ), new Title(new Edit() . ' Bearbeiten'))
                ))
            );

            return $Stage;
        } else {
            return $Stage . new Danger(new Ban() . ' Berechnungsvariante nicht gefunden')
                . new Redirect('/Education/Graduation/Grade/ScoreRule/Condition', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param null $Id
     *
     * @return string
     */
    public function frontendActivateScoreCondition($Id = null): string
    {
        $Route = '/Education/Graduation/Grade/ScoreRule/Condition';

        $Stage = new Stage('Berechnungsvariante', 'Aktivieren/Deaktivieren');
        $Stage->addButton(
            new Standard('Zur&uuml;ck', $Route, new ChevronLeft())
        );

        if (($tblScoreCondition = Grade::useService()->getScoreConditionById($Id))) {
            $IsActive = !$tblScoreCondition->getIsActive();
            if ((Grade::useService()->setScoreConditionActive($tblScoreCondition, $IsActive))) {
                return $Stage . new Success('Die Berechnungsvariante wurde '
                        . ($IsActive ? 'aktiviert.' : 'deaktiviert.')
                        , new \SPHERE\Common\Frontend\Icon\Repository\Success())
                    . new Redirect($Route, Redirect::TIMEOUT_SUCCESS);
            } else {
                return $Stage . new Danger('Die Berechnungsvariante konnte nicht '
                        . ($IsActive ? 'aktiviert' : 'deaktiviert') . ' werden.'
                        , new Ban())
                    . new Redirect($Route, Redirect::TIMEOUT_ERROR);
            }
        } else {
            return $Stage . new Danger('Berechnungsvariante nicht gefunden.', new Ban())
                . new Redirect($Route, Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendScoreGroupSelect($Id = null): Stage
    {
        $Stage = new Stage('Berechnungsvariante', 'Zensuren-Gruppen auswählen');
        $Stage->addButton(new Standard('Zurück', '/Education/Graduation/Grade/ScoreRule/Condition', new ChevronLeft()));

        if (empty($Id)) {
            $Stage->setContent(new Warning('Die Daten konnten nicht abgerufen werden'));
        } else {
            $tblScoreCondition = Grade::useService()->getScoreConditionById($Id);
            if (!$tblScoreCondition) {
                $Stage->setContent(new Warning('Die Zensuren-Gruppe konnte nicht abgerufen werden'));
            } else {
                $tblScoreConditionGroupListByCondition = Grade::useService()->getScoreConditionGroupListByCondition($tblScoreCondition);
                $tblScoreGroupAll = Grade::useService()->getScoreGroupAll();
                $tblScoreGroupAllByCondition = array();
                if ($tblScoreConditionGroupListByCondition) {
                    /** @var TblScoreConditionGroupList $tblScoreConditionGroup */
                    foreach ($tblScoreConditionGroupListByCondition as $tblScoreConditionGroup) {
                        $tblScoreGroupAllByCondition[] = $tblScoreConditionGroup->getTblScoreGroup();
                    }
                }

                if (!empty($tblScoreGroupAllByCondition) && $tblScoreGroupAll) {
                    $tblScoreGroupAll = array_udiff($tblScoreGroupAll, $tblScoreGroupAllByCondition,
                        function (TblScoreGroup $ObjectA, TblScoreGroup $ObjectB) {

                            return $ObjectA->getId() - $ObjectB->getId();
                        }
                    );
                }

                if ($tblScoreConditionGroupListByCondition) {
                    foreach ($tblScoreConditionGroupListByCondition as &$tblScoreConditionGroupList) {
                        $tblScoreConditionGroupList->Name = $tblScoreConditionGroupList->getTblScoreGroup()->getName()
                            . new Small(new Muted(' (' . 'Faktor: ' . $tblScoreConditionGroupList->getTblScoreGroup()->getDisplayMultiplier()
                                . ($tblScoreConditionGroupList->getTblScoreGroup()->getIsEveryGradeASingleGroup()
                                    ? ', Noten einzeln' : '') . ')'));
                        $tblScoreConditionGroupList->Option =
                            (new \SPHERE\Common\Frontend\Link\Repository\Primary(
                                'Entfernen', '/Education/Graduation/Grade/ScoreRule/Condition/Group/Remove',
                                new Minus(), array(
                                'Id' => $tblScoreConditionGroupList->getId()
                            )))->__toString();
                    }
                }

                if ($tblScoreGroupAll) {
                    foreach ($tblScoreGroupAll as $tblScoreGroup) {
                        $tblScoreGroup->DisplayName = $tblScoreGroup->getName()
                            . new Small(new Muted(' (' . 'Faktor: ' . $tblScoreGroup->getDisplayMultiplier()
                                . ($tblScoreGroup->getIsEveryGradeASingleGroup()
                                    ? ', Noten einzeln' : '') . ')'));
                        $tblScoreGroup->Option =
                            (new \SPHERE\Common\Frontend\Link\Repository\Primary(
                                'Hinzufügen',
                                '/Education/Graduation/Grade/ScoreRule/Condition/Group/Add',
                                new Plus(),
                                array(
                                    'tblScoreGroupId' => $tblScoreGroup->getId(),
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
                                    new Panel('Berechnungsvariante', $tblScoreCondition->getName(),
                                        Panel::PANEL_TYPE_INFO), 12
                                ),
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Title('Ausgewählte', 'Zensuren-Gruppen'),
                                    new TableData($tblScoreConditionGroupListByCondition, null,
                                        array(
                                            'Name' => 'Name',
                                            'Option' => ''
                                        ),
                                        array(
                                            'columnDefs' => array(
                                                array('orderable' => false, 'targets' => -1),
                                            )
                                        )
                                    )
                                ), 6
                                ),
                                new LayoutColumn(array(
                                    new Title('Verfügbare', 'Zensuren-Gruppen'),
                                    new TableData($tblScoreGroupAll, null,
                                        array(
                                            'DisplayName' => 'Name ',
                                            'Option' => ' '
                                        ),
                                        array(
                                            'columnDefs' => array(
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
     * @param null $tblScoreGroupId
     * @param null $tblScoreConditionId
     *
     * @return Stage
     */
    public function frontendScoreGroupAdd($tblScoreGroupId = null, $tblScoreConditionId = null): Stage
    {
        $Stage = new Stage('Berechnungsvariante', 'Zensuren-Gruppe einer Berechnungsvariante hinzufügen');

        $tblScoreGroup = Grade::useService()->getScoreGroupById($tblScoreGroupId);
        $tblScoreCondition = Grade::useService()->getScoreConditionById($tblScoreConditionId);

        if ($tblScoreGroup && $tblScoreCondition) {
            $Stage->setContent(Grade::useService()->addScoreConditionGroupList($tblScoreCondition, $tblScoreGroup));
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendScoreGroupRemove($Id = null): Stage
    {
        $Stage = new Stage('Berechnungsvariante', 'Zensuren-Gruppe von einer Berechnungsvariante entfernen');

        $tblScoreConditionGroupList = Grade::useService()->getScoreConditionGroupListById($Id);
        if ($tblScoreConditionGroupList) {
            $Stage->setContent(Grade::useService()->removeScoreConditionGroupList($tblScoreConditionGroupList));
        }

        return $Stage;
    }

    /**
     * @param $Id
     * @param null $Period
     *
     * @return Stage
     */
    public function frontendScoreConditionGradeTypeSelect($Id = null, $Period = null): Stage
    {
        $Stage = new Stage('Berechnungsvariante', 'Bedingungen auswählen');
        $Stage->addButton(new Standard('Zurück', '/Education/Graduation/Grade/ScoreRule/Condition', new ChevronLeft()));

        if (empty($Id)) {
            $Stage->setContent(new Warning('Die Daten konnten nicht abgerufen werden'));
        } else {
            $tblScoreCondition = Grade::useService()->getScoreConditionById($Id);
            if (empty($tblScoreCondition)) {
                $Stage->setContent(new Warning('Die Berechnungsvariante konnte nicht abgerufen werden'));
            } else {
                $contentSelectedTable = array();
                $contentAvailableTable = array();
                $tblGradeTypeAllByCondition = array();
                if (($tblScoreConditionGradeTypeListByCondition = Grade::useService()->getScoreConditionGradeTypeListByCondition($tblScoreCondition))) {
                    foreach ($tblScoreConditionGradeTypeListByCondition as $tblScoreConditionGradeType) {
                        if ($tblGradeType = $tblScoreConditionGradeType->getTblGradeType()) {
                            $tblGradeTypeAllByCondition[$tblGradeType->getId()] = $tblGradeType;
                            $contentSelectedTable[] = array(
                                'Name' => $tblGradeType->getDisplayName(),
                                'Count' => $tblScoreConditionGradeType->getCount(),
                                'Option' => (new \SPHERE\Common\Frontend\Link\Repository\Primary(
                                    'Entfernen', '/Education/Graduation/Grade/ScoreGroup/Condition/GradeType/Remove',
                                    new Minus(), array(
                                    'tblScoreConditionGradeTypeId' => $tblScoreConditionGradeType->getId()
                                )))->__toString()
                            );
                        }
                    }
                }

                $tblScoreGroupRequirementAllByCondition = array();
                if (($tblScoreGroupRequirementList = Grade::useService()->getScoreConditionGroupRequirementAllByCondition($tblScoreCondition))) {
                    foreach ($tblScoreGroupRequirementList as $tblScoreConditionGroupRequirement) {
                        if (($tblScoreGroup = $tblScoreConditionGroupRequirement->getTblScoreGroup())) {
                            $tblScoreGroupRequirementAllByCondition[$tblScoreGroup->getId()] = $tblScoreGroup;
                            $contentSelectedTable[] = array(
                                'Name' => 'Zensuren-Gruppe: ' . $tblScoreGroup->getName(),
                                'Count' => $tblScoreConditionGroupRequirement->getCount(),
                                'Option' => (new \SPHERE\Common\Frontend\Link\Repository\Primary(
                                    'Entfernen', '/Education/Graduation/Grade/ScoreRule/Condition/GradeType/Remove',
                                    new Minus(), array(
                                    'tblScoreConditionGroupRequirementId' => $tblScoreConditionGroupRequirement->getId()
                                )))->__toString()
                            );
                        }
                    }
                }

                if (($tblGradeTypeList = Grade::useService()->getGradeTypeAllByScoreCondition($tblScoreCondition))){
                    foreach ($tblGradeTypeList as $tblGradeType) {
                        if (!isset($tblGradeTypeAllByCondition[$tblGradeType->getId()])) {
                            $contentAvailableTable[] = array(
                                'Name' => $tblGradeType->getDisplayName(),
                                'Option' =>
                                    (new Form(
                                        new FormGroup(
                                            new FormRow(array(
                                                new FormColumn(
                                                    new TextField('GradeType[Count]', 'Anzahl', '', new Quantity()
                                                    )
                                                    , 7),
                                                new FormColumn(
                                                    new Primary('Hinzufügen',
                                                        new Plus())
                                                    , 5)
                                            ))
                                        ), null,
                                        '/Education/Graduation/Grade/ScoreRule/Condition/GradeType/Add', array(
                                            'tblScoreConditionId' => $tblScoreCondition->getId(),
                                            'tblGradeTypeId' => $tblGradeType->getId()
                                        )
                                    ))->__toString()
                            );
                        }
                    }
                }
                if (($tblScoreConditionGroupList = Grade::useService()->getScoreConditionGroupListByCondition($tblScoreCondition))) {
                    foreach ($tblScoreConditionGroupList as $item) {
                        if (($tblScoreGroup = $item->getTblScoreGroup())) {
                            if (!isset($tblScoreGroupRequirementAllByCondition[$tblScoreGroup->getId()])) {
                                $contentAvailableTable[] = array(
                                    'Name' => 'Zensuren-Gruppe: ' . $tblScoreGroup->getName(),
                                    'Option' =>
                                        (new Form(
                                            new FormGroup(
                                                new FormRow(array(
                                                    new FormColumn(
                                                        new TextField('GradeType[Count]', 'Anzahl', '', new Quantity()
                                                        )
                                                        , 7),
                                                    new FormColumn(
                                                        new Primary('Hinzufügen',
                                                            new Plus())
                                                        , 5)
                                                ))
                                            ), null,
                                            '/Education/Graduation/Grade/ScoreRule/Condition/GradeType/Add', array(
                                                'tblScoreConditionId' => $tblScoreCondition->getId(),
                                                'tblScoreGroupId' => $tblScoreGroup->getId()
                                            )
                                        ))->__toString()
                                );
                            }
                        }
                    }
                }

                if ($Period == null) {
                    $global = $this->getGlobal();
                    $valuePeriod = $tblScoreCondition->getPeriod();
                    $global->POST['Period'] = $valuePeriod == null ? TblScoreCondition::PERIOD_FULL_YEAR : $valuePeriod;
                    $global->savePost();
                }

                $periodList[] = new SelectBoxItem(TblScoreCondition::PERIOD_FULL_YEAR, '-Gesamtes Schuljahr-');
                $periodList[] = new SelectBoxItem(TblScoreCondition::PERIOD_FIRST_PERIOD, '1. Halbjahr');
                $periodList[] = new SelectBoxItem(TblScoreCondition::PERIOD_SECOND_PERIOD, '2. Halbjahr');

                $form = new Form(new FormGroup(new FormRow(new FormColumn(
                    new SelectBox('Period', 'Zeitraum',
                        array('{{ Name }}' => $periodList))
                ))));
                $form->appendFormButton(new Primary('Speichern', new Save()));

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel('Berechnungsvariante', $tblScoreCondition->getName(),
                                        Panel::PANEL_TYPE_INFO),
                                    12
                                ),
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Well (
                                        Grade::useService()->updateScoreConditionRequirementPeriod($form, $tblScoreCondition, $Period)
                                    )
                                ),
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Title('Ausgewählte', 'Bedingungen'),
                                    new TableData($contentSelectedTable, null,
                                        array(
                                            'Name' => 'Name',
                                            'Count' => 'Anzahl',
                                            'Option' => ''
                                        ),
                                        array(
                                            'columnDefs' => array(
                                                array('orderable' => false, 'targets' => -1),
                                            )
                                        )
                                    )
                                ), 6),
                                new LayoutColumn(array(
                                    new Title('Verfügbare', 'Bedingungen'),
                                    empty($contentAvailableTable)
                                        ? new Warning('Keine Bedingungen (Zensurengruppen) verfügbar', new Ban())
                                        : new TableData($contentAvailableTable, null,
                                        array(
                                            'Name' => 'Name ',
                                            'Option' => ' '
                                        ),
                                        array(
                                            'columnDefs' => array(
                                                array('orderable' => false, 'targets' => -1),
                                            )
                                        )
                                    )
                                ), 6)
                            )),
                        )),
                    ))
                );
            }
        }

        return $Stage;
    }

    /**
     * @param null $tblScoreConditionId
     * @param null $tblGradeTypeId
     * @param null $tblScoreGroupId
     * @param null $GradeType
     *
     * @return Stage|string
     */
    public function frontendScoreConditionGradeTypeAdd($tblScoreConditionId = null, $tblGradeTypeId = null, $tblScoreGroupId = null, $GradeType = null)
    {
        $Stage = new Stage('Berechnungsvariante (Bedingungen)', 'Bedingung einer Berechnungsvariante hinzufügen');

        if (($tblScoreCondition = Grade::useService()->getScoreConditionById($tblScoreConditionId))) {
            if (isset($GradeType['Count']) && $GradeType['Count'] == '') {
                $count = 1;
            } elseif (isset($GradeType['Count']) && !preg_match(Grade::useService()::PREG_MATCH_DECIMAL_NUMBER, $GradeType['Count'])) {
                return $Stage
                    . new Warning('Bitte geben Sie als Faktor eine Zahl an. Der Zensuren-Type wurde nicht hinzugefügt.', new Exclamation())
                    . new Redirect('/Education/Graduation/Grade/ScoreRule/Condition/GradeType/Select', Redirect::TIMEOUT_ERROR,
                        array('Id' => $tblScoreCondition->getId()));
            } else {
                $count = $GradeType['Count'] ?? 1;
            }

            if (($tblGradeType = Grade::useService()->getGradeTypeById($tblGradeTypeId))) {
                $Stage->setContent(Grade::useService()->addScoreConditionGradeTypeList($tblGradeType, $tblScoreCondition, $count));
            } elseif (($tblScoreGroup = Grade::useService()->getScoreGroupById($tblScoreGroupId))) {
                $Stage->setContent(Grade::useService()->addScoreConditionGroupRequirement($tblScoreGroup, $tblScoreCondition, $count));
            }
        }

        return $Stage;
    }

    /**
     * @param null $tblScoreConditionGradeTypeId
     * @param null $tblScoreConditionGroupRequirementId
     *
     * @return Stage
     */
    public function frontendScoreConditionGradeTypeRemove($tblScoreConditionGradeTypeId = null, $tblScoreConditionGroupRequirementId = null): Stage
    {
        $Stage = new Stage('Berechnungsvariante (Bedingungen)', 'Zensuren-Typ von einer Berechnungsvariante entfernen');

        if (($tblScoreConditionGradeTypeList = Grade::useService()->getScoreConditionGradeTypeListById($tblScoreConditionGradeTypeId))) {
            $Stage->setContent(Grade::useService()->removeScoreConditionGradeTypeList($tblScoreConditionGradeTypeList));
        } elseif (($tblScoreConditionGroupRequirement = Grade::useService()->getScoreConditionGroupRequirementById($tblScoreConditionGroupRequirementId))) {
            $Stage->setContent(Grade::useService()->removeScoreConditionGroupRequirement($tblScoreConditionGroupRequirement));
        }

        return $Stage;
    }
}