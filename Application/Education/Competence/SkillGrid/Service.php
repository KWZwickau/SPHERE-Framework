<?php

namespace SPHERE\Application\Education\Competence\SkillGrid;

use SPHERE\Application\Education\Competence\ScoreType\ScoreType;
use SPHERE\Application\Education\Competence\ScoreType\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Competence\SkillGrid\Service\Data;
use SPHERE\Application\Education\Competence\SkillGrid\Service\Entity\TblSkill;
use SPHERE\Application\Education\Competence\SkillGrid\Service\Entity\TblSkillArea;
use SPHERE\Application\Education\Competence\SkillGrid\Service\Entity\TblSkillGrid;
use SPHERE\Application\Education\Competence\SkillGrid\Service\Setup;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSupportFocusType;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
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
     * @param TblType $tblSchoolType
     * @param int|null $level
     * @param TblSubject|null $tblSubject
     * @param TblSupportFocusType|null $tblSupportFocusType
     *
     * @return array|false
     */
    public function getSkillGridListBySupportFocusType(
        TblType $tblSchoolType, ?int $level = null, ?TblSubject $tblSubject = null, ?TblSupportFocusType $tblSupportFocusType = null): array|false
    {
        return (new Data($this->getBinding()))->getSkillGridListBySupportFocusType($tblSchoolType, $level, $tblSubject, $tblSupportFocusType);
    }

    /**
     * @param TblType $tblSchoolType
     * @param int|null $level
     * @param TblSubject|null $tblSubjectFilter
     *
     * @return array|TblSkillGrid[]
     */
    public function getAvailableSkillGridList(TblType $tblSchoolType, ?int $level = null, ?TblSubject $tblSubjectFilter = null): array
    {
        $tblSkillGridList = [];
        if ($tblSubjectFilter || $this->getIsHeadmaster()) {
            if ($tblSubjectFilter) {
                $tblSkillGridList = $this->getSkillGridListBy($tblSchoolType, $level, $tblSubjectFilter) ?: [];
            } else {
                $tblSkillGridList = (new Data($this->getBinding()))->getSkillGridListForFilter($tblSchoolType, $level) ?: [];
            }
        } else {
            foreach ($this->getAvailableSubjectList() as $tblSubject) {
                if (($tblTempList = $this->getSkillGridListBy($tblSchoolType, $level, $tblSubject))) {
                    $tblSkillGridList = array_merge($tblSkillGridList, $tblTempList);
                }
            }
        }

        return $tblSkillGridList;
    }

    /**
     * @param TblScoreType $tblScoreType
     *
     * @return bool
     */
    public function getIsScoreTypeUsedInAnySkillGrid(TblScoreType $tblScoreType): bool
    {
        return (new Data($this->getBinding()))->getIsScoreTypeUsedInAnySkillGrid($tblScoreType);
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
     * @param TblSkillGrid $tblSkillGrid
     *
     * @return TblSkillArea[]
     */
    public function getSkillAreaListBySkillGrid(TblSkillGrid $tblSkillGrid): array
    {
        return (new Data($this->getBinding()))->getSkillAreaListBySkillGrid($tblSkillGrid);
    }

    /**
     * @param TblSkillGrid $tblSkillGrid
     *
     * @return TblSkill[]
     */
    public function getSkillListBySkillGrid(TblSkillGrid $tblSkillGrid): array
    {
        return (new Data($this->getBinding()))->getSkillListBySkillGrid($tblSkillGrid);
    }

    /**
     * @param TblSkillArea $tblSkillArea
     *
     * @return TblSkill[]
     */
    public function getSkillListBySkillArea(TblSkillArea $tblSkillArea): array
    {
        return (new Data($this->getBinding()))->getSkillListBySkillArea($tblSkillArea);
    }

    /**
     * @param TblType $tblSchoolType
     * @param int|null $level
     * @param TblSubject|null $tblSubject
     * @param TblCourse|null $tblCourse
     * @param TblSupportFocusType|null $tblSupportFocusType
     *
     * @return TblSkill[]
     */
    public function getSkillListBy(TblType $tblSchoolType, ?int $level = null,
        ?TblSubject $tblSubject = null, ?TblCourse $tblCourse = null, ?TblSupportFocusType $tblSupportFocusType = null): array
    {
        // prüfen: ob es Kompetenzen mit und ohne Förderschwerpunkt gibt
        if ($tblSupportFocusType) {
            $tblSkillGridSupport = $this->getSkillGridListBySupportFocusType($tblSchoolType, $level, $tblSubject, $tblSupportFocusType);
            $tblSkillGridWithoutSupport = $this->getSkillGridListBySupportFocusType($tblSchoolType, $level, $tblSubject);

            // beides vorhanden → keine Kompetenz automatisch liefern → sollen händisch hinzugefügt werden
            if ($tblSkillGridSupport && $tblSkillGridWithoutSupport) {
                return [];
            } elseif ($tblSkillGridWithoutSupport) {
                $tblSkillGridList = $tblSkillGridWithoutSupport;
            } else {
                $tblSkillGridList = $tblSkillGridSupport;
            }
        } else {
            $tblSkillGridList = $this->getSkillGridListBySupportFocusType($tblSchoolType, $level, $tblSubject);
        }

        $tblSkillList = [];
        if ($tblSkillGridList) {
            foreach ($tblSkillGridList as $tblSkillGrid) {
                $tblCourseSkillGrid = $tblSkillGrid->getServiceTblCourse();
                // Anzeige alle Kompetenzraster, die den Bildungsgang haben und alle ohne Bildungsgang
                if ((!$tblCourseSkillGrid || $tblCourseSkillGrid->getId() == $tblCourse?->getId())) {
                    $tblSkillList = array_merge($tblSkillList, $tblSkillGrid->getSkills());
                }
            }
        }

        return $tblSkillList;
    }

    /**
     * @param $id
     *
     * @return false|TblSkill
     */
    public function getSkillById($id): false|TblSkill
    {
        return (new Data($this->getBinding()))->getSkillById($id);
    }

    /**
     * @param TblType $tblSchoolType
     * @param $Filter
     * @param $Data
     * @param TblSkillGrid|null $tblSkillGrid
     *
     * @return IFormInterface|string
     */
    public function updateSkillGrid(TblType $tblSchoolType, $Filter, $Data, ?TblSkillGrid $tblSkillGrid = null): IFormInterface|string
    {
        list($hasErrors, $ErrorList, $Data) = $this->checkInputSkillGrid($Data);

        // Data[Skills][$AreaRanking-$SkillRanking][SkillGrid]
        foreach ($Data['Skills'] as $key => $skillArray) {
            $split = explode('-', $key);
            $areaRanking = $split[0];
            $skillRanking = $split[1];
            // Niveau ohne Kompetenz
            if (empty($skillArray['Skill']) && !empty($skillArray['Level'])) {
                $name = "Data[Skills][$areaRanking-$skillRanking][Skill]";
                $ErrorList[$name] = [
                    'Name' => $name,
                    'Message' => 'Bitte geben Sie eine Kompetenz an'
                ];
                $hasErrors = true;
            }
            // Kompetenzbereich ohne Kompetenz
            if ($skillRanking == 1 && !empty($Data['SkillAreas'][$areaRanking]['Area']) && empty($skillArray['Skill'])) {
                $name = "Data[Skills][$areaRanking-$skillRanking][Skill]";
                $ErrorList[$name] = [
                    'Name' => $name,
                    'Message' => 'Bitte geben Sie eine Kompetenz an'
                ];
                $hasErrors = true;
            }
        }

        if ($hasErrors) {
            return new Well(SkillGrid::useFrontend()->formSkillGrid(false, $tblSchoolType->getId(), $Filter, $tblSkillGrid?->getId(), $Data, $ErrorList));
        }

        $tblSubject = Subject::useService()->getSubjectById($Data['SubjectId']);
        $tblCourse = Course::useService()->getCourseById($Data['CourseId'] ?? 0);
        $tblSupportFocusType = Student::useService()->getSupportFocusTypeById($Data['SupportFocusTypeId']);
        $tblScoreType = $Data['ScoreTypeId'] > 0 ? ScoreType::useService()->getScoreTypeById($Data['ScoreTypeId']) : null;

        $tblSkillAreaListExists = [];
        $tblSkillListExists = [];
        if ($tblSkillGrid) {
            (new Data($this->getBinding()))->updateSkillGrid($tblSkillGrid, $Data['Name'], isset($Data['IsAverage']),
                $Data['Level'], $tblSubject ?: null, $tblCourse ?: null, $tblSupportFocusType ?: null, $tblScoreType ?: null);

//            Debugger::devDump($Data);
//            return '';
//            $this->destroySkillsBySkillGrid($tblSkillGrid);

            $tblSkillAreaListExists = $tblSkillGrid->getSkillAreas();
            $tblSkillListExists = $tblSkillGrid->getSkills();

            $tblSkillGridNew = $tblSkillGrid;
        } else {
            $tblSkillGridNew = (new Data($this->getBinding()))->createSkillGrid($tblSchoolType, $Data['Name'], isset($Data['IsAverage']),
                $Data['Level'], $tblSubject ?: null, $tblCourse ?: null, $tblSupportFocusType ?: null, $tblScoreType ?: null);
        }

        $tblSkillAreaListByAreaRanking = [];
        $skillAreaIdList = [];
        $skillIdList = [];
        foreach ($Data['Skills'] as $key => $skillArray) {
            $split = explode('-', $key);
            $areaRanking = $split[0];
            $skillRanking = $split[1];
            if (!empty($skillArray['Skill'])) {
                if (!isset($tblSkillAreaListByAreaRanking[$areaRanking])) {
                    if (isset($Data['SkillAreas'][$areaRanking]['SkillAreaId'])
                        && ($tblSkillArea = $this->getSkillAreaById($Data['SkillAreas'][$areaRanking]['SkillAreaId']))
                    ) {
                        (new Data($this->getBinding()))->updateSkillArea(
                            $tblSkillArea, empty($Data['SkillAreas'][$areaRanking]['Area']) ? null : $Data['SkillAreas'][$areaRanking]['Area'], $areaRanking);

                        $skillAreaIdList[$tblSkillArea->getId()] = 1;
                    } else {
                        $tblSkillArea = (new Data($this->getBinding()))->createSkillArea(
                            $tblSkillGridNew, empty($Data['SkillAreas'][$areaRanking]['Area']) ? null : $Data['SkillAreas'][$areaRanking]['Area'], $areaRanking);
                    }

                    $tblSkillAreaListByAreaRanking[$areaRanking] = $tblSkillArea;
                }

                if (isset($skillArray['SkillId']) && ($tblSkill = $this->getSkillById($skillArray['SkillId']))) {
                    (new Data($this->getBinding()))->updateSkill($tblSkill, $skillArray['Level'] ?: null, $skillArray['Skill'], $skillRanking);

                    $skillIdList[$tblSkill->getId()] = 1;
                } else {
                    (new Data($this->getBinding()))->createSkill($tblSkillAreaListByAreaRanking[$areaRanking], $skillArray['Level'] ?: null, $skillArray['Skill'], $skillRanking);
                }
            }
        }

        // löschen Kompetenzen
        $destroySkillList = [];
        foreach ($tblSkillListExists as $tblSkillTemp) {
            if (!isset($skillIdList[$tblSkillTemp->getId()])) {
                $destroySkillList[$tblSkillTemp->getId()] = $tblSkillTemp;
            }
        }
        // löschen Kompetenzbereiche
        $destroySkillAreaList = [];
        foreach ($tblSkillAreaListExists as $tblSkillAreaTemp) {
            if (!isset($skillAreaIdList[$tblSkillAreaTemp->getId()])) {
                $destroySkillAreaList[] = $tblSkillAreaTemp;
               if ($tblSkillListTemp = $tblSkillAreaTemp->getSkills()) {
                   foreach ($tblSkillListTemp as $tblSkillTemp) {
                       $destroySkillList[$tblSkillTemp->getId()] = $tblSkillTemp;
                   }
               }
            }
        }
        if ($destroySkillList) {
            (new Data($this->getBinding()))->destroySkillBulkList($destroySkillList);
        }
        if ($destroySkillAreaList) {
            (new Data($this->getBinding()))->destroySkillAreaBulkList($destroySkillAreaList);
        }

        return new Success('Die Daten wurden erfolgreich gespeichert', new \SPHERE\Common\Frontend\Icon\Repository\Success())
            . new Redirect('/Education/Competence/SkillGrid', Redirect::TIMEOUT_SUCCESS, ['SchoolTypeId' => $tblSchoolType->getId(), 'Filter' => $Filter]);
    }

    /**
     * @param $Data
     * @return array
     */
    private function checkInputSkillGrid($Data): array
    {
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
        // Fachlehrer müssen ein Fach auswählen
        if (!$this->getIsHeadmaster()
            && (empty($Data['SubjectId']) || !Subject::useService()->getSubjectById($Data['SubjectId']))
        ) {
            $ErrorList[] = [
                'Name' => 'Data[SubjectId]',
                'Message' => 'Bitte geben Sie ein Fach an'
            ];
            $hasErrors = true;
        }
        return array($hasErrors, $ErrorList, $Data);
    }

    /**
     * @param TblType $tblSchoolType
     * @param TblSkillGrid $tblSkillGrid
     * @param $Filter
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function copySkillGrid(TblType $tblSchoolType, TblSkillGrid $tblSkillGrid, $Filter, $Data): IFormInterface|string
    {
        list($hasErrors, $ErrorList, $Data) = $this->checkInputSkillGrid($Data);

        // extra Prüfung für Schulart
        $tblSchoolTypeCopy = false;
        if (empty($Data['SchoolTypeId']) || !($tblSchoolTypeCopy = Type::useService()->getTypeById($Data['SchoolTypeId']))) {
            $ErrorList[] = [
                'Name' => 'Data[SchoolTypeId]',
                'Message' => 'Bitte geben Sie eine Schulart an'
            ];
            $hasErrors = true;
        }

        if ($hasErrors) {
            $form = SkillGrid::useFrontend()->formCopySkillGrid(false, $tblSchoolType->getId(), $Filter, $tblSkillGrid->getId(), $ErrorList);

            return SkillGrid::useFrontend()->loadCopySkillGridContent($form, $tblSkillGrid->getId());
        }

        $tblSubject = Subject::useService()->getSubjectById($Data['SubjectId']);
        $tblSupportFocusType = Student::useService()->getSupportFocusTypeById($Data['SupportFocusTypeId']);
        $tblScoreType = $Data['ScoreTypeId'] > 0 ? ScoreType::useService()->getScoreTypeById($Data['ScoreTypeId']) : null;

        if ($tblSchoolTypeCopy
            && ($tblSkillGridNew = (new Data($this->getBinding()))->createSkillGrid(
                $tblSchoolTypeCopy, $Data['Name'], isset($Data['IsAverage']),
                $Data['Level'], $tblSubject ?: null, null, $tblSupportFocusType ?: null, $tblScoreType ?: null))
            && isset($Data['SkillAreaList'])
        ) {
            foreach($Data['SkillAreaList'] as $skillAreaId => $value)
            {
                if (($tblSkillArea = $this->getSkillAreaById($skillAreaId))) {
                    $tblSkillAreaNew = (new Data($this->getBinding()))->createSkillArea($tblSkillGridNew, $tblSkillArea->getName(), $tblSkillArea->getSortOrder());
                    foreach($tblSkillArea->getSkills() as $tblSkill) {
                        (new Data($this->getBinding()))->createSkill($tblSkillAreaNew, $tblSkill->getLevel(), $tblSkill->getSkill(), $tblSkill->getSortOrder());
                    }
                }
            }
        }

        return new Success('Die Daten wurden erfolgreich gespeichert', new \SPHERE\Common\Frontend\Icon\Repository\Success())
            . new Redirect('/Education/Competence/SkillGrid', Redirect::TIMEOUT_SUCCESS, ['SchoolTypeId' => $tblSchoolType->getId(), 'Filter' => $Filter]);
    }

    /**
     * @param TblType $tblSchoolType
     * @param $data
     *
     * @return void
     */
    public function insertSkillGrid(TblType $tblSchoolType, $data): void
    {
        foreach ($data as $skillGrid) {
            $tblSkillGrid = (new Data($this->getBinding()))->createSkillGrid($tblSchoolType, $skillGrid['name'], $skillGrid['isAverage'],
                $skillGrid['level'], $skillGrid['tblSubject'], $skillGrid['tblCourse'], $skillGrid['tblSupportFocusType'], $skillGrid['tblScoreType']);

            foreach ($skillGrid['SkillAreas'] as $skillAreas) {
                $tblSkillArea = (new Data($this->getBinding()))->createSkillArea($tblSkillGrid, $skillAreas['name'], $skillAreas['sortOrder']);

                foreach ($skillAreas['Skills'] as $skill) {
                    (new Data($this->getBinding()))->createSkill($tblSkillArea, $skill['level'], $skill['skill'], $skill['sortOrder']);
                }
            }
        }
    }

    /**
     * @param TblSkillGrid $tblSkillGrid
     *
     * @return bool
     */
    public function destroySkillGrid(TblSkillGrid $tblSkillGrid): bool
    {
        $this->destroySkillsBySkillGrid($tblSkillGrid);

        return (new Data($this->getBinding()))->destroySkillGrid($tblSkillGrid);
    }

    /**
     * @param TblSkillGrid $tblSkillGrid
     *
     * @return void
     */
    public function destroySkillsBySkillGrid(TblSkillGrid $tblSkillGrid): void
    {
        $destroySkillAreaList = [];
        $destroySkillList = [];

        foreach ($tblSkillGrid->getSkillAreas() as $tblSkillArea) {
            $destroySkillAreaList[] = $tblSkillArea;
        }

        foreach ($tblSkillGrid->getSkills() as $tblSkill) {
            $destroySkillList[] = $tblSkill;
        }

        (new Data($this->getBinding()))->destroySkillBulkList($destroySkillList);
        (new Data($this->getBinding()))->destroySkillAreaBulkList($destroySkillAreaList);
    }

    /**
     * @return bool
     */
    public function getIsHeadmaster(): bool
    {
        return Access::useService()->hasAuthorization('/Education/Competence/ScoreType');
    }

    /**
     * @return array
     */
    public function getAvailableSubjectList(): array
    {
        $hasRightHeadmaster = $this->getIsHeadmaster();
        $tblSubjectList = [];
        if ($hasRightHeadmaster) {
            $tblSubjectList = Subject::useService()->getSubjectAll() ?: [];
        } elseif (($tblPerson = Account::useService()->getPersonByLogin())) {
            $tblSubjectList = DivisionCourse::useService()->getSubjectListByTeacherAndDate($tblPerson);
        }

        return $tblSubjectList;
    }

    /**
     * aktuell sind erstmal nur allgemeinbildende Schulen vorgesehen
     *
     * @return array
     */
    public function getAvailableSchoolTypeList(): array
    {
        // aktuell sind erstmal nur allgemeinbildende Schulen vorgesehen
        return School::useService()->getConsumerSchoolTypeCommonAll() ?: [];
    }

    /**
     * @return bool
     */
    public function getIsConsumerAvailableForCompetence(): bool
    {
        return ($tblRole = Access::useService()->getRoleByName('Bildung: Kompetenzbewertung (Schulleitung)'))
            && ($tblAccount = Account::useService()->getAccountBySession())
            && ($tblConsumer = $tblAccount->getServiceTblConsumer())
            && (Access::useService()->getRoleConsumerBy($tblRole, $tblConsumer));
    }
}