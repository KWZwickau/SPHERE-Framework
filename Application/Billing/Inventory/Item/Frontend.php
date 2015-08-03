<?php

namespace SPHERE\Application\Billing\Inventory\Item;

use SPHERE\Application\Billing\Inventory\Commodity\Commodity;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Conversation;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\Money;
use SPHERE\Common\Frontend\Icon\Repository\MoneyEuro;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\IFrontendInterface;
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
    public function frontendItemStatus()
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Artikel' );
        $Stage->setDescription( 'Übersicht' );
        // ToDo
        $Stage->setMessage(
            'Zeigt alle verfügbaren Artikel an. <br>
            Artikel sind Preise für erbrachte Dienste, die Abhängigkeiten zugewiesen bekommen können. <br />
            Somit werden bei Rechnungen nur die Artikel berechnet, <br />
            die <b>keine</b> oder die <b>zutreffenden</b> Abhängigkeiten für die einzelne Person besitzen.' );
        $Stage->addButton(
            new Primary( 'Artikel anlegen', '/Billing/Inventory/Item/Create', new Plus() )
        );

        $tblItemAll = Item::useService()->entityItemAll();

        if ( !empty( $tblItemAll ) ) {
            array_walk( $tblItemAll, function ( TblItem $tblItem ) {

                $tblItem->PriceString = $tblItem->getPriceString();
                if ( Commodity::useService()->entityCommodityItemAllByItem( $tblItem ) ) {
                    $tblItem->Option =
                        ( new Primary( 'Bearbeiten', '/Billing/Inventory/Item/Edit',
                            new Edit(), array(
                                'Id' => $tblItem->getId()
                            ) ) )->__toString().
                        ( new Primary( 'FIBU-Konten auswählen', '/Billing/Inventory/Item/Account/Select',
                            new Listing(), array(
                                'Id' => $tblItem->getId()
                            ) ) )->__toString();
                } else {
                    $tblItem->Option =
                        ( new Primary( 'Bearbeiten', '/Billing/Inventory/Item/Edit',
                            new Edit(), array(
                                'Id' => $tblItem->getId()
                            ) ) )->__toString().
                        ( new Primary( 'FIBU-Konten auswählen', '/Billing/Inventory/Item/Account/Select',
                            new Listing(), array(
                                'Id' => $tblItem->getId()
                            ) ) )->__toString().
                        ( new Danger( 'Löschen', '/Billing/Inventory/Item/Delete',
                            new Remove(), array(
                                'Id' => $tblItem->getId()
                            ) ) )->__toString();
                }
            } );
        }

        $Stage->setContent(
            new TableData( $tblItemAll, null,
                array(
                    'Name'        => 'Name',
                    'Description' => 'Beschreibung',
                    'PriceString' => 'Preis',
                    'Option'      => 'Option'
                )
            )
        );

        return $Stage;
    }

    /**
     * @param $Item
     *
     * @return Stage
     */
    public function frontendItemCreate( $Item )
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Artikel' );
        $Stage->setDescription( 'Hinzufügen' );
        $Stage->setMessage(
            '<b>Hinweis:</b> <br>
            Ist ein Bildungsgang unter der <i>Bedingung Bildungsgang</i> ausgewählt, wird der Artikel nur für
            Personen (Schüler) berechnet welche diesem Bildungsgang angehören. <br>
            Ist eine Kind-Reihenfolge unter der <i>Bedingung Kind-Reihenfolge</i> ausgewählt, wird der Artikel nur für
            Personen (Schüler) berechnet welche dieser Kind-Reihenfolge entsprechen. <br>
            Beide Bedingungen können einzeln ausgewählt werden, bei der Wahl beider Bedingungen werden diese
            <b>Und</b> verknüpft.
        ' );
        $Stage->addButton( new Primary( 'Zurück', '/Billing/Inventory//Item',
            new ChevronLeft()
        ) );

//        $tblCourseAll = Management::serviceCourse()->entityCourseAll();   //todo
//        array_unshift( $tblCourseAll, new TblCourse( '' ) );
//        $tblChildRankAll = Management::serviceStudent()->entityChildRankAll();
//        array_unshift( $tblChildRankAll, new TblChildRank( '' ) );

        $Stage->setContent( Item::useService()->executeCreateItem(
            new Form( array(
                new FormGroup( array(
                    new FormRow( array(
                        new FormColumn(
                            new TextField( 'Item[Name]', 'Name', 'Name', new Conversation()
                            ), 6 ),
                        new FormColumn(
                            new TextField( 'Item[Price]', 'Preis in €', 'Preis', new MoneyEuro()
                            ), 6 )
                    ) ),
                    new FormRow( array(
                        new FormColumn(
                            new TextField( 'Item[CostUnit]', 'Kostenstelle', 'Kostenstelle', new Money()
                            ), 6 )
                    ) ),
                    new FormRow( array(
                        new FormColumn(
                            new TextField( 'Item[Description]', 'Beschreibung', 'Beschreibung', new Conversation()
                            ), 12 )
                    ) ),
//                    new FormRow( array(       //todo
//                        new FormColumn(
//                            new SelectBox( 'Item[Course]', 'Bedingung Bildungsgang',
//                                array('Name' => $tblCourseAll
//                                ) )
//                            , 6 ),
//                        new FormColumn(
//                            new SelectBox( 'Item[ChildRank]', 'Bedingung Kind-Reihenfolge',
//                                array('Description' => $tblChildRankAll
//                                ) )
//                            , 6 )
//                    ) )
                ) ) ), new \SPHERE\Common\Frontend\Form\Repository\Button\Primary( 'Hinzufügen' ) ), $Item ) );

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendItemDelete( $Id )
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Artikel' );
        $Stage->setDescription( 'Entfernen' );

        $tblItem = Item::useService()->entityItemById( $Id );
        $Stage->setContent( Item::useService()->executeDeleteItem( $tblItem ) );

        return $Stage;
    }

    /**
     * @param $Id
     * @param $Item
     *
     * @return Stage
     */
    public function frontendItemEdit( $Id, $Item )
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Artikel' );
        $Stage->setDescription( 'Bearbeiten' );
        $Stage->setMessage(
            '<b>Hinweis:</b> <br>
            Ist ein Bildungsgang unter der <i>Bedingung Bildungsgang</i> ausgewählt, wird der Artikel nur für
            Personen (Schüler) berechnet welche diesem Bildungsgang angehören. <br>
            Ist eine Kind-Reihenfolge unter der <i>Bedingung Kind-Reihenfolge</i> ausgewählt, wird der Artikel nur für
            Personen (Schüler) berechnet welche dieser Kind-Reihenfolge entsprechen. <br>
            Beide Bedingungen können einzeln ausgewählt werden, bei der Wahl beider Bedingungen werden diese
            <b>Und</b> verknüpft.
        ' );
        $Stage->addButton( new Primary( 'Zurück', '/Billing/Inventory//Item',
            new ChevronLeft()
        ) );

//        $tblCourseAll = Management::serviceCourse()->entityCourseAll();   //todo
//        array_unshift( $tblCourseAll, new TblCourse( '' ) );
//        $tblChildRankAll = Management::serviceStudent()->entityChildRankAll();
//        array_unshift( $tblChildRankAll, new TblChildRank( '' ) );

        if ( empty( $Id ) ) {
            $Stage->setContent( new Warning( 'Die Daten konnten nicht abgerufen werden' ) );
        } else {
            $tblItem = Item::useService()->entityItemById( $Id );
            if ( empty( $tblItem ) ) {
                $Stage->setContent( new Warning( 'Der Artikel konnte nicht abgerufen werden' ) );
            } else {

                $Global = $this->getGlobal();
                if ( !isset( $Global->POST['Item'] ) ) {
                    $Global->POST['Item']['Name'] = $tblItem->getName();
                    $Global->POST['Item']['Description'] = $tblItem->getDescription();
                    $Global->POST['Item']['Price'] = str_replace( '.', ',', $tblItem->getPrice() );
                    $Global->POST['Item']['CostUnit'] = $tblItem->getCostUnit();
//                    if ( $tblItem->getServiceManagementCourse() ) {
//                        $Global->POST['Item']['Course'] = $tblItem->getServiceManagementCourse()->getId();
//                    }
//                    if ( $tblItem->getServiceManagementStudentChildRank() ) {
//                        $Global->POST['Item']['ChildRank'] = $tblItem->getServiceManagementStudentChildRank()->getId();
//                    }
                    $Global->savePost();
                }

                $Stage->setContent( Item::useService()->executeEditItem(
                    new Form( array(
                        new FormGroup( array(
                            new FormRow( array(
                                new FormColumn(
                                    new TextField( 'Item[Name]', 'Name', 'Name', new Conversation()
                                    ), 6 ),
                                new FormColumn(
                                    new TextField( 'Item[Price]', 'Preis in €', 'Preis', new MoneyEuro()
                                    ), 6 )
                            ) ),
                            new FormRow( array(
                                new FormColumn(
                                    new TextField( 'Item[CostUnit]', 'Kostenstelle', 'Kostenstelle', new Money()
                                    ), 6 )
                            ) ),
                            new FormRow( array(
                                new FormColumn(
                                    new TextField( 'Item[Description]', 'Beschreibung', 'Beschreibung', new Conversation()
                                    ), 12 )
                            ) ),
//                            new FormRow( array(   //todo
//                                new FormColumn(
//                                    new SelectBox( 'Item[Course]', 'Bedingung Bildungsgang',
//                                        array('Name' => $tblCourseAll
//                                        ) )
//                                    , 6 ),
//                                new FormColumn(
//                                    new SelectBox( 'Item[ChildRank]', 'Bedingung Kind-Reihenfolge',
//                                        array('Description' => $tblChildRankAll
//                                        ) )
//                                    , 6 )
//                            ) )
                        ) ) ), new \SPHERE\Common\Frontend\Form\Repository\Button\Primary( 'Änderungen speichern' )
                    ), $tblItem, $Item ) );
            }
        }

        return $Stage;
    }
}