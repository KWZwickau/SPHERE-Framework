<?php
namespace SPHERE\Application\Education\Lesson\Term\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblPeriod")
 * @Cache(usage="READ_ONLY")
 */
class TblPeriod extends Element
{

    const ATTR_NAME = 'Name';

    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="datetime")
     */
    protected $From;
    /**
     * @Column(type="datetime")
     */
    protected $To;

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
    public function getFrom()
    {

        if (null === $this->From) {
            return false;
        }
        /** @var \DateTime $From */
        $From = $this->From;
        if ($From instanceof \DateTime) {
            return $From->format('d.m.Y');
        } else {
            return (string)$From;
        }
    }

    /**
     * @param null|\DateTime $From
     */
    public function setFrom(\DateTime $From = null)
    {

        $this->From = $From;
    }

    /**
     * @return string
     */
    public function getTo()
    {

        if (null === $this->To) {
            return false;
        }
        /** @var \DateTime $To */
        $To = $this->To;
        if ($To instanceof \DateTime) {
            return $To->format('d.m.Y');
        } else {
            return (string)$To;
        }
    }

    /**
     * @param null|\DateTime $To
     */
    public function setTo(\DateTime $To = null)
    {

        $this->To = $To;
    }
}
