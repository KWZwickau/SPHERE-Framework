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
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Frontend;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
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
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;

/**
 * Class ApiRelationshipToPerson
 *
 * @package SPHERE\Application\Api\Contact
 */
class ApiRelationshipToPerson  extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('loadRelationshipToPersonContent');

        $Dispatcher->registerMethod('openCreateRelationshipToPersonModal');
        $Dispatcher->registerMethod('saveCreateRelationshipToPersonModal');

        $Dispatcher->registerMethod('openEditRelationshipToPersonModal');
        $Dispatcher->registerMethod('saveEditRelationshipToPersonModal');

        $Dispatcher->registerMethod('openDeleteRelationshipToPersonModal');
        $Dispatcher->registerMethod('saveDeleteRelationshipToPersonModal');

        $Dispatcher->registerMethod('searchPerson');

        $Dispatcher->registerMethod('loadExtraOptions');

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
     * @return Pipeline
     */
    public static function pipelineSearchPerson()
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SearchPerson'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'searchPerson',
        ));
//        $Pipeline->setLoadingMessage('Bitte warten', 'Personen werden gesucht');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $Search
     *
     * @return string
     */
    public function searchPerson($Search = null)
    {

        $Search = trim($Search);
        return Relationship::useFrontend()->loadPersonSearch($Search);
    }

    /**
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineLoadRelationshipToPersonContent($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'RelationshipToPersonContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadRelationshipToPersonContent',
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
    public static function pipelineOpenCreateRelationshipToPersonModal($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateRelationshipToPersonModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
//        $ModalEmitter->setLoadingMessage('Bitte warten', 'Die Daten werden geladen');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineCreateRelationshipToPersonSave($PersonId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateRelationshipToPersonModal'
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
    public static function pipelineOpenEditRelationshipToPersonModal($PersonId, $ToPersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditRelationshipToPersonModal',
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
    public static function pipelineEditRelationshipToPersonSave($PersonId, $ToPersonId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditRelationshipToPersonModal'
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
    public static function pipelineOpenDeleteRelationshipToPersonModal($PersonId, $ToPersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDeleteRelationshipToPersonModal',
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
    public static function pipelineDeleteRelationshipToPersonSave($PersonId, $ToPersonId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveDeleteRelationshipToPersonModal'
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
     * @return Pipeline
     */
    public static function pipelineLoadExtraOptions()
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'ExtraOptions'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadExtraOptions',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function loadRelationshipToPersonContent($PersonId)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        return Relationship::useFrontend()->frontendLayoutPersonNew($tblPerson);
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function openCreateRelationshipToPersonModal($PersonId)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        return $this->getRelationshipToPersonModal(Relationship::useFrontend()->formRelationshipToPerson($PersonId), $tblPerson);
    }

    /**
     * @param $PersonId
     * @param $ToPersonId
     *
     * @return string
     */
    public function openEditRelationshipToPersonModal($PersonId, $ToPersonId)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToPerson = Relationship::useService()->getRelationshipToPersonById($ToPersonId))) {
            return new Danger('Die Personenbeziehung wurde nicht gefunden', new Exclamation());
        }

        return $this->getRelationshipToPersonModal(Relationship::useFrontend()->formRelationshipToPerson($PersonId, $ToPersonId, true), $tblPerson, $ToPersonId);
    }

    /**
     * @param $form
     * @param TblPerson $tblPerson
     * @param null $ToPersonId
     *
     * @return string
     */
    private function getRelationshipToPersonModal($form, TblPerson $tblPerson,  $ToPersonId = null)
    {
        if ($ToPersonId) {
            $title = new Title(new Edit() . ' Personenbeziehung bearbeiten');
        } else {
            $title = new Title(new Plus() . ' Personenbeziehung hinzufügen');
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
    public function openDeleteRelationshipToPersonModal($PersonId, $ToPersonId)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToPerson = Relationship::useService()->getRelationshipToPersonById($ToPersonId))) {
            return new Danger('Die Personenbeziehung wurde nicht gefunden', new Exclamation());
        }

        return new Title(new Remove() . ' Personenbeziehung löschen')
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(new PersonIcon() . ' Person',
                                new Bold($tblPerson->getFullName()),
                                Panel::PANEL_TYPE_SUCCESS
                            )
                            . new Panel(new Question() . ' Diese Personenbeziehung wirklich löschen?', array(
                                $tblToPerson->getTblType()->getName() . ' ' . $tblToPerson->getTblType()->getDescription(),
                                $tblToPerson->getServiceTblPersonTo() ? $tblToPerson->getServiceTblPersonTo()->getLastFirstName() : '',
                                ($tblToPerson->getRemark() ? new Muted(new Small($tblToPerson->getRemark())) : '')
                            ),
                                Panel::PANEL_TYPE_DANGER)
                            . (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteRelationshipToPersonSave($PersonId, $ToPersonId))
                            . (new Standard('Nein', self::getEndpoint(), new Remove()))
                                ->ajaxPipelineOnClick(self::pipelineClose())
                        )
                    )
                )
            );
    }

    /**
     * @param $PersonId
     * @param $Type
     * @param $To
     * @param $Search
     *
     * @return Danger|string
     */
    public function saveCreateRelationshipToPersonModal($PersonId, $Type, $To, $Search)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (($form = Relationship::useService()->checkFormRelationshipToPerson($tblPerson, $Type, $To, null, $Search))) {
            // display Errors on form
            return $this->getRelationshipToPersonModal($form, $tblPerson);
        }

        if (!($tblPersonTo = Person::useService()->getPersonById($To))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (Relationship::useService()->createRelationshipToPerson($tblPerson, $tblPersonTo, $Type)) {
            return new Success('Die Personenbeziehung wurde erfolgreich gespeichert.')
                . self::pipelineLoadRelationshipToPersonContent($PersonId)
                . self::pipelineClose();
        } else {
            return new Danger('Die Personenbeziehung konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $PersonId
     * @param $ToPersonId
     * @param $Type
     * @param $To
     * @param $Search
     *
     * @return string
     */
    public function saveEditRelationshipToPersonModal($PersonId, $ToPersonId, $Type, $To, $Search)
    {

        if (!($tblPersonFrom = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToPerson = Relationship::useService()->getRelationshipToPersonById($ToPersonId))) {
            return new Danger('Die Personenbeziehung wurde nicht gefunden', new Exclamation());
        }

        if (($form = Relationship::useService()->checkFormRelationshipToPerson($tblPersonFrom, $Type, $To, $tblToPerson, $Search))) {
            // display Errors on form
            return $this->getRelationshipToPersonModal($form, $tblPersonFrom, $ToPersonId);
        }

        if (!($tblPersonTo = Person::useService()->getPersonById($To))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (Relationship::useService()->updateRelationshipToPerson($tblToPerson, $tblPersonFrom, $tblPersonTo, $Type)) {
            return new Success('Die Personenbeziehung wurde erfolgreich gespeichert.')
                . self::pipelineLoadRelationshipToPersonContent($PersonId)
                . self::pipelineClose();
        } else {
            return new Danger('Die Personenbeziehung konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $PersonId
     * @param $ToPersonId
     *
     * @return Danger|string
     */
    public function saveDeleteRelationshipToPersonModal($PersonId, $ToPersonId)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToPerson = Relationship::useService()->getRelationshipToPersonById($ToPersonId))) {
            return new Danger('Die Personenbeziehung wurde nicht gefunden', new Exclamation());
        }

        if (Relationship::useService()->removePersonRelationshipToPerson($tblToPerson)) {
            return new Success('Die Personenbeziehung wurde erfolgreich gelöscht.')
                . self::pipelineLoadRelationshipToPersonContent($PersonId)
                . self::pipelineClose();
        } else {
            return new Danger('Die Personenbeziehung konnte nicht gelöscht werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $Type
     * @param $To
     *
     * @return Layout|null
     */
    public function loadExtraOptions($Type, $To)
    {
        if ($Type['Type'] == TblType::CHILD_ID
            || (($tblType = Relationship::useService()->getTypeById($Type['Type']))
                && $tblType->getName() == TblType::IDENTIFIER_GUARDIAN)
        ) {
            // todo Mandanteneinstellung was ist S1, Standard weiblich
            $post = null;
            if ($To
                && ($tblPersonTo = Person::useService()->getPersonById($To))
                && ($genderName = $tblPersonTo->getGenderNameFromGenderOrSalutation())
            ) {
                if ($genderName == 'Weiblich') {
                    $post = 1;
                } elseif ($genderName == 'Männlich') {
                    $post = 2;
                }
            }

            return (new Frontend())->loadExtraOptions(null, $post);
        }

        return null;
    }
}