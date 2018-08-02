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
use SPHERE\Application\People\Meta\Custody\Custody;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Service\Data;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\People\Person\Service\Setup;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Message\Repository\Danger;
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
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
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
     * @param IFormInterface|null $Form
     * @param $Person
     *
     * @return IFormInterface|string
     */
    public function createPerson(IFormInterface $Form = null, $Person)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Person) {
            return $Form;
        }

        $Error = false;

        if (isset( $Person['FirstName'] ) && empty( $Person['FirstName'] )) {
            $Form->setError('Person[FirstName]', 'Bitte geben Sie einen Vornamen an');
            $Error = true;
        }
        if (isset( $Person['LastName'] ) && empty( $Person['LastName'] )) {
            $Form->setError('Person[LastName]', 'Bitte geben Sie einen Nachnamen an');
            $Error = true;
        }

        if (!$Error) {

            if (( $tblPerson = (new Data($this->getBinding()))->createPerson(
                $this->getSalutationById($Person['Salutation']), $Person['Title'], $Person['FirstName'],
                $Person['SecondName'], $Person['LastName'], $Person['BirthName']) )
            ) {
                // Add to Group
                if (isset( $Person['Group'] )) {
                    foreach ((array)$Person['Group'] as $GroupId) {
                        $tblGroup = Group::useService()->getGroupById($GroupId);
                        if($tblGroup){
                            Group::useService()->addGroupPerson(
                                $tblGroup, $tblPerson
                            );
                        }
                    }
                }
                return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Person wurde erfolgreich erstellt')
                .new Redirect('/People/Person', Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $tblPerson->getId())
                );
            } else {
                return new Danger(new Ban() . ' Die Person konnte nicht erstellt werden')
                .new Redirect('/People/Person', Redirect::TIMEOUT_ERROR);
            }
        }

        return $Form;
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
     *
     * @return bool|TblPerson
     */
    public function insertPerson($Salutation, $Title, $FirstName, $SecondName, $LastName, $GroupList, $BirthName = '', $ImportId = '')
    {

        if (( $tblPerson = (new Data($this->getBinding()))->createPerson(
            $Salutation, $Title, $FirstName, $SecondName, $LastName, $BirthName, $ImportId) )
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
     * @param IFormInterface|null $Form
     * @param TblPerson $tblPerson
     * @param $Person
     * @param $Group
     *
     * @return IFormInterface|string
     */
    public function updatePerson(IFormInterface $Form = null, TblPerson $tblPerson, $Person, $Group)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Person) {
            return $Form;
        }

        $Error = false;

        if (isset( $Person['FirstName'] ) && empty( $Person['FirstName'] )) {
            $Form->setError('Person[FirstName]', 'Bitte geben Sie einen Vornamen an');
            $Error = true;
        }
        if (isset( $Person['LastName'] ) && empty( $Person['LastName'] )) {
            $Form->setError('Person[LastName]', 'Bitte geben Sie einen Nachnamen an');
            $Error = true;
        }

        if (!$Error) {

            if ((new Data($this->getBinding()))->updatePerson($tblPerson, $Person['Salutation'], $Person['Title'],
                $Person['FirstName'], $Person['SecondName'], $Person['LastName'], $Person['BirthName'])
            ) {
                // Change Groups
                if (isset( $Person['Group'] )) {
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
                return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Person wurde erfolgreich aktualisiert')
                .new Redirect(null, Redirect::TIMEOUT_SUCCESS);
            } else {
                return new Danger(new Ban() . 'Die Person konnte nicht aktualisiert werden')
                .new Redirect('/People/Person', Redirect::TIMEOUT_ERROR);
            }
        }

        return $Form;
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
    public function  existsPerson($FirstName, $LastName, $ZipCode)
    {

        $exists = false;

        if (( $persons = (new Data($this->getBinding()))->getPersonAllByFirstNameAndLastName($FirstName, $LastName) )
        ) {
            foreach ($persons as $person) {
                if (( $addresses = Address::useService()->getAddressAllByPerson($person) )) {
                    if ($addresses[0]->getTblAddress()->getTblCity()->getCode() == $ZipCode) {
                        $exists = $person;
                    }
                }
            }
        }

        return $exists;
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
     * @param TblPerson $tblPerson
     *
     * @return array
     */
    public function getDestroyDetailList(TblPerson $tblPerson)
    {

        $list[] = new Bold('Person: ' . $tblPerson->getLastFirstName());
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
}
