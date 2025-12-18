<?php

namespace SPHERE\Application\Education\ClassRegister\ScheduleTime;

use DateTime;
use SPHERE\Application\Education\ClassRegister\ScheduleTime\Service\Data;
use SPHERE\Application\Education\ClassRegister\ScheduleTime\Service\Entity\TblScheduleTime;
use SPHERE\Application\Education\ClassRegister\ScheduleTime\Service\Entity\TblScheduleTimeSchoolType;
use SPHERE\Application\Education\ClassRegister\ScheduleTime\Service\Entity\TblScheduleTimeSlot;
use SPHERE\Application\Education\ClassRegister\ScheduleTime\Service\Setup;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\System\Database\Binding\AbstractService;

class Service extends AbstractService
{
    /**
     * @param $doSimulation
     * @param $withData
     * @param $UTF8
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
     * @param $id
     *
     * @return TblScheduleTime|bool
     */
    public function getScheduleTimeById($id): TblScheduleTime|bool
    {
        return (new Data($this->getBinding()))->getScheduleTimeById($id);
    }

    /**
     * @return TblScheduleTime[]
     */
    public function getScheduleTimeAll(): array
    {
        return (new Data($this->getBinding()))->getScheduleTimeAll();
    }

    /**
     * @param TblScheduleTime $tblScheduleTime
     *
     * @return TblType[]
     */
    public function getSchoolTypesByScheduleTime(TblScheduleTime $tblScheduleTime): array
    {
        $list = [];
        foreach ((new Data($this->getBinding()))->getScheduleTimeSchoolTypesByScheduleTime($tblScheduleTime) as $tblScheduleTimeSchoolType) {
            if (($tblSchoolType = $tblScheduleTimeSchoolType->getServiceTblSchoolType())) {
                $list[$tblSchoolType->getId()] = $tblSchoolType;
            }
        }

        return $list;
    }

    /**
     * @param TblScheduleTime $tblScheduleTime
     *
     * @return string
     */
    public function getDisplaySchoolTypesByScheduleTime(TblScheduleTime $tblScheduleTime): string
    {
        $list = [];
        foreach ((new Data($this->getBinding()))->getScheduleTimeSchoolTypesByScheduleTime($tblScheduleTime) as $item) {
            if (($tblSchoolType = $item->getServiceTblSchoolType())) {
                $list[$tblSchoolType->getId()] = $tblSchoolType->getShortName() ?: $tblSchoolType->getName();
            }
        }

        return implode(', ', $list);
    }

    /**
     * @param TblScheduleTime $tblScheduleTime
     *
     * @return TblScheduleTimeSchoolType[]
     */
    public function getScheduleTimeSchoolTypesByScheduleTime(TblScheduleTime $tblScheduleTime): array
    {
        $list = [];
        foreach ((new Data($this->getBinding()))->getScheduleTimeSchoolTypesByScheduleTime($tblScheduleTime) as $tblScheduleTimeSchoolType) {
            if (($tblSchoolType = $tblScheduleTimeSchoolType->getServiceTblSchoolType())) {
                $list[$tblSchoolType->getId()] = $tblScheduleTimeSchoolType;
            }
        }

        return $list;
    }

    /**
     * @param TblScheduleTime $tblScheduleTime
     * @param bool $withLessonAsKey
     *
     * @return TblScheduleTimeSlot[]
     */
    public function getScheduleTimeSlotsByScheduleTime(TblScheduleTime $tblScheduleTime, bool $withLessonAsKey = false): array
    {
        $list = (new Data($this->getBinding()))->getScheduleTimeSlotsByScheduleTime($tblScheduleTime);

        if (!$withLessonAsKey) {
            return $list;
        }

        $result = [];
        foreach ($list as $item) {
            $result[$item->getLesson()] = $item;
        }

        return $result;
    }

    /**
     * @param TblType $tblSchoolType
     * @param int $secondaryLevel
     *
     * @return array
     */
    public function getSlotsBySchoolType(TblType $tblSchoolType, int $secondaryLevel): array
    {
        if (!($tblScheduleTime = (new Data($this->getBinding()))->getScheduleTimeBySchoolTypeAndSecondaryLevel($tblSchoolType, $secondaryLevel))) {
            return [];
        }

        $result = [];
        foreach($this->getScheduleTimeSlotsByScheduleTime($tblScheduleTime) as $tblScheduleTimeSlot) {
            $result[$tblScheduleTimeSlot->getLesson()] = [
                'Lesson' => $tblScheduleTimeSlot->getLesson(),
                'StartTime' => $tblScheduleTimeSlot->getStartTime(),
                'EndTime' => $tblScheduleTimeSlot->getEndTime(),
            ];
        }

        return $result;
    }

    /**
     * @param $Data
     * @param TblScheduleTime|null $tblScheduleTime
     *
     * @return false|Form
     */
    public function checkFormScheduleTime(
        $Data,
        TblScheduleTime $tblScheduleTime = null
    ): Form|bool {
        $error = false;

        $form = ScheduleTime::useFrontend()->formScheduleTime($tblScheduleTime?->getId());

        if (isset($Data['Name']) && empty($Data['Name'])) {
            $form->setError('Data[Name]', 'Bitte geben Sie einen Namen an');
            $error = true;
        }

        if (isset($Data['Times'])) {
            foreach ($Data['Times'] as $lesson => $time) {
                $startTime = trim($time['StartTime']);
                $endTime = trim($time['EndTime']);

                if (!empty($startTime)) {
                    if (!$this->validTime($startTime)) {
                        $form->setError("Data[Times][$lesson][StartTime]", 'Bitte geben Sie eine gültige Uhrzeit an');
                        $error = true;
                    }
                } elseif (!empty($endTime)) {
                    $form->setError("Data[Times][$lesson][StartTime]", 'Bitte geben Sie eine Startzeit an');
                    $error = true;
                }

                if (!empty($endTime)) {
                    if (!$this->validTime($endTime)) {
                        $form->setError("Data[Times][$lesson][EndTime]", 'Bitte geben Sie eine gültige Uhrzeit an');
                        $error = true;
                    }
                } elseif (!empty($startTime)) {
                    $form->setError("Data[Times][$lesson][EndTime]", 'Bitte geben Sie eine Endzeit an');
                    $error = true;
                }
            }
        }

        return $error ? $form : false;
    }

    private function validTime(string $time): bool
    {
        $time = $this->normalizeTime($time);

        $dt = DateTime::createFromFormat('H:i', $time);

        return $dt && $dt->format('H:i') === $time;
    }

    private function normalizeTime(string $time): string
    {
        return strlen($time) == 4 ? str_pad($time, 5, '0', STR_PAD_LEFT) : $time;
    }

    /**
     * @param $Data
     *
     * @return void
     */
    public function createScheduleTime($Data): void
    {
        // Zeitplan hinzufügen
        $tblScheduleTime = (new Data($this->getBinding()))->createScheduleTime($Data['Name'], $Data['SecondaryLevel']);

        // Schularten hinzufügen
        if (isset($Data['SchoolTypes'])) {
            foreach ($Data['SchoolTypes'] as $schoolTypeId => $value) {
                if (($tblSchoolType = Type::useService()->getTypeById($schoolTypeId))) {
                    (new Data($this->getBinding()))->createScheduleTimeSchoolType($tblScheduleTime, $tblSchoolType);
                }
            }
        }

        // nur eingetragene Zeiten hinzufügen
        if (isset($Data['Times'])) {
            foreach ($Data['Times'] as $lesson => $time) {
                $startTime = $this->normalizeTime(trim($time['StartTime']));
                $endTime = $this->normalizeTime(trim($time['EndTime']));

                if (!empty($startTime) && !empty($endTime)) {
                    (new Data($this->getBinding()))->createScheduleTimeSlot($tblScheduleTime, $lesson, $startTime, $endTime);
                }
            }
        }
    }

    /**
     * @param TblScheduleTime $tblScheduleTime
     * @param $Data
     *
     * @return void
     */
    public function updateScheduleTime(TblScheduleTime $tblScheduleTime, $Data): void
    {
        // Zeitplan bearbeiten
        (new Data($this->getBinding()))->updateScheduleTime($tblScheduleTime, $Data['Name'], $Data['SecondaryLevel']);

        $tblScheduleTimeSchoolTypes = $this->getScheduleTimeSchoolTypesByScheduleTime($tblScheduleTime);
        // neue Schularten hinzufügen
        if (isset($Data['SchoolTypes'])) {
            foreach ($Data['SchoolTypes'] as $schoolTypeId => $value) {
                if (!isset($tblScheduleTimeSchoolTypes[$schoolTypeId])
                    && ($tblSchoolType = Type::useService()->getTypeById($schoolTypeId))
                ) {
                    (new Data($this->getBinding()))->createScheduleTimeSchoolType($tblScheduleTime, $tblSchoolType);
                }
            }
        }
        // alte Schularten löschen
        $deleteList = [];
        foreach ($tblScheduleTimeSchoolTypes as $schoolTypeId => $tblScheduleTimeSchoolType) {
            if (!isset($Data['SchoolTypes'][$schoolTypeId])) {
                $deleteList[] = $tblScheduleTimeSchoolType;
            }
        }

        $tblScheduleTimeSlots = $this->getScheduleTimeSlotsByScheduleTime($tblScheduleTime, true);
        // Zeiten hinzufügen oder bearbeiten
        if (isset($Data['Times'])) {
            foreach ($Data['Times'] as $lesson => $time) {
                $startTime = $this->normalizeTime(trim($time['StartTime']));
                $endTime = $this->normalizeTime(trim($time['EndTime']));

                if (!empty($startTime) && !empty($endTime)) {
                    if (!isset($tblScheduleTimeSlots[$lesson])) {
                        (new Data($this->getBinding()))->createScheduleTimeSlot($tblScheduleTime, $lesson, $startTime, $endTime);
                    } else {
                        (new Data($this->getBinding()))->updateScheduleTimeSlot($tblScheduleTimeSlots[$lesson], $startTime, $endTime);
                    }
                }
            }
        }
        // Zeiten löschen
        foreach ($tblScheduleTimeSlots as $lesson => $tblScheduleTimeSlot) {
            if (empty($Data['Times'][$lesson]['StartTime']) || empty($Data['Times'][$lesson]['EndTime'])) {
                $deleteList[] = $tblScheduleTimeSlot;
            }
        }

        (new Data($this->getBinding()))->deleteEntityListBulk($deleteList);
    }

    /**
     * @param TblScheduleTime $tblScheduleTime
     *
     * @return void
     */
    public function deleteScheduleTime(TblScheduleTime $tblScheduleTime): void
    {
        $deleteList = [];
        // Schularten löschen
        $deleteList = array_merge($deleteList, $this->getScheduleTimeSchoolTypesByScheduleTime($tblScheduleTime));
        // Zeiten löschen
        $deleteList = array_merge($deleteList, $this->getScheduleTimeSlotsByScheduleTime($tblScheduleTime));
        (new Data($this->getBinding()))->deleteEntityListBulk($deleteList);

        // Zeitplan löschen
        (new Data($this->getBinding()))->deleteScheduleTime($tblScheduleTime);
    }
}