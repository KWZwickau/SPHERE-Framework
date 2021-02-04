<?php


namespace SPHERE\Application\Transfer\Import\FuxMedia;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Custody\Custody;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Transfer\Import\FuxMedia\Service\Person;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Link\Identifier;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use SPHERE\Application\Transfer\Import\Service as ImportService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Transfer\Import\FuxMedia
 */
class Service
{

    /**
     * @param IFormInterface|null $Stage
     * @param null                $Select
     * @param string              $Redirect
     *
     * @return IFormInterface|Redirect|string
     */
    public function getTypeAndYear(IFormInterface $Stage = null, $Select = null, $Redirect = '')
    {

        /**
         * Skip to Frontend
         */
        if (null === $Select) {
            return $Stage;
        }

        $Error = false;
        if (!isset( $Select['Type'] )) {
            $Error = true;
            $Stage .= new Warning('Schulart nicht gefunden');
        }
        if (!isset( $Select['Year'] )) {
            $Error = true;
            $Stage .= new Warning('Schuljahr nicht gefunden');
        }
        if ($Error) {
            return $Stage;
        }

        return new Redirect($Redirect, 0, array(
            'TypeId' => $Select['Type'],
            'YearId' => $Select['Year'],
        ));
    }

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null   $File
     * @param null                $TypeId
     * @param null                $YearId
     *
     * @return IFormInterface|Danger|string
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
            ini_set('memory_limit', '2G');

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
                //$File->getMimeType()
                /** @var PhpExcel $Document */
                $Document = Document::getDocument($File->getPathname());
                if (!$Document instanceof PhpExcel) {
                    $Form->setError('File', 'Fehler');
                    return $Form;
                }

                $X = $Document->getSheetColumnCount();
                $Y = $Document->getSheetRowCount();

                $error = array();
                /**
                 * Header -> Location
                 */
                $Location = array(
                    'Schüler_Schülernummer'               => null,
                    'Schüler_Name'                        => null,
                    'Schüler_Vorname'                     => null,
                    'Schüler_Klasse'                      => null,
                    'Schüler_Klassenstufe'                => null,
                    'Schüler_Geschlecht'                  => null,
                    'Schüler_Staatsangehörigkeit'         => null,
                    'Schüler_Straße'                      => null,
                    'Schüler_Plz'                         => null,
                    'Schüler_Wohnort'                     => null,
                    'Schüler_Ortsteil'                    => null,
                    'Schüler_Geburtsdatum'                => null,
                    'Schüler_Geburtsort'                  => null,
                    'Schüler_Konfession'                  => null,
                    'Schüler_Einschulung_am'              => null,
                    'Schüler_Aufnahme_am'                 => null,
                    'Schüler_Abgang_am'                   => null,
                    'Schüler_abgebende_Schule_ID'         => null,
                    'Schüler_aufnehmende_Schule_ID'       => null,
                    'Schüler_Schließfach_Schlüsselnummer' => null,
                    'Schüler_Schließfachnummer'           => null,
                    'Schüler_Krankenkasse'                => null,
                    'Sorgeberechtigter1_Name'             => null,
                    'Sorgeberechtigter1_Vorname'          => null,
                    'Sorgeberechtigter1_Straße'           => null,
                    'Sorgeberechtigter1_Plz'              => null,
                    'Sorgeberechtigter1_Wohnort'          => null,
                    'Sorgeberechtigter1_Ortsteil'         => null,
                    'Sorgeberechtigter2_Name'             => null,
                    'Sorgeberechtigter2_Vorname'          => null,
                    'Sorgeberechtigter2_Straße'           => null,
                    'Sorgeberechtigter2_Plz'              => null,
                    'Sorgeberechtigter2_Wohnort'          => null,
                    'Sorgeberechtigter2_Ortsteil'         => null,
                    'Kommunikation_Telefon1'              => null,
                    'Kommunikation_Telefon2'              => null,
                    'Kommunikation_Telefon3'              => null,
                    'Kommunikation_Telefon4'              => null,
                    'Kommunikation_Telefon5'              => null,
                    'Kommunikation_Telefon6'              => null,
                    'Kommunikation_Fax'                   => null,
                    'Kommunikation_Email'                 => null,
                    'Beförderung_Fahrtroute'              => null,
                    'Beförderung_Einsteigestelle'         => null,
                    'Fächer_Religionsunterricht'          => null,
                    'Fächer_Fremdsprache1'                => null,
                    'Fächer_Fremdsprache2'                => null,
                    'Fächer_Fremdsprache3'                => null,
                    'Fächer_Fremdsprache4'                => null,

                    'Schüler_Fotoerlaubnis'               => null,
                    'Schüler_Geschwister'                 => null,
                    'Schüler_letzte_Schulart'             => null,
                    'Schüler_Krankheiten'                 => null,
                    'Schüler_Medikamente'                 => null,
                    'Schüler_Behinderung_Hinweise'        => null,
                    'Schüler_Krankenversicherung_bei'     => null,
                    'Schüler_allgemeine_Bemerkungen'      => null,
                    'Beförderung_Hinweise'                => null,
                    'Schüler_Schulabschluss'              => null,
                    'Fächer_Fremdsprache1_von'            => null,
                    'Fächer_Fremdsprache1_bis'            => null,
                    'Fächer_Fremdsprache2_von'            => null,
                    'Fächer_Fremdsprache2_bis'            => null,
                    'Fächer_Fremdsprache3_von'            => null,
                    'Fächer_Fremdsprache3_bis'            => null,
                    'Fächer_Fremdsprache4_von'            => null,
                    'Fächer_Fremdsprache4_bis'            => null,
                    'Sorgeberechtigter_Status'           => null,
                    'Sorgeberechtigter1_Titel'            => null,
                    'Sorgeberechtigter1_Geschlecht'       => null,
                    'Sorgeberechtigter1_GO'               => null,
                    'Sorgeberechtigter1_GD'               => null,
                    'Sorgeberechtigter1_Beruf'            => null,
                    'Sorgeberechtigter2_Status'           => null,
                    'Sorgeberechtigter2_Titel'            => null,
                    'Sorgeberechtigter2_Geschlecht'       => null,
                    'Sorgeberechtigter2_GO'               => null,
                    'Sorgeberechtigter2_GD'               => null,
                    'Sorgeberechtigter2_Beruf'            => null,
                    'Sorgeberechtigter3_Name'             => null,
                    'Sorgeberechtigter3_Vorname'          => null,
                    'Sorgeberechtigter3_Straße'           => null,
                    'Sorgeberechtigter3_Plz'              => null,
                    'Sorgeberechtigter3_Wohnort'          => null,
                    'Sorgeberechtigter3_Ortsteil'         => null,
                    'Sorgeberechtigter3_Status'           => null,
                    'Sorgeberechtigter3_Titel'            => null,
                    'Sorgeberechtigter3_Geschlecht'       => null,
                    'Sorgeberechtigter3_GO'               => null,
                    'Sorgeberechtigter3_GD'               => null,
                    'Sorgeberechtigter3_Beruf'            => null,
                    'Sorgeberechtigter4_Name'             => null,
                    'Sorgeberechtigter4_Vorname'          => null,
                    'Sorgeberechtigter4_Straße'           => null,
                    'Sorgeberechtigter4_Plz'              => null,
                    'Sorgeberechtigter4_Wohnort'          => null,
                    'Sorgeberechtigter4_Ortsteil'         => null,
                    'Sorgeberechtigter4_Status'           => null,
                    'Sorgeberechtigter4_Titel'            => null,
                    'Sorgeberechtigter4_Geschlecht'       => null,
                    'Sorgeberechtigter4_GO'               => null,
                    'Sorgeberechtigter4_GD'               => null,
                    'Sorgeberechtigter4_Beruf'            => null,
                    'Fächer_Profilfach'                   => null,
                    'Fächer_Neigungskurs'                 => null,
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
                    $importService = new ImportService($Location, $Document);

                    $countStudent = 0;
                    $countCustody = 0;
                    $countCustodyExists = 0;

                    $tblType = Type::useService()->getTypeById($TypeId);
                    $tblYear = Term::useService()->getYearById($YearId);

                    $tblStudentAgreementCategoryPhoto = Student::useService()->getStudentAgreementCategoryById(1);
                    $tblStudentAgreementCategoryName = Student::useService()->getStudentAgreementCategoryById(2);

                    $tblCommonGenderMale = Common::useService()->getCommonGenderByName('Männlich');
                    $tblCommonGenderFemale = Common::useService()->getCommonGenderByName('Weiblich');

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
                            ),
                            '',
                            $tblType->getShortName() . '_Zeile_' . ($RunY + 1)
                        );

                        if ($tblPerson !== false) {
                            $countStudent++;

                            // Student Common
                            Common::useService()->insertMeta(
                                $tblPerson,
                                $importService->formatDateString('Schüler_Geburtsdatum', $RunY, $error),
                                trim($Document->getValue($Document->getCell($Location['Schüler_Geburtsort'], $RunY))),
                                trim($Document->getValue($Document->getCell($Location['Schüler_Geschlecht'],
                                    $RunY))) == 'm' ? $tblCommonGenderMale : $tblCommonGenderFemale,
                                trim($Document->getValue($Document->getCell($Location['Schüler_Staatsangehörigkeit'],
                                    $RunY))),
                                trim($Document->getValue($Document->getCell($Location['Schüler_Konfession'], $RunY))),
                                0,
                                '',
                                trim($Document->getValue($Document->getCell($Location['Schüler_allgemeine_Bemerkungen'], $RunY)))
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
                                        $cityCodeStudent = $importService->formatZipCode('Schüler_Plz', $RunY);

                                        Address::useService()->insertAddressToPerson(
                                            $tblPerson,
                                            $StreetName,
                                            $StreetNumber,
                                            $cityCodeStudent,
                                            trim($Document->getValue($Document->getCell($Location['Schüler_Wohnort'],
                                                $RunY))),
                                            trim($Document->getValue($Document->getCell($Location['Schüler_Ortsteil'],
                                                $RunY))),
                                            ''
                                        );

                                    }
                                }
                            }

                            // Student Contact
                            for ($i = 1; $i < 7; $i++) {
                                $PhoneNumber = trim($Document->getValue($Document->getCell($Location['Kommunikation_Telefon'.$i],
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
                            if (( $Level = trim($Document->getValue($Document->getCell($Location['Schüler_Klassenstufe'],
                                    $RunY))) ) != ''
                            ) {
                                $tblLevel = Division::useService()->insertLevel($tblType, $Level);
                                if ($tblLevel) {
                                    $Division = trim($Document->getValue($Document->getCell($Location['Schüler_Klasse'],
                                        $RunY)));
                                    if ($Division != '') {
                                        if (( $pos = strpos($Division, $Level) ) !== false) {
                                            if (strlen($Division) > ( ( $start = $pos + strlen($Level) ) )) {
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

                            $disease = '';
                            $disease1 = trim($Document->getValue($Document->getCell($Location['Schüler_Krankheiten'],
                                $RunY)));
                            if ($disease1 != '') {
                                $disease = 'Krankheiten: ' . $disease1;
                            }
                            $medication = trim($Document->getValue($Document->getCell($Location['Schüler_Medikamente'],
                                $RunY)));
                            $disease2 = trim($Document->getValue($Document->getCell($Location['Schüler_Behinderung_Hinweise'],
                                $RunY)));
                            if ($disease2 != '') {
                                $disease .= ($disease == '' ? '' : " \n") . 'Behinderung Hinweise: ' . $disease2;
                            }

                            $insurance = trim($Document->getValue($Document->getCell($Location['Schüler_Krankenkasse'],
                                $RunY)));

                            $insuranceState = trim($Document->getValue($Document->getCell($Location['Schüler_Krankenversicherung_bei'],
                                $RunY)));
                            if ($insuranceState != '') {
                                if ($insuranceState == '1') {
                                    // Familie Mutter
                                    $insuranceState = 5;
                                } elseif ($insuranceState == '2') {
                                    // Familie Vater
                                    $insuranceState = 4;
                                } else {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Schüler_Krankenversicherung_bei:' . $insuranceState
                                        . ' konnte nicht angelegt werden.';
                                }
                            }

                            $tblStudentMedicalRecord = Student::useService()->insertStudentMedicalRecord(
                                $disease,
                                $medication,
                                $insurance,
                                $insuranceState
                            );

                            $tblStudentTransport = null;
                            $route = trim($Document->getValue($Document->getCell($Location['Beförderung_Fahrtroute'],
                                $RunY)));
                            $stationEntrance = trim($Document->getValue($Document->getCell($Location['Beförderung_Einsteigestelle'],
                                $RunY)));
                            $transportRemark = trim($Document->getValue($Document->getCell($Location['Beförderung_Hinweise'],
                                $RunY)));
                            if ($route !== '' || $stationEntrance !== '' || $transportRemark != '') {
                                $tblStudentTransport = Student::useService()->insertStudentTransport(
                                    $route,
                                    $stationEntrance,
                                    '',
                                    $transportRemark ? 'Beförderung Hinweise: ' . $transportRemark : ''
                                );
                            }

                            $sibling = trim($Document->getValue($Document->getCell($Location['Schüler_Geschwister'],
                                $RunY)));
                            $tblSiblingRank = false;
                            if ($sibling !== '') {
                                if (!($tblSiblingRank = Relationship::useService()->getSiblingRankById(intval($sibling)))) {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Geschwisterkind konnte nicht angelegt werden.';
                                }
                            }

                            if ($tblSiblingRank) {
                                $tblStudentBilling = Student::useService()->insertStudentBilling($tblSiblingRank);
                            } else {
                                $tblStudentBilling = null;
                            }

                            $tblStudentBaptism = null;
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
                                $importService->setStudentAgreement(
                                    'Schüler_Fotoerlaubnis',
                                    $RunY,
                                    $tblStudent,
                                    $tblStudentAgreementCategoryName,
                                    '1'
                                );
                                $importService->setStudentAgreement(
                                    'Schüler_Fotoerlaubnis',
                                    $RunY,
                                    $tblStudent,
                                    $tblStudentAgreementCategoryPhoto,
                                    '1'
                                );

                                // Schülertransfer
                                $enrollmentDate = $importService->formatDateString('Schüler_Einschulung_am', $RunY, $error);
                                if ($enrollmentDate !== '') {
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

                                $diploma = $arriveDate = trim($Document->getValue($Document->getCell($Location['Schüler_Schulabschluss'],
                                    $RunY)));
                                if ($diploma != '') {
                                    switch ($diploma) {
                                        case 'HSR': $tblCourse = Course::useService()->getCourseByName('Gymnasium'); break;
                                        case 'RSA': $tblCourse = Course::useService()->getCourseByName('Realschule'); break;
                                        case 'HSA': $tblCourse = Course::useService()->getCourseByName('Hauptschule'); break;
                                        default: $tblCourse = false;
                                    }

                                    if ($tblCourse) {
                                        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('Process');
                                        Student::useService()->insertStudentTransfer(
                                            $tblStudent,
                                            $tblStudentTransferType,
                                            null,
                                            null,
                                            $tblCourse
                                        );
                                    } else {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Schüler_Schulabschluss:' . $diploma . ' nicht gefunden.';
                                    }
                                }


                                $arriveDate = $importService->formatDateString('Schüler_Aufnahme_am', $RunY, $error);

                                $arriveTypeShort = trim($Document->getValue($Document->getCell($Location['Schüler_letzte_Schulart'],
                                    $RunY)));
                                $arriveType = false;
                                if ($arriveTypeShort != '') {
                                    if ($arriveTypeShort == 'GY') {
                                        $arriveTypeShort = 'Gy';
                                    }
                                    if (!($arriveType = Type::useService()->getTypeByShortName($arriveTypeShort))) {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Schüler_letzte_Schulart:' . $arriveTypeShort . ' nicht gefunden.';
                                    }
                                }

                                $arriveSchool = null;
                                $arriveSchoolId = trim($Document->getValue($Document->getCell($Location['Schüler_abgebende_Schule_ID'],
                                    $RunY)));
                                if ($arriveSchoolId != '') {
                                    if (($company = Company::useService()->getCompanyByImportId($arriveSchoolId))) {
                                        $arriveSchool = $company;
                                    } else {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Schüler_abgebende_Schule_ID:' . $arriveSchoolId . ' nicht gefunden.';
                                    }
                                }

                                $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('Arrive');
                                Student::useService()->insertStudentTransfer(
                                    $tblStudent,
                                    $tblStudentTransferType,
                                    $arriveSchool ? $arriveSchool : null,
                                    $arriveType ? $arriveType : null,
                                    null,
                                    $arriveDate,
                                    ''
                                );

                                $leaveSchool = null;
                                $leaveSchoolId = trim($Document->getValue($Document->getCell($Location['Schüler_aufnehmende_Schule_ID'],
                                    $RunY)));
                                if ($leaveSchoolId != '') {
                                    if (($company = Company::useService()->getCompanyByImportId($leaveSchoolId))) {
                                        $leaveSchool = $company;
                                    } else {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Schüler_aufnehmende_Schule_ID:' . $leaveSchoolId . ' nicht gefunden.';
                                    }
                                }

                                $leaveDate = $importService->formatDateString('Schüler_Abgang_am', $RunY, $error);
                                $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('Leave');
                                Student::useService()->insertStudentTransfer(
                                    $tblStudent,
                                    $tblStudentTransferType,
                                    $leaveSchool ? $leaveSchool : null,
                                    null,
                                    null,
                                    $leaveDate,
                                    ''
                                );

                                // Fächer
                                $subjectReligion = trim($Document->getValue($Document->getCell($Location['Fächer_Religionsunterricht'],
                                    $RunY)));
                                if ($subjectReligion !== '') {
                                    if (($tblSubject = Subject::useService()->getSubjectByAcronym($subjectReligion))) {
                                        Student::useService()->addStudentSubject(
                                            $tblStudent,
                                            Student::useService()->getStudentSubjectTypeByIdentifier('Religion'),
                                            Student::useService()->getStudentSubjectRankingByIdentifier('1'),
                                            $tblSubject
                                        );
                                    } else {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Fächer_Religionsunterricht:' . $subjectReligion . ' nicht gefunden.';
                                    }
                                }

                                // Profilfach
                                if ($tblType->getShortName() == 'Gy') {
                                    $profile = trim($Document->getValue($Document->getCell($Location['Fächer_Profilfach'],
                                        $RunY)));
                                    if ($profile != '') {
                                        if (($tblSubjectProfile = Subject::useService()->getSubjectByAcronym($profile))) {
                                            Student::useService()->addStudentSubject(
                                                $tblStudent,
                                                Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE'),
                                                Student::useService()->getStudentSubjectRankingByIdentifier('1'),
                                                $tblSubjectProfile
                                            );
                                        } else {
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Fächer_Profilfach:' . $profile . ' nicht gefunden.';
                                        }
                                    }
                                }

                                // Neigungskurs
                                $subjectOrientation = trim($Document->getValue($Document->getCell($Location['Fächer_Neigungskurs'],
                                    $RunY)));
                                if ($subjectOrientation != '') {
                                    if (($tblSubjectOrientation = Subject::useService()->insertSubject($subjectOrientation, $subjectOrientation))) {
                                        Student::useService()->addStudentSubject(
                                            $tblStudent,
                                            Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'),
                                            Student::useService()->getStudentSubjectRankingByIdentifier('1'),
                                            $tblSubjectOrientation
                                        );
                                    } else {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Fächer_Neigungskurs:' . $subjectOrientation . ' konnte nicht angelegt werden.';
                                    }
                                }

                                for ($i = 1; $i < 5; $i++) {
                                    $subjectLanguage = trim($Document->getValue($Document->getCell($Location['Fächer_Fremdsprache'.$i],
                                        $RunY)));
                                    if ($subjectLanguage !== '') {
                                        $tblSubject = Subject::useService()->getSubjectByAcronym($subjectLanguage);
                                        if ($tblSubject) {
                                            $levelFrom = trim($Document->getValue($Document->getCell($Location['Fächer_Fremdsprache'.$i.'_von'],
                                                $RunY)));
                                            $tblLevelFrom = false;
                                            if ($levelFrom != '') {
                                                $level = intval($levelFrom);
                                                if ($level > 0 && $level < 13) {
                                                    $tblLevelFrom = Division::useService()->insertLevel($tblType, $level);
                                                } else {
                                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Fächer_Fremdsprache' . $i . '_von:' . $levelFrom . ' nicht gefunden.';
                                                }
                                            }

                                            $levelTill = trim($Document->getValue($Document->getCell($Location['Fächer_Fremdsprache'.$i.'_bis'],
                                                $RunY)));
                                            $tblLevelTill = false;
                                            if ($levelTill != '') {
                                                $level = intval($levelTill);
                                                if ($level > 0 && $level < 13) {
                                                    $tblLevelTill = Division::useService()->insertLevel($tblType, $level);
                                                } else {
                                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Fächer_Fremdsprache' . $i . '_bis:' . $levelTill . ' nicht gefunden.';
                                                }
                                            }

                                            Student::useService()->addStudentSubject(
                                                $tblStudent,
                                                Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'),
                                                Student::useService()->getStudentSubjectRankingByIdentifier($i),
                                                $tblSubject,
                                                $tblLevelFrom ? $tblLevelFrom : null,
                                                $tblLevelTill ? $tblLevelTill : null
                                            );
                                        } else {
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Fächer_Fremdsprache' . $i . ':' . $subjectLanguage . ' nicht gefunden.';
                                        }
                                    }
                                }
                            }

                            // Sorgeberechtigte
                            for ($i = 1; $i < 5; $i++) {
                                $this->setCustody(
                                    $i,
                                    $tblPerson,
                                    $tblType,
                                    $Document,
                                    $Location,
                                    $RunY,
                                    $error,
                                    $importService,
                                    $tblCommonGenderMale,
                                    $tblCommonGenderFemale,
                                    $countCustody,
                                    $countCustodyExists
                                );
                            }
                        }
                    }

                    return
                        new Success('Es wurden '.$countStudent.' Schüler erfolgreich angelegt.').
                        new Success('Es wurden '.($countCustody).' Sorgeberechtigte erfolgreich angelegt.').
                        ( $countCustodyExists > 0 ?
                            new Warning($countCustodyExists.' Sorgeberechtigte exisistieren bereits.') : '' )
                        . new Panel(
                            'Fehler',
                            $error,
                            Panel::PANEL_TYPE_DANGER
                        );
                } else {
                    return new Warning(json_encode($Location))
                    . new Danger(
                        "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }
        return new Danger('File nicht gefunden');
    }

    /**
     * @param $ranking
     * @param TblPerson $tblPerson
     * @param TblType $tblType
     * @param $Document
     * @param $Location
     * @param $RunY
     * @param $error
     * @param $importService
     * @param $tblCommonGenderMale
     * @param $tblCommonGenderFemale
     * @param $countAdd
     * @param $countExists
     */
    private function setCustody(
        $ranking,
        TblPerson $tblPerson,
        TblType $tblType,
        $Document,
        $Location,
        $RunY,
        $error,
        $importService,
        $tblCommonGenderMale,
        $tblCommonGenderFemale,
        &$countAdd,
        &$countExists
    ) {

        $tblPersonCustody = null;
        $CustodyFirstName = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter' . $ranking . '_Vorname'],
            $RunY)));
        $CustodyLastName = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter' . $ranking . '_Name'],
            $RunY)));
        $cityCode = $importService->formatZipCode('Sorgeberechtigter' . $ranking . '_Plz', $RunY);
        if ($CustodyLastName !== '') {
            $status = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter' . ($ranking == 1 ? '' : $ranking) . '_Status'],
                $RunY)));
            // Beziehungstyp
            switch ($status) {
                case 'FAM':
                case 'ELT':
                case 'NMU':
                case 'NVA': $tblRelationShipType = Relationship::useService()->getTypeByName('Sorgeberechtigt');
                    $relationShipRanking = $ranking;
                    break;
                default: $tblRelationShipType = Relationship::useService()->getTypeByName('Notfallkontakt');
                    $relationShipRanking = null;
            }
            // alleinerziehend
            switch ($status) {
                case 'NMU':
                case 'NVA': $isSingleParent = true; break;
                default: $isSingleParent = false;
            }

            $tblPersonCustodyExists = $this->usePeoplePerson()->getPersonExists(
                $CustodyFirstName,
                $CustodyLastName,
                $cityCode
            );
            if (!$tblPersonCustodyExists) {
                $tblPersonCustody = $this->usePeoplePerson()->insertPerson(
                    null,
                    trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter' . $ranking . '_Titel'], $RunY))),
                    trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter' . $ranking . '_Vorname'],
                        $RunY))),
                    '',
                    $CustodyLastName,
                    array(
                        0 => Group::useService()->getGroupById(1),          //Personendaten
                        1 => Group::useService()->getGroupById(4)           //Sorgeberechtigt
                    ),
                    '',
                    $tblType->getShortName() . '_Zeile_' . ($RunY + 1) . '_S' . $ranking
                );

                $gender = strtolower(trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter' . $ranking . '_Geschlecht'],
                    $RunY))));
                switch ($gender) {
                    case 'm': $tblCommonGender = $tblCommonGenderMale; break;
                    case 'w': $tblCommonGender = $tblCommonGenderFemale; break;
                    default: $tblCommonGender = false;
                }

                Common::useService()->insertMeta(
                    $tblPersonCustody,
                    $importService->formatDateString('Sorgeberechtigter' . $ranking . '_GD', $RunY, $error),
                    trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter' . $ranking . '_GO'], $RunY))),
                    $tblCommonGender ? $tblCommonGender : null,
                    '',
                    '',
                    0,
                    '',
                    ''
                );

                $occupation = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter' . $ranking . '_Beruf'],
                    $RunY)));
                if ($occupation) {
                    Custody::useService()->insertMeta($tblPersonCustody, $occupation, '', '');
                }

                Relationship::useService()->insertRelationshipToPerson(
                    $tblPersonCustody,
                    $tblPerson,
                    $tblRelationShipType,
                    $status,
                    $relationShipRanking,
                    $isSingleParent
                );

                // Sorgeberechtigter1 Address
                if (trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter' . $ranking . '_Wohnort'],
                        $RunY))) != ''
                ) {
                    $Street = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter' . $ranking . '_Straße'],
                        $RunY)));
                    if (preg_match_all('!\d+!', $Street, $matches)) {
                        $pos = strpos($Street, $matches[0][0]);
                        if ($pos !== null) {
                            $StreetName = trim(substr($Street, 0, $pos));
                            $StreetNumber = trim(substr($Street, $pos));

                            Address::useService()->insertAddressToPerson(
                                $tblPersonCustody,
                                $StreetName,
                                $StreetNumber,
                                $cityCode,
                                trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter' . $ranking . '_Wohnort'],
                                    $RunY))),
                                trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter' . $ranking . '_Ortsteil'],
                                    $RunY))),
                                ''
                            );

                        }
                    }
                }

                $countAdd++;
            } else {

                Relationship::useService()->insertRelationshipToPerson(
                    $tblPersonCustodyExists,
                    $tblPerson,
                    $tblRelationShipType,
                    $status,
                    $relationShipRanking,
                    $isSingleParent
                );

                $countExists++;
            }
        }
    }

    /**
     * @return Person
     */
    public static function usePeoplePerson()
    {

        return new Person(
            new Identifier('People', 'Person', null, null, Consumer::useService()->getConsumerBySession()),
            __DIR__.'/../../../People/Person/Service/Entity', 'SPHERE\Application\People\Person\Service\Entity'
        );
    }

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null   $File
     *
     * @return IFormInterface|String
     */
    public function createTeachersFromFile(
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
                $File = $File->move($File->getPath(), $File->getFilename().'.'.$File->getClientOriginalExtension());
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
                    'Lehrerkürzel' => null,
                    'Name'         => null,
                    'Vorname'      => null,
                    'Anrede'       => null,
                    'Geschlecht'   => null,
                    'Straße'       => null,
                    'Plz'          => null,
                    'Wohnort'      => null,
                    'Ortsteil'     => null,
                    'Geburtsdatum' => null,
                    'Geburtsort'   => null,
                    'Geburtsname'  => null,
                    'Konfession'   => null,
                    'Telefon1'     => null,
                    'Telefon2'     => null,
                    'Fax'          => null,
                    'EMail'        => null,
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
                    $countTeacher = 0;
                    $tblCommonGenderMale = Common::useService()->getCommonGenderByName('Männlich');
                    $tblCommonGenderFemale = Common::useService()->getCommonGenderByName('Weiblich');

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
                        if ($lastName) {

                            $gender = trim($Document->getValue($Document->getCell($Location['Geschlecht'],
                                $RunY))) == 'm' ? $tblCommonGenderMale : $tblCommonGenderFemale;

                            $tblPerson = $this->usePeoplePerson()->insertPerson(
                                $this->usePeoplePerson()->getSalutationById($gender),
                                '',
                                trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY))),
                                '',
                                $lastName,
                                array(
                                    0 => Group::useService()->getGroupById(1),           //Personendaten
                                    1 => Group::useService()->getGroupById(5)            //Mitarbeiter
                                ),
                                trim($Document->getValue($Document->getCell($Location['Geburtsname'], $RunY)))
                            );

                            if ($tblPerson !== false) {
                                $countTeacher++;

                                // Teacher Common
                                Common::useService()->insertMeta(
                                    $tblPerson,
                                    trim($Document->getValue($Document->getCell($Location['Geburtsdatum'],
                                        $RunY))),
                                    trim($Document->getValue($Document->getCell($Location['Geburtsort'],
                                        $RunY))),
                                    $gender,
                                    '',
                                    trim($Document->getValue($Document->getCell($Location['Konfession'],
                                        $RunY))),
                                    0,
                                    '',
                                    trim($Document->getValue($Document->getCell($Location['Lehrerkürzel'],
                                        $RunY)))
                                );

                                // Teacher Address
                                if (trim($Document->getValue($Document->getCell($Location['Wohnort'],
                                        $RunY))) != ''
                                ) {
                                    $Street = trim($Document->getValue($Document->getCell($Location['Straße'],
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
                                                trim($Document->getValue($Document->getCell($Location['Plz'],
                                                    $RunY))),
                                                trim($Document->getValue($Document->getCell($Location['Wohnort'],
                                                    $RunY))),
                                                trim($Document->getValue($Document->getCell($Location['Ortsteil'],
                                                    $RunY))),
                                                ''
                                            );

                                        }
                                    }
                                }

                                // Teacher Contact
                                for ($i = 1; $i < 3; $i++) {
                                    $PhoneNumber = trim($Document->getValue($Document->getCell($Location['Telefon'.$i],
                                        $RunY)));
                                    if ($PhoneNumber != '') {
                                        Phone::useService()->insertPhoneToPerson($tblPerson, $PhoneNumber,
                                            Phone::useService()->getTypeById(1), '');
                                    }
                                }
                                $FaxNumber = trim($Document->getValue($Document->getCell($Location['Fax'],
                                    $RunY)));
                                if ($FaxNumber != '') {
                                    Phone::useService()->insertPhoneToPerson($tblPerson, $FaxNumber,
                                        Phone::useService()->getTypeById(7), '');
                                }
                                $MailAddress = trim($Document->getValue($Document->getCell($Location['EMail'],
                                    $RunY)));
                                if ($MailAddress != '') {
                                    Mail::useService()->insertMailToPerson($tblPerson, $MailAddress,
                                        Mail::useService()->getTypeById(1), '');
                                }
                            }
                        }
                    }
                    return
                        new Success('Es wurden '.$countTeacher.' Lehrer erfolgreich angelegt.');
                } else {
                    return new Warning(json_encode($Location))
                    . new Danger(
                        "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }
        return new Danger('File nicht gefunden');
    }

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null   $File
     * @param null                $TypeId
     * @param null                $YearId
     *
     * @return IFormInterface|String
     */
    public function createDivisionsFromFile(
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
                $File = $File->move($File->getPath(), $File->getFilename().'.'.$File->getClientOriginalExtension());
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
                    'Klasse'                            => null,
                    'Klassenstufe'                      => null,
                    'Klassenlehrer_kurz'                => null,
                    'Stellvertreter_Klassenlehrer_kurz' => null,
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
                    $countDivision = 0;
                    $countAddDivisionTeacher = 0;
                    $countTeacherNotExists = 0;

                    $tblType = Type::useService()->getTypeById($TypeId);
                    $tblYear = Term::useService()->getYearById($YearId);

                    for ($RunY = 1; $RunY < $Y; $RunY++) {

                        if (( $Level = trim($Document->getValue($Document->getCell($Location['Klassenstufe'],
                                $RunY))) ) != ''
                        ) {
                            $tblLevel = Division::useService()->insertLevel($tblType, $Level);
                            if ($tblLevel) {
                                $Division = trim($Document->getValue($Document->getCell($Location['Klasse'],
                                    $RunY)));
                                if ($Division != '') {
                                    if (( $pos = strpos($Division, $Level) ) !== false) {
                                        if (strlen($Division) > ( ( $start = $pos + strlen($Level) ) )) {
                                            $Division = substr($Division, $start);
                                        }
                                    }
                                    $tblDivision = Division::useService()->insertDivision($tblYear, $tblLevel,
                                        $Division);
                                    if ($tblDivision) {

                                        $countDivision++;
                                        $teacherCode = trim($Document->getValue($Document->getCell($Location['Klassenlehrer_kurz'],
                                            $RunY)));
                                        if ($teacherCode !== '') {
                                            $tblPerson = $this->usePeoplePerson()->getTeacherByRemark($teacherCode);
                                            if ($tblPerson) {
                                                Division::useService()->insertDivisionTeacher($tblDivision, $tblPerson);
                                                $countAddDivisionTeacher++;
                                            } else {
                                                $countTeacherNotExists++;
                                            }
                                        }
                                        $teacherCode = trim($Document->getValue($Document->getCell($Location['Stellvertreter_Klassenlehrer_kurz'],
                                            $RunY)));
                                        if ($teacherCode !== '') {
                                            $tblPerson = $this->usePeoplePerson()->getTeacherByRemark($teacherCode);
                                            if ($tblPerson) {
                                                Division::useService()->insertDivisionTeacher($tblDivision, $tblPerson);
                                                $countAddDivisionTeacher++;
                                            } else {
                                                $countTeacherNotExists++;
                                            }
                                        }
                                    }
                                }
                            }

                        }
                    }
                    return
                        new Success('Es wurden '.$countDivision.' Klassen erfolgreich angelegt.').
                        new Success('Es wurden '.$countAddDivisionTeacher.' Klassenlehrer und Stellvertreter erfolgreich zugeordnet.').
                        ( $countTeacherNotExists > 0 ?
                            new Warning($countTeacherNotExists.' Lehrer nicht gefunden.') : '' );
                } else {
                    return new Warning(json_encode($Location))
                    . new Danger(
                        "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }
        return new Danger('File nicht gefunden');
    }

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null   $File
     *
     * @return IFormInterface|Danger|Success|string
     */
    public function createCompaniesFromFile(
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
                $File = $File->move($File->getPath(), $File->getFilename().'.'.$File->getClientOriginalExtension());
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
                    'E.nummer' => null,
                    'Einrichtungsname'   => null,
                    'Straße'   => null,
                    'Plz'   => null,
                    'Ort'   => null
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
                    $countCompany = 0;

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        $companyName = trim($Document->getValue($Document->getCell($Location['Einrichtungsname'], $RunY)));

                        if ($companyName) {
                            $tblCompany = Company::useService()->insertCompany(
                                $companyName,
                                '',
                                '',
                                trim($Document->getValue($Document->getCell($Location['E.nummer'], $RunY)))
                            );
                            if ($tblCompany) {
                                $countCompany++;

                                \SPHERE\Application\Corporation\Group\Group::useService()->addGroupCompany(
                                    \SPHERE\Application\Corporation\Group\Group::useService()->getGroupByMetaTable('COMMON'),
                                    $tblCompany
                                );
                                \SPHERE\Application\Corporation\Group\Group::useService()->addGroupCompany(
                                    \SPHERE\Application\Corporation\Group\Group::useService()->getGroupByMetaTable('SCHOOL'),
                                    $tblCompany
                                );

                                list($streetName, $streetNumber) = $importService->splitStreet('Straße', $RunY);
                                $cityName = trim($Document->getValue($Document->getCell($Location['Ort'], $RunY)));
                                $cityCode = $importService->formatZipCode('Plz', $RunY);

                                if ($streetName != '' && $streetNumber != '' && $cityName != '' && $cityCode != '') {
                                    Address::useService()->insertAddressToCompany(
                                        $tblCompany,
                                        $streetName,
                                        $streetNumber,
                                        $cityCode,
                                        $cityName,
                                        '',
                                        ''
                                    );
                                }
                            }
                        }
                    }

                    return new Success('Es wurden '.$countCompany.' Institutionen erfolgreich angelegt.');
                } else {
                    return new Info(json_encode($Location))
                        . new Danger("File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }

        return new Danger('File nicht gefunden');
    }
}
