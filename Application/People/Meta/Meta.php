<?php
namespace SPHERE\Application\People\Meta;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Custody\Custody;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Warning;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Layout\Structure\LayoutTab;
use SPHERE\Common\Frontend\Layout\Structure\LayoutTabs;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Meta
 *
 * @package SPHERE\Application\People\Meta
 */
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

    /**
     * @param bool|false $TabActive
     *
     * @return Stage
     */
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
                    if (!$TabActive) {
                        $tblGroup->setActive();
                    }
                    break;
                case 'PROSPECT':
                    $tblGroup = new LayoutTab( 'Interessent', $tblGroup->getMetaTable() );
                    break;
                case 'STUDENT':
                    $tblGroup = new LayoutTab( 'Schülerakte', $tblGroup->getMetaTable() );
                    break;
                case 'CUSTODY':
                    $tblGroup = new LayoutTab( 'Sorgeberechtigt', $tblGroup->getMetaTable() );
                    break;
                default:
                    $tblGroup = false;
            }
        }, $TabActive );
        $tblGroupList = array_filter( $tblGroupList );

        $tblSalutationAll = Person::useService()->getSalutationAll();
        $BasicTable = ( new Form(
            new FormGroup(
                new FormRow( array(
                    new FormColumn(
                        new SelectBox( 'Person[Salutation]', 'Anrede', array( 'Salutation' => $tblSalutationAll ) )
                        , 2 ),
                    new FormColumn(
                        new TextField( 'Person[Title]' )
                        , 2 ),
                    new FormColumn(
                        new TextField( 'Person[FirstName]' )
                        , 3 ),
                    new FormColumn(
                        new TextField( 'Person[SecondName]' )
                        , 2 ),
                    new FormColumn(
                        new TextField( 'Person[LastName]' )
                        , 3 ),
                ) )
            )
        ) )->setConfirm( 'Eventuelle Änderungen wurden noch nicht gespeichert' );

        switch (strtoupper( $TabActive )) {
            case 'COMMON':
                $MetaTable = Common::useFrontend()->frontendMeta();
                break;
            case 'PROSPECT':
                $MetaTable = Prospect::useFrontend()->frontendMeta();
                break;
            case 'STUDENT':
                $MetaTable = Student::useFrontend()->frontendMeta();
                break;
            case 'CUSTODY':
                $MetaTable = Custody::useFrontend()->frontendMeta();
                break;
            default:
                $MetaTable = new Danger( 'Ansicht nicht verfügbar', new Warning() );
        }

        $Stage->setContent(
            new Layout( array(
                new LayoutGroup(
                    new LayoutRow( new LayoutColumn( $BasicTable ) )
                    , new Title( 'Name', 'der Person' )
                ),
                new LayoutGroup( array(
                    new LayoutRow( new LayoutColumn( new LayoutTabs( $tblGroupList ) ) ),
                    new LayoutRow( new LayoutColumn( $MetaTable ) ),
                ), new Title( 'Metadaten', 'der Person' ) ),
                new LayoutGroup(
                    new LayoutRow( new LayoutColumn( $MetaTable ) )
                ),
            ) )
        );
        return $Stage;
    }
}
