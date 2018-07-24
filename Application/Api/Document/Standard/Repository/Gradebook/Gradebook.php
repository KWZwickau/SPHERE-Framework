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
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class Gradebook
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\Gradebook
 */
class Gradebook
{

    const TEXT_SIZE_HEADER = '10pt';// '12px';
    const TEXT_SIZE_BODY = '9pt';// '11px';
    const HEIGHT_HEADER = 490;

    /** @var null|Frame $Document */
    private $Document = null;

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return \MOC\V\Component\Template\Component\IBridgeInterface
     */
    public function createSingleDocument(TblDivisionSubject $tblDivisionSubject)
    {

        $pageList[] = $this->buildPage($tblDivisionSubject);
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
        if (($tblDivisionSubjectAll = Division::useService()->getDivisionSubjectByDivision($tblDivision))) {
            foreach ($tblDivisionSubjectAll as $tblDivisionSubject) {
                $pageList[] = $this->buildPage($tblDivisionSubject);
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
     *
     * @return Page
     */
    public function buildPage(TblDivisionSubject $tblDivisionSubject)
    {

        if (($tblDivision = $tblDivisionSubject->getTblDivision())
            && ($tblSubject = $tblDivisionSubject->getServiceTblSubject())
        ) {

            $tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup();

            return (new Page())
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent(
                            'Klasse ' . $tblDivision->getDisplayName() . ' - ' . $tblSubject->getDisplayName()
                            .  ($tblSubjectGroup ? new Small(' (Gruppe: ' . $tblSubjectGroup->getName() . ')') : '')
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
                ->addSlice($this->setContent($tblDivision, $tblSubject, $tblSubjectGroup ? $tblSubjectGroup : null))
                ;
        }

        return (new Page());
    }

    private function setContent(TblDivision $tblDivision, TblSubject $tblSubject, TblSubjectGroup $tblSubjectGroup = null)
    {

        $slice = new Slice();
        $paddingLeft = '5px';
//        $minimumTestCountProPeriod = 5;

        $widthStudentColumn = 30;
        $widthPeriodColumns = 100 - $widthStudentColumn;
        $widthStudentColumnString = $widthStudentColumn . '%';

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
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('#')
                        ->styleTextSize(self::TEXT_SIZE_HEADER)
                        ->stylePaddingLeft($paddingLeft)
                        ->styleTextBold()
                        ->styleBorderLeft()
                        , '10%')
                    ->addElementColumn((new Element())
                        ->setContent('Schüler')
                        ->styleTextSize(self::TEXT_SIZE_HEADER)
                        ->stylePaddingLeft($paddingLeft)
                        ->styleTextBold()
                        ->styleBorderLeft()
                        , '70%')
                    ->addElementColumn((new Element())
                        ->setContent('Bg')
                        ->styleTextSize(self::TEXT_SIZE_HEADER)
                        ->stylePaddingLeft($paddingLeft)
                        ->styleTextBold()
                        ->styleBorderLeft()
                        , '20%')
                )
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
//        $countPeriod = 0;
        if ($tblPeriodList) {
            foreach ($tblPeriodList as $tblPeriod) {
                $count = 0;
//                $countPeriod++;
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
                            $date = $tblTest->getDate();
                            if (strlen($date) > 6) {
                                $date = substr($date, 0, 6);
                            }

                            // todo cut description
                            $text = $date . ' '.
                                $tblGradeType->getCode() . ' '
                                . trim($tblTest->getDescription());

                            if (!empty($text)) {
                                $text = str_replace(' ', '&nbsp;', $text);
                                $text = str_replace('-', '&nbsp;', $text);
                            }
//                            Debugger::screenDump($text, strlen(str_replace('&nbsp;', '', $text)));
//                            if (strlen($text)> 20) {
//                                $text = substr($text, 0, 20);
//                                Debugger::screenDump($text);
//                            }

                            $testList[$tblPeriod->getId()][$tblTest->getId()] = $text;
//
//                            $columnDefinition['Test' . $tblTest->getId()] = $tblTest->getDescription()
//                                ? (new ToolTip($text, htmlspecialchars($tblTest->getDescription())))->enableHtml()
//                                : $text;
//
                            // todo für Schüler, welche nicht mehr in der Klasse sind
                            $tblGradeList = \SPHERE\Application\Education\Graduation\Gradebook\Gradebook::useService()->getGradeAllByTest($tblTest);
                            if ($tblGradeList) {
                                foreach ($tblGradeList as $tblGradeItem) {
                                    if (($tblPersonItem = $tblGradeItem->getServiceTblPerson())
                                        && !isset($studentArray[$tblPersonItem->getId()])
                                    ) {
                                        $addStudentList[$tblPersonItem->getId()] = $tblPersonItem;
                                    }
                                }
                            }
                        }
                    }
//                    $columnDefinition['PeriodAverage' . $tblPeriod->getId()] = '&#216;';
                    $count++;
                    $periodListCount[$tblPeriod->getId()] = $count;
                } else {
                    $periodListCount[$tblPeriod->getId()] = 1;
//                    $columnDefinition['Period' . $tblPeriod->getId()] = "";
                }
            }

            $sumCount = array_sum($periodListCount);
            // für gesamt Durchschnitt;
            $sumCount++;
            if ($sumCount > 0) {
//                Debugger::screenDump($sumCount, $widthPeriodColumns, $widthColumnTest);
                $widthColumnTest = $widthPeriodColumns / $sumCount;
                $widthColumnTestString = $widthColumnTest . '%';
            }
            $tblPeriod = false;
            foreach ($tblPeriodList as $tblPeriod) {
                if (isset($periodListCount[$tblPeriod->getId()])) {
                    $countTestPeriod = $periodListCount[$tblPeriod->getId()];
                    $headerSection = new Section();
                    // todo leeres Halbjahr
                    if (isset($testList[$tblPeriod->getId()])) {
                        foreach ($testList[$tblPeriod->getId()] as $text) {
                            // todo fettmarkierte Zensuren-Typen bold?
                            $headerSection = $this->setHeaderTest($headerSection, $text, $widthColumnTestString);
                        }

                        $headerSection = $this->setHeaderTest(
                            $headerSection,
                            '&#216;' . '&nbsp;' . $tblPeriod->getName(),
                            $widthColumnTestString,
                            true
                        );
                    }

                    $section
                        ->addSliceColumn((new Slice)
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    // todo Zeitraum anzeigen
//                                    ->setContent($tblPeriod->getDisplayName())
                                    ->setContent($tblPeriod->getName())
                                    ->styleTextSize(self::TEXT_SIZE_HEADER)
                                    ->stylePaddingLeft($paddingLeft)
                                    ->styleTextBold()
                                    ->styleBorderLeft()
                                    ->styleBorderTop()
                                )
                            )
                            ->addSection($headerSection)
                            , ($countTestPeriod * $widthColumnTest) . '%'
                        );
                }
            }
            $section
                ->addSliceColumn((new Slice)
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
//                            ->styleHeight(self::HEIGHT_HEADER . 'px')
                            ->styleTextSize(self::TEXT_SIZE_HEADER)
                            ->stylePaddingLeft()
                            ->styleTextBold()
                            ->styleBorderLeft()
                            ->styleBorderTop()
                            ->styleBorderRight()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
//                            ->setContent('&#216;')
//                            ->styleTextSize(self::TEXT_SIZE_HEADER)
//                            ->stylePaddingLeft($paddingLeft)
//                            ->styleTextBold()
                                // todo rotation
                            ->setContent('&#216;')
//                            ->setContent($this->setRotatedContend('&#216;' . '&nbsp;' . 'Gesamt'))
//                            ->setContent($this->setRotatedContend('&#216;'))
                            ->stylePaddingLeft($paddingLeft)
                            ->styleTextSize(self::TEXT_SIZE_HEADER)
                            ->styleHeight(self::HEIGHT_HEADER . 'px')
                            ->styleTextBold()
                            ->styleBorderLeft()
                            ->styleBorderRight()
                        )
                    )
//                    , $widthTotalColumnString
                    , $widthColumnTestString
                );
        }

        $slice->addSection($section);

        /**
         * Body
         */
        if (($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))){
            $number = 1;
            // todo additional Students
            foreach ($tblPersonList as $tblPerson) {
                $courseName = '&nbsp;';
                if (($tblStudent = $tblPerson->getStudent())
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

                $section = new Section();

                $section
                    ->addSliceColumn((new Slice)
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent($number++)
                                ->styleTextSize(self::TEXT_SIZE_BODY)
                                ->stylePaddingLeft($paddingLeft)
                                ->styleBorderTop()
                                ->styleBorderLeft()
                                , '10%')
                            ->addElementColumn((new Element())
                                ->setContent($tblPerson->getLastFirstName())
                                ->styleTextSize(self::TEXT_SIZE_BODY)
                                ->stylePaddingLeft($paddingLeft)
                                ->styleBorderTop()
                                ->styleBorderLeft()
                                , '70%')
                            ->addElementColumn((new Element())
                                ->setContent($courseName)
                                ->styleTextSize(self::TEXT_SIZE_BODY)
                                ->stylePaddingLeft($paddingLeft)
                                ->styleBorderTop()
                                ->styleBorderLeft()
                                , '20%')
                        )
                        , $widthStudentColumnString
                    );

                $tblPeriod = false;
                foreach ($tblPeriodList as $tblPeriod) {
                    if (isset($periodListCount[$tblPeriod->getId()])) {
                        $countTestPeriod = $periodListCount[$tblPeriod->getId()];
                        $periodSection = new Section();
                        if (isset($testList[$tblPeriod->getId()])) {
                            foreach ($testList[$tblPeriod->getId()] as $testId => $text) {
                                if (($tblTest = Evaluation::useService()->getTestById($testId))) {
                                    $grade = '&nbsp;';
                                    // todo Datum bei mündlichen Noten, eventuell breite gleich 2 Spalten
                                    if (($tblGrade = \SPHERE\Application\Education\Graduation\Gradebook\Gradebook::useService()
                                        ->getGradeByTestAndStudent($tblTest, $tblPerson))
                                        && $tblGrade->getGrade()
                                    ) {
                                        $grade = $tblGrade->getDisplayGrade();
                                    }

                                    // todo fettmarkierte Zensuren-Typen bold?
                                    $periodSection->addElementColumn((new Element())
                                        ->setContent($grade)
                                        ->styleTextSize(self::TEXT_SIZE_BODY)
                                        ->stylePaddingLeft($paddingLeft)
                                        ->styleBorderTop()
                                        ->styleBorderLeft()
                                        , $widthColumnTestString);
                                }
                            }

                            /*
                             * Calc Average Period
                             */
                            $average = \SPHERE\Application\Education\Graduation\Gradebook\Gradebook::useService()->calcStudentGrade(
                                $tblPerson,
                                $tblDivision,
                                $tblSubject,
                                $tblTestType,
                                $tblScoreRule ? $tblScoreRule : null,
                                $tblPeriod,
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
                            }

                            $periodSection->addElementColumn((new Element())
                                ->setContent($average)
                                ->styleTextSize(self::TEXT_SIZE_BODY)
                                ->stylePaddingLeft($paddingLeft)
                                ->styleTextBold()
                                ->styleBorderTop()
                                ->styleBorderLeft()
                                , $widthColumnTestString);
                        }

                        $section
                            ->addSliceColumn((new Slice)
                                ->addSection($periodSection)
                                , ($countTestPeriod * $widthColumnTest) . '%'
                            );
                    }
                }

                /*
                 * Calc Average Total
                 */
                $average = \SPHERE\Application\Education\Graduation\Gradebook\Gradebook::useService()->calcStudentGrade(
                    $tblPerson,
                    $tblDivision,
                    $tblSubject,
                    $tblTestType,
                    $tblScoreRule ? $tblScoreRule : null,
                    null,
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
                }

                $section
                    ->addSliceColumn((new Slice)
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent($average)
                                ->styleTextSize(self::TEXT_SIZE_BODY)
                                ->stylePaddingLeft($paddingLeft)
                                ->styleTextBold()
                                ->styleBorderTop()
                                ->styleBorderLeft()
                                ->styleBorderRight()
                            )
                        )
                        , $widthColumnTestString
                    );

                $slice->addSection($section);
            }
        }

        $slice
            ->addElement((new Element())
                ->styleBorderTop()
            );

        return $slice;
    }

    /**
     * @param Section $section
     * @param $text
     * @param $width
     * @param bool $isBold
     *
     * @return Section
     */
    private function setHeaderTest(Section $section, $text, $width, $isBold = false)
    {
        $section->addElementColumn((new Element())
            ->setContent($this->setRotatedContend($text))
            ->styleTextSize(self::TEXT_SIZE_HEADER)
            ->styleHeight(self::HEIGHT_HEADER . 'px')
            ->styleTextBold($isBold ? 'bold' : 'normal')
            ->styleBorderLeft()
            , $width);

        return $section;
    }

//    public function setBodyRow(TblPerson)
//    {
//
//    }

    /**
     * @param string $text
     * @param string $paddingTop
     *
     * @return string
     */
    protected function setRotatedContend($text = '&nbsp;', $paddingTop = '2px')
    {

        $paddingLeft = (15 - self::HEIGHT_HEADER) . 'px';

        return
            '<div style="padding-top: ' . $paddingTop
            . '!important; padding-left: ' . $paddingLeft
            . '!important; transform: rotate(270deg)!important;'
            // geht erst ab dompdf 0.8.1
//            . ' white-space: nowrap!important;'
            . '">'
            . $text
            . '</div>';
    }
}