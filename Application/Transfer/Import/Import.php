<?php
namespace SPHERE\Application\Transfer\Import;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Transfer\Import\Chemnitz\Chemnitz;
use SPHERE\Application\Transfer\Import\FuxMedia\FuxSchool;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Import
 *
 * @package SPHERE\Application\Transfer\Import
 */
class Import implements IApplicationInterface
{

    public static function registerApplication()
    {

        FuxSchool::registerModule();
        Chemnitz::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Daten importieren'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));

        Main::getDispatcher()->registerWidget('Import',
            new Thumbnail(
                FileSystem::getFileLoader('/Common/Style/Resource/fuxschool.gif'),
                'FuxSchool', 'Schülerdaten',
                new Standard('', '/Transfer/Import/FuxMedia/Student', new Upload(), array(), 'Upload')
            ), 2, 2
        );
//        Main::getDispatcher()->registerWidget('Transfer',
//            new Thumbnail(
//                FileSystem::getFileLoader('/Common/Style/Resource/fuxschool.gif'),
//                'FuxSchool', 'Schülerdaten',
//                new Standard('', '/Transfer/Import/FuxMedia/Student', new Upload(), array(), 'Upload')
//            ), 2, 2
//        );
        Main::getDispatcher()->registerWidget('Import',
            new Thumbnail(
                FileSystem::getFileLoader('/Common/Style/Resource/fuxschool.gif'),
                'FuxSchool', 'Klassendaten',
                new Standard('', '/Sphere/Transfer/Import/FuxSchool/Division', new Upload(), array(), 'Upload')
            ), 2, 2
        );
//        Main::getDispatcher()->registerWidget('Transfer',
//            new Thumbnail(
//                FileSystem::getFileLoader('/Common/Style/Resource/fuxschool.gif'),
//                'FuxSchool', 'Klassendaten',
//                new Standard('', '/Sphere/Transfer/Import/FuxSchool/Division', new Upload(), array(), 'Upload')
//            ), 2, 2
//        );

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
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Dashboard', 'Import');

        $Stage->setContent(Main::getDispatcher()->fetchDashboard('Import'));

        return $Stage;
    }
}
