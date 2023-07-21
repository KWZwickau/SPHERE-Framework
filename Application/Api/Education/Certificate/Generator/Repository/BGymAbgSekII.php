<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Text\Repository\Underline;

class BGymAbgSekII extends BGymDiplomaStyle
{
    private array $gradeTextList = array(
        '1' => 'sehr gut',
        '2' => 'gut',
        '3' => 'befriedigend',
        '4' => 'ausreichend',
        '5' => 'mangelhaft',
        '6' => 'ungenügend',
    );

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
                    ->styleTextBold()
                    ->styleAlignCenter()
                    ->styleMarginTop('20px')
                )
            )
            ->addSlice($this->getGradeHeader())
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Pflichtbereich')
                    ->styleTextBold()
                    ->styleAlignCenter()
                    ->styleMarginTop('5px')
                )
            )
            ->addSlice($this->getWorkFieldDiploma('Sprachlich-literarisch-künstlerisches Aufgabenfeld', '230px'))
            ->addSlice($this->getWorkFieldDiploma('Gesellschaftswissenschaftliches Aufgabenfeld', '125px'))
            ->addSlice($this->getWorkFieldDiploma('Mathematisch-naturwissenschaftlich-technisches Aufgabenfeld', '125px', 1, 3));

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
            ->addSlice($this->getWorkFieldDiploma('Mathematisch-naturwissenschaftlich-technisches Aufgabenfeld (Fortsetzung)', '125px', 4))
            ->addSlice($this->getWorkFieldDiploma('', '90px'))
            ->addSlice($this->getChosenSubjectsDiploma())
            ->addSlice($this->getBell($personId))
            ->addSlice($this->getLevelElven())
            ->addSlice($this->getRemarkBGym($personId, false, '100px', '10px'))
            ->addSlice($this->getSignPartBGym($personId, true, '40px', false))
            ->addSlice($this->getFootNotesSekII()->styleMarginTop('25px'));

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
                    ->styleTextBold()
                    ->styleAlignCenter()
                    , '40%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Punktzahlen in einfacher Wertung')
                    ->styleTextBold()
                    ->styleAlignCenter()
                    , '40%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Note' . $this->setSup('2)'))
                    ->styleTextBold()
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
                    ->styleTextBold()
                    ->styleAlignCenter()
                    ->styleBorderBottom()
                    ->styleMarginTop($marginTop)
                    ->stylePaddingBottom($paddingBottom)
                    , '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('12/II')
                    ->styleTextBold()
                    ->styleAlignCenter()
                    ->styleBorderBottom()
                    ->styleMarginTop($marginTop)
                    ->stylePaddingBottom($paddingBottom)
                    , '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('13/I')
                    ->styleTextBold()
                    ->styleAlignCenter()
                    ->styleBorderBottom()
                    ->styleMarginTop($marginTop)
                    ->stylePaddingBottom($paddingBottom)
                    , '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('13/II')
                    ->styleTextBold()
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
     * @param $personId
     *
     * @return Slice
     */
    private function getBell($personId): Slice
    {
        $bellPoints = '&ndash;';
        if (($tblLeaveStudent = $this->tblLeaveStudent)
            && ($tblPrepareInformation = Prepare::useService()->getLeaveInformationBy($tblLeaveStudent, 'BellPoints'))
        ) {
            $value = $tblPrepareInformation->getValue();
            if ($value !== null && $value !== '') {
                $bellPoints = str_pad($value,2, 0, STR_PAD_LEFT);
            }
        }

        return (new Slice)
            ->styleBorderAll()
            ->styleMarginTop('15px')
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent(new Underline('Thema:')
                        . '{% if(Content.P' . $personId . '.Input.BellSubject is not empty) %}
                            {{ Content.P' . $personId . '.Input.BellSubject|nl2br }}
                        {% else %}
                            &ndash;
                        {% endif %}'
                    )
                    ->stylePaddingTop('10px')
                    ->stylePaddingBottom('11px')
                    ->stylePaddingLeft('4px')
                )
                ->addElementColumn((new Element())
                    ->setContent('Punktzahl in' . new Container('einfacher Wertung:'))
                    ->stylePaddingTop()
                    ->stylePaddingBottom()
                    ->stylePaddingLeft('4px')
                    ->styleBorderLeft()
                    , '25%'
                )
                ->addElementColumn($this->getElementPoints($bellPoints)
                    ->stylePaddingTop('3px')
                    ->stylePaddingBottom('4px')
                    , '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Punkte')
                    ->stylePaddingTop('10px')
                    ->stylePaddingBottom('11px')
                    ->stylePaddingLeft('4px')
                    , '10%'
                )
            );
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Slice
     */
    private function getLevelElven(): Slice
    {
        $slice = new Slice();
        $slice
            ->styleMarginTop('20px')
            ->addElement((new Element())
                ->setContent('Ergebnisse der Fächer, die in der Klassenstufe 11 abgeschlossen wurden')
                ->styleAlignCenter()
                ->styleTextBold()
                ->styleTextSize('15px')
            );

        $sectionList = array();
        $tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('LEVEL-11');
        if ($this->tblLeaveStudent
            && ($tblLeaveAdditionalGradeList = Prepare::useService()->getLeaveAdditionalGradeListBy(
                $this->tblLeaveStudent,
                $tblPrepareAdditionalGradeType
            ))
        ) {
            $count = 0;
            $section = new Section();
            foreach ($tblLeaveAdditionalGradeList as $tblLeaveAdditionalGrade) {
                $count++;

                $subject = '&ndash;';
                $grade = '&ndash;';

                if (($tblSubject = $tblLeaveAdditionalGrade->getServiceTblSubject())) {
                    $subject = $tblSubject->getName();
                    $grade = $tblLeaveAdditionalGrade->getGrade();
                    if ($grade === '') {
                        continue;
                    }
                    if (isset($this->gradeTextList[$tblLeaveAdditionalGrade->getGrade()])) {
                        $grade = $this->gradeTextList[$tblLeaveAdditionalGrade->getGrade()];
                    }
                }

                if ($count == 2) {
                    $section->addElementColumn((new Element())->setContent('&nbsp;'), '2%');
                }
                $this->setLevelElevenColumn($section, $subject, $grade);
                if ($count == 2) {
                    $count = 0;
                    $sectionList[] = $section;
                    $section = new Section();
                }
            }

            if ($count == 1) {
                $section->addElementColumn((new Element())->setContent('&nbsp;'), '51%');
                $sectionList[] = $section;
            }
        }

        if ($sectionList) {
            $slice
                ->addSectionList($sectionList);
        }

        return $slice
            ->styleHeight('130px');
    }

    /**
     * @param Section $section
     * @param $subject
     * @param $grade
     */
    private function setLevelElevenColumn(Section $section, $subject, $grade)
    {
        $section
            ->addElementColumn((new Element())
                ->setContent($subject)
                ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
                ->styleBorderBottom()
                ->stylePaddingTop('5px')
                ->stylePaddingBottom('4px')
                , '35%'
            )
            ->addElementColumn((new Element())->setContent('&nbsp;'), '1%')
            ->addElementColumn($this->getElementPoints($grade), '13%');
    }
}