<?php

namespace SPHERE\Application\Api\Billing\Bookkeeping;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Setting\Service\Entity\TblSetting;
use SPHERE\Application\Billing\Inventory\Setting\Setting;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\InlineReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Info as InfoIcon;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiBasketVerification
 * @package SPHERE\Application\Api\Billing\Bookkeeping
 */
class ApiBasketVerification extends Extension implements IApiInterface
{

    // registered method
    use ApiTrait;

    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        // reload ColumnContent
        $Dispatcher->registerMethod('getItemPrice');
        $Dispatcher->registerMethod('getItemSummary');
        $Dispatcher->registerMethod('getDebtor');
        $Dispatcher->registerMethod('getTableLayout');

        //Quantity
        $Dispatcher->registerMethod('changeQuantity');
        // DebtorSelection
        $Dispatcher->registerMethod('showEditDebtorSelection');
        $Dispatcher->registerMethod('saveEditDebtorSelection');

        $Dispatcher->registerMethod('showDeleteBasketSelection');
        $Dispatcher->registerMethod('deleteDebtorSelection');


        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Header
     * @param string $Identifier
     *
     * @return ModalReceiver
     */
    public static function receiverModal($Header = '', $Identifier = '')
    {

        return (new ModalReceiver($Header, new Close()))->setIdentifier('Modal'.$Identifier);
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverTableLayout($Content = '')
    {

        return (new BlockReceiver($Content))->setIdentifier('BasketVerificationLayout');
    }

    /**
     * @return InlineReceiver
     */
    public static function receiverService()
    {

        return (new InlineReceiver())->setIdentifier('Service');
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return InlineReceiver
     */
    public static function receiverItemPrice($Content = '', $Identifier = '')
    {

        return (new InlineReceiver($Content))->setIdentifier('Price'.$Identifier);
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return InlineReceiver
     */
    public static function receiverItemQuantity($Content = '', $Identifier = '')
    {

        return (new InlineReceiver($Content))->setIdentifier('Quantity'.$Identifier);
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return InlineReceiver
     */
    public static function receiverItemSummary($Content = '', $Identifier = '')
    {

        return (new InlineReceiver($Content))->setIdentifier('Summary'.$Identifier);
    }

    /**
     * @param string $BasketVerificationId
     *
     * @return Pipeline
     */
    public static function pipelineChangeQuantity($BasketVerificationId = '')
    {

        $Receiver = self::receiverService();
        $Pipeline = new Pipeline(false);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'changeQuantity'
        ));
        $Emitter->setPostPayload(array(
            'BasketVerificationId' => $BasketVerificationId
        ));
        $Emitter->setLoadingMessage('Speichern erfolgreich!');
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $BasketVerificationId
     * @param array  $Quantity
     *
     * @return string
     */
    public function changeQuantity($BasketVerificationId = '', $Quantity = array())
    {

        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))){
            if($Quantity[$BasketVerificationId] && is_numeric($Quantity[$BasketVerificationId])){
                Basket::useService()->changeBasketVerification($tblBasketVerification,
                    $Quantity[$BasketVerificationId]);
            }
        }
        return ''.self::pipelineReloadSummary($BasketVerificationId);
    }

    /**
     * @param string $BasketVerificationId
     *
     * @return Pipeline
     */
    public static function pipelineReloadSummary($BasketVerificationId = '')
    {
        $Pipeline = new Pipeline(false);
        // reload columns
        // reload Summary column
        $Emitter = new ServerEmitter(self::receiverItemSummary('', $BasketVerificationId), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getItemSummary'
        ));
        $Emitter->setPostPayload(array(
            'BasketVerificationId' => $BasketVerificationId
        ));
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    /**
     * @param string $BasketId
     * @param string $Identifier
     *
     * @return Pipeline
     */
    public static function pipelineReloadTable($BasketId = '', $Identifier = '')
    {
        $Pipeline = new Pipeline(false);
        // reload columns
        // reload Summary column
        $Emitter = new ServerEmitter(self::receiverTableLayout(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getTableLayout'
        ));
        $Emitter->setPostPayload(array(
            'BasketId' => $BasketId
        ));
        $Pipeline->appendEmitter($Emitter);
        if($Identifier){
            $Pipeline->appendEmitter((new CloseModal(self::receiverModal('', $Identifier)))->getEmitter());
        }
        return $Pipeline;
    }

    public function getTableLayout($BasketId = '')
    {


        if($BasketId){
            return Basket::useFrontend()->getBasketVerificationLayout($BasketId);
        }
        return 'Fehler';
    }

    /**
     * @param $BasketVerificationId
     *
     * @return string
     */
    public function getItemPrice($BasketVerificationId)
    {

        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))){
            return $tblBasketVerification->getPrice();
        }
        return '';
    }

    /**
     * @param $BasketVerificationId
     *
     * @return string
     */
    public function getItemSummary($BasketVerificationId)
    {

        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))){
            return $tblBasketVerification->getSummaryPrice();
        }
        return '';
    }

    /**
     * @param $BasketVerificationId
     *
     * @return string
     */
    public function getDebtor($BasketVerificationId)
    {

        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))){
            $InfoDebtorNumber = '';
            $IsDebtorNumberNeed = false;
            if($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_IS_DEBTOR_NUMBER_NEED)){
                if($tblSetting->getValue() == 1){
                    $IsDebtorNumberNeed = true;
                }
            }
            // new DebtorNumber
            if($IsDebtorNumberNeed){
                $InfoDebtorNumber = new ToolTip(new DangerText(new Disable()), 'Debit.-Nr. wird benötigt!');
            }

            if(($tblPerson = $tblBasketVerification->getServiceTblPersonDebtor())){
                return $tblPerson->getLastFirstName().' '.$InfoDebtorNumber;
            }
        }
        return new DangerText('Fehler!');
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return InlineReceiver
     */
    public static function receiverDebtor($Content = '', $Identifier = '')
    {

        return (new InlineReceiver($Content))->setIdentifier('Debtor'.$Identifier);
    }

    /**
     * @param       $BasketVerificationId
     * @param array $DebtorSelection
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditDebtorSelectionModal(
        $BasketVerificationId,
        $DebtorSelection = array()
    ){

        $Receiver = self::receiverModal();
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showEditDebtorSelection'
        ));
        $Emitter->setPostPayload(array(
            'BasketVerificationId' => $BasketVerificationId,
            'DebtorSelection'      => $DebtorSelection
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $BasketVerificationId
     *
     * @return Pipeline
     */
    public static function pipelineSaveEditDebtorSelection(
        $BasketVerificationId = ''
    ){

        $Receiver = self::receiverModal();
        $Pipeline = new Pipeline(true);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveEditDebtorSelection'
        ));
        $Emitter->setPostPayload(array(
            'BasketVerificationId' => $BasketVerificationId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     * @param string $BasketVerificationId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteDebtorSelectionModal($Identifier = '', $BasketVerificationId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showDeleteBasketSelection'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'           => $Identifier,
            'BasketVerificationId' => $BasketVerificationId,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     * @param string $BasketVerificationId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteDebtorSelection($Identifier = '', $BasketVerificationId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(false);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'deleteDebtorSelection'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'           => $Identifier,
            'BasketVerificationId' => $BasketVerificationId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $BasketVerificationId
     *
     * @return Pipeline
     */
    public static function pipelineCloseDebtorSelectionModal($BasketVerificationId = '')
    {
        $Pipeline = new Pipeline();
        // reload columns
        // reload debtor column
        $Emitter = new ServerEmitter(self::receiverDebtor('', $BasketVerificationId), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getDebtor'
        ));
        $Emitter->setPostPayload(array(
            'BasketVerificationId' => $BasketVerificationId
        ));
        $Pipeline->appendEmitter($Emitter);
        // reload Price column
        $Emitter = new ServerEmitter(self::receiverItemPrice('', $BasketVerificationId), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getItemPrice'
        ));
        $Emitter->setPostPayload(array(
            'BasketVerificationId' => $BasketVerificationId
        ));
        $Pipeline->appendEmitter($Emitter);
        // reload Summary column
        $Emitter = new ServerEmitter(self::receiverItemSummary('', $BasketVerificationId), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getItemSummary'
        ));
        $Emitter->setPostPayload(array(
            'BasketVerificationId' => $BasketVerificationId
        ));
        $Pipeline->appendEmitter($Emitter);

        $Pipeline->appendEmitter((new CloseModal(self::receiverModal()))->getEmitter());
        return $Pipeline;
    }

    /**
     * @param string $BasketVerificationId
     *
     * @return Form
     */
    public function formDebtorSelection($BasketVerificationId = '')
    {

        // choose between Add and Edit
        $SaveButton = new Primary('Speichern', self::getEndpoint(), new Save());
        $SaveButton->ajaxPipelineOnClick(self::pipelineSaveEditDebtorSelection($BasketVerificationId));

        $PaymentTypeList = array();
        // post Type if not Exist

        $tblPaymentTypeAll = Balance::useService()->getPaymentTypeAll();
        foreach($tblPaymentTypeAll as $tblPaymentType) {
            $PaymentTypeList[$tblPaymentType->getId()] = $tblPaymentType->getName();
            if($tblPaymentType->getName() == 'SEPA-Lastschrift'/*'Bar' // Test*/){
                if(!isset($_POST['DebtorSelection']['PaymentType'])){
                    $_POST['DebtorSelection']['PaymentType'] = $tblPaymentType->getId();
                }
            }
        }

        //get First Variant to Select
        $PostVariantId = '-1';
        if(!isset($_POST['DebtorSelection']['Variant'])){
            $_POST['DebtorSelection']['Variant'] = $PostVariantId;
        }
        $RadioBoxListVariant = array();
        $PersonDebtorList = array();
        $SelectBoxDebtorList = array();
        $tblBankReferenceList = array();
        $ItemName = '';
        $PersonTitle = '';
        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))){

            $tblItem = $tblBasketVerification->getServiceTblItem();
            $tblPersonCauser = $tblBasketVerification->getServiceTblPersonCauser();
            // ToDO Preisvariante nicht mehr eindeutig, da sich der Preis geändert haben kann.
//            if($tblItem && $tblPersonCauser){
//                if(($tblDebtorSelectionList = Debtor::useService()->getDebtorSelectionByPersonCauserAndItem($tblPersonCauser, $tblItem))){
//                    /** @var TblDebtorSelection $tblDebtorSelection */
//                    $tblDebtorSelection = current($tblDebtorSelectionList);
//                    if(($tblItemVariant = $tblDebtorSelection->getServiceTblItemVariant())) {
//                        $PostVariantId = $tblItemVariant->getId();
//                    }
//                }
//            }
            if($tblItem){
                $ItemName = $tblItem->getName();
                if(($tblItemVariantList = Item::useService()->getItemVariantByItem($tblItem))){
                    foreach($tblItemVariantList as $tblItemVariant) {
                        $PriceString = new DangerText('Nicht verfügbar');
                        if(($tblItemCalculation = Item::useService()->getItemCalculationNowByItemVariant($tblItemVariant))){
                            $PriceString = $tblItemCalculation->getPriceString();
                        }

                        $RadioBoxListVariant[] = new RadioBox('DebtorSelection[Variant]',
                            $tblItemVariant->getName().': '.$PriceString, $tblItemVariant->getId());
                    }
                    $RadioBoxListVariant[] = new RadioBox('DebtorSelection[Variant]',
                        'Individuelle Preiseingabe'.new TextField('DebtorSelection[Price]', '', ''), -1);
                }
            }
            if($tblPersonCauser){
                $PersonTitle = ' für '.new Bold($tblPersonCauser->getFirstName().' '.$tblPersonCauser->getLastName());
            }
            if($tblPersonCauser
                && $tblRelationshipType = Relationship::useService()->getTypeByName('Sorgeberechtigt')){
                $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_DEBTOR);
                // is Causer Person in Group "Bezahler"
//                if(Group::useService()->getMemberByPersonAndGroup($tblPersonCauser, $tblGroup)){
                $PersonDebtorList[] = $tblPersonCauser;
//                }
                if(($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPersonCauser,
                    $tblRelationshipType))){
                    foreach($tblRelationshipList as $tblRelationship) {
                        if(($tblPersonRel = $tblRelationship->getServiceTblPersonFrom()) && $tblPersonRel->getId() !== $tblPersonCauser->getId()){
                            // is Person in Group "Bezahler"
                            if(Group::useService()->getMemberByPersonAndGroup($tblPersonRel, $tblGroup)){
                                $DeborNumber = $this->getDebtorNumberByPerson($tblPersonRel);
                                $SelectBoxDebtorList[$tblPersonRel->getId()] = $tblPersonRel->getLastFirstName().' '.$DeborNumber;
                                $PersonDebtorList[] = $tblPersonRel;
                            }
                        }
                    }
                    // Bezahler ohne Gruppe (z.B. Sorgeberechtigte, die ohne Konto bezahlen (Bar/Überweisung))
//                    if(empty($SelectBoxDebtorList)) {
                    $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_CUSTODY);
                    foreach($tblRelationshipList as $tblRelationship) {
                        if(($tblPersonRel = $tblRelationship->getServiceTblPersonFrom()) && $tblPersonRel->getId() !== $tblPersonCauser->getId()){
                            // is Person in Group "Sorgeberechtigte"
                            if(Group::useService()->getMemberByPersonAndGroup($tblPersonRel, $tblGroup)){
                                $DeborNumber = $this->getDebtorNumberByPerson($tblPersonRel);
                                $SelectBoxDebtorList[$tblPersonRel->getId()] = $tblPersonRel->getLastFirstName().' '.$DeborNumber;
                            }
                        }
                    }
//                    }
                }
                if(($tblRelationshipType = Relationship::useService()->getTypeByName('Beitragszahler'))){
                    if(($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPersonCauser,
                        $tblRelationshipType))){
                        foreach($tblRelationshipList as $tblRelationship) {
                            if(($tblPersonRel = $tblRelationship->getServiceTblPersonFrom()) && $tblPersonRel->getId() !== $tblPersonCauser->getId()){
                                // is Person in Group "Bezahler"
                                if(Group::useService()->getMemberByPersonAndGroup($tblPersonRel, $tblGroup)){
                                    $DeborNumber = $this->getDebtorNumberByPerson($tblPersonRel);
                                    $SelectBoxDebtorList[$tblPersonRel->getId()] = $tblPersonRel->getLastFirstName().' '.$DeborNumber;
                                    $PersonDebtorList[] = $tblPersonRel;
                                }
                            }
                        }
                    }
                }

                // Beitragsverursacher steht immer am Schluss
                $DeborNumber = $this->getDebtorNumberByPerson($tblPersonCauser);
                $SelectBoxDebtorList[$tblPersonCauser->getId()] = $tblPersonCauser->getLastFirstName().' '.$DeborNumber;
            }

            if($tblPersonCauser){
                $tblBankReferenceList = Debtor::useService()->getBankReferenceByPerson($tblPersonCauser);
                if($tblBankReferenceList){
                    // Post first entry if PaymentType = SEPA-Lastschrift
                    if(isset($_POST['DebtorSelection']['PaymentType'])
                        && ($tblPaymentType = Balance::useService()->getPaymentTypeById($_POST['DebtorSelection']['PaymentType']))
                        && $tblPaymentType->getName() == 'SEPA-Lastschrift'){
                        if(!isset($_POST['DebtorSelection']['BankReference'])){
                            $_POST['DebtorSelection']['BankReference'] = $tblBankReferenceList[0]->getId();
                        }
                    }
                }
            }
        }

        $PostBankAccountId = false;
        $RadioBoxListBankAccount = array();
        // no BankAccount available
        if(!isset($_POST['DebtorSelection']['BankAccount'])){
            $_POST['DebtorSelection']['BankAccount'] = '-1';
        }
        $RadioBoxListBankAccount['-1'] = new RadioBox('DebtorSelection[BankAccount]'
            , 'kein Konto', -1);
        if(!empty($PersonDebtorList)){
            /** @var TblPerson $PersonDebtor */
            foreach($PersonDebtorList as $PersonDebtor) {
                if(($tblBankAccountList = Debtor::useService()->getBankAccountAllByPerson($PersonDebtor))){
                    foreach($tblBankAccountList as $tblBankAccount) {
                        if(!$PostBankAccountId){
                            $PostBankAccountId = $tblBankAccount->getId();
                            if(isset($_POST['DebtorSelection']['PaymentType'])
                                && !isset($_POST['DebtorSelection']['BankAccount'])
                                && ($tblPaymentType = Balance::useService()->getPaymentTypeById($_POST['DebtorSelection']['PaymentType']))
                                && $tblPaymentType->getName() == 'SEPA-Lastschrift'){
                                // override Post with first found BankAccount
                                $_POST['DebtorSelection']['BankAccount'] = $PostBankAccountId;
                            }
                        }
                        $RadioBoxListBankAccount[$tblBankAccount->getId()] = new RadioBox('DebtorSelection[BankAccount]'
                            , $tblBankAccount->getOwner().'<br/>'.$tblBankAccount->getBankName().'<br/>'
                            .$tblBankAccount->getIBANFrontend()
                            , $tblBankAccount->getId());
                    }
                }
            }
        }

        return (new Form(
            new FormGroup(array(
                new FormRow(
                    new FormColumn(new Title($ItemName, $PersonTitle))
                ),
                new FormRow(array(
                    new FormColumn(
                        (new SelectBox('DebtorSelection[PaymentType]', 'Zahlungsart',
                            $PaymentTypeList))
                        //ToDO Change follow Content
//                        ->ajaxPipelineOnChange()
                        , 6),
                    new FormColumn(
                        (new SelectBox('DebtorSelection[Debtor]', 'Bezahler',
                            $SelectBoxDebtorList /*array('{{ Name }}' => $tblPaymentTypeList)*/))
                        //ToDO Change follow Content
//                        ->ajaxPipelineOnChange()
                        , 6),
                )),
                new FormRow(array(
                    new FormColumn(
                        array(
                            new Bold('Varianten '),
                            new Listing($RadioBoxListVariant)
                        )
                        , 6),
                    new FormColumn(
                        array(
                            new Bold('Konten '),
                            new Listing($RadioBoxListBankAccount)
                        )
                        , 6),
                )),
                new FormRow(array(
                    new FormColumn(
                        new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(''))))
                        , 6
                    ),
                    new FormColumn(
                        new SelectBox('DebtorSelection[BankReference]', 'Mandatsreferenz',
                            array('ReferenceNumber' => $tblBankReferenceList))
                        , 6
                    )
                )),
                new FormRow(
                    new FormColumn(
                        $SaveButton
                    )
                )
            ))
        ))->disableSubmitAction();
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return string
     */
    private function getDebtorNumberByPerson(TblPerson $tblPerson)
    {

        $IsDebtorNumberNeed = false;
        if($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_IS_DEBTOR_NUMBER_NEED)){
            if($tblSetting->getValue() == 1){
                $IsDebtorNumberNeed = true;
            }
        }
        $DeborNumber = '';
        if($IsDebtorNumberNeed){
            $DeborNumber = '(keine Debit.-Nr.)';
        }
        // change warning if necessary to "not in PaymentGroup"
        if(($tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_DEBTOR))){
            if(!Group::useService()->getMemberByPersonAndGroup($tblPerson, $tblGroup)){
                $DeborNumber = '(kein Bezahler)';
            }
        }
        if(($tblDebtorNumberList = Debtor::useService()->getDebtorNumberByPerson($tblPerson))){
            $DebtorNumberList = array();
            foreach($tblDebtorNumberList as $tblDebtorNumber) {
                $DebtorNumberList[] = $tblDebtorNumber->getDebtorNumber();
            }
            $DeborNumber = implode(', ', $DebtorNumberList);
            $DeborNumber = '('.$DeborNumber.')';
        }
        return $DeborNumber;
    }

    /**
     * @param string $BasketVerificationId
     * @param array  $DebtorSelection
     *
     * @return false|string|Form
     */
    private function checkInputDebtorSelection(
        $BasketVerificationId = '',
        $DebtorSelection = array()
    ){

        $Error = false;
        $Warning = '';
        $form = $this->formDebtorSelection($BasketVerificationId);
        if(isset($DebtorSelection['PaymentType']) && empty($DebtorSelection['PaymentType'])){
            $form->setError('DebtorSelection[PaymentType]', 'Bitte geben Sie eine Zahlungsart an');
            $Error = true;
        }
        if(isset($DebtorSelection['Variant']) && empty($DebtorSelection['Variant'])){
            $Warning .= new Warning('Bitte geben Sie eine Bezahlvariante an, steht keine zur Auswahl, stellen Sie bitte eine bei den Beitragsarten ein.');
            $form->setError('DebtorSelection[Variant]', 'Bitte geben Sie eine Bezahlvariante an');
            $Error = true;
        } elseif(isset($DebtorSelection['Variant']) && $DebtorSelection['Variant'] == '-1') {
            // is price empty (is requiered vor no Variant)
            if(isset($DebtorSelection['Price']) && empty($DebtorSelection['Price']) && $DebtorSelection['Price'] !== '0'){
                $Warning .= new Warning('Bitte geben Sie einen individuellen Preis an');
//                $form->setError('DebtorSelection[Price]', 'Bitte geben Sie einen Individuellen Preis an');
                $Error = true;
            } elseif(isset($DebtorSelection['Price']) && !is_numeric(str_replace(',', '.',
                    $DebtorSelection['Price']))) {
                $Warning .= new Warning('Bitte geben Sie eine '.new Bold('Zahl').' als individuellen Preis an');
//                $form->setError('DebtorSelection[Price]', 'Bitte geben Sie einen Individuellen Preis an');
                $Error = true;
            }
        }
        if(isset($DebtorSelection['Debtor']) && empty($DebtorSelection['Debtor'])){
            $form->setError('DebtorSelection[Debtor]', 'Bitte geben Sie einen Bezahler an');
            $Error = true;
        }

        if(($tblPaymentType = Balance::useService()->getPaymentTypeById($DebtorSelection['PaymentType']))){
            $IsSepaAccountNeed = false;
            if($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_IS_SEPA_ACCOUNT_NEED)){
                if($tblSetting->getValue() == 1){
                    $IsSepaAccountNeed = true;
                }
            }
            if($tblPaymentType->getName() == 'SEPA-Lastschrift'){
                if($IsSepaAccountNeed){
                    if(isset($DebtorSelection['BankAccount']) && empty($DebtorSelection['BankAccount'])){
                        $Warning .= new Warning('Bitte geben sie ein Konto an. (Ein Konto wird benötigt, um ein 
                    SEPA-Lastschriftverfahren zu hinterlegen) Wahlweise andere Bezahlart auswählen.');
                        $form->setError('DebtorSelection[BankAccount]', 'Bitte geben Sie eine Konto an');
                        $Error = true;
                    } elseif(isset($DebtorSelection['BankAccount']) && $DebtorSelection['BankAccount'] == '-1') {
                        $Warning .= new Warning('Bitte geben sie ein Konto an. (Ein Konto wird benötigt, um ein 
                    SEPA-Lastschriftverfahren zu hinterlegen) Wahlweise andere Bezahlart auswählen.');
                        $form->setError('DebtorSelection[BankAccount]', 'Bitte geben Sie eine Konto an');
                        $Error = true;
                    }
                }
                //Referenznummern ohne Konto nicht mehr benötigt
//                if (isset($DebtorSelection['BankReference']) && empty($DebtorSelection['BankReference'])) {
//                    $form->setError('DebtorSelection[BankReference]', 'Bitte geben Sie eine Mandatsreferenz an');
//                    $Error = true;
//                }
            }
        }


        if($Error){
            // Debtor::useFrontend()->getPersonPanel($PersonId).
            return new Well($Warning.$form);
        }

        return $Error;
    }

    /**
     * @param       $BasketVerificationId
     * @param array $DebtorSelection
     *
     * @return false|Form|Danger|string
     */
    public function saveEditDebtorSelection(
        $BasketVerificationId,
        $DebtorSelection = array()
    ){

        // Handle error's
        if($form = $this->checkInputDebtorSelection($BasketVerificationId, $DebtorSelection)){

            // display Errors on form
            $Global = $this->getGlobal();
            $Global->POST['DebtorSelection']['PaymentType'] = $DebtorSelection['PaymentType'];
            $Global->POST['DebtorSelection']['Variant'] = $DebtorSelection['Variant'];
            $Global->POST['DebtorSelection']['Price'] = $DebtorSelection['Price'];
            $Global->POST['DebtorSelection']['Debtor'] = $DebtorSelection['Debtor'];
            $Global->POST['DebtorSelection']['BankAccount'] = $DebtorSelection['BankAccount'];
            $Global->POST['DebtorSelection']['BankReference'] = $DebtorSelection['BankReference'];
            $Global->savePost();
            return $form;
        }
        $tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId);
        if($tblBasketVerification){
            $tblPersonCauser = $tblBasketVerification->getServiceTblPersonCauser();
            $tblItem = $tblBasketVerification->getServiceTblItem();
        } else {
            $tblPersonCauser = false;
            $tblItem = false;
        }

        $tblPersonDebtor = Person::useService()->getPersonById($DebtorSelection['Debtor']);
        $tblPaymentType = Balance::useService()->getPaymentTypeById($DebtorSelection['PaymentType']);
        $tblItemVariant = Item::useService()->getItemVariantById($DebtorSelection['Variant']);
        $ItemPrice = '';
        $Value = '';
        // ItemPrice only if Variant is "-1"
        if($DebtorSelection['Variant'] == '-1'){
            $Value = $ItemPrice = $DebtorSelection['Price'];
        }
        if($tblPaymentType && $tblPaymentType->getName() != 'SEPA-Lastschrift'){
            $tblBankAccount = false;
            $tblBankReference = false;
        } else {
            $tblBankAccount = Debtor::useService()->getBankAccountById($DebtorSelection['BankAccount']);
            $tblBankReference = Debtor::useService()->getBankReferenceById($DebtorSelection['BankReference']);
        }

        $IsChange = false;
        if($tblPersonCauser && $tblPersonDebtor && $tblPaymentType && $tblItem){

            // Change BasketVerification
            if($tblItemVariant){
                if(($tblItemCalculation = Item::useService()->getItemCalculationNowByItemVariant($tblItemVariant))){
                    $Value = $tblItemCalculation->getValue(true);
                }
            }
            // switch false to null
            ($tblBankAccount === false ? $tblBankAccount = null : '');
            ($tblBankReference === false ? $tblBankReference = null : '');
            if(Basket::useService()->changeBasketVerificationDebtor($tblBasketVerification, $tblPersonDebtor,
                $tblPaymentType, $Value, $tblBankAccount, $tblBankReference)){
                // Add Person to PaymentGroup
                if(($tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_DEBTOR))){
                    Group::useService()->addGroupPerson($tblGroup, $tblPersonDebtor);
                }
                $IsChange = true;
            }

            // Add DebtorSelection if not already exist
            if(!Debtor::useService()->getDebtorSelectionByPersonCauserAndItem($tblPersonCauser, $tblItem)
                && $IsChange){
                Debtor::useService()->createDebtorSelection($tblPersonCauser, $tblPersonDebtor,
                    $tblPaymentType, $tblItem,
                    ($tblItemVariant ? $tblItemVariant : null),
                    $ItemPrice,
                    ($tblBankAccount ? $tblBankAccount : null),
                    ($tblBankReference ? $tblBankReference : null));
            }
        } else {
            return new Danger('Die Zuordnung des Beitragszahlers konnte nicht gengelegt werden (Person/Typ/Item)');
        }
        if($IsChange){
            return new Success('Die Zuordnung des Beitragszahlers wurde erfolgreich angelegt')
                .self::pipelineCloseDebtorSelectionModal($tblBasketVerification->getId());
        } else {
            return new Danger('Die Zuordnung des Beitragszahlers konnte nicht gengelegt werden');
        }
    }

    /**
     * @param string $Identifier
     * @param string $BasketVerificationId
     *
     * @return string
     */
    public function showDeleteBasketSelection($Identifier = '', $BasketVerificationId = '')
    {

        $tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId);
        if($tblBasketVerification){

            $ItemName = '';
            $PersonCauser = '';
            $PersonDebtor = '';
            $Price = $tblBasketVerification->getPrice();
            $Quantity = $tblBasketVerification->getQuantity();
            $Summary = $tblBasketVerification->getSummaryPrice();
            if(($tblItem = $tblBasketVerification->getServiceTblItem())){
                $ItemName = $tblItem->getName();
            }
            if(($tblPersonCauser = $tblBasketVerification->getServiceTblPersonCauser())){
                $PersonCauser = $tblPersonCauser->getLastFirstName();
            }
            if(($tblPersonDebtor = $tblBasketVerification->getServiceTblPersonDebtor())){
                $PersonDebtor = $tblPersonDebtor->getLastFirstName();
            }
            //column width
            $left = 4;
            $right = 8;

            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Beitragsart: ', $left),
                new LayoutColumn(new Bold($ItemName), $right),
            ))));
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Beitragsverursacher: ', $left),
                new LayoutColumn(new Bold($PersonCauser), $right),
            ))));
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Beitragszahler: ', $left),
                new LayoutColumn(new Bold($PersonDebtor), $right),
            ))));
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Einzelpreis: ', $left),
                new LayoutColumn(new Bold($Price), $right),
            ))));
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Anzahl: ', $left),
                new LayoutColumn(new Bold($Quantity), $right),
            ))));
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Gesamtpreis: ', $left),
                new LayoutColumn(new Bold($Summary), $right),
            ))));

            return new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Soll die Zahlung wirklich entfernt werden?'
                                , $Content, Panel::PANEL_TYPE_DANGER)
                        ),
                        new LayoutColumn(
                            (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteDebtorSelection($Identifier,
                                    $BasketVerificationId))
                            .new Close('Nein', new Disable())
                        )
                    ))
                )
            );

        } else {
            return new Warning('Zahlungszuweisung wurde nicht gefunden');
        }
    }

    public function deleteDebtorSelection($Identifier = '', $BasketVerificationId = '')
    {

        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))){
            $tblBasket = $tblBasketVerification->getTblBasket();
            Basket::useService()->destroyBasketVerification($tblBasketVerification);
            if($tblBasket){
                return new Success('Abrechnung wurde erfolgreich entfernt').self::pipelineReloadTable($tblBasket->getId(),
                        $Identifier);
            }
        }
        return new Danger('Zahlung konnte nicht entfernt werden');
    }

    /**
     * @param $BasketVerificationId
     *
     * @return string
     */
    public function showEditDebtorSelection($BasketVerificationId)
    {

        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))){
            $Global = $this->getGlobal();
            $tblPaymentType = $tblBasketVerification->getServiceTblPaymentType();
            ($tblPaymentType ? $Global->POST['DebtorSelection']['PaymentType'] = $tblPaymentType->getId() : '');
            // Preis als Individuell eintragen
//            $tblItemVariant = $tblBasketVerification->getServiceTblItemVariant();
//            ($tblItemVariant ? $_POST['DebtorSelection']['Variant'] = $tblItemVariant->getId() : '');
            $Value = $tblBasketVerification->getValue(true);
            ($Value !== '0,00' ? $Global->POST['DebtorSelection']['Price'] = $Value : '0,00');
            $tblPersonDebtor = $tblBasketVerification->getServiceTblPersonDebtor();
            ($tblPersonDebtor ? $Global->POST['DebtorSelection']['Debtor'] = $tblPersonDebtor->getId() : '');
            $tblBankAccount = $tblBasketVerification->getServiceTblBankAccount();
            ($tblBankAccount ? $Global->POST['DebtorSelection']['BankAccount'] = $tblBankAccount->getId()
                : $Global->POST['DebtorSelection']['BankAccount'] = '-1');
            $tblBankReference = $tblBasketVerification->getServiceTblBankReference();
            ($tblBankReference ? $Global->POST['DebtorSelection']['BankReference'] = $tblBankReference->getId() : '');
            $Global->savePost();
        }

        // info of Handling with insert Data
        $InfoMessage = new Info('Daten werden '.new Bold('für die aktuelle Abrechnung und in den Einstellungen').' übernommen '
            .new ToolTip(new InfoIcon(), 'Es sind noch keine Daten vorhanden.'));
        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))){
            $tblItem = $tblBasketVerification->getServiceTblItem();
            $tblPersonCauser = $tblBasketVerification->getServiceTblPersonCauser();
            if($tblItem && $tblPersonCauser){
                if(Debtor::useService()->getDebtorSelectionByPersonCauserAndItem($tblPersonCauser, $tblItem)){
                    $InfoMessage = new Info('Daten werden '.new Bold('nur für die aktuelle Abrechnung').' übernommen '
                        .new ToolTip(new InfoIcon(),
                            'Daten für neue Abrechnung werden aus den vorhandenen Einstellungen gezogen.'));
                }
            }
        }

        return $InfoMessage.new Well(self::formDebtorSelection($BasketVerificationId));
    }

}