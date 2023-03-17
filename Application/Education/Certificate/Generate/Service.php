<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 25.11.2016
 * Time: 11:24
 */

namespace SPHERE\Application\Education\Certificate\Generate;

use SPHERE\Application\Education\Certificate\Generate\Service\Data;
use SPHERE\Application\Education\Certificate\Generate\Service\Entity\TblGenerateCertificate;
use SPHERE\Application\Education\Certificate\Generate\Service\Entity\TblGenerateCertificateSetting;
use SPHERE\Application\Education\Certificate\Generate\Service\Setup;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateLevel;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateType;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Certificate\Setting\Setting;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

class Service extends AbstractService
{
    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8): string
    {
        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }

        return $Protocol;
    }

    /**
     * @param $Id
     *
     * @return false|TblGenerateCertificate
     */
    public function getGenerateCertificateById($Id)
    {
        return (new Data($this->getBinding()))->getGenerateCertificateById($Id);
    }

    /**
     * @param TblYear $tblYear
     *
     * @return false|TblGenerateCertificate[]
     */
    public function getGenerateCertificateAllByYear(TblYear $tblYear)
    {
        return (new Data($this->getBinding()))->getGenerateCertificateAllByYear($tblYear);
    }

    /**
     * @return false|TblGenerateCertificate[]
     */
    public function getGenerateCertificateAll()
    {
        return (new Data($this->getBinding()))->getGenerateCertificateAll();
    }

    /**
     * @param IFormInterface|null $Form
     * @param null $Data
     *
     * @return IFormInterface|string
     */
    public function createGenerateCertificate(?IFormInterface $Form, $Data = null)
    {
        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Form;
        }

        $Error = false;
        if (isset($Data['Date']) && empty($Data['Date'])) {
            $Form->setError('Data[Date]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if (!($tblCertificateType = Generator::useService()->getCertificateTypeById($Data['Type']))) {
            $Form->setError('Data[Type]', 'Bitte wählen Sie einen Typ aus');
            $Error = true;
        }
        if (!($tblYear = Term::useService()->getYearById($Data['Year']))) {
            $Form->setError('Data[Year]', 'Bitte wählen Sie einen Typ aus');
            $Error = true;
        }
        if (isset($Data['Name']) && empty($Data['Name'])) {
            $Form->setError('Data[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }

        if ($Error) {
            return $Form;
        }

        $tblAppointedDateTask = Grade::useService()->getTaskById($Data['AppointedDateTask']);
        $tblBehaviorTask = Grade::useService()->getTaskById($Data['BehaviorTask']);

        if ($tblGenerateCertificate = (new Data($this->getBinding()))->createGenerateCertificate(
            $tblYear,
            $Data['Date'],
            $Data['Name'],
            $tblCertificateType,
            $tblAppointedDateTask ?: null,
            $tblBehaviorTask ?: null,
            $Data['HeadmasterName'] ?? '',
            isset($Data['IsTeacherAvailable']),
            isset($Data['GenderHeadmaster']) && ($tblCommonGender = Common::useService()->getCommonGenderById($Data['GenderHeadmaster']))
                ? $tblCommonGender : null,
            $Data['AppointedDateForAbsence']
        )) {
            return new Success('Die Zeugniserstellung ist angelegt worden',
                    new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . new Redirect('/Education/Certificate/Generate/Division/Select', Redirect::TIMEOUT_SUCCESS, array(
                    'GenerateCertificateId' => $tblGenerateCertificate->getId()
                ));
        } else {
            return new Danger('Die Zeugniserstellung konnte nicht angelegt werden', new Exclamation())
                . new Redirect('/Education/Certificate/Generate', Redirect::TIMEOUT_SUCCESS);
        }
    }

    /**
     * @param IFormInterface|null $Form
     * @param TblGenerateCertificate $tblGenerateCertificate
     * @param null $Data
     *
     * @return IFormInterface|string
     */
    public function createPrepareCertificates(
        ?IFormInterface $Form,
        TblGenerateCertificate $tblGenerateCertificate,
        $Data = null
    ) {
        /**
         * Skip to Frontend
         */
        if ($Data === null) {
            return $Form;
        }

        // Soft - Löschen
        $existsDivisionCourseList = array();
        if (($tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate))) {
            foreach ($tblPrepareList as $tblPrepareTemp) {
                if (($tblDivisionCourseTemp = $tblPrepareTemp->getServiceTblDivision())) {
                    if (!isset($Data['Division'][$tblDivisionCourseTemp->getId()])) {
                        Prepare::useService()->destroyPrepareCertificate($tblPrepareTemp);
                    } else {
                        $existsDivisionCourseList[$tblDivisionCourseTemp->getId()] = $tblDivisionCourseTemp;
                    }
                }
            }
        }

        if (isset($Data['Division'])) {
            $saveCertificatesForStudents = array();
            $tblConsumerBySession = Consumer::useService()->getConsumerBySession();
            $tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType();
            foreach ($Data['Division'] as $divisionCourseId => $value) {
                // existiert bereits
                if (isset($existsDivisionCourseList[$divisionCourseId])) {
                    continue;
                }

                if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($divisionCourseId))) {
                    // gelöschte Zeugnisvorbereitung wieder herstellen, forced holen -> update RemoveDate
                    if (($tblPrepareRemove = Prepare::useService()->getForcedPrepareByDivisionCourseAndGenerateCertificate($tblDivisionCourse, $tblGenerateCertificate))) {
                        Prepare::useService()->updatePrepareResetRemove($tblPrepareRemove);
                    // neue Zeugnisvorbereitung anlegen
                    } elseif (($tblPrepare = Prepare::useService()->createPrepareData(
                        $tblDivisionCourse,
                        $tblGenerateCertificate,
                        $tblDivisionCourse->getFirstDivisionTeacher() ?: null
                    ))) {
                        if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())) {
                            foreach ($tblPersonList as $tblPerson) {
                                // Template bereits gesetzt
                                if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))
                                    && $tblPrepareStudent->getServiceTblCertificate()
                                ) {
                                    continue;
                                }

                                if (($tblYear = $tblGenerateCertificate->getServiceTblYear())
                                    && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                                ) {
                                    $tblType = $tblStudentEducation->getServiceTblSchoolType();
                                } else {
                                    $tblStudentEducation = false;
                                    $tblType = false;
                                }

                                // bei Mittelschule und primärer Förderschwerpunkt Lernen oder geistige Entwicklung soll keine
                                // Zeugnisvorlage vorausgewählt werden
                                // SSW-1647 Noteninformation soll unabhängig vom FS immer gesetzt werden
                                if ($tblType && !$this->checkAutoSelect($tblPerson, $tblType)
                                    && $tblCertificateType && $tblCertificateType->getIdentifier() != 'GRADE_INFORMATION'
                                ) {
                                    continue;
                                }

                                // Berufsfachschüler mit Fachrichtung "Generalistik" sollen das korrekte Zeugnis automatisch gesetzt bekommen
                                if (($tblTechnicalCourse = Student::useService()->getTechnicalCourseByPerson($tblPerson))
                                    && $tblTechnicalCourse->getName() == 'Generalistik'
                                ) {
                                    $tblCertificate = Generator::useService()->getCertificateByCertificateClassName('BfsPflegeJ');
                                    $saveCertificatesForStudents[$tblPrepare->getId()][$tblPerson->getId()] = $tblCertificate;
                                    continue;
                                }

                                if ($tblConsumerBySession && $tblCertificateType && $tblStudentEducation) {
                                    // Eigene Vorlagen
                                    if (($certificateList = $this->getPossibleCertificates($tblStudentEducation, $tblCertificateType, $tblConsumerBySession))) {
                                        if (count($certificateList) == 1) {
                                            /** @var TblCertificate $tblCertificate */
                                            $tblCertificate = current($certificateList);
                                            $saveCertificatesForStudents[$tblPrepare->getId()][$tblPerson->getId()] = $tblCertificate;
                                        } elseif (count($certificateList) > 1) {
                                            /** @var TblCertificate $certificate */
                                            $ChosenCertificate = false;
                                            foreach ($certificateList as $certificate) {
                                                if ($certificate->isChosenDefault()) {
                                                    $ChosenCertificate = $certificate;
                                                    break;
                                                }
                                            }
                                            if ($ChosenCertificate) {
                                                $tblCertificate = $ChosenCertificate;
                                                $saveCertificatesForStudents[$tblPrepare->getId()][$tblPerson->getId()] = $tblCertificate;
                                            }
                                        } else {
                                            continue;
                                        }
                                    // Standard Vorlagen
                                    } elseif (($certificateList = $this->getPossibleCertificates($tblStudentEducation, $tblCertificateType))) {
                                        if (count($certificateList) == 1) {
                                            /** @var TblCertificate $tblCertificate */
                                            $tblCertificate = current($certificateList);
                                            $saveCertificatesForStudents[$tblPrepare->getId()][$tblPerson->getId()] = $tblCertificate;
                                        } elseif (count($certificateList) > 1) {
                                            /** @var TblCertificate $certificate */
                                            $ChosenCertificate = false;
                                            foreach ($certificateList as $certificate) {
                                                if ($certificate->isChosenDefault()) {
                                                    $ChosenCertificate = $certificate;
                                                    break;
                                                }
                                            }
                                            if ($ChosenCertificate) {
                                                $tblCertificate = $ChosenCertificate;
                                                $saveCertificatesForStudents[$tblPrepare->getId()][$tblPerson->getId()] = $tblCertificate;
                                            }
                                        } else {
                                            continue;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($saveCertificatesForStudents)) {
                Prepare::useService()->createPrepareStudentSetBulkTemplates($saveCertificatesForStudents);
            }
        }

        return new Success('Die Kurse wurden erfolgreich zugeordnet.',
                new \SPHERE\Common\Frontend\Icon\Repository\Success())
            . new Redirect('/Education/Certificate/Generate/Division', Redirect::TIMEOUT_SUCCESS,
                array('GenerateCertificateId' => $tblGenerateCertificate->getId()));
    }


    /**
     * Prüft ob für die Person automatisch eine Zeugnisvorlage zugewiesen wird
     *
     * @param TblPerson $tblPerson
     * @param TblType $tblType
     *
     * @return bool
     */
    private function checkAutoSelect(TblPerson $tblPerson, TblType $tblType): bool
    {
        if ($tblType->getShortName() == 'OS') {
            if (($tblSupport = Student::useService()->getSupportForReportingByPerson($tblPerson))
                && ($tblPrimaryFocus = Student::useService()->getPrimaryFocusBySupport($tblSupport))
            ) {
                if ($tblPrimaryFocus->getName() == 'Lernen') {
                    return false;
                }

                if ($tblPrimaryFocus->getName() == 'Geistige Entwicklung') {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param TblStudentEducation $tblStudentEducation
     * @param TblCertificateType $tblCertificateType
     * @param TblConsumer|null $tblConsumer
     *
     * @return array|bool
     */
    public function getPossibleCertificates(
        TblStudentEducation $tblStudentEducation,
        TblCertificateType $tblCertificateType,
        TblConsumer $tblConsumer = null
    ) {
        $certificateList = array();
        if (($level = $tblStudentEducation->getLevel())
            && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
            && ($tblCertificateList = Generator::useService()->getCertificateAllForAutoSelect($tblConsumer ?: null, $tblCertificateType, $tblSchoolType))
        ) {
            $levelName = (string) $level;
            // SSW-939 - Noteninformation Zuweisung Vorlage
            if ($tblCertificateType->getIdentifier() == 'GRADE_INFORMATION'
                && Setting::useService()->getCertificateByCertificateClassName('GradeInformation')
            ) {
                return $tblCertificateList;
            }

            $tblCourse = false;
            // Bildungsgang nur hier relevant sonst klappt es bei den anderen nicht korrekt
            // #SSW-1064 Automatische Zuordnung von Zeugnissen ist nicht korrekt in Coswig
            if ($this->getUseCourseForCertificateChoosing()) {
                if ($tblSchoolType->getShortName() == 'OS'
                    && preg_match('!(0?(7|8|9)|10)!is', $levelName)
                ) {
                    $tblCourse = $tblStudentEducation->getServiceTblCourse();
                }
            }

            foreach ($tblCertificateList as $tblCertificate) {
                // Schüler hat keinen Bildungsgang
                if (!$tblCourse) {
                    if (($tblCertificateLevelList = Generator::useService()->getCertificateLevelAllByCertificate($tblCertificate))) {
                        if ($this->findLevel($tblCertificateLevelList, $level)) {
                            $certificateList[] = $tblCertificate;
                        }
                    } else {
                        $certificateList[] = $tblCertificate;
                    }
                    //  Schüler hat Bildungsgang der Vorlage
                } elseif ($tblCertificate->getServiceTblCourse()
                    && $tblCourse->getId() == $tblCertificate->getServiceTblCourse()->getId()
                ) {
                    if (($tblCertificateLevelList = Generator::useService()->getCertificateLevelAllByCertificate($tblCertificate))) {
                        if ($this->findLevel($tblCertificateLevelList, $level)) {
                            $certificateList[] = $tblCertificate;
                        }
                    } else {
                        $certificateList[] = $tblCertificate;
                    }
                }
            }
        }

        return empty($certificateList) ? false : $certificateList;
    }

    /**
     * @param $tblCertificateLevelList
     * @param int $level
     *
     * @return bool
     */
    private function findLevel($tblCertificateLevelList, int $level): bool
    {
        /** @var TblCertificateLevel $tblCertificateLevel */
        foreach ($tblCertificateLevelList as $tblCertificateLevel) {
            if ($tblCertificateLevel->getLevel() == $level) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param IFormInterface|null $Form
     * @param TblPrepareCertificate $tblPrepare
     * @param null $Data
     *
     * @return IFormInterface|string
     */
    public function editCertificateTemplates(
        ?IFormInterface $Form,
        TblPrepareCertificate $tblPrepare,
        $Data = null
    ) {
        /**
         * Skip to Frontend
         */
        if ($Data === null) {
            return $Form;
        }

        if (!empty($Data)) {
            foreach ($Data as $personId => $value) {
                if (($tblPerson = Person::useService()->getPersonById($personId))) {
                    $tblCertificate = Generator::useService()->getCertificateById($value);
                    Prepare::useService()->updatePrepareStudentSetCertificate($tblPrepare, $tblPerson, $tblCertificate ?: null);
                }
            }
        }

        return new Success('Die Zeugnisvorlagen wurden erfolgreich zugeordnet.',
                new \SPHERE\Common\Frontend\Icon\Repository\Success())
            . new Redirect('/Education/Certificate/Generate/Division', Redirect::TIMEOUT_SUCCESS,
                array('GenerateCertificateId' => $tblPrepare->getServiceTblGenerateCertificate()->getId()));
    }

    /**
     * @param IFormInterface|null $Form
     * @param TblGenerateCertificate $tblGenerateCertificate
     * @param null $Data
     *
     * @return IFormInterface|string
     */
    public function updateGenerateCertificate(
        ?IFormInterface $Form,
        TblGenerateCertificate $tblGenerateCertificate,
        $Data = null
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Form;
        }

        $Error = false;
        if (isset($Data['Date']) && empty($Data['Date'])) {
            $Form->setError('Data[Date]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if (isset($Data['Name']) && empty($Data['Name'])) {
            $Form->setError('Data[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }

        if ($Error) {
            return $Form;
        }

        $tblAppointedDateTask = isset($Data['AppointedDateTask']) ? Grade::useService()->getTaskById($Data['AppointedDateTask']) : false;
        $tblBehaviorTask = isset($Data['BehaviorTask']) ? Grade::useService()->getTaskById($Data['BehaviorTask']) : false;

        if ((new Data($this->getBinding()))->updateGenerateCertificate(
            $tblGenerateCertificate,
            $Data['Date'],
            isset($Data['IsTeacherAvailable']),
            $Data['HeadmasterName'] ?? '',
            isset($Data['GenderHeadmaster']) && ($tblCommonGender = Common::useService()->getCommonGenderById($Data['GenderHeadmaster']))
                ? $tblCommonGender : null,
            $tblAppointedDateTask ?: null,
            $tblBehaviorTask ?: null,
            $Data['Name'],
            $Data['AppointedDateForAbsence']
        )) {
            return new Success('Die Zeugniserstellung ist geändert worden',
                    new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . new Redirect('/Education/Certificate/Generate', Redirect::TIMEOUT_SUCCESS, array(
                    'GenerateCertificateId' => $tblGenerateCertificate->getId()
                ));
        } else {
            return new Danger('Die Zeugniserstellung konnte nicht geändert werden', new Exclamation())
                . new Redirect('/Education/Certificate/Generate', Redirect::TIMEOUT_SUCCESS);
        }
    }

    /**
     * @return bool
     */
    public function getUseCourseForCertificateChoosing(): bool
    {
        if (($tblSetting = \SPHERE\Application\Setting\Consumer\Consumer::useService()->getSetting(
            'Education', 'Certificate', 'Generate', 'UseCourseForCertificateChoosing'))
        ) {
            return (boolean)$tblSetting->getValue();
        }

        return false;
    }

    /**
     * @param TblGenerateCertificate $tblGenerateCertificate
     *
     * @return bool
     */
    public function destroyGenerateCertificate(TblGenerateCertificate $tblGenerateCertificate): bool
    {
        if (($tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate))) {
            foreach ($tblPrepareList as $tblPrepare) {
                Prepare::useService()->destroyPrepareCertificate($tblPrepare);
            }
        }

        return (new Data($this->getBinding()))->destroyGenerateCertificate($tblGenerateCertificate);
    }

    /**
     * @param IFormInterface $form
     * @param TblGenerateCertificate $tblGenerateCertificate
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function updateAbiturSettings(
        IFormInterface $form,
        TblGenerateCertificate $tblGenerateCertificate,
        $Data
    ) {
        /**
         * Skip to Frontend
         */
        if ($Data === null) {
            return $form;
        }

        $tblPersonLeader = Person::useService()->getPersonById($Data['Leader']);
        if (($tblGenerateCertificateSettingLeader = $this->getGenerateCertificateSettingBy($tblGenerateCertificate, 'Leader'))) {
            (new Data($this->getBinding()))->updateGenerateCertificateSetting($tblGenerateCertificateSettingLeader, $tblPersonLeader);
        } else {
            (new Data($this->getBinding()))->createGenerateCertificateSetting($tblGenerateCertificate, 'Leader', $tblPersonLeader);
        }

        $tblPersonFirstMember = Person::useService()->getPersonById($Data['FirstMember']);
        if (($tblGenerateCertificateSettingFirstMember = $this->getGenerateCertificateSettingBy($tblGenerateCertificate, 'FirstMember'))) {
            (new Data($this->getBinding()))->updateGenerateCertificateSetting($tblGenerateCertificateSettingFirstMember, $tblPersonFirstMember);
        } else {
            (new Data($this->getBinding()))->createGenerateCertificateSetting($tblGenerateCertificate, 'FirstMember', $tblPersonFirstMember);
        }

        $tblPersonSecondMember = Person::useService()->getPersonById($Data['SecondMember']);
        if (($tblGenerateCertificateSettingSecondMember = $this->getGenerateCertificateSettingBy($tblGenerateCertificate, 'SecondMember'))) {
            (new Data($this->getBinding()))->updateGenerateCertificateSetting($tblGenerateCertificateSettingSecondMember, $tblPersonSecondMember);
        } else {
            (new Data($this->getBinding()))->createGenerateCertificateSetting($tblGenerateCertificate, 'SecondMember', $tblPersonSecondMember);
        }

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Informationen wurden erfolgreich gespeichert.')
            . new Redirect('/Education/Certificate/Generate/Setting', Redirect::TIMEOUT_SUCCESS, array(
                'GenerateCertificateId' => $tblGenerateCertificate->getId(),
            ));
    }

    /**
     * @param TblGenerateCertificate $tblGenerateCertificate
     * @param $Field
     *
     * @return false|TblGenerateCertificateSetting
     */
    public function getGenerateCertificateSettingBy(TblGenerateCertificate $tblGenerateCertificate, $Field)
    {
        return (new Data($this->getBinding()))->getGenerateCertificateSettingBy($tblGenerateCertificate, $Field);
    }

    /**
     * @param TblGenerateCertificate $tblGenerateCertificate
     *
     * @return false|TblGenerateCertificateSetting[]
     */
    public function getGenerateCertificateSettingAllByGenerateCertificate(TblGenerateCertificate $tblGenerateCertificate)
    {
        return (new Data($this->getBinding()))->getGenerateCertificateSettingAllByGenerateCertificate($tblGenerateCertificate);
    }

    /**
     * @param TblGenerateCertificate $tblGenerateCertificate
     * @param bool $isString
     *
     * @return false|Type[]|string
     */
    public function getSchoolTypeListFromGenerateCertificate(TblGenerateCertificate $tblGenerateCertificate, bool $isString = false)
    {
        $resultList = array();
        if (($tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate))) {
            foreach ($tblPrepareList as $tblPrepare) {
                if (($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
                    && ($tempList = $tblDivisionCourse->getSchoolTypeListFromStudents())
                ) {
                    $resultList = array_merge($resultList, $tempList);
                }
            }

            $resultList = array_unique($resultList);
        }

        if (empty($resultList)) {
            return false;
        } elseif ($isString) {
            $list = array();
            foreach ($resultList as $item) {
                $list[] = $item->getShortName() ?: $item->getName();
            }
            return implode(", ", $list);
        } else {
            return $resultList;
        }
    }
}