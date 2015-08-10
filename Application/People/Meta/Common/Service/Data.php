<?php
namespace SPHERE\Application\People\Meta\Common\Service;

use SPHERE\System\Database\Fitting\Binding;

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
