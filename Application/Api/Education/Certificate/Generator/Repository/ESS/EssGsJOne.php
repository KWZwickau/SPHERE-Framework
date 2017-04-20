<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESS;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class EssGsJOne
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class EssGsJOne extends Certificate
{

    const TEXT_SIZE = '11pt';
    const TEXT_SIZE_SMALL = '10pt';
    const TEXT_SIZE_VERY_SMALL = '8pt';

    /**
     * @return array
     */
    public function selectValuesTransfer()
    {
        return array(
            1 => "steigt auf in Klasse 2.",
            2 => "steigt nicht auf."
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

        return (new Page())
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('&nbsp;')
                    ->styleHeight('80px')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '15%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                                      {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                        ->styleTextSize('15pt')
                        , '50%')
//                        ->addElementColumn((new Element())
//                            ->setContent('2. Schulhalbjahr der Klasse {{ Content.P' . $personId . '.Division.Data.Level.Name }}')
//                            ->styleTextSize(self::TEXT_SIZE)
//                            , '35%'
//                        )
                    ->addElementColumn((new Element())
                        ->setContent('Schuljahr {{ Content.P' . $personId . '.Division.Data.Year }}')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleAlignRight()
                        , '30%'
                    )
                    ->addElementColumn((new Element())
                        , '5%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '15%')
                    ->addElementColumn((new Element())
                        ->setContent('Vor- und Zuname')
                        ->stylePaddingBottom('30px')
                        ->styleTextSize(self::TEXT_SIZE_VERY_SMALL)
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '15%')
                    ->addElementColumn((new Element())
                        ->setContent('Evangelische Grundschlue Schneeberg')
                        ->styleTextSize(self::TEXT_SIZE)
                        , '50%')
//                        )
                    ->addElementColumn((new Element())
                        ->setContent('2. Schuljahr der Klasse {{ Content.P' . $personId . '.Division.Data.Level.Name }}')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleAlignRight()
                        , '30%'
                    )
                    ->addElementColumn((new Element())
                        , '5%')
                )
                ->addElement((new Element())
                    ->setContent('Jahreszeugnis der Grundschule')
                    ->styleTextSize('16pt')
                    ->styleAlignCenter()
                    ->stylePaddingTop('70px')
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Input.Remark is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Remark|nl2br }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                    ->styleTextSize(self::TEXT_SIZE)
                    ->styleAlignJustify()
                    ->stylePaddingTop('35px')
                    ->stylePaddingRight('20px')
                    ->stylePaddingLeft('20px')
                    ->stylePaddingBottom('20px')
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('')
                        , '3%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Input.Transfer) %}
                        {{ Content.P' . $personId . '.Input.Transfer }}
                    {% else %}
                          &nbsp;
                    {% endif %}')
                        , '94%')
                    ->addElementColumn((new Element())
                        , '3%')
                )
                ->styleMarginTop('5px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '20%')
                    ->addElementColumn((new Element())
                        ->setContent('Zur Kenntnis genommen:')
                        ->styleTextSize(self::TEXT_SIZE_SMALL)
                        , '22%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleBorderBottom('1px', '#000', 'dotted')
                        , '38%')
                    ->addElementColumn((new Element())
                        , '20%')
                )
                ->styleMarginTop('80px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '42%')
                    ->addElementColumn((new Element())
                        ->setContent('Personensorgeberechtigte/r')
                        ->styleAlignCenter()
                        ->styleTextSize(self::TEXT_SIZE_VERY_SMALL)
                        , '38%')
                    ->addElementColumn((new Element())
                        , '20%')
                )
                ->styleMarginTop('3px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleBorderBottom('1px', '#000', 'dotted')
                        , '25%')
                    ->addElementColumn((new Element())
                        , '70%')
                )
                ->styleMarginTop('40px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if(Content.P' . $personId . '.Input.Remark is not empty) %}
                                    {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                            ')
                        , '25%')
                    ->addElementColumn((new Element())
                        , '40%')
                    ->addElementColumn((new Element())
                        ->setContent('Dienstsiegel')
                        , '30%')
                )
                ->styleMarginTop('3px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleBorderBottom('1px', '#000', 'dotted')
                        , '25%')
                    ->addElementColumn((new Element())
                        , '70%')
                )
                ->styleMarginTop('40px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if(Content.P' . $personId . '.Headmaster.Description is not empty) %}
                                    {{ Content.P' . $personId . '.Headmaster.Description }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                            ')
                        ->styleTextSize(self::TEXT_SIZE)
                        , '25%')
                    ->addElementColumn((new Element())
                        , '30%')
                    ->addElementColumn((new Element())
                        ->setContent('Schneeberg, den 
                            {% if(Content.P' . $personId . '.Input.Date is not empty) %}
                                {{ Content.P' . $personId . '.Input.Date }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                                ')
                        , '40%')
                )
                ->styleMarginTop('3px')
            );
    }
}
