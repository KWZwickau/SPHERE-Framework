<?php
namespace SPHERE\Application\Api\Document;

use DateTime;
use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblType;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\Responsibility\Responsibility;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter;

/**
 * Class AbstractDocument
 *
 * @package SPHERE\Application\Api\Document
 */
abstract class AbstractDocument
{

    /** @var null|Frame $Document */
    private $Document = null;

    /**
     * @var TblPerson|null
     */
    private ?TblPerson $tblPerson = null;

    /**
     * @var TblYear|null
     */
    private ?TblYear $tblYear = null;


    /**
     * @var int
     */
    private $tblAddressRowCount = 0;

    /**
     * @return false|TblPerson
     */
    public function getTblPerson()
    {
        if (null === $this->tblPerson) {
            return false;
        } else {
            return $this->tblPerson;
        }
    }

    /**
     * @param false|TblPerson $tblPerson
     */
    public function setTblPerson(TblPerson $tblPerson = null)
    {

        $this->tblPerson = $tblPerson;
    }

    /**
     * @return TblYear|null
     */
    public function getTblYear(): ?TblYear
    {
        return $this->tblYear;
    }

    /**
     * @param TblYear|null $tblYear
     */
    public function setTblYear(?TblYear $tblYear): void
    {
        $this->tblYear = $tblYear;
    }

    /**
     * @return false|TblStudentEducation
     */
    public function getStudentEducation()
    {
        if (($tblPerson = $this->getTblPerson())) {


            if (($tblYear = $this->getTblYear())) {
                $tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear);
            } else {
                $tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson);
            }

            // Schüler hat keine aktuelle SchülerBildung mehr
            if (!$tblStudentEducation && ($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListByPerson($tblPerson))) {
                $tblStudentEducationList = (new Extension())->getSorter($tblStudentEducationList)->sortObjectBy('YearNameForSorter', null, Sorter::ORDER_DESC);

                $tblStudentEducation = reset($tblStudentEducationList);
            }


            return $tblStudentEducation;
        }

        return false;
    }

    /**
     * @return string
     */
    abstract public function getName();

    /**
     * @param array $pageList
     * @param string $part
     *
     * @return Frame
     */
    abstract public function buildDocument(array $pageList = array(), string $part = '0'): Frame;

    /**
     * @param array $Data
     * @param array $pageList
     * @param string $part
     *
     * @return IBridgeInterface
     */
    public function createDocument($Data = array(), $pageList = array(), $part = '0')
    {

        if (isset($Data['Person']['Id'])) {
            if (($person = Person::useService()->getPersonById($Data['Person']['Id']))) {
                $this->setTblPerson($person);
                $this->allocatePersonData($Data);
                $this->allocatePersonAddress($Data);
                $this->allocatePersonCommon($Data);
                $this->allocateStudent($Data);
                $this->allocateResponsibility($Data);

                $this->allocatePersonParents($Data);
                $this->allocatePersonMail($Data);
                $this->allocatePersonParentsContact($Data);
                $this->allocatePersonContactPhonePrivate($Data);
                $this->allocatePersonContactPhoneEmergency($Data);
                $this->allocatePersonAuthorizedPersons($Data);
            } else {
                $this->setTblPerson(null);
            }
        }

        $this->Document = $this->buildDocument($pageList, $part);

        if (!empty($Data)) {
            $this->Document->setData($Data);
        }
        return $this->Document->getTemplate();
    }

    /**
     * @param array $Data
     *
     * @return array $Data
     */
    private function allocatePersonData(&$Data)
    {

        if ($this->getTblPerson()) {
            $Data['Person']['Data']['Name']['Salutation'] = $this->getTblPerson()->getSalutation();
            $FirstName = Prepare::useService()->useLetterFontReplacement($this->getTblPerson()->getFirstSecondName());
            $LastName = Prepare::useService()->useLetterFontReplacement($this->getTblPerson()->getLastName());
            $Data['Person']['Data']['Name']['First'] = $FirstName;
            $Data['Person']['Data']['Name']['Last'] = $LastName;
        }

        return $Data;
    }

    /**
     * @param array $Data
     *
     * @return array $Data
     */
    private function allocatePersonAddress(&$Data)
    {

        if ($this->getTblPerson()) {
            if (($tblAddress = $this->getTblPerson()->fetchMainAddress())) {
                $Data['Person']['Address']['Street']['Name'] = $tblAddress->getStreetName();
                $Data['Person']['Address']['Street']['Number'] = $tblAddress->getStreetNumber();
                $Data['Person']['Address']['City']['Code'] = $tblAddress->getTblCity()->getCode();
                $Data['Person']['Address']['City']['Name'] = $tblAddress->getTblCity()->getDisplayName();
            }
        }

        return $Data;
    }

    /**
     * @param array $Data
     *
     * @return array $Data
     */
    private function allocatePersonCommon(&$Data)
    {

        if ($this->getTblPerson()) {
            if (($tblCommon = Common::useService()->getCommonByPerson($this->getTblPerson()))
                && $tblCommonBirthDates = $tblCommon->getTblCommonBirthDates()
            ) {
                $Data['Person']['Common']['BirthDates']['Gender'] = ($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())
                    ? $tblCommonGender->getId() : 0;
                $Data['Person']['Common']['BirthDates']['Birthday'] = $tblCommonBirthDates->getBirthday();
                $Data['Person']['Common']['BirthDates']['Birthplace'] = $tblCommonBirthDates->getBirthplace()
                    ? $tblCommonBirthDates->getBirthplace() : '&nbsp;';
            }
                $Data['Person']['Common']['isReligion'] = 'nein';
            if (( $tblCommon = Common::useService()->getCommonByPerson($this->getTblPerson()) )
                && $tblCommonInformation = $tblCommon->getTblCommonInformation()
            ) {

                $Nationality = $tblCommonInformation->getNationality();
                if (strlen($Nationality) >= 15) {
                    $Nationality = substr($Nationality, 0, 14);
                }
                $Data['Person']['Common']['Nationality'] = $Nationality;

                $Denomination = $tblCommonInformation->getDenomination();
                if (strlen($Denomination) >= 15) {
                    $Denomination = substr($Denomination, 0, 14);
                }
                $Data['Person']['Common']['Denomination'] = $Denomination;
                if (!empty($Denomination)) {
                    $Data['Person']['Common']['isReligion'] = 'ja';
                }
            }
        }

        return $Data;
    }

    /**
     * @param array $Data
     *
     * @return array $Data
     */
    private function allocateStudent(&$Data)
    {

        if (($tblPerson = $this->getTblPerson())) {
            if (($tblStudentEducation = $this->getStudentEducation())) {
                if (($tblDivision = $tblStudentEducation->getTblDivision())) {
                    $Data['Student']['Division']['Name'] = $tblDivision->getName();
                } elseif (($tblCoreGroup = $tblStudentEducation->getTblCoreGroup())) {
                    $Data['Student']['Division']['Name'] = $tblCoreGroup->getName();
                }
            }

            if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {

                $Data['Student']['Identifier'] = $tblStudent->getIdentifierComplete();

                if (( $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ENROLLMENT') )) {
                    if (( $tblTransfer = Student::useService()->getStudentTransferByType($tblStudent, $tblTransferType) )) {
                        $Data['Student']['School']['Enrollment']['Date'] = $tblTransfer->getTransferDate();
                        $Year = ( new DateTime($tblTransfer->getTransferDate()) )->format('Y');
                        $YearShort = (integer)(new DateTime($tblTransfer->getTransferDate()))->format('y');
                        $YearString = $Year.'/'.( $YearShort + 1 );
                        $Data['Student']['School']['Enrollment']['Year'] = $YearString;
                        if (($tblStudentSchoolEnrollmentType = $tblTransfer->getTblStudentSchoolEnrollmentType())) {
                            if ($tblStudentSchoolEnrollmentType->getIdentifier() == 'POSTPONED') {
                                $Data['Student']['School']['Enrollment']['Postponed'] = 'X';
                            }
                            if ($tblStudentSchoolEnrollmentType->getIdentifier() == 'PREMATURE') {
                                $Data['Student']['School']['Enrollment']['Premature'] = 'X';
                            }
                            if ($tblStudentSchoolEnrollmentType->getIdentifier() == 'REGULAR') {
                                $Data['Student']['School']['Enrollment']['Regular'] = 'X';
                            }
                        }
                    }
                }

                // Bildungsempfehlung für Oberschule/Gymnasium
                if (($tblCertificate = Generator::useService()->getCertificateByCertificateClassName('BeGs'))
                    && ($tblPrepareStudentList = Prepare::useService()->getPrepareStudentAllByPerson($tblPerson, $tblCertificate ))
                ) {
                    foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                        if (($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
                            && ($tblPrepareInformation = Prepare::useService()->getPrepareInformationBy($tblPrepare, $tblPerson, 'SchoolType'))
                        ) {
                            if (strpos($tblPrepareInformation->getValue(), 'Oberschule') !== false) {
                                $Data['Student']['School']['Education']['Recommendation']['OS'] = 'X';
                            } else {
                                $Data['Student']['School']['Education']['Recommendation']['GYM'] = 'X';
                            }

                            break;
                        }
                    }
                }

                if (( $AttendanceDate = $tblStudent->getSchoolAttendanceStartDate())) {
                    $Data['Student']['School']['Attendance']['Date'] = $AttendanceDate;
                    $Year = ( new DateTime($AttendanceDate) )->format('Y');
//                    $YearShort = (integer)(new \DateTime($AttendanceDate))->format('y');
//                    $YearString = $Year.'/'.( $YearShort + 1 );
                    // nur Kalenderjahr anzeigen
                    $Data['Student']['School']['Attendance']['Year'] = $Year;
                }

                if ($tblStudentEducation) {
                    if (($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())) {
                        $Data['Student']['School']['Type'] = $tblSchoolType->getName();
                    }

                    if (($tblCompany = $tblStudentEducation->getServiceTblCompany())) {
                        if (($tblAddress = $tblCompany->fetchMainAddress())) {
                            $Data['Document']['PlaceDate'] = $tblAddress->getTblCity()->getName() . ', '
                                . date('d.m.Y');
                            $Data['Document']['Date']['Now'] = date('d.m.Y');

                            $Data['Student']['CompanyAddress'] = '';
                            if ($tblAddress->getTblCity()->getDistrict())
                            {
                                $Data['Student']['CompanyAddress'] .= 'OT ' . $tblAddress->getTblCity()->getDistrict() . '<br>';
                                $this->tblAddressRowCount++;
                            }
                            if ($tblAddress->getStreetName())
                            {
                                $Data['Student']['CompanyAddress'] .= $tblAddress->getStreetName()
                                    .' '.$tblAddress->getStreetNumber().'<br>';
                                $this->tblAddressRowCount++;
                            }
                            if ($tblAddress->getTblCity()->getCode())
                            {
                                $Data['Student']['CompanyAddress'] .= $tblAddress->getTblCity()->getCode() .
                                    ' ' . $tblAddress->getTblCity()->getName();
                                $this->tblAddressRowCount++;
                            }
                        }

                        // StudentCard - PrimarySchool
                        if (($tblSetting = Consumer::useService()->getSetting(
                                'Api', 'Document', 'StudentCard_PrimarySchool', 'ShowSchoolName'))
                            && $tblSetting->getValue()
                        ) {
                            if ($tblCompany->getName()) {
                                $Data['Student']['Company'] = $tblCompany->getName();
                                $this->tblAddressRowCount++;
                            }
                            if ($tblCompany->getExtendedName()) {
                                $Data['Student']['Company2'] = $tblCompany->getExtendedName();
                                $this->tblAddressRowCount++;
                            }
                        }
                    }
                }

                if (($tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('LEAVE'))) {
                    if (($tblTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                        $tblTransferType))
                    ) {
                        $Data['Student']['LeaveDate'] = $tblTransfer->getTransferDate();
                    }
                }

                // Lebenswelt
                if (($tblMedicalRecord = $tblStudent->getTblStudentMedicalRecord())) {
                    $InsuranceStateArray = array(
                        0 => '',
                        1 => 'pflicht versichert',
                        2 => 'freiwillig versichert',
                        3 => 'privat versichert',
                        4 => 'familienversichert bei dem Vater',
                        5 => 'familienversichert bei der Mutter',
                    );

                    $Data['Student']['MedicalRecord']['Disease'] = $tblMedicalRecord->getDisease();
                    $Data['Student']['MedicalRecord']['InsuranceState']
                        = isset($InsuranceStateArray[$tblMedicalRecord->getInsuranceState()])
                        ? $InsuranceStateArray[$tblMedicalRecord->getInsuranceState()] : '';
                    $Data['Student']['MedicalRecord']['Insurance'] = $tblMedicalRecord->getInsurance();
                }
            }
        }

        return $Data;
    }

    /**
     * @param array $Data
     *
     * @return array $Data
     */
    private function allocateResponsibility(&$Data)
    {
        $tblPerson = $this->tblPerson;
        // pre fill found information (Responsibility)
        $Data['Responsibility']['Company']['Number'] = School::useService()->getCompanyNumber();

        if ($tblPerson) {
            if (($tblStudentEducation = $this->getStudentEducation())) {
                if (($tblCompany = $tblStudentEducation->getServiceTblCompany())
                    && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                    && ($tblSchool = School::useService()->getSchoolByCompanyAndType($tblCompany, $tblSchoolType))
                ) {
                    // fill found information (School)
                    $Data['Responsibility']['Company']['Number'] = School::useService()->getCompanyNumber($tblSchool);
                }
            }
        }

        $tblResponsibilityList = Responsibility::useService()->getResponsibilityAll();
        if ($tblResponsibilityList) {
            $tblResponsibility = $tblResponsibilityList[0];
            if ($tblResponsibility) {
                $tblCompany = $tblResponsibility->getServiceTblCompany();
                if ($tblCompany) {
                    $Data['Responsibility']['Company']['Display'] = $tblCompany->getDisplayName();
                }
            }
        }

        return $Data;
    }

    /**
     * @param array $Data
     *
     * @return array $Data
     */
    private function allocatePersonParents(&$Data)
    {
        if ($this->getTblPerson()) {
            if (($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($this->getTblPerson()))) {
                foreach ($tblRelationshipList as $tblToPerson) {
                    if (($tblFromPerson = $tblToPerson->getServiceTblPersonFrom())
                        && $tblToPerson->getServiceTblPersonTo()
                        && $tblToPerson->getTblType()->getName() == 'Sorgeberechtigt'
                        && $tblToPerson->getServiceTblPersonTo()->getId() == $this->getTblPerson()->getId()
                    ) {

                        $Ranking = $tblToPerson->getRanking();
                        $Data['Person']['Parent']['S'.$Ranking]['Gender'] = $tblFromPerson->getGenderNameFromGenderOrSalutation();
                        $Data['Person']['Parent']['S'.$Ranking]['Salutation'] = $tblFromPerson->getSalutation();
                        $Data['Person']['Parent']['S'.$Ranking]['Name']['First'] = $tblFromPerson->getFirstName();
                        $Data['Person']['Parent']['S'.$Ranking]['Name']['Last'] = $tblFromPerson->getLastName();


                        $tblAddress = $tblFromPerson->fetchMainAddress();
                        if ($tblAddress) {
                            $Data['Person']['Parent']['S'.$Ranking]['AddressTwoRowString'] = $tblAddress->getGuiString();
                        }

                        if (!isset($Data['Person']['Parent']['Mother']['Name'])) {
                            $Data['Person']['Parent']['Mother']['Name']['First'] = $tblFromPerson->getFirstSecondName();
                            $Data['Person']['Parent']['Mother']['Name']['Last'] = $tblFromPerson->getLastName();
                            $Data['Person']['Parent']['Mother']['Name']['LastFirst'] = $tblFromPerson->getLastFirstName();
                            if ($tblAddress) {
                                $Data['Person']['Parent']['S'.$Ranking]['Address']['CityCode'] = $tblAddress->getTblCity()->getCode();
                                $Data['Person']['Parent']['S'.$Ranking]['Address']['City'] = $tblAddress->getTblCity()->getDisplayName();
                                $Data['Person']['Parent']['S'.$Ranking]['Address']['Street'] = $tblAddress->getStreetName();
                                $Data['Person']['Parent']['S'.$Ranking]['Address']['StreetNumber'] = $tblAddress->getStreetNumber();
                            }
                        } elseif (!isset($Data['Person']['Parent']['Father']['Name'])) {
                            $Data['Person']['Parent']['Father']['Name']['First'] = $tblFromPerson->getFirstSecondName();
                            $Data['Person']['Parent']['Father']['Name']['Last'] = $tblFromPerson->getLastName();
                            $Data['Person']['Parent']['Father']['Name']['LastFirst'] = $tblFromPerson->getLastFirstName();
                            if ($tblAddress) {
                                $Data['Person']['Parent']['S'.$Ranking]['Address']['CityCode'] = $tblAddress->getTblCity()->getCode();
                                $Data['Person']['Parent']['S'.$Ranking]['Address']['City'] = $tblAddress->getTblCity()->getDisplayName();
                                $Data['Person']['Parent']['S'.$Ranking]['Address']['Street'] = $tblAddress->getStreetName();
                                $Data['Person']['Parent']['S'.$Ranking]['Address']['StreetNumber'] = $tblAddress->getStreetNumber();
                            }
                        }

                        if (($tblPhoneList = Phone::useService()->getPhoneAllByPerson($tblFromPerson))){
                            foreach($tblPhoneList as $tblToPersonPhone) {
                                if(($tblToPersonPhone->getTblType()->getName() == 'Privat'
                                    || $tblToPersonPhone->getTblType()->getName() == 'Geschäftlich')
                                    && $tblToPersonPhone->getTblType()->getDescription() == 'Festnetz'
                                    && !isset($Data['Person']['Parent']['S'.$Ranking]['Phone']['Festnetz'])

                                ) {
                                    $Data['Person']['Parent']['S'.$Ranking]['Phone']['Festnetz'] = $tblToPersonPhone->getTblPhone()->getNumber();

                                } elseif ($tblToPersonPhone->getTblType()->getName() == 'Privat'
                                    || $tblToPersonPhone->getTblType()->getName() == 'Geschäftlich'
                                    && $tblToPersonPhone->getTblType()->getDescription() == 'Mobil'
                                    && !isset($Data['Person']['Parent']['S'.$Ranking]['Phone']['Mobil'])

                                ) {
                                    $Data['Person']['Parent']['S'.$Ranking]['Phone']['Mobil'] = $tblToPersonPhone->getTblPhone()->getNumber();

                                }
                            }
                        }
                    }
                }
            }
        }

        return $Data;
    }

    /**
     * @param array $Data
     *
     * @return array $Data
     */
    private function allocatePersonMail(&$Data)
    {

        if ($this->getTblPerson()) {
            $hasPrivateSingleMail = false;
            if (($tblMailList = Mail::useService()->getMailAllByPerson($this->getTblPerson()))) {
                $list = array();
                foreach ($tblMailList as $tblMailToPerson) {
                    $list[] = $tblMailToPerson->getTblMail()->getAddress();
                    if (!$hasPrivateSingleMail && $tblMailToPerson->getTblType()->getName() == 'Privat') {
                        $hasPrivateSingleMail = true;
                        $Data['Person']['Contact']['SinglePrivateMail'] = $tblMailToPerson->getTblMail()->getAddress();
                    }
                }
                if (!empty($list)) {
                    $Data['Person']['Contact']['Mail'] = implode(', ', $list);
                }
            }
        }

        return $Data;
    }

    /**
     * @param array $Data
     *
     * @return array $Data
     */
    private function allocatePersonParentsContact(&$Data)
    {


        $Data['Person']['Contact']['All']['Mail'] = '';
        $Data['Person']['Contact']['All']['Person']['Mail'] = '';
        $Data['Person']['Parent']['Mother']['Phone']['Private'] = '';
        $Data['Person']['Parent']['Mother']['Phone']['Business'] = '';
        $Data['Person']['Parent']['Mother']['Phone']['Mobil'] = '';
        $Data['Person']['Parent']['Father']['Phone']['Private'] = '';
        $Data['Person']['Parent']['Father']['Phone']['Business'] = '';
        $Data['Person']['Parent']['Father']['Phone']['Mobil'] = '';
        if ($this->getTblPerson()) {
            $tblToPersonMailList = Mail::useService()->getMailAllByPerson($this->getTblPerson());
            if ($tblToPersonMailList) {
                $Data['Person']['Contact']['All']['Mail'] = $this->getTblPerson()->getLastFirstName().': ';
                foreach ($tblToPersonMailList as $tblToPersonMail) {
                    if (($tblMail = $tblToPersonMail->getTblMail())) {
                        $Data['Person']['Contact']['All']['Person']['Mail'] .= $this->getTblPerson()->getLastFirstName().': '
                            .$tblMail->getAddress().';<br/>';
                        $Data['Person']['Contact']['All']['Mail'] .= $tblToPersonMail->getTblType()->getName()
                            .' > '.$tblMail->getAddress().'; ';
                    }
                }
                $Data['Person']['Contact']['All']['Mail'] .= '<br/>';
            }
            if (($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($this->getTblPerson()))) {
                $tblPersonMother = false;
                $tblPersonFather = false;
                foreach ($tblRelationshipList as $tblToPerson) {
                    if (($tblFromPerson = $tblToPerson->getServiceTblPersonFrom())
                        && $tblToPerson->getServiceTblPersonTo()
                        && $tblToPerson->getTblType()->getName() == 'Sorgeberechtigt'
                        && $tblToPerson->getServiceTblPersonTo()->getId() == $this->getTblPerson()->getId()
                    ) {
                        // get mail string person name: type mail, type mail... <br/> by next person
                        $tblToPersonMailList = Mail::useService()->getMailAllByPerson($tblFromPerson);
                        if ($tblToPersonMailList) {
                            $Data['Person']['Contact']['All']['Mail'] .= $tblFromPerson->getLastFirstName().': ';
                            foreach ($tblToPersonMailList as $tblToPersonMail) {
                                if (($tblMail = $tblToPersonMail->getTblMail())) {
                                    $Data['Person']['Contact']['All']['Person']['Mail'] .= $tblFromPerson->getLastFirstName().': '
                                        .$tblMail->getAddress().';<br/>';
                                    // set next row if line ist to long
                                    $ControlString = $Data['Person']['Contact']['All']['Mail'].
                                        $tblToPersonMail->getTblType()->getName().' > '.$tblMail->getAddress().'; ';
                                    $PosLastBr = strripos($ControlString, '<br/>');
                                    if (strlen(substr($ControlString, $PosLastBr)) > 90) {
                                        $Data['Person']['Contact']['All']['Mail'] .= '<br/>';
                                    }

                                    $Data['Person']['Contact']['All']['Mail'] .= $tblToPersonMail->getTblType()->getName()
                                        .' > '.$tblMail->getAddress().'; ';
                                }
                            }
                            $Data['Person']['Contact']['All']['Mail'] .= '<br/>';
                        }

                        // get type of phone number (each a single variable)
                        if (!$tblPersonMother) {
                            $tblPersonMother = $tblFromPerson;
                        } elseif (!$tblPersonFather) {
                            $tblPersonFather = $tblFromPerson;
                        }
                        if (($tblPhoneList = Phone::useService()->getPhoneAllByPerson($tblFromPerson))) {

                            if ($tblPersonMother && $tblPersonMother->getId() == $tblFromPerson->getId()) {
                                foreach ($tblPhoneList as $tblToPersonPhone) {
                                    if ($tblToPersonPhone->getTblType()->getName() == 'Privat'
                                        && $tblToPersonPhone->getTblType()->getDescription() == 'Festnetz'
                                    ) {

                                        $Data['Person']['Parent']['Mother']['Phone']['Private'] .=
                                            ($Data['Person']['Parent']['Mother']['Phone']['Private'] != '' ? '<br/>' : '')
                                            .$tblToPersonPhone->getTblPhone()->getNumber();
                                    } elseif ($tblToPersonPhone->getTblType()->getName() == 'Geschäftlich'
                                        && $tblToPersonPhone->getTblType()->getDescription() == 'Festnetz'
                                    ) {
                                        $Data['Person']['Parent']['Mother']['Phone']['Business'] .=
                                            ($Data['Person']['Parent']['Mother']['Phone']['Business'] != '' ? '<br/>' : '')
                                            .$tblToPersonPhone->getTblPhone()->getNumber();
                                    } elseif (($tblToPersonPhone->getTblType()->getName() == 'Privat'
                                            || $tblToPersonPhone->getTblType()->getName() == 'Geschäftlich')
                                        && $tblToPersonPhone->getTblType()->getDescription() == 'Mobil'
                                    ) {
                                        $Data['Person']['Parent']['Mother']['Phone']['Mobil'] .=
                                            ($Data['Person']['Parent']['Mother']['Phone']['Mobil'] != '' ? '<br/>' : '')
                                            .$tblToPersonPhone->getTblPhone()->getNumber();
                                    }
                                }
                            }
                            if ($tblPersonFather && $tblPersonFather->getId() == $tblFromPerson->getId()) {
                                foreach ($tblPhoneList as $tblToPersonPhone) {
                                    if ($tblToPersonPhone->getTblType()->getName() == 'Privat'
                                        && $tblToPersonPhone->getTblType()->getDescription() == 'Festnetz'
                                    ) {
                                        $Data['Person']['Parent']['Father']['Phone']['Private'] .=
                                            ($Data['Person']['Parent']['Father']['Phone']['Private'] != '' ? '<br/>' : '')
                                            .$tblToPersonPhone->getTblPhone()->getNumber();
                                    } elseif ($tblToPersonPhone->getTblType()->getName() == 'Geschäftlich'
                                        && $tblToPersonPhone->getTblType()->getDescription() == 'Festnetz'
                                    ) {
                                        $Data['Person']['Parent']['Father']['Phone']['Business'] .=
                                            ($Data['Person']['Parent']['Father']['Phone']['Business'] != '' ? '<br/>' : '')
                                            .$tblToPersonPhone->getTblPhone()->getNumber();
                                    } elseif (($tblToPersonPhone->getTblType()->getName() == 'Privat'
                                            || $tblToPersonPhone->getTblType()->getName() == 'Geschäftlich')
                                        && $tblToPersonPhone->getTblType()->getDescription() == 'Mobil'
                                    ) {
                                        $Data['Person']['Parent']['Father']['Phone']['Mobil'] .=
                                            ($Data['Person']['Parent']['Father']['Phone']['Mobil'] != '' ? '<br/>' : '')
                                            .$tblToPersonPhone->getTblPhone()->getNumber();
                                    }
                                }
                            }
                            // get combination of person name and all found phone numbers
                            if ($tblPhoneList) {
                                $list = array();
                                foreach ($tblPhoneList as $tblPhoneToPerson) {
                                    $list[] = $tblPhoneToPerson->getTblType()->getName() . ': '
                                        . $tblPhoneToPerson->getTblPhone()->getNumber();
                                }
                                if (!empty($list)) {
                                    sort($list);
                                }
                                if (!empty($list)) {
                                    if (!isset($Data['Person']['Parent']['Mother']['Contact']['Phone'])) {
                                        $Data['Person']['Parent']['Mother']['Contact']['Phone'] =
                                            $tblFromPerson->getLastFirstName() . ': ' . implode(', ', $list);
                                    } elseif (!isset($Data['Person']['Parent']['Father']['Contact']['Phone'])) {
                                        $Data['Person']['Parent']['Father']['Contact']['Phone'] =
                                            $tblFromPerson->getLastFirstName() . ': ' . implode(', ', $list);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $Data;
    }

    /**
     * @param $Data
     *
     * @return array $Data
     */
    public function allocatePersonContactPhonePrivate(&$Data)
    {

        if (($tblPerson = $this->getTblPerson())) {

            $phoneNumberList = $this->setPhoneNumbersByTypeName($tblPerson, 'Privat');
            $phone = '';
            // es passen nur 3 Telefonnummern in das Feld
            if (!empty($phoneNumberList)) {
                $phone = $phoneNumberList[0]
                    . ( isset( $phoneNumberList[1] ) ? '<br>'. $phoneNumberList[1] : '' )
                    . ( isset( $phoneNumberList[2] ) ? '<br>'. $phoneNumberList[2] : '' );

            }
            $Data['Person']['Contact']['Phone']['Number'] = $phone;

            $phoneNumber = '';
            $mobilPhoneNumber = '';
            if (($tblPhoneList = Phone::useService()->getPhoneAllByPerson($tblPerson))){
                foreach($tblPhoneList as $tblToPersonPhone) {
                    if ($tblToPersonPhone->getTblType()->getName() == 'Privat') {
                        if (!$phoneNumber && $tblToPersonPhone->getTblType()->getDescription() == 'Festnetz') {
                            $phoneNumber = $tblToPersonPhone->getTblPhone()->getNumber();
                        }
                        if (!$mobilPhoneNumber && $tblToPersonPhone->getTblType()->getDescription() == 'Mobil') {
                            $mobilPhoneNumber = $tblToPersonPhone->getTblPhone()->getNumber();
                        }
                    }
                }
            }

            if ($mobilPhoneNumber) {
                $Data['Person']['Contact']['Phone']['SinglePrivateNumber'] = $mobilPhoneNumber;
            } elseif ($phoneNumber) {
                $Data['Person']['Contact']['Phone']['SinglePrivateNumber'] = $phoneNumber;
            }
        }

        return $Data;
    }

    /**
     * @param TblPerson $tblPerson
     * @param string $TypeName
     *
     * @return array
     */
    private function setPhoneNumbersByTypeName(TblPerson $tblPerson, $TypeName = 'Privat')
    {
        $phoneNumberList = array();
        $IsRemark = false;
        if($TypeName == 'Notfall'){
            $IsRemark = true;
            $tblPhoneToPersonList = Phone::useService()->getPhoneToPersonAllEmergencyContactByPerson($tblPerson);
            $this->getPhoneNumbersByList($tblPhoneToPersonList, $phoneNumberList, $IsRemark);
        } else {
            if (($tblPhoneType = Phone::useService()->getTypeByNameAndDescription($TypeName, 'Festnetz'))) {
                $this->getPhoneNumbersByType($tblPerson, $tblPhoneType, $phoneNumberList, $IsRemark);
            }
            if (($tblPhoneType = Phone::useService()->getTypeByNameAndDescription($TypeName, 'Mobil'))) {
                $this->getPhoneNumbersByType($tblPerson, $tblPhoneType, $phoneNumberList, $IsRemark);
            }
        }

        // Telefonnummern der Sorgeberechtigten mit Anzeigen
        if (empty($phoneNumberList) || count($phoneNumberList) < 3) {
            if (($tblRelationshipType = Relationship::useService()->getTypeByName('Sorgeberechtigt'))
                && ($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson,
                    $tblRelationshipType))
            ) {
                $phoneNumberMotherList = array();
                $phoneNumberFatherList = array();
                foreach ($tblRelationshipList as $tblRelationship) {
                    if (($tblPersonFrom = $tblRelationship->getServiceTblPersonFrom())
                        && ($tblPersonTo = $tblRelationship->getServiceTblPersonTo())
                        && $tblPerson->getId() == $tblPersonTo->getId()
                    ) {
                        if ($TypeName == 'Notfall') {
                            $tblPhoneToPersonList = Phone::useService()->getPhoneToPersonAllEmergencyContactByPerson($tblPerson);
                            if ($tblPersonFrom->getSalutation() == 'Frau') {
                                $this->getPhoneNumbersByList($tblPhoneToPersonList, $phoneNumberMotherList, $IsRemark);
                            } else {
                                $this->getPhoneNumbersByList($tblPhoneToPersonList, $phoneNumberFatherList, $IsRemark);
                            }
                        } else {
                            if (($tblPhoneType = Phone::useService()->getTypeByNameAndDescription($TypeName,
                                'Festnetz'))
                            ) {
                                if ($tblPersonFrom->getSalutation() == 'Frau') {
                                    $this->getPhoneNumbersByType($tblPersonFrom, $tblPhoneType, $phoneNumberMotherList, $IsRemark);
                                } else {
                                    $this->getPhoneNumbersByType($tblPersonFrom, $tblPhoneType, $phoneNumberFatherList, $IsRemark);
                                }
                            }
                            if (($tblPhoneType = Phone::useService()->getTypeByNameAndDescription($TypeName, 'Mobil'))) {
                                if ($tblPersonFrom->getSalutation() == 'Frau') {
                                    $this->getPhoneNumbersByType($tblPersonFrom, $tblPhoneType, $phoneNumberMotherList, $IsRemark);
                                } else {
                                    $this->getPhoneNumbersByType($tblPersonFrom, $tblPhoneType, $phoneNumberFatherList, $IsRemark);
                                }
                            }
                        }
                    }
                }

                // Zuerst die Telefonnummern der Mütter
                foreach ($phoneNumberMotherList as $motherNumber) {
                    $phoneNumberList[] = $motherNumber;
                }
                foreach ($phoneNumberFatherList as $fatherNumber) {
                    $phoneNumberList[] = $fatherNumber;
                }
            }
        }

        return $phoneNumberList;
    }

    /**
     * @param $phoneNumber
     *
     * @return false|int
     */
    private function filterPhoneNumber($phoneNumber)
    {
        // funktioniert nicht mehr
//        return preg_replace('![^0-9/\s-+]+!', '', $phoneNumber);
        return preg_replace('![^0-9/\s]+!', '', $phoneNumber);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblType   $tblType
     * @param array     $phoneNumberList
     * @param bool      $IsRemark
     */
    private function getPhoneNumbersByType(TblPerson $tblPerson, TblType $tblType, &$phoneNumberList, $IsRemark = false) {
        if (($tblPhoneToPersonList = Phone::useService()->getPhoneToPersonAllBy($tblPerson, $tblType))) {
            $this->getPhoneNumbersByList($tblPhoneToPersonList, $phoneNumberList, $IsRemark);
        }
    }

    /**
     * @param $tblPhoneToPersonList
     * @param $phoneNumberList
     * @param bool $IsRemark
     */
    private function getPhoneNumbersByList($tblPhoneToPersonList, &$phoneNumberList, bool $IsRemark = false)
    {
        if ($tblPhoneToPersonList) {
            foreach ($tblPhoneToPersonList as $tblPhoneToPerson) {
                if (($tblPhone = $tblPhoneToPerson->getTblPhone())) {
                    if($IsRemark){
                        $phoneNumberList[] = $this->filterPhoneNumber($tblPhone->getNumber()) .
                            ($tblPhoneToPerson->getRemark()
                                ? ' ('. $tblPhoneToPerson->getRemark().')'
                                : ''
                            );
                    } else {
                        $phoneNumberList[] = $this->filterPhoneNumber($tblPhone->getNumber());
                    }
                }
            }
        }
    }

    /**
     * @param array $Data
     *
     * @return array $Data
     */
    private function allocatePersonContactPhoneEmergency(&$Data)
    {

        if (($tblPerson = $this->getTblPerson())) {

            $phoneNumberList = $this->setPhoneNumbersByTypeName($tblPerson, 'Notfall');
            $phone = '';
            $phoneNew = '';
            // es passen nur 3 Telefonnummern in das Feld
            if (!empty($phoneNumberList)) {
                $phone = $phoneNumberList[0]
                    . ( isset( $phoneNumberList[1] ) ? '<br>'. $phoneNumberList[1] : '' )
                    . ( isset( $phoneNumberList[2] ) ? '<br>'. $phoneNumberList[2] : '' );

                $Data['Person']['Contact']['Phone']['Radebeul']['EmergencyNumber'] = implode('; ', $phoneNumberList);

                // 2 passen nur 2 Notfallnummern auf die neue Schülerkartei
                $phoneNew = $phoneNumberList[0]
                    . (isset($phoneNumberList[1]) ? '; ' . $phoneNumberList[1] : '');
            }

            $Data['Person']['Contact']['Phone']['EmergencyNumber'] = $phone;
            $Data['Person']['Contact']['Phone']['EmergencyNumberNew'] = $phoneNew;
        }

        return $Data;
    }

    /**
     * Lebenswelt (Zwenkau): Abholberechtigte
     * @param array $Data
     *
     * @return array $Data
     */
    private function allocatePersonAuthorizedPersons(&$Data)
    {

        if ($this->getTblPerson()) {
            if (($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($this->getTblPerson()))) {
                $list = [];
                foreach ($tblRelationshipList as $tblToPerson) {
                    if (($tblFromPerson = $tblToPerson->getServiceTblPersonFrom())
                        && $tblToPerson->getServiceTblPersonTo()
                        && $tblToPerson->getTblType()->getName() == 'Bevollmächtigt'
                        && $tblToPerson->getServiceTblPersonTo()->getId() == $this->getTblPerson()->getId()
                    ) {
                        $remark = $tblToPerson->getRemark();
                        $list[] = $tblFromPerson->getLastFirstName() . ($remark ? ' (' . $remark . ')' : '');
                    }
                }
                if (!empty($list)) {
                    $Data['Person']['AuthorizedPersons'] = implode(', ', $list);
                }
            }
        }

        return $Data;
    }

    /**
     * @return null|Frame
     */
    public function getDocument()
    {
        return $this->Document;
    }

    /**
     * @param null|Frame $Document
     */
    public function setDocument($Document)
    {
        $this->Document = $Document;
    }

    /**
     * Lebenswelt (Zwenkau) Notfall Nummern des Schülers
     *
     * @return Slice
     */
    protected function getEmergencySlice()
    {

        $slice = new Slice();

        $countNumbers = 0;
        if ($this->getTblPerson()) {
            if (($tblPhoneList = Phone::useService()->getPhoneAllByPerson($this->getTblPerson()))) {
                if ($tblPhoneList) {
                    foreach ($tblPhoneList as $tblPhoneToPerson) {
                        if ($tblPhoneToPerson->getIsEmergencyContact()) {
                            $countNumbers++;
                            if ($countNumbers == 1) {
                                $slice
                                    ->addSection((new Section())
                                        ->addElementColumn((new Element())
                                            ->setContent('
                                                {% if(Content.Person.Contact.Phone.Emergency' . $countNumbers . ') %}
                                                    {{ Content.Person.Contact.Phone.Emergency' . $countNumbers . ' }}
                                                {% else %}
                                                      &nbsp;
                                                {% endif %}
                                            ')
                                            ->stylePaddingTop()
                                            ->stylePaddingLeft()
                                            ->styleBorderAll()
                                        )
                                    );
                            } else {
                                $slice
                                    ->addSection((new Section())
                                        ->addElementColumn((new Element())
                                            ->setContent('
                                                {% if(Content.Person.Contact.Phone.Emergency' . $countNumbers . ') %}
                                                    {{ Content.Person.Contact.Phone.Emergency' . $countNumbers . ' }}
                                                {% else %}
                                                      &nbsp;
                                                {% endif %}
                                            ')
                                            ->stylePaddingTop()
                                            ->stylePaddingLeft()
                                            ->styleBorderLeft()
                                            ->styleBorderRight()
                                            ->styleBorderBottom()
                                        )
                                    );
                            }
                        }
                    }
                }
            }
        }

        if ($countNumbers == 0) {
            $slice
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('
                            &nbsp;
                        ')
                        ->stylePaddingTop()
                        ->stylePaddingLeft()
                        ->styleBorderAll()
                    )
                );
        }

        return $slice;
    }

    /**
     * @param string $content
     * @param string $thicknessInnerLines
     *
     * @return Slice
     */
    protected function setCheckBox(
        $content = '&nbsp;',
        $thicknessInnerLines = '0.5px'
    )
    {
        return (new Slice())
        ->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleHeight('5px')
            )
        )
        ->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleHeight('10px')
                , '1.2%')
            ->addElementColumn((new Element())
                ->setContent($content)
                ->styleHeight('12px')
                ->styleTextSize('8.5')
                ->stylePaddingLeft('1.2px')
                ->stylePaddingBottom('0px')
                ->styleBorderAll($thicknessInnerLines)
                , '1.6%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleHeight('10px')
                , '1.2%')
        )
        ->styleHeight('24px');
    }

    /**
     * @param string $with
     *
     * @return Element|Element\Image
     */
    protected function getPictureEnrollmentDocument($with = 'auto')
    {

        $picturePath = $this->getEnrollmentDocumentUsedPicture();
        if ($picturePath != '') {
            $height = $this->getEnrollmentDocumentPictureHeight();
            $column = (new Element\Image($picturePath, $with, $height));
        } else {
            $column = (new Element())
                ->setContent('&nbsp;');
        }
        return $column;
    }

    /**
     * @return string
     */
    private function getEnrollmentDocumentUsedPicture()
    {
        if (($tblSetting = Consumer::useService()->getSetting(
            'Api', 'Document', 'Standard', 'EnrollmentDocument_PictureAddress'))
        ) {
            return (string)$tblSetting->getValue();
        }
        return '';
    }

    /**
     * @return string
     */
    private function getEnrollmentDocumentPictureHeight()
    {

        $value = '';

        if (($tblSetting = Consumer::useService()->getSetting(
            'Api', 'Document', 'Standard', 'EnrollmentDocument_PictureHeight'))
        ) {
            $value = (string)$tblSetting->getValue();
        }

        return $value ? $value : '90px';
    }
}