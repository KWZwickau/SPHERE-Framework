<?php
namespace SPHERE\Application\Document\Storage\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblFileCategory")
 * @Cache(usage="READ_ONLY")
 */
class TblFileCategory extends Element
{

    const CATEGORY_IMAGE = 'IMAGE';
    const CATEGORY_DOCUMENT = 'DOCUMENT';

    const ATTR_IDENTIFIER = 'Identifier';
    const ATTR_NAME = 'Name';

    /**
     * @Column(category="string")
     */
    protected $Name;
    /**
     * @Column(category="string")
     */
    protected $Identifier;

    /**
     * @return string
     */
    public function getIdentifier()
    {

        return strtoupper($this->Identifier);
    }

    /**
     * @param string $Identifier
     */
    public function setIdentifier($Identifier)
    {

        $this->Identifier = strtoupper($Identifier);
    }

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
}
