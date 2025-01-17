<?php
namespace SPHERE\Application\Reporting\CustomEKBO\BerlinZentrum\Person;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Reporting\AbstractModule;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Person
 * @package SPHERE\Application\Reporting\CustomEKBO\BerlinZentrum\Person
 */
class Person extends AbstractModule implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route('SPHERE\Application\Reporting\Custom\SuSList'), new Link\Name('SuS Gesamtliste'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            'SPHERE/Application/Reporting/Custom/SuSList', __NAMESPACE__.'\Frontend::frontendSuSList'
        ));
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service();
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }
}
