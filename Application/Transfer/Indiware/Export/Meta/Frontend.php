<?php

namespace SPHERE\Application\Transfer\Indiware\Export\Meta;

use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Transfer\Indiware\Export\Meta
 */
class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @param bool $IsAllYears
     * @param string|null $YearId
     *
     * @return Stage
     */
    public function frontendPrepare(bool $IsAllYears = false, ?string $YearId = null): Stage
    {
        $Stage = new Stage('Indiware', 'Datentransfer');
        $Stage->addButton(new Standard('Zurück', '/Transfer/Indiware/Export', new ChevronLeft()));
        $Stage->setMessage('Exportvorbereitung / Klassenauswahl');

        $Stage->setContent($this->getCourseSelectStageContent(
            '/Transfer/Indiware/Export/Meta',
            '/Api/Transfer/Indiware/Meta/Download',
            $IsAllYears,
            $YearId
        ));

        return $Stage;
    }

    /**
     * @param string $route
     * @param string $downloadApiRoute
     * @param bool $IsAllYears
     * @param string|null $YearId
     *
     * @return Layout
     */
    public function getCourseSelectStageContent(string $route, string $downloadApiRoute, bool $IsAllYears, ?string $YearId): Layout
    {
        list($yearButtonList, $filterYearList)
            = Term::useFrontend()->getYearButtonsAndYearFilters($route, $IsAllYears, $YearId);

        $dataList = array();
        $tblDivisionCourseList = array();
        if ($filterYearList) {
            foreach ($filterYearList as $tblYear) {
                if (($tblDivisionCourseListDivision = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, TblDivisionCourseType::TYPE_DIVISION))) {
                    $tblDivisionCourseList = $tblDivisionCourseListDivision;
                }
                if (($tblDivisionCourseListCoreGroup = DivisionCourse::useService()->getDivisionCourseListBy($tblYear,
                    TblDivisionCourseType::TYPE_CORE_GROUP))) {
                    $tblDivisionCourseList = array_merge($tblDivisionCourseList, $tblDivisionCourseListCoreGroup);
                }
            }
        } else {
            if (($tblDivisionCourseListDivision = DivisionCourse::useService()->getDivisionCourseListBy(null, TblDivisionCourseType::TYPE_DIVISION))) {
                $tblDivisionCourseList = $tblDivisionCourseListDivision;
            }
            if (($tblDivisionCourseListCoreGroup = DivisionCourse::useService()->getDivisionCourseListBy(null, TblDivisionCourseType::TYPE_CORE_GROUP))) {
                $tblDivisionCourseList = array_merge($tblDivisionCourseList, $tblDivisionCourseListCoreGroup);
            }
        }

        /** @var TblDivisionCourse $tblDivisionCourse */
        foreach ($tblDivisionCourseList as $tblDivisionCourse) {
            $count = $tblDivisionCourse->getCountStudents();
            $dataList[] = array(
                'Year' => $tblDivisionCourse->getYearName(),
                'DivisionCourse' => $tblDivisionCourse->getDisplayName(),
                'DivisionCourseType' => $tblDivisionCourse->getTypeName(),
                'SchoolTypes' => $tblDivisionCourse->getSchoolTypeListFromStudents(true),
                'Count' => $count,
                'Option' => $count > 0
                    ? new Standard('', $downloadApiRoute, new Download(), array('DivisionCourseId' => $tblDivisionCourse->getId()))
                    : ''
            );
        }

        return new Layout(array(
            new LayoutGroup(array(
                new LayoutRow(new LayoutColumn(
                    empty($yearButtonList) ? '' : $yearButtonList
                )),
            )),
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new TableData($dataList, null,
                            array(
                                'Year' => 'Schuljahr',
                                'DivisionCourse' => 'Kurs',
                                'DivisionCourseType' => 'Kurs-Typ',
                                'SchoolTypes' => 'Schularten',
                                'Count' => 'Schüler',
                                'Option' => '',
                            ), array(
                                'order' => array(
                                    array('0', 'desc'),
                                    array('1', 'asc'),
                                ),
                                'columnDefs' => array(
                                    array('type' => 'natural', 'targets' => 1),
                                    array('orderable' => false, 'width' => '1%', 'targets' => -1)
                                ),
                                'responsive' => false
                            )
                        )
                    )
                )
            )
        ));
    }
}
