<?php

namespace SPHERE\Application\Billing\Accounting\Account\Service;

use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccount;
use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccountKey;
use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccountKeyType;
use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccountType;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Fitting\Binding;

class Data
{

    /** @var null|Binding $Connection */
    private $Connection = null;

    /**
     * @param Binding $Connection
     */
    function __construct(Binding $Connection)
    {

        $this->Connection = $Connection;
    }

    public function setupDatabaseContent()
    {

        $this->actionCreateKeyType('U', 'Umsatzsteuer');
        $this->actionCreateKey('01.01.2007', '19', '01.01.2030', 'Mehrwertsteuer', '3',
            $this->entityAccountKeyTypeById('1')
        );
        $this->actionCreateType('Erlöskonto', 'Dient zum erfassen des Erlöses');
        $this->actionCreateType('Umsatzsteuer', 'Dient zum erfassen der Umsatzsteuer');
    }

    /**
     * @param $Name
     * @param $Description
     *
     * @return TblAccountKeyType|null|object
     */
    public function actionCreateKeyType($Name, $Description)
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity('TblAccountKeyType')->findOneBy(array(
            'Name'        => $Name,
            'Description' => $Description
        ));
        if (null === $Entity) {
            $Entity = new TblAccountKeyType();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->Connection->getDatabase(),
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
    public function actionCreateKey(
        $ValidFrom,
        $Value,
        $ValidTo,
        $Description,
        $Code,
        TblAccountKeyType $tblAccountKeyType
    ) {

        $Manager = $this->Connection->getEntityManager();
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
            Protocol::useService()->createInsertEntry($this->Connection->getDatabase(),
                $Entity);
        }
        return $Entity;
    }

    /**
     * @param $Id
     *
     * @return bool|TblAccountKeyType
     */
    public function entityAccountKeyTypeById($Id)
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById('TblAccountKeyType', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Name
     * @param $Description
     *
     * @return TblAccountType|null|object
     */
    public function actionCreateType($Name, $Description)
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity('TblAccountType')->findOneBy(array(
            'Name'        => $Name,
            'Description' => $Description
        ));
        if (null === $Entity) {
            $Entity = new TblAccountType();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->Connection->getDatabase(),
                $Entity);
        }
        return $Entity;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblAccountType
     */
    public function entityAccountTypeById($Id)
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById('TblAccountType', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Id
     *
     * @return bool|TblAccountKey
     */
    public function entityAccountKeyById($Id)
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById('TblAccountKey', $Id);
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
    public function actionAddAccount($Number, $Description, $isActive, TblAccountKey $Key, TblAccountType $Type)
    {

        $Manager = $this->Connection->getEntityManager();

        $Entity = new TblAccount();

        $Entity->setDescription($Description);
        $Entity->setIsActive($isActive);
        $Entity->setNumber($Number);
        $Entity->setTblAccountKey($Key);
        $Entity->setTblAccountType($Type);

        $Manager->saveEntity($Entity);

        Protocol::useService()->createInsertEntry($this->Connection->getDatabase(),
            $Entity);

        return $Entity;
    }

    /**
     * @return bool|TblAccountKey
     */
    public function entityKeyValueAll()
    {

        $Entity = $this->Connection->getEntityManager()->getEntity('TblAccountKey')->findAll();
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblAccountType
     */
    public function entityTypeValueAll()
    {

        $Entity = $this->Connection->getEntityManager()->getEntity('TblAccountType')->findAll();
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Id
     *
     * @return bool|TblAccount
     */
    public function entityAccountById($Id)
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById('TblAccount', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param bool $IsActive
     *
     * @return bool|TblAccount[]
     */
    public function entityAccountAllByActiveState($IsActive = true)
    {

        $Entity = $this->Connection->getEntityManager()->getEntity('TblAccount')->findBy(array(
            TblAccount::ATTR_IS_ACTIVE => $IsActive
        ));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return array|bool|TblAccount[]
     */
    public function entityAccountAll()
    {

        $Entity = $this->Connection->getEntityManager()->getEntity('TblAccount')->findAll();
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
    public function actionEditAccount(
        TblAccount $tblAccount,
        $Description,
        $Number,
        $IsActive,
        TblAccountKey $tblAccountKey,
        TblAccountType $tblAccountType
    ) {

        $Manager = $this->Connection->getEntityManager();

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
            Protocol::useService()->createUpdateEntry($this->Connection->getDatabase(),
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
    public function actionActivateAccount($Id)
    {

        $Manager = $this->Connection->getEntityManager();

        /** @var TblAccount $Entity */
        $Entity = $Manager->getEntityById('TblAccount', $Id);
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setIsActive('1');

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->Connection->getDatabase(),
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
    public function actionDeactivateAccount($Id)
    {

        $Manager = $this->Connection->getEntityManager();

        /** @var TblAccount $Entity */
        $Entity = $Manager->getEntityById('TblAccount', $Id);
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setIsActive('0');

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->Connection->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @return array|bool|TblAccountKey[]
     */
    public function entityAccountKeyAll()
    {

        $Entity = $this->Connection->getEntityManager()->getEntity('TblAccountKey')->findAll();
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return array|bool|TblAccountType[]
     */
    public function entityAccountTypeAll()
    {

        $Entity = $this->Connection->getEntityManager()->getEntity('TblAccountType')->findAll();
        return ( null === $Entity ? false : $Entity );
    }
}
