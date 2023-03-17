<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateSubject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;

/**
 * Class Fs
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
abstract class FsStyle extends Certificate
{

    /**
     * @param        $personId
     * @param string $CertificateName
     *
     * @return Slice
     */
    protected function getSchoolHead($personId, $CertificateName = 'Halbjahreszeugnis', $isChangeableCertificateName = false, $IsLogo = false)
    {

        $name = '';
        $secondLine = '';
        // get company name
        if (($tblCompany = $this->getTblCompany())) {
            $name = $tblCompany->getName();
            $secondLine = $tblCompany->getExtendedName();
        }

        $Slice = (new Slice());
        if($IsLogo){
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
            ->setContent('der Fachschule {% if(Content.P' . $personId . '.Input.FsDestination is not empty) %}
                    {{ Content.P' . $personId . '.Input.FsDestination }}
                {% endif %}')
            ->stylePaddingTop('4px')
            ->styleAlignCenter()
            ->styleTextSize('22px')
        );
        $Slice->addElement((new Element())
            ->setContent('{% if(Content.P' . $personId . '.Input.SubjectArea is not empty) %}
                    Fachrichtung {{ Content.P' . $personId . '.Input.SubjectArea }}
                {% endif %}')
            ->stylePaddingTop('10px')
            ->styleAlignCenter()
            ->styleTextSize('18px')
        );
        $Slice->addElement((new Element())
            ->setContent('{% if(Content.P' . $personId . '.Input.Focus is not empty) %}
                    Schwerpunkt {{ Content.P' . $personId . '.Input.Focus }}
                {% endif %}')
            ->stylePaddingTop('10px')
            ->styleAlignCenter()
            ->styleTextSize('18px')
        );

        return $Slice;
    }

    /**
     * @param        $personId
     * @param bool   $isFhr
     * @param string $CertificateName
     * @param bool   $IsLogo
     *
     * @return Slice
     */
    protected function getSchoolHeadAbs($personId, $isFhr = false, $CertificateName = 'Abschlusszeugnis', $IsLogo = true)
    {

        $name = '';
        $secondLine = '';
        // get company name
        if (($tblCompany = $this->getTblCompany())) {
            $name = $tblCompany->getName();
            $secondLine = $tblCompany->getExtendedName();
        }

        $Slice = (new Slice());
        if($IsLogo){
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
        $Slice->addElement((new Element())
            ->setContent($CertificateName)
            ->styleAlignCenter()
            ->styleTextSize('30px')
        );
        $Slice->addElement((new Element())
            ->setContent('der Fachschule')
            ->styleAlignCenter()
            ->styleTextSize('18px')
            ->stylePaddingTop('7px')
        );
        if ($isFhr) {
            $Slice->addElement((new Element())
                ->setContent('und')
                ->styleAlignCenter()
                ->stylePaddingTop('5px')
            );
            $Slice->addElement((new Element())
                ->setContent('Zeugnis der Fachhochschulreife')
                ->styleAlignCenter()
                ->styleTextSize('22px')
                ->stylePaddingTop('5px')
            );
        }

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
            ->setContent('der Fachschule ')
            ->stylePaddingTop('4px')
            ->styleAlignCenter()
            ->styleTextSize('22px')
        );

        return $Slice;
    }

    /**
     * @param $personId
     * @param string $period
     *
     * @return Slice
     */
    protected function getStudentHead($personId, $period = 'Schulhalbjahr', $LastLineText = '', $isPreText = false)
    {

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
            {% if(Content.P'.$personId.'.Person.Data.Name.Salutation is not empty) %}
                {{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
            {% else %}
                Frau/Herr
            {% endif %}
            {{ Content.P' . $personId . '.Person.Data.Name.First }}
            {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
            ->styleBorderBottom('0.5px')
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
     * @param bool $isFhr
     *
     * @return Slice
     */
    protected function getStudentHeadAbs($personId, $isFhr = false)
    {

        $Slice = new Slice();

        $Slice->stylePaddingTop('30px');

        $Slice->addElement((new Element())
            ->setContent('
            {% if(Content.P'.$personId.'.Person.Data.Name.Salutation is not empty) %}
                {{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
            {% else %}
                Frau/Herr
            {% endif %}
            {{ Content.P' . $personId . '.Person.Data.Name.First }}
            {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
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
            ->stylePaddingTop('20px')
        );
        $Slice->addElement((new Element())
            ->setContent('Fachschule {% if(Content.P' . $personId . '.Input.FsDestination is not empty) %}
                    {{ Content.P' . $personId . '.Input.FsDestination }}
                {% else %}
                    ---
                {% endif %}')
            ->styleAlignCenter()
            ->styleTextSize('20px')
            ->styleTextBold()
            ->stylePaddingTop('15px')
        );

        $Slice->addElement((new Element())
            ->setContent('
                {% if(Content.P' . $personId . '.Input.SubjectArea is not empty) %}
                    {{ Content.P' . $personId . '.Input.SubjectArea }}{% if(Content.P' . $personId . '.Input.Focus is not empty) %}, 
                    {% endif %}
                {% endif %}
                {% if(Content.P' . $personId . '.Input.Focus is not empty) %}
                    {{ Content.P' . $personId . '.Input.Focus }}
                {% endif %}
                ')
            ->styleAlignCenter()
            ->styleTextSize('20px')
            ->styleTextBold()
            ->stylePaddingTop('2px')
        );
        $GenderString = 'Er/Sie';
        if(($tblPerson = Person::useService()->getPersonById($personId))){
            if(($tblGender = $tblPerson->getGender())){
                if($tblGender->getName() == 'Männlich'){
                    $GenderString = 'Er';
                } elseif($tblGender->getName() == 'Weiblich') {
                    $GenderString = 'Sie';
                }
            }
        }

        $Slice->addElement((new Element())
            ->setContent('in
                {% if(Content.P' . $personId . '.Student.TenseOfLesson is not empty) %}
                    {{ Content.P' . $personId . '.Student.TenseOfLesson }}
                {% else %}
                    ---
                {% endif %} besucht und im Schuljahr
                {% if(Content.P' . $personId . '.Division.Data.Year is not empty) %}
                    {{ Content.P' . $personId . '.Division.Data.Year }}
                {% else %}
                    ---
                {% endif %}
                <br/>die Abschlussprüfung bestanden. '.$GenderString.'
                ist berechtigt, die Berufsbezeichnung')
            ->styleAlignCenter()
            ->styleTextSize('16px')
            ->stylePaddingTop('15px')
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
            ->stylePaddingTop('30px')
        );

        if(!$isFhr){
            $Slice->addElement((new Element())
                ->setContent('zu führen.' . $this->setSup('1)'))
                ->styleAlignCenter()
                ->styleTextSize('16px')
                ->stylePaddingTop('30px')
            );
        } else {
            $Slice->addElement((new Element())
                ->setContent('zu führen.
                {% if(Content.P'.$personId.'.Person.Data.Name.Salutation is not empty) %}
                {{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
                {% else %}
                    Frau/Herr
                {% endif %}
                {{ Content.P' . $personId . '.Person.Data.Name.First }}
                {{ Content.P' . $personId . '.Person.Data.Name.Last }}
                 hat die Prüfung zum' )
                ->styleAlignCenter()
                ->styleTextSize('16px')
                ->stylePaddingTop('30px')
            );
            $Slice->addElement((new Element())
                ->setContent('Erwerb der Fachhochschulreife bestanden und den Bildungsgang' )
                ->styleAlignCenter()
                ->styleTextSize('16px')
            );
            $Slice->addElement((new Element())
                ->setContent('an der Fachschule erfolgreich abgeschlossen.' . $this->setSup('1)') . ' Die' )
                ->styleAlignCenter()
                ->styleTextSize('16px')
            );
            $Slice->addElement((new Element())
                ->setContent('Fachhochschulreife')
                ->styleAlignCenter()
                ->styleTextSize('20px')
                ->styleTextBold()
                ->stylePaddingTop('10px')
                ->stylePaddingBottom('10px')
            );
            $Slice->addElement((new Element())
                ->setContent('wird zuerkannt. Damit berechtigt dieses Zeugnis zum Studium an einer Fachhochschule' )
                ->styleAlignCenter()
                ->styleTextSize('16px')
            );
            $Slice->addElement((new Element())
            ->setContent('in der Bundesrepublik Deutschland.' . $this->setSup('2)') )
                ->styleAlignCenter()
                ->styleTextSize('16px')
                ->stylePaddingBottom('10px')
            );
            $Slice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Durchschnittsnote' . $this->setSup('3)') . ':')
                    ->styleMarginTop('15px')
                , '20%')

                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                , '5%')
                ->addElementColumn((new Element())
                    ->setContent('{% if(Content.P'.$personId.'.Input.AddEducation_Average is not empty) %}
                             {{ Content.P'.$personId.'.Input.AddEducation_Average }}
                         {% else %}
                             &ndash;
                         {% endif %}')
                    ->styleAlignCenter()
                    ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
                    ->styleMarginTop('15px')
                    ->stylePaddingTop('2px')
                    ->stylePaddingBottom('1.5px')
                , '14%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                , '10%')
                ->addElementColumn((new Element())
                    ->setContent('{% if(Content.P'.$personId.'.Input.AddEducation_AverageInWord is not empty) %}
                             {{ Content.P'.$personId.'.Input.AddEducation_AverageInWord }}
                         {% else %}
                             &ndash;
                         {% endif %}')
                    ->styleAlignCenter()
                    ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
                    ->styleMarginTop('15px')
                    ->stylePaddingTop('2px')
                    ->stylePaddingBottom('1.5px')
                , '50%')
            );
            $Slice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '25%')
                ->addElementColumn((new Element())
                    ->setContent('in Ziffern')
                    ->styleAlignCenter()
                    ->stylePaddingTop('4px')
                    ->styleTextSize('10px')
                    , '14%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '10%')
                ->addElementColumn((new Element())
                    ->setContent('in Worten')
                    ->styleAlignCenter()
                    ->stylePaddingTop('4px')
                    ->styleTextSize('10px')
                    , '50%')
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
            {% if(Content.P'.$personId.'.Person.Data.Name.Salutation is not empty) %}
                {{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
            {% else %}
                Frau/Herr
            {% endif %}
            {{ Content.P' . $personId . '.Person.Data.Name.First }}
            {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
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
                {% endif %} die')
            ->styleAlignCenter()
            ->styleTextSize('16px')
            ->stylePaddingTop('10px')
        );
        $Slice->addElement((new Element())
            ->setContent('Fachschule {% if(Content.P' . $personId . '.Input.FsDestination is not empty) %}
                    {{ Content.P' . $personId . '.Input.FsDestination }}
                {% endif %}')
            ->styleAlignCenter()
            ->styleTextSize('20px')
            ->styleTextBold()
            ->stylePaddingTop('10px')
        );
        $Slice->addElement((new Element())
            ->setContent('{% if(Content.P' . $personId . '.Input.SubjectArea is not empty) %}
                    {{ Content.P' . $personId . '.Input.SubjectArea }}{% if(Content.P' . $personId . '.Input.Focus is not empty) %}, {{ Content.P' . $personId . '.Input.Focus }}{% endif %}
                {% endif %}')
            ->styleAlignCenter()
            ->styleTextSize('20px')
            ->styleTextBold()
        );
        $Slice->addElement((new Element())
            ->setContent('in 
                {% if(Content.P' . $personId . '.Student.TenseOfLesson is not empty) %}
                    {{ Content.P' . $personId . '.Student.TenseOfLesson }}
                {% else %}
                    ---
                {% endif %} 
                besucht und folgende Leistungen erreicht:')
            ->styleAlignCenter()
            ->styleTextSize('16px')
            ->stylePaddingTop('15px')
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
     * //ToDO maybe remove if not used
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

        // SubjectList By EducationType
        $tblCertificateSubjectAll = $this->getCertificateSubjectByPerson($personId, $tblCertificate);
        $tblGradeList = $this->getGrade();

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

            $TextSize = '14px';
            $TextSizeSmall = '8px';
            foreach ($SubjectList as $SubjectListAlign) {
                // Sort Lane-Ranking (1,2...)
                // 2 Subject in one Line
                $Slice = $this->SubjectTwoLane($Slice, $personId, $SubjectListAlign, $TextSize, $TextSizeSmall);
            }
        }

        $Slice->styleHeight($Height);

        return $Slice;
    }

    /**
     * @return Slice
     * //ToDO maybe remove if not used
     */
    protected function getSubjectLinePerformance()
    {

        $Slice = (new Slice());
        $Slice->addElement((new Element())
            ->setContent('Leistungen')
            ->styleAlignCenter()
            ->styleTextSize('20px')
            ->styleTextBold()
            ->stylePaddingTop('10px')
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
     * @param int            $personId
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
        $DisplaySubjectAmount = 10, $Height = 'auto', $SubjectRankingFrom = 5, $SubjectRankingTill = 14)
    {
        $Slice = (new Slice());

        $Slice->addElement((new Element())
            ->setContent($Title)
            ->styleAlignCenter()
            ->stylePaddingTop('20px')
            ->stylePaddingBottom('10px')
        );

        // SubjectList By EducationType
        $tblCertificateSubjectAll = $this->getCertificateSubjectByPerson($personId, $tblCertificate);
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
                $Slice = $this->SubjectOneLane($Slice, $personId, $Subject, $TextSize, $TextSizeSmall);
            }
        }

        if($CountSubjectMissing > 0){
            $Slice = $this->getEmptySubjectField($Slice, $CountSubjectMissing);
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
     * @param string $Height
     * @param int $SubjectRankingFrom
     * @param int $SubjectRankingTill
     * @param string $paddingTop
     *
     * @return Slice
     */
    protected function getSubjectLineBaseAbg($personId, TblCertificate $tblCertificate, $Title = '&nbsp;', $StartSubject = 1,
        $DisplaySubjectAmount = 10, $Height = 'auto', $SubjectRankingFrom = 5, $SubjectRankingTill = 14, $paddingTop = '20px')
    {
        $Slice = (new Slice());

        $Slice->addElement((new Element())
            ->setContent($Title)
            ->styleAlignCenter()
            ->stylePaddingTop($paddingTop)
            ->stylePaddingBottom('0px')
        );

        // SubjectList By EducationType
        $tblCertificateSubjectAll = $this->getCertificateSubjectByPerson($personId, $tblCertificate);
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

    /**
     * @param int    $personId
     * @param string $CertificateName
     * @param bool   $isChangeableCertificateName
     *
     * @return Slice
     */
    protected function getSecondPageHead($personId, $CertificateName = 'Halbjahresinformation', $page = '2', $isChangeableCertificateName = false)
    {

        $Slice = new Slice();

        if($isChangeableCertificateName){
            $Slice->addElement((new Element())
                ->setContent('
                {% if(Content.P' . $personId . '.Input.CertificateName is not empty) %}
                    {{ Content.P' . $personId . '.Input.CertificateName }}
                {% else %}
                '.$CertificateName.'
                {% endif %}' . ' für ' .
            '{% if(Content.P'.$personId.'.Person.Data.Name.Salutation is not empty) %}
                {{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
            {% else %}
                Frau/Herr
            {% endif %}'
            .'{{ Content.P' . $personId . '.Person.Data.Name.First }}
            {{ Content.P' . $personId . '.Person.Data.Name.Last }},
            geboren am {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday }} - 2. Seite')
                ->styleAlignCenter()
//            ->styleTextSize('16px')
                ->stylePaddingTop('20px')
                ->styleBorderBottom('0.5px')
            );
        } else {
            $Slice->addElement((new Element())
                ->setContent($CertificateName.
                ' für '.
                '{% if(Content.P'.$personId.'.Person.Data.Name.Salutation is not empty) %}
                    {{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
                {% else %}
                    Frau/Herr
                {% endif %}'.
                '{{ Content.P' . $personId . '.Person.Data.Name.First }}
                {{ Content.P' . $personId . '.Person.Data.Name.Last }},
                geboren am {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday }} - ' . $page . '. Seite')
                ->styleAlignCenter()
//            ->styleTextSize('16px')
                ->stylePaddingTop('20px')
                ->styleBorderBottom('0.5px')
            );
        }



        return $Slice;
    }

    /**
     * @param int            $personId
     * @param TblCertificate $tblCertificate
     * @param string         $Height
     *
     * @return Slice
     */
    protected function getSubjectLineChosen($personId, TblCertificate $tblCertificate, $Height = '110px')
    {
        $Slice = (new Slice());

        // JobEducation
        $Slice->addElement((new Element())
            ->setContent('Wahlpflichtbereich')
            ->styleAlignCenter()
            ->stylePaddingTop('20px')
            ->stylePaddingBottom('10px')
        );

        // SubjectList By EducationType
        $tblCertificateSubjectAll = $this->getCertificateSubjectByPerson($personId, $tblCertificate);
        $tblGradeList = $this->getGrade();
        // Anzahl der Abzubildenden Einträge (auch ohne Fach)
        $CountSubjectMissing = 2;
        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if ($tblSubject) {
                    $RankingString = str_pad($tblCertificateSubject->getRanking(), 2 ,'0', STR_PAD_LEFT);
                    $LaneString = str_pad($tblCertificateSubject->getLane(), 2 ,'0', STR_PAD_LEFT);

                    if ($tblCertificateSubject->getRanking() == 15
                        || $tblCertificateSubject->getRanking() == 16
                    ) {

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
                if($CountSubjectMissing !== 0){
                    $SubjectList[] = $Subject;
                    $CountSubjectMissing--;
                }
            }

            $TextSize = '14px';
            $TextSizeSmall = '8px';
            foreach ($SubjectList as $Subject) {
                // Jedes Fach auf separate Zeile
                $Slice = $this->SubjectOneLane($Slice, $personId, $Subject, $TextSize, $TextSizeSmall);
            }
        }

        if($CountSubjectMissing > 0){
            $Slice = $this->getEmptySubjectField($Slice, $CountSubjectMissing);
        }

        $Slice->styleHeight($Height);

        return $Slice;
    }

    /**
     * @param int    $personId
     * @param TblCertificate $tblCertificate
     * @param string $Height
     *
     * @return Slice
     */
    protected function getSubjectLineJobEducation($personId, TblCertificate $tblCertificate, $Height = '80px')
    {
        $Slice = (new Slice());

        // JobEducation
        $Slice->addElement((new Element())
            ->setContent('Berufspraktische Ausbildung')
            ->styleAlignCenter()
            ->stylePaddingTop('20px')
            ->stylePaddingBottom('10px')
        );

        // SubjectList By EducationType
        $tblCertificateSubjectAll = $this->getCertificateSubjectByPerson($personId, $tblCertificate);
        $tblGradeList = $this->getGrade();
        // Anzahl der Abzubildenden Einträge (auch ohne Fach)
        $CountSubjectMissing = 1;
        $TextSize = '14px';
        $TextSizeSmall = '8px';

        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if ($tblSubject) {
                    $RankingString = str_pad($tblCertificateSubject->getRanking(), 2 ,'0', STR_PAD_LEFT);
                    $LaneString = str_pad($tblCertificateSubject->getLane(), 2 ,'0', STR_PAD_LEFT);

                    if($tblCertificateSubject->getRanking() == 17) {

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
                if($CountSubjectMissing !== 0){
                    $SubjectList[] = $Subject;
                    $CountSubjectMissing--;
                }
            }

            foreach ($SubjectList as $Subject) {
                // Jedes Fach auf separate Zeile
                $SubjectSection = (new Section());

                $SubjectSection->addElementColumn((new Element())
                    ->setContent('{% if(Content.P'.$personId.'.Input.JobEducationDuration is not empty) %}
                            (Dauer: {{ Content.P'.$personId.'.Input.JobEducationDuration }} Wochen)
                         {% endif %}'
                        .$Subject['SubjectName'])
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
            for($i = 0; $i < $CountSubjectMissing; $i++){
                $Section = new Section();
                $Section->addElementColumn((new Element())
                    ->setContent('{% if(Content.P'.$personId.'.Input.JobEducationDuration is not empty) %}
                            (Dauer: {{ Content.P'.$personId.'.Input.JobEducationDuration }} Wochen)
                         {% endif %} &nbsp;')
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
        }

        $Slice->styleHeight($Height);

        return $Slice;
    }

    /**
     * @param int $personId
     * @param TblCertificate $tblCertificate
     * @param string $Height
     * @param string $paddingTop
     *
     * @return Slice
     */
    protected function getSubjectLineJobEducationAbg($personId, TblCertificate $tblCertificate, $Height = 'auto', $paddingTop = '20px')
    {
        $Slice = (new Slice());

        // JobEducation
        $Slice->addElement((new Element())
            ->setContent('Berufspraktische Ausbildung')
            ->styleAlignCenter()
            ->stylePaddingTop($paddingTop)
            ->stylePaddingBottom('0px')
        );

        // SubjectList By EducationType
        $tblCertificateSubjectAll = $this->getCertificateSubjectByPerson($personId, $tblCertificate);
        $tblGradeList = $this->getGrade();
        // Anzahl der Abzubildenden Einträge (auch ohne Fach)
        $CountSubjectMissing = 1;

        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if ($tblSubject) {
                    $RankingString = str_pad($tblCertificateSubject->getRanking(), 2 ,'0', STR_PAD_LEFT);
                    $LaneString = str_pad($tblCertificateSubject->getLane(), 2 ,'0', STR_PAD_LEFT);

                    if($tblCertificateSubject->getRanking() == 17) {

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
                if($CountSubjectMissing !== 0){
                    $SubjectList[] = $Subject;
                    $CountSubjectMissing--;
                }
            }

            foreach ($SubjectList as $Subject) {
                // Jedes Fach auf separate Zeile
                $this->getSubjectLineAbg(
                    $Slice,
                    '{% if(Content.P' . $personId . '.Input.JobEducationDuration is not empty) %}
                            Dauer: {{ Content.P' . $personId . '.Input.JobEducationDuration }} Wochen
                    {% else %}
                       Dauer: X Wochen     
                    {% endif %}',//. $Subject['SubjectName'],
                    '{% if(Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                        {{ Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] }}
                    {% else %}
                        &ndash;
                    {% endif %}'
                );
            }
        }

        $Slice->styleHeight($Height);

        return $Slice;
    }

    /**
     * @param int    $personId
     *
     * @return Slice
     */
    protected function getFachhochschulreifeAbg($personId)
    {
        $Slice = (new Slice());

        $Slice->addElement((new Element())
            ->setContent('Zusatzausbildung zum Erwerb der Fachhochschulreife')
            ->styleAlignCenter()
            ->stylePaddingTop('20px')
            ->stylePaddingBottom('0px')
            ->styleTextBold()
        );

        $this->getSubjectLineAbg(
            $Slice,
            '{% if(Content.P'.$personId.'.Input.AddEducation is not empty) %}
                {{ Content.P'.$personId.'.Input.AddEducation }}
            {% else %}
                &ndash;
            {% endif %}',
            '{% if(Content.P'.$personId.'.Input.AddEducation_GradeText is not empty) %}
                 {{ Content.P'.$personId.'.Input.AddEducation_GradeText }}
            {% else %}
               {% if(Content.P'.$personId.'.Input.AddEducation_Grade is not empty) %}
                   {{ Content.P'.$personId.'.Input.AddEducation_Grade }}
               {% else %}
                   &ndash;
               {% endif %}
            {% endif %}'
        );

        return $Slice;
    }

    /**
     * @param int    $personId
     *
     * @return Slice
     */
    protected function getFachhochschulreife($personId)
    {
        $Slice = (new Slice());

        $Slice->addElement((new Element())
            ->setContent('Zusatzausbildung zum Erwerb der Fachhochschulreife')
            ->styleAlignCenter()
            ->stylePaddingTop('20px')
            ->stylePaddingBottom('0px')
            ->styleTextBold()
        );

        $TextSize = '14px';
        $TextSizeSmall = '8px';
        $SubjectSection = (new Section());

        $SubjectSection->addElementColumn((new Element())
            ->setContent('{% if(Content.P'.$personId.'.Input.AddEducation is not empty) %}
                {{ Content.P'.$personId.'.Input.AddEducation }}
            {% else %}
                &ndash;
            {% endif %}')
            ->stylePaddingTop()
            ->styleMarginTop('10px')
            ->stylePaddingBottom('1px')
            ->styleTextSize($TextSize)
            ->styleBorderBottom('0.5px')
            , '91%');


        $SubjectSection->addElementColumn((new Element())
            ->setContent('{% if(Content.P'.$personId.'.Input.AddEducation_GradeText is not empty) %}
                 {{ Content.P'.$personId.'.Input.AddEducation_GradeText }}
            {% else %}
               {% if(Content.P'.$personId.'.Input.AddEducation_Grade is not empty) %}
                   {{ Content.P'.$personId.'.Input.AddEducation_Grade }}
               {% else %}
                   &ndash;
               {% endif %}
            {% endif %}')
            ->styleAlignCenter()
            ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
            ->styleMarginTop('10px')
            ->stylePaddingTop('{% if(Content.P'.$personId.'.Input.AddEducation_GradeText is not empty) %}
                     5.3px
                 {% else %}
                     2px
                 {% endif %}')
            ->stylePaddingBottom('{% if(Content.P'.$personId.'.Input.AddEducation_GradeText is not empty) %}
                     5.5px
                 {% else %}
                     1.5px
                 {% endif %}')
            ->styleTextSize(
                '{% if(Content.P'.$personId.'.Input.AddEducation_GradeText is not empty) %}
                     ' . $TextSizeSmall . '
                 {% else %}
                     ' . $TextSize . '
                 {% endif %}'
            )
            , '9%');
        $Slice->addSection($SubjectSection);

        return $Slice;
    }

    /**
     * @param Slice  $Slice
     * @param int    $personId
     * @param array  $Subject
     * @param string $TextSize
     * @param string $TextSizeSmall
     *
     * @return Slice
     */
    private function SubjectOneLane(Slice $Slice, $personId, $Subject = array(), $TextSize = '14px', $TextSizeSmall = '8px')
    {

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
        return $Slice;
    }

    /**
     * @param Slice  $Slice
     * @param int    $personId
     * @param array  $SubjectListAlign
     * @param string $TextSize
     * @param string $TextSizeSmall
     *
     * @return Slice
     */
    private function SubjectTwoLane(Slice $Slice, $personId, $SubjectListAlign = array(), $TextSize = '14px', $TextSizeSmall = '8px')
    {

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
                ->styleMarginTop('15px')
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
                ->styleMarginTop('15px')
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
        return $Slice;
    }

    /**
     * @param int $personId
     * @param $title
     * @param $identifier
     * @param $rowsCount
     * @param string $Height
     * @param string $paddingTop
     *
     * @return Slice
     */
    protected function getSubjectLineComplexExam($personId, $title, $identifier, $rowsCount, $Height = 'auto', $paddingTop = '20px')
    {
        $Slice = (new Slice());

        $Slice->addElement((new Element())
            ->setContent($title)
            ->styleAlignCenter()
            ->stylePaddingTop($paddingTop)
            ->stylePaddingBottom('0px')
        );

        for($i = 1; $i <= $rowsCount; $i++){
            $this->getSubjectLineAbg(
                $Slice,
                '{% if (Content.P'.$personId.'.ExamList.' . $identifier . '.' . $i . '.Subjects is not empty) %}
                    {{ Content.P'.$personId.'.ExamList.' . $identifier . '.' . $i . '.Subjects }}
                {% else %}
                    &ndash;
                {% endif %}',
                '{% if (Content.P'.$personId.'.ExamList.' . $identifier . '.' . $i . '.Grade is not empty) %}
                    {{ Content.P'.$personId.'.ExamList.' . $identifier . '.' . $i . '.Grade }}
                {% else %}
                    &ndash;
                {% endif %}'
            );
        }

        $Slice->styleHeight($Height);

        return $Slice;
    }

    /**
     * @param int            $personId
     * @param string         $Height
     *
     * @return Slice
     */
    protected function getSubjectLineInformationalExpulsion($personId, $Height = 'auto')
    {
        $Slice = (new Slice());

        $Slice->addElement((new Element())
            ->setContent('Nachrichtliche Ausweisung')
            ->styleAlignCenter()
            ->stylePaddingTop('20px')
            ->stylePaddingBottom('0px')
        );

        // die Vorlage besitzt 12 Zeilen allerdings können es aktuell maximal 4*2 + 2 = 10 werden,
        // aus Platz gründen -> nur 10
        for($i = 1; $i <= 10; $i++){
            $this->getSubjectLineAbg(
                $Slice,
                '{% if(Content.P' . $personId . '.InformationalExpulsion.' . $i .' is not empty) %}
                    {{ Content.P' . $personId . '.InformationalExpulsion.' . $i .' }}
                {% else %}
                    &nbsp;
                {% endif %}',
                '&ndash;',
                'Content.P' . $personId . '.InformationalExpulsion.HasTwoRows' . $i
            );
        }

        $Slice->styleHeight($Height);

        return $Slice;
    }

    /**
     * @param int $personId
     * @param string $footNote
     * @param string $Height
     *
     * @return Slice
     */
    protected function getSubjectLineSkilledWork($personId, $footNote = '2)', $Height = 'auto')
    {
        $Slice = (new Slice());

        $Slice->addElement((new Element())
            ->setContent('Facharbeit')
            ->styleAlignCenter()
            ->stylePaddingTop('20px')
            ->stylePaddingBottom('0px')
        );

        $this->getSubjectLineAbg(
            $Slice,
            '{% if(Content.P'.$personId.'.Input.SkilledWork is not empty) %}
                Thema: {{ Content.P'.$personId.'.Input.SkilledWork }}' . $this->setSup($footNote) .'
            {% else %}
                Thema: &ndash;
            {% endif %}',
            '{% if(Content.P'.$personId.'.Input.SkilledWork_GradeText is not empty) %}
                 {{ Content.P'.$personId.'.Input.SkilledWork_GradeText }}' . $this->setSup($footNote) .'
            {% else %}
               {% if(Content.P'.$personId.'.Input.SkilledWork_Grade is not empty) %}
                   {{ Content.P'.$personId.'.Input.SkilledWork_Grade }}' . $this->setSup($footNote) .'
               {% else %}
                   &ndash;
               {% endif %}
            {% endif %}'
        );

        $Slice->styleHeight($Height);

        return $Slice;
    }

    /**
     * @param Slice $Slice
     * @param int   $count
     *
     * @return Slice
     */
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

    /**
     * @param int $personId
     *
     * @return Slice
     */
    protected function getChosenArea($personId)
    {

        $Slice = new Slice();

        $Slice->addElement((new Element())
            ->setContent('Wahlbereich')
            ->styleTextBold()
            ->styleAlignCenter()
            ->stylePaddingTop('20px')
            ->stylePaddingBottom('5px')
        );

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('{% if(Content.P' . $personId . '.Input.ChosenArea1 is not empty) %}
                        {{ Content.P' . $personId . '.Input.ChosenArea1 }}
                    {% else %}
                        &nbsp;
                    {% endif %}')
                ->styleAlignJustify()
                ->styleBorderBottom()
            , '45%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
            , '10%')
            ->addElementColumn((new Element())
                ->setContent('{% if(Content.P' . $personId . '.Input.ChosenArea2 is not empty) %}
                        {{ Content.P' . $personId . '.Input.ChosenArea2 }}
                    {% else %}
                        &nbsp;
                    {% endif %}')
                ->styleAlignJustify()
                ->styleBorderBottom()
            , '45%')
        );

        return $Slice;
    }

    /**
     * @param int $personId
     * @param string $Height
     *
     * @param bool $hasAdditionalRemarkFhr
     * @return Slice
     */
    protected function getDescriptionFsContent($personId, $Height = '85px', $hasAdditionalRemarkFhr = false)
    {

        $Slice = new Slice();

        // nur auf FsAbs
        if ($hasAdditionalRemarkFhr) {
            $postText = '
            {% if(Content.P' . $personId . '.Input.AdditionalRemarkFhr is not empty) %}
                <br />
                {% if(Content.P'.$personId.'.Person.Data.Name.Salutation is not empty) %}
                    {{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
                {% else %}
                    Frau/Herr
                {% endif %}
            {{ Content.P' . $personId . '.Person.Data.Name.First }}
            {{ Content.P' . $personId . '.Person.Data.Name.Last }}
            {{ Content.P'.$personId.'.Input.AdditionalRemarkFhr }}
            {% endif %}';
        } else {
            $postText = '';
        }

        $Slice->styleMarginTop('30px');
        $Slice->stylePaddingTop('5px');
        $Slice->styleHeight($Height);
        $Slice->styleBorderAll('0.5px');

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
                {% endif %}'
                . $postText
            )
            ->styleAlignJustify()
            ->stylePaddingLeft('5px')
            ->stylePaddingRight('5px')
        );

        return $Slice;
    }

    public function getSecondarySchoolDiploma($personId, $PaddingTop = '30px')
    {

        $Slice = (new Slice());

        $Slice->addElement((new Element())
            ->setContent('Aufgrund des erfolgreichen Fachschulabschlusses<br/>wird 
             {% if(Content.P'.$personId.'.Person.Data.Name.Salutation is not empty) %}
                {{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
            {% else %}
                Frau/Herr
            {% endif %}
            {{ Content.P' . $personId . '.Person.Data.Name.First }}
            {{ Content.P' . $personId . '.Person.Data.Name.Last }}
             der')
            ->stylePaddingTop($PaddingTop)
            ->styleAlignCenter()
            ->styleTextSize('16px')
        );

        $Slice->addElement((new Element())
            ->setContent('MITTLERE SCHULABSCHLUSS')
            ->stylePaddingTop('30px')
            ->styleAlignCenter()
            ->styleTextBold()
            ->styleTextSize('16px')
        );

        $Slice->addElement((new Element())
            ->setContent('und damit ein dem Realschulabschluss<br/>gleichwertiger Bildungsabschluss zuerkannt.')
            ->stylePaddingTop('30px')
            ->styleAlignCenter()
            ->styleTextSize('16px')
        );

        return $Slice;
    }

    /**
     * @param int  $personId
     * @param bool $isChairPerson Abschlusszeugnis
     *
     * @return Slice
     */
    protected function getIndividuallySignPart($personId, $isChairPerson = false)
    {
        $Slice = (new Slice());

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('
                {% if(Content.P' . $personId . '.Company.Address.City.Name is not empty) %}
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
                ->setContent('
                {% if(Content.P' . $personId . '.Input.Date is not empty) %}
                    {{ Content.P' . $personId . '.Input.Date }}
                {% else %}
                    &nbsp;
                {% endif %}')
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

        $paddingTop= '40px';
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleAlignCenter()
                ->stylePaddingTop($paddingTop)
                ->styleBorderBottom('0.5px')
                , '35%')
            ->addElementColumn((new Element())
                , '30%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleAlignCenter()
                ->stylePaddingTop($paddingTop)
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
                            Schulleiter/in
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
                            Klassenlehrer/in
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
            $Slice->addElement((new Element())
                ->setContent('&nbsp;')
                ->styleHeight('30px')
            );
        }

        return $Slice;
    }

    /**
     * @param int $personId
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

        $Slice
            ->addSection((new Section())
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
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                , '60%')
                ->addElementColumn((new Element())
                    ->setContent(
                        '{% if(Content.P' . $personId . '.DivisionTeacher.Name is not empty) %}
                            {{ Content.P' . $personId . '.DivisionTeacher.Name }}
                        {% else %}
                            &nbsp;
                        {% endif %}'
                    )
                    ->styleAlignCenter()
                    ->styleTextSize('10px')
                , '40%')
            );

        return $Slice;
    }

    /**
     * @param string $PaddingTop
     * @param string $Content
     *
     * @return Slice
     */
    protected function getFsInfo($PaddingTop = '20px', $Content = '')
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
     * @param string $PaddingTop
     * @param string $Sup
     * @param string $Content
     *
     * @return Slice
     */
    protected function getFsInfoExtended($PaddingTop = '20px', $Sup= '', $Content = '')
    {
        $Slice = new Slice();
        $Slice->stylePaddingTop($PaddingTop);
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent($Sup)
                ->styleTextSize('8px')
                , '7%')
            ->addElementColumn((new Element())
                ->setContent($Content)
                ->styleTextSize('9.5px')
                , '93%')
        );

        return $Slice;
    }

    /**
     * @param int    $personId
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
            ->styleMarginTop($MarginTop)
            ->styleBorderLeft('0.5px')
            ->styleBorderRight('0.5px')
            ->styleBorderBottom('0.5px');
        return $TransferSlice;
    }

    /**
     * @param                $personId
     * @param TblCertificate $tblCertificate
     *
     * @return bool|TblCertificateSubject[]
     */
    private function getCertificateSubjectByPerson($personId, TblCertificate $tblCertificate)
    {

        $tblTechnicalCourse = null;
        if(($tblPerson = Person::useService()->getPersonById($personId))){
            if(($tblStudent = Student::useService()->getStudentByPerson($tblPerson))){
                if(($tblTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())){
                    $tblTechnicalCourse = $tblTechnicalSchool->getServiceTblTechnicalCourse();
                }
            }
        }

        return Generator::useService()->getCertificateSubjectAll($tblCertificate, $tblTechnicalCourse);
    }

    /**
     * @param Slice $slice
     * @param $subjectName
     * @param $subjectGrade
     * @param $checkTwoRows
     */
    private function getSubjectLineAbg(Slice $slice, $subjectName, $subjectGrade, $checkTwoRows = '')
    {
        $TextSize = '14px';
        $marginTopSubjectOneRow = '15px';
        $marginTopGradeOneRow = '10px';
        $marginTopSubjectTwoRow = '2px';
        $marginTopGradeTwoRow = '14px';

        $SubjectSection = (new Section());

        if (strpos($subjectName, 'Content.P') === false && strlen($subjectName) > 90) {
            $marginTopSubject = $marginTopSubjectTwoRow;
            $marginTopGrade = $marginTopGradeTwoRow;
        } else {
            $marginTopSubject = $marginTopSubjectOneRow;
            $marginTopGrade = $marginTopGradeOneRow;
        }

        $SubjectSection->addElementColumn((new Element())
            ->setContent($subjectName) //. ($checkTwoRows ? '{{ ' . $checkTwoRows . ' }}' : ''))
            ->styleMarginTop($checkTwoRows
                ? '{% if(' . $checkTwoRows . ' is not empty) %} ' . $marginTopSubjectTwoRow . ' {% else %} ' . $marginTopSubjectOneRow . ' {% endif %} '
                : $marginTopSubject)
            ->stylePaddingTop()
            ->stylePaddingBottom('1px')
            ->stylePaddingLeft('3px')
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
            ->styleMarginTop($checkTwoRows
                ? '{% if(' . $checkTwoRows . ' is not empty) %} ' . $marginTopGradeTwoRow . ' {% else %} ' . $marginTopGradeOneRow . ' {% endif %} '
                : $marginTopGrade)
            ->stylePaddingTop('4px')
            ->stylePaddingBottom('4px')
            ->styleTextSize($TextSize)
            , '15%');
        $slice->addSection($SubjectSection);
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
}
