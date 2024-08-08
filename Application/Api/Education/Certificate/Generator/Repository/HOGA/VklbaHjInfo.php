<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\HOGA;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

class VklbaHjInfo  extends Style
{
    /**
     * @return array
     */
    public function getApiModalColumns(): array
    {
        return array(
            'ChosenArea1'              => 'Wahlbereich 1',
            'ChosenArea2'              => 'Wahlbereich 2',
            'PartialCourse'            => 'Teilintegration Bildungsgang',
            'PartialIntegration'       => 'Teilintegration in die Berufsschule'
        );
    }

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

        $startDate = null;
        if ($this->getYear()) {
            list($startDate, $endDate) = Term::useService()->getStartDateAndEndDateOfYear($this->getYear());
        }

        return (new Page())
            ->addSlice($this->getHeader($school, ''))
            ->addSlice((new Slice())
                ->styleMarginTop('10px')
                ->addElement($this->getElement('Halbjahresinformation', '35px')->styleTextBold()->styleAlignCenter())
                ->addElement($this->getElement('der Berufsschule - Vorbereitungsklasse', '20px')
                    ->styleTextBold()
                    ->styleAlignCenter()
                    ->styleMarginTop('-8px')
                    ->styleMarginBottom('10px')
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
                        'in 
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
                ->styleMarginTop('25px')
                ->addElement(
                    $this->getElement(
                        'hat vom ' . ($startDate ? $startDate->format('d.m.Y') : '-') . ' bis 
                        {{ Content.P' . $personId . '.Input.Date }}
                        folgende Leistungen erreicht:', $textSize
                    )
                )
            )
            ->addSlice($this->getCustomSubjectLanesVklba($personId, '5px')->styleHeight('200px'))
            ->addSlice($this->getChosenArea($personId))
            ->addSlice($this->getPartialIntegration($personId))
            ->addSlice($this->getCustomFosRemark($personId, '15px', '150px', self::TEXT_SIZE_NORMAL))
            ->addSlice($this->getCustomSignPartBgj($personId, '5px'))
            ->addSlice($this->getCustomParentSign('10px'))
            ->addSlice($this->getCustomInfoBgj('8px'));
    }
}