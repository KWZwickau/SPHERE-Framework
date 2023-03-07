<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.07.2016
 * Time: 10:42
 */

namespace SPHERE\Application\Education\Certificate\Prepare;

use DateTime;
use SPHERE\Application\Api\Education\Certificate\Generator\Repository\BeGs;
use SPHERE\Application\Api\Education\Certificate\Generator\Repository\GymAbgSekI;
use SPHERE\Application\Api\Education\Certificate\Generator\Repository\GymAbgSekII;
use SPHERE\Application\Api\Education\Prepare\ApiPrepare;
use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Abitur\BlockIView;
use SPHERE\Application\Education\Certificate\Prepare\Abitur\LeavePoints;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveStudent;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Certificate\Setting\Setting;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeText;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Setting\Consumer\Consumer as ConsumerSetting;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\Editor;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\CommodityItem;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Enable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\ResizeVertical;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Icon\Repository\Unchecked;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Certificate\Prepare
 */
class Frontend extends FrontendTechnicalSchool
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
    protected function getTemplateInformation(
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
                /** @var \SPHERE\Application\Api\Education\Certificate\Generator\Certificate $Certificate */
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
                                foreach ($tblDroppedSubjectList as $name) {
                                    $countItem++;
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
                /** @var \SPHERE\Application\Api\Education\Certificate\Generator\Certificate $Certificate */
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
                                    if (isset($FormLabel[$PlaceholderName])) {
                                        $Label = $FormLabel[$PlaceholderName];
                                    } else {
                                        $Label = $PlaceholderName;
                                    }

                                    $key = str_replace('Content.Input.', '', $PlaceholderName);
                                    if (!isset($columnTable[$key])) {
                                        $columnTable[$key] = $Label;
                                    }

                                    if (isset($FormField[$PlaceholderName]) && $FormField[$PlaceholderName] == 'TextArea') {
                                        if (($tblPrepareInformation = Prepare::useService()->getPrepareInformationBy(
                                                $tblPrepareStudent->getTblPrepareCertificate(), $tblPerson, $key))
                                            && trim($tblPrepareInformation->getValue())
                                        ) {
                                            $studentTable[$tblPerson->getId()][$key] =
                                                new Success(new Enable() . ' ' . 'erledigt');
                                        } else {
                                            $studentTable[$tblPerson->getId()][$key] =
                                                new \SPHERE\Common\Frontend\Text\Repository\Warning(
                                                    new Exclamation() . ' ' . 'nicht erledigt');
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

    /**
     * @param null $PrepareId
     * @param null $GroupId
     * @param null $Route
     *
     * @return Stage|string
     */
    public function frontendOldPrepareShowSubjectGrades($PrepareId = null, $GroupId = null, $Route = null)
    {
        // todo remove
        $Stage = new Stage('Zeugnisvorbereitung', 'Fachnoten-Übersicht');

        $description = '';
        $tblPrepareList = false;
        $tblGroup = false;
        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))) {
            $tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate();
            if ($GroupId && ($tblGroup = Group::useService()->getGroupById($GroupId))) {
                $description = 'Gruppe ' . $tblGroup->getName();
                if (($tblGenerateCertificate)) {
                    $tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate);
                }
            } else {
                if (($tblDivisionTemp = $tblPrepare->getServiceTblDivision())) {
                    $description = 'Klasse ' . $tblDivisionTemp->getDisplayName();
                    $tblPrepareList = array(0 => $tblPrepare);
                }
            }

            $Stage->addButton(new Standard('Zurück', '/Education/Certificate/Prepare/Prepare/Preview',
                    new ChevronLeft(),
                    array(
                        'PrepareId' => $PrepareId,
                        'GroupId' => $GroupId,
                        'Route' => $Route
                    )
                )
            );

            $studentList = array();
            $tableHeaderList = array();
            $divisionList = array();
            $divisionPersonList = array();
            $averageGradeList = array();

            if ($tblPrepareList
                && $tblGenerateCertificate
                && ($tblTask = $tblGenerateCertificate->getServiceTblAppointedDateTask())
            ) {
                foreach ($tblPrepareList as $tblPrepareItem) {
                    if (($tblDivision = $tblPrepareItem->getServiceTblDivision())
                        && ($tblDivisionStudentAll = Division::useService()->getStudentAllByDivision($tblDivision))
                    ) {
                        // Alle Klassen ermitteln in denen der Schüler im Schuljahr Unterricht hat
                        foreach ($tblDivisionStudentAll as $tblPerson) {
                            if (!$tblGroup || Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                                $studentList[$tblPerson->getId()]['Name'] = $tblPerson->getLastFirstName();
                                if (($tblYear = $tblDivision->getServiceTblYear())
                                    && ($tblPersonDivisionList = Student::useService()->getDivisionListByPersonAndYearAndIsNotInActive(
                                        $tblPerson,
                                        $tblYear
                                    ))
                                ) {
                                    foreach ($tblPersonDivisionList as $tblDivisionItem) {
                                        if (!isset($divisionList[$tblDivisionItem->getId()])) {
                                            $divisionList[$tblDivisionItem->getId()] = $tblDivisionItem;
                                        }
                                    }
                                }
                                $divisionPersonList[$tblPerson->getId()] = 1;
                            }
                        }

                        foreach ($divisionList as $tblDivisionItem) {
                            if (($tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblTask,
                                $tblDivisionItem))
                            ) {
                                $tblType = $tblDivisionItem->getType();
                                $hasExams = ($Route == 'Diploma' && ($tblType && ($tblType->getShortName() == 'OS' || $tblType->getShortName() == 'FOS' || $tblType->getShortName() == 'BFS')));

                                foreach ($tblTestAllByTask as $tblTest) {
                                    $tblSubject = $tblTest->getServiceTblSubject();
                                    if ($tblSubject && $tblTest->getServiceTblDivision()) {
                                        $tableHeaderList[$tblSubject->getAcronym()] = $tblSubject->getAcronym();
                                        $studentList[0][$tblSubject->getAcronym()] = '';

                                        $tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
                                            $tblTest->getServiceTblDivision(),
                                            $tblSubject,
                                            $tblTest->getServiceTblSubjectGroup() ? $tblTest->getServiceTblSubjectGroup() : null
                                        );

                                        if ($tblDivisionSubject && $tblDivisionSubject->getTblSubjectGroup()) {
                                            $tblSubjectStudentAllByDivisionSubject =
                                                Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubject);
                                            if ($tblSubjectStudentAllByDivisionSubject) {
                                                foreach ($tblSubjectStudentAllByDivisionSubject as $tblSubjectStudent) {

                                                    $tblPerson = $tblSubjectStudent->getServiceTblPerson();
                                                    if (!$tblGroup || Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                                                        if ($tblPerson && isset($divisionPersonList[$tblPerson->getId()])) {
                                                            if ($hasExams) {
                                                                $studentList = $this->setDiplomaGrade($tblPrepareItem,
                                                                    $tblPerson,
                                                                    $tblSubject, $studentList);
                                                            } else {
                                                                $studentList = $this->setTableContentForAppointedDateTask($tblDivision,
                                                                    $tblTest, $tblSubject, $tblPerson, $studentList,
                                                                    $tblDivisionSubject->getTblSubjectGroup()
                                                                        ? $tblDivisionSubject->getTblSubjectGroup() : null,
                                                                    $tblPrepareItem,
                                                                    $averageGradeList
                                                                );
                                                            }
                                                        }
                                                    }
                                                }

                                                // nicht vorhandene Schüler in der Gruppe auf leer setzten
                                                if ($tblDivisionStudentAll) {
                                                    foreach ($tblDivisionStudentAll as $tblPersonItem) {
                                                        if (!$tblGroup || Group::useService()->existsGroupPerson($tblGroup, $tblPersonItem)) {
                                                            if (!isset($studentList[$tblPersonItem->getId()][$tblSubject->getAcronym()])) {
                                                                $studentList[$tblPersonItem->getId()][$tblSubject->getAcronym()] = '';
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        } else {
                                            if ($tblDivisionStudentAll) {
                                                foreach ($tblDivisionStudentAll as $tblPerson) {
                                                    if (!$tblGroup || Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                                                        // nur Schüler der ausgewählten Klasse
                                                        if (isset($divisionPersonList[$tblPerson->getId()])) {
                                                            if ($hasExams) {
                                                                $studentList = $this->setDiplomaGrade($tblPrepareItem,
                                                                    $tblPerson,
                                                                    $tblSubject, $studentList);
                                                            } else {
                                                                $studentList = $this->setTableContentForAppointedDateTask($tblDivision,
                                                                    $tblTest, $tblSubject, $tblPerson, $studentList,
                                                                    null,
                                                                    $tblPrepareItem,
                                                                    $averageGradeList
                                                                );
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $count = 1;
            foreach ($studentList as $personId => $student){
                $studentList[$personId]['Number'] = $count++;
                foreach ($tableHeaderList as $column) {
                    if (!isset($studentList[$personId][$column])) {
                        $studentList[$personId][$column] = '';
                    }
                }
            }

            // Durchschnitte pro Fach-Klasse
            $studentList[0]['Number'] = '';
            $studentList[0]['Name'] = new Muted('&#216; Fach-Klasse');
            foreach ($averageGradeList as $subjectId => $grades) {
                $countGrades = count($grades);
                if (($item = Subject::useService()->getSubjectById($subjectId))) {
                    $studentList[0][$item->getAcronym()] = $countGrades > 0
                        ? round(array_sum($grades) / $countGrades, 2) : '';
                }
            }

            if (!empty($tableHeaderList)) {
                ksort($tableHeaderList);
                $prependTableHeaderList['Number'] = '#';
                $prependTableHeaderList['Name'] = 'Schüler';
                $tableHeaderList = $prependTableHeaderList + $tableHeaderList;
                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Panel(
                                        'Zeugnisvorbereitung',
                                        array(
                                            $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                            $description
                                        ),
                                        Panel::PANEL_TYPE_INFO
                                    ),
                                )),
                                new LayoutColumn(array(
                                    new TableData(
                                        $studentList, null, $tableHeaderList, null
                                    )
                                ))
                            ))
                        ))
                    ))
                );
            }

            return $Stage;

        } else {

            return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden', new Exclamation());
        }
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblTest $tblTest
     * @param TblSubject $tblSubject
     * @param TblPerson $tblPerson
     * @param $studentList
     * @param TblSubjectGroup $tblSubjectGroup
     * @param TblPrepareCertificate $tblPrepare
     *
     * @return  $studentList
     */
    private function setTableContentForAppointedDateTask(
        TblDivision $tblDivision,
        TblTest $tblTest,
        TblSubject $tblSubject,
        TblPerson $tblPerson,
        $studentList,
        TblSubjectGroup $tblSubjectGroup = null,
        TblPrepareCertificate $tblPrepare = null,
        &$averageGradeList = array()
    ) {
        $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
            $tblPerson);

        $tblTask = $tblTest->getTblTask();

        $tblScoreRule = Gradebook::useService()->getScoreRuleByDivisionAndSubjectAndGroup(
            $tblDivision,
            $tblSubject,
            $tblSubjectGroup
        );

        $average = Gradebook::useService()->calcStudentGrade(
            $tblPerson, $tblDivision, $tblSubject, Evaluation::useService()->getTestTypeByIdentifier('TEST'),
            $tblScoreRule ? $tblScoreRule : null,
            ($tblTaskPeriod = $tblTask->getServiceTblPeriodByDivision($tblDivision)) ? $tblTaskPeriod : null, null,
            $tblTask->getDate() ? $tblTask->getDate() : false
        );
        if (is_array($average)) {
            $average = ' ';
        } else {
            $posStart = strpos($average, '(');
            if ($posStart !== false) {
                $average = substr($average, 0, $posStart);
            }
        }

        if ($tblGrade) {
            // Zeugnistext
            if (($tblGradeText = $tblGrade->getTblGradeText())) {
                $studentList[$tblPerson->getId()][$tblSubject->getAcronym()] = $tblGradeText->getName();

                return $studentList;
            }

            $gradeValue = $tblGrade->getGrade();

            if ($gradeValue !== null && $gradeValue !== '') {
                $averageGradeList[$tblSubject->getId()][$tblPerson->getId()] = $gradeValue;
            }

            $isGradeInRange = true;
            if ($average !== ' ' && $average && $gradeValue !== null) {
                if (is_numeric($gradeValue)) {
                    $gradeFloat = floatval($gradeValue);
                    if (($gradeFloat - 0.5) <= $average && ($gradeFloat + 0.5) >= $average) {
                        $isGradeInRange = true;
                    } else {
                        $isGradeInRange = false;
                    }
                }
            }

            $withTrend = true;
            if ($tblPrepare
                && ($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare,
                    $tblGrade->getServiceTblPerson()))
                && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                && !$tblCertificate->isInformation()
            ) {
                $withTrend = false;
            }
            $gradeValue = $tblGrade->getDisplayGrade($withTrend);

            if ($isGradeInRange) {
                $gradeValue = new Success($gradeValue);
            } else {
                $gradeValue = new \SPHERE\Common\Frontend\Text\Repository\Danger($gradeValue);
            }

            $studentList[$tblPerson->getId()][$tblSubject->getAcronym()] = ($tblGrade->getGrade() !== null
                    ? $gradeValue : '') .
                (($average !== ' ' && $average) ? new Muted('&nbsp;&nbsp; &#216;' . $average) : '');
            return $studentList;
        } else {
            $studentList[$tblPerson->getId()][$tblSubject->getAcronym()] =
                new \SPHERE\Common\Frontend\Text\Repository\Warning('fehlt')
                . (($average !== ' ' && $average) ? new Muted('&nbsp;&nbsp; &#216;' . $average) : '');
            return $studentList;
        }
    }

    /**
     * @param null $PrepareId
     * @param null $GroupId
     * @param null $PersonId
     * @param null $Route
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendDroppedSubjects($PrepareId = null, $GroupId = null, $PersonId = null, $Route = null, $Data = null)
    {

        if ($GroupId) {
            $tblGroup = Group::useService()->getGroupById($GroupId);
        } else {
            $tblGroup = false;
        }

        $Stage = new Stage('Abgewählte Fächer', 'Verwalten');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/Prepare/Preview', new ChevronLeft(), array(
                'PrepareId' => $PrepareId,
                'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                'Route' => $Route,
            )
        ));

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblPerson = Person::useService()->getPersonById($PersonId))
        ) {

            $contentList = array();
            if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('PRIOR_YEAR_GRADE'))
                && ($tblPrepareAdditionalGradeList = Prepare::useService()->getPrepareAdditionalGradeListBy($tblPrepare,
                    $tblPerson, $tblPrepareAdditionalGradeType))
            ) {
                $count = 1;
                foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                    if (($tblSubject = $tblPrepareAdditionalGrade->getServiceTblSubject())) {
                        $contentList[] = array(
                            'Ranking' => $count++,
                            'Acronym' => new PullClear(
                                new PullLeft(new ResizeVertical() . ' ' . $tblSubject->getAcronym())
                            ),
                            'Name' => $tblSubject->getName(),
                            'Grade' => $tblPrepareAdditionalGrade->getGrade(),
                            'Option' => (new Standard('', '/Education/Certificate/Prepare/DroppedSubjects/Destroy',
                                new Remove(),
                                array(
                                    'Id' => $tblPrepareAdditionalGrade->getId(),
                                    'Route' => $Route,
                                    'GroupId' => $tblGroup ? $tblGroup->getId() : null
                                ), 'Löschen'))
                        );
                    }
                }
            }

            $form = $this->formCreatePrepareAdditionalGrade($tblPrepare, $tblPerson);
            $form->appendFormButton(
                new Primary('Speichern', new Save()));

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Zeugnisvorbereitung',
                                    array(
                                        $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                        $tblGroup
                                            ? 'Gruppe ' . $tblGroup->getName()
                                            : 'Klasse ' . (($tblDivision = $tblPrepare->getServiceTblDivision())
                                                ? $tblDivision->getDisplayName() : '')
                                    ),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new Panel(
                                    'Schüler',
                                    array(
                                        $tblPerson->getLastFirstName()
                                    ),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new TableData(
                                    $contentList,
                                    null,
                                    array(
                                        'Ranking' => '#',
                                        'Acronym' => 'Kürzel',
                                        'Name' => 'Name',
                                        'Grade' => 'Zensur',
                                        'Option' => ''
                                    ),
                                    array(
                                        'rowReorderColumn' => 1,
                                        'ExtensionRowReorder' => array(
                                            'Enabled' => true,
                                            'Url' => '/Api/Education/Prepare/Reorder',
                                            'Data' => array(
                                                'PrepareId' => $tblPrepare->getId(),
                                                'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                                                'PersonId' => $tblPerson->getId()
                                            )
                                        ),
                                        'paging' => false,
                                    )
                                )
                            ))
                        ))
                    ), new Title(new ListingTable() . ' Übersicht')),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Well(Prepare::useService()->createPrepareAdditionalGradeForm(
                                    $form,
                                    $Data,
                                    $tblPrepare,
                                    $tblGroup ? $tblGroup : null,
                                    $tblPerson,
                                    $Route
                                ))
                            )
                        ))
                    ), new Title(new PlusSign() . ' Hinzufügen'))
                ))
            );

            return $Stage;

        } else {

            return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
        }
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     *
     * @return Form
     */
    private function formCreatePrepareAdditionalGrade(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson
    ) {

        $availableSubjectList = array();
        $tblSubjectAll = Subject::useService()->getSubjectAll();
        if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('PRIOR_YEAR_GRADE'))
            && $tblSubjectAll
            && ($tempList = Prepare::useService()->getPrepareAdditionalGradeListBy(
                $tblPrepareCertificate,
                $tblPerson,
                $tblPrepareAdditionalGradeType
            ))
        ) {

            $usedSubjectList = array();
            foreach ($tempList as $item) {
                if ($item->getServiceTblSubject()) {
                    $usedSubjectList[$item->getServiceTblSubject()->getId()] = $item;
                }
            }

            foreach ($tblSubjectAll as $tblSubject) {
                if (!isset($usedSubjectList[$tblSubject->getId()])) {
                    $availableSubjectList[] = $tblSubject;
                }
            }
        } else {
            $availableSubjectList = $tblSubjectAll;
        }

        return new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new SelectBox('Data[Subject]', 'Fach', array('DisplayName' => $availableSubjectList)), 6
                    ),
                    new FormColumn(
                        new TextField('Data[Grade]', '', 'Zensur'), 6
                    )
                ))
            ))
        ));
    }

    /**
     * @param null $Id
     * @param null $GroupId
     * @param bool|false $Confirm
     * @param null $Route
     *
     * @return Stage|string
     */
    public function frontendDestroyDroppedSubjects(
        $Id = null,
        $GroupId = null,
        $Confirm = false,
        $Route = null
    ) {

        $Stage = new Stage('Abgewähltes Fach', 'Löschen');

        $tblPrepareAdditionalGrade = Prepare::useService()->getPrepareAdditionalGradeById($Id);
        $tblPrepare = $tblPrepareAdditionalGrade->getTblPrepareCertificate();
        $tblPerson = $tblPrepareAdditionalGrade->getServiceTblPerson();

        $parameters = array(
            'PrepareId' => $tblPrepare ? $tblPrepare->getId() : 0,
            'GroupId' => $GroupId,
            'PersonId' => $tblPerson ? $tblPerson->getId() : 0,
            'Route' => $Route
        );

        if ($GroupId) {
            $tblGroup = Group::useService()->getGroupById($GroupId);
        } else {
            $tblGroup = false;
        }

        if ($tblPrepareAdditionalGrade) {
            $Stage->addButton(
                new Standard('Zur&uuml;ck', '/Education/Certificate/Prepare/DroppedSubjects', new ChevronLeft(),
                    $parameters)
            );

            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel(
                                'Zeugnisvorbereitung',
                                array(
                                    $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                    $tblGroup
                                        ? 'Gruppe ' . $tblGroup->getName()
                                        : 'Klasse ' . (($tblDivision = $tblPrepare->getServiceTblDivision())
                                            ? $tblDivision->getDisplayName() : '')
                                ),
                                Panel::PANEL_TYPE_INFO
                            ),
                        ), 6),
                        new LayoutColumn(array(
                            new Panel(
                                'Schüler',
                                array(
                                    $tblPerson->getLastFirstName()
                                ),
                                Panel::PANEL_TYPE_INFO
                            ),
                        ), 6),
                        new LayoutColumn(array(
                                new Panel(
                                    'Abgewähltes Fach',
                                    ($tblSubject = $tblPrepareAdditionalGrade->getServiceTblSubject())
                                        ? $tblSubject->getName() : '',
                                    Panel::PANEL_TYPE_INFO
                                ),
                                new Panel(new Question() . ' Dieses abgewählte Fach wirklich löschen?',
                                    array(
                                        $tblSubject ? 'Fach-Kürzel: ' . $tblSubject->getAcronym() : null,
                                        $tblSubject ? 'Fach-Name: ' . $tblSubject->getName() : null,
                                        'Zensur: ' . $tblPrepareAdditionalGrade->getGrade()
                                    ),
                                    Panel::PANEL_TYPE_DANGER,
                                    new Standard(
                                        'Ja', '/Education/Certificate/Prepare/DroppedSubjects/Destroy', new Ok(),
                                        array('Id' => $Id, 'GroupId'=> $GroupId, 'Confirm' => true, 'Route' => $Route)
                                    )
                                    . new Standard(
                                        'Nein', '/Education/Certificate/Prepare/DroppedSubjects', new Disable(),
                                        $parameters
                                    )
                                )
                            )
                        )
                    ))))
                );
            } else {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            (Prepare::useService()->destroyPrepareAdditionalGrade($tblPrepareAdditionalGrade)
                                ? new \SPHERE\Common\Frontend\Message\Repository\Success(new \SPHERE\Common\Frontend\Icon\Repository\Success()
                                    . ' Das abgewählte Fach wurde gelöscht')
                                : new Danger(new Ban() . ' Das abgewählte Fach konnte nicht gelöscht werden')
                            ),
                            new Redirect('/Education/Certificate/Prepare/DroppedSubjects', Redirect::TIMEOUT_SUCCESS,
                                $parameters)
                        )))
                    )))
                );
            }
        } else {
            return $Stage . new Danger('Abgewähltes Fach nicht gefunden.', new Ban())
                . new Redirect('/Education/Certificate/Prepare/DroppedSubjects', Redirect::TIMEOUT_ERROR, $parameters);
        }

        return $Stage;
    }

    /**
     * @param null $PrepareId
     * @param null $GroupId
     * @param null $SubjectId
     * @param null $Route
     * @param null $IsNotSubject
     * @param null $IsFinalGrade
     * @param null $Data
     * @param null $CertificateList
     *
     * Schulart Mittelschule / Oberschule, Fachoberschule
     *
     * @return Stage|string
     */
    public function frontendPrepareDiplomaSetting(
        $PrepareId = null,
        $GroupId = null,
        $SubjectId = null,
        $Route = null,
        $IsNotSubject = null,
        $IsFinalGrade = null,
        $Data = null,
        $CertificateList = null
    ) {
        if ($tblPrepare = Prepare::useService()->getPrepareById($PrepareId)) {
            $tblGroup = false;
            $tblPrepareList = false;
            $description = '';
            $tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate();
            if ($GroupId && ($tblGroup = Group::useService()->getGroupById($GroupId))) {
                $description = 'Gruppe ' . $tblGroup->getName();
                if (($tblGenerateCertificate)) {
                    $tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate);
                }
            } else {
                if (($tblDivision = $tblPrepare->getServiceTblDivision())) {

                    $description = 'Klasse ' . $tblDivision->getDisplayName();
                    $tblPrepareList = array(0 => $tblPrepare);
                }
            }

            // Fachnoten mit Prüfungsnoten festlegen
            if (!$IsNotSubject
                && $tblPrepare->getServiceTblAppointedDateTask()
                && ($tblDivision = $tblPrepare->getServiceTblDivision())
                && ($tblTestList = Evaluation::useService()->getTestAllByTask($tblPrepare->getServiceTblAppointedDateTask(),
                    $tblDivision))
            ) {

                return $this->setExamsSetting($tblPrepare, $tblDivision, $tblGroup ? $tblGroup : null, $tblTestList, $SubjectId, $Route,
                    $IsFinalGrade, $Data, $IsNotSubject, $tblPrepareList, $description);

                // Sonstige Informationen
            } elseif (($tblDivision = $tblPrepare->getServiceTblDivision())
                && (($IsNotSubject
                        || (!$IsNotSubject && !$tblPrepare->getServiceTblBehaviorTask()))
                    || (!$IsNotSubject && $tblPrepare->getServiceTblBehaviorTask()
                        && !Evaluation::useService()->getTestAllByTask($tblPrepare->getServiceTblBehaviorTask(),
                            $tblDivision)))
            ) {
                $Stage = new Stage('Zeugnisvorbereitung', 'Sonstige Informationen festlegen');

                if ($tblGroup) {
                    $Stage->addButton(new Standard(
                        'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(),
                        array(
                            'GroupId' => $tblGroup->getId(),
                            'Route' => $Route
                        )
                    ));
                } else {
                    $Stage->addButton(new Standard(
                        'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(),
                        array(
                            'DivisionId' => $tblDivision->getId(),
                            'Route' => $Route
                        )
                    ));
                }

                $tblCurrentSubject = false;
                $tblNextSubject = false;
                $tblSubjectList = array();

                if ($tblPrepare->getServiceTblAppointedDateTask()
                    && ($tblDivision = $tblPrepare->getServiceTblDivision())
                ) {
                    $tblTestList = Evaluation::useService()->getTestAllByTask($tblPrepare->getServiceTblAppointedDateTask(),
                        $tblDivision);
                } else {
                    $tblTestList = array();
                }
                $buttonList = $this->createExamsButtonList(
                    $tblPrepare, $tblCurrentSubject, $tblNextSubject, $tblTestList, $SubjectId, $Route, $tblSubjectList,
                    $IsNotSubject, $tblGroup ? $tblGroup : null
                );

                $studentTable = array();
                $columnTable = array(
                    'Number' => '#',
                    'Name' => 'Name',
                );
                if (($tblSchoolType = $tblDivision->getType()) && $tblSchoolType->getShortName() == 'OS') {
                    $columnTable['Course'] = 'Bildungsgang';
                }

                foreach ($tblPrepareList as $tblPrepareItem) {
                    if (($tblDivisionItem = $tblPrepareItem->getServiceTblDivision())
                        && (($tblStudentList = Division::useService()->getStudentAllByDivision($tblDivisionItem)))
                    ) {

                        $isCourseMainDiploma = Prepare::useService()->isCourseMainDiploma($tblPrepareItem);
                        foreach ($tblStudentList as $tblPerson) {
                            if (!$tblGroup || Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                                $isMuted = $isCourseMainDiploma;
                                // Bildungsgang
                                $tblCourse = false;
                                if (($tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS'))
                                    && ($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                                ) {
                                    $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                                        $tblTransferType);
                                    if ($tblStudentTransfer) {
                                        $tblCourse = $tblStudentTransfer->getServiceTblCourse();
                                        if ($tblCourse && $tblCourse->getName() == 'Hauptschule') {
                                            $isMuted = false;
                                        }
                                    }
                                }

                                $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepareItem, $tblPerson);

                                $studentTable[$tblPerson->getId()] = array(
                                    'Number' => $isMuted ? new Muted(count($studentTable) + 1) : (count($studentTable) + 1)
                                        . ' '
                                        . ($tblPrepareStudent && $tblPrepareStudent->isApproved()
                                            ? new ToolTip(new \SPHERE\Common\Frontend\Text\Repository\Warning(new Ban()),
                                                'Das Zeugnis des Schülers wurde bereits freigegeben und kann nicht mehr bearbeitet werden.')
                                            : new ToolTip(new Success(new Edit()), 'Das Zeugnis des Schülers kann bearbeitet werden.')),
                                    'Name' => ($isMuted ? new Muted($tblPerson->getLastFirstName()) : $tblPerson->getLastFirstName())
                                        . ($tblGroup
                                            ? new Small(new Muted(' (' . $tblDivisionItem->getDisplayName() . ')')) : '')
                                );
                                $courseName = $tblCourse ? $tblCourse->getName() : '';
                                $studentTable[$tblPerson->getId()]['Course'] = $isMuted ? new Muted($courseName) : $courseName;

                                /*
                                 * Sonstige Informationen der Zeugnisvorlage
                                 */
                                if (!$isMuted) {
                                    $this->getTemplateInformation($tblPrepareItem, $tblPerson, $studentTable,
                                        $columnTable,
                                        $Data,
                                        $CertificateList);
                                }

                                // leere Elemente auffühlen (sonst steht die Spaltennummer drin)
                                foreach ($columnTable as $columnKey => $columnName) {
                                    foreach ($studentTable as $personId => $value) {
                                        if (!isset($studentTable[$personId][$columnKey])) {
                                            $studentTable[$personId][$columnKey] = '';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                $tableData = new TableData($studentTable, null, $columnTable,
                    array(
                        "columnDefs" => array(
                            array(
                                "width" => "18px",
                                "targets" => 0
                            ),
                            array(
                                "width" => "200px",
                                "targets" => 1
                            ),
                            array(
                                "width" => "80px",
                                "targets" => 2
                            ),
                            array(
                                "width" => "50px",
                                "targets" => array(3, 4)
                            ),
                        ),
                        'order' => array(
                            array('0', 'asc'),
                        ),
                        "paging" => false, // Deaktivieren Blättern
                        "iDisplayLength" => -1,    // Alle Einträge zeigen
                        "searching" => false, // Deaktivieren Suchen
                        "info" => false,  // Deaktivieren Such-Info
                        "sort" => false,
                        "responsive" => false
                    ),
                    true
                );

                $form = new Form(
                    new FormGroup(array(
                        new FormRow(array(
                            new FormColumn(
                                $tableData
                            ),
                            new FormColumn(new HiddenField('Data[IsSubmit]'))
                        )),
                    ))
                    , new Primary('Speichern', new Save())
                );

                $Stage->setContent(
                    ApiPrepare::receiverModal()
                    .new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Panel(
                                        'Zeugnis',
                                        array(
                                            $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate()))
                                        ),
                                        Panel::PANEL_TYPE_INFO
                                    ),
                                ), 6),
                                new LayoutColumn(array(
                                    new Panel(
                                        $tblGroup ? 'Gruppe' : 'Klasse',
                                        $description,
                                        Panel::PANEL_TYPE_INFO
                                    ),
                                ), 6),
                                new LayoutColumn($buttonList),
                            )),
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    !$tblTestList
                                        ? new Warning('Die aktuelle Klasse ist nicht in dem ausgewählten Stichttagsnotenauftrag enthalten.'
                                        , new Exclamation())
                                        : null,
                                    Prepare::useService()->updatePrepareInformationList($form, $tblPrepare,
                                        $tblGroup ? $tblGroup : null, $Route, $Data, $CertificateList)
                                ))
                            ))
                        ))
                    ))
                );

                return $Stage;
            }
        }

        $Stage = new Stage('Zeugnisvorbereitung', 'Einstellungen');

        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare', new ChevronLeft()
        ));

        return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblDivision $tblDivision
     * @param TblGroup|null $tblGroup
     * @param $tblTestList
     * @param $SubjectId
     * @param $Route
     * @param $IsFinalGrade
     * @param $Data
     * @param $IsNotSubject
     * @param false|TblPrepareCertificate[] $tblPrepareList
     * @param $description
     *
     * @return Stage
     */
    private function setExamsSetting(
        TblPrepareCertificate $tblPrepare,
        TblDivision $tblDivision,
        TblGroup $tblGroup = null,
        $tblTestList,
        $SubjectId,
        $Route,
        $IsFinalGrade,
        $Data,
        $IsNotSubject,
        $tblPrepareList,
        $description
    ) {

        $Stage = new Stage('Zeugnisvorbereitung', 'Fachnoten festlegen');

        if ($tblGroup) {
            $Stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(),
                array(
                    'GroupId' => $tblGroup->getId(),
                    'Route' => $Route
                )
            ));
        } else {
            $Stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(),
                array(
                    'DivisionId' => $tblDivision->getId(),
                    'Route' => $Route
                )
            ));
        }

        $tblCurrentSubject = false;
        $tblNextSubject = false;
        $tblSubjectList = array();

        $buttonList = $this->createExamsButtonList(
            $tblPrepare, $tblCurrentSubject, $tblNextSubject, $tblTestList, $SubjectId, $Route, $tblSubjectList,
            $IsNotSubject, $tblGroup ? $tblGroup : null
        );

        $studentTable = array();
        if (Prepare::useService()->isCourseMainDiploma($tblPrepare)) {
            // Klasse 9 Hauptschule
            $columnTable = array(
                'Number' => '#',
                'Name' => 'Name',
                'Course' => 'Bildungsgang',
                'JN' => ($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('JN'))
                    ? $tblPrepareAdditionalGradeType->getName() : 'Jn',
                'LS' => ($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('LS'))
                    ? $tblPrepareAdditionalGradeType->getName() : 'Ls',
                'LM' => ($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('LM'))
                    ? $tblPrepareAdditionalGradeType->getName() : 'Lm',
            );
            if (($tblSchoolType = $tblDivision->getType()) && $tblSchoolType->getShortName() == 'OS') {
                $columnTable['Course'] = 'Bildungsgang';
            }
            if ($IsFinalGrade) {
                $columnTable['Average'] = '&#216;';
                $columnTable['EN'] = 'En (Endnote)';
                $columnTable['Text'] = 'oder Zeugnistext';
                $tableTitle = 'Endnote';
                if ($tblNextSubject) {
                    $textSaveButton = 'Speichern und weiter zum nächsten Fach';
                } else {
                    $textSaveButton = 'Speichern und weiter zu den sonstigen Informationen';
                }
            } else {
                $tableTitle = 'Leistungsnachweisnoten';
                $textSaveButton = 'Speichern und weiter zur Endnote';
            }
        } else {
            // Klasse 10 Realschule
            $columnTable = array(
                'Number' => '#',
                'Name' => 'Name',
            );
            if (($tblSchoolType = $tblDivision->getType()) && $tblSchoolType->getShortName() == 'OS') {
                $columnTable['Course'] = 'Bildungsgang';
            }
            $columnTable['JN'] = ($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('JN'))
                    ? $tblPrepareAdditionalGradeType->getName() : 'Jn';
            $columnTable['PS'] = ($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('PS'))
                    ? $tblPrepareAdditionalGradeType->getName() : 'Ps';
            $columnTable['PM'] = ($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('PM'))
                    ? $tblPrepareAdditionalGradeType->getName() : 'Pm';
            $columnTable['PZ'] = ($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('PZ'))
                    ? $tblPrepareAdditionalGradeType->getName() : 'Pz';
            if ($IsFinalGrade) {
                $columnTable['Average'] = '&#216;';
                $columnTable['EN'] = 'En (Endnote)';
                $columnTable['Text'] = 'oder Zeugnistext';
                $tableTitle = 'Endnote';
                if ($tblNextSubject) {
                    $textSaveButton = 'Speichern und weiter zum nächsten Fach';
                } else {
                    $textSaveButton = 'Speichern und weiter zu den sonstigen Informationen';
                }
            } else {
                $tableTitle = 'Prüfungsnoten';
                $textSaveButton = 'Speichern und weiter zur Endnote';
            }
        }

        list($studentTable, $hasPreviewGrades, $missingTemplateList) = $this->createExamsContent($tblTestList,
            $IsFinalGrade, $studentTable, $tblCurrentSubject, $tblSubjectList, $tblPrepareList, $tblGroup);

        $columnDef = array(
            array(
                "width" => "18px",
                "targets" => 0
            ),
            array(
                "width" => "200px",
                "targets" => 1
            ),
            array(
                "width" => "80px",
                "targets" => 2
            ),
        );

        /** @var TblSubject $tblCurrentSubject */
        $tableTitle = $tblCurrentSubject ? $tblCurrentSubject->getAcronym() . ' - ' . $tableTitle : $tableTitle;

        $tableData = new TableData($studentTable, new \SPHERE\Common\Frontend\Table\Repository\Title($tableTitle), $columnTable,
            array(
                "columnDefs" => $columnDef,
                'order' => array(
                    array('0', 'asc'),
                ),
                "paging" => false, // Deaktivieren Blättern
                "iDisplayLength" => -1,    // Alle Einträge zeigen
                "searching" => false, // Deaktivieren Suchen
                "info" => false,  // Deaktivieren Such-Info
                "sort" => false,
                "responsive" => false
            )
        );

        $form = new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        $tableData
                    ),
                    new FormColumn(new HiddenField('Data[IsSubmit]'))
                )),
            ))
            , new Primary($textSaveButton, new Save())
        );

        /** @var TblSubject $tblCurrentSubject */
        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel(
                                'Zeugnis',
                                array(
                                    $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate()))
                                ),
                                Panel::PANEL_TYPE_INFO
                            ),
                        ), 6),
                        new LayoutColumn(array(
                            new Panel(
                                $tblGroup ? 'Gruppe' : 'Klasse',
                                $description,
                                Panel::PANEL_TYPE_INFO
                            ),
                        ), 6),
                        new LayoutColumn($buttonList),
                        $hasPreviewGrades
                            ? new LayoutColumn(new Warning(
                            'Es wurden noch nicht alle Notenvorschläge gespeichert.', new Exclamation()
                        ))
                            : null,
                        !empty($missingTemplateList)
                            ? new LayoutColumn(new Warning(
                            'Es wurde für die folgenden Hauptschüler keine Zeugnisvorlage ausgewählt: <br>'
                            . implode('<br>', $missingTemplateList)
                            . '<br>'
                            . 'Es können erst Zensuren eingetragen werden, wenn eine Zeugnisvorlage unter: "Zeugnisse generieren" ausgewählt wurde!'
                            , new Exclamation()
                        ))
                            : null,
                    )),
                )),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            Prepare::useService()->updatePrepareExamGrades(
                                $form,
                                $tblPrepare,
                                $tblCurrentSubject,
                                $tblNextSubject ? $tblNextSubject : null,
                                $IsFinalGrade ? $IsFinalGrade : null,
                                $Route,
                                $Data,
                                $tblGroup ? $tblGroup : null
                            )
                        ))
                    ))
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param $tblCurrentSubject
     * @param $tblNextSubject
     * @param $tblTestList
     * @param $SubjectId
     * @param $Route
     * @param $tblSubjectList
     * @param $IsNotSubject
     * @param TblGroup|null $tblGroup
     *
     * @return array
     */
    private function createExamsButtonList(
        TblPrepareCertificate $tblPrepare,
        &$tblCurrentSubject,
        &$tblNextSubject,
        $tblTestList,
        $SubjectId,
        $Route,
        &$tblSubjectList,
        $IsNotSubject,
        TblGroup $tblGroup = null
    ) {

        if ($tblTestList) {
            // Sortierung der Fächer wie auf dem Zeugnis
            $tblTestList = $this->sortSubjects($tblPrepare, $tblTestList);

            /** @var TblTest $tblTest */
            foreach ($tblTestList as $tblTest) {
                if (($tblSubjectItem = $tblTest->getServiceTblSubject())) {
                    if (!isset($tblSubjectList[$tblSubjectItem->getId()][$tblTest->getId()])) {
                        $tblSubjectList[$tblSubjectItem->getId()][$tblTest->getId()] = $tblSubjectItem;
                        if ($tblCurrentSubject && !$tblNextSubject && !$IsNotSubject) {
                            // Bei Gruppen
                            /** @var TblSubject $tblCurrentSubject */
                            if ($tblCurrentSubject->getId() != $tblSubjectItem->getId()) {
                                $tblNextSubject = $tblSubjectItem;
                            }
                        }
                        if ($SubjectId && $SubjectId == $tblSubjectItem->getId() && !$IsNotSubject) {
                            $tblCurrentSubject = $tblSubjectItem;
                        }
                    }
                }
            }
        }

        if (!$IsNotSubject && !$tblCurrentSubject && !empty($tblSubjectList)) {
            reset($tblSubjectList);
            $tblCurrentSubject = Subject::useService()->getSubjectById(key($tblSubjectList));
            if (count($tblSubjectList) > 1) {
                next($tblSubjectList);
                $tblNextSubject = Subject::useService()->getSubjectById(key($tblSubjectList));
            }
        }

        $buttonList = array();

        if (Prepare::useService()->isCourseMainDiploma($tblPrepare)) {
            $textLinkButton = ' - Leistungsnachweisnoten/Endnote';
        } else {
            $textLinkButton = ' - Prüfungsnoten/Endnote';
        }

        foreach ($tblSubjectList as $subjectId => $value) {
            if (($tblSubject = Subject::useService()->getSubjectById($subjectId))) {
                if ($tblCurrentSubject && $tblCurrentSubject->getId() == $tblSubject->getId()) {
                    $name = new Info(new Bold($tblSubject->getAcronym()
                        . $textLinkButton
                    ));
                    $icon = new Edit();
                } else {
                    $name = $tblSubject->getAcronym();
                    $icon = null;
                }

                $buttonList[] = new Standard($name,
                    '/Education/Certificate/Prepare/Prepare/Diploma/Setting', $icon, array(
                        'PrepareId' => $tblPrepare->getId(),
                        'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                        'Route' => $Route,
                        'SubjectId' => $tblSubject->getId()
                    )
                );
            }
        }

        if ($IsNotSubject) {
            $name = new Info(new Bold('Sonstige Informationen'));
            $icon = new Edit();
        } else {
            $name = 'Sonstige Informationen';
            $icon = null;
        }
        $buttonList[] = new Standard($name,
            '/Education/Certificate/Prepare/Prepare/Diploma/Setting', $icon, array(
                'PrepareId' => $tblPrepare->getId(),
                'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                'Route' => $Route,
                'IsNotSubject' => true
            )
        );

        return $buttonList;
    }

    /**
     * @param $tblTestList
     * @param $IsFinalGrade
     * @param $studentTable
     * @param $tblCurrentSubject
     * @param $tblSubjectList
     * @param false|TblPrepareCertificate[] $tblPrepareList
     * @param TblGroup|null $tblGroup
     *
     * @return array
     */
    private function createExamsContent(
        $tblTestList,
        $IsFinalGrade,
        $studentTable,
        $tblCurrentSubject,
        $tblSubjectList,
        $tblPrepareList,
        TblGroup $tblGroup = null
    ) {

        $hasPreviewGrades = false;
        $missingTemplateList = array();
        $tabIndex = 1;
        foreach ($tblPrepareList as $tblPrepareItem) {
            if (($tblDivisionItem = $tblPrepareItem->getServiceTblDivision())
                && ($tblSchoolType = $tblDivisionItem->getType())
                && (($tblStudentList = Division::useService()->getStudentAllByDivision($tblDivisionItem)))
            ) {
                $isCourseMainDiploma = Prepare::useService()->isCourseMainDiploma($tblPrepareItem);
                foreach ($tblStudentList as $tblPerson) {
                    if (!$tblGroup || Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                        $hasSubject = false;
                        $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepareItem, $tblPerson);

                        // Bildungsgang
                        $tblCourse = false;
                        $isMuted = $isCourseMainDiploma;
                        if (($tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS'))
                            && ($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                        ) {
                            $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                                $tblTransferType);
                            if ($tblStudentTransfer) {
                                $tblCourse = $tblStudentTransfer->getServiceTblCourse();
                                if ($tblCourse && $tblCourse->getName() == 'Hauptschule') {
                                    $isMuted = false;

                                    // SSW-640 Hinweistext keine Zeugnisvorlage ausgewählt
                                    if (!$tblPrepareStudent
                                        || ($tblPrepareStudent && !$tblPrepareStudent->getServiceTblCertificate())
                                    ) {
                                        $missingTemplateList[$tblPerson->getId()] = $tblPerson->getLastFirstName();
                                    }
                                }
                            }
                        }

                        $studentTable[$tblPerson->getId()] = array(
                            'Number' => $isMuted ? new Muted(count($studentTable) + 1) : (count($studentTable) + 1)
                                . ' '
                                . ($tblPrepareStudent && $tblPrepareStudent->isApproved()
                                    ? new ToolTip(new \SPHERE\Common\Frontend\Text\Repository\Warning(new Ban()),
                                        'Das Zeugnis des Schülers wurde bereits freigegeben und kann nicht mehr bearbeitet werden.')
                                    : new ToolTip(new Success(new Edit()), 'Das Zeugnis des Schülers kann bearbeitet werden.')),
                            'Name' => ($isMuted ? new Muted($tblPerson->getLastFirstName()) : $tblPerson->getLastFirstName())
                                . ($tblGroup
                                    ? new Small(new Muted(' (' . $tblDivisionItem->getDisplayName() . ')')) : '')
                        );
                        $courseName = $tblCourse ? $tblCourse->getName() : '';
                        $studentTable[$tblPerson->getId()]['Course'] = $isMuted ? new Muted($courseName) : $courseName;

                        if ($tblCurrentSubject) {
                            /** @var TblSubject $tblCurrentSubject */
                            $subjectGradeList = array();
                            /** @var TblTest $tblTest */
                            foreach ($tblTestList as $tblTest) {
                                if (($tblSubject = $tblTest->getServiceTblSubject())
                                    && $tblSubject->getId() == $tblCurrentSubject->getId()
                                ) {
                                    if (($tblSubject = $tblTest->getServiceTblSubject())
                                        && ($tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
                                            $tblPerson))
                                    ) {
                                        $subjectGradeList[$tblSubject->getAcronym()] = $tblGrade;
                                    }

                                    // besucht der Schüler das Fach
                                    if (($tblSubjectGroup = $tblTest->getServiceTblSubjectGroup())) {
                                        if (($tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
                                                $tblDivisionItem, $tblSubject, $tblSubjectGroup
                                            ))
                                            && (($tblSubjectStudent = Division::useService()->exitsSubjectStudent(
                                                $tblDivisionSubject, $tblPerson
                                            )))
                                        ) {
                                            $hasSubject = true;
                                        }
                                    } else {
                                        $hasSubject = true;
                                    }
                                }
                            }

                            // Post setzen
                            if (($tblTask = $tblPrepareItem->getServiceTblAppointedDateTask())
                                && ($tblTestType = $tblTask->getTblTestType())
                                && $tblCurrentSubject
                                && $tblPrepareStudent
                            ) {
                                if (isset($tblSubjectList[$tblCurrentSubject->getId()])) {
                                    $Global = $this->getGlobal();
                                    $gradeList = array();

                                    foreach ($tblSubjectList[$tblCurrentSubject->getId()] as $testId => $value) {
                                        if ($isCourseMainDiploma) {
                                            if (!$isMuted && (($tblTestTemp = Evaluation::useService()->getTestById($testId)))) {
                                                $tblGrade = Gradebook::useService()->getGradeByTestAndStudent(
                                                    $tblTestTemp, $tblPerson
                                                );
                                                if ($tblGrade) {
                                                    $gradeValue = $tblGrade->getDisplayGrade();
                                                    $Global->POST['Data'][$tblPrepareStudent->getId()]['JN'] = $gradeValue;
                                                    if ($gradeValue && is_numeric($gradeValue)) {
                                                        $gradeList['JN'] = $gradeValue;
                                                    } else {
                                                        $gradeList['JN_TEXT'] = $gradeValue;
                                                    }
                                                }
                                            }
                                        } else {
                                            if (($tblTestTemp = Evaluation::useService()->getTestById($testId))) {
                                                $tblGrade = Gradebook::useService()->getGradeByTestAndStudent(
                                                    $tblTestTemp, $tblPerson
                                                );
                                                if ($tblGrade) {
                                                    $gradeValue = $tblGrade->getDisplayGrade();
                                                    $Global->POST['Data'][$tblPrepareStudent->getId()]['JN'] = $gradeValue;
                                                    if ($gradeValue && is_numeric($gradeValue)) {
                                                        $gradeList['JN'] = $gradeValue;
                                                    } else {
                                                        $gradeList['JN_TEXT'] = $gradeValue;
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    if (($tblPrepareAdditionalGradeList = Prepare::useService()->getPrepareAdditionalGradeListBy(
                                        $tblPrepareItem,
                                        $tblPerson
                                    ))
                                    ) {
                                        foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                                            if ($tblPrepareAdditionalGrade->getServiceTblSubject()
                                                && $tblCurrentSubject->getId() == $tblPrepareAdditionalGrade->getServiceTblSubject()->getId()
                                                && ($tblPrepareAdditionalGradeType = $tblPrepareAdditionalGrade->getTblPrepareAdditionalGradeType())
                                                && $tblPrepareAdditionalGradeType->getIdentifier() != 'PRIOR_YEAR_GRADE'
                                            ) {
                                                // Zeugnistext
                                                if ($tblPrepareAdditionalGradeType->getIdentifier() == 'EN'
                                                    && ($tblGradeText = Gradebook::useService()->getGradeTextByName($tblPrepareAdditionalGrade->getGrade()))
                                                ) {
                                                    $Global->POST['Data'][$tblPrepareStudent->getId()]['Text']
                                                        = $tblGradeText->getId();
                                                } else {
                                                    $Global->POST['Data'][$tblPrepareStudent->getId()][$tblPrepareAdditionalGradeType->getIdentifier()]
                                                        = $tblPrepareAdditionalGrade->getGrade();
                                                    if ($tblPrepareAdditionalGrade->getGrade()) {
                                                        $gradeList[$tblPrepareAdditionalGradeType->getIdentifier()] = $tblPrepareAdditionalGrade->getGrade();
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    // calc average --> finalGrade
                                    if ($IsFinalGrade && $tblPrepareStudent) {
                                        if ($isCourseMainDiploma) {
                                            if (!$isMuted) {
                                                $calcValue = '';
                                                if (isset($gradeList['JN'])) {
                                                    $calc = false;
                                                    if (isset($gradeList['LS']) && isset($gradeList['LM'])) {
                                                        $calc = ($gradeList['JN'] + $gradeList['LS'] + $gradeList['LM']) / 3;
                                                    } elseif (isset($gradeList['LS'])) {
                                                        $calc = (2 * $gradeList['JN'] + $gradeList['LS']) / 3;
                                                    } elseif (isset($gradeList['LM'])) {
                                                        $calc = (2 * $gradeList['JN'] + $gradeList['LM']) / 3;
                                                    }

                                                    if ($calc) {
                                                        $calcValue = round($calc, 2);
                                                    } else {
                                                        $calcValue = $gradeList['JN'];
                                                    }
                                                }

                                                $studentTable[$tblPerson->getId()]['Average'] = str_replace('.', ',',
                                                    $calcValue);

                                                if (!Prepare::useService()->getPrepareAdditionalGradeBy(
                                                        $tblPrepareItem,
                                                        $tblPerson,
                                                        $tblCurrentSubject,
                                                        Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('EN')
                                                    )
                                                ) {
                                                    if ($calcValue) {
                                                        if ($tblPrepareStudent->getServiceTblCertificate()) {
                                                            $hasPreviewGrades = true;
                                                        }
                                                        $Global->POST['Data'][$tblPrepareStudent->getId()]['EN'] = round($calcValue, 0);
                                                    } elseif (isset($gradeList['JN_TEXT']) && ($tblGradeTextTemp = Gradebook::useService()->getGradeTextByName($gradeList['JN_TEXT']))) {
                                                        if ($tblPrepareStudent->getServiceTblCertificate()) {
                                                            $hasPreviewGrades = true;
                                                        }
                                                        $Global->POST['Data'][$tblPrepareStudent->getId()]['Text'] = $tblGradeTextTemp->getId();
                                                    }
                                                }
                                            }
                                        } else {
                                            $calcValue = '';
                                            if (isset($gradeList['JN'])) {
                                                $calc = false;
                                                if (isset($gradeList['PZ'])) {
                                                    if (isset($gradeList['PS'])) {
                                                        $calc = ($gradeList['JN'] + $gradeList['PS'] + $gradeList['PZ']) / 3;
                                                    } elseif (isset($gradeList['PM'])) {
                                                        $calc = ($gradeList['JN'] + $gradeList['PM'] + $gradeList['PZ']) / 3;
                                                    }
                                                } else {
                                                    if (isset($gradeList['PS'])) {
                                                        if (isset($gradeList['PM'])) {
                                                            // Sonderfall Englisch
                                                            $calc = ($gradeList['JN'] + $gradeList['PS'] + $gradeList['PM']) / 3;
                                                        } else {
                                                            $calc = ($gradeList['JN'] + $gradeList['PS']) / 2;
                                                        }
                                                    } elseif (isset($gradeList['PM'])) {
                                                        $calc = ($gradeList['JN'] + $gradeList['PM']) / 2;
                                                    }
                                                }
                                                if ($calc) {
                                                    $calcValue = round($calc, 2);
                                                } else {
                                                    $calcValue = $gradeList['JN'];
                                                }
                                            }
                                            $studentTable[$tblPerson->getId()]['Average'] = str_replace('.', ',',
                                                $calcValue);

                                            if (!Prepare::useService()->getPrepareAdditionalGradeBy(
                                                    $tblPrepareItem,
                                                    $tblPerson,
                                                    $tblCurrentSubject,
                                                    Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('EN')
                                                )
                                            ) {
                                                if ($calcValue) {
                                                    if ($tblPrepareStudent->getServiceTblCertificate()) {
                                                        $hasPreviewGrades = true;
                                                    }
                                                    // bei ,5 entscheidet die Prüfungsnote bei FOS und BFS
                                                    if (($tblSchoolType->getShortName() == 'FOS' || $tblSchoolType->getShortName() == 'BFS')
                                                        && strpos($calcValue, '.5') !== false
                                                        && $gradeList['JN'] > $calcValue
                                                    ) {
                                                        $round = PHP_ROUND_HALF_DOWN;
                                                    } else {
                                                        $round = PHP_ROUND_HALF_UP;
                                                    }
                                                    $Global->POST['Data'][$tblPrepareStudent->getId()]['EN'] = round($calcValue, 0, $round);
                                                } elseif (isset($gradeList['JN_TEXT']) && ($tblGradeTextTemp = Gradebook::useService()->getGradeTextByName($gradeList['JN_TEXT']))) {
                                                    if ($tblPrepareStudent->getServiceTblCertificate()) {
                                                        $hasPreviewGrades = true;
                                                    }
                                                    $Global->POST['Data'][$tblPrepareStudent->getId()]['Text'] = $tblGradeTextTemp->getId();
                                                }
                                            }
                                        }
                                    }

                                    $Global->savePost();
                                }
                            }

                            $tblGradeTextList = Gradebook::useService()->getGradeTextAll();

                            if ($isCourseMainDiploma && $tblPrepareStudent) {
                                // Klasse 9 Hauptschule
                                if (!$isMuted && $hasSubject) {
                                    $isApproved = $tblPrepareStudent && $tblPrepareStudent->isApproved();
                                    if ($IsFinalGrade
                                        || $isApproved
                                    ) {
                                        $studentTable[$tblPerson->getId()]['JN'] =
                                            (new TextField('Data[' . $tblPrepareStudent->getId() . '][JN]'))->setDisabled();
                                        $studentTable[$tblPerson->getId()]['LS'] =
                                            (new NumberField('Data[' . $tblPrepareStudent->getId() . '][LS]'))->setDisabled();
                                        $studentTable[$tblPerson->getId()]['LM'] =
                                            (new NumberField('Data[' . $tblPrepareStudent->getId() . '][LM]'))->setDisabled();
                                    } else {
                                        $studentTable[$tblPerson->getId()]['JN'] =
                                            (new TextField('Data[' . $tblPrepareStudent->getId() . '][JN]'))->setTabIndex($tabIndex++)->setDisabled();
                                        $studentTable[$tblPerson->getId()]['LS'] =
                                            (new NumberField('Data[' . $tblPrepareStudent->getId() . '][LS]'))->setTabIndex($tabIndex++);
                                        $studentTable[$tblPerson->getId()]['LM'] =
                                            (new NumberField('Data[' . $tblPrepareStudent->getId() . '][LM]'))->setTabIndex($tabIndex++);
                                    }

                                    if ($IsFinalGrade) {
                                        if ($isApproved) {
                                            $studentTable[$tblPerson->getId()]['EN'] =
                                                (new NumberField('Data[' . $tblPrepareStudent->getId() . '][EN]'))->setDisabled();
                                            if ($tblGradeTextList) {
                                                $studentTable[$tblPerson->getId()]['Text'] = (new SelectBox('Data[' . $tblPrepareStudent->getId() . '][Text]',
                                                    '',
                                                    array(TblGradeText::ATTR_NAME => $tblGradeTextList)))->setDisabled();
                                            }
                                        } else {
                                            if ($tblGradeTextList) {
                                                $studentTable[$tblPerson->getId()]['Text'] = (new SelectBox('Data[' . $tblPrepareStudent->getId() . '][Text]',
                                                    '',
                                                    array(TblGradeText::ATTR_NAME => $tblGradeTextList)))->setTabIndex($tabIndex++);
                                            }
                                            $studentTable[$tblPerson->getId()]['EN'] =
                                                (new NumberField('Data[' . $tblPrepareStudent->getId() . '][EN]'))->setTabIndex($tabIndex++);
                                        }
                                    }
                                } else {
                                    $studentTable[$tblPerson->getId()]['JN']
                                        = $studentTable[$tblPerson->getId()]['LS']
                                        = $studentTable[$tblPerson->getId()]['LM']
                                        = $studentTable[$tblPerson->getId()]['EN'] = '';
                                }
                            } else {
                                // Klasse 10 Realschule
                                if ($hasSubject && $tblPrepareStudent) {
                                    $isApproved = $tblPrepareStudent->isApproved();
                                    if ($IsFinalGrade
                                        || $isApproved
                                    ) {
                                        $studentTable[$tblPerson->getId()]['JN'] =
                                            (new TextField('Data[' . $tblPrepareStudent->getId() . '][JN]'))->setDisabled();
                                        $studentTable[$tblPerson->getId()]['PS'] =
                                            (new NumberField('Data[' . $tblPrepareStudent->getId() . '][PS]'))->setDisabled();
                                        $studentTable[$tblPerson->getId()]['PM'] =
                                            (new NumberField('Data[' . $tblPrepareStudent->getId() . '][PM]'))->setDisabled();
                                        $studentTable[$tblPerson->getId()]['PZ'] =
                                            (new NumberField('Data[' . $tblPrepareStudent->getId() . '][PZ]'))->setDisabled();
                                    } else {
                                        $studentTable[$tblPerson->getId()]['JN'] =
                                            (new TextField('Data[' . $tblPrepareStudent->getId() . '][JN]'))->setTabIndex($tabIndex++)->setDisabled();
                                        $studentTable[$tblPerson->getId()]['PS'] =
                                            (new NumberField('Data[' . $tblPrepareStudent->getId() . '][PS]'))->setTabIndex($tabIndex++);
                                        $studentTable[$tblPerson->getId()]['PM'] =
                                            (new NumberField('Data[' . $tblPrepareStudent->getId() . '][PM]'))->setTabIndex($tabIndex++);
                                        $studentTable[$tblPerson->getId()]['PZ'] =
                                            (new NumberField('Data[' . $tblPrepareStudent->getId() . '][PZ]'))->setTabIndex($tabIndex++);
                                    }

                                    if ($IsFinalGrade) {
                                        if ($isApproved) {
                                            $studentTable[$tblPerson->getId()]['EN'] =
                                                (new NumberField('Data[' . $tblPrepareStudent->getId() . '][EN]'))->setDisabled();
                                            if ($tblGradeTextList) {
                                                $studentTable[$tblPerson->getId()]['Text'] = (new SelectBox('Data[' . $tblPrepareStudent->getId() . '][Text]',
                                                    '',
                                                    array(TblGradeText::ATTR_NAME => $tblGradeTextList)))->setDisabled();
                                            }
                                        } else {
                                            $studentTable[$tblPerson->getId()]['EN'] =
                                                (new NumberField('Data[' . $tblPrepareStudent->getId() . '][EN]'))->setTabIndex($tabIndex++);
                                            if ($tblGradeTextList) {
                                                $studentTable[$tblPerson->getId()]['Text'] = (new SelectBox('Data[' . $tblPrepareStudent->getId() . '][Text]',
                                                    '',
                                                    array(TblGradeText::ATTR_NAME => $tblGradeTextList)))->setTabIndex($tabIndex++);
                                            }
                                        }
                                    }
                                } else {
                                    $studentTable[$tblPerson->getId()]['JN']
                                        = $studentTable[$tblPerson->getId()]['PS']
                                        = $studentTable[$tblPerson->getId()]['PM']
                                        = $studentTable[$tblPerson->getId()]['PZ']
                                        = $studentTable[$tblPerson->getId()]['EN']
                                        = $studentTable[$tblPerson->getId()]['Text'] = '';
                                }
                            }
                        }
                    }
                }
            }
        }

        return array($studentTable, $hasPreviewGrades, $missingTemplateList);
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblSubject $tblSubject
     * @param $studentList
     *
     * @return array
     */
    private function setDiplomaGrade(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblSubject $tblSubject,
        $studentList
    ) {
        if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('EN'))
            && ($tblPrepareAdditionalGrade = Prepare::useService()->getPrepareAdditionalGradeBy(
                $tblPrepare,
                $tblPerson,
                $tblSubject,
                $tblPrepareAdditionalGradeType
            ))
            && $tblPrepareAdditionalGrade->getGrade()
        ) {
            $studentList[$tblPerson->getId()][$tblSubject->getAcronym()] = $tblPrepareAdditionalGrade->getGrade();
        } else {
            $studentList[$tblPerson->getId()][$tblSubject->getAcronym()] =
                new \SPHERE\Common\Frontend\Text\Repository\Warning('fehlt');
        }

        return $studentList;
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param $tblTestList
     * @return array
     */
    private function sortSubjects(TblPrepareCertificate $tblPrepare, $tblTestList)
    {
        $tblCertificate = false;
        // Ermittelung richtiges Zeugnis von Schülern
        if (($tblDivisionItem = $tblPrepare->getServiceTblDivision())
            && (($tblStudentList = Division::useService()->getStudentAllByDivision($tblDivisionItem)))
        ) {
            foreach ($tblStudentList as $tblPerson) {
                if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))
                    && ($tblCertificateStudent = $tblPrepareStudent->getServiceTblCertificate())
                ) {

                    if ($tblCertificateStudent->getTblCertificateType()->getIdentifier() == 'DIPLOMA') {
                        $tblCertificate = $tblCertificateStudent;
                        break;
                    }
                }
            }
        }

        if ($tblCertificate && $tblTestList) {
            $tblTestSortedList = array();
            $offset = 0;
            /** @var TblTest $tblTest */
            foreach ($tblTestList as $tblTest) {
                if (($tblSubjectItem = $tblTest->getServiceTblSubject())) {
                    if ($tblCertificate
                        && ($tblCertificateSubject = Generator::useService()->getCertificateSubjectBySubject($tblCertificate,
                            $tblSubjectItem))
                    ) {
                        if ($tblCertificateSubject->getLane() == 1) {
                            $index = 10 * (2 * $tblCertificateSubject->getRanking());
                        } else {
                            $index = 10 * (2 * $tblCertificateSubject->getRanking() + 1);
                        }
                    } else {
                        $offset++;
                        $index = 1000 + $offset;
                    }

                    // für Fachgruppen notwendig
                    while (isset($tblTestSortedList[$index])) {
                        $index++;
                    }
                    $tblTestSortedList[$index] = $tblTest;
                }
            }
            ksort($tblTestSortedList);
            $tblTestList = $tblTestSortedList;
        }

        return $tblTestList;
    }





    /**
     * @param TblCertificate|null $tblCertificate
     * @param TblLeaveStudent|null $tblLeaveStudent
     * @param TblDivision|null $tblDivision
     * @param TblPerson|null $tblPerson
     * @param Stage $stage
     * @param TblType|null $tblType
     * @param TblCourse|null $tblCourse
     *
     * @return array
     */
    private function setLeaveContentForSekTwo(
        TblCertificate $tblCertificate = null,
        TblLeaveStudent $tblLeaveStudent = null,
        TblDivision $tblDivision = null,
        TblPerson $tblPerson = null,
        Stage $stage,
        TblType $tblType = null,
        TblCourse $tblCourse = null
    ) {

        $form = false;

        if (Student::useService()->getIsSupportByPerson($tblPerson)) {
            $support = ApiSupportReadOnly::openOverViewModal($tblPerson->getId(), false);
        } else {
            $support = false;
        }

        if ($tblCertificate) {
            if ($tblLeaveStudent) {
                $stage->addButton(new External(
                    'Zeugnis als Muster herunterladen',
                    '/Api/Education/Certificate/Generator/PreviewLeave',
                    new Download(),
                    array(
                        'LeaveStudentId' => $tblLeaveStudent->getId(),
                        'Name' => 'Zeugnismuster'
                    ),
                    'Zeugnis als Muster herunterladen'));

                $form = (new LeavePoints($tblLeaveStudent))->getForm();
            }
        }

        $layoutGroups[] = new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    new Panel(
                        'Schüler',
                        $tblPerson->getLastFirstName(),
                        Panel::PANEL_TYPE_INFO
                    )
                    , 3),
                new LayoutColumn(
                    new Panel(
                        'Klasse',
                        $tblDivision
                            ? $tblDivision->getDisplayName()
                            : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                            . ' Keine aktuelle Klasse zum Schüler gefunden!'),
                        $tblDivision ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_WARNING
                    )
                    , 3),
                new LayoutColumn(
                    new Panel(
                        'Schulart',
                        $tblType
                            ? $tblType->getName() . ($tblCourse ? ' - ' . $tblCourse->getName() : '')
                            : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                            . ' Keine aktuelle Schulart zum Schüler gefunden!'),
                        $tblType ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_WARNING
                    )
                    , 3),
                new LayoutColumn(
                    new Panel(
                        'Zeugnisvorlage',
                        $tblCertificate
                            ? $tblCertificate->getName()
                            . ($tblCertificate->getDescription()
                                ? new Muted(' - ' . $tblCertificate->getDescription()) : '')
                            : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                            . ' Keine Zeugnisvorlage verfügbar!'),
                        $tblCertificate ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_WARNING
                    )
                    , 3),
                ($support
                    ? new LayoutColumn(new Panel('Integration', $support, Panel::PANEL_TYPE_INFO))
                    : null
                ),
            )),
        ));

        if ($form && $tblLeaveStudent) {
            $layoutGroups[] = new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(array(
                        new Standard('Punkte bearbeiten',
                            '/Education/Certificate/Prepare/Leave/Student/Abitur/Points',
                            new Edit(), array(
                                'Id' => $tblLeaveStudent->getId(),
                            )
                        ),
                        new Standard('Sonstige Informationen bearbeiten',
                            '/Education/Certificate/Prepare/Leave/Student/Abitur/Information',
                            new Edit(), array(
                                'Id' => $tblLeaveStudent->getId(),
                            )
                        ),
                        '<br />',
                        '<br />'
                    )),
                )),
            ));
        }

        if ($tblCertificate) {
            /** @var Form $form */
            if ($form) {
                $layoutGroups[] = new LayoutGroup(new LayoutRow(new LayoutColumn(
                    $form
                )));
            }

            $panelList[] = array();
            if (($leaveTermInformation = Prepare::useService()->getLeaveInformationBy($tblLeaveStudent, 'LeaveTerm'))) {
                $panelList[] = new Panel(
                    'verlässt das Gymnasium',
                    $leaveTermInformation->getValue()
                );
            }
            if (($midTermInformation = Prepare::useService()->getLeaveInformationBy($tblLeaveStudent, 'MidTerm'))) {
                $panelList[] = new Panel(
                    'Kurshalbjahr',
                    $midTermInformation->getValue()
                );
            }
            if (($dateInformation = Prepare::useService()->getLeaveInformationBy($tblLeaveStudent, 'CertificateDate'))) {
                $panelList[] = new Panel(
                    'Zeugnisdatum',
                    $dateInformation->getValue()
                );
            }
            if (($remarkInformation = Prepare::useService()->getLeaveInformationBy($tblLeaveStudent, 'Remark'))) {
                $panelList[] = new Panel(
                    'Bemerkungen',
                    $remarkInformation->getValue()
                );
            }

            $layoutGroups[] = new LayoutGroup(new LayoutRow(new LayoutColumn(
                new Panel(
                    'Sonstige Informationen',
                    $panelList,
                    Panel::PANEL_TYPE_PRIMARY
                )
            )));
        }

        return $layoutGroups;
    }

    /**
     * @param null $Id
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendLeaveStudentAbiturPoints($Id = null, $Data = null)
    {

        if (($tblLeaveStudent = Prepare::useService()->getLeaveStudentById($Id))
            && ($tblPerson = $tblLeaveStudent->getServiceTblPerson())
        ) {
            $stage = new Stage(new Stage('Zeugnisvorbereitung', 'Abgangszeugnis - Punkte'));

            $tblDivision = $tblLeaveStudent->getServiceTblDivision();
            $tblCertificate = $tblLeaveStudent->getServiceTblCertificate();

            $stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/Prepare/Leave/Student', new ChevronLeft(), array(
                    'PersonId' => $tblPerson->getId(),
                    'DivisionId' => $tblDivision ? $tblDivision->getId() : 0
                )
            ));

            if ($tblDivision
                && ($tblLevel = $tblDivision->getTblLevel())
            ) {
                $tblType = $tblLevel->getServiceTblType();
            } else {
                $tblType = false;
            }

            if (($tblStudent = $tblPerson->getStudent())){
                $tblCourse = $tblStudent->getCourse();
            } else {
                $tblCourse = false;
            }

            if (Student::useService()->getIsSupportByPerson($tblPerson)) {
                $support = ApiSupportReadOnly::openOverViewModal($tblPerson->getId(), false);
            } else {
                $support = false;
            }

            $layoutGroups[] = new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Panel(
                            'Schüler',
                            $tblPerson->getLastFirstName(),
                            Panel::PANEL_TYPE_INFO
                        )
                        , 3),
                    new LayoutColumn(
                        new Panel(
                            'Klasse',
                            $tblDivision
                                ? $tblDivision->getDisplayName()
                                : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                                . ' Keine aktuelle Klasse zum Schüler gefunden!'),
                            $tblDivision ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_WARNING
                        )
                        , 3),
                    new LayoutColumn(
                        new Panel(
                            'Schulart',
                            $tblType
                                ? $tblType->getName() . ($tblCourse ? ' - ' . $tblCourse->getName() : '')
                                : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                                . ' Keine aktuelle Schulart zum Schüler gefunden!'),
                            $tblType ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_WARNING
                        )
                        , 3),
                    new LayoutColumn(
                        new Panel(
                            'Zeugnisvorlage',
                            $tblCertificate
                                ? $tblCertificate->getName()
                                . ($tblCertificate->getDescription()
                                    ? new Muted(' - ' . $tblCertificate->getDescription()) : '')
                                : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                                . ' Keine Zeugnisvorlage verfügbar!'),
                            $tblCertificate ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_WARNING
                        )
                        , 3),
                    ($support
                        ? new LayoutColumn(new Panel('Integration', $support, Panel::PANEL_TYPE_INFO))
                        : null
                    )
                )),
            ));

            $LeavePoints = new LeavePoints($tblLeaveStudent, BlockIView::EDIT_GRADES);
            $form = $LeavePoints->getForm();

            $layoutGroups[] = new LayoutGroup(new LayoutRow(new LayoutColumn(
                new Well(
                    Prepare::useService()->updateLeaveStudentAbiturPoints($form, $tblLeaveStudent, $Data)
                )
            )));

            $stage->setContent(new Layout($layoutGroups));

            return $stage;
        } else {
            return new Stage('Zeugnisvorbereitung', 'Abgangszeugnis - Schüler')
                . new Danger('Schüler nicht gefunden', new Ban())
                . new Redirect('/Education/Certificate/Prepare/Leave', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param null $Id
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendLeaveStudentAbiturInformation($Id = null, $Data = null)
    {

        if (($tblLeaveStudent = Prepare::useService()->getLeaveStudentById($Id))
            && ($tblPerson = $tblLeaveStudent->getServiceTblPerson())
        ) {
            $stage = new Stage(new Stage('Zeugnisvorbereitung', 'Abgangszeugnis - Sonstige Informationen'));

            $tblDivision = $tblLeaveStudent->getServiceTblDivision();
            $tblCertificate = $tblLeaveStudent->getServiceTblCertificate();
            $isApproved = $tblLeaveStudent->isApproved();

            $stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/Prepare/Leave/Student', new ChevronLeft(), array(
                    'PersonId' => $tblPerson->getId(),
                    'DivisionId' => $tblDivision ? $tblDivision->getId() : 0
                )
            ));

            if ($tblDivision
                && ($tblLevel = $tblDivision->getTblLevel())
            ) {
                $tblType = $tblLevel->getServiceTblType();
            } else {
                $tblType = false;
            }

            if (($tblStudent = $tblPerson->getStudent())){
                $tblCourse = $tblStudent->getCourse();
            } else {
                $tblCourse = false;
            }

            if (Student::useService()->getIsSupportByPerson($tblPerson)) {
                $support = ApiSupportReadOnly::openOverViewModal($tblPerson->getId(), false);
            } else {
                $support = false;
            }

            $layoutGroups[] = new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Panel(
                            'Schüler',
                            $tblPerson->getLastFirstName(),
                            Panel::PANEL_TYPE_INFO
                        )
                        , 3),
                    new LayoutColumn(
                        new Panel(
                            'Klasse',
                            $tblDivision
                                ? $tblDivision->getDisplayName()
                                : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                                . ' Keine aktuelle Klasse zum Schüler gefunden!'),
                            $tblDivision ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_WARNING
                        )
                        , 3),
                    new LayoutColumn(
                        new Panel(
                            'Schulart',
                            $tblType
                                ? $tblType->getName() . ($tblCourse ? ' - ' . $tblCourse->getName() : '')
                                : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                                . ' Keine aktuelle Schulart zum Schüler gefunden!'),
                            $tblType ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_WARNING
                        )
                        , 3),
                    new LayoutColumn(
                        new Panel(
                            'Zeugnisvorlage',
                            $tblCertificate
                                ? $tblCertificate->getName()
                                . ($tblCertificate->getDescription()
                                    ? new Muted(' - ' . $tblCertificate->getDescription()) : '')
                                : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                                . ' Keine Zeugnisvorlage verfügbar!'),
                            $tblCertificate ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_WARNING
                        )
                        , 3),
                    ($support
                        ? new LayoutColumn(new Panel('Integration', $support, Panel::PANEL_TYPE_INFO))
                        : null
                    )
                )),
            ));

            if ($tblCertificate) {
                $leaveTerms = GymAbgSekII::getLeaveTerms();
                $midTerms = GymAbgSekII::getMidTerms();

                // Post
                if (($tblLeaveInformationList = Prepare::useService()->getLeaveInformationAllByLeaveStudent($tblLeaveStudent))) {
                    $global = $this->getGlobal();
                    foreach ($tblLeaveInformationList as $tblLeaveInformation) {
                        if ($tblLeaveInformation->getField() == 'LeaveTerm') {
                            $value = array_search($tblLeaveInformation->getValue(), $leaveTerms);
                        } elseif ($tblLeaveInformation->getField() == 'MidTerm') {
                            $value = array_search($tblLeaveInformation->getValue(), $midTerms);
                        } else {
                            $value = $tblLeaveInformation->getValue();
                        }

                        $global->POST['Data'][$tblLeaveInformation->getField()] = $value;
                    }
                    $global->savePost();
                }

                $leaveTermSelectBox = (new SelectBox(
                    'Data[LeaveTerm]',
                    'verlässt das Gymnasium',
                    $leaveTerms
                ))->setRequired();
                $midTermSelectBox = (new SelectBox(
                    'Data[MidTerm]',
                    'Kurshalbjahr',
                    $midTerms
                ))->setRequired();
                $datePicker = (new DatePicker('Data[CertificateDate]', '', 'Zeugnisdatum',
                    new Calendar()))->setRequired();
                $remarkTextArea = new TextArea('Data[Remark]', '', 'Bemerkungen');
                if ($isApproved) {
                    $datePicker->setDisabled();
                    $remarkTextArea->setDisabled();
                    $leaveTermSelectBox->setDisabled();
                    $midTermSelectBox->setDisabled();
                }
                $otherInformationList = array(
                    $leaveTermSelectBox,
                    $midTermSelectBox,
                    $datePicker,
                    $remarkTextArea
                );

                $headmasterNameTextField = new TextField('Data[HeadmasterName]', '',
                    'Name des/der Schulleiters/in');
                $radioSex1 = (new RadioBox('Data[HeadmasterGender]', 'Männlich',
                    ($tblCommonGender = Common::useService()->getCommonGenderByName('Männlich'))
                        ? $tblCommonGender->getId() : 0));
                $radioSex2 = (new RadioBox('Data[HeadmasterGender]', 'Weiblich',
                    ($tblCommonGender = Common::useService()->getCommonGenderByName('Weiblich'))
                        ? $tblCommonGender->getId() : 0));
                if ($isApproved) {
                    $headmasterNameTextField->setDisabled();
                    $radioSex1->setDisabled();
                    $radioSex2->setDisabled();
                }

                $form = new Form(new FormGroup(array(
                    new FormRow(new FormColumn(
                        new Panel(
                            'Sonstige Informationen',
                            $otherInformationList,
                            Panel::PANEL_TYPE_INFO
                        )
                    )),
                    new FormRow(array(
                        new FormColumn(
                            new Panel(
                                'Unterzeichner - Schulleiter',
                                array(
                                    $headmasterNameTextField,
                                    new Panel(
                                        new Small(new Bold('Geschlecht des/der Schulleiters/in')),
                                        array($radioSex1, $radioSex2),
                                        Panel::PANEL_TYPE_DEFAULT
                                    )
                                ),
                                Panel::PANEL_TYPE_INFO
                            )
                            , 6),
                    )),
                )));
            } else {
                $form = null;
            }

            if ($isApproved) {
                $content = $form;
            } else {
                $form->appendFormButton(new Primary('Speichern', new Save()));
                $content = new Well(
                    Prepare::useService()->updateAbiturLeaveInformation($form, $tblLeaveStudent, $Data)
                );
            }

            $layoutGroups[] = new LayoutGroup(new LayoutRow(new LayoutColumn(
                $content
            )));

            $stage->setContent(new Layout($layoutGroups));

            return $stage;
        } else {
            return new Stage('Zeugnisvorbereitung', 'Abgangszeugnis - Schüler')
                . new Danger('Schüler nicht gefunden', new Ban())
                . new Redirect('/Education/Certificate/Prepare/Leave', Redirect::TIMEOUT_ERROR);
        }
    }
}
