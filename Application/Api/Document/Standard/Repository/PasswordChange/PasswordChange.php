<?php
namespace SPHERE\Application\Api\Document\Standard\Repository\PasswordChange;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Application\Setting\Authorization\Account\Account as AccountAuthorization;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Application\Setting\User\Account\Account;

class PasswordChange extends AbstractDocument
{

    const BLOCK_SPACE = '10px';

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
        // Common
        $this->FieldValue['PersonName'] = '';
        $this->FieldValue['UserAccount'] = '';
        $this->FieldValue['Street'] = '';
        $this->FieldValue['District'] = '';
        $this->FieldValue['City'] = '';
        // Text choose decision
        $this->FieldValue['IsParent'] = (isset($DataPost['IsParent']) ? $DataPost['IsParent'] : false);
        // School
        $this->FieldValue['CompanyDisplay'] = '';
        $this->FieldValue['CompanyStreet'] = '';
        $this->FieldValue['CompanyDistrict'] = '';
        $this->FieldValue['CompanyCity'] = '';
        // Contact
        $this->FieldValue['ContactPerson'] = (isset($DataPost['ContactPerson']) && $DataPost['ContactPerson'] != '' ? $DataPost['ContactPerson'] : '&nbsp;');
        $this->FieldValue['Phone'] = (isset($DataPost['Phone']) && $DataPost['Phone'] != '' ? $DataPost['Phone'] : '&nbsp;');
        $this->FieldValue['Fax'] = (isset($DataPost['Fax']) && $DataPost['Fax'] != '' ? $DataPost['Fax'] : '&nbsp;');
        $this->FieldValue['Mail'] = (isset($DataPost['Mail']) && $DataPost['Mail'] != '' ? $DataPost['Mail'] : '&nbsp;');
        $this->FieldValue['Web'] = (isset($DataPost['Web']) && $DataPost['Web'] != '' ? $DataPost['Web'] : '&nbsp;');
        //Signer
        $this->FieldValue['SignerName'] = (isset($DataPost['SignerName']) && $DataPost['SignerName'] != '' ? $DataPost['SignerName'] : '&nbsp;');
        $this->FieldValue['SignerType'] = (isset($DataPost['SignerType']) && $DataPost['SignerType'] != '' ? $DataPost['SignerType'] : '&nbsp;');
        $this->FieldValue['Place'] = (isset($DataPost['Place']) && $DataPost['Place'] != '' ? $DataPost['Place'].', den ' : '');
        $this->FieldValue['Date'] = (isset($DataPost['Date']) && $DataPost['Date'] != '' ? $DataPost['Date'] : '&nbsp;');

        //Account
        $UserAccountId = (isset($DataPost['UserAccountId']) && $DataPost['UserAccountId'] != '' ? $DataPost['UserAccountId'] : false);
        if($UserAccountId){
            $tblUserAccount = Account::useService()->getUserAccountById($UserAccountId);
            $tblAccount = $tblUserAccount->getServiceTblAccount();
            if($tblAccount){
                $this->FieldValue['UserAccount'] = $tblAccount->getUsername();
            }
        }

        // Student/Custody
        $this->FieldValue['PersonId'] = (isset($DataPost['PersonId']) && $DataPost['PersonId'] != '' ? $DataPost['PersonId'] : false);
        if ($this->FieldValue['PersonId'] && ($tblPerson = Person::useService()->getPersonById($this->FieldValue['PersonId']))) {

            if(!isset($DataPost['CompanyId'])){
                if($this->FieldValue['IsParent']){
                    $tblRelationshipType = Relationship::useService()->getTypeByName( TblType::IDENTIFIER_GUARDIAN );
                    if(($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblRelationshipType))){
                        foreach($tblRelationshipList as $tblRelationship){  //ToDO Mehrer Schüler auswahl nach "höherer Bildungsgang"
                            if(($tblPersonStudent = $tblRelationship->getServiceTblPersonTo())){
                                if(($tblDivision = Student::useService()->getCurrentDivisionByPerson($tblPersonStudent))){
                                    if(($tblSchoolType = Type::useService()->getTypeByName($tblDivision->getTypeName()))){
                                        if(($tblSchoolCompany = School::useService()->getSchoolByType($tblSchoolType))){
                                            $tblCompany = $tblSchoolCompany->getServiceTblCompany();
                                            $DataPost['tblCompany'] = $tblCompany;
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    if(($tblDivision = Student::useService()->getCurrentDivisionByPerson($tblPerson))){
                        if(($tblSchoolType = Type::useService()->getTypeByName($tblDivision->getTypeName()))){
                            if(($tblSchoolCompany = School::useService()->getSchoolByType($tblSchoolType))){
                                $tblCompany = $tblSchoolCompany->getServiceTblCompany();
                                $DataPost['tblCompany'] = $tblCompany;
                            }
                        }
                    }
                }
            } else {
                if(($tblCompany = Company::useService()->getCompanyById($DataPost['CompanyId']))){
                    $DataPost['tblCompany'] = $tblCompany;
                }
            }

            $this->FieldValue['PersonName'] = $tblPerson->getFullName();
            $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
            if($tblAddress){
                $this->FieldValue['Street'] = $tblAddress->getStreetName().' '.$tblAddress->getStreetNumber();
                $tblCity = $tblAddress->getTblCity();
                if($tblCity){
                    $this->FieldValue['District'] = $tblCity->getDistrict();
                    $this->FieldValue['City'] = $tblCity->getCode().' '.$tblCity->getName();
                }
            }
        }

        //School information
        /** @var TblCompany $tblCompany */
        if(isset($DataPost['tblCompany'])
            && ($tblCompany = $DataPost['tblCompany'])){
            $this->FieldValue['CompanyDisplay'] = $tblCompany->getName().
                ($tblCompany->getExtendedName() ? '</br>'.$tblCompany->getExtendedName() : '');
            $tblAddress = Address::useService()->getAddressByCompany($tblCompany);
            if($tblAddress){
                $this->FieldValue['CompanyStreet'] = $tblAddress->getStreetName().' '.$tblAddress->getStreetNumber();
                $tblCity = $tblAddress->getTblCity();
                if($tblCity){
//                    if(!isset($this->FieldValue['Place'])){
//                        $this->FieldValue['Place'] = $tblCity->getName().', den ';
//                    }
                    $this->FieldValue['CompanyDistrict'] = $tblCity->getDistrict();
                    $this->FieldValue['CompanyCity'] = $tblCity->getName().' '.$tblCity->getCode();
                }
            }
        }

        //generate new Password
        $this->FieldValue['Password'] = $Password = Account::useService()->generatePassword();
        // remove tblAccount
        if ($tblAccount && $Password) {
            AccountAuthorization::useService()->changePassword($Password, $tblAccount);
            Account::useService()->changePassword($tblAccount, $Password);
        };



        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {

        return 'PasswortÄnderungPDF';
    }

    /**
     * @param array $pageList
     *
     * @return Frame
     */
    public function buildDocument($pageList = array())
    {
        return (new Frame())->addDocument((new Document())
            ->addPage($this->buildPageOne())
            ->addPage($this->buildPageTwo())
        );
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
        if($this->FieldValue['District']){
            $Slice->addElement((new Element())
                ->setContent($this->FieldValue['District'])
            );
        }
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
                ->setContent($this->FieldValue['ContactPerson'])
                ->styleTextSize('8pt')
            , '80%')
        );
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Telefon:')
                ->styleTextSize('8pt')
                , '20%')
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['Phone'])
                ->styleTextSize('8pt')
                , '80%')
        );
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Telefax:')
                ->styleTextSize('8pt')
                , '20%')
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['Fax'])
                ->styleTextSize('8pt')
                , '80%')
        );
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('E-Mail:')
                ->styleTextSize('8pt')
                , '20%')
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['Mail'])
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
                ->setContent($this->FieldValue['Web'])
                ->styleTextSize('8pt')
                ->stylePaddingTop('10px')
                , '80%')
        );
        $Slice->addElement((new Element())
                ->setContent($this->FieldValue['Place'].$this->FieldValue['Date'])
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
                    ->setContent($this->FieldValue['CompanyDisplay'])
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
                    ->setContent($this->FieldValue['CompanyDistrict'])
                    ->styleTextSize('7pt')
                )
                ->addElement((new Element())
                    ->setContent($this->FieldValue['CompanyStreet'])
                    ->styleTextSize('7pt')
                )
                ->addElement((new Element())
                    ->setContent($this->FieldValue['CompanyCity'])
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

    private function getFirstLetterContent($Height = '500px')
    {

        $Slice = new Slice();
        if ($this->FieldValue['IsParent']) {
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
                        ->stylePaddingTop(self::BLOCK_SPACE)
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
                        ->stylePaddingTop(self::BLOCK_SPACE)
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
                        ->stylePaddingTop(self::BLOCK_SPACE)
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
                        ->stylePaddingTop(self::BLOCK_SPACE)
                        ->styleAlignJustify()
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
                        ->stylePaddingTop(self::BLOCK_SPACE)
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
                        ->stylePaddingTop(self::BLOCK_SPACE)
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
                        ->stylePaddingTop(self::BLOCK_SPACE)
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
                        ->stylePaddingTop(self::BLOCK_SPACE)
                        ->styleAlignJustify()
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
    private function buildPageOne()
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
            ->addSlice($this->getFirstLetterContent())
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

    private function getSecondLetterContent()
    {

        $Slice = new Slice();
        if ($this->FieldValue['IsParent']) {
            $Slice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Für die Sicherheit Ihrer Daten ist es allerdings wichtig, ein möglichst gutes Passwort 
                    mit einer Mindestlänge von 8 Zeichen und einer Mischung aus Großbuchstaben, Kleinbuchstaben, Ziffern 
                    und evtl. auch noch Sonderzeichen zu verwenden. <u>Bitte geben Sie Ihre Zugangsdaten nicht weiter, es 
                    erhält jeder Sorgeberechtigte und jeder Schüler einen eigenen personengebundenen Nutzerzugang.</u>')
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
                    ->setContent('Für den Zugriff auf die elektronische Notenübersicht verwenden Sie bitte nachfolgende
                     Zugangsdaten:')
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
                    ->setContent('Adresse: https://schulsoftware.schule')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    , '45%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Benutzername:')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    ->stylePaddingRight('25px')
                    ->styleAlignRight()
                    , '17%'
                )
                ->addElementColumn((new Element())
                    ->setContent($this->FieldValue['UserAccount'])
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    ->stylePaddingLeft('10px')
                    , '30%'
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
                        ->setContent('Für das erstmalige Login verwenden Sie bitte folgendes Passwort:')
                        ->stylePaddingTop()
                        , '62%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent($this->FieldValue['Password'])
                        ->stylePaddingTop()
                        ->stylePaddingLeft('10px')
                        , '30%'
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
                    ->setContent('Lesen Sie sich die Datenschutzbestimmungen und Nutzungsbedingungen genau durch. Wenn 
                    Sie einverstanden sind und die elektronische Notenübersicht nutzen möchten, so vergeben Sie bitte 
                    Ihr zukünftiges Passwort für den Zugang und bestätigen Sie Ihre Eingaben.')
                    ->stylePaddingTop(self::BLOCK_SPACE)
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
                    ->setContent('Notieren Sie sich bitte sofort Ihr vergebenes Passwort:')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    , '49%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    ->styleBorderBottom()
                    , '43%'
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
                    ->setContent('und <b>bewahren Sie bitte den Brief an sicherer Stelle und leicht zu finden auf</b>, 
                    damit ihre Zugangsdaten verfügbar bleiben! Sie ersparen uns damit unnötige Arbeit, denn das 
                    Zurücksetzen vergessener Passwörter und die Zusendung neuer Passwortbriefe verursachen nicht 
                    unerhebliche Aufwände und Kosten.')
                    ->stylePaddingTop(self::BLOCK_SPACE)
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
                    ->setContent('Nach Ihrer Bestätigung sollte Ihnen die Notenübersicht für alle Ihre Kinder an unserer 
                    Schule angezeigt werden. Sofern Sie sich gegen die Nutzung der elektronischen Notenübersicht 
                    entscheiden, bleibt Ihr Zugang deaktiviert. Falls Sie noch Rückfragen oder Probleme mit der 
                    Anwendung haben, so können Sie uns gerne kontaktieren.')
                    ->stylePaddingTop(self::BLOCK_SPACE)
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
                    ->setContent('Wir haben vor, unser Serviceangebot schrittweise zu erweitern, sofern das von Ihnen 
                    gewünscht ist. Bitte teilen Sie uns Ihre Anregungen und Verbesserungsvorschläge dazu mit!')
                    ->stylePaddingTop('12px')
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
                    ->setContent('Mit freundlichen Grüßen')
                    ->stylePaddingTop('12px')
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
                    ->setContent($this->FieldValue['SignerName'])
                    ->stylePaddingTop('35px')
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
                    ->setContent($this->FieldValue['SignerType'])
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
            );
        } else {
            $Slice
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Verwendet bitte für den Zugriff auf die elektronische Notenübersicht folgende Zugangsdaten:')
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
                        ->setContent('Adresse: https://schulsoftware.schule')
                        ->stylePaddingTop(self::BLOCK_SPACE)
                        , '45%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Benutzername:')
                        ->stylePaddingTop(self::BLOCK_SPACE)
                        ->stylePaddingRight('25px')
                        ->styleAlignRight()
                        , '17%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent($this->FieldValue['UserAccount'])
                        ->stylePaddingTop(self::BLOCK_SPACE)
                        ->stylePaddingLeft('10px')
                        , '30%'
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
                        ->setContent('Passwort (für erstmaliges Login):')
                        ->stylePaddingTop()
                        ->styleAlignRight()
                        , '62%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent($this->FieldValue['Password'])
                        ->stylePaddingTop()
                        ->stylePaddingLeft('10px')
                        , '30%'
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
                        ->setContent('Lies Dir bitte die Datenschutzbestimmungen und Nutzungsbedingungen genau durch. 
                        Wenn Du einverstanden bist und die elektronische Notenübersicht nutzen möchtest, so gib Dein 
                        zukünftiges Passwort für den Zugang ein und bestätige Deine Eingaben.')
                        ->stylePaddingTop(self::BLOCK_SPACE)
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
                        ->setContent('Notieren Dir bitte sofort das vergebene Passwort:')
                        ->stylePaddingTop(self::BLOCK_SPACE)
                        , '49%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->stylePaddingTop(self::BLOCK_SPACE)
                        ->styleBorderBottom()
                        , '43%'
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
                        ->setContent('und <b>bewahre den Brief an sicherer Stelle und leicht zu finden auf</b>, 
                    damit Deine Zugangsdaten verfügbar bleiben! Das Zurücksetzen vergessener Passwörter und die 
                    Zusendung neuer Passwortbriefe verursachen nicht unerhebliche Aufwände und Kosten für die Schule.')
                        ->stylePaddingTop(self::BLOCK_SPACE)
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
                        ->setContent('Nach Deiner Bestätigung wird eine Startseite mit dem Verweis auf die Notenübersicht 
                        angezeigt. Alternativ kann man auch die Menüleiste nutzen. Falls Du Dich gegen die Nutzung der 
                        elektronischen Notenübersicht entscheidest, bleibt Dein Zugang deaktiviert. Falls Du Rückfragen 
                        oder Probleme mit der Anwendung hast, so wende Dich bitte an unser Sekretariat.')
                        ->stylePaddingTop(self::BLOCK_SPACE)
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
                        ->setContent('Wir haben vor, unser Serviceangebot im Internet schrittweise zu erweitern, sofern 
                        das von Euch gewünscht ist. Anregungen und Verbesserungsvorschläge dazu nimmt unser Sekretariat 
                        gerne entgegen! ')
                        ->stylePaddingTop('12px')
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
                        ->setContent('Mit freundlichen Grüßen')
                        ->stylePaddingTop('12px')
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
                        ->setContent($this->FieldValue['SignerName'])
                        ->stylePaddingTop('35px')
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
                        ->setContent($this->FieldValue['SignerType'])
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                );
        }
        return $Slice;
    }

    /**
     * @return Page
     */
    private function buildPageTwo()
    {

        return (new Page())
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('&nbsp;')
                    ->styleHeight('250px')
                )
            )
            ->addSlice($this->getSecondLetterContent());
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