<?php
namespace SPHERE\Application\Api\Document\Custom\Radebeul\Repository;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class AuthorizedToCollect
 *
 * @package SPHERE\Application\Api\Document\Custom\Radebeul\Repository#
 */
class AuthorizedToCollect extends AbstractDocument
{

    const TEXT_SIZE = '16px';

    /**
     * @return string
     */
    public function getName()
    {

        return 'Abholvollmacht';
    }

    /**
     * @param array  $pageList
     * @param string $part
     *
     * @return Frame
     */
    public function buildDocument(array $pageList = array(), string $Part = '0'): Frame
    {

        return (new Frame())->addDocument((new Document())
            ->addPage($this->buildPageOne())
        );
    }

    public function buildPageOne()
    {

        $textSize = '16px';
        $fontFamily = 'MetaPro';
        $PaddingBottom = '10px';
        $PaddingBottomLarge = '46px';

        return (new Page())
        ->addSlice((new Slice())
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    , '20%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
//                            ->setContent('Evangelisches Schulzentrum Radebeul')
                    ->styleFontFamily($fontFamily)
                    ->styleTextBold()
                    ->stylePaddingTop('50px')
                    ->styleAlignCenter()
                    ->styleTextSize('23px')
                    , '60%'
                )
                ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/EVSRDokument.jpg',
                    '301px', '90px'))
                    ->stylePaddingTop('10px')
                    ->styleHeight('10px')
                    , '20%'
                )
            )
            ->addElement((new Element())
                ->setContent('Abholvollmacht')
                ->styleFontFamily($fontFamily)
                ->styleTextBold()
                ->stylePaddingTop()
                ->styleAlignCenter()
                ->styleTextSize('23px')
                ->styleHeight('100px')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Name:')
                    ->styleFontFamily($fontFamily)
                    ->styleTextBold()
                    ->styleTextSize($textSize)
                    ->stylePaddingBottom($PaddingBottom)
                    , '15%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                                {% if( Content.Person.Data.Name.Last is not empty) %}
                                    {{ Content.Person.Data.Name.Last }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                    ->styleFontFamily($fontFamily)
                    ->styleTextSize($textSize)
                    , '85%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Vorname:')
                    ->styleFontFamily($fontFamily)
                    ->styleTextBold()
                    ->styleTextSize($textSize)
                    ->stylePaddingBottom($PaddingBottom)
                    , '15%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                                {% if( Content.Person.Data.Name.First is not empty) %}
                                    {{ Content.Person.Data.Name.First }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                    ->styleFontFamily($fontFamily)
                    ->styleTextSize($textSize)
                    , '85%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('geboren am:')
                    ->styleFontFamily($fontFamily)
                    ->styleTextBold()
                    ->styleTextSize($textSize)
                    ->stylePaddingBottom($PaddingBottom)
                    , '15%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                                {% if(Content.Person.Common.BirthDates.Birthday is not empty) %}
                                    {{ Content.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                    ->styleFontFamily($fontFamily)
                    ->styleTextSize($textSize)
                    , '85%'
                )
            )
            ->addElement((new Element())
                ->setContent('Es sind folgende Personen berechtigt das oben genannte Kind aus der Einrichtung abzuholen:')
                ->styleFontFamily($fontFamily)
                ->stylePaddingTop('30px')
                ->styleTextSize($textSize)
            )
            ->addElement((new Element())
                ->setContent('{% if(Content.Person.Common.AuthorizedToCollect is not empty) %}
                        {{ Content.Person.Common.AuthorizedToCollect|nl2br }}
                    {% else %}
                        &nbsp;
                    {% endif %}')
                ->styleFontFamily($fontFamily)
                ->stylePaddingTop('70px')
                ->styleHeight('300px')
                ->styleTextSize($textSize)
            )
            ->addElement((new Element())
                ->setContent('Datum:')
                ->styleFontFamily($fontFamily)
                ->styleTextSize($textSize)
                ->styleTextBold()
                ->stylePaddingBottom($PaddingBottomLarge)
            )
            ->addElement((new Element())
                ->setContent('Unterschrift aller Sorgeberechtigter')
                ->styleFontFamily($fontFamily)
                ->styleTextSize($textSize)
                ->styleTextBold()
                ->stylePaddingBottom($PaddingBottomLarge)
            )
            ->addElement((new Element())
                ->setContent('&nbsp;')
                ->styleBorderBottom('1px', '#000', 'dotted')
                ->styleTextBold()
            )
            ->stylePaddingLeft('30px')
            ->stylePaddingRight('30px')
        );
    }
}