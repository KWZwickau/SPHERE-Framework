<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 26.11.2015
 * Time: 14:19
 */

namespace SPHERE\Application\Education\Graduation\Gradebook\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblScoreGroup")
 * @Cache(usage="READ_ONLY")
 */
class TblScoreGroup extends Element
{

    const ATTR_NAME = 'Name';

    /**
     * @Column(type="string")
     */
    protected $Name;

    /**
     * @Column(type="string")
     */
    protected $Multiplier;

    /**
     * @Column(type="string")
     */
    protected $Round;

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
    public function getMultiplier()
    {
        return $this->Multiplier;
    }

    /**
     * @param string $Multiplier
     */
    public function setMultiplier($Multiplier)
    {
        $this->Multiplier = $Multiplier;
    }

    /**
     * @return string
     */
    public function getRound()
    {
        return $this->Round;
    }

    /**
     * @param string $Round
     */
    public function setRound($Round)
    {
        $this->Round = $Round;
    }
}
