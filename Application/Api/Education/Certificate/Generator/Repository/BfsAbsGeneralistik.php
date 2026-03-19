<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Education\Certificate\Generate\Generate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Layout\Repository\Container;

class BfsAbsGeneralistik extends BfsStyle
{
    const TEXT_SIZE = '12pt';

    /**
     * @return array
     */
    public function getApiModalColumns(): array
    {
        return array(
            'DateGradeConference' => 'Datum der Notenkonferenz',
            'InDepthAssignment'   => 'Vertiefungseinsatz',
        );
    }

    /**
     * @return array
     */
    public function selectValuesInDepthAssignment(): array
    {
        return array(
            1 => "stationären Akutpflege",
            2 => "stationären Langzeitpflege",
            3 => "ambulanten Akut- und Langzeitpflege",
            4 => "pädiatrischen Versorgung",
            5 => "psychiatrischen Versorgung",
        );
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null): array
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $gender1 = 'Die/der';
        $gender2 = 'der/des';
        if ($this->getTblPrepareCertificate()
            && ($tblGenerateCertificate = $this->getTblPrepareCertificate()->getServiceTblGenerateCertificate())
        ) {
            if (($tblGenerateCertificateSettingLeader = Generate::useService()->getGenerateCertificateSettingBy($tblGenerateCertificate, 'Leader'))
                && ($tblPersonLeader = Person::useService()->getPersonById($tblGenerateCertificateSettingLeader->getValue()))
            ) {
                if (($tblCommon = $tblPersonLeader->getCommon())
                    && ($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())
                    && ($tblGender = $tblCommonBirthDates->getTblCommonGender())
                ) {
                    if ($tblGender->getName() == 'Männlich') {
                        $gender1 = 'Der';
                        $gender2 = 'des';
                    } elseif ($tblGender->getName() == 'Weiblich') {
                        $gender1 = 'Die';
                        $gender2 = 'der';
                    }
                }
            }
        }

        $pageList[] = (new Page())
            ->addSlice((new Slice())
                ->styleMarginTop('50px')
                ->addElement((new Element())
                    ->setContent($gender1 . ' Vorsitzende des' . new Container('Prüfungsausschusses'))
                    ->styleTextSize(self::TEXT_SIZE)
                )
            )
            ->addSlice((new Slice())
                ->styleMarginTop('20px')
                ->addSection((new Section())
                    ->addElementColumn($this->isSample()
                        ? (new Element\Sample())
                            ->styleTextSize('24pt')
                        : (new Element())
                        , '33%')
                    ->addElementColumn((new Element())
                        ->setContent('Z E U G N I S')
                        ->styleTextSize('24pt')
                        ->styleTextBold()
                        , '33%')
                    ->addElementColumn((new Element()))
                )
            )
            ->addSlice((new Slice())
                ->styleMarginTop('20px')
                ->addElement((new Element())
                    ->setContent('über die staatliche Prüfung der beruflichen Pflegeausbildung' . new Container('für 
                        {% if(Content.P' . $personId . '.Student.TechnicalCourse is not empty) %}
                            {{ Content.P' . $personId . '.Student.TechnicalCourse }}
                        {% else %}
                            Pflegefachfrauen/Pflegefachmänner
                        {% endif %}'))
                    ->styleTextSize('16pt')
                    ->styleTextBold()
                    ->styleAlignCenter()
                )
            )
            ->addSlice((new Slice())
                ->styleMarginTop('20px')
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Name, Vorname')
                        ->styleTextSize(self::TEXT_SIZE)
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.Last }}, {{ Content.P' . $personId . '.Person.Data.Name.First }}')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleTextBold()
                    )
                )
            )
            ->addSlice((new Slice())
                ->styleMarginTop('15px')
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Geburtsdatum')
                        ->styleTextSize(self::TEXT_SIZE)
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday }}')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleTextBold()
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('Geburtsort')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleAlignCenter()
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Person.Common.BirthDates.Birthplace }}')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleTextBold()
                        , '25%')
                )
            )
            ->addSlice((new Slice())
                ->styleMarginTop('20px')
                ->addElement((new Element())
                    ->setContent('hat am
                         {% if(Content.P'.$personId.'.Input.DateGradeConference is not empty) %}
                                {{ Content.P'.$personId.'.Input.DateGradeConference }}
                            {% else %}
                                ______________
                            {% endif %}
                         die staatliche Prüfung nach § 2 Nummer 1 des Pflegeberufegesetzes vor dem staatlichen Prüfungsausschuss')
                    ->styleTextSize(self::TEXT_SIZE)
                )
            )
            ->addSlice((new Slice())
                ->styleMarginTop('20px')
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('bei der')
                        ->styleTextSize(self::TEXT_SIZE)
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent(($tblCompany = $this->getTblCompany()) ? $tblCompany->getDisplayName() : '')
                        ->styleTextSize(self::TEXT_SIZE)
                    )
                )
            )
            ->addSlice((new Slice())
                ->styleMarginTop('15px')
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('in')
                        ->styleTextSize(self::TEXT_SIZE)
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if( Content.P' . $personId . '.Company.Address.City.Name is not empty) %}
                            {{ Content.P' . $personId . '.Company.Address.City.Name }}
                        {% else %}
                            &nbsp;
                        {% endif %}')
                        ->styleTextSize(self::TEXT_SIZE)
                    )
                )
            )
            ->addSlice((new Slice())
                ->styleMarginTop('20px')
                ->addElement((new Element())
                    ->setContent('bestanden. Der Vertiefungseinsatz nach § 7 Absatz 4 Satz 1 des Pflegeberufegesetzes wurde im Bereich der
                        {% if(Content.P' . $personId . '.Input.InDepthAssignment is not empty) %}
                                {{ Content.P' . $personId . '.Input.InDepthAssignment }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        durchgeführt.')
                    ->styleTextSize(self::TEXT_SIZE)
                )
            )
            ->addSlice((new Slice())
                ->styleMarginTop('20px')
                ->addElement((new Element())
                    ->setContent('
                        {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 2 %}
                                Sie
                            {% else %}
                                {% if Content.P'.$personId.'.Person.Common.BirthDates.Gender == 1 %}
                                    Er
                                {% else %}
                                    Sie/Er
                                {% endif %}
                        {% endif %}
                        hat folgende Gesamtnoten der einzelnen Prüfungsteile erhalten:')
                    ->styleTextSize(self::TEXT_SIZE)
                )
            )
            ->addSliceArray($this->getExams($personId))
            ->addSlice((new Slice())
                ->styleMarginTop('20px')
                ->addElement((new Element())
                    ->setContent('Bemerkungen:')
                    ->styleTextUnderline()
                    ->styleTextSize('11pt')
                )
                ->addElement((new Element())
                    ->setContent('
                        {% if(Content.P' . $personId . '.Input.RemarkWithoutTeam is not empty) %}
                            {{ Content.P' . $personId . '.Input.RemarkWithoutTeam|nl2br }}
                        {% else %}
                            &nbsp;
                        {% endif %}')
                    ->styleTextSize('11pt')
                    ->styleHeight('100px')
                )
            )
            ->addSlice((new Slice())
                ->styleMarginTop('20px')
                ->addElement((new Element())
                    ->setContent('
                        {{ Content.P' . $personId . '.Company.Address.City.Name }}, {{ Content.P' . $personId . '.Input.Date }}
                    ')
                    ->styleTextSize(self::TEXT_SIZE)
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleMarginTop('70px')
                        ->styleBorderBottom()
                        ->styleTextSize(self::TEXT_SIZE)
                        , '58%')
                    ->addElementColumn((new Element())
                        ->styleMarginTop('30px')
                        ->stylePaddingLeft('50px')
                        ->setContent('- Siegel -')
                        ->styleTextSize('10pt')
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('(Unterschrift oder qualifizierte elektronische Signatur ' . $gender2 . ' Vorsitzenden des Prüfungsausschusses)')
                        ->styleTextSize(self::TEXT_SIZE)
                        , '58%')
                    ->addElementColumn((new Element()))
                )
            )
        ;

        return $pageList;
    }

    private function getExams($personId): array
    {
        $tblPrepare = $this->getTblPrepareCertificate();
        $tblPerson = Person::useService()->getPersonById($personId);

        $sliceList[] = (new Slice())
            ->styleMarginTop('15px')
            ->addSection((new Section())
                ->addElementColumn((new Element()), '20%')
                ->addElementColumn((new Element())
                    ->setContent('1.&nbsp;&nbsp;im schriftlichen Teil der Prüfung')
                    ->styleTextSize(self::TEXT_SIZE)
                    , '60%')
                ->addElementColumn((new Element())
                    ->setContent($this->getExamGradeVerbal($tblPrepare ?: null, $tblPerson ?: null, 'WrittenExam_Grade'))
                    ->styleTextSize(self::TEXT_SIZE)
                    , '15%')
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if(Content.P' . $personId . '.Input.WrittenExam_Grade is not empty) %}
                            ({{ Content.P' . $personId . '.Input.WrittenExam_Grade }})
                        {% else %}
                            (&ndash;)
                        {% endif %}')
                    ->styleTextSize(self::TEXT_SIZE)
                    ->styleAlignRight()
                )
            );

        $sliceList[] = (new Slice())
            ->styleMarginTop('15px')
            ->addSection((new Section())
                ->addElementColumn((new Element()), '20%')
                ->addElementColumn((new Element())
                    ->setContent('2.&nbsp;&nbsp;im mündlichen Teil der Prüfung')
                    ->styleTextSize(self::TEXT_SIZE)
                    , '60%')
                ->addElementColumn((new Element())
                    ->setContent($this->getExamGradeVerbal($tblPrepare ?: null, $tblPerson ?: null, 'VerbalExam_Grade'))
                    ->styleTextSize(self::TEXT_SIZE)
                    , '15%')
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if(Content.P' . $personId . '.Input.VerbalExam_Grade is not empty) %}
                            ({{ Content.P' . $personId . '.Input.VerbalExam_Grade }})
                        {% else %}
                            (&ndash;)
                        {% endif %}')
                    ->styleTextSize(self::TEXT_SIZE)
                    ->styleAlignRight()
                )
            );

        $sliceList[] = (new Slice())
            ->styleMarginTop('15px')
            ->addSection((new Section())
                ->addElementColumn((new Element()), '20%')
                ->addElementColumn((new Element())
                    ->setContent('3.&nbsp;&nbsp;im praktischen Teil der Prüfung')
                    ->styleTextSize(self::TEXT_SIZE)
                    , '60%')
                ->addElementColumn((new Element())
                    ->setContent($this->getExamGradeVerbal($tblPrepare ?: null, $tblPerson ?: null, 'PracticalExam_Grade'))
                    ->styleTextSize(self::TEXT_SIZE)
                    , '15%')
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if(Content.P' . $personId . '.Input.PracticalExam_Grade is not empty) %}
                            ({{ Content.P' . $personId . '.Input.PracticalExam_Grade }})
                        {% else %}
                            (&ndash;)
                        {% endif %}')
                    ->styleTextSize(self::TEXT_SIZE)
                    ->styleAlignRight()
                )
            );

        $sliceList[] = (new Slice())
            ->styleMarginTop('15px')
            ->addSection((new Section())
                ->addElementColumn((new Element()), '20%')
                ->addElementColumn((new Element())
                    ->setContent('Gesamtnote der staatlichen Prüfung')
                    ->styleTextSize(self::TEXT_SIZE)
                    ->styleTextBold()
                    , '60%')
                ->addElementColumn((new Element())
                    ->setContent($this->getExamGradeVerbal($tblPrepare ?: null, $tblPerson ?: null, 'Sum_Grade'))
                    ->styleTextSize(self::TEXT_SIZE)
                    ->styleTextBold()
                    , '15%')
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if(Content.P' . $personId . '.Input.Sum_Grade is not empty) %}
                            ({{ Content.P' . $personId . '.Input.Sum_Grade }})
                        {% else %}
                            (&ndash;)
                        {% endif %}')
                    ->styleTextSize(self::TEXT_SIZE)
                    ->styleAlignRight()
                    ->styleTextBold()
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element()), '20%')
                ->addElementColumn((new Element())
                    ->setContent('(auf der Grundlage der Gesamtnoten Nr. 1 bis 3)')
                    ->styleTextSize('10pt')
                )
            )
        ;

        return $sliceList;
    }

    private array $gradeTextList = array(
        '1' => 'sehr gut',
        '2' => 'gut',
        '3' => 'befriedigend',
        '4' => 'ausreichend',
        '5' => 'mangelhaft',
        '6' => 'ungenügend',
    );

    private function getExamGradeVerbal(?TblPrepareCertificate $tblPrepareCertificate, ?TblPerson $tblPerson, string $type): string
    {
        if ($tblPrepareCertificate
            && $tblPerson
            && ($tblPrepareInformation = Prepare::useService()->getPrepareInformationBy($tblPrepareCertificate, $tblPerson, $type))
        ) {
            return $this->gradeTextList[$tblPrepareInformation->getValue()] ?? '&nbsp;';
        }

        return '&nbsp;';
    }
}
