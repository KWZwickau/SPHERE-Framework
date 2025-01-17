<?php

namespace SPHERE\Application\Transfer\Untis\Import\StudentCourse;

/**
 * GPU010.TXT
 * https://platform.untis.at/HTML/WebHelp/de/untis/hid_export.htm
 * A Name
 * B Langname
 * C Text
 * D Beschreibung
 * E Statistik 1
 * F Statistik 2
 * G Kennzeichen
 * H Vorname
 * I Schülernummer
 * J Klasse
 * K Geschlecht (1 = weiblich, 2 = männlich)
 * L (Kurs-)Optimierungskennzeichen
 * M Geburtsdatum JJJJMMTT
 * N E-Mail Adresse (ab Version 2012)
 */

/**
 * GPU015.TXT
 * https://platform.untis.at/HTML/WebHelp/de/untis/hid_export.htm
 * A Student Kurzname
 * B Unterrichtsnummer
 * C Fach
 * D Unterrichtsalias
 * E Klasse
 * F Statistikkennzeichen
 * G Studentennummer (nur Export)
 * H reserviert
 * I reserviert
 * Mit Modul Kursplanung
 * J Unterrichtsnummern der Alternativkurse (mit Tilde ~ getrennt)
 * K Fächer der Alternativkurse (mit ~ getrennt)
 * L reserviert
 * M Prioritäten der Alternativkurse (mit ~ getrennt)
 */

use DateTime;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Transfer\Education\Education;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImport;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImportStudent;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImportStudentCourse;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Service
{
    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null $File10
     * @param UploadedFile|null $File15
     * @param null $Data
     *
     * @return IFormInterface|Danger|string|void|null
     */
    public function createStudentCourseFromFile(?IFormInterface $Form, UploadedFile $File10 = null, UploadedFile $File15 = null, $Data = null)
    {
        /**
         * Skip to Frontend
         */
        if ($Data === null || $File10 === null || $File15 === null) {
            return $Form;
        }

        // File10
        if ($File10->getError()) {
            $Form->setError('File10', 'Fehler');
            $Form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('File10 nicht gefunden')))));

            return $Form;
        }
        if (!(strtolower($File10->getClientOriginalExtension()) == 'txt'
            || strtolower($File10->getClientOriginalExtension()) == 'csv')
        ) {
            $Form->setError('File10', 'Bitte wählen Sie ein txt- oder csv-Datei aus!');

            return $Form;
        }

        // File15
        if ($File15->getError()) {
            $Form->setError('File15', 'Fehler');
            $Form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('File15 nicht gefunden')))));

            return $Form;
        }
        if (!(strtolower($File15->getClientOriginalExtension()) == 'txt'
            || strtolower($File15->getClientOriginalExtension()) == 'csv')
        ) {
            $Form->setError('File15', 'Bitte wählen Sie ein txt- oder csv-Datei aus!');

            return $Form;
        }

        // Schuljahr
        if (!($tblYear = Term::useService()->getYearById($Data['YearId']))) {
            $Form->setError('Data[YearId]', 'Bitte wählen Sie ein Schuljahr aus');

            return $Form;
        }

        // Benutzerkonto
        if (!($tblAccount = Account::useService()->getAccountBySession())) {
            return new Danger('Kein angemeldetes Benutzerkonto gefunden!', new Exclamation());
        }

        $File10Name = $File10->getClientOriginalName();
        $File15Name = $File15->getClientOriginalName();


        $ExternSoftwareName = TblImport::EXTERN_SOFTWARE_NAME_UNTIS;
        $TypeIdentifier = TblImport::TYPE_IDENTIFIER_STUDENT_COURSE;

        if (($tblImportOld = Education::useService()->getImportByAccountAndExternSoftwareNameAndTypeIdentifier(
            $tblAccount, $ExternSoftwareName, $TypeIdentifier
        ))) {
            // alten Import Löschen
            Education::useService()->destroyImport($tblImportOld);
        }

        $tblImport = Education::useService()->createImport(
            new TblImport($tblYear, $tblAccount, $ExternSoftwareName, $TypeIdentifier, $File10Name . ' - ' . $File15Name)
        );

        if (($tblImportStudentList = $this->createImportStudentsFromFile($tblImport, $File10))) {
            if ($this->createImportStudentCoursesFromFile($File15, $tblImportStudentList)) {
                return new Success('Die Schülerkurse wurden erfolgreich eingelesen', new Check())
                    . new Redirect('/Transfer/Untis/Import/StudentCourse/Show', Redirect::TIMEOUT_SUCCESS, array('ImportId' => $tblImport->getId()));
            } else {
                return new Warning('Es wurden keine Schülerkurse gefunden');
            }
        } else {
            return new Warning('Es wurden keine Schüler gefunden');
        }
    }

    /**
     * @param TblImport $tblImport
     * @param UploadedFile $File10
     *
     * @return array|false
     */
    private function createImportStudentsFromFile(TblImport $tblImport, UploadedFile $File10)
    {
        $tblImportStudentList = array();

        /**
         * Prepare
         */
        $File10 = $File10->move($File10->getPath(), $File10->getFilename().'.'.$File10->getClientOriginalExtension());
        // Zeichenkodierung umwandeln
        $File10->convertCharSet();

        /**
         * Read
         */
        $Document = Document::getDocument($File10->getPathname());

        $Y = $Document->getSheetRowCount();

        $Location['Index'] = 0;
        $Location['Name'] = 1;
        $Location['Vorname'] = 7;
        $Location['Klasse'] = 9;
        $Location['Geschlecht'] = 10;
        $Location['Geburtsdatum'] = 12;

        /**
         * Import File10 - Schüler
         */
        if (!in_array(null, $Location, true)) {
            for ($RunY = 0; $RunY < $Y; $RunY++) {
                $DivisionName = trim($Document->getValue($Document->getCell($Location['Klasse'], $RunY)));
                // 10. Klasse ignorieren (sind bei HOGA mit drin)
                if (strpos($DivisionName, '10 ') === 0) {
                    continue;
                }

                $Index = trim($Document->getValue($Document->getCell($Location['Index'], $RunY)));
                $FirstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                $LastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
                $Birthday = null;
                if (($BirthdayString = trim($Document->getValue($Document->getCell($Location['Geburtsdatum'], $RunY))))
                    && strlen($BirthdayString) == 8
                ) {
                    // JJJJMMDD
                    $Birthday = new DateTime();
                    $Birthday->setDate(substr($BirthdayString, 0, 4), substr($BirthdayString, 4, 2), substr($BirthdayString, 6, 2));
                }
                $GenderAcronym = '';
                if ($gender = trim($Document->getValue($Document->getCell($Location['Geschlecht'], $RunY)))) {
                    if ($gender == 1) {
                        $GenderAcronym = 'w';
                    } elseif ($gender == 2) {
                        $GenderAcronym = 'm';
                    }
                }

                if (($tblImportStudent = Education::useService()->createImportStudent(
                    new TblImportStudent($tblImport, $FirstName, $LastName, $Birthday ?: null, $GenderAcronym, $DivisionName)
                ))) {
                    $tblImportStudentList[$Index] = $tblImportStudent;
                }
            }
        }

        return empty($tblImportStudentList) ? false : $tblImportStudentList;
    }

    /**
     * @param UploadedFile $File15
     * @param TblImportStudent[] $tblImportStudentList
     *
     * @return bool
     */
    private function createImportStudentCoursesFromFile(UploadedFile $File15, array $tblImportStudentList): bool
    {
        $createImportStudentCourseList = array();
        /**
         * Prepare
         */
        $File15 = $File15->move($File15->getPath(), $File15->getFilename().'.'.$File15->getClientOriginalExtension());
        // Zeichenkodierung umwandeln
        $File15->convertCharSet();

        /**
         * Read
         */
        $Document = Document::getDocument($File15->getPathname());

        $Y = $Document->getSheetRowCount();

        $Location['Index'] = 0;
        $Location['Fach'] = 2;

        /**
         * Import File15 Schüler-Kurse
         */
        if (!in_array(null, $Location, true)) {
            for ($RunY = 0; $RunY < $Y; $RunY++) {
                $Index = trim($Document->getValue($Document->getCell($Location['Index'], $RunY)));
                if (($tblImportStudent = $tblImportStudentList[$Index] ?? null)
                    && ($CourseName = trim($Document->getValue($Document->getCell($Location['Fach'], $RunY))))
                ) {
                    // Ch-L-1
                    if (preg_match('!^([\w\/]{1,})-([GLgl]-[\d])!', $CourseName, $Match)){
                        $SubjectAcronym =  $Match[1];

                        for ($period = 1; $period < 3; $period++) {
                            $createImportStudentCourseList[] = new TblImportStudentCourse(
                                $tblImportStudent,
                                $SubjectAcronym,
                                $period,
                                $CourseName
                            );
                        }
                    }
                }

            }
        }

        if (!empty($createImportStudentCourseList)) {
            Education::useService()->createEntityListBulk($createImportStudentCourseList);

            return true;
        }

        return false;
    }
}