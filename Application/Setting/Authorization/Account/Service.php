<?php
namespace SPHERE\Application\Setting\Authorization\Account;

use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access as GatekeeperAccess;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account as GatekeeperAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer as GatekeeperConsumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Token as GatekeeperToken;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;

/**
 * Class Service
 *
 * @package SPHERE\Application\Setting\Authorization\Account
 */
class Service extends \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service
{

    /**
     * @param IFormInterface $Form
     * @param array          $Account
     *
     * @return IFormInterface
     */
    public function createAccount(IFormInterface $Form, $Account)
    {

        if (null === $Account) {

            return $Form;
        }

        $Error = false;

        $Username = trim($Account['Name']);
        $Password = trim($Account['Password']);
        $PasswordSafety = trim($Account['PasswordSafety']);

        $tblConsumer = GatekeeperConsumer::useService()->getConsumerBySession();
        if (!( $tblToken = GatekeeperToken::useService()->getTokenById((int)$Account['Token']) )) {
            $tblToken = null;
        }

        if (empty( $Username )) {
            $Form->setError('Account[Name]', 'Bitte geben Sie einen Benutzernamen an');
            $Error = true;
        } else {
            if (preg_match('!^[a-z0-9]{5,}$!is', $Username)) {
                $Username = $tblConsumer->getAcronym().'-'.$Username;
                if (!GatekeeperAccount::useService()->getAccountByUsername($Username)) {
                    $Form->setSuccess('Account[Name]', '');
                } else {
                    $Form->setError('Account[Name]', 'Der angegebene Benutzername ist bereits vergeben');
                    $Error = true;
                }
            } else {
                $Form->setError('Account[Name]',
                    'Der Benutzername darf nur Buchstaben und Zahlen enthalten und muss mindestens 5 Zeichen lang sein');
                $Error = true;
            }
        }

        if (empty( $Password )) {
            $Form->setError('Account[Password]', 'Bitte geben Sie ein Passwort an');
            $Error = true;
        } else {
            if (strlen($Password) >= 8) {
                $Form->setSuccess('Account[Password]', '');
            } else {
                $Form->setError('Account[Password]', 'Das Passwort muss mindestens 8 Zeichen lang sein');
                $Error = true;
            }
        }

        if (empty( $PasswordSafety )) {
            $Form->setError('Account[PasswordSafety]', 'Bitte geben Sie das Passwort erneut an');
            $Error = true;
        }
        if ($Password != $PasswordSafety) {
            $Form->setError('Account[Password]', '');
            $Form->setError('Account[PasswordSafety]', 'Die beiden Passworte stimmen nicht überein');
            $Error = true;
        } else {
            if (!empty( $Password ) && !empty( $PasswordSafety )) {
                $Form->setSuccess('Account[PasswordSafety]', '');
            } else {
                $Form->setError('Account[PasswordSafety]', '');
            }
        }

        if (!$Error) {
            $tblAccount = GatekeeperAccount::useService()->insertAccount($Username, $Password, $tblToken, $tblConsumer);
            if ($tblAccount) {
                $tblIdentification = GatekeeperAccount::useService()->getIdentificationById($Account['Identification']);
                GatekeeperAccount::useService()->addAccountAuthentication($tblAccount, $tblIdentification);
                if (isset( $Account['Role'] )) {
                    foreach ((array)$Account['Role'] as $Role) {
                        $tblRole = GatekeeperAccess::useService()->getRoleById($Role);
                        GatekeeperAccount::useService()->addAccountAuthorization($tblAccount, $tblRole);
                    }
                }
                if (isset( $Account['User'] )) {
                    $tblPerson = Person::useService()->getPersonById($Account['User']);
                    GatekeeperAccount::useService()->addAccountPerson($tblAccount, $tblPerson);
                }
                return new Success('Das Benutzerkonnto wurde erstellt')
                .new Redirect('/Setting/Authorization/Account', 3);
            } else {
                return new Danger('Das Benutzerkonnto konnte nicht erstellt werden')
                .new Redirect('/Setting/Authorization/Account', 3);
            }
        }

        return $Form;
    }
}
