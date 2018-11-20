<?php
namespace SPHERE\Application\Billing\Accounting\Creditor\Service;

use SPHERE\Application\Billing\Accounting\Creditor\Service\Entity\TblCreditor;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

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
     * @param string $District
     * @param string $CreditorId
     * @param string $BankName
     * @param string $IBAN
     * @param string $BIC
     *
     * @return null|object|TblCreditor
     */
    public function createCreditor($Owner = '', $Street = '', $Number = '', $Code = '', $City = '', $District = '', $CreditorId = ''
        , $BankName = '', $IBAN = '', $BIC = ''
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
            $Entity->setDistrict($District);
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

    /**
     * @param TblCreditor $tblCreditor
     * @param string $Owner
     * @param string $Street
     * @param string $Number
     * @param string $Code
     * @param string $City
     * @param string $District
     * @param string $CreditorId
     * @param string $BankName
     * @param string $IBAN
     * @param string $BIC
     *
     * @return bool
     */
    public function updateCreditor(TblCreditor $tblCreditor, $Owner = '', $Street = '', $Number = '', $Code = ''
        , $City = '', $District = '', $CreditorId = '', $BankName = '', $IBAN = '', $BIC = ''
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblCreditor $Entity */
        $Entity = $Manager->getEntityById('TblCreditor', $tblCreditor->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setOwner($Owner);
            $Entity->setStreet($Street);
            $Entity->setNumber($Number);
            $Entity->setCode($Code);
            $Entity->setCity($City);
            $Entity->setDistrict($District);
            $Entity->setCreditorId($CreditorId);
            $Entity->setBankName($BankName);
            $Entity->setIBAN($IBAN);
            $Entity->setBIC($BIC);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCreditor $tblCreditor
     *
     * @return bool
     */
    public function removeCreditor(TblCreditor $tblCreditor)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntityById('TblCreditor', $tblCreditor->getId());
        if (null !== $Entity) {
            /** @var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }
}
