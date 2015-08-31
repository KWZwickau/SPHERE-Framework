<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice\Service;

use SPHERE\Application\Billing\Accounting\Banking\Banking;
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
use SPHERE\Application\Billing\Inventory\Commodity\Commodity;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodity;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemAccount;
use SPHERE\System\Database\Fitting\Binding;

class Data
{

    /** @var null|Binding $Connection */
    private $Connection = null;

    /**
     * @param Binding $Connection
     */
    function __construct(Binding $Connection)
    {

        $this->Connection = $Connection;
    }

    public function setupDatabaseContent()
    {

        /**
         * CommodityType
         */
//        $this->actionCreateCommodityType( 'Einzelleistung' );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblInvoice
     */
    public function entityInvoiceById($Id)
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById('TblInvoice', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblTempInvoice
     */
    public function entityTempInvoiceById($Id)
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById('TblTempInvoice', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblInvoice[]
     */
    public function entityInvoiceAll()
    {

        $Entity = $this->Connection->getEntityManager()->getEntity('TblInvoice')->findAll();
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $IsPaid
     *
     * @return TblInvoice[]|bool
     */
    public function entityInvoiceAllByIsPaidState($IsPaid)
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity('TblInvoice')
            ->findBy(array(TblInvoice::ATTR_IS_PAID => $IsPaid));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param $IsVoid
     *
     * @return TblInvoice[]|bool
     */
    public function entityInvoiceAllByIsVoidState($IsVoid)
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity('TblInvoice')
            ->findBy(array(TblInvoice::ATTR_IS_VOID => $IsVoid));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblInvoiceItem
     */
    public function entityInvoiceItemById($Id)
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById('TblInvoiceItem', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Number
     *
     * @return TblInvoice|bool
     */
    public function entityInvoiceByNumber($Number)
    {

        $Entity = $this->Connection->getEntityManager()->getEntity('TblInvoice')
            ->findOneBy(array(TblInvoice::ATTR_NUMBER => $Number));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblTempInvoice $tblTempInvoice
     *
     * @return TblTempInvoiceCommodity[]|bool
     */
    public function entityTempInvoiceCommodityAllByTempInvoice(TblTempInvoice $tblTempInvoice)
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity('TblTempInvoiceCommodity')
            ->findBy(array(TblTempInvoiceCommodity::ATTR_TBL_TEMP_INVOICE => $tblTempInvoice->getId()));
        return ( null === $EntityList ? false : $EntityList );
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
        $tblInvoiceItemByInvoice = $this->entityInvoiceItemAllByInvoice($tblInvoice);
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
    public function entityInvoiceItemAllByInvoice(TblInvoice $tblInvoice)
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity('TblInvoiceItem')
            ->findBy(array(TblInvoiceItem::ATTR_TBL_INVOICE => $tblInvoice->getId()));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return TblTempInvoice[]|bool
     */
    public function entityTempInvoiceAllByBasket(TblBasket $tblBasket)
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity('TblTempInvoice')
            ->findBy(array(TblTempInvoice::ATTR_SERVICE_BILLING_BASKET => $tblBasket->getId()));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblDebtor $tblDebtor
     *
     * @return bool
     */
    public function checkInvoiceFromDebtorIsPaidByDebtor(TblDebtor $tblDebtor)
    {

        $Entity = $this->Connection->getEntityManager()->getEntity('TblInvoice')->findOneBy(array(
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
    public function actionCreateInvoiceListFromBasket(
        TblBasket $tblBasket,
        $Date
    ) {

        $Manager = $this->Connection->getEntityManager();
        $tblTempInvoiceList = $this->entityTempInvoiceAllByBasket($tblBasket);
        /**@var TblTempInvoice $tblTempInvoice */
        foreach ($tblTempInvoiceList as $tblTempInvoice) {
            $tblDebtor = $tblTempInvoice->getServiceBillingDebtor();
            $tblPersonDebtor = Management::servicePerson()->entityPersonById($tblDebtor->getServiceManagementPerson());
            $tblPerson = $tblTempInvoice->getServiceManagementPerson();
            $Entity = new TblInvoice();
            $Entity->setIsPaid(false);
            $Entity->setIsVoid(false);
            $Entity->setNumber("40000000");
            $Entity->setBasketName($tblBasket->getName());
            $Entity->setServiceBillingBankingPaymentType($tblDebtor->getPaymentType());

            $leadTimeByDebtor = Banking::useService()->entityLeadTimeByDebtor($tblDebtor);
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
            $Entity->setDebtorSalutation($tblPersonDebtor->getTblPersonSalutation()->getName());
            $Entity->setDebtorNumber($tblDebtor->getDebtorNumber());
            $Entity->setServiceManagementPerson($tblPerson);
            if (( $address = Management::servicePerson()->entityAddressAllByPerson($tblPersonDebtor) )) {
                // TODO address type invoice
                $Entity->setServiceManagementAddress($address[0]);
            }

            $Manager->saveEntity($Entity);

            $Entity->setNumber((int)$Entity->getNumber() + $Entity->getId());
            $Manager->saveEntity($Entity);

            Protocol::useService()->createInsertEntry($this->Connection->getDatabase(),
                $Entity);

            $tblTempInvoiceCommodityList = $this->entityTempInvoiceCommodityAllByTempInvoice($tblTempInvoice);
            foreach ($tblTempInvoiceCommodityList as $tblTempInvoiceCommodity) {
                $tblCommodity = $tblTempInvoiceCommodity->getServiceBillingCommodity();
                $tblBasketItemAllByBasketAndCommodity = Basket::useService()->entityBasketItemAllByBasketAndCommodity($tblBasket,
                    $tblCommodity);
                /**@var TblBasketItem $tblBasketItem */
                foreach ($tblBasketItemAllByBasketAndCommodity as $tblBasketItem) {
                    $tblItem = $tblBasketItem->getServiceBillingCommodityItem()->getTblItem();

                    if (!( $tblItem->getServiceManagementCourse() ) && !( $tblItem->getServiceManagementStudentChildRank() )) {
                        $this->actionCreateInvoiceItem($tblCommodity, $tblItem, $tblBasket, $tblBasketItem, $Entity);
                    } else {
                        if ($tblItem->getServiceManagementCourse() && !( $tblItem->getServiceManagementStudentChildRank() )) {
                            if (( $tblStudent = Management::serviceStudent()->entityStudentByPerson($tblPerson) )
                                && $tblItem->getServiceManagementCourse()->getId() == $tblStudent->getServiceManagementCourse()->getId()
                            ) {
                                $this->actionCreateInvoiceItem($tblCommodity, $tblItem, $tblBasket, $tblBasketItem,
                                    $Entity);
                            }
                        } else {
                            if (!( $tblItem->getServiceManagementCourse() ) && $tblItem->getServiceManagementStudentChildRank()) {
                                if (( $tblStudent = Management::serviceStudent()->entityStudentByPerson($tblPerson) )
                                    && $tblItem->getServiceManagementStudentChildRank()->getId() == $tblStudent->getTblChildRank()->getId()
                                ) {
                                    $this->actionCreateInvoiceItem($tblCommodity, $tblItem, $tblBasket, $tblBasketItem,
                                        $Entity);
                                }
                            } else {
                                if ($tblItem->getServiceManagementCourse() && $tblItem->getServiceManagementStudentChildRank()) {
                                    if (( $tblStudent = Management::serviceStudent()->entityStudentByPerson($tblPerson) )
                                        && $tblItem->getServiceManagementCourse()->getId() == $tblStudent->getServiceManagementCourse()->getId()
                                        && $tblItem->getServiceManagementStudentChildRank()->getId() == $tblStudent->getTblChildRank()->getId()
                                    ) {
                                        $this->actionCreateInvoiceItem($tblCommodity, $tblItem, $tblBasket,
                                            $tblBasketItem, $Entity);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * @param TblCommodity  $tblCommodity
     * @param TblItem       $tblItem
     * @param TblBasket     $tblBasket
     * @param TblBasketItem $tblBasketItem
     * @param TblInvoice    $tblInvoice
     */
    private function actionCreateInvoiceItem(
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

        $this->Connection->getEntityManager()->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->Connection->getDatabase(),
            $Entity);

        $tblItemAccountList = Commodity::useService()->entityItemAccountAllByItem($tblItem);
        /** @var TblItemAccount $tblItemAccount */
        foreach ($tblItemAccountList as $tblItemAccount) {
            $EntityItemAccount = new TblInvoiceAccount();
            $EntityItemAccount->setTblInvoiceItem($Entity);
            $EntityItemAccount->setServiceBilling_Account($tblItemAccount->getServiceBilling_Account());

            $this->Connection->getEntityManager()->saveEntity($EntityItemAccount);
            Protocol::useService()->createInsertEntry($this->Connection->getDatabase(),
                $EntityItemAccount);
        }
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return bool
     */
    public function actionCancelInvoice(
        TblInvoice $tblInvoice
    ) {

        $Manager = $this->Connection->getEntityManager();

        /** @var TblInvoice $Entity */
        $Entity = $Manager->getEntityById('TblInvoice', $tblInvoice->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setIsVoid(true);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->Connection->getDatabase(),
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
    public function actionPayInvoice(
        TblInvoice $tblInvoice
    ) {

        $Manager = $this->Connection->getEntityManager();

        /** @var TblInvoice $Entity */
        $Entity = $Manager->getEntityById('TblInvoice', $tblInvoice->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setIsPaid(true);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->Connection->getDatabase(),
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
    public function actionEditInvoiceItem(
        TblInvoiceItem $tblInvoiceItem,
        $Price,
        $Quantity
    ) {

        $Manager = $this->Connection->getEntityManager();

        /** @var TblInvoiceItem $Entity */
        $Entity = $Manager->getEntityById('TblInvoiceItem', $tblInvoiceItem->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setItemPrice(str_replace(',', '.', $Price));
            $Entity->setItemQuantity(str_replace(',', '.', $Quantity));

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->Connection->getDatabase(),
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
    public function actionRemoveInvoiceItem(
        TblInvoiceItem $tblInvoiceItem
    ) {

        $Manager = $this->Connection->getEntityManager();

        $Entity = $Manager->getEntity('TblInvoiceItem')->findOneBy(
            array(
                'Id' => $tblInvoiceItem->getId()
            ));
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->Connection->getDatabase(),
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
    public function actionCreateTempInvoice(
        TblBasket $tblBasket,
        TblPerson $tblPerson,
        TblDebtor $tblDebtor
    ) {

        $Manager = $this->Connection->getEntityManager();

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
            Protocol::useService()->createInsertEntry($this->Connection->getDatabase(),
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
    public function actionCreateTempInvoiceCommodity(
        TblTempInvoice $tblTempInvoice,
        TblCommodity $tblCommodity
    ) {

        $Manager = $this->Connection->getEntityManager();

        $Entity = $Manager->getEntity('TblTempInvoiceCommodity')->findOneBy(array(
            TblTempInvoiceCommodity::ATTR_TBL_TEMP_INVOICE          => $tblTempInvoice->getId(),
            TblTempInvoiceCommodity::ATTR_SERVICE_BILLING_COMMODITY => $tblCommodity->getId()
        ));
        if (null === $Entity) {
            $Entity = new TblTempInvoiceCommodity();
            $Entity->setTblTempInvoice($tblTempInvoice);
            $Entity->setServiceBillingCommodity($tblCommodity);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->Connection->getDatabase(),
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
    public function actionChangeInvoiceAddress(
        TblInvoice $tblInvoice,
        TblAddress $tblAddress
    ) {

        $Manager = $this->Connection->getEntityManager();

        /** @var TblInvoice $Entity */
        $Entity = $Manager->getEntityById('TblInvoice', $tblInvoice->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setServiceManagementAddress($tblAddress);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->Connection->getDatabase(),
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
    public function actionChangeInvoicePaymentType(
        TblInvoice $tblInvoice,
        TblPaymentType $tblPaymentType
    ) {

        $Manager = $this->Connection->getEntityManager();

        /** @var TblInvoice $Entity */
        $Entity = $Manager->getEntityById('TblInvoice', $tblInvoice->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setServiceBillingBankingPaymentType($tblPaymentType);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->Connection->getDatabase(),
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
    public function actionDestroyTempInvoice(
        TblBasket $tblBasket
    ) {

        if ($tblBasket !== null) {
            $Manager = $this->Connection->getEntityManager();

            /** @var  TblTempInvoice[] $EntityList */
            $EntityList = $Manager->getEntity('TblTempInvoice')->findBy(array(
                TblTempInvoice::ATTR_SERVICE_BILLING_BASKET => $tblBasket->getId()
            ));
            foreach ($EntityList as $Entity) {
                $EntitySubList = $Manager->getEntity('TblTempInvoiceCommodity')->findBy(array(
                    TblTempInvoiceCommodity::ATTR_TBL_TEMP_INVOICE => $Entity->getId()
                ));
                foreach ($EntitySubList as $SubEntity) {
                    Protocol::useService()->createDeleteEntry($this->Connection->getDatabase(),
                        $SubEntity);
                    $Manager->bulkKillEntity($SubEntity);
                }
                Protocol::useService()->createDeleteEntry($this->Connection->getDatabase(),
                    $Entity);
                $Manager->bulkKillEntity($Entity);
            }

            $Manager->flushCache();

            return true;
        }

        return false;
    }
}
