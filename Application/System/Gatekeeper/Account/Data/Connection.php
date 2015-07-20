<?php
namespace SPHERE\Application\System\Gatekeeper\Account\Data;

use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Connection
 *
 * @package SPHERE\Application\System\Gatekeeper\Account\Data
 */
class Connection
{

    /** @var null|Binding $Entity */
    protected $Entity = null;
    /** @var null|Structure $Schema */
    protected $Schema = null;

    /**
     *
     */
    final function __construct()
    {

        $Identifier = new Identifier( 'System', 'Gatekeeper', 'Account' );
        $this->Entity = new Binding( $Identifier, __DIR__.'/Entity', __NAMESPACE__.'\Entity' );
        $this->Schema = new Structure( $Identifier );
    }

    /**
     * @return null|Binding
     */
    public function getEntityConnection()
    {

        return $this->Entity;
    }

    /**
     * @return null|Structure
     */
    public function getSchemaConnection()
    {

        return $this->Schema;
    }
}
