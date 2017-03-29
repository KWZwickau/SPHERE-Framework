<?php
namespace SPHERE\Application\Setting\User\Account;

use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson as TblToPersonAddress;
use SPHERE\Application\Contact\Mail\Service\Entity\TblToPerson as TblToPersonMail;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account as AccountPlatform;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Setting\User\Account\Service\Data;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\Application\Setting\User\Account\Service\Setup;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Filter\Link\Pile;

class Service extends AbstractService
{
    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {
        $Protocol = ( new Setup($this->getStructure()) )->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            ( new Data($this->getBinding()) )->setupDatabaseContent();
        }

        return $Protocol;
    }

    /**
     * @param $Id
     *
     * @return bool|TblUserAccount
     */
    public function getUserAccountById($Id)
    {

        return ( new Data($this->getBinding()) )->getUserAccountById($Id);
    }

    /**
     * @param $IsSend
     * @param $IsExport
     *
     * @return false|TblUserAccount[]
     */
    public function getUserAccountByIsSendAndIsExport($IsSend, $IsExport)
    {

        return ( new Data($this->getBinding()) )->getUserAccountByIsSendAndIsExport($IsSend, $IsExport);
    }

    /**
     * @return bool|TblUserAccount[]
     */
    public function getUserAccountAll()
    {

        return ( new Data($this->getBinding()) )->getUserAccountAll();
    }

    /**
     * @param null $FilterGroup
     * @param null $FilterStudent
     * @param null $FilterYear
     * @param bool $IsTimeout (if search reach timeout)
     *
     * @return array|bool
     */
    public function getStudentFilterResultList(
        $FilterGroup = null,
        $FilterStudent = null,
//        $FilterPerson = null,
        $FilterYear = null,
        &$IsTimeout = false
    ) {

        // use every time Group "STUDENT"
        $FilterGroup['TblGroup_Id'] = Group::useService()->getGroupByMetaTable('STUDENT')->getId();

        // Database Join with foreign Key
        $Pile = new Pile(Pile::JOIN_TYPE_OUTER);
        $Pile->addPile(( new ViewPeopleGroupMember() )->getViewService(), new ViewPeopleGroupMember(),
            null, ViewPeopleGroupMember::TBL_MEMBER_SERVICE_TBL_PERSON
        );
        $Pile->addPile(( new ViewPerson() )->getViewService(), new ViewPerson(),
            ViewPerson::TBL_PERSON_ID, ViewPerson::TBL_PERSON_ID
        );
        $Pile->addPile(( new ViewDivisionStudent() )->getViewService(), new ViewDivisionStudent(),
            ViewDivisionStudent::TBL_DIVISION_STUDENT_SERVICE_TBL_PERSON, ViewDivisionStudent::TBL_DIVISION_TBL_YEAR
        );
        $Pile->addPile(( new ViewYear() )->getViewService(), new ViewYear(),
            ViewYear::TBL_YEAR_ID, ViewYear::TBL_YEAR_ID
        );

        if ($FilterGroup) {
            // Preparation FilterGroup
            array_walk($FilterGroup, function (&$Input) {

                if (!is_array($Input)) {
                    if (!empty($Input)) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                }
            });
            $FilterGroup = array_filter($FilterGroup);
        } else {
            $FilterGroup = array();
        }
        // Preparation FilterPerson
//        if ($FilterPerson) {
//            // Preparation FilterPerson
//            array_walk($FilterPerson, function (&$Input) {
//
//                if (!is_array($Input)) {
//                    if (!empty($Input)) {
//                        $Input = explode(' ', $Input);
//                        $Input = array_filter($Input);
//                    } else {
//                        $Input = false;
//                    }
//                }
//            });
//            $FilterPerson = array_filter($FilterPerson);
//        } else {
            $FilterPerson = array();
//        }

        // Preparation $FilterStudent
        if (isset($FilterStudent)) {
            array_walk($FilterStudent, function (&$Input) {
                if (!is_array($Input)) {
                    if (!empty($Input)) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                }
            });
            $FilterStudent = array_filter($FilterStudent);
        } else {
            $FilterStudent = array();
        }
        // Preparation $FilterYear
        if (isset($FilterYear)) {
            array_walk($FilterYear, function (&$Input) {
                if (!is_array($Input)) {
                    if (!empty($Input)) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                }
            });
            $FilterYear = array_filter($FilterYear);
        } else {
            $FilterYear = array();
        }

        $Result = $Pile->searchPile(array(
            0 => $FilterGroup,
            1 => $FilterPerson,
            2 => $FilterStudent,
            3 => $FilterYear
        ));
        // get Timeout status
        $IsTimeout = $Pile->isTimeout();

        return ( !empty($Result) ? $Result : false );
    }

    /**
     * @param null $FilterGroup
     * @param null $FilterPerson
     * @param bool $IsTimeout (if search reach timeout)
     *
     * @return array|bool
     */
    public function getPersonFilterResultList(
        $FilterGroup = null,
        $FilterPerson = null,
        &$IsTimeout = false
    ) {

        // Database Join with foreign Key
        $Pile = new Pile(Pile::JOIN_TYPE_OUTER);
        $Pile->addPile((new ViewPeopleGroupMember())->getViewService(), new ViewPeopleGroupMember(),
            null, ViewPeopleGroupMember::TBL_MEMBER_SERVICE_TBL_PERSON
        );
        $Pile->addPile((new ViewPerson())->getViewService(), new ViewPerson(),
            ViewPerson::TBL_PERSON_ID, ViewPerson::TBL_PERSON_ID
        );

        if ($FilterGroup) {
            // Preparation FilterGroup
            array_walk($FilterGroup, function (&$Input) {

                if (!is_array($Input)) {
                    if (!empty($Input)) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                }
            });
            $FilterGroup = array_filter($FilterGroup);
        } else {
            $FilterGroup = array();
        }
        // Preparation FilterPerson
        if ($FilterPerson) {
            array_walk($FilterPerson, function (&$Input) {

                if (!is_array($Input)) {
                    if (!empty($Input)) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                }
            });
            $FilterPerson = array_filter($FilterPerson);
        } else {
            $FilterPerson = array();
        }

        $Result = $Pile->searchPile(array(
            0 => $FilterGroup,
            1 => $FilterPerson
        ));
        // get Timeout status
        $IsTimeout = $Pile->isTimeout();

        return (!empty($Result) ? $Result : false);
    }

    /**
     * @param TblAccount              $tblAccount
     * @param TblPerson               $tblPerson
     * @param TblToPersonAddress|null $tblToPersonAddress
     * @param TblToPersonMail|null    $tblToPersonMail
     * @param string                  $UserPassword
     *
     * @return false|TblUserAccount
     */
    public function createUserAccount(
        TblAccount $tblAccount,
        TblPerson $tblPerson,
        TblToPersonAddress $tblToPersonAddress = null,
        TblToPersonMail $tblToPersonMail = null,
        $UserPassword
    ) {

        return ( new Data($this->getBinding()) )->createUserAccount(
            $tblAccount,
            $tblPerson,
            $tblToPersonAddress,
            $tblToPersonMail,
            $UserPassword);
    }

    /**
     * @param TblPerson|null       $tblPerson
     * @param TblToPersonMail|null $tblToPersonMail
     *
     * @return string
     */
    public function generateUserName(TblPerson $tblPerson = null, TblToPersonMail $tblToPersonMail = null)
    {
        $UserName = '';
        //use E-Mail if exist
        if ($tblToPersonMail != null) {
            if (($tblMail = $tblToPersonMail->getTblMail())) {
                $UserName = $tblToPersonMail->getTblMail()->getAddress();
            }
        }
        //try to force a AccountName    //ToDO need decision how Accountsname have to look like!
        if ($UserName == '' && $tblPerson) {
            $tblConsumer = Consumer::useService()->getConsumerBySession();
            if ($tblConsumer) {
                $UserName = $tblConsumer->getAcronym().'-'.$tblPerson->getLastName().$tblPerson->getFirstName();
            }
        }

        // find existing UserName?
        $tblAccount = AccountPlatform::useService()->getAccountByUsername($UserName);
        if ($tblAccount) {
            $Mod = 1;
            while ($tblAccount) {
                $UserNameMod = $UserName.$Mod;
                $Mod++;
                $tblAccount = AccountPlatform::useService()->getAccountByUsername($UserNameMod);
                if (!$tblAccount) {
                    $UserName = $UserNameMod;
                }
            }
        }

        return $UserName;
    }

    /**
     * @param TblUserAccount     $tblUserAccount
     * @param TblToPersonAddress $tblToPersonAddress
     *
     * @return bool
     */
    public function updateUserAccountByToPersonAddress(TblUserAccount $tblUserAccount, TblToPersonAddress $tblToPersonAddress)
    {

        return ( new Data($this->getBinding()) )->updateUserAccountByToPersonAddress($tblUserAccount, $tblToPersonAddress);
    }

    /**
     * @param TblUserAccount  $tblUserAccount
     * @param TblToPersonMail $tblToPersonMail
     *
     * @return bool
     */
    public function updateUserAccountByToPersonMail(TblUserAccount $tblUserAccount, TblToPersonMail $tblToPersonMail)
    {

//        $tblPerson = $tblToPersonMail->getServiceTblPerson();
//        if ($tblPerson) {
//            $UserName = Account::useService()->generateUserName($tblPerson, $tblToPersonMail);
//            Account::useService()->updateUserAccountByUserName($tblUserAccount, $UserName); //ToDO update UserName from TblAccount or not?
//        }
        return ( new Data($this->getBinding()) )->updateUserAccountByToPersonMail($tblUserAccount, $tblToPersonMail);
    }

    /**
     * @param TblUserAccount $tblUserAccount
     * @param bool           $IsExport
     *
     * @return bool
     */
    public function updateUserAccountByIsExport(TblUserAccount $tblUserAccount, $IsExport)
    {

        return (new Data($this->getBinding()))->updateUserAccountByIsExport($tblUserAccount, $IsExport);
    }

    /**
     * @param TblUserAccount $tblUserAccount
     * @param bool           $IsExport
     *
     * @return bool
     */
    public function updateUserAccountByIsSend(TblUserAccount $tblUserAccount, $IsExport)
    {

        return (new Data($this->getBinding()))->updateUserAccountByIsSend($tblUserAccount, $IsExport);
    }

    /**
     * @param TblUserAccount $tblUserAccount
     *
     * @return bool
     */
    public function removeUserAccount(TblUserAccount $tblUserAccount)
    {

        return ( new Data($this->getBinding()) )->removeUserAccount($tblUserAccount);
    }
}