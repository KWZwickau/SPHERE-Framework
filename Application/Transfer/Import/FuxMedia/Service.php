<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 09.11.2015
 * Time: 13:28
 */

namespace SPHERE\Application\Transfer\Import\FuxMedia;


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
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Extension\Repository\Debugger;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Service
{

    /**
     * @param IFormInterface|null $Stage
     * @param null $Select
     *
     * @return IFormInterface|Redirect|string
     */
    public function getTypeAndYear(IFormInterface $Stage = null, $Select = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Select) {
            return $Stage;
        }

        $Error = false;
        if (!isset($Select['Type']))
        {
            $Error = true;
            $Stage .=  new Warning('Schulart nicht gefunden');
        }
        if(!isset($Select['Year'])) {
            $Error = true;
            $Stage .=  new Warning('Schuljahr nicht gefunden');
        }
        if ($Error)
        {
            return $Stage;
        }

        return new Redirect('/Transfer/Import/FuxMedia/Student/Import', 0, array(
            'TypeId' => $Select['Type'],
            'YearId' => $Select['Year'],
        ));
    }

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null $File
     * @param null $TypeId
     * @param null $YearId
     * @return IFormInterface|Danger|string
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createStudentsFromFile(
        IFormInterface $Form = null,
        UploadedFile $File = null,
        $TypeId = null,
        $YearId = null
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $File || $TypeId === null || $YearId === null) {
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
                    'Schüler_Schülernummer' => null,
                    'Schüler_Name' => null,
                    'Schüler_Vorname' => null,
                    'Schüler_Klasse' => null,
                    'Schüler_Klassenstufe' => null,
                    'Schüler_Geschlecht' => null,
                    'Schüler_Staatsangehörigkeit' => null,
                    'Schüler_Straße' => null,
                    'Schüler_Plz' => null,
                    'Schüler_Wohnort' => null,
                    'Schüler_Ortsteil' => null,
                    'Schüler_Bundesland' => null,
                    'Schüler_Geburtsdatum' => null,
                    'Schüler_Geburtsort' => null,
                    'Schüler_Konfession' => null,
                    'Sorgeberechtigter1_Name' => null,
                    'Sorgeberechtigter1_Vorname' => null,
                    'Sorgeberechtigter1_Straße' => null,
                    'Sorgeberechtigter1_Plz' => null,
                    'Sorgeberechtigter1_Wohnort' => null,
                    'Sorgeberechtigter1_Ortsteil' => null,
                    'Sorgeberechtigter2_Name' => null,
                    'Sorgeberechtigter2_Vorname' => null,
                    'Sorgeberechtigter2_Straße' => null,
                    'Sorgeberechtigter2_Plz' => null,
                    'Sorgeberechtigter2_Wohnort' => null,
                    'Sorgeberechtigter2_Ortsteil' => null,
                    'Kommunikation_Telefon1' => null,
                    'Kommunikation_Telefon2' => null,
                    'Kommunikation_Telefon3' => null,
                    'Kommunikation_Telefon4' => null,
                    'Kommunikation_Telefon5' => null,
                    'Kommunikation_Telefon6' => null,
                    'Kommunikation_Fax' => null,
                    'Kommunikation_Email' => null,
                );
                for ($RunX = 0; $RunX < $X; $RunX++) {
                    $Value = $Document->getValue($Document->getCell($RunX, 0));
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

                    $tblType = Type::useService()->getTypeById($TypeId);
                    $tblYear = Term::useService()->getYearById($YearId);

                    for ($RunY = 1; $RunY < $Y; $RunY++) {

                        // Student
                        $tblPerson = Person::useService()->insertPerson(
                            Person::useService()->getSalutationById(3),   //Schüler
                            '',
                            trim($Document->getValue($Document->getCell($Location['Schüler_Vorname'], $RunY))),
                            '',
                            trim($Document->getValue($Document->getCell($Location['Schüler_Name'], $RunY))),
                            array(
                                0 => Group::useService()->getGroupById(1),           //Personendaten
                                1 => Group::useService()->getGroupById(3)            //Schüler
                            )
                        );

                        if ($tblPerson !== false) {
                            $countStudent++;

                            // Student Common
                            Common::useService()->insertMeta(
                                $tblPerson,
                                trim($Document->getValue($Document->getCell($Location['Schüler_Geburtsdatum'],
                                    $RunY))),
                                trim($Document->getValue($Document->getCell($Location['Schüler_Geburtsort'], $RunY))),
                                trim($Document->getValue($Document->getCell($Location['Schüler_Geschlecht'],
                                    $RunY))) == 'm' ? 1 : 2,
                                trim($Document->getValue($Document->getCell($Location['Schüler_Staatsangehörigkeit'],
                                    $RunY))),
                                trim($Document->getValue($Document->getCell($Location['Schüler_Konfession'], $RunY))),
                                0,
                                '',
                                ''
                            );

                            // Student Address
                            if (trim($Document->getValue($Document->getCell($Location['Schüler_Wohnort'],
                                    $RunY))) != ''
                            ) {
                                $Street = trim($Document->getValue($Document->getCell($Location['Schüler_Straße'],
                                    $RunY)));
                                if (preg_match_all('!\d+!', $Street, $matches)) {
                                    $pos = strpos($Street, $matches[0][0]);
                                    if ($pos !== null) {
                                        $StreetName = trim(substr($Street, 0, $pos));
                                        $StreetNumber = trim(substr($Street, $pos));

                                        Address::useService()->insertAddressToPerson(
                                            $tblPerson,
                                            $StreetName,
                                            $StreetNumber,
                                            trim($Document->getValue($Document->getCell($Location['Schüler_Plz'],
                                                $RunY))),
                                            trim($Document->getValue($Document->getCell($Location['Schüler_Wohnort'],
                                                $RunY))),
                                            trim($Document->getValue($Document->getCell($Location['Schüler_Ortsteil'],
                                                $RunY))),
                                            '',
                                            null
                                        );

                                    }
                                }
                            }

                            // Student Contact
                            for ($i = 1; $i < 7; $i++) {
                                $PhoneNumber = trim($Document->getValue($Document->getCell($Location['Kommunikation_Telefon' . $i],
                                    $RunY)));
                                if ($PhoneNumber != '') {
                                    Phone::useService()->insertPhoneToPerson($tblPerson, $PhoneNumber,
                                        Phone::useService()->getTypeById(1), '');
                                }
                            }
                            $FaxNumber = trim($Document->getValue($Document->getCell($Location['Kommunikation_Fax'],
                                $RunY)));
                            if ($FaxNumber != '') {
                                Phone::useService()->insertPhoneToPerson($tblPerson, $FaxNumber,
                                    Phone::useService()->getTypeById(7), '');
                            }
                            $MailAddress = trim($Document->getValue($Document->getCell($Location['Kommunikation_Email'],
                                $RunY)));
                            if ($MailAddress != '') {
                                Mail::useService()->insertMailToPerson($tblPerson, $MailAddress,
                                    Mail::useService()->getTypeById(1), '');
                            }

                            // ToDo JohK Klassenzugehörigkeit
                            if (($Level = trim($Document->getValue($Document->getCell($Location['Schüler_Klassenstufe'],
                                    $RunY)))) != ''
                            ) {
                                $tblLevel = Division::useService()->insertLevel($tblType, $Level);
                                if ($tblLevel) {
                                    $Division = trim($Document->getValue($Document->getCell($Location['Schüler_Klasse'],
                                        $RunY)));
                                    if ($Division != '') {
                                        $tblDivision = Division::useService()->insertDivision($tblYear, $tblLevel, $Division);
                                        if ($tblDivision)
                                        {
                                            Division::useService()->insertDivisionStudent($tblDivision, $tblPerson);
                                        }
                                    }
                                }
                            }

                            // ToDo JohK Schülerakte (Schülernummer...)
                            // ToDo JohK Fächerzugehörigkeit
                            // ToDo JohK Prüfung ob Sorgeberechtigter schon vorhanden

                            // Sorgeberechtigter 1
                            $tblPersonFather = null;
                            $FatherLastName = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter1_Name'],
                                $RunY)));
                            if ($FatherLastName !== '') {

                                $tblPersonFather = Person::useService()->insertPerson(
                                    null,
                                    '',
                                    trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter1_Vorname'],
                                        $RunY))),
                                    '',
                                    $FatherLastName,
                                    array(
                                        0 => Group::useService()->getGroupById(1),          //Personendaten
                                        1 => Group::useService()->getGroupById(4)           //Sorgeberechtigt
                                    )
                                );

                                Relationship::useService()->insertRelationshipToPerson(
                                    $tblPersonFather,
                                    $tblPerson,
                                    Relationship::useService()->getTypeById(1),             //Sorgeberechtigt
                                    ''
                                );

                                // Sorgeberechtigter1 Address
                                if (trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter1_Wohnort'],
                                        $RunY))) != ''
                                ) {
                                    $Street = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter1_Straße'],
                                        $RunY)));
                                    if (preg_match_all('!\d+!', $Street, $matches)) {
                                        $pos = strpos($Street, $matches[0][0]);
                                        if ($pos !== null) {
                                            $StreetName = trim(substr($Street, 0, $pos));
                                            $StreetNumber = trim(substr($Street, $pos));

                                            Address::useService()->insertAddressToPerson(
                                                $tblPersonFather,
                                                $StreetName,
                                                $StreetNumber,
                                                trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter1_Plz'],
                                                    $RunY))),
                                                trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter1_Wohnort'],
                                                    $RunY))),
                                                trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter1_Ortsteil'],
                                                    $RunY))),
                                                '',
                                                null
                                            );

                                        }
                                    }
                                }

                                $countFather++;
                            }

                            // Sorgeberechtigter 2
                            $tblPersonMother = null;
                            $MotherLastName = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter2_Name'],
                                $RunY)));
                            if ($MotherLastName !== '') {

                                $tblPersonMother = Person::useService()->insertPerson(
                                    null,
                                    '',
                                    trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter2_Vorname'],
                                        $RunY))),
                                    '',
                                    $MotherLastName,
                                    array(
                                        0 => Group::useService()->getGroupById(1),          //Personendaten
                                        1 => Group::useService()->getGroupById(4)           //Sorgeberechtigt
                                    )
                                );

                                Relationship::useService()->insertRelationshipToPerson(
                                    $tblPersonMother,
                                    $tblPerson,
                                    Relationship::useService()->getTypeById(1),             //Sorgeberechtigt
                                    ''
                                );

                                // Sorgeberechtigter2 Address
                                if (trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter2_Wohnort'],
                                        $RunY))) != ''
                                ) {
                                    $Street = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter2_Straße'],
                                        $RunY)));
                                    if (preg_match_all('!\d+!', $Street, $matches)) {
                                        $pos = strpos($Street, $matches[0][0]);
                                        if ($pos !== null) {
                                            $StreetName = trim(substr($Street, 0, $pos));
                                            $StreetNumber = trim(substr($Street, $pos));

                                            Address::useService()->insertAddressToPerson(
                                                $tblPersonMother,
                                                $StreetName,
                                                $StreetNumber,
                                                trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter2_Plz'],
                                                    $RunY))),
                                                trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter2_Wohnort'],
                                                    $RunY))),
                                                trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter2_Ortsteil'],
                                                    $RunY))),
                                                '',
                                                null
                                            );

                                        }
                                    }
                                }

                                $countMother++;
                            }

                        }
                    }

                    return
                        new Success('Es wurden ' . $countStudent . ' Schüler erfolgreich angelegt.') .
                        new Success('Es wurden ' . ($countFather + $countMother) . ' Sorgeberechtigte erfolgreich angelegt.');
                } else {
                    Debugger::screenDump($Location);
                    return new Danger(
                        "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }
        return new Danger('File nicht gefunden');
    }

}