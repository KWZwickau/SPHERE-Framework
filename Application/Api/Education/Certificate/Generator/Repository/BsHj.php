<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class BsHj
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class BsHj extends Certificate
{

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $pageList[] = (new Page())
            ->addSlice($this->getSchoolHead($personId))
            ->addSlice($this->getStudentHead($personId))
            ->addSlice($this->getSubjectLineAcross($personId))
            ->addSlice($this->getSubjectLineBase($personId, 'Berufsbozogener Bereich'))
        ;

        $pageList[] = (new Page())
            ->addSlice($this->getSecondPageHead($personId))
            ->addSlice($this->getSubjectLineBase($personId, 'Berufsbozogener Bereich (Fortsetzung)', 10, true))
            ->addSlice($this->getSubjectLineChosen($personId))
        ;

        return $pageList;
    }

    /**
     * @param        $personId
     * @param string $CertificateName
     *
     * @return Slice
     */
    private function getSchoolHead($personId, $CertificateName = 'Halbjahresinformation')
    {

        $name = '';
        // get company name
        if (($tblPerson = Person::useService()->getPersonById($personId))) {
            if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {
                if (($tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS'))) {
                    $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                        $tblTransferType);
                    if ($tblStudentTransfer) {
                        if (($tblCompany = $tblStudentTransfer->getServiceTblCompany())) {
                            $name = $tblCompany->getName();
                        }
                    }
                }
            }
        }

        $Slice = (new Slice());
        $Slice->addElement((new Element())
            ->setContent($name ? $name : '&nbsp;')
            ->styleAlignCenter()
            ->styleTextSize('22px')
            ->styleHeight('50px')
            ->stylePaddingTop('20px')
        );
        $Slice->addElement((new Element())
            ->setContent($CertificateName)
            ->styleAlignCenter()
            ->styleTextSize('30px')
        );
        $Slice->addElement((new Element())
            ->setContent('der Berufsschule für ')   //ToDO Eingabe Variable
            ->stylePaddingTop('4px')
            ->styleAlignCenter()
            ->styleTextSize('22px')
        );

        return $Slice;
    }

    /**
     * @param $personId
     *
     * @return Slice
     */
    private function getStudentHead($personId)
    {

        $Slice = new Slice();

        $Slice->stylePaddingTop('20px');
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Klassenstufe {{ Content.P' . $personId . '.Division.Data.Level.Name }}')
                ->styleAlignCenter()
                ->styleBorderBottom('1px', '#000', 'dotted')
                , '30%'
            )
            ->addElementColumn((new Element())
                ->setContent('&nbsp')
                , '40%'
            )
            ->addElementColumn((new Element())
                ->setContent('Schuljahr {{ Content.P' . $personId . '.Division.Data.Year }}')
                ->styleAlignCenter()
                ->styleBorderBottom('1px', '#000', 'dotted')
                , '30%'
            )
        );

        $Slice->addElement((new Element())
            ->setContent('
            {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 2 %}
                Frau
            {% else %}
                {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 1 %}
                    Herr
                {% else %}
                    Frau/Herr
                {% endif %}
            {% endif %}
            {{ Content.P' . $personId . '.Person.Data.Name.First }}
            {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
            ->styleBorderBottom('1px', '#000', 'dotted')
            ->styleAlignCenter()
            ->styleTextSize('26px')
            ->stylePaddingTop('20px')
            ->styleMarginBottom('20px')
        );

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('geboren am  {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday }}')
                ->styleAlignCenter()
                , '30%'
            )
            ->addElementColumn((new Element())
                ->setContent('&nbsp')
                , '40%'
            )
            ->addElementColumn((new Element())
                ->setContent('in {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthplace }}')
                ->styleAlignCenter()
                , '30%'
            )
        );

        $Slice->addElement((new Element())
            ->setContent('hat im zurückliegenden Schulhalbjahr folgende Leistungen erreicht:')
            ->styleAlignCenter()
            ->styleTextSize('16px')
            ->stylePaddingTop('20px')
        );

        return $Slice;
    }

    private function getSubjectLineAcross($personId)
    {
        $Slice = (new Slice());
        $Slice->addElement((new Element())
            ->setContent('Pflichtbereich')
            ->styleAlignCenter()
            ->styleTextSize('18px')
            ->styleTextBold()
            ->stylePaddingTop('40px')
        );
        $Slice->addElement((new Element())
            ->setContent('Berufsübergreifender Bereich')
            ->styleAlignCenter()
            ->stylePaddingTop('20px')
            ->stylePaddingBottom('10px')
        );

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($this->getCertificateEntity());
        $tblGradeList = $this->getGrade();

        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if ($tblSubject) {
                    $RankingString = str_pad($tblCertificateSubject->getRanking(), 2 ,'0', STR_PAD_LEFT);
                    $LaneString = str_pad($tblCertificateSubject->getLane(), 2 ,'0', STR_PAD_LEFT);
                    if($tblCertificateSubject->getRanking() <= 3){
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

            // Berufsübergreifender Bereich
            $SubjectList1 = array();
            ksort($SubjectStructure);
            foreach ($SubjectStructure as $Ranking => $SubjectList) {
                foreach ($SubjectList as $Lane => $Subject) {
                    $SubjectList1[$Ranking][$Lane] = $Subject;
//                    $LaneCounter[$Lane]++;
                }
            }

            $TextSize = '14px';
            foreach ($SubjectList1 as $SubjectList) {
                // Sort Lane-Ranking (1,2...)
                ksort($SubjectList);
                $SubjectSection = (new Section());
                if (count($SubjectList) == 1 && isset($SubjectList["02"])) {
                    $SubjectSection->addElementColumn((new Element()), 'auto');
                }

                foreach ($SubjectList as $Lane => $Subject) {
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
                        ->styleBorderBottom()
                        , '39%');

                    $SubjectSection->addElementColumn((new Element())
                        ->setContent('{% if(Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] is not empty) %}
                                 {{ Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] }}
                             {% else %}
                                 &ndash;
                             {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#BBB')
                        ->styleMarginTop('15px')
                        ->stylePaddingTop('2px')
                        ->stylePaddingBottom('2px')
                        ->styleTextSize($TextSize)
                        , '9%');
                }
                if (count($SubjectList) == 1 && isset($SubjectList["01"])) {
                    $SubjectSection->addElementColumn((new Element()), '52%');
                }

                $Slice->addSection($SubjectSection);
            }
        }

        return $Slice;
    }

    private function getSubjectLineBase($personId, $Title = '&nbsp;', $Length = 10, $isPageTwo = false)
    {
        $Slice = (new Slice());

        $Slice->addElement((new Element())
            ->setContent($Title)
            ->styleAlignCenter()
            ->stylePaddingTop('20px')
            ->stylePaddingBottom('10px')
        );

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($this->getCertificateEntity());
        $tblGradeList = $this->getGrade();

        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if ($tblSubject) {
                    $RankingString = str_pad($tblCertificateSubject->getRanking(), 2 ,'0', STR_PAD_LEFT);
                    $LaneString = str_pad($tblCertificateSubject->getLane(), 2 ,'0', STR_PAD_LEFT);

                    if($tblCertificateSubject->getRanking() >= 5 && $tblCertificateSubject->getRanking() < 15){
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
                if($SubjectCount <= $Length && !$isPageTwo){
                    $SubjectList[$RankingLane] = $Subject;
                } elseif($SubjectCount > $Length && $isPageTwo){
                    $SubjectList[$RankingLane] = $Subject;
                }
                $SubjectCount++;
            }

            $TextSize = '14px';

            $countLF = 1;
            foreach ($SubjectList as $Subject) {
                // Jedes Fach auf separate Zeile
                $SubjectSection = (new Section());

                $SubjectSection->addElementColumn((new Element())
                    ->setContent('LF'.$countLF++.' '.$Subject['SubjectName'])
                    ->stylePaddingTop()
                    ->styleMarginTop('10px')
                    ->stylePaddingBottom('1px')
                    ->styleTextSize($TextSize)
                    ->styleBorderBottom()
                    , '91%');


                $SubjectSection->addElementColumn((new Element())
                    ->setContent('{% if(Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] is not empty) %}
                             {{ Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] }}
                         {% else %}
                             &ndash;
                         {% endif %}')
                    ->styleAlignCenter()
                    ->styleBackgroundColor('#BBB')
                    ->styleMarginTop('10px')
                    ->stylePaddingTop('2px')
                    ->stylePaddingBottom('2px')
                    ->styleTextSize($TextSize)
                    , '9%');
                $Slice->addSection($SubjectSection);
            }
        }

        return $Slice;
    }

    private function getSecondPageHead($personId)
    {

        $Slice = new Slice();

        $Slice->addElement((new Element())
            ->setContent('Halbjahresinformation für
            {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 2 %}
                Frau
            {% else %}
                {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 1 %}
                    Herr
                {% else %}
                    Frau/Herr
                {% endif %}
            {% endif %}
            {{ Content.P' . $personId . '.Person.Data.Name.First }}
            {{ Content.P' . $personId . '.Person.Data.Name.Last }},
            geboren am {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday }} - 2. Seite')
            ->styleAlignCenter()
//            ->styleTextSize('16px')
            ->stylePaddingTop('20px')
            ->styleBorderBottom()
        );

        return $Slice;
    }

    private function getSubjectLineChosen($personId)
    {
        $Slice = (new Slice());

        $Slice->addElement((new Element())
            ->setContent('Wahlflichtbereich')
            ->styleAlignCenter()
            ->stylePaddingTop('20px')
            ->stylePaddingBottom('10px')
        );

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($this->getCertificateEntity());
        $tblGradeList = $this->getGrade();

        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if ($tblSubject) {
                    $RankingString = str_pad($tblCertificateSubject->getRanking(), 2 ,'0', STR_PAD_LEFT);
                    $LaneString = str_pad($tblCertificateSubject->getLane(), 2 ,'0', STR_PAD_LEFT);

                    if($tblCertificateSubject->getRanking() >= 15) {
                        if (isset($tblGradeList['Data'][$tblSubject->getAcronym()])) {
                            $SubjectStructure[$RankingString.$LaneString]['SubjectAcronym']
                                = $tblSubject->getAcronym();
                            $SubjectStructure[$RankingString.$LaneString]['SubjectName']
                                = $tblSubject->getName();
                        }
                    }
                }
            }

            $SubjectList = array();

//            echo count($SubjectStructure2);
//            exit;

            ksort($SubjectStructure);
            foreach ($SubjectStructure as $RankingLane => $Subject) {
                $SubjectList[] = $Subject;
            }

            $TextSize = '14px';
            $countLF = 11;
            foreach ($SubjectList as $Subject) {
                // Jedes Fach auf separate Zeile
                $SubjectSection = (new Section());

                $SubjectSection->addElementColumn((new Element())
                    ->setContent('LF'.$countLF++.' '.$Subject['SubjectName'])
                    ->stylePaddingTop()
                    ->styleMarginTop('10px')
                    ->stylePaddingBottom('1px')
                    ->styleTextSize($TextSize)
                    ->styleBorderBottom()
                    , '91%');


                $SubjectSection->addElementColumn((new Element())
                    ->setContent('{% if(Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] is not empty) %}
                             {{ Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] }}
                         {% else %}
                             &ndash;
                         {% endif %}')
                    ->styleAlignCenter()
                    ->styleBackgroundColor('#BBB')
                    ->styleMarginTop('10px')
                    ->stylePaddingTop('2px')
                    ->stylePaddingBottom('2px')
                    ->styleTextSize($TextSize)
                    , '9%');
                $Slice->addSection($SubjectSection);
            }
        }

        return $Slice;
    }
}
