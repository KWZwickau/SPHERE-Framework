<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Token;
use SPHERE\Common\Frontend\IFrontendInterface;

/**
 * Class Authorization
 *
 * @package SPHERE\Application\System\Gatekeeper\Authorization
 */
class Authorization implements IModuleInterface
{

    public static function registerModule()
    {

        Consumer::registerModule();
        Token::registerModule();
        Access::registerModule();
        Account::registerModule();
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
}
