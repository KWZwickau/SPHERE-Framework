<?php
namespace SPHERE\Application\Setting\Authorization\Account;

use SPHERE\Common\Frontend\Form\IFormInterface;

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

        if (isset( $Account['Name'] ) && empty( $Account['Name'] )) {
            $Form->setError('Account[Name]', 'Bitte geben Sie einen gültigen Benutzernamen ein');
        }
        if (isset( $Account['Password'] ) && empty( $Account['Password'] )) {
            $Form->setError('Account[Password]', 'Bitte geben Sie ein gültiges Passwort ein');
        }

        return $Form;
    }
}
