<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 08.01.2019
 * Time: 11:43
 */

namespace SPHERE\Application\Api\Contact;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Contact\Mail\Mail;
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
 * Class ApiMailToPerson
 *
 * @package SPHERE\Application\Api\Contact
 */
class ApiMailToPerson extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('loadMailToPersonContent');

        $Dispatcher->registerMethod('openCreateMailToPersonModal');
        $Dispatcher->registerMethod('saveCreateMailToPersonModal');

        $Dispatcher->registerMethod('openEditMailToPersonModal');
        $Dispatcher->registerMethod('saveEditMailToPersonModal');

        $Dispatcher->registerMethod('openDeleteMailToPersonModal');
        $Dispatcher->registerMethod('saveDeleteMailToPersonModal');

        $Dispatcher->registerMethod('transferMail');

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
    public static function pipelineLoadMailToPersonContent($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'MailToPersonContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadMailToPersonContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     * @param null $OnlineContactId
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateMailToPersonModal($PersonId, $OnlineContactId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateMailToPersonModal',
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
    public static function pipelineCreateMailToPersonSave($PersonId, $OnlineContactId): Pipeline
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateMailToPersonModal'
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
     * @param null $OnlineContactId
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditMailToPersonModal($PersonId, $ToPersonId, $OnlineContactId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditMailToPersonModal',
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
    public static function pipelineEditMailToPersonSave($PersonId, $ToPersonId, $OnlineContactId): Pipeline
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditMailToPersonModal'
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
    public static function pipelineOpenDeleteMailToPersonModal($PersonId, $ToPersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDeleteMailToPersonModal',
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
    public static function pipelineDeleteMailToPersonSave($PersonId, $ToPersonId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveDeleteMailToPersonModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'ToPersonId' => $ToPersonId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    public function loadMailToPersonContent($PersonId)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        return Mail::useFrontend()->frontendLayoutPersonNew($tblPerson);
    }

    /**
     * @param $PersonId
     * @param $OnlineContactId
     *
     * @return string
     */
    public function openCreateMailToPersonModal($PersonId, $OnlineContactId)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        return $this->getMailToPersonModal(Mail::useFrontend()->formAddressToPerson($PersonId, null, true, $OnlineContactId),
            $tblPerson, null, $OnlineContactId);
    }

    /**
     * @param $PersonId
     * @param $ToPersonId
     * @param $OnlineContactId
     *
     * @return string
     */
    public function openEditMailToPersonModal($PersonId, $ToPersonId, $OnlineContactId)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToPerson = Mail::useService()->getMailToPersonById($ToPersonId))) {
            return new Danger('Die E-Mail Adresse wurde nicht gefunden', new Exclamation());
        }

        return $this->getMailToPersonModal(Mail::useFrontend()->formAddressToPerson($PersonId, $ToPersonId, true, $OnlineContactId),
            $tblPerson, $ToPersonId, $OnlineContactId);
    }

    /**
     * @param $form
     * @param TblPerson $tblPerson
     * @param null $ToPersonId
     * @param null $OnlineContactId
     * @param bool $isMailTransfer
     *
     * @return string
     */
    private function getMailToPersonModal($form, TblPerson $tblPerson,  $ToPersonId = null, $OnlineContactId = null, $isMailTransfer = false)
    {
        if ($ToPersonId) {
            $title = new Title(new Edit() . ' E-Mail Adresse bearbeiten');
        } else {
            $title = new Title(new Plus() . ' E-Mail Adresse hinzufügen');
        }

        if ($OnlineContactId && ($tblOnlineContact = OnlineContactDetails::useService()->getOnlineContactById($OnlineContactId))) {
            $columns[] = new LayoutColumn(new Panel(
                $tblOnlineContact->getContactTypeIcon() . $tblOnlineContact->getContactTypeName(),
                array(
                    'E-Mail Adresse: ' . $tblOnlineContact->getContactContent(),
                    $tblOnlineContact->getContactCreate(),
                    $tblOnlineContact->getRemark() ? new Muted('Bemerkung vom Ersteller: ' . $tblOnlineContact->getRemark()) : '',
                    $ToPersonId && !$isMailTransfer ? (new Primary('E-Mail Adresse ins Formular übernehmen', self::getEndpoint(), new ChevronLeft()))->ajaxPipelineOnClick(
                        self::pipelineTransferMail($tblPerson->getId(), $ToPersonId, $OnlineContactId)
                    ) : '',
                ),
                Panel::PANEL_TYPE_DEFAULT
            ));
        }
        $columns[] = new LayoutColumn(new Well($form));

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
    public function openDeleteMailToPersonModal($PersonId, $ToPersonId)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToPerson = Mail::useService()->getMailToPersonById($ToPersonId))) {
            return new Danger('Die E-Mail Adresse wurde nicht gefunden', new Exclamation());
        }

        return new Title(new Remove() . ' E-Mail Adresse löschen')
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(new PersonIcon() . ' Person',
                                new Bold($tblPerson->getFullName()),
                                Panel::PANEL_TYPE_SUCCESS
                            )
                            . new Panel(new Question() . ' Diese E-Mail Adresse wirklich löschen?', array(
                                $tblToPerson->getTblType()->getName() . ' ' . $tblToPerson->getTblType()->getDescription(),
                                ($tblMail = $tblToPerson->getTblMail()) ? $tblMail->getAddress() : '',
                                ($tblToPerson->getRemark() ? new Muted(new Small($tblToPerson->getRemark())) : '')
                            ),
                                Panel::PANEL_TYPE_DANGER)
                            . (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteMailToPersonSave($PersonId, $ToPersonId))
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
     * @param $Address
     * @param $Type
     *
     * @return Danger|string
     */
    public function saveCreateMailToPersonModal($PersonId, $OnlineContactId, $Address, $Type)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        $IsAccountUserAlias = isset($Address['Alias']);
        $IsAccountRecoveryMail = isset($Address['IsRecoveryMail']);

        $mailAddress = str_replace(' ', '', $Address['Mail']);
        if (($form = Mail::useService()->checkFormMailToPerson($tblPerson, $mailAddress, $Type, $IsAccountUserAlias, $OnlineContactId))) {
            // display Errors on form
            return $this->getMailToPersonModal($form, $tblPerson, null, $OnlineContactId);
        }

        if (Mail::useService()->createMailToPerson($tblPerson, $mailAddress, $Type, $IsAccountUserAlias, $IsAccountRecoveryMail)) {
            if ($OnlineContactId && ($tblOnlineContact = OnlineContactDetails::useService()->getOnlineContactById($OnlineContactId))) {
                OnlineContactDetails::useService()->deleteOnlineContact($tblOnlineContact);
            }

            return new Success('Die E-Mail Adresse wurde erfolgreich gespeichert.')
                . self::pipelineLoadMailToPersonContent($PersonId)
                . ($OnlineContactId ? ApiContactDetails::pipelineLoadContactDetailsStageContent() : '')
                . self::pipelineClose();
        } else {
            return new Danger('Die E-Mail Adresse konnte nicht gespeichert werden.'); // . self::pipelineClose();
        }
    }

    /**
     * @param      $PersonId
     * @param      $ToPersonId
     * @param      $Address
     * @param      $Type
     * @param      $OnlineContactId
     *
     * @return Danger|string
     */
    public function saveEditMailToPersonModal($PersonId, $ToPersonId, $Address, $Type, $OnlineContactId)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToPerson = Mail::useService()->getMailToPersonById($ToPersonId))) {
            return new Danger('Die E-Mail Adresse wurde nicht gefunden', new Exclamation());
        }

        $IsAccountUserAlias = isset($Address['Alias']);
        $IsAccountRecoveryMail = isset($Address['IsRecoveryMail']);

        $mailAddress = str_replace(' ', '', $Address['Mail']);
        if (($form = Mail::useService()->checkFormMailToPerson($tblPerson, $mailAddress, $Type, $IsAccountUserAlias, $OnlineContactId, $tblToPerson))) {
            // display Errors on form
            return $this->getMailToPersonModal($form, $tblPerson, $ToPersonId, $OnlineContactId);
        }

        if (Mail::useService()->updateMailToPerson($tblToPerson, $mailAddress, $Type, $IsAccountUserAlias, $IsAccountRecoveryMail)) {
            if ($OnlineContactId && ($tblOnlineContact = OnlineContactDetails::useService()->getOnlineContactById($OnlineContactId))) {
                OnlineContactDetails::useService()->deleteOnlineContact($tblOnlineContact);
            }

            return new Success('Die E-Mail Adresse wurde erfolgreich gespeichert.')
                . self::pipelineLoadMailToPersonContent($PersonId)
                . ($OnlineContactId ? ApiContactDetails::pipelineLoadContactDetailsStageContent() : '')
                . self::pipelineClose();
        } else {
            return new Danger('Die E-Mail Adresse konnte nicht gespeichert werden.'); // . self::pipelineClose();
        }
    }

    /**
     * @param $PersonId
     * @param $ToPersonId
     *
     * @return Danger|string
     */
    public function saveDeleteMailToPersonModal($PersonId, $ToPersonId)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToPerson = Mail::useService()->getMailToPersonById($ToPersonId))) {
            return new Danger('Die E-Mail Adresse wurde nicht gefunden', new Exclamation());
        }

        if (Mail::useService()->removeMailToPerson($tblToPerson)) {
            return new Success('Die E-Mail Adresse wurde erfolgreich gelöscht.')
                . self::pipelineLoadMailToPersonContent($PersonId)
                . self::pipelineClose();
        } else {
            return new Danger('Die E-Mail Adresse konnte nicht gelöscht werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $PersonId
     * @param $ToPersonId
     * @param $OnlineContactId
     *
     * @return Pipeline
     */
    public static function pipelineTransferMail($PersonId, $ToPersonId, $OnlineContactId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'transferMail',
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
    public function transferMail($PersonId, $ToPersonId, $OnlineContactId)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToPerson = Mail::useService()->getMailToPersonById($ToPersonId))) {
            return new Danger('Die E-Mail Adresse wurde nicht gefunden', new Exclamation());
        }

        return $this->getMailToPersonModal(Mail::useFrontend()->formAddressToPerson($PersonId, $ToPersonId, true, $OnlineContactId, true),
            $tblPerson, $ToPersonId, $OnlineContactId, true);
    }
}