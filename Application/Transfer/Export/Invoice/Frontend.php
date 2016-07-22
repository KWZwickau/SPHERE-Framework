<?php

namespace SPHERE\Application\Transfer\Export\Invoice;

use SPHERE\Application\Api\Response;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Search;
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
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Transfer\Export\Invoice
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendExport()
    {

        $Stage = new Stage('Export', 'aller offenen Posten');

        $TableHeader = array('InvoiceNumber' => 'Rechnungsnummer',
                             'Debtor'        => 'Debitor',
                             'Name'          => 'Name',
                             'StudentNumber' => 'Schülernummer',
                             'Date'          => 'Fälligkeitsdatum',
        );
        $TableContent = Invoice::useService()->createInvoiceList($TableHeader);
        $Stage->addButton(new Standard('Auswahl Herunterladen', '\Billing\Bookkeeping\Export\Prepare', new Search()));

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null, $TableHeader)
                        )
                    )
                    , new Title(new ListingTable().' Übersicht der offenen Posten'))
            )
        );

        return $Stage;
    }

    /**
     * @param null $Prepare
     *
     * @return Stage
     */
    public function frontendPrepare($Prepare = null)
    {

        $Stage = new Stage('Export', 'nach Filterung');
        $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Export'));

        $form = $this->formPrepare($Prepare);
        $form->appendFormButton(new Primary('Speichern', new Save()));
//        $form->setConfirm('Die Zuweisung der Personen wurde noch nicht gespeichert.');


        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Title(new Search().' Filterung', 'der Rechnungen'),
                            new Well(Invoice::useService()->controlPrepare($form, $Prepare))
                        ))
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param $Prepare
     *
     * @return Form
     */
    public function formPrepare($Prepare)
    {

        $Global = $this->getGlobal();
        if ($Prepare == null) {
            $Now = new \DateTime('now');
            $Global->POST['Prepare']['DateFrom'] = $Now->format('d.m.Y');
            $Now->add(new \DateInterval('P1M'));
            $Global->POST['Prepare']['DateTo'] = $Now->format('d.m.Y');
            $Global->POST['Prepare']['Status'] = 1;
            $Global->savePost();
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                        new FormColumn(
                            new Panel('Zeitraum', array(new DatePicker('Prepare[DateFrom]', '', 'Fälligkeit ab:', new Time()),
                                    new DatePicker('Prepare[DateTo]', '', 'Fälligkeit bis:', new Time()))
                                , Panel::PANEL_TYPE_INFO)
                            , 3),
                        new FormColumn(array(
                            new Info('Hinzufügen von'),
                            new Panel('Personendaten', array(
                                    new CheckBox('Prepare[StudentNumber]', 'Schülernummer', 1),
                                    new CheckBox('Prepare[PersonFrom]', 'Leistungsbezieher', 1)
                                )
                                , Panel::PANEL_TYPE_INFO)
                        ), 3),
                        new FormColumn(array(
                            new Info('Hinzufügen von'),
                            new Panel('Bankdaten', array(
                                    new CheckBox('Prepare[BankName]', 'Name der Bank', 1),
                                    new CheckBox('Prepare[Owner]', 'Besitzer des Konto\'s', 1),
                                    new CheckBox('Prepare[IBAN]', 'IBAN', 1),
                                    new CheckBox('Prepare[BIC]', 'BIC', 1)
                                )
                                , Panel::PANEL_TYPE_INFO)
                        ), 3),
                        new FormColumn(array(
                            new Panel('Rechnungsstatus', array(
                                new RadioBox('Prepare[Status]', 'Offene Rechnungen', 1),
                                new RadioBox('Prepare[Status]', 'Bezahlte Rechnungen', 2),
                                new RadioBox('Prepare[Status]', 'Stornierte Rechnungen', 3)
                            ), Panel::PANEL_TYPE_INFO)
                        ), 3)
                    )
                )
            ))
        );
    }

    /**
     * @param $Filter
     *
     * @return Stage
     */
    public function frontendPrepareView($Filter)
    {

        $Filter = json_decode($Filter);

        $Stage = new Stage('Export Filterung', 'Vorschau');
        $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Export/Prepare'));
        if (!empty( $Filter->Error )) {
            return $Stage.new Warning('Übergabe nicht auswertbar');
        }
        $Filter = current($Filter->Data);

        $tblInvoiceList = Invoice::useService()->getInvoiceListByDate($Filter->DateFrom, $Filter->DateTo, $Filter->Status);

        $TableHeader = array();
        $TableHeader['Payer'] = 'Bezahler';
        if (isset( $Filter->PersonFrom ) && $Filter->PersonFrom != 0) {
            $TableHeader['PersonFrom'] = 'Leistungsbezieher';
        }
        if (isset( $Filter->StudentNumber ) && $Filter->StudentNumber != 0) {
            $TableHeader['StudentNumber'] = 'Schüler-Nr.';
        }
        $TableHeader['Date'] = 'Fälligkeitsdatum';
        if (isset( $Filter->IBAN ) && $Filter->IBAN != 0) {
            $TableHeader['IBAN'] = 'IBAN';
        }
        if (isset( $Filter->BIC ) && $Filter->BIC != 0) {
            $TableHeader['BIC'] = 'BIC';
        }
        $TableHeader['BillDate'] = 'Rechnungsdatum';
        $TableHeader['Reference'] = 'Mandats-Ref.';
        if (isset( $Filter->BankName ) && $Filter->BankName != 0) {
            $TableHeader['Bank'] = 'Name der Bank';
        }
        $TableHeader['Client'] = 'Mandant';
        $TableHeader['DebtorNumber'] = 'Debitoren-Nr.';
        if (isset( $Filter->Owner ) && $Filter->Owner != 0) {
            $TableHeader['Owner'] = 'Besitzer';
        }
        $TableHeader['InvoiceNumber'] = 'Buchungstext';
        $TableHeader['Item'] = 'Artikel';
        $TableHeader['ItemPrice'] = 'Einzelpreis';
        $TableHeader['Quantity'] = 'Anzahl';
        $TableHeader['Sum'] = 'Gesamtpreis';

        $TableContent = array();
        if ($tblInvoiceList) {
            $TableContent = Invoice::useService()->createInvoiceListByPrepare(
//                $TableHeader,
                $tblInvoiceList,
                $Filter->PersonFrom,
                $Filter->StudentNumber,
                $Filter->IBAN,
                $Filter->BIC,
                $Filter->BankName,
                $Filter->Owner
            );
            if (!empty( $TableContent )) {
                $Stage->addButton(new \SPHERE\Common\Frontend\Link\Repository\Primary('Herunterladen',
                    '/Api/Billing/Invoice/Download', new Download(),
                    array('Filter' => (new Response())->addData(array(
                        'DateFrom'      => $Filter->DateFrom,
                        'DateTo'        => $Filter->DateTo,
                        'BankName'      => $Filter->BankName,
                        'Owner'         => $Filter->Owner,
                        'IBAN'          => $Filter->IBAN,
                        'BIC'           => $Filter->BIC,
                        'StudentNumber' => $Filter->StudentNumber,
                        'PersonFrom'    => $Filter->PersonFrom,
                        'Status'        => $Filter->Status,
                    ))->__toString()
                    )
                ));
            }
        }
        $Status = ( $Filter->Status == 1 ? 'Offene Rechnungen' : ( $Filter->Status == 2 ? 'Bezahlte Rechnungen' : 'Stornierte Rechnungen' ) );

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ( empty( $TableContent ) ? new Warning('Keine Rechnung gefunden<br/>
                                                                    Datum "Fälligkeit" von: '.$Filter->DateFrom.'<br/>
                                                                    Datum "Fälligkeit" bis: '.$Filter->DateTo.'<br/>
                                                                    Status der Rechnungen: '.$Status)
                                : new TableData($TableContent, null, $TableHeader) )
                        )
                    )
                    , new Title(new ListingTable().' Übersicht'))
            )
        );
        return $Stage;
    }

}
