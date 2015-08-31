<?php
namespace SPHERE\System\Cache;

/**
 * Interface IApiInterface
 *
 * @package SPHERE\System\Cache
 */
interface IApiInterface extends ITypeInterface
{

    /**
     * @param string   $Key
     * @param mixed    $Value
     * @param null|int $Timeout
     *
     * @return bool
     */
    public function setValue($Key, $Value, $Timeout = null);

    /**
     * @param string $Key
     *
     * @return mixed|false
     */
    public function getValue($Key);
}
