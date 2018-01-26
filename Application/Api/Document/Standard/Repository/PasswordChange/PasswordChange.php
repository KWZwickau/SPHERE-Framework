<?php
namespace SPHERE\Application\Api\Document\Standard\Repository\PasswordChange;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Setting\Authorization\Account\Account as AccountAuthorization;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\User\Account\Account;

class PasswordChange extends AbstractDocument
{
    /**
     * PasswordChange constructor.
     *
     * @param array $Data
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    function __construct($Data)
    {

//        echo '<pre>';
//        print_r($Data);
//        echo '</pre>';
//        exit;

        $this->setFieldValue($Data);
    }

    /**
     * @var array
     */
    private $FieldValue = array();


    /**
     * @param array $DataPost
     *
     * @return $this
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    private function setFieldValue($DataPost)
    {

        $tblAccount = false;
        // PersonGender
        $this->FieldValue['PersonName'] = '';
        $this->FieldValue['Street'] = '';
        $this->FieldValue['City'] = '';
        $this->FieldValue['PersonId'] = (isset($DataPost['PersonId']) && $DataPost['PersonId'] != '' ? $DataPost['PersonId'] : false);
        if ($this->FieldValue['PersonId'] && ($tblPerson = Person::useService()->getPersonById($this->FieldValue['PersonId']))) {
            $this->FieldValue['PersonName'] = $tblPerson->getFullName();
            if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
                if (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())) {
                    if (($tblGender = $tblCommonBirthDates->getTblCommonGender())) {
                        $this->FieldValue['Gender'] = $tblGender->getName();
                    }
                }
            }
            $tblAccountList = AccountAuthorization::useService()->getAccountAllByPerson($tblPerson);
            if($tblAccountList){
                $tblAccount = $tblAccountList[0];
            }
            $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
            if($tblAddress){
                $this->FieldValue['Street'] = $tblAddress->getStreetName().' '.$tblAddress->getStreetNumber();
                $tblCity = $tblAddress->getTblCity();
                if($tblCity){
                    $this->FieldValue['City'] = $tblCity->getCode().' '.$tblCity->getDisplayName();
                }
            }
        }

        $this->FieldValue['Password'] = $Password = Account::useService()->generatePassword();
        // remove tblAccount
        if ($tblAccount && $Password) {
            AccountAuthorization::useService()->changePassword($Password, $tblAccount);
            Account::useService()->ChangePassword($tblAccount, $Password);
        };



        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {

        return 'Passwort Änderung per PDF';
    }

    /**
     * @param array $pageList
     *
     * @return Frame
     */
    public function buildDocument($pageList = array())
    {
        return (new Frame())->addDocument((new Document())
            ->addPage($this->buildPage())
        );
    }

    /**
     * @return Slice
     */
    public function getPasswordChange()
    {
        $Slice = new Slice();
        $Slice->addElement((new Element())
            ->setContent('Test')
        );

        return $Slice;
    }

    /**
     * @return Slice
     */
    private function getAddressHead()
    {
        $Slice = new Slice();
        $Slice->addElement((new Element())
            ->setContent('Empfänger')
            ->styleTextSize('8pt')
        );
        $Slice->addElement((new Element())
            ->setContent($this->FieldValue['PersonName'])
        );
        $Slice->addElement((new Element())
            ->setContent($this->FieldValue['Street'])
        );
        $Slice->addElement((new Element())
            ->setContent($this->FieldValue['City'])
        );

        return $Slice;
    }

    /**
     * @return Slice
     */
    private function getContactData()
    {

        $Slice = new Slice();
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Name:')
                ->styleTextSize('8pt')
            , '20%')
            ->addElementColumn((new Element())
                ->setContent('Max Mustermann mit tollem Zeilenumbruch auch bei viel zu langem Namen')
                ->styleTextSize('8pt')
            , '80%')
        );
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Telefon:')
                ->styleTextSize('8pt')
                , '20%')
            ->addElementColumn((new Element())
                ->setContent('')
                ->styleTextSize('8pt')
                , '80%')
        );
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Telefax:')
                ->styleTextSize('8pt')
                , '20%')
            ->addElementColumn((new Element())
                ->setContent('')
                ->styleTextSize('8pt')
                , '80%')
        );
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('E-Mail:')
                ->styleTextSize('8pt')
                , '20%')
            ->addElementColumn((new Element())
                ->setContent('')
                ->styleTextSize('8pt')
                , '80%')
        );
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Internet:')
                ->styleTextSize('8pt')
                ->stylePaddingTop('10px')
                , '20%')
            ->addElementColumn((new Element())
                ->setContent('')
                ->styleTextSize('8pt')
                ->stylePaddingTop('10px')
                , '80%')
        );
        $Slice->addElement((new Element())
                ->setContent('Ort, den Datum')
//                ->styleTextSize('8pt')
                ->stylePaddingTop('10px')
        );

        $Slice->stylePaddingTop('10px');

        return $Slice;
    }

    /**
     * @return Slice
     */
    private function getPasswordFooter()
    {

        $Slice = new Slice();
        $Slice->addSection((new Section())
            ->addSliceColumn((new Slice())
                ->addElement((new Element())
                    ->setContent('Name Schule')
                    ->styleTextSize('7pt')
                )
                ->addElement((new Element())
                    ->setContent('e.V. - gemeinnütziger Trägerverein')
                    ->styleTextSize('7pt')
                )
                ->addElement((new Element())
                    ->setContent('Vorstand §26 BGB:')
                    ->styleTextSize('7pt')
                    ->stylePaddingTop('8px')
                )
                ->addElement((new Element())
                    ->setContent('- Name Vorstand(Vors.)')
                    ->styleTextSize('7pt')
                )
                ->addElement((new Element())
                    ->setContent('- (stv.Vors.)')
                    ->styleTextSize('7pt')
                )
                ->addElement((new Element())
                    ->setContent('- (GF)')
                    ->styleTextSize('7pt')
                )
                , '70%'
            )
            ->addSliceColumn((new Slice())
                ->addElement((new Element())
                    ->setContent('Anschrift Geschäftsstelle / Sekretariat:')
                    ->styleTextSize('7pt')
                )
                ->addElement((new Element())
                    ->setContent('Straße Schule')
                    ->styleTextSize('7pt')
                )
                ->addElement((new Element())
                    ->setContent('Ort Schule')
                    ->styleTextSize('7pt')
                )
                ->addElement((new Element())
                    ->setContent('Steuernummer:')
                    ->styleTextSize('7pt')
                    ->stylePaddingTop('8px')
                )
                ->addElement((new Element())
                    ->setContent('Vereinssitz:')
                    ->styleTextSize('7pt')
                )
                ->addElement((new Element())
                    ->setContent('Vereinsregister:')
                    ->styleTextSize('7pt')
                )
                , '30%'
            )
        );
        return $Slice;
    }

    private function getLetterContent($IsParrent = true, $Height = '500px')
    {

        $Slice = new Slice();
        if ($IsParrent) {
            $Slice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Liebe Eltern,')
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
            )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('ab sofort stellen wir eine elektronische Notenübersicht zur Nutzung bereit. 
                        Dadurch erhalten Sie die Möglichkeit, sämtliche Noten Ihres Kindes einzusehen und über seine 
                        schulische Leistungsentwicklung mit unseren Lehrkräften gezielter zu kommunizieren.')
                        ->stylePaddingTop('12px')
                        ->styleAlignJustify()
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Die Schulstiftung der Ev.-Luth. Landeskirche Sachsens hat eine neue Schulsoftware 
                        entwickeln lassen, die für alle evangelischen Schulen in Sachsen nutzbar ist. Auch wir als 
                        >>Schule<< nutzen diese im Alltag.')
                        ->stylePaddingTop('6px')
                        ->styleAlignJustify()
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Die neue Schulsoftware bietet eine elektronische Notenübersicht für alle Schüler 
                        und deren Sorgeberechtigte, zu deren Nutzung Sie hiermit die notwendigen 
                        Sicherheitsinformationen und Zugangsdaten erhalten.')
                        ->stylePaddingTop('6px')
                        ->styleAlignJustify()
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Die Entwicklung der neuen Softwarelösung erfolgte in enger Abstimmung mit dem 
                        Datenschutzbeauftragten der Ev.-Luth. Landeskirche. Er hat die elektronische Notenübersicht 
                        datenschutzrechtlich überprüft und zur Nutzung freigegeben. Die Kommunikation zwischen Ihrem 
                        Internetbrowser und der Schulsoftware erfolgt ausschließlich über eine verschlüsselte HTTPS-Verbindung ')
                        ->stylePaddingTop('6px')
                        ->styleAlignJustify()
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Der Betrieb der Softwarelösung erfolgt in einem zertifizierten deutschen 
                        Rechenzentrum und wird durch die dortigen Mitarbeiter sowie durch die mit der Entwicklung 
                        beauftragte Firma permanent überwacht und gewartet, um sie vor Cyberangriffen zu schützen. 
                        Da hier personenbezogene vertrauliche Daten verarbeitet werden, gelten vergleichbar hohe 
                        Sicherheitsanforderungen wie beim Onlinebanking. Beispielsweise sind Änderungen an Stammdaten 
                        oder die Eintragung von Benotungen nur für Mitarbeiter der Schule möglich, die über die 
                        entsprechenden Zugriffsberechtigungen verfügen und sich per Zweifaktor-Authentifizierung 
                        (Name, Passwort und Security-Token) anmelden müssen. Für die elektronische Notenübersicht 
                        reicht hingegen die Anmeldung mit Name und Passwort aus')
                        ->stylePaddingTop('6px')
                        ->styleAlignJustify()
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Ihr neues Passwort lautet: '.$this->FieldValue['Password'])
                        ->stylePaddingTop('12px')
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                )
                ->styleHeight($Height);
        } else {
            $Slice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Liebe Schülerinnen und Schüler,')
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
            )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Ab sofort stellen wir eine elektronische Notenübersicht zur Nutzung bereit. Damit 
                        können Eltern und Schüler per Internetzugang Benotungen einsehen und sich über den aktuellen 
                        Leistungsstand informieren.')
                        ->stylePaddingTop('12px')
                        ->styleAlignJustify()
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Die Schulstiftung der Ev.-Luth. Landeskirche Sachsens hat eine neue Schulsoftware 
                        entwickeln lassen, die für alle evangelischen Schulen in Sachsen nutzbar ist. Mit diesem Brief 
                        möchten wir Euch über Eure Zugangsdaten und einige Sicherheitshinweise informieren.')
                        ->stylePaddingTop('6px')
                        ->styleAlignJustify()
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Die Entwicklung der neuen Softwarelösung erfolgte in enger Abstimmung mit dem 
                        Datenschutzbeauftragten der Ev.-Luth. Landeskirche Sachsens. Er hat die elektronische 
                        Notenübersicht datenschutzrechtlich überprüft und zur Nutzung freigegeben. Der Betrieb der 
                        Softwarelösung erfolgt in einem zertifizierten deutschen Rechenzentrum und wird durch die 
                        dortigen Mitarbeiter sowie durch die mit der Entwicklung beauftragte Firma permanent überwacht 
                        und gewartet, um sie vor Cyberangriffen zu schützen.')
                        ->stylePaddingTop('6px')
                        ->styleAlignJustify()
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Die Kommunikation zwischen Eurem Internetbrowser und der Schulsoftware erfolgt 
                        ausschließlich über eine verschlüsselte HTTPs-Verbindung.')
                        ->stylePaddingTop('6px')
                        ->styleAlignJustify()
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Für die Sicherheit Eurer gespeicherten Daten ist es wichtig, ein möglichst gutes 
                        Passwort mit einer Mindestlänge von 8 Zeichen und einer Mischung aus Großbuchstaben, 
                        Kleinbuchstaben, Ziffern und evtl. auch noch Sonderzeichen zu verwenden. <u>Bitte gebt Eure 
                        Zugangsdaten nicht weiter, denn es erhält jeder sorgeberechtigte Elternteil und jeder Schüler 
                        einen eigenen personengebundenen Nutzerzugang.</u>')
                        ->stylePaddingTop('6px')
                        ->styleAlignJustify()
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Ihr neues Passwort lautet: '.$this->FieldValue['Password'])
                        ->stylePaddingTop('12px')
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                )
                ->styleHeight($Height);
        }
        return $Slice;
    }

    /**
     * @return Page
     */
    public function buildPage()
    {
        return (new Page())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '50%'
                    )
                    ->addElementColumn(
                        $this->getPicturePasswordChange()
                            ->styleAlignCenter()
                        , '50%')
                )
                ->styleHeight('120px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                    ->addSliceColumn(
                        $this->getAddressHead()
                    , '52%')
                    ->addSliceColumn(
                        $this->getContactData()
                    , '40%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                )
                ->styleHeight('160px')
//                ->styleBorderAll()
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Neue Schulsoftware und neue elektronische Notenübersicht')
                        ->styleTextBold()
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Sicherheitsinformationen und Zugangsdaten')
                        ->styleTextBold()
                        ->stylePaddingTop('5px')
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                )
//                ->styleBorderAll()
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('&nbsp;')
                    ->styleHeight('40px')
                )
            )
            ->addSlice($this->getLetterContent())
            ->addSlice($this->getEmptyHeight('40px'))
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                    ->addSliceColumn(
                        $this->getPasswordFooter()
                        , '92%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                )
//                ->styleBorderAll()
            );
    }

    /**
     * @param string $with
     *
     * @return Element|Element\Image
     */
    protected function getPicturePasswordChange($with = 'auto')
    {

        $picturePath = $this->getPasswordChangeUsedPicture();
        if ($picturePath != '') {
            $height = $this->getPasswordChangePictureHeight();
            $column = (new Element\Image($picturePath, $with, $height));
        } else {
            $column = (new Element())
                ->setContent('&nbsp;');
        }
        return $column;
    }

    /**
     * @return string
     */
    private function getPasswordChangeUsedPicture()
    {
        if (($tblSetting = Consumer::useService()->getSetting(
            'Api', 'Document', 'Standard', 'PasswordChange_PictureAddress'))
        ) {
            return (string)$tblSetting->getValue();
        }
        return '';
    }

    /**
     * @return string
     */
    private function getPasswordChangePictureHeight()
    {

        $value = '';

        if (($tblSetting = Consumer::useService()->getSetting(
            'Api', 'Document', 'Standard', 'PasswordChange_PictureHeight'))
        ) {
            $value = (string)$tblSetting->getValue();
        }

        return $value ? $value : '120px';
    }
    private function getEmptyHeight($Height = '10px')
    {
        $Slice = new Slice();
        $Slice->addElement((new Element())
            ->setContent('&nbsp;')
            ->styleHeight($Height)
        );
        return $Slice;
    }
}