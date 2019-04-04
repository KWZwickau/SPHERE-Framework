<?php

namespace SPHERE\Application\Billing\Bookkeeping\Basket;

use SPHERE\Application\Billing\Accounting\Creditor\Creditor;
use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblBankAccount;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblBankReference;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblDebtorSelection;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Data;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketItem;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketVerification;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Setup;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Billing\Inventory\Setting\Service\Entity\TblSetting;
use SPHERE\Application\Billing\Inventory\Setting\Setting;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Billing\Bookkeeping\Basket
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {

        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param $Id
     *
     * @return bool|TblBasket
     */
    public function getBasketById($Id)
    {

        return (new Data($this->getBinding()))->getBasketById($Id);
    }

    /**
     * @param string      $Name
     * @param string|bool $Month
     * @param string|bool $Year
     *
     * @return bool|TblBasket
     */
    public function getBasketByName($Name, $Month = false, $Year = false)
    {

        return (new Data($this->getBinding()))->getBasketByName($Name, $Month, $Year);
    }

    /**
     * @param $Id
     *
     * @return bool|TblBasketItem
     */
    public function getBasketItemById($Id)
    {

        return (new Data($this->getBinding()))->getBasketItemById($Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblBasketVerification
     */
    public function getBasketVerificationById($Id)
    {

        return (new Data($this->getBinding()))->getBasketVerificationById($Id);
    }

    /**
     * @return bool|TblBasket[]
     */
    public function getBasketAll()
    {

        return (new Data($this->getBinding()))->getBasketAll();
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool|TblItem[]
     */
    public function getItemAllByBasket(TblBasket $tblBasket)
    {

        $tblBasketItemList = $this->getBasketItemAllByBasket($tblBasket);
        $tblItemList = array();
        if($tblBasketItemList){
            foreach($tblBasketItemList as $tblBasketItem) {
                if(($tblItem = $tblBasketItem->getServiceTblItem())){
                    $tblItemList[] = $tblItem;
                }
            }

        }
        return (empty($tblItemList) ? false : $tblItemList);
    }

    /**
     * @param TblItem $tblItem
     *
     * @return bool|TblBasketItem[]
     */
    public function getBasketItemAllByItem(TblItem $tblItem)
    {

        return (new Data($this->getBinding()))->getBasketItemAllByItem($tblItem);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool|TblBasketItem[]
     */
    public function getBasketItemAllByBasket(TblBasket $tblBasket)
    {

        return (new Data($this->getBinding()))->getBasketItemAllByBasket($tblBasket);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return false|TblBasketVerification[]
     */
    public function getBasketVerificationAllByBasket(TblBasket $tblBasket)
    {

        return (new Data($this->getBinding()))->getBasketVerificationAllByBasket($tblBasket);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return false|TblBasketVerification[]
     */
    public function getBasketVerificationAllByBankReference(TblBankReference $tblBankReference)
    {

        return (new Data($this->getBinding()))->getBasketVerificationAllByBankReference($tblBankReference);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return false|\SPHERE\System\Database\Fitting\Element
     */
    public function countDebtorSelectionCountByBasket(TblBasket $tblBasket)
    {

        return (new Data($this->getBinding()))->countDebtorSelectionCountByBasket($tblBasket);
    }

    /**
     * @param string $Name
     * @param string $Description
     * @param string $Year
     * @param string $Month
     * @param string $TargetTime
     * @param string $CreditorId
     *
     * @return TblBasket
     */
    public function createBasket($Name = '', $Description = '', $Year = '', $Month = '', $TargetTime = '', $CreditorId = '')
    {

        if($TargetTime){
            $TargetTime = new \DateTime($TargetTime);
        } else {
            // now if no input (fallback)
            $TargetTime = new \DateTime();
        }
        // 0 (nicht Ausgewählt) or false to null
        $tblCreditor = false;
        if($CreditorId !== '0'){
            $tblCreditor = Creditor::useService()->getCreditorById($CreditorId);
        }
        if(!$tblCreditor){
            $tblCreditor = null;
        }
        return (new Data($this->getBinding()))->createBasket($Name, $Description, $Year, $Month, $TargetTime, $tblCreditor);
    }

    /**
     * @param TblBasket $tblBasket
     * @param TblItem   $tblItem
     *
     * @return TblBasketItem
     */
    public function createBasketItem(TblBasket $tblBasket, TblItem $tblItem)
    {
        return (new Data($this->getBinding()))->createBasketItem($tblBasket, $tblItem);
    }

    /**
     * @param $tblItem
     *
     * @return TblGroup[]|bool
     */
    public function getGroupListByItem($tblItem)
    {

        $tblGroupList = array();
        if(($tblItemGroupList = Item::useService()->getItemGroupByItem($tblItem))){
            foreach($tblItemGroupList as $tblItemGroup) {
                $tblGroupList[] = $tblItemGroup->getServiceTblGroup();
            }
        }

        return (!empty($tblGroupList) ? $tblGroupList : false);
    }

    /**
     * @param TblGroup[]|bool $tblGroupList
     *
     * @return array|bool
     */
    public function getPersonListByGroupList($tblGroupList)
    {

        $tblPersonList = array();
        if($tblGroupList){
            foreach($tblGroupList as $tblGroup) {
                if($tblPersonFromGroup = Group::useService()->getPersonAllByGroup($tblGroup)){
                    foreach($tblPersonFromGroup as $tblPersonFrom) {
                        $tblPersonList[] = $tblPersonFrom;
                    }
                }
            }
        }
        return (!empty($tblPersonList) ? $tblPersonList : false);
    }

    /**
     * @param TblBasket $tblBasket
     * @param TblItem   $tblItem
     *
     * @return bool
     */
    public function createBasketVerificationBulk(TblBasket $tblBasket, TblItem $tblItem)
    {

        $tblGroupList = $this->getGroupListByItem($tblItem);

        $tblPersonList = $this->getPersonListByGroupList($tblGroupList);

        $IsSepa = true;
        if($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_IS_SEPA)){
            $IsSepa = $tblSetting->getValue();
        }

        $DebtorDataArray = array();
        if($tblPersonList){
            /** @var TblPerson $tblPerson */
            foreach($tblPersonList as $tblPerson) {
                if(($tblDebtorSelectionList = Debtor::useService()->getDebtorSelectionByPersonCauserAndItem($tblPerson,
                    $tblItem))){
                    foreach($tblDebtorSelectionList as $tblDebtorSelection) {
                        $Error = false;
                        $IsNoDebtorSelection = false;
                        // entfernen aller DebtorSelection zu welchen es schon in der aktuellen Rechnungsphase Rechnungen gibt.
                        if(Invoice::useService()->getInvoiceByPersonCauserAndItemAndYearAndMonth($tblPerson, $tblItem,
                            $tblBasket->getYear(), $tblBasket->getMonth())){
                            // vorhandene Rechnung -> keine Zahlungszuweisung erstellen!
                            $Error = true;
                        }
                        $Item = array();
                        if(!$tblDebtorSelection->getServiceTblPersonCauser()){
                            //BasketVerification doesn't work without Causer
                            $Item['Causer'] = '';
                            $Error = true;
                        } else {
                            $Item['Causer'] = $tblDebtorSelection->getServiceTblPersonCauser()->getId();
                        }
                        if(!$tblDebtorSelection->getServiceTblPersonDebtor()){
                            $Item['Debtor'] = '';
                        } else {
                            $Item['Debtor'] = $tblDebtorSelection->getServiceTblPersonDebtor()->getId();
                        }
                        // insert payment from DebtorSelection
                        if(!$tblDebtorSelection->getTblBankAccount()){
                            $Item['BankAccount'] = null;
                        } else {
                            $Item['BankAccount'] = $tblDebtorSelection->getTblBankAccount()->getId();
                        }
                        if(!$tblDebtorSelection->getTblBankReference()){
                            $Item['BankReference'] = null;
                        } else {
                            $Item['BankReference'] = $tblDebtorSelection->getTblBankReference()->getId();
                        }
                        $Item['PaymentType'] = $tblDebtorSelection->getServiceTblPaymentType()->getId();
                        // default special price value
                        $Item['Price'] = $tblDebtorSelection->getValue();
                        // change to selected variant
                        if(($tblItemVariant = $tblDebtorSelection->getServiceTblItemVariant())){
                            if(($tblItemCalculation = Item::useService()->getItemCalculationByDate($tblItemVariant, new \DateTime($tblBasket->getTargetTime())))){
                                $Item['Price'] = $tblItemCalculation->getValue();
                            }
                        }
                        $Item['DebtorSelection'] = $tblDebtorSelection->getId();
                        // Entfernen aller DebtorSelection (SEPA-Lastschrift) welche keine gültige Sepa-Mandatsreferenznummer besitzen.
                        if($tblDebtorSelection->getServiceTblPaymentType()->getName() == 'SEPA-Lastschrift'
                        && $IsSepa){
                            if(($tblBankReference = $tblDebtorSelection->getTblBankReference())){
                                if(new \DateTime($tblBankReference->getReferenceDate()) > new \DateTime($tblBasket->getTargetTime())){
                                    // Datum der Referenz liegt noch in der Zukunft;
                                    $IsNoDebtorSelection = true;
                                }
                            } else {
                                // Keine gültige Mandatsreferenznummer
                                $IsNoDebtorSelection = true;
                            }
                        }
                        if($IsNoDebtorSelection){
                            // entry without valid BankRef
                            $Item['Causer'] = $tblPerson->getId();
                            $Item['Debtor'] = '';
                            $Item['BankAccount'] = null;
                            $Item['BankReference'] = null;
                            $Item['PaymentType'] = null;
                            $Item['DebtorSelection'] = null;
                            // default special price value
                            $Item['Price'] = '0';
                        }
                        if(!$Error){
                            array_push($DebtorDataArray, $Item);
                        }
                    }
                } else {
                    $Error = false;
                    // entfernen aller DebtorSelection zu welchen es schon in der aktuellen Rechnungsphase Rechnungen gibt.
                    if(Invoice::useService()->getInvoiceByPersonCauserAndItemAndYearAndMonth($tblPerson, $tblItem,
                        $tblBasket->getYear(), $tblBasket->getMonth())){
                        // vorhandene Rechnung -> keine Zahlungszuweisung erstellen!
                        $Error = true;
                    }
                    // entry without DebtorSelection
                    $Item['Causer'] = $tblPerson->getId();
                    $Item['Debtor'] = '';
                    $Item['BankAccount'] = null;
                    $Item['BankReference'] = null;
                    $Item['PaymentType'] = null;
                    $Item['DebtorSelection'] = null;
                    // default special price value
                    $Item['Price'] = '0';
                    if(!$Error){
                        array_push($DebtorDataArray, $Item);
                    }
                }
            }
        }
        if(!empty($DebtorDataArray)){
            return (new Data($this->getBinding()))->createBasketVerificationBulk($tblBasket, $tblItem,
                $DebtorDataArray);
        }
        return false;
    }

    /**
     * @param TblBasket $tblBasket
     * @param string    $Name
     * @param string    $Description
     * @param string    $TargetTime
     * @param string    $CreditorId
     *
     * @return IFormInterface|string
     */
    public function changeBasket(TblBasket $tblBasket, $Name, $Description, $TargetTime, $CreditorId = '')
    {

        // String to DateTime object
        $TargetTime = new \DateTime($TargetTime);
        // 0 (nicht Ausgewählt) or false to null
        $tblCreditor = false;
        if($CreditorId !== '0'){
            $tblCreditor = Creditor::useService()->getCreditorById($CreditorId);
        }
        if(!$tblCreditor){
            $tblCreditor = null;
        }

        return (new Data($this->getBinding()))->updateBasket($tblBasket, $Name, $Description, $TargetTime, $tblCreditor);
    }

    /**
     * @param TblBasket $tblBasket
     * @param bool      $IsDone
     *
     * @return bool
     */
    public function changeBasketDone(TblBasket $tblBasket, $IsDone = true)
    {

        return (new Data($this->getBinding()))->updateBasketDone($tblBasket, $IsDone);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool
     */
    public function changeBasketDoneSepa(TblBasket $tblBasket)
    {

        $PersonName = 'Person nicht hinterlegt!';
        if(($tblAccount = Account::useService()->getAccountBySession())){
            if(($tblPersonList = Account::useService()->getPersonAllByAccount($tblAccount))){
                /** @var TblPerson $tblPerson */
                $tblPerson = current($tblPersonList);
                $PersonName = substr($tblPerson->getFirstName(), 0, 1).'. '.$tblPerson->getLastName();
            }
        }

        return (new Data($this->getBinding()))->updateBasketSepa($tblBasket, $PersonName);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool
     */
    public function changeBasketDoneDatev(TblBasket $tblBasket)
    {

        $PersonName = 'Person nicht hinterlegt!';
        if(($tblAccount = Account::useService()->getAccountBySession())){
            if(($tblPersonList = Account::useService()->getPersonAllByAccount($tblAccount))){
                /** @var TblPerson $tblPerson */
                $tblPerson = current($tblPersonList);
                $PersonName = substr($tblPerson->getFirstName(), 0, 1).'. '.$tblPerson->getLastName();
            }
        }

        return (new Data($this->getBinding()))->updateBasketDatev($tblBasket, $PersonName);
    }

    /**
     * @param TblBasketVerification $tblBasketVerification
     * @param string                $Quantity
     *
     * @return bool
     */
    public function changeBasketVerificationInQuantity(TblBasketVerification $tblBasketVerification, $Quantity)
    {

        return (new Data($this->getBinding()))->updateBasketVerificationInQuantity($tblBasketVerification, $Quantity);
    }

    /**
     * @param TblBasketVerification $tblBasketVerification
     * @param TblDebtorSelection    $tblDebtorSelection
     *
     * @return bool
     */
    public function changeBasketVerificationInDebtorSelection(TblBasketVerification $tblBasketVerification,
        TblDebtorSelection $tblDebtorSelection)
    {

        return (new Data($this->getBinding()))->updateBasketVerificationInDebtorSelection($tblBasketVerification, $tblDebtorSelection);
    }

    /**
     * @param TblBasketVerification $tblBasketVerification
     * @param TblPerson             $tblPersonDebtor
     * @param TblPaymentType        $tblPaymentType
     * @param string                $Value
     * @param TblBankAccount|null   $tblBankAccount
     * @param TblBankReference|null $tblBankReference
     *
     * @return bool
     */
    public function changeBasketVerificationDebtor(
        TblBasketVerification $tblBasketVerification,
        TblPerson $tblPersonDebtor,
        TblPaymentType $tblPaymentType,
        $Value = '0',
        TblBankAccount $tblBankAccount = null,
        TblBankReference $tblBankReference = null

    ){

        $Value = str_replace(',', '.', $Value);
        return (new Data($this->getBinding()))->updateBasketVerificationDebtor($tblBasketVerification, $tblPersonDebtor,
            $tblPaymentType, $Value, $tblBankAccount, $tblBankReference);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool
     */
    public function destroyBasket(TblBasket $tblBasket)
    {

        // remove all BasketItem
        $this->destroyBasketItemBulk($tblBasket);
        // remove all BasketVerification
        $this->destroyBasketVerificationBulk($tblBasket);

        return (new Data($this->getBinding()))->destroyBasket($tblBasket);
    }

    /**
     * @param TblBasketItem $tblBasketItem
     *
     * @return string
     */
    public function destroyBasketItem(TblBasketItem $tblBasketItem)
    {

        return (new Data($this->getBinding()))->destroyBasketItem($tblBasketItem);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool
     */
    public function destroyBasketItemBulk(TblBasket $tblBasket)
    {

        $BasketItemIdList = array();
        if(($tblBasketItemList = Basket::useService()->getBasketItemAllByBasket($tblBasket))){
            foreach($tblBasketItemList as $tblBasketItem) {
                $BasketItemIdList[$tblBasketItem->getId()] = $tblBasketItem->getId();
            }
        }
        return (new Data($this->getBinding()))->destroyBasketItemBulk($BasketItemIdList);
    }

    /**
     * @param TblBasketVerification $tblBasketVerification
     *
     * @return string
     */
    public function destroyBasketVerification(TblBasketVerification $tblBasketVerification)
    {

        return (new Data($this->getBinding()))->destroyBasketVerification($tblBasketVerification);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool
     */
    public function destroyBasketVerificationBulk(TblBasket $tblBasket)
    {

        $BasketVerificationIdList = array();
        if(($tblBasketVerificationList = Basket::useService()->getBasketVerificationAllByBasket($tblBasket))){
            foreach($tblBasketVerificationList as $tblBasketVerification) {
                $BasketVerificationIdList[$tblBasketVerification->getId()] = $tblBasketVerification->getId();
            }
        }
        return (new Data($this->getBinding()))->destroyBasketVerificationBulk($BasketVerificationIdList);
    }
}
