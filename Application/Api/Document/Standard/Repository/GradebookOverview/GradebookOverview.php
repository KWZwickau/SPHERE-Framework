<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\GradebookOverview;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Extension\Repository\Sorter;
use SPHERE\System\Extension\Repository\Sorter\DateTimeSorter;

/**
 * Class GradebookOverview
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\GradebookOverview
 */
class GradebookOverview extends AbstractDocument
{

    /**
     * GradebookOverview constructor.
     *
     * @param TblPerson   $tblPerson
     * @param TblDivision $tblDivision
     */
    function __construct(TblPerson $tblPerson, TblDivision $tblDivision)
    {
        $this->setTblPerson($tblPerson);
        $this->setTblDivision($tblDivision);
    }

    /**
     * @var TblDivision|null
     */
    private $tblDivision = null;

    /**
     * @return false|TblDivision
     */
    public function getTblDivision()
    {
        if (null === $this->tblDivision) {
            return false;
        } else {
            return $this->tblDivision;
        }
    }

    /**
     * @param false|TblDivision $tblDivision
     */
    public function setTblDivision(TblDivision $tblDivision = null)
    {

        $this->tblDivision = $tblDivision;
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
     * @param array $pageList
     *
     * @return Frame
     */
    public function buildDocument($pageList = array())
    {
        return (new Frame())->addDocument((new Document())
            ->addPage($this->buildPage())
        );
    }

    /**
     * @return Slice $PageHeader
     */
    public function getPageHeaderSlice() {
        return (new Slice())
            ->addSection((new Section())
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Schüler: ' . ($this->getTblPerson() ? $this->getTblPerson()->getLastFirstName() : ''))
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klasse: ' . ($this->getTblDivision() ? $this->getTblDivision()->getDisplayName() : ''))
                        )
                    ), '33%'
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
     * @return Slice
     */
    public function getGradebookOverviewSlice() {
        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST');
        // GradeCounterMax for standard empty field
        $GradeCounterMax = 8;
        $tblDivision = $this->getTblDivision();
        $tblPerson = $this->getTblPerson();

        $ColumnWidth = array();
        $ColumnWidth['FirstAndLast'] = 6;
        $ColumnWidth['Average'] = 4;

        $TextSize = '14px';
        $SingleRowPaddingTop = '8.5px';
        $SingleRowPaddingBottom = '8.5px';

        $tblSubjectList = array();
        $SubjectSectionList = array();

        $GradebookOverviewSlice = (new Slice());
        $HeaderSection = (new Section());

        if (($tblYear = $tblDivision->getServiceTblYear())
            && ($tblPeriodList = $tblYear->getTblPeriodAll())) {

            $ColumnWidth['Period'] = (100 - ($ColumnWidth['FirstAndLast'] * 2 + $ColumnWidth['Average'] * 2)) / count($tblPeriodList);

            $tblGradeListSort = array();
            if (($tblGradeList = Gradebook::useService()->getGradeAllBy($tblPerson, $tblDivision, null,
                $tblTestType))) {
                $tblTestList = array();
                foreach ($tblGradeList as $tblGrade) {
                    if (($tblTest = $tblGrade->getServiceTblTest())) {
                        $tblTestList[] = $tblTest;
                    }
                }
                if (!empty($tblTestList)) {
                    $tblTestList = $this->getSorter($tblTestList)->sortObjectBy('Date', new DateTimeSorter());

                    foreach ($tblTestList as $tblTest) {
                        $tblGradeListSort[] = Gradebook::useService()->getGradeByTestAndStudent($tblTest, $tblPerson);
                    }
                }
            }

            // Alle Fächer des Schülers
            $tblDivisionSubjectList = Division::useService()->getDivisionSubjectAllByPersonAndYear(
                $tblPerson, $tblYear
            );

//            // Alle Fächer der Klasse (Auskommentiert Entscheidung folgt noch)
//            $tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision, false);

            // Finden aller Fächer aus der DivisionList
            if ($tblDivisionSubjectList) {
                foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                    if ($tblDivisionSubject->getServiceTblSubject()) {
                        $tblSubjectList[$tblDivisionSubject->getServiceTblSubject()->getId()] =
                            $tblDivisionSubject->getServiceTblSubject()->getAcronym();
                    }
                }
            }

            if (!empty($tblSubjectList)) {

                // Sortieren der Fächer nach Alphabet
                asort($tblSubjectList);

                foreach ($tblPeriodList as $tblPeriod) {
                    foreach ($tblSubjectList as $SubjectId => $SubjectAcronym) {
                        $GradeCounter = 0;
                        if (!empty($tblGradeList)) {
                            // Anzahl Noten Pro Fach zählen
                            foreach ($tblGradeList as $tblGrade) {
                                if (($tblGrade->getServiceTblPeriod()->getId() == $tblPeriod->getId())
                                    && ($tblGrade->getServiceTblSubject()->getId() == $SubjectId)
                                    && (!empty($tblGrade->getGrade()))) {
                                    $GradeCounter++;
                                }
                            }
                        }
                        // größte Anzahl setzen
                        if ($GradeCounterMax < $GradeCounter) {
                            $GradeCounterMax = $GradeCounter;
                        }
                    }
                }

                if ($GradeCounterMax > 20) {
                    $TextSize = '8px';
                    $SingleRowPaddingTop = '4.9px';
                    $SingleRowPaddingBottom = '4.9px';
                } elseif ($GradeCounterMax > 15) {
                    $TextSize = '10px';
                    $SingleRowPaddingTop = '6.1px';
                    $SingleRowPaddingBottom = '6.1px';
                } elseif ($GradeCounterMax > 10) {
                    $TextSize = '12px';
                    $SingleRowPaddingTop = '7.3px';
                    $SingleRowPaddingBottom = '7.3px';
                }

                $ColumnWidth['Grade'] = (100 - ($ColumnWidth['FirstAndLast'] * 2 + $ColumnWidth['Average'] * 2)) / ($GradeCounterMax * 2);

                $HeaderSection
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleBorderTop()
                        ->styleBorderBottom()
                        ->styleBorderLeft()
                        ->styleBorderRight()
                        ->styleTextSize($TextSize), $ColumnWidth['FirstAndLast'] . '%'
                    );

                foreach ($tblPeriodList as $tblPeriod) {
                    $HeaderSection
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;' . $tblPeriod->getDisplayName())
                            ->styleBorderTop()
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->styleTextBold()
                            ->styleTextSize($TextSize), $ColumnWidth['Period'] . '%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&#216;')
                            ->styleBorderTop()
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->styleAlignCenter()
                            ->styleTextBold()
                            ->styleTextSize($TextSize), $ColumnWidth['Average'] . '%'
                        );
                }
                $HeaderSection
                    ->addElementColumn((new Element())
                        ->setContent('Gesamt')
                        ->styleBorderTop()
                        ->styleBorderBottom()
                        ->styleBorderRight()
                        ->styleAlignCenter()
                        ->styleTextBold()
                        ->styleTextSize($TextSize), $ColumnWidth['FirstAndLast'] . '%'
                    );

                foreach ($tblSubjectList as $SubjectId => $SubjectAcronym) {
                    $SubjectSectionList[$SubjectId] = (new Section())
                        ->addElementColumn((new Element())
                            ->setContent($SubjectAcronym)
                            ->styleBorderBottom()
                            ->styleBorderLeft()
                            ->styleBorderRight()
                            ->stylePaddingTop($SingleRowPaddingTop)
                            ->stylePaddingBottom($SingleRowPaddingBottom)
                            ->styleTextBold()
                            ->styleAlignCenter()
                            ->styleTextSize($TextSize), $ColumnWidth['FirstAndLast'] . '%'
                        );

                    $tblScoreType = Gradebook::useService()->getScoreRuleByDivisionAndSubjectAndGroup(
                        $tblDivision, Subject::useService()->getSubjectById($SubjectId));

                    foreach ($tblPeriodList as $tblPeriod) {
                        $CounterCurrentGrades = 0;
                        if (!empty($tblGradeList)) {
                            foreach ($tblGradeList as $tblGrade) {
                                if ($tblGrade->getServiceTblSubject()->getId() == $SubjectId
                                    && $tblGrade->getServiceTblPeriod()->getId() == $tblPeriod->getId()
                                    && (!empty($tblGrade->getGrade()))) {
                                    if ($tblGrade->getTblGradeType()->isHighlighted()) {
                                        /** @var Section[] $SubjectSectionList */
                                        $SubjectSectionList[$SubjectId]
                                            ->addElementColumn((new Element())
                                                ->setContent($tblGrade->getDisplayGrade().'<br/>('.$tblGrade->getTblGradeType()->getCode().')')
                                                ->styleBorderBottom()
                                                ->styleBorderRight()
                                                ->styleTextBold()
                                                ->styleAlignCenter()
                                                ->styleTextSize($TextSize), $ColumnWidth['Grade'].'%'
                                            );
                                    } else {
                                        /** @var Section[] $SubjectSectionList */
                                        $SubjectSectionList[$SubjectId]
                                            ->addElementColumn((new Element())
                                                ->setContent($tblGrade->getDisplayGrade().'<br/>('.$tblGrade->getTblGradeType()->getCode().')')
                                                ->styleBorderBottom()
                                                ->styleBorderRight()
                                                ->styleAlignCenter()
                                                ->styleTextSize($TextSize), $ColumnWidth['Grade'].'%'
                                            );
                                    }
                                    unset($tblGrade);
                                    $CounterCurrentGrades++;
                                }
                            }
                        }
                        if (($CounterDifference = $GradeCounterMax - $CounterCurrentGrades) > 0) {
                            for ($i = 0; $i < $CounterDifference; $i++) {
                                $SubjectSectionList[$SubjectId]
                                    ->addElementColumn((new Element())
                                        ->setContent('&nbsp;')
                                        ->styleBorderBottom()
                                        ->styleBorderRight()
                                        ->stylePaddingTop($SingleRowPaddingTop)
                                        ->stylePaddingBottom($SingleRowPaddingBottom)
                                        ->styleTextSize($TextSize), $ColumnWidth['Grade'] . '%'
                                    );
                            }
                        }

                        $average = Gradebook::useService()->calcStudentGrade(
                            $tblPerson,
                            $tblDivision,
                            Subject::useService()->getSubjectById($SubjectId),
                            $tblTestType,
                            $tblScoreType ? $tblScoreType : null,
                            $tblPeriod
                        );

                        if (is_array($average)) {
                            $average = '';
                        } else {
                            $posStart = strpos($average, '(');
                            if ($posStart !== false) {
                                $average = substr($average, 0, $posStart);
                            }
                        }

                        $SubjectSectionList[$SubjectId]
                            ->addElementColumn((new Element())
                                ->setContent($average ? $average : '&nbsp;')
                                ->styleBorderBottom()
                                ->styleBorderRight()
                                ->stylePaddingTop($SingleRowPaddingTop)
                                ->stylePaddingBottom($SingleRowPaddingBottom)
                                ->styleAlignCenter()
                                ->styleTextSize($TextSize), $ColumnWidth['Average'] . '%'
                            );
                    }

                    $average = Gradebook::useService()->calcStudentGrade(
                        $tblPerson,
                        $tblDivision,
                        Subject::useService()->getSubjectById($SubjectId),
                        $tblTestType,
                        $tblScoreType ? $tblScoreType : null
                    );

                    if (is_array($average)) {
                        $average = '';
                    } else {
                        $posStart = strpos($average, '(');
                        if ($posStart !== false) {
                            $average = substr($average, 0, $posStart);
                        }
                    }

                    $SubjectSectionList[$SubjectId]
                        ->addElementColumn((new Element())
                            ->setContent($average ? $average : '&nbsp;')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop($SingleRowPaddingTop)
                            ->stylePaddingBottom($SingleRowPaddingBottom)
                            ->styleAlignCenter()
                            ->styleTextBold()
                            ->styleTextSize($TextSize), $ColumnWidth['FirstAndLast'] . '%'
                        );
                }
            }
        } else {

            $HeaderSection = $HeaderSection->addElementColumn((new Element())
                ->setContent('Eine Darstellung der Noten ist nicht möglich, wenn das Jahr keine Zeiträume besitzt.')
                ->styleTextSize('20px')
                ->styleAlignCenter()
                ->stylePaddingTop('20px')
            );
        }

        $GradebookOverviewSlice
            ->addSection($HeaderSection)
            ->addSectionList($SubjectSectionList);

        return $GradebookOverviewSlice;
    }

    /**
     * @return Page
     */
    public function buildPage()
    {
        return (new Page())
            ->addSlice($this->getPageHeaderSlice())
            ->addSlice($this->getGradebookOverviewSlice());
    }
}