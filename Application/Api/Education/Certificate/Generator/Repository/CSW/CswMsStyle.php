<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\CSW;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;

/**
 * Class CswMsStyle
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\CSW
 */
class CswMsStyle
{
    /**
     * @param $isSample
     *
     * @return Slice
     */
    public static function getHeader($isSample)
    {
        $height = '66px';
        $width = '214px';

        $slice = new Slice();
        $section = new Section();

        // Individually Logo
        $section->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/CSW.jpg', 'auto', $height)), '39%');

        // Sample
        if($isSample){
            $section->addElementColumn((new Element\Sample())->styleTextSize('30px'));
        } else {
            $section->addElementColumn((new Element()), '22%');
        }

        // Standard Logo
        $section->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
            $width, $height))
            ->styleAlignRight()
            , '39%');

        $slice->stylePaddingTop('24px');
        $slice->styleHeight('100px');
        $slice->addSection($section);

        return $slice;
    }

    /**
     * @param $personId
     *
     * @return Slice
     */
    public static function getIndividualSchoolLine($personId)
    {

        $slice = new Slice();
        $slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Name der Schule:')
                , '18%')
            ->addElementColumn((new Element())
                ->setContent('
                        {% if(Content.P'.$personId.'.Company.Data.Name) %}
                            <strong> {{ Content.P'.$personId.'.Company.Data.Name }} </strong>
                            {% if(Content.P'.$personId.'.Company.Data.ExtendedName) %}
                                <br>
                                - {{ Content.P'.$personId.'.Company.Data.ExtendedName }} -
                            {% else %}
                                &nbsp;
                            {% endif %}
                        {% else %}
                              &nbsp;
                        {% endif %}    
                    ')
                ->styleAlignCenter()
            )
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                , '18%')
        )->styleMarginTop('5px');

        $slice->addSection((new Section())
            ->addElementColumn((new Element())
                , '18%')
            ->addElementColumn((new Element())
                ->styleBorderBottom()
                ->styleMarginTop('2px')
            )
        );

        return $slice;
    }
}