<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESRL;


use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class EsrlGsHjOne
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESRL
 */
class EsrlGsHjOne extends EsrlStyle
{
    const TEXT_SIZE = '12pt';

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page
     * @internal param bool $IsSample
     *
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        return (new Page())
            ->addSlice((new Slice())
                ->styleBorderAll('3px', '#050')
                ->stylePaddingTop('20px')
                ->stylePaddingLeft('20px')
                ->stylePaddingRight('20px')
                ->stylePaddingBottom('20px')
                ->addSection((new Section())
                    ->addSliceColumn(
                        self::getESRLHead()
                    )
                )
                ->addSection(
                    self::getESRLHeadLine('HALBJAHRESINFORMATION')
                )
                ->addElement((new Element())
                    ->styleMarginTop('10px')
                )
                ->addSection(
                    self::getESRLDivisionAndYear($personId)
                )
                ->addElement((new Element())
                    ->styleMarginTop('10px')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Vorname und Name:')
                        ->styleTextSize(self::TEXT_SIZE)
                        , '24%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P'.$personId.'.Person.Data.Name.First }}
                                      {{ Content.P'.$personId.'.Person.Data.Name.Last }}')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->stylePaddingLeft('5px')
                        ->styleBorderBottom('1px', '#999')
                        , '76%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P'.$personId.'.Input.Remark is not empty) %}
                            {{ Content.P'.$personId.'.Input.Remark|nl2br }}
                        {% else %}
                            &nbsp;
                        {% endif %}')
                        ->styleAlignJustify()
                        ->styleHeight('520px')
                        ->styleMarginTop('20px')
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Fehltage entschuldigt:')
                        , '22%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P'.$personId.'.Input.Missing is not empty) %}
                                    {{ Content.P'.$personId.'.Input.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
//                        ->styleAlignCenter()
                        , '10%')
                    ->addElementColumn((new Element())
                        ->setContent('unentschuldigt:')
                        , '15%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P'.$personId.'.Input.Bad.Missing is not empty) %}
                                    &nbsp;{{ Content.P'.$personId.'.Input.Bad.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
//                        ->styleAlignCenter()
                        , '10%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '43%')
                )
                ->addElement((new Element())
                    ->styleMarginTop('15px')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Datum:')
                        , '10%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P'.$personId.'.Input.Date is not empty) %}
                                {{ Content.P'.$personId.'.Input.Date }}
                            {% else %}
                                &nbsp;
                            {% endif %}')
                        ->styleBorderBottom('1px', '#999')
                        ->styleAlignCenter()
                        , '20%')
                    ->addElementColumn((new Element())
                        , '70%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '70%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleAlignCenter()
                        ->styleBorderBottom('1px', '#999')
                        ->styleMarginTop('20px')
                        , '30%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '70%')
                    ->addElementColumn((new Element())
                        ->setContent('Klassenlehrer(in)')
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , '30%')
                )
                ->addElement((new Element())
                    ->styleMarginTop('30px')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Zur Kenntnis genommen:')
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleBorderBottom('1px', '#999')
                        , '75%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '70%')
                    ->addElementColumn((new Element())
                        ->setContent('Sorgeberechtigte')
                        ->styleTextSize('11px')
                        ->styleAlignCenter()
                        , '30%')
                )
            );
    }
}