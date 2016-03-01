<?php

namespace SPHERE\Application\Billing\Inventory\Item;

use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblCalculation;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblSiblingRank;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Conversation;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\Money;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Save;
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
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Inventory\Item
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $Item
     *
     * @return Stage
     */
    public function frontendItemStatus($Item = null)
    {

        $Stage = new Stage();
        $Stage->setTitle('Artikel');
        $Stage->setDescription('Übersicht');

        $tblItemAll = Item::useService()->getItemAll();

        $TableContent = array();
        if (!empty( $tblItemAll )) {
            array_walk($tblItemAll, function (TblItem $tblItem) use (&$TableContent) {

                $Item['Name'] = $tblItem->getName();
                $Item['Description'] = $tblItem->getDescription();
                $Item['ItemType'] = $tblItem->getTblItemType()->getName();
                $tblCalculationList = Item::useService()->getCalculationAllByItem($tblItem);
                $CalculationContent = array();
                if ($tblCalculationList) {
                    /** @var TblCalculation $tblCalculation */

                    foreach ($tblCalculationList as $Key => $tblCalculation) {
                        $CalculationContent[$Key] = 'Bedingung: '.$tblCalculation->getPriceString();
                        if ($tblCalculation->getServiceSchoolType()) {
                            $CalculationContent[$Key] .= ' - '.$tblCalculation->getServiceSchoolType()->getName();
                        } else {
                            $CalculationContent[$Key] .= ' - ';
                        }
                        if ($tblCalculation->getServiceStudentChildRank()) {
                            $CalculationContent[$Key] .= ' - '.$tblCalculation->getServiceStudentChildRank()->getName();
                        } else {
                            $CalculationContent[$Key] .= ' - ';
                        }
                        if (!$tblCalculation->getServiceStudentChildRank() && !$tblCalculation->getServiceSchoolType()) {
                            $CalculationContent[$Key] = $tblCalculation->getPriceString().' Grundpreis';
                        }
                    }
                }
                $Item['Condition'] = new \SPHERE\Common\Frontend\Layout\Repository\Listing($CalculationContent);

                $Item['Option'] =
                    new Standard('', '/Billing/Inventory/Item/Calculation', new Money(), array('Id' => $tblItem->getId()), 'Preise / Bedingungen eintragen')
                    .new Standard('', '/Billing/Inventory/Item/Change', new Pencil(), array('Id' => $tblItem->getId()), 'Bearbeiten');

                array_push($TableContent, $Item);
            });

        }

        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Item'] )) {
            $Global->POST['Item']['ItemType'] = true;
            $Global->savePost();
        }

        $Form = $this->formItem()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null,
                                array(
                                    'Name'        => 'Name',
                                    'Description' => 'Beschreibung',
                                    'ItemType'    => 'Art',
                                    'Condition'   => 'Preis - Schulart - Geschwister',
                                    'Option'      => ''
                                )
                            )
                        )
                    ), new Title(new Listing().' Übersicht')
                )
            )
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            Item::useService()->createItem($Form, $Item)
                        ))
                    ), new Title(new PlusSign().' Hinzufügen')
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    public function formItem()
    {

        return new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Artikel',
                            array(
                                new TextField('Item[Name]', 'Name', 'Name', new Conversation()),
                                new TextField('Item[Value]', 'Preis', 'Standard-Preis', new Money()),
                                new RadioBox('Item[ItemType]', 'Sammelleistung', 'Sammelleistung'),
                                new RadioBox('Item[ItemType]', 'Einzelleistung', 'Einzelleistung'),

//                                new CheckBox('Item[ItemType]', 'Einzelleistung', 'Einzelleistung', array('Item[CalculationType]')),
                            ), Panel::PANEL_TYPE_INFO)
                        , 6),
                    new FormColumn(
                        new Panel('Sonstiges',
                            array(
                                new TextArea('Item[Description]', 'Beschreibung', 'Beschreibung', new Conversation()),
                            ), Panel::PANEL_TYPE_INFO)
                        , 6)
                ))
            ))
        ));
    }

    public function formItemChange()
    {

        return new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Artikel',
                            array(
                                new TextField('Item[Name]', 'Name', 'Name', new Conversation()),
                            ), Panel::PANEL_TYPE_INFO)
                        , 6),
                    new FormColumn(
                        new Panel('Sonstiges',
                            array(
                                new TextArea('Item[Description]', 'Beschreibung', 'Beschreibung', new Conversation()),
                            ), Panel::PANEL_TYPE_INFO)
                        , 6)
                ))
            ))
        ));
    }

    /**
     * @param            $Id
     * @param            $CalculationId
     * @param bool|false $Confirm
     *
     * @return Stage
     */
    public function frontendCalculationDestroy($Id, $CalculationId, $Confirm = false)
    {

        $Stage = new Stage('Zuordnung', 'Entfernen');
        if ($CalculationId) {
            $tblCalculation = Item::useService()->getCalculationById($CalculationId);
            $tblItem = Item::useService()->getItemById($Id);
            if ($tblCalculation && $tblItem) {
                $Content = array();
                $Content[] = 'Preis: '.$tblCalculation->getPriceString();
                if ($tblCalculation->getServiceSchoolType()) {
                    $Content[] = 'Schulart: '.$tblCalculation->getServiceSchoolType()->getName();
                }
                if ($tblCalculation->getServiceStudentChildRank()) {
                    $Content[] = 'Geschwister: '.$tblCalculation->getServiceStudentChildRank()->getName();
                }
                if (!$Confirm) {
                    $Stage->setContent(
                        new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                            new Panel(new Question().' Diese Zurodnung wirklich entfernen?',
                                $Content,
                                Panel::PANEL_TYPE_DANGER,
                                new Standard(
                                    'Ja', '/Billing/Inventory/Item/Calculation/Destroy', new Ok(),
                                    array('Id' => $Id, 'Confirm' => true, 'CalculationId' => $CalculationId)
                                )
                                .new Standard(
                                    'Nein', '/Billing/Inventory/Item/Calculation', new Disable(), array('Id' => $tblItem->getId())
                                )
                            )
                        ))))
                    );
                } else {

                    // Destroy Group
                    $Stage->setContent(
                        new Layout(new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(array(
                                ( Item::useService()->destroyCalculation($tblCalculation, $tblItem)
                                    ? new Success('Die Zuordnung wurde gelöscht')
                                    .new Redirect('/Billing/Inventory/Item/Calculation', Redirect::TIMEOUT_SUCCESS,
                                        array('Id' => $tblItem->getId()))
                                    : new Danger('Die Zuordnung konnte nicht gelöscht werden')
                                    .new Redirect('/Billing/Inventory/Item/Calculation', Redirect::TIMEOUT_ERROR,
                                        array('Id' => $tblItem->getId()))
                                )
                            )))
                        )))
                    );
                }
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger('Bedingung nicht gefunden'),
                        new Redirect('/Billing/Inventory/Item', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }

        return $Stage;
    }

    /**
     * @param $Id
     * @param $Item
     *
     * @return Stage
     */
    public function frontendItemChange($Id, $Item = null)
    {

        $Stage = new Stage();
        $Stage->setTitle('Artikel');
        $Stage->setDescription('Bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Billing/Inventory/Item',
            new ChevronLeft()
        ));

        $tblItem = Item::useService()->getItemById($Id);
        if (!$tblItem) {
            $Stage->setContent(new Warning('Die Daten konnten nicht abgerufen werden'));
        } else {
            $Global = $this->getGlobal();
            if (!isset( $Global->POST['Item'] )) {
                $Global->POST['Item']['Name'] = $tblItem->getName();
                $Global->POST['Item']['Description'] = $tblItem->getDescription();
//                $Global->POST['Item']['ItemType'] = $tblItem->getTblItemType()->getId();
                $Global->savePost();
            }

            $PanelValue = array();

            $PanelValue[0] = $tblItem->getName();
            $PanelValue[1] = $tblItem->getDescription();
            $PanelValue[2] = $tblItem->getTblItemType()->getName();

            $PanelContent = new Layout(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Name', $PanelValue[0], Panel::PANEL_TYPE_INFO)
                            , 4),
                        new LayoutColumn(
                            new Panel('Beschreibung', $PanelValue[1], Panel::PANEL_TYPE_INFO)
                            , 4),
                        new LayoutColumn(
                            new Panel('Art', $PanelValue[2], Panel::PANEL_TYPE_INFO)
                            , 4),
                    )),
                ))
            );


            $Form = $this->formItemChange()
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                $PanelContent
                            )
                        )
                    )
                )
                .new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(new Well(
                                Item::useService()->changeItem($Form, $tblItem, $Item)
                            ))
                        )), new Title(new Pencil().' Bearbeiten')
                    )
                )
            );
        }

        return $Stage;
    }

    /**
     * @param      $Id
     * @param null $Calculation
     *
     * @return Stage
     */
    public function frontendItemCalculation($Id, $Calculation = null)
    {

        $Stage = new Stage('Preise', 'mit Bedingungen');
        $Stage->addButton(new Standard('Zurück', '/Billing/Inventory/Item', new ChevronLeft()));
        $tblItem = Item::useService()->getItemById($Id);
        if ($tblItem) {
            $tblCalculationList = Item::useService()->getCalculationAllByItem($tblItem);
            $TableContent = array();
            if (is_array($tblCalculationList)) {
                array_walk($tblCalculationList, function (TblCalculation $tblCalculation) use (&$TableContent, $tblItem) {

                    $Item['Price'] = $tblCalculation->getPriceString();
                    $Item['Cours'] = '';
                    $Item['SiblingRank'] = '';

                    if ($tblCalculation->getServiceSchoolType()) {
                        $Item['Cours'] = $tblCalculation->getServiceSchoolType()->getName();
                    }
                    if ($tblCalculation->getServiceStudentChildRank()) {
                        $Item['SiblingRank'] = $tblCalculation->getServiceStudentChildRank()->getName();
                    }

                    if ($Item['SiblingRank'] === '' && $Item['Cours'] === '') {
                        $Item['Option'] = new Standard('', '/Billing/Inventory/Item/Calculation/Change', new Pencil(),
                            array('Id'            => $tblItem->getId(),
                                  'CalculationId' => $tblCalculation->getId()));
                    } else {
                        $Item['Option'] = new Standard('', '/Billing/Inventory/Item/Calculation/Change', new Pencil(),
                                array('Id'            => $tblItem->getId(),
                                      'CalculationId' => $tblCalculation->getId()))
                            .new Standard('', '/Billing/Inventory/Item/Calculation/Destroy', new Disable(),
                                array('Id'            => $tblItem->getId(),
                                      'CalculationId' => $tblCalculation->getId()));
                    }

                    array_push($TableContent, $Item);
                });
            }

            $Form = $this->formItemCalculation()
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

            $Stage->setContent(
                $this->layoutArtikel($tblItem)
                .new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($TableContent,
                                    null,
                                    array('Price'       => 'Preis',
                                          'Cours'       => 'Schulart',
                                          'SiblingRank' => 'Geschwister',
                                          'Option'      => '',))
                            )
                        ), new Title(new Listing().' Übersicht')
                    ),
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Well(
                                    Item::useService()->createCalculation(
                                        $Form, $tblItem, $Calculation)
                                )
                            )
                        ), new Title(new PlusSign().' Hinzufügen')
                    ),
                ))
            );

        } else {
            $Stage->setContent(new Warning('Kein Artikel gefunden')
                .new Redirect('/Billing/Inventory/Item', Redirect::TIMEOUT_ERROR));
        }
        return $Stage;
    }

    /**
     * @param TblItem $tblItem
     *
     * @return Layout
     */
    public function layoutArtikel(TblItem $tblItem)
    {

        return new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Artikel', $tblItem->getName(), Panel::PANEL_TYPE_SUCCESS)
                        , 4),
                    new LayoutColumn(
                        new Panel('Beschreibung', $tblItem->getDescription(), Panel::PANEL_TYPE_SUCCESS)
                        , 4),
                    new LayoutColumn(
                        new Panel('Art', $tblItem->getTblItemType()->getName(), Panel::PANEL_TYPE_SUCCESS)
                        , 4)
                ))
            )
        );
    }

    /**
     * @return Form
     */
    public function formItemCalculation()
    {

        $tblSchoolType = Type::useService()->getTypeAll();
        $tblSchoolType[] = new TblType();
        $tblSiblingRank = Relationship::useService()->getSiblingRankAll();
        $tblSiblingRank[] = new TblSiblingRank();

        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Preis', array(new TextField('Calculation[Value]', '', '')), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Schulart', array(new SelectBox('Calculation[SchoolType]', '',
                            array('Name' => $tblSchoolType))), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Geschwisterkind', array(new SelectBox('Calculation[SiblingRank]', '',
                            array('Name' => $tblSiblingRank))), Panel::PANEL_TYPE_INFO)
                        , 4)
                ))
            )
        );
    }

    /**
     * @return Form
     */
    public function formItemCalculationPercentage()
    {

        $tblSchoolType = Type::useService()->getTypeAll();
        $tblSchoolType[] = new TblType();
        $tblSiblingRank = Relationship::useService()->getSiblingRankAll();
        $tblSiblingRank[] = new TblSiblingRank();

        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Prozent', array(new TextField('Calculation[Value]', '', '')), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Schulart', array(new SelectBox('Calculation[SchoolType]', '',
                            array('Name' => $tblSchoolType))), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Geschwisterkind', array(new SelectBox('Calculation[SiblingRank]', '',
                            array('Name' => $tblSiblingRank))), Panel::PANEL_TYPE_INFO)
                        , 4)
                ))
            )
        );
    }

    /**
     * @return Form
     */
    public function formItemPrice()
    {

        return new Form(
            new FormGroup(
                new FormRow(
                    new FormColumn(
                        new Panel('Gesamt-Preis', array(new TextField('Calculation[Value]', '', '')), Panel::PANEL_TYPE_INFO)
                        , 4)
                )
            )
        );
    }

    /**
     * @param      $Id
     * @param      $CalculationId
     * @param null $Calculation
     *
     * @return Stage
     */
    public function frontendCalculationChange($Id, $CalculationId, $Calculation = null)
    {

        $Stage = new Stage('Bedinung', 'Bearbeiten');
        $tblItem = Item::useService()->getItemById($Id);
        $tblCalculation = Item::useService()->getCalculationById($CalculationId);
        if (!$tblItem && !$tblCalculation) {
            $Stage->addButton(new Standard('Zurück', '/Billing/Invoice/Item', new ChevronLeft()));
            $Stage->setContent(
                new Warning('Artikel oder Bedingung nicht gefunden')
                .new Redirect('/Billing/Inventory/Item', Redirect::TIMEOUT_ERROR)
            );
        } else {
            $Global = $this->getGlobal();
            if (!isset( $Global->POST['Calculation'] )) {
                $Global->POST['Calculation']['Value'] = $tblCalculation->getValue();
                if ($tblCalculation->getServiceSchoolType()) {
                    $Global->POST['Calculation']['SchoolType'] = $tblCalculation->getServiceSchoolType()->getId();
                }
                if ($tblCalculation->getServiceStudentChildRank()) {
                    $Global->POST['Calculation']['SiblingRank'] = $tblCalculation->getServiceStudentChildRank()->getId();
                }
                $Global->savePost();
            }
            $schoolType = 'Nicht ausgewählt';
            $siblingRank = 'Nicht ausgewählt';
            if ($tblCalculation->getServiceSchoolType()) {
                $schoolType = $tblCalculation->getServiceSchoolType()->getName();
            }
            if ($tblCalculation->getServiceStudentChildRank()) {
                $siblingRank = $tblCalculation->getServiceStudentChildRank()->getName();
            }
            $Form = new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(
                            new Panel('Preis', array(new TextField('Calculation[Value]', '', '')), Panel::PANEL_TYPE_INFO)
                        )
                    )
                )
            );
            $Form->appendFormButton(new Primary('Speichern', new Save()));
            $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

            $Stage->addButton(new Standard('Zurück', '/Billing/Inventory/Item/Calculation', new ChevronLeft(),
                array('Id' => $tblItem->getId())));
            $Stage->setContent(
                $this->layoutArtikel($tblItem)
                .new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel('Preis', $tblCalculation->getPriceString(), Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Bedingungen',
                                    new Layout(
                                        new LayoutGroup(
                                            new LayoutRow(array(
                                                new LayoutColumn(
                                                    new Panel('Schulart:', $schoolType)
                                                    , 6),
                                                new LayoutColumn(
                                                    new Panel('Geschwisterkind:', $siblingRank)
                                                    , 6),
                                            ))
                                        )
                                    ), Panel::PANEL_TYPE_INFO)
                                , 8)
                        )), new Title('Zu bearbeitende Bedingung:')
                    )
                )
                .new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(new Well(
                                    Item::useService()->changeCalculation(
                                        $Form, $tblItem, $tblCalculation, $Calculation))
                                , 6)
                        ), new Title(new Pencil().' Bearbeiten')
                    )
                )
            );
        }

        return $Stage;
    }
}
