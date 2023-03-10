<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EZSH;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class EzshMsAbsHsQ
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\EZSH
 */
class EzshMsAbsHsQ extends EzshStyle
{
    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null)
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

//        // leere Seite (Seite 4)
//        $pageList[] = new Page();
//
//        // leere Seite (Seite 1 - Titel - verwenden vorbedrucktes Papier
//        $pageList[] = new Page();

        $pageList[] = (new Page())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Vorname und Name:')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleLineHeight(self::LINE_HEIGHT)
                        , '22%')
                    ->addElementColumn((new Element())
                        ->setContent('
                                {{ Content.P' . $personId . '.Person.Data.Name.First }}
                                {{ Content.P' . $personId . '.Person.Data.Name.Last }}
                                                ')
                        ->styleBorderBottom('1px', '#BBB')
                        ->stylePaddingLeft('7px')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleLineHeight(self::LINE_HEIGHT)
                    )
                )->styleMarginTop('60px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('geboren am')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleLineHeight(self::LINE_HEIGHT)
                        , '22%')
                    ->addElementColumn((new Element())
                        ->setContent('
                                {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                                                ')
                        ->styleBorderBottom('1px', '#BBB')
                        ->stylePaddingLeft('7px')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleLineHeight(self::LINE_HEIGHT)
                        , '20%')
                    ->addElementColumn((new Element())
                        ->setContent('in')
                        ->styleAlignCenter()
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleLineHeight(self::LINE_HEIGHT)
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthplace is not empty) %}
                                {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthplace }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderBottom('1px', '#BBB')
                        ->stylePaddingLeft('7px')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleLineHeight(self::LINE_HEIGHT)
                    )
                )->styleMarginTop('10px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('wohnhaft in')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleLineHeight(self::LINE_HEIGHT)
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
                        ->styleBorderBottom('1px', '#BBB')
                        ->stylePaddingLeft('7px')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleLineHeight(self::LINE_HEIGHT)
                        , '78%')
                )->styleMarginTop('10px')
            )
            ->addSliceArray(self::getEZSHSchoolPart($personId))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('und hat nach Bestehen der Abschlussprüfung in der Klassenstufe 9 den')
                    ->styleMarginTop('8px')
                    ->styleAlignLeft()
                    ->styleFontFamily(self::FONT_FAMILY)
                    ->styleLineHeight(self::LINE_HEIGHT)
                )
                ->addElement((new Element())
                    ->setContent('qualifizierenden HAUPTSCHULABSCHLUSS')
                    ->styleMarginTop('30px')
                    ->styleTextSize('20px')
                    ->styleTextBold()
                    ->styleFontFamily(self::FONT_FAMILY_BOLD)
                    ->styleLineHeight(self::LINE_HEIGHT)
                )
                ->addElement((new Element())
                    ->setContent('erworben.')
                    ->styleMarginTop('20px')
                    ->styleAlignLeft()
                    ->styleFontFamily(self::FONT_FAMILY)
                    ->styleLineHeight(self::LINE_HEIGHT)
                )
                ->styleAlignCenter()
                ->styleMarginTop('15%')
            );

        $pageList[] = (new Page())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Vorname und Name:')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleLineHeight(self::LINE_HEIGHT)
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('
                                {{ Content.P' . $personId . '.Person.Data.Name.First }}
                                {{ Content.P' . $personId . '.Person.Data.Name.Last }}
                            ')
                        ->styleBorderBottom('1px', '#BBB')
                        ->stylePaddingLeft('7px')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleLineHeight(self::LINE_HEIGHT)
                        , '45%')
                    ->addElementColumn((new Element())
                        ->setContent('Klasse:')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleLineHeight(self::LINE_HEIGHT)
                        ->styleAlignCenter()
                        , '10%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Division.Data.Name }}')
                        ->styleBorderBottom('1px', '#BBB')
                        ->stylePaddingLeft('7px')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleLineHeight(self::LINE_HEIGHT)
                        ->styleAlignCenter()
                    )
                )->styleMarginTop('60px')
                ->styleMarginBottom('20px')
            )
            ->addSlice($this->getEZSHSubjectLanes($personId, true, array(), false, false, true, true)->styleHeight('350px'))
            ->addSlice((new Slice())->addSectionList($this->getEZSHRemark($personId, '170px')))
            ->addSlice($this->getEZSHDateLine($personId))
            ->addSlice((self::getEZSHExaminationsBoard('10px', '11px')))
            ->addSlice((new Slice())->styleMarginTop('150px')->addSectionList($this->getEZSHGradeInfo(false)));

        return $pageList;
    }
}