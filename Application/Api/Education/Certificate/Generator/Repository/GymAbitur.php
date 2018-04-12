<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 08.03.2018
 * Time: 10:44
 */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;

/**
 * Class GymAbitur
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class GymAbitur extends Certificate
{

    /**
     * @var array|false
     */
    private $AdvancedCourses = false;

    /**
     * @var array|false
     */
    private $BasicCourses = false;

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $Header = $this->getHead($this->isSample(), true, 'auto', '50px');

        $this->setCourses($tblPerson);

        // leere Seite
        $pageList[] = new Page();

        $pageList[] = (new Page())
            ->addSlice($Header)
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Zeugnis')
                    ->styleTextSize('27px')
                    ->styleAlignCenter()
                    ->styleMarginTop('25%')
                    ->styleTextBold()
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('der allgemeinen Hochschulreife')
                    ->styleTextSize('27px')
                    ->styleAlignCenter()
                    ->styleMarginTop('20px')
                    ->styleMarginBottom('5px')
                )
            )
            ->addSliceArray($this->getSchoolPartAbitur($personId))
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Vor- und Zuname')
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('
                                {{ Content.P' . $personId . '.Person.Data.Name.First }}
                                {{ Content.P' . $personId . '.Person.Data.Name.Last }}
                        ')
                        ->styleAlignCenter()
                        ->styleBorderBottom()
                    )
                )->styleMarginTop('20px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('geboren am')
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthday is not empty) %}
                                    {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->styleAlignCenter()
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
                        ->styleAlignCenter()
                        ->styleBorderBottom()
                    )
                )->styleMarginTop('20px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('wohnhaft in')
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Person.Address.City.Name) %}
                                    {{ Content.P' . $personId . '.Person.Address.Street.Name }}
                                    {{ Content.P' . $personId . '.Person.Address.Street.Number }},
                                    {{ Content.P' . $personId . '.Person.Address.City.Code }}
                                    {{ Content.P' . $personId . '.Person.Address.City.Name }}
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                        ->styleAlignCenter()
                        ->styleBorderBottom()
                    )
                )->styleMarginTop('20px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('hat sich nach dem Besuch der gymnasialen Oberstufe der Abiturprüfung unterzogen.')
                    )
                )->styleMarginTop('20px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Dem Zeugnis liegt die „Verordnung des Sächsischen Staatsministeriums für Kultus
                            über allgemeinbildende Gymnasien und die Abiturprüfung im Freistaat Sachsen“ (SOGYA) in der jeweils geltenden Fassung zu Grunde.
                        ')
                        ->styleTextSize('12px')
                    )
                )->styleMarginTop('370px')
            );

        /*
         * Block I
         */
        $slice = new Slice();
        $slice
            ->addSection((new Section())
                ->addSliceColumn(
                    (new Slice)
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Fach')
                                ->styleTextBold()
                                ->styleAlignCenter()
                            )
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                            )
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                            )
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                            )
                            ->addElementColumn((new Element())
                                ->setContent('LF²')
                                ->styleAlignCenter()
                                ->styleBorderLeft()
                            , '10%')
                        )
                        ->styleBorderTop()
                        ->styleBorderLeft()
                        ->styleMarginTop('10px')
                , '50%')
                ->addSliceColumn(
                    (new Slice)
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Bewertung¹')
                                ->styleTextBold()
                                ->styleAlignCenter()
                            )
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Halbjahresergebnisse in einfacher Wertung')
                                ->styleAlignCenter()
                            )
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Jahrgangsstufe 11')
                                ->styleAlignCenter()
                            , '50%')
                            ->addElementColumn((new Element())
                                ->setContent('Jahrgangsstufe 12')
                                ->styleAlignCenter()
                                ->styleBorderLeft()
                            , '50%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('1. Halbjahr')
                                ->styleAlignCenter()
                                , '25%')
                            ->addElementColumn((new Element())
                                ->setContent('2. Halbjahr')
                                ->styleAlignCenter()
                                ->styleBorderLeft()
                                , '25%')
                            ->addElementColumn((new Element())
                                ->setContent('1. Halbjahr')
                                ->styleAlignCenter()
                                ->styleBorderLeft()
                                , '25%')
                            ->addElementColumn((new Element())
                                ->setContent('2. Halbjahr')
                                ->styleAlignCenter()
                                ->styleBorderLeft()
                                , '25%')
                        )
                        ->styleBorderLeft()
                        ->styleBorderTop()
                        ->styleBorderRight()
                        ->styleMarginTop('10px')
                    , '50%')
            )
            ->addSection($this->setFieldRow('Sprachlich-literarisch-künstlerisches Aufgabenfeld'))
            ->addSection($this->setSubjectRow($personId, 'Deutsch'))
            ->addSection($this->setSubjectRow($personId, 'Sorbisch'))
            ->addSection($this->setSubjectRow($personId, 'Englisch'))
            ->addSection($this->setSubjectRow($personId, 'Französisch'))
            ->addSection($this->setSubjectRow($personId, 'Griechisch'))
            ->addSection($this->setSubjectRow($personId, 'Italienisch'))
            ->addSection($this->setSubjectRow($personId, 'Latein'))
            ->addSection($this->setSubjectRow($personId, 'Polnisch'))
            ->addSection($this->setSubjectRow($personId, 'Russisch'))
            ->addSection($this->setSubjectRow($personId, 'Spanisch'))
            ->addSection($this->setSubjectRow($personId, 'Tschechisch'))
            ->addSection($this->setSubjectRow($personId))
            ->addSection($this->setSubjectRow($personId, 'Kunst'))
            ->addSection($this->setSubjectRow($personId, 'Musik'))

            ->addSection($this->setFieldRow('Gesellschaftswissenschaftliches Aufgabenfeld'))
            ->addSection($this->setSubjectRow($personId, 'Geschichte'))
            ->addSection($this->setSubjectRow($personId, 'Gemeinschaftskunde/Rechtserziehung/Wirtschaft'))
            ->addSection($this->setSubjectRow($personId, 'Geographie'))

            ->addSection($this->setFieldRow('Mathematisch-naturwissenschaftlich-technisches Aufgabenfeld'))
            ->addSection($this->setSubjectRow($personId, 'Mathematik'))
            ->addSection($this->setSubjectRow($personId, 'Biologie'))
            ->addSection($this->setSubjectRow($personId, 'Chemie'))
            ->addSection($this->setSubjectRow($personId, 'Physik'))
            ->addSection($this->setFieldRow())
            ->addSection($this->setSubjectRow($personId, 'RELIGION'))
            ->addSection($this->setSubjectRow($personId, 'Sport'))
            ->addSection($this->setFieldRow())
            ->addSection($this->setSubjectRow($personId, 'Astronomie', false))
            ->addSection($this->setSubjectRow($personId, 'Informatik', false))
            ->addSection($this->setSubjectRow($personId, 'Philosophie', false))
            ->addSection($this->setSubjectRow($personId, '&nbsp;', false))
            ->addSection($this->setSubjectRow($personId, '&nbsp;', false, true))
        ;


        $pageList[] = (new Page())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Vor- und Zuname')
                        ->styleMarginTop('10px')
                        , '18%')
                    ->addElementColumn((new Element())
                        ->setContent('
                                {{ Content.P' . $personId . '.Person.Data.Name.First }}
                                {{ Content.P' . $personId . '.Person.Data.Name.Last }}
                        ')
                        ->styleAlignCenter()
                        ->styleBorderBottom()
                        ->styleMarginTop('10px')
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Block I: Ergebnisse in der Qualifikationsphase')
                        ->styleTextBold()
                        ->styleMarginTop('10px')
                    )
                )
            )
            ->addSlice($slice)
            ->addSlice($this->getInfo(
                '240px',
                '¹ Alle Punktzahlen werden zweistellig angegeben.',
                '² Grundkursfächer bleiben ohne besondere Kennzeichnung. Leistungskursfächer sind in der betreffenden Zeile der Spalte „LF“ zu kennzeichnen.',
                '³ An Gymnasien gem. § 38 Abs. 2 SOGYA sind die Fächer Ev./Kath. Religion dem gesellschaftswissenschaftlichen Aufgabenfeld zugeordnet.'
//               , new Sup('4') . ' ⁴ mathematisch-naturwissenschaftlich-technisches Aufgabenfeld'
            ))
        ;

        return $pageList;
    }

    /**
     * @param string $name
     * @param bool $isBold
     *
     * @return Section
     */
    private function setFieldRow($name = '&nbsp;', $isBold = true)
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent($name)
                ->stylePaddingLeft('5px')
                ->styleTextBold($isBold ? 'bold' : 'normal')
                ->styleBorderTop()
                ->styleBorderLeft()
                ->styleBorderRight()
            );
    }

    /**
     * @param $personId
     * @param string $subjectName
     * @param bool $hasAdvancedCourse
     * @param bool $isLastRow
     *
     * @return Section
     */
    private function setSubjectRow($personId, $subjectName = '&nbsp;', $hasAdvancedCourse = true, $isLastRow = false)
    {

        $color = '#BBB';
        $isAdvancedSubject = false;

        // tatsächliche Religion aus der Schülerakte bestimmen
        if ($subjectName == 'RELIGION') {
            $subjectName = 'Ev./Kath. Religion³/Ethik';
            if (($tblPerson = Person::useService()->getPersonById($personId))
                && ($tblStudent = $tblPerson->getStudent())
                && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('RELIGION'))
                && ($tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier('1'))
                && ($tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking($tblStudent,
                $tblStudentSubjectType, $tblStudentSubjectRanking))
                && ($tblReligionSubject = $tblStudentSubject->getServiceTblSubject())
            ) {
                $subjectName = $tblReligionSubject->getName() . '³';
            }
        } else {
            // Leistungskurse markieren
            if (isset($this->AdvancedCourses[0])) {
                /** @var TblSubject $advancedSubject1 */
                $advancedSubjectAcronym1 = $this->AdvancedCourses[0];
                if (($tblAdvancedSubject1 = Subject::useService()->getSubjectByAcronym($advancedSubjectAcronym1))
                    && $tblAdvancedSubject1->getName() == $subjectName
                ) {
                    $isAdvancedSubject = true;
                }
            }
            if (isset($this->AdvancedCourses[1])) {
                /** @var TblSubject $advancedSubject2 */
                $advancedSubjectAcronym2 = $this->AdvancedCourses[1];
                if (($tblAdvancedSubject2 = Subject::useService()->getSubjectByAcronym($advancedSubjectAcronym2))
                    && $tblAdvancedSubject2->getName() == $subjectName
                ) {
                    $isAdvancedSubject = true;
                }
            }
        }

        $grades = array(
            '11-1' => '&nbsp;',
            '11-2' => '&nbsp;',
            '12-1' => '&nbsp;',
            '12-2' => '&nbsp;',
        );
        if (($tblPerson = Person::useService()->getPersonById($personId))
            && ($tblSubject = Subject::useService()->getSubjectByName($subjectName))
        ) {
            for ($level = 11; $level < 13; $level++) {
                for ($term = 1; $term < 3; $term++) {
                    $midTerm = $level . '-' . $term;
                    if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier($midTerm))
                        && ($tblPrepare = $this->getTblPrepareCertificate())
                        && ($tblPrepareAdditionalGrade = Prepare::useService()->getPrepareAdditionalGradeBy(
                            $tblPrepare,
                            $tblPerson,
                            $tblSubject,
                            $tblPrepareAdditionalGradeType
                        ))
                    ) {
                        $isSelected = $tblPrepareAdditionalGrade->isSelected();
                        $grades[$midTerm] = ($isSelected ? '' : '(') . $tblPrepareAdditionalGrade->getGrade() . ($isSelected ? '' : ')');
                    }
                }
            }
        }

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent($subjectName)
                ->stylePaddingLeft('5px')
                ->styleBorderTop()
                ->styleBorderLeft()
                ->styleBorderBottom($isLastRow ? '1px' : '0px')
                , $hasAdvancedCourse ? '45%' : '50%')
            ->addElementColumn((new Element())
                ->setContent($isAdvancedSubject ? 'X' : '&nbsp;')
                ->styleAlignCenter()
                ->styleBackgroundColor($hasAdvancedCourse ? $color : '#FFF')
                ->styleBorderTop()
                ->styleBorderLeft($hasAdvancedCourse ? '1px' : '0px')
                ->styleBorderBottom($isLastRow ? '1px' : '0px')
                , $hasAdvancedCourse ? '5%' : '0%')
            ->addElementColumn((new Element())
                ->setContent($grades['11-1'])
                ->styleAlignCenter()
                ->styleBackgroundColor($color)
                ->styleBorderTop()
                ->styleBorderLeft()
                ->styleBorderBottom($isLastRow ? '1px' : '0px')
                , '12.5%')
            ->addElementColumn((new Element())
                ->setContent($grades['11-2'])
                ->styleAlignCenter()
                ->styleBackgroundColor($color)
                ->styleBorderTop()
                ->styleBorderLeft()
                ->styleBorderBottom($isLastRow ? '1px' : '0px')
                , '12.5%')
            ->addElementColumn((new Element())
                ->setContent($grades['12-1'])
                ->styleAlignCenter()
                ->styleBackgroundColor($color)
                ->styleBorderTop()
                ->styleBorderLeft()
                ->styleBorderBottom($isLastRow ? '1px' : '0px')
                , '12.5%')
            ->addElementColumn((new Element())
                ->setContent($grades['12-2'])
                ->styleAlignCenter()
                ->styleBackgroundColor($color)
                ->styleBorderTop()
                ->styleBorderLeft()
                ->styleBorderRight()
                ->styleBorderBottom($isLastRow ? '1px' : '0px')
                , '12.5%')
            ;
    }

    /**
     * @param $personId
     * @param string $MarginTop
     *
     * @return Slice[]
     */
    private function getSchoolPartAbitur($personId, $MarginTop = '20px')
    {

        if (($tblSetting = Consumer::useService()->getSetting(
                'Education', 'Certificate', 'Prepare', 'IsSchoolExtendedNameDisplayed'))
            && $tblSetting->getValue()
        ) {
            $isSchoolExtendedNameDisplayed = true;
        } else {
            $isSchoolExtendedNameDisplayed = false;
        }
        if (($tblSetting = Consumer::useService()->getSetting(
                'Education', 'Certificate', 'Prepare', 'SchoolExtendedNameSeparator'))
            && $tblSetting->getValue()
        ) {
            $separator = $tblSetting->getValue();
        } else {
            $separator = false;
        }
//        $isLargeCompanyName = false;
//        $isLargeSecondRow = false;
        $isLargeCompanyName = true;
        $isLargeSecondRow = true;
        $schoolName = '';
        $extendedName = '';
        // get company name
        if (($tblPerson = Person::useService()->getPersonById($personId))) {
            if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {
                if (($tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS'))) {
                    $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                        $tblTransferType);
                    if ($tblStudentTransfer) {
                        if (($tblCompany = $tblStudentTransfer->getServiceTblCompany())) {
                            $place = '';
                            if (($tblAddress = $tblCompany->fetchMainAddress())
                                && ($tblCity = $tblAddress->getTblCity())
                            ) {
                                $place = ' in ' . $tblCity->getName();
                            }
                            $schoolName = $tblCompany->getName();
                            $extendedName = $isSchoolExtendedNameDisplayed ?
                                ($separator ? ' ' . $separator . ' ' : ' ') . $tblCompany->getExtendedName() : '';
                            $extendedName .= $place;

                            if (strlen($schoolName) > 60) {
                                $isLargeCompanyName = true;
                            }
                            if (strlen($extendedName) > 60) {
                                $isLargeSecondRow = true;
                            }
                        }
                    }
                }
            }
        }

        $SchoolSlice = (new Slice());
        if ($isLargeCompanyName) {
            $SchoolSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Name und Ort der Schule:')
                    , '25%')
                ->addElementColumn((new Element())
                    ->setContent($schoolName ? $schoolName : '&nbsp;')
                    ->styleBorderBottom()
                    ->styleAlignCenter()
                )
            )->styleMarginTop($MarginTop);
        } else {
            $SchoolSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Name und Ort der Schule:')
                    , '25%')
                ->addElementColumn((new Element())
                    ->setContent($schoolName ? $schoolName : '&nbsp;')
                    ->styleBorderBottom()
                    ->styleAlignCenter()
                , '50%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderBottom()
                    , '25%')
            )->styleMarginTop($MarginTop);
        }
        $slices[] = $SchoolSlice;

        $SecondSlice = (new Slice());
        if ($isLargeSecondRow) {
            $SecondSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '25%')
                ->addElementColumn((new Element())
                    ->setContent($extendedName ? $extendedName : '&nbsp;')
                    ->styleBorderBottom()
                    ->styleAlignCenter()
                )
            )->styleMarginTop($MarginTop);
        } else {
            $SecondSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '25%')
                ->addElementColumn((new Element())
                    ->setContent($extendedName ? $extendedName : '&nbsp;')
                    ->styleBorderBottom()
                    ->styleAlignCenter()
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderBottom()
                    , '25%')
            )->styleMarginTop($MarginTop);
        }
        $slices[] = $SecondSlice;

        return $slices;
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    private function setCourses(TblPerson $tblPerson = null)
    {

        $advancedCourses = array();
        $basicCourses = array();
        if ($tblPerson && ($tblDivision = $this->getTblDivision())
            && ($tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision))
        ) {
            foreach ($tblDivisionSubjectList as $tblDivisionSubjectItem) {
                if (($tblSubjectGroup = $tblDivisionSubjectItem->getTblSubjectGroup())) {

                    if (($tblSubjectStudentList = Division::useService()->getSubjectStudentByDivisionSubject(
                        $tblDivisionSubjectItem))
                    ) {
                        foreach ($tblSubjectStudentList as $tblSubjectStudent) {
                            if (($tblSubject = $tblDivisionSubjectItem->getServiceTblSubject())
                                && ($tblPersonStudent = $tblSubjectStudent->getServiceTblPerson())
                                && $tblPerson->getId() == $tblPersonStudent->getId()
                            ) {
                                if ($tblSubjectGroup->isAdvancedCourse()) {
                                    if ($tblSubject->getName() == 'Deutsch' || $tblSubject->getName() == 'Mathematik') {
                                        $advancedCourses[0] = $tblSubject->getAcronym();
                                    } else {
                                        $advancedCourses[1] = $tblSubject->getAcronym();
                                    }
                                } else {
                                    $basicCourses[$tblSubject->getAcronym()] = $tblSubject->getAcronym();
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!empty($advancedCourses)) {
            $this->AdvancedCourses = $advancedCourses;
        }
        if (!empty($basicCourses)) {
            $this->BasicCourses = $basicCourses;
        }
    }
}