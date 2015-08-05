<?php
namespace SPHERE\Application\People\Person\Service;

use SPHERE\System\Database\Fitting\Binding;

/**
 * Class Data
 *
 * @package SPHERE\Application\People\Person\Service
 */
class Data
{

    /** @var null|Binding $Connection */
    private $Connection = null;

    /**
     * @param Binding $Connection
     */
    function __construct( Binding $Connection )
    {

        $this->Connection = $Connection;
    }

    public function setupDatabaseContent()
    {

    }
}
