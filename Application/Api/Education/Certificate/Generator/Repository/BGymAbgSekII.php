<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveStudent;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

class BGymAbgSekII extends BGymStyle
{
    private array $advancedCourses = array();
    private array $basicCourses = array();
    private array $tblSubjectList = array();

    private ?TblLeaveStudent $tblLeaveStudent = null;

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null): array
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        if ($tblPerson) {
            list($this->advancedCourses, $this->basicCourses) = DivisionCourse::useService()->getCoursesForStudent($tblPerson);

            if (($tblStudentEducation = $this->getTblStudentEducation())
                && ($tblYear = $tblStudentEducation->getServiceTblYear())
            ) {
                $this->tblLeaveStudent = Prepare::useService()->getLeaveStudentBy($tblPerson, $tblYear);
            }
        }

        $pageList[] =  (new Page())
            ->addSlice($this->getHeaderBGym('Abgangszeugnis'))
            ->addSlice($this->getStudentLeaveDiploma($personId))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('hat vom 
                        {% if( Content.P' . $personId . '.Input.DateFrom is not empty) %}
                            {{ Content.P' . $personId . '.Input.DateFrom }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                        bis
                        {% if( Content.P' . $personId . '.Input.DateTo is not empty) %}
                            {{ Content.P' . $personId . '.Input.DateTo }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                        das')
                    ->styleAlignCenter()
                    ->styleMarginTop('10px')
                )
            )
            ->addSlice($this->getSubjectAreaDiploma($personId))
            ->addSlice((new Slice())
                ->addElement($this->getElementDiploma('besucht und folgende Leistungen erreicht:')
                    ->styleMarginTop('10px')
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Leistungen in den Jahrgangsstufen 12 und 13' . $this->setSup('1)'))
                    ->styleAlignCenter()
                    ->styleMarginTop('20px')
                )
            )
            ->addSlice($this->getGradeHeader())
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Pflichtbereich')
                    ->styleAlignCenter()
                    ->styleMarginTop('5px')
                )
            )
            ->addSlice($this->getWorkField('Sprachlich-literarisch-künstlerisches Aufgabenfeld', '230px'))
            ->addSlice($this->getWorkField('Gesellschaftswissenschaftliches Aufgabenfeld', '125px'))
            ->addSlice($this->getWorkField('Mathematisch-naturwissenschaftlich-technisches Aufgabenfeld', '125px', 1, 3));

        $pageList[] = (new Page())
            ->addSlice((new Slice())
                ->styleMarginTop('30px')
                ->addElement((new Element())
                    ->setContent('
                        Abgangszeugnis für 
                        {{ Content.P' . $personId . '.Person.Data.Name.Salutation }} {{ Content.P' . $personId . '.Person.Data.Name.First }} 
                        {{ Content.P' . $personId . '.Person.Data.Name.Last }}, geboren am
                        {% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthday is not empty) %}
                            {{ Content.P'.$personId.'.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                        &ndash; 2. Seite
                    ')
                    ->styleAlignCenter()
                    ->styleTextUnderline()
                    ->styleTextSize('11px')
                )
            )
            ->addSlice($this->getGradeHeader())
            ->addSlice($this->getWorkField('Mathematisch-naturwissenschaftlich-technisches Aufgabenfeld (Fortsetzung)', '125px', 4))
            ->addSlice($this->getWorkField('', '90px'))
            ->addSlice($this->getChosenSubjects())
            // todo Thema BELL
            // todo abgeschlossen Fächer der Klasse 11
            ->addSlice($this->getRemarkBGym($personId, false, '80px', '10px'))
            ->addSlice($this->getSignPartBGym($personId, true, '25px', false))
            ->addSlice($this->getFootNotesSekII()->styleMarginTop('15px'));

        return $pageList;
    }

    private function getGradeHeader(): Slice
    {
        $marginTop = '10px';
        $paddingBottom = '4px';

        return (new Slice())
            ->styleMarginTop('10px')
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Fach')
                    ->styleAlignCenter()
                    , '40%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Punktzahlen in einfacher Wertung')
                    ->styleAlignCenter()
                    , '40%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Note' . $this->setSup('2)'))
                    ->styleAlignCenter()
                    , '20%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderBottom()
                    ->styleMarginTop($marginTop)
                    ->stylePaddingBottom($paddingBottom)
                    , '40%'
                )
                ->addElementColumn((new Element())
                    ->setContent('12/I')
                    ->styleAlignCenter()
                    ->styleBorderBottom()
                    ->styleMarginTop($marginTop)
                    ->stylePaddingBottom($paddingBottom)
                    , '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('12/II')
                    ->styleAlignCenter()
                    ->styleBorderBottom()
                    ->styleMarginTop($marginTop)
                    ->stylePaddingBottom($paddingBottom)
                    , '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('13/I')
                    ->styleAlignCenter()
                    ->styleBorderBottom()
                    ->styleMarginTop($marginTop)
                    ->stylePaddingBottom($paddingBottom)
                    , '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('13/II')
                    ->styleAlignCenter()
                    ->styleBorderBottom()
                    ->styleMarginTop($marginTop)
                    ->stylePaddingBottom($paddingBottom)
                    , '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderBottom()
                    ->styleMarginTop($marginTop)
                    ->stylePaddingBottom($paddingBottom)
                    , '20%'
                )
            );
    }

    /**
     * @param string $workField
     * @param string $height
     * @param int $rangeFrom
     * @param int $rangeTo
     *
     * @return Slice
     */
    private function getWorkField(string $workField, string $height, int $rangeFrom = 0, int $rangeTo = 0): Slice
    {
        $slice = (new Slice())
            ->addElement((new Element())
                ->setContent($workField ?: '&nbsp;')
                ->styleAlignCenter()
                ->styleMarginTop('8px')
            );

        $count = 0;
        if (($tblSubjectList = $this->getSubjectListByWorkField(str_replace(' (Fortsetzung)', '', $workField)))) {
            foreach ($tblSubjectList as $tblSubject) {
                if (isset($this->advancedCourses[$tblSubject->getId()])
                    || isset($this->basicCourses[$tblSubject->getId()])
                ) {
                    $count++;
                    if ($rangeFrom && $count < $rangeFrom) {
                        continue;
                    }
                    if ($rangeTo && $count > $rangeTo) {
                        continue;
                    }

                    $slice->addSection($this->getSubjectGradeLine($tblSubject, isset($this->advancedCourses[$tblSubject->getId()])));
                    $this->tblSubjectList[$tblSubject->getId()] = $tblSubject;
                }
            }
        }

        return $slice->styleHeight($height);
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
            ->addSection(isset($chosenSubjectList[0])
                ? $this->getSubjectGradeLine($chosenSubjectList[0], false)
                : (new Section)->addElementColumn((new Element())->setContent('&nbsp;'))
            )
            ->addSection(isset($chosenSubjectList[1])
                ? $this->getSubjectGradeLine($chosenSubjectList[1], false)
                : (new Section)->addElementColumn((new Element())->setContent('&nbsp;'))
            );
    }

    private function getSubjectGradeLine(TblSubject $tblSubject, bool $isAdvancedCourse): Section
    {
        $widthSubject = 39;
        $widthSpace = 1;
        $widthGrade = 20;
        $widthPoints = (100 - $widthSubject - $widthGrade - 5 * $widthSpace) / 4;

        $grades = array(
            '12-1' => '&ndash;',
            '12-2' => '&ndash;',
            '13-1' => '&ndash;',
            '13-2' => '&ndash;',
        );

        $averageList = array();

        if ($this->tblLeaveStudent) {
            for ($level = 12; $level < 14; $level++) {
                for ($term = 1; $term < 3; $term++) {
                    $midTerm = $level . '-' . $term;
                    if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier($midTerm))
                        && ($tblLeaveAdditionalGrade = Prepare::useService()->getLeaveAdditionalGradeBy(
                            $this->tblLeaveStudent,
                            $tblSubject,
                            $tblPrepareAdditionalGradeType
                        ))
                    ) {
                        if ($tblLeaveAdditionalGrade->getGrade() !== null && $tblLeaveAdditionalGrade->getGrade() !== '') {
                            $averageList[] = $tblLeaveAdditionalGrade->getGrade();
                            $value = str_pad($tblLeaveAdditionalGrade->getGrade(), 2, 0, STR_PAD_LEFT);
                            $grades[$midTerm] = $value;
                        }
                    }
                }
            }
        }

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent($tblSubject->getName() . ($isAdvancedCourse ? ' (LF)' : ''))
                ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
                ->styleBorderBottom(self::BORDER_SIZE, self::BORDER_COLOR)
                ->stylePaddingBottom('5px')
                , $widthSubject . '%'
            )
            ->addElementColumn((new Element())->setContent('&nbsp;'), $widthSpace . '%')
            ->addElementColumn($this->getElementPoints($grades['12-1']), $widthPoints . '%')
            ->addElementColumn((new Element())->setContent('&nbsp;'), $widthSpace . '%')
            ->addElementColumn($this->getElementPoints($grades['12-2']), $widthPoints . '%')
            ->addElementColumn((new Element())->setContent('&nbsp;'), $widthSpace . '%')
            ->addElementColumn($this->getElementPoints($grades['13-1']), $widthPoints . '%')
            ->addElementColumn((new Element())->setContent('&nbsp;'), $widthSpace . '%')
            ->addElementColumn($this->getElementPoints($grades['13-2']), $widthPoints . '%')
            ->addElementColumn((new Element())->setContent('&nbsp;'), $widthSpace . '%')
            ->addElementColumn($this->getElementPoints($this->getAverageText($averageList)), $widthGrade . '%')
            ;
    }

    private function getElementPoints(string $contentGrade): Element
    {
        return (new Element())
            ->setContent($contentGrade)
            ->styleAlignCenter()
            ->styleBackgroundColor(self::BACKGROUND)
            ->stylePaddingTop(self::PADDING_TOP_GRADE)
            ->stylePaddingBottom(self::PADDING_BOTTOM_GRADE)
            ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE);
    }
}