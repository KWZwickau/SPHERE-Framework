<?php

namespace SPHERE\Application\App\Authentication\Process\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Exception;
use SPHERE\Application\App\Authentication\Authentication;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblProcess")
 * @Cache(usage="READ_ONLY")
 */
class TblProcess extends Element
{
    public const SERVICE_TBL_IDENTIFICATION = 'serviceTblIdentification';
    public const ATTR_TBL_FACTOR = 'tblFactor';
    public const ATTR_SORT_ORDER = 'sortOrder';
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblIdentification;
    /**
     * @Column(type="bigint")
     */
    protected $tblFactor;
    /**
     * @Column(type="integer")
     */
    protected ?int $sortOrder;

    /**
     * @throws Exception
     */
    public function getTblFactor(): ?TblFactor
    {
        if (null === $this->tblFactor) {
            return null;
        }
        return Authentication::useService()->getFactorById($this->tblFactor);
    }

    public function setTblFactor(?TblFactor $tblFactor): void
    {
        $this->tblFactor = $tblFactor?->getId();
    }

    public function getServiceTblIdentification(): ?TblIdentification
    {
        if (null === $this->serviceTblIdentification) {
            return null;
        }
        $entity = Account::useService()->getIdentificationById($this->serviceTblIdentification);
        if (false === $entity) {
            return null;
        }
        return $entity;
    }

    public function setServiceTblIdentification(?TblIdentification $tblIdentification): void
    {
        $this->serviceTblIdentification = $tblIdentification?->getId();
    }

    /**
     * @return int|null
     */
    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    /**
     * @param int|null $sortOrder
     */
    public function setSortOrder(?int $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
    }
}
