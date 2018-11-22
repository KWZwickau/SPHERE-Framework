<?php
namespace SPHERE\Application\Billing\Accounting\Causer\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblCauser")
 * @Cache(usage="READ_ONLY")
 */
class TblCauser extends Element
{

    const ATTR_OWNER = 'Owner';

    /**
     * @Column(type="string")
     */
    protected $Owner;

    /**
     * @return string $Owner
     */
    public function getOwner()
    {

        return $this->Owner;
    }

    /**
     * @param string $Owner
     */
    public function setOwner($Owner)
    {

        $this->Owner = $Owner;
    }
}
