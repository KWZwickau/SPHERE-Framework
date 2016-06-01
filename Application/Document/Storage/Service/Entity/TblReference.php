<?php
namespace SPHERE\Application\Document\Storage\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblReference")
 * @Cache(usage="READ_ONLY")
 */
class TblReference extends Element
{

    const ATTR_TBL_FILE = 'tblFile';
    const ATTR_TBL_REFERENCE_TYPE = 'tblReferenceType';
    const FOREIGN_TBL_ENTITY = 'foreignTblEntity';

    /**
     * @Column(type="bigint")
     */
    protected $tblFile;
    /**
     * @Column(type="bigint")
     */
    protected $tblReferenceType;
    /**
     * @Column(type="bigint")
     */
    protected $foreignTblEntity;

    /**
     * @return bool|TblFile
     */
    public function getTblFile()
    {

        if (null === $this->tblFile) {
            return false;
        } else {
            return Storage::useService()->getFileById($this->tblFile);
        }
    }

    /**
     * @param null|TblFile $tblFile
     */
    public function setTblFile(TblFile $tblFile = null)
    {

        $this->tblFile = ( null === $tblFile ? null : $tblFile->getId() );
    }

    /**
     * @return bool|TblReferenceType
     */
    public function getTblReferenceType()
    {

        if (null === $this->tblReferenceType) {
            return false;
        } else {
            return Storage::useService()->getReferenceTypeById($this->tblReferenceType);
        }
    }

    /**
     * @param null|TblReferenceType $tblReferenceType
     */
    public function setTblReferenceType(TblReferenceType $tblReferenceType = null)
    {

        $this->tblReferenceType = ( null === $tblReferenceType ? null : $tblReferenceType->getId() );
    }

    /**
     * @param AbstractService $Service
     * @param string          $Method
     *
     * @return bool|Element
     * @throws \Exception
     */
    public function getForeignTblEntity(AbstractService $Service, $Method)
    {

        if (null === $this->foreignTblEntity) {
            return false;
        } else {
            if (method_exists($Service, $Method)) {
                return $Service->$Method($this->foreignTblEntity);
            } else {
                throw new \Exception('Method '.$Method.' not found in '.get_class($Service));
            }
        }
    }

    /**
     * @param null|Element $Entity
     */
    public function setForeignTblEntity(Element $Entity = null)
    {

        $this->foreignTblEntity = ( null === $Entity ? null : $Entity->getId() );
    }
}
