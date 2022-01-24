<?php
namespace SPHERE\Application\Setting\Univention;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\Application\Setting\Authorization\Account\Account;
use SPHERE\Application\Setting\Univention\Service\Data;
use SPHERE\Application\Setting\Univention\Service\Entity\TblUnivention;
use SPHERE\Application\Setting\Univention\Service\Setup;
use SPHERE\Application\Setting\User\Account\Account as AccountUser;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class Service
 * @package SPHERE\Application\Setting\Univention
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

        $Protocol = '';
        if (!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData){
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param string $Type
     *
     * @return false|TblUnivention
     */
    public function getUnivention($Type)
    {

        return (new Data($this->getBinding()))->getUniventionByType($Type);
    }

    /**
     * @param string $Type
     * @param string $Value
     *
     * @return TblUnivention
     */
    public function createUnivention($Type, $Value)
    {

        return (new Data($this->getBinding()))->createUnivention($Type, $Value);
    }

    /**
     * @return array|bool|TblAccount
     */
    public function getAccountAllForAPITransfer()
    {

        // Mitarbeiter / Lehrer mit Token
        $tblIdentification = Account::useService()->getIdentificationByName(TblIdentification::NAME_TOKEN);
        $tblAccountList = Account::useService()->getAccountListByIdentification($tblIdentification);

        if (!is_array($tblAccountList)){
            $tblAccountList = array();
        }

        // Mitarbeiter / Lehrer mit Authenticator App
        $tblIdentification = Account::useService()->getIdentificationByName(TblIdentification::NAME_AUTHENTICATOR_APP);
        if(($tblAccountList2 = Account::useService()->getAccountListByIdentification($tblIdentification))){
            $tblAccountList = array_merge($tblAccountList, $tblAccountList2);
        }

        // Student
        if ($tblUserAccountList = AccountUser::useService()->getUserAccountAllByType(TblUserAccount::VALUE_TYPE_STUDENT)){
            foreach ($tblUserAccountList as $tblUserAccount) {
                if ($tblUserAccount->getServiceTblAccount()){
                    $tblAccountList[] = $tblUserAccount->getServiceTblAccount();
                }
            }
        }
        return (!empty($tblAccountList) ? $tblAccountList : false);
    }

    /**
     * @param TblYear $tblYear
     * @param string  $Acronym
     * @param array   $TeacherClasses
     * @param array   $schoolList
     * @param array   $roleList
     *
     * @return array
     */
    public function getAccountActive(
        TblYear $tblYear,
        $Acronym = '',
        $TeacherClasses = array(),
        $schoolList = array(),
        $roleList = array()
    ) {

        $tblAccountList = Univention::useService()->getAccountAllForAPITransfer();
        $activeAccountList = array();

        if($tblAccountList){
            array_walk($tblAccountList, function(TblAccount $tblAccount) use (
                $tblYear,
                $Acronym,
                &$activeAccountList,
                $TeacherClasses,
                $schoolList,
                $roleList
            ) {
                $UploadItem['name'] = $tblAccount->getUsername();
                $UploadItem['email'] = $tblAccount->getUserAlias();
//                $UploadItem['password'] = $tblAccount->getPassword();
                $UploadItem['firstname'] = '';
                $UploadItem['lastname'] = '';
                $UploadItem['record_uid'] = $tblAccount->getId();
                $UploadItem['source_uid'] = $Acronym.'-'.$tblAccount->getId();
                $UploadItem['roles'] = array();
                $UploadItem['schools'] = array();
                $UploadItem['recoveryMail'] = $tblAccount->getRecoveryMail();

//            $UploadItem['password'] = '';// no passwort transfer
                $UploadItem['school_classes'] = array();
                $UploadItem['group'] = '';

                $tblPerson = Account::useService()->getPersonAllByAccount($tblAccount);
                if($tblPerson){
                    $tblPerson = current($tblPerson);
                    $UploadItem['firstname'] = $tblPerson->getFirstName();
                    $UploadItem['lastname'] = $tblPerson->getLastName();
                }
                // Rollen
                $tblGroupList = Group::useService()->getGroupAllByPerson($tblPerson);
                $groups = array();
                $roles = array();
                $IsTeacher = $IsStaff = $IsStudent = false;
                if($tblGroupList){
                    foreach($tblGroupList as $tblGroup) {
                        if($tblGroup->getMetaTable() === TblGroup::META_TABLE_STAFF){
                            // teacher hat Vorrang
                            if(!isset($roles[0])){
                                $roles[0] = $roleList['staff'];
                                $IsStaff = true;
                            }
                        }
                        if($tblGroup->getMetaTable() === TblGroup::META_TABLE_TEACHER){
                            $roles[0] = $roleList['teacher'];
                            $IsTeacher = true;
                        }
                        if($tblGroup->getMetaTable() === TblGroup::META_TABLE_STUDENT){
                            $roles[0] = $roleList['student'];
                            $IsStudent = true;
                        }
                        if($tblGroup->isCoreGroup()){
                            $groups[$tblGroup->getId()] = $tblGroup->getName();
                        }
                    }
                }
                if($IsStudent && ($IsStaff || $IsTeacher)){
                    unset($roles[0]);
                }
                if(!empty($roles)){
                    $UploadItem['roles'] = $roles;
                }
                if(!empty($groups)){
                    $Item['groupArray'] = $groups;
                }

                // Mandant wird als Schule verwendet
                $Item['schools'] = array($schoolList[$Acronym]);

                $tblDivision = false;
                if(($tblStudent = Student::useService()->getStudentByPerson($tblPerson))){
                    $tblDivision = $tblStudent->getCurrentMainDivision();
                }
                if($tblDivision){
                    $ClassName = $this->getCorrectionClassNameByDivision($tblDivision);
                    $UploadItem['school_classes'][$Acronym][] = $ClassName;
                } else {
                    if(isset($TeacherClasses[$tblPerson->getId()])){
                        $SchoolListWithClasses = $TeacherClasses[$tblPerson->getId()];
                        asort($SchoolListWithClasses);
                        $UploadItem['school_classes'] = $SchoolListWithClasses;
                    }
                }

                $UploadItem['schools'] = array($schoolList[$Acronym]);

                array_push($activeAccountList, $UploadItem);
            });
        }

        return $activeAccountList;
    }

    /**
     * @return array
     */
    public function getApiUser()
    {

        // Benutzerliste suchen
        $Acronym = Account::useService()->getMandantAcronym();
        $UniventionUserList = (new UniventionUser())->getUserListByProperty('name',$Acronym.'-', true);
        $UserUniventionList = array();
        if($UniventionUserList){
            $EmptyCount = 0;
            foreach($UniventionUserList as $User){
                //  Ignore DllpServiceAccounts with value 1
                if(isset($User['udm_properties']['DllpServiceAccount']) && $User['udm_properties']['DllpServiceAccount'] == '1'){
                    continue;
                }

                // Nutzer ohne record_uid müssen in das Array mit eigenem Key aufgenommen werden
                if(!$User['record_uid']){
                    $User['record_uid'] = 'E'.$EmptyCount++;
                }
                $UserUniventionList[$User['record_uid']] =
                    // dn, url, ucsschool_roles[], name, school, firstname, lastname, birthday, disabled, email, record_uid,
                    // roles, schools, school_classes, source_uid, udm_properties
                $UserUniventionList[$User['record_uid']] = array(
                    'record_uid' => (isset($User['record_uid']) ? $User['record_uid'] : ''),
                    'name' => (isset($User['name']) ? $User['name'] : ''),
                    'school' => (isset($User['school']) ? $User['school'] : ''),
                    'firstname' => (isset($User['firstname']) ? $User['firstname'] : ''),
                    'lastname' => (isset($User['lastname']) ? $User['lastname'] : ''),
                    'birthday' => (isset($User['birthday']) ? $User['birthday'] : ''),
                    'email' => (isset($User['email']) ? $User['email'] : ''),
                    'roles' => (isset($User['roles']) ? $User['roles'] : array()),
                    'schools' => (isset($User['schools']) ? $User['schools'] : array()),
//                    // set no content so -> get no content
                    'school_classes' => (($User['school_classes']) ? $User['school_classes'] : array()),
                    // Wird nur beim Import mitgegeben, benötigen wir aber nicht
//                    'source_uid' => (isset($User['source_uid']) ? $User['source_uid'] : ''),
//                    // get no content
                    'udm_properties' => (isset($User['udm_properties']) ? $User['udm_properties'] : array()),
                    // Liefert Array zurück, das Feld werden wir nicht benötigen.
//                    'e-mail' => (isset($User['udm_properties']['e-mail']) ? $User['udm_properties']['e-mail'] : ''),
                );
            }
        }
        return $UserUniventionList;
    }

    /**
     * @param $roleList
     * @param $schoolList
     *
     * @return array
     */
    public function getSchulsoftwareUser($roleList, $schoolList)
    {

        $Acronym = Account::useService()->getMandantAcronym();
        // Lehraufträge
        $TeacherClasses = array();
        if(($tblYearList = Term::useService()->getYearByNow())){
            foreach($tblYearList as $tblYear){
                if(($tblDivisionList = Division::useService()->getDivisionByYear($tblYear))){
                    foreach($tblDivisionList as $tblDivision){
                        if(($tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision))){
                            foreach($tblDivisionSubjectList as $tblDivisionSubject){
                                if(($tblDivisionTeacherList = Division::useService()->getSubjectTeacherByDivisionSubject($tblDivisionSubject))){
                                    foreach($tblDivisionTeacherList as $tblDivisionTeacher){
                                        if(($tblPersonTeacher = $tblDivisionTeacher->getServiceTblPerson())){
//                                            if($Acronym == 'REF'){
//                                                $Acronym = 'DLLP';
//                                            }
                                            $ClassName = $this->getCorrectionClassNameByDivision($tblDivision);
                                            $TeacherClasses[$tblPersonTeacher->getId()][$Acronym][] = $ClassName;
                                            // doppelte werte entfernen
                                            $TeacherClasses[$tblPersonTeacher->getId()][$Acronym] = array_unique($TeacherClasses[$tblPersonTeacher->getId()][$Acronym]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        // ArrayKey muss immer eine normale Zählung bei 0 beginnend ohne Lücken erhalten 0,1,2,3...
        // Key PersonId
        foreach($TeacherClasses as &$AcronymTemp) {
            // Key Acronym
            foreach($AcronymTemp as &$ClassList){
                sort($ClassList);
            }
        }

        return Univention::useService()->getAccountActive($tblYear, $Acronym, $TeacherClasses, $schoolList, $roleList);
    }

    /**
     * @param bool $StudentWithoutAccount
     *
     * @return false|array
     */
    public function getExportAccount($StudentWithoutAccount = true)
    {

        $Acronym = Account::useService()->getMandantAcronym();
        $tblAccountList = Univention::useService()->getAccountAllForAPITransfer();

        $UploadToAPI = array();
        $TeacherClasses = array();

        // Lehraufträge
        if(($tblYearList = Term::useService()->getYearByNow())){
            foreach($tblYearList as $tblYear) {
                if(($tblDivisionList = Division::useService()->getDivisionByYear($tblYear))){
                    foreach($tblDivisionList as $tblDivision) {
                        if(($tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision))){
                            foreach($tblDivisionSubjectList as $tblDivisionSubject) {
                                if(($tblDivisionTeacherList = Division::useService()->getSubjectTeacherByDivisionSubject($tblDivisionSubject))){
                                    foreach($tblDivisionTeacherList as $tblDivisionTeacher) {
                                        if(($tblPersonTeacher = $tblDivisionTeacher->getServiceTblPerson())){
                                            $ClassName = $this->getCorrectionClassNameByDivision($tblDivision);
                                            $TeacherClasses[$tblPersonTeacher->getId()][$tblDivision->getId()] = $Acronym.'-'.$ClassName;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if($tblAccountList){
            foreach ($tblAccountList as $tblAccount) {
                $UploadItem = array();
                $UploadItem['name'] = $tblAccount->getUsername();
                $UploadItem['firstname'] = '';
                $UploadItem['lastname'] = '';
                $UploadItem['record_uid'] = $tblAccount->getId();
                $UploadItem['source_uid'] = $Acronym.'-'.$tblAccount->getId();
                $UploadItem['roles'] = '';
                $UploadItem['schools'] = '';
                $UploadItem['mail'] = '';
                $UploadItem['BackupMail'] = '';
                $UploadItem['groupArray'] = '';

                $UploadItem['password'] = '';
//                $UploadItem['password'] = $tblAccount->getPassword(); // ??
                $UploadItem['school_classes'] = '';

                if ($tblPerson = Account::useService()->getPersonAllByAccount($tblAccount)){
                    $tblPerson = current($tblPerson);
                    $UploadItem = $this->getPersonDataExcel($UploadItem, $tblPerson, $tblYear, $Acronym, $TeacherClasses);
                } else {
                    // Ohne Person kein sinnvoller Account
                    continue;
                }

                if($UploadItem){
                    array_push($UploadToAPI, $UploadItem);
                }
            }
        }
        if($StudentWithoutAccount){
            $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT);
            $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
            if($tblPersonList){
                foreach($tblPersonList as $tblPerson){
                    if(Account::useService()->getAccountAllByPerson($tblPerson)){
                        // ignore students with account
                        continue;
                    }
                    $Item['name'] = '';
                    $Item['firstname'] = '';
                    $Item['lastname'] = '';
                    $Item['record_uid'] = '';
                    $Item['source_uid'] = $Acronym.'-';
                    $Item['roles'] = '';
                    $Item['schools'] = '';
                    $Item['password'] = '';
                    $Item['school_classes'] = '';
                    $Item['mail'] = '';
                    $Item['BackupMail'] = '';
                    $Item['groupArray'] = '';

                    $Item = $this->getPersonDataExcel($Item, $tblPerson, $tblYear, $Acronym, $TeacherClasses);

                    if($Item){
                        array_push($UploadToAPI, $Item);
                    }
                }
            }
        }

        return (!empty($UploadToAPI) ? $UploadToAPI : false);
    }

    /**
     * @param array     $Item
     * @param TblPerson $tblPerson
     * @param TblYear   $tblYear
     * @param string    $Acronym
     * @param array     $TeacherClasses
     *
     * @return bool|array
     */
    private function getPersonDataExcel(
        array $Item,
        TblPerson $tblPerson,
        TblYear $tblYear,
        $Acronym,
        $TeacherClasses
    ) {

        $Item['firstname'] = $tblPerson->getFirstName();
        $Item['lastname'] = $tblPerson->getLastName();

        // Rollen
        $tblGroupList = Group::useService()->getGroupAllByPerson($tblPerson);
        $roles = array();
        $groups = array();
        if($tblGroupList && !empty($tblGroupList)){
            foreach ($tblGroupList as $tblGroup) {
                if ($tblGroup->getMetaTable() === TblGroup::META_TABLE_STAFF){
                    $roles[] = 'staff';
                }
                if ($tblGroup->getMetaTable() === TblGroup::META_TABLE_TEACHER){
                    $roles[] = 'teacher';
                }
                if ($tblGroup->getMetaTable() === TblGroup::META_TABLE_STUDENT){
                    $roles[] = 'student';
                }
                if($tblGroup->isCoreGroup()){
                    $groups[] = $tblGroup->getName();
                }
            }
        }
        // decide teacher / Stuff
        if(in_array('staff', $roles) && in_array('teacher', $roles)){
            $roles = array('teacher');
        }


        if(empty($roles)){
            // Accounts die nicht/nicht mehr zu den 3 Rollen gehören sollen entfernt werden
            return false;
        }
        if(!empty($groups)){
            $Item['groupArray'] = $groups;
        }
        $Item['roles'] = implode(',', $roles);

        $tblDivision = Division::useService()->getDivisionByPersonAndYear($tblPerson, $tblYear);

        $Item['schools'] = $Acronym;

        // Student Search Division
        if ($tblDivision){
            $Item['school_classes'] = $Acronym.'-'.$this->getCorrectionClassNameByDivision($tblDivision);
        } else {
            if(isset($TeacherClasses[$tblPerson->getId()])){
                $ClassList = $TeacherClasses[$tblPerson->getId()];
                sort($ClassList);
                $Item['school_classes'] = implode(',', $ClassList);
            }
        }

        if($tblAccountList = Account::useService()->getAccountAllByPerson($tblPerson)){
            $tblAccount = current($tblAccountList);
            $Item['mail'] = $tblAccount->getUserAlias();
            $Item['BackupMail'] = $tblAccount->getRecoveryMail();
        }
        return $Item;
    }

    /**
     * @return false|FilePointer
     */
    public function downlaodAccountExcel()
    {

        $AccountData = $this->getExportAccount(false);

        if (!empty($AccountData))
        {

            $fileLocation = Storage::createFilePointer('csv');

            $Row = $Column = 0;
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell($Column++, $Row), "uid");
            $export->setValue($export->getCell($Column++, $Row), "Schulen_OU");
            $export->setValue($export->getCell($Column++, $Row), "Vorname");
            $export->setValue($export->getCell($Column++, $Row), "Nachname");
            $export->setValue($export->getCell($Column++, $Row), "Rollen");
            $export->setValue($export->getCell($Column++, $Row), "Klassen");
            $export->setValue($export->getCell($Column++, $Row), "Benutzername");
            $export->setValue($export->getCell($Column++, $Row), "Passwort");
            $export->setValue($export->getCell($Column++, $Row), "Externe_Mailadresse");
            $export->setValue($export->getCell($Column++ , $Row), "PW_vergessen_Mail");
            $export->setValue($export->getCell($Column , $Row), "Stammgruppe");

            foreach ($AccountData as $Account)
            {

                // Accounts mit Umlauten überspringen
                if($this->checkName($Account['name'])){
                    continue;
                }

                $Column = 0;
                $Row++;

                $export->setValue($export->getCell($Column++, $Row), $Account['record_uid']);
                $export->setValue($export->getCell($Column++, $Row), $Account['schools']);
                $export->setValue($export->getCell($Column++, $Row), $Account['firstname']);
                $export->setValue($export->getCell($Column++, $Row), $Account['lastname']);
                $export->setValue($export->getCell($Column++, $Row), $Account['roles']);
                $export->setValue($export->getCell($Column++, $Row), $Account['school_classes']);
                $export->setValue($export->getCell($Column++, $Row), $Account['name']);
                $export->setValue($export->getCell($Column++, $Row), $Account['password']);
                $export->setValue($export->getCell($Column++, $Row), $Account['mail']);
                $export->setValue($export->getCell($Column++, $Row), $Account['BackupMail']);
                if(is_array($Account['groupArray']) && !empty($Account['groupArray'])){
                    $GroupString = implode(',',$Account['groupArray']);
                    $export->setValue($export->getCell($Column, $Row), $GroupString);
                } else {
                    $export->setValue($export->getCell($Column, $Row), '');
                }
            }

            $export->setDelimiter(',');
            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @return false|FilePointer
     */
    public function downlaodSchoolExcel()
    {

        $OU = '';
        $Schulname = '';
        if(($tblAccount = Account::useService()->getAccountBySession())){
            if(($tblConsumer = $tblAccount->getServiceTblConsumer())){
                $OU = $tblConsumer->getAcronym();
                $Schulname = $tblConsumer->getName();
            }
        }

        if ($OU && $Schulname)
        {

            $fileLocation = Storage::createFilePointer('csv');

            $Row = $Column = 0;
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell($Column++, $Row), "OU");
            $export->setValue($export->getCell($Column, $Row), "Schulname");
            $Column = 0;
            $Row++;
            $export->setValue($export->getCell($Column++, $Row), $OU);
            $export->setValue($export->getCell($Column, $Row), $Schulname);

            $export->setDelimiter(',');
            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param TblDivision|null $tblDivision
     *
     * @return string
     */
    public function getCorrectionClassNameByDivision(TblDivision $tblDivision = null)
    {
        $ClassName = $tblDivision->getTblLevel()->getName().$tblDivision->getName();
        $ClassName = str_replace('ä', 'ae', $ClassName);
        $ClassName = str_replace('ü', 'ue', $ClassName);
        $ClassName = str_replace('ö', 'oe', $ClassName);
        $ClassName = str_replace('ß', 'ss', $ClassName);
        return $ClassName;
    }

    /**
     * return true if it's a problem with chars (Umlaute / Sonderzeichen)
     * @param $UserName
     *
     * @return bool
     */
    public function checkName($UserName)
    {
        if((preg_match('!(^[a-zA-Z0-9-]+)!', $UserName, $Match)) && strlen($Match[0]) != strlen($UserName)){
            // enthält andere Zeichen
            return true;
        }
        //alles ok
        return false;
    }
}