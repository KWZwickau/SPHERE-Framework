<?php

namespace SPHERE\Application\Billing\Inventory\Import;

use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblDebtorPeriodType;
use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Inventory\Import\Service\Data;
use SPHERE\Application\Billing\Inventory\Import\Service\Entity\TblImport;
use SPHERE\Application\Billing\Inventory\Import\Service\Setup;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
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
     * @return LayoutRow[]
     */
    public function importBillingData()
    {

        $InfoList = array();
        $tblImportList = $this->getImportAll();
        if ($tblImportList) {
            foreach ($tblImportList as $tblImport) {
                $tblPerson = $tblImport->getServiceTblPerson();
                $tblPersonDebtor = $tblImport->getServiceTblPersonDebtor();
                $tblBankAccount = $tblBankReference = false;
                if($tblPersonDebtor){
                    //Debitornummer zum Debitor
                    if(($DebtorNumber = $tblImport->getDebtorNumber())){
                        Debtor::useService()->createDebtorNumber($tblPersonDebtor, $DebtorNumber);
                    }
                    // Kontodaten zum Debitor
                    if($tblImport->getIBAN() && $tblImport->getBIC()){
                        $tblBankAccount = Debtor::useService()->createBankAccount($tblPersonDebtor,
                            $tblPersonDebtor->getFirstName().' '.$tblPersonDebtor->getLastName(),
                            $tblImport->getBank(), $tblImport->getIBAN(), $tblImport->getBIC());
                    }
                }
                if($tblPerson){
                    // Mandatsreferenz
                    if($tblImport->getReference() && $tblImport->getReferenceDate()){
                        $tblBankReference = Debtor::useService()->createBankReference($tblPerson, $tblImport->getReference(), $tblImport->getReferenceDate());
                    }
                }

                $tblItem = Item::useService()->getItemByName($tblImport->getItem());
                // Zahlungszuweisungen (SEPA)
                if($tblBankAccount && $tblBankReference && $tblItem){
                    $tblPaymentType = Balance::useService()->getPaymentTypeByName('SEPA-Lastschrift');
                    $tblDebtorPeriodType = Debtor::useService()->getDebtorPeriodTypeByName(TblDebtorPeriodType::ATTR_MONTH);
                    $Value = 0;
                    if(!($tblVariant = Item::useService()->getItemVariantByItemAndName($tblItem, $tblImport->getPriceVariant()))){
                        $tblVariant = null;
                        $Value = $tblImport->getValue();
                    }

                    Debtor::useService()->createDebtorSelection($tblPerson, $tblPersonDebtor, $tblPaymentType, $tblItem, $tblDebtorPeriodType,
                        $tblImport->getPaymentFromDate(), $tblImport->getPaymentTillDate(), $tblVariant, $Value, $tblBankAccount, $tblBankReference);
                } else {
                    //ToDO Wie händeln wir Lastschrift/Überweisung/BAR?
                }
            }

            //Delete tblImport
            Import::useService()->destroyImport();
        }


        //ToDO Aufräumen
        $LayoutColumnArray = array();
        if (!empty($InfoList)) {
            // better show result
            foreach ($InfoList as $key => $Info) {
                $divisionName[$key] = strtoupper($Info['DivisionName']);
            }
            array_multisort($divisionName, SORT_NATURAL, $InfoList);
            foreach ($InfoList as $Info) {

                if (isset($Info['DivisionName']) && isset($Info['SubjectList'])) {
                    $LayoutColumnList = array();
                    $PanelContent = array();
                    if (!empty($Info['SubjectList'])) {
                        foreach ($Info['SubjectList'] as $SubjectAndTeacherArray) {
                            if (!empty($SubjectAndTeacherArray)) {
                                foreach ($SubjectAndTeacherArray as $SubjectAndTeacher) {
                                    $PanelContent[] = $SubjectAndTeacher;
                                }
                            }
                        }
                        $LayoutColumnList[] = new LayoutColumn(array(
                                new Title('Klasse: '.$Info['DivisionName']),
                                new Panel('Acronym - Fach'.new PullRight('Lehrer'),
                                    $PanelContent, Panel::PANEL_TYPE_SUCCESS)
                            )
                            , 4);
                    }
                    $LayoutColumnArray = array_merge($LayoutColumnArray, $LayoutColumnList);
                }
            }
        }

        // save clean view by LayoutRows
        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;
        /**
         * @var LayoutColumn $tblPhone
         */
        foreach ($LayoutColumnArray as $LayoutColumn) {
            if ($LayoutRowCount % 3 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($LayoutColumn);
            $LayoutRowCount++;
        }

        return $LayoutRowList;
    }

    /**
     * @return bool
     */
    public function destroyImport()
    {

        return (new Data($this->getBinding()))->destroyImport();
    }
}
