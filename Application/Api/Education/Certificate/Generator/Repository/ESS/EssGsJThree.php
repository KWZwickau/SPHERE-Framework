<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESS;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class EssGsHjThree
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class EssGsJThree extends EssStyle
{

    const TEXT_SIZE = '12pt';
    const TEXT_SIZE_SMALL = '11pt';
    const TEXT_SIZE_VERY_SMALL = '10pt';
    const TEXT_FAMILY = 'MyriadPro';

    /**
     * @return array
     */
    public function selectValuesTransfer()
    {
        return array(
            1 => "wird nach Klasse 4 versetzt.",
            2 => "wird nicht versetzt."
        );
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        if ($this->isSample()) {
            $Header = (new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '25%')
                    ->addElementColumn((new Element\Sample())
                        ->styleTextSize('30px')
                        ->styleHeight('1px')
                    )
                    ->addElementColumn((new Element())
                        , '25%')
                );
        } else {
            $Header = (new Slice());
        }

        return (new Page())
            ->addSlice($Header)
            ->addSlice((new Slice())
                ->addElement((new Element\Image('/Common/Style/Resource/Logo/ESS_Grundschule_Head.png', '700px'))
                    ->styleAlignCenter()
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '25%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('JAHRESZEUGNIS')
                        ->styleLineHeight('105%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->styleTextSize('24px')
                        ->styleMarginTop('7px')
                        , '75%'
                    )
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '3%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Klasse {{ Content.P' . $personId . '.Division.Data.Level.Name }}')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleLineHeight('105%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->styleTextBold()
                        , '97%'
                    )
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '3%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('2. Schulhalbjahr')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleLineHeight('105%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->styleTextBold()
                        , '22%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                                          {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                        ->styleTextSize('15pt')
                        ->styleLineHeight('105%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->styleTextBold()
                        , '50%')
                    ->addElementColumn((new Element())
                        ->setContent('Schuljahr {{ Content.P' . $personId . '.Division.Data.Year }}')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleLineHeight('105%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->styleTextBold()
                        ->styleAlignRight()
                        , '22%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '3%'
                    )
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '3%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Einschätzung <br/> Lern-, Arbeits- und<br/> Sozialverhalten')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleLineHeight('105%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->styleMarginTop('17px')
                        ->styleTextBold()
                        , '22%'
                    )
                    ->addSliceColumn(
                        self::getESSHeadGrade($personId)
                            ->styleMarginTop('2px')
//                        $this->getGradeLanes($personId)
                        , '72%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '3%'
                    )
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '3%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Allgemeine <br/> Einschätzung:')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleLineHeight('105%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->styleMarginTop('25px')
                        ->styleTextBold()
                        , '22%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P'.$personId.'.Input.Rating is not empty) %}
                                {{ Content.P'.$personId.'.Input.Rating|nl2br }}
                            {% else %}
                                &nbsp;
                            {% endif %}')
                        ->styleTextSize(self::TEXT_SIZE_SMALL)
                        ->styleLineHeight('90%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->styleAlignJustify()
                        ->styleMarginTop('25px')
                        ->styleHeight('150px')
                        , '72%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '3%'
                    )
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '3%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Leistungen in den<br/>einzelnen Fächern')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleLineHeight('105%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->styleMarginTop('15px')
                        ->styleTextBold()
                        , '22%'
                    )
                    ->addSliceColumn(
                        self::getESSSubjectLanes($personId)
                            ->styleMarginTop('15px')
//                        $this->getGradeLanes($personId)
                        , '72%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '3%'
                    )
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '15%')
                    ->addElementColumn((new Element())
                        ->setContent('Notenstufen:')
                        ->styleTextSize(self::TEXT_SIZE_VERY_SMALL)
                        ->stylePaddingTop('15px')
                        , '85%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '15%')
                    ->addElementColumn((new Element())
                        ->setContent('1 = sehr gut, 2 = gut, 3 = befriedigend, 4 = ausreichend, 5 = mangelhaft, 6 = ungenügend')
                        ->styleTextSize(self::TEXT_SIZE_VERY_SMALL)
                        , '85%')
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Fachliche <br/> Einschätzung')
                )
                ->addElement((new Element())
                    ->setContent('{% if(Content.P'.$personId.'.Input.TechnicalRating is not empty) %}
                                {{ Content.P'.$personId.'.Input.TechnicalRating|nl2br }}
                            {% else %}
                                &nbsp;
                            {% endif %}')
                    ->styleTextSize('11pt')
                    ->stylePaddingTop('10px')
                    ->stylePaddingLeft('20px')
                    ->stylePaddingRight('20px')
                )
                ->styleMarginTop('15px')
                ->styleHeight('180px')
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Datum:
                            {% if(Content.P'.$personId.'.Input.Date is not empty) %}
                                {{ Content.P'.$personId.'.Input.Date }}
                            {% else %}
                                &nbsp;
                            {% endif %}')
                    ->styleTextSize(self::TEXT_SIZE_SMALL)
                    ->stylePaddingTop('50px')
                    ->stylePaddingBottom('25px')
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('
                                {% if(Content.P'.$personId.'.DivisionTeacher.Description is not empty) %}
                                    {{ Content.P'.$personId.'.DivisionTeacher.Description }}
                                {% else %}
                                    Klassenlehrer(in)
                                {% endif %}
                            ')
                        ->styleTextSize(self::TEXT_SIZE_SMALL)
                        , '50%')
                    ->addElementColumn((new Element())
                        ->setContent('Dienstsiegel')
                        ->styleTextSize(self::TEXT_SIZE_SMALL)
                        , '25%')
                )
                ->stylePaddingBottom('40px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Zur Kenntnis genommen:')
                        ->styleTextSize(self::TEXT_SIZE_SMALL)
                        , '30%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleBorderBottom('1px', '#000', 'dotted')
                        , '40%')
                    ->addElementColumn((new Element())
                        , '30%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '30%')
                    ->addElementColumn((new Element())
                        ->setContent('Eltern')
                        ->styleAlignCenter()
                        ->stylePaddingTop('5px')
                        ->styleTextSize(self::TEXT_SIZE_SMALL)
                        , '40%')
                    ->addElementColumn((new Element())
                        , '30%')
                )->styleMarginTop('10px')
            );
    }
}
