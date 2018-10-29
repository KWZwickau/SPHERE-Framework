<?php

namespace SPHERE\Application\Reporting\Individual\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblPreset")
 * @Cache(usage="READ_ONLY")
 */
class TblPreset extends Element
{

    const ATTR_SERVICE_TBL_ACCOUNT = 'serviceTblAccount';
    const ATTR_NAME = 'Name';
    const ATTR_IS_PUBLIC = 'IsPublic';
    const ATTR_PERSON_CREATOR = 'PersonCreator';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblAccount;
    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="boolean")
     */
    protected $IsPublic;
    /**
     * @Column(type="string")
     */
    protected $PersonCreator;

    /**
     * @return bool|TblAccount
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
     * @param null|TblAccount $tblAccount
     */
    public function setServiceTblAccount(TblAccount $tblAccount = null)
    {

        $this->serviceTblAccount = (null === $tblAccount ? null : $tblAccount->getId());
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->Name;
    }

    /**
     * @param mixed $Name
     */
    public function setName($Name)
    {
        $this->Name = $Name;
    }

    /**
     * @return bool
     */
    public function getIsPublic()
    {
        return $this->IsPublic;
    }

    /**
     * @param bool $IsPublic
     */
    public function setIsPublic($IsPublic)
    {
        $this->IsPublic = $IsPublic;
    }

    /**
     * @return string
     */
    public function getPersonCreator()
    {
        return $this->PersonCreator;
    }

    /**
     * @param string $PersonCreator
     */
    public function setPersonCreator($PersonCreator)
    {
        $this->PersonCreator = $PersonCreator;
    }
}