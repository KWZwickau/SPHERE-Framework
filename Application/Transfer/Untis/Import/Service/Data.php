<?php

namespace SPHERE\Application\Transfer\Untis\Import\Service;

use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Meta\Teacher\Service\Entity\TblTeacher;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Transfer\Untis\Import\Service\Entity\TblUntisImportLectureship;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 * @package SPHERE\Application\Transfer\Untis\Import\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param $Id
     *
     * @return false|TblUntisImportLectureship
     */
    public function getUntisImportLectureshipById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblUntisImportLectureship', $Id);
    }

    /**
     * @return false|TblUntisImportLectureship[]
     */
    public function getUntisImportLectureshipAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblUntisImportLectureship');
    }

    /**
     * @param TblAccount|null $tblAccount
     *
     * @return false|TblUntisImportLectureship[]
     */
    public function getUntisImportLectureshipAllByAccount(TblAccount $tblAccount)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblUntisImportLectureship',
            array(
                TblUntisImportLectureship::ATTR_SERVICE_TBL_ACCOUNT => $tblAccount->getId(),
            ));
    }

    /**
     * @param            $ImportList
     * @param TblYear    $tblYear
     * @param TblAccount $tblAccount
     *
     * @return bool
     */
    public function createUntisImportLectureship(
        $ImportList,
        TblYear $tblYear,
        TblAccount $tblAccount
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        if (!empty($ImportList)) {
            foreach ($ImportList as $Result) {

                $Entity = new TblUntisImportLectureship();
                $Entity->setServiceTblYear($tblYear);
                $Entity->setSchoolClass($Result['FileDivision']);
                $Entity->setTeacherAcronym($Result['FileTeacher']);
                $Entity->setSubjectName($Result['FileSubject']);
                $Entity->setSubjectGroupName($Result['FileSubjectGroup']);
                $Entity->setServiceTblDivision($Result['tblDivision']);
                $Entity->setServiceTblTeacher($Result['tblTeacher']);
                $Entity->setServiceTblSubject($Result['tblSubject']);
                $Entity->setSubjectGroup($Result['AppSubjectGroup']);
                $Entity->setServiceTblAccount($tblAccount);
                $Entity->setIsIgnore(false);

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
     * @param TblUntisImportLectureship|null $tblUntisImportLectureship
     * @param TblDivision|null               $tblDivision
     * @param TblTeacher|null                $tblTeacher
     * @param TblSubject|null                $tblSubject
     * @param string                         $SubjectGroup
     * @param bool                           $IsIgnore
     *
     * @return bool
     */
    public function updateUntisImportLectureship(
        TblUntisImportLectureship $tblUntisImportLectureship = null,
        TblDivision $tblDivision = null,
        TblTeacher $tblTeacher = null,
        TblSubject $tblSubject = null,
        $SubjectGroup = '',
        $IsIgnore = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblUntisImportLectureship $Entity */
        $Entity = $Manager->getEntityById('TblUntisImportLectureship', $tblUntisImportLectureship->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setServiceTblDivision($tblDivision);
            $Entity->setServiceTblTeacher($tblTeacher);
            $Entity->setServiceTblSubject($tblSubject);
            $Entity->setSubjectGroup($SubjectGroup);
            $Entity->setIsIgnore($IsIgnore);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }
        return false;
    }

    /**
     * @param TblUntisImportLectureship $tblUntisImportLectureship
     * @param boolean                   $IsIgnore
     *
     * @return bool
     */
    public function updateUntisImportLectureshipIsIgnore(TblUntisImportLectureship $tblUntisImportLectureship, $IsIgnore = true)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblUntisImportLectureship $Entity */
        $Entity = $Manager->getEntityById('TblUntisImportLectureship', $tblUntisImportLectureship->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setIsIgnore($IsIgnore);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }

        return false;
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool
     */
    public function destroyUntisImportLectureshipByAccount(TblAccount $tblAccount)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $EntityList = $Manager->getEntity('TblUntisImportLectureship')
            ->findBy(array(TblUntisImportLectureship::ATTR_SERVICE_TBL_ACCOUNT => $tblAccount->getId()));
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

    /**
     * @param TblUntisImportLectureship $tblUntisImportLectureship
     *
     * @return bool
     */
    public function destroyUntisImportLectureship(TblUntisImportLectureship $tblUntisImportLectureship)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblUntisImportLectureship $Entity */
        $Entity = $Manager->getEntity('TblUntisImportLectureship')
            ->find($tblUntisImportLectureship->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity, true);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

}