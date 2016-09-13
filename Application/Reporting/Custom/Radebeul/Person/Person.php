<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.09.2016
 * Time: 16:04
 */

namespace SPHERE\Application\Reporting\Custom\Radebeul\Person;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Reporting\AbstractModule;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Person
 *
 * @package SPHERE\Application\Reporting\Custom\Radebeul\Person
 */
class Person extends AbstractModule implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/ParentTeacherConferenceList'), new Link\Name('Anwesenheitsliste für Elternabende'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/DenominationList'), new Link\Name('Religionszugehörigkeit'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/PhoneList'), new Link\Name('Telefonliste'))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __NAMESPACE__.'\Frontend::frontendPerson'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/ParentTeacherConferenceList',
            __NAMESPACE__.'\Frontend::frontendParentTeacherConferenceList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/DenominationList',
            __NAMESPACE__.'\Frontend::frontendDenominationList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/PhoneList',
            __NAMESPACE__.'\Frontend::frontendPhoneList'
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