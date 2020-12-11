<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportFS;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class S04_1_1
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\KamenzReportFS
 */
class S04_1_1
{
    /**
     * @param string $name
     *
     * @return array
     */
    public static function getContent($name)
    {
        switch ($name) {
            case 'S04_1_1':
                $title = 'S04-1.1 Schüler im <u>Vollzeitunterricht</u> im Schuljahr {{ Content.SchoolYear.Current }} nach
                    Ausbildungsstatus, der Anzahl der derzeit erlernten Fremdsprachen und Klassenstufen';
                $maxLevel = 3;
                break;
            case 'S04_2_1':
                $title = 'S04-2.1 Schüler im <u>Teilzeitunterricht</u> im Schuljahr {{ Content.SchoolYear.Current }} nach
                    Ausbildungsstatus, der Anzahl der derzeit erlernten Fremdsprachen und Klassenstufen';
                $maxLevel = 4;
                break;
            default:
                $title = '';
                $maxLevel = 3;
        }

        $width[0] = '22%';
        $width[1] = '18%';
        $width[2] = '48%';
        $width[3] = '12%';
        $width['Level'] = (floatval(48) / floatval($maxLevel)) . '%';

        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginBottom('10px')
            ->addElement((new Element())
                ->setContent($title)
            );

        $levelSectionList = array();
        if (strpos($name, 'S04_1_1') === false) {
            $paddingTop = '18px';
            $paddingBottom = '18.3px';

            $levelSectionList[] = (new Section())
                ->addElementColumn((new Element())
                    ->setContent('1')
                    ->styleBorderRight()
                    ->styleBorderBottom()
                    , '50%')
                ->addElementColumn((new Element())
                    ->setContent('2')
                    ->styleBorderRight()
                    ->styleBorderBottom()
                    , '50%');
        } else {
            $paddingTop = '9px';
            $paddingBottom = '9px';
        }
        $section = new Section();
        for ($i = 1; $i <= $maxLevel; $i++) {
            if (strpos($name, 'S04_1_1') === false) {
                $section
                    ->addElementColumn((new Element())
                        ->setContent($i . '. AJ')
                        ->styleBorderRight()
                        , (floatval(100) / floatval($maxLevel)) . '%');
            } else {
                $section
                    ->addElementColumn((new Element())
                        ->setContent($i)
                        ->styleBorderRight()
                        , (floatval(100) / floatval($maxLevel)) . '%');
            }
        }
        $levelSectionList[] = $section;

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
                    ->stylePaddingTop($paddingTop)
                    ->stylePaddingBottom($paddingBottom)
                    , $width[0])
                ->addElementColumn((new Element())
                    ->setContent('Anzahl der Fremdsprachen')
                    ->styleBorderRight()
                    ->stylePaddingTop($paddingTop)
                    ->stylePaddingBottom($paddingBottom)
                    , $width[1])
                ->addSliceColumn((new Slice())
                    ->addElement((new Element())
                        ->setContent('Schüler in Klassenstufe')
                        ->styleBorderRight()
                        ->styleBorderBottom()
                    )
                    ->addSectionList($levelSectionList)
                    , $width[2])
                ->addSliceColumn((new Slice())
                    ->styleTextBold()
                    ->addElement((new Element())
                        ->setContent('Insgesamt')
                        ->stylePaddingTop($paddingTop)
                        ->stylePaddingBottom($paddingBottom)
                    )
                    , $width[3])
            );

        $sliceList[] = self::getSlice($name, 'Student', $maxLevel, $width);
        $sliceList[] = self::getSlice($name, 'ChangeStudent', $maxLevel, $width);

        return $sliceList;
    }

    /**
     * @param $name
     * @param $type
     * @param $maxLevel
     * @param array $width
     * @return Slice
     */
    private static function getSlice($name, $type, $maxLevel, $width)
    {
        $slice = new Slice();
        $section = new Section();
        $section
            ->addElementColumn((new Element())
                ->setContent($type == 'Student' ? 'Auszubildende/Schüler' : 'Umschüler')
                ->styleBackgroundColor('lightgrey')
                ->styleBorderBottom()
                ->styleBorderRight()
                ->stylePaddingTop('45px')
                ->stylePaddingBottom('45.5px')
                , $width[0]);

        $sectionLineList = array();
        for ($i = 0; $i < 6; $i++) {
            switch ($i) {
                case 0: $text = 'keine'; break;
                case 1: $text = 'eine'; break;
                case 2: $text = 'zwei'; break;
                case 3: $text = 'drei'; break;
                case 4: $text = 'vier und mehr'; break;
                case 5: $text = 'Insgesamt'; break;
                default: $text = '&nbsp;';
            }

            $sectionLine = new Section();

            $sectionLine
                ->addElementColumn((new Element())
                    ->setContent($text)
                    ->styleTextBold()
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderBottom()
                    ->styleBorderRight()
                    , $width[1]);

            for ($level = 1; $level <= $maxLevel; $level++) {
                $sectionLine
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.' . $name . '.' . $type . '.F' . $i . '.L' . $level . ' is not empty) %}
                                {{ Content.' . $name . '.' . $type . '.F' . $i . '.L' . $level . ' }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleTextBold($text == 'Insgesamt' ? 'bold' : 'normal')
                        ->styleBackgroundColor($text == 'Insgesamt' ? 'lightgrey' : 'white')
                        ->styleBorderRight()
                        ->styleBorderBottom()
                        , $width['Level']);
            }

            $sectionLine
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.' . $name . '.' . $type . '.F' . $i . '.TotalCount is not empty) %}
                                {{ Content.' . $name . '.' . $type . '.F' . $i . '.TotalCount }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleTextBold()
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderBottom()
                    , $width[3]);

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