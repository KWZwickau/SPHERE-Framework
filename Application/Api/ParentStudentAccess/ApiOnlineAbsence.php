<?php

namespace SPHERE\Application\Api\ParentStudentAccess;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Absence\Absence;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\ParentStudentAccess\OnlineAbsence\OnlineAbsence;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\System\Extension\Extension;

class ApiOnlineAbsence extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('openCreateOnlineAbsenceModal');
        $Dispatcher->registerMethod('saveCreateOnlineAbsenceModal');

        $Dispatcher->registerMethod('loadOnlineAbsenceContent');

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
     * @param null $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineLoadOnlineAbsenceContent($PersonId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'OnlineAbsenceContent_' . $PersonId), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadOnlineAbsenceContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadOnlineAbsenceContent($PersonId = null): string
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        return OnlineAbsence::useFrontend()->loadOnlineAbsenceTable($tblPerson);
    }

    /**
     * @param null $PersonId
     * @param null $Source
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateOnlineAbsenceModal($PersonId = null, $Source = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateOnlineAbsenceModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'Source' => $Source
        ));

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function openCreateOnlineAbsenceModal($PersonId = null, $Source = null, $Data = null)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        return $this->getOnlineAbsenceModal(OnlineAbsence::useFrontend()->formOnlineAbsence($Data, $PersonId, $Source), $tblPerson);
    }

    /**
     * @param $form
     * @param TblPerson $tblPerson
     *
     * @return string
     */
    private function getOnlineAbsenceModal($form, TblPerson $tblPerson): string
    {
        return new Title(new Plus() . ' Fehlzeit hinzufügen')
            . new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(new Panel(
                                'Schüler',
                                $tblPerson->getFullName(),
                                Panel::PANEL_TYPE_INFO
                            ), 6),
                            new LayoutColumn(new Panel(
                                'Kurse',
                                DivisionCourse::useService()->getCurrentMainCoursesByPersonAndDate($tblPerson),
                                Panel::PANEL_TYPE_INFO
                            ), 6)
                        )),
                        new LayoutRow(
                            new LayoutColumn(
                                new Well(
                                    $form
                                )
                            )
                        )
                    )))
            );
    }

    /**
     * @param null $PersonId
     * @param null $Source
     *
     * @return Pipeline
     */
    public static function pipelineCreateOnlineAbsenceSave($PersonId = null, $Source = null): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateOnlineAbsenceModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'Source' => $Source,
        ));

        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $Data
     * @param null $PersonId
     * @param null $Source
     *
     * @return string
     */
    public function saveCreateOnlineAbsenceModal($Data, $PersonId = null, $Source = null): string
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (($form = Absence::useService()->checkFormOnlineAbsence($Data, $tblPerson, $Source))) {
            // display Errors on form
            return $this->getOnlineAbsenceModal($form, $tblPerson);
        }

        if (Absence::useService()->createOnlineAbsence($Data, $tblPerson, $Source)) {
            return new Success('Die Fehlzeit wurde erfolgreich gespeichert.')
                . self::pipelineLoadOnlineAbsenceContent($PersonId) . self::pipelineClose();
        } else {
            return new Danger('Die Fehlzeit konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }
}