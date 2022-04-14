<?php
namespace SPHERE\Application\Education\ClassRegister\Timetable;

use DateTime;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Data;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetable;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetableReplacement;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Setup;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\ClassRegister\Timetable
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {

        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @return TblTimetable|null
     * @throws \Exception
     */
    public function getTimetableById($Id)
    {

        return (new Data($this->getBinding()))->getTimetableById($Id);
    }

    /**
     * @return TblTimetable[]|null
     */
    public function getTimetableAll()
    {

        return (new Data($this->getBinding()))->getTimetableAll();
    }

    /**
     * @param string $Name
     * @param DateTime $DateFrom
     * @param DateTime $DateTo
     * @return TblTimetable|null
     * @throws \Exception
     */
    public function getTimetableByNameAndTime(string $Name, DateTime $DateFrom, DateTime $DateTo)
    {
        return (new Data($this->getBinding()))->getTimetableByNameAndTime($Name, $DateFrom, $DateTo);
    }

    /**
     * @param TblTimetable $tblTimetable
     * @return mixed
     */
    public function getTimetableNodeListByTimetable(TblTimetable $tblTimetable)
    {

        return (new Data($this->getBinding()))->getTimetableNodeListByTimetable($tblTimetable);
    }

    /**
     * @param TblTimetable $tblTimetable
     * @return mixed
     */
    public function getTimetableWeekListByTimetable(TblTimetable $tblTimetable)
    {

        return (new Data($this->getBinding()))->getTimetableWeekListByTimetable($tblTimetable);
    }

    /**
     * @param DateTime $Date
     * @param $tblPerson
     * @param $tblCourse
     * @param $Hour
     * @return TblTimetableReplacement[]|null
     */
    public function getTimetableReplacementByTime(DateTime $Date, $tblPerson = null, $tblCourse = null, $Hour = null)
    {

        return (new Data($this->getBinding()))->getTimetableReplacementByTime($Date, $tblPerson, $tblCourse, $Hour);
    }

    /**
     * @param DateTime $fromDate
     * @param DateTime $toDate
     *
     * @return TblTimetableReplacement[]|bool
     */
    public function getTimetableReplacementByDate(DateTime $fromDate, DateTime $toDate)
    {

        return (new Data($this->getBinding()))->getTimetableReplacementByDate($fromDate, $toDate);
    }

    /**
     * @param string $Name
     * @param string $Description
     * @param DateTime $DateFrom
     * @param DateTime $DateTo
     * @return TblTimetable|null
     */
    public function createTimetable(string $Name, string $Description, DateTime $DateFrom, DateTime $DateTo): ?TblTimetable
    {

        return (new Data($this->getBinding()))->createTimetable($Name, $Description, $DateFrom, $DateTo);
    }

    /**
     * @param TblTimetable $tblTimetable
     * @param array $ImportList
     * required ArrayKeys
     * [Hour]
     * [Day]
     * [Week]
     * [Room]
     * [SubjectGroup]
     * [Level]
     * [tblCourse]
     * [tblSubject]
     * [tblPerson]
     *
     * @return bool
     */
    public function createTimetableNodeBulk(TblTimetable $tblTimetable, $ImportList)
    {

        return (new Data($this->getBinding()))->createTimetableNodeBulk($tblTimetable, $ImportList);
    }

    /**
     * @param TblTimetable $tblTimetable
     * @param array $ImportList
     * required ArrayKeys
     * [number]
     * [week]
     * [date]
     *
     * @return bool
     */
    public function createTimetableWeekBulk(TblTimetable $tblTimetable, $ImportList)
    {

        return (new Data($this->getBinding()))->createTimetableWeekBulk($tblTimetable, $ImportList);
    }

    /**
     * @param array $ImportList
     * required ArrayKeys
     * [hour]
     * [room]
     * [subjectGroup]
     * [Date]
     * [tblSubject]
     * [tblCourse]
     * [tblPerson]
     *
     * @return bool
     */
    public function createTimetableReplacementBulk($ImportList)
    {

        return (new Data($this->getBinding()))->createTimetableReplacementBulk($ImportList);
    }

    /**
     * @param TblTimetable $tblTimetable
     * @return bool
     */
    public function removeTimetable(TblTimetable $tblTimetable)
    {

        if(($tblTimetableNodeList = $this->getTimetableNodeListByTimetable($tblTimetable))){
            (new Data($this->getBinding()))->removeTimetableNodeList($tblTimetableNodeList);
        }
        if(($tblTimetableWeekList = $this->getTimetableWeekListByTimetable($tblTimetable))){
            (new Data($this->getBinding()))->removeTimetableWeekList($tblTimetableWeekList);
        }
        return (new Data($this->getBinding()))->removeTimetable($tblTimetable);
    }

    /**
     * @return bool
     */
    public function destroyTimetableAllBulk(): bool
    {

        return (new Data($this->getBinding()))->destroyTimetableAllBulk();
    }

    /**
     * @return bool
     */
    public function destroyTimetableReplacementBulk($RemoveList): bool
    {

        return (new Data($this->getBinding()))->destroyTimetableReplacementBulk($RemoveList);
    }
}
