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
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
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

                                        // Header erstellen (dynamisch)
                                        if (!isset( $TableHeader['Item'.$key] )) {
                                            $TableHeader['Item'.$key] = $tblItem->getName();
                                        }
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
     * @param                                                                        $TableHeader
     * @param TblDivision|null                                                       $tblDivision
     * @param \SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem|null $tblItemInventory
     * @param null                                                                   $tblInvoiceList
     *
     * @return array
     */
    public function createInvoiceListByInvoiceListAndDivision(&$TableHeader, TblDivision $tblDivision = null, \SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem $tblItemInventory = null, $tblInvoiceList = null)
    {

        $tblPersonList = array();
        $tblItemArray = array();
        $TableContent = array();
        if ($tblInvoiceList) {

            if ($tblDivision != null) {
                $tblDivisionPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblDivisionPersonList) {
                    foreach ($tblDivisionPersonList as $tblDivisionPerson) {
                        $tblPersonList[] = $tblDivisionPerson;
                    }
                }
            }
            if ($tblItemInventory != null) {
                $tblItemArray[] = $tblItemInventory;
            }

            // Personenliste aus allen offenen Rechnungen erstellen
            foreach ($tblInvoiceList as $tblInvoice) {

                if ($tblDivision == null) {
                    $PersonList = Invoice::useService()->getPersonAllByInvoice($tblInvoice);
                    if ($PersonList) {
                        foreach ($PersonList as $Person) {
//                        if($tblDivision != null){
//                            $tblDivisionPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
//                            if($tblDivisionPersonList){
//                                foreach($tblDivisionPersonList as $tblDivisionPerson)
//                                {
//                                    if($tblDivisionPerson->getId() == $Person->getId()){
//                                        if (empty( $tblPersonList )) {
//                                            $tblPersonList[] = $Person;
//                                        } else {
//                                            /** @var TblPerson $tblPerson */
//                                            $Found = false;
//                                            foreach ($tblPersonList as $tblPerson) {
//                                                if ($tblPerson->getId() == $Person->getId()) {
//                                                    $Found = true;
//                                                }
//                                            }
//                                            if (!$Found) {
//                                                $tblPersonList[] = $Person;
//                                            }
//                                        }
//                                    }
//                                }
//                            }
//                        } else {
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

//                                        $Item[$key] = $tblItem->getName().' - '.( $tblItem->getValue() * $tblItem->getQuantity() );   // Item Kontrolle (mit Namen)
                                        $Item['Item'.$key] = number_format(( $tblItem->getValue() * $tblItem->getQuantity() ), 2).' €';

                                        // Header erstellen (dynamisch)
                                        if (!isset( $TableHeader['Item'.$key] )) {
                                            $TableHeader['Item'.$key] = $tblItem->getName();
                                        }
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