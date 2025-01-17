<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 23.06.2017
 * Time: 14:43
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReport;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

class E08
{
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('E08. Wiederholer im Schuljahr {{ Content.SchoolYear.Current }} nach Klassenstufen')
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
                    ->setContent('Merkmal')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->stylePaddingTop('17.6px')
                    ->stylePaddingBottom('17.6px'), '30%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klassenstufe')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '1&nbsp;%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('5')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.67%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('6')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.66%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('7')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.67%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('8')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.67%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('9')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.66%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('10')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.67%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '8.34%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '8.33%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '8.33%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '8.34%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '8.33%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '8.33%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '8.33%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '8.33%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '8.34%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '8.33%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '8.33%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '8.34%'
                        )
                    ), '60%'
                )
                ->addSliceColumn((new Slice())
                    ->styleTextBold()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Insgesamt')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.6px')
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
                            , '50%'
                        )
                    ), '10%'
                )
            );

        $section = new Section();
        $section
            ->addElementColumn((new Element())
                ->setContent('Schüler')
                ->styleBorderRight(), '30%'
            );

        for ($i = 0; $i < 14; $i++) {
            if ($i <13) {
                $section
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleBorderRight(), '5%'
                    );
            } else {
                $section
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '5%'
                    );
            }
        }
        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection($section);

        $sliceList[] = (new Slice())
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('davon mit<br/>dem Ziel')
                    ->styleBackgroundColor('lightgrey')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->stylePaddingTop('18.1px')
                    , '10%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Hauptschulabschluss')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '22.22%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.HS.L5.m is not empty) %}
                                            {{ Content.E08.HS.L5.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.HS.L5.w is not empty) %}
                                            {{ Content.E08.HS.L5.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.HS.L6.m is not empty) %}
                                            {{ Content.E08.HS.L6.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.HS.L6.w is not empty) %}
                                            {{ Content.E08.HS.L6.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.HS.L7.m is not empty) %}
                                            {{ Content.E08.HS.L7.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.HS.L7.w is not empty) %}
                                            {{ Content.E08.HS.L7.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.HS.L8.m is not empty) %}
                                            {{ Content.E08.HS.L8.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.HS.L8.w is not empty) %}
                                            {{ Content.E08.HS.L8.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.HS.L9.m is not empty) %}
                                            {{ Content.E08.HS.L9.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.HS.L9.w is not empty) %}
                                            {{ Content.E08.HS.L9.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.HS.L10.m is not empty) %}
                                            {{ Content.E08.HS.L10.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.HS.L10.w is not empty) %}
                                            {{ Content.E08.HS.L10.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.HS.TotalCount.m is not empty) %}
                                            {{ Content.E08.HS.TotalCount.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleBorderBottom()
                                    ->styleAlignCenter()
                                    ->styleTextBold()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.HS.TotalCount.w is not empty) %}
                                            {{ Content.E08.HS.TotalCount.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleAlignCenter()
                                    ->styleTextBold()
                                    ->styleBorderBottom(), '5.55%'
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Realschulabschluss')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '22.22%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.RS.L5.m is not empty) %}
                                            {{ Content.E08.RS.L5.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.RS.L5.w is not empty) %}
                                            {{ Content.E08.RS.L5.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.RS.L6.m is not empty) %}
                                            {{ Content.E08.RS.L6.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.RS.L6.w is not empty) %}
                                            {{ Content.E08.RS.L6.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.RS.L7.m is not empty) %}
                                            {{ Content.E08.RS.L7.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.RS.L7.w is not empty) %}
                                            {{ Content.E08.RS.L7.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.RS.L8.m is not empty) %}
                                            {{ Content.E08.RS.L8.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.RS.L8.w is not empty) %}
                                            {{ Content.E08.RS.L8.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.RS.L9.m is not empty) %}
                                            {{ Content.E08.RS.L9.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.RS.L9.w is not empty) %}
                                            {{ Content.E08.RS.L9.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.RS.L10.m is not empty) %}
                                            {{ Content.E08.RS.L10.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.RS.L10.w is not empty) %}
                                            {{ Content.E08.RS.L10.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.RS.TotalCount.m is not empty) %}
                                            {{ Content.E08.RS.TotalCount.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleAlignCenter()
                                    ->styleTextBold()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.RS.TotalCount.w is not empty) %}
                                            {{ Content.E08.RS.TotalCount.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleAlignCenter()
                                    ->styleTextBold()
                                    ->styleBorderBottom(), '5.55%'
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('ohne abs. Unterricht')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleBorderRight(), '22.22%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.WithoutCourse.L5.m is not empty) %}
                                            {{ Content.E08.WithoutCourse.L5.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.WithoutCourse.L5.w is not empty) %}
                                            {{ Content.E08.WithoutCourse.L5.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.WithoutCourse.L6.m is not empty) %}
                                            {{ Content.E08.WithoutCourse.L6.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.WithoutCourse.L6.w is not empty) %}
                                            {{ Content.E08.WithoutCourse.L6.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.WithoutCourse.L7.m is not empty) %}
                                            {{ Content.E08.WithoutCourse.L7.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.WithoutCourse.L7.w is not empty) %}
                                            {{ Content.E08.WithoutCourse.L7.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.WithoutCourse.L8.m is not empty) %}
                                            {{ Content.E08.WithoutCourse.L8.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.WithoutCourse.L8.w is not empty) %}
                                            {{ Content.E08.WithoutCourse.L8.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.WithoutCourse.L9.m is not empty) %}
                                            {{ Content.E08.WithoutCourse.L9.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.WithoutCourse.L9.w is not empty) %}
                                            {{ Content.E08.WithoutCourse.L9.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.WithoutCourse.L10.m is not empty) %}
                                            {{ Content.E08.WithoutCourse.L10.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.WithoutCourse.L10.w is not empty) %}
                                            {{ Content.E08.WithoutCourse.L10.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.WithoutCourse.TotalCount.m is not empty) %}
                                            {{ Content.E08.WithoutCourse.TotalCount.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleBorderRight()
                                    ->styleAlignCenter()
                                    ->styleTextBold()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E08.WithoutCourse.TotalCount.w is not empty) %}
                                            {{ Content.E08.WithoutCourse.TotalCount.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleAlignCenter()
                                    ->styleTextBold()
                                    , '5.55%'
                                )
                            )
                        )
                    ), '90%'
                )
            );

        return $sliceList;
    }
}