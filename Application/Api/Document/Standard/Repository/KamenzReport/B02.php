<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 23.06.2017
 * Time: 13:27
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReport;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

class B02
{
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('B02. Absolventen/Abgänger aus dem Schuljahr {{ Content.SchoolYear.Past }} nach Geburtsjahren und Abschlussarten')
            );

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleAlignCenter()
            ->styleBorderTop()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Geburts-<br/>jahr')
                    ->styleBorderRight()
                    ->stylePaddingTop('9.1px')
                    ->stylePaddingBottom('9px'), '10%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Abgangszeugnis')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '18%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Hauptschul-<br/>abschluss')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '18%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Qual. Haupt-<br/>schulabschluss')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '18%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Realschul-<br/>abschluss')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            , '18%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Insgesamt')
                            ->styleTextBold()
                            ->styleBorderBottom()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '18%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '9%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '9%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '9%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '9%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '9%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '9%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '9%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '9%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleTextBold()
                            ->styleBorderRight(), '9%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleTextBold(), '9%'
                        )
                    )
                )
            );

        for ($i = 0; $i < 10; $i++) {
            $section = new Section();
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.B02.Y' . $i . '.YearName is not empty) %}
                                {{ Content.B02.Y' . $i . '.YearName }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '10%'
                );

            for ($j = 0; $j < 4; $j++) {
                switch ($j) {
                    case 0:
                        $identifier = 'Leave';
                        break;
                    case 1:
                        $identifier = 'MsAbsHs';
                        break;
                    case 2:
                        $identifier = 'MsAbsHsQ';
                        break;
                    case 3:
                        $identifier = 'MsAbsRs';
                        break;
                    default:
                        $identifier = 'Default';
                }
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.B02.Y' . $i . '.' . $identifier . '.m is not empty) %}
                                {{ Content.B02.Y' . $i . '.' . $identifier . '.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '9%'
                    );
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.B02.Y' . $i . '.' . $identifier . '.w is not empty) %}
                                {{ Content.B02.Y' . $i . '.' . $identifier . '.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '9%'
                    );
            }

            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.B02.Y' . $i . '.m is not empty) %}
                                {{ Content.B02.Y' . $i . '.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBackgroundColor('lightgrey')
                    ->styleTextBold()
                    ->styleBorderRight(), '9%'
                );
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.B02.Y' . $i . '.w is not empty) %}
                                {{ Content.B02.Y' . $i . '.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBackgroundColor('lightgrey')
                    ->styleTextBold()
                    , '9%'
                );

            $sliceList[] = (new Slice())
                ->styleAlignCenter()
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
                ->styleBorderRight(), '10%'
            );

        for ($j = 0; $j < 4; $j++) {
            switch ($j) {
                case 0:
                    $identifier = 'Leave';
                    break;
                case 1:
                    $identifier = 'MsAbsHs';
                    break;
                case 2:
                    $identifier = 'MsAbsHsQ';
                    break;
                case 3:
                    $identifier = 'MsAbsRs';
                    break;
                default:
                    $identifier = 'Default';
            }
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.B02.TotalCount.' . $identifier . '.m is not empty) %}
                                {{ Content.B02.TotalCount.' . $identifier . '.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '9%'
                );
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.B02.TotalCount.' . $identifier . '.w is not empty) %}
                                {{ Content.B02.TotalCount.' . $identifier . '.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '9%'
                );
        }

        $section
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.B02.TotalCount.m is not empty) %}
                        {{ Content.B02.TotalCount.m }}
                    {% else %}
                        &nbsp;
                    {% endif %}
                ')
                ->styleAlignCenter()
                ->styleTextBold()
                ->styleBorderRight(), '9%'
            );
        $section
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.B02.TotalCount.w is not empty) %}
                        {{ Content.B02.TotalCount.w }}
                    {% else %}
                        &nbsp;
                    {% endif %}
                ')
                ->styleAlignCenter()
                ->styleTextBold()
                , '9%'
            );

        $sliceList[] = (new Slice())
            ->styleAlignCenter()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->styleBackgroundColor('lightgrey')
            ->styleTextBold()
            ->addSection($section);

        return $sliceList;
    }
}