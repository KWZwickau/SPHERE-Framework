<?php
namespace SPHERE\Application\Transfer\Import\Chemnitz;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

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
            __NAMESPACE__ . '/Student', __NAMESPACE__ . '\Frontend::frontendStudent'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Student/Import', __NAMESPACE__ . '\Frontend::frontendStudentImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Person', __NAMESPACE__ . '\Frontend::frontendPersonImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/InterestedPerson', __NAMESPACE__ . '\Frontend::frontendInterestedPersonImport'
        ));

        Main::getDispatcher()->registerWidget('Import',
            new Thumbnail(
                FileSystem::getFileLoader('/Common/Style/Resource/eszc.png'),
                'Chemntiz', 'Schülerdaten',
                new Standard('', '/Transfer/Import/Chemnitz/Student', new Upload(), array(), 'Upload')
            ), 2, 2
        );
        Main::getDispatcher()->registerWidget('Import',
            new Thumbnail(
                FileSystem::getFileLoader('/Common/Style/Resource/eszc.png'),
                'Chemntiz', 'Interessentendaten',
                new Standard('', '/Transfer/Import/Chemnitz/InterestedPerson', new Upload(), array(), 'Upload')
            ), 2, 2
        );
        Main::getDispatcher()->registerWidget('Import',
            new Thumbnail(
                FileSystem::getFileLoader('/Common/Style/Resource/eszc.png'),
                'Chemntiz', 'Personendaten',
                new Standard('', '/Transfer/Import/Chemnitz/Person', new Upload(), array(), 'Upload')
            ), 2, 2
        );
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
}
