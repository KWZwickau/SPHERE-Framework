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
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
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

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
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
                , new Title('Grunddaten', 'importieren'))
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

        if($File && !$File->getError()
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
            if(!$Control->getCompare()){
                $LayoutColumnList = array();
                $LayoutColumnList[] = new LayoutColumn(new Warning('Die Datei beinhaltet nicht alle benötigten Spalten'));
                $ColumnList = $Control->getDifferenceList();
                if(!empty($ColumnList)){
                    foreach($ColumnList as $Value) {
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
            if($ImportList){
                Import::useService()->createImportBulk($ImportList);
            }

            $Stage->setMessage(new DangerText(new Bold('Validierung '.$Gateway->getErrorCount())
                .' rote Einträge verhindern den Import, überarbeiten Sie die Excel bitte so,
                das alle Fehlermeldungen verschwinden oder Pflegen Sie die Eintsellungen in der Schulsoftware korrekt
                und starten Sie den Import erneut.'));

            // view up to 5 divisions
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new TableData($Gateway->getResultList(), null,
                                    array(
                                        'Row'                 => 'Zeile',
                                        'PersonFrontend'      => 'Beitragsverursacher',
                                        'ValueFrontend'       => 'Betrag',
                                        'ItemVariantFrontend' => 'Preis-Variante',
                                        'ItemControl'         => 'Beitragsart',
                                        'Reference'           => 'Mandatsrefere<nz',
                                        'ReferenceDate'       => 'M.Ref. Gültig ab',
                                        'PaymentFromDate'     => 'Zahlung ab',
                                        'PaymentTillDate'     => 'Zahlung bis',
                                        'DebtorFrontend'      => 'Beitragszahler',
                                        'DebtorNumber'        => 'Debitoren Nr.',
                                        'IBANControl'         => 'IBAN Kontrolle',
                                        'BICControl'          => 'BIC',
                                        'Bank'                => 'Bank',
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
                                new DangerLink('Abbrechen', '/Billing/Inventory/Import/Prepare').
                                ($Gateway->getErrorCount() == 0
                                    ? new Standard('Weiter', '/Billing/Inventory/Import/Do', new ChevronRight())
                                    : ''
                                )
                            )
                        ))
                    )
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

        $Stage = new Stage('Import', 'Prozess');
        $Stage->addButton(new Standard('Zurück', '/Billing/Inventory', new ChevronLeft(), array(),
            'Zurück zum Import'));
        Import::useService()->importBillingData();
        $Stage->setContent(new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Success('Import wurde erfolgreich durchgeführt.')
                    ),
                    new LayoutColumn(
                        new Redirect('/Billing/Inventory', Redirect::TIMEOUT_SUCCESS)
                    )
                ))
            )
        ));

//        $LayoutRowList = Import::useService()->importBillingData();
//        $Stage->setContent(
//            new Layout(
//                new LayoutGroup(
//                    $LayoutRowList
//                )
//            )
//        );
        return $Stage;
    }
}