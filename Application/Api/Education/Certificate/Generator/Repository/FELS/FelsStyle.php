<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\FELS;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;

/**
 * Class FelsStyle
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\FELS
 */
class FelsStyle
{
    /**
     * @param $IsSample
     *
     * @return Slice
     */
    public static function getHeader($IsSample)
    {
        $section = new Section();
        $section->addElementColumn((new Element()), '39%');
        // Sample
        if($IsSample){
            $section->addElementColumn((new Element\Sample())->styleTextSize('30px'));
        } else {
            $section->addElementColumn((new Element()), '22%');
        }
        $section
            ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/FELS.jpg', 'auto', '66px'))->styleAlignRight(), '39%');

        return
            (new Slice())
                ->addSection($section)
                ->stylePaddingTop('24px')
                ->styleHeight('100px');
    }
}