<?php
namespace SPHERE\Application\System\Gatekeeper;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\System\Gatekeeper\Account\Account;
use SPHERE\Application\System\Gatekeeper\Authentication\Authentication;
use SPHERE\Application\System\Gatekeeper\Authorization\Authorization;
use SPHERE\Application\System\Gatekeeper\Consumer\Consumer;
use SPHERE\Application\System\Gatekeeper\Token\Token;
use SPHERE\Common\Frontend\Icon\Repository\PersonKey;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Gatekeeper
 *
 * @package SPHERE\Application\System\Gatekeeper
 */
class Gatekeeper implements IApplicationInterface
{

    public static function registerApplication()
    {

        /**
         * Register Module
         */
        Consumer::registerModule();
        Token::registerModule();
        Account::registerModule();
        Authorization::registerModule();
        Authentication::registerModule();
        /**
         * Register Navigation
         */
        Main::getDisplay()->addServiceNavigation(
            new Link( new Link\Route( __NAMESPACE__.'/Authorization' ), new Link\Name( 'Berechtigungen' ), new Link\Icon( new PersonKey() ) )
        );
    }
}
