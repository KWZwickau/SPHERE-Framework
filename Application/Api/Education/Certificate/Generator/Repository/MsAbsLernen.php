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
 * Class MsAbsLernen
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class MsAbsLernen extends Certificate
{

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

        $Header = $this->getHeadForDiploma($this->isSample(), !$showPictureOnSecondPage);

        // leere Seite
        $pageList[] = new Page();

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
            ->addSliceArray(MsAbsRs::getSchoolPart($personId))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('und hat gemäß § 63 Absatz 1 Satz 1 der Schulordnung Ober- und Abendoberschulen den')
                    ->styleMarginTop('8px')
                    ->styleAlignLeft()
                )
                ->addElement((new Element())
                    ->setContent('Abschluss im Förderschwerpunkt Lernen gemäß <br> § 34a Absatz 1 der Schulordnung Förderschulen')
                    ->styleMarginTop('18px')
                    ->styleTextSize('20px')
                    ->styleTextBold()
                )
                ->addElement((new Element())
                    ->setContent('erworben.')
                    ->styleMarginTop('20px')
                    ->styleAlignLeft()
                )
                ->styleAlignCenter()
                ->styleMarginTop('22%')
            )
            ->addSlice(MsAbsRs::getPictureForDiploma($showPictureOnSecondPage))
        ;

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
                        ->setContent('Klasse:')
                        ->styleAlignCenter()
                        , '10%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Division.Data.Name }}')
                        ->styleBorderBottom()
                        ->styleAlignCenter()
                    )
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
            ->addSlice($this->getSupportContent($personId, 'auto', '15px', 'Bemerkungen: Inklusive Unterrichtung¹: '))
            ->addSlice($this->getSupportSubjectContent($personId, '180px', '0px',
                'Thema der lebenspraktisch orientierten Komplexen Leistung:'
            ))
            ->addSlice($this->getDateLine($personId))
            ->addSlice((new MsAbsRs(
                $this->getTblStudentEducation() ?: null,
                $this->getTblPrepareCertificate() ?: null
            ))->getExaminationsBoard('10px','11px'))
            ->addSlice($this->getInfo('170px',
                'Notenerläuterung:',
                '1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft; 6 = ungenügend',
                '&nbsp;',
                '¹ &nbsp;&nbsp;&nbsp; gemäß § 27 Absatz 6 der Schulordnung Ober- und Abendoberschulen'
            ));

        return $pageList;
    }
}