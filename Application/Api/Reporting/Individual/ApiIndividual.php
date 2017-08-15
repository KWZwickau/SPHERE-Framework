<?php

namespace SPHERE\Application\Api\Reporting\Individual;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Reporting\Individual\Individual;
use SPHERE\Application\Reporting\Individual\Service\Entity\TblPreset;
use SPHERE\Application\Reporting\Individual\Service\Entity\TblWorkSpace;
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
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Accordion;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Info as InfoMessage;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\System\Database\Binding\AbstractView;
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
        $Dispatcher->registerMethod('getNavigation');
        $Dispatcher->registerMethod('removeField');
        $Dispatcher->registerMethod('moveFieldLeft');
        $Dispatcher->registerMethod('moveFieldRight');
        $Dispatcher->registerMethod('removeFieldAll');
        $Dispatcher->registerMethod('getModalPreset');
        $Dispatcher->registerMethod('addField');
        $Dispatcher->registerMethod('getFilter');

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

    /**
     * @return Pipeline
     */
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
            self::API_TARGET => 'getNavigation'
        ));
        $Pipeline->appendEmitter($Emitter);
        // Refresh Filter
        $Emitter = new ServerEmitter(self::receiverFilter(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getFilter'
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param int|null $WorkSpaceId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteFilterField($WorkSpaceId = null)
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'removeField'
        ));
        $Emitter->setPostPayload(array(
            'WorkSpaceId' => $WorkSpaceId
        ));
        $Pipeline->appendEmitter($Emitter);
        $Emitter = new ServerEmitter(self::receiverNavigation(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getNavigation'
        ));
        $Pipeline->appendEmitter($Emitter);
        // Refresh Filter
        $Emitter = new ServerEmitter(self::receiverFilter(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getFilter'
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param int|null $WorkSpaceId
     * @param string   $direction
     *
     * @return Pipeline
     */
    public static function pipelineMoveFilterField($WorkSpaceId = null, $direction = '')
    {

        $Pipeline = new Pipeline();
        if ($direction == 'left') {
            $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
            $Emitter->setGetPayload(array(
                self::API_TARGET => 'moveFieldLeft'
            ));
            $Emitter->setPostPayload(array(
                'WorkSpaceId' => $WorkSpaceId
            ));
            $Pipeline->appendEmitter($Emitter);
        } elseif ($direction == 'right') {
            $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
            $Emitter->setGetPayload(array(
                self::API_TARGET => 'moveFieldRight'
            ));
            $Emitter->setPostPayload(array(
                'WorkSpaceId' => $WorkSpaceId
            ));
            $Pipeline->appendEmitter($Emitter);
        }

//        // Refresh Filter
//        $Emitter = new ServerEmitter(self::receiverFilter(), self::getEndpoint());
//        $Emitter->setGetPayload(array(
//            self::API_TARGET => 'getFilter'
//        ));
//        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
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

    /**
     * @return Pipeline
     */
    public static function pipelineNavigation()
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverNavigation(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getNavigation'
        ));
        $Pipeline->appendEmitter($Emitter);
        // Refresh Filter
        $Emitter = new ServerEmitter(self::receiverFilter(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getFilter'
        ));
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    /**
     * @param $Field
     * @param $View
     *
     * @return Pipeline
     */
    public static function pipelineAddField($Field, $View)
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
            self::API_TARGET => 'getNavigation'
        ));
        $Pipeline->appendEmitter($Emitter);
        // Refresh Filter
        $Emitter = new ServerEmitter(self::receiverFilter(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getFilter'
        ));
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineDisplayFilter()
    {
        $Pipeline = new Pipeline(false);
        $Emitter = new ServerEmitter(self::receiverFilter(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getFilter'
        ));
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    public function removeFieldAll()
    {

        Individual::useService()->removeWorkSpaceAll();
    }

    /**
     * @param int|null $WorkSpaceId
     */
    public function removeField($WorkSpaceId = null)
    {

        $tblWorkSpace = Individual::useService()->getWorkSpaceById($WorkSpaceId);
        Individual::useService()->removeWorkspace($tblWorkSpace);
    }

    /**
     * @param null $WorkSpaceId
     *
     * @return Pipeline
     */
    public function moveFieldLeft($WorkSpaceId = null)
    {

        $tblWorkSpace = Individual::useService()->getWorkSpaceById($WorkSpaceId);
        if ($tblWorkSpace) {
            $pos = $tblWorkSpace->getPosition();
            $tblWorkSpaceList = Individual::useService()->getWorkSpaceAll();
            /** @var TblWorkSpace|bool $closestWorkSpace */
            $closestWorkSpace = false;
            if ($tblWorkSpaceList) {
                foreach ($tblWorkSpaceList as $WorkSpace) {
                    if ($WorkSpace->getPosition() < $pos) {
                        if ($closestWorkSpace) {
                            if ($closestWorkSpace->getPosition() < $WorkSpace->getPosition()) {
                                $closestWorkSpace = $WorkSpace;
                            }
                        } else {
                            $closestWorkSpace = $WorkSpace;
                        }
                    }
                }
            }
            if ($tblWorkSpace && $closestWorkSpace) {
                $posTo = $closestWorkSpace->getPosition();
                Individual::useService()->changeWorkSpace($tblWorkSpace, $posTo);
                Individual::useService()->changeWorkSpace($closestWorkSpace, $pos);
            }
        }

        return ApiIndividual::pipelineDisplayFilter();
    }

    /**
     * @param null $WorkSpaceId
     *
     * @return Pipeline
     */
    public function moveFieldRight($WorkSpaceId = null)
    {

        $tblWorkSpace = Individual::useService()->getWorkSpaceById($WorkSpaceId);
        if ($tblWorkSpace) {
            $pos = $tblWorkSpace->getPosition();
            $tblWorkSpaceList = Individual::useService()->getWorkSpaceAll();
            /** @var TblWorkSpace|bool $closestWorkSpace */
            $closestWorkSpace = false;
            if ($tblWorkSpaceList) {
                foreach ($tblWorkSpaceList as $WorkSpace) {
                    if ($WorkSpace->getPosition() > $pos) {
                        if ($closestWorkSpace) {
                            if ($closestWorkSpace->getPosition() > $WorkSpace->getPosition()) {
                                $closestWorkSpace = $WorkSpace;
                            }
                        } else {
                            $closestWorkSpace = $WorkSpace;
                        }
                    }
                }
            }
            if ($tblWorkSpace && $closestWorkSpace) {
                $posTo = $closestWorkSpace->getPosition();
                Individual::useService()->changeWorkSpace($tblWorkSpace, $posTo);
                Individual::useService()->changeWorkSpace($closestWorkSpace, $pos);
            }
        }
        return ApiIndividual::pipelineDisplayFilter();
    }

    /**
     * @return Layout
     */
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

    /**
     * @param $Field
     * @param $View
     */
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

    /**
     * @return Panel
     */
    public function getNavigation()
    {

//        $Test = (new ViewStudent())->getArrayList();

//        Debugger::screenDump($Test);
//
//        return new Panel('Verfügbare Felder', 'haha');

//        return new Code(print_r($FieldList, true));

        // remove every entry that is Choosen
        $WorkSpaceList = array();
        $ViewList = array();
        $tblWorkSpaceList = Individual::useService()->getWorkSpaceAll();
        if ($tblWorkSpaceList) {
            foreach ($tblWorkSpaceList as $tblWorkSpace) {
                $WorkSpaceList[] = $tblWorkSpace->getField();
                $ViewList[$tblWorkSpace->getView()] = $tblWorkSpace->getView();
            }
        }

        $AccordionList = array();

        $AccordionList[] = (new Accordion(''))
            ->addItem('Schüler', $this->getPanelList(new ViewStudent(), $WorkSpaceList),
                (isset($ViewList['ViewStudent']) ? true : false));

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
                    $AccordionList
                )
            )))
//                , true)
        ));
    }

    /**
     * @param AbstractView   $View
     * @param TblWorkSpace[] $WorkSpaceList
     *
     * @return string
     */
    private function getPanelList(AbstractView $View, $WorkSpaceList = array())
    {
        $PanelString = '';

        $ViewBlockList = array();
        $ConstantList = ViewStudent::getConstants();
        if ($ConstantList) {
            foreach ($ConstantList as $Constant) {
                $Group = $View->getGroupDefinition($Constant);

                if ($Group) {
                    $ViewBlockList[$Group][] = $View->getNameDefinition($View->getNameDefinition($Constant));
                }
            }
//            ksort($ViewBlockList);
        }
        if ($ViewBlockList) {
            foreach ($ViewBlockList as $Block => $FieldList) {

                $FieldListArray = array();
                if ($FieldList) {
                    foreach ($FieldList as $FieldTblName) {

                        $FieldName = $View->getNameDefinition($FieldTblName);

                        if (!in_array($FieldTblName, $WorkSpaceList)) {
                            $FieldListArray[$FieldTblName] = new PullClear($FieldName.new PullRight((new Primary('',
                                    self::getEndpoint(), new Plus()))
                                    ->ajaxPipelineOnClick(self::pipelineAddField($FieldTblName, 'ViewStudent'))));
                        }
                    }
                }

                if (!empty($FieldListArray)) {
                    $PanelString .= new Panel($Block, $FieldListArray, Panel::PANEL_TYPE_PRIMARY);
                }
            }
        }

        return $PanelString;
    }

    /**
     * @return Panel|InfoMessage
     */
    public function getFilter()
    {

        $tblWorkSpaceList = Individual::useService()->getWorkSpaceAll();
        $FormColumnAll = array();

        $View = new ViewStudent();
        if ($tblWorkSpaceList) {
            foreach ($tblWorkSpaceList as $tblWorkSpace) {
                $FieldName = $View->getNameDefinition($tblWorkSpace->getField());
                $FormColumnAll[$tblWorkSpace->getPosition()] =
                    new FormColumn(new Panel($FieldName, array(
                        new TextField($tblWorkSpace->getField()),
                        new Center( //ToDO ajax für Buttons
                            (new Standard('', ApiIndividual::getEndpoint(), new ChevronLeft(), array(),
                                'Position eins vor'))
                                ->ajaxPipelineOnClick(ApiIndividual::pipelineMoveFilterField($tblWorkSpace->getId(),
                                    'left'))
                            .(new Standard('', ApiIndividual::getEndpoint(), new Remove(), array(), 'Filter entfernen'))
                                ->ajaxPipelineOnClick(ApiIndividual::pipelineDeleteFilterField($tblWorkSpace->getId()))
                            .(new Standard('', ApiIndividual::getEndpoint(), new ChevronRight(), array(),
                                'Position eins hinter'))
                                ->ajaxPipelineOnClick(ApiIndividual::pipelineMoveFilterField($tblWorkSpace->getId(),
                                    'right'))
                        )
                    )), 2);
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