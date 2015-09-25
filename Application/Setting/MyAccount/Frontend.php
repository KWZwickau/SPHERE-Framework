<?php
namespace SPHERE\Application\Setting\MyAccount;

use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAuthorization;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\PasswordField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Lock;
use SPHERE\Common\Frontend\Icon\Repository\Repeat;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Setting\MyAccount
 */
class Frontend implements IFrontendInterface
{

    /**
     * @param string $CredentialLock
     * @param string $CredentialLockSafety
     *
     * @return Stage
     */
    public static function frontendChangePassword($CredentialLock = null, $CredentialLockSafety = null)
    {

        $tblAccount = Account::useService()->getAccountBySession();
        $Stage = new Stage('Mein Benutzerkonto', 'Passwort ändern');
        $Stage->setContent(
            new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                MyAccount::useService()->updatePassword(
                    new Form(
                        new FormGroup(
                            new FormRow(array(
                                new FormColumn(
                                    new Panel('Passwort', array(
                                        new PasswordField('CredentialLock', 'Neues Passwort',
                                            'Neues Passwort',
                                            new Lock()),
                                        new PasswordField('CredentialLockSafety', 'Passwort wiederholen',
                                            'Passwort wiederholen',
                                            new Repeat())
                                    ), Panel::PANEL_TYPE_INFO)
                                ),
                            ))
                        ), new Primary('Neues Passwort speichern')
                    ), $tblAccount, $CredentialLock, $CredentialLockSafety
                )
            )), new Title('Neues Passwort'))));
        return $Stage;
    }

    /**
     * @param int $Consumer
     *
     * @return Stage
     */
    public static function frontendChangeConsumer($Consumer = null)
    {

        $tblAccount = Account::useService()->getAccountBySession();

        $Stage = new Stage('Mein Benutzerkonto', 'Mandant ändern');
        $Stage->setContent(
            new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                MyAccount::useService()->updateConsumer(
                    new Form(
                        new FormGroup(
                            new FormRow(array(
                                new FormColumn(
                                    new Panel('Mandant', array(
                                        new SelectBox('Consumer', 'Neuer Mandant',
                                            array(
                                                '{{ Acronym }} {{ Name }}' => Consumer::useService()->getConsumerAll()
                                            ),
                                            new Building()),
                                    ), Panel::PANEL_TYPE_INFO)
                                ),
                            ))
                        ), new Primary('Neuen Mandant speichern')
                    ), $tblAccount, $Consumer
                )
            )), new Title('Mandant ändern'))));

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendMyAccount()
    {

        $Stage = new Stage('Mein Benutzerkonto', 'Profil');

        $tblAccount = Account::useService()->getAccountBySession();

        $tblPersonAll = Account::useService()->getPersonAllByAccount($tblAccount);
        if ($tblPersonAll) {
            array_walk($tblPersonAll, function (TblPerson &$tblPerson) {

                $tblPerson = $tblPerson->getFullName();
            });
        }

        $tblAuthorizationAll = Account::useService()->getAuthorizationAllByAccount($tblAccount);
        if ($tblAuthorizationAll) {
            array_walk($tblAuthorizationAll, function (TblAuthorization &$tblAuthorization) {

                $tblAuthorization = $tblAuthorization->getServiceTblRole()->getName();
            });
        }

        $Person = new LayoutGroup(
            new LayoutRow(
                new LayoutColumn(
                    new Panel('Informationen zur Person',
                        ( !empty( $tblPersonAll ) ? new Listing($tblPersonAll) : new Danger(new Exclamation().new Small(' Keine Person angeben')) )
                    )
                )
            )
        );

        $Authentication = new LayoutGroup(
            new LayoutRow(
                new LayoutColumn(
                    new Panel('Authentication',
                        ( $tblAccount->getServiceTblIdentification() ? $tblAccount->getServiceTblIdentification()->getDescription() : '' )
                    )
                )
            )
        );

        $Authorization = new LayoutGroup(
            new LayoutRow(
                new LayoutColumn(
                    new Panel('Berechtigungen',
                        ( !empty( $tblAuthorizationAll )
                            ? $tblAuthorizationAll
                            : array(new Danger(new Exclamation().new Small(' Keine Berechtigungen vergeben')))
                        )
                    )
                )
            )
        );

        $Token = new LayoutGroup(
            new LayoutRow(
                new LayoutColumn(
                    new Panel('Hardware-Schlüssel',
                        array(
                            $tblAccount->getServiceTblToken()
                                ? substr($tblAccount->getServiceTblToken()->getSerial(), 0,
                                    4).' '.substr($tblAccount->getServiceTblToken()->getSerial(), 4, 4)
                                : new Muted(new Small('Kein Hardware-Schlüssel vergeben'))
                        )
                    )
                )
            )
        );

        $Account = new Layout(array(
            $Person,
            $Authentication,
            $Authorization,
            $Token,
        ));

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(
                                'Benutzerkonto: '.$tblAccount->getUsername(), $Account
                                , Panel::PANEL_TYPE_DEFAULT,
                                new Standard('Passwort ändern', new Route(__NAMESPACE__.'/Password'))
                            )
                        )
                    )
                    , new Title('Benutzerkonto', 'Informationen')),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(
                                $tblAccount->getServiceTblConsumer()->getName().' ['.$tblAccount->getServiceTblConsumer()->getAcronym().']',
                                array(
                                    // TODO: Anzeigen von Schulen, Schulträger, Vörderverein
                                    // TODO: Anzeigen von zugehörigen Adressen, Telefonnummern, Personen
                                    'TODO: Anzeigen von Schulen, Schulträger, Vörderverein',
                                    'TODO: Anzeigen von zugehörigen Adressen, Telefonnummern, Personen'
                                )
                                , Panel::PANEL_TYPE_DEFAULT,
                                new Standard('Mandant ändern', new Route(__NAMESPACE__.'/Consumer'))
                            )
                        )
                    )
                    , new Title('Mandant', 'Informationen')),
            ))
        );

        return $Stage;
    }
}
