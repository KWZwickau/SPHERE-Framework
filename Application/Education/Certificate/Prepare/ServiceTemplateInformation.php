<?php

namespace SPHERE\Application\Education\Certificate\Prepare;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Api\Education\Certificate\Generator\Repository\BeGs;
use SPHERE\Application\Api\Education\Prepare\ApiPrepare;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Certificate\Setting\Setting;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeText;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Setting\Consumer\Consumer as ConsumerSetting;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\Editor;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Icon\Repository\Enable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Warning;

abstract class ServiceTemplateInformation extends ServiceLeave
{
    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     * @param $studentTable
     * @param $columnTable
     * @param $Data
     * @param $CertificateList
     * @param $Page
     * @param $informationPageList
     */
    public function getTemplateInformation(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson,
        &$studentTable,
        &$columnTable,
        $Data,
        &$CertificateList,
        $Page = null,
        $informationPageList = null
    ) {

        $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepareCertificate, $tblPerson);
        if ($tblPrepareStudent && $tblPrepareStudent->getServiceTblCertificate()) {
            $tblCertificate = $tblPrepareStudent->getServiceTblCertificate();
        } else {
            $tblCertificate = false;
        }

        if (($tblSetting = ConsumerSetting::useService()->getSetting('Education', 'Certificate', 'Prepare', 'ShowOrientationsInCertificateRemark'))) {
            $showOrientationsInCertificateRemark = $tblSetting->getValue();
        } else {
            $showOrientationsInCertificateRemark = false;
        }
        if (($tblSetting = ConsumerSetting::useService()->getSetting('Education', 'Certificate', 'Prepare', 'ShowTeamsInCertificateRemark'))) {
            $showTeamsInCertificateRemark = $tblSetting->getValue();
        } else {
            $showTeamsInCertificateRemark = false;
        }

        if ($tblCertificate
            && ($tblDivisionCourse = $tblPrepareCertificate->getServiceTblDivision())
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $tblCertificate->getCertificate();
            if (class_exists($CertificateClass)) {
                $tblSchoolType = $tblCertificate->getServiceTblSchoolType();
                // Wahlbereich gibt es nur bei der Oberschule
                $showOrientationsInCertificateRemark = $showOrientationsInCertificateRemark
                    && ($tblSchoolType->getShortName() == 'OS');

                $tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear);
                /** @var Certificate $Certificate */
                $Certificate = new $CertificateClass($tblStudentEducation ?: null, $tblPrepareCertificate);

                // create Certificate with Placeholders
                $pageList[$tblPerson->getId()] = $Certificate->buildPages($tblPerson);
                $Certificate->createCertificate($Data, $pageList);

                $CertificateList[$tblPerson->getId()] = $Certificate;

                $FormField = Generator::useService()->getFormField();
                $FormLabel = Generator::useService()->getFormLabel($tblSchoolType ?: null);

                $level = 0;
                if (($tblYear = $tblDivisionCourse->getServiceTblYear())) {
                    if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                        $level = $tblStudentEducation->getLevel();
                    }
                }

                if ($Data === null) {
                    $Global = $this->getGlobal();
                    $tblPrepareInformationAll = Prepare::useService()->getPrepareInformationAllByPerson($tblPrepareCertificate, $tblPerson);
                    $hasTransfer = false;
                    $isTeamSet = false;
                    $hasRemarkText = false;
                    $hasEducationDateFrom = false;
                    $isSubjectAreaSet = false;
                    $tblStudent = $tblPerson->getStudent();
                    if ($tblPrepareInformationAll) {
                        foreach ($tblPrepareInformationAll as $tblPrepareInformation) {
                            if ($tblPrepareInformation->getField() == 'Team' || $tblPrepareInformation->getField() == 'TeamExtra') {
                                $isTeamSet = true;
                            }

                            if ($tblPrepareInformation->getField() == 'Remark' || $tblPrepareInformation->getField() == 'RemarkWithoutTeam') {
                                $hasRemarkText = true;
                            }

                            if ($tblPrepareInformation->getField() == 'EducationDateFrom') {
                                $hasEducationDateFrom = true;
                            }

                            if ($tblPrepareInformation->getField() == 'SubjectArea') {
                                $isSubjectAreaSet = true;
                            }

                            if ($tblPrepareInformation->getField() == 'SchoolType'
                                && method_exists($Certificate, 'selectValuesSchoolType')
                            ) {
                                $Global->POST['Data'][$tblPrepareStudent->getId()][$tblPrepareInformation->getField()] =
                                    array_search($tblPrepareInformation->getValue(),
                                        $Certificate->selectValuesSchoolType());
                            } elseif ($tblPrepareInformation->getField() == 'Type'
                                && method_exists($Certificate, 'selectValuesType')
                            ) {
                                $Global->POST['Data'][$tblPrepareStudent->getId()][$tblPrepareInformation->getField()] =
                                    array_search($tblPrepareInformation->getValue(),
                                        $Certificate->selectValuesType());
                            }  elseif ($tblPrepareInformation->getField() == 'Success'
                                && method_exists($Certificate, 'selectValuesSuccess')
                            ) {
                                $Global->POST['Data'][$tblPrepareStudent->getId()][$tblPrepareInformation->getField()] =
                                    array_search($tblPrepareInformation->getValue(),
                                        $Certificate->selectValuesSuccess());
                            } elseif ($tblPrepareInformation->getField() == 'Transfer'
                                && method_exists($Certificate, 'selectValuesTransfer')
                            ) {
                                $Global->POST['Data'][$tblPrepareStudent->getId()][$tblPrepareInformation->getField()] =
                                    array_search($tblPrepareInformation->getValue(), $Certificate->selectValuesTransfer());
                                $hasTransfer = true;
                            } elseif ($tblPrepareInformation->getField() == 'Job_Grade_Text'
                                && method_exists($Certificate, 'selectValuesJobGradeText')
                            ) {
                                $Global->POST['Data'][$tblPrepareStudent->getId()][$tblPrepareInformation->getField()] =
                                    array_search($tblPrepareInformation->getValue(),
                                        $Certificate->selectValuesJobGradeText());
//                                } elseif ($tblPrepareInformation->getField() == 'FoesAbsText' // SSW-1685 Auswahl soll aktuell nicht verfügbar sein, bis aufweiteres aufheben
//                                    && method_exists($Certificate, 'selectValuesFoesAbsText')
//                                ) {
//                                    $Global->POST['Data'][$tblPrepareStudent->getId()][$tblPrepareInformation->getField()] =
//                                        array_search($tblPrepareInformation->getValue(),
//                                            $Certificate->selectValuesFoesAbsText());
                            } elseif (strpos($tblPrepareInformation->getField(), '_GradeText')
                                && ($tblGradeText = Grade::useService()->getGradeTextByName($tblPrepareInformation->getValue()))
                            ) {
                                // Zeugnistext umwandeln
                                $Global->POST['Data'][$tblPrepareStudent->getId()][$tblPrepareInformation->getField()] = $tblGradeText->getId();
                            } elseif ($tblPrepareInformation->getField() == 'AdditionalRemarkFhr') {
                                // Checkbox
                                $Global->POST['Data'][$tblPrepareStudent->getId()][$tblPrepareInformation->getField()]
                                    = $tblPrepareInformation->getValue() ? 1 : 0;
                            } else {
                                $Global->POST['Data'][$tblPrepareStudent->getId()][$tblPrepareInformation->getField()]
                                    = $tblPrepareInformation->getValue();
                            }
                        }
                    }

                    // Coswig Versetzungsvermerk in die Bemerkung vorsetzten
                    if (!$hasRemarkText
                        && Consumer::useService()->getConsumerBySessionIsConsumer(TblConsumer::TYPE_SACHSEN, 'EVSC')
                        && ($tblCertificateType = $tblCertificate->getTblCertificateType())
                        && $tblCertificateType->getIdentifier() == 'YEAR'
                    ) {
                        $nextLevel = 'x';
                        if ($level) {
                            $nextLevel = $level + 1;
                        }
                        $Global->POST['Data'][$tblPrepareStudent->getId()]['Remark'] =
                            $tblPerson->getFirstSecondName() . ' wird versetzt in Klasse ' . $nextLevel . '.';
                    }

                    // Arbeitsgemeinschaften aus der Schülerakte laden
                    if (!$isTeamSet && $showTeamsInCertificateRemark) {
                        if ($tblStudent
                            && ($tblSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('TEAM'))
                            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                                $tblStudent, $tblSubjectType
                            ))
                        ) {
                            $tempList = array();
                            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                                if ($tblStudentSubject->getServiceTblSubject()) {
                                    $tempList[] = $tblStudentSubject->getServiceTblSubject()->getName();
                                }
                            }
                            if (!empty($tempList)) {
                                $Global->POST['Data'][$tblPrepareStudent->getId()]['Team'] = implode(', ', $tempList);
                                $Global->POST['Data'][$tblPrepareStudent->getId()]['TeamExtra'] = implode(', ', $tempList);
                            }
                        }
                    }

                    // Wahlbereich aus der Schülerakte laden
                    if ($showOrientationsInCertificateRemark) {
                        if ($tblStudent
                            && ($tblSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'))
                            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                                $tblStudent, $tblSubjectType
                            ))
                        ) {
                            /** @var TblStudentSubject $tblStudentSubject */
                            $tblStudentSubject = reset($tblStudentSubjectList);
                            if (($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
                                $Global->POST['Data'][$tblPrepareStudent->getId()]['Orientation']
                                    = $tblPerson->getFirstSecondName().' hat im Rahmen des Wahlbereiches am Kurs "'
                                    .$tblSubject->getName().'" teilgenommen.';
                            }
                        }
                    }

                    // Vorsetzen auf Versetzungsvermerk: wird versetzt
                    if (!$hasTransfer) {
                        $Global->POST['Data'][$tblPrepareStudent->getId()]['Transfer'] = 1;
                    }

                    // SSW-340 Halbjahreszeugnis Klasse 10 OS -> abgewählte Fächer in die Bemerkung vorsetzen
                    if (!$hasRemarkText && $tblYear
                        && (($Certificate->getCertificateEntity()->getCertificate() == 'MsHjRs')
                            || ($level == 10 && $Certificate->getCertificateEntity()->getCertificate() == 'HOGA\MsHjZ')
                        )) {
                        if (($tblDroppedSubjectList = Prepare::useService()->getAutoDroppedSubjects($tblPerson, $tblYear))) {
                            $countDroppedSubjects = count($tblDroppedSubjectList);
                            if ($countDroppedSubjects == 1) {
                                $text = current($tblDroppedSubjectList) . ' wurde in der Klassenstufe 9 abgeschlossen.';
                            } else {
                                $countItem = 0;
                                $text = '';
                                $tblDroppedSubjectList = $this->getSorter($tblDroppedSubjectList)->sortObjectBy('Name');
                                /** @var TblSubject $tblSubject */
                                foreach ($tblDroppedSubjectList as $tblSubject) {
                                    $countItem++;
                                    $name = $tblSubject->getName();
                                    if ($countItem == 1) {
                                        $text .= $name;
                                    } elseif ($countItem == $countDroppedSubjects) {
                                        $text .= ' und ' . $name;
                                    } else {
                                        $text .= ', ' . $name;
                                    }
                                }

                                $text .=  ' wurden in der Klassenstufe 9 abgeschlossen.';
                            }

                            $Global->POST['Data'][$tblPrepareStudent->getId()]['Remark'] = $text;
                        }
                    }

                    /*
                    * Individuelle Zeugnisse EVGSM Meerane Klassename vorsetzen
                    */
                    if ($Page == null
                        && strpos($Certificate->getCertificateEntity()->getCertificate(), 'EVGSM') !== false
                    ) {
                        $Global->POST['Data'][$tblPrepareStudent->getId()]['DivisionName'] = $tblDivisionCourse->getDisplayName();
                    }

                    // GTA setzen, werden in der Schülerakte als Arbeitsgemeinschaften gepflegt
                    if (($tblStudent = $tblPerson->getStudent())
                        && ($tblSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('TEAM'))
                        && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                            $tblStudent, $tblSubjectType
                        ))
                    ) {
                        $tempList = array();
                        foreach ($tblStudentSubjectList as $tblStudentSubject) {
                            if ($tblStudentSubject->getServiceTblSubject()) {
                                $tempList[] = $tblStudentSubject->getServiceTblSubject()->getName();
                            }
                        }

                        $textGTA = $tblPerson->getFirstSecondName() . ' besuchte in diesem Schuljahr ';

                        switch (count($tempList)) {
                            case 1: $textGTA .= 'das GTA ' . $tempList[0] . '.';
                                break;
                            case 2: $textGTA .= 'die GTA ' . $tempList[0]
                                . ' und ' . $tempList[1] . '.';
                                break;
                            case 3: $textGTA .= 'die GTA ' . $tempList[0]
                                . ', ' . $tempList[1]
                                . ' und ' . $tempList[2] . '.';
                                break;
                            case 4: $textGTA .= 'die GTA ' . $tempList[0]
                                . ', ' . $tempList[1]
                                . ', ' . $tempList[2]
                                . ' und ' . $tempList[3] . '.';
                                break;
                            case 5: $textGTA .= 'die GTA ' . $tempList[0]
                                . ', ' . $tempList[1]
                                . ', ' . $tempList[2]
                                . ', ' . $tempList[3]
                                . ' und ' . $tempList[4] . '.';
                                break;
                        }

                        $Global->POST['Data'][$tblPrepareStudent->getId()]['GTA'] = $textGTA;
                    }

                    // Vorsetzen des Schulbesuchsjahrs
                    if (($tblStudent = $tblPerson->getStudent())
                        && !isset($Global->POST['Data'][$tblPrepareStudent->getId()]['SchoolVisitYear'])) {
                        $Global->POST['Data'][$tblPrepareStudent->getId()]['SchoolVisitYear'] = $tblStudent->getSchoolAttendanceYear(false);
                    }

                    $isSupportForPrimarySchool = false;
                    // Seelitz Förderbedarf-Satz in die Bemerkung vorsetzen
                    if (!$hasRemarkText
                        && Consumer::useService()->getConsumerBySessionIsConsumer(TblConsumer::TYPE_SACHSEN, 'ESRL')
                    ) {
                        $isSupportForPrimarySchool = true;
                        // staatliche und pseudostaatliche Grundschulzeugnisse Förderbedarf-Satz in die Bemerkung vorsetzen
                    } elseif (!$hasRemarkText
                        && ($Certificate->getCertificateEntity()->getCertificate() == 'GsHjInformation'
                            || $Certificate->getCertificateEntity()->getCertificate() == 'GsHjOneInfo'
                            || $Certificate->getCertificateEntity()->getCertificate() == 'GsJa'
                            || $Certificate->getCertificateEntity()->getCertificate() == 'GsJOne'

                            || $Certificate->getCertificateEntity()->getCertificate() == 'ESZC\CheHjInfoGs'
                            || $Certificate->getCertificateEntity()->getCertificate() == 'ESZC\CheHjInfoGsOne'
                            || $Certificate->getCertificateEntity()->getCertificate() == 'ESZC\CheJGs'
                            || $Certificate->getCertificateEntity()->getCertificate() == 'ESZC\CheJGsOne'
                        )
                    ) {
                        $isSupportForPrimarySchool = true;
                    } elseif (!$hasRemarkText
                        && ($Certificate->getCertificateEntity()->getCertificate() == 'ESBD\EsbdGsHjInformation'
                            || $Certificate->getCertificateEntity()->getCertificate() == 'ESBD\EsbdGsHjOneInfo'
                            || $Certificate->getCertificateEntity()->getCertificate() == 'ESBD\EsbdGsJa'
                            || $Certificate->getCertificateEntity()->getCertificate() == 'ESBD\EsbdGsJOne'
                        )
                    ) {
                        $isSupportForPrimarySchool = true;
                    }

                    if ($isSupportForPrimarySchool) {
                        $textSupport = '';
                        if (($tblSupport = Student::useService()->getSupportForReportingByPerson($tblPerson))
                            && ($tblPrimaryFocus = Student::useService()->getPrimaryFocusBySupport($tblSupport))
                        ) {
                            if ($tblPrimaryFocus->getName() == 'Lernen') {
                                $textSupport = $tblPerson->getFirstSecondName() . ' ' . $tblPerson->getLastName()
                                    . ' wurde inklusiv nach den Lehrplänen der Schule mit dem Förderschwerpunkt Lernen unterrichtet.';
                            }
                            if ($tblPrimaryFocus->getName() == 'Geistige Entwicklung') {
                                $textSupport = $tblPerson->getFirstSecondName() . ' ' . $tblPerson->getLastName()
                                    . ' wurde inklusiv nach den Lehrplänen der Schule mit dem Förderschwerpunkt geistige Entwicklung unterrichtet.';
                            }
                        }

                        // Seelitz
                        $Global->POST['Data'][$tblPrepareStudent->getId()]['RemarkWithoutTeam'] = $textSupport;
                        // staatliche GS-Zeugnisse
                        $Global->POST['Data'][$tblPrepareStudent->getId()]['Remark'] = $textSupport;
                    }

                    // Fachschule
                    if (!$hasRemarkText
                        && ($Certificate->getCertificateEntity()->getCertificate() == 'FsAbs'
                            || $Certificate->getCertificateEntity()->getCertificate() == 'FsAbsFhr')
                    ) {
                        $technicalCourseName = Student::useService()->getTechnicalCourseGenderNameByPerson($tblPerson);
                        $Global->POST['Data'][$tblPrepareStudent->getId()]['RemarkWithoutTeam'] = 'Der Abschluss '
                            . $technicalCourseName . ' ist im Deutschen und Europäischen Qualifikationsrahmen dem Niveau 6 zugeordnet.';
                    }
                    // Vorsetzen der Fachrichtung bei Fachschulen
                    if (!$isSubjectAreaSet
                        && $tblStudent
                        && ($tblStudentTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())
                        && ($tblTechnicalSubjectArea = $tblStudentTechnicalSchool->getServiceTblTechnicalSubjectArea())
                    ) {
                        $Global->POST['Data'][$tblPrepareStudent->getId()]['SubjectArea'] = $tblTechnicalSubjectArea->getName();
                    }

                    // Berufsfachschule
                    if (!$hasRemarkText
                        && $Certificate->getCertificateEntity()->getCertificate() == 'BfsAbs'
                    ) {
                        $technicalCourseName = Student::useService()->getTechnicalCourseGenderNameByPerson($tblPerson);
                        $Global->POST['Data'][$tblPrepareStudent->getId()]['RemarkWithoutTeam'] = 'Der Abschluss '
                            . $technicalCourseName . ' ist im Deutschen und Europäischen Qualifikationsrahmen dem Niveau 4 zugeordnet.';
                    }

                    // Berufsfachschule Pflegeberufe
                    if (!$hasRemarkText
                        && $Certificate->getCertificateEntity()->getCertificate() == 'BfsPflegeJ'
                        && $level == 3
                    ) {
                        $Global->POST['Data'][$tblPrepareStudent->getId()]['RemarkWithoutTeam'] = $tblPerson->getFullName()
                            . ' ' . ' hat regelmässig am theoretischen und praktischen Unterricht sowie der praktischen Ausbildung in den Klassenstufen 1 bis 3 teilgenommen.';
                    }
                    if ($Page == 2
                        && $tblCertificate->getName() == 'Berufsfachschule Jahreszeugnis'
                        && $tblCertificate->getDescription() == 'Generalistik'
                        && $tblYear
                        && ($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                        && ($tblTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())
                        && ($tblTechnicalCourse = $tblTechnicalSchool->getServiceTblTechnicalCourse())
                    ) {
                        if(($tblCertificateSubjectList = Setting::useService()->getCertificateSubjectAll($tblCertificate, $tblTechnicalCourse))){
                            $GradeSum = 0;
                            $GradeSumCount = 0;
                            $GradePracticalSum = 0;
                            $GradePracticalCount = 0;
                            foreach($tblCertificateSubjectList as $tblCertificateSubject){
                                if (($tblSubject = $tblCertificateSubject->getServiceTblSubject()) && $tblCertificateSubject->getRanking() != 15) {
                                    if (($tblGradeList = Grade::useService()->getTestGradeListByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject))) {
                                        foreach ($tblGradeList as $tblGrade) {
                                            if ($tblGrade->getIsGradeNumeric()) {
                                                $GradeSum += $tblGrade->getGradeNumberValue();
                                                $GradeSumCount++;
                                            }
                                        }
                                    }
                                } elseif(($tblSubject = $tblCertificateSubject->getServiceTblSubject()) && $tblCertificateSubject->getRanking() == 15) {
                                    if (($tblGradeList = Grade::useService()->getTestGradeListByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject))) {
                                        foreach ($tblGradeList as $tblGrade) {
                                            if ($tblGrade->getIsGradeNumeric()) {
                                                $GradePracticalSum += $tblGrade->getGradeNumberValue();
                                                $GradePracticalCount++;
                                            }
                                        }
                                    }
                                }
                            }

                            if ($GradeSumCount && !isset($Global->POST['Data'][$tblPrepareStudent->getId()]['YearGradeAverageLesson_Average'])) {
                                $Calc = round($GradeSum/$GradeSumCount, 2);
                                $Calc = number_format($Calc, 2, ",", ".");
                                $Global->POST['Data'][$tblPrepareStudent->getId()]['YearGradeAverageLesson_Average'] = $Calc;
                            }
                            if ($GradePracticalCount && !isset($Global->POST['Data'][$tblPrepareStudent->getId()]['YearGradeAveragePractical_Average'])) {
                                $Calc = round($GradePracticalSum/$GradePracticalCount, 2);
                                $Calc = number_format($Calc, 2, ",", ".");
                                $Global->POST['Data'][$tblPrepareStudent->getId()]['YearGradeAveragePractical_Average'] = $Calc;
                            }
                        }
                    }

                    // Fachoberschule HOGA Jahreszeugnis für Klassenstufe 12
                    if (!$hasRemarkText
                        && $Certificate->getCertificateEntity()->getCertificate() == 'HOGA\FosJ'
                        && $level == 12
                    ) {
                        $Global->POST['Data'][$tblPrepareStudent->getId()]['RemarkWithoutTeam'] = $tblPerson->getFullName()
                            . ' wurde zur Abschlussprüfung nicht zugelassen / hat die Abschlussprüfung nicht bestanden und kann erst nach erfolgreicher Wiederholung der Klassenstufe erneut an der Abschlussprüfung teilnehmen.';
                    }

                    // HOGA Beginn der Ausbildung bei Fachoberschule Abschlusszeugnissen
                    if (!$hasEducationDateFrom
                        && $Certificate->getCertificateEntity()->getCertificate() == 'HOGA\FosAbs'
                        && $tblStudent
                        && ($tblStudentTransfer = Student::useService()->getStudentTransferByType(
                            $tblStudent, Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE')
                        ))
                        && ($transferDate = $tblStudentTransfer->getTransferDate())
                    ) {
                        $Global->POST['Data'][$tblPrepareStudent->getId()]['EducationDateFrom'] = $transferDate;
                    }

                    $Global->savePost();
                }

                // bei der Aufteilung der sonstigen Informationen auf mehrere Seite müssen, diese auf der 1. Seite ignoriert werden
                $ignoreInformationOnFirstPage = array();
                if ($Page == null && isset($informationPageList[$tblCertificate->getId()])) {
                    foreach($informationPageList[$tblCertificate->getId()] as $pageList) {
                        foreach ($pageList as $pageItem) {
                            $ignoreInformationOnFirstPage[$pageItem] = $pageItem;
                        }
                    }
                }

                // Create Form, Additional Information from Template
                $PlaceholderList = $Certificate->getCertificate()->getPlaceholder();
                // Arbeitsgemeinschaften stehen extra und nicht in den Bemerkungen
                $hasTeamExtra = false;
                if ($PlaceholderList) {
                    array_walk($PlaceholderList,
                        function ($Placeholder) use (
                            $Certificate,
                            $FormField,
                            $FormLabel,
                            &$columnTable,
                            &$studentTable,
                            $tblPerson,
                            $tblPrepareStudent,
                            $tblCertificate,
                            &$hasTeamExtra,
                            $Page,
                            $ignoreInformationOnFirstPage,
                            $informationPageList,
                            $tblPrepareCertificate,
                            $showTeamsInCertificateRemark,
                            $showOrientationsInCertificateRemark
                        ) {

                            $PlaceholderList = explode('.', $Placeholder);
                            $Identifier = array_slice($PlaceholderList, 1);
                            if (isset($Identifier[0])) {
                                unset($Identifier[0]);
                            }

                            $FieldName = $PlaceholderList[0] . '[' . implode('][', $Identifier) . ']';

                            $dataFieldName = str_replace('Content[Input]', 'Data[' . $tblPrepareStudent->getId() . ']', $FieldName);

                            $PlaceholderName = str_replace('.P' . $tblPerson->getId(), '', $Placeholder);

                            $Type = array_shift($Identifier);
                            $key = str_replace('Content.Input.', '', $PlaceholderName);

                            // Entscheidung ob das Field auf der aktuelle Seite der sonstige Informationen angezeigt wird
                            $addField = true;
                            if ($Page == null) {
                                if (isset($ignoreInformationOnFirstPage[$key])) {
                                    $addField = false;
                                }
                            } else {
                                $addField = isset($informationPageList[$tblCertificate->getId()][$Page][$key]);
                            }

                            if ($addField && !method_exists($Certificate, 'get' . $Type)) {
                                if (isset($FormField[$PlaceholderName])) {
                                    if (isset($FormLabel[$PlaceholderName])) {
                                        $Label = $FormLabel[$PlaceholderName];
                                    } else {
                                        $Label = $PlaceholderName;
                                    }

                                    $isApiField = false;
                                    $isAdded = isset($columnTable[$key]);
                                    // ApiButton
                                    if (method_exists($Certificate, 'getApiModalColumns')) {
                                        /** @var BeGs $Certificate */
                                        if (isset($Certificate->getApiModalColumns()[$key])) {
                                            $isApiField = true;
                                            if (!$isAdded) {
                                                $columnTable[$key] = $Label
                                                    . new PullRight(
                                                        (new Standard('Alle bearbeiten', ApiPrepare::getEndpoint()))
                                                            ->ajaxPipelineOnClick(ApiPrepare::pipelineOpenInformationModal(
                                                                $tblPrepareCertificate->getId(),
                                                                $key,
                                                                $tblCertificate->getCertificate()
                                                            ))
                                                    );

                                                $isAdded = true;
                                            }
                                        }
                                    }
                                    if (!$isAdded) {
                                        $columnTable[$key] = $Label;
                                    }

                                    if ($key == 'TeamExtra' /*|| isset($columnTable['TeamExtra'])*/) {
                                        $hasTeamExtra = true;
                                    }

                                    if (isset($FormField[$PlaceholderName])) {
                                        $Field = '\SPHERE\Common\Frontend\Form\Repository\Field\\' . $FormField[$PlaceholderName];
                                        if ($Field == '\SPHERE\Common\Frontend\Form\Repository\Field\SelectBox') {
                                            $selectBoxData = array();
                                            if ($PlaceholderName == 'Content.Input.SchoolType'
                                                && method_exists($Certificate, 'selectValuesSchoolType')
                                            ) {
                                                $selectBoxData = $Certificate->selectValuesSchoolType();
                                            } elseif ($PlaceholderName == 'Content.Input.Type'
                                                && method_exists($Certificate, 'selectValuesType')
                                            ) {
                                                $selectBoxData = $Certificate->selectValuesType();
                                            } elseif ($PlaceholderName == 'Content.Input.Success'
                                                && method_exists($Certificate, 'selectValuesSuccess')
                                            ) {
                                                $selectBoxData = $Certificate->selectValuesSuccess();
                                            } elseif ($PlaceholderName == 'Content.Input.Transfer'
                                                && method_exists($Certificate, 'selectValuesTransfer')
                                            ) {
                                                $selectBoxData = $Certificate->selectValuesTransfer();
                                            } elseif ($PlaceholderName == 'Content.Input.Job_Grade_Text'
                                                && method_exists($Certificate, 'selectValuesJobGradeText')
                                            ) {
                                                $selectBoxData = $Certificate->selectValuesJobGradeText();
//                                                } elseif ($PlaceholderName == 'Content.Input.FoesAbsText' // SSW-1685 Auswahl soll aktuell nicht verfügbar sein, bis aufweiteres aufheben
//                                                    && method_exists($Certificate, 'selectValuesFoesAbsText')
//                                                ) {
//                                                    $selectBoxData = $Certificate->selectValuesFoesAbsText();
                                            } elseif (strpos($PlaceholderName, '_GradeText') !== false) {
                                                if (($tblGradeTextList = Grade::useService()->getGradeTextAll())) {
                                                    $selectBoxData = array(TblGradeText::ATTR_NAME => $tblGradeTextList);
                                                }
                                            }
                                            $selectBox = new SelectBox($dataFieldName, '', $selectBoxData);
                                            if ($tblPrepareStudent->isApproved()) {
                                                $studentTable[$tblPerson->getId()][$key] = $selectBox->setDisabled();
                                            } else {
                                                if ($isApiField) {
                                                    $studentTable[$tblPerson->getId()][$key] = ApiPrepare::receiverContent(
                                                        $selectBox,
                                                        'ChangeInformation_' . $key . '_' . $tblPerson->getId()
                                                    );
                                                } else {
                                                    $studentTable[$tblPerson->getId()][$key] = $selectBox;
                                                }
                                            }
                                        } elseif ($Field == '\SPHERE\Common\Frontend\Form\Repository\Field\SelectCompleter') {
                                            // Zensurenfeld
                                            $selectCompleterData[-1] = '';
                                            if (strpos($PlaceholderName, '_Average') !== false) {
                                                // _Average
                                                for ($i = 1; $i < 6; $i++) {
                                                    for ($j = 0; $j < 10; $j++) {
                                                        $value = $i . ',' . $j;
                                                        $selectCompleterData[$value] = $value;
                                                    }
                                                }
                                                $selectCompleterData['6,0'] = '6,0';
                                            } else {
                                                // _Grade
                                                for ($i = 1; $i <= 6; $i++) {
                                                    $selectCompleterData[$i] = (string)($i);
                                                }
                                            }
                                            $selectCompleter = new SelectCompleter($dataFieldName, '', '', $selectCompleterData);
                                            if ($tblPrepareStudent->isApproved()) {
                                                $studentTable[$tblPerson->getId()][$key] = $selectCompleter->setDisabled();
                                            } else {
                                                // noch kein Api verfügbar
//                                                    if ($isApiField) {
//                                                        $studentTable[$tblPerson->getId()][$key] = ApiPrepare::receiverContent(
//                                                            $selectCompleter,
//                                                            'ChangeInformation_' . $key . '_' . $tblPerson->getId()
//                                                        );
//                                                    } else {
                                                $studentTable[$tblPerson->getId()][$key] = $selectCompleter;
//                                                    }
                                            }
                                        } elseif ($Field == '\SPHERE\Common\Frontend\Form\Repository\Field\CheckBox') {
                                            // funktioniert aktuell nur für das Feld AdditionalRemarkFhr
                                            // auch noch kein Api verfügbar
                                            $checkBox = new CheckBox($dataFieldName, 'Erfolglose Teilnahme', 1);
                                            if ($tblPrepareStudent->isApproved()) {
                                                $studentTable[$tblPerson->getId()][$key] = $checkBox->setDisabled();
                                            } else {
//                                                    if ($isApiField) {
//                                                        $studentTable[$tblPerson->getId()][$key] = ApiPrepare::receiverContent(
//                                                            $checkBox,
//                                                            'ChangeInformation_' . $key . '_' . $tblPerson->getId()
//                                                        );
//                                                    } else {
                                                $studentTable[$tblPerson->getId()][$key] = $checkBox;
//                                                    }
                                            }
                                        } elseif ($Field == '\SPHERE\Common\Frontend\Form\Repository\Field\Editor') {
                                            if ($tblPrepareStudent->isApproved()){
                                                $Editor = (new Editor($dataFieldName))->setDisabled();
                                            } else {
                                                $Editor = new Editor($dataFieldName);
                                            }
                                            $studentTable[$tblPerson->getId()][$key] = $Editor;
                                        } else {
                                            if ($tblPrepareStudent->isApproved()) {
                                                $studentTable[$tblPerson->getId()][$key]
                                                    = (new $Field($dataFieldName, '', ''))->setDisabled();
                                            } else {
                                                // Arbeitsgemeinschaften beim Bemerkungsfeld
                                                if ($showTeamsInCertificateRemark
                                                    && !$hasTeamExtra
                                                    && $key == 'Remark'
                                                ) {
                                                    if (!isset($columnTable['Team'])) {
                                                        $columnTable['Team'] = 'Arbeitsgemeinschaften';
                                                    }
                                                    $studentTable[$tblPerson->getId()]['Team'] = (new TextField('Data[' . $tblPrepareStudent->getId() . '][Team]', '', ''));
                                                }

                                                // Wahlbereiche beim Bemerkungsfeld
                                                if ($showOrientationsInCertificateRemark
                                                    && $key == 'Remark'
                                                ) {
                                                    if (!isset($columnTable['Orientation'])) {
                                                        $columnTable['Orientation'] =
                                                            (Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'))->getName();
                                                    }
                                                    $studentTable[$tblPerson->getId()]['Orientation']
                                                        = (new TextArea('Data[' . $tblPrepareStudent->getId() . '][Orientation]',
                                                        '', ''));
                                                }

                                                // TextArea Zeichen begrenzen
                                                if ($FormField[$PlaceholderName] == 'TextArea'
                                                    && (($CharCount = Generator::useService()->getCharCountByCertificateAndField(
                                                        $tblCertificate, $key, !$hasTeamExtra
                                                    )))
                                                ) {
                                                    /** @var TextArea $Field */
                                                    $studentTable[$tblPerson->getId()][$key]
                                                        = (new TextArea($dataFieldName, '', ''))->setMaxLengthValue(
                                                        $CharCount, true
                                                    );
                                                } else {
                                                    if ($isApiField) {
                                                        $studentTable[$tblPerson->getId()][$key] = ApiPrepare::receiverContent(
                                                            new $Field($dataFieldName, '', ''),
                                                            'ChangeInformation_' . $key . '_' . $tblPerson->getId()
                                                        );
                                                    } else  {
                                                        $studentTable[$tblPerson->getId()][$key] = (new $Field($dataFieldName, '', ''));
                                                    }
                                                }
                                            }
                                        }
                                    } else {
                                        if ($tblPrepareStudent->isApproved()) {
                                            $studentTable[$tblPerson->getId()][$key] = (new TextField($FieldName, '', ''))->setDisabled();
                                        } else {
                                            $studentTable[$tblPerson->getId()][$key] = (new TextField($FieldName, '', ''));
                                        }
                                    }
                                }
                            }
                        });
                }

                if ($Page == null) {
                    // für Förderzeugnisse Lernen extra Spalte Inklusive Unterrichtung
                    $isSupportLearningCertificate = false;
                    if (strpos($tblCertificate->getCertificate(), 'FsLernen') !== false) {
                        $isSupportLearningCertificate = true;
                    }

                    if ($isSupportLearningCertificate && $tblPrepareStudent) {
                        if (!isset($columnTable['Support'])) {
                            $columnTable['Support'] = 'Inklusive Unterrichtung';
                        }

                        $textArea = new TextArea('Data[' . $tblPrepareStudent->getId() . '][Support]', '', '');
                        if ($tblPrepareStudent->isApproved()) {
                            $textArea->setDisabled();
                        }

                        $studentTable[$tblPerson->getId()]['Support'] = $textArea;
                    }
                }
            }
        }
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     * @param $studentTable
     * @param $columnTable
     */
    public function getTemplateInformationForPreview(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson,
        &$studentTable,
        &$columnTable
    ) {
        $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepareCertificate, $tblPerson);
        if ($tblPrepareStudent && $tblPrepareStudent->getServiceTblCertificate()) {
            $tblCertificate = $tblPrepareStudent->getServiceTblCertificate();
        } else {
            $tblCertificate = false;
        }

        if ($tblCertificate
            && ($tblDivisionCourse = $tblPrepareCertificate->getServiceTblDivision())
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $tblCertificate->getCertificate();

            if (class_exists($CertificateClass)) {
                $tblSchoolType = $tblCertificate->getServiceTblSchoolType();
                $tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear);
                /** @var Certificate $Certificate */
                $Certificate = new $CertificateClass($tblStudentEducation ?: null, $tblPrepareCertificate);

                // create Certificate with Placeholders
                $pageList[$tblPerson->getId()] = $Certificate->buildPages($tblPerson);
                $Certificate->createCertificate(array(), $pageList);

                $FormField = Generator::useService()->getFormField();
                $FormLabel = Generator::useService()->getFormLabel($tblSchoolType ?: null);

                $PlaceholderList = $Certificate->getCertificate()->getPlaceholder();
                if ($PlaceholderList) {
                    array_walk($PlaceholderList,
                        function ($Placeholder) use (
                            $Certificate,
                            $FormField,
                            $FormLabel,
                            &$columnTable,
                            &$studentTable,
                            $tblPerson,
                            $tblPrepareStudent
                        ) {

                            $PlaceholderList = explode('.', $Placeholder);
                            $Identifier = array_slice($PlaceholderList, 1);
                            if (isset($Identifier[0])) {
                                unset($Identifier[0]);
                            }

                            $PlaceholderName = str_replace('.P' . $tblPerson->getId(), '', $Placeholder);

                            $Type = array_shift($Identifier);
                            if (!method_exists($Certificate, 'get' . $Type)) {
                                if (isset($FormField[$PlaceholderName])) {
                                    $Label = $FormLabel[$PlaceholderName] ?? $PlaceholderName;

                                    $key = str_replace('Content.Input.', '', $PlaceholderName);
                                    if (!isset($columnTable[$key])) {
                                        $columnTable[$key] = $Label;
                                    }

                                    if (isset($FormField[$PlaceholderName]) && $FormField[$PlaceholderName] == 'TextArea') {
                                        if (($tblPrepareInformation = Prepare::useService()->getPrepareInformationBy(
                                                $tblPrepareStudent->getTblPrepareCertificate(), $tblPerson, $key))
                                            && trim($tblPrepareInformation->getValue())
                                        ) {
                                            $studentTable[$tblPerson->getId()][$key] = new Success(new Enable() . ' ' . 'erledigt');
                                        } else {
                                            $studentTable[$tblPerson->getId()][$key] = new Warning(new Exclamation() . ' ' . 'nicht erledigt');
                                        }
                                    } else {
                                        if (($tblPrepareInformation = Prepare::useService()->getPrepareInformationBy(
                                            $tblPrepareStudent->getTblPrepareCertificate(), $tblPerson, $key))
                                        ) {
                                            $studentTable[$tblPerson->getId()][$key] = $tblPrepareInformation->getValue();
                                        } else {
                                            $studentTable[$tblPerson->getId()][$key] = '';
                                        }
                                    }
                                }
                            }
                        });
                }
            }
        }
    }
}