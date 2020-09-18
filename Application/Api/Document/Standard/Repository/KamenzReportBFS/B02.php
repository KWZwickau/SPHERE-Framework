<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

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
    public static function getContent($name = 'B02_1')
    {
        switch ($name) {
            case 'B02_1':
                $title = 'B02-1. Abgänger/ Absolventen im <u>Vollzeitunterricht</u> {{ Content.SchoolYear.Past }} nach 
                    Geburtsjahren, Ausbildungsstatus und Art der Beendigung';
                break;
            case 'B02_1_1':
                $title = 'B02-1.1 Darunter Abgänger/ Absolventen, deren Herkunftssprache nicht oder nicht ausschließlich 
                    Deutsch ist, im <u>Vollzeitunterricht</u> {{ Content.SchoolYear.Past }} nach 
                    </br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Geburtsjahren, 
                    Ausbildungsstatus und Art der Beendigung';
                break;
            case 'B02_2':
                $title = 'B02-2. Abgänger/ Absolventen im <u>Teilunterricht</u> {{ Content.SchoolYear.Past }} nach 
                    Geburtsjahren, Ausbildungsstatus und Art der Beendigung';
                break;
            case 'B02_2_1':
                $title = 'B02-2.1 Darunter Abgänger/ Absolventen, deren Herkunftssprache nicht oder nicht ausschließlich 
                    Deutsch ist, im <u>Teilzeitunterricht</u> {{ Content.SchoolYear.Past }} nach 
                    </br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Geburtsjahren, 
                    Ausbildungsstatus und Art der Beendigung';
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

        $width[0] = '10%';
        $width[1] = '18%';
        $width[2] = '18%';
        $width[3] = '36%';
        $width[4] = '18%';
        $width['gender'] = '9%';

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleAlignCenter()
            ->styleBorderTop()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Geburts-<br/>jahr¹')
                    ->styleBorderRight()
                    ->stylePaddingTop('17.6px')
                    ->stylePaddingBottom('18.6px')
                    , $width[0])
                ->addElementColumn((new Element())
                    ->setContent('Ausbildungsstatus²')
                    ->styleBorderRight()
                    ->stylePaddingTop('25.5px')
                    ->stylePaddingBottom('27.8px')
                    , $width[1])
                ->addSliceColumn((new Slice())
                    ->addElement((new Element())
                        ->setContent('<b>Abgänger</b><br/>mit Abgangszeugnis')
                        ->stylePaddingTop('8.5px')
                        ->stylePaddingBottom('9.6px')
                        ->styleBorderRight()
                        ->styleBorderBottom()
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight()
                            , '50%')
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight()
                            , '50%')
                    ), $width[2])
                ->addSliceColumn((new Slice())
                    ->addElement((new Element())
                        ->setContent('<b>Absolventen</b> mit Abschlusszeugnis')
                        ->styleBorderRight()
                        ->styleBorderBottom()
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('zusammen')
                            ->stylePaddingTop('8.2px')
                            ->stylePaddingBottom('9px')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '50%')
                        ->addElementColumn((new Element())
                            ->setContent('darunter zus. zuerkannter<br/>mittlerer Schulabschluss')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '50%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight()
                            , '25%')
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight()
                            , '25%')
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight()
                            , '25%')
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight()
                            , '25%')
                    )
                    , $width[3])
                ->addSliceColumn((new Slice())
                    ->styleTextBold()
                    ->addElement((new Element())
                        ->setContent('Insgesamt')
                        ->styleBorderBottom()
                        ->stylePaddingTop('17px')
                        ->stylePaddingBottom('18.2px')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight()
                            , '50%')
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            , '50%')
                    ), $width[4])
            );

        for ($i = 0; $i < 6; $i++) {
            $section = new Section();
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.' . $name . '.R' . $i . '.Year is not empty) %}
                                {{ Content.' . $name . '.R' . $i . '.Year }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    , $width[0]);
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.' . $name . '.R' . $i . '.Status is not empty) %}
                                {{ Content.' . $name . '.R' . $i . '.Status }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->stylePaddingLeft('5px')
                    ->styleBorderRight()
                    , $width[1]);
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.' . $name . '.R' . $i . '.Leave.m is not empty) %}
                                {{ Content.' . $name . '.R' . $i . '.Leave.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    , $width['gender'])
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.' . $name . '.R' . $i . '.Leave.w is not empty) %}
                                {{ Content.' . $name . '.R' . $i . '.Leave.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    , $width['gender']);
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.' . $name . '.R' . $i . '.DiplomaTotal.m is not empty) %}
                                {{ Content.' . $name . '.R' . $i . '.DiplomaTotal.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    , $width['gender'])
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.' . $name . '.R' . $i . '.DiplomaTotal.w is not empty) %}
                                {{ Content.' . $name . '.R' . $i . '.DiplomaTotal.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    , $width['gender']);
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.' . $name . '.R' . $i . '.DiplomaAddition.m is not empty) %}
                                {{ Content.' . $name . '.R' . $i . '.DiplomaAddition.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    , $width['gender'])
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.' . $name . '.R' . $i . '.DiplomaAddition.w is not empty) %}
                                {{ Content.' . $name . '.R' . $i . '.DiplomaAddition.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    , $width['gender']);
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.' . $name . '.R' . $i . '.TotalCount.m is not empty) %}
                                {{ Content.' . $name . '.R' . $i . '.TotalCount.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight()
                    ->styleAlignCenter()
                    ->styleTextBold()
                    , $width['gender'])
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.' . $name . '.R' . $i . '.TotalCount.w is not empty) %}
                                {{ Content.' . $name . '.R' . $i . '.TotalCount.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBackgroundColor('lightgrey')
                    ->styleAlignCenter()
                    ->styleTextBold()
                    , $width['gender']);

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
                ->stylePaddingTop('8.5px')
                ->stylePaddingBottom('9.6px')
                , $width[0])
            ->addSliceColumn((new Slice())
                ->addElement((new Element())
                    ->setContent('Auszubildender')
                    ->styleBorderRight()
                    ->styleBorderBottom()
                )
                ->addElement((new Element())
                    ->setContent('Umschüler')
                    ->styleBorderRight()
                )
                , $width[1])
            ->addSliceColumn(self::getTotalSlice($name, 'Leave', 'm'), $width['gender'])
            ->addSliceColumn(self::getTotalSlice($name, 'Leave', 'w'), $width['gender'])
            ->addSliceColumn(self::getTotalSlice($name, 'DiplomaTotal', 'm'), $width['gender'])
            ->addSliceColumn(self::getTotalSlice($name, 'DiplomaTotal', 'w'), $width['gender'])
            ->addSliceColumn(self::getTotalSlice($name, 'DiplomaAddition', 'm'), $width['gender'])
            ->addSliceColumn(self::getTotalSlice($name, 'DiplomaAddition', 'w'), $width['gender'])
            ->addSliceColumn(self::getTotalSlice($name, 'TotalCount', 'm'), $width['gender'])
            ->addSliceColumn(self::getTotalSlice($name, 'TotalCount', 'w', true), $width['gender']);

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleTextBold()
            ->styleAlignCenter()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection($section);

        $sliceList[] = (new Slice())
            ->addElement((new Element())
                ->setContent(
                    '1)&nbsp;&nbsp;Jedes Geburtsjahr erscheint pro Ausbildungsstatus nur einmal. Schüler eines Geburtsjahres bitte zusammenfassen.</br>
                     2)&nbsp;&nbsp;Bitte signieren: Auszubildende/Schüler; Umschüler (Schüler in Maßnahmen der beruflichen Umschulung)</br>
                     3)&nbsp;&nbsp;Nicht erfasst werden Schüler, die bei Verbleib im Bildungsgang die Schule wechseln!</br>
                     &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Ohne Teilnehmer von Schulfremdenprüfungen. Diese werden als 
                     Absolventen/Abgänger an der Schule gezählt, an der die Ausbildung stattfand.'
                )
                ->styleMarginTop('15px')
            );

        return $sliceList;
    }

    /**
     * @param $name
     * @param $identifier
     * @param $gender
     *
     * @param bool $isLastColumn
     *
     * @return Slice
     */
    private static function getTotalSlice($name, $identifier, $gender, $isLastColumn = false)
    {
        return (new Slice())
            ->addElement((new Element())
                ->setContent('
                        {% if (Content.' . $name . '.TotalCount.Student.' . $identifier . '.' . $gender . ' is not empty) %}
                            {{ Content.' . $name . '.TotalCount.Student.' . $identifier . '.' . $gender . ' }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                ->styleBorderRight($isLastColumn ? '0px': '1px')
                ->styleBorderBottom()
            )
            ->addElement((new Element())
                ->setContent('
                        {% if (Content.' . $name . '.TotalCount.ChangeStudent.' . $identifier . '.' . $gender . ' is not empty) %}
                            {{ Content.' . $name . '.TotalCount.ChangeStudent.' . $identifier . '.' . $gender . ' }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                ->styleBorderRight($isLastColumn ? '0px': '1px')
            );
    }
}