<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\Account;

use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;

/**
 * Class AccountToken
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\Account
 */
class AccountToken extends AccountDocument
{
    private $tokenNumber = '0000 0000';

    /**
     * AccountApp constructor.
     * @param TblAccount $tblAccount
     * @param TblPerson $tblPerson
     */
    public function __construct(TblAccount $tblAccount, TblPerson $tblPerson)
    {
        $this->tblAccount = $tblAccount;
        $this->setTblPerson($tblPerson);

        if (($tblToken = $tblAccount->getServiceTblToken())) {
            $this->tokenNumber = trim(chunk_split(str_pad($tblToken->getSerial(), 8, '0'), 4));
        }

        $this->setResponsibilityCompany();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Benutzerkonto-Anschreiben für Yubikey';
    }

    /**
     *
     * @param array $pageList
     * @param string $Part
     *
     * @return Frame
     */
    public function buildDocument(array $pageList = array(), string $Part = '0'): Frame
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
        return (new Page())
            ->addSlice($this->setHeader())
            ->addSlice($this->setSubject('YubiKey (' . $this->tokenNumber . ') zur Nutzung der Schulsoftware'))
            ->addSlice($this->setSalutation())
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('hiermit erhalten Sie die Zugangsdaten sowie den YubiKey zur Hardware-Authentifizierung
                        für den Live-Zugang und Demo-Zugang der Schulsoftware.')
                    ->styleMarginTop('15px')
                )
            )
            ->addSlice($this->setAccountInformation())
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Für den 2. Schritt der Anmeldung benötigen Sie den YubiKey.')
                    ->styleMarginTop('15px')
                )
//                ->addElement((new Element\Image('/Common/Style/Resource/Document/login_token.png')))
                ->addElement((new Element())
                    ->setContent('Dafür schließen Sie den YubiKey an einen freien USB-Steckplatz Ihres PC‘s an. Bei der
                     erstmaligen Benutzung erfolgt eine kurze automatische Installation.')
                    ->styleMarginTop('15px')
                )
//                ->addSection((new Section())
//                    ->addElementColumn((new Element\Image('/Common/Style/Resource/Document/yubiKey_installation_1.jpg', 'auto', '79px')))
//                    ->addElementColumn((new Element()), '1%')
//                    ->addElementColumn((new Element\Image('/Common/Style/Resource/Document/yubiKey_installation_2.jpg', 'auto', '79px')))
//                )
                ->addElement((new Element())
                    ->setContent('Ist die Installation abgeschlossen, klicken Sie in das Feld „YubiKey“ und betätigen 
                        einmal auf dem YubiKey die Taste zur Generierung eines Einmalkennworts.')
                    ->styleMarginTop('15px')
                )
//                ->addElement((new Element\Image('/Common/Style/Resource/Document/yubiKey_using.jpg', 'auto', '100px')))
                ->addElement((new Element())
                    ->setContent('Benötigen Sie Hilfe, wenden Sie sich bitte an den schulinternen Ansprechpartner der 
                        Schulsoftware.')
                    ->styleMarginTop('15px')
                )
            );
    }
}