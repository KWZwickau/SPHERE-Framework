<?php
namespace SPHERE\Application\Platform\System\Session;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblSession;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Session
 * @package SPHERE\Application\Platform\System\Session
 */
class Session extends Extension implements IModuleInterface
{
    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Aktive Sessions'))
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__,
                __CLASS__ . '::frontendSession'
            )
        );
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

    /**
     * @return Stage
     */
    public function frontendSession()
    {
        $Stage = new Stage('Aktive Sessions', 'der aktuell angemeldete Benutzer');

        $tblSessionAll = Account::useService()->getSessionAll();

        $Result = array();

        if ($tblSessionAll) {
            array_walk($tblSessionAll, function (TblSession $tblSession) use (&$Result) {

                $tblAccount = $tblSession->getTblAccount();

                $Interval = $tblSession->getEntityUpdate()->getTimestamp() - $tblSession->getEntityCreate()->getTimestamp();

                array_push($Result, array(
                    'Id'         => $tblSession->getId(),
                    'Consumer'   => ( $tblAccount->getServiceTblConsumer() ?
                        $tblAccount->getServiceTblConsumer()->getAcronym()
                        . '&nbsp;' . new Muted($tblAccount->getServiceTblConsumer()->getName())
                        : '-NA-'
                    ),
                    'Account'    => ($tblAccount ? $tblAccount->getUsername() : '-NA-'),
                    'TTL'        => gmdate("H:i:s", $tblSession->getTimeout() - time()),
                    'ActiveTime' => gmdate('H:i:s', $Interval),
                    'LoginTime'  => $tblSession->getEntityCreate(),
                    'LastAction' => $tblSession->getEntityUpdate(),
                    'Identifier' => strtoupper($tblSession->getSession())
                ));

            });
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new TableData(
                                $Result, null, array(), true
                            ),
                            new Redirect(
                                '/Platform/System/Session', 30
                            )
                        ))
                    )
                )
            )
        );

        return $Stage;
    }
}
