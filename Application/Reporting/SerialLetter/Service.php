<?php

namespace SPHERE\Application\Reporting\SerialLetter;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
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
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

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
     * @return false|int
     */
    public function getSerialLetterCount(TblSerialLetter $tblSerialLetter)
    {

        return ( new Data($this->getBinding()) )->getSerialLetterCount($tblSerialLetter);
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
    public function getPersonBySerialLetter(TblSerialLetter $tblSerialLetter)
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
     * @param array           $Check
     * @param string          $Route
     *
     * @return IFormInterface|string
     */
    public function setPersonAddressSelection(
        IFormInterface $Form,
        TblSerialLetter $tblSerialLetter,
        $Check,
        $Route = '/Reporting/SerialLetter/Address'
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Check) {
            return $Form;
        }

        if (!empty( $Check )) {
            foreach ($Check as $personId => $list) {
                $tblPerson = Person::useService()->getPersonById($personId);
                if ($tblPerson) {
                    // alle Einträge zum Serienbrief dieser Person löschen
                    ( new Data($this->getBinding()) )->destroyAddressPersonAllBySerialLetterAndPerson($tblSerialLetter, $tblPerson);
                    if (is_array($list) && !empty( $list )) {
                        foreach ($list as $key => $item) {
                            if (isset( $item['Address'] )) {
                                $tblToPerson = Address::useService()->getAddressToPersonById($key);
                                if ($tblToPerson && $tblToPerson->getServiceTblPerson()) {
                                    if (isset( $item['Salutation'] )) {
                                        if ($item['Salutation'] == TblAddressPerson::SALUTATION_FAMILY) {
                                            $tblSalutation = new TblSalutation('Familie');
                                            $tblSalutation->setId(TblAddressPerson::SALUTATION_FAMILY);
                                        } else {
                                            $tblSalutation = Person::useService()->getSalutationById($item['Salutation']);
                                        }

                                        $this->createAddressPerson($tblSerialLetter, $tblPerson,
                                            $tblToPerson->getServiceTblPerson(), $tblToPerson,
                                            $tblSalutation ? $tblSalutation : null);
                                    }
                                }
                            }
                        }
                    }
                    return new Success('Erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                    .new Redirect($Route, Redirect::TIMEOUT_SUCCESS,
                        array('Id' => $tblSerialLetter->getId(), 'PersonId' => $tblPerson->getId()));
                }
            }
        }

        return new Success('Erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
        .new Redirect($Route, Redirect::TIMEOUT_SUCCESS,
            array('Id' => $tblSerialLetter->getId()));
    }

    /**
     * @param IFormInterface  $Form
     * @param TblSerialLetter $tblSerialLetter
     * @param null            $Data
     *
     * @return IFormInterface|string
     */
    public function createAddressPersonSelf(IFormInterface $Form, TblSerialLetter $tblSerialLetter, $Data = null)
    {

        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Button'] )) {
            return $Form;
        }

        if (!isset( $Data['Salutation'] ) || $Data['Salutation'] == 0) {
            $Salutation = null;
        } elseif ($Data['Salutation'] === '1000') {
            $tblSalutation = new TblSalutation('Familie');
            $tblSalutation->setId(TblAddressPerson::SALUTATION_FAMILY);
            $Salutation = $tblSalutation;
        } else {
            $Salutation = Person::useService()->getSalutationById($Data['Salutation']);
        }

        $tblSerialPersonList = SerialLetter::useService()->getSerialPersonBySerialLetter($tblSerialLetter);
        if ($tblSerialPersonList) {
            foreach ($tblSerialPersonList as $tblSerialPerson) {
                $tblPerson = $tblSerialPerson->getServiceTblPerson();
                if ($tblPerson) {
                    // Nur Personen die noch keine Adressen haben
                    if (!SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter, $tblPerson)) {
                        if ($Salutation && $Salutation->getSalutation() == 'Schüler') {
                            if ($tblPerson->getTblSalutation() && $tblPerson->getTblSalutation()->getSalutation() == 'Schüler') {
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
                                    SerialLetter::useService()->createAddressPerson($tblSerialLetter, $tblPerson, $tblPerson, $tblToPersonChoose, $Salutation);
                                }
                            }
                        } elseif ($Salutation && $Salutation->getSalutation() == 'Frau') {
                            if ($tblPerson->getTblSalutation() && $tblPerson->getTblSalutation()->getSalutation() == 'Frau') {
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
                                    SerialLetter::useService()->createAddressPerson($tblSerialLetter, $tblPerson, $tblPerson, $tblToPersonChoose, $Salutation);
                                }
                            }
                        } elseif ($Salutation && $Salutation->getSalutation() == 'Herr') {
                            if ($tblPerson->getTblSalutation() && $tblPerson->getTblSalutation()->getSalutation() == 'Herr') {

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
                                    SerialLetter::useService()->createAddressPerson($tblSerialLetter, $tblPerson, $tblPerson, $tblToPersonChoose, $Salutation);
                                }
                            }
                        } else {
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
                                SerialLetter::useService()->createAddressPerson($tblSerialLetter, $tblPerson, $tblPerson, $tblToPersonChoose, $Salutation);
                            }
                        }
                    }
                }
            }
        } else {
            return $Form->setError('Salutation', 'Es sind keine Personen im Serienbrief hinterlegt');
        }
        return $Form.new Success('Mögliche Adressenzuweisungen wurde vorgenommen')
        .new Redirect('/Reporting/SerialLetter/Address', Redirect::TIMEOUT_SUCCESS, array('TabActive' => 'SELF',
                                                                                          'Id'        => $tblSerialLetter->getId()));
    }

    /**
     * @param IFormInterface  $Form
     * @param TblSerialLetter $tblSerialLetter
     * @param null            $Data
     *
     * @return IFormInterface|string
     */
    public function createAddressPersonGuardian(IFormInterface $Form, TblSerialLetter $tblSerialLetter, $Data = null)
    {

        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Button'] )) {
            return $Form;
        }

//        if(!isset($Data['Check'])){
//            return $Form;
//        }

        if (!isset( $Data['Salutation'] ) || $Data['Salutation'] == 0) {
            $Salutation = null;
        } elseif ($Data['Salutation'] === '1000') {
            $tblSalutation = new TblSalutation('Familie');
            $tblSalutation->setId(TblAddressPerson::SALUTATION_FAMILY);
            $Salutation = $tblSalutation;
        } else {
            $Salutation = Person::useService()->getSalutationById($Data['Salutation']);
        }
//        if($Salutation && $Salutation->getSalutation() == 'Schüler'){
//            $Form->setError('Data[Salutation]', 'Sorgeberechtiget sollten nicht mit "Schüler" angeschrieben werden');
//            return $Form;
//        }

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
                            /** @var TblPerson[] $GuardianList */
                            if (!empty( $GuardianList )) {
                                // Bezogen auf Geschlecht
//                                foreach($GuardianList as $Parent){
//                                    if(($tblCommon = $Parent->getCommon())){
//                                        if(($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())){
//                                            if(($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())){
//
//                                                if($Salutation == null
//                                                    || $Salutation && $Salutation->getSalutation() == 'Familie'){
//
//                                                    if($tblCommonGender->getName() === 'Weiblich'){
//                                                        $tblToPersonList = Address::useService()->getAddressAllByPerson($Parent);
//                                                        if($tblToPersonList) {
//                                                            $tblType = Address::useService()->getTypeById(1);
//                                                            $tblToPersonChoose = null;
//                                                            // Ziehen der ersten Hauptadresse (die aktuellste)
//                                                            foreach ($tblToPersonList as $tblToPerson) {
//                                                                if ($tblToPerson->getTblType()->getId() === $tblType->getId() && $tblToPersonChoose === null) {
//                                                                    $tblToPersonChoose = $tblToPerson;
//                                                                }
//                                                            }
//                                                            if($tblToPersonChoose){
//                                                                $ToPersonChooseList[] = $tblToPersonChoose;
//                                                                $Person = $Parent;
//                                                            }
//                                                        }
//                                                    } elseif($tblCommonGender->getName() === 'Männlich') {
//                                                        if ($tblAddress = Address::useService()->getAddressByPerson($Parent)) {
//                                                            if ($Person === null) {
//                                                                $tblToPersonList = Address::useService()->getAddressAllByPerson($Parent);
//                                                                if ($tblToPersonList) {
//                                                                    $tblType = Address::useService()->getTypeById(1);
//                                                                    $tblToPersonChoose = null;
//                                                                    // Ziehen der ersten Hauptadresse (die aktuellste)
//                                                                    foreach ($tblToPersonList as $tblToPerson) {
//                                                                        if ($tblToPerson->getTblType()->getId() === $tblType->getId() && $tblToPersonChoose === null) {
//                                                                            $tblToPersonChoose = $tblToPerson;
//                                                                        }
//                                                                    }
//                                                                    if ($tblToPersonChoose) {
//                                                                        $ToPersonChooseList[] = $tblToPersonChoose;
//                                                                        if($Person === null){
//                                                                            $Person = $Parent;
//                                                                        }
//                                                                    }
//                                                                }
//                                                            }
//                                                        }
//                                                    }
//                                                } elseif($Salutation && $Salutation->getSalutation() == 'Frau') {
//
//                                                    if($tblCommonGender->getName() === 'Weiblich') {
//                                                        $tblToPersonList = Address::useService()->getAddressAllByPerson($Parent);
//                                                        if ($tblToPersonList) {
//                                                            $tblType = Address::useService()->getTypeById(1);
//                                                            $tblToPersonChoose = null;
//                                                            // Ziehen der ersten Hauptadresse (die aktuellste)
//                                                            foreach ($tblToPersonList as $tblToPerson) {
//                                                                if ($tblToPerson->getTblType()->getId() === $tblType->getId() && $tblToPersonChoose === null) {
//                                                                    $tblToPersonChoose = $tblToPerson;
//                                                                }
//                                                            }
//                                                            if ($tblToPersonChoose) {
//                                                                $ToPersonChooseList[] = $tblToPersonChoose;
//                                                            }
//                                                            $Person = $Parent;
//                                                        }
//                                                    }
//                                                } elseif($Salutation && $Salutation->getSalutation() == 'Herr'){
//
//                                                    if($tblCommonGender->getName() === 'Männlich'){
//                                                        if($tblAddress = Address::useService()->getAddressByPerson($Parent)) {
//                                                            if ($Person === null) {
//                                                                $tblToPersonList = Address::useService()->getAddressAllByPerson($Parent);
//                                                                if($tblToPersonList) {
//                                                                    $tblType = Address::useService()->getTypeById(1);
//                                                                    $tblToPersonChoose = null;
//                                                                    // Ziehen der ersten Hauptadresse (die aktuellste)
//                                                                    foreach ($tblToPersonList as $tblToPerson) {
//                                                                        if ($tblToPerson->getTblType()->getId() === $tblType->getId() && $tblToPersonChoose === null) {
//                                                                            $tblToPersonChoose = $tblToPerson;
//                                                                        }
//                                                                    }
//                                                                    if($tblToPersonChoose){
//                                                                        $ToPersonChooseList[] = $tblToPersonChoose;
//                                                                    }
//                                                                }
//                                                                $Person = $Parent;
//                                                            }
//                                                        }
//                                                    }
//                                                }
//                                            }
//                                        }
//                                    }
//                                }
                                // Bezogen auf Anrede
                                foreach ($GuardianList as $Parent) {
                                    if ($Parent->getTblSalutation()) {

                                        if ($Salutation == null
                                            || $Salutation && $Salutation->getSalutation() == 'Familie'
                                        ) {
                                            if ($Parent->getTblSalutation()->getSalutation() === 'Frau') {
                                                $tblToPersonList = Address::useService()->getAddressAllByPerson($Parent);
                                                if ($tblToPersonList) {
                                                    $tblType = Address::useService()->getTypeById(1);
                                                    $tblToPersonChoose = null;
                                                    // Ziehen der ersten Hauptadresse (die aktuellste)
                                                    foreach ($tblToPersonList as $tblToPerson) {
                                                        if ($tblToPerson->getTblType()->getId() === $tblType->getId() && $tblToPersonChoose === null) {
                                                            $tblToPersonChoose = $tblToPerson;
                                                        }
                                                    }
                                                    if ($tblToPersonChoose) {
                                                        $ToPersonChooseList[] = $tblToPersonChoose;
                                                        $Person = $Parent;
                                                    }
                                                }
                                            } elseif ($Parent->getTblSalutation()->getSalutation() === 'Herr') {
                                                if ($tblAddress = Address::useService()->getAddressByPerson($Parent)) {
                                                    if ($Person === null) {
                                                        $tblToPersonList = Address::useService()->getAddressAllByPerson($Parent);
                                                        if ($tblToPersonList) {
                                                            $tblType = Address::useService()->getTypeById(1);
                                                            $tblToPersonChoose = null;
                                                            // Ziehen der ersten Hauptadresse (die aktuellste)
                                                            foreach ($tblToPersonList as $tblToPerson) {
                                                                if ($tblToPerson->getTblType()->getId() === $tblType->getId() && $tblToPersonChoose === null) {
                                                                    $tblToPersonChoose = $tblToPerson;
                                                                }
                                                            }
                                                            if ($tblToPersonChoose) {
                                                                $ToPersonChooseList[] = $tblToPersonChoose;
                                                                if ($Person === null) {
                                                                    $Person = $Parent;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        } elseif ($Salutation && $Salutation->getSalutation() == 'Frau') {

                                            if ($Parent->getTblSalutation()->getSalutation() === 'Frau') {
                                                $tblToPersonList = Address::useService()->getAddressAllByPerson($Parent);
                                                if ($tblToPersonList) {
                                                    $tblType = Address::useService()->getTypeById(1);
                                                    $tblToPersonChoose = null;
                                                    // Ziehen der ersten Hauptadresse (die aktuellste)
                                                    foreach ($tblToPersonList as $tblToPerson) {
                                                        if ($tblToPerson->getTblType()->getId() === $tblType->getId() && $tblToPersonChoose === null) {
                                                            $tblToPersonChoose = $tblToPerson;
                                                        }
                                                    }
                                                    if ($tblToPersonChoose) {
                                                        $ToPersonChooseList[] = $tblToPersonChoose;
                                                    }
                                                    $Person = $Parent;
                                                }
                                            }
                                        } elseif ($Salutation && $Salutation->getSalutation() == 'Herr') {

                                            if ($Parent->getTblSalutation()->getSalutation() === 'Herr') {
                                                if ($tblAddress = Address::useService()->getAddressByPerson($Parent)) {
                                                    if ($Person === null) {
                                                        $tblToPersonList = Address::useService()->getAddressAllByPerson($Parent);
                                                        if ($tblToPersonList) {
                                                            $tblType = Address::useService()->getTypeById(1);
                                                            $tblToPersonChoose = null;
                                                            // Ziehen der ersten Hauptadresse (die aktuellste)
                                                            foreach ($tblToPersonList as $tblToPerson) {
                                                                if ($tblToPerson->getTblType()->getId() === $tblType->getId() && $tblToPersonChoose === null) {
                                                                    $tblToPersonChoose = $tblToPerson;
                                                                }
                                                            }
                                                            if ($tblToPersonChoose) {
                                                                $ToPersonChooseList[] = $tblToPersonChoose;
                                                            }
                                                        }
                                                        $Person = $Parent;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }

                                /** @var TblToPerson[] $ToPersonChooseList */
                                if (!empty( $ToPersonChooseList )) {
                                    if (count($ToPersonChooseList) === 1) {
                                        $tblToPersonChoose = $ToPersonChooseList[0];
                                        SerialLetter::useService()->createAddressPerson($tblSerialLetter, $tblPerson, $Person, $tblToPersonChoose, $Salutation);
                                    } elseif (count($ToPersonChooseList) === 2) {
                                        if ($ToPersonChooseList[0]->getTblAddress()->getId() === $ToPersonChooseList[1]->getTblAddress()->getId()) {
                                            $tblToPersonChoose = $ToPersonChooseList[0];
                                            SerialLetter::useService()->createAddressPerson($tblSerialLetter, $tblPerson, $Person, $tblToPersonChoose, $Salutation);
                                        }
                                    } elseif (count($ToPersonChooseList) === 3) {
                                        if ($ToPersonChooseList[0]->getTblAddress()->getId() === $ToPersonChooseList[1]->getTblAddress()->getId()
                                            && $ToPersonChooseList[1]->getTblAddress()->getId() === $ToPersonChooseList[2]->getTblAddress()->getId()
                                        ) {
                                            $tblToPersonChoose = $ToPersonChooseList[0];
                                            SerialLetter::useService()->createAddressPerson($tblSerialLetter, $tblPerson, $Person, $tblToPersonChoose, $Salutation);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            return $Form->setError('Salutation', 'Es sind keine Personen im Serienbrief hinterlegt');
        }
        return $Form.new Success('Mögliche Adressenzuweisungen wurde vorgenommen')
        .new Redirect('/Reporting/SerialLetter/Address', Redirect::TIMEOUT_SUCCESS, array('TabActive' => 'GUARDIAN',
                                                                                          'Id'        => $tblSerialLetter->getId()));
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

        $tblAddressPersonAllBySerialLetter = $this->getAddressPersonAllBySerialLetter($tblSerialLetter);
        if ($tblAddressPersonAllBySerialLetter) {

            $row = 0;
            $column = 0;
            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell($column++, $row), "Anrede");
            $export->setValue($export->getCell($column++, $row), "Vorname");
            $export->setValue($export->getCell($column++, $row), "Nachname");
            $export->setValue($export->getCell($column++, $row), "Ortsteil");
            $export->setValue($export->getCell($column++, $row), "Adresse 1");
            $export->setValue($export->getCell($column++, $row), "PLZ");
            $export->setValue($export->getCell($column++, $row), "Ort");
            $export->setValue($export->getCell($column++, $row), "Person_Vorname");
            $export->setValue($export->getCell($column++, $row), "Person_Nachname");
            $export->setValue($export->getCell($column, $row), "Schüler-Nr.");

            $row = 1;
            /** @var TblAddressPerson $tblAddressPerson */
            foreach ($tblAddressPersonAllBySerialLetter as $tblAddressPerson) {
                if ($tblAddressPerson->getServiceTblPerson()
                    && $tblAddressPerson->getServiceTblPersonToAddress()
                    && $tblAddressPerson->getServiceTblToPerson()
                ) {
                    $column = 0;
                    $export->setValue($export->getCell($column++, $row),
                        $tblAddressPerson->getServiceTblSalutation() ? $tblAddressPerson->getServiceTblSalutation()->getSalutation() : '');
                    $export->setValue($export->getCell($column++, $row),
                        $tblAddressPerson->getServiceTblPersonToAddress()->getFirstName());
                    $export->setValue($export->getCell($column++, $row),
                        $tblAddressPerson->getServiceTblPersonToAddress()->getLastName());
                    $tblAddress = $tblAddressPerson->getServiceTblToPerson()->getTblAddress();
                    $export->setValue($export->getCell($column++, $row),
                        $tblAddress->getTblCity()->getDistrict());
                    $export->setValue($export->getCell($column++, $row),
                        $tblAddress->getStreetName().' '.$tblAddress->getStreetNumber());
                    $export->setValue($export->getCell($column++, $row), $tblAddress->getTblCity()->getCode());
                    $export->setValue($export->getCell($column++, $row), $tblAddress->getTblCity()->getName());
                    $export->setValue($export->getCell($column++, $row),
                        $tblAddressPerson->getServiceTblPerson()->getFirstName());
                    $export->setValue($export->getCell($column++, $row),
                        $tblAddressPerson->getServiceTblPerson()->getLastName());
                    $tblStudent = Student::useService()->getStudentByPerson($tblAddressPerson->getServiceTblPerson());
                    if ($tblStudent) {
                        $export->setValue($export->getCell($column, $row),
                            $tblStudent->getIdentifier());
                    } else {
                        $export->setValue($export->getCell($column, $row), '');
                    }
                    $row++;
                }
            }

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }
        return false;
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