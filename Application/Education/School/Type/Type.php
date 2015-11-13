<?php
namespace SPHERE\Application\Education\School\Type;

use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Type
 *
 * @package SPHERE\Application\Education\School\Type
 */
class Type implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Schulart'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));

        $tblTypeAll = self::useService()->getTypeAll();
        if ($tblTypeAll) {
            /** @var TblType $tblType */
            foreach ((array)$tblTypeAll as $Index => $tblType) {
                $tblTypeAll[$tblType->getName()] =
                    new Layout(new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn(
                            $tblType->getName()
                            .new Muted(new Small('<br/>'.$tblType->getDescription()))
                        ),
                    ))));
                $tblTypeAll[$Index] = false;
            }
            $tblTypeAll = array_filter($tblTypeAll);
            Main::getDispatcher()->registerWidget('School-Type', new Panel('Schularten verfügbar', $tblTypeAll), 3, 3);
        }
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier('Education', 'School', 'Type', null, Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Dashboard', 'Schulart');

        $Stage->setMessage(
            new Warning('Schularten sind im Moment fest hinterlegt')
        );

        $Stage->setContent(Main::getDispatcher()->fetchDashboard('School-Type'));

        return $Stage;
    }
}
