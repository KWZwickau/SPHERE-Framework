<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Text\Repository\Sup;

class BfsAbsMs extends BfsStyle
{
    /**
     * @return array
     */
    public function getApiModalColumns(): array
    {
        return array(
            'DateFrom' => 'Besucht "seit" die Fachschule',
            'DateTo' => 'Besuchte "bis" die Fachschule',
            'BfsDestination'      => 'Berufsfachschule für ...',

            'OperationTimeTotal' => 'Praktische Ausbildung Dauer in Wochen',
            'Operation1' => 'Einsatzgebiet 1',
            'OperationTime1' => 'Einsatzgebiet Dauer in Wochen 1',
            'Operation2' => 'Einsatzgebiet 2',
            'OperationTime2' => 'Einsatzgebiet Dauer in Wochen 2',
            'Operation3' => 'Einsatzgebiet 3',
            'OperationTime3' => 'Einsatzgebiet Dauer in Wochen 3',
            'Operation4' => 'Einsatzgebiet 4',
            'OperationTime4' => 'Einsatzgebiet Dauer in Wochen 4',

            'DateExam' => 'Datum des Prüfungszeugnisses',
            'ExamCenter' => 'Prüfungszeugnis - Durchschnittsnote',
            'AddEducation_Average_EXAM' => 'Prüfungsstelle',
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

        $pageList[] = (new Page())
            ->addSlice($this->getSchoolHead($personId, 'Abschlusszeugnis', false, true))
            ->addSlice($this->getStudentHeadAbs($personId))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Aufgrund der ausgewiesenen Leistungen mit einer Durchschnittsnote'
                        . new Container('von
                            {% if(Content.P' . $personId . '.Input.AddEducation_Average_BFS is not empty) %}
                                {{ Content.P' . $personId . '.Input.AddEducation_Average_BFS }}
                            {% else %}
                                ---
                            {% endif %}')
                        . new Container('wird {{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
                            {{ Content.P' . $personId . '.Person.Data.Name.First }}
                            {{ Content.P' . $personId . '.Person.Data.Name.Last }} der')

                    )
                    ->styleAlignCenter()
                    ->styleTextSize('16px')
                )
                ->addElement((new Element())
                    ->setContent('MITTLERE SCHULABSCHLUSS')
                    ->styleAlignCenter()
                    ->styleTextSize('20px')
                    ->styleTextBold()
                    ->stylePaddingTop('10px')
                )
                ->addElement((new Element())
                    ->setContent('und damit ein dem Realschulabschluss gleichwertiger Bildungsabschluss zuerkannt.')
                    ->styleAlignCenter()
                    ->styleTextSize('16px')
                    ->stylePaddingTop('10px')
                )
            )
            ->addSlice((new Slice())->addElement((new Element())
                ->setContent('&nbsp;')
                ->stylePaddingTop('170px')
            ))
            ->addSlice($this->getIndividuallySignPart($personId, true))
        ;

        $pageList[] = $this->getDiplomaSecondPage($personId);

        //
        // Zeugnis für den mittleren Schulabschluss
        //
        $pageList[] = (new Page())
            ->addSlice($this->getSchoolHeadFirstSlice(true))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Zeugnis')
                    ->styleAlignCenter()
                    ->styleTextSize('30px')
                )
                ->addElement((new Element())
                    ->setContent('über den mittleren Schulabschluss')
                    ->styleAlignCenter()
                    ->styleTextSize('22px')
                )
            )
            ->addSlice($this->getStudentHeadAbs($personId, false))
            ->addSlice((new Slice())
                ->styleMarginTop('20px')
                ->addElement((new Element())
                    ->setContent('hat erfolgreich die Berufsfachschule besucht.')
                    ->styleAlignCenter()
                )
                ->addElement((new Element())
                    ->setContent('Aufgrund der ausgewiesenen Leistungen im')
                    ->styleAlignCenter()
                )
            )
            ->addSlice((new Slice())
                ->styleMarginTop('10px')
                ->styleBorderAll('0.5px')
                ->addElement((new Element())
                    ->setContent('Prüfungszeugnis')
                    ->styleMarginTop('15px')
                    ->styleAlignCenter()
                    ->styleTextSize('16px')
                )
                ->addElement((new Element())
                    ->setContent('ausgestellt am 
                        {% if(Content.P' . $personId . '.Input.DateExam is not empty) %}
                            {{ Content.P' . $personId . '.Input.DateExam }}
                        {% else %}
                            ---
                        {% endif %}
                    ')
                    ->styleMarginTop('15px')
                    ->styleMarginBottom('10px')
                    ->stylePaddingLeft('5px')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if(Content.P' . $personId . '.Input.ExamCenter is not empty) %}
                                {{ Content.P' . $personId . '.Input.ExamCenter }}
                            {% else %}
                                ---
                            {% endif %}
                        ')
                        ->stylePaddingLeft('5px')
                    , '70%')
                    ->addElementColumn((new Element())
                        ->setContent('mit der Durchschnittsnote 
                            {% if(Content.P' . $personId . '.Input.AddEducation_Average_EXAM is not empty) %}
                                {{ Content.P' . $personId . '.Input.AddEducation_Average_EXAM }}
                            {% else %}
                                ---
                            {% endif %}
                        ')
                        ->stylePaddingRight('5px')
                        ->styleAlignRight()
                    )
                )
                ->addElement((new Element())
                    ->setContent('über den Abschluss im Beruf
                         {% if(Content.P' . $personId . '.Input.BfsDestination is not empty) %}
                            {{ Content.P' . $personId . '.Input.BfsDestination }}
                         {% endif %}
                    ')
                    ->styleMarginTop('10px')
                    ->styleMarginBottom('5px')
                    ->stylePaddingLeft('5px')
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('und im')
                    ->styleMarginTop('5px')
                    ->styleAlignCenter()
                )
            )
            ->addSlice((new Slice())
                ->styleMarginTop('5px')
                ->styleBorderAll('0.5px')
                ->addElement((new Element())
                    ->setContent('Gesamtnotennachweis der Berufsfachschule')
                    ->styleMarginTop('15px')
                    ->styleMarginBottom('20px')
                    ->styleAlignCenter()
                    ->styleTextSize('16px')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('(siehe Rückseite)')
                        ->styleMarginBottom('5px')
                        ->stylePaddingLeft('5px')
                        , '50%')
                    ->addElementColumn((new Element())
                        ->setContent('mit der Durchschnittsnote
                            {% if(Content.P' . $personId . '.Input.AddEducation_Average_BFS is not empty) %}
                                {{ Content.P' . $personId . '.Input.AddEducation_Average_BFS }}
                            {% else %}
                                ---
                            {% endif %}
                        ')
                        ->stylePaddingRight('5px')
                        ->styleAlignRight()
                    )
                )
            )
            ->addSlice((new Slice())
                ->styleMarginTop('25px')
                ->addElement((new Element())
                    ->setContent(
                        'wird {{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
                        {{ Content.P' . $personId . '.Person.Data.Name.First }}
                        {{ Content.P' . $personId . '.Person.Data.Name.Last }} der'
                    )
                    ->styleAlignCenter()
                )
                ->addElement((new Element())
                    ->setContent('mittlere Schulabschluss')
                    ->styleAlignCenter()
                    ->styleTextSize('16px')
                    ->styleTextBold()
                    ->stylePaddingTop('10px')
                )
                ->addElement((new Element())
                    ->setContent('und damit ein dem Realschulabschluss gleichwertiger Bildungsabschluss zuerkannt.')
                    ->styleAlignCenter()
                    ->stylePaddingTop('10px')
                )
            )
            ->addSlice($this->getIndividuallySignPart($personId, false, false, false)
                ->styleMarginTop('150px')
            );

        $pageList[] = (new Page())
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Gesamtnotennachweis' . new Sup('1)') . ' der Berufsfachschule')
                    ->styleMarginTop('20px')
                    ->stylePaddingBottom('10px')
                    ->styleBorderBottom('0.5px')
                    ->styleAlignCenter()
                    ->styleTextSize('20px')
                )
                ->addElement((new Element())
                    ->setContent('
                        {{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
                        {{ Content.P' . $personId . '.Person.Data.Name.First }}
                        {{ Content.P' . $personId . '.Person.Data.Name.Last }}
                        hat vom 
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
                        die
                    ')
                    ->styleAlignCenter()
                    ->styleMarginTop('10px')
                )
                ->addElement((new Element())
                    ->setContent('Berufsfachschule für {% if(Content.P' . $personId . '.Input.BfsDestination is not empty) %}
                            {{ Content.P' . $personId . '.Input.BfsDestination }}
                        {% else %}
                            ---
                        {% endif %}')
                    ->styleAlignCenter()
                    ->styleTextSize('20px')
                    ->styleTextBold()
                    ->stylePaddingTop('15px')
                )
                ->addElement((new Element())
                    ->setContent('besucht und folgende Leistungen erreicht:')
                    ->styleMarginTop('25px')
                    ->styleAlignCenter()
                    ->styleBorderBottom('0.5px')
                )
                ->addElement((new Element())
                    ->setContent('Pflichtbereich')
                    ->styleMarginTop('5px')
                    ->styleAlignCenter()
                    ->styleTextBold()
                )
            )
            ->addSlice($this->getSubjectLineAcrossAbs($personId, $this->getCertificateEntity(), 'Berufsübergreifender Bereich', 1, 5, 1, 4, '150px'))
            ->addSlice($this->getSubjectLineAcrossAbs($personId, $this->getCertificateEntity(), 'Berufsbezogener Bereich', 1, 14, 5, 12, '320px'))
            ->addSlice($this->getSubjectLineAcrossAbs($personId, $this->getCertificateEntity(), 'Wahlpflichtbereich', 1, 2, 13, 13, '80px'))
            ->addSlice($this->getPraktikaShort($personId, $this->getCertificateEntity()))
            ->addSlice($this->getBsInfo('170px', new Sup('1)') . '&nbsp;&nbsp;&nbsp;&nbsp; Die Gesamtnote eines Faches wird aus allen in der Ausbildung in diesem Fach erbrachten Leistungsnachweisen gebildet.'))
            ->addSlice($this->getBsInfo('20px', 'NOTENSTUFEN: sehr gut (1), gut (2), befriedigend (3), ausreichend (4), mangelhaft (5), ungenügend (6)'))
        ;

        return $pageList;
    }

    /**
     * @param $personId
     * @param TblCertificate $tblCertificate
     * @param bool $isAbs
     *
     * @return Slice
     */
    private function getPraktikaShort($personId, TblCertificate $tblCertificate, bool $isAbs = true): Slice
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

        return $Slice;
    }
}