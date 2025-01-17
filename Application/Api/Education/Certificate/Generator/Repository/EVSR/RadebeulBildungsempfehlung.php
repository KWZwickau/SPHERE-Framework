<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 02.11.2016
 * Time: 09:52
 */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVSR;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Layout\Repository\Container;

/**
 * Class RadebeulBildungsempfehlung
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVSR
 */
class RadebeulBildungsempfehlung extends Certificate
{

    /**
     * @return array
     */
    public function selectValuesType()
    {
        return array(
            1 => "der Halbjahresinformation",
            2 => "der für das Jahreszeugnis vorgesehene Noten gemäß Beschluss der Klassenkonferenz"
        );
    }

    /**
     * @return array
     */
    public function selectValuesSchoolType()
    {
        return array(
            1 => "am Gymnasium",
            2 => "an der " . TblType::IDENT_OBER_SCHULE
        );
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        return (new Page())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Name der Schule:')
                        ->stylePaddingTop('20px')
                        ->stylePaddingLeft('5px')
                        ->styleTextSize('11px')
                        , '14%')
                    ->addSliceColumn((new Slice())
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('{% if( Content.P' . $personId . '.Company.Data.Name is not empty) %}
                                        {{ Content.P' . $personId . '.Company.Data.Name }}
                                    {% else %}
                                        Evangelisches Schulzentrum Radebeul
                                    {% endif %}')
                                ->styleAlignCenter()
                            )
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent(
                                    '- Staatlich anerkannte Ersatzschule in freier Trägerschaft -'
                                )
                                ->styleAlignCenter()
                            )
                        )
                        , '56%'
                    )
                    ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                        '165px', '50px'))
                        ->styleAlignCenter()
                        , '30%')
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Bildungsempfehlung in der Klassenstufe 4')
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
                        ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                                          {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                        ->stylePaddingTop()
                        ->stylePaddingLeft()
                        ->styleBorderBottom()
                        , '49%')
                    ->addElementColumn((new Element())
                        , '2%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Division.Data.Level.Name }}')
                        ->stylePaddingTop()
                        ->stylePaddingLeft()
                        ->styleBorderBottom()
                        , '24%')
                    ->addElementColumn((new Element())
                        , '2%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Division.Data.Year }}')
                        ->stylePaddingTop()
                        ->stylePaddingLeft()
                        ->styleBorderBottom()
                        , '23%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Vorname und Name')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->stylePaddingLeft()
                        ->styleTextSize('11px')
                        , '49%')
                    ->addElementColumn((new Element())
                        , '2%')
                    ->addElementColumn((new Element())
                        ->setContent('Klasse')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->stylePaddingLeft()
                        ->styleTextSize('11px')
                        , '24%')
                    ->addElementColumn((new Element())
                        , '2%')
                    ->addElementColumn((new Element())
                        ->setContent('Schuljahr')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->stylePaddingLeft()
                        ->styleTextSize('11px')
                        , '23%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthday is not empty) %}
                                    {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->stylePaddingLeft('5px')
                        ->styleBorderBottom()
                        , '49%')
                    ->addElementColumn((new Element())
                        , '2%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthplace is not empty) %}
                                    {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthplace }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->stylePaddingLeft('5px')
                        ->styleBorderBottom()
                        , '49%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('geboren am')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->stylePaddingLeft()
                        ->styleTextSize('11px')
                        , '49%')
                    ->addElementColumn((new Element())
                        , '2%')
                    ->addElementColumn((new Element())
                        ->setContent('in')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->stylePaddingLeft()
                        ->styleTextSize('11px')
                        , '49%')
                )
                ->addElement((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Person.Address.City.Name) %}
                                    {{ Content.P' . $personId . '.Person.Address.Street.Name }}
                                    {{ Content.P' . $personId . '.Person.Address.Street.Number }},
                                    {{ Content.P' . $personId . '.Person.Address.City.Code }}
                                    {{ Content.P' . $personId . '.Person.Address.City.Name }}
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                    ->stylePaddingTop()
                    ->stylePaddingBottom()
                    ->stylePaddingLeft('5px')
                    ->styleBorderBottom()
                )
                ->addElement((new Element())
                    ->setContent('wohnhaft in')
                    ->stylePaddingTop()
                    ->stylePaddingBottom()
                    ->stylePaddingLeft()
                    ->styleTextSize('11px')
                )
                ->addElement((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Person.Parent.CommaSeparated) %}
                            {{ Content.P' . $personId . '.Person.Parent.CommaSeparated }}
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
//                ->setContent('{% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 2 %}
//                                Die Schülerin kann ihre Ausbildung am Gymnasium fortsetzen.
//                            {% else %}
//                                {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 1 %}
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
                            {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 2 %}
                                    Die Schülerin
                                {% else %}
                                    {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 1 %}
                                        Der Schüler
                                    {% else %}
                                      Die Schülerin/Der Schüler¹
                                    {% endif %}
                                {% endif %}
                            {% if Content.P' . $personId . '.Input.Type is not empty %}
                                  hat ausweislich {{ Content.P' . $personId . '.Input.Type }}
                            {% else %}
                                hat ausweislich der Halbjahresinformation /
                                der für das Jahreszeugnis vorgesehenen Noten gemäß Beschluss der Klassenkonferenz¹
                            {% endif %}
                                vom
                            {% if (Content.P' . $personId . '.Input.Type is not empty) 
                                and (Content.P' . $personId . '.Input.Type 
                                    == "der für das Jahreszeugnis vorgesehene Noten gemäß Beschluss der Klassenkonferenz") %}
                                {% if(Content.P' . $personId . '.Input.DateConference is not empty) %}
                                    {{ Content.P' . $personId . '.Input.DateConference }}
                                {% else %}
                                    ______________
                                {% endif %}
                            {% else %}
                                {% if(Content.P' . $personId . '.Input.DateCertifcate is not empty) %}
                                    {{ Content.P' . $personId . '.Input.DateCertifcate }}
                                {% else %}
                                    ______________
                                {% endif %}
                            {% endif %}
                            folgende Leistungen erreicht:
                            ')
                        ->stylePaddingBottom('25px')
                    )
                )
            )
            ->addSlice($this->getSubjectLanes($personId, true, array(), '14px', false))
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Durchschnitt der Noten aus den angegebenen Fächern')
                        ->styleMarginTop('20px')
                        , '91%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Grade.Data.Average is not empty) %}
                                    {{ Content.P' . $personId . '.Grade.Data.Average }}
                                {% else %}
                                    ---
                                {% endif %}')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->styleMarginTop('20px')
                        ->styleAlignCenter()
                        ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
                        , '9%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '91%')
                    ->addElementColumn((new Element())
                        ->stylePaddingTop()
                        ->setContent('(in Ziffern)')
                        ->styleTextSize('9px')
                        ->styleAlignCenter()
                        , '9%')
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('2. Gutachten²')
                    ->styleTextSize('20px')
                    ->styleTextBold()
                    ->stylePaddingBottom('5px')
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Input.Survey is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Survey|nl2br }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                    ->styleHeight('200px')
                    ->stylePaddingBottom()
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Auf Grund des Leistungsstandes und des Gutachtens wird
                        {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 2 %}
                                der Schülerin
                            {% else %}
                                {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 1 %}
                                    dem Schüler
                                {% else %}
                                    der Schülerin/dem Schüler¹
                                {% endif %}
                            {% endif %} empfohlen,')
                    ->styleMarginTop('15px')
                )
                ->addElement((new Element())
                    ->setContent('{% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 2 %}
                                ihre Ausbildung
                                {% if(Content.P' . $personId . '.Input.SchoolType is not empty) %}
                                {{ Content.P' . $personId . '.Input.SchoolType }}
                                {% else %}
                                    ________________________________
                                {% endif %}
                                fortzusetzen.
                            {% else %}
                                {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 1 %}
                                    seine Ausbildung
                                    {% if(Content.P' . $personId . '.Input.SchoolType is not empty) %}
                                    {{ Content.P' . $personId . '.Input.SchoolType }}
                                    {% else %}
                                        ________________________________
                                    {% endif %}
                                fortzusetzen.
                                {% else %}
                                    ihre/seine¹ Ausbildung
                                     {% if(Content.P' . $personId . '.Input.SchoolType is not empty) %}
                                    {{ Content.P' . $personId . '.Input.SchoolType }}
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
                            {% if(Content.P' . $personId . '.Input.DateConference is not empty) %}
                                {{ Content.P' . $personId . '.Input.DateConference }}
                            {% else %}
                                ______________
                            {% endif %}
                                beschlossen.
                                ')
                    ->styleMarginTop('13px')
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Datum:')
                        , '7%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Input.Date is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Date }}
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
                        ->styleMarginTop('80px')

                        , '35%')
                    ->addElementColumn((new Element())
                        ->setContent('Dienstsiegel der Schule')
                        ->styleTextSize('11px')
                        ->styleAlignCenter()
                        ->styleMarginTop('65px')
                        , '30%')
                    ->addElementColumn((new Element())
                        ->styleBorderBottom('1px', '#000')
                        ->styleAlignCenter()
                        ->styleMarginTop('80px')
                        , '35%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('
                                {% if(Content.P' . $personId . '.Headmaster.Description is not empty) %}
                                    {{ Content.P' . $personId . '.Headmaster.Description }}
                                {% else %}
                                    Schulleiter(in)
                                {% endif %}'
                        )
                        ->styleTextSize('11px')
                        ->stylePaddingTop('5px')
                        ->styleAlignCenter()
                        , '35%')
                    ->addElementColumn((new Element())
                        , '30%')
                    ->addElementColumn((new Element())
                        ->setContent('
                                {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                                    {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                                {% else %}
                                    Klassenlehrer(in)
                                {% endif %}'
                        )
                        ->styleTextSize('11px')
                        ->stylePaddingTop('5px')
                        ->styleAlignCenter()
                        , '35%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent(
                            '{% if(Content.P' . $personId . '.Headmaster.Name is not empty) %}
                                    {{ Content.P' . $personId . '.Headmaster.Name }}
                                {% else %}
                                    &nbsp;
                                {% endif %}'
                        )
                        ->styleTextSize('11px')
                        ->stylePaddingTop('3px')
                        ->styleAlignCenter()
                        , '35%')
                    ->addElementColumn((new Element())
                        , '30%')
                    ->addElementColumn((new Element())
                        ->setContent(
                            '{% if(Content.P' . $personId . '.DivisionTeacher.Name is not empty) %}
                                    {{ Content.P' . $personId . '.DivisionTeacher.Name }}
                                {% else %}
                                    &nbsp;
                                {% endif %}'
                        )
                        ->styleTextSize('11px')
                        ->stylePaddingTop('3px')
                        ->styleAlignCenter()
                        , '35%')
                )
                ->stylePaddingTop()
                ->stylePaddingBottom()
                ->styleMarginTop('13px')
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('
                            {% if(Content.P' . $personId . '.Input.SchoolType is empty) %}
                                ¹ Nichtzutreffendes streichen.
                            {% else %}
                                {% if(Content.P' . $personId . '.Person.Common.BirthDates.Gender is empty) %}
                                    ¹ Nichtzutreffendes streichen.
                                {% else %}
                                    
                                {% endif %}
                            {% endif %}'
                        . new Container('² Falls der Raum für Eintragungen nicht ausreicht, ist ein Beiblatt zu verwenden.')
                    )
                    ->styleTextSize('9px')
                    ->styleMarginTop('20px')
                )
            );
    }
}