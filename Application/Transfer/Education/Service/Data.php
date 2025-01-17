<?php

namespace SPHERE\Application\Transfer\Education\Service;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImport;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImportLectureship;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImportMapping;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImportStudent;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImportStudentCourse;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

class Data extends AbstractData
{
    public function setupDatabaseContent()
    {

    }

    /**
     * @param array $tblEntityList
     *
     * @return bool
     */
    public function createEntityListBulk(array $tblEntityList): bool
    {
        $Manager = $this->getEntityManager();

        foreach ($tblEntityList as $tblEntity) {
            $Manager->bulkSaveEntity($tblEntity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $tblEntity, true);
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }

    /**
     * @param array $tblEntityList
     *
     * @return bool
     */
    public function updateEntityListBulk(array $tblEntityList): bool
    {
        $Manager = $this->getEntityManager();

        /** @var Element $tblElement */
        foreach ($tblEntityList as $tblElement) {
            $Manager->bulkSaveEntity($tblElement);
            /** @var Element $Entity */
            $Entity = $Manager->getEntityById($tblElement->getEntityShortName(), $tblElement->getId());
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Entity, $tblElement, true);
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }

    /**
     * @param array $tblEntityList
     *
     * @return bool
     */
    public function deleteEntityListBulk(array $tblEntityList): bool
    {
        $Manager = $this->getConnection()->getEntityManager();

        /** @var Element $tblElement */
        foreach ($tblEntityList as $tblElement) {
            /** @var Element $Entity */
            $Entity = $Manager->getEntityById($tblElement->getEntityShortName(), $tblElement->getId());

            $Manager->bulkKillEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity, true);
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }

    /**
     * @param $Id
     *
     * @return false|TblImport
     */
    public function getImportById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblImport', $Id);
    }

    /**
     * @param TblAccount $tblAccount
     * @param string $ExternSoftwareName
     * @param string $TypeIdentifier
     *
     * @return false|TblImport
     */
    public function getImportByAccountAndExternSoftwareNameAndTypeIdentifier(TblAccount $tblAccount, string $ExternSoftwareName, string $TypeIdentifier)
    {
        return $this->getForceEntityBy(__METHOD__, $this->getEntityManager(), 'TblImport', array(
           TblImport::ATTR_SERVICE_TBL_ACCOUNT => $tblAccount->getId(),
           TblImport::ATTR_EXTERN_SOFTWARE_NAME => $ExternSoftwareName,
           TblImport::ATTR_TYPE_IDENTIFIER => $TypeIdentifier
        ));
    }

    /**
     * @param TblImport $tblImport
     *
     * @return TblImport
     */
    public function createImport(TblImport $tblImport): TblImport
    {
        $Manager = $this->getEntityManager();

        $Manager->saveEntity($tblImport);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $tblImport);

        return $tblImport;
    }

    /**
     * @param TblImport $tblImport
     *
     * @return bool
     */
    public function destroyImport(TblImport $tblImport): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblImport $Entity */
        $Entity = $Manager->getEntityById('TblImport', $tblImport->getId());
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
     * @return false|TblImportLectureship
     */
    public function getImportLectureshipById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblImportLectureship', $Id);
    }

    /**
     * @param TblImport $tblImport
     *
     * @return false|TblImportLectureship[]
     */
    public function getImportLectureshipListByImport(TblImport $tblImport)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblImportLectureship', array(
            TblImportLectureship::ATTR_TBL_IMPORT => $tblImport->getId()
        ));
    }

    /**
     * @param TblImport $tblImport
     *
     * @return bool
     */
    public function destroyImportLectureshipAllByImport(TblImport $tblImport): bool
    {
        $Manager = $this->getEntityManager();

        if (($tblImportLectureshipList = $this->getForceEntityListBy(__METHOD__, $Manager, 'TblImportLectureship',
            array(TblImportLectureship::ATTR_TBL_IMPORT => $tblImport->getId())))
        ) {
            foreach ($tblImportLectureshipList as $tblImportLectureship) {
                $Manager->bulkKillEntity($tblImportLectureship);
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $tblImportLectureship, true);
            }
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }

    /**
     * @param string $Type
     * @param string $Original
     *
     * @return false|TblImportMapping
     */
    public function getImportMappingBy(string $Type, string $Original)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblImportMapping', array(
            TblImportMapping::ATTR_TYPE => $Type,
            TblImportMapping::ATTR_ORIGINAL => $Original
        ));
    }

    /**
     * @param string $Type
     * @param string $Original
     * @param string $Mapping
     *
     * @return TblImportMapping
     */
    public function updateImportMapping(string $Type, string $Original, string $Mapping): TblImportMapping
    {
        $Manager = $this->getEntityManager();

        /** @var TblImportMapping $Entity */
        $Entity = $Manager->getEntity('TblImportMapping')->findOneBy(array(
            TblImportMapping::ATTR_TYPE => $Type,
            TblImportMapping::ATTR_ORIGINAL => $Original,
        ));
        if ($Entity === null) {
            $Entity = new TblImportMapping();
            $Entity->setType($Type);
            $Entity->setOriginal($Original);
            $Entity->setMapping($Mapping);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        } else {
            $Protocol = clone $Entity;
            $Entity->setMapping($Mapping);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblImportMapping $tblImportMapping
     *
     * @return bool
     */
    public function destroyImportMapping(TblImportMapping $tblImportMapping): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblImportMapping $Entity */
        $Entity = $Manager->getEntityById('TblImportMapping', $tblImportMapping->getId());
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
     * @return false|TblImportStudent
     */
    public function getImportStudentById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblImportStudent', $Id);
    }

    /**
     * @param TblImport $tblImport
     *
     * @return false|TblImportStudent[]
     */
    public function getImportStudentListByImport(TblImport $tblImport)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblImportStudent', array(
            TblImportStudent::ATTR_TBL_IMPORT => $tblImport->getId()
        ));
    }

    /**
     * @param TblImportStudent $tblImportStudent
     *
     * @return TblImportStudent
     */
    public function createImportStudent(TblImportStudent $tblImportStudent): TblImportStudent
    {
        $Manager = $this->getEntityManager();

        $Manager->saveEntity($tblImportStudent);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $tblImportStudent);

        return $tblImportStudent;
    }

    /**
     * @param TblImportStudent $tblImportStudent
     *
     * @return bool
     */
    public function destroyImportStudentCourseAllByImportStudent(TblImportStudent $tblImportStudent): bool
    {
        $Manager = $this->getEntityManager();

        if (($tblImportStudentCourseList = $this->getForceEntityListBy(__METHOD__, $Manager, 'TblImportStudentCourse',
            array(TblImportStudentCourse::ATTR_TBL_IMPORT_STUDENT => $tblImportStudent->getId())))
        ) {
            foreach ($tblImportStudentCourseList as $tblImportStudentCourse) {
                $Manager->bulkKillEntity($tblImportStudentCourse);
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $tblImportStudentCourse, true);
            }
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }

    /**
     * @param $Id
     *
     * @return false|TblImportStudentCourse
     */
    public function getImportStudentCourseById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblImportStudentCourse', $Id);
    }

    /**
     * @param TblImport $tblImport
     *
     * @return false|TblImportStudentCourse[]
     */
    public function getImportStudentCourseListByImport(TblImport $tblImport)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('t')
            ->from(TblImportStudentCourse::class, 't')
            ->leftJoin(TblImportStudent::class, 's', 'WITH', 't.tblImportStudent = s.Id')
            ->leftJoin(TblImport::class, 'i', 'WITH', 's.tblImport = i.Id')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('i.Id', '?1'),
                ),
            )
            ->setParameter(1, $tblImport->getId())
            ->getQuery();

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblImportStudent $tblImportStudent
     *
     * @return false|TblImportStudentCourse[]
     */
    public function getImportStudentCourseListByImportStudent(TblImportStudent $tblImportStudent)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblImportStudentCourse', array(
            TblImportStudentCourse::ATTR_TBL_IMPORT_STUDENT => $tblImportStudent->getId(),
        ));
    }
}