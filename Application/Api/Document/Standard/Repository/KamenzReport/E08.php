<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 23.06.2017
 * Time: 14:43
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReport;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

class E08
{
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('E08. Wiederholer im Schuljahr {{ Content.SchoolYear.Current }} nach Klassenstufen')
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
                    ->setContent('Merkmal')
                    ->styleBorderRight()
                    ->stylePaddingTop('17.6px')
                    ->stylePaddingBottom('17.6px'), '30%'
                )
                ->addSliceColumn((new Slice())
                    ->styleBorderRight()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klassenstufe'), '100%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('5')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.67%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('6')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.66%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('7')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.67%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('8')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.67%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('9')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.66%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('10')
                            ->styleBorderBottom(), '16.67%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '8.34%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '8.33%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '8.33%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '8.34%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '8.33%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '8.33%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '8.33%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '8.33%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '8.34%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '8.33%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '8.33%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w'), '8.34%'
                        )
                    ), '60%'
                )
                ->addSliceColumn((new Slice())
                    ->styleTextBold()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Insgesamt')
                            ->styleBorderBottom()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '100%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w'), '50%'
                        )
                    ), '10%'
                )
            );

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Schüler')
                    ->styleBorderRight(), '30%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;'), '5%'
                )
            );

        $sliceList[] = (new Slice())
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('davon mit<br/>dem Ziel')
                    ->styleBackgroundColor('lightgrey')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->stylePaddingTop('18.1px')
                    ->stylePaddingBottom('18.1px'), '10%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Hauptschulabschluss')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '22.22%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('00')
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('00')
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('00')
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('00')
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('00')
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('00')
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleBorderBottom(), '5.55%'
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Realschulabschluss')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '22.22%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('00')
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('00')
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('00')
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('00')
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('00')
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('00')
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('00')
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('00')
                                    ->styleAlignCenter()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleAlignCenter()
                                    ->styleTextBold()
                                    ->styleBorderBottom()
                                    ->styleBorderRight(), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleAlignCenter()
                                    ->styleTextBold()
                                    ->styleBorderBottom(), '5.55%'
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('ohne abschlussbez.<br/>Unterricht')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleBorderRight(), '22.22%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('00')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->stylePaddingTop('8.6px')
                                    ->stylePaddingBottom('8.5px'), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('00')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->stylePaddingTop('8.6px')
                                    ->stylePaddingBottom('8.5px'), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('00')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->stylePaddingTop('8.6px')
                                    ->stylePaddingBottom('8.5px'), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('00')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->stylePaddingTop('8.6px')
                                    ->stylePaddingBottom('8.5px'), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('00')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->stylePaddingTop('8.6px')
                                    ->stylePaddingBottom('8.5px'), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('00')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->stylePaddingTop('8.6px')
                                    ->stylePaddingBottom('8.5px'), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('00')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->stylePaddingTop('8.6px')
                                    ->stylePaddingBottom('8.5px'), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('00')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->stylePaddingTop('8.6px')
                                    ->stylePaddingBottom('8.5px'), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('00')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->stylePaddingTop('8.6px')
                                    ->stylePaddingBottom('8.5px'), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('00')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->stylePaddingTop('8.6px')
                                    ->stylePaddingBottom('8.5px'), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('00')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->stylePaddingTop('8.6px')
                                    ->stylePaddingBottom('8.5px'), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('00')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->stylePaddingTop('8.6px')
                                    ->stylePaddingBottom('8.5px'), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->styleBackgroundColor('lightgrey')
                                    ->styleBorderRight()
                                    ->stylePaddingTop('8.6px')
                                    ->stylePaddingBottom('8.5px'), '5.55%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->styleBackgroundColor('lightgrey')
                                    ->stylePaddingTop('8.6px')
                                    ->stylePaddingBottom('8.5px'), '5.55%'
                                )
                            )
                        )
                    ), '90%'
                )
            );


        return $sliceList;
    }
}