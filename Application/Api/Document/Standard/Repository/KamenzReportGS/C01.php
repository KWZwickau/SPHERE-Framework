<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 06.09.2018
 * Time: 10:26
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGS;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class C01
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGS
 */
class C01
{
    /**
     * @return Slice[]
     */
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('C01. Anzahl der Schüler, die im <u>Schuljahr {{ Content.SchoolYear.Past }}</u> 
                    diese Schule besucht haben und nicht versetzt </br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; worden sind, nach Klassenstufen')
            );

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleAlignCenter()
            ->styleBorderTop()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Merkmal')
                    ->styleBorderRight()
                    ->stylePaddingTop('17.7px')
                    ->stylePaddingBottom('17.6px'), '50%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klassen- bzw. Jahrgangsstufe')
                            ->styleBorderRight()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('1')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('2')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('3')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('4')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '5%'
                        )
                    ), '40%'
                )
                ->addSliceColumn((new Slice())
                    ->styleTextBold()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Insgesamt')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '50%'
                        )
                    ), '10%'
                )
            );

        $section = new Section();
        $section
            ->addElementColumn((new Element())
                ->setContent('Schüler')
                ->stylePaddingLeft('5px')
                ->styleBackgroundColor('lightgrey')
                ->styleBorderRight(), '50%'
            );

        for ($level = 1; $level < 5; $level++) {
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.C01.L' . $level . '.m is not empty) %}
                            {{ Content.C01.L' . $level . '.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleBackgroundColor($level == 1 ? 'lightgrey' : 'white')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.C01.L' . $level . '.w is not empty) %}
                            {{ Content.C01.L' . $level . '.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleBackgroundColor($level == 1 ? 'lightgrey' : 'white')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                );
        }

        $section
            ->addElementColumn((new Element())
                ->setContent('
                        {% if (Content.C01.TotalCount.m is not empty) %}
                            {{ Content.C01.TotalCount.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                ->styleBackgroundColor('lightgrey')
                ->styleAlignCenter()
                ->styleBorderRight(), '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                        {% if (Content.C01.TotalCount.w is not empty) %}
                            {{ Content.C01.TotalCount.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                ->styleBackgroundColor('lightgrey')
                ->styleAlignCenter()
                ->styleBorderRight(), '5%'
            );

        $sliceList[] = (new Slice())
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->addSection($section);

        return $sliceList;
    }
}