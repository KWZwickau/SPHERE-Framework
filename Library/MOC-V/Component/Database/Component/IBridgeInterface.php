<?php
namespace MOC\V\Component\Database\Component;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use MOC\V\Component\Database\Component\Exception\Repository\NoConnectionException;
use MOC\V\Component\Database\Component\Parameter\Repository\DatabaseParameter;
use MOC\V\Component\Database\Component\Parameter\Repository\DriverParameter;
use MOC\V\Component\Database\Component\Parameter\Repository\HostParameter;
use MOC\V\Component\Database\Component\Parameter\Repository\PasswordParameter;
use MOC\V\Component\Database\Component\Parameter\Repository\PortParameter;
use MOC\V\Component\Database\Component\Parameter\Repository\UsernameParameter;

/**
 * Interface IBridgeInterface
 *
 * @package MOC\V\Component\Database\Component
 */
interface IBridgeInterface
{

    /**
     * @param UsernameParameter $Username
     * @param PasswordParameter $Password
     * @param DatabaseParameter $Database
     * @param DriverParameter   $Driver
     * @param HostParameter     $Host
     * @param PortParameter     $Port
     * @param int               $Timeout
     *
     * @return IBridgeInterface
     */
    public function registerConnection(
        UsernameParameter $Username,
        PasswordParameter $Password,
        DatabaseParameter $Database,
        DriverParameter $Driver,
        HostParameter $Host,
        PortParameter $Port,
        $Timeout = 5
    );

    /**
     * Example: SELECT * FROM example WHERE id = ? AND name = ?
     *
     * @param string $Sql
     *
     * @return IBridgeInterface
     */
    public function prepareStatement($Sql);

    /**
     * @param mixed    $Value
     * @param null|int $Type
     *
     * @return IBridgeInterface
     */
    public function defineParameter($Value, $Type = null);

    /**
     * @return array
     */
    public function executeRead();

    /**
     * @return int
     */
    public function executeWrite();

    /**
     * WARNING: this may drop out with no replacement
     *
     * @return AbstractSchemaManager
     * @throws NoConnectionException
     */
    public function getSchemaManager();

    /**
     * WARNING: this may drop out with no replacement
     *
     * @return QueryBuilder
     * @throws NoConnectionException
     */
    public function getQueryBuilder();

    /**
     * WARNING: this may drop out with no replacement
     *
     * @return Connection
     * @throws NoConnectionException
     */
    public function getConnection();
}
