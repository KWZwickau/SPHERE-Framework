<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\MLS;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;

abstract class Style extends Certificate
{
    const MARGIN_TOP_GRADE_LINES = '9px';

    /**
     * @return array
     */
    private function getSubjectAcronymAttendanceList(): array
    {
        $list = array();
        if ($this->getLevel() == 2) {
            $list['EN'] = 'teilgenommen';
            $list['KU'] = 'teilgenommen';
            $list['MU'] = 'teilgenommen';
            $list['SPO'] = 'teilgenommen';
            $list['RE/E'] = 'teilgenommen';
            $list['WE'] = 'teilgenommen';
        } elseif ($this->getLevel() == 3) {
            $list['EN'] = 'teilgenommen';
        }

        return $list;
    }

    /**
     * @param string $title
     *
     * @return Slice
     */
    protected function getCustomHead(string $title = 'Halbjahresinformation der Grundschule'): Slice
    {
        if ($this->isSample()) {
            $elementSample = (new Element\Sample());
        } else {
            $elementSample = (new Element())->setContent('&nbsp;');
        }

        return (new Slice())
            ->styleMarginTop('40px')
            ->addSection((new Section())
                ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/MLS.jpg', 'auto', '105px'))
                    , '25%')
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Dr. Martin Luther Schule – Freie Lutherische Grundschule')
                            ->styleAlignCenter()
                            ->styleTextBold()
                            ->styleTextSize('16px')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Staatlich anerkannte Ersatzschule')
                            ->styleAlignCenter()
                            ->styleTextBold()
                            ->styleTextSize('16px')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn($elementSample
                            ->stylePaddingTop('15px')
                            ->styleAlignCenter()
                            ->styleHeight('40px')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent($title)
                            ->styleAlignCenter()
                            ->styleTextBold()
                            ->styleTextSize('20px')
                        )
                    )
                )
            );
    }

    /**
     * @param $personId
     * @param string $period
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getCustomDivisionAndYear($personId, string $period = '&nbsp;', string $MarginTop = '20px' ): Slice
    {
        return (new Slice())
            ->styleMarginTop($MarginTop)
            ->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Klasse:')
                , '18%')
            ->addElementColumn((new Element())
                ->setContent('{{ Content.P' . $personId . '.Division.Data.Name }}')
                ->styleBorderBottom()
                ->styleAlignCenter()
                , '13%')
            ->addElementColumn((new Element())
                ->setContent($period)
                ->styleAlignCenter()
            )
            ->addElementColumn((new Element())
                ->setContent('Schuljahr:')
                , '16%')
            ->addElementColumn((new Element())
                ->setContent('{{ Content.P' . $personId . '.Division.Data.Year }}')
                ->styleBorderBottom()
                ->styleAlignCenter()
                , '13%')
        );
    }

    /**
     * @param $personId
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getCustomStudentName($personId, string $MarginTop = '14px'): Slice
    {
        return (new Slice())
            ->styleMarginTop($MarginTop)
            ->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Vor- und Zuname:')
                , '17.9%')
            ->addElementColumn((new Element())
                ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }} {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                ->stylePaddingLeft('7px')
                ->styleBorderBottom()
            )
        );
    }

    /**
     * @param $personId
     * @param string $TextSize
     * @param string $MarginTop
     * @param string $backgroundColor
     *
     * @return Slice
     */
    protected function getCustomGradeLanes(
        $personId,
        string $TextSize = '14px',
        string $MarginTop = '5px',
        string $backgroundColor = '#BBB'
    ): Slice {
        $GradeSlice = (new Slice());
        $GradeStructure = array();
        if (($tblCertificateGradeAll = Generator::useService()->getCertificateGradeAll($this->getCertificateEntity()))) {
            foreach ($tblCertificateGradeAll as $tblCertificateGrade) {
                $tblGradeType = $tblCertificateGrade->getServiceTblGradeType();
                $GradeStructure[$tblCertificateGrade->getRanking()][$tblCertificateGrade->getLane()]['GradeAcronym'] = $tblGradeType->getCode();
                $GradeStructure[$tblCertificateGrade->getRanking()][$tblCertificateGrade->getLane()]['GradeName'] = $tblGradeType->getName();
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
                        $GradeSection->addElementColumn((new Element()), '8%');
                    }
                    $GradeSection->addElementColumn((new Element())
                        ->setContent($Grade['GradeName'])
                        ->stylePaddingTop()
                        ->styleMarginTop(self::MARGIN_TOP_GRADE_LINES)
                        ->styleTextSize($TextSize)
                        , '28%');
                    $GradeSection->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Input["' . $Grade['GradeAcronym'] . '"] is not empty) %}
                                         {{ Content.P' . $personId . '.Input["' . $Grade['GradeAcronym'] . '"] }}
                                     {% else %}
                                         &ndash;
                                     {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor($backgroundColor)
                        ->stylePaddingTop('1px')
                        ->stylePaddingBottom('1px')
                        ->styleMarginTop(self::MARGIN_TOP_GRADE_LINES)
                        ->styleTextSize($TextSize)
                        , '18%');
                }

                if (count($GradeList) == 1 && isset($GradeList[1])) {
                    $GradeSection->addElementColumn((new Element()), '54%');
                }

                $GradeSlice->addSection($GradeSection)->styleMarginTop($MarginTop);
            }
        }

        $GradeSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Noten: 1 = sehr gut, 2 = gut, 3 = befriedigend, 4 = ausreichend, 5 = mangelhaft')
                ->styleMarginTop('10px')
                ->styleTextSize('9px')
            )
        );

        return $GradeSlice;
    }

    /**
     * @param $personId
     * @param string $Height
     * @param string $TextSize
     * @param string $MarginTop
     * @param string $backgroundColor
     *
     * @return Slice
     */
    protected function getCustomSubjectLanes(
        $personId,
        string $MarginTop = '30px',
        string $Height = '210px',
        string $TextSize = '14px',
        string $backgroundColor = '#BBB'
    ): Slice {
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

            $HeaderSection = (new Section());
            $HeaderSection->addElementColumn((new Element())
                ->setContent('Leistungen in den einzelnen Fächern')
                ->styleTextBold()
                ->styleMarginTop($MarginTop)
            );
            $SectionList[] = $HeaderSection;

            $subjectAcronymAttendanceList = $this->getSubjectAcronymAttendanceList();

            foreach ($SubjectStructure as $SubjectList) {
                // Sort Lane-Ranking (1,2...)
                ksort($SubjectList);

                $SubjectSection = (new Section());

                if (count($SubjectList) == 1 && isset($SubjectList[2])) {
                    $SubjectSection->addElementColumn((new Element()));
                }

                foreach ($SubjectList as $Lane => $Subject) {

                    if ($Lane > 1) {
                        $SubjectSection->addElementColumn((new Element()), '8%');
                    }
                    $SubjectSection->addElementColumn((new Element())
                        ->setContent($Subject['SubjectName'])
                        ->stylePaddingTop()
                        ->styleMarginTop(self::MARGIN_TOP_GRADE_LINES)
                        , '28%');

                    $SubjectSection->addElementColumn((new Element())
                        ->setContent('{% if(Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] is not empty) %}
                                             {{ Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] }}
                                         {% else %}'
                                            . ($subjectAcronymAttendanceList[$Subject['SubjectAcronym']] ?? '&ndash;')
                                         . '{% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor($backgroundColor)
                        ->styleMarginTop(self::MARGIN_TOP_GRADE_LINES)
                        ->stylePaddingTop('1px')
                        ->stylePaddingBottom('1px')
                        ->styleTextSize($TextSize)
                        , '18%');
                }

                if (count($SubjectList) == 1 && isset($SubjectList[1])) {
                    $SubjectSection->addElementColumn((new Element()), '54%');
                }
                $SectionList[] = $SubjectSection;
            }

            $SectionList[] = (new Section())
                ->addElementColumn((new Element())
                    ->setContent('Noten: 1 = sehr gut, 2 = gut, 3 = befriedigend, 4 = ausreichend, 5 = mangelhaft, 6 = ungenügend')
                    ->styleMarginTop('10px')
                    ->styleTextSize('9px')
                );

            return $SubjectSlice->addSectionList($SectionList)
                ->styleHeight($Height);
        }

        return $SubjectSlice;
    }

    /**
     * @param $personId
     * @param string $Height
     * @param string $Space
     *
     * @return Slice
     */
    public function getCustomRemark($personId, string $Height = '340px', string $Space = '20px'): Slice
    {
        return (new Slice())
            ->styleMarginTop('15px')
            ->styleHeight($Height)
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Bemerkungen:')
                    ->styleTextBold()
                    , 'auto')
                ->addElementColumn((new Element())
                    ->setContent('Fehltage entschuldigt:')
                    ->styleAlignRight()
                    , '25%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '5%')
                ->addElementColumn((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Input.Missing is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                    ->styleBorderBottom()
                    ->styleAlignCenter()
                    , '10%')
                ->addElementColumn((new Element())
                    ->setContent('unentschuldigt:')
                    ->styleAlignRight()
                    , '25%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '5%')
                ->addElementColumn((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Input.Bad.Missing is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Bad.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                    ->styleBorderBottom()
                    ->styleAlignCenter()
                    , '10%')
            )
            ->addElement((new Element())
                ->setContent('{% if(Content.P'.$personId.'.Input.RemarkWithoutTeam is not empty) %}
                                {{ Content.P'.$personId.'.Input.RemarkWithoutTeam|nl2br }}
                            {% else %}
                                &nbsp;
                            {% endif %}')
                ->styleTextSize('11pt')
                ->styleAlignJustify()
                ->styleMarginTop($Space)
            );
    }

    /**
     * @param $personId
     * @param string $Height
     *
     * @return Slice
     */
    public function getCustomRemarkWithoutHeader($personId, string $Height): Slice
    {
        return (new Slice())
            ->styleMarginTop('15px')
            ->styleHeight($Height)
            ->addElement((new Element())
                ->setContent('{% if(Content.P'.$personId.'.Input.RemarkWithoutTeam is not empty) %}
                                {{ Content.P'.$personId.'.Input.RemarkWithoutTeam|nl2br }}
                            {% else %}
                                &nbsp;
                            {% endif %}')
                ->styleTextSize('11pt')
                ->styleAlignJustify()
            );
    }

    /**
     * @param $personId
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getCustomDateLine($personId, string $MarginTop = '25px'): Slice
    {
        return (new Slice())
            ->styleMarginTop($MarginTop)
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Datum:')
                    , '10%')
                ->addElementColumn((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Input.Date is not empty) %}
                                        {{ Content.P' . $personId . '.Input.Date }}
                                    {% else %}
                                        &nbsp;
                                    {% endif %}')
                    ->styleBorderBottom()
                    ->styleAlignCenter()
                    , '20%')
                ->addElementColumn((new Element()))
            );
    }

    /**
     * @param $personId
     * @param string $MarginTop
     * @param string $Height
     *
     * @return Slice
     */
    protected function getCustomRating($personId, string $MarginTop = '10px', string $Height = '106px'): Slice
    {
        return (new Slice())
            ->styleMarginTop($MarginTop)
            ->styleHeight($Height)
            ->addElement((new Element())
                ->setContent('Einschätzung:')
                ->styleTextBold()
            )
            ->addElement((new Element())
                ->setContent('{% if(Content.P'.$personId.'.Input.Rating is not empty) %}
                                {{ Content.P'.$personId.'.Input.Rating|nl2br }}
                             {% else %}
                                &nbsp;
                             {% endif %}')
                ->styleAlignJustify()
            );
    }

    /**
     * @param $personId
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getCustomTransfer($personId, string  $MarginTop = '5px'): Slice
    {
        return (new Slice())
            ->styleMarginTop($MarginTop)
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Versetzungsvermerk:')
                , '30%')
                ->addElementColumn((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Input.Transfer) %}
                                    {{ Content.P' . $personId . '.Input.Transfer }} in Klasse {{ Content.P' . $personId . '.Division.Data.Level.Name + 1 }}.
                                {% else %}
                                    &nbsp;
                                {% endif %}'
                    )
                    ->stylePaddingLeft('7px')
                    ->styleBorderBottom()
                )
            );
    }

    /**
     * @param $personId
     *
     * @return Slice
     */
    protected function getCustomAbsence($personId): Slice
    {
        return (new Slice())
            ->styleMarginTop('5px')
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Fehltage entschuldigt:')
                    ->styleBorderBottom()
                    , '25%')
                ->addElementColumn((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Input.Missing is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                    ->styleBorderBottom()
                    , '25%')
                ->addElementColumn((new Element())
                    ->setContent('unentschuldigt:')
                    ->styleBorderBottom()
                    , '20%')
                ->addElementColumn((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Input.Bad.Missing is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Bad.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                    ->styleBorderBottom()
                    , 'auto')
            );
    }
}