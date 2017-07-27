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
 * @Table(name="tblWorkSpace")
 * @Cache(usage="READ_ONLY")
 */
class TblWorkSpace extends Element
{

    const ATTR_SERVICE_TBL_ACCOUNT = 'serviceTblAccount';
    const ATTR_FIELD = 'Field';
    const ATTR_VIEW = 'View';
    const ATTR_POSITION = 'Position';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblAccount;
    /**
     * @Column(type="string")
     */
    protected $Field;
    /**
     * @Column(type="string")
     */
    protected $View;
    /**
     * @Column(type="integer")
     */
    protected $Position;

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
     * @param null|TblAccount $serviceTblAccount
     */
    public function setServiceTblAccount(TblAccount $serviceTblAccount = null)
    {

        $this->serviceTblAccount = (null === $serviceTblAccount ? null : $serviceTblAccount->getId());
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->Field;
    }

    /**
     * @param string $Field
     */
    public function setField($Field)
    {
        $this->Field = $Field;
    }

    /**
     * @return string
     */
    public function getView()
    {
        return $this->View;
    }

    /**
     * @param string $View
     */
    public function setView($View)
    {
        $this->View = $View;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->Position;
    }

    /**
     * @param int $Position
     */
    public function setPosition($Position)
    {
        $this->Position = $Position;
    }
}