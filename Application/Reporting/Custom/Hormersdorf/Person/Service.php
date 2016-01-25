<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 25.01.2016
 * Time: 15:47
 */

namespace SPHERE\Application\Reporting\Custom\Hormersdorf\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Document\Explorer\Storage\Storage;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;

class Service
{
    /**
     * @return bool|\SPHERE\Application\People\Person\Service\Entity\TblPerson[]
     */
    public function createStaffList()
    {

        $staffList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByName('Mitarbeiter'));

        if (!empty( $staffList )) {
            foreach ($staffList as $tblPerson) {

                $tblPerson->Name = $tblPerson->getLastFirstName();
                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $tblPerson->Birthday = $common->getTblCommonBirthDates()->getBirthday();
                } else {
                    $tblPerson->Birthday = '';
                }
            }
        }

        return $staffList;
    }

    /**
     * @param $staffList
     *
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createStaffListExcel($staffList)
    {

        if (!empty( $staffList )) {

            $fileLocation = Storage::useWriter()->getTemporary('xls');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Name");
            $export->setValue($export->getCell("1", "0"), "Geburtstag");

            $Row = 1;
            foreach ($staffList as $tblPerson) {

                $export->setValue($export->getCell("0", $Row), $tblPerson->Name);
                $export->setValue($export->getCell("1", $Row), $tblPerson->Birthday);

                $Row++;
            }

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }
}