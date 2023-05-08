<?php

namespace SPHERE\Application\Transfer\Indiware\Import\StudentCourse;

use DateTime;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
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

        if (!($tblSchoolType = Type::useService()->getTypeById($Data['SchoolTypeId']))) {
            $Form->setError('Data[SchoolTypeId]', 'Bitte wählen Sie eine Schulart aus');

            return $Form;
        }


        // todo periode error


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
}