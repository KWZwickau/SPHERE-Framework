<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 26.11.2015
 * Time: 14:18
 */

namespace SPHERE\Application\Education\Graduation\Gradebook\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblScoreCondition")
 * @Cache(usage="READ_ONLY")
 */
class TblScoreCondition extends Element
{
    /**
     * @Column(type="string")
     */
    protected $Name;

    /**
     * @Column(type="integer")
     */
    protected $Priority;

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
    public function getPriority()
    {
        return $this->Priority;
    }

    /**
     * @param string $Priority
     */
    public function setPriority($Priority)
    {
        $this->Priority = $Priority;
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