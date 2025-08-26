<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\KG;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Setting\Consumer\Consumer;

abstract class Style extends Certificate
{
    const COLOR = '#BCE2FF';
    const TEXT_SIZE = '13px';
    const PADDING_LEFT = '4px';

    const PADDING_TOP_GRADE = '3px';
    const PADDING_BOTTOM_GRADE = '2px';
    const MARGIN_TOP_SUBJECT_LINE = '14.75px';
    const MARGIN_TOP_GRADE_LINE = '9px';
    const SUBJECT_WIDTH = 28;
    const GRADE_WIDTH = 15;

    protected function getCustomHeader(string $title): Slice
    {
        return (new Slice())
            ->styleMarginTop('35px')
            ->addElement((new Element())
                ->setContent('Evangelisches Kreuzgymnasium')
                ->styleTextBold()
                ->styleAlignCenter()
                ->styleTextSize('22px')
            )
            ->addSection((new Section())
                ->addElementColumn($this->isSample()
                        ? (new Element\Sample())
                            ->styleAlignCenter()
                            ->stylePaddingTop('40px')
                            ->styleTextSize('30px')
                        : (new Element())
                    , '33%')
                ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/KG.jpg', 'auto', '105px'))
                    ->stylePaddingTop('10px')
                    ->styleAlignCenter()
                    , '34%')
                ->addElementColumn((new Element()))
            )
            ->addElement((new Element())
                ->setContent($title)
                ->styleTextBold()
                ->styleAlignCenter()
                ->styleTextSize('22px')
            )
            ->addElement((new Element())
                ->setContent('des Gymnasiums')
                ->styleTextBold()
                ->styleAlignCenter()
                ->styleTextSize('16px')
            );
    }

    /**
     * @param int $personId
     *
     * @return Slice
     */
    protected function getCustomDivisionYearStudent(int $personId): Slice
    {
        return (new Slice())
            ->styleMarginTop('20px')
            ->addSection((new Section())
                ->addElementColumn($this->getElement('Klasse'), '20%')
                ->addElementColumn($this->getElement('{{ Content.P' . $personId . '.Division.Data.Name }}'), '10%')
                ->addElementColumn((new Element())->setContent('&nbsp;'))
                ->addElementColumn($this->getElement('Schuljahr'), '15%')
                ->addElementColumn($this->getElement('{{ Content.P' . $personId . '.Division.Data.Year }}'), '14%')
            )
            ->addElement((new Element())->styleMarginTop('10px'))
            ->addSection((new Section())
                ->addElementColumn($this->getElement('Vor- und Zuname:', false), '17%')
                ->addElementColumn($this->getElement('{{ Content.P' . $personId . '.Person.Data.Name.First }} {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                    ->styleAlignCenter()
                )
                ->addElementColumn((new Element()), '17%')
            );
    }

    /**
     * @param int $personId
     *
     * @return Slice[]
     */
    public function getCustomRating(int $personId) : array
    {
        $tblSetting = Consumer::useService()->getSetting('Education', 'Certificate', 'Generator', 'IsDescriptionAsJustify');
        $slice = (new Slice())
            ->styleMarginTop('15px')
            ->styleHeight('0px')
            ->addElement($this->getElement('Einschätzung', false));

        $element = $this->getElement(
                '{% if(Content.P'.$personId.'.Input.Rating is not empty) %}
                    {{ Content.P'.$personId.'.Input.Rating }}
                {% else %}
                    ---
                {% endif %}',
                false
            )->styleMarginTop('5px')
            ->styleLineHeight('135%');

        if($tblSetting && $tblSetting->getValue()){
            $element->styleAlignJustify();
        }

        $slice->addElement($element);
        $sliceList[] = $slice;

        // linien ziehen
        $sliceList[] = $this->getLines('22px', 3);

        return $sliceList;
    }

    /**
     * @param int $personId
     *
     * @return Slice[]
     */
    public function getCustomTeamExtra(int $personId) : array
    {
        $sliceList[] = (new Slice())
            ->styleMarginTop('10px')
            ->styleHeight('0px')
            ->addElement($this->getElement('Arbeitsgemeinschaften')
                ->styleTextBold()
            )
            ->addElement($this->getElement(
                    '{% if(Content.P' . $personId . '.Input.TeamExtra is not empty) %}
                        {{ Content.P' . $personId . '.Input.TeamExtra|nl2br }}
                    {% else %}
                        ---
                    {% endif %}',
                    false
                )->styleMarginTop('5px')
                ->styleLineHeight('135%')
            );

        // linien ziehen
        $sliceList[] = $this->getLines('25px', 2);

        return $sliceList;
    }

    /**
     * @param int $personId
     * #
     * @return Slice[]
     */
    public function getCustomRemark(int $personId) : array
    {
        $tblSetting = Consumer::useService()->getSetting('Education', 'Certificate', 'Generator', 'IsDescriptionAsJustify');
        $slice = (new Slice())
            ->styleMarginTop('5px')
            ->styleHeight('0%')
            ->addElement($this->getElement('Bemerkungen')->styleTextBold());

        $element = $this->getElement(
                '{% if(Content.P' . $personId . '.Input.Remark is not empty) %}
                    {{ Content.P' . $personId . '.Input.Remark|nl2br }}
                {% else %}
                    &nbsp;
                {% endif %}',
                false
            )->styleMarginTop('5px')
            ->styleLineHeight('135%');
        if ($tblSetting && $tblSetting->getValue()) {
            $element->styleAlignJustify();
        }
        $sliceList[] =  $slice->addElement($element);

        // linien ziehen
        $sliceList[] = $this->getLines('25px', 2);

        return $sliceList;
    }

    /**
     * @param $personId
     * @param array $languagesWithStartLevel
     *
     * @return Slice
     */
    protected function getCustomSubjectLanes(
        $personId,
        array $languagesWithStartLevel = array()
    ): Slice {
        $SubjectSlice = (new Slice());

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($this->getCertificateEntity());
        $tblGradeList = $this->getGrade();

        $section = (new Section())
            ->addElementColumn($this->getElement('Leistungen in den einzelnen Fächern:', false)
                ->styleTextBold()
                ->styleMarginTop('5px')
                ->styleMarginBottom('0px')
            );
        $SubjectSlice->addSection($section);

        $profileName = '&nbsp;';
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

            // add SecondLanguageField
            $tblSecondForeignLanguage = false;
            if (!empty($languagesWithStartLevel)) {
                if (isset($languagesWithStartLevel['Lane']) && isset($languagesWithStartLevel['Rank'])) {
                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                    [$languagesWithStartLevel['Lane']]['SubjectAcronym'] = 'Empty';
                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                    [$languagesWithStartLevel['Lane']]['SubjectName'] = '&nbsp;';
                    if (($tblSubjectForeignLanguage = $this->getForeignLanguageSubject(2))) {
                        $tblSecondForeignLanguage = $tblSubjectForeignLanguage;
                        $SubjectStructure[$languagesWithStartLevel['Rank']]
                        [$languagesWithStartLevel['Lane']]['SubjectAcronym'] = $tblSubjectForeignLanguage->getAcronym();
                        $SubjectStructure[$languagesWithStartLevel['Rank']]
                        [$languagesWithStartLevel['Lane']]['SubjectName'] = $tblSubjectForeignLanguage->getName();
                    }
                }
            }

            // add Profile
            // Profil
            $tblSubjectProfile = $this->getProfilSubject();
            // 3. Fremdsprache
            $tblSubjectForeignThird = $this->getForeignLanguageSubject(3);
            if ($tblSubjectProfile) {
                $profileName = $tblSubjectProfile->getName();
                $SubjectStructure[4][1]['SubjectAcronym'] = $tblSubjectProfile->getAcronym();
                $SubjectStructure[4][1]['SubjectName'] = 'Profil';
            } elseif ($tblSubjectForeignThird) {
                $profileName = $tblSubjectForeignThird->getName();
                $SubjectStructure[4][1]['SubjectAcronym'] = $tblSubjectForeignThird->getAcronym();
                $SubjectStructure[4][1]['SubjectName'] = 'Profil';
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

            $hasAdditionalLine = array();

            $widthMiddleSpace = (100 - 2 * self::SUBJECT_WIDTH - 2 * self::GRADE_WIDTH) . '%';
            $count = 0;
            foreach ($SubjectStructure as $SubjectList) {
                $count++;
                // Sort Lane-Ranking (1,2...)
                ksort($SubjectList);

                $SubjectSection = (new Section());

                foreach ($SubjectList as $Lane => $Subject) {
                    // 1. Fremdsprache
                    if ($Subject['SubjectName'] == 'Englisch') {
                        $hasAdditionalLine['Lane'] = $Lane;
                        $hasAdditionalLine['Ranking'] = 1;
                        $hasAdditionalLine['SubjectAcronym'] = 'EN';
                    }

                    // 2. Fremdsprache ab Klassenstufe
                    if (isset($languagesWithStartLevel['Lane']) && isset($languagesWithStartLevel['Rank'])
                        && $languagesWithStartLevel['Lane'] == $Lane && $languagesWithStartLevel['Rank'] == $count
                    ) {
                        $hasAdditionalLine['Lane'] = $Lane;
                        $hasAdditionalLine['Ranking'] = 2;
                        $hasAdditionalLine['SubjectAcronym'] = $tblSecondForeignLanguage
                            ? $tblSecondForeignLanguage->getAcronym() : 'Empty';
                    }

                    // lange Fächernamen
                    $Subject['SubjectName'] = str_replace('/', ' / ',  $Subject['SubjectName']);
                    if (strlen($Subject['SubjectName']) > 20) {
                        $marginTop = '4px';
                        $lineHeight = '90%';
                    } else {
                        $marginTop = self::MARGIN_TOP_SUBJECT_LINE;
                        $lineHeight = '100%';
                    }

                    if ($Lane > 1) {
                        // Notenstufen Erklärung in der Mitte
                        if ($count == 1) {
                            $SubjectSection->addSliceColumn($this->getGradeDescriptionSlice(), $widthMiddleSpace);
                        } else {
                            $SubjectSection->addElementColumn((new Element()), $widthMiddleSpace);
                        }
                    }

                    if ($hasAdditionalLine && $Lane == $hasAdditionalLine['Lane']) {
                        $SubjectSection->addElementColumn($this->getElement($Subject['SubjectName'])
                            ->styleMarginBottom('0px')
                            ->styleMarginTop($marginTop)
                            ->styleLineHeight($lineHeight)
                            , self::SUBJECT_WIDTH . '%');
                    } else {
                        $SubjectSection->addElementColumn($this->getElement($Subject['SubjectName'])
                            ->styleMarginTop($marginTop)
                            ->styleLineHeight($lineHeight)
                            , self::SUBJECT_WIDTH . '%');
                    }

                    $SubjectSection->addElementColumn((new Element())
                        ->setContent(
                            '{% if(Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                {{ Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] }}
                            {% else %}
                                &ndash;
                            {% endif %}'
                        )
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleAlignCenter()
                        ->styleBackgroundColor(self::COLOR)
                        ->stylePaddingTop(self::PADDING_TOP_GRADE)
                        ->stylePaddingBottom(self::PADDING_BOTTOM_GRADE)
                        ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
                        , self::GRADE_WIDTH . '%');
                }

                if (count($SubjectList) == 1 && isset($SubjectList[1])) {
                    $SubjectSection->addElementColumn((new Element()), 'auto');
                }

                $SubjectSlice->addSection($SubjectSection);

                if ($hasAdditionalLine) {
                    $SubjectSection = (new Section());
                    if ($hasAdditionalLine['Lane'] == 2) {
                        $SubjectSection->addElementColumn((new Element()), 'auto');
                    }

                    $content = $hasAdditionalLine['Ranking'] . '. Fremdsprache';

                    $SubjectSection->addElementColumn((new Element())
                        ->setContent($content)
                        ->stylePaddingLeft(self::PADDING_LEFT)
                        ->stylePaddingTop('0px')
                        ->stylePaddingBottom('0px')
                        ->styleMarginTop('0px')
                        ->styleMarginBottom('0px')
                        ->styleTextSize('9px')
                        ->styleHeight('0%')
                        , self::SUBJECT_WIDTH . '%');

                    if ($hasAdditionalLine['Lane'] == 1) {
                        $SubjectSection->addElementColumn((new Element()), 'auto');
                    }

                    $hasAdditionalLine = array();

                    $SubjectSlice->addSection($SubjectSection);
                }
            }
        }


        $SubjectSlice->addElement((new Element())->styleMarginTop('5px'));
        $SubjectSlice->addSection((new Section())
            ->addElementColumn($this->getElement('Besuchtes Profil (Klassenstufe 8-10)'), '57%')
            ->addElementColumn($this->getElement($profileName))
        );

        return $SubjectSlice;
    }

    /**
     * @param int $personId
     *
     * @return Slice
     */
    public function getCustomAbsence(int $personId) : Slice
    {
        return (new Slice())
            ->styleMarginTop('4px')
            ->addSection((new Section())
                ->addElementColumn($this->getElement('Fehltage')->styleTextBold(), '30%')
                ->addElementColumn($this->getElement('entschuldigt:'), '20%')
                ->addElementColumn($this->getElement(
                    '{% if(Content.P' . $personId . '.Input.Missing is not empty) %}
                        {{ Content.P' . $personId . '.Input.Missing }}
                    {% else %}
                        &nbsp;
                    {% endif %}',
                ), '17%')
                ->addElementColumn($this->getElement('unentschuldigt:'), '20%')
                ->addElementColumn($this->getElement(
                    '{% if(Content.P' . $personId . '.Input.Bad.Missing is not empty) %}
                        {{ Content.P' . $personId . '.Input.Bad.Missing }}
                    {% else %}
                        &nbsp;
                    {% endif %}',
                ))
            );
    }

    /**
     * @param int $personId
     *
     * @return Slice
     */
    public function getCustomTransfer(int $personId) : Slice
    {
        return (new Slice())
            ->styleMarginTop('4px')
            ->styleHeight('0%')
            ->addSection((new Section())
                ->addElementColumn($this->getElement('Versetzungsvermerk:'), '40%')
                ->addElementColumn($this->getElement('&nbsp;', false))
            )
            ->addSection((new Section())
                ->addElementColumn($this->getElement(
                    '{% if(Content.P' . $personId . '.Input.Transfer) %}
                        {{ Content.P' . $personId . '.Input.Transfer }}.
                    {% else %}
                         &nbsp;
                    {% endif %}'
                ), '40%')
                ->addElementColumn($this->getElement('&nbsp;', false))
            );
    }

    /**
     * @param int $personId
     *
     * @return Slice
     */
    protected function getCustomDateLine(int $personId) : Slice
    {
        $leftWidth = '32%';
        $rightWidth = '32%';
        return (new Slice())
            ->styleMarginTop('18px')
            ->addSection((new Section())
                ->addElementColumn($this->getElement('{{ Content.P' . $personId . '.Company.Address.City.Name }}, {{ Content.P' . $personId . '.Input.Date }}'), $leftWidth)
                ->addElementColumn($this->getElement('&nbsp;', false))
                ->addElementColumn($this->getElement('&nbsp;'), $rightWidth)
            )
            ->addSection((new Section())
                ->addElementColumn($this->getElementSubText('Ort, Datum')
                    , $leftWidth)
                ->addElementColumn($this->getElement('&nbsp;', false))
                ->addElementColumn($this->getElementSubText('Zur Kenntnis genommen Erziehungsberechtigte(r)')
                    ->styleAlignCenter()
                    , $rightWidth)
            );
    }

    /**
     * @param int $personId
     * @param bool $isExtended
     * @param string $marginTop
     *
     * @return Slice
     */
    public function getCustomSignPart(int $personId, bool $isExtended, string $marginTop = '18px') : Slice
    {
        $leftWidth = '32%';
        $rightWidth = '32%';

        if ($isExtended) {
            $slice = (new Slice())
                ->styleMarginTop($marginTop)
                ->addSection((new Section())
                    ->addElementColumn($this->getElement('&nbsp;', false), $leftWidth)
                    ->addElementColumn($this->getElementSignPart('Dienstsiegel der Schule')
                        ->styleMarginTop('60px')
                    )
                    ->addSliceColumn((new Slice())
                        ->addElement($this->getElement('&nbsp;'))
                        ->addElement($this->getElementSignPart('
                        {% if(Content.P' . $personId . '.Headmaster.Description is not empty) %}
                            {{ Content.P' . $personId . '.Headmaster.Description }}
                        {% else %}
                            Schulleiter(in)
                        {% endif %}'
                        ))
                        ->addElement($this->getElementSignPart('
                        {% if(Content.P' . $personId . '.Headmaster.Name is not empty) %}
                            {{ Content.P' . $personId . '.Headmaster.Name }}
                        {% else %}
                            &nbsp;
                        {% endif %}'
                        ))
                        // ------------
                        ->addElement($this->getElement('&nbsp;')
                            ->styleMarginTop('18px')
                        )
                        ->addElement($this->getElementSignPart('
                        {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                            {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                        {% else %}
                            Schulleiter(in)
                        {% endif %}'
                        ))
                        ->addElement($this->getElementSignPart('
                        {% if(Content.P' . $personId . '.DivisionTeacher.Name is not empty) %}
                            {{ Content.P' . $personId . '.DivisionTeacher.Name }}
                        {% else %}
                            &nbsp;
                        {% endif %}'
                        ))
                        , $rightWidth)
                );
        } else {
            $slice = (new Slice())
                ->styleMarginTop('50px')
                ->addSection((new Section())
                    ->addElementColumn($this->getElement('&nbsp;', false))
                    ->addSliceColumn((new Slice())
                        ->styleMarginBottom('15px')
                        ->addElement($this->getElement('&nbsp;'))
                        ->addElement($this->getElementSignPart('
                            {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                                {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                            {% else %}
                                Schulleiter(in)
                            {% endif %}'
                        ))
                        ->addElement($this->getElementSignPart('
                            {% if(Content.P' . $personId . '.DivisionTeacher.Name is not empty) %}
                                {{ Content.P' . $personId . '.DivisionTeacher.Name }}
                            {% else %}
                                &nbsp;
                            {% endif %}'
                        ))
                        , $rightWidth)
                );
        }

        return $slice;
    }

    private function getElement(string $text, bool $isUnderline = true): Element
    {
        $element =  (new Element())
            ->setContent($text)
            ->styleTextSize(self::TEXT_SIZE)
            ->stylePaddingLeft(self::PADDING_LEFT);

        if ($isUnderline) {
            $element->styleBorderBottom('2px', self::COLOR);
        }

        return $element;
    }

    private function getElementSubText(string $text): Element
    {
        return (new Element())
            ->setContent($text)
            ->stylePaddingLeft(self::PADDING_LEFT)
            ->stylePaddingTop('0px')
            ->stylePaddingBottom('0px')
            ->styleMarginTop('0px')
            ->styleMarginBottom('0px')
            ->styleTextSize('9px');
    }

    private function getElementSignPart(string $text): Element
    {
        return (new Element())
            ->setContent($text)
            ->styleAlignCenter()
            ->stylePaddingTop('0px')
            ->stylePaddingBottom('0px')
            ->styleMarginTop('0px')
            ->styleMarginBottom('0px')
            ->styleTextSize('10px');
    }

    private function getLines(string $marginTop, int $lines): Slice
    {
        $sliceLines = (new Slice())
            ->styleMarginTop($marginTop);
        for ($i = 0; $i < $lines; $i++) {
            $sliceLines->addElement($this->getElement('&nbsp;'));
        }

        return $sliceLines;
    }

    private function getGradeDescriptionSlice(): Slice
    {
        return (new Slice())
            ->styleMarginTop('10px')
            ->styleHeight('0%')
            ->addElement($this->getGradeDescriptionLineElement('Notenstufen'))
            ->addElement($this->getGradeDescriptionLineElement('1'))
            ->addElement($this->getGradeDescriptionLineElement('sehr gut'))
            ->addElement($this->getGradeDescriptionLineElement('2'))
            ->addElement($this->getGradeDescriptionLineElement('gut'))
            ->addElement($this->getGradeDescriptionLineElement('3'))
            ->addElement($this->getGradeDescriptionLineElement('befriedigend'))
            ->addElement($this->getGradeDescriptionLineElement('4'))
            ->addElement($this->getGradeDescriptionLineElement('ausreichend'))
            ->addElement($this->getGradeDescriptionLineElement('5'))
            ->addElement($this->getGradeDescriptionLineElement('mangelhaft'))
            ->addElement($this->getGradeDescriptionLineElement('6'))
            ->addElement($this->getGradeDescriptionLineElement('ungenügend'));
    }

    private function getGradeDescriptionLineElement(string $text): Element
    {
        return (new Element())
            ->setContent($text)
            ->styleAlignCenter()
            ->styleTextSize('9px');
    }
}