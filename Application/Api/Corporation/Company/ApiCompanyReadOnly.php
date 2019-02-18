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
use SPHERE\Application\Corporation\Company\FrontendReadOnly;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiCompanyReadOnly
 *
 * @package SPHERE\Application\Api\Corporation\Company
 */
class ApiCompanyReadOnly extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('loadBasicContent');

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
     * @param int $CompanyId
     *
     * @return Pipeline
     */
    public static function pipelineLoadBasicContent($CompanyId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'BasicContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadBasicContent',
        ));
        $emitter->setPostPayload(array(
            'CompanyId' => $CompanyId
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @param null $CompanyId
     *
     * @return string
     */
    public function loadBasicContent($CompanyId = null)
    {

        return FrontendReadOnly::getBasicContent($CompanyId);
    }
}