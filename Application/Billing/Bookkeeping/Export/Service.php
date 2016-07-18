<?php

namespace SPHERE\Application\Billing\Bookkeeping\Export;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblItem;
use SPHERE\Application\Document\Explorer\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Window\Redirect;

/**
 * Class Service
 * @package SPHERE\Application\Billing\Bookkeeping\Export
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
     * @param $TableHeader
     *
     * @return array
     */
    public function createInvoiceListDatev(&$TableHeader)
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
                            /** @var TblItem $tblItem */
                            foreach ($tblItemList as $tblItem) {
                                $Item['StudentNumber'] = '';
                                $Item['Item'] = $tblItem->getName();
                                $Item['ItemPrice'] = number_format(( $tblItem->getValue() * $tblItem->getQuantity() ), 2);
                                $Item['Reference'] = '';
                                $Item['BillDate'] = $tblInvoice->getEntityCreate()->format('d.m.Y');
                                $Item['Date'] = $tblInvoice->getTargetTime();
                                $Item['BookingText'] = 'Wird noch überarbeitet';

                                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                                if ($tblStudent && $tblStudent->getIdentifier() != '') {
                                    $Item['StudentNumber'] = $tblStudent->getIdentifier();
                                }
                                $tblDebtor = Invoice::useService()->getDebtorByInvoiceAndItem($tblInvoice, $tblItem);
                                if (isset( $tblDebtor )) {
                                    $Item['Reference'] = $tblDebtor->getBankReference();
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
     * @param $TableHeader
     *
     * @return array
     */
    public function createInvoiceListSfirm(&$TableHeader)
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
                            /** @var TblItem $tblItem */
                            foreach ($tblItemList as $tblItem) {
                                $Item['Date'] = $tblInvoice->getTargetTime();
                                $Item['IBAN'] = '';
                                $Item['BIC'] = '';
                                $Item['BillDate'] = $tblInvoice->getEntityCreate()->format('d.m.Y');
                                $Item['Reference'] = '';
                                $Item['Bank'] = '';
                                $Item['Client'] = '';
                                $Item['DebtorNumber'] = '';
                                $Item['InvoiceNumber'] = $tblInvoice->getInvoiceNumber();
                                $Item['BookingText'] = 'Noch in Arbeit';
                                $Item['Owner'] = '';
                                $Item['Item'] = $tblItem->getName();
                                $Item['ItemPrice'] = number_format(( $tblItem->getValue() ), 2);
                                $Item['Quantity'] = $tblItem->getQuantity();
                                $Item['Sum'] = number_format(( $tblItem->getValue() * $tblItem->getQuantity() ), 2);

                                $Item['Reference'] = '';
                                $tblDebtor = Invoice::useService()->getDebtorByInvoiceAndItem($tblInvoice, $tblItem);
                                if (isset( $tblDebtor )) {
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
                                $Item['BookingText'] = 'Wird noch überarbeitet';

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
     * @param                                                                        $TableHeader
     * @param TblDivision|null                                                       $tblDivision
     * @param TblGroup|null                                                          $tblGroup
     * @param \SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem|null $tblItemInventory
     * @param null|array                                                             $tblInvoiceList
     *
     * @return array
     */
    public function createInvoiceListByInvoiceListAndDivision(
        &$TableHeader,
        TblDivision $tblDivision = null,
        TblGroup $tblGroup = null,
        \SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem $tblItemInventory = null,
        $tblInvoiceList = null
    ) {

        $tblPersonList = array();
        $tblItemArray = array();
        $TableContent = array();
        if ($tblInvoiceList) {
            if ($tblDivision != null || $tblGroup != null) {
                $tblPersonList = $this->getPersonListByDivisionAndGroup($tblDivision, $tblGroup);
            }

            if ($tblItemInventory != null) {
                $tblItemArray[] = $tblItemInventory;
            }
            foreach ($tblInvoiceList as $tblInvoice) {

                // Personenliste aus allen offenen Rechnungen erstellen
                if ($tblDivision == null && $tblGroup == null) {
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
//                        }
                        }
                    }
                }
                if ($tblItemInventory == null) {
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
            }

            /** @var TblInvoice $tblInvoice */
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

                            // trägt leere Zellen ein
                            foreach ($TableHeader as $key => $value) {
                                if (!isset( $Item[$key] )) {
                                    $Item[$key] = '';
                                }
                            }

                            $ItemExists = false;
                            /** @var TblItem $tblItem */
                            foreach ($tblItemList as $tblItem) {
                                /** @var \SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem $InventoryItem */
                                foreach ($tblItemArray as $key => $InventoryItem) {
                                    if ($InventoryItem->getId() == $tblItem->getServiceTblItem()->getId()) {
                                        $ItemExists = true;

                                        if (empty( $tblDebtor )) {
                                            $tblDebtor = Invoice::useService()->getDebtorByInvoiceAndItem($tblInvoice, $tblItem);
                                        }

                                        $Item['Item'.$key] = number_format(( $tblItem->getValue() * $tblItem->getQuantity() ), 2).' €';

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

                            // nur Einträge mit Artikel aufnehmen
                            if ($ItemExists) {
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
     * @param TblDivision $tblDivision
     * @param TblGroup    $tblGroup
     *
     * @return bool| TblPerson[]
     */
    public function getPersonListByDivisionAndGroup(TblDivision $tblDivision = null, TblGroup $tblGroup = null)
    {
        $tblPersonList = array();
        // Personenliste aus Division & Group erstellen
        if ($tblDivision != null && $tblGroup != null) {
            $tblDivisionPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
            $tblPersonGroupList = Group::useService()->getPersonAllByGroup($tblGroup);
            if ($tblDivisionPersonList && $tblPersonGroupList) {
                foreach ($tblDivisionPersonList as $tblDivisionPerson) {
                    foreach ($tblPersonGroupList as $tblPersonGroup) {
                        if ($tblDivisionPerson->getId() == $tblPersonGroup->getId()) {
                            $tblPersonList[] = $tblDivisionPerson;
                        }
                    }
                }
            }
        }   // PersonenListe aus Division erstellen
        elseif ($tblDivision != null) {
            $tblDivisionPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
            if ($tblDivisionPersonList) {
                foreach ($tblDivisionPersonList as $tblDivisionPerson) {
                    $tblPersonList[] = $tblDivisionPerson;
                }
            }
        }   //PersonenListe aus Group erstellen
        elseif ($tblGroup != null) {
            $tblPersonGroupList = Group::useService()->getPersonAllByGroup($tblGroup);
            if ($tblPersonGroupList) {
                foreach ($tblPersonGroupList as $tblPersonGroup) {
                    $tblPersonList[] = $tblPersonGroup;
                }
            }
        }

        return ( empty( $tblPersonList ) ? false : $tblPersonList );
    }

    /**
     * @param IFormInterface|null $Stage
     * @param null                $Filter
     *
     * @return IFormInterface|string
     */
    public function controlFilter(IFormInterface &$Stage = null, $Filter = null)
    {
        /**
         * Skip to Frontend
         */

        if (null === $Filter) {
            return $Stage;
        }
        $Error = false;
        if (!isset( $Filter['DateFrom'] ) || empty( $Filter['DateFrom'] )) {
            $Stage->setError('Filter[DateFrom]', 'Bitte geben sie einen Anfangszeitraum für die Rechnungen an.');
            $Error = true;
        } else {
            $DateFrom = new \DateTime($Filter['DateFrom']);
            if (!isset( $Filter['DateTo'] ) || empty( $Filter['DateTo'] )) {
                $Filter['DateTo'] = (new \DateTime('now'))->format('d.m.Y');
            }
            $DateTo = new \DateTime($Filter['DateTo']);
            if (!isset( $Filter['Division'] ) || empty( $Filter['Division'] )) {
                $Filter['Division'] = null;
            }
            if (!isset( $Filter['Group'] ) || empty( $Filter['Group'] )) {
                $Filter['Group'] = null;
            }
            if (!isset( $Filter['Item'] ) || empty( $Filter['Item'] )) {
                $Filter['Item'] = null;
            }

            $InvoiceList = Invoice::useService()->getInvoiceAllByDate($DateFrom, $DateTo);
            if (!$InvoiceList) {
                $Stage->setError('Filter[DateFrom]', 'Keine Rechnung im angegebenem Zeitraum');
                $Stage->setError('Filter[DateTo]', 'Keine Rechnung im angegebenem Zeitraum');
                $Error = true;
            }
        }

        if (!$Error) {

            return $Stage.new Redirect('/Billing/Bookkeeping/Export/Filter/View', Redirect::TIMEOUT_SUCCESS
                , array('DateFrom' => $Filter['DateFrom'],
                        'DateTo'   => $Filter['DateTo'],
                        'Division' => $Filter['Division'],
                        'Group'    => $Filter['Group'],
                        'Item'     => $Filter['Item']));
        }
        return $Stage;
    }

    /**
     * @param      $DateFrom
     * @param null $DateTo
     *
     * @return bool|\SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice[]
     */
    public function getInvoiceListByDate($DateFrom, $DateTo = null)
    {

        $DateFrom = new \DateTime($DateFrom);
        if ($DateTo == null) {
            $DateTo = new \DateTime('now');
        } else {
            $DateTo = new \DateTime($DateTo);
        }
        $InvoiceList = Invoice::useService()->getInvoiceAllByDate($DateFrom, $DateTo);
        return $InvoiceList;
    }
}