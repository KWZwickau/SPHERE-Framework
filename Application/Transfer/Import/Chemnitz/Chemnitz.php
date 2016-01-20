<?php
namespace SPHERE\Application\Transfer\Import\Chemnitz;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Main;

/**
 * Class Chemnitz
 *
 * @package SPHERE\Application\Transfer\Import\Chemnitz
 */
class Chemnitz implements IModuleInterface
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
            __NAMESPACE__.'/Person', __NAMESPACE__.'\Frontend::frontendPersonImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/InterestedPerson', __NAMESPACE__.'\Frontend::frontendInterestedPersonImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Staff', __NAMESPACE__.'\Frontend::frontendStaffImport'
        ));

        Main::getDispatcher()->registerWidget('Import', array(__CLASS__, 'widgetChemnitzStaff'), 2, 2);
        Main::getDispatcher()->registerWidget('Import', array(__CLASS__, 'widgetChemnitzStudent'), 2, 2);
        Main::getDispatcher()->registerWidget('Import', array(__CLASS__, 'widgetChemnitzInterestedPerson'), 2, 2);
        Main::getDispatcher()->registerWidget('Import', array(__CLASS__, 'widgetChemnitzPerson'), 2, 2);
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service();
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {

        return new Frontend();
    }

    /**
     * @return Thumbnail
     */
    public static function widgetChemnitzStaff()
    {

        return new Thumbnail(
            FileSystem::getFileLoader('/Common/Style/Resource/eszc.png'),
            'Chemntiz', 'Mitarbeiterdaten',
            new Standard('', '/Transfer/Import/Chemnitz/Staff', new Upload(), array(), 'Upload')
        );
    }

    /**
     * @return Thumbnail
     */
    public static function widgetChemnitzStudent()
    {

        return new Thumbnail(
            FileSystem::getFileLoader('/Common/Style/Resource/eszc.png'),
            'Chemntiz', 'Schülerdaten',
            new Standard('', '/Transfer/Import/Chemnitz/Student', new Upload(), array(), 'Upload')
        );
    }

    /**
     * @return Thumbnail
     */
    public static function widgetChemnitzPerson()
    {

        return new Thumbnail(
            FileSystem::getFileLoader('/Common/Style/Resource/eszc.png'),
            'Chemntiz', 'Personendaten',
            new Standard('', '/Transfer/Import/Chemnitz/Person', new Upload(), array(), 'Upload')
        );
    }

    /**
     * @return Thumbnail
     */
    public static function widgetChemnitzInterestedPerson()
    {

        return new Thumbnail(
            FileSystem::getFileLoader('/Common/Style/Resource/eszc.png'),
            'Chemntiz', 'Interessentendaten',
            new Standard('', '/Transfer/Import/Chemnitz/InterestedPerson', new Upload(), array(), 'Upload')
        );
    }
}
