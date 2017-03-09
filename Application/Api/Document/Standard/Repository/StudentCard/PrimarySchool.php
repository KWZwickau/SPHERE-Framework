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
        $HeightThreeLineBlock = '74px';
        $HeightTwoLineBlock = '20px';

        $SpaceBetween = '100px';

        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSlice($this->setLetterRow())
                ->addSlice(( new Slice() )
                    ->addSection(( new Section() )
                        ->addElementColumn(( new Element() )
                            ->setContent('Schülerkartei')
                            ->stylePaddingTop('10px')
                            ->stylePaddingBottom('5px')
                            ->styleTextSize('18px')
                            ->styleTextBold()
                        )
                    )
                )
                ->addSlice(( new Slice() )
                    ->addSection(( new Section() )
                        ->addSliceColumn(( new Slice() )
                            ->addSectionList(array(
                                ( new Section() )
                                    ->addElementColumn(( new Element() )
                                        ->setContent('Familienname/Vorname')
                                        ->stylePaddingLeft('4px')
                                        ->styleTextSize($SmallTextSize)
                                        ->styleBorderTop($OutLines)
                                        ->styleBorderLeft($OutLines)
                                    ),
                                ( new Section() )
                                    ->addElementColumn(( new Element() )
                                        ->setContent('
                                            {% if( Content.Person.Data.Name.First is not empty) %}
                                                {{ Content.Person.Data.Name.Last }} {{ Content.Person.Data.Name.First }}
                                            {% else %}
                                                &nbsp;
                                            {% endif %}')
                                        ->stylePaddingLeft('4px')
                                        ->stylePaddingTop('1px')
                                        ->styleTextSize($InputText)
                                        ->styleBorderBottom('0.5px')
                                        ->styleBorderLeft($OutLines)
                                        ->styleHeight('18px')
                                    ),
                                ( new Section() )
                                    ->addElementColumn(( new Element() )
                                        ->setContent('Anschrift')
                                        ->stylePaddingLeft('4px')
                                        ->styleTextSize($SmallTextSize)
                                        ->styleBorderLeft($OutLines)
                                    ),
                                ( new Section() )
                                    ->addElementColumn(( new Element() )
                                        ->setContent('
                                            {% if(Content.Person.Address.Street.Name) %}
                                                {{ Content.Person.Address.Street.Name }}
                                                {{ Content.Person.Address.Street.Number }}
                                                {{ Content.Person.Address.City.Code }}
                                                {{ Content.Person.Address.City.Name }}
                                            {% else %}
                                                  &nbsp;
                                            {% endif %}')
                                        ->stylePaddingLeft('4px')
                                        ->stylePaddingTop('1px')
                                        ->styleTextSize($InputText)
                                        ->styleBorderBottom('0.5px')
                                        ->styleBorderLeft($OutLines)
                                        ->styleHeight('18px')
                                    ),
                                ( new Section() )
                                    ->addElementColumn(( new Element() )
                                        ->setContent('Geschlecht')
                                        ->stylePaddingLeft('4px')
                                        ->styleTextSize($SmallTextSize)
                                        ->styleBorderLeft($OutLines)
                                        , '21%')
                                    ->addElementColumn(( new Element() )
                                        ->setContent('Geburtsdatum')
                                        ->stylePaddingLeft('4px')
                                        ->styleTextSize($SmallTextSize)
                                        ->styleBorderLeft('0.5px')
                                        , '21%')
                                    ->addElementColumn(( new Element() )
                                        ->setContent('Geburtsort')
                                        ->stylePaddingLeft('4px')
                                        ->styleTextSize($SmallTextSize)
                                        ->styleBorderLeft('0.5px')
                                        , '74%'),
                                ( new Section() )
                                    ->addElementColumn(( new Element() )
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
                                        ->styleBorderLeft($OutLines)
                                        ->styleHeight('18px')
                                        , '21%')
                                    ->addElementColumn(( new Element() )
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
                                        ->styleBorderLeft('0.5px')
                                        ->styleHeight('18px')
                                        , '21%')
                                    ->addElementColumn(( new Element() )
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
                                        ->styleBorderLeft('0.5px')
                                        ->styleHeight('18px')
                                        , '74%')
                            ))
                            , '70%')
                        ->addSliceColumn(( new Slice() )
                            ->addSection(( new Section() )
                                ->addElementColumn(( new Element() )
                                    ->setContent('Schule')
                                    ->stylePaddingLeft('4px')
                                    ->styleTextSize($SmallTextSize)
                                    ->styleBorderTop($OutLines)
                                    ->styleBorderRight($OutLines)
                                    ->styleBorderLeft('0.5px')
                                )
                            )
                            ->addSection(( new Section() )
                                ->addElementColumn(( new Element() )
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
                                    ->styleTextSize($InputText)
                                    ->styleBorderRight($OutLines)
                                    ->styleBorderLeft('0.5px')
                                    ->styleHeight($HeightThreeLineBlock)
                                )
                            )
                            , '30%')
                    )
                )
                ->addSlice(( new Slice() )
                    ->addSection(( new Section() )
                        ->addSliceColumn(( new Slice() )
                            ->addSection(( new Section() )
                                ->addElementColumn(( new Element() )
                                    ->setContent('Religionszugehörigkeit')
                                    ->stylePaddingLeft('4px')
                                    ->styleTextSize($SmallTextSize)
                                    ->styleBorderTop('0.5px')
                                    ->styleBorderLeft($OutLines)
                                    , '21%')
                                ->addElementColumn(( new Element() )
                                    ->setContent('Staatsangehörigkeit*')
                                    ->stylePaddingLeft('4px')
                                    ->styleTextSize($SmallTextSize)
                                    ->styleBorderTop('0.5px')
                                    ->styleBorderLeft('0.5px')
                                    , '21%')
                                ->addElementColumn(( new Element() )
                                    ->setContent('Telefon')
                                    ->stylePaddingLeft('4px')
                                    ->styleTextSize($SmallTextSize)
                                    ->styleBorderTop('0.5px')
                                    ->styleBorderLeft('0.5px')
                                    , '74%')
                            )
                            , '70%')
                        ->addSliceColumn(( new Slice() )
                            ->addElement(( new Element() )
                                ->setContent('Notfallnummer')
                                ->stylePaddingLeft('4px')
                                ->styleTextSize($SmallTextSize)
                                ->styleBorderTop('0.5px')
                                ->styleBorderLeft('0.5px')
                                ->styleBorderRight($OutLines)
                            )
                            , '30%')
                    )
                )
                ->addSlice(( new Slice() )
                    ->addSection(( new Section() )
                        ->addSliceColumn(( new Slice() )
                            ->addSection(( new Section() )
                                ->addElementColumn(( new Element() )
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
                                    ->styleBorderLeft($OutLines)
                                    ->styleHeight('18px')
                                    , '21%')
                                ->addElementColumn(( new Element() )
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
                                    ->styleBorderLeft('0.5px')
                                    ->styleHeight('18px')
                                    , '21%')
                                ->addElementColumn(( new Element() )
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
                                    ->styleBorderLeft('0.5px')
                                    ->styleHeight('18px')
                                    , '74%')
                            )
                            , '70%')
                        ->addSliceColumn(( new Slice() )
                            ->addElement(( new Element() )
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
                                ->styleBorderRight($OutLines)
                                ->styleBorderLeft('0.5px')
                                ->styleHeight('18px')
                            )
                            , '30%')
                    )
                )
                ->addSlice(( new Slice() )
                    ->addElement(( new Element() )
                        ->setContent('Familienname/Vorname der Eltern')
                        ->stylePaddingLeft('4px')
                        ->styleTextSize($SmallTextSize)
                        ->styleBorderTop('0.5px')
                        ->styleBorderLeft($OutLines)
                        ->styleBorderRight($OutLines)
                    )
                    ->addElement(( new Element() )
                        ->setContent('
                        {% if(Content.Person.Parent.Mother.Name.Last is not empty) %}
                            {% if(Content.Person.Parent.Father.Name.Last is not empty) %}
                                {{ Content.Person.Parent.Mother.Name.Last }} {{ Content.Person.Parent.Mother.Name.First }},
                                {{ Content.Person.Parent.Father.Name.Last }} {{ Content.Person.Parent.Father.Name.First }}
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
                        ->styleBorderLeft($OutLines)
                        ->styleBorderRight($OutLines)
                        ->styleHeight('18px')
                    )
                )
                ->addSlice(( new Slice() )
                    ->addSection(( new Section() )
                        ->addSliceColumn(( new Slice() )
                            ->addElement(( new Element() )
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
                        ->addSliceColumn(( new Slice() )
                            ->addElement(( new Element() )
                                ->setContent('Einschulung am')
                                ->stylePaddingLeft('4px')
                                ->styleTextSize($SmallTextSize)
                                ->styleBorderTop('0.5px')
                                ->styleBorderRight($OutLines)
                            )
                            ->addElement(( new Element() )
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
                            ->addElement(( new Element() )
                                ->setContent('Schuljahr')
                                ->stylePaddingLeft('4px')
                                ->styleTextSize($SmallTextSize)
                                ->styleBorderTop('0.5px')
                                ->styleBorderRight($OutLines)
                            )
                            ->addElement(( new Element() )
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
                ->addSlice(( new Slice() )
                    ->addSection(( new Section() )
                        ->addElementColumn(( new Element() )
                            ->setContent('Bildungsempfehlung für Mittelschule / Gymnasium¹: Aufnahmeprüfung Ja/Nein¹')//ToDO auslesen aus der Schülerakte (später)
                            ->styleTextSize('13.5px')
                            ->stylePaddingTop('6px')
                            ->stylePaddingLeft('4px')
                            ->styleBorderTop('0.5px')
                            ->styleBorderRight('0.5px')
                            ->styleBorderLeft($OutLines)
                            ->styleHeight('22px')
                            , '70%')
                        ->addSliceColumn(( new Slice() )
                            ->addElement(( new Element() )
                                ->setContent('Schuljahr')
                                ->stylePaddingLeft('4px')
                                ->styleTextSize($SmallTextSize)
                                ->styleBorderTop('0.5px')
                                ->styleBorderRight($OutLines)
                            )
                            ->addElement(( new Element() )
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
                ->addSlice(( new Slice() )
                    ->addElement(( new Element() )
                        ->setContent('Besuchte Schulen (von/bis)')//ToDO auslesen aus der Historie (später)
                        ->stylePaddingLeft('4px')
                        ->stylePaddingTop('5px')
                        ->styleBorderTop('0.5px')
                        ->styleBorderRight($OutLines)
                        ->styleBorderLeft($OutLines)
                        ->styleHeight('21px')
                    )
                    ->addElement(( new Element() )
                        ->styleBorderTop('0.5px')
                        ->styleBorderRight($OutLines)
                        ->styleBorderLeft($OutLines)
                        ->styleHeight('26px')
                    )
                    ->addElement(( new Element() )
                        ->styleBorderTop('0.5px')
                        ->styleBorderRight($OutLines)
                        ->styleBorderLeft($OutLines)
                        ->styleHeight('26px')
                    )
                    ->addElement(( new Element() )
                        ->styleBorderTop('0.5px')
                        ->styleBorderRight($OutLines)
                        ->styleBorderLeft($OutLines)
                        ->styleHeight('26px')
                    )
                    ->addElement(( new Element() )
                        ->styleBorderTop('0.5px')
                        ->styleBorderRight($OutLines)
                        ->styleBorderLeft($OutLines)
                        ->styleHeight('26px')
                    )
                    ->addElement(( new Element() )
                        ->styleBorderTop('0.5px')
                        ->styleBorderRight($OutLines)
                        ->styleBorderLeft($OutLines)
                        ->styleBorderBottom($OutLines)
                        ->styleHeight('26px')
                    )
                )
                ->addSlice(( new Slice() )
                    ->addSection(( new Section() )
                        ->addElementColumn(( new Element() )
                            ->setContent('¹ Zutreffendes ist zu unterstreichen')
                            ->stylePaddingTop()
                            ->styleTextSize('6px')
                            , '20%')
                        ->addElementColumn(( new Element() )
                            ->setContent('* mit Einwilligung der Eltern')
                            ->stylePaddingTop()
                            ->styleTextSize('6px')
                            , '20%')
                        ->addElementColumn(( new Element() )
                            , '60%')
                    )
                )
                ->addSlice(( new Slice() )
                    ->addElement(( new Element() )
                        ->setContent('&nbsp;')
                        ->stylePaddingTop($SpaceBetween)
                    )
                )
                ->addSlice(( new Slice() )
                    ->addElement(( new Element() )
                        ->setContent('GRUNDSCHULE')
                        ->stylePaddingBottom('5px')
                        ->styleTextSize('18px')
                        ->styleTextBold()
                    )
                )
                ->addSlice(( new Slice() )
                    ->addElement(( new Element() )
                        ->styleHeight('15px')
                    )
                )
                ->addSliceArray($this->setGradeLayoutHeader())
                ->addSliceArray($this->setGradeLayoutBody())
            )
        );
    }
}