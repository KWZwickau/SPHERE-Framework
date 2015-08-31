<?php
namespace SPHERE\System\Authenticator;

/**
 * Interface ITypeInterface
 *
 * @package SPHERE\System\Authenticator
 */
interface ITypeInterface
{

    /**
     * @return string
     */
    public function getConfiguration();

    /**
     * @param array $Configuration
     */
    public function setConfiguration($Configuration);

    /**
     * @return bool|null
     */
    public function validateSignature();

    /**
     * @param array       $Data
     * @param null|string $Location
     *
     * @return array
     */
    public function createSignature($Data, $Location = null);
}
