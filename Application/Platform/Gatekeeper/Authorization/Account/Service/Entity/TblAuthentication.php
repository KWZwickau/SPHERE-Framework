<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblAuthentication")
 * @Cache(usage="READ_ONLY")
 */
class TblAuthentication extends Element
{

    const ATTR_TBL_ACCOUNT = 'tblAccount';
    const ATTR_TBL_IDENTIFICATION = 'tblIdentification';

    /**
     * @Column(type="bigint")
     */
    protected $tblAccount;
    /**
     * @Column(type="bigint")
     */
    protected $tblIdentification;

    /**
     * @return bool|TblAccount
     */
    public function getTblAccount()
    {

        if (null === $this->tblAccount) {
            return false;
        } else {
            return Account::useService()->getAccountById($this->tblAccount);
        }
    }

    /**
     * @param null|TblAccount $tblAccount
     */
    public function setTblAccount(TblAccount $tblAccount = null)
    {

        $this->tblAccount = ( null === $tblAccount ? null : $tblAccount->getId() );
    }

    /**
     * @return bool|TblIdentification
     */
    public function getTblIdentification()
    {

        if (null === $this->tblIdentification) {
            return false;
        } else {
            return Account::useService()->getIdentificationById($this->tblIdentification);
        }
    }

    /**
     * @param null|TblIdentification $tblIdentification
     */
    public function setTblIdentification(TblIdentification $tblIdentification = null)
    {

        $this->tblIdentification = ( null === $tblIdentification ? null : $tblIdentification->getId() );
    }
}
