<?php

namespace SPHERE\Application\Api\ParentStudentAccess;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
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
     * @param null $DivisionId
     *
     * @return Pipeline
     */
    public static function pipelineLoadOnlineAbsenceContent($PersonId = null, $DivisionId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'OnlineAbsenceContent_' . $PersonId), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadOnlineAbsenceContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'DivisionId' => $DivisionId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $PersonId
     * @param null $DivisionId
     *
     * @return string
     */
    public function loadOnlineAbsenceContent($PersonId = null, $DivisionId = null): string
    {
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblPerson = Person::useService()->getPersonById($PersonId);

        if (!($tblDivision && $tblPerson)) {
            return new Danger('Die Klasse oder Person wurde nicht gefunden', new Exclamation());
        }

        return OnlineAbsence::useFrontend()->loadOnlineAbsenceTable($tblPerson, $tblDivision);
    }

    /**
     * @param null $PersonId
     * @param null $DivisionId
     * @param null $Source
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateOnlineAbsenceModal($PersonId = null, $DivisionId = null, $Source = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateOnlineAbsenceModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'DivisionId' => $DivisionId,
            'Source' => $Source
        ));

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $PersonId
     * @param null $DivisionId
     *
     * @return string
     */
    public function openCreateOnlineAbsenceModal($PersonId = null, $DivisionId = null, $Source = null, $Data = null)
    {
        $tblPerson = Person::useService()->getPersonById($PersonId);
        $tblDivision = Division::useService()->getDivisionById($DivisionId);

        if (!($tblDivision && $tblPerson)) {
            return new Danger('Die Klasse oder Person wurde nicht gefunden', new Exclamation());
        }

        return $this->getOnlineAbsenceModal(OnlineAbsence::useFrontend()->formOnlineAbsence($Data, $PersonId, $DivisionId, $Source), $tblPerson, $tblDivision);
    }

    /**
     * @param $form
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     *
     * @return string
     */
    private function getOnlineAbsenceModal($form, TblPerson $tblPerson, TblDivision $tblDivision): string
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
                                'Klasse',
                                $tblDivision->getDisplayName(),
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
     * @param null $DivisionId
     * @param null $Source
     *
     * @return Pipeline
     */
    public static function pipelineCreateOnlineAbsenceSave($PersonId = null, $DivisionId = null, $Source = null): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateOnlineAbsenceModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'DivisionId' => $DivisionId,
            'Source' => $Source,
        ));

        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $Data
     * @param null $PersonId
     * @param null $DivisionId
     * @param null $Source
     *
     * @return string
     */
    public function saveCreateOnlineAbsenceModal($Data, $PersonId = null, $DivisionId = null, $Source = null): string
    {
        $tblPerson = Person::useService()->getPersonById($PersonId);
        $tblDivision = Division::useService()->getDivisionById($DivisionId);

        if (!($tblDivision && $tblPerson)) {
            return new Danger('Die Klasse oder Person wurde nicht gefunden', new Exclamation());
        }

        if (($form = Absence::useService()->checkFormOnlineAbsence($Data, $tblPerson, $tblDivision, $Source))) {
            // display Errors on form
            return $this->getOnlineAbsenceModal($form, $tblPerson, $tblDivision);
        }

        if (Absence::useService()->createOnlineAbsence(
            $Data,
            $tblPerson,
            $tblDivision,
            $Source
        )) {
            return new Success('Die Fehlzeit wurde erfolgreich gespeichert.')
                . self::pipelineLoadOnlineAbsenceContent($PersonId, $DivisionId) . self::pipelineClose();
        } else {
            return new Danger('Die Fehlzeit konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }
}