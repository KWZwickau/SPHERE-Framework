<?php
namespace SPHERE\Application\Document\Generator\Service\Kamenz;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Common\Common;

/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 23.06.2017
 * Time: 10:05
 */
class KamenzReportService
{

    /**
     * @param array $Content
     *
     * @return array
     */
    public static function setKamenzReportContent(
        $Content
    )
    {

        $currentYearName = '';
        $pastYearName = '';

        $tblKamenzSchoolType = Type::useService()->getTypeByName('Mittelschule / Oberschule');

        // SchoolYears
        if (($tblCurrentYearList = Term::useService()->getYearByNow())) {
            $tblCurrentYear = reset($tblCurrentYearList);
            $currentYearName = $tblCurrentYear->getName();
        }

        if ($currentYearName
            && ($pos = strpos($currentYearName, '/'))
        ) {
            $year[0] = substr($currentYearName, 0, $pos);
            $year[1] = substr($currentYearName, $pos + 1);

            $pastYearName = (string) ($year[0] - 1) . '/' . (string) ($year[1] - 1);
//            $tblPastYearList = Term::useService()->getYearByName($pastYearName);
        }
        $Content['SchoolYear']['Past'] = $pastYearName;

        /**
         * E02  Schüler im Schuljahr 2016/2017 nach Geburtsjahren und Klassenstufen
         */
        if ($tblCurrentYearList) {
            $countArray = array();
            foreach ($tblCurrentYearList as $tblYear) {
                if (($tblDivisionList = Division::useService()->getDivisionAllByYear($tblYear))) {
                    foreach ($tblDivisionList as $tblDivision) {
                        if (($tblLevel = $tblDivision->getTblLevel())
                            && ($tblSchoolType = $tblLevel->getServiceTblType())
                            && $tblSchoolType->getId() == $tblKamenzSchoolType->getId()
                        ) {
                            if (($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))) {
                                foreach ($tblPersonList as $tblPerson) {
                                    if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))
                                        && (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates()))
                                        && ($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())
                                        && ($birthDay = $tblCommonBirthDates->getBirthday())
                                    ){

                                        if ($tblCommonGender->getName() == 'Männlich') {
                                            $gender = 'm';
                                        } elseif ($tblCommonGender->getName() == 'Weiblich') {
                                            $gender = 'w';
                                        } else {
                                            $gender = 'x';
                                        }

                                        $birthDayDate = new \DateTime($birthDay);
                                        if ($birthDayDate) {
                                            $birthYear = $birthDayDate->format('Y');
                                            if (isset($countArray[$birthYear][$tblLevel->getName()][$gender])) {
                                                $countArray[$birthYear][$tblLevel->getName()][$gender]++;
                                            } else {
                                                $countArray[$birthYear][$tblLevel->getName()][$gender] = 1;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            ksort ($countArray);
            $count = 0;
            foreach ($countArray as $year => $levelArray) {
                $Content['E02']['Y' . $count]['YearName'] = $year;
                foreach ($levelArray as $level => $genderArray) {
                    foreach ($genderArray as $gender => $value)
                    $Content['E02']['Y' . $count]['L' . $level][$gender] = $value;
                }

                $count++;
            }
        }

        return $Content;
    }
}