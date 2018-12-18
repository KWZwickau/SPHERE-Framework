<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 17.12.2018
 * Time: 15:50
 */

namespace SPHERE\Application\Api\Corporation\Company;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\FrontendReadOnly;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiCompanyEdit
 *
 * @package SPHERE\Application\Api\Corporation\Company
 */
class ApiCompanyEdit extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('saveCreateCompanyContent');

        $Dispatcher->registerMethod('editBasicContent');
        $Dispatcher->registerMethod('saveBasicContent');

        return $Dispatcher->callMethod($Method);
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
    public static function pipelineSaveCreateCompanyContent()
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'CompanyContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateCompanyContent',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $CompanyId
     *
     * @return Pipeline
     */
    public static function pipelineEditBasicContent($CompanyId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'BasicContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'editBasicContent',
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
    public static function pipelineSaveBasicContent($CompanyId)
    {

        $pipeline = new Pipeline(true);

        $emitter = new ServerEmitter(self::receiverBlock('', 'BasicContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'saveBasicContent',
        ));
        $emitter->setPostPayload(array(
            'CompanyId' => $CompanyId
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @param int $CompanyId
     *
     * @return Pipeline
     */
    public static function pipelineCancelBasicContent($CompanyId)
    {
        $pipeline = new Pipeline(true);

        // Grunddaten neu laden
        $emitter = new ServerEmitter(ApiCompanyReadOnly::receiverBlock('', 'BasicContent'), ApiCompanyReadOnly::getEndpoint());
        $emitter->setGetPayload(array(
            ApiCompanyReadOnly::API_TARGET => 'loadBasicContent',
        ));
        $emitter->setPostPayload(array(
            'CompanyId' => $CompanyId
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @return bool|Well|string
     */
    public function saveCreateCompanyContent()
    {

        $Global = $this->getGlobal();
        $Company = $Global->POST['Company'];
        if (($form = (new FrontendReadOnly())->checkInputCreateCompanyContent($Company))) {
            // display Errors on form
            return $form;
        }

        if (($tblCompany = Company::useService()->createCompanyService($Company))) {

            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Institution wurde erfolgreich erstellt')
                . new Redirect('/Corporation/Company', Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $tblCompany->getId())
                );
        } else {
            return new Danger(new Ban() . ' Die Institution konnte nicht erstellt werden')
                . new Redirect('/Corporation/Company', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param null $CompanyId
     *
     * @return string
     */
    public function editBasicContent($CompanyId = null)
    {

        return (new FrontendReadOnly())->getEditBasicContent($CompanyId);
    }

    /**
     * @param $CompanyId
     *
     * @return bool|Danger|string
     */
    public function saveBasicContent($CompanyId)
    {
        if (!($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            return new Danger('Institution nicht gefunden', new Exclamation());
        }

        $Global = $this->getGlobal();
        $Company = $Global->POST['Company'];
        if (($form = (new FrontendReadOnly())->checkInputBasicContent($tblCompany, $Company))) {
            // display Errors on form
            return $form;
        }

        if (Company::useService()->updateCompanyService($tblCompany, $Company)) {
            return new Success('Die Daten wurden erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . ApiCompanyReadOnly::pipelineLoadBasicContent($CompanyId);
        } else {
            return new Danger('Die Daten konnten nicht gespeichert werden');
        }
    }
}