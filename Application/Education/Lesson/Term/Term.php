<?php
namespace SPHERE\Application\Education\Lesson\Term;

use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Clock;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Time;
use SPHERE\Common\Frontend\Layout\Repository\Headline;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Term
 *
 * @package SPHERE\Application\Education\Lesson\Term
 */
class Term implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Schuljahr'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Create/Year', __NAMESPACE__.'\Frontend::frontendCreateYear'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Edit/Year', __NAMESPACE__.'\Frontend::frontendEditYear'
        )->setParameterDefault('Id', null)
            ->setParameterDefault('Year', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Destroy/Year', __NAMESPACE__.'\Frontend::frontendDestroyYear'
        )->setParameterDefault('Id', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Create/Period', __NAMESPACE__.'\Frontend::frontendCreatePeriod'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Edit/Period', __NAMESPACE__.'\Frontend::frontendEditPeriod'
        )->setParameterDefault('Id', null)
            ->setParameterDefault('Period', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Destroy/Period', __NAMESPACE__.'\Frontend::frontendDestroyPeriod'
        )->setParameterDefault('Id', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Link/Period', __NAMESPACE__.'\Frontend::frontendLinkPeriod'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Choose/Period', __NAMESPACE__.'\Frontend::frontendChoosePeriod'
        )->setParameterDefault('Id', null)
            ->setParameterDefault('PeriodId', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Remove/Period', __NAMESPACE__.'\Frontend::frontendRemovePeriod'
        )->setParameterDefault('PeriodId', null)
            ->setParameterDefault('Id', null)
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

        $Stage = new Stage('Dashboard', 'Schuljahr');

        $Stage->addButton(new Standard('Schuljahr', __NAMESPACE__ . '\Create\Year', new Calendar()));
        $Stage->addButton(new Standard('Zeitraum', __NAMESPACE__ . '\Create\Period', new Time()));

        $tblYearAll = Term::useService()->getYearAll();
        $Year = array();
        if ($tblYearAll) {
            array_walk($tblYearAll, function (TblYear $tblYear) use (&$Year) {

                $tblPeriodAll = $tblYear->getTblPeriodAll();
                if ($tblPeriodAll) {
                    /** @noinspection PhpUnusedParameterInspection */
                    array_walk($tblPeriodAll, function (TblPeriod &$tblPeriod, $index, TblYear $tblYear) {

                        $tblPeriod = $tblPeriod->getName().' '.new Muted(new Small($tblPeriod->getDescription()))
                            .new PullRight(new Standard('', __NAMESPACE__.'\Remove\Period', new Remove(),
                                array('PeriodId' => $tblPeriod->getId(),
                                      'Id'       => $tblYear->getId()), 'Zeitraum entfernen'))
                            .'<br/>'.$tblPeriod->getFromDate().' - '.$tblPeriod->getToDate();
                    }, $tblYear);
                } else {
                    $tblPeriodAll = array();
                }
                array_push($Year, array(
                    'Schuljahr' => $tblYear->getName().'<br/>'.new Muted($tblYear->getDescription()),
                    'Zeiträume' => new Panel(
                        ( empty( $tblPeriodAll ) ?
                            new Standard('', __NAMESPACE__.'\Choose\Period', new Clock(),
                                array('Id' => $tblYear->getId()), 'Zeitraum hinzufügen'
                            ).'Keine Zeiträume hinterlegt'
                            : new Standard('', __NAMESPACE__.'\Choose\Period', new Clock(),
                                array('Id' => $tblYear->getId()), 'Zeitraum hinzufügen'
                            ).count($tblPeriodAll).' Zeiträume' ),
                        $tblPeriodAll,
                        ( empty( $tblPeriodAll ) ? Panel::PANEL_TYPE_WARNING : Panel::PANEL_TYPE_DEFAULT )),
//                    'Optionen'  =>
//                        new Standard('', __NAMESPACE__.'\Edit\Year', new Pencil(),
//                            array('Id' => $tblYear->getId()), 'Bearbeiten'
//                        ).
//                        new Standard('', __NAMESPACE__.'\Choose\Period', new Clock(),
//                            array('Id' => $tblYear->getId()), 'Zeitraum'
//                        )
//                        .( empty( $tblPeriodAll )
//                            ? new Standard('', __NAMESPACE__.'\Destroy\Year', new Remove(),
//                                array('Id' => $tblYear->getId()), 'Löschen'
//                            ) : ''
//                        )
                ));
            });
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Headline('Im System vorhandene Schuljahre'),
                            new TableData(
                                $Year, null, array(
                                    'Schuljahr' => 'Schuljahr',
                                    'Zeiträume' => 'Zeiträume',
//                                    'Optionen'  => 'Zuordnung'
                                )
                            )
                        ))
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier('Education', 'Lesson', 'Term', null, Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }
}
