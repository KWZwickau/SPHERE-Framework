<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportFS;

use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS\Common;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class N03
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\KamenzReportFS
 */
class N03
{
    /**
     * @param string $name
     *
     * @return array
     */
    public static function getContent($name)
    {
        switch ($name) {
            case 'N03_1_A':
                $title = 'N03-1-A. Neuanfänger im <u>Ausbildungsstatus Auszubildende/Schüler im Vollzeitunterricht</u> im
                        Schuljahr {{ Content.SchoolYear.Current }} nach Geburtsjahren und Klassenstufen';
                $columnName = 'Geburtsjahr';
                break;
            case 'N03_1_U':
                $title = 'N03-1-U. Neuanfänger im <u>Ausbildungsstatus Umschüler im Vollzeitunterricht</u> im
                        Schuljahr {{ Content.SchoolYear.Current }} nach Geburtsjahren und Klassenstufen';
                $columnName = 'Geburtsjahr';
                break;
            case 'N03_1_1_A':
                $title = 'N03-1.1-A. Darunter Neuanfänger im <u>Ausbildungsstatus Auszubildende/Schüler im Vollzeitunterricht</u>,
                        deren Herkunftssprache nicht oder nicht </br>' . Common::getBlankSpace(19) . 'ausschließlich
                        Deutsch ist, im Schuljahr {{ Content.SchoolYear.Current }} nach Geburtsjahren und Klassenstufen';
                $columnName = 'Geburtsjahr';
                break;
            case 'N03_1_1_U':
                $title = 'N03-1.1-U. Darunter Neuanfänger im <u>Ausbildungsstatus Umschüler im Vollzeitunterricht</u>,
                        deren Herkunftssprache nicht oder nicht ausschließlich</br>' . Common::getBlankSpace(19)
                    . 'Deutsch ist, im Schuljahr {{ Content.SchoolYear.Current }} nach Geburtsjahren und Klassenstufen';
                $columnName = 'Geburtsjahr';
                break;
            case 'N03_2_A':
                $title = 'N03-2-A. Neuanfänger im <u>Ausbildungsstatus Auszubildende/Schüler im Teilzeitunterrricht</u> im
                        Schuljahr {{ Content.SchoolYear.Current }} nach Geburtsjahren und Klassenstufen';
                $columnName = 'Geburtsjahr';
                break;
            case 'N03_2_U':
                $title = 'N03-2-U. Neuanfänger im <u>Ausbildungsstatus Umschüler im Teilzeitunterrricht</u> im
                        Schuljahr {{ Content.SchoolYear.Current }} nach Geburtsjahren und Klassenstufen';
                $columnName = 'Geburtsjahr';
                break;
            case 'N03_2_1_A':
                $title = 'N03-2.1-A. Darunter Neuanfänger im <u>Ausbildungsstatus Auszubildende/Schüler im Teilzeitunterrricht</u>,
                        deren Herkunftssprache nicht oder nicht </br>' . Common::getBlankSpace(19) . 'ausschließlich
                        Deutsch ist, im Schuljahr {{ Content.SchoolYear.Current }} nach Geburtsjahren und Klassenstufen';
                $columnName = 'Geburtsjahr';
                break;
            case 'N03_2_1_U':
                $title = 'N03-2.1-U. Darunter Neuanfänger im <u>Ausbildungsstatus Umschüler im Teilzeitunterrricht</u>,
                        deren Herkunftssprache nicht oder nicht ausschließlich</br>' . Common::getBlankSpace(19)
                    . 'Deutsch ist, im Schuljahr {{ Content.SchoolYear.Current }} nach Geburtsjahren und Klassenstufen';
                $columnName = 'Geburtsjahr';
                break;
            case 'N04_1_1_A':
                $title = 'N04-1.1-A. Darunter Neuanfänger im <u>Ausbildungsstatus Auszubildende/Schüler im Vollzeitunterricht</u>,
                        deren Herkunftssprache nicht oder nicht </br>' . Common::getBlankSpace(19) . 'ausschließlich
                        Deutsch ist, im Schuljahr {{ Content.SchoolYear.Current }} nach dem Land der Staatsangehörigkeit und Klassenstufen';
                $columnName = 'Land der Staatsangehörigkeit';
                break;
            case 'N04_1_1_U':
                $title = 'N04-1.1-U. Darunter Neuanfänger im <u>Ausbildungsstatus Umschüler im Vollzeitunterricht</u>,
                        deren Herkunftssprache nicht oder nicht ausschließlich</br>' . Common::getBlankSpace(19)
                    . 'Deutsch ist, im Schuljahr {{ Content.SchoolYear.Current }} nach dem Land der Staatsangehörigkeit und Klassenstufen';
                $columnName = 'Land der Staatsangehörigkeit';
                break;
            case 'N04_2_1_A':
                $title = 'N04-2.1-A. Darunter Neuanfänger im <u>Ausbildungsstatus Auszubildende/Schüler im Teilzeitunterrricht</u>,
                        deren Herkunftssprache nicht oder nicht </br>' . Common::getBlankSpace(19) . 'ausschließlich
                        Deutsch ist, im Schuljahr {{ Content.SchoolYear.Current }} nach dem Land der Staatsangehörigkeit und Klassenstufen';
                $columnName = 'Land der Staatsangehörigkeit';
                break;
            case 'N04_2_1_U':
                $title = 'N04-2.1-U. Darunter Neuanfänger im <u>Ausbildungsstatus Umschüler im Teilzeitunterrricht</u>,
                        deren Herkunftssprache nicht oder nicht ausschließlich</br>' . Common::getBlankSpace(19)
                    . 'Deutsch ist, im Schuljahr {{ Content.SchoolYear.Current }} nach dem Land der Staatsangehörigkeit und Klassenstufen';
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

        if (strpos($name, 'N04_2') === false && strpos($name, 'N03_2') === false) {
            $paddingTop = '9px';
            $paddingBottom = '9px';

            $sliceLevel = (new Slice())
                ->addElement((new Element())
                    ->setContent('Neuanfänger in Klassenstufe')
                    ->styleBorderRight()
                    ->styleBorderBottom()
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('1')
                        ->styleBorderRight()
                        , '50%')
                    ->addElementColumn((new Element())
                        ->setContent('2')
                        ->styleBorderRight()
                        , '50%')
                );
        } else {
            $paddingTop = '18px';
            $paddingBottom = '18px';

            $sliceLevel = (new Slice())
                ->addElement((new Element())
                    ->setContent('Neuanfänger in Klassenstufe')
                    ->styleBorderRight()
                    ->styleBorderBottom()
                )
                ->addSection((new Section())
                    ->addSliceColumn((new Slice())
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('1')
                                ->styleBorderBottom()
                                ->styleBorderRight()
                            )
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('1. AJ')
                                ->styleBorderRight()
                                , '50%')
                            ->addElementColumn((new Element())
                                ->setContent('2. AJ')
                                ->styleBorderRight()
                                , '50%')
                        )
                        , '66.666%')
                    ->addSliceColumn((new Slice())
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('2')
                                ->styleBorderBottom()
                                ->styleBorderRight()
                            )
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('3. AJ')
                                ->styleBorderRight()
                            )
                        )
                        , '33.333%')
                );
        }

        if (strpos($name, 'N04') === false) {
            // N03
            $width[0] = '20%';
            $width[1] = '16%';
            $width[2] = '48%';
            $width[3] = '16%';

            if (strpos($name, 'N03_2') === false) {
                $width['Level'] = '24%';
            } else {
                $width['Level'] = '16%';
            }
        } else {
            // N04
            $width[0] = '30%';
            $width[1] = '14%';
            $width[2] = '42%';
            $width[3] = '14%';

            if (strpos($name, 'N04_2') === false) {
                $width['Level'] = '21%';
            } else {
                $width['Level'] = '14%';
            }
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
                ->addSliceColumn($sliceLevel
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
            if (strpos($name, 'N03_2') !== false || strpos($name, 'N04_2') !== false) {
                Common::setContentElement($section, $preText . 'L3', $width['Level'], true);
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
            ->addSliceColumn(Common::setTotalSlice($name, 'L2'), $width['Level']);

        if (strpos($name, 'N03_2') !== false || strpos($name, 'N04_2') !== false) {
            $section->addSliceColumn(Common::setTotalSlice($name, 'L3'), $width['Level']);
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