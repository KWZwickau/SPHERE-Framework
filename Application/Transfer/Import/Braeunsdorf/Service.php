<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 07.06.2019
 * Time: 08:43
 */

namespace SPHERE\Application\Transfer\Import\Braeunsdorf;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonInformation;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Person\Person;
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
use SPHERE\Application\Transfer\Import\Service as ImportService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Transfer\Import\Braeunsdorf
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
                    'Nachname' => null,
                    'Vorname' => null,
                    'Geburtsdatum' => null,
                    'Geburtsort' => null,
                    'Staatsangehörigkeit' => null,
                    'Konfession' => null,
                    'Familienstand' => null,
                    'Anrede' => null,
                    'Nachname Mutter' => null,
                    'Vorname Mutter' => null,
                    'Nachname Vater' => null,
                    'Vorname Vater' => null,
                    'Straße' => null,
                    'HausNr.' => null,
                    'PLZ' => null,
                    'Ort' => null,
                    'Ortsteil' => null,
                    'Telefon privat' => null,
                    'Handy Mutter' => null,
                    'Dienstl. Mutter' => null,
                    'Handy Vater' => null,
                    'Dienstl. Vater' => null,
                    'E-Mail Mutter' => null,
                    'E-Mail Vater' => null,
                    'Impfstatus' => null,
                    'Schulvertrag Beginn' => null,
                    'Schulvertrag Ende' => null,
                );

                for ($RunX = 0; $RunX < $X; $RunX++) {
                    $Value = trim($Document->getValue($Document->getCell($RunX, 0)));
                    if (array_key_exists($Value, $Location)) {
                        $Location[$Value] = $RunX;
                    }
                }

                $importService = new ImportService($Location, $Document);

                /**
                 * Import
                 */
                if (!in_array(null, $Location, true)) {
                    $countStudent = 0;
                    $countMother = 0;
                    $countMotherExists = 0;
                    $countFather = 0;
                    $countFatherExists = 0;

                    $error = array();

                    $tblYear = $importService->insertSchoolYear(19);

                    $tblRelationshipTypeCustody = Relationship::useService()->getTypeByName('Sorgeberechtigt');
                    $tblSchoolType = Type::useService()->getTypeByName('Grundschule');

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        set_time_limit(300);
                        // Student
                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Nachname'], $RunY)));
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
                                    try {
                                        $birthday = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                                    } catch (\Exception $ex) {
                                        $birthday = '';
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Ungültiges Geburtsdatum: ' . $ex->getMessage();
                                    }

                                } else {
                                    $birthday = '';
                                }

                                $remark = '';
                                if (($familyStatus = trim($Document->getValue($Document->getCell($Location['Familienstand'], $RunY)))) != '') {
                                    $remark .= 'Familienstand: ' . $familyStatus . " \n";
                                }
                                if (($salutation = trim($Document->getValue($Document->getCell($Location['Anrede'], $RunY)))) != '') {
                                    $remark .= 'Anrede: ' . $salutation . " \n";
                                }
                                if (($vaccinationStatus = trim($Document->getValue($Document->getCell($Location['Impfstatus'], $RunY)))) != '') {
                                    $remark .= 'Impfstatus: ' . $vaccinationStatus . " \n";
                                }
                                if (($schoolContractBegin = trim($Document->getValue($Document->getCell($Location['Schulvertrag Beginn'], $RunY)))) != '') {
                                    try {
                                        $beginDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($schoolContractBegin));
                                    } catch (\Exception $ex) {
                                        $beginDate = '';
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Ungültiges Schulvertrag Beginn: ' . $ex->getMessage();
                                    }
                                    $remark .= 'Schulvertrag Beginn: ' . $beginDate . " \n";
                                }
                                if (($schoolContractEnd = trim($Document->getValue($Document->getCell($Location['Schulvertrag Ende'], $RunY)))) != '') {
                                    try {
                                        $endDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($schoolContractEnd));
                                    } catch (\Exception $ex) {
                                        $endDate = '';
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Ungültiges Schulvertrag Ende: ' . $ex->getMessage();
                                    }
                                    $remark .= 'Schulvertrag Ende: ' . $endDate . " \n";
                                }

                                Common::useService()->insertMeta(
                                    $tblPerson,
                                    $birthday,
                                    trim($Document->getValue($Document->getCell($Location['Geburtsort'], $RunY))),
                                    '',
                                    trim($Document->getValue($Document->getCell($Location['Staatsangehörigkeit'], $RunY))),
                                    trim($Document->getValue($Document->getCell($Location['Konfession'], $RunY))),
                                    TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                    '',
                                    $remark
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
                                $cityName = trim($Document->getValue($Document->getCell($Location['Ort'], $RunY)));
                                $cityDistrict = trim($Document->getValue($Document->getCell($Location['Ortsteil'], $RunY)));
                                $streetName = trim($Document->getValue($Document->getCell($Location['Straße'], $RunY)));
                                $streetNumber = trim($Document->getValue($Document->getCell($Location['HausNr.'], $RunY)));
                                if ($streetName !== '' && $streetNumber !== ''
                                    && $studentCityCode && $cityName
                                ) {
                                    Address::useService()->insertAddressToPerson(
                                        $tblPerson, $streetName, $streetNumber, $studentCityCode, $cityName, $cityDistrict, ''
                                    );
                                } else {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse ist nicht vollständig.';
                                }

                                // Mother
                                $tblPersonMother = null;
                                $motherLastName = trim($Document->getValue($Document->getCell($Location['Nachname Mutter'],
                                    $RunY)));
                                $motherFirstName = trim($Document->getValue($Document->getCell($Location['Vorname Mutter'],
                                    $RunY)));
                                if ($motherLastName != '') {
                                    $tblPersonMotherExists = Person::useService()->existsPerson(
                                        $motherFirstName,
                                        $motherLastName,
                                        $studentCityCode
                                    );
                                    if (!$tblPersonMotherExists) {
                                        $gender = TblCommonBirthDates::VALUE_GENDER_FEMALE;
                                        $tblSalutation = Person::useService()->getSalutationById(2);

                                        $tblPersonMother = Person::useService()->insertPerson(
                                            $tblSalutation,
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
                                            '',
                                            1
                                        );

                                        Address::useService()->insertAddressToPerson(
                                            $tblPersonMother, $streetName, $streetNumber, $studentCityCode, $cityName, $cityDistrict, ''
                                        );

                                        $importService->insertPrivateMail($tblPersonMother, 'E-Mail Mutter', $RunY);
                                        $importService->insertPrivatePhone($tblPersonMother, 'Handy Mutter', $RunY);
                                        $importService->insertBusinessPhone($tblPersonMother, 'Dienstl. Mutter', $RunY);

                                        $countMother++;
                                    } else {

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonMotherExists,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            '',
                                            1
                                        );

                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Mutter wurde nicht angelegt,
                                            da schon eine Person mit gleichen Namen und gleicher PLZ existiert.
                                            Der Schüler wurde mit der bereits existierenden Person verknüpft';

                                        $countMotherExists++;
                                    }
                                }

                                // Father
                                $tblPersonFather = null;
                                $fatherLastName = trim($Document->getValue($Document->getCell($Location['Nachname Vater'],
                                    $RunY)));
                                $fatherFirstName = trim($Document->getValue($Document->getCell($Location['Vorname Vater'],
                                    $RunY)));
                                if ($fatherLastName != '') {
                                    $tblPersonFatherExists = Person::useService()->existsPerson(
                                        $fatherFirstName,
                                        $fatherLastName,
                                        $studentCityCode
                                    );
                                    if (!$tblPersonFatherExists) {
                                        $gender = TblCommonBirthDates::VALUE_GENDER_MALE;
                                        $tblSalutation = Person::useService()->getSalutationById(1);

                                        $tblPersonFather = Person::useService()->insertPerson(
                                            $tblSalutation,
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
                                            '',
                                            2
                                        );

                                        Address::useService()->insertAddressToPerson(
                                            $tblPersonFather, $streetName, $streetNumber, $studentCityCode, $cityName, $cityDistrict, ''
                                        );

                                        $importService->insertPrivateMail($tblPersonFather, 'E-Mail Vater', $RunY);
                                        $importService->insertPrivatePhone($tblPersonFather, 'Handy Vater', $RunY);
                                        $importService->insertBusinessPhone($tblPersonFather, 'Dienstl. Vater', $RunY);

                                        $countFather++;
                                    } else {

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonFatherExists,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            '',
                                            2
                                        );

                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Vater wurde nicht angelegt,
                                            da schon eine Person mit gleichen Namen und gleicher PLZ existiert.
                                            Der Schüler wurde mit der bereits existierenden Person verknüpft';

                                        $countFatherExists++;
                                    }
                                }

                                $importService->insertPrivatePhone($tblPerson, 'Telefon privat', $RunY);
                            }
                        }
                    }

                    return
                        new Success('Es wurden ' . $countStudent . ' Schüler erfolgreich angelegt.') .
                        new Success('Es wurden ' . $countFather . ' Väter erfolgreich angelegt.') .
                        ($countFatherExists > 0 ?
                            new Warning($countFatherExists . ' Väter exisistieren bereits.') : '') .
                        new Success('Es wurden ' . $countMother . ' Mütter erfolgreich angelegt.') .
                        ($countMotherExists > 0 ?
                            new Warning($countMotherExists . ' Mütter exisistieren bereits.') : '')
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
                    'Bemerkung' => null,
                    'Nachname' => null,
                    'Vorname' => null,
                    'Geburtsdatum' => null,
                    'Geburtsort' => null,
                    'Staatsangehörigkeit' => null,
                    'Konfession' => null,
                    'Familienstand' => null,
                    'Anrede' => null,
                    'Nachname Mutter' => null,
                    'Vorname Mutter' => null,
                    'Nachname Vater' => null,
                    'Vorname Vater' => null,
                    'Straße' => null,
                    'HausNr.' => null,
                    'PLZ' => null,
                    'Ort' => null,
                    'Ortsteil' => null,
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
                    $countMother = 0;
                    $countMotherExists = 0;
                    $countFather = 0;
                    $countFatherExists = 0;

                    $error = array();

                    $tblRelationshipTypeCustody = Relationship::useService()->getTypeByName('Sorgeberechtigt');
                    $tblSchoolType = Type::useService()->getTypeByName('Grundschule');

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        set_time_limit(300);
                        // Student
                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Nachname'], $RunY)));
                        if ($firstName === '' || $lastName === '') {
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Interessent wurde nicht hinzugefügt, da er keinen Vornamen und/oder Namen besitzt.';
                        } else {

                            $tblPerson = Person::useService()->insertPerson(
                                null,
                                '',
                                $firstName,
                                '',
                                $lastName,
                                array(
                                    0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                    1 => Group::useService()->getGroupByMetaTable('PROSPECT'),
                                )
                            );

                            if ($tblPerson === false) {
                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Interessent konnte nicht angelegt werden.';
                            } else {
                                $countStudent++;

                                $day = trim($Document->getValue($Document->getCell($Location['Geburtsdatum'],
                                    $RunY)));
                                if ($day !== '') {
                                    try {
                                        $birthday = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                                    } catch (\Exception $ex) {
                                        $birthday = '';
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Ungültiges Geburtsdatum: ' . $ex->getMessage();
                                    }

                                } else {
                                    $birthday = '';
                                }

                                $remark = '';
                                if (($familyStatus = trim($Document->getValue($Document->getCell($Location['Familienstand'], $RunY)))) != '') {
                                    $remark .= 'Familienstand: ' . $familyStatus . " \n";
                                }
                                if (($salutation = trim($Document->getValue($Document->getCell($Location['Anrede'], $RunY)))) != '') {
                                    $remark .= 'Anrede: ' . $salutation . " \n";
                                }

                                Common::useService()->insertMeta(
                                    $tblPerson,
                                    $birthday,
                                    trim($Document->getValue($Document->getCell($Location['Geburtsort'], $RunY))),
                                    '',
                                    trim($Document->getValue($Document->getCell($Location['Staatsangehörigkeit'], $RunY))),
                                    trim($Document->getValue($Document->getCell($Location['Konfession'], $RunY))),
                                    TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                    '',
                                    $remark
                                );

                                Prospect::useService()->insertMeta(
                                    $tblPerson,
                                    '',
                                    '',
                                    '',
                                    '2020/21',
                                    '1',
                                    $tblSchoolType,
                                    null,
                                    trim($Document->getValue($Document->getCell($Location['Bemerkung'], $RunY)))
                                );

                                // Address
                                $studentCityCode = str_pad(
                                    trim($Document->getValue($Document->getCell($Location['PLZ'], $RunY))),
                                    5,
                                    "0",
                                    STR_PAD_LEFT
                                );
                                $cityName = trim($Document->getValue($Document->getCell($Location['Ort'], $RunY)));
                                $cityDistrict = trim($Document->getValue($Document->getCell($Location['Ortsteil'], $RunY)));
                                $streetName = trim($Document->getValue($Document->getCell($Location['Straße'], $RunY)));
                                $streetNumber = trim($Document->getValue($Document->getCell($Location['HausNr.'], $RunY)));
                                if ($streetName !== '' && $streetNumber !== ''
                                    && $studentCityCode && $cityName
                                ) {
                                    Address::useService()->insertAddressToPerson(
                                        $tblPerson, $streetName, $streetNumber, $studentCityCode, $cityName, $cityDistrict, ''
                                    );
                                } else {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse ist nicht vollständig.';
                                }

                                // Mother
                                $tblPersonMother = null;
                                $motherLastName = trim($Document->getValue($Document->getCell($Location['Nachname Mutter'],
                                    $RunY)));
                                $motherFirstName = trim($Document->getValue($Document->getCell($Location['Vorname Mutter'],
                                    $RunY)));
                                if ($motherLastName != '') {
                                    $tblPersonMotherExists = Person::useService()->existsPerson(
                                        $motherFirstName,
                                        $motherLastName,
                                        $studentCityCode
                                    );
                                    if (!$tblPersonMotherExists) {
                                        $gender = TblCommonBirthDates::VALUE_GENDER_FEMALE;
                                        $tblSalutation = Person::useService()->getSalutationById(2);

                                        $tblPersonMother = Person::useService()->insertPerson(
                                            $tblSalutation,
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
                                            '',
                                            1
                                        );

                                        Address::useService()->insertAddressToPerson(
                                            $tblPersonMother, $streetName, $streetNumber, $studentCityCode, $cityName, $cityDistrict, ''
                                        );

                                        $countMother++;
                                    } else {

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonMotherExists,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            '',
                                            1
                                        );

                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Mutter wurde nicht angelegt,
                                            da schon eine Person mit gleichen Namen und gleicher PLZ existiert.
                                            Der Schüler wurde mit der bereits existierenden Person verknüpft';

                                        $countMotherExists++;
                                    }
                                }

                                // Father
                                $tblPersonFather = null;
                                $fatherLastName = trim($Document->getValue($Document->getCell($Location['Nachname Vater'],
                                    $RunY)));
                                $fatherFirstName = trim($Document->getValue($Document->getCell($Location['Vorname Vater'],
                                    $RunY)));
                                if ($fatherLastName != '') {
                                    $tblPersonFatherExists = Person::useService()->existsPerson(
                                        $fatherFirstName,
                                        $fatherLastName,
                                        $studentCityCode
                                    );
                                    if (!$tblPersonFatherExists) {
                                        $gender = TblCommonBirthDates::VALUE_GENDER_MALE;
                                        $tblSalutation = Person::useService()->getSalutationById(1);

                                        $tblPersonFather = Person::useService()->insertPerson(
                                            $tblSalutation,
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
                                            '',
                                            2
                                        );

                                        Address::useService()->insertAddressToPerson(
                                            $tblPersonFather, $streetName, $streetNumber, $studentCityCode, $cityName, $cityDistrict, ''
                                        );

                                        $countFather++;
                                    } else {

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonFatherExists,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            '',
                                            2
                                        );

                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Vater wurde nicht angelegt,
                                            da schon eine Person mit gleichen Namen und gleicher PLZ existiert.
                                            Der Schüler wurde mit der bereits existierenden Person verknüpft';

                                        $countFatherExists++;
                                    }
                                }
                            }
                        }
                    }

                    return
                        new Success('Es wurden ' . $countStudent . ' Interessenten erfolgreich angelegt.') .
                        new Success('Es wurden ' . $countFather . ' Väter erfolgreich angelegt.') .
                        ($countFatherExists > 0 ?
                            new Warning($countFatherExists . ' Väter exisistieren bereits.') : '') .
                        new Success('Es wurden ' . $countMother . ' Mütter erfolgreich angelegt.') .
                        ($countMotherExists > 0 ?
                            new Warning($countMotherExists . ' Mütter exisistieren bereits.') : '')
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
    public function createPersonsFromFile(IFormInterface $Form = null, UploadedFile $File = null)
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
                    'Gruppe' => null,
                    'Anrede' => null,
                    'Nachname' => null,
                    'Vorname' => null,
                    'Straße' => null,
                    'HausNr.' => null,
                    'PLZ' => null,
                    'Ort' => null,
                    'Ortsteil' => null,
                    'Telefon privat' => null,
                    'Handy' => null,
                    'E-Mail Privat' => null,
                    'Bemerkungen' => null,
                );
                for ($RunX = 0; $RunX < $X; $RunX++) {
                    $Value = trim($Document->getValue($Document->getCell($RunX, 0)));
                    if (array_key_exists($Value, $Location)) {
                        $Location[$Value] = $RunX;
                    }
                }

                $importService = new ImportService($Location, $Document);
                $groupArray[] = Group::useService()->getGroupByMetaTable('COMMON');

                /**
                 * Import
                 */
                if (!in_array(null, $Location, true)) {
                    $countClub = 0;
                    $countClubExists = 0;
                    $error = array();

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Nachname'], $RunY)));
                        if ($firstName !== '' && $lastName !== '') {
                            // Address
                            $cityName = trim($Document->getValue($Document->getCell($Location['Ort'], $RunY)));
                            $cityCode = str_pad(
                                trim($Document->getValue($Document->getCell($Location['PLZ'], $RunY))),
                                5,
                                "0",
                                STR_PAD_LEFT
                            );
                            $cityDistrict = trim($Document->getValue($Document->getCell($Location['Ortsteil'], $RunY)));;

                            $streetName = trim($Document->getValue($Document->getCell($Location['Straße'], $RunY)));
                            $streetNumber = trim($Document->getValue($Document->getCell($Location['HausNr.'], $RunY)));

                            $tblPersonExits = Person::useService()->existsPerson(
                                $firstName,                                $lastName,
                                $cityCode
                            );

                            if ($tblPersonExits) {

                                $error[] = 'Zeile: ' . ($RunY + 1)
                                    . ' (' . $lastName . ', ' . $firstName . ') '
                                    . ' Die Person wurde nicht angelegt, da schon eine Person mit gleichen Namen und gleicher PLZ existiert.';

                                $countClubExists++;
                                $tblPerson = $tblPersonExits;
                            } else {
                                $salutation = trim($Document->getValue($Document->getCell($Location['Anrede'], $RunY)));
                                if ($salutation == 'Herr' || $salutation == 'Herrn') {
                                    $tblSalutation = Person::useService()->getSalutationById(1);
                                } elseif ($salutation == 'Frau') {
                                    $tblSalutation = Person::useService()->getSalutationById(2);
                                } else {
                                    $tblSalutation = false;
                                }

                                $tblPerson = Person::useService()->insertPerson(
                                    $tblSalutation ? $tblSalutation : null,
                                    '',
                                    $firstName,
                                    '',
                                    $lastName,
                                    $groupArray
                                );
                            }

                            if ($tblPerson) {
                                $importService->insertGroupsByName($tblPerson, 'Gruppe', $RunY, ';');

                                if (!$tblPerson->fetchMainAddress()) {
                                    Address::useService()->insertAddressToPerson(
                                        $tblPerson,
                                        $streetName,
                                        $streetNumber,
                                        $cityCode,
                                        $cityName,
                                        $cityDistrict,
                                        ''
                                    );
                                }

                                $importService->insertPrivatePhone($tblPerson, 'Telefon privat', $RunY);
                                $importService->insertPrivatePhone($tblPerson, 'Handy', $RunY);
                                $importService->insertPrivateMail($tblPerson, 'E-Mail Privat', $RunY);

                                // Bemerkungen
                                $remark = trim($Document->getValue($Document->getCell($Location['Bemerkungen'], $RunY)));
                                if ($remark != '') {
                                    if (($tblCommon = $tblPerson->getCommon())) {
                                        Common::useService()->updateCommon($tblCommon, $tblCommon->getRemark() . "\n" . $remark);
                                    } else {
                                        Common::useService()->insertMeta(
                                            $tblPerson,
                                            '',
                                            '',
                                            '',
                                            '',
                                            '',
                                            TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                            '',
                                            $remark
                                        );
                                    }
                                }
                            }
                        }
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

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile $File
     *
     * @return IFormInterface|Danger|string
     */
    public function createCompaniesFromFile(IFormInterface $Form = null, UploadedFile $File = null)
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
                    'Anrede' => null,
                    'Nachname' => null,
                    'Vorname' => null,
                    'Firmenbezeichnung' => null,
                    'Straße' => null,
                    'HausNr.' => null,
                    'PLZ' => null,
                    'Ort' => null,
                    'Ortsteil' => null,
                    'Telefon dienstlich' => null,
                    'E-Mail Firma' => null,
                );
                for ($RunX = 0; $RunX < $X; $RunX++) {
                    $Value = trim($Document->getValue($Document->getCell($RunX, 0)));
                    if (array_key_exists($Value, $Location)) {
                        $Location[$Value] = $RunX;
                    }
                }

                $tblGroupCommon = \SPHERE\Application\Corporation\Group\Group::useService()->getGroupByMetaTable('COMMON');
                $tblTypeCommon = Relationship::useService()->getTypeByName('Allgemein');

                $groupArray[] = Group::useService()->getGroupByMetaTable('COMMON');

                /**
                 * Import
                 */
                if (!in_array(null, $Location, true)) {
                    $countCompany = 0;
                    $error = array();

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        $name = trim($Document->getValue($Document->getCell($Location['Firmenbezeichnung'], $RunY)));
                        if ($name != '') {
                            if (($tblCompany = Company::useService()->insertCompany($name))) {
                                $countCompany++;

                                \SPHERE\Application\Corporation\Group\Group::useService()->addGroupCompany(
                                    $tblGroupCommon,
                                    $tblCompany
                                );

                                // Address
                                $cityName = trim($Document->getValue($Document->getCell($Location['Ort'], $RunY)));
                                $cityCode = str_pad(
                                    trim($Document->getValue($Document->getCell($Location['PLZ'], $RunY))),
                                    5,
                                    "0",
                                    STR_PAD_LEFT
                                );
                                $cityDistrict = trim($Document->getValue($Document->getCell($Location['Ortsteil'], $RunY)));;

                                $streetName = trim($Document->getValue($Document->getCell($Location['Straße'], $RunY)));
                                $streetNumber = trim($Document->getValue($Document->getCell($Location['HausNr.'], $RunY)));

                                Address::useService()->insertAddressToCompany(
                                    $tblCompany,
                                    $streetName,
                                    $streetNumber,
                                    $cityCode,
                                    $cityName,
                                    $cityDistrict,
                                    ''
                                );

                                if (($Number = trim($Document->getValue($Document->getCell($Location['Telefon dienstlich'],
                                        $RunY)))) != ''
                                ) {
                                    $tblType = Phone::useService()->getTypeById(3);
                                    if (0 === strpos($Number, '01')) {
                                        $tblType = Phone::useService()->getTypeById(4);
                                    }
                                    Phone::useService()->insertPhoneToCompany
                                    (
                                        $tblCompany,
                                        $Number,
                                        $tblType,
                                        ''
                                    );
                                }

                                if (($MailAddress = trim($Document->getValue($Document->getCell($Location['E-Mail Firma'],
                                        $RunY)))) != ''
                                ) {
                                    $tblType = Mail::useService()->getTypeById(2);
                                    Mail::useService()->insertMailToCompany
                                    (
                                        $tblCompany,
                                        $MailAddress,
                                        $tblType,
                                        ''
                                    );
                                }
                            }

                            $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                            $lastName = trim($Document->getValue($Document->getCell($Location['Nachname'], $RunY)));
                            if ($firstName !== '' && $lastName !== '') {
                                $tblPersonExits = Person::useService()->getPersonByName($firstName, $lastName);
                                if ($tblPersonExits) {
                                    $error[] = 'Zeile: ' . ($RunY + 1)
                                        . ' (' . $lastName . ', ' . $firstName . ') '
                                        . ' Der Ansprechpartner wurde nicht angelegt, da schon eine Person mit gleichen Namen existiert.';

                                    $tblPerson = $tblPersonExits;
                                } else {
                                    $salutation = trim($Document->getValue($Document->getCell($Location['Anrede'], $RunY)));
                                    if ($salutation == 'Herr' || $salutation == 'Herrn') {
                                        $tblSalutation = Person::useService()->getSalutationById(1);
                                    } elseif ($salutation == 'Frau') {
                                        $tblSalutation = Person::useService()->getSalutationById(2);
                                    } else {
                                        $tblSalutation = false;
                                    }

                                    $tblPerson = Person::useService()->insertPerson(
                                        $tblSalutation ? $tblSalutation : null,
                                        '',
                                        $firstName,
                                        '',
                                        $lastName,
                                        $groupArray
                                    );
                                }

                                if ($tblPerson) {
                                    Relationship::useService()->addCompanyRelationshipToPerson(
                                        $tblCompany,
                                        $tblPerson,
                                        $tblTypeCommon
                                    );
                                }
                            }
                        }
                    }

                    return
                        new Success('Es wurden ' . $countCompany . ' Firmen erfolgreich angelegt.')
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