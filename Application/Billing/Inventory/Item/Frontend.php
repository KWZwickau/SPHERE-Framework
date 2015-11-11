<?php

namespace SPHERE\Application\Billing\Inventory\Item;

use SPHERE\Application\Billing\Inventory\Commodity\Commodity;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
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
use SPHERE\Common\Frontend\Icon\Repository\MoneyEuro;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
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

class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendItemStatus()
    {

        $Stage = new Stage();
        $Stage->setTitle('Artikel');
        $Stage->setDescription('Übersicht');
        $Stage->setMessage(
            'Zeigt alle verfügbaren Artikel an. <br>');
//            Artikel sind Preise für erbrachte Dienste, die Abhängigkeiten zugewiesen bekommen können. <br />
//            Somit werden bei Rechnungen nur die Artikel berechnet, <br />
//            die <b>keine</b> oder die <b>zutreffenden</b> Abhängigkeiten für die einzelne Person besitzen.');
        $Stage->addButton(
            new Standard('Artikel anlegen', '/Billing/Inventory/Item/Create', new Plus())
        );

        $tblItemAll = Item::useService()->getItemAll();

        if (!empty( $tblItemAll )) {
            array_walk($tblItemAll, function (TblItem $tblItem) {

                $tblItem->PriceString = $tblItem->getPriceString();
                if (Commodity::useService()->getCommodityItemAllByItem($tblItem)) {
                    $tblItem->Option =
                        (new Standard('Bearbeiten', '/Billing/Inventory/Item/Change',
                            new Pencil(), array(
                                'Id' => $tblItem->getId()
                            )))->__toString().
                        (new Standard('FIBU-Konten auswählen', '/Billing/Inventory/Commodity/Item/Account/Select',
                            new Listing(), array(
                                'Id' => $tblItem->getId()
                            )))->__toString();
                } else {
                    $tblItem->Option =
                        (new Standard('Bearbeiten', '/Billing/Inventory/Item/Change',
                            new Pencil(), array(
                                'Id' => $tblItem->getId()
                            )))->__toString().
                        (new Standard('FIBU-Konten auswählen', '/Billing/Inventory/Commodity/Item/Account/Select',
                            new Listing(), array(
                                'Id' => $tblItem->getId()
                            )))->__toString().
                        (new Standard('Löschen', '/Billing/Inventory/Item/Destroy',
                            new Remove(), array(
                                'Id' => $tblItem->getId()
                            )))->__toString();
                }
            });
        }

        $Stage->setContent(
            new TableData($tblItemAll, null,
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
    public function frontendItemCreate($Item)
    {

        $Stage = new Stage();
        $Stage->setTitle('Artikel');
        $Stage->setDescription('Hinzufügen');
//        $Stage->setMessage(
//            '<b>Hinweis:</b> <br>
//            Ist ein Bildungsgang unter der <i>Bedingung Bildungsgang</i> ausgewählt, wird der Artikel nur für
//            Personen (Schüler) berechnet welche diesem Bildungsgang angehören. <br>
//            Ist eine Kind-Reihenfolge unter der <i>Bedingung Kind-Reihenfolge</i> ausgewählt, wird der Artikel nur für
//            Personen (Schüler) berechnet welche dieser Kind-Reihenfolge entsprechen. <br>
//            Beide Bedingungen können einzeln ausgewählt werden, bei der Wahl beider Bedingungen werden diese
//            <b>Und</b> verknüpft.
//        ');
        $Stage->addButton(new Standard('Zurück', '/Billing/Inventory/Item',
            new ChevronLeft()
        ));

        //        $tblCourseAll = Management::serviceCourse()->entityCourseAll();   //todo
        //        array_unshift( $tblCourseAll, new TblCourse( '' ) );
        //        $tblChildRankAll = Management::serviceStudent()->entityChildRankAll();
        //        array_unshift( $tblChildRankAll, new TblChildRank( '' ) );

        $Form = $this->formItem()
            ->appendFormButton(new Primary('Hinzufügen'))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(Item::useService()->createItem($Form, $Item));

        return $Stage;
    }

    public function formItem()
    {

        return new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new TextField('Item[Name]', 'Name', 'Name', new Conversation()
                        ), 4),
                    new FormColumn(
                        new TextField('Item[Price]', 'Preis in €', 'Preis', new MoneyEuro()
                        ), 4),
                    new FormColumn(
                        new TextField('Item[CostUnit]', 'Kostenstelle', 'Kostenstelle', new Money()
                        ), 4)
                )),
                new FormRow(array(
                    new FormColumn(
                        new TextField('Item[Description]', 'Beschreibung', 'Beschreibung', new Conversation()
                        ), 12)
                )),
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
            ))
        ));
    }

    /**
     * @param            $Id
     * @param bool|false $Confirm
     *
     * @return Stage
     */
    public function frontendItemDestroy($Id, $Confirm = false)
    {

        $Stage = new Stage('Artikel', 'Entfernen');
        if ($Id) {
            $tblItem = Item::useService()->getItemById($Id);
            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                        new Panel(new Question().' Diesen Artikel "'.$tblItem->getName().'" wirklich entfernen?',
                            array(
                                $tblItem->getName().'<br/>'
                                .$tblItem->getPriceString(),
                            ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Billing/Inventory/Item/Destroy', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            .new Standard(
                                'Nein', '/Billing/Inventory/Item', new Disable()
                            )
                        )
                    ))))
                );
            } else {

                // Destroy Group
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            ( Item::useService()->destroyItem($tblItem)
                                ? new Success('Der Artikel wurde gelöscht')
                                .new Redirect('/Billing/Inventory/Item', 0)
                                : new Danger('Der Artikel konnte nicht gelöscht werden')
                                .new Redirect('/Billing/Inventory/Item', 10)
                            )
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger('Der Artikel konnte nicht gefunden werden'),
                        new Redirect('/Billing/Inventory/Item', 3)
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
    public function frontendItemChange($Id, $Item)
    {

        $Stage = new Stage();
        $Stage->setTitle('Artikel');
        $Stage->setDescription('Bearbeiten');
//        $Stage->setMessage(
//            '<b>Hinweis:</b> <br>
//            Ist ein Bildungsgang unter der <i>Bedingung Bildungsgang</i> ausgewählt, wird der Artikel nur für
//            Personen (Schüler) berechnet welche diesem Bildungsgang angehören. <br>
//            Ist eine Kind-Reihenfolge unter der <i>Bedingung Kind-Reihenfolge</i> ausgewählt, wird der Artikel nur für
//            Personen (Schüler) berechnet welche dieser Kind-Reihenfolge entsprechen. <br>
//            Beide Bedingungen können einzeln ausgewählt werden, bei der Wahl beider Bedingungen werden diese
//            <b>Und</b> verknüpft.
//        ');
        $Stage->addButton(new Standard('Zurück', '/Billing/Inventory/Item',
            new ChevronLeft()
        ));

        //        $tblCourseAll = Management::serviceCourse()->entityCourseAll();   //todo
        //        array_unshift( $tblCourseAll, new TblCourse( '' ) );
        //        $tblChildRankAll = Management::serviceStudent()->entityChildRankAll();
        //        array_unshift( $tblChildRankAll, new TblChildRank( '' ) );

        if (empty( $Id )) {
            $Stage->setContent(new Warning('Die Daten konnten nicht abgerufen werden'));
        } else {
            $tblItem = Item::useService()->getItemById($Id);
            if (empty( $tblItem )) {
                $Stage->setContent(new Warning('Der Artikel konnte nicht abgerufen werden'));
            } else {

                $Global = $this->getGlobal();
                if (!isset( $Global->POST['Item'] )) {
                    $Global->POST['Item']['Name'] = $tblItem->getName();
                    $Global->POST['Item']['Description'] = $tblItem->getDescription();
                    $Global->POST['Item']['Price'] = str_replace('.', ',', $tblItem->getPrice());
                    $Global->POST['Item']['CostUnit'] = $tblItem->getCostUnit();
                    //                    if ( $tblItem->getServiceManagementCourse() ) {
                    //                        $Global->POST['Item']['Course'] = $tblItem->getServiceManagementCourse()->getId();
                    //                    }
                    //                    if ( $tblItem->getServiceManagementStudentChildRank() ) {
                    //                        $Global->POST['Item']['ChildRank'] = $tblItem->getServiceManagementStudentChildRank()->getId();
                    //                    }
                    $Global->savePost();
                }

                $Form = $this->formItem()
                    ->appendFormButton(new Primary('Hinzufügen'))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

                $Stage->setContent(Item::useService()->changeItem($Form, $tblItem, $Item));
            }
        }

        return $Stage;
    }
}
