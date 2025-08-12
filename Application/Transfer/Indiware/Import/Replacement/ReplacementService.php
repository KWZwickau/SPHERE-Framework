<?php
namespace SPHERE\Application\Transfer\Indiware\Import\Replacement;

use DateTime;
use MOC\V\Component\Document\Component\Bridge\Repository\UniversalXml;
use MOC\V\Component\Document\Document;
use MOC\V\Component\Document\Exception\DocumentTypeException as DocumentTypeException;
use MOC\V\Component\Document\Vendor\UniversalXml\Source\Node;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetableNode;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable as TimetableTool;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\Transfer\Education\Education;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImport;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImportMapping;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ReplacementService
{

    private array $UploadList = array();
    private array $WarningList = array();
    private array $DateList = array();
    /* @var TblDivisionCourse[] $CourseList */
    private array $CourseList = array();
    private array $CountImport = array();

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
    public function getDateList(): array
    {
        return $this->DateList;
    }

    /**
     * @return array
     */
    public function getCourseList(): array
    {
        return $this->CourseList;
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
    public function readReplacementFromFile(IFormInterface $Form = null, UploadedFile $File = null, array $Data = array())
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
        }
        if($IsError){
            return new Well($Form);
        }

        if (null !== $File) {

            if ($File->getError()) {
                $Form->setError('File', 'Fehler: '.$File->getError());
                return new Well($Form);
            }
            if (strtoupper($File->getClientOriginalExtension()) != 'XML') {
                $Form->setError('File', 'Fehler: Datei muss eine XML sein');
                return new Well($Form);
            }
            /** Prepare */
            $File = $File->move($File->getPath(), $File->getFilename() . '.' . $File->getClientOriginalExtension());
            /** Read */
            $Document = Document::getDocument($File->getPathname());
            // Prüfung auf Verwendbarkeit
            /** @var Node $Node */
            // note = "upsp"
            $Node = $Document->getContent();
            if(!($Node->getChild('kopf'))
                || !($Node->getChild('plan'))){
                $Form->setError('File', 'Fehler im Inhalt der Datei');
                return new Well($Form);
            }

            if (!$Document instanceof UniversalXml) {
                $Form->setError('File', 'XML kann nicht ausgelesen werden');
                return new Well($Form);
            }

            return Replacement::useFrontend()->frontendImportReplacement($File, $Data);
        }
        return new Danger('File nicht gefunden');
    }

    /**
     * @param File $File
     * @return array|string
     */
    public function getReplacementImportFromFile(File $File)
    {
        $Document = Document::getDocument($File->getPathname());
        /** @var Node $Node */

        $Date = $Day = null;
        // note = "upsp"
        $Node = $Document->getContent();
        if(($Kopf = $Node->getChild('kopf'))){
            $Datum = $Kopf->getChild('datum');
            if($Datum){
                $DateString = $this->getUtf8Encode($Datum->getContent());
                $Date = $this->getDateFromString($DateString);
            }
            $DayChild = $Kopf->getChild('tag');
            if($DayChild){
                $Day = $DayChild->getContent();
            }

        }
        if(!$Date){
            if(!isset($DateString)){
                $DateString = 'nicht vorhanden!';
            }
            return 'Datum in der Datei nicht auslesbar! ('.$DateString.')';
         }
        if(!$Day){
            return 'Tag in der Datei nicht auslesbar!';
        }

        $ImportList = array();
        for ($i = 1; $i <= 5; $i++){
            if(($plan = $Node->getChild('plan', array('tg' => $i)))){
                if(($PlanList = $plan->getChildList())){
                    foreach($PlanList as $Pl){
                        // Daten kommen UTF8 Codiert
                        $DateTemp = clone($Date);
                        $item = array();
                        $plTag = $Pl->getChild('pl_tag');
                        $item['Tag'] = $plTag->getContent();
                        if(($plStunde = $Pl->getChild('pl_stunde'))){
                            $item['Hour'] = $plStunde->getContent();
                        } else {
                            $item['Hour'] = '';
                        }
                        if(($plFach = $Pl->getChild('pl_fach'))){
                            $item['Subject'] = $this->getUtf8Encode($plFach->getContent());
                        } else {
                            $item['Subject'] = '';
                        }
                        if(($plFachori = $Pl->getChild('pl_fachori'))){
                            $item['SubjectOriginal'] = $this->getUtf8Encode($plFachori->getContent());
                        } else {
                            $item['SubjectOriginal'] = '';
                        }
                        if(($plKlasse = $Pl->getChild('pl_klasse'))){
                            $item['Course'] = $this->getUtf8Encode($plKlasse->getContent());
                        } else {
                            $item['Course'] = '';
                        }
                        if(($plLehrer = $Pl->getChild('pl_lehrer'))){
                            $item['Person'] = $this->getUtf8Encode($plLehrer->getContent());
                        } else {
                            $item['Person'] = '';
                        }
                        if(($plRaum = $Pl->getChild('pl_raum'))){
                            $item['Room'] = $this->getUtf8Encode($plRaum->getContent());
                        } else {
                            $item['Room'] = '';
                        }
                        if(($plGruppe = $Pl->getChild('pl_gruppe'))){
                            $item['SubjectGroup'] = $this->getUtf8Encode($plGruppe->getContent());
                        } else {
                            $item['SubjectGroup'] = '';
                        }

                        $Difference = $Day - $plTag->getContent();
                        if($Difference < 0){
                            $DifferenceTemp = $Difference * -1;
                            $item['Date'] = $DateTemp->add(new \DateInterval('P'.$DifferenceTemp.'D'));
                        }elseif($Difference > 0){
                            $item['Date'] = $DateTemp->sub(new \DateInterval('P'.$Difference.'D'));
                        } else {
                            $item['Date'] = $DateTemp;
                        }
                        $this->DateList[$Day - $Difference] = $item['Date'];

                        $ImportList[] = $item;

                        if ($item['Course']) {
                            for ($j = 1; $j < 10; $j++) {
                                $temp = $Pl->getChild('pl_klasse', null, $j);
                                // aus getChild können integer rauskommen. Soll nur bei einem Objekt weiter machen
                                if(is_object($temp)){
                                    if (method_exists($temp, 'getContent') &&
                                        ($division = $this->getUtf8Encode($temp->getContent()))
                                    ) {
                                        $addItem = $item;
                                        $addItem['Course'] = $division;
                                        $ImportList[] = $addItem;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $ImportList;
    }

    /**
     * @param $Value
     * @return \DateTime|null
     */
    private function getDateFromString($Value = '')
    {
        $Date = null;
        if((preg_match('!\w+, (\d+). ([\wäöü]+) (\d+)!', $Value, $match))){
            $Day = $match[1];
            $Month = $match[2];
            $Year = $match[3];
            $Month = $this->getMonth($Month);
            if($Month){
                $Date = new DateTime($Day.'.'.$Month.'.'.$Year);
            }
        }
        return $Date;
    }

    /**
     * @param $Value
     * @return int|null
     */
    private function getMonth($Value = '')
    {

        $Month = null;
        switch ($Value){
            case 'Januar': $Month = 1; break;
            case 'Februar': $Month = 2; break;
            case 'März': $Month = 3; break;
            case 'April': $Month = 4; break;
            case 'Mai': $Month = 5; break;
            case 'Juni': $Month = 6; break;
            case 'Juli': $Month = 7; break;
            case 'August': $Month = 8; break;
            case 'September': $Month = 9; break;
            case 'Oktober': $Month = 10; break;
            case 'November': $Month = 11; break;
            case 'Dezember': $Month = 12; break;
        }
        return $Month;
    }

    /**
     * @param array $result
     * @param \DateTime $Date
     * @return void
     */
    public function getReplacementResult(array $result)
    {
        $tblYearList = Term::useService()->getYearByNow();
        foreach($result as $Row){
            $Row['tblPerson'] = $Row['tblCourse'] = $Row['tblSubstituteSubject'] = false;

            if(isset($Row['Subject']) && $Row['Subject'] !== ''){
                // Mapping
                if (($tblSubject = Education::useService()->getImportMappingValueBy(TblImportMapping::TYPE_SUBJECT_ACRONYM_TO_SUBJECT_ID, $Row['Subject']))) {

                // Found
                } else {
                    $tblSubject = Subject::useService()->getSubjectByVariantAcronym($Row['Subject']);
                }

                if ($tblSubject) {
                    $Row['tblSubstituteSubject'] = $tblSubject;
                }
            }
            if (!$Row['tblSubstituteSubject']) {
                $this->CountImport['Subject'][$Row['Subject']][] = 'Fach nicht gefunden';
            }

            if(isset($Row['Course']) && $Row['Course'] !== ''){
                if($tblYearList){
                    // Suche nach SSW Klasse
                    foreach ($tblYearList as $tblYear) {
                        // Mapping
                        if (($tblDivisionCourse = Education::useService()->getImportMappingValueBy(
                            TblImportMapping::TYPE_DIVISION_NAME_TO_DIVISION_COURSE_NAME, $Row['Course'], $tblYear
                        ))) {

                        // Found
                        } else {
                            $tblDivisionCourse = Education::useService()->getDivisionCourseByDivisionNameAndYear($Row['Course'], $tblYear);
                        }

                        if ($tblDivisionCourse && $tblSubject) {
                            // Spezialfall: Stundenplan für SekII -> es werden direkt beim Stundenplan die SekII-Kurse zugeordnet, falls vorhanden
                            if (DivisionCourse::useService()->getIsCourseSystemByStudentsInDivisionCourse($tblDivisionCourse)
                                && ($tblStudentList = $tblDivisionCourse->getStudents())
                                && ($tblYear = $tblDivisionCourse->getServiceTblYear())
                            ) {
                                $tblDivisionCourse = false;
                                foreach ($tblStudentList as $tblStudent) {
                                    if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblStudent, $tblYear))
                                        && ($level = $tblStudentEducation->getLevel())
                                        && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                                    ) {
                                        $divisionCourseName = Education::useService()->getCourseNameForSystem(
                                            TblImport::EXTERN_SOFTWARE_NAME_INDIWARE, $Row['SubjectGroup'], $level, $tblSchoolType
                                        );

                                        // mapping SekII-Kurs
                                        if (($tblDivisionCourseCourseSystem = Education::useService()->getImportMappingValueBy(
                                            TblImportMapping::TYPE_COURSE_NAME_TO_DIVISION_COURSE_NAME, $divisionCourseName, $tblYear
                                        ))) {

                                            // found SekII-Kurs
                                        } elseif (($tblDivisionCourseCourseSystem = DivisionCourse::useService()->getDivisionCourseByNameAndYear(
                                            $divisionCourseName, $tblYear
                                        ))) {

                                        }

                                        if ($tblDivisionCourseCourseSystem
                                            && ($tblDivisionCourseCourseSystem->getServiceTblSubject())
                                            && $tblDivisionCourseCourseSystem->getServiceTblSubject()->getId() == $tblSubject->getId()
                                        ) {
                                            $tblDivisionCourse = $tblDivisionCourseCourseSystem;
                                        }

                                        break;
                                    }
                                }
                            }
                        }

                        if ($tblDivisionCourse) {
                            $Row['tblCourse'] = $tblDivisionCourse;
                            $Row['CourseId'] = $tblDivisionCourse->getId();
                            break;
                        }
                    }
                }
            }
            if(!$Row['tblCourse']){
                $this->CountImport['Course'][$Row['Course']][] = 'Klasse nicht gefunden';
            }

            if(isset($Row['Person']) && $Row['Person'] !== ''){
                // Mapping
                if (($tblPerson = Education::useService()->getImportMappingValueBy(TblImportMapping::TYPE_TEACHER_ACRONYM_TO_PERSON_ID, $Row['Person']))) {

                    // Found
                } elseif (($tblTeacher = Teacher::useService()->getTeacherByAcronym($Row['Person']))) {
                    $tblPerson = $tblTeacher->getServiceTblPerson();
                }

                if ($tblPerson) {
                    $Row['tblPerson'] = $tblPerson;
                }
            }
            if(!$Row['tblPerson']){
                $this->CountImport['Person'][$Row['Person']][] = 'Lehrerkürzel nicht gefunden';
            }

            // Pflichtangaben
            if($Row['tblSubstituteSubject'] && $Row['tblCourse'] && $Row['tblPerson']) { // && $isRoom
                // Löschliste für Klassen
                if(isset($tblDivisionCourse) && $tblDivisionCourse){
                    $this->CourseList[$tblDivisionCourse->getId()] = $tblDivisionCourse;
                }
                // import
                array_push($this->UploadList, $Row);
            } else {
                array_push($this->WarningList, $Row);
            }
        }
    }

    /**
     * @param $ImportList
     * @return array
     */
    public function getCompareImportList($ImportList)
    {

        $tblCourseList = $this->getCourseList();

        $ReplaceList = array();
        foreach($ImportList as $ImportRow){
            $Day = (string)$ImportRow['Tag'];
            $Hour = (string)$ImportRow['Hour'];
            $CourseId = (string)$ImportRow['CourseId'];
            $ReplaceList[$Day][$Hour][$CourseId][] = $ImportRow;
        }

        $TimeTableList = array();
        if(($tblTimeTableList = TimetableTool::useService()->getTimetableListByDateTime(new DateTime()))){
            foreach($tblTimeTableList as $tblTimeTable){
                if(($tblTimeTableNodeList = TimetableTool::useService()->getTimetableNodeListByTimetable($tblTimeTable))){
                    foreach($tblTimeTableNodeList as $tblTimeTableNode){
                        if(($tblTimeTableCourse = $tblTimeTableNode->getServiceTblCourse()) && key_exists($tblTimeTableCourse->getId(), $tblCourseList)){
                            $Day = (string)$tblTimeTableNode->getDay();
                            $Hour = (string)$tblTimeTableNode->getHour();
                            $CourseId = (string)$tblTimeTableNode->getServiceTblCourse()->getId();
                            $TimeTableList[$Day][$Hour][$CourseId][] = $tblTimeTableNode;
                        }
                    }
                }
            }
        }

        $DayList = $this->getDateList();
        $DifferenceList = array();
        // Day / Wochentag
        for($DayCount = 1; $DayCount <= 5; $DayCount++){
            // Hour / Unterrichtsstunde
            for($HourCount = 1; $HourCount <= 12; $HourCount++){
                foreach($tblCourseList as $CourseId => $tblCourse){
                    if(isset($TimeTableList[$DayCount][$HourCount][$CourseId])
                    && isset($ReplaceList[$DayCount][$HourCount][$CourseId])){
                        $tempSubjectListTimeTable = array();
                        $tempSubjectListReplacement = array();
                        // Vergleich der 2 Unterrichtseinträge (beides Listen)
                        /** @var TblTimetableNode $tblTimeTableNode */
                        foreach($TimeTableList[$DayCount][$HourCount][$CourseId] as $tblTimeTableNode){
                            $tempSubjectListTimeTable[$tblTimeTableNode->getServiceTblSubject()->getId()] = $tblTimeTableNode;
                            foreach($ReplaceList[$DayCount][$HourCount][$CourseId] as &$Row) {
                                if ($Row['Date'] == $DayList[$tblTimeTableNode->getDay()]
//                                && $Row['Room'] == $tblTimeTableNode->getRoom()
                                    && $Row['SubjectGroup'] == $tblTimeTableNode->getSubjectGroup()
                                    && $Row['tblSubstituteSubject']->getId() == $tblTimeTableNode->getServiceTblSubject()->getId()
                                    && $Row['tblCourse']->getId() == $tblTimeTableNode->getServiceTblCourse()->getId()
                                    && isset($Row['tblPerson']) && $tblTimeTableNode->getServiceTblPerson()
                                    && $Row['tblPerson']->getId() == $tblTimeTableNode->getServiceTblPerson()->getId()
                                ){
                                    $Row['found'] = true;
                                }

                                // Originalfach aus dem Import benutzen (ist nicht immer gepflegt)
                                $tblSubject = false;
                                if($Row['SubjectOriginal']){
                                    // Mapping
                                    if(($tblSubject = Subject::useService()->getSubjectByMappingAccronym($Row['SubjectOriginal']))){
                                        $Row['tblSubject'] = $tblSubject;
                                        if ($Row['Date'] == $DayList[$tblTimeTableNode->getDay()]
                                            && $tblSubject->getId() == $tblTimeTableNode->getServiceTblSubject()->getId()
                                        ) {
                                            $tempSubjectListReplacement[$tblTimeTableNode->getServiceTblSubject()->getId()] = true;
                                        }
                                    }
                                }
                                // Originalfach anhand des Stundenplans finden (Muss eindeutig sein)
                                if(!$tblSubject){
                                    if ($Row['Date'] == $DayList[$tblTimeTableNode->getDay()]
                                        && $Row['tblSubstituteSubject']->getId() == $tblTimeTableNode->getServiceTblSubject()->getId()
                                    ) {
                                        $tempSubjectListReplacement[$tblTimeTableNode->getServiceTblSubject()->getId()] = true;
                                    }
                                    // Vorhandenes Fach anfügen, wenn eindeutig
                                    if(count($TimeTableList[$DayCount][$HourCount][$CourseId]) == 1
                                        && count($ReplaceList[$DayCount][$HourCount][$CourseId]) == 1){
                                        $Row['tblSubject'] = $tblTimeTableNode->getServiceTblSubject();
                                    }
                                }
                            }
                        }

                        $hasFound = true;
                        foreach($ReplaceList[$DayCount][$HourCount][$CourseId] as $Row) {
                            if(!isset($Row['found'])){
                                $hasFound = false;
                                // Es kann nur ein Eintrag pro Fach & Klasse geben, bei Parallelunterricht erzeugte diese Stelle auch parallel gleiche Einträge
//                                $DifferenceList[] = $Row;
                                $DifferenceList[$Row['tblSubstituteSubject'].$Row['tblCourse']->getId()] = $Row;
                            }
                        }

                        // es gibt mehrere Stundenplaneinträge zu einer UE und es fällt z.B. ein Fach davon aus
                        if ($hasFound && count($tempSubjectListTimeTable) > count($tempSubjectListReplacement)) {
                            foreach ($tempSubjectListTimeTable as $subjectId => $tblTimeTableNodeTemp) {
                                if (!isset($tempSubjectListReplacement[$subjectId])) {
                                    $item = array();
                                    $item['Date'] = $DayList[$tblTimeTableNodeTemp->getDay()];
                                    $item['Hour'] = $tblTimeTableNodeTemp->getHour();
                                    $item['Room'] = $tblTimeTableNodeTemp->getRoom();
                                    $item['SubjectGroup'] = $tblTimeTableNodeTemp->getSubjectGroup();
                                    $item['tblSubject'] = false;
                                    $item['tblSubstituteSubject'] = $tblTimeTableNodeTemp->getServiceTblSubject();
                                    $item['tblCourse'] = $tblTimeTableNodeTemp->getServiceTblCourse();
                                    $item['tblPerson'] = $tblTimeTableNodeTemp->getServiceTblPerson();
                                    $item['IsCanceled'] = true;
                                    $DifferenceList[] = $item;
                                }
                            }
                        }
                    } elseif(isset($TimeTableList[$DayCount][$HourCount][$CourseId])){
                        /** @var TblTimetableNode $tblTimeTableNode */
                        foreach($TimeTableList[$DayCount][$HourCount][$CourseId] as $tblTimeTableNode){
                            $Row = array();
                            if(isset($DayList[$tblTimeTableNode->getDay()])){
                                $Row['Date'] = $DayList[$tblTimeTableNode->getDay()];
                                $Row['Hour'] = $tblTimeTableNode->getHour();
                                $Row['Room'] = $tblTimeTableNode->getRoom();
                                $Row['SubjectGroup'] = $tblTimeTableNode->getSubjectGroup();
                                $Row['tblSubject'] = false;
                                $Row['tblSubstituteSubject'] = $tblTimeTableNode->getServiceTblSubject();
                                $Row['tblCourse'] = $tblTimeTableNode->getServiceTblCourse();
                                $Row['tblPerson'] = $tblTimeTableNode->getServiceTblPerson();
                                $Row['IsCanceled'] = true;
                                $DifferenceList[] = $Row;
                            }
                        }
                    } elseif(isset($ReplaceList[$DayCount][$HourCount][$CourseId])) {
                        // zusätzlicher Unterricht aus dem Import
                        foreach($ReplaceList[$DayCount][$HourCount][$CourseId] as &$Row) {
                            $Row['tblSubject'] = false;
                            $Row['IsCanceled'] = false;
                            $DifferenceList[] = $Row;
                        }
                    }
                }
            }
        }

        return $DifferenceList;
    }

    public function removeExistingReplacementByDateListAndDivisionList($DateList, $CourseList)
    {
        $removeList = array();
        if(!empty($DateList) && !empty($CourseList)){
            foreach($DateList as $Date){
                foreach($CourseList as $tblCourse){
                    if(($tblTimetableReplacementList = TimetableTool::useService()->getTimetableReplacementByTime($Date, null, $tblCourse))){
                        foreach($tblTimetableReplacementList as $tblTimetableReplacement){
                            $removeList[] = $tblTimetableReplacement;
                        }
                    }
                }
            }
        }
        if(!empty($removeList)) {
            TimetableTool::useService()->destroyTimetableReplacementBulk($removeList);
        }
    }

    public function importTimetableReplacementBulk($importList)
    {

        TimetableTool::useService()->createTimetableReplacementBulk($importList);
    }

    private function getUtf8Encode(?string $item): string
    {
        if ($item === null || $item === '') {
            return '';
        }

        return utf8_encode($item);
    }

    /**
     * @param string $Json
     * @return string
     */
    public function importJsonReplacement(string $Json, $ManualTest = false){

        $ArrayData = json_decode($Json, true);
        $DateArray = array();
//        $schoolName = '';
        $importList = array();
        // über Webhook erhalten

        if(!$ManualTest){
            if(!isset($ArrayData['Gesamtexport']['Vertretungsplan']['Vertretungsplan'])){
                TimetableTool::useService()->createTimetableReplacementLogEntity('Upload war kein Vertretungsplan oder ungültige/leere JSON');
                return 'Kein Vertretungsplan';
            }

            if(($ReplacementList = $ArrayData['Gesamtexport']['Vertretungsplan']['Vertretungsplan'])){
              // EVSR Händisch als Json erhalten
//            if(isset($ArrayData['Vertretungsplan'])
//                && ($ReplacementList = $ArrayData['Vertretungsplan'])){
                $readList = $this->readReplacement($ReplacementList);
                $importList = $this->getObjectList($readList);
                $DateArray = $this->getDateArray($importList);
            }
        } else {
            // Manuel übertragene JSON sieht anders aus und muss deshalb anders ausgelesen werden
            if(!isset($ArrayData['Vertretungsplan'])){
                TimetableTool::useService()->createTimetableReplacementLogEntity('Upload war kein Vertretungsplan oder ungültige/leere JSON');
                return 'Kein Vertretungsplan';
            }
                // EVSR Händisch als Json erhalten
            if(isset($ArrayData['Vertretungsplan'])
            && ($ReplacementList = $ArrayData['Vertretungsplan'])){
                $readList = $this->readReplacement($ReplacementList);
                $importList = $this->getObjectList($readList);
                $DateArray = $this->getDateArray($importList);
            }
        }

        // Datumsangaben aus dem JSON nach bereich Filtern
        $this->cleanupReplacement($DateArray);

        // nicht funktionale Einträge rausfiltern
        if(!empty($importList)){
            $errorList = array();
            foreach($importList as &$import){
                $errorList[] = $this->validateImport($import);
            }
            $errorList = array_filter($errorList);
            $importList = array_filter($importList);
            // save ErrorList
            if(!empty($errorList)) {
                TimetableTool::useService()->createTimetableReplacementLog($errorList);
            }
            // save TimetableReplacement
            if(!empty($importList)){
                TimetableTool::useService()->createTimetableReplacementJsonBulk($importList);
            }
        }

        if(empty($errorList)){
            TimetableTool::useService()->createTimetableReplacementLogEntity('Upload ohne enthaltene Konflikte!');
        }
        return '';
    }

    /**
     * @param array $ReplacementList
     * @return array
     */
    private function readReplacement(array $ReplacementList = array()):array
    {
        $resultList = array();
        foreach($ReplacementList as $Replacement){
//            // Kopf
//            if(isset($Replacement['Kopf']['Schulname'])){
//                $schoolName = $Replacement['Kopf']['Schulname'];
//            }
            // Aktionen
            if(isset($Replacement['Aktionen'])){
                $ReplacementEntryList = $Replacement['Aktionen'];
                foreach($ReplacementEntryList as $ReplacementEntry){
                    $item = array();
                    $item['Date'] = $ReplacementEntry['Ak_DatumVon']?:'';
                    $Hour = $item['Hour'] = $ReplacementEntry['Ak_StundeVon']?:'';
                    $item['Subject'] = $ReplacementEntry['Ak_Fach']?:'';
                    $item['SubjectV'] = isset($ReplacementEntry['Ak_VFach'])?$ReplacementEntry['Ak_VFach']:'';
                    $item['PersonVArray'] = isset($ReplacementEntry['VLehrer'])?$ReplacementEntry['VLehrer']:array();
                    $item['PersonArray'] = isset($ReplacementEntry['Lehrer'])?$ReplacementEntry['Lehrer']:array();
                    $item['RoomVArray'] = isset($ReplacementEntry['VRaeume'])?$ReplacementEntry['VRaeume']:array();

                    $count = 1;
                    if(isset($ReplacementEntry['Ak_StundenAnz'])){
                        $count = (int)$ReplacementEntry['Ak_StundenAnz'];
                    }
                    $OriginalCourseListV = isset($ReplacementEntry['Klassen'])?$ReplacementEntry['Klassen']: '';
                    $CourseListV = isset($ReplacementEntry['VKlassen'])?$ReplacementEntry['VKlassen']: '';

                    // Mehrere Einträge erzeugen wenn notwendig
                    if(!empty($CourseListV)){
                        foreach($CourseListV as $Key => $CourseV){
//                                if($count > 1){
                            // mehrere Einträge
                            if(is_numeric($Hour)){
                                for($i = $Hour; $i < ($Hour + $count); $i++){
                                    $item['Hour'] = (string)$i;
                                    if(isset($OriginalCourseListV[$Key])){
                                        $item['OriginalCourse'] = $OriginalCourseListV[$Key];
                                    }
                                    $item['Course'] = $CourseV;
                                    $resultList[] = $item;
                                }
                            } else {
                                $item['Hour'] = $Hour;
                                if(isset($OriginalCourseListV[$Key])){
                                    $item['OriginalCourse'] = $OriginalCourseListV[$Key];
                                }
                                $item['Course'] = $CourseV;
                                $resultList[] = $item;
                            }
                        }
                    } else {
                        // Einträge ohne VKlasse
                        foreach($OriginalCourseListV as $Course){
                            for($j = $Hour; $j < ($Hour + $count); $j++){
                                $item['Hour'] = (string)$j;
                                $item['Course'] = '';
                                $item['OriginalCourse'] = $Course;
                                $resultList[] = $item;
                            }
                        }
                    }
                }
            }
        }
        return $resultList;
    }

    /**
     * @param $readList
     * @return array
     */
    private function getObjectList($readList):array
    {

        $resultList = array();
        foreach ($readList as $read) {
            $item = array();
            $Date = $read['Date'];
            $DateTime = new DateTime($Date);
            $Hour = $read['Hour'];
            $CourseV = $read['Course'];
            $tblCourse = false;
            $Subject = $read['Subject'];
            $tblSubject = false;
            $SubjectV = $read['SubjectV'];
            $tblSubjectV = false;
            $IsCanceled = ($SubjectV == ''? true : false);
            $PersonV = current($read['PersonVArray']);
            // InitialLehrer für den Ausfall eintragen
            if(empty($PersonV) && $IsCanceled){
                $PersonV = current($read['PersonArray']);
            }
            $tblPersonV = false;
            $RoomV = current($read['RoomVArray']);

            if($IsCanceled && !$CourseV){
                $CourseV = $read['OriginalCourse'];
            }

            if($CourseV && ($YearList = Term::useService()->getYearAllByDate($DateTime))){ // Mapping
                foreach($YearList as $Year){
                    if (!($tblCourse = Education::useService()->getDivisionCourseByDivisionNameAndYear($CourseV, $Year))){
                        if (!($tblCourse = Education::useService()->getImportMappingValueBy(TblImportMapping::TYPE_DIVISION_NAME_TO_DIVISION_COURSE_NAME, $CourseV, $Year))) {
                            $tblCourse = Education::useService()->getImportMappingValueBy(TblImportMapping::TYPE_COURSE_NAME_TO_DIVISION_COURSE_NAME, $CourseV, $Year);
                        }
                    }
                    if($tblCourse){
                        break;
                    }
                }
            }
            if($Subject){ // Mapping
                if (!($tblSubject = Subject::useService()->getSubjectByVariantAcronym($Subject))) {
                    $tblSubject = Education::useService()->getImportMappingValueBy(TblImportMapping::TYPE_SUBJECT_ACRONYM_TO_SUBJECT_ID, $Subject);
                }
            }
            if($SubjectV){ // Mapping
                if (!($tblSubjectV = Subject::useService()->getSubjectByVariantAcronym($SubjectV))) {
                    $tblSubjectV = Education::useService()->getImportMappingValueBy(TblImportMapping::TYPE_SUBJECT_ACRONYM_TO_SUBJECT_ID, $SubjectV);
                }
            }
            if($PersonV){ // Mapping
                if (($tblTeacher = Teacher::useService()->getTeacherByAcronym($PersonV))) {
                    $tblPersonV = $tblTeacher->getServiceTblPerson();
                }
                if(!$tblPersonV){
                    $tblPersonV = Education::useService()->getImportMappingValueBy(TblImportMapping::TYPE_TEACHER_ACRONYM_TO_PERSON_ID, $PersonV);
                }
            }

//            $item['SchoolName'] = $schoolName;
//            $item['ReplacementId'] = '';
            $item['Date'] = $DateTime;
            $item['DateString'] = $Date;
            $item['Hour'] = $Hour;
            $item['Room'] = $RoomV;
            $item['RoomString'] = $RoomV;
            $item['Course'] = $CourseV;
            $item['tblCourse'] = $tblCourse;
            $item['IsCanceled'] = $IsCanceled;
            $item['Subject'] = $Subject;
            $item['tblSubject'] = $tblSubject;
            $item['SubjectSubstitute'] = $SubjectV;
            $item['tblSubstituteSubject'] = $tblSubjectV;
            // eigentlich array
            $item['PersonAcronym'] = $PersonV;
            $item['tblPersonV'] = $tblPersonV;

            $resultList[] = $item;
        }
        return $resultList;
    }

    /**
     * @param $importList
     * @return array
     */
    private function getDateArray($importList):array
    {
        $ArrayDateList = array();
        if(!empty($importList)){
            foreach($importList as $import){
                if($import['DateString']){
                    $ArrayDateList[] = $import['DateString'];
                }
            }
            $ArrayDateList = array_unique($ArrayDateList);
        }
        return $ArrayDateList;
    }

    /**
     * @param $DateArray
     * @return void
     */
    private function cleanupReplacement($DateArray):void
    {
        if(!empty($DateArray)){
            $fromDate = false;
            $toDate = false;
            foreach($DateArray as $DateString){
                if(($compareDate = new DateTime($DateString))){
                    if(!$fromDate || $fromDate > $compareDate) {
                        $fromDate = $compareDate;
                    }
                    if(!$toDate || $compareDate > $toDate){
                        $toDate = $compareDate;
                    }
                }
            }
            // übermittelten Zeitraum bereinigen
            if($fromDate && $toDate){
                // clear to clean build up
                if(($tblTimetableReplacementList = TimetableTool::useService()->getTimetableReplacementByDate($fromDate, $toDate))){
                    TimetableTool::useService()->destroyTimetableReplacementBulk($tblTimetableReplacementList);
                }
            }
        }
    }

    /**
     * @param $import
     * @return false|mixed
     */
    private function validateImport(&$import)
    {

        $errors = [];
//        if (empty($import['SchoolName'])) {
//            $errors[] = '[SchoolName] => keine Schule angegeben';
//        }
        if (!$import['DateString']) {
            $errors[] = '[Datum] => kein Datum angegeben';
        }
        if (!$import['Hour']) {
            $errors[] = '[Stunde] => keine Stunde angegeben';
        } elseif(!is_numeric($import['Hour'])){
            $errors[] = '[Stunde] => Wert ist kein Zahl';
        }
        if (!$import['tblCourse']) {
            $errors[] = '[Klasse] - '.$import['Course'].' => keine passende Klasse gefunden';
        }
        if (!$import['tblPersonV'] && !$import['IsCanceled']) {
            $errors[] = '[Person] - '.$import['PersonAcronym'].' => Vertretung nicht gefunden';
        }
        // möglicherweise können Fächer auch ohne Fach angelegt sein (Bsp.: ESS)
        // "Ak_Fach": "",
        // "Ak_VFach": "GEO",
        // Fach nicht gefunden, soll als Fehler aufgenommen werden, leerer String ist für ein "anlegen" aber ok
        if (!$import['tblSubject'] && $import['Subject']) {
            $errors[] = '[Fach] - '.$import['Subject'].' => Fach nicht gefunden';
        } elseif(!$import['tblSubject'] && !$import['tblSubstituteSubject']){
            $errors[] = '[Fach] - '.($import['Subject']?:'[leer]').' => Fach nicht gefunden';
        }
        if (!$import['tblSubstituteSubject'] && !$import['IsCanceled']) {
            $errors[] = '[Vertretungsfach] - '.$import['SubjectSubstitute'].' => Fach nicht gefunden';
        }
        if (empty($errors)) {
            return false;
        }
        $ErrorList = $import;
        $ErrorList['Error'] = $errors;
        $import = false;
        return $ErrorList;
    }
}