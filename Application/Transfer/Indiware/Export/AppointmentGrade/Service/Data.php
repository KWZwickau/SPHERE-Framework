<?php

namespace SPHERE\Application\Transfer\Indiware\Export\AppointmentGrade\Service;

use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTask;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Transfer\Indiware\Export\AppointmentGrade\Service\Entity\TblIndiwareStudentSubjectOrder;
use SPHERE\System\Database\Binding\AbstractData;

class Data extends AbstractData
{
    public function setupDatabaseContent()
    {

    }

    /**
     * @param $Id
     *
     * @return false|TblIndiwareStudentSubjectOrder
     */
    public function getIndiwareStudentSubjectOrderById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblIndiwareStudentSubjectOrder', $Id);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblIndiwareStudentSubjectOrder
     */
    public function getIndiwareStudentSubjectOrderByPerson(TblPerson $tblPerson)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblIndiwareStudentSubjectOrder',
            array(
                TblIndiwareStudentSubjectOrder::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
    }

    /**
     * @return false|TblIndiwareStudentSubjectOrder[]
     */
    public function getIndiwareStudentSubjectOrderAll()
    {
        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblIndiwareStudentSubjectOrder');
    }

    /**
     * @param array   $ImportList
     * @param int     $Period
     * @param TblTask $tblTask
     *
     * @return bool
     */
    public function createIndiwareStudentSubjectOrderBulk(array $ImportList,int $Period, TblTask $tblTask): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        if (!empty($ImportList)) {
            foreach ($ImportList as $Row) {
                $Entity = new TblIndiwareStudentSubjectOrder();
                $Entity->setServiceTblTask($tblTask);
                $Entity->setPeriod($Period);
                $Entity->setFirstName($Row['FirstName']);
                $Entity->setLastName($Row['LastName']);
                $Entity->setBirthday($Row['Birthday']);
                if (isset($Row['serviceTblPerson']) && $Row['serviceTblPerson']) {
                    $Entity->setServiceTblPerson($Row['serviceTblPerson']);
                } else {
                    $Entity->setServiceTblPerson(null);
                }
                $Entity->setSubject1($Row['FileSubject1']);
                $Entity->setSubject2($Row['FileSubject2']);
                $Entity->setSubject3($Row['FileSubject3']);
                $Entity->setSubject4($Row['FileSubject4']);
                $Entity->setSubject5($Row['FileSubject5']);
                $Entity->setSubject6($Row['FileSubject6']);
                $Entity->setSubject7($Row['FileSubject7']);
                $Entity->setSubject8($Row['FileSubject8']);
                $Entity->setSubject9($Row['FileSubject9']);
                $Entity->setSubject10($Row['FileSubject10']);
                $Entity->setSubject11($Row['FileSubject11']);
                $Entity->setSubject12($Row['FileSubject12']);
                $Entity->setSubject13($Row['FileSubject13']);
                $Entity->setSubject14($Row['FileSubject14']);
                $Entity->setSubject15($Row['FileSubject15']);
                $Entity->setSubject16($Row['FileSubject16']);
                $Entity->setSubject17($Row['FileSubject17']);
                $Manager->bulkSaveEntity($Entity);
                Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity, true);
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function destroyIndiwareStudentSubjectOrderAllBulk()
    {

        $Manager = $this->getConnection()->getEntityManager();

        $EntityList = $Manager->getEntity('TblIndiwareStudentSubjectOrder')
            ->findAll();
        if (null !== $EntityList) {
            foreach ($EntityList as $Entity) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity, true);
                $Manager->bulkKillEntity($Entity);
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
            return true;
        }
        return false;
    }
}