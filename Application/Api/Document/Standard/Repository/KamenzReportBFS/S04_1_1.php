<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class S04_1_1
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS
 */
class S04_1_1
{
    /**
     * @param string $name
     *
     * @return array
     */
    public static function getContent($name = 'S04_1_1')
    {
        switch ($name) {
            case 'S04_1_1':
                $title = 'S04-1.1 Schüler im <u>Vollzeitunterricht</u> im Schuljahr {{ Content.SchoolYear.Current }} nach 
                    der Anzahl der derzeit erlernten Fremdsprachen, Ausbildungsstatus und Klassenstufen';
                $maxLevel = 4;
                $width[0] = '22%';
                $width[1] = '18%';
                $width[2] = '48%';
                $width[3] = '12%';
                $width['gender'] = '6%';
                break;
            case 'S04_2_1':
                $title = 'S04-2.1 Schüler im <u>Teilzeitunterricht</u> im Schuljahr {{ Content.SchoolYear.Current }} nach 
                    der Anzahl der derzeit erlernten Fremdsprachen, Ausbildungsstatus und Klassenstufen';
                $maxLevel = 5;
                $width[0] = '22%';
                $width[1] = '18%';
                $width[2] = '50%';
                $width[3] = '10%';
                $width['gender'] = '5%';
                break;
            default:
                $title = '';
                $maxLevel = 4;
                $width[0] = '22%';
                $width[1] = '18%';
                $width[2] = '48%';
                $width[3] = '12%';
                $width['gender'] = '6%';
        }

        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginBottom('10px')
            ->addElement((new Element())
                ->setContent($title)
            );

        $padding = '3.8px';

        $sectionLevel = new Section();
        for ($i = 1; $i <= $maxLevel; $i++)
        {
            $sectionLevel
                ->addElementColumn((new Element())
                    ->setContent($i)
                    ->styleBorderRight()
                    ->styleBorderBottom()
                    ->stylePaddingTop($padding)
                    ->stylePaddingBottom($padding)
                    , (floatval(100) / floatval($maxLevel)) . '%' );
        }

        $tempWidth = (floatval(100) / floatval(2 * $maxLevel)) . '%';
        $sectionGender = new Section();
        for ($i = 1; $i <= $maxLevel; $i++) {
            $sectionGender
                ->addElementColumn((new Element())
                    ->setContent('m')
                    ->styleBorderRight()
                    , $tempWidth)
                ->addElementColumn((new Element())
                    ->setContent('w')
                    ->styleBorderRight()
                    , $tempWidth);
        }

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleAlignCenter()
            ->styleBorderTop()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Ausbildungsstatus')
                    ->styleBorderRight()
                    ->stylePaddingTop('25px')
                    ->stylePaddingBottom('26.5px')
                    , $width[0])
                ->addElementColumn((new Element())
                    ->setContent('Anzahl der Fremdsprachen')
                    ->styleBorderRight()
                    ->stylePaddingTop('25px')
                    ->stylePaddingBottom('26.5px')
                    , $width[1])
                ->addSliceColumn((new Slice())
                    ->addElement((new Element())
                        ->setContent('Schüler in Klassenstufe')
                        ->styleBorderRight()
                        ->styleBorderBottom()
                        ->stylePaddingTop($padding)
                        ->stylePaddingBottom($padding)
                    )
                    ->addSection($sectionLevel)
                    ->addSection($sectionGender)
                    , $width[2])
                ->addSliceColumn((new Slice())
                    ->styleTextBold()
                    ->addElement((new Element())
                        ->setContent('Insgesamt')
                        ->styleBorderBottom()
                        ->stylePaddingTop('16px')
                        ->stylePaddingBottom('17.3px')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight()
                            , '50%')
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            , '50%')
                    )
                    , $width[3])
            );

        $sliceList[] = self::getSlice($name, 'Student', $maxLevel, $width[0], $width[1], $width['gender']);
        $sliceList[] = self::getSlice($name, 'ChangeStudent', $maxLevel, $width[0], $width[1], $width['gender']);

        return $sliceList;
    }

    /**
     * @param $name
     * @param $type
     * @param $maxLevel
     * @param $width0
     * @param $width1
     * @param $withGender
     *
     * @return Slice
     */
    private static function getSlice($name, $type, $maxLevel, $width0, $width1, $withGender)
    {
        $slice = new Slice();
        $section = new Section();
        $section
            ->addElementColumn((new Element())
                ->setContent($type == 'Student' ? 'Auszubildende/Schüler' : 'Umschüler')
                ->styleBackgroundColor('lightgrey')
                ->styleTextBold()
                ->styleBorderBottom()
                ->styleBorderRight()
                ->stylePaddingTop('62px')
                ->stylePaddingBottom('64.8px')
        , $width0);

        $sectionLineList = array();
        for ($i = 0; $i < 8; $i++) {
            switch ($i) {
                case 0: $text = 'keine'; break;
                case 1: $text = 'eine'; break;
                case 2: $text = 'zwei'; break;
                case 3: $text = 'drei'; break;
                case 4: $text = 'vier'; break;
                case 5: $text = 'fünf'; break;
                case 6: $text = 'sechs und mehr'; break;
                case 7: $text = 'Insgesamt'; break;
                default: $text = '&nbsp;';
            }

            $sectionLine = new Section();

            $sectionLine
                ->addElementColumn((new Element())
                    ->setContent($text)
                    ->styleTextBold($text == 'Insgesamt' ? 'bold' : 'normal')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderBottom()
                    ->styleBorderRight()
                , $width1);

            for ($level = 1; $level <= $maxLevel; $level++) {
                $sectionLine
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.' . $name . '.' . $type . '.F' . $i . '.L' . $level . '.m is not empty) %}
                                {{ Content.' . $name . '.' . $type . '.F' . $i . '.L' . $level . '.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleTextBold($text == 'Insgesamt' ? 'bold' : 'normal')
                        ->styleBackgroundColor($text == 'Insgesamt' ? 'lightgrey' : 'white')
                        ->styleBorderRight()
                        ->styleBorderBottom()
                    , $withGender)
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.' . $name . '.' . $type . '.F' . $i . '.L' . $level . '.w is not empty) %}
                                {{ Content.' . $name . '.' . $type . '.F' . $i . '.L' . $level . '.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleTextBold($text == 'Insgesamt' ? 'bold' : 'normal')
                        ->styleBackgroundColor($text == 'Insgesamt' ? 'lightgrey' : 'white')
                        ->styleBorderRight()
                        ->styleBorderBottom()
                    , $withGender);
            }

            $sectionLine
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.' . $name . '.' . $type . '.F' . $i . '.TotalCount.m is not empty) %}
                                {{ Content.' . $name . '.' . $type . '.F' . $i . '.TotalCount.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleTextBold()
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight()
                    ->styleBorderBottom()
                , $withGender)
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.' . $name . '.' . $type . '.F' . $i . '.TotalCount.w is not empty) %}
                                {{ Content.' . $name . '.' . $type . '.F' . $i . '.TotalCount.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleTextBold()
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderBottom()
                , $withGender);

            $sectionLineList[] = $sectionLine;
        }

        $section
            ->addSliceColumn((new Slice())
                ->addSectionList($sectionLineList)
            );

        return $slice
            ->addSection($section)
            ->styleAlignCenter()
            ->styleBorderLeft()
            ->styleBorderRight();
    }
}