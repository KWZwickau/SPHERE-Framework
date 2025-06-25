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
 * Class StudentCard
 *
 * @package SPHERE\Application\Api\Document\Custom\Radebeul\Repository#
 */
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
     * @param array  $pageList
     * @param string $part
     *
     * @return Frame
     */
    public function buildDocument(array $pageList = array(), string $Part = '0'): Frame
    {

        return (new Frame())->addDocument((new Document())
            ->addPage($this->buildPageOne())
            ->addPage($this->buildPageTwo())
        );
    }

    public function buildPageOne()
    {

        $textSize = '16px';
        $fontFamily = 'MetaPro';
        $PaddingBottom = '18px';

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
                ->setContent('Schülerbogen')
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
//                        ->addElementColumn((new Element())
//                            ->setContent('in:&nbsp;')
//                            ->styleFontFamily($fontFamily)
//                            ->styleTextBold()
//                            ->styleAlignRight()
//                            ->styleTextSize($textSize)
//                            , '4%'
//                        )
//                        ->addElementColumn((new Element())
//                            ->setContent('
//                                {% if(Content.Person.Common.BirthDates.Birthplace is not empty) %}
//                                    {{ Content.Person.Common.BirthDates.Birthplace }}
//                                {% else %}
//                                    &nbsp;
//                                {% endif %}
//                            ')
//                            ->styleFontFamily($fontFamily)
//                            ->styleTextSize($textSize)
//                            ->styleBorderBottom('1px', '#000', 'dotted')
//                            , '23%'
//                        )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Anschrift:')
                    ->styleFontFamily($fontFamily)
                    ->styleTextBold()
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
                    , '85%'
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
                    , '85%'
                )
            )
            ->addElement((new Element())
                ->setContent('Sorgeberechtigter:
                        {% if(Content.Person.Parent.S1.Name.First) %}
                                {{ Content.Person.Parent.S1.Name.First }}
                            {% else %}
                                  &nbsp;
                            {% endif %}
                            {% if(Content.Person.Parent.S1.Name.Last) %}
                                {{ Content.Person.Parent.S1.Name.Last }}
                            {% else %}
                                  &nbsp;
                            {% endif %}')
                ->styleFontFamily($fontFamily)
                ->styleTextBold()
                ->styleTextSize($textSize)
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Telefonnummer Privat')
                    ->styleFontFamily($fontFamily)
                    ->styleTextSize($textSize)
                    ->styleTextSize('11px')
                    , '33%')
                ->addElementColumn((new Element())
                    ->setContent('Geschäftlich')
                    ->styleFontFamily($fontFamily)
                    ->styleTextSize($textSize)
                    ->styleTextSize('11px')
                    , '33%')
                ->addElementColumn((new Element())
                    ->setContent('Mobil')
                    ->styleFontFamily($fontFamily)
                    ->styleTextSize($textSize)
                    ->styleTextSize('11px')
                    , '33%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('{% if(Content.Person.Parent.S1.Phone.Private) %}
                                {{ Content.Person.Parent.S1.Phone.Private }}
                            {% else %}
                                  &nbsp;
                            {% endif %}')
                    ->styleFontFamily($fontFamily)
                    ->styleTextSize($textSize)
                    , '34%')
                ->addElementColumn((new Element())
                    ->setContent('{% if(Content.Person.Parent.S1.Phone.Business) %}
                                {{ Content.Person.Parent.S1.Phone.Business }}
                            {% else %}
                                  &nbsp;
                            {% endif %}')
                    ->styleFontFamily($fontFamily)
                    ->styleTextSize($textSize)
                    , '33%')
                ->addElementColumn((new Element())
                    ->setContent('{% if(Content.Person.Parent.S1.Phone.Mobil) %}
                                {{ Content.Person.Parent.S1.Phone.Mobil }}
                            {% else %}
                                  &nbsp;
                            {% endif %}')
                    ->styleFontFamily($fontFamily)
                    ->styleTextSize($textSize)
                    , '33%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Mail-Adresse für ElternInfoBoard:')
                    ->styleFontFamily($fontFamily)
                    ->styleTextSize($textSize)
                    ->stylePaddingTop($PaddingBottom)
                    , '38%')
                ->addElementColumn((new Element())
                    ->setContent('{% if(Content.Person.Parent.S1.Mail.Private) %}
                                {{ Content.Person.Parent.S1.Mail.Private }}
                            {% else %}
                                  &nbsp;
                            {% endif %}')
                    ->styleFontFamily($fontFamily)
                    ->styleTextSize($textSize)
                    ->stylePaddingTop($PaddingBottom)
                    ->styleBorderBottom('1px', '#000', 'dotted')
                    , '62%')
            )
            ->addElement((new Element())
                ->setContent('Sorgeberechtigter:
                        {% if(Content.Person.Parent.S2.Name.First) %}
                                {{ Content.Person.Parent.S2.Name.First }}
                            {% else %}
                                  &nbsp;
                            {% endif %}
                            {% if(Content.Person.Parent.S2.Name.Last) %}
                                {{ Content.Person.Parent.S2.Name.Last }}
                            {% else %}
                                  &nbsp;
                            {% endif %}')
                ->styleFontFamily($fontFamily)
                ->styleTextBold()
                ->styleTextSize($textSize)
                ->stylePaddingTop($PaddingBottom)
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Telefonnummer Privat')
                    ->styleFontFamily($fontFamily)
                    ->styleTextSize($textSize)
                    ->styleTextSize('11px')
                    , '33%')
                ->addElementColumn((new Element())
                    ->setContent('Geschäftlich')
                    ->styleFontFamily($fontFamily)
                    ->styleTextSize($textSize)
                    ->styleTextSize('11px')
                    , '33%')
                ->addElementColumn((new Element())
                    ->setContent('Mobil')
                    ->styleFontFamily($fontFamily)
                    ->styleTextSize($textSize)
                    ->styleTextSize('11px')
                    , '33%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('{% if(Content.Person.Parent.S2.Phone.Private) %}
                                {{ Content.Person.Parent.S2.Phone.Private }}
                            {% else %}
                                  &nbsp;
                            {% endif %}')
                    ->styleFontFamily($fontFamily)
                    ->styleTextSize($textSize)
                    , '34%')
                ->addElementColumn((new Element())
                    ->setContent('{% if(Content.Person.Parent.S2.Phone.Business) %}
                                {{ Content.Person.Parent.S2.Phone.Business }}
                            {% else %}
                                  &nbsp;
                            {% endif %}')
                    ->styleFontFamily($fontFamily)
                    ->styleTextSize($textSize)
                    , '33%')
                ->addElementColumn((new Element())
                    ->setContent('{% if(Content.Person.Parent.S2.Phone.Mobil) %}
                                {{ Content.Person.Parent.S2.Phone.Mobil }}
                            {% else %}
                                  &nbsp;
                            {% endif %}')
                    ->styleFontFamily($fontFamily)
                    ->styleTextSize($textSize)
                    , '33%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Mail-Adresse für ElternInfoBoard:')
                    ->styleFontFamily($fontFamily)
                    ->styleTextSize($textSize)
                    ->stylePaddingTop($PaddingBottom)
                    , '38%')
                ->addElementColumn((new Element())
                    ->setContent('{% if(Content.Person.Parent.S2.Mail.Private) %}
                                {{ Content.Person.Parent.S2.Mail.Private }}
                            {% else %}
                                  &nbsp;
                            {% endif %}')
                    ->styleFontFamily($fontFamily)
                    ->styleTextSize($textSize)
                    ->stylePaddingTop($PaddingBottom)
                    ->styleBorderBottom('1px', '#000', 'dotted')
                    , '62%')
            )
            ->addElement((new Element())
                ->setContent('Im Notfall zu benachrichtigen:')
                ->styleFontFamily($fontFamily)
                ->styleTextBold()
                ->styleTextSize($textSize)
                ->stylePaddingTop($PaddingBottom)
            )
            ->addElement((new Element())
                ->setContent('
                            {% if(Content.Person.Contact.Phone.Radebeul.EmergencyNumber) %}
                                {{ Content.Person.Contact.Phone.Radebeul.EmergencyNumber }}
                            {% else %}
                              &nbsp;<br/>&nbsp;
                            {% endif %}
                        ')
                ->styleFontFamily($fontFamily)
                ->styleTextSize($textSize)
                ->styleBorderBottom('1px', '#000', 'dotted')
            )
            ->addElement((new Element())
                ->setContent('&nbsp;')
            )
            ->addElement((new Element())
                ->setContent('Besonderheiten / Wichtiges / Allergien / Erkrankungen:')
                ->styleFontFamily($fontFamily)
                ->styleTextBold()
                ->styleTextSize($textSize)
            )
            ->addElement((new Element())
                ->setContent('
                            {% if(Content.Student.MedicalRecord.Disease) %}
                                {{ Content.Student.MedicalRecord.Disease|nl2br }}
                            {% else %}
                                  &nbsp;
                            {% endif %}
                        ')
                ->styleFontFamily($fontFamily)
                ->styleTextSize($textSize)
                ->styleMarginTop('10px')
//                ->styleHeight('100px')
//                ->styleBorderAll('1px', '#000', 'dotted')
            )
            ->stylePaddingLeft('30px')
            ->stylePaddingRight('30px')
        );
    }

    public function buildPageTwo()
    {

        $textSize = '16px';
        $fontFamily = 'MetaPro';
        $PaddingBottomLarge = '46px';

        return (new Page())
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('&nbsp;')
                    ->styleHeight('150px')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent($this->setCheckBox())
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('Mein Kind darf im Rahmen des Schwimmunterrichts und während schulischer    
                            Veranstaltungen, Klassenfahrten und Ausflügen der Schule und des Hortes, baden.
                            Mein Kind ist:')
                        ->styleFontFamily($fontFamily)
                        ->styleLineHeight('90%')
                        ->styleTextSize($textSize)
                        , '95%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent($this->setCheckBox())
                        ->stylePaddingTop('5px')
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('Nichtschwimmer')
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize($textSize)
                        , '90%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent($this->setCheckBox())
                        ->stylePaddingTop('5px')
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('Schwimmer (Seepferdchen, ………………….. Schwimmstufe)')
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize($textSize)
                        ->stylePaddingBottom($PaddingBottomLarge)
                        , '90%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent($this->setCheckBox())
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('Mein Kind darf das Fahrrad für den Schulweg und bei der Durchführung 
                            schulischer Veranstaltungen und Veranstaltungen des Hortes nutzen.
                            Mein Kind hat ein verkehrssicheres Fahrrad, hält sich an die notwendigen Verkehrsregeln   
                            und trägt einen Fahrradhelm.')
                        ->styleFontFamily($fontFamily)
                        ->styleLineHeight('90%')
                        ->styleTextSize($textSize)
                        ->stylePaddingBottom($PaddingBottomLarge)
                        , '95%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent($this->setCheckBox())
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('Mein Kind kann an Schulveranstaltungen teilnehmen, bei denen es auch durch 
                            ehrenamtlich tätige und vom Träger der Einrichtungen entsprechend eingewiesene    
                            Personen betreut wird.')
                        ->styleFontFamily($fontFamily)
                        ->styleLineHeight('90%')
                        ->styleTextSize($textSize)
                        ->stylePaddingBottom($PaddingBottomLarge)
                        , '95%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent($this->setCheckBox())
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('Mein Kind ist Vegetarier')
                        ->styleFontFamily($fontFamily)
                        ->styleLineHeight('90%')
                        ->styleTextSize($textSize)
                        ->stylePaddingBottom($PaddingBottomLarge)
                        , '95%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent($this->setCheckBox())
                        ->setContent('&nbsp;')
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('Mein Kind besitzt monatlich ein Ticket für den ÖPNV:')
                        ->styleFontFamily($fontFamily)
                        ->styleLineHeight('90%')
                        ->styleTextSize($textSize)
                        , '95%')
                )

                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent($this->setCheckBox())
                        ->stylePaddingTop('5px')
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('Ja')
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize($textSize)
                        , '90%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent($this->setCheckBox())
                        ->stylePaddingTop('5px')
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('Nein')
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize($textSize)
                        ->stylePaddingBottom($PaddingBottomLarge)
                        , '90%')
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