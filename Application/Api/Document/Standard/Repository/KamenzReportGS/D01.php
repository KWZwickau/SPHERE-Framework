<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 23.06.2017
 * Time: 15:18
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGS;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

class D01
{
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('D01. Schulanfänger zu Beginn des Schuljahres {{ Content.Schoolyear.Current }}')
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
                    ->setContent('Art der Einschulung')
                    ->styleBorderRight()
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px'), '70%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Schüler'), '100%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '50'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w'), '50'
                        )
                    ), '30%'
                )
            );

        $sliceList[] = (new Slice())
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addSliceColumn((new Slice())
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Schulanfänger (ohne Wiederholer aus der Klassenstufe 1)')
                            ->styleBorderBottom(), '70%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('darunter aus dem Kindergarten und der Kindertagespflege')
                            ->styleBorderBottom(), '70%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('davon')
                            ->styleAlignCenter()
                            ->styleBorderRight()
                            ->stylePaddingTop('36.6px')
                            ->stylePaddingBottom('36.5px'), '10%'
                        )
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('vorzeitige Einschulung')
                                    ->styleBorderBottom(), '90%'
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('fristgem. Einschulung (gem. §27 Abs. 1 SchulG)')
                                    ->styleBorderBottom(), '90%'
                                )
                            )
                            ->addSection((new Section())
                                ->addSliceColumn((new Slice())
                                    ->styleBorderBottom()
                                    ->addSection((new Section())
                                        ->addElementColumn((new Element())
                                            ->setContent('davon')
                                            ->styleAlignCenter()
                                            ->styleBorderRight()
                                            ->stylePaddingTop('9.1px')
                                            ->stylePaddingBottom('9px'), '15%'
                                        )
                                        ->addSliceColumn((new Slice())
                                            ->addSection((new Section())
                                                ->addElementColumn((new Element())
                                                    ->setContent('schulpflichtig geworden bis zum 30.08.2015')
                                                    ->styleBorderBottom()
                                                )
                                            )
                                            ->addSection((new Section())
                                                ->addElementColumn((new Element())
                                                    ->setContent('schulpflichtig geworden vom 1.07. bis zum 30.09.2015')
                                                )
                                            )
                                        )
                                    )
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Einschulung nach Zurückstellung'), '70%'
                                )
                            )
                        )
                    ), '70%'
                )
                //TODO: Zellen füllen
                ->addSliceColumn((new Slice())
                    ->styleAlignCenter()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBackgroundColor('lightgrey')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBackgroundColor('lightgrey')
                            ->styleBorderBottom(), '50%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom(), '50%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom(), '50%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBackgroundColor('lightgrey')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBackgroundColor('lightgrey')
                            ->styleBorderBottom(), '50%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom(), '50%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom(), '50%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderRight(), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00'), '50%'
                        )
                    ), '30%'
                )
            );

        return $sliceList;
    }
}