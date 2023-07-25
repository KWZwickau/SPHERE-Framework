<?php
namespace SPHERE\Application\Transfer\Import\FuxMedia;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Main;

/**
 * Class FuxSchool
 *
 * @package SPHERE\Application\Transfer\Import\FuxMedia
 */
class FuxSchool implements IModuleInterface
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
            __NAMESPACE__.'/Teacher', __NAMESPACE__.'\Frontend::frontendTeacherImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Division', __NAMESPACE__.'\Frontend::frontendDivision'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Division/Import', __NAMESPACE__.'\Frontend::frontendDivisionImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Company', __NAMESPACE__.'\Frontend::frontendCompanyImport'
        ));

        Main::getDispatcher()->registerWidget('Import', array(__CLASS__, 'widgetFuxMediaCompany'), 2, 2);
        Main::getDispatcher()->registerWidget('Import', array(__CLASS__, 'widgetFuxMediaStudent'), 2, 2);
        Main::getDispatcher()->registerWidget('Import', array(__CLASS__, 'widgetFuxMediaTeacher'), 2, 2);
        Main::getDispatcher()->registerWidget('Import', array(__CLASS__, 'widgetFuxMediaDivision'), 2, 2);
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
     * @return Layout
     * @throws \MOC\V\Core\FileSystem\Exception\FileSystemException
     */
    public static function getDownloadLayout()
    {

        return new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn(new Thumbnail(
                FileSystem::getFileLoader('/Common/Style/Resource/fuxschool.gif'),
                'FuxSchool', 'Schülerdaten',
                (new Standard('', '/Transfer/Import/FuxMedia/Student', new Upload(), array(), 'Upload'))->setDisabled()
            ), 2),
            new LayoutColumn(new Thumbnail(
                FileSystem::getFileLoader('/Common/Style/Resource/fuxschool.gif'),
                'FuxSchool', 'Klassendaten',
                (new Standard('', '/Transfer/Import/FuxMedia/Division', new Upload(), array(), 'Upload'))->setDisabled()
            ), 2),
            new LayoutColumn(new Thumbnail(
                FileSystem::getFileLoader('/Common/Style/Resource/fuxschool.gif'),
                'FuxSchool', 'Lehrerdaten',
                (new Standard('', '/Transfer/Import/FuxMedia/Teacher', new Upload(), array(), 'Upload'))->setDisabled()
            ), 2),
            new LayoutColumn(new Thumbnail(
                FileSystem::getFileLoader('/Common/Style/Resource/fuxschool.gif'),
                'FuxSchool', 'Institutionendaten (Einrichtungsdaten)',
                (new Standard('', '/Transfer/Import/FuxMedia/Company', new Upload(), array(), 'Upload'))->setDisabled()
            ), 2),
        ))));
    }
}
