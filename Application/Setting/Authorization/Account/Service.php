<?php
namespace SPHERE\Application\Setting\Authorization\Account;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Token;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class Service
 *
 * @package SPHERE\Application\Setting\Authorization\Account
 */
class Service extends \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service
{

    public function createAccount(IFormInterface $Form, $Account)
    {

        if (null === $Account) {

            return $Form;
        }

        $Error = false;

        $Username = trim($Account['Name']);
        $Password = trim($Account['Password']);
        $PasswordSafety = trim($Account['PasswordSafety']);

        $tblConsumer = Consumer::useService()->getConsumerBySession();
        $tblToken = Token::useService()->getTokenById((int)$Account['Token']);

        if (empty($Username)) {
            $Form->setError('Account[Name]', 'Bitte geben Sie einen Benutzernamen an');
            $Error = true;
        } else {
            if (preg_match('!^[a-z0-9]{5,}$!is', $Username)) {
                $Username = $tblConsumer->getAcronym() . '-' . $Username;
                if (!Account::useService()->getAccountByUsername($Username)) {
                    $Form->setSuccess('Account[Name]', '');
                } else {
                    $Form->setError('Account[Name]', 'Der angegebene Benutzername ist bereits vergeben');
                    $Error = true;
                }
            } else {
                $Form->setError('Account[Name]', 'Der Benutzername darf nur Buchstaben und Zahlen enthalten');
                $Error = true;
            }
        }

        if (empty($Password)) {
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

        if (empty($PasswordSafety)) {
            $Form->setError('Account[PasswordSafety]', 'Bitte geben Sie ein Passwort an');
            $Error = true;
        }
        if ($Password != $PasswordSafety) {
            $Form->setError('Account[Password]', '');
            $Form->setError('Account[PasswordSafety]', 'Die beiden Passworte stimmen nicht überein');
            $Error = true;
        } else {
            if (!empty($Password) && !empty($PasswordSafety)) {
                $Form->setSuccess('Account[PasswordSafety]', '');
            } else {
                $Form->setError('Account[PasswordSafety]', '');
            }
        }

        if (!$Error) {
            Account::useService()->insertAccount($Username, $Password, $tblToken, $tblConsumer);
        } else {
            Debugger::screenDump($Username, $Password, $tblToken, $tblConsumer, $Account);
        }

        return $Form;
    }
}
