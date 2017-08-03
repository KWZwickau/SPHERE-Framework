<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 27.07.2017
 * Time: 14:25
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\GradebookOverview;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Extension\Extension;
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
     * @return Page
     */
    public function buildPage()
    {
        $HeaderSlice = (new Slice())
            ->addSection((new Section())
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Schüler: ' . ($this->getTblPerson() ? $this->getTblPerson()->getFullName() : ''))
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

        $Data = $this->getGradebookOverviewContent();

        $GradeCounterMax = 0;
        $PeriodList = array();
        foreach ($Data as $Subject => $Period) {
            foreach ($Data[$Subject] as $Period => $Grade) {
                if (empty($GradeCounterMax)) {
                    $GradeCounterMax = count($Grade);
                } elseif ($GradeCounterMax < count($Grade)) {
                    $GradeCounterMax = count($Grade);
                }
                $PeriodList[$Period] = true;
            }
        }

        $ColumnWidth = array();
        //change the width for subject (first column) and average (last column), the rest will change automatically
        $ColumnWidth['Subject'] = 10;
        $ColumnWidth['Average'] = 10;
        $ColumnWidth['Period'] = (100 - $ColumnWidth['Subject'] - $ColumnWidth['Average']) / count($PeriodList);

        if (!empty($GradeCounterMax)) {
            $ColumnWidth['Grade'] = (100 - $ColumnWidth['Subject'] - $ColumnWidth['Average']) / (($GradeCounterMax + 1) * count($PeriodList));
        }

        $tblHeaderSection = (new Section())
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleBorderAll(), $ColumnWidth['Subject'] . '%'
            );

        foreach ($PeriodList as $Period => $Bool) {
            $tblHeaderSection
                ->addElementColumn((new Element())
                    ->setContent($Period)
                    ->styleBorderTop()
                    ->styleBorderBottom()
                    ->styleBorderRight()
                    ->styleTextBold(), $ColumnWidth['Period'] . '%'
                );
        }

        $tblHeaderSection
            ->addElementColumn((new Element())
                ->setContent('Gesamt')
                ->styleBorderTop()
                ->styleBorderBottom()
                ->styleBorderRight()
                ->styleTextBold()
                ->stylePaddingLeft('5px'), $ColumnWidth['Average'] . '%'
            );

        /** @var Section[] $tblSubjectSectionList */
        $tblSubjectSectionList = array();
        foreach ($Data as $Subject => $Period) {
            $tblSubjectSectionList[$Subject] = (new Section())
                ->addElementColumn((new Element())
                    ->setContent($Subject)
                    ->styleBorderBottom()
                    ->styleBorderLeft()
                    ->styleBorderRight()
                    ->styleTextBold(), $ColumnWidth['Subject'] . '%'
                );
            foreach ($Data[$Subject] as $Period => $Grade) {
                foreach ($Data[$Subject][$Period] as $Grade => $GradeType) {
                    if (($Mark = Gradebook::useService()->getGradeById($Grade))) {
                        if ($GradeType) {
                            $tblSubjectSectionList[$Subject]
                                ->addElementColumn((new Element())
                                    ->setContent($Mark->getDisplayGrade())
                                    ->styleBorderBottom()
                                    ->styleBorderRight()
                                    ->styleTextBold(), $ColumnWidth['Grade'] . '%'
                                );
                        } else {
                            $tblSubjectSectionList[$Subject]
                                ->addElementColumn((new Element())
                                    ->setContent($Mark->getDisplayGrade())
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), $ColumnWidth['Grade'] . '%'
                                );
                        }
                    } else {
                        $tblSubjectSectionList[$Subject]
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                ->styleBorderBottom()
                                ->styleBorderRight(), $ColumnWidth['Grade'] . '%'
                            );
                    }
                }
                $tblSubjectSectionList[$Subject]
                    ->addElementColumn((new Element())
                        ->setContent('Avg')
                        ->styleBorderBottom()
                        ->styleBorderRight(), $ColumnWidth['Grade'] . '%'
                    );
            }
            $tblSubjectSectionList[$Subject]
                ->addElementColumn((new Element())
                    ->setContent('AAll')
                    ->styleBorderBottom()
                    ->styleBorderRight(), $ColumnWidth['Average'] . '%'
                );
        }

        $tblContentSlice = (new Slice())
            ->addSection($tblHeaderSection)
            ->addSectionList($tblSubjectSectionList);

        return (new Page())
            ->addSlice($HeaderSlice)
            ->addSlice($tblContentSlice);
    }

    /**
     * @return array $Data
     */
    public
    function getGradebookOverviewContent()
    {
        $Data = array();
        $tblDivision = $this->getTblDivision();
        $tblPerson = $this->getTblPerson();
        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST');


        if (($tblYear = $tblDivision->getServiceTblYear())
            && ($tblDivisionSubjectList = Division::useService()->getDivisionSubjectAllByPersonAndYear($tblPerson, $tblYear))
            && ($tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear))) {

            //first loop to get a counter for the number of grades
            $GradeCounterMax = null;
            foreach ($tblDivisionSubjectList as &$tblDivisionSubject) {
//                if (($tblDivisionSubjectTemp = Division::useService()->getDivisionSubjectBySubjectAndDivisionWithoutGroup($tblDivisionSubject->getServiceTblSubject(),$tblDivision))) {
//                    if ($tblDivisionSubjectTemp->getServiceTblSubject()->getId() !== $tblDivisionSubject->getServiceTblSubject()->getId()) {
//                        $tblDivisionSubject = false;
//                        continue;
//                    }
//                }
                foreach ($tblPeriodList as $tblPeriod) {
                    if (($tblSubject = $tblDivisionSubject->getServiceTblSubject())) {
                        $tblTestList = Evaluation::useService()->getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
                            $tblDivision,
                            $tblSubject,
                            $tblTestType,
                            $tblPeriod,
                            $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
                        );
                        if ($tblTestList) {
                            $GradeCounterSubject = null;
                            /** @var TblTest $tblTest */
                            foreach ($tblTestList as $tblTest) {
                                if ($tblTest->getServiceTblGradeType()
                                    && ($tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest, $tblPerson))) {
                                    if ($tblGrade->getGrade() !== null && $tblGrade->getGrade() !== '') {
                                        $GradeCounterSubject++;
                                    }
                                }
                            }
                            if (empty($GradeCounterMax)) {
                                $GradeCounterMax = $GradeCounterSubject;
                            } elseif ($GradeCounterMax < $GradeCounterSubject) {
                                $GradeCounterMax = $GradeCounterSubject;
                            }
                        }
                    }
                }
            }

            //second loop to fill the data
            foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                if (Division::useService()->getSubjectStudentByDivisionSubjectAndPerson($tblDivisionSubject,$tblPerson)) {
                    continue;
                }
                foreach ($tblPeriodList as $tblPeriod) {
                    if (($tblSubject = $tblDivisionSubject->getServiceTblSubject())) {
                        $tblTestList = Evaluation::useService()->getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
                            $tblDivision,
                            $tblSubject,
                            $tblTestType,
                            $tblPeriod,
                            $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
                        );
                        if ($tblTestList) {
                            $tblTestList = (new Extension())->getSorter($tblTestList)->sortObjectBy(
                                'Date', new DateTimeSorter());

                            $GradeCounterNow = 0;
                            /** @var TblTest $tblTest */
                            foreach ($tblTestList as $tblTest) {
                                if ($tblTest->getServiceTblGradeType()
                                    && ($tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest, $tblPerson))) {
                                    if ($tblGrade->getGrade() !== null && $tblGrade->getGrade() !== '') {
                                        $GradeCounterNow++;
                                        $Data[$tblSubject->getAcronym()][$tblPeriod->getDisplayName()][$tblGrade->getId()] = $tblTest->getServiceTblGradeType()->isHighlighted();
                                    }
                                }
                            }
                            if (($GradeCounterMax - $GradeCounterNow) > 0) {
                                for ($i = 0; $i < $GradeCounterMax - $GradeCounterNow; $i++) {
                                    $Data[$tblSubject->getAcronym()][$tblPeriod->getDisplayName()]['filler'.$i] = true;
                                }
                            }
                        } else {
                            for ($i = 0; $i < $GradeCounterMax; $i++) {
                                $Data[$tblSubject->getAcronym()][$tblPeriod->getDisplayName()]['filler'.$i] = true;
                            }
                        }
                    }
                }
            }
        }

        return $Data;
    }
}