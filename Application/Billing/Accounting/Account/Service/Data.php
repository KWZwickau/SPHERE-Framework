<?php

namespace SPHERE\Application\Billing\Accounting\Account\Service;

use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccount;
use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccountKey;
use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccountKeyType;
use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccountType;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        $this->createKeyType('U', 'Umsatzsteuer');
        $this->createKey('01.01.2007', '19', '01.01.2030', 'Mehrwertsteuer', '3',
            $this->getAccountKeyTypeById('1')
        );
        $this->createType('Erlöskonto', 'Dient zum erfassen des Erlöses');
        $this->createType('Umsatzsteuer', 'Dient zum erfassen der Umsatzsteuer');
    }

    /**
     * @param $Name
     * @param $Description
     *
     * @return TblAccountKeyType|null|object
     */
    public function createKeyType($Name, $Description)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblAccountKeyType')->findOneBy(array(
            'Name'        => $Name,
            'Description' => $Description
        ));
        if (null === $Entity) {
            $Entity = new TblAccountKeyType();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }
        return $Entity;
    }

    /**
     * @param                   $ValidFrom
     * @param                   $Value
     * @param                   $ValidTo
     * @param                   $Description
     * @param                   $Code
     * @param TblAccountKeyType $tblAccountKeyType
     *
     * @return TblAccountKey|null|object
     */
    public function createKey(
        $ValidFrom,
        $Value,
        $ValidTo,
        $Description,
        $Code,
        TblAccountKeyType $tblAccountKeyType
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblAccountKey')->findOneBy(array(
            'ValidFrom'         => new \DateTime($ValidFrom),
            'Value'             => $Value,
            'ValidTo'           => new \DateTime($ValidTo),
            'Description'       => $Description,
            'Code'              => $Code,
            'tblAccountKeyType' => $tblAccountKeyType->getId()
        ));

        if (null === $Entity) {
            $Entity = new TblAccountKey();
            $Entity->setValidFrom(new \DateTime($ValidFrom));
            $Entity->setValue($Value);
            $Entity->setValidTo(new \DateTime($ValidTo));
            $Entity->setDescription($Description);
            $Entity->setCode($Code);
            $Entity->setTableAccountKeyType($tblAccountKeyType);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }
        return $Entity;
    }

    /**
     * @param $Id
     *
     * @return bool|TblAccountKeyType
     */
    public function getAccountKeyTypeById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblAccountKeyType', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Name
     * @param $Description
     *
     * @return TblAccountType|null|object
     */
    public function createType($Name, $Description)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblAccountType')->findOneBy(array(
            'Name'        => $Name,
            'Description' => $Description
        ));
        if (null === $Entity) {
            $Entity = new TblAccountType();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }
        return $Entity;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblAccountType
     */
    public function getAccountTypeById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblAccountType', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Id
     *
     * @return bool|TblAccountKey
     */
    public function getAccountKeyById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblAccountKey', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Number
     * @param $Description
     * @param $isActive
     * @param $Key
     * @param $Type
     *
     * @return TblAccount
     */
    public function createAccount($Number, $Description, $isActive, TblAccountKey $Key, TblAccountType $Type)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblAccount();

        $Entity->setDescription($Description);
        $Entity->setIsActive($isActive);
        $Entity->setNumber($Number);
        $Entity->setTblAccountKey($Key);
        $Entity->setTblAccountType($Type);

        $Manager->saveEntity($Entity);

        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
            $Entity);

        return $Entity;
    }

    /**
     * @return bool|TblAccountKey
     */
    public function getKeyValueAll()
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblAccountKey')->findAll();
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblAccountType
     */
    public function getTypeValueAll()
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblAccountType')->findAll();
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Id
     *
     * @return bool|TblAccount
     */
    public function getAccountById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblAccount', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param bool $IsActive
     *
     * @return bool|TblAccount[]
     */
    public function getAccountAllByActiveState($IsActive = true)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblAccount')->findBy(array(
            TblAccount::ATTR_IS_ACTIVE => $IsActive
        ));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return array|bool|TblAccount[]
     */
    public function getAccountAll()
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblAccount')->findAll();
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblAccount     $tblAccount
     * @param                $Description
     * @param                $Number
     * @param                $IsActive
     * @param TblAccountKey  $tblAccountKey
     * @param TblAccountType $tblAccountType
     *
     * @return bool
     */
    public function updateAccount(
        TblAccount $tblAccount,
        $Description,
        $Number,
        $IsActive,
        TblAccountKey $tblAccountKey,
        TblAccountType $tblAccountType
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblAccount $Entity */
        $Entity = $Manager->getEntityById('TblAccount', $tblAccount->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setDescription($Description);
            $Entity->setNumber($Number);
            $Entity->setIsActive($IsActive);
            $Entity->setTblAccountKey($tblAccountKey);
            $Entity->setTblAccountType($tblAccountType);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param $Id
     *
     * @return bool
     */
    public function updateActivateAccount($Id)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblAccount $Entity */
        $Entity = $Manager->getEntityById('TblAccount', $Id);
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setIsActive('1');

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param $Id
     *
     * @return bool
     */
    public function updateDeactivateAccount($Id)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblAccount $Entity */
        $Entity = $Manager->getEntityById('TblAccount', $Id);
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setIsActive('0');

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @return array|bool|TblAccountKey[]
     */
    public function getAccountKeyAll()
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblAccountKey')->findAll();
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return array|bool|TblAccountType[]
     */
    public function getAccountTypeAll()
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblAccountType')->findAll();
        return ( null === $Entity ? false : $Entity );
    }
}
