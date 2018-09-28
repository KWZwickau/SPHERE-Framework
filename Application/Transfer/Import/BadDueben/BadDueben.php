<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 28.09.2018
 * Time: 11:33
 */

namespace SPHERE\Application\Transfer\Import\BadDueben;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Main;

/**
 * Class BadDueben
 *
 * @package SPHERE\Application\Transfer\Import\BadDueben
 */
class BadDueben implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Student', __NAMESPACE__ . '\Frontend::frontendStudentImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Staff', __NAMESPACE__ . '\Frontend::frontendStaffmport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/InterestedPerson', __NAMESPACE__ . '\Frontend::frontendInterestedPersonImport'
        ));
    }

    /**
     * @param array $DataList
     *
     * @return array
     */
    public static function setLinks($DataList){
        $consumer = 'ESBD';

        $DataList[] = array(
            'Consumer' => $consumer,
            'Name' => 'Schüler (Fuxschool)',
            'Option' => new Standard(
                '',
                __NAMESPACE__ . '/Student',
                new Select()
            )
        );
        $DataList[] = array(
            'Consumer' => $consumer,
            'Name' => 'Mitarbeiter',
            'Option' => new Standard(
                '',
                __NAMESPACE__ . '/Staff',
                new Select()
            )
        );
        $DataList[] = array(
            'Consumer' => $consumer,
            'Name' => 'Interessenten',
            'Option' => new Standard(
                '',
                __NAMESPACE__ . '/InterestedPerson',
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