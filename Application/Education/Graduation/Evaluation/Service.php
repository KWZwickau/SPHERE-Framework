<?php
namespace SPHERE\Application\Education\Graduation\Evaluation;

use DateInterval;
use DateTime;
use DI\Debug;
use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Data;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestLink;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Setup;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Extern;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\NotAvailable;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Extension\Repository\Debugger;
use SPHERE\System\Extension\Repository\Sorter\DateTimeSorter;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\Graduation\Evaluation
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
     * @return bool|TblTestType[]
     */
    public function getTestTypeAllWhereTask()
    {

        return (new Data($this->getBinding()))->getTestTypeAllWhereTask();
    }

    /**
     * @param TblTestType $tblTestType
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param null|TblPeriod $tblPeriod
     * @param TblSubjectGroup|null $tblSubjectGroup
     *
     * @return bool|TblTest[]
     */
    public function getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblTestType $tblTestType = null,
        TblPeriod $tblPeriod = null,
        TblSubjectGroup $tblSubjectGroup = null
    ) {

        return (new Data($this->getBinding()))->getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
            $tblDivision, $tblSubject, $tblTestType, $tblPeriod, $tblSubjectGroup
        );
    }

    /**
     * @param TblTestType $tblTestType
     *
     * @return bool|TblTest[]
     */
    public function getTestAllByTestType(TblTestType $tblTestType)
    {

        return (new Data($this->getBinding()))->getTestAllByTestType($tblTestType);
    }

    /**
     * @param TblTestType $tblTestType
     * @param TblDivision $tblDivision
     * @return bool|TblTest[]
     */
    public function getTestAllByTestTypeAndDivision(TblTestType $tblTestType, TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->getTestAllByTestTypeAndDivision($tblTestType, $tblDivision);
    }

    /**
     * @param TblTestType $tblTestType
     * @param TblGradeType $tblGradeType
     * @param TblDivision $tblDivision
     *
     * @return bool|TblTest[]
     */
    public function getTestAllByTestTypeAndGradeTypeAndDivision(TblTestType $tblTestType, TblGradeType $tblGradeType, TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->getTestAllByTestTypeAndGradeTypeAndDivision($tblTestType, $tblGradeType, $tblDivision);
    }

    /**
     * @return bool|TblTask[]
     */
    public function getTaskAll()
    {

        return (new Data($this->getBinding()))->getTaskAll();
    }

    /**
     * @param TblTestType $tblTestType
     * @param TblYear $tblYear
     *
     * @return bool|TblTask[]
     */
    public function getTaskAllByTestType(TblTestType $tblTestType, TblYear $tblYear = null)
    {

        return (new Data($this->getBinding()))->getTaskAllByTestType($tblTestType, $tblYear);
    }

    /**
     * @param TblTask          $tblTask
     * @param TblDivision|null $tblDivision
     *
     * @return array|mixed
     * @throws \Exception
     */
    public function getStudentGrades(
        TblTask $tblTask,
        TblDivision $tblDivision = null
    ) {

        $tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblTask, $tblDivision);

        $tableContent = array();
        $tableHeader = array();

        $gradeList = array();
        $taskDate = new DateTime($tblTask->getDate());
        if ($tblDivision) {
            if (($tblDivisionStudentAll = Division::useService()->getStudentAllByDivision($tblDivision, true))) {
                $count = 1;
                foreach ($tblDivisionStudentAll as $tblPerson) {
                    if ($this->checkIsPersonInActive($tblDivision, $tblPerson, $taskDate)) {
                        continue;
                    }
                    $tableContent[$tblPerson->getId()]['Number'] = $count++;
                    $tableContent[$tblPerson->getId()]['Name'] = $tblPerson->getLastFirstName();
                    $tableContent[$tblPerson->getId()]['FirstName'] = $tblPerson->getFirstSecondName();
                    $tableContent[$tblPerson->getId()]['LastName'] = $tblPerson->getLastName();
                    $tableContent[$tblPerson->getId()]['Average'] = '';
                }
            }

            // Stichtagsnote
            if ($tblTask->getTblTestType()->getId() == Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK')) {
                $averageGradeList = array();
                if (!empty($tblTestAllByTask)) {
                    /** @var TblTest $tblTest */
                    foreach ($tblTestAllByTask as $tblTest) {
                        $tblSubject = $tblTest->getServiceTblSubject();
                        if ($tblSubject && $tblTest->getServiceTblDivision()) {
                            $tableHeader['Subject' . $tblSubject->getId()] = $tblSubject->getAcronym();
                            $tableContent[0]['Subject' . $tblSubject->getId()] = '';
                            $tableContent[0]['Average'] = '';

                            $tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
                                $tblTest->getServiceTblDivision(),
                                $tblSubject,
                                $tblTest->getServiceTblSubjectGroup() ? $tblTest->getServiceTblSubjectGroup() : null
                            );

                            if ($tblDivisionSubject && $tblDivisionSubject->getTblSubjectGroup()) {
                                $tblSubjectStudentAllByDivisionSubject =
                                    Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubject);
                                if ($tblSubjectStudentAllByDivisionSubject) {
                                    foreach ($tblSubjectStudentAllByDivisionSubject as $tblSubjectStudent) {

                                        $tblPerson = $tblSubjectStudent->getServiceTblPerson();
                                        if ($tblPerson) {
                                            if ($this->checkIsPersonInActive($tblDivision, $tblPerson, $taskDate)) {
                                                continue;
                                            }

                                            $tableContent = $this->setTableContentForAppointedDateTask($tblDivision,
                                                $tblTest, $tblSubject, $tblPerson, $tableContent,
                                                $tblDivisionSubject->getTblSubjectGroup()
                                                    ? $tblDivisionSubject->getTblSubjectGroup() : null,
                                                $gradeList,
                                                $averageGradeList
                                            );
                                        }
                                    }
                                }
                            } else {
                                if ($tblDivisionStudentAll) {
                                    foreach ($tblDivisionStudentAll as $tblPerson) {
                                        if ($this->checkIsPersonInActive($tblDivision, $tblPerson, $taskDate)) {
                                            continue;
                                        }

                                        $tableContent = $this->setTableContentForAppointedDateTask($tblDivision,
                                            $tblTest, $tblSubject, $tblPerson, $tableContent, null, $gradeList,
                                            $averageGradeList);
                                    }
                                }
                            }
                        }
                    }
                }

                // Sortierung nach Fächer-Acroynm
                if (!empty($tableHeader)) {
                    asort($tableHeader);
                }
                $prependTableHeader['Number'] = '#';
                $prependTableHeader['Name'] = 'Schüler';
                $tableHeader = $prependTableHeader + $tableHeader;

                // Bug Schüler ist nicht in der Gruppe, wenn nicht alle Schüler in einer Gruppe sind, z.B. bei Ethik
                if (!empty($tableContent)) {
                    $count = 1;
                    foreach ($tableContent as $PersonId => $student) {
                        foreach ($tableHeader as $key => $value) {
                            if ($key == 'Number') {
                                $tableContent[$PersonId][$key] = $count++;
                            } elseif (!isset($student[$key])) {
                                $tableContent[$PersonId][$key] = "";
                            }
                        }
                    }
                }

                // Gesamtdurchschnitt
                $tableHeader['Average'] = 'Ø';
                if (!empty($gradeList)) {
                    foreach ($gradeList as $personId => $gradeArray) {
                        $sum = 0;
                        foreach ($gradeArray as $grade) {
                            $sum += $grade;
                        }
                        $count = count($gradeArray);
                        $tableContent[$personId]['Average'] = $count  > 0
                            ? round($sum / $count, 2 ) : '';
                    }
                }

                // Durchschnitte pro Fach-Klasse
                $tableContent[0]['Number'] = '';
                $tableContent[0]['Name'] = new Muted('&#216; Fach-Klasse');
                $tableContent[0]['FirstName'] = '';
                $tableContent[0]['LastName'] = '';
                foreach ($averageGradeList as $subjectId => $grades) {
                    $countGrades = count($grades);
                    $tableContent[0]['Subject' . $subjectId] = $countGrades  > 0
                        ? round(array_sum($grades) / $countGrades, 2 ) : '';
                }
            } else {

                if (($tblSetting = Consumer::useService()->getSetting('Education', 'Graduation', 'Evaluation',
                    'ShowProposalBehaviorGrade'))
                ) {
                    $showProposalBehaviorGrade = $tblSetting->getValue();
                } else {
                    $showProposalBehaviorGrade = false;
                }

                // Kopfnoten
                $tableHeader['Number'] = '#';
                $tableHeader['Name'] = 'Schüler';
                $grades = array();

                if (!empty($tblTestAllByTask)) {
                    /** @var TblTest $tblTest */
                    foreach ($tblTestAllByTask as $tblTest) {
                        $tblGradeType = $tblTest->getServiceTblGradeType();
                        if ($tblGradeType && $tblTest->getServiceTblDivision() && $tblTest->getServiceTblSubject()) {

                            $tableHeader['Type' . $tblGradeType->getId()]
                                = $tblGradeType->getCode() . ' (' . $tblGradeType->getName() . ')';

                            $tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
                                $tblTest->getServiceTblDivision(),
                                $tblTest->getServiceTblSubject(),
                                $tblTest->getServiceTblSubjectGroup() ? $tblTest->getServiceTblSubjectGroup() : null
                            );

                            if ($tblDivisionSubject) {
                                if ($tblDivisionSubject->getTblSubjectGroup()) {
                                    $tblSubjectStudentAllByDivisionSubject =
                                        Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubject);
                                    if ($tblSubjectStudentAllByDivisionSubject) {
                                        foreach ($tblSubjectStudentAllByDivisionSubject as $tblSubjectStudent) {

                                            $tblPerson = $tblSubjectStudent->getServiceTblPerson();
                                            if ($tblPerson) {
                                                if ($this->checkIsPersonInActive($tblDivision, $tblPerson, $taskDate)) {
                                                    continue;
                                                }

                                                list($tableContent, $grades) = $this->setTableContentForBehaviourTask($tblDivision,
                                                    $tblTest, $tblPerson, $tableContent, $grades);
                                            }
                                        }
                                    }
                                } else {
                                    if ($tblDivisionStudentAll) {
                                        foreach ($tblDivisionStudentAll as $tblPerson) {
                                            if ($this->checkIsPersonInActive($tblDivision, $tblPerson, $taskDate)) {
                                                continue;
                                            }

                                            list($tableContent, $grades) = $this->setTableContentForBehaviourTask($tblDivision,
                                                $tblTest, $tblPerson, $tableContent, $grades);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // calc Average
                    if (isset($tableContent)) {
                        foreach ($tableContent as $personId => $rowList) {
                            $tblPerson = Person::useService()->getPersonById($personId);
                            $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR');
                            $tblGradeTypeAllWhereBehavior = Gradebook::useService()->getGradeTypeAllByTestType($tblTestType);
                            if ($tblPerson && $tblGradeTypeAllWhereBehavior) {
                                foreach ($tblGradeTypeAllWhereBehavior as $tblGradeType) {
                                    $gradeTypeId = $tblGradeType->getId();
                                    // Kopfnotenvorschlag KL
                                    if ($showProposalBehaviorGrade) {
                                        $proposalGrade = new Warning(new Bold('f'));
                                        if (($tblProposalBehaviorGrade = Gradebook::useService()->getProposalBehaviorGrade(
                                                $tblDivision, $tblTask, $tblGradeType, $tblPerson
                                            )) && $tblProposalBehaviorGrade->getDisplayGrade() !== ''
                                        ) {
                                            $proposalGrade = new Bold($tblProposalBehaviorGrade->getDisplayGrade());
                                        }

                                        if (isset($rowList['Type' . $gradeTypeId])) {
                                            $rowList['Type' . $gradeTypeId] .= new Small(' | (KL-Vorschlag: ' . $proposalGrade . ')');
                                        }
                                    }
                                    if (isset($grades[$personId][$gradeTypeId]) && $grades[$personId][$gradeTypeId]['Count'] > 0) {
                                        $average = round(floatval($grades[$personId][$gradeTypeId]['Sum']) / floatval($grades[$personId][$gradeTypeId]['Count']),
                                            2);
                                        $tableContent[$personId]['Type' . $gradeTypeId] =
                                            new Bold('Ø ' . $average
                                                 . ' | ') . $rowList['Type' . $gradeTypeId];
                                        $tableContent[$personId]['AverageExcel' . $gradeTypeId] = 'Ø ' . $average;
                                    } else {
                                        $tableContent[$personId]['AverageExcel' . $gradeTypeId] = 'Ø ';
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return array($tableHeader, $tableContent);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     * @param DateTime $taskDate
     *
     * @return bool
     */
    private function checkIsPersonInActive(TblDivision $tblDivision, TblPerson $tblPerson, DateTime $taskDate)
    {
        // inaktive Schüler abhängig vom Austrittsdatum ignorieren
        if (($tblDivisionStudent = Division::useService()->getDivisionStudentByDivisionAndPerson(
                $tblDivision, $tblPerson
            ))
        ) {
            if (($leaveDate = $tblDivisionStudent->getLeaveDateTime()) !== null
                && $taskDate > $leaveDate
            ) {
                return true;
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblTest $tblTest
     * @param TblSubject $tblSubject
     * @param TblPerson $tblPerson
     * @param $tableContent
     * @param TblSubjectGroup $tblSubjectGroup
     * @param array $gradeList
     *
     * @return  $tableContent
     */
    private function setTableContentForAppointedDateTask(
        TblDivision $tblDivision,
        TblTest $tblTest,
        TblSubject $tblSubject,
        TblPerson $tblPerson,
        $tableContent,
        TblSubjectGroup $tblSubjectGroup = null,
        &$gradeList = array(),
        &$averageGradeList = array()
    ) {

        $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
            $tblPerson);

        $tblTask = $tblTest->getTblTask();

        $tblScoreRule = Gradebook::useService()->getScoreRuleByDivisionAndSubjectAndGroup(
            $tblDivision,
            $tblSubject,
            $tblSubjectGroup
        );

        $average = Gradebook::useService()->calcStudentGrade(
            $tblPerson, $tblDivision, $tblSubject, Evaluation::useService()->getTestTypeByIdentifier('TEST'),
            $tblScoreRule ? $tblScoreRule : null,
            ($tblTaskPeriod = $tblTask->getServiceTblPeriodByDivision($tblDivision)) ? $tblTaskPeriod : null, null,
            $tblTask->getDate() ? $tblTask->getDate() : false, true
        );
        if (is_array($average)) {
            //            $errorRowList = $average;
            $average = ' ';
        } else {
            $posStart = strpos($average, '(');
            if ($posStart !== false) {
                $average = substr($average, 0, $posStart);
            }
        }

        if ($tblGrade) {
            // Zeugnistext
            if (($tblGradeText = $tblGrade->getTblGradeText())) {
                $tableContent[$tblPerson->getId()]['Subject' . $tblSubject->getId()] = $tblGradeText->getName();
                $tableContent[$tblPerson->getId()]['Subject' . $tblSubject->getId().'Grade'] = $tblGradeText->getName();
                $tableContent[$tblPerson->getId()]['Subject' . $tblSubject->getId().'Average'] = ($average || $average === (float)0 ? 'Ø'.$average : '');
                return $tableContent;
            }

            $gradeValue = $tblGrade->getGrade();
            $trend = $tblGrade->getTrend();

            if ($gradeValue !== null && $gradeValue !== '') {
                $gradeList[$tblPerson->getId()][] = $gradeValue;

                $averageGradeList[$tblSubject->getId()][$tblPerson->getId()] = $gradeValue;
            }

            $isGradeInRange = true;
            if ($average !== '' && $average !== null && $gradeValue !== null) {
                if (is_numeric($gradeValue)) {
                    $gradeFloat = floatval($gradeValue);
                    if (($gradeFloat - 0.5) <= $average && ($gradeFloat + 0.5) >= $average) {
                        $isGradeInRange = true;
                    } else {
                        $isGradeInRange = false;
                    }
                }
            }

            if (TblGrade::VALUE_TREND_PLUS === $trend) {
                $gradeValue .= '+';
            } elseif (TblGrade::VALUE_TREND_MINUS === $trend) {
                $gradeValue .= '-';
            }

            $gradeValueExcel = $gradeValue;

            if ($isGradeInRange) {
                $gradeValue = new \SPHERE\Common\Frontend\Text\Repository\Success($gradeValue);
            } else {
                $gradeValue = new \SPHERE\Common\Frontend\Text\Repository\Danger($gradeValue);
            }

            $gradeValue = new Bold($gradeValue);

            $tableContent[$tblPerson->getId()]
            ['Subject' . $tblSubject->getId()] = ($tblGrade->getGrade() !== null ?
                    $gradeValue : '') . (($average || $average === (float)0) ? new Muted(new Small('&nbsp;&nbsp; &#216;' . $average)) : '');
            $tableContent[$tblPerson->getId()]['Subject' . $tblSubject->getId().'Grade'] = ($tblGrade->getGrade() !== null ? $gradeValueExcel : '');
            $tableContent[$tblPerson->getId()]['Subject' . $tblSubject->getId().'Average'] = ($average || $average === (float)0 ? 'Ø'.$average : '');


            return $tableContent;
        } else {
            $tableContent[$tblPerson->getId()]
            ['Subject' . $tblSubject->getId()] =
                new Warning(new Bold('fehlt'))
                . (($average || $average === (float)0) ? new Muted('&nbsp;&nbsp; &#216;' . $average) : '');
            $tableContent[$tblPerson->getId()]['Subject' . $tblSubject->getId().'Grade'] = 'fehlt';
            $tableContent[$tblPerson->getId()]['Subject' . $tblSubject->getId().'Average'] = ($average || $average === (float)0 ? 'Ø'.$average : '');
            return $tableContent;
        }
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblTest $tblTest
     * @param TblPerson $tblPerson
     * @param $tableContent
     * @param $grades
     *
     * @return array
     */
    private function setTableContentForBehaviourTask(
        TblDivision $tblDivision,
        TblTest $tblTest,
        TblPerson $tblPerson,
        $tableContent,
        $grades
    ) {

        $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
            $tblPerson);

        if ($tblTest->getServiceTblGradeType() && $tblTest->getServiceTblSubject()) {
            $gradeTypeId = $tblTest->getServiceTblGradeType()->getId();
            $tblSubject = $tblTest->getServiceTblSubject();
            if ($tblGrade) {
                $gradeText = $tblSubject->getAcronym() . ': ' . ($tblGrade->getGrade() !== null ?
                        $tblGrade->getDisplayGrade() : '');
                if (is_numeric($tblGrade->getGrade())) {
                    if (isset($grades[$tblPerson->getId()][$gradeTypeId]['Count'])) {
                        $grades[$tblPerson->getId()][$gradeTypeId]['Count']++;
                    } else {
                        $grades[$tblPerson->getId()][$gradeTypeId]['Count'] = 1;
                    }
                    if (isset($grades[$tblPerson->getId()][$gradeTypeId]['Sum'])) {
                        $grades[$tblPerson->getId()][$gradeTypeId]['Sum']
                            = floatval($grades[$tblPerson->getId()][$gradeTypeId]['Sum']) + floatval($tblGrade->getGrade());
                    } else {
                        $grades[$tblPerson->getId()][$gradeTypeId]['Sum'] = floatval($tblGrade->getGrade());
                    }
                }
            } else {
                $gradeText = $tblSubject->getAcronym() . ': ' .
                    new Warning(new Bold('f'));
            }
            if (!isset($tableContent[$tblPerson->getId()]['Type' . $gradeTypeId])) {
                $tableContent[$tblPerson->getId()]
                ['Type' . $gradeTypeId] = new Small(new Small($gradeText));
                $tableContent[$tblPerson->getId()]
                ['GradeType' . $gradeTypeId][$tblSubject->getAcronym()] = strip_tags($gradeText);
                return array($tableContent, $grades);
            } else {
                $tableContent[$tblPerson->getId()]
                ['Type' . $gradeTypeId] .= new Small(new Small(' | ' . $gradeText));
                $tableContent[$tblPerson->getId()]
                ['GradeType' . $gradeTypeId][$tblSubject->getAcronym()] = strip_tags($gradeText);
                ksort($tableContent[$tblPerson->getId()]
                ['GradeType' . $gradeTypeId]);
                return array($tableContent, $grades);
            }
        }
        return array($tableContent, $grades);
    }

    /**
     * @param $tableContent
     * @param $tblPersonList
     *
     * @return false|FilePointer
     */
    public function generateTaskGradesExcelHead($tableHeader, $tableContent)
    {
        if (!empty($tableHeader) && !empty($tableContent)) {
            $fileLocation = Storage::createFilePointer('xlsx');

            $Row = 0;
            $Column = 0;

            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell($Column++, $Row), '#');
            $export->setValue($export->getCell($Column++, $Row), 'Vorname');
            $export->setValue($export->getCell($Column++, $Row), 'Nachname');
            unset($tableHeader['Number']);
            unset($tableHeader['Name']);
            $maxCount = 0;
            foreach ($tableContent as $columnList) {
                foreach ($columnList as $subjectAcronym => $gradeList) {
                    if (strpos($subjectAcronym, 'GradeType') !== false) {
                        $count = count($gradeList);
                        if ($count > $maxCount) {
                            $maxCount = $count;
                        }
                    }
                }
            }
            // Header verbinden
            foreach ($tableHeader as $Value) {
                for ($i = 0; $i <= $maxCount; $i++) {
                    $export->setValue($export->getCell($Column++, $Row), $Value);
                }
                $export->setStyle($export->getCell($Column - $i, $Row), $export->getCell($Column - 1, $Row))
                    ->mergeCells()
                    ->setBorderLeft();
            }
            $export->setStyle($export->getCell(0, $Row), $export->getCell($Column - 1, $Row))
                // Header Fett mit Unterstrich
                ->setFontBold()
                ->setBorderBottom();
            $export->getActiveSheet()->getStyle('A:A')
                ->getFont()
                ->setBold(true);
            $maxCount++;
            // Befüllen der Tabelle
            foreach ($tableContent as $tableRow) {
                $Row++;
                $Column = 0;
                $export->setValue($export->getCell($Column++, $Row), $tableRow['Number']);
                $export->setValue($export->getCell($Column++, $Row), $tableRow['FirstName']);
                $export->setValue($export->getCell($Column++, $Row), $tableRow['LastName']);
                foreach ($tableRow as $subjectKey => $personGradeList) {
                    if (strpos($subjectKey, 'GradeType') !== false) {
                        $countRows = 0;
                        $gradeTypeId = str_replace('GradeType', '', $subjectKey);
                        if (isset($tableRow['AverageExcel' . $gradeTypeId])) {
                            $countRows++;
                            $export->setValue($export->getCell($Column++, $Row), $tableRow['AverageExcel' . $gradeTypeId]);
                            $export->setStyle($export->getCell($Column-1, $Row), $export->getCell($Column, $Row))
                                ->setBorderLeft();
                        }
                        foreach ($personGradeList as $gradeText) {
                            $countRows++;
                            $export->setValue($export->getCell($Column++, $Row), $gradeText);
                        }
                        if ($countRows < $maxCount) {
                            $Column += $maxCount - $countRows;
                        }
                    }
                }
            }
            // set column width
            $widths = [3, 11, 15];
            for ($i = 0; $i < $Column; $i++) {
                if (isset($widths[$i])) {
                    $export->setStyle($export->getCell($i, 0))->setColumnWidth($widths[$i]);
                }
            }
            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
            return $fileLocation;
        }
        return false;
    }


    /**
     * @param $tableContent
     * @param $tblPersonList
     *
     * @return false|FilePointer
     */
    public function generateTaskGradesExcel($tableHeader, $tableContent)
    {

        if (!empty($tableHeader) && !empty($tableContent)) {
            $fileLocation = Storage::createFilePointer('xlsx');

            $Row = 0;
            $Column = 0;

            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell($Column++, $Row), '#');
            $export->setValue($export->getCell($Column++, $Row), 'Vorname');
            $export->setValue($export->getCell($Column++, $Row), 'Nachname');
            unset($tableHeader['Number']);
            unset($tableHeader['Name']);
            foreach ($tableHeader as $Value){
                $export->setValue($export->getCell($Column++, $Row), $Value);
                $Column++;
                $export->setStyle($export->getCell($Column-2, $Row), $export->getCell($Column-1, $Row))
                    ->mergeCells();
            }
            $export->setStyle($export->getCell(0, $Row), $export->getCell($Column-1, $Row))
                // Header Fett
                ->setFontBold()
                // Strich nach dem Header
                ->setBorderBottom();
            $export->getActiveSheet()
                ->getStyle('A:A')
                ->getFont()
                ->setBold(true);

            // Befüllen der Tabelle
            $count = count($tableContent);
            foreach ($tableContent as $tableRow) {
                $Row++;
                $Column = 0;
                $export->setValue($export->getCell($Column++, $Row), $tableRow['Number']);
                $export->setValue($export->getCell($Column++, $Row), $tableRow['FirstName']);
                $export->setValue($export->getCell($Column++, $Row), $tableRow['LastName']);
                $export->setStyle($export->getCell($Column, $Row), $export->getCell($Column, $Row));
                foreach ($tableHeader as $SubjectKey => $Value) {
                    if (strpos($SubjectKey, 'Subject') !== false) {
                        if (isset($tableRow[$SubjectKey . 'Grade'])) {
                            $export->setValue($export->getCell($Column, $Row), $tableRow[$SubjectKey . 'Grade']);
                        }
                            // Trennstrich pro Fach
                        if($Row != $count)
                        {
                            $export->setStyle($export->getCell($Column, $Row), $export->getCell($Column, $Row))
                                ->setBorderLeft();
                        }
                        $Column++;
                        if (isset($tableRow[$SubjectKey . 'Average'])) {
                            $export->setValue($export->getCell($Column, $Row), $tableRow[$SubjectKey . 'Average']);
                        }
                        $Column++;
                    }
                }
                if (isset($tableHeader['Average']) && ($Row != $count)) {
                    $export->setValue($export->getCell($Column, $Row), $tableRow['Average']);
                    // Trennstrich Durchschnitt
                    $export->setStyle($export->getCell($Column, $Row), $export->getCell($Column, $Row))
                        ->setBorderLeft();
                }
            }
            // set column width
            $export->setStyle($export->getCell(0, 0))->setColumnWidth(3);
            $export->setStyle($export->getCell(1, 0))->setColumnWidth(13);
            $export->setStyle($export->getCell(2, 0))->setColumnWidth(15);
            $colCount = count($tableHeader);
            $colCount *= 2;
            $a = 3;
            for ($col = 3; $a <= $colCount; $col++) {
                $width = ($a % 2 == 1) ? 3 : 7;
                $export->setStyle($export->getCell($col, 0))->setColumnWidth($width);
                $a++;
            }
            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
            return $fileLocation;
        }
        return false;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param null $DivisionSubjectId
     * @param null $Test
     * @param string $BasicRoute
     *
     * @return IFormInterface|string
     */
    public function createTest(IFormInterface $Stage = null, $DivisionSubjectId = null, $Test = null, $BasicRoute)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Test || $DivisionSubjectId === null) {
            return $Stage;
        }

        $Error = false;
        if (!($tblPeriod = Term::useService()->getPeriodById($Test['Period']))) {
            $Stage->setError('Test[Period]', 'Bitte wählen Sie einen Zeitraum aus');
            $Error = true;
        }
        if (!($tblGradeType = Gradebook::useService()->getGradeTypeById($Test['GradeType']))) {
            $Stage->setError('Test[GradeType]', 'Bitte wählen Sie einen Zensuren-Typ aus');
            $Error = true;
        }
        if (isset($Test['Date']) && empty($Test['Date'])) {
            $Stage->setError('Test[Date]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if ($Error) {
            return $Stage;
        }

        $tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId);

        if (!$tblDivisionSubject) {
            return new Danger(new Ban() . ' Fach-Klasse nicht gefunden')
                . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        if (!($tblDivision = $tblDivisionSubject->getTblDivision())) {
            return new Danger(new Ban() . ' Klasse nicht gefunden')
                . new Redirect($BasicRoute . '/Selected', Redirect::TIMEOUT_ERROR,
                    array('DivisionSubjectId' => $tblDivisionSubject->getId()));
        }

        if (!$tblDivisionSubject->getServiceTblSubject()) {
            return new Danger(new Ban() . ' Fach nicht gefunden')
                . new Redirect($BasicRoute . '/Selected', Redirect::TIMEOUT_ERROR,
                    array('DivisionSubjectId' => $tblDivisionSubject->getId()));
        }

        if (!$tblGradeType) {
            return new Danger(new Ban() . ' Zensuren-Typ nicht gefunden')
                . new Redirect($BasicRoute . '/Selected', Redirect::TIMEOUT_ERROR,
                    array('DivisionSubjectId' => $tblDivisionSubject->getId()));
        }

        $tblTest = (new Data($this->getBinding()))->createTest(
            $tblDivisionSubject->getTblDivision(),
            $tblDivisionSubject->getServiceTblSubject(),
            $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null,
            $tblPeriod,
            $tblGradeType,
            $this->getTestTypeByIdentifier('TEST'),
            null,
            $Test['Description'],
            isset($Test['IsContinues']) ? null : $Test['Date'],
            isset($Test['IsContinues']) ? null : $Test['CorrectionDate'],
            isset($Test['IsContinues']) ? null : $Test['ReturnDate'],
            isset($Test['IsContinues']),
            isset($Test['FinishDate']) ? $Test['FinishDate'] : null
        );
        if (isset($Test['Link']) && $tblTest) {
            $LinkId = $this->getNextLinkId();
            $this->createTestLink($tblTest, $LinkId);

            $tblPeriodOriginList = false;
            if (($tblYear = $tblDivision->getServiceTblYear())) {
                $tblPeriodOriginList = Term::useService()->getPeriodAllByYear($tblYear, $tblDivision);
            }

            foreach ($Test['Link'] as $divisionSubjectToLinkId => $value) {
                if (($tblDivisionSubjectToLink = Division::useService()->getDivisionSubjectById($divisionSubjectToLinkId))) {
                    $tblPeriodLink = $tblPeriod;
                    // SSW-389, korrekte Periode ermitteln
                    if (($tblDivisionToLink = $tblDivisionSubjectToLink->getTblDivision())
                        && ($tblYear = $tblDivisionToLink->getServiceTblYear())
                        && (($tblPeriodLinkList = Term::useService()->getPeriodAllByYear($tblYear, $tblDivision)))
                    ) {
                        $hasPeriod = false;
                        foreach ($tblPeriodLinkList as $tblPeriodItem) {
                            if ($tblPeriod->getId() == $tblPeriodItem->getId()) {
                                $hasPeriod = true;
                                break;
                            }
                        }

                        if (!$hasPeriod && $tblPeriodOriginList) {
                            $periodPosition = 0;
                            foreach ($tblPeriodOriginList as $tblPeriodOrigin) {
                                if ($tblPeriod->getId() == $tblPeriodOrigin->getId()) {
                                    if (isset($tblPeriodLinkList[$periodPosition])) {
                                        $tblPeriodLink = $tblPeriodLinkList[$periodPosition];
                                    }
                                    break;
                                }

                                $periodPosition++;
                            }
                        }
                    }

                    $tblTestAdd = (new Data($this->getBinding()))->createTest(
                        $tblDivisionSubjectToLink->getTblDivision(),
                        $tblDivisionSubjectToLink->getServiceTblSubject(),
                        $tblDivisionSubjectToLink->getTblSubjectGroup() ? $tblDivisionSubjectToLink->getTblSubjectGroup() : null,
                        $tblPeriodLink,
                        $tblGradeType,
                        $this->getTestTypeByIdentifier('TEST'),
                        null,
                        $Test['Description'],
                        isset($Test['IsContinues']) ? null : $Test['Date'],
                        isset($Test['IsContinues']) ? null : $Test['CorrectionDate'],
                        isset($Test['IsContinues']) ? null : $Test['ReturnDate'],
                        isset($Test['IsContinues']),
                        isset($Test['FinishDate']) ? $Test['FinishDate'] : null
                    );

                    $this->createTestLink($tblTestAdd, $LinkId);
                }
            }
        }

        return new Success('Die Leistungsüberprüfung ist angelegt worden',
                new \SPHERE\Common\Frontend\Icon\Repository\Success())
            . new Redirect($BasicRoute . '/Selected', Redirect::TIMEOUT_SUCCESS,
                array('DivisionSubjectId' => $tblDivisionSubject->getId()));

    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup|null $tblSubjectGroup
     * @param TblPeriod|null $tblPeriod
     * @param TblGradeType|null $tblGradeType
     * @param TblTestType|null $tblTestType
     * @param TblTask|null $tblTask
     * @param string $Description
     * @param null $Date
     * @param null $CorrectionDate
     * @param null $ReturnDate
     * @param false $IsContinues
     * @param null $FinishDate
     *
     * @return TblTest|null
     */
    public function insertTest(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup = null,
        TblPeriod $tblPeriod = null,
        TblGradeType $tblGradeType = null,
        TblTestType $tblTestType = null,
        TblTask $tblTask = null,
        $Description = '',
        $Date = null,
        $CorrectionDate = null,
        $ReturnDate = null,
        $IsContinues = false,
        $FinishDate = null
    ) : ?TblTest {
        return (new Data($this->getBinding()))->createTest(
            $tblDivision,
            $tblSubject,
            $tblSubjectGroup,
            $tblPeriod,
            $tblGradeType,
            $tblTestType,
            $tblTask,
            $Description,
            $Date,
            $CorrectionDate,
            $ReturnDate,
            $IsContinues,
            $FinishDate,
        );
    }

    /**
     * @param string $Identifier
     *
     * @return bool|TblTestType
     */
    public function getTestTypeByIdentifier($Identifier)
    {

        return (new Data($this->getBinding()))->getTestTypeByIdentifier($Identifier);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param $Id
     * @param $Test
     * @param $BasicRoute
     *
     * @return IFormInterface|string
     */
    public function updateTest(IFormInterface $Stage = null, $Id, $Test, $BasicRoute)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Test) {
            return $Stage;
        }

        $tblTest = $this->getTestById($Id);
        $Error = false;
        if (!isset($Test['Period']) || !($tblPeriod = Term::useService()->getPeriodById($Test['Period']))) {
            $Stage->setError('Test[Period]', 'Bitte wählen Sie einen Zeitraum aus');
            $Error = true;
        }
        if (!($tblGradeType = Gradebook::useService()->getGradeTypeById($Test['GradeType']))) {
            $Stage->setError('Test[GradeType]', 'Bitte wählen Sie einen Zensuren-Typ aus');
            $Error = true;
        }
        if (isset($Test['Date']) && empty($Test['Date'])) {
            $Stage->setError('Test[Date]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if ($tblTest && $tblTest->getFinishDate() && isset($Test['FinishDate']) && empty($Test['FinishDate'])) {
            $Stage->setError('Test[FinishDate]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if ($Error) {
            return $Stage;
        }

        if ($tblTest) {
            // Change GradeType of Grades
            if ($tblTest->getServiceTblGradeType()
                && $tblGradeType
                && $tblGradeType->getId() != $tblTest->getServiceTblGradeType()->getId()
            ) {
                $isChangeGradesGradeType = true;
                Gradebook::useService()->updateGradesGradeTypeByTest($tblTest, $tblGradeType);
            } else {
                $isChangeGradesGradeType = false;
            }

            // Change Period of Grades
            if ($tblTest->getServiceTblPeriod()
                && $tblPeriod
                && $tblPeriod->getId() != $tblTest->getServiceTblPeriod()->getId()
            ) {
                $isChangeGradesPeriod = true;
                Gradebook::useService()->updateGradesPeriodByTest($tblTest, $tblPeriod);
            } else {
                $isChangeGradesPeriod = false;
            }


            (new Data($this->getBinding()))->updateTest(
                $tblTest,
                $Test['Description'],
                isset($Test['Date']) ? $Test['Date'] : null,
                isset($Test['CorrectionDate']) ? $Test['CorrectionDate'] : null,
                isset($Test['ReturnDate']) ? $Test['ReturnDate'] : null,
                isset($Test['FinishDate']) ? $Test['FinishDate'] : null,
                $tblGradeType ? $tblGradeType : null,
                $tblPeriod ? $tblPeriod : null
            );
            if (($tblTestLinkList = $tblTest->getLinkedTestAll())) {
                foreach ($tblTestLinkList as $tblTestItem) {
                    if ($isChangeGradesGradeType) {
                        Gradebook::useService()->updateGradesGradeTypeByTest($tblTestItem, $tblGradeType);
                    }
                    if ($isChangeGradesPeriod) {
                        Gradebook::useService()->updateGradesPeriodByTest($tblTestItem, $tblPeriod);
                    }

                    (new Data($this->getBinding()))->updateTest(
                        $tblTestItem,
                        $Test['Description'],
                        isset($Test['Date']) ? $Test['Date'] : null,
                        isset($Test['CorrectionDate']) ? $Test['CorrectionDate'] : null,
                        isset($Test['ReturnDate']) ? $Test['ReturnDate'] : null,
                        isset($Test['FinishDate']) ? $Test['FinishDate'] : null,
                        $tblGradeType ? $tblGradeType : null,
                        $tblPeriod ? $tblPeriod : null
                    );
                }
            }
        }

        if (!$tblTest->getServiceTblDivision()) {
            return new Danger(new Ban() . ' Klasse nicht gefunden')
                . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }
        if (!$tblTest->getServiceTblSubject()) {
            return new Danger(new Ban() . ' Fach nicht gefunden')
                . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        $tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
            $tblTest->getServiceTblDivision(),
            $tblTest->getServiceTblSubject(),
            $tblTest->getServiceTblSubjectGroup() ? $tblTest->getServiceTblSubjectGroup() : null
        );

        return new Success('Test erfolgreich geändert.', new \SPHERE\Common\Frontend\Icon\Repository\Success()) .
            new Redirect($BasicRoute . '/Selected', Redirect::TIMEOUT_SUCCESS,
                array('DivisionSubjectId' => $tblDivisionSubject->getId()));
    }

    /**
     * @param $Id
     *
     * @return bool|TblTest
     */
    public function getTestById($Id)
    {

        return (new Data($this->getBinding()))->getTestById($Id);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param $Task
     * @param TblYear $tblYear
     *
     * @return IFormInterface|string
     */
    public function createTask(IFormInterface $Stage = null, $Task, TblYear $tblYear = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Task) {
            return $Stage;
        }

        if (null === $tblYear) {
            return new Danger('Kein Schuljahr ausgewählt', new Exclamation());
        }

        $Error = false;
        if (!($tblTestType = Evaluation::useService()->getTestTypeById($Task['Type']))) {
            $Stage->setError('Task[Type]', 'Bitte wählen Sie eine Kategorie aus');
            $Error = true;
        }
        if (isset($Task['Name']) && empty($Task['Name'])) {
            $Stage->setError('Task[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }
        if (isset($Task['Date']) && empty($Task['Date'])) {
            $Stage->setError('Task[Date]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if (isset($Task['FromDate']) && empty($Task['FromDate'])) {
            $Stage->setError('Task[FromDate]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if (isset($Task['ToDate']) && empty($Task['ToDate'])) {
            $Stage->setError('Task[ToDate]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        } else {
//            $nowDate = new DateTime('now');
            $toDate = new DateTime($Task['ToDate']);
            $fromDate = new DateTime($Task['FromDate']);

//            if ($nowDate > $toDate) {
//                $Stage->setError('Task[ToDate]', 'Bitte geben Sie ein Datum in der Zukunft an');
//                $Error = true;
//            } else
            if ($fromDate > $toDate) {
                $Stage->setError('Task[ToDate]', 'Der "Bearbeitungszeitraum bis" darf nicht kleiner sein, als der "Bearbeitungszeitraum von".');
                $Error = true;
            }
        }

        if (!$Error) {
            if ($Task['Period'] < 0) {
                $tblPeriod = TblTask::getPseudoPeriod($Task['Period']);
            } else {
                $tblPeriod = Term::useService()->getPeriodById($Task['Period']);
            }
            $tblScoreType = Gradebook::useService()->getScoreTypeById($Task['ScoreType']);
            $tblTask = (new Data($this->getBinding()))->createTask(
                $tblTestType, $Task['Name'], $Task['Date'], $Task['FromDate'], $Task['ToDate'],
                $tblPeriod ? $tblPeriod : null, $tblScoreType ? $tblScoreType : null, $tblYear ? $tblYear : null
            );
            $Stage .= new Success('Notenauftrag erfolgreich angelegt',
                    new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . new Redirect('/Education/Graduation/Evaluation/Task/Headmaster/Division', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblTask->getId()));
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return bool|TblTestType
     */
    public function getTestTypeById($Id)
    {

        return (new Data($this->getBinding()))->getTestTypeById($Id);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param                     $Id
     * @param                     $Task
     *
     * @return IFormInterface|Redirect
     */
    public function updateTask(IFormInterface $Stage = null, $Id, $Task)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Task) {
            return $Stage;
        }
        $Error = false;
        if (isset($Task['Name']) && empty($Task['Name'])) {
            $Stage->setError('Task[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }
        if (isset($Task['Date']) && empty($Task['Date'])) {
            $Stage->setError('Task[Date]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if (isset($Task['FromDate']) && empty($Task['FromDate'])) {
            $Stage->setError('Task[FromDate]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if (isset($Task['ToDate']) && empty($Task['ToDate'])) {
            $Stage->setError('Task[ToDate]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        } else {
//            $nowDate = new DateTime('now');
            $toDate = new DateTime($Task['ToDate']);
            $fromDate = new DateTime($Task['FromDate']);

//            if ($nowDate > $toDate) {
//                $Stage->setError('Task[ToDate]', 'Bitte geben Sie ein Datum in der Zukunft an');
//                $Error = true;
//            } else
            if ($fromDate > $toDate) {
                $Stage->setError('Task[ToDate]', 'Der "Bearbeitungszeitraum bis" darf nicht kleiner sein, als der "Bearbeitungszeitraum von".');
                $Error = true;
            }
        }

        if (!$Error) {
            $tblTask = $this->getTaskById($Id);
            if ($Task['Period'] < 0) {
                $tblPeriod = TblTask::getPseudoPeriod($Task['Period']);
            } else {
                $tblPeriod = Term::useService()->getPeriodById($Task['Period']);
            }
            $tblScoreType = Gradebook::useService()->getScoreTypeById($Task['ScoreType']);
            (new Data($this->getBinding()))->updateTask(
                $tblTask,
                $tblTask->getTblTestType(),
                $Task['Name'],
                $Task['Date'],
                $Task['FromDate'],
                $Task['ToDate'],
                $tblPeriod ? $tblPeriod : null,
                $tblScoreType ? $tblScoreType : null,
                $tblTask->isLocked()
            );

            $Stage .= new Success('Notenauftrag erfolgreich geändert',
                    new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . new Redirect('/Education/Graduation/Evaluation/Task/Headmaster', Redirect::TIMEOUT_SUCCESS);

        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return bool|TblTask
     */
    public function getTaskById($Id)
    {

        return (new Data($this->getBinding()))->getTaskById($Id);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param $Id
     * @param null $Data
     *
     * @return IFormInterface|string
     */
    public function updateDivisionTasks(IFormInterface $Stage = null, $Id, $Data = null)
    {

        /**
         * Skip to Frontend
         */
        if ($Data === null) {
            return $Stage;
        }

        $tblTask = Evaluation::useService()->getTaskById($Id);
        if ($tblTask) {
            if ($tblTask->getTblTestType()->getIdentifier() == 'BEHAVIOR_TASK') {
                $isBehaviorTask = true;
            } else {
                $isBehaviorTask = false;
            }

            if ($isBehaviorTask) {

                $behaviorTaskAddList = array();
                $behaviorTaskRemoveTestList = array();

                // add
                if ($Data && isset($Data['GradeType'])) {
                    foreach ($Data['GradeType'] as $gradeTypeId => $value) {
                        $tblGradeType = Gradebook::useService()->getGradeTypeById($gradeTypeId);
                        if ($tblGradeType) {
                            if ($Data && isset($Data['Division'])) {
                                foreach ($Data['Division'] as $divisionId => $divisionValue) {
                                    $tblDivision = Division::useService()->getDivisionById($divisionId);
                                    if ($tblDivision) {
                                        $behaviorTaskAddList[$divisionId . '_' . $gradeTypeId] = array(
                                            'tblTask' => $tblTask,
                                            'tblDivision' => $tblDivision,
                                            'tblGradeType' => $tblGradeType
                                        );
                                    }
                                }
                            }

                            // add Division All by Type
                            $tblYear = $tblTask->getServiceTblYear();
                            if (isset($Data['Type']) && $tblYear) {
                                foreach ($Data['Type'] as $typeId => $typeValue) {
                                    if (($tblSchoolType = Type::useService()->getTypeById($typeId))
                                        && ($tblDivisionListByType = Division::useService()->getDivisionAllByYearAndType($tblYear, $tblSchoolType))
                                    ) {
                                        foreach ($tblDivisionListByType as $tblDivisionFromType) {
                                            $behaviorTaskAddList[$tblDivisionFromType->getId() . '_' . $gradeTypeId] = array(
                                                'tblTask' => $tblTask,
                                                'tblDivision' => $tblDivisionFromType,
                                                'tblGradeType' => $tblGradeType
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                // remove
                $tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblTask);
                if ($tblTestAllByTask) {
                    foreach ($tblTestAllByTask as $tblTest) {
                        $tblDivision = $tblTest->getServiceTblDivision();
                        $tblGradeTypeByTest = $tblTest->getServiceTblGradeType();
                        if ($tblDivision && $tblGradeTypeByTest) {
                            if (!isset($behaviorTaskAddList[$tblDivision->getId() . '_' . $tblGradeTypeByTest->getId()])) {
                                $behaviorTaskRemoveTestList[] = $tblTest;
                            } elseif ($tblTest->getServiceTblGradeType()
                                && !isset($Data['GradeType'][$tblGradeTypeByTest->getId()])
                            ) {
                                // delete single
                                $behaviorTaskRemoveTestList[] = $tblTest;
                            }
                        }
                    }
                }

                (new Data($this->getBinding()))->updateDivisionBehaviorTaskAsBulk($behaviorTaskAddList,
                    $behaviorTaskRemoveTestList);

            } else {

                $addList = array();
                $removeList = array();

                // add
                if ($Data && isset($Data['Division'])) {
                    foreach ($Data['Division'] as $divisionId => $value) {
                        $tblDivision = Division::useService()->getDivisionById($divisionId);
                        if ($tblDivision) {
                            $addList[$tblDivision->getId()] = array(
                                'tblTask' => $tblTask,
                                'tblDivision' => $tblDivision
                            );
                        }
                    }
                }

                // add Division All by Type
                $tblYear = $tblTask->getServiceTblYear();
                if (isset($Data['Type']) && $tblYear) {
                    foreach ($Data['Type'] as $typeId => $value) {
                        if (($tblSchoolType = Type::useService()->getTypeById($typeId))
                            && ($tblDivisionListByType = Division::useService()->getDivisionAllByYearAndType($tblYear, $tblSchoolType))
                        ) {
                            foreach ($tblDivisionListByType as $tblDivisionFromType) {
                                $addList[$tblDivisionFromType->getId()] = array(
                                    'tblTask' => $tblTask,
                                    'tblDivision' => $tblDivisionFromType
                                );
                            }
                        }
                    }
                }

                // remove
                $tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblTask);
                if ($tblTestAllByTask) {
                    foreach ($tblTestAllByTask as $tblTest) {
                        $tblDivision = $tblTest->getServiceTblDivision();
                        if ($tblDivision) {
                            if (!isset($addList[$tblDivision->getId()])) {
                                $removeList[] = $tblTest;
                            }
                        }
                    }
                }

                (new Data($this->getBinding()))->updateDivisionAppointedDateTaskAsBulk($addList, $removeList);
            }
        }

        return new Success('Daten erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
            . new Redirect('/Education/Graduation/Evaluation/Task/Headmaster/Division', Redirect::TIMEOUT_SUCCESS,
                array('Id' => $tblTask->getId()));
    }

    /**
     * @param TblTask $tblTask
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblGradeType $tblGradeType
     * @param TblSubjectGroup|null $tblSubjectGroup
     *
     * @return bool
     */
    public function existsTestByTaskAndGradeType(
        TblTask $tblTask,
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblGradeType $tblGradeType,
        TblSubjectGroup $tblSubjectGroup = null
    ) {

        return (new Data($this->getBinding()))->existsTestByTaskAndGradeType($tblTask, $tblDivision, $tblSubject,
            $tblGradeType, $tblSubjectGroup);
    }

    /**
     * @param TblTask $tblTask
     * @param TblDivision|null $tblDivision
     *
     * @return bool|Service\Entity\TblTest[]
     */
    public function getTestAllByTask(TblTask $tblTask, TblDivision $tblDivision = null)
    {

        $tblTestList = (new Data($this->getBinding()))->getTestAllByTask($tblTask, $tblDivision);
        if ($tblTestList) {
            $tblTestList = $this->getSorter($tblTestList)->sortObjectBy('GradeTypeName');
        }

        return $tblTestList;
    }

    /**
     * @param TblTask $tblTask
     * @return false|TblDivision[]
     */
    public function getDivisionAllByTask(TblTask $tblTask)
    {

        $resultList = array();
        $tblTestList = $this->getTestAllByTask($tblTask);
        if ($tblTestList) {
            foreach ($tblTestList as $tblTest) {
                if ($tblTest->getServiceTblDivision()) {
                    $resultList[$tblTest->getServiceTblDivision()->getId()] = $tblTest->getServiceTblDivision();
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblTask $tblTask
     * @param TblDivision $tblDivision
     */
    public function removeDivisionFromTask(
        TblTask $tblTask,
        TblDivision $tblDivision
    ) {

        $tblTestAllByTask = $this->getTestAllByTask($tblTask, $tblDivision);
        if ($tblTestAllByTask) {
            foreach ($tblTestAllByTask as $tblTest) {
                (new Data($this->getBinding()))->destroyTest($tblTest);
            }
        }
    }

    /**
     * @param TblTask $tblTask
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return TblTest
     */
    public function createTestToAppointedDateTask(TblTask $tblTask, TblDivisionSubject $tblDivisionSubject)
    {
        return (new Data($this->getBinding()))->createTest(
            $tblDivisionSubject->getTblDivision(),
            $tblDivisionSubject->getServiceTblSubject(),
            $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null,
            null,
            null,
            $tblTask->getTblTestType(),
            $tblTask,
            '',
            $tblTask->getDate()
        );
    }

    /**
     * @param TblTask $tblTask
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup $tblSubjectGroup
     *
     * @return bool
     */
    public function existsTestByTask(
        TblTask $tblTask,
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup = null
    ) {

        return (new Data($this->getBinding()))->existsTestByTask($tblTask, $tblDivision, $tblSubject, $tblSubjectGroup);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return false|TblDivision[]
     */
    public function getTestAllByDivision(TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->getTestAllByDivision($tblDivision);
    }

    /**
     * @param TblTest $tblTest
     *
     * @return bool
     */
    public function destroyTest(TblTest $tblTest)
    {
        if (($tblTestLinkList = $tblTest->getLinkedTestAll())) {
            foreach ($tblTestLinkList as $tblTestItem) {
                (new Data($this->getBinding()))->destroyTest($tblTestItem);
            }
        }

        return (new Data($this->getBinding()))->destroyTest($tblTest);
    }

    /**
     * @param TblTask $tblTask
     *
     * @return bool
     */
    public function destroyTask(TblTask $tblTask)
    {

        return (new Data($this->getBinding()))->destroyTask($tblTask);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblTestType $tblTestType
     * @return false|TblTask[]
     */
    public function getTaskAllByDivision(TblDivision $tblDivision, TblTestType $tblTestType)
    {

        return (new Data($this->getBinding()))->getTaskAllByDivision($tblDivision, $tblTestType);
    }

    /**
     * @param TblYear $tblYear
     * @param TblDivisionSubject $tblDivisionSubjectSelected
     *
     * @return bool|Panel
     */
    public function getTestLinkPanel(
        TblYear $tblYear,
        TblDivisionSubject $tblDivisionSubjectSelected
    ) {
        $panel = false;
        if ($tblDivisionSubjectSelected !== null) {
            $tblPerson = false;
            $tblAccount = Account::useService()->getAccountBySession();
            if ($tblAccount) {
                $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
                if ($tblPersonAllByAccount) {
                    $tblPerson = $tblPersonAllByAccount[0];
                }
            }

            $list = array();
            if ($tblPerson) {
                $tblSubjectTeacherList = Division::useService()->getSubjectTeacherAllByTeacher($tblPerson);
                if ($tblSubjectTeacherList) {
                    foreach ($tblSubjectTeacherList as $tblSubjectTeacher) {
                        if (($tblDivisionSubject = $tblSubjectTeacher->getTblDivisionSubject())) {
                            if (($tblDivision = $tblDivisionSubject->getTblDivision())
                                && $tblDivision->getServiceTblYear()
                                && $tblYear->getId() == $tblDivision->getServiceTblYear()
                                && ($tblSubject = $tblDivisionSubject->getServiceTblSubject())
                                && ($tblDivisionSubjectSelected->getServiceTblSubject())
                                && ($tblSubject->getId() == $tblDivisionSubjectSelected->getServiceTblSubject()->getId())
                            ) {

                                if (!$tblDivisionSubject->getTblSubjectGroup()
                                    && ($tblDivisionSubjectListHavingGroup = Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
                                        $tblDivision,
                                        $tblSubject))
                                ) {
                                    foreach ($tblDivisionSubjectListHavingGroup as $groupDivisionSubject) {
                                        if ($groupDivisionSubject->getId() !== $tblDivisionSubjectSelected->getId()) {
                                            $list[$groupDivisionSubject->getId()] = array(
                                                'tblDivision' => $groupDivisionSubject->getTblDivision(),
                                                'tblSubject' => $groupDivisionSubject->getServiceTblSubject(),
                                                'tblSubjectGroup' => $groupDivisionSubject->getTblSubjectGroup()
                                            );
                                        }
                                    }
                                } else {
                                    if ($tblDivisionSubject->getId() !== $tblDivisionSubjectSelected->getId()) {
                                        $list[$tblDivisionSubject->getId()] = array(
                                            'tblDivision' => $tblDivision,
                                            'tblSubject' => $tblSubject,
                                            'tblSubjectGroup' => $tblDivisionSubject->getTblSubjectGroup()
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if (!empty($list)) {
                $itemList = array();
                foreach ($list as $key => $item) {
                    /** @var TblDivision $division */
                    $division = $item['tblDivision'];
                    /** @var TblSubject $subject */
                    $subject = $item['tblSubject'];
                    /** @var TblSubjectGroup | false $group */
                    $group = $item['tblSubjectGroup'];
                    $name = $division->getDisplayName() . ' - ' . $subject->getAcronym()
                        . ($group ? ' - ' . $group->getName() : '');
                    $itemList[$name] =
                        new CheckBox(
                            'Test[Link][' . $key . ']',
                            $name,
                            1
                        );
                }
                ksort($itemList);
                $panel = new Panel(
                    'Leistungsüberprüfungen verknüpfen',
                    $itemList,
                    Panel::PANEL_TYPE_PRIMARY
                );
            }
        }

        return $panel;
    }

    /**
     * @param TblTest $tblTest
     * @param int $LinkId
     *
     * @return TblTestLink
     */
    public function createTestLink(TblTest $tblTest, $LinkId)
    {

        return (new Data($this->getBinding()))->createTestLink($tblTest, $LinkId);
    }

    /**
     * @return int
     */
    public function getNextLinkId()
    {

        return (new Data($this->getBinding()))->getNextLinkId();
    }

    /**
     * @param TblTest $tblTest
     * @return false | TblTest[]
     */
    public function getTestLinkAllByTest(TblTest $tblTest)
    {

        return (new Data($this->getBinding()))->getTestLinkAllByTest($tblTest);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblTask $tblTask
     *
     * @return false|TblTest[]
     */
    public function getTestListBy(TblDivision $tblDivision, TblSubject $tblSubject, TblTask $tblTask)
    {

        return (new Data($this->getBinding()))->getTestListBy($tblDivision, $tblSubject, $tblTask);
    }

    /**
     * @param TblGradeType $tblGradeType
     *
     * @return bool
     */
    public function isGradeTypeUsed(TblGradeType $tblGradeType)
    {

        return (new Data($this->getBinding()))->isGradeTypeUsed($tblGradeType);
    }

    /**
     * @param TblTask $tblTask
     * @param bool $IsLocked
     *
     * @return bool
     */
    public function setTaskLocked(TblTask $tblTask, $IsLocked = true)
    {

        return (new Data($this->getBinding()))->updateTask(
            $tblTask,
            $tblTask->getTblTestType(),
            $tblTask->getName(),
            $tblTask->getDate() ? $tblTask->getDate() : null,
            $tblTask->getFromDate() ? $tblTask->getFromDate() : null,
            $tblTask->getToDate() ? $tblTask->getToDate() : null,
            $tblTask->getServiceTblPeriod() ? $tblTask->getServiceTblPeriod() : null,
            $tblTask->getServiceTblScoreType() ? $tblTask->getServiceTblScoreType() : null,
            $IsLocked
        );
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool
     */
    public function existsTestByDivisionSubject(TblDivisionSubject $tblDivisionSubject)
    {

        return (new Data($this->getBinding()))->existsTestByDivisionSubject($tblDivisionSubject);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|Layout
     */
    public function getTeacherWelcomeGradeTask(TblPerson $tblPerson)
    {

        $appointedDateTaskList = array();
        $behaviorTask = array();
        $futureAppointedDateTaskList = array();
        $futureBehaviorTask = array();
        if (($tblSubjectTeacherList = Division::useService()->getSubjectTeacherAllByPerson($tblPerson))) {
            foreach ($tblSubjectTeacherList as $tblSubjectTeacher) {
                if (($tblDivisionSubject = $tblSubjectTeacher->getTblDivisionSubject())
                    && ($tblDivision = $tblDivisionSubject->getTblDivision())
                    && ($tblSubject = $tblDivisionSubject->getServiceTblSubject())
                ) {

                    if ($tblDivisionSubject->getHasGrading()) {
                        $appointedDateTaskList = $this->setCurrentTaskList(
                            $tblDivision,
                            $tblSubject,
                            ($tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup())
                                ? $tblSubjectGroup : null, $this->getTestTypeByIdentifier('APPOINTED_DATE_TASK'),
                            $appointedDateTaskList);

                        $futureAppointedDateTaskList = $this->setFutureTaskList(
                            $tblDivision,
                            $tblSubject,
                            ($tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup())
                                ? $tblSubjectGroup : null, $this->getTestTypeByIdentifier('APPOINTED_DATE_TASK'),
                            $futureAppointedDateTaskList);
                    }

                    if ($tblDivisionSubject->getHasGrading() || (($tblSetting = Consumer::useService()->getSetting(
                                'Education', 'Graduation', 'Evaluation', 'HasBehaviorGradesForSubjectsWithNoGrading'
                            ))
                            && $tblSetting->getValue())
                    ) {
                        $behaviorTask = $this->setCurrentTaskList(
                            $tblDivision,
                            $tblSubject,
                            ($tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup())
                                ? $tblSubjectGroup : null, $this->getTestTypeByIdentifier('BEHAVIOR_TASK'),
                            $behaviorTask);


                        $futureBehaviorTask = $this->setFutureTaskList(
                            $tblDivision,
                            $tblSubject,
                            ($tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup())
                                ? $tblSubjectGroup : null, $this->getTestTypeByIdentifier('BEHAVIOR_TASK'),
                            $futureBehaviorTask);
                    }
                }
            }
        }

        // Klassenlehrer für Kopfnotenvorschlag
        if (($tblSetting = Consumer::useService()->getSetting('Education', 'Graduation', 'Evaluation',
                'ShowProposalBehaviorGrade'))
            && $tblSetting->getValue()
            && ($tblTestType = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR_TASK'))
            && ($tblYearList = Term::useService()->getYearByNow())
        ) {
            $now = new DateTime('now');
            $tblCurrentYear = reset($tblYearList);
            if (($tblDivisionTeacherList = Division::useService()->getDivisionTeacherAllByTeacher($tblPerson))) {
                foreach ($tblDivisionTeacherList as $tblDivisionTeacher) {
                    if (($tblDivisionItem = $tblDivisionTeacher->getTblDivision())
                        && ($tblYear = $tblDivisionItem->getServiceTblYear())
                        && $tblYear->getId() == $tblCurrentYear->getId()
                    ) {
                        if (($tblTaskList = Evaluation::useService()->getTaskAllByDivision($tblDivisionItem, $tblTestType))) {
                            foreach ($tblTaskList as $tblTask) {
                                $taskFromDate = new DateTime($tblTask->getFromDate());
                                $taskToDate = new DateTime($tblTask->getToDate());

                                // current Task
                                if ($now > $taskFromDate
                                    && $now < ($taskToDate->add(new DateInterval('P1D')))
                                ) {
                                    $countGrades = 0;
                                    if (($tblGradeList = Gradebook::useService()->getProposalBehaviorGradeAllBy($tblDivisionItem, $tblTask))) {
                                        foreach ($tblGradeList as $tblProposalBehaviorGrade) {
                                            if ($tblProposalBehaviorGrade->getDisplayGrade() !== '') {
                                                $countGrades++;
                                            }
                                        }
                                    }

                                    $countPersons = 0;
                                    if (($tblDivisionStudentList = Division::useService()->getDivisionStudentAllByDivision($tblDivisionItem))) {
                                        $countPersons = 4 * count($tblDivisionStudentList);
                                    }

                                    $text = ' ' . $tblDivisionItem->getDisplayName() . ' (Vorschlag-KL): '
                                        . $countGrades . ' von ' . $countPersons . ' Zensuren vergeben';
                                    $behaviorTask[$tblTask->getId()][$tblDivisionItem->getDisplayName()]['Message'] =
                                        new PullClear(($countGrades < $countPersons
                                                ? new Warning(new Exclamation() . $text)
                                                : new \SPHERE\Common\Frontend\Text\Repository\Success(new \SPHERE\Common\Frontend\Icon\Repository\Success()
                                                    . $text))
                                            . new PullRight(new Standard(
                                                '',
                                                '/Education/Graduation/Evaluation/Test/Teacher/Proposal/Grade/Edit',
                                                new Extern(),
                                                array(
                                                    'DivisionId' => $tblDivisionItem->getId(),
                                                    'TaskId' => $tblTask->getId()
                                                ),
                                                'Zur Noteneingabe wechseln'
                                            )));
                                }
                            }
                        }
                    }
                }
            }
        }

        $columns = array();
        $columns = $this->setWelcomeContent($appointedDateTaskList,
            $columns);
        $columns = $this->setWelcomeContent($behaviorTask,
            $columns);
        $columns = $this->setWelcomeContent($futureAppointedDateTaskList,
            $columns, true);
        $columns = $this->setWelcomeContent($futureBehaviorTask,
            $columns, true);

        if (empty($columns)) {
            return false;
        } else {
            $LayoutRowList = array();
            $LayoutRowCount = 0;
            $LayoutRow = null;
            /**
             * @var LayoutColumn $tblPhone
             */
            foreach ($columns as $column) {
                if ($LayoutRowCount % 2 == 0) {
                    $LayoutRow = new LayoutRow(array());
                    $LayoutRowList[] = $LayoutRow;
                }
                $LayoutRow->addColumn($column);
                $LayoutRowCount++;
            }

            return new Layout(new LayoutGroup($LayoutRowList));
        }
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup|null $tblSubjectGroup
     * @param TblTestType $tblTestType
     * @param $taskList
     *
     * @return array
     */
    private function setCurrentTaskList(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup = null,
        TblTestType $tblTestType,
        $taskList
    ) {
        $tblTestList = $this->getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
            $tblDivision,
            $tblSubject,
            $tblTestType,
            null,
            $tblSubjectGroup
        );

        $resultList = $taskList;
        $now = new DateTime('now');
        if ($tblTestList) {
            /** @var TblTest $tblTest */
            foreach ($tblTestList as $tblTest) {
                $tblSubjectGroup = $tblTest->getServiceTblSubjectGroup();
                if (($tblTask = $tblTest->getTblTask())
                    && $tblTask->getFromDate()
                    && $tblTask->getToDate()
                    && ($fromDate = new DateTime($tblTask->getFromDate()))
                    && ($toDate = new DateTime($tblTask->getToDate()))
                    && $now > $fromDate
                    && $now < ($toDate->add(new DateInterval('P1D')))
                ) {

                    $countPersons = 0;
                    if ($tblSubjectGroup
                        && ($tblDivisionSubjectTemp = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
                            $tblDivision,
                            $tblSubject,
                            $tblSubjectGroup
                        ))
                    ) {
                        if (($tblSubjectStudentList = Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubjectTemp))) {
                            foreach ($tblSubjectStudentList as $tblSubjectStudent) {
                                if ($tblSubjectStudent->getServiceTblPerson()
                                    && ($tblDivisionStudent = Division::useService()->getDivisionStudentByDivisionAndPerson(
                                        $tblDivision, $tblSubjectStudent->getServiceTblPerson()
                                    ))
                                    && !$tblDivisionStudent->isInActiveByDateTime(new DateTime($tblTask->getDate()))
                                ) {
                                    $countPersons++;
                                }
                            }
                        }
                    } elseif (!$tblSubjectGroup
                        && ($tblDivisionStudentList = Division::useService()->getDivisionStudentAllByDivision($tblDivision))
                    ) {
                        foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                            if (($tblDivisionStudent->getServiceTblPerson())) {
                                $countPersons++;
                            }
                        }
                    }

                    $countGrades = 0;
                    if (($tblGradeList = Gradebook::useService()->getGradeAllByTest($tblTest))) {
                        foreach ($tblGradeList as $tblGrade) {
                            if ($tblGrade->getServiceTblPerson()
                                && $tblGrade !== null & $tblGrade !== ''
                            ) {
                                $countGrades++;
                            }
                        }
                    }

                    $tblGradeType = $tblTest->getServiceTblGradeType();

                    if ($tblTestType->getIdentifier() == 'APPOINTED_DATE_TASK') {
                        $text = ' ' . $tblDivision->getDisplayName()
                            . ' ' . $tblSubject->getAcronym()
                            . ' ' . $tblSubject->getName()
//                            . ($tblGradeType ? ' ' . $tblGradeType->getName() : '')
                            . ($tblSubjectGroup ? ' (' . $tblSubjectGroup->getName() . ')' : '')
                            . ': ' . $countGrades . ' von ' . $countPersons . ' Zensuren vergeben';
                        $taskList[$tblTask->getId()][$tblDivision->getDisplayName()
                        . $tblSubject->getAcronym()
                        . ($tblSubjectGroup ? $tblSubjectGroup->getName() : '')]['Message'] =
                            new PullClear(($countGrades < $countPersons
                                    ? new Warning(new Exclamation() . $text)
                                    : new \SPHERE\Common\Frontend\Text\Repository\Success(new \SPHERE\Common\Frontend\Icon\Repository\Success()
                                        . $text))
                                . new PullRight(new Standard(
                                    '',
                                    '/Education/Graduation/Evaluation/Test/Teacher/Grade/Edit',
                                    new Extern(),
                                    array(
                                        'Id' => $tblTest->getId()
                                    ),
                                    'Zur Noteneingabe wechseln'
                                )));
                    } else {
                        if ($tblGradeType && $tblGradeType->getName() == 'Betragen') {
                            $taskList[$tblTask->getId()]
                            [$tblDivision->getDisplayName()
                            . $tblSubject->getAcronym()
                            . ($tblSubjectGroup ? $tblSubjectGroup->getName() : '')]
                            ['LinkId'] = $tblTest->getId();
                        }

                        if (!isset($taskList[$tblTask->getId()][$tblDivision->getDisplayName() . $tblSubject->getAcronym()
                            . ($tblSubjectGroup ? $tblSubjectGroup->getName() : '')]['DivisionSubject'])
                        ) {
                            $taskList[$tblTask->getId()][$tblDivision->getDisplayName() . $tblSubject->getAcronym()
                            . ($tblSubjectGroup ? $tblSubjectGroup->getName() : '')]['DivisionSubject']
                                = ' ' . $tblDivision->getDisplayName()
                                . ' ' . $tblSubject->getAcronym()
                                . ' ' . $tblSubject->getName()
                                . ($tblSubjectGroup ? ' (' . $tblSubjectGroup->getName() . ')' : '');
                        }

                        if (isset($taskList[$tblTask->getId()][$tblDivision->getDisplayName() . $tblSubject->getAcronym()
                            . ($tblSubjectGroup ? $tblSubjectGroup->getName() : '')]['CountPersons'])
                        ) {
                            $taskList[$tblTask->getId()][$tblDivision->getDisplayName() . $tblSubject->getAcronym()
                            . ($tblSubjectGroup ? $tblSubjectGroup->getName() : '')]['CountPersons'] += $countPersons;
                        } else {
                            $taskList[$tblTask->getId()][$tblDivision->getDisplayName() . $tblSubject->getAcronym()
                            . ($tblSubjectGroup ? $tblSubjectGroup->getName() : '')]['CountPersons'] = $countPersons;
                        }

                        if (isset($taskList[$tblTask->getId()][$tblDivision->getDisplayName() . $tblSubject->getAcronym()
                            . ($tblSubjectGroup ? $tblSubjectGroup->getName() : '')]['CountGrades'])
                        ) {
                            $taskList[$tblTask->getId()][$tblDivision->getDisplayName() . $tblSubject->getAcronym()
                            . ($tblSubjectGroup ? $tblSubjectGroup->getName() : '')]['CountGrades'] += $countGrades;
                        } else {
                            $taskList[$tblTask->getId()][$tblDivision->getDisplayName() . $tblSubject->getAcronym()
                            . ($tblSubjectGroup ? $tblSubjectGroup->getName() : '')]['CountGrades'] = $countGrades;
                        }
                    }
                }
            }
        }

        if ($tblTestType->getIdentifier() == 'BEHAVIOR_TASK') {
            foreach ($taskList as $taskId => $divisionSubjectArray) {
                if (($tblTask = Evaluation::useService()->getTaskById($taskId))) {
                    foreach ($divisionSubjectArray as $key => $testArray) {
                        $name = isset($testArray['DivisionSubject']) ? $testArray['DivisionSubject'] : '';
                        if (isset($testArray['CountPersons']) && isset($testArray['CountGrades'])) {
                            $countPersons = $testArray['CountPersons'];
                            $countGrades = $testArray['CountGrades'];
                            if (isset($testArray['LinkId'])) {
                                $link = new PullRight(new Standard(
                                    '',
                                    '/Education/Graduation/Evaluation/Test/Teacher/Grade/Edit',
                                    new Extern(),
                                    array(
                                        'Id' => $testArray['LinkId']
                                    ),
                                    'Zur Noteneingabe wechseln'
                                ));
                            } else {
                                $link = false;
                            }

                            $name .= ': ' . $countGrades . ' von ' . $countPersons . ' Zensuren vergeben';
                            $name = ($countGrades < $countPersons
                                ? new Warning(new Exclamation() . $name)
                                : new \SPHERE\Common\Frontend\Text\Repository\Success(new \SPHERE\Common\Frontend\Icon\Repository\Success()
                                    . $name));

                            $resultList[$tblTask->getId()][$key]['Message'] = $name . ($link ? $link : '');
                        }
                    }
                }
            }

            return $resultList;
        }

        return $taskList;
    }

    /**
     * @param $taskList
     * @param $columns
     * @param bool $isFuture
     *
     * @return array
     */
    private function setWelcomeContent($taskList, $columns, $isFuture = false)
    {
        foreach ($taskList as $taskId => $list) {
            if (($tblTask = Evaluation::useService()->getTaskById($taskId))
                && $tblTestType = $tblTask->getTblTestType()
            ) {
                if ($isFuture) {
                    $panel = new Panel(
                        ($tblTestType->getIdentifier() == 'APPOINTED_DATE_TASK'
                            ? 'Nächster Stichtagsnotenauftrag '
                            : 'Nächster Kopfnotenauftrag '),
                        array(
                            new Muted('Stichtag: ' . $tblTask->getDate()),
                            new Muted('Bearbeitungszeitraum: ' . $tblTask->getFromDate() . ' - ' . $tblTask->getToDate())
                        ),
                        Panel::PANEL_TYPE_INFO
                    );
                    $columns[] = new LayoutColumn($panel, 6);
                } else {
                    ksort($list);
                    $messageList = array();
                    foreach ($list as $divisionSubject) {
                        if (isset($divisionSubject['Message'])) {
                            $messageList[] = $divisionSubject['Message'];
                        }
                    }
                    array_unshift($messageList,
                        new Muted('Bearbeitungszeitraum: ' . $tblTask->getFromDate() . ' - ' . $tblTask->getToDate()));
                    array_unshift($messageList, new Muted('Stichtag: ' . $tblTask->getDate()));
                    $panel = new Panel(
                        ($tblTestType->getIdentifier() == 'APPOINTED_DATE_TASK'
                            ? 'Aktueller Stichtagsnotenauftrag '
                            : 'Aktueller Kopfnotenauftrag '),
                        $messageList,
                        Panel::PANEL_TYPE_INFO
                    );
                    $columns[] = new LayoutColumn($panel, 6);
                }
            }
        }
        return $columns;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup|null $tblSubjectGroup
     * @param TblTestType $tblTestType
     * @param $taskList
     * @return mixed
     */
    private function setFutureTaskList(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup = null,
        TblTestType $tblTestType,
        $taskList
    ) {
        $tblTestList = $this->getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
            $tblDivision,
            $tblSubject,
            $tblTestType,
            null,
            $tblSubjectGroup
        );

        $now = new DateTime('now');
        if ($tblTestList) {
            /** @var TblTest $tblTest */
            foreach ($tblTestList as $tblTest) {
                if (($tblTask = $tblTest->getTblTask())
                    && $tblTask->getFromDate()
                    && $tblTask->getToDate()
                    && ($fromDate = new DateTime($tblTask->getFromDate()))
                    && $now < $fromDate
                    && $now > ($fromDate->sub(new DateInterval('P7D')))
                ) {
                    $taskList[$tblTask->getId()] = $tblTask;
                }
            }
        }

        return $taskList;
    }

    /**
     * @param $tblTestList
     *
     * @return array
     */
    public function sortTestList($tblTestList)
    {

        if (($tblSetting = Consumer::useService()->getSetting(
                'Education', 'Graduation', 'Gradebook', 'SortHighlighted'
            ))
            && $tblSetting->getValue()
        ) {
            // Sortierung nach Großen (fettmarkiert) und Klein Noten
            $highlightedTests = array();
            $notHighlightedTests = array();
            $countTests = 1;
            $isHighlightedSortedRight = true;
            if (($tblSettingSortedRight = Consumer::useService()->getSetting(
                'Education', 'Graduation', 'Gradebook', 'IsHighlightedSortedRight'
            ))
            ) {
                $isHighlightedSortedRight = $tblSettingSortedRight->getValue();
            }
            /** @var TblTest $tblTestItem */
            foreach ($tblTestList as $tblTestItem) {
                if (($tblGradeType = $tblTestItem->getServiceTblGradeType())) {
                    if ($tblGradeType->isHighlighted()) {
                        $highlightedTests[$countTests++] = $tblTestItem;
                    } else {
                        $notHighlightedTests[$countTests++] = $tblTestItem;
                    }
                }
            }

            $tblTestList = array();
            if (!empty($notHighlightedTests)) {
                $tblTestList = $this->getSorter($notHighlightedTests)->sortObjectBy('Date', new DateTimeSorter());
            }
            if (!empty($highlightedTests)) {
                $highlightedTests = $this->getSorter($highlightedTests)->sortObjectBy('Date', new DateTimeSorter());

                if ($isHighlightedSortedRight) {
                    $tblTestList = array_merge($tblTestList, $highlightedTests);
                } else {
                    $tblTestList = array_merge($highlightedTests, $tblTestList);
                }
            }
        } else {
            // Sortierung der Tests nach Datum
            $tblTestList = $this->getSorter($tblTestList)->sortObjectBy('Date', new DateTimeSorter());
        }

        return $tblTestList;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblPeriod $tblPeriod
     *
     * @return array|bool
     */
    public function getHighlightedTestList(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblPeriod $tblPeriod
    ) {

        $list = array();

        if (($tblTestType = $this->getTestTypeByIdentifier('TEST'))
            && ($tblTestList = $this->getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
            $tblDivision, $tblSubject, $tblTestType, $tblPeriod
        ))) {
            // Sortieren
            $tblTestList = $this->getSorter($tblTestList)->sortObjectBy('DateForSorter', new DateTimeSorter());
            /** @var TblTest $tblTest */
            foreach ($tblTestList as $tblTest) {
                if (($tblGradeType = $tblTest->getServiceTblGradeType())
                    && $tblGradeType->isHighlighted()
                ) {
                    $list[$tblTest->getId()] = $tblTest;
                }
            }
        }

        return empty($list) ? false : $list;
    }

    /**
     * @param $testArray
     *
     * @return array
     */
    public function getLayoutRowsForTestPlanning($testArray)
    {
        $preview = array();
        if (!empty($testArray)) {
            $trans = array(
                'Mon' => 'Mo',
                'Tue' => 'Di',
                'Wed' => 'Mi',
                'Thu' => 'Do',
                'Fri' => 'Fr',
                'Sat' => 'Sa',
                'Sun' => 'So',
            );
            $columnCount = 0;
            $row = array();
            foreach ($testArray as $calendarWeek => $testList) {
                $panelData = array();
                $date = new DateTime();
                if (!empty($testList)) {
                    /** @var TblTest $tblTest */
                    foreach ($testList as $tblTest) {
                        if (($tblSubject = $tblTest->getServiceTblSubject())
                            && ($tblDivisionTemp = $tblTest->getServiceTblDivision())
                            && ($tblGradeType = $tblTest->getServiceTblGradeType())
                        ) {
                            $tblSubjectGroup = $tblTest->getServiceTblSubjectGroup();
                            $TeacherAcronymList = array();
                            $tblDivisionSubjectMain = false;
                            if (!$tblSubjectGroup) {
                                $tblSubjectGroup = null;
                            } else {
                                $tblDivisionSubjectMain = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup($tblDivisionTemp,
                                    $tblSubject, null);
                            }
                            $tblDivisionSubjectTeacher = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup($tblDivisionTemp,
                                $tblSubject, $tblSubjectGroup);
                            if ($tblDivisionSubjectTeacher) {
                                // Teacher Group (if exist) else Teacher Subject
                                $tblPersonList = Division::useService()->getTeacherAllByDivisionSubject($tblDivisionSubjectTeacher);
                                if ($tblPersonList) {
                                    foreach ($tblPersonList as $tblPerson) {
                                        $TeacherAcronym = new ToolTip(new Small(new NotAvailable())
                                            ,
                                            'Lehrer ' . $tblPerson->getLastFirstName() . ' besitzt kein Lehrerkürzel');
                                        $tblTeacher = Teacher::useService()->getTeacherByPerson($tblPerson);
                                        if ($tblTeacher) {
                                            $TeacherAcronym = $tblTeacher->getAcronym();
                                        }
                                        $TeacherAcronymList[] = $TeacherAcronym;
                                    }
                                }
                            }
                            if ($tblDivisionSubjectMain) {
                                // Teacher Subject (if Group exist)
                                $tblPersonListMain = Division::useService()->getTeacherAllByDivisionSubject($tblDivisionSubjectMain);
                                if ($tblPersonListMain) {
                                    foreach ($tblPersonListMain as $tblPerson) {
                                        $TeacherAcronym = new ToolTip(new Small(new NotAvailable())
                                            ,
                                            'Lehrer ' . $tblPerson->getLastFirstName() . ' besitzt kein Lehrerkürzel');
                                        $tblTeacher = Teacher::useService()->getTeacherByPerson($tblPerson);
                                        if ($tblTeacher) {
                                            $TeacherAcronym = $tblTeacher->getAcronym();
                                        }
                                        $TeacherAcronymList[] = $TeacherAcronym;
                                    }
                                }
                            }

                            // create Teacher string
                            if (!empty($TeacherAcronymList)) {
                                // remove dublicates
                                $TeacherAcronymList = array_unique($TeacherAcronymList);
                                $TeacherAcronym = implode(', ', $TeacherAcronymList);
                            } else {
                                $TeacherAcronym = new ToolTip(new Small(new NotAvailable())
                                    , 'Kein Lehrauftrag vorhanden');
                            }

                            $content = $tblDivisionTemp->getDisplayName() . ' '
                                . $tblSubject->getAcronym() . ' '
                                . ($tblSubjectGroup
                                    ? '(' . $tblSubjectGroup->getName() . ') ' : '')
                                . $tblGradeType->getCode() . ' '
                                . $tblTest->getDescription() . ' ('
                                . strtr(date('D', strtotime($tblTest->getDate())), $trans) . ' ' . date('d.m.y',
                                    strtotime($tblTest->getDate())) . ') - ' . $TeacherAcronym;

                            $panelData[] = new ToolTip($tblGradeType->isHighlighted()
                                ? new Bold($content) : $content, 'Erstellt am: ' . $tblTest->getEntityCreate()->format('d.m.Y H:i'));

                            $date = new DateTime($tblTest->getDate());
                        }
                    }
                }

                $year = $date->format('Y');
                $week = $date->format('W');
                $monday = date('d.m.y', strtotime("$year-W{$week}"));
                $friday = date('d.m.y', strtotime("$year-W{$week}-5"));;

                $panel = new Panel(
                    new Bold('KW: ' . $calendarWeek) . new Muted(' &nbsp;&nbsp;&nbsp;(' . $monday . ' - ' . $friday . ')'),
                    $panelData,
                    $calendarWeek == date('W') ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_DEFAULT
                );
                $columnCount++;
                if ($columnCount > 4) {
                    $preview[] = new LayoutRow($row);
                    $row = array();
                    $columnCount = 1;
                }
                $row[] = new LayoutColumn($panel, 3);
            }
            if (!empty($row)) {
                $preview[] = new LayoutRow($row);
            }
        }

        return $preview;
    }

    /**
     * @param $tblDivisionList
     * @param TblGradeType|null $tblGradeType
     * @param bool $isHighlighted
     *
     * @return bool|TblTest[]
     */
    public function getTestListForPlanning($tblDivisionList, TblGradeType $tblGradeType = null, $isHighlighted = false)
    {

        $result = array();
        $tblGradeTypeList = array();

        if (($tblTestTypeTest = Evaluation::useService()->getTestTypeByIdentifier('TEST'))) {
            if ($tblGradeType) {
                $tblGradeTypeList[] = $tblGradeType;
            } else {
                if (($tblGradeTypeAll = Gradebook::useService()->getGradeTypeAllByTestType($tblTestTypeTest))) {
                    foreach ($tblGradeTypeAll as $tblGradeType) {
                        if ($tblGradeType->isHighlighted() == $isHighlighted) {
                            $tblGradeTypeList[] = $tblGradeType;
                        }
                    }
                }
            }

            foreach ($tblGradeTypeList as $item) {
                foreach ($tblDivisionList as $tblDivision) {
                    if (($tblTestList = $this->getTestAllByTestTypeAndGradeTypeAndDivision(
                        $tblTestTypeTest,
                        $item,
                        $tblDivision
                    ))) {
                        $result = array_merge($result, $tblTestList);
                    }
                }
            }
        }

        return empty($result) ? false : $result;
    }

    /**
     * @param TblDivision $tblDivision
     * @param DateTime $dateTime
     * @param string $interval
     * @return false|TblTask
     */
    public function getTaskByDivisionAndDateAndInterval(TblDivision $tblDivision, DateTime $dateTime, $interval = 'P30D')
    {
        // Notenaufträge zur Klasse
        if ($tblDivision
            && ($type =  Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK'))
            && ($tblTaskList = Evaluation::useService()->getTaskAllByDivision($tblDivision, $type))
        ) {
            $dateInterval = new DateInterval($interval);
            $dateFrom = new DateTime($dateTime->format('d.m.Y'));
            $dateFrom = $dateFrom->sub($dateInterval);
            $dateEnd = new DateTime($dateTime->format('d.m.Y'));
            $dateEnd = $dateEnd->add($dateInterval);

            foreach ($tblTaskList as $tblTask) {
                if (($taskDate = $tblTask->getDateTime())
                    && $taskDate >= $dateFrom
                    && $taskDate <= $dateEnd
                ) {
                    return $tblTask;
                }
            }
        }

        return false;
    }

    /**
     * @param TblTask $tblTask
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup|null $tblSubjectGroup
     *
     * @return false|TblTest
     */
    public function getTestByTaskAndDivisionAndSubject(
        TblTask $tblTask,
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup = null
    ) {
        return (new Data($this->getBinding()))->getTestByTaskAndDivisionAndSubject($tblTask, $tblDivision, $tblSubject, $tblSubjectGroup);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup|null $tblSubjectGroup
     *
     * @return false|TblTest[]
     */
    public function getTestDistinctListBy(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup = null
    ) {
        return (new Data($this->getBinding()))->getTestDistinctListBy($tblDivision, $tblSubject, $tblSubjectGroup);
    }

    /**
     * @param $tblTestList
     *
     * @return bool
     */
    public function destroyTestList(
        $tblTestList
    ): bool {
        return (new Data($this->getBinding()))->destroyTestList($tblTestList);
    }

    /**
     * @param TblTest[] $tblTestList
     *
     * @return bool
     */
    public function destroyTestLinkList(
        array $tblTestList
    ): bool {
        return (new Data($this->getBinding()))->destroyTestLinkList($tblTestList);
    }

    /**
     * @param $tblTestList
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup|null $tblSubjectGroup
     * @param TblPeriod|null $tblPeriod
     *
     * @return bool
     */
    public function updateTests(
        $tblTestList,
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup = null,
        TblPeriod $tblPeriod = null
    ): bool {
        return (new Data($this->getBinding()))->updateTests($tblTestList, $tblDivision, $tblSubject, $tblSubjectGroup, $tblPeriod);
    }
}