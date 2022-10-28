<?php
namespace SPHERE\Application\Contact\Address\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblRegion")
 * @Cache(usage="READ_ONLY")
 */
class TblRegion extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_CODE = 'Code';

    /**
     * @Column(type="string")
     */
    protected $Name;

    /**
     * @Column(type="string")
     */
    protected $Code;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->Name;
    }

    /**
     * @param string $Name
     */
    public function setName($Name): void
    {
        $this->Name = $Name;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->Code;
    }

    /**
     * @param string $Code
     */
    public function setCode(string $Code): void
    {
        $this->Code = $Code;
    }
}
