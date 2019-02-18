<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 08.01.2019
 * Time: 14:16
 */

namespace SPHERE\Application\Api\Contact;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Contact\Web\Web;
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
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;

/**
 * Class ApiWebToCompany
 *
 * @package SPHERE\Application\Api\Contact
 */
class ApiWebToCompany extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('loadWebToCompanyContent');

        $Dispatcher->registerMethod('openCreateWebToCompanyModal');
        $Dispatcher->registerMethod('saveCreateWebToCompanyModal');

        $Dispatcher->registerMethod('openEditWebToCompanyModal');
        $Dispatcher->registerMethod('saveEditWebToCompanyModal');

        $Dispatcher->registerMethod('openDeleteWebToCompanyModal');
        $Dispatcher->registerMethod('saveDeleteWebToCompanyModal');

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
    public static function pipelineLoadWebToCompanyContent($CompanyId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'WebToCompanyContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadWebToCompanyContent',
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
    public static function pipelineOpenCreateWebToCompanyModal($CompanyId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateWebToCompanyModal',
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
    public static function pipelineCreateWebToCompanySave($CompanyId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateWebToCompanyModal'
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
    public static function pipelineOpenEditWebToCompanyModal($CompanyId, $ToCompanyId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditWebToCompanyModal',
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
    public static function pipelineEditWebToCompanySave($CompanyId, $ToCompanyId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditWebToCompanyModal'
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
    public static function pipelineOpenDeleteWebToCompanyModal($CompanyId, $ToCompanyId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDeleteWebToCompanyModal',
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
    public static function pipelineDeleteWebToCompanySave($CompanyId, $ToCompanyId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveDeleteWebToCompanyModal'
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
     * @param $CompanyId
     *
     * @return string
     */
    public function loadWebToCompanyContent($CompanyId)
    {
        if (!($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            return new Danger('Die Institution wurde nicht gefunden', new Exclamation());
        }

        return Web::useFrontend()->frontendLayoutCompanyNew($tblCompany);
    }

    /**
     * @param $CompanyId
     *
     * @return string
     */
    public function openCreateWebToCompanyModal($CompanyId)
    {

        if (!($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            return new Danger('Die Institution wurde nicht gefunden', new Exclamation());
        }

        return $this->getWebToCompanyModal(Web::useFrontend()->formAddressToCompany($CompanyId), $tblCompany);
    }

    /**
     * @param $CompanyId
     * @param $ToCompanyId
     *
     * @return string
     */
    public function openEditWebToCompanyModal($CompanyId, $ToCompanyId)
    {

        if (!($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            return new Danger('Die Institution wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToCompany = Web::useService()->getWebToCompanyById($ToCompanyId))) {
            return new Danger('Die Internet Adresse wurde nicht gefunden', new Exclamation());
        }

        return $this->getWebToCompanyModal(Web::useFrontend()->formAddressToCompany($CompanyId, $ToCompanyId, true), $tblCompany, $ToCompanyId);
    }

    /**
     * @param $form
     * @param TblCompany $tblCompany
     * @param null $ToCompanyId
     *
     * @return string
     */
    private function getWebToCompanyModal($form, TblCompany $tblCompany,  $ToCompanyId = null)
    {
        if ($ToCompanyId) {
            $title = new Title(new Edit() . ' Internet Adresse bearbeiten');
        } else {
            $title = new Title(new Plus() . ' Internet Adresse hinzufügen');
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
    public function openDeleteWebToCompanyModal($CompanyId, $ToCompanyId)
    {

        if (!($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            return new Danger('Die Institution wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToCompany = Web::useService()->getWebToCompanyById($ToCompanyId))) {
            return new Danger('Die Internet Adresse wurde nicht gefunden', new Exclamation());
        }

        return new Title(new Remove() . ' Internet Adresse löschen')
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(new Building() . ' Institution',
                                new Bold($tblCompany->getDisplayName()),
                                Panel::PANEL_TYPE_SUCCESS
                            )
                            . new Panel(new Question() . ' Diese Internet Adresse wirklich löschen?', array(
                                $tblToCompany->getTblType()->getName() . ' ' . $tblToCompany->getTblType()->getDescription(),
                                ($tblWeb = $tblToCompany->getTblWeb()) ? $tblWeb->getAddress() : '',
                                ($tblToCompany->getRemark() ? new Muted(new Small($tblToCompany->getRemark())) : '')
                            ),
                                Panel::PANEL_TYPE_DANGER)
                            . (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteWebToCompanySave($CompanyId, $ToCompanyId))
                            . (new Standard('Nein', self::getEndpoint(), new Remove()))
                                ->ajaxPipelineOnClick(self::pipelineClose())
                        )
                    )
                )
            );
    }

    /**
     * @param $CompanyId
     * @param $Address
     * @param $Type
     *
     * @return Danger|string
     */
    public function saveCreateWebToCompanyModal($CompanyId, $Address, $Type)
    {

        if (!($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            return new Danger('Die Institution wurde nicht gefunden', new Exclamation());
        }

        if (($form = Web::useService()->checkFormWebToCompany($tblCompany, $Address, $Type))) {
            // display Errors on form
            return $this->getWebToCompanyModal($form, $tblCompany);
        }

        if (Web::useService()->createWebToCompany($tblCompany, $Address, $Type)) {
            return new Success('Die Internet Adresse wurde erfolgreich gespeichert.')
                . self::pipelineLoadWebToCompanyContent($CompanyId)
                . self::pipelineClose();
        } else {
            return new Danger('Die Internet Adresse konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $CompanyId
     * @param $ToCompanyId
     * @param $Address
     * @param $Type
     *
     * @return Danger|string
     */
    public function saveEditWebToCompanyModal($CompanyId, $ToCompanyId, $Address, $Type)
    {

        if (!($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            return new Danger('Die Institution wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToCompany = Web::useService()->getWebToCompanyById($ToCompanyId))) {
            return new Danger('Die Internet Adresse wurde nicht gefunden', new Exclamation());
        }

        if (($form = Web::useService()->checkFormWebToCompany($tblCompany, $Address, $Type, $tblToCompany))) {
            // display Errors on form
            return $this->getWebToCompanyModal($form, $tblCompany, $ToCompanyId);
        }

        if (Web::useService()->updateWebToCompany($tblToCompany, $Address, $Type)) {
            return new Success('Die Internet Adresse wurde erfolgreich gespeichert.')
                . self::pipelineLoadWebToCompanyContent($CompanyId)
                . self::pipelineClose();
        } else {
            return new Danger('Die Internet Adresse konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $CompanyId
     * @param $ToCompanyId
     *
     * @return Danger|string
     */
    public function saveDeleteWebToCompanyModal($CompanyId, $ToCompanyId)
    {

        if (!($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            return new Danger('Die Institution wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToCompany = Web::useService()->getWebToCompanyById($ToCompanyId))) {
            return new Danger('Die Internet Adresse wurde nicht gefunden', new Exclamation());
        }

        if (Web::useService()->removeWebToCompany($tblToCompany)) {
            return new Success('Die Internet Adresse wurde erfolgreich gelöscht.')
                . self::pipelineLoadWebToCompanyContent($CompanyId)
                . self::pipelineClose();
        } else {
            return new Danger('Die Internet Adresse konnte nicht gelöscht werden.') . self::pipelineClose();
        }
    }
}