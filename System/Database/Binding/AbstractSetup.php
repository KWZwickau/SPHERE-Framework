<?php
namespace SPHERE\System\Database\Binding;

use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Extension\Extension;

/**
 * Class AbstractSetup
 *
 * @package SPHERE\System\Database\Binding
 */
abstract class AbstractSetup extends Extension
{

    /** @var null|Structure $Connection */
    private $Connection = null;

    /**
     * @param Structure $Connection
     */
    final public function __construct(Structure $Connection)
    {

        $this->Connection = $Connection;
    }

    /**
     * @param bool $Simulate
     *
     * @return string
     */
    abstract public function setupDatabaseSchema($Simulate = true);

    /**
     * @return Structure
     */
    final public function getConnection()
    {

        return $this->Connection;
    }
}
