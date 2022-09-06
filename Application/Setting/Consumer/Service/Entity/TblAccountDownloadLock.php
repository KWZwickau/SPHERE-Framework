<?php

namespace SPHERE\Application\Setting\Consumer\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use phpDocumentor\Reflection\Types\Boolean;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblAccountDownloadLock")
 * @Cache(usage="READ_ONLY")
 */
class TblAccountDownloadLock extends Element
{
    const ATTR_SERVICE_TBL_ACCOUNT = 'serviceTblAccount';
    const ATTR_IDENTIFIER = 'Identifier';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblAccount;

    /**
     * @Column(type="datetime")
     */
    protected $Date;

    /**
     * @Column(type="string")
     */
    protected $Identifier;

    /**
     * @Column(type="boolean")
     */
    protected $IsLocked;

    /**
     * @Column(type="boolean")
     */
    protected $IsLockedLastLoad;

    /**
     * @return TblAccount|false
     */
    public function getServiceTblAccount()
    {
        if (null === $this->serviceTblAccount) {
            return false;
        } else {
            return Account::useService()->getAccountById($this->serviceTblAccount);
        }
    }

    /**
     * @param TblAccount|null $tblAccount
     */
    public function setServiceTblAccount(TblAccount $tblAccount = null)
    {
        $this->serviceTblAccount = (null === $tblAccount ? null : $tblAccount->getId());
    }

    /**
     * @return string
     */
    public function getDate()
    {

        if (null === $this->Date) {
            return false;
        }
        /** @var \DateTime $Date */
        $Date = $this->Date;
        if ($Date instanceof \DateTime) {
            return $Date->format('d.m.Y');
        } else {
            return (string)$Date;
        }
    }

    /**
     * @return \DateTime|null
     */
    public function getDateTime()
    {
        return $this->Date;
    }

    /**
     * @param null|\DateTime $Date
     */
    public function setDate(\DateTime $Date = null)
    {

        $this->Date = $Date;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->Identifier;
    }

    /**
     * @param string $Identifier
     */
    public function setIdentifier($Identifier)
    {
        $this->Identifier = $Identifier;
    }

    /**
     * @return Boolean
     */
    public function getIsLocked()
    {
        return $this->IsLocked;
    }

    /**
     * @param Boolean $IsLocked
     */
    public function setIsLocked($IsLocked)
    {
        $this->IsLocked = $IsLocked;
    }

    /**
     * @return mixed
     */
    public function getIsLockedLastLoad()
    {
        return $this->IsLockedLastLoad;
    }

    /**
     * @param mixed $IsLockedLastLoad
     */
    public function setIsLockedLastLoad($IsLockedLastLoad)
    {
        $this->IsLockedLastLoad = $IsLockedLastLoad;
    }

    /**
     * prüft ob das Herunterladen noch gesperrt ist, nach 3 Minuten wird automatisch freigegeben
     *
     * @return bool
     */
    public function getIsFrontendLocked()
    {
        if ($this->getIsLocked()) {
            $now = new \DateTime();
            $datetime = $this->getDateTime();
            $datetime->modify('+3 minutes');

            if ($datetime > $now) {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }
}