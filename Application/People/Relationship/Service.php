<?php
namespace SPHERE\Application\People\Relationship;

use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Service\Data;
use SPHERE\Application\People\Relationship\Service\Entity\TblGroup;
use SPHERE\Application\People\Relationship\Service\Entity\TblSiblingRank;
use SPHERE\Application\People\Relationship\Service\Entity\TblToCompany;
use SPHERE\Application\People\Relationship\Service\Entity\TblToPerson;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Application\People\Relationship\Service\Entity\ViewRelationshipFromPerson;
use SPHERE\Application\People\Relationship\Service\Entity\ViewRelationshipToCompany;
use SPHERE\Application\People\Relationship\Service\Entity\ViewRelationshipToPerson;
use SPHERE\Application\People\Relationship\Service\Setup;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\People\Relationship
 */
class Service extends AbstractService
{

    /**
     * @return false|ViewRelationshipToPerson[]
     */
    public function viewRelationshipToPerson()
    {

        return (new Data($this->getBinding()))->viewRelationshipToPerson();
    }

    /**
     * @return false|ViewRelationshipFromPerson[]
     */
    public function viewRelationshipFromPerson()
    {

        return ( new Data($this->getBinding()) )->viewRelationshipFromPerson();
    }

    /**
     * @return false|ViewRelationshipToCompany[]
     */
    public function viewRelationshipToCompany()
    {

        return ( new Data($this->getBinding()) )->viewRelationshipToCompany();
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
     * @param TblPerson $tblPerson
     * @param TblType|null $tblType
     * @param bool $isForced
     *
     * @return bool|TblToPerson[]
     */
    public function getPersonRelationshipAllByPerson(TblPerson $tblPerson, TblType $tblType = null, $isForced = false)
    {

        return (new Data($this->getBinding()))->getPersonRelationshipAllByPerson($tblPerson, $tblType, $isForced);
    }

    /**
     * @param TblToPerson[] $tblToPersonList
     *
     * @return array|TblPerson[]
     * sortet by Gender (0 => mother - 1 = father - 2... => unknown)
     * without hits on Mother or Father the unknown get the 0 and 1
     */
    public function getPersonGuardianAllByToPersonList($tblToPersonList)
    {

        $GuardianList = array();
        if ($tblToPersonList && !empty($tblToPersonList)) {
            $i = 2;
            foreach ($tblToPersonList as $tblToPerson) {
                $tblPersonGuardian = $tblToPerson->getServiceTblPersonFrom();
                // get Gender
                $Gender = '';
                if ($tblPersonGuardian && ($common = Common::useService()->getCommonByPerson($tblPersonGuardian))) {
                    if (($tblCommonBirthDates = $common->getTblCommonBirthDates())) {
                        if (($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())) {
                            $Gender = $tblCommonGender->getName();
                        }
                    }
                }
                if ($Gender == '') {
                    $Salutation = $tblPersonGuardian->getSalutation();
                    if ($Salutation == 'Frau') {
                        $Gender = 'Weiblich';
                    } elseif ($Salutation == 'Herr') {
                        $Gender = 'Männlich';
                    }
                }
                // get sorted List (0 => Mother; 1 => Father; 2.. => Other )
                if ($Gender == 'Weiblich') {
                    if (isset($GuardianList[0])) {
                        if (!isset($GuardianList[1])) {
                            $GuardianList[1] = $GuardianList[0];
                        } else {
                            $GuardianList[$i++] = $GuardianList[0];
                        }
                    }
                    $GuardianList[0] = $tblToPerson->getServiceTblPersonFrom();
                } elseif (!isset($GuardianList[1]) && $Gender == 'Männlich') {
                    if (isset($GuardianList[1])) {
                        if (!isset($GuardianList[0])) {
                            $GuardianList[0] = $GuardianList[1];
                        } else {
                            $GuardianList[$i++] = $GuardianList[1];
                        }
                    }
                    $GuardianList[1] = $tblToPerson->getServiceTblPersonFrom();
                } else {
                    // if no matches set unknown to Mother/Father to keep it running
                    $GuardianList[] = $tblToPerson->getServiceTblPersonFrom();
                }
            }
        }
        return $GuardianList;
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool $isForced
     *
     * @return bool|TblToCompany[]
     */
    public function getCompanyRelationshipAllByPerson(TblPerson $tblPerson, $isForced = false)
    {

        return (new Data($this->getBinding()))->getCompanyRelationshipAllByPerson($tblPerson, $isForced);
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return bool|TblToCompany[]
     */
    public function getCompanyRelationshipAllByCompany(TblCompany $tblCompany)
    {

        return (new Data($this->getBinding()))->getCompanyRelationshipAllByCompany($tblCompany);
    }

    /**
     * @param TblPerson $tblPersonFrom
     * @param TblPerson $tblPersonTo
     * @param $Type
     *
     * @return bool
     */
    public function createRelationshipToPerson(
        TblPerson $tblPersonFrom,
        TblPerson $tblPersonTo,
        $Type
    ) {

        // bei der virtuellen Beziehung vom Typ Kind werden die Personen getauscht
        $tempPerson = $tblPersonFrom;
        if ($Type['Type'] == TblType::CHILD_ID) {
            $tblType = $this->getTypeByName('Sorgeberechtigt');
            $tblPersonFrom = $tblPersonTo;
            $tblPersonTo = $tempPerson;
        } else {
            $tblType = $this->getTypeById($Type['Type']);
        }

        if ($tblType) {
            if ((new Data($this->getBinding()))->addPersonRelationshipToPerson($tblPersonFrom, $tblPersonTo, $tblType,
                $Type['Remark'])
            ) {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * @param TblToPerson $tblToPerson
     * @param TblPerson $tblPersonFrom
     * @param $tblPersonTo
     * @param $Type
     *
     * @return bool
     */
    public function updateRelationshipToPerson(
        TblToPerson $tblToPerson,
        TblPerson $tblPersonFrom,
        TblPerson $tblPersonTo,
        $Type
    ) {

        // bei der virtuellen Beziehung vom Typ Kind werden die Personen getauscht
        $tempPerson = $tblPersonFrom;
        if ($Type['Type'] == TblType::CHILD_ID) {
            $tblType = $this->getTypeByName('Sorgeberechtigt');
            $tblPersonFrom = $tblPersonTo;
            $tblPersonTo = $tempPerson;
        } else {
            $tblType = $this->getTypeById($Type['Type']);
        }

        if (!$tblType) {
            return false;
        }

        // Remove current
        (new Data($this->getBinding()))->removePersonRelationshipToPerson($tblToPerson);
        // Add new
        if ((new Data($this->getBinding()))->addPersonRelationshipToPerson($tblPersonFrom, $tblPersonTo, $tblType,
            $Type['Remark'])
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Type
     * @param $To
     * @param TblToPerson|null $tblToPerson
     * @param string $Search
     *
     * @return bool|Form
     */
    public function checkFormRelationshipToPerson(
        TblPerson $tblPerson,
        $Type,
        $To,
        TblToPerson $tblToPerson = null,
        $Search = ''
    ) {

        $error = false;
        $message = null;
        if (empty($To)) {
            $message = new Danger('Bitte wählen Sie eine Person');
            $error = true;
        } else {
            $tblPersonTo = Person::useService()->getPersonById($To);
            if (!$tblPersonTo){
                $message = new Danger('Bitte wählen Sie eine Person');
                $error = true;
            }
            elseif ($tblPerson->getId() == $tblPersonTo->getId()) {
                $message = new Danger('Eine Person kann nur mit einer anderen Person verknüpft werden', new Exclamation());
                $error = true;
            }
        }
        $form = Relationship::useFrontend()->formRelationshipToPerson(
            $tblPerson->getId(),
            $tblToPerson ? $tblToPerson->getId() : null,
            false,
            $Search,
            $message
        );

        // bei der virtuellen Beziehung vom Typ Kind werden die Personen getauscht
        if ($Type['Type'] == TblType::CHILD_ID) {
            $tblType = $this->getTypeByName('Sorgeberechtigt');
        } else {
            $tblType = $this->getTypeById($Type['Type']);
        }

        if (!$tblType) {
            $form->setError('Type[Type]', 'Bitte geben Sie einen Typ an');
            $error = true;
        } else {
            $form->setSuccess('Type[Type]');
        }

        return $error ? $form : false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Type
     * @param $To
     * @param TblToCompany|null $tblToCompany
     * @param string $Search
     *
     * @return bool|Form
     */
    public function checkFormRelationshipToCompany(
        TblPerson $tblPerson,
        $Type,
        $To,
        TblToCompany $tblToCompany = null,
        $Search = ''
    ) {

        $error = false;
        $message = null;
        if (empty($To)) {
            $message = new Danger('Bitte wählen Sie eine Institution');
            $error = true;
        } else {
            $tblCompanyTo = Company::useService()->getCompanyById($To);
            if (!$tblCompanyTo){
                $message = new Danger('Bitte wählen Sie eine Institution');
                $error = true;
            }
        }
        $form = Relationship::useFrontend()->formRelationshipToCompany(
            $tblPerson->getId(),
            $tblToCompany ? $tblToCompany->getId() : null,
            false,
            $Search,
            $message
        );

        if (!($tblType = $this->getTypeById($Type['Type']))) {
            $form->setError('Type[Type]', 'Bitte geben Sie einen Typ an');
            $error = true;
        } else {
            $form->setSuccess('Type[Type]');
        }

        return $error ? $form : false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblCompany $tblCompany
     * @param $Type
     *
     * @return bool
     */
    public function createRelationshipToCompany(
        TblPerson $tblPerson,
        TblCompany $tblCompany,
        $Type
    ) {

        if (!($tblType = $this->getTypeById($Type['Type']))){
            return false;
        }

        if ((new Data($this->getBinding()))->addCompanyRelationshipToPerson($tblCompany, $tblPerson, $tblType,
            $Type['Remark'])
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param TblToCompany $tblToCompany
     * @param TblPerson $tblPerson
     * @param TblCompany $tblCompany
     * @param $Type
     *
     * @return bool
     */
    public function updateRelationshipToCompany(
        TblToCompany $tblToCompany,
        TblPerson $tblPerson,
        TblCompany $tblCompany,
        $Type
    ) {

        if (!($tblType = $this->getTypeById($Type['Type']))){
            return false;
        }

        // Remove current
        (new Data($this->getBinding()))->removeCompanyRelationshipToPerson($tblToCompany);
        // Add new
        if ((new Data($this->getBinding()))->addCompanyRelationshipToPerson($tblCompany, $tblPerson, $tblType,
            $Type['Remark'])
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblType
     */
    public function getTypeById($Id)
    {

        return (new Data($this->getBinding()))->getTypeById($Id);
    }

    /**
     * @param TblGroup|null $tblGroup
     *
     * @return bool|TblType[]
     */
    public function getTypeAllByGroup(TblGroup $tblGroup = null)
    {

        return (new Data($this->getBinding()))->getTypeAllByGroup($tblGroup);
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblGroup
     */
    public function getGroupById($Id)
    {

        return (new Data($this->getBinding()))->getGroupById($Id);
    }

    /**
     * @param string $Identifier
     *
     * @return bool|TblGroup
     */
    public function getGroupByIdentifier($Identifier)
    {

        return (new Data($this->getBinding()))->getGroupByIdentifier($Identifier);
    }

    /**
     * @return bool|TblType[]
     */
    public function getTypeAll()
    {

        return (new Data($this->getBinding()))->getTypeAll();
    }

    /**
     * @param $Name
     * @return false|TblType
     */
    public function getTypeByName($Name)
    {

        return (new Data($this->getBinding()))->getTypeByName($Name);
    }

    /**
     * @param TblToPerson $tblToPerson
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function removePersonRelationshipToPerson(TblToPerson $tblToPerson, $IsSoftRemove = false)
    {

        return (new Data($this->getBinding()))->removePersonRelationshipToPerson($tblToPerson, $IsSoftRemove);
    }

    /**
     * @param TblToCompany $tblToCompany
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function removeCompanyRelationshipToPerson(TblToCompany $tblToCompany, $IsSoftRemove = false)
    {

        return (new Data($this->getBinding()))->removeCompanyRelationshipToPerson($tblToCompany, $IsSoftRemove);
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblToPerson
     */
    public function getRelationshipToPersonById($Id)
    {

        return (new Data($this->getBinding()))->getRelationshipToPersonById($Id);
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblToCompany
     */
    public function getRelationshipToCompanyById($Id)
    {

        return (new Data($this->getBinding()))->getRelationshipToCompanyById($Id);
    }

    /**
     * @param TblCompany $tblCompany
     * @param TblPerson  $tblPerson
     * @param TblType    $tblType
     * @param string     $Remark
     *
     * @return TblToCompany
     */
    public function addCompanyRelationshipToPerson(
        TblCompany $tblCompany,
        TblPerson $tblPerson,
        TblType $tblType,
        $Remark = ''
    ) {
        return (new Data($this->getBinding()))->addCompanyRelationshipToPerson(
            $tblCompany, $tblPerson, $tblType, $Remark
        );
    }

    /**
     * @param TblPerson $tblPersonFrom
     * @param TblPerson $tblPersonTo
     * @param TblType $tblType
     * @param string $Remark
     *
     * @return bool
     */
    public function insertRelationshipToPerson(
        TblPerson $tblPersonFrom,
        TblPerson $tblPersonTo,
        TblType $tblType,
        $Remark
    ) {

        if ((new Data($this->getBinding()))->addPersonRelationshipToPerson($tblPersonFrom, $tblPersonTo, $tblType,
            $Remark)
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblSiblingRank
     */
    public function getSiblingRankById($Id)
    {

        return (new Data($this->getBinding()))->getSiblingRankById($Id);
    }

    /**
     * @return bool|TblSiblingRank[]
     */
    public function getSiblingRankAll()
    {

        return (new Data($this->getBinding()))->getSiblingRankAll();
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool $IsSoftRemove
     */
    public function removeRelationshipAllByPerson(TblPerson $tblPerson, $IsSoftRemove = false)
    {

        if (($tblRelationshipToPersonList = $this->getPersonRelationshipAllByPerson($tblPerson))){
            foreach($tblRelationshipToPersonList as $tblToPerson){
                $this->removePersonRelationshipToPerson($tblToPerson, $IsSoftRemove);
            }
        }
        if (($tblRelationshipToPersonList = $this->getCompanyRelationshipAllByPerson($tblPerson))){
            foreach($tblRelationshipToPersonList as $tblToPerson){
                $this->removeCompanyRelationshipToPerson($tblToPerson, $IsSoftRemove);
            }
        }
    }

    /**
     * @param TblToPerson $tblToPerson
     *
     * @return bool
     */
    public function restoreToPerson(TblToPerson $tblToPerson)
    {

        return (new Data($this->getBinding()))->restoreToPerson($tblToPerson);
    }

    /**
     * @param TblToCompany $tblToCompany
     *
     * @return bool
     */
    public function restoreToCompany(TblToCompany $tblToCompany)
    {

        return (new Data($this->getBinding()))->restoreToCompany($tblToCompany);
    }
}
