<?php
namespace SPHERE\Application\Contact\Mail;

use SPHERE\Application\Contact\Mail\Service\Entity\TblToPerson;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Mail as MailIcon;
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
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Contact\Mail
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param int    $tblPerson
     * @param string $Address
     * @param array  $Type
     *
     * @return Stage
     */
    public function frontendCreateToPerson( $tblPerson, $Address, $Type )
    {

        $Stage = new Stage( 'E-Mail Adresse', 'Hinzufügen' );
        $Stage->setMessage( 'Eine E-Mail Adresse zur gewählten Person hinzufügen' );

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
                            Mail::useService()->createMailToPerson(
                                $this->formAddress()
                                    ->appendFormButton( new Primary( 'E-Mail Adresse hinzufügen' ) )
                                    ->setConfirm( 'Eventuelle Änderungen wurden noch nicht gespeichert' )
                                , $tblPerson, $Address, $Type
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

        $tblMailAll = Mail::useService()->getMailAll();
        $tblTypeAll = Mail::useService()->getTypeAll();

        return new Form(
            new FormGroup( array(
                new FormRow( array(
                    new FormColumn(
                        new Panel( 'E-Mail Adresse',
                            array(
                                new SelectBox( 'Type[Type]', 'Typ',
                                    array( 'Name' => $tblTypeAll ), new TileBig()
                                ),
                                new AutoCompleter( 'Address', 'E-Mail Adresse', 'E-Mail Adresse',
                                    array( 'Address' => $tblMailAll ), new MailIcon()
                                )
                            ), Panel::PANEL_TYPE_INFO
                        ), 6 ),
                    new FormColumn(
                        new Panel( 'Sonstiges',
                            new TextArea( 'Type[Remark]', 'Bemerkungen', 'Bemerkungen', new Pencil() )
                            , Panel::PANEL_TYPE_INFO
                        ), 6 ),
                ) ),
            ) )
        );
    }

    /**
     * @param int    $tblToPerson
     * @param string $Address
     * @param array  $Type
     *
     * @return Stage
     */
    public function frontendUpdateToPerson( $tblToPerson, $Address, $Type )
    {

        $Stage = new Stage( 'E-Mail Adresse', 'Bearbeiten' );
        $Stage->setMessage( 'Eine E-Mail Adresse zur gewählten Person ändern' );

        $tblToPerson = Mail::useService()->getMailToPersonById( $tblToPerson );

        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Address'] )) {
            $Global->POST['Address'] = $tblToPerson->getTblMail()->getAddress();
            $Global->POST['Type']['Type'] = $tblToPerson->getTblType()->getId();
            $Global->POST['Type']['Remark'] = $tblToPerson->getRemark();
            $Global->savePost();
        }

        $Stage->setContent(
            new Layout( array(
                new LayoutGroup( array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel( new PersonIcon().' Person',
                                $tblToPerson->getServiceTblPerson()->getFullName(),
                                Panel::PANEL_TYPE_SUCCESS,
                                new Standard( 'Zurück zur Person', '/People/Person', new ChevronLeft(),
                                    array( 'tblPerson' => $tblToPerson->getServiceTblPerson()->getId() )
                                )
                            )
                        )
                    ),
                ) ),
                new LayoutGroup( array(
                    new LayoutRow(
                        new LayoutColumn(
                            Mail::useService()->updateMailToPerson(
                                $this->formAddress()
                                    ->appendFormButton( new Primary( 'Änderungen speichern' ) )
                                    ->setConfirm( 'Eventuelle Änderungen wurden noch nicht gespeichert' )
                                , $tblToPerson, $Address, $Type
                            )
                        )
                    )
                ) ),
            ) )
        );

        return $Stage;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return Layout
     */
    public function frontendLayoutPerson( TblPerson $tblPerson )
    {

        $tblMailAll = Mail::useService()->getMailAllByPerson( $tblPerson );
        if ($tblMailAll !== false) {
            array_walk( $tblMailAll, function ( TblToPerson &$tblToPerson ) {

                $Panel = array( $tblToPerson->getTblMail()->getAddress() );
                if ($tblToPerson->getRemark()) {
                    array_push( $Panel, new Muted( new Small( $tblToPerson->getRemark() ) ) );
                }

                $tblToPerson = new LayoutColumn(
                    new Panel(
                        new MailIcon().' '.$tblToPerson->getTblType()->getName(), $Panel, Panel::PANEL_TYPE_INFO,

                        new Standard(
                            '', '/People/Person/Mail/Edit', new Pencil(),
                            array( 'tblToPerson' => $tblToPerson->getId() ),
                            'Bearbeiten'
                        )
                        .new Standard(
                            '', '/People/Person/Mail/Destroy', new Remove(),
                            array( 'Id' => $tblToPerson->getId() ), 'Löschen'
                        )
                    )
                    , 4 );
            } );
        } else {
            $tblMailAll = array(
                new LayoutColumn(
                    new Warning( 'Keine E-Mail Adressen hinterlegt' )
                )
            );
        }
        return new Layout( new LayoutGroup( new LayoutRow( $tblMailAll ) ) );
    }
}
