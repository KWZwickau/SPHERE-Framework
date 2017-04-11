<?php
namespace SPHERE\Application\Transfer\Import\Tharandt;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonInformation;
use SPHERE\Application\People\Meta\Custody\Custody;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\System\Extension\Repository\Debugger;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Service
 *
 * @package SPHERE\Application\Transfer\Import\Tharandt
 */
class Service
{

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null   $File
     *
     * @return IFormInterface|Danger|string
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

        if ($File->getError()) {
            $Form->setError('File', 'Fehler');
            $Form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('File nicht gefunden')))));
            return $Form;
        }

        /**
         * Prepare
         */
        $File = $File->move($File->getPath(),
            $File->getFilename().'.'.$File->getClientOriginalExtension());

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
            'Klasse'         => null,
            'Name'           => null,
            'Vorname'        => null,
            'Geburtsdatum'   => null,
            'Straße'         => null,
            'Straße_V'       => null,
            'PLZ'            => null,
            'PLZ_V'          => null,
            'Ort'            => null,
            'Ort_V'          => null,
            'Name_Mutter'    => null,
            'Vorname_Mutter' => null,
            'Name_Vater'     => null,
            'Vorname_Vater'  => null,
            'TelPrivatM'     => null,
            'TelPrivatV'     => null,
            'DienstlM'       => null,
            'DienstlV'       => null,
            'HandyM'         => null,
            'HandyV'         => null,
            'Besonderes'     => null,
            'Konfession'     => null,
            'GS'             => null,
            'Mail_1'         => null,
            'Mail_2'         => null,
            'Beruf_Vater'    => null,
            'Beruf_Mutter'   => null
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
                set_time_limit(300);
                // Student
                $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
                if ($firstName === '' || $lastName === '') {
                    $error[] = 'Zeile: '.($RunY + 1).' Der Schüler wurde nicht hinzugefügt, da er keinen Vornamen und/oder Namen besitzt.';
                    continue;
                }

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
                    $error[] = 'Zeile: '.($RunY + 1).' Der Schüler konnte nicht angelegt werden.';
                    continue;
                }
                $countStudent++;

                // Student Birthday
                $day = trim($Document->getValue($Document->getCell($Location['Geburtsdatum'],
                    $RunY)));
                if ($day !== '') {
                    try {
                        $birthday = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                    } catch (\Exception $ex) {
                        $birthday = '';
                        $error[] = 'Zeile: '.($RunY + 1).' Ungültiges Geburtsdatum: '.$ex->getMessage();
                    }

                } else {
                    $birthday = '';
                }

                Common::useService()->insertMeta(
                    $tblPerson,
                    $birthday,
                    '',
                    '',
                    '',
                    trim($Document->getValue($Document->getCell($Location['Konfession'],
                        $RunY))),
                    TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                    '',
                    ''
                );

                // division
//                $tblSchoolType = false;
                $tblDivision = false;
                $year = 16;
                $division = trim($Document->getValue($Document->getCell($Location['Klasse'],
                    $RunY)));
                $level = '';
                if ($division !== '') {
                    $tblYear = Term::useService()->insertYear('20'.$year.'/'.($year + 1));
                    if ($tblYear) {
                        $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);
                        if (!$tblPeriodList) {
                            // firstTerm
                            $tblPeriod = Term::useService()->insertPeriod(
                                '1. Halbjahr',
                                '01.08.20'.$year,
                                '31.01.20'.($year + 1)
                            );
                            if ($tblPeriod) {
                                Term::useService()->insertYearPeriod($tblYear, $tblPeriod);
                            }

                            // secondTerm
                            $tblPeriod = Term::useService()->insertPeriod(
                                '2. Halbjahr',
                                '01.02.20'.($year + 1),
                                '31.07.20'.($year + 1)
                            );
                            if ($tblPeriod) {
                                Term::useService()->insertYearPeriod($tblYear, $tblPeriod);
                            }
                        }

                        if (strlen($division) > 1) {
                            if (is_numeric(substr($division, 0, 2))) {
                                $pos = 2;
                                $level = substr($division, 0, $pos);
                                // remove the "-"
                                if (substr($division, $pos, 1) == '-') {
                                    $pos = 3;
                                    $division = trim(substr($division, $pos));
                                } else {
                                    $division = trim(substr($division, $pos));
                                }
                            } else {
                                $pos = 1;
                                $level = substr($division, 0, $pos);
                                $division = trim(substr($division, $pos));
                            }
                        } else {
                            $level = $division;
                            $division = '';
                        }

//                            $tblSchoolType = Type::useService()->getTypeById(6); // Grundschule
//                            $tblSchoolType = Type::useService()->getTypeById(8); // Oberschule
                        $tblSchoolType = Type::useService()->getTypeById(7); // Gymnasium

                        $tblLevel = Division::useService()->insertLevel($tblSchoolType, $level);
                        if ($tblLevel) {
                            $tblDivision = Division::useService()->insertDivision(
                                $tblYear,
                                $tblLevel,
                                $division
                            );
                        }
                    }

                    if ($tblDivision) {
                        Division::useService()->insertDivisionStudent($tblDivision, $tblPerson);
                    } else {
                        $error[] = 'Zeile: '.($RunY + 1).' Der Schüler konnte keiner Klasse zugeordnet werden.';
                    }
                }

                // Address

                $CityCode = trim($Document->getValue($Document->getCell($Location['PLZ'], $RunY)));

                $cityDistrict = '';
                $City = trim($Document->getValue($Document->getCell($Location['Ort'], $RunY)));
                if (preg_match('!(\w*\s)(OT\s\w*)!is', $City, $Found)) {
                    $CityName = $Found[1];
                    $cityDistrict = $Found[2];
                } else {
                    $CityName = $City;
                }

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
//                $county = trim($Document->getValue($Document->getCell($Location['Schüler_Landkreis'],
//                    $RunY)));
//                if (trim($Document->getValue($Document->getCell($Location['Schüler_Bundesland'],
//                        $RunY))) == 'SN'
//                ) {
//                    $tblState = Address::useService()->getStateByName('Sachsen');
//                } else {
//                    $tblState = false;
//                }
                if ($streetName !== '' && $streetNumber !== ''
                    && $CityCode && $CityName
                ) {
                    if (($division == 'a' && $level == '9' && $lastName == 'Buschmann')
                        || ($division == 'b' && $level == '10' && $lastName == 'Henze')
                        || ($division == '1' && $level == '11' && $lastName == 'Kost')
                    ) {
                        //students get address by father
                    } else {
                        Address::useService()->insertAddressToPerson(
                            $tblPerson, $streetName, $streetNumber, $CityCode, $CityName,
                            $cityDistrict, '', '', '', null
                        );
                    }
                }

                $tblRelationshipTypeCustody = Relationship::useService()->getTypeById(1); // Sorgeberechtigt;

                // Mother
                $tblPersonMother = null;
                $motherLastName = trim($Document->getValue($Document->getCell($Location['Name_Mutter'],
                    $RunY)));
                $motherFirstName = trim($Document->getValue($Document->getCell($Location['Vorname_Mutter'],
                    $RunY)));
                $title = '';
                if (preg_match('!(\w+\.\s)(\w*)!', $motherFirstName, $FoundTitle)) {
                    if (isset($FoundTitle[1])) {
                        $title = trim($FoundTitle[1]);
                    }
                    if (isset($FoundTitle[2])) {
                        $motherFirstName = $FoundTitle[2];
                    }
                }

                $tblPersonMotherExists = false;
                if ($CityCode !== '' && $motherLastName != '') {
                    $tblPersonMotherExists = Person::useService()->existsPerson(
                        $motherFirstName,
                        $motherLastName,
                        $CityCode
                    );
                }

                if (!$tblPersonMotherExists && $motherLastName != '' && $motherFirstName != '') {
                    $tblGender = Common::useService()->getCommonGenderByName('Weiblich');
                    if ($tblGender) {
                        $gender = $tblGender->getId();
                    } else {
                        $gender = 0;
                    }
                    $tblSalutation = Person::useService()->getSalutationById(2); // Frau

                    $tblPersonMother = Person::useService()->insertPerson(
                        $tblSalutation,
                        $title,
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
                            $gender,
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
                } elseif ($tblPersonMotherExists) {

                    Relationship::useService()->insertRelationshipToPerson(
                        $tblPersonMotherExists,
                        $tblPerson,
                        $tblRelationshipTypeCustody,
                        ''
                    );

                    $error[] = 'Zeile: '.($RunY + 1).' Der Sorgeberechtigte1 wurde nicht angelegt, da schon eine 
                    Person mit gleichen Namen und gleicher PLZ existiert. Der Schüler wurde mit der bereits existierenden
                    Person verknüpft';

                    $countMotherExists++;
                }

                // Father
                $tblPersonFather = null;
                $fatherLastName = trim($Document->getValue($Document->getCell($Location['Name_Vater'],
                    $RunY)));
                $fatherFirstName = trim($Document->getValue($Document->getCell($Location['Vorname_Vater'],
                    $RunY)));
                $title = '';
                if (preg_match('!(\w+\.\s)(\w*)!', $fatherFirstName, $FoundTitle)) {
                    if (isset($FoundTitle[1])) {
                        $title = trim($FoundTitle[1]);
                    }
                    if (isset($FoundTitle[2])) {
                        $fatherFirstName = $FoundTitle[2];
                    }
                }

                $fatherCityCode = trim($Document->getValue($Document->getCell($Location['PLZ_V'], $RunY)));

                $tblPersonFatherExists = false;
                if ($fatherCityCode === '' && $fatherLastName != '' && $CityCode != '') {
                    // father without extra Address

                    $fatherCityCode = $CityCode;

                    $tblPersonFatherExists = Person::useService()->existsPerson(
                        $fatherFirstName,
                        $fatherLastName,
                        $fatherCityCode
                    );
                } elseif ($fatherCityCode !== '' && $fatherLastName != '') {
                    // father with extra Address
                    $tblPersonFatherExists = Person::useService()->existsPerson(
                        $fatherFirstName,
                        $fatherLastName,
                        $fatherCityCode
                    );
                }

                if (!$tblPersonFatherExists && $fatherLastName != '' && $fatherFirstName != '') {

                    $tblGender = Common::useService()->getCommonGenderByName('Männlich');
                    if ($tblGender) {
                        $gender = $tblGender->getId();
                    } else {
                        $gender = 0;
                    }
                    $tblSalutation = Person::useService()->getSalutationById(1); // Herr

                    $tblPersonFather = Person::useService()->insertPerson(
                        $tblSalutation,
                        $title,
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
                            $gender,
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
                } elseif ($tblPersonFatherExists) {

                    Relationship::useService()->insertRelationshipToPerson(
                        $tblPersonFatherExists,
                        $tblPerson,
                        $tblRelationshipTypeCustody,
                        ''
                    );

                    $error[] = 'Zeile: '.($RunY + 1).' Der Sorgeberechtigte2 wurde nicht angelegt, da schon eine 
                    Person mit gleichen Namen und gleicher PLZ existiert. Der Schüler wurde mit der bereits 
                    existierenden Person verknüpft';

                    $countFatherExists++;
                }

                $tblToPersonMother = false;
                if ($tblPersonMother !== null) {

                    if ($streetName !== '' && $streetNumber !== '' && $CityCode && $CityName) {
                        $tblToPersonMother = Address::useService()->insertAddressToPerson(
                            $tblPersonMother,
                            $streetName,
                            $streetNumber,
                            $CityCode,
                            $CityName,
                            $cityDistrict,
                            ''
                        );
                    } else {
                        $error[] = 'Zeile: '.($RunY + 1).' Die Adresse des Sorgeberechtigte1 wurde nicht angelegt,
                        da keine vollständige Adresse hinterlegt ist.';
                    }
                }
                if ($tblPersonFather !== null) {
                    $streetName = '';
                    $streetNumber = '';
                    $street = trim($Document->getValue($Document->getCell($Location['Straße_V'],
                        $RunY)));
                    $City = trim($Document->getValue($Document->getCell($Location['Ort_V'], $RunY)));
                    $cityDistrictFather = '';
                    if (preg_match('!(\w*\s)(OT\s\w*)!is', $City, $Found)) {
                        $CityNameFather = $Found[1];
                        $cityDistrictFather = $Found[2];
                    } else {
                        $CityNameFather = $City;
                    }
                    if ($street != '') {

                        if (preg_match_all('!\d+!', $street, $matches)) {
                            $pos = strpos($street, $matches[0][0]);
                            if ($pos !== null) {
                                $streetName = trim(substr($street, 0, $pos));
                                $streetNumber = trim(substr($street, $pos));
                            }
                        }

                        if ($streetName !== '' && $streetNumber !== '' && $fatherCityCode && $CityNameFather) {
                            Address::useService()->insertAddressToPerson(
                                $tblPersonFather,
                                $streetName,
                                $streetNumber,
                                $fatherCityCode,
                                $CityNameFather,
                                $cityDistrictFather,
                                ''
                            );
                            // students that get same Address like father
                            if (($division == 'a' && $level == '9' && $lastName == 'Buschmann')
                                || ($division == 'b' && $level == '10' && $lastName == 'Henze')
                                || ($division == '1' && $level == '11' && $lastName == 'Kost')
                            ) {
                                Address::useService()->insertAddressToPerson(
                                    $tblPerson,
                                    $streetName,
                                    $streetNumber,
                                    $fatherCityCode,
                                    $CityNameFather,
                                    $cityDistrictFather,
                                    ''
                                );
                            }
                        } else {
                            $error[] = 'Zeile: '.($RunY + 1).' Die Adresse des Sorgeberechtigte2 wurde nicht angelegt,
                             da keine vollständige Adresse hinterlegt ist.';
                        }
                    } elseif ($tblToPersonMother && ($tblAddressMother = $tblToPersonMother->getTblAddress())) {
                        Address::useService()->insertAddressToPerson(
                            $tblPersonFather,
                            $tblAddressMother->getStreetName(),
                            $tblAddressMother->getStreetNumber(),
                            $tblAddressMother->getTblCity()->getCode(),
                            $tblAddressMother->getTblCity()->getName(),
                            $tblAddressMother->getTblCity()->getDistrict(),
                            ''
                        );
                    } else {
                        $error[] = 'Zeile: '.($RunY + 1).' Es konnte keine Adresse für den Vater angelegt werden.';
                    }
                }

                // Insert Job
                $CustodyJobFather = trim($Document->getValue($Document->getCell($Location['Beruf_Vater'], $RunY)));
                if ($CustodyJobFather !== '' && $tblPersonFather) {
                    Custody::useService()->insertMeta($tblPersonFather, $CustodyJobFather, '', '');
                }
                $CustodyJobMother = trim($Document->getValue($Document->getCell($Location['Beruf_Mutter'], $RunY)));
                if ($CustodyJobMother !== '' && $tblPersonMother) {
                    Custody::useService()->insertMeta($tblPersonMother, $CustodyJobMother, '', '');
                }

                // create MedicalRecord
                $tblStudentMedicalRecord = null;
                $MedicalRecord = trim($Document->getValue($Document->getCell($Location['Besonderes'], $RunY)));
                if ($MedicalRecord !== '') {
                    $tblStudentMedicalRecord = Student::useService()->insertStudentMedicalRecord($MedicalRecord, '',
                        '');
                }

                // Create Student
                $tblStudent = Student::useService()->insertStudent($tblPerson, '', $tblStudentMedicalRecord);

                // Add Transfer
                $HistorySchool = trim($Document->getValue($Document->getCell($Location['GS'], $RunY)));
                if ($HistorySchool !== '' && $tblStudent) {
                    $TransferType = Student::useService()->getStudentTransferTypeByIdentifier('ENROLLMENT');
                    $TransferSchoolType = Type::useService()->getTypeByName('Grundschule');
                    if ($tblStudent && $TransferType && $TransferSchoolType) {
                        Student::useService()->insertStudentTransfer($tblStudent, $TransferType, null,
                            $TransferSchoolType,
                            null, null, $HistorySchool);
                    }
                }

                // PhoneNumber by "Father"
                $phonePrivateV = trim($Document->getValue($Document->getCell($Location['TelPrivatV'], $RunY)));
                $phoneCompanyV = trim($Document->getValue($Document->getCell($Location['DienstlV'], $RunY)));
                $phoneHandyV = trim($Document->getValue($Document->getCell($Location['HandyV'], $RunY)));

                if ($phonePrivateV != '' && $tblPersonFather) {
                    $tblType = Phone::useService()->getTypeById(1);
                    if (0 === strpos($phonePrivateV, '01')) {
                        $tblType = Phone::useService()->getTypeById(2);
                    }
                    Phone::useService()->insertPhoneToPerson(
                        $tblPersonFather,
                        $phonePrivateV,
                        $tblType,
                        ''
                    );
                }
                if ($phoneCompanyV != '' && $tblPersonFather) {
                    $tblType = Phone::useService()->getTypeById(3);
                    if (0 === strpos($phoneCompanyV, '01')) {
                        $tblType = Phone::useService()->getTypeById(4);
                    }
                    Phone::useService()->insertPhoneToPerson(
                        $tblPersonFather,
                        $phoneCompanyV,
                        $tblType,
                        ''
                    );
                }
                if ($phoneHandyV != '' && $tblPersonFather) {
                    $tblType = Phone::useService()->getTypeById(1);
                    if (0 === strpos($phoneHandyV, '01')) {
                        $tblType = Phone::useService()->getTypeById(2);
                    }
                    Phone::useService()->insertPhoneToPerson(
                        $tblPersonFather,
                        $phoneHandyV,
                        $tblType,
                        ''
                    );
                }

                // PhoneNumber by "Mother"
                $phonePrivateM = trim($Document->getValue($Document->getCell($Location['TelPrivatM'], $RunY)));
                $phoneCompanyM = trim($Document->getValue($Document->getCell($Location['DienstlM'], $RunY)));
                $phoneHandyM = trim($Document->getValue($Document->getCell($Location['HandyM'], $RunY)));
                if ($phonePrivateM != '' && $tblPersonMother) {
                    $tblType = Phone::useService()->getTypeById(1);
                    if (0 === strpos($phonePrivateM, '01')) {
                        $tblType = Phone::useService()->getTypeById(2);
                    }
                    Phone::useService()->insertPhoneToPerson(
                        $tblPersonMother,
                        $phonePrivateM,
                        $tblType,
                        ''
                    );
                }
                if ($phoneCompanyM != '' && $tblPersonMother) {
                    $tblType = Phone::useService()->getTypeById(3);
                    if (0 === strpos($phoneCompanyM, '01')) {
                        $tblType = Phone::useService()->getTypeById(4);
                    }
                    Phone::useService()->insertPhoneToPerson(
                        $tblPersonMother,
                        $phoneCompanyM,
                        $tblType,
                        ''
                    );
                }
                if ($phoneHandyM != '' && $tblPersonMother) {
                    $tblType = Phone::useService()->getTypeById(1);
                    if (0 === strpos($phoneHandyM, '01')) {
                        $tblType = Phone::useService()->getTypeById(2);
                    }
                    Phone::useService()->insertPhoneToPerson(
                        $tblPersonMother,
                        $phoneHandyM,
                        $tblType,
                        ''
                    );
                }

                // E-Mail
                $motherMail = trim($Document->getValue($Document->getCell($Location['Mail_1'], $RunY)));
                $fatherMail = trim($Document->getValue($Document->getCell($Location['Mail_2'], $RunY)));
                if ($motherMail != '' && $tblPersonMother) {
                    $tblType = Mail::useService()->getTypeById(1);
                    Mail::useService()->insertMailToPerson(
                        $tblPersonMother,
                        $motherMail,
                        $tblType,
                        ''
                    );
                }
                if ($fatherMail != '' && $tblPersonFather) {
                    $tblType = Mail::useService()->getTypeById(1);
                    Mail::useService()->insertMailToPerson(
                        $tblPersonFather,
                        $fatherMail,
                        $tblType,
                        ''
                    );
                }
            }

//            Debugger::screenDump($error);

            return
                new Success('Es wurden '.$countStudent.' Schüler erfolgreich angelegt.').
                new Success('Es wurden '.$countMother.' Weibliche Sorgeberechtigte erfolgreich angelegt.').
                ($countMotherExists > 0 ?
                    new Warning($countMotherExists.' Weibliche Sorgeberechtigte exisistieren bereits.') : '').
                new Success('Es wurden '.$countFather.' Männliche Sorgeberechtigte erfolgreich angelegt.').
                ($countFatherExists > 0 ?
                    new Warning($countFatherExists.' Männliche Sorgeberechtigte exisistieren bereits.') : '')
                .new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new Panel(
                        'Fehler',
                        $error,
                        Panel::PANEL_TYPE_DANGER
                    )
                ))));

        } else {
            Debugger::screenDump($Location);

            return new Warning(json_encode($Location)).new Danger(
                    "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
        }
    }

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null   $File
     *
     * @return IFormInterface|Danger|string
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createInterestedFromFile(
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
                    $File->getFilename().'.'.$File->getClientOriginalExtension());

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
                    'Termin'                => null,
                    'Name'                  => null,
                    'Kind'                  => null,
                    'Geb.- dat.'            => null,
                    'Adresse'               => null,
                    'PLZ'                   => null,
                    'Ort'                   => null,
                    'Name Mutter'           => null,
                    'Vorname Mutter'        => null,
                    'Name Vater'            => null,
                    'Vorname Vater'         => null,
                    'Telefon'               => null,
                    'Mail_1'                => null,
                    'Mail_2'                => null,
                    'Konf.'                 => null,
                    'Anm.-dat.'             => null,
                    'Grundschule'           => null,
                    'Bemerkungen'           => null,
                    'Zweitwunsch Gymnasium' => null,
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
                    $countInterestedPerson = 0;
                    $countFather = 0;
                    $countMother = 0;
                    $countFatherExists = 0;
                    $countMotherExists = 0;

                    $error = array();

                    for ($RunY = 1; $RunY < $Y; $RunY++) {

                        // InterestedPerson
                        $firstName = trim($Document->getValue($Document->getCell($Location['Kind'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));

                        if ($firstName !== '' && $lastName !== '') {
                            $tblPerson = Person::useService()->insertPerson(
                                Person::useService()->getSalutationById(3),    //Schüler
                                '',
                                $firstName,
                                '',
                                $lastName,
                                array(
                                    0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                    1 => Group::useService()->getGroupByMetaTable('PROSPECT')
                                )
                            );

                            if ($tblPerson !== false) {
                                $countInterestedPerson++;

                                $cityName = trim($Document->getValue($Document->getCell($Location['Ort'],
                                    $RunY)));
                                $cityCode = str_pad(
                                    trim($Document->getValue($Document->getCell($Location['PLZ'], $RunY))),
                                    5,
                                    "0",
                                    STR_PAD_LEFT
                                );
                                $cityDistrict = '';
                                $pos = strpos($cityName, " OT ");
                                if ($pos !== false) {
                                    $cityDistrict = trim(substr($cityName, $pos + 4));
                                    $cityName = trim(substr($cityName, 0, $pos));
                                }
                                $StreetName = '';
                                $StreetNumber = '';
                                $Street = trim($Document->getValue($Document->getCell($Location['Adresse'],
                                    $RunY)));
                                if (preg_match_all('!\d+!', $Street, $matches)) {
                                    $pos = strpos($Street, $matches[0][0]);
                                    if ($pos !== null) {
                                        $StreetName = trim(substr($Street, 0, $pos));
                                        $StreetNumber = trim(substr($Street, $pos));
                                    }
                                }

                                $day = trim($Document->getValue($Document->getCell($Location['Geb.- dat.'],
                                    $RunY)));
                                if ($day !== '') {
                                    $birthday = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                                } else {
                                    $birthday = '';
                                }
                                $Denomination = trim($Document->getValue($Document->getCell($Location['Konf.'],
                                    $RunY)));

                                Common::useService()->insertMeta(
                                    $tblPerson,
                                    $birthday,
                                    '',
                                    TblCommonBirthDates::VALUE_GENDER_NULL,
                                    '',
                                    $Denomination,
                                    TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                    '',
                                    ''
                                );

                                $remark = trim($Document->getValue($Document->getCell($Location['Bemerkungen'],
                                    $RunY)));
                                $info = trim($Document->getValue($Document->getCell($Location['Grundschule'], $RunY)));
                                if ($info !== '') {
                                    $remark .= ($remark == '' ? '' : " \n").'Grundschule: '.$info;
                                }
                                $info = trim($Document->getValue($Document->getCell($Location['Zweitwunsch Gymnasium'],
                                    $RunY)));
                                if ($info !== '') {
                                    $remark .= ($remark == '' ? '' : " \n").'Zweitwunsch: '.$info;
                                }

                                $day = trim($Document->getValue($Document->getCell($Location['Termin'],
                                    $RunY)));
                                if ($day !== '') {
                                    $interviewDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                                } else {
                                    $interviewDate = '';
                                }

                                $day = trim($Document->getValue($Document->getCell($Location['Anm.-dat.'],
                                    $RunY)));
                                if ($day !== '') {
                                    $reservationDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                                } else {
                                    $reservationDate = '';
                                }

                                Prospect::useService()->insertMeta(
                                    $tblPerson,
                                    $reservationDate,
                                    $interviewDate,
                                    '',
                                    '',
                                    '',
                                    null,
                                    null,
                                    $remark
                                );

                                $tblRelationshipTypeCustody = Relationship::useService()->getTypeById(1);

                                // Custody1
                                $tblPersonCustody1 = null;
                                $firstNameCustody1 = trim($Document->getValue($Document->getCell($Location['Vorname Mutter'],
                                    $RunY)));
                                $lastNameCustody1 = trim($Document->getValue($Document->getCell($Location['Name Mutter'],
                                    $RunY)));

                                if ($firstNameCustody1 !== '' && $lastNameCustody1 !== '') {
                                    $tblPersonCustody1Exists = Person::useService()->existsPerson(
                                        $firstNameCustody1,
                                        $lastNameCustody1,
                                        $cityCode
                                    );

                                    if (!$tblPersonCustody1Exists) {
                                        $tblPersonCustody1 = Person::useService()->insertPerson(
                                            null,
                                            '',
                                            $firstNameCustody1,
                                            '',
                                            $lastNameCustody1,
                                            array(
                                                0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                                1 => Group::useService()->getGroupByMetaTable('CUSTODY')
                                            )
                                        );

                                        // E-Mail
                                        $motherMail = trim($Document->getValue($Document->getCell($Location['Mail_1'],
                                            $RunY)));
                                        if ($motherMail != '' && $tblPersonCustody1) {
                                            $tblType = Mail::useService()->getTypeById(1);
                                            Mail::useService()->insertMailToPerson(
                                                $tblPersonCustody1,
                                                $motherMail,
                                                $tblType,
                                                ''
                                            );
                                        }

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonCustody1,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            ''
                                        );

                                        // Address
                                        if ($StreetName && $StreetNumber && $cityCode && $cityName) {
                                            Address::useService()->insertAddressToPerson(
                                                $tblPersonCustody1, $StreetName, $StreetNumber, $cityCode, $cityName,
                                                $cityDistrict, ''
                                            );
                                        } else {
                                            $error[] = 'Zeile: '.($RunY + 1).' Die Adresse des Sorgeberechtigen1 wurde nicht angelegt, da sie keine vollständige Adresse besitzt.';
                                        }

                                        $countFather++;
                                    } else {

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonCustody1Exists,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            ''
                                        );

                                        $countFatherExists++;
                                        $error[] = 'Zeile: '.($RunY + 1).' Der Sorgeberechtigte wurde nicht angelegt, da schon eine Person mit gleichen Namen und gleicher PLZ existiert.';
                                    }
                                } else {
                                    $error[] = 'Zeile: '.($RunY + 1).' Der Sorgeberechtigte1 wurde nicht angelegt, da sie keinen Namen und Vornamen hat.';
                                }

                                // Custody2
                                $tblPersonCustody2 = null;
                                $firstNameCustody2 = trim($Document->getValue($Document->getCell($Location['Vorname Vater'],
                                    $RunY)));
                                $lastNameCustody2 = trim($Document->getValue($Document->getCell($Location['Name Vater'],
                                    $RunY)));

                                if ($firstNameCustody2 !== '' && $lastNameCustody2 !== '') {
                                    $tblPersonCustody2Exists = Person::useService()->existsPerson(
                                        $firstNameCustody2,
                                        $lastNameCustody2,
                                        $cityCode
                                    );

                                    if (!$tblPersonCustody2Exists) {
                                        $tblPersonCustody2 = Person::useService()->insertPerson(
                                            null,
                                            '',
                                            $firstNameCustody2,
                                            '',
                                            $lastNameCustody2,
                                            array(
                                                0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                                1 => Group::useService()->getGroupByMetaTable('CUSTODY')
                                            )
                                        );

                                        // E-Mail
                                        $fatherMail = trim($Document->getValue($Document->getCell($Location['Mail_2'],
                                            $RunY)));
                                        if ($fatherMail != '' && $firstNameCustody2) {
                                            $tblType = Mail::useService()->getTypeById(1);
                                            Mail::useService()->insertMailToPerson(
                                                $tblPersonCustody2,
                                                $fatherMail,
                                                $tblType,
                                                ''
                                            );
                                        }

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonCustody2,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            ''
                                        );

                                        if ($StreetName && $StreetNumber && $cityCode && $cityName) {
                                            Address::useService()->insertAddressToPerson(
                                                $tblPersonCustody2, $StreetName, $StreetNumber, $cityCode, $cityName,
                                                $cityDistrict, ''
                                            );
                                        } else {
                                            $error[] = 'Zeile: '.($RunY + 1).' Die Adresse des Sorgeberechtigen2 wurde nicht angelegt, da sie keine vollständige Adresse besitzt.';
                                        }

                                        $countMother++;
                                    } else {

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonCustody2Exists,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            ''
                                        );

                                        $countMotherExists++;
                                        $error[] = 'Zeile: '.($RunY + 1).' Der Sorgeberechtigte wurde nicht angelegt, da schon eine Person mit gleichen Namen und gleicher PLZ existiert.';
                                    }
                                } else {
                                    $error[] = 'Zeile: '.($RunY + 1).' Der Sorgeberechtigte1 wurde nicht angelegt, da sie keinen Namen und Vornamen hat.';
                                }

                                if ($StreetName && $StreetNumber && $cityCode && $cityName) {
                                    Address::useService()->insertAddressToPerson(
                                        $tblPerson, $StreetName, $StreetNumber, $cityCode, $cityName, $cityDistrict, ''
                                    );
                                } else {
                                    $error[] = 'Zeile: '.($RunY + 1).' Die Adresse des Interessenten wurde nicht angelegt, da sie keine vollständige Adresse besitzt.';
                                }

                                /*
                                * Phone
                                */
                                $phoneNumber = trim($Document->getValue($Document->getCell($Location['Telefon'],
                                    $RunY)));
                                if ($phoneNumber !== '') {
                                    $phoneNumberList = explode(',', $phoneNumber);
                                    foreach ($phoneNumberList as $phone) {
                                        $phone = trim($phone);
                                        $tblType = Phone::useService()->getTypeById(1);
                                        if (0 === strpos($phone, '01')) {
                                            $tblType = Phone::useService()->getTypeById(2);
                                        }
                                        Phone::useService()->insertPhoneToPerson(
                                            $tblPerson,
                                            $phone,
                                            $tblType,
                                            ''
                                        );
                                    }
                                }
                            }
                        } else {
                            $error[] = 'Zeile: '.($RunY + 1).' Der Schüler wurde nicht hinzugefügt, da er keinen Vornamen und/oder Namen besitzt.';
                        }
                    }

                    return
                        new Success('Es wurden '.$countInterestedPerson.' Intessenten erfolgreich angelegt.').
                        new Success('Es wurden '.$countFather.' Sorgeberechtigte1 erfolgreich angelegt.').
                        ($countFatherExists > 0 ?
                            new Warning($countFatherExists.' Sorgeberechtigte1 exisistieren bereits.') : '').
                        new Success('Es wurden '.$countMother.' Sorgeberechtigte2 erfolgreich angelegt.').
                        ($countMotherExists > 0 ?
                            new Warning($countMotherExists.' Sorgeberechtigte2 exisistieren bereits.') : '')
                        .new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                            new Panel(
                                'Fehler',
                                $error,
                                Panel::PANEL_TYPE_DANGER
                            )
                        ))));
                } else {
                    Debugger::screenDump($Location);

                    return new Warning(json_encode($Location)).new Danger(
                            "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }

        return new Danger('File nicht gefunden');
    }

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile        $File
     *
     * @return IFormInterface|Danger|string
     *
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createStaffFromFile(IFormInterface $Form = null, UploadedFile $File = null)
    {

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
                $File = $File->move($File->getPath(), $File->getFilename().'.'.$File->getClientOriginalExtension());
                /**
                 * Read
                 */
                /** @var PhpExcel $Document */
                $Document = Document::getDocument($File->getPathname());

                $X = $Document->getSheetColumnCount();
                $Y = $Document->getSheetRowCount();

                /**
                 * Header -> Location
                 */
                $Location = array(
                    'Name'          => null,
                    'Vorname'       => null,
                    'Straße'        => null,
                    'PLZ, Ort'      => null,
                    'Telefon'       => null,
                    'Telefon mobil' => null,
                    'E-Mail'        => null,
                    'Konf.'         => null,
                    'Fächer (EGT)'  => null,
                    'Geburtstag'    => null,
                    'Team'          => null,
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
                    $countStaff = 0;
                    $countStaffExists = 0;
                    $error = array();

                    $tblStaffGroup = Group::useService()->getGroupByMetaTable('STAFF');
                    $tblTeacherGroup = Group::useService()->getGroupByMetaTable('TEACHER');

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
                        if ($firstName !== '' && $lastName !== '') {

                            $city = trim($Document->getValue($Document->getCell($Location['PLZ, Ort'], $RunY)));
                            $cityName = '';
                            $cityCode = '';
                            if (preg_match('!(\d+)\s(\w+)!', $city, $matches)) {

                                if (isset($matches[1])) {
                                    $cityCode = trim($matches[1]);
                                }
                                if (isset($matches[2])) {
                                    $cityName = trim($matches[2]);
                                }
                            }

                            $tblPersonExits = Person::useService()->existsPerson(
                                $firstName,
                                $lastName,
                                $cityCode
                            );
                            $SubjectEGT = trim($Document->getValue($Document->getCell($Location['Fächer (EGT)'],
                                $RunY)));
                            if ($tblPersonExits) {

                                $error[] = 'Zeile: '.($RunY + 1).' Die Person wurde nicht angelegt, 
                                da schon eine Person mit gleichen Namen und gleicher PLZ existiert.';
                                Group::useService()->addGroupPerson($tblStaffGroup, $tblPersonExits);
                                if ($SubjectEGT !== '') {
                                    Group::useService()->addGroupPerson($tblTeacherGroup, $tblPersonExits);
                                }
                                $countStaffExists++;
                            } else {
                                if ($SubjectEGT != '') {
                                    $tblPerson = Person::useService()->insertPerson(
                                        null,
                                        '',
                                        $firstName,
                                        '',
                                        $lastName,
                                        array(
                                            0 => Group::useService()->getGroupByMetaTable('COMMON')->getId(),
                                            1 => $tblStaffGroup->getId(),
                                            2 => $tblTeacherGroup->getId()
                                        )
                                    );
                                } else {
                                    $tblPerson = Person::useService()->insertPerson(
                                        null,
                                        '',
                                        $firstName,
                                        '',
                                        $lastName,
                                        array(
                                            0 => Group::useService()->getGroupByMetaTable('COMMON')->getId(),
                                            1 => $tblStaffGroup->getId()
                                        )
                                    );
                                }

                                if ($tblPerson !== false) {
                                    $countStaff++;

                                    $day = trim($Document->getValue($Document->getCell($Location['Geburtstag'],
                                        $RunY)));
                                    if ($day !== '') {
                                        $birthday = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                                    } else {
                                        $birthday = '';
                                    }
                                    $denomination = trim($Document->getValue($Document->getCell($Location['Konf.'],
                                        $RunY)));

                                    $remark = '';
                                    $info = trim($Document->getValue($Document->getCell($Location['Fächer (EGT)'],
                                        $RunY)));
                                    if ($info !== '') {
                                        $remark = 'Fächer (EGT): '.$info;
                                    }
                                    $info = trim($Document->getValue($Document->getCell($Location['Team'], $RunY)));
                                    if ($info !== '') {
                                        $remark .= ($remark == '' ? '' : "\n").'Team: '.$info;
                                    }

                                    Common::useService()->insertMeta(
                                        $tblPerson,
                                        $birthday,
                                        '',
                                        TblCommonBirthDates::VALUE_GENDER_NULL,
                                        '',
                                        $denomination,
                                        TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                        '',
                                        $remark
                                    );

                                    // Address
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
                                    if ($streetName && $streetNumber && $cityCode && $cityName) {
                                        Address::useService()->insertAddressToPerson(
                                            $tblPerson, $streetName, $streetNumber, $cityCode, $cityName, '', ''
                                        );
                                    } else {
                                        $error[] = 'Zeile: '.($RunY + 1).' Die Adresse der Person wurde nicht angelegt, 
                                        da sie keine vollständige Adresse besitzt.';
                                    }

                                    $phoneNumber = trim($Document->getValue($Document->getCell($Location['Telefon'],
                                        $RunY)));
                                    if ($phoneNumber != '') {
                                        $tblType = Phone::useService()->getTypeById(1);
                                        if (0 === strpos($phoneNumber, '01')) {
                                            $tblType = Phone::useService()->getTypeById(2);
                                        }
                                        Phone::useService()->insertPhoneToPerson(
                                            $tblPerson,
                                            $phoneNumber,
                                            $tblType,
                                            ''
                                        );
                                    }

                                    $phoneNumber = trim($Document->getValue($Document->getCell($Location['Telefon mobil'],
                                        $RunY)));
                                    if ($phoneNumber != '') {
                                        $tblType = Phone::useService()->getTypeById(1);
                                        if (0 === strpos($phoneNumber, '01')) {
                                            $tblType = Phone::useService()->getTypeById(2);
                                        }
                                        Phone::useService()->insertPhoneToPerson(
                                            $tblPerson,
                                            $phoneNumber,
                                            $tblType,
                                            ''
                                        );
                                    }

                                    $mailAddress = trim($Document->getValue($Document->getCell($Location['E-Mail'],
                                        $RunY)));
                                    if ($mailAddress != '') {
                                        Mail::useService()->insertMailToPerson(
                                            $tblPerson,
                                            $mailAddress,
                                            Mail::useService()->getTypeById(1),
                                            ''
                                        );
                                    }
                                }
                            }
                        } else {
                            $error[] = 'Zeile: '.($RunY + 1).' Die Person wurde nicht angelegt, da sie keinen Namen und Vornamen hat.';
                        }
                    }

                    Debugger::screenDump($error);

                    return
                        new Success('Es wurden '.$countStaff.' Mitarbeiter erfolgreich angelegt.').
                        ($countStaffExists > 0 ?
                            new Warning($countStaffExists.' Mitarbeiter exisistieren bereits.') : '')
                        .(empty($error) ? '' : new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                                new Panel(
                                    'Fehler',
                                    $error,
                                    Panel::PANEL_TYPE_DANGER
                                ))
                        ))));

                } else {
                    Debugger::screenDump($Location);

                    return new Warning(json_encode($Location)).new Danger(
                            "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }

        return new Danger('File nicht gefunden');
    }
}