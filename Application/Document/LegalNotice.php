<?php
namespace SPHERE\Application\Document;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IClusterInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Stage;

class LegalNotice implements IClusterInterface, IApplicationInterface, IModuleInterface
{

    public static function registerCluster()
    {

        self::registerApplication();
    }

    public static function registerApplication()
    {

        self::registerModule();
    }

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'\LegalNotice', __CLASS__.'::frontendDashboard'
        ));
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {

    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {

    }

    public function frontendDashboard()
    {

        $Stage = new Stage('Impressum', 'alle Angaben nach §5 TMG');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn('', 3),
                        new LayoutColumn(
                            new Center(
                                new Title(new Bold('Herausgeber'))
                                .new Container('ESDi – Evangelische Schulen DienstleistungsGmbH')
                                .new Container('Franklinstr. 22')
                                .new Container('01069 Dresden')
                            )
                        , 6)
                    ))
                ),
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn('', 3),
                        new LayoutColumn(
                            new Center(
                            new Title(new Bold('Geschäftsführer'))
                            .new Container('Niko Kleinknecht')
                            .new Container('Sitz: Dresden')
                            .new Container('HRB: 39730')
                            )
                        , 6)
                    ))
                ),
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn('', 3),
                        new LayoutColumn(
                            new Center(
                            new Title(new Bold('Gesellschafter'))
                            .new Container('Schulstiftung der Ev.-Luth. Landeskirche Sachsens')
                            .new Container('Franklinstr. 22')
                            .new Container('01069 Dresden')
                            )
                        , 6)
                    ))
                ),
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn('', 3),
                        new LayoutColumn(
                            new Center(
                                new Title(new Bold('Kontakt'))
                                .new Container('Tel.: +49 (0) 351 479330618')
                                .new Container('Mail: info@esdigmbh.de')
                            )
                        , 6)
                    ))
                ),
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn('', 3),
                        new LayoutColumn(
                            new Center(
                                new Title(new Bold('Programmierung'))
                                .new Container('K&W Informatik GmbH')
                                .new Container('Feldstraße 2')
                                .new Container('09366 Niederdorf / Erz.')
                            )
                        , 6)
                    ))
                ),
            ))
        );
        return $Stage;
    }
}
