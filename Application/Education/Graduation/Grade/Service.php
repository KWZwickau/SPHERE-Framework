<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Education\Graduation\Grade\Service\Data;
use SPHERE\Application\Education\Graduation\Grade\Service\Setup;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\System\Database\Binding\AbstractService;

class Service extends AbstractService
{
    /**
     * @param $doSimulation
     * @param $withData
     * @param $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8): string
    {
        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @return false|TblYear
     */
    public function getYear()
    {
        // todo $_GET Url
        if (($tblYearList = Term::useService()->getYearByNow())) {
            return current($tblYearList);
        }

        return false;
    }

    /**
     * @param array $columnList
     * @param int $size
     *
     * @return array
     */
    public function getLayoutRowsByLayoutColumnList(array $columnList, int $size): array
    {
        $rowList = array();
        $rowCount = 0;
        $row = null;
        foreach ($columnList as $column) {
            if ($rowCount % (12 / $size) == 0) {
                $row = new LayoutRow(array());
                $rowList[] = $row;
            }
            $row->addColumn($column);
            $rowCount++;
        }

        return $rowList;
    }

    /**
     * @param $Data
     * @param TblDivisionCourse|null $tblDivisionCourse
     *
     * @return false|Form
     */
    public function checkFormTeacherGroup($Data, TblDivisionCourse $tblDivisionCourse = null)
    {
        $error = false;
        $form = Grade::useFrontend()->formTeacherGroup($tblDivisionCourse ? $tblDivisionCourse->getId() : null, false, $Data);

        $tblYear = $tblDivisionCourse ? $tblDivisionCourse->getServiceTblYear() : $this->getYear();

        if (!$tblDivisionCourse) {
            if (!isset($Data['Subject']) || !(Subject::useService()->getSubjectById($Data['Subject']))) {
                $form->setError('Data[Subject]', 'Bitte wählen Sie ein Fach aus');
                $error = true;
            }
        }

        if (!isset($Data['Name']) || empty($Data['Name'])) {
            $form->setError('Data[Name]', 'Bitte geben Sie einen Name ein');
            $error = true;
        }
        if (isset($Data['Name']) && $Data['Name'] != '') {
            // Prüfung ob name schon mal verwendet wird
            if ($tblYear && ($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear))) {
                foreach ($tblDivisionCourseList as $tblDivisionCourseItem) {
                    if ($tblDivisionCourse && $tblDivisionCourse->getId() == $tblDivisionCourseItem->getId()) {
                        continue;
                    }

                    if (strtolower($Data['Name']) == strtolower($tblDivisionCourseItem->getName())) {
                        $form->setError('Data[Name]', 'Ein Kurs mit diesem Name existiert bereits im Schuljahr');
                        $error = true;
                    }
                }
            }
        }

        return $error ? $form : false;
    }
}