<?php
namespace SPHERE\Application\Setting\MyAccount;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;

/**
 * Class Service
 *
 * @package SPHERE\Application\Setting\MyAccount
 */
class Service extends \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service
{

    /**
     * @param IFormInterface $Form
     * @param TblAccount     $tblAccount
     * @param string         $CredentialLock
     * @param string         $CredentialLockSafety
     *
     * @return IFormInterface|Redirect
     */
    public function updatePassword(
        IFormInterface &$Form,
        TblAccount $tblAccount,
        $CredentialLock,
        $CredentialLockSafety
    ) {

        if (null === $CredentialLock
            && null === $CredentialLockSafety
        ) {
            return $Form;
        }

        $Error = false;

        if (empty( $CredentialLock )) {
            $Form->setError('CredentialLock', 'Bitte geben Sie ein Passwort an');
            $Error = true;
        } else {
            if (strlen($CredentialLock) >= 8) {
                $Form->setSuccess('CredentialLock', '');
            } else {
                $Form->setError('CredentialLock', 'Das Passwort muss mindestens 8 Zeichen lang sein');
                $Error = true;
            }
        }

        if (empty( $CredentialLockSafety )) {
            $Form->setError('CredentialLockSafety', 'Bitte geben Sie ein Passwort an');
            $Error = true;
        }
        if ($CredentialLock != $CredentialLockSafety) {
            $Form->setError('CredentialLock', '');
            $Form->setError('CredentialLockSafety', 'Die beiden Passworte stimmen nicht überein');
            $Error = true;
        } else {
            if (!empty( $CredentialLock ) && !empty( $CredentialLockSafety )) {
                $Form->setSuccess('CredentialLockSafety', '');
            } else {
                $Form->setError('CredentialLockSafety', '');
            }
        }

        if ($Error) {
            return $Form;
        } else {
            if (Account::useService()->changePassword($CredentialLock, $tblAccount)) {
                return new Success('Das Passwort wurde erfolgreich geändert').new Redirect('/Setting/MyAccount', 1);
            } else {
                return new Danger('Das Passwort konnte nicht geändert werden').new Redirect('/Setting/MyAccount', 10);
            }
        }
    }

    /**
     * @param IFormInterface $Form
     * @param TblAccount     $tblAccount
     * @param int            $Consumer
     *
     * @return IFormInterface|Redirect
     */
    public function updateConsumer(
        IFormInterface &$Form,
        TblAccount $tblAccount,
        $Consumer
    ) {

        if (null === $Consumer) {
            return $Form;
        }

        $Error = false;

        if (empty( $Consumer )) {
            $Form->setError('Consumer', 'Bitte wählen Sie einen Mandanten aus');
            $Error = true;
        }

        if ($Error) {
            return $Form;
        } else {
            $tblConsumer = Consumer::useService()->getConsumerById($Consumer);

            if (Account::useService()->changeConsumer($tblConsumer, $tblAccount)) {
                return new Success('Der Mandant wurde erfolgreich geändert').new Redirect('/Setting/MyAccount', 1);
            } else {
                return new Danger('Der Mandant konnte nicht geändert werden').new Redirect('/Setting/MyAccount', 10);
            }
        }
    }

    /**
     * @param IFormInterface $Form
     * @param TblAccount     $tblAccount
     * @param array          $Setting
     *
     * @return IFormInterface|Redirect
     */
    public function updateSetting(
        IFormInterface &$Form,
        TblAccount $tblAccount,
        $Setting
    ) {

        if (empty( $Setting )) {
            return $Form;
        }

        $Error = false;

        foreach ((array)$Setting as $Identifier => $Value) {
            if (!$this->setSettingByAccount($tblAccount, $Identifier, $Value)) {
                $Error = true;
            }
        }

        if ($Error) {
            return new Danger('Einige Einstellungen konnten nicht gespeichert werden').new Redirect('/Setting/MyAccount',
                10);
        } else {
            return new Success('Die Einstellungen wurden erfolgreich gespeichert').new Redirect('/Setting/MyAccount',
                1);
        }
    }

}
