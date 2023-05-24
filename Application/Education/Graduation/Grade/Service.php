<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use DateTime;
use SPHERE\Application\Api\Document\Storage\ApiPersonPicture;
use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Graduation\Grade\Service\Data;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeText;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTestCourseLink;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTestGrade;
use SPHERE\Application\Education\Graduation\Grade\Service\Setup;
use SPHERE\Application\Education\Graduation\Grade\Service\VirtualTestTask;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\System\Extension\Repository\Sorter\DateTimeSorter;

class Service extends ServiceTask
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
     * @param TblYear $tblYear
     * @param array $tblDivisionList
     *
     * @return float
     */
    public function migrateTests(TblYear $tblYear, array $tblDivisionList): float
    {
        return (new Data($this->getBinding()))->migrateTests($tblYear, $tblDivisionList);
    }

    /**
     * @param $id
     *
     * @return false|TblGradeText
     */
    public function getGradeTextById($id)
    {
        return (new Data($this->getBinding()))->getGradeTextById($id);
    }

    /**
     * @param string $name
     *
     * @return false|TblGradeText
     */
    public function getGradeTextByName(string $name)
    {
        return (new Data($this->getBinding()))->getGradeTextByName($name);
    }

    /**
     * @return false|TblGradeText[]
     */
    public function getGradeTextAll()
    {
        return (new Data($this->getBinding()))->getGradeTextAll();
    }

    /**
     * @param $id
     *
     * @return false|TblTest
     */
    public function getTestById($id)
    {
        return (new Data($this->getBinding()))->getTestById($id);
    }

    /**
     * @param TblTest $tblTest
     *
     * @return false|TblDivisionCourse[]
     */
    public function getDivisionCourseListByTest(TblTest $tblTest)
    {
        return (new Data($this->getBinding()))->getDivisionCourseListByTest($tblTest);
    }

    /**
     * @param TblTest $tblTest
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return false|TblTestCourseLink
     */
    public function getTestCourseLinkBy(TblTest $tblTest, TblDivisionCourse $tblDivisionCourse)
    {
        return (new Data($this->getBinding()))->getTestCourseLinkBy($tblTest, $tblDivisionCourse);
    }

    /**
     * @return false|TblYear
     */
    public function getYear()
    {
        if (($tblAccountSetting = Consumer::useService()->getAccountSettingValue("GradeBookSelectedYearId"))
            && ($tblYear = Term::useService()->getYearById($tblAccountSetting))
        ) {
            return $tblYear;
        }

        if (($tblYearList = Term::useService()->getYearByNow())) {
            return current($tblYearList);
        }

        return false;
    }

    /**
     * @return string
     */
    public function getRole(): string
    {
        if (($role = Consumer::useService()->getAccountSettingValue("GradeBookRole"))) {
            // zur Sicherheit prüfen, ob das erforderliche Recht noch vorhanden ist
            if ($role == "Headmaster" && Access::useService()->hasAuthorization('/Education/Graduation/Grade/GradeBook/Headmaster')) {
                return $role;
            }
            // zur Sicherheit prüfen, ob das erforderliche Recht noch vorhanden ist
            if ($role == "AllReadonly" && Access::useService()->hasAuthorization('/Education/Graduation/Grade/GradeBook/AllReadOnly')) {
                return $role;
            }
        }

        return "Teacher";
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     *
     * @return bool
     */
    public function getIsEdit($DivisionCourseId, $SubjectId): bool
    {
        $role = $this->getRole();
        switch ($role) {
            case "Headmaster": return true;
            case "Teacher":
                // der Lehrer darf nur aktuelles Schuljahr bearbeiten und benötigt Lehrauftrag oder eigene Lerngruppe
                if (($tblYearSelected = $this->getYear())
                    && ($tblYearList = Term::useService()->getYearByNow())
                    && ($tblPerson = Account::useService()->getPersonByLogin())
                    && ($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
                    && ($tblSubject = Subject::useService()->getSubjectById($SubjectId))
                ) {
                    foreach ($tblYearList as $tblYear) {
                        if ($tblYear->getId() == $tblYearSelected->getId()
                            && ($tblYearFromDivisionCourse = $tblDivisionCourse->getServiceTblYear())
                            && $tblYear->getId() == $tblYearFromDivisionCourse->getId()
                        ) {
                            return $this->getHasTeacherLectureshipForDivisionCourseAndSubject($tblPerson, $tblDivisionCourse, $tblSubject);
                        }
                    }
                }
                return false;
            case "AllReadonly":
            default: return false;
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject $tblSubject
     *
     * @return bool
     */
    public function getHasTeacherLectureshipForDivisionCourseAndSubject(TblPerson $tblPerson, TblDivisionCourse $tblDivisionCourse, TblSubject $tblSubject): bool
    {
        // Lehrauftrag
        if (DivisionCourse::useService()->getTeacherLectureshipListBy(null, $tblPerson, $tblDivisionCourse, $tblSubject)) {
            return true;
        }
        // eigne Lerngruppe
        if ($tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_TEACHER_GROUP
            && ($tblTeacherList = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse,
                TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER))
        ) {
            foreach ($tblTeacherList as $tblTeacher) {
                if ($tblTeacher->getId() == $tblPerson->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param array $columnList
     * @param int $size
     *
     * @return array
     */
    public function getLayoutRowsByLayoutColumnList(array $columnList, int $size): array
    {
        $rowList = array();
        $rowCount = 0;
        $row = null;
        foreach ($columnList as $column) {
            if ($rowCount % (12 / $size) == 0) {
                $row = new LayoutRow(array());
                $rowList[] = $row;
            }
            $row->addColumn($column);
            $rowCount++;
        }

        return $rowList;
    }

    /**
     * @param $Data
     * @param TblDivisionCourse|null $tblDivisionCourse
     *
     * @return false|Form
     */
    public function checkFormTeacherGroup($Data, TblDivisionCourse $tblDivisionCourse = null)
    {
        $error = false;
        $form = Grade::useFrontend()->formTeacherGroup($tblDivisionCourse ? $tblDivisionCourse->getId() : null, false, $Data);

        $tblYear = $tblDivisionCourse ? $tblDivisionCourse->getServiceTblYear() : $this->getYear();

        if (!$tblDivisionCourse) {
            if (!isset($Data['Subject']) || !(Subject::useService()->getSubjectById($Data['Subject']))) {
                $form->setError('Data[Subject]', 'Bitte wählen Sie ein Fach aus');
                $error = true;
            }
        }

        if (!isset($Data['Name']) || empty($Data['Name'])) {
            $form->setError('Data[Name]', 'Bitte geben Sie einen Name ein');
            $error = true;
        }
        if (isset($Data['Name']) && $Data['Name'] != '') {
            // Prüfung ob name schon mal verwendet wird
            if ($tblYear && ($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear))) {
                foreach ($tblDivisionCourseList as $tblDivisionCourseItem) {
                    if ($tblDivisionCourse && $tblDivisionCourse->getId() == $tblDivisionCourseItem->getId()) {
                        continue;
                    }

                    if (strtolower($Data['Name']) == strtolower($tblDivisionCourseItem->getName())) {
                        $form->setError('Data[Name]', 'Ein Kurs mit diesem Name existiert bereits im Schuljahr');
                        $error = true;
                    }
                }
            }
        }

        return $error ? $form : false;
    }

    /**
     * @param $Data
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $Filter
     * @param $TestId
     *
     * @return false|Form
     */
    public function checkFormTest($Data, $DivisionCourseId, $SubjectId, $Filter, $TestId)
    {
        $error = false;
        $form = Grade::useFrontend()->formTest($DivisionCourseId, $SubjectId, $Filter, $TestId, false, $Data);

        if (!isset($Data['GradeType']) || !(Grade::useService()->getGradeTypeById($Data['GradeType']))) {
            $form->setError('Data[GradeType]', 'Bitte wählen Sie einen Zensuren-Typ aus');
            $error = true;
        }
        if (isset($Data['Date']) && empty($Data['Date'])) {
            $form->setError('Data[Date]', 'Bitte geben Sie ein Datum an');
            $error = true;
        }

        return $error ? $form : false;
    }

    /**
     * @param TblYear $tblYear
     * @param TblSubject $tblSubject
     * @param TblGradeType $tblGradeType
     * @param DateTime|null $Date
     * @param DateTime|null $FinishDate
     * @param DateTime|null $CorrectionDate
     * @param DateTime|null $ReturnDate
     * @param bool $IsContinues
     * @param string $Description
     * @param TblPerson|null $tblTeacher
     *
     * @return TblTest
     */
    public function createTest(TblYear $tblYear, TblSubject $tblSubject, TblGradeType $tblGradeType,
        ?DateTime $Date, ?DateTime $FinishDate, ?DateTime $CorrectionDate, ?DateTime $ReturnDate, bool $IsContinues, string $Description,
        ?TblPerson $tblTeacher): TblTest
    {
        return (new Data($this->getBinding()))->createTest($tblYear, $tblSubject, $tblGradeType, $Date, $FinishDate, $CorrectionDate, $ReturnDate, $IsContinues,
            $Description, $tblTeacher);
    }

    /**
     * @param TblTest $tblTest
     * @param TblGradeType $tblGradeType
     * @param DateTime|null $Date
     * @param DateTime|null $FinishDate
     * @param DateTime|null $CorrectionDate
     * @param DateTime|null $ReturnDate
     * @param bool $IsContinues
     * @param string $Description
     *
     * @return bool
     */
    public function updateTest(TblTest $tblTest, TblGradeType $tblGradeType,
        ?DateTime $Date, ?DateTime $FinishDate, ?DateTime $CorrectionDate, ?DateTime $ReturnDate, bool $IsContinues, string $Description): bool
    {
        return (new Data($this->getBinding()))->updateTest($tblTest, $tblGradeType, $Date, $FinishDate, $CorrectionDate, $ReturnDate, $IsContinues, $Description);
    }

    /**
     * @param TblTest $tblTest
     *
     * @return bool
     */
    public function deleteTest(TblTest $tblTest): bool
    {
        if (($tempList = $tblTest->getGrades())) {
            $this->softRemoveEntityList($tempList);
        }
        if (($tempList = (new Data($this->getBinding()))->getTestCourseLinkListByTest($tblTest))) {
            $this->softRemoveEntityList($tempList);
        }

        return $this->softRemoveEntityList(array($tblTest));
    }

    /**
     * @param array $tblEntityList
     *
     * @return bool
     */
    public function createEntityListBulk(array $tblEntityList): bool
    {
        return (new Data($this->getBinding()))->createEntityListBulk($tblEntityList);
    }

    /**
     * @param array $tblEntityList
     *
     * @return bool
     */
    public function updateEntityListBulk(array $tblEntityList): bool
    {
        return (new Data($this->getBinding()))->updateEntityListBulk($tblEntityList);
    }

    /**
     * @param array $tblEntityList
     *
     * @return bool
     */
    public function deleteEntityListBulk(array $tblEntityList): bool
    {
        return (new Data($this->getBinding()))->deleteEntityListBulk($tblEntityList);
    }

    /**
     * @param array $tblEntityList
     *
     * @return bool
     */
    public function softRemoveEntityList(array $tblEntityList): bool
    {
        return (new Data($this->getBinding()))->softRemoveEntityList($tblEntityList);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject $tblSubject
     *
     * @return TblTest[]|false
     */
    public function getTestListByDivisionCourseAndSubject(TblDivisionCourse $tblDivisionCourse, TblSubject $tblSubject)
    {
        return (new Data($this->getBinding()))->getTestListByDivisionCourseAndSubject($tblDivisionCourse, $tblSubject);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param DateTime $FromDate
     * @param DateTime $ToDate
     *
     * @return TblTest[]|false
     */
    public function getTestListBetween(TblDivisionCourse $tblDivisionCourse, DateTime $FromDate, DateTime $ToDate)
    {
        return (new Data($this->getBinding()))->getTestListBetween($tblDivisionCourse, $FromDate, $ToDate);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSubject $tblSubject
     *
     * @return TblTestGrade[]|false
     */
    public function getTestGradeListByPersonAndYearAndSubject(TblPerson $tblPerson, TblYear $tblYear, TblSubject $tblSubject)
    {
        return (new Data($this->getBinding()))->getTestGradeListByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSubject $tblSubject
     * @param DateTime $fromDate
     * @param DateTime $toDate
     *
     * @return TblTestGrade[]|false
     */
    public function getTestGradeListBetweenDateTimesByPersonAndYearAndSubject(TblPerson $tblPerson, TblYear $tblYear, TblSubject $tblSubject,
        DateTime $fromDate, DateTime $toDate)
    {
        return (new Data($this->getBinding()))->getTestGradeListBetweenDateTimesByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject, $fromDate, $toDate);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblSubject $tblSubject
     * @param DateTime $toDate
     *
     * @return TblTestGrade[]|false
     */
    public function getTestGradeListToDateTimeByPersonAndSubject(TblPerson $tblPerson, TblSubject $tblSubject, DateTime $toDate)
    {
        return (new Data($this->getBinding()))->getTestGradeListToDateTimeByPersonAndSubject($tblPerson, $tblSubject, $toDate);
    }

    /**
     * @param TblTest $tblTest
     *
     * @return false|TblTestGrade[]
     */
    public function getTestGradeListByTest(TblTest $tblTest)
    {
        return (new Data($this->getBinding()))->getTestGradeListByTest($tblTest);
    }

    /**
     * @param TblTest $tblTest
     * @param TblPerson $tblPerson
     *
     * @return false|TblTestGrade
     */
    public function getTestGradeByTestAndPerson(TblTest $tblTest, TblPerson $tblPerson)
    {
        return (new Data($this->getBinding()))->getTestGradeByTestAndPerson($tblTest, $tblPerson);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param array $integrationList
     * @param array $pictureList
     * @param array $courseList
     */
    public function setStudentInfo(TblPerson $tblPerson, TblYear $tblYear, array &$integrationList, array &$pictureList, array &$courseList)
    {
        // Integration
        if(Student::useService()->getIsSupportByPerson($tblPerson)) {
            $integrationList[$tblPerson->getId()] = (new Standard('', ApiSupportReadOnly::getEndpoint(), new EyeOpen()))
                ->ajaxPipelineOnClick(ApiSupportReadOnly::pipelineOpenOverViewModal($tblPerson->getId()));
        }

        // Picture
        if(($tblPersonPicture = Storage::useService()->getPersonPictureByPerson($tblPerson))){
            $pictureList[$tblPerson->getId()] = new Center((new Link($tblPersonPicture->getPicture(), $tblPerson->getId()))
                ->ajaxPipelineOnClick(ApiPersonPicture::pipelineShowPersonPicture($tblPerson->getId())));
        }

        // Course
        if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
            && ($tblCourse = $tblStudentEducation->getServiceTblCourse())
        ) {
            if ($tblCourse->getName() == 'Realschule') {
                $courseList[$tblPerson->getId()] = 'RS';
            } elseif ($tblCourse->getName() == 'Hauptschule') {
                $courseList[$tblPerson->getId()] = 'HS';
            }
        }
    }

    /**
     * @param $Data
     * @param TblTest $tblTest
     * @param TblYear $tblYear
     * @param TblSubject $tblSubject
     * @param $DivisionCourseId
     * @param $Filter
     *
     * @return false|Form
     */
    public function checkFormTestGrades($Data, TblTest $tblTest, TblYear $tblYear, TblSubject $tblSubject, $DivisionCourseId, $Filter)
    {
        $errorList = array();
        if ($Data) {
            foreach ($Data as $personId => $item) {
                if (($tblPerson = Person::useService()->getPersonById($personId))) {
                    $comment = trim($item['Comment']);
                    $grade = str_replace(',', '.', trim($item['Grade']));
                    $isNotAttendance = isset($item['Attendance']);
                    $date = !empty($item['Date']) ? new DateTime($item['Date']) : null;

                    $hasGradeValue = $grade === '0' || (!empty($grade) && $grade != -1);
                    $gradeValue = $isNotAttendance ? null : $grade;

                    // Bewertungssystem Pattern prüfen
                    if ($hasGradeValue
                        && ($tblScoreType = Grade::useService()->getScoreTypeByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject))
                        && ($pattern = $tblScoreType->getPattern())
                    ) {
                        if (!preg_match('!' . $pattern . '!is', $gradeValue)) {
                            $errorList[$personId]['Grade'] = true;
                        }
                    }

                    // Grund bei Noten-Änderung angeben
                    if (($hasGradeValue || $isNotAttendance)
                        && empty($comment)
                        && ($tblTestGrade = Grade::useService()->getTestGradeByTestAndPerson($tblTest, $tblPerson))
                        && $gradeValue != $tblTestGrade->getGrade()
                    ) {
                        $errorList[$personId]['Comment'] = true;
                    }

                    // Datum ist Pflicht, bei fortlaufendem Test ohne Datum
                    if ($hasGradeValue && $tblTest->getIsContinues() && !$tblTest->getFinishDate() && !$date) {
                        $errorList[$personId]['Date'] = true;
                    }
                }
            }
        }

        return empty($errorList) ? false : Grade::useFrontend()->formTestGrades($tblTest, $tblYear, $tblSubject, $DivisionCourseId, $Filter, false, $errorList);
    }

    /**
     * @param string|null $grade
     *
     * @return null|float
     */
    public function getGradeNumberValue(?string $grade) : ?float
    {
        if ($grade === null) {
            return null;
        }

        $grade = str_replace('+', '', $grade);
        $grade = str_replace('-', '', $grade);
        $grade = str_replace(',', '.', $grade);

        return is_numeric($grade) ? (float) $grade : null;
    }

    /**
     * @param float $sum
     * @param int $count
     * @param int $precision
     *
     * @return string
     */
    public function getGradeAverage(float $sum, int $count, int $precision = 2): string
    {
        if ($count > 0) {
            return str_replace('.', ',', round($sum / $count, $precision));
        }
        return '';
    }

    /**
     * @param string $average
     * @param int $precision
     *
     * @return string
     */
    public function getGradeAverageByString(string $average, int $precision = 0): string
    {
        $average = str_replace(',', '.', $average);
        return str_replace('.', ',', round($average, $precision));
    }

    /**
     * @param array $virtualTestTaskList
     * @param array $averagePeriodList
     *
     * @return array
     */
    public function getVirtualTestTaskListSorted(array $virtualTestTaskList, array $averagePeriodList): array
    {
        $isSortedByHighlighted = ($tblSetting = Consumer::useService()->getSetting('Education', 'Graduation', 'Gradebook', 'SortHighlighted'))
            && $tblSetting->getValue();
        $isSortedToRight = ($tblSetting = Consumer::useService()->getSetting('Education', 'Graduation', 'Gradebook', 'IsHighlightedSortedRight'))
            && $tblSetting->getValue();
        if ($isSortedByHighlighted) {
            $tempList = array();
            $resultList = array();
            /** @var VirtualTestTask $virtualTestTask */
            foreach ($virtualTestTaskList as $virtualTestTask) {
                switch ($virtualTestTask->getType()) {
                    case VirtualTestTask::TYPE_TEST:
                        $tblTest = $virtualTestTask->getTblTest();
                        $date = $tblTest->getFinishDate() ?: $tblTest->getDate();
                        $hasPeriodFound = false;
                        $count = 0;
                        if ($date) {
                            foreach ($averagePeriodList['Periods'] as $periodId => $count) {
                                if (($tblPeriod = Term::useService()->getPeriodById($periodId)) && $date <= $tblPeriod->getToDateTime()) {
                                    $hasPeriodFound = true;
                                    break;
                                }
                            }
                        }
                        if (!$hasPeriodFound) {
                            $count++;
                        }
                        if (($tblGradeType = $tblTest->getTblGradeType()) && $tblGradeType->getIsHighlighted()) {
                            $tempList[$count]['Highlighted'][] = $virtualTestTask;
                        } else {
                            $tempList[$count]['NotHighlighted'][] = $virtualTestTask;
                        }
                        break;
                    case VirtualTestTask::TYPE_TASK:
                        $tblTask = $virtualTestTask->getTblTask();
                        $date = $tblTask->getDate();
                        $hasPeriodFound = false;
                        $count = 0;
                        if ($date) {
                            foreach ($averagePeriodList['Periods'] as $periodId => $count) {
                                if (($tblPeriod = Term::useService()->getPeriodById($periodId)) && $date <= $tblPeriod->getToDateTime()) {
                                    $hasPeriodFound = true;
                                    break;
                                }
                            }
                        }
                        if (!$hasPeriodFound) {
                            $count++;
                        }
                        $tempList[$count]['Default'][] = $virtualTestTask;
                        break;
                    case VirtualTestTask::TYPE_PERIOD:
                        $tblPeriod = $virtualTestTask->getTblPeriod();
                        $hasPeriodFound = false;
                        $count = 0;
                        foreach ($averagePeriodList['Periods'] as $periodId => $count) {
                            if ($tblPeriod->getId() == $periodId) {
                                $hasPeriodFound = true;
                                break;
                            }
                        }
                        if (!$hasPeriodFound) {
                            $count++;
                        }
                        $tempList[$count]['Default'][] = $virtualTestTask;
                }
            }

            ksort($tempList);
            foreach ($tempList as $array) {
                $highlightedList = array();
                if (isset($array['Highlighted'])) {
                    $highlightedList = $this->getSorter($array['Highlighted'])->sortObjectBy('Date', new DateTimeSorter());
                }
                $notHighlightedList = array();
                if (isset($array['NotHighlighted'])) {
                    $notHighlightedList = $this->getSorter($array['NotHighlighted'])->sortObjectBy('Date', new DateTimeSorter());
                }
                $defaultList = array();
                if (isset($array['Default'])) {
                    $defaultList = $this->getSorter($array['Default'])->sortObjectBy('Date', new DateTimeSorter());
                }

                if ($isSortedToRight) {
                    if (!empty($notHighlightedList)) {
                        $resultList = array_merge($resultList, $notHighlightedList);
                    }
                    if (!empty($highlightedList)) {
                        $resultList = array_merge($resultList, $highlightedList);
                    }
                } else {
                    if (!empty($highlightedList)) {
                        $resultList = array_merge($resultList, $highlightedList);
                    }
                    if (!empty($notHighlightedList)) {
                        $resultList = array_merge($resultList, $notHighlightedList);
                    }
                }

                if (!empty($defaultList)) {
                    $resultList = array_merge($resultList, $defaultList);
                }
            }
            $virtualTestTaskList = $resultList;

        } else {
            $virtualTestTaskList = $this->getSorter($virtualTestTaskList)->sortObjectBy('Date', new DateTimeSorter());
        }

        return $virtualTestTaskList;
    }

    public function getGradeMirrorForToolTipByTest(TblTest $tblTest, TblScoreType $tblScoreType): string
    {
        $result = '';
        $gradeMirrorList = array();
        if ($tblScoreType->getIdentifier() !== 'VERBAL') {
            $hasMirror = false;
            if ($tblScoreType->getIdentifier() == 'GRADES'
                || $tblScoreType->getIdentifier() == 'GRADES_COMMA'
            ) {
                $hasMirror = true;
                for ($i = 1; $i < 7; $i++) {
                    $gradeMirrorList[$i] = 0;
                }
            } elseif ($tblScoreType->getIdentifier() == 'POINTS') {
                $hasMirror = true;
                for ($i = 0; $i < 16; $i++) {
                    $gradeMirrorList[$i] = 0;
                }
            }
            if ($hasMirror && ($tblTestGradeList = $tblTest->getGrades())) {
                foreach ($tblTestGradeList as $tblTestGrade) {
                    if (($gradeValue = $tblTestGrade->getGradeNumberValue()) !== null) {
                        // auf ganze Note runden
                        $gradeValue = intval(round($gradeValue));
                        $gradeMirrorList[$gradeValue]++;
                    }
                }

                $line[0] = '';
                $line[1] = '';
                foreach ($gradeMirrorList as $key => $value) {
                    $space = ($value > 9 && $key < 10) ? '&nbsp;&nbsp;&nbsp;' : '&nbsp;';
                    $line[0] .= $space . $key;
                    $space = ($value < 9 && $key > 9) ? '&nbsp;&nbsp;&nbsp;' : '&nbsp;';
                    $line[1] .= $space . $value;
                }

                $result = $line[0] . '<br />' . $line[1];
            }
        }

        return $result;
    }

    /**
     * @param TblTest $tblTest
     *
     * @return false|string
     */
    public function getGradeAverageByTest(TblTest $tblTest)
    {
        $count = 0;
        $sum = floatval(0);
        if (($tblTestGradeList = $tblTest->getGrades())) {
            foreach ($tblTestGradeList as $tblTestGrade) {
                if ($tblTestGrade->getIsGradeNumeric()) {
                    $count++;
                    $sum += $tblTestGrade->getGradeNumberValue();
                }
            }
        }

        return $count > 0 ? $this->getGradeAverage($sum, $count) : false;
    }

    /**
     * @param $average
     * @param string $separator
     *
     * @return string
     */
    public function getAverageInWord($average, string $separator = ','): string
    {
        $array = explode($separator, $average);
        $result = '';

        if (isset($array[0])) {
            if (($word1 = $this->getWordByNumber($array[0]))) {
                $result .= $word1;
            } else {
                return '';
            }
        }
        if (isset($array[1])) {
            if (($word2 = $this->getWordByNumber($array[1]))) {
                $result .= ' Komma ' . $word2;
            } else {
                return '';
            }
        }

        return $result;
    }

    /**
     * @param $number
     *
     * @return false|string
     */
    private function getWordByNumber($number)
    {
        $number = intval($number);
        switch ($number) {
            case 0: return 'Null';
            case 1: return 'Eins';
            case 2: return 'Zwei';
            case 3: return 'Drei';
            case 4: return 'Vier';
            case 5: return 'Fünf';
            case 6: return 'Sechs';
            case 7: return 'Sieben';
            case 8: return 'Acht';
            case 9: return 'Neun';
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return integer
     */
    public function getCountPersonTestGrades(TblPerson $tblPerson): int
    {
        return (new Data($this->getBinding()))->getCountPersonTestGrades($tblPerson);
    }
}