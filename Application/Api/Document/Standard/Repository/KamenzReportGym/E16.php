<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 27.06.2017
 * Time: 12:58
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

class E16
{
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('E16. Schüler in Grundkursen an dieser Schule im Schuljahr {{ Content.Schoolyear.Current }} nach Jahrgangsstufen')
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
                    ->setContent('Deutsch')
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
                    ->setContent('Deutsch als Fremdsprache')
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
                    ->setContent('Sorbisch')
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
                    ->setContent('Englisch')
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
                    ->setContent('Französisch')
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
                    ->setContent('Latein')
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
                    ->setContent('Griechisch')
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
                    ->setContent('Russisch')
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
                    ->setContent('Spanisch')
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
                    ->setContent('Italienisch')
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
                    ->setContent('Polnisch')
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
                    ->setContent('Tschechisch')
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
                    ->setContent('Kunst')
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
                    ->setContent('Musik')
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
                    ->setContent('Geschichte')
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
                    ->setContent('Geschichte bikulturell-bilingual')
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
                    ->setContent('Geographie')
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
                    ->setContent('Gemeinschaftskunde/Rechtserz./Wirtschaft')
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
                    ->setContent('Mathematik')
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
                    ->setContent('Physik')
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
                    ->setContent('Chemie')
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
                    ->setContent('Biologie')
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
                    ->setContent('Sport')
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
                    ->setContent('Ethik')
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
                    ->setContent('Evangelische Religion')
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
                    ->setContent('Katholische Religion')
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
                    ->setContent('sonstige Fächer')
                    ->styleBorderRight(), '40%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '12%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '9%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '9%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '12%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '9%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;'), '9%'
                )
            );

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleAlignCenter()
            ->styleTextBold()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Insgesamt')
                    ->styleBorderRight(), '40%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '12%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '9%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '9%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '12%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '9%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;'), '9%'
                )
            );


        return $sliceList;
    }
}