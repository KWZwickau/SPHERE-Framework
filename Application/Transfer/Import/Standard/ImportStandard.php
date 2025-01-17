<?php
namespace SPHERE\Application\Transfer\Import\Standard;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Main;

class ImportStandard implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Student', __NAMESPACE__.'\Frontend::frontendStudentImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Interested', __NAMESPACE__.'\Frontend::frontendInterestedImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Stuff', __NAMESPACE__.'\Frontend::frontendStuffImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Company', __NAMESPACE__.'\Frontend::frontendCompanyImport'
        ));
    }

    /**
     * @return LayoutColumn[]
     */
    public static function getStandardLink()
    {
        $ColumnList = array();
        $ColumnList[] = new LayoutColumn(
            new Thumbnail(FileSystem::getFileLoader('/Common/Style/Resource/Schulsoftware-font.png'), 'Schülerdaten', '',
                new Primary('', __NAMESPACE__.'/Student', new Upload())
            ), 2);
        $ColumnList[] = new LayoutColumn(
            new Thumbnail(FileSystem::getFileLoader('/Common/Style/Resource/Schulsoftware-font.png'), 'Interessenten', '',
                new Primary('', __NAMESPACE__.'/Interested', new Upload())
            ), 2);
        $ColumnList[] = new LayoutColumn(
            new Thumbnail(FileSystem::getFileLoader('/Common/Style/Resource/Schulsoftware-font.png'), 'Mitarbeiter/Lehrer', '',
                new Primary('', __NAMESPACE__.'/Stuff', new Upload())
            ), 2);
        $ColumnList[] = new LayoutColumn(
            new Thumbnail(FileSystem::getFileLoader('/Common/Style/Resource/Schulsoftware-font.png'), 'E-Mail-Adressen', '',
                new Primary('', __NAMESPACE__.'/Mail/Address', new Upload())
            ), 2);
        $ColumnList[] = new LayoutColumn(
            new Thumbnail(FileSystem::getFileLoader('/Common/Style/Resource/Schulsoftware-font.png'), 'Institutionen', '',
                new Primary('', __NAMESPACE__.'/Company', new Upload())
            ), 2);

        return $ColumnList;
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