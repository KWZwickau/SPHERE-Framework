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
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateAddressToPersonModal($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateAddressToPersonModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineCreateAddressToPersonSave($PersonId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateAddressToPersonModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
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
    public static function pipelineOpenEditAddressToPersonModal($PersonId, $ToPersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditAddressToPersonModal',
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
    public static function pipelineEditAddressToPersonSave($PersonId, $ToPersonId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditAddressToPersonModal'
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
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineLoadRelationshipsContent($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'RelationshipsContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadRelationshipsContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
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
     *
     * @return string
     */
    public function openCreateAddressToPersonModal($PersonId)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        return $this->getAddressToPersonModal(Address::useFrontend()->formAddressToPerson($PersonId), $tblPerson);
    }

    /**
     * @param $PersonId
     * @param $ToPersonId
     *
     * @return string
     */
    public function openEditAddressToPersonModal($PersonId, $ToPersonId)
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

        return $this->getAddressToPersonModal(Address::useFrontend()->formAddressToPerson($PersonId, $ToPersonId, true, $showRelationships), $tblPerson, $ToPersonId);
    }

    /**
     * @param $form
     * @param TblPerson $tblPerson
     * @param null $ToPersonId
     *
     * @return string
     */
    private function getAddressToPersonModal($form, TblPerson $tblPerson,  $ToPersonId = null)
    {
        if ($ToPersonId) {
            $title = new Title(new Edit() . ' Adresse bearbeiten');
        } else {
            $title = new Title(new Plus() . ' Adresse hinzufügen');
        }

        return $title
            . new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(
                            new LayoutColumn(
                                new Panel(new PersonIcon() . ' Person',
                                    new Bold($tblPerson ? $tblPerson->getFullName() : ''),
                                    Panel::PANEL_TYPE_SUCCESS

                                )
                            )
                        ),
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
    public function saveCreateAddressToPersonModal($PersonId, $Street, $City, $State, $Type, $County, $Nation, $Relationship)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (($form = Address::useService()->checkFormAddressToPerson($tblPerson, $Street, $City, $Type))) {
            // display Errors on form
            return $this->getAddressToPersonModal($form, $tblPerson);
        }

        if (Address::useService()->createAddressToPersonByApi($tblPerson, $Street, $City, $State, $Type, $County, $Nation)) {
            // Adresse für die ausgewählten Beziehungen speichern
            if (isset($Relationship)) {
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
                    }
                }
            }

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
     * @param $Street
     * @param $City
     * @param $State
     * @param $Type
     * @param $County
     * @param $Nation
     * @param $Relationship
     *
     * @return Danger|string
     */
    public function saveEditAddressToPersonModal($PersonId, $ToPersonId, $Street, $City, $State, $Type, $County, $Nation, $Relationship)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToPerson = Address::useService()->getAddressToPersonById($ToPersonId))) {
            return new Danger('Die Adresse wurde nicht gefunden', new Exclamation());
        }

        if (($form = Address::useService()->checkFormAddressToPerson($tblPerson, $Street, $City, $Type, $tblToPerson))) {
            // display Errors on form
            return $this->getAddressToPersonModal($form, $tblPerson, $ToPersonId);
        }

        if (Address::useService()->updateAddressToPersonByApi($tblToPerson, $Street, $City, $State, $Type, $County, $Nation)) {
            // Adresse für die ausgewählten Beziehungen speichern
            if (isset($Relationship)) {
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
                    }
                }
            }

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
     * @param $Type
     *
     * @return string
     */
    public function loadRelationshipsContent($PersonId, $Type)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (($tblType = Address::useService()->getTypeById($Type['Type']))
            && $tblType->getName() == 'Hauptadresse'
        ) {

            return Address::useFrontend()->getRelationshipsContent($tblPerson) . self::pipelineLoadRelationshipsMessage();
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
}