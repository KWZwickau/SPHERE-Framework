<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.03.2019
 * Time: 10:47
 */

namespace SPHERE\Application\Billing\Inventory\Document\Service\Entity;


use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Inventory\Document\Document;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblDocumentInformation")
 * @Cache(usage="READ_ONLY")
 */
class TblDocumentInformation extends Element
{

    const ATTR_TBL_DOCUMENT = 'tblDocument';
    const ATTR_FIELD = 'Field';

    /**
     * @Column(type="bigint")
     */
    protected $tblDocument;

    /**
     * @Column(type="string")
     */
    protected $Field;

    /**
     * @Column(type="text")
     */
    protected $Value;

    /**
     * @return false|TblDocument
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
     * @param TblDocument|null $tblDocument
     */
    public function setTblDocument(TblDocument $tblDocument = null)
    {
        $this->tblDocument = (null === $tblDocument ? null : $tblDocument->getId());
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->Field;
    }

    /**
     * @param string $Field
     */
    public function setField($Field)
    {
        $this->Field = $Field;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->Value;
    }

    /**
     * @param string $Value
     */
    public function setValue($Value)
    {
        $this->Value = $Value;
    }
}