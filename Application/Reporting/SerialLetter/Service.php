<?php

namespace SPHERE\Application\Reporting\SerialLetter;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Reporting\SerialLetter\Service\Data;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblAddressPerson;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblSerialLetter;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblSerialPerson;
use SPHERE\Application\Reporting\SerialLetter\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Extension\Repository\Debugger;
use SPHERE\System\Extension\Repository\Sorter\StringGermanOrderSorter;

class Service extends AbstractService
{

    /**
     * @param bool $Simulate
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($Simulate, $withData)
    {

        $Protocol = ( new Setup($this->getStructure()) )->setupDatabaseSchema($Simulate);
        if (!$Simulate && $withData) {
            ( new Data($this->getBinding()) )->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblSerialLetter
     */
    public function getSerialLetterById($Id)
    {

        return ( new Data($this->getBinding()) )->getSerialLetterById($Id);
    }

    /**
     * @param string $Name
     *
     * @return false|TblSerialLetter
     */
    public function getSerialLetterByName($Name)
    {

        return ( new Data($this->getBinding()) )->getSerialLetterByName($Name);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblSerialPerson
     */
    public function getSerialPersonById($Id)
    {

        return ( new Data($this->getBinding()) )->getSerialPersonById($Id);
    }

    /**
     * @return bool|TblSerialLetter[]
     */
    public function getSerialLetterAll()
    {

        return ( new Data($this->getBinding()) )->getSerialLetterAll();
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return int
     */
    public function getSerialLetterCount(TblSerialLetter $tblSerialLetter)
    {

        $result = 0;
        $Data = array();
        $tblSerialLetterAddressPersonList = SerialLetter::useService()->getAddressPersonAllBySerialLetter($tblSerialLetter);
        $tblPersonList = array();
        if ($tblSerialLetterAddressPersonList) {
            foreach ($tblSerialLetterAddressPersonList as $tblAddressToPerson) {
                $result++;
                $tblPerson = $tblAddressToPerson->getServiceTblPerson();
                if ($tblPerson) {
                    $tblPersonList[$tblPerson->getId()] = $tblAddressToPerson->getServiceTblPerson();
                }
            }
        }
        if (!empty( $tblPersonList )) {
            /**@var TblPerson $tblPerson */
            foreach ($tblPersonList as $tblPerson) {
                if ($tblPerson) {
                    $tblAddressPersonList =
                        SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter, $tblPerson);
                    if ($tblAddressPersonList) {
                        foreach ($tblAddressPersonList as $tblAddressPerson) {
                            if (( $tblServiceTblToPerson = $tblAddressPerson->getServiceTblToPerson() )) {
                                if (( $tblAddress = $tblServiceTblToPerson->getTblAddress() )) {

//                                    Debugger::screenDump($tblAddress);
                                    if (isset( $Data[$tblPerson->getId().$tblAddress->getId()] )) {
                                        $result--;
                                    }
                                    $Data[$tblPerson->getId().$tblAddress->getId()] = true;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $result;
//        return ( new Data($this->getBinding()) )->getSerialLetterCount($tblSerialLetter);
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return false|TblSerialPerson[]
     */
    public function getSerialPersonBySerialLetter(TblSerialLetter $tblSerialLetter)
    {

        return ( new Data($this->getBinding()) )->getSerialPersonBySerialLetter($tblSerialLetter);
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return false|TblPerson[]
     */
    public function getPersonAllBySerialLetter(TblSerialLetter $tblSerialLetter)
    {
        return ( new Data($this->getBinding()) )->getPersonBySerialLetter($tblSerialLetter);
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param TblPerson       $tblPerson
     *
     * @return bool|TblAddressPerson[]
     */
    public function getAddressPersonAllByPerson(
        TblSerialLetter $tblSerialLetter,
        TblPerson $tblPerson
    ) {

        return ( new Data($this->getBinding()) )->getAddressPersonAllByPerson($tblSerialLetter, $tblPerson);
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return bool|TblAddressPerson[]
     */
    public function getAddressPersonAllBySerialLetter(TblSerialLetter $tblSerialLetter)
    {

        return ( new Data($this->getBinding()) )->getAddressPersonAllBySerialLetter($tblSerialLetter);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param array               $SerialLetter
     *
     * @return IFormInterface|string
     */
    public function createSerialLetter(IFormInterface $Stage = null, $SerialLetter)
    {

        /**
         * Skip to Frontend
         */
        if (null === $SerialLetter) {
            return $Stage;
        }

        $Error = false;
        if (isset( $SerialLetter['Name'] ) && empty( $SerialLetter['Name'] )) {
            $Stage->setError('SerialLetter[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        } else {
            if (SerialLetter::useService()->getSerialLetterByName($SerialLetter['Name'])) {
                $Stage->setError('SerialLetter[Name]', 'Der Name für den Serienbrief exisitert bereits. Bitte wählen Sie einen anderen.');
                $Error = true;
            }
        }

        if (!$Error) {
            ( new Data($this->getBinding()) )->createSerialLetter(
                $SerialLetter['Name'],
                $SerialLetter['Description']
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success().' Die Adressliste für Serienbriefe ist erfasst worden')
            .new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_SUCCESS);
        }

        return $Stage;
    }


    /**
     * @param IFormInterface  $Form
     * @param TblSerialLetter $tblSerialLetter
     * @param TblPerson       $tblPerson
     * @param array           $Check
     * @param string          $Route
     *
     * @return IFormInterface|string
     */
    public function setPersonAddressSelection(
        IFormInterface $Form,
        TblSerialLetter $tblSerialLetter,
        TblPerson $tblPerson,
        $Check,
        $Route = '/Reporting/SerialLetter/Address'
    ) {

        // Get Submit Info
        $Global = $this->getGlobal();

        /**
         * Skip to Frontend
         */
        if (null === $Check && !isset( $Global->POST['Button'] )) {
            return $Form;
        }

        if (!empty( $Check )) {
            foreach ($Check as $personId => $list) {
                // alle Einträge zum Serienbrief dieser Person löschen
                ( new Data($this->getBinding()) )->destroyAddressPersonAllBySerialLetterAndPerson($tblSerialLetter, $tblPerson);
                if (is_array($list) && !empty( $list )) {
                    foreach ($list as $key => $item) {
                        if (isset( $item['Address'] )) {
                            $tblToPerson = Address::useService()->getAddressToPersonById($key);
                            if ($tblToPerson && $tblToPerson->getServiceTblPerson()) {
                                if ($tblPersonToPerson = $tblToPerson->getServiceTblPerson()) {
                                    $tblSalutation = $tblPersonToPerson->getTblSalutation();

                                    $this->createAddressPerson($tblSerialLetter, $tblPerson,
                                        $tblToPerson->getServiceTblPerson(), $tblToPerson,
                                        ( $tblSalutation ? $tblSalutation : null ));
                                } else {
                                    $this->createAddressPerson($tblSerialLetter, $tblPerson,
                                        $tblToPerson->getServiceTblPerson(), $tblToPerson,
                                        null);
                                }
                            }
                        }
                    }
                }
                return new Success('Erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                .new Redirect($Route, Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $tblSerialLetter->getId(), 'PersonId' => $tblPerson->getId()));
            }
        } else {
            ( new Data($this->getBinding()) )->destroyAddressPersonAllBySerialLetterAndPerson($tblSerialLetter, $tblPerson);
        }

        return new Success('Erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
        .new Redirect($Route, Redirect::TIMEOUT_SUCCESS,
            array('Id' => $tblSerialLetter->getId()));
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return Warning|string
     */
    public function createAddressPersonSelf(TblSerialLetter $tblSerialLetter)
    {
        $tblSerialPersonList = SerialLetter::useService()->getSerialPersonBySerialLetter($tblSerialLetter);
        if ($tblSerialPersonList) {
            foreach ($tblSerialPersonList as $tblSerialPerson) {
                $tblPerson = $tblSerialPerson->getServiceTblPerson();
                if ($tblPerson) {
                    // Nur Personen die noch keine Adressen haben
                    if (!SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter, $tblPerson)) {
                        $tblToPersonList = Address::useService()->getAddressAllByPerson($tblPerson);
                        if ($tblToPersonList) {
                            $tblType = Address::useService()->getTypeById(1);
                            $tblToPersonChoose = null;
                            // Ziehen der ersten Hauptadresse (die aktuellste)
                            foreach ($tblToPersonList as $tblToPerson) {
                                if ($tblToPerson->getTblType()->getId() === $tblType->getId() && $tblToPersonChoose === null) {
                                    $tblToPersonChoose = $tblToPerson;
                                }
                            }
                            // Ziehen irgendeiner Adresse
                            if ($tblToPersonChoose === null) {
                                foreach ($tblToPersonList as $tblToPerson) {
                                    $tblToPersonChoose = $tblToPerson;
                                }
                            }
                            $tblSalutation = $tblPerson->getTblSalutation();
                            if (!$tblSalutation) {
                                $tblSalutation = null;
                            }
                            SerialLetter::useService()->createAddressPerson($tblSerialLetter, $tblPerson, $tblPerson, $tblToPersonChoose, $tblSalutation);
                        }
                    }
                }
            }
        } else {
            return new Warning('Es sind keine Personen im Serienbrief hinterlegt');
        }
        return new Success('Mögliche Adressenzuweisungen wurde vorgenommen')
        .new Redirect('/Reporting/SerialLetter/Address', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblSerialLetter->getId()));
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return Warning|string
     */
    public function createAddressPersonGuardian(TblSerialLetter $tblSerialLetter)
    {
        $tblSerialPersonList = SerialLetter::useService()->getSerialPersonBySerialLetter($tblSerialLetter);
        if ($tblSerialPersonList) {
            foreach ($tblSerialPersonList as $tblSerialPerson) {
                $tblPerson = $tblSerialPerson->getServiceTblPerson();
                if ($tblPerson) {
                    // Nur Personen die noch keine Adressen haben
                    if (!SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter, $tblPerson)) {
                        $tblGuardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                        if ($tblGuardianList) {
                            $tblTypeRelationship = Relationship::useService()->getTypeByName('Sorgeberechtigt');
                            $GuardianList = array();
                            /** @var \SPHERE\Application\People\Relationship\Service\Entity\TblToPerson $tblGuardian */
                            foreach ($tblGuardianList as $tblGuardian) {
                                // Alle Sorgeberechtiget
                                if ($tblTypeRelationship->getId() === $tblGuardian->getTblType()->getId()) {
                                    if ($tblPerson->getId() !== $tblGuardian->getServiceTblPersonFrom()->getId()) {
                                        $GuardianList[] = $tblGuardian->getServiceTblPersonFrom();
                                    }
                                }
                            }
                            $Person = null;
                            $ToPersonChooseList = array();
                            $SalutationList = array();
                            /** @var TblPerson[] $GuardianList */
                            if (!empty( $GuardianList )) {
                                // Alle Sorgeberechtigten
                                foreach ($GuardianList as $Parent) {
                                    $tblToPersonList = Address::useService()->getAddressAllByPerson($Parent);
                                    if ($tblToPersonList) {
                                        $tblType = Address::useService()->getTypeById(1);
                                        $tblToPersonChoose = null;
                                        // Ziehen der ersten Hauptadresse
                                        foreach ($tblToPersonList as $tblToPerson) {
                                            if ($tblToPerson->getTblType()->getId() === $tblType->getId() && $tblToPersonChoose === null) {
                                                $ToPersonChooseList[] = $tblToPerson;
                                                $tblSalutation = $Parent->getTblSalutation();
                                                $SalutationList[] = $tblSalutation;
                                                $Person[] = $Parent;
                                            }
                                        }
                                    }
                                }

                                /** @var TblToPerson[] $ToPersonChooseList */
                                if (!empty( $ToPersonChooseList )) {

                                    $count = 0;
                                    foreach ($ToPersonChooseList as $ToPersonChoose) {

                                        $tblToPersonChoose = $ToPersonChoose;
                                        if (isset( $SalutationList[$count] )) {
                                            $tblSalutation = $SalutationList[$count];
                                        } else {
                                            $tblSalutation = false;
                                        }
                                        if (isset( $SalutationList[$count] )) {
                                            $PersonTo = $Person[$count];
                                        } else {
                                            $PersonTo = false;
                                        }
                                        SerialLetter::useService()->createAddressPerson(
                                            $tblSerialLetter, $tblPerson, $PersonTo, $tblToPersonChoose, ( $tblSalutation ? $tblSalutation : null ));
                                        $count++;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            return new Warning('Es sind keine Personen im Serienbrief hinterlegt');
        }
        return new Success('Mögliche Adressenzuweisungen wurde vorgenommen')
        .new Redirect('/Reporting/SerialLetter/Address', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblSerialLetter->getId()));
    }

    /**
     * @param TblSerialLetter    $tblSerialLetter
     * @param TblPerson          $tblPerson
     * @param TblPerson          $tblPersonToAddress
     * @param TblToPerson        $tblToPerson
     * @param TblSalutation|null $tblSalutation
     *
     * @return TblAddressPerson
     */
    public function createAddressPerson(
        TblSerialLetter $tblSerialLetter,
        TblPerson $tblPerson,
        TblPerson $tblPersonToAddress,
        TblToPerson $tblToPerson,
        TblSalutation $tblSalutation = null
    ) {

        return ( new Data($this->getBinding()) )->createAddressPerson($tblSerialLetter, $tblPerson, $tblPersonToAddress,
            $tblToPerson, $tblSalutation);
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return bool|\SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     */
    public function createSerialLetterExcel(TblSerialLetter $tblSerialLetter)
    {

        $tblPersonList = $this->getPersonAllBySerialLetter($tblSerialLetter);
        $ExportData = array();
        $AddressPersonCount = 1;
        if ($tblPersonList) {
            $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy('LastFirstName', new StringGermanOrderSorter());
            /** @var TblPerson $tblPerson */
            foreach ($tblPersonList as $tblPerson) {
                $tblAddressPersonAllByPerson = SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter,
                    $tblPerson);
                if ($tblAddressPersonAllByPerson) {
                    /** @var TblAddressPerson $tblAddressPerson */
                    $AddressList = array();
                    array_walk($tblAddressPersonAllByPerson, function (TblAddressPerson $tblAddressPerson) use (&$AddressList, $tblPerson) {
                        if (( $serviceTblPersonToAddress = $tblAddressPerson->getServiceTblToPerson() )) {
                            if (( $tblToPerson = $tblAddressPerson->getServiceTblToPerson() )) {
                                if (( $PersonToAddress = $tblToPerson->getServiceTblPerson() )) {
                                    if (( $tblAddress = $serviceTblPersonToAddress->getTblAddress() )) {
                                        //Person SerialLetter
                                        $AddressList[$tblPerson->getId().$tblAddress->getId()]['Salutation'] =
                                            $tblPerson->getSalutation();
                                        $AddressList[$tblPerson->getId().$tblAddress->getId()]['FirstName'] =
                                            $tblPerson->getFirstName();
                                        $AddressList[$tblPerson->getId().$tblAddress->getId()]['LastName'] =
                                            $tblPerson->getLastName();

                                        //Person Address
                                        $AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonSalutation'][] =
                                            $PersonToAddress->getSalutation();
                                        $AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonFirstName'][] =
                                            $PersonToAddress->getFirstName();
                                        $AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonLastName'][] =
                                            $PersonToAddress->getLastName();

                                        if (( $tblAddress = $tblAddressPerson->getServiceTblToPerson()->getTblAddress() )) {
                                            $AddressList[$tblPerson->getId().$tblAddress->getId()]['Street'] =
                                                $tblAddress->getStreetName();
                                            $AddressList[$tblPerson->getId().$tblAddress->getId()]['Number'] =
                                                $tblAddress->getStreetNumber();;
                                            if (( $tblCity = $tblAddress->getTblCity() )) {
                                                $AddressList[$tblPerson->getId().$tblAddress->getId()]['District'] =
                                                    $tblCity->getDistrict();
                                                $AddressList[$tblPerson->getId().$tblAddress->getId()]['Code'] =
                                                    $tblCity->getCode();
                                                $AddressList[$tblPerson->getId().$tblAddress->getId()]['City'] =
                                                    $tblCity->getName();
                                            }
                                        }
                                        // ToDO Füllen

                                    }

                                    $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                                    if ($tblStudent) {
                                        $StudentNumber = $tblStudent->getIdentifier();
                                    } else {
                                        $StudentNumber = new Small(new Muted('-NA-'));
                                    }

                                    $AddressList[$tblPerson->getId().$tblAddress->getId()]['Person'] =
                                        ( $tblAddressPerson->getServiceTblPerson()
                                            ? $tblAddressPerson->getServiceTblPerson()->getLastFirstName()
                                            : new Warning(new Exclamation().' Person nicht gefunden.') );
                                    $AddressList[$tblPerson->getId().$tblAddress->getId()]['StudentNumber'] = $StudentNumber;
                                    $AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonToAddress'] =
                                        $AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonToWrite'];
                                    $AddressList[$tblPerson->getId().$tblAddress->getId()]['Address'] =
                                        ( $tblAddressPerson->getServiceTblToPerson()
                                            ? $tblAddressPerson->getServiceTblToPerson()->getTblAddress()->getGuiString()
                                            : new Warning(new Exclamation().' Adresse nicht gefunden.') );
                                    $AddressList[$tblPerson->getId().$tblAddress->getId()]['Salutation'] =
                                        isset( $AddressList[$tblPerson->getId().$tblAddress->getId()]['SalutationList'] )
                                        && $AddressList[$tblPerson->getId().$tblAddress->getId()]['SalutationList'] !== ''
                                            ? $AddressList[$tblPerson->getId().$tblAddress->getId()]['SalutationList']
                                            : new Warning(new Exclamation().' Keine Anrede hinterlegt.');
                                    $AddressList[$tblPerson->getId().$tblAddress->getId()]['Option'] =
                                        new Standard('', '/Reporting/SerialLetter/Address/Edit', new Edit(),
                                            array('Id'       => $Id,
                                                  'PersonId' => $tblPerson->getId(),
                                                  'Route'    => '/Reporting/SerialLetter/Export'));
                                }
                            }
                        }
                    });
                    if ($AddressList) {
                        foreach ($AddressList as $Address) {
                            $countAddresses++;
                            $dataList[] = array(
                                'Number'          => ++$count,
                                'Person'          => ( isset( $Address['Person'] ) ? $Address['Person'] : '' ),
                                'StudentNumber'   => ( isset( $Address['StudentNumber'] ) ? $Address['StudentNumber'] : '' ),
                                'PersonToAddress' => ( isset( $Address['PersonToAddress'] ) ? $Address['PersonToAddress'] : '' ),
                                'Address'         => ( isset( $Address['Address'] ) ? $Address['Address'] : '' ),
                                'Salutation'      => ( isset( $Address['Salutation'] ) ? $Address['Salutation'] : '' ),
                                'Option'          => ( isset( $Address['Option'] ) ? $Address['Option'] : '' )
                            );
                        }
                    }
                } else {
                    $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                    if ($tblStudent) {
                        $StudentNumber = $tblStudent->getIdentifier();
                    } else {
                        $StudentNumber = new Small(new Muted('-NA-'));
                    }

                    $dataList[] = array(
                        'Number'          => ++$count,
                        'Person'          => $tblPerson->getLastFirstName(),
                        'StudentNumber'   => $StudentNumber,
                        'PersonToAddress' => new Warning(new Exclamation().' Keine Person mit Adresse hinterlegt.'),
                        'Address'         => '',
                        'Salutation'      => '',
                        'Option'          => new Standard('', '/Reporting/SerialLetter/Address/Edit', new Edit(),
                            array('Id'       => $Id,
                                  'PersonId' => $tblPerson->getId(),
                                  'Route'    => '/Reporting/SerialLetter/Export'))
                    );
                }
            }
        }

        Debugger::screenDump($ExportData);
        exit;

//        if (!empty($tblPersonList)) {
//
//            $row = 0;
//            $column = 0;
//            $fileLocation = Storage::createFilePointer('xlsx');
//            /** @var PhpExcel $export */
//            $export = Document::getDocument($fileLocation->getFileLocation());
//            $export->setValue($export->getCell($column++, $row), "Anrede");
//            $export->setValue($export->getCell($column++, $row), "Vorname");
//            $export->setValue($export->getCell($column++, $row), "Nachname");
//            $export->setValue($export->getCell($column++, $row), "Ortsteil");
//            $export->setValue($export->getCell($column++, $row), "Adresse 1");
//            $export->setValue($export->getCell($column++, $row), "PLZ");
//            $export->setValue($export->getCell($column++, $row), "Ort");
//            $export->setValue($export->getCell($column++, $row), "Person_Vorname");
//            $export->setValue($export->getCell($column++, $row), "Person_Nachname");
//            $export->setValue($export->getCell($column, $row), "Schüler-Nr.");
//
//            $row = 1;
//            /** @var TblAddressPerson $tblAddressPerson */
//            foreach ($tblPersonList as $tblAddressPerson) {
//                if ($tblAddressPerson->getServiceTblPerson()
//                    && $tblAddressPerson->getServiceTblPersonToAddress()
//                    && $tblAddressPerson->getServiceTblToPerson()
//                ) {
//                    $column = 0;
//                    $export->setValue($export->getCell($column++, $row),
//                        $tblAddressPerson->getServiceTblSalutation() ? $tblAddressPerson->getServiceTblSalutation()->getSalutation() : '');
//                    $export->setValue($export->getCell($column++, $row),
//                        $tblAddressPerson->getServiceTblPersonToAddress()->getFirstName());
//                    $export->setValue($export->getCell($column++, $row),
//                        $tblAddressPerson->getServiceTblPersonToAddress()->getLastName());
//                    $tblAddress = $tblAddressPerson->getServiceTblToPerson()->getTblAddress();
//                    $export->setValue($export->getCell($column++, $row),
//                        $tblAddress->getTblCity()->getDistrict());
//                    $export->setValue($export->getCell($column++, $row),
//                        $tblAddress->getStreetName().' '.$tblAddress->getStreetNumber());
//                    $export->setValue($export->getCell($column++, $row), $tblAddress->getTblCity()->getCode());
//                    $export->setValue($export->getCell($column++, $row), $tblAddress->getTblCity()->getName());
//                    $export->setValue($export->getCell($column++, $row),
//                        $tblAddressPerson->getServiceTblPerson()->getFirstName());
//                    $export->setValue($export->getCell($column++, $row),
//                        $tblAddressPerson->getServiceTblPerson()->getLastName());
//                    $tblStudent = Student::useService()->getStudentByPerson($tblAddressPerson->getServiceTblPerson());
//                    if ($tblStudent) {
//                        $export->setValue($export->getCell($column, $row),
//                            $tblStudent->getIdentifier());
//                    } else {
//                        $export->setValue($export->getCell($column, $row), '');
//                    }
//                    $row++;
//                }
//            }
//
//            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
//
//            return $fileLocation;
//        }
//        return false;
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param TblPerson       $tblPerson
     *
     * @return null|object|TblSerialPerson
     */
    public function addSerialPerson(TblSerialLetter $tblSerialLetter, TblPerson $tblPerson)
    {

        return ( new Data($this->getBinding()) )->addSerialPerson($tblSerialLetter, $tblPerson);
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param TblPerson       $tblPerson
     *
     * @return bool
     */
    public function removeSerialPerson(TblSerialLetter $tblSerialLetter, TblPerson $tblPerson)
    {

        return ( new Data($this->getBinding()) )->removeSerialPerson($tblSerialLetter, $tblPerson);
    }

    /**
     * @param TblSerialPerson $tblSerialPerson
     *
     * @return bool
     */
    public function destroySerialPerson(TblSerialPerson $tblSerialPerson)
    {

        return ( new Data($this->getBinding()) )->destroySerialPerson($tblSerialPerson);
    }

    /**
     * @param TblAddressPerson $tblAddressPerson
     *
     * @return bool
     */
    public function destroySerialAddressPerson(TblAddressPerson $tblAddressPerson)
    {
        return ( new Data($this->getBinding()) )->destroyAddressPerson($tblAddressPerson);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblSerialLetter     $tblSerialLetter
     * @param array               $SerialLetter
     *
     * @return IFormInterface|string
     */
    public function updateSerialLetter(
        IFormInterface $Stage = null,
        TblSerialLetter $tblSerialLetter,
        $SerialLetter = null
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $SerialLetter) {
            return $Stage;
        }

        $Error = false;
        if (isset( $SerialLetter['Name'] ) && empty( $SerialLetter['Name'] )) {
            $Stage->setError('SerialLetter[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        } else {
            if (( $tblSerialLetterByName = SerialLetter::useService()->getSerialLetterByName($SerialLetter['Name']) )) {
                if ($tblSerialLetterByName->getId() !== $tblSerialLetter->getId()) {
                    $Stage->setError('SerialLetter[Name]', 'Der Name für den Serienbrief exisitert bereits. Bitte wählen Sie einen anderen');
                    $Error = true;
                }
            }
        }

        if (!$Error) {
            ( new Data($this->getBinding()) )->updateSerialLetter(
                $tblSerialLetter,
                $SerialLetter['Name'],
                $SerialLetter['Description']
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success().' Die Adressliste für Serienbriefe ist geändert worden')
            .new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_SUCCESS);
        }

        return $Stage;
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return bool
     */
    public function destroySerialLetter(TblSerialLetter $tblSerialLetter)
    {

        $tblSerialPersonList = SerialLetter::useService()->getSerialPersonBySerialLetter($tblSerialLetter);
        if ($tblSerialPersonList) {
            foreach ($tblSerialPersonList as $tblSerialPerson) {
                $tblPerson = $tblSerialPerson->getServiceTblPerson();
                if ($tblPerson) {
                    // Destroy Address
                    SerialLetter::useService()->destroyAddressPersonAllBySerialLetterAndPerson($tblSerialLetter, $tblPerson);
                }
                // Destroy SerialPerson
                SerialLetter::useService()->destroySerialPerson($tblSerialPerson);
            }
        }
        // Destroy SerialLetter
        return ( new Data($this->getBinding()) )->destroySerialLetter($tblSerialLetter);
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return bool
     */
    public function removeSerialLetterAddress(TblSerialLetter $tblSerialLetter)
    {
        $tblSerialPersonList = SerialLetter::useService()->getSerialPersonBySerialLetter($tblSerialLetter);
        if ($tblSerialPersonList) {
            foreach ($tblSerialPersonList as $tblSerialPerson) {
                $tblPerson = $tblSerialPerson->getServiceTblPerson();
                if ($tblPerson) {
                    // Destroy Address
                    SerialLetter::useService()->destroyAddressPersonAllBySerialLetterAndPerson($tblSerialLetter, $tblPerson);
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param TblPerson       $tblPerson
     *
     * @return bool
     */
    public function destroyAddressPersonAllBySerialLetterAndPerson(TblSerialLetter $tblSerialLetter, TblPerson $tblPerson)
    {

        return ( new Data($this->getBinding()) )->destroyAddressPersonAllBySerialLetterAndPerson($tblSerialLetter, $tblPerson);
    }
}