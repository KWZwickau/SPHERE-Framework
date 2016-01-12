<?php

namespace SPHERE\Application\Billing\Inventory\Item;

use SPHERE\Application\Billing\Inventory\Commodity\Commodity;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblSiblingRank;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
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
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
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
     * @return Stage
     */
    public function frontendItemStatus($Item = null)
    {

        $Stage = new Stage();
        $Stage->setTitle('Artikel');
        $Stage->setDescription('Übersicht');
//        $Stage->setMessage(
//            'Zeigt alle verfügbaren Artikel an. <br>');
//            Artikel sind Preise für erbrachte Dienste, die Abhängigkeiten zugewiesen bekommen können. <br />
//            Somit werden bei Rechnungen nur die Artikel berechnet, <br />
//            die <b>keine</b> oder die <b>zutreffenden</b> Abhängigkeiten für die einzelne Person besitzen.');
//        $Stage->addButton(
//            new Standard('Artikel anlegen', '/Billing/Inventory/Item/Create', new Plus())
//        );

        $tblItemAll = Item::useService()->getItemAll();

        $TableContent = array();
        if (!empty( $tblItemAll )) {
            array_walk($tblItemAll, function (TblItem $tblItem) use (&$TableContent) {

                $Temp['Type'] = '';
                $Temp['Rank'] = '';
                $tblCourse = $tblItem->getServiceStudentType();
                if ($tblCourse) {
                    $Temp['Type'] = $tblCourse->getName();
                }
                $tblRank = $tblItem->getServiceStudentChildRank();
                if ($tblRank) {
                    $Temp['Rank'] = $tblRank->getName();
                }

                $Temp['PriceString'] = $tblItem->getPriceString();
                if (Commodity::useService()->getCommodityItemAllByItem($tblItem)) {
                    $Temp['Option'] =
                        (new Standard('Bearbeiten', '/Billing/Inventory/Item/Change',
                            new Pencil(), array(
                                'Id' => $tblItem->getId()
                            )))->__toString().
                        (new Standard('FIBU-Konten auswählen', '/Billing/Inventory/Commodity/Item/Account/Select',
                            new Listing(), array(
                                'Id' => $tblItem->getId()
                            )))->__toString();
                } else {
                    $Temp['Option'] =
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
                $Temp['Name'] = $tblItem->getName();
                $Temp['Description'] = $tblItem->getDescription();
                array_push($TableContent, $Temp);
            });

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
                                    'PriceString' => 'Preis',
                                    'Type'        => 'Schulart',
                                    'Rank'        => 'Geschwisterkind',
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

    /**
     * @return Form
     */
    public function formItem()
    {

        $tblSchoolTypeAll = Type::useService()->getTypeAll();
        $tblSchoolTypeAll[] = new TblType('');
        $tblChildRankAll = Relationship::useService()->getSiblingRankAll();
        $tblChildRankAll[] = new TblSiblingRank('');

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
                new FormRow(array(
                    new FormColumn(
                        new SelectBox('Item[Course]', 'Bedingung Bildungsgang',
                            array(
                                'Name' => $tblSchoolTypeAll
                            ))
                        , 6),
                    new FormColumn(
                        new SelectBox('Item[ChildRank]', 'Bedingung Kind-Reihenfolge',
                            array(
                                'Name' => $tblChildRankAll
                            ))
                        , 6)
                ))
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
                                .new Redirect('/Billing/Inventory/Item', Redirect::TIMEOUT_SUCCESS)
                                : new Danger('Der Artikel konnte nicht gelöscht werden')
                                .new Redirect('/Billing/Inventory/Item', Redirect::TIMEOUT_ERROR)
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
    public function frontendItemChange($Id, $Item)
    {

        $Stage = new Stage();
        $Stage->setTitle('Artikel');
        $Stage->setDescription('Bearbeiten');
//        $Stage->setMessage(
//            '<b>Hinweis:</b> <br>
//            Ist ein Bildungsgang unter der '.new Italic('Bedingung Bildungsgang').' ausgewählt, wird der Artikel nur für
//            Personen (Schüler) berechnet welche diesem Bildungsgang angehören. <br>
//            Ist eine Kind-Reihenfolge unter der '.new Italic('Bedingung Kind-Reihenfolge').' ausgewählt, wird der Artikel nur für
//            Personen (Schüler) berechnet welche dieser Kind-Reihenfolge entsprechen. <br>
//            Beide Bedingungen können einzeln ausgewählt werden, bei der Wahl beider Bedingungen werden diese'
//            .new Bold('Und').' verknüpft.');
        $Stage->addButton(new Standard('Zurück', '/Billing/Inventory/Item',
            new ChevronLeft()
        ));

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
                    if ($tblItem->getServiceStudentType()) {
                        $Global->POST['Item']['Course'] = $tblItem->getServiceStudentType()->getId();
                    }
                    if ($tblItem->getServiceStudentChildRank()) {
                        $Global->POST['Item']['ChildRank'] = $tblItem->getServiceStudentChildRank()->getId();
                    }
                    $Global->savePost();
                }

                $PanelValue = array();

                $PanelValue[0] = $tblItem->getName();
                $PanelValue[1] = Item::useService()->formatPrice($tblItem->getPrice());
                $PanelValue[2] = $tblItem->getCostUnit();
                $PanelValue[3] = $tblItem->getDescription();
                if ($tblItem->getServiceStudentType()) {
                    $PanelValue[4] = $tblItem->getServiceStudentType()->getName();
                } else {
                    $PanelValue[4] = 'Nicht ausgewählt';
                }
                if ($tblItem->getServiceStudentChildRank()) {
                    $PanelValue[5] = $tblItem->getServiceStudentChildRank()->getName();
                } else {
                    $PanelValue[5] = 'Nicht ausgewählt';
                }

                $PanelContent = new Layout(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel('Name', $PanelValue[0], Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Preis', $PanelValue[1], Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Kostenstelle', $PanelValue[2], Panel::PANEL_TYPE_INFO)
                                , 4),
                        )),
                        new LayoutRow(
                            new LayoutColumn(
                                new Panel('Beschreibung', $PanelValue[3], Panel::PANEL_TYPE_INFO)
                            )
                        ),
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel('Bildungsgang', $PanelValue[4], Panel::PANEL_TYPE_INFO)
                                , 6),
                            new LayoutColumn(
                                new Panel('Geschwisterkind', $PanelValue[5], Panel::PANEL_TYPE_INFO)
                                , 6),
                        )),
                    ))
                );


                $Form = $this->formItem()
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
        }

        return $Stage;
    }
}
