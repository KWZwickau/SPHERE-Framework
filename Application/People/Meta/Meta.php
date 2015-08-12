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
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\ChevronUp;
use SPHERE\Common\Frontend\Icon\Repository\Conversation;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Tag;
use SPHERE\Common\Frontend\Icon\Repository\TagList;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Layout\Structure\LayoutTab;
use SPHERE\Common\Frontend\Layout\Structure\LayoutTabs;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Repository\Debugger;

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
    public function frontendMeta( $TabActive = false, $tblPerson = null, $Person = array(), $Meta = array() )
    {

//        Debugger::screenDump( __METHOD__, func_get_args() );

        $Stage = new Stage( 'Personen', 'Datenblatt' );

        // TODO: Group-List of Person
        $tblGroupList = Group::useService()->getGroupAll();
        $MetaTabs = $tblGroupList;

        // Sort by Name
        usort( $tblGroupList, function ( TblGroup $ObjectA, TblGroup $ObjectB ) {

            return strnatcmp( $ObjectA->getName(), $ObjectB->getName() );
        } );
        usort( $MetaTabs, function ( TblGroup $ObjectA, TblGroup $ObjectB ) {

            return strnatcmp( $ObjectA->getName(), $ObjectB->getName() );
        } );

        // Create CheckBoxes
        /** @noinspection PhpUnusedParameterInspection */
        array_walk( $tblGroupList, function ( TblGroup &$tblGroup ) {

            $tblGroup = new CheckBox(
                    'Person[Group]['.$tblGroup->getId().']',
                    $tblGroup->getName().' '.new Muted( new Small( $tblGroup->getDescription() ) ),
                    $tblGroup->getId()
                );
        } );

        // Create Tabs
        /** @noinspection PhpUnusedParameterInspection */
        array_walk( $MetaTabs, function ( TblGroup &$tblGroup, $Index, $TabActive ) {

            switch (strtoupper( $tblGroup->getMetaTable() )) {
                case 'COMMON':
                    $tblGroup = new LayoutTab( 'Allgemein', $tblGroup->getMetaTable() );
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
        /** @var LayoutTab[] $MetaTabs */
        $MetaTabs = array_filter( $MetaTabs );
        // Folded ?
        if (!$TabActive || $TabActive == '#') {
            array_unshift( $MetaTabs, new LayoutTab( '&nbsp;'.new ChevronRight().'&nbsp;', '#' ) );
            $MetaTabs[0]->setActive();
        } else {
            array_unshift( $MetaTabs, new LayoutTab( '&nbsp;'.new ChevronUp().'&nbsp;', '#' ) );
        }

        $tblSalutationAll = Person::useService()->getSalutationAll();
        $BasicTable = ( new Form(
            new FormGroup( array(
                new FormRow( array(
                    new FormColumn(
                        new Panel( 'Anrede', array(
                            new SelectBox( 'Person[Salutation]', 'Anrede', array( 'Salutation' => $tblSalutationAll ),
                                new Conversation() ),
                            new TextField( 'Person[Title]', 'Titel', 'Titel', new Conversation() ),
                        ), Panel::PANEL_TYPE_INFO ), 4 ),
                    new FormColumn(
                        new Panel( 'Name', array(
                            new TextField( 'Person[FirstName]', 'Vorname', 'Vorname' ),
                            new TextField( 'Person[SecondName]', 'Zweitname', 'Zweitname' ),
                            new TextField( 'Person[LastName]', 'Nachname', 'Nachname' ),
                        ), Panel::PANEL_TYPE_INFO ), 4 ),
                    new FormColumn(
                        new Panel( 'Gruppen', $tblGroupList, Panel::PANEL_TYPE_INFO ), 4 ),
                ) )
            ) ), new Primary( 'Grunddaten speichern' )
        ) )->setConfirm( 'Eventuelle Änderungen wurden noch nicht gespeichert' );

        switch (strtoupper( $TabActive )) {
            case 'COMMON':
                $MetaTable = Common::useFrontend()->frontendMeta( $tblPerson, $Meta );
                break;
            case 'PROSPECT':
                $MetaTable = Prospect::useFrontend()->frontendMeta( $tblPerson, $Meta );
                break;
            case 'STUDENT':
                $MetaTable = Student::useFrontend()->frontendMeta( $tblPerson, $Meta );
                break;
            case 'CUSTODY':
                $MetaTable = Custody::useFrontend()->frontendMeta( $tblPerson, $Meta );
                break;
            default:
                $MetaTable = new Well( new Muted( 'Bitte wählen Sie eine Rubrik' ) );
        }

        $Stage->setContent(
            new Layout( array(
                new LayoutGroup(
                    new LayoutRow( new LayoutColumn( $BasicTable ) )
                    , new Title( new \SPHERE\Common\Frontend\Icon\Repository\Person().' Name', 'der Person' )
                ),
                new LayoutGroup( array(
                    new LayoutRow( new LayoutColumn( new LayoutTabs( $MetaTabs ) ) ),
                    new LayoutRow( new LayoutColumn( $MetaTable ) ),
                ), new Title( new Tag().' Informationen', 'zur Person' ) ),
                new LayoutGroup( array(
                    new LayoutRow( new LayoutColumn(
                        new Warning( 'Keine Daten vorhanden' )
                        .new PullRight( new \SPHERE\Common\Frontend\Link\Repository\Warning( 'Hinzufügen', '/',
                            new Plus() ) )
                    ) ),
                ), new Title( new TagList().' Adressdaten', 'der Person' ) ),
                new LayoutGroup( array(
                    new LayoutRow( new LayoutColumn(
                        new Warning( 'Keine Daten vorhanden' )
                        .new PullRight( new \SPHERE\Common\Frontend\Link\Repository\Warning( 'Hinzufügen', '/',
                            new Plus() ) )
                    ) ),
                ), new Title( new TagList().' Kontaktdaten', 'der Person' ) ),
                new LayoutGroup( array(
                    new LayoutRow( new LayoutColumn(
                        new Warning( 'Keine Daten vorhanden' )
                        .new PullRight( new \SPHERE\Common\Frontend\Link\Repository\Warning( 'Hinzufügen', '/',
                            new Plus() ) )
                    ) ),
                ), new Title( new TagList().' Beziehungen', 'zu Personen' ) ),
            ) )
        );
        return $Stage;
    }
}
