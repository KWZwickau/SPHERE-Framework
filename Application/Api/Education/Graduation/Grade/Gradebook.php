<?php

namespace SPHERE\Application\Api\Education\Graduation\Grade;

use DateTime;
use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateType;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTest;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Main;

class Gradebook implements IModuleInterface
{
    public static function registerModule()
    {
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Gradebook/Download',
            __CLASS__ . '::downloadGradebook'
        ));
    }

    public static function useService()
    {
    }

    public static function useFrontend()
    {
    }

    /**
     * @param null $DivisionCourseId
     * @param null $SubjectId
     *
     * @return string
     */
    public static function downloadGradebook($DivisionCourseId = null, $SubjectId = null): string
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblSubject = Subject::useService()->getSubjectById($SubjectId))
        ) {
            $showCourse = false;
            if (($tblSchoolTypeList = $tblDivisionCourse->getSchoolTypeListFromStudents())
                && ($tblSchoolType = Type::useService()->getTypeByShortName('OS'))
                && isset($tblSchoolTypeList[$tblSchoolType->getId()])
            ) {
                $showCourse = true;
            }

            list ($headerList, $bodyList, $boldList) = self::getGradebookContent($tblDivisionCourse, $tblSubject, $showCourse);

            $fileLocation = self::getGradebookExcel($tblDivisionCourse, $tblSubject, $headerList, $bodyList, $boldList, $showCourse);

            // Download-Link für Excel-Datei erstellen
            return FileSystem::getDownload($fileLocation->getRealPath(),
                'Notenbuch ' . $tblDivisionCourse->getName() . ' ' . $tblSubject->getAcronym() . ' ' . date("Y-m-d H:i:s") . ".xlsx")->__toString();
        }

        return 'Keine Daten vorhanden';
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject $tblSubject
     * @param bool $showCourse
     *
     * @return array[]
     */
    private static function getGradebookContent(TblDivisionCourse $tblDivisionCourse, TblSubject $tblSubject, bool $showCourse): array
    {
        $isSekTwo = DivisionCourse::useService()->getIsCourseSystemByStudentsInDivisionCourse($tblDivisionCourse);

        $isShortYear = false;
        $tblYear = $tblDivisionCourse->getServiceTblYear();
        if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())) {
            foreach ($tblPersonList as $tblPerson) {
                if (DivisionCourse::useService()->getIsShortYearByPersonAndYear($tblPerson, $tblYear)) {
                    $isShortYear = true;
                    break;
                }
            }
        }

        $showAverage = false;
        if (($tblSettingAverage = Consumer::useService()->getSetting('Education', 'Graduation', 'Gradebook', 'ShowAverageInPdf'))) {
            $showAverage = $tblSettingAverage->getValue();
        }

        $showCertificateGrade = false;
        if (($tblSettingAppointedDateGrades = Consumer::useService()->getSetting('Education', 'Graduation', 'Gradebook', 'ShowCertificateGradeInPdf'))) {
            $showCertificateGrade = $tblSettingAppointedDateGrades->getValue();
        }

        $headerList[0] = array();
        $headerList[1] = array();
        $headerList[1]['Number'] = '#';
        $headerList[1]['Student'] = 'Schüler';
        if ($showCourse) {
            $headerList[1]['Course'] = 'BG';
        }

        $bodyList = array();
        $certificateTypeList = array();
        $boldList = array();
        if ($tblYear
            && ($tblPeriodList = Term::useService()->getPeriodListByYear($tblYear, $isShortYear))
        ) {
            $countPeriods = 0;
            foreach ($tblPeriodList as $tblPeriod) {
                $countPeriods++;
                if (($tblTestList = Grade::useService()->getTestListByDivisionCourseAndSubject($tblDivisionCourse, $tblSubject))) {
                    $tblTestList = \SPHERE\Application\Api\Document\Standard\Repository\Gradebook\Gradebook::sortTestList($tblTestList);
                    /** @var TblTest $tblTest */
                    foreach ($tblTestList as $tblTest) {
                        if (($tblGradeType = $tblTest->getTblGradeType())) {
                            $dateTime = null;
                            if ($tblTest->getDate()) {
                                $dateTime = $tblTest->getDate();
                            } elseif ($tblTest->getIsContinues() && $tblTest->getFinishDate()) {
                                $dateTime = $tblTest->getFinishDate();
                            }

                            if ($dateTime) {
                                // Tests welche nicht zur Periode gehören überspringen
                                if ($dateTime < $tblPeriod->getFromDateTime() || $dateTime > $tblPeriod->getToDateTime()) {
                                    continue;
                                }
                            }

                            $headerList[0]['Test_' . $tblTest->getId()] = $dateTime ? $dateTime->format('d.m.') : '';
                            $headerList[1]['Test_' . $tblTest->getId()] = $tblGradeType->getCode();

                            if ($tblGradeType->getIsHighlighted()) {
                                $boldList['Test_' . $tblTest->getId()] = true;
                            }
                        }
                    }
                }

                $averageIndex = 'Average_' . $tblPeriod->getId();
                $averageContent = 'Ø ' . $countPeriods . '.HJ';
                $certificateIndex = 'Certificate_' . $countPeriods;
                $certificateContent = 'SN ' . $countPeriods . '.HJ';
                if ($isSekTwo) {
                    $tblCertificateType = Generator::useService()->getCertificateTypeByIdentifier('MID_TERM_COURSE');
                } elseif (count($tblPeriodList) == $countPeriods) {
                    $tblCertificateType = Generator::useService()->getCertificateTypeByIdentifier('YEAR');
                    $averageIndex = 'Average_0';
                    $averageContent = 'Ø ' . 'SJ';
                    $certificateIndex = 'Certificate_0';
                    $certificateContent = 'SN ' . 'SJ';
                } else {
                    $tblCertificateType = Generator::useService()->getCertificateTypeByIdentifier('HALF_YEAR');
                }

                $certificateTypeList[$certificateIndex] = $tblCertificateType;

                if ($showAverage) {
                    $headerList[0][$averageIndex] = '';
                    $headerList[1][$averageIndex] = $averageContent;
                }

                if ($showCertificateGrade) {
                    $headerList[0][$certificateIndex] = '';
                    $headerList[1][$certificateIndex] = $certificateContent;
                    $boldList[$certificateIndex] = true;
                }
            }
        }

        $tblTestGradeListByTest = array();
        if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())) {
            $number = 0;
            foreach ($tblPersonList as $tblPerson) {
                $data = array();
                if (!DivisionCourse::useService()->getVirtualSubjectFromRealAndVirtualByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject)) {
                    // Schüler hat das Fach nicht
                    continue;
                }

                $number++;
                $data['Number'] = $number;
                $data['Student'] = $tblPerson->getLastFirstName();

                if ($showCourse
                    && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                    && ($tblCourse = $tblStudentEducation->getServiceTblCourse())
                ) {
                    if ($tblCourse->getName() == 'Gymnasium') {
                        $data['Course'] = 'GYM';
                    } elseif ($tblCourse->getName() == 'Realschule') {
                        $data['Course'] = 'RS';
                    } elseif ($tblCourse->getName() == 'Hauptschule') {
                        $data['Course'] = 'HS';
                    }
                }

                foreach ($headerList[1] as $index => $value) {
                    if (strpos($index, 'Test_') !== false) {
                        $testId = substr($index, strlen('Test_'));
                        if (($tblTest = Grade::useService()->getTestById($testId))
                            && ($tblGradeType = $tblTest->getTblGradeType())
                        ) {
                            if (($tblGrade = Grade::useService()->getTestGradeByTestAndPerson($tblTest, $tblPerson))
                                && $tblGrade->getGrade() !== false && $tblGrade->getGrade() !== null
                            ) {
                                $data[$index] = $tblGrade->getGrade();
                                if ($tblGrade->getIsGradeNumeric()) {
                                    if (isset($tblTestGradeListByTest[$tblTest->getId()])) {
                                        $tblTestGradeListByTest[$tblTest->getId()]['Sum'] += $tblGrade->getGradeNumberValue();
                                        $tblTestGradeListByTest[$tblTest->getId()]['Count']++;
                                    } else {
                                        $tblTestGradeListByTest[$tblTest->getId()]['Sum'] = $tblGrade->getGradeNumberValue();
                                        $tblTestGradeListByTest[$tblTest->getId()]['Count'] = 1;
                                    }
                                }
                            }
                        }
                    } elseif (strpos($index, 'Average_') !== false) {
                        $periodId = substr($index, strlen('Average_'));
                        $tblPeriod = Term::useService()->getPeriodById($periodId);

                        /*
                         * Calc Average Period or Average Total
                         */
                        $tblScoreRule = Grade::useService()->getScoreRuleByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject, $tblDivisionCourse);
                        if (!$isSekTwo && !$tblPeriod) {
                            $tblTestGradeListByStudent = Grade::useService()->getTestGradeListByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject);
                        } else {
                            $tblTestGradeListByStudent = Grade::useService()->getTestGradeListBetweenDateTimesByPersonAndYearAndSubject(
                                $tblPerson, $tblYear, $tblSubject, $tblPeriod->getFromDateTime(), $tblPeriod->getToDateTime()
                            );
                        }

                        if ($tblTestGradeListByStudent) {
                            list($average) = Grade::useService()->getCalcStudentAverage(
                                $tblPerson, $tblYear, $tblTestGradeListByStudent, $tblScoreRule ?: null, $tblPeriod ?: null
                            );
                            $data[$index] = $average;
                        }
                    } elseif (strpos($index, 'Certificate_') !== false) {
                        $countPeriods = substr($index, strlen('Certificate_'));
                        if (isset($certificateTypeList[$index])) {
                            /** @var TblCertificateType $tblCertificateType */
                            $tblCertificateType = $certificateTypeList[$index];
                            $tblTask = false;
                            $tblCertificate = false;
                            if ($showCertificateGrade
                                && ($tblPrepareStudentList = Prepare::useService()->getPrepareStudentListByPersonAndCertificateTypeAndYear(
                                    $tblPerson, $tblCertificateType, $tblYear, 'ASC'
                                ))
                            ) {
                                $countTemp = 0;
                                foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                                    if (($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())) {
                                        $countTemp++;
                                        if ($isSekTwo && (!$countPeriods || $countTemp == $countPeriods)) {
                                            $tblCertificate = $tblPrepareStudent->getServiceTblCertificate();
                                            $tblTask = $tblPrepare->getServiceTblAppointedDateTask();
                                            break;
                                        } elseif (!$isSekTwo) {
                                            $tblCertificate = $tblPrepareStudent->getServiceTblCertificate();
                                            $tblTask = $tblPrepare->getServiceTblAppointedDateTask();
                                            break;
                                        }
                                    }
                                }
                            }

                            if ($tblTask
                                && ($tblTaskGrade = Grade::useService()->getTaskGradeByPersonAndTaskAndSubject($tblPerson, $tblTask, $tblSubject))
                            ) {
                                $data[$index] = $tblTaskGrade->getDisplayGrade(true, $tblCertificate ?: null);
                            }
                        }
                    }
                }

                $bodyList[] = $data;
            }
        }

        // Fach-Klassen-Durchschnitt
        $data = array();
        $data['Student'] = 'Ø Fach-Klasse';
        foreach ($headerList[1] as $index => $value) {
            if (strpos($index, 'Test_') !== false) {
                $testId = substr($index, strlen('Test_'));
                $data[$index] = isset($tblTestGradeListByTest[$testId])
                    ? Grade::useService()->getGradeAverage($tblTestGradeListByTest[$testId]['Sum'], $tblTestGradeListByTest[$testId]['Count'])
                    : '';
            }
        }
        $bodyList[] = $data;

        return array($headerList, $bodyList, $boldList);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject $tblSubject
     * @param $headerList
     * @param $bodyList
     * @param $boldList
     * @param bool $showCourse
     *
     * @return FilePointer
     */
    private static function getGradebookExcel(TblDivisionCourse $tblDivisionCourse, TblSubject $tblSubject, $headerList, $bodyList, $boldList, bool $showCourse)
    {
        $fileLocation = Storage::createFilePointer('xlsx');

        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());

        $export->setStyle($export->getCell(0, 0))->setFontBold();
        $export->setStyle($export->getCell(0, 1))->setFontBold();

        $Row = 0;
        $Column = 0;
        $export->setValue($export->getCell($Column, $Row++), 'Notenbuch');
        $export->setValue($export->getCell($Column, $Row++),
            $tblDivisionCourse->getTypeName() . ' ' . $tblDivisionCourse->getName() . ' - ' . $tblSubject->getDisplayName()
        );
        $tblTeacherList = array();
        if (($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy(null, null, $tblDivisionCourse, $tblSubject))) {
            foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                if (($tblPerson = $tblTeacherLectureship->getServiceTblPerson())) {
                    $tblTeacherList[$tblPerson->getId()] = $tblTeacherLectureship->getTeacherName();
                }
            }
        }
        $export->setValue($export->getCell($Column, $Row++), 'Fachlehrer: ' . (empty($tblTeacherList) ? '' : implode(', ', $tblTeacherList)));
        $export->setValue($export->getCell($Column, $Row++), 'Stand: ' . (new DateTime())->format('d.m.Y'));
        $Row++;

        for ($i = 0; $i < 2; $i++) {
            $Column = $i == 0 ? ($showCourse ? 3 : 2) : 0;
            foreach ($headerList[$i] as $key => $header) {
                $export->setValue($export->getCell($Column, $Row), $header);
                if (isset($boldList[$key])) {
                    $export->setStyle($export->getCell($Column, $Row))->setFontBold();
                }
                $Column++;
            }
            $Row++;
        }

        foreach ($bodyList as $list) {
            $Column = 0;
            foreach ($headerList[1] as $index => $value) {
                $export->setValue($export->getCell($Column, $Row), $list[$index] ?? '');
                if (isset($boldList[$index])) {
                    $export->setStyle($export->getCell($Column, $Row))->setFontBold();
                }
                $Column++;
            }
            $Row++;
        }

        // set column width
        $export->setStyle($export->getCell(1, 0))->setColumnWidth(30);

        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

        return $fileLocation;
    }
}