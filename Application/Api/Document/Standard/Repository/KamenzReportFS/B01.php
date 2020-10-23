<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportFS;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class B01
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\KamenzReportFS
 */
class B01
{
    /**
     * @param string $name
     *
     * @return array
     */
    public static function getContent($name = 'B01')
    {
        switch ($name) {
            case 'B01':
                $title = 'B01. Abgänger/ Absolventen {{ Content.SchoolYear.Past }} nach Bildungsgängen, planmäßiger 
                    Ausbildungsdauer, Zeitform des Unterrichts, Ausbildungsstatus und Art der 
                    </br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Beendigung';
                break;
            case 'B01_1':
                $title = 'B01.1 Darunter Abgänger/ Absolventen, deren Herkunftssprache nicht oder nicht ausschließlich Deutsch
                    ist, {{ Content.SchoolYear.Past }} nach Bildungsgängen, planmäßiger 
                    </br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Ausbildungsdauer, Zeitform des Unterrichts, 
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

        $width[0] = '13%';
        $width[1] = '5%';
        $width[2] = '9%';
        $width[3] = '13%';
        $width[4] = '12%';
        $width[5] = '36%';
        $width[6] = '12%';
        $width['gender'] = '6%';

        $paddingTop = '15.7px';
        $paddingBottom = '16.5px';

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
                    ->stylePaddingTop('51px')
                    ->stylePaddingBottom('51.6px')
                    , $width[0])
                ->addElementColumn((new Element())
                    ->setContent('Plan-<br/>mäßige<br/>Ausbil-<br/>dungs-<br/>dauer<br/>in Mo-<br/>naten')
                    ->styleBorderRight()
                    , $width[1])
                ->addElementColumn((new Element())
                    ->setContent('Zeitform<br/>des<br/>Unter-<br/>richts¹')
                    ->styleBorderRight()
                    ->stylePaddingTop('24px')
                    ->stylePaddingBottom('27.5px')
                    , $width[2])
                ->addElementColumn((new Element())
                    ->setContent('Ausbildungsstatus²')
                    ->styleBorderRight()
                    ->stylePaddingTop('51px')
                    ->stylePaddingBottom('51.6px')
                    , $width[3])
                ->addSliceColumn((new Slice())
                    ->addElement((new Element())
                        ->setContent('<b>Abgänger</b><br/>mit Abgangszeug.')
                        ->stylePaddingTop('17.2px')
                        ->stylePaddingBottom('18px')
                        ->styleBorderRight()
                        ->styleBorderBottom()
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->stylePaddingTop($paddingTop)
                            ->stylePaddingBottom($paddingBottom)
                            ->styleBorderRight()
                            , '50%')
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->stylePaddingTop($paddingTop)
                            ->stylePaddingBottom($paddingBottom)
                            ->styleBorderRight()
                            , '50%')
                    ), $width[4])
                ->addSliceColumn((new Slice())
                    ->addElement((new Element())
                        ->setContent('<b>Absolventen</b> mit Abschlusszeugnis')
                        ->styleBorderRight()
                        ->styleBorderBottom()
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('zusammen')
                            ->stylePaddingTop('16.5px')
                            ->stylePaddingBottom('17.7px')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '33.333%')
                        ->addElementColumn((new Element())
                            ->setContent('darunter zusätzlich<br/>mittlerer<br/>Schulabschluss')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '33.333%')
                        ->addElementColumn((new Element())
                            ->setContent('darunter zusätzlich<br/>Fach-<br/>hochschulreife')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '33.333%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->stylePaddingTop($paddingTop)
                            ->stylePaddingBottom($paddingBottom)
                            ->styleBorderRight()
                            , '16.666%')
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->stylePaddingTop($paddingTop)
                            ->stylePaddingBottom($paddingBottom)
                            ->styleBorderRight()
                            , '16.666%')
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->stylePaddingTop($paddingTop)
                            ->stylePaddingBottom($paddingBottom)
                            ->styleBorderRight()
                            , '16.666%')
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->stylePaddingTop($paddingTop)
                            ->stylePaddingBottom($paddingBottom)
                            ->styleBorderRight()
                            , '16.666%')
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->stylePaddingTop($paddingTop)
                            ->stylePaddingBottom($paddingBottom)
                            ->styleBorderRight()
                            , '16.666%')
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->stylePaddingTop($paddingTop)
                            ->stylePaddingBottom($paddingBottom)
                            ->styleBorderRight()
                            , '16.666%')
                    )
                    , $width[5])
                ->addSliceColumn((new Slice())
                    ->styleTextBold()
                    ->addElement((new Element())
                        ->setContent('Insgesamt')
                        ->styleBorderBottom()
                        ->stylePaddingTop('25.3px')
                        ->stylePaddingBottom('27px')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->stylePaddingTop($paddingTop)
                            ->stylePaddingBottom($paddingBottom)
                            ->styleBorderRight()
                            , '50%')
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->stylePaddingTop($paddingTop)
                            ->stylePaddingBottom($paddingBottom)
                            , '50%')
                    ), $width[6])
            );

        for ($i = 0; $i < 6; $i++) {
            $section = new Section();
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.' . $name . '.R' . $i . '.Course is not empty) %}
                                {{ Content.' . $name . '.R' . $i . '.Course }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->stylePaddingLeft('5px')
                    ->styleBorderRight()
                    , $width[0]);
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.' . $name . '.R' . $i . '.Time is not empty) %}
                                {{ Content.' . $name . '.R' . $i . '.Time }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    , $width[1]);
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.' . $name . '.R' . $i . '.Lesson is not empty) %}
                                {{ Content.' . $name . '.R' . $i . '.Lesson }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->stylePaddingLeft('5px')
                    ->styleBorderRight()
                    , $width[2]);
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
                    , $width[3]);
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
                            {% if (Content.' . $name . '.R' . $i . '.DiplomaAdditionExtra.m is not empty) %}
                                {{ Content.' . $name . '.R' . $i . '.DiplomaAdditionExtra.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    , $width['gender'])
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.' . $name . '.R' . $i . '.DiplomaAdditionExtra.w is not empty) %}
                                {{ Content.' . $name . '.R' . $i . '.DiplomaAdditionExtra.w }}
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
                ->setContent('Insgesamt')
                ->styleBackgroundColor('lightgrey')
                ->styleBorderRight()
                ->stylePaddingTop('26.3px')
                ->stylePaddingBottom('28px')
                , '18%')
            ->addSliceColumn((new Slice())
                ->addElement((new Element())
                    ->setContent('Vollzeit')
                    ->styleBorderRight()
                    ->styleBorderBottom()
                    ->stylePaddingTop('8.5px')
                    ->stylePaddingBottom('9.6px')
                )
                ->addElement((new Element())
                    ->setContent('Teilzeit')
                    ->styleBorderRight()
                    ->stylePaddingTop('8.5px')
                    ->stylePaddingBottom('9.6px')
                )
                , $width[2])
            ->addSliceColumn((new Slice())
                ->addElement((new Element())
                    ->setContent('Auszubildender')
                    ->styleBorderRight()
                    ->styleBorderBottom()
                )
                ->addElement((new Element())
                    ->setContent('Umschüler')
                    ->styleBorderRight()
                    ->styleBorderBottom()
                )
                ->addElement((new Element())
                    ->setContent('Auszubildender')
                    ->styleBorderRight()
                    ->styleBorderBottom()
                )
                ->addElement((new Element())
                    ->setContent('Umschüler')
                    ->styleBorderRight()
                )
                , $width[3])
            ->addSliceColumn(self::getTotalSlice($name, 'Leave', 'm'), $width['gender'])
            ->addSliceColumn(self::getTotalSlice($name, 'Leave', 'w'), $width['gender'])
            ->addSliceColumn(self::getTotalSlice($name, 'DiplomaTotal', 'm'), $width['gender'])
            ->addSliceColumn(self::getTotalSlice($name, 'DiplomaTotal', 'w'), $width['gender'])
            ->addSliceColumn(self::getTotalSlice($name, 'DiplomaAddition', 'm'), $width['gender'])
            ->addSliceColumn(self::getTotalSlice($name, 'DiplomaAddition', 'w'), $width['gender'])
            ->addSliceColumn(self::getTotalSlice($name, 'DiplomaAdditionExtra', 'm'), $width['gender'])
            ->addSliceColumn(self::getTotalSlice($name, 'DiplomaAdditionExtra', 'w'), $width['gender'])
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
                    '1)&nbsp;&nbsp;Bitte signieren: Vollzeitunterricht; Teilzeitunterricht</br>
                     2)&nbsp;&nbsp;Bitte signieren: Auszubildende/Schüler; Umschüler (Schüler in Maßnahmen der beruflichen Umschulung)'
                )
                ->styleMarginTop('15px')
            );

        return $sliceList;
    }

    /**
     * @param $name
     * @param $identifier
     * @param $gender
     * @param bool $isLastColumn
     *
     * @return Slice
     */
    private static function getTotalSlice($name, $identifier, $gender, $isLastColumn = false)
    {
        return (new Slice())
            ->addElement((new Element())
                ->setContent('
                        {% if (Content.' . $name . '.TotalCount.FullTime.Student.' . $identifier . '.' . $gender . ' is not empty) %}
                            {{ Content.' . $name . '.TotalCount.FullTime.Student.' . $identifier . '.' . $gender . ' }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                ->styleBorderRight($isLastColumn ? '0px': '1px')
                ->styleBorderBottom()
            )
            ->addElement((new Element())
                ->setContent('
                        {% if (Content.' . $name . '.TotalCount.FullTime.ChangeStudent.' . $identifier . '.' . $gender . ' is not empty) %}
                            {{ Content.' . $name . '.TotalCount.FullTime.ChangeStudent.' . $identifier . '.' . $gender . ' }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                ->styleBorderRight($isLastColumn ? '0px': '1px')
                ->styleBorderBottom()
            )
            ->addElement((new Element())
                ->setContent('
                        {% if (Content.' . $name . '.TotalCount.PartTime.Student.' . $identifier . '.' . $gender . ' is not empty) %}
                            {{ Content.' . $name . '.TotalCount.PartTime.Student.' . $identifier . '.' . $gender . ' }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                ->styleBorderRight($isLastColumn ? '0px': '1px')
                ->styleBorderBottom()
            )
            ->addElement((new Element())
                ->setContent('
                        {% if (Content.' . $name . '.TotalCount.PartTime.ChangeStudent.' . $identifier . '.' . $gender . ' is not empty) %}
                            {{ Content.' . $name . '.TotalCount.PartTime.ChangeStudent.' . $identifier . '.' . $gender . ' }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                ->styleBorderRight($isLastColumn ? '0px': '1px')
            );
    }
}