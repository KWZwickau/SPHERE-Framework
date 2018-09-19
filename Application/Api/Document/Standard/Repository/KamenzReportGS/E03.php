<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 05.09.2018
 * Time: 13:22
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGS;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class E03
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGS
 */
class E03
{
    /**
     * @return Slice[]
     */
    public static function getContent()
    {
        $sliceList = array();
        $left = '50%';
        $width = '5%';

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('E03. <u>Schüler, deren Herkunftssprache nicht oder nicht ausschließlich Deutsch ist,</u> 
                    im Schuljahr {{Content.SchoolYear.Current}} </br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; nach dem Land der Staatsangehörigkeit und Klassenstufen')
            );

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleAlignCenter()
            ->styleBorderTop()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Land der <br/> Staatsangehörigkeit')
                    ->styleBorderRight()
                    ->stylePaddingTop('26.1px')
                    ->stylePaddingBottom('26.1px'), $left
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klassenstufe')
                            ->styleBorderRight()
                            , '40%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('1')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('17.1px')
                            ->stylePaddingBottom('17px'), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('2')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('17.1px')
                            ->stylePaddingBottom('17px'), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('3')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('17.1px')
                            ->stylePaddingBottom('17px'), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('4')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('17.1px')
                            ->stylePaddingBottom('17px'), '10%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), $width
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), $width
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), $width
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), $width
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), $width
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), $width
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), $width
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), $width
                        )
                    )
                )
                ->addSliceColumn((new Slice())
                    ->styleTextBold()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Insgesamt')
                            ->styleBorderBottom()
                            ->stylePaddingTop('25.7px')
                            ->stylePaddingBottom('25.6px'), '100%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight()
                            , '50%'
                        )
                    ), '10%'
                )
            );

        for ($i = 0; $i < 10; $i++) {
            $sliceList[] = (new Slice())
                ->styleAlignCenter()
                ->styleBorderBottom()
                ->styleBorderLeft()
                ->styleBorderRight()
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E03.N' . $i . '.NationalityName is not empty) %}
                                {{ Content.E03.N' . $i . '.NationalityName }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderRight(), $left
                    )
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E03.N' . $i . '.L1.m is not empty) %}
                                {{ Content.E03.N' . $i . '.L1.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderRight(), $width
                    )
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E03.N' . $i . '.L1.w is not empty) %}
                                {{ Content.E03.N' . $i . '.L1.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderRight(), $width
                    )
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E03.N' . $i . '.L2.m is not empty) %}
                                {{ Content.E03.N' . $i . '.L2.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderRight(), $width
                    )
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E03.N' . $i . '.L2.w is not empty) %}
                                {{ Content.E03.N' . $i . '.L2.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderRight(), $width
                    )
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E03.N' . $i . '.L3.m is not empty) %}
                                {{ Content.E03.N' . $i . '.L3.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderRight(), $width
                    )
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E03.N' . $i . '.L3.w is not empty) %}
                                {{ Content.E03.N' . $i . '.L3.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderRight(), $width
                    )
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E03.N' . $i . '.L4.m is not empty) %}
                                {{ Content.E03.N' . $i . '.L4.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderRight(), $width
                    )
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E03.N' . $i . '.L4.w is not empty) %}
                                {{ Content.E03.N' . $i . '.L4.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderRight(), $width
                    )
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E03.N' . $i . '.m is not empty) %}
                                {{ Content.E03.N' . $i . '.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBackgroundColor('lightgrey')
                        ->styleBorderRight(), $width
                    )
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E03.N' . $i . '.w is not empty) %}
                                {{ Content.E03.N' . $i . '.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBackgroundColor('lightgrey'), $width
                    )
                );
        }

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleTextBold()
            ->styleAlignCenter()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Insgesamt')
                    ->styleBorderRight(), $left
                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E03.TotalCount.L1.m is not empty) %}
                                {{ Content.E03.TotalCount.L1.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBorderRight(), $width
                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E03.TotalCount.L1.w is not empty) %}
                                {{ Content.E03.TotalCount.L1.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBorderRight(), $width
                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E03.TotalCount.L2.m is not empty) %}
                                {{ Content.E03.TotalCount.L2.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBorderRight(), $width
                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E03.TotalCount.L2.w is not empty) %}
                                {{ Content.E03.TotalCount.L2.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBorderRight(), $width
                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E03.TotalCount.L3.m is not empty) %}
                                {{ Content.E03.TotalCount.L3.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBorderRight(), $width
                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E03.TotalCount.L3.w is not empty) %}
                                {{ Content.E03.TotalCount.L3.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBorderRight(), $width
                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E03.TotalCount.L4.m is not empty) %}
                                {{ Content.E03.TotalCount.L4.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBorderRight(), $width
                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E03.TotalCount.L4.w is not empty) %}
                                {{ Content.E03.TotalCount.L4.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBorderRight(), $width
                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E03.TotalCount.m is not empty) %}
                                {{ Content.E03.TotalCount.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBorderRight(), $width
                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E03.TotalCount.w is not empty) %}
                                {{ Content.E03.TotalCount.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        '), $width
                )
            );

        return $sliceList;
    }
}