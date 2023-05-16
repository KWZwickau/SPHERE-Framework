<?php

namespace SPHERE\Application\Transfer\Untis\Export\Meta;

use DateTime;
use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;

class Service
{
    /**
     * @param string $DivisionCourseId
     *
     * @return bool|FilePointer
     */
    public function createCsv(string $DivisionCourseId = '')
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())
        ) {
            $fileLocation = Storage::createFilePointer('csv');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setDelimiter(';');

            $Row = 0;
            foreach ($tblPersonList as $tblPerson) {
                $birthday = '';
                $gender = '0';
                $mark = '';
                $studentNumber = '';
                if(($tblCommon = Common::useService()->getCommonByPerson($tblPerson))){
                    if(($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())){
                        $birthday = $tblCommonBirthDates->getBirthday();
                        if(($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())){
                            if($tblCommonGender->getName() == 'Männlich'){
                                $gender = '2';
                            } elseif($tblCommonGender->getName() == 'Weiblich') {
                                $gender = '1';
                            }
                        }
                    }
                }
                if (($tblStudent = $tblPerson->getStudent())) {
                    $studentNumber = $tblStudent->getIdentifier();
                }

                $DivisionName = $tblDivisionCourse->getName();

                // todo Jens Ticket Auswahl für den Anzeigenamen erstellen über Formular + Möglichkeit Kennzeichnung "N" zu setzen, steht für nicht drucken
                // Hintergrund aus Datenschutzgründen wird im Beruflichen Gym als Anzeigename die Schülernummer, statt des Schülernamens verwendet
                $displayName = $tblPerson->getLastName() . ' : ' . $tblPerson->getFirstSecondName();
                if (Consumer::useService()->getConsumerBySessionIsConsumer(TblConsumer::TYPE_SACHSEN, 'HOGA')
                    && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                    && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                ) {
                    if ($tblSchoolType->getShortName() == 'BGy') {
                        $displayName = $studentNumber;
                    }
                    if ($tblSchoolType->getShortName() == 'Gy' && $tblStudentEducation->getLevel() == 10) {
                        $mark = "N";
                    }
                }

//                GPU010.txt
//                0  Name
//                1  Langname
//                2  Text
//                3  Beschreibung
//                4  Statistik 1
//                5  Statistik 2
//                6  Kennzeichen
//                7  Vorname
//                8  Schülernummer
//                9 Klasse
//                10 Geschlecht (1 = weiblich, 2 = männlich)
//                11 (Kurs-)Optimierungskennzeichen
//                12 Geburtsdatum JJJJMMTT
//                13 E-Mail Adresse (ab Version 2012)
//                14 Fremdschlüssel (ab Version 2012)

                $export->setValue($export->getCell("0", $Row), utf8_decode($displayName));
                $export->setValue($export->getCell("1", $Row), utf8_decode($tblPerson->getLastName()));
                $export->setValue($export->getCell("2", $Row), "");
                $export->setValue($export->getCell("3", $Row), "");
                $export->setValue($export->getCell("4", $Row), "");
                $export->setValue($export->getCell("5", $Row), "");
                $export->setValue($export->getCell("6", $Row), $mark);
                $export->setValue($export->getCell("7", $Row), utf8_decode($tblPerson->getFirstSecondName()));
                $export->setValue($export->getCell("8", $Row), utf8_decode($studentNumber));
                $export->setValue($export->getCell("9", $Row), utf8_decode($DivisionName));
                $export->setValue($export->getCell("10", $Row), $gender);
                $export->setValue($export->getCell("11", $Row), "");
                $export->setValue($export->getCell("12", $Row), $birthday ? (new DateTime($birthday))->format('Ymd') : "");
                $export->setValue($export->getCell("13", $Row), "");
                $export->setValue($export->getCell("14", $Row), "");

                $Row++;
            }

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }
}