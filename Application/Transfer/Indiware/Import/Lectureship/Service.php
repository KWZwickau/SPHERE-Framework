<?php

namespace SPHERE\Application\Transfer\Indiware\Import\Lectureship;

use MOC\V\Component\Document\Document;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Transfer\Education\Education;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImport;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImportLectureship;
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
    public function createLectureshipFromFile(?IFormInterface $Form, UploadedFile $File = null, $Data = null)
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

        $Location['Fach'] = null;
        $Location['Gruppe'] = null;
        // Lehrer, Lehrer2, Lehrer3
        for ($i = 1; $i <= 3; $i++) {
            $Location['Lehrer' . ($i == 1 ? '' : $i)] = null;
        }
        // Klasse1..20
        for ($j = 1; $j <= 20; $j++) {
            $Location['Klasse' . $j] = null;
        }

        for ($RunX = 0; $RunX < $X; $RunX++) {
            $Value = trim($Document->getValue($Document->getCell($RunX, 0)));
            if (array_key_exists($Value, $Location)) {
                $Location[$Value] = $RunX;
            }
        }

        $ExternSoftwareName = TblImport::EXTERN_SOFTWARE_NAME_INDIWARE;
        $TypeIdentifier = TblImport::TYPE_IDENTIFIER_LECTURESHIP;
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
            $createImportLectureshipList = array();
            for ($RunY = 1; $RunY < $Y; $RunY++) {
                $SubjectAcronym = trim($Document->getValue($Document->getCell($Location['Fach'], $RunY)));
                $SubjectGroup = trim($Document->getValue($Document->getCell($Location['Gruppe'], $RunY)));
                if ($SubjectAcronym) {
                    for ($i = 1; $i <= 3; $i++) {
                        if (($TeacherAcronym = trim($Document->getValue($Document->getCell($Location['Lehrer' . ($i == 1 ? '' : $i)], $RunY))))) {
                            for ($j = 1; $j <= 20; $j++) {
                                if (($DivisionName = trim($Document->getValue($Document->getCell($Location['Klasse' . $j], $RunY))))) {
                                    // doppelte Einträge ignorieren
                                    $createImportLectureshipList[$SubjectAcronym . '_' . $TeacherAcronym . '_' . $DivisionName . '_' . $SubjectGroup]
                                        = new TblImportLectureship(
                                            $tblImport,
                                            $TeacherAcronym,
                                            $DivisionName,
                                            $SubjectAcronym,
                                            $SubjectGroup
                                        );
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($createImportLectureshipList)) {
                Education::useService()->createEntityListBulk($createImportLectureshipList);

                return new Success('Die Lehraufträge wurden erfolgreich eingelesen', new Check())
                    . new Redirect('/Transfer/Indiware/Import/Lectureship/Show', Redirect::TIMEOUT_SUCCESS, array('ImportId' => $tblImport->getId()));
            } else {
                return new Warning('Es wurden keine Lehraufträge gefunden');
            }
        } else {
            return new Warning(json_encode($Location))
                . new Danger("File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
        }
    }
}