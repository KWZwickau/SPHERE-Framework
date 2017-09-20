<?php

namespace SPHERE\Application\Platform\System\DataMaintenance;

use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account as AccountAuthorization;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblUser;
use SPHERE\Application\Setting\User\Account\Account;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Success as SuccessIcon;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;

/**
 * Class DataMaintenance
 * @package SPHERE\Application\Platform\System\DataMaintenance
 */
class DataMaintenance
{
    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Datenpflege'))
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__,
                __CLASS__.'::frontendUserAccount'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Destroy',
                __CLASS__.'::frontendDestroyAccount'
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
     * @param null $AccountType
     *
     * @return Stage
     */
    public function frontendUserAccount($AccountType = null)
    {

        $Stage = new Stage('Benutzeraccounts', 'Löschen');
        $Stage->addButton(new Standard('Alle Schüler', __NAMESPACE__, new EyeOpen(),
            array('AccountType' => 'STUDENT')));
        $Stage->addButton(new Standard('Alle Sorgeberechtigte', __NAMESPACE__, new EyeOpen(),
            array('AccountType' => 'CUSTODY')));

        $TableContent = array();
        if ($AccountType) {
            $tblUserAccountList = Account::useService()->getUserAccountAllByType($AccountType);
            if ($tblUserAccountList) {
                array_walk($tblUserAccountList, function (TblUserAccount $tblUserAccount) use (&$TableContent) {
                    $Item['Account'] = '';
                    $Item['User'] = '';
                    $Item['Type'] = '';

                    $tblAccount = $tblUserAccount->getServiceTblAccount();
                    if ($tblAccount) {
                        $Item['Account'] = $tblAccount->getUsername();
                        $tblUserList = AccountAuthorization::useService()->getUserAllByAccount($tblAccount);
                        /** @var TblUser $tblUser */
                        if ($tblUserList && ($tblUser = current($tblUserList))) {
                            $tblPerson = $tblUser->getServiceTblPerson();
                            if ($tblPerson) {
                                $Item['User'] = $tblPerson->getLastFirstName();
                            }
                        }
                    }
                    $Type = $tblUserAccount->getType();
                    if ($Type === 'STUDENT') {
                        $Item['Type'] = 'Schüler';
                    } elseif ($Type === 'CUSODY') {
                        $Item['Type'] = 'Sorgeberechtigte';
                    }
                    array_push($TableContent, $Item);
                });
            } else {
                $Stage->setContent(
                    new Layout(
                        new LayoutGroup(
                            new LayoutRow(
                                new LayoutColumn(
                                    new Warning('Es sind keine Accounts für den Typ: "'.$AccountType.'" vorhanden')
                                )
                            )
                        )
                    )
                );
            }
        }
        if (!empty($TableContent)) {
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new TableData($TableContent, null,
                                    array(
                                        'Account' => 'Benutzer-Account',
                                        'User'    => 'Person',
                                        'Type'    => 'Account-Typ'
                                    )
                                )
                            ),
                            new LayoutColumn(
                                new DangerLink('Löschen', __NAMESPACE__.'/Destroy', new Remove(),
                                    array('AccountType' => $AccountType))
                            )
                        ))
                    )
                )
            );
        }

        return $Stage;
    }

    /**
     * @param string $AccountType
     * @param bool   $Confirm
     *
     * @return Stage
     */
    public function frontendDestroyAccount($AccountType, $Confirm = false)
    {

        $Stage = new Stage('Benutzeraccounts', 'Löschen');

        if (($tblUserAccountList = Account::useService()->getUserAccountAllByType($AccountType))) {
            $Stage->addButton(new Standard(
                'Zurück', __NAMESPACE__, new ChevronLeft()
            ));

            if (!$Confirm) {
                $Type = 'Unbekannt';
                if ($AccountType == 'STUDENT') {
                    $Type = 'Schüler';
                } elseif ($AccountType == 'CUSTODY') {
                    $Type = 'Sorgeberechtigte';
                }
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(
                            new Question().' Löschabfrage',
                            'Sollen die Accounts mit dem Typ "'.new Bold($Type).'" wirklich gelöscht werden? (Anzahl: '.count($tblUserAccountList).')',
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', __NAMESPACE__.'/Destroy', new Ok(),
                                array(
                                    'AccountType' => $AccountType,
                                    'Confirm'     => true
                                )
                            )
                            .new Standard(
                                'Nein', __NAMESPACE__, new Disable()
                            )
                        ),
                    )))))
                );
            } else {

                $AccountList = array();
                $UserAccountList = array();
                //Service delete complete Account

                foreach ($tblUserAccountList as $tblUserAccount) {
                    if ($tblUserAccount) {
                        $tblAccount = $tblUserAccount->getServiceTblAccount();
                        if ($tblAccount) {
                            // remove tblAccount
                            if (!AccountAuthorization::useService()->destroyAccount($tblAccount)) {
                                $AccountList[] = $tblAccount;
                            }
                        }
                        // remove tblUserAccount
                        if (!Account::useService()->removeUserAccount($tblUserAccount)) {
                            $UserAccountList[] = $tblUserAccount;
                        }
                    }
                }


                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            (empty($AccountList) && empty($UserAccountList)
                                ? new Success(new SuccessIcon().' Die Accounts wurden erfolgreich gelöscht')
                                .new Redirect('/Platform/System/DataMaintenance', Redirect::TIMEOUT_SUCCESS,
                                    array('AccountType' => $AccountType))
                                : new Danger(new Remove().' Die Angezeigten Accounts konnten nicht gelöscht werden.')
                                .new Layout(new LayoutGroup(new LayoutRow(array(
                                    new LayoutColumn(
                                        new TableData($AccountList, new Title('Account'), array(
                                            'Id'                 => 'Id',
                                            'Username'           => 'Benutzer',
                                            'serviceTblConsumer' => 'Consumer Id',
                                        ))
                                        , 6),
                                    new LayoutColumn(
                                        new TableData($UserAccountList, new Title('UserAccount')
                                            , array(
                                                'Id'                => 'Id',
                                                'serviceTblAccount' => 'Account Id',
                                                'EntityCreate'      => 'Erstellungsdatum',
                                                'EntityUpdate'      => 'Letztes Update',
                                            ))
                                        , 6),
                                ))))
                            )
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger(new Ban().' Es konnten keine Accounts gefunden werden'),
                        new Redirect('/Platform/System/DataMaintenance', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }
        return $Stage;
    }
}