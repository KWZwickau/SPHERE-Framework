<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;

/**
 * Class BfsHj
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
abstract class BfsStyle extends Certificate
{

    /**
     * @param        $personId
     * @param string $CertificateName
     * @param bool $isChangeableCertificateName
     * @param bool $IsLogo
     * @param bool $IsCare
     *
     * @return Slice
     */
    protected function getSchoolHead($personId, $CertificateName = 'Halbjahreszeugnis',
        $isChangeableCertificateName = false, $IsLogo = false, $IsCare = false
    ) {

        $Slice = $this->getSchoolHeadFirstSlice($IsLogo);
        if($isChangeableCertificateName){
            $Slice->addElement((new Element())
                ->setContent('
                {% if(Content.P' . $personId . '.Input.CertificateName is not empty) %}
                    {{ Content.P' . $personId . '.Input.CertificateName }}
                {% else %}
                '.$CertificateName.'
                {% endif %}')
                ->styleAlignCenter()
                ->styleTextSize('30px')
            );
        } else {
            $Slice->addElement((new Element())
                ->setContent($CertificateName)
                ->styleAlignCenter()
                ->styleTextSize('30px')
            );
        }

        if ($IsCare) {
            $PreString = 'der Berufsfachschule für Pflegeberufe';
        } elseif($CertificateName === 'Abschlusszeugnis'){
            $PreString = 'der Berufsfachschule';
        } else {
            $PreString = 'der Berufsfachschule für' .' {% if(Content.P' . $personId . '.Input.BfsDestination is not empty) %}
                    {{ Content.P' . $personId . '.Input.BfsDestination }}
                {% endif %}';
        }
        $Slice->addElement((new Element())
            ->setContent($PreString)
            ->stylePaddingTop('4px')
            ->styleAlignCenter()
            ->styleTextSize('22px')
        );

        return $Slice;
    }

    /**
     * @param        $personId
     * @param string $CertificateName
     *
     * @return Slice
     */
    protected function getSchoolHeadAbg($personId, $CertificateName = 'Abgangszeugnis', $isChangeableCertificateName = false)
    {

        $name = '';
        $secondLine = '';
        // get company name
        if (($tblCompany = $this->getTblCompany())) {
            $name = $tblCompany->getName();
            $secondLine = $tblCompany->getExtendedName();
        }

        $Slice = (new Slice());
        $Slice->addElement((new Element())
            ->setContent($name ? $name : '&nbsp;')
            ->styleAlignCenter()
            ->styleTextSize('22px')
            ->styleHeight('28px')
            ->stylePaddingTop('25px')
        );
        $Slice->addElement((new Element())
            ->setContent($secondLine ? $secondLine : '&nbsp;')
            ->styleAlignCenter()
            ->styleTextSize('18px')
            ->styleHeight('42px')
//            ->stylePaddingTop('20px')
        );
        $Slice->addSection($this->getIndividuallyLogo($this->isSample()));
        if($isChangeableCertificateName){
            $Slice->addElement((new Element())
                ->setContent('
                {% if(Content.P' . $personId . '.Input.CertificateName is not empty) %}
                    {{ Content.P' . $personId . '.Input.CertificateName }}
                {% else %}
                '.$CertificateName.'
                {% endif %}')
                ->styleAlignCenter()
                ->styleTextSize('30px')
            );
        } else {
            $Slice->addElement((new Element())
                ->setContent($CertificateName)
                ->styleAlignCenter()
                ->styleTextSize('30px')
            );
        }
        $Slice->addElement((new Element())
            ->setContent('der Berufsfachschule ')
            ->stylePaddingTop('4px')
            ->styleAlignCenter()
            ->styleTextSize('22px')
        );

        return $Slice;
    }

    /**
     * @param $personId
     * @param string $period
     * @param string $LastLineText
     * @param bool $isPreText
     * @param bool $isPersonNameUnderlined
     *
     * @return Slice
     */
    protected function getStudentHead($personId, $period = 'Schulhalbjahr', $LastLineText = '', $isPreText = false,
        $isPersonNameUnderlined = true
    ) {

        $Slice = new Slice();

        if($isPreText){
            $Text = 'hat im zurückliegenden '.$period.' '.$LastLineText;
        } else {
            $Text = $LastLineText;
        }

        $Slice->stylePaddingTop('20px');
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Klassenstufe {{ Content.P' . $personId . '.Division.Data.Level.Name }}')
                ->styleAlignCenter()
                ->styleBorderBottom('0.5px')
                , '35%'
            )
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                , '30%'
            )
            ->addElementColumn((new Element())
                ->setContent('Schuljahr {{ Content.P' . $personId . '.Division.Data.Year }}')
                ->styleAlignCenter()
                ->styleBorderBottom('0.5px')
                , '35%'
            )
        );

        $Slice->addElement((new Element())
            ->setContent('      
                {{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
                {{ Content.P' . $personId . '.Person.Data.Name.First }}
                {{ Content.P' . $personId . '.Person.Data.Name.Last }}
            ')
            ->styleBorderBottom($isPersonNameUnderlined ? '0.5px' : '0px')
            ->styleAlignCenter()
            ->styleTextSize('26px')
            ->stylePaddingTop('20px')
            ->styleMarginBottom('20px')
        );

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('geboren am  {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday }}')
                ->styleAlignCenter()
                ->styleBorderBottom('0.5px')
                , '35%'
            )
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                , '30%'
            )
            ->addElementColumn((new Element())
                ->setContent('in {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthplace }}')
                ->styleAlignCenter()
                ->styleBorderBottom('0.5px')
                , '35%'
            )
        );
        $Slice->addElement((new Element())
            ->setContent($Text)
            ->styleAlignCenter()
            ->styleTextSize('16px')
            ->stylePaddingTop('20px')
            ->styleBorderBottom('0.5px')
        );

        return $Slice;
    }

    /**
     * @param $personId
     * @param bool $hasExtraInfo
     *
     * @return Slice
     */
    protected function getStudentHeadAbs($personId, bool $hasExtraInfo = true)
    {

        $Slice = new Slice();

        $Slice->stylePaddingTop($hasExtraInfo ? '20px' : '15px');

        $Slice->addElement((new Element())
            ->setContent('      
                {{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
                {{ Content.P' . $personId . '.Person.Data.Name.First }}
                {{ Content.P' . $personId . '.Person.Data.Name.Last }}
            ')
            ->styleBorderBottom('0.5px')
            ->styleAlignCenter()
            ->styleTextSize('26px')
            ->styleMarginBottom('20px')
        );

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('geboren am  {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday }}')
                ->styleAlignCenter()
                ->styleBorderBottom('0.5px')
                , '35%'
            )
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                , '30%'
            )
            ->addElementColumn((new Element())
                ->setContent('in {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthplace }}')
                ->styleAlignCenter()
                ->styleBorderBottom('0.5px')
                , '35%'
            )
        );

        if ($hasExtraInfo) {
            $Slice->addElement((new Element())
                ->setContent('hat vom 
                {% if(Content.P' . $personId . '.Input.DateFrom is not empty) %}
                    {{ Content.P' . $personId . '.Input.DateFrom }}
                {% else %}
                    ---
                {% endif %}
                bis 
                {% if(Content.P' . $personId . '.Input.DateTo is not empty) %}
                    {{ Content.P' . $personId . '.Input.DateTo }}
                {% else %}
                    ---
                {% endif %}
                 die')
                ->styleAlignCenter()
                ->styleTextSize('16px')
                ->stylePaddingTop('10px')
            );
            $Slice->addElement((new Element())
                ->setContent('Berufsfachschule für {% if(Content.P' . $personId . '.Input.BfsDestination is not empty) %}
                    {{ Content.P' . $personId . '.Input.BfsDestination }}
                {% else %}
                    ---
                {% endif %}')
                ->styleAlignCenter()
                ->styleTextSize('20px')
                ->styleTextBold()
                ->stylePaddingTop('10px')
            );
            $GenderString = '{{ Content.P' . $personId . '.Person.Data.Name.First }} {{ Content.P' . $personId . '.Person.Data.Name.Last }}';
            if (($tblPerson = Person::useService()->getPersonById($personId))) {
                if (($tblGender = $tblPerson->getGender())) {
                    if ($tblGender->getName() == 'Männlich') {
                        $GenderString = 'Er';
                    } elseif ($tblGender->getName() == 'Weiblich') {
                        $GenderString = 'Sie';
                    }
                }
            }

            $Slice->addElement((new Element())
                ->setContent('besucht und im Schuljahr
                {% if(Content.P' . $personId . '.Division.Data.Year is not empty) %}
                    {{ Content.P' . $personId . '.Division.Data.Year }}
                {% else %}
                    ---
                {% endif %}
                die Abschlussprüfung bestanden. <br/>' . $GenderString . '
                ist berechtigt, die Berufsbezeichnung')
                ->styleAlignCenter()
                ->styleTextSize('16px')
                ->stylePaddingTop('10px')
            );

            $Slice->addElement((new Element())
                ->setContent('
                {% if(Content.P' . $personId . '.Student.TechnicalCourse is not empty) %}
                    {{ Content.P' . $personId . '.Student.TechnicalCourse }}
                {% else %}
                    ---
                {% endif %}')
                ->styleAlignCenter()
                ->styleTextSize('20px')
                ->styleTextBold()
                ->stylePaddingTop('10px')
            );

            $Slice->addElement((new Element())
                ->setContent('zu führen.')
                ->styleAlignCenter()
                ->styleTextSize('16px')
                ->stylePaddingTop('10px')
            );
        }

        return $Slice;
    }

    /**
     * @param $personId
     *
     * @return Slice
     */
    protected function getStudentHeadAbg($personId)
    {

        $Slice = new Slice();

        $Slice->stylePaddingTop('20px');

        $Slice->addElement((new Element())
            ->setContent('      
                {{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
                {{ Content.P' . $personId . '.Person.Data.Name.First }}
                {{ Content.P' . $personId . '.Person.Data.Name.Last }}
            ')
            ->styleBorderBottom('0.5px')
            ->styleAlignCenter()
            ->styleTextSize('26px')
            ->styleMarginBottom('20px')
        );

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('geboren am  {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday }}')
                ->styleAlignCenter()
                ->styleBorderBottom('0.5px')
                , '35%'
            )
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                , '30%'
            )
            ->addElementColumn((new Element())
                ->setContent('in {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthplace }}')
                ->styleAlignCenter()
                ->styleBorderBottom('0.5px')
                , '35%'
            )
        );
        $Slice->addElement((new Element())
            ->setContent('hat vom {{ Content.P' . $personId . '.Input.DateFrom }}
                bis {{ Content.P' . $personId . '.Input.DateTo }} die')
            ->styleAlignCenter()
            ->styleTextSize('16px')
            ->stylePaddingTop('10px')
        );
        $Slice->addElement((new Element())
            ->setContent('Berufsfachschule für {% if(Content.P' . $personId . '.Input.BfsDestination is not empty) %}
                    {{ Content.P' . $personId . '.Input.BfsDestination }}
                {% endif %}')
            ->styleAlignCenter()
            ->styleTextSize('20px')
            ->styleTextBold()
            ->stylePaddingTop('10px')
        );
        $Slice->addElement((new Element())
            ->setContent('besucht und folgende Leistungen erreicht:')
            ->styleAlignCenter()
            ->styleTextSize('16px')
            ->stylePaddingTop('10px')
            ->styleBorderBottom('0.5px')
        );

        return $Slice;
    }

    /**
     * @param                $personId
     * @param TblCertificate $tblCertificate
     * @param string         $Title
     * @param int            $StartSubject
     * @param int            $DisplaySubjectAmount
     * @param int            $SubjectRankingFrom
     * @param int            $SubjectRankingTill
     * @param string         $Height
     *
     * @return Slice
     */
    protected function getSubjectLineAcross($personId, TblCertificate $tblCertificate, $Title = 'Berufsübergreifender Bereich', $StartSubject = 1,
        $DisplaySubjectAmount = 6, $SubjectRankingFrom = 1, $SubjectRankingTill = 4, $Height = '160px')
    {

        $Slice = (new Slice());
        $Slice->addElement((new Element())
            ->setContent($Title)
            ->styleAlignCenter()
            ->stylePaddingTop('20px')
            ->stylePaddingBottom('10px')
        );

        $tblTechnicalCourse = null;
        if(($tblPerson = Person::useService()->getPersonById($personId))){
            if(($tblStudent = Student::useService()->getStudentByPerson($tblPerson))){
                if(($tblTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())){
                    $tblTechnicalCourse = $tblTechnicalSchool->getServiceTblTechnicalCourse();
                }
            }
        }

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($tblCertificate, $tblTechnicalCourse);
        $tblGradeList = $this->getGrade();

        $ShowEmpty = true;
        if (!empty($tblCertificateSubjectAll)) {
            $ShowEmpty = false;
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if ($tblSubject) {
                    $RankingString = str_pad($tblCertificateSubject->getRanking(), 2 ,'0', STR_PAD_LEFT);
                    $LaneString = str_pad($tblCertificateSubject->getLane(), 2 ,'0', STR_PAD_LEFT);
                    if($tblCertificateSubject->getRanking() >= $SubjectRankingFrom
                        && $tblCertificateSubject->getRanking() <= $SubjectRankingTill){
                        // Grade Exists? => Add Subject to Certificate
                        if (isset($tblGradeList['Data'][$tblSubject->getAcronym()])){
                            $SubjectStructure[$RankingString][$LaneString]['SubjectAcronym'] = $tblSubject->getAcronym();
                            $SubjectStructure[$RankingString][$LaneString]['SubjectName'] = $tblSubject->getName();
                        } else {
                            // Grade Missing, But Subject Essential => Add Subject to Certificate
                            if ($tblCertificateSubject->isEssential()){
                                $SubjectStructure[$RankingString][$LaneString]['SubjectAcronym'] = $tblSubject->getAcronym();
                                $SubjectStructure[$RankingString][$LaneString]['SubjectName'] = $tblSubject->getName();
                            }
                        }
                    }
                }
            }

            // Anzahl der Abzubildenden Einträge (auch ohne Fach)
            $CountSubjectMissing = $DisplaySubjectAmount;

            // Berufsübergreifender Bereich
            $SubjectList = array();
            ksort($SubjectStructure);
            $SubjectCount = 1;
            foreach ($SubjectStructure as $Ranking => $SubjectListTemp) {
                foreach ($SubjectListTemp as $Lane => $Subject) {
                    if($SubjectCount >= $StartSubject
                        && $CountSubjectMissing != 0){
                        $SubjectList[$Ranking][$Lane] = $Subject;
                        $CountSubjectMissing--;
                    }
                    $SubjectCount++;
                }
            }

            $TextSize = '14px';
            $TextSizeSmall = '8px';
            foreach ($SubjectList as $SubjectListAlign) {
                // Sort Lane-Ranking (1,2...)
                ksort($SubjectListAlign);
                $SubjectSection = (new Section());
                if (count($SubjectListAlign) == 1 && isset($SubjectListAlign["02"])) {
                    $SubjectSection->addElementColumn((new Element()), 'auto');
                }

                foreach ($SubjectListAlign as $Lane => $Subject) {
                    if ($Lane > 1){
                        $SubjectSection->addElementColumn((new Element())
                            , '4%');
                    }

                    $SubjectSection->addElementColumn((new Element())
                        ->setContent($Subject['SubjectName'])
                        ->stylePaddingTop()
                        ->styleMarginTop('10px')
                        ->stylePaddingBottom('1px')
                        ->styleTextSize($TextSize)
                        ->styleBorderBottom('0.5px')
                        , '39%');

                    $SubjectSection->addElementColumn((new Element())
                        ->setContent('{% if(Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] is not empty) %}
                                 {{ Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] }}
                             {% else %}
                                 &ndash;
                             {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
                        ->styleMarginTop('10px')
                        ->stylePaddingTop('{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 5.3px
                             {% else %}
                                 2px
                             {% endif %}')
                        ->stylePaddingBottom('{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 5.5px
                             {% else %}
                                 1.5px
                             {% endif %}')
                        ->styleTextSize(
                            '{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 ' . $TextSizeSmall . '
                             {% else %}
                                 ' . $TextSize . '
                             {% endif %}'
                        )
                        , '9%');
                }
                if (count($SubjectListAlign) == 1 && isset($SubjectListAlign["01"])) {
                    $SubjectSection->addElementColumn((new Element()), '52%');
                }

                $Slice->addSection($SubjectSection);
            }
        }

        if($ShowEmpty){
            $this->getEmptyHalfSubjectLine($Slice, 6);
        }

        $Slice->styleHeight($Height);

        return $Slice;
    }

    /**
     * @param $personId
     * @param TblCertificate $tblCertificate
     * @param string $Title
     * @param int $StartSubject
     * @param int $DisplaySubjectAmount
     * @param int $SubjectRankingFrom
     * @param int $SubjectRankingTill
     * @param string $Height
     *
     * @return Slice
     */
    protected function getSubjectLineAcrossAbs($personId, TblCertificate $tblCertificate, $Title = 'Berufsübergreifender Bereich',
        $StartSubject = 1, $DisplaySubjectAmount = 6, $SubjectRankingFrom = 1, $SubjectRankingTill = 4, $Height = '160px')
    {

        $Slice = (new Slice());
        $Slice->addElement((new Element())
            ->setContent($Title)
            ->styleAlignCenter()
            ->stylePaddingTop('20px')
            ->stylePaddingBottom('0px')
        );

        $tblTechnicalCourse = null;
        if(($tblPerson = Person::useService()->getPersonById($personId))){
            if(($tblStudent = Student::useService()->getStudentByPerson($tblPerson))){
                if(($tblTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())){
                    $tblTechnicalCourse = $tblTechnicalSchool->getServiceTblTechnicalCourse();
                }
            }
        }

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($tblCertificate, $tblTechnicalCourse);
        $tblGradeList = $this->getGrade();

        $count = 0;
        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if ($tblSubject) {
                    $RankingString = str_pad($tblCertificateSubject->getRanking(), 2 ,'0', STR_PAD_LEFT);
                    $LaneString = str_pad($tblCertificateSubject->getLane(), 2 ,'0', STR_PAD_LEFT);
                    if($tblCertificateSubject->getRanking() >= $SubjectRankingFrom
                        && $tblCertificateSubject->getRanking() <= $SubjectRankingTill){
                        // Grade Exists? => Add Subject to Certificate
                        if (isset($tblGradeList['Data'][$tblSubject->getAcronym()])){
                            $SubjectStructure[$RankingString][$LaneString]['SubjectAcronym'] = $tblSubject->getAcronym();
                            $SubjectStructure[$RankingString][$LaneString]['SubjectName'] = $tblSubject->getName();
                        } else {
                            // Grade Missing, But Subject Essential => Add Subject to Certificate
                            if ($tblCertificateSubject->isEssential()){
                                $SubjectStructure[$RankingString][$LaneString]['SubjectAcronym'] = $tblSubject->getAcronym();
                                $SubjectStructure[$RankingString][$LaneString]['SubjectName'] = $tblSubject->getName();
                            }
                        }
                    }
                }
            }

            // Anzahl der Abzubildenden Einträge (auch ohne Fach)
            $CountSubjectMissing = $DisplaySubjectAmount;

            // Berufsübergreifender Bereich
            $SubjectList = array();
            ksort($SubjectStructure);
            $SubjectCount = 1;
            foreach ($SubjectStructure as $Ranking => $SubjectListTemp) {
                foreach ($SubjectListTemp as $Lane => $Subject) {
                    if($SubjectCount >= $StartSubject
                        && $CountSubjectMissing != 0){
                        $SubjectList[$Ranking][$Lane] = $Subject;
                        $CountSubjectMissing--;
                    }
                    $SubjectCount++;
                }
            }

            foreach ($SubjectList as $SubjectListAlign) {
                // Sort Lane-Ranking (1,2...)
                ksort($SubjectListAlign);
                $SubjectSection = (new Section());
                if (count($SubjectListAlign) == 1 && isset($SubjectListAlign["02"])) {
                    $count++;
                    $this->getHalfSubjectLineAbs($SubjectSection, $personId, '&ndash;', '-', false);
                }

                foreach ($SubjectListAlign as $Lane => $Subject) {
                    if ($Lane > 1){
                        $SubjectSection->addElementColumn((new Element()), '2%');
                    }

                    $count++;
                    $this->getHalfSubjectLineAbs($SubjectSection, $personId, $Subject['SubjectName'],
                        $Subject['SubjectAcronym'], $Title == 'Berufsbezogener Bereich');
                }

                if (count($SubjectListAlign) == 1 && isset($SubjectListAlign["01"])) {
                    $SubjectSection->addElementColumn((new Element()), '2%');
                    if ($count < $DisplaySubjectAmount) {
                        $count++;
                        $this->getHalfSubjectLineAbs($SubjectSection, $personId, '&ndash;', '-', false);
                    } else {
                        $SubjectSection->addElementColumn((new Element()), '49%');
                    }
                }

                $Slice->addSection($SubjectSection);
            }
        }

        if ($count < $DisplaySubjectAmount) {
            $count = (integer) ($count / 2);
            $count++;
            for ($count; $count <= ($DisplaySubjectAmount % 2 == 0 ? $DisplaySubjectAmount : $DisplaySubjectAmount + 1) / 2; $count++) {
                $section = new Section();
                $this->getHalfSubjectLineAbs($section, $personId, '&ndash;', '-', false);
                if (($count == ($DisplaySubjectAmount + 1) / 2) && ($DisplaySubjectAmount % 2 == 1)) {
                    // nur linke Seite
                    $section->addElementColumn((new Element()), 'auto');
                } else {
                    $section->addElementColumn((new Element()), '2%');
                    $this->getHalfSubjectLineAbs($section, $personId, '&ndash;', '-', false);
                }

                $Slice->addSection($section);
            }
        }

        $Slice->styleHeight($Height);

        return $Slice;
    }

    private function getHalfSubjectLineAbs(Section $SubjectSection, $personId, $subjectName, $subjectAcronym, $isSubjectNameShrinkSize)
    {
        if ($isSubjectNameShrinkSize) {
            $TextSizeSubject = '9px';
        } else {
            $TextSizeSubject = '14px';
        }

        $TextSize = '14px';
        $TextSizeSmall = '9px';

        $marginTopSubjectOneRow = '15px';
        $marginTopGradeOneRow = '13px';
        $marginTopSubjectTwoRow = '2px';
        $marginTopGradeTwoRow = '14px';

        $paddingGrade = '2px';
        $paddingGradeShrinkSize = '5px';

        $len = strlen($subjectName);
        if (!$isSubjectNameShrinkSize && ($len > 35)) {
            $marginTopSubject = $marginTopSubjectTwoRow;
            $marginTopGrade = $marginTopGradeTwoRow;
        } elseif ($isSubjectNameShrinkSize) {
            $marginTopSubject = ($len < 50) ? '20px' : '10px';
            $marginTopGrade = $marginTopGradeOneRow;
        } else {
            $marginTopSubject = $marginTopSubjectOneRow;
            $marginTopGrade = $marginTopGradeOneRow;
        }

        $SubjectSection->addElementColumn((new Element())
            ->setContent($subjectName)
            ->stylePaddingTop()
            ->styleMarginTop($marginTopSubject)
            ->stylePaddingBottom('1px')
            ->stylePaddingLeft('2px')
            ->styleTextSize($TextSizeSubject)
            ->styleBorderBottom('0.5px')
            , '37%');

        $SubjectSection->addElementColumn((new Element())
            ->setContent('&nbsp;')
            ->styleTextSize($TextSize)
            , '1%');

        $SubjectSection->addElementColumn((new Element())
            ->setContent('{% if(Content.P'.$personId.'.Grade.Data["'.$subjectAcronym.'"] is not empty) %}
                     {{ Content.P'.$personId.'.Grade.Data["'.$subjectAcronym.'"] }}
                 {% else %}
                     &ndash;
                 {% endif %}')
            ->styleAlignCenter()
            ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
            ->styleMarginTop($marginTopGrade)
            ->stylePaddingTop('{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $subjectAcronym . '"] is not empty)
                    and (Content.P' . $personId . '.Grade.Data["' . $subjectAcronym . '"] is not empty)
                ) %}'
                    . $paddingGradeShrinkSize .
                '{% else %}'
                    . $paddingGrade .
                '{% endif %}')
            ->stylePaddingBottom('{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $subjectAcronym . '"] is not empty)
                    and (Content.P' . $personId . '.Grade.Data["' . $subjectAcronym . '"] is not empty)
                ) %}'
                . $paddingGradeShrinkSize .
                '{% else %}'
                . $paddingGrade .
                '{% endif %}')
            ->styleTextSize('{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $subjectAcronym . '"] is not empty)
                    and (Content.P' . $personId . '.Grade.Data["' . $subjectAcronym . '"] is not empty)
                ) %}
                     ' . $TextSizeSmall . '
                {% else %}
                    ' . $TextSize . '
                {% endif %}'
            )
            , '11%');
    }

    /**
     * @return Slice
     */
    protected function getSubjectLinePerformance()
    {

        $Slice = (new Slice());
        $Slice->addElement((new Element())
            ->setContent('Leistungen')
            ->styleAlignCenter()
            ->styleTextSize('20px')
            ->styleTextBold()
            ->stylePaddingTop('15px')
        );

        return $Slice;
    }

    /**
     * @return Slice
     */
    protected function getSubjectLineDuty($PaddingTop = '30px')
    {

        $Slice = (new Slice());
        $Slice->addElement((new Element())
            ->setContent('Pflichtbereich')
            ->styleAlignCenter()
            ->styleTextSize('18px')
            ->styleTextBold()
            ->stylePaddingTop($PaddingTop)
        );

        return $Slice;
    }

    /**
     * @param                $personId
     * @param TblCertificate $tblCertificate
     * @param string         $Title
     * @param int            $StartSubject
     * @param int            $DisplaySubjectAmount
     * @param string         $Height
     * @param int            $SubjectRankingFrom
     * @param int            $SubjectRankingTill
     *
     * @return Slice
     */
    protected function getSubjectLineBase($personId, TblCertificate $tblCertificate, $Title = '&nbsp;', $StartSubject = 1,
        $DisplaySubjectAmount = 10, $Height = 'auto', $SubjectRankingFrom = 5, $SubjectRankingTill = 12)
    {
        $Slice = (new Slice());

        $Slice->addElement((new Element())
            ->setContent($Title)
            ->styleAlignCenter()
            ->stylePaddingTop('20px')
            ->stylePaddingBottom('10px')
        );

        $tblTechnicalCourse = null;
        if(($tblPerson = Person::useService()->getPersonById($personId))){
            if(($tblStudent = Student::useService()->getStudentByPerson($tblPerson))){
                if(($tblTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())){
                    $tblTechnicalCourse = $tblTechnicalSchool->getServiceTblTechnicalCourse();
                }
            }
        }

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($tblCertificate, $tblTechnicalCourse);
        $tblGradeList = $this->getGrade();

        // Anzahl der Abzubildenden Einträge (auch ohne Fach)
        $CountSubjectMissing = $DisplaySubjectAmount;
        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if ($tblSubject) {
                    $RankingString = str_pad($tblCertificateSubject->getRanking(), 2 ,'0', STR_PAD_LEFT);
                    $LaneString = str_pad($tblCertificateSubject->getLane(), 2 ,'0', STR_PAD_LEFT);

                    if($tblCertificateSubject->getRanking() >= $SubjectRankingFrom
                        && $tblCertificateSubject->getRanking() <= $SubjectRankingTill){
                        if (isset($tblGradeList['Data'][$tblSubject->getAcronym()])){
                            $SubjectStructure[$RankingString.$LaneString]['SubjectAcronym']
                                = $tblSubject->getAcronym();
                            $SubjectStructure[$RankingString.$LaneString]['SubjectName']
                                = $tblSubject->getName();
                        } else {
                            // Grade Missing, But Subject Essential => Add Subject to Certificate
                            if ($tblCertificateSubject->isEssential()){
                                $SubjectStructure[$RankingString.$LaneString]['SubjectAcronym']
                                    = $tblSubject->getAcronym();
                                $SubjectStructure[$RankingString.$LaneString]['SubjectName']
                                    = $tblSubject->getName();
                            }
                        }
                    }
                }
            }

            $SubjectList = array();
            ksort($SubjectStructure);

            $SubjectCount = 1;
            foreach ($SubjectStructure as $RankingLane => $Subject) {
                if($SubjectCount >= $StartSubject
                    && $CountSubjectMissing != 0){
                    $SubjectList[$RankingLane] = $Subject;
                    $CountSubjectMissing--;
                }
                $SubjectCount++;
            }

            $TextSize = '14px';
            $TextSizeSmall = '8px';

            foreach ($SubjectList as $Subject) {
                // Jedes Fach auf separate Zeile
                $SubjectSection = (new Section());

                $SubjectSection->addElementColumn((new Element())
                    ->setContent($Subject['SubjectName'])
                    ->stylePaddingTop()
                    ->styleMarginTop('10px')
                    ->stylePaddingBottom('1px')
                    ->styleTextSize($TextSize)
                    ->styleBorderBottom('0.5px')
                    , '91%');


                $SubjectSection->addElementColumn((new Element())
                    ->setContent('{% if(Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] is not empty) %}
                             {{ Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] }}
                         {% else %}
                             &ndash;
                         {% endif %}')
                    ->styleAlignCenter()
                    ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
                    ->styleMarginTop('10px')
                    ->stylePaddingTop('{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 5.3px
                             {% else %}
                                 2px
                             {% endif %}')
                    ->stylePaddingBottom('{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 5.5px
                             {% else %}
                                 1.5px
                             {% endif %}')
                    ->styleTextSize(
                        '{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 ' . $TextSizeSmall . '
                             {% else %}
                                 ' . $TextSize . '
                             {% endif %}'
                    )
                    , '9%');
                $Slice->addSection($SubjectSection);
            }
        }

        if($CountSubjectMissing > 0){
            $Slice = $this->getEmptySubjectField($Slice, $CountSubjectMissing);
        }
        $Slice->styleHeight($Height);

        return $Slice;
    }

    /**
     * @param                $personId
     * @param TblCertificate $tblCertificate
     * @param string         $Title
     * @param int            $StartSubject
     * @param int            $DisplaySubjectAmount
     * @param string         $Height
     * @param int            $SubjectRankingFrom
     * @param int            $SubjectRankingTill
     *
     * @return Slice
     */
    protected function getSubjectLineBaseAbg($personId, TblCertificate $tblCertificate, $Title = '&nbsp;', $StartSubject = 1,
        $DisplaySubjectAmount = 10, $Height = 'auto', $SubjectRankingFrom = 5, $SubjectRankingTill = 12)
    {
        $Slice = (new Slice());

        $Slice->addElement((new Element())
            ->setContent($Title)
            ->styleAlignCenter()
            ->stylePaddingTop('20px')
            ->stylePaddingBottom('0px')
        );

        $tblTechnicalCourse = null;
        if(($tblPerson = Person::useService()->getPersonById($personId))){
            if(($tblStudent = Student::useService()->getStudentByPerson($tblPerson))){
                if(($tblTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())){
                    $tblTechnicalCourse = $tblTechnicalSchool->getServiceTblTechnicalCourse();
                }
            }
        }

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($tblCertificate, $tblTechnicalCourse);
        $tblGradeList = $this->getGrade();

        // Anzahl der Abzubildenden Einträge (auch ohne Fach)
        $CountSubjectMissing = $DisplaySubjectAmount;
        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if ($tblSubject) {
                    $RankingString = str_pad($tblCertificateSubject->getRanking(), 2 ,'0', STR_PAD_LEFT);
                    $LaneString = str_pad($tblCertificateSubject->getLane(), 2 ,'0', STR_PAD_LEFT);

                    if($tblCertificateSubject->getRanking() >= $SubjectRankingFrom
                        && $tblCertificateSubject->getRanking() <= $SubjectRankingTill){
                        if (isset($tblGradeList['Data'][$tblSubject->getAcronym()])){
                            $SubjectStructure[$RankingString.$LaneString]['SubjectAcronym']
                                = $tblSubject->getAcronym();
                            $SubjectStructure[$RankingString.$LaneString]['SubjectName']
                                = $tblSubject->getName();
                        } else {
                            // Grade Missing, But Subject Essential => Add Subject to Certificate
                            if ($tblCertificateSubject->isEssential()){
                                $SubjectStructure[$RankingString.$LaneString]['SubjectAcronym']
                                    = $tblSubject->getAcronym();
                                $SubjectStructure[$RankingString.$LaneString]['SubjectName']
                                    = $tblSubject->getName();
                            }
                        }
                    }
                }
            }

            $SubjectList = array();
            ksort($SubjectStructure);

            $SubjectCount = 1;
            foreach ($SubjectStructure as $RankingLane => $Subject) {
                if($SubjectCount >= $StartSubject
                    && $CountSubjectMissing != 0){
                    $SubjectList[$RankingLane] = $Subject;
                    $CountSubjectMissing--;
                }
                $SubjectCount++;
            }

            foreach ($SubjectList as $Subject) {
                // Jedes Fach auf separate Zeile
                $this->getSubjectLineAbg($Slice, $Subject['SubjectName'],
                    '{% if(Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] is not empty) %}
                        {{ Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] }}
                    {% else %}
                        &ndash;
                    {% endif %}'
                );
            }
        }

        if($CountSubjectMissing > 0){
            for($i = 0; $i < $CountSubjectMissing; $i++){
                $this->getSubjectLineAbg($Slice, '&nbsp;', '&ndash;');
            }
        }
        $Slice->styleHeight($Height);

        return $Slice;
    }

    private function getSubjectLineAbg(Slice $slice, $subjectName, $subjectGrade)
    {
        $TextSize = '14px';
        $SubjectSection = (new Section());

        if (strlen($subjectName) > 80) {
            $marginTop = '2px';
            $marginTopGrade = '14px';
        } else {
            $marginTop = '15px';
            $marginTopGrade = '10px';
        }

        $SubjectSection->addElementColumn((new Element())
            ->setContent($subjectName)
            ->stylePaddingTop()
            ->styleMarginTop($marginTop)
            ->stylePaddingBottom('1px')
            ->styleTextSize($TextSize)
            ->styleBorderBottom('0.5px')
            , '83%');

        $SubjectSection->addElementColumn((new Element())
            ->setContent('&nbsp;')
            ->styleTextSize($TextSize)
            , '2%');

        $SubjectSection->addElementColumn((new Element())
            ->setContent($subjectGrade)
            ->styleAlignCenter()
            ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
//                    ->styleMarginTop('9px')
            ->styleMarginTop($marginTopGrade)
            ->stylePaddingTop('4px')
            ->stylePaddingBottom('4px')
            ->styleTextSize($TextSize)
            , '15%');
        $slice->addSection($SubjectSection);
    }

    /**
     * @param        $personId
     * @param string $CertificateName
     * @param bool   $isChangeableCertificateName
     *
     * @return Slice
     */
    protected function getSecondPageHead($personId, $CertificateName = 'Halbjahresinformation', $isChangeableCertificateName = false)
    {
        $Slice = new Slice();

        if($isChangeableCertificateName){
            $Slice->addElement((new Element())
                ->setContent('
                    {% if(Content.P' . $personId . '.Input.CertificateName is not empty) %}
                        {{ Content.P' . $personId . '.Input.CertificateName }}
                    {% else %}
                        ' . $CertificateName . '
                    {% endif %}' . ' für ' .
                    '{% if(Content.P'.$personId.'.Person.Data.Name.Salutation == "Herr") %}
                        Herrn
                    {% else %}
                        {{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
                    {% endif %}
                    {{ Content.P' . $personId . '.Person.Data.Name.First }}
                    {{ Content.P' . $personId . '.Person.Data.Name.Last }},
                    geboren am {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday }} - 2. Seite
                ')
                ->styleAlignCenter()
//            ->styleTextSize('16px')
                ->stylePaddingTop('20px')
                ->styleBorderBottom('0.5px')
            );
        } else {
            $Slice->addElement((new Element())
                ->setContent($CertificateName.
                    ' für 
                    {% if(Content.P'.$personId.'.Person.Data.Name.Salutation == "Herr") %}
                        Herrn
                    {% else %}
                        {{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
                    {% endif %} 
                    {{ Content.P' . $personId . '.Person.Data.Name.First }} {{ Content.P' . $personId . '.Person.Data.Name.Last }},
                    geboren am {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday }} - 2. Seite
                ')
                ->styleAlignCenter()
//            ->styleTextSize('16px')
                ->stylePaddingTop('20px')
                ->styleBorderBottom('0.5px')
            );
        }



        return $Slice;
    }

    /**
     * @param        $personId
     * @param TblCertificate $tblCertificate
     * @param string $Height
     * @param int $CountSubjectMissing
     *
     * @return Slice
     */
    protected function getSubjectLineChosen($personId, TblCertificate $tblCertificate, $Height = '130px', $CountSubjectMissing = 2)
    {
        $Slice = (new Slice());

        $Slice->addElement((new Element())
            ->setContent('Wahlpflichtbereich')
            ->styleAlignCenter()
            ->stylePaddingTop('20px')
            ->stylePaddingBottom('10px')
        );

        $tblTechnicalCourse = null;
        if(($tblPerson = Person::useService()->getPersonById($personId))){
            if(($tblStudent = Student::useService()->getStudentByPerson($tblPerson))){
                if(($tblTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())){
                    $tblTechnicalCourse = $tblTechnicalSchool->getServiceTblTechnicalCourse();
                }
            }
        }

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($tblCertificate, $tblTechnicalCourse);
        $tblGradeList = $this->getGrade();
        // Anzahl der Abzubildenden Einträge (auch ohne Fach)
        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if ($tblSubject) {
                    $RankingString = str_pad($tblCertificateSubject->getRanking(), 2 ,'0', STR_PAD_LEFT);
                    $LaneString = str_pad($tblCertificateSubject->getLane(), 2 ,'0', STR_PAD_LEFT);

                    if($tblCertificateSubject->getRanking() >= 13
                        && $tblCertificateSubject->getRanking() < 15) {

                        if (isset($tblGradeList['Data'][$tblSubject->getAcronym()])) {
                            $SubjectStructure[$RankingString.$LaneString]['SubjectAcronym']
                                = $tblSubject->getAcronym();
                            $SubjectStructure[$RankingString.$LaneString]['SubjectName']
                                = $tblSubject->getName();
                        } else {
                            // Grade Missing, But Subject Essential => Add Subject to Certificate
                            if ($tblCertificateSubject->isEssential()){
                                $SubjectStructure[$RankingString.$LaneString]['SubjectAcronym']
                                    = $tblSubject->getAcronym();
                                $SubjectStructure[$RankingString.$LaneString]['SubjectName']
                                    = $tblSubject->getName();
                            }
                        }
                    }
                }
            }

            $SubjectList = array();

            ksort($SubjectStructure);
            foreach ($SubjectStructure as $RankingLane => $Subject) {
                $SubjectList[] = $Subject;
                $CountSubjectMissing--;
            }

            $TextSize = '14px';
            $TextSizeSmall = '8px';
            foreach ($SubjectList as $Subject) {
                // Jedes Fach auf separate Zeile
                $SubjectSection = (new Section());

                $SubjectSection->addElementColumn((new Element())
                    ->setContent($Subject['SubjectName'])
                    ->stylePaddingTop()
                    ->styleMarginTop('10px')
                    ->stylePaddingBottom('1px')
                    ->styleTextSize($TextSize)
                    ->styleBorderBottom('0.5px')
                    , '91%');


                $SubjectSection->addElementColumn((new Element())
                    ->setContent('{% if(Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] is not empty) %}
                             {{ Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] }}
                         {% else %}
                             &ndash;
                         {% endif %}')
                    ->styleAlignCenter()
                    ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
                    ->styleMarginTop('10px')
                    ->stylePaddingTop('{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 5.3px
                             {% else %}
                                 2px
                             {% endif %}')
                    ->stylePaddingBottom('{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 5.5px
                             {% else %}
                                 1.5px
                             {% endif %}')
                    ->styleTextSize(
                        '{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 ' . $TextSizeSmall . '
                             {% else %}
                                 ' . $TextSize . '
                             {% endif %}'
                    )
                    , '9%');
                $Slice->addSection($SubjectSection);
            }
        }

        if($CountSubjectMissing > 0){
            $Slice = $this->getEmptySubjectField($Slice, $CountSubjectMissing);
        }

        $Slice->styleHeight($Height);

        return $Slice;
    }

    private function getEmptySubjectField(Slice $Slice, $count = 0)
    {

        $TextSize = '14px';
        for($i = 0; $i < $count; $i++){
            $Section = new Section();
            $Section->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->stylePaddingTop()
                ->styleMarginTop('10px')
                ->stylePaddingBottom('1px')
                ->styleTextSize($TextSize)
                ->styleBorderBottom('0.5px')
                , '91%');


            $Section->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
                ->styleMarginTop('10px')
                ->stylePaddingTop('2px')
                ->stylePaddingBottom('1.5px')
                ->styleTextSize($TextSize)
                , '9%');
            $Slice->addSection($Section);
        }
        return $Slice;
    }

    private function getEmptyHalfSubjectLine(Slice $Slice, $count = 2)
    {

        $TextSize = '14px';
        $Section = new Section();
        for($i = 0; $i < $count; $i ++){
            $i++;
            $Section->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->stylePaddingTop()
                ->styleMarginTop('10px')
                ->stylePaddingBottom('1px')
                ->styleTextSize($TextSize)
                ->styleBorderBottom('0.5px')
                , '39%');
//
            $Section->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
                ->styleMarginTop('10px')
                ->stylePaddingTop('2px')
                ->stylePaddingBottom('1.5px')
                ->styleTextSize($TextSize)
                , '9%');

            $Section->addElementColumn((new Element())
                , '4%');
            $Section->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->stylePaddingTop()
                ->styleMarginTop('10px')
                ->stylePaddingBottom('1px')
                ->styleTextSize($TextSize)
                ->styleBorderBottom('0.5px')
                , '39%');

            $Section->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
                ->styleMarginTop('10px')
                ->stylePaddingTop('2px')
                ->stylePaddingBottom('1.5px')
                ->styleTextSize($TextSize)
                , '9%');

            $Slice->addSection($Section);
            $Section = new Section();
        }
        return $Slice;
    }

    /**
     * @param $personId
     * @param TblCertificate $tblCertificate
     *
     * @return Slice
     */
    protected function getPraktika($personId, TblCertificate $tblCertificate, $isAbs = false)
    {

        $tblTechnicalCourse = null;
        if(($tblPerson = Person::useService()->getPersonById($personId))){
            if(($tblStudent = Student::useService()->getStudentByPerson($tblPerson))){
                if(($tblTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())){
                    $tblTechnicalCourse = $tblTechnicalSchool->getServiceTblTechnicalCourse();
                }
            }
        }

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($tblCertificate, $tblTechnicalCourse);

        $Subject = array();

        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if ($tblSubject) {
                    $RankingString = str_pad($tblCertificateSubject->getRanking(), 2 ,'0', STR_PAD_LEFT);
                    $LaneString = str_pad($tblCertificateSubject->getLane(), 2 ,'0', STR_PAD_LEFT);

                    if($tblCertificateSubject->getRanking() == 15) {

                        // Wird immer ausgewiesen (Fach wird nicht abgebildet)
                        $SubjectStructure[$RankingString.$LaneString]['SubjectAcronym']
                            = $tblSubject->getAcronym();
                        $SubjectStructure[$RankingString.$LaneString]['SubjectName']
                            = $tblSubject->getName();
                    }
                }
            }
            $Subject = current($SubjectStructure);
        }

        $TextSize = '14px';
        $TextSizeSmall = $isAbs ? '9px' : '8px';

        $Slice = new Slice();
        $Slice->styleBorderAll('0.5px');
        $Slice->styleMarginTop('30px');
        $Slice->stylePaddingTop('10px');
        $Slice->stylePaddingBottom('10px');
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>Praktische Ausbildung</b> (Dauer:
                    {% if(Content.P' . $personId . '.Input.OperationTimeTotal is not empty) %}
                        {{ Content.P' . $personId . '.Input.OperationTimeTotal }}
                    {% else %}
                        X
                    {% endif %}
                    Wochen)')
                ->stylePaddingLeft('5px')
                , 'auto'
            )
            ->addElementColumn((new Element())
                ->setContent(empty($Subject) ? '&ndash;'
                         :'{% if(Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] is not empty) %}
                             {{ Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] }}
                         {% else %}
                             &ndash;
                         {% endif %}')
                ->styleAlignCenter()
                ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
                ->stylePaddingTop(empty($Subject) ? '2px'
                    :'{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 5.3px
                             {% else %}
                                 2px
                             {% endif %}')
                ->stylePaddingBottom(empty($Subject) ? '2px'
                    :'{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 6px
                             {% else %}
                                 2px
                             {% endif %}')
                ->styleTextSize(empty($Subject) ? $TextSize
                            :'{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 ' . $TextSizeSmall . '
                             {% else %}
                                 ' . $TextSize . '
                             {% endif %}'
                )
                , $isAbs ? '11%' : '9%'
            )
        );
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>{% if(Content.P' . $personId . '.Input.Operation1 is not empty) %}
                        {{ Content.P' . $personId . '.Input.Operation1 }}
                    {% else %}
                        &nbsp;
                    {% endif %}</b>
                     {% if(Content.P' . $personId . '.Input.OperationTime1 is not empty) %}
                        (Dauer {{ Content.P' . $personId . '.Input.OperationTime1 }} Wochen)
                    {% else %}
                        &nbsp;
                    {% endif %}')
                ->stylePaddingTop('10px')
                ->stylePaddingLeft('5px')
            )
        );
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>{% if(Content.P' . $personId . '.Input.Operation2 is not empty) %}
                        {{ Content.P' . $personId . '.Input.Operation2 }}
                    {% else %}
                        &nbsp;
                    {% endif %}</b>
                     {% if(Content.P' . $personId . '.Input.OperationTime2 is not empty) %}
                        (Dauer {{ Content.P' . $personId . '.Input.OperationTime2 }} Wochen)
                    {% else %}
                        &nbsp;
                    {% endif %}')
                ->stylePaddingTop('10px')
                ->stylePaddingLeft('5px')
            )
        );
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>{% if(Content.P' . $personId . '.Input.Operation3 is not empty) %}
                        {{ Content.P' . $personId . '.Input.Operation3 }}
                    {% else %}
                        &nbsp;
                    {% endif %}</b>
                     {% if(Content.P' . $personId . '.Input.OperationTime3 is not empty) %}
                        (Dauer {{ Content.P' . $personId . '.Input.OperationTime3 }} Wochen)
                    {% else %}
                        &nbsp;
                    {% endif %}')
                ->stylePaddingTop('10px')
                ->stylePaddingLeft('5px')
                , '60%'
            )
            ->addElementColumn((new Element())
                ->setContent("Dauer gesamt:
                    {% set WeekCount = 0 %}
                    {% if(Content.P" . $personId . ".Input.OperationTime1 is not empty) %}
                        {%set WeekCount = WeekCount + Content.P" . $personId . ".Input.OperationTime1|replace({',' : '.'} ) %}
                    {% endif %}
                    {% if(Content.P" . $personId . ".Input.OperationTime2 is not empty) %}
                        {%set WeekCount = WeekCount + Content.P" . $personId . ".Input.OperationTime2|replace({',' : '.'} ) %}
                    {% endif %}
                    {% if(Content.P" . $personId . ".Input.OperationTime3 is not empty) %}
                        {%set WeekCount = WeekCount + Content.P" . $personId . ".Input.OperationTime3|replace({',' : '.'} ) %}
                    {% endif %}
                    {% if(Content.P" . $personId . ".Input.OperationTime4 is not empty) %}
                        {%set WeekCount = WeekCount + Content.P" . $personId . ".Input.OperationTime4|replace({',' : '.'} ) %}
                    {% endif %}
                    {{ WeekCount|replace({'.' : ','}) }}
                  Wochen")
                ->stylePaddingTop('10px')
                ->styleAlignRight()
                ->stylePaddingRight('15px')
                , '40%'
            )
        );
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>{% if(Content.P' . $personId . '.Input.Operation4 is not empty) %}
                        {{ Content.P' . $personId . '.Input.Operation4 }}
                    {% else %}
                        &nbsp;
                    {% endif %}</b>
                     {% if(Content.P' . $personId . '.Input.OperationTime4 is not empty) %}
                        (Dauer {{ Content.P' . $personId . '.Input.OperationTime4 }} Wochen)
                    {% else %}
                        &nbsp;
                    {% endif %}')
                ->stylePaddingTop('10px')
                ->stylePaddingLeft('5px')
            )
        );

        return $Slice;
    }

    /**
     * @param $personId
     * @param TblCertificate $tblCertificate
     * @param int $operationTimeCount
     *
     * @return Slice
     */
    protected function getPraktikaAbg($personId, TblCertificate $tblCertificate, int $operationTimeCount = 3)
    {
        $tblTechnicalCourse = null;
        if(($tblPerson = Person::useService()->getPersonById($personId))){
            if(($tblStudent = Student::useService()->getStudentByPerson($tblPerson))){
                if(($tblTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())){
                    $tblTechnicalCourse = $tblTechnicalSchool->getServiceTblTechnicalCourse();
                }
            }
        }

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($tblCertificate, $tblTechnicalCourse);

        $Subject = array();

        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if ($tblSubject) {
                    $RankingString = str_pad($tblCertificateSubject->getRanking(), 2 ,'0', STR_PAD_LEFT);
                    $LaneString = str_pad($tblCertificateSubject->getLane(), 2 ,'0', STR_PAD_LEFT);

                    if($tblCertificateSubject->getRanking() == 15) {

                        // Wird immer ausgewiesen (Fach wird nicht abgebildet)
                        $SubjectStructure[$RankingString.$LaneString]['SubjectAcronym']
                            = $tblSubject->getAcronym();
                        $SubjectStructure[$RankingString.$LaneString]['SubjectName']
                            = $tblSubject->getName();
                    }
                }
            }
            $Subject = current($SubjectStructure);
        }

        $TextSize = '14px';

        $Slice = new Slice();
        $Slice->styleBorderAll('0.5px');
        $Slice->styleMarginTop('30px');
        $Slice->stylePaddingTop('10px');
        $Slice->stylePaddingBottom('10px');
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>Praktische Ausbildung</b> (Dauer: 
                    {{ Content.P' . $personId . '.Input.OperationTimeTotal }}
                    Wochen)')
                ->stylePaddingLeft('5px')
                , '85%'
            )
            ->addElementColumn((new Element())
            ->setContent(empty($Subject) ? '&ndash;'
                :'{% if(Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] is not empty) %}
                             {{ Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] }}
                         {% else %}
                             &ndash;
                         {% endif %}')
                ->styleAlignCenter()
                ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
                ->styleMarginTop('0px')
                ->stylePaddingTop('4px')
                ->stylePaddingBottom('4px')
                ->styleTextSize($TextSize)
                , '15%'
            )
        );

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>{% if(Content.P' . $personId . '.Input.Operation1 is not empty) %}
                        {{ Content.P' . $personId . '.Input.Operation1 }}
                    {% else %}
                        < EINSATZGEBIETE >
                    {% endif %}</b> (Dauer 
                     {% if(Content.P' . $personId . '.Input.OperationTime1 is not empty) %}
                        {{ Content.P' . $personId . '.Input.OperationTime1 }}
                    {% else %}
                        X
                    {% endif %}
                     Wochen)')
                ->stylePaddingTop('10px')
                ->stylePaddingLeft('5px')
            )
        );

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>{% if(Content.P' . $personId . '.Input.Operation2 is not empty) %}
                        {{ Content.P' . $personId . '.Input.Operation2 }}
                    {% else %}
                        < EINSATZGEBIETE >
                    {% endif %}</b> (Dauer 
                     {% if(Content.P' . $personId . '.Input.OperationTime2 is not empty) %}
                        {{ Content.P' . $personId . '.Input.OperationTime2 }}
                    {% else %}
                        X
                    {% endif %}
                     Wochen)')
                ->stylePaddingTop('10px')
                ->stylePaddingLeft('5px')
                , '60%'
            )
            ->addElementColumn((new Element())
                ->setContent('Dauer gesamt: {{ Content.P' . $personId . '.Input.OperationTime1|number_format 
                                             + Content.P' . $personId . '.Input.OperationTime2|number_format 
                                             + Content.P' . $personId . '.Input.OperationTime3|number_format
                                             + Content.P' . $personId . '.Input.OperationTime4|number_format}} Wochen')
                ->stylePaddingTop('10px')
                ->styleAlignRight()
                ->stylePaddingRight('15px')
                , '40%'
            )
        );

        for ($i = 3; $i <= $operationTimeCount; $i++) {
            $Slice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('<b>{% if(Content.P' . $personId . '.Input.Operation' . $i . ' is not empty) %}
                        {{ Content.P' . $personId . '.Input.Operation' . $i . ' }}
                    {% else %}
                        < EINSATZGEBIETE >
                    {% endif %}</b> (Dauer 
                     {% if(Content.P' . $personId . '.Input.OperationTime' . $i . ' is not empty) %}
                        {{ Content.P' . $personId . '.Input.OperationTime' . $i . ' }}
                    {% else %}
                        X
                    {% endif %}
                     Wochen)')
                    ->stylePaddingTop('10px')
                    ->stylePaddingLeft('5px')
                )
            );
        }

        return $Slice;
    }

    /**
     * @param        $personId
     * @param string $Height
     *
     * @return Slice
     */
    protected function getDescriptionBsContent($personId, $Height = '195px', $IsAbsence = false)
    {

        $Slice = new Slice();

        $Slice->styleMarginTop('20px');
        $Slice->stylePaddingTop('5px');
        $Slice->styleHeight($Height);
        $Slice->styleBorderAll('0.5px');
        // lediglich Generalistik mit Absence, deswegen feste "Stunden" hinterlegung ok
        if($IsAbsence) {
            $Slice->addElement((new Element())
                ->setContent('Fehlzeiten Unterricht entschuldigt: {% if(Content.P' . $personId . '.Input.Missing is not empty) %}
                    {{ Content.P' . $personId . '.Input.Missing }} Stunden
                {% else %}
                    &nbsp;
                {% endif %}')
                ->stylePaddingLeft('5px')
            );
            $Slice->addElement((new Element())
                ->setContent('Fehlzeiten Unterricht unentschuldigt: {% if(Content.P' . $personId . '.Input.Bad.Missing is not empty) %}
                    {{ Content.P' . $personId . '.Input.Bad.Missing }} Stunden
                {% else %}
                    &nbsp;
                {% endif %}')
                ->stylePaddingLeft('5px')
                ->stylePaddingBottom('3px')
            );
        }

        $Slice->addElement((new Element())
            ->setContent('Bemerkungen:')
            ->styleTextUnderline()
            ->stylePaddingLeft('5px')
        );
        $Slice->addElement((new Element())
            ->setContent('{% if(Content.P' . $personId . '.Input.RemarkWithoutTeam is not empty) %}
                        {{ Content.P' . $personId . '.Input.RemarkWithoutTeam|nl2br }}
                    {% else %}
                        &nbsp;
                    {% endif %}')
            ->styleAlignJustify()
            ->stylePaddingLeft('5px')
            ->stylePaddingRight('5px')
        );

        return $Slice;
    }

    /**
     * @param      $personId
     * @param bool $isChairPerson Abgangszeugnis
     * @param bool $hasExtraSignCare
     * @param bool $hasSignParentsPart
     *
     * @return Slice
     */
    protected function getIndividuallySignPart($personId, $isChairPerson = false, $hasExtraSignCare = false, $hasSignParentsPart = true)
    {
        $Slice = (new Slice());

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('{% if( Content.P' . $personId . '.Company.Address.City.Name is not empty) %}
                            {{ Content.P' . $personId . '.Company.Address.City.Name }}
                        {% else %}
                            &nbsp;
                        {% endif %}')
                ->styleAlignCenter()
                ->styleBorderBottom('0.5px')
                , '35%')
            ->addElementColumn((new Element())
                , '30%')
            ->addElementColumn((new Element())
                ->setContent('{{ Content.P' . $personId . '.Input.Date }}')
                ->styleAlignCenter()
                ->styleBorderBottom('0.5px')
                , '35%')
        )
            ->styleMarginTop('25px')
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
            );

        $marginTop = '40px';
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleAlignCenter()
                ->styleMarginTop($marginTop)
                ->styleBorderBottom('0.5px')
                , '35%')
            ->addElementColumn((new Element())
                , '30%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleAlignCenter()
                ->styleMarginTop($marginTop)
                ->styleBorderBottom('0.5px')
                , '35%')
        );
        if($isChairPerson){
            // Abgangszeugnis
            $Slice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent(
                        '{% if(Content.P' . $personId . '.Leader.Description is not empty) %}
                            {{ Content.P' . $personId . '.Leader.Description }}
                        {% else %}
                            Vorsitzende/r des Prüfungsausschusses
                        {% endif %}'
                    )
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
                    , '35%')
                ->addElementColumn((new Element())
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if(Content.P' . $personId . '.Headmaster.Description is not empty) %}
                            {{ Content.P' . $personId . '.Headmaster.Description }}
                        {% else %}
                            Schulleiter/in
                        {% endif %}'
                    )
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
                    , '35%')
            );
            $Slice->addSection((new Section())
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
                ->addElementColumn((new Element())
                    , '30%')
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
            );
        } else {
            // Standard Zeugnis
            $Slice->addSection((new Section())
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
            );
            $Slice->addSection((new Section())
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
            );

            if ($hasSignParentsPart) {
                $Slice->addElement((new Element())
                    ->setContent('&nbsp;')
                    ->styleHeight('30px')
                );

                $Slice->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Zur Kenntnis genommen:')
                        , '27%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleBorderBottom('0.5px')
                        , '73%'
                    )
                );

                if ($hasExtraSignCare) {
                    // Auszubilende/r / Arbeitgeber/in
                    $Slice->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            , '27%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Eltern')
                            ->styleTextSize('10px')
                            ->styleAlignCenter()
                            , '38%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Auszubilende/r / Arbeitgeber/in')
                            ->styleTextSize('10px')
                            ->styleAlignCenter()
                        )
                    );

                } else {
                    $Slice->addSection((new Section())
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
            }
        }

        return $Slice;
    }

    /**
     * @param $personId
     *
     * @return Slice
     */
    protected function getBottomInformation($personId)
    {

        $Slice = new Slice();

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('
                    {{ Content.P' . $personId . '.Company.Address.City.Name }}, {{ Content.P' . $personId . '.Input.Date }}'
                )
                ->styleAlignCenter()
                ->styleBorderBottom('0.5px')
                , '60%'
            )
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleBorderBottom('0.5px')
                , '40%'
            )
        );

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Ort, Datum')
                ->styleAlignCenter()
                ->styleTextSize('10px')
                , '60%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                        {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                    {% else %}
                        Klassenlehrer/in
                    {% endif %}
                ')
                ->styleAlignCenter()
                ->styleTextSize('10px')
                , '40%'
            )
        );

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleAlignCenter()
                ->styleTextSize('10px')
                , '60%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if(Content.P' . $personId . '.DivisionTeacher.Name is not empty) %}
                        {{ Content.P' . $personId . '.DivisionTeacher.Name }}
                    {% endif %}
                ')
                ->styleAlignCenter()
                ->styleTextSize('10px')
                , '40%'
            )
        );

        $Slice->addElement((new Element())
            ->setContent('&nbsp;')
            ->styleHeight('18px')
        );

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Zur Kenntnis genommen:')
                , '27%'
            )
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleBorderBottom('0.5px')
                , '73%'
            )
        );

        $Slice->addSection((new Section())
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
        return $Slice;
    }

    /**
     * @param string $PaddingTop
     * @param string $Content
     *
     * @return Slice
     */
    protected function getBsInfo($PaddingTop = '20px', $Content = '')
    {
        $Slice = new Slice();
        $Slice->stylePaddingTop($PaddingTop);
        $Slice->addElement((new Element())
                ->setContent($Content)
                ->styleTextSize('9.5px')
        );
        return $Slice;
    }

    /**
     * @param $personId
     * @param string $MarginTop
     *
     * @return Slice
     */
    public function getTransfer($personId, $MarginTop = '0px')
    {
        $TransferSlice = (new Slice());
        $TransferSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Versetzungsvermerk:')
                ->styleTextUnderline()
                ->stylePaddingLeft('5px')
                ->stylePaddingTop('5px')
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
            ->styleMarginTop($MarginTop)
            ->styleBorderLeft('0.5px')
            ->styleBorderRight('0.5px')
            ->styleBorderBottom('0.5px');
        return $TransferSlice;
    }

    /**
     * @param string $height
     *
     * @return Slice
     */
    public function getSpace($height = '20px')
    {
        return (new Slice())->styleHeight($height);
    }

    /**
     * @param $personId
     * @return Slice
     */
    protected function getOccupation($personId)
    {
        if ($this->getLevel() == 3) {
            $content = 'Beruf' . ' {% if(Content.P' . $personId . '.Student.TechnicalCourse is not empty) %}
                    {{ Content.P' . $personId . '.Student.TechnicalCourse }}
                {% else %}
                    ---
                {% endif %}';
        } else {
            $content = '&nbsp;';
        }

        return (new Slice())
            ->addElement((new Element())
                ->setContent($content)
                ->styleTextSize('20px')
                ->stylePaddingTop('20px')
                ->styleAlignCenter()
            );
    }

    /**
     * @param $personId
     *
     * @return Slice
     */
    protected function getYearGradeAverage($personId)
    {
        return (new Slice)
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Jahresnoten')
                    ->styleAlignCenter()
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Jahresnote über die im Unterricht erbrachten Leistungen')
                    ->styleMarginTop('10px')
                    ->stylePaddingBottom('1px')
                    ->styleBorderBottom('0.5px')
                , '91%')
                ->addElementColumn((new Element())
                    // muss aktuell per Hand eingegeben werden, da die Berechnung nicht bekannt ist
                    ->setContent('
                        {% if(Content.P' . $personId . '.Input.YearGradeAverageLesson_Average is not empty) %}
                            {{ Content.P' . $personId . '.Input.YearGradeAverageLesson_Average }}
                        {% else %}
                            &ndash;
                        {% endif %}')
                    ->styleAlignCenter()
                    ->styleMarginTop('8px')
                    ->stylePaddingTop('2px')
                    ->stylePaddingBottom('1.5px')
                    ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
                , '9%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Jahresnote über die in der praktischen Ausbildung erbrachten Leistungen')
                    ->styleMarginTop('10px')
                    ->stylePaddingBottom('1px')
                    ->styleBorderBottom('0.5px')
                    , '91%')
                ->addElementColumn((new Element())
                    // muss aktuell per Hand eingegeben werden, da die Berechnung nicht bekannt ist
                    ->setContent('
                        {% if(Content.P' . $personId . '.Input.YearGradeAveragePractical_Average is not empty) %}
                            {{ Content.P' . $personId . '.Input.YearGradeAveragePractical_Average }}
                        {% else %}
                            &ndash;
                        {% endif %}')
                    ->styleAlignCenter()
                    ->styleMarginTop('8px')
                    ->stylePaddingTop('2px')
                    ->stylePaddingBottom('1.5px')
                    ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
                    , '9%')
            );
    }

    /**
     * @param $personId
     * @param string $height
     *
     * @return Slice
     */
    protected function getMidTerm($personId, $height = '100px')
    {
        if ($this->getLevel() == 2) {
            return (new Slice)
                ->styleHeight($height)
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('ZWISCHENPRÜFUNG')
                        ->styleAlignCenter()
                        ->styleMarginTop('25px')
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Schriftlicher Prüfungsteil')
                        ->styleMarginTop('10px')
                        ->stylePaddingBottom('1px')
                        ->styleBorderBottom('0.5px')
                        , '91%')
                    ->addElementColumn((new Element())
                        // muss aktuell per Hand eingegeben werden, da die Berechnung nicht bekannt ist
                        ->setContent('
                        {% if(Content.P' . $personId . '.Input.WrittenExam_Grade is not empty) %}
                            {{ Content.P' . $personId . '.Input.WrittenExam_Grade }}
                        {% else %}
                            &ndash;
                        {% endif %}')
                        ->styleAlignCenter()
                        ->styleMarginTop('8px')
                        ->stylePaddingTop('2px')
                        ->stylePaddingBottom('1.5px')
                        ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
                        , '9%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Praktischer Prüfungsteil')
                        ->styleMarginTop('10px')
                        ->stylePaddingBottom('1px')
                        ->styleBorderBottom('0.5px')
                        , '91%')
                    ->addElementColumn((new Element())
                        // muss aktuell per Hand eingegeben werden, da die Berechnung nicht bekannt ist
                        ->setContent('
                        {% if(Content.P' . $personId . '.Input.PracticalExam_Grade is not empty) %}
                            {{ Content.P' . $personId . '.Input.PracticalExam_Grade }}
                        {% else %}
                            &ndash;
                        {% endif %}')
                        ->styleAlignCenter()
                        ->styleMarginTop('8px')
                        ->stylePaddingTop('2px')
                        ->stylePaddingBottom('1.5px')
                        ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
                        , '9%')
                );
        } else {
            return $this->getSpace($height);
        }
    }

    /**
     * @param $personId
     * @param TblCertificate $tblCertificate
     *
     * @return Slice
     */
    protected function getPracticalCare($personId, TblCertificate $tblCertificate)
    {
        $tblTechnicalCourse = null;
        if(($tblPerson = Person::useService()->getPersonById($personId))){
            if(($tblStudent = Student::useService()->getStudentByPerson($tblPerson))){
                if(($tblTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())){
                    $tblTechnicalCourse = $tblTechnicalSchool->getServiceTblTechnicalCourse();
                }
            }
        }

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($tblCertificate, $tblTechnicalCourse);

//        $Subject = array();
//
//        if (!empty($tblCertificateSubjectAll)) {
//            $SubjectStructure = array();
//            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
//                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
//                if ($tblSubject) {
//                    $RankingString = str_pad($tblCertificateSubject->getRanking(), 2 ,'0', STR_PAD_LEFT);
//                    $LaneString = str_pad($tblCertificateSubject->getLane(), 2 ,'0', STR_PAD_LEFT);
//
//                    if($tblCertificateSubject->getRanking() == 15) {
//
//                        // Wird immer ausgewiesen (Fach wird nicht abgebildet)
//                        $SubjectStructure[$RankingString.$LaneString]['SubjectAcronym']
//                            = $tblSubject->getAcronym();
//                        $SubjectStructure[$RankingString.$LaneString]['SubjectName']
//                            = $tblSubject->getName();
//                    }
//                }
//            }
//            $Subject = current($SubjectStructure);
//        }
//
//        $TextSize = '14px';
//        $TextSizeSmall = '8px';

        $slice = new Slice();
        $slice->styleBorderAll('0.5px');
        $slice->styleMarginTop('30px');
        $slice->stylePaddingTop('10px');
        $slice->stylePaddingBottom('10px');
        $slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>Praktische Ausbildung</b>')
                ->stylePaddingLeft('5px')
                , 'auto'
            )
//            ->addElementColumn((new Element())
//                ->setContent(empty($Subject) ? '&ndash;'
//                    :'{% if(Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] is not empty) %}
//                                 {{ Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] }}
//                             {% else %}
//                                 &ndash;
//                             {% endif %}')
//                    ->styleAlignCenter()
//                    ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
//                    ->stylePaddingTop(empty($Subject) ? '2px'
//                        :'{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
//                                    and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
//                                ) %}
//                                     5.3px
//                                 {% else %}
//                                     2px
//                                 {% endif %}')
//                    ->stylePaddingBottom(empty($Subject) ? '2px'
//                        :'{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
//                                    and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
//                                ) %}
//                                     6px
//                                 {% else %}
//                                     2px
//                                 {% endif %}')
//                    ->styleTextSize(empty($Subject) ? $TextSize
//                        :'{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
//                                    and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
//                                ) %}
//                                     ' . $TextSizeSmall . '
//                                 {% else %}
//                                     ' . $TextSize . '
//                                 {% endif %}'
//                    )
//                    , '9%'
//                )
        );

        $slice->addElement((new Element())->styleMarginTop('15px'));

        for ($i = 1; $i <= 4; $i++) {
            $section = new Section();
            // 3 Zeilen werden immer abgebildet
//            if($i <= 3){
//                $section
//                    ->addElementColumn((new Element())
//                        ->setContent('
//                    {% if(Content.P' . $personId . '.Input.Subarea' . $i . ' is not empty) %}
//                        {{ Content.P' . $personId . '.Input.Subarea' . $i . ' }}
//                    {% else %}
//                        &lt;TEILBEREICH&gt;
//                    {% endif %}
//                    (Dauer:
//                    {% if(Content.P' . $personId . '.Input.SubareaTime' . $i . ' is not empty) %}
//                        {{ Content.P' . $personId . '.Input.SubareaTime' . $i . ' }}
//                    {% else %}
//                        &lt;X&gt;
//                    {% endif %}
//                    Wochen)
//
//                    &nbsp;&nbsp;&nbsp;
//                    Fehlzeiten entschuldigt:
//                    {% if(Content.P' . $personId . '.Input.SubareaExcusedDays' . $i . ' is not empty) %}
//                        {{ Content.P' . $personId . '.Input.SubareaExcusedDays' . $i . ' }}
//                    {% else %}
//                        &lt;X&gt;
//                    {% endif %}
//
//                    &nbsp;&nbsp;&nbsp;
//                    Fehlzeiten unentschuldigt:
//                    {% if(Content.P' . $personId . '.Input.SubareaUnexcusedDays' . $i . ' is not empty) %}
//                        {{ Content.P' . $personId . '.Input.SubareaUnexcusedDays' . $i . ' }}
//                    {% else %}
//                        &lt;X&gt;
//                    {% endif %}
//                ')
//                        ->styleTextSize('11px')
//                        ->stylePaddingLeft('5px')
//                    );
//            } else {
                // Zeile 4 optional (wenn "Teilbereich" eingetragen wurde)
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if(Content.P' . $personId . '.Input.Subarea' . $i . ' is not empty) %}
                                {{ Content.P' . $personId . '.Input.Subarea' . $i . ' }},
                                Dauer der Ausbildung 
                                {% if(Content.P' . $personId . '.Input.SubareaTimeH' . $i . ' is not empty) %}
                                    {{ Content.P' . $personId . '.Input.SubareaTimeH' . $i . ' }}
                                {% else %}
                                    &lt;X&gt;
                                {% endif %}
                                Stunden
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleTextSize('11px')
                        ->stylePaddingLeft('5px')
                    , '70%');
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if(Content.P' . $personId . '.Input.SubareaTimeHDone' . $i . ' is not empty) %}
                                davon anwesend
                                {{ Content.P' . $personId . '.Input.SubareaTimeHDone' . $i . ' }}
                                Stunden
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleTextSize('11px')
                        ->stylePaddingRight('20px')
                        ->styleAlignRight()
                , '30%');
//            }
            $slice->addSection($section);
        }

        return $slice;
    }

    /**
     * @param $personId
     *
     * @return Slice
     */
    protected function getAbsence($personId)
    {
        return (new Slice())
            ->styleBorderBottom('0.5px')
            ->styleBorderLeft('0.5px')
            ->styleBorderRight('0.5px')
            ->stylePaddingLeft('5px')
            ->stylePaddingTop('3px')
            ->stylePaddingBottom('3px')
            ->styleTextSize('13px')
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Fehlzeiten Unterricht entschuldigt: ')
                    , '32%')
                ->addElementColumn((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Input.Missing is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                    , '10%')
                ->addElementColumn((new Element())
                    ->setContent('Fehlzeiten Unterricht unentschuldigt: ')
                    , '35%')
                ->addElementColumn((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Input.Bad.Missing is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Bad.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                )
            );
    }

    /**
     * @param $personId
     *
     * @return Page
     */
    public function getDiplomaSecondPage($personId)
    {
        return (new Page())
            ->addSlice($this->getSecondPageHead($personId, 'Abschlusszeugnis', false))
            ->addSlice($this->getSubjectLinePerformance())
            ->addSlice($this->getSubjectLineDuty('10px'))
            ->addSlice($this->getSubjectLineAcrossAbs($personId, $this->getCertificateEntity(), 'Berufsübergreifender Bereich', 1, 5, 1, 4, '150px'))
            ->addSlice($this->getSubjectLineAcrossAbs($personId, $this->getCertificateEntity(), 'Berufsbezogener Bereich', 1, 14, 5, 12, '320px'))
            ->addSlice($this->getSubjectLineAcrossAbs($personId, $this->getCertificateEntity(), 'Wahlpflichtbereich', 1, 2, 13, 13, '80px'))
            ->addSlice($this->getPraktika($personId, $this->getCertificateEntity(), true))
            ->addSlice($this->getDescriptionBsContent($personId, '85px'))
            ->addSlice((new Slice())->addElement((new Element())
                ->setContent('&nbsp;')
                ->stylePaddingTop('11px')
            ))
            ->addSlice($this->getBsInfo('23px',
                'NOTENSTUFEN: sehr gut (1), gut (2), befriedigend (3), ausreichend (4), mangelhaft (5), ungenügend (6)'))
            ;
    }

    /**
     * @param bool $IsLogo
     * @return Slice
     */
    protected function getSchoolHeadFirstSlice(bool $IsLogo): Slice
    {
        $name = '';
        $secondLine = '';
        // get company name
        if (($tblCompany = $this->getTblCompany())) {
            $name = $tblCompany->getName();
            $secondLine = $tblCompany->getExtendedName();
        }

        $Slice = (new Slice());
        if ($IsLogo) {
            $Slice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '61%')
                ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                    '214px', '66px'))
                    ->styleAlignRight()
                    ->stylePaddingTop('20px')
                    , '39%')
            );
            $Slice->addElement((new Element())
                ->setContent($name ? $name : '&nbsp;')
                ->styleAlignRight()
                ->styleTextSize('22px')
                ->styleHeight('28px')
                ->stylePaddingTop('40px')
            );
            $Slice->addElement((new Element())
                ->setContent($secondLine ? $secondLine : '&nbsp;')
                ->styleAlignRight()
                ->styleTextSize('18px')
                ->styleHeight('42px')
//            ->stylePaddingTop('20px')
            );
        } else {
            $Slice->addElement((new Element())
                ->setContent($name ? $name : '&nbsp;')
                ->styleAlignCenter()
                ->styleTextSize('22px')
                ->styleHeight('28px')
                ->stylePaddingTop('25px')
            );
            $Slice->addElement((new Element())
                ->setContent($secondLine ? $secondLine : '&nbsp;')
                ->styleAlignCenter()
                ->styleTextSize('18px')
                ->styleHeight('42px')
//            ->stylePaddingTop('20px')
            );
        }

        $Slice->addSection($this->getIndividuallyLogo($this->isSample()));
        return $Slice;
    }
}
