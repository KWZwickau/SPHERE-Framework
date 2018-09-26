<?php
namespace SPHERE\Application\Setting\Consumer\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblSetting")
 * @Cache(usage="READ_ONLY")
 */
class TblSetting extends Element
{

    const ATTR_CLUSTER = 'Cluster';
    const ATTR_APPLICATION = 'Application';
    const ATTR_MODULE = 'Module';
    const ATTR_IDENTIFIER = 'Identifier';
    const ATTR_TYPE = 'Type';
    const ATTR_IS_PUBLIC = 'IsPublic';
    const ATTR_DESCRIPTION = 'Description';

    const TYPE_BOOLEAN = 'boolean';
    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'integer';

    const SORT_GERMAN_AE_WITHOUT = 'german-string-ae-without';
    const SORT_GERMAN_AE_WITH = 'german-string-ae-with';
    const SORT_GERMAN_A_WITHOUT = 'german-string-a-without';
    const SORT_GERMAN_A_WITH = 'german-string-a-with';

    /**
     * @Column(type="string")
     */
    protected $Cluster;

    /**
     * @Column(type="string")
     */
    protected $Application;

    /**
     * @Column(type="string")
     */
    protected $Module;

    /**
     * @Column(type="string")
     */
    protected $Identifier;

    /**
     * @Column(type="string")
     */
    protected $Type;

    /**
     * @Column(type="text")
     */
    protected $Value;

    /**
     * @Column(type="text")
     */
    protected $Description;

    /**
     * @Column(type="boolean")
     */
    protected $IsPublic;

    /**
     * @return string
     */
    public function getCluster()
    {
        return $this->Cluster;
    }

    /**
     * @param string $Cluster
     */
    public function setCluster($Cluster)
    {
        $this->Cluster = $Cluster;
    }

    /**
     * @return string
     */
    public function getApplication()
    {
        return $this->Application;
    }

    /**
     * @param string $Application
     */
    public function setApplication($Application)
    {
        $this->Application = $Application;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->Identifier;
    }

    /**
     * @param string $Identifier
     */
    public function setIdentifier($Identifier)
    {
        $this->Identifier = $Identifier;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->Type;
    }

    /**
     * @param string $Type
     */
    public function setType($Type)
    {
        $this->Type = $Type;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->Value;
    }

    /**
     * @param string $Value
     */
    public function setValue($Value)
    {
        $this->Value = $Value;
    }

    /**
     * @return string|null
     */
    public function getModule()
    {
        return $this->Module;
    }

    /**
     * @param string|null $Module
     */
    public function setModule($Module)
    {
        $this->Module = $Module;
    }

    /**
     * @return mixed
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
     * @return boolean
     */
    public function isPublic()
    {
        return $this->IsPublic;
    }

    /**
     * @param boolean $IsPublic
     */
    public function setIsPublic($IsPublic)
    {
        $this->IsPublic = $IsPublic;
    }
}