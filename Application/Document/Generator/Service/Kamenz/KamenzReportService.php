<?php

namespace SPHERE\Application\Document\Generator\Service\Kamenz;

use SPHERE\Application\Education\Certificate\Generate\Generate;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Student;

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
    ) {

        $currentYearName = '';
        $pastYearName = '';
        $tblPastYearList = false;

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

            $pastYearName = (string)($year[0] - 1) . '/' . (string)($year[1] - 1);
            $tblPastYearList = Term::useService()->getYearByName($pastYearName);
        }

        $Content['SchoolYear']['Current'] = $currentYearName;
        $Content['SchoolYear']['Past'] = $pastYearName;

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
                                    if (($tblStudent = $tblPerson->getStudent())
                                        && $tblStudent->isInPreparationDivisionForMigrants()
                                    ) {
                                        $isInPreparationDivisionForMigrants = true;
                                    }

                                    $hasMigrationBackground = false;
                                    if (($tblStudent = $tblPerson->getStudent())
                                        && $tblStudent->getHasMigrationBackground()
                                    ) {
                                        $hasMigrationBackground = true;
                                    }

                                    $gender = false;
                                    // Todo extract methods
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

                                    /**
                                     * E04 Schüler mit der ersten Fremdsprache im Schuljahr nach Klassenstufen
                                     */
                                    if (($tblStudent = $tblPerson->getStudent())
                                        && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
                                    ) {
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

                                        // todo migranten
                                    }

                                    /**
                                     * E05 Schüler im Ethik- bzw. Religionsunterricht im Schuljahr nach Klassenstufen
                                     */
                                    if (($tblStudent = $tblPerson->getStudent())
                                        && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('RELIGION'))
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
                                    } else {
                                        if (isset($countReligionArray['ZZ_Keine_Teilnahme'][$tblLevel->getName()])) {
                                            $countReligionArray['ZZ_Keine_Teilnahme'][$tblLevel->getName()]++;
                                        } else {
                                            $countReligionArray['ZZ_Keine_Teilnahme'][$tblLevel->getName()] = 1;
                                        }
                                    }

                                    /**
                                     * E12 Schüler im NEIGUNGSKURSBEREICH im Schuljahr nach Klassenstufen
                                     */
                                    if (($tblStudent = $tblPerson->getStudent())
                                        && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'))
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
                                }
                            } else {
                                $countDivisionStudentArray[$tblDivision->getId()][$tblLevel->getName()] = 0;
                            }
                        }
                    }
                }
            }

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

        return $Content;
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