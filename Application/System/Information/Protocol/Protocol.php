<?php
namespace SPHERE\Application\System\Information\Protocol;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Protocol
 *
 * @package SPHERE\Application\System\Information\Protocol
 */
class Protocol implements IModuleInterface
{

    public static function registerModule()
    {

        /**
         * Register Navigation
         */
        Main::getDisplay()->addApplicationNavigation(
            new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'Protokoll' ), new Link\Icon( new Listing() ) )
        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute( __NAMESPACE__,
                'Protocol::frontendProtocol'
            )
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier( 'System', 'Protocol' ),
            __DIR__.'/Service/Entity',
            __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     * @return Stage
     */
    public function frontendProtocol()
    {

        $Stage = new Stage( 'Protokoll', 'Aktivitäten' );

        $Stage->setContent(
            new TableData( $this->useService()->getProtocolAll(), null,
                array(
                    'Id'     => '#',
                    'Editor' => 'Editor',
                    'Origin' => 'Origin',
                    'Commit' => 'Commit'
                ),
                array(
                    "order"      => array(
                        array( 0, 'desc' )
                    ),
                    "columnDefs" => array(
                        array( "orderable" => false, "targets" => 1 ),
                        array( "orderable" => false, "targets" => 2 ),
                        array( "orderable" => false, "targets" => 3 )
                    )
                )
            )
        );

        return $Stage;
    }
}
