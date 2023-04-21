<?php
namespace SPHERE\Application\Api\Document\Standard\Repository\StudentCard;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class StudentCardNew
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository
 */
class StudentCardNew extends AbstractDocument
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Schülerkartei';
    }

    /**
     * @return Page
     */
    public function buildPage(): Page
    {
        $OutLines = '1.2px';
        $InnerLines = '0.5px';

        return (new Page())
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('SCHÜLERKARTEI')
                        ->styleTextSize('16px')
                        ->stylePaddingBottom('10px')
                        ->styleTextBold()
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addSliceColumn((new Slice())
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Schule:')
                                    ->styleTextSize('13px')
                                    , '7%')
                            ->addElementColumn((new Element())
                                ->setContent('
                                    {% if( Content.Student.Company2 is not empty) %}
                                        {{ Content.Student.Company }}
                                        {{ Content.Student.Company2 }}
                                    {% else %}
                                        {{ Content.Student.Company }}
                                    {% endif %}
                                    
                                    {% if( Content.Student.CompanyAddress is not empty) %}
                                        <br/>
                                        {{ Content.Student.CompanyAddress }}
                                    {% endif %}
                                    ')
                           , '93%')
                        )
                        ->stylePaddingBottom('50px')
                        ->styleMarginBottom('13px')
                        ->stylePaddingLeft('5px')
                        ->styleBorderAll($OutLines)
                    )
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addSliceColumn((new Slice())
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Name:')
                                ->styleTextBold()
                                ->styleMarginBottom('5px')
                                ->styleBorderTop($OutLines)
                                ->stylePaddingLeft('5px')
                            , '4%')
                            ->addElementColumn((new Element())
                                ->setContent('
                                            {% if( Content.Person.Data.Name.Last is not empty) %}
                                                {{ Content.Person.Data.Name.Last }}
                                            {% else %}
                                                &nbsp;
                                            {% endif %}')
                                ->styleBorderTop($OutLines)
                                ->stylePaddingLeft('5px')
                            , '44%')
                            ->addElementColumn((new Element())
                                ->setContent('Vorname:')
                                ->styleTextBold()
                                ->styleMarginBottom('5px')
                                ->styleBorderTop($OutLines)
                            , '4%')
                            ->addElementColumn((new Element())
                                ->setContent('
                                            {% if( Content.Person.Data.Name.First is not empty) %}
                                                {{ Content.Person.Data.Name.First }}
                                            {% else %}
                                                &nbsp;
                                            {% endif %}')
                                ->styleBorderTop($OutLines)
                                ->stylePaddingLeft('5px')
                            , '48%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Anschrift:')
                                    ->stylePaddingLeft('5px')
                                    ->stylePaddingBottom('5px')
                                , '8%')
                            ->addElementColumn((new Element())
                                ->setContent('
                                            {% if(Content.Person.Address.Street.Name) %}
                                            {{ Content.Person.Address.Street.Name }}
                                            {{ Content.Person.Address.Street.Number }}
                                            <br>
                                            {{ Content.Person.Address.City.Code }}
                                            {{ Content.Person.Address.City.Name }}
                                        {% else %}
                                              &nbsp;
                                        {% endif %}')
                                ->stylePaddingLeft('5px')
                                ->stylePaddingBottom('5px')
                            ,'92%')
                            )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Geburtsdatum:')
                                ->stylePaddingLeft('5px')
                                ->stylePaddingBottom('5px')
                            , '4%')
                            ->addElementColumn((new Element())
                                ->setContent('
                                        {% if(Content.Person.Common.BirthDates.Birthday is not empty) %}
                                            {{ Content.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}')
                                ->stylePaddingLeft('5px')
                            , '36%')
                            ->addElementColumn((new Element())
                                ->setContent('Geburtsort:')
                                ->stylePaddingLeft('5px')
                                ->stylePaddingBottom('5px')
                            , '4%')
                            ->addElementColumn((new Element())
                                ->setContent('
                                        {% if(Content.Person.Common.BirthDates.Birthplace is not empty) %}
                                            {{ Content.Person.Common.BirthDates.Birthplace }}
                                         {% else %}
                                            &nbsp;
                                         {% endif %}')
                                ->stylePaddingLeft('5px')
                            , '56%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Geschlecht:')
                                ->stylePaddingLeft('5px')
                                ->stylePaddingBottom('5px')
                            , '4%')
                            ->addElementColumn((new Element())
                                ->setContent('{% if Content.Person.Common.BirthDates.Gender == 1 %}
                                                männlich
                                            {% else %}
                                                {% if Content.Person.Common.BirthDates.Gender == 2 %}
                                                    weiblich
                                                {% else %}
                                                    &nbsp;
                                                {% endif %}
                                            {% endif %}')
                                ->stylePaddingLeft('5px')
                            , '39.5%')
                            ->addElementColumn((new Element())
                                ->setContent('Staatsangehörigkeit<sup style="font-size: 9px !important;">1</sup>:')
                                ->stylePaddingBottom('5px')
                            , '20%')
                            ->addElementColumn((new Element())
                                ->setContent('
                                        {% if(Content.Person.Common.Nationality is not empty) %}
                                            {{ Content.Person.Common.Nationality }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}')
                            , '36.5%')
                            )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Religionszugehörigkeit:')
                                ->stylePaddingLeft('5px')
                                ->stylePaddingBottom('5px')
                                ->styleBorderBottom($OutLines)
                            , '22%')
                            ->addElementColumn((new Element())
                                ->setContent('{% if(Content.Person.Common.isReligion is not empty) %}
                                                        {{ Content.Person.Common.isReligion }}
                                                            {% else %}
                                                            nein / ja<sup style="font-size: 9px !important;">2</sup>
                                                            {% endif %}')
                            ->styleBorderBottom($OutLines)
                            ->stylePaddingBottom('5px')
                            , '78%')
                        )
                    )
                )
                ->addSection((new Section())
                    ->addSliceColumn((new Slice())
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Eltern')
                                ->stylePaddingLeft('5px')
                                ->stylePaddingTop('3px')
                                ->styleBorderBottom($InnerLines)
                                ->styleBorderRight($InnerLines)
                                ->styleTextBold()
                            , '20%')
                            ->addElementColumn((new Element())
                                ->setContent('1. Personensorgeberechtigter')
                                ->stylePaddingLeft('35px')
                                ->stylePaddingTop('3px')
                                ->styleBorderBottom($InnerLines)
                                ->styleBorderRight($InnerLines)
                            , '40%')
                            ->addElementColumn((new Element())
                                ->setContent('2. Personensorgeberechtigter')
                                ->stylePaddingLeft('35px')
                                ->stylePaddingTop('3px')
                                ->styleBorderBottom($InnerLines)
                                , '40%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                ->stylePaddingTop('3px')
                                ->styleTextSize('7px')
                                ->styleHeight('10px')
                                ->styleBorderRight($InnerLines)
                            , '20%')
                            ->addElementColumn((new Element())
                                ->setContent('
                                            {% if(Content.Person.Parent.S1.Salutation == "Frau") or
                                                 (Content.Person.Parent.S1.Gender == "Weiblich") %}
                                                 Mutter
                                                
                                            {% else %}
                                              Mutter / Vater / Sonstiger Personenberechtigter²
                                            {% endif %}')
                                ->stylePaddingTop('3px')
                                ->styleAlignCenter()
                                ->styleBorderLeft($InnerLines)
                                ->styleBorderRight($InnerLines)
                                ->styleHeight('10px')
                                ->styleTextSize('9px')
                                , '40%')
                            ->addElementColumn((new Element())
                                ->setContent(
                                    '
                                            {% if(Content.Person.Parent.S2.Salutation == "Herr") or
                                                 (Content.Person.Parent.S2.Gender == "Männlich") %}
                                                 Vater
                                                
                                            {% else %}
                                              Mutter / Vater / Sonstiger Personenberechtigter²
                                            {% endif %}')
                                ->styleAlignCenter()
                                ->stylePaddingTop('3px')
                                ->styleBorderLeft($InnerLines)
                                ->styleHeight('10px')
                                ->styleBorderRight($InnerLines)
                                ->styleTextSize('9px')
                                , '40%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Name und <br> Vorname:')
                                ->stylePaddingLeft('5px')
                                ->stylePaddingTop('10px')
                                ->styleBorderRight($InnerLines)
                                ->styleHeight('40px')
                            , '20%')
                            ->addElementColumn((new Element())
                                ->setContent('
                                            {% if(Content.Person.Parent.S1.Name.Last is not empty) %}
                                            {% if(Content.Person.Parent.S1.Name.First is not empty) %}
                                                {{ Content.Person.Parent.S1.Name.Last }}, {{ Content.Person.Parent.S1.Name.First }}
                                                
                                            {% else %}
                                              &nbsp;
                                            {% endif %}
                                            {% endif %}')
                                ->stylePaddingLeft('5px')
                                ->stylePaddingTop('10px')
                                ->styleHeight('40px')
                                ->styleBorderRight($InnerLines)
                                , '40%')
                            ->addElementColumn((new Element())
                                ->setContent('
                                            {% if(Content.Person.Parent.S2.Name.Last is not empty) %}
                                            {% if(Content.Person.Parent.S2.Name.First is not empty) %}
                                                {{ Content.Person.Parent.S2.Name.Last }}, {{ Content.Person.Parent.S2.Name.First }}
                                                
                                            {% else %}
                                              &nbsp;
                                            {% endif %}
                                            {% endif %}')
                                ->stylePaddingLeft('5px')
                                ->stylePaddingTop('10px')
                                ->styleHeight('40px')
                                ->styleBorderRight($InnerLines)
                                , '40%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Anschrift<sup style="font-size: 9px !important;">3</sup>:')
                                ->stylePaddingLeft('5px')
                                ->stylePaddingTop('10px')
                                ->styleBorderRight($InnerLines)
                                ->styleHeight('50px')
                                , '20%')
                            ->addElementColumn((new Element())
                                ->setContent('
                                            {% if(Content.Person.Parent.S1.Address.Street) %}
                                            {{ Content.Person.Parent.S1.Address.Street }}
                                            {{ Content.Person.Parent.S1.Address.StreetNumber }}
                                            <br>
                                            {{ Content.Person.Parent.S1.Address.CityCode }}
                                            {{ Content.Person.Parent.S1.Address.City }}
                                        {% else %}
                                              &nbsp;
                                        {% endif %}')
                                ->stylePaddingLeft('5px')
                                ->stylePaddingTop('10px')
                                ->styleBorderRight($InnerLines)
                                ->styleHeight('50px')
                                , '40%')
                            ->addElementColumn((new Element())
                                ->setContent('
                                            {% if(Content.Person.Parent.S2.Address.Street) %}
                                            {{ Content.Person.Parent.S2.Address.Street }}
                                            {{ Content.Person.Parent.S2.Address.StreetNumber }}
                                            <br>
                                            {{ Content.Person.Parent.S2.Address.CityCode }}
                                            {{ Content.Person.Parent.S2.Address.City }}
                                            {% else %}
                                              &nbsp;
                                            {% endif %}')
                                ->stylePaddingLeft('5px')
                                ->stylePaddingTop('10px')
                                ->styleBorderRight($InnerLines)
                                ->styleHeight('50px')
                                , '40%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Telefonnummer:')
                                ->stylePaddingLeft('5px')
                                ->stylePaddingTop('10px')
                                ->styleBorderRight($InnerLines)
                                ->styleHeight('25px')
                            , '20%')
                            ->addElementColumn((new Element())
                                ->setContent('
                                {% if(Content.Person.Parent.S1.Phone.Festnetz) %}
                                    {% if(Content.Person.Parent.S1.Phone.Mobil) %}
                                        {{ Content.Person.Parent.S1.Phone.Festnetz }}, {{ Content.Person.Parent.S1.Phone.Mobil }}
                                    {% else %}
                                        {{ Content.Person.Parent.S1.Phone.Festnetz }}
                                    {% endif %}
                                {% else %}
                                    {{ Content.Person.Parent.S1.Phone.Mobil }}
                                {% endif %}')
                                ->stylePaddingLeft('5px')
                                ->stylePaddingTop('10px')
                                ->styleBorderRight($InnerLines)
                                ->styleHeight('25px')
                                , '40%')
                            ->addElementColumn((new Element())
                                ->setContent('
                                            {{ Content.Person.Parent.S2.Phone.Festnetz }}
                                            {{ Content.Person.Parent.S2.Phone.Mobil }} ')
                                ->stylePaddingLeft('5px')
                                ->stylePaddingTop('10px')
                                ->styleBorderRight($InnerLines)
                                ->styleHeight('25px')
                                , '40%')
                        )
                    )
                )
                ->addSection((new Section())
                    ->addSliceColumn((new Slice())
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Kontaktdaten einer im Notfall zu benachrichtigenden Person: 
                                {% if(Content.Person.Contact.Phone.EmergencyNumber) %}
                                {{ Content.Person.Contact.Phone.EmergencyNumber }}
                                 {% else %}
                                  &nbsp;
                                {% endif %}')
                                ->styleBorderTop($OutLines)
                                ->stylePaddingLeft('5px')
                                ->stylePaddingTop('10px')
                                ->styleHeight('35px')
                            , '100%')
                        )
                    )
                )

                    ->styleBorderLeft($OutLines)
                    ->styleBorderRight($OutLines)
                    ->styleBorderBottom($OutLines)
                    ->styleMarginBottom('3px')

                )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addSliceColumn((new Slice())
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Einschulung am:')
                                ->stylePaddingLeft('5px')
                                ->stylePaddingTop('10px')
                                ->stylePaddingBottom('5px')
                                , '15.5%')
                            ->addElementColumn((new Element())
                                ->setContent('
                                {% if(Content.Student.School.Enrollment.Date is not empty) %}
                                    {{ Content.Student.School.Enrollment.Date }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                                ->stylePaddingLeft('5px')
                                ->stylePaddingTop('10px')
                                , '84.5%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Besuch einer Kindertageseinrichtung im Jahr vor der Schulaufnahme<sup style="font-size: 9px !important;">4</sup>: ja / nein<sup style="font-size: 9px !important;">2</sup>')
                                ->stylePaddingLeft('5px')
                                ->stylePaddingBottom('5px')
                            )
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Art und Grad einer Behinderung / chronische Krankheit<sup style="font-size: 9px !important;">1 2 5</sup>: <br> <br>')
                                ->stylePaddingLeft('5px')
                                ->stylePaddingBottom('5px')
                            , '53.5%')
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                ->styleBorderBottom($InnerLines)
                            , '45.5%')
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                            , '1%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                ->styleMarginBottom('5px')
                                ->styleHeight('0px')
                                ,'1%')
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                ->styleBorderBottom($InnerLines)
                                ->styleMarginBottom('5px')
                                ->styleHeight('0px')
                                , '98%')
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                ->styleMarginBottom('5px')
                                ->styleHeight('0px')
                                ,'1%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('festgestellter sonderpädagogischer Förderbedarf<sup style="font-size: 9px !important;">1</sup>:')
                                ->stylePaddingLeft('5px')
                                ->stylePaddingBottom('5px')
                            , '46%')
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                ->styleBorderBottom($InnerLines)
                            , '53%')
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                            , '1%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('festgestellte Teilleistungsschwäche<sup style="font-size: 9px !important;">1</sup>:')
                                ->stylePaddingLeft('5px')
                                ->stylePaddingBottom('3px')
                            , '35%')
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                ->styleBorderBottom($InnerLines)
                            , '64%')
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                            , '1%')
                        )
                    )
                )
                ->styleHeight('155px')
                ->styleMarginTop('12px')
                ->styleBorderAll($OutLines)
                ->styleMarginBottom('12px')
            )

            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addSliceColumn((new Slice())
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Besuchte Schulen (von - bis):')
                                ->stylePaddingLeft('3px')
                                ->stylePaddingTop('5px')
                            , '27.5%')
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                ->styleBorderBottom($InnerLines)
                                ->stylePaddingTop('5px')
                            , '71.5%')
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                , '1%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                , '1%')
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                ->styleBorderBottom($InnerLines)
                                ->stylePaddingTop('5px')
                                , '98%')
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                , '1%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                , '1%')
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                ->styleBorderBottom($InnerLines)
                                ->stylePaddingTop('5px')
                                , '98%')
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                , '1%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                            , '1%')
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                ->styleBorderBottom($InnerLines)
                                ->stylePaddingTop('5px')
                                , '98%')
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                , '1%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                , '1%')
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                ->styleBorderBottom($InnerLines)
                                ->stylePaddingTop('5px')
                                , '98%')
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                , '1%')
                        )
                    )
                )
                ->styleHeight('135px')
                ->styleMarginTop('12px')
                ->styleBorderAll($OutLines)
                ->styleMarginBottom('12px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addSliceColumn((new Slice())
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                ->styleMarginTop('10px')
                                ->styleBorderTop($InnerLines)
                            , '30%')
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                            , '70%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('<sup style="font-size: 8px !important;">1</sup>')
                            , '3%')
                            ->addElementColumn((new Element())
                                ->setContent('nur mit Einwilligung der Eltern, der Schülerin oder des Schülers 
                                             gemäß Ziffer II Nummer 5 der VwV Schuldatenschutz')
                                ->styleTextSize('8px')
                            , '97%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('<sup style="font-size: 8px !important;">2</sup>')
                                ->stylePaddingTop('2px')
                            , '3%')
                            ->addElementColumn((new Element())
                                ->setContent('Zutreffendes unterstreichen')
                                ->styleTextSize('8px')
                                ->stylePaddingTop('2px')
                            , '97%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('<sup style="font-size: 8px !important;">3</sup>')
                                ->stylePaddingTop('2px')
                                , '3%')
                            ->addElementColumn((new Element())
                                ->setContent('nur bei abweichender Anschrift von der Anschrift der Schülerin oder des Schülers')
                                ->styleTextSize('8px')
                                ->stylePaddingTop('2px')
                                , '97%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('<sup style="font-size: 8px !important;">4</sup>')
                                ->stylePaddingTop('2px')
                                , '3%')
                            ->addElementColumn((new Element())
                                ->setContent('nur an Grundschulen gemäß § 3 Absatz 7 Satz 3 Nummer 10 der Schulordnung Grundschulen, an Förderschulen gemäß 
                                                      § 14 Absatz 1 Satz 6 Nummer 10 der Schulordnung Förderschulen, an Oberschulen+ gemäß §64c Absatz 1 
                                                      der Schulordnung Ober- und Abendoberschulen in Verbindung mit § 5 Absatz 7 Satz 1 Nummer 10 
                                                      der Schulordnung Gemeinschaftsschulen und an Gemeinschaftsschulen gemäß § 5 Absatz 7 Satz 1 Nummer 10 der Schulordnung Gemeinschaftsschulen')
                                ->styleTextSize('8px')
                                ->stylePaddingTop('2px')
                                , '97%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('<sup style="font-size: 8px !important;">5</sup>')
                                ->stylePaddingTop('2px')
                                , '3%')
                            ->addElementColumn((new Element())
                                ->setContent('nur soweit für den Schulbesuch von Bedeutung')
                                ->styleTextSize('8px')
                                ->stylePaddingTop('2px')
                                , '97%')
                        )
                    )
                )
            );
    }

    /**
     *
     * @param array $pageList
     * @param string $Part
     *
     * @return Frame
     */
    public function buildDocument($pageList = array(), $Part = '0'): Frame
    {
        return (new Frame())->addDocument((new Document())
            ->addPage($this->buildPage())
        );
    }
}