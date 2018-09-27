<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generate\Generate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;

/**
 * Class MsAbsRs
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class MsAbsRs extends Certificate
{

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     *
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $Header = $this->getHead($this->isSample(), true, 'auto', '50px');

        // leere Seite
        $pageList[] = new Page();

        $pageList[] = (new Page())
            ->addSlice(
                $Header
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('ABSCHLUSSZEUGNIS')
                    ->styleTextSize('27px')
                    ->styleAlignCenter()
                    ->styleMarginTop('32%')
                    ->styleTextBold()
                )
            );

        $pageList[] = (new Page())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Vorname und Name:')
                        , '22%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                                          {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                        ->styleBorderBottom()
                    )
                )->styleMarginTop('60px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('geboren am')
                        , '22%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthday is not empty) %}
                                    {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->styleBorderBottom()
                        , '20%')
                    ->addElementColumn((new Element())
                        ->setContent('in')
                        ->styleAlignCenter()
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthplace is not empty) %}
                                    {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthplace }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->styleBorderBottom()
                    )
                )->styleMarginTop('10px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('wohnhaft in')
                        , '22%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Person.Address.City.Name) %}
                                    {{ Content.P' . $personId . '.Person.Address.Street.Name }}
                                    {{ Content.P' . $personId . '.Person.Address.Street.Number }},
                                    {{ Content.P' . $personId . '.Person.Address.City.Code }}
                                    {{ Content.P' . $personId . '.Person.Address.City.Name }}
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                        ->styleBorderBottom()
                    )
                )->styleMarginTop('10px')
            )
            ->addSliceArray($this->getSchoolPart($personId))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('und hat nach Bestehen der Abschlussprüfung der Schulart Mittelschule den')
                    ->styleMarginTop('8px')
                    ->styleAlignLeft()
                )
                ->addElement((new Element())
                    ->setContent('REALSCHULABSCHLUSS')
                    ->styleMarginTop('18px')
                    ->styleTextSize('20px')
                    ->styleTextBold()
                )
                ->addElement((new Element())
                    ->setContent('erworben.')
                    ->styleMarginTop('20px')
                    ->styleAlignLeft()
                )
                ->styleAlignCenter()
                ->styleMarginTop('22%')
            );

        $pageList[] = (new Page())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Vorname und Name:')
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                                          {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                        ->styleBorderBottom()
                        , '45%')
                    ->addElementColumn((new Element())
                        ->setContent('Klasse')
                        ->styleAlignCenter()
                        , '10%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Division.Data.Level.Name }}{{ Content.P' . $personId . '.Division.Data.Name }}')
                        ->styleBorderBottom()
                        ->styleAlignCenter()
                    )
                )->styleMarginTop('60px')
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Leistungen in den einzelnen Fächern:')
                    ->styleMarginTop('15px')
                    ->styleTextBold()
                )
            )
            ->addSlice($this->getSubjectLanes($personId, true, array(), '14px', false, true)->styleHeight('270px'))
            /////////////////////////
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Leistungen in Fächern, die in Klassenstufe 9 abgeschlossen wurden:')
                    ->styleMarginTop('15px')
                    ->styleTextBold()
                )
            )
            ->addSlice($this->getAdditionalSubjectLanes($personId))
            /////////////////////////
            ->addSlice($this->getDescriptionHead($personId))
            ->addSlice($this->getDescriptionContent($personId, '215px', '15px'))
            ->addSlice($this->getDateLine($personId))
            ///////
//            ->addSlice((new Slice())
//                ->addSection((new Section())
//                    ->addElementColumn((new Element())
//                        , '30%')
//                    ->addElementColumn((new Element())
//                        ->setContent('Der Prüfungsausschuss')
//                        ->styleAlignCenter()
//                        , '40%')
//                    ->addElementColumn((new Element())
//                        , '30%')
//                )
//            )
//            ->addSlice((new Slice())
//                ->addSection((new Section())
//                    ->addElementColumn((new Element())
//                        ->setContent('&nbsp;')
//                        ->styleAlignCenter()
//                        ->styleMarginTop('10px')
//                        ->styleBorderBottom('1px', '#000')
//                        , '30%')
//                    ->addElementColumn((new Element())
//                        , '40%')
//                    ->addElementColumn((new Element())
//                        ->setContent('&nbsp;')
//                        ->styleAlignCenter()
//                        ->styleMarginTop('10px')
//                        ->styleBorderBottom('1px', '#000')
//                        , '30%')
//                )
//                ->styleMarginTop('5px')
//            )
//            ->addSlice((new Slice())
//                ->addSection((new Section())
//                    ->addElementColumn((new Element())
//                        ->setContent('Vorsitzende(r)')
//                        ->styleAlignCenter()
//                        ->styleTextSize('11px')
//                        , '30%')
//                    ->addElementColumn((new Element())
//                        , '5%')
//                    ->addElementColumn((new Element())
//                        ->setContent('Dienstsiegel der Schule')
//                        ->styleAlignCenter()
//                        ->styleTextSize('11px')
//                        , '30%')
//                    ->addElementColumn((new Element())
//                        , '5%')
//                    ->addElementColumn((new Element())
//                        ->setContent('Mitglied')
//                        ->styleAlignCenter()
//                        ->styleTextSize('11px')
//                        , '30%')
//                )
//            )
//            ->addSlice((new Slice())
//                ->addSection((new Section())
//                    ->addElementColumn((new Element())
//                        , '70%')
//                    ->addElementColumn((new Element())
//                        ->setContent('&nbsp;')
//                        ->styleAlignCenter()
//                        ->styleMarginTop('15px')
//                        ->styleBorderBottom('1px', '#000')
//                        , '30%')
//                )
//            )
//            ->addSlice((new Slice())
//                ->addSection((new Section())
//                    ->addElementColumn((new Element())
//                        , '70%')
//                    ->addElementColumn((new Element())
//                        ->setContent('Mitglied')
//                        ->styleAlignCenter()
//                        ->styleTextSize('11px')
//                        , '30%')
//                )
//            )
            ->addSlice($this->getExaminationsBoard('10px','11px'))
            ->addSlice($this->getInfo('60px',
                'Notenerläuterung:',
                '1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft; 6 = ungenügend')
            );

        return $pageList;
    }

    public static function getSchoolPart($personId)
    {

        $sliceList = array();

        // Artikel vor dem Schulnamen
        if (($tblSetting = Consumer::useService()->getSetting(
                'Education', 'Certificate', 'Diploma', 'PreArticleForSchoolName'))
            && $tblSetting->getValue()
        ) {
            $sliceList[] = (new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('hat ' . $tblSetting->getValue())
                        , '9%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Company.Data.Name) %}
                                    {{ Content.P' . $personId . '.Company.Data.Name }}
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                        ->styleBorderBottom('1px')
                        ->styleAlignCenter()
                    )
                    ->addElementColumn((new Element())
                        ->styleBorderBottom('1px')
                        ->setContent('&nbsp;')
                        , '9%')
                )
                ->styleMarginTop('35px');
        } else {
            $sliceList[] = (new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('hat')
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Company.Data.Name) %}
                                    {{ Content.P' . $personId . '.Company.Data.Name }}
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                        ->styleBorderBottom('1px')
                        ->styleAlignCenter()
                    )
                    ->addElementColumn((new Element())
                        ->styleBorderBottom('1px')
                        ->setContent('&nbsp;')
                        , '5%')
                )
                ->styleMarginTop('35px');
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
                        ->styleBorderBottom('1px')
                        ->styleAlignCenter()
                )
                ->styleMarginTop('10px');

        $sliceList[] = (new Slice())
                ->addSection(
                    (new Section())
                        ->addElementColumn(
                            (new Element())
                                ->setContent('&nbsp;')
                                ->styleBorderBottom('1px')
                            , '10%')
                        ->addElementColumn(
                            (new Element())
                                ->setContent('{% if(Content.P' . $personId . '.Company.Address.City.Name) %}
                                            {{ Content.P' . $personId . '.Company.Address.City.Code }}
                                            {{ Content.P' . $personId . '.Company.Address.City.Name }}
                                        {% else %}
                                              &nbsp;
                                        {% endif %}')
                                ->styleBorderBottom('1px')
                                ->styleAlignCenter()
                        )
                        ->addElementColumn(
                            (new Element())
                                ->setContent('besucht')
                                ->styleAlignRight()
                            , '10%')
                )
                ->styleMarginTop('10px');

        $sliceList[] = (new Slice())
            ->addElement((new Element())
                ->setContent('Name und Anschrift der Schule')
                ->styleTextSize('9px')
                ->styleTextColor('#999')
                ->styleAlignCenter()
                ->styleMarginTop('5px')
                ->styleMarginBottom('5px')
            );

        return $sliceList;
    }

    /**
     * @param $personId
     * @param string $TextSize
     * @param bool $IsGradeUnderlined
     *
     * @return Slice
     */
    private function getAdditionalSubjectLanes(
        $personId,
        $TextSize = '14px',
        $IsGradeUnderlined = false
    ) {

        $slice = new Slice();
        if (($tblGradeList = $this->getAdditionalGrade())) {

            // Zeugnisnoten im Wortlaut auf Abschlusszeugnissen --> breiter Zensurenfelder
            if (($tblSetting = Consumer::useService()->getSetting(
                    'Education', 'Certificate', 'Prepare', 'IsGradeVerbalOnDiploma'))
                && $tblSetting->getValue()
            ) {
                $subjectWidth = 37;
                $gradeWidth = 11;
                $TextSizeSmall = '13px';
                $paddingTopShrinking = '4px';
                $paddingBottomShrinking = '4px';
            } else {
                $subjectWidth = 39;
                $gradeWidth = 9;
                $TextSizeSmall = '8.5px';
                $paddingTopShrinking = '5px';
                $paddingBottomShrinking = '6px';
            }

            $count = 0;
            $section = new Section();
            foreach ($tblGradeList['Data'] as $subjectAcronym => $grade) {
                if (($tblSubject = Subject::useService()->getSubjectByAcronym($subjectAcronym))) {
                    $count++;
                    if ($count % 2 == 1) {
                        $section = new Section();
                        $slice->addSection($section);
                    } else {
                        $section->addElementColumn((new Element())
                            , '4%');
                    }

                    $section->addElementColumn((new Element())
                        ->setContent($tblSubject->getName())
                        ->stylePaddingTop()
                        ->styleMarginTop('10px')
                        ->styleTextSize($TextSize)
                        , (string)$subjectWidth . '%');

                    $section->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.AdditionalGrade.Data["' . $tblSubject->getAcronym() . '"] is not empty) %}
                                             {{ Content.P' . $personId . '.AdditionalGrade.Data["' . $tblSubject->getAcronym() . '"] }}
                                         {% else %}
                                             &ndash;
                                         {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#BBB')
                        ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                        ->stylePaddingTop(
                            '{% if(Content.P' . $personId . '.AdditionalGrade.Data.IsShrinkSize["' . $tblSubject->getAcronym() . '"] is not empty) %}
                                 ' . $paddingTopShrinking . ' 
                             {% else %}
                                 2px
                             {% endif %}'
                        )
                        ->stylePaddingBottom(
                            '{% if(Content.P' . $personId . '.AdditionalGrade.Data.IsShrinkSize["' . $tblSubject->getAcronym() . '"] is not empty) %}
                                  ' . $paddingBottomShrinking . ' 
                             {% else %}
                                 2px
                             {% endif %}'
                        )
                        ->styleMarginTop('10px')
                        ->styleTextSize(
                            '{% if(Content.P' . $personId . '.AdditionalGrade.Data.IsShrinkSize["' . $tblSubject->getAcronym() . '"] is not empty) %}
                                 ' . $TextSizeSmall . '
                             {% else %}
                                 ' . $TextSize . '
                             {% endif %}'
                        )
                        , (string)$gradeWidth . '%');
                }
            }

            if ($count % 2 == 1) {
                $section->addElementColumn(new Element(), '52%');
            }
        }

        return $slice;
    }

     /**
     * @param string $marginTop
     * @param string $textSize
     *
     * @return Slice
     * @throws \Exception
     */
    public function getExaminationsBoard($marginTop, $textSize)
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
                    ->styleBorderBottom()
                    ->styleMarginTop('15px')
                    , '30%')
                ->addElementColumn((new Element())
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderBottom()
                    ->styleMarginTop('15px')
                    , '30%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($leaderDescription)
                    ->styleAlignCenter()
                    ->styleTextSize($textSize)
                    ->styleMarginTop('0px')
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent('Dienstsiegel der Schule' )
                    ->styleTextSize($textSize)
                    ->styleAlignCenter()
                    ->styleMarginTop('0px')
                )
                ->addElementColumn((new Element())
                    ->setContent('Mitglied')
                    ->styleAlignCenter()
                    ->styleTextSize($textSize)
                    ->styleMarginTop('0px')
                    , '30%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($leaderName)
                    ->styleAlignCenter()
                    ->styleTextSize($textSize)
                    ->styleMarginTop('0px')
                    , '30%')
                ->addElementColumn((new Element())
                )
                ->addElementColumn((new Element())
                    ->setContent($firstMemberName)
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
                    ->setContent('&nbsp;')
                    ->styleBorderBottom()
                    ->styleMarginTop('15px')
                    , '30%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    , '30%')
                ->addElementColumn((new Element())
                )
                ->addElementColumn((new Element())
                    ->setContent('Mitglied')
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
                    ->styleAlignCenter()
                    ->styleTextSize($textSize)
                    ->styleMarginTop('0px')
                    , '30%')
            )
        ;

        return $slice;
    }
}
