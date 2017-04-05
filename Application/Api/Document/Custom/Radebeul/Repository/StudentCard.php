<?php

namespace SPHERE\Application\Api\Document\Custom\Radebeul\Repository;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

class StudentCard extends AbstractDocument
{

    const TEXT_SIZE = '16px';

    /**
     * @return string
     */
    public function getName()
    {

        return 'Schülerbogen';
    }

    /**
     * @param array $pageList
     *
     * @return Frame
     */
    public function buildDocument($pageList = array())
    {
        $textSize = '16px';
        $fontFamily = 'MetaPro';
        $PaddingBottom = '18px';

        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Evangelische Grundschule Radebeul Staatlich genehmigt')
                        ->styleFontFamily($fontFamily)
                        ->stylePaddingTop('20px')
                        ->styleAlignCenter()
                        ->styleTextSize('23px')
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Schülerbogen')
                        ->styleFontFamily($fontFamily)
                        ->stylePaddingTop()
                        ->styleAlignCenter()
                        ->styleTextSize('23px')
                        ->styleHeight('100px')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Name:')
                            ->styleFontFamily($fontFamily)
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
                            ->styleBorderBottom('1px', '#000', 'dotted')
                            , '30%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            , '55%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Vorname:')
                            ->styleFontFamily($fontFamily)
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
                            ->styleBorderBottom('1px', '#000', 'dotted')
                            , '30%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            , '55%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('geboren am:')
                            ->styleFontFamily($fontFamily)
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
                            ->styleAlignCenter()
                            ->styleTextSize($textSize)
                            ->styleBorderBottom('1px', '#000', 'dotted')
                            , '13%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('in:&nbsp;')
                            ->styleAlignRight()
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            , '4%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Person.Common.BirthDates.Birthplace is not empty) %}
                                    {{ Content.Person.Common.BirthDates.Birthplace }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleBorderBottom('1px', '#000', 'dotted')
                            , '23%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            , '45%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Anschrift:')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->stylePaddingBottom($PaddingBottom)
                            , '15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Person.Address.Street.Name) %}
                                    {{ Content.Person.Address.Street.Name }}
                                    {% if(Content.Person.Address.Street.Number) %}
                                        {{ Content.Person.Address.Street.Number }}
                                    {% endif %}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleBorderBottom('1px', '#000', 'dotted')
                            , '40%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            , '45%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->stylePaddingBottom('26px')
                            , '15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Person.Address.City.Name) %}
                                    {{ Content.Person.Address.City.Code }}
                                    {{ Content.Person.Address.City.Name }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleBorderBottom('1px', '#000', 'dotted')
                            , '40%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            , '45%'
                        )
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Mail-Adresse für Elternbriefe:')
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize($textSize)
                    )
                    ->addElement((new Element())
                        ->setContent('
                            {% if(Content.Person.Contact.All.Mail) %}
                                {{ Content.Person.Contact.All.Mail }}
                            {% else %}
                                  &nbsp;
                            {% endif %}
                        ')
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize($textSize)
                        ->styleBorderBottom('1px', '#000', 'dotted')
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Telefonnummer')
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize($textSize)
                        ->stylePaddingTop($PaddingBottom)
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Sorgeb. 1')
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize('11px')
                    )
                    ->addElement((new Element())
                        ->setContent('
                                {% if(Content.Person.Parent.Mother.Contact.Phone) %}
                                    {{ Content.Person.Parent.Mother.Contact.Phone }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize($textSize)
                        ->styleBorderBottom('1px', '#000', 'dotted')
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Sorgeb. 2')
                        ->styleFontFamily($fontFamily)
                        ->styleMarginTop($PaddingBottom)
                        ->styleTextSize('11px')
                    )
                    ->addElement((new Element())
                        ->setContent('
                                {% if(Content.Person.Parent.Father.Contact.Phone) %}
                                    {{ Content.Person.Parent.Father.Contact.Phone }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize($textSize)
                        ->styleBorderBottom('1px', '#000', 'dotted')
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('&nbsp;')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Besonderheiten / <br/> Wichtiges:')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            , '20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            , '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Student.MedicalRecord.Disease) %}
                                    {{ Content.Student.MedicalRecord.Disease }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleMarginTop('10px')
                            ->stylePaddingLeft('10px')
                            ->stylePaddingRight('10px')
                            ->styleHeight('143px')
                            ->styleBorderAll('1px', '#000', 'dotted')
                            , '65%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            , '10%'
                        )
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('&nbsp;')
                        ->styleHeight('75px')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Document.PlaceDate) %}
                                    {{ Content.Document.PlaceDate }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleBorderBottom('1px', '#000', 'dotted')
                            ->styleAlignCenter()
                            , '30%'
                        )
                        ->addElementColumn((new Element())
                            , '40%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleBorderBottom('1px', '#000', 'dotted')
                            , '30%'
                        )
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Ort, Datum')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleAlignCenter()
                            , '30%'
                        )
                        ->addElementColumn((new Element())
                            , '40%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Unterschrift')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleAlignCenter()
                            , '30%'
                        )
                    )
                )
            )
        );
    }
}