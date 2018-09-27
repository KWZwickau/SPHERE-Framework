<?php

namespace SPHERE\Application\Transfer\Import\Herrnhut;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Main;
use SPHERE\System\Extension\Repository\Debugger;

class Herrnhut implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Student', __NAMESPACE__ . '\Frontend::frontendStudentImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Student/Former', __NAMESPACE__ . '\Frontend::frontendFormerStudentImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Person', __NAMESPACE__ . '\Frontend::frontendPersonImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Company', __NAMESPACE__ . '\Frontend::frontendCompanyImport'
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
        $consumer = 'EZSH';

        $DataList[] = array(
            'Consumer' => $consumer,
            'Name'     => 'Schüler-Daten',
            'Option'   => new Standard(
                '',
                __NAMESPACE__.'/Student',
                new Select()
            )
        );
        $DataList[] = array(
            'Consumer' => $consumer,
            'Name'     => 'Ehemalige Schüler-Daten',
            'Option'   => new Standard(
                '',
                __NAMESPACE__.'/Student/Former',
                new Select(),
                array(
                    'IsNextYear' => false
                )
            )
        );
        $DataList[] = array(
            'Consumer' => $consumer,
            'Name'     => 'Private Kontakte',
            'Option'   => new Standard(
                '',
                __NAMESPACE__.'/Person',
                new Select()
            )
        );
        $DataList[] = array(
            'Consumer' => $consumer,
            'Name'     => 'Institutionen mit Ansprechpartnern',
            'Option'   => new Standard(
                '',
                __NAMESPACE__.'/Company',
                new Select()
            )
        );

        return $DataList;
    }
}
