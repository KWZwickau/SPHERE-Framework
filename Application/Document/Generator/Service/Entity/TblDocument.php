<?php

namespace SPHERE\Application\Document\Generator\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblDocument")
 * @Cache(usage="READ_ONLY")
 */
class TblDocument extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_DOCUMENT_CLASS = 'DocumentClass';
    const SERVICE_TBL_SCHOOL_TYPE = 'serviceTblSchoolType';

    /**
     * @Column(type="string")
     */
    protected $Name;

    /**
     * @Column(type="string")
     */
    protected $DocumentClass;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSchoolType;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->Name;
    }

    /**
     * @param string $Name
     */
    public function setName($Name)
    {
        $this->Name = $Name;
    }

    /**
     * @return string
     */
    public function getDocumentClass()
    {
        return $this->DocumentClass;
    }

    /**
     * @param string $DocumentClass
     */
    public function setDocumentClass($DocumentClass)
    {
        $this->DocumentClass = $DocumentClass;
    }

    /**
     * @return bool|TblType
     */
    public function getServiceTblSchoolType()
    {

        if (null === $this->serviceTblSchoolType) {
            return false;
        } else {
            return Type::useService()->getTypeById($this->serviceTblSchoolType);
        }
    }

    /**
     * @param TblType|null $tblSchoolType
     */
    public function setServiceTblSchoolType(TblType $tblSchoolType = null)
    {

        $this->serviceTblSchoolType = (null === $tblSchoolType ? null : $tblSchoolType->getId());
    }
}