<?php
namespace SPHERE\Application\Billing\Accounting\Debtor\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblDebtorPeriodType")
 * @Cache(usage="READ_ONLY")
 */
class TblDebtorPeriodType extends Element
{

    const ATTR_NAME = 'Name';

    const ATTR_MONTH = 'Monatlich';
    const ATTR_YEAR = 'Jährlich';

    /**
     * @Column(type="string")
     */
    protected $Name;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->Name;
    }

    /**
     * @param string $Name
     */
    public function setName($Name)
    {
        $this->Name = $Name;
    }
}
