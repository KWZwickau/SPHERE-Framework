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
    const ATTR_TYPE = 'Type';
    const ATTR_CATEGORY = 'Category';

    // Regular Options
    const IDENT_DEBTOR_NUMBER_COUNT = 'DebtorNumberCount';
    const IDENT_PERSON_GROUP_ACTIVE_LIST = 'PersonGroupActiveList';
    const IDENT_IS_DEBTOR_NUMBER_NEED = 'IsDebtorNumberNeed';
    const IDENT_IS_AUTO_DEBTOR_NUMBER = 'IsAutoDebtorNumber';
    const IDENT_IS_AUTO_REFERENCE_NUMBER = 'IsAutoReferenceNumber';

    // Sepa Option's
    const IDENT_IS_SEPA = 'IsSepa';
    const IDENT_ADVISER = 'Adviser';
    // ToDO Mandant über Schularten ziehen
    const IDENT_SEPA_ACCOUNT_NUMBER_LENGTH = 'SepaAccountNumberLength';
    const IDENT_IS_WORKER_ACRONYM = 'IsWorkerAcronym';

    const TYPE_BOOLEAN = 'boolean';
    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'integer';

    const CATEGORY_REGULAR = 'Allgemein';
    const CATEGORY_SEPA = 'SEPA';

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
     * @Column(type="string")
     */
    protected $Category;

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

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->Category;
    }

    /**
     * @param string $Category
     */
    public function setCategory($Category)
    {
        $this->Category = $Category;
    }
}