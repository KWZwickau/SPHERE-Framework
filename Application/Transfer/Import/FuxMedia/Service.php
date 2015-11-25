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
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Transfer\Import\FuxMedia\Service\Person;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Repository\Debugger;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use SPHERE\Application\People\Meta\Student\Student;

class Service
{

    /**
     * @return Person
     */
    public static function usePeoplePerson()
    {

        return new Person(
            new Identifier('People', 'Person', null, null, Consumer::useService()->getConsumerBySession()),
            __DIR__ . '/../../../People/Person/Service/Entity', 'SPHERE\Application\People\Person\Service\Entity'
        );
    }

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
        if (!isset($Select['Type'])) {
            $Error = true;
            $Stage .= new Warning('Schulart nicht gefunden');
        }
        if (!isset($Select['Year'])) {
            $Error = true;
            $Stage .= new Warning('Schuljahr nicht gefunden');
        }
        if ($Error) {
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
                    'Schüler_Geburtsdatum' => null,
                    'Schüler_Geburtsort' => null,
                    'Schüler_Konfession' => null,
                    'Schüler_Einschulung_am' => null,
                    'Schüler_Aufnahme_am' => null,
                    'Schüler_Abgang_am' => null,
                    'Schüler_Schließfach_Schlüsselnummer' => null,
                    'Schüler_Schließfachnummer' => null,
                    'Schüler_Krankenkasse' => null,
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
                    'Beförderung_Fahrtroute' => null,
                    'Beförderung_Einsteigestelle' => null,
                    'Fächer_Religionsunterricht' => null,
                    'Fächer_Fremdsprache1' => null,
                    'Fächer_Fremdsprache2' => null,
                    'Fächer_Fremdsprache3' => null,
                    'Fächer_Fremdsprache4' => null,

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
                    $countFatherExists = 0;
                    $countMother = 0;
                    $countMotherExists = 0;

                    $tblType = Type::useService()->getTypeById($TypeId);
                    $tblYear = Term::useService()->getYearById($YearId);

                    for ($RunY = 1; $RunY < $Y; $RunY++) {

                        // Student
                        $tblPerson = $this->usePeoplePerson()->insertPerson(
                            $this->usePeoplePerson()->getSalutationById(3),   //Schüler
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

                            // Division
                            if (($Level = trim($Document->getValue($Document->getCell($Location['Schüler_Klassenstufe'],
                                    $RunY)))) != ''
                            ) {
                                $tblLevel = Division::useService()->insertLevel($tblType, $Level);
                                if ($tblLevel) {
                                    $Division = trim($Document->getValue($Document->getCell($Location['Schüler_Klasse'],
                                        $RunY)));
                                    if ($Division != '') {
                                        if (($pos = strpos($Division, $Level)) !== false) {
                                            if (strlen($Division) > (($start = $pos + strlen($Level)))) {
                                                $Division = substr($Division, $start);
                                            }
                                        }
                                        $tblDivision = Division::useService()->insertDivision($tblYear, $tblLevel,
                                            $Division);
                                        if ($tblDivision) {
                                            Division::useService()->insertDivisionStudent($tblDivision, $tblPerson);
                                        }
                                    }
                                }
                            }

                            // Schülerakte
                            $studentNumber = trim($Document->getValue($Document->getCell($Location['Schüler_Schülernummer'],
                                $RunY)));
                            $tblStudentLocker = null;
                            $LockerNumber = trim($Document->getValue($Document->getCell($Location['Schüler_Schließfachnummer'],
                                $RunY)));
                            $KeyNumber = trim($Document->getValue($Document->getCell($Location['Schüler_Schließfach_Schlüsselnummer'],
                                $RunY)));
                            if ($LockerNumber !== '' || $KeyNumber !== '') {
                                $tblStudentLocker = Student::useService()->insertStudentLocker(
                                    $LockerNumber,
                                    '',
                                    $KeyNumber
                                );
                            }
                            $tblStudentMedicalRecord = null;
                            $insurance = trim($Document->getValue($Document->getCell($Location['Schüler_Krankenkasse'],
                                $RunY)));
                            if ($insurance !== '') {
                                $tblStudentMedicalRecord = Student::useService()->insertStudentMedicalRecord(
                                    '',
                                    '',
                                    $insurance
                                );
                            }
                            $tblStudentTransport = null;
                            $route = trim($Document->getValue($Document->getCell($Location['Beförderung_Fahrtroute'],
                                $RunY)));
                            $stationEntrance = trim($Document->getValue($Document->getCell($Location['Beförderung_Einsteigestelle'],
                                $RunY)));
                            if ($route !== '' || $stationEntrance !== '') {
                                $tblStudentTransport = Student::useService()->insertStudentTransport(
                                    $route,
                                    $stationEntrance,
                                    ''
                                );
                            }
                            $tblStudentBilling = null;
                            $tblStudentBaptism = null;
                            // Todo JohK Förderbedarf -> eventuell komplett in die Bemerkungen
                            $tblStudentIntegration = null;
                            $tblStudent = Student::useService()->insertStudent(
                                $tblPerson,
                                $studentNumber,
                                $tblStudentMedicalRecord,
                                $tblStudentTransport,
                                $tblStudentBilling,
                                $tblStudentLocker,
                                $tblStudentBaptism,
                                $tblStudentIntegration
                            );

                            if ($tblStudent) {

                                // Schülertransfer
                                // ToDo JohK Company
                                $enrollmentDate = trim($Document->getValue($Document->getCell($Location['Schüler_Einschulung_am'],
                                    $RunY)));
                                if ($enrollmentDate !== '' && date_create($enrollmentDate) !== false) {
                                    $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('Enrollment');
                                    Student::useService()->insertStudentTransfer(
                                        $tblStudent,
                                        $tblStudentTransferType,
                                        null,
                                        null,
                                        null,
                                        $enrollmentDate,
                                        ''
                                    );
                                }
                                $arriveDate = trim($Document->getValue($Document->getCell($Location['Schüler_Aufnahme_am'],
                                    $RunY)));
                                if ($arriveDate !== '' && date_create($arriveDate) !== false) {
                                    $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('Arrive');
                                    Student::useService()->insertStudentTransfer(
                                        $tblStudent,
                                        $tblStudentTransferType,
                                        null,
                                        null,
                                        null,
                                        $arriveDate,
                                        ''
                                    );
                                }
                                $leaveDate = trim($Document->getValue($Document->getCell($Location['Schüler_Abgang_am'],
                                    $RunY)));
                                if ($leaveDate !== '' && date_create($leaveDate) !== false) {
                                    $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('Leave');
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

                                // Fächer
                                $subjectReligion = trim($Document->getValue($Document->getCell($Location['Fächer_Religionsunterricht'],
                                    $RunY)));
                                $tblSubject = false;
                                if ($subjectReligion !== '') {
                                    if ($subjectReligion === 'ETH') {
                                        $tblSubject = Subject::useService()->getSubjectByAcronym('ETH');
                                    } elseif ($subjectReligion === 'RE/e') {
                                        $tblSubject = Subject::useService()->getSubjectByAcronym('REV');
                                    } elseif ($subjectReligion === 'RE/k') {
                                        $tblSubject = Subject::useService()->getSubjectByAcronym('RKA');
                                    } elseif ($subjectReligion === 'RE/s') {
                                        // Todo JohK Subject Religion sonstiges anlegen
                                    }
                                    if ($tblSubject) {
                                        Student::useService()->addStudentSubject(
                                            $tblStudent,
                                            Student::useService()->getStudentSubjectTypeByIdentifier('Religion'),
                                            Student::useService()->getStudentSubjectRankingByIdentifier('1'),
                                            $tblSubject
                                        );
                                    }
                                }

                                for ($i = 1; $i < 5; $i++) {
                                    $subjectLanguage = trim($Document->getValue($Document->getCell($Location['Fächer_Fremdsprache' . $i],
                                        $RunY)));
                                    $tblSubject = false;
                                    if ($subjectLanguage !== '') {
                                        if ($subjectLanguage === 'EN') {
                                            $tblSubject = Subject::useService()->getSubjectByAcronym('EN');
                                        } elseif ($subjectLanguage === 'LA') {
                                            $tblSubject = Subject::useService()->getSubjectByAcronym('LA');
                                        } elseif ($subjectLanguage === 'FR') {
                                            $tblSubject = Subject::useService()->getSubjectByAcronym('FR');
                                        } elseif ($subjectLanguage === 'RU') {
                                            $tblSubject = Subject::useService()->getSubjectByAcronym('RU');
                                        } elseif ($subjectLanguage === 'POL') {
                                            $tblSubject = Subject::useService()->getSubjectByAcronym('PO');
                                        } elseif ($subjectLanguage === 'SPA') {
                                            $tblSubject = Subject::useService()->getSubjectByAcronym('SP');
                                        }
                                        // Todo JohK weitere Subject Language anlegen
                                        if ($tblSubject) {
                                            Student::useService()->addStudentSubject(
                                                $tblStudent,
                                                Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'),
                                                Student::useService()->getStudentSubjectRankingByIdentifier($i),
                                                $tblSubject
                                            );
                                        }
                                    }
                                }
                            }

                            // Sorgeberechtigter1
                            $tblPersonFather = null;
                            $FatherFirstName = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter1_Vorname'],
                                $RunY)));
                            $FatherLastName = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter1_Name'],
                                $RunY)));
                            $CityCode = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter1_Plz'],
                                $RunY)));
                            if ($FatherLastName !== '') {
                                $tblPersonFatherExists = $this->usePeoplePerson()->getPersonExists(
                                    $FatherFirstName,
                                    $FatherLastName,
                                    $CityCode
                                );
                                if (!$tblPersonFatherExists) {
                                    $tblPersonFather = $this->usePeoplePerson()->insertPerson(
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
                                                    $CityCode,
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
                                } else {

                                    Relationship::useService()->insertRelationshipToPerson(
                                        $tblPersonFatherExists,
                                        $tblPerson,
                                        Relationship::useService()->getTypeById(1),             //Sorgeberechtigt
                                        ''
                                    );

                                    $countFatherExists++;
                                }
                            }

                            // Sorgeberechtigter2
                            $tblPersonMother = null;
                            $MotherFirstName = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter2_Vorname'],
                                $RunY)));
                            $MotherLastName = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter2_Name'],
                                $RunY)));
                            $CityCode = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter2_Plz'],
                                $RunY)));
                            if ($MotherLastName !== '') {
                                $tblPersonMotherExists = $this->usePeoplePerson()->getPersonExists(
                                    $MotherFirstName,
                                    $MotherLastName,
                                    $CityCode
                                );
                                if (!$tblPersonMotherExists) {
                                    $tblPersonMother = $this->usePeoplePerson()->insertPerson(
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
                                                    $CityCode,
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
                                } else {

                                    Relationship::useService()->insertRelationshipToPerson(
                                        $tblPersonMotherExists,
                                        $tblPerson,
                                        Relationship::useService()->getTypeById(1),             //Sorgeberechtigt
                                        ''
                                    );

                                    $countMotherExists++;
                                }
                            }

                        }
                    }

                    $countExists = $countFatherExists + $countMotherExists;

                    return
                        new Success('Es wurden ' . $countStudent . ' Schüler erfolgreich angelegt.') .
                        new Success('Es wurden ' . ($countFather + $countMother) . ' Sorgeberechtigte erfolgreich angelegt.') .
                        ($countExists > 0 ?
                            new Warning($countExists . ' Sorgeberechtigte exisistieren bereits.') : '');
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