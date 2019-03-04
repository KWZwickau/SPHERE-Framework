<?php
namespace SPHERE\Application\Platform\System\Anonymous;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Group\Group as GroupCompany;
use SPHERE\Application\Corporation\Group\Service\Entity\TblGroup as TblGroupCompany;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\System\Anonymous\Service\Setup;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\Library\RandomGenerator\RandomCity;
use SPHERE\Library\RandomGenerator\RandomName;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Platform\System\Anonymous
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {

        // no DataBaseContent
        $Protocol = '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        return $Protocol;
    }

    /**
     * @return string
     */
    public function UpdatePerson()
    {

        $tblPersonAll = Person::useService()->getPersonAll();
        $PersonList = array();
        if($tblPersonAll){
            array_walk($tblPersonAll, function(TblPerson $tblPerson) use (&$PersonList){
                $PersonList[$tblPerson->getId()] = $tblPerson;
            });
            ksort($PersonList);
        }
        if(!empty($PersonList)){
            $Random = new RandomName();
            $PersonDoneList = array();
            $ProcessList = array();
            array_walk($PersonList, function(TblPerson $tblPerson) use ($Random, &$PersonDoneList, &$ProcessList){
                $lastName = $Random->getLastName();
                // bereits verarbeitete Personen in ruhe lassen
                if(!in_array($tblPerson->getId(), $PersonDoneList)){
//                    Person::useService()->updatePersonName($tblPerson, $Random->getFirstName(), $lastName);
                    $PersonDoneList[] = $tblPerson->getId();
                    $Gender = $this->getGenderByPerson($tblPerson);
                    $ProcessList[$tblPerson->getId()]['Person'] = $tblPerson;
                    $ProcessList[$tblPerson->getId()]['FirstName'] = $Random->getFirstName($Gender);
                    $ProcessList[$tblPerson->getId()]['LastName'] = $lastName;
                    // Personen in Beziehung erhalten gleichen Nachnamen
                    if(($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson))){
                        foreach($tblRelationshipList as $tblRelationship) {
                            if(($tblPersonFrom = $tblRelationship->getServiceTblPersonFrom())
                                && $tblPersonFrom->getId() != $tblPerson->getId()
                                && !in_array($tblPersonFrom->getId(), $PersonDoneList)){
//                                Person::useService()->updatePersonName($tblPersonFrom, $Random->getFirstName(), $lastName);
                                $Gender = $this->getGenderByPerson($tblPersonFrom);
                                $PersonDoneList[] = $tblPersonFrom->getId();
                                $ProcessList[$tblPersonFrom->getId()]['Person'] = $tblPersonFrom;
                                $ProcessList[$tblPersonFrom->getId()]['FirstName'] = $Random->getFirstName($Gender);
                                $ProcessList[$tblPersonFrom->getId()]['LastName'] = $lastName;
                            } elseif(($tblPersonTo = $tblRelationship->getServiceTblPersonTo())
                                && $tblPersonTo->getId() != $tblPerson->getId()
                                && !in_array($tblPersonTo->getId(), $PersonDoneList)) {
//                                Person::useService()->updatePersonName($tblPersonFrom, $Random->getFirstName(), $lastName);
                                $Gender = $this->getGenderByPerson($tblPersonTo);
                                $PersonDoneList[] = $tblPersonTo->getId();
                                $ProcessList[$tblPersonTo->getId()]['Person'] = $tblPersonTo;
                                $ProcessList[$tblPersonTo->getId()]['FirstName'] = $Random->getFirstName($Gender);
                                $ProcessList[$tblPersonTo->getId()]['LastName'] = $lastName;
                            }
                        }
                    }
                }
            });
            if(!empty($ProcessList)){

                Person::useService()->updatePersonAnonymousBulk($ProcessList);
            }
        }
        $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_TEACHER);
        if(($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))){
            $TeacherList = array();
            array_walk($tblPersonList, function(TblPerson $tblPerson) use (&$TeacherList){
                $Acronym = substr($tblPerson->getLastName(),0,1).substr($tblPerson->getFirstName(), 0, 1);
                if(($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPerson))){
                    $TeacherList[$tblTeacher->getId()] = $Acronym;
                }
            });
            Teacher::useService()->updateTeacherAcronymBulk($TeacherList);
        }

        return new Success('Personen wurden erfolgreich Anonymisiert')
            .new Redirect('/Platform/System/Anonymous', Redirect::TIMEOUT_SUCCESS);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return string
     */
    private function getGenderByPerson(TblPerson $tblPerson)
    {

        if(($tblGender = $tblPerson->getGender())){
            // gender by MetaData gender
            $Gender = ($tblGender->getName() == 'Männlich'
                ? RandomName::ATTR_MALE
                : ($tblGender->getName() == 'Weiblich'
                    ? RandomName::ATTR_FEMALE
                    : ''));
        } else {
            // fallback -> salutation to pick gender
            $Salutation = $tblPerson->getSalutation();
            $Gender = ($Salutation == 'Herr'
                ? RandomName::ATTR_MALE
                : ($Salutation == 'Frau'
                    ? RandomName::ATTR_FEMALE
                    : ''));
        }
        return $Gender;
    }

    /**
     * @return string
     */
    public function UpdateAddress()
    {

        $tblAddressAll = Address::useService()->getAddressAll();
        if($tblAddressAll){
            $Random = new RandomCity();
            $ProcessList = array();
            foreach($tblAddressAll as $tblAddress) {
                $ProcessList[$tblAddress->getId()]['tblAddress'] = $tblAddress;
                $ProcessList[$tblAddress->getId()]['tblCity'] = $tblAddress->getTblCity();
                $ProcessList[$tblAddress->getId()]['City'] = $Random->getCityName();
            }
            if(!empty($ProcessList)){
                // second val override random City
                Address::useService()->updateAddressAnonymousBulk($ProcessList, '');
            }
        }
        return new Success('Adressen wurden erfolgreich Anonymisiert')
            .new Redirect('/Platform/System/Anonymous', Redirect::TIMEOUT_SUCCESS);
    }

    /**
     * @return string
     */
    public function UpdateCompany()
    {

        $tblCompanyAll = Company::useService()->getCompanyAll();
        $count = 0;
        if($tblCompanyAll){
            $ProcessList = array();

            foreach($tblCompanyAll as $tblCompany) {
                $count++;
                $Name = 'Institution '.str_pad($count, 3, '0', STR_PAD_LEFT);
                if(($tblGroupList = GroupCompany::useService()->getGroupAllByCompany($tblCompany))){
                    foreach($tblGroupList as $tblGroup){
                        if($tblGroup->getMetaTable() == TblGroupCompany::ATTR_SCHOOL){
                            // schule priorisieren
                            $Name = 'Schule '.str_pad($count, 3, '0', STR_PAD_LEFT);
                            break;
                        }
                        if($tblGroup->getMetaTable() == TblGroupCompany::ATTR_NURSERY){
                            // Kindergarten alternativ
                            $Name = 'Kindergarten '.str_pad($count, 3, '0', STR_PAD_LEFT);
                        }
                    }
                }
                $ProcessList[$tblCompany->getId()]['tblCompany'] = $tblCompany;
                $ProcessList[$tblCompany->getId()]['Name'] = $Name;
            }
            if(!empty($ProcessList)){
                Company::useService()->updateCompanyAnonymousBulk($ProcessList);
            }
        }
        return new Success('Institutionen wurden erfolgreich Anonymisiert')
            .new Redirect('/Platform/System/Anonymous', Redirect::TIMEOUT_SUCCESS);
    }
}
