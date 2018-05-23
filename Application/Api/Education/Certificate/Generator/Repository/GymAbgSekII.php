<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 18.05.2018
 * Time: 08:20
 */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Text\Repository\Sup;

/**
 * Class GymAbg
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class GymAbgSekII extends Certificate
{

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $Header = $this->getHead($this->isSample(), true, 'auto', '50px');

        // todo letzte Seite einkommentieren
//        $pageList[] = (new Page());

//        $pageList[] = (new Page())
//            ->addSlice(
//                $Header
//            )
//            ->addSlice((new Slice())
//                ->addElement((new Element())
//                    ->setContent('ABGANGSZEUGNIS')
//                    ->styleTextSize('27px')
//                    ->styleAlignCenter()
//                    ->styleMarginTop('32%')
//                    ->styleTextBold()
//                )
//            )
//            ->addSlice((new Slice())
//                ->addElement((new Element())
//                    ->setContent('des Gymnasiums')
//                    ->styleTextSize('22px')
//                    ->styleAlignCenter()
//                    ->styleMarginTop('20px')
//                )
//            )->addSlice((new Slice())
//                ->addElement((new Element())
//                    ->setContent('(gymnasiale Oberstufe)')
//                    ->styleTextSize('22px')
//                    ->styleAlignCenter()
//                    ->styleMarginTop('20px')
//                )
//            );
//
//        $pageList[] = (new Page())
//            ->addSlice((new Slice())
//                ->addSection((new Section())
//                    ->addElementColumn((new Element())
//                        ->setContent('Vorname und Zuname')
//                        , '22%')
//                    ->addElementColumn((new Element())
//                        ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
//                                          {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
//                        ->styleAlignCenter()
//                        ->styleBorderBottom()
//                    )
//                )->styleMarginTop('60px')
//            )
//            ->addSlice((new Slice())
//                ->addSection((new Section())
//                    ->addElementColumn((new Element())
//                        ->setContent('geboren am')
//                        , '22%')
//                    ->addElementColumn((new Element())
//                        ->setContent('{% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthday is not empty) %}
//                                    {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
//                                {% else %}
//                                    &nbsp;
//                                {% endif %}')
//                        ->styleAlignCenter()
//                        ->styleBorderBottom()
//                        , '20%')
//                    ->addElementColumn((new Element())
//                        ->setContent('in')
//                        ->styleAlignCenter()
//                        , '5%')
//                    ->addElementColumn((new Element())
//                        ->setContent('{% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthplace is not empty) %}
//                                    {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthplace }}
//                                {% else %}
//                                    &nbsp;
//                                {% endif %}')
//                        ->styleAlignCenter()
//                        ->styleBorderBottom()
//                    )
//                )->styleMarginTop('25px')
//            )
//            ->addSlice((new Slice())
//                ->addSection((new Section())
//                    ->addElementColumn((new Element())
//                        ->setContent('wohnhaft in')
//                        , '22%')
//                    ->addElementColumn((new Element())
//                        ->setContent('{% if(Content.P' . $personId . '.Person.Address.City.Name) %}
//                                    {{ Content.P' . $personId . '.Person.Address.Street.Name }}
//                                    {{ Content.P' . $personId . '.Person.Address.Street.Number }},
//                                    {{ Content.P' . $personId . '.Person.Address.City.Code }}
//                                    {{ Content.P' . $personId . '.Person.Address.City.Name }}
//                                {% else %}
//                                      &nbsp;
//                                {% endif %}')
//                        ->styleAlignCenter()
//                        ->styleBorderBottom()
//                    )
//                )->styleMarginTop('25px')
//
//            );

        // todo informationList

        $textSize = '13px';
        $padding = '8.5px';
        $padding2 = '7.5px';

        $slice = new Slice();
        $slice
            ->addSection((new Section())
                ->addSliceColumn(
                    (new Slice)
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                ->styleTextSize($textSize)
                            )
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Fach')
                                ->stylePaddingLeft('3px')
                                ->styleTextSize($textSize)
                                ->styleTextBold()
                            , '50%')
                            ->addSliceColumn((new Slice())
                                ->addSection((new Section())
                                    ->addElementColumn((new Element())
                                        ->setContent('Fremdsprache')
                                        ->styleTextSize($textSize)
                                        ->stylePaddingLeft('3px')
                                    )
                                )
                                ->addSection((new Section())
                                    ->addElementColumn((new Element())
                                        ->setContent('&nbsp;')
                                        ->styleTextSize($textSize)
                                    )
                                )
                                ->styleBackgroundColor('lightgrey')
                                ->styleBorderTop()
                                ->styleBorderLeft()
                            , '50%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                ->styleTextSize($textSize)
                                ->styleTextBold()
                                , '50%')
                            ->addSliceColumn((new Slice())
                                ->addSection((new Section())
                                    ->addElementColumn((new Element())
                                        ->setContent('von')
                                        ->styleAlignCenter()
                                        ->styleTextSize($textSize)
                                        ->stylePaddingTop($padding)
                                        ->stylePaddingBottom($padding2)
                                    )
                                    ->addElementColumn((new Element())
                                        ->setContent('bis')
                                        ->styleAlignCenter()
                                        ->styleTextSize($textSize)
                                        ->stylePaddingTop($padding)
                                        ->stylePaddingBottom($padding2)
                                    )
                                )
                                ->styleBackgroundColor('lightgrey')
                                ->styleBorderTop()
                                ->styleBorderLeft()
                                , '50%')
                        )
                        ->styleBorderTop()
                        ->styleBorderLeft()
                        ->styleMarginTop('15px')
                    , '44%')
                ->addSliceColumn(
                    (new Slice)
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Punktzahlen')
                                ->styleTextSize($textSize)
                                ->styleAlignCenter()
                            )
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Jahrgangsstufe 11')
                                ->styleTextSize($textSize)
                                ->styleAlignCenter()
                                ->stylePaddingTop($padding)
                                ->stylePaddingBottom($padding)
                                , '50%')
                            ->addElementColumn((new Element())
                                ->setContent('Jahrgangsstufe 12')
                                ->styleTextSize($textSize)
                                ->styleAlignCenter()
                                ->styleBorderLeft()
                                ->stylePaddingTop($padding)
                                ->stylePaddingBottom($padding)
                                , '50%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('1. Halb-')
                                ->styleTextSize($textSize)
                                ->styleBorderTop()
                                ->styleAlignCenter()
                                , '25%')
                            ->addElementColumn((new Element())
                                ->setContent('2. Halb-')
                                ->styleTextSize($textSize)
                                ->styleAlignCenter()
                                ->styleBorderTop()
                                ->styleBorderLeft()
                                , '25%')
                            ->addElementColumn((new Element())
                                ->setContent('1. Halb-')
                                ->styleTextSize($textSize)
                                ->styleAlignCenter()
                                ->styleBorderTop()
                                ->styleBorderLeft()
                                , '25%')
                            ->addElementColumn((new Element())
                                ->setContent('2. Halb-')
                                ->styleTextSize($textSize)
                                ->styleAlignCenter()
                                ->styleBorderTop()
                                ->styleBorderLeft()
                                , '25%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('jahr')
                                ->styleTextSize($textSize)
                                ->styleAlignCenter()
                                , '25%')
                            ->addElementColumn((new Element())
                                ->setContent('jahr')
                                ->styleTextSize($textSize)
                                ->styleAlignCenter()
                                ->styleBorderLeft()
                                , '25%')
                            ->addElementColumn((new Element())
                                ->setContent('jahr')
                                ->styleTextSize($textSize)
                                ->styleAlignCenter()
                                ->styleBorderLeft()
                                , '25%')
                            ->addElementColumn((new Element())
                                ->setContent('jahr')
                                ->styleTextSize($textSize)
                                ->styleAlignCenter()
                                ->styleBorderLeft()
                                , '25%')
                        )
                        ->styleBorderLeft()
                        ->styleBorderTop()
                        ->styleMarginTop('15px')
                    , '36%')
                    ->addSliceColumn((new Slice())
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Durch-')
                                ->styleTextSize($textSize)
                                ->styleAlignCenter()
                                ->styleBorderLeft()
                                ->stylePaddingTop('18px')
                            )
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('schnitt¹')
                                ->styleTextSize($textSize)
                                ->styleAlignCenter()
                                ->styleBorderLeft()
                                ->stylePaddingBottom('31.5px')
                            )
                        )
                        ->styleBorderTop()
                        ->styleMarginTop('15px')
                    , '8%')
                    ->addSliceColumn((new Slice())
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Abgangsnote²')
                                ->styleTextSize($textSize)
                                ->styleAlignCenter()
                                ->styleBorderLeft()
                                ->stylePaddingTop('25px')
                                ->stylePaddingBottom('40.5px')
                            )
                        )
                        ->styleBorderTop()
                        ->styleBorderRight()
                        ->styleMarginTop('15px')
                     )
                )
            ->addSection($this->setFieldRow('Sprachlich-literarisch-künstlerisches Aufgabenfeld'))
            ->addSection($this->setSubjectRow($personId, 'Deutsch'))
            ->addSection($this->setSubjectRow($personId, 'Sorbisch'))
            ->addSection($this->setSubjectRow($personId, 'Englisch', true))
            ->addSection($this->setSubjectRow($personId, 'Französisch', true))
            ->addSection($this->setSubjectRow($personId, 'Griechisch', true))
            ->addSection($this->setSubjectRow($personId, 'Latein', true))
            ->addSection($this->setSubjectRow($personId, 'Polnisch', true))
            ->addSection($this->setSubjectRow($personId, 'Russisch', true))
            ->addSection($this->setSubjectRow($personId, 'Spanisch', true))
            ->addSection($this->setSubjectRow($personId, 'Tschechisch', true))
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
            ->addSection($this->setSubjectRow($personId))
            ->addSection($this->setSubjectRow($personId, 'RELIGION'))
            ->addSection($this->setSubjectRow($personId, 'Sport'))
            ->addSection($this->setSubjectRow($personId))
            ->addSection($this->setFieldRow())
            ->addSection($this->setSubjectRow($personId))
            ->addSection($this->setSubjectRow($personId))
            ->addSection($this->setSubjectRow($personId, '&nbsp;', false, true))
        ;

        $pageList[] = (new Page())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Vor- und Zuname')
                        , '18%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                                          {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                        ->styleAlignCenter()
                        ->styleBorderBottom()
                    )
                )->styleMarginTop('60px')
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Leistungen')
                    ->styleMarginTop('15px')
                    ->styleTextBold()
                )
            )
            ->addSlice($slice)
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('
                                {{ Content.P' . $personId . '.Company.Address.City.Name }}, {{ Content.P' . $personId . '.Input.Date }}
                            ')
                        ->styleMarginTop('70px')
                        ->styleBorderBottom()
//                        ->styleTextSize($textSize)
                        , '35%')
                    ->addElementColumn((new Element()))
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('
                                Ort, Datum
                            ')
                        ->styleTextSize($textSize)
                        ->styleMarginTop('0px')
                        , '35%')
                    ->addElementColumn((new Element()))
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleTextSize($textSize)
                        ->styleAlignCenter()
                        ->styleBorderBottom('1px', '#000')
                        , '35%')
                    ->addElementColumn((new Element())
                        ->setContent('Dienstsiegel')
                        ->styleTextSize($textSize)
                        ->styleAlignCenter()
                        )
                    ->addElementColumn((new Element())
                        , '35%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Schulleiter(in)')
                        ->styleTextSize($textSize)
                        , '35%')
                    ->addElementColumn((new Element())
                        ->setContent('der Schule')
                        ->styleTextSize($textSize)
                        ->styleAlignCenter()
                    )
                    ->addElementColumn((new Element())
                        , '35%')
                )
                ->styleMarginTop('20px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->styleBorderBottom()
                        , '30%')
                    ->addElementColumn((new Element())
                        , '70%')
                )
                ->styleMarginTop('70px')
                ->addSection($this->setInfoRow(1, 'Ist das arithmetische Mittel der in den Kurshalbjahren erreichten Punktzahlen nicht ganzzahlig, so wird auf die nächstgrößere ganze Zahl gerundet.'))
                ->addSection($this->setInfoRow(2, 'Aus dem Punktzahldurchschnitt ergibt sich die Abgangsnote gemäß Tabelle auf Seite 2.'))
                ->addSection($this->setInfoRow(3, 'An Gymnasien gemäß § 38 Absatz 2 der Schulordnung Gymnasien Abiturprüfung sind die Fächer Ev./Kath. Religion dem gesellschaftswissenschaftlichen
                    Aufgabenfeld zugeordnet.'))
            )
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

        $textSize = '13px';
        return (new Section())
            ->addElementColumn((new Element())
                ->setContent($name)
                ->stylePaddingLeft('3px')
                ->styleTextBold($isBold ? 'bold' : 'normal')
                ->styleTextSize($textSize)
                ->styleBorderTop()
                ->styleBorderLeft()
                ->styleBorderRight()
            );
    }

    private function setSubjectRow($personId, $subjectName = '&nbsp;', $isForeignLanguage = false, $isLastRow = false)
    {

        $textSize = '13px';
        $colorPoints = '#BBB';
        $colorForeignLanguage = 'lightgrey';

        $tblLeaveStudent = false;
        $tblPerson = Person::useService()->getPersonById($personId);

        // tatsächliche Religion aus der Schülerakte bestimmen
        if ($subjectName == 'RELIGION') {
            $subjectName = 'Ev./Kath. Religion/Ethik';
            if ($tblPerson
                && ($tblStudent = $tblPerson->getStudent())
                && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('RELIGION'))
                && ($tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier('1'))
                && ($tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking($tblStudent,
                    $tblStudentSubjectType, $tblStudentSubjectRanking))
                && ($tblReligionSubject = $tblStudentSubject->getServiceTblSubject())
            ) {
                $subjectName = $tblReligionSubject->getName() . '³';
            }
        }

        $grades = array(
            '11-1' => '&ndash;',
            '11-2' => '&ndash;',
            '12-1' => '&ndash;',
            '12-2' => '&ndash;',
        );

        $tblSubject = Subject::useService()->getSubjectByName($subjectName);
        if (!$tblSubject && $subjectName == 'Gemeinschaftskunde/Rechtserziehung/Wirtschaft') {
            $tblSubject = Subject::useService()->getSubjectByAcronym('GRW');
        }

        if ($tblPerson
            && ($tblDivision = $this->getTblDivision())
            && ($tblLeaveStudent = Prepare::useService()->getLeaveStudentBy($tblPerson, $tblDivision))
            && $tblSubject
        ) {
            for ($level = 11; $level < 13; $level++) {
                for ($term = 1; $term < 3; $term++) {
                    $midTerm = $level . '-' . $term;
                    if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier($midTerm))
                        && ($tblLeaveAdditionalGrade = Prepare::useService()->getLeaveAdditionalGradeBy(
                            $tblLeaveStudent,
                            $tblSubject,
                            $tblPrepareAdditionalGradeType
                        ))
                    ) {
                        $value = str_pad($tblLeaveAdditionalGrade->getGrade(),2, 0, STR_PAD_LEFT);
                        $grades[$midTerm] = $value;
                    }
                }
            }
        }

        $points = '&ndash;';
        $finalGrade = '&ndash;';
        if ($tblLeaveStudent && $tblSubject) {
            $points = Prepare::useService()->calcAbiturLeaveGradePointsBySubject($tblLeaveStudent, $tblSubject);
            $finalGrade = Prepare::useService()->getAbiturLeaveGradeBySubject($points);
        }

        $section = new Section();
        if ($isForeignLanguage) {

            $levelFrom = '&ndash;';
            $levelTill = '&ndash;';

            if ($tblPerson
                && $tblSubject
                && ($tblStudent = $tblPerson->getStudent())
                && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
                && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent, $tblStudentSubjectType))
            ) {
                foreach ($tblStudentSubjectList as $tblStudentSubject) {
                    if (($tblSubjectItem = $tblStudentSubject->getServiceTblSubject())
                        && $tblSubjectItem->getId() == $tblSubject->getId()
                    ) {
                        if (($tblLevelFrom = $tblStudentSubject->getServiceTblLevelFrom())) {
                            $levelFrom = $tblLevelFrom->getName();
                        }

                        if (($tblLevelTill = $tblStudentSubject->getServiceTblLevelTill())) {
                            $levelTill = $tblLevelTill->getName();
                        }

                        break;
                    }
                }
            }

            $section
                ->addElementColumn((new Element())
                    ->setContent($subjectName)
                    ->stylePaddingLeft('3px')
                    ->styleTextSize($textSize)
                    ->styleBorderTop()
                    ->styleBorderLeft()
                    ->styleBorderBottom($isLastRow ? '1px' : '0px')
                    , '22%')
                ->addElementColumn((new Element())
                    ->setContent($levelFrom)
                    ->styleAlignCenter()
                    ->styleTextSize($textSize)
                    ->styleBorderTop()
                    ->styleBorderLeft()
                    ->styleBackgroundColor($colorForeignLanguage)
                    ->styleBorderBottom($isLastRow ? '1px' : '0px')
                    , '11%')
                ->addElementColumn((new Element())
                    ->setContent($levelTill)
                    ->styleAlignCenter()
                    ->styleTextSize($textSize)
                    ->styleBorderTop()
                    ->styleBackgroundColor($colorForeignLanguage)
                    ->styleBorderBottom($isLastRow ? '1px' : '0px')
                    , '11%');;
        } else {
            $section
                ->addElementColumn((new Element())
                    ->setContent($subjectName)
                    ->styleTextSize($textSize)
                    ->stylePaddingLeft('3px')
                    ->styleBorderTop()
                    ->styleBorderLeft()
                    ->styleBorderBottom($isLastRow ? '1px' : '0px')
                    , '44%');
        }

        $section
            ->addElementColumn((new Element())
                ->setContent($grades['11-1'])
                ->styleTextSize($textSize)
                ->styleAlignCenter()
                ->styleBackgroundColor($colorPoints)
                ->styleBorderTop()
                ->styleBorderLeft()
                ->styleBorderBottom($isLastRow ? '1px' : '0px')
                , '9%')
            ->addElementColumn((new Element())
                ->setContent($grades['11-2'])
                ->styleTextSize($textSize)
                ->styleAlignCenter()
                ->styleBackgroundColor($colorPoints)
                ->styleBorderTop()
                ->styleBorderLeft()
                ->styleBorderBottom($isLastRow ? '1px' : '0px')
                , '9%')
            ->addElementColumn((new Element())
                ->setContent($grades['12-1'])
                ->styleTextSize($textSize)
                ->styleAlignCenter()
                ->styleBackgroundColor($colorPoints)
                ->styleBorderTop()
                ->styleBorderLeft()
                ->styleBorderBottom($isLastRow ? '1px' : '0px')
                , '9%')
            ->addElementColumn((new Element())
                ->setContent($grades['12-2'])
                ->styleTextSize($textSize)
                ->styleAlignCenter()
                ->styleBackgroundColor($colorPoints)
                ->styleBorderTop()
                ->styleBorderLeft()
                ->styleBorderBottom($isLastRow ? '1px' : '0px')
                , '9%')
            ->addElementColumn((new Element())
                ->setContent($points)
                ->styleTextSize($textSize)
                ->styleAlignCenter()
                ->styleBackgroundColor($colorPoints)
                ->styleBorderTop()
                ->styleBorderLeft()
                ->styleBorderBottom($isLastRow ? '1px' : '0px')
                , '8%')
            ->addElementColumn((new Element())
                ->setContent($finalGrade)
                ->styleTextSize($textSize)
                ->styleAlignCenter()
                ->styleBackgroundColor($colorPoints)
                ->styleBorderTop()
                ->styleBorderLeft()
                ->styleBorderRight()
                ->styleBorderBottom($isLastRow ? '1px' : '0px')
            );

        return $section;
    }

    /**
     * @param $i
     * @param $text
     *
     * @return Section
     */
    private function setInfoRow($i, $text)
    {
        $section = new Section();
        $section
            ->addElementColumn((new Element())
                ->setContent(new Sup($i))
                ->styleTextSize('7px')
                ->styleMarginTop('2px')
                , '3%')
            ->addElementColumn((new Element())
                ->setContent($text)
                ->styleTextSize('9.5px')
                ->styleMarginTop('2px')
            );

        return $section;
    }
}