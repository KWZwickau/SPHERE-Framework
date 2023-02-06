<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EZSH;

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
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;

/**
 * Class EzshStyle
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\EZSH
 */
abstract class EzshStyle extends Certificate
{

    const TEXT_SIZE = '10pt';
    const FONT_FAMILY = 'Calluna Sans';
    const FONT_FAMILY_BOLD = 'Calluna Sans Bold';
    const LINE_HEIGHT = '85%';

    /**
     * @param string $height
     *
     * @return Slice
     */
    public function getEZSHSample($height = '110px')
    {

        if ($this->isSample()) {
            $Header = (new Slice)->addSection((new Section())
                ->addElementColumn((new Element\Sample())
                    ->styleTextSize('30px')
                    ->stylePaddingTop('20px')
                    ->styleHeight($height)
                )
            );

        } else {
            $Header = (new Slice)->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleHeight($height)
                )
            );
        }
        return $Header;
    }

    /**
     * @param string $ContentHead
     * @param string $ContentSchool
     *
     * @return Section[]
     */
    public function getEZSHHeadLine($ContentHead = '', $ContentSchool = '')
    {

        $SectionList[] = (new Section())->addElementColumn((new Element())
            ->setContent($ContentHead)
            ->styleTextSize('21pt')
            ->styleTextBold()
            ->styleFontFamily(self::FONT_FAMILY_BOLD)
            ->styleLineHeight(self::LINE_HEIGHT)
        );
        $SectionList[] = (new Section())->addElementColumn((new Element())
            ->setContent($ContentSchool)
            ->styleTextSize('12pt')
            ->styleTextBold()
            ->stylePaddingTop()
            ->styleFontFamily(self::FONT_FAMILY_BOLD)
            ->styleLineHeight(self::LINE_HEIGHT)
        );

        return $SectionList;
    }

    /**
     * @param $personId
     *
     * @return Section
     */
    public function getEZSHName($personId)
    {

        $Section = new Section();
        $Section->addElementColumn((new Element())
            ->setContent('Name')
            ->styleFontFamily(self::FONT_FAMILY)
            ->styleLineHeight(self::LINE_HEIGHT)
            , '15%')
            ->addElementColumn((new Element())
                ->setContent('{{ Content.P'.$personId.'.Person.Data.Name.First }}
                              {{ Content.P'.$personId.'.Person.Data.Name.Last }}')
                ->styleBorderBottom('1px', '#BBB')
                ->stylePaddingLeft('7px')
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleLineHeight(self::LINE_HEIGHT)
                , '66%')
            ->addElementColumn((new Element())
                , '19%');
        return $Section;
    }

    /**
     * @param int    $personId
     * @param string $YearString
     *
     * @return Section
     */
    public function getEZSHDivisionAndYear($personId, $YearString = 'Schuljahr')
    {

        $Section = (new Section());
        $Section
            ->addElementColumn((new Element())
                ->setContent($YearString)
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleLineHeight(self::LINE_HEIGHT)
                , '15%')
            ->addElementColumn((new Element())
                ->setContent('{{ Content.P'.$personId.'.Division.Data.Year }}')
                ->styleBorderBottom('1px', '#BBB')
                ->styleAlignCenter()
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleLineHeight(self::LINE_HEIGHT)
                , '12%')
            ->addElementColumn((new Element())
                , '25%')
            ->addElementColumn((new Element())
                ->setContent('Klasse')
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleLineHeight(self::LINE_HEIGHT)
                , '8%')
            ->addElementColumn((new Element())
                ->setContent('{{ Content.P'.$personId.'.Division.Data.Level.Name }}')
                ->styleBorderBottom('1px', '#BBB')
                ->styleAlignCenter()
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleLineHeight(self::LINE_HEIGHT)
                , '21%')
            ->addElementColumn((new Element())
                , '19%');

        return $Section;
    }

    /**
     * @param $personId
     *
     * @return Section
     */
    public function getEZSHExtendedName($personId)
    {

        $Section = new Section();
        $Section->addElementColumn((new Element())
            ->setContent('Beiblatt zum Zeugnis für:')
            ->styleFontFamily(self::FONT_FAMILY)
            ->styleLineHeight(self::LINE_HEIGHT)
            , '24%')
            ->addElementColumn((new Element())
                ->setContent('{{ Content.P'.$personId.'.Person.Data.Name.First }}
                              {{ Content.P'.$personId.'.Person.Data.Name.Last }}')
                ->styleAlignCenter()
                ->styleBorderBottom()
                ->stylePaddingRight('120px')
                ->stylePaddingLeft('7px')
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleLineHeight(self::LINE_HEIGHT)
                , '76%');
        return $Section;
    }

    /**
     * @param $personId
     *
     * @return Section
     */
    public function getEZSHNameExtraPaper($personId)
    {

        $Section = new Section();
        $Section->addElementColumn((new Element())
            ->setContent('Beiblatt zum Zeugnis für:')
            ->styleFontFamily(self::FONT_FAMILY)
            ->styleLineHeight(self::LINE_HEIGHT)
            , '21%')
            ->addElementColumn((new Element())
                ->setContent('{{ Content.P'.$personId.'.Person.Data.Name.First }}
                              {{ Content.P'.$personId.'.Person.Data.Name.Last }}')
                ->styleBorderBottom()
                ->styleAlignCenter()
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleLineHeight(self::LINE_HEIGHT)
                , '79%');
        return $Section;
    }

    /**
     * @param $personId
     *
     * @return Slice
     */
    protected function getEZSHHeadGrade($personId)
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
                            , '4%');
                    }
                    $GradeSection->addElementColumn((new Element())
                        ->setContent($Grade['GradeName'])
                        ->stylePaddingTop()
                        ->styleMarginTop('10px')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleLineHeight(self::LINE_HEIGHT)
                        , '39%');
                    $GradeSection->addElementColumn((new Element())
                        ->setContent('{% if(Content.P'.$personId.'.Input["'.$Grade['GradeAcronym'].'"] is not empty) %}
                                         {{ Content.P'.$personId.'.Input["'.$Grade['GradeAcronym'].'"] }}
                                     {% else %}
                                         &ndash;
                                     {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->styleMarginTop('10px')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleLineHeight(self::LINE_HEIGHT)
                        , '9%');
                }

                if (count($GradeList) == 1 && isset($GradeList[1])) {
                    $GradeSection->addElementColumn((new Element()), '52%');
                }

                $GradeSlice->addSection($GradeSection);
            }
        }

        return $GradeSlice;
    }

    /**
     * @param $personId
     * @param bool|true $isSlice
     * @param array $languagesWithStartLevel
     * @param bool $isTitle
     * @param bool $showThirdForeignLanguage
     * @param bool $setTitle
     *
     * @return Section[]|Slice
     */
    protected function getEZSHSubjectLanes(
        $personId,
        $isSlice = true,
        $languagesWithStartLevel = array(),
        $isTitle = true,
        $showThirdForeignLanguage = false,
        $setTitle = true,
        $hasSecondLanguageSecondarySchool = false
    ) {

        $tblPerson = Person::useService()->getPersonById($personId);

        $SubjectSlice = (new Slice());

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

            // add SecondLanguageField, Fach wird aus der Schüleraktte des Schülers ermittelt
            $tblSecondForeignLanguage = false;
            $tblThirdForeignLanguage = false;
            $tblSecondForeignLanguageSecondarySchool = false;
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
                                    && ($tblSubjectForeignLanguage = $tblStudentSubject->getServiceTblSubject())
                                ) {
                                    if ($tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier() == '2') {
                                        $tblSecondForeignLanguage = $tblSubjectForeignLanguage;
                                    } elseif ($tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier() == '3') {
                                        $tblThirdForeignLanguage = $tblSubjectForeignLanguage;
                                    }
                                }
                            }

                            if ($tblThirdForeignLanguage && $showThirdForeignLanguage) {
                                $tblSecondForeignLanguage = $tblThirdForeignLanguage;
                            }
                            if ($tblSecondForeignLanguage) {
                                $SubjectStructure[$languagesWithStartLevel['Rank']]
                                [$languagesWithStartLevel['Lane']]['SubjectAcronym'] = $tblSecondForeignLanguage->getAcronym();
                                $SubjectStructure[$languagesWithStartLevel['Rank']]
                                [$languagesWithStartLevel['Lane']]['SubjectName'] = $tblSecondForeignLanguage->getName();
                            }
                        }
                    }
                }
            } else {
                if ($hasSecondLanguageSecondarySchool
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
//                                if ($hasSecondLanguageDiploma) {
//                                    $tblSecondForeignLanguageDiploma = $tblSubjectForeignLanguage;
//                                }

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

            // headline for grades
            if ($setTitle) {
                $HeaderSection = (new Section());
                $HeaderSection->addElementColumn((new Element())
                    ->setContent('LEISTUNGEN in den einzelnen Fächern')
                    ->styleTextSize('10pt')
                    ->styleTextBold()
                    ->stylePaddingTop('10px')
                    ->stylePaddingBottom(($isTitle ? '0px' : '26px'))
                    ->styleFontFamily(self::FONT_FAMILY_BOLD)
                    ->styleLineHeight(self::LINE_HEIGHT)
                );
                $SectionList[] = $HeaderSection;
                $SubjectSlice->addSection($HeaderSection);
            }
            if($isTitle){
                $HeaderSectionTwo = (new Section());
                $HeaderSectionTwo->addElementColumn((new Element())
                    ->setContent('Pflichtbereich:')
                    ->styleTextSize('10pt')
                    ->styleTextBold()
                    ->stylePaddingTop('10px')
                    ->styleFontFamily(self::FONT_FAMILY_BOLD)
                    ->styleLineHeight(self::LINE_HEIGHT)
                );
                $SectionList[] = $HeaderSectionTwo;
                $SubjectSlice->addSection($HeaderSectionTwo);
            }

            $hasAdditionalLine = false;
            $isShrinkMarginTop = false;

            $subjectWidth = 30;
            $gradeWidth = 17;
            $TextSize = '14px';
            $TextSizeSmall = '8.5px';
            $paddingTopShrinking = '4px';
            $paddingBottomShrinking = '4px';

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

                    if ($Lane > 1) {
                        $SubjectSection->addElementColumn((new Element())
                            , '6%');
                    }
                    if ($hasAdditionalLine && $Lane == $hasAdditionalLine['Lane']) {
                        $SubjectSection->addElementColumn((new Element())
                            ->setContent($Subject['SubjectName'])
                            ->stylePaddingTop()
                            ->stylePaddingBottom('0px')
                            ->styleMarginBottom('0px')
                            ->styleBorderBottom('1px', '#BBB')
                            ->styleMarginTop('10px')
                            ->styleTextSize($TextSize)
                            ->styleFontFamily(self::FONT_FAMILY)
                            ->styleLineHeight(self::LINE_HEIGHT)
                            , (string)($subjectWidth - 2) . '%');
                        $SubjectSection->addElementColumn((new Element()), '2%');
                    } elseif (strlen($Subject['SubjectName']) > 27) {
                        $SubjectSection->addElementColumn((new Element())
                            ->setContent($Subject['SubjectName'])
                            ->stylePaddingTop()
                            ->styleMarginTop('5px')
                            ->styleTextSize($TextSize)
                            ->styleFontFamily(self::FONT_FAMILY)
                            ->styleLineHeight(self::LINE_HEIGHT)
                            , (string)$subjectWidth . '%');
                    }elseif ($isShrinkMarginTop) {
                        $SubjectSection->addElementColumn((new Element())
                            ->setContent($Subject['SubjectName'])
                            ->stylePaddingTop()
                            ->styleMarginTop('0px')
                            ->styleTextSize($TextSize)
                            ->styleFontFamily(self::FONT_FAMILY)
                            ->styleLineHeight(self::LINE_HEIGHT)
                            , (string)$subjectWidth . '%');
                    } else {
                        $SubjectSection->addElementColumn((new Element())
                            ->setContent($Subject['SubjectName'])
                            ->stylePaddingTop()
                            ->styleMarginTop('10px')
                            ->styleTextSize($TextSize)
                            ->styleFontFamily(self::FONT_FAMILY)
                            ->styleLineHeight(self::LINE_HEIGHT)
                            , (string)$subjectWidth . '%');
                    }

                    $SubjectSection->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                             {{ Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] }}
                                         {% else %}
                                             &ndash;
                                         {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#E6E6E6')
                        ->styleMarginTop('12px')
                        ->stylePaddingTop(
                            '{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                ' . $paddingTopShrinking . ' 
                             {% else %}
                                 4px
                             {% endif %}'
                        )
                        ->stylePaddingBottom(
                            '{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                               ' . $paddingBottomShrinking . ' 
                             {% else %}
                                 4px
                             {% endif %}'
                        )
                        ->styleMarginTop($isShrinkMarginTop ? '0px' : '10px')
                        ->styleTextSize(
                            '{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 ' . $TextSizeSmall . '
                             {% else %}
                                 ' . $TextSize . '
                             {% endif %}'
                        )
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleLineHeight(self::LINE_HEIGHT)
                        , (string)$gradeWidth . '%');

                    if ($isShrinkMarginTop && $Lane == 2) {
                        $isShrinkMarginTop = false;
                    }
                }

                if (count($SubjectList) == 1 && isset($SubjectList[1])) {
                    $SubjectSection->addElementColumn((new Element()), '53%');
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
                               &nbsp;)
                            {% endif %}';

                    $SubjectSection->addElementColumn((new Element())
                        ->setContent($content)
                        ->stylePaddingTop('0px')
                        ->stylePaddingBottom('0px')
                        ->styleMarginTop('-6px')
                        ->styleMarginBottom('0px')
                        ->styleTextSize('9px')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleLineHeight(self::LINE_HEIGHT)
                        , (string)$subjectWidth . '%');

                    if ($hasAdditionalLine['Lane'] == 1) {
                        $SubjectSection->addElementColumn((new Element()), '53%');
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
     * @param $personId
     * @param string $TextSize
     * @param bool $isProfile
     *
     * @return Slice
     */
    public function getEZSHObligation($personId, $TextSize = '14px', $isProfile = false)
    {

        $tblPerson = Person::useService()->getPersonById($personId);

        $marginTop = '3px';

        $slice = new Slice();
        $sectionList = array();

        $elementObligationName = false;
        $elementObligationGrade = false;
        $elementForeignLanguageName = false;
        $elementForeignLanguageGrade = false;

        $subjectWidth = 83;
        $gradeWidth = 17;
        $paddingTopShrinking = '6px';
        $paddingBottomShrinking = '6px';

        // Zeugnisnoten im Wortlaut auf Abschlusszeugnissen --> breiter Zensurenfelder
        if (($tblCertificate = $this->getCertificateEntity())
            && ($tblCertificateType = $tblCertificate->getTblCertificateType())
            && ($tblCertificateType->getIdentifier() == 'DIPLOMA')
            && ($tblSetting = \SPHERE\Application\Setting\Consumer\Consumer::useService()->getSetting(
                'Education', 'Certificate', 'Prepare', 'IsGradeVerbalOnDiploma'))
            && $tblSetting->getValue()
        ) {
            $TextSizeSmall = '13px';
        } else {
            $TextSizeSmall = '11px';
        }

        if ($tblPerson
            && ($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
        ) {
            $Level = 'false';
            if ($isProfile) {
                // Profil
                if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE'))
                    && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                        $tblStudentSubjectType))
                ) {
                    /** @var TblStudentSubject $tblStudentSubject */
                    $tblStudentSubject = current($tblStudentSubjectList);
                    if (($tblSubjectProfile = $tblStudentSubject->getServiceTblSubject())) {
                        $tblSubject = $tblSubjectProfile;

                        if (($tblSetting = Consumer::useService()->getSetting('Api', 'Education', 'Certificate', 'ProfileAcronym'))
                            && ($value = $tblSetting->getValue())
                        ) {
                            $subjectAcronymForGrade = $value;
                        } else {
                            $subjectAcronymForGrade = $tblSubject->getAcronym();
                        }

                        $elementObligationName = new Element();
                        $elementObligationName
                            ->setContent('
                            {% if(Content.P' . $personId . '.Student.ProfileEZSH["' . $tblSubject->getAcronym() . '"] is not empty) %}
                                 {{ Content.P' . $personId . '.Student.ProfileEZSH["' . $tblSubject->getAcronym() . '"].Name' . ' }}
                            {% else %}
                                 &nbsp;
                            {% endif %}')
                            ->stylePaddingTop('0px')
                            ->stylePaddingBottom('0px')
                            ->styleMarginTop('7px')
                            ->styleTextSize($TextSize)
                            ->styleFontFamily(self::FONT_FAMILY)
                            ->styleLineHeight(self::LINE_HEIGHT);

                        $elementObligationGrade = new Element();
                        $elementObligationGrade
                            ->setContent('
                            {% if(Content.P' . $personId . '.Grade.Data["' . $subjectAcronymForGrade . '"] is not empty) %}
                                {{ Content.P' . $personId . '.Grade.Data["' . $subjectAcronymForGrade . '"] }}
                            {% else %}
                                &ndash;
                            {% endif %}')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#E6E6E6')
                            ->stylePaddingTop(
                                '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $subjectAcronymForGrade . '"] is not empty) %}
                                 ' . $paddingTopShrinking . ' 
                             {% else %}
                                 4px
                             {% endif %}'
                            )
                            ->stylePaddingBottom(
                                '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $subjectAcronymForGrade . '"] is not empty) %}
                                  ' . $paddingBottomShrinking . ' 
                             {% else %}
                                 4px
                             {% endif %}'
                            )
                            ->styleTextSize(
                                '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $subjectAcronymForGrade . '"] is not empty) %}
                                 ' . $TextSizeSmall . '
                             {% else %}
                                 ' . $TextSize . '
                             {% endif %}'
                            )
                            ->styleMarginTop($marginTop)
                            ->styleFontFamily(self::FONT_FAMILY)
                            ->styleLineHeight(self::LINE_HEIGHT);
                    }
                }
            } else {
                // Neigungskurs
//                if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'))
//                    && ($tblSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
//                        $tblStudentSubjectType))
//                ) {
//                    /** @var TblStudentSubject $tblStudentSubject */
//                    $tblStudentSubject = current($tblSubjectList);
//                    if (($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
//
//                        if (($tblSetting = Consumer::useService()->getSetting('Api', 'Education', 'Certificate',
//                                'OrientationAcronym'))
//                            && ($value = $tblSetting->getValue())
//                        ) {
//                            $subjectAcronymForGrade = $value;
//                        } else {
//                            $subjectAcronymForGrade = $tblSubject->getAcronym();
//                        }
//
//                        $elementObligationName = new Element();
//                        $elementObligationName
//                            ->setContent('
//                            {% if(Content.P' . $personId . '.Student.Orientation["' . $tblSubject->getAcronym() . '"] is not empty) %}
//                                 {{ Content.P' . $personId . '.Student.Orientation["' . $tblSubject->getAcronym() . '"].Name' . ' }}
//                            {% else %}
//                                 &nbsp;
//                            {% endif %}')
//                            ->stylePaddingTop('0px')
//                            ->stylePaddingBottom('0px')
//                            ->styleMarginTop('7px')
//                            ->styleTextSize($TextSize)
//                            ->styleFontFamily(self::FONT_FAMILY)
//                            ->styleLineHeight(self::LINE_HEIGHT);
//
//                        $elementObligationGrade = new Element();
//                        $elementObligationGrade
//                            ->setContent('
//                            {% if(Content.P' . $personId . '.Grade.Data["' . $subjectAcronymForGrade . '"] is not empty) %}
//                                {{ Content.P' . $personId . '.Grade.Data["' . $subjectAcronymForGrade . '"] }}
//                            {% else %}
//                                &ndash;
//                            {% endif %}')
//                            ->styleAlignCenter()
//                            ->styleBackgroundColor('#E6E6E6')
//                            ->stylePaddingTop(
//                                '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $subjectAcronymForGrade . '"] is not empty) %}
//                                 ' . $paddingTopShrinking . '
//                             {% else %}
//                                 4px
//                             {% endif %}'
//                            )
//                            ->stylePaddingBottom(
//                                '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $subjectAcronymForGrade . '"] is not empty) %}
//                                  ' . $paddingBottomShrinking . '
//                             {% else %}
//                                 4px
//                             {% endif %}'
//                            )
//                            ->styleTextSize(
//                                '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $subjectAcronymForGrade . '"] is not empty) %}
//                                 ' . $TextSizeSmall . '
//                             {% else %}
//                                 ' . $TextSize . '
//                             {% endif %}'
//                            )
//                            ->styleMarginTop($marginTop)
//                            ->styleFontFamily(self::FONT_FAMILY)
//                            ->styleLineHeight(self::LINE_HEIGHT);
//                    }
//                }

                // 2. Fremdsprache
                if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
                    && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                        $tblStudentSubjectType))
                ) {
                    /** @var TblStudentSubject $tblStudentSubject */
                    foreach ($tblStudentSubjectList as $tblStudentSubject) {
                        if ($tblStudentSubject->getTblStudentSubjectRanking()
                            && $tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier() == '2'
                            && ($tblSubject = $tblStudentSubject->getServiceTblSubject())
                        ) {

                            if (($tblLevelFrom = $tblStudentSubject->getServiceTblLevelFrom())) {
                                $Level = $tblLevelFrom->getName();
                                if (!$Level) {
                                    $Level = 'false';
                                }
                            }

                            $elementForeignLanguageName = new Element();
                            $elementForeignLanguageName
                                ->setContent('
                            {% if(Content.P' . $personId . '.Student.ForeignLanguage["' . $tblSubject->getAcronym() . '"] is not empty) %}
                                 {{ Content.P' . $personId . '.Student.ForeignLanguage["' . $tblSubject->getAcronym() . '"].Name' . ' }}
                            {% else %}
                                 &nbsp;
                            {% endif %}')
                                ->stylePaddingTop('0px')
                                ->stylePaddingBottom('0px')
                                ->styleMarginTop('7px')
                                ->styleTextSize($TextSize)
                                ->styleFontFamily(self::FONT_FAMILY)
                                ->styleLineHeight(self::LINE_HEIGHT);

                            $elementForeignLanguageGrade = new Element();
                            $elementForeignLanguageGrade
                                ->setContent('
                            {% if(Content.P' . $personId . '.Grade.Data["' . $tblSubject->getAcronym() . '"] is not empty) %}
                                {{ Content.P' . $personId . '.Grade.Data["' . $tblSubject->getAcronym() . '"] }}
                            {% else %}
                                &ndash;
                            {% endif %}')
                                ->styleAlignCenter()
                                ->styleBackgroundColor('#E6E6E6')
                                ->stylePaddingTop(
                                    '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $tblSubject->getAcronym() . '"] is not empty) %}
                                 ' . $paddingTopShrinking . ' 
                             {% else %}
                                 4px
                             {% endif %}'
                                )
                                ->stylePaddingBottom(
                                    '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $tblSubject->getAcronym() . '"] is not empty) %}
                                  ' . $paddingBottomShrinking . ' 
                             {% else %}
                                 4px
                             {% endif %}'
                                )
                                ->styleTextSize(
                                    '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $tblSubject->getAcronym() . '"] is not empty) %}
                                 ' . $TextSizeSmall . '
                             {% else %}
                                 ' . $TextSize . '
                             {% endif %}'
                                )
                                ->styleMarginTop($marginTop)
                                ->styleFontFamily(self::FONT_FAMILY)
                                ->styleLineHeight(self::LINE_HEIGHT);
                        }
                    }
                }
            }

            if($isProfile){
                $section = new Section();
                $section
                    ->addElementColumn((new Element())
                        ->setContent('Schulspezifisches Profil ab Klasse 8:')
                        ->styleTextBold()
                        ->styleMarginTop('10px')
                        ->styleTextSize($TextSize)
                        ->styleFontFamily(self::FONT_FAMILY_BOLD)
                        ->styleLineHeight(self::LINE_HEIGHT)
                    );
                $sectionList[] = $section;
            }

            if ($elementObligationName) {
                $section = new Section();
                $section
                    ->addElementColumn($elementObligationName, (string)$subjectWidth.'%')
                    ->addElementColumn($elementObligationGrade, (string)$gradeWidth.'%');
                $sectionList[] = $section;

                $section = new Section();
                if($isProfile) {
                    $section
                        ->addElementColumn((new Element())
                            ->styleBorderTop('1px', '#BBB')
                            ->stylePaddingTop()
                            ->stylePaddingBottom('10px')
                            ->styleFontFamily(self::FONT_FAMILY)
                            ->styleLineHeight(self::LINE_HEIGHT)
                            , (string)($subjectWidth - 2) . '%')
                        ->addElementColumn((new Element()), (string)($gradeWidth + 2) . '%');
                    $sectionList[] = $section;
                } else {
                    $section
                        ->addElementColumn((new Element())
//                            ->setContent('2. Fremdsprache ab Klasse 6')
                            ->setContent('2. Fremdsprache (abschlussorientiert)')
                            ->styleBorderTop('1px', '#BBB')
                            ->styleMarginTop('0px')
                            ->stylePaddingTop()
                            ->styleTextSize('13px')
                            ->styleFontFamily(self::FONT_FAMILY)
                            ->styleLineHeight(self::LINE_HEIGHT)
                            , (string)($subjectWidth - 2) . '%')
                        ->addElementColumn((new Element()), (string)($gradeWidth + 2) . '%');
                    $sectionList[] = $section;

                    // Unterstreichung NK
                    $section = new Section();
                    $section
                        ->addElementColumn((new Element()), '27%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->stylePaddingTop('0px')
                            ->stylePaddingBottom('0px')
                            ->styleTextSize('1px')
                            ->styleBorderTop('0.5px')
                            , '23%')
                        ->addElementColumn((new Element()));
                    $sectionList[] = $section;
                }
            } elseif ($elementForeignLanguageName) {
                $section = new Section();
                $section
                    ->addElementColumn($elementForeignLanguageName, (string)$subjectWidth.'%')
                    ->addElementColumn($elementForeignLanguageGrade, (string)$gradeWidth.'%');
                $sectionList[] = $section;


                $section = new Section();
                //Wahlfach Fremdsprache finden
                if($isProfile) {
                    $section
                        ->addElementColumn((new Element())
                            ->styleBorderTop('1px', '#BBB')
                            ->stylePaddingTop()
                            ->stylePaddingBottom('10px')
                            ->styleFontFamily(self::FONT_FAMILY)
                            ->styleLineHeight(self::LINE_HEIGHT)
                            , (string)($subjectWidth - 2) . '%')
                        ->addElementColumn((new Element()), (string)($gradeWidth + 2) . '%');
                    $sectionList[] = $section;
                } else {
                    $section
                        ->addElementColumn((new Element())
//                            ->setContent('2. Fremdsprache ab Klasse
//                                {% if ' . $Level . ' == false %}
//                                    6
//                                {% else %}
//                                    ' . $Level . '
//                                {% endif %}
//                            ')
                            ->setContent('2. Fremdsprache (abschlussorientiert)')
                            ->styleBorderTop('1px', '#BBB')
                            ->stylePaddingTop()
                            ->styleTextSize('13px')
                            ->styleFontFamily(self::FONT_FAMILY)
                            ->styleLineHeight(self::LINE_HEIGHT)
                            , (string)($subjectWidth - 2) . '%')
                        ->addElementColumn((new Element()), (string)($gradeWidth + 2) . '%');
                    $sectionList[] = $section;

                    // Unterstreichung 2. FS
//                    $section = new Section();
//                    $section
//                        ->addElementColumn((new Element())
//                            ->setContent('&nbsp;')
//                            ->stylePaddingTop('0px')
//                            ->stylePaddingBottom('0px')
//                            ->styleTextSize('1px')
//                            ->styleBorderTop('0.5px')
//                            , '25%')
//                        ->addElementColumn((new Element()));
//                    $sectionList[] = $section;
                }
            } else {
                $elementName = (new Element())
                    ->setContent('---')
                    ->styleMarginTop($marginTop)
                    ->styleTextSize($TextSize)
                    ->styleFontFamily(self::FONT_FAMILY)
                    ->styleLineHeight(self::LINE_HEIGHT);

                $elementGrade = (new Element())
                    ->setContent('&ndash;')
                    ->styleAlignCenter()
                    ->styleBackgroundColor('#E6E6E6')
                    ->stylePaddingTop('4px')
                    ->stylePaddingBottom('4px')
                    ->styleTextSize($TextSize)
                    ->styleMarginTop($marginTop)
                    ->styleFontFamily(self::FONT_FAMILY)
                    ->styleLineHeight(self::LINE_HEIGHT);

                $section = new Section();
                $section
                    ->addElementColumn($elementName
                        , '82%')
                    ->addElementColumn((new Element())
                        , '1%')
                    ->addElementColumn($elementGrade
                        , '17%');
                $sectionList[] = $section;
                $section = new Section();
                if($isProfile) {
                    $section
                        ->addElementColumn((new Element())
                            ->styleBorderTop('1px', '#BBB')
                            ->stylePaddingTop()
                            ->stylePaddingBottom('10px')
                            , '80%')
                        ->addElementColumn((new Element())
                            , '20%');
                    $sectionList[] = $section;
                } else {
                    $section
                        ->addElementColumn((new Element())
//                            ->setContent('2. Fremdsprache ab Klasse'
//                                . '{% if ' . $Level . ' == false %}
//                                    6
//                                {% else %}
//                                    ' . $Level . '
//                                {% endif %}'
//                            )
                            ->setContent('2. Fremdsprache (abschlussorientiert)')
                            ->styleBorderTop('1px', '#BBB')
                            ->stylePaddingTop()
                            ->styleTextSize('13px')
                            ->styleFontFamily(self::FONT_FAMILY)
                            ->styleLineHeight(self::LINE_HEIGHT)
                            , '80%')
                        ->addElementColumn((new Element())
                            , '20%');
                    $sectionList[] = $section;
                }
            }
        } else {

            $section = new Section();
            if($isProfile){
                $section
                    ->addElementColumn((new Element())
                        ->setContent('Wahlpflichtbereich mit informatorischer Bildung ab Klasse 8:')
                        ->styleTextBold()
                        ->styleMarginTop('10px')
                        ->styleTextSize($TextSize)
                        ->styleFontFamily(self::FONT_FAMILY_BOLD)
                        ->styleLineHeight(self::LINE_HEIGHT)
                    );
            } else {
                $section
                    ->addElementColumn((new Element())
                        ->setContent('Wahlpflichtbereich:')
                        ->styleTextBold()
                        ->styleMarginTop('10px')
                        ->styleTextSize($TextSize)
                        ->styleFontFamily(self::FONT_FAMILY_BOLD)
                        ->styleLineHeight(self::LINE_HEIGHT)
                    );
            }
            $sectionList[] = $section;

            $elementName = (new Element())
                ->setContent('---')
                ->styleMarginTop($marginTop)
                ->styleTextSize($TextSize)
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleLineHeight(self::LINE_HEIGHT);

            $elementGrade = (new Element())
                ->setContent('&ndash;')
                ->styleAlignCenter()
                ->styleBackgroundColor('#E6E6E6')
                ->stylePaddingTop('4px')
                ->stylePaddingBottom('4px')
                ->styleTextSize($TextSize)
                ->styleMarginTop($marginTop)
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleLineHeight(self::LINE_HEIGHT);

            $section = new Section();
            $section
                ->addElementColumn($elementName
                    , '82%')
                ->addElementColumn((new Element())
                    , '2%')
                ->addElementColumn($elementGrade
                    , '17%');
            $sectionList[] = $section;
            $section = new Section();
            if($isProfile){
                $section
                    ->addElementColumn((new Element())
                        ->styleBorderTop('1px', '#BBB')
                        ->stylePaddingTop()
                        ->stylePaddingBottom('10px')
                        , '80%')
                    ->addElementColumn((new Element())
                        , '20%');
                $sectionList[] = $section;
            } else {
                $section = new Section();
                $section
                    ->addElementColumn((new Element())
//                        ->setContent('2. Fremdsprache ab Klasse 6')
                        ->setContent('2. Fremdsprache (abschlussorientiert)')
                        ->styleBorderTop('1px', '#BBB')
                        ->stylePaddingTop()
                        ->styleTextSize('13px')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleLineHeight(self::LINE_HEIGHT)
                        , '80%')
                    ->addElementColumn((new Element())
                        , '20%');
                $sectionList[] = $section;
            }
        }

        return empty($sectionList) ? (new Slice())->styleHeight('60px') : $slice->addSectionList($sectionList);
    }

    /**
     * @param int $personId
     *
     * @return Section[]
     */
    public function getEZSHPerformanceGroup($personId)
    {

        $SectionList = array();
        $Section = new Section();
        $Section->addElementColumn((new Element())
            ->setContent('{% if(Content.P'.$personId.'.Input.PerformanceGroup is not empty) %}
                    {{ Content.P'.$personId.'.Input.PerformanceGroup }}
                {% else %}
                    &nbsp;
                {% endif %}')
            ->styleBorderBottom('1px', '#BBB')
            ->styleFontFamily(self::FONT_FAMILY)
            ->styleLineHeight(self::LINE_HEIGHT)
            , '46%')
            ->addSliceColumn(
                $this->setCheckBox('{% if(Content.P'.$personId.'.Input.PerformanceGroup is not empty) %}
                    X
                {% else %}
                    &nbsp;
                {% endif %}')
                    ->styleMarginTop('-4px')
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('teilgenommen')
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleLineHeight(self::LINE_HEIGHT)
                , '22%'
            )
            ->addSliceColumn(
                $this->setCheckBox('{% if(Content.P'.$personId.'.Input.PerformanceGroup is not empty) %}
                    &nbsp;
                {% else %}
                    X
                {% endif %}')
                    ->styleMarginTop('-4px')
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('nicht teilgenommen')
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleLineHeight(self::LINE_HEIGHT)
                , '22%'
            );
        $SectionList[] = $Section;
        $Section = new Section();
        $Section->addElementColumn((new Element())
            ->setContent('Leistungsgruppe')
            ->styleFontFamily(self::FONT_FAMILY)
            ->styleLineHeight(self::LINE_HEIGHT)
        );
        $SectionList[] = $Section;

        return $SectionList;
    }

    /**
     * @param int    $personId
     * @param string $Height
     *
     * @return Section[]
     */
    public function getEZSHRating($personId, $Height = '570px')
    {
        $SectionList = array();
        $Section = new Section();
        $Section->addElementColumn(((new Element())
            ->setContent('EINSCHÄTZUNG')
            ->styleTextSize('10pt')
            ->styleTextBold()
            ->stylePaddingBottom('4px')
            ->styleFontFamily(self::FONT_FAMILY_BOLD)
            ->styleLineHeight(self::LINE_HEIGHT)
        )
        );
        $SectionList[] = $Section;
        $Section = new Section();
        $Section->addElementColumn((new Element())
            ->setContent('{% if(Content.P'.$personId.'.Input.Rating is not empty) %}
                    {{ Content.P'.$personId.'.Input.Rating|nl2br }}
                {% else %}
                    ---
                {% endif %}')
            ->styleHeight($Height)
            ->styleFontFamily(self::FONT_FAMILY)
            ->styleLineHeight(self::LINE_HEIGHT)
        );
        $SectionList[] = $Section;
        return $SectionList;
    }

    /**
     * @param int $personId
     * @param string $Height
     * @param string $text
     *
     * @return Section[]
     */
    public function getEZSHRemark($personId, $Height = '170px', $text = 'BEMERKUNGEN')
    {
        $SectionList = array();
        $Section = new Section();
        $Section->addElementColumn((new Element())
            ->setContent($text)
            ->styleTextSize('10pt')
            ->styleTextBold()
            ->stylePaddingBottom('4px')
            ->styleFontFamily(self::FONT_FAMILY_BOLD)
            ->styleLineHeight(self::LINE_HEIGHT)
        );
        $SectionList[] = $Section;
        $Section = new Section();
        $Section->addElementColumn((new Element())
            ->setContent('{% if(Content.P'.$personId.'.Input.RemarkWithoutTeam is not empty) %}
                    {{ Content.P'.$personId.'.Input.RemarkWithoutTeam|nl2br }}
                {% else %}
                    ---
                {% endif %}')
//            ->styleAlignJustify()
            ->styleHeight($Height)
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
    public function getEZSHMissing($personId)
    {

        $SectionList = array();
        $Section = new Section();
        $Section->addElementColumn((new Element())
            ->setContent('FEHLTAGE')
            ->styleTextBold()
            ->stylePaddingBottom('5px')
            ->styleFontFamily(self::FONT_FAMILY_BOLD)
            ->styleLineHeight(self::LINE_HEIGHT)
        );
        $SectionList[] = $Section;

        $Section = new Section();
        $Section->addElementColumn((new Element())
            ->setContent('entschuldigt')
            ->styleFontFamily(self::FONT_FAMILY)
            ->styleLineHeight(self::LINE_HEIGHT)
            , '16%')
            ->addElementColumn((new Element())
                ->setContent('{% if(Content.P'.$personId.'.Input.Missing is not empty) %}
                {{ Content.P'.$personId.'.Input.Missing }}
            {% else %}
                &nbsp;
            {% endif %}')
                ->styleAlignCenter()
                ->styleBorderBottom('1px', '#BBB')
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleLineHeight(self::LINE_HEIGHT)
                , '18%')
            ->addElementColumn((new Element())
                , '15%')
            ->addElementColumn((new Element())
                ->setContent('unentschuldigt')
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleLineHeight(self::LINE_HEIGHT)
                , '18%')
            ->addElementColumn((new Element())
                ->setContent('{% if(Content.P'.$personId.'.Input.Bad.Missing is not empty) %}
                &nbsp;{{ Content.P'.$personId.'.Input.Bad.Missing }}
            {% else %}
                &nbsp;
            {% endif %}')
                ->styleAlignCenter()
                ->styleBorderBottom('1px', '#BBB')
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleLineHeight(self::LINE_HEIGHT)
                , '18%')
            ->addElementColumn((new Element())
                , '15%');
        $SectionList[] = $Section;
        return $SectionList;
    }

    /**
     * @param $personId
     *
     * @return Section[]
     */
    public function getEZSHTransfer($personId, $height = '60px')
    {
        $SectionList = array();
        $Section = new Section();
        $Section->addElementColumn((new Element())
            ->setContent('VERSETZUNGSVERMERK')
            ->styleTextSize('10pt')
            ->styleTextBold()
            ->stylePaddingBottom('4px')
            ->styleFontFamily(self::FONT_FAMILY_BOLD)
            ->styleLineHeight(self::LINE_HEIGHT)
        );
        $SectionList[] = $Section;

        $Section = new Section();
        $Section->addElementColumn((new Element())
            ->setContent('{% if(Content.P'.$personId.'.Input.Transfer is not empty) %}
                    {{ Content.P'.$personId.'.Input.Transfer|nl2br }}.
                {% else %}
                    &nbsp;
                {% endif %}')
//            ->styleAlignJustify()
            ->styleHeight($height)
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
    public function getEZSHDateSign($personId)
    {

        $SectionList = array();
        $Section = new Section();
        $Section->addElementColumn((new Element())
            ->setContent('{% if(Content.P'.$personId.'.Input.Date is not empty) %}
                    {{ Content.P'.$personId.'.Input.Date }}
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
        $Section->addElementColumn((new Element())
            ->setContent('Datum')
            ->styleFontFamily(self::FONT_FAMILY)
            ->styleLineHeight(self::LINE_HEIGHT)
            , '25%'
        );
        $Section->addElementColumn((new Element())
            ->setContent('{% if(Content.P'.$personId.'.DivisionTeacher.Description is not empty) %}
                    {{ Content.P'.$personId.'.DivisionTeacher.Description }}
                {% else %}
                    Klassenlehrer(in)
                {% endif %}')
            ->styleFontFamily(self::FONT_FAMILY)
            ->styleLineHeight(self::LINE_HEIGHT)
            , '40%'
        );
        $Section->addElementColumn((new Element())
            ->setContent('Für den Schulträger')
            ->styleFontFamily(self::FONT_FAMILY)
            ->styleLineHeight(self::LINE_HEIGHT)
            , '35%'
        );
        $SectionList[] = $Section;
        return $SectionList;
    }

    /**
     * @return Section[]
     */
    public function getEZSHCustody()
    {

        $SectionList = array();
        $SectionList[] = (new Section())->addElementColumn((new Element())
            ->setContent('Zur Kenntnis genommen:')
            ->styleFontFamily(self::FONT_FAMILY)
            ->styleLineHeight(self::LINE_HEIGHT)
            , '30%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleBorderBottom('1px', '#BBB')
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleLineHeight(self::LINE_HEIGHT)
                , '65%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleLineHeight(self::LINE_HEIGHT)
                , '5%');

        $SectionList[] = (new Section())
            ->addElementColumn((new Element())
                , '30%'
            )
            ->addElementColumn((new Element())
                ->setContent('Personensorgeberechtigte(r) ')
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleLineHeight(self::LINE_HEIGHT)
                , '70%'
            );

        return $SectionList;
    }

    /**
     * @param bool $isExtend
     *
     * @return Section[]
     */
    public function getEZSHGradeInfo($isExtend = true)
    {
        $SectionList = array();
        $Section = new Section();
        $Section->addElementColumn((new Element())
            ->setContent('Noten: 1 = sehr gut, 2 = gut, 3 = befriedigend, 4 = ausreichend, 5 = mangelhalft, 6 = ungenügend')
            ->styleTextSize('8pt')
            ->stylePaddingBottom(($isExtend ? '0px' : '12px' ))
            ->styleFontFamily(self::FONT_FAMILY)
            ->styleLineHeight(self::LINE_HEIGHT)
        );
        $SectionList[] = $Section;
        if($isExtend){
            $Section = new Section();
            $Section->addElementColumn((new Element())
                ->setContent('* Bei Belegung der zweiten abschlussorientierten Fremdsprache entfällt der Neigungskurs (§ 18 SOMIA)')
                ->styleTextSize('8pt')
                ->stylePaddingTop()
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleLineHeight(self::LINE_HEIGHT)
            );
            $SectionList[] = $Section;
        }
        return $SectionList;
    }

    /**
     * @param int    $personId
     * @param string $Height
     *
     * @return Section[]
     */
    public function getEZSHArrangement($personId, $Height = '100px')
    {
        $SectionList = array();
        $Section = new Section();
        $Section->addElementColumn(((new Element())
            ->setContent('Besonderes Engagement an den Zinzendorfschulen')
            ->styleTextSize('14px')
            ->styleMarginTop('20px')
            ->styleTextBold()
            ->stylePaddingBottom('4px')
            ->styleFontFamily(self::FONT_FAMILY_BOLD)
            ->styleLineHeight(self::LINE_HEIGHT)
        )
        );
        $SectionList[] = $Section;
        $Section = new Section();
        $Section->addElementColumn((new Element())
            ->setContent('{% if(Content.P'.$personId.'.Input.Arrangement is not empty) %}
                    {{ Content.P'.$personId.'.Input.Arrangement|nl2br }}
                {% else %}
                    ---
                {% endif %}')
            ->styleHeight($Height)
            ->styleFontFamily(self::FONT_FAMILY)
            ->styleLineHeight(self::LINE_HEIGHT)
        );
        $SectionList[] = $Section;
        return $SectionList;
    }

    /**
     * @param $personId
     *
     * @return Section
     */
    public function getEZSHCourse($personId)
    {

        return ((new Section())
            ->addElementColumn((new Element())
                ->setContent(
                    '{% if(Content.P' . $personId . '.Student.Course.Degree is not empty) %}
                        nahm am Unterricht mit dem Ziel des
                        {{ Content.P' . $personId . '.Student.Course.Degree }} teil.
                    {% else %}
                        &nbsp;
                    {% endif %}'
                )
                ->stylePaddingTop('15px')
                ->stylePaddingBottom('10px')
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleLineHeight(self::LINE_HEIGHT)
                , '100%'));
    }

    public static function getEZSHSchoolPart($personId)
    {

        $sliceList = array();

        // SSW-164 Schulname aus den Mandanteneinstellungen verwenden
        if (($tblSetting = Consumer::useService()->getSetting('Education', 'Certificate', 'Diploma', 'AlternateSchoolName'))
            && ($value = trim($tblSetting->getValue()))
        ) {
            $schoolName = $value;
        } else {
            $schoolName = '{% if(Content.P' . $personId . '.Company.Data.Name) %}
                                {{ Content.P' . $personId . '.Company.Data.Name }}
                            {% else %}
                                  &nbsp;
                            {% endif %}';
        }

        // Artikel vor dem Schulnamen
        if (($tblSetting = Consumer::useService()->getSetting(
                'Education', 'Certificate', 'Diploma', 'PreArticleForSchoolName'))
            && $tblSetting->getValue()
        ) {
            $sliceList[] = (new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('hat ' . $tblSetting->getValue())
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleLineHeight(self::LINE_HEIGHT)
                        , '9%')
                    ->addElementColumn((new Element())
                        ->setContent($schoolName)
                        ->styleBorderBottom('1px', '#BBB')
                        ->stylePaddingLeft('7px')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleLineHeight(self::LINE_HEIGHT)
                        ->styleAlignCenter()
                    )
                    ->addElementColumn((new Element())
                        ->styleBorderBottom('1px', '#BBB')
                        ->stylePaddingLeft('7px')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleLineHeight(self::LINE_HEIGHT)
                        ->setContent('&nbsp;')
                        , '9%')
                )
                ->styleMarginTop('35px');
        } else {
            $sliceList[] = (new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('hat')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleLineHeight(self::LINE_HEIGHT)
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent($schoolName)
                        ->styleBorderBottom('1px', '#BBB')
                        ->stylePaddingLeft('7px')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleLineHeight(self::LINE_HEIGHT)
                        ->styleAlignCenter()
                    )
                    ->addElementColumn((new Element())
                        ->styleBorderBottom('1px', '#BBB')
                        ->stylePaddingLeft('7px')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleLineHeight(self::LINE_HEIGHT)
                        ->setContent('&nbsp;')
                        , '5%')
                )
                ->styleMarginTop('35px');
        }

        // Schul-Zusatz
        if (($tblSetting = Consumer::useService()->getSetting('Education', 'Certificate', 'Diploma', 'ShowExtendedSchoolName'))
            && ($value = trim($tblSetting->getValue()))
        ) {
            $showExtendedSchoolName = true;
        } else {
            $showExtendedSchoolName = false;
        }
        if (($tblSetting = Consumer::useService()->getSetting('Education', 'Certificate', 'Diploma', 'AlternateExtendedSchoolName'))
            && ($value = trim($tblSetting->getValue()))
        ) {
            $extendedSchoolName = $value;
        } else {
            $extendedSchoolName = '';
        }
        $hasExtraRow = false;
        if ($showExtendedSchoolName || $extendedSchoolName != '') {
            $hasExtraRow = true;
            if ($extendedSchoolName == '') {
                $extendedSchoolName = '
                {% if(Content.P' . $personId . '.Company.Data.ExtendedName) %}
                    {{ Content.P' . $personId . '.Company.Data.ExtendedName }}
                {% else %}
                    &nbsp;
                {% endif %}';
            }
            $sliceList[] = (new Slice())
                ->addElement(
                    (new Element())
                        ->setContent($extendedSchoolName)
                        ->styleBorderBottom('1px', '#BBB')
                        ->stylePaddingLeft('7px')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleLineHeight(self::LINE_HEIGHT)
                        ->styleAlignCenter()
                )
                ->styleMarginTop('10px');
        }

        $sliceList[] = (new Slice())
            ->addElement(
                (new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Company.Address.Street.Name) %}
                                    {{ Content.P' . $personId . '.Company.Address.Street.Name }}
                                    {{ Content.P' . $personId . '.Company.Address.Street.Number }}
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                    ->styleBorderBottom('1px', '#BBB')
                    ->stylePaddingLeft('7px')
                    ->styleFontFamily(self::FONT_FAMILY)
                    ->styleLineHeight(self::LINE_HEIGHT)
                    ->styleAlignCenter()
            )
            ->styleMarginTop('10px');

        $sliceList[] = (new Slice())
            ->addSection(
                (new Section())
                    ->addElementColumn(
                        (new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom('1px', '#BBB')
                            ->stylePaddingLeft('7px')
                            ->styleFontFamily(self::FONT_FAMILY)
                            ->styleLineHeight(self::LINE_HEIGHT)
                        , '10%')
                    ->addElementColumn(
                        (new Element())
                            ->setContent('{% if(Content.P' . $personId . '.Company.Address.City.Name) %}
                                            {{ Content.P' . $personId . '.Company.Address.City.Code }}
                                            {{ Content.P' . $personId . '.Company.Address.City.Name }}
                                        {% else %}
                                              &nbsp;
                                        {% endif %}')
                            ->styleBorderBottom('1px', '#BBB')
                            ->stylePaddingLeft('7px')
                            ->styleFontFamily(self::FONT_FAMILY)
                            ->styleLineHeight(self::LINE_HEIGHT)
                            ->styleAlignCenter()
                    )
                    ->addElementColumn(
                        (new Element())
                            ->setContent('besucht')
                            ->styleFontFamily(self::FONT_FAMILY)
                            ->styleLineHeight(self::LINE_HEIGHT)
                            ->styleAlignRight()
                        , '10%')
            )
            ->styleMarginTop('10px');

        $sliceList[] = (new Slice())
            ->addElement((new Element())
                ->setContent('Name und Anschrift der Schule')
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleLineHeight(self::LINE_HEIGHT)
                ->styleTextSize('9px')
                ->styleAlignCenter()
                ->styleMarginTop('5px')
                ->styleMarginBottom($hasExtraRow ? '10px' : '30px')
            );

        return $sliceList;
    }

    /**
     * @param $personId
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getEZSHDateLine($personId, $MarginTop = '25px')
    {
        $DateSlice = (new Slice());
        $DateSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Datum:')
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleLineHeight(self::LINE_HEIGHT)
                , '7%')
            ->addElementColumn((new Element())
                ->setContent('{% if(Content.P' . $personId . '.Input.Date is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Date }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                ->styleBorderBottom('1px', '#BBB')
                ->stylePaddingLeft('7px')
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleLineHeight(self::LINE_HEIGHT)
                ->styleAlignCenter()
                , '23%')
            ->addElementColumn((new Element())
                , '70%')
        )
            ->styleMarginTop($MarginTop);
        return $DateSlice;
    }

    /**
     * @param string $marginTop
     * @param string $textSize
     *
     * @return Slice
     * @throws \Exception
     */
    public function getEZSHExaminationsBoard($marginTop, $textSize)
    {

        $leaderName = '&nbsp;';
        $leaderDescription = 'Vorsitzende(r)';
        $firstMemberName = '&nbsp;';
        $secondMemberName = '&nbsp;';

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

        $slice = (new Slice())
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->styleMarginTop($marginTop)
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent('Der Prüfungsausschuss')
                    ->styleFontFamily(self::FONT_FAMILY)
                    ->styleLineHeight(self::LINE_HEIGHT)
                    ->styleAlignCenter()
                    ->styleMarginTop($marginTop)
                )
                ->addElementColumn((new Element())
                    ->styleMarginTop($marginTop)
                    , '30%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderBottom('1px', '#BBB')
                    ->stylePaddingLeft('7px')
                    ->styleFontFamily(self::FONT_FAMILY)
                    ->styleLineHeight(self::LINE_HEIGHT)
                    ->styleMarginTop('15px')
                    , '30%')
                ->addElementColumn((new Element())
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderBottom('1px', '#BBB')
                    ->stylePaddingLeft('7px')
                    ->styleFontFamily(self::FONT_FAMILY)
                    ->styleLineHeight(self::LINE_HEIGHT)
                    ->styleMarginTop('15px')
                    , '30%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($leaderDescription)
                    ->styleFontFamily(self::FONT_FAMILY)
                    ->styleLineHeight(self::LINE_HEIGHT)
                    ->styleAlignCenter()
                    ->styleTextSize($textSize)
                    ->styleMarginTop('0px')
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent('Dienstsiegel der Schule' )
                    ->styleFontFamily(self::FONT_FAMILY)
                    ->styleLineHeight(self::LINE_HEIGHT)
                    ->styleTextSize($textSize)
                    ->styleAlignCenter()
                    ->styleMarginTop('0px')
                )
                ->addElementColumn((new Element())
                    ->setContent('Mitglied')
                    ->styleFontFamily(self::FONT_FAMILY)
                    ->styleLineHeight(self::LINE_HEIGHT)
                    ->styleAlignCenter()
                    ->styleTextSize($textSize)
                    ->styleMarginTop('0px')
                    , '30%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($leaderName)
                    ->styleFontFamily(self::FONT_FAMILY)
                    ->styleLineHeight(self::LINE_HEIGHT)
                    ->styleAlignCenter()
                    ->styleTextSize($textSize)
                    ->styleMarginTop('0px')
                    , '30%')
                ->addElementColumn((new Element())
                )
                ->addElementColumn((new Element())
                    ->setContent($firstMemberName)
                    ->styleFontFamily(self::FONT_FAMILY)
                    ->styleLineHeight(self::LINE_HEIGHT)
                    ->styleAlignCenter()
                    ->styleTextSize($textSize)
                    ->styleMarginTop('0px')
                    , '30%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderBottom('1px', '#BBB')
                    ->stylePaddingLeft('7px')
                    ->styleFontFamily(self::FONT_FAMILY)
                    ->styleLineHeight(self::LINE_HEIGHT)
                    ->styleMarginTop('15px')
                    , '30%')
                ->addElementColumn((new Element())
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderBottom('1px', '#BBB')
                    ->stylePaddingLeft('7px')
                    ->styleFontFamily(self::FONT_FAMILY)
                    ->styleLineHeight(self::LINE_HEIGHT)
                    ->styleMarginTop('15px')
                    , '30%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Für den Schulträger')
                    ->styleFontFamily(self::FONT_FAMILY)
                    ->styleLineHeight(self::LINE_HEIGHT)
                    ->styleAlignCenter()
                    ->styleTextSize($textSize)
                    ->styleMarginTop('0px')
                    , '30%')
                ->addElementColumn((new Element())
                )
                ->addElementColumn((new Element())
                    ->setContent('Mitglied')
                    ->styleFontFamily(self::FONT_FAMILY)
                    ->styleLineHeight(self::LINE_HEIGHT)
                    ->styleAlignCenter()
                    ->styleTextSize($textSize)
                    ->styleMarginTop('0px')
                    , '30%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    , '30%')
                ->addElementColumn((new Element())
                )
                ->addElementColumn((new Element())
                    ->setContent($secondMemberName)
                    ->styleFontFamily(self::FONT_FAMILY)
                    ->styleLineHeight(self::LINE_HEIGHT)
                    ->styleAlignCenter()
                    ->styleTextSize($textSize)
                    ->styleMarginTop('0px')
                    , '30%')
            )
        ;

        return $slice;
    }

    /**
     * @param $personId
     * @param string $TextSize
     * @param bool $IsGradeUnderlined
     *
     * @return Slice
     */
    public function getEZSHAdditionalSubjectLanes(
        $personId,
        &$count
    ) {
        $slice = new Slice();
        if (($tblGradeList = $this->getAdditionalGrade())) {
            $subjectWidth = 30;
            $gradeWidth = 17;
            $TextSize = '14px';
            $TextSizeSmall = '8.5px';
            $paddingTopShrinking = '4px';
            $paddingBottomShrinking = '4px';

            $section = new Section();
            foreach ($tblGradeList['Data'] as $subjectAcronym => $grade) {
                if (($tblSubject = Subject::useService()->getSubjectByAcronym($subjectAcronym))) {
                    $count++;

//                    if ($count == 5) {
//                        continue;
//                    }
                    if ($count % 2 == 1) {
                        $section = new Section();
                        $slice->addSection($section);
                    } else {
                        $section->addElementColumn((new Element()), '6%');
                    }

                    $subjectName = $tblSubject->getName();
//                    if ($tblSubject->getAcronym() == 'GRW') {
//                        $subjectName = 'GRW';
//                    }
//                    if ($tblSubject->getAcronym() == 'PWDS') {
//                        $subjectName = 'PWDS';
//                    }

//                    if (strpos($subjectName, 'naturwissenschaftlich-mathematisches') !== false) {
//                        $subjectName = str_replace('-', ' - ', $subjectName);
//                        continue;
//                    }

                    $section->addElementColumn((new Element())
                        ->setContent($subjectName)
                        ->stylePaddingTop()
                        ->styleMarginTop('10px')
                        ->styleTextSize($TextSize)
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleLineHeight(self::LINE_HEIGHT)
                        , (string)$subjectWidth . '%');

                    $section->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.AdditionalGrade.Data["' . $tblSubject->getAcronym() . '"] is not empty) %}
                                             {{ Content.P' . $personId . '.AdditionalGrade.Data["' . $tblSubject->getAcronym() . '"] }}
                                         {% else %}
                                             &ndash;
                                         {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#E6E6E6')
                        ->styleMarginTop('12px')
                        ->stylePaddingTop(
                            '{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $tblSubject->getAcronym() . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $tblSubject->getAcronym() . '"] is not empty)
                            ) %}
                                ' . $paddingTopShrinking . ' 
                             {% else %}
                                 4px
                             {% endif %}'
                        )
                        ->stylePaddingBottom(
                            '{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $tblSubject->getAcronym() . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $tblSubject->getAcronym() . '"] is not empty)
                            ) %}
                               ' . $paddingBottomShrinking . ' 
                             {% else %}
                                 4px
                             {% endif %}'
                        )
                        ->styleMarginTop('10px')
                        ->styleTextSize(
                            '{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $tblSubject->getAcronym() . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $tblSubject->getAcronym() . '"] is not empty)
                            ) %}
                                 ' . $TextSizeSmall . '
                             {% else %}
                                 ' . $TextSize . '
                             {% endif %}'
                        )
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleLineHeight(self::LINE_HEIGHT)
                        , (string)$gradeWidth . '%');
                }
            }

            if ($count % 2 == 1) {
                $section->addElementColumn((new Element()), '53%');
            }
        }

        if ($count > 4) {
            $slice->styleHeight('130px');
        }

        return $slice;
    }

    /**
     * @param $personId
     * @param string $title
     * @param string $subTitle
     *
     * @return Page
     */
    protected function getRatingPage($personId, string $title, string $subTitle): Page
    {
        return (new Page())
            ->addSlice(
                (new Slice())
                    ->stylePaddingLeft('50px')
                    ->stylePaddingRight('50px')
                    ->addElement((new Element())
                        ->setContent('&nbsp;')
                        ->styleHeight('135px')
                    )
                    ->addSectionList(
                        self::getEZSHHeadLine($title, $subTitle)
                    )
                    ->addElement((new Element())
                        ->styleMarginTop('35px')
                    )
                    ->addSection(
                        self::getEZSHName($personId)
                    )
                    ->addElement((new Element())
                        ->styleMarginTop('35px')
                    )
                    ->addSection(
                        self::getEZSHDivisionAndYear($personId)
                    )
                    ->addElement((new Element())
                        ->styleMarginTop('40px')
                    )
                    ->addSectionList(
                        self::getEZSHRating($personId, '510px')
                    )
                    ->addSectionList(
                        self::getEZSHDateSign($personId)
                    )
                    ->addElement((new Element())
                        ->styleMarginTop('63px')
                    )
                    ->addSectionList(
                        self::getEZSHCustody()
                    )
            );
    }
}