<?php
namespace MOC\V\Component\Database\Component\Parameter\Repository;

use MOC\V\Component\Database\Component\IParameterInterface;
use MOC\V\Component\Database\Component\Parameter\Parameter;

/**
 * Class DatabaseParameter
 *
 * @package MOC\V\Component\Database\Component\Parameter\Repository
 */
class DatabaseParameter extends Parameter implements IParameterInterface
{

    /** @var string $Database */
    private $Database = null;

    /**
     * @param string $Database
     */
    function __construct( $Database )
    {

        $this->setDatabase( $Database );
    }

    /**
     * @return string
     */
    public function getDatabase()
    {

        return $this->Database;
    }

    /**
     * @param string $Database
     */
    public function setDatabase( $Database )
    {

        $this->Database = $Database;
    }
}
