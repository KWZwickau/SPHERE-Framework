<?php
namespace SPHERE\Application\Transfer\Indiware\ErrorLog;

use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetableReplacementLog;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
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
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
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
        if(($ErrorLogList = Timetable::useService()->getTimeTableReplacementLogAll())){
            $ReplacementLog = current($ErrorLogList);
                $Date = $ReplacementLog->getEntityCreate()->format('d.m.Y H:i:s');
                $Date = ' am '.new Bold($Date);

            return new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                new Warning(new Center('Bei der Übertragung vom Vertretungsplan aus Indiware '.$Date.' sind ('.count($ErrorLogList).') Fehler aufgetreten.<br/>'.
                    new \SPHERE\Common\Frontend\Link\Repository\Warning('Anzeigen', '/Transfer/Indiware/ErrorLog', new EyeOpen())))
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
        $Stage->addButton(new Standard('Json "Lokaler Test"', __NAMESPACE__.'/LocalJson', new Download()));
        $Stage->addButton(new Standard('Einstellung Übertragungscode', __NAMESPACE__.'/EditCode', new Plus()));
        $Stage->addButton(new DangerLink('Entfernen', __NAMESPACE__.'/Clean', new Remove()));
        $ReplacementLogAll = Timetable::useService()->getTimeTableReplacementLogAll();
        $Date = false;
        $TableContent = array();
        $ErrorCountArray = array();
        if($ReplacementLogAll){
            array_walk($ReplacementLogAll, function (&$ReplacementLog) use (&$TableContent, &$Date, &$ErrorCountArray) {
                /** @var $ReplacementLog TblTimetableReplacementLog */
                $item = array();
                if(!$Date){
                    $Date = $ReplacementLog->getEntityCreate()->format('d.m.Y');
                    $Date = new Bold($Date);
                }
                $item['Date'] = $ReplacementLog->getDate();
                $item['Hour'] = $ReplacementLog->getHour();
                $item['Room'] = $ReplacementLog->getRoom();
                $item['Course'] = $ReplacementLog->getCourse();
                $item['PersonAcronym'] = $ReplacementLog->getPersonAcronym();
                $item['IsCanceled'] = ($ReplacementLog->getIsCanceled() ? "Ausfall" : "" );
                $item['Subject'] = $ReplacementLog->getSubject();
                $item['SubjectSubstitute'] = $ReplacementLog->getSubjectSubstitute();
                $ErrorList = explode(';', $ReplacementLog->getError());
                $item['Error'] = implode("<br/>", $ErrorList);
                $TableContent[] = $item;


                $this->fillErrorCountCourse($ErrorCountArray, $item['Course']);
                $this->fillErrorCountSubject($ErrorCountArray, $item['Subject']);
                $this->fillErrorCountSubject($ErrorCountArray, $item['SubjectSubstitute']);
                $this->fillErrorCountPerson($ErrorCountArray, $item['PersonAcronym']);
                $this->fillErrorCount($ErrorCountArray, 'Date', $item['Date']);
                $this->fillErrorCount($ErrorCountArray, 'Hour', $item['Hour']);
            });
        }

        $PanelCourse = $this->getLogPanel($ErrorCountArray, 'Course');
        $PanelSubject = $this->getLogPanel($ErrorCountArray, 'Subject');
        $PanelPerson = $this->getLogPanel($ErrorCountArray, 'Person');
        $PanelExtra = $this->getLogPanel($ErrorCountArray, 'Extra');

        $Stage->setContent(
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn($PanelCourse, 3),
                new LayoutColumn($PanelSubject, 3),
                new LayoutColumn($PanelPerson, 3),
                new LayoutColumn($PanelExtra, 3),
                new LayoutColumn(
                    ($Code
                        ? new Headline('Schnittstelle: '.$this->getRequest()->getHost().'/RestApi/Public/Indiware/TimeTableReplacement?Savety='.$MandantAcronym.'-'.$Code)
                        : new Warning('Schnittstelle: Freischaltung erforderlich!'))
                ),
                new LayoutColumn(new Title('Zeitpunkt des letzten fehlerhaften Importes: '.($Date?: 'Keine Fehler vorhanden'))),
                new LayoutColumn(
                    new TableData($TableContent, null, array(
                        'Date' => 'Datum',
                        'Course' => 'Klasse',
                        'Hour' => 'Stunde',
                        'Subject' => 'Fach',
                        'SubjectSubstitute' => 'Vertretungs Fach',
                        'PersonAcronym' => 'Lehrer Kürzel',
                        'Room' => 'Raum',
                        'IsCanceled' => 'Ausfall',
                        'Error' => 'Error',
                        ),
                        array(
                            'order' => array(
                                array(0, 'asc'),
                                array(1, 'asc'),
                                array(2, 'asc')
                            ),
                            'columnDefs' => array(
        //                            array('type' => 'de_date', 'targets' => array(0, 1)),
        //                            array('orderable' => false, 'width' => '1%', 'targets' => -1),
                            ),
                            'responsive' => false
                        )
                    ),
                )
            ))))
        );

        return $Stage;
    }

    private function fillErrorCount(&$ErrorCountArray = array(), $Key = '', $Value = '')
    {
        if(!$Value){
            if(isset($ErrorCountArray['Extra'][$Key])){
                $ErrorCountArray['Extra'][$Key]++;
            } else {
                $ErrorCountArray['Extra'][$Key] = 1;
            }
        }
    }

    private function fillErrorCountCourse(&$ErrorCountArray = array(), $Value = '')
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
        if(!$Value){
            $Value = 'Leer';
        }
        if(!$tblDivisionCourse){
            if(isset($ErrorCountArray['Course'][$Value])){
                $ErrorCountArray['Course'][$Value]++;
            } else {
                $ErrorCountArray['Course'][$Value] = 1;
            }
        }
    }

    private function fillErrorCountSubject(&$ErrorCountArray = array(), $Value = '')
    {


        // Mapping
        if (!($tblSubject = Education::useService()->getImportMappingValueBy(TblImportMapping::TYPE_SUBJECT_ACRONYM_TO_SUBJECT_ID, $Value))) {
            $tblSubject = Subject::useService()->getSubjectByAcronym($Value);
        }
        if(!$Value){
            $Value = 'NA';
        }

        if(!$tblSubject){
            if(isset($ErrorCountArray['Subject'][$Value])){
                $ErrorCountArray['Subject'][$Value]++;
            } else {
                $ErrorCountArray['Subject'][$Value] = 1;
            }
        }
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
        }
    }

    public function getLogPanel($ErrorCountArray = array(), $Key = '')
    {

        if($Key == 'Course'){
            $Content = array();
            if(!empty($ErrorCountArray['Course'])){
                foreach($ErrorCountArray['Course'] as $Key => $Value){
                    $Content[] = $Value.' x '.$Key;
                }
            }
            if(!empty($Content)){
                return new Panel('Klassen', $Content, Panel::PANEL_TYPE_WARNING);
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
                return new Panel('Fächer', $Content, Panel::PANEL_TYPE_WARNING);
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
                return new Panel('Lehrer', $Content, Panel::PANEL_TYPE_WARNING);
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
                return new Panel('Zusätzliche Fehler', $Content, Panel::PANEL_TYPE_WARNING);
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

        $rand = rand(10000, 99999);
        if($Code){
            $_POST['Setting']['Code'] = $Code;
        } else {
            $_POST['Setting']['Code'] = $rand;
        }

        $form = new Form(new FormGroup(array(new FormRow(array(
            new FormColumn(
                new Info('Erzeuge ein Zufälligen Code (10.000 - 99.999): '.$rand)
//                new TextField('Setting[Code]', '', 'Indiware-Code'),
            ),
            new FormColumn(
                new HiddenField('Setting[Code]', $rand)
            )
        )))));
        $form->appendFormButton(new Primary('Speichern', new Save()));
        $form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    $Code
                    ? new Headline('Adresse der Schnittstelle:').new Ruler().
                        '<span style="font-size: 20px">'.$this->getRequest()->getHost().'/RestApi/Public/Indiware/TimeTableReplacement?Savety='.$MandantAcronym.'-'.$Code.'</span>'
                    : 'Zur aktivierung bitte erzeugten Code speichern'
                ),
                new LayoutColumn(
                    '&nbsp;'
                ),
                new LayoutColumn(($Code ? '': new Well(ErrorLog::useService()->createCode($form, $Setting))), 6),
            )))));
        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendDoLocalJson()
    {

        // URL mit "Kürzel-rand(4)" und dieses in der DB hinterlegen (Accounts Settings)

        $Stage = new Stage('Json einspielen');
        $Stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));

        $Mandant = 'EVSR';
        if(!($tblMandant = Consumer::useService()->getConsumerByAcronym($Mandant))){
            return $Stage->setContent(new Danger('Mandant nicht gefunden'));
        }

//        // entfernen alter Log Daten
        Timetable::useService()->destroyTimetableReplacementLogBulk();
        // Test mit Lokalen Daten
        $Json = (new JsonReplacementTest())->getJson($Mandant);
        Replacement::useService()->importJsonReplacement($Json);

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