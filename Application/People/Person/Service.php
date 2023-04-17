<?php
namespace SPHERE\Application\People\Person;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\ParentStudentAccess\OnlineContactDetails\OnlineContactDetails;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Club\Club;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonInformation;
use SPHERE\Application\People\Meta\Custody\Custody;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Frontend\FrontendFamily;
use SPHERE\Application\People\Person\Service\Data;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\People\Person\Service\Setup;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\People\Person
 */
class Service extends AbstractService
{

    /**
     * @return false|ViewPerson[]
     */
    public function viewPerson()
    {

        return (new Data($this->getBinding()))->viewPerson();
    }

    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
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
     * @param bool $isGenderSort
     *
     * @return bool|TblSalutation[]
     */
    public function getSalutationAll(bool $isGenderSort = false)
    {

        if($isGenderSort){
            $tblSalutationAll = (new Data($this->getBinding()))->getSalutationAll();
            $returnList = array();
            foreach($tblSalutationAll as $tblSalutation){
                if($tblSalutation->getSalutation() == TblSalutation::VALUE_WOMAN){
                    $returnList[0] = $tblSalutation;
                } elseif($tblSalutation->getSalutation() == TblSalutation::VALUE_MAN){
                    $returnList[1] = $tblSalutation;
                } elseif($tblSalutation->getSalutation() == TblSalutation::VALUE_STUDENT_FEMALE){
                    $returnList[2] = $tblSalutation;
                } elseif($tblSalutation->getSalutation() == TblSalutation::VALUE_STUDENT){
                    $returnList[3] = $tblSalutation;
                } else {
                    $returnList[$tblSalutation->getId()] = $tblSalutation;
                }
            }
            ksort($returnList);
            return $returnList;
        }

        return (new Data($this->getBinding()))->getSalutationAll();
    }

    /**
     * int
     */
    public function countPersonAll()
    {

        return (new Data($this->getBinding()))->countPersonAll();
    }

    /**
     * @return bool|TblPerson[]
     */
    public function getPersonAll()
    {

        return (new Data($this->getBinding()))->getPersonAll();
    }

    /**
     * @return false|TblPerson[]
     */
    public function getPersonAllBySoftRemove()
    {

        return (new Data($this->getBinding()))->getPersonAllBySoftRemove();
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return int
     */
    public function countPersonAllByGroup(TblGroup $tblGroup)
    {

        return Group::useService()->countMemberByGroup($tblGroup);
    }

    /**
     * @param $Person
     *
     * @return bool|TblPerson
     */
    public function createPersonService($Person)
    {
        if (($tblPerson = (new Data($this->getBinding()))->createPerson(
            $this->getSalutationById($Person['Salutation']), $Person['Title'], $Person['FirstName'],
            $Person['SecondName'], $Person['CallName'], $Person['LastName'], $Person['BirthName'])
        )) {
            // Add to Group
            if (isset($Person['Group'])) {
                foreach ((array)$Person['Group'] as $GroupId) {
                    $tblGroup = Group::useService()->getGroupById($GroupId);
                    if ($tblGroup) {
                        Group::useService()->addGroupPerson(
                            $tblGroup, $tblPerson
                        );
                    }
                }
            }

            return $tblPerson;
        } else {
            return false;
        }
    }

    /**
     * @param int $Id
     *
     * @return bool|TblSalutation
     */
    public function getSalutationById($Id)
    {

        return (new Data($this->getBinding()))->getSalutationById($Id);
    }

    /**
     * @param        $Salutation
     * @param        $Title
     * @param        $FirstName
     * @param        $SecondName
     * @param        $LastName
     * @param        $GroupList
     * @param string $BirthName
     * @param string $ImportId
     * @param string $CallName
     *
     * @return bool|TblPerson
     */
    public function insertPerson($Salutation, $Title, $FirstName, $SecondName, $LastName, $GroupList, $BirthName = '', $ImportId = '', $CallName = '')
    {

        if (( $tblPerson = (new Data($this->getBinding()))->createPerson(
            $Salutation, $Title, $FirstName, $SecondName, $CallName, $LastName, $BirthName, $ImportId) )
        ) {
            // Add to Group
            if (!empty( $GroupList )) {
                foreach ($GroupList as $tblGroup) {
                    Group::useService()->addGroupPerson(
                        Group::useService()->getGroupById($tblGroup), $tblPerson
                    );
                }
            }
            return $tblPerson;
        } else {
            return false;
        }
    }

    /**
     * @param $Id
     * @param bool $IsForced
     *
     * @return bool|TblPerson
     */
    public function getPersonById($Id, $IsForced = false)
    {

        return (new Data($this->getBinding()))->getPersonById($Id, $IsForced);
    }

    /**
     * @param $ImportId
     *
     * @return bool|TblPerson
     */
    public function getPersonByImportId($ImportId)
    {

        return (new Data($this->getBinding()))->getPersonByImportId($ImportId);
    }

    /**
     * @param string $FirstName
     * @param string $LastName
     *
     * @return bool|TblPerson
     */
    public function getPersonByName($FirstName, $LastName, $Birthday = null, $Code = null, $Identifier = null)
    {

        if(($tblPersonList = (new Data($this->getBinding()))->getPersonAllByFirstNameAndLastName($FirstName, $LastName))){
            return $this->getPersonSearchByOptions($tblPersonList, $Birthday, $Code, $Identifier);
        } else {
            // Person wurde so nicht gefunden -> Vorname könnte 2. Vorname enthalten
            $NameList = explode(' ', $FirstName);
            $count = count($NameList);
            $SecondName = '';
            if($count == 2){
                $FirstName = $NameList[0];
                $SecondName = $NameList[1];
            } elseif($count > 2) {
                $FirstName = $NameList[0];
                for($i = 1; $i < $count; $i++){
                    $SecondName .= $NameList[$i];
                }
            }
            if(($tblPersonList = (new Data($this->getBinding()))->getPersonAllByFirstNameAndSecondNameAndLastName($FirstName, $SecondName, $LastName))){
                return $this->getPersonSearchByOptions($tblPersonList, $Birthday, $Code, $Identifier);
            }
        }
        return false;
    }

    /**
     * @param string $FirstName
     * @param string $LastName
     *
     * @return bool|TblPerson[]
     */
    public function getPersonAllByName($FirstName, $LastName, $Birthday = null, $Code = null, $Identifier = null)
    {

        if(($tblPersonList = (new Data($this->getBinding()))->getPersonAllByFirstNameAndLastName($FirstName, $LastName))){
            $tblPersonList = $this->getPersonListSearchByOptions($tblPersonList, $Birthday, $Code, $Identifier);
        } else {
            $NameList = explode(' ', $FirstName);
            $count = count($NameList);
            $SecondName = '';
            if($count ==  2){
                $FirstName = $NameList[0];
                $SecondName = $NameList[1];
            } elseif($count > 2) {
                $FirstName = $NameList[0];
                for($i = 1; $i < $count; $i++){
                    $SecondName .= $NameList[$i];
                }
            }

            if(($tblPersonList = (new Data($this->getBinding()))->getPersonAllByFirstNameAndSecondNameAndLastName($FirstName, $SecondName, $LastName))){
                $tblPersonList = $this->getPersonListSearchByOptions($tblPersonList, $Birthday, $Code, $Identifier);
            }
        }
        return (!empty($tblPersonList) || $tblPersonList !== false ? $tblPersonList : null);
    }

    /**
     * @param array $tblPersonList
     * @param null  $Birthday
     * @param null  $Code
     * @param null  $Identifer
     *
     * @return false|TblPerson
     */
    private function getPersonSearchByOptions(array $tblPersonList, $Birthday = null, $Code = null, $Identifer = null)
    {
        $tblPerson = false;
        if(($tblPersonList = $this->getPersonListSearchByOptions($tblPersonList, $Birthday, $Code, $Identifer))){
            if(count($tblPersonList) == 1){
                $tblPerson = current($tblPersonList);
            }
        }
        return $tblPerson;
    }

    /**
     * @param array $tblPersonList
     * @param null  $Birthday
     * @param null  $Code
     * @param null  $Identifier
     *
     * @return TblPerson[]|false
     */
    private function getPersonListSearchByOptions(array $tblPersonList, $Birthday = null, $Code = null, $Identifier = null)
    {
        $PersonList = array();

        foreach($tblPersonList as $tblPerson){
            if($Birthday){
                $BirthdayTemp = $tblPerson->getBirthday();
                if($Birthday != $BirthdayTemp){
                    continue;
                }
            }
            if($Code){
                if(($tblAddress = Address::useService()->getAddressByPerson($tblPerson))){
                    if(($tblCity = $tblAddress->getTblCity())){
                        $CodeTemp = $tblCity->getCode();
                        if($Code != $CodeTemp){
                            continue;
                        }
                    } else {
                        continue;
                    }
                } else {
                    continue;
                }
            }
            if($Identifier){
                if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {
                    if ($Identifier != $tblStudent->getIdentifier()) {
                        continue;
                    }
                } else {
                    continue;
                }
            }
            // Notwendige Prüfungen überstanden
            $PersonList[] = $tblPerson;
        }
        return (!empty($PersonList) ? $PersonList: false);
    }

    /**
     * @deprecated
     * @param string $FirstName
     * @param string $LastName
     * @param string $Birthday
     * @param string $Identifier
     *
     * @return bool|TblPerson
     */
    public function getPersonByNameAndBirthdayOrIdentifier($FirstName, $LastName, $Birthday, $Identifier)
    {

        $tblPersonList = $this->getPersonAllByName($FirstName, $LastName);
        if ($tblPersonList) {
            foreach ($tblPersonList as $tblPerson) {
                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                if (!$tblCommon) {
                    continue;
                }
                $tblCommonBirthDates = $tblCommon->getTblCommonBirthDates();
                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                if (!$tblStudent || !$tblCommonBirthDates) {
                    continue;
                }

                if ($Birthday && $Birthday == $tblCommonBirthDates->getBirthday()) {
                    return $tblPerson;
                }
                if ($Identifier && $Identifier == $tblStudent->getIdentifier()) {
                    return $tblPerson;
                }
            }
        }
        return false;
    }

    /**
     * @deprecated
     * @param string $FirstName
     * @param string $LastName
     * @param string $Birthday
     *
     * @return bool|TblPerson[]
     */
    public function getPersonAllByNameAndBirthday($FirstName, $LastName, $Birthday)
    {

        $result = array();
        if (($tblPersonList = (new Data($this->getBinding()))->getPersonAllByFirstNameAndLastName($FirstName, $LastName))) {
            foreach ($tblPersonList as $tblPerson) {
                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                if (!$tblCommon) {
                    continue;
                }
                $tblCommonBirthDates = $tblCommon->getTblCommonBirthDates();
                if (!$tblCommonBirthDates) {
                    continue;
                }

                if ($Birthday == $tblCommonBirthDates->getBirthday()) {
                    $result[] = $tblPerson;
                }
            }
        }

        return empty($result) ? false : $result;
    }

    /**
     * @param $Name
     *
     * @return false|TblPerson[]
     */
    public function getPersonListLike($Name)
    {

        return (new Data($this->getBinding()))->getPersonListLike($Name);
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Person
     *
     * @return bool
     */
    public function updatePersonService(TblPerson $tblPerson, $Person)
    {
        // SSw-699 Bearbeitung Stammdaten (Anrede -> automatisches Geschlecht)
        $updateGender = false;
        $salutationId = ($tblSalutationOld = $tblPerson->getTblSalutation()) ? $tblSalutationOld->getId() : 0;
        if ($Person['Salutation']
            && $salutationId != $Person['Salutation']
        ) {
            if (($tblSalutationNew = $this->getSalutationById($Person['Salutation']))
                && ($tblCommon = $tblPerson->getCommon())
                && ($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())
            ) {
                if ($tblSalutationNew->getSalutation() == 'Herr') {
                    if (!($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())
                        || ($tblCommonGender
                            && $tblCommonGender->getName() != 'Männlich')
                    ) {
                        $updateGender = Common::useService()->getCommonGenderByName('Männlich');
                    }
                } elseif ($tblSalutationNew->getSalutation() == 'Frau') {
                    if (!($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())
                        || ($tblCommonGender
                            && $tblCommonGender->getName() != 'Weiblich')
                    ) {
                        $updateGender = Common::useService()->getCommonGenderByName('Weiblich');
                    }
                }

                // ChangeGender
                if ($updateGender) {
                    Common::useService()->updateCommonBirthDates(
                        $tblCommonBirthDates,
                        $tblCommonBirthDates->getBirthday(),
                        $tblCommonBirthDates->getBirthplace(),
                        $updateGender
                    );
                }
            }
        }

        if ((new Data($this->getBinding()))->updatePerson($tblPerson, $Person['Salutation'], $Person['Title'],
            $Person['FirstName'], $Person['SecondName'], $Person['CallName'], $Person['LastName'], $Person['BirthName'])
        ) {
            // Change Groups
            if (isset($Person['Group'])) {
                // Remove all Groups
                $tblGroupList = Group::useService()->getGroupAllByPerson($tblPerson);
                foreach ($tblGroupList as $tblGroup) {
                    Group::useService()->removeGroupPerson($tblGroup, $tblPerson);
                }
                // Add current Groups
                foreach ((array)$Person['Group'] as $tblGroup) {
                    Group::useService()->addGroupPerson(
                        Group::useService()->getGroupById($tblGroup), $tblPerson
                    );
                }
            } else {
                // Remove all Groups
                $tblGroupList = Group::useService()->getGroupAllByPerson($tblPerson);
                foreach ($tblGroupList as $tblGroup) {
                    Group::useService()->removeGroupPerson($tblGroup, $tblPerson);
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param array $ProcessList
     */
    public function updatePersonAnonymousBulk($ProcessList = array())
    {
        (new Data($this->getBinding()))->updatePersonAnonymousBulk($ProcessList);
    }

    /**
     * @param TblPerson     $tblPerson
     * @param TblSalutation $tblSalutation
     *
     * @return bool
     */
    public function updateSalutation(TblPerson $tblPerson, TblSalutation $tblSalutation)
    {

        return ( new Data($this->getBinding()) )->updateSalutation($tblPerson, $tblSalutation);
    }

    /**
     * @param array $IdArray of TblPerson->Id
     *
     * @return TblPerson[]
     */
    public function fetchPersonAllByIdList($IdArray)
    {

        return (new Data($this->getBinding()))->fetchPersonAllByIdList($IdArray);
    }

    /**
     * @param $FirstName
     * @param $LastName
     * @param $ZipCode
     * @return bool|TblPerson
     */
    public function existsPerson($FirstName, $LastName, $ZipCode)
    {

        if (($tblPersonList = $this->getPersonAllByName($FirstName, $LastName))
        ) {
            if($ZipCode === ''){
                return current($tblPersonList);
            }

            foreach ($tblPersonList as $tblPerson) {
                if (( $tblAddress = Address::useService()->getAddressByPerson($tblPerson, true) )) {
                    if ($tblAddress->getTblCity()->getCode() == $ZipCode) {
                        return $tblPerson;
                    }
                }
            }
        }
        return false;
    }

    /**
     * @deprecated
     * @param $firstName
     * @param $lastName
     *
     * @return bool|TblPerson[]
     */
    public function getPersonAllByFirstNameAndLastName($firstName, $lastName)
    {
        return (new Data($this->getBinding()))->getPersonAllByFirstNameAndLastName($firstName, $lastName);
    }

    /**
     * @param string $firstName
     * @param string $lastName
     *
     * @return false|TblPerson[]
     */
    public function getPersonListLikeFirstNameAndLastName($firstName, $lastName)
    {
        return (new Data($this->getBinding()))->getPersonListLikeFirstNameAndLastName($firstName, $lastName);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool
     */
    public function destroyPerson(TblPerson $tblPerson)
    {
        // alle Online-Kontakt-Daten Änderungswünsche zu der Person löschen
        if (($tblOnlineContacts = OnlineContactDetails::useService()->getOnlineContactAllByPerson($tblPerson))) {
            foreach ($tblOnlineContacts as $tblOnlineContact) {
                OnlineContactDetails::useService()->deleteOnlineContact($tblOnlineContact);
            }
        }

        return (new Data($this->getBinding()))->destroyPerson($tblPerson);
    }

    /**
     * @param TblPerson $tblPerson
     */
    public function softRemovePersonReferences(TblPerson $tblPerson)
    {
        return (new Data($this->getBinding()))->softRemovePersonReferences($tblPerson);
    }

    /**
     * @param string $Name
     *
     * @return bool|TblSalutation
     */
    public function getSalutationByName($Name)
    {

        return (new Data($this->getBinding()))->getSalutationByName($Name);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return array
     */
    public function getDestroyDetailList(TblPerson $tblPerson)
    {

        $list[] = new Bold('Person: ' . $tblPerson->getLastFirstName());
        // Person kann nicht mehr mit Account gelöscht werden, Info wird damit überflüssig
//        // Account anzeigen
//        if(($tblAccountList = Account::useService()->getAccountAllByPerson($tblPerson))){
//            $tblAccount = current($tblAccountList);
//            $list[] = new Danger('Die Person ist mit dem folgenden Benutzerkonto verknüpft: '
//                . $tblAccount->getUsername() . '. Das Benutzerkonto wird nicht mit gelöscht.');
//        }
        // Group
        if (($tblGroupList = Group::useService()->getGroupAllByPerson($tblPerson))) {
            foreach ($tblGroupList as $tblGroup) {
                $list[] = 'Personen-Gruppen-Zuordnung: ' . $tblGroup->getName();
            }
        }
        // Common
        if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))){
            $list[] = 'Personendaten der Person';
        }
        // Prospect
        if (($tblProspect = Prospect::useService()->getProspectByPerson($tblPerson))) {
            $list[] = 'Interessenten-Daten der Person';
        }
        // Teacher
        if (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPerson))) {
            $list[] = 'Lehrer-Daten der Person';
        }
        // Student
        if (($tblStudent = $tblPerson->getStudent())) {
            $list[] = 'Schülerakten-Daten der Person';
        }
        // Custody
        if (($tblCustody = Custody::useService()->getCustodyByPerson($tblPerson))) {
            $list[] = 'Sorgerecht-Daten der Person';
        }
        // Club
        if (($tblClub = Club::useService()->getClubByPerson($tblPerson))) {
            $list[] = 'Vereinsmitglied-Daten der Person';
        }

        // Address
        if (($tblAddressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
            foreach ($tblAddressList as $tblToPerson) {
                if (($tblAddress = $tblToPerson->getTblAddress())) {
                    $list[] = 'Adresse der Person: ' . $tblAddress->getGuiString();
                }
            }
        }
        // Phone
        if (($tblPhoneList = Phone::useService()->getPhoneAllByPerson($tblPerson))) {
            foreach ($tblPhoneList as $tblToPerson) {
                if (($tblPhone = $tblToPerson->getTblPhone())
                    && ($tblType = $tblToPerson->getTblType())
                ) {
                    $list[] = 'Telefonnummer der Person: ' . $tblPhone->getNumber()
                        . ' (' . $tblType->getName() . ' ' . $tblType->getDescription() . ')';
                }
            }
        }
        // Mail
        if (($tblMailList = Mail::useService()->getMailAllByPerson($tblPerson))) {
            foreach ($tblMailList as $tblToPerson) {
                if (($tblMail = $tblToPerson->getTblMail())
                    && ($tblType = $tblToPerson->getTblType())
                ) {
                    $list[] = 'E-Mail Adresse der Person: ' . $tblMail->getAddress()
                        . ' (' . $tblType->getName() . ')';
                }
            }
        }

        // Person Relationship
        if (($tblRelationshipToPersonList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson))){
            foreach($tblRelationshipToPersonList as $tblToPerson){
                if (($personFrom = $tblToPerson->getServiceTblPersonFrom())
                    && ($personTo = $tblToPerson->getServiceTblPersonTo())
                    && ($tblType = $tblToPerson->getTblType())
                ) {
                    if ($personFrom->getId() == $tblPerson->getId()) {
                        $dispLayPerson = $personTo;
                    } else {
                        $dispLayPerson = $personFrom;
                    }

                    $list[] = 'Personenbeziehung zu ' . $dispLayPerson->getLastFirstName() . ' (' . $tblType->getName() . ')';
                }
            }
        }
        // Company Relationship
        if (($tblRelationshipToPersonList = Relationship::useService()->getCompanyRelationshipAllByPerson($tblPerson))){
            foreach($tblRelationshipToPersonList as $tblToPerson){
                if (($tblCompany = $tblToPerson->getServiceTblCompany())
                    && ($tblType = $tblToPerson->getTblType())
                ) {
                    $list[] = 'Firmenbeziehung zu ' . $tblCompany->getDisplayName() . ' (' . $tblType->getName() . ')';
                }
            }
        }

        // Division
        if (($tblDivisionList = Division::useService()->getDivisionStudentAllByPerson($tblPerson))) {
            foreach ($tblDivisionList as $tblDivisionStudent) {
                if (($tblDivision = $tblDivisionStudent->getTblDivision())
                    && ($tblYear = $tblDivision->getServiceTblYear())
                ) {
                    $list[] = 'Klassenzuordnung: ' . $tblDivision->getDisplayName()
                        . ' (' . $tblYear->getDisplayName() . ')';
                }
            }
        }
        // Absence
        if (($tblAbsenceList = Absence::useService()->getAbsenceAllByPerson($tblPerson))){
            $list[] = count($tblAbsenceList) . ' Fehlzeiten zur Person';
        }
        // Grades
        if (($tblGradeList = Gradebook::useService()->getGradeAllBy($tblPerson))) {
            $list[] = 'Zugriff auf ' . count($tblGradeList) . ' Zensuren der Person';
        }
        // Certificates
        if (($tblFileList = Storage::useService()->getCertificateRevisionFileAllByPerson($tblPerson))) {
            $list[] = new Bold('Zugriff auf ' . count($tblFileList) . ' Zeugnisse der Person');
        }

        return $list;
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool $isRestore
     * @return array
     */
    public function getRestoreDetailList(TblPerson $tblPerson, $isRestore)
    {

        $count = 1;
        $result[] = array(
            'Number' => $count++,
            'Type' => 'Person',
            'Value' => $tblPerson->getLastFirstName(),
            'EntityRemove' => $tblPerson->getEntityRemove()
        );

        if ($isRestore) {
            (new Data($this->getBinding()))->restorePerson($tblPerson);
        }

        // Group
        if (($tblMemberList = Group::useService()->getMemberAllByPerson($tblPerson, true))) {
            foreach ($tblMemberList as $tblMember) {
                if (($tblGroup = $tblMember->getTblGroup())) {
                    $result[] = array(
                        'Number' => $count++,
                        'Type' => 'Gruppen-Mitglied',
                        'Value' => $tblGroup->getName(),
                        'EntityRemove' => $tblMember->getEntityRemove()
                    );

                    if ($isRestore) {
                        Group::useService()->restoreMember($tblMember);
                    }
                }
            }
        }
        // Common
        if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson, true))){
            $result[] = array(
                'Number' => $count++,
                'Type' => 'Meta-Daten',
                'Value' => 'Personendaten',
                'EntityRemove' => $tblCommon->getEntityRemove()
            );

            if ($isRestore) {
                Common::useService()->restoreCommon($tblCommon);
            }
        }
        // Prospect
        if (($tblProspect = Prospect::useService()->getProspectByPerson($tblPerson, true))) {
            $result[] = array(
                'Number' => $count++,
                'Type' => 'Meta-Daten',
                'Value' => 'Interessenten-Daten',
                'EntityRemove' => $tblProspect->getEntityRemove()
            );

            if ($isRestore) {
                Prospect::useService()->restoreProspect($tblProspect);
            }
        }
        // Teacher
        if (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPerson, true))) {
            $result[] = array(
                'Number' => $count++,
                'Type' => 'Meta-Daten',
                'Value' => 'Lehrer-Daten',
                'EntityRemove' => $tblTeacher->getEntityRemove()
            );

            if ($isRestore) {
                Teacher::useService()->restoreTeacher($tblTeacher);
            }
        }
        // Student
        if (($tblStudent = $tblPerson->getStudent(true))) {
            $result[] = array(
                'Number' => $count++,
                'Type' => 'Meta-Daten',
                'Value' => 'Schülerakten-Daten',
                'EntityRemove' => $tblStudent->getEntityRemove()
            );

            if ($isRestore) {
                Student::useService()->restoreStudent($tblStudent);
            }
        }
        // Custody
        if (($tblCustody = Custody::useService()->getCustodyByPerson($tblPerson, true))) {
            $result[] = array(
                'Number' => $count++,
                'Type' => 'Meta-Daten',
                'Value' => 'Sorgerecht-Daten',
                'EntityRemove' => $tblCustody->getEntityRemove()
            );

            if ($isRestore) {
                Custody::useService()->restoreCustody($tblCustody);
            }
        }
        // Club
        if (($tblClub = Club::useService()->getClubByPerson($tblPerson, true))) {
            $result[] = array(
                'Number' => $count++,
                'Type' => 'Meta-Daten',
                'Value' => 'Vereinsmitglied-Daten',
                'EntityRemove' => $tblClub->getEntityRemove()
            );

            if ($isRestore) {
                Club::useService()->restoreClub($tblClub);
            }
        }

        // Address
        if (($tblAddressList = Address::useService()->getAddressAllByPerson($tblPerson, true))) {
            foreach ($tblAddressList as $tblToPerson) {
                if (($tblAddress = $tblToPerson->getTblAddress())) {
                    $result[] = array(
                        'Number' => $count++,
                        'Type' => 'Adresse',
                        'Value' => $tblAddress->getGuiString(),
                        'EntityRemove' => $tblToPerson->getEntityRemove()
                    );

                    if ($isRestore) {
                        Address::useService()->restoreToPerson($tblToPerson);
                    }
                }
            }
        }
        // Phone
        if (($tblPhoneList = Phone::useService()->getPhoneAllByPerson($tblPerson, true))) {
            foreach ($tblPhoneList as $tblToPerson) {
                if (($tblPhone = $tblToPerson->getTblPhone())
                    && ($tblType = $tblToPerson->getTblType())
                ) {
                    $result[] = array(
                        'Number' => $count++,
                        'Type' => 'Telefonnummer',
                        'Value' => $tblPhone->getNumber()
                            . ' (' . $tblType->getName() . ' ' . $tblType->getDescription() . ')',
                        'EntityRemove' => $tblToPerson->getEntityRemove()
                    );

                    if ($isRestore) {
                        Phone::useService()->restoreToPerson($tblToPerson);
                    }
                }
            }
        }
        // Mail
        if (($tblMailList = Mail::useService()->getMailAllByPerson($tblPerson, true))) {
            foreach ($tblMailList as $tblToPerson) {
                if (($tblMail = $tblToPerson->getTblMail())
                    && ($tblType = $tblToPerson->getTblType())
                ) {
                    $result[] = array(
                        'Number' => $count++,
                        'Type' => 'E-Mail Adresse',
                        'Value' => $tblMail->getAddress() . ' (' . $tblType->getName() . ')',
                        'EntityRemove' => $tblToPerson->getEntityRemove()
                    );

                    if ($isRestore) {
                        Mail::useService()->restoreToPerson($tblToPerson);
                    }
                }
            }
        }

        // Person Relationship
        if (($tblRelationshipToPersonList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, null, true))){
            foreach($tblRelationshipToPersonList as $tblToPerson){
                if (($tblType = $tblToPerson->getTblType())) {
                    if (($personFrom = $tblToPerson->getServiceTblPersonFrom())) {
                        $displayPerson = $personFrom;
                    } elseif (($personTo = $tblToPerson->getServiceTblPersonTo())) {
                        $displayPerson = $personTo;
                    } else {
                        $displayPerson = false;
                    }

                    $result[] = array(
                        'Number' => $count++,
                        'Type' => 'Personenbeziehung',
                        'Value' =>  $displayPerson ? $displayPerson->getLastFirstName() . ' (' . $tblType->getName() . ')' : ' Person nicht mehr vorhanden',
                        'EntityRemove' => $tblToPerson->getEntityRemove()
                    );

                    if ($isRestore) {
                        Relationship::useService()->restoreToPerson($tblToPerson);
                    }
                }
            }
        }
        // Company Relationship
        if (($tblRelationshipToPersonList = Relationship::useService()->getCompanyRelationshipAllByPerson($tblPerson, true))){
            foreach($tblRelationshipToPersonList as $tblToCompany){
                if (($tblCompany = $tblToCompany->getServiceTblCompany())
                    && ($tblType = $tblToCompany->getTblType())
                ) {

                    $result[] = array(
                        'Number' => $count++,
                        'Type' => 'Firmenbeziehung',
                        'Value' =>  $tblCompany->getDisplayName() . ' (' . $tblType->getName() . ')',
                        'EntityRemove' => $tblToCompany->getEntityRemove()
                    );

                    if ($isRestore) {
                        Relationship::useService()->restoreToCompany($tblToCompany);
                    }
                }
            }
        }

        // Division
        if (($tblDivisionList = Division::useService()->getDivisionStudentAllByPerson($tblPerson, true))) {
            foreach ($tblDivisionList as $tblDivisionStudent) {
                if (($tblDivision = $tblDivisionStudent->getTblDivision())
                    && ($tblYear = $tblDivision->getServiceTblYear())
                ) {

                    $result[] = array(
                        'Number' => $count++,
                        'Type' => 'Klassenzuordnung',
                        'Value' =>  $tblDivision->getDisplayName() . ' (' . $tblYear->getDisplayName() . ')',
                        'EntityRemove' => $tblDivisionStudent->getEntityRemove()
                    );

                    if ($isRestore) {
                        Division::useService()->restoreDivisionStudent($tblDivisionStudent);
                    }
                }
            }
        }
        // Absence
        if (($tblAbsenceList = Absence::useService()->getAbsenceAllByPerson($tblPerson, null, true))){
            $result[] = array(
                'Number' => $count,
                'Type' => 'Fehlzeiten',
                'Value' =>  count($tblAbsenceList),
                'EntityRemove' => ''
            );

            foreach ($tblAbsenceList as $tblAbsence) {
                Absence::useService()->restoreAbsence($tblAbsence);
            }
        }

        return $result;
    }

    /**
     * $tblNationalityAll, $tblDenominationAll
     *
     * @return array
     */
    public function getCommonInformationForAutoComplete()
    {
        $tblCommonInformationAll = Common::useService()->getCommonInformationAll();
        $tblNationalityAll = array();
        $tblDenominationAll = array();
        if ($tblCommonInformationAll) {
            array_walk($tblCommonInformationAll,
                function (TblCommonInformation &$tblCommonInformation) use (&$tblNationalityAll, &$tblDenominationAll) {

                    if ($tblCommonInformation->getNationality()) {
                        if (!in_array($tblCommonInformation->getNationality(), $tblNationalityAll)) {
                            array_push($tblNationalityAll, $tblCommonInformation->getNationality());
                        }
                    }
                    if ($tblCommonInformation->getDenomination()) {
                        if (!in_array($tblCommonInformation->getDenomination(), $tblDenominationAll)) {
                            array_push($tblDenominationAll, $tblCommonInformation->getDenomination());
                        }
                    }
                });
            $DefaultDenomination = array(
                'Altkatholisch',
                'Evangelisch',
                'Evangelisch-lutherisch',
                'Evangelisch-reformiert',
                'Französisch-reformiert',
                'Freireligiöse Landesgemeinde Baden',
                'Freireligiöse Landesgemeinde Pfalz',
                'Israelitische Religionsgemeinschaft Baden',
                'Römisch-katholisch',
                'Saarland: israelitisch'
            );
            array_walk($DefaultDenomination, function ($Denomination) use (&$tblDenominationAll) {

                if (!in_array($Denomination, $tblDenominationAll)) {
                    array_push($tblDenominationAll, $Denomination);
                }
            });
        }

        return array($tblNationalityAll, $tblDenominationAll);
    }

    /**
     * @param IFormInterface|null $form
     * @param null $Data
     *
     * @return IFormInterface|string|null
     */
    public function CreateFamily(
        IFormInterface $form = null, $Data = null
    ) {
        /**
         * Skip to Frontend
         */
        if (null === $Data || empty($Data)) {
            return $form;
        }

        $children = array();
        $custodies = array();
        $hasErrors = false;
        $Errors = array();
        $rankingCustodyList = array(
            1 => false,
            2 => false,
        );
        $personIdList = array();

        ksort($Data);

        if (($tblSetting = Consumer::useService()->getSetting('People', 'Person', 'Relationship', 'GenderOfS1'))
            && ($value = $tblSetting->getValue())
        ) {
            $genderSetting = Common::useService()->getCommonGenderById($value);
        } else {
            $genderSetting = false;
        }

        if (isset($Data['S1']['Gender']) && isset($Data['S2']['Gender'])
            && $Data['S1']['Gender'] == 0
            && $Data['S2']['Gender'] == 0
        ) {
            $hasCustodiesGenders = false;
        } else {
            $hasCustodiesGenders = true;
        }

        foreach($Data as $key => $person) {
            $type = substr($key, 0, 1);
//            $ranking = substr($key, 1);

            if ($type == 'C') {
                // Student / Prospect

                $errorChild = false;
                $isAdd = false;

                $tblSalutation = Person::useService()->getSalutationById($person['Salutation']);
                $firstName = $person['FirstName'];
                $lastName = $person['LastName'];
                $secondName = $person['SecondName'];
                $callName = $person['CallName'];
                $tblGroup = Group::useService()->getGroupById($person['Group']);
                $birthday = $person['Birthday'];
                $birthplace = $person['Birthplace'];
                $tblCommonGender = Common::useService()->getCommonGenderById($person['Gender']);
                $nationality = $person['Nationality'];
                $denomination = $person['Denomination'];

                if ($tblSalutation ||  $firstName || $lastName || $secondName || $callName || $birthday || $birthplace
                    || $tblCommonGender || $nationality || $denomination || $tblGroup
                ) {
                    $isAdd = true;
                    $this->setMessage($firstName, $key, 'FirstName', 'Bitte geben Sie einen Vornamen ein.', $Errors, $errorChild);
                    $this->setMessage($lastName, $key, 'LastName', 'Bitte geben Sie einen Nachnamen ein.', $Errors, $errorChild);
                    $this->setMessage($tblGroup, $key, 'Group', 'Bitte wählen Sie eine Gruppe aus.', $Errors, $errorChild);
                }

                if ($errorChild) {
                    $hasErrors = true;
                } elseif ($isAdd) {
                    $children[$key] = array(
                        'tblSalutation' => $tblSalutation ? $tblSalutation : null,
                        'FirstName' => $firstName,
                        'LastName' => $lastName,
                        'SecondName' => $secondName,
                        'CallName' => $callName,
                        'tblGroup' => $tblGroup,
                        'Birthday' => $birthday,
                        'Birthplace' => $birthplace,
                        'tblCommonGender' => $tblCommonGender,
                        'Nationality' => $nationality,
                        'Denomination' => $denomination,
                        'IsSibling' => isset($person['IsSibling'])
                    );
                }
            } else {
                // Custody

                $errorCustody = false;
                $isAdd = false;

                $tblSalutation = Person::useService()->getSalutationById($person['Salutation']);
                $title = $person['Title'];
                $firstName = $person['FirstName'];
                $lastName = $person['LastName'];
                $secondName = $person['SecondName'];
                $birthName = $person['BirthName'];
                $occupation = $person['Occupation'];
                $employment = $person['Employment'];

                $tblCommonGender = Common::useService()->getCommonGenderById($person['Gender']);
                $isSingleParent = isset($person['IsSingleParent']);
                $relationshipRemark = $person['RelationshipRemark'];

                if ($tblSalutation || $title || $firstName || $lastName || $secondName || $birthName || $occupation || $employment || $tblCommonGender) {
                    $isAdd = true;
                    $this->setMessage($firstName, $key, 'FirstName', 'Bitte geben Sie einen Vornamen ein.', $Errors, $errorCustody);
                    $this->setMessage($lastName, $key, 'LastName', 'Bitte geben Sie einen Nachnamen ein.', $Errors, $errorCustody);
                }

                if ($errorCustody) {
                    $hasErrors = true;
                } elseif ($isAdd) {
                    if ($hasCustodiesGenders) {
                        $rankingCustody = 3;
                        // S1 ermitteln
                        if ($tblCommonGender && $genderSetting && $tblCommonGender->getId() == $genderSetting->getId()
                            && !$rankingCustodyList[1]
                        ) {
                            $rankingCustody = 1;
                            $rankingCustodyList[1] = true;
                        } elseif (!$rankingCustodyList[2]) {
                            $rankingCustody = 2;
                            $rankingCustodyList[2] = true;
                        }
                    } else {
                        if (!$rankingCustodyList[1]) {
                            $rankingCustody = 1;
                            $rankingCustodyList[1] = true;
                        } else {
                            $rankingCustody = 2;
                            $rankingCustodyList[2] = true;
                        }
                    }

                    $custodies[$key] = array(
                        'tblSalutation' => $tblSalutation ? $tblSalutation : null,
                        'Title' => $title,
                        'FirstName' => $firstName,
                        'LastName' => $lastName,
                        'SecondName' => $secondName,
                        'BirthName' => $birthName,
                        'Occupation' => $occupation,
                        'Employment' => $employment,
                        'tblCommonGender' => $tblCommonGender,
                        'Ranking' => $rankingCustody,
                        'IsSingleParent' => $isSingleParent,
                        'RelationshipRemark' => $relationshipRemark
                    );
                }
            }
        }

        if ($hasErrors) {
            return (new FrontendFamily())->formCreateFamily($Data, $Errors);
        } else {
            $siblingRelationships = array();
            $custodyRelationships = array();
            $tblGroupCommon = Group::useService()->getGroupByMetaTable('COMMON');
            $tblGroupCustody = Group::useService()->getGroupByMetaTable('CUSTODY');
            $tblTypeCustody = Relationship::useService()->getTypeByName('Sorgeberechtigt');
            $tblTypeSibling = Relationship::useService()->getTypeByName('Geschwisterkind');

            $groups[] = $tblGroupCommon;
            $groups[] = $tblGroupCustody;

            foreach ($children as $child) {
                if (($tblPerson = $this->insertPerson(
                    ($tblSalutation = $child['tblSalutation']) ? $tblSalutation->getId() : null,
                    '', $child['FirstName'], $child['SecondName'],
                    $child['LastName'], array($tblGroupCommon), '', '', $child['CallName'])
                )) {
                    $personIdList[] = $tblPerson->getId();

                    if (($tblGroup = $child['tblGroup'])) {
                        Group::useService()->addGroupPerson($tblGroup, $tblPerson);
                    }
                    Common::useService()->insertMeta(
                        $tblPerson,
                        $child['Birthday'],
                        $child['Birthplace'],
                        ($tblCommonGender = $child['tblCommonGender']) ? $tblCommonGender : null,
                        $child['Nationality'],
                        $child['Denomination'],
                        false,
                        '',
                        ''
                    );

                    if (isset($child['IsSibling'])) {
                        $siblingRelationships[] = $tblPerson;
                    }

                    $custodyRelationships[] = $tblPerson;
                }
            }
            foreach ($custodies as $key => $custody) {
                if (($tblPerson = $this->insertPerson(($tblSalutation = $custody['tblSalutation']) ? $tblSalutation->getId() : null,
                    $custody['Title'], $custody['FirstName'], $custody['SecondName'], $custody['LastName'], $groups, $custody['BirthName'])
                )) {
                    $personIdList[] = $tblPerson->getId();

                    Common::useService()->insertMeta(
                        $tblPerson,
                        '',
                        '',
                        ($tblCommonGender = $custody['tblCommonGender']) ? $tblCommonGender : null,
                        '',
                        '',
                        false,
                        '',
                        ''
                    );

                    Custody::useService()->insertMeta($tblPerson, $custody['Occupation'], $custody['Employment'], '');

                    foreach ($custodyRelationships as $child) {
                        Relationship::useService()->insertRelationshipToPerson(
                            $tblPerson,
                            $child,
                            $tblTypeCustody,
                            $custody['RelationshipRemark'],
                            $custody['Ranking'],
                            $custody['IsSingleParent']
                        );
                    }
                }
            }


            // Geschwisterkinder
            while (count($siblingRelationships) > 0) {
                $tblChildPerson = array_pop($siblingRelationships);
                foreach ($siblingRelationships as $tblPersonSibling) {
                    Relationship::useService()->insertRelationshipToPerson(
                        $tblChildPerson,
                        $tblPersonSibling,
                        $tblTypeSibling,
                        ''
                    );
                }
            }
        }

        if (count($personIdList) > 0) {
            return new Success('Die Personendaten wurden erfolgreich gespeichert',
                    new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . new Redirect('/People/Person/Family/CreateAddress', Redirect::TIMEOUT_SUCCESS,
                    array('PersonIdList' => $personIdList));
        } else {
            // es muss mindestens eine Person angelegt werden
            $Errors['Person'][] = 'Bitte legen Sie mindestens eine Person an.';
            return (new FrontendFamily())->formCreateFamily($Data, $Errors);
        }
    }

    /**
     * @param IFormInterface|null $form
     * @param null $PersonIdList
     * @param null $Data
     *
     * @return IFormInterface|string|null
     */
    public function CreateFamilyContact(
        IFormInterface $form = null, $PersonIdList = null, $Data = null
    ) {
        /**
         * Skip to Frontend
         */
        if (null === $Data || empty($Data)) {
            return $form;
        }

        $addressAddList = array();
        $mainAddressPersonList = array();
        $phoneAddList = array();
        $mailAddList = array();
        $hasErrors = false;
        $Errors = array();

        foreach($Data as $key => $item) {
            $type = substr($key, 0, 1);

            if ($type == 'A') {
                // Adressdaten

                $errorAddress = false;
                $isAdd = false;
                $tblPersonList = array();

                $tblType = Address::useService()->getTypeById($item['Type']);
                $streetName = $item['StreetName'];
                $streetNumber = $item['StreetNumber'];
                $cityCode = $item['CityCode'];
                $cityName = $item['CityName'];
                $cityDistrict = $item['CityDistrict'];
                $county = $item['County'];
                $tblState = Address::useService()->getStateById($item['State']);
                $nation = $item['Nation'];
                $remark = $item['Remark'];

                $countPersons = 0;
                if (isset($item['PersonList'])) {
                    foreach ($item['PersonList'] as $personId => $value) {
                        if (($tblPerson = Person::useService()->getPersonById($personId))) {
                            $tblPersonList[] = $tblPerson;
                            $countPersons++;

                            if ($tblType && $tblType->getName() == 'Hauptadresse') {
                                $mainAddressPersonList[$personId][] = 1;
                            }
                        }
                    }
                }

                if ($tblType || $streetName || $streetNumber || $cityCode || $cityName || $cityDistrict || $county
                    || $tblState || $nation || $remark
                ) {
                    $isAdd = true;
                    $this->setMessage($tblType, $key, 'Type', 'Bitte wählen Sie einen Typ aus.', $Errors, $errorAddress);
                    $this->setMessage($streetName, $key, 'StreetName', 'Bitte geben Sie eine Straße ein.', $Errors, $errorAddress);
                    $this->setMessage($streetNumber, $key, 'StreetNumber', 'Bitte geben Sie eine Hausnummer ein.', $Errors, $errorAddress);
                    $this->setMessage($cityCode, $key, 'CityCode', 'Bitte geben Sie eine Postleitzahl ein.', $Errors, $errorAddress);
                    $this->setMessage($cityName, $key, 'CityName', 'Bitte geben Sie einen Ort ein.', $Errors, $errorAddress);

                    if ($countPersons == 0) {
                        $errorAddress = true;
                        $Errors[$key]['Message'] = 'Bitte wählen Sie mindestens eine Person für diese Adresse aus.';
                    }
                }

                if ($errorAddress) {
                    $hasErrors = true;
                } elseif ($isAdd) {
                    $addressAddList[$key] = array(
                        'tblType' => $tblType,
                        'StreetName' => $streetName,
                        'StreetNumber' => $streetNumber,
                        'CityCode' => $cityCode,
                        'CityName' => $cityName,
                        'CityDistrict' => $cityDistrict,
                        'County' => $county,
                        'tblState' => $tblState,
                        'Nation' => $nation,
                        'Remark' => $remark,
                        'tblPersonList' => $tblPersonList
                    );
                }
            } elseif ($type == 'P') {
                // Telefonnummern

                $errorPhone = false;
                $isAdd = false;
                $tblPersonList = array();

                $tblType = Phone::useService()->getTypeById($item['Type']);
                $address = $item['Number'];
                $remark = $item['Remark'];

                $countPersons = 0;
                if (isset($item['PersonList'])) {
                    foreach ($item['PersonList'] as $personId => $value) {
                        if (($tblPerson = Person::useService()->getPersonById($personId))) {
                            $tblPersonList[] = $tblPerson;
                            $countPersons++;
                        }
                    }
                }

                if ($tblType || $address || $remark) {
                    $isAdd = true;
                    $this->setMessage($tblType, $key, 'Type', 'Bitte wählen Sie einen Typ aus.', $Errors, $errorPhone);
                    $this->setMessage($address, $key, 'Number', 'Bitte geben Sie eine Telefonnummer ein.', $Errors, $errorPhone);

                    if ($countPersons == 0) {
                        $errorPhone = true;
                        $Errors[$key]['Message'] = 'Bitte wählen Sie mindestens eine Person für diese Telefonnummer aus.';
                    }
                }

                if ($errorPhone) {
                    $hasErrors = true;
                } elseif ($isAdd) {
                    $phoneAddList[$key] = array(
                        'tblType' => $tblType,
                        'Number' => $address,
                        'Remark' => $remark,
                        'tblPersonList' => $tblPersonList
                    );
                }
            } elseif ($type == 'M') {
                // Emailadressen

                $errorMail = false;
                $isAdd = false;
                $tblPersonList = array();

                $tblType = Mail::useService()->getTypeById($item['Type']);
                $address = $this->validateMailAddress($item['Address']);
                $remark = $item['Remark'];
                $isAccountUserAlias = isset($item['IsAccountUserAlias']);
                $isAccountRecoveryMail = isset($item['IsAccountRecoveryMail']);

                $countPersons = 0;
                if (isset($item['PersonList'])) {
                    foreach ($item['PersonList'] as $personId => $value) {
                        if (($tblPerson = Person::useService()->getPersonById($personId))) {
                            $tblPersonList[] = $tblPerson;
                            $countPersons++;
                        }
                    }
                }

                if ($tblType || $address || $remark) {
                    $isAdd = true;
                    $this->setMessage($tblType, $key, 'Type', 'Bitte wählen Sie einen Typ aus.', $Errors, $errorMail);
                    $this->setMessage($address, $key, 'Address', 'Bitte geben Sie eine gültige E-Mail Adresse an', $Errors, $errorMail);

                    if ($countPersons == 0) {
                        $errorMail = true;
                        $Errors[$key]['Message'] = 'Bitte wählen Sie mindestens eine Person für diese E-Mail Adresse aus.';
                    }

                    // prüfen userAlias und recoverMail
                    if ($isAccountUserAlias || $isAccountRecoveryMail) {
                        // Es darf nur maximal eine Person ausgewählt werden
                        if ($countPersons != 1) {
                            $errorMail = true;
                            $Errors[$key]['Message'] = 'Zur Verwendung der E-Mail Adresse als UCS Benutzername 
                                oder UCS "Passwort vergessen" darf nur genau eine Person ausgewählt werden.';
                            $tblPersonMail = false;
                        } else {
                            $tblPersonMail = current($tblPersonList);
                        }

                        // Typ muss Geschäftlich sein bei UCS Alias
                        if ($isAccountUserAlias && $tblType && $tblType->getName() != 'Geschäftlich') {
                            $errorMail = true;
                            $Errors[$key]['Message'] = 'Zur Verwendung der E-Mail Adresse als UCS Benutzername 
                                muss der E-Mail Typ: Geschäftlich ausgewählt werden.';
                        }

                        // Eindeutigkeit UCS Alias
                        if ($isAccountUserAlias && $tblPersonMail) {
                            $errorMessage = '';
                            if (!Account::useService()->isUserAliasUnique($tblPersonMail, $address, $errorMessage)) {
                                $errorMail = true;
                                $Errors[$key]['Message'] = $errorMessage;
                            }
                        }
                    }
                }

                if ($errorMail) {
                    $hasErrors = true;
                } elseif ($isAdd) {
                    $mailAddList[$key] = array(
                        'tblType' => $tblType,
                        'Address' => $address,
                        'Remark' => $remark,
                        'IsAccountUserAlias' => $isAccountUserAlias,
                        'IsAccountRecoveryMail' => $isAccountRecoveryMail,
                        'tblPersonList' => $tblPersonList
                    );
                }
            }
        }

        // Prüfungen alle Personen muss mindestens eine Hauptadresse zugewiesen werden,
        // weiterhin darf pro Person nur eine Hauptadresse zugewiesen werden
        foreach ($PersonIdList as $Id) {
            if (($tblPerson = Person::useService()->getPersonById($Id))) {
                if (!isset($mainAddressPersonList[$Id])) {
                    $hasErrors = true;
                    $Errors['Address'][] = 'Bitte geben Sie für: ' . $tblPerson->getFullName() . ' eine Hauptadresse an.';
                } elseif (count($mainAddressPersonList[$Id]) != 1) {
                    $hasErrors = true;
                    $Errors['Address'][] = $tblPerson->getFullName() . ' darf nur eine Hauptadresse besitzen.';
                }
            }
        }

        if ($hasErrors) {
            return (new FrontendFamily())->getFamilyAddressForm($PersonIdList, $Data, $Errors);
        } else {
            foreach ($addressAddList as $address) {
                $tblState = $address['tblState'];
                Address::useService()->insertAddressToPersonList(
                    $address['tblType'],
                    $address['StreetName'],
                    $address['StreetNumber'],
                    $address['CityCode'],
                    $address['CityName'],
                    $address['CityDistrict'],
                    '',
                    $address['County'],
                    $address['Nation'],
                    $address['tblPersonList'],
                    $tblState ? $tblState : null,
                    $address['Remark']
                );
            }

            foreach ($phoneAddList as $phone) {
                Phone::useService()->insertPhoneToPersonList(
                    $phone['Number'],
                    $phone['tblType'],
                    $phone['Remark'],
                    $phone['tblPersonList']
                );
            }

            foreach ($mailAddList as $mail) {
                Mail::useService()->insertMailToPersonList(
                    $mail['Address'],
                    $mail['tblType'],
                    $mail['Remark'],
                    $mail['IsAccountUserAlias'],
                    $mail['IsAccountRecoveryMail'],
                    $mail['tblPersonList']
                );
            }

            return new Success('Die Kontaktdaten wurden erfolgreich gespeichert.')
                . new Redirect('/People/Person', Redirect::TIMEOUT_SUCCESS, array('Id' => $PersonIdList[0]));
        }
    }

    /**
     * @param $variable
     * @param $key
     * @param $identifier
     * @param $message
     * @param $Errors
     * @param $errorAddress
     */
    private function setMessage($variable, $key, $identifier, $message, &$Errors, &$errorAddress)
    {
        if (!$variable) {
            $errorAddress = true;
            $Errors[$key][$identifier] = array(
                'IsError' => true,
                'Message' => $message
            );
        } else {
            $Errors[$key][$identifier] = array(
                'IsError' => false,
                'Message' => ''
            );
        }
    }
}
