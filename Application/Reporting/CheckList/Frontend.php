<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 01.12.2015
 * Time: 10:33
 */

namespace SPHERE\Application\Reporting\CheckList;

use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Corporation\Group\Service\Entity\TblGroup as CompanyGroupEntity;
use SPHERE\Application\People\Group\Service\Entity\TblGroup as PersonGroupEntity;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
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
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\Application\People\Group\Group as PersonGroup;
use SPHERE\Application\Corporation\Group\Group as CompanyGroup;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Debugger;

class Frontend extends Extension implements IFrontendInterface
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
                        array('Id' => $tblList->getId()), 'Elemente (CheckBox, Datum ...) auswählen'))
                    . (new Standard('', '/Reporting/CheckList/Object/Select', new Listing(),
                        array('ListId' => $tblList->getId()), 'Objekte (Personen, Firmen) auswählen'))
                    . (new Standard('', '/Reporting/CheckList/Object/Element/Edit', new Edit(),
                        array('Id' => $tblList->getId()), 'Bearbeiten'));
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

    /**
     * @param null $ListId
     * @param null $ObjectTypeId
     * @param null $ObjectTypeSelect
     * @param null $Object
     *
     * @return Stage
     */
    public function frontendListObjectSelect(
        $ListId = null,
        $ObjectTypeId = null,
        $ObjectTypeSelect = null,
        $Object = null
    ) {

        $Stage = new Stage('Check-Listen', 'Eine Gruppe einer Check-Liste zuordnen');

        $Stage->addButton(new Standard('Zurück', '/Reporting/CheckList', new ChevronLeft()));

        if (empty($ListId)) {
            $Stage->setContent(new Warning('Die Daten konnten nicht abgerufen werden'));
        } else {
            $tblList = CheckList::useService()->getListById($ListId);
            if (empty($tblList)) {
                $Stage->setContent(new Warning('Die Check-Liste konnte nicht abgerufen werden'));
            } else {

                $tblListObjectListByList = CheckList::useService()->getListObjectListByList($tblList);
                if ($tblListObjectListByList) {
                    foreach ($tblListObjectListByList as &$tblListObjectList) {
                        if (($tblObject = $tblListObjectList->getServiceTblObject())) {
                            if ($tblListObjectList->getTblObjectType()->getIdentifier() === 'PERSON') {
                                /** @var TblPerson $tblObject */
                                $tblListObjectList->Name = $tblObject->getFullName();
                            } elseif ($tblListObjectList->getTblObjectType()->getIdentifier() === 'COMPANY') {
                                /** @var TblCompany $tblObject */
                                $tblListObjectList->Name = $tblObject->getName();
                            } elseif ($tblListObjectList->getTblObjectType()->getIdentifier() === 'PERSONGROUP') {
                                /** @var PersonGroupEntity/TblGroup $tblObject */
                                $tblListObjectList->Name = $tblObject->getName();
                            } elseif ($tblListObjectList->getTblObjectType()->getIdentifier() === 'COMPANYGROUP') {
                                /** @var CompanyGroupEntity/TblGroup $tblObject */
                                $tblListObjectList->Name = $tblObject->getName();
                            } else {
                                $tblListObjectList->Name = '';
                            }
                        } else {
                            $tblListObjectList->Name = '';
                        }

                        $tblListObjectList->Type = $tblListObjectList->getTblObjectType()->getName();
                        $tblListObjectList->Option =
                            (new Danger('Entfernen', '/Reporting/CheckList/Object/Remove',
                                new Minus(), array(
                                    'Id' => $tblListObjectList->getId()
                                )))->__toString();
                    }
                }

                $tblObjectTypeAll = CheckList::useService()->getObjectTypeAll();
                $tblObjectType = false;
                $selectList = array();

                if ($ObjectTypeId !== null) {
                    $Global = $this->getGlobal();
                    if (!$Global->POST) {
                        $Global->POST['ObjectTypeSelect']['Id'] = $ObjectTypeId;
                        $Global->savePost();
                    }

                    $tblObjectType = CheckList::useService()->getObjectTypeById($ObjectTypeId);
                    if ($tblObjectType) {
                        if ($tblObjectType->getIdentifier() === 'PERSON') {

                            $tblPersonAll = Person::useService()->getPersonAll();
                            $tblPersonInList = CheckList::useService()->getObjectAllByListAndObjectType($tblList,
                                $tblObjectType);
                            if ($tblPersonAll && $tblPersonInList) {
                                $tblPersonAll = array_udiff($tblPersonAll, $tblPersonInList,
                                    function (TblPerson $ObjectA, TblPerson $ObjectB) {

                                        return $ObjectA->getId() - $ObjectB->getId();
                                    }
                                );
                            }

                            if ($tblPersonAll) {
                                foreach ($tblPersonAll as $tblPerson) {
                                    $tblPerson->Name = $tblPerson->getFullName();
                                    $tblPerson->Option =
                                        (new Form(
                                            new FormGroup(
                                                new FormRow(array(
                                                    new FormColumn(
                                                        new Primary('Hinzufügen',
                                                            new Plus())
                                                        , 5)
                                                ))
                                            ), null,
                                            '/Reporting/CheckList/Object/Add', array(
                                                'ListId' => $tblList->getId(),
                                                'ObjectId' => $tblPerson->getId(),
                                                'ObjectTypeId' => $tblObjectType->getId()
                                            )
                                        ))->__toString();
                                }
                                $selectList = $tblPersonAll;
                            }
                        } elseif ($tblObjectType->getIdentifier() === 'COMPANY') {

                            $tblCompanyAll = Company::useService()->getCompanyAll();
                            $tblCompanyInList = CheckList::useService()->getObjectAllByListAndObjectType($tblList,
                                $tblObjectType);
                            if ($tblCompanyAll && $tblCompanyInList) {
                                $tblCompanyAll = array_udiff($tblCompanyAll, $tblCompanyInList,
                                    function (TblCompany $ObjectA, TblCompany $ObjectB) {

                                        return $ObjectA->getId() - $ObjectB->getId();
                                    }
                                );
                            }

                            if ($tblCompanyAll) {
                                foreach ($tblCompanyAll as $tblCompany) {
                                    $tblCompany->Option =
                                        (new Form(
                                            new FormGroup(
                                                new FormRow(array(
                                                    new FormColumn(
                                                        new Primary('Hinzufügen',
                                                            new Plus())
                                                        , 5)
                                                ))
                                            ), null,
                                            '/Reporting/CheckList/Object/Add', array(
                                                'ListId' => $tblList->getId(),
                                                'ObjectId' => $tblCompany->getId(),
                                                'ObjectTypeId' => $tblObjectType->getId()
                                            )
                                        ))->__toString();
                                }
                                $selectList = $tblCompanyAll;
                            }
                        }
                    }

                }

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel('Check-Liste', $tblList->getName(), Panel::PANEL_TYPE_SUCCESS),
                                    12
                                ),
                                new LayoutColumn(
                                    CheckList::useService()->getObjectType(
                                        new Form(new FormGroup(array(
                                            new FormRow(array(
                                                new FormColumn(
                                                    new SelectBox('ObjectTypeSelect[Id]', 'Objekt-Typ',
                                                        array(
                                                            '{{ Name }}' => $tblObjectTypeAll
                                                        )),
                                                    12
                                                ),
                                            )),
                                        )), new Primary('Auswählen', new Select()))
                                        , $tblList->getId(), $ObjectTypeSelect)
                                )
                            ))
                        ))
                    ))
                    . ($tblObjectType ?
                        new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                            new Panel('Objekt-Typ:',
                                $tblObjectType->getName(),
                                Panel::PANEL_TYPE_SUCCESS), 12
                        ))))
                        . new Layout(new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new TableData($tblListObjectListByList, null,
                                        array(
                                            'Name' => 'Name',
                                            'Type' => 'Typ',
                                            'Option' => 'Option'
                                        )
                                    )
                                ), 6),
                                new LayoutColumn(array(
                                    new TableData($selectList, null,
                                        array(
                                            'Name' => 'Name',
                                            'Option' => 'Option'
                                        )
                                    )
                                ), 6),
                            ))
                        )))
                        : new Layout(new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new TableData($tblListObjectListByList, null,
                                        array(
                                            'Name' => 'Name',
                                            'Type' => 'Typ',
                                            'Option' => 'Option'
                                        )
                                    )
                                ), 12)
                            ))
                        ))))
                );
            }
        }

        return $Stage;
    }

    /**
     * @param null $ListId
     * @param null $ObjectId
     * @param null $ObjectTypeId
     * @return Stage
     */
    public function frontendListObjectAdd($ListId = null, $ObjectId = null, $ObjectTypeId = null)
    {
        $Stage = new Stage('Check-Listen', 'Zensuren-Gruppe einer Berechnungsvorschrift hinzufügen');

        if ($ListId === null || $ObjectId === null || $ObjectTypeId === null) {
            return $Stage;
        }

        $tblList = CheckList::useService()->getListById($ListId);
        $tblObjectType = CheckList::useService()->getObjectTypeById($ObjectTypeId);

        if ($tblList && $tblObjectType && $ObjectId !== null) {
            if ($tblObjectType->getIdentifier() === 'PERSON') {
                $tblPerson = Person::useService()->getPersonById($ObjectId);
                if (CheckList::useService()->addObjectToList($tblList, $tblObjectType, $tblPerson)) {
                    return new Stage('Die ' . $tblObjectType->getName() . ' ist zur Check-Liste hinzugefügt worden.')
                    . new Redirect('/Reporting/CheckList/Object/Select', 0,
                        array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
                } else {
                    return new Stage('Die ' . $tblObjectType->getName() . ' konnte zur Check-Liste nicht hinzugefügt werden.')
                    . new Redirect('/Reporting/CheckList/Object/Select', 3,
                        array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
                }
            } elseif ($tblObjectType->getIdentifier() === 'COMPANY') {
                $tblCompany = Company::useService()->getCompanyById($ObjectId);
                if (CheckList::useService()->addObjectToList($tblList, $tblObjectType, $tblCompany)) {
                    return new Stage('Die ' . $tblObjectType->getName() . ' ist zur Check-Liste hinzugefügt worden.')
                    . new Redirect('/Reporting/CheckList/Object/Select', 0,
                        array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
                } else {
                    return new Stage('Die ' . $tblObjectType->getName() . ' konnte zur Check-Liste nicht hinzugefügt werden.')
                    . new Redirect('/Reporting/CheckList/Object/Select', 3,
                        array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
                }
            }

        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendListObjectRemove($Id)
    {

        return CheckList::useService()->removeObjectFromList($Id);
    }

    /**
     * @param $Id
     * @param null $Data
     * @return Stage
     */
    public function frontendListObjectElementEdit($Id, $Data = null)
    {
        $Stage = new Stage('Check-Listen', 'Bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Reporting/CheckList', new ChevronLeft()));

        $columnDefinition = array(
            'Name' => 'Name',
            'Type' => 'Typ'
        );
        $list = array();

        $tblList = CheckList::useService()->getListById($Id);
        if ($tblList) {
            $tblListObjectListByList = CheckList::useService()->getListObjectListByList($tblList);
            $tblListElementListByList = CheckList::useService()->getListElementListByList($tblList);
            if ($tblListObjectListByList) {

                $tblListObjectElementList = CheckList::useService()->getListObjectElementListByList($tblList);
                if ($tblListObjectElementList) {
                    $Global = $this->getGlobal();
                    foreach ($tblListObjectElementList as $item) {
                        $tblListObjectList = CheckList::useService()->getListObjectListByListAndObjectTypeAndObject($tblList,
                            $item->getTblObjectType(), $item->getServiceTblObject());
                        $Global->POST['Data'][$tblListObjectList->getId()][$item->getTblListElementList()->getId()] = $item->getValue();
                    }

                    $Global->savePost();
                }

                $hasColumnDefinitions = false;
                foreach ($tblListObjectListByList as &$tblListObjectList) {
                    if (($tblObject = $tblListObjectList->getServiceTblObject())) {
                        if ($tblListObjectList->getTblObjectType()->getIdentifier() === 'PERSON') {
                            /** @var TblPerson $tblObject */
                            $list[$tblListObjectList->getId()]['Name'] = $tblObject->getFullName();
                        } elseif ($tblListObjectList->getTblObjectType()->getIdentifier() === 'COMPANY') {
                            /** @var TblCompany $tblObject */
                            $list[$tblListObjectList->getId()]['Name'] = $tblObject->getName();
                        } elseif ($tblListObjectList->getTblObjectType()->getIdentifier() === 'PERSONGROUP') {
                            /** @var PersonGroupEntity/TblGroup $tblObject */
                            $list[$tblListObjectList->getId()]['Name'] = $tblObject->getName();
                        } elseif ($tblListObjectList->getTblObjectType()->getIdentifier() === 'COMPANYGROUP') {
                            /** @var CompanyGroupEntity/TblGroup $tblObject */
                            $list[$tblListObjectList->getId()]['Name'] = $tblObject->getName();
                        } else {
                            $list[$tblListObjectList->getId()]['Name'] = '';
                        }
                    } else {
                        $list[$tblListObjectList->getId()]['Name'] = '';
                    }

                    $list[$tblListObjectList->getId()]['Type'] = $tblListObjectList->getTblObjectType()->getName();

                    if ($tblListElementListByList) {
                        foreach ($tblListElementListByList as $tblListElementList) {
                            if (!$hasColumnDefinitions) {
                                $columnDefinition['Field' . $tblListElementList->getId()] = $tblListElementList->getName();
                            }

                            if ($tblListElementList->getTblElementType()->getIdentifier() === 'CHECKBOX') {
                                $list[$tblListObjectList->getId()]['Field' . $tblListElementList->getId()] = new CheckBox(
                                    'Data[' . $tblListObjectList->getId() . '][' . $tblListElementList->getId() . ']',
                                    ' ', 1
                                );
                            } elseif ($tblListElementList->getTblElementType()->getIdentifier() === 'DATE') {
                                $list[$tblListObjectList->getId()]['Field' . $tblListElementList->getId()] = new DatePicker(
                                    'Data[' . $tblListObjectList->getId() . '][' . $tblListElementList->getId() . ']',
                                    '', '', new Calendar()
                                );
                            } elseif ($tblListElementList->getTblElementType()->getIdentifier() === 'TEXT') {
                                $list[$tblListObjectList->getId()]['Field' . $tblListElementList->getId()] = new TextField(
                                    'Data[' . $tblListObjectList->getId() . '][' . $tblListElementList->getId() . ']'
                                );
                            }
                        }
                        $hasColumnDefinitions = true;
                    }
                }
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Check-Liste', $tblList ? $tblList->getName() : '', Panel::PANEL_TYPE_SUCCESS),
                            12
                        ),
                    ))
                )),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            CheckList::useService()->updateListObjectElementList(
                                new Form(
                                    new FormGroup(array(
                                        new FormRow(
                                            new FormColumn(
                                                new TableData($list, null, $columnDefinition, false)
                                            )
                                        ),
                                    ))
                                    , new Primary('Speichern', new Save()))
                                , $Id, $Data
                            )
                        )
                    ))
                ))
            ))
        );

        return $Stage;
    }
}