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
        $consumer = 'EVGSM';
        $DataList[] = array(
            'Consumer' => $consumer,
            'Name'     => 'Schüler',
            'Option'   => new Standard(
                '',
                __NAMESPACE__.'/Person',
                new Select(),
                array(
                    'IsNextYear' => false
                )
            )
        );

        return $DataList;
    }
}
