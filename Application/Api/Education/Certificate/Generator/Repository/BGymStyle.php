<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Layout\Repository\Container;

abstract class BGymStyle extends Certificate
{
    // Element
    const BORDER_SIZE = '0.5px';
    const BORDER_COLOR = 'lightgray';
    const PADDING_BOTTOM = '3px';

    // Grades
    const BACKGROUND = self::BACKGROUND_GRADE_FIELD;
    const MARGIN_TOP_GRADE_LINE = '8px';
    const PADDING_TOP_GRADE = '4px';
    const PADDING_BOTTOM_GRADE = '4px';
    const SUBJECT_WIDTH = 37;
    const GRADE_WIDTH = 10;
    const TEXT_SIZE_SMALL = '14px';

    protected function getHeaderBGym(string $title, string $textSizeSample = '30px', string $marginTop = '25px'): Slice
    {
        $slice = $this->getSchoolNameBGym($marginTop);

        // Sample
        if ($this->isSample()) {
            $slice->addElement((new Element\Sample())->styleTextSize($textSizeSample));
        } else {
            $slice->addElement((new Element())->setContent('&nbsp;')->styleTextSize($textSizeSample));
        }

        // title
        $slice
            ->addElement((new Element())
                ->setContent($title)
                ->styleAlignCenter()
                ->styleTextSize('28px')
            )
            ->addElement((new Element())
                ->setContent('des Beruflichen Gymnasiums')
                ->styleAlignCenter()
                ->styleTextSize('18px')
                ->styleMarginTop('5px')
            );

        return $slice;
    }

    /**
     * @param string $marginTop
     *
     * @return Slice
     */
    protected function getSchoolNameBGym(string $marginTop = '25px'): Slice
    {
        if (($tblSetting = Consumer::useService()->getSetting(
                'Education', 'Certificate', 'Prepare', 'IsSchoolExtendedNameDisplayed'))
            && $tblSetting->getValue()
        ) {
            $isSchoolExtendedNameDisplayed = true;
        } else {
            $isSchoolExtendedNameDisplayed = false;
        }
        if (($tblSetting = Consumer::useService()->getSetting(
                'Education', 'Certificate', 'Prepare', 'SchoolExtendedNameSeparator'))
            && $tblSetting->getValue()
        ) {
            $separator = $tblSetting->getValue();
        } else {
            $separator = false;
        }

        $name = '&nbsp;';
        // get company name
        if (($tblCompany = $this->getTblCompany())) {
            $name = $isSchoolExtendedNameDisplayed ? $tblCompany->getName() .
                ($separator ? ' ' . $separator . ' ' : ' ') . $tblCompany->getExtendedName() : $tblCompany->getName();
        }

        return (new Slice())
            ->addElement((new Element())
                ->setContent($name)
                ->styleAlignCenter()
                ->styleTextSize('16px')
                ->styleMarginTop($marginTop)
            );
    }

    /**
     * @param $personId
     * @param string $marginTop
     *
     * @return Slice
     */
    protected function getSubjectArea($personId, string $marginTop = '15px'): Slice
    {
        $subjectArea = '&nbsp;';
        if (($tblPerson = Person::useService()->getPersonById($personId))
            && ($tblStudent = $tblPerson->getStudent())
            && ($tblStudentTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())
            && ($tblTechnicalSubjectArea = $tblStudentTechnicalSchool->getServiceTblTechnicalSubjectArea())
        ) {
            $subjectArea = $tblTechnicalSubjectArea->getName();
        }

        return (new Slice())
            ->addElement((new Element())
                ->setContent('Fachrichtung ' . $subjectArea)
                ->styleAlignCenter()
                ->styleTextSize('16px')
                ->styleMarginTop($marginTop)
            );
    }

    /**
     * @param $personId
     * @param string $marginTop
     *
     * @return Slice
     */
    protected function getSubjectAreaDiploma($personId, string $marginTop = '10px'): Slice
    {
        $subjectArea = '&nbsp;';
        if (($tblPerson = Person::useService()->getPersonById($personId))
            && ($tblStudent = $tblPerson->getStudent())
            && ($tblStudentTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())
            && ($tblTechnicalSubjectArea = $tblStudentTechnicalSchool->getServiceTblTechnicalSubjectArea())
        ) {
            $subjectArea = $tblTechnicalSubjectArea->getName();
        }

        return (new Slice())
            ->addElement((new Element())
                ->setContent('Berufliche Gymnasium' . new Container('Fachrichtung ' . $subjectArea))
                ->styleTextBold()
                ->styleAlignCenter()
                ->styleTextSize('16px')
                ->styleMarginTop($marginTop)
            );
    }

    /**
     * @param $personId
     * @param string $period
     * @param string $level
     * @param string $marginTop
     *
     * @return Slice
     */
    protected function getLevelYearStudent($personId, string $period, string $level = 'Klassenstufe', string $marginTop = '15px'): Slice
    {
        return (new Slice())
            ->styleMarginTop($marginTop)
            ->addSection((new Section())
                ->addElementColumn(
                    $this->getElement($level . ' {{ Content.P' . $personId . '.Division.Data.Level.Name }}')
                    , '25%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                )
                ->addElementColumn(
                    $this->getElement($period . ' {{ Content.P' . $personId . '.Division.Data.Year }}')
                    , '25%')
            )
            ->addElement(
                $this->getElement('{{ Content.P' . $personId . '.Person.Data.Name.Salutation }} {{ Content.P' . $personId . '.Person.Data.Name.First }} 
                        {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                    ->styleTextSize('22px')
                    ->styleMarginTop($marginTop)
                    ->styleMarginBottom($marginTop)
            )
            ->addSection((new Section())
                ->addElementColumn(
                    $this->getElement('geboren am 
                        {% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthday is not empty) %}
                            {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                        {% else %}
                            &nbsp;
                        {% endif %}'
                    )
                    , '35%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                )
                ->addElementColumn(
                    $this->getElement('in
                        {% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthplace is not empty) %}
                            {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthplace }}
                        {% else %}
                            &nbsp;
                        {% endif %}'
                    )
                    , '35%')
            );
    }

    public function getStudentLeaveDiploma($personId, string $marginTop = '15px'): Slice
    {
        return (new Slice())
            ->styleMarginTop('5px')
            ->addElement(
                $this->getElementDiploma('{{ Content.P' . $personId . '.Person.Data.Name.Salutation }} {{ Content.P' . $personId . '.Person.Data.Name.First }} 
                        {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                    ->styleTextSize('22px')
                    ->styleMarginTop($marginTop)
                    ->styleMarginBottom($marginTop)
            )
            ->addSection((new Section())
                ->addElementColumn(
                    $this->getElementDiploma('geboren am 
                        {% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthday is not empty) %}
                            {{ Content.P'.$personId.'.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                        {% else %}
                            &nbsp;
                        {% endif %}'
                    )
                    , '35%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                )
                ->addElementColumn(
                    $this->getElementDiploma('in
                        {% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthplace is not empty) %}
                            {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthplace }}
                        {% else %}
                            &nbsp;
                        {% endif %}'
                    )
                    , '35%')
            )
        ;
    }

    protected function getElement(string $content): Element
    {
        return (new Element())
            ->setContent($content)
            ->styleAlignCenter()
            ->styleBorderBottom(self::BORDER_SIZE, self::BORDER_COLOR)
            ->stylePaddingBottom(self::PADDING_BOTTOM);
    }

    protected function getElementDiploma(string $content): Element
    {
        return (new Element())
            ->setContent($content)
            ->styleAlignCenter()
            ->styleBorderBottom()
            ->stylePaddingBottom(self::PADDING_BOTTOM);
    }

    private function getElementSubjectName(string $content): Element
    {
        return (new Element())
            ->setContent($content)
            ->styleBorderBottom(self::BORDER_SIZE, self::BORDER_COLOR)
            ->stylePaddingBottom();
    }

    /**
     * @param int $personId
     * @param string $period
     * @param string $marginTop
     * @param string $height
     *
     * @return Slice
     */
    protected function getSubjectLanesBGym(int $personId, string $period = 'Schulhalbjahr', string $marginTop = '15px', string $height = '270px') : Slice
    {
        $slice = (new Slice())
            ->styleMarginTop($marginTop)
            ->addElement($this->getElement('hat im zurückliegenden ' . $period . ' folgende Leistungen erreicht:'))
            ->addElement((new Element())
                ->setContent('Pflichtbereich')
                ->styleAlignCenter()
                ->styleTextSize('15px')
                ->styleMarginTop('10px')
            );

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
                    if (strlen($Subject['SubjectName']) > 45) {
                        $marginTop = '0px';
                        $paddingBottom = '0px';
                        $lineHeight = '80%';
                    } else {
                        $marginTop = self::MARGIN_TOP_GRADE_LINE;
                        $paddingBottom = '5px';
                        $lineHeight = '100%';
                    }

                    if ($Lane > 1) {
                        $SubjectSection->addElementColumn((new Element())
                            , '4%');
                    }

                    $SubjectSection->addElementColumn($this->getElementSubjectName($Subject['SubjectName'])
                        ->styleMarginTop($marginTop)
                        ->styleLineHeight($lineHeight)
                        ->stylePaddingBottom($paddingBottom)
                        ->styleTextSize(self::TEXT_SIZE_SMALL)
                        , self::SUBJECT_WIDTH . '%');

                    $SubjectSection->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '1%'
                    );

                    $SubjectSection->addElementColumn((new Element())
                        ->setContent(
                            '{% if(Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                {{ Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] }}
                            {% else %}
                                &ndash;
                            {% endif %}'
                        )
                        ->styleAlignCenter()
                        ->styleBackgroundColor(self::BACKGROUND)
                        ->stylePaddingTop(self::PADDING_TOP_GRADE)
                        ->stylePaddingBottom(self::PADDING_BOTTOM_GRADE)
                        ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
                        , self::GRADE_WIDTH . '%');
                }

                if (count($SubjectList) == 1 && isset($SubjectList[1])) {
                    $SubjectSection->addElementColumn((new Element()), '52%');
                }

                $slice->addSection($SubjectSection);
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
    protected function getChosenLanesBGym(int $personId, string $marginTop = '10px') : Slice
    {
        $slice = (new Slice())
            ->styleMarginTop($marginTop)
            ->addElement((new Element())
                ->setContent('Wahlbereich')
                ->styleAlignCenter()
                ->styleTextSize('15px')
                ->styleMarginTop('10px')
            );

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
                    if (strlen($Subject['SubjectName']) > 45) {
                        $marginTop = '0px';
                        $paddingBottom = '0px';
                        $lineHeight = '80%';
                    } else {
                        $marginTop = self::MARGIN_TOP_GRADE_LINE;
                        $paddingBottom = '5px';
                        $lineHeight = '100%';
                    }

                    if ($Lane > 1) {
                        $SubjectSection->addElementColumn((new Element())
                            , '4%');
                    }

                    $SubjectSection->addElementColumn($this->getElementSubjectName($Subject['SubjectName'])
                        ->styleMarginTop($marginTop)
                        ->styleLineHeight($lineHeight)
                        ->stylePaddingBottom($paddingBottom)
                        ->styleTextSize(self::TEXT_SIZE_SMALL)
                        , self::SUBJECT_WIDTH . '%');

                    $SubjectSection->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '1%'
                    );

                    $SubjectSection->addElementColumn((new Element())
                        ->setContent(
                            '{% if(Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                {{ Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] }}
                            {% else %}
                                &ndash;
                            {% endif %}'
                        )
                        ->styleAlignCenter()
                        ->styleBackgroundColor(self::BACKGROUND)
                        ->stylePaddingTop(self::PADDING_TOP_GRADE)
                        ->stylePaddingBottom(self::PADDING_BOTTOM_GRADE)
                        ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
                        , self::GRADE_WIDTH . '%');
                }

                if (count($SubjectList) == 1 && isset($SubjectList[1])) {
                    $SubjectSection->addElementColumn((new Element()), '52%');
                }

                $slice->addSection($SubjectSection);
            }
        }

        return $slice->styleHeight('90px');
    }

    /**
     * @param $personId
     * @param bool $isMissing
     * @param string $height
     * @param string $marginTop
     *
     * @return Slice
     */
    protected function getRemarkBGym($personId, bool $isMissing, string $height = '60px', string $marginTop = '15px'): Slice
    {
        $slice = (new Slice());
        if ($isMissing) {
            $slice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Bemerkungen:')
                    ->styleTextUnderline()
                    , '16%')
                ->addElementColumn((new Element())
                    ->setContent('Fehltage entschuldigt:')
                    ->styleAlignRight()
                    , '25%')
                ->addElementColumn((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Input.Missing is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                    ->styleAlignCenter()
                    , '10%')
                ->addElementColumn((new Element())
                    ->setContent('unentschuldigt:')
                    ->styleAlignRight()
                    , '25%')
                ->addElementColumn((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Input.Bad.Missing is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Bad.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                    ->styleAlignCenter()
                    , '10%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    , '4%')
            );
        } else {
            $slice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Bemerkungen:')
                    ->styleTextUnderline()
                )
            );
        }

        $tblSetting = Consumer::useService()->getSetting('Education', 'Certificate', 'Generator', 'IsDescriptionAsJustify');
        $element = (new Element())
            ->setContent('{{ Content.P' . $personId . '.Input.RemarkWithoutTeam|nl2br }}')
            ->styleMarginTop('5px');
        if ($tblSetting && $tblSetting->getValue()) {
            $element->styleAlignJustify();
        }
        $slice->addElement($element);

        return $slice
            ->styleMarginTop($marginTop)
            ->styleHeight($height)
            ->stylePaddingLeft('4px')
            ->stylePaddingRight('4px')
            ->stylePaddingTop('4px')
            ->stylePaddingBottom('4px')
            ->styleBorderAll(self::BORDER_SIZE, self::BORDER_COLOR, 'dotted')
            ;
    }

    /**
     * @param $personId
     *
     * @return Slice
     */
    public function getTransferBGym($personId): Slice
    {
        return (new Slice())
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Versetzungsvermerk:')
                    ->styleTextUnderline()
                    ->stylePaddingLeft('4px')
                    ->stylePaddingRight('4px')
                    ->stylePaddingTop('4px')
                    ->stylePaddingBottom('4px')
                    , '25%')
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if(Content.P' . $personId . '.Input.Transfer) %}
                            {{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
                            {{ Content.P' . $personId . '.Input.Transfer }}.
                        {% else %}
                              &nbsp;
                        {% endif %}')
                    ->stylePaddingTop('5px')
                    ->stylePaddingBottom('4px')
                    , '75%')
            )
            ->styleBorderLeft(self::BORDER_SIZE, self::BORDER_COLOR, 'dotted')
            ->styleBorderRight(self::BORDER_SIZE, self::BORDER_COLOR, 'dotted')
            ->styleBorderBottom(self::BORDER_SIZE, self::BORDER_COLOR, 'dotted');
    }

    /**
     * @param $personId
     * @param bool $hasTudor
     * @param string $marginTop
     * @param bool $hasCustodySign
     *
     * @return Slice
     */
    protected function getSignPartBGym($personId, bool $hasTudor = false, string $marginTop = '30px', bool $hasCustodySign = true): Slice
    {
        $divisionTeacherDescription = $hasTudor
            ? '{% if(Content.P' . $personId . '.Tudor.Description is not empty) %}
                  {{ Content.P' . $personId . '.Tudor.Description }}
              {% else %}
                  Tutor/in
              {% endif %}'
            : '{% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                  {{ Content.P' . $personId . '.DivisionTeacher.Description }}
              {% else %}
                  Klassenlehrer/in
              {% endif %}';

        $slice = (new Slice())
            ->styleMarginTop($marginTop)
            ->addSection((new Section())
                ->addElementColumn(
                    $this->getElement('{% if( Content.P' . $personId . '.Company.Address.City.Name is not empty) %}
                            {{ Content.P' . $personId . '.Company.Address.City.Name }}
                        {% else %}
                            &nbsp;
                        {% endif %}')
                    , '35%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                )
                ->addElementColumn(
                    $this->getElement('
                        {% if( Content.P' . $personId . '.Input.Date is not empty) %}
                            {{ Content.P' . $personId . '.Input.Date }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    , '35%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Ort')
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
                    , '35%')
                ->addElementColumn((new Element())
                    , '5%')
                ->addElementColumn((new Element())
                    ->setContent('Siegel')
                    ->styleTextColor('gray')
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
                    , '20%')
                ->addElementColumn((new Element())
                    , '5%')
                ->addElementColumn((new Element())
                    ->setContent('Datum')
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
                    , '35%')
            )
            ->addSection((new Section())
                ->addElementColumn(
                    $this->getElement('&nbsp;')->styleMarginTop('30px')
                    , '35%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                )
                ->addElementColumn(
                    $this->getElement('&nbsp;')->styleMarginTop('30px')
                    , '35%')
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
                    ->styleTextSize('11px')
                    , '35%')
                ->addElementColumn((new Element())
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent($divisionTeacherDescription)
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
                    , '35%')
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
                    ->styleTextSize('11px')
                    ->stylePaddingTop('2px')
                    ->styleAlignCenter()
                    , '35%')
                ->addElementColumn((new Element())
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent(
                        '{% if(Content.P' . $personId . '.DivisionTeacher.Name is not empty) %}
                            {{ Content.P' . $personId . '.DivisionTeacher.Name }}
                        {% else %}
                            &nbsp;
                        {% endif %}'
                    )
                    ->styleTextSize('11px')
                    ->stylePaddingTop('2px')
                    ->styleAlignCenter()
                    , '35%')
            )
            ;

        if ($hasCustodySign) {
            $slice
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Zur Kenntnis genommen:')
                        ->styleMarginTop($marginTop)
                        , '27%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleMarginTop('30px')
                        ->styleBorderBottom('1px', 'black', 'dotted')
                        , '73%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '27%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Eltern')
                        ->styleTextSize('10px')
                        ->styleAlignCenter()
                        , '73%'
                    )
                );
        }

        return $slice;
    }

    /**
     * @return Slice
     */
    protected function getGradeLevel(): Slice
    {
        return (new Slice())
            ->addElement((new Element())
                ->setContent('NOTENSTUFEN: sehr gut (1), gut (2), befriedigend (3), ausreichend (4), mangelhaft (5), ungenügend (6)')
                ->styleTextSize('11px')
            );
    }

    /**
     * @param bool $hasGradeLevelLine
     *
     * @return Slice
     */
    protected function getFootNotesSekII(bool $hasGradeLevelLine = true): Slice
    {
        $left = '4%';
        $textSize = '9.5px';
        $slice = (new Slice())
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($this->setSup('1)'))
                    , $left
                )
                ->addElementColumn((new Element())
                    ->setContent('Leistungskursfächer sind mit LF gekennzeichnet. Alle Punktzahlen werden zweistellig angegeben.')
                    ->styleTextSize($textSize)
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($this->setSup('2)'))
                    ->styleMarginTop('5px')
                    , $left
                )
                ->addElementColumn((new Element())
                    ->setContent('Für die Umsetzung der Punkte in Noten gilt:')
                    ->styleMarginTop('5px')
                    ->styleTextSize($textSize)
                    , '25%'
                )
                ->addSliceColumn($this->setPointsOverview($textSize)
                    ->styleMarginTop('5px')
                )
            );

        if ($hasGradeLevelLine) {
            $slice
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent($this->setSup('3)'))
                        ->styleMarginTop('5px')
                        , $left
                    )
                    ->addElementColumn((new Element())
                        ->setContent('NOTENSTUFEN: sehr gut (1), gut (2), befriedigend (3), ausreichend (4), mangelhaft (5), ungenügend (6)')
                        ->styleMarginTop('5px')
                        ->styleTextSize($textSize)
                    )
                );
        }

        return $slice;
    }

    /**
     * @param string $textSize
     *
     * @return Slice
     */
    protected function setPointsOverview(string $textSize): Slice
    {
        $slice = new Slice();

        $section = new Section();
        $this->setColumnElement($section, 'Punkte', $textSize);
        $this->setColumnElement($section, '15 14 13', $textSize);
        $this->setColumnElement($section, '12 11 10', $textSize);
        $this->setColumnElement($section, '09 08 07', $textSize);
        $this->setColumnElement($section, '06 05 04', $textSize);
        $this->setColumnElement($section, '03 02 01', $textSize);
        $this->setColumnElement($section, '00', $textSize, '7px', false, true);
        $slice
            ->addSection($section);

        $section = new Section();
        $this->setColumnElement($section, 'Note', $textSize, '2px', true);
        $this->setColumnElement($section, 'sehr gut', $textSize, '2px', true);
        $this->setColumnElement($section, 'gut', $textSize, '2px', true);
        $this->setColumnElement($section, 'befriedigend', $textSize, '2px', true);
        $this->setColumnElement($section, 'ausreichend', $textSize, '2px', true);
        $this->setColumnElement($section, 'mangelhaft', $textSize, '2px', true);
        $this->setColumnElement($section, 'ungenügend', $textSize, '2px', true, true);
        $slice
            ->addSection($section);

        return $slice;
    }

    /**
     * @param Section $section
     * @param string $name
     * @param $textSize
     * @param string $padding
     * @param bool $isBorderBottom
     * @param bool $isBorderRight
     */
    private function setColumnElement(
        Section $section,
        string $name,
        $textSize,
        string $padding = '7px',
        bool $isBorderBottom = false,
        bool $isBorderRight = false
    ) {

        $section
            ->addElementColumn((new Element())
                ->setContent($name)
                ->styleTextSize($textSize)
                ->styleAlignCenter()
                ->styleBorderLeft()
                ->styleBorderTop()
                ->stylePaddingTop($padding)
                ->stylePaddingBottom($padding)
                ->styleBorderRight($isBorderRight ? '1px' : '0px')
                ->styleBorderBottom($isBorderBottom ? '1px' : '0px')
                , '15%');
    }

    /**
     * @param string $marginTop
     *
     * @return Slice
     */
    protected function getLevelMidTerm(string $marginTop = '15px'): Slice
    {
        $midTerm = '/I';
        if (($tblPrepare = $this->getTblPrepareCertificate())
            && ($date = $tblPrepare->getDateTime())
            && ($month = intval($date->format('m')))
            && $month > 3 && $month < 9
        ) {
            $midTerm = '/II';
        }

        $period = ($this->getLevel() ?: '') . $midTerm;

        return (new Slice())->addElement(
            $this->getElement('hat im Kurshalbjahr ' . $period . ' folgende Leistungen erreicht:' )
                ->styleMarginTop($marginTop)
        );
    }

    /**
     * @param string $workField
     *
     * @return array
     */
    public static function getSubjectListByWorkField(string $workField): array
    {
        $tblSubjectList  = array();
        if ($workField == 'Sprachlich-literarisch-künstlerisches Aufgabenfeld') {
            if (($tblSubject = Subject::useService()->getSubjectByName('Deutsch'))) {
                $tblSubjectList[$tblSubject->getId()] = $tblSubject;
            }
            // Fremdsprachen
            if (($tblCategory = Subject::useService()->getCategoryByIdentifier('FOREIGNLANGUAGE'))
                && ($tblSubjectListForeignLanguage = $tblCategory->getTblSubjectAll())
            ) {
                foreach ($tblSubjectListForeignLanguage as $tblSubjectForeignLanguage) {
                    $tblSubjectList[$tblSubjectForeignLanguage->getId()] = $tblSubjectForeignLanguage;
                }
            }
            if (($tblSubject = Subject::useService()->getSubjectByName('Kunst'))) {
                $tblSubjectList[$tblSubject->getId()] = $tblSubject;
            }
            if (($tblSubject = Subject::useService()->getSubjectByName('Literatur'))) {
                $tblSubjectList[$tblSubject->getId()] = $tblSubject;
            }
            if (($tblSubject = Subject::useService()->getSubjectByName('Musik'))) {
                $tblSubjectList[$tblSubject->getId()] = $tblSubject;
            }
        } elseif ($workField == 'Gesellschaftswissenschaftliches Aufgabenfeld') {
            if (($tblSubject = Subject::useService()->getSubjectByName('Geschichte/Gemeinschaftskunde'))
                || ($tblSubject = Subject::useService()->getSubjectByName('Geschichte / Gemeinschaftskunde'))
            ) {
                $tblSubjectList[$tblSubject->getId()] = $tblSubject;
            }
            if (($tblSubject = Subject::useService()->getSubjectByName('Gesundheit und Soziales'))) {
                $tblSubjectList[$tblSubject->getId()] = $tblSubject;
            }
            if (($tblSubject = Subject::useService()->getSubjectByName('Volks- und Betriebswirtschaftslehre mit Rechnungswesen'))) {
                $tblSubjectList[$tblSubject->getId()] = $tblSubject;
            }
            if (($tblSubject = Subject::useService()->getSubjectByName('Wirtschaftslehre/Recht'))) {
                $tblSubjectList[$tblSubject->getId()] = $tblSubject;
            }
        } elseif ($workField == 'Mathematisch-naturwissenschaftlich-technisches Aufgabenfeld') {
            if (($tblSubject = Subject::useService()->getSubjectByName('Agrartechnik mit Biologie'))) {
                $tblSubjectList[$tblSubject->getId()] = $tblSubject;
            }
            if (($tblSubject = Subject::useService()->getSubjectByName('Biologie'))) {
                $tblSubjectList[$tblSubject->getId()] = $tblSubject;
            }
            if (($tblSubject = Subject::useService()->getSubjectByName('Biotechnik'))) {
                $tblSubjectList[$tblSubject->getId()] = $tblSubject;
            }
            if (($tblSubject = Subject::useService()->getSubjectByName('Chemie'))) {
                $tblSubjectList[$tblSubject->getId()] = $tblSubject;
            }
            if (($tblSubject = Subject::useService()->getSubjectByName('Ernährungslehre mit Chemie'))) {
                $tblSubjectList[$tblSubject->getId()] = $tblSubject;
            }
            if (($tblSubject = Subject::useService()->getSubjectByName('Informatik'))) {
                $tblSubjectList[$tblSubject->getId()] = $tblSubject;
            }
            if (($tblSubject = Subject::useService()->getSubjectByName('Informatiksysteme'))) {
                $tblSubjectList[$tblSubject->getId()] = $tblSubject;
            }
            if (($tblSubject = Subject::useService()->getSubjectByName('Mathematik'))) {
                $tblSubjectList[$tblSubject->getId()] = $tblSubject;
            }
            if (($tblSubject = Subject::useService()->getSubjectByName('Physik'))) {
                $tblSubjectList[$tblSubject->getId()] = $tblSubject;
            }
            if (($tblSubject = Subject::useService()->getSubjectByName('Technik'))) {
                $tblSubjectList[$tblSubject->getId()] = $tblSubject;
            }
        } elseif ($workField == '') {
            if (($tblSubject = Subject::useService()->getSubjectByName('Sport'))) {
                $tblSubjectList[$tblSubject->getId()] = $tblSubject;
            }
            if (($tblSubject = Subject::useService()->getSubjectByName('Evangelische Religion'))) {
                $tblSubjectList[$tblSubject->getId()] = $tblSubject;
            }
            if (($tblSubject = Subject::useService()->getSubjectByName('Katholische Religion'))) {
                $tblSubjectList[$tblSubject->getId()] = $tblSubject;
            }
            if (($tblSubject = Subject::useService()->getSubjectByName('Ethik'))) {
                $tblSubjectList[$tblSubject->getId()] = $tblSubject;
            }
        }

        return $tblSubjectList;
    }

    /**
     * @param array $gradeList
     *
     * @return string
     */
    public static function getAverageTextByGradeList(array $gradeList): string
    {
        if ($gradeList) {
            $average = intval(round(array_sum($gradeList) / count($gradeList)));

            return self::getAverageText($average);
        }

        return '&ndash;';
    }

    /**
     * @param int $points
     * @return string
     */
    protected static function getAverageText(int $points): string
    {
        switch ($points) {
            case 15:
            case 14:
            case 13: return 'sehr gut';
            case 12:
            case 11:
            case 10: return 'gut';
            case 9:
            case 8:
            case 7: return 'befriedigend';
            case 6:
            case 5:
            case 4: return 'ausreichend';
            case 3:
            case 2:
            case 1: return 'mangelhaft';
            case 0: return 'ungenügend';
        }

        return '&ndash;';
    }
}