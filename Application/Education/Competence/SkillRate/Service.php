<?php

namespace SPHERE\Application\Education\Competence\SkillRate;

use DateTime;
use NumberFormatter;
use SPHERE\Application\Api\Education\Competence\ApiSkillRate;
use SPHERE\Application\Education\Competence\ScoreType\ScoreType;
use SPHERE\Application\Education\Competence\SkillGrid\Service\Entity\TblSkill;
use SPHERE\Application\Education\Competence\SkillGrid\SkillGrid;
use SPHERE\Application\Education\Competence\SkillRate\Service\Data;
use SPHERE\Application\Education\Competence\SkillRate\Service\Entity\TblStudentSkill;
use SPHERE\Application\Education\Competence\SkillRate\Service\Entity\TblStudentSkillRate;
use SPHERE\Application\Education\Competence\SkillRate\Service\Setup;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Extension\Repository\Sorter;

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
     * @return TblStudentSkill|false
     */
    public function getStudentSkillById($id): false|TblStudentSkill
    {
        return (new Data($this->getBinding()))->getStudentSkillById($id);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSkill $tblSkill
     *
     * @return false|TblStudentSkill
     */
    public function getStudentSkillBy(TblPerson $tblPerson, TblYear $tblYear, TblSkill $tblSkill): false|TblStudentSkill
    {
        return (new Data($this->getBinding()))->getStudentSkillBy($tblPerson, $tblYear, $tblSkill);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSubject|null $tblSubject
     * @param string $skillName
     *
     * @return false|TblStudentSkill
     */
    public function getStudentSkillBySkillName(TblPerson $tblPerson, TblYear $tblYear, ?TblSubject $tblSubject, string $skillName): false|TblStudentSkill
    {
        return (new Data($this->getBinding()))->getStudentSkillBySkillName($tblPerson, $tblYear, $tblSubject, $skillName);
    }
    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSkill $tblSkill
     *
     * @return TblStudentSkill|TblSkill|false
     */
    public function getVirtualStudentSkillBySkillName(TblPerson $tblPerson, TblYear $tblYear, TblSkill $tblSkill)
    : TblStudentSkill | TblSkill | false
    {
        $tblSubject = $tblSkill->getServiceTblSubject();

        // 1. Fall Schüler hat für Kompetenz bereits ein TblStudentSkill (wurde schon einmal bewertet)
        if (($tblStudentSkill = $this->getStudentSkillBy($tblPerson, $tblYear, $tblSkill))
            || ($tblStudentSkill = $this->getStudentSkillBySkillName(
                $tblPerson, $tblYear, $tblSubject, $tblSkill->getSkill()))
        ) {
            return $tblStudentSkill;
        }

        // 2. Fall Schüler hat Kompetenz über Kompetenzraster
        if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
            && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
            && ($level = $tblStudentEducation->getLevel()) !== null
        ) {
            // todo check performance optimize
            if (($tblSkillList = SkillGrid::useService()->getSkillListBy($tblSchoolType, $level, $tblSubject))) {
                foreach ($tblSkillList as $tblSkillTemp) {
                    if ($tblSkill->getId() === $tblSkillTemp->getId()) {
                        return $tblSkillTemp;
                    }
                    if ($tblSkill->getSkill() === $tblSkillTemp->getSkill()) {
                        return $tblSkillTemp;
                    }
                }
            }
        }

        return false;
    }


    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSubject|null $tblSubject
     *
     * @return TblStudentSkill[]
     */
    public function getStudentSkillListByPersonAndYear(TblPerson $tblPerson, TblYear $tblYear, ?TblSubject $tblSubject): array
    {
        $resultList = [];
        $list = (new Data($this->getBinding()))->getStudentSkillListByPersonAndYear($tblPerson, $tblYear, $tblSubject);
        foreach ($list as $tblStudentSkill) {
            if (($tblSkill = $tblStudentSkill->getServiceTblSkill())) {
                $resultList['SkillId_' . $tblSkill->getId()] = $tblStudentSkill;
            } else {
                $resultList['StudentSkillId_' . $tblStudentSkill->getId()] = $tblStudentSkill;
            }
        }

        return $resultList ?: [];
    }

    /**
     * @param $id
     *
     * @return TblStudentSkillRate|false
     */
    public function getStudentSkillRateById($id): false|TblStudentSkillRate
    {
        return (new Data($this->getBinding()))->getStudentSkillRateById($id);
    }

    /**
     * @param TblStudentSkill $tblStudentSkill
     *
     * @return TblStudentSkillRate[]
     */
    public function getStudentSkillRateListBy(TblStudentSkill $tblStudentSkill): array
    {
        return (new Data($this->getBinding()))->getStudentSkillRateListBy($tblStudentSkill);
    }

    /**
     * @param TblStudentSkill $tblStudentSkill
     *
     * @return TblStudentSkillRate|null
     */
    public function getLastStudentSkillRateBy(TblStudentSkill $tblStudentSkill): ?TblStudentSkillRate
    {
        if (($list = $this->getStudentSkillRateListBy($tblStudentSkill))) {
            return $list[array_key_last($list)];
        }

        return null;
    }

    /**
     * @param TblStudentSkill $tblStudentSkill
     * @param string $extraToolTip
     *
     * @return string
     */
    public function getDisplayStudentSkillRateLastOrAverage(TblStudentSkill $tblStudentSkill, string $extraToolTip = ""): string
    {
        $display = '';
        if ($tblStudentSkill->getIsAverage()) {
            if (($average = $this->getCalcAverageStudentSkillRate($tblStudentSkill)) !== null) {
                // in deutsches Zahlformat umwandeln
                $formatter = new NumberFormatter('de_DE', NumberFormatter::DECIMAL);
                $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 2);

                $display = '&#216; ' . $formatter->format($average) . (!$tblStudentSkill->getServiceTblScoreType() ? '%' : '');
                if ($extraToolTip) {
                    $display = new ToolTip($display, $extraToolTip);
                }
            }
        } else {
            if (($tblStudentSkillRate = $this->getLastStudentSkillRateBy($tblStudentSkill))) {
                $toolTip = "";
                if ($extraToolTip) {
                    $toolTip .= $extraToolTip . "<br /><br />";
                }
                $toolTip .= "Letzte Bewertung am {$tblStudentSkillRate->getDateString()} durch {$tblStudentSkillRate->getDisplayTeacher()}";

                if (($tblScoreTypeItem = $tblStudentSkillRate->getServiceTblScoreTypeItem())) {
                    $display = (new ToolTip($tblScoreTypeItem->getName(), $toolTip))->enableHtml();
                } else {
                    $display = (new ToolTip($tblStudentSkillRate->getRate() . '%', $toolTip))->enableHtml();
                }
            }
        }

        return $display;
    }

    /**
     * @param TblStudentSkill $tblStudentSkill
     *
     * @return float|null
     */
    public function getCalcAverageStudentSkillRate(TblStudentSkill $tblStudentSkill): ?float
    {
        if (($list = $this->getStudentSkillRateListBy($tblStudentSkill))) {
            $sum = array_sum(array_map(fn($item) => $item->getRateFloatValue(), $list));
            return round($sum / count($list), 2);
        }

        return null;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblPerson $tblPerson
     * @param TblSubject|null $tblSubject
     * @param $Data
     *
     * @return string
     */
    public function createStudentSkillRateList(TblDivisionCourse $tblDivisionCourse, TblPerson $tblPerson, ?TblSubject $tblSubject, $Data): string
    {
        list($hasErrors,$ErrorList) = $this->checkStudentSkillRateInput($Data);

        if ($hasErrors) {
            return SkillRate::useFrontend()->getStudentHead($tblPerson, $tblDivisionCourse, $tblSubject)
                . new Well(SkillRate::useFrontend()->formStudentSkillRateList($tblDivisionCourse, $tblPerson, $tblSubject, $ErrorList));
        }

        $tblPersonTeacher = Account::useService()->getPersonByLogin() ?: null;
        $tblYear = $tblDivisionCourse->getServiceTblYear();
        $datetime = new DateTime($Data['Date']);
        $comment = $Data['Comment'] ?: null;
        if (isset($Data['PercentSkills'])) {
            foreach ($Data['PercentSkills'] as $key => $value) {
                if ($value !== '') {
                    $value = trim(str_replace('%', '', $value));
                    if (($tblStudentSkill = $this->getStudentSkillByFrontendKey($key, $tblPerson, $tblYear, $tblSubject, $tblPersonTeacher))) {
                        (new Data($this->getBinding()))->createStudentSkillRate($tblStudentSkill, $tblPersonTeacher,
                            $datetime, $comment, $value, null);
                    }
                }
            }
        }
        // beim Speichern erstmal den Wert mit Speichern, falls das Bewertungssystem nachträglich noch angepasst wird
        if (isset($Data['ScoreTypeSkills'])) {
            foreach ($Data['ScoreTypeSkills'] as $scoreTypeId => $array) {
                if (ScoreType::useService()->getScoreTypeById($scoreTypeId)) {
                    foreach ($array as $key => $scoreTypeItemId) {
                        if ($scoreTypeItemId > 0
                            && ($tblScoreTypeItem = ScoreType::useService()->getScoreTypeItemById($scoreTypeItemId))
                        ) {
                            if (($tblStudentSkill = $this->getStudentSkillByFrontendKey($key, $tblPerson, $tblYear, $tblSubject, $tblPersonTeacher))) {
                                (new Data($this->getBinding()))->createStudentSkillRate($tblStudentSkill, $tblPersonTeacher,
                                    $datetime, $comment, $tblScoreTypeItem->getValue(), $tblScoreTypeItem);
                            }
                        }
                    }
                }
            }
        }

        return new Success('Die Daten wurde erfolgreich gespeichert.')
            . ApiSkillRate::pipelineLoadViewStudentContent($tblDivisionCourse->getId(), $tblPerson->getId(), $tblSubject?->getId());
    }

    private function getStudentSkillByFrontendKey(string $key, TblPerson $tblPerson, $tblYear, ?TblSubject $tblSubject, ?TblPerson $tblPersonTeacher)
        : ?TblStudentSkill
    {
        // key: SkillId_$id oder StudentSkillId_$id (individuelle Kompetenz)
        $split = explode('_', $key);
        if ($split[0] == 'SkillId' && ($tblSkill = SkillGrid::useService()->getSkillById($split[1]))) {
            if (!($tblStudentSkill = $this->getStudentSkillBy($tblPerson, $tblYear, $tblSkill))) {
                $tblStudentSkill = (new Data($this->getBinding()))->createStudentSkill(
                    $tblPerson, $tblYear, $tblSubject, $tblSkill, $tblPersonTeacher,
                    $tblSkill->getTblSkillArea()->getName() ?: null, $tblSkill->getLevel() ?: null, $tblSkill->getSkill());
            }
        } else {
            $tblStudentSkill = $this->getStudentSkillById($split[1]);
        }

        return $tblStudentSkill ?: null;
    }

    /**
     * @param $Data
     *
     * @return array
     */
    public function checkStudentSkillRateInput($Data): array
    {
        $hasErrors = false;
        $ErrorList = [];
        if (empty($Data['Date'])) {
            $ErrorList[] = [
                'Name' => 'Data[Date]',
                'Message' => 'Bitte geben Sie ein Datum an'
            ];
            $hasErrors = true;
        }
        // Prüfung bei Prozent
        if (isset($Data['PercentSkills'])) {
            foreach ($Data['PercentSkills'] as $key => $value) {
                if ($value !== '') {
                    // Prozent prüfen
                    $value = trim(str_replace('%', '', $value));
                    if (!ctype_digit($value) || $value < 0 || $value > 100) {
                        $name = "Data[PercentSkills][$key]";
                        $ErrorList[$name] = [
                            'Name' => $name,
                            'Message' => 'Bitte geben eine Zahl zwischen 0 und 100 ein.'
                        ];
                        $hasErrors = true;
                    }
                }
            }
        }

        return [$hasErrors, $ErrorList];
    }

    /**
     * @param $DivisionCourseId
     * @param TblStudentSkillRate $tblStudentSkillRate
     * @param $Data
     *
     * @return string
     */
    public function updateStudentSkillRate($DivisionCourseId, TblStudentSkillRate $tblStudentSkillRate, $Data): string
    {
        list($hasErrors,$ErrorList) = $this->checkStudentSkillRateInput($Data);

        if ($hasErrors) {
            return SkillRate::useFrontend()->loadEditStudentSkillRateHistoryContent($DivisionCourseId, $tblStudentSkillRate->getId(), $ErrorList);
        }

        $tblStudentSkill = $tblStudentSkillRate->getTblStudentSkill();
        $tblPerson = $tblStudentSkill->getServiceTblPerson() ?: null;
        $tblSubject = $tblStudentSkill->getServiceTblSubject() ?: null;
        $tblPersonTeacher = Account::useService()->getPersonByLogin() ?: null;
        $datetime = new DateTime($Data['Date']);
        $comment = $Data['Comment'] ?: null;
        if (isset($Data['PercentSkills'])) {
            foreach ($Data['PercentSkills'] as $value) {
                if ($value !== '') {

                    $value = trim(str_replace('%', '', $value));
                    (new Data($this->getBinding()))->updateStudentSkillRate($tblStudentSkillRate, $tblPersonTeacher,
                        $datetime, $comment, $value, null);
                }
            }
        }
        // beim Speichern erstmal den Wert mit Speichern, falls das Bewertungssystem nachträglich noch angepasst wird
        if (isset($Data['ScoreTypeSkills'])) {
            foreach ($Data['ScoreTypeSkills'] as $scoreTypeId => $array) {
                if (ScoreType::useService()->getScoreTypeById($scoreTypeId)) {
                    foreach ($array as $scoreTypeItemId) {
                        if ($scoreTypeItemId > 0
                            && ($tblScoreTypeItem = ScoreType::useService()->getScoreTypeItemById($scoreTypeItemId))
                        ) {
                            (new Data($this->getBinding()))->updateStudentSkillRate($tblStudentSkillRate, $tblPersonTeacher,
                                $datetime, $comment, $tblScoreTypeItem->getValue(), $tblScoreTypeItem);
                        }
                    }
                }
            }
        }

        return new Success('Die Daten wurde erfolgreich gespeichert.')
            // Schülerübersicht muss neu geladen werden
            // . ApiSkillRate::pipelineLoadViewStudentSkillRateHistoryContent($DivisionCourseId, $tblStudentSkill->getId());
            . ApiSkillRate::pipelineClose()
            . ApiSkillRate::pipelineLoadViewStudentContent($DivisionCourseId, $tblPerson?->getId(), $tblSubject?->getId());
    }

    /**
     * @return string
     */
    public function getRole(): string
    {
        if (($role = Consumer::useService()->getAccountSettingValue("SkillRateRole"))) {
            // zur Sicherheit prüfen, ob das erforderliche Recht noch vorhanden ist
            if ($role == "Headmaster" && Access::useService()->hasAuthorization('/Education/Competence/SkillRate/Headmaster')) {
                return $role;
            }
            // zur Sicherheit prüfen, ob das erforderliche Recht noch vorhanden ist
            if ($role == "AllReadonly" && Access::useService()->hasAuthorization('/Education/Competence/SkillRate/AllReadOnly')) {
                return $role;
            }
        }

        return "Teacher";
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     * @param $Data
     *
     * @return bool|TblStudentSkill
     */
    public function updateStudentSkill($DivisionCourseId, $PersonId, $SubjectId, $Data): bool|TblStudentSkill
    {
        $tblPersonTeacher = Account::useService()->getPersonByLogin() ?: null;
        $split = explode('_', $Data['Id']);
        if ($split[0] == 'SkillId'
            && ($tblSkill = SkillGrid::useService()->getSkillById($split[1]))
            && ($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $tblStudentSkill = SkillRate::useService()->getStudentSkillBy($tblPerson, $tblYear, $tblSkill);
            if (!$tblStudentSkill) {
                $tblSubject = Subject::useService()->getSubjectById($SubjectId) ?: null;

                return (new Data($this->getBinding()))->createStudentSkill($tblPerson, $tblYear, $tblSubject, $tblSkill, $tblPersonTeacher,
                    $tblSkill->getTblSkillArea()->getName() ?: null, $Data['SkillLevel'] ?: null, $Data['Skill']);
            }
        } else {
            $tblStudentSkill = SkillRate::useService()->getStudentSkillById($split[1]);
        }

        if ($tblStudentSkill) {
            $skillArea = null;
            $isAverage = null;
            $tblScoreType = null;
            if (!$tblStudentSkill->getServiceTblSkill()) {
                $skillArea = $Data['SkillArea'] ?? null;
                $tblScoreType = $Data['ScoreTypeId'] > 0 ? ScoreType::useService()->getScoreTypeById($Data['ScoreTypeId']) : null;
                $isAverage = isset($Data['IsAverage']);
            }

            return (new Data($this->getBinding()))->updateStudentSkill($tblStudentSkill, $tblPersonTeacher,
                $skillArea, $Data['SkillLevel'] ?: null, $Data['Skill'], $isAverage, $tblScoreType ?: null);
        }

        return false;
    }

    /**
     * @param $DivisionCourseId
     * @param TblStudentSkillRate $tblStudentSkillRate
     *
     * @return string
     */
    public function deleteStudentSkillRate($DivisionCourseId, TblStudentSkillRate $tblStudentSkillRate): string
    {
        $tblStudentSkill = $tblStudentSkillRate->getTblStudentSkill();
        $tblPerson = $tblStudentSkill->getServiceTblPerson() ?: null;
        $tblSubject = $tblStudentSkill->getServiceTblSubject() ?: null;

        (new Data($this->getBinding()))->deleteStudentSkillRate($tblStudentSkillRate);

        return new Success('Die Daten wurde erfolgreich gespeichert.')
            // Schülerübersicht muss neu geladen werden
            // . ApiSkillRate::pipelineLoadViewStudentSkillRateHistoryContent($DivisionCourseId, $tblStudentSkill->getId());
            . ApiSkillRate::pipelineClose()
            . ApiSkillRate::pipelineLoadViewStudentContent($DivisionCourseId, $tblPerson?->getId(), $tblSubject?->getId());
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     * @param $Data
     *
     * @return string
     */
    public function addStudentSkill($DivisionCourseId, $PersonId, $SubjectId, $Data): string {

        if (!isset($Data['Id']) || !($tblSkill = SkillGrid::useService()->getSkillById($Data['Id']))) {
            $ErrorList[] = [
                'Name' => 'Data[Id]',
                'Message' => 'Bitte wählen Sie eine Kompetenz aus.'
            ];

            return SkillRate::useFrontend()->openAddStudentSkillModal($DivisionCourseId, $PersonId, $SubjectId, $ErrorList);
        }

        if (($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $tblPersonTeacher = Account::useService()->getPersonByLogin() ?: null;
            $tblSubject = Subject::useService()->getSubjectById($SubjectId) ?: null;

            // prüfen, ob schon vorhanden
            if (!$this->getStudentSkillBy($tblPerson, $tblYear, $tblSkill)) {
                (new Data($this->getBinding()))->createStudentSkill(
                    $tblPerson, $tblYear, $tblSubject, $tblSkill, $tblPersonTeacher,
                    $tblSkill->getTblSkillArea()->getName() ?: null, $tblSkill->getLevel() ?: null, $tblSkill->getSkill()
                );
            }
        }

        return new Success('Kompetenz wurde erfolgreich hinzugefügt.')
            . ApiSkillRate::pipelineClose()
            . ApiSkillRate::pipelineLoadViewStudentContent($DivisionCourseId, $PersonId, $SubjectId);
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     * @param $Data
     *
     * @return string
     */
    public function createStudentSkill($DivisionCourseId, $PersonId, $SubjectId, $Data): string {

        if (empty($Data['Skill'])) {
            $ErrorList[] = [
                'Name' => 'Data[Skill]',
                'Message' => 'Bitte geben Sie eine Kompetenz an.'
            ];

            return SkillRate::useFrontend()->openCreateStudentSkillModal($DivisionCourseId, $PersonId, $SubjectId, $ErrorList);
        }

        if (($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $tblPersonTeacher = Account::useService()->getPersonByLogin() ?: null;
            $tblSubject = Subject::useService()->getSubjectById($SubjectId) ?: null;
            $tblScoreType = $Data['ScoreTypeId'] > 0 ? ScoreType::useService()->getScoreTypeById($Data['ScoreTypeId']) : null;

            (new Data($this->getBinding()))->createStudentSkill(
                $tblPerson, $tblYear, $tblSubject, null, $tblPersonTeacher,
                $Data['SkillArea'] ?: null, $Data['SkillLevel'] ?: null, $Data['Skill'], isset($Data['IsAverage']), $tblScoreType ?: null
            );
        }

        return new Success('Kompetenz wurde erfolgreich hinzugefügt.')
            . ApiSkillRate::pipelineClose()
            . ApiSkillRate::pipelineLoadViewStudentContent($DivisionCourseId, $PersonId, $SubjectId);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param bool $isOldYears
     *
     * @return TblStudentEducation[]
     */
    public function getStudentEducationList(TblPerson $tblPerson, TblYear $tblYear, bool $isOldYears): array
    {
        $tblStudentEducationList = [];
        if ($isOldYears) {
            list($startDate) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
            // neuere Schuljahre ausblenden und sortierung nach Datum absteigend
            if (($tempList = DivisionCourse::useService()->getStudentEducationListByPerson($tblPerson))) {
                $tempList = $this->getSorter($tempList)->sortObjectBy('YearNameForSorter', null, Sorter::ORDER_DESC);
                /** @var TblStudentEducation $temp */
                foreach ($tempList as $temp) {
                    if ($temp->getLeaveDate()) {
                        continue;
                    }

                    if (($tblYearTemp = $temp->getServiceTblYear())) {
                        list($startDateYearTemp) = Term::useService()->getStartDateAndEndDateOfYear($tblYearTemp);
                        if ($startDateYearTemp && $startDateYearTemp > $startDate) {
                            continue;
                        }
                    }

                    $tblStudentEducationList[] = $temp;
                }
            }
        } elseif (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
            $tblStudentEducationList[] = $tblStudentEducation;
        }

        return $tblStudentEducationList;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject|null $tblSubject
     * @param $SelectedYearId
     *
     * @param $Data
     *
     * @return string
     */
    public function createDivisionCourseSkillRateList(TblDivisionCourse $tblDivisionCourse, ?TblSubject $tblSubject, $SelectedYearId, $Data): string
    {
        list($hasErrors, $ErrorList) = $this->checkDivisionCourseRateInput($Data);

        if ($hasErrors) {
            return SkillRate::useFrontend()->loadEditDivisionCourseSkillRateContent(
                $tblDivisionCourse->getId(), $tblSubject?->getId(), $SelectedYearId, $Data, $ErrorList);
        }

        $tblPersonTeacher = Account::useService()->getPersonByLogin() ?: null;
        $tblYear = $tblDivisionCourse->getServiceTblYear();
        $datetime = new DateTime($Data['Date']);
        $comment = $Data['Comment'] ?: null;
        $createTblStudentSkillRateBulkList = [];
        // "Data[PercentSkills][{$tblPerson->getId()}][$inputKey]";
        if (isset($Data['PercentSkills'])) {
            foreach ($Data['PercentSkills'] as $personId => $personArray) {
                if (($tblPerson = Person::useService()->getPersonById($personId))) {
                    foreach ($personArray as $key => $value) {
                        if ($value !== '') {
                            $value = trim(str_replace('%', '', $value));
                            if (($tblStudentSkill = $this->getStudentSkillByFrontendKey($key, $tblPerson, $tblYear, $tblSubject, $tblPersonTeacher))) {
                                $tblStudentSkillRate = new TblStudentSkillRate();
                                $tblStudentSkillRate->setTblStudentSkill($tblStudentSkill);
                                $tblStudentSkillRate->setServiceTblPersonTeacher($tblPersonTeacher);
                                $tblStudentSkillRate->setDate($datetime);
                                $tblStudentSkillRate->setComment($comment);
                                $tblStudentSkillRate->setRate($value);

                                $createTblStudentSkillRateBulkList[] = $tblStudentSkillRate;
                            }
                        }
                    }
                }
            }
        }
        // beim Speichern erstmal den Wert mit Speichern, falls das Bewertungssystem nachträglich noch angepasst wird
        // "Data[ScoreTypeSkills][{$tblScoreType->getId()}][{$tblPerson->getId()}][$inputKey]"
        if (isset($Data['ScoreTypeSkills'])) {
            foreach ($Data['ScoreTypeSkills'] as $scoreTypeId => $scoreTypeArray) {
                if (ScoreType::useService()->getScoreTypeById($scoreTypeId)) {
                    foreach ($scoreTypeArray as $personId => $personArray) {
                        if (($tblPerson = Person::useService()->getPersonById($personId))) {
                            foreach ($personArray as $key => $scoreTypeItemId) {
                                if ($scoreTypeItemId > 0
                                    && ($tblScoreTypeItem = ScoreType::useService()->getScoreTypeItemById($scoreTypeItemId))
                                ) {
                                    if (($tblStudentSkill = $this->getStudentSkillByFrontendKey($key, $tblPerson, $tblYear, $tblSubject, $tblPersonTeacher))) {
                                        $tblStudentSkillRate = new TblStudentSkillRate();
                                        $tblStudentSkillRate->setTblStudentSkill($tblStudentSkill);
                                        $tblStudentSkillRate->setServiceTblPersonTeacher($tblPersonTeacher);
                                        $tblStudentSkillRate->setDate($datetime);
                                        $tblStudentSkillRate->setComment($comment);
                                        $tblStudentSkillRate->setRate($tblScoreTypeItem->getValue());
                                        $tblStudentSkillRate->setServiceTblScoreTypeItem($tblScoreTypeItem);

                                        $createTblStudentSkillRateBulkList[] = $tblStudentSkillRate;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($createTblStudentSkillRateBulkList) {
            (new Data($this->getBinding()))->createEntityListBulk($createTblStudentSkillRateBulkList);
        }

        return new Success('Die Daten wurde erfolgreich gespeichert.')
            . new Redirect('/Education/Competence/SkillRate/DivisionCourse', Redirect::TIMEOUT_SUCCESS,
                ['DivisionCourseId' => $tblDivisionCourse->getId(), 'SubjectId' => $tblSubject?->getId(), 'SelectedYearId' => $SelectedYearId]);
    }

    /**
     * @param $Data
     *
     * @return array
     */
    public function checkDivisionCourseRateInput($Data): array
    {
        $hasErrors = false;
        $ErrorList = [];
        if (empty($Data['Id'])) {
            $ErrorList['Data[Id]'] = [
                'Name' => 'Data[Id]',
                'Message' => 'Bitte wählen Sie eine Kompetenz aus.'
            ];
            $hasErrors = true;
        }
        if (empty($Data['Date'])) {
            $ErrorList['Data[Date]'] = [
                'Name' => 'Data[Date]',
                'Message' => 'Bitte geben Sie ein Datum an.'
            ];
            $hasErrors = true;
        }
        // Prüfung bei Prozent
        if (isset($Data['PercentSkills'])) {
            foreach ($Data['PercentSkills'] as $personId => $personArray) {
                foreach ($personArray as $key => $value) {
                    if ($value !== '') {
                        // Prozent prüfen
                        $value = trim(str_replace('%', '', $value));
                        if (!ctype_digit($value) || $value < 0 || $value > 100) {
                            $name = "Data[PercentSkills][$personId][$key]";
                            $ErrorList[$name] = [
                                'Name' => $name,
                                'Message' => 'Bitte geben eine Zahl zwischen 0 und 100 ein.'
                            ];
                            $hasErrors = true;
                        }
                    }
                }
            }
        }

        return [$hasErrors, $ErrorList];
    }
}