<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice\Service;

use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtor;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Accounting\Basket\Basket;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasketItem;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceAccount;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceItem;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblOrder;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblOrderItem;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblTempInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblTempInvoiceCommodity;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodity;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemAccount;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 * @package SPHERE\Application\Billing\Bookkeeping\Invoice\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param integer $Id
     *
     * @return bool|TblInvoice
     */
    public function getInvoiceById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblInvoice', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Id
     *
     * @return bool|TblOrder
     */
    public function getOrderById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblOrder', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblTempInvoice
     */
    public function getTempInvoiceById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblTempInvoice', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblInvoice[]
     */
    public function getInvoiceAll()
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblInvoice')->findAll();
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblOrder[]
     */
    public function getOrderAll()
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblOrder')->findAll();
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $IsPaid
     *
     * @return TblInvoice[]|bool
     */
    public function getInvoiceAllByIsPaidState($IsPaid)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblInvoice')
            ->findBy(array(TblInvoice::ATTR_IS_PAID => $IsPaid));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param $IsVoid
     *
     * @return TblInvoice[]|bool
     */
    public function getInvoiceAllByIsVoidState($IsVoid)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblInvoice')
            ->findBy(array(TblInvoice::ATTR_IS_VOID => $IsVoid));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblInvoiceItem
     */
    public function getInvoiceItemById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblInvoiceItem', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Id
     *
     * @return bool|TblOrderItem
     */
    public function getOrderItemById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblOrderItem', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Number
     *
     * @return TblInvoice|bool
     */
    public function getInvoiceByNumber($Number)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblInvoice')
            ->findOneBy(array(TblInvoice::ATTR_NUMBER => $Number));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return string
     */
    public function sumPriceItemAllStringByInvoice(TblInvoice $tblInvoice)
    {

        $result = sprintf("%01.2f", $this->sumPriceItemAllByInvoice($tblInvoice));
        return str_replace('.', ',', $result)." €";
    }

    /**
     * @param TblOrder $tblOrder
     *
     * @return string
     */
    public function sumPriceItemAllStringByOrder(TblOrder $tblOrder)
    {

        $result = sprintf("%01.2f", $this->sumPriceItemAllByOrder($tblOrder));
        return str_replace('.', ',', $result)." €";
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return float
     */
    public function sumPriceItemAllByInvoice(TblInvoice $tblInvoice)
    {

        $sum = 0.00;
        $tblInvoiceItemByInvoice = $this->getInvoiceItemAllByInvoice($tblInvoice);
        /** @var TblInvoiceItem $tblInvoiceItem */
        foreach ($tblInvoiceItemByInvoice as $tblInvoiceItem) {
            $sum += $tblInvoiceItem->getItemPrice() * $tblInvoiceItem->getItemQuantity();
        }

        return $sum;
    }

    /**
     * @param $tblOrder
     *
     * @return float
     */
    public function sumPriceItemAllByOrder($tblOrder)
    {

        $sum = 0.00;
        $tblOrderItemList = $this->getInvoiceItemAllByOrder($tblOrder);
        /** @var TblOrderItem $tblOrderItem */
        foreach ($tblOrderItemList as $tblOrderItem) {
            $sum += $tblOrderItem->getItemPrice() * $tblOrderItem->getItemQuantity();
        }

        return $sum;
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return TblInvoiceItem[]|bool
     */
    public function getInvoiceItemAllByInvoice(TblInvoice $tblInvoice)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblInvoiceItem')
            ->findBy(array(TblInvoiceItem::ATTR_TBL_INVOICE => $tblInvoice->getId()));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblOrder $tblOrder
     *
     * @return bool|TblOrderItem[]
     */
    public function getOrderItemAllByOrder(TblOrder $tblOrder)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblOrderItem')
            ->findBy(array(TblOrderItem::ATTR_TBL_ORDER => $tblOrder->getId()));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblOrder $tblOrder
     *
     * @return array|bool
     */
    public function getInvoiceItemAllByOrder(TblOrder $tblOrder)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblOrderItem')
            ->findBy(array(TblOrderItem::ATTR_TBL_ORDER => $tblOrder->getId()));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblDebtor $tblDebtor
     *
     * @return bool
     */
    public function checkInvoiceFromDebtorIsPaidByDebtor(TblDebtor $tblDebtor)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblInvoice')->findOneBy(array(
            TblInvoice::ATTR_IS_PAID       => true,
            TblInvoice::ATTR_DEBTOR_NUMBER => $tblDebtor->getDebtorNumber()
        ));
        return ( null === $Entity ? false : true );
    }

    /**
     * @param TblBasket $tblBasket
     * @param           $Date
     *
     * @return bool
     */
    public function createOrderListFromBasket(
        TblBasket $tblBasket,
        $Date
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $tblTempInvoiceList = $this->getTempInvoiceAllByBasket($tblBasket);
        /**@var TblTempInvoice $tblTempInvoice */
        foreach ($tblTempInvoiceList as $tblTempInvoice) {
            $tblDebtor = $tblTempInvoice->getServiceBillingDebtor();
            $tblPersonDebtor = $tblDebtor->getServiceManagementPerson();
            $tblPerson = $tblTempInvoice->getServiceManagementPerson();
            $Entity = new TblOrder();
//            $Entity->setPaid(false);
//            $Entity->setVoid(false);
//            $Entity->setNumber("40000000");
            $Entity->setBasketName($tblBasket->getName());
            $PaymentType = $tblDebtor->getPaymentType();
            $Entity->setServiceBillingBankingPaymentType($PaymentType);

            $leadTimeByDebtor = false;//Banking::useService()->getLeadTimeByDebtor($tblDebtor); //ToDO Leadtime from School?
            if ($leadTimeByDebtor === false) {
                $leadTimeByDebtor = 5;      //ToDO LeadFirstTime 5 Day's
            }

            $invoiceDate = (new \DateTime($Date))->sub(new \DateInterval('P'.$leadTimeByDebtor.'D'));
            $now = new \DateTime();
            if (( $invoiceDate->format('y.m.d') ) >= ( $now->format('y.m.d') )) {
                $Entity->setInvoiceDate($invoiceDate);
                $Entity->setPaymentDate(new \DateTime($Date));
                $Entity->setPaymentDateModified(false);
            } else {
                $Entity->setInvoiceDate(new \DateTime('now'));
                $Entity->setPaymentDate($now->add(new \DateInterval('P'.$leadTimeByDebtor.'D')));
                $Entity->setPaymentDateModified(true);
            }

            $Entity->setDiscount(0);
            $Entity->setDebtorFirstName($tblPersonDebtor->getFirstName());
            $Entity->setDebtorLastName($tblPersonDebtor->getLastName());
            if ($tblPersonDebtor->getTblSalutation()) {
                $Entity->setDebtorSalutation($tblPersonDebtor->getTblSalutation()->getSalutation());
            } else {
                $Entity->setDebtorSalutation('');
            }
            $Entity->setDebtorNumber($tblDebtor->getDebtorNumber());
            $Entity->setServiceManagementPerson($tblPerson);
            if (( $tblToPerson = Address::useService()->getAddressAllByPerson($tblPersonDebtor) )) {
                $tblAddress = array();
                /**@var TblToPerson $singleAddress */
                foreach ($tblToPerson as $singleAddress) {
                    if ($singleAddress->getTblType()->getName() === 'Rechnungsadresse') {
                        $tblAddress[] = $singleAddress->getTblAddress();
                    }
                }
                if (empty( $tblAddress )) {
                    foreach ($tblToPerson as $singleAddress) {
                        if ($singleAddress->getTblType()->getName() === 'Hauptadresse') {
                            $tblAddress[] = $singleAddress->getTblAddress();
                        }
                    }
                }
                if (empty( $tblAddress )) {
                    foreach ($tblToPerson as $singleAddress) {
                        $tblAddress[] = $singleAddress->getTblAddress();
                    }
                }
                $Entity->setServiceManagementAddress($tblAddress[0]);
            }

            $Manager->saveEntity($Entity);

//            $Entity->setNumber((int)$Entity->getNumber() + $Entity->getId());
//            $Manager->saveEntity($Entity);

            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);

            $tblTempInvoiceCommodityList = $this->getTempInvoiceCommodityAllByTempInvoice($tblTempInvoice);
            foreach ($tblTempInvoiceCommodityList as $tblTempInvoiceCommodity) {
                $tblCommodity = $tblTempInvoiceCommodity->getServiceBillingCommodity();
                $tblBasketItemAllByBasketAndCommodity = Basket::useService()->getBasketItemAllByBasketAndCommodity($tblBasket,
                    $tblCommodity);

                //ToDO Verhalten anpassen (leere Rechnungen möglich...)

                /**@var TblBasketItem $tblBasketItem */
                foreach ($tblBasketItemAllByBasketAndCommodity as $tblBasketItem) {
                    $tblItem = $tblBasketItem->getServiceBillingCommodityItem()->getTblItem();
                    if (!( $tblItem->getServiceStudentType() ) && !( $tblItem->getServiceStudentChildRank() )) {
                        $this->createOrderItem($tblCommodity, $tblItem, $tblBasket, $tblBasketItem, $Entity);
                    } else {
                        if ($tblItem->getServiceStudentType() && !( $tblItem->getServiceStudentChildRank() )) {

                            $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                            if ($tblStudent) {
                                $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                                if ($tblTransferType) {
                                    $Type = Student::useService()->getStudentTransferByType($tblStudent,
                                        $tblTransferType);
                                    if ($Type) {
                                        if ($Type->getServiceTblType()) {
                                            if ($tblItem->getServiceStudentType()->getId() == $Type->getServiceTblType()->getId()) {
                                                $this->createOrderItem($tblCommodity, $tblItem, $tblBasket,
                                                    $tblBasketItem,
                                                    $Entity);
                                            }
                                        }
                                    }
                                }
                            }

                        } else {
                            if (!( $tblItem->getServiceStudentType() ) && $tblChildRank = $tblItem->getServiceStudentChildRank()) {
                                if (( $tblStudent = Student::useService()->getStudentByPerson($tblPerson) )) {
                                    $SiblingRank = $tblStudent->getTblStudentBilling()->getServiceTblSiblingRank();
                                    if (!$SiblingRank) {
                                        if ($tblChildRank->getName() === '1. Geschwisterkind') {
                                            $this->createOrderItem($tblCommodity, $tblItem, $tblBasket, $tblBasketItem,
                                                $Entity);
                                        }
                                    } else {
                                        if ($tblChildRank->getId() === $SiblingRank->getId()) {
                                            $this->createOrderItem($tblCommodity, $tblItem, $tblBasket, $tblBasketItem,
                                                $Entity);
                                        }
                                    }
                                }
                            } else {
                                if ($tblItem->getServiceStudentType() && $tblItem->getServiceStudentChildRank()) {
                                    if (( $tblStudent = Student::useService()->getStudentByPerson($tblPerson) )) {
                                        $studentType = 0;
                                        $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                                        if ($tblTransferType) {
                                            $Type = Student::useService()->getStudentTransferByType($tblStudent,
                                                $tblTransferType);
                                            if ($Type) {
                                                if ($Type->getServiceTblType()) {
                                                    $studentType = $Type->getServiceTblType()->getId();
                                                }
                                            }
                                        }
                                        if (( $SiblingRank = $tblStudent->getTblStudentBilling()->getServiceTblSiblingRank() )
                                            && $tblItem->getServiceStudentType()->getId() == $studentType
                                            && $tblItem->getServiceStudentChildRank()->getId() == $SiblingRank->getId()
                                        ) {
                                            $this->createOrderItem($tblCommodity, $tblItem, $tblBasket,
                                                $tblBasketItem, $Entity);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $tblInvoiceList = Invoice::useService()->getInvoiceAllByIsConfirmedState(false);
        if ($tblInvoiceList) {
            foreach ($tblInvoiceList as $tblInvoice) {

                if (!$emptyInvoice = Invoice::useService()->getInvoiceItemAllByInvoice($tblInvoice)) {
                    Invoice::useService()->removeInvoice($tblInvoice);
                }
            }
        }
        return true;
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return TblTempInvoice[]|bool
     */
    public function getTempInvoiceAllByBasket(TblBasket $tblBasket)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblTempInvoice')
            ->findBy(array(TblTempInvoice::ATTR_SERVICE_BILLING_BASKET => $tblBasket->getId()));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblTempInvoice $tblTempInvoice
     *
     * @return TblTempInvoiceCommodity[]|bool
     */
    public function getTempInvoiceCommodityAllByTempInvoice(TblTempInvoice $tblTempInvoice)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblTempInvoiceCommodity')
            ->findBy(array(TblTempInvoiceCommodity::ATTR_TBL_TEMP_INVOICE => $tblTempInvoice->getId()));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblOrderItem $tblOrderItem
     * @param TblInvoice   $tblInvoice
     *
     * @return TblInvoiceItem
     */
    public function createInvoiceItem(
        TblOrderItem $tblOrderItem,
        TblInvoice $tblInvoice
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblInvoiceItem();
        $Entity->setCommodityName($tblOrderItem->getCommodityName());
        $Entity->setCommodityDescription($tblOrderItem->getCommodityDescription());
        $Entity->setItemName($tblOrderItem->getItemName());
        $Entity->setItemPrice($tblOrderItem->getItemPrice());
        $Entity->setItemQuantity($tblOrderItem->getItemQuantity());
        $Entity->setItemDescription($tblOrderItem->getItemDescription());
        $Entity->setTblInvoice($tblInvoice);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
            $Entity);

        return $Entity;
    }

    /**
     * @param TblOrder $tblOrder
     *
     * @return TblInvoice
     */
    public function createInvoice(TblOrder $tblOrder)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblInvoice();
        $Entity->setBasketName($tblOrder->getBasketName());
        $Entity->setDebtorFirstName($tblOrder->getDebtorFirstName());
        $Entity->setDebtorLastName($tblOrder->getDebtorLastName());
        $Entity->setDebtorNumber($tblOrder->getDebtorNumber());
        $Entity->setDebtorSalutation($tblOrder->getDebtorSalutation());
        $Entity->setDiscount($tblOrder->getDiscount());
        $Entity->setInvoiceDate(new \DateTime($tblOrder->getInvoiceDate()));
        $Entity->setNumber("40000000");
        $Entity->setPaid(false);
        $Entity->setVoid(false);
        $Entity->setPaymentDate(new \DateTime($tblOrder->getPaymentDate()));
        $Entity->setServiceBillingBankingPaymentType($tblOrder->getServiceBillingBankingPaymentType());
        $Entity->setServiceManagementPerson($tblOrder->getServiceManagementPerson());
        $Entity->setServiceManagementAddress($tblOrder->getServiceManagementAddress());

        $leadTimeByDebtor = false;//Banking::useService()->getLeadTimeByDebtor($tblDebtor); //ToDO Leadtime from School?
        if ($leadTimeByDebtor === false) {
            $leadTimeByDebtor = 5;      //ToDO LeadFirstTime 5 Day's
        }
        $invoiceDate = (new \DateTime($tblOrder->getPaymentDate()))->sub(new \DateInterval('P'.$leadTimeByDebtor.'D'));
        $now = new \DateTime();
        if (( $invoiceDate->format('y.m.d') ) >= ( $now->format('y.m.d') )) {
            $Entity->setInvoiceDate($invoiceDate);
            $Entity->setPaymentDate(new \DateTime($tblOrder->getPaymentDate()));
            $Entity->setPaymentDateModified(false);
        } else {
            $Entity->setInvoiceDate(new \DateTime('now'));
            $Entity->setPaymentDate($now->add(new \DateInterval('P'.$leadTimeByDebtor.'D')));
            $Entity->setPaymentDateModified(true);
        }

        $Manager->saveEntity($Entity);

        $Entity->setNumber((int)$Entity->getNumber() + $Entity->getId());
        $Manager->saveEntity($Entity);

        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
            $Entity);

        return $Entity;
    }

    /**
     * @param TblCommodity  $tblCommodity
     * @param TblItem       $tblItem
     * @param TblBasket     $tblBasket
     * @param TblBasketItem $tblBasketItem
     * @param TblOrder      $tblOrder
     */
    private function createOrderItem(
        TblCommodity $tblCommodity,
        TblItem $tblItem,
        TblBasket $tblBasket,
        TblBasketItem $tblBasketItem,
        TblOrder $tblOrder
    ) {

        $Entity = new TblOrderItem();
        $Entity->setCommodityName($tblCommodity->getName());
        $Entity->setCommodityDescription($tblCommodity->getDescription());
        $Entity->setItemName($tblItem->getName());
        $Entity->setItemDescription($tblItem->getDescription());
        if ($tblCommodity->getTblCommodityType()->getName() == 'Einzelleistung') {
            $Entity->setItemPrice($tblBasketItem->getPrice());
        } else {
            $Entity->setItemPrice($tblBasketItem->getPrice() / Basket::useService()->countPersonByBasket($tblBasket));
        }
        $Entity->setItemQuantity($tblBasketItem->getQuantity());
        $Entity->setTblOrder($tblOrder);

        $this->getConnection()->getEntityManager()->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
            $Entity);

        $tblItemAccountList = Item::useService()->getItemAccountAllByItem($tblItem);
        /** @var TblItemAccount $tblItemAccount */
        foreach ($tblItemAccountList as $tblItemAccount) {
            $EntityItemAccount = new TblInvoiceAccount();
            $EntityItemAccount->setTblInvoiceItem($Entity);
            $EntityItemAccount->setServiceBillingAccount($tblItemAccount->getServiceBillingAccount());

            $this->getConnection()->getEntityManager()->saveEntity($EntityItemAccount);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $EntityItemAccount);
        }
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return bool
     */
    public function cancelInvoice(
        TblInvoice $tblInvoice
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblInvoice $Entity */
        $Entity = $Manager->getEntityById('TblInvoice', $tblInvoice->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setVoid(true);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return bool
     */
    public function createPayInvoice(
        TblInvoice $tblInvoice
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblInvoice $Entity */
        $Entity = $Manager->getEntityById('TblInvoice', $tblInvoice->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setPaid(true);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblOrderItem $tblOrderItem
     * @param              $Price
     * @param              $Quantity
     *
     * @return bool
     */
    public function updateOrderItem(
        TblOrderItem $tblOrderItem,
        $Price,
        $Quantity
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblOrderItem $Entity */
        $Entity = $Manager->getEntityById('TblOrderItem', $tblOrderItem->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setItemPrice(str_replace(',', '.', $Price));
            $Entity->setItemQuantity(str_replace(',', '.', $Quantity));

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblOrderItem $tblOrderItem
     *
     * @return bool
     */
    public function destroyOrderItem(
        TblOrderItem $tblOrderItem
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblOrderItem')->findOneBy(
            array(
                'Id' => $tblOrderItem->getId()
            ));
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return bool
     */
    public function destroyInvoice(TblInvoice $tblInvoice)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblInvoice')->findOneBy(
            array(
                'Id' => $tblInvoice->getId()
            ));
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblOrder $tblOrder
     *
     * @return bool
     */
    public function destroyOrder(TblOrder $tblOrder)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntityById('TblOrder', $tblOrder->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblBasket $tblBasket
     * @param TblPerson $tblPerson
     * @param TblDebtor $tblDebtor
     *
     * @return TblTempInvoice|null
     */
    public function createTempInvoice(
        TblBasket $tblBasket,
        TblPerson $tblPerson,
        TblDebtor $tblDebtor
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblTempInvoice')->findOneBy(array(
            TblTempInvoice::ATTR_SERVICE_BILLING_BASKET    => $tblBasket->getId(),
            TblTempInvoice::ATTR_SERVICE_MANAGEMENT_PERSON => $tblPerson->getId(),
            TblTempInvoice::ATTR_SERVICE_BILLING_DEBTOR    => $tblDebtor->getId()
        ));
        if (null === $Entity) {
            $Entity = new TblTempInvoice();
            $Entity->setServiceBillingBasket($tblBasket);
            $Entity->setServiceManagementPerson($tblPerson);
            $Entity->setServiceBillingDebtor($tblDebtor);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblTempInvoice $tblTempInvoice
     * @param TblCommodity   $tblCommodity
     *
     * @return TblTempInvoiceCommodity|null
     */
    public function createTempInvoiceCommodity(
        TblTempInvoice $tblTempInvoice,
        TblCommodity $tblCommodity
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblTempInvoiceCommodity')->findOneBy(array(
            TblTempInvoiceCommodity::ATTR_TBL_TEMP_INVOICE          => $tblTempInvoice->getId(),
            TblTempInvoiceCommodity::ATTR_SERVICE_BILLING_COMMODITY => $tblCommodity->getId()
        ));
        if (null === $Entity) {
            $Entity = new TblTempInvoiceCommodity();
            $Entity->setTblTempInvoice($tblTempInvoice);
            $Entity->setServiceBillingCommodity($tblCommodity);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblOrder   $tblOrder
     * @param TblAddress $tblAddress
     *
     * @return bool
     */
    public function updateOrderAddress(
        TblOrder $tblOrder,
        TblAddress $tblAddress
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblOrder $Entity */
        $Entity = $Manager->getEntityById('TblOrder', $tblOrder->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setServiceManagementAddress($tblAddress);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }

        return false;
    }

    /**
     * @param TblInvoice     $tblInvoice
     * @param TblPaymentType $tblPaymentType
     *
     * @return bool
     */
    public function changeInvoicePaymentType(
        TblInvoice $tblInvoice,
        TblPaymentType $tblPaymentType
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblInvoice $Entity */
        $Entity = $Manager->getEntityById('TblInvoice', $tblInvoice->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setServiceBillingBankingPaymentType($tblPaymentType);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }

        return false;
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool
     */
    public function destroyTempInvoice(
        TblBasket $tblBasket
    ) {

        if ($tblBasket !== null) {
            $Manager = $this->getConnection()->getEntityManager();

            /** @var  TblTempInvoice[] $EntityList */
            $EntityList = $Manager->getEntity('TblTempInvoice')->findBy(array(
                TblTempInvoice::ATTR_SERVICE_BILLING_BASKET => $tblBasket->getId()
            ));
            foreach ($EntityList as $Entity) {
                $EntitySubList = $Manager->getEntity('TblTempInvoiceCommodity')->findBy(array(
                    TblTempInvoiceCommodity::ATTR_TBL_TEMP_INVOICE => $Entity->getId()
                ));
                foreach ($EntitySubList as $SubEntity) {
                    Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                        $SubEntity);
                    $Manager->bulkKillEntity($SubEntity);
                }
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity);
                $Manager->bulkKillEntity($Entity);
            }

            $Manager->flushCache();

            return true;
        }

        return false;
    }
}
