<?php
namespace SPHERE\Application\Manual;

use SPHERE\Application\IClusterInterface;
use SPHERE\Application\Manual\General\General;
use SPHERE\Application\Manual\Help\Help;
use SPHERE\Application\Manual\Kreda\Kreda;
use SPHERE\Application\Manual\StyleBook\StyleBook;
use SPHERE\Application\Manual\Support\Support;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Setting\MyAccount\MyAccount;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Manual
 *
 * @package SPHERE\Application\Manual
 */
class Manual implements IClusterInterface
{

    public static function registerCluster()
    {

        General::registerApplication();
        Kreda::registerApplication();
        StyleBook::registerApplication();
        Help::registerApplication();
        Support::registerApplication();

        Main::getDisplay()->addServiceNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Hilfe & Support'), new Link\Icon(new Question()))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Hilfe', 'Kontakt');

        $tblConsumer = Consumer::useService()->getConsumerBySession();

        $Stage->setContent(
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('', 4),
                new LayoutColumn(array(
                    new Title('Kontaktdaten', 'Informationen'),
                    new Panel(
                        $tblConsumer->getName().' ['.$tblConsumer->getAcronym().']',
                        array(
                            new Container(implode(MyAccount::useFrontend()->listingSchool()))
                            .new Container(implode(MyAccount::useFrontend()->listingResponsibility()))
                            .new Container(implode(MyAccount::useFrontend()->listingSponsorAssociation()))
                        )
                        , Panel::PANEL_TYPE_INFO
//                        , new Standard('Zugriff auf Mandant ändern', new Route(__NAMESPACE__.'/Consumer'))
                    )
                ), 4),
            ))))
        );

        return $Stage;
    }
}
