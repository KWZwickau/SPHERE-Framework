<?php

namespace SPHERE\Application\Education\Graduation\Gradebook;

use SPHERE\Application\Education\Graduation\Gradebook\Service\Data;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Setup;
use SPHERE\Application\Education\Lesson\Division\Division;
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
     * @return IFormInterface
     */
    public function createGrades(
        IFormInterface $Stage = null,
        $Data,
        $tblPersonList,
        TblSubject $tblSubject
    ) {
        if (null === $Data) {
            return $Stage;
        }

        if ($tblPersonList) {
            /** @var TblPerson $tblPerson */
            foreach ($tblPersonList as $tblPerson) {
                (new Data($this->getBinding()))->createGrade($tblPerson, $tblSubject,
                    Term::useService()->getPeriodById($Data['Period']),
                    $this->getGradeTypeById($Data['GradeType']), '');
            }
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
}