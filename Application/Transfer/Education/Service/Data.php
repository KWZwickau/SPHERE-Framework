<?php

namespace SPHERE\Application\Transfer\Education\Service;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImport;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImportLectureship;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImportMapping;
use SPHERE\System\Database\Binding\AbstractData;

class Data extends AbstractData
{
    public function setupDatabaseContent()
    {

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
}