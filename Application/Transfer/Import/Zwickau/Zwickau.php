<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 20.02.2017
 * Time: 08:42
 */

namespace SPHERE\Application\Transfer\Import\Zwickau;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Main;

/**
 * Class Zwickau
 *
 * @package SPHERE\Application\Transfer\Import\Zwickau
 */
class Zwickau implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Company', __NAMESPACE__ . '\Frontend::frontendCompanyImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Student', __NAMESPACE__ . '\Frontend::frontendStudentImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/StudentNextYear', __NAMESPACE__ . '\Frontend::frontendStudentNextYearImport'
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
        $consumer = 'CMS';

        $DataList[] = array(
            'Consumer' => $consumer,
            'Name' => 'Firmen (Fuxschool)',
            'Option' => new Standard(
                '',
                __NAMESPACE__ . '/Company',
                new Select()
            )
        );
        $DataList[] = array(
            'Consumer' => $consumer,
            'Name' => 'Schüler (Fuxschool)',
            'Option' => new Standard(
                '',
                __NAMESPACE__ . '/Student',
                new Select(),
                array(
                    'IsNextYear' => false
                )
            )
        );
        $DataList[] = array(
            'Consumer' => $consumer,
            'Name' => 'Schulanfänger 2017 (Fuxschool)',
            'Option' => new Standard(
                '',
                __NAMESPACE__ . '/Student',
                new Select(),
                array(
                    'IsNextYear' => true
                )
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
