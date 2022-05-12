<?php

namespace SPHERE\Application\Education\Certificate\Reporting;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use PHPExcel_Style_Alignment;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Mail\Service\Entity\TblType as TblMailType;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter\StringGermanOrderSorter;

class Service extends Extension
{
    /**
     * @param TblType $tblSchoolType
     * @param TblCourse|null $tblCourse
     * @param array $subjectList
     *
     * @return array
     */
    public function getDiplomaSerialMailContent(TblType $tblSchoolType, ?TblCourse $tblCourse, array &$subjectList): array
    {
        $tblLevel = false;
        $isMainCourse = $tblCourse && $tblCourse->getName() == 'Hauptschule';
        if ($tblSchoolType->getShortName() == 'OS') {
            if ($isMainCourse) {
                $tblLevel = Division::useService()->getLevelBy($tblSchoolType, '9');
            } else {
                $tblLevel = Division::useService()->getLevelBy($tblSchoolType, '10');
            }
        } elseif ($tblSchoolType->getShortName() == 'FOS') {
            $tblLevel = Division::useService()->getLevelBy($tblSchoolType, '12');
        }

        $content = array();
        if ($tblLevel && ($tblYearList = Term::useService()->getYearByNow())
            && ($tblMailTypePrivat = Mail::useService()->getTypeById(1))
            && ($tblMailTypeBusiness = Mail::useService()->getTypeById(2))
        ) {
            foreach ($tblYearList as $tblYear) {
                if (($tblDivisionList = Division::useService()->getDivisionAllByLevelAndYear($tblLevel, $tblYear))) {
                    $tblDivisionList = $this->getSorter($tblDivisionList)->sortObjectBy('DisplayName', new StringGermanOrderSorter());
                    foreach ($tblDivisionList as $tblDivision) {
                        $gradeList = array();
                        $tblPrepareHalfYear = false;
                        $tblPrepareDiploma = false;
                        if (($tblPrepareList = Prepare::useService()->getPrepareAllByDivision($tblDivision))) {
                            foreach ($tblPrepareList as $tblPrepare) {
                                if (($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())
                                    &&  ($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
                                ) {
                                    if ($tblCertificateType->getIdentifier() == 'HALF_YEAR') {
                                        $tblPrepareHalfYear = $tblPrepare;
                                    } elseif ($tblCertificateType->getIdentifier() == 'DIPLOMA') {
                                        $tblPrepareDiploma = $tblPrepare;
                                        if (($tblTask = $tblPrepare->getServiceTblAppointedDateTask())
                                            && ($tblTestList = Evaluation::useService()->getTestAllByTask($tblTask))
                                        ) {
                                            foreach ($tblTestList as $tblTest) {
                                                if (($tblSubject = $tblTest->getServiceTblSubject())
                                                    && ($tblGradeList = Gradebook::useService()->getGradeAllByTest($tblTest))
                                                ) {
                                                    if (!isset($subjectList[$tblSubject->getAcronym()]['JN'])) {
                                                        $subjectList[$tblSubject->getAcronym()]['JN'] = $tblSubject;
                                                    }
                                                    foreach ($tblGradeList as $tblGrade) {
                                                        if (($tblGrade->getGrade()) && ($tblPersonGrade = $tblGrade->getServiceTblPerson())) {
                                                            $gradeList[$tblPersonGrade->getId()][$tblSubject->getAcronym()]['JN'] = $tblGrade->getDisplayGrade();
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        if (($tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision))) {
                            foreach ($tblStudentList as $tblPerson) {
                                if (($tblStudent = $tblPerson->getStudent())) {
                                    $tblCourseStudent = $tblStudent->getCourse();
                                } else {
                                    $tblCourseStudent = false;
                                }
                                // bei Hauptschüler in der Klasse 9, die Realschüler überspringen
                                if ($isMainCourse && (!$tblCourseStudent || $tblCourseStudent->getId() != $tblCourse->getId())) {
                                    continue;
                                }

                                $item = array();
                                $item['Division'] = $tblDivision->getDisplayName();
                                $item['LastName'] = $tblPerson->getLastName();
                                $item['FirstName'] = $tblPerson->getFirstName();
                                $item['Gender'] = ($tblCommonGender = $tblPerson->getGender()) ? $tblCommonGender->getShortName() : '';
                                // bei Schülern sind die Email-Adressen der Schule meist als Geschäftlich angelegt
                                $item['Student-Mail'] = $this->getMailAddress($tblPerson, $tblMailTypeBusiness);
                                if (($tblPersonRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson))) {
                                    foreach ($tblPersonRelationshipList as $tblPersonTo) {
                                        if ($tblPersonTo->getServiceTblPersonFrom() && ($tblPersonTo->getTblType()->getName() == 'Sorgeberechtigt')) {
                                            $item['Custody-Mail-S' . $tblPersonTo->getRanking()]
                                                = $this->getMailAddress($tblPersonTo->getServiceTblPersonFrom(), $tblMailTypePrivat);
                                        }
                                    }
                                }

                                if (isset($gradeList[$tblPerson->getId()])) {
                                    $item['Grades'] = $gradeList[$tblPerson->getId()];
                                }
                                $this->getGradesForSerialMail($item, $subjectList, $tblPerson, $tblPrepareHalfYear, $tblPrepareDiploma);

                                $content[$tblPerson->getId()] = $item;
                            }
                        }
                    }
                }
            }
        }

        return $content;
    }

    /**
     * @param array $content
     * @param array $subjectList
     *
     * @return bool|FilePointer
     */
    public function createDiplomaSerialMailContentExcel(array $content, array $subjectList): ?FilePointer
    {
        if (!empty($content)) {
            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $row = 0;
            $column = 0;
            $export->setValue($export->getCell($column++, $row), 'Klasse');
            $export->setValue($export->getCell($column++, $row), 'Name');
            $export->setValue($export->getCell($column++, $row), 'Vorname');
            $export->setValue($export->getCell($column++, $row), 'Geschlecht');
            $export->setValue($export->getCell($column++, $row), 'E-Mail des Schülers');
            $export->setValue($export->getCell($column++, $row), 'E-Mail des S1');
            $export->setValue($export->getCell($column++, $row), 'E-Mail des S2');
            // Fächer
            ksort($subjectList);
            foreach($subjectList as $acronym => &$array) {
                if (isset($array['HJN'])) {
                    $export->setValue($export->getCell($column, $row), $acronym . ' HJN');
                    $array['HJN'] = $column++;
                }
                if (isset($array['JN'])) {
                    $export->setValue($export->getCell($column, $row), $acronym . ' JN');
                    $array['JN'] = $column++;
                }
                if (isset($array['PS'])) {
                    $export->setValue($export->getCell($column, $row), $acronym . ' PS');
                    $array['PS'] = $column++;
                }
                if (isset($array['PM'])) {
                    $export->setValue($export->getCell($column, $row), $acronym . ' PM');
                    $array['PM'] = $column++;
                }
                if (isset($array['PZ'])) {
                    $export->setValue($export->getCell($column, $row), $acronym . ' PZ');
                    $array['PZ'] = $column++;
                }
                if (isset($array['EN'])) {
                    $export->setValue($export->getCell($column, $row), $acronym . ' EN');
                    $array['EN'] = $column++;
                }
            }
            foreach($subjectList as $acronym => &$array) {
                if (isset($array['PRIOR_YEAR_GRADE'])) {
                    $export->setValue($export->getCell($column, $row), 'abgeschl. 9 OS - ' . $acronym);
                    $array['PRIOR_YEAR_GRADE'] = $column++;
                }
            }

            $row++;
            foreach ($content as $item) {
                $column = 0;
                $export->setValue($export->getCell($column++, $row), $item['Division']);
                $export->setValue($export->getCell($column++, $row), $item['LastName']);
                $export->setValue($export->getCell($column++, $row), $item['FirstName']);
                $export->setValue($export->getCell($column++, $row), $item['Gender']);
                $export->setValue($export->getCell($column++, $row), $item['Student-Mail']);
                $export->setValue($export->getCell($column++, $row), $item['Custody-Mail-S1'] ?? '');
                $export->setValue($export->getCell($column, $row), $item['Custody-Mail-S2'] ?? '');

                if (isset($item['Grades'])) {
                    foreach ($item['Grades'] as $subjectAcronym => $gradeList) {
                        foreach ($gradeList as $identifier => $grade) {
                            if (isset($subjectList[$subjectAcronym][$identifier])) {
                                $export->setValue($export->getCell($subjectList[$subjectAcronym][$identifier], $row), $grade);
                            }
                        }
                    }
                }

                $row++;
            }

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblMailType $tblMailType
     *
     * @return string
     */
    private function getMailAddress(TblPerson $tblPerson, TblMailType $tblMailType): string {
        if (($tblMailList = Mail::useService()->getMailAllByPerson($tblPerson))) {
            foreach ($tblMailList as $tblMail) {
                if ($tblMail->getTblType()->getId() == $tblMailType->getId()) {
                    return $tblMail->getTblMail()->getAddress();
                }
            }

            return (reset($tblMailList))->getTblMail()->getAddress();
        }

        return '';
    }

    /**
     * @param array $item
     * @param array $subjectList
     * @param TblPerson $tblPerson
     * @param TblPrepareCertificate|null $tblPrepareHalfYear
     * @param TblPrepareCertificate|null $tblPrepareDiploma
     */
    private function getGradesForSerialMail(array &$item, array &$subjectList, TblPerson $tblPerson, ?TblPrepareCertificate $tblPrepareHalfYear, ?TblPrepareCertificate $tblPrepareDiploma)
    {
        if ($tblPrepareHalfYear && ($tblTestType = Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK'))
            && ($tblPrepareGradeList = Prepare::useService()->getPrepareGradeAllByPerson($tblPrepareHalfYear, $tblPerson, $tblTestType))
        ) {
            foreach ($tblPrepareGradeList as $tblPrepareGrade) {
                if (($tblSubjectHalfYear = $tblPrepareGrade->getServiceTblSubject()) && $tblPrepareGrade->getGrade()) {
                    if (!isset($subjectList[$tblSubjectHalfYear->getAcronym()]['HJN'])) {
                        $subjectList[$tblSubjectHalfYear->getAcronym()]['HJN'] = $tblSubjectHalfYear;
                    }
                    $item['Grades'][$tblSubjectHalfYear->getAcronym()]['HJN'] = $tblPrepareGrade->getGrade();
                }
            }
        }

        if ($tblPrepareDiploma && ($tblPrepareAdditionalGradeList = Prepare::useService()->getPrepareAdditionalGradeListBy($tblPrepareDiploma, $tblPerson))) {
            foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                if (($tblSubjectDiploma = $tblPrepareAdditionalGrade->getServiceTblSubject()) && $tblPrepareAdditionalGrade->getGrade()) {
                    $identifier = $tblPrepareAdditionalGrade->getTblPrepareAdditionalGradeType()->getIdentifier();
                    if (!isset($subjectList[$tblSubjectDiploma->getAcronym()][$identifier])) {
                        $subjectList[$tblSubjectDiploma->getAcronym()][$identifier] = $tblSubjectDiploma;
                    }
                    $item['Grades'][$tblSubjectDiploma->getAcronym()][$identifier] = $tblPrepareAdditionalGrade->getGrade();
                }
            }
        }
    }

    /**
     * @param TblType $tblSchoolType
     * @param TblCourse|null $tblCourse
     *
     * @return array
     */
    public function getDiplomaStatisticContent(TblType $tblSchoolType, ?TblCourse $tblCourse): array
    {
        $tblLevel = false;
        $isMainCourse = $tblCourse && $tblCourse->getName() == 'Hauptschule';
        if ($tblSchoolType->getShortName() == 'OS') {
            if ($isMainCourse) {
                $tblLevel = Division::useService()->getLevelBy($tblSchoolType, '9');
            } else {
                $tblLevel = Division::useService()->getLevelBy($tblSchoolType, '10');
            }
        } elseif ($tblSchoolType->getShortName() == 'FOS') {
            $tblLevel = Division::useService()->getLevelBy($tblSchoolType, '12');
        }

        $content = array();
        if ($tblLevel && ($tblYearList = Term::useService()->getYearByNow())) {
            foreach ($tblYearList as $tblYear) {
                if (($tblDivisionList = Division::useService()->getDivisionAllByLevelAndYear($tblLevel, $tblYear))) {
                    foreach ($tblDivisionList as $tblDivision) {
                        $gradeList = array();
                        $tblPrepareDiploma = false;
                        if (($tblPrepareList = Prepare::useService()->getPrepareAllByDivision($tblDivision))) {
                            foreach ($tblPrepareList as $tblPrepare) {
                                if (($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())
                                    &&  ($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
                                ) {
                                    if ($tblCertificateType->getIdentifier() == 'DIPLOMA') {
                                        $tblPrepareDiploma = $tblPrepare;
                                        if (($tblTask = $tblPrepare->getServiceTblAppointedDateTask())
                                            && ($tblTestList = Evaluation::useService()->getTestAllByTask($tblTask))
                                        ) {
                                            foreach ($tblTestList as $tblTest) {
                                                if (($tblSubject = $tblTest->getServiceTblSubject())
                                                    && ($tblGradeList = Gradebook::useService()->getGradeAllByTest($tblTest))
                                                ) {
                                                    foreach ($tblGradeList as $tblGrade) {
                                                        if (($tblGrade->getGrade()) && ($tblPersonGrade = $tblGrade->getServiceTblPerson())) {
                                                            $gradeList[$tblPersonGrade->getId()][$tblSubject->getAcronym()]['JN'] = $tblGrade->getGrade();
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        if (($tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision))) {
                            foreach ($tblStudentList as $tblPerson) {
                                if (($tblStudent = $tblPerson->getStudent())) {
                                    $tblCourseStudent = $tblStudent->getCourse();
                                } else {
                                    $tblCourseStudent = false;
                                }
                                // bei Hauptschüler in der Klasse 9, die Realschüler überspringen
                                if ($isMainCourse && (!$tblCourseStudent || $tblCourseStudent->getId() != $tblCourse->getId())) {
                                    continue;
                                }

                                if (isset($gradeList[$tblPerson->getId()])) {
                                    $this->setGradesForStatistic($content, $gradeList[$tblPerson->getId()], $tblPerson, $tblPrepareDiploma);
                                }
                            }
                        }
                    }
                }
            }
        }

//        $content = array();
//        $content['DE'] = array();
//        $content['MA'] = array();

        return $content;
    }

    /**
     * @param array $content
     * @param array $gradeListPerson
     * @param TblPerson $tblPerson
     * @param TblPrepareCertificate|null $tblPrepareDiploma
     */
    private function setGradesForStatistic(array &$content, array $gradeListPerson, TblPerson $tblPerson, ?TblPrepareCertificate $tblPrepareDiploma)
    {
        if ($tblPrepareDiploma
            && ($tblCommonGender = $tblPerson->getGender())
            && ($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('PS'))
            && ($tblPrepareAdditionalGradeList = Prepare::useService()->getPrepareAdditionalGradeListBy($tblPrepareDiploma, $tblPerson, $tblPrepareAdditionalGradeType))
        ) {
            foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                if (($tblSubject = $tblPrepareAdditionalGrade->getServiceTblSubject())
                    && $tblPrepareAdditionalGrade->getGrade() && intval($tblPrepareAdditionalGrade->getGrade())
                    && isset($gradeListPerson[$tblSubject->getAcronym()]['JN'])
                ) {
                    // Matrix JN - PS
                    if (isset($content[$tblSubject->getAcronym()][$tblCommonGender->getShortName()][$gradeListPerson[$tblSubject->getAcronym()]['JN']][$tblPrepareAdditionalGrade->getGrade()])) {
                        $content[$tblSubject->getAcronym()][$tblCommonGender->getShortName()][$gradeListPerson[$tblSubject->getAcronym()]['JN']][$tblPrepareAdditionalGrade->getGrade()]++;
                    } else {
                        $content[$tblSubject->getAcronym()][$tblCommonGender->getShortName()][$gradeListPerson[$tblSubject->getAcronym()]['JN']][$tblPrepareAdditionalGrade->getGrade()] = 1;
                    }

                    // Gesamtzahl nach Geschlecht
                    if (isset($content[$tblSubject->getAcronym()][$tblCommonGender->getShortName()]['Count'])) {
                        $content[$tblSubject->getAcronym()][$tblCommonGender->getShortName()]['Count']++;
                    } else {
                        $content[$tblSubject->getAcronym()][$tblCommonGender->getShortName()]['Count'] = 1;
                    }
                }
            }
        }
    }

    /**
     * @param array $content
     *
     * @return bool|FilePointer
     */
    public function createDiplomaStatisticContentExcel(array $content): ?FilePointer
    {
        if (!empty($content)) {
            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());

            $row = 0;
            foreach ($content as $acronym => $array) {
                if (($tblSubject = Subject::useService()->getSubjectByAcronym($acronym))) {
                    $export->setValue($export->getCell(0, $row), 'Prüfungsfach: ' . $tblSubject->getDisplayName());
                    $export->setStyle($export->getCell(0, $row), $export->getCell(0, $row))->setFontBold();
                    $row++;
                    $export->setValue($export->getCell(3, $row), 'Jahresnote');
                    $export->setStyle($export->getCell(3, $row), $export->getCell(3, $row))->setFontBold();
                    $export->setStyle($export->getCell(3, $row), $export->getCell(8, $row))->mergeCells()->setAlignmentCenter();
                    $row++;
                    $export->setValue($export->getCell(0, $row + 1), 'Prüfungsnote');
                    $export->setStyle($export->getCell(0, $row + 1), $export->getCell(0, $row + 1))->setFontBold();
                    $export->getActiveSheet()->getStyle($export->getCell(0, $row + 1)->getCellName())->getAlignment()
                        ->setTextRotation(90)
                        ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $export->setStyle($export->getCell(0, $row + 1), $export->getCell(0, $row + 12))->mergeCells();

                    for ($i = 1; $i < 7; $i++) {
                        // Jahresnoten Reihe
                        $export->setValue($export->getCell(2 + $i, $row), $i);
                        $export->setStyle($export->getCell(2 + $i, $row), $export->getCell(2 + $i, $row))->setAlignmentCenter();
                        for ($j = 1; $j < 7; $j++) {
                            // Prüfungsnoten Spalte
                            $export->setValue($export->getCell(1, $row - 1 + 2*$j), $j);
                            $export->setStyle($export->getCell(1, $row - 1 + 2*$j), $export->getCell(1, $row - 1 + 2*$j + 1))->mergeCells()->setAlignmentCenter();
                            $export->getActiveSheet()->getStyle($export->getCell(1, $row - 1 + 2*$j)->getCellName())->getAlignment()
                                ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                            // Geschlecht Spalte
                            $export->setValue($export->getCell(2, $row - 1 + 2*$j), 'männlich');
                            $export->setValue($export->getCell(2, $row - 1 + 2*$j + 1), 'weiblich');

                            if (isset($array['m'][$i][$j])) {
                                $export->setValue($export->getCell(2 + $i, $row - 1 + 2*$j), $array['m'][$i][$j]);
                            }
                            if (isset($array['w'][$i][$j])) {
                                $export->setValue($export->getCell(2 + $i, $row - 1 + 2*$j + 1), $array['w'][$i][$j]);
                            }
                        }
                    }

                    $export->setValue($export->getCell(10, $row + 1), 'männlich:');
                    $export->setValue($export->getCell(10, $row + 2), 'weiblich:');
                    $export->setValue($export->getCell(10, $row + 3), 'Prüflinge:');

                    $export->setValue($export->getCell(11, $row + 1), $array['m']['Count'] ?? 0);
                    $export->setValue($export->getCell(11, $row + 2), $array['w']['Count'] ?? 0 );
                    $export->setValue($export->getCell(11, $row + 3), ($array['m']['Count'] ?? 0) + ($array['w']['Count'] ?? 0));
                }

                $export->setStyle($export->getCell(3, $row - 1), $export->getCell(8, $row))->setBorderAll();
                $export->setStyle($export->getCell(0, $row + 1), $export->getCell(8, $row + 12))->setBorderAll();

                $row += 15;
            }

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }
}