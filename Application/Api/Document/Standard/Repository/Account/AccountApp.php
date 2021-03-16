<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\Account;

use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authentication\TwoFactorApp\TwoFactorApp;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Common\Frontend\Text\Repository\Center;

/**
 * Class AccountApp
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\Account
 */
class AccountApp extends AccountDocument
{
    /**
     * AccountApp constructor.
     * @param TblAccount $tblAccount
     * @param TblPerson $tblPerson
     */
    public function __construct(TblAccount $tblAccount, TblPerson $tblPerson)
    {
        $this->tblAccount = $tblAccount;
        $this->setTblPerson($tblPerson);

        $this->setResponsibilityCompany();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Benutzerkonto-Anschreiben für Authenticator App';
    }

    /**
     *
     * @param array $pageList
     * @param string $Part
     *
     * @return Frame
     */
    public function buildDocument($pageList = array(), $Part = '0')
    {
        $InjectStyle = 'body { margin-left: 1.2cm !important; margin-right: 1.2cm !important; }';

        return (new Frame($InjectStyle))->addDocument((new Document())
            ->addPage($this->buildPage())
        );
    }

    /**
     * @return Page
     */
    public function buildPage()
    {
        $twoFactorApp = new TwoFactorApp();
        $secret = $this->tblAccount ? $this->tblAccount->getAuthenticatorAppSecret() : '';

        return (new Page())
            ->addSlice($this->setHeader())
            ->addSlice($this->setSubject('QR-Code zur Nutzung der Schulsoftware'))
            ->addSlice($this->setSalutation())
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('hiermit erhalten Sie die Zugangsdaten sowie den QR-Code zur 2-Faktor-Authentifizierung
                        mittels Authenticator App am Smartphone für den Live-Zugang und Demo-Zugang der Schulsoftware.')
                    ->styleMarginTop('15px')
                )
            )
            ->addSlice($this->setAccountInformation())
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Für den 2. Schritt benötigen Sie eine Authenticator App (z.B.: FreeOTP Authenticator 
                        von Red Hat Inc.) auf Ihrem Smartphone. Für die erstmalige Einrichtung der App öffnen Sie diese
                        bitte und scannen den folgenden QR-Code ein.')
                    ->styleMarginTop('15px')
                )
                ->addElement((new Element())
//                    ->setContent(new Center('<img src="' . $twoFactorApp->getQRCodeImageAsDataUri($secret, 180) . '">'))
                    ->setContent(new Center($twoFactorApp->getBaconQrCode($secret, 180)))
                    ->styleMarginTop('15px')
                )
                ->addElement((new Element())
                    ->setContent('Die App erzeugt einen 6 stelligen Pin (Einmalpasswort), welcher für eine Zeitbereich 
                        von 30 Sekunden gültig ist. Geben Sie bitte den 6 stelligen Pin innerhalb des Zeitbereichs in 
                        der Schulsoftware in das Feld: "Authenticator App" ein.')
                    ->styleMarginTop('15px')
                )
                ->addElement((new Element\Image('/Common/Style/Resource/Document/login_app.png')))
                ->addElement((new Element())
                    ->setContent('Benötigen Sie Hilfe, wenden Sie sich bitte an den schulinternen Ansprechpartner der 
                        Schulsoftware.')
                    ->styleMarginTop('15px')
                )
            );
    }
}