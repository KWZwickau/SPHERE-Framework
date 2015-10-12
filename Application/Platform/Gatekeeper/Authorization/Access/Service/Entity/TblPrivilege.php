<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblPrivilege")
 * @Cache(usage="READ_ONLY")
 */
class TblPrivilege extends Element
{

    const ATTR_NAME = 'Name';

    /**
     * @Column(type="string")
     */
    protected $Name;

    /**
     * @param string $Name
     */
    public function __construct($Name)
    {

        $this->Name = $Name;
    }

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

    /**
     * @return bool|TblRight[]
     */
    public function getTblRightAll()
    {

        return Access::useService()->getRightAllByPrivilege($this);
    }
}
