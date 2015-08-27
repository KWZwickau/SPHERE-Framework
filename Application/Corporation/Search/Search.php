<?php
namespace SPHERE\Application\Corporation\Search;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Corporation\Group\Group;
use SPHERE\Application\Corporation\Group\Service\Entity\TblGroup;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Italic;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Search
 *
 * @package SPHERE\Application\Corporation\Search
 */
class Search implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        self::registerModule();

        /**
         * Register Navigation
         */
        Main::getDisplay()->addApplicationNavigation(
            new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'Firmensuche' ),
                new Link\Icon( new Info() )
            )
        );
        Main::getDispatcher()->registerRoute( Main::getDispatcher()->createRoute(
            __NAMESPACE__, 'SPHERE\Application\Corporation\Corporation::frontendDashboard'
        ) );
    }

    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link( new Link\Route( __NAMESPACE__.'/Group' ), new Link\Name( 'Nach Firmengruppe' ),
                new Link\Icon( new Info() )
            )
        );
        Main::getDispatcher()->registerRoute( Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Group', __CLASS__.'::frontendGroup'
        ) );
        Main::getDisplay()->addModuleNavigation(
            new Link( new Link\Route( __NAMESPACE__.'/Attribute' ), new Link\Name( 'Nach Eigenschaften' ),
                new Link\Icon( new Info() )
            )
        );
        Main::getDispatcher()->registerRoute( Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Attribute', __CLASS__.'::frontendAttribute'
        ) );

    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

    public function frontendGroup( $Id = false )
    {

        $Stage = new Stage( 'Firmensuche', 'nach Firmengruppe' );

        $tblGroup = Group::useService()->getGroupById( $Id );

        if ($tblGroup) {
            $Stage->setMessage(
                new PullClear( new Bold( $tblGroup->getName() ).' '.new Small( $tblGroup->getDescription() ) ).
                new PullClear( new Danger( new Italic( nl2br( $tblGroup->getRemark() ) ) ) )
            );
        } else {
            $Stage->setMessage( 'Bitte wählen Sie eine Firmengruppe' );
        }

        $tblGroupAll = Group::useService()->getGroupAll();

        /** @noinspection PhpUnusedParameterInspection */
        array_walk( $tblGroupAll, function ( TblGroup &$tblGroup, $Index, Stage $Stage ) {

            $Stage->addButton(
                new Standard(
                    $tblGroup->getName(),
                    new Link\Route( __NAMESPACE__.'/Group' ), null,
                    array(
                        'Id' => $tblGroup->getId()
                    ), $tblGroup->getDescription() )
            );
        }, $Stage );

        if ($tblGroup) {

            // TODO: Company-List

            $tblCompanyAll = Group::useService()->getCompanyAllByGroup( $tblGroup );

            array_walk( $tblCompanyAll, function ( TblCompany &$tblCompany ) {

                $tblCompany->Option = new Standard( '', '/Corporation/Company', new Pencil(),
                    array( 'Id' => $tblCompany->getId() ), 'Bearbeiten' );
            } );

//            Debugger::screenDump( $tblCompanyAll );

            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData( $tblCompanyAll, null,
                                    array(
                                        'Id'     => '#',
                                        'Name'   => 'Name',
                                        'Option' => 'Optionen',
                                    ) )
                            )
                        )
                    )
                )
            );
        }

        return $Stage;
    }


}
