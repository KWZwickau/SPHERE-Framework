<?php

namespace SPHERE\Application\Api\Education\ClassRegister;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\ClassRegister\ScheduleTime\ScheduleTime;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
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
use SPHERE\System\Extension\Extension;

class ApiScheduleTime extends Extension implements IApiInterface
{
    use ApiTrait;

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = ''): string
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('loadScheduleTime');
        $Dispatcher->registerMethod('openCreateScheduleTimeModal');
        $Dispatcher->registerMethod('saveCreateScheduleTimeModal');
        $Dispatcher->registerMethod('openEditScheduleTimeModal');
        $Dispatcher->registerMethod('saveEditScheduleTimeModal');
        $Dispatcher->registerMethod('openDeleteScheduleTimeModal');
        $Dispatcher->registerMethod('saveDeleteScheduleTimeModal');

        return $Dispatcher->callMethod($Method);
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
     * @return Pipeline
     */
    public static function pipelineLoadScheduleTime(): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'ScheduleTime'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadScheduleTime',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return string
     *
     * @noinspection PhpUnused
     */
    public function loadScheduleTime() : string
    {
        return ScheduleTime::useFrontend()->loadScheduleTime();
    }

    /**
     * @return Pipeline
     */
    public static function pipelineOpenCreateScheduleTimeModal(): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateScheduleTimeModal',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return string
     *
     * @noinspection PhpUnused
     */
    public function openCreateScheduleTimeModal(): string
    {
        return $this->getScheduleTimeModal(ScheduleTime::useFrontend()->formScheduleTime(null, true));
    }

    /**
     * @param $form
     * @param null $ScheduleTimeId
     *
     * @return string
     */
    private function getScheduleTimeModal($form, $ScheduleTimeId = null): string
    {
        if ($ScheduleTimeId) {
            $title = new Title(new Edit() . ' Zeitplan bearbeiten');
        } else {
            $title = new Title(new Plus() . ' Zeitplan hinzufügen');
        }

        return $title
            . new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Well(
                                    $form
                                )
                            )
                        )
                    ))
            );
    }

    /**
     * @return Pipeline
     */
    public static function pipelineCreateScheduleTimeSave(): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateScheduleTimeModal'
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $Data
     *
     * @return string
     *
     * @noinspection PhpUnused
     */
    public function saveCreateScheduleTimeModal($Data = null): string
    {
        if (($form = ScheduleTime::useService()->checkFormScheduleTime($Data))) {
            // display Errors on form
            return $this->getScheduleTimeModal($form);
        }

        ScheduleTime::useService()->createScheduleTime($Data);

        return new Success('Der Zeitplan wurde erfolgreich gespeichert.')
            . self::pipelineLoadScheduleTime()
            . self::pipelineClose();

    }

    /**
     * @param $ScheduleTimeId
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditScheduleTimeModal($ScheduleTimeId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditScheduleTimeModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'ScheduleTimeId' => $ScheduleTimeId,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $ScheduleTimeId
     *
     * @return string
     *
     * @noinspection PhpUnused
     */
    public function openEditScheduleTimeModal($ScheduleTimeId): string
    {
        if (!ScheduleTime::useService()->getScheduleTimeById($ScheduleTimeId)) {
            return new Danger('Der Zeitplan wurde nicht gefunden', new Exclamation());
        }

        return $this->getScheduleTimeModal(
            ScheduleTime::useFrontend()->formScheduleTime($ScheduleTimeId, true),
            $ScheduleTimeId
        );
    }

    /**
     * @param $ScheduleTimeId
     *
     * @return Pipeline
     */
    public static function pipelineEditScheduleTimeSave($ScheduleTimeId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditScheduleTimeModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'ScheduleTimeId' => $ScheduleTimeId,
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $ScheduleTimeId
     * @param $Data
     *
     * @return string
     *
     * @noinspection PhpUnused
     */
    public function saveEditScheduleTimeModal($ScheduleTimeId, $Data): string
    {
        if (!($tblScheduleTime = ScheduleTime::useService()->getScheduleTimeById($ScheduleTimeId))) {
            return new Danger('Der Zeitplan wurde nicht gefunden', new Exclamation());
        }

        if (($form = ScheduleTime::useService()->checkFormScheduleTime($Data, $tblScheduleTime))) {
            // display Errors on form
            return $this->getScheduleTimeModal($form, $ScheduleTimeId);
        }

        ScheduleTime::useService()->updateScheduleTime($tblScheduleTime, $Data);

        return new Success('Der Zeitplan wurde erfolgreich gespeichert.')
            . self::pipelineLoadScheduleTime()
            . self::pipelineClose();
    }

    /**
     * @param $ScheduleTimeId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteScheduleTimeModal($ScheduleTimeId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDeleteScheduleTimeModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'ScheduleTimeId' => $ScheduleTimeId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $ScheduleTimeId
     *
     * @return string
     *
     * @noinspection PhpUnused
     */
    public function openDeleteScheduleTimeModal($ScheduleTimeId): string
    {
        if (!($tblScheduleTime = ScheduleTime::useService()->getScheduleTimeById($ScheduleTimeId))) {
            return new Danger('Der Zeitplan wurde nicht gefunden', new Exclamation());
        }

        return new Title(new Remove() . ' Zeitplan löschen')
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(
                                new Question() . ' Diesen Zeitplan wirklich löschen?',
                                array(
                                    $tblScheduleTime->getName(),
                                    $tblScheduleTime->getDisplaySchoolTypes()
                                ),
                                Panel::PANEL_TYPE_DANGER
                            )
                            . (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteScheduleTimeSave($ScheduleTimeId))
                            . (new Standard('Nein', self::getEndpoint(), new Remove()))
                                ->ajaxPipelineOnClick(self::pipelineClose())
                        )
                    )
                )
            );
    }

    /**
     * @param $ScheduleTimeId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteScheduleTimeSave($ScheduleTimeId): Pipeline
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveDeleteScheduleTimeModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'ScheduleTimeId' => $ScheduleTimeId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $ScheduleTimeId
     *
     * @return string
     *
     * @noinspection PhpUnused
     */
    public function saveDeleteScheduleTimeModal($ScheduleTimeId): string
    {
        if (!($tblScheduleTime = ScheduleTime::useService()->getScheduleTimeById($ScheduleTimeId))) {
            return new Danger('Der Zeitplan wurde nicht gefunden', new Exclamation());
        }

        ScheduleTime::useService()->deleteScheduleTime($tblScheduleTime);

        return new Success('Der Zeitplan wurde erfolgreich gelöscht.')
            . self::pipelineLoadScheduleTime()
            . self::pipelineClose();
    }
}