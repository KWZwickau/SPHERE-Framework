<?php
namespace SPHERE\Application\Setting\Univention;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Mail\Service\Entity\TblType as TblTypeMail;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferType;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Setting\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\School\School;
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

        // Mitarbeiter / Lehrer
        $tblIdentification = Account::useService()->getIdentificationByName(TblIdentification::NAME_TOKEN);
        $tblAccountList = Account::useService()->getAccountListByIdentification($tblIdentification);

        if (!is_array($tblAccountList)){
            $tblAccountList = array();
        }

        // Student
        if ($tblUserAccountList = AccountUser::useService()->getUserAccountAllByType(TblUserAccount::VALUE_TYPE_STUDENT)){
            foreach ($tblUserAccountList as $tblUserAccount) {
                if ($tblUserAccount->getServiceTblAccount()){
                    $tblAccountList[] = $tblUserAccount->getServiceTblAccount();
                }
            }

//            // ToDO remove einschränkung
//            $i = 0;
//            foreach($tblAccountList as &$tblAccount){
//                $UserName = $tblAccount->getUsername();
//                if(preg_match('![üöä]!', $UserName)){
//                    $tblAccount = false;
//                    continue;
//                }
//                $i++;
//                if($i > 5){
//                    $tblAccount = false;
//                }
//            }
//            $tblAccountList = array_filter($tblAccountList);
        }
        return (!empty($tblAccountList) ? $tblAccountList : false);
    }

    /**
     * @param TblYear $tblYear
     * @param string  $Acronym
     * @param array   $TeacherSchools
     * @param array   $TeacherClasses
     * @param array   $schoolList
     * @param array   $roleList
     *
     * @return array
     */
    public function getAccountActive(TblYear $tblYear, $Acronym = '', $TeacherSchools = array(), $TeacherClasses = array(), $schoolList = array(), $roleList = array())
    {

        $tblAccountList = Univention::useService()->getAccountAllForAPITransfer();
        $activeAccountList = array();

        if($tblAccountList){
            array_walk($tblAccountList, function(TblAccount $tblAccount) use ($tblYear, $Acronym, &$activeAccountList,
                $TeacherSchools, $TeacherClasses, $schoolList, $roleList){
                $UploadItem['name'] = $tblAccount->getUsername();
                $UploadItem['email'] = $tblAccount->getUserAlias();
                $UploadItem['firstname'] = '';
                $UploadItem['lastname'] = '';
                $UploadItem['record_uid'] = $tblAccount->getId();
                $UploadItem['source_uid'] = $Acronym.'-'.$tblAccount->getId();
                $UploadItem['roles'] = '';
                $UploadItem['schools'] = '';

//            $UploadItem['password'] = '';// no passwort transfer
                $UploadItem['school_classes'] = '';
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
                foreach($tblGroupList as $tblGroup){
                    if($tblGroup->getMetaTable() === TblGroup::META_TABLE_STAFF){
                    $roles[] = $roleList['staff'];
                    }
                    if($tblGroup->getMetaTable() === TblGroup::META_TABLE_TEACHER){
                    $roles[] = $roleList['teacher'];
                    }
                    if($tblGroup->getMetaTable() === TblGroup::META_TABLE_STUDENT){
                    $roles[] = $roleList['student'];
                    }
                    if($tblGroup->isCoreGroup()){
                        $groups[] = $tblGroup->getName();
                    }
                }
                if(!empty($roles)){
                    $UploadItem['roles'] = $roles;
                }
                if(!empty($groups)){
                    $Item['groupArray'] = $groups;
                }

                $tblDivision = Division::useService()->getDivisionByPersonAndYear($tblPerson, $tblYear);
                // Student Search Division
                $schools = array();
                $StudentSchool = '';
                if(!Consumer::useService()->isSchoolSeparated()){
                    // Mandant wird als Schule verwendet
                    $SchoolString = $this->getSchoolString($Acronym);
                    $schools[] = $SchoolString;
                    $StudentSchool = $SchoolString;
                } else {
                    if (($tblCompany = $tblDivision->getServiceTblCompany())){
                        if ($tblDivision){
                            // Schule über Schülerakte Company und Klasse (Schulart)
                            if (($tblSchoolType = $tblDivision->getType())){
                                $SchoolTypeString = Type::useService()->getSchoolTypeString($tblSchoolType);
                                $SchoolString = $this->getSchoolString($Acronym, $SchoolTypeString, $tblCompany);
                                $schools[] = $SchoolString;
                                $StudentSchool = $SchoolString;
                            }
                        }
                    }
                }

                if (!empty($schools)){
                    $schools = array_unique($schools);
                    $Item['schools'] = implode(',', $schools);
                } else {
                    if(isset($TeacherSchools[$tblPerson->getId()])){
                        $SchoolList = $TeacherSchools[$tblPerson->getId()];
                        $SchoolList = array_unique($SchoolList);
                        sort($SchoolList);
                        $Item['schools'] = implode(',', $SchoolList);
                    }
                }

//            // Uploadtest
//            //ToDO use API Schools
            if($tblStudent = Student::useService()->getStudentByPerson($tblPerson)){
                $schools = array(
                        $schoolList['DEMOSCHOOL']
                );
                $StudentSchool = 'DEMOSCHOOL';
            } else {
                $schools = array(
                    $schoolList['DEMOSCHOOL'],
//                    $schoolList['DEMOSCHOOL2']
                );
            }

                if($tblDivision){
                    $UploadItem['school_classes'] = $StudentSchool.'-'.$tblDivision->getTblLevel()->getName().$tblDivision->getName();
//                    $tblDivisionStudent = Division::useService()->getDivisionStudentByDivisionAndPerson($tblDivision, $tblPerson);
                } else {
                    if(isset($TeacherClasses[$tblPerson->getId()])){
                        $ClassList = $TeacherClasses[$tblPerson->getId()];
                        foreach($ClassList as &$Class){
                            $Class = $StudentSchool.'-'.$Class;
                        }
                        sort($ClassList);
                        $Item['school_classes'] = implode(',', $ClassList);
                    }
                }

                if(!empty($schools)){
                    $schools = array_unique($schools);
                }

                $UploadItem['schools'] = $schools;

                array_push($activeAccountList, $UploadItem);
            });
        }

        return $activeAccountList;
    }

    /**
     * @return array|bool
     */
    public function getApiUser()
    {

        // Benutzerliste suchen
        $UniventionUserList = (new UniventionUser())->getUserListByProperty('name','ref-', true);
        $UserUniventionList = array();
        if($UniventionUserList){
            foreach($UniventionUserList as $User){
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
//                    // get no content
                    'school_classes' => (($User['school_classes']) ? $User['school_classes'] : array()),
                    'source_uid' => (isset($User['source_uid']) ? $User['source_uid'] : ''),
//                    // get no content
                    'udm_properties' => (isset($User['udm_properties']) ? $User['udm_properties'] : array()),
                );
            }
        }
        return (!empty($UserUniventionList) ? $UserUniventionList : false);
    }

    public function getSchulsoftwareUser(TblYear $tblYear, $roleList, $schoolList)
    {

        $Acronym = Account::useService()->getMandantAcronym();
        // Lehraufträge
        $TeacherSchools = array();
        $TeacherClasses = array();
        if(($tblDivisionList = Division::useService()->getDivisionByYear($tblYear))){
            foreach($tblDivisionList as $tblDivision){
                if(($tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision))){
                    foreach($tblDivisionSubjectList as $tblDivisionSubject){
                        if(($tblDivisionTeacherList = Division::useService()->getSubjectTeacherByDivisionSubject($tblDivisionSubject))){
                            foreach($tblDivisionTeacherList as $tblDivisionTeacher){
                                if(($tblPersonTeacher = $tblDivisionTeacher->getServiceTblPerson())){
                                    $SchoolString = '';
                                    // wichtig für Schulgetrennte Klassen (nicht Mandantenweise)
                                    if(($tblCompany = $tblDivision->getServiceTblCompany())
                                        && Consumer::useService()->isSchoolSeparated()){
                                        if(($tblSchoolType = $tblDivision->getType())){
                                            $tblSchoolTypeString = Type::useService()->getSchoolTypeString($tblSchoolType);
                                            $SchoolString = $Acronym.$tblSchoolTypeString.$tblCompany->getId();
                                            $TeacherSchools[$tblPersonTeacher->getId()][$tblCompany->getId().'_'.$tblSchoolTypeString] = $SchoolString;
                                            $SchoolString .= '-';
                                        }
                                    }
                                    $TeacherClasses[$tblPersonTeacher->getId()][$tblDivision->getId()] = $SchoolString.$tblDivision->getTblLevel()->getName().$tblDivision->getName();
                                }
                            }
                        }
                    }
                }
            }
        }
        return Univention::useService()->getAccountActive($tblYear, $Acronym, $TeacherSchools, $TeacherClasses, $schoolList, $roleList);

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
        $TeacherSchools = array();

        $tblYear = Term::useService()->getYearByNow();
        if ($tblYear){
            $tblYear = current($tblYear);
            // Lehraufträge
            if(($tblDivisionList = Division::useService()->getDivisionByYear($tblYear))){
                foreach($tblDivisionList as $tblDivision){
                    if(($tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision))){
                        foreach($tblDivisionSubjectList as $tblDivisionSubject){
                            if(($tblDivisionTeacherList = Division::useService()->getSubjectTeacherByDivisionSubject($tblDivisionSubject))){
                                foreach($tblDivisionTeacherList as $tblDivisionTeacher){
                                    if(($tblPersonTeacher = $tblDivisionTeacher->getServiceTblPerson())){
                                        $SchoolString = '';
                                        // wichtig für Schulgetrennte Klassen (nicht Mandantenweise)
                                        if(($tblCompany = $tblDivision->getServiceTblCompany())
                                            && Consumer::useService()->isSchoolSeparated()){
                                            if(($tblSchoolType = $tblDivision->getType())){
                                                $tblSchoolTypeString = Type::useService()->getSchoolTypeString($tblSchoolType);
                                                $SchoolString = $Acronym.$tblSchoolTypeString.$tblCompany->getId();
                                                $TeacherSchools[$tblPersonTeacher->getId()][$tblCompany->getId().'_'.$tblSchoolTypeString] = $SchoolString;
                                                $SchoolString .= '-';
                                            }
                                        }
                                        $TeacherClasses[$tblPersonTeacher->getId()][$tblDivision->getId()] = $SchoolString.$tblDivision->getTblLevel()->getName().$tblDivision->getName();
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
                $UploadItem['groupArray'] = '';

                $UploadItem['password'] = '';
//                $UploadItem['password'] = $tblAccount->getPassword(); // ??
                $UploadItem['school_classes'] = '';

                if ($tblPerson = Account::useService()->getPersonAllByAccount($tblAccount)){
                    $tblPerson = current($tblPerson);
                    $UploadItem = $this->getPersonDataExcel($UploadItem, $tblPerson, $tblYear, $Acronym, $TeacherClasses, $TeacherSchools);
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
                    $Item['groupArray'] = '';

                    $Item = $this->getPersonDataExcel($Item, $tblPerson, $tblYear, $Acronym, $TeacherClasses, $TeacherSchools);

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
     * @param array     $TeacherSchools
     *
     * @return bool|array
     */
    private function getPersonDataExcel($Item, TblPerson $tblPerson, TblYear $tblYear, $Acronym, $TeacherClasses, $TeacherSchools)
    {

        $Item['firstname'] = $tblPerson->getFirstSecondName();
        $Item['lastname'] = $tblPerson->getLastName();

        // Rollen
        $tblGroupList = Group::useService()->getGroupAllByPerson($tblPerson);
        $roles = array();
        $groups = array();
        if(isset($tblGroupList)){
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

        $schools = array();
        $StudentSchool = '';
        if(!Consumer::useService()->isSchoolSeparated()){
            // Mandant wird als Schule verwendet
            $SchoolString = $this->getSchoolString($Acronym);
            $schools[] = $SchoolString;
            $StudentSchool = $SchoolString;
        } else {
            // Schulen im Mandanten werden unterschieden
            if ($tblStudent = Student::useService()->getStudentByPerson($tblPerson)){
                $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier(TblStudentTransferType::PROCESS);
                if (($tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                    $tblStudentTransferType))){
                    if (($tblCompany = $tblStudentTransfer->getServiceTblCompany())){
                        if ($tblDivision){
                            // Schule über Schülerakte Company und Klasse (Schulart)
                            if (($tblSchoolType = $tblDivision->getType())){
                                $SchoolTypeString = Type::useService()->getSchoolTypeString($tblSchoolType);
                                $SchoolString = $this->getSchoolString($Acronym, $SchoolTypeString, $tblCompany);
                                $schools[] = $SchoolString;
                                $StudentSchool = $SchoolString;
                            }
                        }
                    }
                }
            }
        }

        if (!empty($schools)){
            $schools = array_unique($schools);
            $Item['schools'] = implode(',', $schools);
        } else {
            if(isset($TeacherSchools[$tblPerson->getId()])){

                $SchoolList = $TeacherSchools[$tblPerson->getId()];
                $SchoolList = array_unique($SchoolList);
                sort($SchoolList);
                $Item['schools'] = implode(',', $SchoolList);
            }
        }

        // Student Search Division
        if ($tblDivision){
            $Item['school_classes'] = $StudentSchool.'-'.$tblDivision->getTblLevel()->getName().$tblDivision->getName();
        } else {
            if(isset($TeacherClasses[$tblPerson->getId()])){
                $ClassList = $TeacherClasses[$tblPerson->getId()];
                foreach($ClassList as &$Class){
                    $Class = $StudentSchool.'-'.$Class;
                }
                sort($ClassList);
                $Item['school_classes'] = implode(',', $ClassList);
            }
        }

        if(($ToPersonList = Mail::useService()->getMailAllByPerson($tblPerson))){
            // try to get Connexion Mail
            foreach($ToPersonList as $tblToPerson){
                if($tblToPerson->isAccountUserAlias()
                && ($tblMail = $tblToPerson->getTblMail())){
                    $Item['mail'] = $tblMail->getAddress();
                    break;
                }
            }
            // again if no result vor Connexion Mail
            if($Item['mail'] === ''){
                foreach($ToPersonList as $tblToPerson){
                    if($tblToPerson->getTblType()->getName() == TblTypeMail::VALUE_BUSINESS
                    && ($tblMail = $tblToPerson->getTblMail())){
                        $Item['mail'] = $tblMail->getAddress();
                        break;
                    }
                }
            }
        }
        return $Item;
    }

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
            $export->setValue($export->getCell($Column, $Row), "Stammgruppe");

            foreach ($AccountData as $Account)
            {
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

    public function downlaodSchoolExcel()
    {

        $Acronym = Account::useService()->getMandantAcronym();
        $SchoolData = array();
        if(!Consumer::useService()->isSchoolSeparated()){
            $Item['OU'] = $this->getSchoolString($Acronym);
            $tblConsumer = Consumer::useService()->getConsumerBySession();
            $Item['Schulname'] = $tblConsumer->getName();
            array_push($SchoolData, $Item);
        } else {
            if(($tblSchoolList = School::useService()->getSchoolAll())){
                foreach($tblSchoolList as $tblSchool){
                    $Item = array();
                    $tblCompany = $tblSchool->getServiceTblCompany();
                    $tblType = $tblSchool->getServiceTblType();
                    if($tblCompany && $tblType){
                        $SchoolTypeString = Type::useService()->getSchoolTypeString($tblType);
//                    $Item['OU'] = $Acronym.$SchoolTypeString.$tblCompany->getId();
                        $Item['OU'] = $this->getSchoolString($Acronym, $SchoolTypeString, $tblCompany);
                        $Item['Schulname'] = $tblCompany->getName();
                        array_push($SchoolData, $Item);
                    }
                }
            }
        }

        if (!empty($SchoolData))
        {

            $fileLocation = Storage::createFilePointer('csv');

            $Row = $Column = 0;
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell($Column++, $Row), "OU");
            $export->setValue($export->getCell($Column, $Row), "Schulname");

            foreach ($SchoolData as $School)
            {
                $Column = 0;
                $Row++;

                $export->setValue($export->getCell($Column++, $Row), $School['OU']);
                $export->setValue($export->getCell($Column, $Row), $School['Schulname']);

            }
            $export->setDelimiter(',');
            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param string          $Acronym
     * @param string          $SchoolTypeString
     * @param TblCompany|null $tblCompany
     *
     * @return int|string
     */
    public function getSchoolString($Acronym, $SchoolTypeString = '', TblCompany $tblCompany = null)
    {

        if(Consumer::useService()->isSchoolSeparated()){
                return $Acronym.$SchoolTypeString.($tblCompany ? $tblCompany->getId() : '1');
        }
        // ToDO Standard nach Wunsch anpassen
        // Schulen werden in Univention in Mandant zusammen gefasst (Standard)
        return $Acronym;
    }
}