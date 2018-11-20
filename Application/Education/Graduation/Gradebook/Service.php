<?php

namespace SPHERE\Application\Education\Graduation\Gradebook;

use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Application\Education\Graduation\Gradebook\ScoreRule\Service as ServiceScoreRule;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Data;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeText;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreCondition;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreRule;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Setup;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\Service\Entity\TblStudentCustody;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Cache\Handler\MemoryHandler;
use SPHERE\System\Extension\Repository\Sorter\DateTimeSorter;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\Graduation\Gradebook
 */
class Service extends ServiceScoreRule
{

    /**
     * @param bool $Simulate
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($Simulate, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($Simulate);
        if (!$Simulate && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param                     $GradeType
     *
     * @return IFormInterface|string
     */
    public function createGradeType(IFormInterface $Stage = null, $GradeType)
    {

        /**
         * Skip to Frontend
         */
        if (null === $GradeType) {
            return $Stage;
        }

        $Error = false;
        if (isset($GradeType['Name']) && empty($GradeType['Name'])) {
            $Stage->setError('GradeType[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }
        if (isset($GradeType['Code']) && empty($GradeType['Code'])) {
            $Stage->setError('GradeType[Code]', 'Bitte geben Sie eine Abk&uuml;rzung an');
            $Error = true;
        }
        if (!($tblTestType = Evaluation::useService()->getTestTypeById($GradeType['Type']))) {
            $Stage->setError('GradeType[Type]', 'Bitte wählen Sie eine Kategorie aus');
            $Error = true;
        }

        if (!$Error) {
            (new Data($this->getBinding()))->createGradeType(
                $GradeType['Name'],
                $GradeType['Code'],
                $GradeType['Description'],
                isset($GradeType['IsHighlighted']) ? true : false,
                $tblTestType
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Der Zensuren-Typ ist erfasst worden')
            . new Redirect('/Education/Graduation/Gradebook/GradeType', Redirect::TIMEOUT_SUCCESS);
        }

        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param                     $Id
     * @param                     $GradeType
     *
     * @return IFormInterface|string
     */
    public function updateGradeType(IFormInterface $Stage = null, $Id, $GradeType)
    {

        /**
         * Skip to Frontend
         */
        if (null === $GradeType || null === $Id) {
            return $Stage;
        }

        $Error = false;
        if (isset($GradeType['Name']) && empty($GradeType['Name'])) {
            $Stage->setError('GradeType[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        }
        if (isset($GradeType['Code']) && empty($GradeType['Code'])) {
            $Stage->setError('GradeType[Code]', 'Bitte geben sie eine Abkürzung an');
            $Error = true;
        }

        $tblGradeType = $this->getGradeTypeById($Id);
        if (!$tblGradeType) {
            return new Danger(new Ban() . ' Zensuren-Typ nicht gefunden')
            . new Redirect('/Education/Graduation/Gradebook/GradeType', Redirect::TIMEOUT_ERROR);
        }

        if (!$Error) {
            (new Data($this->getBinding()))->updateGradeType(
                $tblGradeType,
                $GradeType['Name'],
                $GradeType['Code'],
                $GradeType['Description'],
                isset($GradeType['IsHighlighted']) ? true : false,
                Evaluation::useService()->getTestTypeById($GradeType['Type']),
                $tblGradeType->isActive()
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Der Zensuren-Typ ist erfolgreich gespeichert worden')
            . new Redirect('/Education/Graduation/Gradebook/GradeType', Redirect::TIMEOUT_SUCCESS);
        }

        return $Stage;
    }

    /**
     * @param TblPerson|null $tblPerson
     * @param TblDivision|null $tblDivision
     * @param TblPeriod|null $tblPeriod
     * @param TblTestType|null $tblTestType
     *
     * @return bool|TblGrade[]
     */
    public function getGradeAllBy(
        TblPerson $tblPerson = null,
        TblDivision $tblDivision = null,
        TblPeriod $tblPeriod = null,
        TblTestType $tblTestType = null
    ) {

        return (new Data($this->getBinding()))->getGradeAllBy($tblPerson, $tblDivision, $tblPeriod, $tblTestType);
    }

    /**
     * @param $Id
     *
     * @return bool|Service\Entity\TblGradeType
     */
    public function getGradeTypeById($Id)
    {

        $Cache = $this->getCache( new MemoryHandler() );
        if( !($Result = $Cache->getValue( $Id, __METHOD__ )) ) {
            $Result = (new Data($this->getBinding()))->getGradeTypeById($Id);
            $Cache->setValue( $Id, $Result, 0, __METHOD__ );
        }
        return $Result;
    }

    /**
     * @param string $Code
     *
     * @return bool|Service\Entity\TblGradeType
     */
    public function getGradeTypeByCode($Code)
    {

        return (new Data($this->getBinding()))->getGradeTypeByCode($Code);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param null $DivisionSubjectId
     * @param null $Select
     * @param string $BasicRoute
     *
     * @return IFormInterface|Redirect
     */
    public function getGradeBook(IFormInterface $Stage = null, $DivisionSubjectId = null, $Select = null, $BasicRoute)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Select || $DivisionSubjectId === null) {
            return $Stage;
        }

        $Error = false;
        if (!isset($Select['ScoreCondition'])) {
            $Error = true;
            $Stage .= new Warning(new Ban() . ' Berechnungsvorschrift nicht gefunden');
        }
        if ($Error) {
            return $Stage;
        }

        $tblScoreCondition = Gradebook::useService()->getScoreConditionById($Select['ScoreCondition']);

        return new Redirect($BasicRoute . '/Selected', Redirect::TIMEOUT_SUCCESS, array(
            'DivisionSubjectId' => $DivisionSubjectId,
            'ScoreConditionId' => $tblScoreCondition->getId()
        ));
    }

    /**
     * @param TblTestType $tblTestType
     * @param bool $IsActive
     *
     * @return bool|TblGradeType[]
     */
    public function getGradeTypeAllByTestType(TblTestType $tblTestType, $IsActive = true)
    {

        return (new Data($this->getBinding()))->getGradeTypeAllByTestType($tblTestType, $IsActive);
    }

    /**
     * @return false|TblGradeType[]
     */
    public function getGradeTypeAll()
    {

        return (new Data($this->getBinding()))->getGradeTypeAll();
    }


    /**
     * @param $Id
     *
     * @return bool|Service\Entity\TblGrade
     */
    public function getGradeById($Id)
    {

        return (new Data($this->getBinding()))->getGradeById($Id);
    }

    /**
     * @param \SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest $tblTest
     *
     * @return TblGrade[]|bool
     */
    public function getGradeAllByTest(TblTest $tblTest)
    {

        return (new Data($this->getBinding()))->getGradeAllByTest($tblTest);
    }

    /**
     * @param TblTest $tblTest
     * @param array $gradeMirror
     *
     * @return bool|float
     */
    public function getAverageByTest(TblTest $tblTest, &$gradeMirror = array())
    {

        $tblDivision = $tblTest->getServiceTblDivision();
        $tblSubject = $tblTest->getServiceTblSubject();
        if ($tblDivision && $tblSubject) {
            $tblScoreType = $this->getScoreTypeByDivisionAndSubject(
                $tblDivision,
                $tblSubject
            );

            if ($tblScoreType && $tblScoreType->getIdentifier() !== 'VERBAL') {
                $hasMirror = false;
                if ($tblScoreType->getIdentifier() == 'GRADES'
                    || $tblScoreType->getIdentifier() == 'GRADES_COMMA'
                ) {
                    $hasMirror = true;
                    for ($i = 1; $i < 7; $i++) {
                        $gradeMirror[$i] = 0;
                    }
                } elseif ($tblScoreType->getIdentifier() == 'POINTS') {
                    $hasMirror = true;
                    for ($i = 0; $i < 16; $i++) {
                        $gradeMirror[$i] = 0;
                    }
                }
                $tblGradeList = $this->getGradeAllByTest($tblTest);
                if ($tblGradeList) {
                    $sum = 0;
                    $count = 0;
                    foreach ($tblGradeList as $tblGrade) {
                        if ($tblGrade->getGrade() && $tblGrade->getGrade() !== '' && is_numeric($tblGrade->getGrade())) {
                            $value = floatval($tblGrade->getGrade());
                            $sum += $value;
                            $count++;
                            if ($hasMirror) {
                                $value = intval(round($value, 0));
                                $gradeMirror[$value]++;
                            }
                        }
                    }

                    if ($count > 0) {
                        return round($sum / $count, 2);
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param null $TestId
     * @param null $Grade
     * @param $BasicRoute
     * @param TblScoreType|null $tblScoreType
     * @param null $studentTestList
     * @param TblTest $tblNextTest
     *
     * @return IFormInterface|string
     */
    public function updateGradeToTest(
        IFormInterface $Stage = null,
        $TestId = null,
        $Grade = null,
        $BasicRoute,
        TblScoreType $tblScoreType = null,
        $studentTestList = null,
        TblTest $tblNextTest = null
    ) {

        /**
         * Skip to Frontend
         */
        if ($TestId === null) {
            return $Stage;
        }
        if ($Grade === null) {
            return $Stage;
        }

        $tblTest = Evaluation::useService()->getTestById($TestId);

        $errorRange = array();
        // check if grade has pattern
        if (!empty($Grade)
            && $tblScoreType
            && $tblScoreType->getPattern() !== ''
        ) {
            foreach ($Grade as $personId => $value) {
                $tblPerson = Person::useService()->getPersonById($personId);
                $gradeValue = str_replace(',', '.', trim($value['Grade']));
                if (!isset($value['Attendance']) && $gradeValue !== '' && $gradeValue !== '-1') {
                    if (!preg_match('!' . $tblScoreType->getPattern() . '!is', $gradeValue)) {
                        if ($tblPerson) {
                            $errorRange[] = new Container(new Bold($tblPerson->getLastFirstName()));
                        }
                    }
                }
            }
        }

        $errorEdit = array();
        // Grund bei Noten-Änderung angeben
        $errorNoGrade = array();
        // Datum ist Pflichtfeld bei einem fortlaufenden Test
        $errorNoDate = array();
        if (!empty($Grade)) {
            foreach ($Grade as $personId => $value) {
                if ($value['Grade'] != -1) {
                    $gradeValue = str_replace(',', '.', trim($value['Grade']));
                    if ((strpos($gradeValue, '+') !== false)) {
                        $trend = TblGrade::VALUE_TREND_PLUS;
                        $gradeValue = str_replace('+', '', $gradeValue);
                    } elseif ((strpos($gradeValue, '-') !== false)) {
                        $trend = TblGrade::VALUE_TREND_MINUS;
                        $gradeValue = str_replace('-', '', $gradeValue);
                    } else {
                        $trend = 0;
                    }

                    $tblPerson = Person::useService()->getPersonById($personId);
                    if ($studentTestList && isset($studentTestList[$personId])) {
                        $tblTestOfPerson = $studentTestList[$personId];
                    } else {
                        $tblTestOfPerson = $tblTest;
                    }
                    $tblGradeItem = Gradebook::useService()->getGradeByTestAndStudent($tblTestOfPerson, $tblPerson, true);
                    if ($tblGradeItem && empty($value['Comment']) && !isset($value['Attendance'])
                        && (($gradeValue != $tblGradeItem->getGrade()
//                            || (isset($value['Trend']) && $value['Trend'] != $tblGrade->getTrend())))
                        || ($trend != $tblGradeItem->getTrend())))
                    ) {
                        $errorEdit[] = new Container(new Bold($tblPerson->getLastFirstName()));
                    }
                    // nicht bei Notenaufträgen #SSW-1085
                    if (!$tblTest->getTblTask()) {
                        if ($tblGradeItem && $gradeValue === ''
                            && !isset($value['Attendance'])
                            && (!isset($value['Text']) || (isset($value['Text']) && !$this->getGradeTextById($value['Text'])))
                        ) {
                            $errorNoGrade[] = new Container(new Bold($tblPerson->getLastFirstName()));
                        }
                    }
                    if ($tblTest->isContinues() && !isset($value['Attendance']) && $gradeValue
                        && empty($value['Date']) && !$tblTest->getFinishDate()
                    ) {
                        $errorNoDate[] = new Container(new Bold($tblPerson->getLastFirstName()));
                    }
                }
            }
        }

        if (!empty($errorRange) || !empty($errorEdit) || !empty($errorNoGrade) || !empty($errorNoDate)) {
            if (!empty($errorRange)) {
                $Stage->prependGridGroup(
                    new FormGroup(new FormRow(new FormColumn(new Danger(
                            'Nicht alle eingebenen Zensuren befinden sich im Wertebereich.
                        Die Daten wurden nicht gespeichert.' . implode('', $errorRange), new Exclamation())
                    ))));
            }
            if (!empty($errorEdit)) {
                $Stage->prependGridGroup(
                    new FormGroup(new FormRow(new FormColumn(new Danger(
                            'Bei den Notenänderungen wurde nicht in jedem Fall ein Grund angegeben.
                             Die Daten wurden nicht gespeichert.' . implode('', $errorEdit), new Exclamation())
                    ))));
            }
            if (!empty($errorNoGrade)) {
                $Stage->prependGridGroup(
                    new FormGroup(new FormRow(new FormColumn(new Danger(
                            'Bereits eingetragene Zensuren können nur über "Nicht teilgenommen" entfernt werden.
                            Die Daten wurden nicht gespeichert.' . implode('', $errorNoGrade), new Exclamation())
                    ))));
            }
            if (!empty($errorNoDate)) {
                $Stage->prependGridGroup(
                    new FormGroup(new FormRow(new FormColumn(new Danger(
                            'Bei einem fortlaufenden Datum muss zu jeder Zensur ein Datum angegeben werden.
                            Die Daten wurden nicht gespeichert.' . implode('', $errorNoDate), new Exclamation())
                    ))));
            }

            return $Stage;
        }

        $tblPersonTeacher = false;
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
            if ($tblPersonAllByAccount) {
                $tblPersonTeacher = $tblPersonAllByAccount[0];
            }
        }

        if (!empty($Grade)) {
            foreach ($Grade as $personId => $value) {
                $tblPerson = Person::useService()->getPersonById($personId);
                if ($studentTestList && isset($studentTestList[$personId])) {
                    $tblTestByPerson = $studentTestList[$personId];
                } else {
                    $tblTestByPerson = $tblTest;
                }

                if ($tblTestByPerson->getServiceTblDivision() && $tblTestByPerson->getServiceTblSubject()) {

                    $grade = str_replace(',', '.', trim($value['Grade']));

                    // set trend
                    $trend = 0;
                    if ($grade != -1) {
                        if (isset($value['Trend'])) {
                            $trend = $value['Trend'];
                        } elseif ((strpos($grade, '+') !== false)) {
                            $trend = TblGrade::VALUE_TREND_PLUS;
                            $grade = str_replace('+', '', $grade);
                        } elseif ((strpos($grade, '-') !== false)) {
                            $trend = TblGrade::VALUE_TREND_MINUS;
                            $grade = str_replace('-', '', $grade);
                        }
                    }

                    if (!($tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTestByPerson,
                        $tblPerson, true))
                    ) {
                        $hasCreatedGrade = false;
                        if (isset($value['Attendance'])) {
                            $hasCreatedGrade = true;
                            (new Data($this->getBinding()))->createGrade(
                                $tblPerson,
                                $tblPersonTeacher ? $tblPersonTeacher : null,
                                $tblTestByPerson->getServiceTblDivision(),
                                $tblTestByPerson->getServiceTblSubject(),
                                $tblTestByPerson->getServiceTblSubjectGroup() ? $tblTestByPerson->getServiceTblSubjectGroup() : null,
                                $tblTestByPerson->getServiceTblPeriod() ? $tblTestByPerson->getServiceTblPeriod() : null,
                                $tblTestByPerson->getServiceTblGradeType() ? $tblTestByPerson->getServiceTblGradeType() : null,
                                $tblTestByPerson,
                                $tblTestByPerson->getTblTestType(),
                                null,
                                trim($value['Comment']),
                                0,
                                null,
                                isset($value['Text']) && ($tblGradeText = $this->getGradeTextById($value['Text']))
                                    ? $tblGradeText : null
                            );
                        } elseif ($grade !== '' && $grade != -1) {
                            $hasCreatedGrade = true;
                            (new Data($this->getBinding()))->createGrade(
                                $tblPerson,
                                $tblPersonTeacher ? $tblPersonTeacher : null,
                                $tblTestByPerson->getServiceTblDivision(),
                                $tblTestByPerson->getServiceTblSubject(),
                                $tblTestByPerson->getServiceTblSubjectGroup() ? $tblTestByPerson->getServiceTblSubjectGroup() : null,
                                $tblTestByPerson->getServiceTblPeriod() ? $tblTestByPerson->getServiceTblPeriod() : null,
                                $tblTestByPerson->getServiceTblGradeType() ? $tblTestByPerson->getServiceTblGradeType() : null,
                                $tblTestByPerson,
                                $tblTestByPerson->getTblTestType(),
                                $grade,
                                trim($value['Comment']),
                                $trend,
                                isset($value['Date']) ? $value['Date'] : null,
                                isset($value['Text']) && ($tblGradeText = $this->getGradeTextById($value['Text']))
                                    ? $tblGradeText : null
                            );
                        } elseif (isset($value['Text']) && ($tblGradeText = $this->getGradeTextById($value['Text']))) {
                            $hasCreatedGrade = true;
                            (new Data($this->getBinding()))->createGrade(
                                $tblPerson,
                                $tblPersonTeacher ? $tblPersonTeacher : null,
                                $tblTestByPerson->getServiceTblDivision(),
                                $tblTestByPerson->getServiceTblSubject(),
                                $tblTestByPerson->getServiceTblSubjectGroup() ? $tblTestByPerson->getServiceTblSubjectGroup() : null,
                                $tblTestByPerson->getServiceTblPeriod() ? $tblTestByPerson->getServiceTblPeriod() : null,
                                $tblTestByPerson->getServiceTblGradeType() ? $tblTestByPerson->getServiceTblGradeType() : null,
                                $tblTestByPerson,
                                $tblTestByPerson->getTblTestType(),
                                $grade,
                                trim($value['Comment']),
                                $trend,
                                isset($value['Date']) ? $value['Date'] : null,
                                $tblGradeText
                            );
                        }

                        if ($hasCreatedGrade) {
                            if (($tblTask = $tblTest->getTblTask()) && !$tblTask->isLocked()) {
                                Evaluation::useService()->setTaskLocked($tblTask);
                            }
                        }
                    } elseif ($tblGrade) {
                        if($this->isEditGrade($tblGrade, trim($value['Comment']), $grade, $trend,
                            isset($value['Date']) ? $value['Date'] : null,
                            isset($value['Text']) && ($tblGradeText = $this->getGradeTextById($value['Text']))
                                ? $tblGradeText : null
                        )){
                            if (isset($value['Attendance'])) {
                                (new Data($this->getBinding()))->updateGrade(
                                    $tblGrade,
                                    null,
                                    trim($value['Comment']),
                                    0,
                                    null,
                                    isset($value['Text']) && ($tblGradeText = $this->getGradeTextById($value['Text']))
                                        ? $tblGradeText : null,
                                    $tblPersonTeacher
                                );
                            } else {
                                (new Data($this->getBinding()))->updateGrade(
                                    $tblGrade,
                                    $grade == -1 ? '': $grade,
                                    trim($value['Comment']),
                                    $trend,
                                    isset($value['Date']) ? $value['Date'] : null,
                                    isset($value['Text']) && ($tblGradeText = $this->getGradeTextById($value['Text']))
                                        ? $tblGradeText : null,
                                    $tblPersonTeacher
                                );
                            }
                        }
                    }
                }
            }
        }

        return new Success('Erfolgreich gespeichert.'
                . ($tblNextTest ? ' Sie werden zum nächsten Kopfnoten-Typ weitergeleitet.' : '')
                , new \SPHERE\Common\Frontend\Icon\Repository\Success())
            . new Redirect($BasicRoute . '/Grade/Edit', Redirect::TIMEOUT_SUCCESS,
                array('Id' => $tblNextTest ? $tblNextTest->getId() : $tblTest->getId()));
    }

    /**
     * @param TblGrade $tblGrade
     * @param          $Comment
     * @param          $grade
     * @param          $trend
     * @param          $date
     * @param          $text
     *
     * @return bool
     */
    private function isEditGrade(TblGrade $tblGrade, $Comment, $grade, $trend, $date, $text)
    {
        $isChange = false;
        if($tblGrade->getComment() != $Comment){
            $isChange = true;
        } elseif($tblGrade->getGrade() != $grade){
            $isChange = true;
        } elseif($tblGrade->getTrend() != $trend){
            $isChange = true;
        } elseif($tblGrade->getDate() != $date){
            $isChange = true;
        } elseif($tblGrade->getTblGradeText() != $text){
            $isChange = true;
        }

        return $isChange;
    }

    /**
     * @param TblTest $tblTest
     * @param TblPerson $tblPerson
     * @param bool $IsForced
     *
     * @return bool|TblGrade
     */
    public function getGradeByTestAndStudent(
        TblTest $tblTest,
        TblPerson $tblPerson,
        $IsForced = false
    ) {

        return (new Data($this->getBinding()))->getGradeByTestAndStudent($tblTest, $tblPerson, $IsForced);
    }

    /**
     * @param TblGrade $tblGrade
     *
     * @return bool
     */
    public function destroyGrade(TblGrade $tblGrade)
    {

        return (new Data($this->getBinding()))->destroyGrade($tblGrade);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblTestType $tblTestType
     * @param TblScoreRule $tblScoreRule
     * @param TblPeriod|null $tblPeriod
     * @param TblSubjectGroup|null $tblSubjectGroup
     * @param bool $isStudentView
     * @param bool $taskDate
     *
     * @return array|bool|float|string
     */
    public function calcStudentGrade(
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblTestType $tblTestType,
        TblScoreRule $tblScoreRule = null,
        TblPeriod $tblPeriod = null,
        TblSubjectGroup $tblSubjectGroup = null,
        $isStudentView = false,
        $taskDate = false
    ) {

        $tblGradeList = $this->getGradesByStudent(
            $tblPerson, $tblDivision, $tblSubject, $tblTestType, $tblPeriod, $tblSubjectGroup
        );

        // entfernen aller Noten nach dem Stichtag (bei Stichtagsnotenauftägen)
        if ($taskDate && $tblGradeList) {
            $tempGradeList = array();
            $taskDate = new \DateTime($taskDate);
            foreach ($tblGradeList as $item) {
                if ($item->getServiceTblTest()) {
                    // Zensuren-Datum
                    if ($item->getServiceTblTest()->isContinues() && $item->getDate()) {
                        $gradeDate = new \DateTime($item->getDate());
                        // Noten nur vom vor dem Stichtag
                        if ($taskDate->format('Y-m-d') >= $gradeDate->format('Y-m-d')) {
                            $tempGradeList[] = $item;
                        }
                    } // Enddatum des Tests, falls vorhanden
                    elseif ($item->getServiceTblTest()->isContinues() && $item->getServiceTblTest()->getFinishDate()) {
                        $gradeDate = new \DateTime($item->getServiceTblTest()->getFinishDate());
                        // Noten nur vom vor dem Stichtag
                        if ($taskDate->format('Y-m-d') >= $gradeDate->format('Y-m-d')) {
                            $tempGradeList[] = $item;
                        }
                    } // Test-Datum
                    elseif ($item->getServiceTblTest()->getDate()) {
                        $testDate = new \DateTime($item->getServiceTblTest()->getDate());
                        // Noten nur vom vor dem Stichtag
                        if ($taskDate->format('Y-m-d') >= $testDate->format('Y-m-d')) {
                            $tempGradeList[] = $item;
                        }
                    }
                }
            }
            $tblGradeList = empty($tempGradeList) ? false : $tempGradeList;
        } elseif ($tblGradeList) {
            $tempGradeList = array();
            // gelöschte Tests ignorieren
            foreach ($tblGradeList as $item) {
                if ($item->getServiceTblTest()) {
                    $tempGradeList[] = $item;
                }
            }
            $tblGradeList = empty($tempGradeList) ? false : $tempGradeList;
        }

        // filter by Test Return for StudentView
        if ($isStudentView && $tblGradeList) {
            $filteredGradeList = array();
            foreach ($tblGradeList as $tblGrade) {
                $tblTest = $tblGrade->getServiceTblTest();
                if ($tblTest) {
                    // Noten-Datum
                    if ($tblTest->isContinues() && $tblGrade->getDate()) {
                        $gradeDate = (new \DateTime($tblGrade->getDate()))->format("Y-m-d");
                        $now = (new \DateTime('now'))->format("Y-m-d");
                        if ($gradeDate <= $now) {
                            $filteredGradeList[$tblGrade->getId()] = $tblGrade;
                        }
                    } // Test-Datum
                    elseif ($tblTest->getReturnDate()) {
                        $testDate = (new \DateTime($tblTest->getReturnDate()))->format("Y-m-d");
                        $now = (new \DateTime('now'))->format("Y-m-d");
                        if ($testDate <= $now) {
                            $filteredGradeList[$tblGrade->getId()] = $tblGrade;
                        }
                    }
                }
            }

            $tblGradeList = empty($filteredGradeList) ? false : $filteredGradeList;
        }

        if ($tblGradeList) {
            $result = array();
            $averageGroup = array();
            $resultAverage = '';
            $count = 0;
            $sum = 0;

            if ($tblScoreRule) {
                $tblLevel = $tblDivision->getTblLevel();
                $tblYear = $tblDivision->getServiceTblYear();
                $tblScoreCondition = $this->getScoreConditionByStudent(
                    $tblScoreRule,
                    $tblGradeList,
                    $tblYear ? $tblYear : null,
                    $tblPeriod ? $tblPeriod : null,
                    $tblLevel && $tblLevel->getName() == '12'
                );
            } else {
                $tblScoreCondition = false;
            }

            $error = array();
            foreach ($tblGradeList as $tblGrade) {
                if ($tblScoreCondition) {
                    /** @var TblScoreCondition $tblScoreCondition */
                    if (($tblScoreConditionGroupListByCondition
                        = Gradebook::useService()->getScoreConditionGroupListByCondition($tblScoreCondition))
                    ) {
                        $hasFoundGradeType = false;
                        foreach ($tblScoreConditionGroupListByCondition as $tblScoreGroup) {
                            if (($tblScoreGroupGradeTypeListByGroup
                                = Gradebook::useService()->getScoreGroupGradeTypeListByGroup($tblScoreGroup->getTblScoreGroup()))
                            ) {
                                foreach ($tblScoreGroupGradeTypeListByGroup as $tblScoreGroupGradeTypeList) {
                                    if ($tblGrade->getTblGradeType() && $tblScoreGroupGradeTypeList->getTblGradeType()
                                        && $tblGrade->getTblGradeType()->getId() === $tblScoreGroupGradeTypeList->getTblGradeType()->getId()
                                    ) {
                                        $hasFoundGradeType = true;
                                        if ($tblGrade->getGrade() !== null && $tblGrade->getGrade() !== '' && is_numeric($tblGrade->getGrade())) {
                                            $count++;
                                            $result[$tblScoreCondition->getId()][$tblScoreGroup->getTblScoreGroup()->getId()][$count]['Value']
                                                = floatval($tblGrade->getGrade()) * floatval($tblScoreGroupGradeTypeList->getMultiplier());
                                            $result[$tblScoreCondition->getId()][$tblScoreGroup->getTblScoreGroup()->getId()][$count]['Multiplier']
                                                = floatval($tblScoreGroupGradeTypeList->getMultiplier());
                                        }

                                        break;
                                    }
                                }
                            }
                        }

                        if (!$hasFoundGradeType && $tblGrade->getGrade() !== null && $tblGrade->getGrade() !== '' && is_numeric($tblGrade->getGrade())) {
                            if ($tblGrade->getTblGradeType()) {
                                $error[$tblGrade->getTblGradeType()->getId()] =
                                    new LayoutRow(
                                        new LayoutColumn(
                                            new Warning('Der Zensuren-Typ: ' . $tblGrade->getTblGradeType()->getDisplayName()
                                                . ' ist nicht in der Berechnungsvariante: ' . $tblScoreCondition->getName() . ' hinterlegt.',
                                                new Ban()
                                            )
                                        )
                                    );
                            }
                        }
                    }
                } else {
                    // alle Noten gleichwertig
                    if ($tblGrade->getGrade() !== null && $tblGrade->getGrade() !== '' && is_numeric($tblGrade->getGrade())) {
                        $count++;
                        $sum = $sum + floatval($tblGrade->getGrade());
                    }
                }
            }
            if (!empty($error)) {
                return $error;
            }

            if (!$tblScoreCondition) {
                if ($count > 0) {
                    $average = $sum / $count;
                    return round($average, 2);
                } else {
                    return false;
                }
            }

            if (!empty($result)) {
                foreach ($result as $conditionId => $groups) {
                    if (!empty($groups)) {
                        foreach ($groups as $groupId => $group) {
                            if (!empty($group)
                                && ($tblScoreGroupItem = Gradebook::useService()->getScoreGroupById($groupId))
                            ) {

                                $countGrades = 0;
                                foreach ($group as $value) {
                                    if ($tblScoreGroupItem->isEveryGradeASingleGroup()) {
                                        $countGrades++;
                                        $averageGroup[$conditionId][$groupId][$countGrades]['Value'] = $value['Value'];
                                        $averageGroup[$conditionId][$groupId][$countGrades]['Multiplier'] = $value['Multiplier'];
                                    } else {
                                        if (isset($averageGroup[$conditionId][$groupId])) {
                                            $averageGroup[$conditionId][$groupId]['Value'] += $value['Value'];
                                            $averageGroup[$conditionId][$groupId]['Multiplier'] += $value['Multiplier'];
                                        } else {
                                            $averageGroup[$conditionId][$groupId]['Value'] = $value['Value'];
                                            $averageGroup[$conditionId][$groupId]['Multiplier'] = $value['Multiplier'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if (!empty($averageGroup[$tblScoreCondition->getId()])) {
                    $average = 0;
                    $totalMultiplier = 0;
                    foreach ($averageGroup[$tblScoreCondition->getId()] as $groupId => $group) {
                        if (($tblScoreGroup = Gradebook::useService()->getScoreGroupById($groupId))) {
                            $multiplier = floatval($tblScoreGroup->getMultiplier());
                            if ($tblScoreGroup->isEveryGradeASingleGroup() && is_array($group)) {

                                foreach ($group as $itemValue) {
                                    if (isset($itemValue['Value']) && isset($itemValue['Multiplier'])) {
                                        $totalMultiplier += $multiplier;
                                        $average += $multiplier * ($itemValue['Value'] / $itemValue['Multiplier']);
                                    }
                                }

                            } else {

                                if (isset($group['Value']) && isset($group['Multiplier'])) {
                                    $totalMultiplier += $multiplier;
                                    $average += $multiplier * ($group['Value'] / $group['Multiplier']);
                                }

                            }
                        }
                    }

                    if ($totalMultiplier > 0) {
                        $average = $average / $totalMultiplier;
                        $resultAverage = round($average, 2);
                    } else {
                        $resultAverage = '';
                    }
                }
            }

            return $resultAverage === '' ? false : $resultAverage
                . ($tblScoreCondition ? '(' . $tblScoreCondition->getPriority() . ': ' . $tblScoreCondition->getName() . ')' : '');
        }

        return false;
    }

    /**
     * @param TblScoreRule $tblScoreRule
     * @param $tblGradeList
     * @param TblYear|null $tblYear
     * @param TblPeriod|null $tblPeriod
     * @param bool $isLevel12
     *
     * @return bool|TblScoreCondition
     */
    public function getScoreConditionByStudent(
        TblScoreRule $tblScoreRule,
        $tblGradeList,
        TblYear $tblYear = null,
        TblPeriod $tblPeriod = null,
        $isLevel12 = false
    ){
        // get ScoreCondition
        $tblScoreCondition = false;
        if ($tblScoreRule !== null) {
            $tblScoreConditionsByRule = Gradebook::useService()->getScoreConditionsByRule($tblScoreRule);
            if ($tblScoreConditionsByRule) {
                if (count($tblScoreConditionsByRule) > 1) {
                    $tblScoreConditionsByRule =
                        $this->getSorter($tblScoreConditionsByRule)->sortObjectBy('Priority');
                    if ($tblScoreConditionsByRule) {
                        /** @var TblScoreCondition $item */
                        foreach ($tblScoreConditionsByRule as $item) {
                            $hasConditions = true;

                            // check period
                            if (($period = $item->getPeriod())) {
                                if (($tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear, $isLevel12))) {
                                    $firstPeriod = reset($tblPeriodList);
                                    if ($period == TblScoreCondition::PERIOD_FIRST_PERIOD) {
                                        if ($tblPeriod && $firstPeriod->getId() == $tblPeriod->getId()) {

                                        } else {
                                            $hasConditions = false;
                                        }
                                    } elseif ($period == TblScoreCondition::PERIOD_SECOND_PERIOD) {
                                        if ($tblPeriod && $firstPeriod->getId() != $tblPeriod->getId()) {

                                        } else {
                                            $hasConditions = false;
                                        }
                                    }
                                }
                            }

                            // check gradeTypes
                            if (($tblScoreConditionGradeTypeListByCondition =
                                Gradebook::useService()->getScoreConditionGradeTypeListByCondition(
                                    $item
                                ))
                            ) {
                                foreach ($tblScoreConditionGradeTypeListByCondition as $tblScoreConditionGradeTypeList) {
                                    $countMinimum = $tblScoreConditionGradeTypeList->getCount();
                                    $countGradeType = 0;
                                    if (($tblGradeType = $tblScoreConditionGradeTypeList->getTblGradeType())) {
                                        /** @var TblGrade $tblGrade */
                                        foreach ($tblGradeList as $tblGrade) {
                                            if (is_numeric($tblGrade->getGrade())
                                                && $tblGrade->getTblGradeType()
                                                && ($tblGrade->getTblGradeType()->getId() == $tblGradeType->getId())
                                            ) {
                                                $countGradeType++;
                                            }
                                        }

                                        if ($countGradeType < $countMinimum) {
                                            $hasConditions = false;
                                        }
                                    }
                                }
                            }

                            // check group requirements
                            if (($tblScoreConditionGroupRequirementList =
                                Gradebook::useService()->getScoreConditionGroupRequirementAllByCondition(
                                    $item
                                ))
                            ) {
                                foreach ($tblScoreConditionGroupRequirementList as $tblScoreConditionGroupRequirement) {
                                    $countMinimum = $tblScoreConditionGroupRequirement->getCount();
                                    $countGradeTypes = 0;
                                    if (($tblScoreGroup = $tblScoreConditionGroupRequirement->getTblScoreGroup())
                                        && ($tblGradeTypeList = Gradebook::useService()->getScoreGroupGradeTypeListByGroup($tblScoreGroup))
                                    ) {
                                        $gradeTypeList = array();
                                        foreach ($tblGradeTypeList as $tblGradeTypeItem) {
                                            if (($tblGradeType = $tblGradeTypeItem->getTblGradeType())){
                                                $gradeTypeList[$tblGradeType->getId()] = $tblGradeType;
                                            }
                                        }

                                        /** @var TblGrade $tblGrade */
                                        foreach ($tblGradeList as $tblGrade) {
                                            if (is_numeric($tblGrade->getGrade())
                                                && $tblGrade->getTblGradeType()
                                                && isset($gradeTypeList[$tblGrade->getTblGradeType()->getId()])
                                            ) {
                                                $countGradeTypes++;
                                            }
                                        }

                                        if ($countGradeTypes < $countMinimum) {
                                            $hasConditions = false;
                                        }
                                    }
                                }
                            }

                            if ($hasConditions) {
                                $tblScoreCondition = $item;
                                break;
                            }
                        }
                    }
                } else {
                    $tblScoreCondition = $tblScoreConditionsByRule[0];
                }
            }
        }

        return $tblScoreCondition;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param \SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType $tblTestType
     * @param TblPeriod|null $tblPeriod
     * @param TblSubjectGroup|null $tblSubjectGroup
     *
     * @return bool|Service\Entity\TblGrade[]
     */
    public function getGradesByStudent(
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblTestType $tblTestType,
        TblPeriod $tblPeriod = null,
        TblSubjectGroup $tblSubjectGroup = null
    ) {

        return (new Data($this->getBinding()))->getGradesByStudent($tblPerson, $tblDivision, $tblSubject, $tblTestType,
            $tblPeriod, $tblSubjectGroup);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param null $Select
     * @param string $Redirect
     *
     * @return IFormInterface|Redirect|string
     */
    public function getYear(IFormInterface $Stage = null, $Select = null, $Redirect = '')
    {

        /**
         * Skip to Frontend
         */
        if (null === $Select) {
            return $Stage;
        }

        $Error = false;
        if (!isset($Select['Year'])) {
            $Error = true;
            $Stage .= new Warning('Schuljahr nicht gefunden');
        }
        if ($Error) {
            return $Stage;
        }

        return new Redirect($Redirect, Redirect::TIMEOUT_SUCCESS, array(
            'YearId' => $Select['Year'],
        ));
    }

    /**
     * @param $Identifier
     *
     * @return bool|TblScoreType
     */
    public function getScoreTypeByIdentifier($Identifier)
    {

        return (new Data($this->getBinding()))->getScoreTypeByIdentifier($Identifier);
    }

    /**
     * @return bool|TblScoreType[]
     */
    public function getScoreTypeAll()
    {

        return (new Data($this->getBinding()))->getScoreTypeAll();
    }

    /**
     * @param TblScoreType $tblScoreType
     * @param $tblDivision
     * @param $tblSubject
     */
    private function setScoreTypeForDivisionSubject(TblScoreType $tblScoreType, $tblDivision, $tblSubject)
    {
        if (($tblScoreRuleDivisionSubject = $this->getScoreRuleDivisionSubjectByDivisionAndSubject(
            $tblDivision, $tblSubject
        ))
        ) {
            if (!$tblScoreRuleDivisionSubject->getTblScoreType()) {
                (new Data($this->getBinding()))->updateScoreRuleDivisionSubject(
                    $tblScoreRuleDivisionSubject,
                    $tblScoreRuleDivisionSubject->getTblScoreRule() ? $tblScoreRuleDivisionSubject->getTblScoreRule() : null,
                    $tblScoreType
                );
            }
        } else {
            (new Data($this->getBinding()))->createScoreRuleDivisionSubject(
                $tblDivision, $tblSubject, null, $tblScoreType
            );
        }
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblScoreType $tblScoreType
     * @param TblYear $tblYear
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function updateScoreTypeDivisionSubject(
        IFormInterface $Stage = null,
        TblScoreType $tblScoreType,
        TblYear $tblYear = null,
        $Data = null
    ) {

        /**
         * Skip to Frontend
         */
        if ($Data === null || $tblYear == null) {
            return $Stage;
        }

        if (is_array($Data)) {
            foreach ($Data as $divisionId => $subjectList) {
                $tblDivision = Division::useService()->getDivisionById($divisionId);
                if ($tblDivision) {
                    if (is_array($subjectList)) {
                        // alle Fächer einer Klassen zuordnen
                        if (isset($subjectList[-1])) {
                            $tblSubjectAllByDivision = Division::useService()->getSubjectAllByDivision($tblDivision);
                            if ($tblSubjectAllByDivision) {
                                foreach ($tblSubjectAllByDivision as $tblSubject) {
                                    $this->setScoreTypeForDivisionSubject($tblScoreType, $tblDivision, $tblSubject);
                                }
                            }
                        } else {
                            foreach ($subjectList as $subjectId => $value) {
                                $tblSubject = Subject::useService()->getSubjectById($subjectId);
                                if ($tblSubject) {
                                    $this->setScoreTypeForDivisionSubject($tblScoreType, $tblDivision, $tblSubject);
                                }
                            }
                        }
                    }
                }
            }
        }

        // bei bereits vorhandenen Einträgen Berechnungsvorschrift zurücksetzten
        $tblScoreTypeDivisionSubjectList = $this->getScoreRuleDivisionSubjectAllByScoreType($tblScoreType);
        if ($tblScoreTypeDivisionSubjectList) {
            $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST');
            foreach ($tblScoreTypeDivisionSubjectList as $tblScoreTypeDivisionSubject) {
                $tblDivision = $tblScoreTypeDivisionSubject->getServiceTblDivision();
                $tblSubject = $tblScoreTypeDivisionSubject->getServiceTblSubject();
                if ($tblDivision && $tblSubject) {
                    if ($tblDivision->getServiceTblYear()->getId() == $tblYear->getId()
                        && !Gradebook::useService()->existsGrades($tblDivision, $tblSubject, $tblTestType)
                    ) {
                        if (!isset($Data[$tblDivision->getId()][-1])                // alle Fächer
                            && !isset($Data[$tblDivision->getId()][$tblSubject->getId()])
                        ) {
                            (new Data($this->getBinding()))->updateScoreRuleDivisionSubject(
                                $tblScoreTypeDivisionSubject,
                                $tblScoreTypeDivisionSubject->getTblScoreRule() ? $tblScoreTypeDivisionSubject->getTblScoreRule() : null,
                                null
                            );
                        }
                    }
                }
            }
        }

        return new Success('Erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
        . new Redirect('/Education/Graduation/Gradebook/Type/Select', Redirect::TIMEOUT_SUCCESS, array(
            'Id' => $tblScoreType->getId(),
            'YearId' => $tblYear->getId()
        ));
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @return false|TblScoreType
     */
    public function getScoreTypeByDivisionAndSubject(
        TblDivision $tblDivision,
        TblSubject $tblSubject
    ) {

        $tblScoreRuleDivisionSubject = $this->getScoreRuleDivisionSubjectByDivisionAndSubject(
            $tblDivision,
            $tblSubject
        );
        if ($tblScoreRuleDivisionSubject) {

            return $tblScoreRuleDivisionSubject->getTblScoreType();
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblGradeType $tblGradeType
     *
     * @return false|Service\Entity\TblGrade[]
     */
    public function getGradesByGradeType(TblPerson $tblPerson, TblDivision $tblDivision, TblSubject $tblSubject, TblGradeType $tblGradeType)
    {

        return (new Data($this->getBinding()))->getGradesByGradeType($tblPerson, $tblDivision, $tblSubject, $tblGradeType);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject  $tblSubject
     * @param TblTestType $tblTestType
     *
     * @return bool
     */
    public function existsGrades(TblDivision $tblDivision, TblSubject $tblSubject, TblTestType $tblTestType)
    {

        return (new Data($this->getBinding()))->existsGrades($tblDivision, $tblSubject, $tblTestType);
    }

    /**
     * @param TblGradeType $tblGradeType
     *
     * @return bool
     */
    public function destroyGradeType(TblGradeType $tblGradeType)
    {

        return (new Data($this->getBinding()))->destroyGradeType($tblGradeType);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param null $Filter
     *
     * @return IFormInterface|Redirect
     */
    public function getFilteredDivisionSubjectList(IFormInterface $Stage = null, $Filter = null)
    {
        /**
         * Skip to Frontend
         */
        if ($Filter === null) {
            return $Stage;
        }

        return new Redirect('/Education/Graduation/Gradebook/Score/Division', Redirect::TIMEOUT_SUCCESS, array(
            'YearDivisionSubjectId' => $Filter['Year'],
            'TypeDivisionSubjectId' => $Filter['Type'],
            'LevelDivisionSubjectId' => $Filter['Level']
        ));
    }

    /**
     * @param $tblDivisionSubjectList
     * @param TblYear|false $filterYear
     * @param TblType|false $filterType
     * @param TblLevel|false $filterLevel
     * @return array
     */
    public function filterDivisionSubjectList(
        $tblDivisionSubjectList,
        TblYear $filterYear = null,
        TblType $filterType = null,
        TblLevel $filterLevel = null
    ) {

        $resultList = array();

        if ($tblDivisionSubjectList) {
            /** @var TblDivisionSubject $tblDivisionSubject */
            foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                $hasYear = false;
                $hasType = false;
                $hasLevel = false;

                if (($tblDivision = $tblDivisionSubject->getTblDivision())) {
                    if ($filterYear
                        && $tblDivision->getServiceTblYear()
                        && $filterYear->getId() == $tblDivisionSubject->getTblDivision()->getServiceTblYear()->getId()
                    ) {
                        $hasYear = true;
                    }
                    if ($filterType
                        && $tblDivision->getTblLevel()
                        && $tblDivision->getTblLevel()->getServiceTblType()
                        && $filterType->getId() == $tblDivision->getTblLevel()->getServiceTblType()->getId()
                    ) {
                        $hasType = true;
                    }
                    if ($filterLevel
                        && $tblDivision->getTblLevel()
                        && $filterLevel->getName() == $tblDivision->getTblLevel()->getName()
                    ) {
                        $hasLevel = true;
                    }
                }

                // Filter "Und"-Verknüpfen
                if ($filterYear && $filterLevel && $filterType) {
                    if ($hasYear && $hasLevel && $hasType) {
                        array_push($resultList, $tblDivisionSubject);
                    }
                } elseif ($filterYear && $filterLevel) {
                    if ($hasYear && $hasLevel) {
                        array_push($resultList, $tblDivisionSubject);
                    }
                } elseif ($filterYear && $filterType) {
                    if ($hasYear && $hasType) {
                        array_push($resultList, $tblDivisionSubject);
                    }
                } elseif ($filterLevel && $filterType) {
                    if ($hasLevel && $hasType) {
                        array_push($resultList, $tblDivisionSubject);
                    }
                } elseif ($filterYear) {
                    if ($hasYear) {
                        array_push($resultList, $tblDivisionSubject);
                    }
                } elseif ($filterLevel) {
                    if ($hasLevel) {
                        array_push($resultList, $tblDivisionSubject);
                    }
                } elseif ($filterType) {
                    if ($hasType) {
                        array_push($resultList, $tblDivisionSubject);
                    }
                } else {
                    array_push($resultList, $tblDivisionSubject);
                }
            }
        }

        return $resultList;
    }

    /**
     * @param $Id
     *
     * @return bool|TblScoreType
     */
    public function getScoreTypeById($Id)
    {

        return (new Data($this->getBinding()))->getScoreTypeById($Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblGradeText
     */
    public function getGradeTextById($Id)
    {

        return (new Data($this->getBinding()))->getGradeTextById($Id);
    }

    /**
     * @param $Identifier
     *
     * @return false|TblGradeText
     */
    public function getGradeTextByIdentifier($Identifier)
    {

        return (new Data($this->getBinding()))->getGradeTextByIdentifier($Identifier);
    }

    /**
     * @return false|TblGradeText[]
     */
    public function getGradeTextAll()
    {

        return (new Data($this->getBinding()))->getGradeTextAll();
    }

    /**
     * @param $Name
     *
     * @return false|TblGradeText
     */
    public function getGradeTextByName($Name)
    {

        return (new Data($this->getBinding()))->getGradeTextByName($Name);
    }

    /**
     * @param TblGradeType $tblGradeType
     *
     * @return bool
     */
    public function isGradeTypeUsed(TblGradeType $tblGradeType)
    {

        if((new Data($this->getBinding()))->isGradeTypeUsedInGradebook($tblGradeType)) {
            return true;
        }

        if (Generator::useService()->isGradeTypeUsed($tblGradeType)) {
            return true;
        }

        if (Prepare::useService()->isGradeTypeUsed($tblGradeType)) {
            return true;
        }

        if (Evaluation::useService()->isGradeTypeUsed($tblGradeType)) {
            return true;
        }

        return false;
    }

    /**
     * @param TblGradeType $tblGradeType
     * @param bool $IsActive
     *
     * @return string
     */
    public function setGradeTypeActive(TblGradeType $tblGradeType, $IsActive = true)
    {

        return (new Data($this->getBinding()))->updateGradeType($tblGradeType, $tblGradeType->getName(),
            $tblGradeType->getCode(), $tblGradeType->getDescription(), $tblGradeType->isHighlighted(),
            $tblGradeType->getServiceTblTestType() ? $tblGradeType->getServiceTblTestType() : null, $IsActive);
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool
     */
    public function existsGradeByDivisionSubject(TblDivisionSubject $tblDivisionSubject)
    {

        return (new Data($this->getBinding()))->existsGradeByDivisionSubject($tblDivisionSubject);
    }

    /**
     * @param TblTest $tblTest
     * @param TblGradeType $tblGradeType
     */
    public function updateGradesGradeTypeByTest(TblTest $tblTest, TblGradeType $tblGradeType) {
        if (($tblGradeList = $this->getGradeAllByTest($tblTest))) {
            (new Data($this->getBinding()))->updateGradesGradeType($tblGradeType, $tblGradeList);
        }
    }

    /**
     * @param IFormInterface $Form
     * @param array          $ParentAccount
     * @param TblAccount     $tblAccountStudent
     *
     * @return IFormInterface|string
     */
    public function setDisableParent(
        IFormInterface $Form,
        $ParentAccount,
        $tblAccountStudent
    ) {

        /**
         * Skip to Frontend
         */
        if ($ParentAccount === null) {
            return $Form;
        }

        if (isset($ParentAccount['IsSubmit'])) {
            unset($ParentAccount['IsSubmit']);
        }

        $Error = false;

        if (!$Error) {

            // Remove old Link
            $tblStudentCustodyList = Consumer::useService()->getStudentCustodyByStudent($tblAccountStudent);
            if ($tblStudentCustodyList) {
                array_walk($tblStudentCustodyList, function (TblStudentCustody $tblStudentCustody) use (&$Error) {
                    if (!Consumer::useService()->removeStudentCustody($tblStudentCustody)) {
                        $Error = false;
                    }
                });
            }

            if ($ParentAccount) {
                // Add new Link
                array_walk($ParentAccount, function ($AccountId) use (&$Error, $tblAccountStudent) {
                    $tblAccountCustody = Account::useService()->getAccountById($AccountId);
                    if ($tblAccountCustody) {
                        Consumer::useService()->createStudentCustody($tblAccountStudent, $tblAccountCustody,
                            $tblAccountStudent);
                    } else {
                        $Error = false;
                    }
                });
            }

            if (!$Error) {
                return new Success('Einstellungen wurden erfolgreich gespeichert')
                    .new Redirect($this->getRequest()->getUrl(), Redirect::TIMEOUT_SUCCESS);
            } else {
                return new Danger('Einstellungen konnten nicht gespeichert werden')
                    .new Redirect($this->getRequest()->getUrl(), Redirect::TIMEOUT_ERROR);
            }
        }
        return $Form;
    }

    /**
     * @param $tblGradeList
     *
     * @return array
     */
    public function sortGradeList($tblGradeList)
    {

        if (($tblSetting = Consumer::useService()->getSetting(
                'Education', 'Graduation', 'Gradebook', 'SortHighlighted'
            ))
            && $tblSetting->getValue()
        ) {
            // Sortierung nach Großen (fettmarkiert) und Klein Noten
            $highlightedGrades = array();
            $notHighlightedGrades = array();
            $countGrades = 1;
            $isHighlightedSortedRight = true;
            if (($tblSettingSortedRight = Consumer::useService()->getSetting(
                'Education', 'Graduation', 'Gradebook', 'IsHighlightedSortedRight'
            ))
            ) {
                $isHighlightedSortedRight = $tblSettingSortedRight->getValue();
            }
            /** @var TblGrade $tblGradeItem */
            foreach ($tblGradeList as $tblGradeItem) {
                $gradeValue = $tblGradeItem->getGrade();
                if (($tblGradeType = $tblGradeItem->getTblGradeType())
                    && $gradeValue !== null
                    && $gradeValue !== ''
                ) {
                    if ($tblGradeType->isHighlighted()) {
                        $highlightedGrades[$countGrades++] = $tblGradeItem;
                    } else {
                        $notHighlightedGrades[$countGrades++] = $tblGradeItem;
                    }
                }
            }

            $tblGradeList = array();
            if (!empty($notHighlightedGrades)) {
                $tblGradeList = $this->getSorter($notHighlightedGrades)->sortObjectBy('DateForSorter', new DateTimeSorter());
            }
            if (!empty($highlightedGrades)) {
                $highlightedGrades = $this->getSorter($highlightedGrades)->sortObjectBy('DateForSorter', new DateTimeSorter());

                if ($isHighlightedSortedRight) {
                    $tblGradeList = array_merge($tblGradeList, $highlightedGrades);
                } else {
                    $tblGradeList = array_merge($highlightedGrades, $tblGradeList);
                }
            }
        } else {
            // Sortierung der Tests nach Datum
            $tblGradeList = $this->getSorter($tblGradeList)->sortObjectBy('DateForSorter', new DateTimeSorter());
        }

        return $tblGradeList;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param $tblStudentList
     *
     * @return array|bool
     */
    public function getGradesFromAnotherDivision(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        $tblStudentList
    ) {
        // Zensuren aus einer deaktivierten Klasse
        $resultList = array();
        if (($tblYear = $tblDivision->getServiceTblYear())
            && ($tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST'))
        ){
            /** @var TblPerson $tblPerson */
            foreach ($tblStudentList as $tblPerson) {
                $resultList = $this->getGradesFromAnotherDivisionByStudent(
                    $tblDivision,
                    $tblSubject,
                    $tblYear,
                    $tblPerson,
                    $tblTestType,
                    $resultList
                );
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblYear $tblYear
     * @param TblPerson $tblPerson
     * @param TblTestType $tblTestType
     * @param $resultList
     *
     * @return array
     */
    public function getGradesFromAnotherDivisionByStudent(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblYear $tblYear,
        TblPerson $tblPerson,
        TblTestType $tblTestType,
        $resultList
    ) {
        if (($tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson))) {
            foreach ($tblDivisionStudentList as $tblDivisionStudentItem) {
                if ($tblDivisionStudentItem->getLeaveDateTime() !== null
                    && $tblDivisionStudentItem->getUseGradesInNewDivision()
                    && ($tblDivisionItem = $tblDivisionStudentItem->getTblDivision())
                    && $tblDivision->getId() != $tblDivisionItem->getId()
                    && ($tblYearItem = $tblDivisionItem->getServiceTblYear())
                    && $tblYear->getId() == $tblYearItem->getId()
                    && ($tblGradeListFromAnotherDivision = Gradebook::useService()->getGradesByStudent(
                        $tblPerson, $tblDivisionItem, $tblSubject, $tblTestType
                    ))
                ) {
                    $tblGradeListFromAnotherDivision = $this->getSorter($tblGradeListFromAnotherDivision)
                        ->sortObjectBy('DateForSorter', new DateTimeSorter());
                    /** @var TblGrade $tblGrade */
                    foreach ($tblGradeListFromAnotherDivision as $tblGrade) {
                        if (($tblPeriod = $tblGrade->getServiceTblPeriod())
                            && ($tblGradeType = $tblGrade->getTblGradeType())
                        ) {
                            $resultList[$tblPeriod->getId()][$tblPerson->getId()][$tblGrade->getId()] =
                                $tblGradeType->isHighlighted()
                                    ? new Bold($tblGrade->getDisplayGrade())
                                    : $tblGrade->getDisplayGrade();
                        }
                    }
                }
            }
        }

        return $resultList;
    }
}
