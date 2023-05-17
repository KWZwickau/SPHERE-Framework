<?php
namespace SPHERE\Application\Transfer\Indiware\Import;

use DateTime;
use MOC\V\Component\Document\Component\Bridge\Repository\UniversalXml;
use MOC\V\Component\Document\Document;
use MOC\V\Component\Document\Exception\DocumentTypeException as DocumentTypeException;
use MOC\V\Component\Document\Vendor\UniversalXml\Source\Node;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetableNode;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable as TimetableClassRegister;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\Transfer\Education\Education;
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
    /* @var TblDivision[] $CourseList */
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
                $DateString = utf8_encode($Datum->getContent());
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
                            $item['Subject'] = utf8_encode($plFach->getContent());
                        } else {
                            $item['Subject'] = '';
                        }
                        if(($plKlasse = $Pl->getChild('pl_klasse'))){
                            $item['Course'] = utf8_encode($plKlasse->getContent());
                        } else {
                            $item['Course'] = '';
                        }
                        if(($plLehrer = $Pl->getChild('pl_lehrer'))){
                            $item['Person'] = utf8_encode($plLehrer->getContent());
                        } else {
                            $item['Person'] = '';
                        }
                        if(($plRaum = $Pl->getChild('pl_raum'))){
                            $item['Room'] = utf8_encode($plRaum->getContent());
                        } else {
                            $item['Room'] = '';
                        }
                        if(($plGruppe = $Pl->getChild('pl_gruppe'))){
                            $item['SubjectGroup'] = utf8_encode($plGruppe->getContent());
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

                        array_push($ImportList, $item);
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
                    $Row['tblSubstituteSubject'] = Subject::useService()->getSubjectByVariantAcronym($Row['Subject']);
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

                        if ($tblDivisionCourse) {
                            $Row['tblCourse'] = $tblDivisionCourse;
                            $Row['CourseId'] = $tblDivisionCourse;
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
        if(($tblTimeTableList = TimetableClassRegister::useService()->getTimetableListByDateTime(new DateTime()))){
            foreach($tblTimeTableList as $tblTimeTable){
                if(($tblTimeTableNodeList = TimetableClassRegister::useService()->getTimetableNodeListByTimetable($tblTimeTable))){
                    foreach($tblTimeTableNodeList as $tblTimeTableNode){
                        if(key_exists($tblTimeTableNode->getServiceTblCourse()->getId(), $tblCourseList)){
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
            for($HourCount = 1; $HourCount <= 10; $HourCount++){
                foreach($tblCourseList as $CourseId => $tblCourse){
                    if(isset($TimeTableList[$DayCount][$HourCount][$CourseId])
                    && isset($ReplaceList[$DayCount][$HourCount][$CourseId])){
                        // Vergleich der 2 Unterrichtseinträge (beides Listen)
                        /** @var TblTimetableNode $tblTimeTableNode */
                        foreach($TimeTableList[$DayCount][$HourCount][$CourseId] as $tblTimeTableNode){
                            foreach($ReplaceList[$DayCount][$HourCount][$CourseId] as &$Row) {
                                if($Row['Date'] == $DayList[$tblTimeTableNode->getDay()]
                                && $Row['Room'] == $tblTimeTableNode->getRoom()
                                && $Row['SubjectGroup'] == $tblTimeTableNode->getSubjectGroup()
                                && $Row['tblSubstituteSubject']->getId() == $tblTimeTableNode->getServiceTblSubject()->getId()
                                && $Row['tblCourse']->getId() == $tblTimeTableNode->getServiceTblCourse()->getId()
                                && $Row['tblPerson']->getId() == $tblTimeTableNode->getServiceTblPerson()->getId()){
                                    $Row['found'] = true;
                                }
                                // Vorhandenes Fach anfügen, wenn eindeutig
                                if(count($TimeTableList[$DayCount][$HourCount][$CourseId]) == 1
                                && count($ReplaceList[$DayCount][$HourCount][$CourseId]) == 1){
                                    $Row['tblSubject'] = $tblTimeTableNode->getServiceTblSubject();
                                }
                            }
                        }

                        foreach($ReplaceList[$DayCount][$HourCount][$CourseId] as $Row) {
                            if(!isset($Row['found'])){
                                $DifferenceList[] = $Row;
                            }
                        }

                    } elseif(isset($TimeTableList[$DayCount][$HourCount][$CourseId])){
                        /** @var TblTimetableNode $tblTimeTableNode */
                        foreach($TimeTableList[$DayCount][$HourCount][$CourseId] as $tblTimeTableNode){
                            $Row = array();
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
                    if(($tblTimetableReplacementList = TimetableClassRegister::useService()->getTimetableReplacementByTime($Date, null, $tblCourse))){
                        foreach($tblTimetableReplacementList as $tblTimetableReplacement){
                            $removeList[] = $tblTimetableReplacement;
                        }
                    }
                }
            }
        }
        if(!empty($removeList)) {
            TimetableClassRegister::useService()->destroyTimetableReplacementBulk($removeList);
        }
    }

    public function importTimetableReplacementBulk($importList)
    {

        TimetableClassRegister::useService()->createTimetableReplacementBulk($importList);
    }
}