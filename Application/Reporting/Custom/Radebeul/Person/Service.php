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
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Group\Group;
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
     * @param TblDivision $tblDivision
     * @param $PersonList
     *
     * @return bool|FilePointer
     * @throws DocumentTypeException
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     */
    public function createParentTeacherConferenceListExcel(TblDivision $tblDivision, $PersonList)
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
            $export->setValue($export->getCell("0", $row++), "Klasse: " . $tblDivision->getDisplayName());
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

    /**
     * @param array $countArray
     *
     * @return array
     */
    public function createDenominationList(&$countArray)
    {

        $TableContent = array();
        $countArray = array(
            'All' => 0,
            'RK' => 0,
            'EV' => 0,
            'KEINE' => 0
        );
        $tblGroup = Group::useService()->getGroupByMetaTable('STUDENT');
        if ($tblGroup) {
            $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
            if ($tblPersonList) {
                $count = 1;
                foreach ($tblPersonList as $tblPerson) {
                    $countArray['All'] = $count;
                    $Item['Number'] = $count++;
                    $Item['LastName'] = $tblPerson->getLastName();
                    $Item['FirstName'] = $tblPerson->getFirstSecondName();

                    if (($tblCommon = $tblPerson->getCommon())
                        && $tblCommon->getTblCommonInformation()
                    ) {
                        $denomination = trim($tblCommon->getTblCommonInformation()->getDenomination());
                        $Item['Denomination'] = $denomination;
                        if (isset($countArray[strtoupper($denomination)])) {
                            $countArray[strtoupper($denomination)]++;
                        } else {
                            $countArray['KEINE']++;
                        }
                    } else {
                        $Item['Denomination'] = '';
                        $countArray['KEINE']++;
                    }

                    array_push($TableContent, $Item);
                }
            }
        }

        return $TableContent;
    }

    /**
     * @param $PersonList
     * @param $countArray
     *
     * @return bool|FilePointer
     * @throws DocumentTypeException
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     */
    public function createDenominationListExcel($PersonList, $countArray)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $row = 0;
            $export = Document::getDocument($fileLocation->getFileLocation());

            // Spaltenbreite
            $export->setStyle($export->getCell(0, 0), $export->getCell(0, $row))->setColumnWidth(8);
            $export->setStyle($export->getCell(1, 0), $export->getCell(1, $row))->setColumnWidth(26);
            $export->setStyle($export->getCell(2, 0), $export->getCell(2, $row))->setColumnWidth(26);
            $export->setStyle($export->getCell(3, 0), $export->getCell(3, $row))->setColumnWidth(30);

            $export->setValue($export->getCell(0, $row), "Religionszugehörigkeit");
            $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->setFontBold();
            $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->setFontSize(14);
            $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->mergeCells();
            $row++;

            $export->setValue($export->getCell(0, $row),
                "Evangelische Grundschule Radebeul Staatlich genehmigte Ersatzschule                       "
                . date('d.m.Y'));
            $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->mergeCells();

            $export->setStyle($export->getCell(0, 0), $export->getCell(3, $row))->setBorderOutline(2);

            foreach ($PersonList as $PersonData) {
                $row++;

                $export->setValue($export->getCell(0, $row), $PersonData['Number']);
                $export->setValue($export->getCell(1, $row), $PersonData['LastName']);
                $export->setValue($export->getCell(2, $row), $PersonData['FirstName']);
                $export->setValue($export->getCell(3, $row), $PersonData['Denomination']);

                // Gittertrennlinie
                $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->setBorderBottom();
            }

            $row++;
            $export->setValue($export->getCell("0", $row),
                "   Schüler:    " . $countArray['All']
                . "             Evangelisch:    " . $countArray['EV']
                . "             Katholisch:    " . $countArray['RK']
                . "             ohne Angabe:    " . $countArray['KEINE']
            );
            $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->setBorderAll();
            $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->setFontBold();
            $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->mergeCells();

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }
}