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

    const TYPE_SACHSEN = 'Sachsen';
    const TYPE_THUERINGEN = 'Thüringen';
    const TYPE_BERLIN = 'Berlin';

    /**
     * @Column(type="string")
     */
    protected string $Acronym;

    /**
     * @Column(type="string")
     */
    protected string $Name;

    /**
     * @Column(type="string")
     */
    protected string $Alias;

    /**
     * @Column(type="string")
     */
    protected string $Type;

    /**
     * @param string $Acronym
     */
    public function __construct(string $Acronym)
    {

        $this->Acronym = $Acronym;
    }

    /**
     * @return string
     */
    public function getAcronym(): string
    {

        return $this->Acronym;
    }

    /**
     * @param string $Acronym
     */
    public function setAcronym(string $Acronym)
    {

        $this->Acronym = $Acronym;
    }

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
    public function setName(string $Name)
    {

        $this->Name = $Name;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->Alias;
    }

    /**
     * @param string $Alias
     */
    public function setAlias(string $Alias)
    {
        $this->Alias = $Alias;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->Type;
    }

    /**
     * @param string $Type
     */
    public function setType($Type): void
    {
        $this->Type = $Type;
    }

    /**
     * @param string $Type
     * @param string $Acronym
     *
     * @return bool
     */
    public function isConsumer(string $Type, string $Acronym): bool
    {
        return $this->getType() == $Type && strtoupper($this->getAcronym()) == strtoupper($Acronym);
    }
}
