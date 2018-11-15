<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 11.07.2016
 * Time: 08:57
 */

namespace SPHERE\Application\Education\ClassRegister\Absence\Service;

use SPHERE\Application\Education\ClassRegister\Absence\Service\Entity\TblAbsence;
use SPHERE\Application\Education\ClassRegister\Absence\Service\Entity\ViewAbsence;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Education\ClassRegister\Absence\Service
 */
class Data extends AbstractData
{

    /**
     * @return false|ViewAbsence[]
     */
    public function viewAbsence()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewAbsence'
        );
    }

    public function setupDatabaseContent()
    {

    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision|null $tblDivision
     * @param bool $isForced
     *
     * @return false|TblAbsence[]
     */
    public function getAbsenceAllByPerson(TblPerson $tblPerson, TblDivision $tblDivision = null, $isForced = false)
    {

        if ($tblDivision) {
            $parameters = array(
                TblAbsence::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblAbsence::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId()
            );
        } else {
            $parameters = array(
                TblAbsence::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            );
        }

        if ($isForced) {
            return $this->getForceEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAbsence',
                $parameters
            );
        } else {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAbsence',
                $parameters
            );
        }
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return false|TblAbsence[]
     */
    public function getAbsenceAllByDivision(TblDivision $tblDivision)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAbsence',
            array(
                TblAbsence::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId()
            )
        );
    }

    /**
     * @param $Id
     *
     * @return false|TblAbsence
     */
    public function getAbsenceById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAbsence', $Id);
    }

    /**
     * @return false|TblAbsence[]
     */
    public function getAbsenceAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblAbsence');
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param $FromDate
     * @param $ToDate
     * @param $Status
     * @param string $Remark
     *
     * @return TblAbsence
     */
    public function createAbsence(
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        $FromDate,
        $ToDate,
        $Status,
        $Remark = ''
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblAbsence')->findOneBy(array(
            TblAbsence::ATTR_SERVICE_TBL_PERSON => $tblPerson,
            TblAbsence::ATTR_SERVICE_TBL_DIVISION => $tblDivision,
            TblAbsence::ATTR_FROM_DATE => $FromDate ? new \DateTime($FromDate) : null,
            TblAbsence::ATTR_TO_DATE => $ToDate ? new \DateTime($ToDate) : null,
        ));

        if (null === $Entity) {
            $Entity = new TblAbsence();
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setServiceTblDivision($tblDivision);
            $Entity->setFromDate($FromDate ? new \DateTime($FromDate) : null);
            $Entity->setToDate($ToDate ? new \DateTime($ToDate) : null);
            $Entity->setStatus($Status);
            $Entity->setRemark($Remark);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblAbsence $tblAbsence
     * @param $FromDate
     * @param $ToDate
     * @param $Remark
     * @param $Status
     *
     * @return bool
     */
    public function updateAbsence(
        TblAbsence $tblAbsence,
        $FromDate,
        $ToDate,
        $Status,
        $Remark = ''
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblAbsence $Entity */
        $Entity = $Manager->getEntityById('TblAbsence', $tblAbsence->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setFromDate($FromDate ? new \DateTime($FromDate) : null);
            $Entity->setToDate($ToDate ? new \DateTime($ToDate) : null);
            $Entity->setRemark($Remark);
            $Entity->setStatus($Status);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblAbsence $tblAbsence
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function destroyAbsence(
        TblAbsence $tblAbsence,
        $IsSoftRemove = false
    ){

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblAbsence $Entity */
        $Entity = $Manager->getEntityById('TblAbsence', $tblAbsence->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            if ($IsSoftRemove) {
                $Manager->removeEntity($Entity);
            } else {
                $Manager->killEntity($Entity);
            }

            return true;
        }

        return false;
    }

    /**
     * @param TblAbsence $tblAbsence
     *
     * @return bool
     */
    public function restoreAbsence(TblAbsence $tblAbsence)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblAbsence $Entity */
        $Entity = $Manager->getEntityById('TblAbsence', $tblAbsence->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setEntityRemove(null);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }
}