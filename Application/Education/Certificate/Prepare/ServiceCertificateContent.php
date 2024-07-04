<?php

namespace SPHERE\Application\Education\Certificate\Prepare;

use DateTime;
use SPHERE\Application\Api\Education\Certificate\Generator\Repository\GymAbgSekI;
use SPHERE\Application\Education\Absence\Absence;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveComplexExam;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveStudent;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareComplexExam;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareStudent;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Setting\Consumer\Consumer as ConsumerSetting;

abstract class ServiceCertificateContent extends ServiceAbitur
{
    /**
     * @param TblPerson $tblPerson
     * @param TblPrepareStudent|null $tblPrepareStudent
     * @param TblLeaveStudent|null $tblLeaveStudent
     * @param array $Content
     *
     * @return array
     */
    public function createCertificateContent(
        TblPerson $tblPerson,
        ?TblPrepareStudent $tblPrepareStudent = null,
        ?TblLeaveStudent $tblLeaveStudent = null,
        array &$Content = array()
    ): array {
        $personId = $tblPerson->getId();
        $tblStudent = $tblPerson->getStudent();
        $tblConsumer = Consumer::useService()->getConsumerBySession();

        $level = 0;
        $tblSchoolType = false;
        $tblCompany = false;
        $tblCourse = false;
        $tblDivision = false;
        $tblCoreGroup = false;
        $tblYear = false;
        $tblPrepare = false;
        if ($tblPrepareStudent && ($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())) {
            $tblYear = $tblPrepare->getYear();
        } elseif ($tblLeaveStudent) {
            $tblYear = $tblLeaveStudent->getServiceTblYear();
        }
        if ($tblYear && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
            $level = $tblStudentEducation->getLevel();
            $tblSchoolType = $tblStudentEducation->getServiceTblSchoolType();
            $tblCompany = $tblStudentEducation->getServiceTblCompany();
            $tblCourse = $tblStudentEducation->getServiceTblCourse();
            $tblDivision = $tblStudentEducation->getTblDivision();
            $tblCoreGroup = $tblStudentEducation->getTblCoreGroup();
        }

        // Person data
        $Content['P' . $personId]['Person']['Id'] = $tblPerson->getId();
        $Content['P' . $personId]['Person']['Data']['Name']['Salutation'] = $tblPerson->getSalutation();
        $Content['P' . $personId]['Person']['Data']['Name']['First'] = $tblPerson->getFirstSecondName();
        $Content['P' . $personId]['Person']['Data']['Name']['Last'] = $tblPerson->getLastName();
        // letterFontFix
        $Content['P' . $personId]['Person']['Data']['Name']['First'] =
            $this->useLetterFontReplacement($Content['P' . $personId]['Person']['Data']['Name']['First']);
        $Content['P' . $personId]['Person']['Data']['Name']['Last'] =
            $this->useLetterFontReplacement($Content['P' . $personId]['Person']['Data']['Name']['Last']);

        // Person address
        if (($tblAddress = $tblPerson->fetchMainAddress())) {
            $Content['P' . $personId]['Person']['Address']['Street']['Name'] = $tblAddress->getStreetName();
            $Content['P' . $personId]['Person']['Address']['Street']['Number'] = $tblAddress->getStreetNumber();
            $Content['P' . $personId]['Person']['Address']['City']['Code'] = $tblAddress->getTblCity()->getCode();
            $Content['P' . $personId]['Person']['Address']['City']['Name'] = $tblAddress->getTblCity()->getDisplayName();
        }

        // Person Common
        if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))
            && $tblCommonBirthDates = $tblCommon->getTblCommonBirthDates()
        ) {
            $Content['P' . $personId]['Person']['Common']['BirthDates']['Gender'] = ($tblCommonGender = $tblCommonBirthDates->getTblCommonGender()) ? $tblCommonGender->getId() : 0;
            $Content['P' . $personId]['Person']['Common']['BirthDates']['Birthday'] = $tblCommonBirthDates->getBirthday();
            $Content['P' . $personId]['Person']['Common']['BirthDates']['Birthplace'] = $tblCommonBirthDates->getBirthplace() ?: '&nbsp;';
        }

        // Person Parents
        if (($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson))) {
            $mother = false;
            $father = false;
            // Standard false
            $IsTitle = false;
            if(($tblConsumerSetting = ConsumerSetting::useService()->getSetting('Education', 'Certificate', 'Prepare', 'ShowParentTitle'))){
                $IsTitle = $tblConsumerSetting->getValue();
            }
            foreach ($tblRelationshipList as $tblToPerson) {
                if (($tblFromPerson = $tblToPerson->getServiceTblPersonFrom())
                    && $tblToPerson->getServiceTblPersonTo()
                    && $tblToPerson->getTblType()->getName() == 'Sorgeberechtigt'
                    && $tblToPerson->getServiceTblPersonTo()->getId() == $tblPerson->getId()
                ) {
                    if (!isset($Content['P' . $personId]['Person']['Parent']['Mother']['Name'])) {
                        $Content['P' . $personId]['Person']['Parent']['Mother']['Name']['First'] = $tblFromPerson->getFirstSecondName();
                        $Content['P' . $personId]['Person']['Parent']['Mother']['Name']['Last'] = $tblFromPerson->getLastName();
                        $mother = ($IsTitle ? $tblFromPerson->getTitle().' ' : '').
                            $tblFromPerson->getFirstSecondName().' '.$tblFromPerson->getLastName();
                    } elseif (!isset($Content['P' . $personId]['Person']['Parent']['Father']['Name'])) {
                        $Content['P' . $personId]['Person']['Parent']['Father']['Name']['First'] = $tblFromPerson->getFirstSecondName();
                        $Content['P' . $personId]['Person']['Parent']['Father']['Name']['Last'] = $tblFromPerson->getLastName();
                        $father = ($IsTitle ? $tblFromPerson->getTitle().' ' : '').
                            $tblFromPerson->getFirstSecondName().' '.$tblFromPerson->getLastName();
                    }
                }
            }
            // comma decision
            // usage only for "Bildungsempfehlung" (Titel Option for Parent!)
            if($mother && $father){
                $Content['P' . $personId]['Person']['Parent']['CommaSeparated'] = $mother.', '.$father;
            } elseif($mother){
                $Content['P' . $personId]['Person']['Parent']['CommaSeparated'] = $mother;
            } elseif($father) {
                $Content['P' . $personId]['Person']['Parent']['CommaSeparated'] = $father;
            }
        }

        // Abschluss (Bildungsgang), allgemeinbildende Schulen
        if ($tblCourse) {
            if ($level > 6) {
                if ($tblCourse->getName() == 'Hauptschule') {
                    $Content['P' . $personId]['Student']['Course']['Degree'] = 'Hauptschulabschlusses';
                    $Content['P' . $personId]['Student']['Course']['Name'] = 'Hauptschulbildungsgang';
                } elseif ($tblCourse->getName() == 'Realschule') {
                    $Content['P' . $personId]['Student']['Course']['Degree'] = 'Realschulabschlusses';
                    $Content['P' . $personId]['Student']['Course']['Name'] = 'Realschulbildungsgang';
                }
            }
        }

        // Company
        if ($tblCompany) {
            $Content['P' . $personId]['Company']['Id'] = $tblCompany->getId();
            $Content['P' . $personId]['Company']['Data']['Name'] = $tblCompany->getName();
            $Content['P'.$personId]['Company']['Data']['ExtendedName'] = $tblCompany->getExtendedName();
            if (($tblAddress = $tblCompany->fetchMainAddress())) {
                $Content['P' . $personId]['Company']['Address']['Street']['Name'] = $tblAddress->getStreetName();
                $Content['P' . $personId]['Company']['Address']['Street']['Number'] = $tblAddress->getStreetNumber();
                $Content['P' . $personId]['Company']['Address']['City']['Code'] = $tblAddress->getTblCity()->getCode();
                $Content['P' . $personId]['Company']['Address']['City']['Name'] = $tblAddress->getTblCity()->getDisplayName();
            }
        }

        // Arbeitsgemeinschaften
        if ($tblStudent
            && ($tblSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('TEAM'))
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent, $tblSubjectType))
        ) {
            $tempList = array();
            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                if ($tblStudentSubject->getServiceTblSubject()) {
                    $tempList[] = $tblStudentSubject->getServiceTblSubject()->getName();
                }
            }
            if (!empty($tempList)) {
                $Content['P' . $personId]['Subject']['Team'] = implode(', ', $tempList);
            }
        }

        // Fremdsprache ab Klassenstufen anzeigen
        if ($tblStudent
            && ($tblSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent, $tblSubjectType))
        ) {
            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                if ($tblStudentSubject->getServiceTblSubject()
                    && ($levelFrom = $tblStudentSubject->getLevelFrom())
                    && ($tblStudentSubjectRanking = $tblStudentSubject->getTblStudentSubjectRanking())
                ) {
                    $Content['P' . $personId]['Subject']['Level'][$tblStudentSubjectRanking->getIdentifier()] = $levelFrom;
                }
            }
        }

        // Förderschule
        if ($tblStudent && $tblStudentSpecialNeeds = $tblStudent->getTblStudentSpecialNeeds()) {
            if(($tblStudentSpecialNeedsLevel = $tblStudentSpecialNeeds->getTblStudentSpecialNeedsLevel())){
                $Content['P' . $personId]['Student']['StudentSpecialNeeds']['LevelName'] = $tblStudentSpecialNeedsLevel->getName();
            }
        }

        // Berufsfachschulen / Fachschulen
        if($tblStudent && ($tblTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())){
            if(($tblStudentTenseOfLesson = $tblTechnicalSchool->getTblStudentTenseOfLesson())){
                $Content['P' . $personId]['Student']['TenseOfLesson'] = $tblStudentTenseOfLesson->getCertificateName();
            }
            if (($tblTechnicalCourse = $tblTechnicalSchool->getServiceTblTechnicalCourse())) {
                $tblCommonGender = $tblPerson->getGender();
                $Content['P' . $personId]['Student']['TechnicalCourse'] = $tblTechnicalCourse->getDisplayName($tblCommonGender ?: null);
            }
        }

        // Schuljahr
        if ($tblYear) {
            $Content['P' . $personId]['Division']['Data']['Year'] = $tblYear->getName();
        }

        // Klasse bzw. Stammgruppe bzw Klassenstufe
        $Content['P' . $personId]['Division']['Data']['Level']['Name'] = (string) $level;
        if ($tblDivision) {
            $Content['P' . $personId]['Division']['Data']['Name'] = $tblDivision->getName();
            // hänge ein e an die Beschreibung, wenn es noch nicht da ist (Mandant-ESS)
            $Description = $tblDivision->getDescription();
            if($Description != '' && substr($Description, -1) != 'e'){
                $Description .= 'e';
            }
            $Content['P' . $personId]['Division']['Data']['DescriptionWithE'] = $Description;
//        } elseif ($tblCoreGroup) {
//            $Content['P' . $personId]['Division']['Data']['Name'] = $tblCoreGroup->getName();
        } else {
            $Content['P' . $personId]['Division']['Data']['Name'] = $level;
        }
        $course = $level;
        // html funktioniert, allerdings kann es der DOM-PDF nicht, enable utf-8 for domPdf? oder eventuell Schriftart ändern
        // $midTerm = '/&#x2160;';
        $midTerm = '/I';
        if ($tblPrepare
            && ($date = $tblPrepare->getDateTime())
            && ($month = intval($date->format('m')))
            && $month > 3 && $month < 9
        ) {
            // $midTerm = '/&#x2161;';
            $midTerm = '/II';
        }
        $course .= $midTerm;
        $Content['P' . $personId]['Division']['Data']['Course']['Name'] = $course;

        $tblCertificate = false;
        $isGradeVerbal = false;
        if ($tblPrepareStudent) {
            $tblCertificate = $tblPrepareStudent->getServiceTblCertificate();
            if ($tblCertificate) {
                $isGradeVerbal = $tblCertificate->getIsGradeVerbal();
            }
        }

        $tblCertificateType = false;
        if ($tblPrepare
            && ($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())
            && ($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
            && $tblCertificateType->getIdentifier() == 'DIPLOMA'
            && ($tblSetting = ConsumerSetting::useService()->getSetting(
                'Education', 'Certificate', 'Prepare', 'IsGradeVerbalOnDiploma'))
            && $tblSetting->getValue()
        ) {
            $isGradeVerbalOnDiploma = true;
        } else {
            $isGradeVerbalOnDiploma = false;
        }

        // zusätzliche Informationen
        if ($tblPrepare) {
            $tblPrepareInformationList = Prepare::useService()->getPrepareInformationAllByPerson($tblPrepare, $tblPerson);

            // Spezialfall für Förderzeugnisse Lernen
            $isSupportLearningCertificate = false;
            if ($tblPrepareStudent && $tblCertificate) {
                if (strpos($tblCertificate->getCertificate(), 'FsLernen') !== false) {
                    $isSupportLearningCertificate = true;
                }
            }

            if (($tblSetting = ConsumerSetting::useService()->getSetting(
                'Education', 'Certificate', 'Prepare', 'HasRemarkBlocking'
            ))) {
                $hasRemarkBlocking = (boolean) $tblSetting->getValue();
            } else {
                $hasRemarkBlocking = true;
            }

            $tblConsumer = Consumer::useService()->getConsumerBySession();
            if ($tblPrepareInformationList) {
                // Spezialfall Arbeitsgemeinschaften im Bemerkungsfeld
                $team = '';
                $teamChange = '';
                // Spezialfall Wahlbereich im Bemerkungsfeld
                $orientation = '';
                $remark = '';
                $support = '';
                $rating = '';

                foreach ($tblPrepareInformationList as $tblPrepareInformation) {
                    if ($tblPrepareInformation->getField() == 'Team') {
                        if ($tblPrepareInformation->getValue() != '') {
                            $team = 'Arbeitsgemeinschaften: ' . $tblPrepareInformation->getValue();
                            $teamChange = $tblPrepareInformation->getValue();
                        }
                    } elseif ($tblPrepareInformation->getField() == 'Orientation') {
                        if ($tblPrepareInformation->getValue() != '') {
                            $orientation = $tblPrepareInformation->getValue();
                        }
                    } elseif ($tblPrepareInformation->getField() == 'Remark') {
                        $remark = $tblPrepareInformation->getValue();
                    } elseif ($tblPrepareInformation->getField() == 'Transfer') {
                        if ($tblPrepareInformation->getValue() == 'kein Versetzungsvermerk') {
                            // SSW-1380 Spezialfall CSW Grumbach
                        } else {
                            $Value = $tblPerson->getFirstSecondName(). ' ' . $tblPerson->getLastName() . ' ' . $tblPrepareInformation->getValue();
                            $Content['P' . $personId]['Input'][$tblPrepareInformation->getField()] = $this->useLetterFontReplacement($Value);
                        }
                    } elseif ($tblPrepareInformation->getField() == 'IndividualTransfer') {
                        // SSWHD-262
                        if ($tblConsumer && $tblConsumer->isConsumer(TblConsumer::TYPE_SACHSEN, 'ESZC')) {
                            $text = '';
                        } else {
                            $text = $tblPerson->getFirstSecondName() . ' ';
                        }

                        $Content['P' . $personId]['Input'][$tblPrepareInformation->getField()] = $text . $tblPrepareInformation->getValue();
                    } elseif ($isSupportLearningCertificate && $tblPrepareInformation->getField() == 'Support') {
                        $support = $tblPrepareInformation->getValue();
                    } else {
                        $value = $tblPrepareInformation->getValue();
                        // Zensuren in Wortlaut darstellen
                        if (strpos($tblPrepareInformation->getField(), '_Grade')
                            && ($isGradeVerbal
                                || ($tblCertificateType && $tblCertificateType->getIdentifier() == 'DIPLOMA' && $isGradeVerbalOnDiploma))
                        ) {
                            $value = $this->getVerbalGrade($value);
                        }

                        $Content['P' . $personId]['Input'][$tblPrepareInformation->getField()] = $value;
                    }

                    if ($tblPrepareInformation->getField() == 'AddEducation_Average') {
                        $Content['P' . $personId]['Input']['AddEducation_AverageInWord'] = Grade::useService()->getAverageInWord($tblPrepareInformation->getValue());
                    }
                    if($tblPrepareInformation->getField() == 'Rating'){
                        $rating = $tblPrepareInformation->getValue();
                    }
                }

                // rating by Settings -> default value "---" or empty
                if($hasRemarkBlocking && $rating == ''){
                    $Content['P' . $personId]['Input']['Rating'] = '---';
                } else {
                    $Content['P' . $personId]['Input']['Rating'] = $rating;
                }

                if ($orientation) {
                    $team .= ($team != '' ? " \n " : '') . $orientation;
                }

                // Spezialfall für Förderzeugnisse Lernen
                if ($isSupportLearningCertificate) {
                    $remark = ($team ? $team . " \n " : '')
                        . ($support ? 'Inklusive Unterrichtung¹: ' . $support : 'Inklusive Unterrichtung¹: ' . '---' )
                        . " \n " . ($remark ? 'Bemerkung: ' . $remark : '');
                } else {
                    // Streichung leeres Bemerkungsfeld
                    if ($hasRemarkBlocking && $remark == '') {
                        $remark = '---';
                    }

                    if ($team) {
                        if ($tblConsumer && $tblConsumer->isConsumer(TblConsumer::TYPE_SACHSEN, 'EVSR')) {
                            // Arbeitsgemeinschaften am Ende der Bemerkungnen
                            $remark = $remark . " \n\n " . $team;
                        } elseif ($tblConsumer && $tblConsumer->isConsumer(TblConsumer::TYPE_SACHSEN, 'ESZC')
                            && $level <= 4
                        ) {
                            $remark = $teamChange . " \n " . $remark;
                        } elseif ($tblConsumer && $tblConsumer->isConsumer(TblConsumer::TYPE_SACHSEN, 'HOGA')) {
                            $remark = $teamChange . " \n " . $remark;
                        } elseif ($tblConsumer && $tblConsumer->isConsumer(TblConsumer::TYPE_SACHSEN, 'ESBD')) {
                            $remark = $team . " \n " . $remark;
                        } else {
                            $remark = $team . " \n\n " . $remark;
                        }
                    }
                }

                $Content['P' . $personId]['Input']['Remark'] = $remark;
            } else {
                if ($isSupportLearningCertificate) {
                    $Content['P' . $personId]['Input']['Remark'] = 'Inklusive Unterrichtung¹: ---';
                } elseif ($hasRemarkBlocking) {
                    $Content['P' . $personId]['Input']['Remark'] = '---';
                } else {
                    $Content['P' . $personId]['Input']['Remark'] = '';
                }
            }
        }

        // Klassenlehrer
        $tblPersonSigner = false;
        $isDivisionTeacherAvailable = false;
        if ($tblPrepare) {
            if (($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())) {
                $isDivisionTeacherAvailable = $tblGenerateCertificate->isDivisionTeacherAvailable();
                if ($tblPrepareStudent->getServiceTblPersonSigner()) {
                    $tblPersonSigner = $tblPrepareStudent->getServiceTblPersonSigner();
                } else {
                    $tblPersonSigner = $tblPrepare->getServiceTblPersonSigner();
                }
            }
        } elseif ($tblLeaveStudent) {
            $isDivisionTeacherAvailable = true;
            if ($personSignerInformation = $this->getLeaveInformationBy($tblLeaveStudent, 'DivisionTeacher')) {
                $tblPersonSigner = Person::useService()->getPersonById($personSignerInformation->getValue());
            }
        }

        if ($tblPersonSigner && $isDivisionTeacherAvailable) {
            $divisionTeacherDescription = 'Klassenlehrer';
            if ($tblConsumer && $tblConsumer->getType() == TblConsumer::TYPE_SACHSEN) {
                $ConsumerAcronym = $tblConsumer->getAcronym();
                // nur Sachsen
                switch ($ConsumerAcronym) {
                    case 'EVSR':
                        $firstName = $tblPersonSigner->getFirstName();
                        if (strlen($firstName) > 1) {
                            $firstName = substr($firstName, 0, 1) . '.';
                        }
                        $Content['P' . $personId]['DivisionTeacher']['Name'] = $firstName . ' ' . $tblPersonSigner->getLastName();
                        break;
                    case 'ESZC':
                        $Content['P' . $personId]['DivisionTeacher']['Name'] = trim($tblPersonSigner->getSalutation() . " " . $tblPersonSigner->getLastName());
                        break;
                    case 'EVSC':
                    case 'EMSP':
                        $Content['P' . $personId]['DivisionTeacher']['Name'] = trim($tblPersonSigner->getFirstName() . " " . $tblPersonSigner->getLastName());
                        $divisionTeacherDescription = 'Klassenleiter';
                        break;
                    case 'EGE':
                        $Content['P'.$personId]['DivisionTeacher']['Name'] = $tblPersonSigner->getFullName();
                        if ($level < 9
                            && $tblSchoolType
                            && $tblSchoolType->getName() == 'Mittelschule / Oberschule'
                        ) {
                            $divisionTeacherDescription = 'Gruppenleiter';
                        }
                        break;
                    case 'EVAMTL':
                        $Content['P'.$personId]['DivisionTeacher']['Name'] = $tblPersonSigner->getFullName();
                        if ($tblSchoolType
                            && $tblSchoolType->getName() != 'Grundschule'
                        ){
                            $divisionTeacherDescription = 'Mentor';
                        }
                        break;
                    case 'CSW':
                        if ($tblSchoolType
                            && $tblSchoolType->getName() == 'Mittelschule / Oberschule'
                        ) {
                            $Content['P' . $personId]['DivisionTeacher']['Name'] = $tblPersonSigner->getFirstSecondName() . ' ' . $tblPersonSigner->getLastName();
                        } else {
                            $Content['P'.$personId]['DivisionTeacher']['Name'] = $tblPersonSigner->getFullName();
                        }
                        break;
                    case 'FESH':
                    case 'ESS':
                    case 'ESBD':
                    case 'WVSZ':
                        $Content['P' . $personId]['DivisionTeacher']['Name'] = trim($tblPersonSigner->getFirstName() . " " . $tblPersonSigner->getLastName());
                        break;
                    default:
                        $Content['P'.$personId]['DivisionTeacher']['Name'] = $tblPersonSigner->getFullName();
                        break;
                }
            } else {
                $Content['P'.$personId]['DivisionTeacher']['Name'] = $tblPersonSigner->getFullName();
            }

            // Spezialfall: alle Klassenlehrer aus der Klassenverwaltung
            if (Consumer::useService()->getConsumerBySessionIsConsumer(TblConsumer::TYPE_SACHSEN, 'EVSC')
                && $tblPrepare
                && ($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
                && ($tblDivisionTeacherList = $tblDivisionCourse->getDivisionTeacherList())
            ) {
                $hasMultipleTeachers = count($tblDivisionTeacherList) > 1;

                $names = array();
                $description = $divisionTeacherDescription;
                foreach ($tblDivisionTeacherList as $tblTeacher) {
                    $names[] = trim($tblTeacher->getFirstName() . " " . $tblTeacher->getLastName());

                    if (!$hasMultipleTeachers) {
                        if (($genderValueTeacher = $this->getGenderByPerson($tblTeacher))
                            && $genderValueTeacher == 'F'
                        ) {
                            $description = $divisionTeacherDescription . 'in';
                        }
                    }
                }

                $Content['P'.$personId]['DivisionTeacherList']['Name'] = implode(', ' , $names);
                $Content['P' . $personId]['DivisionTeacherList']['Description'] = $description;
            }

            if (($genderValue = $this->getGenderByPerson($tblPersonSigner))) {
                $Content['P' . $personId]['DivisionTeacher']['Gender'] = $genderValue;
                if ($genderValue == 'M') {
                    $Content['P' . $personId]['DivisionTeacher']['Description'] = $divisionTeacherDescription;
                    $Content['P' . $personId]['Tudor']['Description'] = 'Tutor';
                    $Content['P' . $personId]['Leader']['Description'] = 'Vorsitzender des Prüfungsausschusses';
                } elseif ($genderValue == 'F') {
                    $Content['P' . $personId]['DivisionTeacher']['Description'] = $divisionTeacherDescription . 'in';
                    $Content['P' . $personId]['Tudor']['Description'] = 'Tutorin';
                    $Content['P' . $personId]['Leader']['Description'] = 'Vorsitzende des Prüfungsausschusses';
                }
            }
        }

        // Schulleitung
        if ($tblPrepare && ($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())) {
            if ($tblGenerateCertificate->getHeadmasterName()
                && $tblGenerateCertificate) {
                $Content['P' . $personId]['Headmaster']['Name'] = $tblGenerateCertificate->getHeadmasterName();
            }
            if (($tblCommonGender = $tblGenerateCertificate->getServiceTblCommonGenderHeadmaster())
                && $tblGenerateCertificate->isDivisionTeacherAvailable()) {
                if ($tblCommonGender->getName() == 'Männlich') {
                    $Content['P' . $personId]['Headmaster']['Description'] = 'Schulleiter';
                } elseif ($tblCommonGender->getName() == 'Weiblich') {
                    $Content['P' . $personId]['Headmaster']['Description'] = 'Schulleiterin';
                }
            }
        }

        if ($tblPrepare) {
            // Kopfnoten
            $tblPrepareGradeBehaviorList = Prepare::useService()->getBehaviorGradeAllByPrepareCertificateAndPerson(
                $tblPrepare,
                $tblPerson
            );
            if ($tblPrepareGradeBehaviorList) {
                foreach ($tblPrepareGradeBehaviorList as $tblPrepareGrade) {
                    if ($tblPrepareGrade->getServiceTblGradeType()) {
                        if ($isGradeVerbal) {
                            $grade = $this->getVerbalGrade($tblPrepareGrade->getGrade());
                            $Content['P' . $personId]['Grade']['Data']['IsShrinkSize'][$tblPrepareGrade->getServiceTblGradeType()->getCode()] = true;
                        } else {
                            $grade = $tblPrepareGrade->getGrade();
                        }

                        $Content['P' . $personId]['Input'][$tblPrepareGrade->getServiceTblGradeType()->getCode()] = $grade;
                    }
                }
            }
            // Kopfnoten von Fachlehrern für Noteninformation
            if ($tblPrepare->isGradeInformation()
                && ($tblBehaviorTask = $tblPrepare->getServiceTblBehaviorTask())
                && ($tblTaskGradeList = Grade::useService()->getTaskGradeListByTaskAndPerson($tblBehaviorTask, $tblPerson))
            ) {
                foreach ($tblTaskGradeList as $tblTaskGrade) {
                    if (($tblSubject = $tblTaskGrade->getServiceTblSubject())
                        && ($tblGradeType = $tblTaskGrade->getTblGradeType())
                    ) {
                        $Content['P' . $personId]['Input']['BehaviorTeacher'][$tblSubject->getAcronym()][$tblGradeType->getCode()] = $tblTaskGrade->getDisplayGrade();
                    }
                }
            }

            // Fachnoten
            // Abschlusszeugnisse mit Extra Prüfungen, aktuell nur Fachoberschule und Oberschule
            $examGradeList = array();
            $gradeListFOS = array();
            if ($tblCertificateType
                && $tblCertificateType->getIdentifier() == 'DIPLOMA'
                && $tblSchoolType
                && ($tblSchoolType->getShortName() == 'FOS' || $tblSchoolType->getShortName() == 'OS' || $tblSchoolType->getShortName() == 'BFS')
            ) {
                // Abiturnoten werden direkt im Certificate in der API gedruckt
                if (($tblPrepareAdditionalGradeType = $this->getPrepareAdditionalGradeTypeByIdentifier('EN'))
                    && ($tblPrepareAdditionalGradeList = $this->getPrepareAdditionalGradeListBy(
                        $tblPrepare, $tblPerson, $tblPrepareAdditionalGradeType
                    ))
                ) {
                    foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                        if (($tblSubject = $tblPrepareAdditionalGrade->getServiceTblSubject())
                            && $tblPrepareAdditionalGrade->getGrade() !== ''
                        ) {
                            $examGradeList[$tblSubject->getId()] = $tblSubject;
                            if ($isGradeVerbalOnDiploma) {
                                $grade = $this->getVerbalGrade($tblPrepareAdditionalGrade->getGrade());
                                if ($tblConsumer && !$tblConsumer->isConsumer(TblConsumer::TYPE_SACHSEN, 'EZSH')) {
                                    $Content['P' . $personId]['Grade']['Data']['IsShrinkSize'][$tblSubject->getAcronym()] = true;
                                }
                            } else {
                                $grade = $tblPrepareAdditionalGrade->getGrade();
                                if ((Grade::useService()->getGradeTextByName($grade))
                                    && $tblConsumer && !$tblConsumer->isConsumer(TblConsumer::TYPE_SACHSEN, 'EZSH')
                                    && $grade != '&ndash;'
//                                        && $grade != 'befreit'
                                ) {
                                    $Content['P' . $personId]['Grade']['Data']['IsShrinkSize'][$tblSubject->getAcronym()] = true;
                                }
                            }

                            // Fachoberschule FHR - Durchschnittsnote berechnen
                            if ($tblSchoolType && $tblSchoolType->getShortName() == 'FOS' && $tblPrepareAdditionalGrade->getGrade()
                                && intval($tblPrepareAdditionalGrade->getGrade())
                            ) {
                                if ($tblConsumer && $tblConsumer->isConsumer(TblConsumer::TYPE_SACHSEN, 'HOGA')) {
                                    // Sport und die Facharbeit werden bei der Berechnung der Durchschnittsnote nicht berücksichtigt
                                    if (strpos($tblSubject->getName(), 'Sport') === false && strpos($tblSubject->getName(), 'Facharbeit') === false) {
                                        $gradeListFOS[] = $tblPrepareAdditionalGrade->getGrade();
                                    }
                                } else {
                                    // die Fussnote bei C.01.09 hat sich geändert, es werden jetzt alle Fächer berücksichtigt
                                    $gradeListFOS[] = $tblPrepareAdditionalGrade->getGrade();
                                }
                            }

                            $Content['P' . $personId]['Grade']['Data'][$tblSubject->getAcronym()] = $grade;
                        }
                    }
                }
            }

            // Fachnoten restliche Zeugnisse
            if (($tblAppointedDateTask = $tblPrepare->getServiceTblAppointedDateTask())
                && $tblCertificate
            ) {
                if (($tblTaskGradeList = Grade::useService()->getTaskGradeListByTaskAndPerson($tblAppointedDateTask, $tblPerson))) {
                    foreach ($tblTaskGradeList as $tblTaskGrade) {
                        if (($tblSubjectTemp = $tblTaskGrade->getServiceTblSubject())) {
                            // bei Abschlusszeugnissen existiert eine Endnote
                            if (isset($examGradeList[$tblSubjectTemp->getId()])) {
                                continue;
                            }

                            // leere Zensuren bei Zeugnissen ignorieren, bei optionalen Zeugnisfächern
                            if ($tblTaskGrade->getGrade() === null && $tblTaskGrade->getTblGradeText() == null) {
                                continue;
                            }

                            // Fachoberschule FHR - Durchschnittsnote berechnen
                            if ($tblSchoolType && $tblSchoolType->getShortName() == 'FOS' && $tblTaskGrade->getGradeNumberValue() !== null) {
                                if ($tblConsumer && $tblConsumer->isConsumer(TblConsumer::TYPE_SACHSEN, 'HOGA')) {
                                    // Sport und die Facharbeit werden bei der Berechnung der Durchschnittsnote nicht berücksichtigt
                                    if (strpos($tblSubjectTemp->getName(), 'Sport') === false && strpos($tblSubjectTemp->getName(), 'Facharbeit') === false) {
                                        $gradeListFOS[] = $tblTaskGrade->getGradeNumberValue();
                                    }
                                } else {
                                    // die Fussnote bei C.01.09 hat sich geändert, es werden jetzt alle Fächer berücksichtigt
                                    $gradeListFOS[] = $tblTaskGrade->getGradeNumberValue();
                                }
                            }

                            // Zensuren im Wortlaut
                            if ($isGradeVerbal
                                // Abschlusszeugnisse für Berufsfachschule und Fachschule: Zensuren kommen direkt aus dem Notenauftrag
                                || ($tblCertificateType && $tblCertificateType->getIdentifier() == 'DIPLOMA' && $isGradeVerbalOnDiploma)
                            ) {
                                if ($tblTaskGrade->getTblGradeText()) {
                                    $Content['P' . $personId]['Grade']['Data'][$tblSubjectTemp->getAcronym()] = $tblTaskGrade->getTblGradeText()->getName();
                                } else {
                                    $Content['P' . $personId]['Grade']['Data'][$tblSubjectTemp->getAcronym()] = $this->getVerbalGrade($tblTaskGrade->getDisplayGrade(false,
                                        $tblCertificate));
                                    $Content['P' . $personId]['Grade']['Data']['IsShrinkSize'][$tblSubjectTemp->getAcronym()] = true;
                                }
                            } else {
                                $Content['P' . $personId]['Grade']['Data'][$tblSubjectTemp->getAcronym()] = $tblTaskGrade->getDisplayGrade(false,
                                    $tblCertificate);
                            }

                            // bei Zeugnistext als Note Schriftgröße verkleinern
                            if ($tblTaskGrade->getTblGradeText()
                                && $tblTaskGrade->getTblGradeText()->getName() != '&ndash;'
                            ) {
                                $Content['P' . $personId]['Grade']['Data']['IsShrinkSize'][$tblSubjectTemp->getAcronym()] = true;
                            }
                        }
                    }
                }

                // Standard Zeugnistext aus der Stundentafel
                if ($level
                    && $tblSchoolType
                    && ($tblSubjectTableList = DivisionCourse::useService()->getSubjectTableListBy($tblSchoolType, $level))
                ) {
                    foreach ($tblSubjectTableList as $tblSubjectTable) {
                        if (($tblGradeText = $tblSubjectTable->getServiceTblGradeText())
                            && ($tblSubjectFromSubjectTable = $tblSubjectTable->getServiceTblSubject())
                            && (!isset($Content['P' . $personId]['Grade']['Data'][$tblSubjectFromSubjectTable->getAcronym()]))
                        ) {
                            $Content['P' . $personId]['Grade']['Data'][$tblSubjectFromSubjectTable->getAcronym()] = $tblGradeText->getName();
                            if ($tblGradeText->getName() != '&ndash;') {
                                $Content['P' . $personId]['Grade']['Data']['IsShrinkSize'][$tblSubjectFromSubjectTable->getAcronym()] = true;
                            }
                        }
                    }
                }
            }

            if ($gradeListFOS) {
                $Content = $this->setCalcValueFOS($gradeListFOS, $Content, $tblPerson);
            }

            // Fachnoten von abgewählten Fächern vom Vorjahr
            if (($tblPrepareAdditionalGradeType = $this->getPrepareAdditionalGradeTypeByIdentifier('PRIOR_YEAR_GRADE'))
                && ($tblPrepareAdditionalGradeList = $this->getPrepareAdditionalGradeListBy($tblPrepare, $tblPerson, $tblPrepareAdditionalGradeType))
            ) {
                foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                    if (($tblSubject = $tblPrepareAdditionalGrade->getServiceTblSubject())) {
                        if ($isGradeVerbalOnDiploma
                            || (Grade::useService()->getGradeTextByName($tblPrepareAdditionalGrade->getGrade()) && $tblPrepareAdditionalGrade->getGrade() != '&ndash;')
                        ) {
                            $grade = $this->getVerbalGrade($tblPrepareAdditionalGrade->getGrade());
                            $Content['P' . $personId]['AdditionalGrade']['Data']['IsShrinkSize'][$tblSubject->getAcronym()] = true;
                        } else {
                            $grade = $tblPrepareAdditionalGrade->getGrade();
                        }

                        $Content['P' . $personId]['AdditionalGrade']['Data'][$tblSubject->getAcronym()] = $grade;
                    }
                }
            }

            // Komplexprüfungen für Fachschule Abschlusszeugnisse
            if (($tblPrepareComplexExamList = Prepare::useService()->getPrepareComplexExamAllByPrepareStudent($tblPrepareStudent))) {
                $countInformationalExpulsion = 1;
                $subjectList = array();
                foreach ($tblPrepareComplexExamList as $tblPrepareComplexExam) {
                    $identifier = $tblPrepareComplexExam->getIdentifier();
                    $ranking = $tblPrepareComplexExam->getRanking();

                    $subjects = '';
                    $tblFirstSubject = $tblPrepareComplexExam->getServiceTblFirstSubject();
                    $tblSecondSubject = $tblPrepareComplexExam->getServiceTblSecondSubject();
                    $preText = $identifier == TblPrepareComplexExam::IDENTIFIER_WRITTEN ? 'K' . $ranking . '&nbsp;&nbsp;' : '';
                    if ($tblFirstSubject || $tblSecondSubject) {
                        $subjects .= $preText
                            . ($tblFirstSubject ? $tblFirstSubject->getTechnicalAcronymForCertificateFromName() : '')
                            . ($tblFirstSubject && $tblSecondSubject ? ' / ' : '')
                            . ($tblSecondSubject ? $tblSecondSubject->getTechnicalAcronymForCertificateFromName() : '');
                    }

                    if ($isGradeVerbalOnDiploma) {
                        $grade = $this->getVerbalGrade($tblPrepareComplexExam->getGrade());
                    } else {
                        $grade = $tblPrepareComplexExam->getGrade();
                    }
                    $Content['P' . $personId]['ExamList'][$identifier][$ranking]['Subjects'] = $subjects;
                    $Content['P' . $personId]['ExamList'][$identifier][$ranking]['Grade'] = $grade;

                    // Nachrichtliche Ausweisung
                    if ($tblFirstSubject && !isset($subjectList[$tblFirstSubject->getId()])) {
                        $subjectList[$tblFirstSubject->getId()] = $tblFirstSubject;
                        $text = $preText . $tblFirstSubject->getName();
                        $Content['P' . $personId]['InformationalExpulsion'][$countInformationalExpulsion] = $text;
                        if (strlen($text) > 90) {
                            // Fachname nimmt 2 Zeilen ein
                            $Content['P' . $personId]['InformationalExpulsion']['HasTwoRows' . $countInformationalExpulsion] = strlen($text);
                        }
                        $countInformationalExpulsion++;
                    }
                    if ($tblSecondSubject && !isset($subjectList[$tblSecondSubject->getId()])) {
                        $subjectList[$tblSecondSubject->getId()] = $tblSecondSubject;
                        $text = $preText . $tblSecondSubject->getName();
                        $Content['P' . $personId]['InformationalExpulsion'][$countInformationalExpulsion] = $text;
                        if (strlen($text) > 90) {
                            // Fachname nimmt 2 Zeilen ein
                            $Content['P' . $personId]['InformationalExpulsion']['HasTwoRows' . $countInformationalExpulsion] = strlen($text);
                        }
                        $countInformationalExpulsion++;
                    }
                }
            }
        }

        // Fehlzeiten
        if ($tblPrepareStudent) {
            if (($tblSettingAbsence = ConsumerSetting::useService()->getSetting(
                'Education', 'ClassRegister', 'Absence', 'UseClassRegisterForAbsence'))
            ) {
                $useClassRegisterForAbsence = $tblSettingAbsence->getValue();
            } else {
                $useClassRegisterForAbsence = false;
            }

            $excusedDays = $tblPrepareStudent->getExcusedDays();
            $unexcusedDays = $tblPrepareStudent->getUnexcusedDays();

            if ($useClassRegisterForAbsence && $tblYear) {
                // Fehlzeiten werden im Klassenbuch gepflegt
                if (($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())
                    && $tblGenerateCertificate->getAppointedDateForAbsence()
                ) {
                    $tillDateAbsence = new DateTime($tblGenerateCertificate->getAppointedDateForAbsence());
                } else {
                    $tillDateAbsence = new DateTime($tblPrepare->getDate());
                }
                list($startDateAbsence) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);

                if ($excusedDays === null) {
                    $excusedDays = Absence::useService()->getExcusedDaysByPerson($tblPerson, $tblYear, $tblCompany ?: null, $tblSchoolType ?: null,
                        $startDateAbsence, $tillDateAbsence);
                }
                if ($unexcusedDays === null) {
                    $unexcusedDays = Absence::useService()->getUnexcusedDaysByPerson($tblPerson, $tblYear, $tblCompany ?: null, $tblSchoolType ?: null,
                        $startDateAbsence, $tillDateAbsence);
                }

                // Zusatztage für die fehlenden Unterrichtseinheiten addieren
                $excusedDays += $tblPrepareStudent->getExcusedDaysFromLessons() ?: 0;
                $unexcusedDays += $tblPrepareStudent->getUnexcusedDaysFromLessons() ?: 0;
            }
            $Content['P' . $personId]['Input']['Missing'] = $excusedDays;
            $Content['P' . $personId]['Input']['Bad']['Missing'] = $unexcusedDays;
            $Content['P' . $personId]['Input']['Total']['Missing'] = $excusedDays + $unexcusedDays;
        }

        // Zeugnisdatum
        if ($tblPrepare) {
            $Content['P' . $personId]['Input']['Date'] = $tblPrepare->getDate();
        }

        if ($tblPrepareStudent) {
            if ($tblCertificate && $tblCertificate->getName() == 'Bildungsempfehlung') {
                // Notendurchschnitt der angegebenen Fächer für Bildungsempfehlung
                $average = $this->calcSubjectGradesAverage($tblPrepareStudent);
                if ($average) {
                    $Content['P' . $personId]['Grade']['Data']['Average'] = number_format($average, 1, ',', '.');
                    //str_replace('.', ',', $average);
                }

                // Notendurchschnitt aller anderen Fächer für Bildungsempfehlung
                $average = $this->calcSubjectGradesAverageOthers($tblPrepareStudent);
                if ($average) {
                    $Content['P' . $personId]['Grade']['Data']['AverageOthers'] = number_format($average, 1, ',', '.');
                }
            }
        }

        // Abgangszeugnisse
        if ($tblLeaveStudent) {
            if (($tblSetting = ConsumerSetting::useService()->getSetting(
                'Education', 'Certificate', 'Prepare', 'IsGradeVerbalOnLeave'
            ))) {
                $isGradeVerbalOnLeave = $tblSetting->getValue();
            } else {
                $isGradeVerbalOnLeave = false;
            }

            if (($tblLeaveGradeList = $this->getLeaveGradeAllByLeaveStudent($tblLeaveStudent))) {
                foreach ($tblLeaveGradeList as $tblLeaveGrade) {
                    if (($tblSubject = $tblLeaveGrade->getServiceTblSubject())) {
                        if ($isGradeVerbalOnLeave) {
                            $grade = $this->getVerbalGrade($tblLeaveGrade->getGrade());
                            $Content['P' . $personId]['Grade']['Data']['IsShrinkSize'][$tblSubject->getAcronym()] = true;
                        } else {
                            // bei Zeugnistext als Note Schriftgröße verkleinern
                            if (Grade::useService()->getGradeTextByName($tblLeaveGrade->getGrade())
                                && $tblLeaveGrade->getGrade() != '&ndash;'
//                                && $tblLeaveGrade->getGrade() != 'befreit'
                            ) {
                                $Content['P' . $personId]['Grade']['Data']['IsShrinkSize'][$tblLeaveGrade->getServiceTblSubject()->getAcronym()] = true;
                            }
                            $grade = $tblLeaveGrade->getGrade();
                        }

                        $Content['P' . $personId]['Grade']['Data'][$tblSubject->getAcronym()] = $grade;
                    }
                }
            }

            // Gleichgestellter Schulabschluss - GymAbgSekI, MsAbg
            if (($tblLeaveInformationEqualGraduation = $this->getLeaveInformationBy($tblLeaveStudent, 'EqualGraduation'))) {
                if ($tblLeaveInformationEqualGraduation->getValue() == GymAbgSekI::COURSE_RS) {
                    $Content['P' . $personId]['Input']['EqualGraduation']['RS'] = true;
                } elseif ($tblLeaveInformationEqualGraduation->getValue() == GymAbgSekI::COURSE_HS) {
                    $Content['P' . $personId]['Input']['EqualGraduation']['HS'] = true;
                } elseif ($tblLeaveInformationEqualGraduation->getValue() == GymAbgSekI::COURSE_HSQ) {
                    $Content['P' . $personId]['Input']['EqualGraduation']['HSQ'] = true;
                } elseif ($tblLeaveInformationEqualGraduation->getValue() == GymAbgSekI::COURSE_LERNEN) {
                    $Content['P' . $personId]['Input']['EqualGraduation']['LERNEN'] = true;
                }
            }

            // Bemerkungen
            $remark = '---';
            if (($tblLeaveInformationRemark = $this->getLeaveInformationBy($tblLeaveStudent, 'Remark'))) {
                $remark = $tblLeaveInformationRemark->getValue() ?: $remark;
            }
            $Content['P' . $personId]['Input']['Remark'] = $remark;

            // Inklusive Unterrichtung
            $support = '---';
            if (($tblLeaveInformationSupport = $this->getLeaveInformationBy($tblLeaveStudent, 'Support'))) {
                $support = $tblLeaveInformationSupport->getValue() ?: $remark;
            }
            $Content['P' . $personId]['Input']['Support'] = $support;

            $remarkWithoutTeam = '---';
            if (($tblLeaveInformationRemarkWithoutTeam = $this->getLeaveInformationBy($tblLeaveStudent, 'RemarkWithoutTeam'))) {
                $remarkWithoutTeam = $tblLeaveInformationRemarkWithoutTeam->getValue() ?: $remarkWithoutTeam;
            }
            $Content['P' . $personId]['Input']['RemarkWithoutTeam'] = $remarkWithoutTeam;

            $arrangement = '---';
            if (($tblLeaveInformationArrangement = $this->getLeaveInformationBy($tblLeaveStudent, 'Arrangement'))) {
                $arrangement = $tblLeaveInformationArrangement->getValue() ?: $arrangement;
            }
            $Content['P' . $personId]['Input']['Arrangement'] = $arrangement;

            // Zeugnisdatum
            if (($tblLeaveInformationCertificateDate = $this->getLeaveInformationBy($tblLeaveStudent, 'CertificateDate'))) {
                $Content['P' . $personId]['Input']['Date'] = $tblLeaveInformationCertificateDate->getValue();
                $certificateDate = new DateTime($tblLeaveInformationCertificateDate->getValue());
                $Content['P' . $personId]['Leave']['CalcEducationDateFrom'] = (new DateTime('01.08.' . ($certificateDate->format('Y') - 2)))->format('d.m.Y');
            }

            // Headmaster
            if (($tblLeaveInformationHeadmasterName = $this->getLeaveInformationBy($tblLeaveStudent, 'HeadmasterName'))) {
                $Content['P' . $personId]['Headmaster']['Name'] = $tblLeaveInformationHeadmasterName->getValue();
            }
            if (($tblLeaveInformationHeadmasterGender = $this->getLeaveInformationBy($tblLeaveStudent, 'HeadmasterGender'))) {
                if (($tblCommonGender = Common::useService()->getCommonGenderById($tblLeaveInformationHeadmasterGender->getValue()))) {
                    if ($tblCommonGender->getName() == 'Männlich') {
                        $Content['P' . $personId]['Headmaster']['Description'] = 'Schulleiter';
                    } elseif ($tblCommonGender->getName() == 'Weiblich') {
                        $Content['P' . $personId]['Headmaster']['Description'] = 'Schulleiterin';
                    }
                }
            }

            // Tudor
            if (($tblLeaveInformationTudorName = $this->getLeaveInformationBy($tblLeaveStudent, 'TudorName'))) {
                $Content['P' . $personId]['DivisionTeacher']['Name'] = $tblLeaveInformationTudorName->getValue();
            }
            if (($tblLeaveInformationTudorGender = $this->getLeaveInformationBy($tblLeaveStudent, 'TudorGender'))) {
                if (($tblCommonGender = Common::useService()->getCommonGenderById($tblLeaveInformationTudorGender->getValue()))) {
                    if ($tblCommonGender->getName() == 'Männlich') {
                        $Content['P' . $personId]['Tudor']['Description'] = 'Tutor';
                    } elseif ($tblCommonGender->getName() == 'Weiblich') {
                        $Content['P' . $personId]['Tudor']['Description'] = 'Tutorin';
                    }
                }
            }

            // weitere Felder (Berufsfachschulen && Fachschulen)
            if (($tblLeaveInformationList = $this->getLeaveInformationAllByLeaveStudent($tblLeaveStudent))) {
                foreach ($tblLeaveInformationList as $tblLeaveInformation) {
                    if (($field = $tblLeaveInformation->getField())
                        && !isset($Content['P' . $personId]['Input'][$field])
                    ) {
                        $value = $tblLeaveInformation->getValue();
                        // Zensuren in Wortlaut darstellen (Abgangszeugnis Fachschule)
                        if ($isGradeVerbalOnLeave && strpos($field, '_Grade')) {
                            $value = $this->getVerbalGrade($value);
                        }

                        $Content['P' . $personId]['Input'][$field] = $value;
                    }
                }
            }

            // Komplexprüfungen für Fachschule Abgangszeugnis
            if (($tblLeaveComplexExamList = Prepare::useService()->getLeaveComplexExamAllByLeaveStudent($tblLeaveStudent))) {
                $countInformationalExpulsion = 1;
                $subjectList = array();
                foreach ($tblLeaveComplexExamList as $tblLeaveComplexExam) {
                    $identifier = $tblLeaveComplexExam->getIdentifier();
                    $ranking = $tblLeaveComplexExam->getRanking();

                    $subjects = '';
                    $tblFirstSubject = $tblLeaveComplexExam->getServiceTblFirstSubject();
                    $tblSecondSubject = $tblLeaveComplexExam->getServiceTblSecondSubject();
                    $preText = $identifier == TblLeaveComplexExam::IDENTIFIER_WRITTEN ? 'K' . $ranking . '&nbsp;&nbsp;' : '';
                    if ($tblFirstSubject || $tblSecondSubject) {
                        $subjects .= $preText
                            . ($tblFirstSubject ? $tblFirstSubject->getTechnicalAcronymForCertificateFromName() : '')
                            . ($tblFirstSubject && $tblSecondSubject ? ' / ' : '')
                            . ($tblSecondSubject ? $tblSecondSubject->getTechnicalAcronymForCertificateFromName() : '');
                    }

                    if ($isGradeVerbalOnLeave) {
                        $grade = $this->getVerbalGrade($tblLeaveComplexExam->getGrade());
                    } else {
                        $grade = $tblLeaveComplexExam->getGrade();
                    }
                    $Content['P' . $personId]['ExamList'][$identifier][$ranking]['Subjects'] = $subjects;
                    $Content['P' . $personId]['ExamList'][$identifier][$ranking]['Grade'] = $grade;

                    // Nachrichtliche Ausweisung
                    if ($tblFirstSubject && !isset($subjectList[$tblFirstSubject->getId()])) {
                        $subjectList[$tblFirstSubject->getId()] = $tblFirstSubject;
                        $text = $preText . $tblFirstSubject->getName();
                        $Content['P' . $personId]['InformationalExpulsion'][$countInformationalExpulsion] = $text;
                        if (strlen($text) > 90) {
                            // Fachname nimmt 2 Zeilen ein
                            $Content['P' . $personId]['InformationalExpulsion']['HasTwoRows' . $countInformationalExpulsion] = strlen($text);
                        }
                        $countInformationalExpulsion++;
                    }
                    if ($tblSecondSubject && !isset($subjectList[$tblSecondSubject->getId()])) {
                        $subjectList[$tblSecondSubject->getId()] = $tblSecondSubject;
                        $text = $preText . $tblSecondSubject->getName();
                        $Content['P' . $personId]['InformationalExpulsion'][$countInformationalExpulsion] = $text;
                        if (strlen($text) > 90) {
                            // Fachname nimmt 2 Zeilen ein
                            $Content['P' . $personId]['InformationalExpulsion']['HasTwoRows' . $countInformationalExpulsion] = strlen($text);
                        }
                        $countInformationalExpulsion++;
                    }
                }
            }
        }

        return $Content;
    }

    /**
     * @param $grade
     *
     * @return string
     */
    private function getVerbalGrade($grade): string
    {
        switch ($grade) {
            case 1 : return 'sehr gut';
            case 2 : return 'gut';
            case 3 : return 'befriedigend';
            case 4 : return 'ausreichend';
            case 5 : return 'mangelhaft';
            case 6 : return 'ungenügend';
        }

        return $grade;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return null|string
     * male -> M | female -> F | nothing -> false
     */
    private function getGenderByPerson(TblPerson $tblPerson)
    {
        $return = false;
        if (($tblCommonTeacher = $tblPerson->getCommon())) {
            if (($tblCommonBirthDates = $tblCommonTeacher->getTblCommonBirthDates())) {
                if (($tblCommonGenderTeacher = $tblCommonBirthDates->getTblCommonGender())) {
                    if ($tblCommonGenderTeacher->getName() == 'Männlich') {
                        $return = 'M';
                    } else {
                        $return = 'F';
                    }
                }
            }
        }

        if (!$return) {
            if ($tblPerson->getSalutation() == 'Herr') {
                $return = 'M';
            } elseif ($tblPerson->getSalutation() == 'Frau') {
                $return = 'F';
            }
        }

        return $return;
    }

    /**
     * @param array $gradeListFOS
     * @param array $Content
     * @param TblPerson $tblPerson
     *
     * @return array
     */
    private function setCalcValueFOS(array $gradeListFOS, array $Content, TblPerson $tblPerson): array
    {
        $calcValueFOS = round(floatval(array_sum($gradeListFOS)) / count($gradeListFOS), 1);
        $calcValueFOS = str_replace('.', ',', $calcValueFOS);
        $Content['P' . $tblPerson->getId()]['Calc']['AddEducation_Average'] = $calcValueFOS;
        $Content['P' . $tblPerson->getId()]['Calc']['AddEducation_AverageInWord'] = Grade::useService()->getAverageInWord($calcValueFOS);

        return $Content;
    }

    /**
     * @param TblPrepareStudent $tblPrepareStudent
     *
     * @return bool|float
     */
    private function calcSubjectGradesAverage(TblPrepareStudent $tblPrepareStudent)
    {
        if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
            && ($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
            && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
            && ($tblAppointedDateTask = $tblPrepare->getServiceTblAppointedDateTask())
        ) {
            $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($tblCertificate);

            if ($tblCertificateSubjectAll) {
                $gradeList = array();
                foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                    if (($tblSubject = $tblCertificateSubject->getServiceTblSubject())) {
                        if (($tblTaskGrade = Grade::useService()->getTaskGradeByPersonAndTaskAndSubject($tblPerson, $tblAppointedDateTask, $tblSubject))
                            && $tblTaskGrade->getIsGradeNumeric()
                        ) {
                            $gradeList[] = $tblTaskGrade->getGradeNumberValue();
                        }
                    }
                }

                if (!empty($gradeList)) {
                    return round(floatval(array_sum($gradeList) / count($gradeList)), 1);
                }
            }
        }

        return false;
    }

    /**
     * @param TblPrepareStudent $tblPrepareStudent
     *
     * @return bool|float
     */
    private function calcSubjectGradesAverageOthers(TblPrepareStudent $tblPrepareStudent)
    {
        if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
            && ($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
            && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
            && ($tblAppointedDateTask = $tblPrepare->getServiceTblAppointedDateTask())
        ) {
            $gradeList = array();
            if (($tblTaskGradeList = Grade::useService()->getTaskGradeListByTaskAndPerson($tblAppointedDateTask, $tblPerson))) {
                foreach ($tblTaskGradeList as $tblTaskGrade) {
                    if ($tblTaskGrade->getIsGradeNumeric()
                        && ($tblSubject = $tblTaskGrade->getServiceTblSubject())
                        // Fach ist nicht auf der Zeugnisvorlage
                        && !Generator::useService()->getCertificateSubjectBySubject($tblCertificate, $tblSubject)
                    ) {
                        $gradeList[] = $tblTaskGrade->getGradeNumberValue();
                    }
                }

            }

            if (!empty($gradeList)) {
                return round(floatval(array_sum($gradeList) / count($gradeList)), 1);
            }
        }

        return false;
    }

    /**
     * @param string $String
     *
     * @return string
     */
    public function useLetterFontReplacement(string $String):string
    {

        $FirstPart = '<span style="font-family: DejaVu Sans, sans-serif; line-height: 95%">';
        $LastPart = '</span>';
        $LetterCorrectionList = array(
            'Č', 'Ď', 'Ě', 'Ň', 'Ř', 'Ť', 'Ů',
            'č', 'ď', 'ě', 'ň', 'ř', 'ť', 'ů',
            'Ą', 'Ć', 'Ę', 'Ł', 'Ń', 'Ś', 'Ź', 'Ż',
            'ą', 'ć', 'ę', 'ł', 'ń', 'ś', 'ź', 'ż'
        );
        foreach($LetterCorrectionList as $Letter){
            $String = str_replace($Letter, $FirstPart.$Letter.$LastPart, $String);
        }
        return $String;
    }
}