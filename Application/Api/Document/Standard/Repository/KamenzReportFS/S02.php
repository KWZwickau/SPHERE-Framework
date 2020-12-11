<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportFS;

use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS\Common;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class S02
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\KamenzReportFS
 */
class S02
{
    /**
     * @param string $name
     *
     * @return array
     */
    public static function getContent($name)
    {
        switch ($name) {
            case 'S02_1_A':
                $title = 'S02-1-A. Schüler im <u>Ausbildungsstatus Auszubildende/Schüler im Vollzeitunterricht</u> im
                        Schuljahr {{ Content.SchoolYear.Current }} nach Geburtsjahren und Klassenstufen';
                $columnName = 'Geburtsjahr';
                break;
            case 'S02_1_U':
                $title = 'S02-1-U. Schüler im <u>Ausbildungsstatus Umschüler im Vollzeitunterricht</u> im
                        Schuljahr {{ Content.SchoolYear.Current }} nach Geburtsjahren und Klassenstufen';
                $columnName = 'Geburtsjahr';
                break;
            case 'S02_1_1_A':
                $title = 'S02-1.1-A. Darunter Schüler im <u>Ausbildungsstatus Auszubildende/Schüler im Vollzeitunterricht</u>,
                        deren Herkunftssprache nicht oder nicht </br>' . Common::getBlankSpace(19) . 'ausschließlich
                        Deutsch ist, im Schuljahr {{ Content.SchoolYear.Current }} nach Geburtsjahren und Klassenstufen';
                $columnName = 'Geburtsjahr';
                break;
            case 'S02_1_1_U':
                $title = 'S02-1.1-U. Darunter Schüler im <u>Ausbildungsstatus Umschüler im Vollzeitunterricht</u>,
                        deren Herkunftssprache nicht oder nicht ausschließlich</br>' . Common::getBlankSpace(19)
                    . 'Deutsch ist, im Schuljahr {{ Content.SchoolYear.Current }} nach Geburtsjahren und Klassenstufen';
                $columnName = 'Geburtsjahr';
                break;
            case 'S02_2_A':
                $title = 'S02-2-A. Schüler im <u>Ausbildungsstatus Auszubildende/Schüler im Teilzeitunterrricht</u> im
                        Schuljahr {{ Content.SchoolYear.Current }} nach Geburtsjahren und Klassenstufen';
                $columnName = 'Geburtsjahr';
                break;
            case 'S02_2_U':
                $title = 'S02-2-U. Schüler im <u>Ausbildungsstatus Umschüler im Teilzeitunterrricht</u> im
                        Schuljahr {{ Content.SchoolYear.Current }} nach Geburtsjahren und Klassenstufen';
                $columnName = 'Geburtsjahr';
                break;
            case 'S02_2_1_A':
                $title = 'S02-2.1-A. Darunter Schüler im <u>Ausbildungsstatus Auszubildende/Schüler im Teilzeitunterrricht</u>,
                        deren Herkunftssprache nicht oder nicht </br>' . Common::getBlankSpace(19) . 'ausschließlich
                        Deutsch ist, im Schuljahr {{ Content.SchoolYear.Current }} nach Geburtsjahren und Klassenstufen';
                $columnName = 'Geburtsjahr';
                break;
            case 'S02_2_1_U':
                $title = 'S02-2.1-U. Darunter Schüler im <u>Ausbildungsstatus Umschüler im Teilzeitunterrricht</u>,
                        deren Herkunftssprache nicht oder nicht ausschließlich</br>' . Common::getBlankSpace(19)
                    . 'Deutsch ist, im Schuljahr {{ Content.SchoolYear.Current }} nach Geburtsjahren und Klassenstufen';
                $columnName = 'Geburtsjahr';
                break;
            case 'S03_1_1_A':
                $title = 'S03-1.1-A. Schüler im <u>Ausbildungsstatus Auszubildende/Schüler im Vollzeitunterricht</u>,
                    deren Herkunftssprache nicht oder nicht ausschließlich Deutsch</br>' . Common::getBlankSpace(18)
                    . ' ist, im Schuljahr {{ Content.SchoolYear.Current }} nach dem Land der Staatsangehörigkeit und Klassenstufen';
                $columnName = 'Land der Staatsangehörigkeit';
                break;
            case 'S03_1_1_U':
                $title = 'S03-1.1-U. Schüler im <u>Ausbildungsstatus Umschüler im Vollzeitunterricht</u>,
                    deren Herkunftssprache nicht oder nicht ausschließlich Deutsch</br>' . Common::getBlankSpace(18)
                    . ' ist, im Schuljahr {{ Content.SchoolYear.Current }} nach dem Land der Staatsangehörigkeit und Klassenstufen';
                $columnName = 'Land der Staatsangehörigkeit';
                break;
            case 'S03_2_1_A':
                $title = 'S03-2.1-A. Schüler im <u>Ausbildungsstatus Auszubildende/Schüler im Teilzeitunterrricht</u>,
                    deren Herkunftssprache nicht oder nicht ausschließlich Deutsch</br>' . Common::getBlankSpace(18)
                    . ' ist, im Schuljahr {{ Content.SchoolYear.Current }} nach dem Land der Staatsangehörigkeit und Klassenstufen';
                $columnName = 'Land der Staatsangehörigkeit';
                break;
            case 'S03_2_1_U':
                $title = 'S03-2.1-U. Schüler im <u>Ausbildungsstatus Umschüler im Teilzeitunterrricht</u>,
                    deren Herkunftssprache nicht oder nicht ausschließlich Deutsch</br>' . Common::getBlankSpace(18)
                    . ' ist, im Schuljahr {{ Content.SchoolYear.Current }} nach dem Land der Staatsangehörigkeit und Klassenstufen';
                $columnName = 'Land der Staatsangehörigkeit';
                break;
            default:
                $title = '';
                $columnName = '';
        }

        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginBottom('10px')
            ->addElement((new Element())
                ->setContent($title)
            );

        if (strpos($name, 'S03') === false) {
            // S02
            $width[0] = '24%';
            $width[1] = '16%';
            if (strpos($name, 'S02_2') === false) {
                // S02_1
                $width[2] = '48%';
                $width[3] = '12%';
                $maxLevel = 3;
                $width['Level'] = (floatval(48) / floatval($maxLevel)) . '%';
            } else {
                // S02_2
                $width[2] = '50%';
                $width[3] = '10%';
                $maxLevel = 4;
                $width['Level'] = (floatval(50) / floatval($maxLevel)) . '%';
            }
        } else {
            // S03
            $width[0] = '30%';
            $width[1] = '10%';
            if (strpos($name, 'S03_2') === false) {
                // S03_1
                $width[2] = '48%';
                $width[3] = '12%';
                $maxLevel = 3;
                $width['Level'] = (floatval(48) / floatval($maxLevel)) . '%';
            } else {
                // S03_2
                $width[2] = '50%';
                $width[3] = '10%';
                $maxLevel = 4;
                $width['Level'] = (floatval(50) / floatval($maxLevel)) . '%';
            }
        }

        $levelSectionList = array();
        if (strpos($name, 'S02_1') === false && strpos($name, 'S03_1') === false) {
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
            if (strpos($name, 'S02_1') === false && strpos($name, 'S03_1') === false) {
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
                    ->setContent($columnName)
                    ->styleBorderRight()
                    ->stylePaddingTop($paddingTop)
                    ->stylePaddingBottom($paddingBottom)
                    , $width[0])
                ->addElementColumn((new Element())
                    ->setContent('Geschlecht')
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

        for ($i = 0; $i < 6; $i++) {
            $section = new Section();
            $preText = 'Content.' . $name . '.R' . $i . '.';
            Common::setContentElement($section, $preText . 'Name', $width[0], true);
            Common::setContentElement($section, $preText . 'Gender', $width[1], true);
            Common::setContentElement($section, $preText . 'L1', $width['Level'], true);
            Common::setContentElement($section, $preText . 'L2', $width['Level'], true);
            Common::setContentElement($section, $preText . 'L3', $width['Level'], true);
            if ($maxLevel > 3) {
                Common::setContentElement($section, $preText . 'L4', $width['Level'], true);
            }
            Common::setContentElement($section, $preText . 'TotalCount', $width[3], true, true);

            $sliceList[] = (new Slice())
                ->styleBorderBottom()
                ->styleBorderLeft()
                ->styleBorderRight()
                ->addSection($section);
        }

        /**
         * TotalCount
         */
        $section = new Section();
        $section
            ->addElementColumn((new Element())
                ->setContent('Insgesamt')
                ->styleBackgroundColor('lightgrey')
                ->styleBorderRight()
                ->stylePaddingTop('36px')
                ->stylePaddingBottom('36.2px')
                , $width[0])
            ->addSliceColumn((new Slice())
                ->addElement((new Element())
                    ->setContent('männlich')
                    ->styleBorderRight()
                    ->styleBorderBottom()
                )
                ->addElement((new Element())
                    ->setContent('weiblich')
                    ->styleBorderRight()
                    ->styleBorderBottom()
                )
                ->addElement((new Element())
                    ->setContent('divers')
                    ->styleBorderRight()
                    ->styleBorderBottom()
                )
                ->addElement((new Element())
                    ->setContent('ohne Angabe¹')
                    ->styleBorderRight()
                    ->styleBorderBottom()
                )
                ->addElement((new Element())
                    ->setContent('insgesamt')
                    ->styleBorderRight()
                )
                , $width[1])
            ->addSliceColumn(Common::setTotalSlice($name, 'L1'), $width['Level'])
            ->addSliceColumn(Common::setTotalSlice($name, 'L2'), $width['Level'])
            ->addSliceColumn(Common::setTotalSlice($name, 'L3'), $width['Level']);
        if ($maxLevel > 3) {
            $section->addSliceColumn(Common::setTotalSlice($name, 'L4'), $width['Level']);
        }
        $section->addSliceColumn(Common::setTotalSlice($name, 'TotalCount', true), $width[3]);

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleTextBold()
            ->styleAlignCenter()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection($section);

        $array[] = 'Laut Eintrag im Geburtenregister';
        $sliceList[] = Common::setFootnotes($array);

        return $sliceList;
    }
}