<?php

namespace SPHERE\Application\Education\Graduation\Gradebook;

use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Data;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreCondition;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreConditionGradeTypeList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreConditionGroupList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreGroup;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreGroupGradeTypeList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreRule;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreRuleConditionList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreRuleDivisionSubject;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Setup;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\Graduation\Gradebook
 */
class Service extends AbstractService
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
            $Stage->setError('GradeType[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        }
        if (isset($GradeType['Code']) && empty($GradeType['Code'])) {
            $Stage->setError('GradeType[Code]', 'Bitte geben sie eine Abk&uuml;rzung an');
            $Error = true;
        }

        if (!$Error) {
            (new Data($this->getBinding()))->createGradeType(
                $GradeType['Name'],
                $GradeType['Code'],
                $GradeType['Description'],
                isset($GradeType['IsHighlighted']) ? true : false,
                Evaluation::useService()->getTestTypeById($GradeType['Type'])
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
                Evaluation::useService()->getTestTypeById($GradeType['Type'])
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Der Zensuren-Typ ist erfolgreich gespeichert worden')
            . new Redirect('/Education/Graduation/Gradebook/GradeType', Redirect::TIMEOUT_SUCCESS);
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return bool|Service\Entity\TblGradeType
     */
    public function getGradeTypeById($Id)
    {

        return (new Data($this->getBinding()))->getGradeTypeById($Id);
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
     * @param $Id
     *
     * @return bool|TblScoreCondition
     */
    public function getScoreConditionById($Id)
    {

        return (new Data($this->getBinding()))->getScoreConditionById($Id);
    }

    /**
     * @return bool|TblGradeType[]
     */
    public function getGradeTypeAllWhereTestOrBehavior()
    {

        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST');
        if (!$tblTestType || !($tblGradeTypeAllTest = $this->getGradeTypeAllByTestType($tblTestType))) {
            $tblGradeTypeAllTest = array();
        }

        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR');
        if (!$tblTestType || !($tblGradeTypeAllBehavior = $this->getGradeTypeAllByTestType($tblTestType))) {
            $tblGradeTypeAllBehavior = array();
        }

        $tblGradeTypeAll = array_merge($tblGradeTypeAllTest, $tblGradeTypeAllBehavior);

        return (empty($tblGradeTypeAll) ? false : $tblGradeTypeAll);
    }

    /**
     * @param TblTestType $tblTestType
     * @return bool|TblGradeType[]
     */
    public function getGradeTypeAllByTestType(TblTestType $tblTestType)
    {

        return (new Data($this->getBinding()))->getGradeTypeAllByTestType($tblTestType);
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
     * @param IFormInterface|null $Stage
     * @param null $TestId
     * @param                     $Grade
     * @param string $BasicRoute
     * @param bool $IsEdit
     *
     * @return IFormInterface|Redirect
     */
    public function updateGradeToTest(
        IFormInterface $Stage = null,
        $TestId = null,
        $Grade = null,
        $BasicRoute,
        $IsEdit
    ) {

        /**
         * Skip to Frontend
         */
        if ($TestId === null) {
            return $Stage;
        }
        $Global = $this->getGlobal();
        if (!isset($Global->POST['Button']['Submit'])) {
            return $Stage;
        }

        $tblTest = Evaluation::useService()->getTestById($TestId);

        if (!empty($Grade)) {
            foreach ($Grade as $personId => $value) {
                $tblPerson = Person::useService()->getPersonById($personId);

                // set trend
                if (isset($value['Trend'])) {
                    $trend = $value['Trend'];
                } else {
                    $trend = 0;
                }

                if (!($tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest, $tblPerson))) {
                    if (isset($value['Attendance'])) {
                        (new Data($this->getBinding()))->createGrade(
                            $tblPerson,
                            $tblTest->getServiceTblDivision(),
                            $tblTest->getServiceTblSubject(),
                            $tblTest->getServiceTblSubjectGroup() ? $tblTest->getServiceTblSubjectGroup() : null,
                            $tblTest->getServiceTblPeriod() ? $tblTest->getServiceTblPeriod() : null,
                            $tblTest->getServiceTblGradeType() ? $tblTest->getServiceTblGradeType() : null,
                            $tblTest,
                            $tblTest->getTblTestType(),
                            null,
                            trim($value['Comment']),
                            $trend
                        );
                    } elseif (trim($value['Grade']) !== '') {
                        (new Data($this->getBinding()))->createGrade(
                            $tblPerson,
                            $tblTest->getServiceTblDivision(),
                            $tblTest->getServiceTblSubject(),
                            $tblTest->getServiceTblSubjectGroup() ? $tblTest->getServiceTblSubjectGroup() : null,
                            $tblTest->getServiceTblPeriod() ? $tblTest->getServiceTblPeriod() : null,
                            $tblTest->getServiceTblGradeType() ? $tblTest->getServiceTblGradeType() : null,
                            $tblTest,
                            $tblTest->getTblTestType(),
                            trim($value['Grade']),
                            trim($value['Comment']),
                            $trend
                        );
                    }
                } elseif ($IsEdit && $tblGrade) {

                    if (isset($value['Attendance'])) {
                        (new Data($this->getBinding()))->updateGrade(
                            $tblGrade,
                            null,
                            trim($value['Comment']),
                            $trend
                        );
                    } else {
                        (new Data($this->getBinding()))->updateGrade(
                            $tblGrade,
                            trim($value['Grade']),
                            trim($value['Comment']),
                            $trend
                        );
                    }
                }
            }
        }

        return new Success('Erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
        . new Redirect($BasicRoute . '/Grade/Edit', Redirect::TIMEOUT_SUCCESS,
            array('Id' => $tblTest->getId()));
    }

    /**
     * @param TblTest $tblTest
     * @param TblPerson $tblPerson
     *
     * @return bool|TblGrade
     */
    public function getGradeByTestAndStudent(
        TblTest $tblTest,
        TblPerson $tblPerson
    ) {

        return (new Data($this->getBinding()))->getGradeByTestAndStudent($tblTest, $tblPerson);
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
     * @return bool|TblScoreGroup[]
     */
    public function getScoreGroupAll()
    {

        return (new Data($this->getBinding()))->getScoreGroupAll();
    }

    /**
     * @return bool|TblScoreCondition[]
     */
    public function getScoreConditionAll()
    {

        return (new Data($this->getBinding()))->getScoreConditionAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblScoreRule
     */
    public function getScoreRuleById($Id)
    {

        return (new Data($this->getBinding()))->getScoreRuleById($Id);
    }

    /**
     * @return bool|TblScoreRule[]
     */
    public function getScoreRuleAll()
    {

        return (new Data($this->getBinding()))->getScoreRuleAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblScoreRuleConditionList
     */
    public function getScoreRuleConditionListById($Id)
    {

        return (new Data($this->getBinding()))->getScoreRuleConditionListById($Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblScoreConditionGradeTypeList
     */
    public function getScoreConditionGradeTypeListById($Id)
    {

        return (new Data($this->getBinding()))->getScoreConditionGradeTypeListById($Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblScoreConditionGroupList
     */
    public function getScoreConditionGroupListById($Id)
    {

        return (new Data($this->getBinding()))->getScoreConditionGroupListById($Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblScoreGroupGradeTypeList
     */
    public function getScoreGroupGradeTypeListById($Id)
    {

        return (new Data($this->getBinding()))->getScoreGroupGradeTypeListById($Id);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param                     $ScoreCondition
     *
     * @return IFormInterface|string
     */
    public function createScoreCondition(IFormInterface $Stage = null, $ScoreCondition = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $ScoreCondition) {
            return $Stage;
        }

        $Error = false;
        if (isset($ScoreCondition['Name']) && empty($ScoreCondition['Name'])) {
            $Stage->setError('ScoreCondition[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        }

        if ($ScoreCondition['Priority'] == '') {
            $priority = 1;
        } else {
            $priority = $ScoreCondition['Priority'];
        }

        if (!$Error) {
            (new Data($this->getBinding()))->createScoreCondition(
                $ScoreCondition['Name'],
                $ScoreCondition['Round'],
                $priority
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Berechnungsvariante ist erfasst worden')
            . new Redirect('/Education/Graduation/Gradebook/Score/Condition', Redirect::TIMEOUT_SUCCESS);
        }

        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param                     $ScoreGroup
     *
     * @return IFormInterface|string
     */
    public function createScoreGroup(IFormInterface $Stage = null, $ScoreGroup = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $ScoreGroup) {
            return $Stage;
        }

        $Error = false;
        if (isset($ScoreGroup['Name']) && empty($ScoreGroup['Name'])) {
            $Stage->setError('ScoreGroup[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        }
        if (isset($ScoreGroup['Multiplier']) && empty($ScoreGroup['Multiplier'])) {
            $Stage->setError('ScoreGroup[Multiplier]', 'Bitte geben sie einen Faktor in Prozent an');
            $Error = true;
        }

        if (!$Error) {
            (new Data($this->getBinding()))->createScoreGroup(
                $ScoreGroup['Name'],
                $ScoreGroup['Round'],
                $ScoreGroup['Multiplier']
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Zensuren-Gruppe ist erfasst worden')
            . new Redirect('/Education/Graduation/Gradebook/Score/Group', Redirect::TIMEOUT_SUCCESS);
        }

        return $Stage;
    }

    /**
     * @param TblGradeType $tblGradeType
     * @param TblScoreGroup $tblScoreGroup
     * @param               $Multiplier
     *
     * @return TblScoreGroupGradeTypeList
     */
    public function addScoreGroupGradeTypeList(
        TblGradeType $tblGradeType,
        TblScoreGroup $tblScoreGroup,
        $Multiplier
    ) {

        if ((new Data($this->getBinding()))->addScoreGroupGradeTypeList($tblGradeType, $tblScoreGroup, $Multiplier)) {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Erfolgreich hinzugefügt.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Group/GradeType/Select', Redirect::TIMEOUT_SUCCESS,
                array('Id' => $tblScoreGroup->getId()));
        } else {
            return new Danger(new Ban() . ' Konnte nicht hinzugefügt werden.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Group/GradeType/Select', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblScoreGroup->getId()));
        }
    }

    /**
     * @param TblScoreGroupGradeTypeList $tblScoreGroupGradeTypeList
     *
     * @return string
     */
    public function removeScoreGroupGradeTypeList(
        TblScoreGroupGradeTypeList $tblScoreGroupGradeTypeList
    ) {

        $tblScoreGroup = $tblScoreGroupGradeTypeList->getTblScoreGroup();
        if ((new Data($this->getBinding()))->removeScoreGroupGradeTypeList($tblScoreGroupGradeTypeList)) {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Erfolgreich entfernt.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Group/GradeType/Select', Redirect::TIMEOUT_SUCCESS,
                array('Id' => $tblScoreGroup->getId()));
        } else {
            return new Danger(new Ban() . ' Konnte nicht entfernt werden.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Group/GradeType/Select', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblScoreGroup->getId()));
        }
    }

    /**
     * @param TblScoreCondition $tblScoreCondition
     * @param TblScoreGroup $tblScoreGroup
     *
     * @return string
     */
    public function addScoreConditionGroupList(
        TblScoreCondition $tblScoreCondition,
        TblScoreGroup $tblScoreGroup
    ) {

        if ((new Data($this->getBinding()))->addScoreConditionGroupList($tblScoreCondition, $tblScoreGroup)) {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Erfolgreich hinzugefügt.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Condition/Group/Select', Redirect::TIMEOUT_SUCCESS,
                array('Id' => $tblScoreCondition->getId()));
        } else {
            return new Danger('Konnte nicht hinzugefügt werden.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Condition/Group/Select', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblScoreCondition->getId()));
        }
    }

    /**
     * @param TblScoreConditionGroupList $tblScoreConditionGroupList
     *
     * @return string
     */
    public function removeScoreConditionGroupList(
        TblScoreConditionGroupList $tblScoreConditionGroupList
    ) {

        $tblScoreCondition = $tblScoreConditionGroupList->getTblScoreCondition();
        if ((new Data($this->getBinding()))->removeScoreConditionGroupList($tblScoreConditionGroupList)) {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Erfolgreich entfernt.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Condition/Group/Select', Redirect::TIMEOUT_SUCCESS,
                array('Id' => $tblScoreCondition->getId()));
        } else {
            return new Danger(new Ban() . ' Konnte nicht entfernt werden.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Condition/Group/Select', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblScoreCondition->getId()));
        }
    }

    /**
     * @param TblGradeType $tblGradeType
     * @param TblScoreCondition $tblScoreCondition
     *
     * @return TblScoreConditionGradeTypeList
     */
    public function addScoreConditionGradeTypeList(
        TblGradeType $tblGradeType,
        TblScoreCondition $tblScoreCondition
    ) {

        if ((new Data($this->getBinding()))->addScoreConditionGradeTypeList($tblGradeType, $tblScoreCondition)) {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Erfolgreich hinzugefügt.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Condition/GradeType/Select', Redirect::TIMEOUT_SUCCESS,
                array('Id' => $tblScoreCondition->getId()));
        } else {
            return new Danger(new Ban() . ' Konnte nicht hinzugefügt werden.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Condition/GradeType/Select', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblScoreCondition->getId()));
        }
    }

    /**
     * @param TblScoreConditionGradeTypeList $tblScoreConditionGradeTypeList
     *
     * @return string
     */
    public function removeScoreConditionGradeTypeList(
        TblScoreConditionGradeTypeList $tblScoreConditionGradeTypeList
    ) {

        $tblScoreCondition = $tblScoreConditionGradeTypeList->getTblScoreCondition();
        if ((new Data($this->getBinding()))->removeScoreConditionGradeTypeList($tblScoreConditionGradeTypeList)) {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Erfolgreich entfernt.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Condition/GradeType/Select', Redirect::TIMEOUT_SUCCESS,
                array('Id' => $tblScoreCondition->getId()));
        } else {
            return new Danger(new Ban() . ' Konnte nicht entfernt werden.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Condition/GradeType/Select', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblScoreCondition->getId()));
        }
    }

    /**
     * @param TblScoreRule $tblScoreRule
     * @param TblScoreCondition $tblScoreCondition
     *
     * @return TblScoreRuleConditionList
     */
    public function addScoreRuleConditionList(
        TblScoreRule $tblScoreRule,
        TblScoreCondition $tblScoreCondition
    ) {

        if ((new Data($this->getBinding()))->addScoreRuleConditionList($tblScoreRule, $tblScoreCondition)) {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Erfolgreich hinzugefügt.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Condition/Select', Redirect::TIMEOUT_SUCCESS,
                array('Id' => $tblScoreRule->getId()));
        } else {
            return new Danger(new Ban() . ' Konnte nicht hinzugefügt werden.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Condition/Select', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblScoreRule->getId()));
        }
    }

    /**
     * @param TblScoreRuleConditionList $tblScoreRuleConditionList
     * @return string
     */
    public function removeScoreRuleConditionList(
        TblScoreRuleConditionList $tblScoreRuleConditionList
    ) {

        $tblScoreRule = $tblScoreRuleConditionList->getTblScoreRule();
        if ((new Data($this->getBinding()))->removeScoreRuleConditionList($tblScoreRuleConditionList)) {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Erfolgreich entfernt.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Condition/Select', Redirect::TIMEOUT_SUCCESS,
                array('Id' => $tblScoreRule->getId()));
        } else {
            return new Danger(new Ban() . ' Konnte nicht entfernt werden.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Condition/Select', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblScoreRule->getId()));
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblTestType $tblTestType
     * @param TblScoreRule $tblScoreRule
     * @param TblPeriod|null $tblPeriod
     * @param TblSubjectGroup|null $tblSubjectGroup
     * @return bool|float
     */
    public function calcStudentGrade(
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblTestType $tblTestType,
        TblScoreRule $tblScoreRule = null,
        TblPeriod $tblPeriod = null,
        TblSubjectGroup $tblSubjectGroup = null
    ) {

        $tblGradeList = $this->getGradesByStudent(
            $tblPerson, $tblDivision, $tblSubject, $tblTestType, $tblPeriod, $tblSubjectGroup
        );

        if ($tblGradeList) {
            $result = array();
            $averageGroup = array();
            $resultAverage = '';
            $count = 0;
            $sum = 0;

            // get ScoreCondition
            $tblScoreCondition = false;
            if ($tblScoreRule !== null) {
                $tblScoreRuleConditionListByRule = Gradebook::useService()->getScoreRuleConditionListByRule($tblScoreRule);
                if ($tblScoreRuleConditionListByRule) {
                    if (count($tblScoreRuleConditionListByRule) > 1) {
                        $tblScoreRuleConditionListByRule =
                            $this->getSorter($tblScoreRuleConditionListByRule)->sortObjectList('Priority');
                        if ($tblScoreRuleConditionListByRule) {
                            /** @var TblScoreRuleConditionList $tblScoreRuleConditionList */
                            foreach ($tblScoreRuleConditionListByRule as $tblScoreRuleConditionList) {
                                $tblScoreConditionGradeTypeListByCondition =
                                    Gradebook::useService()->getScoreConditionGradeTypeListByCondition(
                                        $tblScoreRuleConditionList->getTblScoreCondition()
                                    );
                                if ($tblScoreConditionGradeTypeListByCondition) {
                                    $hasConditions = true;
                                    foreach ($tblScoreConditionGradeTypeListByCondition as $tblScoreConditionGradeTypeList) {
                                        $hasGradeType = false;
                                        foreach ($tblGradeList as $tblGrade) {
                                            if (is_numeric($tblGrade->getGrade())
                                                && ($tblGrade->getTblGradeType()->getId()
                                                    == $tblScoreConditionGradeTypeList->getTblGradeType()->getId())
                                            ) {
                                                $hasGradeType = true;
                                                break;
                                            }
                                        }

                                        if (!$hasGradeType) {
                                            $hasConditions = false;
                                            break;
                                        }
                                    }

                                    if ($hasConditions) {
                                        $tblScoreCondition = $tblScoreRuleConditionList->getTblScoreCondition();
                                        break;
                                    }

                                } else {
                                    // no Conditions
                                    $tblScoreCondition = $tblScoreRuleConditionList->getTblScoreCondition();
                                    break;
                                }
                            }
                        }
                    } else {
                        $tblScoreCondition = $tblScoreRuleConditionListByRule[0]->getTblScoreCondition();
                    }
                }
            }

            foreach ($tblGradeList as $tblGrade) {
                if ($tblScoreCondition) {
                    /** @var TblScoreCondition $tblScoreCondition */
                    if (($tblScoreConditionGroupListByCondition
                        = Gradebook::useService()->getScoreConditionGroupListByCondition($tblScoreCondition))
                    ) {
                        foreach ($tblScoreConditionGroupListByCondition as $tblScoreGroup) {
                            if (($tblScoreGroupGradeTypeListByGroup
                                = Gradebook::useService()->getScoreGroupGradeTypeListByGroup($tblScoreGroup->getTblScoreGroup()))
                            ) {
                                foreach ($tblScoreGroupGradeTypeListByGroup as $tblScoreGroupGradeTypeList) {
                                    if ($tblGrade->getTblGradeType()->getId() === $tblScoreGroupGradeTypeList->getTblGradeType()->getId()) {
                                        if ($tblGrade->getGrade() && $tblGrade->getGrade() !== '' && is_numeric($tblGrade->getGrade())) {
                                            $count++;
                                            $result[$tblScoreCondition->getId()][$tblScoreGroup->getTblScoreGroup()->getId()][$count]['Value']
                                                = floatval($tblGrade->getGrade()) * floatval($tblScoreGroupGradeTypeList->getMultiplier());
                                            $result[$tblScoreCondition->getId()][$tblScoreGroup->getTblScoreGroup()->getId()][$count]['Multiplier']
                                                = floatval($tblScoreGroupGradeTypeList->getMultiplier());
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    // alle Noten gleichwertig
                    if ($tblGrade->getGrade() && $tblGrade->getGrade() !== '' && is_numeric($tblGrade->getGrade())) {
                        $count++;
                        $sum = $sum + floatval($tblGrade->getGrade());
                    }
                }
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
                            if (!empty($group)) {
                                foreach ($group as $value) {
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

                if (!empty($averageGroup[$tblScoreCondition->getId()])) {
                    $average = 0;
                    $totalMultiplier = 0;
                    foreach ($averageGroup[$tblScoreCondition->getId()] as $groupId => $group) {
                        $tblScoreGroup = Gradebook::useService()->getScoreGroupById($groupId);
                        $multiplier = floatval($tblScoreGroup->getMultiplier());
                        if ($group['Value'] > 0) {
                            $totalMultiplier += $multiplier;
                            $average += $multiplier * ($group['Value'] / $group['Multiplier']);
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

            return $resultAverage == '' ? false : $resultAverage
                . ($tblScoreCondition ? '(' . $tblScoreCondition->getPriority() . ')' : '');
        }

        return false;
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
     * @param TblScoreCondition $tblScoreCondition
     *
     * @return bool|TblScoreConditionGroupList[]
     */
    public function getScoreConditionGroupListByCondition(TblScoreCondition $tblScoreCondition)
    {

        return (new Data($this->getBinding()))->getScoreConditionGroupListByCondition($tblScoreCondition);
    }

    /**
     * @param TblScoreGroup $tblScoreGroup
     *
     * @return bool|TblScoreGroupGradeTypeList[]
     */
    public function getScoreGroupGradeTypeListByGroup(TblScoreGroup $tblScoreGroup)
    {

        return (new Data($this->getBinding()))->getScoreGroupGradeTypeListByGroup($tblScoreGroup);
    }

    /**
     * @param TblScoreCondition $tblScoreCondition
     *
     * @return bool|TblScoreConditionGradeTypeList[]
     */
    public function getScoreConditionGradeTypeListByCondition(TblScoreCondition $tblScoreCondition)
    {

        return (new Data($this->getBinding()))->getScoreConditionGradeTypeListByCondition($tblScoreCondition);
    }

    /**
     * @param TblScoreRule $tblScoreRule
     *
     * @return bool|TblScoreRuleConditionList[]
     */
    public function getScoreRuleConditionListByRule(TblScoreRule $tblScoreRule)
    {

        return (new Data($this->getBinding()))->getScoreRuleConditionListByRule($tblScoreRule);
    }

    /**
     * @param $Id
     *
     * @return bool|TblScoreGroup
     */
    public function getScoreGroupById($Id)
    {

        return (new Data($this->getBinding()))->getScoreGroupById($Id);
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
     * @param IFormInterface|null $Stage
     * @param $Id
     * @param $ScoreCondition
     * @return IFormInterface|string
     */
    public function updateScoreCondition(IFormInterface $Stage = null, $Id, $ScoreCondition)
    {

        /**
         * Skip to Frontend
         */
        if (null === $ScoreCondition || null === $Id) {
            return $Stage;
        }

        $Error = false;
        if (isset($ScoreCondition['Name']) && empty($ScoreCondition['Name'])) {
            $Stage->setError('ScoreCondition[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        }

        $tblScoreCondition = $this->getScoreConditionById($Id);
        if (!$tblScoreCondition) {
            return new Danger(new Ban() . ' Berechnungsvariante nicht gefunden')
            . new Redirect('/Education/Graduation/Gradebook/Score', Redirect::TIMEOUT_ERROR);
        }

        if (!$Error) {
            (new Data($this->getBinding()))->updateScoreCondition(
                $tblScoreCondition,
                $ScoreCondition['Name'],
                $ScoreCondition['Round'],
                $ScoreCondition['Priority']
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Berechnungsvariante ist erfolgreich gespeichert worden')
            . new Redirect('/Education/Graduation/Gradebook/Score/Condition', Redirect::TIMEOUT_SUCCESS);
        }

        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param $Id
     * @param $ScoreGroup
     * @return IFormInterface|string
     */
    public function updateScoreGroup(IFormInterface $Stage = null, $Id, $ScoreGroup)
    {

        /**
         * Skip to Frontend
         */
        if (null === $ScoreGroup || null === $Id) {
            return $Stage;
        }

        $Error = false;
        if (isset($ScoreGroup['Name']) && empty($ScoreGroup['Name'])) {
            $Stage->setError('ScoreGroup[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        }

        $tblScoreGroup = $this->getScoreGroupById($Id);
        if (!$tblScoreGroup) {
            return new Danger(new Ban() . ' Zensuren-Gruppe nicht gefunden')
            . new Redirect('/Education/Graduation/Gradebook/Score/Group', Redirect::TIMEOUT_ERROR);
        }

        if (!$Error) {
            (new Data($this->getBinding()))->updateScoreGroup(
                $tblScoreGroup,
                $ScoreGroup['Name'],
                $ScoreGroup['Round'],
                $ScoreGroup['Multiplier']
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Zensuren-Gruppe ist erfolgreich gespeichert worden')
            . new Redirect('/Education/Graduation/Gradebook/Score/Group', Redirect::TIMEOUT_SUCCESS);
        }

        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param                     $ScoreRule
     *
     * @return IFormInterface|string
     */
    public function createScoreRule(IFormInterface $Stage = null, $ScoreRule = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $ScoreRule) {
            return $Stage;
        }

        $Error = false;
        if (isset($ScoreRule['Name']) && empty($ScoreRule['Name'])) {
            $Stage->setError('ScoreRule[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        }

        if (!$Error) {
            (new Data($this->getBinding()))->createScoreRule(
                $ScoreRule['Name'],
                $ScoreRule['Description']
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Berechnungsvorschrift ist erfasst worden')
            . new Redirect('/Education/Graduation/Gradebook/Score', Redirect::TIMEOUT_SUCCESS);
        }

        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param $Id
     * @param $ScoreRule
     * @return IFormInterface|string
     */
    public function updateScoreRule(IFormInterface $Stage = null, $Id, $ScoreRule)
    {

        /**
         * Skip to Frontend
         */
        if (null === $ScoreRule || null === $Id) {
            return $Stage;
        }

        $Error = false;
        if (isset($ScoreRule['Name']) && empty($ScoreRule['Name'])) {
            $Stage->setError('ScoreRule[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        }

        $tblScoreRule = $this->getScoreRuleById($Id);
        if (!$tblScoreRule) {
            return new Danger(new Ban() . ' Berechnungsvorschrift nicht gefunden')
            . new Redirect('/Education/Graduation/Gradebook/Score', Redirect::TIMEOUT_ERROR);
        }

        if (!$Error) {
            (new Data($this->getBinding()))->updateScoreRule(
                $tblScoreRule,
                $ScoreRule['Name'],
                $ScoreRule['Description']
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Berechnungsvorschrift ist erfolgreich gespeichert worden')
            . new Redirect('/Education/Graduation/Gradebook/Score', Redirect::TIMEOUT_SUCCESS);
        }

        return $Stage;
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
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     *
     * @return bool|TblScoreRuleDivisionSubject
     */
    public function getScoreRuleDivisionSubjectByDivisionAndSubject(TblDivision $tblDivision, TblSubject $tblSubject)
    {

        return (new Data($this->getBinding()))->getScoreRuleDivisionSubjectByDivisionAndSubject($tblDivision,
            $tblSubject);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param $Data
     *
     * @return IFormInterface
     */
    public function updateScoreRuleDivisionSubject(IFormInterface $Stage = null, $Data)
    {

        /**
         * Skip to Frontend
         */
        $Global = $this->getGlobal();
        if (!isset($Global->POST['Button']['Submit'])) {
            return $Stage;
        }

        if (isset($Data)) {
            foreach ($Data as $divisionId => $subjectItemList) {
                $tblDivision = Division::useService()->getDivisionById($divisionId);
                foreach ($subjectItemList as $subjectId => $item) {
                    $tblSubject = Subject::useService()->getSubjectById($subjectId);
                    if ($tblDivision && $tblSubject) {
                        $tblScoreRuleDivisionSubject = $this->getScoreRuleDivisionSubjectByDivisionAndSubject($tblDivision,
                            $tblSubject);
                        $tblScoreRule = Gradebook::useService()->getScoreRuleById($item['Rule']);
                        if (!$tblScoreRule) {
                            $tblScoreRule = null;
                        }
                        $tblScoreType = Gradebook::useService()->getScoreTypeById($item['Type']);
                        if (!$tblScoreType) {
                            $tblScoreType = null;
                        }
                        if ($tblScoreRuleDivisionSubject) {
                            (new Data($this->getBinding()))->updateScoreRuleDivisionSubject(
                                $tblScoreRuleDivisionSubject, $tblScoreRule, $tblScoreType
                            );
                        } else {
                            (new Data($this->getBinding()))->createScoreRuleDivisionSubject(
                                $tblDivision, $tblSubject, $tblScoreRule, $tblScoreType
                            );
                        }
                    }
                }
            }
        }

        return $Stage;
    }
}
