<?php

namespace SPHERE\Application\Education\Graduation\Gradebook;

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
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblTestType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Setup;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
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
     * @param $Id
     *
     * @return bool|TblTestType
     */
    public function getTestTypeById($Id)
    {

        return (new Data($this->getBinding()))->getTestTypeById($Id);
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
     * @param $GradeType
     * @return IFormInterface|string
     */
    public function createGradeTypeWhereTest(IFormInterface $Stage = null, $GradeType)
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
                $this->getTestTypeByIdentifier('TEST')
            );
            return new Stage('Der Zensuren-Typ ist erfasst worden')
            . new Redirect('/Education/Graduation/Gradebook/GradeType', 0);
        }

        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param $Id
     * @param $GradeType
     * @param bool $IsOpen
     * @return IFormInterface|string
     */
    public function updateGradeType(IFormInterface $Stage = null, $Id, $GradeType, $IsOpen = false)
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
            $Stage->setError('GradeType[Code]', 'Bitte geben sie eine Abk&uuml;rzung an');
            $Error = true;
        }

        $tblGradeType = $this->getGradeTypeById($Id);
        if (!$tblGradeType) {
            return new Stage('Zensuren-Typ nicht gefunden')
            . new Redirect('/Education/Graduation/Gradebook/GradeType', 2, array('IsOpen' => $IsOpen));
        }

        if (!$Error) {
            (new Data($this->getBinding()))->updateGradeType(
                $tblGradeType,
                $GradeType['Name'],
                $GradeType['Code'],
                $GradeType['Description'],
                isset($GradeType['IsHighlighted']) ? true : false
            );
            return new Stage('Der Zensuren-Typ ist erfasst worden')
            . new Redirect('/Education/Graduation/Gradebook/GradeType', 0, array('IsOpen' => $IsOpen));
        }

        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param null $DivisionSubjectId
     * @param null $Select
     * @return IFormInterface|Redirect
     */
    public function getGradeBook(IFormInterface $Stage = null, $DivisionSubjectId = null, $Select = null)
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
            $Stage .= new Warning('Berechnungsvorschrift nicht gefunden');
        }
        if ($Error) {
            return $Stage;
        }

        $tblScoreCondition = Gradebook::useService()->getScoreConditionById($Select['ScoreCondition']);

        return new Redirect('/Education/Graduation/Gradebook/Gradebook/Selected', 0, array(
            'DivisionSubjectId' => $DivisionSubjectId,
            'ScoreConditionId' => $tblScoreCondition->getId()
        ));
    }

    /**
     * @return bool|Service\Entity\TblGradeType[]
     */
    public function getGradeTypeAllWhereTest()
    {

        return (new Data($this->getBinding()))->getGradeTypeAllWhereTest();
    }

    /**
     * @param $Id
     * @return bool|Service\Entity\TblGradeType
     */
    public function getGradeTypeById($Id)
    {

        return (new Data($this->getBinding()))->getGradeTypeById($Id);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblTestType $tblTestType
     * @param TblPeriod|null $tblPeriod
     * @param TblSubjectGroup|null $tblSubjectGroup
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
     * @param $Id
     * @return bool|Service\Entity\TblGrade
     */
    public function getGradeById($Id)
    {

        return (new Data($this->getBinding()))->getGradeById($Id);
    }

    /**
     * @param TblTest $tblTest
     * @param TblPerson $tblPerson
     * @return bool|TblGrade
     */
    public function getGradeByTestAndStudent(
        TblTest $tblTest,
        TblPerson $tblPerson
    ) {

        return (new Data($this->getBinding()))->getGradeByTestAndStudent($tblTest, $tblPerson);
    }

    /**
     * @param $Id
     * @return bool|Service\Entity\TblTest
     */
    public function getTestById($Id)
    {

        return (new Data($this->getBinding()))->getTestById($Id);
    }

    /**
     * @param TblTestType $tblTestType
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblPeriod|null $tblPeriod
     * @param TblSubjectGroup|null $tblSubjectGroup
     * @return bool|TblTest[]
     */
    public function getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
        TblTestType $tblTestType,
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblPeriod $tblPeriod = null,
        TblSubjectGroup $tblSubjectGroup = null
    ) {

        return (new Data($this->getBinding()))->getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
            $tblTestType, $tblDivision, $tblSubject, $tblPeriod, $tblSubjectGroup
        );
    }

    /**
     * @param IFormInterface|null $Stage
     * @param null $DivisionSubjectId
     * @param null $Test
     * @return IFormInterface|string
     */
    public function createTest(IFormInterface $Stage = null, $DivisionSubjectId = null, $Test = null)
    {
        /**
         * Skip to Frontend
         */
        if (null === $Test || $DivisionSubjectId === null) {
            return $Stage;
        }

        $Error = false;
        if (!isset($Test['Period'])) {
            $Error = true;
            $Stage .= new Warning('Zeitraum nicht gefunden');
        }
        if (!isset($Test['GradeType'])) {
            $Error = true;
            $Stage .= new Warning('Zensuren-Typ nicht gefunden');
        }
        if ($Error) {
            return $Stage;
        }

        $tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId);

        (new Data($this->getBinding()))->createTest(
            $tblDivisionSubject->getTblDivision(),
            $tblDivisionSubject->getServiceTblSubject(),
            $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null,
            Term::useService()->getPeriodById($Test['Period']),
            $this->getGradeTypeById($Test['GradeType']),
            $this->getTestTypeByIdentifier('TEST'),
            $Test['Description'],
            $Test['Date'],
            $Test['CorrectionDate'],
            $Test['ReturnDate']
        );

        return new Stage('Der Test ist erfasst worden')
        . new Redirect('/Education/Graduation/Gradebook/Test/Selected', 0,
            array('DivisionSubjectId' => $tblDivisionSubject->getId()));

    }

    /**
     * @param IFormInterface|null $Stage
     * @param $Id
     * @param $Test
     *
     * @return IFormInterface|Redirect
     */
    public function updateTest(IFormInterface $Stage = null, $Id, $Test)
    {
        /**
         * Skip to Frontend
         */
        if (null === $Test) {
            return $Stage;
        }

        $tblTest = $this->getTestById($Id);
        (new Data($this->getBinding()))->updateTest(
            $tblTest,
            $Test['Description'],
            $Test['Date'],
            $Test['CorrectionDate'],
            $Test['ReturnDate']
        );

        $tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
            $tblTest->getServiceTblDivision(),
            $tblTest->getServiceTblSubject(),
            $tblTest->getServiceTblSubjectGroup() ? $tblTest->getServiceTblSubjectGroup() : null
        );


        return new Redirect('/Education/Graduation/Gradebook/Test/Selected', 0,
            array('DivisionSubjectId' => $tblDivisionSubject->getId()));
    }

    /**
     * @param TblTest $tblTest
     * @return TblGrade[]|bool
     */
    public function getGradeAllByTest(TblTest $tblTest)
    {
        return (new Data($this->getBinding()))->getGradeAllByTest($tblTest);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param null $TestId
     * @param $Grade
     * @return IFormInterface|Redirect
     */
    public function updateGradeToTest(IFormInterface $Stage = null, $TestId = null, $Grade = null)
    {
        /**
         * Skip to Frontend
         */
        if (null === $Grade || $TestId === null) {
            return $Stage;
        }

        $tblTest = $this->getTestById($TestId);
        $tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
            $tblTest->getServiceTblDivision(),
            $tblTest->getServiceTblSubject(),
            $tblTest->getServiceTblSubjectGroup() ? $tblTest->getServiceTblSubjectGroup() : null
        );

        foreach ($Grade as $personId => $value) {
            $tblPerson = Person::useService()->getPersonById($personId);
            if (!Gradebook::useService()->getGradeByTestAndStudent($tblTest, $tblPerson)) {
                if (trim($value['Grade']) !== '') {
                    (new Data($this->getBinding()))->createGrade(
                        $tblPerson,
                        $tblTest->getServiceTblDivision(),
                        $tblTest->getServiceTblSubject(),
                        $tblTest->getServiceTblSubjectGroup() ? $tblTest->getServiceTblSubjectGroup() : null,
                        $tblTest->getServiceTblPeriod(),
                        $tblTest->getTblGradeType(),
                        $tblTest,
                        $tblTest->getTblTestType(),
                        trim($value['Grade']),
                        trim($value['Comment'])
                    );
                }
            }
        }

        return new Redirect('/Education/Graduation/Gradebook/Test/Selected', 0,
            array('DivisionSubjectId' => $tblDivisionSubject->getId()));
    }

    /**
     * @param $Id
     * @return bool|TblScoreGroup
     */
    public function getScoreGroupById($Id)
    {
        return (new Data($this->getBinding()))->getScoreGroupById($Id);
    }

    /**
     * @return bool|TblScoreGroup[]
     */
    public function getScoreGroupAll()
    {
        return (new Data($this->getBinding()))->getScoreGroupAll();
    }

    /**
     * @param $Id
     * @return bool|TblScoreCondition
     */
    public function getScoreConditionById($Id)
    {
        return (new Data($this->getBinding()))->getScoreConditionById($Id);
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
     * @return bool|TblScoreRule
     */
    public function getScoreRuleById($Id)
    {
        return (new Data($this->getBinding()))->getScoreRuleById($Id);
    }

    /**
     * @param $Id
     * @return bool|TblScoreRuleConditionList
     */
    public function getScoreRuleConditionListById($Id)
    {
        return (new Data($this->getBinding()))->getScoreRuleConditionListById($Id);
    }

    /**
     * @param $Id
     * @return bool|TblScoreConditionGradeTypeList
     */
    public function getScoreConditionGradeTypeListById($Id)
    {
        return (new Data($this->getBinding()))->getScoreConditionGradeTypeListById($Id);
    }

    /**
     * @param $Id
     * @return bool|TblScoreConditionGroupList
     */
    public function getScoreConditionGroupListById($Id)
    {
        return (new Data($this->getBinding()))->getScoreConditionGroupListById($Id);
    }

    /**
     * @param TblScoreCondition $tblScoreCondition
     * @return bool|TblScoreConditionGroupList[]
     */
    public function getScoreConditionGroupListByCondition(TblScoreCondition $tblScoreCondition)
    {
        return (new Data($this->getBinding()))->getScoreConditionGroupListByCondition($tblScoreCondition);
    }

    /**
     * @param $Id
     * @return bool|TblScoreGroupGradeTypeList
     */
    public function getScoreGroupGradeTypeListById($Id)
    {
        return (new Data($this->getBinding()))->getScoreGroupGradeTypeListById($Id);
    }

    /**
     * @param TblScoreGroup $tblScoreGroup
     * @return bool|TblScoreGroupGradeTypeList[]
     */
    public function getScoreGroupGradeTypeListByGroup(TblScoreGroup $tblScoreGroup)
    {
        return (new Data($this->getBinding()))->getScoreGroupGradeTypeListByGroup($tblScoreGroup);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param $ScoreCondition
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
            return new Stage('Die Berechnungsvorschrift ist erfasst worden')
            . new Redirect('/Education/Graduation/Gradebook/Score', 0);
        }

        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param $ScoreGroup
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
            return new Stage('Die Zensuren-Gruppe ist erfasst worden')
            . new Redirect('/Education/Graduation/Gradebook/Score/Group', 0);
        }

        return $Stage;
    }

    /**
     * @param TblGradeType $tblGradeType
     * @param TblScoreGroup $tblScoreGroup
     * @param $Multiplier
     *
     * @return TblScoreGroupGradeTypeList
     */
    public function addScoreGroupGradeTypeList(
        TblGradeType $tblGradeType,
        TblScoreGroup $tblScoreGroup,
        $Multiplier
    ) {
        if ((new Data($this->getBinding()))->addScoreGroupGradeTypeList($tblGradeType, $tblScoreGroup, $Multiplier)) {
            return new Success('Erfolgreich hinzugefügt.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Group/GradeType/Select', 0,
                array('Id' => $tblScoreGroup->getId()));
        } else {
            return new Warning('Konnte nicht hinzugefügt werden.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Group/GradeType/Select', 0,
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
            return new Success('Erfolgreich entfernt.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Group/GradeType/Select', 0,
                array('Id' => $tblScoreGroup->getId()));
        } else {
            return new Warning('Konnte nicht entfernt werden.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Group/GradeType/Select', 0,
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
            return new Success('Erfolgreich hinzugefügt.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Group/Select', 0,
                array('Id' => $tblScoreCondition->getId()));
        } else {
            return new Warning('Konnte nicht hinzugefügt werden.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Group/Select', 0,
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
            return new Success('Erfolgreich entfernt.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Group/Select', 0,
                array('Id' => $tblScoreCondition->getId()));
        } else {
            return new Warning('Konnte nicht entfernt werden.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Group/Select', 0,
                array('Id' => $tblScoreCondition->getId()));
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblTestType $tblTestType
     * @param TblScoreCondition $tblScoreCondition
     * @param TblPeriod|null $tblPeriod
     * @param TblSubjectGroup|null $tblSubjectGroup
     * @return bool|float
     */
    public function calcStudentGrade(
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblTestType $tblTestType,
        TblScoreCondition $tblScoreCondition,
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
            foreach ($tblGradeList as $tblGrade) {
                if ($tblScoreCondition) {
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

            return $resultAverage == '' ? false: $resultAverage;
        }

        return false;
    }

}