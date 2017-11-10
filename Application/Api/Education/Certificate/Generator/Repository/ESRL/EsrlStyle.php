<?php
/**
 * Created by PhpStorm.
 * User: rackel
 * Date: 10.11.2017
 * Time: 10:42
 */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESRL;


use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;

abstract class EsrlStyle extends Certificate
{

    const TEXT_SIZE = '12pt';

    /**
     * @param string $PictureHeight
     *
     * @return Slice
     */
    public function getESRLHead($PictureHeight = '160px')
    {

        $PictureSection = (new Section())
            ->addElementColumn((new Element\Image('Common/Style/Resource/Logo/ESRL_Zeugnis_Logo.jpg',
                'auto', $PictureHeight))
                , '50%')
//                    ->addElementColumn((new Element()), '25%')
            ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                '180px', '50px'))
                ->styleMarginTop('4px')
                ->styleAlignRight()
                , '50%');

        if ($this->isSample()) {
            $Header = (new Slice())
                ->addSection(
                    $PictureSection
                )
                // display "Sample" over picture
                ->addSection((new Section())
                    ->addElementColumn((new Element\Sample())
                        ->styleTextSize('30px')
                        ->stylePaddingTop('-'.$PictureHeight)
                    )
                );
        } else {
            $Header = (new Slice())
                ->addSection(
                    $PictureSection
                );
        }
        return $Header;
    }

    /**
     * @param string $Content
     *
     * @return Section
     */
    public function getESRLHeadLine($Content = '')
    {

        $Section = new Section();
        return $Section->addElementColumn((new Element())
            ->setContent($Content)
            ->styleTextSize('27px')
            ->styleTextBold()
            ->styleAlignCenter()
            ->styleMarginTop('2px')
        );
    }

    /**
     * @param $personId
     *
     * @return Section
     */
    public function getESRLDivisionAndYear($personId)
    {

        $Section = (new Section());
        $Section->addElementColumn((new Element())
            ->setContent('Klasse:')
            ->styleTextSize(self::TEXT_SIZE)
            , '10%')
            ->addElementColumn((new Element())
                ->setContent('{{ Content.P'.$personId.'.Division.Data.Level.Name }}{{ Content.P'.$personId.'.Division.Data.Name }}')
                ->styleTextSize(self::TEXT_SIZE)
                ->styleBorderBottom('1px', '#999')
                ->styleAlignCenter()
                , '8%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleTextSize(self::TEXT_SIZE)
                ->styleBorderBottom('1px', '#999')
                , '52%')
            ->addElementColumn((new Element())
                ->setContent('Schuljahr: &nbsp;&nbsp;')
                ->styleTextSize(self::TEXT_SIZE)
                ->styleAlignRight()
                , '15%')
            ->addElementColumn((new Element())
                ->setContent('{{ Content.P'.$personId.'.Division.Data.Year }}')
                ->styleTextSize(self::TEXT_SIZE)
                ->styleBorderBottom('1px', '#999')
                ->styleAlignCenter()
                , '17%');
        return $Section;
    }
}