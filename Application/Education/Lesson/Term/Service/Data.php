<?php
namespace SPHERE\Application\Education\Lesson\Term\Service;

use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Cacheable;

/**
 * Class Data
 *
 * @package SPHERE\Application\Education\Lesson\Term\Service
 */
class Data extends Cacheable
{

    /** @var null|Binding $Connection */
    private $Connection = null;

    /**
     * @param Binding $Connection
     */
    function __construct(Binding $Connection)
    {

        $this->Connection = $Connection;
    }

    public function setupDatabaseContent()
    {

    }
}
