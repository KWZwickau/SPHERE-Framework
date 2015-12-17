<?php
namespace SPHERE\Application\Setting\MyAccount;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAuthorization;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\Responsibility\Responsibility;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Application\Setting\Consumer\School\Service\Entity\TblSchool;
use SPHERE\Application\Setting\Consumer\SponsorAssociation\SponsorAssociation;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\PasswordField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Key;
use SPHERE\Common\Frontend\Icon\Repository\Lock;
use SPHERE\Common\Frontend\Icon\Repository\Repeat;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Accordion;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
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

        $tblSchoolList = School::useService()->getSchoolAll();
        $tblResponsibilityList = Responsibility::useService()->getResponsibilityAll();
        $tblSponsorAssociationList = SponsorAssociation::useService()->getSponsorAssociationAll();
        $result = array();
        if (count($tblSchoolList) > 1) {
            $result = $this->CompanyPanel($tblSchoolList, 'Schulen', $result, '/Setting/Consumer/School', true);
        } else {
            $result = $this->CompanyPanel($tblSchoolList, 'Schule', $result, '/Setting/Consumer/School', true);
        }
        $result = $this->CompanyPanel($tblResponsibilityList, 'Schulträger', $result, '/Setting/Consumer/Responsibility');
        if (count($tblSchoolList) > 1) {
            $result = $this->CompanyPanel($tblSponsorAssociationList, 'Fördervereine', $result, '/Setting/Consumer/SponsorAssociation');
        } else {
            $result = $this->CompanyPanel($tblSponsorAssociationList, 'Förderverein', $result, '/Setting/Consumer/SponsorAssociation');
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
                                                    , Panel::PANEL_TYPE_DEFAULT),
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
                                , Panel::PANEL_TYPE_DEFAULT,
                                new Standard('Mein Passwort ändern', new Route(__NAMESPACE__.'/Password'), new Key())
                            )
                        ), 4),
                        new LayoutColumn(array(
                            new Title('Mandant (Schulträger)', 'Informationen'),
                            new Panel(
                                $tblAccount->getServiceTblConsumer()->getName().' ['.$tblAccount->getServiceTblConsumer()->getAcronym().']',
                                $result
                                // TODO: Anzeigen von Schulen, Schulträger, Vörderverein <- in arbeit
                                // TODO: Anzeigen von zugehörigen Adressen, Telefonnummern, Personen
                                , Panel::PANEL_TYPE_DEFAULT,
                                new Standard('Zugriff auf Mandant ändern', new Route(__NAMESPACE__.'/Consumer'))
                            )
                        ), 4),
                    ))
                )
            )
        );

        return $Stage;
    }

    /**
     * @param array  $tblCompanyList
     * @param string $Head
     * @param array  $result
     * @param string $Path
     * @param bool   $visible
     *
     * @return array
     */
    public function CompanyPanel($tblCompanyList, $Head, $result, $Path = '', $visible = false)
    {

        if ($tblCompanyList) {
            $CompanyTemp = array();
            /** @var TblSchool $tblCompanyLink */
            foreach ($tblCompanyList as $tblCompanyLink) {

                $tblCompany = $tblCompanyLink->getServiceTblCompany();
                $tblAddressList = Address::useService()->getAddressAllByCompany($tblCompany);
                $Address = '';
                if ($tblAddressList) {
                    foreach ($tblAddressList as $tblAddressLink) {
                        $tblAddress = $tblAddressLink->getTblAddress();
                        $Address[] = new Well($tblAddressLink->getTblType()->getName().':<br/>'.$tblAddress->getStreetName().' '.$tblAddress->getStreetNumber().'<br/>'.
                            $tblAddress->getTblCity()->getCode().' '.$tblAddress->getTblCity()->getName());
                    }
                    $Address = implode('<br/>', $Address);
                }


                $tblPersonList = Relationship::useService()->getCompanyRelationshipAllByCompany($tblCompany);
                $Person = '';
                if ($tblPersonList) {
                    foreach ($tblPersonList as $tblPerson) {
                        $Person[] = $tblPerson->getTblType()->getName().''.new PullRight($tblPerson->getServiceTblPerson()->getFullName());
                    }
                    $Person = implode('<br/>', $Person);
                }
                $PhoneList = Phone::useService()->getPhoneAllByCompany($tblCompany);
                $PhoneNumber = '';
                if ($PhoneList) {
                    foreach ($PhoneList as $Phone) {
                        $PhoneNumber[] = 'Tel.: '.$Phone->getTblType()->getName().''.new PullRight($Phone->getTblPhone()->getNumber());
                    }
                    $PhoneNumber = implode('<br/>', $PhoneNumber);
                }
                if ($Address !== '') {
                    $Address = '<br/>'.$Address;
                }
                if ($PhoneNumber !== '') {
                    $PhoneNumber = '<br/>'.$PhoneNumber;
                }
                if ($Person !== '') {
                    $Person = '<br/><br/>'.$Person;
                }

                $CompanyTemp[] = new Well(new Bold($tblCompany->getName())
                        .$Address)
                    .$PhoneNumber
                    .$Person;

            }
            if (is_array($CompanyTemp)) {
                $result[] = (new Accordion())->addItem($Head, New Panel('', $CompanyTemp), $visible);
            }
        } else {
            $result[] = new Well(new Standard($Head.' einstellen', $Path, new Edit()));
        }
        return $result;
    }
}
