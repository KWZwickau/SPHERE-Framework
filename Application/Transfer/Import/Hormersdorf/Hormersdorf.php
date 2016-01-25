<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 22.01.2016
 * Time: 15:00
 */

namespace SPHERE\Application\Transfer\Import\Hormersdorf;


use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Main;

class Hormersdorf implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Student', __NAMESPACE__.'\Frontend::frontendStudent'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Student/Import', __NAMESPACE__.'\Frontend::frontendStudentImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/InterestedPerson', __NAMESPACE__.'\Frontend::frontendInterestedPersonImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/ClubMember', __NAMESPACE__.'\Frontend::frontendClubMemberImport'
        ));

//        Main::getDispatcher()->registerWidget('Import', array(__CLASS__, 'widgetStudent'), 2, 2);
        Main::getDispatcher()->registerWidget('Import', array(__CLASS__, 'widgetInterestedPerson'), 2, 2);
        Main::getDispatcher()->registerWidget('Import', array(__CLASS__, 'widgetClubMember'), 2, 2);
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
     * @return Thumbnail
     */
    public static function widgetInterestedPerson()
    {

        return new Thumbnail(
            FileSystem::getFileLoader('/Common/Style/Resource/logo_kreide2.png'),
            'Hormersdorf', 'Interessentendaten',
            new Standard('', '/Transfer/Import/Hormersdorf/InterestedPerson', new Upload(), array(), 'Upload')
        );
    }

    /**
     * @return Thumbnail
     */
    public static function widgetClubMember()
    {

        return new Thumbnail(
            FileSystem::getFileLoader('/Common/Style/Resource/logo_kreide2.png'),
            'Hormersdorf', 'Schulverein-Daten',
            new Standard('', '/Transfer/Import/Hormersdorf/ClubMember', new Upload(), array(), 'Upload')
        );
    }
}