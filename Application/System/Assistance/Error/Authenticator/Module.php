<?php
namespace SPHERE\Application\System\Assistance\Error\Authenticator;

use SPHERE\Common\Window\Stage;

/**
 * Class Module
 *
 * @package SPHERE\Application\System\Assistance\Error\Authenticator
 */
class Module
{

    /**
     * @return Stage
     */
    public static function frontendAssistance()
    {

        return Frontend::stageAuthenticator();
    }
}
