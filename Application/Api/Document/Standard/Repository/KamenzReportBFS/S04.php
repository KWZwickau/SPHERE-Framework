<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class S04
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS
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
                $maxLevel = 4;
                $width[0] = '25%';
                $width[1] = '60%';
                $width[2] = '15%';
                break;
            case 'S04_1_U':
                $title = 'S04-1-U. Schüler im <u>Ausbildungsstatus Umschüler im Vollzeitunterricht</u> mit
                    fremdsprachlichem Unterricht (an dieser Schule neu begonnene<br />' . Common::getBlankSpace(16)
                    . 'bzw. fortgeführte Fremdsprachen) im Schuljahr {{ Content.SchoolYear.Current }} nach Fremdsprachen und Klassenstufen';
                $maxLevel = 4;
                $width[0] = '25%';
                $width[1] = '60%';
                $width[2] = '15%';
                break;
            case 'S04_2_A':
                $title = 'S04-2-A. Schüler im <u>Ausbildungsstatus Auszubildende/Schüler im Teilzeitunterricht</u> mit
                    fremdsprachlichem Unterricht (an dieser Schule neu begonnene<br />' . Common::getBlankSpace(16)
                    . 'bzw. fortgeführte Fremdsprachen) im Schuljahr {{ Content.SchoolYear.Current }} nach Fremdsprachen und Klassenstufen';
                $maxLevel = 5;
                $width[0] = '25%';
                $width[1] = '62.5%';
                $width[2] = '12.5%';
                break;
            case 'S04_2_U':
                $title = 'S04-2-U. Schüler im <u>Ausbildungsstatus Umschüler im Teilzeitunterricht</u> mit
                    fremdsprachlichem Unterricht (an dieser Schule neu begonnene<br />' . Common::getBlankSpace(16)
                    . 'bzw. fortgeführte Fremdsprachen) im Schuljahr {{ Content.SchoolYear.Current }} nach Fremdsprachen und Klassenstufen';
                $maxLevel = 5;
                $width[0] = '25%';
                $width[1] = '62.5%';
                $width[2] = '12.5%';
                break;
            default:
                $maxLevel = 4;
                $width[0] = '25%';
                $width[1] = '60%';
                $width[2] = '15%';
                $title = '';
        }

        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginBottom('10px')
            ->addElement((new Element())
                ->setContent($title)
            );

        $levelSection = new Section();
        for ($i = 1; $i <= $maxLevel; $i++) {
            $levelSection
                ->addElementColumn((new Element())
                    ->setContent($i)
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    , (floatval(100) / floatval($maxLevel)) . '%' );
        }

        $paddingTop = '9px';
        $paddingBottom = '9px';

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
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->stylePaddingTop($paddingTop)
                    ->stylePaddingBottom($paddingBottom)
                    , $width[0])
                ->addSliceColumn((new Slice())
                    ->addElement((new Element())
                        ->setContent('Schüler in Klassenstufe')
                        ->styleAlignCenter()
                        ->styleBorderRight()
                        ->styleBorderBottom()
                    )
                    ->addSection($levelSection)
                    , $width[1])
                ->addSliceColumn((new Slice())
                    ->styleTextBold()
                    ->addElement((new Element())
                        ->setContent('Insgesamt')
                        ->styleAlignCenter()
                        ->stylePaddingTop($paddingTop)
                        ->stylePaddingBottom($paddingBottom)
                    )
                    , $width[2])
            );

        for ($i = 0; $i < 6; $i++) {
            $section = new Section();
            $preText = 'Content.' . $name . '.R' . $i . '.';
            Common::setContentElement($section, $preText . 'Language', $width[0], true);
            Common::setContentElement($section, $preText . 'L1', $width[2], true);
            Common::setContentElement($section, $preText . 'L2', $width[2], true);
            Common::setContentElement($section, $preText . 'L3', $width[2], true);
            Common::setContentElement($section, $preText . 'L4', $width[2], true);
            if ($maxLevel == 5) {
                Common::setContentElement($section, $preText . 'L5', $width[2], true);
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