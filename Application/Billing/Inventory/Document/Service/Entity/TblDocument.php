<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.03.2019
 * Time: 09:39
 */

namespace SPHERE\Application\Billing\Inventory\Document\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblDocument")
 * @Cache(usage="READ_ONLY")
 */
class TblDocument extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_IS_WARNING = 'IsWarning';

    const IDENT_MAHNBELEG = 'Mahnbeleg';

    /**
     * @Column(type="string")
     */
    protected $Name;

    /**
     * @Column(type="text")
     */
    protected $Description;

    /**
     * @Column(type="boolean")
     */
    protected $IsWarning;

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
     * @return bool
     */
    public function getIsWarning()
    {
        return $this->IsWarning;
    }

    /**
     * @param bool $IsWarning
     */
    public function setIsWarning($IsWarning = false)
    {
        $this->IsWarning = $IsWarning;
    }
}