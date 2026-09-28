<?php
namespace SPHERE\Application\Api\Reporting\Custom\Annaberg;

use DateTime;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Api\Reporting\Standard\ExcelBuilder;
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
     * @param null $YearId
     * @param null $Report
     * @param string $Type
     *
     * @return false|string
     *
     * @noinspection PhpUnused
     */
    public function downloadExportList($YearId = null, $Report = null, string $Type = 'CSV'): false|string
    {
        $missingUsername = [
            'StudentCount' => 0,
            'CustodyCount' => 0
        ];
        if (($tblYear = Term::useService()->getYearById($YearId))) {
            switch ($Report) {
                case 'SchulAPP':
                    $headerList = Person::useService()->getExportHeaderList();
                    $dataList = Person::useService()->createExportList($tblYear);
                    break;
                case 'SchulAPP - Schueler':
                    $headerList = Person::useService()->getExportStudentHeaderList();
                    $dataList = Person::useService()->createExportStudentList($tblYear, $missingUsername, false);
                    break;
                case 'SchulAPP - Schueler und Sorgeberechtigte':
                    $headerList = Person::useService()->getExportStudentCustodyHeaderList();
                    $dataList = Person::useService()->createExportStudentCustodyList($tblYear, $missingUsername, false);
                    break;
                default:
                    $headerList = false;
                    $dataList = false;
            }

            if ($headerList
                && $dataList
            ) {
                if ($Type == 'CSV') {
                    $fileLocation = Person::useService()->createExportListCSV($headerList, $dataList);

                    return FileSystem::getDownload($fileLocation->getRealPath(), 'Export ' . $Report . ' ' . (new DateTime())->format('d-m-Y') . ".csv")->__toString();
                } else {
                    // Excel
                    return ExcelBuilder::getDownloadFile(
                        'Export ' . $Report . ' ' . (new DateTime())->format('d-m-Y'),
                        $headerList,
                        $dataList,
                    );
                }
            }
        }

        return false;
    }
}