<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESS;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Layout\Repository\Container;

/**
 * Class EssGsJThree
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class EssGsJThree extends EssStyle
{

    const TEXT_SIZE_BIG = '18pt';
    const TEXT_SIZE = '12pt';
    const TEXT_SIZE_SMALL = '8pt';
    const TEXT_FAMILY = 'MyriadPro';

    const TEXT_SIZE_RATING = '11pt'; // 10pt
    const LINE_HEIGHT_RATING = '93%'; // 95%

    /**
     * @return array
     */
    public function selectValuesTransfer()
    {
        return array(
            1 => "wird nach Klasse 4 versetzt",
            2 => "wiederholt freiwillig die Klassenstufe 3",
            3 => "&nbsp;"
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
                        ->styleMarginTop('-110px')
                    )
                    ->addElementColumn((new Element())
                        , '25%')
                );
        } else {
            $Header = (new Slice());
        }

        return (new Page())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleHeight('110px')
                    )
                    ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ESS_Grundschule_Head.jpg', '700px')))
                )
            )
            ->addSlice($Header)
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '25%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('JAHRESZEUGNIS')
                        ->styleLineHeight(self::LINE_HEIGHT_RATING)
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->styleTextSize(self::TEXT_SIZE_BIG)
                        ->styleMarginTop('20px')
                        , '75%'
                    )
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('&nbsp;')
                    ->stylePaddingTop('4px')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Klasse {{ Content.P' . $personId . '.Division.Data.Level.Name }}')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleLineHeight(self::LINE_HEIGHT_RATING)
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->styleTextBold()
                        , '100%'
                    )
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('2. Schulhalbjahr')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleLineHeight(self::LINE_HEIGHT_RATING)
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->styleTextBold()
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                                          {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                        ->styleTextSize(self::TEXT_SIZE_BIG)
                        ->styleLineHeight('65%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        , '50%')
                    ->addElementColumn((new Element())
                        ->setContent('Schuljahr {{ Content.P' . $personId . '.Division.Data.Year }}')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleLineHeight(self::LINE_HEIGHT_RATING)
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->styleTextBold()
                        ->styleAlignRight()
                        , '25%')
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '25%'
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
                        , '25%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent(new Container('Grad der Ausprägung:')
                        .new Container('1 = vorbildlich, 2 = stark, 3 = durchschnittlich, 4 = schwach, 5 = unzureichend'))
                        ->styleTextSize(self::TEXT_SIZE_SMALL)
                        ->styleLineHeight(self::LINE_HEIGHT_RATING)
                        ->styleFontFamily(self::TEXT_FAMILY)
                    )
                )
                ->stylePaddingTop('10px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Einschätzung <br/> Lern-, Arbeits- und<br/> Sozialverhalten')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleLineHeight(self::LINE_HEIGHT_RATING)
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->styleMarginTop('12px')
                        ->styleTextBold()
                        , '25%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P'.$personId.'.Input.Rating is not empty) %}
                                {{ Content.P'.$personId.'.Input.Rating|nl2br }}
                            {% else %}
                                &nbsp;
                            {% endif %}')
                        ->styleTextSize(self::TEXT_SIZE_RATING)
                        ->styleLineHeight(self::LINE_HEIGHT_RATING)
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->styleAlignJustify()
                        ->styleMarginTop('15px')
                        , '75%'
                    )
                )
                ->styleHeight('115px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Leistungen in den<br/>einzelnen Fächern')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleLineHeight(self::LINE_HEIGHT_RATING)
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->styleTextBold()
                        , '25%'
                    )
                    ->addSliceColumn(
                        self::getESSSubjectLanes($personId)
                        , '75%'
                    )
                )
                ->styleMarginTop('20px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent(new Container('Notenstufen:').
                            new Container('1 = sehr gut, 2 = gut, 3 = befriedigend, 4 = ausreichend, 5 = mangelhaft, 6 = ungenügend'))
                        ->styleTextSize(self::TEXT_SIZE_SMALL)
                        ->styleLineHeight(self::LINE_HEIGHT_RATING)
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->stylePaddingTop('10px')
                        , '75%')
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Fachliche <br/> Einschätzung')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleLineHeight(self::LINE_HEIGHT_RATING)
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->styleMarginTop('15px')
                        ->styleTextBold()
                        , '25%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P'.$personId.'.Input.TechnicalRating is not empty) %}
                                    {{ Content.P'.$personId.'.Input.TechnicalRating|nl2br }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->styleTextSize(self::TEXT_SIZE_RATING)
                        ->styleLineHeight(self::LINE_HEIGHT_RATING)
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->styleAlignJustify()
                        ->styleMarginTop('15px')
                        , '75%'
                    )
                )
                ->styleHeight('140px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Versetzungsvermerk')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleLineHeight(self::LINE_HEIGHT_RATING)
                        ->styleFontFamily(self::TEXT_FAMILY)
                        , '25%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Input.Transfer) %}
                            {{ Content.P' . $personId . '.Input.Transfer }}.
                        {% else %}
                              &nbsp;
                        {% endif %}')
                        ->styleTextSize(self::TEXT_SIZE_RATING)
                        ->styleLineHeight(self::LINE_HEIGHT_RATING)
                        ->styleFontFamily(self::TEXT_FAMILY)
                        , '75%'
                    )
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Datum:
                                {% if(Content.P'.$personId.'.Input.Date is not empty) %}
                                    {{ Content.P'.$personId.'.Input.Date }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleLineHeight(self::LINE_HEIGHT_RATING)
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->stylePaddingTop('15px')
                        ->stylePaddingBottom('20px')
                        , '100%'
                    )
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if(Content.P' . $personId . '.Headmaster.Description is not empty) %}
                                {{ Content.P' . $personId . '.Headmaster.Description }}
                            {% else %}
                                Schulleiter(in)
                            {% endif %}
                            ')
                        ->styleTextSize(self::TEXT_SIZE_SMALL)
                        ->styleLineHeight(self::LINE_HEIGHT_RATING)
                        ->styleFontFamily(self::TEXT_FAMILY)
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                                {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                            {% else %}
                                Klassenlehrer(in)
                            {% endif %}
                            ')
                        ->styleTextSize(self::TEXT_SIZE_SMALL)
                        ->styleLineHeight(self::LINE_HEIGHT_RATING)
                        ->styleFontFamily(self::TEXT_FAMILY)
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('Dienstsiegel')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleLineHeight(self::LINE_HEIGHT_RATING)
                        ->styleFontFamily(self::TEXT_FAMILY)
                        , '25%')
                )
                ->stylePaddingBottom('30px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Zur Kenntnis genommen:')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleLineHeight(self::LINE_HEIGHT_RATING)
                        ->styleFontFamily(self::TEXT_FAMILY)
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleBorderBottom('1px', '#000', 'dotted')
                        , '45%')
                    ->addElementColumn((new Element())
                        , '30%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('Eltern')
                        ->styleAlignCenter()
                        ->styleTextSize(self::TEXT_SIZE_SMALL)
                        ->styleLineHeight(self::LINE_HEIGHT_RATING)
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->styleHeight('1px')
                        , '45%')
                    ->addElementColumn((new Element())
                        , '30%')
                )
                ->styleMarginTop('5px')
            );
    }
}
