<?php
namespace SPHERE\System\Config\Reader;

use SPHERE\System\Config\ConfigContainer;
use SPHERE\System\Config\ConfigInterface;

/**
 * Interface ReaderInterface
 *
 * @package SPHERE\System\Config\Reader
 */
interface ReaderInterface extends ConfigInterface
{

    /**
     * @param string|array $Content
     *
     * @return ReaderInterface
     */
    public function setConfig($Content);

    /**
     * @param string $Key
     *
     * @return mixed|null|ConfigContainer
     */
    public function getValue($Key);

    /**
     * @return ConfigContainer
     */
    public function getConfig();
}
