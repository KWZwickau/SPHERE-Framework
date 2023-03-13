<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 28.09.2016
 * Time: 13:03
 */

namespace SPHERE\Application\Education\Certificate\GradeInformation;

use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\Certificate\GradeInformation
 */
class Service
{
    /**
     * @param IFormInterface|null $form
     * @param TblPrepareCertificate $tblPrepare
     * @param string $Route
     * @param $Grades
     * @param $Remarks
     *
     * @return IFormInterface|string
     */
    public function updatePrepareBehaviorGradesAndRemark(
        IFormInterface $form,
        TblPrepareCertificate $tblPrepare,
        string $Route,
        $Grades,
        $Remarks
    ) {
        /**
         * Skip to Frontend
         */
        if (null === $Grades && null === $Remarks) {
            return $form;
        }

        if ($Grades) {
            $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR_TASK');
            foreach ($Grades as $personId => $personGrades) {
                if (($tblPerson = Person::useService()->getPersonById($personId))
                    && is_array($personGrades)
                ) {
                    foreach ($personGrades as $gradeTypeId => $value) {
                        if (trim($value) && trim($value) !== ''
                            && ($tblGradeType = Grade::useService()->getGradeTypeById($gradeTypeId))
                        ) {
                            Prepare::useService()->updatePrepareGradeForBehavior(
                                $tblPrepare, $tblPerson, $tblTestType, $tblGradeType, trim($value)
                            );
                        }
                    }
                }
            }
        }

        if ($Remarks) {
            foreach ($Remarks as $personId => $remark) {
                if (($tblPerson = Person::useService()->getPersonById($personId))) {
                    $Content['P' . $personId]['Input']['Remark'] = $remark;

                    Prepare::useService()->updatePrepareInformationDataList($tblPrepare, $tblPerson, $Content);
                }
            }
        }

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Informationen wurden gespeichert.')
            . new Redirect('/Education/Certificate/GradeInformation/Setting/Preview',
                Redirect::TIMEOUT_SUCCESS, array(
                    'PrepareId' => $tblPrepare->getId(),
                    'Route' => $Route
                ));
    }
}