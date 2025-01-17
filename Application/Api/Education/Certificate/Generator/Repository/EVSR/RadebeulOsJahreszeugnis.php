<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 15.11.2018
 * Time: 10:53
 */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVSR;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;

class RadebeulOsJahreszeugnis extends Certificate
{
    const TEXT_COLOR_BLUE = 'rgb(25,59,100)';
    const TEXT_COLOR_RED = 'rgb(202,23,63)';
    const TEXT_SIZE = '11pt';
    const FONT_FAMILY = 'MetaPro';
    const LINE_HEIGHT = '85%';

    /**
     * @return array
     */
    public function selectValuesTransfer()
    {
        return array(
            1 => "wird versetzt",
            2 => "wird nicht versetzt"
        );
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page
     */
    public function buildPages(TblPerson $tblPerson = null)
    {
        $gradeFieldWidth = 18;

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $gradeLanesSlice = $this->getGradeLanesForRadebeul(
            $personId,
            self::TEXT_COLOR_BLUE,
            '10pt',
            'rgb(224,226,231)',
            false,
            '20px',
            $gradeFieldWidth
        );

        $subjectLanesSlice = $this->getSubjectLanesForRadebeul(
            $personId,
            self::TEXT_COLOR_BLUE,
            '10pt',
            'rgb(224,226,231)',
            false,
            '8px',
            $gradeFieldWidth,
            self::FONT_FAMILY,
            '265px',
            true
        );

        return (new Page())
            ->addSlice(self::getHeader('Jahreszeugnis'))
            ->addSliceArray($this->getBody($personId, true, $gradeLanesSlice, $subjectLanesSlice));
    }

    /**
     * @param $name
     * @param string $schoolType
     * @param string $extra
     *
     * @return Slice
     */
    public static function getHeader($name, $schoolType = '- Oberschule -', $extra = 'genehmigte')
    {
        return (new Slice())
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    , '10%')
                ->addSliceColumn((new Slice())
                    ->styleMarginTop('2px')
                    ->addSection((new Section())
                        ->addElementColumn(
                            self::getHeaderElement(' Evangelisches Schulzentrum Radebeul', '26px')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn(
                            self::getHeaderElement($schoolType, '22px', '0px')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent(
                                'Staatlich ' . $extra . ' Ersatzschule in freier Trägerschaft'
                            )
                            ->styleMarginTop('-6px')
                            ->styleTextColor(self::TEXT_COLOR_BLUE)
                            ->styleTextSize('15px')
                            ->styleAlignCenter()
                            ->styleFontFamily(self::FONT_FAMILY)
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent(
                                'im Freistaat Sachsen'
                            )
                            ->styleMarginTop('-4px')
                            ->styleTextColor(self::TEXT_COLOR_BLUE)
                            ->styleTextSize('15px')
                            ->styleAlignCenter()
                            ->styleFontFamily(self::FONT_FAMILY)
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn(
                            self::getHeaderElement($name, '32px', '2px')
                        )
                    )
                )
                ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/EVSR.jpg',
                    '80px', '80px'))
                    ->styleMarginTop('30px')
                    ->styleAlignCenter()
                    , '10%')
            );
    }

    /**
     * @param $content
     * @param string $textSize
     * @param string $marginTop
     * @param bool $isBold
     *
     * @return Element
     */
    private static function getHeaderElement($content, $textSize = '22pt', $marginTop = '13px', $isBold = true)
    {

        return (new Element())
            ->setContent($content)
            ->styleAlignCenter()
            ->styleTextSize($textSize)
            ->styleTextBold($isBold ? 'bold' : 'normal')
            ->styleFontFamily(self::FONT_FAMILY)
            ->styleLineHeight(self::LINE_HEIGHT)
            ->styleMarginTop($marginTop)
            ->styleTextColor(self::TEXT_COLOR_RED);
    }

    /**
     * @param $personId
     * @param bool $hasTransfer
     * @param Slice $gradeLanesSlice
     * @param Slice $subjectLanesSlice
     * @return Slice[]
     */
    public function getBody($personId, $hasTransfer, Slice $gradeLanesSlice, Slice $subjectLanesSlice)
    {
        // zusammen 100%
        $width1 = '20%';
        $width2 = '45%';
        $width3 = '4%';
        $width4 = '15%';
        $width5 = '16%';

        $sliceArray = array();

        $sliceArray[] = (new Slice())
            ->styleMarginTop('15px')
            ->addSection((new Section())
                ->addElementColumn(self::getBodyElement(
                    'Vor- und Zuname:'
                ), $width1)
                ->addElementColumn(self::getBodyElement(
                    '{{ Content.P' . $personId . '.Person.Data.Name.First }} {{ Content.P' . $personId . '.Person.Data.Name.Last }}'
                    , true
                ), $width2)
                ->addElementColumn((new Element()
                ), $width3)
                ->addElementColumn(self::getBodyElement(
                    'Klasse:'
                ), $width4)
                ->addElementColumn(self::getBodyElement(
                    '{{ Content.P' . $personId . '.Division.Data.Name }}'
                    , true
                ), $width5)
            )
            ->addSection((new Section())
                ->addElementColumn(self::getBodyElement(
                    'geboren am:'
                ), $width1)
                ->addElementColumn(self::getBodyElement(
                    '{% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthday is not empty) %}
                        {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                    {% else %}
                        &nbsp;
                    {% endif %}'
                    , true
                ), $width2)
                ->addElementColumn((new Element()
                ), $width3)
                ->addElementColumn(self::getBodyElement(
                    'Schuljahr:'
                ), $width4)
                ->addElementColumn(self::getBodyElement(
                    '{{ Content.P' . $personId . '.Division.Data.Year }}'
                    , true
                ), $width5)
            );

        $course = 'nahm am Unterricht der Schulart ' . TblType::IDENT_OBER_SCHULE .  ' teil.';
        if ($this->getLevel() > 6
            && ($tblCourse = $this->getTblCourse())
        ) {
            if ($tblCourse->getName() == 'Realschule') {
                $course = 'nahm am Unterricht der Schulart ' . TblType::IDENT_OBER_SCHULE .  ' mit dem Ziel des Realschulabschlusses teil.';
            } elseif ($tblCourse->getName() == 'Hauptschule') {
                $course = 'nahm am Unterricht der Schulart ' . TblType::IDENT_OBER_SCHULE .  ' mit dem Ziel des Hauptschulabschlusses teil.';
            }
        }

        $sliceArray[] = (new Slice)
            ->addElement(self::getBodyElement($course));

        $sliceArray[] = $gradeLanesSlice;

        $sliceArray[] = (new Slice())
            ->addElement(self::getBodyElement('Leistung in den einzelnen Fächern:', true, '10px'));

        $sliceArray[] = $subjectLanesSlice;

//        $sliceArray[] = self::getOrientation($personId);

        $sliceArray[] = (new Slice)
            ->addSection((new Section())
                ->addElementColumn(self::getBodyElement(
                    'Bemerkungen:', true, '0px'
                ))
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent(
                        '{% if(Content.P' . $personId . '.Input.Remark is not empty) %}
                        {{ Content.P' . $personId . '.Input.Remark|nl2br }}
                        {% else %}
                            &nbsp;
                        {% endif %}'
                    )
                    ->styleTextSize(self::TEXT_SIZE)
                    ->styleFontFamily(self::FONT_FAMILY)
                    ->styleLineHeight(self::LINE_HEIGHT)
                    ->styleTextColor(self::TEXT_COLOR_BLUE)
                    ->styleMarginTop('0px')
                    ->styleHeight($hasTransfer ? '80px' : '110px'))
            );

        if ($hasTransfer) {
            $sliceArray[] = (new Slice)
                ->addSection((new Section())
                    ->addElementColumn(self::getBodyElement('Versetzungsvermerk:')
                        , '22%')
                    ->addElementColumn(self::getBodyElement(
                        '{% if(Content.P' . $personId . '.Input.Transfer) %}
                            {{ Content.P' . $personId . '.Input.Transfer }}.
                        {% else %}
                              &nbsp;
                        {% endif %}'
                    ))
                );
        }

        $sliceArray[] = (new Slice)
            ->addSection((new Section())
                ->addElementColumn(self::getBodyElement('Fehltage entschuldigt:'), '25%')
                ->addElementColumn(self::getBodyElement(
                    '{% if(Content.P' . $personId . '.Input.Missing is not empty) %}
                        {{ Content.P' . $personId . '.Input.Missing }}
                    {% else %}
                        0
                    {% endif %}')
                    , '10%')
                ->addElementColumn(self::getBodyElement('unentschuldigt:'), '19%')
                ->addElementColumn(self::getBodyElement(
                    '{% if(Content.P' . $personId . '.Input.Bad.Missing is not empty) %}
                        {{ Content.P' . $personId . '.Input.Bad.Missing }}
                    {% else %}
                        0
                    {% endif %}')
                )
            );

        $sliceArray[] = (new Slice)
            ->addSection((new Section())
                ->addElementColumn(self::getBodyElement('Datum:'), '15%')
                ->addElementColumn(self::getBodyElement(
                    '{% if(Content.P' . $personId . '.Input.Date is not empty) %}
                        {{ Content.P' . $personId . '.Input.Date }}
                    {% else %}
                        0
                    {% endif %}')
                )
            );

        $sliceArray[] = self::getSignIndividualPart($personId);

        return $sliceArray;
    }

    /**
     * @param $content
     * @param bool $isBold
     * @param string $marginTop
     *
     * @return Element
     */
    private static function getBodyElement($content, $isBold = false, $marginTop = '7px')
    {

        return (new Element())
            ->setContent($content)
            ->styleTextSize(self::TEXT_SIZE)
            ->styleTextBold($isBold ? 'bold' : 'normal')
            ->styleFontFamily(self::FONT_FAMILY)
            ->styleTextColor(self::TEXT_COLOR_BLUE)
            ->styleMarginTop($marginTop);
    }

    /**
     * @param $personId
     *
     * @return Slice
     */
    private static function getSignIndividualPart($personId)
    {

        $textSize = self::TEXT_SIZE;
        $fontFamily = self::FONT_FAMILY;

        return (new Slice())
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderBottom('1px', self::TEXT_COLOR_BLUE)
                    ->styleTextSize($textSize)
                    ->styleFontFamily($fontFamily)
                    ->styleTextColor(self::TEXT_COLOR_BLUE)
                    ->styleMarginTop('10px')
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent('Dienstsiegel der Schule')
                    ->styleAlignCenter()
                    ->styleTextSize('10px')
                    ->styleFontFamily($fontFamily)
                    ->styleTextColor(self::TEXT_COLOR_BLUE)
                    ->styleMarginTop('20px')
                    , '40%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderBottom('1px', self::TEXT_COLOR_BLUE)
                    ->styleTextSize($textSize)
                    ->styleFontFamily($fontFamily)
                    ->styleTextColor(self::TEXT_COLOR_BLUE)
                    ->styleMarginTop('10px')
                    , '30%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('
                                {% if(Content.P' . $personId . '.Headmaster.Description is not empty) %}
                                    {{ Content.P' . $personId . '.Headmaster.Description }}
                                {% else %}
                                    Schulleiter(in)
                                {% endif %}'
                    )
                    ->styleAlignCenter()
                    ->styleFontFamily($fontFamily)
                    ->styleTextColor(self::TEXT_COLOR_BLUE)
                    ->styleTextSize('10px')
                    , '30%')
                ->addElementColumn((new Element())
                    , '40%')
                ->addElementColumn((new Element())
                    ->setContent('
                                {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                                    {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                                {% else %}
                                    Klassenlehrer(in)
                                {% endif %}'
                    )
                    ->styleAlignCenter()
                    ->styleFontFamily($fontFamily)
                    ->styleTextColor(self::TEXT_COLOR_BLUE)
                    ->styleTextSize('10px')
                    , '30%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent(
                        '{% if(Content.P' . $personId . '.Headmaster.Name is not empty) %}
                                            {{ Content.P' . $personId . '.Headmaster.Name }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}'
                    )
                    ->styleAlignCenter()
                    ->styleMarginTop('-3px')
                    ->styleTextSize('10px')
                    ->styleFontFamily($fontFamily)
                    ->styleTextColor(self::TEXT_COLOR_BLUE)
                    , '30%')
                ->addElementColumn((new Element())
                    , '40%')
                ->addElementColumn((new Element())
                    ->setContent(
                        '{% if(Content.P' . $personId . '.DivisionTeacher.Name is not empty) %}
                                            {{ Content.P' . $personId . '.DivisionTeacher.Name }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}'
                    )
                    ->styleAlignCenter()
                    ->styleMarginTop('-3px')
                    ->styleTextSize('10px')
                    ->styleFontFamily($fontFamily)
                    ->styleTextColor(self::TEXT_COLOR_BLUE)
                    , '30%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Zur Kenntnis genommen:')
                    ->styleTextSize($textSize)
                    ->styleFontFamily($fontFamily)
                    ->styleTextColor(self::TEXT_COLOR_BLUE)
                    ->styleMarginTop('15px')
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleTextSize($textSize)
                    ->styleBorderBottom('1px', self::TEXT_COLOR_BLUE)
                    ->styleFontFamily($fontFamily)
                    ->styleTextColor(self::TEXT_COLOR_BLUE)
                    ->styleMarginTop('15px')
                    , '70%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent('Personensorgeberechtigte/r')
                    ->styleAlignCenter()
                    ->styleMarginTop('-3px')
                    ->styleTextSize('10px')
                    ->styleFontFamily($fontFamily)
                    ->styleTextColor(self::TEXT_COLOR_BLUE)
                    , '40%')
                ->addElementColumn((new Element())
                    , '30%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Notenstufen: 1 = sehr gut, 2 = gut, 3 = befriedigend, 4 = ausreichend,
                                5 = mangelhaft, 6 = ungenügend')
                    ->styleTextSize('10px')
                    ->styleFontFamily($fontFamily)
                    ->styleTextColor(self::TEXT_COLOR_BLUE)
                    ->styleMarginTop('15px')
                )
            );
    }
}