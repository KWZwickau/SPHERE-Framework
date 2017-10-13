<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.10.2017
 * Time: 08:33
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReport;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class E01
 * @package SPHERE\Application\Api\Document\Standard\Repository\KamenzReport
 */
class E01
{

    /**
     * @return array
     */
    public static function getContent()
    {

        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('E01. Schüler, Klassen und Gruppen im Schuljahr {{Content.SchoolYear.Current}} nach abschlussbezogenem Unterricht und Klassenstufen')
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
                    ->styleBorderRight()
                    ->stylePaddingTop('34.7px')
                    ->stylePaddingBottom('34.7px'), '20%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klassenstufe')
                            ->styleBorderRight()
                            , '70%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('5')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('17.1px')
                            ->stylePaddingBottom('17px'), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('6')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('17.1px')
                            ->stylePaddingBottom('17px'), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('7')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('17.1px')
                            ->stylePaddingBottom('17px'), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('8')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('17.1px')
                            ->stylePaddingBottom('17px'), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('9')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('17.1px')
                            ->stylePaddingBottom('17px'), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('10')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('17.1px')
                            ->stylePaddingBottom('17px'), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Vorb.-kl. u.<br/>-gruppen f.<br/>Migranten')
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
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '5%'
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
                            ->setContent('w'), '50%'
                        )
                    ), '10%'
                )
            );

        self::setBlankRow($sliceList, 'Schüler');
        self::setBlankRow($sliceList, 'HS-Abschluss', '25px');
        self::setRow($sliceList, 'HS', 'HSA', 'Pure');
        self::setRow($sliceList, 'RS', 'HSA/RSA', 'Mixed');
        self::setBlankRow($sliceList, 'RS-Abschluss', '25px');
        self::setRow($sliceList, 'RS', 'RSA', 'Pure');
        self::setRow($sliceList, 'HS', 'RSA/HSA', 'Mixed');
        self::setRow($sliceList, 'NoCourse', 'ohne abs. bezog.', 'Pure', '25px');

        self::setDivisionBlankRow($sliceList, 'Klassen');
        self::setDivisionRow($sliceList, 'HS', 'nur HS');
        self::setDivisionRow($sliceList, 'RS', 'nur RS');
        self::setDivisionRow($sliceList, 'Mixed', 'HS + RS');
        self::setDivisionRow($sliceList, 'NoCourse', 'ohne abs. bezog.');

        return $sliceList;
    }

    /**
     * @param $sliceList
     * @param $name
     * @param string $paddingLeft
     */
    private static function setBlankRow(&$sliceList, $name, $paddingLeft = '5px')
    {

        $section = new Section();
        $section
            ->addElementColumn((new Element())
                ->setContent($name)
                ->stylePaddingLeft($paddingLeft)
                ->styleBorderRight()
                ->styleBorderBottom()
                , '20%'
            );

        for ($i = 1; $i < 17; $i++) {
            $section
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight()
                    ->styleBorderBottom()
                    , '5%'
                );
        }

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleBorderLeft()
            ->addSection($section);
    }

    /**
     * @param $sliceList
     * @param $identifier
     * @param $name
     * @param $part
     * @param string $paddingLeft
     */
    private static function setRow(&$sliceList, $identifier, $name, $part, $paddingLeft = '50px')
    {

        $section = new Section();
        $section
            ->addElementColumn((new Element())
                ->setContent($name)
                ->styleBackgroundColor('lightgrey')
                ->stylePaddingLeft($paddingLeft)
                ->styleBorderRight()
                ->styleBorderBottom()
                , '20%'
            );

        for ($level = 5; $level < 11; $level++) {

            $isGrey = false;
            if ($identifier == 'HS' || ($identifier == 'RS' && $part = 'Mixed')) {
                if ($level == '5' || $level == '6' || $level == '10') {
                    $isGrey = true;
                }
            } elseif ($identifier == 'RS') {
                if ($level == '5' || $level == '6') {
                    $isGrey = true;
                }
            }

            $section
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E01.' . $identifier . '.' . $part . '.L' . $level . '.m is not empty) %}
                            {{ Content.E01.' . $identifier . '.' . $part . '.L' . $level . '.m }}
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
                        {% if (Content.E01.' . $identifier . '.' . $part . '.L' . $level . '.w is not empty) %}
                            {{ Content.E01.' . $identifier . '.' . $part . '.L' . $level . '.w }}
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
                        {% if (Content.E01.' . $identifier . '.' . $part . '.Migrants.m is not empty) %}
                            {{ Content.E01.' . $identifier . '.' . $part . '.Migrants.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                ->styleAlignCenter()
                ->styleBorderRight()
                ->styleBackgroundColor('lightgrey')
                ->styleBorderBottom()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                        {% if (Content.E01.' . $identifier . '.' . $part . '.Migrants.w is not empty) %}
                            {{ Content.E01.' . $identifier . '.' . $part . '.Migrants.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                ->styleAlignCenter()
                ->styleBorderRight()
                ->styleBackgroundColor('lightgrey')
                ->styleBorderBottom()
                , '5%'
            );

        $section
            ->addElementColumn((new Element())
                ->setContent('
                        {% if (Content.E01.' . $identifier . '.' . $part . '.TotalCount.m is not empty) %}
                            {{ Content.E01.' . $identifier . '.' . $part . '.TotalCount.m }}
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
                        {% if (Content.E01.' . $identifier . '.' . $part . '.TotalCount.w is not empty) %}
                            {{ Content.E01.' . $identifier . '.' . $part . '.TotalCount.w }}
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

    /**
     * @param $sliceList
     * @param $name
     * @param string $paddingLeft
     */
    private static function setDivisionBlankRow(&$sliceList, $name, $paddingLeft = '5px')
    {

        $section = new Section();
        $section
            ->addElementColumn((new Element())
                ->setContent($name)
                ->stylePaddingLeft($paddingLeft)
                ->styleBorderRight()
                ->styleBorderBottom()
                , '20%'
            );

        for ($i = 1; $i < 9; $i++) {
            $section
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight()
                    ->styleBorderBottom()
                    , '10%'
                );
        }

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleBorderLeft()
            ->addSection($section);
    }

    private static function setDivisionRow(
        &$sliceList,
        $identifier,
        $name
    ) {
        $section = new Section();
        $section
            ->addElementColumn((new Element())
                ->setContent($name)
                ->styleBackgroundColor('lightgrey')
                ->stylePaddingLeft('25px')
                ->styleBorderRight()
                ->styleBorderBottom()
                , '20%'
            );

        for ($level = 5; $level < 11; $level++) {

            $isGrey = false;
            if ($identifier == 'HS' || $identifier == 'Mixed') {
                if ($level == '5' || $level == '6' || $level == '10') {
                    $isGrey = true;
                }
            } elseif ($identifier == 'RS') {
                if ($level == '5' || $level == '6') {
                    $isGrey = true;
                }
            }

            $section
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E01K.' . $identifier . '.L' . $level . ' is not empty) %}
                            {{ Content.E01K.' . $identifier . '.L' . $level . ' }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBackgroundColor($isGrey ? 'lightgrey' : 'white')
                    ->styleBorderRight()
                    ->styleBorderBottom()
                    , '10%'
                );
        }

        $section
            ->addElementColumn((new Element())
                ->setContent('
                        &nbsp;
                    ')
                ->styleAlignCenter()
                ->styleBorderRight()
                ->styleBackgroundColor('lightgrey')
                ->styleBorderBottom()
                , '10%'
            );

        $section
            ->addElementColumn((new Element())
                ->setContent('
                        {% if (Content.E01K.' . $identifier . '.TotalCount is not empty) %}
                            {{ Content.E01K.' . $identifier . '.TotalCount }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                ->styleAlignCenter()
                ->styleBorderRight()
                ->styleTextBold()
                ->styleBackgroundColor('lightgrey')
                ->styleBorderBottom()
                , '10%'
            );

        $sliceList[] = (new Slice())
            ->styleBorderLeft()
            ->addSection($section);
    }
}