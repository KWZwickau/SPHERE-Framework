<?php
namespace SPHERE\Application\Api\Document\Standard\Repository\Gradebook;

use DateTime;
use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTest;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter\DateTimeSorter;

/**
 * Class Gradebook
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\Gradebook
 */
class Gradebook extends AbstractDocument
{
    const TEXT_SIZE_HEADER = '8pt';// '12px';
    const TEXT_SIZE_BODY = '8pt';// '11px';
    const HEIGHT_HEADER = 450;
    const COLOR_HEADER = 'white';
    const COLOR_BODY_ALTERNATE_1 = '#F0F0F0';
    const COLOR_BODY_ALTERNATE_2 = '#FFF';
    const COLOR_BODY_DARK = '#E4E4E4';
    const MINIMUM_TEST_COUNT = 4;

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Notenbücher.pdf';
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject $tblSubject
     *
     * @return IBridgeInterface
     */
    public function createSingleDocument(TblDivisionCourse $tblDivisionCourse, TblSubject $tblSubject): IBridgeInterface
    {
        $pageList = $this->buildPageList($tblDivisionCourse, $tblSubject);

        $Document = $this->buildDocument($pageList);

        return $Document->getTemplate();
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject $tblSubject
     *
     * @return Page[]
     */
    public function buildPageList(TblDivisionCourse $tblDivisionCourse, TblSubject $tblSubject): array
    {
        $pageList = array();

        $isSekTwo = DivisionCourse::useService()->getIsCourseSystemByStudentsInDivisionCourse($tblDivisionCourse);

        $showCourse = false;
        if (($tblSchoolTypeList = $tblDivisionCourse->getSchoolTypeListFromStudents())
            && ($tblSchoolType = Type::useService()->getTypeByShortName('OS'))
            && isset($tblSchoolTypeList[$tblSchoolType->getId()])
        ) {
            $showCourse = true;
        }

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

        if ($tblYear
            && ($tblPeriodList = Term::useService()->getPeriodListByYear($tblYear, $isShortYear))
        ) {
            $count = 0;
            foreach ($tblPeriodList as $tblPeriod) {
                $count++;
                $isLastPeriod = $count == count($tblPeriodList);

                $pageList[] = $this->buildPage($tblDivisionCourse, $tblSubject, $tblPeriod, $tblYear, $isLastPeriod, $isSekTwo, $showCourse);
            }
        }

        return $pageList;
    }

    /**
     * @param array  $pageList
     * @param string $part
     *
     * @return Frame
     */
    public function buildDocument($pageList = array(), $part = '0'): Frame
    {
        $document = new Document();

        foreach ($pageList as $subjectPages) {
            if (is_array($subjectPages)) {
                foreach ($subjectPages as $page) {
                    $document->addPage($page);
                }
            } else {
                $document->addPage($subjectPages);
            }
        }

        return (new Frame())->addDocument($document);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject $tblSubject
     * @param TblPeriod $tblPeriod
     * @param TblYear $tblYear
     * @param bool $isLastPeriod
     * @param bool $isSekTwo
     * @param bool $showCourse
     * @return Page
     */
    public function buildPage(
        TblDivisionCourse $tblDivisionCourse,
        TblSubject $tblSubject,
        TblPeriod $tblPeriod,
        TblYear $tblYear,
        bool $isLastPeriod,
        bool $isSekTwo,
        bool $showCourse
    ): Page {
        $tblTeacherList = array();
        if (($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy(null, null, $tblDivisionCourse, $tblSubject))) {
            foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                if (($tblPerson = $tblTeacherLectureship->getServiceTblPerson())) {
                    $tblTeacherList[$tblPerson->getId()] = $tblTeacherLectureship->getTeacherName();
                }
            }
        }

        return (new Page())
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Notenbuch')
                    ->styleTextSize('20px')
                    ->styleTextBold()
                )
                ->addElement((new Element())
                    ->setContent(
                        $tblDivisionCourse->getTypeName() . ' ' . $tblDivisionCourse->getName() . ' - ' . $tblSubject->getDisplayName()
                    )
                    ->styleTextSize('20px')
                    ->styleTextBold()
                )
                ->addElement((new Element())
                    ->setContent(
                        'Fachlehrer: ' . (empty($tblTeacherList) ? '' : implode(', ', $tblTeacherList))
                    )
                )
                ->addElement((new Element())
                    ->setContent(
                        'Stand: ' . (new DateTime())->format('d.m.Y')
                    )
                )
            )
            ->addSlice((new Slice())->addElement((new Element())->styleHeight('20px')))
            ->addSlice(
                $this->setContent(
                    $tblDivisionCourse,
                    $tblSubject,
                    $tblPeriod,
                    $tblYear,
                    $isLastPeriod,
                    $isSekTwo,
                    $showCourse
                )
            );
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject $tblSubject
     * @param TblPeriod $tblPeriod
     * @param TblYear $tblYear
     * @param bool $isLastPeriod
     * @param bool $isSekTwo
     * @param bool $showCourse
     * @return Slice
     */
    private function setContent(
        TblDivisionCourse $tblDivisionCourse,
        TblSubject $tblSubject,
        TblPeriod $tblPeriod,
        TblYear $tblYear,
        bool $isLastPeriod,
        bool $isSekTwo,
        bool $showCourse
    ): Slice {

        $slice = new Slice();
        $paddingLeft = '3px';

        $minimumTestCount = self::MINIMUM_TEST_COUNT;

        $widthStudentColumn = 30;
        $widthPeriodColumns = 100 - $widthStudentColumn;
        $widthStudentColumnString = $widthStudentColumn . '%';

        $widthNumber = '9%';
        if ($showCourse) {
            $widthStudentName = '76%';
            $widthCourse = '15%';
        } else {
            $widthStudentName = '91%';
            $widthCourse = '0%';
        }

        // wird dynamisch nach der Testanzahl angepasst
        $widthColumnTest = 10;
        $widthColumnTestString = '10%';

        $showAverage = false;
        if (($tblSettingAverage = Consumer::useService()->getSetting('Education', 'Graduation', 'Gradebook', 'ShowAverageInPdf'))) {
            $showAverage = $tblSettingAverage->getValue();
        }

        $showCertificateGrade = false;
        if (($tblSettingAppointedDateGrades = Consumer::useService()->getSetting('Education', 'Graduation', 'Gradebook', 'ShowCertificateGradeInPdf'))) {
            $showCertificateGrade = $tblSettingAppointedDateGrades->getValue();
        }

        $isLastTestLastColumn = false;
        $isAverageLastColumn = false;
        $isCertificateGradeLastColumn = false;
        if ($showCertificateGrade) {
            $isCertificateGradeLastColumn = true;
        } elseif ($showAverage) {
            $isAverageLastColumn = true;
        } else {
            $isLastTestLastColumn = true;
        }

        if ($showAverage && $showCertificateGrade) {
            $offset = 2;
        } elseif ($showAverage || $showCertificateGrade) {
            $offset = 1;
        } else {
            $offset = 0;
        }

        $subSection = new Section();
        $subSection
            ->addElementColumn((new Element())
                ->setContent('#')
                ->styleTextSize(self::TEXT_SIZE_HEADER)
                ->stylePaddingLeft($paddingLeft)
                ->styleTextBold()
                ->styleBorderLeft()
                ->styleBackgroundColor(self::COLOR_HEADER)
                , $widthNumber)
            ->addElementColumn((new Element())
                ->setContent('Schüler')
                ->styleTextSize(self::TEXT_SIZE_HEADER)
                ->stylePaddingLeft($paddingLeft)
                ->styleTextBold()
                ->styleBorderLeft()
                ->styleBackgroundColor(self::COLOR_HEADER)
                , $widthStudentName);

        if ($showCourse) {
            $subSection
                ->addElementColumn((new Element())
                    ->setContent('Bg')
                    ->styleTextSize(self::TEXT_SIZE_HEADER)
                    ->stylePaddingLeft($paddingLeft)
                    ->styleTextBold()
                    ->styleBorderLeft()
                    ->styleBackgroundColor(self::COLOR_HEADER)
                    , $widthCourse);
        }

        $section = new Section();
        $section
            ->addSliceColumn((new Slice)
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleTextSize(self::TEXT_SIZE_HEADER)
                        ->styleHeight(self::HEIGHT_HEADER . 'px')
                        ->styleTextBold()
                        ->styleBorderLeft()
                        ->styleBorderTop()
                        ->styleBackgroundColor(self::COLOR_HEADER)
                    )
                )
                ->addSection($subSection)
                , $widthStudentColumnString
            );

        $periodListCount = array();
        $testList = array();

        $count = 0;

        if (($tblTestList = Grade::useService()->getTestListByDivisionCourseAndSubject($tblDivisionCourse, $tblSubject))) {
            $tblTestList = $this->sortTestList($tblTestList);
            /** @var TblTest $tblTest */
            foreach ($tblTestList as $tblTest) {
                if (($tblGradeType = $tblTest->getTblGradeType())) {
                    $dateTime = null;
                    if ($tblTest->getDate()) {
                        $dateTime = $tblTest->getDate();
                    } elseif ($tblTest->getIsContinues() && $tblTest->getFinishDate()) {
                        $dateTime = $tblTest->getFinishDate();
                    }

                    $date = '';
                    if ($dateTime) {
                        // Tests welche nicht zur Periode gehören überspringen
                        if ($dateTime < $tblPeriod->getFromDateTime() || $dateTime > $tblPeriod->getToDateTime()) {
                            continue;
                        }

                        $date = $dateTime->format('d.m.');
                    }

                    $count++;

                    $description = trim($tblTest->getDescription());
                    if (!empty($description)) {
                        $description = str_replace('-', ' ', $description);
                    }

                    $text = trim($date . ' ' . $tblGradeType->getCode() . ' ' . $description);

                    $testList[$tblPeriod->getId()][$tblTest->getId()] = $text;
                }
            }

            if ($minimumTestCount > $count) {
                $count = $minimumTestCount;
            }

        } else {
            $count = $minimumTestCount;
        }
        if ($showAverage) {
            $count++;
        }
        if ($showCertificateGrade) {
            $count++;
        }
        $periodListCount[$tblPeriod->getId()] = $count;

        $sumCount = array_sum($periodListCount);
        if ($sumCount > 0) {
            $widthColumnTest = $widthPeriodColumns / $sumCount;
            $widthColumnTestString = $widthColumnTest . '%';
        }

        if (isset($periodListCount[$tblPeriod->getId()])) {
            $countTestPeriod = $periodListCount[$tblPeriod->getId()];
            $headerSection = new Section();
            $countTests = 0;
            $countHeaders = 4;
            if (isset($testList[$tblPeriod->getId()])) {
                $countHeaders = count($testList[$tblPeriod->getId()]);
                foreach ($testList[$tblPeriod->getId()] as $testId => $text) {
                    if (($tblTest = Grade::useService()->getTestById($testId))
                        && ($tblGradeType = $tblTest->getTblGradeType())
                    ) {
                        $countTests++;
                        $headerSection = $this->setHeaderTest(
                            $headerSection,
                            $text,
                            $widthColumnTestString,
                            $countHeaders,
                            $tblGradeType->getIsHighlighted(),
                            $isLastTestLastColumn && $countTests == $countTestPeriod
                        );
                    }
                }
            }

            // leer Spalten auffüllen
            if ($countTests < $countTestPeriod - $offset) {
                for (; $countTests < $countTestPeriod - $offset; $countTests++){
                    $headerSection = $this->setHeaderTest(
                        $headerSection,
                        '&nbsp;',
                        $widthColumnTestString,
                        $countHeaders,
                        false,
                        $isLastTestLastColumn && $countTests == ($countTestPeriod - $offset - 1)
                    );
                }
            }

            if ($showAverage) {
                if (!$isSekTwo && $isLastPeriod) {
                    // Gesamtdurchschnitt
                    $headerName = 'Schuljahr';
                } else {
                    // Durchschnitt des Halbjahres
                    $headerName = $tblPeriod->getName();
                }

                $headerSection = $this->setHeaderTest(
                    $headerSection,
                    '&#216;' . '&nbsp;' . $headerName,
                    $widthColumnTestString,
                    $countHeaders,
                    true,
                    $isAverageLastColumn
                );
            }

            if ($showCertificateGrade) {
                if (!$isSekTwo && $isLastPeriod) {
                    // Gesamtdurchschnitt
                    $headerName = 'Schuljahr';
                } else {
                    // Durchschnitt des Halbjahres
                    $headerName = $tblPeriod->getName();
                }

                $headerSection = $this->setHeaderTest(
                    $headerSection,
                    'Zeugnisnote' . '&nbsp;' . $headerName,
                    $widthColumnTestString,
                    $countHeaders,
                    true,
                    $isCertificateGradeLastColumn
                );
            }

            $section
                ->addSliceColumn((new Slice)
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent($tblPeriod->getDisplayName())
                            ->styleTextSize(self::TEXT_SIZE_HEADER)
                            ->stylePaddingLeft($paddingLeft)
                            ->styleTextBold()
                            ->styleBorderLeft()
                            ->styleBorderTop()
                            ->styleBorderRight()
                            ->styleBackgroundColor(self::COLOR_HEADER)
                        )
                    )
                    ->addSection($headerSection)
                    , ($countTestPeriod * $widthColumnTest) . '%'
                );
        }

        $slice->addSection($section);

        if ($isSekTwo) {
            $tblCertificateType = Generator::useService()->getCertificateTypeByIdentifier('MID_TERM_COURSE');
        } elseif ($isLastPeriod) {
            $tblCertificateType = Generator::useService()->getCertificateTypeByIdentifier('YEAR');
        } else {
            $tblCertificateType = Generator::useService()->getCertificateTypeByIdentifier('HALF_YEAR');
        }

        /**
         * Body
         */
        $tblTestGradeListByTest = array();
        if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())) {
            $number = 0;
            foreach ($tblPersonList as $tblPerson) {
                if (!DivisionCourse::useService()->getVirtualSubjectFromRealAndVirtualByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject)) {
                    // Schüler hat das Fach nicht
                    continue;
                }

                $tblTask = false;
                $tblCertificate = false;
                if ($showCertificateGrade
                    && ($tblPrepareStudentList = Prepare::useService()->getPrepareStudentListByPersonAndCertificateTypeAndYear(
                        $tblPerson, $tblCertificateType, $tblYear
                    ))
                ) {
                    foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                        if (($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
                            && ($tblTask = $tblPrepare->getServiceTblAppointedDateTask())
                        ) {
                            $tblCertificate = $tblPrepareStudent->getServiceTblCertificate();
                            break;
                        }
                    }
                }

                $number++;
                $name = $tblPerson->getLastFirstName();

                $courseName = '&nbsp;';
                if ($showCourse
                    && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                    && ($tblCourse = $tblStudentEducation->getServiceTblCourse())
                ) {
                    if ($tblCourse->getName() == 'Gymnasium') {
                        $courseName = 'GYM';
                    } elseif ($tblCourse->getName() == 'Realschule') {
                        $courseName = 'RS';
                    } elseif ($tblCourse->getName() == 'Hauptschule') {
                        $courseName = 'HS';
                    }
                }

                $subSection = new Section();
                $subSection
                    ->addElementColumn((new Element())
                        ->setContent($number)
                        ->styleTextSize(self::TEXT_SIZE_BODY)
                        ->stylePaddingLeft($paddingLeft)
                        ->styleBorderTop()
                        ->styleBorderLeft()
                        ->styleBackgroundColor($number % 2 == 1 ? self::COLOR_BODY_ALTERNATE_1 : self::COLOR_BODY_ALTERNATE_2)
                        , $widthNumber)
                    ->addElementColumn((new Element())
                        ->setContent($name)
                        ->styleTextSize(self::TEXT_SIZE_BODY)
                        ->stylePaddingLeft($paddingLeft)
                        ->styleBorderTop()
                        ->styleBorderLeft()
                        ->styleBackgroundColor($number % 2 == 1 ? self::COLOR_BODY_ALTERNATE_1 : self::COLOR_BODY_ALTERNATE_2)
                        , $widthStudentName);
                if ($showCourse) {
                    $subSection
                        ->addElementColumn((new Element())
                            ->setContent($courseName)
                            ->styleTextSize(self::TEXT_SIZE_BODY)
                            ->stylePaddingLeft($paddingLeft)
                            ->styleBorderTop()
                            ->styleBorderLeft()
                            ->styleBackgroundColor($number % 2 == 1 ? self::COLOR_BODY_ALTERNATE_1 : self::COLOR_BODY_ALTERNATE_2)
                            , $widthCourse);
                }

                $section = new Section();
                $section
                    ->addSliceColumn((new Slice)
                        ->addSection($subSection)
                        , $widthStudentColumnString
                    );

                if (isset($periodListCount[$tblPeriod->getId()])) {
                    $countTestPeriod = $periodListCount[$tblPeriod->getId()];
                    $periodSection = new Section();
                    $countTests = 0;
                    if (isset($testList[$tblPeriod->getId()])) {
                        foreach ($testList[$tblPeriod->getId()] as $testId => $text) {
                            if (($tblTest = Grade::useService()->getTestById($testId))
                                && ($tblGradeType = $tblTest->getTblGradeType())
                            ) {
                                $countTests++;
                                $grade = '&nbsp;';
                                if (($tblGrade = Grade::useService()->getTestGradeByTestAndPerson($tblTest, $tblPerson))
                                    && $tblGrade->getGrade() !== false && $tblGrade->getGrade() !== null
                                ) {
                                    $grade = $tblGrade->getGrade();
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

                                $periodSection->addElementColumn((new Element())
                                    ->setContent($grade)
                                    ->styleTextSize(self::TEXT_SIZE_BODY)
                                    ->stylePaddingLeft($paddingLeft)
                                    ->styleTextBold($tblGradeType->getIsHighlighted() ? 'bold' : 'normal')
                                    ->styleBorderTop()
                                    ->styleBorderLeft()
                                    ->styleBorderRight($isLastTestLastColumn && $countTests == $countTestPeriod
                                        ? '1px' : '0px')
                                    ->styleBackgroundColor($number % 2 == 1 ? self::COLOR_BODY_ALTERNATE_1 : self::COLOR_BODY_ALTERNATE_2)
                                    , $widthColumnTestString);
                            }
                        }
                    }

                    // leer Spalten auffüllen
                    if ($countTests < $countTestPeriod - $offset) {
                        for (; $countTests < $countTestPeriod - $offset; $countTests++){
                            $periodSection->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                ->styleTextSize(self::TEXT_SIZE_BODY)
                                ->stylePaddingLeft($paddingLeft)
                                ->styleBorderTop()
                                ->styleBorderLeft()
                                ->styleBorderRight($isLastTestLastColumn && $countTests == ($countTestPeriod - $offset - 1)
                                    ? '1px' : '0px')
                                ->styleBackgroundColor($number % 2 == 1 ? self::COLOR_BODY_ALTERNATE_1 : self::COLOR_BODY_ALTERNATE_2)
                                , $widthColumnTestString);
                        }
                    }

                    if ($showAverage) {
                        /*
                         * Calc Average Period or Average Total
                         */
                        $tblScoreRule = Grade::useService()->getScoreRuleByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject, $tblDivisionCourse);
                        if (!$isSekTwo && $isLastPeriod) {
                            $tblTestGradeListByStudent = Grade::useService()->getTestGradeListByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject);
                        } else {
                            $tblTestGradeListByStudent = Grade::useService()->getTestGradeListBetweenDateTimesByPersonAndYearAndSubject(
                                $tblPerson, $tblYear, $tblSubject, $tblPeriod->getFromDateTime(), $tblPeriod->getToDateTime()
                            );
                        }

                        $average = '&nbsp;';
                        if ($tblTestGradeListByStudent) {
                            list($average) = Grade::useService()->getCalcStudentAverage(
                                $tblPerson, $tblYear, $tblTestGradeListByStudent, $tblScoreRule ?: null, $tblPeriod
                            );
                        }

                        $periodSection->addElementColumn((new Element())
                            ->setContent($average)
                            ->styleTextSize(self::TEXT_SIZE_BODY)
                            ->stylePaddingLeft($paddingLeft)
                            ->styleTextBold()
                            ->styleBorderTop()
                            ->styleBorderLeft()
                            ->styleBorderRight($isAverageLastColumn ? '1px' : '0px')
                            ->styleBackgroundColor(self::COLOR_BODY_DARK)
                            , $widthColumnTestString);
                    }

                    if ($showCertificateGrade) {
                        $certificateGrade = '&nbsp;';
                        if ($tblTask
                            && ($tblTaskGrade = Grade::useService()->getTaskGradeByPersonAndTaskAndSubject($tblPerson, $tblTask, $tblSubject))
                        ) {
                            $certificateGrade = $tblTaskGrade->getDisplayGrade(true, $tblCertificate ?: null);
                        }

                        $periodSection->addElementColumn((new Element())
                            ->setContent($certificateGrade)
                            ->styleTextSize(self::TEXT_SIZE_BODY)
                            ->stylePaddingLeft($paddingLeft)
                            ->styleTextBold()
                            ->styleBorderTop()
                            ->styleBorderLeft()
                            ->styleBorderRight($isCertificateGradeLastColumn ? '1px' : '0px')
                            ->styleBackgroundColor(self::COLOR_BODY_DARK)
                            , $widthColumnTestString);
                    }

                    $section
                        ->addSliceColumn((new Slice)
                            ->addSection($periodSection)
                            , ($countTestPeriod * $widthColumnTest) . '%'
                        );
                }

                $slice->addSection($section);
            }
        }

        /**
         * Test Fach-Klassen-Durchschnitt
         */
        $subSection = new Section();
        $subSection
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleTextSize(self::TEXT_SIZE_BODY)
                ->stylePaddingLeft($paddingLeft)
                ->styleBorderTop()
                ->styleBorderLeft()
                ->styleBorderBottom()
                ->styleBackgroundColor(self::COLOR_BODY_DARK)
                , $widthNumber)
            ->addElementColumn((new Element())
                ->setContent('&#216;' . '&nbsp;' . 'Fach-Klasse')
                ->styleTextSize(self::TEXT_SIZE_BODY)
                ->stylePaddingLeft($paddingLeft)
                ->styleBorderTop()
                ->styleBorderLeft()
                ->styleBorderBottom()
                ->styleTextItalic()
                ->styleBackgroundColor(self::COLOR_BODY_DARK)
                , $widthStudentName);
        if ($showCourse) {
            $subSection
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleTextSize(self::TEXT_SIZE_BODY)
                    ->stylePaddingLeft($paddingLeft)
                    ->styleBorderTop()
                    ->styleBorderLeft()
                    ->styleBorderBottom()
                    ->styleBackgroundColor(self::COLOR_BODY_DARK)
                    , $widthCourse);
        }

        $section = new Section();
        $section
            ->addSliceColumn((new Slice)
                ->addSection($subSection)
                , $widthStudentColumnString
            );

        if (isset($periodListCount[$tblPeriod->getId()])) {
            $countTestPeriod = $periodListCount[$tblPeriod->getId()];
            $periodSection = new Section();
            $countTests = 0;
            if (isset($testList[$tblPeriod->getId()])) {
                foreach ($testList[$tblPeriod->getId()] as $testId => $text) {
                    if (($tblTest = Grade::useService()->getTestById($testId))
                        && ($tblGradeType = $tblTest->getTblGradeType())
                    ) {
                        $countTests++;
                        $testAverage = isset($tblTestGradeListByTest[$tblTest->getId()])
                            ? Grade::useService()->getGradeAverage(
                                $tblTestGradeListByTest[$tblTest->getId()]['Sum'], $tblTestGradeListByTest[$tblTest->getId()]['Count']
                            )
                            : '&nbsp;';

                        $periodSection->addElementColumn((new Element())
                            ->setContent($testAverage)
                            ->styleTextSize(self::TEXT_SIZE_BODY)
                            ->stylePaddingLeft($paddingLeft)
                            ->styleTextBold($tblGradeType->getIsHighlighted() ? 'bold' : 'normal')
                            ->styleTextItalic()
                            ->styleBorderTop()
                            ->styleBorderLeft()
                            ->styleBorderBottom()
                            ->styleBorderRight($isLastTestLastColumn && $countTests == $countTestPeriod
                                ? '1px' : '0px')
                            ->styleBackgroundColor(self::COLOR_BODY_DARK)
                            , $widthColumnTestString);
                    }
                }
            }

            // leer Spalten auffüllen
            if ($countTests < $countTestPeriod - $offset) {
                for (; $countTests < $countTestPeriod - $offset; $countTests++){
                    $periodSection->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleTextSize(self::TEXT_SIZE_BODY)
                        ->stylePaddingLeft($paddingLeft)
                        ->styleBorderTop()
                        ->styleBorderLeft()
                        ->styleBorderBottom()
                        ->styleBorderRight($isLastTestLastColumn && $countTests == ($countTestPeriod - 1)
                            ? '1px' : '0px')
                        ->styleBackgroundColor(self::COLOR_BODY_DARK)
                        , $widthColumnTestString);
                }
            }

            if ($showAverage) {
                $periodSection->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleTextSize(self::TEXT_SIZE_BODY)
                    ->stylePaddingLeft($paddingLeft)
                    ->styleTextBold()
                    ->styleBorderTop()
                    ->styleBorderLeft()
                    ->styleBorderBottom()
                    ->styleBorderRight($isAverageLastColumn ? '1px' : '0px')
                    ->styleBackgroundColor(self::COLOR_BODY_DARK)
                    , $widthColumnTestString);
            }

            if ($showCertificateGrade) {
                $periodSection->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleTextSize(self::TEXT_SIZE_BODY)
                    ->stylePaddingLeft($paddingLeft)
                    ->styleTextBold()
                    ->styleBorderTop()
                    ->styleBorderLeft()
                    ->styleBorderBottom()
                    ->styleBorderRight($isCertificateGradeLastColumn ? '1px' : '0px')
                    ->styleBackgroundColor(self::COLOR_BODY_DARK)
                    , $widthColumnTestString);
            }

            $section
                ->addSliceColumn((new Slice)
                    ->addSection($periodSection)
                    , ($countTestPeriod * $widthColumnTest) . '%'
                );
        }

        $slice->addSection($section);

        return $slice;
    }

    /**
     * @param Section $section
     * @param $text
     * @param $width
     * @param $countHeaders
     * @param bool $isBold
     * @param bool $hasBorderRight
     *
     * @return Section
     */
    private function setHeaderTest(Section $section, $text, $width, $countHeaders, bool $isBold = false, bool $hasBorderRight = false): Section
    {
        $section->addElementColumn((new Element())
            ->setContent($this->setRotatedContend($text, $countHeaders))
            ->styleTextSize(self::TEXT_SIZE_HEADER)
            ->styleHeight(self::HEIGHT_HEADER . 'px')
            ->styleTextBold($isBold ? 'bold' : 'normal')
            ->styleBackgroundColor(self::COLOR_HEADER)
            ->styleBorderLeft()
            ->styleBorderRight($hasBorderRight ? '1px' : '0px')
            , $width);

        return $section;
    }

    /**
     * @param string $text
     * @param $countHeaders
     *
     * @return string
     */
    protected function setRotatedContend(string $text, $countHeaders): string
    {

        // für Zeilenumbruch im Thema des Tests
        $height = self::HEIGHT_HEADER . 'px';

        // paddingTop abhängig von der anzahl der Spalten, mehr Spalten $paddingTop = '-140px'
        if ($countHeaders < 6) {
            $paddingTop = '-150px';
        } elseif ($countHeaders < 8) {
            $paddingTop = '-140px';
        } elseif ($countHeaders < 10) {
            $paddingTop = '-130px';
        } else {
            $paddingTop = '-130px';
        }

//        $paddingTop = '-150px';
        $paddingLeft = '-260px';

        if (strlen($text) > 90) {
            $text = substr($text, 0, 90);
        }

        return
            '<div style="padding-top: ' . $paddingTop
            . '!important; padding-left: ' . $paddingLeft
            . '!important; transform: rotate(270deg)!important;'
            . 'max-width: ' . $height . ';'
            // geht erst ab dompdf 0.8.1
//            . ' white-space: nowrap!important;'
            . '">'
            . $text
            . '</div>';
    }

    /**
     * @param $tblTestList
     *
     * @return array
     */
    public function sortTestList($tblTestList): array
    {
        if (($tblSetting = Consumer::useService()->getSetting(
                'Education', 'Graduation', 'Gradebook', 'SortHighlighted'
            ))
            && $tblSetting->getValue()
        ) {
            // Sortierung nach Großen (fettmarkiert) und Klein Noten
            $highlightedTests = array();
            $notHighlightedTests = array();
            $countTests = 1;
            $isHighlightedSortedRight = true;
            if (($tblSettingSortedRight = Consumer::useService()->getSetting(
                'Education', 'Graduation', 'Gradebook', 'IsHighlightedSortedRight'
            ))
            ) {
                $isHighlightedSortedRight = $tblSettingSortedRight->getValue();
            }
            /** @var TblTest $tblTestItem */
            foreach ($tblTestList as $tblTestItem) {
                if (($tblGradeType = $tblTestItem->getTblGradeType())) {
                    if ($tblGradeType->getIsHighlighted()) {
                        $highlightedTests[$countTests++] = $tblTestItem;
                    } else {
                        $notHighlightedTests[$countTests++] = $tblTestItem;
                    }
                }
            }

            $tblTestList = array();
            if (!empty($notHighlightedTests)) {
                $tblTestList = (new Extension())->getSorter($notHighlightedTests)->sortObjectBy('Date', new DateTimeSorter());
            }
            if (!empty($highlightedTests)) {
                $highlightedTests = (new Extension())->getSorter($highlightedTests)->sortObjectBy('Date', new DateTimeSorter());

                if ($isHighlightedSortedRight) {
                    $tblTestList = array_merge($tblTestList, $highlightedTests);
                } else {
                    $tblTestList = array_merge($highlightedTests, $tblTestList);
                }
            }
        } else {
            // Sortierung der Tests nach Datum
            $tblTestList = (new Extension())->getSorter($tblTestList)->sortObjectBy('Date', new DateTimeSorter());
        }

        return $tblTestList;
    }
}