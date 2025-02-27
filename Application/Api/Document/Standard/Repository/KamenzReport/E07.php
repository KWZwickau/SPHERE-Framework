<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 27.09.2017
 * Time: 14:23
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReport;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;

class E07
{
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('E07. Schüler im Schuljahr {{ Content.SchoolYear.Current }} nach Klassenstufen und der im vergangenen Schuljahr besuchten Schulart')
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
                    ->setContent('Im vergangenen Schuljahr besuchte Schulart')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->stylePaddingTop('9.3px')
                    ->stylePaddingBottom('9.3px'), '30%'
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
                ->setContent('Grundschule')
                ->stylePaddingLeft('5px')
                ->styleBackgroundColor('lightgrey')
                ->styleBorderRight(), '30%'
            );

        for ($i = 0; $i < 13; $i++) {
            if ($i <12) {
                if ($i == 0) {
                    $section
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if (Content.E07.PrimarySchool.L5.m is not empty) %}
                                    {{ Content.E07.PrimarySchool.L5.m }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                            ->styleAlignCenter()
                            ->styleBorderRight()
                            , '5%'
                        );
                } elseif ($i == 1) {
                    $section
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if (Content.E07.PrimarySchool.L5.w is not empty) %}
                                    {{ Content.E07.PrimarySchool.L5.w }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                            ->styleAlignCenter()
                            ->styleBorderRight()
                            , '5%'
                        );
                } else {
                    $section
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBackgroundColor('lightgrey')
                            ->styleBorderRight(), '5%'
                        );
                }
            } else {
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E07.PrimarySchool.TotalCount.m is not empty) %}
                                {{ Content.E07.PrimarySchool.TotalCount.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}                                    
                        ')
                        ->styleBackgroundColor('lightgrey')
                        ->styleBorderRight()
                        ->styleAlignCenter()
                        ->styleTextBold()
                        , '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E07.PrimarySchool.TotalCount.w is not empty) %}
                                {{ Content.E07.PrimarySchool.TotalCount.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}                                    
                        ')
                        ->styleBackgroundColor('lightgrey')
                        ->styleTextBold()
                        ->styleAlignCenter()
                        , '5%'
                    );
            }
        }
        $sliceList[] = (new Slice())
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection($section);


        $section = new Section();
        $section
            ->addElementColumn((new Element())
                ->setContent(TblType::IDENT_OBER_SCHULE)
                ->stylePaddingLeft('5px')
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
                    ->stylePaddingTop('9.3px')
                    ->stylePaddingBottom('9.3px')
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
                                        {% if (Content.E07.SecondarySchoolHs.L5.m is not empty) %}
                                            {{ Content.E07.SecondarySchoolHs.L5.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E07.SecondarySchoolHs.L5.w is not empty) %}
                                            {{ Content.E07.SecondarySchoolHs.L5.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E07.SecondarySchoolHs.L6.m is not empty) %}
                                            {{ Content.E07.SecondarySchoolHs.L6.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E07.SecondarySchoolHs.L6.w is not empty) %}
                                            {{ Content.E07.SecondarySchoolHs.L6.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E07.SecondarySchoolHs.L7.m is not empty) %}
                                            {{ Content.E07.SecondarySchoolHs.L7.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E07.SecondarySchoolHs.L7.w is not empty) %}
                                            {{ Content.E07.SecondarySchoolHs.L7.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E07.SecondarySchoolHs.L8.m is not empty) %}
                                            {{ Content.E07.SecondarySchoolHs.L8.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E07.SecondarySchoolHs.L8.w is not empty) %}
                                            {{ Content.E07.SecondarySchoolHs.L8.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E07.SecondarySchoolHs.L9.m is not empty) %}
                                            {{ Content.E07.SecondarySchoolHs.L9.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E07.SecondarySchoolHs.L9.w is not empty) %}
                                            {{ Content.E07.SecondarySchoolHs.L9.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E07.SecondarySchoolHs.L10.m is not empty) %}
                                            {{ Content.E07.SecondarySchoolHs.L10.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E07.SecondarySchoolHs.L10.w is not empty) %}
                                            {{ Content.E07.SecondarySchoolHs.L10.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E07.SecondarySchoolHs.TotalCount.m is not empty) %}
                                            {{ Content.E07.SecondarySchoolHs.TotalCount.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleTextBold()
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    ->styleAlignCenter()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E07.SecondarySchoolHs.TotalCount.w is not empty) %}
                                            {{ Content.E07.SecondarySchoolHs.TotalCount.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleTextBold()
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    , '5.55%'
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
                                        {% if (Content.E07.SecondarySchoolRs.L5.m is not empty) %}
                                            {{ Content.E07.SecondarySchoolRs.L5.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E07.SecondarySchoolRs.L5.w is not empty) %}
                                            {{ Content.E07.SecondarySchoolRs.L5.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E07.SecondarySchoolRs.L6.m is not empty) %}
                                            {{ Content.E07.SecondarySchoolRs.L6.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E07.SecondarySchoolRs.L6.w is not empty) %}
                                            {{ Content.E07.SecondarySchoolRs.L6.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E07.SecondarySchoolRs.L7.m is not empty) %}
                                            {{ Content.E07.SecondarySchoolRs.L7.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E07.SecondarySchoolRs.L7.w is not empty) %}
                                            {{ Content.E07.SecondarySchoolRs.L7.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E07.SecondarySchoolRs.L8.m is not empty) %}
                                            {{ Content.E07.SecondarySchoolRs.L8.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E07.SecondarySchoolRs.L8.w is not empty) %}
                                            {{ Content.E07.SecondarySchoolRs.L8.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E07.SecondarySchoolRs.L9.m is not empty) %}
                                            {{ Content.E07.SecondarySchoolRs.L9.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E07.SecondarySchoolRs.L9.w is not empty) %}
                                            {{ Content.E07.SecondarySchoolRs.L9.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E07.SecondarySchoolRs.L10.m is not empty) %}
                                            {{ Content.E07.SecondarySchoolRs.L10.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E07.SecondarySchoolRs.L10.w is not empty) %}
                                            {{ Content.E07.SecondarySchoolRs.L10.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E07.SecondarySchoolRs.TotalCount.m is not empty) %}
                                            {{ Content.E07.SecondarySchoolRs.TotalCount.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleTextBold()
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    ->styleAlignCenter()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E07.SecondarySchoolRs.TotalCount.w is not empty) %}
                                            {{ Content.E07.SecondarySchoolRs.TotalCount.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleTextBold()
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    , '5.55%'
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
                                        {% if (Content.E07.SecondarySchool.L5.m is not empty) %}
                                            {{ Content.E07.SecondarySchool.L5.m }}
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
                                        {% if (Content.E07.SecondarySchool.L5.w is not empty) %}
                                            {{ Content.E07.SecondarySchool.L5.w }}
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
                                        {% if (Content.E07.SecondarySchool.L6.m is not empty) %}
                                            {{ Content.E07.SecondarySchool.L6.m }}
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
                                        {% if (Content.E07.SecondarySchool.L6.w is not empty) %}
                                            {{ Content.E07.SecondarySchool.L6.w }}
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
                                        {% if (Content.E07.SecondarySchool.L7.m is not empty) %}
                                            {{ Content.E07.SecondarySchool.L7.m }}
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
                                        {% if (Content.E07.SecondarySchool.L7.w is not empty) %}
                                            {{ Content.E07.SecondarySchool.L7.w }}
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
                                        {% if (Content.E07.SecondarySchool.L8.m is not empty) %}
                                            {{ Content.E07.SecondarySchool.L8.m }}
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
                                        {% if (Content.E07.SecondarySchool.L8.w is not empty) %}
                                            {{ Content.E07.SecondarySchool.L8.w }}
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
                                        {% if (Content.E07.SecondarySchool.L9.m is not empty) %}
                                            {{ Content.E07.SecondarySchool.L9.m }}
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
                                        {% if (Content.E07.SecondarySchool.L9.w is not empty) %}
                                            {{ Content.E07.SecondarySchool.L9.w }}
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
                                        {% if (Content.E07.SecondarySchool.L10.m is not empty) %}
                                            {{ Content.E07.SecondarySchool.L10.m }}
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
                                        {% if (Content.E07.SecondarySchool.L10.w is not empty) %}
                                            {{ Content.E07.SecondarySchool.L10.w }}
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
                                        {% if (Content.E07.SecondarySchool.TotalCount.m is not empty) %}
                                            {{ Content.E07.SecondarySchool.TotalCount.m }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleTextBold()
                                    ->styleBorderRight()
                                    ->styleAlignCenter()
                                    , '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if (Content.E07.SecondarySchool.TotalCount.w is not empty) %}
                                            {{ Content.E07.SecondarySchool.TotalCount.w }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}                                    
                                    ')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleTextBold()
                                    ->styleAlignCenter()
                                    , '5.55%'
                                )
                            )
                        )
                    ), '90%'
                )
            );

        $section = new Section();
        $section
            ->addElementColumn((new Element())
                ->setContent('Gymnasium')
                ->stylePaddingLeft('5px')
                ->styleBackgroundColor('lightgrey')
                ->styleBorderRight(), '30%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.GrammarSchool.L5.m is not empty) %}
                        {{ Content.E07.GrammarSchool.L5.m }}
                    {% else %}
                        &nbsp;
                    {% endif %}
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.GrammarSchool.L5.w is not empty) %}
                        {{ Content.E07.GrammarSchool.L5.w }}
                    {% else %}
                        &nbsp;
                    {% endif %}
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.GrammarSchool.L6.m is not empty) %}
                        {{ Content.E07.GrammarSchool.L6.m }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.GrammarSchool.L6.w is not empty) %}
                        {{ Content.E07.GrammarSchool.L6.w }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.GrammarSchool.L7.m is not empty) %}
                        {{ Content.E07.GrammarSchool.L7.m }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.GrammarSchool.L7.w is not empty) %}
                        {{ Content.E07.GrammarSchool.L7.w }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.GrammarSchool.L8.m is not empty) %}
                        {{ Content.E07.GrammarSchool.L8.m }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.GrammarSchool.L8.w is not empty) %}
                        {{ Content.E07.GrammarSchool.L8.w }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.GrammarSchool.L9.m is not empty) %}
                        {{ Content.E07.GrammarSchool.L9.m }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.GrammarSchool.L9.w is not empty) %}
                        {{ Content.E07.GrammarSchool.L9.w }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.GrammarSchool.L10.m is not empty) %}
                        {{ Content.E07.GrammarSchool.L10.m }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.GrammarSchool.L10.w is not empty) %}
                        {{ Content.E07.GrammarSchool.L10.w }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.GrammarSchool.TotalCount.m is not empty) %}
                        {{ Content.E07.GrammarSchool.TotalCount.m }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleBackgroundColor('lightgrey')
                ->styleTextBold()
                ->styleBorderRight()
                ->styleAlignCenter()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.GrammarSchool.TotalCount.w is not empty) %}
                        {{ Content.E07.GrammarSchool.TotalCount.w }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleBackgroundColor('lightgrey')
                ->styleTextBold()
                ->styleAlignCenter()
                , '5%'
            );

        $sliceList[] = (new Slice())
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection($section);

        $section = new Section();
        $section
            ->addElementColumn((new Element())
                ->setContent('Unbekannt*')
                ->stylePaddingLeft('5px')
                ->styleTextColor('red')
                ->styleBackgroundColor('lightgrey')
                ->styleBorderRight(), '30%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.Unknown.L5.m is not empty) %}
                        {{ Content.E07.Unknown.L5.m }}
                    {% else %}
                        &nbsp;
                    {% endif %}
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.Unknown.L5.w is not empty) %}
                        {{ Content.E07.Unknown.L5.w }}
                    {% else %}
                        &nbsp;
                    {% endif %}
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.Unknown.L6.m is not empty) %}
                        {{ Content.E07.Unknown.L6.m }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.Unknown.L6.w is not empty) %}
                        {{ Content.E07.Unknown.L6.w }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.Unknown.L7.m is not empty) %}
                        {{ Content.E07.Unknown.L7.m }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.Unknown.L7.w is not empty) %}
                        {{ Content.E07.Unknown.L7.w }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.Unknown.L8.m is not empty) %}
                        {{ Content.E07.Unknown.L8.m }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.Unknown.L8.w is not empty) %}
                        {{ Content.E07.Unknown.L8.w }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.Unknown.L9.m is not empty) %}
                        {{ Content.E07.Unknown.L9.m }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.Unknown.L9.w is not empty) %}
                        {{ Content.E07.Unknown.L9.w }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.Unknown.L10.m is not empty) %}
                        {{ Content.E07.Unknown.L10.m }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.Unknown.L10.w is not empty) %}
                        {{ Content.E07.Unknown.L10.w }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.Unknown.TotalCount.m is not empty) %}
                        {{ Content.E07.Unknown.TotalCount.m }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleBackgroundColor('lightgrey')
                ->styleTextBold()
                ->styleBorderRight()
                ->styleAlignCenter()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.Unknown.TotalCount.w is not empty) %}
                        {{ Content.E07.Unknown.TotalCount.w }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleBackgroundColor('lightgrey')
                ->styleTextBold()
                ->styleAlignCenter()
                , '5%'
            );

        $sliceList[] = (new Slice())
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection($section);

        $section = new Section();
        $section
            ->addElementColumn((new Element())
                ->setContent('Insgesamt')
                ->stylePaddingLeft('5px')
                ->styleBorderRight(), '30%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.TotalCount.L5.m is not empty) %}
                        {{ Content.E07.TotalCount.L5.m }}
                    {% else %}
                        &nbsp;
                    {% endif %}
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.TotalCount.L5.w is not empty) %}
                        {{ Content.E07.TotalCount.L5.w }}
                    {% else %}
                        &nbsp;
                    {% endif %}
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.TotalCount.L6.m is not empty) %}
                        {{ Content.E07.TotalCount.L6.m }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.TotalCount.L6.w is not empty) %}
                        {{ Content.E07.TotalCount.L6.w }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.TotalCount.L7.m is not empty) %}
                        {{ Content.E07.TotalCount.L7.m }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.TotalCount.L7.w is not empty) %}
                        {{ Content.E07.TotalCount.L7.w }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.TotalCount.L8.m is not empty) %}
                        {{ Content.E07.TotalCount.L8.m }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.TotalCount.L8.w is not empty) %}
                        {{ Content.E07.TotalCount.L8.w }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.TotalCount.L9.m is not empty) %}
                        {{ Content.E07.TotalCount.L9.m }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.TotalCount.L9.w is not empty) %}
                        {{ Content.E07.TotalCount.L9.w }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.TotalCount.L10.m is not empty) %}
                        {{ Content.E07.TotalCount.L10.m }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.TotalCount.L10.w is not empty) %}
                        {{ Content.E07.TotalCount.L10.w }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleAlignCenter()
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.TotalCount.TotalCount.m is not empty) %}
                        {{ Content.E07.TotalCount.TotalCount.m }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleBackgroundColor('lightgrey')
                ->styleBorderRight()
                ->styleAlignCenter()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.TotalCount.TotalCount.w is not empty) %}
                        {{ Content.E07.TotalCount.TotalCount.w }}
                    {% else %}
                        &nbsp;
                    {% endif %}                                    
                ')
                ->styleBackgroundColor('lightgrey')
                ->styleAlignCenter()
                , '5%'
            );

        $sliceList[] = (new Slice())
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->styleBackgroundColor('lightgrey')
            ->styleTextBold()
            ->addSection($section);

        $sliceList[] = (new Slice())
            ->addSection((new Section)
                ->addElementColumn((new Element())
                    ->setContent('* Für diese Schüler konnte das letzte Schuljahr in der Schulsoftware nicht bestimmt werden. Diese Schülerzahlen müssen manuell eingeordnet werden.')
                    ->styleMarginTop('10px')
                    ->styleTextColor('red')
                    ->styleTextSize('10px')
                )
            );

        return $sliceList;
    }
}