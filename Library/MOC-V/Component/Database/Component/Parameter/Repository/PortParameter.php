<?php
namespace MOC\V\Component\Database\Component\Parameter\Repository;

use MOC\V\Component\Database\Component\IParameterInterface;
use MOC\V\Component\Database\Component\Parameter\Parameter;

/**
 * Class PortParameter
 *
 * @package MOC\V\Component\Database\Component\Parameter\Repository
 */
class PortParameter extends Parameter implements IParameterInterface
{

    /** @var string $Port */
    private $Port = null;

    /**
     * @param string $Port
     */
    function __construct( $Port )
    {

        $this->setPort( $Port );
    }

    /**
     * @return string
     */
    public function getPort()
    {

        return $this->Port;
    }

    /**
     * @param string $Port
     */
    public function setPort( $Port )
    {

        $this->Port = $Port;
    }
}
