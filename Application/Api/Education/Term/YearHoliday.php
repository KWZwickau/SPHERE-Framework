<?php

namespace SPHERE\Application\Api\Education\Term;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblHoliday;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\MinusSign;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
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
 * Class YearHoliday
 *
 * @package SPHERE\Application\Api\Education\Term
 */
class YearHoliday extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('tableHoliday');
        $Dispatcher->registerMethod('serviceAddHoliday');
        $Dispatcher->registerMethod('serviceRemoveHoliday');

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
     * @param TblCompany|null $tblCompany
     *
     * @return array
     */
    public static function getTableContentUsed(TblYear $tblYear, TblCompany $tblCompany = null)
    {
        $tblHolidayUsedList = Term::useService()->getHolidayAllByYear($tblYear, $tblCompany);
        $usedList = array();
        if ($tblHolidayUsedList) {
            foreach ($tblHolidayUsedList as $tblHolidayUsed) {
                $usedList[] = array(
                    'Id' => $tblHolidayUsed->getId(),
                    'Name' => $tblHolidayUsed->getName(),
                    'FromDate' => $tblHolidayUsed->getFromDate(),
                    'ToDate' => $tblHolidayUsed->getToDate(),
                    'Type' => $tblHolidayUsed->getTblHolidayType()->getName()
                );
            }
        }

        return $usedList;
    }

    /**
     * @param TblYear $tblYear
     * @param TblCompany|null $tblCompany
     *
     * @return array
     */
    public static function getTableContentAvailable(TblYear $tblYear, TblCompany $tblCompany = null)
    {
        $tblHolidayUsedList = Term::useService()->getHolidayAllByYear($tblYear, $tblCompany);
        $tblHolidayAllWhereYears = Term::useService()->getHolidayAllWhereYear($tblYear);

        $contentHolidayAvailable = array();
        if (is_array($tblHolidayUsedList) && is_array($tblHolidayAllWhereYears)) {
            $tblHolidayAvailableList = array_udiff($tblHolidayAllWhereYears, $tblHolidayUsedList,
                function (TblHoliday $ObjectA, TblHoliday $ObjectB) {
                    return $ObjectA->getId() - $ObjectB->getId();
                }
            );
        } else {
            $tblHolidayAvailableList = $tblHolidayAllWhereYears;
        }

        if (is_array($tblHolidayAvailableList)) {
            foreach ($tblHolidayAvailableList as $tblHolidayAvailable) {
                $contentHolidayAvailable[] = array(
                    'Id' => $tblHolidayAvailable->getId(),
                    'Name' => $tblHolidayAvailable->getName(),
                    'FromDate' => $tblHolidayAvailable->getFromDate(),
                    'ToDate' => $tblHolidayAvailable->getToDate(),
                    'Type' => $tblHolidayAvailable->getTblHolidayType()->getName()
                );
            }
        }

        return $contentHolidayAvailable;
    }

    /**
     * @param null $YearId
     * @param null $CompanyId
     *
     * @return Layout
     */
    public static function tableHoliday($YearId = null, $CompanyId = null)
    {
        // get Content
        $tblYear = Term::useService()->getYearById($YearId);
        $tblCompany = Company::useService()->getCompanyById($CompanyId);
        $ContentList = false;
        $ContentListAvailable = false;
        if ($tblYear) {
            $ContentList = self::getTableContentUsed($tblYear, $tblCompany ? $tblCompany : null);
            $ContentListAvailable = self::getTableContentAvailable($tblYear, $tblCompany ? $tblCompany : null);
        }

        // Select
        $Table = array();
        if (is_array($ContentList)) {
            if (!empty($ContentList)) {
                foreach ($ContentList as $Holiday) {
                    $Table[] = array(
                        'Name' => $Holiday['Name'],
                        'FromDate' => $Holiday['FromDate'],
                        'ToDate' => $Holiday['ToDate'],
                        'Type' => $Holiday['Type'],
                        'Option' => (new Standard('', self::getEndpoint(), new MinusSign(), array(), 'Entfernen'))
                            ->ajaxPipelineOnClick(self::pipelineMinus($Holiday['Id'], $tblYear->getId(), $CompanyId))
                    );
                }
                // Anzeige
                $left = (new TableData($Table, new Title('Ausgewählte', 'Unterrichtsfreie Zeiträume'),
                    array(
                        'FromDate' => 'Datum von',
                        'ToDate' => 'Datum bis',
                        'Name' => 'Name',
                        'Type' => 'Typ',
                        'Option'  => ''
                    ),
                    array(
                        'order' => array(
                            array(0, 'desc'),
                            array(1, 'desc')
                        ),
                        'columnDefs' => array(
                            array('type' => 'de_date', 'targets' => array(0, 1)),
                            array('orderable' => false, 'width' => '1%', 'targets' => -1),
                        ),
                        'responsive' => false
                    )
                ))->setHash(__NAMESPACE__ . 'YearHoliday' . 'Selected');
            } else {
                $left = new Info('Keine Unterrichtsfreie Zeiträume ausgewählt');
            }
        } else {
            $left = new Warning('Schuljahr nicht gefunden');
        }

        // Select
        $TableAvailable = array();
        if (is_array($ContentListAvailable)) {
            if (!empty($ContentListAvailable)) {
                foreach ($ContentListAvailable as $Holiday) {
                    $TableAvailable[] = array(
                        'Name' => $Holiday['Name'],
                        'FromDate' => $Holiday['FromDate'],
                        'ToDate' => $Holiday['ToDate'],
                        'Type' => $Holiday['Type'],
                        'Option' => (new Standard('', self::getEndpoint(), new PlusSign(), array(), 'Hinzufügen'))
                            ->ajaxPipelineOnClick(self::pipelinePlus($Holiday['Id'], $tblYear->getId(), $CompanyId))
                    );
                }
                // Anzeige
                $right = (new TableData($TableAvailable, new Title('Verfügbare', 'Unterrichtsfreie Zeiträume'),
                    array(
                        'FromDate' => 'Datum von',
                        'ToDate' => 'Datum bis',
                        'Name' => 'Name',
                        'Type' => 'Typ',
                        'Option'  => ''
                    ),
                    array(
                        'order' => array(
                            array(0, 'desc'),
                            array(1, 'desc')
                        ),
                        'columnDefs' => array(
                            array('type' => 'de_date', 'targets' => array(0, 1)),
                            array('orderable' => false, 'width' => '1%', 'targets' => -1),
                        ),
                        'responsive' => false
                    )
                ))->setHash(__NAMESPACE__ . 'YearHoliday' . 'Available');
            } else {
                $right = new Info('Keine weiteren Unterrichtsfreie Zeiträume verfügbar');
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
     * @param null $CompanyId
     *
     * @return Pipeline
     */
    public static function pipelineMinus($Id = null, $YearId = null, $CompanyId = null)
    {
        $Pipeline = new Pipeline();

        // execute Service
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'serviceRemoveHoliday',
            'Id' => $Id,
            'YearId' => $YearId,
            'CompanyId' => $CompanyId
        ));
        $Pipeline->appendEmitter($Emitter);

        // refresh Table
        $Emitter = new ServerEmitter(self::receiverUsed(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'tableHoliday',
            'YearId' => $YearId,
            'CompanyId' => $CompanyId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param null $Id
     * @param null $YearId
     * @param null $CompanyId
     */
    public function serviceRemoveHoliday($Id = null, $YearId = null, $CompanyId = null)
    {
        if (($tblHoliday = Term::useService()->getHolidayById($Id))
            && ($tblYear = Term::useService()->getYearById($YearId))
        ) {
            $tblCompany = Company::useService()->getCompanyById($CompanyId);
            Term::useService()->removeYearHoliday($tblYear, $tblHoliday, $tblCompany ? $tblCompany : null);
        }
    }

    /**
     * @param null $Id
     * @param null $YearId
     * @param null $CompanyId
     *
     * @return Pipeline
     */
    public static function pipelinePlus($Id = null, $YearId = null, $CompanyId = null)
    {
        $Pipeline = new Pipeline();

        // execute Service
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'serviceAddHoliday',
            'Id' => $Id,
            'YearId' => $YearId,
            'CompanyId' => $CompanyId
        ));
        $Pipeline->appendEmitter($Emitter);

        // refresh Table
        $Emitter = new ServerEmitter(self::receiverUsed(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'tableHoliday',
            'YearId' => $YearId,
            'CompanyId' => $CompanyId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param null $Id
     * @param null $YearId
     * @param null $CompanyId
     */
    public function serviceAddHoliday($Id = null, $YearId = null, $CompanyId = null)
    {
        if (($tblHoliday = Term::useService()->getHolidayById($Id))
            && ($tblYear = Term::useService()->getYearById($YearId))
        ) {
            $tblCompany = Company::useService()->getCompanyById($CompanyId);
            Term::useService()->addYearHoliday($tblYear, $tblHoliday, $tblCompany ? $tblCompany : null);
        }
    }
}