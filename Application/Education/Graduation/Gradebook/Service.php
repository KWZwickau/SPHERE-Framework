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
use SPHERE\Application\Education\Graduation\Gradebook\Service\Setup;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Term;
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
     * @param IFormInterface|null $Stage
     * @param $GradeType
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
                isset($GradeType['IsHighlighted']) ? true : false
            );
            return new Stage('Der Zensuren-Typ ist erfasst worden')
            . new Redirect('/Education/Graduation/Gradebook/GradeType', 0);
        }

        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param null $Select
     * @return IFormInterface|Redirect
     */
    public function getGradeBook(IFormInterface $Stage = null, $Select = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Select) {
            return $Stage;
        }

        $Error = false;
        if (!isset($Select['Division'])) {
            $Error = true;
            $Stage .= new Warning('Klasse nicht gefunden');
        }
        if (!isset($Select['Subject'])) {
            $Error = true;
            $Stage .= new Warning('Fach nicht gefunden');
        }
        if (!isset($Select['ScoreCondition'])) {
            $Error = true;
            $Stage .= new Warning('Berechnungsvorschrift nicht gefunden');
        }
        if ($Error) {
            return $Stage;
        }

        $tblDivision = Division::useService()->getDivisionById($Select['Division']);
        $tblSubject = Subject::useService()->getSubjectById($Select['Subject']);
        $tblScoreCondition = Gradebook::useService()->getScoreConditionById($Select['ScoreCondition']);

        return new Redirect('/Education/Graduation/Gradebook/Selected', 0, array(
            'DivisionId' => $tblDivision->getId(),
            'SubjectId' => $tblSubject->getId(),
            'ScoreConditionId' => $tblScoreCondition->getId()
        ));
    }

    /**
     * @param IFormInterface|null $Stage
     * @param $Data
     * @param $tblPersonList
     * @param TblSubject $tblSubject
     * @param TblDivision $tblDivision
     * @return IFormInterface|Redirect
     */
    public function createGrades(
        IFormInterface $Stage = null,
        $Data,
        $tblPersonList,
        TblSubject $tblSubject,
        TblDivision $tblDivision
    ) {
        if (null === $Data) {
            return $Stage;
        }

        $editId = null;
        if ($tblPersonList) {
            /** @var TblPerson $tblPerson */
            foreach ($tblPersonList as $tblPerson) {

                $grade = (new Data($this->getBinding()))->createGrade($tblPerson, $tblSubject,
                    Term::useService()->getPeriodById($Data['Period']),
                    $this->getGradeTypeById($Data['GradeType']), '');

                if ($editId === null) {
                    $editId = $grade->getId();
                }
            }

            return new Redirect('/Education/Graduation/Gradebook/Selected',
                0,
                array(
                    'DivisionId' => $tblDivision->getId(),
                    'SubjectId' => $tblSubject->getId(),
                    'EditId' => $editId
                ));
        }

        return $Stage;
    }

    /**
     * @return bool|Service\Entity\TblGradeType[]
     */
    public function getGradeTypeAll()
    {

        return (new Data($this->getBinding()))->getGradeTypeAll();
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
     * @param TblSubject $tblSubject
     * @param TblPeriod $tblPeriod
     *
     * @return bool|Service\Entity\TblGrade[]
     */
    public function getGradesByStudentAndSubjectAndPeriod(
        TblPerson $tblPerson,
        TblSubject $tblSubject,
        TblPeriod $tblPeriod
    ) {
        return (new Data($this->getBinding()))->getGradesByStudentAndSubjectAndPeriod($tblPerson, $tblSubject,
            $tblPeriod);
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
     * @param $Id
     * @return bool|Service\Entity\TblTest
     */
    public function getTestById($Id)
    {

        return (new Data($this->getBinding()))->getTestById($Id);
    }

    /**
     * @return bool|Service\Entity\TblTest[]
     */
    public function getTestAll()
    {

        return (new Data($this->getBinding()))->getTestAll();
    }

    /**
     * @param IFormInterface|null $Stage
     * @param $Test
     *
     * @return IFormInterface
     */
    public function createTest(IFormInterface $Stage = null, $Test)
    {
        /**
         * Skip to Frontend
         */
        if (null === $Test) {
            return $Stage;
        }

        $Error = false;
        if (!isset($Test['Division'])) {
            $Error = true;
            $Stage .= new Warning('Klasse nicht gefunden');
        }
        if (!isset($Test['Subject'])) {
            $Error = true;
            $Stage .= new Warning('Fach nicht gefunden');
        }
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

        $tblTest = (new Data($this->getBinding()))->createTest(
            Division::useService()->getDivisionById($Test['Division']),
            Subject::useService()->getSubjectById($Test['Subject']),
            Term::useService()->getPeriodById($Test['Period']),
            $this->getGradeTypeById($Test['GradeType']),
            $Test['Description'],
            $Test['Date'],
            $Test['CorrectionDate'],
            $Test['ReturnDate']
        );

        if ($tblTest) {
            $studentList = Division::useService()->getStudentAllByDivision($tblTest->getServiceTblDivision());
            if ($studentList) {
                foreach ($studentList as $tblPerson) {
                    $this->createGradeToTest($tblTest, $tblPerson);
                }
            }
        }

        return new Stage('Der Test ist erfasst worden')
        . new Redirect('/Education/Graduation/Gradebook/Test', 0);

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

        (new Data($this->getBinding()))->updateTest(
            $this->getTestById($Id),
            $Test['Description'],
            $Test['Date'],
            $Test['CorrectionDate'],
            $Test['ReturnDate']
        );

        return new Redirect('/Education/Graduation/Gradebook/Test', 0);
    }

    /**
     * @param TblTest $tblTest
     * @param TblPerson $tblPerson
     * @param string $Grade
     * @param string $Comment
     *
     * @return null|Service\Entity\TblGrade
     */
    public function createGradeToTest(
        TblTest $tblTest,
        TblPerson $tblPerson,
        $Grade = '',
        $Comment = ''
    ) {
        return (new Data($this->getBinding()))->createGradeToTest($tblTest, $tblPerson, $Grade, $Comment);
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
     * @param $Grade
     * @return IFormInterface|Redirect
     */
    public function updateGradeToTest(IFormInterface $Stage = null, $Grade = null)
    {
        /**
         * Skip to Frontend
         */
        if (null === $Grade) {
            return $Stage;
        }

        foreach ($Grade as $key => $value) {
            $grade = $this->getGradeById($key);
            (new Data($this->getBinding()))->updateGrade($grade, $value['Grade'], $value['Comment']);
        }

        return new Redirect('/Education/Graduation/Gradebook/Test', 0);
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
     * @param TblSubject $tblSubject
     * @param TblScoreCondition $tblScoreCondition
     * @param TblPeriod|null $tblPeriod
     * @param TblDivision $tblDivision
     * @return bool|float|string
     */
    public function calcStudentGrade(
        TblPerson $tblPerson,
        TblSubject $tblSubject,
        TblScoreCondition $tblScoreCondition,
        TblPeriod $tblPeriod = null,
        TblDivision $tblDivision = null
    ) {
        $grades = false;
        if ($tblPeriod !== null) {
            $grades = (new Data($this->getBinding()))->getGradesByStudentAndSubjectAndPeriod($tblPerson, $tblSubject,
                $tblPeriod);
        } elseif ($tblDivision !== null) {
            if (($tblYear = $tblDivision->getServiceTblYear())) {
                if (($tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear))) {
                    $grades = array();
                    foreach ($tblPeriodList as $tblPeriod) {
                        if (($gradesByPeriod = (new Data($this->getBinding()))->getGradesByStudentAndSubjectAndPeriod($tblPerson,
                            $tblSubject,
                            $tblPeriod))
                        ) {
                            if (!empty($grades)) {
                                $grades = array_merge($grades, $gradesByPeriod);
                            } else {
                                $grades = $gradesByPeriod;
                            }
                        }
                    }
                }
            } else {
                return false;
            }

        } else {
            return false;
        }

        return $this->calcAverageFromGrades($tblScoreCondition, $grades);
    }

    /**
     * @param TblScoreCondition $tblScoreCondition
     * @param $grades
     * @return bool|float|string
     */
    private function calcAverageFromGrades(TblScoreCondition $tblScoreCondition, $grades)
    {
        // ToDo JohK isfloat grade = zahl
        // ToDo JohK round
        // ToDo JohK fehler bei nicht vorhandenen Typ
        if ($grades) {
            $result = array();
            $averageGroup = array();
            $resultAverage = '';
            $count = 0;
            /** @var TblGrade $tblGrade */
            foreach ($grades as $tblGrade) {
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
                                        $count++;
                                        $result[$tblScoreCondition->getId()][$tblScoreGroup->getTblScoreGroup()->getId()][]
                                            = floatval($tblGrade->getGrade()) * floatval($tblScoreGroupGradeTypeList->getMultiplier());
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
                                        $averageGroup[$conditionId][$groupId]['Value'] += $value;
                                        $averageGroup[$conditionId][$groupId]['Count']++;
                                    } else {
                                        $averageGroup[$conditionId][$groupId]['Value'] = $value;
                                        $averageGroup[$conditionId][$groupId]['Count'] = 1;
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
                        $totalMultiplier += $multiplier;
                        $average += $multiplier * ($group['Value'] / $group['Count']);
                    }

                    $average = $average / $totalMultiplier;
                    $resultAverage = round($average, 2);
                }
            }

            return $resultAverage;

        } else {
            return false;
        }
    }
}