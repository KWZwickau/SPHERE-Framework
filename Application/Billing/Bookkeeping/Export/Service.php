<?php
namespace SPHERE\Application\Billing\Bookkeeping\Export;

use DateTime;
use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Mail\Service\Entity\TblType as TblTypeMail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblType as TblTypePhone;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Billing\Bookkeeping\Export
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

        // no setup to Install
        $Protocol= '';
//        if(!$withData){
//            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
//        }
//        if (!$doSimulation && $withData) {
//            (new Data($this->getBinding()))->setupDatabaseContent();
//        }
        return $Protocol;
    }

    /**
     * @return array|false
     */
    public function getAccountingContentByGroup()
    {

        $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_DEBTOR);
        if(false === $tblGroup){
            return false;
        }
        $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
        if(false === $tblPersonList){
            return false;
        }

        $LastName = array();
        $FirstName = array();
        foreach ($tblPersonList as $key => $tblPerson) {
            $LastName[$key] = $tblPerson->getLastName();
            $FirstName[$key] = $tblPerson->getFirstName();
        }
        array_multisort($LastName, SORT_ASC, $FirstName, SORT_ASC, $tblPersonList);

//        $tblPersonList = (new Sorter($tblPersonList))->sortObjectBy('getLastFirstName');

        $ExcelContent = array();
        $DateNow = new DateTime();
        foreach($tblPersonList as $tblPersonDebtor){
            $item = array();
            $item['DebtorFirstName'] = $tblPersonDebtor->getFirstName();
            $item['DebtorLastName'] = $tblPersonDebtor->getLastName();
            $item['StreetName'] = $item['StreetNumber'] = $item['Code'] = $item['City'] = $item['District'] = '';
            if(($tblAddress = Address::useService()->getAddressByPerson($tblPersonDebtor))){
                $item['StreetName'] = $tblAddress->getStreetName();
                $item['StreetNumber'] = $tblAddress->getStreetNumber();
                if(($tblCity = $tblAddress->getTblCity())){
                    $item['Code'] = $tblCity->getCode();
                    $item['City'] = $tblCity->getName();
                    $item['District'] = $tblCity->getDistrict();
                }
            }
            $item['DebtorNumber'] = '';
            if(($tblDebtorNumberList = Debtor::useService()->getDebtorNumberByPerson($tblPersonDebtor))){
                $tblDebtorNumber = current($tblDebtorNumberList);
                $item['DebtorNumber'] = $tblDebtorNumber->getDebtorNumber();
            }
            $item['MailPrivate'] = $item['MailBusiness'] = '';
            if(($tblToPersonMailList = Mail::useService()->getMailAllByPerson($tblPersonDebtor))){
                foreach($tblToPersonMailList as $tblToPersonMail){
                    if($tblToPersonMail->getTblType()->getName() == TblTypeMail::VALUE_PRIVATE
                        && ($tblMail = $tblToPersonMail->getTblMail())){
                        if($item['MailPrivate']){
                            $item['MailPrivate'] .= '; '.$tblMail->getAddress();
                        } else {
                            $item['MailPrivate'] = $tblMail->getAddress();
                        }

                    }
                    if($tblToPersonMail->getTblType()->getName() == TblTypeMail::VALUE_BUSINESS
                        && ($tblMail = $tblToPersonMail->getTblMail())){
                        if($item['MailBusiness']){
                            $item['MailBusiness'] .= '; '.$tblMail->getAddress();
                        } else {
                            $item['MailBusiness'] = $tblMail->getAddress();
                        }

                    }
                }
            }
            $item['Phone'] = $item['PhoneMobile'] = '';
            if(($tblToPersonPhoneList = Phone::useService()->getPhoneAllByPerson($tblPersonDebtor))){
                foreach($tblToPersonPhoneList as $tblToPersonPhone){
                    if($tblToPersonPhone->getTblType()->getName() == TblTypePhone::VALUE_NAME_PRIVATE
                        && $tblToPersonPhone->getTblType()->getDescription() == TblTypePhone::VALUE_DESCRIPTION_PHONE
                        && ($tblPhone = $tblToPersonPhone->getTblPhone())){
                        if($item['Phone']){
                            $item['Phone'] .= '; '.$tblPhone->getNumber();
                        } else {
                            $item['Phone'] = $tblPhone->getNumber();
                        }
                    }
                    if($tblToPersonPhone->getTblType()->getName() == TblTypePhone::VALUE_NAME_PRIVATE
                        && $tblToPersonPhone->getTblType()->getDescription() == TblTypePhone::VALUE_DESCRIPTION_MOBILE
                        && ($tblPhone = $tblToPersonPhone->getTblPhone())){
                        if($item['PhoneMobile']){
                            $item['PhoneMobile'] .= '; '.$tblPhone->getNumber();
                        } else {
                            $item['PhoneMobile'] = $tblPhone->getNumber();
                        }
                    }
                }
            }
            // muss für den else zweig gesetzt werden
            $item['CreateUpdate'] = $item['ItemName'] =$item['Value'] = $item['VariantPrice'] = '';
            $item['DivisionCourse'] = $item['CauserFirstName'] = $item['CauserLastName'] = '';
            $item['PaymentType'] = $item['ReferenceDate'] = $item['ReferenceNumber'] = '';
            $item['BankName'] = $item['IBAN'] = $item['BIC'] = $item['Owner'] = '';
            $item['From'] = $item['To'] = '';
            if(($tblDerbtorSelectionList = Debtor::useService()->getDebtorSelectionByPersonDebtor($tblPersonDebtor))){
                $UsingBankAccountList = array();
                $tblYearList = Term::useService()->getYearByNow();
                foreach($tblDerbtorSelectionList as $tblDerbtorSelection){
                    // muss für jeden Schleifenaufruf erneut gesetzt werden
                    $item['CreateUpdate'] = $item['ItemName'] =$item['Value'] = $item['VariantPrice'] = '';
                    $item['DivisionCourse'] = $item['CauserFirstName'] = $item['CauserLastName'] = '';
                    $item['PaymentType'] = $item['ReferenceDate'] = $item['ReferenceNumber'] = '';
                    $item['BankName'] = $item['IBAN'] = $item['BIC'] = $item['Owner'] = '';
                    if(($tblBankAccount = $tblDerbtorSelection->getTblBankAccount())){
                        if(($EntityUpdate = $tblBankAccount->getEntityUpdate())){
                            $item['CreateUpdate'] = $EntityUpdate->format('d.m.Y');
                        } else {
                            $item['CreateUpdate'] = $tblBankAccount->getEntityCreate()->format('d.m.Y');
                        }
                        $UsingBankAccountList[] = $tblBankAccount->getId();
                        $item['BankName'] = $tblBankAccount->getBankName();
                        $item['IBAN'] = $tblBankAccount->getIBAN();
                        $item['BIC'] = $tblBankAccount->getBIC();
                        $item['Owner'] = $tblBankAccount->getOwner();
                    }
                    if(($tblItem = $tblDerbtorSelection->getServiceTblItem())){
                        $item['ItemName'] = $tblItem->getName();
                    }
                    // Variant || Value
                    if($tblItemVariant = $tblDerbtorSelection->getServiceTblItemVariant()){
                        $VariantName = $tblItemVariant->getName().($tblItemVariant->getDescription() ? ' - '.$tblItemVariant->getDescription() : '');
                        if(($tblItemCalculationList = Item::useService()->getItemCalculationByItemVariant($tblItemVariant))){
                            foreach($tblItemCalculationList as $tblItemCalculation){
                                if($tblItemCalculation->getDateFrom(true) <= $DateNow
                                    && ($tblItemCalculation->getDateTo() === false || $tblItemCalculation->getDateTo(true) >= $DateNow)){
                                    $item['Variant'] = $VariantName;
                                    $item['VariantPrice'] = $tblItemCalculation->getPriceString();
                                    break;
                                }
                            }
                        }
                    } else {
                        $item['Variant'] = $item['VariantPrice'] = '';
                        $item['Value'] = $tblDerbtorSelection->getValuePriceString();
                    }
                    if(($tblPaymentType = $tblDerbtorSelection->getServiceTblPaymentType())){
                        $item['PaymentType'] = $tblPaymentType->getName();
                    }
                    if(($tblPersonCauser = $tblDerbtorSelection->getServiceTblPersonCauser())){
                        $item['CauserFirstName'] = $tblPersonCauser->getFirstName();
                        $item['CauserLastName'] = $tblPersonCauser->getLastName();
                        if($tblYearList){
                            $DivisionNameList = array();
                            foreach($tblYearList as $tblYear){
                                if(($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByStudentAndYear($tblPersonCauser, $tblYear))){
                                    foreach($tblDivisionCourseList as $tblDivisionCourse){
                                        if($tblDivisionCourse->getType() == TblDivisionCourseType::TYPE_DIVISION
                                        || $tblDivisionCourse->getType() == TblDivisionCourseType::TYPE_CORE_GROUP)
                                        $DivisionNameList[] = $tblDivisionCourse->getDisplayName();
                                    }
                                }
                            }
                            sort($DivisionNameList);
                            $item['DivisionCourse'] = implode(', ', $DivisionNameList);
                        }
                    }
                    if(($tblBankReference = $tblDerbtorSelection->getTblBankReference())){
                        $item['ReferenceDate'] = $tblBankReference->getReferenceDate();
                        $item['ReferenceNumber'] = $tblBankReference->getReferenceNumber();
                    }

                    $item['From'] = $tblDerbtorSelection->getFromDate();
                    $item['To'] = $tblDerbtorSelection->getToDate();

                    array_push($ExcelContent, $item);
                }
//                $UsingBankAccountList
                // vorhandene Kontodaten zu denen keine Zahlungsinformation hinterlegt ist (Bar / Überweisung)
                if(($tblBankAccountList = Debtor::useService()->getBankAccountAllByPerson($tblPersonDebtor))){
                    // Werte für Datensatz nicht verfügbar
                    $item['CreateUpdate'] = $item['ItemName'] =$item['Value'] = $item['VariantPrice'] = '';
                    $item['DivisionCourse'] = $item['CauserFirstName'] = $item['CauserLastName'] = '';
                    $item['PaymentType'] = $item['ReferenceDate'] = $item['ReferenceNumber'] = '';
                    $item['BankName'] = $item['IBAN'] = $item['BIC'] = $item['Owner'] = '';
                    $item['From'] = $item['To'] = '';
                    foreach($tblBankAccountList as $tblBankAccount){
                        // nicht verwendete Bankdaten hinzufügen
                        if(!in_array($tblBankAccount->getId(), $UsingBankAccountList)){
                            $item['BankName'] = $tblBankAccount->getBankName();
                            $item['IBAN'] = $tblBankAccount->getIBAN();
                            $item['BIC'] = $tblBankAccount->getBIC();
                            $item['Owner'] = $tblBankAccount->getOwner();
                            if(($EntityUpdate = $tblBankAccount->getEntityUpdate())){
                                $item['CreateUpdate'] = $EntityUpdate->format('d.m.Y');
                            } else {
                                $item['CreateUpdate'] = $tblBankAccount->getEntityCreate()->format('d.m.Y');
                            }
                            array_push($ExcelContent, $item);
                        }
                    }
                }
            } else {
                $item['CreateUpdate'] = $item['BankName'] = $item['IBAN'] = $item['BIC'] = $item['Owner'] = '';
                // keine Zahlungszuweisung
                // vorhandene Kontodaten
                if(($tblBankAccountList = Debtor::useService()->getBankAccountAllByPerson($tblPersonDebtor))){
                    foreach($tblBankAccountList as $tblBankAccount){
                        $item['BankName'] = $tblBankAccount->getBankName();
                        $item['IBAN'] = $tblBankAccount->getIBAN();
                        $item['BIC'] = $tblBankAccount->getBIC();
                        $item['Owner'] = $tblBankAccount->getOwner();
                        if(($EntityUpdate = $tblBankAccount->getEntityUpdate())){
                            $item['CreateUpdate'] = $EntityUpdate->format('d.m.Y');
                        } else {
                            $item['CreateUpdate'] = $tblBankAccount->getEntityCreate()->format('d.m.Y');
                        }
                        array_push($ExcelContent, $item);
                    }
                } else {
                    // Ohne Zahlungszuweisungen oder Kontodaten
                    array_push($ExcelContent, $item);
                }
            }
        }
        return (!empty($ExcelContent) ? $ExcelContent : false);
    }

    /**
     * @param array $ExcelContent
     *
     * @return false|FilePointer
     */
    public function createAccountingExcelDownload($ExcelContent = array())
    {
        $fileLocation = Storage::createFilePointer('xlsx');
        $Column = 0;
        $Row = 0;
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());
        $export->setValue($export->getCell($Column++, $Row), "Datum der Bankdaten");
        $export->setValue($export->getCell($Column++, $Row), "Beitragszahler Vorname");
        $export->setValue($export->getCell($Column++, $Row), "Beitragszahler Nachname");
        $export->setValue($export->getCell($Column++, $Row), "E-Mail Privat");
        $export->setValue($export->getCell($Column++, $Row), "E-Mail Geschäftlich");
        $export->setValue($export->getCell($Column++, $Row), "Telefon Festnetz");
        $export->setValue($export->getCell($Column++, $Row), "Telefon Mobil");
        $export->setValue($export->getCell($Column++, $Row), "Straße");
        $export->setValue($export->getCell($Column++, $Row), "Hausnr.");
        $export->setValue($export->getCell($Column++, $Row), "PLZ");
        $export->setValue($export->getCell($Column++, $Row), "Stadt");
        $export->setValue($export->getCell($Column++, $Row), "Ortsteil");
        $export->setValue($export->getCell($Column++, $Row), "Art der Zahlung");
        $export->setValue($export->getCell($Column++, $Row), "Name der Bank");
        $export->setValue($export->getCell($Column++, $Row), "Kontoinhaber");
        $export->setValue($export->getCell($Column++, $Row), "IBAN");
        $export->setValue($export->getCell($Column++, $Row), "BIC");
        $export->setValue($export->getCell($Column++, $Row), "Debitoren-Nr.");
        $export->setValue($export->getCell($Column++, $Row), "Beitragsverursacher Vorname");
        $export->setValue($export->getCell($Column++, $Row), "Beitragsverursacher Nachname");
        $export->setValue($export->getCell($Column++, $Row), "Klasse/Stammgruppe");
        $export->setValue($export->getCell($Column++, $Row), "Beitragsart");
        $export->setValue($export->getCell($Column++, $Row), "Individualpreis");
        $export->setValue($export->getCell($Column++, $Row), "Preisvariante");
        $export->setValue($export->getCell($Column++, $Row), "Preisvariante Preis");
        $export->setValue($export->getCell($Column++, $Row), "Datum beitragspflichtig von");
        $export->setValue($export->getCell($Column++, $Row), "Datum beitragspflichtig bis");
        $export->setValue($export->getCell($Column++, $Row), "Mandatsreferenznummer gültig ab");
        $export->setValue($export->getCell($Column, $Row), "Mandatsreferenz");

        foreach ($ExcelContent as $Content) {
            $Row++;
            $Column = 0;

            $export->setValue($export->getCell($Column++, $Row), $Content['CreateUpdate']);
            $export->setValue($export->getCell($Column++, $Row), $Content['DebtorFirstName']);
            $export->setValue($export->getCell($Column++, $Row), $Content['DebtorLastName']);
            $export->setValue($export->getCell($Column++, $Row), $Content['MailPrivate']);
            $export->setValue($export->getCell($Column++, $Row), $Content['MailBusiness']);
            $export->setValue($export->getCell($Column++, $Row), $Content['Phone']);
            $export->setValue($export->getCell($Column++, $Row), $Content['PhoneMobile']);
            $export->setValue($export->getCell($Column++, $Row), $Content['StreetName']);
            $export->setValue($export->getCell($Column++, $Row), $Content['StreetNumber']);
            $export->setValue($export->getCell($Column++, $Row), $Content['Code']);
            $export->setValue($export->getCell($Column++, $Row), $Content['City']);
            $export->setValue($export->getCell($Column++, $Row), $Content['District']);
            $export->setValue($export->getCell($Column++, $Row), $Content['PaymentType']);
            $export->setValue($export->getCell($Column++, $Row), $Content['BankName']);
            $export->setValue($export->getCell($Column++, $Row), $Content['Owner']);
            $export->setValue($export->getCell($Column++, $Row), $Content['IBAN']);
            $export->setValue($export->getCell($Column++, $Row), $Content['BIC']);
            $export->setValue($export->getCell($Column++, $Row), $Content['DebtorNumber']);
            $export->setValue($export->getCell($Column++, $Row), $Content['CauserFirstName']);
            $export->setValue($export->getCell($Column++, $Row), $Content['CauserLastName']);
            $export->setValue($export->getCell($Column++, $Row), $Content['DivisionCourse']);
            $export->setValue($export->getCell($Column++, $Row), $Content['ItemName']);
            $export->setValue($export->getCell($Column++, $Row), $Content['Value']);
            $export->setValue($export->getCell($Column++, $Row), $Content['Variant'] ?? '');
            $export->setValue($export->getCell($Column++, $Row), $Content['VariantPrice'] ?? '');
            $export->setValue($export->getCell($Column++, $Row), $Content['From']);
            $export->setValue($export->getCell($Column++, $Row), $Content['To']);
            $export->setValue($export->getCell($Column++, $Row), $Content['ReferenceDate']);
            $export->setValue($export->getCell($Column, $Row), $Content['ReferenceNumber']);

        }

        //Column width
        $Column = 0;
        $export->setStyle($export->getCell($Column, 0), $export->getCell($Column++, $Row))->setColumnWidth(15);
        $export->setStyle($export->getCell($Column, 0), $export->getCell($Column++, $Row))->setColumnWidth(15);
        $export->setStyle($export->getCell($Column, 0), $export->getCell($Column++, $Row))->setColumnWidth(15);
        $export->setStyle($export->getCell($Column, 0), $export->getCell($Column++, $Row))->setColumnWidth(10);
        $export->setStyle($export->getCell($Column, 0), $export->getCell($Column++, $Row))->setColumnWidth(10);
        $export->setStyle($export->getCell($Column, 0), $export->getCell($Column++, $Row))->setColumnWidth(15);
        $export->setStyle($export->getCell($Column, 0), $export->getCell($Column++, $Row))->setColumnWidth(15);
        $export->setStyle($export->getCell($Column, 0), $export->getCell($Column++, $Row))->setColumnWidth(20);
        $export->setStyle($export->getCell($Column, 0), $export->getCell($Column++, $Row))->setColumnWidth(5);
        $export->setStyle($export->getCell($Column, 0), $export->getCell($Column++, $Row))->setColumnWidth(7);
        $export->setStyle($export->getCell($Column, 0), $export->getCell($Column++, $Row))->setColumnWidth(15);
        $export->setStyle($export->getCell($Column, 0), $export->getCell($Column++, $Row))->setColumnWidth(10);
        $export->setStyle($export->getCell($Column, 0), $export->getCell($Column++, $Row))->setColumnWidth(16);
        $export->setStyle($export->getCell($Column, 0), $export->getCell($Column++, $Row))->setColumnWidth(15);
        $export->setStyle($export->getCell($Column, 0), $export->getCell($Column++, $Row))->setColumnWidth(25);
        $export->setStyle($export->getCell($Column, 0), $export->getCell($Column++, $Row))->setColumnWidth(24);
        $export->setStyle($export->getCell($Column, 0), $export->getCell($Column++, $Row))->setColumnWidth(14);
        $export->setStyle($export->getCell($Column, 0), $export->getCell($Column++, $Row))->setColumnWidth(13);
        $export->setStyle($export->getCell($Column, 0), $export->getCell($Column++, $Row))->setColumnWidth(15);
        $export->setStyle($export->getCell($Column, 0), $export->getCell($Column++, $Row))->setColumnWidth(15);
        $export->setStyle($export->getCell($Column, 0), $export->getCell($Column++, $Row))->setColumnWidth(10);
        $export->setStyle($export->getCell($Column, 0), $export->getCell($Column++, $Row))->setColumnWidth(20);
        $export->setStyle($export->getCell($Column, 0), $export->getCell($Column++, $Row))->setColumnWidth(9);
        $export->setStyle($export->getCell($Column, 0), $export->getCell($Column++, $Row))->setColumnWidth(50);
        $export->setStyle($export->getCell($Column, 0), $export->getCell($Column++, $Row))->setColumnWidth(9);
        $export->setStyle($export->getCell($Column, 0), $export->getCell($Column++, $Row))->setColumnWidth(11);
        $export->setStyle($export->getCell($Column, 0), $export->getCell($Column++, $Row))->setColumnWidth(11);
        $export->setStyle($export->getCell($Column, 0), $export->getCell($Column++, $Row))->setColumnWidth(11);
        $export->setStyle($export->getCell($Column, 0), $export->getCell($Column, $Row))->setColumnWidth(10);

        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }
}