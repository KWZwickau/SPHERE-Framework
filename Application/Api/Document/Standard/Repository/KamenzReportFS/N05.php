<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportFS;

use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS\Common;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class N05
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\KamenzReportFS
 */
class N05
{
    /**
     * @param string $name
     *
     * @return array
     */
    public static function getContent($name)
    {
        switch ($name) {
            case 'N05_1_A':
                $title = 'N05-1-A. Neuanfänger im <u>Ausbildungsstatus Auszubildende/Schüler im Vollzeitunterricht</u> 
                    im Schuljahr {{ Content.SchoolYear.Current }} nach Bildungsgängen, planmäßiger<br />'
                    . Common::getBlankSpace(16) . 'Ausbildungsdauer, Förderschwerpunkten und Klassenstufen';
                break;
            case 'N05_1_U':
                $title = 'N05-1-U. Neuanfänger im <u>Ausbildungsstatus Umschüler im Vollzeitunterricht</u> 
                    im Schuljahr {{ Content.SchoolYear.Current }} nach Bildungsgängen, planmäßiger<br />'
                    . Common::getBlankSpace(16) . 'Ausbildungsdauer, Förderschwerpunkten und Klassenstufen';
                break;
            case 'N05_1_1_A':
                $title = 'N05-1.1-A. Darunter Neuanfänger im <u>Ausbildungsstatus Auszubildende/Schüler im Vollzeitunterricht</u>,
                    deren Herkunftssprache nicht oder nicht<br />' . Common::getBlankSpace(19) . 'ausschließlich Deutsch ist,
                    im Schuljahr {{ Content.SchoolYear.Current }} nach Bildungsgängen, planmäßiger Ausbildungsdauer, 
                    Förderschwerpunkten und<br />' . Common::getBlankSpace(19) . 'Klassenstufen';
                break;
            case 'N05_1_1_U':
                $title = 'N05-1.1-U. Darunter Neuanfänger im <u>Ausbildungsstatus Umschüler im Vollzeitunterricht</u>,
                    deren Herkunftssprache nicht oder nicht ausschließlich Deutsch<br />' . Common::getBlankSpace(19) . 'ist,
                    im Schuljahr {{ Content.SchoolYear.Current }} nach Bildungsgängen, planmäßiger Ausbildungsdauer, 
                    Förderschwerpunkten und Klassenstufen';
                break;
            case 'N05_2_A':
                $title = 'N05-2-A. Neuanfänger im <u>Ausbildungsstatus Auszubildende/Schüler im Teillzeitunterricht</u> 
                    im Schuljahr {{ Content.SchoolYear.Current }} nach Bildungsgängen, planmäßiger<br />'
                    . Common::getBlankSpace(16) . 'Ausbildungsdauer, Förderschwerpunkten und Klassenstufen';
                break;
            case 'N05_2_U':
                $title = 'N05-2-U. Neuanfänger im <u>Ausbildungsstatus Umschüler im Teillzeitunterricht</u> 
                    im Schuljahr {{ Content.SchoolYear.Current }} nach Bildungsgängen, planmäßiger<br />'
                    . Common::getBlankSpace(16) . 'Ausbildungsdauer, Förderschwerpunkten und Klassenstufen';
                break;
            case 'N05_2_1_A':
                $title = 'N05-2.1-A. Darunter Neuanfänger im <u>Ausbildungsstatus Auszubildende/Schüler im Teillzeitunterricht</u>,
                    deren Herkunftssprache nicht oder nicht<br />' . Common::getBlankSpace(19) . 'ausschließlich Deutsch ist,
                    im Schuljahr {{ Content.SchoolYear.Current }} nach Bildungsgängen, planmäßiger Ausbildungsdauer, 
                    Förderschwerpunkten und<br />' . Common::getBlankSpace(19) . 'Klassenstufen';
                break;
            case 'N05_2_1_U':
                $title = 'N05-2.1-U. Darunter Neuanfänger im <u>Ausbildungsstatus Umschüler im Teillzeitunterricht</u>,
                    deren Herkunftssprache nicht oder nicht ausschließlich Deutsch<br />' . Common::getBlankSpace(19) . 'ist,
                    im Schuljahr {{ Content.SchoolYear.Current }} nach Bildungsgängen, planmäßiger Ausbildungsdauer, 
                    Förderschwerpunkten und Klassenstufen';
                break;
            default:
                $title = '';
        }

        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginBottom('10px')
            ->addElement((new Element())
                ->setContent($title)
            );

        if (strpos($name, 'N05_2') === false) {
            $width['Level'] = '16.5%';

            $sliceLevel = (new Slice())
                ->addElement((new Element())
                    ->setContent('Neuanfänger in Klassenstufe')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->styleBorderBottom()
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('1')
                        ->styleAlignCenter()
                        ->stylePaddingTop('33px')
                        ->stylePaddingBottom('34.5px')
                        ->styleBorderRight()
                        , '50%')
                    ->addElementColumn((new Element())
                        ->setContent('2')
                        ->styleAlignCenter()
                        ->stylePaddingTop('33px')
                        ->stylePaddingBottom('34.5px')
                        ->styleBorderRight()
                        , '50%')
                );
        } else {
            $width['Level'] = '11%';

            $sliceLevel = (new Slice())
                ->addElement((new Element())
                    ->setContent('Neuanfänger in Klassenstufe')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->styleBorderBottom()
                )
                ->addSection((new Section())
                    ->addSliceColumn((new Slice())
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('1')
                                ->styleAlignCenter()
                                ->styleBorderBottom()
                                ->styleBorderRight()
                            )
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('1. AJ')
                                ->styleAlignCenter()
                                ->stylePaddingTop('24px')
                                ->stylePaddingBottom('25.2px')
                                ->styleBorderRight()
                                , '50%')
                            ->addElementColumn((new Element())
                                ->setContent('2. AJ')
                                ->styleAlignCenter()
                                ->stylePaddingTop('24px')
                                ->stylePaddingBottom('25.2px')
                                ->styleBorderRight()
                                , '50%')
                        )
                        , '66.666%')
                    ->addSliceColumn((new Slice())
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('2')
                                ->styleAlignCenter()
                                ->styleBorderBottom()
                                ->styleBorderRight()
                            )
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('3. AJ')
                                ->styleAlignCenter()
                                ->stylePaddingTop('24px')
                                ->stylePaddingBottom('25.2px')
                                ->styleBorderRight()
                            )
                        )
                        , '33.333%')
                );
        }

        $width[0] = '26%';
        $width[1] = '6%';
        $width[2] = '14%';
        $width[3] = '10%';
        $width[4] = '33%';
        $width[5] = '11%';

        $paddingTop = '42px';
        $paddingBottom = '43.5px';

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleAlignCenter()
            ->styleBorderTop()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Bildungsgang')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->stylePaddingTop($paddingTop)
                    ->stylePaddingBottom($paddingBottom)
                    , $width[0])
                ->addElementColumn((new Element())
                    ->setContent('Plan-<br/>mäßige<br/>Ausbil-<br/>dungs-<br/>dauer in<br/>Monaten')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    , $width[1])
                ->addElementColumn((new Element())
                    ->setContent('Förderschwerpunkt')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->stylePaddingTop($paddingTop)
                    ->stylePaddingBottom($paddingBottom)
                    , $width[2])
                ->addElementColumn((new Element())
                    ->setContent('Geschlecht')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->stylePaddingTop($paddingTop)
                    ->stylePaddingBottom($paddingBottom)
                    , $width[3])
                ->addSliceColumn($sliceLevel
                    , $width[4])
                ->addSliceColumn((new Slice())
                    ->styleTextBold()
                    ->addElement((new Element())
                        ->setContent('Insgesamt')
                        ->styleAlignCenter()
                        ->stylePaddingTop($paddingTop)
                        ->stylePaddingBottom($paddingBottom)
                    )
                    , $width[5])
            );

        for ($i = 0; $i < 6; $i++) {
            $section = new Section();
            $preText = 'Content.' . $name . '.R' . $i . '.';
            Common::setContentElement($section, $preText . 'Course', $width[0], false);
            Common::setContentElement($section, $preText . 'Time', $width[1], true);
            Common::setContentElement($section, $preText . 'Support', $width[2], false);
            Common::setContentElement($section, $preText . 'Gender', $width[3], true);
            Common::setContentElement($section, $preText . 'L1', $width['Level'], true);
            Common::setContentElement($section, $preText . 'L2', $width['Level'], true);
            if (strpos($name, 'N05_2') !== false) {
                Common::setContentElement($section, $preText . 'L3', $width['Level'], true);
            }
            Common::setContentElement($section, $preText . 'TotalCount', $width[5], true, true);

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
                , '46%')
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
                , $width[3])
            ->addSliceColumn(Common::setTotalSlice($name, 'L1'), $width['Level'])
            ->addSliceColumn(Common::setTotalSlice($name, 'L2'), $width['Level']);

        if (strpos($name, 'N05_2') !== false) {
            $section->addSliceColumn(Common::setTotalSlice($name, 'L3'), $width['Level']);
        }

        $section->addSliceColumn(Common::setTotalSlice($name, 'TotalCount', true), $width[5]);

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