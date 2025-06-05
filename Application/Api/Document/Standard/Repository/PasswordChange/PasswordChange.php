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
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonGender;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer as GatekeeperConsumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Setting\Authorization\Account\Account as AccountAuthorization;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\User\Account\Account;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;

class PasswordChange extends AbstractDocument
{

    const BLOCK_SPACE = '30px';
    const BORDER = '4%';

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
        $this->FieldValue['PersonSalutation'] = '';
        $this->FieldValue['PersonFirstLastName'] = '';
        $this->FieldValue['PersonTitle'] = '';
        $this->FieldValue['PersonLastName'] = '';
        $this->FieldValue['Gender'] = 0;
        $this->FieldValue['UserAccount'] = '';
        $this->FieldValue['Street'] = '';
        $this->FieldValue['District'] = '';
        $this->FieldValue['City'] = '';
        // Text choose decision
        $this->FieldValue['IsParent'] = (isset($DataPost['IsParent']) ? $DataPost['IsParent'] : false);
        $this->FieldValue['ChildCount'] = 0;
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

            $ChildCount = 0;
            if($this->FieldValue['IsParent']){
                if(($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson))){
                    foreach($tblRelationshipList as $tblRelationship){
                        $tblType = $tblRelationship->getTblType();
                        if($tblType->getName() == 'Sorgeberechtigt'){
                            $ChildCount++;
                        }
                    }
                }
            }
            if($ChildCount == 1){
                $this->FieldValue['ChildCount'] = 1;
            }elseif($ChildCount > 1){
                $this->FieldValue['ChildCount'] = 2;
            }

            if(($tblCommon = $tblPerson->getCommon())) {
                if(($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())){
                    if(($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())){
                        if($tblCommonGender->getName() == "Männlich"){
                            $this->FieldValue['Gender'] = 1;
                        }
                        if($tblCommonGender->getName() == "Weiblich") {
                            $this->FieldValue['Gender'] = 2;
                        }
                    }
                }
            }

            $this->FieldValue['PersonTitle'] = $tblPerson->getTitle();
            $this->FieldValue['PersonLastName'] = $tblPerson->getLastName();
            $this->FieldValue['PersonSalutation'] = $tblPerson->getSalutation();
            $this->FieldValue['PersonFirstLastName'] = $tblPerson->getFirstName().' '.$tblPerson->getLastName();
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

        //generate new Password
        $this->FieldValue['Password'] = $Password = Account::useService()->generatePassword();
        // remove tblAccount
        if ($tblAccount && $Password) {
            if(($tblUserAccount = Account::useService()->getUserAccountByAccount($tblAccount))){
                AccountAuthorization::useService()->changePassword($Password, $tblAccount);
                Account::useService()->changePassword($tblUserAccount, $Password);
                Account::useService()->changeUpdateDate($tblUserAccount, TblUserAccount::VALUE_UPDATE_TYPE_RENEW);
            }
        };

        $this->FieldValue['FirstLine'] = 'Lieber Nutzer,';
        if(($tblPerson = Person::useService()->getPersonById($this->FieldValue['PersonId']))){
            if($tblPerson->getSalutation() == TblSalutation::VALUE_MAN){
                $this->FieldValue['FirstLine'] = 'Lieber '.$tblPerson->getSalutation().' '.$tblPerson->getLastName().',';
            } elseif($tblPerson->getSalutation() == TblSalutation::VALUE_WOMAN) {
                $this->FieldValue['FirstLine'] = 'Liebe '.$tblPerson->getSalutation().' '.$tblPerson->getLastName().',';
            }else {
                if($tblPerson->getGender() && $tblPerson->getGender()->getId() == TblCommonGender::VALUE_MALE){
                    $this->FieldValue['FirstLine'] = 'Lieber '.$tblPerson->getFullName().',';
                } elseif($tblPerson->getGender() && $tblPerson->getGender()->getId() == TblCommonGender::VALUE_FEMALE) {
                    $this->FieldValue['FirstLine'] = 'Liebe '.$tblPerson->getFullName().',';
                } else {
                    $this->FieldValue['FirstLine'] = 'Liebe(r) '.$tblPerson->getFullName().',';
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

        return 'Passwort-Schulsoftware';
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
        return (new Frame())->addDocument((new Document())
            ->addPage($this->buildPageOne())
        );
    }

    /**
     * @return Slice
     */
    private function getAddressHead()
    {
        $Slice = new Slice();

        $Slice->addElement((new Element())
            ->setContent($this->FieldValue['CompanyName'])
            ->styleTextSize('8pt')
        );
        if($this->FieldValue['CompanyExtendedName'] != '&nbsp;'){
            $Slice->addElement((new Element())
                ->setContent($this->FieldValue['CompanyExtendedName'])
                ->styleTextSize('8pt')
            );
        }
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

//        $Slice->addElement((new Element())
//            ->setContent('Empfänger')
//            ->styleTextSize('8pt')
//        );
        $Slice->addElement((new Element())
            ->setContent($this->FieldValue['PersonSalutation'].' '.$this->FieldValue['PersonFirstLastName'])
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

    private function getFirstLetterContent( $Height = '500px')
    {
        $Live = 'https://schulsoftware.schule';

        $tblConsumer = GatekeeperConsumer::useService()->getConsumerBySession();
        if ($tblConsumer && $tblConsumer->getType() == TblConsumer::TYPE_BERLIN && $tblConsumer->getAcronym() !== 'SSB') {
            $Live = 'https://ekbo.schulsoftware.schule';
        }

        $PaidContent = '';
        if(($tblSetting = Consumer::useService()->getSetting('ParentStudentAccess', 'Person', 'ContactDetails', 'PasswordRecoveryCost'))){
            if(($Amount = $tblSetting->getValue())){
                if ($this->FieldValue['IsParent']) {
                    $PaidContent = 'Hierfür erheben wir einen Unkostenbeitrag von&nbsp;'.$Amount.'&nbsp;€, der Ihnen bei
                     der nächsten Abrechnung belastet wird.';
                } else {
                    $PaidContent = 'Hierfür erheben wir einen Unkostenbeitrag von&nbsp;'.$Amount.'&nbsp;€, der Deinen
                     Eltern berechnet wird.';
                }
            }
        }

        $Slice = new Slice();
        if ($this->FieldValue['IsParent']) {
            $Slice->addElement($this->getTextElement('Ihre neuen Zugangsdaten für die Schulsoftware 
            {% if '.$this->FieldValue['ChildCount'].' == 1 %}
                    Ihres Kindes
                {% elseif '.$this->FieldValue['ChildCount'].' == 2 %}
                    Ihrer Kinder
                {% else %}
                    Ihres Kindes / Ihrer Kinder
            {% endif %}')->styleTextBold());
            $Slice->addElement($this->getTextElement($this->FieldValue['FirstLine']));
            $Slice->addElement($this->getTextElement('Wunschgemäß übersenden wir Ihnen Ihre neuen Zugangsdaten für
             die Schulsoftware
             {% if '.$this->FieldValue['ChildCount'].' == 1 %}
                    Ihres Kindes.
                {% elseif '.$this->FieldValue['ChildCount'].' == 2 %}
                    Ihrer Kinder.
                {% else %}
                    Ihres Kindes / Ihrer Kinder.
            {% endif %}'));
        } else {
            $Slice->addElement($this->getTextElement('Deine neuen Zugangsdaten zur Schulsoftware')->styleTextBold());
            $Slice->addElement($this->getTextElement($this->FieldValue['FirstLine']));
            $Slice->addElement($this->getTextElement('Wunschgemäß übersenden wir Dir deine neue Zugangsdaten zur
             Schulsoftware.'));
        }
        // password block
        $Slice->addElement((new Element())->setContent('&nbsp;')->stylePaddingTop('18px'));
        $Slice->addSection($this->getInfoSection('Adresse:', $Live));
        $Slice->addSection($this->getInfoSection('Benutzername:', $this->FieldValue['UserAccount']));
        $Slice->addSection($this->getInfoSection('Passwort:', $this->FieldValue['Password']));

        if ($this->FieldValue['IsParent']) {
            $Slice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Bitte ändern Sie das Initialpasswort und notieren Sie sich das neue Passwort hier:')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    , '79%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    ->styleBorderBottom()
                    , '21%'
                )
            );
        } else {
            $Slice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Bitte ändere das Initialpasswort und notiere das neue Passwort hier:')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    , '66%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    ->styleBorderBottom()
                    , '34%'
                )
            );
        }
        // end block
        if($PaidContent){
            $Slice->addElement($this->getTextElement($PaidContent));
        }

        if ($this->FieldValue['IsParent']) {
            $Slice->addElement($this->getTextElement('Bitte heben Sie dieses Schreiben gut auf.')->styleTextBold());
            $Slice->addElement($this->getTextElement('Für Rückfragen stehen wir Ihnen gern zur Verfügung.'));
        } else {
            $Slice->addElement($this->getTextElement('Bitte hebe dieses Schreiben gut auf.')->styleTextBold());
            $Slice->addElement($this->getTextElement('Für Rückfragen stehen wir Dir gern zur Verfügung.'));
        }

        $Slice->addElement($this->getTextElement('Dieses Schreiben wurde maschinell erstellt und ist auch ohne Unterschrift rechtsgültig.'));
        $Slice->styleHeight($Height);

        // Ränder
        $Slice = (new Slice())->addSection((new Section())
            ->addElementColumn((new Element())->setContent('&nbsp;'), self::BORDER)
            ->addSliceColumn($Slice)
            ->addElementColumn((new Element())->setContent('&nbsp;'), self::BORDER)
        );

        return $Slice;
    }

    /**
     * @param $Text
     * @return Element
     */
    private function getTextElement($Text = '')
    {

        $Element = new Element();
        $Element->setContent($Text)
            ->stylePaddingTop(self::BLOCK_SPACE);
        return $Element;
    }

    /**
     * @param $Info
     * @param $Value
     * @return Section
     */
    private function getInfoSection($Info = '', $Value = '')
    {

        $Section = new Section();
        $Section->addElementColumn((new Element())->setContent('&nbsp;'), '5%');
        $Section->addElementColumn((new Element())->setContent($Info), '22%');
        $Section->addElementColumn((new Element())->setContent($Value), '73%');
        return $Section;
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
            ->addSlice($this->getFirstLetterContent());
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
}