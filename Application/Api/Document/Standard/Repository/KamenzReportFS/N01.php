<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportFS;

use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS\Common;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class N01
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\KamenzReportFS
 */
class N01
{
    /**
     * @param string $name
     *
     * @return array
     */
    public static function getContent($name)
    {
        $blankSpace = 16;
        switch ($name) {
            case 'N01_1_A':
                $title = 'N01-1-A. Neuanfänger im <u>Ausbildungsstatus Auszubildende/Schüler im Vollzeitunterricht</u> 
                    im Schuljahr {{ Content.SchoolYear.Current }} nach allgemeinbildenden Abschlüssen,<br />'
                    . Common::getBlankSpace(16) . 'Schularten, Förderschwerpunkten und Klassenstufen';
                $columnName = 'Allgemeinbildender Abschluss¹';
                break;
            case 'N01_1_U':
                $title = 'N01-1-U. Neuanfänger im <u>Ausbildungsstatus Umschüler im Vollzeitunterricht</u> 
                    im Schuljahr {{ Content.SchoolYear.Current }} nach allgemeinbildenden Abschlüssen,<br />'
                    . Common::getBlankSpace(16) . 'Schularten, Förderschwerpunkten und Klassenstufen';
                $columnName = 'Allgemeinbildender Abschluss¹';
                break;
            case 'N01_1_1_A':
                $title = 'N01-1.1-A. Darunter Neuanfänger im <u>Ausbildungsstatus Auszubildende/Schüler im Vollzeitunterricht</u>,
                    deren Herkunftssprache nicht oder nicht<br />' . Common::getBlankSpace(19) . 'ausschließlich Deutsch ist,
                    im Schuljahr {{ Content.SchoolYear.Current }} nach allgemeinbildenden Abschlüssen, Schularten, 
                    Förderschwerpunkten und Klassenstufen';
                $columnName = 'Allgemeinbildender Abschluss¹';
                $blankSpace = 19;
                break;
            case 'N01_1_1_U':
                $title = 'N01-1.1-U. Darunter Neuanfänger im <u>Ausbildungsstatus Umschüler im Vollzeitunterricht</u>,
                    deren Herkunftssprache nicht oder nicht ausschließlich Deutsch<br />' . Common::getBlankSpace(19) . 'ist,
                    im Schuljahr {{ Content.SchoolYear.Current }} nach allgemeinbildenden Abschlüssen, Schularten, 
                    Förderschwerpunkten und Klassenstufen';
                $columnName = 'Allgemeinbildender Abschluss¹';
                $blankSpace = 19;
                break;
            case 'N01_2_A':
                $title = 'N01-2-A. Neuanfänger im <u>Ausbildungsstatus Auszubildende/Schüler im Teillzeitunterricht</u> 
                    im Schuljahr {{ Content.SchoolYear.Current }} nach allgemeinbildenden Abschlüssen,<br />'
                    . Common::getBlankSpace(16) . 'Schularten, Förderschwerpunkten und Klassenstufen';
                $columnName = 'Allgemeinbildender Abschluss¹';
                break;
            case 'N01_2_U':
                $title = 'N01-2-U. Neuanfänger im <u>Ausbildungsstatus Umschüler im Teillzeitunterricht</u> 
                    im Schuljahr {{ Content.SchoolYear.Current }} nach allgemeinbildenden Abschlüssen,<br />'
                    . Common::getBlankSpace(16) . 'Schularten, Förderschwerpunkten und Klassenstufen';
                $columnName = 'Allgemeinbildender Abschluss¹';
                break;
            case 'N01_2_1_A':
                $title = 'N01-2.1-A. Darunter Neuanfänger im <u>Ausbildungsstatus Auszubildende/Schüler im Teillzeitunterricht</u>,
                    deren Herkunftssprache nicht oder nicht<br />' . Common::getBlankSpace(19) . 'ausschließlich Deutsch ist,
                    im Schuljahr {{ Content.SchoolYear.Current }} nach allgemeinbildenden Abschlüssen, Schularten, 
                    Förderschwerpunkten und Klassenstufen';
                $columnName = 'Allgemeinbildender Abschluss¹';
                $blankSpace = 19;
                break;
            case 'N01_2_1_U':
                $title = 'N01-2.1-U. Darunter Neuanfänger im <u>Ausbildungsstatus Umschüler im Teillzeitunterricht</u>,
                    deren Herkunftssprache nicht oder nicht ausschließlich Deutsch<br />' . Common::getBlankSpace(19) . 'ist,
                    im Schuljahr {{ Content.SchoolYear.Current }} nach allgemeinbildenden Abschlüssen, Schularten, 
                    Förderschwerpunkten und Klassenstufen';
                $columnName = 'Allgemeinbildender Abschluss¹';
                $blankSpace = 19;
                break;
            case 'N02_1_A':
                $title = 'N02-1-A. Neuanfänger im <u>Ausbildungsstatus Auszubildende/Schüler im Vollzeitunterricht</u> 
                    im Schuljahr {{ Content.SchoolYear.Current }} nach berufsbildenden Abschlüssen,<br />'
                    . Common::getBlankSpace(16) . 'Schularten, Förderschwerpunkten und Klassenstufen';
                $columnName = 'Berufsbildender Abschluss¹';
                break;
            case 'N02_1_U':
                $title = 'N02-1-U. Neuanfänger im <u>Ausbildungsstatus Umschüler im Vollzeitunterricht</u> 
                    im Schuljahr {{ Content.SchoolYear.Current }} nach berufsbildenden Abschlüssen,<br />'
                    . Common::getBlankSpace(16) . 'Schularten, Förderschwerpunkten und Klassenstufen';
                $columnName = 'Berufsbildender Abschluss¹';
                break;
            case 'N02_1_1_A':
                $title = 'N02-1.1-A. Darunter Neuanfänger im <u>Ausbildungsstatus Auszubildende/Schüler im Vollzeitunterricht</u>,
                    deren Herkunftssprache nicht oder nicht<br />' . Common::getBlankSpace(19) . 'ausschließlich Deutsch ist,
                    im Schuljahr {{ Content.SchoolYear.Current }} nach berufsbildenden Abschlüssen, Schularten, 
                    Förderschwerpunkten und Klassenstufen';
                $columnName = 'Berufsbildender Abschluss¹';
                $blankSpace = 19;
                break;
            case 'N02_1_1_U':
                $title = 'N02-1.1-U. Darunter Neuanfänger im <u>Ausbildungsstatus Umschüler im Vollzeitunterricht</u>,
                    deren Herkunftssprache nicht oder nicht ausschließlich Deutsch<br />' . Common::getBlankSpace(19) . 'ist,
                    im Schuljahr {{ Content.SchoolYear.Current }} nach berufsbildenden Abschlüssen, Schularten, 
                    Förderschwerpunkten und Klassenstufen';
                $columnName = 'Berufsbildender Abschluss¹';
                $blankSpace = 19;
                break;
            case 'N02_2_A':
                $title = 'N02-2-A. Neuanfänger im <u>Ausbildungsstatus Auszubildende/Schüler im Teillzeitunterricht</u> 
                    im Schuljahr {{ Content.SchoolYear.Current }} nach berufsbildenden Abschlüssen,<br />'
                    . Common::getBlankSpace(16) . 'Schularten, Förderschwerpunkten und Klassenstufen';
                $columnName = 'Berufsbildender Abschluss¹';
                break;
            case 'N02_2_U':
                $title = 'N02-2-U. Neuanfänger im <u>Ausbildungsstatus Umschüler im Teillzeitunterricht</u> 
                    im Schuljahr {{ Content.SchoolYear.Current }} nach berufsbildenden Abschlüssen,<br />'
                    . Common::getBlankSpace(16) . 'Schularten, Förderschwerpunkten und Klassenstufen';
                $columnName = 'Berufsbildender Abschluss¹';
                break;
            case 'N02_2_1_A':
                $title = 'N02-2.1-A. Darunter Neuanfänger im <u>Ausbildungsstatus Auszubildende/Schüler im Teillzeitunterricht</u>,
                    deren Herkunftssprache nicht oder nicht<br />' . Common::getBlankSpace(19) . 'ausschließlich Deutsch ist,
                    im Schuljahr {{ Content.SchoolYear.Current }} nach berufsbildenden Abschlüssen, Schularten, 
                    Förderschwerpunkten und Klassenstufen';
                $columnName = 'Berufsbildender Abschluss¹';
                $blankSpace = 19;
                break;
            case 'N02_2_1_U':
                $title = 'N02-2.1-U. Darunter Neuanfänger im <u>Ausbildungsstatus Umschüler im Teillzeitunterricht</u>,
                    deren Herkunftssprache nicht oder nicht ausschließlich Deutsch<br />' . Common::getBlankSpace(19) . 'ist,
                    im Schuljahr {{ Content.SchoolYear.Current }} nach berufsbildenden Abschlüssen, Schularten, 
                    Förderschwerpunkten und Klassenstufen';
                $columnName = 'Berufsbildender Abschluss¹';
                $blankSpace = 19;
                break;
            default:
                $title = '';
                $columnName = '';
        }

        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginBottom(strpos($name, 'N02') === false ? '5px' : '10px')
            ->addElement((new Element())
                ->setContent($title)
            );
        if (strpos($name, 'N02') === false) {
            $sliceList[] = (new Slice())
                ->styleMarginBottom('10px')
                ->addElement((new Element())
                    ->setContent(Common::getBlankSpace($blankSpace) . 'Schüler, die <u>erstmals</u> im derzeit belegten
                    Bildungsgang beschult werden.'
                    )
                );
        }

        if (strpos($name, 'N01_2') === false && strpos($name, 'N02_2') === false) {
            $width['Level'] = '12%';
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
            $width['Level'] = '8%';
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

        $width[0] = '20%';
        $width[1] = '24%';
        $width[2] = '14%';
        $width[3] = '10%';
        $width[4] = '24%';
        $width[5] = '8%';

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
                    ->setContent('Schulart, an der der Abschluss erfolgte')
                    ->styleBorderRight()
                    ->stylePaddingTop($paddingTop)
                    ->stylePaddingBottom($paddingBottom)
                    , $width[1])
                ->addElementColumn((new Element())
                    ->setContent('Förderschwerpunkt')
                    ->styleBorderRight()
                    ->stylePaddingTop($paddingTop)
                    ->stylePaddingBottom($paddingBottom)
                    , $width[2])
                ->addElementColumn((new Element())
                    ->setContent('Geschlecht')
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
                        ->stylePaddingTop($paddingTop)
                        ->stylePaddingBottom($paddingBottom)
                    )
                    , $width[5])
            );

        for ($i = 0; $i < 6; $i++) {
            $section = new Section();
            $preText = 'Content.' . $name . '.R' . $i . '.';
            Common::setContentElement($section, $preText . 'Diploma', $width[0], false);
            Common::setContentElement($section, $preText . 'SchoolType', $width[1], false);
            Common::setContentElement($section, $preText . 'Support', $width[2], false);
            Common::setContentElement($section, $preText . 'Gender', $width[3], true);
            Common::setContentElement($section, $preText . 'L1', $width['Level'], true);
            Common::setContentElement($section, $preText . 'L2', $width['Level'], true);
            if (strpos($name, 'N01_2') !== false || strpos($name, 'N02_2') !== false) {
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
                ->styleBackgroundColor('lightgrey')
                ->styleBorderRight()
                ->stylePaddingTop('36px')
                ->stylePaddingBottom('36.2px')
                , '58%')
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
                    ->setContent('ohne Angabe²')
                    ->styleBorderRight()
                    ->styleBorderBottom()
                )
                ->addElement((new Element())
                    ->setContent('insgesamt')
                    ->styleBorderRight()
                )
                , $width[3])
            ->addSliceColumn(Common::setTotalSlice($name, 'L1'), $width['Level'])
            ->addSliceColumn(Common::setTotalSlice($name, 'L2'), $width['Level']);

        if (strpos($name, 'N01_2') !== false || strpos($name, 'N02_2') !== false) {
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

        $array[] = 'Bitte nur den höchsten Abschluss an einer <u><b>' . (strpos($name, 'N02') === false
                ? 'allgemeinbildenden Schule bzw. Schule des zweiten Bildungsweges'
                : 'berufsbildenden Schule')
            .'</b></u> angeben.';
        $array[] = 'Laut Eintrag im Geburtenregister';
        $sliceList[] = Common::setFootnotes($array);

        return $sliceList;
    }
}