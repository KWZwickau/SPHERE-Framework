<?php
namespace SPHERE\Application\People\Search;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Search
 *
 * @package SPHERE\Application\People\Search
 */
class Search implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        self::registerModule();

        /**
         * Register Navigation
         */
        Main::getDisplay()->addApplicationNavigation(
            new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'Personensuche' ),
                new Link\Icon( new Info() )
            )
        );
    }

    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link( new Link\Route( __NAMESPACE__.'/Group' ), new Link\Name( 'Nach Personengruppe' ),
                new Link\Icon( new Info() )
            )
        );
        Main::getDispatcher()->registerRoute( Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Group', __CLASS__.'::frontendGroup'
        ) );
        Main::getDisplay()->addModuleNavigation(
            new Link( new Link\Route( __NAMESPACE__.'/Attribute' ), new Link\Name( 'Nach Eigenschaften' ),
                new Link\Icon( new Info() )
            )
        );
        Main::getDispatcher()->registerRoute( Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Attribute', __CLASS__.'::frontendAttribute'
        ) );

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

    public function frontendGroup()
    {

        $Stage = new Stage( 'Personensuche', 'nach Personengruppe' );

        $Stage->setMessage( 'Bitte wählen Sie eine Personengruppe' );

        $Stage->addButton(
            new Standard( 'Interessenten', new Link\Route( __NAMESPACE__.'/Group' ), null, array(
                'tblGroup' => 1
            ), true )
        );
        $Stage->addButton(
            new Standard( 'Schüler', new Link\Route( __NAMESPACE__.'/Group' ), null, array(
                'tblGroup' => 1
            ), true )
        );
        $Stage->addButton(
            new Standard( 'Sorgeberechtigte', new Link\Route( __NAMESPACE__.'/Group' ), null, array(
                'tblGroup' => 1
            ), true )
        );
        $Stage->addButton(
            new Standard( 'Lehrer', new Link\Route( __NAMESPACE__.'/Group' ), null, array(
                'tblGroup' => 1
            ), true )
        );

        return $Stage;
    }


}
