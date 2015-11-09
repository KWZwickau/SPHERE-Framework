<?php

namespace SPHERE\Application\Billing\Accounting\Basket;

use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasketCommodity;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasketItem;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasketPerson;
use SPHERE\Application\Billing\Inventory\Commodity\Commodity;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodity;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
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
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\MoneyEuro;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Quantity;
use SPHERE\Common\Frontend\Icon\Repository\Question;
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
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Link\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
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
        $Stage->setTitle('Warenkorb');
        $Stage->setDescription('Übersicht');
        $Stage->setMessage('Zeigt alle vorhandenen Warenkörbe an');
        $Stage->addButton(
            new Standard('Warenkorb anlegen', '/Billing/Accounting/Basket/Create')
        );

        $tblBasketAll = Basket::useService()->getBasketAll();

        if (!empty( $tblBasketAll )) {
            array_walk($tblBasketAll, function (TblBasket &$tblBasket) {

                $tblBasket->Number = $tblBasket->getId();
                $tblBasket->Option =
                    (new Standard('Weiter Bearbeiten', '/Billing/Accounting/Basket/Commodity/Select',
                        new Pencil(), array(
                            'Id' => $tblBasket->getId()
                        )))->__toString().
                    (new Standard('Name Bearbeiten', '/Billing/Accounting/Basket/Change',
                        new Edit(), array(
                            'Id' => $tblBasket->getId()
                        )))->__toString().
                    (new Standard('Löschen', '/Billing/Accounting/Basket/Destroy',
                        new Remove(), array(
                            'Id' => $tblBasket->getId()
                        )))->__toString();
            });
        }

        $Stage->setContent(
            new TableData($tblBasketAll, null,
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
    public function  frontendBasketCreate($Basket)
    {

        $Stage = new Stage();
        $Stage->setTitle('Warenkorb');
        $Stage->setDescription('Hinzufügen');
        $Stage->setMessage('Der Name des Warenkorbs ist Teil des Buchungstextes');
        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Basket',
            new ChevronLeft()
        ));

        $Form = new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new TextField('Basket[Name]', 'Name', 'Name', new Conversation()
                        ), 6),
                )),
            ))
        ));
        $Form->appendFormButton(new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Hinzufügen'));
        $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(Basket::useService()->createBasket($Form, $Basket));

        return $Stage;
    }

    /**
     * @param $Id
     * @param $Basket
     *
     * @return Stage
     */
    public function  frontendBasketChange($Id, $Basket)
    {

        $Stage = new Stage();
        $Stage->setTitle('Warenkorb');
        $Stage->setDescription('Bearbeiten');
        $Stage->setMessage('Der Name des Warenkorbs ist Teil des Buchungstextes');
        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Basket',
            new ChevronLeft()
        ));

        if (empty( $Id )) {
            $Stage->setContent(new Warning('Die Daten konnten nicht abgerufen werden'));
        } else {
            $tblBasket = Basket::useService()->getBasketById($Id);
            if (empty( $tblBasket )) {
                $Stage->setContent(new Warning('Der Warenkorb konnte nicht abgerufen werden'));
            } else {

                $Global = $this->getGlobal();
                if (!isset( $Global->POST['Basket'] )) {
                    $Global->POST['Basket']['Name'] = $tblBasket->getName();
                    $Global->savePost();
                }

                $Form = new Form(array(
                    new FormGroup(array(
                        new FormRow(array(
                            new FormColumn(
                                new TextField('Basket[Name]', 'Name', 'Name', new Conversation()
                                ), 6),
                        ))
                    ))
                ));
                $Form->appendFormButton(new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Änderungen speichern'));
                $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

                $Stage->setContent(Basket::useService()->changeBasket($Form, $tblBasket, $Basket));
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
    public function  frontendBasketDestroy($Id, $Confirm = false)
    {

        $Stage = new Stage('Warenkorb', 'Löschen');
        if ($Id) {
            $tblBasket = Basket::useService()->getBasketById($Id);
            if (!$Confirm) {

                $Item = array();
                $tblItemAll = Basket::useService()->getBasketItemAllByBasket($tblBasket);
                if ($tblItemAll) {
                    foreach ($tblItemAll as $tblItem) {
                        $Item[] = $tblItem->getServiceBillingCommodityItem()->getTblItem()->getName().'<br/>
                            Preis: '.$tblItem->getServiceBillingCommodityItem()->getTblItem()->getPriceString();
                    }
                }
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                        new Panel(new Question().' Diesen Warenkorb mit dem Namen "<b>'.$tblBasket->getName().'</b>" wirklich löschen?',
                            array(
                                $tblBasket->getName().' erstellt am: '.$tblBasket->getCreateDate(),
                                new Panel('Artikel', array(
                                    ( isset( $Item[0] ) ? new Muted(new Small($Item[0])) : false ),
                                    ( isset( $Item[1] ) ? new Muted(new Small($Item[1])) : false ),
                                    ( isset( $Item[2] ) ? new Muted(new Small($Item[2])) : false ),
                                    ( isset( $Item[3] ) ? new Muted(new Small($Item[3])) : false ),
                                    ( isset( $Item[4] ) ? new Muted(new Small($Item[4])) : false ),
                                    ( isset( $Item[5] ) ? new Muted(new Small($Item[5])) : false ),
                                    ( isset( $Item[6] ) ? new Muted(new Small($Item[6])) : false ),
                                )),
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
                    ))))
                );
            } else {

                // Destroy Basket
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            ( Basket::useService()->destroyBasket($tblBasket)
                                ? new \SPHERE\Common\Frontend\Message\Repository\Success('Der Warenkorb wurde gelöscht')
                                .new Redirect('/Billing/Accounting/Basket', 0)
                                : new \SPHERE\Common\Frontend\Message\Repository\Danger('Der Warenkorb konnte nicht gelöscht werden')
                                .new Redirect('/Billing/Accounting/Basket', 10)
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
                        new Redirect('/Billing/Accounting/Basket', 10)
                    )))
                )))
            );
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function  frontendBasketCommoditySelect($Id)
    {

        $Stage = new Stage();
        $Stage->setTitle('Warenkorb');
        $Stage->setDescription('Leistungen Auswählen');
        $Stage->setMessage('Bitte wählen Sie die Leistungen zur Fakturierung aus');
        $Stage->addButton(new Primary('Weiter', '/Billing/Accounting/Basket/Item',
            new ChevronRight(), array(
                'Id' => $Id
            )));

        $tblBasket = Basket::useService()->getBasketById($Id);
        $tblCommodityAll = Commodity::useService()->getCommodityAll();
        $tblCommodityAllByBasket = Basket::useService()->getCommodityAllByBasket($tblBasket);

        if (!empty( $tblCommodityAllByBasket )) {
            $tblCommodityAll = array_udiff($tblCommodityAll, $tblCommodityAllByBasket,
                function (TblCommodity $ObjectA, TblCommodity $ObjectB) {

                    return $ObjectA->getId() - $ObjectB->getId();
                }
            );

            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblCommodityAllByBasket,
                function (TblCommodity &$tblCommodity, $Index, TblBasket $tblBasket) {

                    $tblCommodity->Type = $tblCommodity->getTblCommodityType()->getName();
                    $tblCommodity->ItemCount = Commodity::useService()->countItemAllByCommodity($tblCommodity);
                    $tblCommodity->SumPriceItem = Commodity::useService()->sumPriceItemAllByCommodity($tblCommodity);
                    $tblCommodity->Option =
                        (new Danger('Entfernen', '/Billing/Accounting/Basket/Commodity/Remove',
                            new Minus(), array(
                                'Id'          => $tblBasket->getId(),
                                'CommodityId' => $tblCommodity->getId()
                            )))->__toString();
                }, $tblBasket);
        }

        $Options = true;
        if (!empty( $tblCommodityAll )) {
            if (empty( Basket::useService()->getCommodityAllByBasket($tblBasket) )) {
                /** @noinspection PhpUnusedParameterInspection */
                array_walk($tblCommodityAll, function (TblCommodity $tblCommodity, $Index, TblBasket $tblBasket) {

                    $tblCommodity->Type = $tblCommodity->getTblCommodityType()->getName();
                    $tblCommodity->ItemCount = Commodity::useService()->countItemAllByCommodity($tblCommodity);
                    $tblCommodity->SumPriceItem = Commodity::useService()->sumPriceItemAllByCommodity($tblCommodity);
                    $tblCommodity->Option =
                        (new Success('Hinzufügen', '/Billing/Accounting/Basket/Commodity/Add',
                            new Plus(), array(
                                'Id'          => $tblBasket->getId(),
                                'CommodityId' => $tblCommodity->getId()
                            )))->__toString();
                }, $tblBasket);
            } else {
                array_walk($tblCommodityAll, function (TblCommodity $tblCommodity) {

                    $tblCommodity->Type = $tblCommodity->getTblCommodityType()->getName();
                    $tblCommodity->ItemCount = Commodity::useService()->countItemAllByCommodity($tblCommodity);
                    $tblCommodity->SumPriceItem = Commodity::useService()->sumPriceItemAllByCommodity($tblCommodity);
                }, $tblBasket);
                $Options = false;
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Warenkorb - Nummer', $tblBasket->getId(),
                                Panel::PANEL_TYPE_SUCCESS), 3
                        ),
                        new LayoutColumn(
                            new Panel('Warenkorb - Name', $tblBasket->getName(),
                                Panel::PANEL_TYPE_SUCCESS), 6
                        ),
                        new LayoutColumn(
                            new Panel('Erstellt am', $tblBasket->getCreateDate(),
                                Panel::PANEL_TYPE_SUCCESS), 3
                        )
                    )),
                )),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                                new TableData($tblCommodityAllByBasket, null,
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
                    )),
                ), new Title('zugewiesene Leistungen')),
                ( $Options ) ?
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                    new TableData($tblCommodityAll, null,
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
                        )),
                    ), new Title('mögliche Leistungen'))
                    :
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                    new TableData($tblCommodityAll, null,
                                        array(
                                            'Name'         => 'Name',
                                            'Description'  => 'Beschreibung',
                                            'Type'         => 'Leistungsart',
                                            'ItemCount'    => 'Artikelanzahl',
                                            'SumPriceItem' => 'Gesamtpreis',
                                        )
                                    )
                                )
                            )
                        )),
                    ), new Title('mögliche Leistungen'))
            ))
        );

        return $Stage;
    }

    /**
     * @param $Id
     * @param $CommodityId
     *
     * @return Stage
     */
    public function  frontendBasketCommodityAdd($Id, $CommodityId)
    {

        $Stage = new Stage();
        $Stage->setTitle('Warenkorb');
        $Stage->setDescription('Leistung Hinzufügen');

        $tblBasket = Basket::useService()->getBasketById($Id);
        $tblCommodity = Commodity::useService()->getCommodityById($CommodityId);
        $Stage->setContent(Basket::useService()->addCommodityToBasket($tblBasket, $tblCommodity));

        return $Stage;
    }

    /**
     * @param $Id
     * @param $CommodityId
     *
     * @return Stage
     */
    public function  frontendBasketCommodityRemove($Id, $CommodityId)
    {

        $Stage = new Stage();
        $Stage->setTitle('Warenkorb');
        $Stage->setDescription('Leistung Entfernen');

        $tblBasket = Basket::useService()->getBasketById($Id);
        $tblCommodity = Commodity::useService()->getCommodityById($CommodityId);
        $Stage->setContent(Basket::useService()->removeCommodityToBasket($tblBasket, $tblCommodity));

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function  frontendBasketItemStatus($Id)
    {

        $Stage = new Stage();
        $Stage->setTitle('Warenkorb');
        $Stage->setDescription('Artikel Übersicht');
        $Stage->setMessage('Zeigt alle Artikel im Warenkorb');
        $Stage->addButton(new Primary('Zurück', '/Billing/Accounting/Basket/Commodity/Select',
            new ChevronLeft(), array(
                'Id' => $Id
            )));
        $Stage->addButton(new Primary('Weiter', '/Billing/Accounting/Basket/Person/Select',
            new ChevronRight(), array(
                'Id' => $Id
            )));

        $tblBasket = Basket::useService()->getBasketById($Id);
        $tblBasketItemAll = Basket::useService()->getBasketItemAllByBasket($tblBasket);

        if (!empty( $tblBasketItemAll )) {
            array_walk($tblBasketItemAll, function (TblBasketItem &$tblBasketItem) {

                $tblCommodity = $tblBasketItem->getServiceBillingCommodityItem()->getTblCommodity();
                $tblItem = $tblBasketItem->getServiceBillingCommodityItem()->getTblItem();
                $tblBasketItem->CommodityName = $tblCommodity->getName();
                $tblBasketItem->ItemName = $tblItem->getName();
                $tblBasketItem->TotalPriceString = $tblBasketItem->getTotalPriceString();
                $tblBasketItem->QuantityString = str_replace('.', ',', $tblBasketItem->getQuantity());
                $tblBasketItem->PriceString = $tblBasketItem->getPriceString();
                $tblBasketItem->Option =
                    (new Standard('Bearbeiten', '/Billing/Accounting/Basket/Item/Change',
                        new Edit(), array(
                            'Id' => $tblBasketItem->getId()
                        )))->__toString().
                    (new Danger('Entfernen',
                        '/Billing/Accounting/Basket/Item/Remove',
                        new Minus(), array(
                            'Id' => $tblBasketItem->getId()
                        )))->__toString();
            });
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Warenkorb - Nummer', $tblBasket->getId(),
                                Panel::PANEL_TYPE_SUCCESS), 3
                        ),
                        new LayoutColumn(
                            new Panel('Warenkorb - Name', $tblBasket->getName(),
                                Panel::PANEL_TYPE_SUCCESS), 6
                        ),
                        new LayoutColumn(
                            new Panel('Erstellt am', $tblBasket->getCreateDate(),
                                Panel::PANEL_TYPE_SUCCESS), 3
                        )
                    )),
                )),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                                new TableData($tblBasketItemAll, null,
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
                    )),
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function  frontendBasketItemRemove($Id)
    {

        $Stage = new Stage();
        $Stage->setTitle('Warenkorb');
        $Stage->setDescription('Artikel Entfernen');

        $tblBasketItem = Basket::useService()->getBasketItemById($Id);
        $Stage->setContent(Basket::useService()->removeBasketItem($tblBasketItem));

        return $Stage;
    }

    /**
     * @param $Id
     * @param $BasketItem
     *
     * @return Stage
     */
    public function  frontendBasketItemChange($Id, $BasketItem)
    {

        $tblBasketItem = Basket::useService()->getBasketItemById($Id);
        $Stage = new Stage();
        $Stage->setTitle('Warenkorb');
        $Stage->setDescription('Artikel Bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Basket/Item',
            new ChevronLeft(), array(
                'Id' => $tblBasketItem->getTblBasket()->getId()
            )));

        if (empty( $Id )) {
            $Stage->setContent(new Warning('Die Daten konnten nicht abgerufen werden'));
        } else {
            if (empty( $tblBasketItem )) {
                $Stage->setContent(new Warning('Der Artikel konnte nicht abgerufen werden'));
            } else {

                $Global = $this->getGlobal();
                if (!isset( $Global->POST['BasketItem'] )) {
                    $Global->POST['BasketItem']['Price'] = str_replace('.', ',', $tblBasketItem->getPrice());
                    $Global->POST['BasketItem']['Quantity'] = str_replace('.', ',', $tblBasketItem->getQuantity());
                    $Global->savePost();
                }

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel('Leistung-Name',
                                        $tblBasketItem->getServiceBillingCommodityItem()->getTblCommodity()->getName()
                                        , Panel::PANEL_TYPE_SUCCESS), 3
                                ),
                                new LayoutColumn(
                                    new Panel('Artikel-Name',
                                        $tblBasketItem->getServiceBillingCommodityItem()->getTblItem()->getName()
                                        , Panel::PANEL_TYPE_SUCCESS), 3
                                ),
                                new LayoutColumn(
                                    new Panel('Artikel-Beschreibung',
                                        $tblBasketItem->getServiceBillingCommodityItem()->getTblItem()->getDescription()
                                        , Panel::PANEL_TYPE_SUCCESS), 6
                                )
                            )),
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                        Basket::useService()->changeBasketItem(
                                            new Form(array(
                                                new FormGroup(array(
                                                    new FormRow(array(
                                                        new FormColumn(
                                                            new TextField('BasketItem[Price]', 'Preis in €', 'Preis',
                                                                new MoneyEuro()
                                                            ), 6),
                                                        new FormColumn(
                                                            new TextField('BasketItem[Quantity]', 'Menge', 'Menge',
                                                                new Quantity()
                                                            ), 6)
                                                    ))
                                                ))
                                            ),
                                                new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Änderungen speichern')
                                            ), $tblBasketItem, $BasketItem
                                        )
                                    )
                                )
                            ))
                        ))
                    ))
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
    public function  frontendBasketPersonSelect($Id)
    {

        $Stage = new Stage();
        $Stage->setTitle('Warenkorb');
        $Stage->setDescription('Personen Auswählen');
        $Stage->setMessage('Bitte wählen Sie Personen zur Fakturierung aus');
        $Stage->addButton(new Primary('Zurück', '/Billing/Accounting/Basket/Item', new ChevronLeft(),
            array('Id' => $Id)));
        $Stage->addButton(new Primary('Weiter', '/Billing/Accounting/Basket/Summary', new ChevronRight(),
            array('Id' => $Id)));

        $tblBasket = Basket::useService()->getBasketById($Id);
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

        if (!empty( $tblBasketPersonList )) {
            array_walk($tblBasketPersonList, function (TblBasketPerson &$tblBasketPerson) {

                $tblPerson = $tblBasketPerson->getServiceManagementPerson();
                $tblBasketPerson->FirstName = $tblPerson->getFirstName();
                $tblBasketPerson->LastName = $tblPerson->getLastName();
                $tblBasketPerson->Option =
                    (new Danger('Entfernen', '/Billing/Accounting/Basket/Person/Remove',
                        new Minus(), array(
                            'Id' => $tblBasketPerson->getId()
                        )))->__toString();
            });
        }

        if (!empty( $tblPersonAll )) {
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblPersonAll, function (TblPerson &$tblPerson, $Index, TblBasket $tblBasket) {

                $tblPerson->Option =
                    (new Success('Hinzufügen', '/Billing/Accounting/Basket/Person/Add',
                        new Plus(), array(
                            'Id'       => $tblBasket->getId(),
                            'PersonId' => $tblPerson->getId()
                        )))->__toString();
            }, $tblBasket);
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Warenkorb - Nummer', $tblBasket->getId(),
                                Panel::PANEL_TYPE_SUCCESS), 3
                        ),
                        new LayoutColumn(
                            new Panel('Warenkorb - Name', $tblBasket->getName(),
                                Panel::PANEL_TYPE_SUCCESS), 6
                        ),
                        new LayoutColumn(
                            new Panel('Erstellt am', $tblBasket->getCreateDate(),
                                Panel::PANEL_TYPE_SUCCESS), 3
                        )
                    )),
                )),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                                new TableData($tblBasketPersonList, null,
                                    array(
                                        'FirstName' => 'Vorname',
                                        'LastName'  => 'Nachname',
                                        'Option'    => 'Option '
                                    )
                                )
                            )
                        )
                    )),
                ), new Title('zugewiesene Personen')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                                new TableData($tblPersonAll, null,
                                    array(
                                        'FirstName' => 'Vorname',
                                        'LastName'  => 'Nachname',
                                        'Option'    => 'Option'
                                    )
                                )
                            )
                        )
                    )),
                ), new Title('mögliche Personen'))
            ))
        );

        return $Stage;
    }

    /**
     * @param $Id
     * @param $PersonId
     *
     * @return Stage
     */
    public function  frontendBasketPersonAdd($Id, $PersonId)
    {

        $Stage = new Stage();
        $Stage->setTitle('Warenkorb');
        $Stage->setDescription('Person Hinzufügen');

        $tblBasket = Basket::useService()->getBasketById($Id);
        $tblPerson = Person::useService()->getPersonById($PersonId);
        $Stage->setContent(Basket::useService()->addBasketPerson($tblBasket, $tblPerson));

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function  frontendBasketPersonRemove($Id)
    {

        $Stage = new Stage();
        $Stage->setTitle('Warenkorb');
        $Stage->setDescription('Person Entfernen');

        $tblBasketPerson = Basket::useService()->getBasketPersonById($Id);
        $Stage->setContent(Basket::useService()->removeBasketPerson($tblBasketPerson));

        return $Stage;
    }

    /**
     * @param $Id
     * @param $Basket
     *
     * @return Stage
     */
    public function  frontendBasketSummary($Id, $Basket = null)
    {

        $Stage = new Stage();
        $Stage->setTitle('Warenkorb');
        $Stage->setDescription('Zusammenfassung');
        $Stage->setMessage('Schließen Sie den Warenkorb zur Fakturierung ab');
        $Stage->addButton(new Primary('Zurück', '/Billing/Accounting/Basket/Person/Select',
            new ChevronLeft(), array(
                'Id' => $Id
            )));

        $tblBasket = Basket::useService()->getBasketById($Id);
        $tblBasketItemAll = Basket::useService()->getBasketItemAllByBasket($tblBasket);
        $tblPersonByBasketList = Basket::useService()->getPersonAllByBasket($tblBasket);

        if (!empty( $tblBasketItemAll )) {
            array_walk($tblBasketItemAll, function (TblBasketItem &$tblBasketItem) {

                $tblCommodity = $tblBasketItem->getServiceBillingCommodityItem()->getTblCommodity();
                $tblItem = $tblBasketItem->getServiceBillingCommodityItem()->getTblItem();
                $tblBasketItem->CommodityName = $tblCommodity->getName();
                $tblBasketItem->ItemName = $tblItem->getName();
                $tblBasketItem->TotalPriceString = $tblBasketItem->getTotalPriceString();
                $tblBasketItem->QuantityString = str_replace('.', ',', $tblBasketItem->getQuantity());
                $tblBasketItem->PriceString = $tblBasketItem->getPriceString();
            });
        }

        $Result = 0.00;
        foreach ($tblBasketItemAll as $tblBasketItem) {
            if ($tblBasketItem->getServiceBillingCommodityItem()->getTblCommodity()->getTblCommodityType()->getName() === 'Sammelleistung') {
                $Numerator = count($tblPersonByBasketList);

                $Result = ( ( $tblBasketItem->getPrice() * $tblBasketItem->getQuantity() ) / $Numerator ) + $Result;
            } else {
                $Result = ( $tblBasketItem->getPrice() * $tblBasketItem->getQuantity() ) + $Result;
            }
        }

        $Form = new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new DatePicker('Basket[Date]', 'Zahlungsdatum (Fälligkeit)',
                            'Zahlungsdatum (Fälligkeit)',
                            new Time())
                        , 3)
                )),
            ), new \SPHERE\Common\Frontend\Form\Repository\Title('Zahlungsdatum'))
        );
        $Form->appendFormButton(new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Warenkorb fakturieren (prüfen)'));
        $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Warenkorb - Nummer', $tblBasket->getId(),
                                Panel::PANEL_TYPE_SUCCESS), 3
                        ),
                        new LayoutColumn(
                            new Panel('Warenkorb - Name', $tblBasket->getName(),
                                Panel::PANEL_TYPE_SUCCESS), 6
                        ),
                        new LayoutColumn(
                            new Panel('Erstellt am', $tblBasket->getCreateDate(),
                                Panel::PANEL_TYPE_SUCCESS), 3
                        )
                    )),
                )),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            array(
                                new TableData($tblBasketItemAll, null,
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
                    ))
                ), new Title('Artikel')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(), 8),
                        new LayoutColumn(array(
                            new Panel('Preis pro Person: '.$Result.' €', '', Panel::PANEL_TYPE_PRIMARY)
                        ), 3)
                    ))
                )),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            array(
                                new TableData($tblPersonByBasketList, null,
                                    array(
                                        'FirstName' => 'Vorname',
                                        'LastName'  => 'Nachname'
                                    )
                                )
                            )
                        )
                    ))
                ), new Title('Personen')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            Basket::useService()->checkBasket($Form, $tblBasket, $Basket)
                        )
                    ))
                ))
            ))
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
    public function  frontendBasketDebtorSelect($Id, $Date, $Data, $Save)
    {

        $Stage = new Stage();
        $Stage->setTitle('Warenkorb');
        $Stage->setDescription('Debitoren zuordnen');
        $Stage->setMessage('Es konnten im Warenkorb nicht zu alle Personen bei allen Leistungen eindeutig ein Debitor
            ermittelt werden. Es werden alle nicht automatisch zuordenbaren Kombinationen von Personen und Leistungen
            angezeigt. Bitte weisen Sie die entsprechenden Debitoren zu');

        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Save'] )) {
            $Global->POST['Save'] = 1;
        }
        $Global->savePost();

        $tblBasket = Basket::useService()->getBasketById($Id);
        $tblBasketCommodityList = Basket::useService()->getBasketCommodityAllByBasket($tblBasket);
        /**@var TblBasketCommodity $tblBasketCommodity */
        array_walk($tblBasketCommodityList, function (TblBasketCommodity $tblBasketCommodity) {

            $tblBasketCommodityDebtorList = Basket::useService()->getBasketCommodityDebtorAllByBasketCommodity($tblBasketCommodity);

            $tblBasketCommodity->Name = $tblBasketCommodity->getServiceManagementPerson()->getFullName();
            $tblBasketCommodity->Commodity = $tblBasketCommodity->getServiceBillingCommodity()->getName();

            $tblBasketCommodity->Select = new SelectBox('Data['.$tblBasketCommodity->getId().']', '', array(
                '{{ ServiceBillingDebtor.DebtorNumber }}'
                .' - {{ ServiceBillingDebtor.ServiceManagementPerson.FullName }}'
                .'{% if( ServiceBillingDebtor.Description is not empty) %} - {{ ServiceBillingDebtor.Description }}{% endif %}'
                => $tblBasketCommodityDebtorList
            ));
        });

        $Form = new Form(
            new FormGroup(array(
                new FormRow(
                    new FormColumn(
                        new TableData(
                            $tblBasketCommodityList, null, array(
                            'Name'      => 'Person',
                            'Commodity' => 'Leistung',
                            'Select'    => 'Debitorennummer - Debitor - Beschreibung'
                        ), false)
                    )
                ),
                new FormRow(array(
                    new FormColumn(
                        new SelectBox('Save', '', array(
                            1 => 'Nicht speichern',
                            2 => 'Als Standard speichern'
                        ))
                        , 3),
                ))
            ))
        );
        $Form->appendFormButton(new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Debitoren zuordnen (prüfen)'));
        $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Warenkorb - Nummer', $tblBasket->getId(),
                                Panel::PANEL_TYPE_SUCCESS), 3
                        ),
                        new LayoutColumn(
                            new Panel('Warenkorb - Name', $tblBasket->getName(),
                                Panel::PANEL_TYPE_SUCCESS), 6
                        ),
                        new LayoutColumn(
                            new Panel('Erstellt am', $tblBasket->getCreateDate(),
                                Panel::PANEL_TYPE_SUCCESS), 3
                        )
                    )),
                )),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            Basket::useService()->checkDebtors($Form, $Id, $Date, $Data, $Save)
                        )
                    ))
                )),
            ))
        );

        return $Stage;
    }
}
