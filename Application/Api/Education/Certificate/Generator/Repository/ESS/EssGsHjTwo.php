<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESS;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class EssGsHjTwo
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class EssGsHjTwo extends Certificate
{

    const TEXT_SIZE = '11pt';
    const TEXT_SIZE_SMALL = '10pt';
    const TEXT_SIZE_VERY_SMALL = '8pt';

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
                    ->setContent('HALBJAHRESINFORMATION')
                    ->styleTextSize('24px')
                    ->styleAlignCenter()
                    ->styleMarginTop('60px')
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '3%')
                    ->addElementColumn((new Element())
                        ->setContent('Klasse {{ Content.P' . $personId . '.Division.Data.Level.Name }}')
                        ->styleTextSize(self::TEXT_SIZE)
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '3%')
                    ->addElementColumn((new Element())
                        ->setContent('1. Schulhalbjahr')
                        ->styleTextSize(self::TEXT_SIZE)
                        , '20%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                                          {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                        ->styleTextSize('15pt')
                        , '54%')
                    ->addElementColumn((new Element())
                        ->setContent('Schuljahr {{ Content.P' . $personId . '.Division.Data.Year }}')
                        ->styleAlignCenter()
                        ->styleTextSize(self::TEXT_SIZE)
                        , '20%')
                    ->addElementColumn((new Element())
                        , '3%')
                )->styleMarginTop('55px')
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Allgemeine <br/> Einschätzung')
                )
                ->addElement((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Input.Remark is not empty) %}
                                {{ Content.P' . $personId . '.Input.Remark|nl2br }}
                            {% else %}
                                &nbsp;
                            {% endif %}')
                    ->styleTextSize('11pt')
                )
                ->styleMarginTop('15px')
                ->styleHeight('300px')
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Fachliche <br/> Einschätzung')
                )
                ->addElement((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Input.Rating is not empty) %}
                                {{ Content.P' . $personId . '.Input.Rating|nl2br }}
                            {% else %}
                                &nbsp;
                            {% endif %}')
                    ->styleTextSize('11pt')
                )
                ->styleMarginTop('15px')
                ->styleHeight('300px')
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Datum:
                            {% if(Content.P' . $personId . '.Input.Date is not empty) %}
                                {{ Content.P' . $personId . '.Input.Date }}
                            {% else %}
                                &nbsp;
                            {% endif %}')
                    ->styleTextSize(self::TEXT_SIZE_SMALL)
                    ->stylePaddingBottom('25px')
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
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
                        , '50%')
                    ->addElementColumn((new Element())
                        ->setContent('Dienstsiegel')
                        ->styleTextSize(self::TEXT_SIZE_SMALL)
                        , '25%')
                )
                ->stylePaddingBottom('30px')
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
