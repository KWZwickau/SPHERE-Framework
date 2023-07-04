<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

class BGymAbgSekII extends BGymDiplomaStyle
{
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
}