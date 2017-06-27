<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 27.06.2017
 * Time: 13:56
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

class E16_01
{
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('E16.1 Schüler in Grundkursen gemäß § 41 Abs. 1 SOGYA bzw. in fächerverbindenden Grundkursen im Schuljahr {{ Content.Schoolyear.Current }} nach Jahrgangsstufen')
            );

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleAlignCenter()
            ->styleBorderAll()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Fach')
                    ->styleBorderRight()
                    ->stylePaddingTop('34.1px')
                    ->stylePaddingBottom('34.1px'), '40%'
                )
                ->addSliceColumn((new Slice())
                    ->styleBorderRight()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Jahrgangsstufe 11')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Anzahl der<br/>eingerich-<br/>teten<br/>Grundkurse')
                            ->styleBorderRight(), '40%'
                        )
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Schüler')
                                    ->styleBorderBottom()
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('m')
                                    ->styleBorderRight()
                                    ->stylePaddingTop('16.5px')
                                    ->stylePaddingBottom('16.5px'), '50%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('w')
                                    ->stylePaddingTop('16.5px')
                                    ->stylePaddingBottom('16.5px'), '50%'
                                )
                            ), '60%'
                        )
                    ), '30%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Jahrgangsstufe 12')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Anzahl der<br/>eingerich-<br/>teten<br/>Grundkurse')
                            ->styleBorderRight(), '40%'
                        )
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Schüler')
                                    ->styleBorderBottom()
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('m')
                                    ->styleBorderRight()
                                    ->stylePaddingTop('16.5px')
                                    ->stylePaddingBottom('16.5px'), '50%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('w')
                                    ->stylePaddingTop('16.5px')
                                    ->stylePaddingBottom('16.5px'), '50%'
                                )
                            ), '60%'
                        )
                    ), '30%'
                )
            );

        $sliceList[] = (new Slice())
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Informatik')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '40%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '12%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '9%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '9%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '12%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '9%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter(), '9%'
                )
            );

        $sliceList[] = (new Slice())
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Astronomie')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '40%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '12%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '9%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '9%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '12%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '9%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter(), '9%'
                )
            );

        $sliceList[] = (new Slice())
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Philosophie')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '40%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '12%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '9%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '9%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '12%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '9%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter(), '9%'
                )
            );

        $sliceList[] = (new Slice())
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Fort-<br/>geführte<br/>Fremd-<br/>sprache')
                    ->styleBackgroundColor('lightgrey')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->stylePaddingTop('46.7px')
                    ->stylePaddingBottom('46.7px'),'15%'
                )
                ->addSliceColumn((new Slice())
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Englisch')
                            ->styleBorderBottom()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Französisch')
                            ->styleBorderBottom()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Latein')
                            ->styleBorderBottom()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Griechisch')
                            ->styleBorderBottom()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Russisch')
                            ->styleBorderBottom()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Spanisch')
                            ->styleBorderBottom()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Italienisch')
                            ->styleBorderBottom()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Polnisch')
                            ->styleBorderBottom()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Tschechisch')
                        )
                    ), '25%'
                )
                ->addSliceColumn((new Slice())
                    ->styleAlignCenter()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom(),'15%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom(),'15%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom(),'15%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom(),'15%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom(),'15%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom(),'15%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom(),'15%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(),'15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom(),'15%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderRight(),'20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderRight(),'15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderRight(),'15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderRight(),'20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderRight(),'15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00'),'15%'
                        )
                    )
                )
            );

        $sliceList[] = (new Slice())
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Fächerverbindende Grundkurs')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '40%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '12%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '9%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '9%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '12%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '9%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter(), '9%'
                )
            );

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Sonstige Grundkurse')
                    ->styleBorderRight(), '40%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '12%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '9%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '9%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '12%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '9%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter(), '9%'
                )
            );

        return $sliceList;
    }

}