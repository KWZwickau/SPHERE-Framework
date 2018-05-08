<?php
namespace SPHERE\Application\Transfer\Import\Dresden;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Main;

class Dresden implements IModuleInterface
{

    public static function registerModule()
    {

        /*
        * Person
        */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Person', __NAMESPACE__ . '\Frontend::frontendPersonImport'
        ));

        /*
        * Company
        */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Company', __NAMESPACE__ . '\Frontend::frontendCompanyImport'
        ));
        /*
         * Personen/Institutionen Gruppenzuweisung
         */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Group', __NAMESPACE__ . '\Frontend::frontendUpdateGroupImport'
        ));
        /*
         * Personen/Institutionen Beschreibung
         */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Description', __NAMESPACE__ . '\Frontend::frontendUpdateDescriptionImport'
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

    /**
     * @param array $DataList
     *
     * @return array
     */
    public static function setLinks($DataList)
    {
        $consumer = 'FES';
        $DataList[] = array(
            'Consumer' => $consumer,
            'Name'     => '1. Personen',
            'Option'   => new Standard(
                '',
                __NAMESPACE__.'/Person',
                new Select()
            )
        );
        $DataList[] = array(
            'Consumer' => $consumer,
            'Name'     => '2. Institutionen',
            'Option'   => new Standard(
                '',
                __NAMESPACE__.'/Company',
                new Select()
            )
        );
        $DataList[] = array(
            'Consumer' => $consumer,
            'Name'     => '3. Gruppenzuweisung',
            'Option'   => new Standard(
                '',
                __NAMESPACE__.'/Group',
                new Select()
            )
        );
        $DataList[] = array(
            'Consumer' => $consumer,
            'Name'     => '4. Bemerkungen',
            'Option'   => new Standard(
                '',
                __NAMESPACE__.'/Description',
                new Select()
            )
        );

        return $DataList;
    }
}
