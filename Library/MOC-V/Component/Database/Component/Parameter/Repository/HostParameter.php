<?php
namespace MOC\V\Component\Database\Component\Parameter\Repository;

use MOC\V\Component\Database\Component\IParameterInterface;
use MOC\V\Component\Database\Component\Parameter\Parameter;

/**
 * Class HostParameter
 *
 * @package MOC\V\Component\Database\Component\Parameter\Repository
 */
class HostParameter extends Parameter implements IParameterInterface
{

    /** @var string $Host */
    private $Host = 'localhost';

    /**
     * @param string $Host
     */
    function __construct( $Host )
    {

        $this->setHost( $Host );
    }

    /**
     * @return string
     */
    public function getHost()
    {

        return $this->Host;
    }

    /**
     * @param string $Host
     */
    public function setHost( $Host )
    {

        $this->Host = $Host;
    }
}
