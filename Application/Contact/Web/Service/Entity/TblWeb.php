<?php
namespace SPHERE\Application\Contact\Web\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblWeb")
 * @Cache(usage="READ_ONLY")
 */
class TblWeb extends Element
{

    const ATTR_ADDRESS = 'Address';

    /**
     * @Column(type="string")
     */
    protected $Address;

    /**
     * @return string
     */
    public function getAddress()
    {

        return $this->Address;
    }

    /**
     * @param string $Address
     */
    public function setAddress($Address)
    {

        $this->Address = $Address;
    }
}
