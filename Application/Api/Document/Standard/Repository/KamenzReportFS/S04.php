<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportFS;

use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS\Common;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class S04
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\KamenzReportFS
 */
class S04
{
    /**
     * @param string $name
     *
     * @return array
     */
    public static function getContent($name)
    {
        switch ($name) {
            case 'S04_1_A':
                $title = 'S04-1-A. Schüler im <u>Ausbildungsstatus Auszubildende/Schüler im Vollzeitunterricht</u> mit
                    fremdsprachlichem Unterricht (an dieser Schule neu begonnene<br />' . Common::getBlankSpace(16)
                    . 'bzw. fortgeführte Fremdsprachen) im Schuljahr {{ Content.SchoolYear.Current }} nach Fremdsprachen und Klassenstufen';
                $maxLevel = 3;
                break;
            case 'S04_1_U':
                $title = 'S04-1-U. Schüler im <u>Ausbildungsstatus Umschüler im Vollzeitunterricht</u> mit
                    fremdsprachlichem Unterricht (an dieser Schule neu begonnene<br />' . Common::getBlankSpace(16)
                    . 'bzw. fortgeführte Fremdsprachen) im Schuljahr {{ Content.SchoolYear.Current }} nach Fremdsprachen und Klassenstufen';
                $maxLevel = 3;
                break;
            case 'S04_2_A':
                $title = 'S04-2-A. Schüler im <u>Ausbildungsstatus Auszubildende/Schüler im Teilzeitunterricht</u> mit
                    fremdsprachlichem Unterricht (an dieser Schule neu begonnene<br />' . Common::getBlankSpace(16)
                    . 'bzw. fortgeführte Fremdsprachen) im Schuljahr {{ Content.SchoolYear.Current }} nach Fremdsprachen und Klassenstufen';
                $maxLevel = 4;
                break;
            case 'S04_2_U':
                $title = 'S04-2-U. Schüler im <u>Ausbildungsstatus Umschüler im Teilzeitunterricht</u> mit
                    fremdsprachlichem Unterricht (an dieser Schule neu begonnene<br />' . Common::getBlankSpace(16)
                    . 'bzw. fortgeführte Fremdsprachen) im Schuljahr {{ Content.SchoolYear.Current }} nach Fremdsprachen und Klassenstufen';
                $maxLevel = 4;
                break;
            default:
                $maxLevel = 3;
                $title = '';
        }

        $width[0] = '35%';
        $width[1] = '50%';
        $width[2] = '15%';
        $width['Level'] = (floatval(50) / floatval($maxLevel)) . '%';

        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginBottom('10px')
            ->addElement((new Element())
                ->setContent($title)
            );

        $levelSectionList = array();
        if (strpos($name, 'S04_1') === false) {
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
            if (strpos($name, 'S04_1') === false) {
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
                    ->setContent('Fremdsprache¹')
                    ->styleBorderRight()
                    ->stylePaddingTop($paddingTop)
                    ->stylePaddingBottom($paddingBottom)
                    , $width[0])
                ->addSliceColumn((new Slice())
                    ->addElement((new Element())
                        ->setContent('Schüler in Klassenstufe')
                        ->styleBorderRight()
                        ->styleBorderBottom()
                    )
                    ->addSectionList($levelSectionList)
                    , $width[1])
                ->addSliceColumn((new Slice())
                    ->styleTextBold()
                    ->addElement((new Element())
                        ->setContent('Insgesamt')
                        ->stylePaddingTop($paddingTop)
                        ->stylePaddingBottom($paddingBottom)
                    )
                    , $width[2])
            );

        for ($i = 0; $i < 6; $i++) {
            $section = new Section();
            $preText = 'Content.' . $name . '.R' . $i . '.';
            Common::setContentElement($section, $preText . 'Language', $width[0], true);
            Common::setContentElement($section, $preText . 'L1', $width['Level'], true);
            Common::setContentElement($section, $preText . 'L2', $width['Level'], true);
            Common::setContentElement($section, $preText . 'L3', $width['Level'], true);
            if ($maxLevel > 3) {
                Common::setContentElement($section, $preText . 'L4', $width['Level'], true);
            }
            Common::setContentElement($section, $preText . 'TotalCount', $width[2], true, true);

            $sliceList[] = (new Slice())
                ->styleBorderBottom()
                ->styleBorderLeft()
                ->styleBorderRight()
                ->addSection($section);
        }

        $array[] = 'Jeder Schüler wird entsprechend der Zahl der belegten Fremdsprachen gezählt, also Mehrfachzählungen möglich.';
        $sliceList[] = Common::setFootnotes($array);

        return $sliceList;
    }
}