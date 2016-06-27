<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESZC;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Document;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Frame;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Common\Frontend\Layout\Repository\Container;

/**
 * Class CheBeGs
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class CheBeGs extends Certificate
{

    /**
     * @param bool $IsSample
     *
     * @return Frame
     */
    public function buildCertificate($IsSample = true)
    {

        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Name der Schule:')
                            ->stylePaddingTop('10px')
                            ->stylePaddingLeft('5px')
                            ->styleTextSize('11px')
                            , '14%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Company.Data.Name is not empty) %}
                                    {{ Content.Company.Data.Name }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->stylePaddingTop('9px')
                            ->styleBorderBottom()
                            , '86%')
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Bildungsempfehlung in den Klassenstufe 4')
                        ->styleAlignCenter()
                        ->styleTextSize('20px')
                        ->styleTextBold()
                        ->stylePaddingTop('15px')
                        ->stylePaddingBottom('20px')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Person.Data.Name.First }}
                                          {{ Content.Person.Data.Name.Last }}')
                            ->stylePaddingTop()
                            ->stylePaddingLeft()
                            ->styleBorderBottom()
                            , '68%')
                        ->addElementColumn((new Element())
                            , '1%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Division.Data.Level.Name }}{{ Content.Division.Data.Name }}')
                            ->stylePaddingTop()
                            ->stylePaddingLeft()
                            ->styleBorderBottom()
                            , '15%')
                        ->addElementColumn((new Element())
                            , '1%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Division.Data.Year }}')
                            ->stylePaddingTop()
                            ->stylePaddingLeft()
                            ->styleBorderBottom()
                            , '15%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Vorname und Name')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->stylePaddingLeft()
                            ->styleTextSize('11px')
                            , '68%')
                        ->addElementColumn((new Element())
                            , '1%')
                        ->addElementColumn((new Element())
                            ->setContent('Klasse')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->stylePaddingLeft()
                            ->styleTextSize('11px')
                            , '15%')
                        ->addElementColumn((new Element())
                            , '1%')
                        ->addElementColumn((new Element())
                            ->setContent('Schuljahr')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->stylePaddingLeft()
                            ->styleTextSize('11px')
                            , '15%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Person.Common.BirthDates.Birthday is not empty) %}
                                    {{ Content.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->stylePaddingLeft('5px')
                            ->styleBorderBottom()
                            , '30%')
                        ->addElementColumn((new Element())
                            , '1%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Person.Common.BirthDates.Birthplace is not empty) %}
                                    {{ Content.Person.Common.BirthDates.Birthplace }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->stylePaddingLeft('5px')
                            ->styleBorderBottom()
                            , '69%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('geboren am')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->stylePaddingLeft()
                            ->styleTextSize('11px')
                            , '30%')
                        ->addElementColumn((new Element())
                            , '1%')
                        ->addElementColumn((new Element())
                            ->setContent('in')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->stylePaddingLeft()
                            ->styleTextSize('11px')
                            , '70%')
                    )
                    ->addElement((new Element())
                        ->setContent('{% if(Content.Person.Address.City.Name) %}
                                    {{ Content.Person.Address.Street.Name }}
                                    {{ Content.Person.Address.Street.Number }},
                                    {{ Content.Person.Address.City.Code }}
                                    {{ Content.Person.Address.City.Name }}
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->stylePaddingLeft('5px')
                        ->styleBorderBottom()
                    )
                    ->addElement((new Element())
                        ->setContent('Wohnhaft in')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->stylePaddingLeft()
                        ->styleTextSize('11px')
                    )
                    ->addElement((new Element())
                        ->setContent('{% if(Content.Person.Parent) %}
                                    {{ Content.Person.Parent.Mother.Name.First }}
                                    {{ Content.Person.Parent.Mother.Name.Last }},
                                    {{ Content.Person.Parent.Father.Name.First }}
                                    {{ Content.Person.Parent.Father.Name.Last }}
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->stylePaddingLeft('5px')
                        ->styleBorderBottom()
                    )
                    ->addElement((new Element())
                        ->setContent('Name der Eltern')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->stylePaddingLeft()
                        ->styleTextSize('11px')
                    )
                )
                ////////////////////////////////////////////////////////////////////////////////////////////////////
//                ->setContent('{% if Content.Person.Common.BirthDates.Gender == 2 %}
//                                Die Schülerin kann ihre Ausbildung am Gymnasium fortsetzen.
//                            {% else %}
//                                {% if Content.Person.Common.BirthDates.Gender == 1 %}
//                                    Der Schüler kann seine Ausbildung am Gymnasium fortsetzen.
//                                {% else %}
//                                    Die Schülerin/Der Schüler¹ kann ihre/seine¹ Ausbildung am Gymnasium fortsetzen.
//                                {% endif %}
//                            {% endif %}')
                ////////////////////////////////////////////////////////////////////////////////////////////////////
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('1. Leistungsstand')
                        ->styleTextSize('20px')
                        ->styleTextBold()
                        ->stylePaddingBottom('5px')
                        ->styleMarginTop('5px')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                            {% if Content.Person.Common.BirthDates.Gender == 2 %}
                                                Die Schülerin
                                            {% else %}
                                              {% if Content.Person.Common.BirthDates.Gender == 1 %}
                                                    Der Schüler
                                                {% else %}
                                                  Die Schülerin/Der Schüler¹
                                                {% endif %}
                                            {% endif %}
                                hat ausweislich der Halbjahresinformation /
                            der für das Jahreszeugnis vorgesehene Noten gemäß Beschluss der Klassenkonferenz¹
                             vom
                             {% if(Content.Input.DateCertifcate is not empty) %}
                                {{ Content.Input.DateCertifcate }}
                            {% else %}
                                ______________
                            {% endif %}
                            folgende Leistungen erreicht:')
                            ->stylePaddingBottom('25px')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Deutsch/Sorbisch¹ ²')
                            ->stylePaddingTop()
                            , '25%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.DE is not empty) %}
                                    {{ Content.Grade.Data.DE }}
                                {% else %}
                                    ---
                                {% endif %}')
                            ->styleAlignCenter()
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->styleBackgroundColor('#BBB')
                            , '20%')
                        ->addElementColumn((new Element())
                            , '10%')
                        ->addElementColumn((new Element())
                            ->setContent('Mathematik')
                            ->stylePaddingTop()
                            , '25%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.MA is not empty) %}
                                    {{ Content.Grade.Data.MA }}
                                {% else %}
                                    ---
                                {% endif %}')
                            ->styleAlignCenter()
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->styleBackgroundColor('#BBB')
                            , '20%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Sachunterricht')
                            ->stylePaddingTop()
                            ->styleMarginTop('10px')
                            , '25%')
                        ->addElementColumn((new Element())
                            // ToDO Sachunterricht
                            ->setContent('{% if(Content.Grade.Data.SA is not empty) %}
                                    ...
                                {% else %}
                                    ---
                                {% endif %}')
                            ->styleMarginTop('10px')
                            ->styleAlignCenter()
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->styleBackgroundColor('#BBB')
                            , '20%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleMarginTop('10px')
                            , '55%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Durchschnitt der Noten aus den angegebenen Fächern')
                            ->styleMarginTop('20px')
                            , '80%')
                        ->addElementColumn((new Element())
                            // ToDO EN replace to Sachunterricht
                            ->setContent('{% if(Content.Grade.Data.DE is not empty) %}
                                    {% if(Content.Grade.Data.MA is not empty) %}
                                        {% if(Content.Grade.Data.EN is not empty) %}
                                            {{ ((Content.Grade.Data.DE + Content.Grade.Data.MA + Content.Grade.Data.EN) / 3)|round(2, "floor") }}
                                        {% else %}
                                            ---
                                        {% endif %}
                                    {% else %}
                                        ---
                                    {% endif %}
                                {% else %}
                                    ---
                                {% endif %}')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->styleMarginTop('20px')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#BBB')
                            , '20%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '80%')
                        ->addElementColumn((new Element())
                            ->stylePaddingTop()
                            ->setContent('(in Ziffern)')
                            ->styleTextSize('9px')
                            ->styleAlignCenter()
                            , '20%')
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('2. Gutachten³')
                        ->styleTextSize('20px')
                        ->styleTextBold()
                        ->styleMarginTop('5px')
                        ->stylePaddingBottom('5px')
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('{% if(Content.Input.Survey is not empty) %}
                                    {{ Content.Input.Survey|nl2br }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->styleHeight('200px')
                        ->stylePaddingBottom()
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Auf Grund des Leitungsstandes und des Gutachtens wird
                        {% if Content.Person.Common.BirthDates.Gender == 2 %}
                                der Schülerin
                            {% else %}
                                {% if Content.Person.Common.BirthDates.Gender == 1 %}
                                    dem Schüler
                                {% else %}
                                    der Schülerin/dem Schüler¹
                                {% endif %}
                            {% endif %} empfohlen,')
                        ->styleMarginTop('15px')
                    )
                    ->addElement((new Element())
                        ->setContent('{% if Content.Person.Common.BirthDates.Gender == 2 %}
                                ihre Ausbildung
                                {% if(Content.Input.SchoolType is not empty) %}
                                {{ Content.Input.SchoolType }}
                                {% else %}
                                    ________________________________
                                {% endif %}
                                fortzusetzen.
                            {% else %}
                                {% if Content.Person.Common.BirthDates.Gender == 1 %}
                                    seine Ausbildung
                                    {% if(Content.Input.SchoolType is not empty) %}
                                    {{ Content.Input.SchoolType }}
                                    {% else %}
                                        ________________________________
                                    {% endif %}
                                fortzusetzen.
                                {% else %}
                                    ihre/seine¹ Ausbildung
                                     {% if(Content.Input.SchoolType is not empty) %}
                                    {{ Content.Input.SchoolType }}
                                    {% else %}
                                        ________________________________
                                    {% endif %}
                                     fortzusetzen.
                                {% endif %}
                            {% endif %}')
                        ->styleMarginTop('13px')

                    )
                    ->stylePaddingTop()
                    ->stylePaddingBottom()
                    ->styleMarginTop('20px')
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Diese Empfehlung wurde durch die Klassenkonferenz am
                            {% if(Content.Input.DateConference is not empty) %}
                                {{ Content.Input.DateConference }}
                            {% else %}
                                ______________
                            {% endif %}
                                beschlossen.
                                ')
                        ->styleMarginTop('20px')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Datum:')
                            , '7%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Date is not empty) %}
                                    {{ Content.Input.Date }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleBorderBottom('1px', '#000')
                            ->styleAlignCenter()
                            , '18%')
                        ->addElementColumn((new Element())
                            , '75%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->styleBorderBottom('1px', '#000')
                            ->styleAlignCenter()
                            ->styleMarginTop('100px')

                            , '35%')
                        ->addElementColumn((new Element())
                            ->setContent('Dienstsiegel der Schule')
                            ->styleTextSize('11px')
                            ->styleAlignCenter()
                            ->styleMarginTop('80px')
                            , '30%')
                        ->addElementColumn((new Element())
                            ->styleBorderBottom('1px', '#000')
                            ->styleAlignCenter()
                            ->styleMarginTop('100px')
                            , '35%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Schulleiter/in')
                            ->styleTextSize('11px')
                            ->stylePaddingTop('5px')
                            ->styleAlignCenter()
                            , '35%')
                        ->addElementColumn((new Element())
                            , '30%')
                        ->addElementColumn((new Element())
                            ->setContent('Klassenlehrer/in')
                            ->styleTextSize('11px')
                            ->stylePaddingTop('5px')
                            ->styleAlignCenter()
                            , '35%')
                    )
                    ->stylePaddingTop()
                    ->stylePaddingBottom()
                    ->styleMarginTop('20px')
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('¹ Nichtzutreffendes streichen.'
                            .new Container('² An sorbische Schulen, an denen Sorbisch je nach Unterrichtsfach und Klassenstufe
                            Unterrichtssprache ist, kann nach Entscheidung ')
                            .new Container('&nbsp;&nbsp;der Schulkonferenz gem. § 21 Abs. 5 SOGS das
                            Fach Deutsch durch das Fach Sorbisch ersetzt werden.')
                            .new Container('³ Falls der Raum für Eintragungen nicht ausreicht, ist ein Beiblatt zu verwenden.')
                        )
                        ->styleTextSize('9px')
                        ->styleMarginTop('5px')
                    )
                )
            )
        );
    }
}
