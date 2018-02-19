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

        $Header = $this->getHead($this->isSample(), true, 'auto', '50px');

        $pageList[] = (new Page())
            ->addSlice($Header)
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('ABGANGSZEUGNIS')
                    ->styleTextSize('27px')
                    ->styleAlignCenter()
                    ->styleMarginTop('32%')
                    ->styleTextBold()
                )
//                ->addSlice((new Slice())
//                    ->addElement((new Element())
//                        ->setContent('der Mittelschule')
//                        ->styleTextSize('22px')
//                        ->styleAlignCenter()
//                        ->styleMarginTop('15px')
//                    )
//                )
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
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('hat')
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Company.Data.Name) %}
                                    {{ Content.P' . $personId . '.Company.Data.Name }}
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                        ->styleBorderBottom('1px')
                        ->styleAlignCenter()
                    )
                    ->addElementColumn((new Element())
                        ->styleBorderBottom('1px')
                        ->setContent('&nbsp;')
                        , '5%')
                )
                ->styleMarginTop('20px')
            )
            ->addSlice(
                (new Slice())
                    ->addElement(
                        (new Element())
                            ->setContent('{% if(Content.P' . $personId . '.Company.Address.Street.Name) %}
                                    {{ Content.P' . $personId . '.Company.Address.Street.Name }}
                                    {{ Content.P' . $personId . '.Company.Address.Street.Number }},
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                            ->styleBorderBottom('1px')
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
                                    ->styleBorderBottom('1px')
                                , '10%')
                            ->addElementColumn(
                                (new Element())
                                    ->setContent('{% if(Content.P' . $personId . '.Company.Address.City.Name) %}
                                            {{ Content.P' . $personId . '.Company.Address.City.Code }}
                                            {{ Content.P' . $personId . '.Company.Address.City.Name }}
                                        {% else %}
                                              &nbsp;
                                        {% endif %}')
                                    ->styleBorderBottom('1px')
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
                    ->setContent('und verlässt nach Erfüllung der Vollzeitschulpflicht gemäß § 28 Abs. 1 Nr. 1 SchulG'
                        . new Container('die Schulart Mittelschule - Hauptschulbildungsgang.')
                    )
                    ->styleMarginTop('8px')
                    ->styleAlignCenter()
                )->styleMarginTop('27%')
            );

        $pageList[] = (new Page())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Vorname und Name:')
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('
                                {{ Content.P' . $personId . '.Person.Data.Name.Salutation }}
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
                        ->setContent('
                                {{ Content.P' . $personId . '.Division.Data.Level.Name }}{{ Content.P' . $personId . '.Division.Data.Name }}
                            ')
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
            ->addSlice($this->getSubjectLanes($personId)->styleHeight('270px'))
            ->addSlice($this->getOrientationStandard($personId))
            ->addSlice($this->getDescriptionHead($personId))
            ->addSlice($this->getDescriptionContent($personId, '235px', '15px'))
            ->addSlice($this->getDateLine($personId))
            ->addSlice($this->getSignPart($personId))
            ->addSlice($this->getInfo('150px',
                'Notenerläuterung:',
                '1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft; 6 = ungenügend')
            );

        // leere Seite
        $pageList[] = new Page();

        return $pageList;
    }
}