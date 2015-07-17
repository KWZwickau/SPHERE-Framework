<?php
namespace SPHERE\System\Support;

/**
 * Interface ITypeInterface
 *
 * @package SPHERE\System\Support
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
    public function setConfiguration( $Configuration );

    /**
     * @param string $Summary
     * @param string $Description
     *
     * @throws \Exception
     * @return array
     */
    public function createTicket( $Summary, $Description );
}
