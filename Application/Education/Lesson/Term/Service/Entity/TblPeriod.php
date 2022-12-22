<?php
namespace SPHERE\Application\Education\Lesson\Term\Service\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblPeriod")
 * @Cache(usage="READ_ONLY")
 */
class TblPeriod extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_DESCRIPTION = 'Description';
    const ATTR_FROM_DATE = 'FromDate';
    const ATTR_TO_DATE = 'ToDate';
    const ATTR_Is_Level_12 = 'IsLevel12';

    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="string")
     */
    protected $Description;
    /**
     * @Column(type="datetime")
     */
    protected $FromDate;
    /**
     * @Column(type="datetime")
     */
    protected $ToDate;

    /**
     * @Column(type="boolean")
     */
    protected $IsLevel12;

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
    public function getFromDate()
    {

        if (null === $this->FromDate) {
            return false;
        }
        /** @var DateTime $From */
        $From = $this->FromDate;
        if ($From instanceof DateTime) {
            return $From->format('d.m.Y');
        } else {
            return (string)$From;
        }
    }

    /**
     * @return DateTime|null
     */
    public function getFromDateTime(): ?DateTime
    {
        return $this->FromDate;
    }

    /**
     * @param null|DateTime $FromDate
     */
    public function setFromDate(DateTime $FromDate = null)
    {

        $this->FromDate = $FromDate;
    }

    /**
     * @return string
     */
    public function getToDate()
    {

        if (null === $this->ToDate) {
            return false;
        }
        /** @var DateTime $To */
        $To = $this->ToDate;
        if ($To instanceof DateTime) {
            return $To->format('d.m.Y');
        } else {
            return (string)$To;
        }
    }

    /**
     * @return DateTime|null
     */
    public function getToDateTime(): ?DateTime
    {
        return $this->ToDate;
    }

    /**
     * @param null|DateTime $ToDate
     */
    public function setToDate(DateTime $ToDate = null)
    {

        $this->ToDate = $ToDate;
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
     * @return string
     */
    public function getDisplayName()
    {

        return $this->getName()
            . ($this->getFromDate() && $this->getToDate()
                ? ' ' . new Small(new Muted('(' .$this->getFromDate() . ' - ' . $this->getToDate() . ')'))
                : ''
            );
    }

    /**
     * @return boolean
     */
    public function isLevel12()
    {
        return $this->IsLevel12;
    }

    /**
     * @param boolean $IsLevel12
     */
    public function setIsLevel12($IsLevel12)
    {
        $this->IsLevel12 = $IsLevel12;
    }
}
