<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice\Service;

use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtor;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Accounting\Basket\Basket;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasketItem;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceAccount;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceItem;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblTempInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblTempInvoiceCommodity;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodity;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemAccount;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Search\Group\Group;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

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
    public function createInvoiceListFromBasket(
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
            $Entity = new TblInvoice();
            $Entity->setIsPaid(false);
            $Entity->setIsVoid(false);
            $Entity->setNumber("40000000");
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
                $Entity->setIsPaymentDateModified(false);
            } else {
                $Entity->setInvoiceDate(new \DateTime('now'));
                $Entity->setPaymentDate($now->add(new \DateInterval('P'.$leadTimeByDebtor.'D')));
                $Entity->setIsPaymentDateModified(true);
            }

            $Entity->setDiscount(0);
            $Entity->setDebtorFirstName($tblPersonDebtor->getFirstName());
            $Entity->setDebtorLastName($tblPersonDebtor->getLastName());
            $Entity->setDebtorSalutation($tblPersonDebtor->getTblSalutation()->getSalutation());
            $Entity->setDebtorNumber($tblDebtor->getDebtorNumber());
            $Entity->setServiceManagementPerson($tblPerson);
            if (( $tblToPerson = Address::useService()->getAddressAllByPerson($tblPersonDebtor) )) {
                // TODO address type invoice
                $tblAddress = array();
                /**@var TblToPerson $singleAddress */
                foreach ($tblToPerson as $singleAddress) {
                    $tblAddress[] = $singleAddress->getTblAddress();
                }
                $Entity->setServiceManagementAddress($tblAddress[0]);
            }

            $Manager->saveEntity($Entity);

            $Entity->setNumber((int)$Entity->getNumber() + $Entity->getId());
            $Manager->saveEntity($Entity);

            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);

            $tblTempInvoiceCommodityList = $this->getTempInvoiceCommodityAllByTempInvoice($tblTempInvoice);
            foreach ($tblTempInvoiceCommodityList as $tblTempInvoiceCommodity) {
                $tblCommodity = $tblTempInvoiceCommodity->getServiceBillingCommodity();
                $tblBasketItemAllByBasketAndCommodity = Basket::useService()->getBasketItemAllByBasketAndCommodity($tblBasket,
                    $tblCommodity);
                /**@var TblBasketItem $tblBasketItem */
                foreach ($tblBasketItemAllByBasketAndCommodity as $tblBasketItem) {
                    $tblItem = $tblBasketItem->getServiceBillingCommodityItem()->getTblItem();

                    if (!( $tblItem->getServiceManagementCourse() ) && !( $tblItem->getServiceManagementStudentChildRank() )) {
                        $this->createInvoiceItem($tblCommodity, $tblItem, $tblBasket, $tblBasketItem, $Entity);
                    } else {
                        if ($tblItem->getServiceManagementCourse() && !( $tblItem->getServiceManagementStudentChildRank() )) {
                            $tblGroup = \SPHERE\Application\People\Group\Group::useService()->getGroupByMetaTable('STUDENT');
                            if (( $tblStudent = Group::useService()->getPersonAllByGroup($tblGroup) )
//                                && $tblItem->getServiceManagementCourse()->getId() == $tblStudent->getServiceManagementCourse()->getId()      //ToDo Course
                            ) {
                                $this->createInvoiceItem($tblCommodity, $tblItem, $tblBasket, $tblBasketItem,
                                    $Entity);
                            }
                        } else {
//                            if (!( $tblItem->getServiceManagementCourse() ) && $tblItem->getServiceManagementStudentChildRank()) {            //ToDo Course
//                                if (( $tblStudent = Management::serviceStudent()->entityStudentByPerson($tblPerson) )
//                                    && $tblItem->getServiceManagementStudentChildRank()->getId() == $tblStudent->getTblChildRank()->getId()
//                                ) {
//                                    $this->createInvoiceItem($tblCommodity, $tblItem, $tblBasket, $tblBasketItem,
//                                        $Entity);
//                                }
//                            } else {
//                                if ($tblItem->getServiceManagementCourse() && $tblItem->getServiceManagementStudentChildRank()) {
//                                    if (( $tblStudent = Management::serviceStudent()->entityStudentByPerson($tblPerson) )
//                                        && $tblItem->getServiceManagementCourse()->getId() == $tblStudent->getServiceManagementCourse()->getId()
//                                        && $tblItem->getServiceManagementStudentChildRank()->getId() == $tblStudent->getTblChildRank()->getId()
//                                    ) {
//                                        $this->createInvoiceItem($tblCommodity, $tblItem, $tblBasket,
//                                            $tblBasketItem, $Entity);
//                                    }
//                                }
//                            }
                        }
                    }
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
     * @param TblCommodity  $tblCommodity
     * @param TblItem       $tblItem
     * @param TblBasket     $tblBasket
     * @param TblBasketItem $tblBasketItem
     * @param TblInvoice    $tblInvoice
     */
    private function createInvoiceItem(
        TblCommodity $tblCommodity,
        TblItem $tblItem,
        TblBasket $tblBasket,
        TblBasketItem $tblBasketItem,
        TblInvoice $tblInvoice
    ) {

        $Entity = new TblInvoiceItem();
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
        $Entity->setTblInvoice($tblInvoice);

        $this->getConnection()->getEntityManager()->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
            $Entity);

        $tblItemAccountList = Item::useService()->getItemAccountAllByItem($tblItem);
        /** @var TblItemAccount $tblItemAccount */
        foreach ($tblItemAccountList as $tblItemAccount) {
            $EntityItemAccount = new TblInvoiceAccount();
            $EntityItemAccount->setTblInvoiceItem($Entity);
            $EntityItemAccount->setServiceBilling_Account($tblItemAccount->getServiceBilling_Account());

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
            $Entity->setIsVoid(true);
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
            $Entity->setIsPaid(true);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblInvoiceItem $tblInvoiceItem
     * @param                $Price
     * @param                $Quantity
     *
     * @return bool
     */
    public function updateInvoiceItem(
        TblInvoiceItem $tblInvoiceItem,
        $Price,
        $Quantity
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblInvoiceItem $Entity */
        $Entity = $Manager->getEntityById('TblInvoiceItem', $tblInvoiceItem->getId());
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
     * @param TblInvoiceItem $tblInvoiceItem
     *
     * @return bool
     */
    public function destroyInvoiceItem(
        TblInvoiceItem $tblInvoiceItem
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblInvoiceItem')->findOneBy(
            array(
                'Id' => $tblInvoiceItem->getId()
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
     * @param TblInvoice $tblInvoice
     * @param TblAddress $tblAddress
     *
     * @return bool
     */
    public function updateInvoiceAddress(
        TblInvoice $tblInvoice,
        TblAddress $tblAddress
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblInvoice $Entity */
        $Entity = $Manager->getEntityById('TblInvoice', $tblInvoice->getId());
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
