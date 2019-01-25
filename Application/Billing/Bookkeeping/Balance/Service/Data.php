<?php

namespace SPHERE\Application\Billing\Bookkeeping\Balance\Service;

use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 * @package SPHERE\Application\Billing\Bookkeeping\Balance\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        /**
         * TblPayment
         */
        $this->createPaymentType('SEPA-Lastschrift');
        $this->createPaymentType('SEPA-Überweisung');
        $this->createPaymentType('Bar');
    }

    /**
     * @param $PaymentType
     *
     * @return TblPaymentType|null|object
     */
    public function createPaymentType($PaymentType)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblPaymentType')->findOneBy(array(TblPaymentType::ATTR_NAME => $PaymentType));
        if (null === $Entity) {
            $Entity = new TblPaymentType();
            $Entity->setName($PaymentType);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param $Id
     *
     * @return false|TblPaymentType
     */
    public function getPaymentTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPaymentType', $Id);
    }

    /**
     * @return false|TblPaymentType[]
     */
    public function getPaymentTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPaymentType');
    }

    /**
     * @param $Name
     *
     * @return false|TblPaymentType
     */
    public function getPaymentTypeByName($Name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPaymentType',
            array(TblPaymentType::ATTR_NAME => $Name));
    }



    /**
     * @param TblItem $tblItem
     * @param         $Year
     * @param         $MonthFrom
     * @param         $MonthTo
     *
     * @return array|bool
     */
    public function getPriceList(TblItem $tblItem, $Year, $MonthFrom, $MonthTo)
    {
        $Manager = $this->getConnection()->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        // Test mit vielen Daten:
//        $query = $queryBuilder->select('i.InvoiceNumber, i.IntegerNumber, i.Year, i.Month, i.TargetTime, i.FirstName,
//         i.LastName, i.tblInvoiceCreditor as InvoiceCreditorId, i.serviceTblPersonCauser as PeronCauserId, iid.Id as tblInvoiceItemDebtorId
//        ,iid.Name, iid.Value, iid.Quantity, iid.DebtorNumber, iid.DebtorPerson, iid.IsPaid, iid.serviceTblItem as ItemId,
//         iid.serviceTblPersonDebtor as PersonDebtorId, iid.serviceTblPaymentType as PaymentTypeId, iid.tblInvoice as InvoiceId')
            $query = $queryBuilder->select('i.Year, i.Month, i.serviceTblPersonCauser as PeronCauserId, iid.Value,
             iid.Quantity, iid.IsPaid, iid.serviceTblPersonDebtor as PersonDebtorId')
            ->from('SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice', 'i')
            ->leftJoin('SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceItemDebtor', 'iid', 'WITH', 'i.Id = iid.tblInvoice')
            ->where($queryBuilder->expr()->eq('i.Year', '?1'))
            ->andWhere($queryBuilder->expr()->between('i.Month', '?2', '?3'))
            ->andWhere($queryBuilder->expr()->eq('iid.serviceTblItem', '?4'))
            ->setParameter(1, $Year)
            ->setParameter(2, $MonthFrom)
            ->setParameter(3, $MonthTo)
            ->setParameter(4, $tblItem->getId())
            ->getQuery();

        $PriceList = $query->getResult();

        return !empty($PriceList) ? $PriceList : false;
    }
}
