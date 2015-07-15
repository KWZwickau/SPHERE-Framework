<?php
namespace MOC\V\Component\Database\Component\Parameter\Repository;

use MOC\V\Component\Database\Component\IParameterInterface;
use MOC\V\Component\Database\Component\Parameter\Parameter;

/**
 * Class PasswordParameter
 *
 * @package MOC\V\Component\Database\Component\Parameter\Repository
 */
class PasswordParameter extends Parameter implements IParameterInterface
{

    /** @var string $Password */
    private $Password = null;

    /**
     * @param string $Password
     */
    function __construct( $Password )
    {

        $this->setPassword( $Password );
    }

    /**
     * @return string
     */
    public function getPassword()
    {

        return $this->Password;
    }

    /**
     * @param string $Password
     */
    public function setPassword( $Password )
    {

        $this->Password = $Password;
    }
}
