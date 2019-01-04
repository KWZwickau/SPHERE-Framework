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
use SPHERE\Common\Frontend\Ajax\Receiver\InlineReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
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
        $Dispatcher->registerMethod('getItemQuantity');
        $Dispatcher->registerMethod('getItemSummary');
        $Dispatcher->registerMethod('getDebtor');

        //Price
        $Dispatcher->registerMethod('showEditPrice');
        $Dispatcher->registerMethod('saveEditPrice');
        // DebtorSelection
        $Dispatcher->registerMethod('showEditDebtorSelection');
        $Dispatcher->registerMethod('saveEditDebtorSelection');

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

        return (new ModalReceiver($Header, new Close()))->setIdentifier('Modal' . $Identifier);
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return InlineReceiver
     */
    public static function receiverItemPrice($Content = '', $Identifier = '')
    {

        return (new InlineReceiver($Content))->setIdentifier('Price' . $Identifier);
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return InlineReceiver
     */
    public static function receiverItemQuantity($Content = '', $Identifier = '')
    {

        return (new InlineReceiver($Content))->setIdentifier('Quantity' . $Identifier);
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return InlineReceiver
     */
    public static function receiverItemSummary($Content = '', $Identifier = '')
    {

        return (new InlineReceiver($Content))->setIdentifier('Summary' . $Identifier);
    }

    /**
     * @param int|string $BasketVerificationId
     * @param array      $Price
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditPrice($BasketVerificationId = '')
    {

        $Receiver = self::receiverModal();
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showEditPrice'
        ));
        $Emitter->setPostPayload(array(
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
    public static function pipelineSaveEditPrice($BasketVerificationId = '')
    {

        $Receiver = self::receiverModal();
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveEditPrice'
        ));
        $Emitter->setPostPayload(array(
            'BasketVerificationId' => $BasketVerificationId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $BasketVerificationId
     * @param array  $Price
     *
     * @return string
     */
    public function showEditPrice($BasketVerificationId = '')
    {

        return new Well($this->formPrice($BasketVerificationId));
    }

    /**
     * @param string $BasketVerificationId
     * @param array  $Price
     *
     * @return string
     */
    public function saveEditPrice($BasketVerificationId = '', $Price = array())
    {

//        // Handle error's
//        if($form = $this->checkInputDebtorSelection($Identifier, $PersonId, $ItemId, $DebtorSelectionId,
//            $DebtorSelection)) {
//            // display Errors on form
//            $Global = $this->getGlobal();
//            $Global->POST['DebtorSelection']['PaymentType'] = $DebtorSelection['PaymentType'];
//            $Global->POST['DebtorSelection']['Variant'] = $DebtorSelection['Variant'];
//            $Global->POST['DebtorSelection']['Price'] = $DebtorSelection['Price'];
//            $Global->POST['DebtorSelection']['Debtor'] = $DebtorSelection['Debtor'];
//            $Global->POST['DebtorSelection']['BankAccount'] = $DebtorSelection['BankAccount'];
//            $Global->POST['DebtorSelection']['BankReference'] = $DebtorSelection['BankReference'];
//            $Global->savePost();
//            return $form;
//        }
        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))) {
            Basket::useService()->changeBasketVerification($tblBasketVerification, $Price['Value'], $Price['Quantity']);
        }

        return new Success('Die Zuordnung des Beitragszahlers erfolgreich geändert') . self::pipelineClosePriceModal($BasketVerificationId);

//        return ($IsChange
//            ? new Success('Die Zuordnung des Beitragszahlers erfolgreich geändert') . self::pipelineCloseModal($Identifier,
//                $PersonId, $ItemId)
//            : new Danger('Die Zuordnung des Beitragszahlers konnte nicht geändert werden'));
    }

    /**
     * @param string $BasketVerificationId
     *
     * @return Pipeline
     */
    public static function pipelineClosePriceModal($BasketVerificationId = '')
    {
        $Pipeline = new Pipeline();
        // reload columns
        // reload price column
        $Emitter = new ServerEmitter(self::receiverItemPrice('', $BasketVerificationId), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getItemPrice'
        ));
        $Emitter->setPostPayload(array(
            'BasketVerificationId' => $BasketVerificationId
        ));
        $Pipeline->appendEmitter($Emitter);
        // reload quantity column
        $Emitter = new ServerEmitter(self::receiverItemQuantity('', $BasketVerificationId), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getItemQuantity'
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

        $Pipeline->appendEmitter((new CloseModal(self::receiverModal('', $BasketVerificationId)))->getEmitter());
        return $Pipeline;
    }

    /**
     * @param $BasketVerificationId
     *
     * @return string
     */
    public function getItemPrice($BasketVerificationId)
    {

        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))) {
            return $tblBasketVerification->getPrice();
        }
        return '';
    }

    /**
     * @param $BasketVerificationId
     *
     * @return int|string
     */
    public function getItemQuantity($BasketVerificationId)
    {

        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))) {
            return $tblBasketVerification->getQuantity();
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

        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))) {
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

        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))) {
            if(($tblPerson = $tblBasketVerification->getServiceTblPersonDebtor()))
            return $tblPerson->getLastFirstName();
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

        return (new InlineReceiver($Content))->setIdentifier('Debtor' . $Identifier);
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
    ) {

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
     * @param string     $Identifier
     * @param string     $PersonId
     * @param string     $ItemId
     * @param int|string $DebtorSelectionId
     *
     * @return Pipeline
     */
    public static function pipelineSaveEditDebtorSelection($Identifier = '', $PersonId = '', $ItemId = '',
        $DebtorSelectionId = ''
    ) {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(true);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveEditDebtorSelection'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'        => $Identifier,
            'PersonId'          => $PersonId,
            'ItemId'            => $ItemId,
            'DebtorSelectionId' => $DebtorSelectionId
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
        $Emitter = new ServerEmitter(self::receiverItemPrice('', $BasketVerificationId), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getDebtor'
        ));
        $Emitter->setPostPayload(array(
            'BasketVerificationId' => $BasketVerificationId
        ));
        $Pipeline->appendEmitter($Emitter);
        // ToDO chance also Price with selectet Entry
        // reload Price column
        $Emitter = new ServerEmitter(self::receiverItemQuantity('', $BasketVerificationId), self::getEndpoint());
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

        $Pipeline->appendEmitter((new CloseModal(self::receiverModal('', $BasketVerificationId)))->getEmitter());
        return $Pipeline;
    }

    /**
     * @param string $BasketVerificationId
     * @param array  $Price
     *
     * @return Form
     */
    public function formPrice($BasketVerificationId = '')
    {

        $SaveButton = new Primary('Speichern', self::getEndpoint(), new Save());
        $SaveButton->ajaxPipelineOnClick(self::pipelineSaveEditPrice($BasketVerificationId));

        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))) {
            $_POST['Price']['Value'] = $tblBasketVerification->getValue(true);
            $_POST['Price']['Quantity'] = $tblBasketVerification->getQuantity();
            $_POST['Price']['Summary'] = $tblBasketVerification->getSummaryPrice();
        }

        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new TextField('Price[Value]', '0,00', 'Einzelpreis')
                        , 6),
                    new FormColumn(
                        new NumberField('Price[Quantity]', '1', 'Einzelpreis')
                        , 6),
                    new FormColumn(
                        $SaveButton
                    )
                ))
            )
        );
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
        foreach ($tblPaymentTypeAll as $tblPaymentType) {
            $PaymentTypeList[$tblPaymentType->getId()] = $tblPaymentType->getName();
            if($tblPaymentType->getName() == 'SEPA-Lastschrift'/*'Bar' // Test*/) {
                if(!isset($_POST['DebtorSelection']['PaymentType'])) {
                    $_POST['DebtorSelection']['PaymentType'] = $tblPaymentType->getId();
                }
            }
        }

        //get First Variant to Select
        $PostVariantId = '-1';
        if(!isset($_POST['DebtorSelection']['Variant'])) {
            $_POST['DebtorSelection']['Variant'] = $PostVariantId;
        }
        $RadioBoxListVariant = array();
        $PersonDebtorList = array();
        $SelectBoxDebtorList = array();
        $tblBankReferenceList = array();
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
                if(($tblItemVariantList = Item::useService()->getItemVariantByItem($tblItem))) {
                    foreach ($tblItemVariantList as $tblItemVariant) {
                        $PriceString = new DangerText('Nicht verfügbar');
                        if(($tblItemCalculation = Item::useService()->getItemCalculationNowByItemVariant($tblItemVariant))) {
                            $PriceString = $tblItemCalculation->getPriceString();
                        }

                        $RadioBoxListVariant[] = new RadioBox('DebtorSelection[Variant]',
                            $tblItemVariant->getName() . ': ' . $PriceString, $tblItemVariant->getId());
                    }
                    $RadioBoxListVariant[] = new RadioBox('DebtorSelection[Variant]',
                        'Individuelle Preiseingabe' . new TextField('DebtorSelection[Price]', '', ''), -1);
                }
            }
            if($tblPersonCauser
                && $tblRelationshipType = Relationship::useService()->getTypeByName('Sorgeberechtigt')) {
                $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_DEBTOR);
                // is Causer Person in Group "Bezahler"
                if(Group::useService()->getMemberByPersonAndGroup($tblPersonCauser, $tblGroup)) {
                    $DeborNumber = $this->getDebtorNumberByPerson($tblPersonCauser);
                    $SelectBoxDebtorList[$tblPersonCauser->getId()] = $tblPersonCauser->getLastFirstName() . ' ' . $DeborNumber;
                    $PersonDebtorList[] = $tblPersonCauser;
                }
                if(($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPersonCauser,
                    $tblRelationshipType))) {
                    foreach ($tblRelationshipList as $tblRelationship) {
                        if(($tblPersonRel = $tblRelationship->getServiceTblPersonFrom()) && $tblPersonRel->getId() !== $tblPersonCauser->getId()) {
                            // is Person in Group "Bezahler"
                            if(Group::useService()->getMemberByPersonAndGroup($tblPersonRel, $tblGroup)) {
                                $DeborNumber = $this->getDebtorNumberByPerson($tblPersonRel);
                                $SelectBoxDebtorList[$tblPersonRel->getId()] = $tblPersonRel->getLastFirstName() . ' ' . $DeborNumber;
                                $PersonDebtorList[] = $tblPersonRel;
                            }
                        }
                    }
                    // ToDO Sorgeberechtigte immer anzeigen oder ganz deaktivieren?
                    // Bezahler ohne Gruppe (z.B. Sorgeberechtigte, die ohne Konto bezhalen (Bar/Überweisung))
                    if(empty($SelectBoxDebtorList)) {
                        $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_CUSTODY);
                        foreach ($tblRelationshipList as $tblRelationship) {
                            if(($tblPersonRel = $tblRelationship->getServiceTblPersonFrom()) && $tblPersonRel->getId() !== $tblPersonCauser->getId()) {
                                // is Person in Group "Sorgeberechtigte"
                                if(Group::useService()->getMemberByPersonAndGroup($tblPersonRel, $tblGroup)) {
                                    $DeborNumber = $this->getDebtorNumberByPerson($tblPersonRel);
                                    $SelectBoxDebtorList[$tblPersonRel->getId()] = $tblPersonRel->getLastFirstName() . ' ' . $DeborNumber;
                                }
                            }
                        }
                    }
                }
                if(($tblRelationshipType = Relationship::useService()->getTypeByName('Beitragszahler'))) {
                    if(($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPersonCauser,
                        $tblRelationshipType))) {
                        foreach ($tblRelationshipList as $tblRelationship) {
                            if(($tblPersonRel = $tblRelationship->getServiceTblPersonFrom()) && $tblPersonRel->getId() !== $tblPersonCauser->getId()) {
                                // is Person in Group "Bezahler"
                                if(Group::useService()->getMemberByPersonAndGroup($tblPersonRel, $tblGroup)) {
                                    $DeborNumber = $this->getDebtorNumberByPerson($tblPersonRel);
                                    $SelectBoxDebtorList[$tblPersonRel->getId()] = $tblPersonRel->getLastFirstName() . ' ' . $DeborNumber;
                                    $PersonDebtorList[] = $tblPersonRel;
                                }
                            }
                        }
                    }
                }
            }

            if($tblPersonCauser){
                $tblBankReferenceList = Debtor::useService()->getBankReferenceByPerson($tblPersonCauser);
                if($tblBankReferenceList) {
                    // Post first entry if PaymentType = SEPA-Lastschrift
                    if(isset($_POST['DebtorSelection']['PaymentType'])
                        && ($tblPaymentType = Balance::useService()->getPaymentTypeById($_POST['DebtorSelection']['PaymentType']))
                        && $tblPaymentType->getName() == 'SEPA-Lastschrift') {
                        if(!isset($_POST['DebtorSelection']['BankReference'])) {
                            $_POST['DebtorSelection']['BankReference'] = $tblBankReferenceList[0]->getId();
                        }
                    }
                }
            }
        }

        $PostBankAccountId = false;
        $RadioBoxListBankAccount = array();
        // no BankAccount available
        if(!isset($_POST['DebtorSelection']['BankAccount'])) {
            $_POST['DebtorSelection']['BankAccount'] = '-1';
        }
        $RadioBoxListBankAccount['-1'] = new RadioBox('DebtorSelection[BankAccount]'
            , 'kein Konto', -1);
        if(!empty($PersonDebtorList)) {
            /** @var TblPerson $PersonDebtor */
            foreach ($PersonDebtorList as $PersonDebtor) {
                if(($tblBankAccountList = Debtor::useService()->getBankAccountAllByPerson($PersonDebtor))) {
                    foreach ($tblBankAccountList as $tblBankAccount) {
                        if(!$PostBankAccountId) {
                            $PostBankAccountId = $tblBankAccount->getId();
                            if(isset($_POST['DebtorSelection']['PaymentType'])
                                && !isset($_POST['DebtorSelection']['BankAccount'])
                                && ($tblPaymentType = Balance::useService()->getPaymentTypeById($_POST['DebtorSelection']['PaymentType']))
                                && $tblPaymentType->getName() == 'SEPA-Lastschrift') {
                                // override Post with first found BankAccount
                                $_POST['DebtorSelection']['BankAccount'] = $PostBankAccountId;
                            }
                        }
                        $RadioBoxListBankAccount[$tblBankAccount->getId()] = new RadioBox('DebtorSelection[BankAccount]'
                            , $tblBankAccount->getOwner() . '<br/>' . $tblBankAccount->getBankName() . '<br/>'
                            . $tblBankAccount->getIBANFrontend()
                            , $tblBankAccount->getId());
                    }
                }
            }
        }

        return (new Form(
            new FormGroup(array(
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
        if($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_IS_DEBTOR_NUMBER_NEED)) {
            if($tblSetting->getValue() == 1) {
                $IsDebtorNumberNeed = true;
            }
        }

        $DeborNumber = ($IsDebtorNumberNeed
            ? '(keine Debitor-Nr.)'
            : '');
        if(($tblDebtorNumberList = Debtor::useService()->getDebtorNumberByPerson($tblPerson))) {
            $DebtorNumberList = array();
            foreach ($tblDebtorNumberList as $tblDebtorNumber) {
                $DebtorNumberList[] = $tblDebtorNumber->getDebtorNumber();
            }
            $DeborNumber = implode(', ', $DebtorNumberList);
            $DeborNumber = '(' . $DeborNumber . ')';
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
    ) {

        $Error = false;
        $Warning = '';
        $form = $this->formDebtorSelection($BasketVerificationId);
        if(isset($DebtorSelection['PaymentType']) && empty($DebtorSelection['PaymentType'])) {
            $form->setError('DebtorSelection[PaymentType]', 'Bitte geben Sie eine Zahlungsart an');
            $Error = true;
        }
        if(isset($DebtorSelection['Variant']) && empty($DebtorSelection['Variant'])) {
            $Warning .= new Warning('Bitte geben Sie eine Bezahlvariante an, steht keine zur Auswahl, stellen Sie bitte eine bei den Beitragsarten ein.');
            $form->setError('DebtorSelection[Variant]', 'Bitte geben Sie eine Bezahlvariante an');
            $Error = true;
        } elseif(isset($DebtorSelection['Variant']) && $DebtorSelection['Variant'] == '-1') {
            // is price empty (is requiered vor no Variant)
            if(isset($DebtorSelection['Price']) && empty($DebtorSelection['Price'])) {
                $Warning .= new Warning('Bitte geben Sie einen individuellen Preis an');
//                $form->setError('DebtorSelection[Price]', 'Bitte geben Sie einen Individuellen Preis an');
                $Error = true;
            } elseif(isset($DebtorSelection['Price']) && !is_numeric(str_replace(',', '.',
                    $DebtorSelection['Price']))) {
                $Warning .= new Warning('Bitte geben Sie eine ' . new Bold('Zahl') . ' als individuellen Preis an');
//                $form->setError('DebtorSelection[Price]', 'Bitte geben Sie einen Individuellen Preis an');
                $Error = true;
            }
        }
        if(isset($DebtorSelection['Debtor']) && empty($DebtorSelection['Debtor'])) {
            $form->setError('DebtorSelection[Debtor]', 'Bitte geben Sie einen Bezahler an');
            $Error = true;
        }

        if(($tblPaymentType = Balance::useService()->getPaymentTypeById($DebtorSelection['PaymentType']))) {
            $IsSepaAccountNeed = false;
            if($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_IS_SEPA_ACCOUNT_NEED)) {
                if($tblSetting->getValue() == 1) {
                    $IsSepaAccountNeed = true;
                }
            }
            if($tblPaymentType->getName() == 'SEPA-Lastschrift') {
                if($IsSepaAccountNeed) {
                    if(isset($DebtorSelection['BankAccount']) && empty($DebtorSelection['BankAccount'])) {
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


        if($Error) {
            // Debtor::useFrontend()->getPersonPanel($PersonId).
            return new Well($Warning . $form);
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
    ) {

        // Handle error's
        if($form = $this->checkInputDebtorSelection($BasketVerificationId, $DebtorSelection)) {

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
        if($DebtorSelection['Variant'] == '-1') {
            $Value = $ItemPrice = $DebtorSelection['Price'];
        }
        if($tblPaymentType && $tblPaymentType->getName() != 'SEPA-Lastschrift') {
            $tblBankAccount = false;
            $tblBankReference = false;
        } else {
            $tblBankAccount = Debtor::useService()->getBankAccountById($DebtorSelection['BankAccount']);
            $tblBankReference = Debtor::useService()->getBankReferenceById($DebtorSelection['BankReference']);
        }

        $IsChange = false;
        if($tblPersonCauser && $tblPersonDebtor && $tblPaymentType && $tblItem) {

            // Change BasketVerifivation
            if($tblItemVariant) {
                if(($tblItemCalculation = Item::useService()->getItemCalculationNowByItemVariant($tblItemVariant))){
                    $Value = $tblItemCalculation->getValue(true);
                }

            }
            if(Basket::useService()->changeBasketVerificationDebtor($tblBasketVerification, $tblPersonDebtor,
                $tblPaymentType, $tblBankAccount, $tblBankReference, $Value)){
                $IsChange = true;
            }

            // Add DebtorSelection if not already exist
            if(Debtor::useService()->getDebtorSelectionByPersonCauserAndItem($tblPersonCauser, $tblItem)){
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
            return new Success('Die Zuordnung des Beitragszahlers wurde erfolgreich angelegt');
        } else {
            return new Danger('Die Zuordnung des Beitragszahlers konnte nicht gengelegt werden');
        }
    }

    /**
     * @param $BasketVerificationId
     *
     * @return string
     */
    public function showEditDebtorSelection($BasketVerificationId)
    {

        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId) )) {
            $Global = $this->getGlobal();
            $tblPaymentType = $tblBasketVerification->getServiceTblPaymentType();
            ($tblPaymentType ? $Global->POST['DebtorSelection']['PaymentType'] = $tblPaymentType->getId() : '');
            //ToDO Preis ermitteln oder als Individuell eintragen
//            $tblItemVariant = $tblBasketVerification->getServiceTblItemVariant();
//            ($tblItemVariant ? $_POST['DebtorSelection']['Variant'] = $tblItemVariant->getId() : '');
            $Value = $tblBasketVerification->getValue(true);
            ($Value !== '0,00' ? $Global->POST['DebtorSelection']['Price'] = $Value : '');
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
        $InfoMessage = new Info('Daten werden nur für die aktuelle Abrechnung übernommen,
                     Daten für neue Abrechnung werden aus den vorhandenen Einstellungen gezogen.');
        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))){
            $tblItem = $tblBasketVerification->getServiceTblItem();
            $tblPersonCauser = $tblBasketVerification->getServiceTblPersonCauser();
            if($tblItem && $tblPersonCauser){
                if(Debtor::useService()->getDebtorSelectionByPersonCauserAndItem($tblPersonCauser, $tblItem)) {
                    $InfoMessage = new Info('Daten werden für die aktuelle Abrechnung und in den Einstellungen übernommen,
                     da diese noch nicht bei dem Beitragsverursacher vorhanden sind.');
                }
            }
        }

        return $InfoMessage.new Well(self::formDebtorSelection($BasketVerificationId));
    }

}