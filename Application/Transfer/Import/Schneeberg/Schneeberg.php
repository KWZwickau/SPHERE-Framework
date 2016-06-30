<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 29.06.2016
 * Time: 08:06
 */

namespace SPHERE\Application\Transfer\Import\Schneeberg;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Main;

class Schneeberg implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Student', __NAMESPACE__ . '\Frontend::frontendStudentImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Staff', __NAMESPACE__ . '\Frontend::frontendStaffImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/InterestedPerson', __NAMESPACE__ . '\Frontend::frontendInterestedPersonImport'
        ));

        Main::getDispatcher()->registerWidget('Import', array(__CLASS__, 'widgetStudent'), 2, 2);
        Main::getDispatcher()->registerWidget('Import', array(__CLASS__, 'widgetStaff'), 2, 2);
        Main::getDispatcher()->registerWidget('Import', array(__CLASS__, 'widgetInterestedPerson'), 2, 2);
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
    public static function widgetStudent()
    {

        return new Thumbnail(
            FileSystem::getFileLoader('/Common/Style/Resource/logo_kreide2.png'),
            'Schneeberg', 'Schüler-Daten Oberschule',
            new Standard('', '/Transfer/Import/Schneeberg/Student', new Upload(), array(), 'Upload')
        );
    }

    /**
     * @return Thumbnail
     */
    public static function widgetStaff()
    {

        return new Thumbnail(
            FileSystem::getFileLoader('/Common/Style/Resource/logo_kreide2.png'),
            'Schneeberg', 'Mitarbeiter/Lehrer Oberschule',
            new Standard('', '/Transfer/Import/Schneeberg/Staff', new Upload(), array(), 'Upload')
        );
    }


    /**
     * @return Thumbnail
     */
    public static function widgetInterestedPerson()
    {

        return new Thumbnail(
            FileSystem::getFileLoader('/Common/Style/Resource/logo_kreide2.png'),
            'Schneeberg', 'Interessenten Oberschule',
            new Standard('', '/Transfer/Import/Schneeberg/InterestedPerson', new Upload(), array(), 'Upload')
        );
    }
}
