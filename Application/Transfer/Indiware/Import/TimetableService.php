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
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class TimetableService
{

    private $UploadList = array();
    private $WarningList = array();
    private $CountImport = array();

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
     * @return array
     */
    public function getCountImport(): array
    {
        return $this->CountImport;
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

    public function getTimeTableImportFromFile(File $File)
    {

        $timetableImport = array();
        $unterrichtList = array();
        $planList = array();
        $this->Document = Document::getDocument($File->getPathname());
        /** @var Node $Node */
        // note = "upsp"
        $Node = $this->Document->getContent();
        if(($Unterricht = $Node->getChild('unterricht'))){
            $UnterrichtChildList = $Unterricht->getChildList();
            foreach($UnterrichtChildList as $UnterrichtChild){
                $item = array();
                $item['un_nummer'] = '';
//                $item['un_stunden'] = '';
                $item['un_stufe'] = '';
                $item['un_klassen'] = array();
                $item['un_fach'] = '';
                $item['un_lehrer'] = array();
                $item['un_gruppe'] = '';
                if(($unList = $UnterrichtChild->getChildList())){
//                    Debugger::devDump($unList);
                    foreach($unList as $un){
                        if($un->getName() == 'un_lehrer'){
                            $unLehrerList = $un->getChildList();
                            foreach($unLehrerList as $unLehrer){
                                $item[$un->getName()][] = $unLehrer->getContent();
                            }
                        } elseif($un->getName() == 'un_klassen'){
                            $unKlassenList = $un->getChildList();
                            foreach($unKlassenList as $unKlassen){
                                $item[$un->getName()][] = $unKlassen->getContent();
                            }
                        } else {
                            $item[$un->getName()] = $un->getContent();
                        }
                    }
                }
                if($item['un_nummer'] !== ''
                && $item['un_fach'] !== ''
                && !empty($item['un_lehrer'])
                && !empty($item['un_klassen'])
                && $item['un_stufe'] !== ''
                ){
                    array_push($unterrichtList, $item);
                }
            }
        }

        if(($Plan = $Node->getChild('plan'))){
            $PlanChildList = $Plan->getChildList();
            foreach($PlanChildList as $PlanChild){
                if($PlanChild->getName() == 'pl'){
                    $plList = $PlanChild->getChildList();
                    $item = array();
                    // auszulesende Werte vordefiniert
                    $item['pl_nummer'] = '';
                    $item['pl_stunde'] = '';
                    $item['pl_tag'] = '';
                    $item['pl_woche'] = '';
                    $item['pl_raum'] = '';
                    foreach($plList as $pl){
                        $item[$pl->getName()] = $pl->getContent();
                    }
                    // Mindestbedingung zur aufnahme der Daten
                    if($item['pl_nummer'] !== ''
                    || $item['pl_tag'] !== ''
                    || $item['pl_stunde'] !== ''){
                        array_push($planList, $item);
                    }
                }
            }
        }

        // Kombiniren der Einträge
        foreach($unterrichtList as $unterricht){
            foreach($planList as $plan){
                if($unterricht['un_nummer'] === $plan['pl_nummer']){
                    foreach($unterricht['un_lehrer'] as $Teacher){
                        foreach($unterricht['un_klassen'] as $Division){
                            $item = array();
                            $item['Hour'] = $plan['pl_stunde'];
                            $item['Day'] = $plan['pl_tag'];
                            $item['Week'] = $plan['pl_woche'];
                            $item['Room'] = $plan['pl_raum'];
                            $item['Subject'] = $unterricht['un_fach'];
                            $item['Level'] = $unterricht['un_stufe'];
                            $item['Division'] = $Division;
                            $item['Person'] = $Teacher;
                            $item['SubjectGroup'] = $unterricht['un_gruppe'];
                            array_push($timetableImport, $item);
                        }
                    }
                }
            }
        }

        return $timetableImport;
    }

    public function getWeekDataFromFile(File $File)
    {

        $this->Document = Document::getDocument($File->getPathname());
        /** @var Node $Node */
        // note = "upsp"
        $Node = $this->Document->getContent();

        // Wochen
        $WeekImport = array();
        if(($Grunddaten = $Node->getChild('grunddaten'))){
            if(($Weeks = $Grunddaten->getChild('g_schulwochen'))){
                $WeekList = $Weeks->getChildList();
                foreach($WeekList as $Week){
                    $item = array();
                    $item['Number'] = $Week->getAttribute('g_sw_nr');
                    $item['Week'] = $Week->getAttribute('g_sw_wo');
                    $item['Date'] = $Week->getContent();

                    if($item['Number'] != ''
                        && $item['Week'] != ''
                        && $item['Date'] != ''){
                        array_push($WeekImport, $item);
                    }

                }
            }
        }
        return $WeekImport;
    }

    /**
     * @param array $result
     * @param \DateTime $Date
     * @return void
     * @throws \Exception
     */
    public function getProductiveResult(array $result, \DateTime $Date)
    {

        // Jahresliste nach "Start Datum"
        $tblYearList = Term::useService()->getYearAllByDate($Date);

        foreach($result as $Row){
            $isRoom = $isPerson = $isDivision = $isSubject = true;
            // frontend column
            $Room = $Person = $Division = $Subject = '';
            $Row['SSWPerson'] = $Row['SSWDivision'] = $Row['SSWSubject'] = '';
            // backend
            $Row['tblPerson'] = $Row['tblCourse'] = $Row['tblSubject'] = false;
            if(isset($Row['Subject']) && $Row['Subject'] !== ''){
                $Row['tblSubject'] = Subject::useService()->getSubjectByAcronym($Row['Subject']);
            }
            if (!$Row['tblSubject']) {
                $this->CountImport['Subject'][$Row['Subject']][] = 'Fach nicht gefunden';
            }
            if(isset($Row['Division']) && $Row['Division'] !== ''){
                if($tblYearList){
                    // Suche nach SSW Klasse
                    foreach ($tblYearList as $tblYear) {
                        //ToDO Change Division to Course
                        if (($tblDivision = Division::useService()->getDivisionByDivisionDisplayNameAndYear($Row['Division'], $tblYear))) {
//                        $Row['tblDivision'] = $tblDivision;
                            $Row['tblCourse'] = $tblDivision;
                            $Row['SSWDivision'] = $tblDivision->getDisplayName();
                            break;
                        }
                    }
                }
            }
            if(!$Row['tblCourse']){
                $this->CountImport['Division'][$Row['Division']][] = 'Klasse nicht gefunden';
            }
            if(isset($Row['Person']) && $Row['Person'] !== ''){
                $tblTeacher = Teacher::useService()->getTeacherByAcronym($Row['Person']);
                if($tblTeacher && $tblTeacher->getServiceTblPerson()){
                    $Row['tblPerson'] = $tblTeacher->getServiceTblPerson();
                }
            }
            if(!$Row['tblPerson']){
                $this->CountImport['Person'][$Row['Person']][] = 'Lehrerkürzel nicht gefunden';
            }
            // Pflichtangaben
            if(!$Row['tblSubject'] || !$Row['tblCourse'] || !$Row['tblPerson'] || $Row['Room'] == ''){
                array_push($this->WarningList, $Row);
            } elseif($isSubject && $isDivision && $isPerson && $isRoom){
                array_push($this->UploadList, $Row);
            }
        }
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
     * @param array $ImportList
     * @param array $WeekImport
     * @return bool
     */
    public function importTimetable(string $Name, string $Description, \DateTime $DateFrom, \DateTime $DateTo, $ImportList, $WeekImport)
    {

        // insert
        $tblTimetable = TimetableClassRegister::useService()->createTimetable($Name, $Description, $DateFrom, $DateTo);
        if($tblTimetable){
            TimetableClassRegister::useService()->createTimetableWeekBulk($tblTimetable, $WeekImport);
            return TimetableClassRegister::useService()->createTimetableNodeBulk($tblTimetable, $ImportList);
        }
        return false;
    }
}