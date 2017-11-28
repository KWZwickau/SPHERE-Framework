<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\CMS;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;

/**
 * Class EsrlStyle
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESRL
 */
abstract class CmsStyle extends Certificate
{

    const TEXT_SIZE = '10pt';

    /**
     * @param int $PictureHeight
     *
     * @return Slice
     */
    public function getCMSHead($PictureHeight = 100)
    {

        $PictureSection = (new Section())
            ->addElementColumn((new Element\Image('Common/Style/Resource/Logo/CMS_Logo.jpg',
                'auto', $PictureHeight.'px'))
                ->styleAlignCenter()
                , '100%');

        if ($this->isSample()) {
            $Header = (new Slice())
                ->addSection(
                    $PictureSection
                )
                // display "Sample" over picture
                ->addSection((new Section())
                    ->addElementColumn((new Element\Sample())
                        ->styleTextSize('30px')
                        ->stylePaddingTop('-'.($PictureHeight + 30).'px')
                    )
                );
        } else {
            $Header = (new Slice())
                ->addSection(
                    $PictureSection
                );
        }
        return $Header;
    }

    /**
     * @param string $firstLine
     * @param string $secondLine
     *
     * @return Section[]
     */
    public function getCMSSchoolLine($firstLine = '', $secondLine = '')
    {

        $SectionList[] = (new Section())->addElementColumn((new Element())
            ->setContent($firstLine)
            ->styleTextSize(self::TEXT_SIZE)
            ->styleAlignCenter()
        );
        $SectionList[] = (new Section())->addElementColumn((new Element())
            ->setContent($secondLine)
            ->styleTextSize(self::TEXT_SIZE)
            ->styleAlignCenter()
            ->styleMarginTop('18px')
            ->stylePaddingBottom()
            ->styleBorderBottom()
        );
        return $SectionList;
    }

    /**
     * @param string $Content
     *
     * @return Section
     */
    public function getCMSHeadLine($Content = '')
    {

        $Section = new Section();
        return $Section->addElementColumn((new Element())
            ->setContent($Content)
            ->styleTextSize('14pt')
            ->styleTextBold()
            ->styleAlignCenter()
        );
    }

    /**
     * @param $personId
     *
     * @return Section
     */
    public function getCMSDivisionAndYear($personId)
    {

        $Section = (new Section());
        $Section->addElementColumn((new Element())
            ->setContent('Klasse:')
            ->styleTextSize(self::TEXT_SIZE)
            , '10%')
            ->addElementColumn((new Element())
                ->setContent('{{ Content.P'.$personId.'.Division.Data.Level.Name }}{{ Content.P'.$personId.'.Division.Data.Name }}')
                ->styleTextSize('11pt')
                ->styleBorderBottom('1px', '#999')
                ->styleAlignCenter()
                , '15%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleTextSize(self::TEXT_SIZE)
                , '40%')
            ->addElementColumn((new Element())
                ->setContent('Schuljahr: &nbsp;&nbsp;')
                ->styleTextSize(self::TEXT_SIZE)
                ->styleAlignRight()
                , '15%')
            ->addElementColumn((new Element())
                ->setContent('{{ Content.P'.$personId.'.Division.Data.Year }}')
                ->styleTextSize('11pt')
                ->styleBorderBottom('1px', '#999')
                ->styleAlignCenter()
                , '20%');
        return $Section;
    }

    /**
     * @param $personId
     *
     * @return Section
     */
    public function getCMSName($personId)
    {

        $Section = new Section();
        $Section->addElementColumn((new Element())
            ->setContent('{{ Content.P'.$personId.'.Person.Data.Name.First }}
                          {{ Content.P'.$personId.'.Person.Data.Name.Last }}')
            ->styleTextSize('13pt')
            ->styleTextBold()
            ->styleAlignCenter()
        );
        return $Section;
    }

    /**
     * @param $personId
     *
     * @return Slice
     */
    protected function getCMSHeadGrade($personId)
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
                        ->styleMarginTop('8px')
                        , '28%');
                    $GradeSection->addElementColumn((new Element())
                        ->setContent('{% if(Content.P'.$personId.'.Input["'.$Grade['GradeAcronym'].'"] is not empty) %}
                                 {{ Content.P'.$personId.'.Input["'.$Grade['GradeAcronym'].'"] }}
                             {% else %}
                                 &ndash;
                             {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#CCC')
                        ->stylePaddingTop('1px')
                        ->stylePaddingBottom('1px')
                        ->styleMarginTop('8px')
                        , '20%');
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
     * @param int    $personId
     * @param bool   $IsHeadline
     * @param string $Height
     *
     * @return Slice
     */
    public function getCMSSubjectLanes($personId, $IsHeadline = true, $Height = '235px')
    {

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

            if ($IsHeadline) {
                $HeaderSection = (new Section());
                $HeaderSection->addElementColumn((new Element())
                    ->setContent('Leistungen in den einzelnen Fächern:')
                    ->styleTextSize('10pt')
                    ->styleTextBold()
                );
                $SectionList[] = $HeaderSection;
            }

            foreach ($SubjectStructure as $SubjectList) {
                $count++;
                // Sort Lane-Ranking (1,2...)
                ksort($SubjectList);

                $SubjectSection = (new Section());

                if (count($SubjectList) == 1 && isset($SubjectList[2])) {
                    $SubjectSection->addElementColumn((new Element()), 'auto');
                }

                foreach ($SubjectList as $Lane => $Subject) {

                    if ($Lane > 1) {
                        $SubjectSection->addElementColumn((new Element())
                            , '4%');
                    }
                    $SubjectSection->addElementColumn((new Element())
                        ->setContent($Subject['SubjectName'])
                        ->stylePaddingTop()
                        ->styleMarginTop('8px')
                        , '28%');


                    $SubjectSection->addElementColumn((new Element())
                        ->setContent('{% if(Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] is not empty) %}
                                             {{ Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] }}
                                         {% else %}
                                             &ndash;
                                         {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#CCC')
                        ->stylePaddingTop('1px')
                        ->stylePaddingBottom('1px')
                        ->styleMarginTop('8px')
                        , '20%');
                }

                if (count($SubjectList) == 1 && isset($SubjectList[1])) {
                    $SubjectSection->addElementColumn((new Element()), '52%');
                }
                $SectionList[] = $SubjectSection;
            }
            return $SubjectSlice->addSectionList($SectionList)
                ->styleHeight($Height);
        }

        return $SubjectSlice;
    }

    /**
     * @param bool $IsHead
     *
     * @return Section
     */
    public function getCMSGradeInfo($IsHead = false)
    {

        $Section = new Section();
        if ($IsHead) {
            $Section->addElementColumn((new Element())
                ->setContent('Noten: 1 = sehr gut, 2 = gut, 3 = befriedigend, 4 = ausreichend, 5 = mangelhalft')
                ->styleTextSize('7pt')
                ->stylePaddingTop('5px')
            );
        } else {
            $Section->addElementColumn((new Element())
                ->setContent('Noten: 1 = sehr gut, 2 = gut, 3 = befriedigend, 4 = ausreichend, 5 = mangelhalft, 6 = ungenügend')
                ->styleTextSize('7pt')
            );
        }
        return $Section;
    }

    /**
     * @param        $personId
     * @param string $Hight
     *
     * @return Section
     */
    public function getCMSRemark($personId, $Hight = '100px')
    {

        $Section = new Section();
        $Section->addElementColumn((new Element())
            ->setContent('{% if(Content.P'.$personId.'.Input.Remark is not empty) %}
                    {{ Content.P'.$personId.'.Input.Remark|nl2br }}
                {% else %}
                    &nbsp;
                {% endif %}')
            ->styleAlignJustify()
            ->styleHeight($Hight)
        );
        return $Section;
    }

    public function getCMSMissing($personId)
    {

        $Section = new Section();
        $Section->addElementColumn((new Element())
            ->setContent('Fehltage entschuldigt:')
            ->styleTextSize(self::TEXT_SIZE)
            ->styleBorderBottom()
            , '35%')
            ->addElementColumn((new Element())
                ->setContent('{% if(Content.P'.$personId.'.Input.Missing is not empty) %}
                    {{ Content.P'.$personId.'.Input.Missing }}
                {% else %}
                    &nbsp;
                {% endif %}')
                ->styleTextSize(self::TEXT_SIZE)
                ->styleBorderBottom()
                , '15%')
            ->addElementColumn((new Element())
                ->setContent('unentschuldigt:')
                ->styleTextSize(self::TEXT_SIZE)
                ->styleBorderBottom()
                , '35%')
            ->addElementColumn((new Element())
                ->setContent('{% if(Content.P'.$personId.'.Input.Bad.Missing is not empty) %}
                    &nbsp;{{ Content.P'.$personId.'.Input.Bad.Missing }}
                {% else %}
                    &nbsp;
                {% endif %}')
                ->styleTextSize(self::TEXT_SIZE)
                ->styleBorderBottom()
                , '15%');
        return $Section;
    }

    /**
     * @param $personId
     *
     * @return Section
     */
    public function getCMSTransfer($personId)   // noch nicht angepasst
    {
        $Section = new Section();
        $Section->addElementColumn((new Element())
            ->setContent('Versetzungsvermerk:')
            ->styleTextSize(self::TEXT_SIZE)
            ->styleTextBold()
            , '24%')
            ->addElementColumn((new Element())
                ->setContent('{% if(Content.P'.$personId.'.Input.Transfer) %}
                        {{ Content.P'.$personId.'.Input.Transfer }}
                    {% else %}
                          &nbsp;
                    {% endif %}')
                , '76%');
        return $Section;
    }

    /**
     * @param $personId
     *
     * @return Section
     */
    public function getCMSDate($personId)
    {

        $Section = (new Section())->addElementColumn((new Element())
            ->setContent('Zwickau, den')
            ->styleTextSize(self::TEXT_SIZE)
            , '15%')
            ->addElementColumn((new Element())
                ->setContent('{% if(Content.P'.$personId.'.Input.Date is not empty) %}
                            {{ Content.P'.$personId.'.Input.Date }}
                        {% else %}
                            &nbsp;
                        {% endif %}')
                ->styleTextSize(self::TEXT_SIZE)
                ->styleBorderBottom()
                ->styleAlignCenter()
                , '20%')
            ->addElementColumn((new Element())
                , '65%');
        return $Section;
    }

    /**
     * @param        $personId
     * @param bool   $isExtended with directory and stamp
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getCMSTeacher($personId, $isExtended = true, $MarginTop = '25px')
    {
        $SignSlice = (new Slice());
        if ($isExtended) {
            $SignSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderBottom()
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent('Dienstsiegel der Schule')
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
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
                            {% if(Content.P'.$personId.'.Headmaster.Description is not empty) %}
                                {{ Content.P'.$personId.'.Headmaster.Description }}
                            {% else %}
                                Schulleiter(in)
                            {% endif %}'
                        )
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , '30%')
                    ->addElementColumn((new Element())
                        , '40%')
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if(Content.P'.$personId.'.DivisionTeacher.Description is not empty) %}
                                {{ Content.P'.$personId.'.DivisionTeacher.Description }}
                            {% else %}
                                Klassenlehrer(in)
                            {% endif %}'
                        )
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , '30%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent(
                            '{% if(Content.P'.$personId.'.Headmaster.Name is not empty) %}
                                {{ Content.P'.$personId.'.Headmaster.Name }}
                            {% else %}
                                &nbsp;
                            {% endif %}'
                        )
                        ->styleTextSize('11px')
                        ->stylePaddingTop('2px')
                        ->styleAlignCenter()
                        , '30%')
                    ->addElementColumn((new Element())
                        , '40%')
                    ->addElementColumn((new Element())
                        ->setContent(
                            '{% if(Content.P'.$personId.'.DivisionTeacher.Name is not empty) %}
                                {{ Content.P'.$personId.'.DivisionTeacher.Name }}
                            {% else %}
                                &nbsp;
                            {% endif %}'
                        )
                        ->styleTextSize('11px')
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
                    ->styleBorderBottom()
                    , '30%')
            )
                ->styleMarginTop($MarginTop)
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '70%')
                    ->addElementColumn((new Element())
                        ->setContent('
                        {% if(Content.P'.$personId.'.DivisionTeacher.Description is not empty) %}
                                {{ Content.P'.$personId.'.DivisionTeacher.Description }}
                            {% else %}
                                Klassenlehrer(in)
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , '30%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '70%')
                    ->addElementColumn((new Element())
                        ->setContent(
                            '{% if(Content.P'.$personId.'.DivisionTeacher.Name is not empty) %}
                                {{ Content.P'.$personId.'.DivisionTeacher.Name }}
                            {% else %}
                                &nbsp;
                            {% endif %}'
                        )
                        ->styleTextSize('11px')
                        ->stylePaddingTop('2px')
                        ->styleAlignCenter()
                        , '30%')
                );
        }
        return $SignSlice;
    }

    /**
     * @return Section[]
     */
    public function getCMSCustody()
    {

        $SectionList = array();
        $SectionList[] = (new Section())->addElementColumn((new Element())
            ->setContent('Zur Kenntnis genommen:')
            , '25%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleBorderBottom()
                , '50%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                , '25%');

        $SectionList[] = (new Section())
            ->addElementColumn((new Element())
                ->setContent('Erziehungsberechtigte/r')
                ->styleTextSize('11px')
                ->styleAlignCenter()
            );

        return $SectionList;
    }

    /**
     * @return Section[]
     */
    public function getCMSFoot()
    {
        $SectionList = array();
        $SectionList[] = (new Section())->addElementColumn((new Element())
            ->setContent('&nbsp;')
            ->styleTextSize('5px')
            ->styleBorderBottom()
        );
        $SectionList[] = (new Section())->addElementColumn((new Element())
            ->setContent('Evangelische Schule Stephan Roth, Kirchstr. 4, 08064 Zwickau')
            ->styleTextSize('8.5px')
            ->styleAlignRight()
            ->stylePaddingTop()
        );
        return $SectionList;
    }
}