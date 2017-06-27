<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 27.06.2017
 * Time: 09:34
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

class E03
{
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('E03. <u>Schüler mit Migrationshintergund</u> Schuljahr {{ Content.Schoolyear.Current }} nach dem Land der Staatsangehörigkeit und Klassen- bzw. Jahrgangsstufen')
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
                    ->setContent('Land der<br/>Staatsan-<br/>gehörigkeit')
                    ->styleBorderRight()
                    ->stylePaddingTop('1px'), '10%'
                )
                ->addSliceColumn((new Slice())
                    ->styleBorderRight()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klassen- bzw. Jahrgangsstufe')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('5')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('6')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('7')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('8')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('9')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('10')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('11')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('12')
                            ->styleBorderBottom(), '10%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w'), '5%'
                        )
                    ), '80%'
                )
                ->addSliceColumn((new Slice())
                    ->styleTextBold()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Insgesamt')
                            ->styleBorderBottom()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px')
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

        for ($i = 1; $i < 5; $i++) {
            $sliceList[] = (new Slice())
                ->styleBorderBottom()
                ->styleBorderLeft()
                ->styleBorderRight()
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Land')
                        ->styleBorderRight(), '10%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('00')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('00')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('00')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('00')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('00')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('00')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('00')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('00')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('00')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('00')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('00')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('00')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('00')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('00')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('00')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('00')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleBackgroundColor('lightgrey')
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleBackgroundColor('lightgrey'), '5%'
                    )
                );
        }

        $sliceList[] = (new Slice())
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Insgesamt')
                    ->styleTextBold()
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey'), '5%'
                )
            );

        return $sliceList;
    }
}