<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Text\Repository\Underline;

abstract class BGymStyle extends Certificate
{
    // Element
    const BORDER_SIZE = '0.5px';
    const BORDER_COLOR = 'lightgray';
    const PADDING_BOTTOM = '3px';

    // Grades
    const BACKGROUND = self::BACKGROUND_GRADE_FIELD;
    const MARGIN_TOP_GRADE_LINE = '10px';
    const PADDING_TOP_GRADE = '3px';
    const PADDING_BOTTOM_GRADE = '3px';
    const SUBJECT_WIDTH = 37;
    const GRADE_WIDTH = 10;
    const TEXT_SIZE_SMALL = '14px';

    protected function getHeaderBGym(string $title): Slice
    {
        $slice = $this->getSchoolNameBGym();

        // Sample
        if($this->isSample()){
            $slice->addElement((new Element\Sample())->styleTextSize('30px'));
        } else {
            $slice->addElement((new Element())->setContent('&nbsp;')->styleTextSize('30px'));
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
            )
            ;

        return $slice;
    }

    private function getSchoolNameBGym(): Slice
    {
        if (($tblSetting = \SPHERE\Application\Setting\Consumer\Consumer::useService()->getSetting(
                'Education', 'Certificate', 'Prepare', 'IsSchoolExtendedNameDisplayed'))
            && $tblSetting->getValue()
        ) {
            $isSchoolExtendedNameDisplayed = true;
        } else {
            $isSchoolExtendedNameDisplayed = false;
        }
        if (($tblSetting = \SPHERE\Application\Setting\Consumer\Consumer::useService()->getSetting(
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
                ->styleMarginTop('25px')
            );
    }

    /**
     * @param $personId
     *
     * @return Slice
     */
    protected function getSubjectArea($personId): Slice
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
                ->styleMarginTop('15px')
            );
    }

    /**
     * @param $personId
     * @param string $period
     *
     * @return Slice
     */
    protected function getLevelYearStudent($personId, string $period): Slice
    {
        $slice = (new Slice())
            ->styleMarginTop('15px')
            ->addSection((new Section())
                ->addElementColumn(
                    $this->getElement('Klassenstufe {{ Content.P' . $personId . '.Division.Data.Level.Name }}')
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
                    ->styleMarginTop('15px')
                    ->styleMarginBottom('15px')
            )
            ->addSection((new Section())
                ->addElementColumn(
                    $this->getElement('geboren am 
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
                    $this->getElement('in
                        {% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthplace is not empty) %}
                            {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthplace }}
                        {% else %}
                            &nbsp;
                        {% endif %}'
                    )
                    , '35%')
            )
        ;

        return $slice;
    }

    private function getElement(string $content): Element
    {
        return (new Element())
            ->setContent($content)
            ->styleAlignCenter()
            ->styleBorderBottom(self::BORDER_SIZE, self::BORDER_COLOR)
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

    protected function getRemarkBGym($personId, bool $isMissing, string $height = '60px'): Slice
    {
        $slice = (new Slice());
        if ($isMissing) {
            $slice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent(new Underline('Bemerkungen:'))
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
            )
                ->styleMarginTop('15px');
        } else {
            $slice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Bemerkungen:'))
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
            ->styleMarginTop('20px')
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
                        {% if(Content.P'.$personId.'.Person.Data.Name.Salutation is not empty) %}
                            {{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
                        {% else %}
                            Frau/Herr
                        {% endif %}
                        {% if(Content.P' . $personId . '.Input.Transfer) %}
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
     *
     * @return Slice
     */
    protected function getSignPartBGym($personId): Slice
    {
        $slice = (new Slice())
            ->styleMarginTop('30px')
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
                    $this->getElement('{{ Content.P' . $personId . '.Input.Date }}')
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
                    $this->getElement('&nbsp;')->styleMarginTop('40px')
                    , '35%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                )
                ->addElementColumn(
                    $this->getElement('&nbsp;')->styleMarginTop('40px')
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
                    ->setContent('
                        {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                            {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                        {% else %}
                            Klassenlehrer(in)
                        {% endif %}'
                    )
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
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Zur Kenntnis genommen:')
                    ->styleMarginTop('30px')
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
            )
            ->addElement((new Element())
                ->setContent('NOTENSTUFEN: sehr gut (1), gut (2), befriedigend (3), ausreichend (4), mangelhaft (5), ungenügend (6)')
                ->styleTextSize('9px')
            )
            ;

        return $slice;
    }
}