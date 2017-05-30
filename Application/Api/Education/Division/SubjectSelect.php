<?php

namespace SPHERE\Application\Api\Education\Division;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Lesson\Division\Division as DivisionAPP;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
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
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\System\Extension\Extension;

class SubjectSelect extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('tableAvailableSubject');
        $Dispatcher->registerMethod('tableUsedSubject');
        $Dispatcher->registerMethod('serviceAddSubject');
        $Dispatcher->registerMethod('serviceRemoveSubject');

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
    public static function receiverAvailable($Content = '')
    {
        return (new BlockReceiver($Content))->setIdentifier('AvailableReceiver');
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
     * @param null $DivisionId
     *
     * @return Layout
     */
    public static function tableUsedSubject($DivisionId = null)
    {

        // get Content
        $tblDivision = DivisionAPP::useService()->getDivisionById($DivisionId);
        $ContentList = false;
        $ContentListAvailable = false;
        if ($tblDivision) {
            $ContentList = self::getTableContentUsed($tblDivision);
            $ContentListAvailable = self::getTableContentAvailable($tblDivision);
        }

        // Select
        $Table = array();
        if (is_array($ContentList)) {
            if (!empty($ContentList)) {
                foreach ($ContentList as $Subject) {
                    $Table[] = array(
                        'Acronym'     => $Subject['Acronym'],
                        'Name'        => $Subject['Name'],
                        'Description' => $Subject['Description'],
                        'Option'      => (new Standard('', SubjectSelect::getEndpoint(), new MinusSign(),
                            array('Id' => $Subject['Id'], 'DivisionId' => $Subject['DivisionId']), 'Entfernen'))
                            ->ajaxPipelineOnClick(SubjectSelect::pipelineMinus($Subject['Id'], $Subject['DivisionId']))
                    );
                }
                // Anzeige
                $left = new TableData($Table, null, array(
                    'Acronym'     => 'Kürzel',
                    'Name'        => 'Name',
                    'Description' => 'Beschreibung',
                    'Option'      => ''
                ),
                    array(
                        'columnDefs' => array(
                            array('orderable' => false, 'width' => '1%', 'targets' => -1)
                        ),
                    )
                );
            } else {
                $left = new Info('Keine Fächer ausgewählt');
            }
        } else {
            $left = new Warning('Klasse nicht gefunden');
        }

        // Select
        $TableAvailable = array();
        if (is_array($ContentListAvailable)) {
            if (!empty($ContentListAvailable)) {
                foreach ($ContentListAvailable as $Subject) {
                    $TableAvailable[] = array(
                        'Acronym'     => $Subject['Acronym'],
                        'Name'        => $Subject['Name'],
                        'Description' => $Subject['Description'],
                        'Option'      => (new Standard('', SubjectSelect::getEndpoint(), new PlusSign(),
                            array('Id' => $Subject['Id'], 'DivisionId' => $Subject['DivisionId']), 'Hinzufügen'))
                            ->ajaxPipelineOnClick(SubjectSelect::pipelinePlus($Subject['Id'], $Subject['DivisionId']))
                    );
                }
                // Anzeige
                $right = new TableData($TableAvailable, null, array(
                    'Acronym'     => 'Kürzel',
                    'Name'        => 'Name',
                    'Description' => 'Beschreibung',
                    'Option'      => ''
                ),
                    array(
                        'columnDefs' => array(
                            array('orderable' => false, 'width' => '1%', 'targets' => -1)
                        ),
                    )
                );
            } else {
                $right = new Info('Keine weiteren Fächer verfügbar');
            }
        } else {
            $right = new Warning('Klasse nicht gefunden');
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
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public static function getTableContentUsed(TblDivision $tblDivision)
    {

        $tblSubjectUsedList = DivisionAPP::useService()->getSubjectAllByDivision($tblDivision);

        $usedList = array();
        if ($tblSubjectUsedList) {
            array_walk($tblSubjectUsedList, function (TblSubject $tblSubjectUsed) use ($tblDivision, &$usedList) {
                $Item['Id'] = $tblSubjectUsed->getId();
                $Item['DivisionId'] = $tblDivision->getId();
                $Item['Acronym'] = $tblSubjectUsed->getAcronym();
                $Item['Name'] = $tblSubjectUsed->getName();
                $Item['Description'] = $tblSubjectUsed->getDescription();
                array_push($usedList, $Item);
            });
        }

        return $usedList;
    }

    /**
     * @param null $Id
     * @param null $DivisionId
     *
     * @return Pipeline
     */
    public static function pipelineMinus($Id = null, $DivisionId = null)
    {

        $Pipeline = new Pipeline();

        // execute Service
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'serviceRemoveSubject',
            'Id'             => $Id,
            'DivisionId'     => $DivisionId
        ));
        $Pipeline->appendEmitter($Emitter);

        // refresh Table
        $Emitter = new ServerEmitter(self::receiverUsed(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'tableUsedSubject',
            'DivisionId'     => $DivisionId
        ));
        $Pipeline->appendEmitter($Emitter);
//
//        // refresh Table
//        $Emitter = new ServerEmitter(self::receiverAvailable(), self::getEndpoint());
//        $Emitter->setPostPayload(array(
//            self::API_TARGET => 'tableAvailableSubject',
//            'DivisionId'     => $DivisionId
//        ));
//        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param null $Id
     * @param null $DivisionId
     */
    public function serviceRemoveSubject($Id = null, $DivisionId = null)
    {

        $tblSubject = Subject::useService()->getSubjectById($Id);
        $tblDivision = DivisionAPP::useService()->getDivisionById($DivisionId);

        if ($tblSubject && $tblDivision) {
            $tblDivisionSubjectList = DivisionAPP::useService()->getDivisionSubjectBySubjectAndDivision($tblSubject,
                $tblDivision);
            if ($tblDivisionSubjectList) {
                foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                    DivisionAPP::useService()->removeDivisionSubject($tblDivisionSubject);
                }
            }
        }
    }

    /**
     * @param $DivisionId
     *
     * @return Info|Warning|TableData
     */
    public static function tableAvailableSubject($DivisionId)
    {

        // get Content
        $tblDivision = DivisionAPP::useService()->getDivisionById($DivisionId);
        $ContentList = false;
        if ($tblDivision) {
            $ContentList = self::getTableContentAvailable($tblDivision);
        }

        // Select
        $Table = array();
        if (is_array($ContentList)) {
            if (!empty($ContentList)) {
                foreach ($ContentList as $Subject) {
                    $Table[] = array(
                        'Acronym'     => $Subject['Acronym'],
                        'Name'        => $Subject['Name'],
                        'Description' => $Subject['Description'],
                        'Option'      => (new Standard('', SubjectSelect::getEndpoint(), new PlusSign(),
                            array('Id' => $Subject['Id'], 'DivisionId' => $Subject['DivisionId'])))
                            ->ajaxPipelineOnClick(SubjectSelect::pipelinePlus($Subject['Id'], $Subject['DivisionId']))
                    );
                }
                // Anzeige
                return new TableData($Table, null, array(
                    'Acronym'     => 'Kürzel',
                    'Name'        => 'Name',
                    'Description' => 'Beschreibung',
                    'Option'      => 'Option'
                ),
                    array(
                        'columnDefs' => array(
                            array('orderable' => false, 'width' => '1%', 'targets' => -1)
                        ),
                    )
                );
            }
            return new Info('Keine weiteren Fächer verfügbar');
        }
        return new Warning('Klasse nicht gefunden');
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public static function getTableContentAvailable(TblDivision $tblDivision)
    {

        $tblSubjectUsedList = DivisionAPP::useService()->getSubjectAllByDivision($tblDivision);
        $tblSubjectAll = Subject::useService()->getSubjectAll();

        // get available Subjects in compare of used Subjects
        if (is_array($tblSubjectUsedList)) {
            $tblSubjectAvailable = array_diff($tblSubjectAll, $tblSubjectUsedList);
        } else {
            $tblSubjectAvailable = $tblSubjectAll;
        }

        $availableList = array();
        if ($tblSubjectAvailable) {
            array_walk($tblSubjectAvailable, function (TblSubject $tblSubjectUsed) use ($tblDivision, &$availableList) {
                $Item['Id'] = $tblSubjectUsed->getId();
                $Item['DivisionId'] = $tblDivision->getId();
                $Item['Acronym'] = $tblSubjectUsed->getAcronym();
                $Item['Name'] = $tblSubjectUsed->getName();
                $Item['Description'] = $tblSubjectUsed->getDescription();
                array_push($availableList, $Item);
            });
        }
        return $availableList;
    }

    /**
     * @param null $Id
     * @param null $DivisionId
     *
     * @return Pipeline
     */
    public static function pipelinePlus($Id = null, $DivisionId = null)
    {

        $Pipeline = new Pipeline();

        // execute Service
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'serviceAddSubject',
            'Id'             => $Id,
            'DivisionId'     => $DivisionId
        ));
        $Pipeline->appendEmitter($Emitter);

        // refresh Table
        $Emitter = new ServerEmitter(self::receiverUsed(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'tableUsedSubject',
            'DivisionId'     => $DivisionId
        ));
        $Pipeline->appendEmitter($Emitter);

//        // refresh Table
//        $Emitter = new ServerEmitter(self::receiverAvailable(), self::getEndpoint());
//        $Emitter->setPostPayload(array(
//            self::API_TARGET => 'tableAvailableSubject',
//            'DivisionId'     => $DivisionId
//        ));
//        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param null $Id
     * @param null $DivisionId
     */
    public function serviceAddSubject($Id = null, $DivisionId = null)
    {

        $tblSubject = Subject::useService()->getSubjectById($Id);
        $tblDivision = DivisionAPP::useService()->getDivisionById($DivisionId);

        if ($tblSubject && $tblDivision) {
            DivisionAPP::useService()->addSubjectToDivision($tblDivision, $tblSubject);
        }
    }
}
