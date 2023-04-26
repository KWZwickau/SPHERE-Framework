<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\GradebookOverview;

use DateTime;
use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Extension\Repository\Sorter;

/**
 * Class GradebookOverview
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\GradebookOverview
 */
class GradebookOverview extends AbstractDocument
{

    // inclusive average
    const MINIMUM_GRADE_COUNT = 6;

    function __construct()
    {

    }

    /**
     * @return string
     */
    public function getName()
    {

        return 'Notenübersicht';
    }


    /**
     * @param array $List
     *
     * @return Sorter
     */
    public function getSorter($List)
    {
        return new Sorter($List);
    }

    /**
     *
     * @param array $pageList
     * @param string $Part
     *
     * @return Frame
     */
    public function buildDocument($pageList = array(), $Part = '0')
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
     * @param TblPerson|null   $tblPerson
     * @param TblDivision|null $tblDivision
     *
     * @return Slice $PageHeader
     */
    public function getPageHeaderSlice(TblPerson $tblPerson = null, TblDivision $tblDivision = null)
    {
        return (new Slice())
            ->addSection((new Section())
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Schüler: ' . ($tblPerson ? $tblPerson->getLastFirstName() : ''))
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klasse: ' . ($tblDivision ? $tblDivision->getDisplayName() : ''))
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Stand: ' . (new DateTime())->format('d.m.Y'))
                        )
                    )
                    , '33%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Schülerübersicht')
                    ->styleAlignCenter()
                    ->styleTextSize('30px')
                    ->styleTextUnderline(), '34%'
                )
                ->addElementColumn((new Element())
                    ->setContent(''), '33%'
                )
            )->stylePaddingBottom('25px');
    }

    /**
     * @param TblPerson|null   $tblPerson
     * @param TblDivision|null $tblDivision
     *
     * @return Slice
     */
    public function getGradebookOverviewSlice(TblPerson $tblPerson = null, TblDivision $tblDivision = null)
    {

        if ($tblDivision
            && $tblPerson
            && ($tblYear = $tblDivision->getServiceTblYear())
        ) {
            $tblTestTypeAppointedDateTask = Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK');
            $divisionList = array();
            if ($tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson)) {
                foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                    if (($tblDivisionTemp = $tblDivisionStudent->getTblDivision())
                        && ($tblYearTemp = $tblDivisionTemp->getServiceTblYear())
                        && $tblYear->getId() == $tblYearTemp->getId()
                        && !$tblDivisionStudent->isInActive()
                    ) {
                        $divisionList[$tblDivisionTemp->getId()] = $tblDivisionTemp;
                    }
                }
            }

            $data = array();
            $maxGradesPerPeriodCount = array();
            $tblPeriodList = $tblYear->getTblPeriodAll($tblDivision);
            foreach ($divisionList as $tblDivision) {
                if (($tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision))) {
                    foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                        // Fächer ohne Bneotung werden nicht benötigt
                        if(!$tblDivisionSubject->getHasGrading()){
                            continue;
                        }
                        // Verbale Benotung soll nicht auf die Schülerübersicht
                        if(($tblSubject = $tblDivisionSubject->getServiceTblSubject())){
                            if(($tblScoreRuleDivisionSubject = Gradebook::useService()->getScoreRuleDivisionSubjectByDivisionAndSubject($tblDivision, $tblSubject))){
                                if(($tblScoreType = $tblScoreRuleDivisionSubject->getTblScoreType())){
                                    if($tblScoreType->getIdentifier() === 'VERBAL'){
                                        continue;
                                    }
                                }
                            }
                        }
                        if ($tblDivisionSubject->getServiceTblSubject() && $tblDivisionSubject->getTblDivision()) {
                            if (!$tblDivisionSubject->getTblSubjectGroup()) {
                                $hasStudentSubject = false;
                                $tblDivisionSubjectWhereGroup =
                                    Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
                                        $tblDivision,
                                        $tblDivisionSubject->getServiceTblSubject()
                                    );

                                if ($tblDivisionSubjectWhereGroup) {
                                    foreach ($tblDivisionSubjectWhereGroup as $tblDivisionSubjectGroup) {

                                        if (Division::useService()->getSubjectStudentByDivisionSubjectAndPerson($tblDivisionSubjectGroup,
                                            $tblPerson)
                                        ) {
                                            $hasStudentSubject = true;
                                        }
                                    }
                                } else {
                                    $hasStudentSubject = true;
                                }

                                if ($hasStudentSubject) {
                                    if ($tblPeriodList
                                        && ($tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST'))
                                    ) {
                                        $tblScoreRule = Gradebook::useService()->getScoreRuleByDivisionAndSubjectAndGroup(
                                            $tblDivisionSubject->getTblDivision(),
                                            $tblDivisionSubject->getServiceTblSubject(),
                                            $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
                                        );

                                        $appointedDateGradeList = Gradebook::useService()->getGradesAllByStudentAndYearAndSubject(
                                            $tblPerson,
                                            $tblYear,
                                            $tblDivisionSubject->getServiceTblSubject(),
                                            $tblTestTypeAppointedDateTask
                                        );

                                        $hasGrades = false;
                                        $yearGradeListForAverage = array();
                                        foreach ($tblPeriodList as $tblPeriod) {
                                            $maxCount = 0;
                                            $tblGradeList = Gradebook::useService()->getGradesAllByStudentAndYearAndSubject(
                                                $tblPerson,
                                                $tblYear,
                                                $tblDivisionSubject->getServiceTblSubject(),
                                                $tblTestType,
                                                $tblPeriod
                                            );
                                            $tblGradeListForAverage = $tblGradeList;

                                            // Stichtagsnoten den Halbjahren zuordnen
                                            if ($appointedDateGradeList) {
                                                if (!$tblGradeList) {
                                                    $tblGradeList = array();
                                                }
                                                /**@var TblGrade $appointedDateGrade **/
                                                foreach ($appointedDateGradeList as $appointedDateGrade) {
                                                    if (Gradebook::useService()->isAppointedDateGradeInPeriod($appointedDateGrade, $tblPeriod)) {
                                                        $tblGradeList[] = $appointedDateGrade;
                                                    }
                                                }
                                            }

                                            if ($tblGradeList) {
                                                $hasGrades = true;
                                                // Sortieren der Zensuren
                                                $gradeListSorted = $this->getSorter($tblGradeList)->sortObjectBy('DateForSorter', new Sorter\DateTimeSorter());

                                                if ($tblGradeListForAverage) {
                                                    $yearGradeListForAverage = array_merge($yearGradeListForAverage, $tblGradeListForAverage);
                                                }

                                                /**@var TblGrade $tblGrade * */
                                                foreach ($gradeListSorted as $tblGrade) {
                                                    $tblTest = $tblGrade->getServiceTblTest();
                                                    if ($tblTest && ($tblGrade->getGrade() !== null && $tblGrade->getGrade() !== '')) {
                                                        $data[$tblDivisionSubject->getServiceTblSubject()->getAcronym()]
                                                        [$tblPeriod->getId()][$tblTest->getId()] = $tblGrade;
                                                        $maxCount++;
                                                    }
                                                }

                                                // period Average
                                                $average = Gradebook::useService()->calcStudentGrade(
                                                    $tblPerson, $tblDivisionSubject->getTblDivision(),
                                                    $tblDivisionSubject->getServiceTblSubject(),
                                                    Evaluation::useService()->getTestTypeByIdentifier('TEST'),
                                                    $tblScoreRule ? $tblScoreRule : null, $tblPeriod,
                                                    $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null,
                                                    false,
                                                    false,
                                                    $tblGradeListForAverage ?: array()
                                                );
                                                if (is_array($average)) {
                                                    $average = 'Fehler';
                                                } elseif (is_string($average) && strpos($average,
                                                        '(')
                                                ) {
                                                    $average = substr($average, 0,
                                                        strpos($average, '('));
                                                }
                                                $data[$tblDivisionSubject->getServiceTblSubject()->getAcronym()]
                                                [$tblPeriod->getId()]['Average'] = $average;
                                                $maxCount++;

                                                if (isset($maxGradesPerPeriodCount[$tblPeriod->getId()])) {
                                                    if ($maxGradesPerPeriodCount[$tblPeriod->getId()] < $maxCount) {
                                                        $maxGradesPerPeriodCount[$tblPeriod->getId()] = $maxCount;
                                                    }
                                                } else {
                                                    $maxGradesPerPeriodCount[$tblPeriod->getId()] = $maxCount;
                                                }
                                            } else {
                                                // Anzahl Fächer nur auf 0 setzen, wen noch nicht vorhanden
                                                if(!isset($maxGradesPerPeriodCount[$tblPeriod->getId()])){
                                                    $maxGradesPerPeriodCount[$tblPeriod->getId()] = 0;
                                                }
                                                // Fächer ohne Zensuren auch mit anzeigen
                                                $data[$tblDivisionSubject->getServiceTblSubject()->getAcronym()][$tblPeriod->getId()] = array(
                                                    'Average' => ''
                                                );
                                            }
                                        }

                                        if ($hasGrades) {
                                            // Total average
                                            $average = Gradebook::useService()->calcStudentGrade(
                                                $tblPerson, $tblDivisionSubject->getTblDivision(),
                                                $tblDivisionSubject->getServiceTblSubject(),
                                                Evaluation::useService()->getTestTypeByIdentifier('TEST'),
                                                $tblScoreRule ? $tblScoreRule : null, null,
                                                $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null,
                                                false,
                                                false,
                                                $yearGradeListForAverage
                                            );
                                            if (is_array($average)) {
                                                $average = 'Fehler';
                                            } elseif (is_string($average) && strpos($average,
                                                    '(')
                                            ) {
                                                $average = substr($average, 0,
                                                    strpos($average, '('));
                                            }
                                            $data[$tblDivisionSubject->getServiceTblSubject()->getAcronym()]
                                            ['Total']['Average'] = $average;
                                        } else {
                                            // Fächer ohne Zensuren auch mit anzeigen
                                            $data[$tblDivisionSubject->getServiceTblSubject()->getAcronym()]['Total']['Average'] = '';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $widthSubject = 5;
            $widthSubjectString = $widthSubject . '%';

            // grade width
            $totalGradeCount = 0;
            $totalGradeCountPeriod = 5;
            foreach ($maxGradesPerPeriodCount as &$value) {
                if ($value < self::MINIMUM_GRADE_COUNT) {
                    $value = self::MINIMUM_GRADE_COUNT;
                }
                if($totalGradeCountPeriod < $value){
                    $totalGradeCountPeriod = $value;
                }
                $totalGradeCount += $value;
            }

//            // +1 für durchschnitt am Ende // Durchschnitt am Ende nun mit fester Breite
//            $totalGradeCount++;
            $widthGrade = (100 - ($widthSubject*2)) / ($totalGradeCountPeriod * 2);
            $widthGradeString = $widthGrade . '%';

            // header
            $count = 0;
            $slice = new Slice();
            $section = new Section();
            $section
                ->addElementColumn((new Element())
                    ->setContent('Fach')
                    ->styleTextBold()
                    ->stylePaddingTop('5px')
                    ->stylePaddingBottom('5px')
                    ->styleBackgroundColor('lightgrey')
                    , $widthSubjectString);
            if ($tblPeriodList) {
                foreach ($tblPeriodList as $tblPeriod) {
                    $width = ($widthGrade * ($totalGradeCountPeriod)) . '%';
                    $section
                        ->addElementColumn((new Element())
                            ->setContent($tblPeriod->getDisplayName())
                            ->styleBorderRight()
                            ->styleBorderLeft($count++ < 1 ? '1px' : '0px')
                            ->styleTextBold()
                            ->stylePaddingTop('5px')
                            ->stylePaddingBottom('5px')
                            ->styleBackgroundColor('lightgrey')
                            , $width);
                }
            }
            $section
                ->addElementColumn((new Element())
                    ->setContent('&#216;')
                    ->styleBorderRight()
                    ->styleTextBold()
                    ->stylePaddingTop('5px')
                    ->stylePaddingBottom('5px')
                    ->styleBackgroundColor('lightgrey')
                    , $widthSubjectString);
            $slice
                ->addSection($section)
                ->styleBorderTop()
                ->styleBorderLeft()
                ->styleAlignCenter();

            ksort($data);
            foreach ($data as $acronym => $periodArray) {
                $section = new Section();
                if(strpos($acronym, ' ')){
                    $section->addElementColumn((new Element())
                        ->setContent($acronym)
                        ->styleBorderTop()
                        ->stylePaddingTop('1.5px')
                        ->stylePaddingBottom('1px')
                        ->styleTextBold()
                        ->styleHeight('34px')
                        ->styleBackgroundColor('lightgrey')
                        , $widthSubjectString);
                } else {
                    $section->addElementColumn((new Element())
                        ->setContent($acronym)
                        ->styleBorderTop()
                        ->stylePaddingTop('10px')
                        ->stylePaddingBottom('9.5px')
                        ->styleTextBold()
                        ->styleBackgroundColor('lightgrey')
                        , $widthSubjectString);
                }
                if (is_array($periodArray)) {
                    $count = 0;
                    foreach ($tblPeriodList as $tblPeriod) {
                        if (isset($periodArray[$tblPeriod->getId()])) {
                            foreach ($periodArray[$tblPeriod->getId()] as $key => $tblGrade) {
                                if ($key != 'Average') {
                                    if (($tblTest = $tblGrade->getServiceTblTest())) {
                                        if ($tblTest->isContinues()) {
                                            if ($tblGrade->getDate()) {
                                                $date = $tblGrade->getDate();
                                            } else {
                                                $date = $tblTest->getFinishDate();
                                            }
                                        } else {
                                            $date = $tblTest->getDate();
                                        }
                                        $GradeFontSize = '10px';
                                        if($totalGradeCount > 25){
                                            // Notfall Plan kleinere Schrift
                                            $GradeFontSize = '8px';
                                        }

                                        if (strlen($date) > 5) {
                                            $date = substr($date, 0, 5);
                                        }
                                        $tblGradeTypeTest = $tblTest->getServiceTblGradeType();
                                        $text = $date
                                            . '<br>'
                                            . ($tblGradeTypeTest ? $tblGradeTypeTest->getCode() : 'SN')
                                            . '<br>'
                                            . ($tblGrade->getDisplayGrade() !== null
                                            && $tblGrade->getDisplayGrade() !== '' ? $tblGrade->getDisplayGrade() : '&nbsp;');
                                        $section
                                            ->addElementColumn((new Element())
                                                ->setContent($text)
                                                ->styleTextSize($GradeFontSize)
                                                ->styleBorderTop()
                                                ->styleBorderRight()
                                                ->styleHeight('36.6px')
                                                ->styleBorderLeft($count++ < 1 ? '1px' : '0px')
                                                ->styleBackgroundColor(!$tblGradeTypeTest ? 'lightgrey' : '')
                                                ->styleTextBold(!$tblGradeTypeTest || $tblGradeTypeTest->isHighlighted() ? 'bold' : 'normal')
                                                , $widthGradeString);
                                    }
                                }
                            }

                            // leer auffüllen
                            if (count($periodArray[$tblPeriod->getId()]) < $totalGradeCountPeriod) {
                                for ($i = 0; $i < ($totalGradeCountPeriod - count($periodArray[$tblPeriod->getId()])); $i++) {
                                    $section
                                        ->addElementColumn((new Element())
                                            ->setContent(
                                                '&nbsp;<br>&nbsp;<br>&nbsp;'
                                            )
                                            ->styleTextSize('10px')
                                            ->styleBorderTop()
                                            ->styleBorderRight()
                                            ->styleBorderLeft($count == 0 ? '1px' : '0px')
                                            , $widthGradeString);
                                    $count++;
                                }
                            }

                            if (isset($periodArray[$tblPeriod->getId()]['Average'])) {
                                $section
                                    ->addElementColumn((new Element())
                                        ->setContent(
                                            '&#216;<br>' . $periodArray[$tblPeriod->getId()]['Average'] . '<br> &nbsp;'
                                        )
                                        ->styleTextSize('10px')
                                        ->styleBorderTop()
                                        ->styleBorderRight()
                                        ->styleTextBold()
                                        ->styleBackgroundColor('lightgrey')
                                        , $widthGradeString);
                            }
                        } else {
                            for ($i = 0; $i < $maxGradesPerPeriodCount[$tblPeriod->getId()]; $i++) {
                                $section
                                    ->addElementColumn((new Element())
                                        ->setContent(
                                            '&nbsp;<br>&nbsp;<br>&nbsp;'
                                        )
                                        ->styleTextSize('10px')
                                        ->styleBorderTop()
                                        ->styleBorderRight()
                                        ->styleBorderLeft($i < 1 ? '1px' : '0px')
                                        , $widthGradeString);
                            }
                            $count++;
                        }
                    }

                    if (isset($periodArray['Total'])) {
                        $section
                            ->addElementColumn((new Element())
                                ->setContent(
                                    '&nbsp;<br>' . $periodArray['Total']['Average'] . '<br> &nbsp;'
                                )
                                ->styleTextSize('10px')
                                ->styleBorderTop()
                                ->styleBorderRight()
                                ->styleTextBold()
                                ->styleBackgroundColor('lightgrey')
                                , $widthSubjectString);
                    }
                } else {
                    for ($i = 0; $i < $totalGradeCount; $i++) {
                        $section
                            ->addElementColumn((new Element())
                                ->setContent(
                                   '&nbsp;<br>&nbsp;<br>&nbsp;'
                                )
                                ->styleTextSize('10px')
                                ->styleBorderTop()
                                ->styleBorderRight()
                                ->styleBorderLeft($i < 1 ? '1px' : '0px')
                                , $widthGradeString);
                    }
                }
                $slice
                    ->addSection($section);
            }

            return $slice
                    ->styleBorderBottom();
        }

        return new Slice();
    }

    /**
     * @param TblPerson   $tblPerson
     * @param TblDivision $tblDivision
     *
     * @return Page
     */
    public function buildPage(TblPerson $tblPerson, TblDivision $tblDivision)
    {
        return (new Page())
            ->addSlice($this->getPageHeaderSlice($tblPerson, $tblDivision))
            ->addSlice($this->getGradebookOverviewSlice($tblPerson, $tblDivision));
    }
}