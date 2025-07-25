<?php
namespace SPHERE\Application\Reporting\Custom\Hoga\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Component\Parameter\Repository\PaperOrientationParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Reporting\Standard\Person\Person;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Debugger;

class Service extends Extension
{

    /**
     * @return array
     */
    public function createCleverReachList()
    {

        $TableContent = array();
        $tblGroupCustody = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_CUSTODY);
        if($tblGroupCustody && ($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroupCustody))) {
            $countChildMax = $this->getChildMaxCount($tblPersonList);
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, $countChildMax) {
                $item = array();
                // Content
                $item['Salutation'] = $tblPerson->getSalutation();
                $item['LastName'] = $tblPerson->getLastName();
                $item['FirstName'] = $tblPerson->getFirstSecondName();
                for($i = 1; $i <= $countChildMax; $i++){
                    $item['DivisionChild'.$i] = '';
                    $item['SchoolTypeChild'.$i] = '';
                    $item['LastNameChild'.$i] = '';
                    $item['FirstNameChild'.$i] = '';
                    $item['GenderChild'.$i] = '';
                    $item['SecondLanguageChild'.$i] = '';
                    $item['ThirdLanguageChild'.$i] = '';
                    $item['ReligionChild'.$i] = ''; // auch Ethik
                    $item['ProfilChild'.$i] = '';
                }
                // Data Students
                $tblRelationshipType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_GUARDIAN);
                if($tblRelationshipType && ($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblRelationshipType))){
                    $countChild = 0;
                    foreach($tblRelationshipList as $tblRelationship){
                        if(!($tblPersonStudent = $tblRelationship->getServiceTblPersonTo())){
                            continue;
                        }
                        $countChild++;
                        $item['LastNameChild'.$countChild] = $tblPersonStudent->getLastName();
                        $item['FirstNameChild'.$countChild] = $tblPersonStudent->getFirstName();
                        if($tblCommonGender = $tblPersonStudent->getGender()){
                            $item['GenderChild'.$countChild] = $tblCommonGender->getName();
                        }
                        // StudentEducation
                        if(($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPersonStudent))){
                            if(($tblDivisionCourse = $tblStudentEducation->getTblDivision())){
                                $item['DivisionChild'.$countChild] = $tblDivisionCourse->getName();
                            } elseif(($tblDivisionCourse = $tblStudentEducation->getTblCoreGroup())) {
                                $item['DivisionChild'.$countChild] = $tblDivisionCourse->getName();
                            }
                            if(($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())){
                                $item['SchoolTypeChild'.$countChild] = $tblSchoolType->getName();
                            }
                        }
                        // Student
                        if(($tblStudent = $tblPersonStudent->getStudent())){
                            for ($rank = 2; $rank <= 3; $rank++) {
                                if(($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
                                    && ($tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier($rank))
                                    && ($tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking($tblStudent, $tblStudentSubjectType, $tblStudentSubjectRanking))){
                                    $isFromTill = false;
                                    if(($fromLevel = $tblStudentSubject->getLevelFrom())){
                                        $fromLevel = 'ab '.$fromLevel.'. ';
                                        $isFromTill =  true;
                                    }
                                    if(($tillLevel = $tblStudentSubject->getLevelTill())){
                                        $tillLevel = ($isFromTill? ' ': '').'bis '.$tillLevel.'.';
                                        $isFromTill =  true;
                                    }
                                    $stringContent = '';
                                    if(($subject = $tblStudentSubject->getServiceTblSubject())){
                                        $stringContent = $subject->getName().($isFromTill ? '('.$fromLevel.$tillLevel.')' : '');
                                    }

                                    if($rank == 2){
                                        $item['SecondLanguageChild'.$countChild] = $stringContent;
                                    } elseif($rank == 3){
                                        $item['ThirdLanguageChild'.$countChild] = $stringContent;
                                    }
                                }
                            }
                            if(($tblSubjectReligion = $tblStudent->getTblSubjectReligion())){
                                $item['ReligionChild'.$countChild] = $tblSubjectReligion->getName(); // auch Ethik
                            }
                            if(($tblSubjectProfile = $tblStudent->getTblSubjectProfile())){
                                $item['ProfilChild'.$countChild] = $tblSubjectProfile->getName(); // auch Ethik
                            }
                        }
                    }
                }

                // Alle E-Mails
                $item['Mail'] = '';
                $MailList = array();
                if(($tblToPersonMailList = Mail::useService()->getMailAllByPerson($tblPerson))){
                    foreach($tblToPersonMailList as $tblToPersonMail){
                        if(($tblMail = $tblToPersonMail->getTblMail())){
                            $MailList[] = $tblMail->getAddress();
                        }
                    }
                }
                // Alle Daten für jede E-Mail erzeugen
                if(!empty($MailList)){
                    foreach($MailList as $Mail){
                        $item['Mail'] = $Mail;
                        array_push($TableContent, $item);
                    }
                }
            });
        }
        return $TableContent;
    }

    /**
     * @param TblPerson[] $tblPersonList
     *
     * @return int
     */
    public function getChildMaxCount(array $tblPersonList)
    {
        $countChildMax = 3;
        $tblRelationshipType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_GUARDIAN);
        array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$countChildMax, $tblRelationshipType) {
            if(($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblRelationshipType))
                && count($tblRelationshipList) > $countChildMax){
                $countChildMax = count($tblRelationshipList);
            }
        });
        return $countChildMax;
    }

    /**
     * @param FilePointer $fileLocation
     * @param array       $TableContent
     *
     * @return FilePointer
     */
    public function createCleverReachExcel(FilePointer $fileLocation, array $TableContent): FilePointer
    {

        $ChildMaxCount = 3;
        $tblGroupCustody = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_CUSTODY);
        if($tblGroupCustody && ($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroupCustody))){
            $ChildMaxCount = $this->getChildMaxCount($tblPersonList);
        }
        /** @var PhpExcel $export */
        $column = 0;
        $row = 0;
        $export = Document::getDocument($fileLocation->getFileLocation());

        $export->setValue($export->getCell($column++, $row), "E-Mail");
        $export->setValue($export->getCell($column++, $row), "Anrede");
        $export->setValue($export->getCell($column++, $row), "Name");
        $export->setValue($export->getCell($column++, $row), "Vorname");
        for($i = 1; $i <= $ChildMaxCount; $i++){
            $export->setValue($export->getCell($column++, $row), "Klasse des Kindes ".$i);
            $export->setValue($export->getCell($column++, $row), "Schulart des Kindes ".$i);
            $export->setValue($export->getCell($column++, $row), "Name des Kindes ".$i);
            $export->setValue($export->getCell($column++, $row), "Vorname des Kindes ".$i);
            $export->setValue($export->getCell($column++, $row), "Geschlecht des Kindes ".$i);
            $export->setValue($export->getCell($column++, $row), "ggf. 2. Fremdsprache des Kindes ".$i." (nur wenn das Kind diese auch aktuell besucht)");
            $export->setValue($export->getCell($column++, $row), "ggf. 3. Fremdsprache des Kindes ".$i." (nur wenn das Kind diese auch aktuell besucht)");
            $export->setValue($export->getCell($column++, $row), "Ethik o. Religion Kind ".$i); // auch Ethik
            $export->setValue($export->getCell($column++, $row), "ggf. Profil des Kindes ".$i);
        }

        foreach ($TableContent as $Data) {
            $column = 0;
            $row++;
            $export->setValue($export->getCell($column++, $row), $Data['Mail']);
            $export->setValue($export->getCell($column++, $row), $Data['Salutation']);
            $export->setValue($export->getCell($column++, $row), $Data['LastName']);
            $export->setValue($export->getCell($column++, $row), $Data['FirstName']);
            for($j = 1; $j <= $ChildMaxCount; $j++){
                $export->setValue($export->getCell($column++, $row), $Data['DivisionChild'.$j]);
                $export->setValue($export->getCell($column++, $row), $Data['SchoolTypeChild'.$j]);
                $export->setValue($export->getCell($column++, $row), $Data['LastNameChild'.$j]);
                $export->setValue($export->getCell($column++, $row), $Data['FirstNameChild'.$j]);
                $export->setValue($export->getCell($column++, $row), $Data['GenderChild'.$j]);
                $export->setValue($export->getCell($column++, $row), $Data['SecondLanguageChild'.$j]);
                $export->setValue($export->getCell($column++, $row), $Data['ThirdLanguageChild'.$j]);
                $export->setValue($export->getCell($column++, $row), $Data['ReligionChild'.$j]);
                $export->setValue($export->getCell($column++, $row), $Data['ProfilChild'.$j]);
            }
        }
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }
}