<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 09.09.2016
 * Time: 15:52
 */

namespace SPHERE\Application\Api\Document\Custom;

use SPHERE\Application\Api\Document\Custom\Gersdorf\Gersdorf;
use SPHERE\Application\Api\Document\Custom\Hoga\Hoga;
use SPHERE\Application\Api\Document\Custom\Lebenswelt\Lebenswelt;
use SPHERE\Application\Api\Document\Custom\Limbach\Limbach;
use SPHERE\Application\Api\Document\Custom\Radebeul\Radebeul;
use SPHERE\Application\Api\Document\Custom\Zwickau\Zwickau;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class Custom
 *
 * @package SPHERE\Application\Api\Document\Custom
 */
class Custom extends Extension implements IModuleInterface
{

    public static function registerModule()
    {

        $consumerAcronym = ( Consumer::useService()->getConsumerBySession() ? Consumer::useService()->getConsumerBySession()->getAcronym() : '' );
        // Lebenswelt
        if ($consumerAcronym === 'LWSZ') {
            Lebenswelt::registerModule();
        }
        // Radebeul
        if ($consumerAcronym === 'EVSR') {
            Radebeul::registerModule();
        }
        // Zwickau
        if ($consumerAcronym === 'CMS') {
            Zwickau::registerModule();
        }
        // Zwickau
        if ($consumerAcronym === 'FELS') { // local test || $consumerAcronym === 'REF'
            Limbach::registerModule();
        }
        if ($consumerAcronym === 'HOGA') {
            Hoga::registerModule();
        }
        // ToDO nach der Veröffentlichung kann der Ref für die Einstellung wieder entfernt werden
        if ($consumerAcronym === 'EVOSG'|| $consumerAcronym === 'REF') {
            Gersdorf::registerModule();
        }
    }

    /**
     * @return IServiceInterface|void
     */
    public static function useService()
    {

    }

    /**
     * @return IFrontendInterface|void
     */
    public static function useFrontend()
    {

    }
}