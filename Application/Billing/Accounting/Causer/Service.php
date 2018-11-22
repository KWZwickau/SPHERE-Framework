<?php
namespace SPHERE\Application\Billing\Accounting\Causer;

use SPHERE\Application\Billing\Accounting\Causer\Service\Data;
use SPHERE\Application\Billing\Accounting\Causer\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\RedirectScript;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Billing\Accounting\Causer
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

//    /**
//     * @param $Id
//     *
//     * @return false|TblCreditor
//     */
//    public function getCreditorById($Id)
//    {
//
//        return (new Data($this->getBinding()))->getCreditorById($Id);
//    }
//
//    /**
//     * @return false|TblCreditor[]
//     */
//    public function getCreditorAll()
//    {
//
//        return (new Data($this->getBinding()))->getCreditorAll();
//    }

    /**
     * @param IFormInterface $Form
     * @param string|string  $GroupId
     *
     * @return IFormInterface|string
     */
    public function directRoute(IFormInterface &$Form, $GroupId = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $GroupId) {
            return $Form;
        }
        if('0' === $GroupId){
            $Form->setError('GroupId', 'Bitte wählen Sie eine Gruppe aus');
            return $Form;
        }

        return 'Lädt...'
            .(new ProgressBar(0, 100, 0, 12))->setColor(ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_SUCCESS)
            .new RedirectScript('/Billing/Accounting/Causer/View', Redirect::TIMEOUT_SUCCESS, array('GroupId' => $GroupId));
    }

//    /**
//     * @param string $Owner
//     * @param string $Street
//     * @param string $Number
//     * @param string $Code
//     * @param string $City
//     * @param string $District
//     * @param string $CreditorId
//     * @param string $BankName
//     * @param string $IBAN
//     * @param string $BIC
//     *
//     * @return null|object|TblCreditor
//     */
//    public function createCreditor($Owner = '',$Street = '', $Number = '', $Code = '', $City = '', $District = ''
//        , $CreditorId = '', $BankName = '', $IBAN = '', $BIC = '')
//    {
//        return (new Data($this->getBinding()))->createCreditor($Owner, $Street, $Number, $Code, $City, $District, $CreditorId
//            , $BankName, $IBAN, $BIC);
//    }
//
//    /**
//     * @param TblCreditor $tblCreditor
//     * @param string $Owner
//     * @param string $Street
//     * @param string $Number
//     * @param string $Code
//     * @param string $City
//     * @param string $District
//     * @param string $CreditorId
//     * @param string $BankName
//     * @param string $IBAN
//     * @param string $BIC
//     *
//     * @return bool
//     */
//    public function changeCreditor(TblCreditor $tblCreditor, $Owner = '',$Street = '', $Number = '', $Code = '', $City = '', $District = ''
//        , $CreditorId = '', $BankName = '', $IBAN = '', $BIC = '')
//    {
//        return (new Data($this->getBinding()))->updateCreditor($tblCreditor, $Owner, $Street, $Number, $Code, $City, $District, $CreditorId
//            , $BankName, $IBAN, $BIC);
//    }
//
//    /**
//     * @param TblCreditor $tblCreditor
//     *
//     * @return bool
//     */
//    public function removeCreditor(TblCreditor $tblCreditor)
//    {
//        return (new Data($this->getBinding()))->removeCreditor($tblCreditor);
//    }
}
