<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Common\Frontend\Text\Repository\Sup;

/**
 * Class B01
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS
 */
class B01
{
    /**
     * @param string $name
     *
     * @return array
     */
    public static function getContent($name)
    {
        switch ($name) {
            case 'B01_1_A':
                $title = 'B01-1-A. Absolventen/Abgänger im <u>Ausbildungsstatus Auszubildende/Schüler im Vollzeitunterricht</u>
                    aus dem Schuljahr {{ Content.SchoolYear.Past }} nach Bildungsgängen,</br>' . Common::getBlankSpace(15)
                    . 'planmäßiger Ausbildungsdauer, Förderschwerpunkten und Abschlussarten';
                break;
            case 'B01_1_U':
                $title = 'B01-1-U. Absolventen/Abgänger im <u>Ausbildungsstatus Umschüler im Vollzeitunterricht</u>
                    aus dem Schuljahr {{ Content.SchoolYear.Past }} nach Bildungsgängen,</br>' . Common::getBlankSpace(15)
                    . 'planmäßiger Ausbildungsdauer, Förderschwerpunkten und Abschlussarten';
                break;
            case 'B01_1_1_A':
                $title = 'B01-1.1-A Darunter Absolventen/Abgänger im <u>Ausbildungsstatus Auszubildende/Schüler im Vollzeitunterricht</u>
                    , deren Herkunftssprache nicht oder nicht</br>' . Common::getBlankSpace(17)
                    . ' ausschließlich Deutsch ist, aus dem Schuljahr {{ Content.SchoolYear.Past }}
                    nach Bildungsgängen, planmäßiger Ausbildungsdauer, Förderschwerpunkten und </br>'
                    . Common::getBlankSpace(18) . 'Abschlussarten';
                break;
            case 'B01_1_1_U':
                $title = 'B01-1.1-U Darunter Absolventen/Abgänger im <u>Ausbildungsstatus Umschüler im Vollzeitunterricht</u>
                    , deren Herkunftssprache nicht oder nicht</br>' . Common::getBlankSpace(17)
                    . ' ausschließlich Deutsch ist, aus dem Schuljahr {{ Content.SchoolYear.Past }}
                    nach Bildungsgängen, planmäßiger Ausbildungsdauer, Förderschwerpunkten und </br>'
                    . Common::getBlankSpace(18) . 'Abschlussarten';
                break;
            case 'B01_2_A':
                $title = 'B01-2-A. Absolventen/Abgänger im <u>Ausbildungsstatus Auszubildende/Schüler im Teilzeitunterricht</u>
                    aus dem Schuljahr {{ Content.SchoolYear.Past }} nach Bildungsgängen,</br>' . Common::getBlankSpace(15)
                    . 'planmäßiger Ausbildungsdauer, Förderschwerpunkten und Abschlussarten';
                break;
            case 'B01_2_U':
                $title = 'B01-2-U. Absolventen/Abgänger im <u>Ausbildungsstatus Umschüler im Teilzeitunterricht</u>
                    aus dem Schuljahr {{ Content.SchoolYear.Past }} nach Bildungsgängen,</br>' . Common::getBlankSpace(15)
                    . 'planmäßiger Ausbildungsdauer, Förderschwerpunkten und Abschlussarten';
                break;
            case 'B01_2_1_A':
                $title = 'B01-2.1-A Darunter Absolventen/Abgänger im <u>Ausbildungsstatus Auszubildende/Schüler im Teilzeitunterricht</u>
                    , deren Herkunftssprache nicht oder nicht</br>' . Common::getBlankSpace(17)
                    . ' ausschließlich Deutsch ist, aus dem Schuljahr {{ Content.SchoolYear.Past }}
                    nach Bildungsgängen, planmäßiger Ausbildungsdauer, Förderschwerpunkten und </br>'
                    . Common::getBlankSpace(18) . 'Abschlussarten';
                break;
            case 'B01_2_1_U':
                $title = 'B01-2.1-U Darunter Absolventen/Abgänger im <u>Ausbildungsstatus Umschüler im Teilzeitunterricht</u>
                    , deren Herkunftssprache nicht oder nicht</br>' . Common::getBlankSpace(17)
                    . ' ausschließlich Deutsch ist, aus dem Schuljahr {{ Content.SchoolYear.Past }}
                    nach Bildungsgängen, planmäßiger Ausbildungsdauer, Förderschwerpunkten und </br>'
                    . Common::getBlankSpace(18) . 'Abschlussarten';
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

        $width[0] = '26%';
        $width[1] = '6%';
        $width[2] = '14%';
        $width[3] = '10%';
        $width[4] = '12%';
        $width[5] = '24%';
        $width[6] = '8%';

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
                    ->styleBorderRight()
                    ->stylePaddingTop($paddingTop)
                    ->stylePaddingBottom($paddingBottom)
                , $width[0])
                ->addElementColumn((new Element())
                    ->setContent('Plan-<br/>mäßige<br/>Ausbil-<br/>dungs-<br/>dauer in<br/>Monaten')
                    ->styleBorderRight()
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
                ->addElementColumn((new Element())
                    ->setContent('<b>Abgänger</b><br/>mit Abgangszeugnis¹')
                    ->stylePaddingTop('25.5px')
                    ->stylePaddingBottom('25.7px')
                    ->styleBorderRight()
                , $width[4])
                ->addSliceColumn((new Slice())
                    ->addElement((new Element())
                        ->setContent('<b>Absolventen</b> mit Abschlusszeugnis²')
                        ->styleBorderRight()
                        ->styleBorderBottom()
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('zusammen')
                            ->stylePaddingTop('33px')
                            ->stylePaddingBottom('34.3px')
                            ->styleBorderRight()
                        , '50%')
                        ->addElementColumn((new Element())
                            ->setContent('darunter zu-<br/>sätzlich zuer-<br/>kannter mittlerer<br/>Schulabschluss')
                            ->stylePaddingTop('8px')
                            ->stylePaddingBottom('8px')
                            ->styleBorderRight()
                        , '50%')
                    )
                , $width[5])
                ->addSliceColumn((new Slice())
                    ->styleTextBold()
                    ->addElement((new Element())
                        ->setContent('Insgesamt')
                        ->stylePaddingTop($paddingTop)
                        ->stylePaddingBottom($paddingBottom)
                    )
                , $width[6])
            );

        for ($i = 0; $i < 6; $i++) {
            $section = new Section();
            $preText = 'Content.' . $name . '.R' . $i . '.';
            Common::setContentElement($section, $preText . 'Course', $width[0], false);
            Common::setContentElement($section, $preText . 'Time', $width[1], true);
            Common::setContentElement($section, $preText . 'Support', $width[2], false);
            Common::setContentElement($section, $preText . 'Gender', $width[3], true);
            Common::setContentElement($section, $preText . 'Leave', $width[4], true);
            Common::setContentElement($section, $preText . 'DiplomaTotal', $width[4], true);
            Common::setContentElement($section, $preText . 'DiplomaAddition', $width[4], true);
            Common::setContentElement($section, $preText . 'TotalCount', $width[6], true, true);

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
                ->styleBackgroundColor('lightgrey')
                ->styleBorderRight()
                ->stylePaddingTop('36px')
                ->stylePaddingBottom('36.2px')
            , '46%')
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
                    ->setContent('ohne Angabe' . new Sup('4'))
                    ->styleBorderRight()
                    ->styleBorderBottom()
                )
                ->addElement((new Element())
                    ->setContent('insgesamt')
                    ->styleBorderRight()
                )
            , $width[3])
            ->addSliceColumn(Common::setTotalSlice($name, 'Leave'), $width[4])
            ->addSliceColumn(Common::setTotalSlice($name, 'DiplomaTotal'), $width[4])
            ->addSliceColumn(Common::setTotalSlice($name, 'DiplomaAddition'), $width[4])
            ->addSliceColumn(Common::setTotalSlice($name, 'TotalCount', true), $width[6]);

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
        $array[] = 'Nicht erfasst werden Schüler, die bei Verbleib im Bildungsgang die Schule wechseln!</br>'
                    . Common::getBlankSpace(5) . 'Ohne Teilnehmer von Schulfremdenprüfungen. Diese werden als 
                    Absolventen/Abgänger an der Schule gezählt, an der die Ausbildung stattfand.';
        $array[] = 'Laut Eintrag im Geburtenregister';
        $sliceList[] = Common::setFootnotes($array);

        return $sliceList;
    }


}