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
 * Class ApiPhoneToCompany
 *
 * @package SPHERE\Application\Api\Contact
 */
class ApiPhoneToCompany extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('loadPhoneToCompanyContent');

        $Dispatcher->registerMethod('openCreatePhoneToCompanyModal');
        $Dispatcher->registerMethod('saveCreatePhoneToCompanyModal');

        $Dispatcher->registerMethod('openEditPhoneToCompanyModal');
        $Dispatcher->registerMethod('saveEditPhoneToCompanyModal');

        $Dispatcher->registerMethod('openDeletePhoneToCompanyModal');
        $Dispatcher->registerMethod('saveDeletePhoneToCompanyModal');

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
    public static function pipelineLoadPhoneToCompanyContent($CompanyId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'PhoneToCompanyContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadPhoneToCompanyContent',
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
    public static function pipelineOpenCreatePhoneToCompanyModal($CompanyId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreatePhoneToCompanyModal',
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
    public static function pipelineCreatePhoneToCompanySave($CompanyId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreatePhoneToCompanyModal'
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
    public static function pipelineOpenEditPhoneToCompanyModal($CompanyId, $ToCompanyId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditPhoneToCompanyModal',
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
    public static function pipelineEditPhoneToCompanySave($CompanyId, $ToCompanyId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditPhoneToCompanyModal'
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
    public static function pipelineOpenDeletePhoneToCompanyModal($CompanyId, $ToCompanyId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDeletePhoneToCompanyModal',
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
    public static function pipelineDeletePhoneToCompanySave($CompanyId, $ToCompanyId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveDeletePhoneToCompanyModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'CompanyId' => $CompanyId,
            'ToCompanyId' => $ToCompanyId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    public function loadPhoneToCompanyContent($CompanyId)
    {
        if (!($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            return new Danger('Die Institution wurde nicht gefunden', new Exclamation());
        }

        return Phone::useFrontend()->frontendLayoutCompanyNew($tblCompany);
    }

    /**
     * @param $CompanyId
     *
     * @return string
     */
    public function openCreatePhoneToCompanyModal($CompanyId)
    {

        if (!($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            return new Danger('Die Institution wurde nicht gefunden', new Exclamation());
        }

        return $this->getPhoneToCompanyModal(Phone::useFrontend()->formNumberToCompany($CompanyId), $tblCompany);
    }

    /**
     * @param $CompanyId
     * @param $ToCompanyId
     *
     * @return string
     */
    public function openEditPhoneToCompanyModal($CompanyId, $ToCompanyId)
    {

        if (!($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            return new Danger('Die Institution wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToCompany = Phone::useService()->getPhoneToCompanyById($ToCompanyId))) {
            return new Danger('Die Adresse wurde nicht gefunden', new Exclamation());
        }

        return $this->getPhoneToCompanyModal(Phone::useFrontend()->formNumberToCompany($CompanyId, $ToCompanyId, true), $tblCompany, $ToCompanyId);
    }

    /**
     * @param $form
     * @param TblCompany $tblCompany
     * @param null $ToCompanyId
     *
     * @return string
     */
    private function getPhoneToCompanyModal($form, TblCompany $tblCompany,  $ToCompanyId = null)
    {
        if ($ToCompanyId) {
            $title = new Title(new Edit() . ' Telefonnummer bearbeiten');
        } else {
            $title = new Title(new Plus() . ' Telefonnummer hinzufügen');
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
    public function openDeletePhoneToCompanyModal($CompanyId, $ToCompanyId)
    {

        if (!($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            return new Danger('Die Institution wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToCompany = Phone::useService()->getPhoneToCompanyById($ToCompanyId))) {
            return new Danger('Die Telefonnummer wurde nicht gefunden', new Exclamation());
        }

        return new Title(new Remove() . ' Telefonnummer löschen')
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(new Building() . ' Institution',
                                new Bold($tblCompany->getDisplayName()),
                                Panel::PANEL_TYPE_SUCCESS
                            )
                            . new Panel(new Question() . ' Diese Telefonnummer wirklich löschen?', array(
                                $tblToCompany->getTblType()->getName() . ' ' . $tblToCompany->getTblType()->getDescription(),
                                ($tblPhone = $tblToCompany->getTblPhone()) ? $tblPhone->getNumber() : '',
                                ($tblToCompany->getRemark() ? new Muted(new Small($tblToCompany->getRemark())) : '')
                            ),
                                Panel::PANEL_TYPE_DANGER)
                            . (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeletePhoneToCompanySave($CompanyId, $ToCompanyId))
                            . (new Standard('Nein', self::getEndpoint(), new Remove()))
                                ->ajaxPipelineOnClick(self::pipelineClose())
                        )
                    )
                )
            );
    }

    /**
     * @param $CompanyId
     * @param $Number
     * @param $Type
     *
     * @return Danger|string
     */
    public function saveCreatePhoneToCompanyModal($CompanyId, $Number, $Type)
    {

        if (!($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            return new Danger('Die Institution wurde nicht gefunden', new Exclamation());
        }

        if (($form = Phone::useService()->checkFormPhoneToCompany($tblCompany, $Number, $Type))) {
            // display Errors on form
            return $this->getPhoneToCompanyModal($form, $tblCompany);
        }

        if (Phone::useService()->createPhoneToCompany($tblCompany, $Number, $Type)) {
            return new Success('Die Telefonnummer wurde erfolgreich gespeichert.')
                . self::pipelineLoadPhoneToCompanyContent($CompanyId)
                . self::pipelineClose();
        } else {
            return new Danger('Die Telefonnummer konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $CompanyId
     * @param $ToCompanyId
     * @param $Number
     * @param $Type
     *
     * @return Danger|string
     */
    public function saveEditPhoneToCompanyModal($CompanyId, $ToCompanyId, $Number, $Type)
    {

        if (!($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            return new Danger('Die Institution wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToCompany = Phone::useService()->getPhoneToCompanyById($ToCompanyId))) {
            return new Danger('Die Telefonnummer wurde nicht gefunden', new Exclamation());
        }

        if (($form = Phone::useService()->checkFormPhoneToCompany($tblCompany, $Number, $Type, $tblToCompany))) {
            // display Errors on form
            return $this->getPhoneToCompanyModal($form, $tblCompany, $ToCompanyId);
        }

        if (Phone::useService()->updatePhoneToCompany($tblToCompany, $Number, $Type)) {
            return new Success('Die Telefonnummer wurde erfolgreich gespeichert.')
                . self::pipelineLoadPhoneToCompanyContent($CompanyId)
                . self::pipelineClose();
        } else {
            return new Danger('Die Telefonnummer konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $CompanyId
     * @param $ToCompanyId
     *
     * @return Danger|string
     */
    public function saveDeletePhoneToCompanyModal($CompanyId, $ToCompanyId)
    {

        if (!($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            return new Danger('Die Institution wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToCompany = Phone::useService()->getPhoneToCompanyById($ToCompanyId))) {
            return new Danger('Die Telefonnummer wurde nicht gefunden', new Exclamation());
        }

        if (Phone::useService()->removePhoneToCompany($tblToCompany)) {
            return new Success('Die Telefonnummer wurde erfolgreich gelöscht.')
                . self::pipelineLoadPhoneToCompanyContent($CompanyId)
                . self::pipelineClose();
        } else {
            return new Danger('Die Telefonnummer konnte nicht gelöscht werden.') . self::pipelineClose();
        }
    }
}