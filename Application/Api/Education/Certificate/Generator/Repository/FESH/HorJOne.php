<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\FESH;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;

/**
 * Class HorJOne
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class HorJOne extends Certificate
{

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $TextSize = '12px';
        $TextSizeInput = '15px';

        $Header = ((new Element\Sample())
            ->styleTextSize('30px')
            ->styleHeight('12px')
        );

        if (($tblSetting = Consumer::useService()->getSetting(
                'Education', 'Certificate', 'Hormersdorf', 'RemarkTextSizeHorJOne'))
            && $tblSetting->getValue()
        ) {
            $TextSizeRemark = $tblSetting->getValue();
        } else {
            $TextSizeRemark = '13px';
        }

        return (new Page())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn(
                        ($this->isSample()
                            ? $Header
                            : ((new Element())
                                ->setContent('&nbsp;')
                                ->styleHeight('12px')
                            )
                        )
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/FESH.jpg', '155px',
                        '90px'))
                        ->styleAlignCenter()
                        , '25%')
                    ->addSliceColumn((new Slice())
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Name der Schule:')
                                ->styleTextSize('11px')
                                ->styleMarginTop('6px')
                                , '20%')
                            ->addElementColumn((new Element())
                                ->setContent('Freie Evangelische Grundschule Hormersdorf')
                                ->styleTextSize('17px')
                                ->styleTextBold()
                                ->styleBorderBottom('0.5px', '#767676')
                                ->styleAlignCenter()
                                , '78%')
                            ->addElementColumn((new Element())
                                , '2%'
                            )
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                , '27%')
                            ->addElementColumn((new Element())
                                ->setContent('(Staatlich anerkannte Ersatzschule)')
                                ->styleTextSize('11px')
                                ->styleAlignCenter()
                                , '71%')
                            ->addElementColumn((new Element())
                                , '2%'
                            )
                        )
                        ->styleMarginTop('40px')
                        , '75%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('JAHRESZEUGNIS DER GRUNDSCHULE')
                        ->styleTextSize('24px')
                        ->styleTextBold()
                        ->styleAlignCenter()
                        ->styleMarginTop('40px')
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '2%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Klasse')
                        ->styleTextSize($TextSize)
                        ->styleMarginTop('40px')
                        , '8%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Division.Data.Name }}')
                        ->styleTextSize($TextSizeInput)
                        ->styleMarginTop('38px')
                        , '43%')
                    ->addElementColumn((new Element())
                        ->setContent('Schuljahr')
                        ->styleTextSize($TextSize)
                        ->styleAlignRight()
                        ->styleMarginTop('40px')
                        , '30%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Division.Data.Year }}')
                        ->styleTextSize($TextSizeInput)
                        ->styleAlignCenter()
                        ->styleMarginTop('38px')
                        , '15%')
                    ->addElementColumn((new Element())
                        , '2%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '2%'
                    )
                    ->addElementColumn((new Element())
                        ->styleBorderBottom('0.5px', '#767676')
                        , '96%'
                    )
                    ->addElementColumn((new Element())
                        , '2%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '2%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Vor- und Zuname:')
                        ->styleTextSize($TextSize)
                        ->styleMarginTop('17px')
                        , '18%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                                          {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                        ->styleTextSize($TextSizeInput)
                        ->styleMarginTop('15px')
                        , '78%')
                    ->addElementColumn((new Element())
                        , '2%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '2%'
                    )
                    ->addElementColumn((new Element())
                        ->styleBorderBottom('0.5px', '#767676')
                        , '96%'
                    )
                    ->addElementColumn((new Element())
                        , '2%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Input.Remark is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Remark|nl2br }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->styleTextSize($TextSizeRemark)
                        ->styleHeight('490px')
                        ->styleMarginTop('35px')
                        ->stylePaddingLeft('30px')
                        ->stylePaddingRight('30px')
                        ->styleAlignJustify()
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '2%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Fehltage entschuldigt:')
                        ->styleTextSize($TextSize)
                        ->styleMarginTop('2px')
                        , '19%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Input.Missing is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        , '12%')
                    ->addElementColumn((new Element())
                        ->setContent('unentschuldigt:')
                        ->styleTextSize($TextSize)
                        ->styleMarginTop('2px')
                        , '14%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Input.Bad.Missing is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Bad.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        , '51%')
                    ->addElementColumn((new Element())
                        , '2%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '2%'
                    )
                    ->addElementColumn((new Element())
                        ->styleBorderBottom('0.5px', '#767676')
                        , '96%'
                    )
                    ->addElementColumn((new Element())
                        , '2%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '2%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Datum:')
                        ->styleTextSize($TextSize)
                        ->styleMarginTop('32px')
                        , '15%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Input.Date is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Date }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->styleMarginTop('30px')
                        , '20%')
                    ->addElementColumn((new Element())
                        ->styleMarginTop('30px')
                        , '63%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '2%'
                    )
                    ->addElementColumn((new Element())
                        ->styleBorderBottom('0.5px', '#767676')
                        , '35%'
                    )
                    ->addElementColumn((new Element())
                        , '63%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '2%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleBorderBottom('0.5px', '#767676')
                        ->styleMarginTop('30px')
                        , '33%')
                    ->addElementColumn((new Element())
                        ->setContent('Dienststempel der Schule')
                        ->styleTextSize('9px')
                        ->styleAlignCenter()
                        ->styleMarginTop('30px')
                        , '30%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleBorderBottom('0.5px', '#767676')
                        ->styleMarginTop('30px')
                        , '33%')
                    ->addElementColumn((new Element())
                        , '2%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('
                                {% if(Content.P' . $personId . '.Headmaster.Description is not empty) %}
                                    {{ Content.P' . $personId . '.Headmaster.Description }}
                                {% else %}
                                    Schulleiter(in)
                                {% endif %}'
                        )
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , '35%')
                    ->addElementColumn((new Element())
                        , '30%')
                    ->addElementColumn((new Element())
                        ->setContent('
                                {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                                    {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                                {% else %}
                                    Klassenlehrer(in)
                                {% endif %}'
                        )
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , '33%')
                    ->addElementColumn((new Element())
                        , '2%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('
                                        {% if(Content.P' . $personId . '.Headmaster.Name is not empty) %}
                                            {{ Content.P' . $personId . '.Headmaster.Name }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                        ->styleTextSize('11px')
                        ->stylePaddingTop('2px')
                        ->styleAlignCenter()
                        , '35%')
                    ->addElementColumn((new Element())
                        , '30%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.DivisionTeacher.Name is not empty) %}
                                    {{ Content.P' . $personId . '.DivisionTeacher.Name }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->styleTextSize('11px')
                        ->stylePaddingTop('2px')
                        ->styleAlignCenter()
                        , '33%')
                    ->addElementColumn((new Element())
                        , '2%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '2%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Zur Kenntnis genommen:')
                        ->styleTextSize($TextSize)
                        ->styleMarginTop('20px')
                        , '96%'
                    )
                    ->addElementColumn((new Element())
                        , '2%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '23%'
                    )
                    ->addElementColumn((new Element())
                        ->styleBorderBottom('0.5px', '#767676')
                        , '75%'
                    )
                    ->addElementColumn((new Element())
                        , '2%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Personensorgeberechtigte/r')
                        ->styleTextSize('11px')
                        ->styleAlignCenter()
                        ->stylePaddingLeft('15px')
                        ->stylePaddingRight('15px')
                    )
                )
                ->styleBorderAll('2px', '#767676')
                ->styleHeight('1020px')
            );
    }
}
