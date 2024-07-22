<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\HOGA;

use DateTime;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

class FosAbs extends Style
{
    /**
     * @return array
     */
    public function selectValuesJobGradeText()
    {
        return array(
            1 => "bestanden",
            2 => "nicht bestanden"
        );
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null)
    {
        $textSize = self::TEXT_SIZE_LARGE;
        $textSize2 = '19px';
        $textSizeFooter = '10px';
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $school = $this->getCustomSchoolName('Berufliches Schulzentrum');

        if (($tblPrepare = $this->getTblPrepareCertificate()) && $tblPrepare->getDate()) {
            $certificateDate = $tblPrepare->getDate();
            $educationDateFrom = (new DateTime('01.08.' . ((new DateTime($tblPrepare->getDate()))->format('Y') - 2)))->format('d.m.Y');
        } else {
            $certificateDate = '';
            $educationDateFrom = '';
        }

        $pageList[] = (new Page())
            ->addSlice($this->getHeader($school, '', false, false))
            ->addSlice((new Slice())
                ->styleMarginTop('20px')
                ->addElement($this->getElement('Zeugnis der Fachhochschulreife', '35px')->styleTextBold()->styleAlignCenter()))
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn($this->getElement(
                        '{{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
                            {{ Content.P'.$personId.'.Person.Data.Name.First }} {{ Content.P'.$personId.'.Person.Data.Name.Last }}'
                        , '22px'
                    )
                        ->styleTextBold()
                        ->styleAlignCenter()
                        ->styleMarginTop('20px')
                        ->styleMarginBottom('25px')
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn($this->getElement('geboren am', $textSize), '20%')
                    ->addElementColumn($this->getElement(
                        '{% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthday is not empty) %}
                            {{ Content.P'.$personId.'.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                        {% else %}
                            &nbsp;
                        {% endif %}',
                        $textSize
                    ), '20%')
                    ->addElementColumn($this->getElement('&nbsp;'))
                    ->addElementColumn($this->getElement(
                        'in &nbsp;&nbsp;&nbsp;
                        {% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthplace is not empty) %}
                            {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthplace }}
                        {% else %}
                            &nbsp;
                        {% endif %}',
                        $textSize
                    ), '35%')
                )
            )
            ->addSlice((new Slice())
                ->addElement($this->getElement(
                    'hat vom 
                    {% if(Content.P' . $personId . '.Input.EducationDateFrom is not empty) %}
                        {{ Content.P' . $personId . '.Input.EducationDateFrom }}
                    {% else %}'
                        . $educationDateFrom .
                    '{% endif %}
                    bis ' . $certificateDate
                    , $textSize)->styleAlignCenter()->styleMarginTop('20px'))
                ->addElement($this->getElement('den zweijährigen Bildungsgang der', $textSize)->styleAlignCenter()->styleMarginTop('-10px'))
                ->addElement($this->getElement(
                        'Fachoberschule,
                        {% if(Content.P' . $personId . '.Input.SubjectArea is not empty) %}
                            Fachrichtung {{ Content.P' . $personId . '.Input.SubjectArea }}
                        {% else %}
                            Fachrichtung &ndash;
                        {% endif %}',
                         $textSize2
                    )
                    ->styleAlignCenter()
                    ->styleTextBold()
                    ->styleMarginTop('10px')
                )
                ->addElement($this->getElement(
                        'in
                        {% if(Content.P' . $personId . '.Student.TenseOfLesson is not empty) %}
                            {{ Content.P' . $personId . '.Student.TenseOfLesson | upper }}
                        {% else %}
                            ---
                        {% endif %}
                        besucht und im Schuljahr {{ Content.P' . $personId . '.Division.Data.Year }}'
                        , $textSize
                    )
                    ->styleAlignCenter()
                    ->styleMarginTop('10px')
                )
                ->addElement($this->getElement(
                        'die Abschlussprüfung bestanden.
                        {{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
                        {{ Content.P'.$personId.'.Person.Data.Name.First }} {{ Content.P'.$personId.'.Person.Data.Name.Last }}
                        hat die',
                        $textSize
                    )
                    ->styleAlignCenter()
                    ->styleMarginTop('-10px')
                )
                ->addElement($this->getElement(
                        'Fachhochschulreife',
                        $textSize2
                    )
                    ->styleAlignCenter()
                    ->styleTextBold()
                    ->styleMarginTop('10px')
                )
                ->addElement($this->getElement(
                        'erworben. Damit berechtigt dieses Zeugnis zum Studium an einer Fachhochschule'
                        , $textSize
                    )
                    ->styleAlignCenter()
                    ->styleMarginTop('10px')
                )
                ->addElement($this->getElement(
                        'in der Bundesrepublik Deutschland.' . $this->setSup('1)'),
                        $textSize
                    )
                    ->styleAlignCenter()
                    ->styleMarginTop('-10px')
                )
            )
            ->addSlice((new Slice())
                ->styleMarginTop('18px')
                ->addSection((new Section())
                    ->addElementColumn($this->getElement('Durchschnittsnote' . $this->setSup('2)'), $textSize)
                        ->styleMarginTop('15px')
                        , '25%')
                    // kein Input Feld mehr direkte Berechnung
                    ->addElementColumn($this->getElement('{% if(Content.P'.$personId.'.Calc.AddEducation_Average is not empty) %}
                             {{ Content.P'.$personId.'.Calc.AddEducation_Average }}
                         {% else %}
                             &ndash;
                         {% endif %}', $textSize)
                        ->styleAlignCenter()
                        ->styleBackgroundColor(self::BACKGROUND)
                        ->styleMarginTop('15px')
                        ->stylePaddingBottom('1.5px')
                        , '14%')
                    ->addElementColumn($this->getElement('&nbsp;', $textSize)
                        , '10%')
                    ->addElementColumn($this->getElement('{% if(Content.P'.$personId.'.Calc.AddEducation_AverageInWord is not empty) %}
                             {{ Content.P'.$personId.'.Calc.AddEducation_AverageInWord }}
                         {% else %}
                             &ndash;
                         {% endif %}', $textSize)
                        ->styleAlignCenter()
                        ->styleBackgroundColor(self::BACKGROUND)
                        ->styleMarginTop('15px')
                        ->stylePaddingBottom('1.5px')
                        , '50%')
                )
                ->addSection((new Section())
                    ->addElementColumn($this->getElement('&nbsp;', self::TEXT_SIZE_SMALL)
                        , '25%')
                    ->addElementColumn($this->getElement('in Ziffern', self::TEXT_SIZE_SMALL)
                        ->styleMarginTop('-5px')
                        ->styleAlignCenter()
                        , '14%')
                    ->addElementColumn($this->getElement('&nbsp;', self::TEXT_SIZE_SMALL)
                        , '10%')
                    ->addElementColumn($this->getElement('in Worten', self::TEXT_SIZE_SMALL)
                        ->styleMarginTop('-5px')
                        ->styleAlignCenter()
                        , '50%')
                )
            )
            ->addSlice($this->getCustomFosAbsSignPart($personId));

        $pageList[] = (new Page)
            ->addSlice((new Slice())
                ->styleMarginTop('20px')
                ->addSection((new Section())
                    ->addElementColumn($this->getElement(
                            'Zeugnis der Fachhochschulreife für
                            {{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
                            {{ Content.P'.$personId.'.Person.Data.Name.First }} {{ Content.P'.$personId.'.Person.Data.Name.Last }},
                            geboren am
                            {% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthday is not empty) %}
                                {{ Content.P'.$personId.'.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                            {% else %}
                                &nbsp;
                            {% endif %}', '11px'
                        )
                        ->styleBorderBottom('0.5px')
                        ->stylePaddingBottom('3px')
                        , '80%')
                    ->addElementColumn($this->getElement('2. Seite', '11px')
                        ->styleBorderBottom('0.5px')
                        ->stylePaddingBottom('3px')
                        ->styleAlignRight()
                    )
                )
            )
            ->addSlice($this->getCustomSubjectLanesFosAbs($personId)->styleHeight('580px'))
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn($this->getElement('Bemerkungen:', $textSize)
                        ->styleTextUnderline()
                        , '20%')
                    ->addElementColumn($this->getElement(
                        '{% if(Content.P' . $personId . '.Input.RemarkWithoutTeam is not empty) %}
                            {{ Content.P' . $personId . '.Input.RemarkWithoutTeam|nl2br }}
                        {% else %}
                            &nbsp;
                        {% endif %}',
                        $textSize
                    ))
                )
            )
            ->addSlice($this->getCustomFosSkilledWork($personId))
            ->addSlice((new Slice())
                ->styleMarginTop('150px')
                ->addSection((new Section())
                    ->addElementColumn($this->getElement($this->setSup('1)', '80%'), $textSizeFooter), '1.5%')
                    ->addElementColumn($this->getElement(
                        'Dem Zeugnis liegt die Verordnung des Sächsischen Staatsministeriums für Kultus über die Fachoberschule vom 27. Februar 2017
                        (SächsGVBl. S. 128), die zuletzt durch Artikel 38 der Verordnung vom 26. April 2018 (SächsGVBl. S. 198) geändert worden ist, in
                        der jeweils geltenden Fassung, zu Grunde. <br/>
                        Entsprechend der Rahmenvereinbarung über die Fachoberschule - Beschluss der Kultusministerkonferenz vom 16.12.2004 in der
                        jeweils geltenden Fassung - berechtigt dieses Zeugnis in allen Ländern in der Bundesrepublik Deutschland zum Studium an
                        Fachhochschulen.'
                    , $textSizeFooter)->styleLineHeight('80%'))
                )
                ->addSection((new Section())
                    ->addElementColumn($this->getElement($this->setSup('2)', '80%'), $textSizeFooter), '1.5%')
                    ->addElementColumn($this->getElement(
                        'Die Durchschnittsnote ergibt sich aus allen Zeugnisnoten mit Ausnahme der Noten für die Facharbeit und des Faches Sport.'
                        , $textSizeFooter)->styleLineHeight('100%'))
                )
            )
            ->addSlice($this->getCustomFosInfo());

        return $pageList;
    }
}