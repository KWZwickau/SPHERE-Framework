<?php
namespace SPHERE\Application\Setting\Univention;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\Application\Setting\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\Univention\Service\Data;
use SPHERE\Application\Setting\Univention\Service\Entity\TblUnivention;
use SPHERE\Application\Setting\Univention\Service\Setup;
use SPHERE\Application\Setting\User\Account\Account as AccountUser;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\System\Database\Binding\AbstractService;

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

        // Mitarbeiter ohne 2 Wege Authentifizierung
        $tblIdentification = Account::useService()->getIdentificationByName(TblIdentification::NAME_CREDENTIAL);
        if(($tblAccountList3 = Account::useService()->getAccountListByIdentification($tblIdentification))){
            $tblAccountList = array_merge($tblAccountList, $tblAccountList3);
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
     * @param TblYear[] $tblYear
     * @param string  $Acronym
     * @param array   $TeacherClasses
     * @param array   $schoolList
     * @param array   $roleList
     *
     * @return array
     */
    public function getAccountActive(
        $tblYearList,
        $Acronym = '',
        $TeacherClasses = array(),
        $schoolList = array(),
        $roleList = array()
    ) {

        $tblAccountList = Univention::useService()->getAccountAllForAPITransfer();
        $activeAccountList = array();

        if($tblAccountList){
            array_walk($tblAccountList, function(TblAccount $tblAccount) use (
                $tblYearList,
                $Acronym,
                &$activeAccountList,
                $TeacherClasses,
                $schoolList,
                $roleList
            ) {
                // Reihenfolge für Fehleranzeige wichtig
                $UploadItem['name'] = $tblAccount->getUsername();
                $UploadItem['roles'] = array();
                $UploadItem['school_classes'] = array();
                $UploadItem['email'] = $tblAccount->getUserAlias();
//                $UploadItem['password'] = $tblAccount->getPassword();
                $UploadItem['firstname'] = '';
                $UploadItem['lastname'] = '';
                $UploadItem['record_uid'] = $tblAccount->getId();
                $UploadItem['source_uid'] = $Acronym.'-'.$tblAccount->getId();
                $UploadItem['schools'] = array($schoolList[$Acronym]);
                $UploadItem['recoveryMail'] = $tblAccount->getRecoveryMail();
//            $UploadItem['password'] = '';// no passwort transfer
                $UploadItem['school_type'] = '';
                $UploadItem['groupArray'] = '';

                $tblDivisionCourse = false;
                $tblSchoolType = false;
                $tblPerson = Account::useService()->getPersonAllByAccount($tblAccount);
                if($tblPerson){
                    $tblPerson = current($tblPerson);
                    $UploadItem['firstname'] = $tblPerson->getFirstName();
                    $UploadItem['lastname'] = $tblPerson->getLastName();
                    $tblDivisionCourse = false;
                    if($tblYearList){
                        foreach($tblYearList as $tblYear){
                            if(($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))){
                                $tblDivisionCourse = $tblStudentEducation->getTblDivision();
                                $tblSchoolType = $tblStudentEducation->getServiceTblSchoolType();
                                if($tblDivisionCourse){
                                    break;
                                }
                            }
                        }
                    }
                }
                // Klasse

                if($tblDivisionCourse){
                    $ClassName = $this->getCorrectionClassNameByDivision($tblDivisionCourse);
                    $UploadItem['school_classes'][$Acronym][] = $ClassName;
                    if($tblSchoolType && ( $SchoolTypeString = $tblSchoolType->getShortName() ))
                        $UploadItem['school_type'] = $SchoolTypeString;
                } else {
                    if(isset($TeacherClasses[$tblPerson->getId()])){
                        $SchoolListWithClasses = $TeacherClasses[$tblPerson->getId()];
                        asort($SchoolListWithClasses);
                        $UploadItem['school_classes'] = $SchoolListWithClasses;
                    }
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
                    $UploadItem['groupArray'] = $groups;
                }

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
                if(is_string($User)){ // strpos($User, 'error when reading'
                    echo '<pre> Antwort der API:<br/>'.print_r($User, true).'</pre>';
                    continue;
                }

                // Nutzer ohne record_uid müssen in das Array mit eigenem Key aufgenommen werden
                if(!$User['record_uid']){
                    $User['record_uid'] = 'E'.$EmptyCount++;
                }
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
    public function getSchulsoftwareUser($roleList, $schoolList, $YearId = '')
    {

        $Acronym = Account::useService()->getMandantAcronym();
        // Lehraufträge
        $TeacherClasses = array();
        if($YearId && ($tblYear = Term::useService()->getYearById($YearId))){
            $this->getTeacherClassesByYear($Acronym, $tblYear, $TeacherClasses);
            $tblYearList[] = $tblYear;
        } elseif(($tblYearList = Term::useService()->getYearByNow())){
            foreach($tblYearList as $tblYear){
                $this->getTeacherClassesByYear($Acronym, $tblYear, $TeacherClasses);
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

        return Univention::useService()->getAccountActive($tblYearList, $Acronym, $TeacherClasses, $schoolList, $roleList);
    }

    /**
     * @param $Acronym
     * @param $tblYear
     * @param $TeacherClasses
     *
     * @return void
     */
    private function getTeacherClassesByYear($Acronym, $tblYear, &$TeacherClasses)
    {
        if(($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear))){
            foreach($tblTeacherLectureshipList as $tblTeacherLectureship){
                $tblPersonTeacher = $tblTeacherLectureship->getServiceTblPerson();
                $tblDivisionCourse = $tblTeacherLectureship->getTblDivisionCourse();
                $ClassName = $this->getCorrectionClassNameByDivision($tblDivisionCourse);
                $TeacherClasses[$tblPersonTeacher->getId()][$Acronym][$tblDivisionCourse->getId()] = $ClassName;
//                // doppelte werte entfernen
//                $TeacherClasses[$tblPersonTeacher->getId()][$Acronym] = array_unique($TeacherClasses[$tblPersonTeacher->getId()][$Acronym]);
            }
        }
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
                if(($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear))){
                    foreach($tblTeacherLectureshipList as $tblTeacherLectureship){
                        $tblPersonTeacher = $tblTeacherLectureship->getServiceTblPerson();
                        $tblDivisionCourse = $tblTeacherLectureship->getTblDivisionCourse();
                        $ClassName = $this->getCorrectionClassNameByDivision($tblDivisionCourse);
                        $tblSubject = $tblTeacherLectureship->getServiceTblSubject();
                        $TeacherClasses[$tblPersonTeacher->getId()][$tblDivisionCourse->getId()] = $tblSubject->getAcronym().'-'.$ClassName;
                    }
                }
            }
        }

        if($tblAccountList){
            /** @var TblAccount $tblAccount */
            foreach ($tblAccountList as $tblAccount) {
                $UploadItem = array();
                $UploadItem['Type'] = 'Teacher';
                if($tblAccount->getServiceTblIdentification()->getName() == TblIdentification::NAME_USER_CREDENTIAL){
                    $UploadItem['Type'] = 'Student';
                }
                $UploadItem['name'] = $tblAccount->getUsername();
                $UploadItem['firstname'] = '';
                $UploadItem['lastname'] = '';
                $UploadItem['record_uid'] = $tblAccount->getId();
                $UploadItem['source_uid'] = $Acronym.'-'.$tblAccount->getId();
                $UploadItem['roles'] = '';
                $UploadItem['schools'] = '';
                $UploadItem['mail'] = '';
                $UploadItem['BackupMail'] = '';
                $UploadItem['group'] = '';

                $UploadItem['password'] = '';
//                $UploadItem['password'] = $tblAccount->getPassword(); // ??
                $UploadItem['school_classes'] = '';
                $UploadItem['school_type'] = '';

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
                    $Item['Type'] = 'Student';
                    $Item['name'] = '';
                    $Item['firstname'] = '';
                    $Item['lastname'] = '';
                    $Item['record_uid'] = '';
                    $Item['source_uid'] = $Acronym.'-';
                    $Item['roles'] = '';
                    $Item['schools'] = '';
                    $Item['password'] = '';
                    $Item['school_classes'] = '';
                    $Item['school_type'] = '';
                    $Item['mail'] = '';
                    $Item['BackupMail'] = '';
                    $Item['group'] = '';

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
     * @return array ShortName of SchoolTypes as array
     */
    public function getSchoolTypeException(){
        $list = array();
        if(!($tblSetting = Consumer::useService()->getSetting('Setting', 'Univention', 'Univention', 'API_Mail'))){
            return $list;
        }
        $Value = $tblSetting->getValue();
        if($Value != '' && ($TypeList = explode(',', $Value))){
            foreach($TypeList as $Type){
                if(($tblType = Type::useService()->getTypeByShortName(trim($Type)))){
                    $list[] = $tblType->getShortName();
                }
            }
        }
        return $list;
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
            }
        }
        if(($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))){
            if(($tblDivisionCourseCoreGroup = $tblStudentEducation->getTblCoreGroup())) {
                $Item['group'] = $tblDivisionCourseCoreGroup->getName();
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
        $Item['roles'] = implode(',', $roles);
        $Item['schools'] = $Acronym;
        // Student Search Division
        if(($StudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
            if(($tblDivisionCourse = $StudentEducation->getTblDivision())
            && $tblSchoolType = $StudentEducation->getServiceTblSchoolType()){
                $Item['school_classes'] = $Acronym.'-'.$this->getCorrectionClassNameByDivision($tblDivisionCourse);
                $Item['school_type'] = $tblSchoolType->getShortName();
            }
        } else {
            if(isset($TeacherClasses[$tblPerson->getId()])) {
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

            $row = $column = 0;
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell($column++, $row), "uid");
            $export->setValue($export->getCell($column++, $row), "Schulen_OU");
            $export->setValue($export->getCell($column++, $row), "Vorname");
            $export->setValue($export->getCell($column++, $row), "Nachname");
            $export->setValue($export->getCell($column++, $row), "Rollen");
            $export->setValue($export->getCell($column++, $row), "Klassen");
            $export->setValue($export->getCell($column++, $row), "Benutzername");
            $export->setValue($export->getCell($column++, $row), "Passwort");
            $export->setValue($export->getCell($column++, $row), "Externe_Mailadresse");
            $export->setValue($export->getCell($column++, $row), "PW_vergessen_Mail");
            $export->setValue($export->getCell($column ,$row++), "Stammgruppe");
            foreach ($AccountData as $Account)
            {
                // Accounts mit Umlauten überspringen
                if($this->checkName($Account['name'])){
                    continue;
                }
                $column = 0;
                $export->setValue($export->getCell($column++, $row), $Account['record_uid']);
                $export->setValue($export->getCell($column++, $row), $Account['schools']);
                $export->setValue($export->getCell($column++, $row), $Account['firstname']);
                $export->setValue($export->getCell($column++, $row), $Account['lastname']);
                $export->setValue($export->getCell($column++, $row), $Account['roles']);
                $export->setValue($export->getCell($column++, $row), $Account['school_classes']);
                $export->setValue($export->getCell($column++, $row), $Account['name']);
                $export->setValue($export->getCell($column++, $row), $Account['password']);
                $export->setValue($export->getCell($column++, $row), $Account['mail']);
                $export->setValue($export->getCell($column++, $row), $Account['BackupMail']);
                $export->setValue($export->getCell($column, $row++), $Account['group']);
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

            $row = $column = 0;
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell($column++, $row), "OU");
            $export->setValue($export->getCell($column, $row), "Schulname");
            $column = 0;
            $row++;
            $export->setValue($export->getCell($column++, $row), $OU);
            $export->setValue($export->getCell($column, $row), $Schulname);

            $export->setDelimiter(',');
            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param TblDivisionCourse|null $tblDivisionCourse
     *
     * @return string
     */
    public function getCorrectionClassNameByDivision(TblDivisionCourse $tblDivisionCourse = null)
    {
        $ClassName = $tblDivisionCourse->getDisplayName();
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