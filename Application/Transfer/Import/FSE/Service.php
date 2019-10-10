<?php

namespace SPHERE\Application\Transfer\Import\FSE;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Group\Group as CompanyGroup;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Club\Club;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonInformation;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Setting\Consumer\Consumer;
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
 * @package SPHERE\Application\Transfer\Import\FSE
 */
class Service
{
    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null $File
     *
     * @return IFormInterface|Danger|string
     */
    public function createMembersFromFile(
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
                    'ID' => null,
                    'Titel' => null,
                    'Name' => null,
                    'Vorname' => null,
                    'Strasse' => null,
                    'Ort' => null,
                    'PLZ' => null,
                    'Fax' => null,
                    'email' => null,
                    'Antragsdatum' => null,
                    'Aufnahmedatum' => null,
                    'Geburtsdatum' => null,
                    'Geschlecht' => null
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
                    $countMembers = 0;
                    $error = array();

                    $tblGroupCommon = Group::useService()->getGroupByMetaTable('COMMON');
                    $tblGroupClub = Group::useService()->getGroupByMetaTable('CLUB');

                    $groups = array(
                        0 => $tblGroupCommon,
                        1 => $tblGroupClub,
                    );

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        set_time_limit(300);

                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
                        if ($firstName === '' || $lastName === '') {
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person wurde nicht hinzugefügt, da er keinen Vornamen und/oder Namen besitzt.';
                        } else {
                            $tblPerson = Person::useService()->insertPerson(
                                null,
                                trim($Document->getValue($Document->getCell($Location['Titel'], $RunY))),
                                $firstName,
                                '',
                                $lastName,
                                $groups,
                                '',
                                trim($Document->getValue($Document->getCell($Location['ID'], $RunY)))
                            );

                            if ($tblPerson === false) {
                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person konnte nicht angelegt werden.';
                            } else {
                                $countMembers++;

                                $gender = trim($Document->getValue($Document->getCell($Location['Geschlecht'], $RunY)));
                                if ($gender == '2') {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_MALE;
                                } elseif ($gender == '1') {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_FEMALE;
                                } else {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_NULL;
                                }

                                $day = trim($Document->getValue($Document->getCell($Location['Geburtsdatum'],
                                    $RunY)));
                                if ($day !== '' && $day !== '0000-00-00') {
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
                                    $gender,
                                    '',
                                    '',
                                    TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                    '',
                                    ''
                                );

                                // Address
                                $studentCityCode = $importService->formatZipCode('PLZ', $RunY);
                                list($cityName, $cityDistrict) = $importService->splitCity('Ort', $RunY);
                                list($streetName, $streetNumber) = $importService->splitStreet('Strasse', $RunY);
                                $streetNumber = str_replace(' ', '', $streetNumber);
                                if ($streetName !== '' && $streetNumber !== ''
                                    && $studentCityCode && $cityName
                                ) {
                                    Address::useService()->insertAddressToPerson(
                                        $tblPerson, $streetName, $streetNumber, $studentCityCode, $cityName, $cityDistrict, ''
                                    );
                                } else {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse ist nicht vollständig.';
                                }

                                // Club
                                $applicationDate = '';
                                $day1 = trim($Document->getValue($Document->getCell($Location['Antragsdatum'], $RunY)));
                                if ($day1 !== '' && $day1 !== '0000-00-00') {
                                    try {
                                        $applicationDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day1));
                                    } catch (\Exception $ex) {
                                        $applicationDate = '';
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Ungültiges Antragsdatum: ' . $ex->getMessage();
                                    }
                                }

                                $entryDate = '';
                                $day2 = trim($Document->getValue($Document->getCell($Location['Aufnahmedatum'], $RunY)));
                                if ($day2 !== '' && $day2 !== '0000-00-00') {
                                    try {
                                        $entryDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day2));
                                    } catch (\Exception $ex) {
                                        $entryDate = '';
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Ungültiges Aufnahmedatum: ' . $ex->getMessage();
                                    }

                                }

                                if ($applicationDate !== '' or $entryDate !== '')
                                {
                                    if ($applicationDate !== '') {
                                        $remark = 'Antragsdatum: ' . $applicationDate;
                                    } else {
                                        $remark = '';
                                    }

                                    Club::useService()->insertMeta(
                                        $tblPerson,
                                        '',
                                        $entryDate,
                                        '',
                                        $remark
                                    );
                                }

                                // Contact
                                $importService->insertPrivateFax($tblPerson, 'Fax', $RunY);
                                $importService->insertPrivateMail($tblPerson, 'email', $RunY);
                            }
                        }
                    }

                    return
                        new Success('Es wurden ' . $countMembers . ' Personen erfolgreich angelegt.')
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
     */
    public function createMemberStaffsFromFile(
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
                    'ID' => null,
                    'Titel' => null,
                    'Name' => null,
                    'Vorname' => null,
                    'Strasse' => null,
                    'Ort' => null,
                    'PLZ' => null,
                    'Tel' => null,
                    'Fax' => null,
                    'mobil' => null,
                    'email' => null,
                    'Antragsdatum' => null,
                    'Aufnahmedatum' => null,
                    'Geburtsdatum' => null,
                    'Geschlecht' => null,
                    'Status' => null,
                    'Stat_text' => null,
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
                    $countMembers = 0;
                    $countCompanies = 0;
                    $error = array();

                    $tblGroupCommon = Group::useService()->getGroupByMetaTable('COMMON');
                    $tblGroupClub = Group::useService()->getGroupByMetaTable('CLUB');

                    $tblCompanyGroupCommon = CompanyGroup::useService()->getGroupByMetaTable('COMMON');

                    $groupsPerson = array(
                        0 => $tblGroupCommon,
                        1 => $tblGroupClub,
                    );

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        set_time_limit(300);

                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));

                        $status = trim($Document->getValue($Document->getCell($Location['Status'], $RunY)));
                        if ($status == 9) {
                            $title = trim($Document->getValue($Document->getCell($Location['Titel'], $RunY)));
                            $name = ($title !== '' ? $title . ' ' : '')
                                . ($lastName !== '' ? $lastName . ' ' : '');
                            if (($tblCompany = Company::useService()->insertCompany(
                                trim($name), '', $firstName
                            ))) {
                                $countCompanies++;
                                CompanyGroup::useService()->addGroupCompany($tblCompanyGroupCommon, $tblCompany);
                                $groupName = trim($Document->getValue($Document->getCell($Location['Stat_text'], $RunY)));
                                if ($groupName !== '') {
                                    if (($tblCompanyGroup = CompanyGroup::useService()->createGroupFromImport($groupName))) {
                                        CompanyGroup::useService()->addGroupCompany($tblCompanyGroup, $tblCompany);
                                    }
                                }

                                // Address
                                $studentCityCode = $importService->formatZipCode('PLZ', $RunY);
                                list($cityName, $cityDistrict) = $importService->splitCity('Ort', $RunY);
                                list($streetName, $streetNumber) = $importService->splitStreet('Strasse', $RunY);
                                $streetNumber = str_replace(' ', '', $streetNumber);
                                if ($streetName !== '' && $streetNumber !== ''
                                    && $studentCityCode && $cityName
                                ) {
                                    Address::useService()->insertAddressToCompany(
                                        $tblCompany, $streetName, $streetNumber, $studentCityCode, $cityName,
                                        $cityDistrict, ''
                                    );
                                } else {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse ist nicht vollständig.';
                                }
                            }
                        } else {
                            if ($firstName === '' || $lastName === '') {
                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person wurde nicht hinzugefügt, da er keinen Vornamen und/oder Namen besitzt.';
                            } else {
                                $tblPerson = Person::useService()->insertPerson(
                                    null,
                                    trim($Document->getValue($Document->getCell($Location['Titel'], $RunY))),
                                    $firstName,
                                    '',
                                    $lastName,
                                    $groupsPerson,
                                    '',
                                    trim($Document->getValue($Document->getCell($Location['ID'], $RunY)))
                                );

                                if ($tblPerson === false) {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person konnte nicht angelegt werden.';
                                } else {
                                    $countMembers++;

                                    $gender = trim($Document->getValue($Document->getCell($Location['Geschlecht'],
                                        $RunY)));
                                    if ($gender == '2') {
                                        $gender = TblCommonBirthDates::VALUE_GENDER_MALE;
                                    } elseif ($gender == '1') {
                                        $gender = TblCommonBirthDates::VALUE_GENDER_FEMALE;
                                    } else {
                                        $gender = TblCommonBirthDates::VALUE_GENDER_NULL;
                                    }

                                    $day = trim($Document->getValue($Document->getCell($Location['Geburtsdatum'],
                                        $RunY)));
                                    if ($day !== '' && $day !== '0000-00-00') {
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
                                        $gender,
                                        '',
                                        '',
                                        TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                        '',
                                        ''
                                    );

                                    // Address
                                    $studentCityCode = $importService->formatZipCode('PLZ', $RunY);
                                    list($cityName, $cityDistrict) = $importService->splitCity('Ort', $RunY);
                                    list($streetName, $streetNumber) = $importService->splitStreet('Strasse', $RunY);
                                    $streetNumber = str_replace(' ', '', $streetNumber);
                                    if ($streetName !== '' && $streetNumber !== ''
                                        && $studentCityCode && $cityName
                                    ) {
                                        Address::useService()->insertAddressToPerson(
                                            $tblPerson, $streetName, $streetNumber, $studentCityCode, $cityName,
                                            $cityDistrict, ''
                                        );
                                    } else {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse ist nicht vollständig.';
                                    }

                                    // Club
                                    $applicationDate = '';
                                    $day1 = trim($Document->getValue($Document->getCell($Location['Antragsdatum'],
                                        $RunY)));
                                    if ($day1 !== '' && $day1 !== '0000-00-00') {
                                        try {
                                            $applicationDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day1));
                                        } catch (\Exception $ex) {
                                            $applicationDate = '';
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Ungültiges Antragsdatum: ' . $ex->getMessage();
                                        }
                                    }

                                    $entryDate = '';
                                    $day2 = trim($Document->getValue($Document->getCell($Location['Aufnahmedatum'],
                                        $RunY)));
                                    if ($day2 !== '' && $day2 !== '0000-00-00') {
                                        try {
                                            $entryDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day2));
                                        } catch (\Exception $ex) {
                                            $entryDate = '';
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Ungültiges Aufnahmedatum: ' . $ex->getMessage();
                                        }

                                    }

                                    if ($applicationDate !== '' or $entryDate !== '') {
                                        if ($applicationDate !== '') {
                                            $remark = 'Antragsdatum: ' . $applicationDate;
                                        } else {
                                            $remark = '';
                                        }

                                        Club::useService()->insertMeta(
                                            $tblPerson,
                                            '',
                                            $entryDate,
                                            '',
                                            $remark
                                        );
                                    }

                                    // Contact
                                    $importService->insertPrivatePhone($tblPerson, 'Tel', $RunY);
                                    $importService->insertPrivatePhone($tblPerson, 'mobil', $RunY);
                                    $importService->insertPrivateFax($tblPerson, 'Fax', $RunY);
                                    $importService->insertPrivateMail($tblPerson, 'email', $RunY);

                                    $groupName = trim($Document->getValue($Document->getCell($Location['Stat_text'], $RunY)));
                                    if ($groupName !== '') {
                                        if (($tblGroup = Group::useService()->insertGroup(
                                            $groupName
                                        ))) {
                                            Group::useService()->addGroupPerson(
                                                $tblGroup,
                                                $tblPerson
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    }

                    return
                        new Success('Es wurden ' . $countMembers . ' Personen erfolgreich angelegt.')
                        . new Success('Es wurden ' . $countCompanies . ' Firmen erfolgreich angelegt.')
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
                    'Schulname' => null,
                    'PLZ' => null,
                    'Ort' => null,
                    'Adresse' => null,
                    'OT' => null,
                    'Anrede' => null,
                    'Nachname' => null,
                    'Vorname' => null,
                    'Telefon' => null,
                    'Fax' => null
                );
                for ($RunX = 0; $RunX < $X; $RunX++) {
                    $Value = trim($Document->getValue($Document->getCell($RunX, 0)));
                    if (array_key_exists($Value, $Location)) {
                        $Location[$Value] = $RunX;
                    }
                }

                $tblGroupCommon = CompanyGroup::useService()->getGroupByMetaTable('COMMON');
                $tblGroupSchool = CompanyGroup::useService()->getGroupByMetaTable('SCHOOL');

                $tblTypeCommon = Relationship::useService()->getTypeByName('Allgemein');

                $groupArray[] = Group::useService()->getGroupByMetaTable('COMMON');
                $groupArray[] = Group::useService()->getGroupByMetaTable('COMPANY_CONTACT');

                $importService = new ImportService($Location, $Document);

                /**
                 * Import
                 */
                if (!in_array(null, $Location, true)) {
                    $countNewCompany = 0;
                    $countEditCompany = 0;
                    $error = array();

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        $name = trim($Document->getValue($Document->getCell($Location['Schulname'], $RunY)));
                        if ($name != '') {
                            if (($tblCompany = Company::useService()->insertCompany($name))) {
                                $countNewCompany++;

                                CompanyGroup::useService()->addGroupCompany(
                                    $tblGroupCommon,
                                    $tblCompany
                                );
                                CompanyGroup::useService()->addGroupCompany(
                                    $tblGroupSchool,
                                    $tblCompany
                                );
                            }

                            if ($tblCompany) {
                                // Address
                                $cityName = trim($Document->getValue($Document->getCell($Location['Ort'], $RunY)));
                                $cityCode = $importService->formatZipCode('PLZ', $RunY);
                                $cityDistrict = trim($Document->getValue($Document->getCell($Location['OT'], $RunY)));;

                                list($streetName, $streetNumber) = $importService->splitStreet('Adresse', $RunY);

                                Address::useService()->insertAddressToCompany(
                                    $tblCompany,
                                    $streetName,
                                    $streetNumber,
                                    $cityCode,
                                    $cityName,
                                    $cityDistrict,
                                    ''
                                );

                                if (($Number = trim($Document->getValue($Document->getCell($Location['Telefon'],
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

                                if (($Number = trim($Document->getValue($Document->getCell($Location['Fax'],
                                        $RunY)))) != ''
                                ) {
                                    $tblType = Phone::useService()->getTypeById(8);
                                    Phone::useService()->insertPhoneToCompany
                                    (
                                        $tblCompany,
                                        $Number,
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
                        new Success('Es wurden ' . $countNewCompany . ' Firmen erfolgreich angelegt.')
                        . new Success('Es wurden ' . $countEditCompany . ' Firmen erfolgreich umbenannt.')
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