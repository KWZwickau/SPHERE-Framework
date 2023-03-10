<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 19.02.2018
 * Time: 11:41
 */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Layout\Repository\Container;

/**
 * Class MsAbgHs
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class MsAbg extends Certificate
{

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        // leere Seite
        $pageList[] = new Page();

        $pageList[] = (new Page())
            ->addSlice($this->getHeadForLeave($this->isSample()))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('ABGANGSZEUGNIS')
                    ->styleTextSize('27px')
                    ->styleAlignCenter()
                    ->styleMarginTop('20%')
                    ->styleTextBold()
                )
                ->addElement((new Element())
                    ->setContent('der Oberschule')
                    ->styleTextSize('22px')
                    ->styleAlignCenter()
                    ->styleMarginTop('15px')
                )
            );

        $pageList[] = (new Page())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Vorname und Name:')
                        , '22%')
                    ->addElementColumn((new Element())
                        ->setContent('
                                {{ Content.P' . $personId . '.Person.Data.Name.First }}
                                {{ Content.P' . $personId . '.Person.Data.Name.Last }}
                            ')
                        ->styleBorderBottom()
                    )
                )->styleMarginTop('50px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('geboren am')
                        , '22%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthday is not empty) %}
                                    {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
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
                        ->setContent('{% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthplace is not empty) %}
                                    {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthplace }}
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
                        ->setContent('{% if(Content.P' . $personId . '.Person.Address.City.Name) %}
                                    {{ Content.P' . $personId . '.Person.Address.Street.Name }}
                                    {{ Content.P' . $personId . '.Person.Address.Street.Number }},
                                    {{ Content.P' . $personId . '.Person.Address.City.Code }}
                                    {{ Content.P' . $personId . '.Person.Address.City.Name }}
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                        ->styleBorderBottom()
                    )
                )->styleMarginTop('10px')
            )
            ->addSliceArray(MsAbsRs::getSchoolPart($personId))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent(
                        new Container('und verlässt nach Erfüllung der Vollzeitschulpflicht gemäß')
                        . new Container('§ 28 Absatz 1 Nummer 1 des Sächsischen Schulgesetzes')
                        . new Container('die Oberschule
                            {% if(Content.P' . $personId . '.Student.Course.Name) %}
                                 – {{ Content.P' . $personId . '.Student.Course.Name }}.
                            {% else %}
                                .
                            {% endif %}
                        ')
                    )
                    ->styleMarginTop('8px')
                    ->styleAlignCenter()
                )->styleMarginTop('60px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addSliceColumn(
                        $this->setCheckBox(
                            '{% if(Content.P' . $personId . '.Input.EqualGraduation.HS is not empty) %}
                                X
                            {% else %}
                                &nbsp;
                            {% endif %}'
                        )
                        , '4%')
                    ->addElementColumn((new Element())
                        ->setContent('
                            <u> {{ Content.P' . $personId . '.Person.Data.Name.First }} {{ Content.P' . $personId . '.Person.Data.Name.Last }} </u> hat
                            gemäß § 6 Absatz 1 Satz 7 des Sächsischen Schulgesetzes mit der Versetzung in die Klassenstufe 10
                            des Realschulbildungsganges einen dem Hauptschulabschluss gleichgestellten Abschluss erworben.¹')
                        ->stylePaddingBottom()
                    )
                )->styleMarginTop('45px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addSliceColumn(
                        $this->setCheckBox(
                            '{% if(Content.P' . $personId . '.Input.EqualGraduation.HSQ is not empty) %}
                                X
                            {% else %}
                                &nbsp;
                            {% endif %}'
                        )
                        , '4%')
                    ->addElementColumn((new Element())
                        ->setContent('
                            <u> {{ Content.P' . $personId . '.Person.Data.Name.First }} {{ Content.P' . $personId . '.Person.Data.Name.Last }} </u> hat 
                            gemäß § 27 Absatz 9 Satz 2 Nummer 2 der Schulordnung Ober- und Abendoberschulen mit der Versetzung in die Klassenstufe 10 des
                            Realschulbildungsganges und der erfolgreichen Teilnahme an der Prüfung zum Erwerb desHauptschulabschlusses den qualifizierenden 
                            Hauptschulabschluss erworben.¹')
                        ->stylePaddingBottom()
                    )
                )->styleMarginTop('15px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addSliceColumn(
                        $this->setCheckBox(
                            '{% if(Content.P' . $personId . '.Input.EqualGraduation.LERNEN is not empty) %}
                                X
                            {% else %}
                                &nbsp;
                            {% endif %}'
                        )
                        , '4%')
                    ->addElementColumn((new Element())
                        ->setContent('
                             <u> {{ Content.P' . $personId . '.Person.Data.Name.First }} {{ Content.P' . $personId . '.Person.Data.Name.Last }} </u> hat
                            gemäß § 63 Absatz 3 Nummer 3 der Schulordnung Ober- und Abendoberschulen einen dem Abschluss im Förderschwerpunkt Lernen gemäß 
                            § 34a Absatz 1 der Schulordnung Förderschulen gleichgestellten Abschluss erworben.¹')
                        ->stylePaddingBottom()
                    )
                )->styleMarginTop('15px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->styleBorderBottom()
                        , '30%')
                    ->addElementColumn((new Element()))
                )
                ->addElement((new Element())
                    ->setContent('¹ Zutreffendes ist anzukreuzen sowie Vorname und Name einzutragen.')
                    ->styleTextSize('9.5px')
                )
                ->styleMarginTop('330px')
            );

        $pageList[] = (new Page())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Vorname und Name:')
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('
                                {{ Content.P' . $personId . '.Person.Data.Name.First }}
                                {{ Content.P' . $personId . '.Person.Data.Name.Last }}
                            ')
                        ->styleBorderBottom()
                        , '45%')
                    ->addElementColumn((new Element())
                        ->setContent('Klasse')
                        ->styleAlignCenter()
                        , '10%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Division.Data.Name }}')
                        ->styleBorderBottom()
                        ->styleAlignCenter()
                    )
                )->styleMarginTop('50px')
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Leistungen in den einzelnen Fächern:')
                    ->styleMarginTop('15px')
                    ->styleTextBold()
                )
            )
            ->addSlice($this->getSubjectLanes($personId,true, array(), '14px', false, false, true, true)->styleHeight('320px'))
//            ->addSlice($this->getOrientationStandard($personId))
            ->addSlice($this->getDescriptionHead($personId))
            ->addSlice($this->getDescriptionContent($personId, '200px', '15px'))
            ->addSlice($this->getDateLine($personId))
            ->addSlice($this->getSignPart($personId, true, '30px'))
            ->addSlice($this->getInfo('180px',
                'Notenerläuterung:',
                '1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft; 6 = ungenügend',
                '¹ Gilt nicht für Schülerinnen und Schüler mit sonderpädagogischem Förderbedarf im Förderschwerpunkt Lernen.'
            ));

        return $pageList;
    }
}