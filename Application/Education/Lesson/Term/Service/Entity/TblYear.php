<?php
namespace SPHERE\Application\Education\Lesson\Term\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblYear")
 * @Cache(usage="READ_ONLY")
 */
class TblYear extends Element
{

    const ATTR_YEAR = 'Year';
    const ATTR_DESCRIPTION = 'Description';

    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="string")
     */
    protected $Year;
    /**
     * @Column(type="string")
     */
    protected $Description;

    /**
     * @return string
     */
    public function getName()
    {

        if (empty( $this->Name )) {
            $this->setName($this->Year);
        }
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
    public function getYear()
    {

        if (empty( $this->Year )) {
            $this->setYear($this->getName());
        }
        return $this->Year;
    }

    /**
     * @param string $Year
     */
    public function setYear($Year)
    {

        $this->Year = $Year;
    }

    /**
     * @return bool|TblPeriod[]
     */
    public function getTblPeriodAll()
    {

        return Term::useService()->getPeriodAllByYear($this);
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

        return $this->getYear().' '.new Muted($this->getDescription());
    }
}
