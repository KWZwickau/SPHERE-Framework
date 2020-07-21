<?php
namespace SPHERE\Application\Api\Platform\View;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\System\Database\ReportingUpgrade;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Success;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Accordion;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Main;
use SPHERE\System\Extension\Extension;

/**
 * Class View
 *
 * @package SPHERE\Application\Api\Platform\View
 */
class View extends Extension implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Upgrade', __CLASS__.'::executeUpgrade'
        ));
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {

    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {

    }

    /**
     * @param string $Consumer
     *
     * @return string
     */
    public function executeUpgrade($Consumer)
    {

        $Result = array();
        if ($Consumer) {
            $tblConsumer = Consumer::useService()->getConsumerByAcronym($Consumer);
            if ($tblConsumer) {
                $ReportingUpgrade = new ReportingUpgrade('127.0.0.1', 'root', 'sphere');
                $isPassed = false;
                $Protocol = $ReportingUpgrade->migrateActiveReportingSingleResult($Consumer, $isPassed);
                $Success = new \SPHERE\Common\Frontend\Text\Repository\Success(new Success());
                $Exclamation = new \SPHERE\Common\Frontend\Text\Repository\Danger(new Exclamation());
                $Result = $Consumer.' '.(new Accordion(false))->addItem(($isPassed ? $Success : $Exclamation).
                        ' Protocol für '.$Consumer.' (Execution on: '.$Consumer.')',
                        $Protocol)->getContent(); // $Protocol
            } else {
                return json_encode($Consumer.' '.(new Danger('Mandant '.$Consumer.' not valid!')));
            }
        }
        return json_encode($Result);
    }
}