<?php
namespace SPHERE\System\Database\Fitting;

use SPHERE\System\Database\Database;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Binding
 *
 * @package SPHERE\Application
 */
class Binding
{

    /** @var null|Database $Database */
    private $Database = null;
    /** @var string $EntityPath */
    private $EntityPath = '';
    /** @var string $EntityNamespace */
    private $EntityNamespace = '';

    /**
     * @param Identifier $Identifier
     * @param string     $EntityPath
     * @param string     $EntityNamespace
     */
    public function __construct(Identifier $Identifier, $EntityPath, $EntityNamespace)
    {

        $this->Database = new Database($Identifier);
        $this->EntityPath = $EntityPath;
        $this->EntityNamespace = $EntityNamespace;
    }

    /**
     * @param bool $useCache
     *
     * @return Manager
     */
    public function getEntityManager($useCache = true)
    {

        return $this->Database->getEntityManager($this->EntityPath, $this->EntityNamespace, $useCache);
    }

    /**
     * @param $Statement
     *
     * @return int The number of affected rows
     */
    public function setStatement($Statement)
    {

        return $this->Database->setStatement($Statement);
    }

    /**
     * @param $Statement
     *
     * @return array
     */
    public function getStatement($Statement)
    {

        return $this->Database->getStatement($Statement);
    }

    /**
     * @return string
     */
    public function getDatabase()
    {

        return $this->Database->getDatabase();
    }

    /**
     * @param string $Item
     */
    public function addProtocol($Item)
    {

        $this->Database->addProtocol($Item);
    }


    /**
     * @param bool $Simulate
     *
     * @return string
     */
    public function getProtocol($Simulate = false)
    {

        return $this->Database->getProtocol($Simulate);
    }

    /**
     * @return string
     */
    public function getEntityNamespace()
    {

        return $this->EntityNamespace;
    }
}
