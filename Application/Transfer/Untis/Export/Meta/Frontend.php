<?php

namespace SPHERE\Application\Transfer\Untis\Export\Meta;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
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

class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @return Stage
     */
    public function frontendPrepare(): Stage
    {
        $Stage = new Stage('Untis', 'Datentransfer');
        $Stage->addButton(new Standard('Zurück', '/Transfer/Untis/Export', new ChevronLeft()));
        $Stage->setMessage('Exportvorbereitung / Klassenauswahl');

        $tblDivisionList = array();
        $tableContent = array();

        $tblFutureYearList = Term::useService()->getYearAllFutureYears(1);
        $tblYearList = Term::useService()->getYearByNow();

        if ($tblFutureYearList && $tblYearList) {
            $tblYearList = array_merge($tblYearList, $tblFutureYearList);
        } elseif ($tblFutureYearList) {
            $tblYearList = $tblFutureYearList;
        }

        if($tblYearList){
            foreach($tblYearList as $tblYear) {
                $currentList = Division::useService()->getDivisionAllByYear($tblYear);
                if($currentList){
                    foreach($currentList as $current){
                        if($current->getTypeName() == 'Gymnasium' || $current->getTypeName() == 'Berufliches Gymnasium'){
                            $tblDivisionList[] = $current;
                        }
                    }
                }
            }
        }
        if(!empty($tblDivisionList)){
            array_walk($tblDivisionList, function(TblDivision $tblDivision) use (&$tableContent){
                $item['Division'] = $tblDivision->getDisplayName();
                $item['Term'] = $tblDivision->getServiceTblYear()->getDisplayName();
                $item['SchoolType'] = $tblDivision->getTypeName();
                $item['countStudent'] = 0;
                $item['Option'] = new Standard('', '/Api/Transfer/Untis/Meta/Download', new Download(), array('DivisionId' => $tblDivision->getId()));

                $tblDivisionStudentList = Division::useService()->getDivisionStudentAllByDivision($tblDivision);
                if($tblDivisionStudentList){
                    $item['countStudent'] = count($tblDivisionStudentList);
                }

                array_push($tableContent, $item);
            });
        }

        $Stage->setContent(new Layout(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new TableData($tableContent, null,
                            array(
                                'Term' => 'Schuljahr',
                                'Division' => 'Klasse',
                                'SchoolType' => 'Schulart',
                                'countStudent' => 'Anzahl Schüler',
                                'Option' => '',
                            ),
                            array(
                                'order' => array(
                                    array(0, 'desc'),
                                    array(1, 'asc'),
                                ),
                                'columnDefs' => array(
                                    array('type' => 'natural', 'targets' => 1),
                                )
                            )
                        )
                    )
                )
            )
        ));

        return $Stage;
    }
}