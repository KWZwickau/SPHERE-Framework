<?php
namespace SPHERE\Application\Api\Document\Standard\Repository\MultiPassword;

use DateTime;
use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonGender;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account as AccountGatekeeper;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer as GatekeeperConsumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\User\Account\Account;
use SPHERE\Common\Frontend\Layout\Repository\Container;

class MultiPassword extends AbstractDocument
{

    const BLOCK_SPACE = '20px';
    const PLACE_HOLDER = '#BBBB00';
    const BORDER = '4%';

    /**
     * @var array
     */
    private $pageList = array();

    /**
     * MultiPassword constructor.
     *
     * @param $Data
     */
    function __construct($Data)
    {

        $this->setFieldValue($Data);
    }

    /**
     * @return array
     */
    public function getPageList()
    {
        return $this->pageList;
    }

    /**
     * @var array
     */
    private $FieldValue = array();

    /**
     * @param array $DataPost
     *
     * @return $this
     */
    private function setFieldValue($DataPost)
    {

        // Text choose decision
        //
        $this->FieldValue['GroupByTime'] = (isset($DataPost['GroupByTime']) ? $DataPost['GroupByTime'] : false);
        $this->FieldValue['GroupByCount'] = (isset($DataPost['GroupByCount']) ? $DataPost['GroupByCount'] : false);

//        $this->FieldValue['Gender'] = false;
//        $this->FieldValue['GenderC'] = array();
        $this->FieldValue['UserAccount'] = '';
//        $this->FieldValue['Street'] = '';
//        $this->FieldValue['District'] = '';
//        $this->FieldValue['City'] = '';
//        $this->FieldValue['IsParent'] = (isset($DataPost['IsParent']) ? $DataPost['IsParent'] : false);
        // School
        $this->FieldValue['CompanyName'] = (isset($DataPost['CompanyName']) && $DataPost['CompanyName'] != '' ? $DataPost['CompanyName'] : '&nbsp;');
        $this->FieldValue['CompanyExtendedName'] = (isset($DataPost['CompanyExtendedName']) && $DataPost['CompanyExtendedName'] != '' ? $DataPost['CompanyExtendedName'] : '&nbsp;');
        $this->FieldValue['CompanyStreet'] = (isset($DataPost['CompanyStreet']) && $DataPost['CompanyStreet'] != '' ? $DataPost['CompanyStreet'] : '&nbsp;');
        $this->FieldValue['CompanyDistrict'] = (isset($DataPost['CompanyDistrict']) && $DataPost['CompanyDistrict'] != '' ? $DataPost['CompanyDistrict'] : '&nbsp;');
        $this->FieldValue['CompanyCity'] = (isset($DataPost['CompanyCity']) && $DataPost['CompanyCity'] != '' ? $DataPost['CompanyCity'] : '&nbsp;');
        // Contact
        $this->FieldValue['Phone'] = (isset($DataPost['Phone']) && $DataPost['Phone'] != '' ? $DataPost['Phone'] : '&nbsp;');
        $this->FieldValue['Fax'] = (isset($DataPost['Fax']) && $DataPost['Fax'] != '' ? $DataPost['Fax'] : '&nbsp;');
        $this->FieldValue['Mail'] = (isset($DataPost['Mail']) && $DataPost['Mail'] != '' ? $DataPost['Mail'] : '&nbsp;');
        $this->FieldValue['Web'] = (isset($DataPost['Web']) && $DataPost['Web'] != '' ? $DataPost['Web'] : '&nbsp;');
        //Signer
        $this->FieldValue['Place'] = (isset($DataPost['Place']) && $DataPost['Place'] != '' ? $DataPost['Place'].', den ' : '');
        $this->FieldValue['Date'] = (isset($DataPost['Date']) && $DataPost['Date'] != '' ? $DataPost['Date'] : '&nbsp;');

        if($this->FieldValue['GroupByTime'] && $this->FieldValue['GroupByCount']){
            if(($tblUserAccountList = Account::useService()->getUserAccountByTimeAndCount(
                new DateTime($this->FieldValue['GroupByTime']), $this->FieldValue['GroupByCount']))){
                foreach($tblUserAccountList as $tblUserAccount){
                    /** @var TblAccount $tblAccount */
                    if(($tblAccount = $tblUserAccount->getServiceTblAccount())){
                        if(!isset($this->FieldValue['IsParent'])){
                            $this->FieldValue['IsParent'] = ($tblUserAccount->getType() == 'CUSTODY' ? true : false);
                        }

                        // default value
                        $this->FieldValue['PersonSalutation'][$tblAccount->getId()] = '';
                        $this->FieldValue['PersonFirstLastName'][$tblAccount->getId()] = '';
                        $this->FieldValue['PersonTitle'][$tblAccount->getId()] = '';
                        $this->FieldValue['PersonLastName'][$tblAccount->getId()] = '';
                        $this->FieldValue['PersonName'][$tblAccount->getId()] = '';
                        $this->FieldValue['Street'][$tblAccount->getId()] = '';
                        $this->FieldValue['District'][$tblAccount->getId()] = '';
                        $this->FieldValue['City'][$tblAccount->getId()] = '';
                        $this->FieldValue['GenderC'][$tblAccount->getId()] = false;
                        $this->FieldValue['tblAccountList'][] = $tblAccount->getId();
                        $this->FieldValue['UserAccountNameList'][$tblAccount->getId()] = $tblAccount->getUsername();
                        $this->FieldValue['Password'][$tblAccount->getId()] = $tblUserAccount->getUserPassword();

                        $this->FieldValue['ChildList'][$tblAccount->getId()] = '';

                        // School choose
                        if(($tblPerson = $tblUserAccount->getServiceTblPerson())){

                            $this->FieldValue['PersonName'][$tblAccount->getId()] = $tblPerson->getFullName();
                            //Address
                            $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                            if($tblAddress){
                                $this->FieldValue['Street'][$tblAccount->getId()] = $tblAddress->getStreetName().' '.$tblAddress->getStreetNumber();
                                $tblCity = $tblAddress->getTblCity();
                                if($tblCity){
                                    $this->FieldValue['District'][$tblAccount->getId()] = $tblCity->getDistrict();
                                    $this->FieldValue['City'][$tblAccount->getId()] = $tblCity->getCode().' '.$tblCity->getName();
                                }
                            }
                            $tblToPersonType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_GUARDIAN);
                            if(($tblToPersonList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblToPersonType))){
                                $PersonNameList = array();
                                foreach($tblToPersonList as $tblToPerson){
                                    if(($tblPersonTo = $tblToPerson->getServiceTblPersonTo())){
                                        $PersonNameList[] = $tblPersonTo->getLastFirstName();
                                    }
                                }

                                $this->FieldValue['ChildList'][$tblAccount->getId()] = implode('<br/>', $PersonNameList);
                            }

                        }

                        $this->pageList[] = $this->buildPageOne($tblAccount->getId());
                        $this->pageList[] = $this->buildPageTwo($tblAccount->getId());
                    }
                }
                // set flag IsExport
                $isExportFlag = true;
                // IsExport only by non System Accounts (Support) soll keine einträge
                if (($tblAccount = AccountGatekeeper::useService()->getAccountBySession())
                    && (AccountGatekeeper::useService()->getHasAuthenticationByAccountAndIdentificationName($tblAccount, TblIdentification::NAME_SYSTEM))
                ) {
                    $isExportFlag = false;
                }
                if($isExportFlag){
                    Account::useService()->updateDownloadBulk($tblUserAccountList);
                }
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {

        $UserAccountList = Account::useService()->getUserAccountByTime(new DateTime($this->FieldValue['GroupByTime']));
        if($UserAccountList){
            if($UserAccountList[0]->getType() == 'CUSTODY'){
                $ListIdentifierString = 'Sorgeberechtigte';
            } else {
                $ListIdentifierString = 'Schüler';
            }
        } else {
            $ListIdentifierString = $this->FieldValue['tblAccountList'][0];
        }
        $Time = new DateTime();
        $Time = $Time->format('d_m_Y-h_i_s');
        return 'Zugang-Schulsoftware-'.$ListIdentifierString.'-Liste_'.$this->FieldValue['GroupByCount'].'_'.$Time;
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

        // wird jetzt nicht mehr verwendet
        $Document = new Document();
        if(!empty($this->FieldValue['tblAccountList'])){
            /** @var TblAccount $tblAccount */
            foreach($this->FieldValue['tblAccountList'] as $AccountId){
                $Document->addPage($this->buildPageOne($AccountId));
                $Document->addPage($this->buildPageTwo($AccountId));
            }
        }

        return (new Frame())->addDocument($Document);
    }

    /**
     * @param int $AccountId
     *
     * @return Slice
     */
    private function getAddressHead($AccountId)
    {
        $Slice = new Slice();
        if($this->FieldValue['CompanyName'] === '&nbsp;'){
            $Slice->addElement((new Element())
                ->setContent('>> Name der Schule <<')
                ->styleTextColor($this::PLACE_HOLDER)
                ->styleTextSize('8pt')
            );
        } else {
            $Slice->addElement((new Element())
                ->setContent($this->FieldValue['CompanyName'])
                ->styleTextSize('8pt')
            );
        }
        if($this->FieldValue['CompanyExtendedName'] != '&nbsp;'){
            $Slice->addElement((new Element())
                ->setContent($this->FieldValue['CompanyExtendedName'])
                ->styleTextSize('8pt')
            );
        }
        if($this->FieldValue['CompanyDistrict'] === '&nbsp;'
            && $this->FieldValue['CompanyStreet'] === '&nbsp;'
            && $this->FieldValue['CompanyCity'] === '&nbsp;') {
            $Slice->addElement((new Element())
                ->setContent('>> Adresse <<')
                ->styleTextColor($this::PLACE_HOLDER)
                ->styleTextSize('8pt')
                ->stylePaddingBottom('15px')
            );
        } else {
            $Slice->addElement((new Element())
                ->setContent(
                    ($this->FieldValue['CompanyDistrict'] != '&nbsp;'
                        ? $this->FieldValue['CompanyDistrict'].' '
                        : '')
                    .$this->FieldValue['CompanyStreet'].' '
                    .$this->FieldValue['CompanyCity'])
                ->styleTextSize('8pt')
                ->stylePaddingBottom('15px')
            );
        }

        if($this->FieldValue['PersonName'][$AccountId] === '&nbsp;'){
            $Slice->addElement((new Element())
                ->setContent('>> Name der Person <<')
                ->styleTextColor($this::PLACE_HOLDER)
                ->styleTextSize('8pt')
            );
        } else {
            $Slice->addElement((new Element())
                ->setContent($this->FieldValue['PersonName'][$AccountId])
            );
        }
        if($this->FieldValue['District']){
            $Slice->addElement((new Element())
                ->setContent($this->FieldValue['District'][$AccountId])
            );
        }

        if(!$this->FieldValue['Street'][$AccountId]){
            $Slice->addElement((new Element())
                ->setContent('>> Straße <<')
                ->styleTextColor($this::PLACE_HOLDER)
                ->styleTextSize('8pt')
            );
        } else {
            $Slice->addElement((new Element())
                ->setContent($this->FieldValue['Street'][$AccountId])
            );
        }

        if(!$this->FieldValue['City'][$AccountId]){
            $Slice->addElement((new Element())
                ->setContent('>> Stadt <<')
                ->styleTextColor($this::PLACE_HOLDER)
                ->styleTextSize('8pt')
            );
        } else {
            $Slice->addElement((new Element())
                ->setContent($this->FieldValue['City'][$AccountId])
            );
        }

        return $Slice;
    }

    /**
     * @return Slice
     */
    private function getContactData()
    {

        $Slice = new Slice();
        if($this->FieldValue['Phone'] === '&nbsp;'){
            $Slice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Telefon:')
                    ->styleTextSize('8pt')
                    , '20%')
                ->addElementColumn((new Element())
                    ->setContent('>> Telefonnummer <<')
                    ->styleTextColor($this::PLACE_HOLDER)
                    ->styleTextSize('8pt')
                    , '80%')
            );
        } else {
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
        }

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
        if($this->FieldValue['Mail'] === '&nbsp;'){
            $Slice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('E-Mail:')
                    ->styleTextSize('8pt')
                    , '20%')
                ->addElementColumn((new Element())
                    ->setContent('>> E-Mail <<')
                    ->styleTextColor($this::PLACE_HOLDER)
                    ->styleTextSize('8pt')
                    , '80%')
            );
        } else {
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
        }
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
        if($this->FieldValue['Date'] === '&nbsp;'){
            $Slice->addElement((new Element())
                ->setContent('>> Datum <<')
                ->styleTextColor($this::PLACE_HOLDER)
                ->stylePaddingTop('10px')
            );
        } else {
            $Slice->addElement((new Element())
                ->setContent($this->FieldValue['Place'].$this->FieldValue['Date'])
                ->stylePaddingTop('10px')
            );
        }

        $Slice->stylePaddingTop('10px');

        return $Slice;
    }

    /**
     * @param $AccountId
     *
     * @return Slice
     */
    private function getFirstLetterContent($AccountId = '')
    {
        $Height = '500px';
        if($this->FieldValue['IsParent']){
            $FirstLine = 'Liebe Eltern,';
        } else {
            $FirstLine = 'Liebe Schülerinnen und Schüler,';
        }

        if(($tblAccount = \SPHERE\Application\Setting\Authorization\Account\Account::useService()->getAccountById($AccountId))){
            if(($tblPersonList = \SPHERE\Application\Setting\Authorization\Account\Account::useService()->getPersonAllByAccount($tblAccount))){
                /** @var TblPerson $tblPerson */
                $tblPerson = current($tblPersonList);
                if($tblPerson->getSalutation() == TblSalutation::VALUE_MAN || $tblPerson->getSalutation() == TblSalutation::VALUE_STUDENT){
                    $FirstLine = 'Lieber '.$tblPerson->getFullName().',';
                } elseif($tblPerson->getSalutation() == TblSalutation::VALUE_WOMAN) {
                    $FirstLine = 'Liebe '.$tblPerson->getFullName().',';
                }else {
                    if($tblPerson->getGender() && $tblPerson->getGender()->getId() == TblCommonGender::VALUE_MALE){
                        $FirstLine = 'Lieber '.$tblPerson->getFullName().',';
                    } elseif($tblPerson->getGender() && $tblPerson->getGender()->getId() == TblCommonGender::VALUE_FEMALE) {
                        $FirstLine = 'Liebe '.$tblPerson->getFullName().',';
                    } else {
                        $FirstLine = 'Liebe(r) '.$tblPerson->getFullName().',';
                    }
                }
            }
        }
        $Slice = new Slice();

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleHeight('120px')
                , '50%'
            )
            ->addElementColumn(
                $this->getPicturePasswordChange()
                    ->styleAlignCenter()
                , '50%')
            )
            ->addSection((new Section())
                ->addSliceColumn(
                    $this->getAddressHead($AccountId)
                        ->styleHeight('160px')
                    , '57%')
                ->addSliceColumn(
                    $this->getContactData()
                    , '43%')
            )
            ->addElement((new Element())
                ->setContent('Zugangsdaten und Sicherheitsinformationen zur Anmeldung für die Schulsoftware')
                ->styleTextBold()
            )
            ->addElement((new Element())
                ->setContent('&nbsp;')
                ->styleHeight('40px')
            );

        if ($this->FieldValue['IsParent']) {
            $Slice->addElement((new Element())
                    ->setContent($FirstLine)
                );
            $Slice->addElement((new Element())
                    ->setContent('die Schulstiftung der Ev.-Luth. Landeskirche Sachsens hat, in enger Abstimmung mit dem Datenschutzbeauftragten der Ev.-Luth.
                        Landeskirche, eine Schulsoftware entwickeln lassen, die für alle evangelischen Schulen in Sachsen und darüber hinausnutzbar ist. Auch
                        wir als '.$this->FieldValue['CompanyName']??'<span style="color: ' . $this::PLACE_HOLDER . '"> >> Schulname << </span>'.' nutzen diese
                        im Alltag. Die Nutzung der Software erfolgt über einen gängigen Webbrowser. Der Betrieb der Software erfolgt in einem zertifizierten
                        deutschen Rechenzentrum und wird durch die dortigen Mitarbeiter sowie durch die mit der Entwicklung beauftragten Firma permanent
                        überwacht und gewartet, um sie vor Cyberangriffen zu schützen. Da hier personenbezogene vertrauliche Daten verarbeitet werden, gelten
                        vergleichbar hohe Sicherheitsanforderungen wie beim Onlinebanking. Beispielsweise sind Änderungen an Stammdaten oder die Eintragung von
                        Benotungen nur für Mitarbeiter der Schule möglich, die über die entsprechenden Zugriffsberechtigungen verfügen und sich per
                        Zweifaktor-Authentifizierung (Name, Passwort und Security-Token) anmelden müssen.')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    ->styleAlignJustify()
                );
            $Slice->addElement((new Element())
                    ->setContent('Ab sofort stellen wir ihnen dazu einen Zugang zur webbasierten Schulsoftware zur Verfügung.
                        Dadurch erhalten Sie die Möglichkeit, einen Überblick zu Ihrem Kind zu bekommen. Die
                        Kommunikation zwischen Ihrem Internetbrowser und der Schulsoftware erfolgt ausschließlich über eine
                        verschlüsselte HTTPS-Verbindung.')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    ->styleAlignJustify()
                )
                ->addElement((new Element())
                    ->setContent('Für die Sicherheit Ihrer Daten ist es allerdings wichtig, ein möglichst gutes Passwort mit einer
                        Mindestlänge von 8 Zeichen und einer Mischung aus Großbuchstaben, Kleinbuchstaben, Ziffern und
                        evtl. auch noch Sonderzeichen zu verwenden. <u>Bitte geben Sie Ihre Zugangsdaten nicht weiter, denn es
                        erhält jeder Sorgeberechtigte und jeder Schüler einen eigenen personengebundenen Nutzerzugang.</u>')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    ->styleAlignJustify()
                )
                ->styleHeight($Height);
        } else {
            $Slice->addElement((new Element())
                ->setContent($FirstLine)
            )
                ->addElement((new Element())
                    ->setContent('ab sofort stellen wir dir einen Zugang zu unserer Schulsoftware zur Nutzung bereit. Damit kannst
                        du und deine Eltern per Internetzugang die wichtigsten Informationen einsehen.')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    ->styleAlignJustify()
                )
                ->addElement((new Element())
                    ->setContent('Die Schulstiftung der Ev.-Luth. Landeskirche Sachsens hat, in enger Abstimmung mit dem
                        Datenschutzbeauftragten der Ev.-Luth. Landeskirche Sachsens, eine Schulsoftware entwickeln
                        lassen, die für alle evangelischen Schulen in Sachsen und darüber hinaus nutzbar ist. Der Betrieb
                        der Softwarelösung erfolgt in einem zertifizierten deutschen Rechenzentrum und wird durch die dortigen
                        Mitarbeiter sowie durch die mit der Entwicklung beauftragten Firma permanent überwacht und
                        gewartet, um sie vor Cyberangriffen zu schützen. Die Kommunikation zwischen Eurem
                        Internetbrowser und der Schulsoftware erfolgt ausschließlich über eine verschlüsselte HTTPS-
                        Verbindung.')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    ->styleAlignJustify()
                )
                ->addElement((new Element())
                    ->setContent('Mit diesem Brief möchten wir dich über deine Zugangsdaten und einige Sicherheitshinweise informieren.')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    ->styleAlignJustify()
                )
                ->addElement((new Element())
                    ->setContent('Für die Sicherheit deiner gespeicherten Daten ist es wichtig, ein möglichst gutes Passwort mit einer
                        Mindestlänge von 8 Zeichen und einer Mischung aus Großbuchstaben, Kleinbuchstaben, Ziffern und
                        evtl. auch noch Sonderzeichen zu verwenden. <u>Bitte gib deine Zugangsdaten nicht weiter, denn
                        es erhält jeder sorgeberechtigte Elternteil und jeder Schüler einen eigenen
                        personengebundenen</u>')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    ->styleAlignJustify()
                )
                ->styleHeight($Height);
        }
        // Ränder
        $Slice = (new Slice())->addSection((new Section())
            ->addElementColumn((new Element())->setContent('&nbsp;'), self::BORDER)
            ->addSliceColumn($Slice)
            ->addElementColumn((new Element())->setContent('&nbsp;'), self::BORDER)
        );
        return $Slice;
    }

    /**
     * @param int $AccountId
     *
     * @return Page
     */
    private function buildPageOne($AccountId)
    {

        return (new Page())
            ->addSlice($this->getFirstLetterContent($AccountId))
            ->addSlice($this->getEmptyHeight('40px'));
    }

    /**
     * @param $AccountId
     *
     * @return Slice
     */
    private function getSecondLetterContent($AccountId)
    {
        $Live = 'https://schulsoftware.schule';

        $tblConsumer = GatekeeperConsumer::useService()->getConsumerBySession();
        if ($tblConsumer && $tblConsumer->getType() == TblConsumer::TYPE_BERLIN && $tblConsumer->getAcronym() !== 'SSB') {
            $Live = 'https://ekbo.schulsoftware.schule';
        }

        $PaidContent = '';
        $PaidContentCustody = 'Sollten Sie Ihr Kennwort vergessen, so kann es durch die Schule zurückgesetzt werden.';
        if(($tblSetting = Consumer::useService()->getSetting('ParentStudentAccess', 'Person', 'ContactDetails', 'PasswordRecoveryCost'))){
            if(($Amount = $tblSetting->getValue())){
                $PaidContent = new Container('Hierfür erheben wir einen Unkostenbeitrag von '.$Amount.' €.');
                $PaidContentCustody = 'Sollten Sie Ihr Kennwort vergessen, so kann es durch die Schule gegen eine Gebühr von '.$Amount.' € zurückgesetzt werden.';
            }
        }

        $Slice = new Slice();
        if ($this->FieldValue['IsParent']) {
            $Slice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Verwenden Sie bitte für den Zugriff Zugangsdaten:')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    ->styleAlignJustify()
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '5%')
                ->addElementColumn((new Element())
                    ->setContent('Adresse:')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    , '22%'
                )
                ->addElementColumn((new Element())
                    ->setContent($Live)
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    , '73%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '5%')
                ->addElementColumn((new Element())
                    ->setContent('Benutzername:')
                    , '22%'
                )
                ->addElementColumn((new Element())
                    ->setContent($this->FieldValue['UserAccountNameList'][$AccountId])
                    , '73%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Für das erstmalige Login verwenden Sie bitte folgendes')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Passwort:')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    , '22%'
                )
                ->addElementColumn((new Element())
                    ->setContent($this->FieldValue['Password'][$AccountId])
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    , '73%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Lesen Sie sich die Datenschutzbestimmungen und Nutzungsbedingungen genau durch. Wenn Sie einverstanden sind und die 
                    elektronische Notenübersicht nutzen möchten, so vergeben Sie bitte Ihr zukünftiges Passwort für den Zugang und bestätigen Sie Ihre Eingaben.')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    ->styleAlignJustify()
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Notieren Sie sich bitte sofort Ihr vergebenes Passwort')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    , '53%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    ->styleBorderBottom()
                    , '47%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('und <b>bewahren Sie bitte den Brief an sicherer Stelle und leicht zu finden auf</b>, 
                        damit Ihre Zugangsdaten verfügbar bleiben! Geben Sie die Zugangsdaten nicht an Dritte weiter! '.$PaidContentCustody)
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    ->styleAlignJustify()
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Falls Sie noch Rückfragen oder Probleme mit der Anwendung haben, können Sie uns gerne kontaktieren.')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    ->styleAlignJustify()
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Namen der sorgeberechtigten Kinder:')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    ->styleAlignJustify()
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($this->FieldValue['ChildList'][$AccountId])
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    ->styleAlignJustify()
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Dieses Schreiben wurde maschinell erstellt und ist auch ohne Unterschrift rechtsgültig.')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                )
            );
        } else {
            $Slice
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Verwendet bitte für den Zugriff auf die Schulsoftware folgende Zugangsdaten:')
                        ->stylePaddingTop(self::BLOCK_SPACE)
                        ->styleAlignJustify()
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Adresse:')
                        ->stylePaddingTop(self::BLOCK_SPACE)
                        , '22%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent($Live)
                        ->stylePaddingTop(self::BLOCK_SPACE)
                        , '73%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Benutzername:')
                        , '22%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent($this->FieldValue['UserAccountNameList'][$AccountId])
                        , '73%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Für das erstmalige Login verwendet bitte folgendes')
                        ->stylePaddingTop(self::BLOCK_SPACE)
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('Passwort:')
                        ->stylePaddingTop(self::BLOCK_SPACE)
                        , '22%%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent($this->FieldValue['Password'][$AccountId])
                        ->stylePaddingTop(self::BLOCK_SPACE)
                        , '73%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Lies Dir bitte die Datenschutzbestimmungen und Nutzungsbedingungen genau durch. Wenn Du
                            einverstanden bist, so gib Dein zukünftiges Passwort für den Zugang ein und bestätige Deine Eingaben.')
                        ->stylePaddingTop(self::BLOCK_SPACE)
                        ->styleAlignJustify()
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Notiere Dir bitte sofort das vergebene Passwort')
                        ->stylePaddingTop(self::BLOCK_SPACE)
                        , '53%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->stylePaddingTop(self::BLOCK_SPACE)
                        ->styleBorderBottom()
                        , '47%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('und <b>bewahre den Brief an sicherer Stelle auf</b>, damit Deine Zugangsdaten verfügbar bleiben!
                         Das Zurücksetzen vergessener Passwörter und die Zusendung neuer Passwortbriefe verursachen nicht unerhebliche Aufwände und Kosten
                         für die Schule. '.$PaidContent)
                        ->stylePaddingTop(self::BLOCK_SPACE)
                        ->styleAlignJustify()
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Nach Deiner Bestätigung wird eine Startseite mit dem Verweis auf die wichtigsten Übersichten
                            angezeigt. Alternativ kann man auch die Menüleiste nutzen. Falls Du Dich gegen die Nutzung der
                            Schulsoftware entscheidest, bleibt Dein Zugang deaktiviert. Falls Du Rückfragen oder Probleme mit der
                            Anwendung hast, wende Dich bitte an unser Sekretariat.')
                        ->stylePaddingTop(self::BLOCK_SPACE)
                        ->styleAlignJustify()
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Dieses Schreiben wurde maschinell erstellt und ist auch ohne Unterschrift rechtsgültig.')
                        ->stylePaddingTop(self::BLOCK_SPACE)
                    )
                );
        }
        // Ränder
        $Slice = (new Slice())->addSection((new Section())
            ->addElementColumn((new Element())->setContent('&nbsp;'), self::BORDER)
            ->addSliceColumn($Slice)
            ->addElementColumn((new Element())->setContent('&nbsp;'), self::BORDER)
        );

        return $Slice;
    }

    /**
     * @param int $AccountId
     *
     * @return Page
     */
    private function buildPageTwo($AccountId)
    {

        return (new Page())
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('&nbsp;')
                    ->styleHeight('250px')
                )
            )
            ->addSlice($this->getSecondLetterContent($AccountId));
    }

    /**
     * @param string $with
     *
     * @return Element|Element\Image
     */
    protected function getPicturePasswordChange($with = 'auto')
    {

        $picturePath = $this->getPasswordUsedPicture();
        if ($picturePath != '') {
            $height = $this->getPasswordPictureHeight();
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
    private function getPasswordUsedPicture()
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
    private function getPasswordPictureHeight()
    {

        $value = '';

        if (($tblSetting = Consumer::useService()->getSetting(
            'Api', 'Document', 'Standard', 'PasswordChange_PictureHeight'))
        ) {
            $value = (string)$tblSetting->getValue();
        }

        return $value ? $value : '120px';
    }

    /**
     * @param string $Height
     *
     * @return Slice
     */
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