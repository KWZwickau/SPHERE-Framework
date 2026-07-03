<?php
namespace SPHERE\Application\Api\Reporting\Custom\Annaberg;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Reporting\Custom\Annaberg\Person\Person;

class Common
{
    /**
     * @param null $DivisionCourseId
     *
     * @return string|bool
     */
    public function downloadPrintClassList($DivisionCourseId = null)
    {

        if(($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
        && !empty($TableContent = Person::useService()->createPrintClassList($tblDivisionCourse))) {
            $fileLocation = Person::useService()->createPrintClassListExcel($TableContent, $tblDivisionCourse);
            return FileSystem::getDownload($fileLocation->getRealPath(), "Klassenliste ".$tblDivisionCourse->getDisplayName()." ".date("Y-m-d").".xlsx")->__toString();
        }

        return false;
    }

    /**
     * @param $YearId
     *
     * @return false|string
     * @noinspection PhpUnused
     */
    public function downloadExportList($YearId = null): false|string
    {
        if (($tblYear = Term::useService()->getYearById($YearId))
            && ($headerList = Person::useService()->getExportHeaderList())
            && ($dataList = Person::useService()->createExportList($tblYear))
        ) {
            $fileLocation = Person::useService()->createExportListCSV($headerList, $dataList);

            return FileSystem::getDownload($fileLocation->getRealPath(), "Export " . date("Y-m-d").".csv")->__toString();
        }

        return false;
    }
}