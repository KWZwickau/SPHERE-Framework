<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\HOGA;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Setting\Consumer\Consumer;

abstract class Style extends Certificate
{
    const FONT_FAMILY = 'FreeSans';
    const BACKGROUND = '#CCC';
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
    protected function getHeader(array $school, string $title, bool $isSchoolLogoVisible = true) : Slice
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
            $section->addElementColumn((new Element()), '22%');
        }
        // Standard Logo
        $section->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
            $logoWidth, $logoHeight))
            ->styleAlignRight()
            , '39%');
        $slice->addSection($section);

        $slice->addSection($this->getSectionSpace('15px'));

        foreach ($school as $line) {
            $slice->addSection((new Section())
                ->addElementColumn(
                    $this->getElement($line, '15px')
                        ->styleAlignCenter()
                        ->styleMarginTop('-10px')
                )
            );
        }

        $slice->addSection((new Section())->addElementColumn(
            $this->getElement($title, '19px')
                ->styleTextBold()
                ->styleAlignCenter()
        ));

        return $slice;
    }

    /**
     * @param int $personId
     * @param string $period
     * @param string $marginTop
     *
     * @return Slice
     */
    protected function getDivisionYearStudent(int $personId, string $period = '1. Schulhalbjahr:', string $marginTop = '10px') : Slice
    {
        $paddingTop = '4.5px';
        return (new Slice())
            ->styleMarginTop($marginTop)
            ->addSection((new Section())
                ->addElementColumn($this->getElement('Klasse:')->stylePaddingTop($paddingTop), '20%')
                ->addElementColumn($this->getElement(
                    '{{ Content.P'.$personId.'.Division.Data.Level.Name }}{{ Content.P'.$personId.'.Division.Data.Name }}',
                    self::TEXT_SIZE_LARGE
                )->styleTextBold(), '20%')
                ->addElementColumn($this->getElement('&nbsp;'))
                ->addElementColumn($this->getElement($period)
                    ->stylePaddingTop($paddingTop)
                    ->styleAlignRight()
                    , '15%')
                ->addElementColumn($this->getElement('{{ Content.P'.$personId.'.Division.Data.Year }}', self::TEXT_SIZE_LARGE)
                    ->styleTextBold()
                    ->styleAlignCenter()
                    , '15%')
                ->addElementColumn($this->getElement('&nbsp;'), '10%')
            )
            ->addSection((new Section())
                ->addElementColumn($this->getElement('Vorname und Name:')->stylePaddingTop($paddingTop), '20%')
                ->addElementColumn($this->getElement('{{ Content.P'.$personId.'.Person.Data.Name.First }}
                    {{ Content.P'.$personId.'.Person.Data.Name.Last }}', self::TEXT_SIZE_LARGE)->styleTextBold())
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
    public function getCustomRating(int $personId, string $marginTop = '0px', string $height = '65px') : Slice
    {
        $tblSetting = Consumer::useService()->getSetting('Education', 'Certificate', 'Generator', 'IsDescriptionAsJustify');
        $slice = (new Slice())
            ->styleMarginTop($marginTop)
            ->styleHeight($height)
            ->addElement($this->getElement('Einschätzung:', self::TEXT_SIZE_NORMAL)->styleTextBold());

        $element = $this->getElement(
            '{% if(Content.P'.$personId.'.Input.Rating is not empty) %}
                {{ Content.P'.$personId.'.Input.Rating|nl2br }}
            {% else %}
                ---
            {% endif %}',
            self::TEXT_SIZE_SMALL
        );
        $element->styleLineHeight('80%');
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
    public function getCustomTeamExtra(int $personId, string $marginTop = '3px') : Slice
    {
        return (new Slice())
            ->styleMarginTop($marginTop)
            ->addElement($this->getElement(
                '<b>Teilnahme an zusätzlichen schulischen Veranstaltungen:</b>
                {% if(Content.P' . $personId . '.Input.TeamExtra is not empty) %}
                    {{ Content.P' . $personId . '.Input.TeamExtra|nl2br }}
                {% else %}
                    ---
                {% endif %}',
                self::TEXT_SIZE_NORMAL
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
            ->addElement($this->getElement('Bemerkungen:', self::TEXT_SIZE_NORMAL)->styleTextBold());

        $element = $this->getElement(
            '{% if(Content.P' . $personId . '.Input.Remark is not empty) %}
                {{ Content.P' . $personId . '.Input.Remark|nl2br }}
            {% else %}
                &nbsp;
            {% endif %}',
            self::TEXT_SIZE_SMALL
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
    protected function getCustomInfo(string $marginTop = '0px') : Slice
    {
        $textSize = '9.5px';

        return (new Slice())
            ->styleMarginTop($marginTop)
            ->addElement($this->getElement('Notenerläuterung:', $textSize))
            ->addElement($this->getElement('1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft; 6 = ungenügend',
                $textSize)->stylePaddingTop('-5px'));
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
                                    $tillLevel = $tblStudentSubject->getServiceTblLevelTill();
                                    $fromLevel = $tblStudentSubject->getServiceTblLevelFrom();
                                    if (($tblDivision = $this->getTblDivision())
                                        && ($tblLevel = $tblDivision->getTblLevel())
                                    ) {
                                        $levelName = $tblLevel->getName();
                                    } else {
                                        $levelName = false;
                                    }

                                    if ($tillLevel && $fromLevel) {
                                        if (floatval($fromLevel->getName()) <= floatval($levelName)
                                            && floatval($tillLevel->getName()) >= floatval($levelName)
                                        ) {
                                            $tblSecondForeignLanguageSecondarySchool = $tblSubjectForeignLanguage;
                                        }
                                    } elseif ($tillLevel) {
                                        if (floatval($tillLevel->getName()) >= floatval($levelName)) {
                                            $tblSecondForeignLanguageSecondarySchool = $tblSubjectForeignLanguage;
                                        }
                                    } elseif ($fromLevel) {
                                        if (floatval($fromLevel->getName()) <= floatval($levelName)) {
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

            // Abschlusszeugnis 2. Fremdsprache anfügen
            // todo
//            if ($hasSecondLanguageDiploma) {
//                // Zeiger auf letztes Element
//                end($SubjectStructure);
//                $lastItem = &$SubjectStructure[key($SubjectStructure)];
//                //
//                if (isset($lastItem[1])) {
//                    $SubjectStructure[][1] = $this->addSecondForeignLanguageDiploma($tblSecondForeignLanguageDiploma
//                        ? $tblSecondForeignLanguageDiploma : null);
//                } else {
//                    $lastItem[1] = $this->addSecondForeignLanguageDiploma($tblSecondForeignLanguageDiploma
//                        ? $tblSecondForeignLanguageDiploma : null);
//                }
//            }

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
                    if (strlen($Subject['SubjectName']) > 15) {
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
                            ->styleBorderBottom('0.5px', '#000')
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
}