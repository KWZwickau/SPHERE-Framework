<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.06.2018
 * Time: 14:08
 */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EZSH;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;

/**
 * Class EzshGymAbg
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\EZSH
 */
class EzshGymAbg extends EzshStyle
{
    /**
     * @param $personId
     *
     * @return Page
     */
    private function firstPage($personId)
    {
        $marginTop = '10px';

        $Page = (new Page())
            ->addSlice(
                (new Slice())
                    ->stylePaddingLeft('50px')
                    ->stylePaddingRight('50px')
                    ->addSection((new Section())
                        ->addSliceColumn(
                            self::getEZSHSample('200px')
                        )
                    )
                    ->addSection(
                        $this->setHeadLine('ABGANGSZEUGNIS')
                    )
                    ->addSection(
                        $this->setHeadLine('des Gymnasiums')
                    )
                    ->addSection(
                        $this->setHeadLine('(Sekundarstufe I)')
                    )
                    ->addElement((new Element())
                        ->styleMarginTop('60px')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Vor- und Zuname')
                            ->styleFontFamily(self::FONT_FAMILY)
                            , '20%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {{ Content.P' . $personId . '.Person.Data.Name.First }}
                                {{ Content.P' . $personId . '.Person.Data.Name.Last }}
                            ')
//                            ->styleAlignCenter()
                            ->styleBorderBottom('1px', '#BBB')
                            ->stylePaddingLeft('7px')
                            ->styleFontFamily(self::FONT_FAMILY)
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('geboren am')
                            ->styleMarginTop($marginTop)
                            ->styleFontFamily(self::FONT_FAMILY)
                            , '20%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthday is not empty) %}
                                    {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                            ->styleMarginTop($marginTop)
//                            ->styleAlignCenter()
                            ->styleBorderBottom('1px', '#BBB')
                            ->stylePaddingLeft('7px')
                            ->styleFontFamily(self::FONT_FAMILY)
                            , '20%')
                        ->addElementColumn((new Element())
                            ->setContent('in')
                            ->styleMarginTop($marginTop)
                            ->styleAlignCenter()
                            ->styleFontFamily(self::FONT_FAMILY)
                            , '10%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthplace is not empty) %}
                                    {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthplace }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                            ->styleMarginTop($marginTop)
//                            ->styleAlignCenter()
                            ->styleBorderBottom('1px', '#BBB')
                            ->stylePaddingLeft('7px')
                            ->styleFontFamily(self::FONT_FAMILY)
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('wohnhaft in')
                            ->styleMarginTop($marginTop)
                            ->styleFontFamily(self::FONT_FAMILY)
                            , '20%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.P' . $personId . '.Person.Address.City.Name) %}
                                    {{ Content.P' . $personId . '.Person.Address.Street.Name }}
                                    {{ Content.P' . $personId . '.Person.Address.Street.Number }},
                                    {{ Content.P' . $personId . '.Person.Address.City.Code }}
                                    {{ Content.P' . $personId . '.Person.Address.City.Name }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->styleMarginTop($marginTop)
//                            ->styleAlignCenter()
                            ->styleBorderBottom('1px', '#BBB')
                            ->stylePaddingLeft('7px')
                            ->styleFontFamily(self::FONT_FAMILY)
                        )
                    )
                    ->addElement((new Element())
                        ->styleMarginTop('30px')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('hat')
                            ->styleMarginTop($marginTop)
                            ->styleFontFamily(self::FONT_FAMILY)
                            , '20%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.P' . $personId . '.Company.Data.Name) %}
                                    {{ Content.P' . $personId . '.Company.Data.Name }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->styleMarginTop($marginTop)
//                            ->styleAlignCenter()
                            ->styleBorderBottom('1px', '#BBB')
                            ->stylePaddingLeft('7px')
                            ->styleFontFamily(self::FONT_FAMILY)
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleMarginTop($marginTop)
                            ->styleFontFamily(self::FONT_FAMILY)
                            , '20%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.P' . $personId . '.Company.Address.Street.Name) %}
                                    {{ Content.P' . $personId . '.Company.Address.Street.Name }}
                                    {{ Content.P' . $personId . '.Company.Address.Street.Number }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->styleMarginTop($marginTop)
//                            ->styleAlignCenter()
                            ->styleBorderBottom('1px', '#BBB')
                            ->stylePaddingLeft('7px')
                            ->styleFontFamily(self::FONT_FAMILY)
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleMarginTop($marginTop)
                            ->styleFontFamily(self::FONT_FAMILY)
                            , '20%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.P' . $personId . '.Company.Address.City.Name) %}
                                    {{ Content.P' . $personId . '.Company.Address.City.Code }}
                                    {{ Content.P' . $personId . '.Company.Address.City.Name }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->styleMarginTop($marginTop)
//                            ->styleAlignCenter()
                            ->stylePaddingLeft('7px')
                            ->styleBorderBottom('1px', '#BBB')
                            ->styleFontFamily(self::FONT_FAMILY)
                            , '60%')
                        ->addElementColumn((new Element())
                            ->setContent('besucht.')
                            ->styleMarginTop($marginTop)
                            ->styleAlignRight()
                            ->styleFontFamily(self::FONT_FAMILY)
                            , '20%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleFontFamily(self::FONT_FAMILY)
                            , '20%')
                        ->addElementColumn((new Element())
                            ->setContent('Name und Anschrift der Schule')
                            ->styleAlignCenter()
                            ->styleFontFamily(self::FONT_FAMILY)
                            , '60%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleAlignRight()
                            ->styleFontFamily(self::FONT_FAMILY)
                            , '20%')
                    )
                    ->addElement((new Element())
                        ->setContent('und verlässt nach Erfüllung der Vollzeitschulpflicht gemäß § 28 Abs.1 Nr.1 SchulG das Gymnasium.')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleMarginTop('30px')
                        ->styleMarginBottom('35px')
                    )
                    ->addSection((new Section())
                        ->addSliceColumn(
                            $this->setCheckBox(
                                '{% if(Content.P' . $personId . '.Input.EqualGraduation.RS is not empty) %}
                                X
                            {% else %}
                                &nbsp;
                            {% endif %}'
                            )
                            ->styleMarginTop('22px')
                            , '4.5%')
                        ->addSliceColumn((new Slice())
                            ->addElement((new Element()))
                            , '15%')
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
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
                                    ')
                                    ->styleFontFamily(self::FONT_FAMILY)
                                    ->styleLineHeight(self::LINE_HEIGHT)
                                    , '10%')
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if(Content.P' . $personId . '.Input.EqualGraduation.RS is not empty) %}
                                            {{ Content.P' . $personId . '.Person.Data.Name.First }}
                                            {{ Content.P' . $personId . '.Person.Data.Name.Last }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleBorderBottom('1px', '#BBB')
                                    ->stylePaddingLeft('7px')
                                    ->styleFontFamily(self::FONT_FAMILY)
                                    ->styleLineHeight(self::LINE_HEIGHT)
                                    , '60%')
                                ->addElementColumn((new Element())
                                    ->setContent('hat,')
                                    ->stylePaddingLeft('10px')
                                    ->styleFontFamily(self::FONT_FAMILY)
                                    ->styleLineHeight(self::LINE_HEIGHT)
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('gemäß § 7 Abs. 7 SchulG, mit der Versetzung von Klassenstufe 10 nach')
                                    ->styleFontFamily(self::FONT_FAMILY)
                                    ->styleLineHeight(self::LINE_HEIGHT)
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Jahrgangsstufe 11 des Gymnasiums einen dem Realschulabschluss')
                                    ->styleFontFamily(self::FONT_FAMILY)
                                    ->styleLineHeight(self::LINE_HEIGHT)
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('gleichgestellten Schulabschluss erworben.¹')
                                    ->styleFontFamily(self::FONT_FAMILY)
                                    ->styleLineHeight(self::LINE_HEIGHT)
                                )
                            )
                        )
                    )
                    ->addElement((new Element())
                        ->styleMarginTop('25px')
                    )
                    ->addSection((new Section())
                        ->addSliceColumn(
                            $this->setCheckBox(
                                '{% if(Content.P' . $personId . '.Input.EqualGraduation.HS is not empty) %}
                                    X
                                {% else %}
                                    &nbsp;
                                {% endif %}'
                            )
                            ->styleMarginTop('22px')
                            , '4.5%')
                        ->addSliceColumn((new Slice())
                            ->addElement((new Element()))
                            , '15%')
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
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
                                    ')
                                    ->styleFontFamily(self::FONT_FAMILY)
                                    ->styleLineHeight(self::LINE_HEIGHT)
                                    , '10%')
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if(Content.P' . $personId . '.Input.EqualGraduation.HS is not empty) %}
                                            {{ Content.P' . $personId . '.Person.Data.Name.First }}
                                            {{ Content.P' . $personId . '.Person.Data.Name.Last }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleBorderBottom('1px', '#BBB')
                                    ->stylePaddingLeft('7px')
                                    ->styleFontFamily(self::FONT_FAMILY)
                                    ->styleLineHeight(self::LINE_HEIGHT)
                                    , '60%')
                                ->addElementColumn((new Element())
                                    ->setContent('hat,')
                                    ->stylePaddingLeft('10px')
                                    ->styleFontFamily(self::FONT_FAMILY)
                                    ->styleLineHeight(self::LINE_HEIGHT)
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('gemäß § 26 Abs. 7 Satz 3 SOGYA, mit der Versetzung von Klassenstufe 9 nach')
                                    ->styleFontFamily(self::FONT_FAMILY)
                                    ->styleLineHeight(self::LINE_HEIGHT)
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Klassenstufe 10 des Gymnasiums einen dem Hauptschulabschluss')
                                    ->styleFontFamily(self::FONT_FAMILY)
                                    ->styleLineHeight(self::LINE_HEIGHT)
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('gleichgestellten Schulabschluss erworben.¹')
                                    ->styleFontFamily(self::FONT_FAMILY)
                                    ->styleLineHeight(self::LINE_HEIGHT)
                                )
                            )
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('¹ Zutreffendes ist anzukreuzen')
                            ->styleTextSize('9.5px')
                            ->styleBorderTop('1px', '#BBB')
                            ->styleMarginTop('70px')
                            ->styleFontFamily(self::FONT_FAMILY)
                            ->styleLineHeight(self::LINE_HEIGHT)
                        )
                    )
            );
        return $Page;
    }

    /**
     * @param $personId
     *
     * @return Page
     */
    private function secondPage($personId)
    {
        $Page = (new Page())
            ->addSlice(
                (new Slice())
                    ->stylePaddingLeft('50px')
                    ->stylePaddingRight('50px')
                    ->addElement((new Element())
                        ->setContent('&nbsp;')
                        ->stylePaddingTop('30px')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Vor- und Zuname')
                            ->styleFontFamily(self::FONT_FAMILY)
                            , '20%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {{ Content.P' . $personId . '.Person.Data.Name.First }}
                                {{ Content.P' . $personId . '.Person.Data.Name.Last }}
                            ')
                            ->styleBorderBottom('1px', '#BBB')
                            ->stylePaddingLeft('7px')
                            ->styleFontFamily(self::FONT_FAMILY)
                            , '50%')
                        ->addElementColumn((new Element())
                            ->setContent('Klasse')
                            ->stylePaddingLeft('15px')
                            ->styleFontFamily(self::FONT_FAMILY)
                            , '12%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.P' . $personId . '.Division.Data.Name }}')
                            ->styleAlignCenter()
                            ->styleBorderBottom('1px', '#BBB')
                            ->stylePaddingLeft('7px')
                            ->styleFontFamily(self::FONT_FAMILY)
                        )
                    )
                    ->addElement((new Element())
                        ->setContent('LEISTUNGEN in den einzelnen Fächern')
                        ->styleTextBold()
                        ->styleFontFamily(self::FONT_FAMILY_BOLD)
                        ->styleMarginTop('40px')
                        ->styleMarginBottom('15px')
                    )
                    ->addSection((new Section())
                        ->addSliceColumn(
                            self::getEZSHSubjectLanes($personId, true, array('Lane' => 1, 'Rank' => 3), false, true, false)
//                                ->styleHeight('360px')
                        )
                    )
//                    ->addSectionList(
//                        self::getProfile($personId)
//                    )
                    ->addElement((new Element())
                        ->styleMarginTop('20px')
                    )
                    ->addSection((new Section())
                        ->addSliceColumn(
                            self::getEZSHObligation($personId, '14px', true)
                        )
                    )
                    ->addElement((new Element())
                        ->styleMarginTop('20px')
                    )
                    ->addSectionList(
                        self::getEZSHGradeInfo(false)
                    )
                    ->addSectionList(
                        self::getEZSHArrangement($personId)
                    )
                    ->addSectionList(
                        self::getEZSHRemark($personId, '170px', 'Bemerkungen')
                    )
                    ->addSectionList(
                        self::getEZSHDateSignCustom($personId)
                    )
                    ->addElement((new Element())
                        ->styleMarginTop('70px')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '32%')
                        ->addElementColumn((new Element())
                            ->setContent('Stempel')
                            ->styleFontFamily(self::FONT_FAMILY)
                            ->styleLineHeight(self::LINE_HEIGHT)
                            , '36.5%')
                        ->addElementColumn((new Element())
                            ->setContent('Für den Schulträger')
                            ->styleBorderTop('1px', '#BBB')
                            ->styleFontFamily(self::FONT_FAMILY)
                            ->styleLineHeight(self::LINE_HEIGHT)
                        )
                    )
            );

        return $Page;
    }


    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     * @internal param bool $IsSample
     *
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        return array(
            self::firstPage($personId),
            self::secondPage($personId)
        );
    }

    private function setHeadLine($text)
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent($text)
                ->styleAlignCenter()
                ->styleMarginTop('10px')
                ->styleTextSize('21pt')
                ->styleTextBold()
                ->styleFontFamily(self::FONT_FAMILY_BOLD)
            );
    }

    /**
     * @param $personId
     *
     * @return Section[]
     */
    private function getEZSHDateSignCustom($personId)
    {

        $SectionList = array();
        $Section = new Section();
        $Section
            ->addElementColumn((new Element())
                ->setContent('Datum')
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleLineHeight(self::LINE_HEIGHT)
                , '10%'
            )
            ->addElementColumn((new Element())
                ->setContent('{% if(Content.P' . $personId . '.Input.Date is not empty) %}
                        {{ Content.P' . $personId . '.Input.Date }}
                    {% else %}
                        &nbsp;
                    {% endif %}')
                ->styleBorderBottom('1px', '#BBB')
                ->styleAlignCenter()
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleLineHeight(self::LINE_HEIGHT)
                , '15%')
            ->addElementColumn((new Element())
                , '10%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleBorderBottom('1px', '#BBB')
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleLineHeight(self::LINE_HEIGHT)
                , '30%')
            ->addElementColumn((new Element())
                , '10%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleBorderBottom('1px', '#BBB')
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleLineHeight(self::LINE_HEIGHT)
                , '30%')
            ->addElementColumn((new Element())
                , '5%');
        $SectionList[] = $Section;
        $Section = new Section();
        $Section
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleLineHeight(self::LINE_HEIGHT)
                , '32%'
            );
        $Section
            ->addElementColumn((new Element())
                ->setContent('{% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                        {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                    {% else %}
                        Klassenlehrer(in)
                    {% endif %}')
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleLineHeight(self::LINE_HEIGHT)
                , '36.5%'
            );
        $Section
            ->addElementColumn((new Element())
                ->setContent('
                    {% if(Content.P' . $personId . '.Headmaster.Description is not empty) %}
                        {{ Content.P' . $personId . '.Headmaster.Description }}
                    {% else %}
                        Schulleiter(in)
                    {% endif %}'
                )
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleLineHeight(self::LINE_HEIGHT)
            );
        $SectionList[] = $Section;
        return $SectionList;
    }

    /**
     * @param $personId
     *
     * @return Section[]
     */
    private function getProfile($personId)
    {

        $subjectAcronymForGrade = 'PRO';
        if (($tblPerson = Person::useService()->getPersonById($personId))
            && ($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
        ) {
            // Profil
            if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE'))
                && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                    $tblStudentSubjectType))
            ) {
                /** @var TblStudentSubject $tblStudentSubject */
                $tblStudentSubject = current($tblStudentSubjectList);
                if (($tblSubjectProfile = $tblStudentSubject->getServiceTblSubject())) {
                    $tblSubject = $tblSubjectProfile;

                    if (($tblSetting = Consumer::useService()->getSetting('Api', 'Education', 'Certificate',
                            'ProfileAcronym'))
                        && ($value = $tblSetting->getValue())
                    ) {
                        $subjectAcronymForGrade = $value;
                    } else {
                        $subjectAcronymForGrade = $tblSubject->getAcronym();
                    }

                }
            }
        }

        $sectionList[] = (new Section())
        ->addElementColumn((new Element())
            ->setContent('Wahlpflichtbereich:')
            ->styleMarginTop('10px')
            ->styleFontFamily(self::FONT_FAMILY)
            , '20%')
        ->addElementColumn((new Element())
            ->setContent('
                {% if(Content.P' . $personId . '.Student.ProfileEZSH["' . $subjectAcronymForGrade . '"] is not empty) %}
                     {{ Content.P' . $personId . '.Student.ProfileEZSH["' . $subjectAcronymForGrade . '"].Name' . ' }}
                {% else %}
                     &nbsp;
                {% endif %}
            ')
            ->styleMarginTop('10px')
            ->styleBorderBottom('1px', '#BBB')
            ->stylePaddingLeft('7px')
            ->styleFontFamily(self::FONT_FAMILY)
            , '30%')
        ->addElementColumn((new Element())
            ->setContent('mit informatischer Bildung')
            ->styleMarginTop('10px')
            ->stylePaddingLeft('15px')
            ->styleFontFamily(self::FONT_FAMILY)
            , '33%')
        ->addElementColumn((new Element())
            ->setContent('
                {% if(Content.P' . $personId . '.Grade.Data["' . $subjectAcronymForGrade . '"] is not empty) %}
                    {{ Content.P' . $personId . '.Grade.Data["' . $subjectAcronymForGrade . '"] }}
                {% else %}
                    &ndash;
                {% endif %}
            ')
            ->styleMarginTop('10px')
            ->styleAlignCenter()
            ->stylePaddingTop('4px')
            ->stylePaddingBottom('4px')
            ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
            ->styleFontFamily(self::FONT_FAMILY)
            ->styleLineHeight(self::LINE_HEIGHT)
        );

        $sectionList[] = ((new Section())
            ->addElementColumn((new Element())
                , '27%')
            ->addElementColumn((new Element())
                ->setContent('(besuchtes Profil)')
                ->styleAlignCenter()
                ->styleTextSize('12px')
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleLineHeight(self::LINE_HEIGHT)
                , '23%')
            ->addElementColumn((new Element()))
        );

        return $sectionList;
    }
}