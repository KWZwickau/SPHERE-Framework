<?php

namespace SPHERE\Application\Transfer\Indiware\Export\Meta;

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

/**
 * Class Frontend
 * @package SPHERE\Application\Transfer\Indiware\Export\Meta
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendPrepare()
    {
        $Stage = new Stage('Indiware', 'Datentransfer');
        $Stage->addButton(new Standard('Zurück', '/Transfer/Indiware/Export', new ChevronLeft()));
        $Stage->setMessage('Exportvorbereitung / Klassenauswahl');

        $YearId = null;
        $tblDivisionList = array();
        $tableContent = array();
        if(($tblYearList = Term::useService()->getYearByNow())){
            foreach($tblYearList as $tblYear) {
                $currentList = Division::useService()->getDivisionAllByYear($tblYear);
                if($currentList){
                    foreach($currentList as $current){
                        if($current->getTypeName() == 'Gymnasium'){
                            $tblDivisionList[] = $current;
                        }
                    }
                }
            }
        }
        if(!empty($tblDivisionList)){
            // Klassen der Schulart: Gymnasium
            array_walk($tblDivisionList, function(TblDivision $tblDivision) use (&$tableContent){
                $item['Division'] = $tblDivision->getDisplayName();
                $item['Term'] = $tblDivision->getServiceTblYear()->getDisplayName();
                $item['SchoolType'] = $tblDivision->getTypeName();
                $item['countStudent'] = 0;
                $item['Option'] = new Standard('', '/Api/Transfer/Indiware/Meta/Download', new Download(), array('DivisionId' => $tblDivision->getId()));

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
                        new TableData($tableContent, null, array(
                            'Division' => 'Klasse',
                            'Term' => 'Schuljahr',
                            'SchoolType' => 'Schulart',
                            'countStudent' => 'Anzahl Schüler',
                            'Option' => '',
                        ))
                    )
                )
            )
        ));

        return $Stage;
    }

}
