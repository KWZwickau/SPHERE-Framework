<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportFS;

use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS\Common;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class B02
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\KamenzReportFS
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
        $width[1] = '15%';
        $width[2] = '13%';
        $width[3] = '39%';
        $width[4] = '13%';

        $paddingTop = '26px';
        $paddingBottom = '27.4px';

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
                        ->setContent('<b>Abgänger</b> mit<br/>Abgangszeugnis')
                        ->stylePaddingTop('18px')
                        ->stylePaddingBottom('18.2px')
                        ->styleBorderRight()
                    )
                    , $width[2])
                ->addSliceColumn((new Slice())
                    ->addElement((new Element())
                        ->setContent('<b>Absolventen</b> mit Abschlusszeugnis')
                        ->styleBorderRight()
                        ->styleBorderBottom()
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('zusammen')
                            ->stylePaddingTop('17.2px')
                            ->stylePaddingBottom('18px')
                            ->styleBorderRight()
                            , '33.333%')
                        ->addSliceColumn((new Slice())
                            ->addElement((new Element())
                                ->setContent('darunter zusätzlich')
                                ->styleBorderRight()
                                ->styleBorderBottom()
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('zuerk. mittlerer<br/>Schulabschluss')
                                    ->styleBorderRight()
                                    , '50%')
                                ->addElementColumn((new Element())
                                    ->setContent('erworbene Fach-<br/>hochschulreife')
                                    ->styleBorderRight()
                                    , '50%')
                            )
                            , '66.666%')
                    )
                    , $width[3])
                ->addSliceColumn((new Slice())
                    ->styleTextBold()
                    ->addElement((new Element())
                        ->setContent('Insgesamt')
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
            Common::setContentElement($section, $preText . 'DiplomaExtra', $width[2], true);
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
                ->setContent('Insgesamt¹')
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
                    ->setContent('ohne Angabe²')
                    ->styleBorderRight()
                    ->styleBorderBottom()
                )
                ->addElement((new Element())
                    ->setContent('insgesamt')
                    ->styleBorderRight()
                )
                , $width[1])
            ->addSliceColumn(Common::setTotalSlice($name, 'Leave'), $width[2])
            ->addSliceColumn(Common::setTotalSlice($name, 'DiplomaTotal'), $width[2])
            ->addSliceColumn(Common::setTotalSlice($name, 'DiplomaAddition'), $width[2])
            ->addSliceColumn(Common::setTotalSlice($name, 'DiplomaExtra'), $width[2])
            ->addSliceColumn(Common::setTotalSlice($name, 'TotalCount', true), $width[4]);

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleTextBold()
            ->styleAlignCenter()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection($section);

        $array[] = 'Nicht erfasst werden Schüler, die bei Verbleib im Bildungsgang die Schule wechseln!<br />'
            . Common::getBlankSpace(5) . 'Ohne Teilnehmer von Schulfremdenprüfungen. Diese werden als 
                    Absolventen/Abgänger an der Schule gezählt, an der die Ausbildung stattfand.';
        $array[] = 'Laut Eintrag im Geburtenregister';
        $sliceList[] = Common::setFootnotes($array);

        return $sliceList;
    }
}