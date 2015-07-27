<?php
namespace SPHERE\Application\System\Gatekeeper\Authorization\Account\Service\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\System\Gatekeeper\Authorization\Account\Account;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblAuthentication")
 */
class TblAuthentication extends Element
{

    const SERVICE_TBL_ACCOUNT = 'serviceTblAccount';
    const SERVICE_TBL_IDENTIFICATION = 'serviceTblIdentification';
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblIdentification;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblAccount;

    /**
     * @return bool|TblAccount
     */
    public function getServiceTblAccount()
    {

        if (null === $this->serviceTblAccount) {
            return false;
        } else {
            return Account::useService()->getAccountById( $this->serviceTblAccount );
        }
    }

    /**
     * @param null|TblAccount $tblAccount
     */
    public function setServiceTblAccount( TblAccount $tblAccount = null )
    {

        $this->serviceTblAccount = ( null === $tblAccount ? null : $tblAccount->getId() );
    }

    /**
     * @return bool|TblIdentification
     */
    public function getServiceTblIdentification()
    {

        if (null === $this->serviceTblIdentification) {
            return false;
        } else {
            return Account::useService()->getIdentificationById( $this->serviceTblIdentification );
        }
    }

    /**
     * @param null|TblIdentification $tblIdentification
     */
    public function setServiceTblIdentification( TblIdentification $tblIdentification = null )
    {

        $this->serviceTblIdentification = ( null === $tblIdentification ? null : $tblIdentification->getId() );
    }
}
