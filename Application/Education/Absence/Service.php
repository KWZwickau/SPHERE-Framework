<?php

namespace SPHERE\Application\Education\Absence;

use DateTime;
use SPHERE\Application\Education\Absence\Service\Data;
use SPHERE\Application\Education\Absence\Service\Entity\TblAbsence;
use SPHERE\Application\Education\Absence\Service\Entity\TblAbsenceLesson;
use SPHERE\Application\Education\Absence\Service\Setup;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Binding\AbstractService;

class Service extends AbstractService
{
    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8): string
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
     * @param TblYear $tblYear
     *
     * @return array
     */
    public function migrateYear(TblYear $tblYear): array
    {
        return (new Data($this->getBinding()))->migrateYear($tblYear);
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool $isForced
     *
     * @return false|TblAbsence[]
     */
    public function getAbsenceAllByPerson(TblPerson $tblPerson, bool $isForced = false)
    {
        return (new Data($this->getBinding()))->getAbsenceAllByPerson($tblPerson, $isForced);
    }

    /**
     * @param $Id
     *
     * @return false|TblAbsence
     */
    public function getAbsenceById($Id)
    {
        return (new Data($this->getBinding()))->getAbsenceById($Id);
    }

    /**
     * @return false|TblAbsence[]
     */
    public function getAbsenceAll()
    {
        return (new Data($this->getBinding()))->getAbsenceAll();
    }

    /**
     * @param DateTime $fromDate
     * @param DateTime $toDate
     *
     * @return TblAbsence[]|bool
     */
    public function getAbsenceAllBetween(DateTime $fromDate, DateTime $toDate)
    {
        return (new Data($this->getBinding()))->getAbsenceAllBetween($fromDate, $toDate);
    }

    /**
     * @param TblAbsence $tblAbsence
     *
     * @return false|TblAbsenceLesson[]
     */
    public function getAbsenceLessonAllByAbsence(TblAbsence $tblAbsence)
    {
        return (new Data($this->getBinding()))->getAbsenceLessonAllByAbsence($tblAbsence);
    }
}