<?php
namespace SPHERE\System\Database\Binding;

use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Cacheable;

/**
 * Class AbstractData
 *
 * @package SPHERE\System\Database\Binding
 */
abstract class AbstractData extends Cacheable
{

    /** @var null|Binding $Connection */
    private $Connection = null;

    /**
     * @param Binding $Connection
     */
    final public function __construct(Binding $Connection)
    {

        $this->Connection = $Connection;
    }

    /**
     * @return void
     */
    abstract public function setupDatabaseContent();

    /**
     * @return Binding
     */
    final public function getConnection()
    {

        return $this->Connection;
    }
}
