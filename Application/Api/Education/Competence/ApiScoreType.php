<?php

namespace SPHERE\Application\Api\Education\Competence;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Competence\ScoreType\ScoreType;
use SPHERE\Application\Education\Competence\ScoreType\Service\Entity\TblScoreType;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\System\Extension\Extension;

class ApiScoreType extends Extension implements IApiInterface
{
    use ApiTrait;

    /**
     * @param $Method
     *
     * @return string
     * @noinspection PhpMissingParamTypeInspection
     */
    public function exportApi($Method = ''): string
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('loadScoreTypeTable');
        $Dispatcher->registerMethod('loadScoreTypeItemContent');

        $Dispatcher->registerMethod('loadEditScoreTypeContent');
        $Dispatcher->registerMethod('saveEditScoreType');

        $Dispatcher->registerMethod('openDeleteScoreTypeModal');
        $Dispatcher->registerMethod('saveDeleteScoreTypeModal');

        $Dispatcher->registerMethod('openConversionScoreTypeModal');
        $Dispatcher->registerMethod('saveConversionScoreTypeModal');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverBlock(string $Content = '', string $Identifier = ''): BlockReceiver
    {
        return (new BlockReceiver($Content))->setIdentifier($Identifier);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModal(): ModalReceiver
    {
        return (new ModalReceiver(null, new Close()))->setIdentifier('ModalReceiver');
    }

    /**
     * @return Pipeline
     */
    public static function pipelineClose(): Pipeline
    {
        $Pipeline = new Pipeline();
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal()))->getEmitter());

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoadScoreTypeTable(): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'ScoreTypeTable'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadScoreTypeTable',
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return string
     * @noinspection PhpUnused
     */
    public function loadScoreTypeTable(): string
    {
        return ScoreType::useFrontend()->loadScoreTypeTable();
    }

    /**
     * @param null $ScoreTypeId
     * @param null $Action
     * @param null $ActionId
     * @param null $Data
     *
     * @return Pipeline
     */
    public static function pipelineLoadEditScoreTypeContent($ScoreTypeId = null, $Action = null, $ActionId = null, $Data = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'EditScoreTypeContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadEditScoreTypeContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'ScoreTypeId' => $ScoreTypeId,
            'Action' => $Action,
            'ActionId' => $ActionId,
            'Data' => $Data
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $ScoreTypeId
     * @param null $Action
     * @param null $ActionId
     * @param null $Data
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function loadEditScoreTypeContent($ScoreTypeId = null, $Action = null, $ActionId = null, $Data = null): string
    {
        return ScoreType::useFrontend()->loadEditScoreTypeContent(false, $ScoreTypeId, $Action, $ActionId, $Data);
    }

    /**
     * @param $ScoreTypeId
     * @param $Ranking
     * @param $Data
     *
     * @return Pipeline
     */
    public static function pipelineLoadScoreTypeItemContent($ScoreTypeId, $Ranking, $Data): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'ScoreTypeItem_' . $Ranking), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadScoreTypeItemContent',
        ));

        $ModalEmitter->setPostPayload(array(
            'ScoreTypeId' => $ScoreTypeId,
            'Ranking' => $Ranking,
            'Data' => $Data
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $ScoreTypeId
     * @param $Ranking
     * @param $Data
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function loadScoreTypeItemContent($ScoreTypeId, $Ranking, $Data): string
    {
        return ScoreType::useFrontend()->loadScoreTypeItemContent($ScoreTypeId, $Ranking, true, $Data);
    }

    /**
     * @param null $ScoreTypeId
     * @param null $Data
     *
     * @return Pipeline
     */
    public static function pipelineSaveEditScoreType($ScoreTypeId = null, $Data = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'EditScoreTypeContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditScoreType',
        ));
        $ModalEmitter->setPostPayload(array(
            'ScoreTypeId' => $ScoreTypeId,
            'Data' => $Data
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $ScoreTypeId
     * @param null $Data
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function saveEditScoreType($ScoreTypeId = null, $Data = null): string
    {
        $tblScoreType = null;
        if ($ScoreTypeId) {
            $tblScoreType = ScoreType::useService()->getScoreTypeById($ScoreTypeId);
        }

        return ScoreType::useService()->updateScoreType($Data, $tblScoreType);
    }

    /**
     * @param $ScoreTypeId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteScoreTypeModal($ScoreTypeId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDeleteScoreTypeModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'ScoreTypeId' => $ScoreTypeId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $ScoreTypeId
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function openDeleteScoreTypeModal($ScoreTypeId): string
    {
        if (!($tblScoreType = ScoreType::useService()->getScoreTypeById($ScoreTypeId))) {
            return new Danger('Das Bewertungssystem wurde nicht gefunden', new Exclamation());
        }

        return new Title(new Remove() . ' Bewertungssystem löschen')
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(
                                new Question() . ' Dieses Bewertungssystem wirklich löschen?',
                                array(
                                    'Name: ' . new Bold($tblScoreType->getName()),
                                    'Beschreibung: ' . $tblScoreType->getDescription(),
                                    'Bewertungen: ' . $tblScoreType->getDisplayNames()
                                ),
                                Panel::PANEL_TYPE_DANGER
                            )
                            . (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteScoreTypeSave($ScoreTypeId))
                            . (new Standard('Nein', self::getEndpoint(), new Remove()))
                                ->ajaxPipelineOnClick(self::pipelineClose())
                        )
                    )
                )
            );
    }

    /**
     * @param $ScoreTypeId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteScoreTypeSave($ScoreTypeId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveDeleteScoreTypeModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'ScoreTypeId' => $ScoreTypeId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $ScoreTypeId
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function saveDeleteScoreTypeModal($ScoreTypeId): string
    {
        if (!($tblScoreType = ScoreType::useService()->getScoreTypeById($ScoreTypeId))) {
            return new Danger('Der Bewertungssystem wurde nicht gefunden', new Exclamation());
        }

        if (ScoreType::useService()->destroyScoreType($tblScoreType)) {
            return new Success('Der Bewertungssystem wurde erfolgreich gelöscht.')
                . self::pipelineLoadScoreTypeTable()
                . self::pipelineClose();
        } else {
            return new Danger('Der Bewertungssystem konnte nicht gelöscht werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $ScoreTypeId
     *
     * @return Pipeline
     */
    public static function pipelineOpenConversionScoreTypeModal($ScoreTypeId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openConversionScoreTypeModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'ScoreTypeId' => $ScoreTypeId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $ScoreTypeId
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function openConversionScoreTypeModal($ScoreTypeId): string
    {
        if (!($tblScoreType = ScoreType::useService()->getScoreTypeById($ScoreTypeId))) {
            return new Danger('Das Bewertungssystem wurde nicht gefunden', new Exclamation());
        }

        return $this->getConversionScoreTypeModal(ScoreType::useFrontend()->formScoreTypeConversion(true, $tblScoreType), $tblScoreType);
    }

    /**
     * @param $form
     * @param TblScoreType $tblScoreType
     *
     * @return string
     */
    private function getConversionScoreTypeModal($form, TblScoreType $tblScoreType): string
    {
        return new Title('Umrechnung Bewertungssystem in Zensuren für Notenzeugnisse')
            . new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    new Panel('Name', $tblScoreType->getName(), Panel::PANEL_TYPE_INFO)
                    , 6),
                new LayoutColumn(
                    new Panel('Beschreibung', $tblScoreType->getDescription() ?: '&nbsp;', Panel::PANEL_TYPE_INFO)
                    , 6)
            ))))
            . new Well($form);
    }

    /**
     * @param $ScoreTypeId
     *
     * @return Pipeline
     */
    public static function pipelineConversionScoreTypeSave($ScoreTypeId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveConversionScoreTypeModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'ScoreTypeId' => $ScoreTypeId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $ScoreTypeId
     * @param null $Data
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function saveConversionScoreTypeModal($ScoreTypeId, $Data = null): string
    {
        if (!($tblScoreType = ScoreType::useService()->getScoreTypeById($ScoreTypeId))) {
            return new Danger('Das Bewertungssystem wurde nicht gefunden', new Exclamation());
        }

        if (($form = ScoreType::useService()->checkFormConversionScoreType($Data, $tblScoreType))) {
            // display Errors on form
            return $this->getConversionScoreTypeModal($form, $tblScoreType);
        }

        if (ScoreType::useService()->updateScoreTypeConversions($tblScoreType, $Data)) {
            return new Success('Die Daten wurde erfolgreich gespeichert.')
                . self::pipelineLoadScoreTypeTable()
                . self::pipelineClose();
        } else {
            return new Danger('Die Daten konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }
}