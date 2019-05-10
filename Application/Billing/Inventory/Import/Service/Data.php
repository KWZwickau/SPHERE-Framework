<?php

namespace SPHERE\Application\Billing\Inventory\Import\Service;

use SPHERE\Application\Billing\Inventory\Import\Service\Entity\TblImport;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 * @package SPHERE\Application\Billing\Inventory\Import\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param $Id
     *
     * @return false|TblImport
     */
    public function getImportById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblImport', $Id);
    }

    /**
     * @return false|TblImport[]
     */
    public function getImportAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblImport');
    }

    /**
     * @param            $ImportList
     *
     * @return bool
     */
    public function createImportBulk(
        $ImportList
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        if (!empty($ImportList)) {
            foreach ($ImportList as $Result) {

                $Entity = new TblImport();
                $Entity->setRow($Result['Row']);
                $Entity->setFirstName($Result['FirstName']);
                $Entity->setLastName($Result['LastName']);
                $Entity->setBirthday($Result['Birthday']);
                if(isset($Result['serviceTblPerson'])){
                    $Entity->setServiceTblPerson($Result['serviceTblPerson']);
                }
                $Entity->setValue($Result['Value']);
                $Entity->setPriceVariant($Result['PriceVariant']);
                $Entity->setItem($Result['Item']);
                $Entity->setReference($Result['Reference']);
                $Entity->setReferenceDate($Result['ReferenceDate']);
                $Entity->setPaymentFromDate($Result['PaymentFromDate']);
                $Entity->setPaymentTillDate($Result['PaymentTillDate']);
                $Entity->setDebtorFirstName($Result['DebtorFirstName']);
                $Entity->setDebtorLastName($Result['DebtorLastName']);
                if(isset($Result['serviceTblPersonDebtor'])){
                    $Entity->setServiceTblPersonDebtor($Result['serviceTblPersonDebtor']);
                }
                $Entity->setDebtorNumber($Result['DebtorNumber']);
                $Entity->setIBAN($Result['IBAN']);
                $Entity->setBIC($Result['BIC']);
                $Entity->setBank($Result['Bank']);
                $Manager->bulkSaveEntity($Entity);
                Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity, true);
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function destroyImport()
    {

        $Manager = $this->getConnection()->getEntityManager();

        $EntityList = $Manager->getEntity('TblImport')->findAll();
        if (null !== $EntityList) {
            foreach ($EntityList as $Entity) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity, true);
                $Manager->bulkKillEntity($Entity);
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
            return true;
        }
        return false;
    }
}