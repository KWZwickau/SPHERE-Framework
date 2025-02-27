<?php
namespace SPHERE\Application\Education\School\Type\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\School\Type\Type;
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
    const IDENT_BERUFS_VORBEREITUNGS_JAHR = 'Berufsvorbereitungsjahr';
    const IDENT_FACH_OBER_SCHULE = 'Fachoberschule';
    const IDENT_FACH_SCHULE = 'Fachschule';
    const IDENT_GRUND_SCHULE = 'Grundschule';
    const IDENT_GYMNASIUM = 'Gymnasium';
    const IDENT_KINDER_TAGES_EINRICHTUNG = 'Kindertageseinrichtung';
    const IDENT_OBER_SCHULE = 'Oberschule';
    const IDENT_ALLGEMEIN_BILDENDE_FOERDERSCHULE = 'Förderschule';
    // Berlin
    const IDENT_INTEGRIERTE_SEKUNDAR_SCHULE = 'Integrierte Sekundarschule';
    const IDENT_GEMEINSCHAFTS_SCHULE = 'Gemeinschaftsschule';

    const ATTR_NAME = 'Name';
    const ATTR_SHORT_NAME = 'ShortName';
    const ATTR_IS_BASIC = 'IsBasic';
    const TBL_CATEGORY = 'tblCategory';

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
     * @Column(type="boolean")
     */
    protected $IsBasic;
    /**
     * @Column(type="bigint")
     */
    protected $tblCategory;

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
        if (($tblCategory = $this->getTblCategory())
            && $tblCategory->getIdentifier() == TblCategory::TECHNICAL
        ) {
            return  true;
        }

        return false;
    }

    /**
     * @return boolean
     */
    public function getIsBasic()
    {
        return $this->IsBasic;
    }

    /**
     * @param boolean $IsBasic
     */
    public function setIsBasic($IsBasic)
    {
        $this->IsBasic = $IsBasic;
    }

    /**
     * @return bool|TblCategory
     */
    public function getTblCategory()
    {
        if (null === $this->tblCategory) {
            return false;
        } else {
            return Type::useService()->getCategoryById($this->tblCategory);
        }
    }

    /**
     * @param null|TblCategory $tblCategory
     */
    public function setTblCategory(TblCategory $tblCategory = null)
    {
        $this->tblCategory = ( null === $tblCategory ? null : $tblCategory->getId() );
    }

    /**
     * @return false|int
     */
    public function getMaxLevel()
    {
        return Type::useService()->getMaxLevelByType($this);
    }

    /**
     * @return false|int
     */
    public function getMinLevel()
    {
        return Type::useService()->getMinLevelByType($this);
    }
}
