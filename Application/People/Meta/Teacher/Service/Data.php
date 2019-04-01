<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 20.05.2016
 * Time: 08:15
 */

namespace SPHERE\Application\People\Meta\Teacher\Service;

use SPHERE\Application\People\Meta\Teacher\Service\Entity\TblTeacher;
use SPHERE\Application\People\Meta\Teacher\Service\Entity\ViewPeopleMetaTeacher;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 * @package SPHERE\Application\People\Meta\Teacher\Service
 */
class Data  extends AbstractData
{

    /**
     * @return false|ViewPeopleMetaTeacher[]
     */
    public function viewPeopleMetaTeacher()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewPeopleMetaTeacher'
        );
    }

    public function setupDatabaseContent()
    {

    }

    /**
     * @return false|TblTeacher[]
     */
    public function getTeacherAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblTeacher');
    }

    /**
     * @param $Id
     *
     * @return false|TblTeacher
     */
    public function getTeacherById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblTeacher', $Id);
    }

    /**
     *
     * @param TblPerson $tblPerson
     * @param bool $isForced
     *
     * @return bool|TblTeacher
     */
    public function getTeacherByPerson(TblPerson $tblPerson, $isForced = false)
    {

        if ($isForced) {
            return $this->getForceEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblTeacher', array(
                TblTeacher::SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
        } else {
            return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblTeacher', array(
                TblTeacher::SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
        }
    }

    /**
     * @param $Acronym
     *
     * @return false|TblTeacher
     */
    public function getTeacherByAcronym($Acronym)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblTeacher', array(
            TblTeacher::ATTR_ACRONYM => $Acronym
        ));
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Acronym
     *
     * @return TblTeacher
     */
    public function createTeacher(
        TblPerson $tblPerson,
        $Acronym
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblTeacher();
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setAcronym($Acronym);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblTeacher $tblTeacher
     * @param $Acronym
     *
     * @return bool
     */
    public function updateTeacher(
        TblTeacher $tblTeacher,
        $Acronym
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblTeacher $Entity */
        $Entity = $Manager->getEntityById('TblTeacher', $tblTeacher->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setAcronym($Acronym);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param array $TeacherList
     *
     * @return bool
     */
    public function updateTeacherBulk(
        $TeacherList
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        foreach($TeacherList as $TeacherId => $Acronym){
            /** @var null|TblTeacher $Entity */
            $Entity = $Manager->getEntityById('TblTeacher', $TeacherId);
            if (null !== $Entity) {
//                $Protocol = clone $Entity;
                $Entity->setAcronym($Acronym);

                $Manager->bulkSaveEntity($Entity);
                // no Protocol necessary
//                Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            }
        }

        $Manager->flushCache();
//        Protocol::useService()->flushBulkEntries();
        return true;
    }

    /**
     * @param TblTeacher $tblTeacher
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function destroyTeacher(TblTeacher $tblTeacher, $IsSoftRemove = false)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblTeacher $Entity */
        $Entity = $Manager->getEntityById('TblTeacher', $tblTeacher->getId());
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
     * @param TblTeacher $tblTeacher
     *
     * @return bool
     */
    public function restoreTeacher(TblTeacher $tblTeacher)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblTeacher $Entity */
        $Entity = $Manager->getEntityById('TblTeacher', $tblTeacher->getId());
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