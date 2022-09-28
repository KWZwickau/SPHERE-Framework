<?php

namespace SPHERE\Application\Api\Education\DivisionCourse;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Frontend;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\System\Extension\Extension;

class ApiTeacherLectureship  extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('loadTeacherLectureshipContent');
        $Dispatcher->registerMethod('loadDivisionCoursesSelectBox');
        $Dispatcher->registerMethod('openCreateTeacherLectureshipModal');
        $Dispatcher->registerMethod('saveCreateTeacherLectureshipModal');
        $Dispatcher->registerMethod('openEditTeacherLectureshipModal');
        $Dispatcher->registerMethod('saveEditTeacherLectureshipModal');

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
    public static function pipelineClose(): Pipeline
    {
        $Pipeline = new Pipeline();
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal()))->getEmitter());

        return $Pipeline;
    }

    /**
     * @param null $Filter
     *
     * @return Pipeline
     */
    public static function pipelineLoadTeacherLectureshipContent($Filter = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'TeacherLectureshipContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadTeacherLectureshipContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'Filter' => $Filter
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $Filter
     *
     * @return string
     */
    public function loadTeacherLectureshipContent($Filter = null): string
    {
        return DivisionCourse::useFrontend()->loadTeacherLectureshipTable($Filter);
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoadDivisionCoursesSelectBox(): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'DivisionCoursesSelectBox'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadDivisionCoursesSelectBox',
        ));
//        $ModalEmitter->setPostPayload(array(
//            'Data' => $Data,
//        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $Data
     *
     * @return SelectBox|null
     */
    public function loadDivisionCoursesSelectBox($Data = null): ?SelectBox
    {
        return (new Frontend())->loadDivisionCoursesSelectBox($Data);
    }

    /**
     * @param $Filter
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateTeacherLectureshipModal($Filter = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateTeacherLectureshipModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'Filter' => $Filter
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $Filter
     *
     * @return string
     */
    public function openCreateTeacherLectureshipModal($Filter = null): string
    {
        return $this->getTeacherLectureshipModal(DivisionCourse::useFrontend()->formTeacherLectureship(null, $Filter, true));
    }

    /**
     * @param $form
     * @param string|null $TeacherLectureshipId
     *
     * @return string
     */
    private function getTeacherLectureshipModal($form, string $TeacherLectureshipId = null): string
    {
        if ($TeacherLectureshipId) {
            $title = new Title(new Edit() . ' Lehrauftrag bearbeiten');
        } else {
            $title = new Title(new Plus() . ' Lehrauftrag hinzufügen');
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
     * @param null $Filter
     *
     * @return Pipeline
     */
    public static function pipelineCreateTeacherLectureshipSave($Filter = null): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateTeacherLectureshipModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'Filter' => $Filter
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $Filter
     * @param array|null $Data
     *
     * @return string
     */
    public function saveCreateTeacherLectureshipModal($Filter = null, array $Data = null): string
    {
        if (($form = DivisionCourse::useService()->checkFormTeacherLectureship($Filter, $Data))) {
            // display Errors on form
            return $this->getTeacherLectureshipModal($form);
        }

        if (DivisionCourse::useService()->createTeacherLectureship($Data)) {
            return new Success('Lehrauftrag wurde erfolgreich gespeichert.')
                . self::pipelineLoadTeacherLectureshipContent($Filter)
                . self::pipelineClose();
        } else {
            return new Danger('Lehrauftrag konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $TeacherLectureshipId
     * @param $Filter
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditTeacherLectureshipModal($TeacherLectureshipId, $Filter = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditTeacherLectureshipModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'TeacherLectureshipId' => $TeacherLectureshipId,
            'Filter' => $Filter
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $TeacherLectureshipId
     * @param null $Filter
     *
     * @return string
     */
    public function openEditTeacherLectureshipModal($TeacherLectureshipId, $Filter = null)
    {
        if (!($tblTeacherLectureship = DivisionCourse::useService()->getTeacherLectureshipById($TeacherLectureshipId))) {
            return new Danger('Der Lehrauftrag wurde nicht gefunden', new Exclamation());
        }

        return $this->getTeacherLectureshipModal(DivisionCourse::useFrontend()->formTeacherLectureship($TeacherLectureshipId, $Filter, true), $TeacherLectureshipId);
    }

    /**
     * @param $TeacherLectureshipId
     * @param null $Filter
     *
     * @return Pipeline
     */
    public static function pipelineEditTeacherLectureshipSave($TeacherLectureshipId, $Filter = null): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditTeacherLectureshipModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'TeacherLectureshipId' => $TeacherLectureshipId,
            'Filter' => $Filter
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $TeacherLectureshipId
     * @param $Filter
     * @param $Data
     *
     * @return Danger|string
     */
    public function saveEditTeacherLectureshipModal($TeacherLectureshipId, $Filter, $Data)
    {
        if (!($tblTeacherLectureship = DivisionCourse::useService()->getTeacherLectureshipById($TeacherLectureshipId))) {
            return new Danger('Der Lehrauftrag wurde nicht gefunden', new Exclamation());
        }

        if (($form = DivisionCourse::useService()->checkFormTeacherLectureship($Filter, $Data, $tblTeacherLectureship))) {
            // display Errors on form
            return $this->getTeacherLectureshipModal($form, $TeacherLectureshipId);
        }

        if (DivisionCourse::useService()->updateTeacherLectureship($tblTeacherLectureship, $Data)) {
            return new Success('Der Lehrauftrag wurde erfolgreich gespeichert.')
                . self::pipelineLoadTeacherLectureshipContent($Filter)
                . self::pipelineClose();
        } else {
            return new Danger('Der Lehrauftrag konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }
}