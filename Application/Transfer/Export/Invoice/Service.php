<?php

namespace SPHERE\Application\Transfer\Export\Invoice;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Api\Response;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblItem;
use SPHERE\Application\Document\Explorer\Storage\Storage;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Window\Redirect;

/**
 * Class Service
 * @package SPHERE\Application\Transfer\Export\Invoice
 */
class Service
{

    /**
     * @param array $TableHeader
     *
     * @return array
     */
    public function createInvoiceList(&$TableHeader)
    {

        $tblInvoiceList = Invoice::useService()->getInvoiceByIsPaid(false);
        $tblPersonList = array();
        $tblItemArray = array();
        $TableContent = array();
        if ($tblInvoiceList) {
            // Personenliste aus allen offenen Rechnungen erstellen
            foreach ($tblInvoiceList as $tblInvoice) {
                $PersonList = Invoice::useService()->getPersonAllByInvoice($tblInvoice);
                if ($PersonList) {
                    foreach ($PersonList as $Person) {
                        if (empty( $tblPersonList )) {
                            $tblPersonList[] = $Person;
                        } else {
                            /** @var TblPerson $tblPerson */
                            $Found = false;
                            foreach ($tblPersonList as $tblPerson) {
                                if ($tblPerson->getId() == $Person->getId()) {
                                    $Found = true;
                                }
                            }
                            if (!$Found) {
                                $tblPersonList[] = $Person;
                            }
                        }
                    }
                }
                $tblItemList = Invoice::useService()->getItemAllByInvoice($tblInvoice);
                if ($tblItemList) {
                    foreach ($tblItemList as $Item) {
                        if (empty( $tblItemArray )) {
                            $InventoryItem = $Item->getServiceTblItem();
                            if ($InventoryItem) {
                                $tblItemArray[] = $InventoryItem;
                            }
                        } else {
                            /** @var TblItem $tblItem */
                            $Found = false;
                            $InventoryItem = $Item->getServiceTblItem();
                            if ($InventoryItem) {
                                foreach ($tblItemArray as $tblItem) {
                                    if ($tblItem->getId() == $InventoryItem->getId()) {
                                        $Found = true;
                                    }
                                }
                                if (!$Found) {
                                    $tblItemArray[] = $InventoryItem;
                                }
                            }
                        }
                    }
                }
            }

            foreach ($tblInvoiceList as $tblInvoice) {
                if (!empty( $tblPersonList )) {
                    array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, $tblInvoice, $tblItemArray, &$TableHeader) {

                        $tblItemList = Invoice::useService()->getItemAllInvoiceAndPerson($tblInvoice, $tblPerson);
                        if ($tblItemList) {

                            $Item['Debtor'] = '';
                            $Item['Name'] = $tblPerson->getLastFirstName();
                            $Item['StudentNumber'] = 'keine';
                            $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                            if ($tblStudent && $tblStudent->getIdentifier() != '') {
                                $Item['StudentNumber'] = $tblStudent->getIdentifier();
                            }
                            $Item['Date'] = $tblInvoice->getTargetTime();

                            // trägt bei fehlenden angaben leere Zellen ein
                            foreach ($TableHeader as $key => $value) {
                                if (!isset( $Item[$key] )) {
                                    $Item[$key] = '';
                                }
                            }

                            /** @var TblItem $tblItem */
                            foreach ($tblItemList as $tblItem) {
                                /** @var \SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem $InventoryItem */
                                foreach ($tblItemArray as $key => $InventoryItem) {
                                    if ($InventoryItem->getId() == $tblItem->getServiceTblItem()->getId()) {
//                                        $Item[$key] = $tblItem->getName().' - '.( $tblItem->getValue() * $tblItem->getQuantity() );   // Item Kontrolle (mit Namen)
                                        $Item['Item'.$key] = number_format(( $tblItem->getValue() * $tblItem->getQuantity() ), 2).' €';

                                        if (empty( $tblDebtor )) {
                                            $tblDebtor = Invoice::useService()->getDebtorByInvoiceAndItem($tblInvoice, $tblItem);
                                        }

                                        // Header erstellen (dynamisch)
                                        if (!isset( $TableHeader['Item'.$key] )) {
                                            $TableHeader['Item'.$key] = $tblItem->getName();
                                        }
                                    }
                                }
                            }
                            if (isset( $tblDebtor )) {
                                if (( $tblDebtorService = $tblDebtor->getServiceTblDebtor() )) {
                                    if (( $tblPersonDebtor = $tblDebtorService->getServiceTblPerson() )) {
                                        $Item['Debtor'] = $tblPersonDebtor->getFullName();
                                    }
                                }
                            }

                            array_push($TableContent, $Item);
                        }
                    });
                }
            }
        }

        return $TableContent;
    }

    /**
     * @param array $TableContent
     * @param array $TableHeader
     *
     * @return bool|\SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createInvoiceListExcel($TableContent, $TableHeader)
    {

        if ($TableContent) {

            $fileLocation = Storage::useWriter()->getTemporary('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());

            if (!empty( $TableHeader )) {
                $HeadTitleCount = 0;
                foreach ($TableHeader as $Title) {
                    $export->setValue($export->getCell($HeadTitleCount, "0"), $Title);
                    $HeadTitleCount++;
                }
            }

            $Row = 1;

            foreach ($TableContent as &$Content) {

                $ContentColumn = 0;

                foreach ($Content as &$Column) {

                    if (empty( $Column )) {
                        $Column = ' - ';
                    }
                    $export->setValue($export->getCell($ContentColumn, $Row), $Column);
                    $ContentColumn++;
                }
                $Row++;
            }

//            exit;

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }
        return false;
    }

    /**
     * @param null $tblInvoiceList
     * @param      $PersonFrom
     * @param      $StudentNumber
     * @param      $IBAN
     * @param      $BIC
     * @param      $BankName
     * @param      $Owner
     *
     * @return array
     */
    public function createInvoiceListByPrepare(
//        &$TableHeader,
        $tblInvoiceList = null,
        $PersonFrom,
        $StudentNumber,
        $IBAN,
        $BIC,
        $BankName,
        $Owner
    ) {

        $tblPersonList = array();
        $tblItemArray = array();
        $TableContent = array();
        if ($tblInvoiceList != null) {
            // Personenliste aus allen offenen Rechnungen erstellen
            foreach ($tblInvoiceList as $tblInvoice) {
                $PersonList = Invoice::useService()->getPersonAllByInvoice($tblInvoice);
                if ($PersonList) {
                    foreach ($PersonList as $Person) {
                        if (empty( $tblPersonList )) {
                            $tblPersonList[] = $Person;
                        } else {
                            /** @var TblPerson $tblPerson */
                            $Found = false;
                            foreach ($tblPersonList as $tblPerson) {
                                if ($tblPerson->getId() == $Person->getId()) {
                                    $Found = true;
                                }
                            }
                            if (!$Found) {
                                $tblPersonList[] = $Person;
                            }
                        }
                    }
                }
                $tblItemList = Invoice::useService()->getItemAllByInvoice($tblInvoice);
                if ($tblItemList) {
                    foreach ($tblItemList as $Item) {
                        if (empty( $tblItemArray )) {
                            $InventoryItem = $Item->getServiceTblItem();
                            if ($InventoryItem) {
                                $tblItemArray[] = $InventoryItem;
                            }
                        } else {
                            /** @var TblItem $tblItem */
                            $Found = false;
                            $InventoryItem = $Item->getServiceTblItem();
                            if ($InventoryItem) {
                                foreach ($tblItemArray as $tblItem) {
                                    if ($tblItem->getId() == $InventoryItem->getId()) {
                                        $Found = true;
                                    }
                                }
                                if (!$Found) {
                                    $tblItemArray[] = $InventoryItem;
                                }
                            }
                        }
                    }
                }
            }

            /** @var TblInvoice $tblInvoice */
            foreach ($tblInvoiceList as $tblInvoice) {
                if (!empty( $tblPersonList )) {
                    array_walk($tblPersonList, function (TblPerson $tblPerson) use (
                        &$TableContent,
                        $tblInvoice,
                        $tblItemArray,
                        $PersonFrom,
                        $StudentNumber,
                        $IBAN,
                        $BIC,
                        $BankName,
                        $Owner
                    ) {

                        $tblItemList = Invoice::useService()->getItemAllInvoiceAndPerson($tblInvoice, $tblPerson);
                        if ($tblItemList) {
                            /** @var TblItem $tblItem */
                            foreach ($tblItemList as $tblItem) {
                                $Item['Payer'] = '';
                                $Item['PersonFrom'] = $tblPerson->getFullName();
                                $Item['StudentNumber'] = '';
                                $Item['Date'] = $tblInvoice->getTargetTime();
                                $Item['IBAN'] = '';
                                $Item['BIC'] = '';
                                $Item['BillDate'] = $tblInvoice->getEntityCreate()->format('d.m.Y');
                                $Item['Reference'] = '';
                                $Item['Bank'] = '';
                                $Item['Client'] = '';
                                $Item['DebtorNumber'] = '';
                                $Item['Owner'] = '';
                                $Item['InvoiceNumber'] = $tblInvoice->getInvoiceNumber();
                                $Item['Item'] = $tblItem->getName();
                                $Item['ItemPrice'] = number_format(( $tblItem->getValue() ), 2);
                                $Item['Quantity'] = $tblItem->getQuantity();
                                $Item['Sum'] = number_format(( $tblItem->getValue() * $tblItem->getQuantity() ), 2);

                                $Item['Reference'] = '';
                                $tblDebtor = Invoice::useService()->getDebtorByInvoiceAndItem($tblInvoice, $tblItem);
                                if (isset( $tblDebtor )) {
                                    $tblServiceDebtor = $tblDebtor->getServiceTblDebtor();
                                    if ($tblServiceDebtor) {
                                        $tblPersonPayer = $tblServiceDebtor->getServiceTblPerson();
                                        if ($tblPersonPayer) {
                                            $Item['Payer'] = $tblPersonPayer->getFullName();
                                        }
                                    }
                                    $Item['IBAN'] = $tblDebtor->getIBAN();
                                    $Item['BIC'] = $tblDebtor->getBIC();
                                    $Item['Reference'] = $tblDebtor->getBankReference();
                                    $Item['Bank'] = $tblDebtor->getBankName();
                                    $Item['DebtorNumber'] = $tblDebtor->getDebtorNumber();
                                    $Item['Owner'] = $tblDebtor->getOwner();
                                }
                                if (Consumer::useService()->getConsumerBySession()->getName()) {
                                    $Item['Client'] = Consumer::useService()->getConsumerBySession()->getName();
                                }
                                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                                if ($tblStudent) {
                                    $Item['StudentNumber'] = $tblStudent->getIdentifier();
                                }
                                if ($BankName == 0) {
                                    unset( $Item['Bank'] );
                                }
                                if ($Owner == 0) {
                                    unset( $Item['Owner'] );
                                }
                                if ($IBAN == 0) {
                                    unset( $Item['IBAN'] );
                                }
                                if ($BIC == 0) {
                                    unset( $Item['BIC'] );
                                }
                                if ($StudentNumber == 0) {
                                    unset( $Item['StudentNumber'] );
                                }
                                if ($PersonFrom == 0) {
                                    unset( $Item['PersonFrom'] );
                                }

                                array_push($TableContent, $Item);
                            }
                        }
                    });
                }
            }
        }
        return $TableContent;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param null                $Prepare
     *
     * @return IFormInterface|string
     */
    public function controlPrepare(IFormInterface &$Stage = null, $Prepare = null)
    {
        ini_set('display_errors', 1);
        /**
         * Skip to Frontend
         */

        if (null === $Prepare) {
            return $Stage;
        }
        $Error = false;
        if (!isset( $Prepare['DateFrom'] ) || empty( $Prepare['DateFrom'] )) {
            $Stage->setError('Prepare[DateFrom]', 'Bitte geben sie einen Anfangszeitraum für die Rechnungen an.');
            $Error = true;
        } else {
            $DateFrom = new \DateTime($Prepare['DateFrom']);
            if (!isset( $Prepare['DateTo'] ) || empty( $Prepare['DateTo'] )) {
                $Prepare['DateTo'] = (new \DateTime('now'))->format('d.m.Y');
            }
            $DateTo = new \DateTime($Prepare['DateTo']);
            $InvoiceList = Invoice::useService()->getInvoiceAllByDate($DateFrom, $DateTo);
            if (!$InvoiceList) {
                $Stage->setError('Prepare[DateFrom]', 'Keine Rechnung im angegebenem Zeitraum');
                $Stage->setError('Prepare[DateTo]', 'Keine Rechnung im angegebenem Zeitraum');
                $Error = true;
            }
        }

        if (!$Error) {
            $DateFrom = $Prepare['DateFrom'];
            if (!isset( $Prepare['DateTo'] ) || empty( $Prepare['DateTo'] )) {
                $Prepare['DateTo'] = (new \DateTime('now'))->format('d.m.Y');
            }
            $DateTo = $Prepare['DateTo'];
            $BankName = ( isset( $Prepare['BankName'] ) ? 1 : 0 );
            $Owner = ( isset( $Prepare['Owner'] ) ? 1 : 0 );
            $IBAN = ( isset( $Prepare['IBAN'] ) ? 1 : 0 );
            $BIC = ( isset( $Prepare['BIC'] ) ? 1 : 0 );
            $StudentNumber = ( isset( $Prepare['StudentNumber'] ) ? 1 : 0 );
            $PersonFrom = ( isset( $Prepare['PersonFrom'] ) ? 1 : 0 );
            $Status = $Prepare['Status'];

            return $Stage.new Redirect('/Billing/Bookkeeping/Export/Prepare/View', Redirect::TIMEOUT_SUCCESS
                , array('Filter' => (new Response())->addData(array(
                    'DateFrom'      => $DateFrom,
                    'DateTo'        => $DateTo,
                    'BankName'      => $BankName,
                    'Owner'         => $Owner,
                    'IBAN'          => $IBAN,
                    'BIC'           => $BIC,
                    'StudentNumber' => $StudentNumber,
                    'PersonFrom'    => $PersonFrom,
                    'Status'        => $Status
                ))->__toString()
                ));
        }

        return $Stage;
    }

    /**
     * @param      $DateFrom
     * @param null $DateTo
     * @param int  $Status "Invoice" 1 = open, 2 = paid, 3 = storno
     *
     * @return bool|\SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice[]
     */
    public function getInvoiceListByDate($DateFrom, $DateTo = null, $Status = 1)
    {

        $DateFrom = new \DateTime($DateFrom);
        if ($DateTo == null) {
            $DateTo = new \DateTime('now');
        } else {
            $DateTo = new \DateTime($DateTo);
        }
        $InvoiceList = Invoice::useService()->getInvoiceAllByDate($DateFrom, $DateTo, $Status);
        return $InvoiceList;
    }
}