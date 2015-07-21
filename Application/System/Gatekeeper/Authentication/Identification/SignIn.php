<?php
namespace SPHERE\Application\System\Gatekeeper\Authentication\Identification;

use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\PasswordField;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Lock;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\Icon\Repository\YubiKey;
use SPHERE\Common\Window\Stage;

/**
 * Class SignIn
 *
 * @package SPHERE\Application\System\Gatekeeper\Authentication\Identification
 */
class SignIn
{

    /**
     * @param string $CredentialName
     * @param string $CredentialLock
     * @param string $CredentialKey
     *
     * @return Stage
     */
    public static function stageTeacher( $CredentialName, $CredentialLock, $CredentialKey )
    {

        $View = new Stage( 'Anmeldung', 'Lehrer' );
        $View->setMessage( 'Bitte geben Sie Ihre Benutzerdaten ein' );
        $View->setContent( Gatekeeper::serviceAccount()->executeActionSignInWithToken(
            new Form(
                new FormGroup( array(
                        new FormRow(
                            new FormColumn( new TextField( 'CredentialName', 'Benutzername', '', new Person() ) )
                        ),
                        new FormRow(
                            new FormColumn( new PasswordField( 'CredentialLock', 'Passwort', '', new Lock() ) )
                        ),
                        new FormRow(
                            new FormColumn( new PasswordField( 'CredentialKey', 'YubiKey', '', new YubiKey() ) )
                        )
                    )
                ), new Primary( 'Anmelden' )
            ),
            $CredentialName, $CredentialLock, $CredentialKey,
            Gatekeeper::serviceAccount()->entityAccountTypeByName( 'Lehrer' )
        ) );
        return $View;
    }

    /**
     * @param string $CredentialName
     * @param string $CredentialLock
     * @param string $CredentialKey
     *
     * @return Stage
     */
    public static function stageSystem( $CredentialName, $CredentialLock, $CredentialKey )
    {

        $View = new Stage();
        $View->setTitle( 'Anmeldung' );
        $View->setDescription( 'System' );
        $View->setMessage( 'Bitte geben Sie Ihre Benutzerdaten ein' );
        $View->setContent( Gatekeeper::serviceAccount()->executeActionSignInWithToken(
            new Form(
                new FormGroup( array(
                        new FormRow(
                            new FormColumn( new TextField( 'CredentialName', 'Benutzername', '', new Person() ) )
                        ),
                        new FormRow(
                            new FormColumn( new PasswordField( 'CredentialLock', 'Passwort', '', new Lock() ) )
                        ),
                        new FormRow(
                            new FormColumn( new PasswordField( 'CredentialKey', 'YubiKey', '', new YubiKey() ) )
                        )
                    )
                ), new Primary( 'Anmelden' )
            ),
            $CredentialName, $CredentialLock, $CredentialKey,
            Gatekeeper::serviceAccount()->entityAccountTypeByName( 'System' )
        ) );
        return $View;
    }

    /**
     * @param string $CredentialName
     * @param string $CredentialLock
     *
     * @return Stage
     */
    public static function stageStudent( $CredentialName, $CredentialLock )
    {

        $View = new Stage();
        $View->setTitle( 'Anmeldung' );
        $View->setDescription( 'Schüler' );
        $View->setMessage( 'Bitte geben Sie Ihre Benutzerdaten ein' );
        $View->setContent(
            Gatekeeper::serviceAccount()->executeActionSignIn(

                new Form(
                    new FormGroup( array(
                            new FormRow(
                                new FormColumn( new TextField( 'CredentialName', 'Benutzername', 'Benutzername',
                                    new Person() ) )
                            ),
                            new FormRow(
                                new FormColumn( new PasswordField( 'CredentialLock', 'Passwort', 'Passwort',
                                    new Lock() ) )
                            )
                        )
                    ), new Primary( 'Anmelden' )
                ),

                $CredentialName, $CredentialLock,

                Gatekeeper::serviceAccount()->entityAccountTypeByName( 'Schüler' )

            )

        );
        return $View;
    }

    /**
     * @param string $CredentialName
     * @param string $CredentialLock
     * @param string $CredentialKey
     *
     * @return Stage
     */
    public static function stageManagement( $CredentialName, $CredentialLock, $CredentialKey )
    {

        $View = new Stage();
        $View->setTitle( 'Anmeldung' );
        $View->setDescription( 'Verwaltung' );
        $View->setMessage( 'Bitte geben Sie Ihre Benutzerdaten ein' );
        $View->setContent( Gatekeeper::serviceAccount()->executeActionSignInWithToken(
            new Form(
                new FormGroup( array(
                        new FormRow(
                            new FormColumn( new TextField( 'CredentialName', 'Benutzername', '', new Person() ) )
                        ),
                        new FormRow(
                            new FormColumn( new PasswordField( 'CredentialLock', 'Passwort', '', new Lock() ) )
                        ),
                        new FormRow(
                            new FormColumn( new PasswordField( 'CredentialKey', 'YubiKey', '', new YubiKey() ) )
                        )
                    )
                ), new Primary( 'Anmelden' )
            ),
            $CredentialName, $CredentialLock, $CredentialKey,
            Gatekeeper::serviceAccount()->entityAccountTypeByName( 'Verwaltung' )
        ) );
        return $View;
    }
}
