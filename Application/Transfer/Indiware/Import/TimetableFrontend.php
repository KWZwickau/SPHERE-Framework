<?php
namespace SPHERE\Application\Transfer\Indiware\Import;

use MOC\V\Component\Document\Exception\DocumentTypeException as DocumentTypeException;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable as TimetableClassregister;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Clock;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Warning as WarningText;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class TimetableFrontend
 * @package SPHERE\Application\Transfer\Indiware\Import
 */
class TimetableFrontend extends Extension implements IFrontendInterface
{

    public function frontendTimetableDashboard()
    {

        $Stage = new Stage('Stundenplan', 'Übersicht');
        $Stage->addButton(new Standard('Zurück', '/Transfer/Indiware/Import', new ChevronLeft()));
        $Stage->addButton(new Standard('Import Stundenplan', '/Transfer/Indiware/Import/Timetable/Prepare', new Upload()));
        $tblTimetableList = TimetableClassregister::useService()->getTimetableAll();
        $TableContent = array();
        if($tblTimetableList){
            foreach($tblTimetableList as $tblTimetable){
                $item = array();
                $item['Name'] = $tblTimetable->getName();
                $item['Description'] = $tblTimetable->getDescription();
                $item['DateFrom'] = $tblTimetable->getDateFrom();
                $item['DateTo'] = $tblTimetable->getDateTo();
                $item['Option'] =
                    new Standard('', '/Transfer/Indiware/Import/Timetable/Edit', new Edit(), array('TimetableId' => $tblTimetable->getId()), 'Bearbeiten') .
                    new Standard('', '/Transfer/Indiware/Import/Timetable/Remove', new Remove(), array('TimetableId' => $tblTimetable->getId()), 'Entfernen');
                array_push($TableContent, $item);
            }
        }

        $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
            new TableData($TableContent, null, array(
                'Name' => 'Name',
                'Description' => 'Beschreibung',
                'DateFrom' => 'Gültig ab',
                'DateTo' => 'Gültig bis',
                'Option' => '',
            ), array(
                'order' => array(
                    array('2', 'desc'),
                ),
                'columnDefs' => array(
                    array('width' => '70px', "targets" => -1),
                    array('type' => 'de_date', 'targets' => array(2, 3)),
                ),
            ))
        )))));
        return $Stage;
    }

    /**
     * @param null $File
     * @param array $Data
     * @return Stage
     * @throws DocumentTypeException
     */
    public function frontendTimetableImport($File = null, array $Data = array())
    {

        if(!isset($_POST['Data']['IsImport'])){
            $_POST['Data']['IsImport'] = '0';
        }
//        $_POST['Data']['Name'] = 'Test123';
//        $_POST['Data']['DateFrom'] = '01.03.2022';
//        $_POST['Data']['DateTo'] = '02.03.2022';

        $Stage = new Stage('Import', 'Stundenplan aus Indiware');
        $Stage->addButton(new Standard('Zurück', '/Transfer/Indiware/Import/Timetable', new ChevronLeft()));
        $Form = $this->formTimetable();
        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Info('Bitte stellen Sie sicher, dass das Startdatum innerhalb des gewünschten Schuljahres liegt', null, false, '5', '5')
                        , 6),
                        new LayoutColumn(
                            Timetable::useService()->readTimetableFromFile($Form, $File, $Data)
                        )
                    ))
                )
            )
        );
        return $Stage;
    }

    /**
     * @param bool   $IsUploadField
     * @param string $ButtonText
     *
     * @return Form
     */
    private function formTimetable($IsUploadField = true, $ButtonText = 'Hochladen')
    {

        $form = new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    (new TextField('Data[Name]', '', 'Name'))->setRequired()
                    , 4),
                new FormColumn(
                    new TextField('Data[Description]', '', 'Beschreibung')
                    , 8)
            ))
        )), new Primary($ButtonText),);

        if($IsUploadField){
            $form->appendGridGroup(new FormGroup(new FormRow(array(
                new FormColumn(array(
                    (new DatePicker('Data[DateFrom]', '', 'Gültig ab', new Clock()))->setRequired()
                ), 4),
                new FormColumn(array(
                    (new DatePicker('Data[DateTo]', '', 'Gültig bis', new Clock()))->setRequired()
                ), 4),
                new FormColumn(
                    (new FileUpload('File', 'Datei auswählen', 'Datei auswählen '.new WarningText(new Exclamation().' XML-Export (Gesamt)'), null,
                        array('showPreview' => false)))->setRequired()
                    , 4),
            ))));

            $form->appendGridGroup(new FormGroup(new FormRow(
                new FormColumn(new Listing(
                    array(
                        new RadioBox('Data[IsImport]', 'Test des Imports', '0'),
                        new RadioBox('Data[IsImport]', 'Importieren', '1')
                    )
                ), 4)
            )));
        } else {
            $form->appendGridGroup(new FormGroup(new FormRow(array(
                new FormColumn(array(
                    (new DatePicker('Data[DateFrom]', '', 'Gültig ab', new Clock()))->setRequired()
                ), 4),
                new FormColumn(array(
                    (new DatePicker('Data[DateTo]', '', 'Gültig bis', new Clock()))->setRequired()
                ), 4),
            ))));
        }

        return $form;
    }

    /**
     * @param File $File
     * @param array $Data
     *
     * @return Layout
     */
    public function frontendImportTimetable(File $File, array $Data = array())
    {

        $ImportRead = Timetable::useService()->getTimeTableImportFromFile($File);
        $WeekImport = Timetable::useService()->getWeekDataFromFile($File);
        $Service = Timetable::useService();
        $DateFrom = new \DateTime($Data['DateFrom']);
        $Service->getProductiveResult($ImportRead, $DateFrom);
        $WarningList = $Service->getWarningList();
        $ImportList = $Service->getUploadList();
//        $WarningList = Timetable::useService()->getProductiveResult($result);
//        $DoList = Timetable::useService()->getProductiveResult($result, false);
        if(count($ImportList) > 0){
            $ImportReady = new Success(count($ImportList).' Importierbare Stundenzuweisungen', null, false, 5, 5);
            if($Data['IsImport'] == '1'){

                Timetable::useService()->importTimetable($Data['Name'], $Data['Description'], new \DateTime($Data['DateFrom']), new \DateTime($Data['DateTo']),
                    $ImportList, $WeekImport);
                $ImportReady = new Success('Import durchgeführt'.new Container(count($ImportList).' Importierte Stundenzuweisungen'));
            }
        } else {
            $ImportReady = new Danger(count($ImportList).' Importierbare Stundenzuweisungen', null, false, 5, 5);
        }

        $LayoutColumnList = array();
        if(!empty($Service->getCountImport())){

            $LayoutColumnList[] = new LayoutColumn(new Warning(count($WarningList).' Fehlerhafte Einträge können nicht importiert werden', null, false, 5,5));

            $Count = $Service->getCountImport();
            if(isset($Count['Course'])){
                $PanelContent = array();
                foreach($Count['Course'] as $Division => $FoundList){
                    $PanelContent[] = $Division.' (x'.count($FoundList).')';
                }
                $LayoutColumnList[] = new LayoutColumn(new Panel('Klasse nicht zuweisbar', $PanelContent, Panel::PANEL_TYPE_WARNING), 4);
            } else {
                $LayoutColumnList[] = new LayoutColumn(new Panel('Klasse nicht zuweisbar', '', Panel::PANEL_TYPE_WARNING), 4);
            }
            if(isset($Count['Subject'])){
                $PanelContent = array();
                foreach($Count['Subject'] as $Subject => $FoundList){
                    $PanelContent[] = $Subject.' (x'.count($FoundList).')';
                }
                $LayoutColumnList[] = new LayoutColumn(new Panel('Fach nicht zuweisbar', $PanelContent, Panel::PANEL_TYPE_WARNING), 4);
            } else {
                $LayoutColumnList[] = new LayoutColumn(new Panel('Fach nicht zuweisbar', '', Panel::PANEL_TYPE_WARNING), 4);
            }
            if(isset($Count['Person'])){
                $PanelContent = array();
                foreach($Count['Person'] as $Person => $FoundList){
                    $PanelContent[] = $Person.' (x'.count($FoundList).')';
                }
                $LayoutColumnList[] = new LayoutColumn(new Panel('Lehrer nicht zuweisbar', $PanelContent, Panel::PANEL_TYPE_WARNING), 4);
            } else {
                $LayoutColumnList[] = new LayoutColumn(new Panel('Lehrer nicht zuweisbar', '', Panel::PANEL_TYPE_WARNING), 4);
            }
        } else {
            $LayoutColumnList[] = new LayoutColumn(new Success('Keine Fehlerhafte Einträge', null, false, 5,5));
        }

        return new Layout(new LayoutGroup(array(
            new LayoutRow(
                $LayoutColumnList
            ),
            new LayoutRow(
                new LayoutColumn($ImportReady)
            )
        )));
    }

    /**
     * @param array  $Data
     * @param string $TimetableId
     *
     * @return Stage
     * @throws DocumentTypeException
     */
    public function frontendEditTimetable(array $Data = array(), string $TimetableId = '')
    {

        $Stage = new Stage('Stundenplan', 'Bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Transfer/Indiware/Import/Timetable', new ChevronLeft()));

        $tblTimetable = TimetableClassregister::useService()->getTimetableById($TimetableId);
        if(!$tblTimetable){
            $Stage->setContent(new Danger('Stundenplan nicht vorhanden'));
            return $Stage;
        }
        $Global = $this->getGlobal();
        if(!isset($Global->POST['Data']['Name'])){
            $Global->POST['Data']['Name'] = $tblTimetable->getName();
            $Global->POST['Data']['Description'] = $tblTimetable->getDescription();
            $Global->POST['Data']['DateFrom'] = $tblTimetable->getDateFrom();
            $Global->POST['Data']['DateTo'] = $tblTimetable->getDateTo();
            $Global->savePost();
        }

        $form = $this->formTimetable(false, 'Speichern');

        $Stage->setContent(
            new Well(
                Timetable::useService()->editTimetable($form, $tblTimetable, $Data)
            )
        );

        //        $Stage->setContent(new Success('Stundenplan erfolgreich entfernt')
        //            .new Redirect('/Transfer/Untis/Import/Timetable', Redirect::TIMEOUT_SUCCESS));
        return $Stage;
    }

    /**
     * @param string $TimetableId
     * @param false  $isDelete
     *
     * @return Stage
     */
    public function frontendRemoveTimetable(string $TimetableId, $isDelete = false)
    {

        $Stage = new Stage('Stundenplan', 'entfernen');
        $Stage->addButton(new Standard('Zurück', '/Transfer/Indiware/Import/Timetable', new ChevronLeft()));
        $tblTimetable = TimetableClassregister::useService()->getTimetableById($TimetableId);
        if(!$tblTimetable){
            $Stage->setContent(new Danger('Stundenplan nicht vorhanden'));
            return $Stage;
        }
        if(!$isDelete){
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn('&nbsp;', 3),
                        new LayoutColumn(new Panel('Soll der Standenplan '.new Bold('"'.$tblTimetable->getName().'"').' wirklich gelöscht werden?', array(
                                new Layout(new LayoutGroup(new LayoutRow(array(
                                    new LayoutColumn('Gültig ab:', 2),
                                    new LayoutColumn($tblTimetable->getDateFrom(), 10),
                                )))),
                                new Layout(new LayoutGroup(new LayoutRow(array(
                                    new LayoutColumn('Gültig bis:', 2),
                                    new LayoutColumn($tblTimetable->getDateTo(), 10),
                                )))),
                            ), Panel::PANEL_TYPE_DANGER)
                            .new DangerLink('Ja', '/Transfer/Indiware/Import/Timetable/Remove', new Check(), array('TimetableId' => $TimetableId, 'isDelete' => true))
                            .new Standard('Nein', '/Transfer/Indiware/Import/Timetable', new Disable())
                            , 6),
                    )),
                )))
            );
        } else {
            TimetableClassregister::useService()->removeTimetable($tblTimetable);
            $Stage->setContent(new Success('Stundenplan erfolgreich entfernt')
                .new Redirect('/Transfer/Indiware/Import/Timetable', Redirect::TIMEOUT_SUCCESS));
            return $Stage;
        }
        return $Stage;
    }
}