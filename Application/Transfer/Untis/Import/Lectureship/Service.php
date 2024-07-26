<?php
/**
 * Export Unterricht (GPU002.TXT) Reihenfolge der Felder in der DIF-Datei GPU002.TXT
 * GPU002.TXT DIF-Datei Unterricht:
 * Nummer        Feld                Art
 * 1        Unt-Nummer        Num
 * 2        Wochenstunden        Num
 * 3        Wochenstd. Kla.        Num *1)
 * 4        Wochenstd. Le.        Num *1)
 * 5        Klasse
 * 6        Lehrer
 * 7        Fach
 * 8        Fachraum
 * 9        Statistik 1 Unt.
 * 10       Studentenzahl        Num
 * 11       Wochenwert        Num *2)
 * 12       Gruppe
 * 13       Zeilentext 1
 * 14       Zeilenwert (in Tausendstel) *3)
 * 15       Datum von        Datum
 * 16       Datum bis        Datum
 * 17       Jahreswert Num *4)
 * 18       Text (früher U-ID)
 * 19       Teilungs-Nummer
 * 20       Stammraum
 * 21       Beschreibung
 * 22       Farbe Vg.        Farbe
 * 23       Farbe Hg.        Farbe
 * 24       Kennzeichen
 * 25       Fachfolge Klassen
 * 26       Fachfolge Lehrer
 * 27       Klassen-Kollisions-Kennz.
 * 28       Doppelstd. min.        Num
 * 29       Doppelstd. max.        Num
 * 30       Blockgröße        Num
 * 31       Std. im Raum        Num
 * 32       Priorität
 * 33       Statistik 1 Lehrer
 * 34       Studenten männl.        Num
 * 35       Studenten weibl.        Num
 * 36       Wert bzw. Faktor
 * 37       2. Block
 * 38       3. Block
 * 39       Zeilentext-2
 * 40       Eigenwert (ohne Faktoren - ausser Faktor Unterricht)
 * 41       Eigenwert (in 1/100000)
 * 42       Schülergruppe
 * 43       Wochenstunden in Jahres-Perioden-Planung (z.B. '2,4,0,2,3')
 * 44       Jahresstunden
 * 45       Zeilen-Unterrichtsgruppe
 *
 *
 * *1) Wochenstunden Klassen/Lehrer: Erscheint eine Klasse in mehreren Zeilen des selben Unterrichtes (mehrere Lehrer in dieser Klasse), so ist nur in der ersten Zeile diese Anzahl ungleich null.
 * *2) Wochenwert: Aus den Faktoren gerechneter Wochenwert für den Lehrer dieser Zeile.
 * *3) Zeilenwert: Nicht umgeschlüsselte Eintragung im Feld Zeilenwert.
 * *4) Jahreswert: Ohne Stundenplan gemittelte Jahreswertstunden (Wochenwert mal Jahreswochen).
 */
namespace SPHERE\Application\Transfer\Untis\Import\Lectureship;

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
            || strtolower($File->getClientOriginalExtension()) == 'csv'
        )) {
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
        // Zeichenkodierung umwandeln
        $File->convertCharSet();

        /**
         * Read
         */
        $Document = Document::getDocument($File->getPathname());

//        $X = $Document->getSheetColumnCount();
        $Y = $Document->getSheetRowCount();

        $ExternSoftwareName = TblImport::EXTERN_SOFTWARE_NAME_UNTIS;
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

        $Location['Klasse'] = 4;
        $Location['Lehrer'] = 5;
        $Location['Fach'] = 6;
        $Location['Gruppe'] = 41;

        /**
         * Import
         */
        if (!in_array(null, $Location, true)) {
            $createImportLectureshipList = array();
            for ($RunY = 0; $RunY < $Y; $RunY++) {
                $SubjectAcronym = trim($Document->getValue($Document->getCell($Location['Fach'], $RunY)));
                $SubjectGroup = trim($Document->getValue($Document->getCell($Location['Gruppe'], $RunY)));
                $TeacherAcronym = trim($Document->getValue($Document->getCell($Location['Lehrer'], $RunY)));
                $DivisionName = trim($Document->getValue($Document->getCell($Location['Klasse'], $RunY)));
                if ($SubjectAcronym && $TeacherAcronym && $DivisionName) {
                    // bei Untis steht bei der SekII der Kursname im Fach
                    if (preg_match('!^([\w\/]{1,})-([GLgl]-[\d])!', $SubjectAcronym, $Match)) {
                        $SubjectGroup = $SubjectAcronym;
                        $SubjectAcronym = $Match[1];
                    }

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

            if (!empty($createImportLectureshipList)) {
                Education::useService()->createEntityListBulk($createImportLectureshipList);

                return new Success('Die Lehraufträge wurden erfolgreich eingelesen', new Check())
                    . new Redirect('/Transfer/Untis/Import/Lectureship/Show', Redirect::TIMEOUT_SUCCESS, array('ImportId' => $tblImport->getId()));
            } else {
                return new Warning('Es wurden keine Lehraufträge gefunden');
            }
        } else {
            return new Warning(json_encode($Location))
                . new Danger("File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
        }
    }
}