<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 08.01.2019
 * Time: 08:13
 */

namespace SPHERE\Application\Api\Contact;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
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
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
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
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\System\Extension\Extension;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;

/**
 * Class ApiPhoneToPerson
 * 
 * @package SPHERE\Application\Api\Contact
 */
class ApiPhoneToPerson extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('loadPhoneToPersonContent');

        $Dispatcher->registerMethod('openCreatePhoneToPersonModal');
        $Dispatcher->registerMethod('saveCreatePhoneToPersonModal');

        $Dispatcher->registerMethod('openEditPhoneToPersonModal');
        $Dispatcher->registerMethod('saveEditPhoneToPersonModal');

        $Dispatcher->registerMethod('openDeletePhoneToPersonModal');
        $Dispatcher->registerMethod('saveDeletePhoneToPersonModal');

        $Dispatcher->registerMethod('transferPhone');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModal()
    {

        return (new ModalReceiver(null, new Close()))->setIdentifier('ModalReciever');
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
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineLoadPhoneToPersonContent($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'PhoneToPersonContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadPhoneToPersonContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     * @param $OnlineContactId
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreatePhoneToPersonModal($PersonId, $OnlineContactId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreatePhoneToPersonModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'OnlineContactId' => $OnlineContactId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     * @param $OnlineContactId
     *
     * @return Pipeline
     */
    public static function pipelineCreatePhoneToPersonSave($PersonId, $OnlineContactId): Pipeline
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreatePhoneToPersonModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'OnlineContactId' => $OnlineContactId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     * @param $ToPersonId
     * @param $OnlineContactId
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditPhoneToPersonModal($PersonId, $ToPersonId, $OnlineContactId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditPhoneToPersonModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'ToPersonId' => $ToPersonId,
            'OnlineContactId' => $OnlineContactId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     * @param $ToPersonId
     * @param $OnlineContactId
     *
     * @return Pipeline
     */
    public static function pipelineEditPhoneToPersonSave($PersonId, $ToPersonId, $OnlineContactId): Pipeline
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditPhoneToPersonModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'ToPersonId' => $ToPersonId,
            'OnlineContactId' => $OnlineContactId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $PersonId
     * @param $ToPersonId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeletePhoneToPersonModal($PersonId, $ToPersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDeletePhoneToPersonModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'ToPersonId' => $ToPersonId,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     * @param $ToPersonId
     *
     * @return Pipeline
     */
    public static function pipelineDeletePhoneToPersonSave($PersonId, $ToPersonId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveDeletePhoneToPersonModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'ToPersonId' => $ToPersonId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    public function loadPhoneToPersonContent($PersonId)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        return Phone::useFrontend()->frontendLayoutPersonNew($tblPerson);
    }

    /**
     * @param $PersonId
     * @param $OnlineContactId
     *
     * @return string
     */
    public function openCreatePhoneToPersonModal($PersonId, $OnlineContactId)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        return $this->getPhoneToPersonModal(Phone::useFrontend()->formNumberToPerson($PersonId, null, true, $OnlineContactId),
            $tblPerson, null, $OnlineContactId);
    }

    /**
     * @param $PersonId
     * @param $ToPersonId
     * @param $OnlineContactId
     *
     * @return string
     */
    public function openEditPhoneToPersonModal($PersonId, $ToPersonId, $OnlineContactId)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToPerson = Phone::useService()->getPhoneToPersonById($ToPersonId))) {
            return new Danger('Die Telefonnummer wurde nicht gefunden', new Exclamation());
        }

        return $this->getPhoneToPersonModal(Phone::useFrontend()->formNumberToPerson($PersonId, $ToPersonId, true, $OnlineContactId),
            $tblPerson, $ToPersonId, $OnlineContactId);
    }

    /**
     * @param $form
     * @param TblPerson $tblPerson
     * @param null $ToPersonId
     * @param null $OnlineContactId
     * @param bool $isPhoneTransfer
     *
     * @return string
     */
    private function getPhoneToPersonModal($form, TblPerson $tblPerson,  $ToPersonId = null, $OnlineContactId = null, $isPhoneTransfer = false)
    {
        if ($ToPersonId) {
            $title = new Title(new Edit() . ' Telefonnummer bearbeiten');
        } else {
            $title = new Title(new Plus() . ' Telefonnummer hinzufügen');
        }

        if ($OnlineContactId && ($tblOnlineContact = OnlineContactDetails::useService()->getOnlineContactById($OnlineContactId))) {
            $columns[] = new LayoutColumn(new Well($form), 6);
            $columns[] = new LayoutColumn(new Panel(
                $tblOnlineContact->getContactTypeIcon() . $tblOnlineContact->getContactTypeName(),
                array(
                    'Telefonnummer: ' . $tblOnlineContact->getContactContent(),
                    $tblOnlineContact->getContactCreate(),
                    $tblOnlineContact->getRemark() ? new Muted('Bemerkung vom Ersteller: ' . $tblOnlineContact->getRemark()) : '',
                    $ToPersonId && !$isPhoneTransfer ? (new Primary('Telefonnummer nach links übernehmen', self::getEndpoint(), new ChevronLeft()))->ajaxPipelineOnClick(
                        self::pipelineTransferPhone($tblPerson->getId(), $ToPersonId, $OnlineContactId)
                    ) : '',
                ),
                Panel::PANEL_TYPE_DEFAULT
            ), 6);
        } else {
            $columns[] = new LayoutColumn(new Well($form));
        }

        return $title
            . new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(
                            new LayoutColumn(
                                new Panel(new PersonIcon() . ' Person', new Bold($tblPerson->getFullName()), Panel::PANEL_TYPE_SUCCESS)
                            )
                        ),
                    )),
                    new LayoutGroup(
                        new LayoutRow(
                            $columns
                        )
                    ))
            );
    }

    /**
     * @param $PersonId
     * @param $ToPersonId
     *
     * @return string
     */
    public function openDeletePhoneToPersonModal($PersonId, $ToPersonId)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToPerson = Phone::useService()->getPhoneToPersonById($ToPersonId))) {
            return new Danger('Die Telefonnummer wurde nicht gefunden', new Exclamation());
        }

        return new Title(new Remove() . ' Telefonnummer löschen')
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(new PersonIcon() . ' Person',
                                new Bold($tblPerson->getFullName()),
                                Panel::PANEL_TYPE_SUCCESS
                            )
                            . new Panel(new Question() . ' Diese Telefonnummer wirklich löschen?', array(
                                $tblToPerson->getTblType()->getName() . ' ' . $tblToPerson->getTblType()->getDescription(),
                                ($tblPhone = $tblToPerson->getTblPhone()) ? $tblPhone->getNumber() : '',
                                ($tblToPerson->getRemark() ? new Muted(new Small($tblToPerson->getRemark())) : '')
                            ),
                                Panel::PANEL_TYPE_DANGER)
                            . (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeletePhoneToPersonSave($PersonId, $ToPersonId))
                            . (new Standard('Nein', self::getEndpoint(), new Remove()))
                                ->ajaxPipelineOnClick(self::pipelineClose())
                        )
                    )
                )
            );
    }

    /**
     * @param $PersonId
     * @param $OnlineContactId
     * @param $Number
     * @param $Type
     *
     * @return Danger|string
     */
    public function saveCreatePhoneToPersonModal($PersonId, $OnlineContactId, $Number, $Type)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (($form = Phone::useService()->checkFormPhoneToPerson($tblPerson, $Number, $Type, $OnlineContactId))) {
            // display Errors on form
            return $this->getPhoneToPersonModal($form, $tblPerson, null, $OnlineContactId);
        }

        if (Phone::useService()->createPhoneToPerson($tblPerson, $Number, $Type)) {
            if ($OnlineContactId && ($tblOnlineContact = OnlineContactDetails::useService()->getOnlineContactById($OnlineContactId))) {
                OnlineContactDetails::useService()->deleteOnlineContact($tblOnlineContact);
            }

            return new Success('Die Telefonnummer wurde erfolgreich gespeichert.')
                . self::pipelineLoadPhoneToPersonContent($PersonId)
                . ($OnlineContactId ? ApiContactDetails::pipelineLoadContactDetailsStageContent() : '')
                . self::pipelineClose();
        } else {
            return new Danger('Die Telefonnummer konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $PersonId
     * @param $ToPersonId
     * @param $Number
     * @param $Type
     * @param $OnlineContactId
     *
     * @return Danger|string
     */
    public function saveEditPhoneToPersonModal($PersonId, $ToPersonId, $Number, $Type, $OnlineContactId)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToPerson = Phone::useService()->getPhoneToPersonById($ToPersonId))) {
            return new Danger('Die Telefonnummer wurde nicht gefunden', new Exclamation());
        }

        if (($form = Phone::useService()->checkFormPhoneToPerson($tblPerson, $Number, $Type, $OnlineContactId, $tblToPerson))) {
            // display Errors on form
            return $this->getPhoneToPersonModal($form, $tblPerson, $ToPersonId, $OnlineContactId);
        }

        if (Phone::useService()->updatePhoneToPerson($tblToPerson, $Number, $Type)) {
            if ($OnlineContactId && ($tblOnlineContact = OnlineContactDetails::useService()->getOnlineContactById($OnlineContactId))) {
                OnlineContactDetails::useService()->deleteOnlineContact($tblOnlineContact);
            }

            return new Success('Die Telefonnummer wurde erfolgreich gespeichert.')
                . self::pipelineLoadPhoneToPersonContent($PersonId)
                . ($OnlineContactId ? ApiContactDetails::pipelineLoadContactDetailsStageContent() : '')
                . self::pipelineClose();
        } else {
            return new Danger('Die Telefonnummer konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $PersonId
     * @param $ToPersonId
     *
     * @return Danger|string
     */
    public function saveDeletePhoneToPersonModal($PersonId, $ToPersonId)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToPerson = Phone::useService()->getPhoneToPersonById($ToPersonId))) {
            return new Danger('Die Telefonnummer wurde nicht gefunden', new Exclamation());
        }

        if (Phone::useService()->removePhoneToPerson($tblToPerson)) {
            return new Success('Die Telefonnummer wurde erfolgreich gelöscht.')
                . self::pipelineLoadPhoneToPersonContent($PersonId)
                . self::pipelineClose();
        } else {
            return new Danger('Die Telefonnummer konnte nicht gelöscht werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $PersonId
     * @param $ToPersonId
     * @param $OnlineContactId
     *
     * @return Pipeline
     */
    public static function pipelineTransferPhone($PersonId, $ToPersonId, $OnlineContactId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'transferPhone',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'ToPersonId' => $ToPersonId,
            'OnlineContactId' => $OnlineContactId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     * @param $ToPersonId
     * @param $OnlineContactId
     *
     * @return string
     */
    public function transferPhone($PersonId, $ToPersonId, $OnlineContactId)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToPerson = Phone::useService()->getPhoneToPersonById($ToPersonId))) {
            return new Danger('Die Telefonnummer wurde nicht gefunden', new Exclamation());
        }

        return $this->getPhoneToPersonModal(Phone::useFrontend()->formNumberToPerson($PersonId, $ToPersonId, true, $OnlineContactId, true),
            $tblPerson, $ToPersonId, $OnlineContactId, true);
    }
}