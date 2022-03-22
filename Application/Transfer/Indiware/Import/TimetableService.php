<?php
namespace SPHERE\Application\Transfer\Indiware\Import;

use MOC\V\Component\Document\Component\Bridge\Repository\UniversalXml;
use MOC\V\Component\Document\Document;
use MOC\V\Component\Document\Exception\DocumentTypeException as DocumentTypeException;
use MOC\V\Component\Document\Vendor\UniversalXml\Source\Node;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable as TimetableClassRegister;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Success as SuccessIcon;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class TimetableService
{

    private $UploadList = array();
    private $WarningList = array();

    /**
     * @return array
     */
    public function getUploadList(): array
    {
        return $this->UploadList;
    }

    /**
     * @return array
     */
    public function getWarningList(): array
    {
        return $this->WarningList;
    }

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null $File
     * @param array $Data
     * @return Well|Layout|Danger
     * @throws DocumentTypeException
     */
    public function readTimetableFromFile(IFormInterface $Form = null, UploadedFile $File = null, array $Data = array())
    {

        /**
         * Skip to Frontend
         */
        if(empty($Data) && $File == null){
            return new Well($Form);
        }
        $IsError = false;
        if (null === $File) {
            $Form->setError('File', 'Wählen Sie eine Datei aus');
            $IsError = true;
        } else {
            $_POST['File'] = $File;
        }
        if(!isset($Data['Name']) || $Data['Name'] == ''){
            $Form->setError('Data[Name]', 'Für den Stundenplan wird ein Name benötigt');
            $IsError = true;
        }
        $DateFrom = '';
        if(isset($Data['DateFrom'])){
            $DateFrom = $Data['DateFrom'];
        }
        if($DateFrom == ''){
            $Form->setError('Data[DateFrom]', 'Ein Gültigkeitszeitraum wird benötigt');
            $IsError = true;
        }

        $DateTo = '';
        if(isset($Data['DateTo'])){
            $DateTo = $Data['DateTo'];
        }
        if($DateTo == ''){
            $Form->setError('Data[DateTo]', 'Ein Gültigkeitszeitraum wird benötigt');
            $IsError = true;
        }
        // Zeitraum sollte sich nicht mit anderen Zeiträumen der gleichen Klassen überschneiden

        // Datum zueinander kontrollieren
        if(!$IsError){
            $DateTimeFrom = new \DateTime($DateFrom);
            $DateTimeTo = new \DateTime($DateTo);
            if($DateTimeFrom > $DateTimeTo){
                $Form->setError('Data[DateFrom]', '"Gültig ab" muss vor "Gültig bis" sein');
                $Form->setError('Data[DateTo]', '"Gültig bis" muss nach "Gültig ab" sein');
                $IsError = true;
            }
            if(TimetableClassRegister::useService()->getTimetableByNameAndTime($Data['Name'], $DateTimeFrom, $DateTimeTo)){
                $Form->setError('Data[Name]', 'Für den Zeitraum ist der Name schon in Verwendung');
                $IsError = true;
            }
        }

        if($IsError){
            return new Well($Form);
        }

        if (null !== $File) {

            if ($File->getError()) {
                $Form->setError('File', 'Fehler');
                return new Well($Form);
            }
            /** Prepare */
            $File = $File->move($File->getPath(), $File->getFilename() . '.' . $File->getClientOriginalExtension());
            /** Read */
            $Document = Document::getDocument($File->getPathname());
            if (!$Document instanceof UniversalXml) {
                $Form->setError('File', 'XML kann nicht ausgelesen werden');
                return new Well($Form);
            }

            return Timetable::useFrontend()->frontendImportTimetable($File, $Data);
        }
        return new Danger('File nicht gefunden');
    }

    public function getArrayFromTimetableFile(File $File)
    {

        $result = array();
        $unterrichtList = array();
        $planList = array();
        $this->Document = Document::getDocument($File->getPathname());
        /** @var Node $Node */
        // note = "upsp"
        $Node = $this->Document->getContent();
//        if(($Unterricht = $Node->getChild('unterricht'))){
//            $UnterrichtChildList = $Unterricht->getChildList();
//            /** @var Node $UnterrichtChild */
//            foreach($UnterrichtChildList as $UnterrichtChild){
//                $item = array();
//                $item['un_nummer'] = '';
////                $item['un_stunden'] = '';
//                $item['un_stufe'] = '';
//                $item['un_klassen'] = array();
//                $item['un_fach'] = '';
//                $item['un_lehrer'] = array();
//                $item['un_gruppe'] = '';
//                if(($unList = $UnterrichtChild->getChildList())){
////                    Debugger::devDump($unList);
//                    foreach($unList as $un){
//                        if($un->getName() == 'un_lehrer'){
//                            $unLehrerList = $un->getChildList();
//                            foreach($unLehrerList as $unLehrer){
//                                $item[$un->getName()][] = $unLehrer->getContent();
//                            }
//                        } elseif($un->getName() == 'un_klassen'){
//                            $unKlassenList = $un->getChildList();
//                            foreach($unKlassenList as $unKlassen){
//                                $item[$un->getName()][] = $unKlassen->getContent();
//                            }
//                        } else {
//                            $item[$un->getName()] = $un->getContent();
//                        }
//                    }
//                }
//                if($item['un_nummer'] !== ''
//                && $item['un_fach'] !== ''
//                && !empty($item['un_lehrer'])
//                && !empty($item['un_klassen'])
//                && $item['un_stufe'] !== ''
//                ){
//                    array_push($unterrichtList, $item);
//                }
//            }
//        }
//
//        if(($Unterricht = $Node->getChild('plan'))){
//            $PlanChildList = $Unterricht->getChildList();
//            /** @var Node $UnterrichtChild */
//            foreach($PlanChildList as $PlanChild){
//                if($PlanChild->getName() == 'pl'){
//                    $plList = $PlanChild->getChildList();
//                    $item = array();
//                    $item['pl_nummer'] = '';
//                    $item['pl_stunde'] = '';
//                    $item['pl_tag'] = '';
//                    $item['pl_woche'] = '';
//                    $item['pl_raum'] = '';
//                    foreach($plList as $pl){
//                        $item[$pl->getName()] = $pl->getContent();
//                    }
//                    if($item['pl_nummer'] !== ''
//                    || $item['pl_tag'] !== ''
//                    || $item['pl_stunde'] !== ''){
//                        array_push($planList, $item);
//                    }
//                }
//            }
//        }
        // Wochen
        $WeekImport = array();
        if(($Grunddaten = $Node->getChild('grunddaten'))){
            if(($Weeks = $Grunddaten->getChild('g_schulwochen'))){
                $WeekList = $Weeks->getChildList();
                foreach($WeekList as $Week){
                    $item = array();
                    $item['Nr'] = $Week->getAttribute('g_sw_nr');
                    $item['Week'] = $Week->getAttribute('g_sw_wo');
                    preg_match('!\d{2}.\d{2}.\d{4}!is',$Week->getContent(), $Match);
                    if(isset($Match[0])){
                        $item['Date'] = $Match[0];
                    } else {
                        $item['Date'] = '';
                    }
                    if($item['Nr'] != ''
                    && $item['Week'] != ''
                    && $item['Date'] != ''){
                        array_push($WeekImport, $item);
                    }

                }
            }
        }
//        Debugger::devDump($WeekImport);

        // Kombiniren der Einträge
        foreach($unterrichtList as $unterricht){
            foreach($planList as $plan){
                if($unterricht['un_nummer'] === $plan['pl_nummer']){
                    foreach($unterricht['un_lehrer'] as $Teacher){
                        foreach($unterricht['un_klassen'] as $Division){
                            $item = array();
                            $item['stunde'] = $plan['pl_stunde'];
                            $item['tag'] = $plan['pl_tag'];
                            $item['woche'] = $plan['pl_woche'];
                            $item['raum'] = $plan['pl_raum'];
                            $item['fach'] = $unterricht['un_fach'];
                            $item['stufe'] = $unterricht['un_stufe'];
                            $item['klasse'] = $Division;
                            $item['lehrer'] = $Teacher;
                            $item['gruppe'] = $unterricht['un_gruppe'];
                            array_push($result, $item);
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function getProductiveResult($result = array())
    {

        $FilterResult = array();
        $tblYearList = Term::useService()->getYearByNow();

        foreach($result as $Row){
            $isRaum = $isLehrer = $isKlasse = $isFach = true;
            // frontend column
            $raum = $lehrer = $klasse = $fach = '';
            $Row['SSWLehrer'] = $Row['SSWKlasse'] = $Row['SSWFach'] = '';
            // backend
            $Row['tblPerson'] = $Row['tblCourse'] = $Row['tblSubject'] = false;
            if(isset($Row['fach']) && $Row['fach'] !== ''){
                $Row['tblSubject'] = Subject::useService()->getSubjectByAcronym($Row['fach']);
                if ($Row['tblSubject']) {
                    $Row['SSWFach'] = $Row['tblSubject']->getAcronym().' - '.$Row['tblSubject']->getName();
                } else {
                    $Row['SSWFach'] = $this->setHiddenSort($Row['fach']).new ToolTip(new DangerText(new Remove().' '.$Row['fach']), 'Fach nicht vorhanden');
                    $isFach = false;
                }
            } else {
                $Row['fach'] = $this->setHiddenSort().new ToolTip(new DangerText(new Remove()), 'Für den Import fehlt das Fach') ;
                $isFach = false;
            }
            if(isset($Row['klasse']) && $Row['klasse'] !== ''){
                if($tblYearList){
                    // Suche nach SSW Klasse
                    foreach ($tblYearList as $tblYear) {
                        //ToDO Change Division to Course
                        if (($tblDivision = Division::useService()->getDivisionByDivisionDisplayNameAndYear($Row['klasse'], $tblYear))) {
//                        $Row['tblDivision'] = $tblDivision;
                            $Row['tblCourse'] = $tblDivision;
                            $Row['SSWKlasse'] = $tblDivision->getDisplayName();
                            break;
                        }
                    }
                }
                if(!$Row['tblCourse']){
                    $Row['SSWKlasse'] = $this->setHiddenSort($Row['klasse']).new ToolTip(new DangerText(new Remove().' '.$Row['klasse']), 'Klasse nicht vorhanden ');
                    $isKlasse = false;
                }
            } else {
                $Row['klasse'] = $this->setHiddenSort().new ToolTip(new DangerText(new Remove()), 'Für den Import fehlt die Klasse') ;
                $isKlasse = false;
            }
            if(isset($Row['lehrer']) && $Row['lehrer'] !== ''){
                $Row['tblPerson'] = false;
                $tblTeacher = Teacher::useService()->getTeacherByAcronym($Row['lehrer']);
                if($tblTeacher && $tblTeacher->getServiceTblPerson()){
                    $Row['tblPerson'] = $tblTeacher->getServiceTblPerson();
                }
                if($Row['tblPerson']){
                    $Row['SSWLehrer'] = $Row['tblPerson']->getLastFirstName();
                } else {
                    $Row['SSWLehrer'] = $this->setHiddenSort($Row['lehrer']).new ToolTip(new DangerText(new Remove().' '.$Row['lehrer']), 'Kürzel keiner Person zugewiesen');
                    $isLehrer = false;
                }
            } else {
                $Row['lehrer'] = $this->setHiddenSort().new ToolTip(new DangerText(new Remove()), 'Für den Import fehlt eine Lehrkraft') ;
                $isLehrer = false;
            }
            if(!isset($Row['raum']) && $Row['raum'] !== ''){
                $Row['raum'] = $this->setHiddenSort().new ToolTip(new DangerText(new Remove()), 'Für den Import fehlt ein Raum') ;
                $isRaum = false;
            }
//            if($isWarning){
                // Pflichtangaben
                if(!$isFach || !$isKlasse || !$isLehrer || !$isRaum){
                    array_push($this->WarningList, $Row);
                } elseif($isFach && $isKlasse && $isLehrer && $isRaum){
                    $Row['success'] = new SuccessText(new SuccessIcon());
                    array_push($this->UploadList, $Row);
                }
//            } else {
//                // Pflichtangaben
//                if($isFach && $isKlasse && $isLehrer && $isRaum){
//                    $Row['success'] = new SuccessText(new SuccessIcon());
//                    array_push($this->UploadList, $Row);
//                }
//            }
        }
//        return $FilterResult;
    }

    private function setHiddenSort($Value = '')
    {

        return '<span hidden>0'.$Value.'</span>';
    }

    /**
     * @param string $Name
     * @param string $Description
     * @param \DateTime $DateFrom
     * @param \DateTime $DateTo
     * @param $ImportList
     * @return bool
     */
    public function importTimetable(string $Name, string $Description, \DateTime $DateFrom, \DateTime $DateTo, $ImportList)
    {

        // insert
        $tblTimetable = TimetableClassRegister::useService()->createTimetable($Name, $Description, $DateFrom, $DateTo);
        if($tblTimetable){
            return TimetableClassRegister::useService()->createTimetableNodeBulk($tblTimetable, $ImportList);
        }
        return false;
    }

//    /**
//     * @param $Result
//     * @return void
//     */
//    public function importUpdateTimetableNode($Result)
//    {
//        // ToDO nur noch Sachen löschen, welche durch den Import ersetzt werden (ja Klasse / Kurs)
//        // cleanup
//        TimetableClassRegister::useService()->destroyTimetableAllByTimetableAndCourseBulk();
//        // insert
//        TimetableClassRegister::useService()->createTimetableNodeBulk($Result);
//    }
}