<?php
namespace SPHERE\Application\Api\Reporting\SerialLetter;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Reporting\SerialLetter\SerialLetter;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Success as SuccessIcon;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\System\Extension\Extension;

class ApiSerialLetter  extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('serviceChangeStatus');
        $Dispatcher->registerMethod('serviceDisplayChangeStatus');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     * @param        $ReceiverId
     *
     * @return BlockReceiver
     */
    public static function receiverCompanyStatus($Content = '', $ReceiverId)
    {

        return (new BlockReceiver($Content))->setIdentifier($ReceiverId);
    }

    /**
     * @param $Id
     *
     * @return Pipeline
     */
    public static function pipelineCompanyChangeStatus($Id)
    {
        $ComparePasswordPipeline = new Pipeline(true);
        $ComparePasswordEmitter = new ServerEmitter(self::receiverCompanyStatus('', $Id), ApiSerialLetter::getEndpoint());
        $ComparePasswordEmitter->setGetPayload(array(
            ApiSerialLetter::API_TARGET => 'serviceChangeStatus'
        ));
        $ComparePasswordEmitter->setPostPayload(array(
            'Id' => $Id
        ));
        $ComparePasswordPipeline->appendEmitter($ComparePasswordEmitter);

        $ComparePasswordEmitter = new ServerEmitter(self::receiverCompanyStatus('', $Id.'Display'), ApiSerialLetter::getEndpoint());
        $ComparePasswordEmitter->setGetPayload(array(
            ApiSerialLetter::API_TARGET => 'serviceDisplayChangeStatus'
        ));
        $ComparePasswordEmitter->setPostPayload(array(
            'Id' => $Id
        ));
        $ComparePasswordPipeline->appendEmitter($ComparePasswordEmitter);

        return $ComparePasswordPipeline;
    }

    /**
     * @param $Id
     *
     * @return $this|string
     */
    public function serviceChangeStatus($Id)
    {

        $Content = '';
        $tblSerialCompany = SerialLetter::useService()->getSerialCompanyById($Id);
        if($tblSerialCompany){
            SerialLetter::useService()->changeSerialCompanyStatus($tblSerialCompany, !$tblSerialCompany->getisIgnore());

            if($tblSerialCompany->getisIgnore()){
                $Content = (new Standard('', self::getEndpoint(), new SuccessIcon(), array(), 'Exportieren'))
                    ->ajaxPipelineOnClick(self::pipelineCompanyChangeStatus($tblSerialCompany->getId()));
            } else {
                $Content = (new Standard('', self::getEndpoint(), new Disable(), array(), 'Nicht Exportieren'))
                    ->ajaxPipelineOnClick(self::pipelineCompanyChangeStatus($tblSerialCompany->getId()));
            }
        }

        return $Content;
    }

    /**
     * @param $Id
     *
     * @return $this|string
     */
    public function serviceDisplayChangeStatus($Id)
    {

        $Content = '';
        $tblSerialCompany = SerialLetter::useService()->getSerialCompanyById($Id);
        if($tblSerialCompany){
            $Content = ($tblSerialCompany->getisIgnore()
                ? new Center(new DangerText(new ToolTip(new Disable(), 'Wird nicht Exportiert')))
                : new Center(new SuccessText(new ToolTip(new SuccessIcon(), 'Wird Exportiert'))));
        }

        return $Content;
    }
}