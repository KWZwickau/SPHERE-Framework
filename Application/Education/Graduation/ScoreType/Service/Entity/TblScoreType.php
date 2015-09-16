<?php

namespace SPHERE\Application\Education\Graduation\ScoreType\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblScoreType")
 * @Cache(usage="READ_ONLY")
 */
class TblScoreType extends Element
{

    const ATTR_NAME = 'Name';

    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="string")
     */
    protected $Short;

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
    public function getShort()
    {

        return $this->Short;
    }

    /**
     * @param string $Short
     */
    public function setShort($Short)
    {

        $this->Short = $Short;
    }


}
