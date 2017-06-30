<?php
namespace SPHERE\Application\Setting\User\Account;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson as TblToPersonAddress;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account as AccountPlatform;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Setting\User\Account\Service\Data;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\Application\Setting\User\Account\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
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
     * @param $IsExport
     *
     * @return false|TblUserAccount[]
     */
    public function getUserAccountByIsExport($IsExport)
    {

        return (new Data($this->getBinding()))->getUserAccountByIsExport($IsExport);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblUserAccount
     */
    public function getUserAccountByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getUserAccountByPerson($tblPerson);
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return false|TblUserAccount
     */
    public function getUserAccountByAccount(TblAccount $tblAccount)
    {

        return (new Data($this->getBinding()))->getUserAccountByAccount($tblAccount);
    }

    /**
     * @return bool|TblUserAccount[]
     */
    public function getUserAccountAll()
    {

        return ( new Data($this->getBinding()) )->getUserAccountAll();
    }

    /**
     * @param string $type
     *
     * @return bool|TblUserAccount[]
     */
    public function getUserAccountAllByType($type)
    {

        return (new Data($this->getBinding()))->getUserAccountAllByType($type);
    }

    /**
     * @param string $type
     *
     * @return bool|TblUserAccount[]
     */
    public function countUserAccountAllByType($type)
    {

        return (new Data($this->getBinding()))->countUserAccountAllByType($type);
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
     * @param IFormInterface $form
     * @param array          $PersonIdArray
     * @param string         $AccountType S = Student, E = Custody
     *
     * @return IFormInterface|Layout
     */
    public function createAccount(IFormInterface $form, $PersonIdArray = array(), $AccountType = 'S')
    {

        $Global = $this->getGlobal();
        if (!isset($Global->POST['Button']['Submit'])) {
            return $form;
        } elseif ($Global->POST['Button']['Submit'] != 'Speichern') {
            return $form;
        }

        if (empty($PersonIdArray)) {
            return $form;
        }

        $IsMissingAddress = false;
        $TimeStamp = new \DateTime('now');

        foreach ($PersonIdArray as $PersonId) {
            $tblPerson = Person::useService()->getPersonById($PersonId);
            if ($tblPerson) {
                // ignore Person with Account
                if (AccountPlatform::useService()->getAccountAllByPerson($tblPerson)) {
                    continue;
                }
                // ignore Person without Main Address
                $tblAddressToPerson = Address::useService()->getAddressToPersonByPerson($tblPerson);
                if (!$tblAddressToPerson) {
                    $IsMissingAddress = true;
                    continue;
                }
                // ignore without Consumer
                $tblConsumer = Consumer::useService()->getConsumerBySession();
                if ($tblConsumer == '') {
                    continue;
                }
                $name = $this->generateUserName($tblPerson, $tblConsumer, $AccountType);
                $password = $this->generatePassword(8, 1, 2, 1);

                $tblAccount = AccountPlatform::useService()->insertAccount($name, $password, null, $tblConsumer);

                if ($tblAccount) {
                    $tblIdentification = AccountPlatform::useService()->getIdentificationByName('UserCredential');
                    AccountPlatform::useService()->addAccountAuthentication($tblAccount,
                        $tblIdentification);
                    $tblRole = Access::useService()->getRoleByName('Einstellungen: Benutzer');
                    if ($tblRole && !$tblRole->isSecure()) {
                        AccountPlatform::useService()->addAccountAuthorization($tblAccount, $tblRole);
                    }
                    $tblRole = Access::useService()->getRoleByName('Bildung: Zensurenübersicht (Schüler/Eltern)');
                    if ($tblRole && !$tblRole->isSecure()) {
                        AccountPlatform::useService()->addAccountAuthorization($tblAccount, $tblRole);
                    }
                    if ($tblPerson) {
                        AccountPlatform::useService()->addAccountPerson($tblAccount, $tblPerson);
                    }


                    if ($AccountType == 'S') {
                        $type = TblUserAccount::VALUE_TYPE_STUDENT;
                    } elseif ($AccountType == 'E') {
                        $type = TblUserAccount::VALUE_TYPE_CUSTODY;
                    } else {
                        // default setting
                        $type = TblUserAccount::VALUE_TYPE_STUDENT;
                    }
                    // add tblUserAccount
                    $this->createUserAccount($tblAccount, $tblPerson, $tblAddressToPerson, $TimeStamp, $password,
                        $type);
                }
            }
        }
        return new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        ($IsMissingAddress
                            ? new Warning('Personen ohne Hauptadresse ignoriert (Accounts zu Personen mit Gültiger Hauptadresse wurden erfolgreich angelegt).')
                            : new Success('Accounts wurden erfolgreich angelegt.')
                        )
                    ),
                    new LayoutColumn(
                        ($AccountType == 'S'
                            ? new Redirect('/Setting/User/Account/Student/Add',
                                ($IsMissingAddress
                                    ? Redirect::TIMEOUT_ERROR
                                    : Redirect::TIMEOUT_SUCCESS
                                ))
                            : ($AccountType == 'C'
                                ? new Redirect('/Setting/User/Account/Custody/Add',
                                    ($IsMissingAddress
                                        ? Redirect::TIMEOUT_ERROR
                                        : Redirect::TIMEOUT_SUCCESS
                                    ))
                                : ''
                            )
                        )
                    )
                ))
            )
        );
    }

    /**
     * @param int $completeLength number all filled up with (abcdefghjkmnpqrstuvwxyz)
     * @param int $specialLength number of (!$%&=?*-:;.,+_)
     * @param int $numberLength number of (123456789)
     * @param int $capitalLetter number of (ABCDEFGHJKMNPQRSTUVWXYZ)
     *
     * @return string
     */
    private function generatePassword($completeLength = 8, $specialLength = 0, $numberLength = 0, $capitalLetter = 0)
    {

        $numberChars = '123456789';
        $specialChars = '!$%&=?*-:;.,+_';
        $secureChars = 'abcdefghjkmnpqrstuvwxyz';
        $secureCapitalChars = strtoupper($secureChars);
        $return = '';

        $count = $completeLength - $specialLength - $numberLength - $capitalLetter;
        if ($count > 0) {
            // get normal characters
            $temp = str_shuffle($secureChars);
            $return = substr($temp, 0, $count);
        }
        if ($capitalLetter > 0) {
            // get special characters
            $temp = str_shuffle($secureCapitalChars);
            $return .= substr($temp, 0, $capitalLetter);
        }
        if ($specialLength > 0) {
            // get special characters
            $temp = str_shuffle($specialChars);
            $return .= substr($temp, 0, $specialLength);
        }
        if ($numberLength > 0) {
            // get numbers
            $temp = str_shuffle($numberChars);
            $return .= substr($temp, 0, $numberLength);
        }
        // Random
        $return = str_shuffle($return);

        return $return;
    }

    /**
     * @param TblAccount              $tblAccount
     * @param TblPerson               $tblPerson
     * @param TblToPersonAddress|null $tblToPersonAddress
     * @param \DateTime               $TimeStamp
     * @param string                  $userPassword
     * @param string                  $type STUDENT|CUSTODY
     *
     * @return false|TblUserAccount
     */
    public function createUserAccount(
        TblAccount $tblAccount,
        TblPerson $tblPerson,
        TblToPersonAddress $tblToPersonAddress,
        \DateTime $TimeStamp,
        $userPassword,
        $type
    ) {

        return ( new Data($this->getBinding()) )->createUserAccount(
            $tblAccount,
            $tblPerson,
            $tblToPersonAddress,
            $TimeStamp,
            $userPassword,
            $type);
    }

    /**
     * @param TblPerson|null   $tblPerson
     * @param TblConsumer|null $tblConsumer
     * @param string           $AccountType
     *
     * @return string
     */
    public function generateUserName(TblPerson $tblPerson = null, TblConsumer $tblConsumer = null, $AccountType = 'S')
    {
        $UserName = '';

        $StudentNumber = rand(1000, 9999);
        // search StudentIdentifier
        $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
        if ($tblStudent) {
            if (($Number = $tblStudent->getIdentifier())) {
                $StudentNumber = $Number;
            }
        }

        if ($tblConsumer) {
            $lengthCount = 8;
            $FirstName = $tblPerson->getFirstName();
            $LastName = $tblPerson->getLastName();
            $lengthFirstName = strlen($FirstName);
            $lengthLastName = strlen($LastName);
            $length = $lengthFirstName + $lengthLastName;

            if ($lengthFirstName <= ($lengthCount / 2)) {         // full FirstName if short (prefer)
                $lengthLastName = $lengthCount - $lengthFirstName;
            } elseif ($lengthLastName <= ($lengthCount / 2)) {   // full LastName if short
                $lengthFirstName = $lengthCount - $lengthLastName;
            } else {
                $modifier = $lengthCount / $length;          // get Number be Ration
                $lengthFirstName = round($modifier * $lengthFirstName);
                $lengthLastName = round($modifier * $lengthLastName);

                // correct round error ([round 3.5] => 4 + [round 4.5] => 5)
                if (($lengthFirstName + $lengthLastName) > $lengthCount) {
                    $lengthLastName = $lengthLastName - 1;
                }
            }

            // cut string with UTF8 encoding
            mb_internal_encoding("UTF-8");
            $UserName = $tblConsumer->getAcronym().'-'.$AccountType.'-'.
                mb_substr($FirstName, 0, $lengthFirstName).mb_substr($LastName, 0, $lengthLastName);
        }

        $UserNamePrepare = $UserName.$StudentNumber;

//        $tblAccount = true;

        // find existing UserName?
        $tblAccount = AccountPlatform::useService()->getAccountByUsername($UserNamePrepare);
        if ($tblAccount) {
            while ($tblAccount) {
                $randNumber = rand(1000, 9999);
                $UserNameMod = $UserName.$randNumber;
                $tblAccount = AccountPlatform::useService()->getAccountByUsername($UserNameMod);
                if (!$tblAccount) {
                    $UserName = $UserNameMod;
                }

            }
        } else {
            $UserName = $UserNamePrepare;
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
     *
     * @return bool
     */
    public function removeUserAccount(TblUserAccount $tblUserAccount)
    {

        return ( new Data($this->getBinding()) )->removeUserAccount($tblUserAccount);
    }
}