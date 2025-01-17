<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTaskGrade;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Layout\Repository\Container;

class BGymKurshalbjahreszeugnis extends BGymStyle
{
    private array $advancedCourses = array();
    private array $basicCourses = array();
    private array $tblSubjectList = array();
    private array $tblTaskGradeList = array();

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page
     */
    public function buildPages(TblPerson $tblPerson = null): Page
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        if ($tblPerson) {
            list($this->advancedCourses, $this->basicCourses) = DivisionCourse::useService()->getCoursesForStudent($tblPerson);
            if (($tblPrepare = $this->getTblPrepareCertificate())
                && ($tblTask = $tblPrepare->getServiceTblAppointedDateTask())
                && ($tblTaskGradeListTemp = Grade::useService()->getTaskGradeListByTaskAndPerson($tblTask, $tblPerson))
            ) {
                foreach ($tblTaskGradeListTemp as $tblTaskGradeTemp) {
                    if (($tblSubject = $tblTaskGradeTemp->getServiceTblSubject())) {
                        $this->tblTaskGradeList[$tblSubject->getId()] = $tblTaskGradeTemp;
                    }
                }
            }
        }

        return (new Page())
            ->addSlice($this->getHeaderBGym('Halbjahreszeugnis', '14px', '20px'))
            ->addSlice($this->getSubjectArea($personId, '5px'))
            ->addSlice($this->getLevelYearStudent($personId, 'Schuljahr', 'Jahrgangsstufe', '8px'))
            ->addSlice($this->getLevelMidTerm('10px'))
            ->addSlice($this->getSubjectPointsLine())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addSliceColumn($this->getWorkField('Sprachlich-literarisch-künstlerisches Aufgabenfeld', '230px')
                        , '49%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '2%'
                    )
                    ->addSliceColumn($this->getWorkField('Mathematisch-naturwissenschaftlich-technisches Aufgabenfeld', '230px')
                        , '49%'
                    )
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addSliceColumn($this->getWorkField('Gesellschaftswissenschaftliches Aufgabenfeld', '125px')
                        , '49%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '2%'
                    )
                    ->addSliceColumn($this->getWorkField('', '125px')
                        , '49%'
                    )
                )
            )
            ->addSlice($this->getChosenSubjects())
            ->addSlice($this->getRemarkBGym($personId, false, '45px', '10px'))
            ->addSlice($this->getSignPartBGym($personId, true, '10px'))
            ->addSlice($this->getFootNotesSekII(false));
    }

    private function getSubjectPointsLine(): Slice
    {
        return (new Slice)
            ->styleMarginTop('7px')
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Fach')
                    ->styleAlignCenter()
                    ->styleBorderBottom(self::BORDER_SIZE, self::BORDER_COLOR)
                    , '38%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Punkte' . $this->setSup('1)'))
                    ->styleAlignCenter()
                    ->styleBorderBottom(self::BORDER_SIZE, self::BORDER_COLOR)
                    , '12%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Fach')
                    ->styleAlignCenter()
                    ->styleBorderBottom(self::BORDER_SIZE, self::BORDER_COLOR)
                    , '38%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Punkte' . $this->setSup('1)'))
                    ->styleAlignCenter()
                    ->styleBorderBottom(self::BORDER_SIZE, self::BORDER_COLOR)
                    , '12%'
                )
            )
            ->addElement((new Element())
                ->setContent('Pflichtbereich')
                ->styleAlignCenter()
                ->styleMarginTop('7px')
            );
    }

    /**
     * @param string $workField
     * @param string $height
     *
     * @return Slice
     */
    private function getWorkField(string $workField, string $height): Slice
    {
        switch ($workField) {
            case 'Sprachlich-literarisch-künstlerisches Aufgabenfeld':
                $name = 'Sprachlich-literarisch-' . new Container('künstlerisches Aufgabenfeld'); break;
            case 'Mathematisch-naturwissenschaftlich-technisches Aufgabenfeld':
                $name = 'Mathematisch-naturwissenschaftlich-' . new Container('technisches Aufgabenfeld'); break;
            case 'Gesellschaftswissenschaftliches Aufgabenfeld':
                $name = 'Gesellschaftswissenschaftliches' . new Container('Aufgabenfeld'); break;
            case '':
                $name = '&nbsp;' . new Container('&nbsp;'); break;
            default: $name = $workField;
        }
        $slice = (new Slice())
            ->addElement((new Element())
                ->setContent($name)
                ->styleAlignCenter()
//                ->styleTextSize('13px')
                ->styleMarginTop('-2px')
                ->styleMarginBottom('-8px')
            );

        if (($tblSubjectList = $this->getSubjectListByWorkField($workField))) {
            foreach ($tblSubjectList as $tblSubject) {
                if (isset($this->advancedCourses[$tblSubject->getId()])
                    || isset($this->basicCourses[$tblSubject->getId()])
                ) {
                    $slice->addSection($this->getSubjectGradeLine($tblSubject, isset($this->advancedCourses[$tblSubject->getId()])));
                    $this->tblSubjectList[$tblSubject->getId()] = $tblSubject;
                }
            }
        }

        return $slice->styleHeight($height);
    }

    private function getSubjectGradeLine(TblSubject $tblSubject, bool $isAdvancedCourse): Section
    {
        $widthSubject = 75;
        $widthSpace = 1;
        $widthGrade = 100 - $widthSubject - $widthSpace;

        $contentGrade = '&ndash;';
        /** @var TblTaskGrade $tblTaskGrade */
        if (($tblTaskGrade = $this->tblTaskGradeList[$tblSubject->getId()] ?? null)
            && $tblTaskGrade->getGrade() !== null
        ) {
            $contentGrade = str_pad($tblTaskGrade->getGrade(), 2, '0', STR_PAD_LEFT);
        }

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent($tblSubject->getName() . ($isAdvancedCourse ? ' (LF)' : ''))
                ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
                ->styleBorderBottom(self::BORDER_SIZE, self::BORDER_COLOR)
                ->stylePaddingBottom('5px')
                , $widthSubject . '%'
            )
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                , $widthSpace . '%'
            )
            ->addElementColumn((new Element())
                ->setContent($contentGrade)
                ->styleAlignCenter()
                ->styleBackgroundColor(self::BACKGROUND)
                ->stylePaddingTop(self::PADDING_TOP_GRADE)
                ->stylePaddingBottom(self::PADDING_BOTTOM_GRADE)
                ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
                , $widthGrade . '%'
            );
    }

    private function getChosenSubjects(): Slice
    {
        $chosenSubjectList = array();
        foreach ($this->advancedCourses as $advancedCourse) {
            if (!isset($this->tblSubjectList[$advancedCourse->getId()])) {
                $chosenSubjectList[] = $advancedCourse;
            }
        }
        foreach ($this->basicCourses as $basicCourse) {
            if (!isset($this->tblSubjectList[$basicCourse->getId()])) {
                $chosenSubjectList[] = $basicCourse;
            }
        }

        return (new Slice())
            ->addElement((new Element())
                ->setContent('Wahlbereich')
                ->styleAlignCenter()
                ->styleMarginTop('7px')
            )
            ->addSection((new Section())
                ->addSliceColumn(
                    isset($chosenSubjectList[0])
                        ? (new Slice())->addSection($this->getSubjectGradeLine($chosenSubjectList[0], false))
                        : (new Slice())->addElement((new Element())->setContent('&nbsp;'))
                    , '49%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '2%'
                )
                ->addSliceColumn(
                    isset($chosenSubjectList[1])
                        ? (new Slice())->addSection($this->getSubjectGradeLine($chosenSubjectList[1], false))
                        : (new Slice())->addElement((new Element())->setContent('&nbsp;'))
                    , '49%'
                )
            );
    }
}