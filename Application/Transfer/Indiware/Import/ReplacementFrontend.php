<?php
namespace SPHERE\Application\Transfer\Indiware\Import;

use MOC\V\Component\Document\Exception\DocumentTypeException as DocumentTypeException;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable as TimetableClassregister;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Warning as WarningText;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class ReplacementFrontend
 * @package SPHERE\Application\Transfer\Indiware\Import
 */
class ReplacementFrontend extends Extension implements IFrontendInterface
{
    public function frontendReplacementDashboard()
    {

        $Stage = new Stage('Vertretungsplan', 'Übersicht');
        $Stage->setMessage('Übersicht aller Klassen mit abweichungen zum Stundenplan');
        $Stage->addButton(new Standard('Zurück', '/Transfer/Indiware/Import', new ChevronLeft()));
        $Stage->addButton(new Standard('Import Vertretungsplan', '/Transfer/Indiware/Import/Replacement/Prepare', new Upload()));

        $Date = new \DateTime();
        // 5 Tage Zukunft
        $DateTo = clone($Date);
        $DateTo->add(new \DateInterval('P5D'));
        // aktueller Tag Vergangenheit
        $DateFrom = $Date;
        $DateFrom->sub(new \DateInterval('P1D'));

        $TableContentTemp = array();
        if(($tblTimetableReplacementList = TimetableClassregister::useService()->getTimetableReplacementByDate($DateFrom, $DateTo))){
            foreach($tblTimetableReplacementList as $tblTimetableReplacement){
                $tblDivision = $tblTimetableReplacement->getServiceTblCourse();
                $TableContentTemp[$tblTimetableReplacement->getDate()][$tblDivision->getId()] = $tblDivision;
            }
        }
        $TableContent = array();
        if(!empty($TableContentTemp)){
            foreach($TableContentTemp as $Date => $DivisionList){
                $item = array();
                $item['Date'] = $Date;
                $DivisionList = $this->getSorter($DivisionList)->sortObjectBy('DisplayName');
                $DivList = array();
                /** @var TblDivisionCourse $Division */
                foreach($DivisionList as $Division){
                    $DivList[] = $Division->getName();
                }
                $item['Course'] = implode(', ', $DivList);
                array_push($TableContent, $item);
            }
        }

        $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
            new TableData($TableContent, null, array(
                'Date' => 'Datum',
                'Course' => 'Klassen/Kurse',
            ), array(
                'columnDefs' => array(
                    array('type' => 'de_date', 'targets' => 0),
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
    public function frontendReplacementPrepare($File = null, array $Data = array())
    {

        if(!isset($_POST['Data']['IsImport'])){
            $_POST['Data']['IsImport'] = '0';
        }

        $Stage = new Stage('Import', 'Vertretungsplan aus Indiware');
        $Stage->addButton(new Standard('Zurück', '/Transfer/Indiware/Import/Replacement', new ChevronLeft()));
        $Form = $this->formReplacement();
        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            Replacement::useService()->readReplacementFromFile($Form, $File, $Data)
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
    private function formReplacement()
    {

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    (new FileUpload('File', 'Datei auswählen', 'Datei auswählen '.new WarningText(new Exclamation().' XML-Export (Vertretungsplan)'), null,
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
        )), new Primary('Hochladen')
        );
    }

    /**
     * @param File $File
     * @param array $Data
     *
     * @return Layout
     */
    public function frontendImportReplacement(File $File, array $Data = array())
    {

        $Service = Replacement::useService();
        $ImportRead = $Service->getReplacementImportFromFile($File);
        if(is_string($ImportRead)){
            return new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(new Danger($ImportRead)))));
        }

        $Service->getReplacementResult($ImportRead);
        $WarningList = $Service->getWarningList();
        $DateList = $Service->getDateList();
        $CourseList = $Service->getCourseList();
        $UploadList = $Service->getUploadList();

        $ImportList = $Service->getCompareImportList($UploadList);

        if(count($ImportList) > 0){
            $ImportReady = new Success(count($ImportList).' Importierbare Stundenzuweisungen', null, false, 5, 5);
            if($Data['IsImport'] == '1'){
                // entfernen vorhandener Klassen die im Import enthalten sind.
                Replacement::useService()->removeExistingReplacementByDateListAndDivisionList($DateList, $CourseList);
                // import
                Replacement::useService()->importTimetableReplacementBulk($ImportList);
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
}