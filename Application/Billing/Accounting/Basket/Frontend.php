<?php

namespace SPHERE\Application\Billing\Accounting\Basket;

use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasketCommodity;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasketItem;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasketPerson;
use SPHERE\Application\Billing\Inventory\Commodity\Commodity;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodity;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\Conversation;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\MoneyEuro;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Quantity;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Time;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function  frontendBasketList()
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Warenkorb' );
        $Stage->setDescription( 'Übersicht' );
        $Stage->setMessage( 'Zeigt alle vorhandenen Warenkörbe an' );
        $Stage->addButton(
            new Primary( 'Warenkorb anlegen', '/Billing/Accounting/Basket/Create' )
        );

        $tblBasketAll = Basket::useService()->entityBasketAll();

        if ( !empty( $tblBasketAll ) ) {
            array_walk( $tblBasketAll, function ( TblBasket &$tblBasket ) {

                $tblBasket->Number = $tblBasket->getId();
                $tblBasket->Option =
                    ( new Primary( 'Weiter Bearbeiten', '/Billing/Accounting/Basket/Commodity/Select',
                        new Edit(), array(
                            'Id' => $tblBasket->getId()
                        ) ) )->__toString().
                    ( new Primary( 'Name Bearbeiten', '/Billing/Accounting/Basket/Edit',
                        new Edit(), array(
                            'Id' => $tblBasket->getId()
                        ) ) )->__toString().
                    ( new Danger( 'Löschen', '/Billing/Accounting/Basket/Delete',
                        new Remove(), array(
                            'Id' => $tblBasket->getId()
                        ) ) )->__toString();
            } );
        }

        $Stage->setContent(
            new TableData( $tblBasketAll, null,
                array(
                    'Number'     => 'Nummer',
                    'Name'       => 'Name',
                    'CreateDate' => 'Erstellt am',
                    'Option'     => 'Option'
                )
            )
        );

        return $Stage;
    }

    /**
     * @param $Basket
     *
     * @return Stage
     */
    public function  frontendBasketCreate( $Basket )
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Warenkorb' );
        $Stage->setDescription( 'Hinzufügen' );
        $Stage->setMessage( 'Der Name des Warenkorbs ist Teil des Buchungstextes' );
        $Stage->addButton( new Primary( 'Zurück', '/Billing/Accounting/Basket',
            new ChevronLeft()
        ) );

        $Stage->setContent( Basket::useService()->executeCreateBasket(
            new Form( array(
                new FormGroup( array(
                    new FormRow( array(
                        new FormColumn(
                            new TextField( 'Basket[Name]', 'Name', 'Name', new Conversation()
                            ), 6 ),
                    ) ),
                ) )
            ), new \SPHERE\Common\Frontend\Form\Repository\Button\Primary( 'Hinzufügen' ) ), $Basket ) );

        return $Stage;
    }

    /**
     * @param $Id
     * @param $Basket
     *
     * @return Stage
     */
    public function  frontendBasketEdit( $Id, $Basket )
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Warenkorb' );
        $Stage->setDescription( 'Bearbeiten' );
        $Stage->setMessage( 'Der Name des Warenkorbs ist Teil des Buchungstextes' );
        $Stage->addButton( new Primary( 'Zurück', '/Billing/Accounting/Basket',
            new ChevronLeft()
        ) );

        if ( empty( $Id ) ) {
            $Stage->setContent( new Warning( 'Die Daten konnten nicht abgerufen werden' ) );
        } else {
            $tblBasket = Basket::useService()->entityBasketById( $Id );
            if ( empty( $tblBasket ) ) {
                $Stage->setContent( new Warning( 'Der Warenkorb konnte nicht abgerufen werden' ) );
            } else {

                $Global = $this->getGlobal();
                if ( !isset( $Global->POST['Basket'] ) ) {
                    $Global->POST['Basket']['Name'] = $tblBasket->getName();
                    $Global->savePost();
                }

                $Stage->setContent( Basket::useService()->executeEditBasket(
                    new Form( array(
                        new FormGroup( array(
                            new FormRow( array(
                                new FormColumn(
                                    new TextField( 'Basket[Name]', 'Name', 'Name', new Conversation()
                                    ), 6 ),
                            ) )
                        ) )
                    ), new \SPHERE\Common\Frontend\Form\Repository\Button\Primary( 'Änderungen speichern' )
                    ), $tblBasket, $Basket ) );
            }
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function  frontendBasketDelete( $Id )
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Warenkorb' );
        $Stage->setDescription( 'Löschen' );

        $tblBasket = Basket::useService()->entityBasketById( $Id );
        $Stage->setContent( Basket::useService()->executeDestroyBasket( $tblBasket ) );

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function  frontendBasketCommoditySelect( $Id )
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Warenkorb' );
        $Stage->setDescription( 'Leistungen Auswählen' );
        $Stage->setMessage( 'Bitte wählen Sie die Leistungen zur Fakturierung aus' );
        $Stage->addButton( new Primary( 'Weiter', '/Billing/Accounting/Basket/Item',
            new ChevronRight(), array(
                'Id' => $Id
            ) ) );

        $tblBasket = Basket::useService()->entityBasketById( $Id );
        $tblCommodityAll = Commodity::useService()->entityCommodityAll();
        $tblCommodityAllByBasket = Basket::useService()->entityCommodityAllByBasket( $tblBasket );

        if ( !empty( $tblCommodityAllByBasket ) ) {
            $tblCommodityAll = array_udiff( $tblCommodityAll, $tblCommodityAllByBasket,
                function ( TblCommodity $ObjectA, TblCommodity $ObjectB ) {

                    return $ObjectA->getId() - $ObjectB->getId();
                }
            );

            array_walk( $tblCommodityAllByBasket,
                function ( TblCommodity &$tblCommodity, $Index, TblBasket $tblBasket ) {

                    $tblCommodity->Type = $tblCommodity->getTblCommodityType()->getName();
                    $tblCommodity->ItemCount = Commodity::useService()->countItemAllByCommodity( $tblCommodity );
                    $tblCommodity->SumPriceItem = Commodity::useService()->sumPriceItemAllByCommodity( $tblCommodity );
                    $tblCommodity->Option =
                        ( new Danger( 'Entfernen', '/Billing/Accounting/Basket/Commodity/Remove',
                            new Minus(), array(
                                'Id'          => $tblBasket->getId(),
                                'CommodityId' => $tblCommodity->getId()
                            ) ) )->__toString();
                }, $tblBasket );
        }

        if ( !empty( $tblCommodityAll ) ) {
            array_walk( $tblCommodityAll, function ( TblCommodity $tblCommodity, $Index, TblBasket $tblBasket ) {

                $tblCommodity->Type = $tblCommodity->getTblCommodityType()->getName();
                $tblCommodity->ItemCount = Commodity::useService()->countItemAllByCommodity( $tblCommodity );
                $tblCommodity->SumPriceItem = Commodity::useService()->sumPriceItemAllByCommodity( $tblCommodity );
                $tblCommodity->Option =
                    ( new Primary( 'Hinzufügen', '/Billing/Accounting/Basket/Commodity/Add',
                        new Plus(), array(
                            'Id'          => $tblBasket->getId(),
                            'CommodityId' => $tblCommodity->getId()
                        ) ) )->__toString();
            }, $tblBasket );
        }

        $Stage->setContent(
            new Layout( array(
                new LayoutGroup( array(
                    new LayoutRow( array(
                        new LayoutColumn(
                            new Panel( 'Warenkorb - Nummer', $tblBasket->getId(),
                                Panel::PANEL_TYPE_SUCCESS ), 3
                        ),
                        new LayoutColumn(
                            new Panel( 'Warenkorb - Name', $tblBasket->getName(),
                                Panel::PANEL_TYPE_SUCCESS ), 6
                        ),
                        new LayoutColumn(
                            new Panel( 'Erstellt am', $tblBasket->getCreateDate(),
                                Panel::PANEL_TYPE_SUCCESS ), 3
                        )
                    ) ),
                ) ),
                new LayoutGroup( array(
                    new LayoutRow( array(
                        new LayoutColumn( array(
                                new TableData( $tblCommodityAllByBasket, null,
                                    array(
                                        'Name'         => 'Name',
                                        'Description'  => 'Beschreibung',
                                        'Type'         => 'Leistungsart',
                                        'ItemCount'    => 'Artikelanzahl',
                                        'SumPriceItem' => 'Gesamtpreis',
                                        'Option'       => 'Option'
                                    )
                                )
                            )
                        )
                    ) ),
                ), new Title( 'zugewiesene Leistungen' ) ),
                new LayoutGroup( array(
                    new LayoutRow( array(
                        new LayoutColumn( array(
                                new TableData( $tblCommodityAll, null,
                                    array(
                                        'Name'         => 'Name',
                                        'Description'  => 'Beschreibung',
                                        'Type'         => 'Leistungsart',
                                        'ItemCount'    => 'Artikelanzahl',
                                        'SumPriceItem' => 'Gesamtpreis',
                                        'Option'       => 'Option'
                                    )
                                )
                            )
                        )
                    ) ),
                ), new Title( 'mögliche Leistungen' ) )
            ) )
        );

        return $Stage;
    }

    /**
     * @param $Id
     * @param $CommodityId
     *
     * @return Stage
     */
    public function  frontendBasketCommodityAdd( $Id, $CommodityId )
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Warenkorb' );
        $Stage->setDescription( 'Leistung Hinzufügen' );

        $tblBasket = Basket::useService()->entityBasketById( $Id );
        $tblCommodity = Commodity::useService()->entityCommodityById( $CommodityId );
        $Stage->setContent( Basket::useService()->executeAddBasketCommodity( $tblBasket, $tblCommodity ) );

        return $Stage;
    }

    /**
     * @param $Id
     * @param $CommodityId
     *
     * @return Stage
     */
    public function  frontendBasketCommodityRemove( $Id, $CommodityId )
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Warenkorb' );
        $Stage->setDescription( 'Leistung Entfernen' );

        $tblBasket = Basket::useService()->entityBasketById( $Id );
        $tblCommodity = Commodity::useService()->entityCommodityById( $CommodityId );
        $Stage->setContent( Basket::useService()->executeRemoveBasketCommodity( $tblBasket, $tblCommodity ) );

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function  frontendBasketItemStatus( $Id )
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Warenkorb' );
        $Stage->setDescription( 'Artikel Übersicht' );
        $Stage->setMessage( 'Zeigt alle Artikel im Warenkorb' );
        $Stage->addButton( new Primary( 'Zurück', '/Billing/Accounting/Basket/Commodity/Select',
            new ChevronLeft(), array(
                'Id' => $Id
            ) ) );
        $Stage->addButton( new Primary( 'Weiter', '/Billing/Accounting/Basket/Person/Select',
            new ChevronRight(), array(
                'Id' => $Id
            ) ) );

        $tblBasket = Basket::useService()->entityBasketById( $Id );
        $tblBasketItemAll = Basket::useService()->entityBasketItemAllByBasket( $tblBasket );

        if ( !empty( $tblBasketItemAll ) ) {
            array_walk( $tblBasketItemAll, function ( TblBasketItem &$tblBasketItem ) {

                $tblCommodity = $tblBasketItem->getServiceBillingCommodityItem()->getTblCommodity();
                $tblItem = $tblBasketItem->getServiceBillingCommodityItem()->getTblItem();
                $tblBasketItem->CommodityName = $tblCommodity->getName();
                $tblBasketItem->ItemName = $tblItem->getName();
                $tblBasketItem->TotalPriceString = $tblBasketItem->getTotalPriceString();
                $tblBasketItem->QuantityString = str_replace( '.', ',', $tblBasketItem->getQuantity() );
                $tblBasketItem->PriceString = $tblBasketItem->getPriceString();
                $tblBasketItem->Option =
                    ( new Primary( 'Bearbeiten', '/Billing/Accounting/Basket/Item/Edit',
                        new Edit(), array(
                            'Id' => $tblBasketItem->getId()
                        ) ) )->__toString().
                    ( new Danger( 'Entfernen',
                        '/Billing/Accounting/Basket/Item/Remove',
                        new Minus(), array(
                            'Id' => $tblBasketItem->getId()
                        ) ) )->__toString();
            } );
        }

        $Stage->setContent(
            new Layout( array(
                new LayoutGroup( array(
                    new LayoutRow( array(
                        new LayoutColumn(
                            new Panel( 'Warenkorb - Nummer', $tblBasket->getId(),
                                Panel::PANEL_TYPE_SUCCESS ), 3
                        ),
                        new LayoutColumn(
                            new Panel( 'Warenkorb - Name', $tblBasket->getName(),
                                Panel::PANEL_TYPE_SUCCESS ), 6
                        ),
                        new LayoutColumn(
                            new Panel( 'Erstellt am', $tblBasket->getCreateDate(),
                                Panel::PANEL_TYPE_SUCCESS ), 3
                        )
                    ) ),
                ) ),
                new LayoutGroup( array(
                    new LayoutRow( array(
                        new LayoutColumn( array(
                                new TableData( $tblBasketItemAll, null,
                                    array(
                                        'CommodityName'    => 'Leistung',
                                        'ItemName'         => 'Artikel',
                                        'PriceString'      => 'Preis',
                                        'QuantityString'   => 'Menge',
                                        'TotalPriceString' => 'Gesamtpreis',
                                        'Option'           => 'Option'
                                    )
                                )
                            )
                        )
                    ) ),
                ) )
            ) )
        );

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function  frontendBasketItemRemove( $Id )
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Warenkorb' );
        $Stage->setDescription( 'Artikel Entfernen' );

        $tblBasketItem = Basket::useService()->entityBasketItemById( $Id );
        $Stage->setContent( Basket::useService()->executeRemoveBasketItem( $tblBasketItem ) );

        return $Stage;
    }

    /**
     * @param $Id
     * @param $BasketItem
     *
     * @return Stage
     */
    public function  frontendBasketItemEdit( $Id, $BasketItem )
    {

        $tblBasketItem = Basket::useService()->entityBasketItemById( $Id );
        $Stage = new Stage();
        $Stage->setTitle( 'Warenkorb' );
        $Stage->setDescription( 'Artikel Bearbeiten' );
        $Stage->addButton( new Primary( 'Zurück', '/Billing/Accounting/Basket/Item',
            new ChevronLeft(), array(
                'Id' => $tblBasketItem->getTblBasket()->getId()
            ) ) );

        if ( empty( $Id ) ) {
            $Stage->setContent( new Warning( 'Die Daten konnten nicht abgerufen werden' ) );
        } else {
            if ( empty( $tblBasketItem ) ) {
                $Stage->setContent( new Warning( 'Der Artikel konnte nicht abgerufen werden' ) );
            } else {

                $Global = $this->getGlobal();
                if ( !isset( $Global->POST['BasketItem'] ) ) {
                    $Global->POST['BasketItem']['Price'] = str_replace( '.', ',', $tblBasketItem->getPrice() );
                    $Global->POST['BasketItem']['Quantity'] = str_replace( '.', ',', $tblBasketItem->getQuantity() );
                    $Global->savePost();
                }

                $Stage->setContent(
                    new Layout( array(
                        new LayoutGroup( array(
                            new LayoutRow( array(
                                new LayoutColumn(
                                    new Panel( 'Leistung-Name',
                                        $tblBasketItem->getServiceBillingCommodityItem()->getTblCommodity()->getName()
                                        , Panel::PANEL_TYPE_SUCCESS ), 3
                                ),
                                new LayoutColumn(
                                    new Panel( 'Artikel-Name',
                                        $tblBasketItem->getServiceBillingCommodityItem()->getTblItem()->getName()
                                        , Panel::PANEL_TYPE_SUCCESS ), 3
                                ),
                                new LayoutColumn(
                                    new Panel( 'Artikel-Beschreibung',
                                        $tblBasketItem->getServiceBillingCommodityItem()->getTblItem()->getDescription()
                                        , Panel::PANEL_TYPE_SUCCESS ), 6
                                )
                            ) ),
                        ) ),
                        new LayoutGroup( array(
                            new LayoutRow( array(
                                new LayoutColumn( array(
                                        Basket::useService()->executeEditBasketItem(
                                            new Form( array(
                                                new FormGroup( array(
                                                    new FormRow( array(
                                                        new FormColumn(
                                                            new TextField( 'BasketItem[Price]', 'Preis in €', 'Preis',
                                                                new MoneyEuro()
                                                            ), 6 ),
                                                        new FormColumn(
                                                            new TextField( 'BasketItem[Quantity]', 'Menge', 'Menge',
                                                                new Quantity()
                                                            ), 6 )
                                                    ) )
                                                ) )
                                            ), new \SPHERE\Common\Frontend\Form\Repository\Button\Primary( 'Änderungen speichern' )
                                            ), $tblBasketItem, $BasketItem
                                        )
                                    )
                                )
                            ) )
                        ) )
                    ) )
                );
            }
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function  frontendBasketPersonSelect( $Id )
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Warenkorb' );
        $Stage->setDescription( 'Personen Auswählen' );
        $Stage->setMessage( 'Bitte wählen Sie Personen zur Fakturierung aus' );
        $Stage->addButton( new Primary( 'Zurück', '/Billing/Accounting/Basket/Item', new ChevronLeft(),
            array( 'Id' => $Id ) ) );
        $Stage->addButton( new Primary( 'Weiter', '/Billing/Accounting/Basket/Summary', new ChevronRight(),
            array( 'Id' => $Id ) ) );

        $tblBasket = Basket::useService()->entityBasketById( $Id );
        $tblBasketPersonList = Basket::useService()->entityBasketPersonAllByBasket( $tblBasket );
        $tblPersonByBasketList = Basket::useService()->entityPersonAllByBasket( $tblBasket );
//        $tblPersonAll = Management::servicePerson()->entityPersonAll();   //todo
        $tblPersonAll = false;
//
//        if ( !empty( $tblPersonByBasketList ) ) {
//            $tblPersonAll = array_udiff( $tblPersonAll, $tblPersonByBasketList,
//                function ( TblPerson $ObjectA, TblPerson $ObjectB ) {
//
//                    return $ObjectA->getId() - $ObjectB->getId();
//                }
//            );
//        }

        if ( !empty( $tblBasketPersonList ) ) {
            array_walk( $tblBasketPersonList, function ( TblBasketPerson &$tblBasketPerson ) {

                $tblPerson = $tblBasketPerson->getServiceManagementPerson();
                $tblBasketPerson->FirstName = $tblPerson->getFirstName();
                $tblBasketPerson->LastName = $tblPerson->getLastName();
                $tblBasketPerson->Option =
                    ( new Danger( 'Entfernen', '/Billing/Accounting/Basket/Person/Remove',
                        new Minus(), array(
                            'Id' => $tblBasketPerson->getId()
                        ) ) )->__toString();
            } );
        }

        if ( !empty( $tblPersonAll ) ) {
            array_walk( $tblPersonAll, function ( TblPerson &$tblPerson, $Index, TblBasket $tblBasket ) {

                $tblPerson->Option =
                    ( new Primary( 'Hinzufügen', '/Billing/Accounting/Basket/Person/Add',
                        new Plus(), array(
                            'Id'       => $tblBasket->getId(),
                            'PersonId' => $tblPerson->getId()
                        ) ) )->__toString();
            }, $tblBasket );
        }

        $Stage->setContent(
            new Layout( array(
                new LayoutGroup( array(
                    new LayoutRow( array(
                        new LayoutColumn(
                            new Panel( 'Warenkorb - Nummer', $tblBasket->getId(),
                                Panel::PANEL_TYPE_SUCCESS ), 3
                        ),
                        new LayoutColumn(
                            new Panel( 'Warenkorb - Name', $tblBasket->getName(),
                                Panel::PANEL_TYPE_SUCCESS ), 6
                        ),
                        new LayoutColumn(
                            new Panel( 'Erstellt am', $tblBasket->getCreateDate(),
                                Panel::PANEL_TYPE_SUCCESS ), 3
                        )
                    ) ),
                ) ),
                new LayoutGroup( array(
                    new LayoutRow( array(
                        new LayoutColumn( array(
                                new TableData( $tblBasketPersonList, null,
                                    array(
                                        'FirstName' => 'Vorname',
                                        'LastName'  => 'Nachname',
                                        'Option'    => 'Option '
                                    )
                                )
                            )
                        )
                    ) ),
                ), new Title( 'zugewiesene Personen' ) ),
                new LayoutGroup( array(
                    new LayoutRow( array(
                        new LayoutColumn( array(
                                new TableData( $tblPersonAll, null,
                                    array(
                                        'FirstName' => 'Vorname',
                                        'LastName'  => 'Nachname',
                                        'Option'    => 'Option'
                                    )
                                )
                            )
                        )
                    ) ),
                ), new Title( 'mögliche Personen' ) )
            ) )
        );

        return $Stage;
    }

    /**
     * @param $Id
     * @param $PersonId
     *
     * @return Stage
     */
    public function  frontendBasketPersonAdd( $Id, $PersonId )
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Warenkorb' );
        $Stage->setDescription( 'Person Hinzufügen' );

        $tblBasket = Basket::useService()->entityBasketById( $Id );
//        $tblPerson = Management::servicePerson()->entityPersonById( $PersonId );  //todo
        $tblPerson = false; //todo
        $Stage->setContent( Basket::useService()->executeAddBasketPerson( $tblBasket, $tblPerson ) );

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function  frontendBasketPersonRemove( $Id )
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Warenkorb' );
        $Stage->setDescription( 'Person Entfernen' );

        $tblBasketPerson = Basket::useService()->entityBasketPersonById( $Id );
        $Stage->setContent( Basket::useService()->executeRemoveBasketPerson( $tblBasketPerson ) );

        return $Stage;
    }


    /**
     * @param $Id
     * @param $Basket
     *
     * @return Stage
     */
    public function  frontendBasketSummary( $Id, $Basket = null )
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Warenkorb' );
        $Stage->setDescription( 'Zusammenfassung' );
        $Stage->setMessage( 'Schließen Sie den Warenkorb zur Fakturierung ab' );
        $Stage->addButton( new Primary( 'Zurück', '/Billing/Accounting/Basket/Person/Select',
            new ChevronLeft(), array(
                'Id' => $Id
            ) ) );

        $tblBasket = Basket::useService()->entityBasketById( $Id );
        $tblBasketItemAll = Basket::useService()->entityBasketItemAllByBasket( $tblBasket );

        if ( !empty( $tblBasketItemAll ) ) {
            array_walk( $tblBasketItemAll, function ( TblBasketItem &$tblBasketItem ) {

                $tblCommodity = $tblBasketItem->getServiceBillingCommodityItem()->getTblCommodity();
                $tblItem = $tblBasketItem->getServiceBillingCommodityItem()->getTblItem();
                $tblBasketItem->CommodityName = $tblCommodity->getName();
                $tblBasketItem->ItemName = $tblItem->getName();
                $tblBasketItem->TotalPriceString = $tblBasketItem->getTotalPriceString();
                $tblBasketItem->QuantityString = str_replace( '.', ',', $tblBasketItem->getQuantity() );
                $tblBasketItem->PriceString = $tblBasketItem->getPriceString();
            } );
        }

        $tblBasket = Basket::useService()->entityBasketById( $Id );
        $tblPersonByBasketList = Basket::useService()->entityPersonAllByBasket( $tblBasket );

        $Stage->setContent(
            new Layout( array(
                new LayoutGroup( array(
                    new LayoutRow( array(
                        new LayoutColumn(
                            new Panel( 'Warenkorb - Nummer', $tblBasket->getId(),
                                Panel::PANEL_TYPE_SUCCESS ), 3
                        ),
                        new LayoutColumn(
                            new Panel( 'Warenkorb - Name', $tblBasket->getName(),
                                Panel::PANEL_TYPE_SUCCESS ), 6
                        ),
                        new LayoutColumn(
                            new Panel( 'Erstellt am', $tblBasket->getCreateDate(),
                                Panel::PANEL_TYPE_SUCCESS ), 3
                        )
                    ) ),
                ) ),
                new LayoutGroup( array(
                    new LayoutRow( array(
                        new LayoutColumn(
                            array(
                                new TableData( $tblBasketItemAll, null,
                                    array(
                                        'CommodityName'    => 'Leistung',
                                        'ItemName'         => 'Artikel',
                                        'PriceString'      => 'Preis',
                                        'QuantityString'   => 'Menge',
                                        'TotalPriceString' => 'Gesamtpreis'
                                    )
                                )
                            )
                        )
                    ) )
                ), new Title( 'Artikel' ) ),
                new LayoutGroup( array(
                    new LayoutRow( array(
                        new LayoutColumn(
                            array(
                                new TableData( $tblPersonByBasketList, null,
                                    array(
                                        'FirstName' => 'Vorname',
                                        'LastName'  => 'Nachname'
                                    )
                                )
                            )
                        )
                    ) )
                ), new Title( 'Personen' ) ),
                new LayoutGroup( array(
                    new LayoutRow( array(
                        new LayoutColumn(
                            Basket::useService()->executeCheckBasket(
                                new Form(
                                    new FormGroup( array(
                                        new FormRow( array(
                                            new FormColumn(
                                                new DatePicker( 'Basket[Date]', 'Zahlungsdatum (Fälligkeit)',
                                                    'Zahlungsdatum (Fälligkeit)',
                                                    new Time() )
                                                , 3 )
                                        ) ),
                                    ), new \SPHERE\Common\Frontend\Form\Repository\Title( 'Zahlungsdatum' ) )
                                    , new \SPHERE\Common\Frontend\Form\Repository\Button\Primary( 'Warenkorb fakturieren (prüfen)' )
                                ), $tblBasket, $Basket
                            )
                        )
                    ) )
                ) )
            ) )
        );

        return $Stage;
    }

    /**
     * @param $Id
     * @param $Date
     * @param $Data
     * @param $Save
     *
     * @return Stage
     */
    public function  frontendBasketDebtorSelect( $Id, $Date, $Data, $Save )
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Warenkorb' );
        $Stage->setDescription( 'Debitoren zuordnen' );
        $Stage->setMessage( 'Es konnten im Warenkorb nicht zu alle Personen bei allen Leistungen eindeutig ein Debitor
            ermittelt werden. Es werden alle nicht automatisch zuordenbaren Kombinationen von Personen und Leistungen
            angezeigt. Bitte weisen Sie die entsprechenden Debitoren zu' );

        $Global = $this->getGlobal();
        if ( !isset( $Global->POST['Save'] ) ) {
            $Global->POST['Save'] = 1;
        }
        $Global->savePost();

        $tblBasket = Basket::useService()->entityBasketById( $Id );
        $tblBasketCommodityList = Basket::useService()->entityBasketCommodityAllByBasket( $tblBasket );
        /**@var TblBasketCommodity $tblBasketCommodity */
        array_walk( $tblBasketCommodityList, function ( TblBasketCommodity $tblBasketCommodity ) {

            $tblBasketCommodityDebtorList = Basket::useService()->entityBasketCommodityDebtorAllByBasketCommodity( $tblBasketCommodity );

            $tblBasketCommodity->Name = $tblBasketCommodity->getServiceManagementPerson()->getFullName();
            $tblBasketCommodity->Commodity = $tblBasketCommodity->getServiceBillingCommodity()->getName();

            $tblBasketCommodity->Select = new SelectBox( 'Data['.$tblBasketCommodity->getId().']', '', array(
                '{{ ServiceBillingDebtor.DebtorNumber }}'
                .' - {{ ServiceBillingDebtor.ServiceManagementPerson.FullName }}'
                .'{% if( ServiceBillingDebtor.Description is not empty) %} - {{ ServiceBillingDebtor.Description }}{% endif %}'
                => $tblBasketCommodityDebtorList
            ) );
        } );

        $Stage->setContent(
            new Layout( array(
                new LayoutGroup( array(
                    new LayoutRow( array(
                        new LayoutColumn(
                            new Panel( 'Warenkorb - Nummer', $tblBasket->getId(),
                                Panel::PANEL_TYPE_SUCCESS ), 3
                        ),
                        new LayoutColumn(
                            new Panel( 'Warenkorb - Name', $tblBasket->getName(),
                                Panel::PANEL_TYPE_SUCCESS ), 6
                        ),
                        new LayoutColumn(
                            new Panel( 'Erstellt am', $tblBasket->getCreateDate(),
                                Panel::PANEL_TYPE_SUCCESS ), 3
                        )
                    ) ),
                ) ),
                new LayoutGroup( array(
                    new LayoutRow( array(
                        new LayoutColumn(
                            Basket::useService()->executeCheckDebtors(
                                new Form(
                                    new FormGroup( array(
                                        new FormRow(
                                            new FormColumn(
                                                new TableData(
                                                    $tblBasketCommodityList, null, array(
                                                    'Name'      => 'Person',
                                                    'Commodity' => 'Leistung',
                                                    'Select'    => 'Debitorennummer - Debitor - Beschreibung'
                                                ), false )
                                            )
                                        ),
                                        new FormRow( array(
                                            new FormColumn(
                                                new SelectBox( 'Save', '', array(
                                                    1 => 'Nicht speichern',
                                                    2 => 'Als Standard speichern'
                                                ) )
                                                , 3 ),
                                            new FormColumn(
                                                new \SPHERE\Common\Frontend\Form\Repository\Button\Primary( 'Debitoren zuordnen (prüfen)' )
                                                , 3 ),
                                        ) )
                                    ) )
                                )
                                , $Id, $Date, $Data, $Save
                            )
                        )
                    ) )
                ) ),
            ) )
        );

        return $Stage;
    }
}