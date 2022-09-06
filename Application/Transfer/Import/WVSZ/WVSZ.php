<?php

namespace SPHERE\Application\Transfer\Import\WVSZ;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Main;

/**
 * Class WVSZ
 *
 * @package SPHERE\Application\Transfer\Import\WVSZ
 */
class WVSZ implements IModuleInterface
{
    public static function registerModule()
    {
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Student', __NAMESPACE__ . '\Frontend::frontendStudentImport'
        ));
    }

    /**
     * @param array $DataList
     *
     * @return array
     */
    public static function setLinks($DataList)
    {
        $consumer = 'WVSZ';

        $DataList[] = array(
            'Consumer' => $consumer,
            'Name' => '02 - Individualler Zusatz Schüler Import',
            'Option' => new Standard(
                '',
                __NAMESPACE__ . '/Student',
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