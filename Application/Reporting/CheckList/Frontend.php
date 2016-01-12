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
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Group\Service\Entity\TblGroup as PersonGroupEntity;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Comment;
use SPHERE\Common\Frontend\Icon\Repository\CommodityItem;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Equalizer;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
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
use SPHERE\Application\People\Group\Group as PersonGroup;
use SPHERE\Application\Corporation\Group\Group as CompanyGroup;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Reporting\CheckList
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $List
     *
     * @return Stage
     */
    public function frontendList($List = null)
    {

        $Stage = new Stage('Check-Listen', 'Übersicht');

        $tblListAll = CheckList::useService()->getListAll();

        if ($tblListAll) {
            foreach ($tblListAll as &$tblList) {
                $tblList->Option =
                    (new Standard('(' . CheckList::useService()->countListElementListByList($tblList) . ')',
                        '/Reporting/CheckList/Element/Select', new Equalizer(),
                        array('Id' => $tblList->getId()), 'Elemente (CheckBox, Datum ...) auswählen'))
                    . (new Standard('(' . CheckList::useService()->countListObjectListByList($tblList) . ')',
                        '/Reporting/CheckList/Object/Select', new Listing(),
                        array('ListId' => $tblList->getId()), 'Objekte (Personen, Firmen) auswählen'))
                    . (new Standard(new Edit(), '/Reporting/CheckList/Object/Element/Edit', new CommodityItem(),
                        array('Id' => $tblList->getId()), 'Check-Listen-Inhalt bearbeiten'));
            }
        }

        $Form = $this->formList()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($tblListAll, null, array(
                                'Name' => 'Name',
                                'Description' => 'Beschreibung',
                                'Option' => '',
                            ))
                        ))
                    ))
                ), new Title(new ListingTable() . ' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Well(CheckList::useService()->createList($Form, $List))
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
     *
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
                            (new \SPHERE\Common\Frontend\Link\Repository\Primary('Entfernen',
                                '/Reporting/CheckList/Element/Remove',
                                new Minus(), array(
                                    'Id' => $tblListElementList->getId()
                                )))->__toString();
                    }
                }

                $Form = $this->formElement()
                    ->appendFormButton(new Primary('Speichern', new Save()))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel('Check-Liste', new Bold($tblList->getName()) .
                                        ($tblList->getDescription() !== '' ? '&nbsp;&nbsp;'
                                            . new Muted(new Small(new Small($tblList->getDescription()))) : ''),
                                        Panel::PANEL_TYPE_INFO),
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
                                            'Option' => ''
                                        )
                                    )
                                ))
                            ))
                        ), new Title(new ListingTable() . ' Übersicht')),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Well(CheckList::useService()->addElementToList($Form, $Id, $Element))
                                ))
                            ))
                        ), new Title(new PlusSign() . ' Hinzufügen'))
                    ))
                );
            }
        }

        return $Stage;
    }

    /**
     * @return Form
     */
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
    public function frontendListElementRemove($Id = null)
    {

        return CheckList::useService()->removeElementFromList($Id);
    }

    /**
     * @param null $ListId
     * @param null $ObjectTypeId
     * @param null $ObjectTypeSelect
     *
     * @return Stage
     */
    public function frontendListObjectSelect(
        $ListId = null,
        $ObjectTypeId = null,
        $ObjectTypeSelect = null
    ) {

        $Stage = new Stage('Check-Listen', 'Ein Object einer Check-Liste zuordnen');
        $Stage->setMessage('Bei Gruppen können entweder alle Objekte dieser Gruppe zum aktuellen Stand hinzugefügt
         werden oder die Gruppe direkt der Check-Liste zugeordnet (dynamisch -> Ändern sich die Mitglieder dieser Gruppe,
         ändern sich auch die Objekte in der Check-Liste mit).');

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
                                $tblListObjectList->DisplayName = $tblObject->getFullName();
                            } elseif ($tblListObjectList->getTblObjectType()->getIdentifier() === 'COMPANY') {
                                /** @var TblCompany $tblObject */
                                $tblListObjectList->DisplayName = $tblObject->getName();
                            } elseif ($tblListObjectList->getTblObjectType()->getIdentifier() === 'PERSONGROUP') {
                                /** @var PersonGroupEntity $tblObject */
                                $tblListObjectList->DisplayName = $tblObject->getName()
                                    . ' (' . PersonGroup::useService()->countPersonAllByGroup($tblObject) . ')';
                            } elseif ($tblListObjectList->getTblObjectType()->getIdentifier() === 'COMPANYGROUP') {
                                /** @var CompanyGroupEntity $tblObject */
                                $tblListObjectList->DisplayName = $tblObject->getName()
                                    . ' (' . CompanyGroup::useService()->countCompanyAllByGroup($tblObject) . ')';
                            } elseif ($tblListObjectList->getTblObjectType()->getIdentifier() === 'DIVISIONGROUP') {
                                /** @var TblDivision $tblObject */
                                $tblYear = $tblObject->getServiceTblYear();
                                $tblListObjectList->DisplayName = ($tblYear ? $tblYear->getName() . ' ' : '')
                                    . $tblObject->getDisplayName()
                                    . ' (' . Division::useService()->countDivisionStudentAllByDivision($tblObject) . ')';
                            } else {
                                $tblListObjectList->Name = '';
                            }
                        } else {
                            $tblListObjectList->Name = '';
                        }

                        $tblListObjectList->Type = $tblListObjectList->getTblObjectType()->getName();
                        $tblListObjectList->Option =
                            (new \SPHERE\Common\Frontend\Link\Repository\Primary('Entfernen',
                                '/Reporting/CheckList/Object/Remove',
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
                                    $tblPerson->DisplayName = $tblPerson->getFullName();
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
                                    $tblCompany->DisplayName = $tblCompany->getName();
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
                        } elseif ($tblObjectType->getIdentifier() === 'PERSONGROUP') {

                            $tblPersonGroupAll = PersonGroup::useService()->getGroupAll();
                            $tblPersonGroupInList = CheckList::useService()->getObjectAllByListAndObjectType($tblList,
                                $tblObjectType);
                            if ($tblPersonGroupAll && $tblPersonGroupInList) {
                                $tblPersonGroupAll = array_udiff($tblPersonGroupAll, $tblPersonGroupInList,
                                    function (PersonGroupEntity $ObjectA, PersonGroupEntity $ObjectB) {

                                        return $ObjectA->getId() - $ObjectB->getId();
                                    }
                                );
                            }

                            if ($tblPersonGroupAll) {
                                foreach ($tblPersonGroupAll as $tblPersonGroup) {
                                    $tblPersonGroup->DisplayName = $tblPersonGroup->getName()
                                        . ' (' . PersonGroup::useService()->countPersonAllByGroup($tblPersonGroup) . ')';
                                    $tblPersonGroup->Option =
                                        (new Form(
                                            new FormGroup(
                                                new FormRow(array(
                                                    new FormColumn(
                                                        new CheckBox('Option[' . $tblPersonGroup->getId() . ']',
                                                            'dynamisch', 1)
                                                        , 7),
                                                    new FormColumn(
                                                        new Primary('Hinzufügen',
                                                            new Plus())
                                                        , 5)
                                                ))
                                            ), null,
                                            '/Reporting/CheckList/Object/Add', array(
                                                'ListId' => $tblList->getId(),
                                                'ObjectId' => $tblPersonGroup->getId(),
                                                'ObjectTypeId' => $tblObjectType->getId()
                                            )
                                        ))->__toString();
                                }
                                $selectList = $tblPersonGroupAll;
                            }
                        } elseif ($tblObjectType->getIdentifier() === 'COMPANYGROUP') {

                            $tblCompanyGroupAll = CompanyGroup::useService()->getGroupAll();
                            $tblCompanyGroupInList = CheckList::useService()->getObjectAllByListAndObjectType($tblList,
                                $tblObjectType);
                            if ($tblCompanyGroupAll && $tblCompanyGroupInList) {
                                $tblCompanyGroupAll = array_udiff($tblCompanyGroupAll, $tblCompanyGroupInList,
                                    function (CompanyGroupEntity $ObjectA, CompanyGroupEntity $ObjectB) {

                                        return $ObjectA->getId() - $ObjectB->getId();
                                    }
                                );
                            }

                            if ($tblCompanyGroupAll) {
                                foreach ($tblCompanyGroupAll as $tblCompanyGroup) {
                                    $tblCompanyGroup->DisplayName = $tblCompanyGroup->getName()
                                        . ' (' . CompanyGroup::useService()->countCompanyAllByGroup($tblCompanyGroup) . ')';
                                    $tblCompanyGroup->Option =
                                        (new Form(
                                            new FormGroup(
                                                new FormRow(array(
                                                    new FormColumn(
                                                        new CheckBox('Option[' . $tblCompanyGroup->getId() . ']',
                                                            'dynamisch', 1)
                                                        , 7),
                                                    new FormColumn(
                                                        new Primary('Hinzufügen',
                                                            new Plus())
                                                        , 5)
                                                ))
                                            ), null,
                                            '/Reporting/CheckList/Object/Add', array(
                                                'ListId' => $tblList->getId(),
                                                'ObjectId' => $tblCompanyGroup->getId(),
                                                'ObjectTypeId' => $tblObjectType->getId()
                                            )
                                        ))->__toString();
                                }
                                $selectList = $tblCompanyGroupAll;
                            }
                        } elseif ($tblObjectType->getIdentifier() === 'DIVISIONGROUP') {

                            $tblDivisionAll = Division::useService()->getDivisionAll();
                            $tblDivisionInList = CheckList::useService()->getObjectAllByListAndObjectType($tblList,
                                $tblObjectType);
                            if ($tblDivisionAll && $tblDivisionInList) {
                                $tblDivisionAll = array_udiff($tblDivisionAll, $tblDivisionInList,
                                    function (TblDivision $ObjectA, TblDivision $ObjectB) {

                                        return $ObjectA->getId() - $ObjectB->getId();
                                    }
                                );
                            }

                            if ($tblDivisionAll) {
                                foreach ($tblDivisionAll as $tblDivision) {
                                    $tblYear = $tblDivision->getServiceTblYear();
                                    $tblDivision->DisplayName = ($tblYear ? $tblYear->getName() . ' ' : '')
                                        . $tblDivision->getDisplayName()
                                        . ' (' . Division::useService()->countDivisionStudentAllByDivision($tblDivision) . ')';
                                    $tblDivision->Option =
                                        (new Form(
                                            new FormGroup(
                                                new FormRow(array(
                                                    new FormColumn(
                                                        new CheckBox('Option[' . $tblDivision->getId() . ']',
                                                            'dynamisch', 1)
                                                        , 7),
                                                    new FormColumn(
                                                        new Primary('Hinzufügen',
                                                            new Plus())
                                                        , 5)
                                                ))
                                            ), null,
                                            '/Reporting/CheckList/Object/Add', array(
                                                'ListId' => $tblList->getId(),
                                                'ObjectId' => $tblDivision->getId(),
                                                'ObjectTypeId' => $tblObjectType->getId()
                                            )
                                        ))->__toString();
                                }
                                $selectList = $tblDivisionAll;
                            }
                        }
                    }

                }

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel('Check-Liste', new Bold($tblList->getName()) .
                                        ($tblList->getDescription() !== '' ? '&nbsp;&nbsp;'
                                            . new Muted(new Small(new Small($tblList->getDescription()))) : ''),
                                        Panel::PANEL_TYPE_INFO),
                                    12
                                ),
                                new LayoutColumn(new Well(
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
                                ))
                            ))
                        ))
                    ))
                    . ($tblObjectType ?
                        new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                            new Panel('Objekt-Typ:',
                                $tblObjectType->getName(),
                                Panel::PANEL_TYPE_INFO), 12
                        ))))
                        . new Layout(new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Title('Ausgewählte', 'Objekte'),
                                    new TableData($tblListObjectListByList, null,
                                        array(
                                            'DisplayName' => 'Name',
                                            'Type' => 'Typ',
                                            'Option' => ''
                                        )
                                    )
                                ), 6),
                                new LayoutColumn(array(
                                    new Title('Verfügbare', 'Objekte'),
                                    new TableData($selectList, null,
                                        array(
                                            'DisplayName' => 'Name',
                                            'Option' => ''
                                        )
                                    )
                                ), 6),
                            ))
                        )))
                        : new Layout(new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Title('Ausgewählte', 'Objekte'),
                                    new TableData($tblListObjectListByList, null,
                                        array(
                                            'DisplayName' => 'Name',
                                            'Type' => 'Typ',
                                            'Option' => ''
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
     * @param null $Option
     *
     * @return Stage
     */
    public function frontendListObjectAdd($ListId = null, $ObjectId = null, $ObjectTypeId = null, $Option = null)
    {

        $Stage = new Stage('Check-Listen', 'Ein Object einer Check-Liste hinzufügen');

        if ($ListId === null || $ObjectId === null || $ObjectTypeId === null) {
            return $Stage;
        }

        $tblList = CheckList::useService()->getListById($ListId);
        $tblObjectType = CheckList::useService()->getObjectTypeById($ObjectTypeId);

        if ($tblList && $tblObjectType && $ObjectId !== null) {
            if ($tblObjectType->getIdentifier() === 'PERSON') {
                $tblPerson = Person::useService()->getPersonById($ObjectId);
                if (CheckList::useService()->addObjectToList($tblList, $tblObjectType, $tblPerson)) {
                    return $Stage . new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() .
                        ' Die ' . $tblObjectType->getName() . ' ist zur Check-Liste hinzugefügt worden.')
                    . new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_SUCCESS,
                        array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
                } else {
                    return $Stage . new Danger(new Ban() .
                        ' Die ' . $tblObjectType->getName() . ' konnte zur Check-Liste nicht hinzugefügt werden.')
                    . new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_ERROR,
                        array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
                }
            } elseif ($tblObjectType->getIdentifier() === 'COMPANY') {
                $tblCompany = Company::useService()->getCompanyById($ObjectId);
                if (CheckList::useService()->addObjectToList($tblList, $tblObjectType, $tblCompany)) {
                    return $Stage . new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() .
                        ' Die ' . $tblObjectType->getName() . ' ist zur Check-Liste hinzugefügt worden.')
                    . new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_SUCCESS,
                        array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
                } else {
                    return $Stage . new Danger(new Ban() .
                        ' Die ' . $tblObjectType->getName() . ' konnte zur Check-Liste nicht hinzugefügt werden.')
                    . new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_ERROR,
                        array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
                }
            } elseif ($tblObjectType->getIdentifier() === 'PERSONGROUP') {
                $tblPersonGroup = PersonGroup::useService()->getGroupById($ObjectId);

                if (isset($Option[$tblPersonGroup->getId()])) {

                    if (CheckList::useService()->addObjectToList($tblList, $tblObjectType, $tblPersonGroup)) {
                        return $Stage . new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() .
                            ' Die ' . $tblObjectType->getName() . ' ist zur Check-Liste hinzugefügt worden.')
                        . new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_SUCCESS,
                            array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
                    } else {
                        return $Stage . new Danger(new Ban() .
                            ' Die ' . $tblObjectType->getName() . ' konnte zur Check-Liste nicht hinzugefügt werden.')
                        . new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_ERROR,
                            array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
                    }

                } else {
                    $tblPersonAllByGroup = PersonGroup::useService()->getPersonAllByGroup($tblPersonGroup);
                    $countAdd = 0;
                    $countExists = 0;
                    if ($tblPersonAllByGroup) {
                        foreach ($tblPersonAllByGroup as $tblPerson) {
                            if (CheckList::useService()->getListObjectListByListAndObjectTypeAndObject(
                                $tblList, CheckList::useService()->getObjectTypeByIdentifier('PERSON'), $tblPerson)
                            ) {
                                $countExists++;
                            } else {
                                CheckList::useService()->addObjectToList($tblList,
                                    CheckList::useService()->getObjectTypeByIdentifier('PERSON'), $tblPerson);
                                $countAdd++;
                            }
                        }
                    }

                    return $Stage . new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() .
                        ' Die ' . $tblObjectType->getName() . ' ist zur Check-Liste hinzugefügt worden.')
                    . new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . $countAdd . ' Person/en hinzugefügt.')
                    . ($countExists > 0 ? new Warning($countExists . ' Person/en existierten bereits in der Check-Liste') : '')
                    . new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_SUCCESS,
                        array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
                }
            } elseif ($tblObjectType->getIdentifier() === 'COMPANYGROUP') {
                $tblCompanyGroup = CompanyGroup::useService()->getGroupById($ObjectId);

                if (isset($Option[$tblCompanyGroup->getId()])) {

                    if (CheckList::useService()->addObjectToList($tblList, $tblObjectType, $tblCompanyGroup)) {
                        return $Stage . new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() .
                            ' Die ' . $tblObjectType->getName() . ' ist zur Check-Liste hinzugefügt worden.')
                        . new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_SUCCESS,
                            array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
                    } else {
                        return $Stage . new Danger(new Ban() .
                            ' Die ' . $tblObjectType->getName() . ' konnte zur Check-Liste nicht hinzugefügt werden.')
                        . new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_ERROR,
                            array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
                    }

                } else {
                    $tblCompanyByGroup = CompanyGroup::useService()->getCompanyAllByGroup($tblCompanyGroup);
                    $countAdd = 0;
                    $countExists = 0;
                    if ($tblCompanyByGroup) {
                        foreach ($tblCompanyByGroup as $tblCompany) {
                            if (CheckList::useService()->getListObjectListByListAndObjectTypeAndObject(
                                $tblList, CheckList::useService()->getObjectTypeByIdentifier('COMPANY'), $tblCompany)
                            ) {
                                $countExists++;
                            } else {
                                CheckList::useService()->addObjectToList($tblList,
                                    CheckList::useService()->getObjectTypeByIdentifier('COMPANY'), $tblCompany);
                                $countAdd++;
                            }
                        }
                    }

                    return $Stage . new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() .
                        ' Die ' . $tblObjectType->getName() . ' ist zur Check-Liste hinzugefügt worden.')
                    . new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . $countAdd . ' Firma/en hinzugefügt.')
                    . ($countExists > 0 ? new Warning($countExists . ' Firma/en existierten bereits in der Check-Liste') : '')
                    . new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_SUCCESS,
                        array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
                }
            } elseif ($tblObjectType->getIdentifier() === 'DIVISIONGROUP') {
                $tblDivision = Division::useService()->getDivisionById($ObjectId);

                if (isset($Option[$tblDivision->getId()])) {

                    if (CheckList::useService()->addObjectToList($tblList, $tblObjectType, $tblDivision)) {
                        return $Stage . new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() .
                            ' Die ' . $tblObjectType->getName() . ' ist zur Check-Liste hinzugefügt worden.')
                        . new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_SUCCESS,
                            array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
                    } else {
                        return $Stage . new Danger(new Ban() .
                            ' Die ' . $tblObjectType->getName() . ' konnte zur Check-Liste nicht hinzugefügt werden.')
                        . new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_ERROR,
                            array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
                    }

                } else {
                    $tblDivisionStudentByGroup = Division::useService()->getStudentAllByDivision($tblDivision);
                    $countAdd = 0;
                    $countExists = 0;
                    if ($tblDivisionStudentByGroup) {
                        foreach ($tblDivisionStudentByGroup as $tblPerson) {
                            if (CheckList::useService()->getListObjectListByListAndObjectTypeAndObject(
                                $tblList, CheckList::useService()->getObjectTypeByIdentifier('PERSON'), $tblPerson)
                            ) {
                                $countExists++;
                            } else {
                                CheckList::useService()->addObjectToList($tblList,
                                    CheckList::useService()->getObjectTypeByIdentifier('PERSON'), $tblPerson);
                                $countAdd++;
                            }
                        }
                    }

                    return $Stage . new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() .
                        ' Die ' . $tblObjectType->getName() . ' ist zur Check-Liste hinzugefügt worden.')
                    . new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . $countAdd . ' Person/en hinzugefügt.')
                    . ($countExists > 0 ? new Warning($countExists . ' Person/en existierten bereits in der Check-Liste') : '')
                    . new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_SUCCESS,
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
    public function frontendListObjectRemove($Id = null)
    {

        return CheckList::useService()->removeObjectFromList($Id);
    }

    /**
     * @param      $Id
     * @param null $Data
     * @param null $HasData
     *
     * @return Stage
     */
    public function frontendListObjectElementEdit($Id = null, $Data = null, $HasData = null)
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

            // set Header
            $tblListElementListByList = CheckList::useService()->getListElementListByList($tblList);
            if ($tblListElementListByList) {
                foreach ($tblListElementListByList as $tblListElementList) {
                    $columnDefinition['Field' . $tblListElementList->getId()] = $tblListElementList->getName();
                }
            }

            // set Post
            $tblListObjectElementList = CheckList::useService()->getListObjectElementListByList($tblList);
            if ($tblListObjectElementList) {
                $Global = $this->getGlobal();
                foreach ($tblListObjectElementList as $item) {

                    $Global->POST['Data'][$item->getTblObjectType()->getId()][$item->getServiceTblObject()->getId()]
                    [$item->getTblListElementList()->getId()] = $item->getValue();
                }

                $Global->savePost();
            }

            $tblListObjectListByList = CheckList::useService()->getListObjectListByList($tblList);
            $objectList = array();

            // get Objects
            if ($tblListObjectListByList) {
                foreach ($tblListObjectListByList as &$tblListObjectList) {
                    if (($tblObject = $tblListObjectList->getServiceTblObject())) {
                        if ($tblListObjectList->getTblObjectType()->getIdentifier() === 'PERSON') {
                            /** @var TblPerson $tblObject */
                            $objectList[$tblListObjectList->getTblObjectType()->getId()][$tblObject->getId()] = 1;
                        } elseif ($tblListObjectList->getTblObjectType()->getIdentifier() === 'COMPANY') {
                            /** @var TblCompany $tblObject */
                            $objectList[$tblListObjectList->getTblObjectType()->getId()][$tblObject->getId()] = 1;
                        } elseif ($tblListObjectList->getTblObjectType()->getIdentifier() === 'PERSONGROUP') {
                            /** @var PersonGroupEntity $tblObject */
                            $tblPersonAllByGroup = PersonGroup::useService()->getPersonAllByGroup($tblObject);
                            if ($tblPersonAllByGroup) {
                                foreach ($tblPersonAllByGroup as $tblPerson) {
                                    $objectList[CheckList::useService()->getObjectTypeByIdentifier('PERSON')->getId()]
                                    [$tblPerson->getId()] = 1;
                                }
                            }
                        } elseif ($tblListObjectList->getTblObjectType()->getIdentifier() === 'COMPANYGROUP') {
                            /** @var CompanyGroupEntity $tblObject */
                            $tblCompanyAllByGroup = CompanyGroup::useService()->getCompanyAllByGroup($tblObject);
                            if ($tblCompanyAllByGroup) {
                                foreach ($tblCompanyAllByGroup as $tblCompany) {
                                    $objectList[CheckList::useService()->getObjectTypeByIdentifier('COMPANY')->getId()]
                                    [$tblCompany->getId()] = 1;
                                }
                            }
                        } elseif ($tblListObjectList->getTblObjectType()->getIdentifier() === 'DIVISIONGROUP') {
                            /** @var TblDivision $tblObject */
                            $tblStudentAllByDivision = Division::useService()->getStudentAllByDivision($tblObject);
                            if ($tblStudentAllByDivision) {
                                foreach ($tblStudentAllByDivision as $tblPerson) {
                                    $objectList[CheckList::useService()->getObjectTypeByIdentifier('PERSON')->getId()]
                                    [$tblPerson->getId()] = 1;
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($objectList)) {
                $count = 0;
                foreach ($objectList as $objectTypeId => $objects) {
                    $tblObjectType = CheckList::useService()->getObjectTypeById($objectTypeId);
                    if (!empty($objects)) {
                        foreach ($objects as $objectId => $value) {
                            if ($tblObjectType->getIdentifier() === 'PERSON') {
                                $tblPerson = Person::useService()->getPersonById($objectId);
                                $list[$count]['Name'] = $tblPerson->getFullName();
                            } elseif ($tblObjectType->getIdentifier() === 'COMPANY') {
                                $tblCompany = Company::useService()->getCompanyById($objectId);
                                $list[$count]['Name'] = $tblCompany->getName();
                            } else {
                                $list[$count]['Name'] = '';
                            }

                            $list[$count]['Type'] = $tblObjectType->getName();

                            if ($tblListElementListByList) {
                                foreach ($tblListElementListByList as $tblListElementList) {

                                    if ($tblListElementList->getTblElementType()->getIdentifier() === 'CHECKBOX') {
                                        $list[$count]['Field' . $tblListElementList->getId()] = new CheckBox(
                                            'Data[' . $objectTypeId . '][' . $objectId . '][' . $tblListElementList->getId() . ']',
                                            ' ', 1
                                        );
                                    } elseif ($tblListElementList->getTblElementType()->getIdentifier() === 'DATE') {
                                        $list[$count]['Field' . $tblListElementList->getId()] = new DatePicker(
                                            'Data[' . $objectTypeId . '][' . $objectId . '][' . $tblListElementList->getId() . ']',
                                            '', '', new Calendar()
                                        );
                                    } elseif ($tblListElementList->getTblElementType()->getIdentifier() === 'TEXT') {
                                        $list[$count]['Field' . $tblListElementList->getId()] = new TextField(
                                            'Data[' . $objectTypeId . '][' . $objectId . '][' . $tblListElementList->getId() . ']',
                                            '', '', new Comment()
                                        );
                                    }
                                }
                            }
                            $count++;
                        }
                    }
                }
            }
        }

        if (!empty($list)) {
            $Stage->addButton(
                new \SPHERE\Common\Frontend\Link\Repository\Primary('Herunterladen',
                    '/Api/Reporting/CheckList/Download', new Download(), array('ListId' => $tblList->getId()))
            );
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Check-Liste', new Bold($tblList->getName()) .
                                ($tblList->getDescription() !== '' ? '&nbsp;&nbsp;'
                                    . new Muted(new Small(new Small($tblList->getDescription()))) : ''),
                                Panel::PANEL_TYPE_INFO),
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
                                        new FormRow(array(
                                            new FormColumn(
                                                new TableData($list, null, $columnDefinition, null)
                                            ),
                                            new FormColumn(   // to send only unchecked CheckBoxes
                                                new HiddenField('HasData')
                                            )
                                        ))
                                    ))
                                    , new Primary('Speichern', new Save()))
                                , $Id, $Data, $HasData
                            )
                        )
                    ))
                ))
            ))
        );

        return $Stage;
    }
}
