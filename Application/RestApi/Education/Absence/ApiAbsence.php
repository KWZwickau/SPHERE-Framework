<?php

namespace SPHERE\Application\RestApi\Education\Absence;

use SPHERE\Application\Education\Absence\Absence;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\ParentStudentAccess\OnlineAbsence\OnlineAbsence;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Main;

class ApiAbsence
{
    public static function registerApi(): void
    {
        Main::getRestApiDispatcher()->registerRoute(Main::getRestApiDispatcher()->createRoute(
            __NAMESPACE__ . '/Load', __CLASS__ . '::getAbsenceLoad',
        ));
        Main::getRestApiDispatcher()->registerRoute(Main::getRestApiDispatcher()->createRoute(
            __NAMESPACE__ . '/Save', __CLASS__ . '::getAbsenceSave',
        ));
    }

    /**
     * @return array
     */
    public static function getAbsenceLoad(): array
    {
        list($tblPersonList, $source) = OnlineAbsence::useService()->getPersonListAndSourceFromAccountBySession();

        $result = array();
        if ($tblPersonList) {
            /** @var TblPerson $tblPerson */
            foreach ($tblPersonList as $tblPerson) {
                $hasAbsenceTypeOptions = false;
                $absenceList = array();
                if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson))
                    && ($tableData = Absence::useService()->getStudentAbsenceDataForParentStudentAccess($tblPerson, $tblStudentEducation, $hasAbsenceTypeOptions))
                ) {
                    foreach ($tableData as $item) {
                        $temp = array(
                            'FromDate' => $item['FromDate'],
                            'ToDate' => $item['ToDate'] === false ? null : $item['ToDate'],
                            'DaysCount' => $item['DaysCount'],
                            'LessonsCount' => $item['LessonsCount'],
                            'StatusShort' => $item['StatusShort'],
                            'PersonCreator' => $item['PersonCreator']
                        );
                        if ($hasAbsenceTypeOptions) {
                            $temp['Type'] = $item['Type'];
                        }

                        $absenceList[] = $temp;
                    }
                }

                $result[] = array(
                    'Person' => $tblPerson->getLastFirstName(),
                    'Division' => DivisionCourse::useService()->getCurrentMainCoursesByPersonAndDate($tblPerson),
                    'AbsenceList' => $absenceList,
                );
            }
        }

        return $result;
    }
}