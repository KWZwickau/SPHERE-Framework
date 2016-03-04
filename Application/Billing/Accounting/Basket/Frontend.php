<?php

namespace SPHERE\Application\Billing\Accounting\Basket;

use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasketItem;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasketPerson;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasketVerification;
use SPHERE\Application\Billing\Inventory\Commodity\Commodity;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodity;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\CommodityItem;
use SPHERE\Common\Frontend\Icon\Repository\Conversation;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\Money;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Quantity;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Backward;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Accounting\Basket
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $Basket
     *
     * @return Stage
     */
    public function frontendBasketList($Basket = null)
    {

        $Stage = new Stage('Warenkorb', 'Übersicht');
        $Stage->setMessage('Zeigt alle vorhandenen Warenkörbe an');
        new Backward();
        $tblBasketAll = Basket::useService()->getBasketAll();

        $TableContent = array();
        if (!empty( $tblBasketAll )) {
            array_walk($tblBasketAll, function (TblBasket &$tblBasket) use (&$TableContent) {

                $Item['Number'] = $tblBasket->getId();
                $Item['Name'] = $tblBasket->getName();
                $Item['CreateDate'] = $tblBasket->getCreateDate();

                $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);

                $Item['Option'] =
                    ( !$tblBasketVerification ?
                        (new Standard('Warenkorb füllen', '/Billing/Accounting/Basket/Content',
                            new Listing(), array(
                                'Id' => $tblBasket->getId()
                            )))->__toString() :

                        (new Standard('Berechnung bearbeiten', '/Billing/Accounting/Basket/Verification',
                            new Pencil(), array(
                                'Id' => $tblBasket->getId()
                            )))->__toString() ).
                    (new Standard('Name bearbeiten', '/Billing/Accounting/Basket/Change',
                        new Edit(), array(
                            'Id' => $tblBasket->getId()
                        )))->__toString().
                    ( !$tblBasketVerification ?
                        (new Standard('Löschen', '/Billing/Accounting/Basket/Destroy',
                            new Remove(), array(
                                'Id' => $tblBasket->getId()
                            )))->__toString() : null );
                array_push($TableContent, $Item);
            });
        }
        $Form = new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Warenkorb',
                            new TextField('Basket[Name]', 'Name', 'Name', new Conversation()),
                            Panel::PANEL_TYPE_INFO), 6),
                    new FormColumn(
                        new Panel('Warenkorb',
                            new TextArea('Basket[Description]', 'Beschreibung', 'Beschreibung', new Conversation()),
                            Panel::PANEL_TYPE_INFO), 6),
                )),
            ))
        ));
        $Form->appendFormButton(new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Speichern', new Save()));
        $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null,
                                array(
                                    'Number'     => 'Nummer',
                                    'Name'       => 'Name',
                                    'CreateDate' => 'Erstellt am',
                                    'Option'     => ''
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
                            Basket::useService()->createBasket($Form, $Basket)
                        ), 12)
                    ), new Title(new PlusSign().' Hinzufügen')
                )
            )
        );

        return $Stage;
    }

    /**
     * @param $Id
     * @param $Basket
     *
     * @return Stage
     */
    public function frontendBasketChange($Id, $Basket)
    {

        $Stage = new Stage('Warenkorb', 'Bearbeiten');
        $Stage->setMessage('Der Name des Warenkorbs ist Teil des Buchungstextes');
//        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Basket',
//            new ChevronLeft()
//        ));
        $Stage->addButton(new Backward());

        if (empty( $Id )) {
            $Stage->setContent(new Warning('Die Daten konnten nicht abgerufen werden'));
        } else {
            $tblBasket = Basket::useService()->getBasketById($Id);
            if (!$tblBasket) {
                $Stage->setContent(new Warning('Der Warenkorb konnte nicht abgerufen werden'));
                return $Stage.new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR);
            } else {

                $Global = $this->getGlobal();
                if (!isset( $Global->POST['Basket'] )) {
                    $Global->POST['Basket']['Name'] = $tblBasket->getName();
                    $Global->POST['Basket']['Description'] = $tblBasket->getDescription();
                    $Global->savePost();
                }

                $Form = new Form(array(
                    new FormGroup(array(
                        new FormRow(array(
                            new FormColumn(
                                new Panel('Warenkorb', new TextField('Basket[Name]', 'Name', 'Name', new Conversation()),
                                    Panel::PANEL_TYPE_INFO)
                                , 6),
                            new FormColumn(
                                new Panel('Sonstiges', new TextArea('Basket[Description]', 'Beschreibung', 'Beschreibung', new Conversation()),
                                    Panel::PANEL_TYPE_INFO)
                                , 6),
                        ))
                    ))
                ));
                $Form->appendFormButton(new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Speichern', new Save()));
                $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

                $tblBasketItemList = Basket::useService()->getBasketItemAllByBasket($tblBasket);

                $Content = array();
                if ($tblBasketItemList) {
                    foreach ($tblBasketItemList as $tblBasketItem) {
                        $Content[] = $tblBasketItem->getServiceTblItem()->getName();
                    }
                }

                $Stage->setContent(
                    new Layout(
                        new LayoutGroup(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel('Warenkorb', array(
                                        'Nummer: '.$tblBasket->getId(),
                                        'Name: '.$tblBasket->getName(),
                                        'Beschreibung: '.$tblBasket->getDescription(),
                                        'Datum: '.$tblBasket->getCreateDate()
                                    ), Panel::PANEL_TYPE_SUCCESS)
                                    , 6),
                                new LayoutColumn(
                                    new Panel('Artikel', $Content,
                                        Panel::PANEL_TYPE_SUCCESS)
                                    , 6)
                            ))
                        )
                    )
                    .new Layout(
                        new LayoutGroup(
                            new LayoutRow(
                                new LayoutColumn(new Well(
                                    Basket::useService()->changeBasket($Form, $tblBasket, $Basket)
                                ), 12)
                            ), new Title(new Pencil().' Bearbeiten')
                        )
                    )
                );
            }
        }

        return $Stage;
    }

    /**
     * @param            $Id
     * @param bool|false $Confirm
     *
     * @return Stage
     */
    public function frontendBasketDestroy($Id, $Confirm = false)
    {

        $Stage = new Stage('Warenkorb', 'Löschen');
        if ($Id) {
            $tblBasket = Basket::useService()->getBasketById($Id);
            if (!$tblBasket) {
                $Stage->setContent(new Warning('Warenkorb nicht gefunden'));
                return $Stage.new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR);
            }
            // Abbruch bei aktiver Berechnung
            $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);
            if ($tblBasketVerification) {
                $Stage->setContent(new Warning('Berechnung schon im Gange'));
                return $Stage.new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR);
            }
            if (!$Confirm) {

                $Item = array();
                $tblBasketItemAll = Basket::useService()->getBasketItemAllByBasket($tblBasket);
                if ($tblBasketItemAll) {
                    foreach ($tblBasketItemAll as $tblBasketItem) {
                        $Item[] = new Muted(new Small($tblBasketItem->getServiceTblItem()->getName()));
//                            .'<br/>
//                            Preis: '.$tblItem->getServiceBillingCommodityItem()->getTblItem()->getPriceString();
                    }
                }
                $Stage->addButton(new Backward());
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                        new Panel(new Question().' Diesen Warenkorb mit dem Namen "<b>'.$tblBasket->getName().'</b>" wirklich löschen?',
                            array(
                                $tblBasket->getName().' erstellt am: '.$tblBasket->getCreateDate(),
                                new Panel('Artikel', $Item),
                            ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Billing/Accounting/Basket/Destroy', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            .new Standard(
                                'Nein', '/Billing/Accounting/Basket', new Disable()
                            )
                        )
                        , 6))))
                );
            } else {

                // Destroy Basket
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            ( Basket::useService()->destroyBasket($tblBasket)
                                ? new Success('Der Warenkorb wurde gelöscht')
                                .new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_SUCCESS)
                                : new \SPHERE\Common\Frontend\Message\Repository\Danger('Der Warenkorb konnte nicht gelöscht werden')
                                .new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR)
                            )
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new \SPHERE\Common\Frontend\Message\Repository\Danger('Der Warenkorb konnte nicht gefunden werden'),
                        new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }

        return $Stage;
    }

    public function layoutBasket(TblBasket $tblBasket)
    {

        $ItemCount = 0;
        $PersonCount = 0;
        if (Basket::useService()->getBasketItemAllByBasket($tblBasket)) {
            $ItemCount = count(Basket::useService()->getBasketItemAllByBasket($tblBasket));
        }
        if (Basket::useService()->getPersonAllByBasket($tblBasket)) {
            $PersonCount = count(Basket::useService()->getPersonAllByBasket($tblBasket));
        }
        $ItemUsed = 0;
        $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if ($tblBasketVerification) {
            $ItemUsed = count($tblBasketVerification);
        }

        return new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Warenkorb', array(
                                'Nummer: '.$tblBasket->getId()
                            , 'Name: '.$tblBasket->getName()
                            , 'Beschreibung: '.$tblBasket->getDescription())
                            , Panel::PANEL_TYPE_SUCCESS)
                        , 6),
                    new LayoutColumn(
                        new Panel('Information', array(
                                'Artikel: '.$ItemCount
                            , 'Personen: '.$PersonCount
                            , 'Zuweisungen: '.$ItemUsed)
                            , Panel::PANEL_TYPE_SUCCESS)
                        , 6),

                ))
            )
        );
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendBasketContent($Id)
    {

        $Stage = new Stage('Warenkorb', 'Übersicht');
        $Stage->setMessage('Enthaltene Artikel und Personen');
//        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Basket', new ChevronLeft()));
        $Stage->addButton(new Backward());

        $tblBasket = Basket::useService()->getBasketById($Id);
        if (!$tblBasket) {
            $Stage->setContent(new Warning('Warenkorb nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR);
        }
        $tblBasketItem = Basket::useService()->getBasketItemAllByBasket($tblBasket);
        $tblBasketPerson = Basket::useService()->getBasketPersonAllByBasket($tblBasket);

        $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if ($tblBasketVerification) {
            $Stage->setContent(new Warning('Berechnung schon im Gange'));
            return $Stage.new Redirect('/Billing/Accounting/Basket/Verification', Redirect::TIMEOUT_ERROR, array('Id' => $tblBasket->getId()));
        }
//        $Stage->addButton(new Standard('Artikel hinzufügen/entfernen', '/Billing/Accounting/Basket/Item/Select', null,
//            array('Id' => $tblBasket->getId())));
//        $Stage->addButton(new Standard('Personen hinzufügen/entfernen', '/Billing/Accounting/Basket/Person/Select', null,
//            array('Id' => $tblBasket->getId())));
        $Stage->addButton(
            new Standard('Warenkorb berechnen', '/Billing/Accounting/Basket/Calculation', null,
                array('Id' => $tblBasket->getId())));

        $TableItemContent = array();
        if ($tblBasketItem) {
            array_walk($tblBasketItem, function (TblBasketItem $tblBasketItem) use (&$TableItemContent) {

                $tblItem = $tblBasketItem->getServiceTblItem();
                $Item['Name'] = '';
                $Item['Description'] = '';
                $Item['Calculation'] = '';
                if ($tblItem) {
                    $Item['Name'] = $tblItem->getName();
                    $Item['Description'] = $tblItem->getDescription();
                    $tblCalculationList = Item::useService()->getCalculationAllByItem($tblItem);
                    if (is_array($tblCalculationList)) {
                        $Item['Calculation'] = count($tblCalculationList) - 1;
                    }
                }
//                $Item['Option'] = new Standard('', '/Billing/Accounting/Basket/Item/Remove', new Disable(), array('Id' => $tblBasketItem->getId()));
                array_push($TableItemContent, $Item);
            });
        }

        $TablePersonContent = array();
        if ($tblBasketPerson) {
            array_walk($tblBasketPerson, function (TblBasketPerson $tblBasketPerson) use (&$TablePersonContent) {

                $tblPerson = $tblBasketPerson->getServiceTblPerson();
                $Item['Name'] = '';
                $Item['Address'] = new \SPHERE\Common\Frontend\Text\Repository\Warning('Keine Adresse hinterlegt');
                if ($tblPerson) {
                    $Item['Name'] = $tblPerson->getFullName();
                    if (Address::useService()->getAddressByPerson($tblPerson)) {
                        $Item['Address'] = Address::useService()->getAddressByPerson($tblPerson)->getGuiString();
                    }
                }
//                $Item['Option'] = new Standard('', '/Billing/Accounting/Basket/Person/Remove', new Disable(), array('Id' => $tblBasketPerson->getId()));
                array_push($TablePersonContent, $Item);
            });
        }

        $Stage->setContent(
            $this->layoutBasket($tblBasket)
            .new Layout(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new TableData($TableItemContent, new \SPHERE\Common\Frontend\Table\Repository\Title('Artikel'),
                                array('Name'        => 'Artikel',
                                      'Description' => 'Beschreibung',
                                      'Calculation' => 'Anzahl Bedingungen',
//                                      'Option'      => '',
                                ), array("bPaginate" => false))
                            , 6),
                        new LayoutColumn(
                            new TableData($TablePersonContent, new \SPHERE\Common\Frontend\Table\Repository\Title('Personen'),
                                array('Name'    => 'Name',
                                      'Address' => 'Adresse',
//                                      'Option'  => '',
                                ), array("bPaginate" => false))
                            , 6),
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(
                            (new Standard('Artikel hinzufügen/entfernen', '/Billing/Accounting/Basket/Item/Select', null,
                                array('Id' => $tblBasket->getId())))
                            , 6),
                        new LayoutColumn(
                            new Standard('Personen hinzufügen/entfernen', '/Billing/Accounting/Basket/Person/Select', null,
                                array('Id' => $tblBasket->getId()))
                            , 6),
                    ))
                ))
            )
//            .new Standard('Warenkorb Fakturieren', '/Billing/Accounting/Basket/Calculation', null, array('Id' => $tblBasket->getId()))
        );

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage|string
     */
    public function frontendItemSelect($Id)
    {

        $Stage = new Stage('Artikel', 'Auswahl');
        $Stage->setMessage('Einzelne Artikel oder ganze Artikelgruppen auswählen');
//        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Basket/Content', new ChevronLeft(), array('Id' => $Id)));
        $Stage->addButton(new Backward());
        $tblBasket = Basket::useService()->getBasketById($Id);
        if (!$tblBasket) {
            $Stage->setContent(new Warning('Warenkorb nicht gefunden'));
            return $Stage
            .new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR);
        }
        $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if ($tblBasketVerification) {
            $Stage->setContent(new Warning('Berechnung schon im Gange'));
            return $Stage.new Redirect('/Billing/Accounting/Basket/Verification', Redirect::TIMEOUT_ERROR, array('Id' => $tblBasket->getId()));
        }

        $tblBasketItem = Basket::useService()->getBasketItemAllByBasket($tblBasket);

        $tblItemUsed = array();
        $TableItemContent = array();
        if ($tblBasketItem) {
            foreach ($tblBasketItem as $singleItem) {
                $tblItemUsed[] = $singleItem->getServiceTblItem();
            }

            array_walk($tblBasketItem, function (TblBasketItem $tblBasketItem) use (&$TableItemContent) {

                $tblItem = $tblBasketItem->getServiceTblItem();
                $Item['Name'] = '';
                $Item['Description'] = '';
                $Item['Type'] = $tblItem->getTblItemType()->getName();
                $Item['Calculation'] = '';
                if ($tblItem) {
                    $Item['Name'] = $tblItem->getName();
                    $Item['Description'] = $tblItem->getDescription();
                    $tblCalculationList = Item::useService()->getCalculationAllByItem($tblItem);
                    if (is_array($tblCalculationList)) {
                        $Item['Calculation'] = count($tblCalculationList) - 1;
                    }
                }
                $Item['Option'] = new Standard('', '/Billing/Accounting/Basket/Item/Remove', new Disable(),
                    array('Id' => $tblBasketItem->getId()), 'Entfernen');
                array_push($TableItemContent, $Item);
            });
        }

        $tblItemAll = Item::useService()->getItemAll();
        /*Warenkorb entfernen*/
        if (!empty( $tblItemUsed )) {
            $tblItemAll = array_udiff($tblItemAll, $tblItemUsed,
                function (TblItem $ObjectA, TblItem $ObjectB) {

                    return $ObjectA->getId() - $ObjectB->getId();
                }
            );
        }

        $TableItemAddContent = array();
        if ($tblItemAll) {
            array_walk($tblItemAll, function (TblItem $tblItem) use (&$TableItemAddContent, $tblBasket) {

                $Item['Name'] = $tblItem->getName();
                $Item['Description'] = $tblItem->getDescription();
                $Item['Type'] = $tblItem->getTblItemType()->getName();
                $Item['Option'] = new Standard('', '/Billing/Accounting/Basket/Item/Add', new Plus(),
                    array('Id'     => $tblBasket->getId(),
                          'ItemId' => $tblItem->getId()), 'Hinzufügen');

                array_push($TableItemAddContent, $Item);
            });
        }
//        $tblCommodityAll = Commodity::useService()->getCommodityAll();

        $tblCommodityUnused = Basket::useService()->getUnusedCommodityByBasket($tblBasket);

        $TableCommodityAddContent = array();
        if ($tblCommodityUnused) {
            array_walk($tblCommodityUnused, function (TblCommodity $tblCommodity) use (&$TableCommodityAddContent, $tblBasket) {

                $Item['Name'] = $tblCommodity->getName();
                $Item['Description'] = $tblCommodity->getDescription();
                $Item['Item'] = '';
                $tblItemList = Commodity::useService()->getItemAllByCommodity($tblCommodity);
                $ItemArray = array();
                if ($tblItemList) {
                    foreach ($tblItemList as $tblItem) {
                        $ItemArray[] = $tblItem->getName().' - '.$tblItem->getTblItemType()->getName();
                    }
                    $Item['Item'] = new \SPHERE\Common\Frontend\Layout\Repository\Listing($ItemArray);
                }

                $Item['Option'] = new Standard('', '/Billing/Accounting/Basket/Commodity/Add', new Plus(),
                    array('Id'          => $tblBasket->getId(),
                          'CommodityId' => $tblCommodity->getId()), 'Hinzufügen');

                array_push($TableCommodityAddContent, $Item);
            });
        }


        $Stage->setContent(
            $this->layoutBasket($tblBasket)
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableItemContent, null, array(
                                'Name'        => 'Name',
                                'Description' => 'Beschreibung',
                                'Type'        => 'Typ',
                                'Calculation' => 'Anzahl Bedingungen',
                                'Option'      => '',
                            ), array("bPaginate" => false))
                        )
                    ), new Title(new Listing().' im Warenkorb')
                )
            )
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new TableData($TableItemAddContent, new \SPHERE\Common\Frontend\Table\Repository\Title('Artikel')
                                , array('Name'        => 'Artikel',
                                        'Description' => 'Beschreibung',
                                        'Type'        => 'Typ',
                                        'Option'      => '',
                                ))
                            , 6),
                        new LayoutColumn(
                            new TableData($TableCommodityAddContent, new \SPHERE\Common\Frontend\Table\Repository\Title('Artikelgruppen')
                                , array('Name'        => 'Artikel',
                                        'Description' => 'Beschreibung',
                                        'Item'        => 'Artikel - Typ',
                                        'Option'      => '',
                                ))
                            , 6),
                    )), new Title(new PlusSign().' Hinzufügen')
                )
            )
        );

        return $Stage;
    }

    /**
     * @param $Id
     * @param $CommodityId
     *
     * @return Stage
     */
    public function frontendBasketCommodityAdd($Id, $CommodityId)
    {

        $Stage = new Stage('Warenkorb', 'Artikelgruppe Hinzufügen');
        $tblBasket = Basket::useService()->getBasketById($Id);
        if (!$tblBasket) {
            $Stage->setContent(new Warning('Warenkorb nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR);
        }
        $tblCommodity = Commodity::useService()->getCommodityById($CommodityId);
        $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if ($tblBasketVerification) {
            $Stage->setContent(new Warning('Berechnung schon im Gange'));
            return $Stage.new Redirect('/Billing/Accounting/Basket/Verification', Redirect::TIMEOUT_ERROR, array('Id' => $tblBasket->getId()));
        }
        $Stage->setContent(Basket::useService()->addCommodityToBasket($tblBasket, $tblCommodity));
        return $Stage;
    }

    /**
     * @param $Id
     * @param $ItemId
     *
     * @return Stage
     */
    public function frontendBasketItemAdd($Id, $ItemId)
    {

        $Stage = new Stage('Warenkorb', 'Artikel Hinzufügen');
        $tblBasket = Basket::useService()->getBasketById($Id);
        if (!$tblBasket) {
            $Stage->setContent(new Warning('Warenkorb nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR);
        }
        $tblItem = Item::useService()->getItemById($ItemId);
        $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if ($tblBasketVerification) {
            $Stage->setContent(new Warning('Berechnung schon im Gange'));
            return $Stage.new Redirect('/Billing/Accounting/Basket/Verification', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblBasket->getId()));
        }
        $Stage->setContent(Basket::useService()->addItemToBasket($tblBasket, $tblItem));
        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendBasketItemRemove($Id)
    {

        $Stage = new Stage('Warenkorb', 'Artikel Entfernen');
        $tblBasketItem = Basket::useService()->getBasketItemById($Id);
        $tblBasket = $tblBasketItem->getTblBasket();
        $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if ($tblBasketVerification) {
            $Stage->setContent(new Warning('Berechnung schon im Gange'));
            return $Stage.new Redirect('/Billing/Accounting/Basket/Verification', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblBasket->getId()));
        }
        $Stage->setContent(Basket::useService()->removeBasketItem($tblBasketItem));
        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendBasketPersonSelect($Id)     //ToDO
    {

        $Stage = new Stage('Warenkorb', 'Personen Auswählen');
        $Stage->setMessage('Bitte wählen Sie Personen zur Fakturierung aus');
        $tblBasket = Basket::useService()->getBasketById($Id);
        if (!$tblBasket) {
            $Stage->setContent(new Warning('Warenkorb nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR);
        }
        $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if ($tblBasketVerification) {
            $Stage->setContent(new Warning('Berechnung schon im Gange'));
            return $Stage.new Redirect('/Billing/Accounting/Basket/Verification', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblBasket->getId()));
        }
//        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Basket/Content', new ChevronLeft(),
//            array('Id' => $Id)));
        $Stage->addButton(new Backward());
        $tblBasketPersonList = Basket::useService()->getBasketPersonAllByBasket($tblBasket);
        $tblPersonByBasketList = Basket::useService()->getPersonAllByBasket($tblBasket);
        $tblPersonAll = Person::useService()->getPersonAll();


        if (!empty( $tblPersonByBasketList )) {
            $tblPersonAll = array_udiff($tblPersonAll, $tblPersonByBasketList,
                function (TblPerson $ObjectA, TblPerson $ObjectB) {

                    return $ObjectA->getId() - $ObjectB->getId();
                }
            );
        }

        $TableContent = array();
        if (!empty( $tblBasketPersonList )) {
            array_walk($tblBasketPersonList, function (TblBasketPerson $tblBasketPerson) use (&$TableContent) {

                $tblPerson = $tblBasketPerson->getServiceTblPerson();
                $Temp['Salutation'] = $tblPerson->getSalutation();
                $Temp['Name'] = $tblPerson->getLastName().', '.$tblPerson->getFirstName();

                $idAddressAll = Address::useService()->fetchIdAddressAllByPerson($tblPerson);
                $tblAddressAll = Address::useService()->fetchAddressAllByIdList($idAddressAll);
                if (!empty( $tblAddressAll )) {
                    $tblAddress = current($tblAddressAll)->getGuiString();
                } else {
                    $tblAddress = new \SPHERE\Common\Frontend\Text\Repository\Warning(
                        new \SPHERE\Common\Frontend\Icon\Repository\Warning().' Keine Adresse hinterlegt');
                }
                $Temp['Address'] = $tblAddress;
                $Temp['Remove'] =
                    (new Standard('', '/Billing/Accounting/Basket/Person/Remove',
                        new Disable(), array(
                            'Id' => $tblBasketPerson->getId()
                        ), 'Entfernen'))->__toString();
                array_push($TableContent, $Temp);
            });
        }

        $TableContentPerson = array();
        if (!empty( $tblPersonAll )) {
            array_walk($tblPersonAll, function (TblPerson $tblPerson) use (&$TableContentPerson, $tblBasket) {

                $Temp['Salutation'] = $tblPerson->getSalutation();
                $Temp['Name'] = $tblPerson->getLastName().', '.$tblPerson->getFirstName();

                $idAddressAll = Address::useService()->fetchIdAddressAllByPerson($tblPerson);
                $tblAddressAll = Address::useService()->fetchAddressAllByIdList($idAddressAll);
                if (!empty( $tblAddressAll )) {
                    $tblAddress = current($tblAddressAll)->getGuiString();
                } else {
                    $tblAddress = new \SPHERE\Common\Frontend\Text\Repository\Warning(
                        new \SPHERE\Common\Frontend\Icon\Repository\Warning().' Keine Adresse hinterlegt');
                }
                $Temp['Address'] = $tblAddress;
                $Temp['Add'] =
                    (new Standard('', '/Billing/Accounting/Basket/Person/Add',
                        new Plus(), array(
                            'Id'       => $tblBasket->getId(),
                            'PersonId' => $tblPerson->getId()
                        ), 'Hinzufügen'))->__toString();
                array_push($TableContentPerson, $Temp);
            });
        }

        $Stage->setContent(
            $this->layoutBasket($tblBasket)
            .new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null,
                                array(
                                    'Salutation' => 'Anrede',
                                    'Name'       => 'Name',
                                    'Address'    => 'Adresse',
                                    'Remove'     => ''
                                )
                            )
                        )
                    ), new Title(new \SPHERE\Common\Frontend\Icon\Repository\Person().' zugewiesene Personen')
                )
            ))
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContentPerson, null,
                                array(
                                    'Salutation' => 'Anrede',
                                    'Name'       => 'Name',
                                    'Address'    => 'Adresse',
                                    'Add'        => ' '
                                )
                            )

                        )
                    ), new Title(new \SPHERE\Common\Frontend\Icon\Repository\Person().' mögliche Personen')
                )
            )
        );

        return $Stage;
    }

    /**
     * @param $Id
     * @param $PersonId
     *
     * @return Stage
     */
    public function frontendBasketPersonAdd($Id, $PersonId)
    {

        $Stage = new Stage('Warenkorb', 'Person Hinzufügen');
        $tblBasket = Basket::useService()->getBasketById($Id);
        if (!$tblBasket) {
            $Stage->setContent(new Warning('Warenkorb nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR);
        }
        $tblPerson = Person::useService()->getPersonById($PersonId);
        $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if ($tblBasketVerification) {
            $Stage->setContent(new Warning('Berechnung schon im Gange'));
            return $Stage.new Redirect('/Billing/Accounting/Basket/Verification', Redirect::TIMEOUT_ERROR, array('Id' => $tblBasket->getId()));
        }

        $Stage->setContent(Basket::useService()->addBasketPerson($tblBasket, $tblPerson));
        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendBasketPersonRemove($Id)
    {

        $Stage = new Stage('Warenkorb', 'Person Entfernen');
        $tblBasketPerson = Basket::useService()->getBasketPersonById($Id);
        $tblBasket = $tblBasketPerson->getTblBasket();
        $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if ($tblBasketVerification) {
            $Stage->setContent(new Warning('Berechnung schon im Gange'));
            return $Stage.new Redirect('/Billing/Accounting/Basket/Verification', Redirect::TIMEOUT_ERROR, array('Id' => $tblBasket->getId()));
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ( Basket::useService()->removeBasketPerson($tblBasketPerson) )
                                ? new Success('Die Person '.$tblBasketPerson->getServiceTblPerson()->getFullName().' wurde erfolgreich entfernt')
                                .new Redirect('/Billing/Accounting/Basket/Person/Select', Redirect::TIMEOUT_SUCCESS,
                                    array('Id' => $tblBasketPerson->getTblBasket()->getId()))
                                : new Warning('Die Person '.$tblBasketPerson->getServiceTblPerson()->getFullName().' konnte nicht entfernt werden')
                                .new Redirect('/Billing/Accounting/Basket/Person/Select', Redirect::TIMEOUT_ERROR,
                                    array('Id' => $tblBasketPerson->getTblBasket()->getId()))
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage|string
     */
    public function frontendBasketCalculation($Id)
    {

        $Stage = new Stage('Warenkorb', 'Berechnung');
        $tblBasket = Basket::useService()->getBasketById($Id);
        if (!$tblBasket) {
            $Stage->setContent(new Warning('Warenkorb nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR);
        }
        $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if ($tblBasketVerification) {
            $Stage->setContent(new Warning('Berechnung schon im Gange'));
            return $Stage.new Redirect('/Billing/Accounting/Basket/Verification', Redirect::TIMEOUT_ERROR, array('Id' => $tblBasket->getId()));
        }
        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Basket', new ChevronLeft(), array('Id' => $tblBasket->getId())));

        $Stage->setContent(
            $this->layoutBasket($tblBasket)
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            Basket::useService()->createBasketVerification($tblBasket)
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage|string
     */
    public function frontendBasketVerification($Id)
    {

        $Stage = new Stage('Warenkorb', 'Berechnung');
        $tblBasket = Basket::useService()->getBasketById($Id);
        if (!$tblBasket) {
            $Stage->setContent(new Warning('Warenkorb nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR);
        }
        $Stage->addButton(new Standard('Warenkorb verlassen', '/Billing/Accounting/Basket', new ChevronLeft()));
        new Backward();
        $Stage->addButton(new \SPHERE\Common\Frontend\Link\Repository\Danger('Berechnungen leeren', '/Billing/Accounting/Basket/Verification/Destroy', new Disable()
            , array('BasketId' => $tblBasket->getId())));
        $Stage->addButton(new Standard('Zahlung fakturieren', '/Billing/Accounting/Pay/Selection', new Ok()
            , array('Id' => $tblBasket->getId())));

        $tblPersonList = Basket::useService()->getPersonAllByBasket($tblBasket);
        if (!$tblPersonList) {
            $Stage->setContent(new Warning('Keine Personen in der Berechnung enthalten.'));
            return $Stage.new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR);
        }
        $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if (!$tblBasketVerification) {
            $Stage->setContent(new Warning('Keine Daten zum fakturieren vorhanden.'));
            return $Stage.new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR);
        }

        $TableContent = array();
        if ($tblPersonList) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, $tblBasket) {

                $Item['LastName'] = $tblPerson->getLastName();
                $Item['FirstName'] = $tblPerson->getFirstName();
                $Address = new \SPHERE\Common\Frontend\Text\Repository\Warning('keine Adresse hinterlegt');
                if (Address::useService()->getAddressByPerson($tblPerson)) {
                    $Address = Address::useService()->getAddressByPerson($tblPerson)->getGuiString();
                }
                $Item['Address'] = $Address;
                $tblBasketVerificationList = Basket::useService()->getBasketVerificationByPersonAndBasket($tblPerson, $tblBasket);
                $ItemArray = array();
                $Sum = 0;
                if (is_array($tblBasketVerificationList)) {
                    /** @var TblBasketVerification $tblBasketVerification */
                    foreach ($tblBasketVerificationList as $tblBasketVerification) {
                        if ($tblBasketVerification->getServiceTblItem()) {
                            $ItemArray[] = $tblBasketVerification->getServiceTblItem()->getName();
                        }
                        $Sum += $tblBasketVerification->getValue();
                    }
                }
                $Item['ItemList'] = implode(', ', $ItemArray);
                $Item['SummaryPrice'] = round($Sum, 2).' €';

                $Item['ChildRank'] = '';
                $Item['CourseType'] = '';

                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                if ($tblStudent) {
                    if ($tblStudent->getTblStudentBilling()->getServiceTblSiblingRank()) {
                        $Item['ChildRank'] = $tblStudent->getTblStudentBilling()->getServiceTblSiblingRank()->getName();
                    }
                    $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                    if ($tblTransferType) {
                        $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                            $tblTransferType);
                        if ($tblStudentTransfer) {
                            $tblType = $tblStudentTransfer->getServiceTblType();
                            if ($tblType) {
                                $Item['CourseType'] = $tblType->getName();
                            }
                        }
                    }
                }

                $Item['Option'] = new Standard('', '/Billing/Accounting/Basket/Verification/Person', new EyeOpen(),
                        array('PersonId' => $tblPerson->getId(),
                              'BasketId' => $tblBasket->getId()), 'Artikel anzeigen')
                    .new Standard('', '/Billing/Accounting/Basket/Verification/Person/Remove', new Disable(),
                        array('Id'       => $tblBasket->getId(),
                              'PersonId' => $tblPerson->getId()), 'Person aus Berechnung entfernen');

                array_push($TableContent, $Item);
            });
        }

        $Stage->setContent(
            $this->layoutBasket($tblBasket)
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null,
                                array(
                                    'LastName'     => 'Nachname',
                                    'FirstName'    => 'Vorname',
                                    'Address'      => 'Adresse',
                                    // Todo entfernen?
                                    'ChildRank'    => 'Geschwisterkind',
                                    'CourseType'   => 'Schulart',
                                    //
                                    'ItemList'     => 'Artikel Liste',
                                    'SummaryPrice' => 'Gesammtpreis',
                                    'Option'       => '',
                                ))
                        )
                    ), new Title(new Listing().' Übersicht')
                )
            )
        );

        return $Stage;
    }

    public function frontendBasketVerificationPersonShow($PersonId, $BasketId)
    {

        $Stage = new Stage('Warenkorb', 'Berechnung');
        $tblPerson = Person::useService()->getPersonById($PersonId);
        $tblBasket = Basket::useService()->getBasketById($BasketId);

        if (!$tblBasket) {
            $Stage->setContent(new Warning('Warenkorb nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR);
        }
        if (!$tblPerson) {
            $Stage->setContent(new Warning('Person nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/Basket/Verification', Redirect::TIMEOUT_ERROR, array('Id' => $tblBasket->getId()));
        }
        $Address = new \SPHERE\Common\Frontend\Text\Repository\Warning('Keine Adresse hinterlegt');
        if (Address::useService()->getAddressByPerson($tblPerson)) {
            $Address = Address::useService()->getAddressByPerson($tblPerson)->getGuiLayout();
        }

//        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Basket/Verification', new ChevronLeft(),
//            array('Id' => $tblBasket->getId())));
        $Stage->addButton(new Backward());

        $TableContent = array();
        $tblBasketVerificationList = Basket::useService()->getBasketVerificationByPersonAndBasket($tblPerson, $tblBasket);
        if ($tblBasketVerificationList) {
            /** @var TblBasketVerification $tblBasketVerification */
            array_walk($tblBasketVerificationList, function (TblBasketVerification $tblBasketVerification) use (&$TableContent) {

                $tblItem = $tblBasketVerification->getServiceTblItem();
                $Item['Name'] = $tblItem->getName();
                $Item['Description'] = $tblItem->getDescription();
                $Item['SinglePrice'] = $tblBasketVerification->getSinglePrice();
                $Item['Quantity'] = $tblBasketVerification->getQuantity();
                $Item['Summary'] = $tblBasketVerification->getSummaryPrice();
                $Item['Option'] = new Standard('', '/Billing/Accounting/Basket/Verification/Edit', new Pencil(),
                        array('Id' => $tblBasketVerification->getId()), 'Preis / Anzahl bearbeiten')
                    .new Standard('', '/Billing/Accounting/Basket/Verification/Destroy', new Disable(),
                        array('Id' => $tblBasketVerification->getId()), 'Artikel von Person entfernen');

                array_push($TableContent, $Item);
            });
        }
        $SiblingRank = '-';
        $SchoolType = '-';
        $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
        if ($tblStudent) {
            if (( $tblBilling = $tblStudent->getTblStudentBilling() )) {
                if (( $tblSiblingRank = $tblBilling->getServiceTblSiblingRank() )) {
                    $SiblingRank = $tblSiblingRank->getName();
                }
            }

            $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
            if ($tblTransferType) {
                $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                    $tblTransferType);
                if ($tblStudentTransfer) {
                    $tblType = $tblStudentTransfer->getServiceTblType();
                    if ($tblType) {
                        $SchoolType = $tblType->getName();
                    }
                }
            }
        }

        $Stage->setContent(
            $this->layoutBasket($tblBasket)
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Person', array($tblPerson->getFullName(),
                                    'Geschwisterkind: '.$SiblingRank
                                , 'Schulart: '.$SchoolType)
                                , Panel::PANEL_TYPE_SUCCESS)
                            , 6),
                        new LayoutColumn(
                            new Panel('Adresse', array($Address)
                                , Panel::PANEL_TYPE_SUCCESS)
                            , 6),
                    )), new Title(new \SPHERE\Common\Frontend\Icon\Repository\Person().' Person')
                )
            )
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null,
                                array('Name'        => 'Artikel',
                                      'Description' => 'Beschreibung',
                                      'SinglePrice' => 'Einzelpreis',
                                      'Quantity'    => 'Anzahl',
                                      'Summary'     => 'Gesammtpreis',
                                      'Option'      => '',
                                ))
                        )
                    ), new Title(new Listing().' Übersicht')
                )
            )
        );
        return $Stage;
    }

    public function frontendBasketVerificationEdit($Id, $Item = null)
    {

        $Stage = new Stage('Warenkorb', 'Berechnung');
        $tblBasketVerification = Basket::useService()->getBasketVerificationById($Id);
        if (!$tblBasketVerification) {
            $Stage->setContent(new Warning('Warenkorbinhalt nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR);
        }
//        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Basket/Verification/Person', new ChevronLeft(),
//            array('PersonId' => $tblBasketVerification->getServicePeoplePerson()->getId(),
//                  'BasketId' => $tblBasketVerification->getTblBasket()->getId())));
        $Stage->addButton(new Backward());

        $tblItem = $tblBasketVerification->getServiceTblItem();
        $tblPerson = $tblBasketVerification->getServiceTblPerson();
        $tblBasket = $tblBasketVerification->getTblBasket();
        if (!$tblItem) {
            $Stage->setContent(new Warning('Artikel nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/Basket/Verification', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblBasketVerification->getTblBasket()->getId()));
        }
        if (!$tblPerson) {
            $Stage->setContent(new Warning('Person nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/Basket/Verification', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblBasketVerification->getTblBasket()->getId()));
        }
//        $Address = 'Keine Adresse hinterlegt';
//        if (Address::useService()->getAddressByPerson($tblPerson)) {
//            $Address = Address::useService()->getAddressByPerson($tblPerson)->getGuiString();
//        }

        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Item'] )) {
            $Global->POST['Item']['Price'] = $tblBasketVerification->getSinglePrice();
            $Global->POST['Item']['Quantity'] = $tblBasketVerification->getQuantity();
            $Global->POST['Item']['PriceChoice'] = true;
            $Global->savePost();
        }

        $SiblingRank = '-';
        $SchoolType = '-';
        $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
        if ($tblStudent) {
            if (( $tblBilling = $tblStudent->getTblStudentBilling() )) {
                if (( $tblSiblingRank = $tblBilling->getServiceTblSiblingRank() )) {
                    $SiblingRank = $tblSiblingRank->getName();
                }
            }

            $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
            if ($tblTransferType) {
                $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                    $tblTransferType);
                if ($tblStudentTransfer) {
                    $tblType = $tblStudentTransfer->getServiceTblType();
                    if ($tblType) {
                        $SchoolType = $tblType->getName();
                    }
                }
            }
        }

        $layout = new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Person', array($tblPerson->getFullName(),
                                'Geschwisterkind: '.$SiblingRank
                            , 'Schulart: '.$SchoolType)
                            , Panel::PANEL_TYPE_SUCCESS)
                        , 6),
                    new LayoutColumn(
                        new Panel('Artikel: '.$tblItem->getName(), array(
                            'Beschreibung: '.$tblItem->getDescription(),
                            'Einzelpreis: '.$tblBasketVerification->getSinglePrice(),
                            'Anzahl: '.$tblBasketVerification->getQuantity(),
                            'Gesamtpreis: '.$tblBasketVerification->getSummaryPrice(),
                        ), Panel::PANEL_TYPE_SUCCESS)
                        , 6),
                )), new Title(new \SPHERE\Common\Frontend\Icon\Repository\Person().' Person / '.new CommodityItem().' Artikel')
            )
        );

        $Form = new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Artikelpreis', array(
                                new TextField('Item[Price]', '', 'Preis', new Money()),
                                new RadioBox('Item[PriceChoice]', 'Gesamtpreis', 'Gesamtpreis'),
                                new RadioBox('Item[PriceChoice]', 'Einzelpreis', 'Einzelpreis'),)
                            , Panel::PANEL_TYPE_INFO)
                        , 6),
                    new FormColumn(
                        new Panel('Anzahl der Artikel', array(new TextField('Item[Quantity]', '', 'Anzahl', new Quantity()))
                            , Panel::PANEL_TYPE_INFO)
                        , 6),
                ))
            )
        );
        $Form->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            $this->layoutBasket($tblBasket)
            .$layout
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            Basket::useService()->changeBasketVerification($Form,
                                $tblBasketVerification, $Item)
                        ))
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param      $Id
     * @param      $PersonId
     * @param bool $Confirm
     *
     * @return Stage|string
     */
    public function frontendBasketVerificationPersonRemove($Id, $PersonId, $Confirm = false)
    {

        $Stage = new Stage('Eintrag', 'Löschen');
        $tblBasket = Basket::useService()->getBasketById($Id);
        if (!$tblBasket) {
            $Stage->setContent(new Warning('Warenkorb nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR);
        }
        $tblPerson = Person::useService()->getPersonById($PersonId);
        if (!$tblPerson) {
            $Stage->setContent(new Warning('Person nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/Basket/Verification', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblBasket->getId()));
        }

        if (!$Confirm) {
            $Person = $tblPerson->getFullName();
            $tblBasketVerificationList = Basket::useService()->getBasketVerificationByPersonAndBasket($tblPerson, $tblBasket);
            $Content = array();
            if ($tblBasketVerificationList) {
                foreach ($tblBasketVerificationList as $Key => $tblBasketVerification) {
                    $Content[$Key] = $tblBasketVerification->getServiceTblItem()->getName();
                    $Content[$Key] .= ' - '.$tblBasketVerification->getSummaryPrice();
                }
            }
            if (empty( $Content )) {
                $Content = 'Keine Artikel zugewiesen.';
            }
            $Stage->setContent(
                new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new Panel(new Question().' Diese Person "'.$Person.'" inklusive folgender Artikel wirklich entfernen?', $Content
                        , Panel::PANEL_TYPE_DANGER,
                        new Standard(
                            'Ja', '/Billing/Accounting/Basket/Verification/Person/Remove', new Ok(),
                            array('Id' => $Id, 'PersonId' => $tblPerson->getId(), 'Confirm' => true)
                        )
                        .new Standard(
                            'Nein', '/Billing/Accounting/Basket/Verification', new Disable(),
                            array('Id' => $tblBasket->getId())
                        )
                    )
                    , 6))))
            );
        } else {

            $tblBasketPerson = Basket::useService()->getBasketPersonByBasketAndPerson($tblBasket, $tblPerson);
            if (!$tblBasketPerson) {
                $Stage->setContent(new Warning('Person in Fakturierung nicht gefunden'));
                return $Stage.new Redirect('/Billing/Accounting/Basket/Verification', Redirect::TIMEOUT_ERROR,
                    array('Id' => $tblBasket->getId()));
            }

            // Destroy Basket
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        ( Basket::useService()->removeBasketPerson($tblBasketPerson) ?
                            new Success('Person erfolgreich aus der Fakturierung entfernt')
                            .new Redirect('/Billing/Accounting/Basket/Verification', Redirect::TIMEOUT_SUCCESS,
                                array('Id' => $tblBasket->getId())) :
                            new Danger('Person konnte nicht aus der Fakturierung entfernt werden.')
                            .new Redirect('/Billing/Accounting/Basket/Verification', Redirect::TIMEOUT_ERROR,
                                array('Id' => $tblBasket->getId())) )
                    )))
                )))
            );
        }

        return $Stage;
    }

    public function frontendBasketVerificationDestroy($Id = null, $BasketId = null, $Confirm = false)
    {

        $Stage = new Stage('Eintrag', 'Löschen');
        if ($Id) {
            $tblBasketVerification = Basket::useService()->getBasketVerificationById($Id);
            if (!$tblBasketVerification) {
                $Stage->setContent(new Warning('Artikel nicht gefunden'));
                return $Stage.new Redirect('/Billing/Accounting/Basket/Verification', Redirect::TIMEOUT_ERROR,
                    array('BasketId' => $BasketId));
            }
            if (!$Confirm) {
                $Person = '';
                $Item = '';
                $Price = '';
                $ItemType = '';
                if ($tblBasketVerification->getServiceTblPerson()) {
                    $Person = $tblBasketVerification->getServiceTblPerson()->getFullName();
                }
                if ($tblBasketVerification->getServiceTblItem()) {
                    $Item = $tblBasketVerification->getServiceTblItem()->getName();
                    $Price = $tblBasketVerification->getSummaryPrice();
                    if ($tblBasketVerification->getServiceTblItem()->getTblItemType()) {
                        $ItemType = $tblBasketVerification->getServiceTblItem()->getTblItemType()->getName();
                    }
                }

                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                        new Panel(new Question().' Diesen Eintrag mit folgenden Daten wirklich entfernen?',
                            array(
                                'Person: '.$Person,
                                'Artikel: '.$Item,
                                'Preis: '.$Price,
                                'Artikel Typ: '.$ItemType,
                            ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Billing/Accounting/Basket/Verification/Destroy', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            .new Standard(
                                'Nein', '/Billing/Accounting/Basket/Verification/Person', new Disable(),
                                array('PersonId' => $tblBasketVerification->getServiceTblPerson()->getId(),
                                      'BasketId' => $tblBasketVerification->getTblBasket()->getId())
                            )
                        )
                        , 6))))
                );
            } else {

                // Destroy Basket
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            Basket::useService()->destroyBasketVerification($tblBasketVerification)
                        )))
                    )))
                );
            }
        } else {
            if (null !== $BasketId) {
                $tblBasket = Basket::useService()->getBasketById($BasketId);
                if ($tblBasket) {
                    $tblBasketVerificationList = Basket::useService()->getBasketVerificationByBasket($tblBasket);
                    if ($tblBasketVerificationList) {
                        if (!$Confirm) {
                            $CountVerification = count($tblBasketVerificationList);
                            $tblBasketVerification = $tblBasketVerificationList[0];
                            $PanelContent = array();
                            foreach ($tblBasketVerificationList as $tblBasketVerifications) {
                                if ($tblBasketVerifications->getServiceTblItem()) {
                                    $PanelContent[] = $tblBasketVerifications->getServiceTblItem()->getName();
                                }
                            }
                            $Stage->setContent(
                                new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                                    new Panel(new Question().' Berechnung mit '.$CountVerification.' Einträgen wirklich löschen?',
                                        array(),
                                        Panel::PANEL_TYPE_DANGER,
                                        new Standard(
                                            'Ja', '/Billing/Accounting/Basket/Verification/Destroy', new Ok(),
                                            array('BasketId' => $BasketId, 'Confirm' => true)
                                        )
                                        .new Standard(
                                            'Nein', '/Billing/Accounting/Basket/Verification', new Disable(),
                                            array('Id' => $tblBasketVerification->getTblBasket()->getId())
                                        )
                                    )
                                    , 6))))
                            );
                            return $Stage;
                        } else {

                            // Destroy BasketVerification
                            $Error = false;
                            foreach ($tblBasketVerificationList as $tblBasketVerification) {
                                if (!Basket::useService()->destroyBasketVerificationList($tblBasketVerification)) {
                                    $Error = true;
                                }
                            }
                            if (!$Error) {
                                $Stage->setContent(new Success('Berechnung wurde geleert'));
                                return $Stage.new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_SUCCESS);
                            } else {
                                $Stage->setContent(new Danger('Berechnung konnte nicht geleert werden'));
                                return $Stage.new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR);
                            }
                        }
                    }
                }
            }
            $Stage->setContent(new Warning('Warenkorb nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR);
        }

        return $Stage;
    }

//    /**
//     * @param $Id
//     * @param $Basket
//     *
//     * @return Stage
//     */
//    public function frontendBasketSummary($Id, $Basket = null)
//    {
//
//        $Stage = new Stage();
//        $Stage->setTitle('Warenkorb');
//        $Stage->setDescription('Zusammenfassung');
//
//        $Stage->setMessage('Schließen Sie den Warenkorb zur Fakturierung ab');
//        $Stage->addButton(new Primary('Zurück', '/Billing/Accounting/Basket/Person/Select',
//            new ChevronLeft(), array(
//                'Id' => $Id
//            )));
//
//        $tblBasket = Basket::useService()->getBasketById($Id);
//        $tblBasketItemList = Basket::useService()->getBasketItemAllByBasket($tblBasket);
//        $tblPersonByBasketList = Basket::useService()->getPersonAllByBasket($tblBasket);
//        $PersonTable = array();
//        if (!empty( $tblPersonByBasketList )) {
//            /** @var TblPerson $tblPerson */
//            array_walk($tblPersonByBasketList, function (TblPerson $tblPerson) use (&$PersonTable, $tblBasketItemList, $tblPersonByBasketList) {
//
//                $Temp['FirstName'] = $tblPerson->getFirstName();
//                $Temp['LastName'] = $tblPerson->getLastName();
//                $Temp['Rank'] = '';
//                $Temp['Type'] = '';
//                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
//                if ($tblStudent) {
//                    if ($tblStudentBilling = $tblStudent->getTblStudentBilling()) {
//                        if ($tblSiblingRank = $tblStudentBilling->getServiceTblSiblingRank()) {
//                            $Temp['Rank'] = $tblSiblingRank->getName();
//                        }
//                    }
//                    $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
//                    if ($tblTransferType) {
//                        $Type = Student::useService()->getStudentTransferByType($tblStudent, $tblTransferType);
//                        if ($Type) {
//                            if ($SchoolType = $Type->getServiceTblType()) {
//                                $Temp['Type'] = $SchoolType->getName();
//                            }
//                        }
//                    }
//                }
//                $Result = 0.00;
//                $Temp['Price'] = str_replace('.', ',', $Result)." €";
//
//                array_push($PersonTable, $Temp);
//            });
//        }
//
//        $TableContent = array();
//        if (!empty( $tblBasketItemList )) {
//            array_walk($tblBasketItemList, function (TblBasketItem &$tblBasketItem) use (&$TableContent) {
//
//                $tblCommodity = $tblBasketItem->getServiceBillingCommodityItem()->getTblCommodity();
//                $tblItem = $tblBasketItem->getServiceBillingCommodityItem()->getTblItem();
//                $Temp['CommodityName'] = $tblCommodity->getName();
//                $Temp['ItemName'] = $tblItem->getName();
//                $Temp['Type'] = '';
//                $Temp['Rank'] = '';
////                if ($tblItem->getServiceStudentType()) {
////                    $Temp['Type'] = $tblItem->getServiceStudentType()->getName();
////                }
////                if ($tblItem->getServiceStudentChildRank()) {
////                    $Temp['Rank'] = $tblItem->getServiceStudentChildRank()->getName();
////                }
//
//                $Temp['TotalPriceString'] = $tblBasketItem->getTotalPriceString();
//                $Temp['QuantityString'] = str_replace('.', ',', $tblBasketItem->getQuantity());
//                $Temp['PriceString'] = $tblBasketItem->getPriceString();
//                array_push($TableContent, $Temp);
//            });
//        }
//
////        $Result = 0.00;
////        foreach ($tblBasketItemAll as $tblBasketItem) {
////            if ($tblBasketItem->getServiceBillingCommodityItem()->getTblCommodity()->getTblCommodityType()->getName() === 'Sammelleistung') {
////                $Numerator = count($tblPersonByBasketList);
////
////                $Result = ( ( $tblBasketItem->getPrice() * $tblBasketItem->getQuantity() ) / $Numerator ) + $Result;
////            } else {
////                $Result = ( $tblBasketItem->getPrice() * $tblBasketItem->getQuantity() ) + $Result;
////            }
////        }
//
//        $Form = new Form(
//            new FormGroup(array(
//                new FormRow(array(
//                    new FormColumn(
//                        new DatePicker('Basket[Date]', 'Zahlungsdatum (Fälligkeit)',
//                            'Zahlungsdatum (Fälligkeit)',
//                            new Time())
//                        , 3)
//                )),
//            ), new \SPHERE\Common\Frontend\Form\Repository\Title('Zahlungsdatum'))
//        );
//        $Form->appendFormButton(new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Warenkorb fakturieren (prüfen)'));
//        $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
//
//        $Stage->setContent(
//            new Layout(array(
//                new LayoutGroup(array(
//                    new LayoutRow(array(
//                        new LayoutColumn(
//                            new Panel('Warenkorb - Nummer', $tblBasket->getId(),
//                                Panel::PANEL_TYPE_SUCCESS), 3
//                        ),
//                        new LayoutColumn(
//                            new Panel('Warenkorb - Name', $tblBasket->getName(),
//                                Panel::PANEL_TYPE_SUCCESS), 6
//                        ),
//                        new LayoutColumn(
//                            new Panel('Erstellt am', $tblBasket->getCreateDate(),
//                                Panel::PANEL_TYPE_SUCCESS), 3
//                        )
//                    )),
//                    new LayoutRow(
//                        new LayoutColumn(
//                            new Panel('Warenkorb - Beschreibung', $tblBasket->getDescription(),
//                                Panel::PANEL_TYPE_SUCCESS), 12
//                        )
//                    )
//                )),
//                new LayoutGroup(array(
//                    new LayoutRow(array(
//                        new LayoutColumn(
//                            array(
//                                new TableData($TableContent, null,
//                                    array(
//                                        'CommodityName'    => 'Leistung',
//                                        'ItemName'         => 'Artikel',
//                                        'Type'             => 'Typ',
//                                        'Rank'             => 'Geschwister',
//                                        'PriceString'      => 'Preis',
//                                        'QuantityString'   => 'Menge',
//                                        'TotalPriceString' => 'Gesamtpreis'
//                                    )
//                                )
//                            )
//                        )
//                    ))
//                ), new Title('Artikel')),
//                new LayoutGroup(array(
//                    new LayoutRow(array(
//                        new LayoutColumn(array(), 8),
////                        new LayoutColumn(array(
////                            new Panel('Preis pro Person: '.$Result.' €', '', Panel::PANEL_TYPE_PRIMARY)
////                        ), 3)
//                    ))
//                )),
//                new LayoutGroup(array(
//                    new LayoutRow(array(
//                        new LayoutColumn(
//                            array(
//                                new TableData($PersonTable, null,
//                                    array(
//                                        'FirstName' => 'Vorname',
//                                        'LastName'  => 'Nachname',
//                                        'Type'      => 'Typ',
//                                        'Rank'      => 'Geschwister',
//                                        'Price'     => 'Gesamt',
//                                    )
//                                )
//                            )
//                        )
//                    ))
//                ), new Title('Personen')),
//                new LayoutGroup(array(
//                    new LayoutRow(array(
//                        new LayoutColumn(
//                            Basket::useService()->checkBasket($Form, $tblBasket, $Basket)
//                        )
//                    ))
//                ))
//            ))
//        );
//
//        return $Stage;
//    }
//
//    /**
//     * @param $Id
//     * @param $Date
//     * @param $Data
//     * @param $Save
//     *
//     * @return Stage
//     */
//    public function frontendBasketDebtorSelect($Id, $Date, $Data, $Save)
//    {
//
//        $Stage = new Stage();
//        $Stage->setTitle('Warenkorb');
//        $Stage->setDescription('Debitoren zuordnen');
//        $Stage->setMessage('Es konnten im Warenkorb nicht zu alle Personen bei allen Leistungen eindeutig ein Debitor
//            ermittelt werden. Es werden alle nicht automatisch zuordenbaren Kombinationen von Personen und Leistungen
//            angezeigt. Bitte weisen Sie die entsprechenden Debitoren zu');
//
//        $Global = $this->getGlobal();
//        if (!isset( $Global->POST['Save'] )) {
//            $Global->POST['Save'] = 1;
//        }
//        $Global->savePost();
//
//        $tblBasket = Basket::useService()->getBasketById($Id);
//        $tblBasketCommodityList = Basket::useService()->getBasketCommodityAllByBasket($tblBasket);
//
//        $TableContent = array();
//        /**@var TblBasketCommodity $tblBasketCommodity */
//        array_walk($tblBasketCommodityList, function (TblBasketCommodity $tblBasketCommodity) use (&$TableContent) {
//
//            $tblBasketCommodityDebtorList = Basket::useService()->getBasketCommodityDebtorAllByBasketCommodity($tblBasketCommodity);
//
//            $Temp['Name'] = $tblBasketCommodity->getServiceManagementPerson()->getFullName();
//            $Temp['Commodity'] = $tblBasketCommodity->getServiceBillingCommodity()->getName();
//
//            $Temp['Select'] = new SelectBox('Data['.$tblBasketCommodity->getId().']', '', array(
//                '{{ ServiceBillingDebtor.DebtorNumber }}'
//                .' - {{ ServiceBillingDebtor.ServiceManagementPerson.FullName }}'
//                .'{% if( ServiceBillingDebtor.Description is not empty) %} - {{ ServiceBillingDebtor.Description }}{% endif %}'
//                => $tblBasketCommodityDebtorList
//            ));
//            array_push($TableContent, $Temp);
//        });
//
//        $Form = new Form(
//            new FormGroup(array(
//                new FormRow(
//                    new FormColumn(
//                        new TableData(
//                            $TableContent, null, array(
//                            'Name'      => 'Person',
//                            'Commodity' => 'Leistung',
//                            'Select'    => 'Debitorennummer - Debitor - Beschreibung'
//                        ), false)
//                    )
//                ),
//                new FormRow(array(
//                    new FormColumn(
//                        new SelectBox('Save', '', array(
//                            1 => 'Nicht speichern',
//                            2 => 'Als Standard speichern'
//                        ))
//                        , 3),
//                ))
//            ))
//        );
//        $Form->appendFormButton(new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Debitoren zuordnen (prüfen)'));
//        $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
//
//        $Stage->setContent(
//            new Layout(array(
//                new LayoutGroup(array(
//                    new LayoutRow(array(
//                        new LayoutColumn(
//                            new Panel('Warenkorb - Nummer', $tblBasket->getId(),
//                                Panel::PANEL_TYPE_SUCCESS), 3
//                        ),
//                        new LayoutColumn(
//                            new Panel('Warenkorb - Name', $tblBasket->getName(),
//                                Panel::PANEL_TYPE_SUCCESS), 6
//                        ),
//                        new LayoutColumn(
//                            new Panel('Erstellt am', $tblBasket->getCreateDate(),
//                                Panel::PANEL_TYPE_SUCCESS), 3
//                        )
//                    )),
//                    new LayoutRow(
//                        new LayoutColumn(
//                            new Panel('Warenkorb - Beschreibung', $tblBasket->getDescription(),
//                                Panel::PANEL_TYPE_SUCCESS), 12
//                        )
//                    )
//                )),
//                new LayoutGroup(array(
//                    new LayoutRow(array(
//                        new LayoutColumn(new Well(
//                            Basket::useService()->checkDebtors($Form, $Id, $Date, $Data, $Save)
//                        ))
//                    ))
//                )),
//            ))
//        );
//
//        return $Stage;
//    }
}
