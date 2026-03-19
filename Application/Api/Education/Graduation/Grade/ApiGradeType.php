<?php /** @noinspection PhpUnused */

namespace SPHERE\Application\Api\Education\Graduation\Grade;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\MinusSign;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
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

class ApiGradeType  extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('loadGradeTypeContent');
        $Dispatcher->registerMethod('openCreateGradeTypeModal');
        $Dispatcher->registerMethod('saveCreateGradeTypeModal');
        $Dispatcher->registerMethod('openEditGradeTypeModal');
        $Dispatcher->registerMethod('saveEditGradeTypeModal');
        $Dispatcher->registerMethod('openDeleteGradeTypeModal');
        $Dispatcher->registerMethod('saveDeleteGradeTypeModal');
        $Dispatcher->registerMethod('openActiveGradeTypeModal');
        $Dispatcher->registerMethod('saveActiveGradeTypeModal');

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
     * @return Pipeline
     */
    public static function pipelineLoadGradeTypeContent(): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'GradeTypeContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadGradeTypeContent',
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return string
     */
    public function loadGradeTypeContent(): string
    {
        return Grade::useFrontend()->loadGradeTypeTable();
    }

    /**
     * @return Pipeline
     */
    public static function pipelineOpenCreateGradeTypeModal(): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateGradeTypeModal',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return string
     */
    public function openCreateGradeTypeModal(): string
    {
        return $this->getGradeTypeModal(Grade::useFrontend()->formGradeType(null, true));
    }

    /**
     * @param $form
     * @param string|null $GradeTypeId
     *
     * @return string
     */
    private function getGradeTypeModal($form, string $GradeTypeId = null): string
    {
        if ($GradeTypeId) {
            $title = new Title(new Edit() . ' Zensuren-Typ bearbeiten');
        } else {
            $title = new Title(new Plus() . ' Zensuren-Typ hinzufügen');
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
    public static function pipelineCreateGradeTypeSave(): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateGradeTypeModal'
        ));

        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param array|null $Data
     *
     * @return string
     */
    public function saveCreateGradeTypeModal(array $Data = null): string
    {
        if (($form = Grade::useService()->checkFormGradeType($Data))) {
            // display Errors on form
            return $this->getGradeTypeModal($form);
        }

        if (Grade::useService()->createGradeType($Data)) {
            return new Success('Zensuren-Typ wurde erfolgreich gespeichert.')
                . self::pipelineLoadGradeTypeContent()
                . self::pipelineClose();
        } else {
            return new Danger('Zensuren-Typ konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $GradeTypeId
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditGradeTypeModal($GradeTypeId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditGradeTypeModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'GradeTypeId' => $GradeTypeId,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $GradeTypeId
     *
     * @return string
     */
    public function openEditGradeTypeModal($GradeTypeId): string
    {
        if (!Grade::useService()->getGradeTypeById($GradeTypeId)) {
            return new Danger('Der Zensuren-Typ wurde nicht gefunden', new Exclamation());
        }

        return $this->getGradeTypeModal(Grade::useFrontend()->formGradeType($GradeTypeId, true), $GradeTypeId);
    }

    /**
     * @param $GradeTypeId
     *
     * @return Pipeline
     */
    public static function pipelineEditGradeTypeSave($GradeTypeId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditGradeTypeModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'GradeTypeId' => $GradeTypeId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $GradeTypeId
     * @param $Data
     *
     * @return string
     */
    public function saveEditGradeTypeModal($GradeTypeId, $Data): string
    {
        if (!($tblGradeType = Grade::useService()->getGradeTypeById($GradeTypeId))) {
            return new Danger('Der Zensuren-Typ wurde nicht gefunden', new Exclamation());
        }

        if (($form = Grade::useService()->checkFormGradeType($Data, $tblGradeType))) {
            // display Errors on form
            return $this->getGradeTypeModal($form, $GradeTypeId);
        }

        if (Grade::useService()->updateGradeType($tblGradeType, $Data)) {
            return new Success('Der Zensuren-Typ wurde erfolgreich gespeichert.')
                . self::pipelineLoadGradeTypeContent()
                . self::pipelineClose();
        } else {
            return new Danger('Der Zensuren-Typ konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $GradeTypeId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteGradeTypeModal($GradeTypeId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDeleteGradeTypeModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'GradeTypeId' => $GradeTypeId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $GradeTypeId
     *
     * @return string
     */
    public function openDeleteGradeTypeModal($GradeTypeId): string
    {
        if (!($tblGradeType = Grade::useService()->getGradeTypeById($GradeTypeId))) {
            return new Danger('Der Zensuren-Typ wurde nicht gefunden', new Exclamation());
        }

        return new Title(new Remove() . ' Zensuren-Typ löschen')
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(
                                new Question() . ' Diesen Zensuren-Typ wirklich löschen?',
                                array(
                                    'Abkürzung: ' . $tblGradeType->getCode(),
                                    'Name: ' . new Bold($tblGradeType->getName()),
                                    'Beschreibung:' . $tblGradeType->getDescription()
                                ),
                                Panel::PANEL_TYPE_DANGER
                            )
                            . (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteGradeTypeSave($GradeTypeId))
                            . (new Standard('Nein', self::getEndpoint(), new Remove()))
                                ->ajaxPipelineOnClick(self::pipelineClose())
                        )
                    )
                )
            );
    }

    /**
     * @param $GradeTypeId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteGradeTypeSave($GradeTypeId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveDeleteGradeTypeModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'GradeTypeId' => $GradeTypeId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $GradeTypeId
     *
     * @return string
     */
    public function saveDeleteGradeTypeModal($GradeTypeId): string
    {
        if (!($tblGradeType = Grade::useService()->getGradeTypeById($GradeTypeId))) {
            return new Danger('Der Zensuren-Typ wurde nicht gefunden', new Exclamation());
        }

        if (Grade::useService()->deleteGradeType($tblGradeType)) {
            return new Success('Der Zensuren-Typ wurde erfolgreich gelöscht.')
                . self::pipelineLoadGradeTypeContent()
                . self::pipelineClose();
        } else {
            return new Danger('Der Zensuren-Typ konnte nicht gelöscht werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $GradeTypeId
     *
     * @return Pipeline
     */
    public static function pipelineOpenActiveGradeTypeModal($GradeTypeId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openActiveGradeTypeModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'GradeTypeId' => $GradeTypeId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $GradeTypeId
     *
     * @return string
     */
    public function openActiveGradeTypeModal($GradeTypeId): string
    {
        if (!($tblGradeType = Grade::useService()->getGradeTypeById($GradeTypeId))) {
            return new Danger('Der Zensuren-Typ wurde nicht gefunden', new Exclamation());
        }

        $text = $tblGradeType->getIsActive() ? 'deaktivieren' : 'aktivieren';
        $icon = $tblGradeType->getIsActive() ? new MinusSign() : new PlusSign();
        return new Title($icon . ' Zensuren-Typ ' . $text)
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(
                                new Question() . ' Diesen Zensuren-Typ wirklich ' . $text . '?',
                                array(
                                    'Abkürzung: ' . $tblGradeType->getCode(),
                                    'Name: ' . new Bold($tblGradeType->getName()),
                                    'Beschreibung:' . $tblGradeType->getDescription()
                                ),
                                Panel::PANEL_TYPE_WARNING
                            )
                            . (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineActiveGradeTypeSave($GradeTypeId))
                            . (new Standard('Nein', self::getEndpoint(), new Remove()))
                                ->ajaxPipelineOnClick(self::pipelineClose())
                        )
                    )
                )
            );
    }

    /**
     * @param $GradeTypeId
     *
     * @return Pipeline
     */
    public static function pipelineActiveGradeTypeSave($GradeTypeId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveActiveGradeTypeModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'GradeTypeId' => $GradeTypeId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $GradeTypeId
     *
     * @return string
     */
    public function saveActiveGradeTypeModal($GradeTypeId): string
    {
        if (!($tblGradeType = Grade::useService()->getGradeTypeById($GradeTypeId))) {
            return new Danger('Der Zensuren-Typ wurde nicht gefunden', new Exclamation());
        }

        $IsActive = !$tblGradeType->getIsActive();

        if (Grade::useService()->updateGradeTypeActive($tblGradeType, $IsActive)) {
            return new Success('Der Zensuren-Typ wurde ' . ($IsActive ? 'aktiviert.' : 'deaktiviert.'))
                . self::pipelineLoadGradeTypeContent()
                . self::pipelineClose();
        } else {
            return new Danger('Der Zensuren-Typ konnte nicht gelöscht werden.') . self::pipelineClose();
        }
    }
}