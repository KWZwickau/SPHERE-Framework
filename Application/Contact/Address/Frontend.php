<?php
namespace SPHERE\Application\Contact\Address;

use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Map;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Contact\Address
 */
class Frontend implements IFrontendInterface
{

    /**
     * @param int   $tblPerson
     * @param array $Street
     * @param array $City
     * @param int   $State
     * @param array $Type
     *
     * @return Stage
     */
    public function frontendCreateToPerson( $tblPerson, $Street, $City, $State, $Type )
    {

        $Stage = new Stage( 'Adresse', 'Hinzufügen' );
        $Stage->setMessage( 'Eine Adresse zur gewählten Person hinzufügen' );

        $tblPerson = Person::useService()->getPersonById( $tblPerson );

        $Stage->setContent(
            new Layout( array(
                new LayoutGroup( array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel( new PersonIcon().' Person',
                                $tblPerson->getFullName(),
                                Panel::PANEL_TYPE_SUCCESS,
                                new Standard( 'Zurück zur Person', '/People/Person', new ChevronLeft(),
                                    array( 'tblPerson' => $tblPerson->getId() )
                                )
                            )
                        )
                    ),
                ) ),
                new LayoutGroup( array(
                    new LayoutRow(
                        new LayoutColumn(
                            Address::useService()->createAddressToPerson(
                                $this->formAddress()
                                    ->appendFormButton( new Primary( 'Adresse hinzufügen' ) )
                                    ->setConfirm( 'Eventuelle Änderungen wurden noch nicht gespeichert' )
                                , $tblPerson, $Street, $City, $State, $Type
                            )
                        )
                    )
                ) ),
            ) )
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formAddress()
    {

        $tblAddress = Address::useService()->getAddressAll();
        $tblCity = Address::useService()->getCityAll();
        $tblState = Address::useService()->getStateAll();
        $tblType = Address::useService()->getTypeAll();

        return new Form(
            new FormGroup( array(
                new FormRow( array(
                    new FormColumn(
                        new Panel( 'Straße', array(
                            new SelectBox( 'Type[Type]', 'Typ', array( 'Name' => $tblType ), new TileBig() ),
                            new AutoCompleter( 'Street[Name]', 'Name', 'Name',
                                array( 'StreetName' => $tblAddress ), new MapMarker()
                            ),
                            new TextField( 'Street[Number]', 'Hausnummer', 'Hausnummer', new MapMarker() )
                        ), Panel::PANEL_TYPE_INFO )
                        , 4 ),
                    new FormColumn(
                        new Panel( 'Stadt', array(
                            new AutoCompleter( 'City[Code]', 'Postleitzahl', 'Postleitzahl',
                                array( 'Code' => $tblCity ), new MapMarker()
                            ),
                            new AutoCompleter( 'City[Name]', 'Ort', 'Ort',
                                array( 'Name' => $tblCity ), new MapMarker()
                            ),
                            new AutoCompleter( 'City[District]', 'Ortsteil', 'Ortsteil',
                                array( 'District' => $tblCity ), new MapMarker()
                            ),
                            new SelectBox( 'State', 'Bundesland',
                                array( 'Name' => $tblState ), new Map()
                            )
                        ), Panel::PANEL_TYPE_INFO )
                        , 4 ),
                    new FormColumn(
                        new Panel( 'Sonstiges', array(
                            new TextArea( 'Type[Remark]', 'Bemerkungen', 'Bemerkungen', new Pencil() )
                        ), Panel::PANEL_TYPE_INFO )
                        , 4 ),
                ) ),
            ) )
        );
    }

    /**
     * @param int   $tblCompany
     * @param array $Street
     * @param array $City
     * @param int   $State
     * @param array $Type
     *
     * @return Stage
     */
    public function frontendCreateToCompany( $tblCompany, $Street, $City, $State, $Type )
    {

        $Stage = new Stage( 'Adresse', 'Hinzufügen' );

        $tblCompany = Company::useService()->getCompanyById( $tblCompany );

        $Stage->setContent(
            Address::useService()->createAddressToCompany(
                $this->formAddress()
                    ->appendFormButton( new Primary( 'Adresse anlegen' ) )
                    ->setConfirm( 'Eventuelle Änderungen wurden noch nicht gespeichert' )
                , $tblCompany, $Street, $City, $State, $Type
            )
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendUpdate()
    {

        $Stage = new Stage( 'Adresse', 'Bearbeiten' );

        $Stage->setContent(
            $this->formAddress()
                ->appendFormButton( new Primary( 'Änderungen speichern' ) )
                ->setConfirm( 'Eventuelle Änderungen wurden noch nicht gespeichert' )
        );

        return $Stage;

    }

    /**
     * @return Stage
     */
    public function frontendDestroy()
    {

        $Stage = new Stage( 'Adresse', 'Löschen' );

        return $Stage;

    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return Layout
     */
    public function frontendLayoutPerson( TblPerson $tblPerson )
    {

        $tblAddressAll = Address::useService()->getAddressAllByPerson( $tblPerson );
        if ($tblAddressAll !== false) {
            array_walk( $tblAddressAll, function ( TblToPerson &$tblToPerson ) {

                $Panel = array( $tblToPerson->getTblAddress()->getLayout() );
                if ($tblToPerson->getRemark()) {
                    array_push( $Panel, new Muted( new Small( $tblToPerson->getRemark() ) ) );
                }

                $tblToPerson = new LayoutColumn(
                    new Panel(
                        new MapMarker().' '.$tblToPerson->getTblType()->getName(), $Panel, Panel::PANEL_TYPE_INFO,
                        new Standard(
                            '', '/People/Person/Address/Edit', new Pencil(),
                            array( 'tblToPerson' => $tblToPerson->getId() ),
                            'Bearbeiten'
                        )
                        .new Standard(
                            '', '/People/Person/Address/Destroy', new Remove(),
                            array( 'Id' => $tblToPerson->getId() ), 'Löschen'
                        )
                    )
                    , 4 );
            } );
        } else {
            $tblAddressAll = array(
                new LayoutColumn(
                    new Warning( 'Keine Adressen hinterlegt' )
                )
            );
        }
        return new Layout( new LayoutGroup( new LayoutRow( $tblAddressAll ) ) );
    }
}
