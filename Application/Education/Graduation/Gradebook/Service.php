<?php

namespace SPHERE\Application\Education\Graduation\Gradebook;

use SPHERE\Application\Education\Graduation\Gradebook\Service\Data;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeStudentSubjectLink;
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

        $tblDivision = Division::useService()->getDivisionById($Select['Division']);
        $tblSubject = Subject::useService()->getSubjectById($Select['Subject']);

        return new Redirect('/Education/Graduation/Gradebook/Selected', 0, array(
            'DivisionId' => $tblDivision->getId(),
            'SubjectId' => $tblSubject->getId(),
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
     * @return bool|Service\Entity\TblGradeStudentSubjectLink[]
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
     * @return bool|Service\Entity\TblGradeStudentSubjectLink
     */
    public function getGradeById($Id)
    {

        return (new Data($this->getBinding()))->getGradeById($Id);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param $Id
     * @param $GradeData
     *
     * @return IFormInterface
     */
    public function updateGrade(
        IFormInterface $Stage = null,
        $Id,
        $GradeData
    ) {

        var_dump($GradeData);
        if (null === $GradeData) {
            return $Stage;
        }

        (new Data($this->getBinding()))->updateGrade($this->getGradeById($Id), $GradeData[$Id]);

        return $Stage;
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
     * @return null|Service\Entity\TblGradeStudentSubjectLink
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
     * @return TblGradeStudentSubjectLink[]|bool
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
    public function updateGradeToTest(IFormInterface $Stage = null, $Grade)
    {
        /**
         * Skip to Frontend
         */
        if (null === $Grade) {
            return $Stage;
        }

        foreach ($Grade as $key => $value) {
            (new Data($this->getBinding()))->updateGrade($this->getGradeById($key), $value['Grade'], $value['Comment']);
        }

        return new Redirect('/Education/Graduation/Gradebook/Test', 0);
    }
}