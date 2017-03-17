<?php
namespace SPHERE\Application\Transfer\Import\Tharandt;


use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Main;

class Tharandt implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Teacher', __NAMESPACE__.'\Frontend::frontendTeacherImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Student', __NAMESPACE__.'\Frontend::frontendStudentImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Interested', __NAMESPACE__.'\Frontend::frontendInterestedImport'
        ));
    }

    /**
     * @param array $DataList
     *
     * @return array
     */
    public static function setLinks($DataList)
    {
        $consumer = 'CSW';

        $DataList[] = array(
            'Consumer' => $consumer,
            'Name'     => 'Lehrer',
            'Option'   => new Standard(
                '',
                __NAMESPACE__.'/Teacher',
                new Select()
            )
        );
        $DataList[] = array(
            'Consumer' => $consumer,
            'Name'     => 'Schüler',
            'Option'   => new Standard(
                '',
                __NAMESPACE__.'/Student',
                new Select(),
                array(
                    'IsNextYear' => false
                )
            )
        );
        $DataList[] = array(
            'Consumer' => $consumer,
            'Name'     => 'Interessenten',
            'Option'   => new Standard(
                '',
                __NAMESPACE__.'/Interested',
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