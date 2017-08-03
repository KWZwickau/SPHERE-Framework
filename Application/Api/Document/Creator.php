<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 09.09.2016
 * Time: 10:35
 */

namespace SPHERE\Application\Api\Document;

use MOC\V\Component\Document\Component\Parameter\Repository\PaperOrientationParameter;
use MOC\V\Component\Document\Document as PdfDocument;
use MOC\V\Component\Template\Component\IBridgeInterface;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Api\Document\Standard\Repository\GradebookOverview;
use SPHERE\Application\Api\Document\Standard\Repository\StudentCard\AbstractStudentCard;
use SPHERE\Application\Api\Document\Standard\Repository\StudentCard\GrammarSchool;
use SPHERE\Application\Api\Document\Standard\Repository\StudentCard\MultiStudentCard;
use SPHERE\Application\Api\Document\Standard\Repository\StudentCard\PrimarySchool;
use SPHERE\Application\Api\Document\Standard\Repository\StudentCard\SecondarySchool;
use SPHERE\Application\Document\Generator\Generator;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
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
    const PAPERORIENTATION_PORTRAIT = 'PORTRAIT';
    const PAPERORIENTATION_LANDSCAPE = 'LANDSCAPE';
    /**
     * @param null $PersonId
     * @param $DocumentClass
     * @param string $paperOrientation
     *
     * @return Stage|string
     */
    public static function createPdf($PersonId, $DocumentClass, $paperOrientation = Creator::PAPERORIENTATION_PORTRAIT)
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

            $File = self::buildDummyFile($Document, $Data, array(), $paperOrientation);

            $FileName = $Document->getName() . ' ' . $tblPerson->getLastFirstName() . ' ' . date("Y-m-d") . ".pdf";

            return self::buildDownloadFile($File, $FileName);
        } elseif (class_exists($DocumentClass)) {
            // create PDF without Data and PersonId
            /** @var AbstractDocument $Document */
            $Document = new $DocumentClass();
            $File = self::buildDummyFile($Document, array(), array(), $paperOrientation);
            $FileName = $Document->getName().' '.date("Y-m-d").".pdf";

            return self::buildDownloadFile($File, $FileName);
        }
        return new Stage('Dokument', 'Konnte nicht erstellt werden.');
    }

    /**
     * @param null $PersonId
     * @param null $DivisionId
     * @param $DocumentClass
     * @param string $paperOrientation
     *
     * @return Stage|string
     */
    public static function createGradebookOverviewPdf($PersonId, $DivisionId, $paperOrientation = Creator::PAPERORIENTATION_LANDSCAPE) {
        if (($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblDivision = Division::useService()->getDivisionById($DivisionId))
        ) {
            $Document = new GradebookOverview\GradebookOverview($tblPerson, $tblDivision);

            $File = self::buildDummyFile($Document, array(), array(), $paperOrientation);

            $FileName = $Document->getName() . ' ' . $tblPerson->getLastFirstName() . ' ' . date("Y-m-d") . ".pdf";

            return self::buildDownloadFile($File, $FileName);
        }

        return new Stage('Dokument', 'Konnte nicht erstellt werden.');
    }

    /**
     * @param AbstractDocument|AbstractStudentCard $DocumentClass
     * @param array $Data
     * @param array $pageList
     * @param string $paperOrientation
     *
     * @return FilePointer
     */
    private static function buildDummyFile($DocumentClass, $Data = array(), $pageList = array(), $paperOrientation = Creator::PAPERORIENTATION_PORTRAIT)
    {

        ini_set('memory_limit', '1G');

        // Create Tmp
        $File = Storage::createFilePointer('pdf');

        // build before const is set (picture)
        /** @var IBridgeInterface $Content */
        $Content = $DocumentClass->createDocument($Data, $pageList);
        /** @var DomPdf $Document */
        $Document = PdfDocument::getPdfDocument($File->getFileLocation());
        $Document->setPaperOrientationParameter(new PaperOrientationParameter($paperOrientation));
        $Document->setContent($Content);
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

        return FileSystem::getStream(
            $File->getRealPath(),
            $FileName ? $FileName : "Dokument " . date("Y-m-d") . ".pdf"
        )->__toString();
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblType[] $tblSchoolTypeList
     * @param string $paperOrientation
     * @return Stage|string
     */
    public static function createMultiPdf(TblPerson $tblPerson, $tblSchoolTypeList, $paperOrientation = 'PORTRAIT')
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
            $File = self::buildDummyFile($Document, $Data, $pageList, $paperOrientation);
            $FileName = $Document->getName() . ' ' . $tblPerson->getLastFirstName() . ' ' . date("Y-m-d") . ".pdf";

            return self::buildDownloadFile($File, $FileName);
        }

        return "Keine Schülerkartei vorhanden!";
    }
}