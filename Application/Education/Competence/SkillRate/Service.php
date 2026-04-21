<?php

namespace SPHERE\Application\Education\Competence\SkillRate;

use DateTime;
use NumberFormatter;
use SPHERE\Application\Education\Competence\ScoreType\ScoreType;
use SPHERE\Application\Education\Competence\SkillGrid\Service\Entity\TblSkill;
use SPHERE\Application\Education\Competence\SkillGrid\SkillGrid;
use SPHERE\Application\Education\Competence\SkillRate\Service\Data;
use SPHERE\Application\Education\Competence\SkillRate\Service\Entity\TblStudentSkill;
use SPHERE\Application\Education\Competence\SkillRate\Service\Entity\TblStudentSkillRate;
use SPHERE\Application\Education\Competence\SkillRate\Service\Setup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
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
     *
     * @return TblStudentSkill[]
     */
    public function getStudentSkillListByPersonAndYear(TblPerson $tblPerson, TblYear $tblYear): array
    {
        $resultList = [];
        $list = (new Data($this->getBinding()))->getStudentSkillListByPersonAndYear($tblPerson, $tblYear);
        foreach ($list as $tblStudentSkill) {
            if (($tblSkill = $tblStudentSkill->getServiceTblSkill())) {
                $resultList[$tblSkill->getId()] = $tblStudentSkill;
            }
        }

        return $resultList ?: [];
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblStudentSkill $tblStudentSkill
     *
     * @return TblStudentSkillRate[]
     */
    public function getStudentSkillRateListBy(TblPerson $tblPerson, TblStudentSkill $tblStudentSkill): array
    {
        return (new Data($this->getBinding()))->getStudentSkillRateListBy($tblPerson, $tblStudentSkill);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblStudentSkill $tblStudentSkill
     *
     * @return TblStudentSkillRate|null
     */
    public function getLastStudentSkillRateBy(TblPerson $tblPerson, TblStudentSkill $tblStudentSkill): ?TblStudentSkillRate
    {
        if (($list = $this->getStudentSkillRateListBy($tblPerson, $tblStudentSkill))) {
            return $list[array_key_last($list)];
        }

        return null;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblStudentSkill $tblStudentSkill
     *
     * @return string
     */
    public function getDisplayStudentSkillRateLastOrAverage(TblPerson $tblPerson, TblStudentSkill $tblStudentSkill): string
    {
        $display = '';
        if (($tblSkill = $tblStudentSkill->getServiceTblSkill())
            && ($tblSkillGrid = $tblSkill->getTblSkillGrid())
            && $tblSkillGrid->getIsAverage()
        ) {
            if (($average = $this->getCalcAverageStudentSkillRate($tblPerson, $tblStudentSkill)) !== null) {
                // in deutsches Zahlformat umwandeln
                $formatter = new NumberFormatter('de_DE', NumberFormatter::DECIMAL);
                $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 2);

                return '&#216; ' . $formatter->format($average) . (!$tblSkillGrid->getServiceTblScoreType() ? '%' : '');
            }
        } else {
            if (($tblStudentSkillRate = $this->getLastStudentSkillRateBy($tblPerson, $tblStudentSkill))) {
                if (($tblScoreTypeItem = $tblStudentSkillRate->getServiceTblScoreTypeItem())) {
                    $display = new ToolTip($tblScoreTypeItem->getName(),
                        "Letzte Bewertung am {$tblStudentSkillRate->getDateString()} durch {$tblStudentSkillRate->getDisplayTeacher()}");
                } else {
                    $display = new ToolTip($tblStudentSkillRate->getRate() . '%',
                        "Letzte Bewertung am {$tblStudentSkillRate->getDateString()} durch {$tblStudentSkillRate->getDisplayTeacher()}");
                }
            }
        }

        return $display;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblStudentSkill $tblStudentSkill
     *
     * @return float|null
     */
    public function getCalcAverageStudentSkillRate(TblPerson $tblPerson, TblStudentSkill $tblStudentSkill): ?float
    {
        if (($list = $this->getStudentSkillRateListBy($tblPerson, $tblStudentSkill))) {
            $sum = array_sum(array_map(fn($item) => $item->getRateFloatValue(), $list));
            return round($sum / count($list), 2);
        }

        return null;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSubject|null $tblSubject
     * @param $Data
     *
     * @return string
     */
    public function createStudentSkillRateList(TblPerson $tblPerson, TblYear $tblYear, ?TblSubject $tblSubject, $Data): string
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
            foreach ($Data['PercentSkills'] as $skillId => $value) {
                if ($value !== '') {
                    // Prozent prüfen
                    $value = trim(str_replace('%', '', $value));
                    if (!ctype_digit($value) || $value < 0 || $value > 100) {
                        $name = "Data[PercentSkills][$skillId]";
                        $ErrorList[$name] = [
                            'Name' => $name,
                            'Message' => 'Bitte geben eine Zahl zwischen 0 und 100 ein.'
                        ];
                        $hasErrors = true;
                    }
                }
            }
        }

        if ($hasErrors) {
            return new Well(SkillRate::useFrontend()->formStudentSkillRateList($tblPerson, $tblYear, $tblSubject, $ErrorList));
        }

        $tblPersonTeacher = Account::useService()->getPersonByLogin() ?: null;
        $datetime = new DateTime($Data['Date']);
        $comment = $Data['Comment'] ?: null;
        // "Data[PercentSkills][{$tblSkill->getId()}]"
        if (isset($Data['PercentSkills'])) {
            foreach ($Data['PercentSkills'] as $skillId => $value) {
                if ($value !== ''
                    && ($tblSkill = SkillGrid::useService()->getSkillById($skillId))
                ) {
                    if (!($tblStudentSkill = $this->getStudentSkillBy($tblPerson, $tblYear, $tblSkill))) {
                        $tblStudentSkill = (new Data($this->getBinding()))->createStudentSkill(
                            $tblPerson, $tblYear, $tblSubject, $tblSkill, $tblPersonTeacher,
                            $tblSkill->getTblSkillArea()->getName() ?: null, $tblSkill->getLevel() ?: null, $tblSkill->getSkill());
                    }

                    $value = trim(str_replace('%', '', $value));
                    (new Data($this->getBinding()))->createStudentSkillRate($tblPerson, $tblYear, $tblSubject, $tblStudentSkill, $tblPersonTeacher,
                        $datetime, $comment, $value, null);
                }
            }
        }
        // beim Speichern erstmal den Wert mit Speichern, falls das Bewertungssystem nachträglich noch angepasst wird
        // "Data[ScoreTypeSkills][{$scoreType['tblScoreTypeId']}][{$tblSkill->getId()}]"
        if (isset($Data['ScoreTypeSkills'])) {
            foreach ($Data['ScoreTypeSkills'] as $scoreTypeId => $array) {
                if (ScoreType::useService()->getScoreTypeById($scoreTypeId)) {
                    foreach ($array as $skillId => $scoreTypeItemId) {
                        if ($scoreTypeItemId > 0
                            && ($tblScoreTypeItem = ScoreType::useService()->getScoreTypeItemById($scoreTypeItemId))
                            && ($tblSkill = SkillGrid::useService()->getSkillById($skillId))
                        ) {
                            if (!($tblStudentSkill = $this->getStudentSkillBy($tblPerson, $tblYear, $tblSkill))) {
                                $tblStudentSkill = (new Data($this->getBinding()))->createStudentSkill(
                                    $tblPerson, $tblYear, $tblSubject, $tblSkill, $tblPersonTeacher,
                                    $tblSkill->getTblSkillArea()->getName() ?: null, $tblSkill->getLevel() ?: null, $tblSkill->getSkill());
                            }

                            (new Data($this->getBinding()))->createStudentSkillRate($tblPerson, $tblYear, $tblSubject, $tblStudentSkill, $tblPersonTeacher,
                                $datetime, $comment, $tblScoreTypeItem->getValue(), $tblScoreTypeItem);
                        }
                    }
                }
            }
        }

        return new Success('Die Daten wurde erfolgreich gespeichert.');
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
}