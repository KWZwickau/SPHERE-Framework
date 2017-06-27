<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 27.06.2017
 * Time: 11:16
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

class E12
{
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('E12. Schüler und Profilgruppen in weiteren Profilen im Schuljahr {{ Content.Schoolyear.Current }} nach Klassenstufen')
            );

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleAlignCenter()
            ->styleBorderAll()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Merkmal')
                    ->styleBorderRight()
                    ->stylePaddingTop('17.2px')
                    ->stylePaddingBottom('17.1px'), '40%'
                )
                ->addSliceColumn((new Slice())
                    ->styleBorderRight()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klassenstufe')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('8')
                            ->styleBorderRight(), '33.33%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('9')
                            ->styleBorderRight(), '33.33%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('10'), '33.34%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '16.66%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '16.67%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '16.66%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '16.67%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '16.67%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w'), '16.67%'
                        )
                    ), '45%'
                )
                ->addSliceColumn((new Slice())
                    ->styleTextBold()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Insgesamt')
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
                    ), '15%'
                )
            );

        $sliceList[] = (new Slice())
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Gesellschaftswissenschaftl.<br/>Profil mit informat. Bildung')
                    ->styleBorderRight()
                    ->stylePaddingTop('1px'), '27.5%'
                )
                ->addSliceColumn((new Slice())
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Schüler')
                            ->styleBorderBottom()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Profilgruppen')
                        )
                    ), '12.5%'
                )
                ->addSliceColumn((new Slice())
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.66%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.67%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.66%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.67%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.66%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom(), '16.67%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderRight(), '33.33%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderRight(), '33.33%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00'), '33.34%'
                        )
                    ), '45%'
                )
                ->addSliceColumn((new Slice())
                    ->styleBackgroundColor('lightgrey')
                    ->styleTextBold()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom(), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom(), '50%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;'), '100%'
                        )
                    ), '15%'
                )
            );

        $sliceList[] = (new Slice())
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Künstlerisches Profil mit<br/>informatischer Bildung')
                    ->styleBorderRight()
                    ->stylePaddingTop('1px'), '27.5%'
                )
                ->addSliceColumn((new Slice())
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Schüler')
                            ->styleBorderBottom()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Profilgruppen')
                        )
                    ), '12.5%'
                )
                ->addSliceColumn((new Slice())
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.66%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.67%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.66%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.67%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.66%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom(), '16.67%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderRight(), '33.33%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderRight(), '33.33%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00'), '33.34%'
                        )
                    ), '45%'
                )
                ->addSliceColumn((new Slice())
                    ->styleBackgroundColor('lightgrey')
                    ->styleTextBold()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom(), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom(), '50%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;'), '100%'
                        )
                    ), '15%'
                )
            );

        $sliceList[] = (new Slice())
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Naturwissenschaftliches Profil<br/>mit informatischer Bildung')
                    ->styleBorderRight()
                    ->stylePaddingTop('1px'), '27.5%'
                )
                ->addSliceColumn((new Slice())
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Schüler')
                            ->styleBorderBottom()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Profilgruppen')
                        )
                    ), '12.5%'
                )
                ->addSliceColumn((new Slice())
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.66%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.67%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.66%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.67%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.66%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom(), '16.67%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderRight(), '33.33%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderRight(), '33.33%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00'), '33.34%'
                        )
                    ), '45%'
                )
                ->addSliceColumn((new Slice())
                    ->styleBackgroundColor('lightgrey')
                    ->styleTextBold()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom(), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom(), '50%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;'), '100%'
                        )
                    ), '15%'
                )
            );

        $sliceList[] = (new Slice())
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Sportliches Profil mit<br/>informatischer Bildung')
                    ->styleBorderRight()
                    ->stylePaddingTop('1px'), '27.5%'
                )
                ->addSliceColumn((new Slice())
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Schüler')
                            ->styleBorderBottom()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Profilgruppen')
                        )
                    ), '12.5%'
                )
                ->addSliceColumn((new Slice())
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.66%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.67%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.66%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.67%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.66%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom(), '16.67%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderRight(), '33.33%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderRight(), '33.33%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00'), '33.34%'
                        )
                    ), '45%'
                )
                ->addSliceColumn((new Slice())
                    ->styleBackgroundColor('lightgrey')
                    ->styleTextBold()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom(), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom(), '50%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;'), '100%'
                        )
                    ), '15%'
                )
            );

        $sliceList[] = (new Slice())
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Sonstige Profile')
                    ->styleBorderRight()
                    ->stylePaddingTop('9.2px')
                    ->stylePaddingBottom('9.1px'), '27.5%'
                )
                ->addSliceColumn((new Slice())
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Schüler')
                            ->styleBorderBottom()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Profilgruppen')
                        )
                    ), '12.5%'
                )
                ->addSliceColumn((new Slice())
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.66%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.67%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.66%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.67%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '16.66%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderBottom(), '16.67%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderRight(), '33.33%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00')
                            ->styleBorderRight(), '33.33%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('00'), '33.34%'
                        )
                    ), '45%'
                )
                ->addSliceColumn((new Slice())
                    ->styleBackgroundColor('lightgrey')
                    ->styleTextBold()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom(), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom(), '50%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;'), '100%'
                        )
                    ), '15%'
                )
            );

        return $sliceList;
    }
}