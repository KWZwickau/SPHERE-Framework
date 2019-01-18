<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 10.01.2019
 * Time: 13:02
 */

namespace SPHERE\Application\Api\Contact;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
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
 * Class ApiRelationshipToCompany
 *
 * @package SPHERE\Application\Api\Contact
 */
class ApiRelationshipToCompany  extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('loadRelationshipToCompanyContent');

        $Dispatcher->registerMethod('openCreateRelationshipToCompanyModal');
        $Dispatcher->registerMethod('saveCreateRelationshipToCompanyModal');

        $Dispatcher->registerMethod('openEditRelationshipToCompanyModal');
        $Dispatcher->registerMethod('saveEditRelationshipToCompanyModal');

        $Dispatcher->registerMethod('openDeleteRelationshipToCompanyModal');
        $Dispatcher->registerMethod('saveDeleteRelationshipToCompanyModal');

        $Dispatcher->registerMethod('searchCompany');

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
    public static function pipelineSearchCompany()
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SearchCompany'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'searchCompany',
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
    public function searchCompany($Search = null)
    {

        $Search = trim($Search);
        return Relationship::useFrontend()->loadCompanySearch($Search);
    }

    /**
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineLoadRelationshipToCompanyContent($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'RelationshipToCompanyContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadRelationshipToCompanyContent',
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
    public static function pipelineOpenCreateRelationshipToCompanyModal($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateRelationshipToCompanyModal',
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
    public static function pipelineCreateRelationshipToCompanySave($PersonId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateRelationshipToCompanyModal'
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
     * @param $ToCompanyId
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditRelationshipToCompanyModal($PersonId, $ToCompanyId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditRelationshipToCompanyModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'ToCompanyId' => $ToCompanyId,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     * @param $ToCompanyId
     *
     * @return Pipeline
     */
    public static function pipelineEditRelationshipToCompanySave($PersonId, $ToCompanyId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditRelationshipToCompanyModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'ToCompanyId' => $ToCompanyId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $PersonId
     * @param $ToCompanyId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteRelationshipToCompanyModal($PersonId, $ToCompanyId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDeleteRelationshipToCompanyModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'ToCompanyId' => $ToCompanyId,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     * @param $ToCompanyId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteRelationshipToCompanySave($PersonId, $ToCompanyId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveDeleteRelationshipToCompanyModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'ToCompanyId' => $ToCompanyId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function loadRelationshipToCompanyContent($PersonId)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        return Relationship::useFrontend()->frontendLayoutCompanyNew($tblPerson);
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function openCreateRelationshipToCompanyModal($PersonId)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        return $this->getRelationshipToCompanyModal(Relationship::useFrontend()->formRelationshipToCompany($PersonId), $tblPerson);
    }

    /**
     * @param $PersonId
     * @param $ToCompanyId
     *
     * @return string
     */
    public function openEditRelationshipToCompanyModal($PersonId, $ToCompanyId)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToCompany = Relationship::useService()->getRelationshipToCompanyById($ToCompanyId))) {
            return new Danger('Die Institutionenbeziehung wurde nicht gefunden', new Exclamation());
        }

        return $this->getRelationshipToCompanyModal(Relationship::useFrontend()->formRelationshipToCompany($PersonId, $ToCompanyId, true), $tblPerson, $ToCompanyId);
    }

    /**
     * @param $form
     * @param TblPerson $tblPerson
     * @param null $ToCompanyId
     *
     * @return string
     */
    private function getRelationshipToCompanyModal($form, TblPerson $tblPerson,  $ToCompanyId = null)
    {
        if ($ToCompanyId) {
            $title = new Title(new Edit() . ' Institutionenbeziehung bearbeiten');
        } else {
            $title = new Title(new Plus() . ' Institutionenbeziehung hinzufügen');
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
     * @param $ToCompanyId
     *
     * @return string
     */
    public function openDeleteRelationshipToCompanyModal($PersonId, $ToCompanyId)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToCompany = Relationship::useService()->getRelationshipToCompanyById($ToCompanyId))) {
            return new Danger('Die Institutionenbeziehung wurde nicht gefunden', new Exclamation());
        }

        return new Title(new Remove() . ' Institutionenbeziehung löschen')
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(new PersonIcon() . ' Person',
                                new Bold($tblPerson->getFullName()),
                                Panel::PANEL_TYPE_SUCCESS
                            )
                            . new Panel(new Question() . ' Diese Institutionenbeziehung wirklich löschen?', array(
                                $tblToCompany->getTblType()->getName() . ' ' . $tblToCompany->getTblType()->getDescription(),
                                $tblToCompany->getServiceTblCompany() ? $tblToCompany->getServiceTblCompany()->getDisplayName() : '',
                                ($tblToCompany->getRemark() ? new Muted(new Small($tblToCompany->getRemark())) : '')
                            ),
                                Panel::PANEL_TYPE_DANGER)
                            . (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteRelationshipToCompanySave($PersonId, $ToCompanyId))
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
    public function saveCreateRelationshipToCompanyModal($PersonId, $Type, $To, $Search)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (($form = Relationship::useService()->checkFormRelationshipToCompany($tblPerson, $Type, $To, null, $Search))) {
            // display Errors on form
            return $this->getRelationshipToCompanyModal($form, $tblPerson);
        }

        if (!($tblCompany = Company::useService()->getCompanyById($To))) {
            return new Danger('Die Institution wurde nicht gefunden', new Exclamation());
        }

        if (Relationship::useService()->createRelationshipToCompany($tblPerson, $tblCompany, $Type)) {
            return new Success('Die Institutionenbeziehung wurde erfolgreich gespeichert.')
                . self::pipelineLoadRelationshipToCompanyContent($PersonId)
                . self::pipelineClose();
        } else {
            return new Danger('Die Institutionenbeziehung konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $PersonId
     * @param $ToCompanyId
     * @param $Type
     * @param $To
     * @param $Search
     *
     * @return string
     */
    public function saveEditRelationshipToCompanyModal($PersonId, $ToCompanyId, $Type, $To, $Search)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToCompany = Relationship::useService()->getRelationshipToCompanyById($ToCompanyId))) {
            return new Danger('Die Institutionenbeziehung wurde nicht gefunden', new Exclamation());
        }

        if (($form = Relationship::useService()->checkFormRelationshipToCompany($tblPerson, $Type, $To, $tblToCompany, $Search))) {
            // display Errors on form
            return $this->getRelationshipToCompanyModal($form, $tblPerson, $ToCompanyId);
        }

        if (!($tblCompany = Company::useService()->getCompanyById($To))) {
            return new Danger('Die Institution wurde nicht gefunden', new Exclamation());
        }

        if (Relationship::useService()->updateRelationshipToCompany($tblToCompany, $tblPerson, $tblCompany, $Type)) {
            return new Success('Die Institutionenbeziehung wurde erfolgreich gespeichert.')
                . self::pipelineLoadRelationshipToCompanyContent($PersonId)
                . self::pipelineClose();
        } else {
            return new Danger('Die Institutionenbeziehung konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $PersonId
     * @param $ToCompanyId
     *
     * @return Danger|string
     */
    public function saveDeleteRelationshipToCompanyModal($PersonId, $ToCompanyId)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToCompany = Relationship::useService()->getRelationshipToCompanyById($ToCompanyId))) {
            return new Danger('Die Institutionenbeziehung wurde nicht gefunden', new Exclamation());
        }

        if (Relationship::useService()->removeCompanyRelationshipToPerson($tblToCompany)) {
            return new Success('Die Institutionenbeziehung wurde erfolgreich gelöscht.')
                . self::pipelineLoadRelationshipToCompanyContent($PersonId)
                . self::pipelineClose();
        } else {
            return new Danger('Die Institutionenbeziehung konnte nicht gelöscht werden.') . self::pipelineClose();
        }
    }
}