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
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareStudent;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
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
        $level = false;
        $isMainCourse = $tblCourse && $tblCourse->getName() == 'Hauptschule';
        if ($tblSchoolType->getShortName() == 'OS') {
            if ($isMainCourse) {
                $level = 9;
            } else {
                $level = 10;
            }
        } elseif ($tblSchoolType->getShortName() == 'FOS') {
            $level = 12;
        }

        $content = array();
        if ($level
            && ($tblYearList = Term::useService()->getYearByNow())
            && ($tblMailTypePrivat = Mail::useService()->getTypeById(1))
            && ($tblMailTypeBusiness = Mail::useService()->getTypeById(2))
        ) {
            foreach ($tblYearList as $tblYear) {
                list($tblDivisionCourseList, $personCourseList) = $this->getDivisionCourseListByYearAndSchoolTypeAndLevel($tblYear, $tblSchoolType, $level);
                if ($tblDivisionCourseList) {
                    $tblDivisionCourseList = $this->getSorter($tblDivisionCourseList)->sortObjectBy('DisplayName', new StringGermanOrderSorter());
                    /** @var TblDivisionCourse $tblDivisionCourse */
                    foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                        $tblPrepareHalfYear = false;
                        $tblPrepareDiploma = false;
                        if (($tblPrepareList = Prepare::useService()->getPrepareAllByDivisionCourse($tblDivisionCourse))) {
                            foreach ($tblPrepareList as $tblPrepare) {
                                if (($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())
                                    &&  ($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
                                ) {
                                    if ($tblCertificateType->getIdentifier() == 'HALF_YEAR') {
                                        $tblPrepareHalfYear = $tblPrepare;
                                    } elseif ($tblCertificateType->getIdentifier() == 'DIPLOMA') {
                                        $tblPrepareDiploma = $tblPrepare;
                                    }
                                }
                            }
                        }

                        if (($tblStudentList = $tblDivisionCourse->getStudentsWithSubCourses())) {
                            foreach ($tblStudentList as $tblPerson) {
                                $tblCourseStudent = $personCourseList[$tblPerson->getId()] ?? false;
                                // bei Hauptschüler in der Klasse 9, die Realschüler überspringen
                                if ($isMainCourse && (!$tblCourseStudent || $tblCourseStudent->getId() != $tblCourse->getId())) {
                                    continue;
                                }

                                $item = array();
                                $item['Division'] = $tblDivisionCourse->getName();
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

                                $tblPrepareHalfYear = $tblPrepareHalfYear? :null;
                                $tblPrepareDiploma = $tblPrepareDiploma? :null;
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
            $export->setValue($export->getCell($column++, $row), 'Kurs');
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
                if (isset($array['LS'])) {
                    $export->setValue($export->getCell($column, $row), $acronym . ' LS');
                    $array['LS'] = $column++;
                }
                if (isset($array['LM'])) {
                    $export->setValue($export->getCell($column, $row), $acronym . ' LM');
                    $array['LM'] = $column++;
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
                            if (isset($subjectList[$subjectAcronym][$identifier]) && intval($subjectList[$subjectAcronym][$identifier])) {
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
        if ($tblPrepareHalfYear
            && ($tblTask = $tblPrepareHalfYear->getServiceTblAppointedDateTask())
            && ($tblTaskGradeList = Grade::useService()->getTaskGradeListByTaskAndPerson($tblTask, $tblPerson))
        ) {
            foreach ($tblTaskGradeList as $tblTaskGrade) {
                if (($tblSubjectHalfYear = $tblTaskGrade->getServiceTblSubject())
                    && $tblTaskGrade->getGrade() !== null
                ) {
                    if (!isset($subjectList[$tblSubjectHalfYear->getAcronym()]['HJN'])) {
                        $subjectList[$tblSubjectHalfYear->getAcronym()]['HJN'] = $tblSubjectHalfYear;
                    }
                    $item['Grades'][$tblSubjectHalfYear->getAcronym()]['HJN'] = $tblTaskGrade->getGrade();
                }
            }
        }

        if ($tblPrepareDiploma) {
            if (($tblPrepareAdditionalGradeList = Prepare::useService()->getPrepareAdditionalGradeListBy($tblPrepareDiploma, $tblPerson))) {
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

            if (($tblTask = $tblPrepareDiploma->getServiceTblAppointedDateTask())
                && ($tblTaskGradeList = Grade::useService()->getTaskGradeListByTaskAndPerson($tblTask, $tblPerson))
            ) {
                foreach ($tblTaskGradeList as $tblTaskGrade) {
                    if (($tblSubjectYear = $tblTaskGrade->getServiceTblSubject())
                        && $tblTaskGrade->getGrade() !== null
                    ) {
                        if (!isset($subjectList[$tblSubjectYear->getAcronym()]['JN'])) {
                            $subjectList[$tblSubjectYear->getAcronym()]['JN'] = $tblSubjectYear;
                        }
                        $item['Grades'][$tblSubjectYear->getAcronym()]['JN'] = $tblTaskGrade->getGrade();
                    }
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
        $level = false;
        $isMainCourse = $tblCourse && $tblCourse->getName() == 'Hauptschule';
        if ($tblSchoolType->getShortName() == 'OS') {
            if ($isMainCourse) {
                $level = 9;
            } else {
                $level = 10;
            }
        } elseif ($tblSchoolType->getShortName() == 'FOS') {
            $level = 12;
        }

        $content = array();
        if ($level && ($tblYearList = Term::useService()->getYearByNow())) {
            foreach ($tblYearList as $tblYear) {
                list($tblDivisionCourseList, $personCourseList) = $this->getDivisionCourseListByYearAndSchoolTypeAndLevel($tblYear, $tblSchoolType, $level);
                if ($tblDivisionCourseList) {
                    $tblDivisionCourseList = $this->getSorter($tblDivisionCourseList)->sortObjectBy('DisplayName', new StringGermanOrderSorter());
                    /** @var TblDivisionCourse $tblDivisionCourse */
                    foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                        $tblPrepareDiploma = false;
                        if (($tblPrepareList = Prepare::useService()->getPrepareAllByDivisionCourse($tblDivisionCourse))) {
                            foreach ($tblPrepareList as $tblPrepare) {
                                if (($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())
                                    && ($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
                                    && $tblCertificateType->getIdentifier() == 'DIPLOMA'
                                ) {
                                    $tblPrepareDiploma = $tblPrepare;
                                }
                            }
                        }

                        if (($tblStudentList = $tblDivisionCourse->getStudentsWithSubCourses())) {
                            foreach ($tblStudentList as $tblPerson) {
                                $tblCourseStudent = $personCourseList[$tblPerson->getId()] ?? false;
                                // bei Hauptschüler in der Klasse 9, die Realschüler überspringen
                                if ($isMainCourse && (!$tblCourseStudent || $tblCourseStudent->getId() != $tblCourse->getId())) {
                                    continue;
                                }

                                $gradeList = array();
                                if (($tblTask = $tblPrepareDiploma->getServiceTblAppointedDateTask())
                                    && ($tblTaskGradeList = Grade::useService()->getTaskGradeListByTaskAndPerson($tblTask, $tblPerson))
                                ) {
                                    foreach ($tblTaskGradeList as $tblTaskGrade) {
                                        if (($tblSubject = $tblTaskGrade->getServiceTblSubject())
                                            && $tblTaskGrade->getGrade() !== null
                                        ) {
                                            $gradeList[$tblSubject->getAcronym()]['JN'] = $tblTaskGrade->getGrade();
                                        }
                                    }
                                }

                                if (isset($gradeList)) {
                                    $this->setGradesForStatistic($content, $gradeList, $tblPerson, $tblPrepareDiploma, $isMainCourse);
                                }
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
     * @param array $gradeListPerson
     * @param TblPerson $tblPerson
     * @param TblPrepareCertificate|null $tblPrepareDiploma
     * @param bool $isMainCourse
     */
    private function setGradesForStatistic(array &$content, array $gradeListPerson, TblPerson $tblPerson, ?TblPrepareCertificate $tblPrepareDiploma, bool $isMainCourse)
    {
        if ($tblPrepareDiploma
            && ($tblCommonGender = $tblPerson->getGender())
            && ($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier($isMainCourse ? 'LS' : 'PS'))
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

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array
     */
    public function getCourseGradesContent(TblDivisionCourse $tblDivisionCourse): array
    {
        $content = array();
        if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())) {
            foreach ($tblPersonList as $tblPerson) {
                $prepareStudentList = Prepare::useService()->getPrepareStudentListFromMidTermCertificatesByPerson($tblPerson);

                // get PrepareStudent Diploma
                if (($tblCertificateType = Generator::useService()->getCertificateTypeByIdentifier('DIPLOMA'))
                    && ($tblPrepareStudentTempList = Prepare::useService()->getPrepareStudentListByPersonAndCertificateType($tblPerson, $tblCertificateType))
                ) {
                    foreach ($tblPrepareStudentTempList as $tblPrepareStudent) {
                        $prepareStudentList['DIPLOMA'] = $tblPrepareStudent;
                        break;
                    }
                }

                list($advancedCourses) = DivisionCourse::useService()->getCoursesForStudent($tblPerson);
                ksort($prepareStudentList);
                /** @var TblPrepareStudent  $value */
                foreach ($prepareStudentList as $key => $value) {
                    if ($key == 'DIPLOMA') {
                        // Prüfungsnoten
                        if (($tblPrepareItem = $value->getTblPrepareCertificate())
                            && ($tblPrepareAdditionalGradeList = Prepare::useService()->getPrepareAdditionalGradeListBy($tblPrepareItem, $tblPerson))
                        ) {
                            foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                                if (($tblPrepareAdditionalGradeType = $tblPrepareAdditionalGrade->getTblPrepareAdditionalGradeType())
                                    && ($tblPrepareAdditionalGradeType->getIdentifier() == 'WRITTEN_EXAM'
                                        || $tblPrepareAdditionalGradeType->getIdentifier() == 'VERBAL_EXAM'
                                        || $tblPrepareAdditionalGradeType->getIdentifier() == 'EXTRA_VERBAL_EXAM')
                                    && ($tblSubjectItem = $tblPrepareAdditionalGrade->getServiceTblSubject())
                                    && $tblPrepareAdditionalGrade->getGrade() !== ''
                                ) {
                                    $content[] = array(
                                        'Name' => $tblPerson->getLastFirstName(),
                                        'Term' => $tblPrepareAdditionalGradeType->getName(),
                                        'Subject' => $tblSubjectItem->getAcronym(),
                                        'Course' => $tblPrepareAdditionalGrade->getRanking() < 3 ? 'L' : 'G',
                                        'Grade' => $tblPrepareAdditionalGrade->getGrade()
                                    );
                                }
                            }
                        }
                    } else {
                        // Kursnoten
                        if (($tblPrepareItem = $value->getTblPrepareCertificate())
                            && ($tblTask = $tblPrepareItem->getServiceTblAppointedDateTask())
                            && ($tblTaskGradeList = Grade::useService()->getTaskGradeListByTaskAndPerson($tblTask, $tblPerson))
                        ) {
                            foreach ($tblTaskGradeList as $tblTaskGrade) {
                                if (($tblSubject = $tblTaskGrade->getServiceTblSubject())) {
                                    $content[] = array(
                                        'Name' => $tblPerson->getLastFirstName(),
                                        'Term' => $key,
                                        'Subject' => $tblSubject->getAcronym(),
                                        'Course' => isset($advancedCourses[$key][$tblSubject->getId()]) ? 'L' : 'G',
                                        'Grade' => $tblTaskGrade->getGrade()
                                    );
                                }
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
     *
     * @return bool|FilePointer
     */
    public function createCourseGradesContentExcel(array $content): ?FilePointer
    {
        if (!empty($content)) {
            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());

            $row = 0;
            $column = 0;
            $export->setValue($export->getCell($column++, $row), 'Schüler');
            $export->setValue($export->getCell($column++, $row), 'Kurshalbjahr/Prüfung');
            $export->setValue($export->getCell($column++, $row), 'Fach');
            $export->setValue($export->getCell($column++, $row), 'Kurstyp');
            $export->setValue($export->getCell($column, $row), 'Punkte');
            $export->setStyle($export->getCell(0, $row), $export->getCell($column, $row))->setFontBold();

            foreach ($content as $item) {
                $row++;
                $column = 0;
                $export->setValue($export->getCell($column++, $row), $item['Name']);
                $export->setValue($export->getCell($column++, $row), $item['Term']);
                $export->setValue($export->getCell($column++, $row), $item['Subject']);
                $export->setValue($export->getCell($column++, $row), $item['Course']);
                $export->setValue($export->getCell($column++, $row), $item['Grade']);
            }

            //Column width
            $column = 0;
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(25);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(25);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(10);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(10);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(10);

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param TblYear $tblYear
     * @param TblType $tblSchoolType
     * @param int $level
     *
     * @return array[]
     */
    public function getDivisionCourseListByYearAndSchoolTypeAndLevel(TblYear $tblYear, TblType $tblSchoolType, int $level): array
    {
        $tblDivisionCourseList = array();
        $personCourseList = array();
        if (($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListBy($tblYear, $tblSchoolType, $level))) {
            foreach ($tblStudentEducationList as $tblStudentEducation) {
                if (($tblDivision = $tblStudentEducation->getTblDivision()) && !isset($tblDivisionCourseList[$tblDivision->getId()])) {
                    $tblDivisionCourseList[$tblDivision->getId()] = $tblDivision;
                }
                if (($tblCoreGroup = $tblStudentEducation->getTblCoreGroup()) && !isset($tblDivisionCourseList[$tblCoreGroup->getId()])) {
                    $tblDivisionCourseList[$tblCoreGroup->getId()] = $tblCoreGroup;
                }
                if (($tblPersonTemp = $tblStudentEducation->getServiceTblPerson())
                    && ($tblCourseTemp = $tblStudentEducation->getServiceTblCourse())
                ) {
                    $personCourseList[$tblPersonTemp->getId()] = $tblCourseTemp;
                }
            }
        }

        return array($tblDivisionCourseList, $personCourseList);
    }

    /**
     * @param array $tblDivisionCourseList
     * @param TblYear $tblYear
     * @param TblType $tblSchoolType
     * @param int $level
     */
    public function setDivisionCourseList(array &$tblDivisionCourseList, TblYear $tblYear, TblType $tblSchoolType, int $level)
    {
        if (($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListBy($tblYear, $tblSchoolType, $level))) {
            foreach ($tblStudentEducationList as $tblStudentEducation) {
                if (($tblDivision = $tblStudentEducation->getTblDivision()) && !isset($tblDivisionCourseList[$tblDivision->getId()])) {
                    $tblDivisionCourseList[$tblDivision->getId()] = $tblDivision;
                }
                if (($tblCoreGroup = $tblStudentEducation->getTblCoreGroup()) && !isset($tblDivisionCourseList[$tblCoreGroup->getId()])) {
                    $tblDivisionCourseList[$tblCoreGroup->getId()] = $tblCoreGroup;
                }
            }
        }
    }
}