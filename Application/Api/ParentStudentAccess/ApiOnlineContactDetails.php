<?php

namespace SPHERE\Application\Api\ParentStudentAccess;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\ParentStudentAccess\OnlineContactDetails\OnlineContactDetails;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
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
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\System\Extension\Extension;

class ApiOnlineContactDetails extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('loadContactDetailsStageContent');

        $Dispatcher->registerMethod('openCreatePhoneModal');
        $Dispatcher->registerMethod('saveCreatePhoneModal');

        $Dispatcher->registerMethod('openCreateAddressModal');
        $Dispatcher->registerMethod('saveCreateAddressModal');

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
    public static function pipelineLoadContactDetailsStageContent(): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'ContactDetailsStageContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadContactDetailsStageContent',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return string
     */
    public function loadContactDetailsStageContent(): string
    {
        return OnlineContactDetails::useFrontend()->loadContactDetailsStageContent();
    }

    /**
     * @param string|null $PersonId
     * @param string|null $ToPersonId
     * @param array $PersonIdList
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreatePhoneModal(string $PersonId = null, string $ToPersonId = null, array $PersonIdList = array()): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreatePhoneModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'ToPersonId' => $ToPersonId,
            'PersonIdList' => $PersonIdList
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     * @param $ToPersonId
     * @param $PersonIdList
     *
     * @return string
     */
    public function openCreatePhoneModal($PersonId, $ToPersonId, $PersonIdList)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        return $this->getPhoneModal(OnlineContactDetails::useFrontend()->formPhone($PersonId, $ToPersonId, $PersonIdList), $tblPerson, $ToPersonId);
    }

    /**
     * @param $form
     * @param TblPerson $tblPerson
     * @param null $ToPersonId
     *
     * @return string
     */
    private function getPhoneModal($form, TblPerson $tblPerson,  $ToPersonId = null): string
    {
        if ($ToPersonId) {
            $title = new Title(new Edit() . ' Telefonnummer bearbeiten (Änderungswunsch)');
        } else {
            $title = new Title(new Plus() . ' Telefonnummer hinzufügen');
        }

        return $title
            . new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(new PersonIcon() . ' Person',
                                    new Bold($tblPerson->getFullName()),
                                    Panel::PANEL_TYPE_SUCCESS
                                )
                            , $ToPersonId ? 6: 12),
                            $ToPersonId && ($tblToPerson = Phone::useService()->getPhoneToPersonById($ToPersonId))
                                ? new LayoutColumn(new Panel(new \SPHERE\Common\Frontend\Icon\Repository\Phone() . ' Telefonnummer',
                                    new Bold($tblToPerson->getTblPhone()->getNumber()),
                                    Panel::PANEL_TYPE_SUCCESS), 6)
                                : null
                        )),
                    )),
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
     * @param $PersonId
     * @param $ToPersonId
     * @param $PersonIdList
     *
     * @return Pipeline
     */
    public static function pipelineCreatePhoneSave($PersonId, $ToPersonId, $PersonIdList): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreatePhoneModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'ToPersonId' => $ToPersonId,
            'PersonIdList' => $PersonIdList
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     * @param $ToPersonId
     * @param $PersonIdList
     * @param $Data
     *
     * @return string
     */
    public function saveCreatePhoneModal($PersonId, $ToPersonId, $PersonIdList, $Data): string
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (($form = OnlineContactDetails::useService()->checkFormPhone($tblPerson, $ToPersonId, $PersonIdList, $Data))) {
            // display Errors on form
            return $this->getPhoneModal($form, $tblPerson, $ToPersonId);
        }

        if (OnlineContactDetails::useService()->createPhone($tblPerson, $ToPersonId, $Data)) {
            return new Success('Die Telefonnummer wurde erfolgreich gespeichert.')
                . self::pipelineLoadContactDetailsStageContent()
                . self::pipelineClose();
        } else {
            return new Danger('Die Telefonnummer konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param string|null $PersonId
     * @param string|null $ToPersonId
     * @param array $PersonIdList
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateAddressModal(string $PersonId = null, string $ToPersonId = null, array $PersonIdList = array()): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateAddressModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'ToPersonId' => $ToPersonId,
            'PersonIdList' => $PersonIdList
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     * @param $ToPersonId
     * @param $PersonIdList
     *
     * @return string
     */
    public function openCreateAddressModal($PersonId, $ToPersonId, $PersonIdList)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        return $this->getAddressModal(OnlineContactDetails::useFrontend()->formAddress($PersonId, $ToPersonId, $PersonIdList), $tblPerson, $ToPersonId);
    }

    /**
     * @param $form
     * @param TblPerson $tblPerson
     * @param null $ToPersonId
     *
     * @return string
     */
    private function getAddressModal($form, TblPerson $tblPerson,  $ToPersonId = null): string
    {
        if ($ToPersonId) {
            $title = new Title(new Edit() . ' Adresse bearbeiten (Änderungswunsch)');
        } else {
            $title = new Title(new Plus() . ' Adresse hinzufügen');
        }

        return $title
            . new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(new PersonIcon() . ' Person',
                                    new Bold($tblPerson->getFullName()),
                                    Panel::PANEL_TYPE_SUCCESS
                                )
                                , $ToPersonId ? 6: 12),
                            $ToPersonId && ($tblToPerson = Address::useService()->getAddressToPersonById($ToPersonId))
                                ? new LayoutColumn(new Panel(new MapMarker() . ' Adresse',
                                new Bold($tblToPerson->getTblAddress()->getGuiString()),
                                Panel::PANEL_TYPE_SUCCESS), 6)
                                : null
                        )),
                    )),
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
     * @param $PersonId
     * @param $ToPersonId
     * @param $PersonIdList
     *
     * @return Pipeline
     */
    public static function pipelineCreateAddressSave($PersonId, $ToPersonId, $PersonIdList): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateAddressModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'ToPersonId' => $ToPersonId,
            'PersonIdList' => $PersonIdList
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     * @param $ToPersonId
     * @param $PersonIdList
     * @param $Data
     *
     * @return string
     */
    public function saveCreateAddressModal($PersonId, $ToPersonId, $PersonIdList, $Data): string
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (($form = OnlineContactDetails::useService()->checkFormAddress($tblPerson, $ToPersonId, $PersonIdList, $Data))) {
            // display Errors on form
            return $this->getAddressModal($form, $tblPerson, $ToPersonId);
        }

        if (OnlineContactDetails::useService()->createAddress($tblPerson, $ToPersonId, $Data)) {
            return new Success('Die Adresse wurde erfolgreich gespeichert.')
                . self::pipelineLoadContactDetailsStageContent()
                . self::pipelineClose();
        } else {
            return new Danger('Die Adresse konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }
}