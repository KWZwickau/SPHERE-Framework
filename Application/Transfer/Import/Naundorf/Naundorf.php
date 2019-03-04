<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 04.03.2019
 * Time: 08:35
 */

namespace SPHERE\Application\Transfer\Import\Naundorf;


use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Main;

/**
 * Class Naundorf
 *
 * @package SPHERE\Application\Transfer\Import\Naundorf
 */
class Naundorf implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Student', __NAMESPACE__ . '\Frontend::frontendStudentImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/StudentMeta', __NAMESPACE__ . '\Frontend::frontendStudentMetaImport'
        ));
//        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
//            __NAMESPACE__ . '/Staff', __NAMESPACE__ . '\Frontend::frontendStaffImport'
//        ));
//        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
//            __NAMESPACE__ . '/InterestedPerson', __NAMESPACE__ . '\Frontend::frontendInterestedPersonImport'
//        ));
    }

    /**
     * @param array $DataList
     *
     * @return array
     */
    public static function setLinks($DataList)
    {
        $consumer = 'EWS';

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
            'Name' => '02 - Schüler-Meta-Daten',
            'Option' => new Standard(
                '',
                __NAMESPACE__ . '/StudentMeta',
                new Select()
            )
        );
//        $DataList[] = array(
//            'Consumer' => $consumer,
//            'Name' => 'Mitarbeiter',
//            'Option' => new Standard(
//                '',
//                __NAMESPACE__ . '/Staff',
//                new Select()
//            )
//        );
//        $DataList[] = array(
//            'Consumer' => $consumer,
//            'Name' => 'Interessenten',
//            'Option' => new Standard(
//                '',
//                __NAMESPACE__ . '/InterestedPerson',
//                new Select()
//            )
//        );

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