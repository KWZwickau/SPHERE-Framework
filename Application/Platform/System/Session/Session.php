<?php
namespace SPHERE\Application\Platform\System\Session;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblSession;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Platform\System\Protocol\Service\Entity\TblProtocol;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Family;
use SPHERE\Common\Frontend\Icon\Repository\Key;
use SPHERE\Common\Frontend\Icon\Repository\Off;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\Icon\Repository\PhoneMobil;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Session
 *
 * @package SPHERE\Application\Platform\System\Session
 */
class Session extends Extension implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Aktive Sessions'))
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__,
                __CLASS__ . '::frontendSession'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/History',
                __CLASS__ . '::frontendSessionHistory'
            )
        );
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {

    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {

    }

    /**
     * @param null $Id
     *
     * @return Stage
     */
    public function frontendSession($Id = null)
    {

        $Stage = new Stage('Active Session', 'der aktuell angemeldete Benutzer');
//        $Stage->addButton(new Standard('Login History', __NAMESPACE__.'/History'));

        if ($Id) {
            $tblSessionAll = Account::useService()->getSessionAll();
            if ($tblSessionAll) {
                foreach ($tblSessionAll as $tblSession) {
                    if ($tblSession->getId() == $Id) {
                        Account::useService()->destroySession(null, $tblSession->getSession());
                    }
                }
            }
        }
        $Result = array();

        $tblSessionAll = Account::useService()->getSessionAll();
        $countArray = array(
            'tokenLogin' => 0,
            'appLogin' => 0,
            'combinedLogin' => 0,
            'pwOnly' => 0,
            'studentCustody' => 0,
        );
        if ($tblSessionAll) {
            array_walk($tblSessionAll, function (TblSession $tblSession) use (&$Result, &$countArray) {
                if (($tblAccount = $tblSession->getTblAccount())) {
                    $loginTime = $tblAccount->getSessionTimeOut();

                $Activity = gmdate("H:i:s", $loginTime - ($tblSession->getTimeout() - time()));
                // create sort string with 5 digits -> last activity with leading 0
                $lastActivity = str_pad($loginTime - ($tblSession->getTimeout() - time()), 5, 0, STR_PAD_LEFT);

                    if ($tblSession->getEntityUpdate() && $tblSession->getEntityCreate()) {
                        $Interval = $tblSession->getEntityUpdate()->getTimestamp() - $tblSession->getEntityCreate()->getTimestamp();
                    } else {
                        if (!$tblSession->getEntityUpdate() && $tblSession->getEntityCreate()) {
                            $Interval = time() - $tblSession->getEntityCreate()->getTimestamp();
                        } else {
                            $Interval = 0;
                        }
                    }

                // need to much time and info is not necessary
//                if (($Activity = Protocol::useService()->getProtocolLastActivity($tblAccount))) {
//                    $Activity = current($Activity)->getEntityCreate();
//                } else {
//                    $Activity = '-NA-';
//                }

                    $UserName = new Info($tblAccount->getUsername());
                    if (Account::useService()->getHasAuthenticationByAccountAndIdentificationName($tblAccount, TblIdentification::NAME_SYSTEM)) {
                        $AccountType = new ToolTip('A <span hidden>'.$lastActivity.'</span>' . new Key(), 'Admin');
                    } elseif (Account::useService()->getHasAuthenticationByAccountAndIdentificationName($tblAccount, TblIdentification::NAME_TOKEN)
                        || Account::useService()->getHasAuthenticationByAccountAndIdentificationName($tblAccount, TblIdentification::NAME_AUTHENTICATOR_APP)
                    ) {
                        $UserNamePrepare = $tblAccount->getUsername();
                        $separatorStringPos = strpos($UserNamePrepare, '-');
                        $UserNameBuild = new Success(substr($UserNamePrepare, 0, $separatorStringPos));
                        $UserNameBuild .= substr($UserNamePrepare, $separatorStringPos);
                        $UserName = new Bold($UserNameBuild);
                        $AccountType = new ToolTip('M <span hidden>'.$lastActivity.'</span>'
                            . (Account::useService()->getHasAuthenticationByAccountAndIdentificationName($tblAccount, TblIdentification::NAME_TOKEN) ? new Key() : '')
                            . (Account::useService()->getHasAuthenticationByAccountAndIdentificationName($tblAccount, TblIdentification::NAME_AUTHENTICATOR_APP) ? new PhoneMobil() : ''),
                            'Mitarbeiter'
                        );
                    } elseif (Account::useService()->getHasAuthenticationByAccountAndIdentificationName($tblAccount, TblIdentification::NAME_CREDENTIAL)) {
                        $AccountType = new ToolTip('M PW <span hidden>'.$lastActivity.'</span>&nbsp;' . new Person(), 'Mitarbeiter PW Login');
                    } elseif (Account::useService()->getHasAuthenticationByAccountAndIdentificationName($tblAccount, TblIdentification::NAME_USER_CREDENTIAL)) {
                        $AccountType = new ToolTip('S <span hidden>'.$lastActivity.'</span>&nbsp;' . new Family(), 'Sorgeberechtigte / Schüler');
                    } else {
                        $AccountType = '-NA-';
                    }

                    $Result[] = array(
                        'Id' => $tblSession->getId(),
                        'Consumer' => ($tblAccount->getServiceTblConsumer() ?
                            $tblAccount->getServiceTblConsumer()->getAcronym()
                            . '&nbsp;' . new Muted($tblAccount->getServiceTblConsumer()->getName())
                            : '-NA-'
                        ),
                        'Account' => $UserName,
                        'AccountType' => $AccountType,
                        'TTL' => gmdate("H:i:s", $tblSession->getTimeout() - time()),
                        'ActiveTime' => gmdate('H:i:s', $Interval),
                        'LoginTime' => $tblSession->getEntityCreate(),
                        'LastAction' => $Activity,
                        'Identifier' => strtoupper($tblSession->getSession()),
                        'Option' => new Danger('', new Link\Route(__NAMESPACE__), new Off(), array(
                            'Id' => $tblSession->getId()
                        ))
                    );
                    if($tblAccount->getServiceTblToken() && $tblAccount->getAuthenticatorAppSecret()){
                        $countArray['combinedLogin']++;
                    } elseif ($tblAccount->getServiceTblToken()) {
                        $countArray['tokenLogin']++;
                    } elseif ($tblAccount->getAuthenticatorAppSecret()) {
                        $countArray['appLogin']++;
                    } elseif($tblAccount->getHasAuthentication(TblIdentification::NAME_CREDENTIAL)) {
                        $countArray['pwOnly']++;
                    } else {
                        $countArray['studentCustody']++;
                    }
                }
            });
        }

        $space = '&nbsp;&nbsp;&nbsp;&nbsp;';
        $Stage->setDescription(
            'der aktuell angemeldete Benutzer'.$space
            .new Bold('Token und App: ').$countArray['combinedLogin'].$space
            .new Bold('Token: ').$countArray['tokenLogin'].$space
            .new Bold('App: ').$countArray['appLogin'].$space
            .new Bold('PW Only: ').$countArray['pwOnly'].$space
            .new Bold('Gesamt: ').$countArray['combinedLogin']+$countArray['tokenLogin']+$countArray['appLogin']+$countArray['pwOnly'].$space
            .new Bold('Schüler/Eltern: ').$countArray['studentCustody']
        );

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(new Standard('Login History', __NAMESPACE__.'/History').'<div style="height: 8px">&nbsp;</div>'),
                        new LayoutColumn(array(
                            new TableData($Result, null, array(
                                'Id' => '#',
                                'Consumer' => 'Mandant',
                                'Account' => 'Benutzer',
                                'AccountType' => 'Typ',
                                'LoginTime' => 'Anmeldung',
                                'ActiveTime' => 'Dauer',
                                'LastAction' => 'letzte Aktivität',
                                'TTL' => 'Timeout',
                                'Identifier' => 'Session',
                                'Option' => ''
                            ), array(
                                'order' => array(
                                    array(3, 'asc')
                                ),
                                'columnDefs' => array(
                                    array('width' => '1%', 'orderable' => false, 'targets' => -1),
                                    array('type' => 'de_date', 'targets' => 4, 5, 6, 7)
                                )
                            ), true),
                            new Redirect(
                                '/Platform/System/Session', 60
                            )
                        ))
                    )), new Title('Aktive Benutzer')
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendSessionHistory()
    {
        $Stage = new Stage('Session History', 'der letzten 250 Benutzer');
        $Stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));
        $History = array();

        $tblProtocolAll = Protocol::useService()->getProtocolAllCreateSession();
        if ($tblProtocolAll) {
            array_walk($tblProtocolAll, function (TblProtocol $tblProtocol) use (&$History) {

                array_push($History, array(
                    'Consumer' => $tblProtocol->getConsumerAcronym() . '&nbsp;' . new Muted($tblProtocol->getConsumerName()),
                    'LoginTime' => $tblProtocol->getEntityCreate(),
                    'Account' => $tblProtocol->getAccountUsername(),
                    'AccountId' => ($tblProtocol->getServiceTblAccount() ? $tblProtocol->getServiceTblAccount()->getId() : '-NA-')
                ));

            });
        }
        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new TableData($History, null, array(
                                'LoginTime' => 'Zeitpunkt',
                                'AccountId' => 'Account',
                                'Account' => 'Benutzer',
                                'Consumer' => 'Mandant',
                            ), array(
                                'order' => array(array(0, 'desc')),
                                'columnDefs' => array(
                                    array('type' => 'de_datetime', 'width' => '20%', 'targets' => 0),
                                    array('width' => '35%', 'targets' => array(2, 3))
                                )
                            )),
                        ))
                    ), new Title('Protokoll der Anmeldungen')
                )
            )
        );
        return $Stage;
    }
}
