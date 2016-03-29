<?php
namespace SPHERE\Application\Education\Graduation\Certificate;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\Icon\Repository\Certificate as CertificateIcon;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Certificate
 *
 * @package SPHERE\Application\Education\Graduation\Certificate
 */
class Certificate implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'\Select\Division'), new Link\Name('Zeugnisse'),
                new Link\Icon(new CertificateIcon()))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'\Select\Division', __NAMESPACE__.'\Frontend::frontendSelectDivision'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'\Select\Student', __NAMESPACE__.'\Frontend::frontendSelectStudent'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'\Select\Certificate', __NAMESPACE__.'\Frontend::frontendSelectCertificate'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'\Select\Content', __NAMESPACE__.'\Frontend::frontendSelectContent'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'\Create', __NAMESPACE__.'\Frontend::frontendCreate'
        ));

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

        return new Frontend();
    }

}
