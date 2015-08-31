<?php
namespace SPHERE\System\Proxy;

/**
 * Interface ITypeInterface
 *
 * @package SPHERE\System\Proxy
 */
interface ITypeInterface
{

    /**
     * @return null|string
     */
    public function getHost();

    /**
     * @return null|string
     */
    public function getPort();

    /**
     * @return null|string
     */
    public function getUsername();

    /**
     * @return null|string
     */
    public function getPassword();

    /**
     * @return string
     */
    public function getConfiguration();

    /**
     * @param array $Configuration
     */
    public function setConfiguration($Configuration);
}
