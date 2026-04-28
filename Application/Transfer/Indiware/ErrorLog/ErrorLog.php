<?php
namespace SPHERE\Application\Transfer\Indiware\ErrorLog;

use SPHERE\Application\Api\Transfer\Indiware\ApiIndiware;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetableReplacementLog;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblSetting;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Transfer\Education\Education;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImportMapping;
use SPHERE\Application\Transfer\Indiware\Import\Replacement\Replacement;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Headline;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Ruler;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Primary as PrimaryLink;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Log
 *
 * @package SPHERE\Application\Transfer\Indiware\ErrorLog
 */
class ErrorLog extends Extension implements IModuleInterface
{
    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Api Logfile'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendLogOverview'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/LocalJson', __CLASS__.'::frontendDoLocalJson'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/EditCode', __CLASS__.'::frontendEditCode'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Clean', __CLASS__.'::frontendCleanup'
        ));
    }

    /**
     */
    public static function useService()
    {
        return new Service();
    }

    /**
     */
    public static function useFrontend()
    {

    }

    /**
     * @return Layout|string
     */
    public static function getWelcome()
    {
        if(($ErrorLogList = Timetable::useService()->getTimeTableReplacementLogAll(true))){
            $ReplacementLog = current($ErrorLogList);
                $Date = $ReplacementLog->getEntityCreate()->format('d.m.Y').' um '.$ReplacementLog->getEntityCreate()->format('H:i:s');
                $Date = ' am '.new Bold($Date);

            return new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                new Info(new Center('Übertragung aus Indiware '.$Date.' kontrollieren '. new PrimaryLink('Anzeigen', '/Transfer/Indiware/ErrorLog', new EyeOpen())))
            ))));
        }
        return '';
    }

    /**
     * @return Stage
     */
    public function frontendLogOverview(): Stage
    {
        $Stage = new Stage('Indiware', 'API-Errorlog');
        $Code = '';
        $MandantAcronym = Account::useService()->getMandantAcronym();
        if(($tblAccount = Account::useService()->getAccountByUsername($MandantAcronym.'-Indiware'))){
            if(($tblSetting = Account::useService()->getSettingByAccount($tblAccount, TblSetting::ATTR_INDIWARE_CODE))){
                $Code = $tblSetting->getValue();
            }
        }
        // Lokaler Button wird nicht freigegeben
        $ButtonString = '';
        // Route für Adminansicht
        $ButtonString .= new Standard('Json "Lokaler Test"', __NAMESPACE__.'/LocalJson', new Download());
        // Route für Adminansicht verwendet
        $ButtonString .= (new Standard('Letzte JSON anzeigen', __NAMESPACE__.'/LocalJson', new EyeOpen()))->ajaxPipelineOnClick(
            ApiIndiware::pipelineShowLastJSONContent()
        );
        $ButtonString .= new DangerLink('Logfile zurücksetzen', __NAMESPACE__.'/Clean', new Remove());
        if($Code){
            // anzeige nur bei vorhandenem Code
            $ReceiverURL = ApiIndiware::receiverContent((new Standard('URL anzeigen', '/Api/Transfer/Indiware/ApiIndiware', new EyeOpen()))
                ->ajaxPipelineOnClick(ApiIndiware::pipelineShowUrl()),'HideButton');
            $ButtonString .= $ReceiverURL;
        } else {
            // gibt es nur für Admin und auch nur, wenn kein Code vergeben ist
            $ButtonString .= new Standard('Übertragungscode (Freischaltung)', __NAMESPACE__.'/EditCode', new Plus());
        }
        $tblReplacementLogAll = Timetable::useService()->getTimeTableReplacementLogAll(true);
        $Date = false;
        $TableContent = array();
        $ErrorCountArray = array();
        $isInformation = false;
        if($tblReplacementLogAll){
            if(count($tblReplacementLogAll) == 1
                && ($tblReplacementLog = current($tblReplacementLogAll))
                && $tblReplacementLog->getHour() == ''
            ){
                $isInformation = true;
            }
            array_walk($tblReplacementLogAll, function (&$tblReplacementLog) use (&$TableContent, &$Date, &$ErrorCountArray, $isInformation) {
                /** @var $tblReplacementLog TblTimetableReplacementLog */
                $item = array();
                if(!$Date){
                    $Date = $tblReplacementLog->getEntityCreate()->format('H:i:s d.m.Y');
                    $Date = new Bold($Date);
                }
                $item['Date'] = $tblReplacementLog->getDate();
//                $item['Day'] = $this->getDayString($tblReplacementLog->getDate());
                $item['Hour'] = $tblReplacementLog->getHour();
                $item['Room'] = $tblReplacementLog->getRoom();
                $item['Course'] = $tblReplacementLog->getCourse();
                $item['PersonAcronym'] = $tblReplacementLog->getPersonAcronym();
                $item['IsCanceled'] = ($tblReplacementLog->getIsCanceled() ? "Ausfall" : "" );
                $item['Subject'] = $tblReplacementLog->getSubject();
                $item['SubjectSubstitute'] = $tblReplacementLog->getSubjectSubstitute();
                $ErrorList = explode(';', $tblReplacementLog->getError());
                $item['Error'] = implode("<br/>", $ErrorList);

                // fill error & color
                $item['Course'] = $this->fillErrorCountCourse($ErrorCountArray, $item['Course'], $isInformation);
                $item['Subject'] = $this->fillErrorCountSubject($ErrorCountArray, $item['Subject'], $item['SubjectSubstitute'], false, $isInformation);
                $item['SubjectSubstitute'] = $this->fillErrorCountSubject($ErrorCountArray, $item['SubjectSubstitute'], $item['SubjectSubstitute'], true);
                $item['PersonAcronym'] = $this->fillErrorCountPerson($ErrorCountArray, $item['PersonAcronym']);
                $item['Date'] = $this->fillErrorCount($ErrorCountArray, 'Date', $item['Date']);
                $item['Hour'] = $this->fillErrorCount($ErrorCountArray, 'Hour', $item['Hour'], true);

                $TableContent[] = $item;
            });
        } else {
            $isInformation = true;
        }

        $ColumnSummary = $ColumnCourse = $ColumnSubject = $ColumnPerson = $ColumnExtra = '';
        if(!$isInformation){
            $ColumnSummary = new LayoutColumn(new Title('Zusammenfassung der nicht zuweisbaren Daten:'));
            $ColumnCourse = $this->getLogLayoutColumn($ErrorCountArray, 'Course');
            $ColumnSubject = $this->getLogLayoutColumn($ErrorCountArray, 'Subject');
            $ColumnPerson = $this->getLogLayoutColumn($ErrorCountArray, 'Person');
            $ColumnExtra = $this->getLogLayoutColumn($ErrorCountArray, 'Extra');
        }
        $ColumnDetail = $ColumnTable = '';
        if($Code){
            $ColumnDetail = new LayoutColumn(new Title('Detailansicht:'));
            $ColumnTable = new LayoutColumn(
                new TableData($TableContent, null, array(
                    'Date'              => 'Datum',
//                'Day'               => 'Tag',
                    'Course'            => 'Klasse',
                    'Hour'              => 'Stunde',
                    'Subject'           => 'Fach',
                    'SubjectSubstitute' => 'Vert. Fach',
                    'PersonAcronym'     => 'Lehrer Kürzel',
                    'Room'              => 'Raum',
                    'IsCanceled'        => 'Ausfall',
                    'Error'             => 'Error',
                ),
                    array(
                        'order' => array(
                            array(0, 'asc'),
                            array(1, 'asc'),
                            array(2, 'asc')
                        ),
                        'columnDefs' => array(
//                    array('type' => 'de_date', 'targets' => array(0, 1)),
//                    array('orderable' => false, 'width' => '1%', 'targets' => -1),
                        ),
                        'responsive' => false
                    )
                ),
            );
        }


        $ReceiverLastJSON = ApiIndiware::receiverContent('', 'ShowJSON');
        $ReceiverURL = ApiIndiware::receiverContent('', 'ShowURL');
        $Stage->setContent(
            new Layout(new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn($ButtonString),
                ),
                new LayoutRow(
                    new LayoutColumn(
                        ($Code
                            ? $ReceiverURL
                            .'<div style="height: 8px;"></div>'
                            .'Zeitpunkt Import: '.$Date
                            .$ReceiverLastJSON
                            : '<div style="height: 8px;"></div>'
                            .new Warning('Schnittstelle: Freischaltung erforderlich!'))
                    ),
                ),
                new LayoutRow(
                    $ColumnSummary
                ),
                new LayoutRow(array(
                    $ColumnCourse,
                    $ColumnSubject,
                    $ColumnPerson,
                    $ColumnExtra,
                )),
                new LayoutRow(
                    $ColumnDetail,
                ),
                new LayoutRow(
                    $ColumnTable
                )
            )))
        );

        return $Stage;
    }

    private function fillErrorCount(&$ErrorCountArray = array(), $Key = '', $Value = '', $IsNumeric = false)
    {
        if(!$Value){
            if(isset($ErrorCountArray['Extra'][$Key])){
                $ErrorCountArray['Extra'][$Key]++;
            } else {
                $ErrorCountArray['Extra'][$Key] = 1;
            }
            return new DangerText($Value);
        }
        if($IsNumeric){
            if(!is_numeric($Value)){
                return new DangerText($Value);
            }
        }

        return $Value;
    }

    private function fillErrorCountCourse(&$ErrorCountArray = array(), $Value = '', $isInformation = false)
    {

        $tblDivisionCourse = false;
        if(($tblYearList = Term::useService()->getYearByNow())){
            foreach($tblYearList as $tblYear){
                // Mapping Division
                if (!($tblDivisionCourse = Education::useService()->getImportMappingValueBy(TblImportMapping::TYPE_DIVISION_NAME_TO_DIVISION_COURSE_NAME, $Value, $tblYear))) {
                    // Mapping Course
                    if (!($tblDivisionCourse = Education::useService()->getImportMappingValueBy(TblImportMapping::TYPE_COURSE_NAME_TO_DIVISION_COURSE_NAME, $Value, $tblYear))) {
                        $tblDivisionCourse = Education::useService()->getDivisionCourseByDivisionNameAndYear($Value, $tblYear);
                    }
                }
                if($tblDivisionCourse){
                    break;
                }
            }
        }
        if(!$Value && !$isInformation){
            $Value = '[Leer]';
        }
        if(!$tblDivisionCourse){
            if(isset($ErrorCountArray['Course'][$Value])){
                $ErrorCountArray['Course'][$Value]++;
            } else {
                $ErrorCountArray['Course'][$Value] = 1;
            }
            return new DangerText($Value);
        }
        return $Value;
    }

    private function fillErrorCountSubject(&$ErrorCountArray = array(), $Value = '', $SubstituteSubject = false, $isSubstituteSubject = false, $isInformation = false)
    {


        // Mapping
        if (!($tblSubject = Education::useService()->getImportMappingValueBy(TblImportMapping::TYPE_SUBJECT_ACRONYM_TO_SUBJECT_ID, $Value))) {
            $tblSubject = Subject::useService()->getSubjectByAcronym($Value);
        }
        // nicht vorhandenes $Value auf Leer, ausnahme Vertretungsfach, dies ist bei einem Ausfall auch leer
        if(!$Value && !$isSubstituteSubject && !$isInformation){
            $Value = '[Leer]';
        }
        // Vertretungsfach lässt $Value leer, fällt damit aus der Übersicht raus
        if(!$tblSubject && $Value) {
            if(isset($ErrorCountArray['Subject'][$Value])) {
                $ErrorCountArray['Subject'][$Value]++;
            } else {
                $ErrorCountArray['Subject'][$Value] = 1;
            }
                return new DangerText($Value);
        }
        return $Value;
    }

    private function fillErrorCountPerson(&$ErrorCountArray = array(), $Value = '')
    {
        // Mapping
        if (!($tblPerson = Education::useService()->getImportMappingValueBy(TblImportMapping::TYPE_TEACHER_ACRONYM_TO_PERSON_ID, $Value))) {
            $tblPerson = Teacher::useService()->getTeacherByAcronym($Value);
        }

        if(!$tblPerson){
            if(isset($ErrorCountArray['Person'][$Value])){
                $ErrorCountArray['Person'][$Value]++;
            } else {
                $ErrorCountArray['Person'][$Value] = 1;
            }
            return new DangerText($Value);
        }
        return $Value;
    }

    private function getDayString($DateString = 'now')
    {
        $DateTime = new \DateTime($DateString);
        $Day = $DateTime->format('w');
        $DayList = array(0 => 'Sonntag',
            1 => 'Montag',
            2 => 'Dienstag',
            3 => 'Mittwoch',
            4 => 'Donnerstag',
            5 => 'Freitag',
            6 => 'Samstag');
        return $DayList[$Day];
    }

    /**
     * @param $ErrorCountArray
     * @param $Key
     *
     * @return LayoutColumn|string
     */
    private function getLogLayoutColumn($ErrorCountArray = array(), $Key = '')
    {
        $size = 3;
        if($Key == 'Course'){
            $Content = array();
            if(!empty($ErrorCountArray['Course'])){
                foreach($ErrorCountArray['Course'] as $Key => $Value){
                    $Content[] = $Value.' x '.$Key;
                }
            }
            if(!empty($Content)){
                return new LayoutColumn(new Panel('Klassen', $Content, Panel::PANEL_TYPE_WARNING), $size);
            }
            return '';
        } elseif($Key == 'Subject'){
            $Content = array();
            if(!empty($ErrorCountArray['Subject'])){
                foreach($ErrorCountArray['Subject'] as $Key => $Value){
                    $Content[] = $Value.' x '.$Key;
                }
            }
            if(!empty($Content)){
                return new LayoutColumn(new Panel('Fächer', $Content, Panel::PANEL_TYPE_WARNING), $size);
            }
            return '';
        } elseif($Key == 'Person'){
            $Content = array();
            if(!empty($ErrorCountArray['Person'])){
                foreach($ErrorCountArray['Person'] as $Key => $Value){
                    $Content[] = $Value.' x '.$Key;
                }
            }
            if(!empty($Content)){
                return new LayoutColumn(new Panel('Lehrer', $Content, Panel::PANEL_TYPE_WARNING), $size);
            }
            return '';
        } elseif($Key == 'Extra'){
            $Content = array();
            if(!empty($ErrorCountArray['Extra'])){
                foreach($ErrorCountArray['Extra'] as $Key => $Value){
                    $Content[] = $Value.' x '.$Key;
                }
            }
            if(!empty($Content)){
                return new LayoutColumn(new Panel('Zusätzliche Fehler', $Content, Panel::PANEL_TYPE_WARNING), $size);
            }
            return '';
        }
        return '';
    }

    public function frontendEditCode($Setting = null)
    {
        $Stage = new Stage('Indiware', 'Übertragungscode erstellen');
        $Stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));
        $Code = '';
        $MandantAcronym = Account::useService()->getMandantAcronym();
        if(($tblAccount = Account::useService()->getAccountByUsername($MandantAcronym.'-Indiware'))){
            if(($tblSetting = Account::useService()->getSettingByAccount($tblAccount, TblSetting::ATTR_INDIWARE_CODE))){
                $Code = $tblSetting->getValue();
            }
        }

        $rand = $this->createGUID();
        if($Code){
            $_POST['Setting']['Code'] = $Code;
        } else {
            $_POST['Setting']['Code'] = $rand;
        }

        $form = new Form(new FormGroup(array(new FormRow(array(
            new FormColumn(
                new Info('Zufällig erzeugter Code (GUID): '.$rand)
//                new TextField('Setting[Code]', '', 'Indiware-Code'),
            ),
            new FormColumn(
                new HiddenField('Setting[Code]', $rand)
            )
        )))));
        $form->appendFormButton(new Primary('Speichern', new Save()));
        $form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn('', 3),
                    new LayoutColumn(
                        $Code
                            ? $this->getStyledApiURL($Code)
                            : new Info(new Container('Code wird autmoatsch erzeugt.')
                            .new Container('Zur Aktivierung einfach speichern.')
                            .new Container('Parallel wird ein Account [Kürzel]-Indiware erzeugt der für das speichern der Daten verwendet wird.')
                        )
                        , 6),
                )),
                new LayoutRow(new LayoutColumn(
                    '&nbsp;'
                )),
                new LayoutRow(array(
                    new LayoutColumn('', 3),
                    new LayoutColumn(($Code ? '': new Well(ErrorLog::useService()->createCode($form, $Setting))), 6),
                )),
            ))));
        return $Stage;
    }

    /**
     * @param string $Code
     *
     * @return string
     */
    public function getStyledApiURL(string $Code = ''): string
    {

        if(!$Code){
            $MandantAcronym = Account::useService()->getMandantAcronym();
            if(($tblAccount = Account::useService()->getAccountByUsername($MandantAcronym.'-Indiware'))){
                if(($tblSetting = Account::useService()->getSettingByAccount($tblAccount, TblSetting::ATTR_INDIWARE_CODE))){
                    $Code = $tblSetting->getValue();
                }
            }
        }
        return new Headline('Adresse der Schnittstelle:').new Ruler().
            '<span style="font-size: 20px">'.'https://'.$this->getRequest()->getHost().'/RestApi/Public/Indiware/TimeTable?Savety='.$Code.'</span>';
    }

    public static function createGUID()
    {
        if (function_exists('com_create_guid') === true)
        {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    /**
     * @return Stage
     */
    public function frontendDoLocalJson()
    {

        // URL mit "Kürzel-rand(4)" und dieses in der DB hinterlegen (Accounts Settings)

        $Stage = new Stage('Json einspielen');
        $Stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));

        $Mandant = 'KG';
        if(!($tblMandant = Consumer::useService()->getConsumerByAcronym($Mandant))){
            return $Stage->setContent(new Danger('Mandant nicht gefunden'));
        }

//        // entfernen alter Log Daten
        Timetable::useService()->destroyTimetableReplacementLogBulk();
        // Test mit Lokalen Daten
        $Json = (new JsonReplacementTest())->getJson($Mandant); // .'Manual'
        Replacement::useService()->importJsonReplacement($Json, true);

//        Account::useService()->destroySession(null, session_id());
        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendCleanup()
    {

        $Stage = new Stage('Log', 'entfernen');
        // entfernen alter Log Daten
        Timetable::useService()->destroyTimetableReplacementLogBulk();
        $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(
            new LayoutColumn(
                new Success('Log wurde bereinigt.')
                .new Redirect('/Transfer/Indiware/ErrorLog', Redirect::TIMEOUT_SUCCESS) //
            )
        ))));
        return $Stage;
    }
}