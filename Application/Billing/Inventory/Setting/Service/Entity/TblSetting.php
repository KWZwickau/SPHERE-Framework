<?php
namespace SPHERE\Application\Billing\Inventory\Setting\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblSetting")
 * @Cache(usage="READ_ONLY")
 */
class TblSetting extends Element
{

    const ATTR_IDENTIFIER = 'Identifier';
    const ATTR_VALUE = 'Value';

    const IDENT_DEBTOR_NUMBER_COUNT = 'DebtorNumberCount';
    const IDENT_PERSON_GROUP_ACTIVE_LIST = 'PersonGroupActiveList';
    const IDENT_IS_DEBTOR_NUMBER_NEED = 'IsDebtorNumberNeed';
    const IDENT_IS_SEPA_ACCOUNT_NEED = 'IsSepaAccountNeed';

    const TYPE_BOOLEAN = 'boolean';
    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'integer';

    /**
     * @Column(type="string")
     */
    protected $Identifier;
    /**
     * @Column(type="string")
     */
    protected $Value;
    /**
     * @Column(type="string")
     */
    protected $Type;

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->Identifier;
    }

    /**
     * @param string $Identifier
     */
    public function setIdentifier($Identifier)
    {
        $this->Identifier = $Identifier;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->Value;
    }

    /**
     * @param string $Value
     */
    public function setValue($Value)
    {
        $this->Value = $Value;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->Type;
    }

    /**
     * @param string $Type
     */
    public function setType($Type)
    {
        $this->Type = $Type;
    }
}