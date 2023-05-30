<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\HOGA;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generate\Generate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Layout\Repository\Container;

abstract class Style extends Certificate
{
    const FONT_FAMILY = 'FreeSans';
    const BACKGROUND = self::BACKGROUND_GRADE_FIELD;
    const TEXT_SIZE_SMALL = '12.5px';
    const TEXT_SIZE_NORMAL = '14px';
    const TEXT_SIZE_LARGE = '15.5px';
    const PADDING_TOP_GRADE = '-4px';
    const MARGIN_TOP_GRADE_LINE = '8px';
    const SUBJECT_WIDTH = 25.5;
    const GRADE_WIDTH = 22.5;

    /**
     * @param array $school
     * @param string $title
     * @param bool $isSchoolLogoVisible
     *
     * @return Slice
     */
    protected function getHeader(array $school, string $title = '', bool $isStateLogoVisible = false,
        bool $isSchoolLogoVisible = false) : Slice
    {
        $logoHeight = '50px';
        $logoWidth = '165px';

        $slice = new Slice();
        $slice->addSection($this->getSectionSpace('10px'));

        $section = new Section();
        // Individually Logo
        if ($isSchoolLogoVisible) {
            $section->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/HOGA.jpg',
                'auto', $logoHeight)), '39%');
        } else {
            $section->addElementColumn((new Element()), '39%');
        }

        // Sample
        if($this->isSample()){
            $section->addElementColumn((new Element\Sample())->styleTextSize('30px'));
        } else {
            $section->addElementColumn((new Element())->setContent('&nbsp;')->styleTextSize('30px'), '22%');
        }

        // Standard Logo
        if ($isStateLogoVisible) {
            $section->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                $logoWidth, $logoHeight))
                ->styleAlignRight()
                , '39%');
        } else {
            $section->addElementColumn((new Element()), '39%');
        }

        $slice->addSection($section);
        $slice->addSection($this->getSectionSpace('35px'));

        foreach ($school as $line) {
            $slice->addSection((new Section())
                ->addElementColumn(
                    $this->getElement($line, '14.5px')
                        ->styleAlignCenter()
                        ->styleMarginTop('-10px')
                )
            );
        }

        if ($title) {
            $slice->addSection((new Section())->addElementColumn(
                $this->getElement($title, '19px')
                    ->styleTextBold()
                    ->styleAlignCenter()
            ));
        }

        return $slice;
    }

    /**
     * @param array $school
     * @param bool $isSchoolLogoVisible
     *
     * @return Slice
     */
    protected function getHeaderBGym(array $school, bool $isStateLogoVisible = false,
        bool $isSchoolLogoVisible = false) : Slice
    {
        $logoHeight = '50px';
        $logoWidth = '165px';

        $slice = new Slice();
        $slice->addSection($this->getSectionSpace('50px'));

        $section = new Section();
        // Individually Logo
        if ($isSchoolLogoVisible) {
            $section->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/HOGA.jpg',
                'auto', $logoHeight)), '39%');
        } else {
            $section->addElementColumn((new Element()), '39%');
        }

        // Sample
        if($this->isSample()){
            $section->addElementColumn((new Element\Sample())->styleTextSize('30px'));
        } else {
            $section->addElementColumn((new Element())->setContent('&nbsp;')->styleTextSize('30px'), '22%');
        }
        // Standard Logo
        if ($isStateLogoVisible) {
            $section->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                $logoWidth, $logoHeight))
                ->styleAlignRight()
                , '39%');
        } else {
            $section->addElementColumn((new Element()), '39%');
        }
        $slice->addSection($section);

        $slice->addSection($this->getSectionSpace('5px'));

        foreach ($school as $line) {
            $slice->addSection((new Section())
                ->addElementColumn(
                    $this->getElement($line, self::TEXT_SIZE_SMALL)
                        ->styleAlignCenter()
                        ->styleMarginTop('-10px')
                )
            );
        }

        return $slice;
    }

    /**
     * @param string $title
     * @param string $subTitle
     * @param string $marginTop
     *
     * @return Slice
     */
    protected function getTitleBGym(string $title, string $subTitle, string $marginTop = '15px') : Slice
    {
        return (new Slice())
            ->styleMarginTop($marginTop)
            ->addElement($this->getElement($title, '25px')->styleAlignCenter())
            ->addElement($this->getElement($subTitle, '17px')->styleAlignCenter());
    }

    /**
     * @param int $personId
     * @param string $marginTop
     *
     * @return Slice
     */
    protected function getSubjectArea(int $personId, string $marginTop = '2px') : Slice
    {
        return (new Slice())
            ->styleMarginTop($marginTop)
            ->addElement($this->getElement(
                '{% if(Content.P' . $personId . '.Input.SubjectArea is not empty) %}
                    Fachrichtung {{ Content.P' . $personId . '.Input.SubjectArea }}
                {% else %}
                    Fachrichtung &ndash;
                {% endif %}'
                , '15px')->styleAlignCenter());
    }

    /**
     * @param int $personId
     * @param string $period
     * @param string $marginTop
     * @param bool $hasBirthInformation
     *
     * @return Slice
     */
    protected function getDivisionYearStudent(
        int $personId,
        string $period = '1. Schulhalbjahr:',
        string $marginTop = '10px',
        bool $hasBirthInformation = false,
        bool $hasLevel = false
    ) : Slice {

        $paddingTop = '4px';
        if ($hasLevel) {
            $slice = (new Slice())
                ->styleMarginTop($marginTop)
                ->addSection((new Section())
                    ->addElementColumn($this->getElement('Klassenstufe:')->stylePaddingTop($paddingTop), '20%')
                    ->addElementColumn($this->getElement(
                        '{{ Content.P' . $personId . '.Division.Data.Level.Name }}',
                        self::TEXT_SIZE_LARGE
                    )->styleTextBold(), '20%')
                    ->addElementColumn($this->getElement('&nbsp;'))
                    ->addElementColumn($this->getElement($period)
                        ->stylePaddingTop($paddingTop)
                        ->styleAlignRight()
                        , '15%')
                    ->addElementColumn($this->getElement('{{ Content.P' . $personId . '.Division.Data.Year }}',
                        self::TEXT_SIZE_LARGE)
                        ->styleTextBold()
                        ->stylePaddingLeft('10px')
                        , '25%')
                );
        } else {
            $slice = (new Slice())
                ->styleMarginTop($marginTop)
                ->addSection((new Section())
                    ->addElementColumn($this->getElement('Klasse:')->stylePaddingTop($paddingTop), '20%')
                    ->addElementColumn($this->getElement(
                        '{{ Content.P' . $personId . '.Division.Data.Name }}',
                        self::TEXT_SIZE_LARGE
                    )->styleTextBold(), '20%')
                    ->addElementColumn($this->getElement('&nbsp;'))
                    ->addElementColumn($this->getElement($period)
                        ->stylePaddingTop($paddingTop)
                        ->styleAlignRight()
                        , '15%')
                    ->addElementColumn($this->getElement('{{ Content.P' . $personId . '.Division.Data.Year }}',
                        self::TEXT_SIZE_LARGE)
                        ->styleTextBold()
                        ->stylePaddingLeft('10px')
                        , '25%')
                );
        }
        $slice->addSection((new Section())
            ->addElementColumn($this->getElement('Vorname und Name:')->stylePaddingTop($paddingTop), '20%')
            ->addElementColumn($this->getElement('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                    {{ Content.P' . $personId . '.Person.Data.Name.Last }}', self::TEXT_SIZE_LARGE)->styleTextBold())
        );

        if ($hasBirthInformation) {
            $slice->addSection((new Section())
                ->addElementColumn($this->getElement('geboren am:')->stylePaddingTop($paddingTop), '20%')
                ->addElementColumn($this->getElement(
                    '{% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthday is not empty) %}
                        {{ Content.P'.$personId.'.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                    {% else %}
                        &nbsp;
                    {% endif %}',
                    self::TEXT_SIZE_LARGE
                )->styleTextBold(), '20%')
                ->addElementColumn($this->getElement('&nbsp;'))
                ->addElementColumn($this->getElement('in:')
                    ->stylePaddingTop($paddingTop)
                    ->styleAlignRight()
                    , '15%')
                ->addElementColumn($this->getElement(
                    '{% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthplace is not empty) %}
                        {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthplace }}
                    {% else %}
                        &nbsp;
                    {% endif %}'
                    , self::TEXT_SIZE_LARGE)
                    ->styleTextBold()
                    ->stylePaddingLeft('10px')
                    , '25%')
            );
        }

        return $slice;
    }

    /**
     * @param int $personId
     * @param string $period
     * @param string $marginTop
     *
     * @return Slice
     */
    protected function getDivisionYearStudentBgj(int $personId, string $period = '1. Schulhalbjahr', string $marginTop = '15px') : Slice
    {
        return (new Slice())
            ->styleMarginTop($marginTop)
            ->addSection((new Section())
                ->addElementColumn($this->getElement('
                    {% if(Content.P'.$personId.'.Person.Data.Name.Salutation is not empty) %}
                        {{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
                    {% else %}
                        Frau/Herr
                    {% endif %}
                    {{ Content.P'.$personId.'.Person.Data.Name.First }} {{ Content.P'.$personId.'.Person.Data.Name.Last }}'
                    , self::TEXT_SIZE_NORMAL)->styleTextBold()
                , '70%')
                ->addElementColumn($this->getElement($period, self::TEXT_SIZE_NORMAL)
                    ->styleAlignRight()
                    , '20%')
                ->addElementColumn($this->getElement('{{ Content.P'.$personId.'.Division.Data.Year }}', self::TEXT_SIZE_NORMAL)
                    ->styleTextBold()
                    ->styleAlignRight()
                    , '10%')
            )
            ->addSection((new Section())
                ->addElementColumn($this->getElement('geboren am', self::TEXT_SIZE_NORMAL), '13%')
                ->addElementColumn($this->getElement(
                    '{% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthday is not empty) %}
                        {{ Content.P'.$personId.'.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                    {% else %}
                        &nbsp;
                    {% endif %}',
                    self::TEXT_SIZE_NORMAL
                )->styleTextBold(), '10%')
                ->addElementColumn($this->getElement('in', self::TEXT_SIZE_NORMAL)->styleAlignRight(), '42%')
                ->addElementColumn($this->getElement(
                    '{% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthplace is not empty) %}
                        {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthplace }}
                    {% else %}
                        &nbsp;
                    {% endif %}',
                    self::TEXT_SIZE_NORMAL
                )->styleTextBold()->stylePaddingLeft('15px'))
            );
    }

    /**
     * @param int $personId
     * @param string $marginTop
     *
     * @return Slice
     */
    protected function getCustomElective(int $personId, string $marginTop = '5px') : Slice
    {
        $subjectName = '&ndash;';
        $grade = '&ndash;';
        if (($tblPerson = Person::useService()->getPersonById($personId))
            && ($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
        ) {

            // Neigungskurs
            if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'))
                && ($tblSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                    $tblStudentSubjectType))
            ) {
                /** @var TblStudentSubject $tblStudentSubject */
                $tblStudentSubject = current($tblSubjectList);
                if (($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
                    $subjectName = $tblSubject->getName();
                    $grade = '{% if(Content.P' . $personId . '.Grade.Data["' . $tblSubject->getAcronym() . '"] is not empty) %}
                            {{ Content.P' . $personId . '.Grade.Data["' . $tblSubject->getAcronym() . '"] }}
                        {% else %}
                            &ndash;
                        {% endif %}';
                }
            }
        }

        return (new Slice())
            ->styleMarginTop($marginTop)
            ->addSection((new Section())
                ->addElementColumn($this->getElement('Wahlbereich:', self::TEXT_SIZE_NORMAL)
                    ->styleTextBold()
                    ->styleMarginTop('6px')
                    , self::SUBJECT_WIDTH . '%')
                ->addElementColumn($this->getElement($subjectName, self::TEXT_SIZE_SMALL)
                    ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE))
                ->addElementColumn($this->getElement($grade, self::TEXT_SIZE_NORMAL)
                    ->styleAlignCenter()
                    ->styleBackgroundColor(self::BACKGROUND)
                    ->stylePaddingTop(self::PADDING_TOP_GRADE)
                    ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
                    , self::GRADE_WIDTH . '%')
            );
    }

    /**
     * @param int $personId
     * @param string $marginTop
     * @param string $height
     *
     * @return Slice
     */
    public function getCustomRating(int $personId, string $marginTop = '5px', string $height = '65px') : Slice
    {
        $tblSetting = Consumer::useService()->getSetting('Education', 'Certificate', 'Generator', 'IsDescriptionAsJustify');
        $slice = (new Slice())
            ->styleMarginTop($marginTop)
            ->styleHeight($height)
            ->styleLineHeight('80%')
            ->addElement($this->getElement('Einschätzung:', self::TEXT_SIZE_NORMAL)->styleTextBold());

        $element = $this->getElement(
            '{% if(Content.P'.$personId.'.Input.Rating is not empty) %}
                {{ Content.P'.$personId.'.Input.Rating|nl2br }}
            {% else %}
                ---
            {% endif %}',
            self::TEXT_SIZE_SMALL
        );

        if($tblSetting && $tblSetting->getValue()){
            $element->styleAlignJustify();
        }

        return $slice->addElement($element);
    }

    /**
     * @param int $personId
     * @param string $marginTop
     *
     * @return Slice
     */
    public function getCustomTeamExtra(int $personId, string $marginTop = '3px', $isSmall = true) : Slice
    {
        return (new Slice())
            ->styleLineHeight($isSmall ? '80%' : '100%')
            ->styleMarginTop($marginTop)
            ->addElement($this->getElement(
                '<b>Teilnahme an zusätzlichen schulischen Veranstaltungen:</b>
                {% if(Content.P' . $personId . '.Input.TeamExtra is not empty) %}
                    {{ Content.P' . $personId . '.Input.TeamExtra|nl2br }}
                {% else %}
                    ---
                {% endif %}',
                $isSmall ? self::TEXT_SIZE_SMALL : self::TEXT_SIZE_NORMAL
            ));
    }

    /**
     * @param int $personId
     * @param string $marginTop
     * @param string $height
     * #
     * @return Slice
     */
    public function getCustomRemark(int $personId, string $marginTop = '31px', string $height = '70px') : Slice
    {
        $tblSetting = Consumer::useService()->getSetting('Education', 'Certificate', 'Generator',
            'IsDescriptionAsJustify');
        $slice = (new Slice())
            ->styleMarginTop($marginTop)
            ->styleHeight($height)
            ->styleLineHeight('80%')
            ->addElement($this->getElement('Bemerkungen:', self::TEXT_SIZE_NORMAL)->styleTextBold());

        $element = $this->getElement(
            '{% if(Content.P' . $personId . '.Input.Remark is not empty) %}
                {{ Content.P' . $personId . '.Input.Remark|nl2br }}
            {% else %}
                &nbsp;
            {% endif %}',
            self::TEXT_SIZE_SMALL
        );
        if ($tblSetting && $tblSetting->getValue()) {
            $element->styleAlignJustify();
        }

        return $slice->addElement($element);
    }

    /**
     * @param int $personId
     * @param string $marginTop
     * @param string $height
     * #
     * @return Slice
     */
    public function getCustomRemarkBgj(int $personId, string $marginTop = '10px', string $height = '150px') : Slice
    {
        $tblSetting = Consumer::useService()->getSetting('Education', 'Certificate', 'Generator',
            'IsDescriptionAsJustify');
        $slice = (new Slice())
            ->styleMarginTop($marginTop)
            ->styleHeight($height)
            ->addSection((new Section())
                ->addElementColumn($this->getElement('Bemerkungen:', self::TEXT_SIZE_NORMAL)->styleBorderBottom()->stylePaddingBottom('-2px'), '12%')
                ->addElementColumn(new Element())
            );

        $element = $this->getElement(
            '{% if(Content.P' . $personId . '.Input.RemarkWithoutTeam is not empty) %}
                {{ Content.P' . $personId . '.Input.RemarkWithoutTeam|nl2br }}
            {% else %}
                &nbsp;
            {% endif %}',
            self::TEXT_SIZE_NORMAL
        );
        $element->styleLineHeight('80%');
        if ($tblSetting && $tblSetting->getValue()) {
            $element->styleAlignJustify();
        }

        return $slice->addElement($element);
    }

    /**
     * @param int $personId
     * @param string $marginTop
     *
     * @return Slice
     */
    public function getCustomAbsence(int $personId, string $marginTop = '2px') : Slice
    {
        return (new Slice())
            ->styleMarginTop($marginTop)
            ->addSection((new Section())
                ->addElementColumn($this->getElement('Fehltage entschuldigt:', self::TEXT_SIZE_SMALL), '23%')
                ->addElementColumn($this->getElement(
                    '{% if(Content.P' . $personId . '.Input.Missing is not empty) %}
                        {{ Content.P' . $personId . '.Input.Missing }}
                    {% else %}
                        &nbsp;
                    {% endif %}',
                    self::TEXT_SIZE_SMALL
                ), '17%')
                ->addElementColumn($this->getElement('unentschuldigt:', self::TEXT_SIZE_SMALL), '17%')
                ->addElementColumn($this->getElement(
                    '{% if(Content.P' . $personId . '.Input.Bad.Missing is not empty) %}
                        {{ Content.P' . $personId . '.Input.Bad.Missing }}
                    {% else %}
                        &nbsp;
                    {% endif %}',
                    self::TEXT_SIZE_SMALL
                ))
            );
    }

    /**
     * @param int $personId
     *
     * @return Slice
     */
    public function getCustomCourse(int $personId) : Slice
    {
        return (new Slice())
            ->addElement($this->getElement('
                    {% if(Content.P' . $personId . '.Student.Course.Degree is not empty) %}
                        nahm am Unterricht mit dem Ziel des {{ Content.P' . $personId . '.Student.Course.Degree }} teil.
                    {% endif %}',
                    self::TEXT_SIZE_SMALL
                )
                ->styleMarginTop('3px')
            );
    }

    /**
     * @param int $personId
     * @param string $marginTop
     *
     * @return Slice
     */
    public function getCustomTransfer(int $personId, string $marginTop = '2px') : Slice
    {
        return (new Slice())
            ->styleMarginTop($marginTop)
            ->addSection((new Section())
                ->addElementColumn($this->getElement('Versetzungsvermerk:', self::TEXT_SIZE_SMALL)
                    , '23%')
                ->addElementColumn($this->getElement(
                    '{% if(Content.P' . $personId . '.Input.Transfer) %}
                        {{ Content.P' . $personId . '.Input.Transfer }}.
                    {% else %}
                          &nbsp;
                    {% endif %}',
                    self::TEXT_SIZE_SMALL
                ))
            );
    }

    /**
     * @param int $personId
     * @param string $marginTop
     *
     * @return Slice
     */
    protected function getCustomDateLine(int $personId, string $marginTop = '27px') : Slice
    {
        return (new Slice())
            ->styleMarginTop($marginTop)
            ->addSection((new Section())
                    ->addElementColumn($this->getElement('Datum:', self::TEXT_SIZE_SMALL), '9%')
                    ->addElementColumn($this->getElement(
                        '{% if(Content.P' . $personId . '.Input.Date is not empty) %}
                            {{ Content.P' . $personId . '.Input.Date }}
                        {% else %}
                            &nbsp;
                        {% endif %}',
                        self::TEXT_SIZE_SMALL
                    ))
                );
    }

    /**
     * @param int $personId
     * @param bool $isExtended
     * @param string $marginTop
     *
     * @return Slice
     */
    public function getCustomSignPart(int $personId, bool $isExtended, string $marginTop = '25px') : Slice
    {
        $textSize = '10px';
        $paddingTop = '-5px';

        $slice = (new Slice());
        if ($isExtended) {
            $slice
                ->styleMarginTop($marginTop)
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleAlignCenter()
                        ->styleBorderBottom('0.5px', '#000')
                        , '30%')
                    ->addElementColumn((new Element())
                        , '5%')
                    ->addElementColumn($this->getElement('Stempel der Schule', $textSize)
                        ->styleAlignCenter()
                        , '30%')
                    ->addElementColumn((new Element())
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleAlignCenter()
                        ->styleBorderBottom('0.5px', '#000')
                        , '30%')
                )
                ->addSection((new Section())
                    ->addElementColumn($this->getElement('
                            {% if(Content.P' . $personId . '.Headmaster.Description is not empty) %}
                                {{ Content.P' . $personId . '.Headmaster.Description }}
                            {% else %}
                                Schulleiter(in)
                            {% endif %}',
                            $textSize
                        )
                        ->stylePaddingTop($paddingTop)
                        ->styleAlignCenter()
                        , '30%')
                    ->addElementColumn((new Element())
                        , '40%')
                    ->addElementColumn($this->getElement('
                            {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                                {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                            {% else %}
                                Klassenlehrer(in)
                            {% endif %}',
                            $textSize
                        )
                        ->stylePaddingTop($paddingTop)
                        ->styleAlignCenter()
                        , '30%')
                )
                ->addSection((new Section())
                    ->addElementColumn($this->getElement(
                            '{% if(Content.P' . $personId . '.Headmaster.Name is not empty) %}
                                {{ Content.P' . $personId . '.Headmaster.Name }}
                            {% else %}
                                &nbsp;
                            {% endif %}',
                            $textSize
                        )
                        ->stylePaddingTop($paddingTop)
                        ->styleAlignCenter()
                        , '30%')
                    ->addElementColumn((new Element())
                        , '40%')
                    ->addElementColumn($this->getElement(
                            '{% if(Content.P' . $personId . '.DivisionTeacher.Name is not empty) %}
                                {{ Content.P' . $personId . '.DivisionTeacher.Name }}
                            {% else %}
                                &nbsp;
                            {% endif %}',
                            $textSize
                        )
                        ->stylePaddingTop($paddingTop)
                        ->styleAlignCenter()
                        , '30%')
                );
        } else {
            $slice
                ->styleMarginTop($marginTop)
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '70%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleAlignCenter()
                        ->styleBorderBottom('0.5px', '#000')
                        , '30%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '70%')
                    ->addElementColumn($this->getElement('
                            {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                                {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                            {% else %}
                                Klassenlehrer(in)
                            {% endif %}',
                            $textSize
                        )
                        ->stylePaddingTop($paddingTop)
                        ->styleAlignCenter()
                        , '30%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '70%')
                    ->addElementColumn($this->getElement(
                            '{% if(Content.P' . $personId . '.DivisionTeacher.Name is not empty) %}
                                {{ Content.P' . $personId . '.DivisionTeacher.Name }}
                            {% else %}
                                &nbsp;
                            {% endif %}',
                            $textSize
                        )
                        ->stylePaddingTop($paddingTop)
                        ->styleAlignCenter()
                        , '30%')
                );
        }

        return $slice;
    }

    public function getCustomSignPartBgj(int $personId, string $marginTop = '15px') : Slice
    {
        $textSize = self::TEXT_SIZE_NORMAL;
        $paddingTop = '-8px';

        return (new Slice())
            ->styleMarginTop($marginTop)
            ->addSection((new Section())
                ->addElementColumn($this->getElement(
                        '{{ Content.P' . $personId . '.Company.Address.City.Name }}, {{ Content.P' . $personId . '.Input.Date }}',
                        $textSize
                    )
                    ->styleBorderBottom('0.5px')
                    , '30%')
                ->addElementColumn((new Element()))
                ->addElementColumn($this->getElement('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderBottom('0.5px')
                    , '30%')
            )
            ->addSection((new Section())
                ->addElementColumn($this->getElement('Ort, Datum', $textSize)
                    ->stylePaddingTop($paddingTop)
                    ->styleAlignLeft()
                    , '30%')
                ->addElementColumn((new Element())
                    , '40%')
                ->addElementColumn($this->getElement('
                        {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                            {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                        {% else %}
                            Klassenlehrer(in)
                        {% endif %}',
                    $textSize
                )
                    ->stylePaddingTop($paddingTop)
                    ->styleAlignCenter()
                    , '30%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    , '30%')
                ->addElementColumn((new Element())
                    , '40%')
                ->addElementColumn($this->getElement(
                    '{% if(Content.P' . $personId . '.DivisionTeacher.Name is not empty) %}
                            {{ Content.P' . $personId . '.DivisionTeacher.Name }}
                        {% else %}
                            &nbsp;
                        {% endif %}',
                    $textSize
                )
                    ->stylePaddingTop($paddingTop)
                    ->styleAlignCenter()
                    , '30%')
            );
    }

    /**
     * @param string $marginTop
     *
     * @return Slice
     */
    protected function getCustomParentSign(string $marginTop = '5px') : Slice
    {
        $textSize = '10px';
        $paddingTop = '-10px';

        return (new Slice())
            ->styleMarginTop($marginTop)
            ->addSection((new Section())
                ->addElementColumn($this->getElement('Zur Kenntnis genommen:', self::TEXT_SIZE_SMALL)
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderBottom('0.5px')
                    , '40%')
                ->addElementColumn((new Element())
                    , '30%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    , '30%')
                ->addElementColumn($this->getElement('Eltern', $textSize)
                    ->styleAlignCenter()
                    ->stylePaddingTop($paddingTop)
                    , '40%')
                ->addElementColumn((new Element())
                    , '30%')
            );
    }

    /**
     * @param string $marginTop
     *
     * @return Slice
     */
    protected function getCustomInfo(string $marginTop = '0px', array $lines = array()) : Slice
    {
        $textSize = '9.5px';

        $slice = (new Slice())
            ->styleMarginTop($marginTop)
            ->addElement($this->getElement('Notenerläuterung:', $textSize))
            ->addElement($this->getElement('1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft; 6 = ungenügend',
                $textSize)->stylePaddingTop('-5px'));

        foreach ($lines as $item) {
            $slice->addElement($this->getElement($item, $textSize)->stylePaddingTop('-5px'));
        }

        return $slice;
    }

    /**
     * @param string $marginTop
     *
     * @return Slice
     */
    protected function getCustomInfoBgj(string $marginTop = '25px') : Slice
    {
        $textSize = '9.5px';

        return (new Slice())
            ->styleMarginTop($marginTop)
            ->addElement($this->getElement(
                'Notenstufen: sehr gut (1), gut (2), befriedigend (3), ausreichend (4), mangelhaft (5), ungenügend (6)',
                $textSize
            )->stylePaddingTop('-5px'));
    }

    /**
     * @param string $content
     * @param string $textSize
     *
     * @return Element
     */
    protected function getElement(string $content, string $textSize = self::TEXT_SIZE_SMALL) : Element
    {
        return (new Element())
            ->setContent($content)
            ->styleTextSize($textSize)
            ->styleFontFamily(self::FONT_FAMILY);
    }

    /**
     * @param string $height
     *
     * @return Section
     */
    protected function getSectionSpace(string $height) : Section
    {
        return (new Section())
            ->addElementColumn(
                (new Element())
                    ->setContent('&nbsp;')
                    ->styleHeight($height)
            );
    }

    /**
     * @param string $height
     *
     * @return Slice
     */
    protected function getSliceSpace(string $height) : Slice
    {
        return (new Slice())
            ->styleHeight($height);
    }

    /**
     * @param Section $section
     * @param string $subjectName
     * @param string $grade
     */
    protected function setGradeLine(Section $section, string $subjectName, string $grade)
    {
        $section->addElementColumn(
            $this->getElement($subjectName, self::TEXT_SIZE_SMALL)
                ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
            , self::SUBJECT_WIDTH . '%');
        $section->addElementColumn(
            $this->getElement($grade, self::TEXT_SIZE_NORMAL)
                ->styleAlignCenter()
                ->styleBackgroundColor(self::BACKGROUND)
                ->stylePaddingTop(self::PADDING_TOP_GRADE)
                ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
            , self::GRADE_WIDTH . '%');
    }

    /**
     * @param Section $section
     * @param string $subjectName
     * @param string $grade
     * @param string $textSize
     */
    protected function setGradeFullLine(Section $section, string $subjectName, string $grade, string $textSize = self::TEXT_SIZE_NORMAL)
    {
        $section->addElementColumn(
            $this->getElement($subjectName, $textSize)
                ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
            , '75%');
        $section->addElementColumn(
            $this->getElement($grade, $textSize)
                ->styleAlignCenter()
                ->styleBackgroundColor(self::BACKGROUND)
                ->stylePaddingTop(self::PADDING_TOP_GRADE)
                ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
            , '25%');
    }

    /**
     * @param int $personId
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getCustomGradeLanes(int $personId, string $MarginTop = '25px') : Slice
    {
        $slice = new Slice();
        $GradeStructure = array();
        $tblCertificateGradeAll = Generator::useService()->getCertificateGradeAll($this->getCertificateEntity());
        if (!empty($tblCertificateGradeAll)) {
            foreach ($tblCertificateGradeAll as $tblCertificateGrade) {
                $tblGradeType = $tblCertificateGrade->getServiceTblGradeType();
                $GradeStructure[$tblCertificateGrade->getRanking()][$tblCertificateGrade->getLane()]['GradeAcronym']
                    = $tblGradeType->getCode();
                $GradeStructure[$tblCertificateGrade->getRanking()][$tblCertificateGrade->getLane()]['GradeName']
                    = $tblGradeType->getName();
            }
        }

        // Shrink Lanes
        $LaneCounter = array(1 => 0, 2 => 0);
        $GradeLayout = array();
        if ($GradeStructure) {
            ksort($GradeStructure);
            foreach ($GradeStructure as $GradeList) {
                ksort($GradeList);
                foreach ($GradeList as $Lane => $Grade) {
                    $GradeLayout[$LaneCounter[$Lane]][$Lane] = $Grade;
                    $LaneCounter[$Lane]++;
                }
            }
            $GradeStructure = $GradeLayout;

            foreach ($GradeStructure as $GradeList) {
                // Sort Lane-Ranking (1,2...)
                ksort($GradeList);
                $section = new Section();
                if (count($GradeList) == 1 && isset($GradeList[2])) {
                    $section->addElementColumn((new Element()));
                }
                foreach ($GradeList as $Lane => $Grade) {
                    if ($Lane > 1) {
                        $section->addElementColumn((new Element()), '4%');
                    }

                    $this->setGradeLine(
                        $section,
                        $Grade['GradeName'],
                        '{% if(Content.P' . $personId . '.Input["' . $Grade['GradeAcronym'] . '"] is not empty) %}
                                 {{ Content.P' . $personId . '.Input["' . $Grade['GradeAcronym'] . '"] }}
                        {% else %}
                             &ndash;
                        {% endif %}'
                    );
                }

                if (count($GradeList) == 1 && isset($GradeList[1])) {
                    $section->addElementColumn((new Element()), '52%');
                }

                $slice->addSection($section)->styleMarginTop($MarginTop);
            }
        }

        return $slice;
    }

    /**
     * @param $personId
     * @param bool $isSlice
     * @param array $languagesWithStartLevel
     * @param false $hasSecondLanguageDiploma
     * @param false $hasSecondLanguageSecondarySchool
     *
     * @return Section[]|Slice
     */
    protected function getCustomSubjectLanes(
        $personId,
        $isSlice = true,
        $languagesWithStartLevel = array(),
        $hasSecondLanguageDiploma = false,
        $hasSecondLanguageSecondarySchool = false
    ) {
        $tblPerson = Person::useService()->getPersonById($personId);

        $SubjectSlice = (new Slice());

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($this->getCertificateEntity());
        $tblGradeList = $this->getGrade();

        $SectionList = array();

        $section = (new Section())
            ->addElementColumn($this->getElement('Leistungen in den einzelnen Fächern:', self::TEXT_SIZE_NORMAL)
                ->styleTextBold()
                ->styleMarginTop('5px')
                ->styleMarginBottom('0px')
            );
        $SubjectSlice->addSection($section);
        $SectionList[] = $section;

        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if ($tblSubject) {
                    // Grade Exists? => Add Subject to Certificate
                    if (isset($tblGradeList['Data'][$tblSubject->getAcronym()])) {
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym']
                            = $tblSubject->getAcronym();
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName']
                            = $tblSubject->getName();
                    } else {
                        // Grade Missing, But Subject Essential => Add Subject to Certificate
                        if ($tblCertificateSubject->isEssential()) {
                            $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym']
                                = $tblSubject->getAcronym();
                            $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName']
                                = $tblSubject->getName();
                        }
                    }
                }
            }

            $tblSecondForeignLanguageDiploma = false;
            $tblSecondForeignLanguageSecondarySchool = false;

            // add SecondLanguageField, Fach wird aus der Schüleraktte des Schülers ermittelt
            $tblSecondForeignLanguage = false;
            if (!empty($languagesWithStartLevel)) {
                if (isset($languagesWithStartLevel['Lane']) && isset($languagesWithStartLevel['Rank'])) {
                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                    [$languagesWithStartLevel['Lane']]['SubjectAcronym'] = 'Empty';
                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                    [$languagesWithStartLevel['Lane']]['SubjectName'] = '&nbsp;';
                    if ($tblPerson
                        && ($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                    ) {
                        if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
                            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                                $tblStudentSubjectType))
                        ) {
                            /** @var TblStudentSubject $tblStudentSubject */
                            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                                if ($tblStudentSubject->getTblStudentSubjectRanking()
                                    && $tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier() == '2'
                                    && ($tblSubjectForeignLanguage = $tblStudentSubject->getServiceTblSubject())
                                ) {
                                    $tblSecondForeignLanguage = $tblSubjectForeignLanguage;
                                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                                    [$languagesWithStartLevel['Lane']]['SubjectAcronym'] = $tblSubjectForeignLanguage->getAcronym();
                                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                                    [$languagesWithStartLevel['Lane']]['SubjectName'] = $tblSubjectForeignLanguage->getName();
                                }
                            }
                        }
                    }
                }
            } else {
                if (($hasSecondLanguageDiploma || $hasSecondLanguageSecondarySchool)
                    && $tblPerson
                    && ($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                ) {
                    if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
                        && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                            $tblStudentSubjectType))
                    ) {
                        /** @var TblStudentSubject $tblStudentSubject */
                        foreach ($tblStudentSubjectList as $tblStudentSubject) {
                            if ($tblStudentSubject->getTblStudentSubjectRanking()
                                && $tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier() == '2'
                                && ($tblSubjectForeignLanguage = $tblStudentSubject->getServiceTblSubject())
                            ) {
                                if ($hasSecondLanguageDiploma) {
                                    $tblSecondForeignLanguageDiploma = $tblSubjectForeignLanguage;
                                }

                                // Mittelschulzeugnisse
                                if ($hasSecondLanguageSecondarySchool)  {
                                    // SSW-484
                                    $tillLevel = $tblStudentSubject->getLevelTill();
                                    $fromLevel = $tblStudentSubject->getLevelFrom();
                                    $level = $this->getLevel();

                                    if ($tillLevel && $fromLevel) {
                                        if ($fromLevel <= $level && $tillLevel >= $level) {
                                            $tblSecondForeignLanguageSecondarySchool = $tblSubjectForeignLanguage;
                                        }
                                    } elseif ($tillLevel) {
                                        if ($tillLevel >= $level) {
                                            $tblSecondForeignLanguageSecondarySchool = $tblSubjectForeignLanguage;
                                        }
                                    } elseif ($fromLevel) {
                                        if ($fromLevel <= $level) {
                                            $tblSecondForeignLanguageSecondarySchool = $tblSubjectForeignLanguage;
                                        }
                                    } else {
                                        $tblSecondForeignLanguageSecondarySchool = $tblSubjectForeignLanguage;
                                    }
                                }
                            }
                        }
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

            $hasAdditionalLine = false;
            $isShrinkMarginTop = false;

            // Mittelschulzeugnisse 2. Fremdsprache anfügen
            if ($hasSecondLanguageSecondarySchool) {
                // Zeiger auf letztes Element
                end($SubjectStructure);
                $lastItem = &$SubjectStructure[key($SubjectStructure)];

                $column = array(
                    'SubjectAcronym' => $tblSecondForeignLanguageSecondarySchool
                        ? $tblSecondForeignLanguageSecondarySchool->getAcronym() : 'SECONDLANGUAGE',
                    'SubjectName' => $tblSecondForeignLanguageSecondarySchool
                        ? $tblSecondForeignLanguageSecondarySchool->getName()
                        : '&ndash;'
                );
                //
                if (isset($lastItem[1])) {
                    $SubjectStructure[][1] = $column;
                } else {
                    $lastItem[1] = $column;
                }
            }

            $count = 0;
            foreach ($SubjectStructure as $SubjectList) {
                $count++;
                // Sort Lane-Ranking (1,2...)
                ksort($SubjectList);

                $SubjectSection = (new Section());

                if (count($SubjectList) == 1 && isset($SubjectList[2])) {
                    $SubjectSection->addElementColumn((new Element()), 'auto');
                }

                foreach ($SubjectList as $Lane => $Subject) {
                    // 2. Fremdsprache ab Klassenstufe
                    if (isset($languagesWithStartLevel['Lane']) && isset($languagesWithStartLevel['Rank'])
                        && $languagesWithStartLevel['Lane'] == $Lane && $languagesWithStartLevel['Rank'] == $count
                    ) {
                        $hasAdditionalLine['Lane'] = $Lane;
                        $hasAdditionalLine['Ranking'] = 2;
                        $hasAdditionalLine['SubjectAcronym'] = $tblSecondForeignLanguage
                            ? $tblSecondForeignLanguage->getAcronym() : 'Empty';
                    } elseif ($hasSecondLanguageSecondarySchool
                        && ($Subject['SubjectAcronym'] == 'SECONDLANGUAGE'
                            || ($tblSecondForeignLanguageSecondarySchool && $Subject['SubjectAcronym'] == $tblSecondForeignLanguageSecondarySchool->getAcronym())
                        )
                    ) {
                        $hasAdditionalLine['Lane'] = $Lane;
                        $hasAdditionalLine['Ranking'] = 2;
                        $hasAdditionalLine['SubjectAcronym'] = $tblSecondForeignLanguageSecondarySchool
                            ? $tblSecondForeignLanguageSecondarySchool->getAcronym() : 'Empty';
                    }

                    // lange Fächernamen
                    $Subject['SubjectName'] = str_replace('/', ' / ',  $Subject['SubjectName']);
                    if (strlen($Subject['SubjectName']) > 20) {
                        $marginTop = '0px';
                        $lineHeight = '80%';
                    } else {
                        $marginTop = self::MARGIN_TOP_GRADE_LINE;
                        $lineHeight = '100%';
                    }

                    if ($Lane > 1) {
                        $SubjectSection->addElementColumn((new Element())
                            , '4%');
                    }
                    if ($hasAdditionalLine && $Lane == $hasAdditionalLine['Lane']) {
                        $SubjectSection->addElementColumn($this->getElement($Subject['SubjectName'], self::TEXT_SIZE_SMALL)
                            ->styleMarginBottom('0px')
//                            ->styleBorderBottom('0.5px', '#000')
                            ->styleMarginTop($marginTop)
                            ->styleLineHeight($lineHeight)
                            , (self::SUBJECT_WIDTH - 2) . '%');
                        $SubjectSection->addElementColumn((new Element()), '2%');
                    } else {
                        $SubjectSection->addElementColumn($this->getElement($Subject['SubjectName'], self::TEXT_SIZE_SMALL)
                            ->styleMarginTop($marginTop)
                            ->styleLineHeight($lineHeight)
                            , self::SUBJECT_WIDTH . '%');
                    }

                    $SubjectSection->addElementColumn($this->getElement(
                            '{% if(Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                {{ Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] }}
                            {% else %}
                                &ndash;
                            {% endif %}',
                            self::TEXT_SIZE_NORMAL
                        )
                        ->styleAlignCenter()
                        ->styleBackgroundColor(self::BACKGROUND)
                        ->stylePaddingTop(self::PADDING_TOP_GRADE)
                        ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
                        , self::GRADE_WIDTH . '%');

                    if ($isShrinkMarginTop && $Lane == 2) {
                        $isShrinkMarginTop = false;
                    }
                }

                if (count($SubjectList) == 1 && isset($SubjectList[1])) {
                    $SubjectSection->addElementColumn((new Element()), '52%');
                    $isShrinkMarginTop = false;
                }

                $SubjectSlice->addSection($SubjectSection);
                $SectionList[] = $SubjectSection;

                if ($hasAdditionalLine) {
                    $SubjectSection = (new Section());
                    if ($hasAdditionalLine['Lane'] == 2) {
                        $SubjectSection->addElementColumn((new Element()), '52%');
                    }

                    $content = $hasSecondLanguageSecondarySchool
                        ? $hasAdditionalLine['Ranking'] . '. Fremdsprache (abschlussorientiert)'
                        : $hasAdditionalLine['Ranking'] . '. Fremdsprache (ab Klassenstufe ' .
                            '{% if(Content.P' . $personId . '.Subject.Level["' . $hasAdditionalLine['SubjectAcronym'] . '"] is not empty) %}
                                {{ Content.P' . $personId . '.Subject.Level["' . $hasAdditionalLine['SubjectAcronym'] . '"] }})
                            {% else %}
                               &ndash;)
                            {% endif %}';

                    $SubjectSection->addElementColumn((new Element())
                        ->setContent($content)
                        ->stylePaddingTop('0px')
                        ->stylePaddingBottom('0px')
                        ->styleMarginTop('0px')
                        ->styleMarginBottom('0px')
                        ->styleTextSize('9px')
                        , self::SUBJECT_WIDTH . '%');

                    if ($hasAdditionalLine['Lane'] == 1) {
                        $SubjectSection->addElementColumn((new Element()), '52%');
                    }

                    $hasAdditionalLine = false;

                    // es wird abstand gelassen, einkommentieren für keinen extra Abstand der nächsten Zeile
//                    $isShrinkMarginTop = true;

                    $SubjectSlice->addSection($SubjectSection);
                    $SectionList[] = $SubjectSection;
                }
            }
        }

        if ($isSlice) {
            return $SubjectSlice;
        } else {
            return $SectionList;
        }
    }

    /**
     * @param int $personId
     * @param string $marginTop
     *
     * @return Slice
     */
    protected function getCustomSubjectLanesBgj(int $personId, string $marginTop = '12px') : Slice
    {
        $slice = (new Slice())
            ->styleMarginTop($marginTop)
            ->addSection((new Section())
                ->addElementColumn($this->getElement('hat im zurückliegenden Schulhalbjahr folgende Leistungen erreicht:', self::TEXT_SIZE_NORMAL)));

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($this->getCertificateEntity());
        $tblGradeList = $this->getGrade();
        if ($tblCertificateSubjectAll) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if ($tblSubject) {
                    // Grade Exists? => Add Subject to Certificate
                    if (isset($tblGradeList['Data'][$tblSubject->getAcronym()])) {
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym']
                            = $tblSubject->getAcronym();
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName']
                            = $tblSubject->getName();
                    } else {
                        // Grade Missing, But Subject Essential => Add Subject to Certificate
                        if ($tblCertificateSubject->isEssential()) {
                            $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym']
                                = $tblSubject->getAcronym();
                            $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName']
                                = $tblSubject->getName();
                        }
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

            $count = 0;
            foreach ($SubjectStructure as $SubjectList) {
                $count++;
                // Sort Lane-Ranking (1,2...)
                ksort($SubjectList);

                foreach ($SubjectList as $Lane => $Subject) {
                    $section = new Section();
                    $this->setGradeFullLine(
                        $section,
                        $Subject['SubjectName'],
                        '{% if(Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                            {{ Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] }}
                        {% else %}
                            &ndash;
                        {% endif %}'
                    );
                    $slice->addSection($section);
                }
            }
        }

        return $slice;
    }

    /**
     * @param int $personId
     * @param string $period
     * @param string $marginTop
     * @param string $height
     *
     * @return Slice
     */
    protected function getCustomSubjectLanesBGym(int $personId, string $period = 'Schulhalbjahr', string $marginTop = '5px',
        string $height = '290px') : Slice
    {
        $slice = (new Slice())
            ->styleMarginTop($marginTop)
            ->addSection((new Section())
                ->addElementColumn($this->getElement('hat im zurückliegenden ' . $period
                    . ' folgende Leistungen erreicht:', self::TEXT_SIZE_NORMAL)))
            ->addSection((new Section())
                ->addElementColumn($this->getElement('Pflichtbereich', self::TEXT_SIZE_NORMAL)->styleAlignCenter()->styleTextBold()));

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($this->getCertificateEntity());
        $tblGradeList = $this->getGrade();
        if ($tblCertificateSubjectAll) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if ($tblSubject && $tblCertificateSubject->getRanking() < 20) {
                    // Grade Exists? => Add Subject to Certificate
                    if (isset($tblGradeList['Data'][$tblSubject->getAcronym()])) {
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym']
                            = $tblSubject->getAcronym();
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName']
                            = $tblSubject->getName();

                    } else {
                        // Grade Missing, But Subject Essential => Add Subject to Certificate
                        if ($tblCertificateSubject->isEssential()) {
                            $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym']
                                = $tblSubject->getAcronym();
                            $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName']
                                = $tblSubject->getName();
                        }
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

            $count = 0;
            foreach ($SubjectStructure as $SubjectList) {
                $count++;
                // Sort Lane-Ranking (1,2...)
                ksort($SubjectList);

                $SubjectSection = (new Section());

                if (count($SubjectList) == 1 && isset($SubjectList[2])) {
                    $SubjectSection->addElementColumn((new Element()), 'auto');
                }

                foreach ($SubjectList as $Lane => $Subject) {
                    // lange Fächernamen
                    $Subject['SubjectName'] = str_replace('/', ' / ',  $Subject['SubjectName']);
                    if (strlen($Subject['SubjectName']) > 30) {
                        $marginTop = '0px';
                        $lineHeight = '80%';
                    } else {
                        $marginTop = self::MARGIN_TOP_GRADE_LINE;
                        $lineHeight = '100%';
                    }

                    if ($Lane > 1) {
                        $SubjectSection->addElementColumn((new Element())
                            , '4%');
                    }

                    $SubjectSection->addElementColumn($this->getElement($Subject['SubjectName'], self::TEXT_SIZE_SMALL)
                        ->styleMarginTop($marginTop)
                        ->styleLineHeight($lineHeight)
                        , self::SUBJECT_WIDTH . '%');

                    $SubjectSection->addElementColumn($this->getElement(
                        '{% if(Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                {{ Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] }}
                            {% else %}
                                &ndash;
                            {% endif %}',
                        self::TEXT_SIZE_NORMAL
                    )
                        ->styleAlignCenter()
                        ->styleBackgroundColor(self::BACKGROUND)
                        ->stylePaddingTop(self::PADDING_TOP_GRADE)
                        ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
                        , self::GRADE_WIDTH . '%');
                }

                if (count($SubjectList) == 1 && isset($SubjectList[1])) {
                    $SubjectSection->addElementColumn((new Element()), '52%');
                }

                $slice->addSection($SubjectSection);
                $SectionList[] = $SubjectSection;
            }
        }

        return $slice->styleHeight($height);
    }

    /**
     * @param int $personId
     * @param string $marginTop
     *
     * @return Slice
     */
    protected function getCustomChosenLanesBGym(int $personId, string $marginTop = '5px') : Slice
    {
        $slice = (new Slice())
            ->styleMarginTop($marginTop)
            ->addSection((new Section())
                ->addElementColumn($this->getElement('Wahlbereich', self::TEXT_SIZE_NORMAL)->styleAlignCenter()->styleTextBold()));

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($this->getCertificateEntity());
        $tblGradeList = $this->getGrade();
        if ($tblCertificateSubjectAll) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if ($tblSubject && $tblCertificateSubject->getRanking() >= 20) {
                    // Grade Exists? => Add Subject to Certificate
                    if (isset($tblGradeList['Data'][$tblSubject->getAcronym()])) {
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym']
                            = $tblSubject->getAcronym();
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName']
                            = $tblSubject->getName();

                    } else {
                        // Grade Missing, But Subject Essential => Add Subject to Certificate
                        if ($tblCertificateSubject->isEssential()) {
                            $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym']
                                = $tblSubject->getAcronym();
                            $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName']
                                = $tblSubject->getName();
                        }
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

            $count = 0;
            foreach ($SubjectStructure as $SubjectList) {
                $count++;
                // Sort Lane-Ranking (1,2...)
                ksort($SubjectList);

                $SubjectSection = (new Section());

                if (count($SubjectList) == 1 && isset($SubjectList[2])) {
                    $SubjectSection->addElementColumn((new Element()), 'auto');
                }

                foreach ($SubjectList as $Lane => $Subject) {
                    // lange Fächernamen
                    $Subject['SubjectName'] = str_replace('/', ' / ',  $Subject['SubjectName']);
                    if (strlen($Subject['SubjectName']) > 30) {
                        $marginTop = '0px';
                        $lineHeight = '80%';
                    } else {
                        $marginTop = self::MARGIN_TOP_GRADE_LINE;
                        $lineHeight = '100%';
                    }

                    if ($Lane > 1) {
                        $SubjectSection->addElementColumn((new Element())
                            , '4%');
                    }

                    $SubjectSection->addElementColumn($this->getElement($Subject['SubjectName'], self::TEXT_SIZE_SMALL)
                        ->styleMarginTop($marginTop)
                        ->styleLineHeight($lineHeight)
                        , self::SUBJECT_WIDTH . '%');

                    $SubjectSection->addElementColumn($this->getElement(
                        '{% if(Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                {{ Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] }}
                            {% else %}
                                &ndash;
                            {% endif %}',
                        self::TEXT_SIZE_NORMAL
                    )
                        ->styleAlignCenter()
                        ->styleBackgroundColor(self::BACKGROUND)
                        ->stylePaddingTop(self::PADDING_TOP_GRADE)
                        ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
                        , self::GRADE_WIDTH . '%');
                }

                if (count($SubjectList) == 1 && isset($SubjectList[1])) {
                    $SubjectSection->addElementColumn((new Element()), '52%');
                }

                $slice->addSection($SubjectSection);
                $SectionList[] = $SubjectSection;
            }
        }

        return $slice->styleHeight('60px');
    }

    /**
     * @param $personId
     * @param string $marginTop
     * @param bool $hasFootNote
     *
     * @return Slice
     */
    public function getCustomProfile($personId, string $marginTop = '5px', bool $hasFootNote = false) : Slice
    {
        $tblPerson = Person::useService()->getPersonById($personId);

        $slice = new Slice();
        $sectionList = array();

        $tblSubjectProfile = false;
        $tblSubjectForeign = false;

        // Profil
        if ($tblPerson
            && ($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
            && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE'))
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                $tblStudentSubjectType))
        ) {
            /** @var TblStudentSubject $tblStudentSubject */
            $tblStudentSubject = current($tblStudentSubjectList);
            $tblSubjectProfile = $tblStudentSubject->getServiceTblSubject();
        }

        // 3. Fremdsprache
        if ($tblPerson
            && ($tblStudent = $tblPerson->getStudent())
            && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                $tblStudentSubjectType))
        ) {
            /** @var TblStudentSubject $tblStudentSubject */
            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                if ($tblStudentSubject->getTblStudentSubjectRanking()
                    && $tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier() == '3'
                ) {
                    $tblSubjectForeign = $tblStudentSubject->getServiceTblSubject();
                }
            }
        }

        $section = new Section();
        $section
            ->addElementColumn($this->getElement('Wahlpflichtbereich' . ($hasFootNote ? '¹' : '') . ':', self::TEXT_SIZE_NORMAL)
                ->styleTextBold()
                ->styleMarginTop($marginTop)
                ->styleMarginBottom('5px')
            );

        $profileSubjectName = $tblSubjectProfile ? $tblSubjectProfile->getName() : '&ndash;';
        $subjectAcronymForGrade = $tblSubjectProfile ? $tblSubjectProfile->getAcronym() : 'SubjectAcronymForGrade';
        if (strlen($profileSubjectName) > 30) {
            $marginTopProfile = '0px';
            $lineHeightProfile = '80%';
        } else {
            $marginTopProfile = self::MARGIN_TOP_GRADE_LINE;
            $lineHeightProfile = '100%';
        }

        $foreignSubjectName = $tblSubjectForeign ? $tblSubjectForeign->getName() : '&ndash;';
        if (strlen($foreignSubjectName) > 30) {
            $marginTopForeign = '0px';
            $lineHeightForeign = '80%';
        } else {
            $marginTopForeign = self::MARGIN_TOP_GRADE_LINE;
            $lineHeightForeign = '100%';
        }


        $sectionList[] = $section;
        $section = new Section();
        $section
            ->addElementColumn($this->getElement(
                    $profileSubjectName,
                    self::TEXT_SIZE_SMALL
                )
                ->styleMarginTop($marginTopProfile)
                ->styleLineHeight($lineHeightProfile)
                , self::SUBJECT_WIDTH . '%')
            ->addElementColumn($this->getElement(
                    '{% if(Content.P' . $personId . '.Grade.Data["' . $subjectAcronymForGrade . '"] is not empty) %}
                        {{ Content.P' . $personId . '.Grade.Data["' . $subjectAcronymForGrade . '"] }}
                    {% else %}
                        &ndash;
                    {% endif %}',
                    self::TEXT_SIZE_NORMAL
                )
                ->styleAlignCenter()
                ->styleBackgroundColor(self::BACKGROUND)
                ->stylePaddingTop(self::PADDING_TOP_GRADE)
                ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
                , self::GRADE_WIDTH . '%')
            ->addElementColumn((new Element()), '4%')
            ->addElementColumn($this->getElement(
                    $foreignSubjectName,
                    self::TEXT_SIZE_SMALL
                )
                ->styleMarginTop($marginTopForeign)
                ->styleLineHeight($lineHeightForeign)
                , self::SUBJECT_WIDTH . '%')
            ->addElementColumn($this->getElement(
                    $tblSubjectForeign
                        ? '{% if(Content.P' . $personId . '.Grade.Data["' . $tblSubjectForeign->getAcronym() . '"] is not empty) %}
                            {{ Content.P' . $personId . '.Grade.Data["' . $tblSubjectForeign->getAcronym() . '"] }}
                        {% else %}
                            &ndash;
                        {% endif %}'
                        : '&ndash;',
                    self::TEXT_SIZE_NORMAL
                )
                ->styleAlignCenter()
                ->styleBackgroundColor(self::BACKGROUND)
                ->stylePaddingTop(self::PADDING_TOP_GRADE)
                ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
                , self::GRADE_WIDTH . '%');
        $sectionList[] = $section;

        $section = new Section();
        $section
            ->addElementColumn($this->getElement('besuchtes schulspezifisches Profil' . ($hasFootNote ? '²' : ''), '9px'), '52%')
            ->addElementColumn($this->getElement('3. Fremdsprache (ab Klassenstufe 8)' . ($hasFootNote ? '²' : ''), '9px'));
        $sectionList[] = $section;

        return $slice->addSectionList($sectionList);
    }

    /**
     * @param string $title
     * @param string $subTitle
     * @param string $marginTop
     *
     * @return Slice
     */
    public function getCustomBgjTitle(string $title, string $subTitle, string $marginTop = '35px')
    {
        return (new Slice())
            ->styleMarginTop($marginTop)
            ->addElement($this->getElement($title, '35px')->styleTextBold()->styleAlignCenter())
            ->addElement($this->getElement($subTitle, '17px')->styleTextBold()->styleAlignCenter()->styleMarginTop('-5px'));
    }

    /**
     * @param string $title
     * @param string $subTitle
     * @param string $description
     *
     * @return Page
     */
    public function getCoverPage(string $title, string $subTitle, string $description, bool $isStateLogoVisible = true) : Page
    {
        $logoHeight = '70px';

        $section = new Section();
        $section->addElementColumn((new Element()), '39%');

        // Sample
        if($this->isSample()){
            $section->addElementColumn((new Element\Sample())->styleTextSize('30px'));
        } else {
            $section->addElementColumn((new Element()), '22%');
        }

        // Standard Logo
        if ($isStateLogoVisible) {
            $section->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                'auto', $logoHeight))
                ->styleAlignRight()
                , '39%');
        } else {
            $section->addElementColumn((new Element())->styleHeight($logoHeight), '39%');
        }

        return (new Page())
            ->addSlice((new Slice())
                ->styleMarginTop('50px')
                ->addSection($section)
                ->addElement($this->getElement($title, '33px')
                    ->styleAlignCenter()
                    ->styleMarginTop('15%')
                    ->styleTextBold()
                )
            )
            ->addSlice((new Slice())
                ->addElement($this->getElement($subTitle, '22px')
                    ->styleAlignCenter()
                    ->styleMarginTop('15px')
                    ->styleTextBold()
                )
            )->addSlice((new Slice())
                ->addElement($this->getElement($description, '22px')
                    ->styleAlignCenter()
                    ->styleMarginTop('20px')
                    ->styleTextBold()
                )
            );
    }

    /**
     * @return Slice
     */
    public function getLogoSecondPage() : Slice
    {
        return (new Slice())
            ->styleMarginTop('40px')
            ->addElement((new Element\Image('/Common/Style/Resource/Logo/HOGA.jpg', 'auto', '100px'))
                ->styleAlignCenter()
            );
    }

    public function getStudentHeader(int $personId, bool $hasDivision = false, string $marginTop = '65px') : Slice
    {
        $paddingTop = '4px';
        $section = new Section();
        $section
            ->addElementColumn($this->getElement('Vorname und Name:')->stylePaddingTop($paddingTop), '20%')
            ->addElementColumn($this->getElement('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                    {{ Content.P' . $personId . '.Person.Data.Name.Last }}', self::TEXT_SIZE_LARGE)
                ->styleTextBold());

        if ($hasDivision) {
            $section
                ->addElementColumn($this->getElement('Klasse:')->stylePaddingTop($paddingTop), '8%')
                ->addElementColumn($this->getElement('{{ Content.P' . $personId . '.Division.Data.Name }}', self::TEXT_SIZE_LARGE)->styleTextBold()
                , '20%');
        }

        return (new Slice())
            ->styleMarginTop($marginTop)
            ->addSection($section);
    }

    /**
     * @param int $personId
     * @param string $marginSpace
     *
     * @return Page
     */
    public function getSecondPageTop(int $personId, string $marginSpace = '45px') : Page
    {
        $paddingTop = '4px';
        return (new Page)
            ->addSlice($this->getStudentHeader($personId, false))
            ->addSlice((new Slice())
                ->styleMarginTop($marginSpace)
                ->addSection((new Section())
                    ->addElementColumn($this->getElement('geboren am:')
                        ->stylePaddingTop($paddingTop)
                        , '20%')
                    ->addElementColumn($this->getElement(
                            '{% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthday is not empty) %}
                                {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                            {% else %}
                                &nbsp;
                            {% endif %}'
                            , self::TEXT_SIZE_LARGE)
                        ->styleTextBold()
                        , '10%')
                    ->addElementColumn($this->getElement('in:')
                        ->stylePaddingTop($paddingTop)
                        ->stylePaddingLeft('20px')
                        , '4%')
                    ->addElementColumn($this->getElement(
                            '{% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthplace is not empty) %}
                                {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthplace }}
                            {% else %}
                                &nbsp;
                            {% endif %}'
                        , self::TEXT_SIZE_LARGE)
                        ->styleTextBold()
                        ->stylePaddingLeft('8px')
                    )
                )
            )
            ->addSlice((new Slice())
                ->styleMarginTop($marginSpace)
                ->addSection((new Section())
                    ->addElementColumn($this->getElement('wohnhaft in:')
                        ->stylePaddingTop($paddingTop)
                        , '20%')
                    ->addElementColumn($this->getElement(
                        '{% if(Content.P' . $personId . '.Person.Address.City.Name) %}
                            {{ Content.P' . $personId . '.Person.Address.Street.Name }}
                            {{ Content.P' . $personId . '.Person.Address.Street.Number }},
                            {{ Content.P' . $personId . '.Person.Address.City.Code }}
                            {{ Content.P' . $personId . '.Person.Address.City.Name }}
                        {% else %}
                              &nbsp;
                        {% endif %}'
                        , self::TEXT_SIZE_LARGE)
                        ->styleTextBold()
                    )
                )
            );
    }

    /**
     * @param string $content
     *
     * @return Slice
     */
    protected function setCustomCheckBox(string $content = '&nbsp;') : Slice
    {
        return (new Slice())
            ->addSection((new Section())
                ->addElementColumn($this->getElement($content, '22px')
                    ->styleHeight('30px')
                    ->stylePaddingLeft('8px')
                    ->stylePaddingTop('-8px')
                    ->stylePaddingBottom('8px')
                    ->styleBorderAll('0.5px')
                )
            );
    }

    /**
     * @param $personId
     *
     * @return Slice
     */
    protected function getCustomAdditionalSubjectLanes($personId) : Slice
    {
        $slice = new Slice();
        $slice
            ->addElement($this->getElement(
                    'Leistungen in Fächern, die in Klassenstufe 9 abgeschlossen wurden:',
                    self::TEXT_SIZE_NORMAL
                )->styleTextBold()->styleMarginBottom('4px')
            );
        if (($tblGradeList = $this->getAdditionalGrade())) {
            $count = 0;
            $section = new Section();
            foreach ($tblGradeList['Data'] as $subjectAcronym => $grade) {
                if (($tblSubject = Subject::useService()->getSubjectByAcronym($subjectAcronym))) {
                    // lange Fächernamen
                    $subjectName = str_replace('/', ' / ',  $tblSubject->getName());
                    if (strlen($subjectName) > 20) {
                        $marginTop = '0px';
                        $lineHeight = '80%';
                    } else {
                        $marginTop = self::MARGIN_TOP_GRADE_LINE;
                        $lineHeight = '100%';
                    }


                    $count++;
                    if ($count % 2 == 1) {
                        $section = new Section();
                        $slice->addSection($section);
                    } else {
                        $section->addElementColumn((new Element())
                            , '4%');
                    }

//                    $this->setGradeLine(
//                        $section,
//                        $subjectName,
//                        '{% if(Content.P' . $personId . '.AdditionalGrade.Data["' . $tblSubject->getAcronym() . '"] is not empty) %}
//                             {{ Content.P' . $personId . '.AdditionalGrade.Data["' . $tblSubject->getAcronym() . '"] }}
//                         {% else %}
//                             &ndash;
//                         {% endif %}'
//                    );

                    $section->addElementColumn(
                        $this->getElement($subjectName)
                            ->styleMarginTop($marginTop)
                            ->styleLineHeight($lineHeight)
                        , self::SUBJECT_WIDTH . '%');
                    $section->addElementColumn(
                        $this->getElement('{% if(Content.P' . $personId . '.AdditionalGrade.Data["' . $tblSubject->getAcronym() . '"] is not empty) %}
                                 {{ Content.P' . $personId . '.AdditionalGrade.Data["' . $tblSubject->getAcronym() . '"] }}
                             {% else %}
                                 &ndash;
                             {% endif %}', self::TEXT_SIZE_NORMAL)
                            ->styleAlignCenter()
                            ->styleBackgroundColor(self::BACKGROUND)
                            ->stylePaddingTop(self::PADDING_TOP_GRADE)
                            ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
                        , self::GRADE_WIDTH . '%');
                }
            }

            if ($count % 2 == 1) {
                $section->addElementColumn(new Element(), '52%');
            }
        }

        return $slice->styleHeight('100px');
    }

    /**
     * @param string $marginTop
     *
     * @return Slice
     */
    public function getCustomExaminationsBoard(string $marginTop = '45px') : Slice
    {
        $leaderName = '&nbsp;';
        $leaderDescription = 'Vorsitzende(r)';
        $firstMemberName = '&nbsp;';
        $secondMemberName = '&nbsp;';

        $textSize = '10px';
        $paddingTop = '-5px';

        if ($this->getTblPrepareCertificate()
            && ($tblGenerateCertificate = $this->getTblPrepareCertificate()->getServiceTblGenerateCertificate())
        ) {

            if (($tblGenerateCertificateSettingLeader = Generate::useService()->getGenerateCertificateSettingBy($tblGenerateCertificate, 'Leader'))
                && ($tblPersonLeader = Person::useService()->getPersonById($tblGenerateCertificateSettingLeader->getValue()))
            ) {
                $leaderName = $tblPersonLeader->getFullName();
                if (($tblCommon = $tblPersonLeader->getCommon())
                    && ($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())
                    && ($tblGender = $tblCommonBirthDates->getTblCommonGender())
                ) {
                    if ($tblGender->getName() == 'Männlich') {
                        $leaderDescription = 'Vorsitzender';
                    } elseif ($tblGender->getName() == 'Weiblich') {
                        $leaderDescription = 'Vorsitzende';
                    }
                }
            }

            if (($tblGenerateCertificateSettingFirstMember = Generate::useService()->getGenerateCertificateSettingBy($tblGenerateCertificate, 'FirstMember'))
                && ($tblPersonFirstMember = Person::useService()->getPersonById($tblGenerateCertificateSettingFirstMember->getValue()))
            ) {
                $firstMemberName = $tblPersonFirstMember->getFullName();
            }

            if (($tblGenerateCertificateSettingSecondMember = Generate::useService()->getGenerateCertificateSettingBy($tblGenerateCertificate, 'SecondMember'))
                && ($tblPersonSecondMember = Person::useService()->getPersonById($tblGenerateCertificateSettingSecondMember->getValue()))
            ) {
                $secondMemberName = $tblPersonSecondMember->getFullName();
            }
        }

        return (new Slice())
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Der Prüfungsausschuss')
                    ->styleAlignCenter()
                    ->styleMarginTop($marginTop)
                )
            )
            ->addSection((new Section())
                ->addElementColumn($this->getElement('&nbsp;')
                    ->styleBorderBottom()
//                    ->styleMarginTop('5px')
                    , '30%')
                ->addElementColumn($this->getElement(''))
                ->addElementColumn($this->getElement('&nbsp;')
                    ->styleBorderBottom()
//                    ->styleMarginTop('5px')
                    , '30%')
            )
            ->addSection((new Section())
                ->addElementColumn($this->getElement($leaderDescription, $textSize)
                    ->stylePaddingTop($paddingTop)
                    ->styleAlignCenter()
                    ->styleMarginTop('0px')
                    , '30%')
                ->addElementColumn($this->getElement('Stempel der Schule', $textSize)
                    ->stylePaddingTop($paddingTop)
                    ->styleAlignCenter()
                    ->styleMarginTop('0px')
                )
                ->addElementColumn($this->getElement('Mitglied', $textSize)
                    ->stylePaddingTop($paddingTop)
                    ->styleAlignCenter()
                    ->styleMarginTop('0px')
                    , '30%')
            )
            ->addSection((new Section())
                ->addElementColumn($this->getElement($leaderName, $textSize)
                    ->stylePaddingTop($paddingTop)
                    ->styleAlignCenter()
                    ->styleMarginTop('0px')
                    , '30%')
                ->addElementColumn($this->getElement(''))
                ->addElementColumn($this->getElement($firstMemberName, $textSize)
                    ->stylePaddingTop($paddingTop)
                    ->styleAlignCenter()
                    ->styleMarginTop('0px')
                    , '30%')
            )
            ->addSection((new Section())
                ->addElementColumn($this->getElement('')
                    , '30%')
                ->addElementColumn($this->getElement(''))
                ->addElementColumn($this->getElement('&nbsp;')
                    ->styleBorderBottom()
                    ->styleMarginTop('15px')
                    , '30%')
            )
            ->addSection((new Section())
                ->addElementColumn($this->getElement('')
                    , '30%')
                ->addElementColumn($this->getElement(''))
                ->addElementColumn($this->getElement('Mitglied', $textSize)
                    ->stylePaddingTop($paddingTop)
                    ->styleAlignCenter()
                    ->styleMarginTop('0px')
                    , '30%')
            )
            ->addSection((new Section())
                ->addElementColumn($this->getElement('')
                    , '30%')
                ->addElementColumn($this->getElement(''))
                ->addElementColumn($this->getElement($secondMemberName, $textSize)
                    ->stylePaddingTop($paddingTop)
                    ->styleAlignCenter()
                    ->styleMarginTop('0px')
                    , '30%')
            )
        ;
    }

    /**
     * @param int $personId
     * @param string $title
     * @param string $marginTop
     * @param bool $hasSubjectArea
     *
     * @return Slice
     */
    public function getCustomFosTitle(
        int $personId,
        string $title = 'Jahreszeugnis',
        string $marginTop = '10px',
        bool $hasSubjectArea = true
    ) : Slice
    {
        $slice = (new Slice())
            ->styleMarginTop($marginTop)
            ->addElement($this->getElement($title, '35px')->styleTextBold()->styleAlignCenter())
            ->addElement($this->getElement('der Fachoberschule', '17px')->styleTextBold()->styleAlignCenter()->styleMarginTop('-5px'));

        if($hasSubjectArea) {
            $slice
                ->addElement($this->getElement(
                    '{% if(Content.P' . $personId . '.Input.SubjectArea is not empty) %}
                        Fachrichtung {{ Content.P' . $personId . '.Input.SubjectArea }}
                    {% else %}
                        Fachrichtung &ndash;
                    {% endif %}'
                    , '17px'
                )
                    ->styleTextBold()
                    ->styleAlignCenter()
                    ->styleMarginTop('-10px')
                );
        }

        return $slice;
    }

    /**
     * @param int $personId
     * @param string $period
     * @param string $marginTop
     * @param bool $hasBirthInformation
     *
     * @return Slice
     */
    protected function getCustomFosDivisionYearStudent(
        int $personId,
        string $period = '1. Schulhalbjahr',
        string $marginTop = '10px'
    ) : Slice {

        $textSize = self::TEXT_SIZE_LARGE;

        return (new Slice())
            ->styleMarginTop($marginTop)
            ->addSection((new Section())
                ->addElementColumn($this->getElement('Klassenstufe', $textSize), '20%')
                ->addElementColumn($this->getElement(
                    '{{ Content.P'.$personId.'.Division.Data.Level.Name }}',
                    $textSize
                ), '20%')
                ->addElementColumn($this->getElement('&nbsp;'))
                ->addElementColumn($this->getElement($period, $textSize)
                    , '20%')
                ->addElementColumn($this->getElement('{{ Content.P'.$personId.'.Division.Data.Year }}', $textSize)
                    ->stylePaddingLeft('10px')
                    , '15%')
            )
            ->addSection((new Section())
                ->addElementColumn($this->getElement(
                        '{{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
                        {{ Content.P'.$personId.'.Person.Data.Name.First }} {{ Content.P'.$personId.'.Person.Data.Name.Last }}'
                        , '22px'
                    )
                    ->styleTextBold()
                    ->styleAlignCenter()
                    ->styleMarginTop('0px')
                    ->styleMarginBottom('10px')
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
                    )
                    , '35%')
            );
    }

    /**
     * @param int $personId
     * @param string $marginTop
     * @param bool $hasJobGrade
     * @param bool $hasPretext
     *
     * @return Slice
     */
    protected function getCustomFosSubjectLanes(
        int $personId,
        string $marginTop = '10px',
        bool $hasJobGrade = false,
        bool $hasPretext = false
    ) : Slice
    {
        $textSizeSubject = self::TEXT_SIZE_NORMAL;
        $textSizeGrade = self::TEXT_SIZE_NORMAL;
        $slice = (new Slice())
            ->styleMarginTop($marginTop);

        $slice->addElement($this->getElement('hat im zurückliegenden Schuljahr folgende Leistungen erreicht:', self::TEXT_SIZE_LARGE));
//        $slice->addElement($this->getElement('Pflichtbereich', self::TEXT_SIZE_LARGE)->styleTextBold());

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($this->getCertificateEntity());
        $tblGradeList = $this->getGrade();
        if ($tblCertificateSubjectAll) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if ($tblSubject) {
                    // Grade Exists? => Add Subject to Certificate
                    if (isset($tblGradeList['Data'][$tblSubject->getAcronym()])) {
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym']
                            = $tblSubject->getAcronym();
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName']
                            = $tblSubject->getName();
                    } else {
                        // Grade Missing, But Subject Essential => Add Subject to Certificate
                        if ($tblCertificateSubject->isEssential()) {
                            $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym']
                                = $tblSubject->getAcronym();
                            $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName']
                                = $tblSubject->getName();
                        }
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

            $count = 0;
            foreach ($SubjectStructure as $SubjectList) {
                $count++;
                // Sort Lane-Ranking (1,2...)
                ksort($SubjectList);

                $SubjectSection = (new Section());

                if (count($SubjectList) == 1 && isset($SubjectList[2])) {
                    $SubjectSection->addElementColumn((new Element()), 'auto');
                }

                foreach ($SubjectList as $Lane => $Subject) {
                    // lange Fächernamen
                    $subjectName = str_replace('/', ' / ',  $Subject['SubjectName']);
                    $marginTopGrade = self::MARGIN_TOP_GRADE_LINE;
                    if ($subjectName == 'Volks- und Betriebswirtschaftslehre mit Rechungswesen') {
                        $subjectName = new Container('Volks- und') . new Container('Betriebswirtschaftslehre') . new Container('mit Rechungswesen');
                        $marginTop = '4px';
                        $lineHeight = '60%';
                        $marginTopGrade = '11px';
                    } elseif (strlen($Subject['SubjectName']) > 20) {
                        $marginTop = '0px';
                        $lineHeight = '70%';
                    } else {
                        $marginTop = self::MARGIN_TOP_GRADE_LINE;
                        $lineHeight = '100%';
                    }

                    if ($Lane > 1) {
                        $SubjectSection->addElementColumn((new Element())
                            , '2%');
                    }

                    $SubjectSection->addElementColumn($this->getElement($subjectName, $textSizeSubject)
                        ->styleMarginTop($marginTop)
                        ->styleLineHeight($lineHeight)
//                        , self::SUBJECT_WIDTH . '%');
                        , '26.5%');

                    $SubjectSection->addElementColumn($this->getElement(
                            '{% if(Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                {{ Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] }}
                            {% else %}
                                &ndash;
                            {% endif %}',
                            $textSizeGrade
                        )
                        ->styleAlignCenter()
                        ->styleBackgroundColor(self::BACKGROUND)
                        ->stylePaddingTop(self::PADDING_TOP_GRADE)
                        ->styleMarginTop($marginTopGrade)
                        , self::GRADE_WIDTH . '%');
                }

                if (count($SubjectList) == 1 && isset($SubjectList[1])) {
                    $SubjectSection->addElementColumn((new Element()), '51%');
                }

                $slice->addSection($SubjectSection);
                $SectionList[] = $SubjectSection;
            }
        }

        if ($hasJobGrade) {
            $slice
                ->addSection((new Section())
                    ->addElementColumn($this->getElement('Fachpraktischer Teil der Ausbildung', self::TEXT_SIZE_NORMAL))
                    ->addElementColumn($this->getElement(
                        '{% if(Content.P' . $personId . '.Input.Job_Grade_Text is not empty) %}
                           {{ Content.P' . $personId . '.Input.Job_Grade_Text }}
                        {% else %}
                           &ndash;
                        {% endif %}',
                        $textSizeGrade
                    )
                        ->styleAlignCenter()
                        ->styleBackgroundColor(self::BACKGROUND)
                        ->stylePaddingTop(self::PADDING_TOP_GRADE)
                        ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
                        , self::GRADE_WIDTH . '%')
                );
        }

        return $slice;
    }

    /**
     * @param int $personId
     * @param string $marginTop
     * @param string $height
     * #
     * @return Slice
     */
    public function getCustomFosRemark(int $personId, string $marginTop = '10px', string $height = '75px',
        string $textSize = self::TEXT_SIZE_LARGE) : Slice
    {
        $tblSetting = Consumer::useService()->getSetting('Education', 'Certificate', 'Generator',
            'IsDescriptionAsJustify');
        $slice = (new Slice())
            ->styleMarginTop($marginTop)
            ->styleHeight($height)
            ->addSection((new Section())
                ->addElementColumn($this->getElement('Bemerkungen:', $textSize)
                    ->styleTextUnderline()
                , '25%')
                ->addElementColumn($this->getElement('entschuldigte Fehltage:', $textSize)
                , '27%')
                ->addElementColumn($this->getElement(
                    '{% if(Content.P' . $personId . '.Input.Missing is not empty) %}
                        {{ Content.P' . $personId . '.Input.Missing }}
                    {% else %}
                        &nbsp;
                    {% endif %}',
                    $textSize)
                , '10%')
                ->addElementColumn($this->getElement('unentschuldigte Fehltage:', $textSize)
                , '30%')
                ->addElementColumn($this->getElement(
                    '{% if(Content.P' . $personId . '.Input.Bad.Missing is not empty) %}
                        {{ Content.P' . $personId . '.Input.Bad.Missing }}
                    {% else %}
                        &nbsp;
                    {% endif %}'
                    , $textSize
                ))
            );

        $element = $this->getElement(
            '{% if(Content.P' . $personId . '.Input.RemarkWithoutTeam is not empty) %}
                {{ Content.P' . $personId . '.Input.RemarkWithoutTeam|nl2br }}
            {% else %}
                &nbsp;
            {% endif %}',
            $textSize
        );
        $element->styleLineHeight('80%');
        if ($tblSetting && $tblSetting->getValue()) {
            $element->styleAlignJustify();
        }

        return $slice->addElement($element);
    }

    /**
     * @param int $personId
     * @param string $marginTop
     *
     * @return Slice
     */
    public function getCustomFosTransfer(int $personId, string $marginTop = '5px') : Slice
    {
        // SSWHD-2163 Klasse 12 bei den FOS Jahreszeugnissen besitzt keinen Versetzungsvermerk
        if ($this->getLevel() == 12) {
            $content = '&nbsp;';
        } else {
            $content = '{% if(Content.P' . $personId . '.Input.Transfer) %}
                        {{ Content.P'.$personId.'.Person.Data.Name.Salutation }} {{ Content.P' . $personId . '.Input.Transfer }}.
                    {% else %}
                          &nbsp;
                    {% endif %}';
        }

        return (new Slice())
            ->styleMarginTop($marginTop)
            ->addSection((new Section())
                ->addElementColumn($this->getElement(
                    $content,
                    self::TEXT_SIZE_LARGE
                ))
            );
    }

    /**
     * @param int $personId
     * @param string $marginTop
     *
     * @return Slice
     */
    public function getCustomFosSignPart(int $personId, string $marginTop = '25px', string $textSize = self::TEXT_SIZE_LARGE) : Slice
    {
        $paddingTop = '-8px';
        $paddingTop2 = '-12px';

        return (new Slice())
            ->styleMarginTop($marginTop)
            ->addSection((new Section())
                ->addElementColumn($this->getElement('&nbsp;{{ Content.P' . $personId . '.Company.Address.City.Name }}', $textSize)
                    ->styleAlignCenter()
                    ->styleBorderBottom('0.5px')
                    , '35%')
                ->addElementColumn($this->getElement('', $textSize))
                ->addElementColumn($this->getElement('{{ Content.P' . $personId . '.Input.Date }}', $textSize)
                    ->styleAlignCenter()
                    ->styleBorderBottom('0.5px')
                    , '35%')
            )
            ->addSection((new Section())
                ->addElementColumn($this->getElement('Ort', $textSize)
                    ->stylePaddingTop($paddingTop)
                    ->styleAlignCenter()
                    , '35%')
                ->addElementColumn($this->getElement('&nbsp;', $textSize))
                ->addElementColumn($this->getElement('Datum', $textSize)
                    ->stylePaddingTop($paddingTop)
                    ->styleAlignCenter()
                    , '35%')
            )
            ->addElement($this->getElement('Stempel', self::TEXT_SIZE_SMALL)->styleAlignCenter()->styleMarginTop('-10px'))
            ->addSection((new Section())
                ->addElementColumn($this->getElement('&nbsp;', $textSize)
                    ->styleAlignCenter()
                    ->styleBorderBottom('0.5px')
                    , '35%')
                ->addElementColumn($this->getElement('', $textSize))
                ->addElementColumn($this->getElement('&nbsp;', $textSize)
                    ->styleAlignCenter()
                    ->styleBorderBottom('0.5px')
                    , '35%')
            )
            ->addSection((new Section())
                ->addElementColumn($this->getElement('
                            {% if(Content.P' . $personId . '.Headmaster.Description is not empty) %}
                                {{ Content.P' . $personId . '.Headmaster.Description }}
                            {% else %}
                                Schulleiter(in)
                            {% endif %}',
                        $textSize
                    )
                    ->stylePaddingTop($paddingTop)
                    ->styleAlignCenter()
                    , '35%')
                ->addElementColumn($this->getElement('&nbsp;', $textSize))
                ->addElementColumn($this->getElement('
                        {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                            {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                        {% else %}
                            Klassenlehrer(in)
                        {% endif %}',
                        $textSize
                    )
                    ->stylePaddingTop($paddingTop)
                    ->styleAlignCenter()
                    , '35%')
            )
            ->addSection((new Section())
                ->addElementColumn($this->getElement(
                        '{% if(Content.P' . $personId . '.Headmaster.Name is not empty) %}
                            {{ Content.P' . $personId . '.Headmaster.Name }}
                        {% else %}
                            &nbsp;
                        {% endif %}',
                        $textSize
                    )
                    ->stylePaddingTop($paddingTop2)
                    ->styleAlignCenter()
                    , '35%')
                ->addElementColumn($this->getElement('', $textSize)->stylePaddingTop($paddingTop2))
                ->addElementColumn($this->getElement(
                        '{% if(Content.P' . $personId . '.DivisionTeacher.Name is not empty) %}
                            {{ Content.P' . $personId . '.DivisionTeacher.Name }}
                        {% else %}
                            &nbsp;
                        {% endif %}',
                        $textSize
                    )
                    ->stylePaddingTop($paddingTop2)
                    ->styleAlignCenter()
                    , '35%')
            );
    }

    /**
     * @param int $personId
     * @param string $marginTop
     *
     * @return Slice
     */
    public function getCustomFosAbsSignPart(int $personId, string $marginTop = '145px') : Slice
    {
        $textSize = self::TEXT_SIZE_LARGE;
        $paddingTop = '-8px';
        $paddingTop2 = '-12px';

        $leaderName = '&nbsp;';
        $leaderDescription = 'Vorsitzende/r des Prüfungsausschusses';
        if ($this->getTblPrepareCertificate()
            && ($tblGenerateCertificate = $this->getTblPrepareCertificate()->getServiceTblGenerateCertificate())
        ) {

            if (($tblGenerateCertificateSettingLeader = Generate::useService()->getGenerateCertificateSettingBy($tblGenerateCertificate, 'Leader'))
                && ($tblPersonLeader = Person::useService()->getPersonById($tblGenerateCertificateSettingLeader->getValue()))
            ) {
                $leaderName = $tblPersonLeader->getFullName();
                if (($tblCommon = $tblPersonLeader->getCommon())
                    && ($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())
                    && ($tblGender = $tblCommonBirthDates->getTblCommonGender())
                ) {
                    if ($tblGender->getName() == 'Männlich') {
                        $leaderDescription = 'Vorsitzender des Prüfungsausschusses';
                    } elseif ($tblGender->getName() == 'Weiblich') {
                        $leaderDescription = 'Vorsitzende des Prüfungsausschusses';
                    }
                }
            }
        }

        return (new Slice())
            ->styleMarginTop($marginTop)
            ->addSection((new Section())
                ->addElementColumn($this->getElement('&nbsp;{{ Content.P' . $personId . '.Company.Address.City.Name }}', $textSize)
                    ->styleAlignCenter()
                    ->styleBorderBottom('0.5px')
                    , '30%')
                ->addElementColumn($this->getElement('', $textSize))
                ->addElementColumn($this->getElement('{{ Content.P' . $personId . '.Input.Date }}', $textSize)
                    ->styleAlignCenter()
                    ->styleBorderBottom('0.5px')
                    , '30%')
            )
            ->addSection((new Section())
                ->addElementColumn($this->getElement('Ort', $textSize)
                    ->stylePaddingTop($paddingTop)
                    ->styleAlignCenter()
                    , '30%')
                ->addElementColumn($this->getElement('&nbsp;', $textSize))
                ->addElementColumn($this->getElement('Datum', $textSize)
                    ->stylePaddingTop($paddingTop)
                    ->styleAlignCenter()
                    , '30%')
            )

            ->addElement($this->getElement('Stempel', self::TEXT_SIZE_SMALL)
                ->styleAlignCenter()
                ->styleMarginTop('-10px')
                ->styleMarginBottom('30px')
            )

            ->addSection((new Section())
                ->addElementColumn($this->getElement('&nbsp;', $textSize)
                    ->styleAlignCenter()
                    ->styleBorderBottom('0.5px')
                    , '45%')
                ->addElementColumn($this->getElement('', $textSize))
                ->addElementColumn($this->getElement('&nbsp;', $textSize)
                    ->styleAlignCenter()
                    ->styleBorderBottom('0.5px')
                    , '30%')
            )
            ->addSection((new Section())
                ->addElementColumn($this->getElement($leaderDescription, $textSize)
                    ->stylePaddingTop($paddingTop)
                    ->styleAlignCenter()
                    , '45%')
                ->addElementColumn($this->getElement('&nbsp;', $textSize))
                ->addElementColumn($this->getElement(
                        '{% if(Content.P' . $personId . '.Headmaster.Description is not empty) %}
                            {{ Content.P' . $personId . '.Headmaster.Description }}
                        {% else %}
                            Schulleiter(in)
                        {% endif %}',
                        $textSize
                    )
                    ->stylePaddingTop($paddingTop)
                    ->styleAlignCenter()
                    , '30%')
            )
            ->addSection((new Section())
                ->addElementColumn($this->getElement($leaderName, $textSize)
                    ->stylePaddingTop($paddingTop2)
                    ->styleAlignCenter()
                    , '45%')
                ->addElementColumn($this->getElement('', $textSize)->stylePaddingTop($paddingTop2))
                ->addElementColumn($this->getElement(
                        '{% if(Content.P' . $personId . '.Headmaster.Name is not empty) %}
                            {{ Content.P' . $personId . '.Headmaster.Name }}
                        {% else %}
                            &nbsp;
                        {% endif %}',
                        $textSize
                    )
                    ->stylePaddingTop($paddingTop2)
                    ->styleAlignCenter()
                    , '30%')
            );
    }

    /**
     * @param string $marginTop
     *
     * @return Slice
     */
    protected function getCustomFosParentSign(string $marginTop = '10px') : Slice
    {
        $textSize = self::TEXT_SIZE_LARGE;
        $paddingTop = '-8px';

        return (new Slice())
            ->styleMarginTop($marginTop)
            ->addSection((new Section())
                ->addElementColumn($this->getElement('Zur Kenntnis genommen:', $textSize)
                    , '30%')
                ->addElementColumn($this->getElement('&nbsp;', $textSize)
                    ->styleBorderBottom('0.5px')
                )
            )
            ->addSection((new Section())
                ->addElementColumn($this->getElement('&nbsp;', $textSize)
                    , '30%')
                ->addElementColumn($this->getElement('Eltern', $textSize)
                    ->styleAlignCenter()
                    ->stylePaddingTop($paddingTop)
                )
            );
    }

    /**
     * @param string $marginTop
     *
     * @return Slice
     */
    protected function getCustomFosInfo(string $marginTop = '0px') : Slice
    {
        $textSize = '11px';

        return (new Slice())
            ->styleMarginTop($marginTop)
            ->addElement($this->getElement(
                'Notenstufen: sehr gut (1), gut (2), befriedigend (3), ausreichend (4), mangelhaft (5), ungenügend (6)',
                $textSize
            ));
    }

    /**
     * @param int $personId
     * @param string $marginTop
     *
     * @return Slice
     */
    protected function getCustomFosSkilledWork(int $personId, string $marginTop = '5px') : Slice
    {
        return (new Slice())
            ->styleMarginTop($marginTop)
            ->addSection((new Section())
                ->addElementColumn($this->getElement('Thema der Facharbeit:', self::TEXT_SIZE_LARGE)
                    , (self::SUBJECT_WIDTH + 1) . '%')
                ->addElementColumn($this->getElement(
                    '{% if(Content.P'.$personId.'.Input.SkilledWork is not empty) %}
                        {{ Content.P'.$personId.'.Input.SkilledWork }}
                    {% else %}
                        &ndash;
                    {% endif %}',
                    self::TEXT_SIZE_LARGE
                ))
            )
            ->addSection((new Section())
                ->addElementColumn($this->getElement('Note der Facharbeit:', self::TEXT_SIZE_LARGE)
                    , (self::SUBJECT_WIDTH + 1) . '%')
                ->addElementColumn(
                    $this->getElement(
                            '{% if(Content.P'.$personId.'.Input.SkilledWork_GradeText is not empty) %}
                                 {{ Content.P'.$personId.'.Input.SkilledWork_GradeText }}
                            {% else %}
                               {% if(Content.P'.$personId.'.Input.SkilledWork_Grade is not empty) %}
                                   {{ Content.P'.$personId.'.Input.SkilledWork_Grade }}
                               {% else %}
                                   &ndash;
                               {% endif %}
                            {% endif %}',
                            self::TEXT_SIZE_LARGE
                        )
                        ->styleAlignCenter()
                        ->styleBackgroundColor(self::BACKGROUND)
                        ->stylePaddingTop(self::PADDING_TOP_GRADE)
                        ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
                    , self::GRADE_WIDTH . '%')
                ->addElementColumn($this->getElement('&nbsp;', self::TEXT_SIZE_LARGE))
            );
    }

    /**
     * @param int $personId
     * @param string $marginTop
     *
     * @return Slice
     */
    protected function getCustomSubjectLanesFosAbs(int $personId, string $marginTop = '28px') : Slice
    {
        $slice = (new Slice())
            ->styleMarginTop($marginTop)
            ->addElement($this->getElement('Leistungen:', self::TEXT_SIZE_LARGE)->styleTextBold())
//            ->addElement($this->getElement('Pflichtbereich', self::TEXT_SIZE_LARGE)->styleTextBold()->styleMarginTop('5px'))
        ;

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($this->getCertificateEntity());
        $tblGradeList = $this->getGrade();
        if ($tblCertificateSubjectAll) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if ($tblSubject) {
                    // Grade Exists? => Add Subject to Certificate
                    if (isset($tblGradeList['Data'][$tblSubject->getAcronym()])) {
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym']
                            = $tblSubject->getAcronym();
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName']
                            = $tblSubject->getName();
                    } else {
                        // Grade Missing, But Subject Essential => Add Subject to Certificate
                        if ($tblCertificateSubject->isEssential()) {
                            $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym']
                                = $tblSubject->getAcronym();
                            $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName']
                                = $tblSubject->getName();
                        }
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

            $count = 0;
            foreach ($SubjectStructure as $SubjectList) {
                $count++;
                // Sort Lane-Ranking (1,2...)
                ksort($SubjectList);

                foreach ($SubjectList as $Lane => $Subject) {
                    $section = new Section();
                    $this->setGradeFullLine(
                        $section,
                        $Subject['SubjectName'],
                        '{% if(Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                            {{ Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] }}
                        {% else %}
                            &ndash;
                        {% endif %}',
                        self::TEXT_SIZE_LARGE
                    );
                    $slice->addSection($section);
                }
            }
        }

        $section = new Section();
        $this->setGradeFullLine(
            $section,
            'Fachpraktischer Teil der Ausbildung',
            '{% if(Content.P' . $personId . '.Input.Job_Grade_Text is not empty) %}
               {{ Content.P' . $personId . '.Input.Job_Grade_Text }}
            {% else %}
               &ndash;
            {% endif %}',
            self::TEXT_SIZE_LARGE
        );
        $slice->addSection($section);

        return $slice;
    }

    /**
     * @param int $personId
     *
     * @return Slice
     */
    public function getCustomIndustrialPlacement(int $personId) : Slice
    {
        return (new Slice())
            ->styleMarginTop('10px')
            ->addSection((new Section())
                ->addElementColumn($this->getElement('Betriebspraktikum', self::TEXT_SIZE_LARGE)->styleTextBold(), '25%')
                ->addElementColumn($this->getElement(
                    '{% if(Content.P' . $personId . '.Input.IndustrialPlacement is not empty) %}
                       {{ Content.P' . $personId . '.Input.IndustrialPlacement }}
                    {% else %}
                       &ndash;
                    {% endif %}'
                    , self::TEXT_SIZE_LARGE))
                ->addElementColumn($this->getElement(
                    'Dauer:
                    {% if(Content.P' . $personId . '.Input.IndustrialPlacementDuration is not empty) %}
                       {{ Content.P' . $personId . '.Input.IndustrialPlacementDuration }}
                    {% else %}
                       &ndash;
                    {% endif %}
                    Wochen',
                    self::TEXT_SIZE_LARGE)->styleAlignRight(), '20%')
            );
    }

    /**
     * @param int $personId
     * @param string $marginTop
     *
     * @return Slice
     */
    protected function getCustomSubjectLanesBgjAbs(int $personId, string $marginTop = '28px', $isHalfYear = false) : Slice
    {
        $textSize = $isHalfYear ? self::TEXT_SIZE_NORMAL : self::TEXT_SIZE_LARGE;
        $textSizeGrade = self::TEXT_SIZE_NORMAL;
        $textSizeSubject = self::TEXT_SIZE_NORMAL;

        if ($isHalfYear) {
            $slice = (new Slice())
                ->styleMarginTop($marginTop)
                ->addElement($this->getElement('hat im zurückliegenden Schulhalbjahr folgende Leistungen erreicht:', $textSize))
                ->addElement($this->getElement('Pflichtbereich',
                    $textSize)->styleTextBold()->styleMarginTop('5px'));
        } else {
            $slice = (new Slice())
                ->styleMarginTop($marginTop)
                ->addElement($this->getElement('Leistungen', $textSize)->styleTextBold())
                ->addElement($this->getElement('Pflichtbereich',
                    $textSize)->styleTextBold()->styleMarginTop('5px'));
        }

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($this->getCertificateEntity());
        $tblGradeList = $this->getGrade();
        if ($tblCertificateSubjectAll) {
            // Berufsübergreifender Bereich, 2 spaltig
            $slice->addElement($this->getElement('Berufsübergreifender Bereich', $textSize)
                ->styleTextUnderline()
                ->stylePaddingBottom('-5px')
                ->stylePaddingBottom('4px')
            );
            $SubjectStructure = $this->getSubjectStructure($tblCertificateSubjectAll, $tblGradeList, 1, 4);
            $count = 0;
            foreach ($SubjectStructure as $SubjectList) {
                $count++;
                // Sort Lane-Ranking (1,2...)
                ksort($SubjectList);

                $SubjectSection = (new Section());

                if (count($SubjectList) == 1 && isset($SubjectList[2])) {
                    $SubjectSection->addElementColumn((new Element()), 'auto');
                }

                foreach ($SubjectList as $Lane => $Subject) {
                    // lange Fächernamen
                    $Subject['SubjectName'] = str_replace('/', ' / ', $Subject['SubjectName']);
                    if (strlen($Subject['SubjectName']) > 25) {
                        $marginTop = '0px';
                        $lineHeight = '70%';
                    } else {
                        $marginTop = self::MARGIN_TOP_GRADE_LINE;
                        $lineHeight = '100%';
                    }

                    if ($Lane > 1) {
                        $SubjectSection->addElementColumn((new Element())
                            , '2%');
                    }

                    $SubjectSection->addElementColumn($this->getElement($Subject['SubjectName'], $textSizeSubject)
                        ->styleMarginTop($marginTop)
                        ->styleLineHeight($lineHeight)
                        , (self::SUBJECT_WIDTH + 1) . '%');

                    $SubjectSection->addElementColumn($this->getElement(
                        '{% if(Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                {{ Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] }}
                            {% else %}
                                &ndash;
                            {% endif %}',
                        $textSizeGrade
                    )
                        ->styleAlignCenter()
                        ->styleBackgroundColor(self::BACKGROUND)
                        ->stylePaddingTop(self::PADDING_TOP_GRADE)
                        ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
                        , self::GRADE_WIDTH . '%');
                }

                if (count($SubjectList) == 1 && isset($SubjectList[1])) {
                    $SubjectSection->addElementColumn((new Element()), '51%');
                }

                $slice->addSection($SubjectSection);
            }

            // Berufsbezogener Bereich - fachtheoretischer Unterricht, 1 spaltig
            $slice->addElement($this->getElement('Berufsbezogener Bereich - fachtheoretischer Unterricht', $textSize)
                ->styleTextUnderline()
                ->stylePaddingBottom('-5px')
                ->stylePaddingBottom('4px')
                ->styleMarginTop('4px')
            );
            $SubjectStructure = $this->getSubjectStructure($tblCertificateSubjectAll, $tblGradeList, 5, 10);
            $count = 0;
            foreach ($SubjectStructure as $SubjectList) {
                $count++;
                // Sort Lane-Ranking (1,2...)
                ksort($SubjectList);

                foreach ($SubjectList as $Lane => $Subject) {
                    $section = new Section();

                    $section->addElementColumn(
                        $this->getElement($Subject['SubjectName'], $textSizeSubject)
                            ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
                    );
                    $section->addElementColumn(
                        $this->getElement(
                                '{% if(Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                    {{ Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] }}
                                {% else %}
                                    &ndash;
                                {% endif %}',
                                $textSizeGrade
                            )
                            ->styleAlignCenter()
                            ->styleBackgroundColor(self::BACKGROUND)
                            ->stylePaddingTop(self::PADDING_TOP_GRADE)
                            ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
                        , self::GRADE_WIDTH . '%');

                    $slice->addSection($section);
                }
            }

            // Berufsbezogener Bereich - fachpraktischer Unterricht, 1 spaltig
            $slice->addElement($this->getElement('Berufsbezogener Bereich - fachpraktischer Unterricht', $textSize)
                ->styleTextUnderline()
                ->stylePaddingBottom('-5px')
                ->stylePaddingBottom('4px')
                ->styleMarginTop('4px')
            );
            $SubjectStructure = $this->getSubjectStructure($tblCertificateSubjectAll, $tblGradeList, 11, 15);
            $count = 0;
            foreach ($SubjectStructure as $SubjectList) {
                $count++;
                // Sort Lane-Ranking (1,2...)
                ksort($SubjectList);

                foreach ($SubjectList as $Lane => $Subject) {
                    $section = new Section();

                    $section->addElementColumn(
                        $this->getElement($Subject['SubjectName'], $textSizeSubject)
                            ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
                    );
                    $section->addElementColumn(
                        $this->getElement(
                            '{% if(Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                    {{ Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] }}
                                {% else %}
                                    &ndash;
                                {% endif %}',
                            $textSizeGrade
                        )
                            ->styleAlignCenter()
                            ->styleBackgroundColor(self::BACKGROUND)
                            ->stylePaddingTop(self::PADDING_TOP_GRADE)
                            ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
                        , self::GRADE_WIDTH . '%');

                    $slice->addSection($section);
                }
            }
        }

        return $slice;
    }

    /**
     * @param array $tblCertificateSubjectAll
     * @param $tblGradeList
     * @param int $subjectRankingFrom
     * @param int $subjectRankingTo
     *
     * @return array
     */
    private function getSubjectStructure(array $tblCertificateSubjectAll, $tblGradeList,
        int $subjectRankingFrom, int $subjectRankingTo) : array
    {
        $SubjectStructure = array();
        foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
            $tblSubject = $tblCertificateSubject->getServiceTblSubject();
            if ($tblSubject) {
                if($tblCertificateSubject->getRanking() >= $subjectRankingFrom
                    && $tblCertificateSubject->getRanking() <= $subjectRankingTo){
                    // Grade Exists? => Add Subject to Certificate
                    if (isset($tblGradeList['Data'][$tblSubject->getAcronym()])) {
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym']
                            = $tblSubject->getAcronym();
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName']
                            = $tblSubject->getName();
                    } else {
                        // Grade Missing, But Subject Essential => Add Subject to Certificate
                        if ($tblCertificateSubject->isEssential()) {
                            $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym']
                                = $tblSubject->getAcronym();
                            $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName']
                                = $tblSubject->getName();
                        }
                    }
                }
            }
        }

        // Shrink Lanes
        $LaneCounter = array(1 => 0, 2 => 0);
        $SubjectLayout = array();
        if (!empty($SubjectStructure)) {
            ksort($SubjectStructure);
            foreach ($SubjectStructure as $SubjectList) {
                ksort($SubjectList);
                foreach ($SubjectList as $Lane => $Subject) {
                    $SubjectLayout[$LaneCounter[$Lane]][$Lane] = $Subject;
                    $LaneCounter[$Lane]++;
                }
            }
        }
        return $SubjectLayout;
    }
}