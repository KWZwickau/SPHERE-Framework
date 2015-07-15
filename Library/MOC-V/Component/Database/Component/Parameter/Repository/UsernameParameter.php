<?php
namespace MOC\V\Component\Database\Component\Parameter\Repository;

use MOC\V\Component\Database\Component\IParameterInterface;
use MOC\V\Component\Database\Component\Parameter\Parameter;

/**
 * Class UsernameParameter
 *
 * @package MOC\V\Component\Database\Component\Parameter\Repository
 */
class UsernameParameter extends Parameter implements IParameterInterface
{

    /** @var string $Username */
    private $Username = null;

    /**
     * @param string $Username
     */
    function __construct( $Username )
    {

        $this->setUsername( $Username );
    }

    /**
     * @return string
     */
    public function getUsername()
    {

        return $this->Username;
    }

    /**
     * @param string $Username
     */
    public function setUsername( $Username )
    {

        $this->Username = $Username;
    }
}
