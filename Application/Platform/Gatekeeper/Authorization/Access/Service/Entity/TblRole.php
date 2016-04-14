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
 * @Table(name="tblRole")
 * @Cache(usage="READ_ONLY")
 */
class TblRole extends Element
{

    const ATTR_NAME = 'Name';

    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="boolean")
     */
    protected $IsInternal;
    /**
     * @Column(type="boolean")
     */
    protected $IsSecure;
    
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
     * @return bool|TblLevel[]
     */
    public function getTblLevelAll()
    {

        return Access::useService()->getLevelAllByRole($this);
    }

    /**
     * @return bool
     */
    public function isInternal()
    {

        return (bool)$this->IsInternal;
    }

    /**
     * @param bool $IsInternal
     */
    public function setInternal($IsInternal)
    {

        $this->IsInternal = (bool)$IsInternal;
    }

    /**
     * @return bool
     */
    public function isSecure()
    {

        return (bool)$this->IsSecure;
    }

    /**
     * @param bool $IsSecure
     */
    public function setSecure($IsSecure)
    {

        $this->IsSecure = (bool)$IsSecure;
    }
}
