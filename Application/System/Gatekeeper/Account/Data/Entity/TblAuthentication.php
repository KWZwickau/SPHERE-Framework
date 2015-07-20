<?php
namespace SPHERE\Application\System\Gatekeeper\Account\Data\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="tblAuthentication")
 */
class TblAuthentication extends \SPHERE\System\Database\Fitting\Entity
{

    const ATTR_NAME = 'Name';

    /**
     * @Column(type="string")
     */
    protected $Name;

    /**
     * @return integer
     */
    public function getName()
    {

        return $this->Name;
    }

    /**
     * @param integer $Name
     */
    public function setName( $Name )
    {

        $this->Name = $Name;
    }
}
