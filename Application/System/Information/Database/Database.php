<?php
namespace SPHERE\Application\System\Information\Database;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Database
 *
 * @package SPHERE\Application\System\Information\Database
 */
class Database extends Extension implements IModuleInterface
{

    public static function registerModule()
    {

        /**
         * Register Navigation
         */
        Main::getDisplay()->addApplicationNavigation(
            new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'Datenbank' ) )
        );
        Main::getDisplay()->addModuleNavigation(
            new Link( new Link\Route( __NAMESPACE__.'/Setup/Simulation' ), new Link\Name( 'Setup - Simulation' ) )
        );
        Main::getDisplay()->addModuleNavigation(
            new Link( new Link\Route( __NAMESPACE__.'/Setup/Execution' ), new Link\Name( 'Setup - Durchführung' ) )
        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute( __NAMESPACE__,
                'Database::frontendStatus'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute( __NAMESPACE__.'/Setup/Simulation',
                __CLASS__.'::frontendSetup'
            )->setParameterDefault( 'Simulation', true )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute( __NAMESPACE__.'/Setup/Execution',
                __CLASS__.'::frontendSetup'
            )->setParameterDefault( 'Simulation', false )
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
     * @return Stage
     */
    public function frontendStatus()
    {

        $Stage = new Stage( 'Database', 'Status' );

        return $Stage;
    }

    /**
     * @param bool $Simulation
     *
     * @return Stage
     */
    public function frontendSetup( $Simulation = true )
    {

        $Stage = new Stage( 'Database', 'Setup' );

        // Fetch Modules
        $ClassList = get_declared_classes();
        array_walk( $ClassList, function( &$Class, $Index, $Simulation ) {
            $Inspection = new \ReflectionClass( $Class );
            if( $Inspection->isInternal() ) {
                $Class = false;
            } else {
                if( $Inspection->implementsInterface( '\SPHERE\Application\IModuleInterface' ) ) {
                    /** @var IModuleInterface $Class */
                    $Class = $Inspection->newInstance();
                    $Class = $Class->useService();
                    /** @var IServiceInterface $Class */
                    if( $Class instanceof IServiceInterface ) {
                        $Class = $Class->setupService( $Simulation );
                    } else {
                        $Class = false;
                    }
                } else {
                    $Class = false;
                }
            }
        }, $Simulation );
        $ClassList = array_filter( $ClassList );
        $Stage->setContent( implode( $ClassList ) );
        return $Stage;
    }
}
