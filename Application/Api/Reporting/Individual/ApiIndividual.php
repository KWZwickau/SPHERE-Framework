<?php

namespace SPHERE\Application\Api\Reporting\Individual;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Reporting\Individual\Individual;
use SPHERE\Application\Reporting\Individual\Service\Entity\TblPreset;
use SPHERE\Application\Reporting\Individual\Service\Entity\ViewStudent;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Info as InfoMessage;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiIndividual
 * @package SPHERE\Application\Api\Reporting\Individual
 */
class ApiIndividual extends Extension implements IApiInterface
{
    use ApiTrait;

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        $Dispatcher->registerMethod('getNewNavigation');
        $Dispatcher->registerMethod('removeFieldAll');
        $Dispatcher->registerMethod('getModalPreset');
        $Dispatcher->registerMethod('addField');
        $Dispatcher->registerMethod('buildFilter');


        $Dispatcher->registerMethod('getStudentNavigation');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverNavigation($Content = '')
    {
        return (new BlockReceiver($Content))
            ->setIdentifier('ReceiverNavigation');
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverFilter($Content = '')
    {
        return (new BlockReceiver($Content))
            ->setIdentifier('ReceiverFilter');
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverService($Content = '')
    {
        return (new BlockReceiver($Content))
            ->setIdentifier('ReceiverService');
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverResult($Content = '')
    {
        return (new BlockReceiver($Content))
            ->setIdentifier('ReceiverResult');
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModal()
    {
        return (new ModalReceiver('', new Close()))
            ->setIdentifier('ModalReceiver');
    }

    public static function pipelineDelete()
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'removeFieldAll'
        ));
        $Pipeline->appendEmitter($Emitter);
        $Emitter = new ServerEmitter(self::receiverNavigation(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getNewNavigation'
        ));
        $Pipeline->appendEmitter($Emitter);
        // Refresh Filter
        $Emitter = new ServerEmitter(self::receiverFilter(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'buildFilter'
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    public static function pipelinePresetModal()
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getModalPreset'
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    public static function pipelineNewNavigation()
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverNavigation(), self::getEndpoint());

        $tblWorkspaceList = Individual::useService()->getWorkSpaceAll();
        // Default
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getNewNavigation'
        ));
        // Find selected Group
        if ($tblWorkspaceList) {
            $tblWorkspace = current($tblWorkspaceList);
            if ($tblWorkspace->getView() == 'ViewStudent') {
                $Emitter->setGetPayload(array(
                    self::API_TARGET => 'getStudentNavigation'
                ));
            }
        }

        $Pipeline->appendEmitter($Emitter);
//        // Refresh Filter
//        $Emitter = new ServerEmitter(self::receiverFilter(), self::getEndpoint());
//        $Emitter->setGetPayload(array(
//            self::API_TARGET => 'buildFilter'
//        ));
//        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    public static function pipelineAddField($Field, $View, $NavigationTarget)
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'addField'
        ));
        $Emitter->setPostPayload(array(
            'Field' => $Field,
            'View'  => $View
        ));
        $Pipeline->appendEmitter($Emitter);
        $Emitter = new ServerEmitter(self::receiverNavigation(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => $NavigationTarget
        ));
        $Pipeline->appendEmitter($Emitter);
        // Refresh Filter
        $Emitter = new ServerEmitter(self::receiverFilter(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'buildFilter'
        ));
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    public static function pipelineStudentNavigation()
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverNavigation(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getStudentNavigation'
        ));
        $Pipeline->appendEmitter($Emitter);
        // Refresh Filter
        $Emitter = new ServerEmitter(self::receiverFilter(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'buildFilter'
        ));
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    public static function pipelineDisplayFilter()
    {
        $Pipeline = new Pipeline(false);
        $Emitter = new ServerEmitter(self::receiverFilter(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'buildFilter'
        ));
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    public function removeFieldAll()
    {

        Individual::useService()->removeWorkSpaceFieldAll();
    }

    public function getModalPreset()
    {

        $tblPresetList = Individual::useService()->getPresetAll();
        $TableContent = array();

        if ($tblPresetList) {
            array_walk($tblPresetList, function (TblPreset $tblPreset) use (&$TableContent) {
                $Item['Name'] = $tblPreset->getName();
                $Item['FieldCount'] = '';

                $tblPresetSetting = Individual::useService()->getPresetSettingAllByPreset($tblPreset);
                if ($tblPresetSetting) {
                    $Item['FieldCount'] = count($tblPresetSetting);
                }
                $TableContent = array_merge($TableContent, $Item);
            });
        }

        $Content = new Layout(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new TableData($TableContent, null,
                            array(
                                'Name'       => 'Gespeicherte Filterung',
                                'FieldCount' => 'Anzahl Filter'
                            ))
                    )
                )
            )
        );

        return $Content;
    }

    public function getNewNavigation()
    {

        return new Panel('Verfügbar', array(
            new Panel('Auswertung über', array(
                'Schüler'.new PullRight((new Primary('', self::getEndpoint(), new Plus()))->ajaxPipelineOnClick(
                    self::pipelineStudentNavigation()
                )),
//                'Lehrer'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
            ), Panel::PANEL_TYPE_PRIMARY),
        ));
    }

    public function addField($Field, $View)
    {

        $Position = 1;
        $tblWorkSpaceList = Individual::useService()->getWorkSpaceAll();
        if ($tblWorkSpaceList) {
            foreach ($tblWorkSpaceList as $tblWorkSpace) {
                if ($tblWorkSpace->getPosition() >= $Position) {
                    $Position = $tblWorkSpace->getPosition();
                }
            }
            $Position++;
        }

        Individual::useService()->addWorkSpaceField($Field, $View, $Position);
    }

    public function getStudentNavigation()
    {

//        $Test = (new ViewStudent())->getArrayList();

//        Debugger::screenDump($Test);
//
//        return new Panel('Verfügbare Felder', 'haha');

//        return new Code(print_r($FieldList, true));

        // remove every entry that is Choosen
        $WorkSpaceList = array();
        $tblWorkSpaceList = Individual::useService()->getWorkSpaceAll();
        if ($tblWorkSpaceList) {
            foreach ($tblWorkSpaceList as $tblWorkSpace) {
                $WorkSpaceList[] = $tblWorkSpace->getField();
            }
        }

        $View = new ViewStudent();

        $ViewStudentBlockList = array();
        $ConstantList = ViewStudent::getConstants();
        if ($ConstantList) {
            foreach ($ConstantList as $Constant) {
                $Group = $View->getGroupDefinition($Constant);

                if ($Group) {
                    $ViewStudentBlockList[$Group][] = $View->getNameDefinition($View->getNameDefinition($Constant));
                }
            }
//            ksort($ViewStudentBlockList);
        }

        $PanelList = array();
        if ($ViewStudentBlockList) {
            foreach ($ViewStudentBlockList as $Block => $FieldList) {

                $FieldListArray = array();
                if ($FieldList) {
                    foreach ($FieldList as $FieldTblName) {

                        $FieldName = $View->getNameDefinition($FieldTblName);

                        if (!in_array($FieldTblName, $WorkSpaceList)) {
                            $FieldListArray[$FieldTblName] = new PullClear($FieldName.new PullRight((new Primary('',
                                    self::getEndpoint(), new Plus()))
                                    ->ajaxPipelineOnClick(self::pipelineAddField($FieldTblName, 'ViewStudent',
                                        'getStudentNavigation'))));
                        }
                    }
                }

                if (!empty($FieldListArray)) {
                    $PanelList[] = new Panel($Block, $FieldListArray, Panel::PANEL_TYPE_PRIMARY);
                }
            }
        }

        return new Panel('Verfügbare Felder', array(
            (new Danger('Löschen', ApiIndividual::getEndpoint(), new Disable()))->ajaxPipelineOnClick(
                ApiIndividual::pipelineDelete()
            ).(new Primary('Masken', ApiIndividual::getEndpoint(), new Save(), array(),
                'Laden/Speichern von Filtereinstellungen'))->ajaxPipelineOnClick(
                ApiIndividual::pipelinePresetModal()
            ),
//            (new Accordion())->addItem('Schüler Grunddaten',
                new Layout(new LayoutGroup(new LayoutRow(
                    new LayoutColumn(
                        $PanelList
//                        new Listing(
//                            $FieldList
//                        )
                    )
                )))
//                , true)
        ));
    }

    public function buildFilter()
    {

        $tblWorkSpaceList = Individual::useService()->getWorkSpaceAll();
        $FormColumnAll = array();

        $View = new ViewStudent();
        if ($tblWorkSpaceList) {
            foreach ($tblWorkSpaceList as $tblWorkSpace) {
                $FieldName = $View->getNameDefinition($tblWorkSpace->getField());
                $FormColumnAll[$tblWorkSpace->getPosition()] =
                    new FormColumn(new TextField($tblWorkSpace->getField()
                        , '', $FieldName), 2);
            }
            ksort($FormColumnAll);
        }

        $FormRowList = array();
        $FormRowCount = 0;
        $FormRow = null;
        if (!empty($FormColumnAll)) {
            /**
             * @var FormColumn $FormColumn
             */
            foreach ($FormColumnAll as $FormColumn) {
                if ($FormRowCount % 6 == 0) {
                    $FormRow = new FormRow(array());
                    $FormRowList[] = $FormRow;
                }
                $FormRow->addColumn($FormColumn);
                $FormRowCount++;
            }
            $FormRowList[] = new FormRow(new FormColumn((new Primary('Filtern', self::getEndpoint(),
                new Filter()))->setDisabled()));
        }

        if (!empty($FormRowList)) {
            $Form = new Form(
                new FormGroup(
                    $FormRowList
                )
            );
            $Panel = new Panel('Filter', $Form, Panel::PANEL_TYPE_INFO);
        }

        return (isset($Panel) ? $Panel : new InfoMessage('Bitte wählen Sie aus welche Spalten gefilter werden sollen'));
    }
}