<?php
namespace SPHERE\Application\Setting\Consumer\School\Service\Entity;

use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblType")
 * @Cache(usage="READ_ONLY")
 */
class TblType extends Element
{

    const ATTR_NAME = 'Name';

    /**
     * @Column(type="string")
     */
    protected $Name;

    /**
     * @return string $Name
     */
    public function getName()
    {

        return $this->Name;
    }

    /**
     * @param string $Name
     */
    public function setName($Name = null)
    {

        $this->Name = $Name;
    }
}
