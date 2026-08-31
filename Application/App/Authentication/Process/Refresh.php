<?php

namespace SPHERE\Application\App\Authentication\Process;

use MOC\V\Core\HttpKernel\HttpKernel;
use SPHERE\Application\App\AppException;
use SPHERE\Application\App\Authentication\Authentication;
use SPHERE\Application\App\Dispatcher;
use SPHERE\Application\App\ModuleInterface;
use SPHERE\Application\App\Response\Code\Response201;
use SPHERE\Application\App\Response\Code\Response400;
use SPHERE\Application\App\Response\Code\Response401;
use SPHERE\Application\App\Response\Code\Response409;
use SPHERE\Application\App\Response\Code\Response422;
use SPHERE\Application\App\Response\RequestMethod;
use SPHERE\Application\App\Response\ResponseInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumerLogin;
use SPHERE\Common\Main;

/**
 *
 */
class Refresh implements ModuleInterface
{
    /**
     * @throws AppException
     */
    public static function registerModule(): void
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = Main::getDispatcher();
        $route = $dispatcher::createRoute(__NAMESPACE__ . '/refresh', __CLASS__ . '::handleRequest');
        $dispatcher::registerRoute($route, true);
    }

    /**
     * @return \MOC\V\Core\HttpKernel\Component\IBridgeInterface
     */
    public static function getRequest()
    {

        return HttpKernel::getRequest();
    }

    public static function handleRequest(
//        ?string $deviceIdentifier = null,
        ?string $authenticationToken = null,
    ): ResponseInterface {
        // -----
        // Validate request input
        // -----
        if (!RequestMethod::wasPostMethod()) {
            return RequestMethod::wasWrong();
        }

        // read from header
        $headerArray = self::getRequest()->getHeaderArray();
        $deviceIdentifier = $headerArray['x-device-key'][0] ?? null;
//        return new Response201($deviceIdentifier);

        // -----
        // Validate user input
        // -----
        // Test availability (structure)
        if (
            null === $deviceIdentifier
            || null === $authenticationToken
        ) {
            return new Response400('Missing mandatory parameters');
        }
        // Test compatibility (content)
        if (
            empty(trim($deviceIdentifier))
            || empty(trim($authenticationToken))
        ) {
            return new Response422('Missing mandatory parameters');
        }

        // Rewire to sign in if token is not valid or not matching
        $tblDevice = Authentication::useService()->getDeviceByAuthentication($authenticationToken);
        if (!$tblDevice) {
            return new Response401('Invalid authentication token');
        }
        // find consumer on account
        if(!($tblAccount = $tblDevice->getServiceTblAccount())
        || !($tblConsumer = $tblAccount->getServiceTblConsumer())){
            return new Response401('Invalid credentials');
        }
        if(!($tblConsumerLogin = Consumer::useService()->getConsumerLoginByConsumerAndSystem($tblConsumer, TblConsumerLogin::VALUE_SYSTEM_SSW_APP))){
            return new Response401('Consumer is disabled');
        }

        if ($tblDevice->getDeviceIdentifier() !== $deviceIdentifier) {
            return new Response409('Wrong device identifier');
        }
        // Device disabled by user?
        if (false === $tblDevice->getIsActive()) {
            return new Response401('Device is disabled');
        }
        // Await device activation by user
        if (null === $tblDevice->getIsActive()) {
            return new Response409('Activation needed');
        }

        // Timeout Session
        $accessToken = $tblDevice->getAccessToken();
        if ($accessToken) {
            Account::useService()->destroySession(null, $accessToken);
        }

        // -----
        // All steps are solved
        // -----
        Authentication::useService()->modifyAccessToken(
            $tblDevice, Authentication::produceAccessToken(), Authentication::ACCESS_TOKEN_TIMEOUT
        );
        return new Response201([
            'accessToken' => $tblDevice->getAccessToken()
        ]);
    }

    public static function useService(): Service
    {
        return Authentication::useService();
    }
}
