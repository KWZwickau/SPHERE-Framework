<?php
namespace SPHERE\Application\Api\Reporting\CustomEKBO;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;

/**
 * Class CustomEKBO
 *
 * @package SPHERE\Application\Api\Reporting\CustomEKBO
 */
class CustomEKBO implements IModuleInterface
{

    public static function registerModule()
    {
        $tblConsumer = Consumer::useService()->getConsumerBySession();
        if ($tblConsumer && $tblConsumer->getType() == TblConsumer::TYPE_BERLIN) {
            $consumerAcronym = $tblConsumer->getAcronym();
            // Berlin Zentrum
            if ($consumerAcronym === 'ESBZ') {
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/BerlinZentrum/SuSList/Download', __NAMESPACE__ . '\BerlinZentrum\Common::downloadSuSList'
                ));
            }
        }
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // Implement useFrontend() method.
    }
}
