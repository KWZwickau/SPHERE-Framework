<?php

namespace SPHERE\Application\Api\Education\Term;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\MinusSign;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Unchecked;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\System\Extension\Extension;

/**
 * Class YearPeriod
 *
 * @package SPHERE\Application\Api\Education\Term
 */
class YearPeriod extends Extension implements IApiInterface
{
    use ApiTrait;

    /**
     * @param string $Method Callable Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('tablePeriod');
        $Dispatcher->registerMethod('serviceAddPeriod');
        $Dispatcher->registerMethod('serviceRemovePeriod');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverUsed($Content = '')
    {
        return (new BlockReceiver($Content))->setIdentifier('UsedReceiver');
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverService($Content = '')
    {
        return (new BlockReceiver($Content))->setIdentifier('ServiceReceiver');
    }

    /**
     * @param TblYear $tblYear
     *
     * @return array
     */
    public static function getTableContentUsed(TblYear $tblYear)
    {
        $tblPeriodUsedList = Term::useService()->getPeriodListByYear($tblYear, false, true);
        $usedList = array();
        if ($tblPeriodUsedList) {
            foreach ($tblPeriodUsedList as $tblPeriodUsed) {
                $usedList[] = array(
                    'Id' => $tblPeriodUsed->getId(),
                    'Name' => $tblPeriodUsed->getName(),
                    'FromDate' => $tblPeriodUsed->getFromDate(),
                    'ToDate' => $tblPeriodUsed->getToDate(),
                    'Description' => $tblPeriodUsed->getDescription(),
                    'IsLevel12' => $tblPeriodUsed->isLevel12() ? new Check() : new Unchecked()
                );
            }
        }

        return $usedList;
    }

    /**
     * @param TblYear $tblYear
     *
     * @return array
     */
    public static function getTableContentAvailable(TblYear $tblYear)
    {
        $tblPeriodUsedList = Term::useService()->getPeriodListByYear($tblYear, false, true);
        $tblPeriodAll = Term::useService()->getPeriodAll();

        $contentPeriodAvailable = array();
        if (is_array($tblPeriodUsedList)) {
            $tblPeriodAvailableList = array_udiff($tblPeriodAll, $tblPeriodUsedList,
                function (TblPeriod $ObjectA, TblPeriod $ObjectB) {
                    return $ObjectA->getId() - $ObjectB->getId();
                }
            );
        } else {
            $tblPeriodAvailableList = $tblPeriodAll;
        }

        if (is_array($tblPeriodAvailableList)) {
            foreach ($tblPeriodAvailableList as $tblPeriodAvailable) {
                $contentPeriodAvailable[] = array(
                    'Id' => $tblPeriodAvailable->getId(),
                    'Name' => $tblPeriodAvailable->getName(),
                    'FromDate' => $tblPeriodAvailable->getFromDate(),
                    'ToDate' => $tblPeriodAvailable->getToDate(),
                    'Description' => $tblPeriodAvailable->getDescription(),
                    'IsLevel12' => $tblPeriodAvailable->isLevel12() ? new Check() : new Unchecked(),
                );
            }
        }
        
        return $contentPeriodAvailable;
    }

    /**
     * @param null $YearId
     *
     * @return Layout
     */
    public static function tablePeriod($YearId = null)
    {
        // get Content
        $tblYear = Term::useService()->getYearById($YearId);
        $ContentList = false;
        $ContentListAvailable = false;
        if ($tblYear) {
            $ContentList = self::getTableContentUsed($tblYear);
            $ContentListAvailable = self::getTableContentAvailable($tblYear);
        }

        // Select
        $Table = array();
        if (is_array($ContentList)) {
            if (!empty($ContentList)) {
                foreach ($ContentList as $Period) {
                    $Table[] = array(
                        'Name' => $Period['Name'],
                        'FromDate' => $Period['FromDate'],
                        'ToDate' => $Period['ToDate'],
                        'Description' => $Period['Description'],
                        'IsLevel12' => $Period['IsLevel12'],
                        'Option' => (new Standard('', self::getEndpoint(), new MinusSign(), array(), 'Entfernen'))
                            ->ajaxPipelineOnClick(self::pipelineMinus($Period['Id'], $tblYear->getId()))
                    );
                }
                // Anzeige
                $left = (new TableData($Table, new Title('Ausgewählte', 'Zeiträume'), array(
                    'Name'        => 'Name',
                    'FromDate'    => 'Von',
                    'ToDate'      => 'Bis',
                    'Description' => 'Beschreibung',
                    'IsLevel12' => 'Für 12. Klasse Gy / 13. Klasse BGy',
                    'Option'      => ''
                ),
                    array(
                        'order' => array(
                            array('1', 'desc'),
                        ),
                        'columnDefs' => array(
                            array('type' => 'de_date', 'targets' => array(1, 2)),
                            array('orderable' => false, 'width' => '1%', 'targets' => -1),
                        ),
                        'responsive' => false
                    )
                ))->setHash(__NAMESPACE__ . 'YearPeriod' . 'Selected');
            } else {
                $left = new Info('Keine Zeiträume ausgewählt');
            }
        } else {
            $left = new Warning('Schuljahr nicht gefunden');
        }

        // Select
        $TableAvailable = array();
        if (is_array($ContentListAvailable)) {
            if (!empty($ContentListAvailable)) {
                foreach ($ContentListAvailable as $Period) {
                    $TableAvailable[] = array(
                        'Name' => $Period['Name'],
                        'FromDate' => $Period['FromDate'],
                        'ToDate' => $Period['ToDate'],
                        'Description' => $Period['Description'],
                        'IsLevel12' => $Period['IsLevel12'],
                        'Option' => (new Standard('', self::getEndpoint(), new PlusSign(), array(), 'Hinzufügen'))
                            ->ajaxPipelineOnClick(self::pipelinePlus($Period['Id'], $tblYear->getId()))
                    );
                }
                // Anzeige
                $right = (new TableData($TableAvailable, new Title('Verfügbare', 'Zeiträume'), array(
                    'Name'        => 'Name',
                    'FromDate'    => 'Von',
                    'ToDate'      => 'Bis',
                    'Description' => 'Beschreibung',
                    'IsLevel12' => 'Für 12. Klasse Gy / 13. Klasse BGy',
                    'Option'      => ''
                ),
                    array(
                        'order' => array(
                            array('1', 'desc'),
                        ),
                        'columnDefs' => array(
                            array('type' => 'de_date', 'targets' => array(1, 2)),
                            array('orderable' => false, 'width' => '1%', 'targets' => -1),
                        ),
                        'responsive' => false
                    )
                ))->setHash(__NAMESPACE__ . 'YearPeriod' . 'Available');
            } else {
                $right = new Info('Keine weiteren Zeiträume verfügbar');
            }
        } else {
            $right = new Warning('Schuljahr nicht gefunden');
        }

        return
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    $left
                    , 6),
                new LayoutColumn(
                    $right
                    , 6)
            ))));

    }

    /**
     * @param null $Id
     * @param null $YearId
     *
     * @return Pipeline
     */
    public static function pipelineMinus($Id = null, $YearId = null)
    {
        $Pipeline = new Pipeline();

        // execute Service
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'serviceRemovePeriod',
            'Id' => $Id,
            'YearId' => $YearId
        ));
        $Pipeline->appendEmitter($Emitter);

        // refresh Table
        $Emitter = new ServerEmitter(self::receiverUsed(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'tablePeriod',
            'YearId' => $YearId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param null $Id
     * @param null $YearId
     */
    public function serviceRemovePeriod($Id = null, $YearId = null)
    {
        if (($tblPeriod = Term::useService()->getPeriodById($Id))
            && ($tblYear = Term::useService()->getYearById($YearId))
        ) {
            Term::useService()->removeYearPeriod($tblYear, $tblPeriod);
        }
    }

    /**
     * @param null $Id
     * @param null $YearId
     *
     * @return Pipeline
     */
    public static function pipelinePlus($Id = null, $YearId = null)
    {
        $Pipeline = new Pipeline();

        // execute Service
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'serviceAddPeriod',
            'Id' => $Id,
            'YearId' => $YearId
        ));
        $Pipeline->appendEmitter($Emitter);

        // refresh Table
        $Emitter = new ServerEmitter(self::receiverUsed(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'tablePeriod',
            'YearId' => $YearId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param null $Id
     * @param null $YearId
     */
    public function serviceAddPeriod($Id = null, $YearId = null)
    {
        if (($tblPeriod = Term::useService()->getPeriodById($Id))
            && ($tblYear = Term::useService()->getYearById($YearId))
        ) {
            Term::useService()->addYearPeriod($tblYear, $tblPeriod);
        }
    }
}