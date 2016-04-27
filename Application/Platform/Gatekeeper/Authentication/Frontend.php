<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authentication;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Token;
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
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Backward;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\System\Gatekeeper\Authentication
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendWelcome()
    {

        $Stage = new Stage('Willkommen', 'KREDA Professional');
        $Stage->addButton(new Backward(true));
        $Stage->setMessage(date('d.m.Y - H:i:s'));

        $Stage->setContent($this->getCleanLocalStorage());

        return $Stage;
    }

    private function getCleanLocalStorage()
    {

        return '<script language=javascript>
            //noinspection JSUnresolvedFunction
            executeScript(function()
            {
                Client.Use("ModCleanStorage", function()
                {
                    jQuery().ModCleanStorage();
                });
            });
        </script>';
    }

    /**
     * @param string $CredentialName
     * @param string $CredentialLock
     * @param string $CredentialKey
     *
     * @return Stage
     */
    public function frontendIdentification($CredentialName = null, $CredentialLock = null, $CredentialKey = null)
    {

        $View = new Stage('Anmeldung');

        // Prepare Environment
        switch (strtolower($this->getRequest()->getHost())) {
            case 'www.kreda.schule':
                $Environment = new External('Zur Demo-Umgebung wechseln', 'http://demo.kreda.schule/', null, array(),
                    false);
                break;
            case 'demo.kreda.schule':
                $Environment = new External('Zur Live-Umgebung wechseln', 'http://www.kreda.schule/', null, array(),
                    false);
                break;
            default:
                $Environment = new External('Zur Demo-Umgebung wechseln', 'http://demo.kreda.schule/', null, array(),
                    false);
        }

        $View->addButton(
            $Environment
        );

        $View->setMessage('');

        // Get Identification-Type (Credential,Token,System)
        $Identifier = $this->getModHex($CredentialKey)->getIdentifier();
        $tblToken = Token::useService()->getTokenByIdentifier($Identifier);
        if ($tblToken) {
            if ($tblToken->getServiceTblConsumer()) {
                $Identification = Account::useService()->getIdentificationByName('Token');
            } else {
                $Identification = Account::useService()->getIdentificationByName('System');
            }
        } else {
            $Identification = Account::useService()->getIdentificationByName('Credential');
        }

        // Create Form
        $Form = new Form(
            new FormGroup(array(
                    new FormRow(
                        new FormColumn(
                            new Panel('Benutzername & Passwort', array(
                                new TextField('CredentialName', 'Benutzername', 'Benutzername',
                                    new Person()),
                                new PasswordField('CredentialLock', 'Passwort', 'Passwort', new Lock())
                            ), Panel::PANEL_TYPE_INFO)
                        )
                    ),
                    new FormRow(array(
                        new FormColumn(
                            new Panel('Hardware-Schlüssel *', array(
                                new PasswordField('CredentialKey', 'Yubi-Key', 'Yubi-Key', new YubiKey())
                            ), Panel::PANEL_TYPE_WARNING, new Small('* Optional'))
                        )
                    ))
                )
            ), new Primary('Anmelden')
        );

        // Switch Service
        if ($tblToken) {
            $FormService = Account::useService()->createSessionCredentialToken(
                $Form, $CredentialName, $CredentialLock, $CredentialKey, $Identification
            );
        } else {
            $FormService = Account::useService()->createSessionCredential(
                $Form, $CredentialName, $CredentialLock, $Identification
            );
        }

        $View->setContent(
            new Well(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            ''
                            , 2),
                        new LayoutColumn(
                            $FormService
                            , 8),
                        new LayoutColumn(
                            ''
                            , 2),
                    )),
                ), new Title('Anmelden', 'Bitte geben Sie Ihre Benutzerdaten ein')))
            )
        );
        return $View;
    }

    /**
     * @return Stage
     */
    public function frontendDestroySession()
    {

        $View = new Stage('Abmelden', 'Bitte warten...');
        $View->setContent(Account::useService()->destroySession(
                new Redirect('/Platform/Gatekeeper/Authentication', 5)
            ).$this->getCleanLocalStorage());
        return $View;
    }
}
