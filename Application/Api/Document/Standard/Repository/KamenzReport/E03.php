<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 23.06.2017
 * Time: 14:08
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReport;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

class E03
{
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('E03. <u>Schüler, deren Herkunftssprache nicht oder nicht ausschließlich Deutsch ist,</u>
                    im Schuljahr {{Content.SchoolYear.Current}} <br /> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    nach dem Land der Staatsangehörigkeit und Klassenstufen')
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
                    ->setContent('Land der Staats-<br/>angehörigkeit')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->stylePaddingTop('26.1px')
                    ->stylePaddingBottom('26.1px'), '20%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klassenstufe')
                            ->styleAlignCenter()
                            ->styleBorderRight()
                            , '70%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('5')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('17.1px')
                            ->stylePaddingBottom('17px'), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('6')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('17.1px')
                            ->stylePaddingBottom('17px'), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('7')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('17.1px')
                            ->stylePaddingBottom('17px'), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('8')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('17.1px')
                            ->stylePaddingBottom('17px'), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('9')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('17.1px')
                            ->stylePaddingBottom('17px'), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('10')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('17.1px')
                            ->stylePaddingBottom('17px'), '10%'
                        )
//                        ->addElementColumn((new Element())
//                            ->setContent('Vorb.-kl. u.<br/>-gruppen f.<br/>Migranten')
//                            ->styleBorderBottom()
//                            ->styleBorderRight()
//                            , '10%'
//                        )
                        ->addElementColumn((new Element())
                            ->setContent('Sonder-<br/>klassen<br/>&nbsp;')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleAlignCenter()
                            ->styleBorderRight()
                            , '5%'
                        )
                    )
                )
                ->addSliceColumn((new Slice())
                    ->styleTextBold()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Insgesamt')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            ->stylePaddingTop('25.7px')
                            ->stylePaddingBottom('25.6px'), '100%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleAlignCenter()
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
                        ->styleAlignCenter()
                        ->styleBorderRight(), '20%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E03.N' . $i . '.L5.m is not empty) %}
                                {{ Content.E03.N' . $i . '.L5.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E03.N' . $i . '.L5.w is not empty) %}
                                {{ Content.E03.N' . $i . '.L5.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E03.N' . $i . '.L6.m is not empty) %}
                                {{ Content.E03.N' . $i . '.L6.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E03.N' . $i . '.L6.w is not empty) %}
                                {{ Content.E03.N' . $i . '.L6.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E03.N' . $i . '.L7.m is not empty) %}
                                {{ Content.E03.N' . $i . '.L7.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E03.N' . $i . '.L7.w is not empty) %}
                                {{ Content.E03.N' . $i . '.L7.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E03.N' . $i . '.L8.m is not empty) %}
                                {{ Content.E03.N' . $i . '.L8.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E03.N' . $i . '.L8.w is not empty) %}
                                {{ Content.E03.N' . $i . '.L8.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E03.N' . $i . '.L9.m is not empty) %}
                                {{ Content.E03.N' . $i . '.L9.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E03.N' . $i . '.L9.w is not empty) %}
                                {{ Content.E03.N' . $i . '.L9.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E03.N' . $i . '.L10.m is not empty) %}
                                {{ Content.E03.N' . $i . '.L10.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E03.N' . $i . '.L10.w is not empty) %}
                                {{ Content.E03.N' . $i . '.L10.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleAlignCenter()
//                        ->setContent('
//                            {% if (Content.E03.N' . $i . '.LMigration.m is not empty) %}
//                                {{ Content.E03.N' . $i . '.LMigration.m }}
//                            {% else %}
//                                &nbsp;
//                            {% endif %}
//                        ')
//                        ->styleBackgroundColor('lightgrey')
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleAlignCenter()
//                        ->setContent('
//                            {% if (Content.E03.N' . $i . '.LMigration.w is not empty) %}
//                                {{ Content.E03.N' . $i . '.LMigration.w }}
//                            {% else %}
//                                &nbsp;
//                            {% endif %}
//                        ')
//                        ->styleBackgroundColor('lightgrey')
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E03.N' . $i . '.m is not empty) %}
                                {{ Content.E03.N' . $i . '.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('lightgrey')
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E03.N' . $i . '.w is not empty) %}
                                {{ Content.E03.N' . $i . '.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('lightgrey'), '5%'
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
                    ->styleAlignCenter()
                    ->styleBorderRight(), '20%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E03.TotalCount.L5.m is not empty) %}
                                {{ Content.E03.TotalCount.L5.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E03.TotalCount.L5.w is not empty) %}
                                {{ Content.E03.TotalCount.L5.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E03.TotalCount.L6.m is not empty) %}
                                {{ Content.E03.TotalCount.L6.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E03.TotalCount.L6.w is not empty) %}
                                {{ Content.E03.TotalCount.L6.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E03.TotalCount.L7.m is not empty) %}
                                {{ Content.E03.TotalCount.L7.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E03.TotalCount.L7.w is not empty) %}
                                {{ Content.E03.TotalCount.L7.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E03.TotalCount.L8.m is not empty) %}
                                {{ Content.E03.TotalCount.L8.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E03.TotalCount.L8.w is not empty) %}
                                {{ Content.E03.TotalCount.L8.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E03.TotalCount.L9.m is not empty) %}
                                {{ Content.E03.TotalCount.L9.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E03.TotalCount.L9.w is not empty) %}
                                {{ Content.E03.TotalCount.L9.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E03.TotalCount.L10.m is not empty) %}
                                {{ Content.E03.TotalCount.L10.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E03.TotalCount.L10.w is not empty) %}
                                {{ Content.E03.TotalCount.L10.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
//                    ->setContent('
//                        {% if (Content.E03.TotalCount.LMigration.m is not empty) %}
//                            {{ Content.E03.TotalCount.LMigration.m }}
//                        {% else %}
//                            &nbsp;
//                        {% endif %}
//                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
//                    ->setContent('
//                        {% if (Content.E03.TotalCount.LMigration.w is not empty) %}
//                            {{ Content.E03.TotalCount.LMigration.w }}
//                        {% else %}
//                            &nbsp;
//                        {% endif %}
//                    ')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E03.TotalCount.m is not empty) %}
                                {{ Content.E03.TotalCount.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E03.TotalCount.w is not empty) %}
                                {{ Content.E03.TotalCount.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter(), '5%'
                )
            );

        return $sliceList;
    }
}