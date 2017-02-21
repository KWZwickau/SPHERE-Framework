<?php

namespace SPHERE\Application\Transfer\Untis\Import\Service;

use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
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
     * @param TblAccount $tblAccount
     *
     * @return false|TblUntisImportLectureship[]
     */
    public function getUntisImportLectureshipByAccount(TblAccount $tblAccount)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblUntisImportLectureship',
            array(
                TblUntisImportLectureship::ATTR_SERVICE_TBL_ACCOUNT => $tblAccount->getId(),
            ));
    }

    public function createUntisImportLectureship(
        TblYear $tblYear,
        $SchoolClass,
        $TeacherAcronym,
        $SubjectName,
        $GroupName,
        $tblDivision,
        $tblPerson,
        $tblSubject,
        $tblGroup,
        $tblAccount
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblUntisImportLectureship();
        $Entity->setServiceTblYear($tblYear);
        $Entity->setSchoolClass($SchoolClass);
        $Entity->setTeacherAcronym($TeacherAcronym);
        $Entity->setSubjectName($SubjectName);
        $Entity->setGroupName($GroupName);
        $Entity->setServiceTblDivision($tblDivision);
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setServiceTblSubject($tblSubject);
        $Entity->setServiceTblSubjectGroup($tblGroup);
        $Entity->setServiceTblAccount($tblAccount);
        $Entity->setIsIgnore(false);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblUntisImportLectureship|null $tblUntisImportLectureship
     * @param TblDivision|null               $tblDivision
     * @param TblPerson|null                 $tblPerson
     * @param TblSubject|null                $tblSubject
     * @param TblSubjectGroup                $tblSubjectGroup
     *
     * @return bool
     */
    public function updateUntisImportLectureship(
        TblUntisImportLectureship $tblUntisImportLectureship = null,
        TblDivision $tblDivision = null,
        TblPerson $tblPerson = null,
        TblSubject $tblSubject = null,
        TblSubjectGroup $tblSubjectGroup = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblUntisImportLectureship $Entity */
        $Entity = $Manager->getEntityById('TblUntisImportLectureship', $tblUntisImportLectureship->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setServiceTblDivision($tblDivision);
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setSubjectName($tblSubject);
            $Entity->setServiceTblSubjectGroup($tblSubjectGroup);

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
    public function updateUntisImportLectureshipIsIgnore(TblUntisImportLectureship $tblUntisImportLectureship, $IsIgnore)
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

}