<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 09.09.2016
 * Time: 10:54
 */

namespace SPHERE\Application\Api\Document;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\Responsibility\Responsibility;
use SPHERE\Application\Setting\Consumer\School\School;

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
    private $tblPerson = null;

    /**
     * @var int
     */
    private $tblAdressRowCount = 0;

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
     * @return string
     */
    abstract public function getName();

    /**
     * @param array $pageList
     *
     * @return Frame
     */
    abstract public function buildDocument($pageList = array());

    /**
     * @param array $Data
     * @param array $pageList
     *
     * @return IBridgeInterface
     */
    public function createDocument($Data = array(), $pageList = array())
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

        $this->Document = $this->buildDocument($pageList);

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
            $Data['Person']['Data']['Name']['First'] = $this->getTblPerson()->getFirstSecondName();
            $Data['Person']['Data']['Name']['Last'] = $this->getTblPerson()->getLastName();
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
                $Data['Person']['Common']['BirthDates']['Gender'] = $tblCommonBirthDates->getGender();
                $Data['Person']['Common']['BirthDates']['Birthday'] = $tblCommonBirthDates->getBirthday();
                $Data['Person']['Common']['BirthDates']['Birthplace'] = $tblCommonBirthDates->getBirthplace()
                    ? $tblCommonBirthDates->getBirthplace() : '&nbsp;';
            }
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

        if ($this->getTblPerson()) {
            if (($tblDivisionList = Student::useService()->getCurrentDivisionListByPerson($this->getTblPerson()))) {
                foreach ($tblDivisionList as $tblDivision) {
                    if (!$tblDivision->getTblLevel()->getIsChecked()) {
                        $Data['Student']['Division']['Name'] = $tblDivision->getDisplayName();
                        break;
                    }
                }
            }

            if (($tblStudent = Student::useService()->getStudentByPerson($this->getTblPerson()))) {

                $Data['Student']['Identifier'] = $tblStudent->getIdentifier();

                if (( $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ENROLLMENT') )) {
                    if (( $tblTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                        $tblTransferType) )
                    ) {
                        $Data['Student']['School']['Enrollment']['Date'] = $tblTransfer->getTransferDate();
                        $Year = ( new \DateTime($tblTransfer->getTransferDate()) )->format('Y');
                        $YearShort = (integer)(new \DateTime($tblTransfer->getTransferDate()))->format('y');
                        $YearString = $Year.'/'.( $YearShort + 1 );
                        $Data['Student']['School']['Enrollment']['Year'] = $YearString;
                    }
                }
                if (( $AttendanceDate = $tblStudent->getSchoolAttendanceStartDate())) {
                    $Data['Student']['School']['Attendance']['Date'] = $AttendanceDate;
                    $Year = ( new \DateTime($AttendanceDate) )->format('Y');
                    $YearShort = (integer)(new \DateTime($AttendanceDate))->format('y');
                    $YearString = $Year.'/'.( $YearShort + 1 );
                    $Data['Student']['School']['Attendance']['Year'] = $YearString;
                }

                if (($tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS'))) {
                    if (($tblTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                        $tblTransferType))
                    ) {
                        if ($tblTransfer->getServiceTblType()) {
                            $Data['Student']['School']['Type'] = $tblTransfer->getServiceTblType()->getName();
                        }

                        if (($tblCompany = $tblTransfer->getServiceTblCompany())) {
                            if (($tblAddress = $tblCompany->fetchMainAddress())) {
                                $Data['Document']['PlaceDate'] = $tblAddress->getTblCity()->getName() . ', '
                                    . date('d.m.Y');
                                $Data['Document']['Date']['Now'] = date('d.m.Y');

                                $Data['Student']['CompanyAddress'] = '';
                                if ($tblAddress->getTblCity()->getDistrict())
                                {
                                    $Data['Student']['CompanyAddress'] .= 'OT ' . $tblAddress->getTblCity()->getDistrict() . '<br>';
                                    $this->tblAdressRowCount++;
                                }
                                if ($tblAddress->getStreetName())
                                {
                                    $Data['Student']['CompanyAddress'] .= $tblAddress->getStreetName()
                                        .' '.$tblAddress->getStreetNumber().'<br>';
                                    $this->tblAdressRowCount++;
                                }
                                if ($tblAddress->getTblCity()->getCode())
                                {
                                    $Data['Student']['CompanyAddress'] .= $tblAddress->getTblCity()->getCode() .
                                        ' ' . $tblAddress->getTblCity()->getName();
                                    $this->tblAdressRowCount++;
                                }

//                                $Data['Student']['CompanyAddress'] = ($tblAddress->getTblCity()->getDistrict() ? 'OT ' . $tblAddress->getTblCity()->getDistrict() . '<br>' : '')
//                                    . $tblAddress->getStreetName()
//                                    . ' ' . $tblAddress->getStreetNumber()
//                                    . ',<br>' . $tblAddress->getTblCity()->getCode()
//                                    . ' ' . $tblAddress->getTblCity()->getName();
//
//                                    $tblAddress->getGuiTwoRowString();
                            }
                            if ($tblCompany->getName()) {
                                $Data['Student']['Company'] = $tblCompany->getName();
                                $this->tblAdressRowCount++;
                            }
                            if ($tblCompany->getExtendedName()) {
                                $Data['Student']['Company2'] = $tblCompany->getExtendedName();
                                $this->tblAdressRowCount++;
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
            $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
            if ($tblStudent) {
                if (($tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS'))) {
                    if (($tblTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                        $tblTransferType))
                    ) {
                        $tblCompany = $tblTransfer->getServiceTblCompany();
                        $tblSchoolType = $tblTransfer->getServiceTblType();
                        if ($tblCompany && $tblSchoolType) {
                            $tblSchool = School::useService()->getSchoolByCompanyAndType($tblCompany, $tblSchoolType);
                            if ($tblSchool) {
                                // fill found information (School)
                                $Data['Responsibility']['Company']['Number'] = School::useService()->getCompanyNumber($tblSchool);
                            }
                        }
                    }
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
                        if (!isset($Data['Person']['Parent']['Mother']['Name'])) {
                            $Data['Person']['Parent']['Mother']['Name']['First'] = $tblFromPerson->getFirstSecondName();
                            $Data['Person']['Parent']['Mother']['Name']['Last'] = $tblFromPerson->getLastName();
                            $Data['Person']['Parent']['Mother']['Name']['LastFirst'] = $tblFromPerson->getLastFirstName();
                            $tblAddress = Address::useService()->getAddressByPerson($tblFromPerson);
                            if ($tblAddress) {
                                $Data['Person']['Parent']['Mother']['Address'] = $tblAddress->getGuiString();
                            }
                        } elseif (!isset($Data['Person']['Parent']['Father']['Name'])) {
                            $Data['Person']['Parent']['Father']['Name']['First'] = $tblFromPerson->getFirstSecondName();
                            $Data['Person']['Parent']['Father']['Name']['Last'] = $tblFromPerson->getLastName();
                            $Data['Person']['Parent']['Father']['Name']['LastFirst'] = $tblFromPerson->getLastFirstName();
                            $tblAddress = Address::useService()->getAddressByPerson($tblFromPerson);
                            if ($tblAddress) {
                                $Data['Person']['Parent']['Father']['Address'] = $tblAddress->getGuiString();
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
            if (($tblMailList = Mail::useService()->getMailAllByPerson($this->getTblPerson()))) {
                if ($tblMailList) {
                    $list = array();
                    foreach ($tblMailList as $tblMailToPerson) {
                        $list[] = $tblMailToPerson->getTblMail()->getAddress();
                    }
                    if (!empty($list)) {
                        $Data['Person']['Contact']['Mail'] = implode(', ', $list);
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
    private function allocatePersonContactPhonePrivate(&$Data)
    {

        $PhoneNumber = array();

        if ($this->getTblPerson()) {
            if (( $tblPhoneList = Phone::useService()->getPhoneAllByPerson($this->getTblPerson()) )) {
                if ($tblPhoneList) {
                    array_walk($tblPhoneList, function (TblToPerson $tblPhoneToPerson) use (&$PhoneNumber) {
                        if ($tblPhoneToPerson->getTblType()->getName() == 'Privat') {
                            $Description = $tblPhoneToPerson->getTblType()->getDescription();
                            if ($Description == 'Festnetz') {
                                $PhoneNumber[] = $tblPhoneToPerson->getTblPhone()->getNumber();
                            }
                        }
                    });
                    array_walk($tblPhoneList, function (TblToPerson $tblPhoneToPerson) use (&$PhoneNumber) {
                        if ($tblPhoneToPerson->getTblType()->getName() == 'Privat') {
                            $Description = $tblPhoneToPerson->getTblType()->getDescription();
                            if ($Description == 'Mobil') {
                                $PhoneNumber[] = $tblPhoneToPerson->getTblPhone()->getNumber();
                            }
                        }
                    });

                    $PhoneString = '';
                    if (!empty( $PhoneNumber )) {
                        $PhoneString = $PhoneNumber[0].( isset( $PhoneNumber[1] ) ? ', '.$PhoneNumber[1] : '' );
                    }
                    $Data['Person']['Contact']['Phone']['Number'] = $PhoneString;
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
    private function allocatePersonContactPhoneEmergency(&$Data)
    {

        $countNumbers = 0;
        if ($this->getTblPerson()) {
            if (($tblPhoneList = Phone::useService()->getPhoneAllByPerson($this->getTblPerson()))) {
                if ($tblPhoneList) {
                    foreach ($tblPhoneList as $tblPhoneToPerson) {
                        if ($tblPhoneToPerson->getTblType()->getName() == 'Notfall') {
                            $countNumbers++;
                            $remark = $tblPhoneToPerson->getRemark();
                            $Data['Person']['Contact']['Phone']['Emergency' . $countNumbers]
                                = $tblPhoneToPerson->getTblPhone()->getNumber() . ($remark ? ' (' . trim($remark) . ')' : '');
                        }
                    }
                }
            }
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
                        if ($tblPhoneToPerson->getTblType()->getName() == 'Notfall') {
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
                ->styleHeight('7px')
            )
        )
        ->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleHeight('10px')
                , '1.2%')
            ->addElementColumn((new Element())
                ->setContent($content)
                ->styleHeight('14px')
                ->styleTextSize('8.5')
                ->stylePaddingLeft('1.2px')
                ->stylePaddingTop('-2px')
                ->stylePaddingBottom('-2px')
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
            $column = (new Element\Image($picturePath, $with, $height))->stylePaddingTop(
                (($this->tblAdressRowCount * 8 + 32) - (substr($height, 0, strlen($height) - 2) / 2)) . 'px');
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