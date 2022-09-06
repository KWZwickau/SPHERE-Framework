<?php

namespace SPHERE\Application\Education\ClassRegister\Instruction\Service;

use DateTime;
use SPHERE\Application\Education\ClassRegister\Instruction\Service\Entity\TblInstruction;
use SPHERE\Application\Education\ClassRegister\Instruction\Service\Entity\TblInstructionItem;
use SPHERE\Application\Education\ClassRegister\Instruction\Service\Entity\TblInstructionItemStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

class Data extends AbstractData
{
    public function setupDatabaseContent()
    {
        if (!$this->getInstructionAll(false)) {
            $this->createInstruction('Einhaltung der Hausordnung', '');
            $this->createInstruction('Verhalten bei Schadensereignissen und Bedrohungslagen', '');
            $this->createInstruction('Verhalten bei Katastrophenalarm', '');
            $this->createInstruction('Verhalten in den Fachräumen: Physik', '');
            $this->createInstruction('Verhalten in den Fachräumen: Biologie', '');
            $this->createInstruction('Verhalten in den Fachräumen: Chemie', '');
            $this->createInstruction('Verhalten im Sportunterricht', '');
            $this->createInstruction('Verhinderung und Bekämpfung von Bränden', '');
            $this->createInstruction('Verhalten bei Gefahren im Winter', '');
            $this->createInstruction('Verhalten im Straßenverkehr', '');
        }
    }

    /**
     * @param $Subject
     * @param $Content
     *
     * @return TblInstruction
     */
    public function createInstruction(
        $Subject,
        $Content
    ): TblInstruction {
        $Manager = $this->getEntityManager();
        $Entity = $Manager->getEntity('TblInstruction')->findOneBy(array(
            TblInstruction::ATTR_SUBJECT => $Subject
        ));
        if (null === $Entity) {
            $Entity = new TblInstruction();
            $Entity->setSubject($Subject);
            $Entity->setContent($Content);
            $Entity->setIsActive(true);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblInstruction $tblInstruction
     * @param $Subject
     * @param $Content
     *
     * @return bool
     */
    public function updateInstruction(
        TblInstruction $tblInstruction,
        $Subject,
        $Content
    ): bool {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblInstruction $Entity */
        $Entity = $Manager->getEntityById('TblInstruction', $tblInstruction->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setSubject($Subject);
            $Entity->setContent($Content);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblInstruction $tblInstruction
     * @param bool $isActive
     *
     * @return bool
     */
    public function activateInstruction(
        TblInstruction $tblInstruction,
        bool $isActive
    ): bool {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblInstruction $Entity */
        $Entity = $Manager->getEntityById('TblInstruction', $tblInstruction->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setIsActive($isActive);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblInstruction $tblInstruction
     *
     * @return bool
     */
    public function destroyInstruction(TblInstruction $tblInstruction): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblInstruction $Entity */
        $Entity = $Manager->getEntityById('TblInstruction', $tblInstruction->getId());
        if (null !== $Entity) {
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param $Id
     *
     * @return false|TblInstruction
     */
    public function getInstructionById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblInstruction', $Id);
    }

    /**
     * @return false|TblInstruction[]
     */
    public function getInstructionAll(bool $isActive)
    {
        if ($isActive) {
            return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblInstruction', array(TblInstruction::ATTR_IS_ACTIVE => $isActive));
        } else {
            return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblInstruction');
        }
    }

    /**
     * @param $Id
     *
     * @return false|TblInstructionItem
     */
    public function getInstructionItemById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblInstructionItem', $Id);
    }

    /**
     * @param TblInstruction $tblInstruction
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     * @param TblDivisionSubject|null $tblDivisionSubject
     * @param TblYear|null $tblYear
     *
     * @return false|TblInstructionItem[]
     */
    public function getInstructionItemAllByInstruction(TblInstruction $tblInstruction, ?TblDivision $tblDivision, ?TblGroup $tblGroup,
        ?TblDivisionSubject $tblDivisionSubject, ?TblYear $tblYear)
    {
        if ($tblDivisionSubject) {
            return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblInstructionItem', array(
                TblInstructionItem::ATTR_TBL_INSTRUCTION => $tblInstruction->getId(),
                TblInstructionItem::ATTR_SERVICE_TBL_DIVISION_SUBJECT => $tblDivisionSubject->getId()
            ), array(TblInstructionItem::ATTR_DATE => self::ORDER_ASC));
        } elseif ($tblDivision) {
            return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblInstructionItem', array(
                TblInstructionItem::ATTR_TBL_INSTRUCTION => $tblInstruction->getId(),
                TblInstructionItem::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId()
            ), array(TblInstructionItem::ATTR_DATE => self::ORDER_ASC));
        } elseif ($tblGroup && $tblYear) {
            return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblInstructionItem', array(
                TblInstructionItem::ATTR_TBL_INSTRUCTION => $tblInstruction->getId(),
                TblInstructionItem::ATTR_SERVICE_TBL_GROUP => $tblGroup->getId(),
                TblInstructionItem::ATTR_SERVICE_TBL_YEAR => $tblYear->getId()
            ), array(TblInstructionItem::ATTR_DATE => self::ORDER_ASC));
        } else {
            return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblInstructionItem', array(
                TblInstructionItem::ATTR_TBL_INSTRUCTION => $tblInstruction->getId(),
            ), array(TblInstructionItem::ATTR_DATE => self::ORDER_ASC));
        }
    }

    /**
     * @param TblInstruction $tblInstruction
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     * @param TblDivisionSubject|null $tblDivisionSubject
     * @param TblYear|null $tblYear
     *
     * @return false|TblInstructionItem
     */
    public function getMainInstructionItemBy(TblInstruction $tblInstruction, ?TblDivision $tblDivision, ?TblGroup $tblGroup,
        ?TblDivisionSubject $tblDivisionSubject, ?TblYear $tblYear)
    {
        if ($tblDivisionSubject) {
            return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblInstructionItem', array(
                TblInstructionItem::ATTR_TBL_INSTRUCTION => $tblInstruction->getId(),
                TblInstructionItem::ATTR_SERVICE_TBL_DIVISION_SUBJECT => $tblDivisionSubject->getId(),
                TblInstructionItem::ATTR_IS_MAIN => 1
            ));
        } elseif ($tblDivision) {
            return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblInstructionItem', array(
                TblInstructionItem::ATTR_TBL_INSTRUCTION => $tblInstruction->getId(),
                TblInstructionItem::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                TblInstructionItem::ATTR_IS_MAIN => 1
            ));
        } elseif ($tblGroup && $tblYear) {
            return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblInstructionItem', array(
                TblInstructionItem::ATTR_TBL_INSTRUCTION => $tblInstruction->getId(),
                TblInstructionItem::ATTR_SERVICE_TBL_GROUP => $tblGroup->getId(),
                TblInstructionItem::ATTR_SERVICE_TBL_YEAR => $tblYear->getId(),
                TblInstructionItem::ATTR_IS_MAIN => 1
            ));
        }

        return false;
    }

    /**
     * @param TblInstruction $tblInstruction
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     * @param TblDivisionSubject|null $tblDivisionSubject
     * @param TblYear|null $tblYear
     * @param TblPerson|null $tblPerson
     * @param $Date
     * @param $Subject
     * @param $Content
     * @param $IsMain
     *
     * @return TblInstructionItem
     */
    public function createInstructionItem(
        TblInstruction $tblInstruction,
        ?TblDivision $tblDivision,
        ?TblGroup $tblGroup,
        ?TblDivisionSubject $tblDivisionSubject,
        ?TblYear $tblYear,
        ?TblPerson $tblPerson,
        $Date,
        $Subject,
        $Content,
        $IsMain
    ): TblInstructionItem {

        $Manager = $this->getEntityManager();
        $Entity = new TblInstructionItem();
        $Entity->setTblInstruction($tblInstruction);
        $Entity->setServiceTblDivision($tblDivision);
        $Entity->setServiceTblGroup($tblGroup);
        $Entity->setServiceTblDivisionSubject($tblDivisionSubject);
        $Entity->setServiceTblYear($tblYear);
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setDate($Date ? new DateTime($Date) : null);
        $Entity->setSubject($Subject);
        $Entity->setContent($Content);
        $Entity->setIsMain($IsMain);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblInstructionItem $tblInstructionItem
     * @param TblPerson|null $tblPerson
     * @param $Date
     * @param $Content
     *
     * @return bool
     */
    public function updateInstructionItem(
        TblInstructionItem $tblInstructionItem,
        ?TblPerson $tblPerson,
        $Date,
        $Content
    ): bool {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblInstructionItem $Entity */
        $Entity = $Manager->getEntityById('TblInstructionItem', $tblInstructionItem->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setDate($Date ? new DateTime($Date) : null);
            $Entity->setContent($Content);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblInstructionItem $tblInstructionItem
     *
     * @return bool
     */
    public function destroyInstructionItem(TblInstructionItem $tblInstructionItem): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblInstructionItem $Entity */
        $Entity = $Manager->getEntityById('TblInstructionItem', $tblInstructionItem->getId());
        if (null !== $Entity) {
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblInstructionItem $tblInstructionItem
     * @param TblPerson $tblPerson
     *
     * @return TblInstructionItemStudent
     */
    public function addInstructionItemStudent(TblInstructionItem $tblInstructionItem, TblPerson $tblPerson): TblInstructionItemStudent
    {
        $Manager = $this->getEntityManager();
        $Entity = $Manager->getEntity('TblInstructionItemStudent')
            ->findOneBy(array(
                TblInstructionItemStudent::ATTR_TBL_InstructionItem => $tblInstructionItem->getId(),
                TblInstructionItemStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            ));
        if (null === $Entity) {
            $Entity = new TblInstructionItemStudent();
            $Entity->setTblInstructionItem($tblInstructionItem);
            $Entity->setServiceTblPerson($tblPerson);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblInstructionItemStudent $tblInstructionItemStudent
     *
     * @return bool
     */
    public function removeInstructionItemStudent(TblInstructionItemStudent $tblInstructionItemStudent): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblInstructionItemStudent $Entity */
        $Entity = $Manager->getEntityById('TblInstructionItemStudent', $tblInstructionItemStudent->getId());
        if (null !== $Entity) {
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblInstructionItem $tblInstructionItem
     *
     * @return false|TblInstructionItemStudent[]
     */
    public function getMissingStudentsByInstructionItem(TblInstructionItem $tblInstructionItem)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblInstructionItemStudent', array(
            TblInstructionItemStudent::ATTR_TBL_InstructionItem => $tblInstructionItem->getId()
        ));
    }
}