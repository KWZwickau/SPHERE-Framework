<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.03.2019
 * Time: 09:37
 */

namespace SPHERE\Application\Billing\Inventory\Document\Service;

use SPHERE\Application\Billing\Inventory\Document\Service\Entity\TblDocument;
use SPHERE\Application\Billing\Inventory\Document\Service\Entity\TblDocumentInformation;
use SPHERE\Application\Billing\Inventory\Document\Service\Entity\TblDocumentItem;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Billing\Inventory\Document\Service
 */
class Data extends AbstractData
{
    /**
     * @return void
     */
    public function setupDatabaseContent()
    {

    }

    /**
     * @param $Id
     *
     * @return false|TblDocument
     */
    public function getDocumentById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblDocument', $Id);
    }

    /**
     * @return false|TblDocument[]
     */
    public function getDocumentAll()
    {
        return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblDocument');
    }

    /**
     * @param $Name
     * @param $Description
     *
     * @return TblDocument
     */
    public function createDocument($Name, $Description)
    {
        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblDocument();
        $Entity->setName($Name);
        $Entity->setDescription($Description);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblDocument $tblDocument
     * @param $Name
     * @param $Description
     *
     * @return bool
     */
    public function updateDocument(
        TblDocument $tblDocument,
        $Name,
        $Description
    ) {
        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblDocument $Entity */
        $Entity = $Manager->getEntityById('TblDocument', $tblDocument->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);
            $Entity->setDescription($Description);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblDocument $tblDocument
     *
     * @return false|TblDocumentItem[]
     */
    public function getDocumentItemAllByDocument(TblDocument $tblDocument)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblDocumentItem', array(
           TblDocumentItem::ATTR_TBL_DOCUMENT => $tblDocument->getId()
        ));
    }

    /**
     * @param TblItem $tblItem
     *
     * @return false|TblDocumentItem[]
     */
    public function getDocumentItemAllByItem(TblItem $tblItem)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblDocumentItem', array(
                TblDocumentItem::ATTR_SERVICE_TBL_ITEM => $tblItem->getId())
        );
    }

    /**
     * @param TblDocument $tblDocument
     * @param TblItem $tblItem
     *
     * @return TblDocumentItem
     */
    public function addDocumentItem(TblDocument $tblDocument,TblItem $tblItem)
    {
        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblDocumentItem')
            ->findOneBy(array(
                TblDocumentItem::ATTR_TBL_DOCUMENT => $tblDocument->getId(),
                TblDocumentItem::ATTR_SERVICE_TBL_ITEM => $tblItem->getId(),
            ));
        if (null === $Entity) {
            $Entity = new TblDocumentItem();
            $Entity->setTblDocument($tblDocument);
            $Entity->setServiceTblItem($tblItem);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblDocumentItem $tblDocumentItem
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function removeDocumentItem(TblDocumentItem $tblDocumentItem, $IsSoftRemove = false)
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblDocumentItem $Entity */
        $Entity = $Manager->getEntityById('TblDocumentItem', $tblDocumentItem->getId());
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
     * @param TblDocument $tblDocument
     *
     * @return bool
     */
    public function removeDocument(TblDocument $tblDocument)
    {
        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblDocument $Entity */
        $Entity = $Manager->getEntityById('TblDocument', $tblDocument->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->removeEntity($Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblDocument $tblDocument
     * @param $Field
     *
     * @return false|TblDocumentInformation
     */
    public function getDocumentInformationBy(TblDocument $tblDocument, $Field)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblDocumentInformation', array(
            TblDocumentInformation::ATTR_TBL_DOCUMENT => $tblDocument->getId(),
            TblDocumentInformation::ATTR_FIELD => $Field
        ));
    }

    /**
     * @param TblDocument $tblDocument
     *
     * @return false|TblDocumentInformation[]
     */
    public function getDocumentInformationAllByDocument(TblDocument $tblDocument)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblDocumentInformation', array(
            TblDocumentInformation::ATTR_TBL_DOCUMENT => $tblDocument->getId()
        ));
    }

    /**
     * @param TblDocument $tblDocument
     * @param $Field
     * @param $Value
     *
     * @return TblDocumentInformation
     */
    public function createDocumentInformation(
        TblDocument $tblDocument,
        $Field,
        $Value
    ) {
        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblDocumentInformation')->findOneBy(array(
            TblDocumentInformation::ATTR_TBL_DOCUMENT => $tblDocument->getId(),
            TblDocumentInformation::ATTR_FIELD => $Field,
        ));
        if ($Entity === null) {
            $Entity = new TblDocumentInformation();
            $Entity->setTblDocument($tblDocument);
            $Entity->setField($Field);
            $Entity->setValue($Value);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblDocumentInformation $tblDocumentInformation
     * @param $Field
     * @param $Value
     *
     * @return bool
     */
    public function updateDocumentInformation(
        TblDocumentInformation $tblDocumentInformation,
        $Field,
        $Value
    ) {
        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblDocumentInformation $Entity */
        $Entity = $Manager->getEntityById('TblDocumentInformation', $tblDocumentInformation->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setField($Field);
            $Entity->setValue($Value);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }
}