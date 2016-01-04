<?php
namespace SPHERE\Application\Platform\Assistance;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Platform\Assistance\Error\Error;
use SPHERE\Application\Platform\Assistance\Support\Support;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Stage;

/**
 * Class Assistance
 *
 * @package SPHERE\Application\System\Assistance
 */
class Assistance implements IApplicationInterface
{

    public static function registerApplication()
    {

        /**
         * Register Module
         */
        Error::registerModule();
        Support::registerModule();
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__,
                'Assistance::frontendAssistance'
            )
        );
    }

    /**
     * @return Stage
     */
    public function frontendAssistance()
    {

        $Stage = new Stage('Hilfe', 'Bitte wählen Sie ein Thema');

        return $Stage;
    }
}
