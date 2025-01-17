<?php

namespace SPHERE\Application\Billing\Inventory\Import;

use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblDebtorPeriodType;
use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Inventory\Import\Service\Data;
use SPHERE\Application\Billing\Inventory\Import\Service\Entity\TblImport;
use SPHERE\Application\Billing\Inventory\Import\Service\Setup;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Billing\Inventory\Import
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
     * @param int $Id
     *
     * @return false|TblImport
     */
    public function getImportById($Id)
    {

        return (new Data($this->getBinding()))->getImportById($Id);
    }

    /**
     * @return false|TblImport[]
     */
    public function getImportAll()
    {

        return (new Data($this->getBinding()))->getImportAll();
    }

    /**
     * @param array $ImportList
     *
     * @return bool
     */
    public function createImportBulk($ImportList)
    {

        return (new Data($this->getBinding()))->createImportBulk($ImportList);
    }

    /**
     * @return string
     */
    public function importBillingData()
    {

        $tblImportList = $this->getImportAll();
        if ($tblImportList) {
            $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_DEBTOR);
            foreach ($tblImportList as $tblImport) {
                $tblPerson = $tblImport->getServiceTblPerson();
                $tblPersonDebtor = $tblImport->getServiceTblPersonDebtor();
                if($tblImport->getOwner()){
                    $Owner = $tblImport->getOwner();
                } else {
                    $Owner = $tblPersonDebtor->getFirstName().' '.$tblPersonDebtor->getLastName();
                }
                $tblBankAccount = $tblBankReference = false;
                if($tblPersonDebtor){
                    // Debitor als Beitragszahler hinzufügen
                    Group::useService()->addGroupPerson($tblGroup, $tblPersonDebtor);

                    //Debitornummer zum Debitor
                    if(($DebtorNumber = $tblImport->getDebtorNumber())){
                        // Debitornummer wird nur gespeichert, wenn noch keine hinterlegt ist.
                        if(!Debtor::useService()->getDebtorNumberByPerson($tblPersonDebtor, true)){
                            Debtor::useService()->createDebtorNumber($tblPersonDebtor, $DebtorNumber);
                        }
                    }
                    // Kontodaten zum Debitor
                    if($tblImport->getIBAN()){
                        $tblBankAccount = Debtor::useService()->createBankAccount($tblPersonDebtor, $Owner,
                            $tblImport->getBank(), $tblImport->getIBAN(), $tblImport->getBIC());
                    }
                }
                if($tblPerson){
                    // Mandatsreferenz
                    if($tblImport->getReference() && $tblImport->getReferenceDate()){
                        $tblBankReference = Debtor::useService()->createBankReference($tblPerson,
                            $tblImport->getReference(),
                            $tblImport->getReferenceDescription(),
                            $tblImport->getReferenceDate());
                    }
                }
                $tblItem = Item::useService()->getItemByName($tblImport->getItem());
                // Zahlungszuweisungen (SEPA) // andere Zahlungsarten werden nicht importiert!
                if($tblBankAccount && $tblBankReference && $tblItem){
                    $tblPaymentType = Balance::useService()->getPaymentTypeByName('SEPA-Lastschrift');
                    if($tblImport->getIsYear()){
                        $tblDebtorPeriodType = Debtor::useService()->getDebtorPeriodTypeByName(TblDebtorPeriodType::ATTR_YEAR);
                    } else {
                        $tblDebtorPeriodType = Debtor::useService()->getDebtorPeriodTypeByName(TblDebtorPeriodType::ATTR_MONTH);
                    }
                    $Value = 0;
                    if(!($tblVariant = Item::useService()->getItemVariantByItemAndName($tblItem, $tblImport->getPriceVariant()))){
                        $tblVariant = null;
                        $Value = $tblImport->getValue();
                    }

                    Debtor::useService()->createDebtorSelection($tblPerson, $tblPersonDebtor, $tblPaymentType, $tblItem, $tblDebtorPeriodType,
                        $tblImport->getPaymentFromDate(), $tblImport->getPaymentTillDate(), $tblVariant, $Value, $tblBankAccount, $tblBankReference);
                }
            }
            //Delete tblImport
            Import::useService()->destroyImport();
        }
        return '';
    }

    /**
     * @return bool
     */
    public function destroyImport()
    {

        return (new Data($this->getBinding()))->destroyImport();
    }
}
