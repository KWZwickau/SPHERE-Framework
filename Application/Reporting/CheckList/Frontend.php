<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 01.12.2015
 * Time: 10:33
 */

namespace SPHERE\Application\Reporting\CheckList;

use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;

class Frontend
{
    /**
     * @param null $List
     * @return Stage
     */
    public function frontendList($List = null)
    {
        $Stage = new Stage('Check-Listen', 'Übersicht');

        $tblListAll = CheckList::useService()->getListAll();

        if ($tblListAll) {
            foreach ($tblListAll as &$tblList) {
                $tblList->Option =
                    (new Standard('', '/Reporting/CheckList/Element/Select', new Listing(),
                        array('Id' => $tblList->getId()), 'Elemente (CheckBox, Datum ...) bearbeiten'))
                    . (new Standard('', '/Reporting/CheckList/Group/Select', new Listing(),
                        array('Id' => $tblList->getId()), 'Objekte (Personen, Firmen) bearbeiten'));
            }
        }

        $Form = $this->formList()
            ->appendFormButton(new Primary('Hinzufügen', new Plus()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($tblListAll, null, array(
                                'Name' => 'Name',
                                'Description' => 'Beschreibung',
                                'Option' => 'Optionen',
                            ))
                        ))
                    ))
                ), new Title('Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            CheckList::useService()->createList($Form, $List)
                        ))
                    ))
                ), new Title('Hinzufügen'))
            ))
        );

        return $Stage;
    }

    private function formList()
    {
        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new TextField('List[Name]', 'Name', 'Name'), 12
                ),
                new FormColumn(
                    new TextField('List[Description]', 'Beschreibung', 'Beschreibung'), 12
                )
            ))
        )));
    }

    /**
     * @param null $Id
     * @param null $Element
     * @return Stage
     */
    public function frontendListElementSelect($Id = null, $Element = null)
    {

        $Stage = new Stage('Check-Listen', 'Elemente einer Check-Liste zuordnen');

        $Stage->addButton(new Standard('Zurück', '/Reporting/CheckList', new ChevronLeft()));

        if (empty($Id)) {
            $Stage->setContent(new Warning('Die Daten konnten nicht abgerufen werden'));
        } else {
            $tblList = CheckList::useService()->getListById($Id);
            if (empty($tblList)) {
                $Stage->setContent(new Warning('Die Check-Liste konnte nicht abgerufen werden'));
            } else {
                $tblListElementListByList = CheckList::useService()->getListElementListByList($tblList);

                if ($tblListElementListByList) {
                    foreach ($tblListElementListByList as &$tblListElementList) {
                        $tblListElementList->Type = $tblListElementList->getTblElementType()->getName();
                        $tblListElementList->Option =
                            (new Danger('Entfernen', '/Reporting/CheckList/Element/Remove',
                                new Minus(), array(
                                    'Id' => $tblListElementList->getId()
                                )))->__toString();
                    }
                }

                $Form = $this->formElement()
                    ->appendFormButton(new Primary('Hinzufügen', new Plus()))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel('Check-Liste', $tblList->getName(), Panel::PANEL_TYPE_SUCCESS),
                                    12
                                ),
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new TableData($tblListElementListByList, null,
                                        array(
                                            'Name' => 'Name',
                                            'Type' => 'Typ',
                                            'Option' => 'Option'
                                        )
                                    )
                                ))
                            ))
                        ), new Title('Übersicht')),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    CheckList::useService()->addElementToList($Form, $Id, $Element)
                                ))
                            ))
                        ), new Title('Hinzufügen'))
                    ))
                );
            }
        }

        return $Stage;
    }

    private function formElement()
    {

        $tblElementTypeAll = CheckList::useService()->getElementTypeAll();

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new TextField('Element[Name]', 'Name', 'Name'), 6
                ),
                new FormColumn(
                    new SelectBox('Element[Type]', 'Typ', array('{{ Name }}' => $tblElementTypeAll)), 6
                ),
            ))
        )));
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendListElementRemove($Id)
    {

        return CheckList::useService()->removeElementFromList($Id);
    }

//    /**
//     * @param null $Id
//     * @param null $Group
//     * @return Stage
//     */
//    public function frontendListGroupSelect($Id = null, $Group = null)
//    {
//
//        $Stage = new Stage('Check-Listen', 'Eine Gruppe einer Check-Liste zuordnen');
//
//        $Stage->addButton(new Standard('Zurück', '/Reporting/CheckList', new ChevronLeft()));
//
//        if (empty($Id)) {
//            $Stage->setContent(new Warning('Die Daten konnten nicht abgerufen werden'));
//        } else {
//            $tblList = CheckList::useService()->getListById($Id);
//            if (empty($tblList)) {
//                $Stage->setContent(new Warning('Die Check-Liste konnte nicht abgerufen werden'));
//            } else {
//                $tblListObjectListByListWhereGroup = CheckList::useService()->getListObjectListByListWhereGroup($tblList);
//                if ($tblListObjectListByListWhereGroup) {
//                    foreach ($tblListObjectListByListWhereGroup as &$tblListObjectList) {
//                        $tblListObjectList->Name = $tblListObjectList->getServiceTblGroup()->getName();
//                        $tblListObjectList->Option =
//                            (new Danger('Entfernen', '/Reporting/CheckList/Group/Remove',
//                                new Minus(), array(
//                                    'Id' => $tblListObjectList->getId()
//                                )))->__toString();
//                    }
//                }
//
//                if ($tblList->getTblListType()->getIdentifier() === 'PERSON') {
//                    $groupList = Group::useService()->getGroupAll();
//                } else {
//                    $groupList = \SPHERE\Application\Corporation\Group\Group::useService()->getGroupAll();
//                }
//                $Form = $this->formGroup($groupList)
//                    ->appendFormButton(new Primary('Hinzufügen', new Plus()))
//                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
//
//                $Stage->setContent(
//                    new Layout(array(
//                        new LayoutGroup(array(
//                            new LayoutRow(array(
//                                new LayoutColumn(
//                                    new Panel('Check-Liste', $tblList->getName(), Panel::PANEL_TYPE_SUCCESS),
//                                    12
//                                ),
//                            ))
//                        )),
//                        new LayoutGroup(array(
//                            new LayoutRow(array(
//                                new LayoutColumn(array(
//                                    new TableData($tblListObjectListByListWhereGroup, null,
//                                        array(
//                                            'Name' => 'Name',
//                                            'Type' => 'Typ',
//                                            'Option' => 'Option'
//                                        )
//                                    )
//                                ))
//                            ))
//                        ), new Title('Übersicht')),
//                        new LayoutGroup(array(
//                            new LayoutRow(array(
//                                new LayoutColumn(array(
//                                    CheckList::useService()->addGroupToList($Form, $Id, $Group)
//                                ))
//                            ))
//                        ), new Title('Hinzufügen'))
//                    ))
//                );
//            }
//        }
//
//        return $Stage;
//    }
//
//    private function formGroup($groupList)
//    {
//
//        return new Form(new FormGroup(array(
//            new FormRow(array(
//                new FormColumn(
//                    new SelectBox('Group[Group]', 'Typ', array('{{ Name }}' => $groupList)), 12
//                ),
//            ))
//        )));
//    }
//
//    /**
//     * @param $Id
//     *
//     * @return Stage
//     */
//    public function frontendListGroupRemove($Id)
//    {
//
////        return CheckList::useService()->removeGroupFromList($Id);
//    }
}