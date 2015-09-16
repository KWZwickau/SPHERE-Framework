<?php
namespace SPHERE\Application\People\Person;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Person
 *
 * @package SPHERE\Application\People\Person
 */
class Person implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        self::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Person'),
                new Link\Icon(new \SPHERE\Common\Frontend\Icon\Repository\Person())
            )
        );
    }

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, 'Frontend::frontendPerson'
        ));

        // Contact: Address
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Address/Create', 'SPHERE\Application\Contact\Address\Frontend::frontendCreateToPerson'
        )
            ->setParameterDefault('Street', null)
            ->setParameterDefault('City', null)
            ->setParameterDefault('State', null)
            ->setParameterDefault('Type', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Address/Edit', 'SPHERE\Application\Contact\Address\Frontend::frontendUpdateToPerson'
        )
            ->setParameterDefault('Id', null)
            ->setParameterDefault('Street', null)
            ->setParameterDefault('City', null)
            ->setParameterDefault('State', null)
            ->setParameterDefault('Type', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Address/Destroy', 'SPHERE\Application\Contact\Address\Frontend::frontendDestroyToPerson'
        )
            ->setParameterDefault('Id', null)
            ->setParameterDefault('Confirm', false)
        );
        // Contact: Mail
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Mail/Create', 'SPHERE\Application\Contact\Mail\Frontend::frontendCreateToPerson'
        )
            ->setParameterDefault('Address', null)
            ->setParameterDefault('Type', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Mail/Edit', 'SPHERE\Application\Contact\Mail\Frontend::frontendUpdateToPerson'
        )
            ->setParameterDefault('Id', null)
            ->setParameterDefault('Address', null)
            ->setParameterDefault('Type', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Mail/Destroy', 'SPHERE\Application\Contact\Mail\Frontend::frontendDestroyToPerson'
        )
            ->setParameterDefault('Id', null)
            ->setParameterDefault('Confirm', false)
        );
        // Contact: Phone
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Phone/Create', 'SPHERE\Application\Contact\Phone\Frontend::frontendCreateToPerson'
        )
            ->setParameterDefault('Number', null)
            ->setParameterDefault('Type', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Phone/Edit', 'SPHERE\Application\Contact\Phone\Frontend::frontendUpdateToPerson'
        )
            ->setParameterDefault('Id', null)
            ->setParameterDefault('Number', null)
            ->setParameterDefault('Type', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Phone/Destroy', 'SPHERE\Application\Contact\Phone\Frontend::frontendDestroyToPerson'
        )
            ->setParameterDefault('Id', null)
            ->setParameterDefault('Confirm', false)
        );
        // People: Relationship
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Relationship/Create',
            'SPHERE\Application\People\Relationship\Frontend::frontendCreateToPerson'
        )
            ->setParameterDefault('To', null)
            ->setParameterDefault('Type', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Relationship/Edit',
            'SPHERE\Application\People\Relationship\Frontend::frontendUpdateToPerson'
        )
            ->setParameterDefault('Id', null)
            ->setParameterDefault('To', null)
            ->setParameterDefault('Type', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Relationship/Destroy',
            'SPHERE\Application\People\Relationship\Frontend::frontendDestroyToPerson'
        )
            ->setParameterDefault('Id', null)
            ->setParameterDefault('Confirm', false)
        );
        // Corporation: Relationship
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            'SPHERE\Application\Corporation\Company'.'/Relationship/Create',
            'SPHERE\Application\People\Relationship\Frontend::frontendCreateToCompany'
        )
            ->setParameterDefault('To', null)
            ->setParameterDefault('Type', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            'SPHERE\Application\Corporation\Company'.'/Relationship/Edit',
            'SPHERE\Application\People\Relationship\Frontend::frontendUpdateToCompany'
        )
            ->setParameterDefault('Id', null)
            ->setParameterDefault('To', null)
            ->setParameterDefault('Type', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            'SPHERE\Application\Corporation\Company'.'/Relationship/Destroy',
            'SPHERE\Application\People\Relationship\Frontend::frontendDestroyToCompany'
        )
            ->setParameterDefault('Id', null)
            ->setParameterDefault('Confirm', false)
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier('People', 'Person', null, null, Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }
}
