<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.07.2016
 * Time: 10:42
 */

namespace SPHERE\Application\Education\Certificate\Prepare;

use SPHERE\Application\Api\Education\Prepare\ApiPrepare;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Certificate\Setting\Setting;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeText;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Setting\Consumer\Consumer as ConsumerSetting;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\Editor;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Enable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\ResizeVertical;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Certificate\Prepare
 */
class Frontend extends FrontendSetting
{
    /**
     * @param null $PrepareId
     * @param null $GroupId
     * @param null $Route
     *
     * @return Stage|string
     */
    public function frontendOldPrepareShowSubjectGrades($PrepareId = null, $GroupId = null, $Route = null)
    {
        // todo remove
        $Stage = new Stage('Zeugnisvorbereitung', 'Fachnoten-Übersicht');

        $description = '';
        $tblPrepareList = false;
        $tblGroup = false;
        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))) {
            $tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate();
            if ($GroupId && ($tblGroup = Group::useService()->getGroupById($GroupId))) {
                $description = 'Gruppe ' . $tblGroup->getName();
                if (($tblGenerateCertificate)) {
                    $tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate);
                }
            } else {
                if (($tblDivisionTemp = $tblPrepare->getServiceTblDivision())) {
                    $description = 'Klasse ' . $tblDivisionTemp->getDisplayName();
                    $tblPrepareList = array(0 => $tblPrepare);
                }
            }

            $Stage->addButton(new Standard('Zurück', '/Education/Certificate/Prepare/Prepare/Preview',
                    new ChevronLeft(),
                    array(
                        'PrepareId' => $PrepareId,
                        'GroupId' => $GroupId,
                        'Route' => $Route
                    )
                )
            );

            $studentList = array();
            $tableHeaderList = array();
            $divisionList = array();
            $divisionPersonList = array();
            $averageGradeList = array();

            if ($tblPrepareList
                && $tblGenerateCertificate
                && ($tblTask = $tblGenerateCertificate->getServiceTblAppointedDateTask())
            ) {
                foreach ($tblPrepareList as $tblPrepareItem) {
                    if (($tblDivision = $tblPrepareItem->getServiceTblDivision())
                        && ($tblDivisionStudentAll = Division::useService()->getStudentAllByDivision($tblDivision))
                    ) {
                        // Alle Klassen ermitteln in denen der Schüler im Schuljahr Unterricht hat
                        foreach ($tblDivisionStudentAll as $tblPerson) {
                            if (!$tblGroup || Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                                $studentList[$tblPerson->getId()]['Name'] = $tblPerson->getLastFirstName();
                                if (($tblYear = $tblDivision->getServiceTblYear())
                                    && ($tblPersonDivisionList = Student::useService()->getDivisionListByPersonAndYearAndIsNotInActive(
                                        $tblPerson,
                                        $tblYear
                                    ))
                                ) {
                                    foreach ($tblPersonDivisionList as $tblDivisionItem) {
                                        if (!isset($divisionList[$tblDivisionItem->getId()])) {
                                            $divisionList[$tblDivisionItem->getId()] = $tblDivisionItem;
                                        }
                                    }
                                }
                                $divisionPersonList[$tblPerson->getId()] = 1;
                            }
                        }

                        foreach ($divisionList as $tblDivisionItem) {
                            if (($tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblTask,
                                $tblDivisionItem))
                            ) {
                                $tblType = $tblDivisionItem->getType();
                                $hasExams = ($Route == 'Diploma' && ($tblType && ($tblType->getShortName() == 'OS' || $tblType->getShortName() == 'FOS' || $tblType->getShortName() == 'BFS')));

                                foreach ($tblTestAllByTask as $tblTest) {
                                    $tblSubject = $tblTest->getServiceTblSubject();
                                    if ($tblSubject && $tblTest->getServiceTblDivision()) {
                                        $tableHeaderList[$tblSubject->getAcronym()] = $tblSubject->getAcronym();
                                        $studentList[0][$tblSubject->getAcronym()] = '';

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
                                                    if (!$tblGroup || Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                                                        if ($tblPerson && isset($divisionPersonList[$tblPerson->getId()])) {
                                                            if ($hasExams) {
                                                                $studentList = $this->setDiplomaGrade($tblPrepareItem,
                                                                    $tblPerson,
                                                                    $tblSubject, $studentList);
                                                            } else {
                                                                $studentList = $this->setTableContentForAppointedDateTask($tblDivision,
                                                                    $tblTest, $tblSubject, $tblPerson, $studentList,
                                                                    $tblDivisionSubject->getTblSubjectGroup()
                                                                        ? $tblDivisionSubject->getTblSubjectGroup() : null,
                                                                    $tblPrepareItem,
                                                                    $averageGradeList
                                                                );
                                                            }
                                                        }
                                                    }
                                                }

                                                // nicht vorhandene Schüler in der Gruppe auf leer setzten
                                                if ($tblDivisionStudentAll) {
                                                    foreach ($tblDivisionStudentAll as $tblPersonItem) {
                                                        if (!$tblGroup || Group::useService()->existsGroupPerson($tblGroup, $tblPersonItem)) {
                                                            if (!isset($studentList[$tblPersonItem->getId()][$tblSubject->getAcronym()])) {
                                                                $studentList[$tblPersonItem->getId()][$tblSubject->getAcronym()] = '';
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        } else {
                                            if ($tblDivisionStudentAll) {
                                                foreach ($tblDivisionStudentAll as $tblPerson) {
                                                    if (!$tblGroup || Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                                                        // nur Schüler der ausgewählten Klasse
                                                        if (isset($divisionPersonList[$tblPerson->getId()])) {
                                                            if ($hasExams) {
                                                                $studentList = $this->setDiplomaGrade($tblPrepareItem,
                                                                    $tblPerson,
                                                                    $tblSubject, $studentList);
                                                            } else {
                                                                $studentList = $this->setTableContentForAppointedDateTask($tblDivision,
                                                                    $tblTest, $tblSubject, $tblPerson, $studentList,
                                                                    null,
                                                                    $tblPrepareItem,
                                                                    $averageGradeList
                                                                );
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $count = 1;
            foreach ($studentList as $personId => $student){
                $studentList[$personId]['Number'] = $count++;
                foreach ($tableHeaderList as $column) {
                    if (!isset($studentList[$personId][$column])) {
                        $studentList[$personId][$column] = '';
                    }
                }
            }

            // Durchschnitte pro Fach-Klasse
            $studentList[0]['Number'] = '';
            $studentList[0]['Name'] = new Muted('&#216; Fach-Klasse');
            foreach ($averageGradeList as $subjectId => $grades) {
                $countGrades = count($grades);
                if (($item = Subject::useService()->getSubjectById($subjectId))) {
                    $studentList[0][$item->getAcronym()] = $countGrades > 0
                        ? round(array_sum($grades) / $countGrades, 2) : '';
                }
            }

            if (!empty($tableHeaderList)) {
                ksort($tableHeaderList);
                $prependTableHeaderList['Number'] = '#';
                $prependTableHeaderList['Name'] = 'Schüler';
                $tableHeaderList = $prependTableHeaderList + $tableHeaderList;
                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Panel(
                                        'Zeugnisvorbereitung',
                                        array(
                                            $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                            $description
                                        ),
                                        Panel::PANEL_TYPE_INFO
                                    ),
                                )),
                                new LayoutColumn(array(
                                    new TableData(
                                        $studentList, null, $tableHeaderList, null
                                    )
                                ))
                            ))
                        ))
                    ))
                );
            }

            return $Stage;

        } else {

            return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden', new Exclamation());
        }
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblTest $tblTest
     * @param TblSubject $tblSubject
     * @param TblPerson $tblPerson
     * @param $studentList
     * @param TblSubjectGroup $tblSubjectGroup
     * @param TblPrepareCertificate $tblPrepare
     *
     * @return  $studentList
     */
    private function setTableContentForAppointedDateTask(
        TblDivision $tblDivision,
        TblTest $tblTest,
        TblSubject $tblSubject,
        TblPerson $tblPerson,
        $studentList,
        TblSubjectGroup $tblSubjectGroup = null,
        TblPrepareCertificate $tblPrepare = null,
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
            $tblTask->getDate() ? $tblTask->getDate() : false
        );
        if (is_array($average)) {
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
                $studentList[$tblPerson->getId()][$tblSubject->getAcronym()] = $tblGradeText->getName();

                return $studentList;
            }

            $gradeValue = $tblGrade->getGrade();

            if ($gradeValue !== null && $gradeValue !== '') {
                $averageGradeList[$tblSubject->getId()][$tblPerson->getId()] = $gradeValue;
            }

            $isGradeInRange = true;
            if ($average !== ' ' && $average && $gradeValue !== null) {
                if (is_numeric($gradeValue)) {
                    $gradeFloat = floatval($gradeValue);
                    if (($gradeFloat - 0.5) <= $average && ($gradeFloat + 0.5) >= $average) {
                        $isGradeInRange = true;
                    } else {
                        $isGradeInRange = false;
                    }
                }
            }

            $withTrend = true;
            if ($tblPrepare
                && ($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare,
                    $tblGrade->getServiceTblPerson()))
                && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                && !$tblCertificate->isInformation()
            ) {
                $withTrend = false;
            }
            $gradeValue = $tblGrade->getDisplayGrade($withTrend);

            if ($isGradeInRange) {
                $gradeValue = new Success($gradeValue);
            } else {
                $gradeValue = new \SPHERE\Common\Frontend\Text\Repository\Danger($gradeValue);
            }

            $studentList[$tblPerson->getId()][$tblSubject->getAcronym()] = ($tblGrade->getGrade() !== null
                    ? $gradeValue : '') .
                (($average !== ' ' && $average) ? new Muted('&nbsp;&nbsp; &#216;' . $average) : '');
            return $studentList;
        } else {
            $studentList[$tblPerson->getId()][$tblSubject->getAcronym()] =
                new \SPHERE\Common\Frontend\Text\Repository\Warning('fehlt')
                . (($average !== ' ' && $average) ? new Muted('&nbsp;&nbsp; &#216;' . $average) : '');
            return $studentList;
        }
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblSubject $tblSubject
     * @param $studentList
     *
     * @return array
     */
    private function setDiplomaGrade(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblSubject $tblSubject,
        $studentList
    ) {
        if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('EN'))
            && ($tblPrepareAdditionalGrade = Prepare::useService()->getPrepareAdditionalGradeBy(
                $tblPrepare,
                $tblPerson,
                $tblSubject,
                $tblPrepareAdditionalGradeType
            ))
            && $tblPrepareAdditionalGrade->getGrade()
        ) {
            $studentList[$tblPerson->getId()][$tblSubject->getAcronym()] = $tblPrepareAdditionalGrade->getGrade();
        } else {
            $studentList[$tblPerson->getId()][$tblSubject->getAcronym()] =
                new \SPHERE\Common\Frontend\Text\Repository\Warning('fehlt');
        }

        return $studentList;
    }
}
