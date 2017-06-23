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
                ->setContent('B02. Absolventen/Abgänger aus dem Schuljahr {{ Content.Schoolyear.Current }} nach Geburtsjahren und Abschlussarten')
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
                    ->stylePaddingBottom('9px'), '12%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Abgangszeugnis')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '17.6%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Hauptschul-<br/>abschluss')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '17.6%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Quali. Haupt-<br/>schulabschluss')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '17.6%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Realschul-<br/>abschluss')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            , '17.6%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Insgesamt')
                            ->styleTextBold()
                            ->styleBorderBottom()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '17.6%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '8.8%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '8.8%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '8.8%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '8.8%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '8.8%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '8.8%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '8.8%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '8.8%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleTextBold()
                            ->styleBorderRight(), '8.8%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleTextBold(), '8.8%'
                        )
                    )
                )
            );

        //ToDo: Zellen befüllen
        for ($i = 0; $i < 10; $i++) {
            $sliceList[] = (new Slice())
                ->styleAlignCenter()
                ->styleBorderBottom()
                ->styleBorderLeft()
                ->styleBorderRight()
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Test')
                        ->styleBorderRight(), '12%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Test')
                        ->styleBorderRight(), '8.8%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Test')
                        ->styleBorderRight(), '8.8%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Test')
                        ->styleBorderRight(), '8.8%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Test')
                        ->styleBorderRight(), '8.8%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Test')
                        ->styleBorderRight(), '8.8%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Test')
                        ->styleBorderRight(), '8.8%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Test')
                        ->styleBorderRight(), '8.8%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Test')
                        ->styleBorderRight(), '8.8%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleBackgroundColor('lightgrey')
                        ->styleBorderRight(), '8.8%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleBackgroundColor('lightgrey'), '8.8%'
                    )
                );
        }

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleTextBold()
            ->styleAlignCenter()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Insgesamt')
                    ->styleBorderRight(), '12%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '8.8%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '8.8%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '8.8%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '8.8%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '8.8%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '8.8%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '8.8%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '8.8%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '8.8%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;'), '8.8%'
                )
            );


        return $sliceList;
    }
}