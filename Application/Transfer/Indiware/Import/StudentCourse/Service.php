<?php

namespace SPHERE\Application\Transfer\Indiware\Import\StudentCourse;

use DateTime;
use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Education\Certificate\Generate\Generate;
use SPHERE\Application\Education\Certificate\Prepare\Abitur\BlockI;
use SPHERE\Application\Education\Certificate\Prepare\Abitur\BlockIView;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Lesson\Subject\Subject;
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
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
use SPHERE\Common\Window\Redirect;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Service
{
    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null $File
     * @param $Data
     *
     * @return IFormInterface|Danger|string|void|null
     */
    public function createStudentCourseFromFile(?IFormInterface $Form, UploadedFile $File = null, $Data = null)
    {
        /**
         * Skip to Frontend
         */
        if (null === $File) {
            return $Form;
        }

        if ($File->getError()) {
            $Form->setError('File', 'Fehler');
            $Form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('File nicht gefunden')))));

            return $Form;
        }

        if (!(strtolower($File->getClientOriginalExtension()) == 'txt'
            || strtolower($File->getClientOriginalExtension()) == 'csv')
        ) {
            $Form->setError('File', 'Bitte wählen Sie ein txt- oder csv-Datei aus!');

            return $Form;
        }

        if (!($tblYear = Term::useService()->getYearById($Data['YearId']))) {
            $Form->setError('Data[YearId]', 'Bitte wählen Sie ein Schuljahr aus');

            return $Form;
        }

        if (!($tblAccount = Account::useService()->getAccountBySession())) {
            return new Danger('Kein angemeldetes Benutzerkonto gefunden!', new Exclamation());
        }

        $FileName = $File->getClientOriginalName();

        /**
         * Prepare
         */
        $File = $File->move($File->getPath(), $File->getFilename().'.'.$File->getClientOriginalExtension());

        /**
         * Read
         */
        $Document = Document::getDocument($File->getPathname());

        $X = $Document->getSheetColumnCount();
        $Y = $Document->getSheetRowCount();

        $Location['Name'] = null;
        $Location['Vorname'] = null;
        $Location['Geburtsdatum'] = null;
        $Location['Geschlecht'] = null;
        $Location['Klasse'] = null;

        // Fach1..17
        for ($i = 1; $i <= 17; $i++) {
            $Location['Fach' . $i] = null;
        }
        // Kurs1..4 mit 1..17
        for ($i = 1; $i <= 4; $i++) {
            for ($j = 1; $j <= 17; $j++) {
                $Location['Kurs' . $i . $j] = null;
            }
        }

        for ($RunX = 0; $RunX < $X; $RunX++) {
            $Value = trim($Document->getValue($Document->getCell($RunX, 0)));
            if (array_key_exists($Value, $Location)) {
                $Location[$Value] = $RunX;
            }
        }

        $ExternSoftwareName = TblImport::EXTERN_SOFTWARE_NAME_INDIWARE;
        $TypeIdentifier = TblImport::TYPE_IDENTIFIER_STUDENT_COURSE;

        if (($tblImportOld = Education::useService()->getImportByAccountAndExternSoftwareNameAndTypeIdentifier(
            $tblAccount, $ExternSoftwareName, $TypeIdentifier
        ))) {
            // alten Import Löschen
            Education::useService()->destroyImport($tblImportOld);
        }

        $tblImport = Education::useService()->createImport(
            new TblImport($tblYear, $tblAccount, $ExternSoftwareName, $TypeIdentifier, $FileName)
        );

        /**
         * Import
         */
        if (!in_array(null, $Location, true)) {
            $createImportStudentCourseList = array();
            for ($RunY = 1; $RunY < $Y; $RunY++) {
                $FirstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                $LastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
                if (($Birthday = trim($Document->getValue($Document->getCell($Location['Geburtsdatum'], $RunY))))) {
                    $Birthday = new DateTime($Birthday);
                }
                $GenderAcronym = trim($Document->getValue($Document->getCell($Location['Geschlecht'], $RunY)));
                $DivisionName = trim($Document->getValue($Document->getCell($Location['Klasse'], $RunY)));
                if (($tblImportStudent = Education::useService()->createImportStudent(
                    new TblImportStudent($tblImport, $FirstName, $LastName, $Birthday ?: null, $GenderAcronym, $DivisionName)
                ))) {
                    for ($j = 1; $j <= 17; $j++) {
                        if (($SubjectAcronym = trim($Document->getValue($Document->getCell($Location['Fach' . $j], $RunY))))) {
                            for ($i = 1; $i <= 4; $i++) {
                                if (($CourseName = trim($Document->getValue($Document->getCell($Location['Kurs' . $i . $j], $RunY))))) {
                                    $createImportStudentCourseList[] = new TblImportStudentCourse(
                                        $tblImportStudent,
                                        $SubjectAcronym,
                                        $i . $j,
                                        $CourseName
                                    );
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($createImportStudentCourseList)) {
                Education::useService()->createEntityListBulk($createImportStudentCourseList);

                return new Success('Die Schülerkurse wurden erfolgreich eingelesen', new Check())
                    . new Redirect('/Transfer/Indiware/Import/StudentCourse/Show', Redirect::TIMEOUT_SUCCESS, array('ImportId' => $tblImport->getId()));
            } else {
                return new Warning('Es wurden keine Schülerkurse gefunden');
            }
        } else {
            return new Warning(json_encode($Location))
                . new Danger("File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
        }
    }

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null $File
     * @param null $Data
     *
     * @return IFormInterface|Danger|string
     */
    public function createSelectedCourseFromFile(IFormInterface $Form = null, UploadedFile $File = null, $Data = null)
    {
        /**
         * Skip to Frontend
         */
        if (null === $File) {
            return $Form;
        }

        if (!($tblGenerateCertificate = Generate::useService()->getGenerateCertificateById($Data['GenerateCertificateId']))) {
            $Form->setError('Data[GenerateCertificateId]', 'Bitte geben Sie einen Zeugnisauftrag an');
            return $Form;
        }


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
                'Vorname' => null,
                'Name' => null,
                'Geburtsdatum' => null
            );

            for ($j = 1; $j < 5; $j++) {
                for ($i = 1; $i < 18; $i++) {
                    if ($j == 1) {
                        $Location['Fach' . $i] = null;
                    }

                    $Location['Einbringung' . $j . $i] = null;
                }
            }

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
                $error = array();
                $success = array();
                $countPersons = 0;
                $countMissingPersons = 0;
                $countDuplicatePersons = 0;

                $prepareStudents = array();
                // alle möglichen Schüler mit entsprechender Zeugnisvorbereitung ermitteln
                if (($tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate))) {
                    foreach ($tblPrepareList as $tblPrepare) {
                        if (($tblPrepareStudentList = Prepare::useService()->getPrepareStudentAllByPrepare($tblPrepare))) {
                            $prepareStudents = array_merge($prepareStudents, $tblPrepareStudentList);
                        }
                    }
                }

                for ($RunY = 1; $RunY < $Y; $RunY++) {
                    $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                    $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));

                    $birthday = trim($Document->getValue($Document->getCell($Location['Geburtsdatum'], $RunY)));
                    if ($birthday) {
                        if (strpos($birthday, '.') === false) {
                            $birthday = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($birthday));
                        }
                    }

                    // person finden
                    $personList = array();
                    $tblPerson = false;
                    $tblPrepareStudent = false;
                    if ($firstName !== '' && $lastName !== '') {
                        foreach ($prepareStudents as $tblPrepareStudentTemp) {
                            if (($tblPersonTemp = $tblPrepareStudentTemp->getServiceTblPerson())
                                && strtolower($tblPersonTemp->getFirstSecondName()) == strtolower($firstName)
                                && strtolower($tblPersonTemp->getLastName()) == strtolower($lastName)
                            ) {
                                if (($birthdayPerson = $tblPersonTemp->getBirthday())
                                    && $birthday
                                ) {
                                    $birthdayPerson = new DateTime($birthdayPerson);
                                    $birthday = new DateTime($birthday);

                                    if ($birthday != $birthdayPerson) {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person ' . $firstName . ' ' . $lastName
                                            . ' hat ein anderes Geburtsdatum.';
                                        continue;
                                    }
                                }

                                $personList[] = $tblPersonTemp;
                                $tblPerson = $tblPersonTemp;
                                $tblPrepareStudent = $tblPrepareStudentTemp;
                            }
                        }

                        if (count($personList) == 1) {
                            $countPersons++;
                        } elseif (count($personList) > 1) {
                            $countDuplicatePersons++;
                            $tblPerson = false;
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person ' . $firstName . ' ' . $lastName . ' wurde nicht mehrmals gefunden.';
                        } else {
                            $countMissingPersons++;
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person ' . $firstName . ' ' . $lastName . ' wurde nicht gefunden.';
                        }
                    }

                    if ($tblPerson && $tblPrepareStudent && ($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())) {
                        // Zensuren kopieren aus Zeugnissen und Stichtagsnotenauftrag
                        $blockI = new BlockI($tblPerson, $tblPrepare, BlockIView::PREVIEW);

                        // Fächer pro Schüler zuordnen
                        $studentSubjectList = array();
                        for ($i = 1; $i < 18; $i++) {
                            $subject = trim($Document->getValue($Document->getCell($Location['Fach' . $i], $RunY)));
                            if ($subject != '') {
                                if (($tblSubject = Subject::useService()->getSubjectByAcronym($subject))) {
                                    // prüfen ob der Schüler das Fach besucht bzw. noten hat
                                    $studentSubjectList[$i] = $tblSubject;
                                } else {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Bei Person ' . $firstName . ' ' . $lastName
                                        . ' wurde das Fach' . $i . ':' . $subject . ' nicht gefunden.';
                                }
                            }
                        }

                        // Kurseinbringung der Zensuren updaten aus csv
                        $countSelectedCourse = 0;
                        for ($j = 1; $j < 5; $j++) {
                            switch ($j) {
                                case 1: $identifier = '11-1'; break;
                                case 2: $identifier = '11-2'; break;
                                case 3: $identifier = '12-1'; break;
                                case 4: $identifier = '12-2'; break;
                                default: $identifier = '11-1';
                            }
                            if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier($identifier))) {
                                for ($i = 1; $i < 18; $i++) {
                                    $selected = trim($Document->getValue($Document->getCell($Location['Einbringung' . $j . $i],
                                        $RunY)));
                                    if ($selected != '') {
                                        if (($isCourseSelected = strtoupper($selected) == 'WAHR')) {
                                            $countSelectedCourse++;
                                        }

                                        if (!isset($studentSubjectList[$i]) && !$isCourseSelected) {
                                            // nicht eingebrachte Kurse stehen auf 'FALSCH', auch wenn es keinen Kurs gibt
                                        } elseif (isset($studentSubjectList[$i])) {
                                            $tblSubject = $studentSubjectList[$i];

                                            // Zensur finden und Kurseinbringung setzen
                                            if (($tblPrepareAdditionalGrade = Prepare::useService()->getPrepareAdditionalGradeBy(
                                                $tblPrepare, $tblPerson, $tblSubject, $tblPrepareAdditionalGradeType, true
                                            ))) {
                                                Prepare::useService()->updatePrepareAdditionalGrade(
                                                    $tblPrepareAdditionalGrade,
                                                    $tblPrepareAdditionalGrade->getGrade(),
                                                    $isCourseSelected
                                                );
                                                // Spezialfall en2
                                            } elseif ($tblSubject->getAcronym() == 'EN2'
                                                && ($tblSubjectTemp = Subject::useService()->getSubjectByAcronym('EN'))
                                                && ($tblPrepareAdditionalGrade = Prepare::useService()->getPrepareAdditionalGradeBy(
                                                    $tblPrepare, $tblPerson, $tblSubjectTemp, $tblPrepareAdditionalGradeType, true
                                                ))
                                            ) {
                                                Prepare::useService()->updatePrepareAdditionalGrade(
                                                    $tblPrepareAdditionalGrade,
                                                    $tblPrepareAdditionalGrade->getGrade(),
                                                    $isCourseSelected
                                                );
                                            } elseif ($isCourseSelected) {
                                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Bei Person ' . $firstName . ' ' . $lastName
                                                    . ' wurde für die Einbringung' . $j . $i . ':' . $selected
                                                    . ' keine Zensur in der Schulsoftware gefunden';
                                            } else {
                                                //  Bei 'FALSCH' kann auch keine Zensur vorhanden sein
                                            }
                                        } else {
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Bei Person ' . $firstName . ' ' . $lastName
                                                . ' wurde für die Einbringung' . $j . $i . ':' . $selected . ' das Fach nicht gefunden';
                                        }
                                    }
                                }
                            }
                        }

                        $text = $firstName . ' ' . $lastName . ' wurden ' . $countSelectedCourse . ' von 40 Kursen zugeordnet.';
                        $success[] =  $countSelectedCourse == 40
                            ? new SuccessText($text)
                            : new \SPHERE\Common\Frontend\Text\Repository\Warning($text);
                    }
                }

                return
//                    new Success('Es wurden ' . $countPersons . ' Personen erfolgreich gefunden.') .
                    new Panel(
                        'Es wurden ' . $countPersons . ' Personen erfolgreich gefunden.',
                        $success,
                        Panel::PANEL_TYPE_SUCCESS
                    ) .
                    ($countDuplicatePersons > 0 ? new Warning($countDuplicatePersons . ' Doppelte Personen gefunden') : '') .
                    ($countMissingPersons > 0 ? new Warning($countMissingPersons . ' Personen nicht gefunden') : '') .
                    (empty($error)
                        ? ''
                        : new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                            new Panel(
                                'Fehler',
                                $error,
                                Panel::PANEL_TYPE_DANGER
                            )
                        )))))
                    ;
            } else {
                return new Warning(json_encode($Location)) . new Danger(
                        "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
            }
        }

        return new Danger('File nicht gefunden');
    }
}