<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 08.03.2017
 * Time: 09:18
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\StudentCard;

use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class PrimarySchool
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository
 */
class PrimarySchool extends AbstractStudentCard
{

    /**
     * @return string
     */
    public function getName()
    {

        return 'Schülerkartei - Grundschule';
    }

    /**
     * @return Frame
     */
    public function buildDocument()
    {

        $SmallTextSize = '7px';
        $InputText = '12px';
        $OutLines = '1.2px';
        $InnerLines = '0.5px';
        $HeightThreeLineBlock = '74px';
        $HeightTwoLineBlock = '20px';

        $padding = '4.7px';

        $SpaceBetween = '100px';

        $subjectPosition = array();

        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSlice($this->setLetterRow())
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Familienname')
                                    ->stylePaddingLeft('4px')
                                    ->styleTextSize($SmallTextSize)
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('
                                            {% if( Content.Person.Data.Name.Last is not empty) %}
                                                {{ Content.Person.Data.Name.Last }}
                                            {% else %}
                                                &nbsp;
                                            {% endif %}')
                                    ->stylePaddingLeft('4px')
                                    ->stylePaddingTop('1px')
                                    ->styleTextSize($InputText)
                                    ->styleHeight('28px')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Geschlecht')
                                    ->stylePaddingLeft('4px')
                                    ->styleBorderTop($InnerLines)
                                    ->styleTextSize($SmallTextSize)
                                    , '50%')
                                ->addElementColumn((new Element())
                                    ->setContent('Geburtsort')
                                    ->stylePaddingLeft('4px')
                                    ->styleTextSize($SmallTextSize)
                                    ->styleBorderTop($InnerLines)
                                    ->styleBorderLeft($InnerLines)
                                    , '50%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('
                                            {% if Content.Person.Common.BirthDates.Gender == 1 %}
                                                männlich
                                            {% else %}
                                                {% if Content.Person.Common.BirthDates.Gender == 2 %}
                                                    weiblich
                                                {% else %}
                                                    &nbsp;
                                                {% endif %}
                                            {% endif %}
                                        ')
                                    ->stylePaddingLeft('4px')
                                    ->stylePaddingTop('1px')
                                    ->styleTextSize($InputText)
                                    ->styleHeight('18px')
                                    , '50%')
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if(Content.Person.Common.BirthDates.Birthday is not empty) %}
                                            {{ Content.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                        ')
                                    ->stylePaddingLeft('4px')
                                    ->stylePaddingTop('1px')
                                    ->styleTextSize($InputText)
                                    ->styleBorderLeft($InnerLines)
                                    ->styleHeight('18px')
                                    , '50%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Religionszugehörigkeit')
                                    ->stylePaddingLeft('4px')
                                    ->styleTextSize($SmallTextSize)
                                    ->styleBorderTop($InnerLines)
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('
                                            {% if(Content.Person.Common.Denomination is not empty) %}
                                                {{ Content.Person.Common.Denomination }}
                                            {% else %}
                                                &nbsp;
                                            {% endif %}
                                            ')
                                    ->stylePaddingLeft('4px')
                                    ->stylePaddingTop('1px')
                                    ->styleTextSize($InputText)
                                    ->styleHeight('18px')
                                )
                            )
                            , '30%')
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Vorname')
                                    ->stylePaddingLeft('4px')
                                    ->styleTextSize($SmallTextSize)
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('
                                            {% if( Content.Person.Data.Name.First is not empty) %}
                                                {{ Content.Person.Data.Name.First }}
                                            {% else %}
                                                &nbsp;
                                            {% endif %}')
                                    ->stylePaddingLeft('4px')
                                    ->stylePaddingTop('1px')
                                    ->styleTextSize($InputText)
                                    ->styleHeight('28px')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Geburtsort')
                                    ->stylePaddingLeft('4px')
                                    ->styleTextSize($SmallTextSize)
                                    ->styleBorderTop($InnerLines)
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('
                                         {% if(Content.Person.Common.BirthDates.Birthplace is not empty) %}
                                            {{ Content.Person.Common.BirthDates.Birthplace }}
                                         {% else %}
                                            &nbsp;
                                         {% endif %}
                                    ')
                                    ->stylePaddingLeft('4px')
                                    ->stylePaddingTop('1px')
                                    ->styleTextSize($InputText)
                                    ->styleHeight('18px')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Staatsangehörigkeit*')
                                    ->stylePaddingLeft('4px')
                                    ->styleTextSize($SmallTextSize)
                                    ->styleBorderTop($InnerLines)
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if(Content.Person.Common.Nationality is not empty) %}
                                            {{ Content.Person.Common.Nationality }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->stylePaddingLeft('4px')
                                    ->stylePaddingTop('1px')
                                    ->styleTextSize($InputText)
                                    ->styleHeight('18px')
                                )
                            )
                            ->styleBorderLeft($InnerLines)
                            ->styleBorderRight($OutLines)
                            , '30%')
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Schule')
                                    ->stylePaddingLeft('4px')
                                    ->styleTextSize($SmallTextSize)
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('
                                            {% if(Content.Student.Company is not empty) %}
                                                {{ Content.Student.Company }}
                                                {% if(Content.Student.Company2 is not empty) %}
                                                    <br/> {{ Content.Student.Company2 }}
                                                {% else %}
                                                    &nbsp;
                                                {% endif %}
                                            {% else %}
                                                &nbsp;
                                            {% endif %}
                                    ')
                                    ->stylePaddingLeft('4px')
                                    ->stylePaddingTop('1px')
                                    ->styleHeight('81px')
                                    ->styleTextSize($InputText)
                                )
                            )
                            , '40%')
                    )
                    ->styleBorderLeft($OutLines)
                    ->styleBorderTop($OutLines)
                    ->styleBorderRight($OutLines)
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Familienname und Vorname der Eltern')
                                    ->stylePaddingLeft('4px')
                                    ->styleTextSize($SmallTextSize)
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('
                                         {% if(Content.Person.Parent.Mother.Name.Last is not empty) %}
                                            {% if(Content.Person.Parent.Father.Name.Last is not empty) %}
                                                {{ Content.Person.Parent.Mother.Name.Last }}, {{ Content.Person.Parent.Mother.Name.First }}
                                                </br>
                                                {{ Content.Person.Parent.Father.Name.Last }}, {{ Content.Person.Parent.Father.Name.First }}
                                            {% else %}
                                                {{ Content.Person.Parent.Mother.Name.Last }} {{ Content.Person.Parent.Mother.Name.First }}
                                            {% endif %}
                                        {% else %}
                                            {% if(Content.Person.Parent.Father.Name.Last is not empty) %}
                                                {{ Content.Person.Parent.Father.Name.Last }} {{ Content.Person.Parent.Father.Name.First }}
                                                {% else %}
                                                &nbsp;
                                            {% endif %}
                                        {% endif %}
                                    ')
                                    ->stylePaddingLeft('4px')
                                    ->stylePaddingTop('1px')
                                    ->styleTextSize($InputText)
                                    ->styleHeight('35px')
                                )
                            )
                            , '30%')
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Anschrift')
                                    ->stylePaddingLeft('4px')
                                    ->styleTextSize($SmallTextSize)
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('
                                         {% if(Content.Person.Address.Street.Name) %}
                                            {{ Content.Person.Address.Street.Name }}
                                            {{ Content.Person.Address.Street.Number }}
                                            </br>
                                            {{ Content.Person.Address.City.Code }}
                                            {{ Content.Person.Address.City.Name }}
                                        {% else %}
                                              &nbsp;
                                        {% endif %}
                                    ')
                                    ->stylePaddingLeft('4px')
                                    ->stylePaddingTop('1px')
                                    ->styleTextSize($InputText)
                                    ->styleHeight('35px')
                                )
                            )
                            ->styleBorderLeft($InnerLines)
                            ->styleBorderRight($OutLines)
                            , '30%')
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Telefonnummer')
                                    ->stylePaddingLeft('4px')
                                    ->styleTextSize($SmallTextSize)
                                    , '50%')
                                ->addElementColumn((new Element())
                                    ->setContent('Notfallnummer')
                                    ->stylePaddingLeft('4px')
                                    ->styleTextSize($SmallTextSize)
                                    ->styleBorderLeft($OutLines)
                                    , '50%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if(Content.Person.Contact.Phone.Number is not empty) %}
                                            {{ Content.Person.Contact.Phone.Number }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->stylePaddingLeft('4px')
                                    ->stylePaddingTop('1px')
                                    ->styleTextSize($InputText)
                                    ->styleHeight('35px')
                                    , '50%')
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if(Content.Person.Contact.Phone.Emergency1 is not empty) %}
                                            {{ Content.Person.Contact.Phone.Emergency1 }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->stylePaddingLeft('4px')
                                    ->stylePaddingTop('1px')
                                    ->styleTextSize($InputText)
                                    ->styleBorderLeft($OutLines)
                                    ->styleHeight('35px')
                                    , '50%')
                            )
                            , '40%')
                    )
                    ->styleBorderTop($OutLines)
                    ->styleBorderLeft($OutLines)
                    ->styleBorderRight($OutLines)
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Beginn der Schulpflicht')
                            ->styleTextSize($InputText)
                            ->stylePaddingLeft('4px')
                            ->stylePaddingTop($padding)
                            ->stylePaddingBottom($padding)
                            ->styleBorderLeft($OutLines)
                            , '25%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Student.School.Attendance.Date is not empty) %}
                                    {{ Content.Student.School.Attendance.Date }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                            ->styleTextSize($InputText)
                            ->stylePaddingLeft('4px')
                            ->stylePaddingTop($padding)
                            ->stylePaddingBottom($padding)
                            ->styleBorderLeft($InnerLines)
                            , '25%')
                        ->addElementColumn((new Element())
                            ->setContent('Einschulung am')
                            ->styleTextSize($InputText)
                            ->stylePaddingLeft('4px')
                            ->stylePaddingTop($padding)
                            ->stylePaddingBottom($padding)
                            ->styleBorderLeft($OutLines)
                            , '25%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Student.School.Enrollment.Date is not empty) %}
                                    {{ Content.Student.School.Enrollment.Date }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                            ->styleTextSize($InputText)
                            ->stylePaddingLeft('4px')
                            ->stylePaddingTop($padding)
                            ->stylePaddingBottom($padding)
                            ->styleBorderLeft($InnerLines)
                            ->styleBorderRight($OutLines)
                            , '25%')
                    )
                    ->styleBorderTop($OutLines)
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Zurückstellung')
                            ->styleTextSize($InputText)
                            ->stylePaddingLeft('4px')
                            ->stylePaddingTop($padding)
                            ->stylePaddingBottom($padding)
                            ->styleBorderLeft($OutLines)
                            ->styleBorderTop($InnerLines)
                            , '25%')
                        ->addSliceColumn(
                            $this->setCheckBox()
                                ->styleBorderLeft($InnerLines)
                                ->styleBorderTop($InnerLines)
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('ja')
                            ->styleTextSize($InputText)
                            ->stylePaddingLeft('4px')
                            ->stylePaddingTop($padding)
                            ->stylePaddingBottom($padding)
                            ->styleBorderTop($InnerLines)
                            , '8%')
                        ->addSliceColumn(
                            $this->setCheckBox()
                                ->styleBorderTop($InnerLines)
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('nein')
                            ->styleTextSize($InputText)
                            ->stylePaddingLeft('4px')
                            ->stylePaddingTop($padding)
                            ->stylePaddingBottom($padding)
                            ->styleBorderTop($InnerLines)
                            , '9%')
                        ->addElementColumn((new Element())
                            ->setContent('Schuljahr')
                            ->styleTextSize($InputText)
                            ->stylePaddingLeft('4px')
                            ->stylePaddingTop($padding)
                            ->stylePaddingBottom($padding)
                            ->styleBorderLeft($OutLines)
                            ->styleBorderTop($InnerLines)
                            , '25%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Student.School.Enrollment.Year is not empty) %}
                                    {{ Content.Student.School.Enrollment.Year }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                            ->styleTextSize($InputText)
                            ->stylePaddingLeft('4px')
                            ->stylePaddingTop($padding)
                            ->stylePaddingBottom($padding)
                            ->styleBorderLeft($InnerLines)
                            ->styleBorderRight($OutLines)
                            ->styleBorderTop($InnerLines)
                            , '25%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Vorzeitige Einschulung')
                            ->styleTextSize($InputText)
                            ->stylePaddingLeft('4px')
                            ->stylePaddingTop($padding)
                            ->stylePaddingBottom($padding)
                            ->styleBorderLeft($OutLines)
                            ->styleBorderTop($InnerLines)
                            , '25%')
                        ->addSliceColumn(
                            $this->setCheckBox()
                                ->styleBorderLeft($InnerLines)
                                ->styleBorderTop($InnerLines)
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('ja')
                            ->styleTextSize($InputText)
                            ->stylePaddingLeft('4px')
                            ->stylePaddingTop($padding)
                            ->stylePaddingBottom($padding)
                            ->styleBorderTop($InnerLines)
                            , '8%')
                        ->addSliceColumn(
                            $this->setCheckBox()
                                ->styleBorderTop($InnerLines)
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('nein')
                            ->styleTextSize($InputText)
                            ->stylePaddingLeft('4px')
                            ->stylePaddingTop($padding)
                            ->stylePaddingBottom($padding)
                            ->styleBorderTop($InnerLines)
                            , '9%')
                        ->addElementColumn((new Element())
                            ->setContent('Vorbereitungsklasse')
                            ->styleTextSize($InputText)
                            ->stylePaddingLeft('4px')
                            ->stylePaddingTop($padding)
                            ->stylePaddingBottom($padding)
                            ->styleBorderLeft($OutLines)
                            ->styleBorderTop($InnerLines)
                            , '25%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                von - bis
                            ')
                            ->styleTextSize($SmallTextSize)
                            ->styleAlignCenter()
                            ->styleBorderLeft($InnerLines)
                            ->styleBorderRight($OutLines)
                            ->styleBorderTop($InnerLines)
                            ->styleHeight('24px')
                            , '25%')
                    )
                )


                // Todo DocumentSetting nach ConsumerSetting verschieben

                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addSliceColumn((new Slice())
                            ->addElement((new Element())
                                ->setContent('Beginn der Schulpflicht: Zurückstellung / vorzeitige Einschulung / Aufnahme¹')//ToDO auslesen aus der Schülerakte (später)
                                ->styleTextSize('13.5px')
                                ->stylePaddingTop($HeightTwoLineBlock)
                                ->stylePaddingLeft('4px')
                                ->styleBorderTop('0.5px')
                                ->styleBorderRight('0.5px')
                                ->styleBorderLeft($OutLines)
                                ->styleHeight('36px')
                            )
                            , '70%')
                        ->addSliceColumn((new Slice())
                            ->addElement((new Element())
                                ->setContent('Einschulung am')
                                ->stylePaddingLeft('4px')
                                ->styleTextSize($SmallTextSize)
                                ->styleBorderTop('0.5px')
                                ->styleBorderRight($OutLines)
                            )
                            ->addElement((new Element())
                                ->setContent('
                                {% if(Content.Student.School.Attendance.Date is not empty) %}
                                    {{ Content.Student.School.Attendance.Date }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                                ')
                                ->stylePaddingLeft('4px')
                                ->stylePaddingTop('1px')
                                ->styleTextSize($InputText)
                                ->styleHeight('18.5px')
                                ->styleBorderRight($OutLines)
                            )
                            ->addElement((new Element())
                                ->setContent('Schuljahr')
                                ->stylePaddingLeft('4px')
                                ->styleTextSize($SmallTextSize)
                                ->styleBorderTop('0.5px')
                                ->styleBorderRight($OutLines)
                            )
                            ->addElement((new Element())
                                ->setContent('
                                {% if(Content.Student.School.Attendance.Year is not empty) %}
                                    {{ Content.Student.School.Attendance.Year }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                                ')
                                ->stylePaddingLeft('4px')
                                ->stylePaddingTop('1px')
                                ->styleTextSize($InputText)
                                ->styleHeight('18px')
                                ->styleBorderRight($OutLines)
                            )
                            , '30%')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Bildungsempfehlung für Mittelschule / Gymnasium¹: Aufnahmeprüfung Ja/Nein¹')//ToDO auslesen aus der Schülerakte (später)
                            ->styleTextSize('13.5px')
                            ->stylePaddingTop('6px')
                            ->stylePaddingLeft('4px')
                            ->styleBorderTop('0.5px')
                            ->styleBorderRight('0.5px')
                            ->styleBorderLeft($OutLines)
                            ->styleHeight('22px')
                            , '70%')
                        ->addSliceColumn((new Slice())
                            ->addElement((new Element())
                                ->setContent('Schuljahr')
                                ->stylePaddingLeft('4px')
                                ->styleTextSize($SmallTextSize)
                                ->styleBorderTop('0.5px')
                                ->styleBorderRight($OutLines)
                            )
                            ->addElement((new Element())
//                                ->setContent('2020/21') //ToDO füllen ?
                                ->stylePaddingLeft('4px')
                                ->stylePaddingTop('1px')
                                ->styleTextSize($InputText)
                                ->styleBorderRight($OutLines)
                                ->styleHeight('18.5px')
                            )
                            , '30%')
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Besuchte Schulen (von/bis)')//ToDO auslesen aus der Historie (später)
                        ->stylePaddingLeft('4px')
                        ->stylePaddingTop('5px')
                        ->styleBorderTop('0.5px')
                        ->styleBorderRight($OutLines)
                        ->styleBorderLeft($OutLines)
                        ->styleHeight('21px')
                    )
                    ->addElement((new Element())
                        ->styleBorderTop('0.5px')
                        ->styleBorderRight($OutLines)
                        ->styleBorderLeft($OutLines)
                        ->styleHeight('26px')
                    )
                    ->addElement((new Element())
                        ->styleBorderTop('0.5px')
                        ->styleBorderRight($OutLines)
                        ->styleBorderLeft($OutLines)
                        ->styleHeight('26px')
                    )
                    ->addElement((new Element())
                        ->styleBorderTop('0.5px')
                        ->styleBorderRight($OutLines)
                        ->styleBorderLeft($OutLines)
                        ->styleHeight('26px')
                    )
                    ->addElement((new Element())
                        ->styleBorderTop('0.5px')
                        ->styleBorderRight($OutLines)
                        ->styleBorderLeft($OutLines)
                        ->styleHeight('26px')
                    )
                    ->addElement((new Element())
                        ->styleBorderTop('0.5px')
                        ->styleBorderRight($OutLines)
                        ->styleBorderLeft($OutLines)
                        ->styleBorderBottom($OutLines)
                        ->styleHeight('26px')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('¹ Zutreffendes ist zu unterstreichen')
                            ->stylePaddingTop()
                            ->styleTextSize('6px')
                            , '20%')
                        ->addElementColumn((new Element())
                            ->setContent('* mit Einwilligung der Eltern')
                            ->stylePaddingTop()
                            ->styleTextSize('6px')
                            , '20%')
                        ->addElementColumn((new Element())
                            , '60%')
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('&nbsp;')
                        ->stylePaddingTop($SpaceBetween)
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('GRUNDSCHULE')
                        ->stylePaddingBottom('5px')
                        ->styleTextSize('18px')
                        ->styleTextBold()
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->styleHeight('15px')
                    )
                )
                ->addSliceArray($this->setGradeLayoutHeader($subjectPosition))
                ->addSliceArray($this->setGradeLayoutBody($subjectPosition))
            )
        );
    }
}