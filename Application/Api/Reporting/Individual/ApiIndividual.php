<?php

namespace SPHERE\Application\Api\Reporting\Individual;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Reporting\Individual\Individual;
use SPHERE\Application\Reporting\Individual\Service\Entity\TblPreset;
use SPHERE\Application\Reporting\Individual\Service\Entity\TblWorkSpace;
use SPHERE\Application\Reporting\Individual\Service\Entity\ViewEducationStudent;
use SPHERE\Application\Reporting\Individual\Service\Entity\ViewStudent;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Accordion;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Link\Structure\LinkGroup;
use SPHERE\Common\Frontend\Message\Repository\Info as InfoMessage;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Muted;
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
        $Dispatcher->registerMethod('changeFilterCount');
        $Dispatcher->registerMethod('removeFieldAll');
        $Dispatcher->registerMethod('getModalPreset');
        $Dispatcher->registerMethod('loadPreset');
        $Dispatcher->registerMethod('deletePreset');
        $Dispatcher->registerMethod('getModalSavePreset');
        $Dispatcher->registerMethod('createPreset');
        $Dispatcher->registerMethod('addField');
        $Dispatcher->registerMethod('getFilter');
        $Dispatcher->registerMethod('getResult');

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
     * @param string $Header
     *
     * @return ModalReceiver
     */
    public static function receiverModal($Header = '')
    {
        return (new ModalReceiver($Header, new Close()))
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
     * @param string   $direction [left,right]
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
     * @param string   $direction [plus,minus]
     *
     * @return Pipeline
     */
    public static function pipelineChangeFilterCount($WorkSpaceId = null, $direction = '')
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'changeFilterCount'
        ));
        $Emitter->setPostPayload(array(
            'WorkSpaceId' => $WorkSpaceId,
            'direction'   => $direction,
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
     * @param string $Info
     *
     * @return Pipeline
     */
    public static function pipelinePresetShowModal($Info = '')
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getModalPreset'
        ));
        $Emitter->setPostPayload(array(
            'Info' => $Info
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param int|null $PresetId
     *
     * @return Pipeline
     */
    public static function pipelineLoadPreset($PresetId = null)
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'loadPreset'
        ));
        $Emitter->setPostPayload(array(
            'PresetId' => $PresetId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param int|null $PresetId
     *
     * @return Pipeline
     */
    public static function pipelineDeletePreset($PresetId = null)
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'deletePreset'
        ));
        $Emitter->setPostPayload(array(
            'PresetId' => $PresetId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Info
     *
     * @return Pipeline
     */
    public static function pipelinePresetSaveModal($Info = '')
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverModal('Als Vorlage Speichern'), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getModalSavePreset'
        ));
        $Emitter->setPostPayload(array(
            'Info' => $Info
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelinePresetSave()
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'createPreset'
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineCloseModal()
    {

        $Pipeline = new Pipeline(false);
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal()))->getEmitter());

        return $Pipeline;
    }

    /**
     * @param bool $isLoadFilter
     *
     * @return Pipeline
     */
    public static function pipelineNavigation($isLoadFilter = true)
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverNavigation(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getNavigation'
        ));
        $Pipeline->appendEmitter($Emitter);
        if ($isLoadFilter) {
            // Refresh Filter
            $Emitter = new ServerEmitter(self::receiverFilter(), self::getEndpoint());
            $Emitter->setGetPayload(array(
                self::API_TARGET => 'getFilter'
            ));
            $Pipeline->appendEmitter($Emitter);
        }
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
    }

    /**
     * @param null $WorkSpaceId
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
    }

    /**
     * @param int|null $WorkSpaceId
     * @param string   $direction
     */
    public function changeFilterCount($WorkSpaceId = null, $direction = '')
    {
        $tblWorkSpace = Individual::useService()->getWorkSpaceById($WorkSpaceId);
        if ($tblWorkSpace) {
            $FieldCount = $tblWorkSpace->getFieldCount();
            if ($direction == 'plus') {
                $FieldCount++;
            } elseif ($direction == 'minus') {
                $FieldCount--;
            }
            if ($tblWorkSpace && $direction) {
                Individual::useService()->changeWorkSpace($tblWorkSpace, null, $FieldCount);
            }
        }
    }

    /**
     * @param string $Info
     *
     * @return Layout
     */
    public function getModalPreset($Info = '')
    {

        $tblPresetList = Individual::useService()->getPresetAll();
        $TableContent = array();

        if ($tblPresetList) {
            array_walk($tblPresetList, function (TblPreset $tblPreset) use (&$TableContent) {
                $Item['Name'] = $tblPreset->getName();
                $Item['EntityCreate'] = $tblPreset->getEntityCreate();
                $Item['FieldCount'] = '';

                $tblPresetSetting = Individual::useService()->getPresetSettingAllByPreset($tblPreset);
                if ($tblPresetSetting) {
                    $Item['FieldCount'] = count($tblPresetSetting);
                }
                $Item['Option'] = (new Standard('', self::getEndpoint(), new Check(), array(), 'Laden der Vorlage'))
                        ->ajaxPipelineOnClick(ApiIndividual::pipelineLoadPreset($tblPreset->getId()))
                    .(new Standard('', self::getEndpoint(), new Remove(), array(), 'Löschen der Vorlage'))
                        ->ajaxPipelineOnClick(ApiIndividual::pipelineDeletePreset($tblPreset->getId()));

                array_push($TableContent, $Item);
            });
        }

        if ($Info != '') {
            $Info = new Warning($Info);
        }

        $Content = new Layout(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new TableData($TableContent, null,
                            array(
                                'Name'         => 'Name der Vorlage',
                                'FieldCount'   => 'Anzahl gewählter Felder',
                                'EntityCreate' => 'Speicherdatum',
                                'Option'       => ''
                            ))
                        .$Info
                    )
                )
            )
        );

        return $Content;
    }

    /**
     * @param null $PresetId
     *
     * @return Pipeline|string
     */
    public function loadPreset($PresetId = null)
    {

        $tblPreset = Individual::useService()->getPresetById($PresetId);
        if ($tblPreset) {
            // destroy existing Workspace
            Individual::useService()->removeWorkSpaceAll();

            $tblPresetSettingList = Individual::useService()->getPresetSettingAllByPreset($tblPreset);
            if ($tblPresetSettingList) {
                foreach ($tblPresetSettingList as $tblPresetSetting) {
                    Individual::useService()->addWorkSpaceField(
                        $tblPresetSetting->getField(),
                        $tblPresetSetting->getView(),
                        $tblPresetSetting->getPosition(),
                        $tblPreset);
                }
            }

//            $Info = 'Laden erfolgreich';
            return ApiIndividual::pipelineNavigation()
                .ApiIndividual::pipelineCloseModal();
        }

        $Info = 'Vorlage nicht gefunden.';
        return ApiIndividual::pipelinePresetShowModal($Info);
    }

    /**
     * @param null $PresetId
     *
     * @return Pipeline|string
     */
    public function deletePreset($PresetId = null)
    {

        $tblPreset = Individual::useService()->getPresetById($PresetId);
        if ($tblPreset) {
            $tblWorkSpaceList = Individual::useService()->getWorkSpaceAll();
            if ($tblWorkSpaceList) {
                foreach ($tblWorkSpaceList as $tblWorkSpace) {
                    if (($tblWorkSpacePreset = $tblWorkSpace->getTblPreset()) && $tblWorkSpacePreset->getId() == $tblPreset->getId()) {
                        // remove foreignKey if exist
                        Individual::useService()->changeWorkSpacePreset($tblWorkSpace, null);
                    }
                }
            }

            Individual::useService()->removePreset($tblPreset);
//            $Info = 'Erfolgreich entfernt';
            return ApiIndividual::pipelinePresetShowModal();
        }

        $Info = 'Vorlage nicht gefunden.';
        return ApiIndividual::pipelinePresetShowModal($Info);
    }

    /**
     * @param string $Info
     *
     * @return Layout
     */
    public function getModalSavePreset($Info = '')
    {

        $tblPresetList = Individual::useService()->getPresetAll();
        $TableContent = array();
        $viewStudent = new ViewStudent();

        if ($tblPresetList) {
            array_walk($tblPresetList, function (TblPreset $tblPreset) use (&$TableContent, $viewStudent) {
                $Item['Name'] = $tblPreset->getName();
                $Item['EntityCreate'] = $tblPreset->getEntityCreate();
                $Item['FieldCount'] = '';

                $tblPresetSettingList = Individual::useService()->getPresetSettingAllByPreset($tblPreset);
                if ($tblPresetSettingList) {
                    $FieldCount = count($tblPresetSettingList);
//                    //Anzeige der Felder als Accordion
//                    $FieldList = array();
//                    foreach($tblPresetSettingList as $tblPresetSetting){
//                        if($tblPresetSetting->getView() == 'ViewStudent'){
//                            $FieldList[] =  $viewStudent->getNameDefinition($tblPresetSetting->getField());
//                        } else {
//                            $FieldList[] =  $tblPresetSetting->getField();
//                        }
//                    }
//                    $Item['FieldCount'] = (new Accordion())
//                        ->addItem($FieldCount.' Felder ',(new Listing($FieldList)));

                    $Item['FieldCount'] = $FieldCount;
                }
                array_push($TableContent, $Item);
            });
        }

        $form = (new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Speichern', array(
                            new TextField('PresetName', 'Name', 'Name der Vorlage')
                        ), Panel::PANEL_TYPE_INFO)
                    ),
                    new FormColumn((new Primary('Speichern', self::getEndpoint(), new Save()))
                        ->ajaxPipelineOnClick(ApiIndividual::pipelinePresetSave())
                    )
                ))
            )
        ))->disableSubmitAction();

        if ($Info == 'Speicherung erfolgreich') {
            $Info = new Success($Info);
        } elseif ($Info != '') {
            $Info = new Warning($Info);
        }

        $Content = new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new TableData($TableContent, new Title('Vorhandene Vorlagen'),
                            array(
                                'Name'         => 'Name der Vorlage',
                                'FieldCount'   => 'Anzahl gewählter Felder',
                                'EntityCreate' => 'Speicherdatum'
                            ))
                    ),
                    new LayoutColumn(
                        $Info
                        .new Well($form)
                    )
                ))
            )
        );

        return $Content;
    }

    /**
     * @param $PresetName
     *
     * @return Pipeline|string
     */
    public function createPreset($PresetName)
    {

        $tblWorkSpaceList = Individual::useService()->getWorkSpaceAll();
        if ($tblWorkSpaceList && $PresetName) {
            $tblPreset = Individual::useService()->createPreset($PresetName);
            foreach ($tblWorkSpaceList as $tblWorkSpace) {
                Individual::useService()->createPresetSetting($tblPreset, $tblWorkSpace);
            }
            $Info = 'Speicherung erfolgreich';
            return ApiIndividual::pipelinePresetSaveModal($Info)
                .ApiIndividual::pipelineCloseModal();
        }

        $Info = 'Speicherung konnte nicht erfolgen bitte überprüfen Sie ihre Eingabe';
        return ApiIndividual::pipelinePresetSaveModal($Info);

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

        // remove every entry that is already chosen
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
                (isset($ViewList['ViewStudent']) ? true : false))
            ->addItem('Schüler - Klassen', $this->getPanelList(new ViewEducationStudent(), $WorkSpaceList),
                (isset($ViewList['ViewEducationStudent']) ? true : false));

        return new Panel('Verfügbare Felder', array(
            (new Primary('Vorlage laden', ApiIndividual::getEndpoint(), new Download(), array(),
                'Laden von Filtervorlagen'))->ajaxPipelineOnClick(
                ApiIndividual::pipelinePresetShowModal()
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
        $ConstantList = $View::getConstants();    //ToDO auslesen der Konstanten
        if ($ConstantList) {
            foreach ($ConstantList as $Constant) {
                $Group = $View->getGroupDefinition($Constant);
                if ($Group) {
                    $ViewBlockList[$Group][] = $Constant;
                }
            }
        }
//        $ConstantList = ViewEducationStudent::getConstants();
//        if ($ConstantList) {
//            foreach ($ConstantList as $Constant) {
//                $Group = $View->getGroupDefinition($Constant);
//                if ($Group) {
//                    $ViewBlockList[$Group][] = $Constant;
//                }
//            }
//        }
        if ($ViewBlockList) {
            foreach ($ViewBlockList as $Block => $FieldList) {

                $FieldListArray = array();
                if ($FieldList) {
                    foreach ($FieldList as $FieldTblName) {

                        $ViewFieldName = $FieldTblName;
                        $FieldName = $View->getNameDefinition($FieldTblName);

                        if (!in_array($FieldTblName, $WorkSpaceList)) {
                            $ViewName = $View->getViewObjectName();
                            $FieldListArray[$FieldTblName] = new PullClear($FieldName.new PullRight((new Primary('',
                                    self::getEndpoint(), new Plus()))
                                    ->ajaxPipelineOnClick(self::pipelineAddField($ViewFieldName, $ViewName))));
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

        $View = new ViewStudent();  //ToDO mehrere Views möglich! (Namensgebung View)
        if ($tblWorkSpaceList) {
            foreach ($tblWorkSpaceList as $tblWorkSpace) {
                $FieldCount = $tblWorkSpace->getFieldCount();
                if ($FieldCount <= 1) {
                    $LinkGroup = (new LinkGroup())
                        ->addLink((new Standard('', ApiIndividual::getEndpoint(), new Plus(), array(), 'mehr Angaben'))
                            ->ajaxPipelineOnClick(ApiIndividual::pipelineChangeFilterCount($tblWorkSpace->getId(),
                                'plus')))
                        ->addLink((new Standard('', ApiIndividual::getEndpoint(), new Remove(), array(),
                            'Filter entfernen'))
                            ->ajaxPipelineOnClick(ApiIndividual::pipelineDeleteFilterField($tblWorkSpace->getId())));
                } else {
                    $LinkGroup = (new LinkGroup())
                        ->addLink((new Standard('', ApiIndividual::getEndpoint(), new Plus(), array(), 'mehr Angaben'))
                            ->ajaxPipelineOnClick(ApiIndividual::pipelineChangeFilterCount($tblWorkSpace->getId(),
                                'plus')))
                        ->addLink((new Standard('', ApiIndividual::getEndpoint(), new Minus(), array(),
                            'weniger Angaben'))
                            ->ajaxPipelineOnClick(ApiIndividual::pipelineChangeFilterCount($tblWorkSpace->getId(),
                                'minus')))
                        ->addLink((new Standard('', ApiIndividual::getEndpoint(), new Remove(), array(),
                            'Filter entfernen'))
                            ->ajaxPipelineOnClick(ApiIndividual::pipelineDeleteFilterField($tblWorkSpace->getId())));
                }

                $FieldName = $View->getNameDefinition($tblWorkSpace->getField());

                $FilterInputList = array();
                for ($i = 1; $i <= $FieldCount; $i++) {
                    if ($View->getDisableDefinition($tblWorkSpace->getField())) {
                        $FilterInputList[] = new Muted(new Center('Informations-Anzeige'));
                        // LinkButton reduzieren
                        $LinkGroup = (new LinkGroup())
                            ->addLink((new Standard('', ApiIndividual::getEndpoint(), new Remove(), array(),
                                'Filter entfernen'))
                                ->ajaxPipelineOnClick(ApiIndividual::pipelineDeleteFilterField($tblWorkSpace->getId())));
                    } else {
                        $FilterInputList[] = new TextField($tblWorkSpace->getField().'['.$i.']', 'Alle');
                    }
                }
                $FilterInputList[] = new Center(
                    (new Standard('', ApiIndividual::getEndpoint(), new ChevronLeft(), array(),
                        'Position eins vor'))
                        ->ajaxPipelineOnClick(ApiIndividual::pipelineMoveFilterField($tblWorkSpace->getId(),
                            'left'))
                    .$LinkGroup
                    .(new Standard('', ApiIndividual::getEndpoint(), new ChevronRight(), array(),
                        'Position eins hinter'))
                        ->ajaxPipelineOnClick(ApiIndividual::pipelineMoveFilterField($tblWorkSpace->getId(),
                            'right'))
                );

                $FormColumnAll[$tblWorkSpace->getPosition()] =
                    new FormColumn(new Panel($FieldName, $FilterInputList), 3);
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
                if ($FormRowCount % 4 == 0) {
                    $FormRow = new FormRow(array());
                    $FormRowList[] = $FormRow;
                }
                $FormRow->addColumn($FormColumn);
                $FormRowCount++;
            }
            $FormRowList[] = new FormRow(new FormColumn(array(
                (new Primary('Filtern', self::getEndpoint(),
                    new Filter()))->ajaxPipelineOnClick(self::pipelineResult())// ->setDisabled()
            ,
                (new Danger('Filter entfernen', ApiIndividual::getEndpoint(), new Disable()))->ajaxPipelineOnClick(
                    ApiIndividual::pipelineDelete())
            ,
                (new Primary('Vorlage speichern', ApiIndividual::getEndpoint(), new Save(), array(),
                    'Speichern als Filtervorlage'))->ajaxPipelineOnClick(
                    ApiIndividual::pipelinePresetSaveModal()
                )
            )));
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

    /**
     * @return Pipeline
     */
    public static function pipelineResult()
    {
        $Pipeline = new Pipeline();
        $Pipeline->setLoadingMessage( 'Daten werden verarbeitet...' );
        $Emitter = new ServerEmitter(self::receiverResult(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getResult'
        ));
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    /**
     * @return string
     */
    public function getResult() {

        $Manager = Individual::useService()->getBinding()->getEntityManager();
        $Builder = $Manager->getQueryBuilder();

        $tblAccount = Account::useService()->getAccountBySession();
        if(!empty($tblAccount)) {
            $tblWorkspaceAll = Individual::useService()->getWorkSpaceAllByAccount($tblAccount);
            if( !empty($tblWorkspaceAll) ) {
                $ViewList = array();
                $ParameterList = array();

                /** @var TblWorkSpace $tblWorkSpace */
                foreach ($tblWorkspaceAll as $Index => $tblWorkSpace) {

                    if( false === strpos( '\\', $tblWorkSpace->getView() )) {
                        $ViewClass = 'SPHERE\Application\Reporting\Individual\Service\Entity\\' . $tblWorkSpace->getView();
                    } else {
                        $ViewClass = $tblWorkSpace->getView();
                    }

                    // Add View to Query (if not exists)
                    if (!in_array($tblWorkSpace->getView(), $ViewList)) {
                        if (empty($ViewList)) {
                            $Builder->from($ViewClass, $tblWorkSpace->getView());
                        } else {
                            $Builder->join($ViewClass, $tblWorkSpace->getView(), Join::WITH,
                                current( $ViewList ).'.TblPerson_Id = '.$tblWorkSpace->getView().'.TblPerson_Id'
                            );
                        }
                        $ViewList[] = $tblWorkSpace->getView();
                    }

                    // Add Field to Select
                    /** @var AbstractView $ViewClass */
                    $ViewClass = new $ViewClass();

                    $Builder->addSelect($tblWorkSpace->getView() . '.' . $tblWorkSpace->getField()
                        . ' AS ' . $ViewClass->getNameDefinition($tblWorkSpace->getField())
                    );

                    // Add Condition to Parameter (if exists and is not empty)
                    $Filter = $this->getGlobal()->POST;
                    /** @var null|Orx $OrExp */
                    $OrExp = null;
                    if (isset($Filter[$tblWorkSpace->getField()]) && count($Filter[$tblWorkSpace->getField()]) > 1) {
                        // Multiple Values
                        foreach ($Filter[$tblWorkSpace->getField()] as $Count => $Value) {
                            // If User Input exists
                            if (!empty($Value)) {
                                $Parameter = ':Filter' . $Index . 'Value' . $Count;
                                if (!$OrExp) {
                                    $OrExp = $Builder->expr()->orX(
                                        $Builder->expr()->like($tblWorkSpace->getView() . '.' . $tblWorkSpace->getField(),
                                            $Parameter)
                                    );
                                } else {
                                    $OrExp->add(
                                        $Builder->expr()->like($tblWorkSpace->getView() . '.' . $tblWorkSpace->getField(),
                                            $Parameter)
                                    );
                                }
                                $ParameterList[$Parameter] = $Value;
                            }
                        }
                        // Add AND Condition to Where (if filter is set)
                        if ($OrExp) {
                            $Builder->andWhere($OrExp);
                        }
                    } else {
                        if (isset($Filter[$tblWorkSpace->getField()]) && count($Filter[$tblWorkSpace->getField()]) == 1) {
                            // Single Value
                            foreach ($Filter[$tblWorkSpace->getField()] as $Count => $Value) {
                                // If User Input exists
                                if (!empty($Value)) {
                                    $Parameter = ':Filter' . $Index . 'Value' . $Count;
                                    // Add AND Condition to Where (if filter is set)
                                    $Builder->andWhere(
                                        $Builder->expr()->like($tblWorkSpace->getView() . '.' . $tblWorkSpace->getField(),
                                            $Parameter)
                                    );
                                    $ParameterList[$Parameter] = $Value;
                                }
                            }
                        }
                    }
                }

                // Bind Parameter to Query
                foreach ($ParameterList as $Parameter => $Value) {
                    $Builder->setParameter((string)$Parameter, '%' . $Value . '%');
                }

                $Query = $Builder->getQuery();
                $Query->setMaxResults(1000);

                return ''
                .new Warning($Query->getDQL())
                .new Info($Query->getSQL())
                .new TableData($Query->getResult())
                    ;
            } else {
                return ':(';
            }
        }
        return ':(';
    }
}
