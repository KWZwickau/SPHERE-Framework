<?php

namespace SPHERE\Application\Transfer\Import\WVSZ;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblType;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Transfer\Import\Service as ImportService;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
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
 * @package SPHERE\Application\Transfer\Import\WVSZ
 */
class Service
{
    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null $File
     *
     * @return IFormInterface|Danger|string
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
                    'Name' => null,
                    'Vorname' => null,

                    'MB-Mehrfachbehindert' => null,
                    'SMB-Schwerstmehrfachbehindert' => null,
                    'Erhöhungsfaktor SMB Schule' => null,
                    'Erhöhungsfaktor SMB LaSuB' => null,
                    'Bemerkungen zu SMB' => null,
                    'GdB' => null,
                    'Merkzeichen' => null,
                    'Gültig bis' => null,

                    'S1_Notfall_Festnetz' => null,
                    'S1_Notfall_Mobil' => null,
                    'S2_Notfall_Festnetz' => null,
                    'S2_Notfall_Mobil' => null,

                    'Klasse/Kurs' => null,
                    'Schulart' => null,
                    'Stufe' => null,

                    'Medikamente' => null,
                    'Notfallmedizin' => null,
                    'Vers.nr.' => null,
                    'Versichert bei' => null
                );

                for ($RunX = 0; $RunX < $X; $RunX++) {
                    $Value = trim($Document->getValue($Document->getCell($RunX, 1)));
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
                    $countMissingStudent = 0;

                    $error = array();

                    $tblYear = $importService->insertSchoolYear(20);
                    $tblSchoolTypePrimary = Type::useService()->getTypeByName('Grundschule');
                    // wird umbenannt -> danach auch Kürzel verfügbar
                    if (!($tblSchoolTypeSpecialNeeds = Type::useService()->getTypeByName('allgemein bildende Förderschule'))) {
                        $tblSchoolTypeSpecialNeeds = Type::useService()->getTypeByName('Förderschule');
                    }
                    $tblCompany = Company::useService()->insertCompany('Werner-Vogel-Schulzentrum');

                    for ($RunY = 2; $RunY < $Y; $RunY++) {
                        set_time_limit(300);
                        // Student
                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));

                        $tblPerson = Person::useService()->getPersonByImportId($RunY + 1);
                        if (!($tblPerson && $tblPerson->getFirstName() == $firstName && $tblPerson->getLastName() == $lastName)) {
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler wurde nicht gefunden';
                            $countMissingStudent++;
                        } else {
                            $countStudent++;
                            $tblStudent = $tblPerson->getStudent();
                            $tblStudentSpecialNeedsLevel = false;

                            // Krankenakte
                            $medication = trim($Document->getValue($Document->getCell($Location['Medikamente'], $RunY)));
                            if (($emergencyMedication = trim($Document->getValue($Document->getCell($Location['Notfallmedizin'], $RunY))))) {
                                $emergencyMedication = 'Notfallmedizin: ' . $emergencyMedication;
                                if ($medication) {
                                    $medication = $emergencyMedication . " \n \n" . $medication;
                                } else {
                                    $medication = $emergencyMedication;
                                }
                            }
                            $insuranceNumber = trim($Document->getValue($Document->getCell($Location['Vers.nr.'], $RunY)));
                            if (($insuranceState = trim($Document->getValue($Document->getCell($Location['Versichert bei'], $RunY))))) {
                                if ($insuranceState == 'Mutter') {
                                    $insuranceState = 5;
                                } elseif ($insuranceState == 'Vater') {
                                    $insuranceState = 4;
                                } elseif ($insuranceState == 'Privat') {
                                    $insuranceState = 3;
                                } else {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Versichert bei: ' . $insuranceState . ' nicht gefunden';
                                    $insuranceState = '';
                                }
                            }
                            if ($tblStudent
                                && ($tblStudentMedicalRecord = $tblStudent->getTblStudentMedicalRecord())
                            ) {
                                Student::useService()->updateStudentMedicalRecordService(
                                    $tblStudentMedicalRecord,
                                    $tblStudentMedicalRecord->getDisease(),
                                    $medication,
                                    $tblStudentMedicalRecord->getAttendingDoctor(),
                                    $insuranceState,
                                    $tblStudentMedicalRecord->getInsurance(),
                                    $insuranceNumber,
                                    $tblStudentMedicalRecord->getMasernDate() ? $tblStudentMedicalRecord->getMasernDate() : null,
                                    $tblStudentMedicalRecord->getMasernDocumentType() ? $tblStudentMedicalRecord->getMasernDocumentType() : null,
                                    $tblStudentMedicalRecord->getMasernCreatorType() ? $tblStudentMedicalRecord->getMasernCreatorType() : null
                                );
                            } else {
                                $tblStudentMedicalRecord = Student::useService()->insertStudentMedicalRecord(
                                    '',
                                    $medication,
                                    '',
                                    $insuranceState,
                                    '',
                                    null,
                                    null,
                                    null,
                                    $insuranceNumber
                                );
                            }

                            $levelName = trim($Document->getValue($Document->getCell($Location['Stufe'], $RunY)));
                            if ($levelName) {
                                if (!($tblStudentSpecialNeedsLevel = Student::useService()->getStudentSpecialNeedsLevelByName($levelName))) {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Stufe: ' . $levelName . ' nicht gefunden';
                                }
                            }

                            $tblStudentSpecialNeeds = Student::useService() ->createStudentSpecialNeeds(
                                (trim($Document->getValue($Document->getCell($Location['MB-Mehrfachbehindert'], $RunY))) == 'x'),
                                (trim($Document->getValue($Document->getCell($Location['SMB-Schwerstmehrfachbehindert'], $RunY))) == 'x'),
                                trim($Document->getValue($Document->getCell($Location['Erhöhungsfaktor SMB Schule'], $RunY))),
                                trim($Document->getValue($Document->getCell($Location['Erhöhungsfaktor SMB LaSuB'], $RunY))),
                                trim($Document->getValue($Document->getCell($Location['Bemerkungen zu SMB'], $RunY))),
                                trim($Document->getValue($Document->getCell($Location['GdB'], $RunY))),
                                trim($Document->getValue($Document->getCell($Location['Merkzeichen'], $RunY))),
                                trim($Document->getValue($Document->getCell($Location['Gültig bis'], $RunY))),
                                $tblStudentSpecialNeedsLevel ? $tblStudentSpecialNeedsLevel : null
                            );
                            if ($tblStudentSpecialNeeds) {
                                if ($tblStudent) {
                                    Student::useService()->insertStudent(
                                        $tblPerson,
                                        '',
                                        $tblStudentMedicalRecord,
                                        ($tblStudentTransport = $tblStudent->getTblStudentTransport()) ? $tblStudentTransport : null,
                                        ($tblStudentBilling = $tblStudent->getTblStudentBilling()) ? $tblStudentBilling : null,
                                        ($tblStudentLocker = $tblStudent->getTblStudentLocker()) ? $tblStudentLocker : null,
                                        ($tblStudentBaptism = $tblStudent->getTblStudentBaptism()) ? $tblStudentBaptism : null,
                                        ($tblStudentIntegration = $tblStudent->getTblStudentIntegration()) ? $tblStudentIntegration : null,
                                        $tblStudentSpecialNeeds,
                                        $tblStudent->getSchoolAttendanceStartDate()
                                    );
                                } else {
                                    Student::useService()->insertStudent(
                                        $tblPerson,
                                        '',
                                        $tblStudentMedicalRecord,
                                        null,
                                        null,
                                        null,
                                        null,
                                        null,
                                        $tblStudentSpecialNeeds
                                    );
                                }
                            }

                            $tblDivision = false;
                            $type = trim($Document->getValue($Document->getCell($Location['Schulart'], $RunY)));
                            if ($type == 'GS') {
                                if (($level = trim($Document->getValue($Document->getCell($Location['Klasse/Kurs'], $RunY))))) {
                                    $tblSchoolType = $tblSchoolTypePrimary;
                                    if (($tblLevel = Division::useService()->insertLevel($tblSchoolType, $level))) {
                                        $tblDivision = Division::useService()->insertDivision(
                                            $tblYear,
                                            $tblLevel,
                                            '',
                                            '',
                                            $tblCompany ? $tblCompany : null
                                        );

                                        if ($tblDivision) {
                                            Division::useService()->insertDivisionStudent($tblDivision, $tblPerson);
                                        }
                                    }
                                }
                            } elseif ($type == 'FS') {
                                if (($division = trim($Document->getValue($Document->getCell($Location['Klasse/Kurs'], $RunY))))) {
                                    $tblSchoolType = $tblSchoolTypeSpecialNeeds;
                                    if (($tblLevel = Division::useService()->insertLevel($tblSchoolType, '', ''))) {
                                        // Sonderfälle: 1,2,3
                                        if (strlen($division) == 1) {
                                            $tblDivision = Division::useService()->insertDivision(
                                                $tblYear,
                                                $tblLevel,
                                                'U4',
                                                '',
                                                $tblCompany ? $tblCompany : null
                                            );
                                        } else {
                                            $tblDivision = Division::useService()->insertDivision(
                                                $tblYear,
                                                $tblLevel,
                                                $division,
                                                '',
                                                $tblCompany ? $tblCompany : null
                                            );
                                        }

                                        if ($tblDivision) {
                                            Division::useService()->insertDivisionStudent($tblDivision, $tblPerson);
                                        }
                                    }
                                }
                            } else {
                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Schulart: ' . $type . ' nicht gefunden.';
                            }

                            if (!$tblDivision) {
                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler konnte keiner Klasse zugeordnet werden.';
                            }

                            if (($phoneNumber = trim($Document->getValue($Document->getCell($Location['S1_Notfall_Festnetz'], $RunY))))) {
                                $this->insertPhoneNumber($phoneNumber, $tblPerson, Phone::useService()->getTypeById(5));
                            }
                            if (($phoneNumber = trim($Document->getValue($Document->getCell($Location['S1_Notfall_Mobil'], $RunY))))) {
                                $this->insertPhoneNumber($phoneNumber, $tblPerson, Phone::useService()->getTypeById(6));
                            }
                            if (($phoneNumber = trim($Document->getValue($Document->getCell($Location['S2_Notfall_Festnetz'], $RunY))))) {
                                $this->insertPhoneNumber($phoneNumber, $tblPerson, Phone::useService()->getTypeById(5));
                            }
                            if (($phoneNumber = trim($Document->getValue($Document->getCell($Location['S2_Notfall_Mobil'], $RunY))))) {
                                $this->insertPhoneNumber($phoneNumber, $tblPerson, Phone::useService()->getTypeById(6));
                            }
                        }
                    }

                    return
                        new Success('Es wurden ' . $countStudent . ' Schüler erfolgreich gefunden.')
                        . new Warning('Es wurden ' . $countMissingStudent . ' Schüler nicht gefunden.', new Exclamation())
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
     * @param $phoneNumber
     * @param TblPerson $tblPerson
     * @param TblType $tblType
     *
     * @param string $Separator
     */
    private function insertPhoneNumber($phoneNumber, TblPerson $tblPerson, TblType $tblType, $Separator = ',')
    {
        if ($Separator != '' && (strpos($phoneNumber, $Separator) !== false)) {
            $array = preg_split('/' . $Separator . '/', $phoneNumber);
        } else {
            $array = array(0 => $phoneNumber);
        }

        foreach ($array as $item) {
            $number = trim($item);

            $remark = '';
            // Klammern in die Bemerkung schreiben
            if (($startPos = strpos($number, '(')) && ($endPos = strpos($number, ')'))) {
                $remark = trim(substr($number, $startPos + 1, $endPos - $startPos - 1));
                $number = trim(substr($number, 0, $startPos - 1));
            }

            Phone::useService()->insertPhoneToPerson($tblPerson, $number, $tblType, $remark);
        }
    }
}