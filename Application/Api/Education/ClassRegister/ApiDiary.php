<?php

namespace SPHERE\Application\Api\Education\ClassRegister;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\ClassRegister\Diary\Diary;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
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
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiDiary
 *
 * @package SPHERE\Application\Api\Education\ClassRegister
 */
class ApiDiary extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('loadDiaryContent');

        $Dispatcher->registerMethod('openCreateDiaryModal');
        $Dispatcher->registerMethod('saveCreateDiaryModal');

        $Dispatcher->registerMethod('openEditDiaryModal');
        $Dispatcher->registerMethod('saveEditDiaryModal');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModal()
    {

        return (new ModalReceiver(null, new Close()))->setIdentifier('ModalReceiver');
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverBlock($Content = '', $Identifier = '')
    {

        return (new BlockReceiver($Content))->setIdentifier($Identifier);
    }

    /**
     * @return Pipeline
     */
    public static function pipelineClose()
    {
        $Pipeline = new Pipeline();
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal()))->getEmitter());

        return $Pipeline;
    }

    /**
     * @param int $DivisionId
     *
     * @return Pipeline
     */
    public static function pipelineLoadDiaryContent($DivisionId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'DiaryContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadDiaryContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionId' => $DivisionId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $DivisionId
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateDiaryModal($DivisionId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateDiaryModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionId' => $DivisionId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionId
     *
     * @return Pipeline
     */
    public static function pipelineCreateDiarySave($DivisionId)
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateDiaryModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionId' => $DivisionId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $DiaryId
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditDiaryModal($DiaryId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditDiaryModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'DiaryId' => $DiaryId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DiaryId
     *
     * @return Pipeline
     */
    public static function pipelineEditDiarySave($DiaryId)
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditDiaryModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'DiaryId' => $DiaryId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionId
     *
     * @return Danger|TableData
     */
    public function loadDiaryContent($DivisionId)
    {
        if (!($tblDivision = Division::useService()->getDivisionById($DivisionId))) {
            return new Danger('Die Klasse wurde nicht gefunden', new Exclamation());
        }

        return Diary::useFrontend()->loadDiaryTable($tblDivision);
    }

    /**
     * @param $DivisionId
     *
     * @return Danger|string
     */
    public function openCreateDiaryModal($DivisionId)
    {
        if (!($tblDivision = Division::useService()->getDivisionById($DivisionId))) {
            return new Danger('Die Klasse wurde nicht gefunden', new Exclamation());
        }

        return $this->getDiaryModal(Diary::useFrontend()->formDiary($tblDivision));
    }

    /**
     * @param $form
     * @param null $DiaryId
     *
     * @return string
     */
    private function getDiaryModal($form, $DiaryId = null)
    {
        if ($DiaryId) {
            $title = new Title(new Edit() . ' Eintrag bearbeiten');
        } else {
            $title = new Title(new Plus() . ' Eintrag hinzufügen');
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
     * @param $DivisionId
     * @param $Data
     *
     * @return Danger|string
     */
    public function saveCreateDiaryModal($DivisionId, $Data)
    {
        if (!($tblDivision = Division::useService()->getDivisionById($DivisionId))) {
            return new Danger('Die Klasse wurde nicht gefunden', new Exclamation());
        }

        if (($form = Diary::useService()->checkFormDiary($tblDivision, $Data))) {
            // display Errors on form
            return $this->getDiaryModal($form);
        }

        if (Diary::useService()->createDiary($tblDivision, $Data)) {
            return new Success('Der Eintrag wurde erfolgreich gespeichert.')
                . self::pipelineLoadDiaryContent($DivisionId)
                . self::pipelineClose();
        } else {
            return new Danger('Der Eintrag konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $DiaryId
     *
     * @return string
     */
    public function openEditDiaryModal($DiaryId)
    {
        if (!($tblDiary = Diary::useService()->getDiaryById($DiaryId))) {
            return new Danger('Der Eintrag wurde nicht gefunden', new Exclamation());
        }
        if (!($tblDivision = $tblDiary->getServiceTblDivision())) {
            return new Danger('Die Klasse wurde nicht gefunden', new Exclamation());
        }

        return $this->getDiaryModal(Diary::useFrontend()->formDiary($tblDivision, $DiaryId, true), $DiaryId);
    }

    /**
     * @param $DiaryId
     * @param $Data
     *
     * @return Danger|string
     */
    public function saveEditDiaryModal($DiaryId, $Data)
    {

        if (!($tblDiary = Diary::useService()->getDiaryById($DiaryId))) {
            return new Danger('Der Eintrag wurde nicht gefunden', new Exclamation());
        }
        if (!($tblDivision = $tblDiary->getServiceTblDivision())) {
            return new Danger('Die Klasse wurde nicht gefunden', new Exclamation());
        }

        if (($form = Diary::useService()->checkFormDiary($tblDivision, $Data, $tblDiary))) {
            // display Errors on form
            return $this->getDiaryModal($form, $DiaryId);
        }

        if (Diary::useService()->updateDiary($tblDiary, $Data)) {
            return new Success('Der Eintrag wurde erfolgreich gespeichert.')
                . self::pipelineLoadDiaryContent($tblDivision->getId())
                . self::pipelineClose();
        } else {
            return new Danger('Der Eintrag konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }
}