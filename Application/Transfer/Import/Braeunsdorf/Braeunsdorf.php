<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 07.06.2019
 * Time: 08:43
 */

namespace SPHERE\Application\Transfer\Import\Braeunsdorf;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Main;

/**
 * Class Braeunsdorf
 *
 * @package SPHERE\Application\Transfer\Import\Braeunsdorf
 */
class Braeunsdorf  implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Student', __NAMESPACE__ . '\Frontend::frontendStudentImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/InterestedPerson', __NAMESPACE__ . '\Frontend::frontendInterestedPersonImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Person', __NAMESPACE__ . '\Frontend::frontendPersonImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Company', __NAMESPACE__ . '\Frontend::frontendCompanyImport'
        ));
    }

    /**
     * @param array $DataList
     *
     * @return array
     */
    public static function setLinks($DataList)
    {
        $consumer = 'EVSB';

        $DataList[] = array(
            'Consumer' => $consumer,
            'Name' => '01 - Schüler',
            'Option' => new Standard(
                '',
                __NAMESPACE__ . '/Student',
                new Select()
            )
        );
        $DataList[] = array(
            'Consumer' => $consumer,
            'Name' => '02 - Interessenten',
            'Option' => new Standard(
                '',
                __NAMESPACE__ . '/InterestedPerson',
                new Select()
            )
        );
        $DataList[] = array(
            'Consumer' => $consumer,
            'Name' => '03 - Mitglieder',
            'Option' => new Standard(
                '',
                __NAMESPACE__ . '/Person',
                new Select()
            )
        );
        $DataList[] = array(
            'Consumer' => $consumer,
            'Name' => '04 - Firmen',
            'Option' => new Standard(
                '',
                __NAMESPACE__ . '/Company',
                new Select()
            )
        );

        return $DataList;
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