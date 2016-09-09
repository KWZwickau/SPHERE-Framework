<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 09.09.2016
 * Time: 10:35
 */

namespace SPHERE\Application\Api\Document\Standard;

use MOC\V\Component\Document\Document;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\People\Person\Person;
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

    public function createPdf($PersonId = null, $DocumentName = null)
    {

        if (($tblPerson = Person::useService()->getPersonById($PersonId)) && $DocumentName) {
            $DocumentClass = '\SPHERE\Application\Api\Document\Standard\Repository\\' . $DocumentName;
            if (class_exists($DocumentClass)) {
                /** @var AbstractDocument $Document */
                $Document = new $DocumentClass();

                $Data['Person']['Id'] = $tblPerson->getId();
                $File = $this->buildDummyFile($Document, $Data);

                $FileName = $Document->getName() . ' '  . $tblPerson->getLastFirstName() . ' ' . date("Y-m-d") . ".pdf";

                return $this->buildDownloadFile($File, $FileName);
            }


        }

        return new Stage('Dokument', 'Konnte nicht erstellt werden.');
    }

    /**
     * @param AbstractDocument $DocumentClass
     * @param array $Data
     *
     * @return FilePointer
     */
    private function buildDummyFile(AbstractDocument $DocumentClass, $Data = array())
    {

        // Create Tmp
        $File = Storage::createFilePointer('pdf');
        /** @var DomPdf $Document */
        $Document = Document::getPdfDocument($File->getFileLocation());
        $Document->setContent($DocumentClass->createDocument($Data));
        $Document->saveFile(new FileParameter($File->getFileLocation()));

        return $File;
    }

    /**
     * @param FilePointer $File
     * @param string      $FileName
     *
     * @return string
     */
    private function buildDownloadFile(FilePointer $File, $FileName = '')
    {

        return FileSystem::getDownload(
            $File->getRealPath(),
            $FileName ? $FileName : "Dokument ".date("Y-m-d").".pdf"
        )->__toString();
    }
}