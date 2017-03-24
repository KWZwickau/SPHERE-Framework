<?php
namespace SPHERE\Application\Api\Setting\UserAccount;

use SPHERE\Application\Api\Response;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Setting\Authorization\Account\Account as AccountAuthorization;
use SPHERE\Application\Setting\User\Account\Account as AccountUser;
use SPHERE\Common\Frontend\Icon\Repository\HazardSign;
use SPHERE\Common\Frontend\Icon\Repository\Success;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;

/**
 * Class SerialLetter
 * @package SPHERE\Application\Api\Setting\UserAccount
 */
class UserAccount implements IModuleInterface
{
    public static function registerModule()
    {
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Exchange', __NAMESPACE__.'\UserAccount::executeUserAccount'
        ));
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

    /**
     * @param null $Direction
     * @param null $Data
     *
     * @return Response
     */
    public function executeUserAccount($Direction = null, $Data = null) // $Additional = null
    {

        if ($Data && $Direction) {
            if (!isset($Data['Id']) && !isset($Data['PersonId'])) {
                return ( new Response() )->addError('Fehler!',
                    new HazardSign().' Die Zuweisung der Person konnte nicht aktualisiert werden.', 0);
            }
            if (isset($Data['Id'])) {
                $Id = $Data['Id'];
            }
            if (isset($Data['PersonId'])) {
                $PersonId = $Data['PersonId'];
            }

            if ($Direction['From'] == 'TableAvailable') {
                $Remove = false;
            } else {
                $Remove = true;
            }

//            return ( new Response() )->addData($Data['Id'].' - '.$Data['PersonId'] );

            if ($Remove && isset($Id)) {
                $tblUserAccount = AccountUser::useService()->getUserAccountById($Id);
                if ($tblUserAccount) {
                    $tblAccount = $tblUserAccount->getServiceTblAccount();
                    if ($tblUserAccount && $tblAccount) {
                        // remove tblAccount
                        AccountAuthorization::useService()->destroyAccount($tblAccount);

                        // remove tblUserAccount
                        AccountUser::useService()->removeUserAccount($tblUserAccount);
                        return (new Response())->addData(new Success().' Die Zuweisung der Person wurde erfolgreich aktualisiert.');
                    }
                }
            } elseif (isset($PersonId)) {
                $tblPerson = Person::useService()->getPersonById($PersonId);
                if ($tblPerson) {
                    //check existing Account
                    if (AccountAuthorization::useService()->getAccountAllByPerson($tblPerson)) {
                        return (new Response())->addError('Fehler!',
                            new HazardSign().' Person besitzt bereits einen Account.', 0);
                    }
                    // prepare information
                    $tblToPersonAddress = Address::useService()->getAddressToPersonByPerson($tblPerson);
                    if (!$tblToPersonAddress) {
                        $tblToPersonAddress = null;
                    }
                    $tblToPersonMail = null;
                    $tblMailList = Mail::useService()->getMailAllByPerson($tblPerson);
                    if ($tblMailList) {
                        $tblToPersonMail = current($tblMailList);
                    }
                    $userName = AccountUser::useService()->generateUserName($tblPerson, $tblToPersonMail);
                    $userPassword = $this->generatePassword(8, 1, 2, 2);
                    $tblToken = null;
                    $tblConsumer = Consumer::useService()->getConsumerBySession();
                    if ($userName != '' && $tblConsumer) {
                        // add Account
                        $tblAccount = AccountAuthorization::useService()->insertAccount($userName, $userPassword,
                            $tblToken, $tblConsumer);
                        if ($tblAccount) {
                            $tblIdentification = AccountAuthorization::useService()->getIdentificationByName('UserCredential');
                            AccountAuthorization::useService()->addAccountAuthentication($tblAccount,
                                $tblIdentification);
                            $tblRole = Access::useService()->getRoleByName('Einstellungen: Benutzer');
                            if ($tblRole && !$tblRole->isSecure()) {
                                AccountAuthorization::useService()->addAccountAuthorization($tblAccount, $tblRole);
                            }
                            $tblRole = Access::useService()->getRoleByName('Bildung: Zensurenübersicht (Schüler/Eltern)');
                            if ($tblRole && !$tblRole->isSecure()) {
                                AccountAuthorization::useService()->addAccountAuthorization($tblAccount, $tblRole);
                            }
                            if ($tblPerson) {
                                AccountAuthorization::useService()->addAccountPerson($tblAccount, $tblPerson);
                            }

                            // add tblUserAccount
                            if (AccountUser::useService()->createUserAccount(
                                $tblAccount,
                                $tblPerson,
                                $tblToPersonAddress,
                                $tblToPersonMail,
                                $userPassword)
                            ) {
                                return (new Response())->addData(new Success().' Die Zuweisung der Person wurde erfolgreich aktualisiert.');
                            }
                        }
                    }
                }
            }
        }
        return ( new Response() )->addError('Fehler!',
            new HazardSign().' Die Zuweisung der Person konnte nicht aktualisiert werden.', 0);
    }

    /**
     * @param int $completeLength number all filled up with (abcdefghjkmnpqrstuvwxyz)
     * @param int $specialLength number of (!$%&=?*-:;.,+_)
     * @param int $numberLength number of (123456789)
     * @param int $capitalLetter number of (ABCDEFGHJKMNPQRSTUVWXYZ)
     *
     * @return string
     */
    private function generatePassword($completeLength = 8, $specialLength = 0, $numberLength = 0, $capitalLetter = 0)
    {

        $numberChars = '123456789';
        $specialChars = '!$%&=?*-:;.,+_';
        $secureChars = 'abcdefghjkmnpqrstuvwxyz';
        $secureCapitalChars = strtoupper($secureChars);
        $return = '';

        $count = $completeLength - $specialLength - $numberLength - $capitalLetter;
        if ($count > 0) {
            // get normal characters
            $temp = str_shuffle($secureChars);
            $return = substr($temp, 0, $count);
        }
        if ($capitalLetter > 0) {
            // get special characters
            $temp = str_shuffle($secureCapitalChars);
            $return .= substr($temp, 0, $capitalLetter);
        }
        if ($specialLength > 0) {
            // get special characters
            $temp = str_shuffle($specialChars);
            $return .= substr($temp, 0, $specialLength);
        }
        if ($numberLength > 0) {
            // get numbers
            $temp = str_shuffle($numberChars);
            $return .= substr($temp, 0, $numberLength);
        }
        // Random
        $return = str_shuffle($return);

        return $return;
    }
}