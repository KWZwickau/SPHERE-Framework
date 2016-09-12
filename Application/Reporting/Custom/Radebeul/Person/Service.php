<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.09.2016
 * Time: 16:05
 */

namespace SPHERE\Application\Reporting\Custom\Radebeul\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Component\Document\Exception\DocumentTypeException;
use MOC\V\Core\FileSystem\Component\Exception\Repository\TypeFileException;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Extension\Extension;

/**
 * Class Service
 *
 * @package SPHERE\Application\Reporting\Custom\Radebeul\Person
 */
class Service extends Extension
{

    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public function createParentTeacherConferenceList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        $TableContent = array();
        if (!empty($tblPersonList)) {
            $count = 1;
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$count) {

                $Item['Number'] = $count++;
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['Attendance'] = '';

                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param array $PersonList
     *
     * @return false|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createParentTeacherConferenceListExcel($PersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $row = 0;
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("3", $row++), "EVANGELISCHE");
            $export->setValue($export->getCell("3", $row++), "GRUNDSCHULE");
            $export->setValue($export->getCell("3", $row++), "RADEBEUL");

            $export->setValue($export->getCell("0", $row++), "Anwesenheitsliste Elternabend");
            $export->setValue($export->getCell("0", $row++), "Datum:");
            $export->setValue($export->getCell("0", $row++), "Thema:");
            $row++;
            $export->setValue($export->getCell("0", $row++), "Klasse:");
            $row++;
            $headerRow = $row;
            $export->setValue($export->getCell("0", $row), "lfdNr.");
            $export->setValue($export->getCell("1", $row), "Name");
            $export->setValue($export->getCell("2", $row), "Vorname");
            $export->setValue($export->getCell("3", $row), "Unterschrift");
            // Gittertrennlinie
            $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->setBorderBottom();
            // Zentriert
            $export->setStyle($export->getCell(0, $row), $export->getCell(0, $row))->setAlignmentCenter();
            $row++;
            foreach ($PersonList as $PersonData) {

                $export->setValue($export->getCell("0", $row), $PersonData['Number']);
                $export->setValue($export->getCell("1", $row), $PersonData['LastName']);
                $export->setValue($export->getCell("2", $row), $PersonData['FirstName']);
                $export->setValue($export->getCell("3", $row), $PersonData['Attendance']);

                // Gittertrennlinie
                $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->setBorderBottom();
                // Zentriert
                $export->setStyle($export->getCell(0, $row), $export->getCell(0, $row))->setAlignmentCenter();
                $row++;
            }

            // Gitterlinien
            $export->setStyle($export->getCell(0, $headerRow), $export->getCell(3, 1))->setBorderBottom();
            $export->setStyle($export->getCell(0, $headerRow), $export->getCell(3, $row - 1))->setBorderVertical();
            $export->setStyle($export->getCell(0, $headerRow), $export->getCell(3, $row - 1))->setBorderOutline();

            // Spaltenbreite
            $export->setStyle($export->getCell(0, 0), $export->getCell(0, $row))->setColumnWidth(8);
            $export->setStyle($export->getCell(1, 0), $export->getCell(1, $row))->setColumnWidth(26);
            $export->setStyle($export->getCell(2, 0), $export->getCell(2, $row))->setColumnWidth(26);
            $export->setStyle($export->getCell(3, 0), $export->getCell(3, $row))->setColumnWidth(30);

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }
}