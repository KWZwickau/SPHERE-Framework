<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 07.01.2019
 * Time: 15:42
 */

namespace SPHERE\Application\Api\Contact;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
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
 * Class ApiAddressToCompany
 */
class ApiAddressToCompany  extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('loadAddressToCompanyContent');

        $Dispatcher->registerMethod('openCreateAddressToCompanyModal');
        $Dispatcher->registerMethod('saveCreateAddressToCompanyModal');

        $Dispatcher->registerMethod('openEditAddressToCompanyModal');
        $Dispatcher->registerMethod('saveEditAddressToCompanyModal');

        $Dispatcher->registerMethod('openDeleteAddressToCompanyModal');
        $Dispatcher->registerMethod('saveDeleteAddressToCompanyModal');

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
     * @param int $CompanyId
     *
     * @return Pipeline
     */
    public static function pipelineLoadAddressToCompanyContent($CompanyId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'AddressToCompanyContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadAddressToCompanyContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'CompanyId' => $CompanyId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $CompanyId
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateAddressToCompanyModal($CompanyId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateAddressToCompanyModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'CompanyId' => $CompanyId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $CompanyId
     *
     * @return Pipeline
     */
    public static function pipelineCreateAddressToCompanySave($CompanyId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateAddressToCompanyModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'CompanyId' => $CompanyId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $CompanyId
     * @param $ToCompanyId
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditAddressToCompanyModal($CompanyId, $ToCompanyId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditAddressToCompanyModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'CompanyId' => $CompanyId,
            'ToCompanyId' => $ToCompanyId,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $CompanyId
     * @param $ToCompanyId
     *
     * @return Pipeline
     */
    public static function pipelineEditAddressToCompanySave($CompanyId, $ToCompanyId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditAddressToCompanyModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'CompanyId' => $CompanyId,
            'ToCompanyId' => $ToCompanyId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $CompanyId
     * @param $ToCompanyId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteAddressToCompanyModal($CompanyId, $ToCompanyId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDeleteAddressToCompanyModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'CompanyId' => $CompanyId,
            'ToCompanyId' => $ToCompanyId,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $CompanyId
     * @param $ToCompanyId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteAddressToCompanySave($CompanyId, $ToCompanyId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveDeleteAddressToCompanyModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'CompanyId' => $CompanyId,
            'ToCompanyId' => $ToCompanyId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    public function loadAddressToCompanyContent($CompanyId)
    {
        if (!($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            return new Danger('Die Company wurde nicht gefunden', new Exclamation());
        }

        return Address::useFrontend()->frontendLayoutCompanyNew($tblCompany);
    }

    /**
     * @param $CompanyId
     *
     * @return string
     */
    public function openCreateAddressToCompanyModal($CompanyId)
    {

        if (!($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            return new Danger('Die Company wurde nicht gefunden', new Exclamation());
        }

        return $this->getAddressToCompanyModal(Address::useFrontend()->formAddressToCompany($CompanyId), $tblCompany);
    }

    /**
     * @param $CompanyId
     * @param $ToCompanyId
     *
     * @return string
     */
    public function openEditAddressToCompanyModal($CompanyId, $ToCompanyId)
    {

        if (!($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            return new Danger('Die Company wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToCompany = Address::useService()->getAddressToCompanyById($ToCompanyId))) {
            return new Danger('Die Adresse wurde nicht gefunden', new Exclamation());
        }

        return $this->getAddressToCompanyModal(Address::useFrontend()->formAddressToCompany($CompanyId, $ToCompanyId), $tblCompany, $ToCompanyId);
    }

    /**
     * @param $form
     * @param TblCompany $tblCompany
     * @param null $ToCompanyId
     *
     * @return string
     */
    private function getAddressToCompanyModal($form, TblCompany $tblCompany,  $ToCompanyId = null)
    {
        if ($ToCompanyId) {
            $title = new Title(new Edit() . ' Adresse bearbeiten');
        } else {
            $title = new Title(new Plus() . ' Adresse hinzufügen');
        }

        return $title
            . new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(
                            new LayoutColumn(
                                new Panel(new Building() . ' Institution',
                                    new Bold($tblCompany ? $tblCompany->getDisplayName() : ''),
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
     * @param $CompanyId
     * @param $ToCompanyId
     *
     * @return string
     */
    public function openDeleteAddressToCompanyModal($CompanyId, $ToCompanyId)
    {

        if (!($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            return new Danger('Die Company wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToCompany = Address::useService()->getAddressToCompanyById($ToCompanyId))) {
            return new Danger('Die Adresse wurde nicht gefunden', new Exclamation());
        }

        return new Title(new Remove() . ' Adresse löschen')
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(new Building() . ' Institution',
                                new Bold($tblCompany->getDisplayName()),
                                Panel::PANEL_TYPE_SUCCESS
                            )
                            . new Panel(new Question() . ' Diese Adresse wirklich löschen?', array(
                                $tblToCompany->getTblType()->getName() . ' ' . $tblToCompany->getTblType()->getDescription(),
                                new AddressLayout($tblToCompany->getTblAddress()),
                                ($tblToCompany->getRemark() ? new Muted(new Small($tblToCompany->getRemark())) : '')
                            ),
                                Panel::PANEL_TYPE_DANGER)
                            . (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteAddressToCompanySave($CompanyId, $ToCompanyId))
                            . (new Standard('Nein', self::getEndpoint(), new Remove()))
                                ->ajaxPipelineOnClick(self::pipelineClose())
                        )
                    )
                )
            );
    }

    /**
     * @param $CompanyId
     * @param $Street
     * @param $City
     * @param $State
     * @param $Type
     * @param $County
     * @param $Nation
     *
     * @return bool|\SPHERE\Common\Frontend\Form\Structure\Form|Danger|string
     */
    public function saveCreateAddressToCompanyModal($CompanyId, $Street, $City, $State, $Type, $County, $Nation)
    {

        if (!($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            return new Danger('Die Company wurde nicht gefunden', new Exclamation());
        }

        if (($form = Address::useService()->checkFormAddressToCompany($tblCompany, $Street, $City, $Type))) {
            // display Errors on form
            return $this->getAddressToCompanyModal($form, $tblCompany);
        }

        if (Address::useService()->createAddressToCompanyByApi($tblCompany, $Street, $City, $State, $Type, $County, $Nation)) {
            return new Success('Die Adresse wurde erfolgreich gespeichert.')
                . self::pipelineLoadAddressToCompanyContent($CompanyId)
                . self::pipelineClose();
        } else {
            return new Danger('Die Adresse konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $CompanyId
     * @param $ToCompanyId
     * @param $Street
     * @param $City
     * @param $State
     * @param $Type
     * @param $County
     * @param $Nation
     *
     * @return Danger|string
     */
    public function saveEditAddressToCompanyModal($CompanyId, $ToCompanyId, $Street, $City, $State, $Type, $County, $Nation)
    {

        if (!($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            return new Danger('Die Company wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToCompany = Address::useService()->getAddressToCompanyById($ToCompanyId))) {
            return new Danger('Die Adresse wurde nicht gefunden', new Exclamation());
        }

        if (($form = Address::useService()->checkFormAddressToCompany($tblCompany, $Street, $City, $Type, $tblToCompany->getTblAddress()))) {
            // display Errors on form
            return $this->getAddressToCompanyModal($form, $tblCompany, $ToCompanyId);
        }

        if (Address::useService()->updateAddressToCompanyByApi($tblToCompany, $Street, $City, $State, $Type, $County, $Nation)) {
            return new Success('Die Adresse wurde erfolgreich gespeichert.')
                . self::pipelineLoadAddressToCompanyContent($CompanyId)
                . self::pipelineClose();
        } else {
            return new Danger('Die Adresse konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $CompanyId
     * @param $ToCompanyId
     *
     * @return Danger|string
     */
    public function saveDeleteAddressToCompanyModal($CompanyId, $ToCompanyId)
    {

        if (!($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            return new Danger('Die Company wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToCompany = Address::useService()->getAddressToCompanyById($ToCompanyId))) {
            return new Danger('Die Adresse wurde nicht gefunden', new Exclamation());
        }

        if (Address::useService()->removeAddressToCompany($tblToCompany)) {
            return new Success('Die Adresse wurde erfolgreich gelöscht.')
                . self::pipelineLoadAddressToCompanyContent($CompanyId)
                . self::pipelineClose();
        } else {
            return new Danger('Die Adresse konnte nicht gelöscht werden.') . self::pipelineClose();
        }
    }
}