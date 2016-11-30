<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authentication;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Token;
use SPHERE\Application\Platform\System\Database\Database;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Common\Frontend\Ajax\Emitter\StandardEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\FieldValueReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\InlineReceiver;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\PasswordField;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Hospital;
use SPHERE\Common\Frontend\Icon\Repository\Lock;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\Icon\Repository\Shield;
use SPHERE\Common\Frontend\Icon\Repository\Transfer;
use SPHERE\Common\Frontend\Icon\Repository\YubiKey;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Backward;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Cache\CacheFactory;
use SPHERE\System\Cache\Handler\TwigHandler;
use SPHERE\System\Extension\Extension;

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

        $Stage = new Stage('Willkommen', '');
        $Stage->addButton(new Backward(true));
        $Stage->setMessage(date('d.m.Y - H:i:s'));

//        $P = new Pipeline();
//
//        $RA = new FieldValueReceiver( new TextField('A') );
//        $RB = new FieldValueReceiver( new TextArea('B') );
//        $RC = new FieldValueReceiver( new PasswordField('C') );
//        $RD = new FieldValueReceiver( new TextField('D') );
//        $RDI = new InlineReceiver();
//
//        $EA = new StandardEmitter( new Route('/Api/Test/AjaxTest'), $RA );
//        $EA->setPostPayload(array( 'ABC' => 0 ));
//        $EB = new StandardEmitter( new Route('/Api/Test/AjaxTest'), $RB );
//        $EB->setPostPayload(array( 'ABC' => 1 ));
//        $EC = new StandardEmitter( new Route('/Api/Test/AjaxTest'), $RC );
//        $EC->setPostPayload(array( 'ABC' => 2 ));
//        $ED = new StandardEmitter( new Route('/Api/Test/AjaxTest'), $RD );
//        $ED->setPostPayload(array( 'ABC' => 3 ));
//        $EDI = new StandardEmitter( new Route('/Api/Test/AjaxTest'), $RDI );
//        $EDI->setPostPayload(array( 'ABC' => 3 ));
//
//        $P->addEmitter( $EA );
//        $P->addEmitter( $EB );
//        $P->addEmitter( $EC );
//        $P->addEmitter( $ED );
//        $P->addEmitter( $EDI );
//
//        $PF = new Pipeline();
//        $EF = new StandardEmitter( new Route('/Api/Test/AjaxTest'), $RDI );
//        $PF->addEmitter( $EF );
//        $PF->addEmitter( $EB );
//
//        $Stage->setContent(
//            (new Form(
//                new FormGroup(
//                    new FormRow(
//                        new FormColumn(
//                            new Panel('Form', array(
//                                $RC, $RD
//                            ))
//                        )
//                    )
//                )
//            ,new Primary('Send')))->ajaxPipelineOnSubmit( $PF ).
//
//
//            new TableData(array(
//                array(
//                    'A' => $RA,
//                    'B' => $RB,
//                    'C' => $RC,
//                    'D' => $RD
//                )
//            )).(new Standard('Rinne',''))->ajaxPipelineOnClick( $P ).new Info($RDI)
//        );

        /*

        $R1 = new InlineReceiver();
        $R2 = new FieldValueReceiver((new TextField('EinTextFeld[1]', 'TextFeld', 'TextFeld'))->setRequired());
        $R3 = new FieldValueReceiver((new TextArea('EineTextArea[asd][frt]', 'TextArea', 'TextArea'))->setRequired());

        $E1 = new StandardEmitter(new Route('/Api/Test/AjaxTest'), $R1);
        $E2 = new StandardEmitter(new Route('/Api/Test/AjaxTest'), $R2);
        $E3 = new StandardEmitter(new Route('/Api/Test/AjaxTest'), $R3);

        $E1->setGetPayload(array(':P' => 'XD'));
        $E2->setPostPayload(array('TextArea' => 'XD :)'));
        $E3->setPostPayload(array('TextArea' => '<b>????</b>'));

        $P1 = new Pipeline();
        $P1->addEmitter($E1);

        $P2 = new Pipeline();
        $P2->addEmitter($E2);
        $P2->addEmitter($E3);

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Receiver', array(
                                $R1, $R2, $R3
                            ))
                            , 6),
                        new LayoutColumn(array(
                            new Panel('Receiver 1', $R1),
                            new Panel('Receiver 2', $R2),
                            new Panel('Receiver 3', $R3)
                        ), 6)
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            new Form(
                                new FormGroup(
                                    new FormRow(
                                        new FormColumn(
                                            new Panel( 'Form', array(
                                            $R2, $R3
                                            ))
                                        )
                                    )
                                )
                            , new Primary('Send'))
                        )
                    ),
                    new LayoutRow(
                        new LayoutColumn(array(
                            $this->getCleanLocalStorage(), $P1,
                                (new Standard('Ajax?',''))->ajaxPipelineOnClick( $P2 )
                        ))
                    )
                ))
            )
        );
*/
        return $Stage;
    }

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
     * @param string $CredentialName
     * @param string $CredentialLock
     * @param string $CredentialKey
     *
     * @return Stage
     */
    public function frontendIdentification($CredentialName = null, $CredentialLock = null, $CredentialKey = null)
    {

        if ($CredentialName !== null) {
            Protocol::useService()->createLoginAttemptEntry($CredentialName, $CredentialLock, $CredentialKey);
        }

        $View = new Stage('Anmeldung');

        // Prepare Environment
        switch (strtolower($this->getRequest()->getHost())) {
            case 'www.schulsoftware.schule':
            case 'www.kreda.schule':
                $Environment = new Standard('Zur Demo-Umgebung wechseln', 'http://demo.schulsoftware.schule/', new Transfer(),
                    array(),
                    false);
                break;
            case 'demo.schulsoftware.schule':
            case 'demo.kreda.schule':
                $Environment = new Standard('Zur Live-Umgebung wechseln', 'http://www.schulsoftware.schule/', new Transfer(),
                    array(),
                    false);
                break;
            default:
                $Environment = new Standard('Zur Demo-Umgebung wechseln', 'http://demo.schulsoftware.schule/', new Transfer(),
                    array(),
                    false);
        }

        $View->addButton(
            $Environment
        );

        $View->setMessage('Bitte geben Sie Ihre Benutzerdaten ein');

        // Get Identification-Type (Credential,Token,System)
        $Identifier = $this->getModHex($CredentialKey)->getIdentifier();
        if ($Identifier) {
            $tblToken = Token::useService()->getTokenByIdentifier($Identifier);
            if ($tblToken) {
                if ($tblToken->getServiceTblConsumer()) {
                    $Identification = Account::useService()->getIdentificationByName('Token');
                } else {
                    $Identification = Account::useService()->getIdentificationByName('System');
                }
            } else {
                $Identification = Account::useService()->getIdentificationByName('Credential');
            }
        } else {
            $Identification = Account::useService()->getIdentificationByName('Credential');
            $tblToken = null;
        }

        if (!$Identification) {
            $Protocol = (new Database())->frontendSetup(false, true);

            $Stage = new Stage(new Danger(new Hospital()) . ' Installation', 'Erster Aufruf der Anwendung');
            $Stage->setMessage('Dieser Schritt wird automatisch ausgeführt wenn die Datenbank nicht die notwendigen Einträge aufweist. Üblicherweise beim ersten Aufruf.');
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(array(
                                new Panel('Was ist das?', array(
                                    (new Info(new Shield() . ' Es wird eine automatische Installation der Datenbank und eine Überprüfung der Daten durchgeführt')),
                                ), Panel::PANEL_TYPE_PRIMARY,
                                    new PullRight(strip_tags((new Redirect(self::getRequest()->getPathInfo(), 110)),
                                        '<div><a><script><span>'))
                                ),
                                new Panel('Protokoll', array(
                                    $Protocol
                                ))
                            ))
                        )
                    )
                )
            );
            return $Stage;
        }

        // Create Form
        $Form = new Form(
            new FormGroup(array(
                    new FormRow(
                        new FormColumn(
                            new Panel('Benutzername & Passwort', array(
                                (new TextField('CredentialName', 'Benutzername', 'Benutzername', new Person()))
                                    ->setRequired(),
                                (new PasswordField('CredentialLock', 'Passwort', 'Passwort', new Lock()))
                                    ->setRequired()->setDefaultValue($CredentialLock, true)
                            ), Panel::PANEL_TYPE_INFO)
                        )
                    ),
                    new FormRow(array(
                        new FormColumn(
                            new Panel('Hardware-Schlüssel *', array(
                                new PasswordField('CredentialKey', 'YubiKey', 'YubiKey', new YubiKey())
                            ), Panel::PANEL_TYPE_INFO, new Small('* Wenn zu Ihrem Zugang ein YubiKey gehört geben Sie zuerst oben Ihren Benutzername und Passwort an, stecken Sie dann bitte den YubiKey an, klicken in das Feld YubiKey und drücken anschließend auf den metallischen Sensor am YubiKey. <br/>Das Formular wird daraufhin automatisch abgeschickt.'))
                        )
                    ))
                )
            ), new Primary('Anmelden')
        );

        // Switch Service
        if ($tblToken) {
            $FormService = Account::useService()->createSessionCredentialToken(
                $Form, $CredentialName, $CredentialLock, $CredentialKey, $Identification
            );
        } else {
            $FormService = Account::useService()->createSessionCredential(
                $Form, $CredentialName, $CredentialLock, $Identification
            );
        }

        $View->setContent(
            new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        ''
                        , 3),
                    new LayoutColumn(
                        new Well($FormService)
                        , 6),
                    new LayoutColumn(
                        ''
                        , 3),
                )),
            )))
        );
        return $View;
    }

    /**
     * @return Stage
     */
    public function frontendDestroySession()
    {

        $View = new Stage('Abmelden', 'Bitte warten...');
        $View->setContent(Account::useService()->destroySession(
                new Redirect('/Platform/Gatekeeper/Authentication', Redirect::TIMEOUT_SUCCESS)
            ) . $this->getCleanLocalStorage());
        return $View;
    }
}
