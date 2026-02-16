<?php

namespace SPHERE\Application\Education\ClassRegister\Digital\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblClassRegisterStudentListColumn")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentListColumn extends Element
{
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';

    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblPerson;
    /**
     * @Column(type="string")
     */
    protected string $Columns;
    /**
     * @Column(type="string")
     */
    protected string $FreeTexts;

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPerson(): bool|TblPerson
    {
        return Person::useService()->getPersonById($this->serviceTblPerson);
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson = null): void
    {
        $this->serviceTblPerson = $tblPerson?->getId();
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        return json_decode($this->Columns, true) ?: [];
    }

    /**
     * @param string $Columns
     *
     * @return void
     */
    public function setColumns(string $Columns): void
    {
        $this->Columns = $Columns;
    }

    /**
     * @return array
     */
    public function getFreeTexts(): array
    {
        return json_decode($this->FreeTexts, true) ?: [];
    }

    /**
     * @param string $FreeTexts
     *
     * @return void
     */
    public function setFreeTexts(string $FreeTexts): void
    {
        $this->FreeTexts = $FreeTexts;
    }
}