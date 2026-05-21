<?php
namespace SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblClassRegisterTimetableReplacementPut")
 * @Cache(usage="READ_ONLY")
 */
class TblTimetableReplacementPut extends Element
{

    const ATTR_VALUE = 'Value';

    /** @Column(type="string") */
    protected string $Value;

    /**
     * @return string
     */
    public function getValue(): string
    {

        return $this->Value;
    }

    /**
     * @param string $Value
     * @return void
     */
    public function setValue(string $Value): void
    {
        $this->Value = $Value;
    }
}
