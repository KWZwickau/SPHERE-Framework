<?php

namespace SPHERE\Application\Billing\Inventory\Item;

use SPHERE\Application\Api\Billing\Inventory\ApiItem;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Conversation;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Money;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
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
    public function frontendItem()
    {

        $Stage = new Stage('Beitragsart', 'Übersicht');
        $tblItemAll = Item::useService()->getItemAll();

        $TableContent = array();
        if (!empty( $tblItemAll )) {
            array_walk($tblItemAll, function (TblItem $tblItem) use (&$TableContent) {

                $Item['Name'] = $tblItem->getName();
                $Item['Description'] = $tblItem->getDisplayDescription();
//                $Item['ItemType'] = $tblItem->getTblItemType()->getName();
                $CalculationContent = array();
                $Item['Condition'] = new Listing($CalculationContent);

                $Item['Option'] =
                    (new Standard('', ApiItem::getEndpoint(), new Pencil(), array(), 'Bearbeiten'))
                    ->ajaxPipelineOnClick(ApiItem::pipelineOpenModal(ApiItem::MODAL_SHOW_EDIT_ITEM))
                    .(new Standard('', ApiItem::getEndpoint(), new Plus(), array(), 'Varianten hinzufügen'))
                        ->ajaxPipelineOnClick(ApiItem::pipelineOpenModal(ApiItem::MODAL_SHOW_ADD_VARIANT))
                    .(new Standard('', ApiItem::getEndpoint(), new Disable(), array(), 'Löschen'))
                        ->ajaxPipelineOnClick(ApiItem::pipelineOpenModal(ApiItem::MODAL_SHOW_DELETE_ITEM));

                array_push($TableContent, $Item);
            });
        }

        $Stage->setContent(
            ApiItem::receiverModal()
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null,
                                array(
                                    'Name'        => 'Name',
                                    'Description' => 'Beschreibung',
//                                    'ItemType'    => 'Art',
                                    'Condition'   => 'Preis - Schulart - Geschwister',
                                    'Option'      => ''
                                )
                            )
                        )
                    ), new Title(new ListingTable().' Übersicht')
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
                                new TextField('Item[Value]', 'Preis', 'Standardpreis', new Money()),
                                new RadioBox('Item[ItemType]', 'Einzelleistung', 'Einzelleistung'),
                                new RadioBox('Item[ItemType]', 'Sammelleistung', 'Sammelleistung'),

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
}
