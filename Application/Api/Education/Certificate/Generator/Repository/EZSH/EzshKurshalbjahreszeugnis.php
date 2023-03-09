<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 14.11.2018
 * Time: 11:49
 */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EZSH;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class EzshKurshalbjahreszeugnis
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\EZSH
 */
class EzshKurshalbjahreszeugnis extends Certificate
{
    const TEXT_SIZE = '11pt';
    const TEXT_SIZE_SUBJECT = '10pt';
    const TEXT_SIZE_SMALL = '9pt';
    const FONT_FAMILY = 'Calluna Sans';
    const FONT_FAMILY_BOLD = 'Calluna Sans Bold';
    const LINE_HEIGHT = '85%';

    /**
     * @var array|false
     */
    private $AdvancedCourses = false;

    /**
     * @var array|false
     */
    private $BasicCourses = false;

    /**
     * @return false|TblSubject
     */
    protected function getFirstAdvancedCourse()
    {
        foreach ($this->AdvancedCourses as $tblSubject) {
            $name = $tblSubject->getName();
            if ($name == 'Deutsch' || $name == 'Mathematik') {
                return $tblSubject;
            }
        }

        return false;
    }

    /**
     * @return false|TblSubject
     */
    protected function getSecondAdvancedCourse()
    {
        foreach ($this->AdvancedCourses as $tblSubject) {
            $name = $tblSubject->getName();
            if ($name != 'Deutsch' && $name != 'Mathematik') {
                return $tblSubject;
            }
        }

        return false;
    }

    /**
     * @param TblPerson|null $tblPerson
     * @return Page
     * @internal param bool $IsSample
     *
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;
        $headerHeight = '110px';

        if ($this->isSample()) {
            $Header = (new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element)
                        ->styleAlignCenter()
                        , '25%')
                    ->addElementColumn((new Element\Sample())
                        ->styleTextSize('30px')
                    )
                    ->addElementColumn((new Element())
                        , '25%')
                )
                ->styleHeight($headerHeight);
        } else {
            $Header = (new Slice())
                    ->styleHeight($headerHeight);
        }

        $this->setCourses($tblPerson);

        return (new Page())
            ->addSlice(
                $Header
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('KURSHALBJAHRESZEUGNIS')
                    ->styleTextSize('22px')
                    ->styleFontFamily(self::FONT_FAMILY_BOLD)
                    ->styleLineHeight(self::LINE_HEIGHT)
                    ->styleTextBold()
            ))
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Kurshalbjahr')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleTextSize(self::TEXT_SIZE)
                        , '18%')
                    ->addElementColumn((new Element())
                        ->setContent(
                            '  {% if(Content.P' . $personId . '.Division.Data.Course.Name is not empty) %}
                                {{ Content.P' . $personId . '.Division.Data.Course.Name }}
                            {% else %}
                                &nbsp;
                            {% endif %}'
                        )
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleBorderBottom()
                        ->styleAlignCenter()
                        , '15%')
                    ->addElementColumn((new Element())
                        ->setContent('Schuljahr')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleAlignRight()
                        , '18%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Division.Data.Year }}')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleBorderBottom()
                        ->styleAlignCenter()
                        , '15%')
                    ->addElementColumn((new Element()))
                )->styleMarginTop('35px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Vor- und Zuname')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleTextSize(self::TEXT_SIZE)
                        , '18%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                              {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleAlignCenter()
                        ->styleBorderBottom()
                    )
                )->styleMarginTop('10px')
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Leistungen in den einzelnen Fächern: &nbsp;&nbsp;&nbsp;¹')
                    ->styleFontFamily(self::FONT_FAMILY_BOLD)
                    ->styleTextSize(self::TEXT_SIZE)
                    ->styleMarginTop('20px')
                    ->styleTextBold()
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Leistungskurse')
                    ->styleFontFamily(self::FONT_FAMILY_BOLD)
                    ->styleTextSize(self::TEXT_SIZE)
                    ->styleMarginTop('8px')
                    ->styleTextBold()
                )
            )
            ->addSlice($this->getAdvancedCourses($tblPerson))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Grundkurse')
                    ->styleFontFamily(self::FONT_FAMILY_BOLD)
                    ->styleLineHeight(self::LINE_HEIGHT)
                    ->styleTextSize(self::TEXT_SIZE)
                    ->styleMarginTop('18px')
                    ->styleTextBold()
                )
            )
            ->addSlice($this->getBasicCourses($tblPerson))
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent(
                            '{% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 2 %}
                                Die Schülerin
                            {% else %}
                                {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 1 %}
                                    Der Schüler
                                {% else %}
                                    Die Schülerin/Der Schüler¹ kann ihre/seine¹ Ausbildung am Gymnasium fortsetzen.
                                {% endif %}
                            {% endif %}
                            erbringt eine Besondere Lernleistung mit dem Thema:'
                        )
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleMarginTop('10px')
                    )
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent(
                            '{% if(Content.P' . $personId . '.Input.BellSubject is not empty) %}
                                {{ Content.P' . $personId . '.Input.BellSubject }}
                            {% else %}
                                ---
                            {% endif %}'
                        )
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleBorderBottom()
                        ->styleMarginTop('5px')
                    )
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Bemerkungen: &nbsp;&nbsp;&nbsp; {% if(Content.P' . $personId . '.Input.Remark is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Remark|nl2br }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleLineHeight(self::LINE_HEIGHT)
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleHeight('45px')
                    )
                )
                ->styleMarginTop('10px')
            )
            ->addSlice((new Slice)
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Datum:')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleTextSize(self::TEXT_SIZE)
                        , '8%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Input.Date is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Date }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleBorderBottom('1px', '#000')
                        ->styleAlignCenter()
                        , '22%')
                    ->addElementColumn((new Element()))
                )
                ->styleMarginTop('10px')
            )
            ->addSlice($this->getOwnSignPart($personId))
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Zur Kenntnis genommen:')
                        ->styleMarginTop('10px')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleTextSize(self::TEXT_SIZE)
                        , '30%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleMarginTop('10px')
                        ->styleBorderBottom()
                        , '40%')
                    ->addElementColumn((new Element())
                        , '30%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '30%')
                    ->addElementColumn((new Element())
                        ->setContent('Eltern')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleTextSize(self::TEXT_SIZE_SMALL)
                        ->styleLineHeight(self::LINE_HEIGHT)
                        ->styleAlignCenter()
                        , '40%')
                    ->addElementColumn((new Element())
                        , '30%')
                )
            )
            ->addSlice($this->setPointsOverview())
            ->addSlice((new Slice)
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->styleBorderBottom()
                        , '10%')
                    ->addElementColumn((new Element())
                        , '90%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('¹ &nbsp;&nbsp;&nbsp;&nbsp; Bei Fächern, die nicht belegt wurden, ist das betreffende Feld zu sperren.')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleTextSize('9.5px')
                        , '30%')
                )
                ->styleMarginTop('10px')
            );
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    private function setCourses(TblPerson $tblPerson = null)
    {
        list($this->AdvancedCourses, $this->BasicCourses) = DivisionCourse::useService()->getCoursesForStudent($tblPerson);
    }

    private function getAdvancedCourses(TblPerson $tblPerson = null, $IsGradeUnderlined = true)
    {

        $slice = new Slice();
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $section = new Section();
        if (($tblSubject = $this->getFirstAdvancedCourse())) {
            $this->setCourseSubject($tblSubject, $section, true, $IsGradeUnderlined, $personId);
        } else {
            $this->setCourseSubject(null, $section, true, $IsGradeUnderlined, $personId);
        }

        $section
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                , '2%');

        if (($tblSubject = $this->getSecondAdvancedCourse())) {
            $this->setCourseSubject($tblSubject, $section, true, $IsGradeUnderlined, $personId);
        } else {
            $this->setCourseSubject(null, $section, true, $IsGradeUnderlined, $personId);
        }

        $slice->addSection($section);

        return $slice;
    }

    /**
     * @param TblPerson|null $tblPerson
     * @param bool $IsGradeUnderlined
     *
     * @return Slice
     */
    private function getBasicCourses(TblPerson $tblPerson = null, $IsGradeUnderlined = true)
    {

        $slice = new Slice();
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $SubjectStructure = array();

        if (($tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($this->getCertificateEntity()))) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if ($tblSubject) {
                    $isAddSubject = false;
                    // Student has basicCourse? => Add Subject to Certificate
                    if (isset($this->BasicCourses[$tblSubject->getId()])) {
                        $isAddSubject = true;
                    } else {
                        // Grade Missing, But Subject Essential => Add Subject to Certificate
                        if ($tblCertificateSubject->isEssential()) {
                            $isAddSubject = true;
                        }
                    }

                    if ($isAddSubject) {
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym']
                            = $tblSubject->getAcronym();
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName']
                            = $tblSubject->getName();
                    }
                }
            }

            // Shrink Lanes
            $LaneCounter = array(1 => 0, 2 => 0);
            $SubjectLayout = array();
            ksort($SubjectStructure);
            foreach ($SubjectStructure as $SubjectList) {
                ksort($SubjectList);
                foreach ($SubjectList as $Lane => $Subject) {
                    $SubjectLayout[$LaneCounter[$Lane]][$Lane] = $Subject;
                    $LaneCounter[$Lane]++;
                }
            }
            $SubjectStructure = $SubjectLayout;
        }

        $count = 0;
        $subSection = false;
        $isShrinkMarginTop = false;
        foreach ($SubjectStructure as $SubjectList) {
            $count++;
            // Sort Lane-Ranking (1,2...)
            ksort($SubjectList);

            $section = new Section();
            if (count($SubjectList) == 1 && isset($SubjectList[2])) {
                $section->addElementColumn((new Element()), 'auto');
                $isSecondLane = true;
            } else {
                $isSecondLane = false;
            }

            foreach ($SubjectList as $Lane => $Subject) {
                if (($tblSubject = Subject::useService()->getSubjectByAcronym($Subject['SubjectAcronym']))) {
                    if (isset($this->AdvancedCourses[$tblSubject->getId()])) {
                        $isAdvancedCourse = true;
                    } else {
                        $isAdvancedCourse = false;
                    }

                    if (($tblCategory = Subject::useService()->getCategoryByIdentifier('FOREIGNLANGUAGE'))
                        && Subject::useService()->existsCategorySubject($tblCategory, $tblSubject)
                    ) {
                        $isLanguage = true;
                    } else {
                        $isLanguage = false;
                    }

                    $this->setCourseSubject($tblSubject, $section, true, $IsGradeUnderlined, $personId,
                        $isShrinkMarginTop ? '2px' : '10px', !$isAdvancedCourse, $isLanguage);

                    if ($isLanguage) {
                        $subSection = new Section();
                        $subSection
                            ->addElementColumn((new Element())
                                ->setContent('Fremdsprache')
                                ->styleFontFamily(self::FONT_FAMILY)
                                ->styleMarginTop('0px')
                                ->styleMarginBottom('0px')
                                ->styleTextSize('8px')
                            );
                    }

                    if ($isSecondLane) {
                        $slice->addSection($section);
                        if ($subSection) {
                            $slice->addSection($subSection);
                            $subSection = false;
                            $isShrinkMarginTop = true;
                        } else {
                            $isShrinkMarginTop = false;
                        }
                        $section = new Section();
                    } else {
                        $section
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                ->styleMarginTop($isShrinkMarginTop ? '2px' : '10px')
                                , '2%');
                    }

                    $isSecondLane = !$isSecondLane;
                }
            }

            if ($isSecondLane) {
                $section
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                    );
                $slice->addSection($section);
            }
        }

        return $slice
            ->styleHeight('270px');
    }

    /**
     * @param TblSubject|null $tblSubject
     * @param Section $section
     * @param $isSubjectUnderlined
     * @param $isGradeUnderlined
     * @param $personId
     * @param string $marginTop
     * @param bool $isGradeShown
     * @param bool $isLanguage
     */
    private function setCourseSubject(
        TblSubject $tblSubject = null,
        Section $section,
        $isSubjectUnderlined,
        $isGradeUnderlined,
        $personId,
        $marginTop = '10px',
        $isGradeShown = true,
        $isLanguage = false
    ) {

        $width = '36%';
        if ($tblSubject) {
            if ($tblSubject->getAcronym() == 'GRW') {
                $section
                    ->addElementColumn((new Element())
                        ->setContent($tblSubject->getName())
                        ->styleBorderBottom($isSubjectUnderlined || $isLanguage ? '1px' : '0px')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleTextSize(self::TEXT_SIZE_SUBJECT)
                        ->styleLineHeight(self::LINE_HEIGHT)
                        ->styleMarginTop('2px')
                        , $width);
            } else {
                $section
                    ->addElementColumn((new Element())
                        ->setContent($tblSubject->getName())
                        ->styleBorderBottom($isSubjectUnderlined || $isLanguage ? '1px' : '0px')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleTextSize(self::TEXT_SIZE_SUBJECT)
                        ->styleMarginTop($marginTop)
                        , $width);
            }
        } else {
            $section
                ->addElementColumn((new Element())
                    ->setContent('---')
                    ->styleBorderBottom()
                    ->styleAlignCenter()
                    ->styleMarginTop($marginTop)
                    , $width);
        }
        $section
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleMarginTop($marginTop)
                , '1%')
            ->addElementColumn((new Element())
                ->setContent(
                    $tblSubject && $isGradeShown ?
                        '{% if(Content.P' . $personId . '.Grade.Data["' . $tblSubject->getAcronym() . '"] is not empty) %}
                            {{ Content.P' . $personId . '.Grade.Data["' . $tblSubject->getAcronym() . '"] }}
                        {% else %}
                             &ndash;
                        {% endif %}'
                        : '&ndash;')
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleTextSize(self::TEXT_SIZE_SUBJECT)
                ->styleAlignCenter()
                ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
                ->styleBorderBottom($isGradeUnderlined ? '1px' : '0px', '#000')
                ->styleMarginTop($marginTop)
                , '12%');
    }

    /**
     * @param string $MarginTop
     *
     * @return Slice
     */
    private function setPointsOverview($MarginTop = '5px')
    {

        $textSize = '10px';
        $slice = new Slice();
        $slice
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Für die Umsetzung der Noten in Punkte gilt:')
                    ->styleFontFamily(self::FONT_FAMILY)
                    ->styleTextSize($textSize)
                )
            );

        $section = new Section();
        $this->setColumnElement($section, 'Notenstufen', $textSize);
        $this->setColumnElement($section, 'sehr gut', $textSize);
        $this->setColumnElement($section, 'gut', $textSize);
        $this->setColumnElement($section, 'befriedigend', $textSize);
        $this->setColumnElement($section, 'ausreichend', $textSize);
        $this->setColumnElement($section, 'mangelhaft', $textSize);
        $this->setColumnElement($section, 'ungenügend', $textSize, false, true);
        $slice
            ->addSection($section);

        $section = new Section();
        $this->setColumnElement($section, 'Noten', $textSize);
        $this->setColumnElement($section, '+&nbsp;&nbsp;&nbsp;1&nbsp;&nbsp;&nbsp;-', $textSize);
        $this->setColumnElement($section, '+&nbsp;&nbsp;&nbsp;2&nbsp;&nbsp;&nbsp;-', $textSize);
        $this->setColumnElement($section, '+&nbsp;&nbsp;&nbsp;3&nbsp;&nbsp;&nbsp;-', $textSize);
        $this->setColumnElement($section, '+&nbsp;&nbsp;&nbsp;4&nbsp;&nbsp;&nbsp;-', $textSize);
        $this->setColumnElement($section, '+&nbsp;&nbsp;&nbsp;5&nbsp;&nbsp;&nbsp;-', $textSize);
        $this->setColumnElement($section, '6', $textSize, false, true);
        $slice
            ->addSection($section);

        $section = new Section();
        $this->setColumnElement($section, 'Punkte', $textSize, true);
        $this->setColumnElement($section, '15 14 13', $textSize, true);
        $this->setColumnElement($section, '12 11 10', $textSize, true);
        $this->setColumnElement($section, '09 08 07', $textSize, true);
        $this->setColumnElement($section, '06 05 04', $textSize, true);
        $this->setColumnElement($section, '03 02 01', $textSize, true);
        $this->setColumnElement($section, '00', $textSize, true, true);
        $slice
            ->addSection($section);

        return $slice
            ->styleMarginTop($MarginTop);
    }

    /**
     * @param Section $section
     * @param string $name
     * @param $textSize
     * @param bool $isBorderBottom
     * @param bool $isBorderRight
     */
    private function setColumnElement(
        Section $section,
        $name,
        $textSize,
        $isBorderBottom = false,
        $isBorderRight = false
    ) {

        $section
            ->addElementColumn((new Element())
                ->setContent($name)
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleTextSize($textSize)
                ->styleAlignCenter()
                ->styleBorderLeft()
                ->styleBorderTop()
                ->styleBorderRight($isBorderRight ? '1px' : '0px')
                ->styleBorderBottom($isBorderBottom ? '1px' : '0px')
                , '14.28%');
    }

    /**
     * @param $personId
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getOwnSignPart($personId, $MarginTop = '25px')
    {
        $SignSlice = (new Slice());

        $SignSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleAlignCenter()
                ->styleBorderBottom('1px', '#000')
                , '30%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleAlignCenter()
                , '40%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleAlignCenter()
                ->styleBorderBottom('1px', '#000')
                , '30%')
        )
            ->styleMarginTop($MarginTop)
            ->addSection((new Section())
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
                    ->styleTextSize(self::TEXT_SIZE_SMALL)
                    ->styleAlignCenter()
                    , '30%')
                ->addElementColumn((new Element())
                    , '5%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    , '30%')
                ->addElementColumn((new Element())
                    , '5%')
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if(Content.P' . $personId . '.Tudor.Description is not empty) %}
                                {{ Content.P' . $personId . '.Tudor.Description }}
                            {% else %}
                                Tutor(in)
                            {% endif %}'
                    )
                    ->styleFontFamily(self::FONT_FAMILY)
                    ->styleLineHeight(self::LINE_HEIGHT)
                    ->styleTextSize(self::TEXT_SIZE_SMALL)
                    ->styleAlignCenter()
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
                    ->styleFontFamily(self::FONT_FAMILY)
                    ->styleLineHeight(self::LINE_HEIGHT)
                    ->styleTextSize(self::TEXT_SIZE_SMALL)
                    ->stylePaddingTop('2px')
                    ->styleAlignCenter()
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
                    ->styleFontFamily(self::FONT_FAMILY)
                    ->styleLineHeight(self::LINE_HEIGHT)
                    ->styleTextSize(self::TEXT_SIZE_SMALL)
                    ->stylePaddingTop('2px')
                    ->styleAlignCenter()
                    , '30%')
            );

        return $SignSlice;
    }
}