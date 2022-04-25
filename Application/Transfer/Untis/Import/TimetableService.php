<?php
namespace SPHERE\Application\Transfer\Untis\Import;

use DateTime;
use MOC\V\Component\Document\Exception\DocumentTypeException as DocumentTypeException;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable as TimetableClassRegister;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class TimetableService
 * @package SPHERE\Application\Transfer\Untis\Import
 */
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
        if (null === $File || $File->getError() || strtoupper($File->getClientOriginalExtension()) !== 'TXT') {
            $Form->setError('File', 'Wählen Sie eine Datei aus');
            $IsError = true;
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
            $DateTimeFrom = new DateTime($DateFrom);
            $DateTimeTo = new DateTime($DateTo);
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
            return (new Timetable())->frontendImportTimetable($File, $Data);
        }
        return new Danger('File nicht gefunden');
    }

    /**
     * @param string $Name
     * @param string $Description
     * @param DateTime $DateFrom
     * @param DateTime $DateTo
     * @param array $ImportList
     * @return bool
     */
    public function importTimetable(string $Name, string $Description, DateTime $DateFrom, DateTime $DateTo, $ImportList)
    {

        // insert
        $tblTimetable = TimetableClassRegister::useService()->createTimetable($Name, $Description, $DateFrom, $DateTo);
        if($tblTimetable){
//            if(!empty($WeekImport)){
//                TimetableClassRegister::useService()->createTimetableWeekBulk($tblTimetable, $WeekImport);
//            }
            return TimetableClassRegister::useService()->createTimetableNodeBulk($tblTimetable, $ImportList);
        }
        return false;
    }
}