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
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\System\Extension\Repository\Debugger;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Service
{
    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile $File
     *
     * @return IFormInterface|Danger|string
     *
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createStudentsFromFile(IFormInterface $Form = null, UploadedFile $File = null)
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

                            // Student
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
//                            if (trim($Document->getValue($Document->getCell($Location['Schüler_Wohnort'],
//                                $RunY)))!= '')
//                            {
//                                Address::useService()->insertAddressToPerson(
//                                  $tblPerson,
//                                    trim($Document->getValue($Document->getCell($Location['Schüler_Straße'],
//                                        $RunY))),
//                                    '',
//                                    trim($Document->getValue($Document->getCell($Location['Schüler_Plz'],
//                                        $RunY))),
//                                    trim($Document->getValue($Document->getCell($Location['Schüler_Wohnort'],
//                                        $RunY))),
//                                    trim($Document->getValue($Document->getCell($Location['Schüler_Ortsteil'],
//                                        $RunY))),
//                                    '',
//                                    trim($Document->getValue($Document->getCell($Location['Schüler_Bundesland'],
//                                        $RunY)))
//                                );
//                            }


                            // ToDo JohK Schülerakte
                            // ToDo JohK Klassenzugehörigkeit

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

                                $countMother++;
                            }

                        }
//
//                            // Addresses
//                            $City = trim($Document->getValue($Document->getCell($Location['PLZ Ort'], $RunY)));
//                            $CityCode = substr($City, 0, 5);
//                            $CityName = substr($City, 6);
//                            $this->useContactAddress()->createAddressToPersonFromImport(
//                                $tblPerson,
//                                trim($Document->getValue($Document->getCell($Location['Straße'], $RunY))),
//                                trim($Document->getValue($Document->getCell($Location['Hausnr.'], $RunY))),
//                                $CityCode,
//                                $CityName,
//                                \SPHERE\Application\Contact\Address\Address::useService()->getStateById(1)
//                            );
//                            if ($tblPersonFather !== null) {
//                                $this->useContactAddress()->createAddressToPersonFromImport(
//                                    $tblPersonFather,
//                                    trim($Document->getValue($Document->getCell($Location['Straße'], $RunY))),
//                                    trim($Document->getValue($Document->getCell($Location['Hausnr.'], $RunY))),
//                                    $CityCode,
//                                    $CityName,
//                                    \SPHERE\Application\Contact\Address\Address::useService()->getStateById(1)
//                                );
//                            }
//                            if ($tblPersonMother !== null) {
//                                $this->useContactAddress()->createAddressToPersonFromImport(
//                                    $tblPersonMother,
//                                    trim($Document->getValue($Document->getCell($Location['Straße'], $RunY))),
//                                    trim($Document->getValue($Document->getCell($Location['Hausnr.'], $RunY))),
//                                    $CityCode,
//                                    $CityName,
//                                    \SPHERE\Application\Contact\Address\Address::useService()->getStateById(1)
//                                );
//                            }
//                        }
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