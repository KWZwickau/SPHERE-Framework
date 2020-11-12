<?php
namespace SPHERE\Application\Education\School\Type\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblType")
 * @Cache(usage="READ_ONLY")
 */
class TblType extends Element
{

    const IDENT_BERUFLICHES_GYMNASIUM = 'Berufliches Gymnasium';
    const IDENT_BERUFS_FACH_SCHULE = 'Berufsfachschule';
    const IDENT_BERUFS_SCHULE = 'Berufsschule';
    const IDENT_FACH_OBER_SCHULE = 'Fachoberschule';
    const IDENT_FACH_SCHULE = 'Fachschule';
    const IDENT_GRUND_SCHULE = 'Grundschule';
    const IDENT_GYMNASIUM = 'Gymnasium';
    const IDENT_OBER_SCHULE = 'Mittelschule / Oberschule';
    const IDENT_ALLGEMEIN_BILDENDE_FOERDERSCHULE = 'Förderschule';

    const ATTR_NAME = 'Name';
    const ATTR_SHORT_NAME = 'ShortName';

    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="string")
     */
    protected $Description;
    /**
     * @Column(type="string")
     */
    protected $ShortName;

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
     * @return string
     */
    public function getShortName()
    {
        return $this->ShortName;
    }

    /**
     * @param string $ShortName
     */
    public function setShortName($ShortName)
    {
        $this->ShortName = $ShortName;
    }

    /**
     * @return bool
     */
    public function isTechnical()
    {
        if ($this->getName() == 'Berufliches Gymnasium'
            || $this->getName() == 'Berufsfachschule'
            || $this->getName() == 'Berufsschule'
            || $this->getName() == 'Fachoberschule'
            || $this->getName() == 'Fachschule'
        ) {
            return true;
        } else {
            return false;
        }
    }
}
