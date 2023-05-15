<?php
namespace SPHERE\Application\Setting\ItsLearning;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\System\Extension\Extension;

/**
 * Class Service
 * @package SPHERE\Application\Transfer\ItsLearning\Import
 */
class Service extends Extension
{

    /**
     * @return array
     */
    public function getStudentCustodyAccountList($YearId = null): array
    {
        $PersonAccountList = array();
        $SiblingCount = array();
        $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT);
        if(($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))){
            foreach($tblPersonList as $tblPerson){
                $tblPersonToList = array();
                $Account = false;
                if(($tblAccountList = Account::useService()->getAccountAllByPerson($tblPerson))){
                    $Account = current($tblAccountList);
                }
                $PersonAccountList[$tblPerson->getId()]['Account'] = ($Account
                    ? $Account->getId()
                    : '');
                $PersonAccountList[$tblPerson->getId()]['Identification'] = '';
                $PersonAccountList[$tblPerson->getId()]['AccountName'] = ($Account
                    ? $Account->getUsername()
                    : '');
                $PersonAccountList[$tblPerson->getId()]['FirstName'] = $tblPerson->getFirstName();
                $PersonAccountList[$tblPerson->getId()]['LastName'] = $tblPerson->getLastName();

                // Aktuelle Klasse
                $level = '';
                $Division = '';
                $DivisionDisplay = '';
                $SchoolType = '';

                if(($tblYear = Term::useService()->getYearById($YearId))
                && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))){
                    $SchoolType = $tblStudentEducation->getServiceTblSchoolType()->getName();
                    if($SchoolType == 'Mittelschule / Oberschule'){
                        $SchoolType = 'Oberschule';
                    }
                    $level = $tblStudentEducation->getLevel();
                    if(($tblDivisionCourse = $tblStudentEducation->getTblDivision())) {
                        $Division = $tblDivisionCourse->getName();
                        $DivisionDisplay = $tblDivisionCourse->getDisplayName();
                    }
                }

                $PersonAccountList[$tblPerson->getId()]['Level'] = $level;
                $PersonAccountList[$tblPerson->getId()]['Division'] = $Division;
                $PersonAccountList[$tblPerson->getId()]['DivisionDisplay'] = $DivisionDisplay;
                $PersonAccountList[$tblPerson->getId()]['SchoolType'] = $SchoolType;
                if(($tblStudent = Student::useService()->getStudentByPerson($tblPerson))){
                    $PersonAccountList[$tblPerson->getId()]['Identification'] = $tblStudent->getIdentifierComplete();
                }

                // Sorgeberechtigte, Bevollmächtigte, Vormund
                $tblTypeList = array();
                if(($tblRelationshipType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_GUARDIAN))){
                    $tblTypeList[] = $tblRelationshipType;
                }
                if(($tblRelationshipType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_AUTHORIZED))){
                    $tblTypeList[] = $tblRelationshipType;
                }
                if(($tblRelationshipType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_GUARDIAN_SHIP))){
                    $tblTypeList[] = $tblRelationshipType;
                }
                foreach($tblTypeList as $tblType){
                    if(($tblPersonToTempList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblType))){
                        foreach($tblPersonToTempList as $tblPersonToTemp){
                            $tblPersonToList[$tblPersonToTemp->getId()] = $tblPersonToTemp;
                        }
                    }
                }
                $PersonAccountList[$tblPerson->getId()]['Sibling1'] = 1;
                $PersonAccountList[$tblPerson->getId()]['Sibling2'] = 1;
                if(!empty($tblPersonToList)){
                    foreach($tblPersonToList as $tblPersonTo){
                        $tblPersonGuardian = $tblPersonTo->getServiceTblPersonFrom();
                        if($tblPersonGuardian){
                            // Berücksichtigung nur bei vorhandenem Account
                            if(($tblAccountList = Account::useService()->getAccountAllByPerson($tblPersonGuardian))){
                                $AccountGuardian = $tblAccountList[0];
                                // Kind Zählung anhand des Sorgeberechtigten
                                if(isset($SiblingCount[$tblPersonGuardian->getId()])){
                                    $SiblingCount[$tblPersonGuardian->getId()] += 1;
                                    if($tblPersonTo->getRanking() == 1){
                                        $PersonAccountList[$tblPerson->getId()]['Sibling1'] = $SiblingCount[$tblPersonGuardian->getId()];
                                    } elseif($tblPersonTo->getRanking() == 2){
                                        $PersonAccountList[$tblPerson->getId()]['Sibling2'] = $SiblingCount[$tblPersonGuardian->getId()];
                                    } elseif($tblPersonTo->getRanking() == 3){
                                        $PersonAccountList[$tblPerson->getId()]['Sibling3'] = $SiblingCount[$tblPersonGuardian->getId()];
                                    }
                                } else {
                                    $SiblingCount[$tblPersonGuardian->getId()] = 1;
                                }

                                $PersonAccountList[$tblPerson->getId()]['Custody'][$tblPersonGuardian->getId()]['Account'] = ($AccountGuardian
                                    ? $AccountGuardian->getId()
                                    : '');
                                $PersonAccountList[$tblPerson->getId()]['Custody'][$tblPersonGuardian->getId()]['AccountName'] = ($AccountGuardian
                                    ? $AccountGuardian->getUsername()
                                    : '');
                                $PersonAccountList[$tblPerson->getId()]['Custody'][$tblPersonGuardian->getId()]['FirstName'] = $tblPersonGuardian->getFirstName();
                                $PersonAccountList[$tblPerson->getId()]['Custody'][$tblPersonGuardian->getId()]['LastName'] = $tblPersonGuardian->getLastName();
                                $PersonAccountList[$tblPerson->getId()]['Custody'][$tblPersonGuardian->getId()]['Mail'] = '';
                                if(($tblPersonToMailList = Mail::useService()->getMailAllByPerson($tblPersonGuardian))){
                                    foreach($tblPersonToMailList as $tblPersonToMail){
                                        if(($tblMail = $tblPersonToMail->getTblMail()) && $tblPersonToMail->getTblType()->getName() == 'Privat'){
                                            $PersonAccountList[$tblPerson->getId()]['Custody'][$tblPersonGuardian->getId()]['Mail'] = $tblMail->getAddress();
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $PersonAccountList;
    }

    /**
     * @return false|FilePointer
     */
    public function downloadStudentCustodyCSV($Year): ?FilePointer
    {

        $countCustody = 0;
        $UploadList = array();
        // Maximale spalten Sorgeberechtigte
        if(($StudentAccountList = $this->getStudentCustodyAccountList($Year))){
            foreach($StudentAccountList as $PersonId => &$StudentData){
                $item = array();
                // Fehler werden bereinigt
                if(!$StudentData['Account']){
                    $StudentData = false;
                    continue;
                }
                if(!$StudentData['Level']){
                    $StudentData = false;
                    continue;
                }
                if(!$StudentData['Division']){
                    $StudentData = false;
                    continue;
                }
                $item['Id'] = $StudentData['Account'];
                $item['Identification'] = $StudentData['Identification'];
                $item['AccountName'] = $StudentData['AccountName'];
                $item['FirstName'] = $StudentData['FirstName'];
                $item['LastName'] = $StudentData['LastName'];
                $item['Level'] = $StudentData['Level'];
                $item['Division'] = $StudentData['Division'];
                $item['DivisionDisplay'] = $StudentData['DivisionDisplay'];
                $item['SchoolType'] = $StudentData['SchoolType'];

                if(isset($StudentData['Custody'])){
                    if(count($StudentData['Custody']) > $countCustody){
                        $countCustody = count($StudentData['Custody']);
                    }
                    $i = 1;
                    foreach($StudentData['Custody'] as $CustodyId => $CustodyData){
                        // Geschwisterkind Angabe, nur wenn Elternaccounts vorhanden sind
                        if(isset($StudentData['Sibling'.$i])){
                            $item['Sibling'.$i] = $StudentData['Sibling'.$i];
                        } else {
                            //default
                            $item['Sibling'.$i] = 'Test';
                        }

                        $item['IdS'.$i] = $CustodyId;
                        $item['AccountNameS'.$i] = $CustodyData['AccountName'];
                        $item['FirstNameS'.$i] = $CustodyData['FirstName'];
                        $item['LastNameS'.$i] = $CustodyData['LastName'];
                        $item['MailS'.$i] = $CustodyData['Mail'];
                        $i++;
                    }
                }
                array_push($UploadList, $item);
            }
        }

        if (!empty($UploadList)){
            $fileLocation = Storage::createFilePointer('csv');

            $row = $column = 0;
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell($column++, $row), "Schüler:ID");
            $export->setValue($export->getCell($column++, $row), "Schüler:Nummer");
            $export->setValue($export->getCell($column++, $row), "Schüler:Vorname");
            $export->setValue($export->getCell($column++, $row), "Schüler:Name");
            $export->setValue($export->getCell($column++, $row), "Schüler:Nutzername");
            $export->setValue($export->getCell($column++, $row), "Schüler:Jahrgang");
            $export->setValue($export->getCell($column++, $row), "Klasse");
            $export->setValue($export->getCell($column++, $row), "Klasse mit Beschreibung");
            $export->setValue($export->getCell($column++, $row), "Schulart");
            for($i = 1; $i <= $countCustody; $i++){
                $number = $i;
                if($i == 1){
                    $number = '';
                }
                $export->setValue($export->getCell($column++, $row), "Kind Nr.".$number);
                $export->setValue($export->getCell($column++, $row), "Eltern:ID".$number);
                $export->setValue($export->getCell($column++, $row), "Eltern:Vorname".$number);
                $export->setValue($export->getCell($column++, $row), "Eltern:Name".$number);
                $export->setValue($export->getCell($column++, $row), "Eltern:Nutzername".$number);
                $export->setValue($export->getCell($column++, $row), "Eltern:E-Mail".$number);
            }
            foreach($UploadList as $Upload){
                $row++;
                $column = 0;
                $export->setValue($export->getCell($column++, $row), $Upload['Id']);
                $export->setValue($export->getCell($column++, $row), $Upload['Identification']);
                $export->setValue($export->getCell($column++, $row), $Upload['FirstName']);
                $export->setValue($export->getCell($column++, $row), $Upload['LastName']);
                $export->setValue($export->getCell($column++, $row), $Upload['AccountName']);
                $export->setValue($export->getCell($column++, $row), $Upload['Level']);
                $export->setValue($export->getCell($column++, $row), $Upload['Division']);
                $export->setValue($export->getCell($column++, $row), $Upload['DivisionDisplay']);
                $export->setValue($export->getCell($column++, $row), $Upload['SchoolType']);
                for($j = 1; $j <= $countCustody; $j++) {
                    $export->setValue($export->getCell($column++, $row), (isset($Upload['Sibling'.$j]) ? $Upload['Sibling'.$j] : ''));
                    $export->setValue($export->getCell($column++, $row), (isset($Upload['IdS'.$j]) ? $Upload['IdS'.$j] : ''));
                    $export->setValue($export->getCell($column++, $row), (isset($Upload['FirstNameS'.$j]) ? $Upload['FirstNameS'.$j] : ''));
                    $export->setValue($export->getCell($column++, $row), (isset($Upload['LastNameS'.$j]) ? $Upload['LastNameS'.$j] : ''));
                    $export->setValue($export->getCell($column++, $row), (isset($Upload['AccountNameS'.$j]) ? $Upload['AccountNameS'.$j] : ''));
                    $export->setValue($export->getCell($column++, $row), (isset($Upload['MailS'.$j]) ? $Upload['MailS'.$j] : ''));
                }
            }

            $export->setDelimiter(',');
            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return null;
    }

    /**
     * @return array
     */
    public function getTeacherAccountList(): array
    {

        $PersonAccountList = array();
        $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_TEACHER);
        $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
        foreach($tblPersonList as $tblPerson){
            $personId = $tblPerson->getId();
            $PersonAccountList[$personId]['FirstName'] = $tblPerson->getFirstName();
            $PersonAccountList[$personId]['LastName'] = $tblPerson->getLastName();
            $PersonAccountList[$personId]['Account'] = '';
            $PersonAccountList[$personId]['AccountName'] = '';
            if($tblAccountList = Account::useService()->getAccountAllByPerson($tblPerson)){
                $tblAccount = $tblAccountList[0];
                $PersonAccountList[$personId]['Account'] = $tblAccount->getId();
                $PersonAccountList[$personId]['AccountName'] = $tblAccount->getUsername();
            }
        }

        return $PersonAccountList;
    }

    /**
     * @return false|FilePointer
     */
    public function downloadTeacherCSV(): ?FilePointer
    {

        $countCustody = 0;
        $UploadList = array();
        if(($TeacherAccountList = $this->getTeacherAccountList())){
            foreach($TeacherAccountList as $PersonId => &$TeacherData){
                $item = array();
                // Fehler werden bereinigt
                if(!$TeacherData['AccountName']){
                    $TeacherData = false;
                    continue;
                }
                $item['Id'] = $TeacherData['Account'];
                $item['AccountName'] = $TeacherData['AccountName'];
                $item['FirstName'] = $TeacherData['FirstName'];
                $item['LastName'] = $TeacherData['LastName'];
                array_push($UploadList, $item);
            }
        }

        if (!empty($UploadList)){
            $fileLocation = Storage::createFilePointer('csv');

            $row = $column = 0;
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell($column++, $row), "ID");
            $export->setValue($export->getCell($column++, $row), "Vorname");
            $export->setValue($export->getCell($column++, $row), "Name");
            $export->setValue($export->getCell($column, $row), "Nutzername");

            foreach($UploadList as $Upload) {
                $row++;
                $column = 0;
                $export->setValue($export->getCell($column++, $row), $Upload['Id']);
                $export->setValue($export->getCell($column++, $row), $Upload['FirstName']);
                $export->setValue($export->getCell($column++, $row), $Upload['LastName']);
                $export->setValue($export->getCell($column, $row), $Upload['AccountName']);
            }

            $export->setDelimiter(',');
            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return null;
    }
}