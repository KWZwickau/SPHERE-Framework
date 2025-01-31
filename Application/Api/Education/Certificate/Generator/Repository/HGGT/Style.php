<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\HGGT;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;

abstract class Style extends Certificate
{
    const TEXT_SIZE = '12pt';
    const MARGIN_TOP_GRADE_LINE = '8px';
    const SUBJECT_WIDTH = 30;
    const GRADE_WIDTH = 4;
    const SPACE_WIDTH = 100  - 2 * (self::SUBJECT_WIDTH + self::GRADE_WIDTH);

    /**
     * @param $IsSample
     * @param string title
     *
     * @return Slice
     */
    protected function getCustomHeader($IsSample, string $title): Slice
    {
        $sample = $IsSample ? (new Element\Sample()) : (new Element())->setContent('&nbsp;');

        return (new Slice())
            ->stylePaddingTop('10px')
            ->addSection((new Section())
                ->addElementColumn(
                    (new Element\Image('/Common/Style/Resource/Logo/HGGT.jpg', 'auto', '105px'))->styleAlignLeft()
                , '25%')
                ->addSliceColumn((new Slice())
                    ->styleMarginTop('15px')
                    ->addSection((new Section())
                        ->addElementColumn($sample
                            ->styleTextSize('22pt')
                            ->styleAlignRight()
                        , '30%')
                        ->addElementColumn((new Element())
                            ->setContent($title)
                            ->styleTextSize('22pt')
                            ->styleAlignRight()
                            ->styleTextBold()
                        )
                    )
                    ->addElement((new Element())
                        ->setContent('Humanistisches Greifenstein Gymnasium Thum')
                        ->styleTextSize('14pt')
                        ->styleAlignRight()
                        ->styleTextBold()
                    )
                    ->addElement((new Element())
                        ->setContent('Staatlich anerkannte Ersatzschule in freier Trägerschaft')
                        ->styleTextSize('10pt')
                        ->styleAlignRight()
                        ->styleTextBold()
                    )
                )
            );
    }

    /**
     * @param string $textSize
     *
     * @return Element
     */
    private function getCustomBoldElement(string $textSize = self::TEXT_SIZE): Element
    {
        return (new Element())
            ->styleTextSize($textSize)
            ->styleTextBold();
    }

    /**
     * @param $personId
     * @param string $yearString
     *
     * @return Slice
     */
    protected function getCustomDivisionAndYear($personId, string $yearString): Slice
    {
        return (new Slice())
            ->styleMarginTop('35px')
            ->addSection((new Section())
                ->addElementColumn($this->getCustomBoldElement()
                    ->setContent('für')
                    , '20%')
                ->addElementColumn($this->getCustomBoldElement()
                    ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.Last }}, {{ Content.P' . $personId . '.Person.Data.Name.First }}')
                )
            )
            ->addElement((new Element())
                ->styleHeight('20px')
            )
            ->addSection((new Section())
                ->addElementColumn($this->getCustomBoldElement()
                    ->setContent('Klasse')
                    , '20%')
                ->addElementColumn($this->getCustomBoldElement()
                    ->setContent('{{ Content.P' . $personId . '.Division.Data.Name }}')
                    , '30%')
                ->addElementColumn($this->getCustomBoldElement()
                    ->setContent($yearString)
                    ->styleAlignRight()
                    , '36%')
                ->addElementColumn($this->getCustomBoldElement()
                    ->setContent('{{ Content.P' . $personId . '.Division.Data.Year }}')
                    ->styleAlignRight()
                )
            )
            ->addElement((new Element())
                ->setContent('&nbsp;')
                ->styleHeight('15px')
                ->styleBorderBottom()
            );

    }

    /**
     * @param $personId
     *
     * @return Slice
     */
    protected function getCustomRatingContent($personId): Slice
    {
        return (new Slice())
            ->styleMarginTop('10px')
            ->styleHeight('
                {% if(Content.P'.$personId.'.Input.Rating is not empty) %}
                    75px
                {% else %}
                    20px
                {% endif %}
            ')
            ->addElement($this->getCustomBoldElement()
                ->setContent('
                    {% if(Content.P'.$personId.'.Input.Rating is not empty) %}
                        Einschätzung:
                    {% else %}
                        &nbsp;
                    {% endif %}
                ')
            )
            ->addElement((new Element())
                ->setContent('
                    {% if(Content.P'.$personId.'.Input.Rating is not empty) %}
                        {{ Content.P'.$personId.'.Input.Rating|nl2br }}
                    {% else %}
                        &nbsp;
                    {% endif %}
                ')
                ->styleAlignJustify()
                ->styleTextSize('10pt')
            );
    }

    /**
     * @param $personId
     *
     * @return Slice
     */
    protected function getCustomGradeLanes($personId): Slice
    {
        $GradeSlice = (new Slice());

        $tblCertificateGradeAll = Generator::useService()->getCertificateGradeAll($this->getCertificateEntity());
        $GradeStructure = array();
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

                $GradeSection = (new Section());

                if (count($GradeList) == 1 && isset($GradeList[2])) {
                    $GradeSection->addElementColumn((new Element()), 'auto');
                }

                foreach ($GradeList as $Lane => $Grade) {

                    if ($Lane > 1) {
                        $GradeSection->addElementColumn((new Element())
                            , self::SPACE_WIDTH . '%');
                    }

                    $this->setGradeLine(
                        $GradeSection,
                        $Grade['GradeName'],
                        '{% if(Content.P' . $personId . '.Input["' . $Grade['GradeAcronym'] . '"] is not empty) %}
                             {{ Content.P' . $personId . '.Input["' . $Grade['GradeAcronym'] . '"] }}
                        {% else %}
                            &ndash;
                        {% endif %}',
                    );
                }

                if (count($GradeList) == 1 && isset($GradeList[1])) {
                    $GradeSection->addElementColumn((new Element()));
                }

                $GradeSlice->addSection($GradeSection)->styleMarginTop(self::MARGIN_TOP_GRADE_LINE);
            }
        }

        return $GradeSlice
            ->addElement((new Element())
                ->styleMarginTop('5px')
                ->setContent('Noten: 1 = sehr gut, 2 = gut, 3 = befriedigend, 4 = ausreichend, 5 = mangelhaft')
                ->styleTextSize('9pt')
            );
    }

    /**
     * @param $personId
     *
     * @return Slice
     */
    protected function getCustomSubjectLanes($personId): Slice
    {
        $SubjectSlice = (new Slice())
            ->styleMarginTop('20px')
            ->styleHeight('270px');

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($this->getCertificateEntity());
        $tblGradeList = $this->getGrade();

        $SectionList = array();

        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if ($tblSubject) {
                    // Grade Exists? => Add Subject to Certificate
                    if (isset($tblGradeList['Data'][$tblSubject->getAcronym()])) {
                        // 3. FS steht extra weiter unten
                        if (($tblForeignLanguage = $this->getForeignLanguageSubject(3)) && $tblForeignLanguage->getAcronym() == $tblSubject->getAcronym()) {
                            continue;
                        }

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

            foreach ($SubjectStructure as $SubjectList) {
                // Sort Lane-Ranking (1,2...)
                ksort($SubjectList);

                $SubjectSection = (new Section());

                if (count($SubjectList) == 1 && isset($SubjectList[2])) {
                    $SubjectSection->addElementColumn((new Element()), 'auto');
                }

                foreach ($SubjectList as $Lane => $Subject) {
                    if ($Lane > 1) {
                        $SubjectSection->addElementColumn((new Element())
                            , self::SPACE_WIDTH . '%');
                    }

                    $this->setGradeLine(
                        $SubjectSection,
                        $Subject['SubjectAcronym'] == 'GRW' ? 'G / R / W' : $Subject['SubjectName'],
                        '{% if(Content.P' . $personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] is not empty) %}
                             {{ Content.P' . $personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] }}
                        {% else %}
                            &ndash;
                        {% endif %}',
                    );

                }

                if (count($SubjectList) == 1 && isset($SubjectList[1])) {
                    $SubjectSection->addElementColumn((new Element()), 'auto');
                }
                $SectionList[] = $SubjectSection;
            }

            $SubjectSlice->addSectionList($SectionList);
        }

        if (($section = $this->getProfileLine($personId))) {
            $SubjectSlice->addSection($section);
        }

        return $SubjectSlice
            ->addElement((new Element())
                ->styleMarginTop('5px')
                ->setContent('Noten: 1 = sehr gut, 2 = gut, 3 = befriedigend, 4 = ausreichend, 5 = mangelhaft, 6 = ungenügend')
                ->styleTextSize('9pt')
            );
    }

    private function getProfileLine($personId): ?Section
    {
        // Profil
        $tblSubjectProfile = $this->getProfilSubject();
        // 3. Fremdsprache
        $tblSubjectForeign = $this->getForeignLanguageSubject(3);

        $section = new Section();
        $section->addElementColumn($this->getCustomBoldElement()
            ->setContent('Wahlpflichtbereich')
            ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
            , self::SUBJECT_WIDTH . '%');
        if ($tblSubjectForeign) {
            $section->addElementColumn($this->getCustomBoldElement()
                ->setContent('Sprachliches Profil')
                ->stylePaddingLeft('14px')
                ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
                , (self::GRADE_WIDTH + self::SPACE_WIDTH)  . '%');
            $this->setGradeLine(
                $section,
                $tblSubjectForeign->getName(),
                '{% if(Content.P' . $personId . '.Grade.Data["' . $tblSubjectForeign->getAcronym() . '"] is not empty) %}
                     {{ Content.P' . $personId . '.Grade.Data["' . $tblSubjectForeign->getAcronym() . '"] }}
                {% else %}
                    &ndash;
                {% endif %}'
            );

            return $section;
        } elseif ($tblSubjectProfile) {
            $section->addElementColumn($this->getCustomBoldElement()
                ->setContent($tblSubjectProfile->getName())
                ->stylePaddingLeft('14px')
                ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
                , (self::GRADE_WIDTH + self::SPACE_WIDTH + self::SUBJECT_WIDTH)  . '%');
            $section->addElementColumn($this->getCustomBoldElement()
                ->setContent(
                    '{% if(Content.P' . $personId . '.Grade.Data["' . $tblSubjectProfile->getAcronym() . '"] is not empty) %}
                         {{ Content.P' . $personId . '.Grade.Data["' . $tblSubjectProfile->getAcronym() . '"] }}
                    {% else %}
                        &ndash;
                    {% endif %}'
                )
                ->styleAlignRight()
                ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
                , self::GRADE_WIDTH . '%');

            return $section;
        } else {
            return null;
        }
    }

    /**
     * @param Section $section
     * @param string $subjectName
     * @param string $grade
     */
    protected function setGradeLine(Section $section, string $subjectName, string $grade): void
    {
        $section->addElementColumn($this->getCustomBoldElement()
                ->setContent($subjectName)
                ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
            , self::SUBJECT_WIDTH . '%');
        $section->addElementColumn($this->getCustomBoldElement()
                ->setContent($grade)
                ->styleAlignRight()
                ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
            , self::GRADE_WIDTH . '%');
    }

    /**
     * @param $personId
     *
     * @return Slice
     */
    protected function getCustomRemark($personId): Slice
    {
        return (new Slice())
            ->styleMarginTop('10px')
//            ->styleHeight('60px')
            ->addElement($this->getCustomBoldElement()
                ->setContent('
                    {% if(Content.P'.$personId.'.Input.RemarkWithoutTeam is not empty) %}
                        Bemerkung:
                    {% else %}
                        &nbsp;
                    {% endif %}
                ')
            )
            ->addElement((new Element())
                ->setContent('
                    {% if(Content.P'.$personId.'.Input.RemarkWithoutTeam is not empty) %}
                        {{ Content.P'.$personId.'.Input.RemarkWithoutTeam|nl2br }}
                    {% else %}
                        &nbsp;
                    {% endif %}
                ')
                ->styleAlignJustify()
                ->styleTextSize('10pt')
            );
    }

    /**
     * @param $personId
     *
     * @return Slice
     */
    protected function getCustomMissing($personId): Slice
    {
        return (new Slice())
            ->styleMarginTop('15px')
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Fehltage entschuldigt:')
                    ->styleTextSize(self::TEXT_SIZE)
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Input.Missing is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                    ->styleTextSize(self::TEXT_SIZE)
                    , 'auto')
                ->addElementColumn((new Element())
                    ->setContent('Fehltage unentschuldigt:')
                    ->styleTextSize(self::TEXT_SIZE)
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Input.Bad.Missing is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Bad.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                    ->styleTextSize(self::TEXT_SIZE)
                    ->styleAlignRight()
                    , '5%')
            );
    }

    /**
     * @param $personId
     * @param bool $isOptional
     *
     * @return Slice
     */
    protected function getCustomTransfer($personId, bool $isOptional = true): Slice
    {
        return (new Slice())
            ->styleMarginTop('15px')
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($isOptional
                        ? '{% if(Content.P' . $personId . '.Input.Transfer) %}
                            Versetzungsvermerk:
                        {% else %}
                            &nbsp;
                        {% endif %}'
                        : 'Versetzungsvermerk:'
                    )
                    ->styleTextSize(self::TEXT_SIZE)
                    , '26%')
                ->addElementColumn($this->getCustomBoldElement()
                    ->setContent('{% if(Content.P' . $personId . '.Input.Transfer) %}
                        {{ Content.P' . $personId . '.Input.Transfer }}.
                    {% else %}
                        &nbsp;
                    {% endif %}')
                )
            );
    }

    /**
     * @param $personId
     *
     * @return Slice
     */
    protected function getCustomDateLine($personId): Slice
    {
        return (new Slice())
            ->styleMarginTop('
                {% if(Content.P'.$personId.'.Input.Rating is not empty) %}
                    20px
                {% else %}
                    60px
                {% endif %}
            ')
            ->addElement((new Element())
                ->setContent('Thum, {{ Content.P' . $personId . '.Input.Date }}')
                ->styleTextSize(self::TEXT_SIZE)
            );
    }

    /**
     * @param $personId
     * @param bool $isExtended with directory and stamp
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getCustomSignPart($personId, bool $isExtended = true, string $MarginTop = '65px'): Slice
    {
        $SignSlice = (new Slice());
        if ($isExtended) {
            $SignSlice
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleAlignCenter()
                        ->styleBorderBottom()
                        , '30%')
                    ->addElementColumn((new Element())
                        , '40%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleAlignCenter()
                        ->styleBorderBottom()
                        , '30%')
                )
                ->styleMarginTop($MarginTop)
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if(Content.P' . $personId . '.Headmaster.Description is not empty) %}
                                {{ Content.P' . $personId . '.Headmaster.Description }}
                            {% else %}
                                Schulleiter/in
                            {% endif %}'
                        )
//                        ->styleAlignCenter()
                        ->styleTextSize('10pt')
                        , '30%')
                    ->addElementColumn((new Element())
                        , '40%')
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                                {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                            {% else %}
                                Klassenleiter/in
                            {% endif %}'
                        )
//                        ->styleAlignCenter()
                        ->styleTextSize('10pt')
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
                        ->styleTextSize('10pt')
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
                        ->styleTextSize('10pt')
                        ->stylePaddingTop('2px')
                        ->styleAlignCenter()
                        , '30%')
                );
        } else {
            $SignSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    , '70%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderBottom('1px', '#000')
                    , '30%')
            )
                ->styleMarginTop($MarginTop)
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '70%')
                    ->addElementColumn((new Element())
                        ->setContent('
                        {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                                {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                            {% else %}
                                Klassenleiter/in
                            {% endif %}
                        ')
//                        ->styleAlignCenter()
                        ->styleTextSize('10pt')
                        , '30%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '70%')
                    ->addElementColumn((new Element())
                        ->setContent(
                            '{% if(Content.P' . $personId . '.DivisionTeacher.Name is not empty) %}
                                {{ Content.P' . $personId . '.DivisionTeacher.Name }}
                            {% else %}
                                &nbsp;
                            {% endif %}'
                        )
                        ->styleTextSize('10pt')
                        ->stylePaddingTop('2px')
//                        ->styleAlignCenter()
                        , '30%')
                );
        }

        return $SignSlice;
    }

    /**
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getCustomParentSign(string $MarginTop = '35px'): Slice
    {
        return (new Slice())
            ->styleMarginTop($MarginTop)
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Zur Kenntnis genommen:')
                    ->styleTextSize(self::TEXT_SIZE)
                    , '32%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderBottom()
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    , '32%')
                ->addElementColumn((new Element())
                    ->setContent('Erziehungsberechtigte/r')
                    ->styleAlignCenter()
                    ->styleTextSize('10pt')
                )
            );
    }
}