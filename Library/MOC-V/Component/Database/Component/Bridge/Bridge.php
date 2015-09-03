<?php
namespace MOC\V\Component\Database\Component\Bridge;

use MOC\V\Component\Database\Component\IBridgeInterface;

/**
 * Class Bridge
 *
 * @package MOC\V\Component\Database\Component\Bridge
 */
abstract class Bridge implements IBridgeInterface
{

    /** @var array $StatementList */
    protected static $StatementList = array();

    /** @var array $ParameterList */
    protected static $ParameterList = array();

    /**
     * Example: SELECT * FROM example WHERE id = ? AND name = ?
     *
     * @param string $Sql
     *
     * @return IBridgeInterface
     */
    final public function prepareStatement($Sql)
    {

        if (is_array($Sql)) {
            static::$StatementList += $Sql;
        } else {
            array_push(static::$StatementList, $Sql);
        }
        return $this;
    }

    /**
     * @param mixed    $Value
     * @param null|int $Type
     *
     * @return IBridgeInterface
     */
    final public function defineParameter($Value, $Type = null)
    {

        array_push(static::$ParameterList, array($Value, $Type));
        return $this;
    }
}
