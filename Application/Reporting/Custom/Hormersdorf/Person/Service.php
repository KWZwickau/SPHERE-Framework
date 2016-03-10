<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 25.01.2016
 * Time: 15:47
 */

namespace SPHERE\Application\Reporting\Custom\Hormersdorf\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Document\Explorer\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Window\Redirect;

class Service
{
    /**
     * @return bool|\SPHERE\Application\People\Person\Service\Entity\TblPerson[]
     */
    public function createStaffList()
    {

        $staffList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByName('Mitarbeiter'));

        if (!empty( $staffList )) {
            foreach ($staffList as $tblPerson) {

                $tblPerson->Name = $tblPerson->getLastFirstName();
                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $tblPerson->Birthday = $common->getTblCommonBirthDates()->getBirthday();
                } else {
                    $tblPerson->Birthday = '';
                }
            }
        }

        return $staffList;
    }

    /**
     * @param $staffList
     *
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createStaffListExcel($staffList)
    {

        if (!empty( $staffList )) {

            $fileLocation = Storage::useWriter()->getTemporary('xls');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Name");
            $export->setValue($export->getCell("1", "0"), "Geburtstag");

            $Row = 1;
            foreach ($staffList as $tblPerson) {

                $export->setValue($export->getCell("0", $Row), $tblPerson->Name);
                $export->setValue($export->getCell("1", $Row), $tblPerson->Birthday);

                $Row++;
            }

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param null                $Select
     * @param string              $Redirect
     *
     * @return IFormInterface|Redirect
     */
    public function getClass(IFormInterface $Stage = null, $Select = null, $Redirect)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Select) {
            return $Stage;
        }

        $tblDivision = Division::useService()->getDivisionById($Select['Division']);

        if ($tblDivision) {
            return new Redirect($Redirect, Redirect::TIMEOUT_SUCCESS, array(
                'DivisionId' => $tblDivision->getId(),
            ));
        } else {
            return new Danger('Klasse nicht gefunden.', new Ban());
        }
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return bool|\SPHERE\Application\People\Person\Service\Entity\TblPerson[]
     */
    public function createClassList(TblDivision $tblDivision)
    {

        $studentList = Division::useService()->getStudentAllByDivision($tblDivision);

        if (!empty( $studentList )) {
            foreach ($studentList as $tblPerson) {
                $father = null;
                $mother = null;
                $fatherPhoneList = false;
                $motherPhoneList = false;
                $guardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($guardianList) {
                    foreach ($guardianList as $guardian) {
                        if ($guardian->getTblType()->getId() == 1) {
                            if ($father === null) {
                                $father = $guardian->getServiceTblPersonFrom();
                                if ($father) {
                                    $fatherPhoneList = Phone::useService()->getPhoneAllByPerson($father);
                                }
                            } else {
                                $mother = $guardian->getServiceTblPersonFrom();
                                if ($mother) {
                                    $motherPhoneList = Phone::useService()->getPhoneAllByPerson($mother);
                                }
                            }
                        }
                    }
                }

                if (( $addressList = Address::useService()->getAddressAllByPerson($tblPerson) )) {
                    $address = $addressList[0];
                } else {
                    $address = null;
                }

                $tblPerson->FatherName = $father !== null ? ( $tblPerson->getLastName() == $father->getLastName()
                    ? $father->getFirstSecondName() : $father->getFirstSecondName().' '.$father->getLastName() ) : '';
                $tblPerson->MotherName = $mother !== null ? ( $tblPerson->getLastName() == $mother->getLastName()
                    ? $mother->getFirstSecondName() : $mother->getFirstSecondName().' '.$mother->getLastName() ) : '';
                $tblPerson->DisplayName = $tblPerson->getLastFirstName()
                    .( $father !== null || $mother !== null ? '<br>('.( $mother !== null ? $tblPerson->MotherName
                            .( $father !== null ? ', ' : '' ) : '' )
                        .( $father !== null ? $tblPerson->FatherName : '' ).')' : '' );

                $tblPerson->ExcelNameRow1 = $tblPerson->getLastFirstName();
                if ($father !== null || $mother !== null) {
                    $tblPerson->ExcelNameRow2 = '('.( $mother !== null ? $tblPerson->MotherName
                            .( $father !== null ? ', ' : '' ) : '' )
                        .( $father !== null ? $tblPerson->FatherName : '' ).')';
                } else {
                    $tblPerson->ExcelNameRow2 = '';
                }

                if ($address !== null) {
                    $tblPerson->Address = $address->getTblAddress()->getStreetName().' '.
                        $address->getTblAddress()->getStreetNumber().'<br>'.
                        $address->getTblAddress()->getTblCity()->getCode().' '.
                        $address->getTblAddress()->getTblCity()->getName() . ' ' .
                        $address->getTblAddress()->getTblCity()->getDistrict();
                    $tblPerson->ExcelAddressRow1 = $address->getTblAddress()->getStreetName().' '.
                        $address->getTblAddress()->getStreetNumber();
                    $tblPerson->ExcelAddressRow2 = $address->getTblAddress()->getTblCity()->getCode().' '.
                        $address->getTblAddress()->getTblCity()->getName();
                } else {
                    $tblPerson->Address = '';
                    $tblPerson->ExcelAddressRow1 = '';
                    $tblPerson->ExcelAddressRow2 = '';
                }

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $tblPerson->Birthday = $common->getTblCommonBirthDates()->getBirthday();
                    $tblPerson->Birthplace = $common->getTblCommonBirthDates()->getBirthplace();
                } else {
                    $tblPerson->Birthday = $tblPerson->Birthplace = '';
                }

                $phoneNumbers = array();
                $phoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
                if ($phoneList) {
                    foreach ($phoneList as $phone) {
                        $phoneNumbers[] = $phone->getTblPhone()->getNumber().' '.$phone->getTblType()->getName()
                            .( $phone->getRemark() !== '' ? ' '.$phone->getRemark() : '' );
                    }
                }
                if ($fatherPhoneList) {
                    foreach ($fatherPhoneList as $phone) {
                        if ($phone->getServiceTblPerson()) {
                            $phoneNumbers[] = $phone->getTblPhone()->getNumber() . ' ' . $phone->getTblType()->getName() . ' '
                                . $phone->getServiceTblPerson()->getFullName() . ($phone->getRemark() !== '' ? ' ' . $phone->getRemark() : '');
                        }
                    }
                }
                if ($motherPhoneList) {
                    foreach ($motherPhoneList as $phone) {
                        if ($phone->getServiceTblPerson()) {
                            $phoneNumbers[] = $phone->getTblPhone()->getNumber() . ' ' . $phone->getTblType()->getName() . ' '
                                . $phone->getServiceTblPerson()->getFullName() . ($phone->getRemark() !== '' ? ' ' . $phone->getRemark() : '');
                        }
                    }
                }

                if (empty( $phoneNumbers )) {
                    $tblPerson->PhoneNumbers = '';
                } else {
                    $tblPerson->PhoneNumbers = implode('<br>', $phoneNumbers);
                    $tblPerson->ExcelPhoneNumbers = $phoneNumbers;
                }

                // ToDo JohK zusammenfassung am Ende
            }
        }

        return $studentList;
    }

    /**
     * @param $studentList
     *
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createClassListExcel($studentList)
    {

        if (!empty( $studentList )) {

            $fileLocation = Storage::useWriter()->getTemporary('xls');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Name");
            $export->setValue($export->getCell("1", "0"), "Geb.-Datum");
            $export->setValue($export->getCell("2", "0"), "Adresse");
            $export->setValue($export->getCell("3", "0"), "Telefonnummer");

            $row = 2;
            foreach ($studentList as $tblPerson) {
                $rowPerson = $row;
                $export->setValue($export->getCell("0", $row), $tblPerson->ExcelNameRow1);
                $export->setValue($export->getCell("1", $row), $tblPerson->Birthday);
                $export->setValue($export->getCell("2", $row), $tblPerson->ExcelAddressRow1);

                $row++;
                $export->setValue($export->getCell("0", $row), $tblPerson->ExcelNameRow2);
                $export->setValue($export->getCell("2", $row), $tblPerson->ExcelAddressRow2);
                $row++;

                if (!empty( $tblPerson->ExcelPhoneNumbers )) {
                    foreach ($tblPerson->ExcelPhoneNumbers as $phone) {
                        $export->setValue($export->getCell("3", $rowPerson++), $phone);
                    }
                }

                if ($rowPerson > $row) {
                    $row = $rowPerson;
                }

                $row++;
            }

            $row = $row +2;
            $export->setValue($export->getCell("0", $row++), count($studentList) . ' Schüler/innen');
            $export->setValue($export->getCell("0", $row), 'Stand ' . date("d.m.Y"));

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }
        return false;
    }

}