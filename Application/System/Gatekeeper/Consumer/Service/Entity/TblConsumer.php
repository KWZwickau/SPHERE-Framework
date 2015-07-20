<?php
namespace SPHERE\Application\System\Gatekeeper\Consumer\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="tblConsumer")
 * @Cache(usage="READ_ONLY")
 */
class TblConsumer extends \SPHERE\System\Database\Fitting\Entity
{

    const ATTR_NAME = 'Name';
    const ATTR_ACRONYM = 'Acronym';

    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="string")
     */
    protected $Acronym;

    /**
     * @param string $Acronym
     */
    function __construct( $Acronym )
    {

        $this->Acronym = $Acronym;
    }

    /**
     * @return string
     */
    public function getAcronym()
    {

        return $this->Acronym;
    }

    /**
     * @param string $Acronym
     */
    public function setAcronym( $Acronym )
    {

        $this->Acronym = $Acronym;
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
    public function setName( $Name )
    {

        $this->Name = $Name;
    }
}
