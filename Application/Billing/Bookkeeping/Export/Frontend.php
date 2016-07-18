<?php

namespace SPHERE\Application\Billing\Bookkeeping\Export;

use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\CommodityItem;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Search;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Icon\Repository\Time;
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
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Bookkeeping\Export
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendExport()
    {

        $Stage = new Stage('Export', 'aller offenen Posten');

        $TableHeader = array('Name'          => 'Name',
                             'StudentNumber' => 'Schülernummer',
                             'Date'          => 'Fälligkeitsdatum',
        );
        $TableContent = Export::useService()->createInvoiceList($TableHeader);
        if (!empty( $TableContent )) {
            $Stage->addButton(new \SPHERE\Common\Frontend\Link\Repository\Primary('Herunterladen',
                '/Api/Billing/Invoice/InvoiceAll/Download', new Download()));
            $Stage->addButton(new \SPHERE\Common\Frontend\Link\Repository\Primary('Datev',
                '/Api/Billing/Invoice/Datev/Download', new Download()));
            $Stage->addButton(new \SPHERE\Common\Frontend\Link\Repository\Primary('SFirm',
                '/Api/Billing/Invoice/Sfirm/Download', new Download()));
        }
        $Stage->addButton(new Standard('Filterung', '\Billing\Bookkeeping\Export\Filter', new Search()));

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null, $TableHeader)
                        )
                    )
                    , new Title(new ListingTable().' Übersicht'))
            )
        );

        return $Stage;
    }

    /**
     * @param null $Filter
     *
     * @return Stage
     */
    public function frontendFilter($Filter = null)
    {

        $Stage = new Stage('Export', 'nach Filterung');
        $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Export'));

        $form = $this->formFilter();
        $form->appendFormButton(new Primary('Speichern', new Save()));
        $form->setConfirm('Die Zuweisung der Personen wurde noch nicht gespeichert.');

        $Global = $this->getGlobal();
        if ($Filter == null) {
            $Now = new \DateTime('now');
            $Global->POST['Filter']['DateTo'] = $Now->format('d.m.Y');
            $Now->sub(new \DateInterval('P1M'));
            $Global->POST['Filter']['DateFrom'] = $Now->format('d.m.Y');
            $Global->savePost();
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Title(new Search().' Filterung', 'der Rechnungen'),
                            new Well(Export::useService()->controlFilter($form, $Filter))
                        ))
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    public function formFilter()
    {

        // get all Division by now (SelectBox)
        $DivisionList = array();
        $tblYearList = Term::useService()->getYearAllByDate(new \DateTime('now'));
        if ($tblYearList) {
            foreach ($tblYearList as $tblYear) {
                $tblDivisionYearList = Division::useService()->getDivisionByYear($tblYear);
                if ($tblDivisionYearList) {
                    foreach ($tblDivisionYearList as $tblDivisionYear) {
                        $DivisionList[] = $tblDivisionYear;
                    }
                }
            }
        }
        $tblGroupAll = Group::useService()->getGroupAll();
        $tblItemAll = Item::useService()->getItemAll();

        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Zeitraum', array(new DatePicker('Filter[DateFrom]', '', 'Fälligkeit ab:', new Time()),
                                new DatePicker('Filter[DateTo]', '', 'Fälligkeit bis:', new Time()))
                            , Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Personen auswahl', array(new SelectBox('Filter[Division]', 'Klasse', array('{{ DisplayName }}' => $DivisionList), new Select()),
                                new SelectBox('Filter[Group]', 'Gruppe', array('{{ Name }}' => $tblGroupAll), new Select()))
                            , Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Artikel auswahl', array(new SelectBox('Filter[Item]', 'Artikel', array('{{ Name }}' => $tblItemAll), new CommodityItem()))
                            , Panel::PANEL_TYPE_INFO)
                        , 4)
                ))
            )
        );
    }

    /**
     * @param      $DateFrom
     * @param null $DateTo
     * @param null $Division
     * @param null $Group
     * @param null $Item
     *
     * @return Stage
     */
    public function frontendFilterView($DateFrom, $DateTo = null, $Division = null, $Group = null, $Item = null)
    {

        $Stage = new Stage('Export Filterung', 'Vorschau');
        $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Export/Filter'));

        $tblInvoiceList = Export::useService()->getInvoiceListByDate($DateFrom, $DateTo);
        $tblDivision = ( $Division == null ? null : Division::useService()->getDivisionById($Division) );
        $tblGroup = ( $Group == null ? null : Group::useService()->getGroupById($Group) );
        $tblItem = ( $Item == null ? null : Item::useService()->getItemById($Item) );

        $TableHeader = array('Name'          => 'Name',
                             'StudentNumber' => 'Schülernummer',
                             'Date'          => 'Fälligkeitsdatum',
        );
        $TableContent = Export::useService()->createInvoiceListByInvoiceListAndDivision($TableHeader, $tblDivision, $tblGroup, $tblItem, $tblInvoiceList);
        if (!empty( $TableContent )) {
            $Stage->addButton(new \SPHERE\Common\Frontend\Link\Repository\Primary('Herunterladen',
                '/Api/Billing/Invoice/Select/Download', new Download(),
                array('DateFrom' => $DateFrom,
                      'DateTo'   => $DateTo,
                      'Division' => $Division,
                      'Group'    => $Group,
                      'Item'     => $Item)));
        }


        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ( empty( $TableContent ) ? new Warning('Keine Rechnung gefunden<br/>
                                                                    Datum "Fälligkeit" von: '.$DateFrom.'<br/>
                                                                    Datum "Fälligkeit" bis: '.$DateTo.'<br/>
                                                                    Klasse: '.( $tblDivision ? $tblDivision->getDisplayName() : null ).'<br/>
                                                                    Gruppe: '.( $tblGroup ? $tblGroup->getName() : null ).'<br/>
                                                                    Artikel: '.( $tblItem ? $tblItem->getName() : null ))
                                : new TableData($TableContent, null, $TableHeader) )
                        )
                    )
                    , new Title(new ListingTable().' Übersicht'))
            )
        );

        return $Stage;
    }
}
