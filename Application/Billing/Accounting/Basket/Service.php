<?php

namespace SPHERE\Application\Billing\Accounting\Basket;

use SPHERE\Application\Billing\Accounting\Basket\Service\Data;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasketCommodity;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasketCommodityDebtor;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasketItem;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasketPerson;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasketVerification;
use SPHERE\Application\Billing\Accounting\Basket\Service\Setup;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Inventory\Commodity\Commodity;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodity;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodityItem;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblCalculation;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class Service
 * @package SPHERE\Application\Billing\Accounting\Basket
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }

        return $Protocol;
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool|TblCommodity[]
     */
    public function getCommodityAllByBasket(TblBasket $tblBasket)
    {

        return (new Data($this->getBinding()))->getCommodityAllByBasket($tblBasket);
    }

    /**
     * @return bool|TblBasket[]
     */
    public function getBasketAll()
    {

        return (new Data($this->getBinding()))->getBasketAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblBasketItem
     */
    public function getBasketItemById($Id)
    {

        return (new Data($this->getBinding()))->getBasketItemById($Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblBasketPerson
     */
    public function getBasketPersonById($Id)
    {

        return (new Data($this->getBinding()))->getBasketPersonById($Id);
    }

    /**
     * @param TblBasketCommodity $tblBasketCommodity
     *
     * @return bool|TblBasketCommodityDebtor[]
     */
    public function getBasketCommodityDebtorAllByBasketCommodity(TblBasketCommodity $tblBasketCommodity)
    {

        return (new Data($this->getBinding()))->getBasketCommodityDebtorAllByBasketCommodity($tblBasketCommodity);
    }

    /**
     * @param $Id
     *
     * @return bool|TblBasketCommodity
     */
    public function getBasketCommodityById($Id)
    {

        return (new Data($this->getBinding()))->getBasketCommodityById($Id);
    }

    /**
     * @param TblBasket    $tblBasket
     * @param TblCommodity $tblCommodity
     *
     * @return bool|TblBasketItem[]
     */
    public function getBasketItemAllByBasketAndCommodity(TblBasket $tblBasket, TblCommodity $tblCommodity)
    {

        return (new Data($this->getBinding()))->getBasketItemAllByBasketAndCommodity($tblBasket, $tblCommodity);
    }

    /**
     * @param TblCommodityItem $tblCommodityItem
     *
     * @return bool|TblBasketItem[]
     */
    public function getBasketItemAllByCommodityItem(TblCommodityItem $tblCommodityItem)
    {

        return (new Data($this->getBinding()))->getBasketItemAllByCommodityItem($tblCommodityItem);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return int
     */
    public function countPersonByBasket(TblBasket $tblBasket)
    {

        return (new Data($this->getBinding()))->countPersonByBasket($tblBasket);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool|TblBasketCommodity[]
     */
    public function getBasketCommodityAllByBasket(TblBasket $tblBasket)
    {

        return (new Data($this->getBinding()))->getBasketCommodityAllByBasket($tblBasket);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return false|TblBasketVerification[]
     */
    public function getBasketVerificationByBasket(TblBasket $tblBasket)
    {

        return (new Data($this->getBinding()))->getBasketVerificationByBasket($tblBasket);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool|TblPerson[]
     */
    public function getPersonAllByBasket(TblBasket $tblBasket)
    {

        $tblBasketPersonList = $this->getBasketPersonAllByBasket($tblBasket);
        $tblPerson = array();
        if ($tblBasketPersonList) {
            foreach ($tblBasketPersonList as $tblBasketPerson) {
                array_push($tblPerson, $tblBasketPerson->getServicePeople_Person());
            }
        }


        return ( empty( $tblPerson ) ? false : $tblPerson );
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool|TblItem[]
     */
    public function getItemAllByBasket(TblBasket $tblBasket)
    {

        $tblBasketItemList = $this->getBasketItemAllByBasket($tblBasket);
        $tblItem = array();
        if ($tblBasketItemList) {
            foreach ($tblBasketItemList as $tblBasketItem) {
                array_push($tblItem, $tblBasketItem->getServiceInventoryItem());
            }

        }
        return ( empty( $tblItem ) ? false : $tblItem );
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool|TblBasketPerson[]
     */
    public function getBasketPersonAllByBasket(TblBasket $tblBasket)
    {

        return (new Data($this->getBinding()))->getBasketPersonAllByBasket($tblBasket);
    }

    /**
     * @param IFormInterface $Stage
     * @param                $Basket
     *
     * @return IFormInterface|string
     */
    public function createBasket(IFormInterface &$Stage = null, $Basket)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Basket
        ) {
            return $Stage;
        }

        $Error = false;

        if (isset( $Basket['Name'] ) && empty( $Basket['Name'] )) {
            $Stage->setError('Basket[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }

        if (!$Error) {
            if (!isset( $Basket['Description'] )) {
                $Basket['Description'] = '';
            }

            $tblBasket = (new Data($this->getBinding()))->createBasket(
                $Basket['Name'], $Basket['Description']
            );
            return new Success('Der Warenkorb wurde erfolgreich erstellt')
            .new Redirect('/Billing/Accounting/Basket/Content', Redirect::TIMEOUT_SUCCESS
                , array('Id' => $tblBasket->getId()));
        }

        return $Stage;
    }

    public function createBasketVerification(TblBasket $tblBasket)
    {

        $tblPersonList = $this->getPersonAllByBasket($tblBasket);
        $tblItemList = $this->getItemAllByBasket($tblBasket);

        if (!$tblPersonList && !$tblItemList) {
            return new Warning('Keine Personen und Artikel im Warenkorb')
            .new Redirect('/Billing/Accounting/Basket/Content', Redirect::TIMEOUT_ERROR, array('Id' => $tblBasket->getId()));
        }
        if (!$tblPersonList) {
            return new Warning('Keine Personen im Warenkorb')
            .new Redirect('/Billing/Accounting/Basket/Content', Redirect::TIMEOUT_ERROR, array('Id' => $tblBasket->getId()));
        }
        if (!$tblItemList) {
            return new Warning('Keine Artikel im Warenkorb')
            .new Redirect('/Billing/Accounting/Basket/Content', Redirect::TIMEOUT_ERROR, array('Id' => $tblBasket->getId()));
        }
        $PersonCount = Count($tblPersonList);

        foreach ($tblPersonList as $tblPerson) {
            $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
            $PersonChildRank = false;
            $PersonCourse = false;
            if ($tblStudent) {
                $tblBilling = $tblStudent->getTblStudentBilling();
                if ($tblBilling) {
                    $tblSiblingRank = $tblBilling->getServiceTblSiblingRank();
                    if ($tblSiblingRank) {
                        $PersonChildRank = $tblSiblingRank->getId();
                    }
                }
                $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                if ($tblTransferType) {
                    $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                        $tblTransferType);
                    if ($tblStudentTransfer) {
                        $tblType = $tblStudentTransfer->getServiceTblType();
                        if ($tblType) {
                            $PersonCourse = $tblType->getId();
                        }
                    }
                }
            }
            foreach ($tblItemList as $tblItem) {
                $tblCalculationList = Item::useService()->getCalculationAllByItem($tblItem);
                if (is_array($tblCalculationList)) {
                    /** @var TblCalculation $tblCalculation */
                    if (count($tblCalculationList) === 1) {
                        foreach ($tblCalculationList as $tblCalculation) {
                            if ((new Data($this->getBinding()))->checkBasketVerificationIsSet($tblBasket, $tblPerson, $tblItem)) {
                                break;
                            }
                            $ItemChildRankId = false;
                            $ItemCourseId = false;
                            $tblItemCourseType = $tblCalculation->getServiceSchoolType();
                            if ($tblItemCourseType) {
                                $ItemCourseId = $tblItemCourseType->getId();
                            }
                            $tblItemChildRank = $tblCalculation->getServiceStudentChildRank();
                            if ($tblItemChildRank) {
                                $ItemChildRankId = $tblItemChildRank->getId();
                            }

                            if (false === $ItemChildRankId && false === $ItemCourseId) {
                                print_r('Item ChildRank False && Item Course False'.'<br/>');
                                if ($tblItem->getTblItemType()->getName() === 'Sammelleistung') {
                                    $Price = $tblCalculation->getValue();
                                    $Price = ( ceil(( $Price / $PersonCount ) * 100) ) / 100; // Centbetrag immer aufrunden
                                } else {
                                    $Price = $tblCalculation->getValue();
                                }
                                (new Data($this->getBinding()))->createBasketVerification($tblBasket, $tblPerson, $tblItem, $Price);
                            }
                        }
                    }

                    foreach ($tblCalculationList as $tblCalculation) {
                        if ((new Data($this->getBinding()))->checkBasketVerificationIsSet($tblBasket, $tblPerson, $tblItem)) {
                            break;
                        }
                        $ItemChildRankId = false;
                        $ItemChildRankName = '';
                        $ItemCourseId = false;
                        $tblItemCourseType = $tblCalculation->getServiceSchoolType();
                        if ($tblItemCourseType) {
                            $ItemCourseId = $tblItemCourseType->getId();
                        }
                        $tblItemChildRank = $tblCalculation->getServiceStudentChildRank();
                        if ($tblItemChildRank) {
                            $ItemChildRankId = $tblItemChildRank->getId();
                            $ItemChildRankName = $tblItemChildRank->getName();
                        }
                        // Bedinungen stimmen
                        if ($PersonChildRank === $ItemChildRankId && $PersonCourse === $ItemCourseId) {
                            print_r($PersonChildRank.' = '.$ItemChildRankId.' && '.$PersonCourse.' = '.$ItemCourseId.'<br/>');
                            $Price = $tblCalculation->getValue();
                            (new Data($this->getBinding()))->createBasketVerification($tblBasket, $tblPerson, $tblItem, $Price);
                        }   // Fehlende Geschwisterangabe = 1.Geschwisterkind
                        if ($PersonChildRank === false && $ItemChildRankName === '1. Geschwisterkind' && $PersonCourse === $ItemCourseId) {
                            print_r('False = '.$ItemChildRankName.' && '.$PersonCourse.' = '.$ItemCourseId.'<br/>');
                            $Price = $tblCalculation->getValue();
                            (new Data($this->getBinding()))->createBasketVerification($tblBasket, $tblPerson, $tblItem, $Price);
                        }
                    }


                    foreach ($tblCalculationList as $tblCalculation) {

                        if ((new Data($this->getBinding()))->checkBasketVerificationIsSet($tblBasket, $tblPerson, $tblItem)) {
                            break;
                        }
                        $ItemChildRankId = false;
                        $ItemChildRankName = '';
                        $ItemCourseId = false;

                        $tblItemCourseType = $tblCalculation->getServiceSchoolType();
                        if ($tblItemCourseType) {
                            $ItemCourseId = $tblItemCourseType->getId();
                        }
                        $tblItemChildRank = $tblCalculation->getServiceStudentChildRank();
                        if ($tblItemChildRank) {
                            $ItemChildRankId = $tblItemChildRank->getId();
                            $ItemChildRankName = $tblItemChildRank->getName();
                        }
                        // Ignoriert Geschwisterkinder
                        if (false === $ItemChildRankId && $PersonCourse === $ItemCourseId) {
                            print_r($PersonChildRank.' = '.$ItemChildRankId.' && '.$PersonCourse.' = '.$ItemCourseId.'<br/>');
                            $Price = $tblCalculation->getValue();
                            (new Data($this->getBinding()))->createBasketVerification($tblBasket, $tblPerson, $tblItem, $Price);
                        }
                        // Ignoriert SchulTyp
                        if ($PersonChildRank === $ItemChildRankId && false === $ItemCourseId) {
                            print_r($PersonChildRank.' = '.$ItemChildRankId.' && '.$PersonCourse.' = '.$ItemCourseId.'<br/>');
                            $Price = $tblCalculation->getValue();
                            (new Data($this->getBinding()))->createBasketVerification($tblBasket, $tblPerson, $tblItem, $Price);
                        }
                        // Fehlende Geschwisterangabe = 1.Geschwisterkind
                        if ($PersonChildRank === false && $ItemChildRankName === '1. Geschwisterkind' && false === $ItemCourseId) {
                            print_r('False = '.$ItemChildRankName.' && '.$PersonCourse.' = '.$ItemCourseId.'<br/>');
                            $Price = $tblCalculation->getValue();
                            (new Data($this->getBinding()))->createBasketVerification($tblBasket, $tblPerson, $tblItem, $Price);
                        }
                    }

                    if ($tblItem->getTblItemType()->getName() === 'Sammelleistung') {
                        foreach ($tblCalculationList as $tblCalculation) {
                            if ((new Data($this->getBinding()))->checkBasketVerificationIsSet($tblBasket, $tblPerson, $tblItem)) {
                                break;
                            }
                            $ItemChildRankId = false;
                            $ItemCourseId = false;
                            Debugger::screenDump($ItemChildRankId.' - '.$ItemCourseId);
                            $tblItemCourseType = $tblCalculation->getServiceSchoolType();
                            if ($tblItemCourseType) {
                                $ItemCourseId = $tblItemCourseType->getId();
                            }
                            $tblItemChildRank = $tblCalculation->getServiceStudentChildRank();
                            if ($tblItemChildRank) {
                                $ItemChildRankId = $tblItemChildRank->getId();
                            }
                            // Das Verteilen der Sammelleistung bei vergebenen Bedingungen an alle nicht der Bedingung zutreffenden Personen
                            if (false === $ItemChildRankId && false === $ItemCourseId) {
                                print_r('Item ChildRank False && Item Course False'.'<br/>');
                                $Price = $tblCalculation->getValue();
                                $Price = ( ceil(( $Price / $PersonCount ) * 100) ) / 100; // Centbetrag immer aufrunden
                                (new Data($this->getBinding()))->createBasketVerification($tblBasket, $tblPerson, $tblItem, $Price);
                            }
                        }
                    }
                }
            }
        }
        return new Success('Warenkorb bereitmachen für Bearbeitung')
        .new Redirect('/Billing/Accounting/Basket/Verification', 600 /*Redirect::TIMEOUT_WAIT*/, array('Id' => $tblBasket->getId()));
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return array|bool
     */
    public function getUnusedCommodityByBasket(TblBasket $tblBasket)
    {

        $tblBasketItemList = Basket::useService()->getBasketItemAllByBasket($tblBasket);
        $ItemList = array();
        if ($tblBasketItemList) {
            foreach ($tblBasketItemList as $tblBasketItem) {
                $ItemList[] = $tblBasketItem->getServiceInventoryItem();
            }
        }

        $tblCommodityAll = Commodity::useService()->getCommodityAll();
        $tblCommodityList = array();

        if (!empty( $tblCommodityAll )) {
            if (empty( $ItemList )) {
                $tblCommodityList = $tblCommodityAll;
            } else {
                foreach ($tblCommodityAll as $tblCommodity) {
                    $CommodityItemList = Commodity::useService()->getItemAllByCommodity($tblCommodity);
                    if (!empty( $CommodityItemList )) {
                        $CommodityItemList = array_udiff($CommodityItemList, $ItemList,
                            function (TblItem $ObjectA, TblItem $ObjectB) {

                                return $ObjectA->getId() - $ObjectB->getId();
                            }
                        );
                        if (!empty( $CommodityItemList )) {
                            $tblCommodityList[] = $tblCommodity;
                        }
                    }
                }
            }
        }

        return ( ( $tblCommodityList === null ) ? false : $tblCommodityList );
    }

    /**
     * @param IFormInterface $Stage
     * @param TblBasket      $tblBasket
     * @param                $Basket
     *
     * @return IFormInterface|string
     */
    public function changeBasket(IFormInterface &$Stage = null, TblBasket $tblBasket, $Basket)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Basket
        ) {
            return $Stage;
        }

        $Error = false;

        if (isset( $Basket['Name'] ) && empty( $Basket['Name'] )) {
            $Stage->setError('Basket[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }

        if (!$Error) {
            if ((new Data($this->getBinding()))->updateBasket(
                $tblBasket,
                $Basket['Name'],
                $Basket['Description']
            )
            ) {
                $Stage .= new Success('Änderungen gespeichert, die Daten werden neu geladen...')
                    .new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_SUCCESS);
            } else {
                $Stage .= new Danger('Änderungen konnten nicht gespeichert werden');
            };
        }
        return $Stage;
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return string
     */
    public function destroyBasket(TblBasket $tblBasket)
    {

        $tblBasket = (new Data($this->getBinding()))->destroyBasket($tblBasket);
        if ($tblBasket) {
            return new Success('Der Warenkorb wurde erfolgreich gelöscht')
            .new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_SUCCESS);
        } else {
            return new Warning('Der Warenkorb konnte nicht gelöscht werden')
            .new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param TblBasketVerification $tblBasketVerification
     *
     * @return string
     */
    public function destroyBasketVerification(TblBasketVerification $tblBasketVerification)
    {

        if ((new Data($this->getBinding()))->destroyBasketVerification($tblBasketVerification)) {
            return new Success('Der Eintrag wurde erfolgreich gelöscht')
            .new Redirect('/Billing/Accounting/Basket/Verification', Redirect::TIMEOUT_SUCCESS,
                array('Id' => $tblBasketVerification->getTblBasket()->getId()));
        } else {
            return new Warning('Der Eintrag konnte nicht gelöscht werden')
            .new Redirect('/Billing/Accounting/Basket/Verification', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblBasketVerification->getTblBasket()->getId()));
        }
    }

    /**
     * @param TblBasketVerification $tblBasketVerification
     *
     * @return bool
     */
    public function destroyBasketVerificationList(TblBasketVerification $tblBasketVerification)
    {

        return (new Data($this->getBinding()))->destroyBasketVerification($tblBasketVerification);
    }

    /**
     * @param TblBasket    $tblBasket
     * @param TblCommodity $tblCommodity
     *
     * @return string
     */
    public function addCommodityToBasket(TblBasket $tblBasket, TblCommodity $tblCommodity)
    {

        if ((new Data($this->getBinding()))->addBasketItemsByCommodity($tblBasket, $tblCommodity)) {
            return new Success('Die Artikelgruppe '.$tblCommodity->getName().' wurde erfolgreich hinzugefügt')
            .new Redirect('/Billing/Accounting/Basket/Item/Select', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblBasket->getId()));
        } else {
            return new Warning('Die Artikelgruppe '.$tblCommodity->getName().' konnte nicht kmplett hinzugefügt werden')
            .new Redirect('/Billing/Accounting/Basket/Item/Select', Redirect::TIMEOUT_ERROR, array('Id' => $tblBasket->getId()));
        }
    }

    /**
     * @param TblBasket $tblBasket
     * @param TblItem   $tblItem
     *
     * @return string
     */
    public function addItemToBasket(TblBasket $tblBasket, TblItem $tblItem)
    {

        (new Data($this->getBinding()))->addItemToBasket($tblBasket, $tblItem);

        return new Success('Der Artikel '.$tblItem->getName().' wurde erfolgreich hinzugefügt')
        .new Redirect('/Billing/Accounting/Basket/Item/Select', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblBasket->getId()));
    }

    /**
     * @param TblBasket    $tblBasket
     * @param TblCommodity $tblCommodity
     *
     * @return string
     */
    public function removeCommodityToBasket(TblBasket $tblBasket, TblCommodity $tblCommodity)
    {

        if ((new Data($this->getBinding()))->removeBasketItemsByCommodity($tblBasket, $tblCommodity)) {
            return new Success('Die Leistung '.$tblCommodity->getName().' wurde erfolgreich entfernt')
            .new Redirect('/Billing/Accounting/Basket/Commodity/Select', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblBasket->getId()));
        } else {
            return new Warning('Die Leistung '.$tblCommodity->getName().' konnte nicht entfernt werden')
            .new Redirect('/Billing/Accounting/Basket/Commodity/Select', Redirect::TIMEOUT_ERROR, array('Id' => $tblBasket->getId()));
        }
    }

    /**
     * @param TblBasketItem $tblBasketItem
     *
     * @return string
     */
    public function removeBasketItem(TblBasketItem $tblBasketItem)
    {

        if ((new Data($this->getBinding()))->removeBasketItem($tblBasketItem)) {
            return new Success('Der Artikel '.$tblBasketItem->getServiceInventoryItem()->getName().' wurde erfolgreich entfernt')
            .new Redirect('/Billing/Accounting/Basket/Item/Select', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblBasketItem->getTblBasket()->getId()));
        } else {
            return new Warning('Der Artikel '.$tblBasketItem->getServiceInventoryItem()->getName().' konnte nicht entfernt werden')
            .new Redirect('/Billing/Accounting/Basket/Item/Select', Redirect::TIMEOUT_ERROR, array('Id' => $tblBasketItem->getTblBasket()->getId()));
        }
    }

    public function changeBasketVerification(IFormInterface &$Stage = null, TblBasketVerification $tblBasketVerification, $Item)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Item
        ) {
            return $Stage;
        }

        $Error = false;

        if (isset( $Item['Price'] ) && empty( $Item['Price'] )) {
            $Stage->setError('Item[Price]', 'Bitte geben Sie einen Preis an');
            $Error = true;
        }
        if (isset( $Item['Quantity'] ) && empty( $Item['Quantity'] )) {
            $Stage->setError('Item[Quantity]', 'Bitte geben Sie eine Anzahl an');
            $Error = true;
        } else {
            $Item['Quantity'] = (int)round(str_replace(',', '.', $Item['Quantity']), 0);
            if (!is_integer($Item['Quantity']) || $Item['Quantity'] < 1) {
                $Stage->setError('Item[Quantity]', 'Bitte geben Sie eine Natürliche Zahl an');
                $Error = true;
            }
        }
        if (!$Error) {
            if ($Item['PriceChoice'] === 'Einzelpreis') {
                $Item['Price'] = $Item['Price'] * $Item['Quantity'];
            }
            if ((new Data($this->getBinding()))->updateBasketVerification(
                $tblBasketVerification,
                $Item['Price'],
                $Item['Quantity']
            )
            ) {
                $Stage .= new Success('Änderungen gespeichert, die Daten werden neu geladen...')
                    .new Redirect('/Billing/Accounting/Basket/Verification', Redirect::TIMEOUT_SUCCESS,
                        array('Id' => $tblBasketVerification->getTblBasket()->getId()));
            } else {
                $Stage .= new Danger('Änderungen konnten nicht gespeichert werden')
                    .new Redirect('/Billing/Accounting/Basket/Verification', Redirect::TIMEOUT_ERROR,
                        array('Id' => $tblBasketVerification->getTblBasket()->getId()));
            };
        }
        return $Stage;
    }

    /**
     * @param IFormInterface $Stage
     * @param TblBasketItem  $tblBasketItem
     * @param                $BasketItem
     *
     * @return IFormInterface|string
     */
    public function changeBasketItem(IFormInterface &$Stage = null, TblBasketItem $tblBasketItem, $BasketItem)
    {

        /**
         * Skip to Frontend
         */
        if (null === $BasketItem
        ) {
            return $Stage;
        }

        $Error = false;

        if (isset( $BasketItem['Price'] ) && empty( $BasketItem['Price'] )) {
            $Stage->setError('BasketItem[Price]', 'Bitte geben Sie einen Preis an');
            $Error = true;
        }
        if (isset( $BasketItem['Quantity'] ) && empty( $BasketItem['Quantity'] )) {
            $Stage->setError('BasketItem[Quantity]', 'Bitte geben Sie eine Menge an');
            $Error = true;
        }

        if (!$Error) {
            if ((new Data($this->getBinding()))->updateBasketItem(
                $tblBasketItem,
                $BasketItem['Price'],
                $BasketItem['Quantity']
            )
            ) {
                $Stage .= new Success('Änderungen gespeichert, die Daten werden neu geladen...')
                    .new Redirect('/Billing/Accounting/Basket/Item', Redirect::TIMEOUT_SUCCESS,
                        array('Id' => $tblBasketItem->getTblBasket()->getId()));
            } else {
                $Stage .= new Danger('Änderungen konnten nicht gespeichert werden')
                    .new Redirect('/Billing/Accounting/Basket/Item', Redirect::TIMEOUT_ERROR,
                        array('Id' => $tblBasketItem->getTblBasket()->getId()));
            };
        }
        return $Stage;
    }

    /**
     * @param TblBasket $tblBasket
     * @param TblPerson $tblPerson
     *
     * @return string
     */
    public function addBasketPerson(TblBasket $tblBasket, TblPerson $tblPerson)
    {

        (new Data($this->getBinding()))->addBasketPerson($tblBasket, $tblPerson);

        return new Success('Die Person '.$tblPerson->getFullName().' wurde erfolgreich hinzugefügt')
        .new Redirect('/Billing/Accounting/Basket/Person/Select', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblBasket->getId()));
    }

    /**
     * @param TblBasketPerson $tblBasketPerson
     *
     * @return string
     */
    public function removeBasketPerson(TblBasketPerson $tblBasketPerson)
    {

        if ((new Data($this->getBinding()))->removeBasketPerson($tblBasketPerson)) {
            return new Success('Die Person '.$tblBasketPerson->getServicePeople_Person()->getFullName().' wurde erfolgreich entfernt')
            .new Redirect('/Billing/Accounting/Basket/Person/Select', Redirect::TIMEOUT_SUCCESS,
                array('Id' => $tblBasketPerson->getTblBasket()->getId()));
        } else {
            return new Warning('Die Person '.$tblBasketPerson->getServicePeople_Person()->getFullName().' konnte nicht entfernt werden')
            .new Redirect('/Billing/Accounting/Basket/Person/Select', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblBasketPerson->getTblBasket()->getId()));
        }
    }

    /**
     * @param IFormInterface $Stage
     * @param TblBasket      $tblBasket
     * @param                $Basket
     *
     * @return IFormInterface|string
     */
    public function checkBasket(IFormInterface &$Stage = null, TblBasket $tblBasket, $Basket)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Basket
        ) {
            return $Stage;
        }

        $Error = false;

        if (isset( $Basket['Date'] ) && empty( $Basket['Date'] )) {
            $Stage->setError('Basket[Date]', 'Bitte geben Sie ein Fälligkeitsdatum an');
            $Error = true;
        }

        $ErrorMissing = false;
        if (!$this->getBasketItemAllByBasket($tblBasket)) {
            $Stage .= new Danger("Im Warenkorb befinden sich keine Artikel. Bitte gehen Sie zurück und wählen welche aus");
            $ErrorMissing = true;
        }

        $tblBasketPersonAllByBasket = $this->getBasketPersonAllByBasket($tblBasket);
        if (!$tblBasketPersonAllByBasket) {
            $Stage .= new Danger("Im Warenkorb befinden sich keine Schüler. Bitte gehen Sie zurück und wählen welche aus");
            $ErrorMissing = true;
        } else {
            foreach ($tblBasketPersonAllByBasket as $tblBasketPerson) {
                $tblPerson = $tblBasketPerson->getServicePeople_Person();
                if (!(new Data($this->getBinding()))->checkDebtorExistsByPerson($tblPerson)) {
                    $Stage .= new Danger("Für die Person ".$tblBasketPerson->getServicePeople_Person()->getFullName()
                        ." gibt es noch keinen relevanten Debitoren. Bitte legen Sie diesen zunächst an");
                    $ErrorMissing = true;
                }
            }
        }

        if ($ErrorMissing) {
            return $Stage;
        }

        if (!$Error) {
            //destroy TempTables
            (new Data($this->getBinding()))->destroyBasketCommodity($tblBasket);
            Invoice::useService()->destroyTempInvoice($tblBasket);

            if ((new Data($this->getBinding()))->checkDebtors($tblBasket, null)) {
                if (Invoice::useService()->createOrderListFromBasket($tblBasket, $Basket['Date'])) {
                    $Stage .= new Success('Die Rechnungen wurden erfolgreich erstellt')
                        .new Redirect('/Billing/Bookkeeping/Invoice/Order', Redirect::TIMEOUT_SUCCESS);
                } else {
                    $Stage .= new Success('Die Rechnungen konnten nicht erstellt werden')
                        .new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR);
                }
            } else {
                $Stage .= new Warning('Es konnten nicht alle Debitoren eindeutig zugeordnet werden')
                    .new Redirect('/Billing/Accounting/Basket/Debtor/Select', Redirect::TIMEOUT_ERROR, array(
                        'Id'   => $tblBasket->getId(),
                        'Date' => $Basket['Date'],
                    ));
            }
        }

        return $Stage;
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool|TblBasketItem[]
     */
    public function getBasketItemAllByBasket(TblBasket $tblBasket)
    {

        return (new Data($this->getBinding()))->getBasketItemAllByBasket($tblBasket);
    }

    /**
     * @param IFormInterface $Stage
     * @param                $Id
     * @param                $Date
     * @param                $Data
     * @param                $Save
     *
     * @return IFormInterface|string
     */
    public function checkDebtors(
        IFormInterface &$Stage = null,
        $Id,
        $Date,
        $Data,
        $Save
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Data && null === $Save
        ) {
            return $Stage;
        }

        $isSave = $Save == 2;
        $tblBasket = Basket::useService()->getBasketById($Id);

        if ((new Data($this->getBinding()))->checkDebtors($tblBasket, $Data, $isSave)) {
            if (Invoice::useService()->createOrderListFromBasket($tblBasket, $Date)) {
                $Stage .= new Success('Die Rechnungen wurden erfolgreich erstellt')
                    .new Redirect('/Billing/Bookkeeping/Invoice/Order', Redirect::TIMEOUT_SUCCESS);
            } else {
                $Stage .= new Success('Die Rechnungen konnten nicht erstellt werden')
                    .new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR);
            }
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return bool|TblBasket
     */
    public function getBasketById($Id)
    {

        return (new Data($this->getBinding()))->getBasketById($Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblBasketVerification
     */
    public function getBasketVerificationById($Id)
    {

        return (new Data($this->getBinding()))->getBasketVerificationById($Id);
    }

    /**
     * @param TblBasketItem $tblBasketItem
     * @param TblPerson     $tblPerson
     * @param               $tblPersonByBasketList
     * @param               $Result
     *
     * @return float
     */
    public function getPricePerPerson(TblBasketItem $tblBasketItem, TblPerson $tblPerson, $tblPersonByBasketList, $Result)
    {

        $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
        $tblCommodityItem = $tblBasketItem->getServiceBillingCommodityItem();
        if ($tblCommodityItem) {
            $tblCommodity = $tblCommodityItem->getTblCommodity();
            $tblItem = $tblCommodityItem->getTblItem();
            if ($tblCommodity && $tblItem) {
                if ($tblCommodity->getTblCommodityType()->getName() === 'Sammelleistung') {
                    $PersonCount = count($tblPersonByBasketList);
                    if ($tblItemRank = $tblItem->getServiceStudentChildRank()) {
                        if ($tblStudent) {
                            if ($tblStudentBilling = $tblStudent->getTblStudentBilling()) {
                                if ($SiblingRank = $tblStudentBilling->getServiceTblSiblingRank()) {
                                    if (
                                        $tblItemRank->getId() === $SiblingRank->getId()
                                    ) {
                                        $Result = ( ( $tblBasketItem->getPrice() * $tblBasketItem->getQuantity() ) / $PersonCount ) + $Result;
                                    }
                                } elseif ($SiblingRank === false && $tblItemRank->getName() === '1. Geschwisterkind') {
                                    $Result = ( ( $tblBasketItem->getPrice() * $tblBasketItem->getQuantity() ) / $PersonCount ) + $Result;
                                }
                            }
                        }
                    } else {
                        $Result = ( ( $tblBasketItem->getPrice() * $tblBasketItem->getQuantity() ) / $PersonCount ) + $Result;
                    }
                } else {
                    if ($tblItemRank = $tblItem->getServiceStudentChildRank()) {
                        if ($tblStudent) {
                            if ($tblStudentBilling = $tblStudent->getTblStudentBilling()) {
                                if ($SiblingRank = $tblStudentBilling->getServiceTblSiblingRank()) {
                                    if ($tblItemRank->getId() === $SiblingRank->getId()
                                    ) {
                                        $Result = ( $tblBasketItem->getPrice() * $tblBasketItem->getQuantity() ) + $Result;
                                    }
                                } elseif ($SiblingRank === false && $tblItemRank->getName() === '1. Geschwisterkind') {
                                    $Result = ( $tblBasketItem->getPrice() * $tblBasketItem->getQuantity() ) + $Result;
                                }
                            }
                        }
                    } else {
                        $Result = ( $tblBasketItem->getPrice() * $tblBasketItem->getQuantity() ) + $Result;
                    }
                }
            }
        }
        return $Result;
    }
}
