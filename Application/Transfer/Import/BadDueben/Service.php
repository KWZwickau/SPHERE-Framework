<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 28.09.2018
 * Time: 11:32
 */

namespace SPHERE\Application\Transfer\Import\BadDueben;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonInformation;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Person\Person;
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
 * @package SPHERE\Application\Transfer\Import\BadDueben
 */
class Service
{

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
                    'G' => null, // Geschlecht
                    'Name' => null,
                    'Vorname' => null,
                    'Straße' => null,
                    'Ort' => null,
                    'Geburtstag' => null,
                    'Konfession' => null,
                    'Telefon' => null,
                    'Bemerkung' => null,
                    'email' => null,
                );

                $optionalLocation = array(
                    // Klasse 1
                    'staatl. GS' => null,
                    'Kindergarten' => null,
                    'GeS Name' => null,

                    // Klasse 5
                    'Eingang Anmeldung' => null,
                    'OS' => null,
                    'Gym' => null,
                    'Name der bisher besuchten Schule' => null
                );

                for ($RunX = 0; $RunX < $X; $RunX++) {
                    $Value = trim($Document->getValue($Document->getCell($RunX, 0)));
                    if (array_key_exists($Value, $Location)) {
                        $Location[$Value] = $RunX;
                    }
                    if (array_key_exists($Value, $optionalLocation)) {
                        $optionalLocation[$Value] = $RunX;
                    }
                }

                /**
                 * Import
                 */
                if (!in_array(null, $Location, true)) {
                    $countInterestedPerson = 0;
//                    $countFather = 0;
//                    $countMother = 0;
//                    $countFatherExists = 0;
//                    $countMotherExists = 0;

                    $error = array();

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

                                $cityName = trim($Document->getValue($Document->getCell($Location['Ort'], $RunY)));
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
                                $Street = trim($Document->getValue($Document->getCell($Location['Straße'],
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

                                $day = trim($Document->getValue($Document->getCell($Location['Geburtstag'], $RunY)));
                                if ($day !== '') {
                                    $birthday = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                                } else {
                                    $birthday = '';
                                }

                                $gender = trim($Document->getValue($Document->getCell($Location['G'], $RunY)));
                                if ($gender == 'm') {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_MALE;
                                } elseif ($gender == 'w') {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_FEMALE;
                                }

                                $remarkCommon = '';
                                if ($optionalLocation['staatl. GS'] != null && $optionalLocation['Kindergarten'] != null) {
                                    $infoSchool = trim($Document->getValue($Document->getCell($optionalLocation['staatl. GS'],
                                        $RunY)));
                                    if ($infoSchool !== '') {
                                        $remarkCommon = 'staatliche GS: ' . $infoSchool;
                                    }
                                    $infoNursery = trim($Document->getValue($Document->getCell($optionalLocation['Kindergarten'],
                                        $RunY)));
                                    if ($infoNursery !== '') {
                                        $remarkCommon .= ($remarkCommon == '' ? '' : " \n") . 'Kindergarten: ' . $infoNursery;
                                    }
                                } elseif ($optionalLocation['Name der bisher besuchten Schule'] != null) {
                                    $infoSchool = trim($Document->getValue($Document->getCell($optionalLocation['Name der bisher besuchten Schule'],
                                        $RunY)));
                                    if ($infoSchool !== '') {
                                        $remarkCommon = 'Name der bisher besuchten Schule: ' . $infoSchool;
                                    }
                                }

                                Common::useService()->insertMeta(
                                    $tblPerson,
                                    $birthday,
                                    '',
                                    $gender,
                                    '',
                                    trim($Document->getValue($Document->getCell($Location['Konfession'], $RunY))),
                                    TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                    '',
                                    $remarkCommon
                                );

                                $remark = '';
                                if ($optionalLocation['GeS Name'] != null) {
                                    $info = trim($Document->getValue($Document->getCell($optionalLocation['GeS Name'],
                                        $RunY)));
                                    if ($info !== '') {
                                        $remark = 'Geschwister: ' . $info;
                                    }
                                }
                                $info = trim($Document->getValue($Document->getCell($Location['Bemerkung'], $RunY)));
                                if ($info !== '') {
                                    $remark .= ($remark == '' ? '' : " \n") . 'Bemerkung: ' . $info;
                                }

                                $reservationDate = '';
                                if ($optionalLocation['Eingang Anmeldung'] != null) {
                                    $day = trim($Document->getValue($Document->getCell($optionalLocation['Eingang Anmeldung'],
                                        $RunY)));
                                    if ($day !== '') {
                                        $reservationDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                                    }
                                }

                                if ($optionalLocation['OS'] != null && $optionalLocation['Gym'] != null) {
                                    $level = 5;
                                    $os = trim($Document->getValue($Document->getCell($optionalLocation['OS'],
                                        $RunY)));
                                    if ($os == 'x') {
                                        $tblSchoolType = Type::useService()->getTypeByName('Mittelschule / Oberschule');
                                    }
                                    $gym = trim($Document->getValue($Document->getCell($optionalLocation['Gym'],
                                        $RunY)));
                                    if ($gym == 'x') {
                                        $tblSchoolType = Type::useService()->getTypeByName('Gymnasium');
                                    }
                                } else {
                                    $level = 1;
                                    $tblSchoolType = Type::useService()->getTypeByName('Grundschule');
                                }
                                Prospect::useService()->insertMeta(
                                    $tblPerson,
                                    $reservationDate,
                                    '',
                                    '',
                                    '2019/20',
                                    $level,
                                    $tblSchoolType ? $tblSchoolType : null,
                                    null,
                                    $remark
                                );

//                                $tblRelationshipTypeCustody = Relationship::useService()->getTypeById(1);
//
//                                // Custody1
//                                $tblPersonCustody1 = null;
//                                $firstNameCustody1 = trim($Document->getValue($Document->getCell($Location['Sorgeber 1 Vorname'],
//                                    $RunY)));
//                                $lastNameCustody1 = trim($Document->getValue($Document->getCell($Location['Sorgeber 1 Nachname'],
//                                    $RunY)));
//
//                                if ($firstNameCustody1 !== '' && $lastNameCustody1 !== '') {
//                                    $tblPersonCustody1Exists = Person::useService()->existsPerson(
//                                        $firstNameCustody1,
//                                        $lastNameCustody1,
//                                        $cityCode
//                                    );
//
//                                    if (!$tblPersonCustody1Exists) {
//                                        $tblPersonCustody1 = Person::useService()->insertPerson(
//                                            null,
//                                            '',
//                                            $firstNameCustody1,
//                                            '',
//                                            $lastNameCustody1,
//                                            array(
//                                                0 => Group::useService()->getGroupByMetaTable('COMMON'),
//                                                1 => Group::useService()->getGroupByMetaTable('CUSTODY')
//                                            )
//                                        );
//
//                                        Relationship::useService()->insertRelationshipToPerson(
//                                            $tblPersonCustody1,
//                                            $tblPerson,
//                                            $tblRelationshipTypeCustody,
//                                            ''
//                                        );
//
//                                        // Address
//                                        if ($StreetName && $StreetNumber && $cityCode && $cityName) {
//                                            Address::useService()->insertAddressToPerson(
//                                                $tblPersonCustody1, $StreetName, $StreetNumber, $cityCode, $cityName,
//                                                $cityDistrict, ''
//                                            );
//                                        } else {
//                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse des Sorgeberechtigen1 wurde nicht angelegt, da sie keine vollständige Adresse besitzt.';
//                                        }
//
//                                        $countFather++;
//                                    } else {
//
//                                        Relationship::useService()->insertRelationshipToPerson(
//                                            $tblPersonCustody1Exists,
//                                            $tblPerson,
//                                            $tblRelationshipTypeCustody,
//                                            ''
//                                        );
//
//                                        $countFatherExists++;
//                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Sorgeberechtigte wurde nicht angelegt, da schon eine Person mit gleichen Namen und gleicher PLZ existiert.';
//                                    }
//                                } else {
//                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Sorgeberechtigte1 wurde nicht angelegt, da sie keinen Namen und Vornamen hat.';
//                                }
//
//                                // Custody2
//                                $tblPersonCustody2 = null;
//                                $firstNameCustody2 = trim($Document->getValue($Document->getCell($Location['Sorgeber 2 Vorname'],
//                                    $RunY)));
//                                $lastNameCustody2 = trim($Document->getValue($Document->getCell($Location['Sorgeber 2 Nachname'],
//                                    $RunY)));
//
//                                if ($firstNameCustody2 !== '' && $lastNameCustody2 !== '') {
//                                    $tblPersonCustody2Exists = Person::useService()->existsPerson(
//                                        $firstNameCustody2,
//                                        $lastNameCustody2,
//                                        $cityCode
//                                    );
//
//                                    if (!$tblPersonCustody2Exists) {
//                                        $tblPersonCustody2 = Person::useService()->insertPerson(
//                                            null,
//                                            '',
//                                            $firstNameCustody2,
//                                            '',
//                                            $lastNameCustody2,
//                                            array(
//                                                0 => Group::useService()->getGroupByMetaTable('COMMON'),
//                                                1 => Group::useService()->getGroupByMetaTable('CUSTODY')
//                                            )
//                                        );
//
//                                        Relationship::useService()->insertRelationshipToPerson(
//                                            $tblPersonCustody2,
//                                            $tblPerson,
//                                            $tblRelationshipTypeCustody,
//                                            ''
//                                        );
//
//                                        if ($StreetName && $StreetNumber && $cityCode && $cityName) {
//                                            Address::useService()->insertAddressToPerson(
//                                                $tblPersonCustody2, $StreetName, $StreetNumber, $cityCode, $cityName,
//                                                $cityDistrict, ''
//                                            );
//                                        } else {
//                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse des Sorgeberechtigen2 wurde nicht angelegt, da sie keine vollständige Adresse besitzt.';
//                                        }
//
//                                        $countMother++;
//                                    } else {
//
//                                        Relationship::useService()->insertRelationshipToPerson(
//                                            $tblPersonCustody2Exists,
//                                            $tblPerson,
//                                            $tblRelationshipTypeCustody,
//                                            ''
//                                        );
//
//                                        $countMotherExists++;
//                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Sorgeberechtigte wurde nicht angelegt, da schon eine Person mit gleichen Namen und gleicher PLZ existiert.';
//                                    }
//                                } else {
//                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Sorgeberechtigte1 wurde nicht angelegt, da sie keinen Namen und Vornamen hat.';
//                                }
//
//                                if ($StreetName && $StreetNumber && $cityCode && $cityName) {
//                                    Address::useService()->insertAddressToPerson(
//                                        $tblPerson, $StreetName, $StreetNumber, $cityCode, $cityName, $cityDistrict, ''
//                                    );
//                                } else {
//                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse des Interessenten wurde nicht angelegt, da sie keine vollständige Adresse besitzt.';
//                                }

                                /*
                                * Phone
                                */
                                $phoneNumber = trim($Document->getValue($Document->getCell($Location['Telefon'],
                                    $RunY)));
                                if ($phoneNumber !== '') {
                                    $phoneNumberList = preg_split("/(\r\n|\n|\r)/",$phoneNumber);
                                    foreach ($phoneNumberList as $phone) {
                                        $remarkPhone = '';
                                        if (strpos($phone, 'P') !== false) {
                                            $remarkPhone = 'Vater';
                                            $phone = trim(str_replace('P', '', $phone));
                                        }
                                        if (strpos($phone, 'M') !== false) {
                                            $remarkPhone = 'Mutter';
                                            $phone = trim(str_replace('M', '', $phone));
                                        }

                                        $tblType = Phone::useService()->getTypeById(1);
                                        if (0 === strpos($phone, '01')) {
                                            $tblType = Phone::useService()->getTypeById(2);
                                        }
                                        Phone::useService()->insertPhoneToPerson(
                                            $tblPerson,
                                            $phone,
                                            $tblType,
                                            $remarkPhone
                                        );
                                    }
                                }

                                /*
                                 * Email
                                 */
                                $mailAddress = trim($Document->getValue($Document->getCell($Location['email'],
                                    $RunY)));
                                $tblMailType = Mail::useService()->getTypeById(1);
                                if ($mailAddress !== '' && $tblMailType) {
                                    $mailArray =  preg_split("/(\r\n|\n|\r)/",$mailAddress);
                                    foreach ($mailArray as $value) {
                                        Mail::useService()->insertMailToPerson($tblPerson, $value, $tblMailType, '');
                                    }
                                }
                            }
                        } else {
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Interessent wurde nicht hinzugefügt, da er keinen Vornamen und/oder Namen besitzt.';
                        }
                    }

                    return
                        new Success('Es wurden ' . $countInterestedPerson . ' Intessenten erfolgreich angelegt.')
//                        . new Success('Es wurden ' . $countFather . ' Sorgeberechtigte1 erfolgreich angelegt.') .
//                        ($countFatherExists > 0 ?
//                            new Warning($countFatherExists . ' Sorgeberechtigte1 exisistieren bereits.') : '') .
//                        new Success('Es wurden ' . $countMother . ' Sorgeberechtigte2 erfolgreich angelegt.') .
//                        ($countMotherExists > 0 ?
//                            new Warning($countMotherExists . ' Sorgeberechtigte2 exisistieren bereits.') : '')
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