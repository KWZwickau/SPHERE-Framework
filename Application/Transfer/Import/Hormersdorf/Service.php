<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 22.01.2016
 * Time: 15:00
 */

namespace SPHERE\Application\Transfer\Import\Hormersdorf;


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
                    'Vorname' => null,
                    'Nachname' => null,
                    'Geburtsdatum' => null,
                    'PLZ' => null,
                    'Wohnort' => null,
                    'Straße' => null,
                    'Hausnummer' => null,
                    'Telefon' => null,
                    'E-mail' => null,
                    'Name der Mutter' => null,
                    'Name des Vaters' => null,
                    'Bemerkungen' => null,
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

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        // InterestedPerson
                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        if ($firstName !== '') {
                            $tblPerson = Person::useService()->insertPerson(
                                Person::useService()->getSalutationById(3),    //Schüler
                                '',
                                $firstName,
                                '',
                                trim($Document->getValue($Document->getCell($Location['Nachname'], $RunY))),
                                array(
                                    0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                    1 => Group::useService()->getGroupByMetaTable('PROSPECT')
                                )
                            );

                            if ($tblPerson !== false) {
                                $countInterestedPerson++;

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
                                    $cityDistrict = trim(substr($cityName, $pos));
                                    $cityName = trim(substr($cityName, 0, $pos));
                                }

                                Common::useService()->insertMeta(
                                    $tblPerson,
                                    date('Y-m-d', \PHPExcel_Shared_Date::ExcelToPHP(
                                        trim($Document->getValue($Document->getCell($Location['Geburtsdatum'],
                                            $RunY))))),
                                    '',
                                    TblCommonBirthDates::VALUE_GENDER_NULL,
                                    '',
                                    '',
                                    TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                    '',
                                    trim($Document->getValue($Document->getCell($Location['Bemerkungen'], $RunY)))
                                );

                                // Grundschule
                                $tblOptionTypeA = Type::useService()->getTypeById(6);

                                Prospect::useService()->insertMeta(
                                    $tblPerson,
                                    '',
                                    '',
                                    '',
                                    '2016/17',
                                    '1',
                                    $tblOptionTypeA,
                                    null,
                                    ''
                                );

                                $tblRelationshipTypeCustody = Relationship::useService()->getTypeById(1);

                                // Father
                                $tblPersonFather = null;
                                $fatherFullName = trim($Document->getValue($Document->getCell($Location['Name des Vaters'],
                                    $RunY)));
                                $pos = strrpos($fatherFullName, ' ');
                                if ($pos !== false) {
                                    $firstName = trim(substr($fatherFullName, 0, $pos));
                                    $lastName = trim(substr($fatherFullName, $pos));

                                    $tblPersonFatherExists = Person::useService()->existsPerson(
                                        $firstName,
                                        $lastName,
                                        $cityCode
                                    );

                                    if (!$tblPersonFatherExists) {
                                        $tblPersonFather = Person::useService()->insertPerson(
                                            Person::useService()->getSalutationById(1),
                                            '',
                                            $firstName,
                                            '',
                                            $lastName,
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

                                        $countFatherExists++;
                                    }
                                }

                                // Mother
                                $tblPersonMother = null;
                                $motherFullName = trim($Document->getValue($Document->getCell($Location['Name der Mutter'],
                                    $RunY)));
                                $pos = strrpos($motherFullName, ' ');
                                if ($pos !== false) {
                                    $firstName = trim(substr($motherFullName, 0, $pos));
                                    $lastName = trim(substr($motherFullName, $pos));

                                    $tblPersonMotherExists = Person::useService()->existsPerson(
                                        $firstName,
                                        $lastName,
                                        $cityCode
                                    );

                                    if (!$tblPersonMotherExists) {
                                        $tblPersonMother = Person::useService()->insertPerson(
                                            Person::useService()->getSalutationById(2),
                                            '',
                                            $firstName,
                                            '',
                                            $lastName,
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

                                        $countMotherExists++;
                                    }
                                }

                                // Addresses
                                $StreetName = trim($Document->getValue($Document->getCell($Location['Straße'], $RunY)));
                                $StreetNumber = trim($Document->getValue($Document->getCell($Location['Hausnummer'],
                                    $RunY)));
                                Address::useService()->insertAddressToPerson(
                                    $tblPerson, $StreetName, $StreetNumber, $cityCode, $cityName, $cityDistrict, ''
                                );
                                if ($tblPersonFather !== null) {
                                    Address::useService()->insertAddressToPerson(
                                        $tblPersonFather, $StreetName, $StreetNumber, $cityCode, $cityName,
                                        $cityDistrict, ''
                                    );
                                }
                                if ($tblPersonMother !== null) {
                                    Address::useService()->insertAddressToPerson(
                                        $tblPersonMother, $StreetName, $StreetNumber, $cityCode, $cityName,
                                        $cityDistrict, ''
                                    );
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
                                $mailAddress = trim($Document->getValue($Document->getCell($Location['E-mail'],
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
                        new Success('Es wurden ' . $countInterestedPerson . ' Intessenten erfolgreich angelegt.') .
                        new Success('Es wurden ' . $countFather . ' Väter erfolgreich angelegt.') .
                        ($countFatherExists > 0 ?
                            new Warning($countFatherExists . ' Väter exisistieren bereits.') : '') .
                        new Success('Es wurden ' . $countMother . ' Mütter erfolgreich angelegt.') .
                        ($countMotherExists > 0 ?
                            new Warning($countMotherExists . ' Mütter exisistieren bereits.') : '');

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