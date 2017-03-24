<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 09.09.2016
 * Time: 10:35
 */

namespace SPHERE\Application\Api\Document;

use MOC\V\Component\Document\Document as PdfDocument;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Api\Document\Standard\Repository\StudentCard\AbstractStudentCard;
use SPHERE\Application\Api\Document\Standard\Repository\StudentCard\GrammarSchool;
use SPHERE\Application\Api\Document\Standard\Repository\StudentCard\MultiStudentCard;
use SPHERE\Application\Api\Document\Standard\Repository\StudentCard\PrimarySchool;
use SPHERE\Application\Api\Document\Standard\Repository\StudentCard\SecondarySchool;
use SPHERE\Application\Document\Generator\Generator;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use MOC\V\Component\Document\Component\Bridge\Repository\DomPdf;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;

/**
 * Class Creator
 *
 * @package SPHERE\Application\Api\Document\Standard
 */
class Creator extends Extension
{

    /**
     * @param null $PersonId
     * @param $DocumentClass
     *
     * @return Stage|string
     */
    public static function createPdf($PersonId, $DocumentClass)
    {

        if (($tblPerson = Person::useService()->getPersonById($PersonId))
            && class_exists($DocumentClass)
        ) {
            /** @var AbstractDocument $Document */
            $Document = new $DocumentClass();

            $Data['Person']['Id'] = $tblPerson->getId();
            if (strpos($DocumentClass, 'StudentCard') !== false ) {
                $Data = Generator::useService()->setStudentCardContent($Data, $tblPerson, $Document);
            }

            $File = self::buildDummyFile($Document, $Data);

            $FileName = $Document->getName() . ' ' . $tblPerson->getLastFirstName() . ' ' . date("Y-m-d") . ".pdf";

            return self::buildDownloadFile($File, $FileName);
        }

        return new Stage('Dokument', 'Konnte nicht erstellt werden.');
    }

    /**
     * @param AbstractDocument|AbstractStudentCard $DocumentClass
     * @param array $Data
     * @param array $pageList
     *
     * @return FilePointer
     */
    private static function buildDummyFile($DocumentClass, $Data = array(), $pageList = array())
    {

        ini_set('memory_limit', '1G');

        // Create Tmp
        $File = Storage::createFilePointer('pdf');
        /** @var DomPdf $Document */
        $Document = PdfDocument::getPdfDocument($File->getFileLocation());
        $Document->setContent($DocumentClass->createDocument($Data, $pageList));
        $Document->saveFile(new FileParameter($File->getFileLocation()));

        return $File;
    }

    /**
     * @param FilePointer $File
     * @param string $FileName
     *
     * @return string
     */
    private static function buildDownloadFile(FilePointer $File, $FileName = '')
    {

        return FileSystem::getDownload(
            $File->getRealPath(),
            $FileName ? $FileName : "Dokument " . date("Y-m-d") . ".pdf"
        )->__toString();
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblType[] $tblSchoolTypeList
     * @return Stage|string
     */
    public static function createMultiPdf(TblPerson $tblPerson, $tblSchoolTypeList)
    {

        $Data['Person']['Id'] = $tblPerson->getId();
        $pageList = array();
        foreach ($tblSchoolTypeList as $tblType)
        {
            if ($tblType->getName() == 'Grundschule') {
                $DocumentItem = new PrimarySchool();
            } else if ($tblType->getName() == 'Gymnasium') {
                $DocumentItem = new GrammarSchool();
            } else if ($tblType->getName() == 'Mittelschule / Oberschule') {
                $DocumentItem = new SecondarySchool();
            } else {
                $DocumentItem = false;
            }

            if ($DocumentItem) {
                $Data = Generator::useService()->setStudentCardContent($Data, $tblPerson, $DocumentItem, $tblType);
                $DocumentItem->setTblPerson($tblPerson);
                $pageList[] = $DocumentItem->buildPage();
                $pageList[] = $DocumentItem->buildRemarkPage($tblType);
            }
        }

        if (!empty($pageList))
        {
            $Document = new MultiStudentCard();
            $File = self::buildDummyFile($Document, $Data, $pageList);
            $FileName = $Document->getName() . ' ' . $tblPerson->getLastFirstName() . ' ' . date("Y-m-d") . ".pdf";

            return self::buildDownloadFile($File, $FileName);
        }

        return "Keine Schülerkartei vorhanden!";
    }
}