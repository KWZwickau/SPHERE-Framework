<?php
namespace SPHERE\Application\Education\Lesson\Division\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\System\Database\Fitting\Element;

/**
 * e.g. 6
 *
 * @Entity
 * @Table(name="tblLevel")
 * @Cache(usage="READ_ONLY")
 */
class TblLevel extends Element
{

    const ATTR_NAME = 'Name';
    const SERVICE_TBL_TYPE = 'serviceTblType';

    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="string")
     */
    protected $Description;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblType;

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
    public function getDescription()
    {

        return $this->Description;
    }

    /**
     * @param string $Description
     */
    public function setDescription($Description)
    {

        $this->Description = $Description;
    }

    /**
     * @return bool|TblType
     */
    public function getServiceTblType()
    {

        if (null === $this->serviceTblType) {
            return false;
        } else {
            return Type::useService()->getTypeById($this->serviceTblType);
        }
    }

    /**
     * @param TblType|null $tblType
     */
    public function setServiceTblType(TblType $tblType = null)
    {

        $this->serviceTblType = ( null === $tblType ? null : $tblType->getId() );
    }
}
