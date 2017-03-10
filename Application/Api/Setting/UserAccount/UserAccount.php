<?php
namespace SPHERE\Application\Api\Setting\UserAccount;

use SPHERE\Application\Api\Response;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Setting\User\Account\Account;
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

            if ($Remove && isset($Id)) {
                $tblUserAccount = Account::useService()->getUserAccountById($Id);
                if ($tblUserAccount) {
                    // remove tblUserAccount
                    Account::useService()->removeUserAccount($tblUserAccount);
                }
                return ( new Response() )->addData(new Success().' Die Zuweisung der Person wurde erfolgreich aktualisiert.');
            } elseif (isset($PersonId)) {
                $tblPerson = Person::useService()->getPersonById($PersonId);
                if ($tblPerson) {
//                    // added tblUserAccount
                    $tblToPersonAddress = Address::useService()->getAddressToPersonByPerson($tblPerson);
                    if (!$tblToPersonAddress) {
                        $tblToPersonAddress = null;
                    }
                    $tblToPersonMail = null;
                    $tblMailList = Mail::useService()->getMailAllByPerson($tblPerson);
                    if ($tblMailList) {
                        $tblToPersonMail = current($tblMailList);
                    }

                    Account::useService()->createUserAccount($tblPerson, $tblToPersonAddress, $tblToPersonMail);
                }
                return ( new Response() )->addData(new Success().' Die Zuweisung der Person wurde erfolgreich aktualisiert.');
            }
            return ( new Response() )->addError('Fehler!',
                new HazardSign().' Die Zuweisung der Person konnte nicht aktualisiert werden.', 0);
        }
        return ( new Response() )->addError('Fehler!',
            new HazardSign().' Die Zuweisung der Person konnte nicht aktualisiert werden.', 0);
    }
}