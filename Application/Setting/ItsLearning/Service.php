<?php
namespace SPHERE\Application\Setting\ItsLearning;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
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
                    $Account = $tblAccountList[0];
                }
                $PersonAccountList[$tblPerson->getId()]['Account'] = ($Account
                    ? $Account->getId()
                    : '');
                $PersonAccountList[$tblPerson->getId()]['Identification'] = '';
                $PersonAccountList[$tblPerson->getId()]['AccountName'] = ($Account
                    ? utf8_encode($Account->getUsername())
                    : '');
                $PersonAccountList[$tblPerson->getId()]['FirstName'] = utf8_encode($tblPerson->getFirstName());
                $PersonAccountList[$tblPerson->getId()]['LastName'] = utf8_encode($tblPerson->getLastName());

                // Aktuelle Klasse
                $Level = '';
                $Division = '';

                if(($tblYear = Term::useService()->getYearById($YearId))){
                    $tblDivision = Division::useService()->getDivisionByPersonAndYear($tblPerson, $tblYear);
                    if ($tblDivision && $tblDivision->getTblLevel() && $tblDivision->getTblLevel()->getName() != '') {
                        $Level = $tblDivision->getTblLevel()->getName();
                        $Division = $tblDivision->getDisplayName();
                    }
                }

                $PersonAccountList[$tblPerson->getId()]['Level'] = utf8_encode($Level);
                $PersonAccountList[$tblPerson->getId()]['Division'] = utf8_encode($Division);
                if(($tblStudent = Student::useService()->getStudentByPerson($tblPerson))){
                    $PersonAccountList[$tblPerson->getId()]['Identification'] = utf8_encode($tblStudent->getIdentifierComplete());
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
                                    ? utf8_encode($AccountGuardian->getUsername())
                                    : '');
                                $PersonAccountList[$tblPerson->getId()]['Custody'][$tblPersonGuardian->getId()]['FirstName'] = utf8_encode($tblPersonGuardian->getFirstName());
                                $PersonAccountList[$tblPerson->getId()]['Custody'][$tblPersonGuardian->getId()]['LastName'] = utf8_encode($tblPersonGuardian->getLastName());
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
                $Item = array();
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
                $Item['Id'] = $StudentData['Account'];
                $Item['Identification'] = $StudentData['Identification'];
                $Item['AccountName'] = $StudentData['AccountName'];
                $Item['FirstName'] = $StudentData['FirstName'];
                $Item['LastName'] = $StudentData['LastName'];
                $Item['Level'] = $StudentData['Level'];
                $Item['Division'] = $StudentData['Division'];

                if(isset($StudentData['Custody'])){
                    if(count($StudentData['Custody']) > $countCustody){
                        $countCustody = count($StudentData['Custody']);
                    }
                    $i = 1;
                    foreach($StudentData['Custody'] as $CustodyId => $CustodyData){
                        // Geschwisterkind Angabe, nur wenn Elternaccounts vorhanden sind
                        if(isset($StudentData['Sibling'.$i])){
                            $Item['Sibling'.$i] = $StudentData['Sibling'.$i];
                        } else {
                            //default
                            $Item['Sibling'.$i] = 'Test';
                        }

                        $Item['IdS'.$i] = $CustodyId;
                        $Item['AccountNameS'.$i] = $CustodyData['AccountName'];
                        $Item['FirstNameS'.$i] = $CustodyData['FirstName'];
                        $Item['LastNameS'.$i] = $CustodyData['LastName'];
                        $Item['MailS'.$i] = $CustodyData['Mail'];
                        $i++;
                    }
                }
                array_push($UploadList, $Item);
            }
        }

        if (!empty($UploadList)){
            $fileLocation = Storage::createFilePointer('csv');

            $Row = $Column = 0;
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell($Column++, $Row), "Schüler:ID");
            $export->setValue($export->getCell($Column++, $Row), "Schüler:Nummer");
            $export->setValue($export->getCell($Column++, $Row), "Schüler:Vorname");
            $export->setValue($export->getCell($Column++, $Row), "Schüler:Name");
            $export->setValue($export->getCell($Column++, $Row), "Schüler:Nutzername");
            $export->setValue($export->getCell($Column++, $Row), "Schüler:Jahrgang");
            $export->setValue($export->getCell($Column++, $Row), "Klasse");
            for($i = 1; $i <= $countCustody; $i++){
                $Number = $i;
                if($i == 1){
                    $Number = '';
                }
                $export->setValue($export->getCell($Column++, $Row), "Kind Nr.".$Number);
                $export->setValue($export->getCell($Column++, $Row), "Eltern:ID".$Number);
                $export->setValue($export->getCell($Column++, $Row), "Eltern:Vorname".$Number);
                $export->setValue($export->getCell($Column++, $Row), "Eltern:Name".$Number);
                $export->setValue($export->getCell($Column++, $Row), "Eltern:Nutzername".$Number);
                $export->setValue($export->getCell($Column++, $Row), "Eltern:E-Mail".$Number);
            }
            foreach($UploadList as $Upload){
                $Row++;
                $Column = 0;
                $export->setValue($export->getCell($Column++, $Row), $Upload['Id']);
                $export->setValue($export->getCell($Column++, $Row), $Upload['Identification']);
                $export->setValue($export->getCell($Column++, $Row), $Upload['FirstName']);
                $export->setValue($export->getCell($Column++, $Row), $Upload['LastName']);
                $export->setValue($export->getCell($Column++, $Row), $Upload['AccountName']);
                $export->setValue($export->getCell($Column++, $Row), $Upload['Level']);
                $export->setValue($export->getCell($Column++, $Row), $Upload['Division']);
                for($j = 1; $j <= $countCustody; $j++) {
                    $export->setValue($export->getCell($Column++, $Row), (isset($Upload['Sibling'.$j]) ? $Upload['Sibling'.$j] : ''));
                    $export->setValue($export->getCell($Column++, $Row), (isset($Upload['IdS'.$j]) ? $Upload['IdS'.$j] : ''));
                    $export->setValue($export->getCell($Column++, $Row), (isset($Upload['FirstNameS'.$j]) ? $Upload['FirstNameS'.$j] : ''));
                    $export->setValue($export->getCell($Column++, $Row), (isset($Upload['LastNameS'.$j]) ? $Upload['LastNameS'.$j] : ''));
                    $export->setValue($export->getCell($Column++, $Row), (isset($Upload['AccountNameS'.$j]) ? $Upload['AccountNameS'.$j] : ''));
                    $export->setValue($export->getCell($Column++, $Row), (isset($Upload['MailS'.$j]) ? $Upload['MailS'.$j] : ''));
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
                $Item = array();
                // Fehler werden bereinigt
                if(!$TeacherData['AccountName']){
                    $TeacherData = false;
                    continue;
                }
                $Item['Id'] = $TeacherData['Account'];
                $Item['AccountName'] = $TeacherData['AccountName'];
                $Item['FirstName'] = $TeacherData['FirstName'];
                $Item['LastName'] = $TeacherData['LastName'];
                array_push($UploadList, $Item);
            }
        }

        if (!empty($UploadList)){
            $fileLocation = Storage::createFilePointer('csv');

            $Row = $Column = 0;
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell($Column++, $Row), "ID");
            $export->setValue($export->getCell($Column++, $Row), "Vorname");
            $export->setValue($export->getCell($Column++, $Row), "Name");
            $export->setValue($export->getCell($Column, $Row), "Nutzername");

            foreach($UploadList as $Upload) {
                $Row++;
                $Column = 0;
                $export->setValue($export->getCell($Column++, $Row), $Upload['Id']);
                $export->setValue($export->getCell($Column++, $Row), $Upload['FirstName']);
                $export->setValue($export->getCell($Column++, $Row), $Upload['LastName']);
                $export->setValue($export->getCell($Column, $Row), $Upload['AccountName']);
            }

            $export->setDelimiter(',');
            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return null;
    }
}