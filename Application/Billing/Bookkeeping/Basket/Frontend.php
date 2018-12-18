<?php

namespace SPHERE\Application\Billing\Bookkeeping\Basket;

use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasket;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Conversation;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Equalizer;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Repeat;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Bookkeeping\Basket
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendBasketList()
    {

        $Stage = new Stage('Abrechnung', 'Übersicht');
        $Stage->setMessage('Zeigt alle vorhandenen Abrechnungen an');
        $tblBasketAll = Basket::useService()->getBasketAll();
        //ToDO API
        $Stage->addButton((new Primary('Abrechnung hinzufügen', '#', new Plus())));

        $TableContent = array();
        if (!empty( $tblBasketAll )) {
            array_walk($tblBasketAll, function (TblBasket &$tblBasket) use (&$TableContent) {

                $Item['Number'] = $tblBasket->getId();
                $Item['Name'] = $tblBasket->getName();
                $Item['Description'] = $tblBasket->getDescription();
//                $Item['CreateDate'] = $tblBasket->getCreateDate();
                $Item['CountDebtorSelection'] = '';
                $Count = Basket::useService()->countDebtorSelectionCountByBasket($tblBasket);
                if ($Count) {
                    $Item['CountDebtorSelection'] = $Count;
                }
                $Item['Item'] = '';
                $tblItemList = Basket::useService()->getItemAllByBasket($tblBasket);
                $ItemArray = array();
                if ($tblItemList) {
                    foreach ($tblItemList as $tblItem) {
                        $ItemArray[] = $tblItem->getName();
                    }
                    sort($ItemArray);
                    $Item['Item'] = implode(', ', $ItemArray);
                }

                $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);

                $Item['Option'] =
                    (new Standard('', '/Billing/Bookkeeping/Basket/Change',
                        new Edit(), array(
                            'Id' => $tblBasket->getId()
                        ), 'Name bearbeiten'))->__toString().
                    ( !$tblBasketVerification ?
                        (new Standard('', '/Billing/Bookkeeping/Basket/Content',
                            new Listing(), array(
                                'Id' => $tblBasket->getId()
                            ), 'Warenkorb füllen'))->__toString() :

                        (new Standard('', '/Billing/Bookkeeping/Basket/Verification',
                            new Equalizer(), array(
                                'Id' => $tblBasket->getId()
                            ), 'Berechnung bearbeiten'))->__toString()
                        .new Standard(''
                            , '/Billing/Bookkeeping/Basket/Verification/Destroy', new Repeat()
                            , array('BasketId' => $tblBasket->getId()), 'Berechnung leeren') ).
                    ( !$tblBasketVerification ?
                        (new Standard('', '/Billing/Bookkeeping/Basket/Destroy',
                            new Remove(), array(
                                'Id' => $tblBasket->getId()
                            ), 'Löschen'))->__toString() : null );
                array_push($TableContent, $Item);
            });
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null,
                                array(
                                    'Number'               => 'Nummer',
                                    'Name'                 => 'Name',
                                    'Description'          => 'Beschreibung',
                                    'CountDebtorSelection' => 'Anzahl Zahlungszuweisungen',
                                    'Item'                 => 'Artikel',
                                    'Option'               => ''
                                )
                            )
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    public function formBasket()
    {

        //ToDO erweitern um ItemList
        return new Form(array(
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
    }
}
