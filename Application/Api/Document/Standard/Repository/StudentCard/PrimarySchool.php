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
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;

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
     * @return int
     */
    public function getTypeId()
    {

        if (($tblType = Type::useService()->getTypeByName('Grundschule'))) {
            return $tblType->getId();
        } else {
            return 0;
        }
    }

    /**
     * @return false|TblType
     */
    public function getType()
    {

        return Type::useService()->getTypeByName('Grundschule');
    }

    /**
     * @return Page
     */
    public function buildPage()
    {

        $SmallTextSize = '7px';
        $InputText = '12px';
        $OutLines = '1.2px';
        $InnerLines = '0.5px';
        $padding = '4.7px';
        $SpaceBetween = '90px';

        $subjectPosition = array();

        return (new Page())
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
                                ->setContent('Geburtsdatum')
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
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Aufnahme')
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
                        ->setContent('Grundschulpflicht beendet')
                        ->styleTextSize($InputText)
                        ->stylePaddingLeft('4px')
                        ->stylePaddingTop($padding)
                        ->stylePaddingBottom($padding)
                        ->styleBorderLeft($OutLines)
                        ->styleBorderTop($InnerLines)
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('
                                &nbsp;
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
            )
            ->addSlice((new Slice())
                ->styleBorderTop($OutLines)
                ->styleBorderLeft($OutLines)
                ->styleBorderRight($OutLines)
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Bildungsempfehlung für')
                        ->styleTextSize($InputText)
                        ->stylePaddingLeft('4px')
                        ->stylePaddingTop('17px')
                        ->stylePaddingBottom('17px')
                        , '25%')
                    ->addSliceColumn((new Slice())
                        ->addSection((new Section())
                            ->addSliceColumn($this->setCheckBox(), '4%')
                            ->addElementColumn((new Element())
                                ->setContent('Mittelschule')
                                ->styleTextSize($InputText)
                                ->stylePaddingLeft('4px')
                                ->stylePaddingTop($padding)
                                ->stylePaddingBottom($padding)
                                , '21%')
                        )
                        ->addSection((new Section())
                            ->addSliceColumn($this->setCheckBox(), '4%')
                            ->addElementColumn((new Element())
                                ->setContent('Gymnasium')
                                ->styleTextSize($InputText)
                                ->stylePaddingLeft('4px')
                                ->stylePaddingTop($padding)
                                ->stylePaddingBottom($padding)
                                , '21%')
                        )
                        , '25%')
                    ->addSliceColumn((new Slice())
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Aufnahmeprüfung')
                                ->styleTextSize($InputText)
                                ->stylePaddingLeft('4px')
                                ->stylePaddingTop($padding)
                                ->stylePaddingBottom($padding)
                                ->styleBorderLeft($OutLines)
                            )
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Schuljahr')
                                ->styleTextSize($InputText)
                                ->stylePaddingLeft('4px')
                                ->stylePaddingTop($padding)
                                ->stylePaddingBottom($padding)
                                ->styleBorderLeft($OutLines)
                            )
                        )
                        , '25.1%')
                    ->addSliceColumn((new Slice())
                        ->addSection((new Section())
                            ->addSliceColumn(
                                $this->setCheckBox()
                                    ->styleBorderLeft($InnerLines)
                                , '4%')
                            ->addElementColumn((new Element())
                                ->setContent('ja')
                                ->styleTextSize($InputText)
                                ->stylePaddingLeft('4px')
                                ->stylePaddingTop($padding)
                                ->stylePaddingBottom($padding)
                                , '8%')
                            ->addSliceColumn(
                                $this->setCheckBox()
                                , '4%')
                            ->addElementColumn((new Element())
                                ->setContent('nein')
                                ->styleTextSize($InputText)
                                ->stylePaddingLeft('4px')
                                ->stylePaddingTop($padding)
                                ->stylePaddingBottom($padding)
                                , '9%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                ->styleTextSize($InputText)
                                ->stylePaddingLeft('4px')
                                ->stylePaddingTop($padding)
                                ->stylePaddingBottom($padding)
                                ->styleBorderLeft($InnerLines)
                                ->styleBorderTop($InnerLines)
                            )
                        )
                    )
                )
            )
            ->addSlice((new Slice())
                ->styleBorderLeft($OutLines)
                ->styleBorderTop($OutLines)
                ->styleBorderRight($OutLines)
                ->styleBorderBottom($OutLines)
                ->addSection((new Section())
                    ->addSliceColumn((new Slice())
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Besuchte Schulen')
                                ->stylePaddingLeft('4px')
                                ->styleTextSize($SmallTextSize)
                            )
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                ->stylePaddingLeft('4px')
                                ->stylePaddingTop('1px')
                                ->styleTextSize($InputText)
                            )
                        )
                        , '75%')
                    ->addElementColumn((new Element())
                        ->setContent('von - bis')
                        ->styleTextSize($SmallTextSize)
                        ->styleAlignCenter()
                        , '25%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleTextSize($InputText)
                        ->stylePaddingLeft('4px')
                        ->stylePaddingTop($padding)
                        ->stylePaddingBottom($padding)
                        ->styleBorderTop($InnerLines)
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleTextSize($InputText)
                        ->stylePaddingLeft('4px')
                        ->stylePaddingTop($padding)
                        ->stylePaddingBottom($padding)
                        ->styleBorderTop($InnerLines)
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleTextSize($InputText)
                        ->stylePaddingLeft('4px')
                        ->stylePaddingTop($padding)
                        ->stylePaddingBottom($padding)
                        ->styleBorderTop($InnerLines)
                    )
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('* mit Einwilligung der Eltern')
                        ->stylePaddingTop()
                        ->styleTextSize('6px')
                        ->styleAlignRight()
                    )
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('&nbsp;')
                    ->stylePaddingTop($SpaceBetween)
                )
            )
            ->addSlice((new Slice())
                ->styleBorderAll($OutLines)
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Grundschule')
                        ->stylePaddingTop('5px')
                        ->stylePaddingBottom('5px')
                        ->stylePaddingLeft('5px')
                        ->stylePaddingRight('5px')
                        ->styleTextSize('18px')
                        ->styleTextBold()
                        ->styleBorderRight($OutLines)
                        , '18%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->stylePaddingTop('5px')
                        ->stylePaddingBottom('5px')
                        ->stylePaddingLeft('5px')
                        ->stylePaddingRight('5px')
                        ->styleTextSize('18px')
                    )
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->styleHeight('8px')
                )
            )
            ->addSliceArray($this->setGradeLayoutHeader($subjectPosition))
            ->addSliceArray($this->setGradeLayoutBody($subjectPosition, $this->getTypeId()));
    }

    /**
     * @param array $pageList
     *
     * @return Frame
     */
    public function buildDocument($pageList = array())
    {
        return (new Frame())->addDocument((new Document())
            ->addPage($this->buildPage())
            ->addPage($this->buildRemarkPage($this->getType() ? $this->getType() : null))
        );
    }
}