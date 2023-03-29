<?php

namespace SPHERE\Application\ParentStudentAccess\OnlineContactDetails;

use DateTime;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\ParentStudentAccess\OnlineContactDetails\Service\Data;
use SPHERE\Application\ParentStudentAccess\OnlineContactDetails\Service\Entity\TblOnlineContact;
use SPHERE\Application\ParentStudentAccess\OnlineContactDetails\Service\Setup;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Fitting\Element;

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
     * eingeloggte Person ist ein Schüler -> nur ab 18 Jahre
     *
     * @return array|false
     */
    public function getPersonListFromStudentLogin()
    {
        $tblPersonList = array();
        if (($tblPerson = Account::useService()->getPersonByLogin())
            && ($tblSetting = Consumer::useService()->getSetting('ParentStudentAccess', 'Person', 'ContactDetails', 'OnlineContactDetailsAllowedForSchoolTypes'))
            && ($tblSchoolTypeAllowedList = Consumer::useService()->getSchoolTypeBySettingString($tblSetting->getValue()))
        ) {
            if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson))
                && ($tblType = $tblStudentEducation->getServiceTblSchoolType())
                && isset($tblSchoolTypeAllowedList[$tblType->getId()])
                && ($birthday = $tblPerson->getBirthday())
                && (new DateTime($birthday)) <= ((new DateTime('now'))->modify('-18 year'))
            ) {
                $tblPersonList[$tblPerson->getId()] = $tblPerson;

                // und anzeige der Sorgeberechtigt, Bevollmächtigt, Vormund -> erstmal nicht
//                if (($tblPersonRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson))) {
//                    foreach ($tblPersonRelationshipList as $relationship) {
//                        if (($tblPersonFrom = $relationship->getServiceTblPersonFrom())
//                            && $tblPersonFrom->getId() != $tblPerson->getId()
//                            && ($relationship->getTblType()->getName() == 'Sorgeberechtigt'
//                                || $relationship->getTblType()->getName() == 'Bevollmächtigt'
//                                || $relationship->getTblType()->getName() == 'Vormund')
//                        ) {
//                            $tblPersonList[$tblPersonFrom->getId()] = $tblPersonFrom;
//                        }
//                    }
//                }
            }
        }

        return empty($tblPersonList) ? false : $tblPersonList;
    }

    /**
     * eingeloggter Elternteil (Sorgeberechtigt, Bevollmächtigt, Vormund) + Kinder,
     * ist das Elternteil ein Sorgeberechtigter, werden auch die weiteren Sorgeberechtigten und Notfallkontakte für das Kind mit angezeigt
     *
     * @return array|false
     */
    public function getPersonListFromCustodyLogin()
    {
        $tblPersonList = array();
        if (($tblPerson = Account::useService()->getPersonByLogin())
            && ($tblSetting = Consumer::useService()->getSetting('ParentStudentAccess', 'Person', 'ContactDetails', 'OnlineContactDetailsAllowedForSchoolTypes'))
            && ($tblSchoolTypeAllowedList = Consumer::useService()->getSchoolTypeBySettingString($tblSetting->getValue()))
        ) {
            $tblMainAddress = $tblPerson->fetchMainAddress();
            // Kinder des Elternteils
            if (($tblPersonRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson))) {
                foreach ($tblPersonRelationshipList as $relationship) {
                    if (($tblPersonChild = $relationship->getServiceTblPersonTo())
                        && $tblPersonChild->getId() != $tblPerson->getId()
                        && ($relationship->getTblType()->getName() == 'Sorgeberechtigt'
                            || $relationship->getTblType()->getName() == 'Bevollmächtigt'
                            || $relationship->getTblType()->getName() == 'Vormund')
                    ) {
                        // prüfen: ob die Schulart freigeben ist
                        if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPersonChild))
                            && ($tblType = $tblStudentEducation->getServiceTblSchoolType())
                            && isset($tblSchoolTypeAllowedList[$tblType->getId()])
                        ) {
                            // eingeloggter Elternteil
                            if (!isset($tblPersonList[$tblPerson->getId()])) {
                                $tblPersonList[$tblPerson->getId()] = $tblPerson;
                            }

                            // für Sorgeberechtigte sollen die weiteren Sorgeberechtigten und Notfallkontakte für das Kind mit angezeigt werden
                            if ($relationship->getTblType()->getName() == 'Sorgeberechtigt') {
                                if (($tblPersonRelationshipFromChildList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPersonChild))) {
                                    foreach ($tblPersonRelationshipFromChildList as $tblPersonRelationshipFromChild) {
                                        if (($tblPersonFrom = $tblPersonRelationshipFromChild->getServiceTblPersonFrom())
                                            && $tblPersonFrom->getId() != $tblPersonChild->getId()
                                        ) {
                                            // aus Datenschutzgründen muss der weitere Sorgeberechtigte die gleiche Hauptadresse besitzen
                                            if ($tblPersonRelationshipFromChild->getTblType()->getName() == 'Sorgeberechtigt'
                                                && $tblMainAddress && ($tblAddress = $tblPersonFrom->fetchMainAddress())
                                                && $tblMainAddress->getId() == $tblAddress->getId()
                                            ) {
                                                $tblPersonList[$tblPersonFrom->getId()] = $tblPersonFrom;
                                            } elseif ($tblPersonRelationshipFromChild->getTblType()->getName() == 'Notfallkontakt') {
                                                $tblPersonList[$tblPersonFrom->getId()] = $tblPersonFrom;
                                            }
                                        }
                                    }
                                }
                            }

                            $tblPersonList[$tblPersonChild->getId()] = $tblPersonChild;
                        }
                    }
                }
            }
        }

        return empty($tblPersonList) ? false : $tblPersonList;
    }

    /**
     * @param array $tblPersonList
     *
     * @return array
     */
    public function getPersonIdListFromPersonList(array $tblPersonList): array
    {
        $personIdList = array();
        foreach ($tblPersonList as $tblPerson) {
            $personIdList[$tblPerson->getId()] = $tblPerson->getId();
        }

        return $personIdList;
    }

    /**
     * @param $tblPersonList
     * @param $filterPersonIdList
     *
     * @return array
     */
    public function getPersonListWithFilter($tblPersonList, $filterPersonIdList): array
    {
        $result = array();
        if ($tblPersonList) {
            foreach ($tblPersonList as $tblPerson) {
                if (isset($filterPersonIdList[$tblPerson->getId()])) {
                    $result[$tblPerson->getId()] = $tblPerson->getId();
                }
            }
        }

        return $result;
    }

    /**
     * @param array|false $personIdList
     *
     * @return string
     */
    public function getNameStringFromPersonIdList($personIdList): string
    {
        $result = array();
        if ($personIdList) {
            foreach ($personIdList as $personId) {
                if ($tblPerson = Person::useService()->getPersonById($personId)) {
                    $result[$personId] = $tblPerson->getFullName();
                }
            }
        }

        return empty($result) ? '' : implode(', ', $result) ;
    }

    /**
     * @param array|false $personIdList
     *
     * @return array
     */
    public function getNameListFromPersonIdList($personIdList): array
    {
        $result = array();
        if ($personIdList) {
            foreach ($personIdList as $personId) {
                if ($tblPerson = Person::useService()->getPersonById($personId)) {
                    $result[$personId] = $tblPerson->getFullName();
                }
            }
        }

        return $result;
    }

    /**
     * @param string $ContactType
     * @param Element $tblToPerson
     *
     * @return false|TblOnlineContact[]
     */
    public function getOnlineContactAllByToPerson(string $ContactType, Element $tblToPerson)
    {
        return (new Data($this->getBinding()))->getOnlineContactAllByToPerson($ContactType, $tblToPerson);
    }

    /**
     * @param TblPerson $tblPerson
     * @param string|null $ContactType
     *
     * @return false|TblOnlineContact[]
     */
    public function getOnlineContactAllByPerson(TblPerson $tblPerson, ?string $ContactType = null)
    {
        return (new Data($this->getBinding()))->getOnlineContactAllByPerson($tblPerson, $ContactType);
    }

    /**
     * @param TblPerson $tblPerson
     * @param $ToPersonId
     * @param $PersonIdList
     * @param array $Data
     *
     * @return false|Form
     */
    public function checkFormPhone(
        TblPerson $tblPerson,
        $ToPersonId,
        $PersonIdList,
        array $Data
    ) {
        $error = false;
        $form = OnlineContactDetails::useFrontend()->formPhone($tblPerson->getId(), $ToPersonId, $PersonIdList);
        if (isset($Data['Number']) && empty($Data['Number'])) {
            $form->setError('Data[Number]', 'Bitte geben Sie eine gültige Telefonnummer an');
            $error = true;
        } else {
            $form->setSuccess('Number');
        }
        // Typ der Telefonnummer nur bei neuen Telefonnummern
        if (!$ToPersonId && isset($Data['Type']) && empty($Data['Type'])) {
            $form->setError('Data[Type]', 'Bitte wählen Sie einen Typ aus');
            $error = true;
        } else {
            $form->setSuccess('Type');
        }

        return $error ? $form : false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param $ToPersonId
     * @param array $Data
     *
     * @return bool
     */
    public function createPhone(
        TblPerson $tblPerson,
        $ToPersonId,
        array $Data
    ): bool {

        if (($tblPhone = Phone::useService()->insertPhone($Data['Number']))
            && ($tblPersonLogin = Account::useService()->getPersonByLogin())
        ) {
            $tblToPerson = $ToPersonId ? Phone::useService()->getPhoneToPersonById($ToPersonId) : null;

            $tblPhoneType = isset($Data['Type']) && ($tblPhoneType = Phone::useService()->getTypeById($Data['Type'])) ? $tblPhoneType : null;

            (new Data($this->getBinding()))->createOnlineContact(TblOnlineContact::VALUE_TYPE_PHONE, $tblToPerson ?: null, $tblPhone, $tblPerson,
                $Data['Remark'], $tblPersonLogin, $tblPhoneType);

            if (isset($Data['PersonList'])) {
                foreach ($Data['PersonList'] as $personId => $value) {
                    if ($tblPersonItem = Person::useService()->getPersonById($personId)) {
                        if ($tblToPerson)  {
                            $tblToPersonTemp = Phone::useService()->getPhoneToPersonByPersonAndPhone($tblPersonItem, $tblToPerson->getTblPhone());
                        } else {
                            $tblToPersonTemp = null;
                        }

                        (new Data($this->getBinding()))->createOnlineContact(TblOnlineContact::VALUE_TYPE_PHONE, $tblToPersonTemp ?: null, $tblPhone, $tblPersonItem,
                            $Data['Remark'], $tblPersonLogin, $tblPhoneType);
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param $AddressId
     * @param $PersonIdList
     * @param array $Data
     *
     * @return false|Form
     */
    public function checkFormAddress(
        TblPerson $tblPerson,
        $AddressId,
        $PersonIdList,
        array $Data
    ) {
        $error = false;
        $form = OnlineContactDetails::useFrontend()->formAddress($tblPerson->getId(), $AddressId, $PersonIdList);
        if (isset($Data['Street']['Name']) && empty($Data['Street']['Name'])) {
            $form->setError('Data[Street][Name]', 'Bitte geben Sie eine Straße an');
            $error = true;
        } else {
            $form->setSuccess('Data[Street][Name]');
        }
        if (isset($Data['Street']['Number']) && empty($Data['Street']['Number'])) {
            $form->setError('Data[Street][Number]', 'Bitte geben Sie eine Hausnummer an');
            $error = true;
        } else {
            $form->setSuccess('Data[Street][Number]');
        }
        if (isset($Data['City']['Code']) && empty($Data['City']['Code'])) {
            $form->setError('Data[City][Code]', 'Bitte geben Sie eine Postleitzahl an');
            $error = true;
        } else {
            $form->setSuccess('Data[City][Code]');
        }
        if (isset($Data['City']['Name']) && empty($Data['City']['Name'])) {
            $form->setError('Data[City][Name]', 'Bitte geben Sie einen Ort an');
            $error = true;
        } else {
            $form->setSuccess('Data[City][Name]');
        }

        return $error ? $form : false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param $ToPersonId
     * @param array $Data
     *
     * @return bool
     */
    public function createAddress(
        TblPerson $tblPerson,
        $ToPersonId,
        array $Data
    ): bool {

        if (($tblAddress = Address::useService()->insertAddress(
                $Data['Street']['Name'],
                $Data['Street']['Number'],
                $Data['City']['Code'],
                $Data['City']['Name'],
                $Data['City']['District']
            )) && ($tblPersonLogin = Account::useService()->getPersonByLogin())
        ) {
            $tblToPerson = $ToPersonId ? Address::useService()->getAddressToPersonById($ToPersonId) : null;

            (new Data($this->getBinding()))->createOnlineContact(TblOnlineContact::VALUE_TYPE_ADDRESS, $tblToPerson ?: null, $tblAddress, $tblPerson,
                $Data['Remark'], $tblPersonLogin);

            if (isset($Data['PersonList'])) {
                foreach ($Data['PersonList'] as $personId => $value) {
                    if ($tblPersonItem = Person::useService()->getPersonById($personId)) {
                        if ($tblToPerson)  {
                            $tblToPersonTemp = Address::useService()->getAddressToPersonByPersonAndAddress($tblPersonItem, $tblToPerson->getTblAddress());
                        } else {
                            $tblToPersonTemp = null;
                        }

                        (new Data($this->getBinding()))->createOnlineContact(TblOnlineContact::VALUE_TYPE_ADDRESS, $tblToPersonTemp ?: null, $tblAddress,
                            $tblPersonItem, $Data['Remark'], $tblPersonLogin);
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param $ToPersonId
     * @param $PersonIdList
     * @param array $Data
     *
     * @return false|Form
     */
    public function checkFormMail(
        TblPerson $tblPerson,
        $ToPersonId,
        $PersonIdList,
        array $Data
    ) {
        $error = false;
        $form = OnlineContactDetails::useFrontend()->formMail($tblPerson->getId(), $ToPersonId, $PersonIdList);
        if (isset($Data['Address']) && empty($Data['Address'])) {
            $form->setError('Data[Address]', 'Bitte geben Sie eine gültige E-Mail-Adresse an');
            $error = true;
        } else {
            $form->setSuccess('Address');
        }
        // Typ der Email-Adresse nur bei neuen Email-Adressen
        if (!$ToPersonId && isset($Data['Type']) && empty($Data['Type'])) {
            $form->setError('Data[Type]', 'Bitte wählen Sie einen Typ aus');
            $error = true;
        } else {
            $form->setSuccess('Type');
        }

        return $error ? $form : false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param $ToPersonId
     * @param array $Data
     *
     * @return bool
     */
    public function createMail(
        TblPerson $tblPerson,
        $ToPersonId,
        array $Data
    ): bool {

        if (($tblMail = Mail::useService()->insertMail($Data['Address']))
            && ($tblPersonLogin = Account::useService()->getPersonByLogin())
        ) {
            $tblToPerson = $ToPersonId ? Mail::useService()->getMailToPersonById($ToPersonId) : null;

            $tblMailType = isset($Data['Type']) && ($tblMailType = Mail::useService()->getTypeById($Data['Type'])) ? $tblMailType : null;

            (new Data($this->getBinding()))->createOnlineContact(TblOnlineContact::VALUE_TYPE_MAIL, $tblToPerson ?: null, $tblMail, $tblPerson,
                $Data['Remark'], $tblPersonLogin, $tblMailType);

            if (isset($Data['PersonList'])) {
                foreach ($Data['PersonList'] as $personId => $value) {
                    if ($tblPersonItem = Person::useService()->getPersonById($personId)) {
                        if ($tblToPerson)  {
                            $tblToPersonTemp = Mail::useService()->getMailToPersonByPersonAndMail($tblPersonItem, $tblToPerson->getTblMail());
                        } else {
                            $tblToPersonTemp = null;
                        }

                        (new Data($this->getBinding()))->createOnlineContact(TblOnlineContact::VALUE_TYPE_MAIL, $tblToPersonTemp ?: null, $tblMail, $tblPersonItem,
                            $Data['Remark'], $tblPersonLogin, $tblMailType);
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param TblOnlineContact $tblOnlineContact
     *
     * @return bool
     */
    public function deleteOnlineContact(TblOnlineContact $tblOnlineContact): bool
    {
        return (new Data($this->getBinding()))->deleteOnlineContact($tblOnlineContact);
    }

    /**
     * @param $Id
     *
     * @return false|TblOnlineContact
     */
    public function getOnlineContactById($Id)
    {
        return (new Data($this->getBinding()))->getOnlineContactById($Id);
    }

    /**
     * @return false|TblOnlineContact[]
     */
    public function getOnlineContactAll()
    {
        return (new Data($this->getBinding()))->getOnlineContactAll();
    }

    /**
     * @param TblOnlineContact $tblOnlineContact
     * @param bool $isResultNameString
     *
     * @return array|false|string
     */
    public function getPersonListForOnlineContact(TblOnlineContact $tblOnlineContact, bool $isResultNameString)
    {
        $resultList = array();
        if (($tblOnlineContactList = (new Data($this->getBinding()))->getOnlineContactAllByOnlineContact($tblOnlineContact))) {
            foreach ($tblOnlineContactList as $item) {
                if (($tblPerson = $item->getServiceTblPerson())) {
                    if ($isResultNameString) {
                        $resultList[$tblPerson->getId()] = $tblPerson->getFullName();
                    } else {
                        $resultList[$tblPerson->getId()] = $tblPerson;
                    }
                }
            }
        }

        if ($isResultNameString) {
            return empty($resultList) ? '' : implode(', ' ,$resultList);
        } else {
            return empty($resultList) ? false : $resultList;
        }
    }

    /**
     * @param Element $tblContact
     * @param string $contactType
     * @param TblPerson $tblPerson
     *
     * @return false|TblOnlineContact
     */
    public function getOnlineContactByContactAndPerson(Element $tblContact, string $contactType, TblPerson $tblPerson)
    {
        return (new Data($this->getBinding()))->getOnlineContactByContactAndPerson($tblContact, $contactType, $tblPerson);
    }
}