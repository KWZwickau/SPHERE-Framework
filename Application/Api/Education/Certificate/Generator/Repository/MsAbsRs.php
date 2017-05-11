<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class MsAbsRs
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class MsAbsRs extends Certificate
{

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     *
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $Header = $this->getHead($this->isSample(), false, 'auto', '50px');

        $pageList[] = (new Page())
            ->addSlice(
                $Header
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('ABSCHLUSSZEUGNIS')
                    ->styleTextSize('27px')
                    ->styleAlignCenter()
                    ->styleMarginTop('32%')
                    ->styleTextBold()
                )

//                ->addSlice((new Slice())
//                    ->addElement((new Element())
//                        ->setContent('der Mittelschule')
//                        ->styleTextSize('22px')
//                        ->styleAlignCenter()
//                        ->styleMarginTop('15px')
//                    )
            );

        $pageList[] = (new Page())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Vorname und Name:')
                        , '22%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                                          {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                        ->styleBorderBottom()
                    )
                )->styleMarginTop('60px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('geboren am')
                        , '22%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthday is not empty) %}
                                    {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->styleBorderBottom()
                        , '20%')
                    ->addElementColumn((new Element())
                        ->setContent('in')
                        ->styleAlignCenter()
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthplace is not empty) %}
                                    {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthplace }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->styleBorderBottom()
                    )
                )->styleMarginTop('10px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('wohnhaft in')
                        , '22%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Person.Address.City.Name) %}
                                    {{ Content.P' . $personId . '.Person.Address.Street.Name }}
                                    {{ Content.P' . $personId . '.Person.Address.Street.Number }},
                                    {{ Content.P' . $personId . '.Person.Address.City.Code }}
                                    {{ Content.P' . $personId . '.Person.Address.City.Name }}
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                        ->styleBorderBottom()
                    )
                )->styleMarginTop('10px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('hat')
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Company.Data.Name) %}
                                    {{ Content.P' . $personId . '.Company.Data.Name }}
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                        ->styleBorderBottom('1px')
                        ->styleAlignCenter()
                    )
                    ->addElementColumn((new Element())
                        ->styleBorderBottom('1px')
                        ->setContent('&nbsp;')
                        , '5%')
                )
                ->styleMarginTop('20px')
            )
            ->addSlice(
                (new Slice())
                    ->addElement(
                        (new Element())
                            ->setContent('{% if(Content.P' . $personId . '.Company.Address.Street.Name) %}
                                    {{ Content.P' . $personId . '.Company.Address.Street.Name }}
                                    {{ Content.P' . $personId . '.Company.Address.Street.Number }},
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                            ->styleBorderBottom('1px')
                            ->styleAlignCenter()
                    )
                    ->styleMarginTop('10px')
            )
            ->addSlice(
                (new Slice())
                    ->addSection(
                        (new Section())
                            ->addElementColumn(
                                (new Element())
                                    ->setContent('&nbsp;')
                                    ->styleBorderBottom('1px')
                                , '10%')
                            ->addElementColumn(
                                (new Element())
                                    ->setContent('{% if(Content.P' . $personId . '.Company.Address.City.Name) %}
                                            {{ Content.P' . $personId . '.Company.Address.City.Code }}
                                            {{ Content.P' . $personId . '.Company.Address.City.Name }}
                                        {% else %}
                                              &nbsp;
                                        {% endif %}')
                                    ->styleBorderBottom('1px')
                                    ->styleAlignCenter()
                            )
                            ->addElementColumn(
                                (new Element())
                                    ->setContent('besucht')
                                    ->styleAlignRight()
                                , '10%')
                    )
                    ->styleMarginTop('10px')
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Name und Anschrift der Schule')
                    ->styleTextSize('9px')
                    ->styleTextColor('#999')
                    ->styleAlignCenter()
                    ->styleMarginTop('5px')
                    ->styleMarginBottom('5px')
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('und hat nach Bestehen der Abschlussprüfung der Schulart Mittelschule den')
                    ->styleMarginTop('8px')
                    ->styleAlignLeft()
                )
                ->addElement((new Element())
                    ->setContent('REALSCHULABSCHLUSS')
                    ->styleMarginTop('18px')
                    ->styleTextSize('20px')
                    ->styleTextBold()
                )
                ->addElement((new Element())
                    ->setContent('erworben.')
                    ->styleMarginTop('20px')
                    ->styleAlignLeft()
                )
                ->styleAlignCenter()
                ->styleMarginTop('22%')
            );

        $pageList[] = (new Page())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Vorname und Name:')
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                                          {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                        ->styleBorderBottom()
                        , '45%')
                    ->addElementColumn((new Element())
                        ->setContent('Klasse')
                        ->styleAlignCenter()
                        , '10%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Division.Data.Level.Name }}{{ Content.P' . $personId . '.Division.Data.Name }}')
                        ->styleBorderBottom()
                        ->styleAlignCenter()
                    )
                )->styleMarginTop('60px')
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Leistungen in den einzelnen Fächern:')
                    ->styleMarginTop('15px')
                    ->styleTextBold()
                )
            )
            ->addSlice($this->getSubjectLanes($personId)->styleHeight('270px'))
            /////////////////////////
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Leistungen in Fächern, die in Klassenstufe 9 abgeschlossen wurden:')
                    ->styleMarginTop('15px')
                    ->styleTextBold()
                )
            )
            ->addSlice($this->getAdditionalSubjectLanes($personId))
            /////////////////////////
            ->addSlice($this->getDescriptionHead($personId))
            ->addSlice($this->getDescriptionContent($personId, '215px', '15px'))
            ->addSlice($this->getDateLine($personId))
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '30%')
                    ->addElementColumn((new Element())
                        ->setContent('Der Prüfungsausschuss')
                        ->styleAlignCenter()
                        , '40%')
                    ->addElementColumn((new Element())
                        , '30%')
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleAlignCenter()
                        ->styleBorderBottom('1px', '#000')
                        , '30%')
                    ->addElementColumn((new Element())
                        , '40%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleAlignCenter()
                        ->styleBorderBottom('1px', '#000')
                        , '30%')
                )
                ->styleMarginTop('5px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Vorsitzende(r)')
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , '30%')
                    ->addElementColumn((new Element())
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('Dienstsiegel der Schule')
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , '30%')
                    ->addElementColumn((new Element())
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('Mitglied')
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , '30%')
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '70%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleAlignCenter()
                        ->styleBorderBottom('1px', '#000')
                        , '30%')
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '70%')
                    ->addElementColumn((new Element())
                        ->setContent('Mitglied')
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , '30%')
                )
            )
            ->addSlice($this->getInfo('120px',
                'Notenerläuterung:',
                '1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft; 6 = ungenügend')
            );

        // leere Seite
        $pageList[] = new Page();

        return $pageList;
    }

    /**
     * @param $personId
     * @param string $TextSize
     * @param bool $IsGradeUnderlined
     *
     * @return Slice
     */
    private function getAdditionalSubjectLanes(
        $personId,
        $TextSize = '14px',
        $IsGradeUnderlined = false
    ) {

        $TextSizeSmall = '8px';

        $slice = new Slice();
        if (($tblGradeList = $this->getAdditionalGrade())) {
            $count = 0;
            $section = new Section();
            foreach ($tblGradeList['Data'] as $subjectAcronym => $grade) {
                if (($tblSubject = Subject::useService()->getSubjectByAcronym($subjectAcronym))) {
                    $count++;
                    if ($count % 2 == 1) {
                        $section = new Section();
                        $slice->addSection($section);
                    } else {
                        $section->addElementColumn((new Element())
                            , '4%');
                    }

                    $section->addElementColumn((new Element())
                        ->setContent($tblSubject->getName())
                        ->stylePaddingTop()
                        ->styleMarginTop('10px')
                        ->styleTextSize($TextSize)
                        , '39%');



                    $section->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.AdditionalGrade.Data["' . $tblSubject->getAcronym() . '"] is not empty) %}
                                             {{ Content.P' . $personId . '.AdditionalGrade.Data["' . $tblSubject->getAcronym() . '"] }}
                                         {% else %}
                                             &ndash;
                                         {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#BBB')
                        ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
//                        ->stylePaddingTop('2px')
//                        ->stylePaddingBottom('2px')
                        ->stylePaddingTop(
                            '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $tblSubject->getAcronym() . '"] is not empty) %}
                                 5px
                             {% else %}
                                 2px
                             {% endif %}'
                        )
                        ->stylePaddingBottom(
                            '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $tblSubject->getAcronym() . '"] is not empty) %}
                                 6px
                             {% else %}
                                 2px
                             {% endif %}'
                        )
                        ->styleMarginTop('10px')
                        ->styleTextSize(
                            '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $tblSubject->getAcronym() . '"] is not empty) %}
                                 ' . $TextSizeSmall . '
                             {% else %}
                                 ' . $TextSize . '
                             {% endif %}'
                        )
                        , '9%');
                }
            }

            if ($count % 2 == 1) {
                $section->addElementColumn(new Element(), '52%');
            }
        }

        return $slice;
    }
}
