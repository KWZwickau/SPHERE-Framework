<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESBD;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;

/**
 * Class EsbdStyle
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESBD
 */
abstract class EsbdStyle extends Certificate
{

    const TEXT_SIZE = '10pt';

    /**
     * @param string $SchoolName
     * @param string $with
     * @param string $height
     *
     * @return Slice
     */
    protected function getEsbdHeadSlice($SchoolName = '', $with = 'auto', $height = '57px')
    {

        $ShowRightLogo = true;
        if(strpos($SchoolName, 'Gymnasium') || strpos($SchoolName, 'Oberschule')){
            $ShowRightLogo = false;
        }

        if($ShowRightLogo){
            $Header = (new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element()), '1%')
                    ->addElementColumn((new Element\Image('Common/Style/Resource/Logo/ESBD_Zeugnis.jpg', $with, $height))
                        , '74%')
                    ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                        'auto', '52px'))
                        ->styleMarginTop('2px')
                        ->styleAlignRight()
                        , '24%')
                    ->addElementColumn((new Element()), '1%')
                );
        } else {
            $Header = (new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element()), '1%')
                    ->addElementColumn((new Element\Image('Common/Style/Resource/Logo/ESBD_Zeugnis.jpg', $with, $height))
                        , '74%')
                    ->addElementColumn((new Element()), '25%')
                );
        }


        $Header->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Name der Schule:')
                ->styleMarginTop('20px')
            , '18%')
            ->addElementColumn((new Element())
                ->setContent($SchoolName)
                ->styleBorderBottom()
                ->styleAlignCenter()
                ->styleMarginTop('20px')
            , '64%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleBorderBottom()
                ->styleMarginTop('20px')
            , '18%')
        );

        if ($this->isSample()) {
            $Header->addElement((new Element\Sample())
                ->styleTextSize('30px')
                ->styleAlignRight()
                ->styleHeight('0px')
                ->styleMarginTop('10px')
            );
        } else {
            $Header->addElement((new Element())
                ->styleHeight('30px')
            );
        }
        return $Header;
    }

    /**
     * @param string $Content
     *
     * @return Element
     */
    public function getEsbdBottomLine()
    {

        $Element = new Element();
        return $Element->styleBorderBottom('5px', '#29948E')
            ->styleMarginTop('15px');
    }

    /**o
     * @param $personId
     *
     * @return Slice
     */
    public function getEsbdCourse($personId)
    {

        $Slice = (new Slice())
            ->addElement((new Element())
                ->setContent('
                {% if(Content.P' . $personId . '.Student.Course.Degree is not empty) %}
                    nahm am Unterricht mit dem Ziel des
                    {{ Content.P' . $personId . '.Student.Course.Degree }} teil.
                {% else %}
                    &nbsp;
                {% endif %}'
                )
                ->styleMarginTop('5px')
                ->styleHeight('18px')
            );
        return $Slice;
    }

}