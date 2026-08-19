<?php

namespace SPHERE\Application\Transfer\Import\Competence;

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

class Competence implements IModuleInterface
{
    /**
     * @return void
     */
    public static function registerModule(): void
    {
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__  . '/SkillGrid', __NAMESPACE__ . '\Frontend::frontendSkillGridImport'
        ));
    }

    /**
     * @return Service
     */
    public static function useService(): Service
    {
        return new Service();
    }

    /**
     * @return Frontend
     */
    public static function useFrontend(): Frontend
    {
        return new Frontend();
    }

    /**
     * @return string
     */
    public static function getDownloadLayout(): string
    {
        return new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn(new Thumbnail(
                FileSystem::getFileLoader('/Common/Style/Resource/Schulsoftware-font.png'),
                'Kompetenz', 'Kompetenzraster',
                new Standard('', '/Transfer/Import/Competence/SkillGrid', new Upload(), array(), 'Upload')
            ), 2),
        ))));
    }
}