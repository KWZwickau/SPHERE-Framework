<?php
namespace SPHERE\Application\Setting\Consumer;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Setting\Consumer\Responsibility\Responsibility;
use SPHERE\Application\Setting\Consumer\Responsibility\Service\Entity\TblResponsibility;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Application\Setting\Consumer\School\Service\Entity\TblSchool;
use SPHERE\Application\Setting\Consumer\SponsorAssociation\Service\Entity\TblSponsorAssociation;
use SPHERE\Application\Setting\Consumer\SponsorAssociation\SponsorAssociation;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
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

/**
 * Class Consumer
 *
 * @package SPHERE\Application\Setting\Consumer
 */
class Consumer implements IApplicationInterface
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

        $tblSchoolAll = School::useService()->getSchoolAll();
        if ($tblSchoolAll) {
            /** @var TblSchool $tblSchool */
            foreach ((array)$tblSchoolAll as $Index => $tblSchool) {
                $tblSchoolAll[$tblSchool->getServiceTblCompany()->getName().$tblSchool->getServiceTblType()->getName()] =
                    new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn(
                                new Muted(new Small($tblSchool->getServiceTblCompany()->getDescription())).'<br/>'
                                .$tblSchool->getServiceTblCompany()->getName()
                                , 12),
                        )
                    )));
                $tblSchoolAll[$Index] = false;
            }
            $tblSchoolAll = array_filter($tblSchoolAll);
        } else {
            $tblSchoolAll = new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(new Warning('Kein Eintrag',
                new Remove())))));
        }
        $tblResponsibilityAll = Responsibility::useService()->getResponsibilityAll();
        if ($tblResponsibilityAll) {
            /** @var TblResponsibility $tblResponsibility */
            foreach ((array)$tblResponsibilityAll as $Index => $tblResponsibility) {
                $tblResponsibilityAll[$tblResponsibility->getServiceTblCompany()->getName()] =
                    new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn(
                                $tblResponsibility->getServiceTblCompany()->getName()
                                .new Muted(new Small('<br/>'.$tblResponsibility->getServiceTblCompany()->getDescription()))
                                , 12),
                        )
                    )));
                $tblResponsibilityAll[$Index] = false;
            }
            $tblResponsibilityAll = array_filter($tblResponsibilityAll);
        } else {
            $tblResponsibilityAll = new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(new Warning('Kein Eintrag',
                new Remove())))));
        }
        $tblSponsorAssociationAll = SponsorAssociation::useService()->getSponsorAssociationAll();
        if ($tblSponsorAssociationAll) {
            /** @var TblSponsorAssociation $tblSponsorAssociation */
            foreach ((array)$tblSponsorAssociationAll as $Index => $tblSponsorAssociation) {
                $tblSponsorAssociationAll[$tblSponsorAssociation->getServiceTblCompany()->getName()] =
                    new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn(
                                $tblSponsorAssociation->getServiceTblCompany()->getName()
                                .new Muted(new Small('<br/>'.$tblSponsorAssociation->getServiceTblCompany()->getDescription()))
                                , 12),
                        )
                    )));
                $tblSponsorAssociationAll[$Index] = false;
            }
            $tblSponsorAssociationAll = array_filter($tblSponsorAssociationAll);
        } else {
            $tblSponsorAssociationAll = new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(new Warning('Kein Eintrag',
                new Remove())))));
        }
        if (empty( $tblSchoolAll )) {
            $tblSchoolAll = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(array(
                    new Muted('Kein Eintrag'),
                    new PullRight(new Standard(new Pencil(), '/Setting/Consumer/School/Create'))
                ), 12),
            ))));
        }
        if (empty( $tblResponsibilityAll )) {
            $tblResponsibilityAll = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(array(
                    new Muted('Kein Eintrag'),
                    new PullRight(new Standard(new Pencil(), '/Setting/Consumer/Responsibility/Create'))
                ), 12),
            ))));
        }
        if (empty( $tblSponsorAssociationAll )) {
            $tblSponsorAssociationAll = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(array(
                    new Muted('Kein Eintrag'),
                    new PullRight(new Standard(new Pencil(), '/Setting/Consumer/SponsorAssociation/Create'))
                ), 12),
            ))));
        }

        Main::getDispatcher()->registerWidget('Consumer',
            new Panel('Schule', $tblSchoolAll), 2, 2);
        Main::getDispatcher()->registerWidget('Consumer',
            new Panel('Schulträger', $tblResponsibilityAll), 2, 2);
        Main::getDispatcher()->registerWidget('Consumer',
            new Panel('Förderverein', $tblSponsorAssociationAll), 2, 2);
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
}
