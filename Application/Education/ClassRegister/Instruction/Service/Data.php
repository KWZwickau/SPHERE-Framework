<?php

namespace SPHERE\Application\Education\ClassRegister\Instruction\Service;

use SPHERE\Application\Education\ClassRegister\Instruction\Service\Entity\TblInstruction;
use SPHERE\Application\Education\ClassRegister\Instruction\Service\Entity\TblInstructionItem;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

class Data extends AbstractData
{
    public function setupDatabaseContent()
    {
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

        $Entity = new TblInstruction();
        $Entity->setSubject($Subject);
        $Entity->setContent($Content);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

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
    public function getInstructionAll()
    {
        return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblInstruction');
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
}