<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblConsumer")
 * @Cache(usage="READ_ONLY")
 */
class TblConsumer extends Element
{

    const ATTR_ACRONYM = 'Acronym';
    const ATTR_NAME = 'Name';

    /**
     * @Column(type="string")
     */
    protected $Acronym;

    /**
     * @Column(type="string")
     */
    protected $Name;

    /**
     * @Column(type="string")
     */
    protected $Alias;

    /**
     * @param string $Acronym
     */
    public function __construct($Acronym)
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
    public function setAcronym($Acronym)
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
    public function setName($Name)
    {

        $this->Name = $Name;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->Alias;
    }

    /**
     * @param string $Alias
     */
    public function setAlias($Alias)
    {
        $this->Alias = $Alias;
    }
}
