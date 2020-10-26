<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportFS;

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
    public static function getContent($name = 'N01')
    {
        switch ($name) {
            case 'N01':
                $title = 'N01. Neuanfänger im Schuljahr {{ Content.SchoolYear.Current }} nach allgemeinbildenden Abschlüssen, 
                    Schularten, Zeitform des Unterrichts, Ausbildungsstatus und
                    </br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Klassenstufen';
                $columnName = 'Allgemeinbildender Abschluss¹';
                $footNote = '1)&nbsp;&nbsp;Bitte nur den höchsten Abschluss an einer allgemeinbildenden Schule angeben.</br>
                     2)&nbsp;&nbsp;Bitte signieren: Vollzeitunterricht; Teilzeitunterricht</br>';
                break;
            case 'N01_1':
                $title = 'N01.1 Darunter Neuanfänger, deren Herkunftssprache nicht oder nicht ausschließlich Deutsch ist,
                    im Schuljahr {{ Content.SchoolYear.Current }} nach allgemeinbildenden
                    </br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Abschlüssen, Schularten, Zeitform des 
                    Unterrichts, Ausbildungsstatus und Klassenstufen';
                $columnName = 'Allgemeinbildender Abschluss¹';
                $footNote = '1)&nbsp;&nbsp;Bitte nur den höchsten Abschluss an einer allgemeinbildenden Schule angeben.</br>
                     2)&nbsp;&nbsp;Bitte signieren: Vollzeitunterricht; Teilzeitunterricht</br>';
                break;
            case 'N02':
                $title = 'N02. Neuanfänger im Schuljahr {{ Content.SchoolYear.Current }} nach berufsbildenden Abschlüssen, 
                    Schularten, Zeitform des Unterrichts, Ausbildungsstatus und Klassenstufen';
                $columnName = 'Berufsbildender Abschluss¹';
                $footNote = '1)&nbsp;&nbsp;Bitte nur den höchsten Abschluss an einer berufsbildenden Schule angeben.</br>
                     2)&nbsp;&nbsp;Bitte signieren: Vollzeitunterricht; Teilzeitunterricht; diese Angaben beziehen sich 
                     auf die derzeitige Ausbildung!</br>';
                break;
            case 'N02_1':
                $title = 'N02.1 Darunter Neuanfänger, deren Herkunftssprache nicht oder nicht ausschließlich Deutsch ist,
                    im Schuljahr {{ Content.SchoolYear.Current }} nach berufsbildenden
                    </br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Abschlüssen, Schularten, Zeitform des 
                    Unterrichts, Ausbildungsstatus und Klassenstufen';
                $columnName = 'Berufsbildender Abschluss¹';
                $footNote = '1)&nbsp;&nbsp;Bitte nur den höchsten Abschluss an einer berufsbildenden Schule angeben.</br>
                     2)&nbsp;&nbsp;Bitte signieren: Vollzeitunterricht; Teilzeitunterricht; diese Angaben beziehen sich 
                     auf die derzeitige Ausbildung!</br>';
                break;
            default:
                $title = '';
                $columnName = '';
                $footNote = '';
        }

        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginBottom('10px')
            ->addElement((new Element())
                ->setContent($title)
            );

        $width[0] = '18%';
        $width[1] = '13%';
        $width[2] = '6%';
        $width[3] = '15%';
        $width[4] = '40%';
        $width[5] = '8%';
        $width['gender'] = '4%';

        $padding = '3.8px';

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
                    ->stylePaddingTop('35px')
                    ->stylePaddingBottom('35.5px')
                    , $width[0])
                ->addElementColumn((new Element())
                    ->setContent('Schulart, an der der Abschluss erfolgte')
                    ->styleBorderRight()
                    ->stylePaddingTop('35px')
                    ->stylePaddingBottom('35.5px')
                    , $width[1])
                ->addElementColumn((new Element())
                    ->setContent('Zeitform<br/>des<br/>Unter-<br/>richts²')
                    ->styleBorderRight()
                    ->stylePaddingTop('18px')
                    ->stylePaddingBottom('18.2px')
                    , $width[2])
                ->addElementColumn((new Element())
                    ->setContent('Ausbildungsstatus³')
                    ->styleBorderRight()
                    ->stylePaddingTop('43px')
                    ->stylePaddingBottom('44.7px')
                    , $width[3])
                ->addSliceColumn((new Slice())
                    ->addElement((new Element())
                        ->setContent('Neuanfänger in Klassenstufe')
                        ->styleBorderRight()
                        ->styleBorderBottom()
                        ->stylePaddingTop($padding)
                        ->stylePaddingBottom($padding)
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Vollzeit')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            ->stylePaddingTop($padding)
                            ->stylePaddingBottom($padding)
                            , '40%')
                        ->addElementColumn((new Element())
                            ->setContent('Teilzeit')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            ->stylePaddingTop($padding)
                            ->stylePaddingBottom($padding)
                            , '60%')
                    )
                    ->addSection((new Section())
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('1')
                                    ->stylePaddingTop('9px')
                                    ->stylePaddingBottom('9px')
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '50%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('2')
                                    ->stylePaddingTop('9px')
                                    ->stylePaddingBottom('9px')
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '50%'
                                )
                            )
                        , '40%')
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addSliceColumn((new Slice())
                                    ->addSection((new Section())
                                        ->addElementColumn((new Element())
                                            ->setContent('1')
                                            ->styleBorderRight()
                                            ->styleBorderBottom()
                                        )
                                    )
                                    ->addSection((new Section())
                                        ->addElementColumn((new Element())
                                            ->setContent('1. AJ')
                                            ->styleBorderRight()
                                            ->styleBorderBottom()
                                            , '50%'
                                        )
                                        ->addElementColumn((new Element())
                                            ->setContent('2. AJ')
                                            ->styleBorderRight()
                                            ->styleBorderBottom()
                                            , '50%'
                                        )
                                    )
                                    , '66.666%')
                                ->addSliceColumn((new Slice())
                                    ->addSection((new Section())
                                        ->addElementColumn((new Element())
                                            ->setContent('2')
                                            ->styleBorderRight()
                                            ->styleBorderBottom()
                                        )
                                    )
                                    ->addSection((new Section())
                                        ->addElementColumn((new Element())
                                            ->setContent('3. AJ')
                                            ->styleBorderRight()
                                            ->styleBorderBottom()
                                        )
                                    )
                                , '33.333%')
                            )
                        , '60%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight()
                            , '10%')
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight()
                            , '10%')
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight()
                            , '10%')
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight()
                            , '10%')
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight()
                            , '10%')
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight()
                            , '10%')
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight()
                            , '10%')
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight()
                            , '10%')
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight()
                            , '10%')
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight()
                            , '10%')
                    )
                    , $width[4])
                ->addSliceColumn((new Slice())
                    ->styleTextBold()
                    ->addElement((new Element())
                        ->setContent('Insgesamt')
                        ->styleBorderBottom()
                        ->stylePaddingTop('34px')
                        ->stylePaddingBottom('35.5px')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight()
                            , '50%')
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            , '50%')
                    )
                    , $width[5])
            );

        for ($i = 0; $i < 6; $i++) {
            $section = new Section();
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.' . $name . '.R' . $i . '.Diploma is not empty) %}
                            {{ Content.' . $name . '.R' . $i . '.Diploma }}
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
                        {% if (Content.' . $name . '.R' . $i . '.SchoolType is not empty) %}
                            {{ Content.' . $name . '.R' . $i . '.SchoolType }}
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
                        {% if (Content.' . $name . '.R' . $i . '.Lesson is not empty) %}
                            {{ Content.' . $name . '.R' . $i . '.Lesson }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
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
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    , $width[3]);

            // todo Vollzeit und Teilzeit
            for ($j = 1; $j < 6; $j++) {
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.' . $name . '.R' . $i . '.L' . $j . '.m is not empty) %}
                                {{ Content.' . $name . '.R' . $i . '.L' . $j . '.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderRight()
                        ->styleAlignCenter()
                        , $width['gender'])
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.' . $name . '.R' . $i . '.L' . $j . '.w is not empty) %}
                                {{ Content.' . $name . '.R' . $i . '.L' . $j . '.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderRight()
                        ->styleAlignCenter()
                        , $width['gender']);
            }

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
                , '31%')
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
                    ->setContent('Azubi/Schüler')
                    ->styleBorderRight()
                    ->styleBorderBottom()
                )
                ->addElement((new Element())
                    ->setContent('Umschüler')
                    ->styleBorderRight()
                    ->styleBorderBottom()
                )
                ->addElement((new Element())
                    ->setContent('Azubi/Schüler')
                    ->styleBorderRight()
                    ->styleBorderBottom()
                )
                ->addElement((new Element())
                    ->setContent('Umschüler')
                    ->styleBorderRight()
                )
                , $width[3])
            // todo Vollzeit und Teilzeit
            ->addSliceColumn(self::getTotalSlice($name, 'L1', 'm'), $width['gender'])
            ->addSliceColumn(self::getTotalSlice($name, 'L1', 'w'), $width['gender'])
            ->addSliceColumn(self::getTotalSlice($name, 'L2', 'm'), $width['gender'])
            ->addSliceColumn(self::getTotalSlice($name, 'L2', 'w'), $width['gender'])
            ->addSliceColumn(self::getTotalSlice($name, 'L1', 'm'), $width['gender'])
            ->addSliceColumn(self::getTotalSlice($name, 'L1', 'w'), $width['gender'])
            ->addSliceColumn(self::getTotalSlice($name, 'L2', 'm'), $width['gender'])
            ->addSliceColumn(self::getTotalSlice($name, 'L2', 'w'), $width['gender'])
            ->addSliceColumn(self::getTotalSlice($name, 'L3', 'm'), $width['gender'])
            ->addSliceColumn(self::getTotalSlice($name, 'L3', 'w'), $width['gender'])
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
                    $footNote
                    . '3)&nbsp;&nbsp;Bitte signieren: Auszubildende/Schüler; Umschüler (Schüler in Maßnahmen der
                    beruflichen Umschulung); diese Angaben beziehen sich auf die derzeitige Ausbildung!'
                )
                ->styleMarginTop('15px')
                ->styleTextSize('13px')
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