<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\HOGA;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

class BgjAbs extends Style
{
    /**
     * @return array
     */
    public function getApiModalColumns() : array
    {
        return array(
            'Success' => 'Abschluss erfolgreich',
        );
    }

    /**
     * @return array
     */
    public function selectValuesSuccess() : array
    {
        return array(
            1 => "mit Erfolg",
            2 => "ohne Erfolg"
        );
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null) : array
    {
        $textSize = self::TEXT_SIZE_LARGE;
        $textSize2 = '19px';
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $school = $this->getCustomSchoolName('Berufliches Schulzentrum');

        $pageList[] = (new Page())
            ->addSlice($this->getHeader($school, '', false, false))
            ->addSlice((new Slice())
                ->styleMarginTop('20px')
                ->addElement($this->getElement('Zeugnis der Berufsschule', '35px')->styleTextBold()->styleAlignCenter())
                ->addElement($this->getElement('Berufsgrundbildungsjahr', '20px')
                    ->styleTextBold()->styleAlignCenter()->styleMarginTop('-8px'))
            )
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
                    )->styleTextBold(), '20%')
                    ->addElementColumn($this->getElement('&nbsp;'))
                    ->addElementColumn($this->getElement(
                        'in &nbsp;&nbsp;&nbsp;
                        <b>{% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthplace is not empty) %}
                            {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthplace }}
                        {% else %}
                            &nbsp;
                        {% endif %}</b>',
                        $textSize
                    ), '35%')
                )
            )
            ->addSlice((new Slice())
                ->addElement($this->getElement(
                        'hat im Schuljahr
                        <b>{{ Content.P' . $personId . '.Division.Data.Year }}</b>
                        das',
                        $textSize
                    )
                    ->styleAlignCenter()->styleMarginTop('20px'))
                ->addElement($this->getElement('Berufsgrundbildungsjahr', $textSize2)
                    ->styleAlignCenter()->styleTextBold()->styleMarginTop('20px'))
                ->addElement($this->getElement(
                    'im {% if(Content.P' . $personId . '.Input.SubjectArea is not empty) %}
                        Berufsbereich {{ Content.P' . $personId . '.Input.SubjectArea }}
                    {% else %}
                        Berufsbereich &ndash;
                    {% endif %}'
                    , $textSize2)->styleAlignCenter()->styleTextBold()->styleMarginTop('-10px'))
                ->addElement($this->getElement(
                        '{% if(Content.P' . $personId . '.Input.Success is not empty) %}
                            {{ Content.P' . $personId . '.Input.Success }}
                        {% else %}
                            mit/ohne Erfolg
                        {% endif %}
                        besucht. Die Berufsschulpflicht
                        {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 2 %}
                            der Schülerin
                        {% else %}
                            {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 1 %}
                                des Schülers
                            {% else %}
                                der Schülerin/des Schülers
                            {% endif %}
                        {% endif %}',
                        $textSize
                    )
                    ->styleAlignCenter()
                    ->styleMarginTop('30px')
                )
                ->addElement($this->getElement('wird hiermit nach § 28 Abs. 5 SchulG für beendet erklärt.', $textSize)
                    ->styleAlignCenter()
                    ->styleMarginTop('-10px')
                )
                ->addElement($this->getElement('Die Berufsschulpflicht lebt wieder auf, wenn ein Berufsausbildungsverhältnis', $textSize)
                    ->styleAlignCenter()
                    ->styleMarginTop('-10px')
                )
                ->addElement($this->getElement('begonnen wird und das 18. Lebensjahr noch nicht beendet wurde.', $textSize)
                    ->styleAlignCenter()
                    ->styleMarginTop('-10px')
                )
            )
            ->addSlice($this->getCustomFosSignPart($personId, '270px'));

        $pageList[] = (new Page)
            ->addSlice((new Slice())
                ->styleMarginTop('20px')
                ->addSection((new Section())
                    ->addElementColumn($this->getElement(
                        'Zeugnis des Berufsgrundbildungsjahres für
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
            ->addSlice($this->getCustomSubjectLanesBgjAbs($personId)->styleHeight('680px'))
            ->addSlice($this->getCustomIndustrialPlacement($personId))
            ->addSlice($this->getCustomFosRemark($personId, '10px', '180px'))
            ->addSlice($this->getCustomFosInfo());

        return $pageList;
    }
}