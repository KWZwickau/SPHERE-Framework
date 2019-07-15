<?php


namespace SPHERE\Application\Billing\Inventory\Import;


use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\Download;
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
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Info;
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

        $Stage = new Stage('Datenimport', 'Fakturierung ');

        $PanelImport[] =
            new Info('Bitte verwenden Sie die Vorlage, um ihre Daten korrekt in das Tool einzuspielen: &nbsp;&nbsp;&nbsp;&nbsp;'
                .new External('Download Import-Vorlage','/Api/Billing/Inventory/DownloadTemplateInvoice',
                    new Download(), array(), false), null, false, 5, 3)
            .new PullClear('Grundimport für Fakturierung: '.
            new Center(new Standard('', '/Billing/Inventory/Import/Prepare', new Upload()
                , array(), 'Hochladen, danach kontrollieren')));

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel('Grunddaten', $PanelImport
                                , Panel::PANEL_TYPE_INFO)
                            , 6)
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

        $tblItemList = array();
        if(($tblItemAll = Item::useService()->getItemAll())){
            array_walk($tblItemAll, function(TblItem $tblItem) use (&$tblItemList){
                if(!Debtor::useService()->getDebtorSelectionFindTestByItem($tblItem)){
                    $tblItemList[] = $tblItem;
                }
            });
        }

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
                                                    (new SelectBox('Item', 'Beitragsart', array('{{ Name }}' => $tblItemList)))
                                                        ->setRequired()
                                                    .(new FileUpload('File', 'Datei auswählen', 'Datei auswählen '
                                                        .new ToolTip(new InfoIcon(), 'Fakturierung Import.xlsx')
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
     * @param string            $Item
     *
     * @return Stage|string
     */
    public function frontendUpload(UploadedFile $File = null, $Item = '')
    {

        $Stage = new Stage('Fakturierung Grunddaten', 'importieren');

        if ($File && !$File->getError()
            && (strtolower($File->getClientOriginalExtension()) == 'xlsx')
            && $Item
        ){
            if(($tblItem = Item::useService()->getItemById($Item))){
                $Item = $tblItem->getName();
            }

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
            $Gateway = new ImportGateway($Payload->getRealPath(), $Control, $Item);

            $ImportList = $Gateway->getImportList();
            if ($ImportList){
                Import::useService()->createImportBulk($ImportList);
            }

            if($Gateway->getErrorCount() > 0){
                $Stage->setMessage(new DangerText(new Bold($Gateway->getErrorCount())
                    .' Einträge (rot) verhindern den Import.<br/>
                Bitte überarbeiten Sie die Excel-Vorlage und/oder prüfen Sie, ob die Daten in der Personenverwaltung
                der Schulsoftware korrekt hinterlegt sind.'));
            }

            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new TableData($Gateway->getResultList(), null,
                                    array(
                                        'Row'                 => 'Zeile',
                                        'IsError'             => 'Fehler',
                                        'PersonFrontend'      => 'Beitragsverursacher',
                                        'ValueFrontend'       => 'Betrag',
                                        'ItemVariantFrontend' => 'Preis-Variante',
                                        'ItemControl'         => 'Beitragsart',
                                        'Reference'           => 'Mandatsrefere<nz',
                                        'ReferenceDate'       => 'M.Ref. Gültig ab',
                                        'PaymentFromDate'     => 'Zahlung ab',
                                        'PaymentTillDate'     => 'Zahlung bis',
                                        'DebtorFrontend'      => 'Beitragszahler',
                                        'DebtorNumberControl' => 'Debitoren Nr.',
                                        'IBANControl'         => 'IBAN Kontrolle',
                                        'BICControl'          => 'BIC',
                                        'Bank'                => 'Bank',
                                    ),
                                    array(
                                        'order'      => array(array(1, 'desc')),
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
            if($Item){
                return $Stage->setContent(new Warning('Ungültige Dateiendung!'))
                    .new Redirect('/Billing/Inventory/Import/Prepare', Redirect::TIMEOUT_ERROR);
            } else {
                if($File && !$File->getError()
                    && (strtolower($File->getClientOriginalExtension()) == 'xlsx')){
                    return $Stage->setContent(new Warning('Bitte füllen Sie die Beitragsart aus.'))
                        .new Redirect('/Billing/Inventory/Import/Prepare', Redirect::TIMEOUT_ERROR);
                } else {
                    return $Stage->setContent(new Warning('Bitte füllen Sie die Beitragsart aus.')
                        .new Warning('Ungültige Dateiendung!'))
                        .new Redirect('/Billing/Inventory/Import/Prepare', Redirect::TIMEOUT_ERROR);
                }
            }

        }

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendDoImport()
    {

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
        return $Stage;
    }
}