<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Common\Frontend\Text\Repository\Sup;

/**
 * Class B02
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS
 */
class B02
{
    /**
     * @param string $name
     *
     * @return array
     */
    public static function getContent($name)
    {
        switch ($name) {
            case 'B02_1_A':
                $title = 'B02-1-A. Absolventen/Abgänger im <u>Ausbildungsstatus Auszubildende/Schüler im Vollzeitunterricht</u>
                    aus dem Schuljahr {{ Content.SchoolYear.Past }} nach Geburtsjahren und<br />' . Common::getBlankSpace(16)
                    . 'Abschlussarten';
                break;
            case 'B02_1_U':
                $title = 'B02-1-U. Absolventen/Abgänger im <u>Ausbildungsstatus Umschüler im Vollzeitunterricht</u>
                    aus dem Schuljahr {{ Content.SchoolYear.Past }} nach Geburtsjahren und<br />' . Common::getBlankSpace(16)
                    . 'Abschlussarten';
                break;
            case 'B02_1_1_A':
                $title = 'B02-1.1-A. Darunter Absolventen/Abgänger im <u>Ausbildungsstatus Auszubildende/Schüler im Vollzeitunterricht</u>,
                    deren Herkunftssprache nicht oder nicht<br />' . Common::getBlankSpace(18)
                    . 'ausschließlich Deutsch ist, aus dem Schuljahr {{ Content.SchoolYear.Past }} nach Geburtsjahren und Abschlussarten';
                break;
            case 'B02_1_1_U':
                $title = 'B02-1.1-U. Darunter Absolventen/Abgänger im <u>Ausbildungsstatus Umschüler im Vollzeitunterricht</u>,
                    deren Herkunftssprache nicht oder nicht<br />' . Common::getBlankSpace(18)
                    . 'ausschließlich Deutsch ist, aus dem Schuljahr {{ Content.SchoolYear.Past }} nach Geburtsjahren und Abschlussarten';
                break;

            case 'B02_2_A':
                $title = 'B02-2-A. Absolventen/Abgänger im <u>Ausbildungsstatus Auszubildende/Schüler im Teilzeitunterricht</u>
                    aus dem Schuljahr {{ Content.SchoolYear.Past }} nach Geburtsjahren und<br />' . Common::getBlankSpace(16)
                    . 'Abschlussarten';
                break;
            case 'B02_2_U':
                $title = 'B02-2-U. Absolventen/Abgänger im <u>Ausbildungsstatus Umschüler im Teilzeitunterricht</u>
                    aus dem Schuljahr {{ Content.SchoolYear.Past }} nach Geburtsjahren und<br />' . Common::getBlankSpace(16)
                    . 'Abschlussarten';
                break;
            case 'B02_2_1_A':
                $title = 'B02-2.1-A. Darunter Absolventen/Abgänger im <u>Ausbildungsstatus Auszubildende/Schüler im Teilzeitunterricht</u>,
                    deren Herkunftssprache nicht oder nicht<br />' . Common::getBlankSpace(18)
                    . 'ausschließlich Deutsch ist, aus dem Schuljahr {{ Content.SchoolYear.Past }} nach Geburtsjahren und Abschlussarten';
                break;
            case 'B02_2_1_U':
                $title = 'B02-2.1-U. Darunter Absolventen/Abgänger im <u>Ausbildungsstatus Umschüler im Teilzeitunterricht</u>,
                    deren Herkunftssprache nicht oder nicht<br />' . Common::getBlankSpace(18)
                    . 'ausschließlich Deutsch ist, aus dem Schuljahr {{ Content.SchoolYear.Past }} nach Geburtsjahren und Abschlussarten';
                break;

            default: $title = '';
        }

        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginBottom('10px')
            ->addElement((new Element())
                ->setContent($title)
            );

        $width[0] = '20%';
        $width[1] = '16%';
        $width[2] = '16%';
        $width[3] = '32%';
        $width[4] = '16%';
        $width['gender'] = '9%';

        $paddingTop = '17px';
        $paddingBottom = '18px';

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleAlignCenter()
            ->styleBorderTop()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Geburtsjahr')
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
                        ->setContent('<b>Abgänger</b> mit<br/>Abgangszeugnis¹')
                        ->styleAlignCenter()
                        ->stylePaddingTop('8.5px')
                        ->stylePaddingBottom('9.6px')
                        ->styleBorderRight()
                    )
                    , $width[2])
                ->addSliceColumn((new Slice())
                    ->addElement((new Element())
                        ->setContent('<b>Absolventen</b> mit Abschlusszeugnis²')
                        ->styleAlignCenter()
                        ->styleBorderRight()
                        ->styleBorderBottom()
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('zusammen')
                            ->styleAlignCenter()
                            ->stylePaddingTop('8.2px')
                            ->stylePaddingBottom('9px')
                            ->styleBorderRight()
                            , '50%')
                        ->addElementColumn((new Element())
                            ->setContent('darunter zus. zuerkannter<br/>mittlerer Schulabschluss')
                            ->styleAlignCenter()
                            ->styleBorderRight()
                            , '50%')
                    )
                    , $width[3])
                ->addSliceColumn((new Slice())
                    ->styleTextBold()
                    ->addElement((new Element())
                        ->setContent('Insgesamt')
                        ->styleAlignCenter()
                        ->stylePaddingTop($paddingTop)
                        ->stylePaddingBottom($paddingBottom)
                    )
                , $width[4])
            );

        for ($i = 0; $i < 6; $i++) {
            $section = new Section();
            $preText = 'Content.' . $name . '.R' . $i . '.';
            Common::setContentElement($section, $preText . 'Year', $width[0], true);
            Common::setContentElement($section, $preText . 'Gender', $width[1], true);
            Common::setContentElement($section, $preText . 'Leave', $width[2], true);
            Common::setContentElement($section, $preText . 'DiplomaTotal', $width[2], true);
            Common::setContentElement($section, $preText . 'DiplomaAddition', $width[2], true);
            Common::setContentElement($section, $preText . 'TotalCount', $width[4], true, true);

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
                ->setContent('Insgesamt³')
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
                    ->setContent('ohne Angabe' . new Sup('4'))
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
            ->addSliceColumn(Common::setTotalSlice($name, 'Leave'), $width[2])
            ->addSliceColumn(Common::setTotalSlice($name, 'DiplomaTotal'), $width[2])
            ->addSliceColumn(Common::setTotalSlice($name, 'DiplomaAddition'), $width[2])
            ->addSliceColumn(Common::setTotalSlice($name, 'TotalCount', true), $width[4]);

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleTextBold()
            ->styleAlignCenter()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection($section);

        $array[] = 'Ohne erfolgreichen Abschluss des Bildungsganges';
        $array[] = 'Erfolgreicher Abschluss des Bildungsganges';
        $array[] = 'Nicht erfasst werden Schüler, die bei Verbleib im Bildungsgang die Schule wechseln!<br />'
            . Common::getBlankSpace(5) . 'Ohne Teilnehmer von Schulfremdenprüfungen. Diese werden als 
                    Absolventen/Abgänger an der Schule gezählt, an der die Ausbildung stattfand.';
        $array[] = 'Laut Eintrag im Geburtenregister';
        $sliceList[] = Common::setFootnotes($array);

        return $sliceList;
    }
}