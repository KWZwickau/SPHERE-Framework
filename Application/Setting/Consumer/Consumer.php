<?php
namespace SPHERE\Application\Setting\Consumer;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Setting\Consumer\Responsibility\Responsibility;
use SPHERE\Application\Setting\Consumer\Responsibility\Service\Entity\TblResponsibility;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Application\Setting\Consumer\School\Service\Entity\TblSchool;
use SPHERE\Application\Setting\Consumer\SponsorAssociation\Service\Entity\TblSponsorAssociation;
use SPHERE\Application\Setting\Consumer\SponsorAssociation\SponsorAssociation;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Consumer
 *
 * @package SPHERE\Application\Setting\Consumer
 */
class Consumer implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        School::registerModule();
        Responsibility::registerModule();
        SponsorAssociation::registerModule();

        Main::getDisplay()->addApplicationNavigation(new Link(new Link\Route(__NAMESPACE__),
            new Link\Name('Mandant'), new Link\Icon(new Building())
        ));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, 'Consumer::frontendDashboard'
        ));

        Main::getDispatcher()->registerWidget('Consumer', array(__CLASS__, 'widgetSchool'), 2, 2);
        Main::getDispatcher()->registerWidget('Consumer', array(__CLASS__, 'widgetResponsibility'), 2, 2);
        Main::getDispatcher()->registerWidget('Consumer', array(__CLASS__, 'widgetSponsorAssociation'), 2, 2);
    }

    /**
     * @return Panel
     */
    public static function widgetSchool()
    {
        $tblSchoolAll = School::useService()->getSchoolAll();
        if ($tblSchoolAll) {
            /** @var TblSchool $tblSchool */
            foreach ((array)$tblSchoolAll as $Index => $tblSchool) {
                if ($tblSchool->getServiceTblCompany() && $tblSchool->getServiceTblType()) {
                    $tblSchoolAll[$tblSchool->getServiceTblCompany()->getName() . $tblSchool->getServiceTblType()->getName()] =
                        new Layout(new LayoutGroup(new LayoutRow(array(
                                new LayoutColumn(
                                    $tblSchool->getServiceTblCompany()->getName()
                                    .( $tblSchool->getServiceTblCompany()->getExtendedName() != '' ?
                                        new Container($tblSchool->getServiceTblCompany()->getExtendedName()) : null )
                                    .( $tblSchool->getServiceTblCompany()->getDescription() != '' ?
                                        new Container(new Muted(new Small($tblSchool->getServiceTblCompany()->getDescription()))) : null )
                                    , 12),
                            )
                        )));
                }
                $tblSchoolAll[$Index] = false;
            }
            $tblSchoolAll = array_filter($tblSchoolAll);
        } else {
            $tblSchoolAll = new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(new Warning('Kein Eintrag',
                new Remove())))));
        }

        if (empty($tblSchoolAll)) {
            $tblSchoolAll = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(array(
                    new Muted('Kein Eintrag'),
                    new PullRight(new Standard(new Pencil(), '/Setting/Consumer/School/Create'))
                ), 12),
            ))));
        }

        return new Panel('Schule', $tblSchoolAll);
    }

    /**
     * @return Panel
     */
    public static function widgetResponsibility()
    {
        $tblResponsibilityAll = Responsibility::useService()->getResponsibilityAll();
        if ($tblResponsibilityAll) {
            /** @var TblResponsibility $tblResponsibility */
            foreach ((array)$tblResponsibilityAll as $Index => $tblResponsibility) {
                if ($tblResponsibility->getServiceTblCompany()) {
                    $tblResponsibilityAll[$tblResponsibility->getServiceTblCompany()->getName()] =
                        new Layout(new LayoutGroup(new LayoutRow(array(
                                new LayoutColumn(
                                    $tblResponsibility->getServiceTblCompany()->getName()
                                    .( $tblResponsibility->getServiceTblCompany()->getExtendedName() != '' ?
                                        new Container($tblResponsibility->getServiceTblCompany()->getExtendedName()) : null )
                                    .( $tblResponsibility->getServiceTblCompany()->getDescription() != '' ?
                                        new Container(new Muted(new Small($tblResponsibility->getServiceTblCompany()->getDescription()))) : null )
                                    , 12),
                            )
                        )));
                }
                $tblResponsibilityAll[$Index] = false;
            }
            $tblResponsibilityAll = array_filter($tblResponsibilityAll);
        } else {
            $tblResponsibilityAll = new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(new Warning('Kein Eintrag',
                new Remove())))));
        }

        if (empty($tblResponsibilityAll)) {
            $tblResponsibilityAll = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(array(
                    new Muted('Kein Eintrag'),
                    new PullRight(new Standard(new Pencil(), '/Setting/Consumer/Responsibility/Create'))
                ), 12),
            ))));
        }
        return new Panel('Schulträger', $tblResponsibilityAll);
    }

    /**
     * @return Panel
     */
    public static function widgetSponsorAssociation()
    {
        $tblSponsorAssociationAll = SponsorAssociation::useService()->getSponsorAssociationAll();
        if ($tblSponsorAssociationAll) {
            /** @var TblSponsorAssociation $tblSponsorAssociation */
            foreach ((array)$tblSponsorAssociationAll as $Index => $tblSponsorAssociation) {
                if ($tblSponsorAssociation->getServiceTblCompany()) {
                    $tblSponsorAssociationAll[$tblSponsorAssociation->getServiceTblCompany()->getName()] =
                        new Layout(new LayoutGroup(new LayoutRow(array(
                                new LayoutColumn(
                                    $tblSponsorAssociation->getServiceTblCompany()->getName()
                                    .( $tblSponsorAssociation->getServiceTblCompany()->getExtendedName() != '' ?
                                        new Container($tblSponsorAssociation->getServiceTblCompany()->getExtendedName()) : null )
                                    .( $tblSponsorAssociation->getServiceTblCompany()->getDescription() != '' ?
                                        new Container(new Muted(new Small($tblSponsorAssociation->getServiceTblCompany()->getDescription()))) : null )
                                    , 12),
                            )
                        )));
                }
                $tblSponsorAssociationAll[$Index] = false;
            }
            $tblSponsorAssociationAll = array_filter($tblSponsorAssociationAll);
        } else {
            $tblSponsorAssociationAll = new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(new Warning('Kein Eintrag',
                new Remove())))));
        }

        if (empty( $tblSponsorAssociationAll )) {
            $tblSponsorAssociationAll = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(array(
                    new Muted('Kein Eintrag'),
                    new PullRight(new Standard(new Pencil(), '/Setting/Consumer/SponsorAssociation/Create'))
                ), 12),
            ))));
        }
        return new Panel('Förderverein', $tblSponsorAssociationAll);
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Dashboard', 'Mandant');

        $Stage->setContent(Main::getDispatcher()->fetchDashboard('Consumer'));

        return $Stage;
    }

    public static function registerModule()
    {

    }

    /**
     * @return Service
     */
    public static function useService()
    {
        return new Service(
            new Identifier('Setting', 'Consumer', null, null,
                \SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }

    public static function useFrontend()
    {

    }
}
