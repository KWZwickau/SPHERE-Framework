<?php


namespace SPHERE\Application\Billing\Inventory\Import;


use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\Info as InfoIcon;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\Icon\Repository\Warning as WarningIcon;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Fakturierung', 'Import');

        $PanelImport[] = new PullClear('Grundimport für Fakturierung: '.
            new Center(new Standard('', '/Billing/Inventory/Import/Prepare', new Upload()
                , array(), 'Hochladen, danach kontrollieren')));

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel('Import Fakturierung', $PanelImport
                                , Panel::PANEL_TYPE_INFO)
                            , 4)
                    )
                )
            )
        );

        return $Stage;
    }

    public function frontendImportPrepare()
    {
        $Stage = new Stage('Fakturierung', 'Import');
        $Stage->setMessage('Importvorbereitung / Daten importieren');
        $Stage->addButton(new Standard('Zurück', '/Billing/Inventory/Import', new ChevronLeft()));

        $tblIndiwareImportLectureshipList = Import::useService()->getImportAll();

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            ($tblIndiwareImportLectureshipList ? new Warning(new WarningIcon().' Vorsicht vorhandene Importdaten werden entfernt!') : '')
                            , 6, array(LayoutColumn::GRID_OPTION_HIDDEN_SM)
                        )),
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            new Form(
                                new FormGroup(array(
                                    new FormRow(
                                        new FormColumn(
                                            new Panel('Import',
                                                array(
                                                    (new FileUpload('File', 'Datei auswählen', 'Datei auswählen '
                                                        .new ToolTip(new InfoIcon(), 'Import.xlsx')
                                                        , null, array('showPreview' => false)))->setRequired()
                                                ), Panel::PANEL_TYPE_INFO)
                                        )
                                    ),
                                )),
                                new Primary('Hochladen und Voransicht', new Upload()),
                                new Route(__NAMESPACE__.'/Upload')
                            )
                        ), 6)
                    )
                ), new Title('Grunddaten', 'importieren'))
            )
        );

        return $Stage;
    }

    /**
     * @param null|UploadedFile $File
     *
     * @return Stage|string
     */
    public function frontendUpload(UploadedFile $File = null)
    {

        $Stage = new Stage('Indiware', 'Daten importieren');
        $Stage->setMessage('Grunddaten importieren');

        if ($File && !$File->getError()
            && (strtolower($File->getClientOriginalExtension()) == 'xlsx')
        ){

            // remove existing import
            Import::useService()->destroyImport();

            // match File
            $Extension = strtolower($File->getClientOriginalExtension());

            $Payload = new FilePointer($Extension);
            $Payload->setFileContent(file_get_contents($File->getRealPath()));
            $Payload->saveFile();


            // Test
            $Control = new ImportControl($Payload->getRealPath());
            if (!$Control->getCompare()){
                $LayoutColumnList = array();
                $LayoutColumnList[] = new LayoutColumn(new Warning('Die Datei beinhaltet nicht alle benötigten Spalten'));
                $ColumnList = $Control->getDifferenceList();
                if (!empty($ColumnList)){
                    foreach ($ColumnList as $Value) {
                        $LayoutColumnList[] = new LayoutColumn(new Panel('Fehlende Spalte', $Value,
                            Panel::PANEL_TYPE_DANGER), 3);
                    }
                }

                $Stage->addButton(new Standard('Zurück', '/Billing/Inventory/Import/Prepare',
                    new ChevronLeft()));

                $Stage->setContent(
                    new Layout(
                        new LayoutGroup(array(
                            new LayoutRow(
                                $LayoutColumnList
                            )
                        ))
                    )
                );
                return $Stage;
            }

            // add import
            $Gateway = new ImportGateway($Payload->getRealPath(), $Control);

            $ImportList = $Gateway->getImportList();
            if ($ImportList){
                Import::useService()->createImportBulk($ImportList);
            }

            // view up to 5 divisions
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new TableData($Gateway->getResultList(), null,
                                    array(
                                        'Row' => 'Zeile',
                                        'FirstName' => 'Vorname',
                                        'LastName' => 'Nachname',
                                        'Birthday' => 'Geburtstag',
                                        'PersonFrontend' => 'Test',
                                        'Value' => 'Betrag',
                                        'PriceVariant' => 'Preis-Variante',
                                        'Item' => 'Beitragsart',
                                        'Reference' => 'Mandatsreferenz',
                                        'ReferenceDate' => 'M.Ref. Gültig ab',
                                        'PaymentFromDate' => 'Zahlung ab',
                                        'PaymentTillDate' => 'Zahlung bis',
                                        'DebtorFirstName' => 'Zahler Vorname',
                                        'DebtorLastName' => 'Zahler Nachname',
                                        'DebtorFrontend' => 'Test',
                                        'DebtorNumber' => 'Debitoren Nr.',
                                        'IBANControl' => 'IBAN Kontrolle',
                                        'BIC' => 'BIC',
                                        'Bank' => 'Bank',
                                    ),
                                    array(
                                        'order'      => array(array(0, 'desc')),
                                        'columnDefs' => array(
                                            array('type' => 'natural', 'targets' => 0),
                                        ),
                                        'responsive' => false,
                                        'pageLength' => -1,
                                    )
                                )
                            ),
                            new LayoutColumn(
                                new DangerLink('Abbrechen', '/Billing/Inventory/Import').
                                new Standard('Weiter', '/Billing/Inventory/Import/Do', new ChevronRight())
                            )
                        ))
                        , new Title('Validierung',
                        'Rote '.new Danger(new WarningIcon()).' Einträge wurden nicht für die Bearbeitung aufgenommen! '
                        .new ToolTip(new InfoIcon(), 'Werden Klassen nicht in der Schulsoftware gefunden, kann kein 
                        Lehrauftrag für diese erstellt werden!')))
                )
            );
        } else {
            return $Stage->setContent(new Warning('Ungültige Dateiendung!'))
                .new Redirect('/Billing/Inventory/Import', Redirect::TIMEOUT_ERROR);
        }

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendDoImport()
    {

        //ToDO Überarbeitung

        $Stage = new Stage('Import', 'Ergebnis');
        $Stage->addButton(new Standard('Zurück', '/Billing/Inventory/Import', new ChevronLeft(), array(),
            'Zurück zum Import'));

        $LayoutRowList = Import::useService()->importBillingData();
        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    $LayoutRowList
                )
            )
        );
        return $Stage;
    }
}