<?php

namespace SPHERE\Application\Document\Generator\Service\Kamenz;

use SPHERE\Application\Corporation\Group\Group;
use SPHERE\Application\Education\Certificate\Generate\Generate;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

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
    public static function setKamenzReportOsContent(
        $Content
    ) {

        $tblCurrentYearList = false;
        $tblPastYearList = false;

        $tblKamenzSchoolType = Type::useService()->getTypeByName('Mittelschule / Oberschule');

        self::setYears($Content, $tblCurrentYearList, $tblPastYearList);

        /**
         * B
         */
        self::setGraduate($tblPastYearList, $Content);

        if ($tblCurrentYearList) {
            $countArray = array();
            $countMigrantsArray = array();
            $countMigrantsNationalityArray = array();
            $countForeignSubjectArray = array();
            $countSecondForeignSubjectArray = array();
            $countReligionArray = array();
            $countOrientationArray = array();
            $countDivisionStudentArray = array();
            /** @var TblYear[] $tblCurrentYearList */
            foreach ($tblCurrentYearList as $tblYear) {
                if (($tblDivisionList = Division::useService()->getDivisionAllByYear($tblYear))) {
                    foreach ($tblDivisionList as $tblDivision) {
                        if (($tblLevel = $tblDivision->getTblLevel())
                            && ($tblSchoolType = $tblLevel->getServiceTblType())
                            && $tblSchoolType->getId() == $tblKamenzSchoolType->getId()
                        ) {
                            if (($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))) {
                                $countDivisionStudentArray[$tblDivision->getId()][$tblLevel->getName()] = count($tblPersonList);

                                foreach ($tblPersonList as $tblPerson) {

                                    $isInPreparationDivisionForMigrants = false;
                                    $tblStudent = $tblPerson->getStudent();

                                    if ($tblStudent
                                        && $tblStudent->isInPreparationDivisionForMigrants()
                                    ) {
                                        $isInPreparationDivisionForMigrants = true;
                                    }

                                    $hasMigrationBackground = false;
                                    if ($tblStudent
                                        && $tblStudent->getHasMigrationBackground()
                                    ) {
                                        $hasMigrationBackground = true;
                                    }

                                    $gender = false;
                                    $birthDay = false;
                                    self::countStudentLevels($tblPerson, $tblLevel, $gender, $hasMigrationBackground,
                                        $isInPreparationDivisionForMigrants, $birthDay, $countArray,
                                        $countMigrantsArray,
                                        $countMigrantsNationalityArray);

                                    if ($tblStudent) {
                                        self::countForeignLanguages($tblStudent, $tblLevel, $Content, $gender,
                                            $isInPreparationDivisionForMigrants, $countForeignSubjectArray,
                                            $countSecondForeignSubjectArray);

                                        $countReligionArray = self::countReligion($tblStudent, $tblLevel,
                                            $countReligionArray);

                                        if (preg_match('!(0?(7|8|9))!is', $tblLevel->getName())) {
                                            $countOrientationArray = self::countOrientation($tblStudent, $tblLevel,
                                                $gender, $countOrientationArray);
                                        }
                                    } else {
                                        if (isset($countReligionArray['ZZ_Keine_Teilnahme'][$tblLevel->getName()])) {
                                            $countReligionArray['ZZ_Keine_Teilnahme'][$tblLevel->getName()]++;
                                        } else {
                                            $countReligionArray['ZZ_Keine_Teilnahme'][$tblLevel->getName()] = 1;
                                        }
                                    }

                                    self::setRepeaters($tblPerson, $tblLevel, $tblDivision, $Content, $gender);

                                    if ($tblStudent) {
                                        self::setStudentFocus($tblStudent, $tblLevel, $Content, $gender,
                                            $hasMigrationBackground,
                                            $isInPreparationDivisionForMigrants);
                                    }
                                }
                            } else {
                                $countDivisionStudentArray[$tblDivision->getId()][$tblLevel->getName()] = 0;
                            }
                        }
                    }
                }
            }

            self::setStudentLevels($Content, $countArray, $countMigrantsArray, $countMigrantsNationalityArray);
            self::setForeignLanguages($Content, $countForeignSubjectArray, $countSecondForeignSubjectArray);
            self::setReligion($Content, $countReligionArray);
            self::setOrientation($Content, $countOrientationArray);
            self::setDivisionFrequency($Content, $countDivisionStudentArray);
        }

        return $Content;
    }

    /**
     * @param array $Content
     *
     * @return array
     */
    public static function setKamenzReportGsContent(
        $Content
    ) {

        $tblCurrentYearList = false;
        $tblPastYearList = false;
        $year = false;

        $tblKamenzSchoolType = Type::useService()->getTypeByName('Grundschule');

        self::setYears($Content, $tblCurrentYearList, $tblPastYearList, $year);

        /**
         * B
         */
        self::setGraduate($tblPastYearList, $Content);

        if ($tblCurrentYearList) {
            $countArray = array();
            $countMigrantsArray = array();
            $countMigrantsNationalityArray = array();
            $countForeignSubjectArray = array();
            $countSecondForeignSubjectArray = array();
            $countReligionArray = array();
            $countDivisionStudentArray = array();
            /** @var TblYear[] $tblCurrentYearList */
            foreach ($tblCurrentYearList as $tblYear) {
                if (($tblDivisionList = Division::useService()->getDivisionAllByYear($tblYear))) {
                    foreach ($tblDivisionList as $tblDivision) {
                        if (($tblLevel = $tblDivision->getTblLevel())
                            && ($tblSchoolType = $tblLevel->getServiceTblType())
                            && $tblSchoolType->getId() == $tblKamenzSchoolType->getId()
                        ) {
                            if (($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))) {

                                if (isset($Content['E01']['Division']['L' . $tblLevel->getName()])) {
                                    $Content['E01']['Division']['L' . $tblLevel->getName()]++;
                                } else {
                                    $Content['E01']['Division']['L' . $tblLevel->getName()] = 1;
                                }
                                if (isset($Content['E01']['Division']['TotalCount'])) {
                                    $Content['E01']['Division']['TotalCount']++;
                                } else {
                                    $Content['E01']['Division']['TotalCount'] = 1;
                                }

                                $countDivisionStudentArray[$tblDivision->getId()][$tblLevel->getName()] = count($tblPersonList);

                                foreach ($tblPersonList as $tblPerson) {

                                    $isInPreparationDivisionForMigrants = false;
                                    $tblStudent = $tblPerson->getStudent();

                                    if ($tblStudent
                                        && $tblStudent->isInPreparationDivisionForMigrants()
                                    ) {
                                        $isInPreparationDivisionForMigrants = true;
                                    }

                                    $hasMigrationBackground = false;
                                    if ($tblStudent
                                        && $tblStudent->getHasMigrationBackground()
                                    ) {
                                        $hasMigrationBackground = true;
                                    }

                                    $gender = false;
                                    $birthDay = false;
                                    self::countStudentLevels($tblPerson, $tblLevel, $gender, $hasMigrationBackground,
                                        $isInPreparationDivisionForMigrants, $birthDay, $countArray,
                                        $countMigrantsArray,
                                        $countMigrantsNationalityArray);

                                    self::setDivisionStudents($Content, $tblPerson, $tblLevel, $tblDivision, $gender,
                                        $isInPreparationDivisionForMigrants);

                                    if ($tblStudent) {
                                        self::countForeignLanguages($tblStudent, $tblLevel, $Content, $gender,
                                            $isInPreparationDivisionForMigrants, $countForeignSubjectArray,
                                            $countSecondForeignSubjectArray);

                                        $countReligionArray = self::countReligion($tblStudent, $tblLevel,
                                            $countReligionArray);

                                    } else {
                                        if (isset($countReligionArray['ZZ_Keine_Teilnahme'][$tblLevel->getName()])) {
                                            $countReligionArray['ZZ_Keine_Teilnahme'][$tblLevel->getName()]++;
                                        } else {
                                            $countReligionArray['ZZ_Keine_Teilnahme'][$tblLevel->getName()] = 1;
                                        }
                                    }

                                    // Schulanfänger
                                    self::setNewSchoolStarter(
                                        $Content,
                                        $tblPerson,
                                        $tblStudent,
                                        $tblLevel,
                                        $tblDivision,
                                        $gender,
                                        $birthDay,
                                        $year
                                    );
                                }
                            } else {
                                $countDivisionStudentArray[$tblDivision->getId()][$tblLevel->getName()] = 0;
                            }
                        }
                    }
                }
            }

            self::setStudentLevels($Content, $countArray, $countMigrantsArray, $countMigrantsNationalityArray);
            self::setForeignLanguages($Content, $countForeignSubjectArray, $countSecondForeignSubjectArray);
            self::setReligion($Content, $countReligionArray);
            self::setDivisionFrequency($Content, $countDivisionStudentArray);
        }

        return $Content;
    }

    /**
     * @param array $Content
     *
     * @return array
     */
    public static function setKamenzReportGymContent(
        $Content
    ) {

//        $tblCurrentYearList = false;
//        $tblPastYearList = false;

//        $tblKamenzSchoolType = Type::useService()->getTypeByName('Gymnasium');
//
//        self::setYears($Content, $tblCurrentYearList, $tblPastYearList);

        return $Content;
    }

    /**
     * @param $Content
     * @param $tblCurrentYearList
     * @param $tblPastYearList
     * @param bool $currentYear
     */
    private static function setYears(
        &$Content,
        &$tblCurrentYearList,
        &$tblPastYearList,
        &$currentYear = false
    ) {
        // SchoolYears
        if (($tblCurrentYearList = Term::useService()->getYearByNow())) {
            $tblCurrentYear = reset($tblCurrentYearList);
            $currentYearName = $tblCurrentYear->getName();

            if ($currentYearName
                && ($pos = strpos($currentYearName, '/'))
            ) {
                $year[0] = substr($currentYearName, 0, $pos);
                $year[1] = substr($currentYearName, $pos + 1);

                $currentYear = $year[0];

                $pastYearName = (string)($year[0] - 1) . '/' . (string)($year[1] - 1);
                $tblPastYearList = Term::useService()->getYearByName($pastYearName);
                $Content['SchoolYear']['Current'] = $currentYearName;
                $Content['SchoolYear']['Past'] = $pastYearName;
            }
        }
    }

    /**
     * F01. Integrierte Schüler mit sonderpädagogischem Förderbedarf im Schuljahr 2016/17 nach
     * Förderschwerpunkten und Klassenstufen
     *
     * @param TblStudent $tblStudent
     * @param TblLevel $tblLevel
     * @param $Content
     * @param $gender
     * @param $hasMigrationBackground
     * @param $isInPreparationDivisionForMigrants
     */
    private static function setStudentFocus(
        TblStudent $tblStudent,
        TblLevel $tblLevel,
        &$Content,
        $gender,
        $hasMigrationBackground,
        $isInPreparationDivisionForMigrants
    ) {
        if (($tblStudentFocus = Student::useService()->getStudentFocusPrimary($tblStudent))
            && ($tblStudentFocusType = $tblStudentFocus->getTblStudentFocusType())
        ) {

            $name = preg_replace('/[^a-zA-Z]/', '', $tblStudentFocusType->getName());

            /**
             * Schüler
             */
            if (isset($Content['F01'][$name]['Student']['L' . $tblLevel->getName()][$gender])) {
                $Content['F01'][$name]['Student']['L' . $tblLevel->getName()][$gender]++;
            } else {
                $Content['F01'][$name]['Student']['L' . $tblLevel->getName()][$gender] = 1;
            }
            if ($isInPreparationDivisionForMigrants) {
                if (isset($Content['F01'][$name]['Student']['IsInPreparationDivisionForMigrants'][$gender])) {
                    $Content['F01'][$name]['Student']['IsInPreparationDivisionForMigrants'][$gender]++;
                } else {
                    $Content['F01'][$name]['Student']['IsInPreparationDivisionForMigrants'][$gender] = 1;
                }
            }
            if (isset($Content['F01'][$name]['Student']['TotalCount'][$gender])) {
                $Content['F01'][$name]['Student']['TotalCount'][$gender]++;
            } else {
                $Content['F01'][$name]['Student']['TotalCount'][$gender] = 1;
            }
            if (isset($Content['F01']['TotalCount']['Student']['TotalCount'][$gender])) {
                $Content['F01']['TotalCount']['Student']['TotalCount'][$gender]++;
            } else {
                $Content['F01']['TotalCount']['Student']['TotalCount'][$gender] = 1;
            }

            /**
             * Schüler mit Migrationshintergrund
             */
            if ($hasMigrationBackground) {
                if (isset($Content['F01'][$name]['HasMigrationBackground']['L' . $tblLevel->getName()][$gender])) {
                    $Content['F01'][$name]['HasMigrationBackground']['L' . $tblLevel->getName()][$gender]++;
                } else {
                    $Content['F01'][$name]['HasMigrationBackground']['L' . $tblLevel->getName()][$gender] = 1;
                }
                if ($isInPreparationDivisionForMigrants) {
                    if (isset($Content['F01'][$name]['HasMigrationBackground']['IsInPreparationDivisionForMigrants'][$gender])) {
                        $Content['F01'][$name]['HasMigrationBackground']['IsInPreparationDivisionForMigrants'][$gender]++;
                    } else {
                        $Content['F01'][$name]['HasMigrationBackground']['IsInPreparationDivisionForMigrants'][$gender] = 1;
                    }
                }
                if (isset($Content['F01'][$name]['HasMigrationBackground']['TotalCount'][$gender])) {
                    $Content['F01'][$name]['HasMigrationBackground']['TotalCount'][$gender]++;
                } else {
                    $Content['F01'][$name]['HasMigrationBackground']['TotalCount'][$gender] = 1;
                }
                if (isset($Content['F01']['TotalCount']['HasMigrationBackground']['TotalCount'][$gender])) {
                    $Content['F01']['TotalCount']['HasMigrationBackground']['TotalCount'][$gender]++;
                } else {
                    $Content['F01']['TotalCount']['HasMigrationBackground']['TotalCount'][$gender] = 1;
                }
            }

            /**
             * Schüler mit gutachterl. best. Autismus
             */
            if (($tblStudentDisorderTypeAutism = Student::useService()->getStudentDisorderTypeByName('Autismus'))
                && ($tblStudentDisorder = Student::useService()->getStudentDisorder($tblStudent,
                    $tblStudentDisorderTypeAutism))
            ) {
                if (isset($Content['F01'][$name]['Autism']['L' . $tblLevel->getName()][$gender])) {
                    $Content['F01'][$name]['Autism']['L' . $tblLevel->getName()][$gender]++;
                } else {
                    $Content['F01'][$name]['Autism']['L' . $tblLevel->getName()][$gender] = 1;
                }
                if ($isInPreparationDivisionForMigrants) {
                    if (isset($Content['F01'][$name]['Autism']['IsInPreparationDivisionForMigrants'][$gender])) {
                        $Content['F01'][$name]['Autism']['IsInPreparationDivisionForMigrants'][$gender]++;
                    } else {
                        $Content['F01'][$name]['Autism']['IsInPreparationDivisionForMigrants'][$gender] = 1;
                    }
                }
                if (isset($Content['F01'][$name]['Autism']['TotalCount'][$gender])) {
                    $Content['F01'][$name]['Autism']['TotalCount'][$gender]++;
                } else {
                    $Content['F01'][$name]['Autism']['TotalCount'][$gender] = 1;
                }
                if (isset($Content['F01']['TotalCount']['Autism']['TotalCount'][$gender])) {
                    $Content['F01']['TotalCount']['Autism']['TotalCount'][$gender]++;
                } else {
                    $Content['F01']['TotalCount']['Autism']['TotalCount'][$gender] = 1;
                }
            }
        }
    }

    /**
     * @param TblStudent $tblStudent
     * @param TblLevel $tblLevel
     * @param $Content
     * @param $gender
     * @param $isInPreparationDivisionForMigrants
     * @param $countForeignSubjectArray
     * @param $countSecondForeignSubjectArray
     */
    private static function countForeignLanguages(
        TblStudent $tblStudent,
        TblLevel $tblLevel,
        &$Content,
        $gender,
        $isInPreparationDivisionForMigrants,
        &$countForeignSubjectArray,
        &$countSecondForeignSubjectArray
    ) {
        /**
         * E04 Schüler mit der ersten Fremdsprache im Schuljahr nach Klassenstufen
         */
        if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))) {
            if ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                $tblStudent, $tblStudentSubjectType
            )
            ) {
                foreach ($tblStudentSubjectList as $tblStudentSubject) {
                    if (($tblSubject = $tblStudentSubject->getServiceTblSubject())
                        && ($tblStudentSubjectRanking = $tblStudentSubject->getTblStudentSubjectRanking())
                    ) {
                        if ($tblStudentSubjectRanking->getIdentifier() == 1) {
                            if (isset($countForeignSubjectArray[$tblSubject->getAcronym()][$tblLevel->getName()])) {
                                $countForeignSubjectArray[$tblSubject->getAcronym()][$tblLevel->getName()]++;
                            } else {
                                $countForeignSubjectArray[$tblSubject->getAcronym()][$tblLevel->getName()] = 1;
                            }
                        } elseif ($tblStudentSubjectRanking->getIdentifier() == 2) {
                            /**
                             * E11. Schüler in der zweiten FREMDSPRACHE - abschlussorientiert im Schuljahr nach Klassenstufen
                             */
                            if ($gender) {
                                if (isset($countSecondForeignSubjectArray[$tblSubject->getAcronym()][$tblLevel->getName()][$gender])) {
                                    $countSecondForeignSubjectArray[$tblSubject->getAcronym()][$tblLevel->getName()][$gender]++;
                                } else {
                                    $countSecondForeignSubjectArray[$tblSubject->getAcronym()][$tblLevel->getName()][$gender] = 1;
                                }
                            }
                        }
                    }
                }

                $countForeignSubjectsByStudent = count($tblStudentSubjectList);
            } else {
                $countForeignSubjectsByStudent = 0;
            }

            /**
             * E04.1 Schüler im Schuljahr nach der Anzahl der derzeit erlernten Fremdsprachen und Klassenstufen
             */
            if ($countForeignSubjectsByStudent > 4) {
                $countForeignSubjectsByStudent = 4;
            }
            if (isset($Content['E04_1']['F' . $countForeignSubjectsByStudent]['L' . $tblLevel->getName()])) {
                $Content['E04_1']['F' . $countForeignSubjectsByStudent]['L' . $tblLevel->getName()]++;
            } else {
                $Content['E04_1']['F' . $countForeignSubjectsByStudent]['L' . $tblLevel->getName()] = 1;
            }
            if (isset($Content['E04_1']['F' . $countForeignSubjectsByStudent]['TotalCount'])) {
                $Content['E04_1']['F' . $countForeignSubjectsByStudent]['TotalCount']++;
            } else {
                $Content['E04_1']['F' . $countForeignSubjectsByStudent]['TotalCount'] = 1;
            }
            if (isset($Content['E04_1']['TotalCount']['L' . $tblLevel->getName()])) {
                $Content['E04_1']['TotalCount']['L' . $tblLevel->getName()]++;
            } else {
                $Content['E04_1']['TotalCount']['L' . $tblLevel->getName()] = 1;
            }

            if ($isInPreparationDivisionForMigrants) {
                if (isset($Content['E04_1']['F' . $countForeignSubjectsByStudent]['Migration'])) {
                    $Content['E04_1']['F' . $countForeignSubjectsByStudent]['Migration']++;
                } else {
                    $Content['E04_1']['F' . $countForeignSubjectsByStudent]['Migration'] = 1;
                }

                if (isset($Content['E04_1']['TotalCount']['Migration'])) {
                    $Content['E04_1']['TotalCount']['Migration']++;
                } else {
                    $Content['E04_1']['TotalCount']['Migration'] = 1;
                }
            }
        }
    }

    /**
     * @param $Content
     * @param $countForeignSubjectArray
     * @param $countSecondForeignSubjectArray
     */
    private static function setForeignLanguages(
        &$Content,
        $countForeignSubjectArray,
        $countSecondForeignSubjectArray
    ) {
        /**
         * E04 Schüler mit der ersten Fremdsprache im Schuljahr nach Klassenstufen
         */
        ksort($countForeignSubjectArray);
        $count = 0;
        foreach ($countForeignSubjectArray as $acronym => $levelArray) {
            $Content['E04']['S' . $count]['TotalCount'] = 0;
            $Content['E04']['S' . $count]['SubjectName'] = ($tblSubject = Subject::useService()->getSubjectByAcronym($acronym))
                ? $tblSubject->getName() : '';
            foreach ($levelArray as $level => $value) {
                $Content['E04']['S' . $count]['L' . $level] = $value;
                $Content['E04']['S' . $count]['TotalCount'] += $value;
            }

            $count++;
        }

        /**
         * E11 Schüler in der zweiten FREMDSPRACHE - abschlussorientiert im Schuljahr nach Klassenstufen
         */
        ksort($countSecondForeignSubjectArray);
        $count = 0;
        foreach ($countSecondForeignSubjectArray as $acronym => $levelArray) {
            $Content['E11']['S' . $count]['SubjectName'] = ($tblSubject = Subject::useService()->getSubjectByAcronym($acronym))
                ? $tblSubject->getName() : '';
            foreach ($levelArray as $level => $genderArray) {
                foreach ($genderArray as $gender => $value) {
                    $Content['E11']['S' . $count]['L' . $level][$gender] = $value;

                    if (isset($Content['E11']['TotalCount']['L' . $level][$gender])) {
                        $Content['E11']['TotalCount']['L' . $level][$gender] += $value;
                    } else {
                        $Content['E11']['TotalCount']['L' . $level][$gender] = $value;
                    }

                    if (isset($Content['E11']['S' . $count]['TotalCount'][$gender])) {
                        $Content['E11']['S' . $count]['TotalCount'][$gender] += $value;
                    } else {
                        $Content['E11']['S' . $count]['TotalCount'][$gender] = $value;
                    }
                }
            }

            $count++;
        }
    }

    /**
     * E05 Schüler im Ethik- bzw. Religionsunterricht im Schuljahr nach Klassenstufen
     *
     * @param TblStudent $tblStudent
     * @param TblLevel $tblLevel
     * @param $countReligionArray
     *
     * @return array
     */
    private static function countReligion(
        TblStudent $tblStudent,
        TblLevel $tblLevel,
        $countReligionArray
    ) {

        if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('RELIGION'))
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                $tblStudent, $tblStudentSubjectType
            ))
        ) {

            /** @var TblStudentSubject $tblStudentSubject */
            if (($tblStudentSubject = reset($tblStudentSubjectList))
                && ($tblSubject = $tblStudentSubject->getServiceTblSubject())
            ) {
                if (isset($countReligionArray[$tblSubject->getAcronym()][$tblLevel->getName()])) {
                    $countReligionArray[$tblSubject->getAcronym()][$tblLevel->getName()]++;
                } else {
                    $countReligionArray[$tblSubject->getAcronym()][$tblLevel->getName()] = 1;
                }
            }

            return $countReligionArray;
        }

        if (isset($countReligionArray['ZZ_Keine_Teilnahme'][$tblLevel->getName()])) {
            $countReligionArray['ZZ_Keine_Teilnahme'][$tblLevel->getName()]++;
        } else {
            $countReligionArray['ZZ_Keine_Teilnahme'][$tblLevel->getName()] = 1;
        }

        return $countReligionArray;
    }

    /**
     * @param $Content
     * @param $countReligionArray
     */
    private static function setReligion(
        &$Content,
        $countReligionArray
    ) {
        /**
         * E05 Schüler im Ethik- bzw. Religionsunterricht im Schuljahr nach Klassenstufen
         */
        ksort($countReligionArray);
        $count = 0;
        foreach ($countReligionArray as $acronym => $levelArray) {
            $Content['E05']['S' . $count]['TotalCount'] = 0;
            $Content['E05']['S' . $count]['SubjectName'] = ($tblSubject = Subject::useService()->getSubjectByAcronym($acronym))
                ? $tblSubject->getName() : 'Keine Teilnahme';
            foreach ($levelArray as $level => $value) {
                $Content['E05']['S' . $count]['L' . $level] = $value;
                $Content['E05']['S' . $count]['TotalCount'] += $value;
                if (isset($Content['E05']['TotalCount']['L' . $level])) {
                    $Content['E05']['TotalCount']['L' . $level] += $value;
                } else {
                    $Content['E05']['TotalCount']['L' . $level] = $value;
                }
            }

            $count++;
        }
    }

    /**
     * E08. Wiederholer im Schuljahr 2016/17 nach Klassenstufen
     *
     * @param TblPerson $tblPerson
     * @param TblLevel $tblLevel
     * @param TblDivision $tblDivision
     * @param $Content
     * @param $gender
     */
    private static function setRepeaters(
        TblPerson $tblPerson,
        TblLevel $tblLevel,
        TblDivision $tblDivision,
        &$Content,
        $gender
    ) {

        if (($tblDivisionStudentAllByPerson = Division::useService()->getDivisionStudentAllByPerson($tblPerson))) {
            /**@var TblDivisionStudent $tblDivisionStudent * */
            foreach ($tblDivisionStudentAllByPerson as $tblDivisionStudent) {
                if (($tblDivisionTemp = $tblDivisionStudent->getTblDivision())
                    && $tblDivisionTemp->getId() != $tblDivision->getId()
                    && ($tblTempLevel = $tblDivisionTemp->getTblLevel())
                    && $tblLevel->getId() == $tblTempLevel->getId()
                ) {
                    if ($gender) {
                        if (isset($Content['E08']['WithoutCourse']['L' . $tblLevel->getName()][$gender])) {
                            $Content['E08']['WithoutCourse']['L' . $tblLevel->getName()][$gender]++;
                        } else {
                            $Content['E08']['WithoutCourse']['L' . $tblLevel->getName()][$gender] = 1;
                        }

                        if (isset($Content['E08']['WithoutCourse']['TotalCount'][$gender])) {
                            $Content['E08']['WithoutCourse']['TotalCount'][$gender]++;
                        } else {
                            $Content['E08']['WithoutCourse']['TotalCount'][$gender] = 1;
                        }
                    }

                    break;
                }
            }
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblLevel $tblLevel
     * @param TblDivision $tblDivision
     *
     * @return bool
     */
    private static function hasRepeaters(
        TblPerson $tblPerson,
        TblLevel $tblLevel,
        TblDivision $tblDivision
    ) {

        if (($tblDivisionStudentAllByPerson = Division::useService()->getDivisionStudentAllByPerson($tblPerson))) {
            /**@var TblDivisionStudent $tblDivisionStudent * */
            foreach ($tblDivisionStudentAllByPerson as $tblDivisionStudent) {
                if (($tblDivisionTemp = $tblDivisionStudent->getTblDivision())
                    && $tblDivisionTemp->getId() != $tblDivision->getId()
                    && ($tblTempLevel = $tblDivisionTemp->getTblLevel())
                    && $tblLevel->getId() == $tblTempLevel->getId()
                ) {

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * E12 Schüler im NEIGUNGSKURSBEREICH im Schuljahr nach Klassenstufen
     *
     * @param TblStudent $tblStudent
     * @param TblLevel $tblLevel
     * @param $gender
     * @param $countOrientationArray
     *
     * @return array
     */
    private static function countOrientation(
        TblStudent $tblStudent,
        TblLevel $tblLevel,
        $gender,
        $countOrientationArray
    ) {

        if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'))
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                $tblStudent, $tblStudentSubjectType
            ))
        ) {
            /** @var TblStudentSubject $tblStudentSubject */
            if (($tblStudentSubject = reset($tblStudentSubjectList))
                && ($tblSubject = $tblStudentSubject->getServiceTblSubject())
            ) {

                if ($gender) {
                    if (isset($countOrientationArray[$tblSubject->getAcronym()][$tblLevel->getName()][$gender])) {
                        $countOrientationArray[$tblSubject->getAcronym()][$tblLevel->getName()][$gender]++;
                    } else {
                        $countOrientationArray[$tblSubject->getAcronym()][$tblLevel->getName()][$gender] = 1;
                    }
                }
            }
        }

        return $countOrientationArray;
    }

    /**
     * @param $Content
     * @param $countOrientationArray
     */
    private static function setOrientation(
        &$Content,
        $countOrientationArray
    ) {
        /**
         * E12 Schüler im NEIGUNGSKURSBEREICH im Schuljahr nach Klassenstufen
         */
        ksort($countOrientationArray);
        $count = 0;
        foreach ($countOrientationArray as $acronym => $levelArray) {
            $name = '';
            if (($tblSubject = Subject::useService()->getSubjectByAcronym($acronym))) {
                if (($startPos = strpos($tblSubject->getName(), '(')) !== false
                    && ($endPos = strpos($tblSubject->getName(), ')')) !== false
                ) {
                    $name = substr($tblSubject->getName(), $startPos + 1, $endPos - ($startPos + 1));
                }
            }
            $Content['E12']['S' . $count]['SubjectName'] = $name;
            foreach ($levelArray as $level => $genderArray) {
                foreach ($genderArray as $gender => $value) {

                    $Content['E12']['S' . $count]['L' . $level][$gender] = $value;

                    if (isset($Content['E12']['TotalCount']['L' . $level][$gender])) {
                        $Content['E12']['TotalCount']['L' . $level][$gender] += $value;
                    } else {
                        $Content['E12']['TotalCount']['L' . $level][$gender] = $value;
                    }

                    if (isset($Content['E12']['S' . $count]['TotalCount'][$gender])) {
                        $Content['E12']['S' . $count]['TotalCount'][$gender] += $value;
                    } else {
                        $Content['E12']['S' . $count]['TotalCount'][$gender] = $value;
                    }
                }
            }

            $count++;
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblLevel $tblLevel
     * @param $gender
     * @param $hasMigrationBackground
     * @param $isInPreparationDivisionForMigrants
     * @param $birthDay
     * @param $countArray
     * @param $countMigrantsArray
     * @param $countMigrantsNationalityArray
     */
    private static function countStudentLevels(
        TblPerson $tblPerson,
        TblLevel $tblLevel,
        &$gender,
        $hasMigrationBackground,
        $isInPreparationDivisionForMigrants,
        &$birthDay,
        &$countArray,
        &$countMigrantsArray,
        &$countMigrantsNationalityArray
    ) {
        /**
         * E02  Schüler im Schuljahr 2016/2017 nach Geburtsjahren und Klassenstufen
         */
        if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
            if (($tblCommonInformation = $tblCommon->getTblCommonInformation())) {
                $nationality = $tblCommonInformation->getNationality();
            } else {
                $nationality = false;
            }

            if (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())
                && ($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())
            ) {

                if ($tblCommonGender->getName() == 'Männlich') {
                    $gender = 'm';
                } elseif ($tblCommonGender->getName() == 'Weiblich') {
                    $gender = 'w';
                } else {
                    $gender = 'x';
                }

                if (($birthDay = $tblCommonBirthDates->getBirthday())) {
                    $birthDayDate = new \DateTime($birthDay);
                    if ($birthDayDate) {
                        $birthYear = $birthDayDate->format('Y');

                        if (isset($countArray[$birthYear][$tblLevel->getName()][$gender])) {
                            $countArray[$birthYear][$tblLevel->getName()][$gender]++;
                        } else {
                            $countArray[$birthYear][$tblLevel->getName()][$gender] = 1;
                        }

                        if ($isInPreparationDivisionForMigrants) {
                            if (isset($countArray[$birthYear]['Migration'][$gender])) {
                                $countArray[$birthYear]['Migration'][$gender]++;
                            } else {
                                $countArray[$birthYear]['Migration'][$gender] = 1;
                            }
                        }

                        /**
                         * E02.1 Darunter Schüler mit Migrationshintergrund im Schuljahr 2016/17 nach Geburtsjahren und Klassenstufen
                         */
                        if ($hasMigrationBackground) {
                            if (isset($countMigrantsArray[$birthYear][$tblLevel->getName()][$gender])) {
                                $countMigrantsArray[$birthYear][$tblLevel->getName()][$gender]++;
                            } else {
                                $countMigrantsArray[$birthYear][$tblLevel->getName()][$gender] = 1;
                            }

                            /**
                             * E03. Schüler mit Migrationshintergrund im Schuljahr 2016/17 nach dem Land der Staatsangehörigkeit und Klassenstufen
                             */
                            if ($nationality) {
                                if (isset($countMigrantsNationalityArray[$nationality][$tblLevel->getName()][$gender])) {
                                    $countMigrantsNationalityArray[$nationality][$tblLevel->getName()][$gender]++;
                                } else {
                                    $countMigrantsNationalityArray[$nationality][$tblLevel->getName()][$gender] = 1;
                                }
                            }

                            if ($isInPreparationDivisionForMigrants) {
                                if (isset($countMigrantsArray[$birthYear]['Migration'][$gender])) {
                                    $countMigrantsArray[$birthYear]['Migration'][$gender]++;
                                } else {
                                    $countMigrantsArray[$birthYear]['Migration'][$gender] = 1;
                                }

                                if ($nationality) {
                                    if (isset($countMigrantsNationalityArray[$nationality]['Migration'][$gender])) {
                                        $countMigrantsNationalityArray[$nationality]['Migration'][$gender]++;
                                    } else {
                                        $countMigrantsNationalityArray[$nationality]['Migration'][$gender] = 1;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $Content
     * @param $countArray
     * @param $countMigrantsArray
     * @param $countMigrantsNationalityArray
     */
    private static function setStudentLevels(
        &$Content,
        $countArray,
        $countMigrantsArray,
        $countMigrantsNationalityArray
    ) {
        /**
         * E02  Schüler im Schuljahr 2016/2017 nach Geburtsjahren und Klassenstufen
         */
        ksort($countArray);
        $count = 0;
        $Content['E02']['TotalCount']['m'] = 0;
        $Content['E02']['TotalCount']['w'] = 0;
        foreach ($countArray as $year => $levelArray) {
            $Content['E02']['Y' . $count]['YearName'] = $year;
            $Content['E02']['Y' . $count]['m'] = 0;
            $Content['E02']['Y' . $count]['w'] = 0;
            $Content['E02']['Y' . $count]['x'] = 0;
            foreach ($levelArray as $level => $genderArray) {
                foreach ($genderArray as $gender => $value) {
                    $Content['E02']['Y' . $count]['L' . $level][$gender] = $value;

                    if (isset($Content['E02']['TotalCount']['L' . $level][$gender])) {
                        $Content['E02']['TotalCount']['L' . $level][$gender] += $value;
                    } else {
                        $Content['E02']['TotalCount']['L' . $level][$gender] = $value;
                    }

                    $Content['E02']['Y' . $count][$gender] += $value;
                    $Content['E02']['TotalCount'][$gender] += $value;
                }
            }

            $count++;
        }

        /**
         * E02.1 Darunter Schüler mit Migrationshintergrund im Schuljahr 2016/17 nach Geburtsjahren und Klassenstufen
         */
        ksort($countMigrantsArray);
        $count = 0;
        $Content['E02_1']['TotalCount']['m'] = 0;
        $Content['E02_1']['TotalCount']['w'] = 0;
        foreach ($countMigrantsArray as $year => $levelArray) {
            $Content['E02_1']['Y' . $count]['YearName'] = $year;
            $Content['E02_1']['Y' . $count]['m'] = 0;
            $Content['E02_1']['Y' . $count]['w'] = 0;
            $Content['E02_1']['Y' . $count]['x'] = 0;
            foreach ($levelArray as $level => $genderArray) {
                foreach ($genderArray as $gender => $value) {
                    $Content['E02_1']['Y' . $count]['L' . $level][$gender] = $value;

                    if (isset($Content['E02_1']['TotalCount']['L' . $level][$gender])) {
                        $Content['E02_1']['TotalCount']['L' . $level][$gender] += $value;
                    } else {
                        $Content['E02_1']['TotalCount']['L' . $level][$gender] = $value;
                    }

                    $Content['E02_1']['Y' . $count][$gender] += $value;
                    $Content['E02_1']['TotalCount'][$gender] += $value;
                }
            }

            $count++;
        }

        /**
         * E03. Schüler mit Migrationshintergrund im Schuljahr 2016/17 nach dem Land der Staatsangehörigkeit und Klassenstufen
         */
        ksort($countMigrantsNationalityArray);
        $count = 0;
        $Content['E03']['TotalCount']['m'] = 0;
        $Content['E03']['TotalCount']['w'] = 0;
        foreach ($countMigrantsNationalityArray as $nation => $levelArray) {
            $Content['E03']['N' . $count]['NationalityName'] = $nation;
            $Content['E03']['N' . $count]['m'] = 0;
            $Content['E03']['N' . $count]['w'] = 0;
            $Content['E03']['N' . $count]['x'] = 0;
            foreach ($levelArray as $level => $genderArray) {
                foreach ($genderArray as $gender => $value) {
                    $Content['E03']['N' . $count]['L' . $level][$gender] = $value;

                    if (isset($Content['E03']['TotalCount']['L' . $level][$gender])) {
                        $Content['E03']['TotalCount']['L' . $level][$gender] += $value;
                    } else {
                        $Content['E03']['TotalCount']['L' . $level][$gender] = $value;
                    }

                    $Content['E03']['N' . $count][$gender] += $value;
                    $Content['E03']['TotalCount'][$gender] += $value;
                }
            }

            $count++;
        }
    }

    /**
     * @param $Content
     * @param $countDivisionStudentArray
     */
    private static function setDivisionFrequency(
        &$Content,
        $countDivisionStudentArray
    ) {
        /**
         * G01. Klassenfrequenz im Schuljahr zum Stichtag 02. September
         */
        $levelCount = array();
        foreach ($countDivisionStudentArray as $levelArray) {
            foreach ($levelArray as $level => $value) {
                if (isset($levelCount[$level])) {
                    $levelCount[$level]++;
                } else {
                    $levelCount[$level] = 1;
                }
                $Content['G01']['D' . $levelCount[$level]]['L' . $level] = $value;

                if (isset($Content['G01']['L' . $level]['TotalCount'])) {
                    $Content['G01']['L' . $level]['TotalCount'] += $value;
                } else {
                    $Content['G01']['L' . $level]['TotalCount'] = $value;
                }
            }
        }
    }

    /**
     * @param $Content
     * @param TblPerson $tblPerson
     * @param TblStudent $tblStudent
     * @param TblLevel $tblLevel
     * @param TblDivision $tblDivision
     * @param $gender
     * @param $birthDay
     * @param $year
     */
    private static function setNewSchoolStarter(
        &$Content,
        TblPerson $tblPerson,
        TblStudent $tblStudent,
        TblLevel $tblLevel,
        TblDivision $tblDivision,
        $gender,
        $birthDay,
        $year
    ) {
        if ($tblLevel->getName() == '1' || $tblLevel->getName() == '01') {
            if (!self::hasRepeaters($tblPerson, $tblLevel, $tblDivision)) {
                if (isset($Content['D01']['NewSchoolStarter'][$gender])) {
                    $Content['D01']['NewSchoolStarter'][$gender]++;
                } else {
                    $Content['D01']['NewSchoolStarter'][$gender] = 1;
                }

                if ($tblStudent) {
                    if (($tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE'))
                        && ($tblStudentTransfer = Student::useService()->getStudentTransferByType(
                            $tblStudent, $tblStudentTransferType
                        ))
                        && ($tblArriveCompany = $tblStudentTransfer->getServiceTblCompany())
                        && ($tblGroup = Group::useService()->getGroupByMetaTable('NURSERY'))
                        && Group::useService()->existsGroupCompany($tblGroup,
                            $tblArriveCompany)
                    ) {
                        if (isset($Content['D01']['Nursery'][$gender])) {
                            $Content['D01']['Nursery'][$gender]++;
                        } else {
                            $Content['D01']['Nursery'][$gender] = 1;
                        }
                    }

                    if (($tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ENROLLMENT'))
                        && ($tblStudentTransfer = Student::useService()->getStudentTransferByType(
                            $tblStudent, $tblStudentTransferType
                        ))
                        && ($tblSchoolEnrollmentType = $tblStudentTransfer->getTblStudentSchoolEnrollmentType())
                    ) {
                        if ($tblSchoolEnrollmentType->getIdentifier() == 'PREMATURE') {
                            if (isset($Content['D01']['Premature'][$gender])) {
                                $Content['D01']['Premature'][$gender]++;
                            } else {
                                $Content['D01']['Premature'][$gender] = 1;
                            }
                        } elseif ($tblSchoolEnrollmentType->getIdentifier() == 'REGULAR') {
                            if (isset($Content['D01']['Regular']['Total'][$gender])) {
                                $Content['D01']['Regular']['Total'][$gender]++;
                            } else {
                                $Content['D01']['Regular']['Total'][$gender] = 1;
                            }

                            $date = false;
                            if ($tblStudent->getSchoolAttendanceStartDate()) {
                                $date = new \DateTime($tblStudent->getSchoolAttendanceStartDate());
                            } elseif ($birthDay) {
                                $date = new \DateTime($birthDay);
                                $date->add(new \DateInterval('P6Y'));
                            }

                            if ($date) {
                                if ($year <= $date->format('Y') && $date->format('m') > 6) {
                                    if (isset($Content['D01']['Regular']['Second'][$gender])) {
                                        $Content['D01']['Regular']['Second'][$gender]++;
                                    } else {
                                        $Content['D01']['Regular']['Second'][$gender] = 1;
                                    }
                                } else {
                                    if (isset($Content['D01']['Regular']['First'][$gender])) {
                                        $Content['D01']['Regular']['First'][$gender]++;
                                    } else {
                                        $Content['D01']['Regular']['First'][$gender] = 1;
                                    }
                                }
                            }
                        } elseif ($tblSchoolEnrollmentType->getIdentifier() == 'POSTPONED') {
                            if (isset($Content['D01']['Postponed'][$gender])) {
                                $Content['D01']['Postponed'][$gender]++;
                            } else {
                                $Content['D01']['Postponed'][$gender] = 1;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $Content
     * @param TblPerson $tblPerson
     * @param TblLevel $tblLevel
     * @param TblDivision $tblDivision
     * @param $gender
     * @param $isInPreparationDivisionForMigrants
     */
    private static function setDivisionStudents(
        &$Content,
        TblPerson $tblPerson,
        TblLevel $tblLevel,
        TblDivision $tblDivision,
        $gender,
        $isInPreparationDivisionForMigrants
    ) {
        if ($gender) {
            if (isset($Content['E01']['Student']['L' . $tblLevel->getName()][$gender])) {
                $Content['E01']['Student']['L' . $tblLevel->getName()][$gender]++;
            } else {
                $Content['E01']['Student']['L' . $tblLevel->getName()][$gender] = 1;
            }

            if ($isInPreparationDivisionForMigrants) {
                if (isset($Content['E01']['Student']['Migration'][$gender])) {
                    $Content['E01']['Student']['Migration'][$gender]++;
                } else {
                    $Content['E01']['Student']['Migration'][$gender] = 1;
                }
            }

            if (isset($Content['E01']['Student']['TotalCount'][$gender])) {
                $Content['E01']['Student']['TotalCount'][$gender]++;
            } else {
                $Content['E01']['Student']['TotalCount'][$gender] = 1;
            }

            /**
             * E07
             */
            if (($tblLevel->getName() == '1' || $tblLevel->getName() == '01')
                && !self::hasRepeaters($tblPerson, $tblLevel, $tblDivision)
            ) {
                $identifier = 'NewSchoolStarter';
            } else {
                $identifier = 'PrimarySchool';
            }

            if (isset($Content['E07'][$identifier]['L' . $tblLevel->getName()][$gender])) {
                $Content['E07'][$identifier]['L' . $tblLevel->getName()][$gender]++;
            } else {
                $Content['E07'][$identifier]['L' . $tblLevel->getName()][$gender] = 1;
            }

            if ($isInPreparationDivisionForMigrants) {
                if (isset($Content['E07'][$identifier]['Migration'][$gender])) {
                    $Content['E07'][$identifier]['Migration'][$gender]++;
                } else {
                    $Content['E07'][$identifier]['Migration'][$gender] = 1;
                }
            }

            if (isset($Content['E07'][$identifier]['TotalCount'][$gender])) {
                $Content['E07'][$identifier]['TotalCount'][$gender]++;
            } else {
                $Content['E07'][$identifier]['TotalCount'][$gender] = 1;
            }

            /**
             * TotalCount
             */
            $identifier = 'TotalCount';
            if (isset($Content['E07'][$identifier]['L' . $tblLevel->getName()][$gender])) {
                $Content['E07'][$identifier]['L' . $tblLevel->getName()][$gender]++;
            } else {
                $Content['E07'][$identifier]['L' . $tblLevel->getName()][$gender] = 1;
            }
            if ($isInPreparationDivisionForMigrants) {
                if (isset($Content['E07'][$identifier]['Migration'][$gender])) {
                    $Content['E07'][$identifier]['Migration'][$gender]++;
                } else {
                    $Content['E07'][$identifier]['Migration'][$gender] = 1;
                }
            }
            if (isset($Content['E07'][$identifier]['TotalCount'][$gender])) {
                $Content['E07'][$identifier]['TotalCount'][$gender]++;
            } else {
                $Content['E07'][$identifier]['TotalCount'][$gender] = 1;
            }
        }
    }

    /**
     * @param $tblPastYearList
     * @param $Content
     */
    private static function setGraduate(
        $tblPastYearList,
        &$Content
    ) {
        if ($tblPastYearList) {
            $countArray = array();
            foreach ($tblPastYearList as $tblPastYear) {
                if (($tblGenerateCertificateList = Generate::useService()->getGenerateCertificateAllByYear($tblPastYear))) {
                    foreach ($tblGenerateCertificateList as $tblGenerateCertificate) {
                        if (($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
                            && ($tblCertificateType->getIdentifier() == 'LEAVE'
                                || $tblCertificateType->getIdentifier() == 'DIPLOMA')
                            && (($tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate)))
                        ) {

                            foreach ($tblPrepareList as $tblPrepare) {
                                if (($tblPrepareStudentList = Prepare::useService()->getPrepareStudentAllByPrepare($tblPrepare))) {
                                    foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                                        if ($tblPrepareStudent->isPrinted()
                                            && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
                                            && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                                            && ($tblDivision = $tblPrepare->getServiceTblDivision())
                                            && ($tblLevel = $tblDivision->getTblLevel())
                                        ) {

                                            $certificate = $tblLevel->getName();

                                            $hasMigrationBackground = false;
                                            if (($tblStudent = $tblPerson->getStudent())
                                                && $tblStudent->getHasMigrationBackground()
                                            ) {
                                                $hasMigrationBackground = true;
                                            }

                                            if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))
                                                && (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates()))
                                                && ($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())
                                                && ($birthDay = $tblCommonBirthDates->getBirthday())
                                            ) {

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
                                                } else {
                                                    $birthYear = false;
                                                }

                                                if ($tblCertificateType->getIdentifier() == 'LEAVE') {

                                                    if (isset($Content['B01']['Leave']['L' . $certificate][$gender])) {
                                                        $Content['B01']['Leave']['L' . $certificate][$gender]++;
                                                    } else {
                                                        $Content['B01']['Leave']['L' . $certificate][$gender] = 1;
                                                    }
                                                    if (isset($Content['B01']['Leave']['TotalCount'][$gender])) {
                                                        $Content['B01']['Leave']['TotalCount'][$gender]++;
                                                    } else {
                                                        $Content['B01']['Leave']['TotalCount'][$gender] = 1;
                                                    }
                                                    if (isset($Content['B01']['TotalCount']['L' . $certificate][$gender])) {
                                                        $Content['B01']['TotalCount']['L' . $certificate][$gender]++;
                                                    } else {
                                                        $Content['B01']['TotalCount']['L' . $certificate][$gender] = 1;
                                                    }
                                                    if (isset($Content['B01']['TotalCount'][$gender])) {
                                                        $Content['B01']['TotalCount'][$gender] += 1;
                                                    } else {
                                                        $Content['B01']['TotalCount'][$gender] = 1;
                                                    }

                                                    /**
                                                     * B02
                                                     */
                                                    if ($birthYear) {
                                                        if (isset($countArray[$birthYear]['Leave'][$gender])) {
                                                            $countArray[$birthYear]['Leave'][$gender]++;
                                                        } else {
                                                            $countArray[$birthYear]['Leave'][$gender] = 1;
                                                        }
                                                    }

                                                    /**
                                                     * B01.1
                                                     */
                                                    if ($hasMigrationBackground) {
                                                        if (isset($Content['B01_1']['Leave']['L' . $certificate][$gender])) {
                                                            $Content['B01_1']['Leave']['L' . $certificate][$gender]++;
                                                        } else {
                                                            $Content['B01_1']['Leave']['L' . $certificate][$gender] = 1;
                                                        }
                                                        if (isset($Content['B01_1']['Leave']['TotalCount'][$gender])) {
                                                            $Content['B01_1']['Leave']['TotalCount'][$gender]++;
                                                        } else {
                                                            $Content['B01_1']['Leave']['TotalCount'][$gender] = 1;
                                                        }
                                                        if (isset($Content['B01_1']['TotalCount']['L' . $certificate][$gender])) {
                                                            $Content['B01_1']['TotalCount']['L' . $certificate][$gender]++;
                                                        } else {
                                                            $Content['B01_1']['TotalCount']['L' . $certificate][$gender] = 1;
                                                        }
                                                        if (isset($Content['B01_1']['TotalCount'][$gender])) {
                                                            $Content['B01_1']['TotalCount'][$gender] += 1;
                                                        } else {
                                                            $Content['B01_1']['TotalCount'][$gender] = 1;
                                                        }
                                                    }

                                                } else {

                                                    if (isset($Content['B01'][$tblCertificate->getCertificate()]['L' . $certificate][$gender])) {
                                                        $Content['B01'][$tblCertificate->getCertificate()]['L' . $certificate][$gender]++;
                                                    } else {
                                                        $Content['B01'][$tblCertificate->getCertificate()]['L' . $certificate][$gender] = 1;
                                                    }
                                                    if (isset($Content['B01'][$tblCertificate->getCertificate()]['TotalCount'][$gender])) {
                                                        $Content['B01'][$tblCertificate->getCertificate()]['TotalCount'][$gender]++;
                                                    } else {
                                                        $Content['B01'][$tblCertificate->getCertificate()]['TotalCount'][$gender] = 1;
                                                    }
                                                    if (isset($Content['B01']['TotalCount']['L' . $certificate][$gender])) {
                                                        $Content['B01']['TotalCount']['L' . $certificate][$gender]++;
                                                    } else {
                                                        $Content['B01']['TotalCount']['L' . $certificate][$gender] = 1;
                                                    }

                                                    if (isset($Content['B01']['TotalCount'][$gender])) {
                                                        $Content['B01']['TotalCount'][$gender] += 1;
                                                    } else {
                                                        $Content['B01']['TotalCount'][$gender] = 1;
                                                    }

                                                    /**
                                                     * B02
                                                     */
                                                    if ($birthYear) {
                                                        if (isset($countArray[$birthYear][$tblCertificate->getCertificate()][$gender])) {
                                                            $countArray[$birthYear][$tblCertificate->getCertificate()][$gender]++;
                                                        } else {
                                                            $countArray[$birthYear][$tblCertificate->getCertificate()][$gender] = 1;
                                                        }
                                                    }

                                                    /**
                                                     * B01.1
                                                     */
                                                    if ($hasMigrationBackground) {
                                                        if (isset($Content['B01_1'][$tblCertificate->getCertificate()]['L' . $certificate][$gender])) {
                                                            $Content['B01_1'][$tblCertificate->getCertificate()]['L' . $certificate][$gender]++;
                                                        } else {
                                                            $Content['B01_1'][$tblCertificate->getCertificate()]['L' . $certificate][$gender] = 1;
                                                        }
                                                        if (isset($Content['B01_1'][$tblCertificate->getCertificate()]['TotalCount'][$gender])) {
                                                            $Content['B01_1'][$tblCertificate->getCertificate()]['TotalCount'][$gender]++;
                                                        } else {
                                                            $Content['B01_1'][$tblCertificate->getCertificate()]['TotalCount'][$gender] = 1;
                                                        }
                                                        if (isset($Content['B01_1']['TotalCount']['L' . $certificate][$gender])) {
                                                            $Content['B01_1']['TotalCount']['L' . $certificate][$gender]++;
                                                        } else {
                                                            $Content['B01_1']['TotalCount']['L' . $certificate][$gender] = 1;
                                                        }
                                                        if (isset($Content['B01_1']['TotalCount'][$gender])) {
                                                            $Content['B01_1']['TotalCount'][$gender] += 1;
                                                        } else {
                                                            $Content['B01_1']['TotalCount'][$gender] = 1;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            /**
             * B02. Absolventen/Abgänger aus dem Schuljahr 2015/16 nach Geburtsjahren und Abschlussarten
             */
            ksort($countArray);
            $count = 0;
            $Content['B02']['TotalCount']['m'] = 0;
            $Content['B02']['TotalCount']['w'] = 0;
            foreach ($countArray as $year => $certificateArray) {
                $Content['B02']['Y' . $count]['YearName'] = $year;
                $Content['B02']['Y' . $count]['m'] = 0;
                $Content['B02']['Y' . $count]['w'] = 0;
                $Content['B02']['Y' . $count]['x'] = 0;
                foreach ($certificateArray as $certificate => $genderArray) {
                    foreach ($genderArray as $gender => $value) {
                        $Content['B02']['Y' . $count][$certificate][$gender] = $value;

                        if (isset($Content['B02']['TotalCount'][$certificate][$gender])) {
                            $Content['B02']['TotalCount'][$certificate][$gender] += $value;
                        } else {
                            $Content['B02']['TotalCount'][$certificate][$gender] = $value;
                        }

                        $Content['B02']['Y' . $count][$gender] += $value;
                        $Content['B02']['TotalCount'][$gender] += $value;
                    }
                }

                $count++;
            }
        }
    }
}