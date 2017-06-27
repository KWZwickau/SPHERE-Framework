<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 27.06.2017
 * Time: 11:46
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

class E21
{
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('E21. Schüler aus dem Ausland mit individuellem Schulbesuch im Schuljahr {{ Content.Schoolyear.Current }}')
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
                    ->setContent('Besuch der Klassen-<br/> bzw. Jahrgangsstufe...<br/>im Schuljahr {{ Content.Schoolyear.Current }}')
                    ->styleBorderRight(), '30%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Land des Staatsangehörigkeit')
                    ->styleBorderRight()
                    ->stylePaddingTop('17.1px')
                    ->stylePaddingBottom('17.1px'), '35%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Dauer des<br/>Aufenthaltes<br/>in Monaten')
                    ->styleBorderRight(), '15%'
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
                            ->stylePaddingTop('8.1px')
                            ->stylePaddingBottom('8px'), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->stylePaddingTop('8.1px')
                            ->stylePaddingBottom('8px'), '50%'
                        )
                    ), '20%'
                )
            );

        for ($i = 0; $i < 8; $i++) {
            $sliceList[] = (new Slice())
                ->styleAlignCenter()
                ->styleBorderBottom()
                ->styleBorderLeft()
                ->styleBorderRight()
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Klassenstufe')
                        ->styleBorderRight(), '30%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Staatsangehörigkeit')
                        ->styleBorderRight(), '35%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Dauer')
                        ->styleBorderRight(), '15%'
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
                    ->styleBorderRight(), '80%'
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