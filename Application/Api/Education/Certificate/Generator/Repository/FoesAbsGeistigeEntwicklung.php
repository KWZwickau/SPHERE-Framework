<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;

/**
 * Class FoesAbsGeistigeEntwicklung
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class FoesAbsGeistigeEntwicklung extends Certificate
{

//    /**
//     * @return array
//     */
//    public function selectValuesFoesAbsText()
//    {
//        return array(
//            1 => "die Schule mit dem Förderschwer-punkt geistige Entwicklung",
//            2 => "die Förderschule in der Klasse mit Förderbedarf im Förderschwerpunkt geistige Entwicklung"
//        );
//    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $showPictureOnSecondPage = true;
        if (($tblSetting = Consumer::useService()->getSetting(
            'Education', 'Certificate', 'Generate', 'PictureDisplayLocationForDiplomaCertificate'))
        ) {
            $showPictureOnSecondPage = $tblSetting->getValue();
        }

        $Header = $this->getHead($this->isSample());

//        // leere Seite
//        $pageList[] = new Page();

        $pageList[] = (new Page())
            ->addSlice($Header)
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('ABSCHLUSSZEUGNIS')
                    ->styleTextSize('27px')
                    ->styleAlignCenter()
                    ->styleMarginTop('20%')
                    ->styleTextBold()
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
                        ->setContent('
                                {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                                                ')
                        ->styleBorderBottom()
                        , '20%')
                    ->addElementColumn((new Element())
                        ->setContent('in')
                        ->styleAlignCenter()
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthplace is not empty) %}
                                {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthplace }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
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
                        , '78%')
//                        )
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
                    ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }} {{ Content.P' . $personId . '.Person.Data.Name.Last }}
                    erfüllt am Ende der Werkstufe die Anforderungen des Förderplans gemäß § 17 Absatz 1 der Schulordnung Förderschulen und hat
                    die Förderschule in der Klasse mit Förderbedarf im Förderschwerpunkt geistige Entwicklung mit Erfolg abgeschlossen.')
                    ->styleMarginTop('8px')
                    ->styleAlignLeft()
                // {% if(Content.P' . $personId . '.Input.FoesAbsText) %}  // SSW-1685 Auswahl soll aktuell nicht verfügbar sein, bis aufweiteres aufheben
                //     {{ Content.P' . $personId . '.Input.FoesAbsText }}
                // {% else %}
                //       ---
                // {% endif %}
                )
                ->styleAlignCenter()
                ->styleMarginTop('80px')
            )
            ->addSlice(MsAbsRs::getPictureForDiploma($showPictureOnSecondPage))
            ->addSlice($this->getDescriptionWithoutTeamContent($personId, '150', '15px'))
            ->addSlice($this->getDateLine($personId))
            ->addSlice($this->getSignPart($personId, true, '30px'));

        return $pageList;
    }
}