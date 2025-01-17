<?php
namespace SPHERE\Application\Document\Storage\Service;

use MOC\V\Component\Database\Component\IBridgeInterface;
use MOC\V\Component\Database\Database as MocDatabase;
use SPHERE\Application\Document\Storage\Service\Entity\TblBinary;
use SPHERE\Application\Document\Storage\Service\Entity\TblBinaryRevision;
use SPHERE\Application\Document\Storage\Service\Entity\TblDirectory;
use SPHERE\Application\Document\Storage\Service\Entity\TblFile;
use SPHERE\Application\Document\Storage\Service\Entity\TblFileCategory;
use SPHERE\Application\Document\Storage\Service\Entity\TblFileType;
use SPHERE\Application\Document\Storage\Service\Entity\TblPartition;
use SPHERE\Application\Document\Storage\Service\Entity\TblPersonPicture;
use SPHERE\Application\Document\Storage\Service\Entity\TblReferenceType;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Database;
use SPHERE\System\Database\Type\MySql;

/**
 * Class Data
 *
 * @package SPHERE\Application\Document\Explorer\Storage\Service
 */
class Data extends AbstractData
{

    /**
     * @return void
     */
    public function setupDatabaseContent()
    {

        $this->createPartition(
            'Zeugnisse', 'revisionssichere Archivierung', true, TblPartition::IDENTIFIER_CERTIFICATE_STORAGE
        );

        $FileCategoryDOCUMENT = $this->createFileCategory('Dokumente', TblFileCategory::CATEGORY_DOCUMENT);
        $FileCategoryIMAGE = $this->createFileCategory('Bilder', TblFileCategory::CATEGORY_IMAGE);

        // Documents
        $this->createFileType('PDF-Datei', 'pdf', 'application/pdf', $FileCategoryDOCUMENT);
        // Images
        $this->createFileType('PNG-Datei', 'png', 'image/png', $FileCategoryIMAGE);
    }

    /**
     * @param string $Name
     * @param string $Description
     * @param bool $IsLocked
     * @param string $Identifier
     *
     * @return TblPartition
     */
    public function createPartition($Name, $Description = '', $IsLocked = false, $Identifier = '')
    {

        $Manager = $this->getConnection()->getEntityManager();

        if ($IsLocked) {
            $Entity = $Manager->getEntity('TblPartition')->findOneBy(array(
                TblPartition::ATTR_IS_LOCKED => $IsLocked,
                TblPartition::ATTR_IDENTIFIER => $Identifier,
                TblPartition::ENTITY_REMOVE => null
            ));
        } else {
            $Entity = $Manager->getEntity('TblPartition')->findOneBy(array(
                TblPartition::ATTR_NAME => $Name,
                TblPartition::ENTITY_REMOVE => null
            ));
        }

        if (null === $Entity) {
            $Entity = new TblPartition();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setLocked($IsLocked);
            $Entity->setIdentifier($Identifier);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param string $Name
     * @param string $Identifier
     *
     * @return TblFileCategory
     */
    public function createFileCategory($Name, $Identifier)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblFileCategory')->findOneBy(array(
            TblFileCategory::ATTR_IDENTIFIER => strtoupper($Identifier)
        ));

        if (null === $Entity) {
            $Entity = new TblFileCategory();
            $Entity->setName($Name);
            $Entity->setIdentifier($Identifier);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param string $Name
     * @param string $Extension
     * @param string $MimeType
     * @param TblFileCategory $tblFileCategory
     *
     * @return TblFileType
     */
    public function createFileType($Name, $Extension, $MimeType, TblFileCategory $tblFileCategory)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblFileType')->findOneBy(array(
            TblFileType::ATTR_EXTENSION => $Extension,
            TblFileType::ATTR_MIME_TYPE => $MimeType
        ));

        if (null === $Entity) {
            $Entity = new TblFileType();
            $Entity->setName($Name);
            $Entity->setExtension($Extension);
            $Entity->setMimeType($MimeType);
            $Entity->setTblFileCategory($tblFileCategory);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblPartition $tblPartition
     * @param string $Name
     * @param string $Description
     * @param TblDirectory $tblDirectory
     * @param bool $IsLocked
     * @param string $Identifier
     *
     * @return TblDirectory
     */
    public function createDirectory(
        TblPartition $tblPartition,
        $Name,
        $Description,
        TblDirectory $tblDirectory = null,
        $IsLocked = false,
        $Identifier = ''
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        if ($IsLocked) {
            $Entity = $Manager->getEntity('TblDirectory')->findOneBy(array(
                TblDirectory::ATTR_IS_LOCKED => $IsLocked,
                TblDirectory::ATTR_IDENTIFIER => strtoupper($Identifier),
                TblDirectory::ATTR_TBL_PARTITION => $tblPartition->getId(),
                TblDirectory::ATTR_TBL_DIRECTORY => ($tblDirectory ? $tblDirectory->getId() : null),
                TblDirectory::ENTITY_REMOVE => null
            ));
        } else {
            $Entity = $Manager->getEntity('TblDirectory')->findOneBy(array(
                TblDirectory::ATTR_NAME => $Name,
                TblDirectory::ATTR_TBL_PARTITION => $tblPartition->getId(),
                TblDirectory::ATTR_TBL_DIRECTORY => ($tblDirectory ? $tblDirectory->getId() : null),
                TblDirectory::ENTITY_REMOVE => null
            ));
        }

        if (null === $Entity) {
            $Entity = new TblDirectory();
            $Entity->setTblPartition($tblPartition);
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setTblDirectory($tblDirectory);
            $Entity->setLocked($IsLocked);
            $Entity->setIdentifier(strtoupper($Identifier));
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param int $Id
     *
     * @return false|TblPartition
     */
    public function getPartitionById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPartition', $Id);
    }

    /**
     * @param string $Identifier
     *
     * @return false|TblPartition
     */
    public function getPartitionByIdentifier($Identifier)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPartition', array(
            TblPartition::ATTR_IDENTIFIER => strtoupper($Identifier)
        ));
    }

    /**
     * @return false|TblPartition[]
     */
    public function getPartitionAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPartition');
    }

    /**
     * @param int $Id
     *
     * @return false|TblBinary
     */
    public function getBinaryById($Id)
    {

        return $this->getForceEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBinary', $Id);
    }

    /**
     * @return false|TblBinary[]
     */
    public function getBinaryAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBinary');
    }

    /**
     * @param int $Id
     *
     * @return false|TblFile
     */
    public function getFileById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblFile', $Id);
    }

    /**
     * @param TblDirectory $tblDirectory
     *
     * @return false|TblFile[]
     */
    public function getFileAllByDirectory(TblDirectory $tblDirectory)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblFile',
            array(
                TblFile::ATTR_TBL_DIRECTORY => $tblDirectory->getId()
            ));
    }

    /**
     * @return false|TblFile[]
     */
    public function getFileAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblFile');
    }

    /**
     * @param int $Id
     *
     * @return false|TblDirectory
     */
    public function getDirectoryById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDirectory', $Id);
    }

    /**
     * @param null|TblDirectory $tblDirectory
     *
     * @return false|TblDirectory[]
     */
    public function getDirectoryAllByParent(TblDirectory $tblDirectory = null)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDirectory',
            array(
                TblDirectory::ATTR_TBL_DIRECTORY => ($tblDirectory ? $tblDirectory->getId() : null)
            ));
    }

    /**
     * @param TblPartition $tblPartition
     *
     * @return false|TblDirectory[]
     */
    public function getDirectoryAllByPartition(TblPartition $tblPartition)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDirectory',
            array(
                TblDirectory::ATTR_TBL_PARTITION => $tblPartition->getId()
            ));
    }

    /**
     * @param string $identifier
     *
     * @return false|TblDirectory[]
     */
    public function getDirectoryAllByIdentifier(string $identifier)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDirectory',
            array(
                TblDirectory::ATTR_IDENTIFIER => $identifier
            ));
    }

    /**
     * @return false|TblDirectory[]
     */
    public function getDirectoryAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDirectory');
    }

    /**
     * @param $BinaryBlob
     * @param int $fileSizeKByte
     * @param string $hash
     * @param TblPerson|null $tblPerson
     *
     * @return TblBinary
     */
    public function createBinary($BinaryBlob, int $fileSizeKByte, string $hash, ?TblPerson $tblPerson): TblBinary
    {

        $Manager = $this->getConnection()->getEntityManager();

        $New = new TblBinary();
        $New->setBinaryBlob($BinaryBlob);
        $New->setFileSizeKiloByte($fileSizeKByte);
        $New->setHash($hash);
        $New->setServiceTblPersonPrinter($tblPerson);

        $Entity = $Manager->getEntity('TblBinary')->findOneBy(array(
            TblBinary::ATTR_HASH => $hash,
            TblBinary::ENTITY_REMOVE => null
        ));

        if (null === $Entity) {
            $Entity = $New;
            $Manager->saveEntity($Entity);
//            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblFile $tblFile
     * @param TblBinary $tblBinary
     * @param int $version
     * @param string $description
     *
     * @return TblBinaryRevision
     */
    public function createBinaryRevision(
        TblFile $tblFile,
        TblBinary $tblBinary,
        int $version,
        string $description
    ) {
        $Manager = $this->getEntityManager();

        $Entity = new TblBinaryRevision();
        $Entity->setTblFile($tblFile);
        $Entity->setTblBinary($tblBinary);
        $Entity->setVersion($version);
        $Entity->setDescription($description);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblFile $tblFile
     *
     * @return false|TblBinaryRevision[]
     */
    public function getBinaryRevisionListByFile(TblFile $tblFile)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblBinaryRevision', array(TblBinaryRevision::ATTR_TBL_FILE => $tblFile->getId()),
             array(TblBinaryRevision::ATTR_VERSION => self::ORDER_DESC)
        );
    }

    /**
     * @param $Id
     *
     * @return false|TblBinaryRevision
     */
    public function getBinaryRevisionById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblBinaryRevision', $Id);
    }

    /**
     * @param TblBinary $tblBinary
     * @param TblDirectory $tblDirectory
     * @param TblFileType $tblFileType
     * @param string $Name
     * @param string $Description
     * @param bool $IsLocked
     *
     * @return TblFile
     */
    public function createFile(
        TblBinary $tblBinary,
        TblDirectory $tblDirectory,
        TblFileType $tblFileType,
        $Name,
        $Description = '',
        $IsLocked = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblFile')->findOneBy(array(
            TblFile::ATTR_TBL_DIRECTORY => $tblDirectory->getId(),
            TblFile::ATTR_TBL_FILE_TYPE => $tblFileType->getId(),
            TblFile::ATTR_NAME => $Name,
            TblFile::ENTITY_REMOVE => null
        ));

        if (null === $Entity) {
            $Entity = new TblFile();
            $Entity->setTblBinary($tblBinary);
            $Entity->setTblDirectory($tblDirectory);
            $Entity->setTblFileType($tblFileType);
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setLocked($IsLocked);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param string $MimeType
     *
     * @return false|TblFileType
     */
    public function getFileTypeByMimeType($MimeType)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblFileType', array(
            TblFileType::ATTR_MIME_TYPE => $MimeType
        ));
    }

    /**
     * @param int $Id
     *
     * @return false|TblFileType
     */
    public function getFileTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblFileType', $Id);
    }

    /**
     * @param int $Id
     *
     * @return false|TblFileCategory
     */
    public function getFileCategoryById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblFileCategory',
            $Id);
    }

    /**
     * @param int $Id
     *
     * @return false|TblReferenceType
     */
    public function getReferenceTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblReferenceType',
            $Id);
    }

    /**
     * @param TblDirectory $tblDirectory
     * @param TblFileType $tblFileType
     * @param $Name
     *
     * @return false|TblFile
     */
    public function exitsFile(
        TblDirectory $tblDirectory,
        TblFileType $tblFileType,
        $Name
    ) {
        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblFile', array(
            TblFile::ATTR_TBL_DIRECTORY => $tblDirectory->getId(),
            TblFile::ATTR_TBL_FILE_TYPE => $tblFileType->getId(),
            TblFile::ATTR_NAME => $Name,
            TblFile::ENTITY_REMOVE => null
        ));
    }

    /**
     * @param TblFile $tblFile
     * @param TblBinary $tblBinary
     * @param $Description
     *
     * @return bool
     */
    public function updateFile(
        TblFile $tblFile,
        TblBinary $tblBinary,
        $Description
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblFile $Entity*/
        $Entity = $Manager->getEntityById('TblFile', $tblFile->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setTblBinary($tblBinary);
            $Entity->setDescription($Description);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblPersonPicture
     */
    public function getPersonPictureByPerson(TblPerson $tblPerson)
    {

        /** @var TblPersonPicture $Entity */
        $Entity = $this->getCachedEntityBy(__Method__, $this->getConnection()->getEntityManager(), 'TblPersonPicture',
            array(TblPersonPicture::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()));
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param TblPerson $tblPerson
     * @param           $File
     *
     * @return void
     */
    public function insertPersonPicture(TblPerson $tblPerson, $File)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblPersonPicture')->findOneBy(array(
            TblPersonPicture::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
        ));

        if(null === $Entity){
            // create
            $Entity = new TblPersonPicture();
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setPicture($File);

            $Manager->saveEntity($Entity);
            //            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
            //                $Entity);
        } else {
            // update
            /** @var TblPersonPicture $Entity */
            $Entity->setPicture($File);
            $Manager->saveEntity($Entity);
        }
    }

    /**
     * @param TblPersonPicture $TblPersonPicture
     *
     * @return void
     */
    public function destroyPersonPicture(TblPersonPicture $TblPersonPicture)
    {

        $Manager = $this->getConnection()->getEntityManager();
        //        Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $TblPersonPicture);

        $Manager->killEntity($TblPersonPicture);
    }

    /**
     * @return bool|array[]
     */
    public function getBinaryIdAndFileSizeListWithoutFileSize(int $maxResults, int $startId = 0): bool|array
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('b.Id, LENGTH(b.BinaryBlob) as FileSize')
            ->from(TblBinary::class, 'b')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('b.FileSizeKiloByte', '?1'),
                    $queryBuilder->expr()->gte('b.Id', '?2')
                )
            )
            ->setParameter(1, 0)
            ->setParameter(2, $startId)
            ->setMaxResults($maxResults)
            ->getQuery();

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param array $list
     *
     * @return float
     */
    public function updateFileSize(array $list): float
    {
        $Manager = $this->getEntityManager();
        $start = hrtime(true);

        foreach($list as $item) {
            /** @var TblBinary $Entity */
            $Entity = $Manager->getEntityById('TblBinary', $item['Id']);
//            $Protocol = clone $Entity;
            $Entity->setFileSizeKiloByte(intdiv($item['FileSize'], 1024));

//            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity, true);
        }

        $Manager->flushCache();
//        Protocol::useService()->flushBulkEntries();

        $end = hrtime(true);

        return round(($end - $start) / 1000000000, 2);
    }

    /**
     * @param TblConsumer $tblConsumer
     * @param bool $isOnlyWithoutFile
     *
     * @return int
     */
    public function getFileSizeByConsumer(TblConsumer $tblConsumer, bool $isOnlyWithoutFile = false): int
    {
        $sumKiloByte = 0;
        $connection = false;
        $container = Database::getDataBaseConfig($tblConsumer);

        if ($container) {
            try {
                $connection = $this->getConnectionByAcronym(
                    $container->getContainer('Host')->getValue(),
                    $container->getContainer('Username')->getValue(),
                    $container->getContainer('Password')->getValue(),
                    $tblConsumer->getAcronym()
                );
                if ($connection) {
                    $queryBuilder = $connection->getQueryBuilder();

                    if ($isOnlyWithoutFile) {
                        $query = $queryBuilder->select('SUM(b.FileSizeKiloByte) as SumFileSize')
                            ->from($tblConsumer->getAcronym() . '_DocumentStorage.tblBinary', 'b')
                            ->where('not exists (select * from ' .$tblConsumer->getAcronym() . '_DocumentStorage.tblFile f where f.tblBinary = b.Id)');
                    } else {
                        $query = $queryBuilder->select('SUM(b.FileSizeKiloByte) as SumFileSize')
                            ->from($tblConsumer->getAcronym() . '_DocumentStorage.tblBinary', 'b');
                    }

                    $result = $query->execute();
                    $array = $result->fetch();

                    if (isset($array['SumFileSize'])) {
                        $sumKiloByte = $array['SumFileSize'];
                    }

                    $connection->getConnection()->close();
                }
            } catch (\Exception $Exception) {
                if ($connection) {
                    $connection->getConnection()->close();
                }
                $connection = null;
            }
        }

        return $sumKiloByte;
    }

    /**
     * @param string $Host Server-Address (IP)
     * @param string $User
     * @param string $Password
     * @param string $Acronym DatabaseName will get prefix '_DocumentStorage' e.g. {Acronym}_DocumentStorage
     *
     * @return bool|IBridgeInterface
     */
    private function getConnectionByAcronym($Host, $User, $Password, $Acronym)
    {
        $Connection = MocDatabase::getDatabase(
            $User, $Password, strtoupper($Acronym).'_DocumentStorage', (new MySql())->getIdentifier(), $Host
        );
        if ($Connection->getConnection()->isConnected()) {
            return $Connection;
        }
        return false;
    }
}
