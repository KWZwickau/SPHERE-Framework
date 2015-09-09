<?php

namespace SPHERE\Application\Billing\Accounting\Basket;

use SPHERE\Application\Billing\Accounting\Basket\Service\Data;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasketCommodity;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasketCommodityDebtor;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasketItem;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasketPerson;
use SPHERE\Application\Billing\Accounting\Basket\Service\Setup;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodity;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;

class Service implements IServiceInterface
{

    /** @var null|Binding */
    private $Binding = null;
    /** @var null|Structure */
    private $Structure = null;

    /**
     * Define Database Connection
     *
     * @param Identifier $Identifier
     * @param string     $EntityPath
     * @param string     $EntityNamespace
     */
    public function __construct(Identifier $Identifier, $EntityPath, $EntityNamespace)
    {

        $this->Binding = new Binding($Identifier, $EntityPath, $EntityNamespace);
        $this->Structure = new Structure($Identifier);
    }

    /**
     * @param bool $Simulate
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($Simulate, $withData)
    {

        $Protocol = (new Setup($this->Structure))->setupDatabaseSchema($Simulate);
        if (!$Simulate && $withData) {
            (new Data($this->Binding))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool|TblCommodity[]
     */
    public function entityCommodityAllByBasket(TblBasket $tblBasket)
    {

        return (new Data($this->Binding))->entityCommodityAllByBasket($tblBasket);
    }

    /**
     * @return bool|TblBasket[]
     */
    public function entityBasketAll()
    {

        return (new Data($this->Binding))->entityBasketAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblBasketItem
     */
    public function entityBasketItemById($Id)
    {

        return (new Data($this->Binding))->entityBasketItemById($Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblBasketPerson
     */
    public function entityBasketPersonById($Id)
    {

        return (new Data($this->Binding))->entityBasketPersonById($Id);
    }

    /**
     * @param TblBasketCommodity $tblBasketCommodity
     *
     * @return bool|TblBasketCommodityDebtor[]
     */
    public function entityBasketCommodityDebtorAllByBasketCommodity(TblBasketCommodity $tblBasketCommodity)
    {

        return (new Data($this->Binding))->entityBasketCommodityDebtorAllByBasketCommodity($tblBasketCommodity);
    }

    /**
     * @param $Id
     *
     * @return bool|TblBasketCommodity
     */
    public function entityBasketCommodityById($Id)
    {

        return (new Data($this->Binding))->entityBasketCommodityById($Id);
    }

    /**
     * @param TblBasket    $tblBasket
     * @param TblCommodity $tblCommodity
     *
     * @return bool|TblBasketItem[]
     */
    public function entityBasketItemAllByBasketAndCommodity(TblBasket $tblBasket, TblCommodity $tblCommodity)
    {

        return (new Data($this->Binding))->entityBasketItemAllByBasketAndCommodity($tblBasket, $tblCommodity);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return int
     */
    public function countPersonByBasket(TblBasket $tblBasket)
    {

        return (new Data($this->Binding))->countPersonByBasket($tblBasket);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool|TblBasketCommodity[]
     */
    public function entityBasketCommodityAllByBasket(TblBasket $tblBasket)
    {

        return (new Data($this->Binding))->entityBasketCommodityAllByBasket($tblBasket);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return array
     */
    public function entityPersonAllByBasket(TblBasket $tblBasket)
    {

        $tblBasketPersonList = $this->entityBasketPersonAllByBasket($tblBasket);
        $tblPerson = array();
        foreach ($tblBasketPersonList as $tblBasketPerson) {
            array_push($tblPerson, $tblBasketPerson->getServiceManagementPerson());
        }

        return $tblPerson;
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool|TblBasketPerson[]
     */
    public function entityBasketPersonAllByBasket(TblBasket $tblBasket)
    {

        return (new Data($this->Binding))->entityBasketPersonAllByBasket($tblBasket);
    }

    /**
     * @param IFormInterface $Stage
     * @param                $Basket
     *
     * @return IFormInterface|string
     */
    public function executeCreateBasket(IFormInterface &$Stage = null, $Basket)
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
            $tblBasket = (new Data($this->Binding))->actionCreateBasket(
                $Basket['Name']
            );
            return new Success('Der Warenkorb wurde erfolgreich erstellt')
            .new Redirect('/Billing/Accounting/Basket/Commodity/Select', 1, array('Id' => $tblBasket->getId()));
        }

        return $Stage;
    }

    /**
     * @param IFormInterface $Stage
     * @param TblBasket      $tblBasket
     * @param                $Basket
     *
     * @return IFormInterface|string
     */
    public function executeEditBasket(IFormInterface &$Stage = null, TblBasket $tblBasket, $Basket)
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
            if ((new Data($this->Binding))->actionEditBasket(
                $tblBasket,
                $Basket['Name']
            )
            ) {
                $Stage .= new Success('Änderungen gespeichert, die Daten werden neu geladen...')
                    .new Redirect('/Billing/Accounting/Basket', 1);
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
    public function executeDestroyBasket(TblBasket $tblBasket)
    {

        $tblBasket = (new Data($this->Binding))->actionDestroyBasket($tblBasket);
        if ($tblBasket) {
            return new Success('Der Warenkorb wurde erfolgreich gelöscht')
            .new Redirect('/Billing/Accounting/Basket', 1);
        } else {
            return new Warning('Der Warenkorb konnte nicht gelöscht werden')
            .new Redirect('/Billing/Accounting/Basket', 1);
        }
    }

    /**
     * @param TblBasket    $tblBasket
     * @param TblCommodity $tblCommodity
     *
     * @return string
     */
    public function executeAddBasketCommodity(TblBasket $tblBasket, TblCommodity $tblCommodity)
    {

        if ((new Data($this->Binding))->actionCreateBasketItemsByCommodity($tblBasket, $tblCommodity)) {
            return new Success('Die Leistung '.$tblCommodity->getName().' wurde erfolgreich hinzugefügt')
            .new Redirect('/Billing/Accounting/Basket/Commodity/Select', 0, array('Id' => $tblBasket->getId()));
        } else {
            return new Warning('Die Leistung '.$tblCommodity->getName().' konnte nicht hinzugefügt werden')
            .new Redirect('/Billing/Accounting/Basket/Commodity/Select', 2, array('Id' => $tblBasket->getId()));
        }
    }

    /**
     * @param TblBasket    $tblBasket
     * @param TblCommodity $tblCommodity
     *
     * @return string
     */
    public function executeRemoveBasketCommodity(TblBasket $tblBasket, TblCommodity $tblCommodity)
    {

        if ((new Data($this->Binding))->actionDestroyBasketItemsByCommodity($tblBasket, $tblCommodity)) {
            return new Success('Die Leistung '.$tblCommodity->getName().' wurde erfolgreich entfernt')
            .new Redirect('/Billing/Accounting/Basket/Commodity/Select', 0, array('Id' => $tblBasket->getId()));
        } else {
            return new Warning('Die Leistung '.$tblCommodity->getName().' konnte nicht entfernt werden')
            .new Redirect('/Billing/Accounting/Basket/Commodity/Select', 2, array('Id' => $tblBasket->getId()));
        }
    }

    /**
     * @param TblBasketItem $tblBasketItem
     *
     * @return string
     */
    public function executeRemoveBasketItem(TblBasketItem $tblBasketItem)
    {

        if ((new Data($this->Binding))->actionRemoveBasketItem($tblBasketItem)) {
            return new Success('Der Artikel '.$tblBasketItem->getServiceBillingCommodityItem()->getTblItem()->getName().' wurde erfolgreich entfernt')
            .new Redirect('/Billing/Accounting/Basket/Item', 0, array('Id' => $tblBasketItem->getTblBasket()->getId()));
        } else {
            return new Warning('Der Artikel '.$tblBasketItem->getServiceBillingCommodityItem()->getTblItem()->getName().' konnte nicht entfernt werden')
            .new Redirect('/Billing/Accounting/Basket/Item', 2, array('Id' => $tblBasketItem->getTblBasket()->getId()));
        }
    }

    /**
     * @param IFormInterface $Stage
     * @param TblBasketItem  $tblBasketItem
     * @param                $BasketItem
     *
     * @return IFormInterface|string
     */
    public function executeEditBasketItem(IFormInterface &$Stage = null, TblBasketItem $tblBasketItem, $BasketItem)
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
            if ((new Data($this->Binding))->actionEditBasketItem(
                $tblBasketItem,
                $BasketItem['Price'],
                $BasketItem['Quantity']
            )
            ) {
                $Stage .= new Success('Änderungen gespeichert, die Daten werden neu geladen...')
                    .new Redirect('/Billing/Accounting/Basket/Item', 1,
                        array('Id' => $tblBasketItem->getTblBasket()->getId()));
            } else {
                $Stage .= new Danger('Änderungen konnten nicht gespeichert werden')
                    .new Redirect('/Billing/Accounting/Basket/Item', 2,
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
    public function executeAddBasketPerson(TblBasket $tblBasket, TblPerson $tblPerson)
    {

        if ((new Data($this->Binding))->actionAddBasketPerson($tblBasket, $tblPerson)) {
            return new Success('Die Person '.$tblPerson->getFullName().' wurde erfolgreich hinzugefügt')
            .new Redirect('/Billing/Accounting/Basket/Person/Select', 0, array('Id' => $tblBasket->getId()));
        } else {
            return new Warning('Die Person '.$tblPerson->getFullName().' konnte nicht hinzugefügt werden')
            .new Redirect('/Billing/Accounting/Basket/Person/Select', 2, array('Id' => $tblBasket->getId()));
        }
    }

    /**
     * @param TblBasketPerson $tblBasketPerson
     *
     * @return string
     */
    public function executeRemoveBasketPerson(TblBasketPerson $tblBasketPerson)
    {

        if ((new Data($this->Binding))->actionRemoveBasketPerson($tblBasketPerson)) {
            return new Success('Die Person '.$tblBasketPerson->getServiceManagementPerson()->getFullName().' wurde erfolgreich entfernt')
            .new Redirect('/Billing/Accounting/Basket/Person/Select', 0,
                array('Id' => $tblBasketPerson->getTblBasket()->getId()));
        } else {
            return new Warning('Die Person '.$tblBasketPerson->getServiceManagementPerson()->getFullName().' konnte nicht entfernt werden')
            .new Redirect('/Billing/Accounting/Basket/Person/Select', 2,
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
    public function executeCheckBasket(IFormInterface &$Stage = null, TblBasket $tblBasket, $Basket)
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
        if (!$this->entityBasketItemAllByBasket($tblBasket)) {
            $Stage .= new Danger("Im Warenkorb befinden sich keine Artikel. Bitte gehen Sie zurück und wählen welche aus");
            $ErrorMissing = true;
        }

        $tblBasketPersonAllByBasket = $this->entityBasketPersonAllByBasket($tblBasket);
        if (!$tblBasketPersonAllByBasket) {
            $Stage .= new Danger("Im Warenkorb befinden sich keine Schüler. Bitte gehen Sie zurück und wählen welche aus");
            $ErrorMissing = true;
        } else {
            foreach ($tblBasketPersonAllByBasket as $tblBasketPerson) {
                if (!(new Data($this->Binding))->checkDebtorExistsByPerson($tblBasketPerson->getServiceManagementPerson())) {
                    $Stage .= new Danger("Für den Schüler ".$tblBasketPerson->getServiceManagementPerson()->getFullName()
                        ." gibt es noch keinen relevanten Debitoren. Bitte legen Sie diese zunächst einen an");
                    $ErrorMissing = true;
                }
            }
        }

        if ($ErrorMissing) {
            return $Stage;
        }

        if (!$Error) {
            //destroy TempTables
            (new Data($this->Binding))->actionDestroyBasketCommodity($tblBasket);
            Invoice::useService()->executeDestroyTempInvoice($tblBasket);

            if ((new Data($this->Binding))->checkDebtors($tblBasket, null)) {
                if (Invoice::useService()->executeCreateInvoiceListFromBasket($tblBasket, $Basket['Date'])) {
                    $Stage .= new Success('Die Rechnungen wurden erfolgreich erstellt')
                        .new Redirect('/Billing/Bookkeeping/Invoice/IsNotConfirmed', 2);
                } else {
                    $Stage .= new Success('Die Rechnungen konnten nicht erstellt werden')
                        .new Redirect('/Billing/Accounting/Basket', 2);
                }
            } else {
                $Stage .= new Warning('Es konnten nicht alle Debitoren eindeutig zugeordnet werden')
                    .new Redirect('/Billing/Accounting/Basket/Debtor/Select', 2, array(
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
    public function entityBasketItemAllByBasket(TblBasket $tblBasket)
    {

        return (new Data($this->Binding))->entityBasketItemAllByBasket($tblBasket);
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
    public function executeCheckDebtors(
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
        $tblBasket = Basket::useService()->entityBasketById($Id);

        if ((new Data($this->Binding))->checkDebtors($tblBasket, $Data, $isSave)) {
            if (Invoice::useService()->executeCreateInvoiceListFromBasket($tblBasket, $Date)) {
                $Stage .= new Success('Die Rechnungen wurden erfolgreich erstellt')
                    .new Redirect('/Billing/Bookkeeping/Invoice/IsNotConfirmed', 2);
            } else {
                $Stage .= new Success('Die Rechnungen konnten nicht erstellt werden')
                    .new Redirect('/Billing/Accounting/Basket', 2);
            }
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return bool|TblBasket
     */
    public function entityBasketById($Id)
    {

        return (new Data($this->Binding))->entityBasketById($Id);
    }
}
