<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\HOGA;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

class BgjHjInfo extends Style
{
    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page
     */
    public function buildPages(TblPerson $tblPerson = null) : Page
    {
        $textSize = self::TEXT_SIZE_NORMAL;
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $school = $this->getCustomSchoolName('Berufliches Schulzentrum');

        return (new Page())
            ->addSlice($this->getHeader($school, ''))
            ->addSlice((new Slice())
                ->styleMarginTop('0px')
                ->addElement($this->getElement('Halbjahresinformation', '35px')->styleTextBold()->styleAlignCenter())
                ->addElement($this->getElement('der Berufsschule - Berufsgrundbildungsjahr', '20px')
                    ->styleTextBold()->styleAlignCenter()->styleMarginTop('-8px'))
                ->addElement($this->getElement('
                    {% if(Content.P' . $personId . '.Input.SubjectArea is not empty) %}
                        Berufsbereich - {{ Content.P' . $personId . '.Input.SubjectArea }}
                    {% else %}
                        Berufsbereich &ndash;
                    {% endif %} 
                    ', '20px')
                    ->styleTextBold()->styleAlignCenter()->styleMarginTop('-12px'))
            )
            ->addSlice((new Slice())
                ->styleMarginTop('3px')
                ->addSection((new Section())
                    ->addElementColumn($this->getElement('1. Schulhalbjahr&nbsp;', $textSize)->styleAlignRight(), '50%')
                    ->addElementColumn($this->getElement('{{ Content.P' . $personId . '.Division.Data.Year }}', $textSize)
                        ->styleTextBold(), '50%')
                )
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
//                        ->styleMarginTop('3px')
                        ->styleMarginBottom('7px')
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
            ->addSlice($this->getCustomSubjectLanesBgjAbs($personId, '5px', true)->styleHeight('480px'))
            ->addSlice($this->getCustomFosRemark($personId, '10px', '70px', self::TEXT_SIZE_NORMAL))
            ->addSlice($this->getCustomSignPartBgj($personId, '5px'))
            ->addSlice($this->getCustomParentSign('0px'))
            ->addSlice($this->getCustomInfoBgj('8px'));
    }
}