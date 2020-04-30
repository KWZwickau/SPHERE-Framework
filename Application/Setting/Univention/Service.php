<?php
namespace SPHERE\Application\Setting\Univention;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferType;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\Application\Setting\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Application\Setting\Univention\Service\Data;
use SPHERE\Application\Setting\Univention\Service\Entity\TblUnivention;
use SPHERE\Application\Setting\Univention\Service\Setup;
use SPHERE\Application\Setting\User\Account\Account as AccountUser;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Fitting\Element;

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
        }
        return (!empty($tblAccountList) ? $tblAccountList : false);
    }

    /**
     * @param Element $tbl
     * @param array   $DateCompare
     */
    public function setDateList(Element $tbl, &$DateCompare)
    {

        if (($update = $tbl->getEntityUpdate())){
            $DateCompare[] = $update;
        } else {
            $DateCompare[] = $tbl->getEntityCreate();
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

        $tblYear = Term::useService()->getYearByNow();
        if ($tblYear){
            $tblYear = current($tblYear);
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
                $UploadItem['groupArray'] = '';

                $UploadItem['password'] = '';
//                $UploadItem['password'] = $tblAccount->getPassword(); // ??
                $UploadItem['school_classes'] = '';

                if ($tblPerson = Account::useService()->getPersonAllByAccount($tblAccount)){
                    $tblPerson = current($tblPerson);
                    $UploadItem['firstname'] = $tblPerson->getFirstSecondName();
                    $UploadItem['lastname'] = $tblPerson->getLastName();
                } else {
                    // Ohne Person kein sinnvoller Account
                    continue;
                }
                // Rollen
                $tblGroupList = Group::useService()->getGroupAllByPerson($tblPerson);
                $roles = array();
                $groups = array();
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
                if(empty($roles)){
                    // Accounts die nicht/nicht mehr zu den 3 Rollen gehören sollen entfernt werden
                    continue;
                }
                if(!empty($groups)){
                    $UploadItem['groupArray'] = $groups;
                }
                $UploadItem['roles'] = implode(',', $roles);


                $tblDivision = false;
                if ($tblYear){
                    ($tblDivision = Division::useService()->getDivisionByPersonAndYear($tblPerson, $tblYear));
                }

                $schools = array();
                $StudentSchool = '';
                if ($tblStudent = Student::useService()->getStudentByPerson($tblPerson)){
                    $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier(TblStudentTransferType::PROCESS);
                    if (($tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                        $tblStudentTransferType))){
                        if (($tblCompany = $tblStudentTransfer->getServiceTblCompany())){
                            if ($tblDivision){
                                // Schule über Schülerakte Company und Klasse (Schulart)
                                if (($tblSchoolType = $tblDivision->getType())){
                                    $SchoolTypeString = Type::useService()->getSchoolTypeString($tblSchoolType);
                                    $SchoolString = $Acronym.$SchoolTypeString.$tblCompany->getId();
                                    $schools[] = $SchoolString;
                                    $StudentSchool = $SchoolString;
                                }
                            }
                        }
                    }
                } else {
                    // keine Schüler -> Accunt bekommt alle Schulen des Mandanten
                    if(($tblSchoolList =  School::useService()->getSchoolAll())){
                        foreach($tblSchoolList as $tblSchool){
                            $tblCompany = $tblSchool->getServiceTblCompany();
                            $tblSchoolType = $tblSchool->getServiceTblType();
                            if($tblCompany && $tblSchoolType){
                                $SchoolTypeString = Type::useService()->getSchoolTypeString($tblSchoolType);
                                $SchoolString = $Acronym.$SchoolTypeString.$tblCompany->getId();
                                $schools[] = $SchoolString;
                                // ToDO Schoolstring aus Array
//                                $schools[] = $schoolList[$schoolString];
                            }
                        }
                    }
                }
                if (!empty($schools)){
                    $UploadItem['schools'] = implode(',', $schools);
                }

                // Student Search Division
                if ($tblDivision){
                    $UploadItem['school_classes'] = $StudentSchool.'-'.$tblDivision->getTblLevel()->getName().$tblDivision->getName();
                }

                array_push($UploadToAPI, $UploadItem);
            }
        }
        $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT);
        $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
        if($tblPersonList && $StudentWithoutAccount){
            foreach($tblPersonList as $tblPerson){
                if(Account::useService()->getAccountAllByPerson($tblPerson)){
                    // ignore students with account
                    continue;
                }
                $Item['name'] = '';
                $Item['firstname'] = $tblPerson->getFirstName();
                $Item['lastname'] = $tblPerson->getLastName();
                $Item['record_uid'] = '';
                $Item['source_uid'] = $Acronym.'-';
                $Item['roles'] = '';
                $Item['schools'] = '';
                $Item['password'] = '';
                $Item['school_classes'] = '';
                $Item['groupArray'] = '';

                // Rollen
                $tblGroupList = Group::useService()->getGroupAllByPerson($tblPerson);
                $roles = array();
                $groups = array();
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
                $Item['roles'] = implode(',', $roles);
                if(!empty($groups)){
                    $Item['groupArray'] = $groups;
                }

                $tblDivision = false;
                if ($tblYear){
                    // Student Search Division
                    $tblDivision = Division::useService()->getDivisionByPersonAndYear($tblPerson, $tblYear);
                }

                // Schulen (alle) //ToDO Schulstring erzeugen
                $schools = array();
                $StudentSchool = '';
                if ($tblStudent = Student::useService()->getStudentByPerson($tblPerson)){
                    $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier(TblStudentTransferType::PROCESS);
                    if (($tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                        $tblStudentTransferType))){
                        if (($tblCompany = $tblStudentTransfer->getServiceTblCompany())){
                            if ($tblDivision){
                                // Schule über Schülerakte Company und Klasse (Schulart)
                                if (($tblSchoolType = $tblDivision->getType())){
                                    $SchoolTypeString = Type::useService()->getSchoolTypeString($tblSchoolType);
                                    $SchoolString = $Acronym.$SchoolTypeString.$tblCompany->getId();
                                    $schools[] = $SchoolString;
                                    $StudentSchool = $SchoolString;
                                }
                            }
                        }
                    }
                }
                if (!empty($schools)){
                    $Item['schools'] = implode(',', $schools);
                }

                if($tblDivision){
                    $Item['school_classes'] = $StudentSchool.'-'.$tblDivision->getTblLevel()->getName().$tblDivision->getName();
                }
                array_push($UploadToAPI, $Item);
            }
        }

        return (!empty($UploadToAPI) ? $UploadToAPI : false);
    }

    public function downlaodAccountExcel()
    {

        $AccountData = $this->getExportAccount(true);

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
                $export->setValue($export->getCell($Column++, $Row), '');
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
        if(($tblSchoolList = School::useService()->getSchoolAll())){
            foreach($tblSchoolList as $tblSchool){
                $Item = array();
                $tblCompany = $tblSchool->getServiceTblCompany();
                $tblType = $tblSchool->getServiceTblType();
                if($tblCompany && $tblType){
                    $SchoolTypeString = Type::useService()->getSchoolTypeString($tblType);
                    $Item['OU'] = $Acronym.$SchoolTypeString.$tblCompany->getId();
                    $Item['Schulname'] = $tblCompany->getName();
                    array_push($SchoolData, $Item);
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
}