<?php

namespace SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblBasketType")
 * @Cache(usage="READ_ONLY")
 */
class TblBasketType extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_DESCRIPTION = 'Description';

    const IDENT_ABRECHNUNG = 'Abrechnung';
    const IDENT_AUSZAHLUNG = 'Auszahlung';
    const IDENT_GUTSCHRIFT = 'Gutschrift';

    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="text")
     */
    protected $Description;

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
    public function getDescription()
    {

        return $this->Description;
    }

    /**
     * @param string $Description
     */
    public function setDescription($Description)
    {

        $this->Description = $Description;
    }

}
