<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authentication;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
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
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Link\Repository\Danger;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\System\Gatekeeper\Authentication
 */
class Frontend implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendWelcome()
    {

        $Stage = new Stage('Willkommen', 'KREDA Professional');
        $Stage->setMessage(date('d.m.Y - H:i:s'));
        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendIdentification()
    {

        $View = new Stage('Anmeldung', 'Bitte wählen Sie den Typ der Anmeldung');
        $View->setMessage('Anmeldend als:');
        $View->setContent(
            (new Standard('Schüler / Eltern',
                '/Platform/Gatekeeper/Authentication/Student', new Lock()
            ))->setDisabled()
            .new \SPHERE\Common\Frontend\Link\Repository\Primary('Personal',
                '/Platform/Gatekeeper/Authentication/Staff', new YubiKey()
            )
            .new Danger('System',
                '/Platform/Gatekeeper/Authentication/System', new YubiKey()
            )
        );
        return $View;
    }

//    /**
//     * @param string $CredentialName
//     * @param string $CredentialLock
//     * @param string $CredentialKey
//     *
//     * @return Stage
//     */
//    public function frontendCreateSessionTeacher($CredentialName, $CredentialLock, $CredentialKey)
//    {
//
//        $View = new Stage('Anmeldung', 'Lehrer');
//        $View->setMessage('Bitte geben Sie Ihre Benutzerdaten ein');
//        $View->setContent(Account::useService()->createSessionCredentialToken(
//            new Form(
//                new FormGroup(array(
//                        new FormRow(
//                            new FormColumn(new TextField('CredentialName', 'Benutzername', 'Benutzername',
//                                new Person()))
//                        ),
//                        new FormRow(
//                            new FormColumn(new PasswordField('CredentialLock', 'Passwort', 'Passwort', new Lock()))
//                        ),
//                        new FormRow(
//                            new FormColumn(new PasswordField('CredentialKey', 'YubiKey', 'YubiKey', new YubiKey()))
//                        )
//                    )
//                ), new Primary('Anmelden')
//            ),
//            $CredentialName, $CredentialLock, $CredentialKey,
//            Account::useService()->getIdentificationByName('Teacher')
//        ));
//        return $View;
//    }

    /**
     * @param string $CredentialName
     * @param string $CredentialLock
     * @param string $CredentialKey
     *
     * @return Stage
     */
    public function frontendCreateSessionSystem($CredentialName, $CredentialLock, $CredentialKey)
    {

        $View = new Stage('Anmeldung', 'System');
        $View->addButton(
            new External('Zur Live-Umgebung wechseln', 'http://www.kreda.schule/', null, array(), false)
        );
        $View->setMessage('Bitte geben Sie Ihre Benutzerdaten ein');
        $View->setContent(Account::useService()->createSessionCredentialToken(
            new Form(
                new FormGroup(array(
                        new FormRow(
                            new FormColumn(new TextField('CredentialName', 'Benutzername', 'Benutzername',
                                new Person()))
                        ),
                        new FormRow(
                            new FormColumn(new PasswordField('CredentialLock', 'Passwort', 'Passwort', new Lock()))
                        ),
                        new FormRow(
                            new FormColumn(new PasswordField('CredentialKey', 'YubiKey', 'YubiKey', new YubiKey()))
                        )
                    )
                ), new Primary('Anmelden')
            ),
            $CredentialName, $CredentialLock, $CredentialKey,
            Account::useService()->getIdentificationByName('System')
        ));
        return $View;
    }

//    /**
//     * @param string $CredentialName
//     * @param string $CredentialLock
//     *
//     * @return Stage
//     */
//    public function frontendCreateSessionStudent($CredentialName, $CredentialLock)
//    {
//
//        $View = new Stage('Anmeldung', 'Schüler');
//        $View->setMessage('Bitte geben Sie Ihre Benutzerdaten ein');
//        $View->setContent(Account::useService()->createSessionCredential(
//            new Form(
//                new FormGroup(array(
//                        new FormRow(
//                            new FormColumn(new TextField('CredentialName', 'Benutzername', 'Benutzername',
//                                new Person()))
//                        ),
//                        new FormRow(
//                            new FormColumn(new PasswordField('CredentialLock', 'Passwort', 'Passwort',
//                                new Lock()))
//                        )
//                    )
//                ), new Primary('Anmelden')
//            ),
//            $CredentialName, $CredentialLock,
//            Account::useService()->getIdentificationByName('Credential')
//        ));
//        return $View;
//    }

    /**
     * @param string $CredentialName
     * @param string $CredentialLock
     * @param string $CredentialKey
     *
     * @return Stage
     */
    public function frontendCreateSessionStaff($CredentialName, $CredentialLock, $CredentialKey)
    {

        $View = new Stage('Anmeldung', 'Personal');
        $View->addButton(
            new External('Zur Live-Umgebung wechseln', 'http://www.kreda.schule/', null, array(), false)
        );
        $View->setMessage('Bitte geben Sie Ihre Benutzerdaten ein');
        $View->setContent(Account::useService()->createSessionCredentialToken(
            new Form(
                new FormGroup(array(
                        new FormRow(
                            new FormColumn(new TextField('CredentialName', 'Benutzername', 'Benutzername',
                                new Person()))
                        ),
                        new FormRow(
                            new FormColumn(new PasswordField('CredentialLock', 'Passwort', 'Passwort', new Lock()))
                        ),
                        new FormRow(
                            new FormColumn(new PasswordField('CredentialKey', 'YubiKey', 'YubiKey', new YubiKey()))
                        )
                    )
                ), new Primary('Anmelden')
            ),
            $CredentialName, $CredentialLock, $CredentialKey,
            Account::useService()->getIdentificationByName('Token')
        ));
        return $View;
    }

    /**
     * @return Stage
     */
    public function frontendDestroySession()
    {

        $View = new Stage('Abmelden', 'Bitte warten...');
        $View->setContent(Account::useService()->destroySession(
            new Redirect('/Platform/Gatekeeper/Authentication', 0)
        ));
        return $View;

    }
}
