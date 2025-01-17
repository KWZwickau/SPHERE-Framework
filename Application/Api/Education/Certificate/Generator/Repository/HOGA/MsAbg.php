<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\HOGA;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Layout\Repository\Container;

class MsAbg extends Style
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

        $pageList[] = $this->getCoverPage('ABGANGSZEUGNIS', 'der Oberschule', '');

        $school = $this->getCustomSchoolName('Oberschule');

        $paddingTop = '4px';
        $marginSpace = '45px';
        $page = $this->getSecondPageTop($personId, $marginSpace);
        $page
            ->addSlice((new Slice())
                ->styleMarginTop($marginSpace)
                ->addSection((new Section())
                    ->addElementColumn($this->getElement('hat die')
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
                        . new Container('die Oberschule
                            {% if(Content.P' . $personId . '.Student.Course.Name is not empty) %}
                                - {{ Content.P' . $personId . '.Student.Course.Name }}{% endif %}.'
                        ))
                        ->styleAlignCenter()
                        ->styleLineHeight('70%')
                        ->stylePaddingTop($paddingTop))
                )
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
                        hat gemäß § 6 Absatz 1 Satz 7 des Sächsischen Schulgesetzes mit der Versetzung in die Klassenstufe
                        10 des Realschulbildungsganges einen dem Hauptschulabschluss gleichgestellten Abschluss
                        erworben.'
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
                            '{% if(Content.P' . $personId . '.Input.EqualGraduation.HSQ is not empty) %}
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
                        hat gemäß § 27 Absatz 9 Satz 3 der Schulordnung Ober- und Abendoberschulen mit der Versetzung in
                        die Klassenstufe 10 des Realschulbildungsganges und der erfolgreichen Teilnahme an der Prüfung
                        zum Erwerb des Hauptschulabschlusses den qualifizierenden Hauptschulabschluss erworben.'
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
            ->addSlice($this->getCustomSubjectLanes($personId, true, array(), false, true)->styleHeight('320px'))
            ->addSlice($this->getCustomRemark($personId, '5px', '300px'))
            ->addSlice($this->getCustomDateLine($personId))
            ->addSlice($this->getCustomSignPart($personId, true, '90px'))
            ->addSlice($this->getCustomInfo('60px'));

        return $pageList;
    }
}