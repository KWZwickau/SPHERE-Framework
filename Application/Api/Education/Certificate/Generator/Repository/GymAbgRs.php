<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class GymAbgRs
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class GymAbgRs extends Certificate
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
            ->addSlice(
                $Header
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
            );

        $pageList[] = (new Page())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Vorname und Name:')
                        , '22%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                                          {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
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
//                ->addSlice(
//                    (new Slice())
//                        ->addElement(
//                            (new Element())
//                                ->setContent('
//                                            {{ Content.P' . $personId . '.Company.Data.Name }},
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
                    ->setContent('und verlässt nach Erfüllung der Vollzeitschulpflicht gemäß § 28 Abs. 1 Nr. 1 SchulG das Gymnasium.')
                    ->styleMarginTop('8px')
                    ->styleAlignLeft()
                )->styleMarginTop('30%')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())/* nicht Ausgewählt = 0; männlich = 1; weiblich = 2 */
                    ->setContent('
                            {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 2 %}
                                Frau
                            {% else %}
                                {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 1 %}
                                    Herr
                                {% else %}
                                    Frau/Herr
                                {% endif %}
                            {% endif %}
                            {{ Content.P' . $personId . '.Person.Data.Name.First }} {{ Content.P' . $personId . '.Person.Data.Name.Last }} hat, gemäß § 7 Abs.
                            7 SchulG, mit der Versetzung von Klassenstufe 10 nach Jahrgangsstufe 11 des Gymnasiums einen
                            dem Realschulabschluss gleichgestellten mittleren Schulabschluss erworben.
                            ')
                        ->stylePaddingBottom()
                    )
                )->styleMarginTop('1px')

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
            );

        $pageList[] = (new Page())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Vorname und Name:')
                        , '21%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                                          {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                        ->styleBorderBottom()
                        , '59%')
                    ->addElementColumn((new Element())
                        ->setContent('Klasse')
                        ->styleAlignCenter()
                        , '10%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Division.Data.Level.Name }}{{ Content.P' . $personId . '.Division.Data.Name }}')
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
            ->addSlice($this->getSubjectLanes($personId)->styleHeight('270px'))
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Wahlpflichtbereich¹:')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->styleTextBold()
                        , '20%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Input.Choose is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Choose }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->styleAlignLeft()
                        ->styleBorderBottom('1px', '#000')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        , '32%')
                    ->addElementColumn((new Element())
                        ->setContent('Profil mit Informatischer Bildung²')
                        ->stylePaddingTop()
                        ->stylePaddingLeft('6px')
                        , '48%'
                    )
                )
                ->styleMarginTop('5px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '30%')
                    ->addElementColumn((new Element())
                        ->setContent('besuchtes Profil¹')
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
                )->styleMarginTop('15px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '52%')
                    ->addElementColumn((new Element())
                        ->setContent('Fremdsprache (ab Klassenstufe {{ Content.P' . $personId . '.Input.LevelThree }} ) Im sprachlichen Profil')
                        ->styleTextSize('9.5px')
                        ->styleAlignCenter()
                        , '48%')
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Vertiefungsrichtung³:')
                        ->styleTextBold()
                        , '20%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Input.Deepening is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Deepening }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->styleBorderBottom()
                    )
                )->styleMarginTop('15px')
            )
            ->addSlice($this->getDescriptionHead($personId))
            ->addSlice($this->getDescriptionContent($personId, '135px', '15px'))
            ->addSlice($this->getDateLine($personId))
            ->addSlice($this->getSignPart($personId))
            ->addSlice($this->getParentSign())
            ->addSlice($this->getInfo('70px',
                'Notenerläuterung:',
                '1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft;
                         6 = ungenügend (6 = ungenügend nur bei der Bewertung der Leistungen)',
                '¹ Gilt nicht an Gymnasien mit vertiefter Ausbildung gemäß § 4 SOGYA.',
                '² In Klassenstufe 8 ist der Zusatz „mit informatischer Bildung“ zu streichen. 
                                    Beim sprachlichen Profil ist der Zusatz „mit informatischer Bildung“ zu
                                    streichen und die Fremdsprache anzugeben.',
                '³ Nur für Schüler mit vertiefter Ausbildung gemäß § 4 SOGYA')
            );

        // leere Seite
        $pageList[] = new Page();

        return $pageList;
    }
}
