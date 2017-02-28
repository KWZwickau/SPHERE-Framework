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
     * @param TblYear     $tblYear
     * @param string      $SchoolClass
     * @param string      $TeacherAcronym
     * @param string      $SubjectName
     * @param string      $SubjectGroupName
     * @param TblDivision $tblDivision
     * @param TblTeacher  $tblTeacher
     * @param TblSubject  $tblSubject
     * @param string      $SubjectGroup
     * @param TblAccount  $tblAccount
     *
     * @return \SPHERE\System\Database\Fitting\Manager
     */
    public function createUntisImportLectureship(
        TblYear $tblYear,
        $SchoolClass,
        $TeacherAcronym,
        $SubjectName,
        $SubjectGroupName,
        TblDivision $tblDivision = null,
        TblTeacher $tblTeacher = null,
        TblSubject $tblSubject = null,
        $SubjectGroup,
        TblAccount $tblAccount
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblUntisImportLectureship();
        $Entity->setServiceTblYear($tblYear);
        $Entity->setSchoolClass($SchoolClass);
        $Entity->setTeacherAcronym($TeacherAcronym);
        $Entity->setSubjectName($SubjectName);
        $Entity->setSubjectGroupName($SubjectGroupName);
        $Entity->setServiceTblDivision($tblDivision);
        $Entity->setServiceTblTeacher($tblTeacher);
        $Entity->setServiceTblSubject($tblSubject);
        $Entity->setSubjectGroup($SubjectGroup);
        $Entity->setServiceTblAccount($tblAccount);
        $Entity->setIsIgnore(false);

        $Manager->bulkSaveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity, true);

        return $Manager;
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