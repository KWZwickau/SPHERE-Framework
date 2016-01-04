<?php

namespace SPHERE\Application\Education\Graduation\Gradebook;

use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
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
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Setup;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
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
    public function createGradeTypeWhereTest(IFormInterface $Stage = null, $GradeType)
    {

        /**
         * Skip to Frontend
         */
        if (null === $GradeType) {
            return $Stage;
        }

        $Error = false;
        if (isset( $GradeType['Name'] ) && empty( $GradeType['Name'] )) {
            $Stage->setError('GradeType[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        }
        if (isset( $GradeType['Code'] ) && empty( $GradeType['Code'] )) {
            $Stage->setError('GradeType[Code]', 'Bitte geben sie eine Abk&uuml;rzung an');
            $Error = true;
        }

        if (!$Error) {
            (new Data($this->getBinding()))->createGradeType(
                $GradeType['Name'],
                $GradeType['Code'],
                $GradeType['Description'],
                isset( $GradeType['IsHighlighted'] ) ? true : false,
                Evaluation::useService()->getTestTypeByIdentifier('TEST')
            );
            return new Stage('Der Zensuren-Typ ist erfasst worden')
            .new Redirect('/Education/Graduation/Gradebook/GradeType', 0);
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
        if (isset( $GradeType['Name'] ) && empty( $GradeType['Name'] )) {
            $Stage->setError('GradeType[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        }
        if (isset( $GradeType['Code'] ) && empty( $GradeType['Code'] )) {
            $Stage->setError('GradeType[Code]', 'Bitte geben sie eine Abk&uuml;rzung an');
            $Error = true;
        }

        $tblGradeType = $this->getGradeTypeById($Id);
        if (!$tblGradeType) {
            return new Stage('Zensuren-Typ nicht gefunden')
            .new Redirect('/Education/Graduation/Gradebook/GradeType', 2);
        }

        if (!$Error) {
            (new Data($this->getBinding()))->updateGradeType(
                $tblGradeType,
                $GradeType['Name'],
                $GradeType['Code'],
                $GradeType['Description'],
                isset( $GradeType['IsHighlighted'] ) ? true : false
            );
            return new Stage('Der Zensuren-Typ ist erfasst worden')
            .new Redirect('/Education/Graduation/Gradebook/GradeType', 0);
        }

        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param null                $DivisionSubjectId
     * @param null                $Select
     * @param string              $BasicRoute
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
        if (!isset( $Select['ScoreCondition'] )) {
            $Error = true;
            $Stage .= new Warning('Berechnungsvorschrift nicht gefunden');
        }
        if ($Error) {
            return $Stage;
        }

        $tblScoreCondition = Gradebook::useService()->getScoreConditionById($Select['ScoreCondition']);

        return new Redirect($BasicRoute.'/Selected', 0, array(
            'DivisionSubjectId' => $DivisionSubjectId,
            'ScoreConditionId'  => $tblScoreCondition->getId()
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
     *
     * @return bool|Service\Entity\TblGradeType
     */
    public function getGradeTypeById($Id)
    {

        return (new Data($this->getBinding()))->getGradeTypeById($Id);
    }

    /**
     * @param TblPerson                                                                      $tblPerson
     * @param TblDivision                                                                    $tblDivision
     * @param TblSubject                                                                     $tblSubject
     * @param \SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType $tblTestType
     * @param TblPeriod|null                                                                 $tblPeriod
     * @param TblSubjectGroup|null                                                           $tblSubjectGroup
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
     * @param $Id
     *
     * @return bool|Service\Entity\TblGrade
     */
    public function getGradeById($Id)
    {

        return (new Data($this->getBinding()))->getGradeById($Id);
    }

    /**
     * @param TblTest   $tblTest
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
     * @param null                $TestId
     * @param                     $Grade
     * @param string              $BasicRoute
     * @param bool                $IsEdit
     *
     * @return IFormInterface|Redirect
     */
    public function updateGradeToTest(IFormInterface $Stage = null, $TestId = null, $Grade = null, $BasicRoute, $IsEdit)
    {

        /**
         * Skip to Frontend
         */
        if ($TestId === null) {
            return $Stage;
        }
        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Button']['Submit'] )) {
            return $Stage;
        }

        $tblTest = Evaluation::useService()->getTestById($TestId);

        if (!empty( $Grade )) {
            foreach ($Grade as $personId => $value) {
                $tblPerson = Person::useService()->getPersonById($personId);
                if (!( $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest, $tblPerson) )) {
                    if (isset( $value['Attendance'] )) {
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
                            trim($value['Comment'])
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
                            trim($value['Comment'])
                        );
                    }
                } elseif ($IsEdit && $tblGrade) {
                    if (isset( $value['Attendance'] )) {
                        (new Data($this->getBinding()))->updateGrade(
                            $tblGrade,
                            null,
                            trim($value['Comment'])
                        );
                    } else {
                        (new Data($this->getBinding()))->updateGrade(
                            $tblGrade,
                            trim($value['Grade']),
                            trim($value['Comment'])
                        );
                    }
                }
            }
        }

//        $tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
//            $tblTest->getServiceTblDivision(),
//            $tblTest->getServiceTblSubject(),
//            $tblTest->getServiceTblSubjectGroup() ? $tblTest->getServiceTblSubjectGroup() : null
//        );
//        return new Redirect($BasicRoute . '/Selected', 0,
//            array('DivisionSubjectId' => $tblDivisionSubject->getId()));
        return new Redirect($BasicRoute.'/Grade/Edit', 0,
            array('Id' => $tblTest->getId()));
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
     * @param $Id
     *
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
     *
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
     *
     * @return bool|TblScoreRule
     */
    public function getScoreRuleById($Id)
    {

        return (new Data($this->getBinding()))->getScoreRuleById($Id);
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
     * @param TblScoreCondition $tblScoreCondition
     *
     * @return bool|TblScoreConditionGroupList[]
     */
    public function getScoreConditionGroupListByCondition(TblScoreCondition $tblScoreCondition)
    {

        return (new Data($this->getBinding()))->getScoreConditionGroupListByCondition($tblScoreCondition);
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
     * @param TblScoreGroup $tblScoreGroup
     *
     * @return bool|TblScoreGroupGradeTypeList[]
     */
    public function getScoreGroupGradeTypeListByGroup(TblScoreGroup $tblScoreGroup)
    {

        return (new Data($this->getBinding()))->getScoreGroupGradeTypeListByGroup($tblScoreGroup);
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
        if (isset( $ScoreCondition['Name'] ) && empty( $ScoreCondition['Name'] )) {
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
            .new Redirect('/Education/Graduation/Gradebook/Score', 0);
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
        if (isset( $ScoreGroup['Name'] ) && empty( $ScoreGroup['Name'] )) {
            $Stage->setError('ScoreGroup[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        }
        if (isset( $ScoreGroup['Multiplier'] ) && empty( $ScoreGroup['Multiplier'] )) {
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
            .new Redirect('/Education/Graduation/Gradebook/Score/Group', 0);
        }

        return $Stage;
    }

    /**
     * @param TblGradeType  $tblGradeType
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
            return new Success('Erfolgreich hinzugefügt.').
            new Redirect('/Education/Graduation/Gradebook/Score/Group/GradeType/Select', 0,
                array('Id' => $tblScoreGroup->getId()));
        } else {
            return new Warning('Konnte nicht hinzugefügt werden.').
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
            return new Success('Erfolgreich entfernt.').
            new Redirect('/Education/Graduation/Gradebook/Score/Group/GradeType/Select', 0,
                array('Id' => $tblScoreGroup->getId()));
        } else {
            return new Warning('Konnte nicht entfernt werden.').
            new Redirect('/Education/Graduation/Gradebook/Score/Group/GradeType/Select', 0,
                array('Id' => $tblScoreGroup->getId()));
        }
    }

    /**
     * @param TblScoreCondition $tblScoreCondition
     * @param TblScoreGroup     $tblScoreGroup
     *
     * @return string
     */
    public function addScoreConditionGroupList(
        TblScoreCondition $tblScoreCondition,
        TblScoreGroup $tblScoreGroup
    ) {

        if ((new Data($this->getBinding()))->addScoreConditionGroupList($tblScoreCondition, $tblScoreGroup)) {
            return new Success('Erfolgreich hinzugefügt.').
            new Redirect('/Education/Graduation/Gradebook/Score/Group/Select', 0,
                array('Id' => $tblScoreCondition->getId()));
        } else {
            return new Warning('Konnte nicht hinzugefügt werden.').
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
            return new Success('Erfolgreich entfernt.').
            new Redirect('/Education/Graduation/Gradebook/Score/Group/Select', 0,
                array('Id' => $tblScoreCondition->getId()));
        } else {
            return new Warning('Konnte nicht entfernt werden.').
            new Redirect('/Education/Graduation/Gradebook/Score/Group/Select', 0,
                array('Id' => $tblScoreCondition->getId()));
        }
    }

    /**
     * @param TblPerson                                                                      $tblPerson
     * @param TblDivision                                                                    $tblDivision
     * @param TblSubject                                                                     $tblSubject
     * @param \SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType $tblTestType
     * @param TblScoreCondition                                                              $tblScoreCondition
     * @param TblPeriod|null                                                                 $tblPeriod
     * @param TblSubjectGroup|null                                                           $tblSubjectGroup
     *
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
                    if (( $tblScoreConditionGroupListByCondition
                        = Gradebook::useService()->getScoreConditionGroupListByCondition($tblScoreCondition) )
                    ) {
                        foreach ($tblScoreConditionGroupListByCondition as $tblScoreGroup) {
                            if (( $tblScoreGroupGradeTypeListByGroup
                                = Gradebook::useService()->getScoreGroupGradeTypeListByGroup($tblScoreGroup->getTblScoreGroup()) )
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

            if (!empty( $result )) {
                foreach ($result as $conditionId => $groups) {
                    if (!empty( $groups )) {
                        foreach ($groups as $groupId => $group) {
                            if (!empty( $group )) {
                                foreach ($group as $value) {
                                    if (isset( $averageGroup[$conditionId][$groupId] )) {
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

                if (!empty( $averageGroup[$tblScoreCondition->getId()] )) {
                    $average = 0;
                    $totalMultiplier = 0;
                    foreach ($averageGroup[$tblScoreCondition->getId()] as $groupId => $group) {
                        $tblScoreGroup = Gradebook::useService()->getScoreGroupById($groupId);
                        $multiplier = floatval($tblScoreGroup->getMultiplier());
                        if ($group['Value'] > 0) {
                            $totalMultiplier += $multiplier;
                            $average += $multiplier * ( $group['Value'] / $group['Multiplier'] );
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

            return $resultAverage == '' ? false : $resultAverage;
        }

        return false;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param null                $Select
     * @param string              $Redirect
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
        if (!isset( $Select['Year'] )) {
            $Error = true;
            $Stage .= new Warning('Schuljahr nicht gefunden');
        }
        if ($Error) {
            return $Stage;
        }

        return new Redirect($Redirect, 0, array(
            'YearId' => $Select['Year'],
        ));
    }
}
