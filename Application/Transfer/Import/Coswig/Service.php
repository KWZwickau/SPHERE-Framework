<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 16.03.2016
 * Time: 10:00
 */

namespace SPHERE\Application\Transfer\Import\Coswig;


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
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Danger;
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
                    'Klasse' => null,
                    'Name' => null,
                    'Vorname' => null,
                    'Geb.' => null,
                    'Geb.-Ort' => null,
                    'Mutter' => null,
                    'Vater' => null,
                    'Familienst.' => null,
                    'Straße' => null,
                    'Nr.' => null,
                    'PLZ' => null,
                    'Ort' => null,
                    'Email' => null,
                    'Telefon privat' => null,
                    'Telefon dienstlich' => null,
                    'Geschw.' => null,
                    'Konf.' => null,
                    'Schule' => null,
                    'KiTa' => null,
                    'KiTa-Std.' => null,
                    'Bemerkungen' => null,
                    'Beruf Mutter' => null,
                    'Beruf Vater' => null,
//                    'Mitglied Mutter' => null,
//                    'Mitglied Vater' => null,
                    'Bürgschaft' => null,
                );

                $phonePrivate2Location = false;
                $phoneBusiness2Location = false;

                for ($RunX = 0; $RunX < $X; $RunX++) {
                    $Value = trim($Document->getValue($Document->getCell($RunX, 0)));
                    if (array_key_exists($Value, $Location)) {
                        $Location[$Value] = $RunX;
                    }

                    if ($Value == 'Telefon privat 2') {
                        $phonePrivate2Location = $RunX;
                    }
                    if ($Value == 'Telefon dienstlich 2') {
                        $phoneBusiness2Location = $RunX;
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

                        if ($RunY > 210){
                            break;
                        }

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
                                    1 => Group::useService()->getGroupByMetaTable('STUDENT')
                                )
                            );

                            if ($tblPerson === false) {
                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler konnte nicht angelegt werden.';
                            } else {
                                $countStudent++;

                                $cityCode = str_pad(
                                    trim($Document->getValue($Document->getCell($Location['PLZ'], $RunY))),
                                    5,
                                    "0",
                                    STR_PAD_LEFT
                                );

                                $cityName = trim($Document->getValue($Document->getCell($Location['Ort'], $RunY)));
                                $cityDistrict = '';
                                $pos = strpos($cityName, " OT ");
                                if ($pos !== false) {
                                    $cityDistrict = trim(substr($cityName, $pos));
                                    $cityName = trim(substr($cityName, 0, $pos));
                                }

                                Common::useService()->insertMeta(
                                    $tblPerson,
                                    date('Y-m-d', \PHPExcel_Shared_Date::ExcelToPHP(
                                        trim($Document->getValue($Document->getCell($Location['Geb.'],
                                            $RunY))))),
                                    trim($Document->getValue($Document->getCell($Location['Geb.-Ort'], $RunY))),
                                    TblCommonBirthDates::VALUE_GENDER_NULL,
                                    '',
                                    trim($Document->getValue($Document->getCell($Location['Konf.'], $RunY))),
                                    TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                    '',
                                    trim($Document->getValue($Document->getCell($Location['Bemerkungen'], $RunY)))
                                );

                                // Todo JohK schuljahr 16 für zukünftige 1. Klasse
                                // division
                                $tblDivision = false;
                                $year = 15;
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

                                    $division = trim($Document->getValue($Document->getCell($Location['Klasse'],
                                        $RunY)));
                                    $tblType = Type::useService()->getTypeById(6); // Grundschule
                                    if ($division > 4) {
                                        $tblType = Type::useService()->getTypeById(8); // Mittelschule / Oberschule
                                    }
                                    if ($tblType) {
                                        $tblLevel = Division::useService()->insertLevel($tblType, $division);
                                        if ($tblLevel) {
                                            $tblDivision = Division::useService()->insertDivision($tblYear, $tblLevel,
                                                '');
                                        }
                                    }
                                }

                                if ($tblDivision) {
                                    Division::useService()->insertDivisionStudent($tblDivision, $tblPerson);
                                } else {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler konnte keiner Klasse zugeordnet werden.';
                                }

                                $tblRelationshipTypeCustody = Relationship::useService()->getTypeById(1);

                                $familyStatus = trim($Document->getValue($Document->getCell($Location['Familienst.'],
                                    $RunY)));
                                $security = trim($Document->getValue($Document->getCell($Location['Bürgschaft'],
                                    $RunY)));

                                // Father
                                $tblPersonFather = null;
                                $fatherFullName = trim($Document->getValue($Document->getCell($Location['Vater'],
                                    $RunY)));
                                $pos = strrpos($fatherFullName, ' ');
                                if ($pos === false) {
                                    if ($fatherFullName != '') {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Vater wurde nicht angelegt,
                                    da der Name des Vaters nicht getrennt werden konnte (Enthält kein Leerzeichen).';
                                    }
                                } else {
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
                                                ($familyStatus !== '' ? 'Familienstand: ' . $familyStatus . ' ' : '')
                                                . ($security !== '' ? 'Bürgschaft:' . $security : '')
                                            );

                                            $occupationFather = trim($Document->getValue($Document->getCell($Location['Beruf Vater'],
                                                $RunY)));
                                            if ($occupationFather !== '') {
                                                Custody::useService()->insertMeta(
                                                    $tblPersonFather,
                                                    $occupationFather,
                                                    '',
                                                    ''
                                                );
                                            }
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

                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Vater wurde nicht angelegt,
                                            da schon eine Person mit gleichen Namen und gleicher PLZ existiert.
                                            Der Schüler wurde mit der bereits existierenden Person verknüpft';

                                        $countFatherExists++;
                                    }
                                }

                                // Mother
                                $tblPersonMother = null;
                                $motherFullName = trim($Document->getValue($Document->getCell($Location['Mutter'],
                                    $RunY)));
                                $pos = strrpos($motherFullName, ' ');
                                if ($pos === false) {
                                    if ($motherFullName != '') {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Mutter wurde nicht angelegt,
                                    da der Name der Mutter nicht getrennt werden konnte (Enthält kein Leerzeichen).';
                                    }
                                } else {
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
                                                ($familyStatus !== '' ? 'Familienstand: ' . $familyStatus . ' ' : '')
                                                . ($security !== '' ? 'Bürgschaft:' . $security : '')
                                            );

                                            $occupationMother = trim($Document->getValue($Document->getCell($Location['Beruf Mutter'],
                                                $RunY)));
                                            if ($occupationMother !== '') {
                                                Custody::useService()->insertMeta(
                                                    $tblPersonMother,
                                                    $occupationMother,
                                                    '',
                                                    ''
                                                );
                                            }
                                        }

                                        // Todo JohK Mitglied Mutter, Mitglied Vater

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

                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Mutter wurde nicht angelegt,
                                            da schon eine Person mit gleichen Namen und gleicher PLZ existiert.
                                            Der Schüler wurde mit der bereits existierenden Person verknüpft';

                                        $countMotherExists++;
                                    }
                                }

                                if ($tblPersonFather && $tblPersonMother && $familyStatus !== '') {
                                    if ($familyStatus === 'verh.') {
                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonFather,
                                            $tblPersonMother,
                                            Relationship::useService()->getTypeById(6),  // Ehepartner
                                            ''
                                        );
                                    } elseif ($familyStatus === 'LG.' || $familyStatus === 'eheä.G.' || $familyStatus === 'eheähnl.G.') {
                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonFather,
                                            $tblPersonMother,
                                            Relationship::useService()->getTypeById(7),  // Lebenspartner
                                            ''
                                        );
                                    } elseif ($familyStatus === 'alleinerz.' || $familyStatus === 'getr.lebend') {
                                        // Keine Beziehung
                                    } else {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Unbekannter Familienstand. Keine Beziehung angelegt.';
                                    }
                                }

                                // Addresses
                                $StreetName = trim($Document->getValue($Document->getCell($Location['Straße'], $RunY)));
                                $StreetNumber = trim($Document->getValue($Document->getCell($Location['Nr.'],
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

                                $phoneNumber = trim($Document->getValue($Document->getCell($Location['Telefon privat'],
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

                                $phoneNumber = trim($Document->getValue($Document->getCell($Location['Telefon dienstlich'],
                                    $RunY)));
                                if ($phoneNumber != '') {
                                    $tblType = Phone::useService()->getTypeById(3);
                                    if (0 === strpos($phoneNumber, '01')) {
                                        $tblType = Phone::useService()->getTypeById(4);
                                    }
                                    Phone::useService()->insertPhoneToPerson(
                                        $tblPerson,
                                        $phoneNumber,
                                        $tblType,
                                        ''
                                    );
                                }

                                if ($phonePrivate2Location) {
                                    $phoneNumber = trim($Document->getValue($Document->getCell($phonePrivate2Location,
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
                                }

                                if ($phoneBusiness2Location) {
                                    $phoneNumber = trim($Document->getValue($Document->getCell($phoneBusiness2Location,
                                        $RunY)));
                                    if ($phoneNumber != '') {
                                        $tblType = Phone::useService()->getTypeById(3);
                                        if (0 === strpos($phoneNumber, '01')) {
                                            $tblType = Phone::useService()->getTypeById(4);
                                        }
                                        Phone::useService()->insertPhoneToPerson(
                                            $tblPerson,
                                            $phoneNumber,
                                            $tblType,
                                            ''
                                        );
                                    }
                                }

                                $mailAddress = trim($Document->getValue($Document->getCell($Location['Email'],
                                    $RunY)));
                                if ($mailAddress != '') {
                                    Mail::useService()->insertMailToPerson(
                                        $tblPerson,
                                        $mailAddress,
                                        Mail::useService()->getTypeById(1),
                                        ''
                                    );
                                }

                                // student
                                $sibling = trim($Document->getValue($Document->getCell($Location['Geschw.'],
                                    $RunY)));
                                $tblSiblingRank = false;
                                if ($sibling !== '') {
                                    if ($sibling == '1') {
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
                                $tblStudent = Student::useService()->insertStudent($tblPerson, '', null, null,
                                    $tblStudentBilling);
                                if ($tblStudent) {
                                    $school = trim($Document->getValue($Document->getCell($Location['Schule'],
                                        $RunY)));
                                    if ($school != '') {
                                        Student::useService()->insertStudentTransfer(
                                            $tblStudent,
                                            Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE'),
                                            null,
                                            null,
                                            null,
                                            '',
                                            'Schule: ' . $school
                                        );
                                    }

                                    $daycare = trim($Document->getValue($Document->getCell($Location['KiTa'],
                                        $RunY)));
                                    $daycareHours = trim($Document->getValue($Document->getCell($Location['KiTa-Std.'],
                                        $RunY)));
                                    if ($daycare != '') {
                                        Student::useService()->insertStudentTransfer(
                                            $tblStudent,
                                            Student::useService()->getStudentTransferTypeByIdentifier('PROCESS'),
                                            null,
                                            null,
                                            null,
                                            '',
                                            'KiTa: ' . $daycare . ($daycareHours ? ' KiTa-Std.: ' . $daycareHours : '')
                                        );
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
                            new Warning($countMotherExists . ' Mütter exisistieren bereits.') : '')
                        . new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                            new Panel(
                                'Fehler',
                                $error,
                                Panel::PANEL_TYPE_DANGER
                            )
                        ))));

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