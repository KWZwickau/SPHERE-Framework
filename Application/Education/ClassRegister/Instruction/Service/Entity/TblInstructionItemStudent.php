<?php

namespace SPHERE\Application\Education\ClassRegister\Instruction\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\ClassRegister\Instruction\Instruction;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblClassRegisterInstructionItemStudent")
 * @Cache(usage="READ_ONLY")
 */
class TblInstructionItemStudent extends Element
{
    const ATTR_TBL_InstructionItem = 'tblInstructionItem';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';

    /**
     * @Column(type="bigint")
     */
    protected $tblInstructionItem;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;

    /**
     * @return bool|TblInstructionItem
     */
    public function getTblInstructionItem()
    {

        if (null === $this->tblInstructionItem) {
            return false;
        } else {
            return Instruction::useService()->getInstructionItemById($this->tblInstructionItem);
        }
    }

    /**
     * @param TblInstructionItem|null $tblInstructionItem
     */
    public function setTblInstructionItem(TblInstructionItem $tblInstructionItem = null)
    {

        $this->tblInstructionItem = (null === $tblInstructionItem ? null : $tblInstructionItem->getId());
    }

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPerson()
    {
        if (null === $this->serviceTblPerson) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPerson);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson = null)
    {
        $this->serviceTblPerson = (null === $tblPerson ? null : $tblPerson->getId());
    }
}