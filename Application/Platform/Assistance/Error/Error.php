<?php
namespace SPHERE\Application\Platform\Assistance\Error;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Error
 *
 * @package SPHERE\Application\System\Assistance\Error
 */
class Error implements IModuleInterface
{

    public static function registerModule()
    {

        /**
         * Register Navigation
         */
//        Main::getDisplay()->addModuleNavigation(
//            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Fehlermeldungen'))
//        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__,
                'Error::frontendError'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Authenticator',
                __NAMESPACE__.'\Frontend::frontendAuthenticator'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Authorization',
                __NAMESPACE__.'\Frontend::frontendRoute'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Shutdown',
                __NAMESPACE__.'\Frontend::frontendShutdown'
            )
        );
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {

    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }

    /**
     * @param null|string $Type
     *
     * @return Stage
     */
    public function frontendError($Type = null)
    {

        $Stage = new Stage('Fehlermeldungen', 'Bitte wählen Sie ein Thema');
        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Inhalt', array(
                                new Standard('Betriebsstörung', new Link\Route(__NAMESPACE__), null,
                                    array('Type' => (new Link\Route(__NAMESPACE__.'/Shutdown'))->getValue())
                                ),
                                new Standard('Berechtigung', new Link\Route(__NAMESPACE__), null,
                                    array('Type' => (new Link\Route(__NAMESPACE__.'/Authorization'))->getValue())
                                ),
                                new Standard('Authentifikator', new Link\Route(__NAMESPACE__), null,
                                    array('Type' => (new Link\Route(__NAMESPACE__.'/Authenticator'))->getValue())
                                ),
                            ))
                            , 2),
                        new LayoutColumn(
                            ( $Type
                                ? new Well(Main::getDispatcher()->fetchRoute($Type))
                                : new Info('Bitte wählen Sie einen Inhalt')
                            )
                            , 10),
                    ))
                )
            )
        );

        return $Stage;
    }
}
