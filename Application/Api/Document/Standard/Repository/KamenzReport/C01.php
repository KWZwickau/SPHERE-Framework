<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 07.09.2018
 * Time: 08:59
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReport;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class C01
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\KamenzReport
 */
class C01
{
    /**
     * @return Slice[]
     */
    public static function getContent()
    {
        $sliceList = array();
        $left = '30%';

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('C01. Anzahl der Schüler, die im <u>Schuljahr {{ Content.SchoolYear.Past }}</u> diese Schule
                    besucht haben und nicht versetzt </br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    worden sind, nach abschlussbezogenem Unterricht und Klassenstufen')
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
                    ->stylePaddingBottom('17.6px'), $left
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klassenstufe')
                            ->styleBorderRight()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('5')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('6')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('7')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('8')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('9')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('10')
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
                    ), '60%'
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

        self::setRow($sliceList, 'NoCourse', 'Schüler');
        self::setRow($sliceList, 'HS', 'HS-Abschluss');
        self::setRow($sliceList, 'RS', 'RS-Abschluss');

        return $sliceList;
    }

    /**
     * @param $sliceList
     * @param $identifier
     * @param $name
     */
    private static function setRow(&$sliceList, $identifier, $name)
    {

        $paddingLeft = '5px';
        $section = new Section();

        if ($identifier == 'HS') {
            $section
                ->addElementColumn((new Element())
                    ->setContent('davon mit')
                    ->styleBackgroundColor('lightgrey')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->styleBorderBottom('1px', 'lightgrey')
                    , '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent($name)
                    ->styleBackgroundColor('lightgrey')
                    ->stylePaddingLeft($paddingLeft)
                    ->styleBorderRight()
                    ->styleBorderBottom()
                    , '20%'
                );
        } elseif ($identifier == 'RS') {
            $section
                ->addElementColumn((new Element())
                    ->setContent('dem Ziel')
                    ->styleBackgroundColor('lightgrey')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->styleBorderBottom()
                    , '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent($name)
                    ->styleBackgroundColor('lightgrey')
                    ->stylePaddingLeft($paddingLeft)
                    ->styleBorderRight()
                    ->styleBorderBottom()
                    , '20%'
                );
        } else {
            $section
                ->addElementColumn((new Element())
                    ->setContent($name)
                    ->styleBackgroundColor('lightgrey')
                    ->stylePaddingLeft($paddingLeft)
                    ->styleBorderRight()
                    ->styleBorderBottom()
                    , '30%'
                );
        }

        for ($level = 5; $level < 11; $level++) {

            $isGrey = false;
            if ($identifier == 'HS' || $identifier == 'RS') {
                if ($level == 5 || $level == 6) {
                    $isGrey = true;
                }

                if ($identifier == 'HS' && $level == 10) {
                    $isGrey = true;
                }
            } else {
                if ($level > 6) {
                    $isGrey = true;
                }
            }

            $section
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.C01.' . $identifier . '.L' . $level . '.m is not empty) %}
                            {{ Content.C01.' . $identifier . '.L' . $level . '.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBackgroundColor($isGrey ? 'lightgrey' : 'white')
                    ->styleBorderRight()
                    ->styleBorderBottom()
                    , '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.C01.' . $identifier . '.L' . $level . '.w is not empty) %}
                            {{ Content.C01.' . $identifier . '.L' . $level . '.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBackgroundColor($isGrey ? 'lightgrey' : 'white')
                    ->styleBorderRight()
                    ->styleBorderBottom()
                    , '5%'
                );
        }

        $section
            ->addElementColumn((new Element())
                ->setContent('
                        {% if (Content.C01.' . $identifier . '.TotalCount.m is not empty) %}
                            {{ Content.C01.' . $identifier . '.TotalCount.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                ->styleAlignCenter()
                ->styleTextBold()
                ->styleBorderRight()
                ->styleBackgroundColor('lightgrey')
                ->styleBorderBottom()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                        {% if (Content.C01.' . $identifier . '.TotalCount.w is not empty) %}
                            {{ Content.C01.' . $identifier . '.TotalCount.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                ->styleAlignCenter()
                ->styleTextBold()
                ->styleBorderRight()
                ->styleBackgroundColor('lightgrey')
                ->styleBorderBottom()
                , '5%'
            );

        $sliceList[] = (new Slice())
            ->styleBorderLeft()
            ->addSection($section);
    }
}