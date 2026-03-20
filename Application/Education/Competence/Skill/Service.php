<?php

namespace SPHERE\Application\Education\Competence\Skill;

use SPHERE\Application\Education\Competence\Skill\Service\Data;
use SPHERE\Application\Education\Competence\Skill\Service\Entity\TblSkillArea;
use SPHERE\Application\Education\Competence\Skill\Service\Entity\TblSkillGrid;
use SPHERE\Application\Education\Competence\Skill\Service\Setup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Extension\Repository\Debugger;

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
        $Protocol = '';
        if (!$withData) {
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
     * @return TblSkillGrid|false
     */
    public function getSkillGridById($id): TblSkillGrid|false
    {
        return (new Data($this->getBinding()))->getSkillGridById($id);
    }

    /**
     * @param TblType $tblSchoolType
     * @param int|null $level
     * @param TblSubject|null $tblSubject
     *
     * @return TblSkillGrid[]|false
     */
    public function getSkillGridListBy(TblType $tblSchoolType, ?int $level = null, ?TblSubject $tblSubject = null): array|false
    {
        return (new Data($this->getBinding()))->getSkillGridListBy($tblSchoolType, $level, $tblSubject);
    }

    /**
     * @param $id
     *
     * @return TblSkillArea|false
     */
    public function getSkillAreaById($id): TblSkillArea|false
    {
        return (new Data($this->getBinding()))->getSkillAreaById($id);
    }

    /**
     * @param IFormInterface $form
     * @param TblType $tblSchoolType
     * @param $Filter
     * @param $Data
     * @param TblSkillGrid|null $tblSkillGrid
     *
     * @return IFormInterface|string
     */
    public function updateSkillGrid(IFormInterface $form, TblType $tblSchoolType, $Filter, $Data, ?TblSkillGrid $tblSkillGrid = null): IFormInterface|string
    {
        if ($Data === null) {
            return $form;
        }

        // TOdo check pflicht imputs
        $hasErrors = false;
        $ErrorList = [];
        if (empty($Data['Name'])) {
            $ErrorList[] = [
                'Name' => 'Data[Name]',
                'Message' => 'Bitte geben Sie einen Namen an'
            ];
            $hasErrors = true;
        }
        if (empty($Data['Level'])) {
            $ErrorList[] = [
                'Name' => 'Data[Level]',
                'Message' => 'Bitte geben Sie eine Klassenstufe an'
            ];
            $hasErrors = true;
        }
        foreach ($Data['SkillAreas'] as $areaRanking => $areaArray) {
            foreach ($areaArray['Skills'] as $skillRanking => $skillArray) {
                if (empty($skillArray['Skill']) && (!empty($areaArray['Area']))) {
                    $ErrorList[] = [
                        'Name' => "Data[SkillAreas][$areaRanking][Skills][$skillRanking][Skill]",
                        'Message' => 'Bitte geben Sie eine Kompetenz an'
                    ];
                    $hasErrors = true;
                }
            }
        }

        if ($hasErrors) {
            return Skill::useFrontend()->formSkillGrid($tblSchoolType->getId(), $Filter, $tblSkillGrid?->getId(), false, $Data, $ErrorList);
        }

        $tblSubject = Subject::useService()->getSubjectById($Data['SubjectId']);
        $tblCourse = Course::useService()->getCourseById($Data['CourseId']);
        $tblSupportFocusType = Student::useService()->getSupportFocusTypeById($Data['SupportFocusTypeId']);

//        Debugger::devDump($Data);
//        exit;

        $tblSkillGrid = (new Data($this->getBinding()))->createSkillGrid($tblSchoolType, $Data['Name'], isset($Data['IsAverage']),
            $Data['Level'], $tblSubject ?: null, $tblCourse ?: null, $tblSupportFocusType ?: null);

        foreach ($Data['SkillAreas'] as $areaRanking => $areaArray) {
            $tblSkillArea = null;
            foreach ($areaArray['Skills'] as $skillRanking => $skillArray) {
                if (!empty($skillArray['Skill'])) {
                    if (!$tblSkillArea) {
                        $tblSkillArea = (new Data($this->getBinding()))->createSkillArea($tblSkillGrid,
                            empty($areaArray['Area']) ? null : $areaArray['Area'], $areaRanking);
                    }
                    (new Data($this->getBinding()))->createSkill($tblSkillArea, $skillArray['Level'], $skillArray['Skill'], $skillRanking);
                }
            }
        }

        return new Success('Die Daten wurden erfolgreich gespeichert', new \SPHERE\Common\Frontend\Icon\Repository\Success())
            . new Redirect('/Education/Competence/Skill', Redirect::TIMEOUT_SUCCESS, ['SchoolTypeId' => $tblSchoolType->getId(), 'Filter' => $Filter]);
    }
}