<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\HOGA;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Layout\Repository\Container;

class GymAbgSekI extends Style
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

        $pageList[] = $this->getCoverPage('ABGANGSZEUGNIS', 'des Gymnasiums', '(Sekundarstufe I)');

        $school = $this->getCustomSchoolName('Allgemeinbildendes Gymnasium');

        $paddingTop = '4px';
        $marginSpace = '45px';
        $page = $this->getSecondPageTop($personId, $marginSpace);
        $page
            ->addSlice((new Slice())
                ->styleMarginTop($marginSpace)
                ->addSection((new Section())
                    ->addElementColumn($this->getElement('hat das')
                        ->stylePaddingTop($paddingTop))
                )
            )
            ->addSlice($this->getLogoSecondPage())
            ->addSlice((new Slice())
                ->styleMarginTop('30px')
                ->addSection((new Section())
                    ->addElementColumn(
                        $this->getElement($school[0], self::TEXT_SIZE_SMALL)
                            ->styleAlignCenter()
                            ->styleTextBold()
                            ->styleMarginTop('-6px')
                    )
                )
                ->addSection((new Section())
                        ->addElementColumn(
                        $this->getElement($school[1], self::TEXT_SIZE_SMALL)
                            ->styleAlignCenter()
                            ->styleMarginTop('-6px')
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn(
                        $this->getElement($school[2], self::TEXT_SIZE_SMALL)
                            ->styleAlignCenter()
                            ->styleMarginTop('-6px')
                    )
                )
            )
            ->addSlice((new Slice())
                ->styleMarginTop('30px')
                ->addSection((new Section())
                    ->addElementColumn($this->getElement('besucht')
                        ->stylePaddingTop($paddingTop))
                )
            )
            ->addSlice((new Slice())
                ->styleMarginTop('30px')
                ->addSection((new Section())
                    ->addElementColumn($this->getElement(
                            new Container('und verlässt nach Erfüllung der Vollzeitschulpflicht gemäß')
                            . new Container('§ 28 Absatz 1 Nummer 1 des Sächsischen Schulgesetzes')
                            . new Container('das Gymnasium.')
                        )
                        ->styleAlignCenter()
                        ->styleLineHeight('70%')
                        ->stylePaddingTop($paddingTop))
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addSliceColumn(
                        $this->setCustomCheckBox(
                            '{% if(Content.P' . $personId . '.Input.EqualGraduation.RS is not empty) %}
                                X
                            {% else %}
                                &nbsp;
                            {% endif %}'
                        )
                        , '30px')
                    ->addElementColumn($this->getElement(
                            '{% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 2 %}
                                Frau
                            {% else %}
                                {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 1 %}
                                    Herr
                                {% endif %}
                            {% endif %}
                            {{ Content.P' . $personId . '.Person.Data.Name.First }} {{ Content.P' . $personId . '.Person.Data.Name.Last }}
                            hat gemäß § 7 Absatz 7 Satz 2 des Sächsischen Schulgesetzes mit der Versetzung von Klassenstufe
                            10 nach Jahrgangsstufe 11 des Gymnasiums einen dem Realschulabschluss gleichgestellten
                            mittleren Schulabschluss erworben.'
                        )
                        ->stylePaddingLeft('10px')
                        ->styleLineHeight('70%')
                    )
                )->styleMarginTop('30px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addSliceColumn(
                        $this->setCustomCheckBox(
                            '{% if(Content.P' . $personId . '.Input.EqualGraduation.HS is not empty) %}
                                X
                            {% else %}
                                &nbsp;
                            {% endif %}'
                        )
                        , '30px')
                    ->addElementColumn($this->getElement(
                        '{% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 2 %}
                                Frau
                            {% else %}
                                {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 1 %}
                                    Herr
                                {% endif %}
                            {% endif %}
                            {{ Content.P' . $personId . '.Person.Data.Name.First }} {{ Content.P' . $personId . '.Person.Data.Name.Last }}
                            hat gemäß § 7 Absatz 7 Satz 1 des Sächsischen Schulgesetzes mit der Versetzung von
                            Klassenstufe 9 nach Klassenstufe 10 des Gymnasiums einen dem Hauptschulabschluss gleichgestellten
                            Schulabschluss erworben.'
                    )
                        ->stylePaddingLeft('10px')
                        ->styleLineHeight('70%')
                    )
                )->styleMarginTop('20px')
            );

        $pageList[] = $page;

        $pageList[] = (new Page())
            ->addSlice($this->getStudentHeader($personId, true))
            ->addSlice($this->getSliceSpace('15px'))
            ->addSlice($this->getCustomSubjectLanes($personId, true, array('Lane' => 1, 'Rank' => 3))->styleHeight('290px'))
            ->addSlice($this->getCustomProfile($personId, '5px', true))
            ->addSlice($this->getCustomTeamExtra($personId, '5px', false)->styleHeight('40px'))
            ->addSlice($this->getCustomRemark($personId, '5px', '130px'))
            ->addSlice($this->getCustomDateLine($personId))
            ->addSlice($this->getCustomSignPart($personId, true, '90px'))
            ->addSlice($this->getCustomInfo('60px', array(
                0 => '¹ Gilt nicht für Gymnasien mit vertiefter Ausbildung gemäß § 4 der Schulordnung Gymnasien Abiturprüfung.',
                1 => '² Die Bezeichnung des besuchten schulspezifischen Profils ist anzugeben. Beim Erlernen einer dritten
                    Fremdsprache ist anstelle des Profils oder in der',
                3 => '&nbsp;&nbsp;vertieften sprachlichen Ausbildung die Fremdsprache anzugeben.'
            )));

        return $pageList;
    }
}