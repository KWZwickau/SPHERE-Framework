<?php

namespace SPHERE\Application\Document\Generator\Service\Kamenz;

use DateInterval;
use DateTime;
use SPHERE\Application\Api\Education\Certificate\Generator\Repository\GymAbgSekI;
use SPHERE\Application\Corporation\Group\Group;
use SPHERE\Application\Education\Certificate\Generate\Generate;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonGender;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTenseOfLesson;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTrainingStatus;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class KamenzReportService
 *
 * @package SPHERE\Application\Document\Generator\Service\Kamenz
 */
class KamenzReportService
{
    /**
     * @param array $Content
     *
     * @return array
     */
    public static function setKamenzReportOsContent(array $Content): array
    {
        $tblCurrentYearList = array();
        $tblPastYearList = array();

        $tblKamenzSchoolType = Type::useService()->getTypeByName('Mittelschule / Oberschule');

        self::setYears($Content, $tblCurrentYearList, $tblPastYearList);

        self::setRepeatersFromCertificates($Content, $tblPastYearList, $tblKamenzSchoolType);

        /**
         * B
         */
        self::setGraduate($tblPastYearList, $Content, $tblKamenzSchoolType);

        if ($tblCurrentYearList) {
            $countArray = array();
            $countMigrantsArray = array();
            $countMigrantsNationalityArray = array();
            $countForeignSubjectArray = array();
            $countSecondForeignSubjectArray = array();
            $countReligionArray = array();
            $countOrientationArray = array();
            $countDivisionStudentArray = array();
            $countDivisionStudentArrayForSecondarySchool = array();

            /** @var TblYear[] $tblCurrentYearList */
            foreach ($tblCurrentYearList as $tblYear) {
                if (($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListBy($tblYear, $tblKamenzSchoolType))) {
                    foreach ($tblStudentEducationList as $tblStudentEducation) {
                        if (($tblPerson = $tblStudentEducation->getServiceTblPerson())
                            && ($level = $tblStudentEducation->getLevel())
                            && (($tblDivision = $tblStudentEducation->getTblDivision())
                                || ($tblDivision = $tblStudentEducation->getTblCoreGroup()))
                        ) {
                            if (isset($countDivisionStudentArray[$tblDivision->getId()][$level])) {
                                $countDivisionStudentArray[$tblDivision->getId()][$level]++;
                            } else {
                                $countDivisionStudentArray[$tblDivision->getId()][$level] = 1;
                            }

                            $tblStudent = $tblPerson->getStudent();

                            $isInPreparationDivisionForMigrants = false;
                            if ($tblStudent && $tblStudent->isInPreparationDivisionForMigrants()) {
                                $isInPreparationDivisionForMigrants = true;
                            }

                            $hasMigrationBackground = false;
                            if ($tblStudent && $tblStudent->getHasMigrationBackground()) {
                                $hasMigrationBackground = true;
                            }

                            $gender = false;
                            $birthDay = false;
                            self::countStudentLevels(
                                $tblPerson, $level, $gender, $hasMigrationBackground, $isInPreparationDivisionForMigrants, $birthDay, $countArray,
                                $countMigrantsArray, $countMigrantsNationalityArray
                            );

                            if ($tblStudent) {
                                self::countForeignLanguages(
                                    $tblStudent, $level, $tblKamenzSchoolType, $Content, $gender, $isInPreparationDivisionForMigrants,
                                    $countForeignSubjectArray, $countSecondForeignSubjectArray
                                );

                                $countReligionArray = self::countReligion($tblStudent, $level, $countReligionArray);

                                if (preg_match('!(0?([789]))!is', (string) $level)) {
                                    $countOrientationArray = self::countOrientation($tblStudent, $level, $gender, $countOrientationArray);
                                }
                            } else {
                                if (isset($countReligionArray['ZZ_Keine_Teilnahme'][$level])) {
                                    $countReligionArray['ZZ_Keine_Teilnahme'][$level]++;
                                } else {
                                    $countReligionArray['ZZ_Keine_Teilnahme'][$level] = 1;
                                }
                            }

                            $tblCourse = $tblStudentEducation->getServiceTblCourse();

                            self::countDivisionStudentsForSecondarySchool(
                                $countDivisionStudentArrayForSecondarySchool, $tblDivision, $level, $gender, $isInPreparationDivisionForMigrants,
                                $tblCourse ?: null
                            );

                            self::setRepeatersOs($tblPerson, $level, $tblPastYearList, $Content, $gender, $tblCourse ?: null, $tblKamenzSchoolType);

                            self::setStudentFocus($tblPerson, $level, $Content, $gender, $hasMigrationBackground, $isInPreparationDivisionForMigrants);

                            self::setSchoolTypeLastYear(
                                $Content, $tblPastYearList, $tblPerson, $level, $gender, $isInPreparationDivisionForMigrants, $tblKamenzSchoolType
                            );
                        }
                    }
                }
            }

            self::setStudentLevels($Content, $countArray, $countMigrantsArray, $countMigrantsNationalityArray);
            self::setForeignLanguages($Content, $countForeignSubjectArray, $countSecondForeignSubjectArray, $tblKamenzSchoolType);
            self::setReligion($Content, $countReligionArray);
            self::setOrientation($Content, $countOrientationArray);
            self::setDivisionFrequency($Content, $countDivisionStudentArray);
            self::setDivisionStudentsForSecondarySchool($Content, $countDivisionStudentArrayForSecondarySchool);
        }

        return $Content;
    }

    /**
     * @param array $Content
     *
     * @return array
     */
    public static function setKamenzReportGsContent(array $Content): array
    {
        $tblCurrentYearList = array();
        $tblPastYearList = array();
        $year = false;

        $tblKamenzSchoolType = Type::useService()->getTypeByName('Grundschule');

        self::setYears($Content, $tblCurrentYearList, $tblPastYearList, $year);

        self::setRepeatersFromCertificates($Content, $tblPastYearList, $tblKamenzSchoolType);

        if ($tblCurrentYearList) {
            $countArray = array();
            $countMigrantsArray = array();
            $countMigrantsNationalityArray = array();
            $countForeignSubjectArray = array();
            $countSecondForeignSubjectArray = array();
            $countReligionArray = array();
            $countDivisionStudentArray = array();
            $countDivisionByLevelArray = array();

            /** @var TblYear[] $tblCurrentYearList */
            foreach ($tblCurrentYearList as $tblYear) {
                if (($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListBy($tblYear, $tblKamenzSchoolType))) {
                    foreach ($tblStudentEducationList as $tblStudentEducation) {
                        if (($tblPerson = $tblStudentEducation->getServiceTblPerson())
                            && ($level = $tblStudentEducation->getLevel())
                            && (($tblDivision = $tblStudentEducation->getTblDivision())
                                || ($tblDivision = $tblStudentEducation->getTblCoreGroup()))
                        ) {
                            $countDivisionByLevelArray[$level][$tblDivision->getId()] = 1;

                            if (isset($countDivisionStudentArray[$tblDivision->getId()][$level])) {
                                $countDivisionStudentArray[$tblDivision->getId()][$level]++;
                            } else {
                                $countDivisionStudentArray[$tblDivision->getId()][$level] = 1;
                            }

                            $tblStudent = $tblPerson->getStudent();

                            $isInPreparationDivisionForMigrants = false;
                            if ($tblStudent && $tblStudent->isInPreparationDivisionForMigrants()) {
                                $isInPreparationDivisionForMigrants = true;
                            }

                            $hasMigrationBackground = false;
                            if ($tblStudent && $tblStudent->getHasMigrationBackground()) {
                                $hasMigrationBackground = true;
                            }

                            $gender = false;
                            $birthDay = false;
                            self::countStudentLevels(
                                $tblPerson, $level, $gender, $hasMigrationBackground, $isInPreparationDivisionForMigrants, $birthDay, $countArray,
                                $countMigrantsArray, $countMigrantsNationalityArray
                            );

                            self::setDivisionStudents(
                                $Content, $tblPerson, $level, $tblPastYearList, $gender, $isInPreparationDivisionForMigrants, $tblKamenzSchoolType
                            );

                            if ($tblStudent) {
                                self::countForeignLanguages(
                                    $tblStudent, $level, $tblKamenzSchoolType, $Content, $gender, $isInPreparationDivisionForMigrants,
                                    $countForeignSubjectArray, $countSecondForeignSubjectArray
                                );

                                $countReligionArray = self::countReligion($tblStudent, $level, $countReligionArray);

                                // Schulanfänger
                                self::setNewSchoolStarter(
                                    $Content,
                                    $tblPerson,
                                    $tblStudent,
                                    $level,
                                    $tblPastYearList,
                                    $gender,
                                    $birthDay,
                                    $year,
                                    $tblKamenzSchoolType
                                );

                                self::setStudentFocus(
                                    $tblPerson, $level, $Content, $gender, $hasMigrationBackground, $isInPreparationDivisionForMigrants
                                );
                            } else {
                                if (isset($countReligionArray['ZZ_Keine_Teilnahme'][$level])) {
                                    $countReligionArray['ZZ_Keine_Teilnahme'][$level]++;
                                } else {
                                    $countReligionArray['ZZ_Keine_Teilnahme'][$level] = 1;
                                }
                            }
                        }
                    }
                }
            }

            self::setStudentLevels($Content, $countArray, $countMigrantsArray, $countMigrantsNationalityArray);
            self::setForeignLanguages($Content, $countForeignSubjectArray, $countSecondForeignSubjectArray, $tblKamenzSchoolType);
            self::setReligion($Content, $countReligionArray);
            self::setDivisionFrequency($Content, $countDivisionStudentArray);
            self::setDivisionByLevel($Content, $countDivisionByLevelArray);
        }

        return $Content;
    }

    /**
     * @param array $Content
     *
     * @return array
     */
    public static function setKamenzReportGymContent(array $Content): array
    {
        $tblCurrentYearList = array();
        $tblPastYearList = array();

        $tblKamenzSchoolType = Type::useService()->getTypeByName('Gymnasium');

        self::setYears($Content, $tblCurrentYearList, $tblPastYearList);

        self::setRepeatersFromCertificates($Content, $tblPastYearList, $tblKamenzSchoolType);

        /**
         * B
         */
        self::setGraduate($tblPastYearList, $Content, $tblKamenzSchoolType);

        if ($tblCurrentYearList) {
            $countArray = array();
            $countMigrantsArray = array();
            $countMigrantsNationalityArray = array();
            $countForeignSubjectArray = array();
            $countForeignSubjectMatrix = array();
            $countSecondForeignSubjectArray = array();
            $countProfileArray = array();
            $countReligionArray = array();
            $countDivisionStudentArray = array();
            $countDivisionByLevelArray = array();
            $countAdvancedCourseArray = array();
            $countBasisCourseArray = array();
            $personAdvancedCourseList = array();
            /** @var TblYear[] $tblCurrentYearList */
            foreach ($tblCurrentYearList as $tblYear) {
                if (($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListBy($tblYear, $tblKamenzSchoolType))) {
                    foreach ($tblStudentEducationList as $tblStudentEducation) {
                        if (($tblPerson = $tblStudentEducation->getServiceTblPerson())
                            && ($level = $tblStudentEducation->getLevel())
                            && (($tblDivision = $tblStudentEducation->getTblDivision())
                                || ($tblDivision = $tblStudentEducation->getTblCoreGroup()))
                        ) {
                            $countDivisionByLevelArray[$level][$tblDivision->getId()] = 1;

                            if (isset($countDivisionStudentArray[$tblDivision->getId()][$level])) {
                                $countDivisionStudentArray[$tblDivision->getId()][$level]++;
                            } else {
                                $countDivisionStudentArray[$tblDivision->getId()][$level] = 1;
                            }

                            $tblStudent = $tblPerson->getStudent();

                            $isInPreparationDivisionForMigrants = false;
                            if ($tblStudent && $tblStudent->isInPreparationDivisionForMigrants()) {
                                $isInPreparationDivisionForMigrants = true;
                            }

                            $hasMigrationBackground = false;
                            if ($tblStudent && $tblStudent->getHasMigrationBackground()) {
                                $hasMigrationBackground = true;
                            }

                            $gender = false;
                            $birthDay = false;
                            self::countStudentLevels(
                                $tblPerson, $level, $gender, $hasMigrationBackground, $isInPreparationDivisionForMigrants, $birthDay, $countArray,
                                $countMigrantsArray, $countMigrantsNationalityArray
                            );

                            self::setDivisionStudents(
                                $Content, $tblPerson, $level, $tblPastYearList, $gender, $isInPreparationDivisionForMigrants, $tblKamenzSchoolType
                            );

                            self::setRepeatersGym($tblPerson, $level, $tblPastYearList, $Content, $gender, $tblKamenzSchoolType);

                            self::countCourses($tblPerson, $level, $tblYear, $gender, $countAdvancedCourseArray, $countBasisCourseArray, $personAdvancedCourseList);

                            if ($tblStudent) {
                                self::countForeignLanguages(
                                    $tblStudent, $level, $tblKamenzSchoolType, $Content, $gender, $isInPreparationDivisionForMigrants,
                                    $countForeignSubjectArray, $countSecondForeignSubjectArray
                                );

                                $countReligionArray = self::countReligion($tblStudent, $level, $countReligionArray);

                                $countProfileArray = self::countProfile($tblStudent, $level, $gender, $countProfileArray);

                                self::setStudentFocus(
                                    $tblPerson, $level, $Content, $gender, $hasMigrationBackground, $isInPreparationDivisionForMigrants
                                );

                                if ($level < 11) {
                                    $countForeignSubjectMatrix = self::countForeignLanguagesMatrix($tblPerson, $level, $countForeignSubjectMatrix);
                                }

                            } else {
                                if (isset($countReligionArray['ZZ_Keine_Teilnahme'][$level])) {
                                    $countReligionArray['ZZ_Keine_Teilnahme'][$level]++;
                                } else {
                                    $countReligionArray['ZZ_Keine_Teilnahme'][$level] = 1;
                                }
                            }

                            // get last Level
                            self::setSchoolTypeLastYear(
                                $Content, $tblPastYearList, $tblPerson, $level, $gender, $isInPreparationDivisionForMigrants, $tblKamenzSchoolType
                            );
                        }
                    }
                }
            }

            self::setStudentLevels($Content, $countArray, $countMigrantsArray, $countMigrantsNationalityArray);
            self::setForeignLanguages($Content, $countForeignSubjectArray, $countSecondForeignSubjectArray, $tblKamenzSchoolType);
            self::setReligion($Content, $countReligionArray);
            self::setProfile($Content, $countProfileArray);
            self::setCourses($Content, $countAdvancedCourseArray, $countBasisCourseArray);
            self::setDivisionFrequency($Content, $countDivisionStudentArray);
            self::setForeignLanguagesMatrix($Content, $countForeignSubjectMatrix);
            self::setCoursesMatrix($Content, $personAdvancedCourseList);
            self::setDivisionByLevel($Content, $countDivisionByLevelArray);
        }

        return $Content;
    }

    /**
     * @param array $Content
     * @param TblType $tblKamenzSchoolType
     *
     * @return array
     */
    public static function setKamenzReportBFSContent(
        array $Content,
        TblType $tblKamenzSchoolType
    ): array {
        $tblCurrentYearList = array();
        $tblPastYearList = array();

        self::setYears($Content, $tblCurrentYearList, $tblPastYearList);

        /**
         * B
         */
        self::setGraduateTechnicalSchool($tblPastYearList, $Content, $tblKamenzSchoolType);

        if ($tblCurrentYearList) {
            /** @var TblYear[] $tblCurrentYearList */
            foreach ($tblCurrentYearList as $tblYear) {
                if (($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListBy($tblYear, $tblKamenzSchoolType))) {
                    foreach ($tblStudentEducationList as $tblStudentEducation) {
                        if (($tblPerson = $tblStudentEducation->getServiceTblPerson())
                            && ($level = $tblStudentEducation->getLevel())
                        ) {
                            $tblStudent = $tblPerson->getStudent();

                            $hasMigrationBackground = false;
                            if ($tblStudent && $tblStudent->getHasMigrationBackground()) {
                                $hasMigrationBackground = true;
                            }

                            $tblCommonGender = false;
                            $birthYear = false;
                            $nationality = false;
                            if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
                                if (($tblCommonInformation = $tblCommon->getTblCommonInformation())) {
                                    $nationality = $tblCommonInformation->getNationality();
                                }
                                if (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())) {
                                    $tblCommonGender = $tblCommonBirthDates->getTblCommonGender();
                                    if (($birthDay = $tblCommonBirthDates->getBirthday())) {
                                        $birthYear = (new DateTime($birthDay))->format('Y');
                                    }
                                }
                            }

                            if ($tblStudent && ($tblStudentTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())
                                && ($tblStudentTenseOfLesson = $tblStudentTechnicalSchool->getTblStudentTenseOfLesson())
                                && ($tblStudentTrainingStatus = $tblStudentTechnicalSchool->getTblStudentTrainingStatus())
                                && $tblCommonGender
                            ) {
                                $isFullTime = $tblStudentTenseOfLesson->getIdentifier() == TblStudentTenseOfLesson::FULL_TIME;
                                $isChangeStudent = $tblStudentTrainingStatus->getIdentifier() == TblStudentTrainingStatus::CHANGE_STUDENT;
                                $schoolDiploma = ($tblSchoolDiploma = $tblStudentTechnicalSchool->getServiceTblSchoolDiploma())
                                    ? $tblSchoolDiploma->getName() : '&nbsp;';
                                $schoolType = ($tblSchoolType = $tblStudentTechnicalSchool->getServiceTblSchoolType())
                                    ? $tblSchoolType->getName() : '&nbsp;';
                                $technicalDiploma = ($tblTechnicalDiploma = $tblStudentTechnicalSchool->getServiceTblTechnicalDiploma())
                                    ? $tblTechnicalDiploma->getName() : '&nbsp;';
                                $technicalType = ($tblTechnicalType = $tblStudentTechnicalSchool->getServiceTblTechnicalType())
                                    ? $tblTechnicalType->getName() : '&nbsp;';
                                $course = ($tblTechnicalCourse = $tblStudentTechnicalSchool->getServiceTblTechnicalCourse())
                                    ? $tblTechnicalCourse->getName() : '&nbsp;';
                                $time = $tblStudentTechnicalSchool->getDurationOfTraining();

                                if (($tblSupport = Student::useService()->getSupportForReportingByPerson($tblPerson))
                                    && ($tblSupportFocus = Student::useService()->getSupportPrimaryFocusBySupport($tblSupport))
                                    && ($tblSupportFocusType = $tblSupportFocus->getTblSupportFocusType())
                                ) {
                                    $support = $tblSupportFocusType->getName();
                                } else {
                                    $support = '&nbsp;';
                                }

                                self::setDivisionStudentsForTechnicalSchool(
                                    $Content, $level, $isFullTime ? 'FullTime' : 'PartTime', $isChangeStudent ? 'ChangeStudent' : 'Student'
                                );

                                self::setStudentFocusBFS(
                                    $tblPerson, $level, $Content, $tblCommonGender->getShortName(), $hasMigrationBackground, $isFullTime ? 'F01_1' : 'F01_2'
                                );

                                // Neuanfänger
                                if (self::isNewSchoolStarterForTechnicalSchool($tblPerson, $tblPastYearList)) {
                                    // N01
                                    self::setNewSchoolStarterDiplomaForTechnicalSchool(
                                        $Content,
                                        'N01_' . ($isFullTime ? '1' : '2') . '_' . ($isChangeStudent ? 'U' : 'A'),
                                        $schoolDiploma,
                                        $schoolType,
                                        $support,
                                        $tblCommonGender,
                                        $level
                                    );
                                    if ($hasMigrationBackground) {
                                        self::setNewSchoolStarterDiplomaForTechnicalSchool(
                                            $Content,
                                            'N01_' . ($isFullTime ? '1' : '2') . '_1_' . ($isChangeStudent ? 'U' : 'A'),
                                            $schoolDiploma,
                                            $schoolType,
                                            $support,
                                            $tblCommonGender,
                                            $level
                                        );
                                    }

                                    // N02
                                    self::setNewSchoolStarterDiplomaForTechnicalSchool(
                                        $Content,
                                        'N02_' . ($isFullTime ? '1' : '2') . '_' . ($isChangeStudent ? 'U' : 'A'),
                                        $technicalDiploma,
                                        $technicalType,
                                        $support,
                                        $tblCommonGender,
                                        $level
                                    );
                                    if ($hasMigrationBackground) {
                                        self::setNewSchoolStarterDiplomaForTechnicalSchool(
                                            $Content,
                                            'N02_' . ($isFullTime ? '1' : '2') . '_1_' . ($isChangeStudent ? 'U' : 'A'),
                                            $schoolDiploma,
                                            $schoolType,
                                            $support,
                                            $tblCommonGender,
                                            $level
                                        );
                                    }

                                    // N03
                                    if ($birthYear) {
                                        self::setBirthYearOrNationalityForTechnicalSchool(
                                            $Content,
                                            'N03_' . ($isFullTime ? '1' : '2') . '_' . ($isChangeStudent ? 'U' : 'A'),
                                            $birthYear,
                                            $tblCommonGender,
                                            $level
                                        );
                                        if ($hasMigrationBackground) {
                                            self::setBirthYearOrNationalityForTechnicalSchool(
                                                $Content,
                                                'N03_' . ($isFullTime ? '1' : '2') . '_1_' . ($isChangeStudent ? 'U' : 'A'),
                                                $birthYear,
                                                $tblCommonGender,
                                                $level
                                            );
                                        }
                                    }

                                    // N04
                                    if ($nationality && $hasMigrationBackground) {
                                        self::setBirthYearOrNationalityForTechnicalSchool(
                                            $Content,
                                            'N04_' . ($isFullTime ? '1' : '2') . '_1_' . ($isChangeStudent ? 'U' : 'A'),
                                            $nationality,
                                            $tblCommonGender,
                                            $level
                                        );
                                    }

                                    // N05
                                    if ($course && $time) {
                                        self::setCourseForTechnicalSchool(
                                            $Content,
                                            'N05_' . ($isFullTime ? '1' : '2') . '_' . ($isChangeStudent ? 'U' : 'A'),
                                            $course,
                                            $time,
                                            $support,
                                            $tblCommonGender,
                                            $level
                                        );
                                        if ($hasMigrationBackground) {
                                            self::setCourseForTechnicalSchool(
                                                $Content,
                                                'N05_' . ($isFullTime ? '1' : '2') . '_1_' . ($isChangeStudent ? 'U' : 'A'),
                                                $course,
                                                $time,
                                                $support,
                                                $tblCommonGender,
                                                $level
                                            );
                                        }
                                    }
                                }

                                // S01
                                if ($course && $time) {
                                    self::setCourseForTechnicalSchool(
                                        $Content,
                                        'S01_' . ($isFullTime ? '1' : '2') . '_' . ($isChangeStudent ? 'U' : 'A'),
                                        $course,
                                        $time,
                                        $support,
                                        $tblCommonGender,
                                        $level
                                    );
                                    if ($hasMigrationBackground) {
                                        self::setCourseForTechnicalSchool(
                                            $Content,
                                            'S01_' . ($isFullTime ? '1' : '2') . '_1_' . ($isChangeStudent ? 'U' : 'A'),
                                            $course,
                                            $time,
                                            $support,
                                            $tblCommonGender,
                                            $level
                                        );
                                    }
                                }

                                // S02
                                if ($birthYear) {
                                    self::setBirthYearOrNationalityForTechnicalSchool(
                                        $Content,
                                        'S02_' . ($isFullTime ? '1' : '2') . '_' . ($isChangeStudent ? 'U' : 'A'),
                                        $birthYear,
                                        $tblCommonGender,
                                        $level
                                    );
                                    if ($hasMigrationBackground) {
                                        self::setBirthYearOrNationalityForTechnicalSchool(
                                            $Content,
                                            'S02_' . ($isFullTime ? '1' : '2') . '_1_' . ($isChangeStudent ? 'U' : 'A'),
                                            $birthYear,
                                            $tblCommonGender,
                                            $level
                                        );
                                    }
                                }

                                // S03
                                if ($nationality && $hasMigrationBackground) {
                                    self::setBirthYearOrNationalityForTechnicalSchool(
                                        $Content,
                                        'S03_' . ($isFullTime ? '1' : '2') . '_1_' . ($isChangeStudent ? 'U' : 'A'),
                                        $nationality,
                                        $tblCommonGender,
                                        $level
                                    );
                                }

                                // S04
                                $countLanguages = self::setForeignLanguagesForTechnicalSchool(
                                    $Content,
                                    'S04_' . ($isFullTime ? '1' : '2') . '_' . ($isChangeStudent ? 'U' : 'A'),
                                    $tblStudent,
                                    $level
                                );

                                // S04-1.1
                                $name = 'S04_' . ($isFullTime ? '1' : '2') . '_1';
                                if (isset($Content[$name][($isChangeStudent ? 'ChangeStudent' : 'Student')]['F' . $countLanguages]['L' . $level])) {
                                    $Content[$name][($isChangeStudent ? 'ChangeStudent' : 'Student')]['F' . $countLanguages]['L' . $level]++;
                                } else {
                                    $Content[$name][($isChangeStudent ? 'ChangeStudent' : 'Student')]['F' . $countLanguages]['L' . $level] = 1;
                                }
                                if (isset($Content[$name][($isChangeStudent ? 'ChangeStudent' : 'Student')]['F' . $countLanguages]['TotalCount'])) {
                                    $Content[$name][($isChangeStudent ? 'ChangeStudent' : 'Student')]['F' . $countLanguages]['TotalCount']++;
                                } else {
                                    $Content[$name][($isChangeStudent ? 'ChangeStudent' : 'Student')]['F' . $countLanguages]['TotalCount'] = 1;
                                }
                            }
                        }
                    }
                }
            }

            self::sumNewSchoolStarterDiplomaForTechnicalSchool($Content, 'N01_1_A');
            self::sumNewSchoolStarterDiplomaForTechnicalSchool($Content, 'N01_1_U');
            self::sumNewSchoolStarterDiplomaForTechnicalSchool($Content, 'N01_1_1_A');
            self::sumNewSchoolStarterDiplomaForTechnicalSchool($Content, 'N01_1_1_U');

            self::sumNewSchoolStarterDiplomaForTechnicalSchool($Content, 'N01_2_A');
            self::sumNewSchoolStarterDiplomaForTechnicalSchool($Content, 'N01_2_U');
            self::sumNewSchoolStarterDiplomaForTechnicalSchool($Content, 'N01_2_1_A');
            self::sumNewSchoolStarterDiplomaForTechnicalSchool($Content, 'N01_2_1_U');

            self::sumNewSchoolStarterDiplomaForTechnicalSchool($Content, 'N02_1_A');
            self::sumNewSchoolStarterDiplomaForTechnicalSchool($Content, 'N02_1_U');
            self::sumNewSchoolStarterDiplomaForTechnicalSchool($Content, 'N02_1_1_A');
            self::sumNewSchoolStarterDiplomaForTechnicalSchool($Content, 'N02_1_1_U');

            self::sumNewSchoolStarterDiplomaForTechnicalSchool($Content, 'N02_2_A');
            self::sumNewSchoolStarterDiplomaForTechnicalSchool($Content, 'N02_2_U');
            self::sumNewSchoolStarterDiplomaForTechnicalSchool($Content, 'N02_2_1_A');
            self::sumNewSchoolStarterDiplomaForTechnicalSchool($Content, 'N02_2_1_U');

            self::sumBirthYearOrNationalityForTechnicalSchool($Content, 'N03_1_A');
            self::sumBirthYearOrNationalityForTechnicalSchool($Content, 'N03_1_U');
            self::sumBirthYearOrNationalityForTechnicalSchool($Content, 'N03_1_1_A');
            self::sumBirthYearOrNationalityForTechnicalSchool($Content, 'N03_1_1_U');

            self::sumBirthYearOrNationalityForTechnicalSchool($Content, 'N03_2_A');
            self::sumBirthYearOrNationalityForTechnicalSchool($Content, 'N03_2_U');
            self::sumBirthYearOrNationalityForTechnicalSchool($Content, 'N03_2_1_A');
            self::sumBirthYearOrNationalityForTechnicalSchool($Content, 'N03_2_1_U');

            self::sumBirthYearOrNationalityForTechnicalSchool($Content, 'N04_1_1_A');
            self::sumBirthYearOrNationalityForTechnicalSchool($Content, 'N04_1_1_U');
            self::sumBirthYearOrNationalityForTechnicalSchool($Content, 'N04_2_1_A');
            self::sumBirthYearOrNationalityForTechnicalSchool($Content, 'N04_2_1_U');

            self::sumCourseForTechnicalSchool($Content, 'N05_1_A');
            self::sumCourseForTechnicalSchool($Content, 'N05_1_U');
            self::sumCourseForTechnicalSchool($Content, 'N05_1_1_A');
            self::sumCourseForTechnicalSchool($Content, 'N05_1_1_U');

            self::sumCourseForTechnicalSchool($Content, 'N05_2_A');
            self::sumCourseForTechnicalSchool($Content, 'N05_2_U');
            self::sumCourseForTechnicalSchool($Content, 'N05_2_1_A');
            self::sumCourseForTechnicalSchool($Content, 'N05_2_1_U');

            self::sumCourseForTechnicalSchool($Content, 'S01_1_A');
            self::sumCourseForTechnicalSchool($Content, 'S01_1_U');
            self::sumCourseForTechnicalSchool($Content, 'S01_1_1_A');
            self::sumCourseForTechnicalSchool($Content, 'S01_1_1_U');

            self::sumCourseForTechnicalSchool($Content, 'S01_2_A');
            self::sumCourseForTechnicalSchool($Content, 'S01_2_U');
            self::sumCourseForTechnicalSchool($Content, 'S01_2_1_A');
            self::sumCourseForTechnicalSchool($Content, 'S01_2_1_U');

            self::sumBirthYearOrNationalityForTechnicalSchool($Content, 'S02_1_A');
            self::sumBirthYearOrNationalityForTechnicalSchool($Content, 'S02_1_U');
            self::sumBirthYearOrNationalityForTechnicalSchool($Content, 'S02_1_1_A');
            self::sumBirthYearOrNationalityForTechnicalSchool($Content, 'S02_1_1_U');

            self::sumBirthYearOrNationalityForTechnicalSchool($Content, 'S02_2_A');
            self::sumBirthYearOrNationalityForTechnicalSchool($Content, 'S02_2_U');
            self::sumBirthYearOrNationalityForTechnicalSchool($Content, 'S02_2_1_A');
            self::sumBirthYearOrNationalityForTechnicalSchool($Content, 'S02_2_1_U');

            self::sumBirthYearOrNationalityForTechnicalSchool($Content, 'S03_1_1_A');
            self::sumBirthYearOrNationalityForTechnicalSchool($Content, 'S03_1_1_U');
            self::sumBirthYearOrNationalityForTechnicalSchool($Content, 'S03_2_1_A');
            self::sumBirthYearOrNationalityForTechnicalSchool($Content, 'S03_2_1_U');

            self::sumForeignLanguagesForTechnicalSchool($Content, 'S04_1_A');
            self::sumForeignLanguagesForTechnicalSchool($Content, 'S04_1_U');

            self::sumForeignLanguagesForTechnicalSchool($Content, 'S04_2_A');
            self::sumForeignLanguagesForTechnicalSchool($Content, 'S04_2_U');
        }

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
        bool &$currentYear = false
    ) {

        $Date = Term::useService()->getYearStringAsArray();
        //past year as string
        $PastDisplayYear = $Date['PastDisplayYear'];
        //current year as string
        $CurrentDisplayYear = $Date['CurrentDisplayYear'];
        //current year exact year
        $currentYear = $Date['CurrentYear'];
        $tblCurrentYearList = Term::useService()->getYearByName($CurrentDisplayYear);
        if (($temp = Term::useService()->getYearByName($PastDisplayYear))) {
            $tblPastYearList = $temp;
        }

        $Content['SchoolYear']['Current'] = $CurrentDisplayYear;
        $Content['SchoolYear']['Past'] = $PastDisplayYear;
        $Content['Year']['Current'] = $currentYear;
    }

    /**
     * @param $Content
     * @param $tblPastYearList
     * @param TblType $tblKamenzSchoolType
     */
    private static function setRepeatersFromCertificates(&$Content, $tblPastYearList, TblType $tblKamenzSchoolType)
    {
        if ($tblPastYearList) {
            foreach ($tblPastYearList as $tblPastYear) {
                if (($tblGeneratePrepareList = Generate::useService()->getGenerateCertificateAllByYear($tblPastYear))) {
                    foreach ($tblGeneratePrepareList as $tblGenerateCertificate) {
                        if (($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
                            && ($tblCertificateType->getIdentifier() == 'YEAR' || $tblCertificateType->getIdentifier() == 'DIPLOMA')
                            && ($tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate))
                        ) {
                            foreach ($tblPrepareList as $tblPrepare) {
                                if (($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
                                    && ($tblSchoolTypeList = $tblDivisionCourse->getSchoolTypeListFromStudents())
                                    && isset($tblSchoolTypeList[$tblKamenzSchoolType->getId()])
                                    && ($tblPrepareStudentList = Prepare::useService()->getPrepareStudentAllByPrepare($tblPrepare))
                                ) {
                                    foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                                        if (($tblPerson = $tblPrepareStudent->getServiceTblPerson())
                                            && ((($tblPrepareInformationTransfer = Prepare::useService()->getPrepareInformationBy($tblPrepare, $tblPerson, 'Transfer'))
                                                    && (strpos($tblPrepareInformationTransfer->getValue(), 'nicht versetzt') !== false))
                                                || (($tblPrepareInformationIndividualTransfer = Prepare::useService()->getPrepareInformationBy(
                                                        $tblPrepare, $tblPerson, 'IndividualTransfer'
                                                    ))
                                                    && (strpos($tblPrepareInformationIndividualTransfer->getValue(), 'nicht versetzt') !== false))
                                            )
                                        ) {
                                            $level = 0;
                                            $tblCourse = false;
                                            if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblPastYear))) {
                                                $level = $tblStudentEducation->getLevel();
                                                $tblCourse = $tblStudentEducation->getServiceTblCourse();
                                            }

                                            $gender = 'x';
                                            if (($tblCommonGender = $tblPerson->getGender())) {
                                                $gender = $tblCommonGender->getShortName();
                                            }

                                            if ($tblKamenzSchoolType->getName() == 'Mittelschule / Oberschule') {
                                                $course = 'NoCourse';
                                                if (!($level == 5 || $level == 6)) {
                                                    if ($tblCourse) {
                                                        if ($tblCourse->getName() == 'Hauptschule') {
                                                            $course = 'HS';
                                                        } elseif ($tblCourse->getName() == 'Realschule') {
                                                            $course = 'RS';
                                                        }
                                                    }
                                                }

                                                if (isset($Content['C01'][$course]['L' . $level][$gender])) {
                                                    $Content['C01'][$course]['L' . $level][$gender]++;
                                                } else {
                                                    $Content['C01'][$course]['L' . $level][$gender] = 1;
                                                }

                                                if (isset($Content['C01'][$course]['TotalCount'][$gender])) {
                                                    $Content['C01'][$course]['TotalCount'][$gender]++;
                                                } else {
                                                    $Content['C01'][$course]['TotalCount'][$gender] = 1;
                                                }

                                            } else {
                                                if (isset($Content['C01']['L' . $level][$gender])) {
                                                    $Content['C01']['L' . $level][$gender]++;
                                                } else {
                                                    $Content['C01']['L' . $level][$gender] = 1;
                                                }

                                                if (isset($Content['C01']['TotalCount'][$gender])) {
                                                    $Content['C01']['TotalCount'][$gender]++;
                                                } else {
                                                    $Content['C01']['TotalCount'][$gender] = 1;
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
     * F01. Integrierte Schüler mit sonderpädagogischem Förderbedarf im Schuljahr 2016/17 nach
     * Förderschwerpunkten und Klassenstufen
     *
     * @param TblPerson $tblPerson
     * @param int $level
     * @param $Content
     * @param $gender
     * @param $hasMigrationBackground
     * @param $isInPreparationDivisionForMigrants
     * @param string $name
     */
    private static function setStudentFocus(
        TblPerson $tblPerson,
        int $level,
        &$Content,
        $gender,
        $hasMigrationBackground,
        $isInPreparationDivisionForMigrants,
        string $name = 'F01'
    ) {

        if (($tblSupport = Student::useService()->getSupportForReportingByPerson($tblPerson))
            && ($tblSupportFocus = Student::useService()->getSupportPrimaryFocusBySupport($tblSupport))
            && ($tblSupportFocusType = $tblSupportFocus->getTblSupportFocusType())
        ) {

            $text = preg_replace('/[^a-zA-Z]/', '', $tblSupportFocusType->getName());

            /**
             * Schüler
             */
            if (isset($Content[$name][$text]['Student']['L' . $level][$gender])) {
                $Content[$name][$text]['Student']['L' . $level][$gender]++;
            } else {
                $Content[$name][$text]['Student']['L' . $level][$gender] = 1;
            }
            if ($isInPreparationDivisionForMigrants) {
                if (isset($Content[$name][$text]['Student']['IsInPreparationDivisionForMigrants'][$gender])) {
                    $Content[$name][$text]['Student']['IsInPreparationDivisionForMigrants'][$gender]++;
                } else {
                    $Content[$name][$text]['Student']['IsInPreparationDivisionForMigrants'][$gender] = 1;
                }
            }
            if (isset($Content[$name][$text]['Student']['TotalCount'][$gender])) {
                $Content[$name][$text]['Student']['TotalCount'][$gender]++;
            } else {
                $Content[$name][$text]['Student']['TotalCount'][$gender] = 1;
            }
            if (isset($Content[$name]['TotalCount']['Student']['TotalCount'][$gender])) {
                $Content[$name]['TotalCount']['Student']['TotalCount'][$gender]++;
            } else {
                $Content[$name]['TotalCount']['Student']['TotalCount'][$gender] = 1;
            }

            /**
             * Schüler mit Migrationshintergrund
             */
            if ($hasMigrationBackground) {
                if (isset($Content[$name][$text]['HasMigrationBackground']['L' . $level][$gender])) {
                    $Content[$name][$text]['HasMigrationBackground']['L' . $level][$gender]++;
                } else {
                    $Content[$name][$text]['HasMigrationBackground']['L' . $level][$gender] = 1;
                }
                if ($isInPreparationDivisionForMigrants) {
                    if (isset($Content[$name][$text]['HasMigrationBackground']['IsInPreparationDivisionForMigrants'][$gender])) {
                        $Content[$name][$text]['HasMigrationBackground']['IsInPreparationDivisionForMigrants'][$gender]++;
                    } else {
                        $Content[$name][$text]['HasMigrationBackground']['IsInPreparationDivisionForMigrants'][$gender] = 1;
                    }
                }
                if (isset($Content[$name][$text]['HasMigrationBackground']['TotalCount'][$gender])) {
                    $Content[$name][$text]['HasMigrationBackground']['TotalCount'][$gender]++;
                } else {
                    $Content[$name][$text]['HasMigrationBackground']['TotalCount'][$gender] = 1;
                }
                if (isset($Content[$name]['TotalCount']['HasMigrationBackground']['TotalCount'][$gender])) {
                    $Content[$name]['TotalCount']['HasMigrationBackground']['TotalCount'][$gender]++;
                } else {
                    $Content[$name]['TotalCount']['HasMigrationBackground']['TotalCount'][$gender] = 1;
                }
            }

            /**
             * Schüler mit gutachterl. best. Autismus
             */
            if (($tblSpecialList = Student::useService()->getSpecialByPerson($tblPerson))) {

                $hasAutism = false;
                foreach ($tblSpecialList as $tblSpecial) {
                    if (($tblSpecialDisorderTypeList = Student::useService()->getSpecialDisorderTypeAllBySpecial($tblSpecial))) {
                        foreach ($tblSpecialDisorderTypeList as $tblSpecialDisorderType) {
                            if ($tblSpecialDisorderType->getName() == 'Störungen aus dem Autismusspektrum') {
                                $hasAutism = true;
                                break;
                            }
                        }
                    }

                    if ($hasAutism) {
                        break;
                    }
                }

                if ($hasAutism) {
                    if (isset($Content[$name][$text]['Autism']['L' . $level][$gender])) {
                        $Content[$name][$text]['Autism']['L' . $level][$gender]++;
                    } else {
                        $Content[$name][$text]['Autism']['L' . $level][$gender] = 1;
                    }
                    if ($isInPreparationDivisionForMigrants) {
                        if (isset($Content[$name][$text]['Autism']['IsInPreparationDivisionForMigrants'][$gender])) {
                            $Content[$name][$text]['Autism']['IsInPreparationDivisionForMigrants'][$gender]++;
                        } else {
                            $Content[$name][$text]['Autism']['IsInPreparationDivisionForMigrants'][$gender] = 1;
                        }
                    }
                    if (isset($Content[$name][$text]['Autism']['TotalCount'][$gender])) {
                        $Content[$name][$text]['Autism']['TotalCount'][$gender]++;
                    } else {
                        $Content[$name][$text]['Autism']['TotalCount'][$gender] = 1;
                    }
                    if (isset($Content[$name]['TotalCount']['Autism']['TotalCount'][$gender])) {
                        $Content[$name]['TotalCount']['Autism']['TotalCount'][$gender]++;
                    } else {
                        $Content[$name]['TotalCount']['Autism']['TotalCount'][$gender] = 1;
                    }
                }
            }
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param int $level
     * @param $Content
     * @param $gender
     * @param $hasMigrationBackground
     * @param string $name
     */
    private static function setStudentFocusBFS(
        TblPerson $tblPerson,
        int $level,
        &$Content,
        $gender,
        $hasMigrationBackground,
        string $name = 'F01_1'
    ) {

        if (($tblSupport = Student::useService()->getSupportForReportingByPerson($tblPerson))
            && ($tblSupportFocus = Student::useService()->getSupportPrimaryFocusBySupport($tblSupport))
            && ($tblSupportFocusType = $tblSupportFocus->getTblSupportFocusType())
        ) {

            $text = preg_replace('/[^a-zA-Z]/', '', $tblSupportFocusType->getName());

            /**
             * Schüler
             */
            if (isset($Content[$name][$text]['Student']['L' . $level][$gender])) {
                $Content[$name][$text]['Student']['L' . $level][$gender]++;
            } else {
                $Content[$name][$text]['Student']['L' . $level][$gender] = 1;
            }
            if (isset($Content[$name][$text]['Student']['L' . $level]['TotalCount'])) {
                $Content[$name][$text]['Student']['L' . $level]['TotalCount']++;
            } else {
                $Content[$name][$text]['Student']['L' . $level]['TotalCount'] = 1;
            }
            if (isset($Content[$name][$text]['Student']['TotalCount'][$gender])) {
                $Content[$name][$text]['Student']['TotalCount'][$gender]++;
            } else {
                $Content[$name][$text]['Student']['TotalCount'][$gender] = 1;
            }
            if (isset($Content[$name]['TotalCount']['Student']['TotalCount'][$gender])) {
                $Content[$name]['TotalCount']['Student']['TotalCount'][$gender]++;
            } else {
                $Content[$name]['TotalCount']['Student']['TotalCount'][$gender] = 1;
            }

            /**
             * Schüler mit Migrationshintergrund
             */
            if ($hasMigrationBackground) {
                if (isset($Content[$name][$text]['HasMigrationBackground']['L' . $level]['TotalCount'])) {
                    $Content[$name][$text]['HasMigrationBackground']['L' . $level]['TotalCount']++;
                } else {
                    $Content[$name][$text]['HasMigrationBackground']['L' . $level]['TotalCount'] = 1;
                }
                if (isset($Content[$name][$text]['HasMigrationBackground']['TotalCount']['TotalCount'])) {
                    $Content[$name][$text]['HasMigrationBackground']['TotalCount']['TotalCount']++;
                } else {
                    $Content[$name][$text]['HasMigrationBackground']['TotalCount']['TotalCount'] = 1;
                }
                if (isset($Content[$name]['TotalCount']['HasMigrationBackground']['L' . $level]['TotalCount'])) {
                    $Content[$name]['TotalCount']['HasMigrationBackground']['L' . $level]['TotalCount']++;
                } else {
                    $Content[$name]['TotalCount']['HasMigrationBackground']['L' . $level]['TotalCount'] = 1;
                }
                if (isset($Content[$name]['TotalCount']['HasMigrationBackground']['TotalCount']['TotalCount'])) {
                    $Content[$name]['TotalCount']['HasMigrationBackground']['TotalCount']['TotalCount']++;
                } else {
                    $Content[$name]['TotalCount']['HasMigrationBackground']['TotalCount']['TotalCount'] = 1;
                }
            }

            /**
             * Schüler mit gutachterl. best. Autismus
             */
            if (($tblSpecialList = Student::useService()->getSpecialByPerson($tblPerson))) {

                $hasAutism = false;
                foreach ($tblSpecialList as $tblSpecial) {
                    if (($tblSpecialDisorderTypeList = Student::useService()->getSpecialDisorderTypeAllBySpecial($tblSpecial))) {
                        foreach ($tblSpecialDisorderTypeList as $tblSpecialDisorderType) {
                            if ($tblSpecialDisorderType->getName() == 'Störungen aus dem Autismusspektrum') {
                                $hasAutism = true;
                                break;
                            }
                        }
                    }

                    if ($hasAutism) {
                        break;
                    }
                }

                if ($hasAutism) {
                    if (isset($Content[$name][$text]['Autism']['L' . $level]['TotalCount'])) {
                        $Content[$name][$text]['Autism']['L' . $level]['TotalCount']++;
                    } else {
                        $Content[$name][$text]['Autism']['L' . $level]['TotalCount'] = 1;
                    }
                    if (isset($Content[$name][$text]['Autism']['TotalCount']['TotalCount'])) {
                        $Content[$name][$text]['Autism']['TotalCount']['TotalCount']++;
                    } else {
                        $Content[$name][$text]['Autism']['TotalCount']['TotalCount'] = 1;
                    }
                    if (isset($Content[$name]['TotalCount']['Autism']['L' . $level]['TotalCount'])) {
                        $Content[$name]['TotalCount']['Autism']['L' . $level]['TotalCount']++;
                    } else {
                        $Content[$name]['TotalCount']['Autism']['L' . $level]['TotalCount'] = 1;
                    }
                    if (isset($Content[$name]['TotalCount']['Autism']['TotalCount']['TotalCount'])) {
                        $Content[$name]['TotalCount']['Autism']['TotalCount']['TotalCount']++;
                    } else {
                        $Content[$name]['TotalCount']['Autism']['TotalCount']['TotalCount'] = 1;
                    }
                }
            }
        }
    }

    /**
     * @param TblStudent $tblStudent
     * @param int $level
     * @param TblType $tblType
     * @param $Content
     * @param $gender
     * @param $isInPreparationDivisionForMigrants
     * @param $countForeignSubjectArray
     * @param $countSecondForeignSubjectArray
     */
    private static function countForeignLanguages(
        TblStudent $tblStudent,
        int $level,
        TblType $tblType,
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
            $countForeignSubjectsByStudent = 0;
            if ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent, $tblStudentSubjectType)) {
                foreach ($tblStudentSubjectList as $tblStudentSubject) {
                    if (($tblSubject = $tblStudentSubject->getServiceTblSubject())
                        && ($tblStudentSubjectRanking = $tblStudentSubject->getTblStudentSubjectRanking())
                    ) {
                        // #SSW-1596 abgeschlossene und noch nicht begonnene Fremdsprachen ignorieren
                        if (($LevelFrom = $tblStudentSubject->getLevelFrom())
                            && $LevelFrom > $level
                        ) {
                            continue;
                        }
                        if (($LevelTill = $tblStudentSubject->getLevelTill())
                            && $LevelTill < $level
                        ) {
                            continue;
                        }

                        $countForeignSubjectsByStudent++;

                        if ($tblType->getShortName() == 'OS') {
                            // bei Mittelschule nur 1. Fremdsprache
                            if ($tblStudentSubjectRanking->getIdentifier() == 1) {
                                if (isset($countForeignSubjectArray[$tblSubject->getName()][$level])) {
                                    $countForeignSubjectArray[$tblSubject->getName()][$level]++;
                                } else {
                                    $countForeignSubjectArray[$tblSubject->getName()][$level] = 1;
                                }
                            }
                        } else {
                            if ($level < 11) {
                                if (isset($countForeignSubjectArray[$tblSubject->getName()][$level])) {
                                    $countForeignSubjectArray[$tblSubject->getName()][$level]++;
                                } else {
                                    $countForeignSubjectArray[$tblSubject->getName()][$level] = 1;
                                }
                            }
                        }

                        if ($tblStudentSubjectRanking->getIdentifier() == 2) {
                            /**
                             * E11. Schüler in der zweiten FREMDSPRACHE - abschlussorientiert im Schuljahr nach Klassenstufen
                             */
                            if ($gender) {
                                if (isset($countSecondForeignSubjectArray[$tblSubject->getAcronym()][$level][$gender])) {
                                    $countSecondForeignSubjectArray[$tblSubject->getAcronym()][$level][$gender]++;
                                } else {
                                    $countSecondForeignSubjectArray[$tblSubject->getAcronym()][$level][$gender] = 1;
                                }
                            }
                        }
                    }
                }
            }

            /**
             * E04.1 Schüler im Schuljahr nach der Anzahl der derzeit erlernten Fremdsprachen und Klassenstufen
             */
            if ($countForeignSubjectsByStudent > 4) {
                $countForeignSubjectsByStudent = 4;
            }
            if (isset($Content['E04_1']['F' . $countForeignSubjectsByStudent]['L' . $level])) {
                $Content['E04_1']['F' . $countForeignSubjectsByStudent]['L' . $level]++;
            } else {
                $Content['E04_1']['F' . $countForeignSubjectsByStudent]['L' . $level] = 1;
            }
            if (isset($Content['E04_1']['F' . $countForeignSubjectsByStudent]['TotalCount'])) {
                $Content['E04_1']['F' . $countForeignSubjectsByStudent]['TotalCount']++;
            } else {
                $Content['E04_1']['F' . $countForeignSubjectsByStudent]['TotalCount'] = 1;
            }
            if (isset($Content['E04_1']['TotalCount']['L' . $level])) {
                $Content['E04_1']['TotalCount']['L' . $level]++;
            } else {
                $Content['E04_1']['TotalCount']['L' . $level] = 1;
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
     * @param TblPerson $tblPerson
     *
     * @return array
     */
    private static function getForeignLanguages(TblPerson $tblPerson): array
    {
        $subjects = array();
        if (($tblStudent = $tblPerson->getStudent())
            && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent, $tblStudentSubjectType))
        ) {
            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                if (($tblSubject = $tblStudentSubject->getServiceTblSubject())
                    && ($tblStudentSubjectRanking = $tblStudentSubject->getTblStudentSubjectRanking())
                ) {
                    $subjects[$tblStudentSubjectRanking->getIdentifier()] = $tblSubject->getAcronym();
                }
            }
        }

        return $subjects;
    }

    /**
     * @param TblPerson $tblPerson
     * @param int $level
     * @param $countForeignSubjectMatrix
     *
     * @return array
     */
    private static function countForeignLanguagesMatrix(
        TblPerson $tblPerson,
        int $level,
        $countForeignSubjectMatrix
    ): array {
        /**
         * E15. Schüler in Sprachenfolgen im Schuljahr nach Klassenstufen
         */
        $subjects = self::getForeignLanguages($tblPerson);
        if (!empty($subjects)) {
            $identifier = '';
            ksort($subjects);
            foreach ($subjects as $ranking => $acronym) {
                $identifier .= $acronym;
            }

            $count = count($subjects);
            if (!isset($countForeignSubjectMatrix[$count][$identifier])) {
                foreach ($subjects as $ranking => $acronym) {
                    $countForeignSubjectMatrix[$count][$identifier]['Subjects'][$ranking] = $acronym;
                }
            }

            if (isset($countForeignSubjectMatrix[$count][$identifier]['Levels'][$level])) {
                $countForeignSubjectMatrix[$count][$identifier]['Levels'][$level]++;
            } else {
                $countForeignSubjectMatrix[$count][$identifier]['Levels'][$level] = 1;
            }
        }

        return $countForeignSubjectMatrix;
    }

    /**
     * @param $Content
     * @param $countForeignSubjectArray
     * @param $countSecondForeignSubjectArray
     * @param TblType $tblSchoolType
     */
    private static function setForeignLanguages(
        &$Content,
        $countForeignSubjectArray,
        $countSecondForeignSubjectArray,
        TblType $tblSchoolType
    ) {
        /**
         * E04 Schüler mit der ersten Fremdsprache im Schuljahr nach Klassenstufen
         */
        ksort($countForeignSubjectArray);
        $count = 0;
        foreach ($countForeignSubjectArray as $subjectName => $levelArray) {
            $Content['E04']['S' . $count]['TotalCount'] = 0;
            $Content['E04']['S' . $count]['SubjectName'] = $subjectName;
            foreach ($levelArray as $level => $value) {
                $Content['E04']['S' . $count]['L' . $level] = $value;
                $Content['E04']['S' . $count]['TotalCount'] += $value;
            }

            $count++;
        }

        if ($tblSchoolType->getName() == 'Mittelschule / Oberschule') {
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
    }

    /**
     * @param $Content
     * @param $countForeignSubjectMatrix
     */
    private static function setForeignLanguagesMatrix(
        &$Content,
        $countForeignSubjectMatrix
    ) {
        /**
         * E15. Schüler in Sprachenfolgen im Schuljahr nach Klassenstufen
         */
        ksort($countForeignSubjectMatrix);
        $count = 0;
        foreach ($countForeignSubjectMatrix as $counter => $identifierArray) {
            ksort($identifierArray);
            foreach ($identifierArray as $identifier => $array) {
                if (isset($array['Subjects'])) {
                    foreach ($array['Subjects'] as $ranking => $acronym) {
                        $Content['E15']['S' . $count]['N' . $ranking] =
                            ($tblSubject = Subject::useService()->getSubjectByAcronym($acronym))
                                ? $tblSubject->getName()
                                : '';
                    }
                }
                if (isset($array['Levels'])) {
                    foreach ($array['Levels'] as $level => $value) {
                        $Content['E15']['S' . $count]['L' . $level] = $value;

                        if (isset($Content['E15']['TotalCount']['L' . $level])) {
                            $Content['E15']['TotalCount']['L' . $level] += $value;
                        } else {
                            $Content['E15']['TotalCount']['L' . $level] = $value;
                        }
                    }
                }

                $count++;
            }
        }
    }

    /**
     * E05 Schüler im Ethik- bzw. Religionsunterricht im Schuljahr nach Klassenstufen
     *
     * @param TblStudent $tblStudent
     * @param int $level
     * @param $countReligionArray
     *
     * @return array
     */
    private static function countReligion(
        TblStudent $tblStudent,
        int $level,
        $countReligionArray
    ): array {

        if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('RELIGION'))
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent, $tblStudentSubjectType))
        ) {
            /** @var TblStudentSubject $tblStudentSubject */
            if (($tblStudentSubject = reset($tblStudentSubjectList))
                && ($tblSubject = $tblStudentSubject->getServiceTblSubject())
            ) {
                if (isset($countReligionArray[$tblSubject->getAcronym()][$level])) {
                    $countReligionArray[$tblSubject->getAcronym()][$level]++;
                } else {
                    $countReligionArray[$tblSubject->getAcronym()][$level] = 1;
                }
            }

            return $countReligionArray;
        }

        if (isset($countReligionArray['ZZ_Keine_Teilnahme'][$level])) {
            $countReligionArray['ZZ_Keine_Teilnahme'][$level]++;
        } else {
            $countReligionArray['ZZ_Keine_Teilnahme'][$level] = 1;
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
                if (intval($level) < 11) {
                    $Content['E05']['S' . $count]['L' . $level] = $value;
                    $Content['E05']['S' . $count]['TotalCount'] += $value;
                    if (isset($Content['E05']['TotalCount']['L' . $level])) {
                        $Content['E05']['TotalCount']['L' . $level] += $value;
                    } else {
                        $Content['E05']['TotalCount']['L' . $level] = $value;
                    }
                }
            }

            $count++;
        }
    }

    /**
     * E08. Wiederholer im Schuljahr 2016/17 nach Klassenstufen
     *
     * @param TblPerson $tblPerson
     * @param int $level
     * @param array $tblPastYearList
     * @param $Content
     * @param $gender
     * @param TblCourse|null $tblCourse
     * @param TblType $tblSchoolTypeKamenz
     */
    private static function setRepeatersOs(
        TblPerson $tblPerson,
        int $level,
        array $tblPastYearList,
        &$Content,
        $gender,
        ?TblCourse $tblCourse,
        TblType $tblSchoolTypeKamenz
    ) {
        foreach ($tblPastYearList as $tblPastYear) {
            if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblPastYear))
                && $level == $tblStudentEducation->getLevel()
                && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                && $tblSchoolType->getId() == $tblSchoolTypeKamenz->getId()
            ) {
                $course = 'WithoutCourse';
                if ($level > 6 && $tblCourse) {
                    if ($tblCourse->getName() == 'Hauptschule') {
                        $course = 'HS';
                    } elseif ($tblCourse->getName() == 'Realschule') {
                        $course = 'RS';
                    }
                }

                if ($gender) {
                    if (isset($Content['E08'][$course]['L' . $level][$gender])) {
                        $Content['E08'][$course]['L' . $level][$gender]++;
                    } else {
                        $Content['E08'][$course]['L' . $level][$gender] = 1;
                    }

                    if (isset($Content['E08'][$course]['TotalCount'][$gender])) {
                        $Content['E08'][$course]['TotalCount'][$gender]++;
                    } else {
                        $Content['E08'][$course]['TotalCount'][$gender] = 1;
                    }
                }

                break;
            }
        }
    }

    /**
     * E08. Wiederholer im Schuljahr 2016/17 nach Klassenstufen
     *
     * @param TblPerson $tblPerson
     * @param int $level
     * @param array $tblPastYearList
     * @param $Content
     * @param $gender
     * @param TblType $tblSchoolTypeKamenz
     */
    private static function setRepeatersGym(
        TblPerson $tblPerson,
        int $level,
        array $tblPastYearList,
        &$Content,
        $gender,
        TblType $tblSchoolTypeKamenz
    ) {
        foreach ($tblPastYearList as $tblPastYear) {
            if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblPastYear))
                && $level == $tblStudentEducation->getLevel()
                && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                && $tblSchoolType->getId() == $tblSchoolTypeKamenz->getId()
            ) {
                if ($gender) {
                    if (isset($Content['E08']['L' . $level][$gender])) {
                        $Content['E08']['L' . $level][$gender]++;
                    } else {
                        $Content['E08']['L' . $level][$gender] = 1;
                    }

                    if (isset($Content['E08']['TotalCount'][$gender])) {
                        $Content['E08']['TotalCount'][$gender]++;
                    } else {
                        $Content['E08']['TotalCount'][$gender] = 1;
                    }
                }

                break;
            }
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param int $level
     * @param array $tblPastYearList
     * @param TblType $tblSchoolTypeKamenz
     *
     * @return bool
     */
    private static function hasRepeaters(
        TblPerson $tblPerson,
        int $level,
        array $tblPastYearList,
        TblType $tblSchoolTypeKamenz
    ): bool {
        foreach ($tblPastYearList as $tblPastYear) {
            if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblPastYear))
                && $level == $tblStudentEducation->getLevel()
                && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                && $tblSchoolType->getId() == $tblSchoolTypeKamenz->getId()
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * E12 Schüler im NEIGUNGSKURSBEREICH im Schuljahr nach Klassenstufen
     *
     * @param TblStudent $tblStudent
     * @param int $level
     * @param $gender
     * @param $countOrientationArray
     *
     * @return array
     */
    private static function countOrientation(
        TblStudent $tblStudent,
        int $level,
        $gender,
        $countOrientationArray
    ): array {

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
                    $name = '';
                    if (($startPos = strpos($tblSubject->getName(), '(')) !== false
                        && ($endPos = strpos($tblSubject->getName(), ')')) !== false
                    ) {
                        $name = substr($tblSubject->getName(), $startPos + 1, $endPos - ($startPos + 1));
                    }

                    if ($name != '') {
                        if (isset($countOrientationArray[$name][$level][$gender])) {
                            $countOrientationArray[$name][$level][$gender]++;
                        } else {
                            $countOrientationArray[$name][$level][$gender] = 1;
                        }
                    }
                }
            }
        }

        return $countOrientationArray;
    }

    /**
     * @param TblStudent $tblStudent
     * @param int $level
     * @param $gender
     * @param $countProfileArray
     *
     * @return array
     */
    private static function countProfile(
        TblStudent $tblStudent,
        int $level,
        $gender,
        $countProfileArray
    ): array {
        if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE'))
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent, $tblStudentSubjectType))
        ) {
            /** @var TblStudentSubject $tblStudentSubject */
            if (($tblStudentSubject = reset($tblStudentSubjectList))
                && ($tblSubject = $tblStudentSubject->getServiceTblSubject())
            ) {
                if ($level < 11 && $gender) {
                    if (isset($countProfileArray[$tblSubject->getAcronym()][$level][$gender])) {
                        $countProfileArray[$tblSubject->getAcronym()][$level][$gender]++;
                    } else {
                        $countProfileArray[$tblSubject->getAcronym()][$level][$gender] = 1;
                    }
                }
            }
        }

        return $countProfileArray;
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
        foreach ($countOrientationArray as $name => $levelArray) {
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
     * @param $Content
     * @param $countProfileArray
     */
    private static function setProfile(
        &$Content,
        $countProfileArray
    ) {
        ksort($countProfileArray);
        $countLanguageProfile = -1;
        $countOthers = -1;
        foreach ($countProfileArray as $acronym => $levelArray) {
            if (($tblSubject = Subject::useService()->getSubjectByAcronym($acronym))) {
                $name = $tblSubject->getName();
                if (strpos(strtolower($name), 'sprach') !== false) {
                    $countLanguageProfile++;
                    $count = $countLanguageProfile;
                    $section = 'E11';
                } else {
                    $countOthers++;
                    $count = $countOthers;
                    $section = 'E12';
                }

                $Content[$section]['S' . $count]['SubjectName'] = $name;
                foreach ($levelArray as $level => $genderArray) {
                    foreach ($genderArray as $gender => $value) {

                        $Content[$section]['S' . $count]['L' . $level][$gender] = $value;

                        if (isset($Content[$section]['TotalCount']['L' . $level][$gender])) {
                            $Content[$section]['TotalCount']['L' . $level][$gender] += $value;
                        } else {
                            $Content[$section]['TotalCount']['L' . $level][$gender] = $value;
                        }

                        if (isset($Content[$section]['S' . $count]['TotalCount'][$gender])) {
                            $Content[$section]['S' . $count]['TotalCount'][$gender] += $value;
                        } else {
                            $Content[$section]['S' . $count]['TotalCount'][$gender] = $value;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param int $level
     * @param $gender
     * @param $hasMigrationBackground
     * @param $isInPreparationDivisionForMigrants
     * @param $birthDay
     * @param $countArray
     * @param $countMigrantsArray
     * @param $countMigrantsNationalityArray
     *
     * @return bool|string
     */
    private static function countStudentLevels(
        TblPerson $tblPerson,
        int $level,
        &$gender,
        $hasMigrationBackground,
        $isInPreparationDivisionForMigrants,
        &$birthDay,
        &$countArray,
        &$countMigrantsArray,
        &$countMigrantsNationalityArray
    ) {
        $nationality = false;
        /**
         * E02 Schüler im Schuljahr 2016/2017 nach Geburtsjahren und Klassenstufen
         */
        if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
            if (($tblCommonInformation = $tblCommon->getTblCommonInformation())) {
                $nationality = $tblCommonInformation->getNationality();
            }

            if (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())
                && ($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())
            ) {

                $gender = $tblCommonGender->getShortName();
                // die Kamenzstatistik der allgemeinbildenden Schulen unterstützt aktuell nur männlich und weiblich
                if ($gender != 'm' && $gender != 'w') {
                    $gender = false;
                    return $nationality;
                }

                if (($birthDay = $tblCommonBirthDates->getBirthday())) {
                    $birthDayDate = new DateTime($birthDay);
                    if ($birthDayDate) {
                        $birthYear = $birthDayDate->format('Y');

                        if (isset($countArray[$birthYear][$level][$gender])) {
                            $countArray[$birthYear][$level][$gender]++;
                        } else {
                            $countArray[$birthYear][$level][$gender] = 1;
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
                            if (isset($countMigrantsArray[$birthYear][$level][$gender])) {
                                $countMigrantsArray[$birthYear][$level][$gender]++;
                            } else {
                                $countMigrantsArray[$birthYear][$level][$gender] = 1;
                            }

                            /**
                             * E03. Schüler mit Migrationshintergrund im Schuljahr 2016/17 nach dem Land der Staatsangehörigkeit und Klassenstufen
                             */
                            if ($nationality) {
                                if (isset($countMigrantsNationalityArray[$nationality][$level][$gender])) {
                                    $countMigrantsNationalityArray[$nationality][$level][$gender]++;
                                } else {
                                    $countMigrantsNationalityArray[$nationality][$level][$gender] = 1;
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

        return $nationality;
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
         * E02 Schüler im Schuljahr 2016/2017 nach Geburtsjahren und Klassenstufen
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
     * @param $countDivisionByLevelArray
     */
    private static function setDivisionByLevel(
        &$Content,
        $countDivisionByLevelArray
    ) {
        /**
         * Zusatz Klassen für E01. Schüler und Klassen im Schuljahr 2022/23 nach Klassenstufen
         */
        foreach ($countDivisionByLevelArray as $level => $divisionArray) {
            $value = count($divisionArray);
            $Content['E01']['Division']['L' . $level] = $value;

            if (isset($Content['E01']['Division']['TotalCount'])) {
                $Content['E01']['Division']['TotalCount'] += $value;
            } else {
                $Content['E01']['Division']['TotalCount'] = $value;
            }
        }
    }

    /**
     * @param $Content
     * @param TblPerson $tblPerson
     * @param TblStudent $tblStudent
     * @param int $level
     * @param array $tblPastYearList
     * @param $gender
     * @param $birthDay
     * @param $year
     * @param TblType $tblSchoolTypeKamenz
     */
    private static function setNewSchoolStarter(
        &$Content,
        TblPerson $tblPerson,
        TblStudent $tblStudent,
        int $level,
        array $tblPastYearList,
        $gender,
        $birthDay,
        $year,
        TblType $tblSchoolTypeKamenz
    ) {
        if ($level == 1) {
            if (!self::hasRepeaters($tblPerson, $level, $tblPastYearList, $tblSchoolTypeKamenz)) {
                if (isset($Content['D01']['NewSchoolStarter'][$gender])) {
                    $Content['D01']['NewSchoolStarter'][$gender]++;
                } else {
                    $Content['D01']['NewSchoolStarter'][$gender] = 1;
                }

                if (($tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE'))
                    && ($tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent, $tblStudentTransferType))
                    && ($tblArriveCompany = $tblStudentTransfer->getServiceTblCompany())
                    && ($tblGroup = Group::useService()->getGroupByMetaTable('NURSERY'))
                    && Group::useService()->existsGroupCompany($tblGroup, $tblArriveCompany)
                ) {
                    if (isset($Content['D01']['Nursery'][$gender])) {
                        $Content['D01']['Nursery'][$gender]++;
                    } else {
                        $Content['D01']['Nursery'][$gender] = 1;
                    }
                }

                if (($tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ENROLLMENT'))
                    && ($tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent, $tblStudentTransferType))
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
                            $date = new DateTime($tblStudent->getSchoolAttendanceStartDate());
                        } elseif ($birthDay) {
                            $date = new DateTime($birthDay);
                            $date->add(new DateInterval('P6Y'));
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

    /**
     * @param TblPerson $tblPerson
     * @param TblYear[] $tblPastYearList
     *
     * @return bool
     */
    private static function isNewSchoolStarterForTechnicalSchool(
        TblPerson $tblPerson,
        array $tblPastYearList
    ): bool {
        // Neuanfänger sind alle Schüler die im vergangenen Schuljahr noch nicht da waren
        foreach ($tblPastYearList as $tblPastYear) {
            if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblPastYear))
                && ($tblStudentEducation->getTblDivision() || $tblStudentEducation->getTblCoreGroup())
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * N01, N02
     *
     * @param $Content
     * @param string $name
     * @param $schoolDiploma
     * @param $schoolType
     * @param string $support
     * @param TblCommonGender $tblCommonGender
     * @param int $level
     */
    private static function setNewSchoolStarterDiplomaForTechnicalSchool(
        &$Content,
        string $name,
        $schoolDiploma,
        $schoolType,
        string $support,
        TblCommonGender $tblCommonGender,
        int $level
    ) {
        $gender = $tblCommonGender->getName();
        $genderShort = $tblCommonGender->getShortName();
        /**
         * Neuanfänger im Ausbildungsstatus Umschüler im Vollzeitunterricht im Schuljahr 2020/2021 nach allgemeinbildenden
         * Abschlüssen, Schularten, Förderschwerpunkten und Klassenstufen
         */
        if ($schoolDiploma && $schoolType) {
            if (isset($Content[$name]['Temp'][$schoolDiploma . '_' . $schoolType . '_' . $support . '_' . $gender]['L' . $level])) {
                $Content[$name]['Temp'][$schoolDiploma . '_' . $schoolType . '_' . $support . '_' . $gender]['L' . $level]++;
            } else {
                $Content[$name]['Temp'][$schoolDiploma . '_' . $schoolType . '_' . $support . '_' . $gender]['L' . $level] = 1;
            }
            if (isset($Content[$name]['Temp'][$schoolDiploma . '_' . $schoolType . '_' . $support . '_' . $gender]['TotalCount'])) {
                $Content[$name]['Temp'][$schoolDiploma . '_' . $schoolType . '_' . $support . '_' . $gender]['TotalCount']++;
            } else {
                $Content[$name]['Temp'][$schoolDiploma . '_' . $schoolType . '_' . $support . '_' . $gender]['TotalCount'] = 1;
            }

            if (isset($Content[$name]['TotalCount']['L' . $level][$genderShort])) {
                $Content[$name]['TotalCount']['L' . $level][$genderShort]++;
            } else {
                $Content[$name]['TotalCount']['L' . $level][$genderShort] = 1;
            }
            if (isset($Content[$name]['TotalCount']['L' . $level]['TotalCount'])) {
                $Content[$name]['TotalCount']['L' . $level]['TotalCount']++;
            } else {
                $Content[$name]['TotalCount']['L' . $level]['TotalCount'] = 1;
            }
        }
    }

    /**
     * N01, N02
     * @param $Content
     * @param string $name
     */
    private static function sumNewSchoolStarterDiplomaForTechnicalSchool(
        &$Content,
        string $name = 'N01'
    ) {
        if (isset($Content[$name]['Temp'])) {
            $i = 0;
            foreach ($Content[$name]['Temp'] as $key => $row) {
                $array = explode('_', $key);
                $Content[$name]['R' . $i] = array(
                    'Diploma' => $array[0],
                    'SchoolType' => $array[1],
                    'Support' => $array[2],
                    'Gender' => $array[3]
                );
                foreach ($row as $subKey => $subRow) {
                    $Content[$name]['R' . $i][$subKey] = $subRow;
                }
                $i++;
            }
        }
    }

    /**
     * N03, N04
     *
     * @param $Content
     * @param string $name
     * @param $item
     * @param TblCommonGender $tblCommonGender
     * @param int $level
     */
    private static function setBirthYearOrNationalityForTechnicalSchool(
        &$Content,
        string $name,
        $item,
        TblCommonGender $tblCommonGender,
        int $level
    ) {
        $gender = $tblCommonGender->getName();
        $genderShort = $tblCommonGender->getShortName();
        /**
         * N03-1-A. Neuanfänger im Ausbildungsstatus Auszubildende/Schüler im Vollzeitunterricht im Schuljahr 2020/2021
         * nach Geburtsjahren und Klassenstufen
         */
        if (isset($Content[$name]['Temp'][$item . '_' . $gender]['L' . $level])) {
            $Content[$name]['Temp'][$item . '_' . $gender]['L' . $level]++;
        } else {
            $Content[$name]['Temp'][$item . '_' . $gender]['L' . $level] = 1;
        }
        if (isset($Content[$name]['Temp'][$item . '_' . $gender]['TotalCount'])) {
            $Content[$name]['Temp'][$item . '_' . $gender]['TotalCount']++;
        } else {
            $Content[$name]['Temp'][$item . '_' . $gender]['TotalCount'] = 1;
        }

        if (isset($Content[$name]['TotalCount']['L' . $level][$genderShort])) {
            $Content[$name]['TotalCount']['L' . $level][$genderShort]++;
        } else {
            $Content[$name]['TotalCount']['L' . $level][$genderShort] = 1;
        }
        if (isset($Content[$name]['TotalCount']['L' . $level]['TotalCount'])) {
            $Content[$name]['TotalCount']['L' . $level]['TotalCount']++;
        } else {
            $Content[$name]['TotalCount']['L' . $level]['TotalCount'] = 1;
        }
    }

    /**
     * N03, N04
     *
     * @param $Content
     * @param string $name
     */
    private static function sumBirthYearOrNationalityForTechnicalSchool(
        &$Content,
        string $name
    ) {
        if (isset($Content[$name]['Temp'])) {
            $i = 0;
            ksort($Content[$name]['Temp']);
            foreach ($Content[$name]['Temp'] as $key => $row) {
                $array = explode('_', $key);
                $Content[$name]['R' . $i] = array(
                    'Name' => $array[0],
                    'Gender' => $array[1],
                );
                foreach ($row as $subKey => $subRow) {
                    $Content[$name]['R' . $i][$subKey] = $subRow;
                }
                $i++;
            }
        }
    }

    /**
     * N05, S01
     *
     * @param $Content
     * @param string $name
     * @param $course
     * @param $time
     * @param $support
     * @param TblCommonGender $tblCommonGender
     * @param int $level
     */
    private static function setCourseForTechnicalSchool(
        &$Content,
        string $name,
        $course,
        $time,
        $support,
        TblCommonGender $tblCommonGender,
        int $level
    ) {
        $gender = $tblCommonGender->getName();
        $genderShort = $tblCommonGender->getShortName();
        /**
         * N05-1-A. Neuanfänger im Ausbildungsstatus Auszubildende/Schüler im Vollzeitunterricht im Schuljahr 2020/2021
         * nach Bildungsgängen, planmäßiger Ausbildungsdauer, Förderschwerpunkten und Klassenstufen
         */
        if ($course && $time) {
            if (isset($Content[$name]['Temp'][$course . '_' . $time . '_' . $support . '_' . $gender]['L' . $level])) {
                $Content[$name]['Temp'][$course . '_' . $time . '_' . $support . '_' . $gender]['L' . $level]++;
            } else {
                $Content[$name]['Temp'][$course . '_' . $time . '_' . $support . '_' . $gender]['L' . $level] = 1;
            }
            if (isset($Content[$name]['Temp'][$course . '_' . $time . '_' . $support . '_' . $gender]['TotalCount'])) {
                $Content[$name]['Temp'][$course . '_' . $time . '_' . $support . '_' . $gender]['TotalCount']++;
            } else {
                $Content[$name]['Temp'][$course . '_' . $time . '_' . $support . '_' . $gender]['TotalCount'] = 1;
            }

            if (isset($Content[$name]['TotalCount']['L' . $level][$genderShort])) {
                $Content[$name]['TotalCount']['L' . $level][$genderShort]++;
            } else {
                $Content[$name]['TotalCount']['L' . $level][$genderShort] = 1;
            }
            if (isset($Content[$name]['TotalCount']['L' . $level]['TotalCount'])) {
                $Content[$name]['TotalCount']['L' . $level]['TotalCount']++;
            } else {
                $Content[$name]['TotalCount']['L' . $level]['TotalCount'] = 1;
            }
        }
    }

    /**
     * N05, S01
     *
     * @param $Content
     * @param string $name
     */
    private static function sumCourseForTechnicalSchool(
        &$Content,
        string $name = 'N05'
    ) {
        if (isset($Content[$name]['Temp'])) {
            $i = 0;
            foreach ($Content[$name]['Temp'] as $key => $row) {
                $array = explode('_', $key);
                $Content[$name]['R' . $i] = array(
                    'Course' => $array[0],
                    'Time' => $array[1],
                    'Support' => $array[2],
                    'Gender' => $array[3]
                );
                foreach ($row as $subKey => $subRow) {
                    $Content[$name]['R' . $i][$subKey] = $subRow;
                }
                $i++;
            }
        }
    }

    /**
     * @param $Content
     * @param TblPerson $tblPerson
     * @param int $level
     * @param array $tblPastYearList
     * @param $gender
     * @param $isInPreparationDivisionForMigrants
     * @param TblType $tblSchoolType
     */
    private static function setDivisionStudents(
        &$Content,
        TblPerson $tblPerson,
        int $level,
        array $tblPastYearList,
        $gender,
        $isInPreparationDivisionForMigrants,
        TblType $tblSchoolType
    ) {
        if ($gender) {
            if (isset($Content['E01']['Student']['L' . $level][$gender])) {
                $Content['E01']['Student']['L' . $level][$gender]++;
            } else {
                $Content['E01']['Student']['L' . $level][$gender] = 1;
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

            if ($tblSchoolType->getName() == 'Grundschule') {
                /**
                 * E07
                 */
                if ($level == 1
                    && !self::hasRepeaters($tblPerson, $level, $tblPastYearList, $tblSchoolType)
                ) {
                    $identifier = 'NewSchoolStarter';
                } else {
                    $identifier = 'PrimarySchool';
                }

                if (isset($Content['E07'][$identifier]['L' . $level][$gender])) {
                    $Content['E07'][$identifier]['L' . $level][$gender]++;
                } else {
                    $Content['E07'][$identifier]['L' . $level][$gender] = 1;
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
                if (isset($Content['E07'][$identifier]['L' . $level][$gender])) {
                    $Content['E07'][$identifier]['L' . $level][$gender]++;
                } else {
                    $Content['E07'][$identifier]['L' . $level][$gender] = 1;
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
    }

    /**
     * K01. Klassen im Schuljahr 2019/2020 nach Zeitform des Unterrichts, Ausbildungsstatus und Klassenstufen
     *
     * @param $Content
     * @param int $level
     * @param string $lesson
     * @param string $type
     */
    private static function setDivisionStudentsForTechnicalSchool(
        &$Content,
        int $level,
        string $lesson = 'FullTime',
        string $type = 'ChangeStudent'
    ) {
        if (isset($Content['K01'][$lesson][$type]['L' . $level])) {
            $Content['K01'][$lesson][$type]['L' . $level]++;
        } else {
            $Content['K01'][$lesson][$type]['L' . $level] = 1;
        }

        if (isset($Content['K01'][$lesson]['TotalCount']['L' . $level])) {
            $Content['K01'][$lesson]['TotalCount']['L' . $level]++;
        } else {
            $Content['K01'][$lesson]['TotalCount']['L' . $level] = 1;
        }

        if (isset($Content['K01']['TotalCount'][$type])) {
            $Content['K01']['TotalCount'][$type]++;
        } else {
            $Content['K01']['TotalCount'][$type] = 1;
        }
    }

    /**
     * @param $countDivisionStudents
     * @param TblDivisionCourse $tblDivision
     * @param int $level
     * @param $gender
     * @param $isInPreparationDivisionForMigrants
     * @param TblCourse|null $tblCourse
     */
    private static function countDivisionStudentsForSecondarySchool(
        &$countDivisionStudents,
        TblDivisionCourse $tblDivision,
        int $level,
        $gender,
        $isInPreparationDivisionForMigrants,
        ?TblCourse $tblCourse
    ) {
        if ($gender) {
            if ($level < 7) {
                $course = 'NoCourse';
            }
            elseif ($tblCourse == null) {
                $course = 'NoCourse';
            } elseif ($tblCourse->getName() == 'Hauptschule') {
                $course = 'HS';
            } elseif ($tblCourse->getName() == 'Realschule') {
                $course = 'RS';
            } else {
                $course = 'NoCourse';
            }

            if (isset($countDivisionStudents['Division'][$tblDivision->getId()][$level][$course][$gender])) {
                $countDivisionStudents['Division'][$tblDivision->getId()][$level][$course][$gender]++;
            } else {
                $countDivisionStudents['Division'][$tblDivision->getId()][$level][$course][$gender] = 1;
            }

            if ($isInPreparationDivisionForMigrants) {
                if (isset($countDivisionStudents['Migrants'][$level][$course][$gender])) {
                    $countDivisionStudents['Migrants'][$level][$course][$gender]++;
                } else {
                    $countDivisionStudents['Migrants'][$level][$course][$gender] = 1;
                }
            }
        }
    }

    /**
     * @param $Content
     * @param $countDivisionStudents
     */
    private static function setDivisionStudentsForSecondarySchool(
        &$Content,
        $countDivisionStudents
    ) {
        if (isset($countDivisionStudents['Division'])) {
            foreach ($countDivisionStudents['Division'] as $levelArray) {
                foreach ($levelArray as $level => $courseArray) {
                    $courseString = 'Error';
                    if (isset($courseArray['RS']) && isset($courseArray['HS'])) {
                        $isMixed = true;
                    } else {
                        if (isset($courseArray['RS'])) {
                            $courseString = 'RS';
                        } elseif (isset($courseArray['HS'])) {
                            $courseString = 'HS';
                        } elseif (isset($courseArray['NoCourse'])) {
                            $courseString = 'NoCourse';
                        }
                        $isMixed = false;
                    }

                    foreach ($courseArray as $course => $genderArray) {
                        foreach ($genderArray as $gender => $count) {
                            if (isset( $Content['E01'][$course][$isMixed && $course != 'NoCourse'? 'Mixed' : 'Pure']['L' . $level][$gender])) {
                                $Content['E01'][$course][$isMixed && $course != 'NoCourse' ? 'Mixed' : 'Pure']['L' . $level][$gender] += $count;
                            } else {
                                $Content['E01'][$course][$isMixed && $course != 'NoCourse' ? 'Mixed' : 'Pure']['L' . $level][$gender] = $count;
                            }

                            if (isset($Content['E01'][$course][$isMixed && $course != 'NoCourse' ? 'Mixed' : 'Pure']['TotalCount'][$gender])) {
                                $Content['E01'][$course][$isMixed && $course != 'NoCourse' ? 'Mixed' : 'Pure']['TotalCount'][$gender] += $count;
                            } else {
                                $Content['E01'][$course][$isMixed && $course != 'NoCourse' ? 'Mixed' : 'Pure']['TotalCount'][$gender] = $count;
                            }
                        }
                    }

                    if (isset($Content['E01K'][$isMixed ? 'Mixed' : $courseString]['L' . $level])) {
                        $Content['E01K'][$isMixed ? 'Mixed' : $courseString]['L' . $level]++;
                    } else {
                        $Content['E01K'][$isMixed ? 'Mixed' : $courseString]['L' . $level] = 1;
                    }

                    if (isset($Content['E01K'][$isMixed ? 'Mixed' : $courseString]['TotalCount'])) {
                        $Content['E01K'][$isMixed ? 'Mixed' : $courseString]['TotalCount']++;
                    } else {
                        $Content['E01K'][$isMixed ? 'Mixed' : $courseString]['TotalCount'] = 1;
                    }
                }
            }
        }
    }

    /**
     * @param $tblPastYearList
     * @param $Content
     * @param TblType $tblKamenzSchoolType
     */
    private static function setGraduate(
        $tblPastYearList,
        &$Content,
        TblType $tblKamenzSchoolType
    ) {
        if ($tblPastYearList) {
            $countArray = array();
            foreach ($tblPastYearList as $tblPastYear) {
                // Abgangszeugnisse
                if (($tblLeaveStudentList = Prepare::useService()->getLeaveStudentAllByYear($tblPastYear))) {
                    foreach ($tblLeaveStudentList as $tblLeaveStudent) {
                        if ($tblLeaveStudent->isApproved() && $tblLeaveStudent->isPrinted()
                            && ($tblPerson = $tblLeaveStudent->getServiceTblPerson())
                            && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblPastYear))
                            && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                            && $tblSchoolType->getId() == $tblKamenzSchoolType->getId()
                        ) {
                            $levelName = $tblStudentEducation->getLevel();

                            $hasMigrationBackground = false;
                            if (($tblStudent = $tblPerson->getStudent())
                                && $tblStudent->getHasMigrationBackground()
                            ) {
                                $hasMigrationBackground = true;
                            }

                            $identifier = 'Leave';
                            if (($tblCertificate = $tblLeaveStudent->getServiceTblCertificate())
                                && $tblCertificate->getCertificate() == 'GymAbgSekI'
                            ) {
                                if (($tblLeaveInformation = Prepare::useService()->getLeaveInformationBy($tblLeaveStudent, 'EqualGraduation'))) {
                                    if ($tblLeaveInformation->getValue() == GymAbgSekI::COURSE_RS) {
                                        $identifier = 'LeaveRS';
                                    } elseif ($tblLeaveInformation->getValue() == GymAbgSekI::COURSE_HS
                                        || $tblLeaveInformation->getValue() == GymAbgSekI::COURSE_HSQ) {
                                        $identifier = 'LeaveHS';
                                    }
                                }
                            }

                            if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))
                                && (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates()))
                                && ($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())
                                && ($birthDay = $tblCommonBirthDates->getBirthday())
                            ) {

                                $gender = $tblCommonGender->getShortName();

                                $birthDayDate = new DateTime($birthDay);
                                $birthYear = $birthDayDate->format('Y');

                                if (isset($Content['B01'][$identifier]['L' . $levelName][$gender])) {
                                    $Content['B01'][$identifier]['L' . $levelName][$gender]++;
                                } else {
                                    $Content['B01'][$identifier]['L' . $levelName][$gender] = 1;
                                }
                                if (isset($Content['B01'][$identifier]['TotalCount'][$gender])) {
                                    $Content['B01'][$identifier]['TotalCount'][$gender]++;
                                } else {
                                    $Content['B01'][$identifier]['TotalCount'][$gender] = 1;
                                }
                                if (isset($Content['B01']['TotalCount']['L' . $levelName][$gender])) {
                                    $Content['B01']['TotalCount']['L' . $levelName][$gender]++;
                                } else {
                                    $Content['B01']['TotalCount']['L' . $levelName][$gender] = 1;
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
                                    if (isset($countArray[$birthYear][$identifier][$gender])) {
                                        $countArray[$birthYear][$identifier][$gender]++;
                                    } else {
                                        $countArray[$birthYear][$identifier][$gender] = 1;
                                    }
                                }

                                /**
                                 * B01.1
                                 */
                                if ($hasMigrationBackground) {
                                    if (isset($Content['B01_1'][$identifier]['L' . $levelName][$gender])) {
                                        $Content['B01_1'][$identifier]['L' . $levelName][$gender]++;
                                    } else {
                                        $Content['B01_1'][$identifier]['L' . $levelName][$gender] = 1;
                                    }
                                    if (isset($Content['B01_1'][$identifier]['TotalCount'][$gender])) {
                                        $Content['B01_1'][$identifier]['TotalCount'][$gender]++;
                                    } else {
                                        $Content['B01_1'][$identifier]['TotalCount'][$gender] = 1;
                                    }
                                    if (isset($Content['B01_1']['TotalCount']['L' . $levelName][$gender])) {
                                        $Content['B01_1']['TotalCount']['L' . $levelName][$gender]++;
                                    } else {
                                        $Content['B01_1']['TotalCount']['L' . $levelName][$gender] = 1;
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

                // Abschlusszeugnisse
                if (($tblGenerateCertificateList = Generate::useService()->getGenerateCertificateAllByYear($tblPastYear))) {
                    foreach ($tblGenerateCertificateList as $tblGenerateCertificate) {
                        if (($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
                            && $tblCertificateType->getIdentifier() == 'DIPLOMA'
                            && (($tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate)))
                        ) {

                            foreach ($tblPrepareList as $tblPrepare) {
                                if (($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
                                    && ($tblSchoolTypeList = $tblDivisionCourse->getSchoolTypeListFromStudents())
                                    && isset($tblSchoolTypeList[$tblKamenzSchoolType->getId()])
                                    && ($tblPrepareStudentList = Prepare::useService()->getPrepareStudentAllByPrepare($tblPrepare))
                                ) {
                                    foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                                        if (($tblPerson = $tblPrepareStudent->getServiceTblPerson())
                                            && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                                        ) {
                                            $levelName = 0;
                                            if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblPastYear))) {
                                                $levelName = $tblStudentEducation->getLevel();
                                            }
                                            $certificate = $tblCertificate->getCertificate();
                                            if ($certificate == 'MsAbsHsQ') {
                                                $certificate = 'MsAbsHs';
                                            }

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

                                                $gender = $tblCommonGender->getShortName();

                                                $birthDayDate = new DateTime($birthDay);
                                                $birthYear = $birthDayDate->format('Y');

                                                if (isset($Content['B01'][$certificate]['L' . $levelName][$gender])) {
                                                    $Content['B01'][$certificate]['L' . $levelName][$gender]++;
                                                } else {
                                                    $Content['B01'][$certificate]['L' . $levelName][$gender] = 1;
                                                }
                                                if (isset($Content['B01'][$certificate]['TotalCount'][$gender])) {
                                                    $Content['B01'][$certificate]['TotalCount'][$gender]++;
                                                } else {
                                                    $Content['B01'][$certificate]['TotalCount'][$gender] = 1;
                                                }
                                                if (isset($Content['B01']['TotalCount']['L' . $levelName][$gender])) {
                                                    $Content['B01']['TotalCount']['L' . $levelName][$gender]++;
                                                } else {
                                                    $Content['B01']['TotalCount']['L' . $levelName][$gender] = 1;
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
                                                    if (isset($countArray[$birthYear][$certificate][$gender])) {
                                                        $countArray[$birthYear][$certificate][$gender]++;
                                                    } else {
                                                        $countArray[$birthYear][$certificate][$gender] = 1;
                                                    }
                                                }

                                                /**
                                                 * B01.1
                                                 */
                                                if ($hasMigrationBackground) {
                                                    if (isset($Content['B01_1'][$certificate]['L' . $levelName][$gender])) {
                                                        $Content['B01_1'][$certificate]['L' . $levelName][$gender]++;
                                                    } else {
                                                        $Content['B01_1'][$certificate]['L' . $levelName][$gender] = 1;
                                                    }
                                                    if (isset($Content['B01_1'][$certificate]['TotalCount'][$gender])) {
                                                        $Content['B01_1'][$certificate]['TotalCount'][$gender]++;
                                                    } else {
                                                        $Content['B01_1'][$certificate]['TotalCount'][$gender] = 1;
                                                    }
                                                    if (isset($Content['B01_1']['TotalCount']['L' . $levelName][$gender])) {
                                                        $Content['B01_1']['TotalCount']['L' . $levelName][$gender]++;
                                                    } else {
                                                        $Content['B01_1']['TotalCount']['L' . $levelName][$gender] = 1;
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
                foreach ($certificateArray as $levelName => $genderArray) {
                    foreach ($genderArray as $gender => $value) {
                        $Content['B02']['Y' . $count][$levelName][$gender] = $value;

                        if (isset($Content['B02']['TotalCount'][$levelName][$gender])) {
                            $Content['B02']['TotalCount'][$levelName][$gender] += $value;
                        } else {
                            $Content['B02']['TotalCount'][$levelName][$gender] = $value;
                        }

                        $Content['B02']['Y' . $count][$gender] += $value;
                        $Content['B02']['TotalCount'][$gender] += $value;
                    }
                }

                $count++;
            }
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param int $level
     * @param TblYear $tblYear
     * @param $gender
     * @param $countAdvancedCourseArray
     * @param $countBasicCourseArray
     * @param $personAdvancedCourseList
     */
    private static function countCourses(
        TblPerson $tblPerson,
        int $level,
        TblYear $tblYear,
        $gender,
        &$countAdvancedCourseArray,
        &$countBasicCourseArray,
        &$personAdvancedCourseList
    ) {
        if ($level == 11 || $level == 12) {
            if (($tblStudentSubjectList = DivisionCourse::useService()->getStudentSubjectListByPersonAndYear($tblPerson, $tblYear, true))) {
                foreach ($tblStudentSubjectList as $tblStudentSubject) {
                    if (($tblSubject = $tblStudentSubject->getServiceTblSubject())
                        && ($tblDivisionCourse = $tblStudentSubject->getTblDivisionCourse())
                        && strpos($tblStudentSubject->getPeriodIdentifier(), '/1')
                    ) {
                        if ($tblStudentSubject->getIsAdvancedCourse()) {
                            $countAdvancedCourseArray[$tblSubject->getAcronym()][$level][$tblDivisionCourse->getId()] = 1;
                            // mehr als 2 Leistungskurse möglich, deswegen muss das Array die Zählung übernehmen.
                            // Platz 0 wird für Deutsch/Mathe reserviert.
                            if(!isset($personAdvancedCourseList[$level][$tblPerson->getId()][0])){
                                $personAdvancedCourseList[$level][$tblPerson->getId()][0] = '';
                            }
                            if ($tblSubject->getName() == 'Deutsch' || $tblSubject->getName() == 'Mathematik') {
                                $personAdvancedCourseList[$level][$tblPerson->getId()][0] = $tblSubject->getAcronym();
                            } else {
                                $personAdvancedCourseList[$level][$tblPerson->getId()][] = $tblSubject->getAcronym();
                            }
                        } else {
                            $countBasicCourseArray[$tblSubject->getAcronym()][$level]['CoursesCount'][$tblDivisionCourse->getId()] = 1;

                            if (isset($countBasicCourseArray[$tblSubject->getAcronym()][$level][$gender])) {
                                $countBasicCourseArray[$tblSubject->getAcronym()][$level][$gender] += 1;
                            } else {
                                $countBasicCourseArray[$tblSubject->getAcronym()][$level][$gender] = 1;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $Content
     * @param $personAdvancedCourseList
     */
    private static function setCoursesMatrix(
        &$Content,
        $personAdvancedCourseList
    ) {

        $countAdvancedCourseList = array();
        foreach ($personAdvancedCourseList as $level => $personArray) {
            foreach ($personArray as $personId => $subjects) {
                $identifier = '';
                ksort($subjects);
                foreach ($subjects as $ranking => $acronym) {
                    $identifier .= $acronym;
                }

                $gender = 'x';
                if (($tblPerson = Person::useService()->getPersonById($personId))
                    && ($tblCommon = Common::useService()->getCommonByPerson($tblPerson))
                    && ($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())
                    && ($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())
                ) {
                    $gender = $tblCommonGender->getShortName();
                }

                $count = count($subjects);
                if (!isset($countAdvancedCourseList[$count][$identifier])) {
                    foreach ($subjects as $ranking => $acronym) {
                        $countAdvancedCourseList[$count][$identifier]['Subjects'][$ranking] = $acronym;
                    }
                }

                if (isset($countAdvancedCourseList[$count][$identifier]['Levels'][$level][$gender])) {
                    $countAdvancedCourseList[$count][$identifier]['Levels'][$level][$gender]++;
                } else {
                    $countAdvancedCourseList[$count][$identifier]['Levels'][$level][$gender] = 1;
                }
            }
        }

        ksort($countAdvancedCourseList);
        $count = 0;
        foreach ($countAdvancedCourseList as $counter => $identifierArray) {
            ksort($identifierArray);
            foreach ($identifierArray as $identifier => $array) {
                if (isset($array['Subjects'])) {
                    foreach ($array['Subjects'] as $ranking => $acronym) {
                        $Content['E18']['S' . $count]['N' . $ranking] =
                            ($tblSubject = Subject::useService()->getSubjectByAcronym($acronym))
                                ? $tblSubject->getName()
                                : '';
                    }
                }
                if (isset($array['Levels'])) {
                    foreach ($array['Levels'] as $level => $genderArray) {
                        foreach ($genderArray as $gender => $value) {
                            $Content['E18']['S' . $count]['L' . $level][$gender] = $value;

                            if (isset($Content['E18']['TotalCount']['L' . $level][$gender])) {
                                $Content['E18']['TotalCount']['L' . $level][$gender] += $value;
                            } else {
                                $Content['E18']['TotalCount']['L' . $level][$gender] = $value;
                            }
                        }
                    }
                }

                $count++;
            }
        }
    }

    /**
     * @param $Content
     * @param $countAdvancedCourseArray
     * @param $countBasisCourseArray
     */
    private static function setCourses(
        &$Content,
        $countAdvancedCourseArray,
        $countBasisCourseArray
    ) {
        /**
         * E16. Schüler in Grundkursen an dieser Schule im Schuljahr 2016/2017 nach Jahrgangsstufen
         */
        ksort($countBasisCourseArray);
        $count = 0;
        foreach ($countBasisCourseArray as $acronym => $levelArray) {
            $subjectName = ($tblSubject = Subject::useService()->getSubjectByAcronym($acronym))
                ? $tblSubject->getName() : '';
            if ($subjectName == 'Gemeinschaftskunde/Rechtserziehung/Wirtschaft') {
                $subjectName = 'GRW';
            }
            $Content['E16']['S' . $count]['SubjectName'] = $subjectName;
            foreach ($levelArray as $level => $valueArray) {
                foreach ($valueArray as $identifier => $value) {
                    if ($identifier == 'CoursesCount') {
                        $value = count($value);
                    }

                    $Content['E16']['S' . $count]['L' . $level][$identifier] = $value;

                    if (isset($Content['E16']['TotalCount']['L' . $level][$identifier])) {
                        $Content['E16']['TotalCount']['L' . $level][$identifier] += $value;
                    } else {
                        $Content['E16']['TotalCount']['L' . $level][$identifier] = $value;
                    }
                }
            }

            $count++;
        }

        /**
         * E17. Anzahl der Leistungskurse an dieser Schule im Schuljahr 2016/2017 nach Jahrgangsstufen
         */
        ksort($countAdvancedCourseArray);
        $count = 0;
        foreach ($countAdvancedCourseArray as $acronym => $levelArray) {
            $Content['E17']['S' . $count]['SubjectName'] = ($tblSubject = Subject::useService()->getSubjectByAcronym($acronym))
                ? $tblSubject->getName() : '';
            foreach ($levelArray as $level => $divisionCourseArray) {
                $value = count($divisionCourseArray);
                $Content['E17']['S' . $count]['L' . $level] = $value;

                if (isset($Content['E17']['S' . $count]['TotalCount'])) {
                    $Content['E17']['S' . $count]['TotalCount'] += $value;
                } else {
                    $Content['E17']['S' . $count]['TotalCount'] = $value;
                }

                if (isset($Content['E17']['TotalCount']['L' . $level])) {
                    $Content['E17']['TotalCount']['L' . $level] += $value;
                } else {
                    $Content['E17']['TotalCount']['L' . $level] = $value;
                }

                if (isset($Content['E17']['TotalCount']['TotalCount'])) {
                    $Content['E17']['TotalCount']['TotalCount'] += $value;
                } else {
                    $Content['E17']['TotalCount']['TotalCount'] = $value;
                }
            }

            $count++;
        }
    }

    /**
     * @param $Content
     * @param $tblPastYearList
     * @param TblPerson $tblPerson
     * @param TblCourse|null $tblCourse
     * @param int $level
     * @param $gender
     * @param $isInPreparationDivisionForMigrants
     * @param TblType $tblSchoolTypeKamenz
     */
    private static function setSchoolTypeLastYear(
        &$Content,
        $tblPastYearList,
        TblPerson $tblPerson,
        int $level,
        $gender,
        $isInPreparationDivisionForMigrants,
        TblType $tblSchoolTypeKamenz
    ) {
        $levelLastYear = false;
        $tblSchoolTypeLastYear = false;
        $tblCourse = false;
        if ($tblPastYearList && is_array($tblPastYearList)) {
            foreach ($tblPastYearList as $tblPastYear) {
                if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblPastYear))) {
                    $levelLastYear = $tblStudentEducation->getLevel();
                    $tblSchoolTypeLastYear = $tblStudentEducation->getServiceTblSchoolType();
                    $tblCourse = $tblStudentEducation->getServiceTblCourse();
                }
            }
        }

        $identifier = false;
        if ($levelLastYear && $tblSchoolTypeLastYear) {
            if ($tblSchoolTypeLastYear->getName() == 'Grundschule') {
                $identifier = 'PrimarySchool';
            } elseif ($tblSchoolTypeLastYear->getName() == 'Gymnasium') {
                $identifier = 'GrammarSchool';
            } elseif ($tblSchoolTypeLastYear->getName() == 'Mittelschule / Oberschule') {
                if ($tblSchoolTypeKamenz->getName() == 'Mittelschule / Oberschule') {
                    if ($level > 6 && $tblCourse) {
                        if ($tblCourse->getName() == 'Hauptschule') {
                            $identifier = 'SecondarySchoolHs';
                        } elseif ($tblCourse->getName() == 'Realschule') {
                            $identifier = 'SecondarySchoolRs';
                        }
                    } else {
                        $identifier = 'SecondarySchool';
                    }
                } else {
                    $identifier = 'SecondarySchool';
                }
            }
        } else {
            $identifier = 'Unknown';
            // aus der Schülerakte bestimmen SSW-322
            if (($tblStudent = $tblPerson->getStudent())
                && ($tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE'))
                && ($tblStudentTransfer = Student::useService()->getStudentTransferByType(
                    $tblStudent, $tblStudentTransferType
                ))
            ) {
                if (($tblSchoolTypeTransfer = $tblStudentTransfer->getServiceTblType())) {
                    $useUnknown = false;
                    // SSW-396 Datum überprüfen z.B. für Schüler im Auslandssemester
                    if (($date = $tblStudentTransfer->getTransferDate())) {
                        $date = new DateTime($date);
                        $now = new DateTime('now');

                        if ($date->format('Y') != $now->format('Y')) {
                            $useUnknown = true;
                        }
                    }

                    if ($useUnknown) {
                        $identifier = 'Unknown';
                    } elseif ($tblSchoolTypeTransfer->getName() == 'Grundschule') {
                        $identifier = 'PrimarySchool';
                    } elseif ($tblSchoolTypeTransfer->getName() == 'Gymnasium') {
                        $identifier = 'GrammarSchool';
                    } elseif ($tblSchoolTypeTransfer->getName() == 'Mittelschule / Oberschule') {
                        $identifier = 'SecondarySchool';
                        if ($tblSchoolTypeKamenz->getName() == 'Mittelschule / Oberschule') {
                            if (($tblCourseTransfer = $tblStudentTransfer->getServiceTblCourse())) {
                                if($tblCourseTransfer->getName() == 'Hauptschule') {
                                    $identifier = 'SecondarySchoolHs';
                                } elseif ($tblCourseTransfer->getName() == 'Realschule') {
                                    $identifier = 'SecondarySchoolRs';
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($identifier) {
            if (isset($Content['E07'][$identifier]['L' . $level][$gender])) {
                $Content['E07'][$identifier]['L' . $level][$gender]++;
            } else {
                $Content['E07'][$identifier]['L' . $level][$gender] = 1;
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
            if (isset($Content['E07'][$identifier]['L' . $level][$gender])) {
                $Content['E07'][$identifier]['L' . $level][$gender]++;
            } else {
                $Content['E07'][$identifier]['L' . $level][$gender] = 1;
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
     * @param TblType $tblKamenzSchoolType
     */
    private static function setGraduateTechnicalSchool(
        $tblPastYearList,
        &$Content,
        TblType $tblKamenzSchoolType
    ) {
        if ($tblPastYearList) {
            foreach ($tblPastYearList as $tblPastYear) {
                /**
                 * Abgangszeugnisse
                 */
                if (($tblLeaveStudentList = Prepare::useService()->getLeaveStudentAllByYear($tblPastYear))) {
                    foreach ($tblLeaveStudentList as $tblLeaveStudent) {
                        if ($tblLeaveStudent->isApproved() && $tblLeaveStudent->isPrinted()
                            && ($tblPerson = $tblLeaveStudent->getServiceTblPerson())
                            && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblPastYear))
                            && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                            && $tblSchoolType->getId() == $tblKamenzSchoolType->getId()
                        ) {
                            self::setTechnicalGraduationForPerson($tblPerson, 'Leave', $Content);
                        }
                    }
                }

                /**
                 * Abschlusszeugnisse
                 */
                if (($tblGenerateCertificateList = Generate::useService()->getGenerateCertificateAllByYear($tblPastYear))) {
                    foreach ($tblGenerateCertificateList as $tblGenerateCertificate) {
                        if (($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
                            && ($tblCertificateType->getIdentifier() == 'DIPLOMA')
                            && (($tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate)))
                        ) {
                            foreach ($tblPrepareList as $tblPrepare) {
                                if (($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
                                    && ($tblSchoolTypeList = $tblDivisionCourse->getSchoolTypeListFromStudents())
                                    && isset($tblSchoolTypeList[$tblKamenzSchoolType->getId()])
                                    && ($tblPrepareStudentList = Prepare::useService()->getPrepareStudentAllByPrepare($tblPrepare))
                                ) {
                                    foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                                        if (($tblPerson = $tblPrepareStudent->getServiceTblPerson())
                                            && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                                        ) {
                                            self::setTechnicalGraduationForPerson($tblPerson, 'DiplomaTotal', $Content);
                                            if ($tblCertificate->getCertificate() == 'FsAbsFhr') {
                                                self::setTechnicalGraduationForPerson($tblPerson, 'DiplomaExtra', $Content);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Temp to Rows
            self::sumGraduationCourseForTechnicalSchool($Content, 'B01_1_A');
            self::sumGraduationCourseForTechnicalSchool($Content, 'B01_1_U');
            self::sumGraduationCourseForTechnicalSchool($Content, 'B01_1_1_A');
            self::sumGraduationCourseForTechnicalSchool($Content, 'B01_1_1_U');

            self::sumGraduationCourseForTechnicalSchool($Content, 'B01_2_A');
            self::sumGraduationCourseForTechnicalSchool($Content, 'B01_2_U');
            self::sumGraduationCourseForTechnicalSchool($Content, 'B01_2_1_A');
            self::sumGraduationCourseForTechnicalSchool($Content, 'B01_2_1_U');

            self::sumGraduationBirthYearForTechnicalSchool($Content, 'B02_1_A');
            self::sumGraduationBirthYearForTechnicalSchool($Content, 'B02_1_U');
            self::sumGraduationBirthYearForTechnicalSchool($Content, 'B02_1_1_A');
            self::sumGraduationBirthYearForTechnicalSchool($Content, 'B02_1_1_U');

            self::sumGraduationBirthYearForTechnicalSchool($Content, 'B02_2_A');
            self::sumGraduationBirthYearForTechnicalSchool($Content, 'B02_2_U');
            self::sumGraduationBirthYearForTechnicalSchool($Content, 'B02_2_1_A');
            self::sumGraduationBirthYearForTechnicalSchool($Content, 'B02_2_1_U');
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param $identifier
     * @param $Content
     */
    private static function setTechnicalGraduationForPerson(TblPerson $tblPerson, $identifier, &$Content)
    {
        $hasMigrationBackground = false;
        if (($tblStudent = $tblPerson->getStudent())
            && $tblStudent->getHasMigrationBackground()
        ) {
            $hasMigrationBackground = true;
        }

        if ($tblStudent && ($tblStudentTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())
            && ($tblStudentTenseOfLesson = $tblStudentTechnicalSchool->getTblStudentTenseOfLesson())
            && ($tblStudentTrainingStatus = $tblStudentTechnicalSchool->getTblStudentTrainingStatus())
        ) {
            $isFullTime = $tblStudentTenseOfLesson->getIdentifier() == TblStudentTenseOfLesson::FULL_TIME;
            $isChangeStudent = $tblStudentTrainingStatus->getIdentifier() == TblStudentTrainingStatus::CHANGE_STUDENT;
            $course = ($tblTechnicalCourse = $tblStudentTechnicalSchool->getServiceTblTechnicalCourse())
                ? $tblTechnicalCourse->getName() : '&nbsp;';
            $time = $tblStudentTechnicalSchool->getDurationOfTraining();

            if (($tblSupport = Student::useService()->getSupportForReportingByPerson($tblPerson))
                && ($tblSupportFocus = Student::useService()->getSupportPrimaryFocusBySupport($tblSupport))
                && ($tblSupportFocusType = $tblSupportFocus->getTblSupportFocusType())
            ) {
                $support = $tblSupportFocusType->getName();
            } else {
                $support = '&nbsp;';
            }

            if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))
                && (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates()))
                && ($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())
                && ($birthDay = $tblCommonBirthDates->getBirthday())
            ) {

                $gender = $tblCommonGender->getName();
                $genderShort = $tblCommonGender->getShortName();

                $birthDayDate = new DateTime($birthDay);
                $birthYear = $birthDayDate->format('Y');

                self::addTempCount(
                    $Content,
                    'B01_' . ($isFullTime ? '1' : '2') . '_' . ($isChangeStudent ? 'U' : 'A'),
                    $course . '_' . $time . '_' . $support . '_' . $gender,
                    $identifier,
                    $genderShort
                );

                if ($hasMigrationBackground) {
                    self::addTempCount(
                        $Content,
                        'B01_' . ($isFullTime ? '1' : '2') . '_1_' . ($isChangeStudent ? 'U' : 'A'),
                        $course . '_' . $time . '_' . $support . '_' . $gender,
                        $identifier,
                        $genderShort
                    );
                }

                if ($birthYear) {
                    self::addTempCount(
                        $Content,
                        'B02_' . ($isFullTime ? '1' : '2') . '_' . ($isChangeStudent ? 'U' : 'A'),
                        $birthYear . '_' . $gender,
                        $identifier,
                        $genderShort
                    );

                    if ($hasMigrationBackground) {
                        self::addTempCount(
                            $Content,
                            'B02_' . ($isFullTime ? '1' : '2') . '_1_' . ($isChangeStudent ? 'U' : 'A'),
                            $birthYear . '_' . $gender,
                            $identifier,
                            $genderShort
                        );
                    }
                }
            }
        }
    }

    /**
     * @param $Content
     * @param $name
     * @param $preText
     * @param $identifier
     * @param $genderShort
     */
    private static function addTempCount(&$Content, $name, $preText, $identifier, $genderShort)
    {
        if (isset($Content[$name]['Temp'][$preText][$identifier])) {
            $Content[$name]['Temp'][$preText][$identifier]++;
        } else {
            $Content[$name]['Temp'][$preText][$identifier] = 1;
        }

        /** TotalCount */
        if (isset($Content[$name]['TotalCount'][$identifier][$genderShort])) {
            $Content[$name]['TotalCount'][$identifier][$genderShort]++;
        } else {
            $Content[$name]['TotalCount'][$identifier][$genderShort] = 1;
        }
    }

    /**
     * @param $Content
     * @param $name
     */
    private static function sumGraduationCourseForTechnicalSchool(
        &$Content,
        $name
    ) {
        if (isset($Content[$name]['Temp'])) {
            $i = 0;
            foreach ($Content[$name]['Temp'] as $key => $row) {
                $array = explode('_', $key);
                $Content[$name]['R' . $i] = array(
                    'Course' => $array[0],
                    'Time' => $array[1],
                    'Support' => $array[2],
                    'Gender' => $array[3]
                );

                $Content[$name]['R' . $i]['TotalCount'] = 0;
                foreach ($row as $subKey => $subRow) {
                    $Content[$name]['R' . $i][$subKey] = $subRow;
                    $Content[$name]['R' . $i]['TotalCount'] += intval($subRow);
                }

                $i++;
            }
        }
    }

    /**
     * @param $Content
     * @param $name
     */
    private static function sumGraduationBirthYearForTechnicalSchool(
        &$Content,
        $name
    ) {
        if (isset($Content[$name]['Temp'])) {
            $i = 0;
            foreach ($Content[$name]['Temp'] as $key => $row) {
                $array = explode('_', $key);
                $Content[$name]['R' . $i] = array(
                    'Year' => $array[0],
                    'Gender' => $array[1]
                );

                $Content[$name]['R' . $i]['TotalCount'] = 0;
                foreach ($row as $subKey => $subRow) {
                    $Content[$name]['R' . $i][$subKey] = $subRow;
                    $Content[$name]['R' . $i]['TotalCount'] += intval($subRow);
                }

                $i++;
            }
        }
    }

    /**
     * @param $Content
     * @param $name
     * @param TblStudent $tblStudent
     * @param int $level
     *
     * @return int
     */
    private static function setForeignLanguagesForTechnicalSchool(
        &$Content,
        $name,
        TblStudent $tblStudent,
        int $level
    ): int {
        $countLanguages = 0;
        if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
           && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                $tblStudent, $tblStudentSubjectType
            ))
        ) {
            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                if (($tblSubject = $tblStudentSubject->getServiceTblSubject())
                    && $tblStudentSubject->getTblStudentSubjectRanking()
                ) {
                    // #SSW-1596 abgeschlossene und noch nicht begonnene Fremdsprachen ignorieren
                    if (($LevelFrom = $tblStudentSubject->getLevelFrom())
                        && $LevelFrom > $level
                    ) {
                        continue;
                    }
                    if (($LevelTill = $tblStudentSubject->getLevelTill())
                        && $LevelTill < $level
                    ) {
                        continue;
                    }

                    if (isset($Content[$name]['Temp'][$tblSubject->getName()]['L' . $level])) {
                        $Content[$name]['Temp'][$tblSubject->getName()]['L' . $level]++;
                    } else {
                        $Content[$name]['Temp'][$tblSubject->getName()]['L' . $level] = 1;
                    }

                    $countLanguages++;
                }
            }
        }

        return $countLanguages;
    }

    /**
     * @param $Content
     * @param string $name
     */
    private static function sumForeignLanguagesForTechnicalSchool(
        &$Content,
        string $name
    ) {
        if (isset($Content[$name]['Temp'])) {
            $i = 0;
            ksort($Content[$name]['Temp']);
            foreach ($Content[$name]['Temp'] as $key => $row) {

                $Content[$name]['R' . $i] = array(
                    'Language' => $key
                );

                $Content[$name]['R' . $i]['TotalCount'] = 0;
                foreach ($row as $subKey => $subRow) {
                    $Content[$name]['R' . $i][$subKey] = $subRow;
                    $Content[$name]['R' . $i]['TotalCount'] += intval($subRow);
                }

                $i++;
            }
        }
    }
}