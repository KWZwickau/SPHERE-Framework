<?php
namespace SPHERE\Application\People\Meta\Common\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblCommonGender")
 * @Cache(usage="READ_ONLY")
 */
class TblCommonGender extends Element
{

    const ATTR_NAME = 'Name';
    const VALUE_NULL = 0;
    const VALUE_MALE = 1;
    const VALUE_FEMALE = 2;
    const VALUE_DIVERS = 3;
    const VALUE_OTHER = 4;

    /**
     * @Column(type="string")
     */
    protected $Name;

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
     * m, w, d, o
     * @return false|string
     */
    public function getShortName()
    {
        return substr(strtolower($this->Name), 0, 1);
    }
}
