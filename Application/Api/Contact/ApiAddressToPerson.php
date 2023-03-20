<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 04.01.2019
 * Time: 13:08
 */

namespace SPHERE\Application\Api\Contact;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Contact\Address\Address;
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
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
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
use SPHERE\Common\Frontend\Layout\Repository\Address as AddressLayout;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;

/**
 * Class ApiAddressToPerson
 *
 * @package SPHERE\Application\Api\Contact
 */
class ApiAddressToPerson  extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('loadAddressToPersonContent');

        $Dispatcher->registerMethod('openCreateAddressToPersonModal');
        $Dispatcher->registerMethod('saveCreateAddressToPersonModal');

        $Dispatcher->registerMethod('openEditAddressToPersonModal');
        $Dispatcher->registerMethod('saveEditAddressToPersonModal');

        $Dispatcher->registerMethod('openDeleteAddressToPersonModal');
        $Dispatcher->registerMethod('saveDeleteAddressToPersonModal');

        $Dispatcher->registerMethod('loadRelationshipsContent');
        $Dispatcher->registerMethod('loadRelationshipsMessage');

        $Dispatcher->registerMethod('addAddressToPerson');

        $Dispatcher->registerMethod('openAddAddressToPersonModal');
        $Dispatcher->registerMethod('saveAddAddressToPersonModal');

        $Dispatcher->registerMethod('transferAddress');

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
    public static function pipelineLoadAddressToPersonContent($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'AddressToPersonContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadAddressToPersonContent',
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
    public static function pipelineOpenCreateAddressToPersonModal($PersonId, $OnlineContactId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateAddressToPersonModal',
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
    public static function pipelineCreateAddressToPersonSave($PersonId, $OnlineContactId): Pipeline
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateAddressToPersonModal'
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
    public static function pipelineOpenEditAddressToPersonModal($PersonId, $ToPersonId, $OnlineContactId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditAddressToPersonModal',
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
    public static function pipelineEditAddressToPersonSave($PersonId, $ToPersonId, $OnlineContactId): Pipeline
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditAddressToPersonModal'
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
    public static function pipelineOpenDeleteAddressToPersonModal($PersonId, $ToPersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDeleteAddressToPersonModal',
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
    public static function pipelineDeleteAddressToPersonSave($PersonId, $ToPersonId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveDeleteAddressToPersonModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'ToPersonId' => $ToPersonId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     * @param $OnlineContactId
     *
     * @return Pipeline
     */
    public static function pipelineLoadRelationshipsContent($PersonId, $OnlineContactId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'RelationshipsContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadRelationshipsContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'OnlineContactId' => $OnlineContactId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoadRelationshipsMessage()
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'RelationshipsMessage'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadRelationshipsMessage',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $PersonId
     * @param int $ToPersonId
     *
     * @return Pipeline
     */
    public static function pipelineAddAddressToPerson($PersonId, $ToPersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'AddressToPersonContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'addAddressToPerson',
        ));
        $ModalEmitter->setLoadingMessage('Die Adresse wird hinzugefügt.');
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'ToPersonId' => $ToPersonId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $PersonId
     * @param int $ToPersonId
     * @return Pipeline
     */
    public static function pipelineOpenAddAddressToPersonModal($PersonId, $ToPersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openAddAddressToPersonModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'ToPersonId' => $ToPersonId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $PersonId
     * @param int $ToPersonId
     *
     * @return Pipeline
     */
    public static function pipelineAddAddressToPersonSave($PersonId, $ToPersonId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveAddAddressToPersonModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'ToPersonId' => $ToPersonId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     *
     * @return Danger|string
     */
    public function loadAddressToPersonContent($PersonId)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        return Address::useFrontend()->frontendLayoutPersonNew($tblPerson);
    }

    /**
     * @param $PersonId
     * @param $OnlineContactId
     *
     * @return string
     */
    public function openCreateAddressToPersonModal($PersonId, $OnlineContactId)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        return $this->getAddressToPersonModal(Address::useFrontend()->formAddressToPerson($PersonId, null, true, false, $OnlineContactId),
            $tblPerson, null, $OnlineContactId);
    }

    /**
     * @param $PersonId
     * @param $ToPersonId
     * @param $OnlineContactId
     *
     * @return string
     */
    public function openEditAddressToPersonModal($PersonId, $ToPersonId, $OnlineContactId)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToPerson = Address::useService()->getAddressToPersonById($ToPersonId))) {
            return new Danger('Die Adresse wurde nicht gefunden', new Exclamation());
        }

        if (($tblType = $tblToPerson->getTblType())
            && $tblType->getName() == 'Hauptadresse'
        ) {
            $showRelationships = true;
        } else {
            $showRelationships = false;
        }

        return $this->getAddressToPersonModal(Address::useFrontend()->formAddressToPerson($PersonId, $ToPersonId, true, $showRelationships, $OnlineContactId),
            $tblPerson, $ToPersonId, $OnlineContactId);
    }

    /**
     * @param $form
     * @param TblPerson $tblPerson
     * @param null $ToPersonId
     * @param null $OnlineContactId
     * @param bool $isAddressTransfer
     *
     * @return string
     */
    private function getAddressToPersonModal($form, TblPerson $tblPerson,  $ToPersonId = null, $OnlineContactId = null, $isAddressTransfer = false)
    {
        if ($ToPersonId) {
            $title = new Title(new Edit() . ' Adresse bearbeiten');
        } else {
            $title = new Title(new Plus() . ' Adresse hinzufügen');
        }

        if ($OnlineContactId && ($tblOnlineContact = OnlineContactDetails::useService()->getOnlineContactById($OnlineContactId))) {
            $columns[] = new LayoutColumn(new Panel(
                $tblOnlineContact->getContactTypeIcon() . ' ' . $tblOnlineContact->getContactTypeName()
                    . '  für ' . OnlineContactDetails::useService()->getPersonListForOnlineContact($tblOnlineContact, true) .  ') ',
                array(
                    'Adresse: ' . $tblOnlineContact->getContactContent(),
                    $tblOnlineContact->getContactCreate(),
                    $tblOnlineContact->getRemark() ? new Muted('Bemerkung vom Ersteller: ' . $tblOnlineContact->getRemark()) : '',
                    $ToPersonId && !$isAddressTransfer ? (new Primary('Adresse ins Formular übernehmen', self::getEndpoint(), new ChevronLeft()))->ajaxPipelineOnClick(
                        self::pipelineTransferAddress($tblPerson->getId(), $ToPersonId, $OnlineContactId)
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
    public function openDeleteAddressToPersonModal($PersonId, $ToPersonId)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToPerson = Address::useService()->getAddressToPersonById($ToPersonId))) {
            return new Danger('Die Adresse wurde nicht gefunden', new Exclamation());
        }

        return new Title(new Remove() . ' Adresse löschen')
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(new PersonIcon() . ' Person',
                                new Bold($tblPerson->getFullName()),
                                Panel::PANEL_TYPE_SUCCESS
                            )
                            . new Panel(new Question() . ' Diese Adresse wirklich löschen?', array(
                                $tblToPerson->getTblType()->getName() . ' ' . $tblToPerson->getTblType()->getDescription(),
                                new AddressLayout($tblToPerson->getTblAddress()),
                                ($tblToPerson->getRemark() ? new Muted(new Small($tblToPerson->getRemark())) : '')
                            ),
                                Panel::PANEL_TYPE_DANGER)
                            . (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteAddressToPersonSave($PersonId, $ToPersonId))
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
     * @param $Street
     * @param $City
     * @param $State
     * @param $Type
     * @param $County
     * @param $Nation
     * @param $Relationship
     *
     * @return bool|\SPHERE\Common\Frontend\Form\Structure\Form|Danger|string
     */
    public function saveCreateAddressToPersonModal($PersonId, $OnlineContactId, $Street, $City, $State, $Type, $County, $Nation, $Relationship)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (($form = Address::useService()->checkFormAddressToPerson($tblPerson, $Street, $City, $Type, $OnlineContactId))) {
            // display Errors on form
            return $this->getAddressToPersonModal($form, $tblPerson, null, $OnlineContactId);
        }

        if (Address::useService()->createAddressToPersonByApi($tblPerson, $Street, $City, $State, $Type, $County, $Nation)) {
            $tblOnlineContact = OnlineContactDetails::useService()->getOnlineContactById($OnlineContactId);

            // Adresse für die ausgewählten Beziehungen speichern
            if (isset($Relationship)) {
                if ($tblOnlineContact) {
                    $tblPersonOnlineContactList = OnlineContactDetails::useService()->getPersonListForOnlineContact($tblOnlineContact, false);
                    $tblContact = $tblOnlineContact->getServiceTblContact();
                } else {
                    $tblPersonOnlineContactList = false;
                    $tblContact = false;
                }

                foreach ($Relationship as $personId => $value) {
                    if (($tblPersonRelationship = Person::useService()->getPersonById($personId))) {
                        // vorhandene Hauptadresse überschreiben
                        if (($tblToPerson = Address::useService()->getAddressToPersonByPerson($tblPersonRelationship))) {
                            Address::useService()->updateAddressToPersonByApi(
                                $tblToPerson,
                                $Street,
                                $City,
                                $State,
                                $Type,
                                '',
                                $County,
                                $Nation
                            );
                        // neue Hauptadresse anlegen
                        } else {
                            Address::useService()->createAddressToPersonByApi(
                                $tblPersonRelationship,
                                $Street,
                                $City,
                                $State,
                                $Type,
                                $County,
                                $Nation
                            );
                        }

                        if ($tblOnlineContact && $tblContact && $tblPersonOnlineContactList
                            && isset($tblPersonOnlineContactList[$tblPersonRelationship->getId()])
                            && ($tblOnlineContactRelationship = OnlineContactDetails::useService()->getOnlineContactByContactAndPerson(
                                $tblContact, $tblOnlineContact->getContactType(), $tblPersonRelationship
                            ))
                        ) {
                            OnlineContactDetails::useService()->deleteOnlineContact($tblOnlineContactRelationship);
                        }
                    }
                }
            }

            if ($tblOnlineContact) {
                OnlineContactDetails::useService()->deleteOnlineContact($tblOnlineContact);
            }

            return new Success('Die Adresse wurde erfolgreich gespeichert.')
                . self::pipelineLoadAddressToPersonContent($PersonId)
                . ($OnlineContactId ? ApiContactDetails::pipelineLoadContactDetailsStageContent() : '')
                . self::pipelineClose();
        } else {
            return new Danger('Die Adresse konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $PersonId
     * @param $ToPersonId
     * @param $Street
     * @param $City
     * @param $State
     * @param $Type
     * @param $County
     * @param $Nation
     * @param $Relationship
     * @param $OnlineContactId
     * @param string $Region
     *
     * @return Danger|string
     */
    public function saveEditAddressToPersonModal($PersonId, $ToPersonId, $Street, $City, $State, $Type, $County, $Nation, $Relationship, $OnlineContactId, $Region = '')
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToPerson = Address::useService()->getAddressToPersonById($ToPersonId))) {
            return new Danger('Die Adresse wurde nicht gefunden', new Exclamation());
        }

        if (($form = Address::useService()->checkFormAddressToPerson($tblPerson, $Street, $City, $Type, $OnlineContactId, $tblToPerson))) {
            // display Errors on form
            return $this->getAddressToPersonModal($form, $tblPerson, $ToPersonId, $OnlineContactId);
        }

        if (Address::useService()->updateAddressToPersonByApi($tblToPerson, $Street, $City, $State, $Type, $Region, $County, $Nation)) {
            $tblOnlineContact = OnlineContactDetails::useService()->getOnlineContactById($OnlineContactId);

            // Adresse für die ausgewählten Beziehungen speichern
            if (isset($Relationship)) {
                if ($tblOnlineContact) {
                    $tblPersonOnlineContactList = OnlineContactDetails::useService()->getPersonListForOnlineContact($tblOnlineContact, false);
                    $tblContact = $tblOnlineContact->getServiceTblContact();
                } else {
                    $tblPersonOnlineContactList = false;
                    $tblContact = false;
                }

                foreach ($Relationship as $personId => $value) {
                    if (($tblPersonRelationship = Person::useService()->getPersonById($personId))) {
                        // vorhandene Hauptadresse überschreiben
                        if (($tblToPersonRelationship = Address::useService()->getAddressToPersonByPerson($tblPersonRelationship))) {
                            Address::useService()->updateAddressToPersonByApi(
                                $tblToPersonRelationship,
                                $Street,
                                $City,
                                $State,
                                $Type,
                                $Region,
                                $County,
                                $Nation
                            );
                            // neue Hauptadresse anlegen
                        } else {
                            Address::useService()->createAddressToPersonByApi(
                                $tblPersonRelationship,
                                $Street,
                                $City,
                                $State,
                                $Type,
                                $County,
                                $Nation
                            );
                        }

                        if ($tblOnlineContact && $tblContact && $tblPersonOnlineContactList
                            && isset($tblPersonOnlineContactList[$tblPersonRelationship->getId()])
                            && ($tblOnlineContactRelationship = OnlineContactDetails::useService()->getOnlineContactByContactAndPerson(
                                $tblContact, $tblOnlineContact->getContactType(), $tblPersonRelationship
                            ))
                        ) {
                            OnlineContactDetails::useService()->deleteOnlineContact($tblOnlineContactRelationship);
                        }
                    }
                }
            }

            if ($tblOnlineContact) {
                OnlineContactDetails::useService()->deleteOnlineContact($tblOnlineContact);
            }

            return new Success('Die Adresse wurde erfolgreich gespeichert.')
                . self::pipelineLoadAddressToPersonContent($PersonId)
                . ($OnlineContactId ? ApiContactDetails::pipelineLoadContactDetailsStageContent() : '')
                . self::pipelineClose();
        } else {
            return new Danger('Die Adresse konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $PersonId
     * @param $ToPersonId
     *
     * @return Danger|string
     */
    public function saveDeleteAddressToPersonModal($PersonId, $ToPersonId)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToPerson = Address::useService()->getAddressToPersonById($ToPersonId))) {
            return new Danger('Die Adresse wurde nicht gefunden', new Exclamation());
        }

        if (Address::useService()->removeAddressToPerson($tblToPerson)) {
            return new Success('Die Adresse wurde erfolgreich gelöscht.')
                . self::pipelineLoadAddressToPersonContent($PersonId)
                . self::pipelineClose();
        } else {
            return new Danger('Die Adresse konnte nicht gelöscht werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $PersonId
     * @param null $OnlineContactId
     * @param null $Type
     *
     * @return string
     */
    public function loadRelationshipsContent($PersonId, $OnlineContactId = null, $Type = null)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        $tblOnlineContact = OnlineContactDetails::useService()->getOnlineContactById($OnlineContactId);
        if (($tblType = Address::useService()->getTypeById($Type['Type']))
            && $tblType->getName() == 'Hauptadresse'
        ) {

            return Address::useFrontend()->getRelationshipsContent($tblPerson, $tblOnlineContact ?: null) . self::pipelineLoadRelationshipsMessage();
        } else {
            return '' . self::pipelineLoadRelationshipsMessage();
        }
    }

    /**
     * @param $Relationship
     *
     * @return string
     */
    public function loadRelationshipsMessage($Relationship)
    {

        if ($Relationship) {
            foreach ($Relationship as $personId => $value) {
                if (($tblPerson = Person::useService()->getPersonById($personId))
                    && $tblPerson->fetchMainAddress()
                ) {
                    return new Danger('Möchten Sie die bestehende Hauptadresse von der in beziehungstehender Person überschreiben!',
                        new Exclamation());
                }
            }
        }

        return '';
    }

    /**
     * @param $PersonId
     * @param $ToPersonId
     *
     * @return string
     */
    public function addAddressToPerson($PersonId, $ToPersonId)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToPerson = Address::useService()->getAddressToPersonById($ToPersonId))) {
            return new Danger('Die Adresse wurde nicht gefunden', new Exclamation());
        }

        if (Address::useService()->addAddressToPerson(
            $tblPerson, $tblToPerson->getTblAddress(), $tblToPerson->getTblType(), $tblToPerson->getRemark()
        )) {
            return new Success('Die Adresse wurde erfolgreich gespeichert.')
              . self::pipelineLoadAddressToPersonContent($PersonId);
        } else {
            return new Danger('Die Adresse konnte nicht gespeichert werden.');
        }
    }

    /**
     * @param int $PersonId
     * @param int $ToPersonId
     *
     * @return string
     */
    public function openAddAddressToPersonModal($PersonId, $ToPersonId)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToPerson = Address::useService()->getAddressToPersonById($ToPersonId))) {
            return new Danger('Die Adresse wurde nicht gefunden', new Exclamation());
        }

        $tblAddress = $tblPerson->fetchMainAddress();
        $tblNewAddress = $tblToPerson->getTblAddress();
        $content[] = new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn(
                new Bold('Aktuelle Hauptadresse'), 6
            ),
            new LayoutColumn(
                new Bold('Neue Hauptadresse'), 6
            ),
        ))));
        $content[] = new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn(
                $tblAddress->getGuiString(), 6
            ),
            new LayoutColumn(
                $tblNewAddress ? $tblNewAddress->getGuiString() : '', 6
            ),
        ))));

        return new Title(new MapMarker() . ' Hauptadresse überschreiben')
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(new PersonIcon() . ' Person',
                                new Bold($tblPerson->getFullName()),
                                Panel::PANEL_TYPE_SUCCESS
                            )
                            . new Panel(
                                new Question() . ' Wollen sie die akutelle Hauptadresse wirklich überschreiben?',
                                $content,
                                Panel::PANEL_TYPE_DANGER)
                            . (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineAddAddressToPersonSave($PersonId, $ToPersonId))
                            . (new Standard('Nein', self::getEndpoint(), new Remove()))
                                ->ajaxPipelineOnClick(self::pipelineClose())
                        )
                    )
                )
            );
    }

    /**
     * @param int $PersonId
     * @param int $ToPersonId
     *
     * @return Danger|string
     */
    public function saveAddAddressToPersonModal($PersonId, $ToPersonId)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToPerson = Address::useService()->getAddressToPersonById($ToPersonId))) {
            return new Danger('Die Adresse wurde nicht gefunden', new Exclamation());
        }

        if (($tblMainToPerson = Address::useService()->getAddressToPersonByPerson($tblPerson))
            && Address::useService()->removeAddressToPerson($tblMainToPerson)
            && Address::useService()->addAddressToPerson(
                $tblPerson, $tblToPerson->getTblAddress(), $tblToPerson->getTblType(), $tblToPerson->getRemark()
            )
        ) {

            return new Success('Die Adresse wurde erfolgreich gespeichert.')
                . self::pipelineLoadAddressToPersonContent($PersonId)
                . self::pipelineClose();
        } else {
            return new Danger('Die Adresse konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $PersonId
     * @param $ToPersonId
     * @param $OnlineContactId
     *
     * @return Pipeline
     */
    public static function pipelineTransferAddress($PersonId, $ToPersonId, $OnlineContactId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'transferAddress',
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
    public function transferAddress($PersonId, $ToPersonId, $OnlineContactId)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToPerson = Address::useService()->getAddressToPersonById($ToPersonId))) {
            return new Danger('Die Adresse wurde nicht gefunden', new Exclamation());
        }

        return $this->getAddressToPersonModal(Address::useFrontend()->formAddressToPerson($PersonId, $ToPersonId, true, false, $OnlineContactId, true),
            $tblPerson, $ToPersonId, $OnlineContactId, true);
    }
}