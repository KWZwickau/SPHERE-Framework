<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 15.12.2015
 * Time: 09:41
 */

namespace SPHERE\Application\Education\Graduation\Evaluation;

use SPHERE\Application\Education\Graduation\Evaluation\Service\Data;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Setup;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Education\Graduation\Evaluation
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
     * @param $Id
     * @return bool|TblTest
     */
    public function getTestById($Id)
    {

        return (new Data($this->getBinding()))->getTestById($Id);
    }

    /**
     * @param TblTestType $tblTestType
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param null|TblPeriod $tblPeriod
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
     * @param TblTestType $tblTestType
     * @return bool|TblTest[]
     */
    public function getTestAllByTestType(TblTestType $tblTestType)
    {

        return (new Data($this->getBinding()))->getTestAllByTestType($tblTestType);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param null $DivisionSubjectId
     * @param null $Test
     * @param string $BasicRoute
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
            Gradebook::useService()->getGradeTypeById($Test['GradeType']),
            $this->getTestTypeByIdentifier('TEST'),
            $Test['Description'],
            $Test['Date'],
            $Test['CorrectionDate'],
            $Test['ReturnDate']
        );

        return new Stage('Der Test ist erfasst worden')
        . new Redirect($BasicRoute . '/Selected', 0,
            array('DivisionSubjectId' => $tblDivisionSubject->getId()));

    }

    /**
     * @param IFormInterface|null $Stage
     * @param $Id
     * @param $Test
     * @param string $BasicRoute
     * @return IFormInterface|Redirect
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

        return new Redirect($BasicRoute . '/Selected', 0,
            array('DivisionSubjectId' => $tblDivisionSubject->getId()));
    }


    public function createAppointedDateTask(IFormInterface $Stage = null, $Task){
        /**
         * Skip to Frontend
         */
        if (null === $Task) {
            return $Stage;
        }


        return $Stage;
    }

}