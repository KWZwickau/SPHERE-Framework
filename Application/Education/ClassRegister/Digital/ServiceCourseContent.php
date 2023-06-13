<?php

namespace SPHERE\Application\Education\ClassRegister\Digital;

use DateTime;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Data;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblCourseContent;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\System\Database\Binding\AbstractService;

abstract class ServiceCourseContent extends AbstractService
{
    /**
     * @param $Id
     *
     * @return false|TblCourseContent
     */
    public function getCourseContentById($Id)
    {
        return (new Data($this->getBinding()))->getCourseContentById($Id);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return false|TblCourseContent[]
     */
    public function getCourseContentListBy(TblDivisionCourse $tblDivisionCourse)
    {
        return (new Data($this->getBinding()))->getCourseContentListBy($tblDivisionCourse);
    }

    /**
     * @param $Data
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblCourseContent|null $tblCourseContent
     *
     * @return false|Form
     */
    public function checkFormCourseContent(
        $Data,
        TblDivisionCourse $tblDivisionCourse,
        TblCourseContent $tblCourseContent = null
    ) {
        $error = false;

        $form = Digital::useFrontend()->formCourseContent(
            $tblDivisionCourse, $tblCourseContent ? $tblCourseContent->getId() : null
        );
        if (isset($Data['Date']) && empty($Data['Date'])) {
            $form->setError('Data[Date]', 'Bitte geben Sie ein Datum an');
            $error = true;
        } else {
            // Prüfung ob das Datum innerhalb des Schuljahres liegt.
            if (($tblYear = $tblDivisionCourse->getServiceTblYear())) {
                list($startDateSchoolYear, $endDateSchoolYear) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
                if ($startDateSchoolYear && $endDateSchoolYear) {
                    $date = new DateTime($Data['Date']);
                    if ($date < $startDateSchoolYear || $date > $endDateSchoolYear) {
                        $form->setError('Data[Date]', 'Das ausgewählte Datum: ' . $Data['Date'] . ' befindet sich außerhalb des Schuljahres.');
                        $error = true;
                    }
                } else {
                    $form->setError('Data[Date]', 'Das Schuljahr besitzt keinen Zeitraum');
                    $error = true;
                }
            } else {
                $form->setError('Data[Date]', 'Kein Schuljahr gefunden');
                $error = true;
            }
        }
        if (isset($Data['Lesson']) && $Data['Lesson'] == 0) {
            $form->setError('Data[Lesson]', 'Bitte geben Sie eine Unterrichtseinheit an');
            $error = true;
        }

        return $error ? $form : false;
    }

    /**
     * @param $Data
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return bool
     */
    public function createCourseContent($Data, TblDivisionCourse $tblDivisionCourse): bool
    {
        // key -1 bei 0. UE
        $lesson = $Data['Lesson'];
        if ($lesson == -1) {
            $lesson = 0;
        }

        (new Data($this->getBinding()))->createCourseContent(
            $tblDivisionCourse,
            $Data['Date'],
            $lesson,
            $Data['Content'],
            $Data['Homework'],
            $Data['Remark'],
            $Data['Room'],
            isset($Data['IsDoubleLesson']),
            ($tblPerson = Account::useService()->getPersonByLogin()) ? $tblPerson : null
        );

        return  true;
    }

    /**
     * @param TblCourseContent $tblCourseContent
     * @param $Data
     *
     * @return bool
     */
    public function updateCourseContent(TblCourseContent $tblCourseContent, $Data): bool
    {
        // key -1 bei 0. UE
        $lesson = $Data['Lesson'];
        if ($lesson == -1) {
            $lesson = 0;
        }

        return (new Data($this->getBinding()))->updateCourseContent(
            $tblCourseContent,
            $Data['Date'],
            $lesson,
            $Data['Content'],
            $Data['Homework'],
            $Data['Remark'],
            $Data['Room'],
            isset($Data['IsDoubleLesson']),
            ($tblPerson = Account::useService()->getPersonByLogin()) ? $tblPerson : null
        );
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     */
    public function updateBulkCourseContentHeadmaster(TblDivisionCourse $tblDivisionCourse)
    {
        $updateList = array();
        if (($tblCourseContentList = $this->getCourseContentListBy($tblDivisionCourse))) {
            foreach ($tblCourseContentList as $tblCourseContent) {
                if (!$tblCourseContent->getDateHeadmaster() || !$tblCourseContent->getServiceTblPersonHeadmaster()) {
                    $updateList[] = $tblCourseContent;
                }
            }
        }

        if ($updateList && ($tblPerson = Account::useService()->getPersonByLogin())) {
            (new Data($this->getBinding()))->updateBulkCourseContent($updateList, (new DateTime('today'))->format('d.m.Y'), $tblPerson);
        }
    }

    /**
     * @param TblCourseContent $tblCourseContent
     *
     * @return bool
     */
    public function destroyCourseContent(TblCourseContent $tblCourseContent): bool
    {
        return (new Data($this->getBinding()))->destroyCourseContent($tblCourseContent);
    }
}