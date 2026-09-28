<?php
namespace SPHERE\Application\App\Authentication\Process\Service\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblLoginToken")
 */
class TblLoginToken extends Element
{
    public const SERVICE_TBL_ACCOUNT = 'serviceTblAccount';
    public const ATTR_CREDENTIAL_JWT = 'credentialJwt';
//    public const ATTR_TIMEOUT = 'Timeout';


    /**
     * @Column(type="string", nullable=false)
     */
    protected string $serviceTblAccount;
    /**
     * @Column(type="string", nullable=false)
     */
    protected string $credentialJwt;
//    protected string $timeout;

    public function getServiceTblAccount(): ?TblAccount
    {

        if (null === $this->serviceTblAccount) {
            return null;
        }

        return Account::useService()->getAccountById($this->serviceTblAccount);
    }

    /**
     * @param null|TblAccount $tblAccount
     */
    public function setServiceTblAccount(TblAccount $tblAccount = null): void
    {

        $this->serviceTblAccount = (null === $tblAccount ? null : $tblAccount->getId());
    }

    public function getCredentialJwt(): string
    {
        return $this->credentialJwt;
    }

    public function setCredentialJwt(string $credentialJwt): void
    {
        $this->credentialJwt = $credentialJwt;
    }

//    public function getTimeout(): ?int
//    {
//        return $this->timeout;
//    }
//
//    public function setTimeout(?int $timeout): void
//    {
//        $this->timeout = $timeout;
//    }
}
