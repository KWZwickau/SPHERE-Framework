<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 04.03.2019
 * Time: 08:36
 */

namespace SPHERE\Application\Transfer\Import\Naundorf;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Club\Club;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonInformation;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Service
 *
 * @package SPHERE\Application\Transfer\Import\Naundorf
 */
class Service
{

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile $File
     *
     * @return IFormInterface|Danger|string
     */
    public function createStaffsFromFile(IFormInterface $Form = null, UploadedFile $File = null)
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
                $File = $File->move($File->getPath(), $File->getFilename() . '.' . $File->getClientOriginalExtension());
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
                    'Name' => null,
                    'Vorname' => null,
                    'Kürzel' => null,
                    'Geburtsdatum' => null,
                    'Fach' => null,
                    'Adresse' => null,
                    'PLZ' => null,
                    'Wohnort' => null,
                    'Festnetz' => null,
                    'Mobil' => null,
                    'Mail privat' => null,
                    'Mail dienstl.' => null
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
                        if ($firstName !== '' && $lastName !== '' && $firstName !== 'Vorname') {

                            // Address
                            $cityCode = str_pad(
                                trim($Document->getValue($Document->getCell($Location['PLZ'], $RunY))),
                                5,
                                "0",
                                STR_PAD_LEFT
                            );
                            $cityName = trim($Document->getValue($Document->getCell($Location['Wohnort'], $RunY)));
                            $cityDistrict = '';
                            $pos = strpos($cityName, " OT ");
                            if ($pos !== false) {
                                $cityDistrict = trim(substr($cityName, $pos + 4));
                                $cityName = trim(substr($cityName, 0, $pos));
                            }
                            $streetName = '';
                            $streetNumber = '';
                            $street = trim($Document->getValue($Document->getCell($Location['Adresse'],
                                $RunY)));
                            if (preg_match_all('!\d+!', $street, $matches)) {
                                $pos = strpos($street, $matches[0][0]);
                                if ($pos !== null) {
                                    $streetName = trim(substr($street, 0, $pos));
                                    $streetNumber = trim(substr($street, $pos));
                                }
                            }

                            $tblPersonExits = Person::useService()->existsPerson(
                                $firstName,
                                $lastName,
                                $cityCode
                            );

                            $infoSubjects = trim($Document->getValue($Document->getCell($Location['Fach'],
                                $RunY)));
                            $acronym = trim($Document->getValue($Document->getCell($Location['Kürzel'],
                                $RunY)));

                            if ($tblPersonExits) {

                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person wurde nicht angelegt, da schon eine Person mit gleichen Namen und gleicher PLZ existiert.';

                                Group::useService()->addGroupPerson($tblStaffGroup, $tblPersonExits);
                                Group::useService()->addGroupPerson($tblTeacherGroup, $tblPersonExits);

                                Teacher::useService()->insertTeacher($tblPersonExits, $acronym);

                                $countStaffExists++;
                            } else {
                                $groupArray = array();
                                $groupArray[] = Group::useService()->getGroupByMetaTable('COMMON');
                                $groupArray[] = $tblStaffGroup;
                                $groupArray[] = $tblTeacherGroup;

                                $tblPerson = Person::useService()->insertPerson(
                                    null,
                                    '',
                                    $firstName,
                                    '',
                                    $lastName,
                                    $groupArray
                                );

                                if ($tblPerson !== false) {
                                    $countStaff++;

                                    $day = trim($Document->getValue($Document->getCell($Location['Geburtsdatum'],
                                        $RunY)));
                                    if ($day !== '') {
                                        $birthday = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                                    } else {
                                        $birthday = '';
                                    }

                                    Common::useService()->insertMeta(
                                        $tblPerson,
                                        $birthday,
                                        '',
                                        TblCommonBirthDates::VALUE_GENDER_NULL,
                                        '',
                                        '',
                                        TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                        '',
                                        $infoSubjects !== '' ? 'Fächer: ' . $infoSubjects : ''
                                    );

                                    Teacher::useService()->insertTeacher($tblPerson, $acronym);

                                    // Address
                                    if ($streetName && $streetNumber && $cityCode && $cityName) {
                                        Address::useService()->insertAddressToPerson(
                                            $tblPerson, $streetName, $streetNumber, $cityCode, $cityName, $cityDistrict, ''
                                        );
                                    } else {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse der Person wurde nicht angelegt, da sie keine vollständige Adresse besitzt.';
                                    }

                                    $phoneNumber = trim($Document->getValue($Document->getCell($Location['Festnetz'],
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

                                    $phoneNumber = trim($Document->getValue($Document->getCell($Location['Mobil'],
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

                                    $mailAddress = trim($Document->getValue($Document->getCell($Location['Mail privat'],
                                        $RunY)));
                                    if ($mailAddress != '') {
                                        Mail::useService()->insertMailToPerson(
                                            $tblPerson,
                                            $mailAddress,
                                            Mail::useService()->getTypeById(1),
                                            ''
                                        );
                                    }

                                    $mailAddress = trim($Document->getValue($Document->getCell($Location['Mail dienstl.'],
                                        $RunY)));
                                    if ($mailAddress != '') {
                                        Mail::useService()->insertMailToPerson(
                                            $tblPerson,
                                            $mailAddress,
                                            Mail::useService()->getTypeById(2),
                                            ''
                                        );
                                    }
                                }
                            }
                        } else {
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person wurde nicht angelegt, da sie keinen Namen und Vornamen hat.';
                        }
                    }

                    return
                        new Success('Es wurden ' . $countStaff . ' Mitarbeiter erfolgreich angelegt.') .
                        ($countStaffExists > 0 ?
                            new Warning($countStaffExists . ' Mitarbeiter exisistieren bereits.') : '')
                        . new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                            new Panel(
                                'Fehler',
                                $error,
                                Panel::PANEL_TYPE_DANGER
                            )
                        ))));

                } else {
                    return new Warning(json_encode($Location)) . new Danger(
                            "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }

        return new Danger('File nicht gefunden');
    }

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null $File
     *
     * @return IFormInterface|Danger|string
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createInterestedPersonsFromFile(
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
                    'Schuljahr' => null,
                    'Name' => null,
                    'Vorname' => null,
                    'Geburtsdatum' => null,
                    'bisherige GS' => null,
                    'Sorg 1 Vorname' => null,
                    'Sorg 1 Nachname' => null,
                    'Sorg 2 Vorname' => null,
                    'Sorg 2 Nachname' => null,
                    'Adresse' => null,
                    'PLZ/Ort' => null,
                    'Telefon1' => null,
                    'Telefon2' => null,
                    'Email' => null,
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

                    $levelName = '5';
                    $tblSchoolType = Type::useService()->getTypeByName('Mittelschule / Oberschule');

                    for ($RunY = 1; $RunY < $Y; $RunY++) {

                        // InterestedPerson
                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));

                        if ($firstName !== '' && $lastName !== '') {
                            $tblPerson = Person::useService()->insertPerson(
                                null,
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

                                $cityName = trim($Document->getValue($Document->getCell($Location['PLZ/Ort'], $RunY)));
                                $cityCode = '';
                                $cityDistrict = '';
                                $pos = strpos($cityName, " ");
                                if ($pos !== false) {
                                    $cityCode = trim(substr($cityName, 0, $pos));
                                    $cityName = trim(substr($cityName, $pos + 1));

                                    $pos = strpos($cityName, " OT ");
                                    if ($pos !== false) {
                                        $cityDistrict = trim(substr($cityName, $pos + 4));
                                        $cityName = trim(substr($cityName, 0, $pos));
                                    }
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

                                if ($StreetName && $StreetNumber && $cityCode && $cityName) {
                                    Address::useService()->insertAddressToPerson(
                                        $tblPerson, $StreetName, $StreetNumber, $cityCode, $cityName,
                                        $cityDistrict, ''
                                    );
                                } else {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse des Interessenten wurde nicht angelegt, da sie keine vollständige Adresse besitzt.';
                                }

                                $day = trim($Document->getValue($Document->getCell($Location['Geburtsdatum'], $RunY)));
                                if ($day !== '') {
                                    $birthday = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                                } else {
                                    $birthday = '';
                                }

                                $remark = '';
                                $primarySchool = trim($Document->getValue($Document->getCell($Location['bisherige GS'],
                                    $RunY)));
                                if ($primarySchool !== '') {
                                    $remark = 'bisherige GS: ' . $primarySchool;
                                }

                                Common::useService()->insertMeta(
                                    $tblPerson,
                                    $birthday,
                                    '',
                                    '',
                                    '',
                                    '',
                                    TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                    '',
                                    ''
                                );

                                Prospect::useService()->insertMeta(
                                    $tblPerson,
                                    '',
                                    '',
                                    '',
                                    trim($Document->getValue($Document->getCell($Location['Schuljahr'], $RunY))),
                                    $levelName,
                                    $tblSchoolType ? $tblSchoolType : null,
                                    null,
                                    $remark
                                );

                                $tblRelationshipTypeCustody = Relationship::useService()->getTypeById(1);

                                // Custody1
                                $tblPersonCustody1 = null;
                                $firstNameCustody1 = trim($Document->getValue($Document->getCell($Location['Sorg 1 Vorname'],
                                    $RunY)));
                                $lastNameCustody1 = trim($Document->getValue($Document->getCell($Location['Sorg 1 Nachname'],
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

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonCustody1,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            ''
                                        );

                                        // Address
                                        if ($StreetName && $StreetNumber && $cityCode && $cityName) {
                                            Address::useService()->insertAddressToPerson(
                                                $tblPersonCustody1, $StreetName, $StreetNumber, $cityCode,
                                                $cityName,
                                                $cityDistrict, ''
                                            );
                                        } else {
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse des Sorgeberechtigen1 wurde nicht angelegt, da sie keine vollständige Adresse besitzt.';
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
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Sorgeberechtigte1 wurde nicht angelegt, da schon eine Person mit gleichen Namen und gleicher PLZ existiert.';
                                    }
                                } else {
//                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Sorgeberechtigte1 wurde nicht angelegt, da sie keinen Namen und Vornamen hat.';
                                }

                                // Custody2
                                $tblPersonCustody2 = null;
                                $firstNameCustody2 = trim($Document->getValue($Document->getCell($Location['Sorg 2 Vorname'],
                                    $RunY)));
                                $lastNameCustody2 = trim($Document->getValue($Document->getCell($Location['Sorg 2 Nachname'],
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

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonCustody2,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            ''
                                        );

                                        if ($StreetName && $StreetNumber && $cityCode && $cityName) {
                                            Address::useService()->insertAddressToPerson(
                                                $tblPersonCustody2, $StreetName, $StreetNumber, $cityCode,
                                                $cityName,
                                                $cityDistrict, ''
                                            );
                                        } else {
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse des Sorgeberechtigen2 wurde nicht angelegt, da sie keine vollständige Adresse besitzt.';
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
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Sorgeberechtigte2 wurde nicht angelegt, da schon eine Person mit gleichen Namen und gleicher PLZ existiert.';
                                    }
                                } else {
//                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Sorgeberechtigte2 wurde nicht angelegt, da sie keinen Namen und Vornamen hat.';
                                }

                                /*
                                * Phone
                                */
                                $phone = trim($Document->getValue($Document->getCell($Location['Telefon1'],
                                    $RunY)));
                                if ($phone !== '') {
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
                                $phone = trim($Document->getValue($Document->getCell($Location['Telefon2'],
                                    $RunY)));
                                if ($phone !== '') {
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

                                /*
                                 * Email
                                 */
                                $mailAddress = trim($Document->getValue($Document->getCell($Location['Email'],
                                    $RunY)));
                                $tblMailType = Mail::useService()->getTypeById(1);
                                if ($mailAddress !== '' && $tblMailType) {
                                    Mail::useService()->insertMailToPerson($tblPerson, $mailAddress, $tblMailType, '');
                                }
                            }
                        } else {
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Interessent wurde nicht hinzugefügt, da er keinen Vornamen und/oder Namen besitzt.';
                        }
                    }

                    return
                        new Success('Es wurden ' . $countInterestedPerson . ' Intessenten erfolgreich angelegt.')
                        . new Success('Es wurden ' . $countFather . ' Sorgeberechtigte1 erfolgreich angelegt.') .
                        ($countFatherExists > 0 ?
                            new Warning($countFatherExists . ' Sorgeberechtigte1 exisistieren bereits.') : '') .
                        new Success('Es wurden ' . $countMother . ' Sorgeberechtigte2 erfolgreich angelegt.') .
                        ($countMotherExists > 0 ?
                            new Warning($countMotherExists . ' Sorgeberechtigte2 exisistieren bereits.') : '')
                        . new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                            new Panel(
                                'Fehler',
                                $error,
                                Panel::PANEL_TYPE_DANGER
                            )
                        ))));
                } else {

                    return new Warning(json_encode($Location)) . new Danger(
                            "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }

        return new Danger('File nicht gefunden');
    }

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
                    'Klasse' => null,
                    'Name' => null,
                    'Vorname' => null,
                    'Geburtsdatum' => null,
                    'Strasse' => null,
                    'PLZ' => null,
                    'Wohnort' => null,
                    'Mail1' => null,
                    'Mail2' => null,
                    'Festnetz priv.' => null,
                    'Mobil priv.' => null,
                    'Festnetz dienstl.' => null,
                    'Mobil dienstl.' => null,
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
                    $error = array();

                    // insert Schuljahr
                    $year = 18;
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
                    }

                    $tblSchoolType = Type::useService()->getTypeByName('Mittelschule / Oberschule');

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        set_time_limit(300);
                        // Student
                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
                        if ($firstName === '' || $lastName === '') {
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler wurde nicht hinzugefügt, da er keinen Vornamen und/oder Namen besitzt.';
                        } else {

                            $tblPerson = Person::useService()->insertPerson(
                                null,
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

                                $day = trim($Document->getValue($Document->getCell($Location['Geburtsdatum'],
                                    $RunY)));
                                if ($day !== '') {
//                                    $birthday = $day;
                                    try {
                                        $birthday = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                                    } catch (\Exception $ex) {
                                        $birthday = '';
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Ungültiges Geburtsdatum: ' . $ex->getMessage();
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
                                    '',
                                    TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                    '',
                                    ''
                                );

                                // division
                                $level = trim($Document->getValue($Document->getCell($Location['Klasse'],
                                    $RunY)));
                                if ($level !== '' && $tblYear && $tblSchoolType) {
                                    $tblLevel = Division::useService()->insertLevel($tblSchoolType, $level);
                                    if ($tblLevel) {
                                        $tblDivision = Division::useService()->insertDivision(
                                            $tblYear,
                                            $tblLevel,
                                            ''
                                        );

                                        if ($tblDivision) {
                                            Division::useService()->insertDivisionStudent($tblDivision, $tblPerson);
                                        } else {
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler konnte keiner Klasse zugeordnet werden.';
                                        }
                                    }
                                }

                                // Address
                                $studentCityCode = str_pad(
                                    trim($Document->getValue($Document->getCell($Location['PLZ'], $RunY))),
                                    5,
                                    "0",
                                    STR_PAD_LEFT
                                );
                                $cityName = trim($Document->getValue($Document->getCell($Location['Wohnort'], $RunY)));
                                $cityDistrict = '';
                                $pos = strpos($cityName, " OT ");
                                if ($pos !== false) {
                                    $cityDistrict = trim(substr($cityName, $pos + 4));
                                    $cityName = trim(substr($cityName, 0, $pos));
                                }
                                $streetName = '';
                                $streetNumber = '';
                                $street = trim($Document->getValue($Document->getCell($Location['Strasse'],
                                    $RunY)));
                                if (preg_match_all('!\d+!', $street, $matches)) {
                                    $pos = strpos($street, $matches[0][0]);
                                    if ($pos !== null) {
                                        $streetName = trim(substr($street, 0, $pos));
                                        $streetNumber = trim(substr($street, $pos));
                                    }
                                }
                                $county = '';
                                $state = '';
                                if ($state != '') {
                                    $tblState = Address::useService()->getStateByName($state);
                                } else {
                                    $tblState = false;
                                }
                                if ($streetName !== '' && $streetNumber !== ''
                                    && $studentCityCode && $cityName
                                ) {
                                    Address::useService()->insertAddressToPerson(
                                        $tblPerson, $streetName, $streetNumber, $studentCityCode, $cityName,
                                        $cityDistrict, '', $county, '', $tblState ? $tblState : null
                                    );
                                }

                                /*
                                * Phone
                                */
                                $phoneNumber = trim($Document->getValue($Document->getCell($Location['Festnetz priv.'],
                                    $RunY)));
                                if ($phoneNumber !== '') {
                                    $phoneNumberList = explode(',', $phoneNumber);
                                    foreach ($phoneNumberList as $phone) {
                                        $phone = trim($phone);
                                        $remark = '';
                                        if (($pos = strpos($phone, ' ')) !== false) {
                                            $remark = trim(substr($phone, $pos));
                                            $phone = trim(substr($phone, 0, $pos));
                                        }
                                        $tblType = Phone::useService()->getTypeById(1);

                                        Phone::useService()->insertPhoneToPerson(
                                            $tblPerson,
                                            $phone,
                                            $tblType,
                                            $remark
                                        );
                                    }
                                }

                                $phoneNumber = trim($Document->getValue($Document->getCell($Location['Mobil priv.'],
                                    $RunY)));
                                if ($phoneNumber !== '') {
                                    $phoneNumberList = explode(',', $phoneNumber);
                                    foreach ($phoneNumberList as $phone) {
                                        $phone = trim($phone);
                                        $remark = '';
                                        if (($pos = strpos($phone, ' ')) !== false) {
                                            $remark = trim(substr($phone, $pos));
                                            $phone = trim(substr($phone, 0, $pos));
                                        }
                                        $tblType = Phone::useService()->getTypeById(2);

                                        Phone::useService()->insertPhoneToPerson(
                                            $tblPerson,
                                            $phone,
                                            $tblType,
                                            $remark
                                        );
                                    }
                                }

                                $phoneNumber = trim($Document->getValue($Document->getCell($Location['Festnetz dienstl.'],
                                    $RunY)));
                                if ($phoneNumber !== '') {
                                    $phoneNumberList = explode(',', $phoneNumber);
                                    foreach ($phoneNumberList as $phone) {
                                        $phone = trim($phone);
                                        $remark = '';
                                        if (($pos = strpos($phone, ' ')) !== false) {
                                            $remark = trim(substr($phone, $pos));
                                            $phone = trim(substr($phone, 0, $pos));
                                        }
                                        $tblType = Phone::useService()->getTypeById(3);

                                        Phone::useService()->insertPhoneToPerson(
                                            $tblPerson,
                                            $phone,
                                            $tblType,
                                            $remark
                                        );
                                    }
                                }

                                $phoneNumber = trim($Document->getValue($Document->getCell($Location['Mobil dienstl.'],
                                    $RunY)));
                                if ($phoneNumber !== '') {
                                    $phoneNumberList = explode(',', $phoneNumber);
                                    foreach ($phoneNumberList as $phone) {
                                        $phone = trim($phone);
                                        $remark = '';
                                        if (($pos = strpos($phone, ' ')) !== false) {
                                            $remark = trim(substr($phone, $pos));
                                            $phone = trim(substr($phone, 0, $pos));
                                        }
                                        $tblType = Phone::useService()->getTypeById(4);

                                        Phone::useService()->insertPhoneToPerson(
                                            $tblPerson,
                                            $phone,
                                            $tblType,
                                            $remark
                                        );
                                    }
                                }

                                $mailAddress = trim($Document->getValue($Document->getCell($Location['Mail1'],
                                    $RunY)));
                                if ($mailAddress != '') {
                                    Mail::useService()->insertMailToPerson(
                                        $tblPerson,
                                        $mailAddress,
                                        Mail::useService()->getTypeById(1),
                                        ''
                                    );
                                }
                                $mailAddress = trim($Document->getValue($Document->getCell($Location['Mail2'],
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
                    }

                    return
                        new Success('Es wurden ' . $countStudent . ' Schüler erfolgreich angelegt.')
                        . new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                            new Panel(
                                'Fehler',
                                $error,
                                Panel::PANEL_TYPE_DANGER
                            )
                        ))));

                } else {
                    return new Warning(json_encode($Location)) . new Danger(
                            "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }

        return new Danger('File nicht gefunden');
    }

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null $File
     *
     * @return IFormInterface|Danger|string
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createStudentMetasFromFile(
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
                    'Name' => null,
                    'Vorname' => null,
                    'Geburtsdatum' => null,
                    'Geburtsort' => null,
                    'Konfession' => null,
                    'Quereinstieg' => null
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
                    $error = array();

                    $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE');

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        set_time_limit(300);
                        // Student
                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
                        $day = trim($Document->getValue($Document->getCell($Location['Geburtsdatum'],
                            $RunY)));
                        if ($firstName === '' || $lastName === '' || $day === '') {
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler wurde nicht hinzugefügt, da er keinen Vornamen und/oder Namen besitzt.';
                        } else {
                            try {
                                $birthday = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                            } catch (\Exception $ex) {
                                $birthday = '';
                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Ungültiges Geburtsdatum: ' . $ex->getMessage();
                            }

                            if ($birthday !== '') {
                                if (($tblPersonList = Person::useService()->getPersonAllByNameAndBirthday($firstName, $lastName, $birthday))) {
                                    $countPersons = count($tblPersonList);
                                } else {
                                    $countPersons = 0;
                                }

                                if ($countPersons == 1) {
                                    // meta daten setzen
                                    /** @var TblPerson $tblPerson */
                                    $tblPerson = reset($tblPersonList);
                                    if ($tblPerson) {
                                        $Meta = array();
                                        $Meta['BirthDates']['Birthday'] = $birthday;
                                        $Meta['BirthDates']['Birthplace'] = trim($Document->getValue($Document->getCell($Location['Geburtsort'], $RunY)));
                                        $Meta['BirthDates']['Gender'] = '';
                                        $Meta['Information']['Nationality'] = '';
                                        $Meta['Information']['Denomination'] = trim($Document->getValue($Document->getCell($Location['Konfession'], $RunY)));
                                        $Meta['Information']['IsAssistance'] = '';
                                        $Meta['Information']['AssistanceActivity'] = '';
                                        $Meta['Remark'] = '';

                                        $entrance = trim($Document->getValue($Document->getCell($Location['Quereinstieg'], $RunY)));
                                        if ($entrance != '') {
                                            $entrance = str_replace('ab ', '', $entrance);
                                            // php kommt nicht mit 2 stelligen Jahreszahlen klar :(
                                            if (($split = preg_split('/[.]/', $entrance))) {
                                                $entrance = $split[0] . '.' . $split[1] . '.' . '20' . $split[2];
                                            }

                                            if (($tblStudent = Student::useService()->insertStudent($tblPerson, ''))){
                                                Student::useService()->insertStudentTransfer(
                                                    $tblStudent,
                                                    $tblStudentTransferType,
                                                    null,
                                                    null,
                                                    null,
                                                    $entrance,
                                                    'Quereinstieg'
                                                );
                                            }
                                        }

                                        if ((Common::useService()->updateMetaService($tblPerson, $Meta))) {
                                            $countStudent++;
                                        }
                                    }
                                } elseif ($countPersons > 1) {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Es wurden mehrere Personen gefunden: ' . implode(', ', $tblPersonList);
                                } else {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Es wurden keine Person gefunden: ' . $firstName . ' ' . $lastName;
                                }
                            }
                        }
                    }

                    return
                        new Success('Es wurden ' . $countStudent . ' Schüler-Meta-Daten erfolgreich angelegt.')
                        . new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                            new Panel(
                                'Fehler',
                                $error,
                                Panel::PANEL_TYPE_DANGER
                            )
                        ))));

                } else {
                    return new Warning(json_encode($Location)) . new Danger(
                            "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }

        return new Danger('File nicht gefunden');
    }

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile $File
     *
     * @return IFormInterface|Danger|string
     */
    public function createClubsFromFile(IFormInterface $Form = null, UploadedFile $File = null)
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
                $File = $File->move($File->getPath(), $File->getFilename() . '.' . $File->getClientOriginalExtension());
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
                    'MNr' => null,
                    'Name' => null,
                    'Vorname' => null,
                    'Geburtsdatum' => null,
                    'Straße' => null,
                    'PLZ, Ort' => null,
                    'Telefon' => null,
                    'E-Mail' => null,
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
                    $countClub = 0;
                    $countClubExists = 0;
                    $error = array();

                    $tblClubGroup = Group::useService()->getGroupByMetaTable('CLUB');

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
                        if ($firstName !== '' && $lastName !== '' && $firstName !== 'Vorname') {

                            // Address
                            $cityName = trim($Document->getValue($Document->getCell($Location['PLZ, Ort'], $RunY)));
                            $cityCode = '';
                            $cityDistrict = '';
                            $pos = strpos($cityName, " ");
                            if ($pos !== false) {
                                $cityCode = trim(substr($cityName, 0, $pos));
                                $cityName = trim(substr($cityName, $pos + 1));

                                $pos = strpos($cityName, " OT ");
                                if ($pos !== false) {
                                    $cityDistrict = trim(substr($cityName, $pos + 4));
                                    $cityName = trim(substr($cityName, 0, $pos));
                                }
                            }

                            $streetName = '';
                            $streetNumber = '';
                            $Street = trim($Document->getValue($Document->getCell($Location['Straße'],
                                $RunY)));
                            if (preg_match_all('!\d+!', $Street, $matches)) {
                                $pos = strpos($Street, $matches[0][0]);
                                if ($pos !== null) {
                                    $streetName = trim(substr($Street, 0, $pos));
                                    $streetNumber = trim(substr($Street, $pos));
                                }
                            }

                            $tblPersonExits = Person::useService()->existsPerson(
                                $firstName,
                                $lastName,
                                $cityCode
                            );

                            if ($tblPersonExits) {

                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person wurde nicht angelegt, da schon eine Person mit gleichen Namen und gleicher PLZ existiert.';
                                Group::useService()->addGroupPerson($tblClubGroup, $tblPersonExits);
                                Club::useService()->insertMeta($tblPersonExits,
                                    trim($Document->getValue($Document->getCell($Location['MNr'],
                                        $RunY))));
                                $countClubExists++;

                            } else {
                                $groupArray = array();
                                $groupArray[] = Group::useService()->getGroupByMetaTable('COMMON');
                                $groupArray[] = $tblClubGroup;

                                $tblPerson = Person::useService()->insertPerson(
                                    null,
                                    '',
                                    $firstName,
                                    '',
                                    $lastName,
                                    $groupArray
                                );

                                if ($tblPerson !== false) {
                                    $countClub++;

                                    $day = trim($Document->getValue($Document->getCell($Location['Geburtsdatum'],
                                        $RunY)));
                                    if ($day !== '') {
                                        $birthday = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                                    } else {
                                        $birthday = '';
                                    }

                                    Common::useService()->insertMeta(
                                        $tblPerson,
                                        $birthday,
                                        '',
                                        TblCommonBirthDates::VALUE_GENDER_NULL,
                                        '',
                                        '',
                                        TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                        '',
                                        ''
                                    );

                                    Club::useService()->insertMeta($tblPerson,
                                        trim($Document->getValue($Document->getCell($Location['MNr'],
                                            $RunY))));

                                    // Address
                                    if ($streetName && $streetNumber && $cityCode && $cityName) {
                                        Address::useService()->insertAddressToPerson(
                                            $tblPerson, $streetName, $streetNumber, $cityCode, $cityName, $cityDistrict,
                                            ''
                                        );
                                    } else {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse der Person wurde nicht angelegt, da sie keine vollständige Adresse besitzt.';
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
                        }
//                        } else {
//                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person wurde nicht angelegt, da sie keinen Namen und Vornamen hat.';
//                        }
                    }

                    return
                        new Success('Es wurden ' . $countClub . ' Vereinsmitglieder erfolgreich angelegt.') .
                        ($countClubExists > 0 ?
                            new Warning($countClubExists . ' Vereinsmitglieder exisistieren bereits.') : '')
                        . new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                            new Panel(
                                'Fehler',
                                $error,
                                Panel::PANEL_TYPE_DANGER
                            )
                        ))));

                } else {
                    return new Warning(json_encode($Location)) . new Danger(
                            "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }

        return new Danger('File nicht gefunden');
    }
}