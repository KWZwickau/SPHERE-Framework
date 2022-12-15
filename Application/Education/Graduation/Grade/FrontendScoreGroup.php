<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreGroupGradeTypeList;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\MinusSign;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Quantity;
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
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Italic;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;

abstract class FrontendScoreGroup extends FrontendScoreCondition
{
    /**
     * @param null $ScoreGroup
     *
     * @return Stage
     */
    public function frontendScoreGroup($ScoreGroup = null): Stage
    {
        $Stage = new Stage('Zensuren-Gruppe', 'Übersicht');
        $Stage->setMessage(
            'Hier werden die Zensuren-Gruppen verwaltet.' . '<br>' .
            'Die Zensuren-Gruppe bildet die 3. Ebene der Berechnungsvorschriften und setzt sich aus einem Faktor
            und Zensuren-Typen zusammen.' . '<br>' .
            'Der Faktor gibt an, wie die Zensuren-Gruppe als ganzes zu anderen Zensuren-Gruppen gewichtet wird.' . '<br>' .
            'Über die Option ' . new Italic('Noten einzeln')
            . ' werden alle Noten dieser Zensurengruppe nicht zu einem Durchschnitt zusammen gerechnet, sondern alle Noten dieser Gruppe einzeln gewertet.'
        );
        $this->setScoreStageMenuButtons($Stage, self::GRADE_GROUP);

        $contentTable = array();
        $tblScoreGroupAll = Grade::useService()->getScoreGroupAll(true);
        if ($tblScoreGroupAll) {
            foreach ($tblScoreGroupAll as $tblScoreGroup) {
                $gradeTypes = '';
                $tblScoreGroupGradeTypes = Grade::useService()->getScoreGroupGradeTypeListByGroup($tblScoreGroup);
                if ($tblScoreGroupGradeTypes) {
                    foreach ($tblScoreGroupGradeTypes as $tblScoreGroupGradeType) {
                        if (($tblGradeType = $tblScoreGroupGradeType->getTblGradeType())) {
                            $gradeTypes .= $tblGradeType->getDisplayName()
                                . new Small(new Muted(' (' . 'Faktor: ' . $tblScoreGroupGradeType->getDisplayMultiplier() . ')')) . ', ';
                        }
                    }
                }
                if (($length = strlen($gradeTypes)) > 2) {
                    $gradeTypes = substr($gradeTypes, 0, $length - 2);
                }

                $contentTable[] = array(
                    'Status' => $tblScoreGroup->getIsActive()
                        ? new Success(new PlusSign() . ' aktiv')
                        : new Warning(new MinusSign() . ' inaktiv'),
                    'Name' => $tblScoreGroup->getName(),
                    'Multiplier' => $tblScoreGroup->getMultiplier(),
                    'GradeTypes' => $gradeTypes,
                    'IsEveryGradeASingleGroup' => $tblScoreGroup->getIsEveryGradeASingleGroup() ? 'Ja' : new Muted('Nein'),
                    'Option' =>
                        (new Standard('', '/Education/Graduation/Grade/ScoreRule/Group/Edit', new Edit(),
                            array('Id' => $tblScoreGroup->getId()), 'Bearbeiten'))
                        . ($tblScoreGroup->getIsActive()
                            ? (new Standard('', '/Education/Graduation/Grade/ScoreRule/Group/Activate', new MinusSign(),
                                array('Id' => $tblScoreGroup->getId()), 'Deaktivieren'))
                            : (new Standard('', '/Education/Graduation/Grade/ScoreRule/Group/Activate', new PlusSign(),
                                array('Id' => $tblScoreGroup->getId()), 'Aktivieren')))
                        . ($tblScoreGroup->getIsUsed()
                            ? ''
                            : (new Standard('', '/Education/Graduation/Grade/ScoreRule/Group/Destroy', new Remove(),
                                array('Id' => $tblScoreGroup->getId()), 'Löschen')))
                        . ($tblScoreGroup->getIsActive() ?
                            (new Standard('', '/Education/Graduation/Grade/ScoreRule/Group/GradeType/Select',
                                new Listing(),
                                array('Id' => $tblScoreGroup->getId()), 'Zensuren-Typen auswählen')) : '')
                );
            }
        }

        $Form = $this->formScoreGroup()
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
                                'Multiplier' => 'Faktor',
                                'GradeTypes' => 'Zensuren-Typen',
                                'IsEveryGradeASingleGroup' => 'Noten einzeln',
                                'Option' => '',
                            ), array(
                                'order' => array(
                                    array('0', 'asc'),
                                    array('1', 'asc'),
                                ),
                                'columnDefs' => array(
                                    array('type' => 'natural', 'targets' => 2),
                                    array('orderable' => false, 'targets' => -1),
                                ),
                            ))
                        ))
                    ))
                ), new Title(new ListingTable() . ' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Well(Grade::useService()->createScoreGroup($Form, $ScoreGroup))
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
    private function formScoreGroup(): Form
    {
        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new TextField('ScoreGroup[Name]', '', 'Name'), 10
                ),
                new FormColumn(
                    new TextField('ScoreGroup[Multiplier]', '', 'Faktor'), 2
                ),
            )),
            new FormRow(array(
                new FormColumn(
                    new CheckBox('ScoreGroup[IsEveryGradeASingleGroup]',
                        'Noten einzeln (Noten dieser Gruppe als eigene Gruppe betrachten)', 1)
                )
            ))
        )));
    }

    /**
     * @param null $Id
     * @param null $ScoreGroup
     * @return Stage|string
     */
    public function frontendEditScoreGroup($Id = null, $ScoreGroup = null)
    {
        $Stage = new Stage('Zensuren-Gruppe', 'Bearbeiten');
        $Stage->addButton(
            new Standard('Zur&uuml;ck', '/Education/Graduation/Grade/ScoreRule/Group', new ChevronLeft())
        );

        $tblScoreGroup = Grade::useService()->getScoreGroupById($Id);
        if ($tblScoreGroup) {
            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['ScoreGroup']['Name'] = $tblScoreGroup->getName();
                $Global->POST['ScoreGroup']['Multiplier'] = $tblScoreGroup->getDisplayMultiplier();
                $Global->POST['ScoreGroup']['IsEveryGradeASingleGroup'] = $tblScoreGroup->getIsEveryGradeASingleGroup();

                $Global->savePost();
            }

            $Form = $this->formScoreGroup()
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    'Zensuren-Gruppe',
                                    $tblScoreGroup->getName(),
                                    Panel::PANEL_TYPE_INFO
                                )
                            ),
                        ))
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Well(Grade::useService()->updateScoreGroup($Form, $Id, $ScoreGroup))
                            ),
                        ))
                    ), new Title(new Edit() . ' Bearbeiten'))
                ))
            );

            return $Stage;
        } else {
            return $Stage . new Danger(new Ban() . ' Zensuren-Gruppe nicht gefunden')
                . new Redirect('/Education/Graduation/Grade/ScoreRule/Group', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param null $Id
     *
     * @return string
     */
    public function frontendActivateScoreGroup($Id = null): string
    {
        $Route = '/Education/Graduation/Grade/ScoreRule/Group';

        $Stage = new Stage('Zensuren-Gruppe', 'Aktivieren/Deaktivieren');
        $Stage->addButton(
            new Standard('Zur&uuml;ck', $Route, new ChevronLeft())
        );

        if (($tblScoreGroup = Grade::useService()->getScoreGroupById($Id))) {
            $IsActive = !$tblScoreGroup->getIsActive();
            if ((Grade::useService()->setScoreGroupActive($tblScoreGroup, $IsActive))) {
                return $Stage . new \SPHERE\Common\Frontend\Message\Repository\Success('Die Zensuren-Gruppe wurde '
                        . ($IsActive ? 'aktiviert.' : 'deaktiviert.')
                        , new \SPHERE\Common\Frontend\Icon\Repository\Success())
                    . new Redirect($Route, Redirect::TIMEOUT_SUCCESS);
            } else {
                return $Stage . new Danger('Die Zensuren-Gruppe konnte nicht '
                        . ($IsActive ? 'aktiviert' : 'deaktiviert') . ' werden.'
                        , new Ban())
                    . new Redirect($Route, Redirect::TIMEOUT_ERROR);
            }
        } else {
            return $Stage . new Danger('Zensuren-Gruppe nicht gefunden.', new Ban())
                . new Redirect($Route, Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendScoreGroupGradeTypeSelect($Id = null): Stage
    {
        $Stage = new Stage('Zensuren-Gruppe', 'Zensuren-Typen auswählen');

        $Stage->addButton(new Standard('Zurück', '/Education/Graduation/Grade/ScoreRule/Group', new ChevronLeft()));

        if (empty($Id)) {
            $Stage->setContent(new \SPHERE\Common\Frontend\Message\Repository\Warning('Die Daten konnten nicht abgerufen werden'));
        } else {
            $tblScoreGroup = Grade::useService()->getScoreGroupById($Id);
            if (empty($tblScoreGroup)) {
                $Stage->setContent(new Warning('Die Zensuren-Gruppe konnte nicht abgerufen werden'));
            } else {
                $tblScoreGroupGradeTypeListByGroup = Grade::useService()->getScoreGroupGradeTypeListByGroup($tblScoreGroup);
                $tblGradeTypeAll = Grade::useService()->getGradeTypeList();
                $tblGradeTypeAllByGroup = array();
                if ($tblScoreGroupGradeTypeListByGroup) {
                    /** @var TblScoreGroupGradeTypeList $tblScoreGroupGradeType */
                    foreach ($tblScoreGroupGradeTypeListByGroup as $tblScoreGroupGradeType) {
                        if ($tblScoreGroupGradeType->getTblGradeType()) {
                            $tblGradeTypeAllByGroup[] = $tblScoreGroupGradeType->getTblGradeType();
                        }
                    }
                }

                if (!empty($tblGradeTypeAllByGroup) && $tblGradeTypeAll) {
                    $tblGradeTypeAll = array_udiff($tblGradeTypeAll, $tblGradeTypeAllByGroup,
                        function (TblGradeType $ObjectA, TblGradeType $ObjectB) {

                            return $ObjectA->getId() - $ObjectB->getId();
                        }
                    );
                }

                $contentSelectedTable = array();
                if ($tblScoreGroupGradeTypeListByGroup) {
                    /** @var TblScoreGroupGradeTypeList $tblScoreGroupGradeTypeList */
                    foreach ($tblScoreGroupGradeTypeListByGroup as $tblScoreGroupGradeTypeList) {

                        if ($tblScoreGroupGradeTypeList->getTblGradeType()) {
                            $contentSelectedTable[] = array(
                                'Name' => $tblScoreGroupGradeTypeList->getTblGradeType()->getDisplayName(),
                                'DisplayMultiplier' => $tblScoreGroupGradeTypeList->getDisplayMultiplier(),
                                'Option' =>
                                    (new \SPHERE\Common\Frontend\Link\Repository\Primary(
                                        'Entfernen', '/Education/Graduation/Grade/ScoreRule/Group/GradeType/Remove',
                                        new Minus(), array(
                                        'Id' => $tblScoreGroupGradeTypeList->getId()
                                    )))->__toString()
                            );
                        }
                    }
                }

                $contentAvailableTable = array();
                if ($tblGradeTypeAll) {
                    foreach ($tblGradeTypeAll as $tblGradeType) {
                        $contentAvailableTable[] = array(
                            'Name' => $tblGradeType->getDisplayName(),
                            'Option' =>
                                (new Form(
                                    new FormGroup(
                                        new FormRow(array(
                                            new FormColumn(
                                                new TextField('GradeType[Multiplier]', 'Faktor', '', new Quantity()
                                                )
                                                , 7),
                                            new FormColumn(
                                                new Primary('Hinzufügen',
                                                    new Plus())
                                                , 5)
                                        ))
                                    ), null,
                                    '/Education/Graduation/Grade/ScoreRule/Group/GradeType/Add', array(
                                        'tblScoreGroupId' => $tblScoreGroup->getId(),
                                        'tblGradeTypeId' => $tblGradeType->getId()
                                    )
                                ))->__toString()
                        );
                    }
                }

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel('Zensuren-Gruppe',
                                        $tblScoreGroup->getName()
                                        . new Small(new Muted(' (' . 'Faktor: ' . $tblScoreGroup->getDisplayMultiplier() . ')')),
                                        Panel::PANEL_TYPE_INFO),
                                    12
                                ),
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Title('Ausgewählte', 'Zensuren-Typen'),
                                    new TableData($contentSelectedTable, null,
                                        array(
                                            'Name' => 'Name',
                                            'DisplayMultiplier' => 'Faktor',
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
                                    new Title('Verfügbare', 'Zensuren-Typen'),
                                    new TableData($contentAvailableTable, null,
                                        array(
                                            'Name' => 'Name',
                                            'Option' => 'Faktor'
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
     * @param null $tblGradeTypeId
     * @param null $GradeType
     *
     * @return Stage|string
     */
    public function frontendScoreGroupGradeTypeAdd($tblScoreGroupId = null, $tblGradeTypeId = null, $GradeType = null)
    {
        $Stage = new Stage('Zensuren-Berechnung', 'Zensuren-Typ einer Zenuseren-Gruppe hinzufügen');

        if ($tblScoreGroupId === null || $tblGradeTypeId === null) {
            return $Stage;
        }

        $tblScoreGroup = Grade::useService()->getScoreGroupById($tblScoreGroupId);
        $tblGradeType = Grade::useService()->getGradeTypeById($tblGradeTypeId);

        if (isset($GradeType['Multiplier']) && $GradeType['Multiplier'] == '') {
            $multiplier = 1;
        } elseif (isset($GradeType['Multiplier']) && !preg_match(Grade::useService()::PREG_MATCH_DECIMAL_NUMBER, $GradeType['Multiplier'])) {
            return $Stage
                . new \SPHERE\Common\Frontend\Message\Repository\Warning(
                    'Bitte geben Sie als Faktor eine Zahl an. Der Zensuren-Type wurde nicht hinzugefügt.', new Exclamation()
                )
                . new Redirect('/Education/Graduation/Grade/ScoreRule/Group/GradeType/Select', Redirect::TIMEOUT_ERROR,
                    array('Id' => $tblScoreGroup->getId()));
        } else {
            $multiplier = $GradeType['Multiplier'];
        }

        if ($tblScoreGroup && $tblGradeType) {
            $Stage->setContent(Grade::useService()->addScoreGroupGradeTypeList($tblGradeType, $tblScoreGroup, $multiplier));
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendScoreGroupGradeTypeRemove($Id = null): Stage
    {
        $Stage = new Stage('Zensuren-Berechnung', 'Zensuren-Typ von einer Zenuseren-Gruppe entfernen');

        $tblScoreGroupGradeTypeList = Grade::useService()->getScoreGroupGradeTypeListById($Id);
        if ($tblScoreGroupGradeTypeList) {
            $Stage->setContent(Grade::useService()->removeScoreGroupGradeTypeList($tblScoreGroupGradeTypeList));
        }

        return $Stage;
    }

    /**
     * @param null $Id
     * @param bool|false $Confirm
     *
     * @return Stage|string
     */
    public function frontendDestroyScoreGroup($Id = null, $Confirm = false)
    {
        $Route = '/Education/Graduation/Grade/ScoreRule/Group';

        $Stage = new Stage('Zensuren-Gruppe', 'Löschen');
        $Stage->addButton(
            new Standard('Zur&uuml;ck', $Route, new ChevronLeft())
        );

        if (($tblScoreGroup = Grade::useService()->getScoreGroupById($Id))) {
            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel('Zensuren-Gruppe', $tblScoreGroup->getName(), Panel::PANEL_TYPE_INFO),
                        new Panel(new Question() . ' Diese Zensuren-Gruppe wirklich löschen?', array($tblScoreGroup->getName()), Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', $Route . '/Destroy', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            . new Standard(
                                'Nein', $Route, new Disable())
                        )
                    )))))
                );
            } else {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            (Grade::useService()->destroyScoreGroup($tblScoreGroup)
                                ? new \SPHERE\Common\Frontend\Message\Repository\Success(new \SPHERE\Common\Frontend\Icon\Repository\Success()
                                    . ' Die Zensuren-Gruppe wurde gelöscht')
                                : new Danger(new Ban() . ' Die Zensuren-Gruppe konnte nicht gelöscht werden')
                            ),
                            new Redirect($Route, Redirect::TIMEOUT_SUCCESS)
                        )))
                    )))
                );
            }

            return $Stage;

        } else {
            return $Stage . new Danger('Zensuren-Gruppe nicht gefunden.', new Ban())
                . new Redirect($Route, Redirect::TIMEOUT_ERROR);
        }
    }
}