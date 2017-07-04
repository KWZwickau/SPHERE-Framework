<?php
namespace SPHERE\Application\Setting\User\Account;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Exception\Repository\TypeFileException;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Component\Document\Exception\DocumentTypeException;
use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson as TblToPersonAddress;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember;
use SPHERE\Application\People\Meta\Common\Common;
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
     * @param \DateTime $dateTime
     *
     * @return false|TblUserAccount[]
     */
    public function getUserAccountByTimeGroup(\DateTime $dateTime)
    {

        return (new Data($this->getBinding()))->getUserAccountByTimeGroup($dateTime);
    }

    /**
     * @param false|array $tblUserAccountAll
     *
     * @return array|bool result[GroupByTime][]
     * result[GroupByTime][]
     */
    public function getUserAccountListAndCount($tblUserAccountAll)
    {

        $result = array();
        if ($tblUserAccountAll && !empty($tblUserAccountAll)) {
            foreach ($tblUserAccountAll as $tblUserAccount) {
                $result[$tblUserAccount->getGroupByTime()][] = $tblUserAccount;
            }
        }
        return !empty($result) ? $result : false;
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
     * @param array $tblUserAccountList
     *
     * @return array
     */
    public function getExcelData($tblUserAccountList = array())
    {

        $result = array();
        if (!empty($tblUserAccountList)) {

            // set flag IsExport
            $this->updateIsExportBulk($tblUserAccountList);

            array_walk($tblUserAccountList, function (TblUserAccount $tblUserAccount) use (&$result) {
                $tblPerson = $tblUserAccount->getServiceTblPerson();
                $tblAccount = $tblUserAccount->getServiceTblAccount();

                $item['Salutation'] = $tblPerson->getSalutation();
                $item['Title'] = $tblPerson->getTitle();
                $item['FirstName'] = $tblPerson->getFirstName();
                $item['SecondName'] = $tblPerson->getSecondName();
                $item['LastName'] = $tblPerson->getLastName();
                $item['Gender'] = '';
                $item['AccountName'] = $tblAccount->getUsername();
                $item['Password'] = $tblUserAccount->getUserPassword();

                $item['StreetName'] = '';
                $item['StreetNumber'] = '';
                $item['CityCode'] = '';
                $item['CityName'] = '';
                $item['District'] = '';
                $item['State'] = '';
                $item['Nation'] = '';
                $item['Country'] = '';

                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                if ($tblCommon) {
                    $tblBirthDates = $tblCommon->getTblCommonBirthDates();
                    if ($tblBirthDates) {
                        $tblGender = $tblBirthDates->getTblCommonGender();
                        if ($tblGender) {
                            $item['Gender'] = substr($tblGender->getName(), 0, 1);
                        }
                    }
                }

                $tblAddress = $tblPerson->fetchMainAddress();
                if ($tblAddress) {
                    $item['StreetName'] = $tblAddress->getStreetName();
                    $item['StreetNumber'] = $tblAddress->getStreetNumber();
                    $tblCity = $tblAddress->getTblCity();
                    if ($tblCity) {
                        $item['CityCode'] = $tblCity->getCode();
                        $item['CityName'] = $tblCity->getName();
                        $item['District'] = $tblCity->getDistrict();
                    }
                    $tblState = $tblAddress->getTblState();
                    if ($tblState) {
                        $item['State'] = $tblState->getName();
                    }
                    $item['Nation'] = $tblAddress->getNation();
                    $item['Country'] = $tblAddress->getCounty();
                }

                array_push($result, $item);
            });
        }
        return $result;
    }

    /**
     * @param array $result
     *
     * @return false|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createClassListExcel($result = array())
    {

        if (!empty($result)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Anrede");
            $export->setValue($export->getCell("1", "0"), "Titel");
            $export->setValue($export->getCell("2", "0"), "Vorname");
            $export->setValue($export->getCell("3", "0"), "2. Vorn.");
            $export->setValue($export->getCell("4", "0"), "Name");
            $export->setValue($export->getCell("5", "0"), "M/W");
            $export->setValue($export->getCell("6", "0"), "Account");
            $export->setValue($export->getCell("7", "0"), "Passwort");
            $export->setValue($export->getCell("8", "0"), "Straße");
            $export->setValue($export->getCell("9", "0"), "Str.Nr.");
            $export->setValue($export->getCell("10", "0"), "PLZ");
            $export->setValue($export->getCell("11", "0"), "Stadt");
            $export->setValue($export->getCell("12", "0"), "Ortsteil");
            $export->setValue($export->getCell("13", "0"), "Bundesland");
            $export->setValue($export->getCell("14", "0"), "Land");

            $export->setStyle($export->getCell(0, 0), $export->getCell(14, 0))
                ->setFontBold();

            $Row = 0;

            foreach ($result as $Data) {
                $Row++;

                $export->setValue($export->getCell("0", $Row), $Data['Salutation']);
                $export->setValue($export->getCell("1", $Row), $Data['Title']);
                $export->setValue($export->getCell("2", $Row), $Data['FirstName']);
                $export->setValue($export->getCell("3", $Row), $Data['SecondName']);
                $export->setValue($export->getCell("4", $Row), $Data['LastName']);
                $export->setValue($export->getCell("5", $Row), $Data['Gender']);
                $export->setValue($export->getCell("6", $Row), $Data['AccountName']);
                $export->setValue($export->getCell("7", $Row), $Data['Password']);
                $export->setValue($export->getCell("8", $Row), $Data['StreetName']);
                $export->setValue($export->getCell("9", $Row), $Data['StreetNumber']);
                $export->setValue($export->getCell("10", $Row), $Data['CityCode']);
                $export->setValue($export->getCell("11", $Row), $Data['CityName']);
                $export->setValue($export->getCell("12", $Row), $Data['District']);
                $export->setValue($export->getCell("13", $Row), $Data['Nation']);
                $export->setValue($export->getCell("14", $Row), $Data['Country']);
            }

            //Column width
            $export->setStyle($export->getCell(0, 0), $export->getCell(0, $Row))->setColumnWidth(8);
            $export->setStyle($export->getCell(1, 0), $export->getCell(1, $Row))->setColumnWidth(9);
            $export->setStyle($export->getCell(2, 0), $export->getCell(2, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell(3, 0), $export->getCell(3, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell(4, 0), $export->getCell(4, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell(5, 0), $export->getCell(5, $Row))->setColumnWidth(5);
            $export->setStyle($export->getCell(6, 0), $export->getCell(6, $Row))->setColumnWidth(22);
            $export->setStyle($export->getCell(7, 0), $export->getCell(7, $Row))->setColumnWidth(12);
            $export->setStyle($export->getCell(8, 0), $export->getCell(8, $Row))->setColumnWidth(22);
            $export->setStyle($export->getCell(9, 0), $export->getCell(9, $Row))->setColumnWidth(7);
            $export->setStyle($export->getCell(10, 0), $export->getCell(10, $Row))->setColumnWidth(7);
            $export->setStyle($export->getCell(11, 0), $export->getCell(11, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell(12, 0), $export->getCell(12, $Row))->setColumnWidth(12);
            $export->setStyle($export->getCell(13, 0), $export->getCell(13, $Row))->setColumnWidth(11);
            $export->setStyle($export->getCell(14, 0), $export->getCell(14, $Row))->setColumnWidth(12);

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }
        return false;
    }

    /**
     * @param IFormInterface $form
     * @param array          $PersonIdArray
     * @param string         $AccountType S = Student, C = Custody
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

        $successCount = 0;
        $errorCount = 0;

        foreach ($PersonIdArray as $PersonId) {
            $tblPerson = Person::useService()->getPersonById($PersonId);
            if ($tblPerson) {
                // ignore Person with Account
                if (AccountPlatform::useService()->getAccountAllByPerson($tblPerson)) {
                    continue;
                }
                // ignore Person without Main Address
                $tblAddress = $tblPerson->fetchMainAddress();
                if (!$tblAddress) {
                    $IsMissingAddress = true;
                    $errorCount++;
                    continue;
                }
                // ignore without Consumer
                $tblConsumer = Consumer::useService()->getConsumerBySession();
                if ($tblConsumer == '') {
                    continue;
                }
                $Acronym = $AccountType;
                if ($Acronym == 'C') {
                    // Custody = "E"ltern
                    $Acronym = 'E';
                }
                $name = $this->generateUserName($tblPerson, $tblConsumer, $Acronym);
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
                    AccountPlatform::useService()->addAccountPerson($tblAccount, $tblPerson);
                    $successCount++;

                    if ($AccountType == 'S') {
                        $type = TblUserAccount::VALUE_TYPE_STUDENT;
                    } elseif ($AccountType == 'C') {
                        $type = TblUserAccount::VALUE_TYPE_CUSTODY;
                    } else {
                        // default setting
                        $type = TblUserAccount::VALUE_TYPE_STUDENT;
                    }
                    // add tblUserAccount
                    $this->createUserAccount($tblAccount, $tblPerson, $TimeStamp, $password,
                        $type);
                }
            }
        }
        return new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        ($IsMissingAddress
                            ? new Warning($errorCount.' Personen ohne Hauptadresse ignoriert ('.$successCount.
                                ' Benutzerzugänge zu Personen mit Gültiger Hauptadresse wurden erfolgreich angelegt).')
                            : new Success($successCount.' Benutzer wurden erfolgreich angelegt.')
                        )
                    ),
//                    new LayoutColumn(
//                        ($AccountType == 'S'
//                            ? new Redirect('/Setting/User/Account/Student/Add',
//                                ($IsMissingAddress
//                                    ? Redirect::TIMEOUT_ERROR
//                                    : Redirect::TIMEOUT_SUCCESS
//                                ))
//                            : ($AccountType == 'C'
//                                ? new Redirect('/Setting/User/Account/Custody/Add',
//                                    ($IsMissingAddress
//                                        ? Redirect::TIMEOUT_ERROR
//                                        : Redirect::TIMEOUT_SUCCESS
//                                    ))
//                                : ''
//                            )
//                        )
//                    )
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
        \DateTime $TimeStamp,
        $userPassword,
        $type
    ) {

        return ( new Data($this->getBinding()) )->createUserAccount(
            $tblAccount,
            $tblPerson,
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
     * @param TblUserAccount[] $tblUserAccountList
     *
     * @return bool
     */
    public function updateIsExportBulk($tblUserAccountList)
    {

        return (new Data($this->getBinding()))->updateIsExportBulk($tblUserAccountList);
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

    /**
     * @param \DateTime $GroupByTime
     *
     * @return bool
     */
    public function clearPassword(\DateTime $GroupByTime)
    {

        $tblUserAccountList = $this->getUserAccountByTimeGroup($GroupByTime);
        if ($tblUserAccountList) {
            return (new Data($this->getBinding()))->updateUserAccountClearPassword($tblUserAccountList);
        }
        return false;
    }
}