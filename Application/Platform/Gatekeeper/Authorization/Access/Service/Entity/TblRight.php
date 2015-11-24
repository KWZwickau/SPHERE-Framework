<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblRight")
 * @Cache(usage="READ_ONLY")
 */
class TblRight extends Element
{

    const ATTR_ROUTE = 'Route';

    /**
     * @Column(type="string")
     */
    protected $Route;

    /**
     * @param string $Route
     */
    public function __construct($Route)
    {

        $this->Route = $Route;
    }

    /**
     * @return string
     */
    public function getRoute()
    {

        return $this->Route;
    }

    /**
     * @param string $Route
     */
    public function setRoute($Route)
    {

        $this->Route = $Route;
    }
}
