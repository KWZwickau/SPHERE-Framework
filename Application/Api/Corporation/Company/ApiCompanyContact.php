<?php

namespace SPHERE\Application\Api\Corporation\Company;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Extension\Extension;

class ApiCompanyContact extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('searchPerson');
        $Dispatcher->registerMethod('loadPerson');
        $Dispatcher->registerMethod('saveRelationship');

        return $Dispatcher->callMethod($Method);
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
     * @param $CompanyId
     *
     * @return Pipeline
     */
    public static function pipelineSearchPerson($CompanyId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SearchContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'searchPerson',
        ));
        $ModalEmitter->setPostPayload(array(
            'CompanyId' => $CompanyId,
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen.');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $CompanyId
     * @param null $Data
     *
     * @return string
     */
    public function searchPerson($CompanyId = null, $Data = null): string
    {
        return Company::useFrontend()->loadPersonSearch($CompanyId, isset($Data['Search']) ? trim($Data['Search']) : '');
    }

    /**
     * @param $CompanyId
     * @param null $Search
     *
     * @return Pipeline
     */
    public static function pipelineLoadPerson($CompanyId, $Search = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'LoadPerson'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadPerson',
        ));
        $ModalEmitter->setPostPayload(array(
            'CompanyId' => $CompanyId,
            'Search' => $Search
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen.');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $CompanyId
     * @param null $Search
     * @param null $Data
     *
     * @return string
     */
    public function loadPerson($CompanyId = null, $Search = null, $Data = null): string
    {
        return
            new Title(new PlusSign() . ' Neue Institution-Beziehung anlegen')
            . Company::useFrontend()->getCompanyContactForm(
                $CompanyId,
                isset($Data['SelectedPerson']) ? trim($Data['SelectedPerson']) : '',
                $Search,
                true
            );
    }

    /**
     * @param null $CompanyId
     * @param null $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineSaveRelationship($CompanyId = null, $PersonId = null): Pipeline
    {

        $pipeline = new Pipeline(true);

        $emitter = new ServerEmitter(self::receiverBlock('', 'LoadPerson'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'saveRelationship',
        ));
        $emitter->setPostPayload(array(
            'CompanyId' => $CompanyId,
            'PersonId' => $PersonId
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @param $CompanyId
     * @param $PersonId
     * @param $Data
     *
     * @return bool|string
     */
    public function saveRelationship($CompanyId = null, $PersonId = null, $Data = null): bool|string
    {
        if (!($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            return new Danger('Institution nicht gefunden', new Exclamation());
        }

        if (($form = Company::useService()->checkCompanyContact($CompanyId, $PersonId, $Data))) {
            // display Errors on form
            return new Title(new PlusSign() . ' Neue Institution-Beziehung anlegen') . $form;
        }

        if (($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            $tblSalutation = Person::useService()->getSalutationById($Data['SalutationId']);

            if ($PersonId) {
                if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
                    // feste Gruppe Ansprechpartner hinzufügen
                    Group::useService()->addGroupPerson(Group::useService()->getGroupByMetaTable('COMPANY_CONTACT'), $tblPerson);
                    // Update Titel, Anrede
                    Person::useService()->updatePerson($tblPerson, $tblSalutation ? $tblSalutation->getId() : null, $Data['Title'],
                        $tblPerson->getFirstName(), $tblPerson->getSecondName(), $tblPerson->getCallName(), $tblPerson->getLastName(), $tblPerson->getBirthName());
                }
            } else {
                $tblPerson = Person::useService()->insertPerson(
                    $tblSalutation ? $tblSalutation->getId() : null,
                    $Data['Title'],
                    $Data['FirstName'] ?: '.',
                    '',
                    $Data['LastName'],
                    array(
                        Group::useService()->getGroupByMetaTable('COMMON'),
                        Group::useService()->getGroupByMetaTable('COMPANY_CONTACT'),
                    )
                );
            }

            if ($tblPerson) {
                Relationship::useService()->addCompanyRelationshipToPerson(
                    $tblCompany, $tblPerson, Relationship::useService()->getTypeById($Data['TypeId']), $Data['Remark']
                );

                return new Success('Die Daten wurden erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                    . new Redirect('/Corporation/Company/Contact/Create', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblCompany->getId()));
            }
        }

        return new Danger('Die Daten konnten nicht gespeichert werden');
    }
}