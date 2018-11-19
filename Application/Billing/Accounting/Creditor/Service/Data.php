<?php
namespace SPHERE\Application\Billing\Accounting\Creditor\Service;

use SPHERE\Application\Billing\Accounting\Creditor\Service\Entity\TblCreditor;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 * @package SPHERE\Application\Billing\Accounting\Creditor\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param $Id
     *
     * @return false|TblCreditor
     */
    public function getCreditorById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCreditor', $Id);
    }

    /**
     * @return false|TblCreditor[]
     */
    public function getCreditorAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCreditor');
    }

    /**
     * @param string $Owner
     * @param string $Street
     * @param string $Number
     * @param string $Code
     * @param string $City
     * @param string $BankName
     * @param string $IBAN
     * @param string $BIC
     *
     * @param            $CreditorId
     *
     * @return null|object|TblCreditor
     */
    public function createCreditor($Owner = '',$Street = '',$Number = '',$Code = '',$City = '',$BankName = '',
        $IBAN = '',$BIC = '',$CreditorId = ''
    )
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCreditor')->findOneBy(array(
            TblCreditor::ATTR_OWNER => $Owner
        ));

        if ($Entity === null) {
            $Entity = new TblCreditor();
            $Entity->setOwner($Owner);
            $Entity->setStreet($Street);
            $Entity->setNumber($Number);
            $Entity->setCode($Code);
            $Entity->setCity($City);
            $Entity->setCreditorId($CreditorId);
            $Entity->setBankName($BankName);
            $Entity->setIBAN($IBAN);
            $Entity->setBIC($BIC);

            $Manager->saveEntity($Entity);

            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }
}
