<?php
namespace SPHERE\Application\Platform\System\Protocol;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Protocol
 *
 * @package SPHERE\Application\System\Platform\Protocol
 */
class Protocol implements IModuleInterface
{

    public static function registerModule()
    {

        /**
         * Register Navigation
         */
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Protokoll'), new Link\Icon(new Listing()))
        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__,
                'Protocol::frontendProtocol'
            )
        );
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
    public function frontendProtocol()
    {

        $Stage = new Stage('Protokoll', 'Aktivitäten');

        $Stage->setContent(
            new TableData($this->useService()->getProtocolAll(), null,
                array(
                    'Id'     => '#',
                    'Editor' => 'Editor',
                    'Origin' => 'Origin',
                    'Commit' => 'Commit'
                ),
                array(
                    "order"      => array(
                        array(0, 'desc')
                    ),
                    "columnDefs" => array(
                        array("orderable" => false, "targets" => 1),
                        array("orderable" => false, "targets" => 2),
                        array("orderable" => false, "targets" => 3)
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @return \SPHERE\Application\Platform\System\Protocol\Service
     */
    public static function useService()
    {

        return new Service(new Identifier('Platform', 'System', 'Protocol'),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }

}
