<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 01.04.2016
 * Time: 07:50
 */

namespace SPHERE\Application\Transfer\Import\Herrnhut;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonInformation;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\System\Extension\Repository\Debugger;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Service
{
    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null $File
     *
     * @return IFormInterface|Danger|string
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createStudentsFromFile(
        IFormInterface $Form = null,
        UploadedFile $File = null
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $File) {
            return $Form;
        }

        if (null !== $File) {
            if ($File->getError()) {
                $Form->setError('File', 'Fehler');
            } else {

                /**
                 * Prepare
                 */
                $File = $File->move($File->getPath(),
                    $File->getFilename() . '.' . $File->getClientOriginalExtension());

                /**
                 * Read
                 */
                //$File->getMimeType()
                /** @var PhpExcel $Document */
                $Document = Document::getDocument($File->getPathname());
                if (!$Document instanceof PhpExcel) {
                    $Form->setError('File', 'Fehler');
                    return $Form;
                }

                $X = $Document->getSheetColumnCount();
                $Y = $Document->getSheetRowCount();

                /**
                 * Header -> Location
                 */
                $Location = array(
                    'Kl.' => null,
                    'Aufnahme am' => null,
                    'Name' => null,
                    'Vorname' => null,
                    'Geschl.' => null,
                    'FS1' => null,
                    'FS2' => null,
                    'FS3' => null,
                    'Profil' => null,
                    'Förderschwerpunkt' => null,
                    'Förderung Hinweise' => null,
                    'Geburtsd.' => null,
                    'Geburtsort' => null,
                    'Straße' => null,
                    'Plz' => null,
                    'Wohnort' => null,
                    'Ortsteil' => null,
                    'Konfession' => null,
                    'privat' => null,
                    'privat 2' => null,
                    'Mutter mobil' => null,
                    'Vater mobil' => null,
                    'Notfall 1' => null,
                    'Notfall 2' => null,
                    'E-Mail' => null,
                    'Sorg1 Name' => null,
                    'Sorg1 Vorname' => null,
                    'Sorg2 Name' => null,
                    'Sorg2 Vorname' => null,
                    'Einsteigestelle' => null,
                    'Beförderung Hinweise' => null,
                    'Verkehrsmittel' => null,
                    'Religionsunterricht' => null,
                    'Email1' => null,
                    'Email2' => null,
                    'Fax' => null,
                    'Abgang am' => null,
                    'abg. Schule ID' => null,
                    'Einschulung am' => null,
                    'Einschulungsart Zusatz' => null,
                    'Geschw.' => null,
                    'Schüler_Integr_Förderschüler' => null,
                    'Landkreis' => null,
                    'letzte Schulart' => null,
                    'FS1 von' => null,
                    'FS1 bis' => null,
                    'FS2 von' => null,
                    'FS2 bis' => null,
                    'FS3 von' => null,
                    'FS3 bis' => null,
                );

                for ($RunX = 0; $RunX < $X; $RunX++) {
                    $Value = trim($Document->getValue($Document->getCell($RunX, 0)));
                    if (array_key_exists($Value, $Location)) {
                        $Location[$Value] = $RunX;
                    }
                }

                /**
                 * Import
                 */
                if (!in_array(null, $Location, true)) {
                    $countStudent = 0;
                    $countFather = 0;
                    $countMother = 0;
                    $countFatherExists = 0;
                    $countMotherExists = 0;

                    $error = array();
                    for ($RunY = 1; $RunY < $Y; $RunY++) {

                        // Student
                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
                        if ($firstName === '' || $lastName === '') {
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler wurde nicht hinzugefügt, da er keinen Vornamen und/oder Namen besitzt.';
                        } else {

                            $tblPerson = Person::useService()->insertPerson(
                                Person::useService()->getSalutationById(3),    //Schüler
                                '',
                                $firstName,
                                '',
                                $lastName,
                                array(
                                    0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                    1 => Group::useService()->getGroupByMetaTable('STUDENT'),
                                )
                            );

                            if ($tblPerson === false) {
                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler konnte nicht angelegt werden.';
                            } else {
                                $countStudent++;

                                $cityCode = str_pad(
                                    trim($Document->getValue($Document->getCell($Location['Plz'], $RunY))),
                                    5,
                                    "0",
                                    STR_PAD_LEFT
                                );

                                $cityName = trim($Document->getValue($Document->getCell($Location['Wohnort'], $RunY)));
                                $cityDistrict = trim($Document->getValue($Document->getCell($Location['Ortsteil'],
                                    $RunY)));

                                $gender = trim($Document->getValue($Document->getCell($Location['Geschl.'], $RunY)));
                                if ($gender == 'm') {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_MALE;
                                } elseif ($gender == 'w') {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_FEMALE;
                                } else {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_NULL;
                                }

                                Common::useService()->insertMeta(
                                    $tblPerson,
                                    trim($Document->getValue($Document->getCell($Location['Geburtsd.'],
                                        $RunY))),
                                    trim($Document->getValue($Document->getCell($Location['Geburtsort'], $RunY))),
                                    $gender,
                                    '',
                                    trim($Document->getValue($Document->getCell($Location['Konfession'], $RunY))),
                                    TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                    '',
                                    ''
                                );

                                // division
                                $tblDivision = false;
                                $year = 15;
                                $division = trim($Document->getValue($Document->getCell($Location['Kl.'],
                                    $RunY)));
                                if (stripos($division, 'A') !== false || stripos($division, 'U') !== false){
                                    $tblInactiveGroup = Group::useService()->insertGroup('Inaktive Schüler');
                                    if ($tblInactiveGroup) {
                                        Group::useService()->addGroupPerson($tblInactiveGroup, $tblPerson);
                                    }
                                } else {
                                    $tblYear = Term::useService()->insertYear('20' . $year . '/' . ($year + 1));
                                    if ($tblYear) {
                                        $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);
                                        if (!$tblPeriodList) {
                                            // firstTerm
                                            $tblPeriod = Term::useService()->insertPeriod(
                                                '1. Halbjahr',
                                                '01.08.20' . $year,
                                                '31.01.20' . ($year + 1)
                                            );
                                            if ($tblPeriod) {
                                                Term::useService()->insertYearPeriod($tblYear, $tblPeriod);
                                            }

                                            // secondTerm
                                            $tblPeriod = Term::useService()->insertPeriod(
                                                '2. Halbjahr',
                                                '01.02.20' . ($year + 1),
                                                '31.07.20' . ($year + 1)
                                            );
                                            if ($tblPeriod) {
                                                Term::useService()->insertYearPeriod($tblYear, $tblPeriod);
                                            }
                                        }

                                        $tblSchoolType = Type::useService()->getTypeById(7); // Gymnasium
                                        if ($tblSchoolType) {
                                            if (($pos = stripos($division, '-'))) {
                                                $level = substr($division, 0, $pos);
                                                $division = substr($division, $pos + 1);
                                                $tblGroup = Group::useService()->insertGroup((2020 - $level) . '-' . $division);
                                            } else {
                                                $level = $division;
                                                $division = '';
                                                $tblGroup = Group::useService()->insertGroup((2020 - $level));
                                            }
                                            $tblLevel = Division::useService()->insertLevel($tblSchoolType, $level);
                                            if ($tblLevel) {
                                                $tblDivision = Division::useService()->insertDivision($tblYear,
                                                    $tblLevel,
                                                    $division);
                                            }
                                            // Stammgruppe
                                            if ($tblGroup){
                                                Group::useService()->addGroupPerson($tblGroup, $tblPerson);
                                            }
                                        }
                                    }

                                    if ($tblDivision) {
                                        Division::useService()->insertDivisionStudent($tblDivision, $tblPerson);
                                    } else {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler konnte keiner Klasse zugeordnet werden.';
                                    }
                                }

                                $tblRelationshipTypeCustody = Relationship::useService()->getTypeById(1);

                                // Father
                                $tblPersonFather = null;
                                $fatherLastName = trim($Document->getValue($Document->getCell($Location['Sorg2 Name'],
                                    $RunY)));
                                $fatherFirstName = trim($Document->getValue($Document->getCell($Location['Sorg2 Vorname'],
                                    $RunY)));

                                $tblPersonFatherExists = Person::useService()->existsPerson(
                                    $fatherFirstName,
                                    $fatherLastName,
                                    $cityCode
                                );

                                if (!$tblPersonFatherExists) {
                                    $tblPersonFather = Person::useService()->insertPerson(
                                        Person::useService()->getSalutationById(1),
                                        '',
                                        $fatherFirstName,
                                        '',
                                        $fatherLastName,
                                        array(
                                            0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                            1 => Group::useService()->getGroupByMetaTable('CUSTODY')
                                        )
                                    );

                                    if ($tblPersonFather) {
                                        Common::useService()->insertMeta(
                                            $tblPersonFather,
                                            '',
                                            '',
                                            TblCommonBirthDates::VALUE_GENDER_MALE,
                                            '',
                                            '',
                                            TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                            '',
                                            ''
                                        );
                                    }

                                    Relationship::useService()->insertRelationshipToPerson(
                                        $tblPersonFather,
                                        $tblPerson,
                                        $tblRelationshipTypeCustody,
                                        ''
                                    );

                                    $countFather++;
                                } else {

                                    Relationship::useService()->insertRelationshipToPerson(
                                        $tblPersonFatherExists,
                                        $tblPerson,
                                        $tblRelationshipTypeCustody,
                                        ''
                                    );

                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Vater wurde nicht angelegt, da schon eine Person mit gleichen Namen und gleicher PLZ existiert. Der Schüler wurde mit der bereits existierenden Person verknüpft';

                                    $countFatherExists++;
                                }

                                // Mother
                                $tblPersonMother = null;
                                $motherLastName = trim($Document->getValue($Document->getCell($Location['Sorg1 Name'],
                                    $RunY)));
                                $motherFirstName = trim($Document->getValue($Document->getCell($Location['Sorg1 Vorname'],
                                    $RunY)));

                                $tblPersonMotherExists = Person::useService()->existsPerson(
                                    $motherFirstName,
                                    $motherLastName,
                                    $cityCode
                                );

                                if (!$tblPersonMotherExists) {
                                    $tblPersonMother = Person::useService()->insertPerson(
                                        Person::useService()->getSalutationById(2),
                                        '',
                                        $motherFirstName,
                                        '',
                                        $motherLastName,
                                        array(
                                            0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                            1 => Group::useService()->getGroupByMetaTable('CUSTODY')
                                        )
                                    );

                                    if ($tblPersonMother) {
                                        Common::useService()->insertMeta(
                                            $tblPersonMother,
                                            '',
                                            '',
                                            TblCommonBirthDates::VALUE_GENDER_FEMALE,
                                            '',
                                            '',
                                            TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                            '',
                                            ''
                                        );
                                    }

                                    Relationship::useService()->insertRelationshipToPerson(
                                        $tblPersonMother,
                                        $tblPerson,
                                        $tblRelationshipTypeCustody,
                                        ''
                                    );

                                    $countMother++;
                                } else {

                                    Relationship::useService()->insertRelationshipToPerson(
                                        $tblPersonMotherExists,
                                        $tblPerson,
                                        $tblRelationshipTypeCustody,
                                        ''
                                    );

                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Mutter wurde nicht angelegt, da schon eine Person mit gleichen Namen und gleicher PLZ existiert. Der Schüler wurde mit der bereits existierenden Person verknüpft';

                                    $countMotherExists++;
                                }

                                // Addresses
                                $streetName = '';
                                $streetNumber = '';
                                $street = trim($Document->getValue($Document->getCell($Location['Straße'],
                                    $RunY)));
                                if (preg_match_all('!\d+!', $street, $matches)) {
                                    $pos = strpos($street, $matches[0][0]);
                                    if ($pos !== null) {
                                        $streetName = trim(substr($street, 0, $pos));
                                        $streetNumber = trim(substr($street, $pos));
                                    }
                                }
                                $county = trim($Document->getValue($Document->getCell($Location['Landkreis'],
                                    $RunY)));
                                Address::useService()->insertAddressToPerson(
                                    $tblPerson, $streetName, $streetNumber, $cityCode, $cityName, $cityDistrict, '', $county
                                );
                                if ($tblPersonFather !== null) {
                                    Address::useService()->insertAddressToPerson(
                                        $tblPersonFather, $streetName, $streetNumber, $cityCode, $cityName,
                                        $cityDistrict, '', $county
                                    );
                                }
                                if ($tblPersonMother !== null) {
                                    Address::useService()->insertAddressToPerson(
                                        $tblPersonMother, $streetName, $streetNumber, $cityCode, $cityName,
                                        $cityDistrict, '', $county
                                    );
                                }

                                $phoneNumber = trim($Document->getValue($Document->getCell($Location['privat'],
                                    $RunY)));
                                if ($phoneNumber != '') {
                                    $tblType = Phone::useService()->getTypeById(1);
                                    if (0 === strpos($phoneNumber, '01')) {
                                        $tblType = Phone::useService()->getTypeById(2);
                                    }
                                    if (($pos = stripos($phoneNumber, ' '))){
                                        $remark = substr($phoneNumber, $pos + 1);
                                        $phoneNumber = substr($phoneNumber, 0, $pos);
                                    } else {
                                        $remark = '';
                                    }
                                    Phone::useService()->insertPhoneToPerson(
                                        $tblPerson,
                                        $phoneNumber,
                                        $tblType,
                                        $remark
                                    );
                                }

                                $phoneNumber = trim($Document->getValue($Document->getCell($Location['privat 2'],
                                    $RunY)));
                                if ($phoneNumber != '') {
                                    $tblType = Phone::useService()->getTypeById(1);
                                    if (0 === strpos($phoneNumber, '01')) {
                                        $tblType = Phone::useService()->getTypeById(2);
                                    }
                                    if (($pos = stripos($phoneNumber, ' '))){
                                        $remark = substr($phoneNumber, $pos + 1);
                                        $phoneNumber = substr($phoneNumber, 0, $pos);
                                    } else {
                                        $remark = '';
                                    }
                                    Phone::useService()->insertPhoneToPerson(
                                        $tblPerson,
                                        $phoneNumber,
                                        $tblType,
                                        $remark
                                    );
                                }

                                if ($tblPersonMother !== null) {
                                    $phoneNumber = trim($Document->getValue($Document->getCell($Location['Mutter mobil'],
                                        $RunY)));
                                    if ($phoneNumber != '') {
                                        $tblType = Phone::useService()->getTypeById(1);
                                        if (0 === strpos($phoneNumber, '01')) {
                                            $tblType = Phone::useService()->getTypeById(2);
                                        }
                                        if (($pos = stripos($phoneNumber, ' '))) {
                                            $remark = substr($phoneNumber, $pos + 1);
                                            $phoneNumber = substr($phoneNumber, 0, $pos);
                                        } else {
                                            $remark = '';
                                        }
                                        Phone::useService()->insertPhoneToPerson(
                                            $tblPersonMother,
                                            $phoneNumber,
                                            $tblType,
                                            $remark
                                        );
                                    }
                                }

                                if ($tblPersonFather !== null) {
                                    $phoneNumber = trim($Document->getValue($Document->getCell($Location['Vater mobil'],
                                        $RunY)));
                                    if ($phoneNumber != '') {
                                        $tblType = Phone::useService()->getTypeById(1);
                                        if (0 === strpos($phoneNumber, '01')) {
                                            $tblType = Phone::useService()->getTypeById(2);
                                        }
                                        if (($pos = stripos($phoneNumber, ' '))) {
                                            $remark = substr($phoneNumber, $pos + 1);
                                            $phoneNumber = substr($phoneNumber, 0, $pos);
                                        } else {
                                            $remark = '';
                                        }
                                        Phone::useService()->insertPhoneToPerson(
                                            $tblPersonFather,
                                            $phoneNumber,
                                            $tblType,
                                            $remark
                                        );
                                    }
                                }

                                $phoneNumber = trim($Document->getValue($Document->getCell($Location['Notfall 1'],
                                    $RunY)));
                                if ($phoneNumber != '') {
                                    $tblType = Phone::useService()->getTypeById(5);
                                    if (0 === strpos($phoneNumber, '01')) {
                                        $tblType = Phone::useService()->getTypeById(6);
                                    }
                                    if (($pos = stripos($phoneNumber, ' '))){
                                        $remark = substr($phoneNumber, $pos + 1);
                                        $phoneNumber = substr($phoneNumber, 0, $pos);
                                    } else {
                                        $remark = '';
                                    }
                                    Phone::useService()->insertPhoneToPerson(
                                        $tblPerson,
                                        $phoneNumber,
                                        $tblType,
                                        $remark
                                    );
                                }

                                $phoneNumber = trim($Document->getValue($Document->getCell($Location['Notfall 2'],
                                    $RunY)));
                                if ($phoneNumber != '') {
                                    $tblType = Phone::useService()->getTypeById(5);
                                    if (0 === strpos($phoneNumber, '01')) {
                                        $tblType = Phone::useService()->getTypeById(6);
                                    }
                                    if (($pos = stripos($phoneNumber, ' '))){
                                        $remark = substr($phoneNumber, $pos + 1);
                                        $phoneNumber = substr($phoneNumber, 0, $pos);
                                    } else {
                                        $remark = '';
                                    }
                                    Phone::useService()->insertPhoneToPerson(
                                        $tblPerson,
                                        $phoneNumber,
                                        $tblType,
                                        $remark
                                    );
                                }

                                $phoneNumber = trim($Document->getValue($Document->getCell($Location['Fax'],
                                    $RunY)));
                                if ($phoneNumber != '') {
                                    $tblType = Phone::useService()->getTypeById(7);

                                    if (($pos = stripos($phoneNumber, ' '))){
                                        $remark = substr($phoneNumber, $pos + 1);
                                        $phoneNumber = substr($phoneNumber, 0, $pos);
                                    } else {
                                        $remark = '';
                                    }
                                    Phone::useService()->insertPhoneToPerson(
                                        $tblPerson,
                                        $phoneNumber,
                                        $tblType,
                                        $remark
                                    );
                                }

                                // Herrnhut will Emailadressen manuell nacharbeiten
//                                $mailAddress = trim($Document->getValue($Document->getCell($Location['E-Mail'],
//                                    $RunY)));
//                                if ($mailAddress != '') {
//                                    Mail::useService()->insertMailToPerson(
//                                        $tblPerson,
//                                        $mailAddress,
//                                        Mail::useService()->getTypeById(1),
//                                        ''
//                                    );
//                                }
//
//                                $mailAddress = trim($Document->getValue($Document->getCell($Location['Email1'],
//                                    $RunY)));
//                                if ($mailAddress != '') {
//                                    Mail::useService()->insertMailToPerson(
//                                        $tblPerson,
//                                        $mailAddress,
//                                        Mail::useService()->getTypeById(1),
//                                        ''
//                                    );
//                                }
//
//                                $mailAddress = trim($Document->getValue($Document->getCell($Location['Email2'],
//                                    $RunY)));
//                                if ($mailAddress != '') {
//                                    Mail::useService()->insertMailToPerson(
//                                        $tblPerson,
//                                        $mailAddress,
//                                        Mail::useService()->getTypeById(1),
//                                        ''
//                                    );
//                                }

                                /*
                                 * student
                                 */
                                $sibling = trim($Document->getValue($Document->getCell($Location['Geschw.'],
                                    $RunY)));
                                $tblSiblingRank = false;
                                if ($sibling !== '') {
                                    if ($sibling == '0') {
                                        // do nothing
                                    } elseif ($sibling == '1') {
                                        $tblSiblingRank = Relationship::useService()->getSiblingRankById(1);
                                    } elseif ($sibling == '2') {
                                        $tblSiblingRank = Relationship::useService()->getSiblingRankById(2);
                                    } elseif ($sibling == '3') {
                                        $tblSiblingRank = Relationship::useService()->getSiblingRankById(3);
                                    } elseif ($sibling == '4') {
                                        $tblSiblingRank = Relationship::useService()->getSiblingRankById(4);
                                    } elseif ($sibling == '5') {
                                        $tblSiblingRank = Relationship::useService()->getSiblingRankById(5);
                                    } elseif ($sibling == '6') {
                                        $tblSiblingRank = Relationship::useService()->getSiblingRankById(6);
                                    } else {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Geschwisterkind konnte nicht angelegt werden.';
                                    }
                                }

                                if ($tblSiblingRank) {
                                    $tblStudentBilling = Student::useService()->insertStudentBilling($tblSiblingRank);
                                } else {
                                    $tblStudentBilling = null;
                                }

                                $coachingRequired = (trim($Document->getValue($Document->getCell($Location['Schüler_Integr_Förderschüler'],
                                        $RunY))) == 'Ja');
                                if ($coachingRequired) {
                                    $tblStudentIntegration = Student::useService()->insertStudentIntegration(
                                        null,
                                        null,
                                        null,
                                        null,
                                        null,
                                        true
                                    );
                                } else {
                                    $tblStudentIntegration = null;
                                }

                                $disease = trim($Document->getValue($Document->getCell($Location['Förderung Hinweise'],
                                    $RunY)));
                                if ($disease) {
                                    $tblStudentMedicalRecord = Student::useService()->insertStudentMedicalRecord(
                                        $disease,
                                        '',
                                        ''
                                    );
                                } else {
                                    $tblStudentMedicalRecord = null;
                                }

                                $transport = trim($Document->getValue($Document->getCell($Location['Verkehrsmittel'],
                                    $RunY)));
                                $tblStudentTransport = Student::useService()->insertStudentTransport(
                                    trim($Document->getValue($Document->getCell($Location['Beförderung Hinweise'],
                                        $RunY))),
                                    trim($Document->getValue($Document->getCell($Location['Einsteigestelle'],
                                        $RunY))),
                                    '',
                                    $transport !== '' ? 'Verkehrsmittel: ' . $transport : ''
                                );

                                $tblStudent = Student::useService()->insertStudent($tblPerson, '',
                                    $tblStudentMedicalRecord, $tblStudentTransport,
                                    $tblStudentBilling, null, null, $tblStudentIntegration);
                                if ($tblStudent) {

                                    // Schülertransfer
                                    $enrollmentDate = trim($Document->getValue($Document->getCell($Location['Einschulung am'],
                                        $RunY)));
                                    $enrollmentRemark = trim($Document->getValue($Document->getCell($Location['Einschulungsart Zusatz'],
                                        $RunY)));
                                    if ($enrollmentDate !== '' && date_create($enrollmentDate) !== false) {
                                        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ENROLLMENT');
                                        Student::useService()->insertStudentTransfer(
                                            $tblStudent,
                                            $tblStudentTransferType,
                                            null,
                                            null,
                                            null,
                                            $enrollmentDate,
                                            $enrollmentRemark
                                        );
                                    }
                                    $arriveDate = trim($Document->getValue($Document->getCell($Location['Aufnahme am'],
                                        $RunY)));
                                    $arriveSchool = null;
                                    $company = trim($Document->getValue($Document->getCell($Location['abg. Schule ID'],
                                        $RunY)));
                                    if ($company != '' && ($tblCompany = Company::useService()->insertCompany($company))
                                    ) {
                                        $arriveSchool = $tblCompany;
                                        $tblCompanyGroup = \SPHERE\Application\Corporation\Group\Group::useService()->getGroupByMetaTable('COMMON');
                                        \SPHERE\Application\Corporation\Group\Group::useService()->addGroupCompany($tblCompanyGroup,
                                            $tblCompany);
                                        $tblCompanyGroup = \SPHERE\Application\Corporation\Group\Group::useService()->getGroupByMetaTable('SCHOOL');
                                        \SPHERE\Application\Corporation\Group\Group::useService()->addGroupCompany($tblCompanyGroup,
                                            $tblCompany);
                                    }
                                    if ($arriveDate !== '' && date_create($arriveDate) !== false) {
                                        $schoolType = trim($Document->getValue($Document->getCell($Location['letzte Schulart'],
                                            $RunY)));
                                        if ($schoolType == 'MS' || $schoolType == 'RS') {
                                            $tblSchoolType = Type::useService()->getTypeById(8); // Mittelschule / Oberschule
                                        } elseif ($schoolType == 'GY') {
                                            $tblSchoolType = Type::useService()->getTypeById(7); // Gymnasium
                                        }  elseif ($schoolType == 'GS') {
                                            $tblSchoolType = Type::useService()->getTypeById(6); // Grundschule
                                        } else {
                                            $tblSchoolType = false;
                                        }
                                        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE');
                                        Student::useService()->insertStudentTransfer(
                                            $tblStudent,
                                            $tblStudentTransferType,
                                            $arriveSchool,
                                            $tblSchoolType ? $tblSchoolType : null,
                                            null,
                                            $arriveDate,
                                            ''
                                        );
                                    }
                                    $leaveDate = trim($Document->getValue($Document->getCell($Location['Abgang am'],
                                        $RunY)));
                                    if ($leaveDate !== '' && date_create($leaveDate) !== false) {
                                        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('LEAVE');
                                        Student::useService()->insertStudentTransfer(
                                            $tblStudent,
                                            $tblStudentTransferType,
                                            null,
                                            null,
                                            null,
                                            $leaveDate,
                                            ''
                                        );
                                    }
//                                    $currentSchool = null;
//                                    $company = trim($Document->getValue($Document->getCell($Location['auf welche Schule_ID'],
//                                        $RunY)));
//                                    if ($company !== '' && ($tblCompany = Company::useService()->insertCompany($company))
//                                    ) {
//                                        $currentSchool = $tblCompany;
//                                        $tblCompanyGroup = \SPHERE\Application\Corporation\Group\Group::useService()->getGroupByMetaTable('COMMON');
//                                        \SPHERE\Application\Corporation\Group\Group::useService()->addGroupCompany($tblCompanyGroup,
//                                            $tblCompany);
//                                        $tblCompanyGroup = \SPHERE\Application\Corporation\Group\Group::useService()->getGroupByMetaTable('SCHOOL');
//                                        \SPHERE\Application\Corporation\Group\Group::useService()->addGroupCompany($tblCompanyGroup,
//                                            $tblCompany);
//                                    }
//                                    $tblCourse = null;
//                                    if (($course = trim($Document->getValue($Document->getCell($Location['Bildungsgang'],
//                                        $RunY))))
//                                    ) {
//                                        if ($course == 'HS') {
//                                            $tblCourse = Course::useService()->getCourseById(1); // Hauptschule
//                                        } elseif ($course == 'GY') {
//                                            $tblCourse = Course::useService()->getCourseById(3); // Gymnasium
//                                        } elseif ($course == 'RS' || $course == 'ORS') {
//                                            $tblCourse = Course::useService()->getCourseById(2); // Realschule
//                                        } elseif ($course == '') {
//                                            // do nothing
//                                        } else {
//                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Bildungsgang nicht gefunden.';
//                                        }
//                                    }
//                                    $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
//                                    Student::useService()->insertStudentTransfer(
//                                        $tblStudent,
//                                        $tblStudentTransferType,
//                                        $currentSchool,
//                                        $tblSchoolType ? $tblSchoolType : null,
//                                        $tblCourse ? $tblCourse : null,
//                                        null,
//                                        ''
//                                    );

                                    /*
                                     * Fächer
                                     */
                                    // Religion
                                    $subjectReligion = trim($Document->getValue($Document->getCell($Location['Religionsunterricht'],
                                        $RunY)));
                                    $tblSubject = false;
                                    if ($subjectReligion !== '') {
                                        if ($subjectReligion === 'ETH') {
                                            $tblSubject = Subject::useService()->getSubjectByAcronym('ETH');
                                        } elseif ($subjectReligion === 'RE/e') {
                                            $tblSubject = Subject::useService()->getSubjectByAcronym('REV');
                                        }
                                        if ($tblSubject) {
                                            Student::useService()->addStudentSubject(
                                                $tblStudent,
                                                Student::useService()->getStudentSubjectTypeByIdentifier('RELIGION'),
                                                Student::useService()->getStudentSubjectRankingByIdentifier('1'),
                                                $tblSubject
                                            );
                                        }
                                    }
                                    // Profil
                                    $subjectProfile = trim($Document->getValue($Document->getCell($Location['Profil'],
                                        $RunY)));
                                    $tblSubject = false;
                                    if ($subjectProfile !== '') {
                                        if ($subjectProfile === 'P/gw-WED') {
                                            $tblSubject = Subject::useService()->getSubjectByAcronym('WED');
                                            if (!$tblSubject) {
                                                $tblSubject = Subject::useService()->insertSubject('WED',
                                                    'gesellschaftswissenschaftliches Profil / Wirtschaftsethik und Diakonie');
                                                $tblCategory = Subject::useService()->getCategoryByIdentifier('PROFILE');
                                                Subject::useService()->addCategorySubject($tblCategory, $tblSubject);
                                            }
                                        } elseif ($subjectProfile === 'P/nw-WDS') {
                                            $tblSubject = Subject::useService()->getSubjectByAcronym('WDS');
                                            if (!$tblSubject) {
                                                $tblSubject = Subject::useService()->insertSubject('WDS',
                                                    'naturwissenschaftlich-mathematisches Profil / "Welt der Sinne"');
                                                $tblCategory = Subject::useService()->getCategoryByIdentifier('PROFILE');
                                                Subject::useService()->addCategorySubject($tblCategory, $tblSubject);
                                            }
                                        }
                                        if ($tblSubject) {
                                            Student::useService()->addStudentSubject(
                                                $tblStudent,
                                                Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE'),
                                                Student::useService()->getStudentSubjectRankingByIdentifier('1'),
                                                $tblSubject
                                            );
                                        }
                                    }
                                    // Fremdsprachen
                                    for ($i = 1; $i <= 3; $i++) {
                                        $subjectLanguage = trim($Document->getValue($Document->getCell($Location['FS' . $i],
                                            $RunY)));
                                        $tblSubject = false;
                                        if ($subjectLanguage !== '') {
                                            if ($subjectLanguage === 'EN') {
                                                $tblSubject = Subject::useService()->getSubjectByAcronym('EN');
                                            } elseif ($subjectLanguage === 'FR') {
                                                $tblSubject = Subject::useService()->getSubjectByAcronym('FR');
                                            } elseif ($subjectLanguage === 'LA') {
                                                $tblSubject = Subject::useService()->getSubjectByAcronym('LA');
                                            } elseif ($subjectLanguage === 'SPA') {
                                                $tblSubject = Subject::useService()->getSubjectByAcronym('SP');
                                            } elseif ($subjectLanguage === 'TSC') {
                                                $tblSubject = Subject::useService()->getSubjectByAcronym('TSC');
                                                if (!$tblSubject) {
                                                    $tblSubject = Subject::useService()->insertSubject('TSC',
                                                        'Tschechisch');
                                                    $tblCategory = Subject::useService()->getCategoryByIdentifier('FOREIGNLANGUAGE');
                                                    Subject::useService()->addCategorySubject($tblCategory, $tblSubject);
                                                }
                                            } elseif ($subjectLanguage === 'fort. TSC') {
                                                $tblSubject = Subject::useService()->getSubjectByAcronym('TSC-f');
                                                if (!$tblSubject) {
                                                    $tblSubject = Subject::useService()->insertSubject('TSC-f',
                                                        'Tschechisch, fortgeführt');
                                                    $tblCategory = Subject::useService()->getCategoryByIdentifier('FOREIGNLANGUAGE');
                                                    Subject::useService()->addCategorySubject($tblCategory, $tblSubject);
                                                }
                                            }
                                            if ($tblSubject) {
                                                $tblSchoolType = Type::useService()->getTypeById(7); // Gymnasium
                                                $tblFromLevel = false;
                                                $fromLevel = trim($Document->getValue($Document->getCell($Location['FS' . $i . ' von'],
                                                    $RunY)));
                                                if ($fromLevel !== ''){
                                                    if (strlen($fromLevel) == 2){
                                                        $fromLevel = ltrim($fromLevel,'0');
                                                        $tblFromLevel = Division::useService()->insertLevel(
                                                            $tblSchoolType,
                                                            $fromLevel
                                                        );
                                                    }
                                                }

                                                $tblToLevel = false;
                                                $toLevel = trim($Document->getValue($Document->getCell($Location['FS' . $i . ' bis'],
                                                    $RunY)));
                                                if ($toLevel !== ''){
                                                    if (strlen($toLevel) == 2){
                                                        $toLevel = ltrim($toLevel,'0');
                                                        $tblToLevel = Division::useService()->insertLevel(
                                                            $tblSchoolType,
                                                            $toLevel
                                                        );
                                                    }
                                                }

                                                Student::useService()->addStudentSubject(
                                                    $tblStudent,
                                                    Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'),
                                                    Student::useService()->getStudentSubjectRankingByIdentifier($i),
                                                    $tblSubject,
                                                    $tblFromLevel ? $tblFromLevel : null,
                                                    $tblToLevel ? $tblToLevel: null
                                                );
                                            }
                                        }
                                    }

                                    /*
                                     * Förderungsbedarf
                                     */
                                    $integration = trim($Document->getValue($Document->getCell($Location['Förderschwerpunkt'],
                                        $RunY)));
                                    if ($integration !== ''){
                                        if ($integration === 'Diskalkulie'){
                                            $tblStudentDisorderType = Student::useService()->getStudentDisorderTypeByName('Dyskalkulie');
                                            Student::useService()->addStudentDisorder($tblStudent, $tblStudentDisorderType);
                                        } elseif ($integration === 'LRS'){
                                            $tblStudentDisorderType = Student::useService()->getStudentDisorderTypeByName('LRS');
                                            Student::useService()->addStudentDisorder($tblStudent, $tblStudentDisorderType);
                                        } elseif ($integration === 'KME'){
                                            $tblStudentFocusType = Student::useService()->getStudentFocusTypeByName('Körperlich-motorische Entwicklung');
                                            Student::useService()->addStudentFocus($tblStudent, $tblStudentFocusType);
                                        } elseif ($integration === 'ESE'){
                                            $tblStudentFocusType = Student::useService()->getStudentFocusTypeByName('Sozial-emotionale Entwicklung');
                                            Student::useService()->addStudentFocus($tblStudent, $tblStudentFocusType);
                                        }
                                    }

                                }
                            }
                        }
                    }

                    Debugger::screenDump($error);

                    return
                        new Success('Es wurden ' . $countStudent . ' Schüler erfolgreich angelegt.') .
                        new Success('Es wurden ' . $countFather . ' Väter erfolgreich angelegt.') .
                        ($countFatherExists > 0 ?
                            new Warning($countFatherExists . ' Väter exisistieren bereits.') : '') .
                        new Success('Es wurden ' . $countMother . ' Mütter erfolgreich angelegt.') .
                        ($countMotherExists > 0 ?
                            new Warning($countMotherExists . ' Mütter exisistieren bereits.') : '');
//                        . new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
//                            new Panel(
//                                'Fehler',
//                                $error,
//                                Panel::PANEL_TYPE_DANGER
//                            )
//                        ))));

                } else {
                    Debugger::screenDump($Location);

                    return new Warning(json_encode($Location)) . new Danger(
                        "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }

        return new Danger('File nicht gefunden');
    }
}