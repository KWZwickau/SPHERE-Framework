<?php
namespace SPHERE\Application\Setting\MyAccount;

use DateTime;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Setting\Consumer\Consumer as ConsumerSetting;
use SPHERE\Application\Setting\User\Account\Account as UserAccount;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;

/**
 * Class Service
 *
 * @package SPHERE\Application\Setting\MyAccount
 */
class Service extends \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service
{

    /**
     * Personenbeziehungen, die ein volljähriger Schüler vom Zugriff auf seine Daten ausschließen kann
     */
    const CUSTODY_RELATIONSHIP_TYPE_LIST = array('Sorgeberechtigt', 'Bevollmächtigt', 'Vormund');

    /**
     * @param IFormInterface $Form
     * @param TblAccount     $tblAccount
     * @param string         $CredentialLock
     * @param string         $CredentialLockSafety
     *
     * @return IFormInterface|Redirect|string
     */
    public function updatePassword(
        IFormInterface $Form,
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
                $Form->setError('CredentialLockSafety', '');
                $Error = true;
            }
            $tblUserAccount = UserAccount::useService()->getUserAccountByAccount($tblAccount);
            if($tblUserAccount && $tblUserAccount->getAccountPassword() == hash('sha256', $CredentialLock)){
                $Form->setError('CredentialLock', 'Das Passwort darf nicht das Initialpasswort sein');
                $Error = true;
            }
            $tblAccountInitial = Account::useService()->getAccountInitialByAccount($tblAccount);
            if($tblAccountInitial && $tblAccountInitial->getPassword() == hash('sha256', $CredentialLock)){
                $Form->setError('CredentialLock', 'Das Passwort darf nicht das Initialpasswort sein');
                $Error = true;
            }
        }

        if (empty( $CredentialLockSafety )) {
            $Form->setError('CredentialLockSafety', 'Bitte geben Sie ein Passwort an');
            $Error = true;
        }
        if ($CredentialLock != $CredentialLockSafety && !$Error) {
            $Form->setError('CredentialLock', '');
            $Form->setError('CredentialLockSafety', 'Die beiden Passwörter stimmen nicht überein');
            $Error = true;
        } elseif (!$Error) {
            if (!empty( $CredentialLock ) && !empty( $CredentialLockSafety )) {
                $Form->setSuccess('CredentialLock', '');
                $Form->setSuccess('CredentialLockSafety', '');
            } else {
                $Form->setError('CredentialLock', '');
                $Form->setError('CredentialLockSafety', '');
            }
        }

        // are enough criteria matched?
        $Step = 0;
        if ($CredentialLock && !$Error) {
            if (preg_match('![a-z]!s', $CredentialLock)) {
                $Step++;
            }
            if (preg_match('![A-Z]!s', $CredentialLock)) {
                $Step++;
            }
            if (preg_match('![0-9]!s', $CredentialLock)) {
                $Step++;
            }
            if (preg_match('![^\w\d]!s', $CredentialLock)) {
                $Step++;
            }
            // min 3 criteria
            if ($Step < 3) {
                $Form->setError('CredentialLock', 'Nicht genügend Sicherheitskriterien erfüllt');
                $Form->setError('CredentialLockSafety', '');
                $Error = true;
            }
        }

        if ($Error) {
            return $Form;
        } else {
            $tblAccountUpdate = $tblAccount->getEntityUpdate();
            if (Account::useService()->changePassword($CredentialLock, $tblAccount)) {
                // erste PW Änderung von UserAccounts -> Weiterleitung Startseite
                if ($tblAccountUpdate === null) {
                    $tblUserAccount = UserAccount::useService()->getUserAccountByAccount($tblAccount);
                    if ($tblUserAccount) {
                        return new Success('Das Passwort wurde erfolgreich geändert').new Redirect('/',
                                Redirect::TIMEOUT_SUCCESS);
                    }
                }
                return new Success('Das Passwort wurde erfolgreich geändert').new Redirect('/Setting/MyAccount', Redirect::TIMEOUT_SUCCESS);
            } else {
                return new Danger('Das Passwort konnte nicht geändert werden').new Redirect('/Setting/MyAccount', Redirect::TIMEOUT_ERROR);
            }
        }
    }

    /**
     * @param IFormInterface $Form
     * @param int $tblAccount
     * @param string $newCredentialLock
     * @param string $newCredentialLockSafety
     * @return IFormInterface|void
     */
    public function updatePasswordInitial(
        IFormInterface $Form, $tblAccount, $newCredentialLock = null, $newCredentialLockSafety = null
    ){
        // Return on Input Error
        if (!($tblAccount = Account::useService()->getAccountById($tblAccount))) {
            return new Redirect('/', Redirect::TIMEOUT_ERROR);
        }

        if ('' == $newCredentialLock && '' == $newCredentialLockSafety
        ) {
            return $Form;
        }

        $Error = false;
        if (empty( $newCredentialLock )) {
            $Form->setError('newCredentialLock', 'Bitte geben Sie ein Passwort an');
            $Error = true;
        } else {
            if (strlen($newCredentialLock) < 8) {
                $Form->setError('newCredentialLock', 'Das Passwort muss mindestens 8 Zeichen lang sein');
                $Form->setError('newCredentialLockSafety', '');
                $Error = true;
            }
            $InitialPassword = Account::useService()->getAccountInitialPasswordByAccountWithoutLogin($tblAccount);
            if($InitialPassword && $InitialPassword == hash('sha256', $newCredentialLock)){
                $Form->setError('newCredentialLock', 'Das Passwort darf nicht das Initialpasswort sein');
                $Error = true;
            }
        }

        if (empty( $newCredentialLockSafety )) {
            $Form->setError('newCredentialLockSafety', 'Bitte wiederholen Sie das Passwort');
            $Error = true;
        }
        if ($newCredentialLock != $newCredentialLockSafety && !$Error) {
            $Form->setError('newCredentialLock', '');
            $Form->setError('newCredentialLockSafety', 'Die beiden Passwörter stimmen nicht überein');
            $Error = true;
        }

        // are enough criteria matched?
        $Step = 0;
        if ($newCredentialLock && !$Error) {
            if (preg_match('![a-z]!s', $newCredentialLock)) {
                $Step++;
            }
            if (preg_match('![A-Z]!s', $newCredentialLock)) {
                $Step++;
            }
            if (preg_match('![0-9]!s', $newCredentialLock)) {
                $Step++;
            }
            if (preg_match('![^\w\d]!s', $newCredentialLock)) {
                $Step++;
            }
            // min 3 criteria
            if ($Step < 3) {
                $Form->setError('newCredentialLock', 'Nicht genügend Sicherheitskriterien erfüllt');
                $Form->setError('newCredentialLockSafety', '');
                $Error = true;
            }
        }
        if ($Error) {

            return $Form;
        } else {
            $LoginTest = Account::useService()->getAccountBySession();
            if(!$LoginTest){
                // Password not stored as preset -> LOGIN
                Account::useService()->createSession($tblAccount);
            }
            if (Account::useService()->changePassword($newCredentialLock, $tblAccount)) {
                return new Success('Das Passwort wurde erfolgreich geändert').new Redirect('/', Redirect::TIMEOUT_SUCCESS);
            } else {
                return new Danger('Das Passwort konnte nicht geändert werden').new Redirect('/', Redirect::TIMEOUT_ERROR);
            }
        }
    }

    /**
     * @param TblAccount  $tblAccount
     * @param TblConsumer $tblConsumer
     *
     * @return string
     */
    public function updateConsumer(
        TblAccount $tblAccount,
        TblConsumer $tblConsumer
    ) {

        if (Account::useService()->changeConsumer($tblConsumer, $tblAccount)) {
            return new Success('Der Mandant wurde erfolgreich geändert').new Redirect('/Setting/MyAccount', Redirect::TIMEOUT_SUCCESS);
        } else {
            return new Danger('Der Mandant konnte nicht geändert werden').new Redirect('/Setting/MyAccount', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param IFormInterface $Form
     * @param TblAccount     $tblAccount
     * @param array          $Setting
     *
     * @return IFormInterface|Redirect|string
     */
    public function updateSetting(
        IFormInterface $Form,
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
                Redirect::TIMEOUT_ERROR);
        } else {
            return new Success('Die Einstellungen wurden erfolgreich gespeichert').new Redirect('/Setting/MyAccount',
                Redirect::TIMEOUT_SUCCESS);
        }
    }

    /**
     * Schüler-Account, dessen Person mindestens 18 Jahre alt ist
     *
     * @param TblAccount $tblAccount
     *
     * @return bool
     */
    public function isStudentOfAge(TblAccount $tblAccount): bool
    {
        if (($tblUserAccount = UserAccount::useService()->getUserAccountByAccount($tblAccount))
            && $tblUserAccount->getType() == TblUserAccount::VALUE_TYPE_STUDENT
            && ($tblPersonList = Account::useService()->getPersonAllByAccount($tblAccount))
            && ($tblPerson = current($tblPersonList))
            && ($birthday = $tblPerson->getBirthday())
        ) {
            return (new DateTime($birthday)) <= ((new DateTime('now'))->modify('-18 year'));
        }

        return false;
    }

    /**
     * Accounts der Sorgeberechtigten, Bevollmächtigten und Vormünder des Schülers
     *
     * @param TblAccount $tblAccountStudent
     *
     * @return TblAccount[]
     */
    public function getCustodyAccountListForStudent(TblAccount $tblAccountStudent): array
    {
        $tblAccountList = array();
        if (($tblPersonList = Account::useService()->getPersonAllByAccount($tblAccountStudent))
            && ($tblPersonStudent = current($tblPersonList))
            && ($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPersonStudent))
        ) {
            foreach ($tblRelationshipList as $tblRelationship) {
                if (($tblPersonCustody = $tblRelationship->getServiceTblPersonFrom())
                    && ($tblPersonTo = $tblRelationship->getServiceTblPersonTo())
                    && $tblPersonTo->getId() == $tblPersonStudent->getId()
                    && $tblPersonCustody->getId() != $tblPersonStudent->getId()
                    && in_array($tblRelationship->getTblType()->getName(), self::CUSTODY_RELATIONSHIP_TYPE_LIST)
                    && ($tblAccountCustodyList = Account::useService()->getAccountAllByPerson($tblPersonCustody))
                ) {
                    foreach ($tblAccountCustodyList as $tblAccountCustody) {
                        $tblAccountList[$tblAccountCustody->getId()] = $tblAccountCustody;
                    }
                }
            }
        }

        return $tblAccountList;
    }

    /**
     * @param TblAccount $tblAccountStudent
     * @param TblAccount $tblAccountCustody
     *
     * @return bool
     */
    public function isCustodyBlocked(TblAccount $tblAccountStudent, TblAccount $tblAccountCustody): bool
    {
        return (bool) ConsumerSetting::useService()->getStudentCustodyByStudentAndCustody($tblAccountStudent, $tblAccountCustody);
    }

    /**
     * Schüler-Accounts, die den Account des Sorgeberechtigten vom Zugriff ausgeschlossen haben
     *
     * @param TblAccount $tblAccountCustody
     *
     * @return TblAccount[]
     */
    public function getBlockedStudentAccountList(TblAccount $tblAccountCustody): array
    {
        $tblAccountList = array();
        if (($tblStudentCustodyList = ConsumerSetting::useService()->getStudentCustodyByCustody($tblAccountCustody))) {
            foreach ($tblStudentCustodyList as $tblStudentCustody) {
                if (($tblAccountStudent = $tblStudentCustody->getServiceTblAccountStudent())) {
                    $tblAccountList[$tblAccountStudent->getId()] = $tblAccountStudent;
                }
            }
        }

        return $tblAccountList;
    }

    /**
     * Ist der Zugriff auf die Daten des Schülers für den aktiven Account gesperrt
     *
     * @param TblPerson $tblPersonStudent
     *
     * @return bool
     */
    public function isPersonBlockedForCustody(TblPerson $tblPersonStudent): bool
    {
        if (!($tblAccountCustody = Account::useService()->getAccountBySession())) {
            // ohne Account keine Daten (geblockt)
            return true;
        }

        // Eintrag in der Blockliste -> geblockt
        if (($tblAccountBlockedList = $this->getBlockedStudentAccountList($tblAccountCustody))
            && ($tblAccountStudentList = Account::useService()->getAccountAllByPerson($tblPersonStudent))
        ) {
            foreach ($tblAccountStudentList as $tblAccountStudent) {
                if (isset($tblAccountBlockedList[$tblAccountStudent->getId()])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Gesperrte Schüler ans Ende der Liste verschieben, Reihenfolge und Schlüssel bleiben sonst erhalten
     *
     * @param TblPerson[] $tblPersonList
     *
     * @return TblPerson[]
     */
    public function sortBlockedPersonListToEnd(array $tblPersonList): array
    {
        $allowedList = array();
        $blockedList = array();
        foreach ($tblPersonList as $key => $tblPerson) {
            if ($this->isPersonBlockedForCustody($tblPerson)) {
                $blockedList[$key] = $tblPerson;
            } else {
                $allowedList[$key] = $tblPerson;
            }
        }

        return $allowedList + $blockedList;
    }

    /**
     * Hinweis anstelle des Inhalts, wenn der volljährige Schüler den Zugriff gesperrt hat
     *
     * @param TblPerson $tblPersonStudent
     *
     * @return Warning
     */
    public function getCustodyBlockedWarning(TblPerson $tblPersonStudent): Warning
    {
        return new Warning('Der Zugriff auf die Daten von ' . $tblPersonStudent->getLastFirstName()
            . ' wurde durch den volljährigen Schüler gesperrt');
    }

    /**
     * Ersetzt die gesperrten Sorgeberechtigten des Schülers durch die übergebene Auswahl
     *
     * @param TblAccount $tblAccountStudent
     * @param array $CustodyIds
     *
     * @return bool
     */
    public function saveBlockedCustody(TblAccount $tblAccountStudent, array $CustodyIds): bool
    {
        $isSuccess = true;
        if (($tblStudentCustodyList = ConsumerSetting::useService()->getStudentCustodyByStudent($tblAccountStudent))) {
            foreach ($tblStudentCustodyList as $tblStudentCustody) {
                if (!ConsumerSetting::useService()->removeStudentCustody($tblStudentCustody)) {
                    $isSuccess = false;
                }
            }
        }

        foreach ($CustodyIds as $CustodyId) {
            if (($tblAccountCustody = Account::useService()->getAccountById($CustodyId))
                && !ConsumerSetting::useService()->createStudentCustody($tblAccountStudent, $tblAccountCustody, $tblAccountStudent)
            ) {
                $isSuccess = false;
            }
        }

        return $isSuccess;
    }

}
