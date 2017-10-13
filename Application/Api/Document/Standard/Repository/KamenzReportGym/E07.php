<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 11.10.2017
 * Time: 08:32
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class E07
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym
 */
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
                ->setContent('E07. Schüler im Schuljahr {{ Content.SchoolYear.Current }} nach Klassen- bzw. Jahrgangsstufen und der im vergangenen Schuljahr besuchten Schulart')
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
                    ->setContent('Im verg. Schuljahr besuchte Schulart')
                    ->styleBorderRight()
                    ->stylePaddingTop('9.3px')
                    ->stylePaddingBottom('9.3px'), '20%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klassenstufen bzw. Jahrgangsstufe')
                            ->styleBorderRight(), '1&nbsp;%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('5')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('6')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('7')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('8')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('9')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('10')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('11')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('12')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '12.5%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '6.25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '6.25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '6.25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '6.25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '6.25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '6.25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '6.25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '6.25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '6.25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '6.25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '6.25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '6.25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '6.25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '6.25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '6.25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '6.25%'
                        )
                    ), '70%'
                )
                ->addSliceColumn((new Slice())
                    ->styleTextBold()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Insgesamt')
                            ->styleBorderBottom()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.6px')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            , '50%'
                        )
                    ), '10%'
                )
            );

        self::setRowContent($sliceList, 'Grundschule', 'PrimarySchool');
        self::setRowContent($sliceList, 'Mittelschule', 'SecondarySchool');
        self::setRowContent($sliceList, 'Gymnasium', 'GrammarSchool');
        self::setRowContent($sliceList, 'Unbekannt', 'Unknown');
        self::setRowContent($sliceList, 'Insgesamt', 'TotalCount');

        return $sliceList;
    }

    /**
     * @param $sliceList
     * @param $name
     * @param $identifier
     */
    private static function setRowContent(&$sliceList, $name, $identifier)
    {

        $section = new Section();
        $section
            ->addElementColumn((new Element())
                ->setContent($name)
                ->stylePaddingLeft('5px')
                ->styleTextBold($identifier == 'TotalCount' ? 'bold' : 'normal')
                ->styleBackgroundColor('lightgrey')
                ->styleBorderRight(), '20%'
            );

        for ($level = 5; $level < 13; $level++)
        {
            $backGround = 'white';
            if ($identifier == 'PrimarySchool') {
                if ($level > 5) {
                    $backGround = 'lightgrey';
                }
            } else if ($identifier == 'SecondarySchool') {
                if ($level > 10) {
                    $backGround = 'lightgrey';
                }
            } elseif ($identifier == 'TotalCount') {
                $backGround = 'lightgrey';
            }

            $section
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E07.' . $identifier . '.L' . $level . '.m is not empty) %}
                            {{ Content.E07.' . $identifier . '.L' . $level . '.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBackgroundColor($backGround)
                    ->styleTextBold($identifier == 'TotalCount' ? 'bold' : 'normal')
                    ->styleBorderRight()
                    , '4.375%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                                {% if (Content.E07.' . $identifier . '.L' . $level . '.w is not empty) %}
                                    {{ Content.E07.' . $identifier . '.L' . $level . '.w }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                    ->styleAlignCenter()
                    ->styleBackgroundColor($backGround)
                    ->styleTextBold($identifier == 'TotalCount' ? 'bold' : 'normal')
                    ->styleBorderRight()
                    , '4.375%'
                );
        }

        $section
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.' . $identifier . '.TotalCount.m is not empty) %}
                        {{ Content.E07.' . $identifier . '.TotalCount.m }}
                    {% else %}
                        &nbsp;
                    {% endif %}
                ')
                ->styleAlignCenter()
                ->styleTextBold()
                ->styleBackgroundColor('lightgrey')
                ->styleBorderRight()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.E07.' . $identifier . '.TotalCount.w is not empty) %}
                        {{ Content.E07.' . $identifier . '.TotalCount.w }}
                    {% else %}
                        &nbsp;
                    {% endif %}
                ')
                ->styleAlignCenter()
                ->styleTextBold()
                ->styleBackgroundColor('lightgrey')
                , '5%'
            );

        $sliceList[] = (new Slice())
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection($section);
    }
}