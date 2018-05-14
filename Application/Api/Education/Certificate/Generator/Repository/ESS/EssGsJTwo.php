<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESS;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class EssGsJTwo
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class EssGsJTwo extends Certificate
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
            1 => "wird nach Klasse 3 versetzt.",
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
                        ->styleMarginBottom('20px')
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
                        ->styleHeight('205px')
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
                    ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ESS_Grundschule_down.png', '500px'))
                        ->styleAlignCenter()
                        ->stylePaddingBottom('10px')
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
                        ->setContent('Fachliche <br/> Einschätzung:')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleLineHeight('105%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->styleTextBold()
                        , '22%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P'.$personId.'.Input.TechnicalRating is not empty) %}
                            {{ Content.P'.$personId.'.Input.TechnicalRating|nl2br }}
                        {% else %}
                            &nbsp;
                        {% endif %}')
                        ->styleTextSize(self::TEXT_SIZE_SMALL)
                        ->styleLineHeight('90%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->styleHeight('283px')
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
                        ->setContent('Versetzungsvermerk')
                        ->styleTextSize(self::TEXT_SIZE_VERY_SMALL)
                        ->styleLineHeight('105%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        , '22%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Input.Transfer) %}
                            {{ Content.P' . $personId . '.Input.Transfer }}
                        {% else %}
                              &nbsp;
                        {% endif %}')
                        ->styleTextSize(self::TEXT_SIZE_VERY_SMALL)
                        ->styleLineHeight('105%')
                        ->styleFontFamily(self::TEXT_FAMILY)
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
                        ->setContent('Datum:
                        {% if(Content.P' . $personId . '.Input.Date is not empty) %}
                            {{ Content.P' . $personId . '.Input.Date }}
                        {% else %}
                            &nbsp;
                        {% endif %}')
                        ->styleTextSize(self::TEXT_SIZE_VERY_SMALL)
                        ->styleLineHeight('105%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->stylePaddingBottom('20px')
                        ->stylePaddingTop('20px')
                        , '94%'
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
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if(Content.P' . $personId . '.Headmaster.Description is not empty) %}
                                {{ Content.P' . $personId . '.Headmaster.Description }}
                            {% else %}
                                Schulleiter(in)
                            {% endif %}
                            ')
                        ->styleTextSize(self::TEXT_SIZE_VERY_SMALL)
                        ->styleLineHeight('105%')
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
                        ->styleTextSize(self::TEXT_SIZE_VERY_SMALL)
                        ->styleLineHeight('105%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('Dienstsiegel')
                        ->styleTextSize(self::TEXT_SIZE_VERY_SMALL)
                        ->styleLineHeight('105%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        , '25%')
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '3%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Zur Kenntnis genommen:')
                        ->styleTextSize(self::TEXT_SIZE_VERY_SMALL)
                        ->styleLineHeight('105%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        , '22%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleBorderBottom('1px', '#000', 'dotted')
                        , '40%')
                    ->addElementColumn((new Element())
                        , '35%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('Eltern')
                        ->styleTextSize(self::TEXT_SIZE_VERY_SMALL)
                        ->styleLineHeight('105%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->styleAlignCenter()
                        , '40%')
                    ->addElementColumn((new Element())
                        , '35%')
                )->styleMarginTop('20px')
            );
    }
}
