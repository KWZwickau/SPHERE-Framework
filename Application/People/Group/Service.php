<?php
namespace SPHERE\Application\People\Group;

use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\People\Group\Service\Data;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Group\Service\Entity\TblMember;
use SPHERE\Application\People\Group\Service\Setup;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer as ConsumerGatekeeper;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumerLogin;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Success as SuccessIcon;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Extension\Repository\Sorter;

/**
 * Class Service
 *
 * @package SPHERE\Application\People\Group
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
     * @return bool|TblMember[]
     */
    public function getMemberAll()
    {

        return (new Data($this->getBinding()))->getMemberAll();
    }

    /**
     * @param TblGroup $tblGroup
     * @param bool     $IsForced
     *
     * @return false|TblMember[]
     */
    public function getMemberAllByGroup(TblGroup $tblGroup, $IsForced = false)
    {

        return ( new Data($this->getBinding()) )->getMemberAllByGroup($tblGroup, ( $IsForced ? $IsForced : null ));
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblGroup  $tblGroup
     * @param bool      $IsForced
     *
     * @return false|TblMember
     */
    public function getMemberByPersonAndGroup(TblPerson $tblPerson, TblGroup $tblGroup, $IsForced = false)
    {

        return ( new Data($this->getBinding()) )->getMemberByPersonAndGroup($tblPerson, $tblGroup, ( $IsForced ? $IsForced : null ));
    }

    /**
     * @param bool $isCoreGroup
     *
     * @return false|TblGroup[]
     */
    public function getGroupListByIsCoreGroup($isCoreGroup = true)
    {

        return ( new Data($this->getBinding()) )->getGroupListByIsCoreGroup($isCoreGroup);
    }

    /**
     * Sortierung erst feste Gruppen, dann individuelle Gruppen
     *
     * @return bool|TblGroup[]
     */
    public function getGroupAllSorted()
    {

        $lockedList = array();
        $customList = array();
        $tblGroupAll = $this->getGroupAll();
        if ($tblGroupAll) {
            foreach ($tblGroupAll as $tblGroup) {
                if ($tblGroup->isLocked()) {
                    $lockedList[$tblGroup->getId()] = $tblGroup;
                } else {
                    $customList[$tblGroup->getId()] = $tblGroup;
                }
            }
        }

        $lockedList = $this->getSorter($lockedList)->sortObjectBy('Name', new Sorter\StringNaturalOrderSorter());
        $customList = $this->getSorter($customList)->sortObjectBy('Name', new Sorter\StringNaturalOrderSorter());

        return array_merge($lockedList, $customList);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblGroup[]
     */
    public function getGroupAllSortedByPerson(TblPerson $tblPerson)
    {
        $lockedList = array();
        $customList = array();
        $tblGroupAll = $this->getGroupAllByPerson($tblPerson);
        if ($tblGroupAll) {
            foreach ($tblGroupAll as $tblGroup) {
                if ($tblGroup->isLocked()) {
                    $lockedList[$tblGroup->getId()] = $tblGroup;
                } else {
                    $customList[$tblGroup->getId()] = $tblGroup;
                }
            }
        }

        $lockedList = $this->getSorter($lockedList)->sortObjectBy('Name', new Sorter\StringNaturalOrderSorter());
        $customList = $this->getSorter($customList)->sortObjectBy('Name', new Sorter\StringNaturalOrderSorter());

        return array_merge($lockedList, $customList);
    }

    /**
     * @return bool|TblGroup[]
     */
    public function getGroupAll()
    {

        return (new Data($this->getBinding()))->getGroupAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblGroup
     */
    public function getGroupById($Id)
    {

        return (new Data($this->getBinding()))->getGroupById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblMember
     */
    public function getMemberById($Id)
    {

        return (new Data($this->getBinding()))->getMemberById($Id);
    }

    /**
     * @param IFormInterface $Form
     * @param array          $Group
     *
     * @return IFormInterface|string
     */
    public function createGroup(IFormInterface $Form = null, $Group)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Group) {
            return $Form;
        }

        $Error = false;

        if (isset( $Group['Name'] ) && empty( $Group['Name'] )) {
            $Form->setError('Group[Name]', 'Bitte geben Sie einen Namen für die Gruppe an');
            $Error = true;
        } else {
            if ($this->getGroupByName($Group['Name'])) {
                $Form->setError('Group[Name]', 'Bitte geben Sie einen eineindeutigen Namen für die Gruppe an');
                $Error = true;
            }
            // ist ein UCS Mandant?
            $IsUCSMandant = false;
            if(($tblConsumer = ConsumerGatekeeper::useService()->getConsumerBySession())){
                if(ConsumerGatekeeper::useService()->getConsumerLoginByConsumerAndSystem($tblConsumer, TblConsumerLogin::VALUE_SYSTEM_UCS)){
                    $IsUCSMandant = true;
                }
            }
            // Gruppen Zeicheneingrenzung nur für UCS Mandanten
            if (isset($Group['Name']) && $Group['Name'] != '' && $IsUCSMandant) {
                if(!preg_match('!^[\w]+[\w -_]*[\w]+$!', $Group['Name'])){ // muss mit Buchstaben/Zahl anfangen und Aufhören + mindestens 2 Zeichen
                    $Form->setError('Group[Name]', 'Erlaubte Zeichen [a-zA-Z0-9 -_]');
                    $Error = true;
                }
            }
        }

        if (!$Error) {
//            $isCoreGroup = false;
//            if(isset($Group['IsCoreGroup'])){
//                $isCoreGroup = true;
//            }
            if ((new Data($this->getBinding()))
                ->createGroup($Group['Name'], $Group['Description'], $Group['Remark'], false, '')) {
                return new Success(new SuccessIcon().' Die Gruppe wurde erfolgreich erstellt').new Redirect('/People/Group',
                    Redirect::TIMEOUT_SUCCESS);
            } else {
                return new Danger(new Ban().' Die Gruppe konnte nicht erstellt werden').new Redirect('/People/Group',
                    Redirect::TIMEOUT_ERROR);
            }
        }

        return $Form;
    }

    /**
     * @param string $Name
     *
     * @return bool|TblGroup
     */
    public function getGroupByName($Name)
    {

        return (new Data($this->getBinding()))->getGroupByName($Name);
    }

    /**
     * @param string $Name
     * @param string $Description
     *
     * @return bool|TblGroup
     */
    public function createGroupFromImport($Name, $Description = '')
    {

        if (!($tblGroup = $this->getGroupByName($Name))) {
            return (new Data($this->getBinding()))->createGroup($Name, $Description, '');
        } else {
            return $tblGroup;
        }
    }

    /**
     * @param string $MetaTable
     *
     * @return bool|TblGroup
     */
    public function getGroupByMetaTable($MetaTable)
    {

        return (new Data($this->getBinding()))->getGroupByMetaTable($MetaTable);
    }

    /**
     * @return bool|TblGroup[]
     */
    public function getGroupByNotLocked()
    {

        return (new Data($this->getBinding()))->getGroupByNotLocked();
    }

    /**
     * @param IFormInterface $Form
     * @param TblGroup       $tblGroup
     * @param array          $Group
     *
     * @return IFormInterface|string
     */
    public function updateGroup(IFormInterface $Form = null, TblGroup $tblGroup, $Group)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Group) {
            return $Form;
        }

        $Error = false;

        if (isset( $Group['Name'] ) && empty( $Group['Name'] )) {
            $Form->setError('Group[Name]', 'Bitte geben Sie einen Namen für die Gruppe an');
            $Error = true;
        } else {
            $tblGroupTwin = $this->getGroupByName($Group['Name']);
            if ($tblGroupTwin && $tblGroupTwin->getId() != $tblGroup->getId()) {
                $Form->setError('Group[Name]', 'Bitte geben Sie einen eineindeutigen Namen für die Gruppe an');
                $Error = true;
            }

            // Stammgruppen werden jetzt als Kurse in der Bildung gepflegt und nicht mehr als Personengruppen
//            // ist ein UCS Mandant?
//            $IsUCSMandant = false;
//            if(($tblConsumer = ConsumerGatekeeper::useService()->getConsumerBySession())){
//                if(ConsumerGatekeeper::useService()->getConsumerLoginByConsumerAndSystem($tblConsumer, TblConsumerLogin::VALUE_SYSTEM_UCS)){
//                    $IsUCSMandant = true;
//                }
//            }
//            // Gruppen Zeicheneingrenzung nur für UCS Mandanten
//            if (isset($Group['Name']) && $Group['Name'] != '' && $IsUCSMandant) {
//                if(!preg_match('!^[\w]+[\w -_]*[\w]+$!', $Group['Name'])){ // muss mit Buchstaben/Zahl anfangen und Aufhören + mindestens 2 Zeichen
//                    $Form->setError('Group[Name]', 'Erlaubte Zeichen [a-zA-Z0-9 -_]');
//                    $Error = true;
//                }
//            }
        }

        if (!$Error) {
            if ((new Data($this->getBinding()))->updateGroup(
                $tblGroup, $Group['Name'], $Group['Description'], $Group['Remark']
            )
            ) {
                return new Success(new SuccessIcon().' Die Änderungen wurden erfolgreich gespeichert')
                .new Redirect('/People/Group', Redirect::TIMEOUT_SUCCESS);
            } else {
                return new Danger(new Ban().' Die Änderungen konnte nicht gespeichert werden')
                .new Redirect('/People/Group', Redirect::TIMEOUT_ERROR);
            }
        }

        return $Form;
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return bool|TblPerson[]
     */
    public function getPersonAllByGroup(TblGroup $tblGroup)
    {

        return (new Data($this->getBinding()))->getPersonAllByGroup($tblGroup);
    }

    /**
     * @return bool|TblPerson[]
     */
    public function getPersonAllHavingNoGroup()
    {

        return (new Data($this->getBinding()))->getPersonAllHavingNoGroup();
    }

    /**
     * @deprecated countPersonAllByGroup -> countMemberAllByGroup
     *
     * @param TblGroup $tblGroup
     *
     * @return int
     */
    public function countPersonAllByGroup(TblGroup $tblGroup)
    {

        return $this->countMemberAllByGroup($tblGroup);
    }

    /**
     * @deprecated use countMemberByGroup
     * @param TblGroup $tblGroup
     *
     * @return int
     */
    public function countMemberAllByGroup(TblGroup $tblGroup)
    {

        return $this->countEntityList((new Data($this->getBinding()))->getMemberAllByGroup($tblGroup));
    }

    /**
     * @param TblGroup $tblGroup
     * @return int
     */
    public function countMemberByGroup(TblGroup $tblGroup)
    {
        return (new Data($this->getBinding()))->countMemberByGroup($tblGroup);
    }

    /**
     *
     * @param TblPerson $tblPerson
     * @param bool $isForced
     *
     * @return bool|TblGroup[]
     */
    public function getGroupAllByPerson(TblPerson $tblPerson, $isForced = false)
    {

        return (new Data($this->getBinding()))->getGroupAllByPerson($tblPerson, $isForced);
    }

    /**
     *
     * @param TblPerson $tblPerson
     * @param bool $isForced
     *
     * @return bool|TblMember[]
     */
    public function getMemberAllByPerson(TblPerson $tblPerson, $isForced = false)
    {

        return (new Data($this->getBinding()))->getMemberAllByPerson($tblPerson, $isForced);
    }

    /**
     * @param TblGroup $tblGroup
     * @param TblPerson $tblPerson
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function removeGroupPerson(TblGroup $tblGroup, TblPerson $tblPerson, $IsSoftRemove = false)
    {

        return (new Data($this->getBinding()))->removeGroupPerson($tblGroup, $tblPerson, $IsSoftRemove);
    }

    /**
     * @param TblGroup $tblGroup
     * @param TblPerson $tblPerson
     *
     * @return TblMember
     */
    public function addGroupPerson(TblGroup $tblGroup, TblPerson $tblPerson)
    {

        // automatic identifier for Student
        if($tblGroup->getMetaTable() == TblGroup::META_TABLE_STUDENT){
            // control settings
            $tblSetting = Consumer::useService()->getSetting('People', 'Meta', 'Student', 'Automatic_StudentNumber');
            if($tblSetting && $tblSetting->getValue()) {
                $MaxIdentifier = Student::useService()->getStudentMaxIdentifier();
                $this->setAutoStudentNumber($tblPerson, $MaxIdentifier);
            }
        }
        return (new Data($this->getBinding()))->addGroupPerson($tblGroup, $tblPerson);
    }

    /**
     * @param TblGroup $tblGroup
     * @param TblPerson[] $tblPersonList
     *
     * @return bool
     */
    public function addGroupPersonList(TblGroup $tblGroup, $tblPersonList)
    {

        $result = (new Data($this->getBinding()))->addGroupPersonList($tblGroup, $tblPersonList);
        if($tblGroup->getMetaTable() == TblGroup::META_TABLE_STUDENT){
            // control settings
            $tblSetting = Consumer::useService()->getSetting('People', 'Meta', 'Student', 'Automatic_StudentNumber');
            if($tblSetting && $tblSetting->getValue()) {
                $MaxIdentifier = Student::useService()->getStudentMaxIdentifier();
                foreach($tblPersonList as $tblPerson){
                    $MaxIdentifier = $this->setAutoStudentNumber($tblPerson, $MaxIdentifier);
                }
            }
        }
        return $result;
    }

    /**
     * @param TblPerson $tblPerson
     * @param int       $MaxIdentifier
     *
     * @return int
     */
    public function setAutoStudentNumber(TblPerson $tblPerson, $MaxIdentifier = 0)
    {

        $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
        if($tblStudent){
            if($tblStudent->getIdentifier() == ''){
                $MaxIdentifier++;
                Student::useService()->updateStudentIdentifier($tblStudent, $MaxIdentifier);
            }
        } else {
            $MaxIdentifier++;
            $Prefix = '';
            Student::useService()->createStudent($tblPerson, $Prefix, $MaxIdentifier);
        }

        return $MaxIdentifier;
    }

    /**
     * @param TblMember $tblMember
     *
     * @return bool
     */
    public function removeMember(TblMember $tblMember)
    {

        return (new Data($this->getBinding()))->removeMember($tblMember);
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return bool
     */
    public function destroyGroup(TblGroup $tblGroup)
    {

        $tblMemberList = Group::useService()->getMemberAllByGroup($tblGroup, true);
        if ($tblMemberList) {
            foreach ($tblMemberList as $tblMember) {
                Group::useService()->removeMember($tblMember);
            }
        }

        return (new Data($this->getBinding()))->destroyGroup($tblGroup);
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return array TblPerson->Id
     */
    public function fetchIdPersonAllByGroup(TblGroup $tblGroup)
    {

        return (new Data($this->getBinding()))->fetchIdPersonAllByGroup($tblGroup);
    }

    /**
     * @param TblGroup $tblGroup
     * @param TblPerson $tblPerson
     *
     * @return bool|TblMember
     */
    public function existsGroupPerson(TblGroup $tblGroup, TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->existsGroupPerson($tblGroup, $tblPerson);
    }

    /**
     * @param        $Name
     * @param string $Description
     * @param string $Remark
     *
     * @return TblGroup
     */
    public function insertGroup($Name, $Description = '', $Remark = '')
    {

        return (new Data($this->getBinding()))->createGroup(
            $Name, $Description, $Remark
        );
    }

    /**
     * @param IFormInterface   $Form
     * @param TblGroup         $tblGroup
     * @param null             $DataAddPerson
     * @param null             $DataRemovePerson
     * @param TblGroup|null    $tblFilterGroup
     * @param TblDivisionCourse|null $tblFilterDivisionCourse
     *
     * @return IFormInterface|string
     */
    public function addPersonsToGroup(
        IFormInterface $Form,
        TblGroup $tblGroup,
        $DataAddPerson = null,
        $DataRemovePerson = null,
        TblGroup $tblFilterGroup = null,
        TblDivisionCourse $tblFilterDivisionCourse = null
    ) {
        /**
         * Skip to Frontend
         */
        if ($DataAddPerson === null && $DataRemovePerson === null) {
            return $Form;
        }

        // entfernen
        if ($DataRemovePerson !== null) {
            $this->removePersonListFromGroup($tblGroup, $DataRemovePerson);
        }

        // hinzufügen
        if ($DataAddPerson !== null) {
            $this->addPersonListToGroup($tblGroup, $DataAddPerson);
        }

        return new Success('Daten erfolgreich gespeichert', new SuccessIcon())
        .new Redirect('/People/Group/Person/Add', Redirect::TIMEOUT_SUCCESS, array(
            'Id'               => $tblGroup->getId(),
            'FilterGroupId'    => $tblFilterGroup ? $tblFilterGroup->getId() : null,
            'FilterDivisionCourseId' => $tblFilterDivisionCourse ? $tblFilterDivisionCourse->getId() : null,
        ));
    }

    /**
     * @param TblGroup $tblGroup
     * @param          $DataRemovePerson
     */
    private function removePersonListFromGroup(TblGroup $tblGroup, $DataRemovePerson)
    {

        foreach ($DataRemovePerson as $personId => $value) {
            $tblPerson = Person::useService()->getPersonById($personId);
            if ($tblPerson) {
                $this->removeGroupPerson($tblGroup, $tblPerson);
            }
        }
    }

    /**
     * @param TblGroup $tblGroup
     * @param          $DataAddPerson
     */
    private function addPersonListToGroup(TblGroup $tblGroup, $DataAddPerson)
    {

        $tblPersonList = array();
        foreach ($DataAddPerson as $personId => $value) {
            $tblPersonList[] = Person::useService()->getPersonById($personId);
        }
        if(!empty($tblPersonList)){
            $this->addGroupPersonList($tblGroup, $tblPersonList);
        }
    }

    /**
     * @param IFormInterface $Form
     * @param TblGroup       $tblGroup
     * @param null           $Filter
     *
     * @return IFormInterface|string
     */
    public function getFilter(IFormInterface $Form, TblGroup $tblGroup, $Filter = null)
    {
        /**
         * Skip to Frontend
         */
        if ($Filter === null) {
            return $Form;
        }

        $tblFilterGroup = false;
        $tblDivisionCourse = false;
        if (isset( $Filter['Group'] )) {
            $tblFilterGroup = $this->getGroupById($Filter['Group']);
        }
        if (isset( $Filter['Division'] )) {
            $tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($Filter['Division']);
        }

        return new Success('Die verfügbaren Personen werden gefiltert.',
            new SuccessIcon())
        .new Redirect('/People/Group/Person/Add', Redirect::TIMEOUT_SUCCESS, array(
            'Id'               => $tblGroup->getId(),
            'FilterGroupId'    => $tblFilterGroup ? $tblFilterGroup->getId() : null,
            'FilterDivisionCourseId' => $tblDivisionCourse ? $tblDivisionCourse->getId() : null,
        ));
    }

    /**
     * @param TblPerson $tblPerson
     * @param $IsSoftRemove
     */
    public function removeMemberAllByPerson(TblPerson $tblPerson, $IsSoftRemove)
    {

        if (($tblGroupList = $this->getGroupAllByPerson($tblPerson))) {
            foreach ($tblGroupList as $tblGroup) {
                $this->removeGroupPerson($tblGroup, $tblPerson, $IsSoftRemove);
            }
        }
    }

    /**
     * @param TblMember $tblMember
     *
     * @return bool
     */
    public function restoreMember(TblMember $tblMember)
    {

        return (new Data($this->getBinding()))->restoreMember($tblMember);
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return TblGroup[]|false
     */
    public function getTudorGroupAll(TblPerson $tblPerson = null)
    {
        $list = $this->getGroupListByIsCoreGroup(true);
        if ($tblPerson
            && $list
        ) {
            $tudorGroupList = array();
            foreach ($list as $tblGroup) {
                if ($this->existsGroupPerson($tblGroup, $tblPerson)) {
                    $tudorGroupList[] = $tblGroup;
                }
            }

            return empty($tudorGroupList) ? false : $tudorGroupList;
        } else {
            return $list;
        }
    }

    /**
     * @param string $Name
     *
     * @return false|TblGroup[]
     */
    public function getGroupListLike($Name)
    {
        return (new Data($this->getBinding()))->getGroupListLike($Name);
    }
}
