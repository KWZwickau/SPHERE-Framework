<?php
namespace SPHERE\Application\Api\Education\Graduation\Certificate\Repository;

use SPHERE\Application\Api\Education\Graduation\Certificate\Certificate;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Document;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Element;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Frame;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Page;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Section;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Slice;
use SPHERE\Common\Frontend\Layout\Repository\Container;

class GymAbgHs extends Certificate
{

    /**
     * @return Frame
     */
    public function buildCertificate()
    {

        $Header = (new Slice())
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('GYM Abgangszeugnis Klasse 9 Hauptschulabschluss 4e.pdf')
                    ->styleTextSize('12px')
                    ->styleTextColor('#CCC')
                    ->styleAlignCenter()
                    , '25%')
                ->addElementColumn((new Element\Sample())
                    ->styleTextSize('30px')
                )
                ->addElementColumn((new Element())
                    , '25%')
            );

        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSlice(
                    $Header
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '68%')
                        ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg', '200px'))
                            , '25%'
                        )
                        ->addElementColumn((new Element())
                            , '7%')
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('ABGANGSZEUGNIS')
                        ->styleTextSize('27px')
                        ->styleAlignCenter()
                        ->styleMarginTop('32%')
                        ->styleTextBold()
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('des Gymnasiums')
                        ->styleTextSize('22px')
                        ->styleAlignCenter()
                        ->styleMarginTop('15px')
                    )
                )->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('(Sekundarstufe I)')
                        ->styleTextSize('22px')
                        ->styleAlignCenter()
                        ->styleMarginTop('5px')
                    )
                )
            )
            ->addPage((new Page())
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Vorname und Name:')
                            , '22%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Person.Data.Name.First }}
                                          {{ Content.Person.Data.Name.Last }}')
                            ->styleBorderBottom()
                        )
                    )->styleMarginTop('60px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('geboren am')
                            , '22%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Person.Common.BirthDates.Birthday is not empty) %}
                                    {{ Content.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleBorderBottom()
                            , '20%')
                        ->addElementColumn((new Element())
                            ->setContent('in')
                            ->styleAlignCenter()
                            , '5%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Person.Common.BirthDates.Birthplace is not empty) %}
                                    {{ Content.Person.Common.BirthDates.Birthplace }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleBorderBottom()
                        )
                    )->styleMarginTop('10px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('wohnhaft in')
                            , '22%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Person.Address.City.Name) %}
                                    {{ Content.Person.Address.Street.Name }}
                                    {{ Content.Person.Address.Street.Number }},
                                    {{ Content.Person.Address.City.Code }}
                                    {{ Content.Person.Address.City.Name }}
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                            ->styleBorderBottom()
                        )
                    )->styleMarginTop('10px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('hat')
                            , '5%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Company.Data.Name) %}
                                    {{ Content.Company.Data.Name }}
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                            ->styleBorderBottom('1px', '#BBB')
                            ->styleAlignCenter()
                        )
                        ->addElementColumn((new Element())
                            ->styleBorderBottom('1px', '#BBB')
                            ->setContent('&nbsp;')
                            , '5%')
                    )
                    ->styleMarginTop('20px')
                )
//                ->addSlice(
//                    (new Slice())
//                        ->addElement(
//                            (new Element())
//                                ->setContent('
//                                            {{ Content.Company.Data.Name }},
//                                        ')
//                                ->styleBorderBottom('1px', '#BBB')
//                                ->styleAlignCenter()
//                        )
//                        ->styleMarginTop('10px')
//                )
                ->addSlice(
                    (new Slice())
                        ->addElement(
                            (new Element())
                                ->setContent('{% if(Content.Company.Address.Street.Name) %}
                                    {{ Content.Company.Address.Street.Name }}
                                    {{ Content.Company.Address.Street.Number }},
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                                ->styleBorderBottom('1px', '#BBB')
                                ->styleAlignCenter()
                        )
                        ->styleMarginTop('10px')
                )
                ->addSlice(
                    (new Slice())
                        ->addSection(
                            (new Section())
                                ->addElementColumn(
                                    (new Element())
                                        ->setContent('&nbsp;')
                                        ->styleBorderBottom('1px', '#BBB')
                                    , '10%')
                                ->addElementColumn(
                                    (new Element())
                                        ->setContent('{% if(Content.Company.Address.City.Name) %}
                                            {{ Content.Company.Address.City.Code }}
                                            {{ Content.Company.Address.City.Name }}
                                        {% else %}
                                              &nbsp;
                                        {% endif %}')
                                        ->styleBorderBottom('1px', '#BBB')
                                        ->styleAlignCenter()
                                )
                                ->addElementColumn(
                                    (new Element())
                                        ->setContent('besucht')
                                        ->styleAlignRight()
                                    , '10%')
                        )
                        ->styleMarginTop('10px')
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Name und Anschrift der Schule')
                        ->styleTextSize('9px')
                        ->styleTextColor('#999')
                        ->styleAlignCenter()
                        ->styleMarginTop('5px')
                        ->styleMarginBottom('5px')
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('und verlässt nach Erfüllung der Vollzeitschulpflicht gemäß § 28 Abs. 1 Nr. 1 SchulG das Gymnasium.')
                        ->styleMarginTop('8px')
                        ->styleAlignLeft()
                    )->styleMarginTop('30%')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())/* nicht Ausgewählt = 0; männlich = 1; weiblich = 2 */
                        ->setContent('
                            {% if Content.Person.Common.BirthDates.Gender == 2 %}
                                Frau
                            {% else %}
                                {% if Content.Person.Common.BirthDates.Gender == 1 %}
                                    Herr
                                {% else %}
                                    Frau/Herr
                                {% endif %}
                            {% endif %}
                            {{ Content.Person.Data.Name.First }} {{ Content.Person.Data.Name.Last }} hat, gemäß § 30
                            Abs. 7 Satz 2 SOGYA, mit der Versetzung von Klassenstufe 9 nach Klassenstufe 10 des Gymnasiums
                            einen dem Hauptschulabschluss gleichgestellten Schulabschluss erworben.')
                            ->stylePaddingBottom()
                        )
                    )->styleMarginTop('1px')
                )
//                ->addSlice((new Slice())
//                    ->addSection((new Section())
//                        ->addElementColumn((new Element())
//                            ->setContent('¹ Zutreffendes ist zu unterstreichen.<br/>
//                                          ² Zutreffendes ist anzukreuzen')
//                            ->styleTextSize('9.5px')
//                            ->styleBorderTop()
//                            , '33%')
//                        ->addElementColumn((new Element())
//                        )
//                    )
//                    ->styleMarginTop('410px')
//                )
            )
            ->addPage((new Page())
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Vorname und Name:')
                            , '21%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Person.Data.Name.First }}
                                          {{ Content.Person.Data.Name.Last }}')
                            ->styleBorderBottom()
                            , '59%')
                        ->addElementColumn((new Element())
                            ->setContent('Klasse')
                            ->styleAlignCenter()
                            , '10%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Division.Data.Level.Name }}{{ Content.Division.Data.Name }}')
                            ->styleBorderBottom()
                            ->styleAlignCenter()
                            , '10%')
                    )->styleMarginTop('60px')
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Leistungen in den einzelnen Fächern:')
                        ->styleMarginTop('15px')
                        ->styleTextBold()
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Deutsch')
                            ->stylePaddingTop()
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Grade.Data.DE is not empty) %}
                                    {{ Content.Grade.Data.DE }}
                                {% else %}
                                    ---
                                {% endif %}
                            ')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#BBB')
                            ->styleBorderBottom('1px', '#000')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            , '9%')
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('Mathematik')
                            ->stylePaddingTop()
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Grade.Data.MA is not empty) %}
                                    {{ Content.Grade.Data.MA }}
                                {% else %}
                                    ---
                                {% endif %}
                            ')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#BBB')
                            ->styleBorderBottom('1px', '#000')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            , '9%')
                    )
                    ->styleMarginTop('5px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Englisch')
                            ->stylePaddingTop()
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Grade.Data.EN is not empty) %}
                                    {{ Content.Grade.Data.EN }}
                                {% else %}
                                    ---
                                {% endif %}
                            ')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#BBB')
                            ->styleBorderBottom('1px', '#000')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            , '9%')
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('Biologie')
                            ->stylePaddingTop()
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Grade.Data.BI is not empty) %}
                                    {{ Content.Grade.Data.BI }}
                                {% else %}
                                    ---
                                {% endif %}
                            ')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#BBB')
                            ->styleBorderBottom('1px', '#000')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            , '9%')
                    )
                    ->styleMarginTop('3px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->styleBorderBottom()
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#BBB')
                            ->styleBorderBottom('1px', '#000')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            , '9%')
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('Chemie')
                            ->stylePaddingTop()
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Grade.Data.CH is not empty) %}
                                    {{ Content.Grade.Data.CH }}
                                {% else %}
                                    ---
                                {% endif %}
                            ')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#BBB')
                            ->styleBorderBottom('1px', '#000')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            , '9%')
                    )
                    ->styleMarginTop('3px')
                )->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('2. Fremdsprache (ab Klassenstufe {{ Content.Input.Level }} )')
                            ->styleTextSize('9.5px')
                            , '39%')
                        ->addElementColumn((new Element())
                            , '61%')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Kunst')
                            ->stylePaddingTop()
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Grade.Data.KU is not empty) %}
                                    {{ Content.Grade.Data.KU }}
                                {% else %}
                                    ---
                                {% endif %}
                            ')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#BBB')
                            ->styleBorderBottom('1px', '#000')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            , '9%')
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('Physik')
                            ->stylePaddingTop()
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Grade.Data.PH is not empty) %}
                                    {{ Content.Grade.Data.PH }}
                                {% else %}
                                    ---
                                {% endif %}
                            ')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#BBB')
                            ->styleBorderBottom('1px', '#000')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            , '9%')
                    )
                    ->styleMarginTop('3px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Musik')
                            ->stylePaddingTop()
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Grade.Data.MU is not empty) %}
                                    {{ Content.Grade.Data.MU }}
                                {% else %}
                                    ---
                                {% endif %}
                            ')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#BBB')
                            ->styleBorderBottom('1px', '#000')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            , '9%')
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('Sport')
                            ->stylePaddingTop()
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.ToDO is not empty) %}
                                    {{ Content.Grade.Data.ToDO }}
                                {% else %}
                                    ---
                                {% endif %}')//ToDO Sport ist kein vorgegebenes Fach
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#BBB')
                            ->styleBorderBottom('1px', '#000')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            , '9%')
                    )
                    ->styleMarginTop('3px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Geschichte')
                            ->stylePaddingTop()
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.GE is not empty) %}
                                    {{ Content.Grade.Data.GE }}
                                {% else %}
                                    ---
                                {% endif %}')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#BBB')
                            ->styleBorderBottom('1px', '#000')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            , '9%')
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.ETH is not empty) %}
                                    Ethik
                                {% else %}
                                    {% if(Content.Grade.Data.RKA is not empty) %}
                                        Kath. Religion
                                    {% else %}
                                        {% if(Content.Grade.Data.REV is not empty) %}
                                            Ev. Religion
                                        {% else %}
                                            Ev./Kath. Religion/Ethik¹
                                        {% endif %}
                                    {% endif %}
                                {% endif %}')
                            ->stylePaddingTop()
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.ETH is not empty) %}
                                    {{ Content.Grade.Data.ETH }}
                                {% else %}
                                    {% if(Content.Grade.Data.RKA is not empty) %}
                                        {{ Content.Grade.Data.RKA }}
                                    {% else %}
                                        {% if(Content.Grade.Data.REV is not empty) %}
                                        {{ Content.Grade.Data.REV }}
                                        {% else %}
                                            ---
                                        {% endif %}
                                    {% endif %}
                                {% endif %}')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#BBB')
                            ->styleBorderBottom('1px', '#000')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            , '9%')
                    )
                    ->styleMarginTop('3px')
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Gemeinschaftskunde/')
                        ->styleMarginTop('3px')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Rechtserziehung/Wirtschaft')
                            ->stylePaddingTop()
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.ToDO is not empty) %}
                                    {{ Content.Grade.Data.ToDO }}
                                {% else %}
                                    ---
                                {% endif %}')//ToDO Gemeinschaftskunde/Rechtserziehung/Wirtschaft ist kein vorgegebenes Fach
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#BBB')
                            ->styleBorderBottom('1px', '#000')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            , '9%')
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('Technik/Computer')
                            ->stylePaddingTop()
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.ToDO is not empty) %}
                                    {{ Content.Grade.Data.ToDO }}
                                {% else %}
                                    ---
                                {% endif %}')//ToDO Technik/Computer ist kein vorgegebenes Fach
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#BBB')
                            ->styleBorderBottom('1px', '#000')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            , '9%')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Geographie')
                            ->stylePaddingTop()
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.GEO is not empty) %}
                                    {{ Content.Grade.Data.GEO }}
                                {% else %}
                                    ---
                                {% endif %}')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#BBB')
                            ->styleBorderBottom('1px', '#000')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            , '9%')
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('Informatik')
                            ->stylePaddingTop()
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.IN is not empty) %}
                                    {{ Content.Grade.Data.IN }}
                                {% else %}
                                    ---
                                {% endif %}')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#BBB')
                            ->styleBorderBottom('1px', '#000')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            , '9%')
                    )
                    ->styleMarginTop('3px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->stylePaddingTop()
                            ->styleBorderBottom()
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#BBB')
                            ->styleBorderBottom()
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            , '9%')
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->stylePaddingTop()
                            ->styleBorderBottom()
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#BBB')
                            ->styleBorderBottom()
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            , '9%')
                    )->styleMarginTop('3px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Wahlpflichtbereich²:')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->styleTextBold()
                            , '30%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Person.Data.ToDO is not empty) %}
                                    {{ Content.Grade.Data.ToDO }} Vertiefungskurs
                                {% else %}
                                    {% if(Content.Person.Data.ToDO is not empty) %}
                                        {{ Content.Grade.Data.ToDO }} 2. Fremdsprache (abschlussorientiert)
                                    {% else %}
                                        &nbsp;
                                    {% endif %}
                                {% endif %}')//ToDO Wahlpflichtbereich
                            ->styleAlignCenter()
                            ->styleBorderBottom('1px', '#000')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            , '22%')
                        ->addElementColumn((new Element())
                            ->setContent('Profil mit informatischer Bildung³')
                            , '48%')
                    )
                    ->styleMarginTop('5px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '30%')
                        ->addElementColumn((new Element())
                            ->setContent('besuchtes Profil²')
                            ->styleAlignCenter()
                            ->styleTextSize('9.5px')
                            , '22%')
                        ->addElementColumn((new Element())
                            , '48%')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Profil')
                            ->stylePaddingTop()
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#BBB')
                            ->styleBorderBottom('1px', '#000')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            , '9%')
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->styleBorderBottom()
                            , '48%')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '52%')
                        ->addElementColumn((new Element())
                            ->setContent('Fremdsprache (ab Klassenstufe 8) Im sprachlichen Profil')
                            ->styleTextSize('9.5px')
                            ->styleAlignCenter()
                            , '48%')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Vertiefungsrichtung⁴:')
                            ->styleTextBold()
                            , '20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Deepening is not empty) %}
                                    {{ Content.Input.Deepening }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleBorderBottom()
                        )
                    )->styleMarginTop('15px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Bemerkungen:')
                            , '16%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Remark is not empty) %}
                                    {{ Content.Input.Remark|nl2br }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleBorderBottom('1px', '#BBB')
                            , '84%')
                    )
                    ->styleMarginTop('15px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom('1px', '#BBB')
                        )
                    )
                    ->styleMarginTop('5px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom('1px', '#BBB')
                        )
                    )
                    ->styleMarginTop('5px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom('1px', '#BBB')
                        )
                    )
                    ->styleMarginTop('5px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom('1px', '#BBB')
                        )
                    )
                    ->styleMarginTop('5px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom('1px', '#BBB')
                        )
                    )
                    ->styleMarginTop('5px')
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
                            , '23%')
                        ->addElementColumn((new Element())
                            , '5%')
                        ->addElementColumn((new Element())
                            , '30%')
                        ->addElementColumn((new Element())
                            , '5%')
                        ->addElementColumn((new Element())
                            , '30%')
                    )
                    ->styleMarginTop('30px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleAlignCenter()
                            ->styleBorderBottom('1px', '#000')
                            , '30%')
                        ->addElementColumn((new Element())
                            , '40%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleAlignCenter()
                            ->styleBorderBottom('1px', '#000')
                            , '30%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Schulleiter(in)')
                            ->styleAlignCenter()
                            ->styleTextSize('11px')
                            , '30%')
                        ->addElementColumn((new Element())
                            , '5%')
                        ->addElementColumn((new Element())
                            ->setContent('Dienstsiegel der Schule')
                            ->styleAlignCenter()
                            ->styleTextSize('11px')
                            , '30%')
                        ->addElementColumn((new Element())
                            , '5%')
                        ->addElementColumn((new Element())
                            ->setContent('Klassenlehrer(in)')
                            ->styleAlignCenter()
                            ->styleTextSize('11px')
                            , '30%')
                    )
                    ->styleMarginTop('30px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Zur Kenntnis genommen:')
                            , '30%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp')
                            ->styleBorderBottom()
                            , '40px')
                        ->addElementColumn((new Element())
                            , '30%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '30%')
                        ->addElementColumn((new Element())
                            ->setContent('Eltern')
                            ->styleAlignCenter()
                            ->styleTextSize('11px')
                            , '40px')
                        ->addElementColumn((new Element())
                            , '30%')
                    )->styleMarginTop('30px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->styleBorderBottom()
                            , '30%')
                        ->addElementColumn((new Element())
                            , '70%')
                    )->styleMarginTop('152px')
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Notenerläuterung:'
                                .new Container('1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft;
                                         6 = ungenügend (6 = ungenügend nur bei der Bewertung der Leistungen)')
                                .new Container('¹ &nbsp;&nbsp;&nbsp; Zutreffendes ist zu unterstreichen.')
                                .new Container('² &nbsp;&nbsp;&nbsp; Gilt nicht an Gymnasien mit vertiefter Ausbildung gemäß § 4 SOGYA')
                                .new Container('³ &nbsp;&nbsp;&nbsp; In Klassenstufe 8 ist der Zusatz " mit informatischer Bildung" zu streichen.')
                                .new Container('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Beim spreachlichen Profil ist der Zusatz "mit informatischer Bildung" zu streichen und die Fremdsprache anzugeben.')
                                .new Container('⁴ &nbsp;&nbsp;&nbsp; Nur für Schüler mit vertiefter Ausbildung gemäß § 4 SOGYA'))
                            ->styleTextSize('9.5px')
                            , '30%')
                    )
                )
            )
        );
    }
}