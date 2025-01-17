<?php
namespace SPHERE\Application\Document;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IClusterInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\Icon\Repository\Conversation;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Success;
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
                                '<h4>'.new Bold('Herausgeber').'</h4>'
                                .new Listing(array(
                                 new Container('ESDi – Evangelische Schulen DienstleistungsGmbH')
                                .new Container('Franklinstr. 22')
                                .new Container('01069 Dresden')
                                ))
                            )
                        , 6)
                    ))
                ),
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn('', 3),
                        new LayoutColumn(
                            new Center('<h4>'.new Bold('Geschäftsführer').'</h4>')
                            .new Listing(array(
                                new Layout(new LayoutGroup(new LayoutRow(array(
                                    new LayoutColumn('', 5),
                                    new LayoutColumn(
                                        new Container('Thomas Melcher')
                                        .new Container('Sitz: Dresden')
                                        .new Container('HRB: 39730')
                                    , 7)
                                ))))
                            ))
                        , 6)
                    ))
                ),
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn('', 3),
                        new LayoutColumn(
                            new Center(
                                '<h4>'.new Bold('Gesellschafter').'</h4>'
                                .new Listing(array(
                                    new Container('Schulstiftung der Ev.-Luth. Landeskirche Sachsens')
                                    .new Container('Franklinstr. 22')
                                    .new Container('01069 Dresden')
                                ))
                            )
                        , 6)
                    ))
                ),
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn('', 3),
                        new LayoutColumn(
                            new Center(
                                '<h4>'.new Bold('Kontakt').'</h4>'
                                .new Listing(array(
                                    new Container('Tel.: +49 (0) 351 479330618')
                                    .new Container('Mail: info@esdigmbh.de')
                                ))
                            )
                        , 6)
                    ))
                ),
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn('', 3),
                        new LayoutColumn(
                            new Center(
                                '<h4>'.new Bold('Programmierung').'</h4>'
                                .new Listing(array(
                                    new Container('K&W Informatik GmbH')
                                    .new Container('Feldstraße 2')
                                    .new Container('09366 Niederdorf / Erz.')
                                ))
                            )
                        , 6)
                    ))
                ),
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn('', 3),
                        new LayoutColumn(
                            new Center('<h4>'.new Bold(new Conversation().' Support').'</h4>')
                            .new Success(new Center('Bitte wenden Sie sich für den Support der Schulsoftware direkt
                            an die entsprechenden Ansprechpartner in Ihrer Schule.'), null, false, 7)
                        , 6)
                    ))
                )
            ))
        );
        return $Stage;
    }
}
