<?php

namespace SPHERE\Application\Platform\Gatekeeper\Authentication;

use DateTime;
use Exception;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\ParentStudentAccess\OnlineAbsence\OnlineAbsence;
use SPHERE\Application\ParentStudentAccess\OnlineContactDetails\OnlineContactDetails;
use SPHERE\Application\ParentStudentAccess\OnlineGradebook\OnlineGradebook;
use SPHERE\Application\People\ContactDetails\ContactDetails;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\Platform\Gatekeeper\Authentication\Saml\SamlDLLP;
use SPHERE\Application\Platform\Gatekeeper\Authentication\Saml\SamlDLLPDemo;
use SPHERE\Application\Platform\Gatekeeper\Authentication\Saml\SamlPlaceholder;
use SPHERE\Application\Platform\Gatekeeper\Authentication\TwoFactorApp\TwoFactorApp;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblSetting;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Token;
use SPHERE\Application\Setting\Agb\Agb;
use SPHERE\Application\Setting\MyAccount\MyAccount;
use SPHERE\Application\Setting\User\Account\Account as UserAccount;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\PasswordField;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Cog;
use SPHERE\Common\Frontend\Icon\Repository\CogWheels;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Enable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Globe;
use SPHERE\Common\Frontend\Icon\Repository\Key;
use SPHERE\Common\Frontend\Icon\Repository\Lock;
use SPHERE\Common\Frontend\Icon\Repository\MoreItems;
use SPHERE\Common\Frontend\Icon\Repository\Nameplate;
use SPHERE\Common\Frontend\Icon\Repository\Off;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\Icon\Repository\Picture;
use SPHERE\Common\Frontend\Icon\Repository\YubiKey;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Header;
use SPHERE\Common\Frontend\Layout\Repository\Headline;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Paragraph;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Ruler;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary as PrimaryLink;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Link\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Danger as DangerMessage;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\ITextInterface;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Info as InfoText;
use SPHERE\Common\Frontend\Text\Repository\Italic;
use SPHERE\Common\Frontend\Text\Repository\Warning as WarningText;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Debugger\DebuggerFactory;
use SPHERE\System\Debugger\Logger\ErrorLogger;
use SPHERE\System\Debugger\Logger\FileLogger;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\phpSaml;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\System\Gatekeeper\Authentication
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendWelcome()
    {

        $Stage = new Stage('Willkommen', '', '');
        $Date = '2022-08-18 ';
        $IsMaintenance = (new DateTime('now') >= new DateTime($Date.'13:00:00')
                       && new DateTime('now') <= new DateTime($Date.'23:59:59'));
        $maintenanceMessage = '';
        $contentTeacherWelcome = false;
        $contentSecretariatWelcome = false;
        $contentMissingTimeSpan = false;
        $IsChangePassword = false;
        $IsNavigationAssistance = false;
        $IsStudentAccount = false;

        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
            if ($tblPersonAllByAccount) {
                $tblPerson = $tblPersonAllByAccount[0];
                if ($tblPerson
                    && ($tblGroup = Group::useService()->getGroupByMetaTable('TEACHER'))
                    && Group::useService()->existsGroupPerson($tblGroup, $tblPerson)
                ) {
                    $contentTeacherWelcome = Grade::useService()->getTeacherWelcomeGradeTask($tblPerson)
                        . (($timeTable = Timetable::useService()->getTimetablePanelForTeacher())
                            ? $timeTable : Digital::useService()->getDigitalClassRegisterPanelForTeacher());
                }
            }

            // gespeichertes Notenbuch Schuljahr zurücksetzen
            if (\SPHERE\Application\Setting\Consumer\Consumer::useService()->getAccountSettingValue("GradeBookSelectedYearId")) {
                \SPHERE\Application\Setting\Consumer\Consumer::useService()->createAccountSetting("GradeBookSelectedYearId", "");
            }
        }
        if ($tblAccount) {
            if ($tblAccount->getHasAuthentication(TblIdentification::NAME_USER_CREDENTIAL)) {
                // Alle TblUserAccounts erhalten direktlink Button
                $IsNavigationAssistance = true;

                // Eltern und Schüler funktionieren anders als die anderen Accounts
                if (($tblUserAccount = UserAccount::useService()->getUserAccountByAccount($tblAccount))) {
                    $IsStudentAccount = $tblUserAccount->getType() == TblUserAccount::VALUE_TYPE_STUDENT;
                    $Password = $tblUserAccount->getAccountPassword();
                    if ($tblAccount->getPassword() == $Password) {
                        $IsChangePassword = true;
                    }
                }
            } else {
                if (Account::useService()->isAccountPWInitial($tblAccount)
                    // Standard-Passwort
                    || $tblAccount->getPassword() == '547d0783ae13fa4ab68ae8f3a1f1ee44e6795be7137b1c14b808c393d328f2e7'
                ) {
                    $IsChangePassword = true;
                }
            }
        }
        if ($IsMaintenance) {
            $now = new DateTime();
            if ($now >= new DateTime('22:00')) {
                $PanelColor = Panel::PANEL_TYPE_DANGER;
                $maintenanceMessage = new Panel(new Headline(new Bold(new Center(new Cog().' Wartung &nbsp;'.new CogWheels()))),
                    new DangerMessage(new Container(new Center(new Bold('Achtung laufende Wartungsarbeiten seit 22:00
                        bis vorraussichtlich 0:00.')))
                    .new Container(new Center(new Bold('Es wird empfohlen, sich wegen der Wartung abzumelden,
                     um Datenverlust der getätigten Eingaben zu vermeiden.')))
                    .new Container((new ProgressBar(0,100,0, 8))->setColor(ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_DANGER))
                    , null, false, '8', '5'), $PanelColor);
            } elseif ($now >= new DateTime('20:00')) {
                $PanelColor = Panel::PANEL_TYPE_WARNING;
                $DiffTime = (new DateTime('now'))->diff(new DateTime($Date.' 22:00:00'));
//                $DiffTime = (new DateTime('now'))->diff(new DateTime('2021-07-28 22:00:00'));
                $Minutes = $DiffTime->h * 60;
                $Minutes = $Minutes + $DiffTime->i;
                $aktiveProgressbar = $Minutes/120*100;
                $doneProgressbar = 100 - $aktiveProgressbar;
                $maintenanceMessage = new Panel(new Headline(new Bold(new Center(new Cog().' Wartung &nbsp;'.new CogWheels()))),
                    new DangerMessage(new Container(new Center('Achtung heute ('.$now->format('d.m.Y')
                            .') ab 22:00 Wartungsarbeiten, voraussichtlich 2 Stunden.')).new Container(new Center(new Bold('Es wird empfohlen, sich 
                        vor der Wartung abzumelden, um Datenverlust von den Eingaben zu vermeiden.').' ('.new Italic('noch '.$Minutes.' Minuten').')'))
                        .new Container((new ProgressBar(0, $doneProgressbar, $aktiveProgressbar, 8))->setColor(ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_WARNING, ProgressBar::BAR_COLOR_SUCCESS))
                        , null, false, '8', '5'), $PanelColor
                );
            } elseif ($now >= new DateTime('9:00')) {
                $PanelColor = Panel::PANEL_TYPE_WARNING;
                $maintenanceMessage = new Panel(new Headline(new Bold(new Center(new Cog().' Wartung &nbsp;'.new CogWheels()))),
                    new Warning(new Center('Achtung heute ('.$now->format('d.m.Y').') ab 22:00
                     Wartungsarbeiten, voraussichtlich 2 Stunden.'), null, false, '8', '5'), $PanelColor
                );
            }
        }

        // specialLogin?
        $isConsumerLogin = false;
        if(($tblAccount = Account::useService()->getAccountBySession())) {
            if (($tblConsumer = $tblAccount->getServiceTblConsumer())) {
                if(Consumer::useService()->getConsumerLoginListByConsumer($tblConsumer)){
                    $isConsumerLogin = true;
                }
                // Vidis temporär Login auf der Demo-Version
                if( strtolower($this->getRequest()->getHost()) == 'demo.schulsoftware.schule'
                    || $this->getRequest()->getHost() == '192.168.202.230'
                ){
                    if($tblConsumer->getAcronym() == 'DEMO'){
                        $isConsumerLogin = true;
                    }
                }
            }
        }

        if (Access::useService()->hasAuthorization('/People/ContactDetails')) {
            $contentSecretariatWelcome = ContactDetails::useFrontend()->getWelcome();
        }
        if (Access::useService()->hasAuthorization('/Education/Lesson/Term/Create/Year')) {
            $contentMissingTimeSpan = Term::useFrontend()->getWelcome();
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ($IsMaintenance
                                ? $maintenanceMessage
                                : ''
                            )
                        )
                    )
                )
            )
            .($IsChangePassword && !$isConsumerLogin
                ? $this->layoutPasswordChange()
                : ''
            )
            . ($IsNavigationAssistance
                ? $this->layoutNavigationAssistance($IsStudentAccount)
                : ''
            )
            . ($contentTeacherWelcome ?: '')
            . ($contentSecretariatWelcome ?: '')
            . ($contentMissingTimeSpan ?: '')
            . $this->getCleanLocalStorage()
        );

        return $Stage;
    }

    /**
     * @return Layout
     */
    private function layoutPasswordChange()
    {
        return new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn('', 2),
                    new LayoutColumn(
                        new Center(new Panel('Passwortänderung',
                            new Warning('Bitte ändern Sie zu Ihrer Sicherheit das Passwort.')
                            , Panel::PANEL_TYPE_DANGER,
                            new Standard('Passwort ändern', '/Setting/MyAccount/Password'
                                , new Key(), array(), 'Schnellzugriff der Passwort Änderung')))
                        , 8)
                ))
            )
        );
    }

    /**
     * @param bool $IsStudentAccount
     * @return string
     */
    private function layoutNavigationAssistance(bool $IsStudentAccount): string
    {
        $columns = array();
        if ($IsStudentAccount ? OnlineGradebook::useService()->getPersonListFromStudentLogin() : OnlineGradebook::useService()->getPersonListFromCustodyLogin()) {
            $columns[] = new LayoutColumn(new Link(
                (new Thumbnail(FileSystem::getFileLoader('/Common/Style/Resource/SSWInfo.png'), 'Notenübersicht'))->setPictureHeight(),
                '/ParentStudentAccess/OnlineGradebook'
            ), 4);
        }
        if ($IsStudentAccount ? OnlineAbsence::useService()->getPersonListFromStudentLogin() : OnlineAbsence::useService()->getPersonListFromCustodyLogin()) {
            $columns[] = new LayoutColumn(new Link(
                (new Thumbnail(FileSystem::getFileLoader('/Common/Style/Resource/SSWImport.png'), 'Fehlzeiten'))->setPictureHeight(),
                '/ParentStudentAccess/OnlineAbsence'
            ), 4);
        }
        if ($IsStudentAccount ? OnlineContactDetails::useService()->getPersonListFromStudentLogin() : OnlineContactDetails::useService()->getPersonListFromCustodyLogin()) {
            $columns[] = new LayoutColumn(new Link(
                (new Thumbnail(FileSystem::getFileLoader('/Common/Style/Resource/SSWUser.png'), 'Kontakt-Daten'))->setPictureHeight(),
                '/ParentStudentAccess/OnlineContactDetails'
            ), 4);
        }

        return empty($columns) ? '' : new Layout(new LayoutGroup(new LayoutRow($columns)));
    }

    /**
     * @return string
     */
    private function getCleanLocalStorage()
    {

        return '<script language=javascript>
            //noinspection JSUnresolvedFunction
            executeScript(function()
            {
                Client.Use("ModCleanStorage", function()
                {
                    jQuery().ModCleanStorage();
                });
            });
        </script>';
    }

    /**
     * Step 1/3
     *
     * @param string $CredentialName
     * @param string $CredentialLock
     *
     * @return Stage
     */
    public function frontendIdentificationCredential($CredentialName = null, $CredentialLock = null)
    {
        $View = new Stage(new Nameplate().' Anmelden', '', $this->getIdentificationEnvironment());

        // Search for matching Account
        $tblAccount = null;
        if ($CredentialName && $CredentialLock) {
            $tblAccount = Account::useService()->getAccountByCredential($CredentialName, $CredentialLock);
        }

        // Matching Account found?
        if ($tblAccount) {
            if ($tblAccount->getHasAuthentication(TblIdentification::NAME_SYSTEM)
                || $tblAccount->getHasAuthentication(TblIdentification::NAME_TOKEN)
                || $tblAccount->getHasAuthentication(TblIdentification::NAME_AUTHENTICATOR_APP)
            ) {
                return $this->frontendIdentificationToken($tblAccount->getId());
            } elseif ($tblAccount->getHasAuthentication(TblIdentification::NAME_CREDENTIAL)
                || $tblAccount->getHasAuthentication(TblIdentification::NAME_USER_CREDENTIAL)
            ) {
                return $this->frontendIdentificationAgb($tblAccount->getId());
            }
        }

        // Field Definition
        $CredentialNameField = (new TextField('CredentialName', 'Benutzername', 'Benutzername', new Person()))
            ->setRequired()->setAutoFocus();
        $CredentialLockField = (new PasswordField('CredentialLock', 'Passwort', 'Passwort', new EyeOpen()))
            ->setRequired()->setDefaultValue($CredentialLock, true);
        $CredentialLockField->setShow(new Lock());

        // Error Handling
        if ($CredentialName !== null) {
            if (empty($CredentialName)) {
                $CredentialNameField->setError('Bitte geben Sie Ihren Benutzernamen an');
            }
        }
        if ($CredentialLock !== null) {
            if (empty($CredentialLock)) {
                $CredentialLockField->setError('Bitte geben Sie Ihr Passwort an');
            }
        }
        $FormError = new Container('');
        if ($CredentialName && $CredentialLock && !$tblAccount) {
            $CredentialNameField->setError('');
            $CredentialLockField->setError('');
            $FormError = new Listing(array(new Danger(new Exclamation() . ' Die eingegebenen Zugangsdaten sind nicht gültig')));
        }

        // Create Form
        $Form = new Form(
            new FormGroup(array(
                new FormRow(
                    new FormColumn(array(
                        new Headline('Anmeldung Schulsoftware'),
                        new Ruler(),
                        new Listing(array(
                            new Container($CredentialNameField) .
                            new Container($CredentialLockField)
                        )),
                        $FormError
                    ))
                ),
                new FormRow(
                    new FormColumn(array(
                        (new Primary('Login')),
                    ))
                )
            ))
        );

        // set depending information
        if(strtolower($this->getRequest()->getHost()) == 'www.schulsoftware.schule'
//            || $this->getRequest()->getHost() == '192.168.75.128' // local test
//            || $this->getRequest()->getHost() == '192.168.37.128' // local test
        ){
            $Form.= new Layout(new LayoutGroup(new LayoutRow(
                new LayoutColumn(array(
                    '<br/><br/><br/><br/>',
                    new Title('Anmeldung UCS'),
                    new PrimaryLink('Login', 'SPHERE\Application\Platform\Gatekeeper\Saml\Login\DLLP')
                    // Frontend dazu muss noch entschieden werden
//                    .new PrimaryLink('Placeholder', 'SPHERE\Application\Platform\Gatekeeper\Saml\Login\Placeholder') // -> Beispiel kann für zukünftige IDP's verwendet werden

                ))
            )));
//        } elseif( strtolower($this->getRequest()->getHost()) == 'demo.schulsoftware.schule'
//        || $this->getRequest()->getHost() == '192.168.202.230'
//        ){
//            // Vidis temporär Login auf der Demo-Version
//            $Form.= new Layout(new LayoutGroup(new LayoutRow(
//                new LayoutColumn(array(
//                    '<br/><br/><br/><br/>',
//                    new Title('Anmeldung Vidis Demo'),
//                        (new PrimaryLink('Login', '/Platform/Gatekeeper/OAuth2/OAuthSite'))->setExternal()
//                    .'<script src="https://repo.vidis.schule/repository/vidis-cdn/latest/vidisLogin.umd.js"></script>'
////                    // size L/M/S
////                    // cookie = "true" (zum testen erstmal false)
//                    .'<vidis-login Size = "L" cookie = "true" loginurl="http://demo.schulsoftware.schule/Platform/Gatekeeper/OAuth2/OAuthSite"></vidis-login>'
//                ))
//            )));
        }

        setcookie('cookies_available', 'enabled', time() + (86400 * 365), '/');

        $View->setContent($this->getIdentificationLayout($Form));

        return $View;
    }

    /**
     * @return Stage
     */
    public function frontendIdentificationSamlPlaceholder()
    {

        return $this->LoginSecondPageLogic(SamlPlaceholder::getSAML());
    }

    /**
     * @return Stage
     */
    public function frontendIdentificationSamlDLLP()
    {

        return $this->LoginSecondPageLogic(SamlDLLP::getSAML());
    }

    /**
     * @return Stage
     */
    public function frontendIdentificationSamlDLLPDemo()
    {

        return $this->LoginSecondPageLogic(SamlDLLPDemo::getSAML());
    }

//    /**
//     * // EKM -> Beispiel kann für zukünftige IDP's verwendet werden
//     * @return Stage
//     */
//    public function frontendIdentificationSamlEKM()
//    {
//
//        return $this->LoginSecondPageLogic(SamlEKM::getSAML());
//    }

    private function LoginSecondPageLogic($Config = array())
    {

        $Stage = new Stage(new Nameplate().' Anmelden', '', $this->getIdentificationEnvironment());

        $Saml = new phpSaml($Config);
        if(($Error = $Saml->getAuthRequest())){
            $Stage->setContent($Error);
            return $Stage;
        }
        $tblAccount = null;
        $LoginOk = false;

        if(isset($_SESSION['samlUserdata']['uid']) && !empty($_SESSION['samlUserdata']['uid'])){
            $AccountNameAPI = current($_SESSION['samlUserdata']['uid']);
        } else {
            $AccountNameAPI = new Bold('UCS missing (uid)');
        }

        if(isset($_SESSION['samlUserdata']['ucsschoolRecordUID']) && $_SESSION['samlUserdata']['ucsschoolRecordUID']){
//            $AccountNameAPI = current($_SESSION['samlUserdata']['uid']);
            $AccountId = current($_SESSION['samlUserdata']['ucsschoolRecordUID']);
            $tblAccount = Account::useService()->getAccountById($AccountId);
        } else {
            $AccountId = new Bold('UCS missing (ucsschoolRecordUID)');
        }

        // AccountId gegen Prüfung
        if(isset($AccountNameAPI)
            && $tblAccount
            && $tblAccount->getUsername() != $AccountNameAPI){
            $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(
                new LayoutColumn(new Warning('Ihr Login ist irregulär, bitte wenden Sie sich an einen zuständigen Administrator'))
            ))));
            return $Stage;
        }

        // Login block für System Accounts
        if ($tblAccount && $tblAccount->getHasAuthentication(TblIdentification::NAME_SYSTEM)) {
            $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(
                new LayoutColumn(new Warning('Systemaccounts dürfen keinen SSO Login erhalten'))
            ))));
            return $Stage;
        }
        if(isset($_SESSION['isAuthenticated']) && $_SESSION['isAuthenticated']){
            $LoginOk = true;
        }

        if(($ExistSessionAccount = Account::useService()->getAccountBySession())){
            // is requested account the same like session account go to welcome
            if($tblAccount && $ExistSessionAccount->getId() == $tblAccount->getId()){
                $Stage->setContent(new Redirect('/', Redirect::TIMEOUT_SUCCESS));
                return $Stage;
            }
            // remove existing Session if User is not the same
            if($Session = session_id()){
                Account::useService()->destroySession(null, $Session);
            }
        }

        // Matching Account found?
        if ($tblAccount && $LoginOk) {
            // Anfragen von SAML müssen Cookies aktiviert haben
            $isCookieAvailable = true;
            if ($tblAccount->getHasAuthentication(TblIdentification::NAME_SYSTEM)
                || $tblAccount->getHasAuthentication(TblIdentification::NAME_TOKEN)
                || $tblAccount->getHasAuthentication(TblIdentification::NAME_AUTHENTICATOR_APP)
            ) {
                return $this->frontendIdentificationToken($tblAccount->getId(), null, $isCookieAvailable);
            } elseif ($tblAccount->getHasAuthentication(TblIdentification::NAME_CREDENTIAL)
                || $tblAccount->getHasAuthentication(TblIdentification::NAME_USER_CREDENTIAL)
            ) {
                return $this->frontendIdentificationAgb($tblAccount->getId(), 0, $isCookieAvailable);
            }
        }

        $detailInfo = '';
        if(isset($AccountNameAPI) || isset($AccountId)){
            $detailInfo = '( '.(isset($AccountNameAPI)? $AccountNameAPI: '').' [ '.(isset($AccountId)? $AccountId: '').' ]'.' )';
        }

        $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(
            new LayoutColumn(new Warning('Ihr Login von UCS '.$detailInfo.' ist im System nicht bekannt, bitte wenden Sie sich an einen zuständigen Administrator'))
        ))));

        return $Stage;
    }

    /**
     * Environment Information
     *
     * @return ITextInterface
     */
    private function getIdentificationEnvironment()
    {
        switch (strtolower($this->getRequest()->getHost())) {
            case 'www.schulsoftware.schule':
            case 'www.kreda.schule':
            case 'ekbo.schulsoftware.schule':
                return new InfoText('');
            case 'ekbodemo.schulsoftware.schule':
            case 'demo.schulsoftware.schule':
            case 'demo.kreda.schule':
                return new Danger(new Picture().' Demo-Umgebung');
            case 'nightly.schulsoftware.schule':
                return new Danger(new Picture().' Nightly-Umgebung');
            default:
                return new WarningText( new Globe().' '.$this->getRequest()->getHost());
        }
    }

    /**
     * @param int         $tblAccount
     * @param null|string $otpCredentialKey
     * @param bool        $isCookieAvailable
     *
     * @return Stage
     */
    public function frontendIdentificationToken($tblAccount, $otpCredentialKey = null, $isCookieAvailable = false)
    {
        $View = new Stage(new YubiKey() . ' Anmelden', '', $this->getIdentificationEnvironment());

        $tblAccount = Account::useService()->getAccountById($tblAccount);

        // Return on Input Error
        if (!$tblAccount) {
            // Restart Identification Process
            return $this->frontendIdentificationCredential();
        }

        // Token und App gleichzeitig für Benutzer möglich
        if ($tblAccount->getHasAuthentication(TblIdentification::NAME_AUTHENTICATOR_APP)
            && ($tblAccount->getHasAuthentication(TblIdentification::NAME_SYSTEM) || $tblAccount->getHasAuthentication(TblIdentification::NAME_TOKEN))
        ) {
            // SSW-2129 OTP direkt aus Passwort-Manager funktioniert nicht in diesem Fall (beide Authentifizierungsverfahren aktiv)
            $otpCredentialKeyField = (new PasswordField('otpCredentialKey', '', 'YubiKey oder Authenticator App'))->setRequired()->setAutoFocus();
        } elseif ($tblAccount->getHasAuthentication(TblIdentification::NAME_AUTHENTICATOR_APP)) {
            // Field Definition
            // SSW-2129 OTP direkt aus Passwort-Manager
            // Lable, Name und AutoComplete wird von verschiedenen Anwendungen (Browser/Handy) unterschiedlich ausgelesen, deswegen alle 3 Varianten integriert
            $otpCredentialKeyField = (new TextField('otpCredentialKey', '', 'Authenticator App'))
                ->setRequired()
                ->setAutoFocus()
                ->setAutoComplete();
        } else {
            // Field Definition
            $otpCredentialKeyField = (new PasswordField('otpCredentialKey', 'YubiKey', 'YubiKey', new YubiKey()))->setRequired()->setAutoFocus();
        }

        $FormError = new Container('');
        if ($otpCredentialKey) {
            // App ist immer 6-stellig
            if ($tblAccount->getHasAuthentication(TblIdentification::NAME_AUTHENTICATOR_APP) && strlen($otpCredentialKey) == 6) {
                // Credential correct, OTP correct -> LOGIN
                try {
                    $twoFactorApp = new TwoFactorApp();
                    if ($twoFactorApp->verifyCode($tblAccount->getAuthenticatorAppSecret(), $otpCredentialKey)) {
                        if (session_status() == PHP_SESSION_ACTIVE) {
                            session_regenerate_id();
                        }
                        Account::useService()->createSession($tblAccount, session_id());
                        $View->setTitle(new Ok() . ' Anmelden');
                        $View->setContent(
                            $this->getIdentificationLayout(
                                new Headline('Anmelden', 'Bitte warten...')
                                . new Redirect('/', Redirect::TIMEOUT_SUCCESS)
                            )
                        );
                        return $View;
                    } else {
                        // Error OTP APP invalid
                        $otpCredentialKeyField->setError('');
                        $FormError = new Listing(array(new Danger(new Exclamation() . ' Die eingegebenen Zugangsdaten sind nicht gültig')));
                    }
                } catch (Exception $Exception) {

                    (new DebuggerFactory())->createLogger(new ErrorLogger())->addLog('Authenticator App Error: ' . $Exception->getMessage());
                    (new DebuggerFactory())->createLogger(new FileLogger())->addLog('Authenticator App Error: ' . $Exception->getMessage());

                    // Error OTP APP Error
                    $otpCredentialKeyField->setError('');
                    $FormError = new Listing(array(new Danger(new Exclamation() . ' Die eingegebenen Zugangsdaten sind nicht gültig')));
                }
            } else {
                // Search for matching Token
                $Identifier = $this->getModHex($otpCredentialKey)->getIdentifier();
                $tblToken = Token::useService()->getTokenByIdentifier($Identifier);
                if (
                    $tblToken
                    && $tblAccount->getServiceTblToken()
                    && $tblAccount->getServiceTblToken()->getId() == $tblToken->getId()
                ) {
                    // Credential correct, Token correct -> LOGIN
                    try {
                        if (Token::useService()->isTokenValid($otpCredentialKey)) {
                            if (session_status() == PHP_SESSION_ACTIVE) {
                                session_regenerate_id();
                            }
                            Account::useService()->createSession($tblAccount, session_id());
                            $View->setTitle(new Ok() . ' Anmelden');
                            $View->setContent(
                                $this->getIdentificationLayout(
                                    new Headline('Anmelden', 'Bitte warten...')
                                    . new Redirect('/', Redirect::TIMEOUT_SUCCESS)
                                )
                            );
                            return $View;
                        } else {
                            // Error Token invalid
                            $otpCredentialKeyField->setError('');
                            $FormError = new Listing(array(new Danger(new Exclamation() . ' Die eingegebenen Zugangsdaten sind nicht gültig')));
                        }
                    } catch (Exception $Exception) {

                        (new DebuggerFactory())->createLogger(new ErrorLogger())->addLog('YubiKey-Api Error: ' . $Exception->getMessage());
                        (new DebuggerFactory())->createLogger(new FileLogger())->addLog('YubiKey-Api Error: ' . $Exception->getMessage());

                        // Error Token API Error
                        $otpCredentialKeyField->setError('');
                        $FormError = new Listing(array(new Danger(new Exclamation() . ' Die eingegebenen Zugangsdaten sind nicht gültig')));
                    }
                } else {
                    // Error Token not registered
                    $otpCredentialKeyField->setError('');
                    $FormError = new Listing(array(new Danger(new Exclamation() . ' Die eingegebenen Zugangsdaten sind nicht gültig')));
                }
            }
        }

        // Switch User/Account (Restart Identification Process)
        if (($tblConsumer = $tblAccount->getServiceTblConsumer())) {
            $FormInformation = array(
                $tblConsumer->getAcronym() . ' - ' . $tblConsumer->getName(),
                'Benutzer: ' . $tblAccount->getUsername()
                // . new PullRight(new Small(new Link('Mit einem anderen Benutzer anmelden', new Route(__NAMESPACE__))))
            );
        } else {
            $FormInformation = array(
                'Benutzer: ' . $tblAccount->getUsername()
            );

            // ist der Mandant gelöscht werden System-Accounts auf den REF-Mandanten umgeleitet
            if (($tblConsumer = Consumer::useService()->getConsumerByAcronym('REF'))
                && $tblAccount->getHasAuthentication(TblIdentification::NAME_SYSTEM)
            ) {
                $FormInformation[] = $tblConsumer->getName();
                MyAccount::useService()->updateConsumer($tblAccount, $tblConsumer);
            }
        }

        if (isset($_COOKIE['cookies_available']) || $isCookieAvailable) {
            // Create Form
            $Form = new Form(
                new FormGroup(array(
                        new FormRow(
                            new FormColumn(array(
                                new Headline('Bitte geben Sie Ihre Zugangsdaten ein'),
                                new Ruler(),
                                new Listing($FormInformation),
                                new Listing(array(
                                    new Container($otpCredentialKeyField)
                                )),
                                $FormError
                            ))
                        ),
                        new FormRow(
                            new FormColumn(array(
                                (new Primary('Bestätigen'))
                            ))
                        )
                    )
                )
                , null, new Route(__NAMESPACE__ . '/Token'), array(
                'tblAccount' => $tblAccount,
            ));

            $View->setContent($this->getIdentificationLayout($Form));
        } else {
            // es sind keine Cookies erlaubt -> Login ist nicht möglich
            $layout = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(array(
                    new Headline('Bitte geben Sie Ihre Zugangsdaten ein'),
                    new Ruler(),
                    new Listing($FormInformation),
                    $this->getCookieMessage()
                ))
            ))));
            $View->setContent($this->getIdentificationLayout($layout));
        }

        return $View;
    }

    /**
     * @return DangerMessage
     */
    private function getCookieMessage()
    {
        return new DangerMessage(
            new Title(new Exclamation().' Browsereinstellungen')
            .'Sie scheinen Cookies in Ihrem Browser deaktiviert zu haben.' . '<br/>'
            .'Bitte überprüfen Sie die Einstellungen in Ihrem Browser und versuchen Sie es erneut.'
//            'Ihre Browsereinstellungen lassen keine Cookies zu.' . '<br><br>'
//            . 'Um auf die Schulsoftware zu können, müssen Sie Cookies in Ihrem Browser zulassen.' . '<br><br>'
//            . 'Darum sind Cookies notwendig:' . '<br>'
//            . 'Aus Sicherheitsgründen wird beim Login ein Cookie auf Ihrem Rechner gespeichert. ' . '<br>'
//            . 'So wird sichergestellt, dass nur Sie während einer Sitzung auf die Schulsoftware zugreifen können.' . '<br>'
//            . 'Wenn Sie sich ausloggen oder das Browserfenster schließen, wird das Cookie gelöscht und Ihre Sitzung dadurch ungültig gemacht.'
        );
    }

    /**
     * Stage Layout
     *
     * @param $Content
     * @return Layout
     */
    private function getIdentificationLayout($Content)
    {
        return new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    ''
                    , 2),
                new LayoutColumn(
                    $Content
                    , 8),
                new LayoutColumn(
                    ''
                    , 2),
            )),
        )));
    }

    /**
     * @param int  $tblAccount
     * @param int  $tblIdentification
     * @param int  $doAccept 0|1
     * @param bool $isCookieAvailable
     *
     * @return Stage
     */
    public function frontendIdentificationAgb($tblAccount, $doAccept = 0, $isCookieAvailable = false)
    {

        $View = new Stage(new MoreItems().' Anmelden', '', $this->getIdentificationEnvironment());

        $tblAccount = Account::useService()->getAccountById($tblAccount);

        // Return on Input Error
        if (!$tblAccount || !$tblAccount->getServiceTblConsumer()) {
            // Restart Identification Process
            return $this->frontendIdentificationCredential();
        }

        // es sind keine Cookies erlaubt -> Login ist nicht möglich
        if (!isset($_COOKIE['cookies_available'])) { //  && $doAccept == 0
            // Bypass Cookies
            if(!$isCookieAvailable){
                $FormInformation = array(
                    $tblAccount->getServiceTblConsumer()->getAcronym() . ' - ' . $tblAccount->getServiceTblConsumer()->getName(),
                    'Benutzer: ' . $tblAccount->getUsername()
                );

                $layout = new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(array(
                        new Headline('Bitte geben Sie Ihre Zugangsdaten ein'),
                        new Ruler(),
                        new Listing($FormInformation),
                        $this->getCookieMessage()
                    ))
                ))));

                $View->setContent($this->getIdentificationLayout($layout));

                return $View;
            }
        }

        $Headline = 'Allgemeine Geschäftsbedingungen';

        // IS Accepted?
        // Sanatize Agb Setting
        $tblSetting = Account::useService()->getSettingByAccount($tblAccount, 'AGB');
        if (!$tblSetting) {
            $tblSetting = Account::useService()->setSettingByAccount($tblAccount, 'AGB', TblSetting::VAR_EMPTY_AGB);
        }
        // Check/Set Agb Setting
        if( $tblSetting->getValue() == TblSetting::VAR_ACCEPT_AGB || $doAccept == 1 ) {
            if( $doAccept == 1 ) {
                Account::useService()->setSettingByAccount($tblAccount, 'AGB', TblSetting::VAR_ACCEPT_AGB);
            }
            // Credential correct, Agb accepted -> LOGIN
            Account::useService()->createSession($tblAccount);
            $View->setTitle( new Ok().' Anmelden' );
            $View->setContent(
                $this->getIdentificationLayout(
                    new Headline('Anmelden', 'Bitte warten...')
                    . new Redirect('/', Redirect::TIMEOUT_SUCCESS)
                )
            );
            return $View;
        }

        // NOT Accepted?
        // Check if Parent-Account || Student-Account
        $tblUserAccount = UserAccount::useServiceByConsumer($tblAccount->getServiceTblConsumer())->getUserAccountByAccount($tblAccount);
        if ($tblUserAccount &&
            ($tblUserAccount->getType() == TblUserAccount::VALUE_TYPE_CUSTODY || $tblUserAccount->getType() == TblUserAccount::VALUE_TYPE_STUDENT)) {
            // IS Parent-Account || Student-Account
            if($tblSetting->getValue() == TblSetting::VAR_UPDATE_AGB) {
                $Headline = 'Allgemeine Geschäftsbedingungen - Aktualisierung';
            }
            $View->setDescription( $Headline );
        } else {
            // NOT Parent-Account
            // Credential correct, NO Agb check -> LOGIN
            Account::useService()->createSession($tblAccount);
            $View->setTitle( new Ok().' Anmelden' );
            $View->setContent(
                $this->getIdentificationLayout(
                    new Headline('Anmelden', 'Bitte warten...')
                    . new Redirect('/', Redirect::TIMEOUT_SUCCESS)
                )
            );
            return $View;
        }

        // Switch User/Account (Restart Identification Process)
        $FormInformation = array(
            $tblAccount->getServiceTblConsumer()->getAcronym().' - '.$tblAccount->getServiceTblConsumer()->getName(),
            'Benutzer: ' . $tblAccount->getUsername()
            // . new PullRight(new Small(new Link('Mit einem anderen Benutzer anmelden', new Route(__NAMESPACE__))))
        );
        // Create Form
        $Form = new Layout(
            new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Headline('Bestätigung der Allgemeine Geschäftsbedingungen:'),
                            new Paragraph('Wenn Sie vorstehenden Regelungen einverstanden sind und die elektronische
                            Notenübersicht nutzen möchten, so klicken sie unten auf [Einwilligen].')
                            .new Paragraph('Andernfalls klicken Sie auf [Ablehnen], um keinen Zugang zum elektronischen
                            Notenbuch zu erhalten.'),
                            new Listing($FormInformation)
                        ))
                    ),
                    new LayoutRow(
                        new LayoutColumn(array(
                            new PullLeft( new Success('Einwilligen',new Route(__NAMESPACE__ . '/Agb'), new Enable(), array(
                                'tblAccount' => $tblAccount,
                                'doAccept' => 1,
                                'isCookieAvailable' => $isCookieAvailable
                            )) ),
                            new PullRight( new DangerLink('Ablehnen',new Route(__NAMESPACE__ ), new Disable(), array()) )
                        ))
                    )
                )
            ));

        $View->setContent(
            $this->getIdentificationLayout(
                new Listing(array(
                    new Header(new Bold($Headline)),
                    Agb::useFrontend()->getAgbContent($tblAccount)
                ))
                . $Form
            )
        );

        return $View;
    }

    /**
     * @return Stage
     */
    public function frontendDestroySession()
    {
        $Stage = new Stage(new Off().' Abmelden', '', $this->getIdentificationEnvironment());

//        $tblAccount = Account::useService()->getAccountBySession();
//        if($tblAccount->getId() == 3823 ){
//            $phpSaml = new phpSaml();
//            $phpSaml->getSLO();
//        }
        $Stage->setContent(
            $this->getIdentificationLayout(
                new Headline('Abmelden', 'Bitte warten...').
                Account::useService()->destroySession(
                    new Redirect('/Platform/Gatekeeper/Authentication', Redirect::TIMEOUT_SUCCESS)
                ) . $this->getCleanLocalStorage()
            )
        );

        return $Stage;
    }

    /**
     * Prepare if other sign out will come. now not in use.
     * Route deprecated
     * @return Stage
     */
    public function frontendSLO()
    {

        $Stage = new Stage(new Off().' Abmelden', '', $this->getIdentificationEnvironment());

        $Stage->setContent(
            $this->getIdentificationLayout(
                new Headline('Abmelden', 'Bitte warten...').
                Account::useService()->destroySession(
                    new Redirect('/Platform/Gatekeeper/Authentication', Redirect::TIMEOUT_SUCCESS)
                ) . $this->getCleanLocalStorage()
            )
        );

        return $Stage;
    }
}
