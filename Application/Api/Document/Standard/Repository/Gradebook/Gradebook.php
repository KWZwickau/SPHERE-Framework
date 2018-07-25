<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 18.07.2018
 * Time: 08:36
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\Gradebook;

use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Text\Repository\Small;

/**
 * Class Gradebook
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\Gradebook
 */
class Gradebook
{

    const TEXT_SIZE_HEADER = '8pt';// '12px';
    const TEXT_SIZE_BODY = '8pt';// '11px';
    const HEIGHT_HEADER = 450;
    const COLOR_HEADER = 'darkgray';
    const COLOR_BODY_ALTERNATE_1 = '#E4E4E4';
    const COLOR_BODY_ALTERNATE_2 = '#FFF';

    /** @var null|Frame $Document */
    private $Document = null;

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return \MOC\V\Component\Template\Component\IBridgeInterface
     */
    public function createSingleDocument(TblDivisionSubject $tblDivisionSubject)
    {

        $pageList = array();
        if (($tblDivision = $tblDivisionSubject->getTblDivision())
            && ($tblYear = $tblDivision->getServiceTblYear())
            && ($tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear))
        ) {
            $count = 0;
            foreach ($tblPeriodList as $tblPeriod) {
                $count++;
                $isLastPeriod = $count == count($tblPeriodList);

                $pageList[] = $this->buildPage($tblDivisionSubject, $tblPeriod, $isLastPeriod);
            }
        }

        $this->Document = $this->buildDocument($pageList);

        return $this->Document->getTemplate();
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return \MOC\V\Component\Template\Component\IBridgeInterface
     */
    public function createMultiDocument(TblDivision $tblDivision)
    {

        $pageList = array();
        if (($tblDivisionSubjectAll = Division::useService()->getDivisionSubjectByDivision($tblDivision))
            && ($tblYear = $tblDivision->getServiceTblYear())
            && ($tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear))
        ) {
            $subjectCount = 0;
            // todo Sortierung
            foreach ($tblDivisionSubjectAll as $tblDivisionSubject) {
                $subjectCount++;
                $count = 0;
                foreach ($tblPeriodList as $tblPeriod) {
                    $count++;
                    $isLastPeriod = $count == count($tblPeriodList);

                    $pageList[] = $this->buildPage($tblDivisionSubject, $tblPeriod, $isLastPeriod);
                }

                // todo entfernen
                if ($subjectCount > 5) {
                    break;
                }
            }
        }

        $this->Document = $this->buildDocument($pageList);

        return $this->Document->getTemplate();
    }

    /**
     * @param array $pageList
     *
     * @return Frame
     */
    public function buildDocument($pageList = array())
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
     * @param TblDivisionSubject $tblDivisionSubject
     * @param TblPeriod $tblPeriod
     * @param bool $isLastPeriod
     *
     * @return Page
     */
    public function buildPage(
        TblDivisionSubject $tblDivisionSubject,
        TblPeriod $tblPeriod,
        $isLastPeriod = false
    ) {

        if (($tblDivision = $tblDivisionSubject->getTblDivision())
            && ($tblSubject = $tblDivisionSubject->getServiceTblSubject())
        ) {

            $tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup();

            return (new Page())
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Notenbuch')
                        ->styleTextSize('20px')
                        ->styleTextBold()
                    )
                    ->addElement((new Element())
                        ->setContent(
                            'Klasse ' . $tblDivision->getDisplayName() . ' - ' . $tblSubject->getDisplayName()
                            . ($tblSubjectGroup ? new Small(' (Gruppe: ' . $tblSubjectGroup->getName() . ')') : '')
                        )
                        ->styleTextSize('20px')
                        ->styleTextBold()
                    )
                    ->addElement((new Element())
                        ->setContent(
                            'Fachlehrer: ' . Division::useService()->getSubjectTeacherNameList(
                                $tblDivision, $tblSubject, $tblDivisionSubject->getTblSubjectGroup()
                                ? $tblDivisionSubject->getTblSubjectGroup() : null
                            )
                        )
                    )
                )
                ->addSlice((new Slice())->addElement((new Element())->styleHeight('20px')))
                ->addSlice(
                    $this->setContent(
                        $tblDivision,
                        $tblSubject,
                        $tblSubjectGroup ? $tblSubjectGroup : null,
                        $tblPeriod,
                        $isLastPeriod
                    )
                );
        }

        return (new Page());
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup|null $tblSubjectGroup
     * @param TblPeriod $tblPeriod
     * @param bool $isLastPeriod
     *
     * @return Slice
     */
    private function setContent(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup = null,
        TblPeriod $tblPeriod,
        $isLastPeriod = false
    ) {

        $slice = new Slice();
        $paddingLeft = '3px';

        $widthStudentColumn = 30;
        $widthPeriodColumns = 100 - $widthStudentColumn;
        $widthStudentColumnString = $widthStudentColumn . '%';

        $showCourse = false;
        $isSekTwo = false;
        if (($tblLevel = $tblDivision->getTblLevel())
            && ($tblType = $tblLevel->getServiceTblType())
        ) {
            if ($tblType->getName() == 'Mittelschule / Oberschule'
                && intval($tblLevel->getName()) > 6
            ) {
                $showCourse = true;
            } elseif ($tblType->getName() == 'Gymnasium'
                && intval($tblLevel->getName()) > 10
            ) {
                $isSekTwo = true;
            }
        }
        if ($showCourse) {
            $widthNumber = '9%';
            $widthStudentName = '76%';
            $widthCourse = '15%';
        } else {
            $widthNumber = '9%';
            $widthStudentName = '91%';
            $widthCourse = '0%';
        }

        // wird dynamisch nach der Testanzahl angepasst
        $widthColumnTest = 10;
        $widthColumnTestString = '10%';

        $tblScoreRule = \SPHERE\Application\Education\Graduation\Gradebook\Gradebook::useService()->getScoreRuleByDivisionAndSubjectAndGroup(
            $tblDivision,
            $tblSubject,
            $tblSubjectGroup ? $tblSubjectGroup : null
        );

        $tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
            $tblDivision,
            $tblSubject,
            $tblSubjectGroup ? $tblSubjectGroup : null
        );

        $showAverage = false;
        if (($tblSettingAverage = Consumer::useService()->getSetting('Education', 'Graduation', 'Gradebook', 'ShowAverageInPdf'))) {
            $showAverage = $tblSettingAverage->getValue();
        }

        // todo showAppointedDateGrades
//        $showAppointedDateGrades = false;
//        if (($tblSettingAppointedDateGrades = Consumer::useService()->getSetting('Education', 'Graduation', 'Gradebook', 'ShowAppointedDateGradesInPdf'))) {
//            $showAppointedDateGrades = $tblSettingAppointedDateGrades->getValue();
//        }

        // todo tatsächlich ermitteln
        $isLastTestLastColumn = false;
        $isAverageLastColumn = $showAverage;
        if (!$isAverageLastColumn) {
            $isLastTestLastColumn = true;
        }

        if ($tblSubjectGroup) {
            $tblPersonList = Division::useService()->getStudentByDivisionSubject($tblDivisionSubject);
        } elseif ($tblDivisionSubject) {
            $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        } else {
            $tblPersonList = false;
        }

        $addStudentList = array();
        $existingPersonList = array();
        if ($tblPersonList) {
            foreach ($tblPersonList as $tblPerson) {
                $existingPersonList[$tblPerson->getId()] = $tblPerson;
            }
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

        $tblYear = $tblDivision->getServiceTblYear();
        if ($tblYear) {
            $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);
        } else {
            $tblPeriodList = false;
        }
        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST');

        $periodListCount = array();
        $testList = array();

        $count = 0;
        $tblTestList = Evaluation::useService()->getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
            $tblDivision,
            $tblSubject,
            $tblTestType,
            $tblPeriod,
            $tblSubjectGroup
        );
        if ($tblTestList) {
            $tblTestList = Evaluation::useService()->sortTestList($tblTestList);
            /** @var TblTest $tblTest */
            foreach ($tblTestList as $tblTest) {
                if (($tblGradeType = $tblTest->getServiceTblGradeType())) {
                    $count++;
                    if ($tblTest->getDate()) {
                        $date = $tblTest->getDate();
                        if (strlen($date) > 6) {
                            $date = substr($date, 0, 6);
                        }
                    } elseif ($tblTest->isContinues() && $tblTest->getFinishDate()) {
                        $date = $tblTest->getFinishDate();
                        if (strlen($date) > 6) {
                            $date = '(' . substr($date, 0, 6) . ')';
                        }
                    } else {
                        $date = '';
                    }

                    $text = trim($date . ' ' .
                        $tblGradeType->getCode() . ' '
                        . trim($tblTest->getDescription()));

                    if (!empty($text)) {
                        $text = str_replace(' ', '&nbsp;', $text);
                        $text = str_replace('-', '&nbsp;', $text);
                    }

                    $testList[$tblPeriod->getId()][$tblTest->getId()] = $text;

                    // für Schüler, welche nicht mehr in der Klasse sind
                    $tblGradeList = \SPHERE\Application\Education\Graduation\Gradebook\Gradebook::useService()->getGradeAllByTest($tblTest);
                    if ($tblGradeList) {
                        foreach ($tblGradeList as $tblGradeItem) {
                            if (($tblPersonItem = $tblGradeItem->getServiceTblPerson())
                                && !isset($existingPersonList[$tblPersonItem->getId()])
                            ) {
                                $addStudentList[$tblPersonItem->getId()] = $tblPersonItem;
                            }
                        }
                    }
                }
            }


            if ($showAverage) {
                $count++;
            }
            // todo Stichtagsnoten
            $periodListCount[$tblPeriod->getId()] = $count;
        } else {
            // todo keine Tests (leeres Halbjahr)
            // für gesamt Durchschnitt;
            if ($isLastPeriod) {
                $periodListCount[$tblPeriod->getId()] = 2;
            } else {
                $periodListCount[$tblPeriod->getId()] = 1;
            }
        }

        $sumCount = array_sum($periodListCount);
        if ($sumCount > 0) {
            $widthColumnTest = $widthPeriodColumns / $sumCount;
            $widthColumnTestString = $widthColumnTest . '%';
        }

        if (isset($periodListCount[$tblPeriod->getId()])) {
            $countTestPeriod = $periodListCount[$tblPeriod->getId()];
            $headerSection = new Section();
            if (isset($testList[$tblPeriod->getId()])) {
                $countTests = 0;
                foreach ($testList[$tblPeriod->getId()] as $testId => $text) {
                    $countTests++;
                    if (($tblTest = Evaluation::useService()->getTestById($testId))
                        && ($tblGradeType = $tblTest->getServiceTblGradeType())
                    ) {
                        $headerSection = $this->setHeaderTest(
                            $headerSection,
                            $text,
                            $widthColumnTestString,
                            $tblGradeType->isHighlighted(),
                            $isLastTestLastColumn && $countTests == count($testList[$tblPeriod->getId()])
                        );
                    }
                }

                if ($showAverage) {
                    if (!$isSekTwo && $isLastPeriod) {
                        // Gesamtdurchschnitt
                        if ($isLastPeriod) {
                            $headerSection = $this->setHeaderTest(
                                $headerSection,
                                '&#216;' . '&nbsp;' . 'Gesamtes Schuljahr',
                                $widthColumnTestString,
                                true,
                                $isAverageLastColumn
                            );
                        }
                    } else {
                        // Durchschnitt des Halbjahres
                        $headerSection = $this->setHeaderTest(
                            $headerSection,
                            '&#216;' . '&nbsp;' . $tblPeriod->getName(),
                            $widthColumnTestString,
                            true,
                            $isAverageLastColumn
                        );
                    }
                }
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

        if (!empty($addStudentList)) {
            if (!$tblPersonList) {
                $tblPersonList = array();
            }
            foreach ($addStudentList as $tblAddPerson) {
                $tblPersonList[$tblAddPerson->getId()] = $tblAddPerson;
            }
        }

        /**
         * Body
         */
        if ($tblPersonList) {
            $number = 1;
            foreach ($tblPersonList as $tblPerson) {
                $isMissing = isset($addStudentList[$tblPerson->getId()]);
                $name = $isMissing ? '<s>' . $tblPerson->getLastFirstName() . '</s>' : $tblPerson->getLastFirstName();

                $courseName = '&nbsp;';
                if ($showCourse
                    && ($tblStudent = $tblPerson->getStudent())
                    && ($tblCourse = $tblStudent->getCourse())
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
                        ->setContent($number++)
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

                foreach ($tblPeriodList as $tblPeriod) {
                    if (isset($periodListCount[$tblPeriod->getId()])) {
                        $countTestPeriod = $periodListCount[$tblPeriod->getId()];
                        $periodSection = new Section();
                        if (isset($testList[$tblPeriod->getId()])) {
                            $countTests = 0;
                            foreach ($testList[$tblPeriod->getId()] as $testId => $text) {
                                if (($tblTest = Evaluation::useService()->getTestById($testId))
                                    && ($tblGradeType = $tblTest->getServiceTblGradeType())
                                ) {
                                    $countTests++;
                                    $grade = '&nbsp;';
                                    if (($tblGrade = \SPHERE\Application\Education\Graduation\Gradebook\Gradebook::useService()
                                            ->getGradeByTestAndStudent($tblTest, $tblPerson))
                                        && $tblGrade->getGrade()
                                    ) {
                                        $grade = $tblGrade->getDisplayGrade();
                                    }

                                    $periodSection->addElementColumn((new Element())
                                        ->setContent($grade)
                                        ->styleTextSize(self::TEXT_SIZE_BODY)
                                        ->stylePaddingLeft($paddingLeft)
                                        ->styleTextBold($tblGradeType->isHighlighted() ? 'bold' : 'normal')
                                        ->styleBorderTop()
                                        ->styleBorderLeft()
                                        ->styleBorderRight($isLastTestLastColumn && $countTests == count($testList[$tblPeriod->getId()])
                                            ? '1px' : '0px')
                                        ->styleBackgroundColor($number % 2 == 1 ? self::COLOR_BODY_ALTERNATE_1 : self::COLOR_BODY_ALTERNATE_2)
                                        , $widthColumnTestString);
                                }
                            }

                            if ($showAverage) {
                                /*
                                 * Calc Average Period or Average Total
                                 */
                                $average = \SPHERE\Application\Education\Graduation\Gradebook\Gradebook::useService()->calcStudentGrade(
                                    $tblPerson,
                                    $tblDivision,
                                    $tblSubject,
                                    $tblTestType,
                                    $tblScoreRule ? $tblScoreRule : null,
                                    !$isSekTwo && $isLastPeriod ? null : $tblPeriod,
                                    $tblSubjectGroup ? $tblSubjectGroup : null
                                );

                                if (is_array($average)) {
                                    $average = 'f';
                                } elseif (is_string($average) && strpos($average,
                                        '(')
                                ) {
                                    $average = substr($average, 0,
                                        strpos($average, '('));
                                    $average = str_replace('.', ',', $average);
                                } else {
                                    $average = '&nbsp;';
                                }

                                $periodSection->addElementColumn((new Element())
                                    ->setContent($average)
                                    ->styleTextSize(self::TEXT_SIZE_BODY)
                                    ->stylePaddingLeft($paddingLeft)
                                    ->styleTextBold()
                                    ->styleBorderTop()
                                    ->styleBorderLeft()
                                    ->styleBorderRight($isAverageLastColumn ? '1px' : '0px')
                                    ->styleBackgroundColor(self::COLOR_HEADER)
                                    , $widthColumnTestString);
                            }
                        }

                        $section
                            ->addSliceColumn((new Slice)
                                ->addSection($periodSection)
                                , ($countTestPeriod * $widthColumnTest) . '%'
                            );
                    }
                }

                $slice->addSection($section);
            }
        }

        /**
         * Test Durchschnitt
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
                ->styleBackgroundColor(self::COLOR_HEADER)
                , $widthNumber)
            ->addElementColumn((new Element())
                ->setContent('&#216;' . '&nbsp;' . 'Fach-Klasse')
                ->styleTextSize(self::TEXT_SIZE_BODY)
                ->stylePaddingLeft($paddingLeft)
                ->styleBorderTop()
                ->styleBorderLeft()
                ->styleBorderBottom()
                ->styleTextItalic()
                ->styleBackgroundColor(self::COLOR_HEADER)
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
                    ->styleBackgroundColor(self::COLOR_HEADER)
                    , $widthCourse);
        }

        $section = new Section();
        $section
            ->addSliceColumn((new Slice)
                ->addSection($subSection)
                , $widthStudentColumnString
            );

        foreach ($tblPeriodList as $tblPeriod) {
            if (isset($periodListCount[$tblPeriod->getId()])) {
                $countTestPeriod = $periodListCount[$tblPeriod->getId()];
                $periodSection = new Section();
                if (isset($testList[$tblPeriod->getId()])) {
                    $countTests = 0;
                    foreach ($testList[$tblPeriod->getId()] as $testId => $text) {
                        if (($tblTest = Evaluation::useService()->getTestById($testId))
                            && ($tblGradeType = $tblTest->getServiceTblGradeType())
                        ) {
                            $countTests++;
                            $testAverage = \SPHERE\Application\Education\Graduation\Gradebook\Gradebook::useService()
                                ->getAverageByTest($tblTest);

                            $periodSection->addElementColumn((new Element())
                                ->setContent($testAverage ? str_replace('.', ',',$testAverage) : '&nbsp;')
                                ->styleTextSize(self::TEXT_SIZE_BODY)
                                ->stylePaddingLeft($paddingLeft)
                                ->styleTextBold($tblGradeType->isHighlighted() ? 'bold' : 'normal')
                                ->styleTextItalic()
                                ->styleBorderTop()
                                ->styleBorderLeft()
                                ->styleBorderBottom()
                                ->styleBorderRight($isLastTestLastColumn && $countTests == count($testList[$tblPeriod->getId()])
                                    ? '1px' : '0px')
                                ->styleBackgroundColor(self::COLOR_HEADER)
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
                            ->styleBackgroundColor(self::COLOR_HEADER)
                            , $widthColumnTestString);
                    }
                }

                $section
                    ->addSliceColumn((new Slice)
                        ->addSection($periodSection)
                        , ($countTestPeriod * $widthColumnTest) . '%'
                    );
            }
        }

        $slice->addSection($section);

//        $slice
//            ->addElement((new Element())
//                ->styleBorderTop()
//            );

        return $slice;
    }

    /**
     * @param Section $section
     * @param $text
     * @param $width
     * @param bool $isBold
     * @param bool $hasBorderRight
     *
     * @return Section
     */
    private function setHeaderTest(Section $section, $text, $width, $isBold = false, $hasBorderRight = false)
    {
        $section->addElementColumn((new Element())
            ->setContent($this->setRotatedContend($text))
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
     * @param string $paddingTop
     *
     * @return string
     */
    protected function setRotatedContend($text = '&nbsp;', $paddingTop = '2px')
    {

        $paddingLeft = (15 - self::HEIGHT_HEADER) . 'px';
        // für Zeilenumbruch im Thema des Tests
        $height = self::HEIGHT_HEADER . 'px';

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
}