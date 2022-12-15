<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreCondition;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreRuleConditionList;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
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
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;

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
                            (new Standard('', '/Education/Graduation/Grade/ScoreRule/Division', new Equalizer(),
                                array('Id' => $tblScoreRule->getId()), 'Fach-Klassen zuordnen')) .
                            (new Standard('', '/Education/Graduation/Grade/ScoreRule/SubjectGroup', new Group(),
                                array('Id' => $tblScoreRule->getId()), 'Fachgruppen zuordnen'))
                            : '')
                );
            }
        }

        $Form = $this->formScoreRule()
            ->appendFormButton(new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Speichern', new Save()))
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
}