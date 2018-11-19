<?php
namespace SPHERE\Application\Billing\Accounting\Creditor;

use SPHERE\Application\Billing\Accounting\Creditor\Service\Data;
use SPHERE\Application\Billing\Accounting\Creditor\Service\Entity\TblCreditor;
use SPHERE\Application\Billing\Accounting\Creditor\Service\Setup;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Billing\Accounting\SchoolAccount
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
     * @param $Id
     *
     * @return false|TblCreditor
     */
    public function getCreditorById($Id)
    {

        return (new Data($this->getBinding()))->getCreditorById($Id);
    }

    /**
     * @return false|TblCreditor[]
     */
    public function getCreditorAll()
    {

        return (new Data($this->getBinding()))->getCreditorAll();
    }

}
