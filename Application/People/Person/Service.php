<?php
namespace SPHERE\Application\People\Person;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Lesson\Division\Division;
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
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Extension\Repository\Debugger;

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
     * @return bool|TblSalutation[]
     */
    public function getSalutationAll()
    {

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
     * @param string $Birthday
     *
     * @return bool|TblPerson
     */
    public function getPersonByNameAndBirthday($FirstName, $LastName, $Birthday)
    {

        $tblPersonList = (new Data($this->getBinding()))->getPersonAllByFirstNameAndLastName($FirstName, $LastName);

        if ($tblPersonList) {
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
                    return $tblPerson;
                }
            }
        }
        return false;
    }

    /**
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
     * @param string $FirstName
     * @param string $LastName
     *
     * @return bool|TblPerson
     */
    public function getPersonByName($FirstName, $LastName)
    {

        $tblPersonList = (new Data($this->getBinding()))->getPersonAllByFirstNameAndLastName($FirstName, $LastName);
        if($tblPersonList){
            return current($tblPersonList);
        }
        return false;
    }


    /**
     * @param string $FirstName
     * @param string $LastName
     *
     * @return bool|TblPerson[]
     */
    public function getPersonAllByName($FirstName, $LastName)
    {

        return (new Data($this->getBinding()))->getPersonAllByFirstNameAndLastName($FirstName, $LastName);
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

        if (( $tblPersonList = (new Data($this->getBinding()))->getPersonAllByFirstNameAndLastName($FirstName, $LastName) )
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
        $error = false;

        ksort($Data);

        foreach($Data as $key => $person) {
            $type = substr($key, 0, 1);
            $ranking = substr($key, 1);

            if ($type == 'C') {
                // Student / Prospect

                $errorChild = false;
                $isAdd = false;

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

                if ($firstName || $lastName || $secondName || $callName || $birthday || $birthplace || $tblCommonGender
                    || $nationality || $denomination
                ) {
                    $isAdd = true;
                    if (!$firstName) {
                        $errorChild = true;
                        // todo funktioniert hier noch nicht
                        $form->setError('Data[' . $key . '][FirstName]', 'Bitte geben Sie einen Vornamen ein.' );
                    } else {
                        $form->setSuccess('Data[' . $key . '][FirstName]');
                    }

                    if (!$lastName) {
                        $errorChild = true;
                        $form->setError('Data[' . $key . '][LastName]', 'Bitte geben Sie einen Nachnamen ein.' );
                    } else {
                        $form->setSuccess('Data[' . $key . '][LastName]');
                    }
                }

                if ($errorChild) {
                    $error = true;
                } elseif ($isAdd) {
                    $children[$key] = array(
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
                $birthName = $person['BirthName'];
                $tblCommonGender = Common::useService()->getCommonGenderById($person['Gender']);
                $isSingleParent = isset($person['IsSingleParent']);

                if ($tblSalutation || $title || $firstName || $lastName || $birthName || $tblCommonGender) {
                    $isAdd = true;
                    if (!$firstName) {
                        $errorCustody = true;
                        $form->setError('Data[' . $key . '][FirstName]', 'Bitte geben Sie einen Vornamen ein.' );
                    } else {
                        $form->setSuccess('Data[' . $key . '][FirstName]');
                    }

                    if (!$lastName) {
                        $errorCustody = true;
                        $form->setError('Data[' . $key . '][LastName]', 'Bitte geben Sie einen Nachnamen ein.' );
                    } else {
                        $form->setSuccess('Data[' . $key . '][LastName]');
                    }
                }

                if ($errorCustody) {
                    $error = true;
                } elseif ($isAdd) {
                    $custodies[$key] = array(
                        'tblSalutation' => $tblSalutation ? $tblSalutation : null,
                        'Title' => $title,
                        'FirstName' => $firstName,
                        'LastName' => $lastName,
                        'BirthName' => $birthName,
                        'tblCommonGender' => $tblCommonGender,
                        'Ranking' => $ranking,
                        'IsSingleParent' => $isSingleParent
                    );
                }
            }
        }

        if ($error) {
            return $form;
        } else {
            $siblingRelationships = array();
            $custodyRelationships = array();
            $personIdList = array();
            $tblGroupCommon = Group::useService()->getGroupByMetaTable('COMMON');
            $tblGroupCustody = Group::useService()->getGroupByMetaTable('CUSTODY');
            $tblTypeCustody = Relationship::useService()->getTypeByName('Sorgeberechtigt');
            $tblTypeSibling = Relationship::useService()->getTypeByName('Geschwisterkind');

            $groups[] = $tblGroupCommon;
            $groups[] = $tblGroupCustody;

            foreach ($children as $child) {
                if (($tblPerson = $this->insertPerson(null, '', $child['FirstName'], $child['SecondName'],
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
                    $custody['Title'], $custody['FirstName'], '', $custody['LastName'], $groups, $custody['BirthName'])
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

                    foreach ($custodyRelationships as $child) {
                        Relationship::useService()->insertRelationshipToPerson(
                            $tblPerson,
                            $child,
                            $tblTypeCustody,
                            '',
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

        return new Success('Die Personendaten wurden erfolgreich gespeichert', new \SPHERE\Common\Frontend\Icon\Repository\Success())
            . new Redirect('/People/Person/Family/CreateAddress', Redirect::TIMEOUT_SUCCESS, array('PersonIdList' => $personIdList));
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
        $phoneAddList = array();
        $mailAddList = array();
        $hasErrors = false;
        $Errors = array();

        Debugger::screenDump($Data);

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

                if (isset($item['PersonList'])) {
                    foreach ($item['PersonList'] as $personId => $value) {
                        if (($tblPerson = Person::useService()->getPersonById($personId))) {
                            $tblPersonList[] = $tblPerson;
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
                }

                // todo Prüfungen alle Personen muss mindestens eine Hauptadresse zugewiesen werden
                // es darf pro Person nur eine Hauptadresse zugewiesen werden
                // es muss mindestens eine Person ausgewählt sein

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

                if (isset($item['PersonList'])) {
                    foreach ($item['PersonList'] as $personId => $value) {
                        if (($tblPerson = Person::useService()->getPersonById($personId))) {
                            $tblPersonList[] = $tblPerson;
                        }
                    }
                }

                if ($tblType || $address || $remark) {
                    $isAdd = true;
                    $this->setMessage($tblType, $key, 'Type', 'Bitte wählen Sie einen Typ aus.', $Errors, $errorPhone);
                    $this->setMessage($address, $key, 'Number', 'Bitte geben Sie eine Telefonnummer ein.', $Errors, $errorPhone);
                }

                // todo es muss mindestens eine Person ausgewählt werden

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
                $address = $item['Address'];
                $remark = $item['Remark'];

                if (isset($item['PersonList'])) {
                    foreach ($item['PersonList'] as $personId => $value) {
                        if (($tblPerson = Person::useService()->getPersonById($personId))) {
                            $tblPersonList[] = $tblPerson;
                        }
                    }
                }

                if ($tblType || $address || $remark) {
                    $isAdd = true;
                    $this->setMessage($tblType, $key, 'Type', 'Bitte wählen Sie einen Typ aus.', $Errors, $errorMail);
                    $this->setMessage($address, $key, 'Address', 'Bitte geben Sie eine E-Mail Adresse ein.', $Errors, $errorMail);
                }

                // todo es muss mindestens eine Person ausgewählt werden

                if ($errorMail) {
                    $hasErrors = true;
                } elseif ($isAdd) {
                    $mailAddList[$key] = array(
                        'tblType' => $tblType,
                        'Address' => $address,
                        'Remark' => $remark,
                        'tblPersonList' => $tblPersonList
                    );
                }
            }
        }

        if ($hasErrors) {
            return (new FrontendFamily())->getFamilyAddressForm($PersonIdList, $Data, $Errors);
        } else {
            // todo test create
            foreach ($addressAddList as $address) {
                $tblState = $address['tblState'];
                Address::useService()->insertAddressToPersonList(
                    $address['tblType'],
                    $address['StreetName'],
                    $address['StreetNumber'],
                    $address['CityCode'],
                    $address['CityName'],
                    $address['CityDistrict'],
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
                    $mail['tblPersonList']
                );
            }

            // todo return success
            // weiterleiten zum 1. Kind
        }

        return $form;
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
