<?php
namespace SPHERE\Application\Transfer\Import;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Transfer\Import\FuxMedia\FuxSchool;
use SPHERE\Application\Transfer\Import\Standard\ImportStandard;
use SPHERE\Application\Transfer\Import\Standard\Mail\Mail;
use SPHERE\Common\Frontend\Layout\Repository\Ruler;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Text\Repository\Bold;
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
        ImportStandard::registerModule();
        Mail::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Daten importieren'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__ . '::frontendDashboard'
        ));
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Dashboard', 'Import');
        $Stage->setContent(
            new Bold('<H3>Standard Import</H3>')
            .new Layout(new LayoutGroup(array(
                new LayoutRow(ImportStandard::getStandardLink()),
                new LayoutRow(new LayoutColumn(
                    new Ruler()
                    .FuxSchool::getDownloadLayout()
                ))
            )))
        );
        return $Stage;
    }
}
