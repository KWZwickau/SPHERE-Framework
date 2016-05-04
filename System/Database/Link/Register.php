<?php
namespace SPHERE\System\Database\Link;

/**
 * Class Register
 *
 * @package SPHERE\System\Database\Link
 */
class Register
{

    private static $Register = array();

    /**
     * @param Identifier $Identifier
     * @param Connection $Connection
     *
     * @return Register
     */
    public function addConnection(Identifier $Identifier, Connection $Connection)
    {

        if (!$this->hasConnection($Identifier)) {
            self::$Register[$Identifier->getIdentifier()] = $Connection;
        }
        return $this;
    }

    /**
     * @param Identifier $Identifier
     *
     * @return bool
     */
    public function hasConnection(Identifier $Identifier)
    {

        return isset( self::$Register[$Identifier->getIdentifier()] );
    }

    /**
     * @param Identifier $Identifier
     *
     * @return Connection
     * @throws \Exception
     */
    public function getConnection(Identifier $Identifier)
    {

        if ($this->hasConnection($Identifier)) {
            return self::$Register[$Identifier->getIdentifier()];
        } else {
            throw new \Exception(__CLASS__.' > Connection not available: ('.$Identifier->getIdentifier().') ['.$Identifier->getConfiguration(true).']');
        }
    }
}
