<?php
namespace SPHERE\Application\Api\Education\Graduation\Evaluation;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Certificate\Reporting\Reporting;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Reporting\Standard\Person\Person as ReportingPerson;
use SPHERE\Application\Reporting\Standard\Person\Service;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;

/**
 * Class Evaluation
 *
 * @package SPHERE\Application\Api\Education\Graduation\Evaluation
 */
class Evaluation implements IModuleInterface
{
    public static function registerModule()
    {
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/TaskGrades/Download',
            __NAMESPACE__ . 'TaskGrades::downloadTaskGrades'
        ));
    }

    public static function useService()
    {
        // TODO: implement useService
    }

    public static function useFrontend()
    {

    }

    public static function downloadTaskGrades($DivisionId): string
    {

        return FileSystem::getDownload($fileLocation->getRealPath(),
            "Noten&uuml;bersicht" . date("Y-m-d H:i:s") . ".xlsx")->__toString();

    }
}
/*
if(($tblDivision = Division::useService()->getDivisionById($DivisionId))
&& ($content = Reporting::useService()->getCourseGradesContent($tblDivision))
&& ($fileLocation = Reporting::useService()->createCourseGradesContentExcel($content))
){
return FileSystem::getDownload($fileLocation->getRealPath(), 'Kursnoten '
. $tblDivision->getTypeName() . ' Klasse ' . $tblDivision->getDisplayName() . ' ' . date("Y-m-d H:i:s").".xlsx")->__toString();
}

return 'Keine Daten vorhanden!';
}


public function downloadDivisionTeacherList()
    {
        list($TableContent, $headers) = ReportingPerson::useService()->createDivisionTeacherList();
        if ($TableContent) {
            $fileLocation = ReportingPerson::useService()->createDivisionTeacherExcelList($TableContent, $headers);

            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Klassenlehrer".date("Y-m-d H:i:s").".xlsx")->__toString();
        }

        return false;
    }
    /*
}
public function frontendClassTeacher(?string $YearId = null): Stage
{

    $Stage = new Stage('Auswertung', 'Klassenlehrer');
    $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
    $Stage->addButton(
        new Primary('Herunterladen',
            '/Api/Reporting/Standard/Person/DivisionTeacherList/Download', new Download())
    );

    list($TableContent, $headers) = Person::useService()->createDivisionTeacherList();

    $Stage->setContent(
        new Layout(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new TableData($TableContent, null, $headers,


                            array(
                                'columnDefs' => array(
                                    array('type' => 'natural', 'targets' => array(0)),
                                    array("orderable" => false, "targets"   => -1),
                                ),
                                'order' => array(
                                    array(0, 'asc')
                                ),
                                'responsive' => false

                            ))
                        , 12)
                ), new Title(new Listing() . ' Übersicht')
            )
        ));
    return $Stage;
}

 public function createDivisionTeacherExcelList(array $content, array $headers)
    {
        if (!empty($content)) {
            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */                                                                /*
$export = Document::getDocument($fileLocation->getFileLocation());

$row = 0;
$column = 0;
foreach ($headers as $header) {
    $export->setValue($export->getCell($column++, $row), str_replace('&nbsp;', ' ', $header));
}
$export->setStyle($export->getCell(0, $row), $export->getCell($column, $row))->setFontBold();

foreach ($content as $item) {
    $row++;
    $column = 0;
    foreach ($headers as $key => $header) {
        if (isset($item[$key])) {
            $export->setValue($export->getCell($column, $row), $item[$key]);
        }
        $column++;
    }
}

$export->saveFile(new FileParameter($fileLocation->getFileLocation()));

return $fileLocation;
}

return false;
}
}
 */