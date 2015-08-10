<?php
namespace SPHERE\Application\People\Meta;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Custody\Custody;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Common\Frontend\Icon\Repository\Warning;
use SPHERE\Common\Frontend\Layout\Structure\LayoutTab;
use SPHERE\Common\Frontend\Layout\Structure\LayoutTabs;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

class Meta implements IApplicationInterface
{

    public static function registerApplication()
    {

        Common::registerModule();
        Prospect::registerModule();
        Student::registerModule();
        Custody::registerModule();

        Main::getDispatcher()->registerRoute( Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendMeta'
        ) );
    }

    public function frontendMeta( $TabActive = false )
    {

        $Stage = new Stage( 'Person', 'Datenblatt' );

        // TODO: Group-List of Person
        $tblGroupList = Group::useService()->getGroupAll();

        // Sort by Name
        usort( $tblGroupList, function ( TblGroup $ObjectA, TblGroup $ObjectB ) {

            return strnatcmp( $ObjectA->getName(), $ObjectB->getName() );
        } );

        // Create Tabs
        /** @noinspection PhpUnusedParameterInspection */
        array_walk( $tblGroupList, function ( TblGroup &$tblGroup, $Index, $TabActive ) {

            switch (strtoupper( $tblGroup->getMetaTable() )) {
                case 'COMMON':
                    $tblGroup = new LayoutTab( 'Allgemein', $tblGroup->getMetaTable() );
                    if( !$TabActive ) {
                        $tblGroup->setActive();
                    }
                    break;
                case 'PROSPECT':
                    $tblGroup = new LayoutTab( 'Interessent', $tblGroup->getMetaTable() );
                    break;
                case 'STUDENT':
                    $tblGroup = new LayoutTab( 'Schüler', $tblGroup->getMetaTable() );
                    break;
                case 'CUSTODY':
                    $tblGroup = new LayoutTab( 'Sorgeberechtigt', $tblGroup->getMetaTable() );
                    break;
                default:
                    $tblGroup = false;
            }
        }, $TabActive );
        $tblGroupList = array_filter( $tblGroupList );

        switch (strtoupper( $TabActive )) {
            case 'COMMON':
                $Form = Common::useFrontend()->frontendMeta();
                break;
            case 'PROSPECT':
                $Form = Prospect::useFrontend()->frontendMeta();
                break;
            case 'STUDENT':
                $Form = Student::useFrontend()->frontendMeta();
                break;
            case 'CUSTODY':
                $Form = Custody::useFrontend()->frontendMeta();
                break;
            default:
                $Form = new Danger( 'Ansicht nicht verfügbar', new Warning() );
        }

        $Stage->setContent(
            new Stage( 'Name' )
            .new LayoutTabs( $tblGroupList )
            .$Form
        );
        return $Stage;
    }
}
