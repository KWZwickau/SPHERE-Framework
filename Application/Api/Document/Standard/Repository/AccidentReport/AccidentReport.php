<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\AccidentReport;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Person\Person;

/**
 * Class AccidentReport
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository
 */
class AccidentReport extends AbstractDocument
{

    /**
     * AccidentReport constructor.
     *
     * @param array $Data
     */
    function __construct($Data)
    {

        $this->setFieldValue($Data);
    }

    /**
     * @var array
     */
    private $FieldValue = array();

    /**
     * @param $DataPost
     *
     * @return $this
     */
    private function setFieldValue($DataPost)
    {
        //Bsp.:
        // PersonGender
        $this->FieldValue['Gender'] = '';
        $this->FieldValue['PersonId'] = (isset($DataPost['PersonId']) && $DataPost['PersonId'] != '' ? $DataPost['PersonId'] : false);
        if ($this->FieldValue['PersonId'] && ($tblPerson = Person::useService()->getPersonById($this->FieldValue['PersonId']))) {
            if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
                if (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())) {
                    if (($tblGender = $tblCommonBirthDates->getTblCommonGender())) {
                        $this->FieldValue['Gender'] = $tblGender->getName();
                    }
                }
            }
        }
        // Header
        $this->FieldValue['LeaveSchool'] = (isset($DataPost['LeaveSchool']) && $DataPost['LeaveSchool'] != '' ? $DataPost['LeaveSchool'] : '&nbsp;');

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {

        return 'Unfallbericht';
    }

    /**
     *
     * @param array $pageList
     *
     * @return Frame
     */
    public function buildDocument($pageList = array())
    {
        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSlice((new Slice())
                    ->styleBorderAll()
                    ->styleHeight('920px')
                    ->addSection((new Section())
                        ->addSliceColumn((new Slice())
                            ->addElement((new Element())
                                ->styleHeight('46px')
                            )
                            ->addElement((new Element())
                                ->setContent('1 Name und Anschrift der Einrichtung (Tageseinrichtung, Schule, Hochschule)')
                                ->styleTextSize('11px')
                                ->stylePaddingLeft('5px')
                            )
                            ->addElement((new Element())
                                ->setContent('
                                {% if (Content.Student.Company is not empty) %}
                                    {{ Content.Student.Company }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                                {% if (Content.Student.Company2 is not empty) %}
                                    <br/>
                                    {{ Content.Student.Company2 }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                                <br/>
                                {% if (Content.Student.CompanyAddress is not empty) %}
                                    {{ Content.Student.CompanyAddress }}
                                {% else %}
                                    &nbsp;
                                {% endif %}

                                ')
                                ->styleHeight('100px')
                                ->stylePaddingLeft('20px')
                            )
                            ->addElement((new Element())
                                ->setContent('4 Empfänger')
                                ->styleTextSize('11px')
                                ->stylePaddingLeft('5px')
                                ->stylePaddingBottom('5px')
                            )
                            ->addElement((new Element())
                                ->setContent('Unfallkasse Sachsen')
                                ->stylePaddingBottom('10px')
                                ->styleTextBold()
                                ->stylePaddingLeft('20px')
                            )
                            ->addElement((new Element())
                                ->setContent('Postfach 42')
                                ->stylePaddingBottom('10px')
                                ->styleTextBold()
                                ->stylePaddingLeft('20px')
                            )
                            ->addElement((new Element())
                                ->setContent('01651 Meißen')
                                ->stylePaddingBottom()
                                ->styleTextBold()
                                ->stylePaddingLeft('20px')
                                ->styleHeight('50px')
                            )
                            , '60%'
                        )
                        ->addSliceColumn((new Slice())
                            ->addElement((new Element())
                                ->setContent('UNFALLANZEIGE')
                                ->styleTextSize('24px')
                                ->styleTextBold()
                            )
                            ->addElement((new Element())
                                ->setContent('für Kinder in Tageseinrichtungen, Schüler, Studierende')
                                ->styleTextSize('12px')
                                ->styleHeight('40px')
                            )
                            ->addElement((new Element())
                                ->setContent('2 Träger der Einrichtung')
                                ->styleTextSize('11px')
                            )
                            ->addElement((new Element())
                                ->setContent('
                                    {% if( Content.Responsibility.Company.Display is not empty) %}
                                        {{ Content.Responsibility.Company.Display }}
                                    {% else %}
                                        &nbsp;
                                    {% endif %}
                                ')
                                ->styleTextSize('12px')
                                ->styleHeight('29px')
                            )
                            ->addElement((new Element())
                                ->setContent('3 Unternehmensnr. des Unfallversicherungsträgers')
                                ->styleTextSize('11px')
                            )
                            ->addElement((new Element())
                                ->setContent('
                                {% if( Content.Responsibility.Company.Number is not empty) %}
                                    {{ Content.Responsibility.Company.Number }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                                ')
                                ->styleTextSize('12px')
                            )
                            , '40%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleTextSize('1px')
                            ->styleBorderBottom()
                        )
                    )
                    /////// Name Geburtstag
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('5 Name, Vorname des Versicherten')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            ->styleBorderRight()
                            , '55%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('6 Geburtstag')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            ->styleBorderRight()
                            , '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Tag')
                            ->styleTextSize('11px')
                            ->styleAlignCenter()
                            ->styleBorderRight()
                            , '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Monat')
                            ->styleTextSize('11px')
                            ->styleAlignCenter()
                            ->styleBorderRight()
                            , '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Jahr')
                            ->styleTextSize('11px')
                            ->styleAlignCenter()
                            , '10%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if( Content.Person.Data.Name.Last is not empty) %}
                                    {{ Content.Person.Data.Name.Last }} {{ Content.Person.Data.Name.First }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                            ->stylePaddingLeft('5px')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '55%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->stylePaddingLeft('5px')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('
                            {% if(Content.Person.Common.BirthDates.Birthday is not empty) %}
                                    {{ Content.Person.Common.BirthDates.Birthday|date("d") }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                            ->styleAlignCenter()
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('
                            {% if(Content.Person.Common.BirthDates.Birthday is not empty) %}
                                    {{ Content.Person.Common.BirthDates.Birthday|date("m") }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                            ->styleAlignCenter()
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('
                            {% if(Content.Person.Common.BirthDates.Birthday is not empty) %}
                                    {{ Content.Person.Common.BirthDates.Birthday|date("Y") }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            , '10%'
                        )
                    )
                    ///////// Adresse
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('7 Straße, Hausnummer')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            ->styleBorderRight()
                            , '40%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Postleitzahl')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            ->styleBorderRight()
                            , '15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Ort')
                            ->stylePaddingLeft('5px')
                            ->styleTextSize('11px')
                            , '45%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Person.Address.Street.Name) %}
                                    {{ Content.Person.Address.Street.Name }}
                                    {{ Content.Person.Address.Street.Number }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->stylePaddingLeft('5px')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '40%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Person.Address.City.Code) %}
                                    {{ Content.Person.Address.City.Code }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->stylePaddingLeft('5px')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Person.Address.City.Name) %}
                                    {{ Content.Person.Address.City.Name }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->stylePaddingLeft('5px')
                            ->styleBorderBottom()
                            , '45%'
                        )
                    )
                    /////// Meta & gesetzlicher Vertreter
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('8 Geschlecht')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            ->styleBorderRight()
                            , '22%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('9 Staatsangehörigkeit')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            ->styleBorderRight()
                            , '18%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('10 Name und Anschrift der gesetzlichen Vertreter')
                            ->stylePaddingLeft('5px')
                            ->styleTextSize('11px')
                            , '60%'
                        )
                    )
                    ->addSection((new Section())
                        ->addSliceColumn(
                            $this->setCheckBox(
                                '{% if Content.Person.Common.BirthDates.Gender == 1 %}
                                    X
                                {% endif %}'
                            )
                                ->styleBorderBottom()
                                ->styleHeight('29px')
                            , '4%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('männlich')
                            ->styleTextSize('12px')
                            ->styleHeight('25px')
                            ->stylePaddingTop('4px')
                            ->styleBorderBottom()
                            , '7%'
                        )
                        ->addSliceColumn(
                            $this->setCheckBox(
                                '{% if Content.Person.Common.BirthDates.Gender == 2 %}
                                    X
                                {% endif %}'
                            )
                                ->styleBorderBottom()
                                ->styleHeight('29px')
                            , '4%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('weiblich')
                            ->styleTextSize('12px')
                            ->styleHeight('25px')
                            ->stylePaddingTop('4px')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '7%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('
                                Deutschland
                            ')
                            ->stylePaddingLeft('5px')
                            ->styleHeight('27px')
                            ->stylePaddingTop()
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '18%'
                        )
                        ->addElementColumn((new Element())
                            // (Content.Person.Parent.Father.Address|length >= 38) Zählen der Länge entfällt
                            ->setContent('
                                {% if (Content.Person.Parent.Father.Address)
                                and (Content.Person.Parent.Mother.Address)
                                and (Content.Person.Parent.Father.Address == Content.Person.Parent.Mother.Address) %}
                                    {% if(Content.Person.Parent.Father.Name.First) and (Content.Person.Parent.Mother.Name.First) %}
                                        {{ Content.Person.Parent.Father.Name.First }}
                                        {{ Content.Person.Parent.Father.Name.Last }},
                                        {{ Content.Person.Parent.Mother.Name.First }}
                                        {{ Content.Person.Parent.Mother.Name.Last }}
                                        <br/>
                                        {% if(Content.Person.Parent.Father.Address) %}
                                            {{ Content.Person.Parent.Father.Address }}
                                        {% endif %}
                                    {% endif %}
                                {% else %}
                                    {% if(Content.Person.Parent.Father.Name.First) %}
                                        {{ Content.Person.Parent.Father.Name.First }}
                                        {{ Content.Person.Parent.Father.Name.Last }}
                                        {% if(Content.Person.Parent.Father.Address) %}
                                            {{ Content.Person.Parent.Father.Address }}
                                        {% endif %}
                                        <br/>
                                    {% endif %}
                                    {% if(Content.Person.Parent.Mother.Name.First) %}
                                        {{ Content.Person.Parent.Mother.Name.First }}
                                        {{ Content.Person.Parent.Mother.Name.Last }}
                                        {% if(Content.Person.Parent.Mother.Address) %}
                                            {{ Content.Person.Parent.Mother.Address }}
                                        {% else %}
                                              &nbsp;
                                        {% endif %}
                                    {% endif %}
                                {% endif %}
                            ')
                            ->stylePaddingLeft('5px')
                            ->styleTextSize('11px')
                            ->stylePaddingTop()
                            ->styleBorderBottom()
                            ->styleHeight('27px')
                            , '60%'
                        )
                    )
                    /////// Unfall Infos
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('11 Tödlicher Unfall')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            ->styleBorderRight()
                            , '20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('12 Unfallzeitpunkt')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            ->styleBorderRight()
                            , '35%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('13 Unfallort (genaue Orts- und Straßenangabe mit PLZ)')
                            ->stylePaddingLeft('5px')
                            ->styleTextSize('11px')
                            , '45%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->styleHeight('32px')
                            ->styleBorderBottom()
                            , '2%'
                        )
                        ->addSliceColumn(
                            $this->setCheckBox()
                                ->styleBorderBottom()
                                ->stylePaddingTop('3px')
                                ->styleHeight('29px')
                            , '4%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('ja')
                            ->styleTextSize('12px')
                            ->styleHeight('24px')
                            ->stylePaddingTop('8px')
                            ->styleBorderBottom()
                            , '4%'
                        )
                        ->addSliceColumn(
                            $this->setCheckBox()
                                ->styleBorderBottom()
                                ->stylePaddingTop('3px')
                                ->styleHeight('29px')
                            , '4%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('nein')
                            ->styleTextSize('12px')
                            ->styleHeight('24px')
                            ->stylePaddingTop('8px')
                            ->styleBorderBottom()
                            , '4%'
                        )
                        ->addElementColumn((new Element())
                            ->styleHeight('32px')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '2%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Tag <br/> ')
                            ->stylePaddingLeft('5px')
                            ->styleTextSize('11px')
                            ->styleHeight('30px')
                            ->styleAlignCenter()
                            ->stylePaddingTop()
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '7%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Monat <br/> ')
                            ->stylePaddingLeft('5px')
                            ->styleTextSize('11px')
                            ->styleHeight('30px')
                            ->styleAlignCenter()
                            ->stylePaddingTop()
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '7%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Jahr <br/> ')
                            ->stylePaddingLeft('5px')
                            ->styleTextSize('11px')
                            ->styleHeight('30px')
                            ->styleAlignCenter()
                            ->stylePaddingTop()
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '7%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Stunde <br/> ')
                            ->stylePaddingLeft('5px')
                            ->styleTextSize('11px')
                            ->styleHeight('30px')
                            ->styleAlignCenter()
                            ->stylePaddingTop()
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '7%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Minute <br/> ')
                            ->stylePaddingLeft('5px')
                            ->styleTextSize('11px')
                            ->styleHeight('30px')
                            ->styleAlignCenter()
                            ->stylePaddingTop()
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '7%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('
                                &nbsp;
                            ')
                            ->stylePaddingLeft('5px')
                            ->styleTextSize('12px')
                            ->stylePaddingTop()
                            ->styleBorderBottom()
                            ->styleHeight('30px')
                            , '45%'
                        )
                    )
                    ////// Schilderung des Unfallhergangs
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('14 Ausführliche Schilderung des Unfallhergangs (insbesondere Art der Veranstalltung,
                            bei Sportunfällen auch Sportart')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                &nbsp;
                            ')
                            ->styleHeight('200px')
                            ->stylePaddingLeft('20px')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Die angaben beruhen auf der Schilderung')
                            ->styleTextSize('11px')
                            ->stylePaddingTop('5px')
                            ->styleHeight('15px')
                            ->stylePaddingLeft('5px')
                            ->styleBorderBottom()
                            , '35%'
                        )
                        ->addSliceColumn($this->setCheckBox()
                            ->styleHeight('20px')
                            ->styleBorderBottom()
                            , '4%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('des Versicherten')
                            ->styleTextSize('11px')
                            ->stylePaddingTop('5px')
                            ->styleHeight('15px')
                            ->styleBorderBottom()
                            , '15%'
                        )
                        ->addSliceColumn($this->setCheckBox()
                            ->styleHeight('20px')
                            ->styleBorderBottom()
                            , '4%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('andere Personen')
                            ->styleTextSize('11px')
                            ->stylePaddingTop('5px')
                            ->styleHeight('15px')
                            ->styleBorderBottom()
                            , '15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleHeight('20px')
                            ->styleBorderBottom()
                            , '27%'
                        )
                    )
                    /////// Verletzungen
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('15 Verletzte Körperteile')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            ->styleBorderRight()
                            , '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('16 Art der Verletzung')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            , '50%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->stylePaddingLeft('5px')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->stylePaddingLeft('5px')
                            ->styleBorderBottom()
                            , '50%'
                        )
                    )
                    /////// Unterbrechung
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('17 Hat der Versicherte den Besuch der <br/> Einrichtung unterbrochen?')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            ->styleHeight('27px')
                            ->styleBorderBottom()
                            , '35%'
                        )
                        ->addSliceColumn($this->setCheckBox()
                            ->styleHeight('27px')
                            ->styleBorderBottom()
                            , '4%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('nein')
                            ->stylePaddingTop('3px')
                            ->styleHeight('24px')
                            ->styleBorderBottom()
                            , '11%'
                        )
                        ->addSliceColumn($this->setCheckBox()
                            ->styleHeight('27px')
                            ->styleBorderBottom()
                            , '4%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('sofort')
                            ->stylePaddingTop('3px')
                            ->styleHeight('24px')
                            ->styleBorderBottom()
                            , '11%'
                        )
                        ->addSliceColumn($this->setCheckBox()
                            ->styleHeight('27px')
                            ->styleBorderBottom()
                            , '4%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('später am')
                            ->stylePaddingTop('3px')
                            ->styleHeight('24px')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '11%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Tag <br/> ')
                            ->styleTextSize('11px')
                            ->styleAlignCenter()
                            ->styleHeight('27px')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '6%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Monat <br/> ')
                            ->styleTextSize('11px')
                            ->styleAlignCenter()
                            ->styleHeight('27px')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '7%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Stunde <br/> ')
                            ->styleTextSize('11px')
                            ->styleAlignCenter()
                            ->styleHeight('27px')
                            ->styleBorderBottom()
                            , '7%'
                        )
                    )
                    /////// Vortsetzung
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('18 hat der Versicherte den Besuch der <br/> Einrichtung wieder aufgenommen?')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            ->styleHeight('27px')
                            ->styleBorderBottom()
                            , '50%'
                        )
                        ->addSliceColumn($this->setCheckBox()
                            ->styleHeight('27px')
                            ->styleBorderBottom()
                            , '4%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('nein')
                            ->stylePaddingTop('3px')
                            ->styleHeight('24px')
                            ->styleBorderBottom()
                            , '11%'
                        )
                        ->addSliceColumn($this->setCheckBox()
                            ->styleHeight('27px')
                            ->styleBorderBottom()
                            , '4%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('ja, am')
                            ->stylePaddingTop('3px')
                            ->styleHeight('24px')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '11%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Tag <br/> ')
                            ->styleTextSize('11px')
                            ->styleAlignCenter()
                            ->styleHeight('27px')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '6%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Monat <br/> ')
                            ->styleTextSize('11px')
                            ->styleAlignCenter()
                            ->styleHeight('27px')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '7%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Jahr <br/> ')
                            ->styleTextSize('11px')
                            ->styleAlignCenter()
                            ->styleHeight('27px')
                            ->styleBorderBottom()
                            , '7%'
                        )
                    )
                    /////// Kenntnis
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('19 Wer hat von dem Unfall zuerst Kenntnis genommen? (Name, Anschrift von Zeugen)')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            , '70%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('War diese Person Augenzeuge?')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            , '30%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->stylePaddingLeft('5px')
                            ->stylePaddingTop('3px')
                            ->styleHeight('22px')
                            ->styleBorderBottom()
                            , '70%'
                        )
                        ->addSliceColumn($this->setCheckBox()
                            ->styleHeight('25px')
                            ->styleBorderBottom()
                            , '4%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('ja')
                            ->stylePaddingTop('3px')
                            ->styleHeight('22px')
                            ->stylePaddingLeft('5px')
                            ->styleBorderBottom()
                            , '11%'
                        )
                        ->addSliceColumn($this->setCheckBox()
                            ->styleHeight('25px')
                            ->styleBorderBottom()
                            , '4%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('nein')
                            ->stylePaddingTop('3px')
                            ->styleHeight('22px')
                            ->stylePaddingLeft('5px')
                            ->styleBorderBottom()
                            , '11%'
                        )
                    )
                    /////// Kenntnis
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('20 Name und Anschrift des erstbehandelnden Arztes / Krankenhauses')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            ->styleBorderRight()
                            , '60%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('21 Beginn und Ende des Besuchs der Einrichtung')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            , '40%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->stylePaddingLeft('5px')
                            ->styleHeight('42.3px')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '60%'
                        )
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Beginn')
                                    ->styleAlignCenter()
                                    ->styleTextSize('11px')
                                    ->stylePaddingLeft('5px')
                                    ->stylePaddingBottom()
                                    ->styleBorderRight()
                                    , '50%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('Ende')
                                    ->styleAlignCenter()
                                    ->styleTextSize('11px')
                                    ->stylePaddingLeft('5px')
                                    ->stylePaddingBottom()
                                    , '50%'
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Stunde <br/> &nbsp;')
                                    ->styleTextSize('11px')
                                    ->styleAlignCenter()
                                    ->stylePaddingLeft('5px')
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '25%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('Minute <br/> &nbsp;')
                                    ->styleTextSize('11px')
                                    ->styleAlignCenter()
                                    ->stylePaddingLeft('5px')
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '25%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('Stunde <br/> &nbsp;')
                                    ->styleTextSize('11px')
                                    ->styleAlignCenter()
                                    ->stylePaddingLeft('5px')
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '25%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('Minute <br/> &nbsp;')
                                    ->styleTextSize('11px')
                                    ->styleAlignCenter()
                                    ->stylePaddingLeft('5px')
                                    ->styleBorderBottom()
                                    , '25%'
                                )
                            )
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if( Content.Document.Date.Now) %}
                                    {{ Content.Document.Date.Now }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                            ->stylePaddingLeft('5px')
                            ->stylePaddingTop('22px')
                            ->styleHeight('18px')
                            ->styleBorderBottom()
                            , '20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->stylePaddingLeft('5px')
                            ->stylePaddingTop('22px')
                            ->styleHeight('18px')
                            ->styleBorderBottom()
                            , '40%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->stylePaddingLeft('5px')
                            ->stylePaddingTop('22px')
                            ->styleHeight('18px')
                            ->styleBorderBottom()
                            , '40%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('22 Datum')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            , '20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Leiter (Beauftragter) der Einrichtung')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            , '40%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Telefon-Nr. für Rückfragen (Ansprechpartner)')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            , '40%'
                        )
                    )
                )
            )
        );
    }

}