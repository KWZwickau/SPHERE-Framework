<?php

namespace SPHERE\Application\Transfer\Indiware\Export\AppointmentGrade;

use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Transfer\Indiware\Export\AppointmentGrade
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendExport()
    {

        $Stage = new Stage('Export', 'aller Stichtagsnoten eines Halbjahres');

        $TableContent = array();
        $tblTestTypeAppointed = Evaluation::useService()->getTestTypeByIdentifier(TblTestType::APPOINTED_DATE_TASK);
        $tblTaskListAppointed = Evaluation::useService()->getTaskAllByTestType($tblTestTypeAppointed);
//        $tblTestTypeBehavior = Evaluation::useService()->getTestTypeByIdentifier(TblTestType::BEHAVIOR_TASK);
//        $tblTaskListBehavior = Evaluation::useService()->getTaskAllByTestType($tblTestTypeBehavior);
        $tblTaskList = array();
        if ($tblTaskListAppointed) {
            $tblTaskList = $tblTaskListAppointed;
        }
//        if($tblTaskListAppointed && $tblTaskListBehavior){
//            $tblTaskList = array_merge($tblTaskListAppointed, $tblTaskListBehavior);
//        } elseif($tblTaskListAppointed && !$tblTaskListBehavior) {
//            $tblTaskList = $tblTaskListAppointed;
//        } elseif(!$tblTaskListAppointed && $tblTaskListBehavior) {
//            $tblTaskList = $tblTaskListBehavior;
//        }

        if ($tblTaskList) {
            foreach ($tblTaskList as $tblTask) {
                $Item['TaskName'] = $tblTask->getName();
                $Item['Year'] = '';
                $Item['Period'] = 'Gesamtes Schuljahr';
                if (($tblYear = $tblTask->getServiceTblYear())) {
                    $Item['Year'] = $tblYear->getDisplayName();

                    // Zeitraum ganzes Schuljahr
                    $tblPeriodList = $tblYear->getTblPeriodAll();
                    $from = false;
                    $to = false;
                    if ($tblPeriodList) {
                        foreach ($tblPeriodList as $Period) {
                            if (!$from || new \DateTime($from) >= new \DateTime($Period->getFromDate())) {
                                $from = $Period->getFromDate();
                            }
                            if (!$to || new \DateTime($to) <= new \DateTime($Period->getToDate())) {
                                $to = $Period->getToDate();
                            }
                        }
                        if ($to & $from) {
                            $Item['Period'] = 'Gesamtes Schuljahr '.new Muted(new Small('('.$from.' - '.$to.')'));
                        }
                    }
                }

                if (($tblPeriod = $tblTask->getServiceTblPeriod())) {
                    $Item['Period'] = $tblPeriod->getDisplayName();
                }

                $Item['WorkTime'] = $tblTask->getFromDate().' - '.$tblTask->getToDate();
                $Item['Date'] = $tblTask->getDate();
                $Item['Option'] = new External('', 'SPHERE\Application\Api\Transfer\Indiware\AppointmentGrade\Download',
                    new Download(),
                    array('TaskId' => $tblTask->getId()),
                    'Download Starten');
                array_push($TableContent, $Item);
            }
        }

//        $TableContent = Graduation::useService()->createGradeList(14);

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Title(new ListingTable().' Übersicht der Tests')
                            .new TableData($TableContent, null, array(
                                'TaskName' => 'Stichtagsauftrag',
                                'Year'     => 'Jahr',
                                'WorkTime' => 'Bearbeitungszeitraum',
                                'Date'     => 'Stichtag',
                                'Period'   => 'Halbjahr',
                                'Option'   => ''
                            ), array(
                                'order'      => array(array(3, 'desc')),
                                'columnDefs' => array(
                                    array('type' => 'de_date', 'targets' => 3)
                                )

                            ))
//                            new Title(new ListingTable().' Schüler Noten')
//                            .new TableData($TableContent)
                        )
                    ))
                )
            )
        );

        return $Stage;
    }

}
