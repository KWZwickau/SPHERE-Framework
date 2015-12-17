<?php
namespace SPHERE\Application\Setting\MyAccount;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Mail\Service\Entity\TblToCompany as TblToCompanyMail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToCompany as TblToCompanyPhone;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAuthorization;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\Responsibility\Responsibility;
use SPHERE\Application\Setting\Consumer\Responsibility\Service\Entity\TblResponsibility;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Application\Setting\Consumer\School\Service\Entity\TblSchool;
use SPHERE\Application\Setting\Consumer\SponsorAssociation\Service\Entity\TblSponsorAssociation;
use SPHERE\Application\Setting\Consumer\SponsorAssociation\SponsorAssociation;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\PasswordField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Key;
use SPHERE\Common\Frontend\Icon\Repository\Lock;
use SPHERE\Common\Frontend\Icon\Repository\Repeat;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Setting\MyAccount
 */
class Frontend extends Extension implements IFrontendInterface
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
     * @param array $Setting
     *
     * @return Stage
     */
    public function frontendMyAccount($Setting = array())
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

        $Person = new Panel('Informationen zur Person',
            ( !empty( $tblPersonAll ) ? new Listing($tblPersonAll) : new Danger(new Exclamation().new Small(' Keine Person angeben')) )
        );

        $Authentication = new Panel('Authentication',
            ( $tblAccount->getServiceTblIdentification() ? $tblAccount->getServiceTblIdentification()->getDescription() : '' )
        );

        $Authorization = new Panel('Berechtigungen',
            ( !empty( $tblAuthorizationAll )
                ? $tblAuthorizationAll
                : array(new Danger(new Exclamation().new Small(' Keine Berechtigungen vergeben')))
            )
        );

        $Token = new Panel('Hardware-Schlüssel',
            array(
                $tblAccount->getServiceTblToken()
                    ? substr($tblAccount->getServiceTblToken()->getSerial(), 0,
                        4).' '.substr($tblAccount->getServiceTblToken()->getSerial(), 4, 4)
                    : new Muted(new Small('Kein Hardware-Schlüssel vergeben'))
            )
        );

        $Account = array(
            $Person,
            $Authentication,
            $Authorization,
            $Token,
        );

        if (empty( $Setting )) {
            $Global = $this->getGlobal();
            $SettingSurface = MyAccount::useService()->getSettingByAccount($tblAccount, 'Surface');
            if ($SettingSurface) {
                $Global->POST['Setting']['Surface'] = $SettingSurface->getValue();
                $Global->savePost();
            }
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Title('Konfiguration', 'Benutzereinstellungen'),
                            MyAccount::useService()->updateSetting(
                                new Form(
                                    new FormGroup(
                                        new FormRow(
                                            new FormColumn(array(
                                                new Panel(
                                                    'Oberfläche', array(
                                                        new SelectBox(
                                                            'Setting[Surface]',
                                                            'Aussehen der Programmoberfläche',
                                                            array(1 => 'Webseite', 2 => 'Anwendung')
                                                        ),
                                                    )
                                                    , Panel::PANEL_TYPE_INFO),
//                                                new Panel(
//                                                    'Statistik', array(
//                                                        '<iframe class="sphere-iframe-style" src="/Library/Piwik/index.php?module=CoreAdminHome&action=optOut&language=de"></iframe>',
//                                                    )
//                                                    , Panel::PANEL_TYPE_DEFAULT),
                                            ))
                                        )
                                    )
                                    , new Primary('Einstellungen speichern')
                                ), $tblAccount, $Setting)
                        ), 4),
                        new LayoutColumn(array(
                            new Title('Profil', 'Informationen'),
                            new Panel(
                                'Benutzerkonto: '.new Bold($tblAccount->getUsername()), $Account
                                , Panel::PANEL_TYPE_INFO,
                                new Standard('Mein Passwort ändern', new Route(__NAMESPACE__.'/Password'), new Key())
                            )
                        ), 4),
                        new LayoutColumn(array(
                            new Title('Kontaktdaten', 'Informationen'),
                            new Panel(
                                $tblAccount->getServiceTblConsumer()->getName().' ['.$tblAccount->getServiceTblConsumer()->getAcronym().']',
                                array(
                                    new Container(implode($this->listingSchool()))
                                    .new Container(implode($this->listingResponsibility()))
                                    .new Container(implode($this->listingSponsorAssociation()))
                                )
                                , Panel::PANEL_TYPE_INFO,
                                new Standard('Zugriff auf Mandant ändern', new Route(__NAMESPACE__.'/Consumer'))
                            )
                        ), 4),
                    ))
                )
            )
        );

        return $Stage;
    }

    private function listingSchool()
    {

        $tblSchoolAll = School::useService()->getSchoolAll();
        $Result = array();
        if ($tblSchoolAll) {
            array_walk($tblSchoolAll, function (TblSchool $tblSchool) use (&$Result) {

                $List = array();

                $tblCompany = $tblSchool->getServiceTblCompany();

                $tblAddressAll = Address::useService()->getAddressAllByCompany($tblCompany);
                if ($tblAddressAll) {
                    array_walk($tblAddressAll, function (TblToCompany $tblToCompany) use (&$List) {

                        if ($tblToCompany->getTblType()->getId() == 1) // TODO: Find 'Hauptadresse' by Identifier
                        {
                            $tblAddress = $tblToCompany->getTblAddress();
                            $List[] = $tblAddress->getGuiLayout();
                        }
                    });
                }
                $tblPhoneAll = Phone::useService()->getPhoneAllByCompany($tblCompany);
                if ($tblPhoneAll) {
                    array_walk($tblPhoneAll, function (TblToCompanyPhone $tblToCompany) use (&$List) {

                        $tblPhone = $tblToCompany->getTblPhone();
                        $List[] = $tblToCompany->getTblType()->getName().' '.$tblToCompany->getTblType()->getDescription().': '.$tblPhone->getNumber();
                    });
                }
                $tblMailAll = Mail::useService()->getMailAllByCompany($tblCompany);
                if ($tblMailAll) {
                    array_walk($tblMailAll, function (TblToCompanyMail $tblToCompany) use (&$List) {

                        $tblMail = $tblToCompany->getTblMail();
                        $List[] = $tblToCompany->getTblType()->getName().' '.$tblToCompany->getTblType()->getDescription().': '.$tblMail->getAddress();
                    });
                }
                $Result[] = new Panel(
                    new Bold($tblSchool->getServiceTblType()->getName()).' - '.$tblCompany->getName().' '.$tblCompany->getDescription(),
                    $List,
                    Panel::PANEL_TYPE_DEFAULT
                );
            });
        }
        return $Result;
    }

    private function listingResponsibility()
    {

        $tblResponsibilityAll = Responsibility::useService()->getResponsibilityAll();
        $Result = array();
        if ($tblResponsibilityAll) {
            array_walk($tblResponsibilityAll, function (TblResponsibility $tblResponsibility) use (&$Result) {

                $List = array();

                $tblCompany = $tblResponsibility->getServiceTblCompany();

                $tblAddressAll = Address::useService()->getAddressAllByCompany($tblCompany);
                if ($tblAddressAll) {
                    array_walk($tblAddressAll, function (TblToCompany $tblToCompany) use (&$List) {

                        if ($tblToCompany->getTblType()->getId() == 1) // TODO: Find 'Hauptadresse' by Identifier
                        {
                            $tblAddress = $tblToCompany->getTblAddress();
                            $List[] = $tblAddress->getGuiLayout();
                        }
                    });
                }
                $tblPhoneAll = Phone::useService()->getPhoneAllByCompany($tblCompany);
                if ($tblPhoneAll) {
                    array_walk($tblPhoneAll, function (TblToCompanyPhone $tblToCompany) use (&$List) {

                        $tblPhone = $tblToCompany->getTblPhone();
                        $List[] = $tblToCompany->getTblType()->getName().' '.$tblToCompany->getTblType()->getDescription().': '.$tblPhone->getNumber();
                    });
                }
                $tblMailAll = Mail::useService()->getMailAllByCompany($tblCompany);
                if ($tblMailAll) {
                    array_walk($tblMailAll, function (TblToCompanyMail $tblToCompany) use (&$List) {

                        $tblMail = $tblToCompany->getTblMail();
                        $List[] = $tblToCompany->getTblType()->getName().' '.$tblToCompany->getTblType()->getDescription().': '.$tblMail->getAddress();
                    });
                }
                $Result[] = new Panel(
                    new Bold('Schulträger: ').$tblCompany->getName().' '.$tblCompany->getDescription(), $List,
                    Panel::PANEL_TYPE_DEFAULT
                );
            });
        }
        return $Result;
    }

    private function listingSponsorAssociation()
    {

        $tblSponsorAssociationAll = SponsorAssociation::useService()->getSponsorAssociationAll();
        $Result = array();
        if ($tblSponsorAssociationAll) {
            array_walk($tblSponsorAssociationAll,
                function (TblSponsorAssociation $tblSponsorAssociation) use (&$Result) {

                    $List = array();

                    $tblCompany = $tblSponsorAssociation->getServiceTblCompany();

                    $tblAddressAll = Address::useService()->getAddressAllByCompany($tblCompany);
                    if ($tblAddressAll) {
                        array_walk($tblAddressAll, function (TblToCompany $tblToCompany) use (&$List) {

                            if ($tblToCompany->getTblType()->getId() == 1) // TODO: Find 'Hauptadresse' by Identifier
                            {
                                $tblAddress = $tblToCompany->getTblAddress();
                                $List[] = $tblAddress->getGuiLayout();
                            }
                        });
                    }
                    $tblPhoneAll = Phone::useService()->getPhoneAllByCompany($tblCompany);
                    if ($tblPhoneAll) {
                        array_walk($tblPhoneAll, function (TblToCompanyPhone $tblToCompany) use (&$List) {

                            $tblPhone = $tblToCompany->getTblPhone();
                            $List[] = $tblToCompany->getTblType()->getName().' '.$tblToCompany->getTblType()->getDescription().': '.$tblPhone->getNumber();
                        });
                    }
                    $tblMailAll = Mail::useService()->getMailAllByCompany($tblCompany);
                    if ($tblMailAll) {
                        array_walk($tblMailAll, function (TblToCompanyMail $tblToCompany) use (&$List) {

                            $tblMail = $tblToCompany->getTblMail();
                            $List[] = $tblToCompany->getTblType()->getName().' '.$tblToCompany->getTblType()->getDescription().': '.$tblMail->getAddress();
                        });
                    }
                    $Result[] = new Panel(
                        new Bold('Förderverein: ').$tblCompany->getName().' '.$tblCompany->getDescription(), $List,
                        Panel::PANEL_TYPE_DEFAULT
                    );
                });
        }
        return $Result;
    }
}
