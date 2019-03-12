<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.03.2019
 * Time: 09:47
 */

namespace SPHERE\Application\Billing\Inventory\Document\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Inventory\Document\Document;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblDocumentItem")
 * @Cache(usage="READ_ONLY")
 */
class TblDocumentItem extends Element
{
    const ATTR_TBL_DOCUMENT = 'tblDocument';
    const ATTR_SERVICE_TBL_ITEM = 'serviceTblItem';

    /**
     * @Column(type="bigint")
     */
    protected $tblDocument;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblItem;

    /**
     * @return bool|TblDocument
     */
    public function getTblDocument()
    {
        if (null === $this->tblDocument) {
            return false;
        } else {
            return Document::useService()->getDocumentById($this->tblDocument);
        }
    }

    /**
     * @param null|TblDocument $tblDocument
     */
    public function setTblDocument(TblDocument $tblDocument = null)
    {
        $this->tblDocument = (null === $tblDocument ? null : $tblDocument->getId());
    }

    /**
     * @return bool|TblItem
     */
    public function getServiceTblItem()
    {
        if (null === $this->serviceTblItem) {
            return false;
        } else {
            return Item::useService()->getItemById($this->serviceTblItem);
        }
    }

    /**
     * @param null|TblItem $serviceTblItem
     */
    public function setServiceTblItem(TblItem $serviceTblItem)
    {
        $this->serviceTblItem = (null === $serviceTblItem ? null : $serviceTblItem->getId());
    }
}