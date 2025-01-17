<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use DateTime;
use SPHERE\Application\Education\Certificate\Generate\Generate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Text\Repository\Underline;

class BGymAbitur extends BGymDiplomaStyle
{
    private array $gradeTextList = array(
        '1' => 'sehr gut',
        '2' => 'gut',
        '3' => 'befriedigend',
        '4' => 'ausreichend',
        '5' => 'mangelhaft',
        '6' => 'ungenügend',
    );

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null): array
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $isBellUsed = false;
        if ($tblPerson) {
            list($this->advancedCourses, $this->basicCourses) = DivisionCourse::useService()->getCoursesForStudent($tblPerson);
            $this->tblPerson = $tblPerson;

            if ($this->getTblPrepareCertificate()
                && ($tblPrepareInformation = Prepare::useService()->getPrepareInformationBy($this->getTblPrepareCertificate(), $tblPerson, 'IsBellUsed'))
                && $tblPrepareInformation->getValue()
            ) {
                $isBellUsed = true;
            }
        }

        // Seite 4 zuerst für Multi-Pdf-Druck
        $pageList[] = (new Page())
            ->addSlice($this->getPageHeader($personId, 4))
            ->addSlice($this->getLevelElven($tblPerson ?: null))
            ->addSlice($this->getForeignLanguagesWithRemark($tblPerson ?: null))
            ->addSlice($this->getFootNotes()->styleMarginTop('15px'));

        if (($tblPrepare = $this->getTblPrepareCertificate()) && $tblPrepare->getDate()) {
            $certificateDate = $tblPrepare->getDate();
            $educationDateFrom = (new DateTime('01.08.' . ((new DateTime($tblPrepare->getDate()))->format('Y') - 2)))->format('d.m.Y');
        } else {
            $certificateDate = '';
            $educationDateFrom = '';
        }

        $pageList[] =  (new Page())
            ->addSlice($this->getHeadForBGyDiploma())
            ->addSlice($this->getSchoolNameBGym('0px'))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Zeugnis der allgemeinen Hochschulreife')
                    ->styleTextSize('28px')
                    ->styleMarginTop('15px')
                    ->styleMarginBottom('30px')
                    ->styleAlignCenter()
                )
            )
            ->addSlice($this->getStudentLeaveDiploma($personId, '20px'))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('hat vom 
                    {% if(Content.P' . $personId . '.Input.EducationDateFrom is not empty) %}
                        {{ Content.P' . $personId . '.Input.EducationDateFrom }}
                    {% else %}'
                        . $educationDateFrom .
                        '{% endif %}
                    bis ' . $certificateDate . ' das')
                    ->styleAlignCenter()
                    ->styleMarginTop('85px')
                )
            )
            ->addSlice($this->getSubjectAreaDiploma($personId, '50px'))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent(
                        'besucht und die Abiturprüfung bestanden.
                         {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 2 %}
                                Sie
                            {% else %}
                                {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 1 %}
                                    Er
                                {% else %}
                                    {{ Content.P' . $personId . '.Person.Data.Name.First }} {{ Content.P' . $personId . '.Person.Data.Name.Last }}
                                {% endif %}
                            {% endif %}
                         hat damit die Berechtigung zum'
                        . new Container('Studium an einer Hochschule in der Bundesrepublik Deutschland erworben.' . $this->setSup('1)'))
                    )
                    ->styleMarginTop('50px')
                    ->styleAlignCenter()
                )
            )
            ->addSlice($this->getSignPartBGymDiploma($personId, '270px'));

        $pageList[] = (new Page())
            ->addSlice($this->getPageHeader($personId, 2))
            ->addSlice($this->getGradeHeader())
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Pflichtbereich')
                    ->styleTextBold()
                    ->styleAlignCenter()
                    ->styleMarginTop()
                    ->styleMarginTop('5px')
                )
            )
            ->addSlice($this->getWorkFieldDiploma('Sprachlich-literarisch-künstlerisches Aufgabenfeld', '230px'))
            ->addSlice($this->getWorkFieldDiploma('Gesellschaftswissenschaftliches Aufgabenfeld', '125px'))
            ->addSlice($this->getWorkFieldDiploma('Mathematisch-naturwissenschaftlich-technisches Aufgabenfeld', '230px'))
            ->addSlice($this->getWorkFieldDiploma('', '125px'))
            ->addSlice($this->getChosenSubjectsDiploma());


        $pageList[] = (new Page())
            ->addSlice($this->getPageHeader($personId, 3))
            ->addSlice($this->getExamHeader())
            ->addSlice($this->setExamRows($personId, $isBellUsed))
            ->addSlice($this->getBell($personId, $isBellUsed))
            ->addSlice($this->getResult($personId));

        return $pageList;
    }

    /**
     * @param string $marginTop
     * @param bool $showPicture
     *
     * @return Slice
     */
    private function getHeadForBGyDiploma(string $marginTop = '55px', bool $showPicture = true): Slice
    {
        $elementSaxonyLogo = (new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg', '214px', '66px'))->styleAlignRight();

        $pictureAddress = '';
        $pictureHeight = '66px';
        if ($showPicture) {
            if (($tblSettingAddress = Consumer::useService()->getSetting(
                'Education', 'Certificate', 'Generate', 'PictureAddressForDiplomaCertificate'))
            ) {
                $pictureAddress = trim($tblSettingAddress->getValue());
            }
            if (($tblSettingHeight = Consumer::useService()->getSetting(
                    'Education', 'Certificate', 'Generate', 'PictureHeightForDiplomaCertificate'))
                && ($value = trim($tblSettingHeight->getValue()))
            ) {
                $pictureHeight = $value;
            }
        }
        if ($pictureAddress) {
            $elementSchoolLogo = new Element\Image($pictureAddress, 'auto', $pictureHeight);
        } else {
            $elementSchoolLogo = (new Element())->setContent('&nbsp;');
        }

        $Header = (new Slice())
            ->styleMarginTop($marginTop)
            ->addSection((new Section())
                ->addElementColumn($elementSchoolLogo, '39%')
                ->addElementColumn($this->isSample()
                    ? (new Element\Sample())
                        ->styleTextSize('30px')
                        ->styleAlignCenter()
                        ->styleHeight('0px')
                    : (new Element())->setContent('&nbsp;')
                )
                ->addElementColumn($elementSaxonyLogo, '39%')
            );
        $Header->styleHeight('100px');

        return $Header;
    }

    /**
     * @param $personId
     * @param string $marginTop
     *
     * @return Slice
     */
    protected function getSignPartBGymDiploma($personId, string $marginTop = '450px'): Slice
    {
        $leaderName = '&nbsp;';
        $leaderDescription = 'Vorsitzende/r';

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
        }

        return (new Slice())
            ->styleMarginTop($marginTop)
            ->addSection((new Section())
                ->addElementColumn(
                    $this->getElementDiploma('{% if( Content.P' . $personId . '.Company.Address.City.Name is not empty) %}
                            {{ Content.P' . $personId . '.Company.Address.City.Name }}
                        {% else %}
                            &nbsp;
                        {% endif %}')
                    , '35%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                )
                ->addElementColumn(
                    $this->getElementDiploma('
                        {% if( Content.P' . $personId . '.Input.Date is not empty) %}
                            {{ Content.P' . $personId . '.Input.Date }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    , '35%')
            )
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
            )
            ->addSection((new Section())
                ->addElementColumn(
                    $this->getElementDiploma('&nbsp;')->styleMarginTop('30px')
                    , '35%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                )
                ->addElementColumn(
                    $this->getElementDiploma('&nbsp;')->styleMarginTop('30px')
                    , '35%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($leaderDescription . ' des Prüfungsausschusses')
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
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($leaderName)
                    ->styleTextSize('11px')
                    ->stylePaddingTop()
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
                    ->stylePaddingTop()
                    ->styleAlignCenter()
                    , '35%')
            );
    }

    /**
     * @param $personId
     * @param int $page
     *
     * @return Slice
     */
    private function getPageHeader($personId, int $page): Slice
    {
        return (new Slice())
            ->styleMarginTop('30px')
            ->addElement((new Element())
                ->setContent('
                        Zeugnis der allgemeinen Hochschulreife für 
                        {{ Content.P' . $personId . '.Person.Data.Name.Salutation }} {{ Content.P' . $personId . '.Person.Data.Name.First }} 
                        {{ Content.P' . $personId . '.Person.Data.Name.Last }}, geboren am
                        {% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthday is not empty) %}
                            {{ Content.P'.$personId.'.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                        &ndash; ' . $page . '. Seite'
                )
                ->styleAlignCenter()
                ->styleTextUnderline()
                ->styleTextSize('11px')
            );
    }

    private function getGradeHeader(): Slice
    {
        $marginTop = '10px';
        $paddingBottom = '4px';

        return (new Slice())
            ->styleMarginTop('15px')
            ->addElement((new Element())
                ->setContent('Leistungen in der Qualifikationsphase' . $this->setSup('2)'))
                ->styleTextBold()
                ->styleAlignCenter()
                ->styleMarginBottom('10px')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Fach')
                    ->styleTextBold()
                    ->styleAlignCenter()
                    , '40%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Ergebnisse in einfacher Wertung')
                    ->styleTextBold()
                    ->styleAlignCenter()
                    , '40%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Note' . $this->setSup('3)'))
                    ->styleTextBold()
                    ->styleAlignCenter()
                    , '20%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderBottom()
                    ->styleMarginTop($marginTop)
                    ->stylePaddingBottom($paddingBottom)
                    , '40%'
                )
                ->addElementColumn((new Element())
                    ->setContent('12/I')
                    ->styleTextBold()
                    ->styleAlignCenter()
                    ->styleBorderBottom()
                    ->styleMarginTop($marginTop)
                    ->stylePaddingBottom($paddingBottom)
                    , '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('12/II')
                    ->styleTextBold()
                    ->styleAlignCenter()
                    ->styleBorderBottom()
                    ->styleMarginTop($marginTop)
                    ->stylePaddingBottom($paddingBottom)
                    , '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('13/I')
                    ->styleTextBold()
                    ->styleAlignCenter()
                    ->styleBorderBottom()
                    ->styleMarginTop($marginTop)
                    ->stylePaddingBottom($paddingBottom)
                    , '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('13/II')
                    ->styleTextBold()
                    ->styleAlignCenter()
                    ->styleBorderBottom()
                    ->styleMarginTop($marginTop)
                    ->stylePaddingBottom($paddingBottom)
                    , '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderBottom()
                    ->styleMarginTop($marginTop)
                    ->stylePaddingBottom($paddingBottom)
                    , '20%'
                )
            );
    }

    private function getExamHeader(): Slice
    {
        $textSize = '11px';

        return (new Slice())
            ->styleMarginTop('15px')
            ->addElement((new Element())
                ->setContent('Leistungen in der Abiturprüfung')
                ->styleTextSize('17px')
                ->styleAlignCenter()
                ->styleTextBold()
                ->styleMarginBottom('10px')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Fach')
                    ->stylePaddingTop('7px')
                    ->styleTextBold()
                    ->styleAlignCenter()
                    ->styleTextSize($textSize)
                    , '40%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Ergebnisse in' . new Container('einfacher Wertung'))
                    ->styleTextBold()
                    ->styleAlignCenter()
                    ->styleTextSize($textSize)
                    , '20%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Gesamtergebnis in' . new Container('vierfacher Wertung'))
                    ->styleTextBold()
                    ->styleAlignCenter()
                    ->styleTextSize($textSize)
                    , '20%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Note')
                    ->stylePaddingTop('7px')
                    ->styleTextBold()
                    ->styleAlignCenter()
                    ->styleTextSize($textSize)
                    , '20%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleTextSize($textSize)
                    , '40%'
                )
                ->addElementColumn((new Element())
                    ->setContent('schriftliche Prüfung')
                    ->styleAlignCenter()
                    ->styleTextSize($textSize)
                    ->stylePaddingTop('7px')
                    , '11%'
                )
                ->addElementColumn((new Element())
                    ->setContent('zusätzliche mündliche Prüfung')
                    ->styleAlignCenter()
                    ->styleTextSize($textSize)
                    , '11%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleTextSize($textSize)
                )
            );
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Slice
     */
    private function getLevelElven(TblPerson $tblPerson = null): Slice
    {
        $slice = new Slice();
        $slice
            ->styleMarginTop('20px')
            ->addElement((new Element())
                ->setContent('Ergebnisse der Fächer, die in der Klassenstufe 11 abgeschlossen wurden')
                ->styleAlignCenter()
                ->styleTextBold()
                ->styleTextSize('16px')
            );

        // Zensuren ausblenden wenn der Schüler widersprochen hat
        if (($tblPrepareInformation = Prepare::useService()->getPrepareInformationBy($this->getTblPrepareCertificate(),
            $tblPerson, 'LevelTenGradesAreNotShown'))
        ) {
            $levelTenGradesAreNotShown = $tblPrepareInformation->getValue();
        } else {
            $levelTenGradesAreNotShown = false;
        }

        $tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('LEVEL-11');
        if (($tblPrepareAdditionalGradeList = Prepare::useService()->getPrepareAdditionalGradeListBy(
            $this->getTblPrepareCertificate(),
            $tblPerson,
            $tblPrepareAdditionalGradeType
        ))) {
            foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                $subject = '&ndash;';
                $grade = '&ndash;';

                if (($tblSubject = $tblPrepareAdditionalGrade->getServiceTblSubject())) {
                    $subject = $tblSubject->getName();
                    if (!$levelTenGradesAreNotShown) {
                        $grade = $tblPrepareAdditionalGrade->getGrade();
                        if ($grade === '') {
                            continue;
                        }
                        if (isset($this->gradeTextList[$tblPrepareAdditionalGrade->getGrade()])) {
                            $grade = $this->gradeTextList[$tblPrepareAdditionalGrade->getGrade()];
                        }
                    }
                }

                $this->setLevelElvenRow($slice, $subject, $grade);
            }
        }

        return $slice->styleHeight('300px');
    }

    /**
     * @param Slice $slice
     * @param $subject
     * @param $grade
     */
    private function setLevelElvenRow(Slice $slice, $subject, $grade)
    {
        $slice
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($subject)
                    ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
                    ->styleBorderBottom()
                    ->stylePaddingTop('5px')
                    ->stylePaddingBottom('4px')
                    , '84%'
                )
                ->addElementColumn((new Element())->setContent('&nbsp;'), '1%')
                ->addElementColumn($this->getElementPoints($grade))
            );
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Slice
     */
    private function getForeignLanguagesWithRemark(TblPerson $tblPerson = null): Slice
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $slice = new Slice();
        $slice
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Fremdsprachen')
                    ->styleAlignCenter()
                    ->styleTextBold()
                    ->styleTextSize('16px')
                    ->styleMarginBottom('5px')
                )
            );

        $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE');
        if (($tblPerson)) {
            $tblStudent = $tblPerson->getStudent();
        } else {
            $tblStudent = false;
        }

        $preRemark = '';
        for ($i = 1; $i < 3; $i++) {
            switch ($i) {
                case 1: $ranking = 'ersten'; break;
                case 2: $ranking = 'zweiten'; break;
//                case 3: $ranking = 'dritten'; break;
//                case 4: $ranking = 'vierten'; break;
                default: $ranking = '';
            }

            if ($tblStudent
                && $tblStudentSubjectType
                && ($tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier($i))
                && ($tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking(
                    $tblStudent, $tblStudentSubjectType, $tblStudentSubjectRanking
                ))
                && ($tblYear = $this->getYear())
            ) {
                if (($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
                    $slice->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent(($i == 1 ?  'In ' : ' und in ') . $ranking . ' Fremdsprache')
                            ->styleMarginTop('5px')
                            , '35%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent($tblSubject->getName())
                            ->styleMarginTop('5px')
                        )
                    );

                    if (($tblPrepareInformation = Prepare::useService()->getPrepareInformationBy(
                        $this->getTblPrepareCertificate(),
                        $tblPerson,
                        'ForeignLanguages' . $tblStudentSubject->getTblStudentSubjectRanking()->getId()
                    ))) {
                        $value = $tblPrepareInformation->getValue();
                    } else {
                        $value = Generator::useService()->getReferenceForLanguageByStudent(
                            $this->getCertificateEntity(),
                            $tblStudentSubject,
                            $tblPerson,
                            $tblYear
                        );
                    }

                    $preRemark .= new Container(
                        'Das in '. $tblSubject->getName() . ' erreichte Sprachniveau entspricht der Stufe '
                        . $value . ' des Gemeinsamen europäischen Referenzrahmens.'
                    );
                }
            }
        }

        $slice->addElement((new Element())
            ->setContent('ist Unterricht in dem für den Erwerb der allgemeinen Hochschulreife erforderlichen Umfang besucht worden.')
            ->styleMarginTop('5px')
        );

        $slice->addElement((new Element())
            ->setContent(
                'Bemerkungen:'
                . $preRemark
                . new Container('{% if(Content.P' . $personId . '.Input.Remark is not empty) %}
                        {{ Content.P' . $personId . '.Input.Remark|nl2br }}
                    {% else %}
                        &nbsp;
                    {% endif %}')
            )
            ->styleMarginTop('10px')
            ->stylePaddingLeft('4px')
            ->stylePaddingRight('4px')
            ->stylePaddingTop('4px')
            ->stylePaddingBottom('4px')
            ->styleBorderAll('1px', '#000', 'dotted')
        );

        return $slice->styleHeight('500px');
    }

    private function getFootNotes(): Slice
    {
        $left = '4%';
        $textSize = '9.5px';

        return (new Slice())
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($this->setSup('1)'))
                    , $left
                )
                ->addElementColumn((new Element())
                    ->setContent('Dem Zeugnis liegt die Verordnung des Sächsischen Staatsministeriums für Kultus über Berufliche Gymnasien in der Fassung der Bekanntmachung vom 10. November 1998 (SächsGVBl.
                        1999 S. 16, 130), die zuletzt durch Artikel 2 der Verordnung vom 24. Juli 2018 (SächsGVBl. S. 531) geändert worden ist, in der jeweiligen Fassung, zu Grunde.')
                    ->styleTextSize($textSize)
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($this->setSup('2)'))
                    ->styleMarginTop('5px')
                    , $left
                )
                ->addElementColumn((new Element())
                    ->setContent('Leistungskursfächer sind mit LF gekennzeichnet. Alle Punktzahlen werden zweistellig angegeben. Die Ergebnisse von Kurshalbjahren, die nicht in die Gesamtqualifikation eingehen, sind
                        in Klammern gesetzt.')
                    ->styleMarginTop('5px')
                    ->styleTextSize($textSize)
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($this->setSup('3)'))
                    ->styleMarginTop('5px')
                    , $left
                )
                ->addElementColumn((new Element())
                    ->setContent('Bei der Berechnung der Note sind alle Kurse einbezogen. Für die Umsetzung der Punkte in Noten gilt:')
                    ->styleMarginTop('5px')
                    ->styleTextSize($textSize)
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleMarginTop('5px')
                    , $left
                )
                ->addElementColumn((new Element())
                    ->setContent($this->setPointsOverview($textSize))
                    ->styleMarginTop('5px')
                    ->styleTextSize($textSize)
                , '70%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleMarginTop('5px')
                )
            );
    }

    /**
     * @param $personId
     * @param bool $isBellUsed
     *
     * @return Slice
     */
    private function setExamRows($personId, bool $isBellUsed): Slice
    {
        $sectionList = array();

        for ($i = 1; $i < 6; $i++) {
            $subjectName = '&ndash;';
            $writtenExam = '&ndash;';
            $verbalExam = '&ndash;';
            $extraVerbalExam = '&ndash;';
            $total = '&ndash;';

            if (($tblPerson = Person::useService()->getPersonById($personId))
                && $this->getTblPrepareCertificate()
            ) {
                if ($i == 4) {
                    $sectionList[] = (new Section())
                        ->addElementColumn((new Element())->setContent('&nbsp;'), '40%')
                        ->addElementColumn((new Element())
                            ->setContent('mündliche Prüfung')
                            ->styleTextSize('11px')
                            ->styleMarginBottom('-4px')
                            ->styleAlignCenter()
                            , '11%'
                        )
                        ->addElementColumn((new Element())->setContent('&nbsp;'))
                    ;
                }

                if ($i < 4) {
                    if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('WRITTEN_EXAM'))
                        && ($writtenExamGrade = Prepare::useService()->getPrepareAdditionalGradeByRanking(
                            $this->getTblPrepareCertificate(),
                            $tblPerson,
                            $tblPrepareAdditionalGradeType,
                            $i))
                    ) {
                        if (($tblSubject = $writtenExamGrade->getServiceTblSubject())) {
                            $subjectName = $i . '. ' . ($i < 3 ? '(LF) ' : ' ') . $tblSubject->getName();
                        }

                        $writtenExam = str_pad($writtenExamGrade->getGrade(), 2, 0, STR_PAD_LEFT);

                        if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('EXTRA_VERBAL_EXAM'))
                            && ($extraVerbalExamGrade = Prepare::useService()->getPrepareAdditionalGradeByRanking(
                                $this->getTblPrepareCertificate(),
                                $tblPerson,
                                $tblPrepareAdditionalGradeType,
                                $i
                            ))
                        ) {
                            $extraVerbalExamGradeValue = $extraVerbalExamGrade->getGrade();
                            if ($extraVerbalExamGradeValue !== '' && $extraVerbalExamGradeValue !== null) {
                                $extraVerbalExam = str_pad($extraVerbalExamGradeValue, 2, 0, STR_PAD_LEFT);
                            }
                        } else {
                            $extraVerbalExamGrade = false;
                        }

                        $total = Prepare::useService()->calcAbiturExamGradesTotalForWrittenExam(
                            $writtenExamGrade,
                            $extraVerbalExamGrade ?: null
                        );
                    }
                } else {
                    if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('VERBAL_EXAM'))
                        && ($verbalExamGrade = Prepare::useService()->getPrepareAdditionalGradeByRanking(
                            $this->getTblPrepareCertificate(),
                            $tblPerson,
                            $tblPrepareAdditionalGradeType,
                            $i
                        ))
                    ) {
                        if (($tblSubject = $verbalExamGrade->getServiceTblSubject())) {
                            $subjectName = $i . '. ' . $tblSubject->getName();
                        }

                        $verbalExam = ($isBellUsed && $i == 5 ? '(' : '')
                            . str_pad($verbalExamGrade->getGrade(), 2, 0, STR_PAD_LEFT)
                            . ($isBellUsed && $i == 5 ? ')' : '');

                        if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('EXTRA_VERBAL_EXAM'))
                            && ($extraVerbalExamGrade = Prepare::useService()->getPrepareAdditionalGradeByRanking(
                                $this->getTblPrepareCertificate(),
                                $tblPerson,
                                $tblPrepareAdditionalGradeType,
                                $i))
                        ) {
                            $extraVerbalExamGradeValue = $extraVerbalExamGrade->getGrade();
                            if ($extraVerbalExamGradeValue !== '' && $extraVerbalExamGradeValue !== null) {
                                $extraVerbalExam = ($isBellUsed && $i == 5 ? '(' : '')
                                    . str_pad($extraVerbalExamGradeValue, 2, 0, STR_PAD_LEFT)
                                    . ($isBellUsed && $i == 5 ? ')' : '');
                            }
                        } else {
                            $extraVerbalExamGrade = false;
                        }

                        $total = Prepare::useService()->calcAbiturExamGradesTotalForVerbalExam(
                            $verbalExamGrade,
                            $extraVerbalExamGrade ?: null
                        );

                        $total = ($isBellUsed && $i == 5 ? '(' : '')
                            . $total
                            . ($isBellUsed && $i == 5 ? ')' : '');
                    }
                }
            }

            $sectionList[] = $this->getExamGradeLineDiploma($subjectName, $i < 4 ? $writtenExam : $verbalExam, $extraVerbalExam, $total);
        }

        $slice = new Slice();
        if ($sectionList) {
            $slice->addSectionList($sectionList);
        }

        return $slice;
    }

    /**
     * @param string $subjectName
     * @param string $firstColumn
     * @param string $secondColumn
     * @param string $total
     *
     * @return Section
     */
    private function getExamGradeLineDiploma(string $subjectName, string $firstColumn, string $secondColumn, string $total): Section
    {
        $widthSubject = 39;
        $widthSpace = 1;
        $widthSpaceLarge = 4;
        $widthGrade = 20;
        $widthPoints = (100 - $widthSubject - 2 * $widthSpace - 2 * $widthSpaceLarge - $widthGrade) / 3;

        $gradeText = '&ndash;';
        if ($total) {
            $gradeText = $this->getAverageText(round(intval($total) / 4));
        }

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent($subjectName)
                ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
                ->styleBorderBottom()
                ->stylePaddingTop('5px')
                ->stylePaddingBottom('4px')
                , $widthSubject . '%'
            )
            ->addElementColumn((new Element())->setContent('&nbsp;'), $widthSpace . '%')
            ->addElementColumn($this->getElementPoints($firstColumn), $widthPoints . '%')
            ->addElementColumn((new Element())->setContent('&nbsp;'), $widthSpace . '%')
            ->addElementColumn($this->getElementPoints($secondColumn), $widthPoints . '%')
            ->addElementColumn((new Element())->setContent('&nbsp;'), $widthSpaceLarge . '%')
            ->addElementColumn($this->getElementPoints($total), $widthPoints . '%')
            ->addElementColumn((new Element())->setContent('&nbsp;'), $widthSpaceLarge . '%')
            ->addElementColumn($this->getElementPoints($gradeText), $widthGrade . '%');
    }

    /**
     * @param $personId
     * @param bool $isBellUsed
     *
     * @return Slice
     */
    private function getBell($personId, bool $isBellUsed): Slice
    {
        $textSize = '11px';

        $bellPoints = '&ndash;';
        $gradeText = '&ndash;';
        if (($tblPerson = Person::useService()->getPersonById($personId))
            && ($tblPrepareInformation = Prepare::useService()->getPrepareInformationBy($this->getTblPrepareCertificate(), $tblPerson, 'BellPoints'))
        ) {
            $value = $tblPrepareInformation->getValue();
            if ($value !== null && $value !== '') {
                $bellPoints = ($isBellUsed ? '' : '(')
                    . str_pad($value,2, 0, STR_PAD_LEFT)
                    . ($isBellUsed ? '' : ')');
                $gradeText = $this->getAverageText(round(intval($value) / 4));
            }
        }

        return (new Slice)
            ->addElement((new Element())
                ->setContent('Besondere Lernleistung')
                ->styleTextSize('17px')
                ->styleTextBold()
                ->styleAlignCenter()
                ->styleMarginTop('70px')
                ->styleMarginBottom('10px')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())->setContent('&nbsp;'), '60%')
                ->addElementColumn((new Element())
                    ->setContent('Gesamtergebnis in' . new Container('vierfacher Wertung'))
                    ->styleTextBold()
                    ->styleAlignCenter()
                    ->styleTextSize($textSize)
                    , '20%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Note')
                    ->stylePaddingTop('7px')
                    ->styleTextBold()
                    ->styleAlignCenter()
                    ->styleTextSize($textSize)
                    , '20%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent(new Underline('Thema:')
                        . '{% if(Content.P' . $personId . '.Input.BellSubject is not empty) %}
                            {{ Content.P' . $personId . '.Input.BellSubject|nl2br }}
                        {% else %}
                            &ndash;
                        {% endif %}'
                    )
                    ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
                    ->styleBorderBottom()
                    ->stylePaddingTop('5px')
                    ->stylePaddingBottom('4px')
                )
                ->addElementColumn((new Element())->setContent('&nbsp;'), '4%')
                ->addElementColumn($this->getElementPoints($bellPoints), '10.33%')
                ->addElementColumn((new Element())->setContent('&nbsp;'), '4%')
                ->addElementColumn($this->getElementPoints($gradeText), '20%')
            );
    }

    /**
     * @param $personId
     *
     * @return Slice
     */
    private function getResult($personId): Slice
    {
        $textSize = '11px';
        $resultBlockI = '&ndash;';
        $resultBlockII = '&ndash;';
        $resultAverageGrade = '&ndash;';
        $resultAverageWord = '&ndash;';

        $middle = 12;
        $right = 20;

        if (($tblPerson = Person::useService()->getPersonById($personId))
            && ($tblPrepare = $this->getTblPrepareCertificate())
        ) {
            // Berechnung der Gesamtqualifikation und der Durchschnittsnote
            /** @noinspection PhpUnusedLocalVariableInspection */
            list($countCourses, $resultBlockI) = Prepare::useService()->getResultForAbiturBlockI(
                $tblPrepare,
                $tblPerson
            );
            $resultBlockII = Prepare::useService()->getResultForAbiturBlockII(
                $tblPrepare,
                $tblPerson
            );
            $resultPoints = $resultBlockI + $resultBlockII;
            if ($resultBlockI >= 200 && $resultBlockII >= 100) {
                $resultAverageGrade = Prepare::useService()->getResultForAbiturAverageGrade($resultPoints);
                $resultAverageWord = Grade::useService()->getAverageInWord($resultAverageGrade);
            } else {
                $resultAverageGrade = '&nbsp;';
            }
        }

        return (new Slice)
            ->addElement((new Element())
                ->setContent('Gesamtqualifikation und Durchschnittsnote')
                ->styleTextSize('17px')
                ->styleTextBold()
                ->styleAlignCenter()
                ->styleMarginTop('70px')
                ->styleMarginBottom('10px')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('1.&nbsp;&nbsp;&nbsp;&nbsp;Punktzahl in der Qualifikationsphase')
                    ->stylePaddingTop('5px')
                    ->stylePaddingBottom('4px')
                    ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
                )
                ->addElementColumn($this->getElementPoints($resultBlockI)
                    , $middle . '%'
                )
                ->addElementColumn((new Element())
                    ->setContent(
                        'mindestens 200 Punkte'
                        . new Container('höchstens&nbsp;&nbsp; 600 Punkte')
                    )
                    ->styleAlignCenter()
                    ->stylePaddingTop('6px')
                    ->stylePaddingBottom('4px')
                    ->styleTextSize($textSize)
                    , $right . '%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('2.&nbsp;&nbsp;&nbsp;&nbsp;Punktsumme der Gesamtergebnisse in der Abiturprüfung')
                    ->stylePaddingTop('5px')
                    ->stylePaddingBottom('4px')
                    ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
                )
                ->addElementColumn($this->getElementPoints($resultBlockII)
                    , $middle . '%'
                )
                ->addElementColumn((new Element())
                    ->setContent(
                        'mindestens 100 Punkte'
                        . new Container('höchstens&nbsp;&nbsp; 300 Punkte')
                    )
                    ->styleAlignCenter()
                    ->stylePaddingTop('6px')
                    ->stylePaddingBottom('4px')
                    ->styleTextSize($textSize)
                    , $right . '%'
                )
            )
            ->addElement((new Element())
                ->setContent('&nbsp;')
                ->styleTextSize($textSize)
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Gesamtpunktzahl')
                    ->stylePaddingTop('5px')
                    ->stylePaddingBottom('4px')
                    ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
                )
                ->addElementColumn($this->getElementPoints($resultBlockI + $resultBlockII)
                    , $middle . '%'
                )
                ->addElementColumn((new Element())
                    ->setContent(
                        'mindestens 300 Punkte'
                        . new Container('höchstens&nbsp;&nbsp; 900 Punkte')
                    )
                    ->styleAlignCenter()
                    ->stylePaddingTop('6px')
                    ->stylePaddingBottom('4px')
                    ->styleTextSize($textSize)
                    , $right . '%'
                )
            )
            ->addElement((new Element())
                ->setContent('&nbsp;')
                ->styleTextSize($textSize)
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Durchschnittsnote:')
                    ->stylePaddingTop('5px')
                    ->stylePaddingBottom('4px')
                    ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE),
                    '35%'
                )
                ->addElementColumn($this->getElementPoints($resultAverageGrade)
                    , $middle . '%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                )
                ->addElementColumn($this->getElementPoints($resultAverageWord)
                    , '32%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleTextSize($textSize),
                    '35%'
                )
                ->addElementColumn((new Element())
                    ->setContent('in Ziffern')
                    ->styleAlignCenter()
                    ->styleTextSize($textSize)
                    , $middle . '%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleTextSize($textSize)
                )
                ->addElementColumn((new Element())
                    ->setContent('in Worten')
                    ->styleAlignCenter()
                    ->styleTextSize($textSize)
                    , '32%'
                )
            );
    }
}