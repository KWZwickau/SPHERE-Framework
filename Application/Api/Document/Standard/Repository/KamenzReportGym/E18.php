<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 27.06.2017
 * Time: 15:28
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

class E18
{
    public static function getContent()
    {
        $sliceList = array();


        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('E18. Schüler in Leistungskursen im Schuljahr {{ Content.Schoolyear.Current }} nach Jahrgangsstufen')
            );

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleAlignCenter()
            ->styleBorderAll()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Fächerkombination')
                    ->styleBorderRight()
                    ->stylePaddingTop('13.2')
                    ->stylePaddingBottom('13.1'), '60%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Schüler in der Jahrgangsstufe')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('11')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->styleBorderBottom()
                            ->setContent('12'), '50%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w'), '25%'
                        )
                    ), '40%'
                )
            );

        for ($i = 0; $i < 10; $i++) {
            $sliceList[] = (new Slice())
                ->styleAlignCenter()
                ->styleBorderBottom()
                ->styleBorderLeft()
                ->styleBorderRight()
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Fach')
                        ->styleBorderRight(), '15%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Fach')
                        ->styleBorderRight(), '15%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Fach')
                        ->styleBorderRight(), '15%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Fach')
                        ->styleBorderRight(), '15%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('00')
                        ->styleBorderRight(), '10%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('00')
                        ->styleBorderRight(), '10%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('00')
                        ->styleBorderRight(), '10%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('00'), '10%'
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
                    ->styleBorderRight(), '60%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;'), '10%'
                )
            );

        return $sliceList;
    }
}