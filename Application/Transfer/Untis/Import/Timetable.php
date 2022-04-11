<?php
namespace SPHERE\Application\Transfer\Untis\Import;

use MOC\V\Component\Document\Exception\DocumentTypeException as DocumentTypeException;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable as TimetableClassregister;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Icon\Repository\Clock;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Warning as WarningText;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use Symfony\Component\HttpFoundation\File\File;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Warning;

/**
 * Class Timetable
 * @package SPHERE\Application\Transfer\Untis\Import
 */
class Timetable implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Timetable', __CLASS__.'::frontendTimetableDashboard'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Timetable/Prepare', __CLASS__.'::frontendTimetableImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Timetable/Import', __CLASS__.'::frontendImportTimetable'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Timetable/Remove', __CLASS__.'::frontendRemoveTimetable'
        ));
    }

    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

    /**
     * @return TimetableService
     */
    public static function useService()
    {
        return new TimetableService();
    }

    public function frontendTimetableDashboard()
    {

        $Stage = new Stage('Stundenplan', 'Übersicht');
        $Stage->addButton(new Standard('Zurück', '/Transfer/Untis/Import', new ChevronLeft()));
        $Stage->addButton(new Standard('Import Stundenplan', '/Transfer/Untis/Import/Timetable/Prepare', new Upload()));
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
                    // new Standard('', '#', new Edit(), array('tblTimetableId' => $tblTimetable->getId()), 'Erneuter Upload für diesen Stundenplan') .
                    new Standard('', '/Transfer/Untis/Import/Timetable/Remove', new Remove(), array('TimetableId' => $tblTimetable->getId()), 'Erneuter Upload für diesen Stundenplan');
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
                'columnDefs' => array(
                    array('width' => '70px', "targets" => -1),
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

        $Stage = new Stage('Import', 'Stundenplan aus Untis');
        $Stage->addButton(new Standard('Zurück', '/Transfer/Untis/Import/Timetable', new ChevronLeft()));
        $Form = $this->formTimetable();
        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
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
     * @return Form
     */
    private function formTimetable()
    {

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    (new TextField('Data[Name]', '', 'Name'))
                    , 4),
                new FormColumn(
                    new TextField('Data[Description]', '', 'Beschreibung')
                    , 8)
            )),
            new FormRow(array(
                new FormColumn(array(
                    (new DatePicker('Data[DateFrom]', '', 'Gültig ab', new Clock()))->setRequired()
                ), 4),
                new FormColumn(array(
                    (new DatePicker('Data[DateTo]', '', 'Gültig bis', new Clock()))->setRequired()
                ), 4),
                new FormColumn(
                    (new FileUpload('File', 'Datei auswählen', 'Datei auswählen '.new WarningText(new Exclamation().' GPU001.TXT'), null,
                        array('showPreview' => false)))->setRequired()
                    , 4),
            )),
            new FormRow(
                new FormColumn(new \SPHERE\Common\Frontend\Layout\Repository\Listing(
                    array(
                        new RadioBox('Data[IsImport]', 'Test des Imports', '0'),
                        new RadioBox('Data[IsImport]', 'Importieren', '1')
                    )
                ), 4)
            )
        )), new Primary('Hochladen'),
//            new Route(__CLASS__.'/Import')
        );
    }

    /**
     * @param File $File
     * @param array $Data
     *
     * @return Layout
     * @throws \Exception
     */
    public function frontendImportTimetable(File $File, array $Data = array())
    {

        $Payload01 = new FilePointer('csv');
        $Payload01->setFileContent(file_get_contents($File->getRealPath()));
        $Payload01->saveFile();

//        $WeekImport = Timetable::useService()->getWeekDataFromFile($File);
//        $WeekImport = array();

        $Gateway001 = new TimetableGPU001($Payload01->getRealPath(), $Data);
        $WarningCount = $Gateway001->getWarningCount();
        $ImportList = $Gateway001->getImportList();

        $ImportReady = new Success(count($ImportList).' Importierbare Stundenzuweisungen', null, false, 5, 5);
        if($Data['IsImport'] == '1'){
            if(!empty($ImportList)){
                Timetable::useService()->importTimetable($Data['Name'], $Data['Description'], new \DateTime($Data['DateFrom']), new \DateTime($Data['DateTo']),
                    $ImportList); // , $WeekImport
                $ImportReady = new Success('Import durchgeführt'.new Container(count($ImportList).' Importierte Stundenzuweisungen'));
            } else {
                $ImportReady = new Danger('Keine Daten zum Import vorhanden');
            }
        }

        $LayoutColumnList = array();
        if(!empty($ImportList)){

            $LayoutColumnList[] = new LayoutColumn(new \SPHERE\Common\Frontend\Message\Repository\Warning($WarningCount.' Fehlerhafte Einträge können nicht importiert werden', null, false, 5,5));

            $Count = $Gateway001->getCountImport();
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
     * @param string $TimetableId
     * @return Warning|string
     * @throws \Exception
     */
    public function frontendRemoveTimetable(string $TimetableId)
    {

        $Stage = new Stage('Stundenplan', 'entfernen');

        $tblTimetable = TimetableClassregister::useService()->getTimetableById($TimetableId);
        if($tblTimetable){
            TimetableClassregister::useService()->removeTimetable($tblTimetable);
            $Stage->setContent(new Success('Stundenplan erfolgreich entfernt')
                .new Redirect('/Transfer/Untis/Import/Timetable', Redirect::TIMEOUT_SUCCESS));
            return $Stage;
        }
        $Stage->setContent(new Warning('Stundenplan konnte nicht entfernt werden'));
        return $Stage;
    }
}