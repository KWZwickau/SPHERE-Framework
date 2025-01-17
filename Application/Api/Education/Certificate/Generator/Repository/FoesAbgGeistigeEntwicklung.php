<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer as ConsumerGatekeeper;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Common\Frontend\Layout\Repository\Container;

/**
 * Class FoesAbgGeistigeEntwicklung
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class FoesAbgGeistigeEntwicklung extends Certificate
{

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $pageList[] = (new Page())
            ->addSlice($this->getHeadForLeave($this->isSample()))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('ZEUGNIS')
                    ->styleTextSize('27px')
                    ->styleAlignCenter()
                    ->styleMarginTop('20%')
                    ->styleTextBold()
                )
                ->addElement((new Element())
//                    ->setContent('der Oberschule' . new Container('zur Schulentlassung'))
                    ->setContent(new Container('zur Schulentlassung'))
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
            ->addSliceArray(MsAbsRs::getSchoolPart($personId, false))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Name, Förderschultyp gemäß § 13 Absatz 2 Satz 1 in Verbindung mit § 4c Absatz 2 Nummer 1 bis 4 des 
                                  Sächsischen Schulgesetzes und Anschrift der Schule')
                )
                ->styleTextSize('11.5px')
                ->stylePaddingRight('60px')
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent(
                        new Container('und verlässt nach Erfüllung der Vollzeit- und Berufsschulpflicht gemäß')
                        . new Container('§ 28 Absatz 1 des Sächsischen Schulgesetzes die Schule.')
                    )
                    ->styleMarginTop('8px')
                    ->styleAlignCenter()
                )->styleMarginTop('60px')
            )
            ->addSlice($this->getDescriptionWithoutTeamContent($personId, '200px', '55px'))
            ->addSlice($this->getDateLine($personId))
            ->addSlice($this->getSignPart($personId, true, '30px'))
//            ->addSlice($this->getInfo('187px',
//                '¹ &nbsp;&nbsp;&nbsp; gemäß § 27 Absatz 6 der Schulordnung Ober- und Abendoberschulen'
//            ))
        ;

        return $pageList;
    }
}