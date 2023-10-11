<?php

namespace SPHERE\Application\Api\Education\ClassRegister;

use DateTime;
use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable;
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

class ApiTimetable extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('loadTimetable');
        $Dispatcher->registerMethod('openCreateTimetableModal');
        $Dispatcher->registerMethod('saveCreateTimetableModal');
        $Dispatcher->registerMethod('openEditTimetableModal');
        $Dispatcher->registerMethod('saveEditTimetableModal');
        $Dispatcher->registerMethod('openDeleteTimetableModal');
        $Dispatcher->registerMethod('saveDeleteTimetableModal');

        $Dispatcher->registerMethod('loadTimetableContent');

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
    public static function pipelineLoadTimetable(): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Timetable'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadTimetable',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return string
     */
    public function loadTimetable() : string
    {
        return Timetable::useFrontend()->loadTimetable();
    }

    /**
     * @return Pipeline
     */
    public static function pipelineOpenCreateTimetableModal(): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateTimetableModal',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return string
     */
    public function openCreateTimetableModal(): string
    {
        return $this->getTimetableModal(Timetable::useFrontend()->formTimetable());
    }

    /**
     * @param $form
     * @param null $TimetableId
     *
     * @return string
     */
    private function getTimetableModal($form, $TimetableId = null): string
    {
        if ($TimetableId) {
            $title = new Title(new Edit() . ' Stundenplan bearbeiten');
        } else {
            $title = new Title(new Plus() . ' Stundenplan hinzufügen');
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
    public static function pipelineCreateTimetableSave(): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateTimetableModal'
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $Data
     *
     * @return string
     */
    public function saveCreateTimetableModal($Data = null): string
    {
        if (($form = Timetable::useService()->checkFormTimetable($Data))) {
            // display Errors on form
            return $this->getTimetableModal($form);
        }

        if (Timetable::useService()->createTimetable($Data['Name'], $Data['Description'], new DateTime($Data['DateFrom']), new DateTime($Data['DateTo']))) {
            return new Success('Der Stundenplan wurde erfolgreich gespeichert.')
                . self::pipelineLoadTimetable()
                . self::pipelineClose();
        } else {
            return new Danger('Der Stundenplan konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $TimetableId
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditTimetableModal($TimetableId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditTimetableModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'TimetableId' => $TimetableId,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $TimetableId
     * 
     * @return Danger|string
     */
    public function openEditTimetableModal($TimetableId)
    {
        if (!($tblTimetable = Timetable::useService()->getTimetableById($TimetableId))) {
            return new Danger('Der Stundenplan wurde nicht gefunden', new Exclamation());
        }

        return $this->getTimetableModal(
            Timetable::useFrontend()->formTimetable($TimetableId, true),
            $TimetableId
        );
    }

    /**
     * @param $TimetableId
     *
     * @return Pipeline
     */
    public static function pipelineEditTimetableSave($TimetableId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditTimetableModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'TimetableId' => $TimetableId,
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $TimetableId
     * @param $Data
     *
     * @return Danger|string
     */
    public function saveEditTimetableModal($TimetableId, $Data)
    {
        if (!($tblTimetable = Timetable::useService()->getTimetableById($TimetableId))) {
            return new Danger('Der Stundenplan wurde nicht gefunden', new Exclamation());
        }

        if (($form = Timetable::useService()->checkFormTimetable($Data, $tblTimetable))) {
            // display Errors on form
            return $this->getTimetableModal($form, $TimetableId);
        }

        if (Timetable::useService()->updateTimetable($tblTimetable, $Data['Name'], $Data['Description'], new DateTime($Data['DateFrom']), new DateTime($Data['DateTo']))) {
            return new Success('Der Stundenplan wurde erfolgreich gespeichert.')
                . self::pipelineLoadTimetable()
                . self::pipelineClose();
        } else {
            return new Danger('Der Stundenplan konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $TimetableId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteTimetableModal($TimetableId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDeleteTimetableModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'TimetableId' => $TimetableId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    public function openDeleteTimetableModal($TimetableId)
    {
        if (!($tblTimetable = Timetable::useService()->getTimetableById($TimetableId))) {
            return new Danger('Der Stundenplan wurde nicht gefunden', new Exclamation());
        }

        return new Title(new Remove() . ' Stundenplan löschen')
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(
                                new Question() . ' Diesen Stundenplan wirklich löschen?',
                                array(
                                    $tblTimetable->getName(),
                                    $tblTimetable->getDescription(),
                                    $tblTimetable->getDateFrom() . ' - ' . $tblTimetable->getDateTo()
                                ),
                                Panel::PANEL_TYPE_DANGER
                            )
                            . (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteTimetableSave($TimetableId))
                            . (new Standard('Nein', self::getEndpoint(), new Remove()))
                                ->ajaxPipelineOnClick(self::pipelineClose())
                        )
                    )
                )
            );
    }

    /**
     * @param $TimetableId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteTimetableSave($TimetableId): Pipeline
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveDeleteTimetableModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'TimetableId' => $TimetableId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $TimetableId
     *
     * @return Danger|string
     */
    public function saveDeleteTimetableModal($TimetableId)
    {
        if (!($tblTimetable = Timetable::useService()->getTimetableById($TimetableId))) {
            return new Danger('Der Stundenplan wurde nicht gefunden', new Exclamation());
        }

        if (Timetable::useService()->removeTimetable($tblTimetable)) {
            return new Success('Der Stundenplan wurde erfolgreich gelöscht.')
                . self::pipelineLoadTimetable()
                . self::pipelineClose();
        } else {
            return new Danger('Der Stundenplan konnte nicht gelöscht werden.') . self::pipelineClose();
        }
    }

    /**
     * @param string|null $TimetableId
     * @param string|null $DivisionCourseId
     * @param string|null $Day
     *
     * @return Pipeline
     */
    public static function pipelineLoadTimetableContent(string $TimetableId = null, string $DivisionCourseId = null, string $Day = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'TimetableContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadTimetableContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'TimetableId' => $TimetableId,
            'Day' => $Day
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string|null $TimetableId
     * @param string|null $DivisionCourseId
     * @param string|null $Day
     *
     * @return string
     */
    public function loadTimetableContent(string $TimetableId = null, string $DivisionCourseId = null, string $Day = null) : string
    {
        return Timetable::useFrontend()->loadTimetableContent($TimetableId, $DivisionCourseId, $Day);
    }
}