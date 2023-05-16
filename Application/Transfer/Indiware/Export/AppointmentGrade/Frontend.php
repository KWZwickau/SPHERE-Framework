<?php

namespace SPHERE\Application\Transfer\Indiware\Export\AppointmentGrade;

use SPHERE\Application\Api\Transfer\Indiware\AppointmentGrade\ApiAppointmentGrade;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTask;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\Transfer\Indiware\Export\AppointmentGrade\Service\Entity\TblIndiwareStudentSubjectOrder;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Info as InfoIcon;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Success as SuccessIcon;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary as PrimaryLink;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Success as SuccessMessage;
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Transfer\Indiware\Export\AppointmentGrade
 */
class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @return Stage
     */
    public function frontendAppointmentGradePrepare(): Stage
    {
        $Stage = new Stage('Indiware', 'Datentransfer');
        $Stage->addButton(new Standard('Zurück', '/Transfer/Indiware/Export', new ChevronLeft()));
        $Stage->setMessage('Exportvorbereitung / Daten exportieren');

        $YearId = null;
        if(($tblYearList = Term::useService()->getYearByNow())){
            // Vorauswahl nur wenn das Jahr eindeutig ist
            if(count($tblYearList) == 1){
                $YearId = current($tblYearList)->getId();
            }
        }

        $SchoolType = array();
        $tblType = Type::useService()->getTypeByName(TblType::IDENT_GYMNASIUM);
        $PreselectId = $tblType->getId();
        $SchoolType[$tblType->getId()] = $tblType->getName();
        $tblType = Type::useService()->getTypeByName(TblType::IDENT_BERUFLICHES_GYMNASIUM);
        $SchoolType[$tblType->getId()] = $tblType->getName();

        if($YearId !== null){
            $Global = $this->getGlobal();
            $Global->POST['YearId'] = $YearId;
            $Global->POST['SchoolTypeId'] = $PreselectId;
            $Global->savePost();
        }

        // Vorladen der Selectbox mit Notenaufträgen des aktuellen Schuljahres
        $ReceiverAppointmentTask = ApiAppointmentGrade::receiverFormSelect((new ApiAppointmentGrade())->reloadTaskSelect($YearId), 'AppointmentTask');
        $ReceiverPeriod = ApiAppointmentGrade::receiverFormSelect((new ApiAppointmentGrade())->reloadPeriodSelect($PreselectId), 'Period');

        // Anzeige nur für alle aktuellen Jahre + das letzte Schuljahr
        $tblYearList = Term::useService()->getYearAllSinceYears(1);

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            new Form(
                                new FormGroup(array(
                                    new FormRow(
                                        new FormColumn(
                                            new Panel('Benötigte Informationen',
                                                array(
                                                    (new SelectBox('YearId',
                                                        'Auswahl Schuljahr '.new ToolTip(new InfoIcon(),
                                                            'Auswahl der Notenaufträge wird nach der Selektion geladen.'),
                                                        array('{{ Name }} {{ Description }}' => $tblYearList)
                                                    ))
                                                        ->ajaxPipelineOnChange(ApiAppointmentGrade::pipelineCreateTaskSelect($ReceiverAppointmentTask))
                                                        ->setRequired(),
                                                    $ReceiverAppointmentTask,
                                                    (new SelectBox('SchoolTypeId', 'Schulart', $SchoolType, null, false, null))
                                                        ->ajaxPipelineOnChange(ApiAppointmentGrade::pipelineCreatePeriodSelect($ReceiverPeriod))
                                                        ->setRequired(),
                                                    $ReceiverPeriod,
//                                                    (new SelectBox('Period',
//                                                        'Auswahl Schulhalbjahr '.new ToolTip(new InfoIcon(),
//                                                            'Indiware benötigt diese Information um den Export zuweisen zu können'),
//                                                        $PeriodList
//                                                    ))->setRequired(),
                                                    (new FileUpload('File', 'Datei auswählen', 'Datei auswählen '
                                                        .new ToolTip(new InfoIcon(), 'Schueler.csv'), null,
                                                        array('showPreview' => false)))->setRequired()
                                                    .new WarningMessage('Damit das System beim Export der Noten die korrekte 
                                                    Spaltenreihenfolge festlegen kann, ist es notwendig zuerst einen 
                                                    Export aus der Abiturverwaltung von Indiware einzulesen. Bitte verwenden 
                                                    Sie dafür einen kompletten Schüler-Export (alle Spalten) aus der 
                                                    Abiturverwaltung von Indiware mit '.new Bold('„Komma oder Semikolon“')
                                                    .' als Trennzeichen.'
                                                    .new Container('&nbsp;')
                                                    .new Container(new Bold(new InfoIcon().' Neu').' der Export benutzt
                                                    als Trennzeichen ein '. new Bold('„Semikolon“').'.'))
                                                    .new Danger(new Small(new Small('Pflichtfelder ')).'*')
                                                ), Panel::PANEL_TYPE_INFO)
                                        )
                                    ),
                                )),
                                new Primary('Hochladen und Voransicht', new Upload()),
                                new Route(__NAMESPACE__.'/Process')
                            )
                        ), 6)
                    )
                ), new Title('Noten', 'aus Stichtagsnotenaufträgen exportieren'))
            )
        );

        return $Stage;
    }

    /**
     * @param UploadedFile|null $File
     * @param int|null          $Period
     * @param int|null          $TaskId
     * @param int|null          $SchoolTypeId
     *
     * @return Stage|string
     */
    public function frontendAppointmentGradeUpload(
        UploadedFile $File = null,
        int $Period = null,
        int $TaskId = null,
        int $SchoolTypeId = null
    ): string {
        $Stage = new Stage('Indiware', 'Daten Export');
        $Stage->setMessage('Schüler-Fächer-Reihenfolge SEK II importieren');

        if ($TaskId == null || $TaskId == 0) {
            $Stage->setContent(
                new WarningMessage('Bitte wählen Sie einen Notenauftrag aus')
                .new Redirect(new Route(__NAMESPACE__.'/Prepare'), Redirect::TIMEOUT_ERROR, array(
                    'TaskId' => $TaskId,
                    'Period' => $Period
                ))
            );
            return $Stage;
        } else {
            $tblTask = Grade::useService()->getTaskById($TaskId);
            if (!$tblTask) {
                $Stage->setContent(
                    new WarningMessage('Notenauftrag wurde nicht gefunden')
                    .new Redirect(new Route(__NAMESPACE__.'/Prepare'), Redirect::TIMEOUT_ERROR, array(
                        'TaskId' => $TaskId,
                        'Period' => $Period
                    ))
                );
                return $Stage;
            }
        }

        if ($File === null || $Period == 0) {

            $Stage->setContent(
                ($Period == 0
                    ? new WarningMessage('Bitte geben Sie das Schulhalbjahr an.')
                    : new WarningMessage('Bitte geben sie die Datei an.'))
                .new Redirect(new Route(__NAMESPACE__.'/Prepare'), Redirect::TIMEOUT_ERROR, array(
                    'TaskId' => $TaskId,
                    'Period' => $Period
                ))
            );
            return $Stage;
        }

        if ($File && !$File->getError()
            && (strtolower($File->getClientOriginalExtension()) == 'txt'
                || strtolower($File->getClientOriginalExtension()) == 'csv')
        ) {

            //remove existing StudentSubjectOrder
            AppointmentGrade::useService()->destroyIndiwareStudentSubjectOrderAllBulk();

            // match File
            $Extension = (strtolower($File->getClientOriginalExtension()) == 'txt'
                ? 'csv'
                : strtolower($File->getClientOriginalExtension())
            );

            $Payload = new FilePointer($Extension);
            $Payload->setFileContent(file_get_contents($File->getRealPath()));
            $Payload->saveFile();

            // Test
            $Control = new AppointmentGradeControl($Payload->getRealPath());
            if (!$Control->getCompare()) {

                $LayoutColumnList = array();
                $LayoutColumnList[] = new LayoutColumn(new WarningMessage('Die Datei beinhaltet nicht alle benötigten Spalten'));
                $DifferenceList = $Control->getDifferenceList();
                if (!empty($DifferenceList)) {

                    foreach ($DifferenceList as $Value) {
                        $LayoutColumnList[] = new LayoutColumn(new Panel('Fehlende Spalte', $Value,
                            Panel::PANEL_TYPE_DANGER), 3);
                    }
                }

                $Stage->addButton(new Standard('Zurück', __NAMESPACE__.'/Prepare', new ChevronLeft(),
                    array(
                        'TaskId' => $TaskId,
                        'Period' => $Period,
                        'SchoolTypeId' => $SchoolTypeId
                    )));
                $Stage->setContent(
                    new Layout(
                        new LayoutGroup(
                            new LayoutRow(
                                $LayoutColumnList
                            )
                        )
                    )
                );
                return $Stage;
            }

            // add import
            $Gateway = new AppointmentGradeGateway($Payload->getRealPath(), $Control);

            $ImportList = $Gateway->getImportList();
            if(!empty($ImportList)){
                AppointmentGrade::useService()->createIndiwareStudentSubjectOrderBulk($ImportList, $Period, $tblTask);
            }

        } else {
            return $Stage->setContent(new WarningMessage('Ungültige Dateiendung!'))
                .new Redirect('/Transfer/Indiware/Export/AppointmentGrade/Prepare', Redirect::TIMEOUT_ERROR);
        }

        return $Stage->setContent(new SuccessMessage('Reihenfolge erfolgreich aufgenommen. Weiterleitung erfolgt.'))
            .new Redirect('/Transfer/Indiware/Export/AppointmentGrade', Redirect::TIMEOUT_SUCCESS, array('SchoolTypeId' => $SchoolTypeId));
    }

    /**
     * @param string|null $SchoolTypeId
     *
     * @return Stage
     */
    public function frontendExport(string $SchoolTypeId = null): Stage
    {

        $Stage = new Stage('Export', 'Stichtagsnoten eines Halbjahres');

        $IndiwareStudentSubjectOrderAll = AppointmentGrade::useService()->getIndiwareStudentSubjectOrderAll();
        $TableContentStudentOrder = array();
        $SelectPeriod = false;
        $tblTask = false;
        $tblPersonOrderList = array();
        $ShowDownload = false;
        if ($IndiwareStudentSubjectOrderAll) {

            $PersonFoundList = false;
            // get Selected Task
            $tblTask = $IndiwareStudentSubjectOrderAll[0]->getServiceTblTask();
            if ($tblTask) {
                $PersonFoundList = AppointmentGrade::useService()->getStudentExistInTaskList($tblTask->getId());
//                Debugger::screenDump($PersonFoundList);
            }

            array_walk($IndiwareStudentSubjectOrderAll,
                function (TblIndiwareStudentSubjectOrder $tblStudentSubjectOrder)
                use (
                    &$TableContentStudentOrder,
                    &$SelectPeriod,
                    &$tblPersonOrderList,
                    $PersonFoundList,
                    &$ShowDownload
                ) {
                    // get Selected Period
                    if (!$SelectPeriod) {
                        $SelectPeriod = $tblStudentSubjectOrder->getPeriod();
                    }
                    $Item['FirstName'] = $tblStudentSubjectOrder->getFirstName();
                    $Item['LastName'] = $tblStudentSubjectOrder->getLastName();
                    $Item['Birthday'] = $tblStudentSubjectOrder->getBirthday();
                    if ($tblStudentSubjectOrder->getServiceTblPerson()) {
                        $Item['PersonFound'] = new Success(new SuccessIcon().' Person gefunden');
                        $tblPersonOrderList[] = $tblStudentSubjectOrder->getServiceTblPerson();
                    } else {
                        $Item['PersonFound'] = new WarningMessage(new Danger(' Person nicht gefunden '
                            .new ToolTip(new InfoIcon(),
                                'Personen die in der Schulsoftware nicht gefunden werden, können nicht exportiert werden.')
                        ));
                    }
                    // course definition
                    $Item['PersonTest'] = new WarningMessage('Keine Noten vorhanden. Weitere Hinweise siehe Info-Symbol '.
                        new ToolTip(new InfoIcon(),
                            'Die Personen, die keine Noten erhalten haben, können nicht exportiert werden. Mögliche Ursache:
                             keine Stichtagsnote hinterlegt oder falscher Stichtagsnotenauftrag ausgewählt.'));
                    if (($tblPerson = $tblStudentSubjectOrder->getServiceTblPerson())
                        && $PersonFoundList
                        && isset($PersonFoundList[$tblPerson->getId()])) {
                        if ($PersonFoundList[$tblPerson->getId()]) {
                            $Item['PersonTest'] = new Success(new SuccessIcon().' Ok');
                            $ShowDownload = true;
                        }
                    }

                    array_push($TableContentStudentOrder, $Item);
                });
        }

        $TaskString = ' - ';
        /** @var TblTask|false $tblTask */
        if ($tblTask) {
            if ($ShowDownload) {
                $Stage->addButton(new PrimaryLink('Herunterladen',
                        'SPHERE\Application\Api\Transfer\Indiware\AppointmentGrade\Download',
                        new Download(),
                        array(
                            'Period' => $SelectPeriod,
                            'TaskId' => $tblTask->getId()
                        ), false)
                );
            }
            $TaskString = $tblTask->getName();
        }

        $MissingDownloadInfo = '';
        if (!$ShowDownload) {
            $MissingDownloadInfo = new WarningMessage('Download nicht möglich: Keine Personen im ausgewähltem Notenauftrag');
        }

        // switch start year
        $Grade = 11;
        if(($TblType = Type::useService()->getTypeById($SchoolTypeId))
            && $TblType->getName() == 'Berufliches Gymnasium'){
            $Grade = 12;
        }

        $PeriodString = '---';
        switch ($SelectPeriod) {
            case 1:
                $PeriodString = new Bold('Stufe '.$Grade.' 1. Halbjahr');
                break;
            case 2:
                $PeriodString = new Bold('Stufe '.$Grade.' 2. Halbjahr');
                break;
            case 3:
                $PeriodString = new Bold('Stufe '.++$Grade.' 1. Halbjahr');
                break;
            case 4:
                $PeriodString = new Bold('Stufe '.++$Grade.' 2. Halbjahr');
                break;
        }

        $Stage->setDescription('Stichtagsnoten "'.$TaskString.'" für '.$PeriodString);

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            $MissingDownloadInfo
                            .new Title(new ListingTable().' Personen aus Indiware')
                            .new TableData($TableContentStudentOrder, null,
                                array(
                                    'FirstName'   => 'Vorname',
                                    'LastName'    => 'Nachname',
                                    'Birthday'    => 'Geburtstag',
                                    'PersonFound' => 'Person in Schulsoftware',
                                    'PersonTest'  => 'Notensuche',
                                )
                                , array(
                                    'order' => array(
                                        array(3, 'asc')
                                    ,
                                        array(4, 'asc')
                                    ,
                                        array(1, 'asc')
                                    ),
                                )
                            )
                        ),
                        new LayoutColumn(
                            ($tblTask && $ShowDownload
                                ? new PrimaryLink('Herunterladen',
                                    'SPHERE\Application\Api\Transfer\Indiware\AppointmentGrade\Download',
                                    new Download(),
                                    array(
                                        'Period' => $SelectPeriod,
                                        'TaskId' => $tblTask->getId()
                                    ), false)
                                : '')
                        )
                    ))
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendMissExport(): Stage
    {

        $Stage = new Stage('Export CSV', 'Fehler');
        $Stage->setContent(
            new WarningMessage('Es ist keine Person aus der Importierten CSV-Datei im Stichtagsnotenauftrag enthalten')
        );
        return $Stage;
    }

}
