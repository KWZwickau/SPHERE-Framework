<?php
namespace SPHERE\System\Database\Fitting;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use SPHERE\System\Extension\Extension;

/**
 * Class Element
 *
 * - Id (bigint)
 * - EntityCreate (datetime)
 * - EntityUpdate (datetime)
 *
 * @package SPHERE\System\Database\Fitting
 * @MappedSuperclass
 * @HasLifecycleCallbacks
 */
abstract class Element extends Extension
{

    /**
     * @Id
     * @GeneratedValue
     * @Column(type="bigint")
     */
    protected $Id;
    /**
     * @Column(type="datetime")
     */
    protected $EntityCreate;
    /**
     * @Column(type="datetime")
     */
    protected $EntityUpdate;
    /**
     * @Column(type="datetime")
     */
    protected $EntityRemove;

    /**
     * @PrePersist
     */
    final public function lifecycleCreate()
    {

        if (empty( $this->EntityCreate )) {
            $this->EntityCreate = new \DateTime("now");
        }
    }

    /**
     * @PreUpdate
     */
    final public function lifecycleUpdate()
    {

        $this->EntityUpdate = new \DateTime("now");
    }

    /**
     * @throws \Exception
     */
    final public function __toArray()
    {

        $Array = get_object_vars($this);
        array_walk($Array, function (&$V) {

            if (is_object($V)) {
                if ($V instanceof \DateTime) {
                    $V = $V->format('d.m.Y H:i:s');
                }
            }
        });

        return $Array;
    }

    /**
     * @return \DateTime
     */
    public function getEntityCreate()
    {

        return $this->EntityCreate;
    }

    /**
     * @return null|\DateTime
     */
    public function getEntityUpdate()
    {

        return $this->EntityUpdate;
    }

    /**
     * @return null|\DateTime
     */
    public function getEntityRemove()
    {

        return $this->EntityRemove;
    }

    /**
     * @param bool $Toggle
     *
     * @return Element
     */
    public function setEntityRemove($Toggle = true)
    {

        if( $Toggle ) {
            $this->EntityRemove = new \DateTime("now");
        } else {
            $this->EntityRemove = null;
        }
        return $this;
    }

    /**
     * Return Object-Id
     * Fix: Doctrine - Entity can't be converted to 'string' while getting Entity-Id
     *
     * @return string
     */
    final public function __toString()
    {

        return strval($this->getId());
    }

    /**
     * @return integer
     */
    final public function getId()
    {

        return $this->Id;
    }

    /**
     * @param integer $Id
     */
    final public function setId($Id)
    {

        $this->Id = $Id;
    }
}
