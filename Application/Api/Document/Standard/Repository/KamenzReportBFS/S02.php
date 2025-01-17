<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class S02
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS
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
                        deren Herkunftssprache nicht oder nicht <br />' . Common::getBlankSpace(19) . 'ausschließlich
                        Deutsch ist, im Schuljahr {{ Content.SchoolYear.Current }} nach Geburtsjahren und Klassenstufen';
                $columnName = 'Geburtsjahr';
                break;
            case 'S02_1_1_U':
                $title = 'S02-1.1-U. Darunter Schüler im <u>Ausbildungsstatus Umschüler im Vollzeitunterricht</u>,
                        deren Herkunftssprache nicht oder nicht ausschließlich<br />' . Common::getBlankSpace(19)
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
                        deren Herkunftssprache nicht oder nicht <br />' . Common::getBlankSpace(19) . 'ausschließlich
                        Deutsch ist, im Schuljahr {{ Content.SchoolYear.Current }} nach Geburtsjahren und Klassenstufen';
                $columnName = 'Geburtsjahr';
                break;
            case 'S02_2_1_U':
                $title = 'S02-2.1-U. Darunter Schüler im <u>Ausbildungsstatus Umschüler im Teilzeitunterrricht</u>,
                        deren Herkunftssprache nicht oder nicht ausschließlich<br />' . Common::getBlankSpace(19)
                    . 'Deutsch ist, im Schuljahr {{ Content.SchoolYear.Current }} nach Geburtsjahren und Klassenstufen';
                $columnName = 'Geburtsjahr';
                break;
            case 'S03_1_1_A':
                $title = 'S03-1.1-A. Schüler im <u>Ausbildungsstatus Auszubildende/Schüler im Vollzeitunterricht</u>,
                    deren Herkunftssprache nicht oder nicht ausschließlich Deutsch<br />' . Common::getBlankSpace(18)
                    . ' ist, im Schuljahr {{ Content.SchoolYear.Current }} nach dem Land der Staatsangehörigkeit und Klassenstufen';
                $columnName = 'Land der Staatsangehörigkeit';
                break;
            case 'S03_1_1_U':
                $title = 'S03-1.1-U. Schüler im <u>Ausbildungsstatus Umschüler im Vollzeitunterricht</u>,
                    deren Herkunftssprache nicht oder nicht ausschließlich Deutsch<br />' . Common::getBlankSpace(18)
                    . ' ist, im Schuljahr {{ Content.SchoolYear.Current }} nach dem Land der Staatsangehörigkeit und Klassenstufen';
                $columnName = 'Land der Staatsangehörigkeit';
                break;
            case 'S03_2_1_A':
                $title = 'S03-2.1-A. Schüler im <u>Ausbildungsstatus Auszubildende/Schüler im Teilzeitunterrricht</u>,
                    deren Herkunftssprache nicht oder nicht ausschließlich Deutsch<br />' . Common::getBlankSpace(18)
                    . ' ist, im Schuljahr {{ Content.SchoolYear.Current }} nach dem Land der Staatsangehörigkeit und Klassenstufen';
                $columnName = 'Land der Staatsangehörigkeit';
                break;
            case 'S03_2_1_U':
                $title = 'S03-2.1-U. Schüler im <u>Ausbildungsstatus Umschüler im Teilzeitunterrricht</u>,
                    deren Herkunftssprache nicht oder nicht ausschließlich Deutsch<br />' . Common::getBlankSpace(18)
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
                $maxLevel = 4;
            } else {
                // S02_2
                $width[2] = '50%';
                $width[3] = '10%';
                $maxLevel = 5;
            }
        } else {
            // S03
            $width[0] = '30%';
            $width[1] = '10%';
            if (strpos($name, 'S03_2') === false) {
                // S03_1
                $width[2] = '48%';
                $width[3] = '12%';
                $maxLevel = 4;
            } else {
                // S03_2
                $width[2] = '50%';
                $width[3] = '10%';
                $maxLevel = 5;
            }
        }

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
                    ->setContent($columnName)
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->stylePaddingTop($paddingTop)
                    ->stylePaddingBottom($paddingBottom)
                    , $width[0])
                ->addElementColumn((new Element())
                    ->setContent('Geschlecht')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->stylePaddingTop($paddingTop)
                    ->stylePaddingBottom($paddingBottom)
                    , $width[1])
                ->addSliceColumn((new Slice())
                    ->addElement((new Element())
                        ->setContent('Schüler in Klassenstufe')
                        ->styleAlignCenter()
                        ->styleBorderRight()
                        ->styleBorderBottom()
                    )
                    ->addSection($levelSection)
                    , $width[2])
                ->addSliceColumn((new Slice())
                    ->styleTextBold()
                    ->addElement((new Element())
                        ->setContent('Insgesamt')
                        ->styleAlignCenter()
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
            Common::setContentElement($section, $preText . 'L1', $width[3], true);
            Common::setContentElement($section, $preText . 'L2', $width[3], true);
            Common::setContentElement($section, $preText . 'L3', $width[3], true);
            Common::setContentElement($section, $preText . 'L4', $width[3], true);
            if ($maxLevel == 5) {
                Common::setContentElement($section, $preText . 'L5', $width[3], true);
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
                ->styleAlignCenter()
                ->styleBackgroundColor('lightgrey')
                ->styleBorderRight()
                ->stylePaddingTop('36px')
                ->stylePaddingBottom('36.2px')
                , $width[0])
            ->addSliceColumn((new Slice())
                ->addElement((new Element())
                    ->setContent('männlich')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->styleBorderBottom()
                )
                ->addElement((new Element())
                    ->setContent('weiblich')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->styleBorderBottom()
                )
                ->addElement((new Element())
                    ->setContent('divers')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->styleBorderBottom()
                )
                ->addElement((new Element())
                    ->setContent('ohne Angabe¹')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->styleBorderBottom()
                )
                ->addElement((new Element())
                    ->setContent('insgesamt')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                )
                , $width[1])
            ->addSliceColumn(Common::setTotalSlice($name, 'L1'), $width[3])
            ->addSliceColumn(Common::setTotalSlice($name, 'L2'), $width[3])
            ->addSliceColumn(Common::setTotalSlice($name, 'L3'), $width[3])
            ->addSliceColumn(Common::setTotalSlice($name, 'L4'), $width[3]);
        if ($maxLevel == 5) {
            $section->addSliceColumn(Common::setTotalSlice($name, 'L5'), $width[3]);
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