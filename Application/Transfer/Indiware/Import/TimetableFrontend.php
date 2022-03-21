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
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Clock;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
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
                $item['Option'] = new Standard('', '#', new Edit(), array('tblTimetableId' => $tblTimetable->getId()), 'Erneuter Upload für diesen Stundenplan')
                .new Standard('', '#', new Remove(), array('tblTimetableId' => $tblTimetable->getId()), 'Erneuter Upload für diesen Stundenplan');
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

        $_POST['Data']['IsImport'] = '0';

        $Stage = new Stage('Import', 'Stundenplan aus Indiware');
        $Stage->addButton(new Standard('Zurück', '/Transfer/Indiware/Import/Timetable', new ChevronLeft()));
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
                    (new TextField('Data[Name]', '', 'Name'))->setRequired()
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
                    (new FileUpload('File', 'Datei auswählen', 'Datei auswählen '.new WarningText(new Exclamation().' XML-Export (Gesamt)'), null,
                        array('showPreview' => false)))->setRequired()
                , 4),
            )),
            new FormRow(
                new FormColumn(new Listing(
                    array(
                        new RadioBox('Data[IsImport]', 'Test des Imports', '0'),
                        new RadioBox('Data[IsImport]', 'Importieren', '1')
                    )
                ), 4)
            )
        )), new Primary('Hochladen'));
    }

    /**
     * @param File $File
     * @param array $Data
     *
     * @return Layout
     */
    public function frontendImportTimetable(File $File, array $Data = array())
    {

        $result = Timetable::useService()->getArrayFromTimetableFile($File);
        $Service = Timetable::useService();
        $Service->getProductiveResult($result);
        $WarningList = $Service->getWarningList();
        $ImportList = $Service->getUploadList();
//        $WarningList = Timetable::useService()->getProductiveResult($result);
//        $DoList = Timetable::useService()->getProductiveResult($result, false);
        $ImportInfo = '';
        $ImportWarning = 'Stundenzuweisungen, die nicht importiert werden können';
        $ImportReady = 'Importierbare Stundenzuweisungen';
        if($Data['IsImport'] == '1'){

            Timetable::useService()->importTimetable($Data['Name'], $Data['Description'], new \DateTime($Data['DateFrom']), new \DateTime($Data['DateTo']), $ImportList);
            $ImportInfo = new Success('Import durchgeführt');
            $ImportWarning = 'Stundenzuweisungen, die nicht importiert werden konnten';
            $ImportReady = 'Importierte Stundenzuweisungen';
        }


        return new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn(
                new Title(new DangerText($ImportWarning))
                . (!empty($WarningList)
                    ? new TableData($WarningList, null, array(
                        'tag'       => 'Tag',
                        'stunde'    => 'Stunde',
                        'fach'      => 'Fach',
                        'SSWFach'   => 'SSW-Fach',
                        'klasse'    => 'Klasse',
                        'SSWKlasse' => 'SSW-Klasse',
                        'lehrer'    => 'Lehrer',
                        'SSWLehrer' => 'SSW-Lehrer',
                        'raum'      => 'Raum',
                    ), array(
                        'pageLength' => 10
                    ))
                    : new Success('Keine Fehler vorhanden'))
            ),
            new LayoutColumn(
                new Title($ImportReady)
                .$ImportInfo
                .(!empty($WarningList)
                    ?new TableData($ImportList, null, array(
                        'tag'       => 'Tag',
                        'stunde'    => 'Stunde',
                        'fach'      => 'Fach',
                        'SSWFach'   => 'SSW-Fach',
                        'klasse'    => 'Klasse',
                        'SSWKlasse' => 'SSW-Klasse',
                        'lehrer'    => 'Lehrer',
                        'SSWLehrer' => 'SSW-Lehrer',
                        'raum'      => 'Raum',
                        'success'   => '',
                    ))
                    : new Danger('Keine Daten für den Import vorhanden')
                )
            ),
        ))));
    }

    /**
     * @param string $TimeTableId
     * @return Warning|string
     * @throws \Exception
     */
    public function frontendRemoveTimetable(string $TimeTableId)
    {

        $tblTimeTable = TimetableClassregister::useService()->getTimetableById($TimeTableId);
        if($tblTimeTable){
            TimetableClassregister::useService()->removeTimeTable($tblTimeTable);
            return new Success('Stundenplan erfolgreich entfernt')
                .new Redirect('/Transfer/Indiware/Import/Timetable', Redirect::TIMEOUT_SUCCESS);
        }
        return new Warning('Stundenplan konnte nicht entfernt werden');
    }
}